<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require dirname(__DIR__).'/Deploy.php';
require dirname(__DIR__).'/NativeSupport.php';
use IssabelDeploy\Deployer;
use IssabelDeploy\Files;
use IssabelDeploy\GitSource;
use function IssabelDeploy\run;

define('DB_FETCHMODE_ASSOC',2);
$installedRows = array();
$externalMetadata = array();
function sql($query,$mode = null,$fetch = null) {
    global $installedRows;
    return array_map(function ($name,$row) { return array('modulename'=>$name,'version'=>$row['version'],'enabled'=>$row['enabled']); },array_keys($installedRows),array_values($installedRows));
}
function version_compare_issabel($a,$b,$operator = null) { return $operator === null ? version_compare($a,$b) : version_compare($a,$b,$operator); }
function module_getinfo($name = false,$status = false,$force = false) { global $externalMetadata; return $externalMetadata; }
function module_checkdepends($metadata) {
    global $installedRows;
    $errors = array();
    foreach ($metadata['depends']['module'] ?? array() as $requirement) {
        $parts = preg_split('/\s+/',trim($requirement));
        if (empty($installedRows[$parts[0]]['enabled'])) { $errors[] = 'Missing dependency '.$parts[0]; }
    }
    return $errors;
}
function check($condition,$message) {
    global $checks;
    if (!$condition) { throw new RuntimeException($message); }
    $checks++;
}
function rejects(callable $operation,string $message): void {
    try { $operation(); } catch (Throwable $error) {
        check(stripos($error->getMessage(),$message) !== false,'Unexpected error: '.$error->getMessage().' (expected '.$message.')');
        return;
    }
    throw new RuntimeException('Expected rejection: '.$message);
}
function removeTree(string $path): void {
    if (is_link($path) || !is_dir($path)) { unlink($path); return; }
    foreach (new DirectoryIterator($path) as $entry) { if (!$entry->isDot()) { removeTree($entry->getPathname()); } }
    rmdir($path);
}
class FixtureBridge implements IssabelDeploy\Bridge {
    public $root;
    public $pending = false;
    public $reloads = 0;
    public $failReload = 0;
    public $failInstall = false;
    public $failBackup = false;
    public $failHealth = false;
    public $installed = array();
    public $validateCalls = 0;
    public $lateConflict = false;
    public function __construct($root) { $this->root = $root; }
    public function call(string $action,array $data = array()): array {
        global $installedRows;
        if ($action === 'inspect') {
            return array('modules_dir'=>$this->root.'/web/admin/modules','config_dir'=>$this->root.'/etc', 'webroot'=>$this->root.'/web',
                'user'=>posix_getpwuid(posix_geteuid())['name'],'uid'=>posix_geteuid(),'gid'=>posix_getegid(),
                'php_version'=>PHP_VERSION,'asterisk_version'=>'22.9.0','pbx_version'=>'2.13.0.0','modules'=>$installedRows,'pending_reload'=>$this->pending);
        }
        if ($action === 'validate') {
            $this->validateCalls++;
            if ($this->lateConflict && $this->validateCalls === 3) { file_put_contents($this->root.'/web/admin/modules/core/etc/pjsip.conf','late operator edit'); }
            return deploy_targets($data['targets']);
        }
        if ($action === 'backup-check') { return array('estimated_bytes'=>1024); }
        if ($action === 'permissions') { return array('ok'=>true); }
        if ($action === 'backup') {
            if ($this->failBackup) { throw new RuntimeException('Backup failed'); }
            check(array_keys($data['modules']) === array('core','sipsettings'),'Backup must name both module destinations');
            file_put_contents($data['directory'].'/database.sql','fixture database snapshot');
            file_put_contents($data['directory'].'/astdb.sqlite3','fixture astdb snapshot');
            return array('ok'=>true);
        }
        if ($action === 'install') {
            $this->installed[] = $data['module'];
            if ($this->failInstall) { throw new RuntimeException('Simulated migration failure'); }
            $xml = simplexml_load_file($this->root.'/web/admin/modules/'.$data['module'].'/module.xml');
            $installedRows[$data['module']]['version'] = (string)$xml->version;
            return array('ok'=>true);
        }
        if ($action === 'reload') {
            $this->reloads++;
            file_put_contents($this->root.'/etc/generated.conf',hash_file('sha256',$this->root.'/web/admin/modules/core/functions.inc.php'));
            if ($this->failReload-- > 0) { throw new RuntimeException('Simulated reload failure'); }
            return array('status'=>'success');
        }
        if ($action === 'health') {
            if ($this->failHealth) { throw new RuntimeException('Simulated health failure'); }
            foreach ($data['targets'] as $target) {
                check($installedRows[$target['rawname']]['version'] === $target['version'],'Installed version must match target');
            }
            return array('status'=>'healthy');
        }
        throw new RuntimeException('Unexpected fixture action '.$action);
    }
}
class Fixture {
    public $root;
    public $repo;
    public $base;
    public $target;
    public $bridge;
    public $deployer;
    public function git(array $args): string { return trim(run(array_merge(array('git','-C',$this->repo),$args))); }
    public function file($path,$bytes): void {
        $path = $this->repo.'/'.$path;
        if (!is_dir(dirname($path))) { mkdir(dirname($path),0755,true); }
        file_put_contents($path,$bytes);
    }
    public function commit(): string {
        $this->git(array('add','.'));
        $this->git(array('-c','user.name=Deployment Test','-c','user.email=test@example.invalid','commit','-qm','fixture'));
        return $this->git(array('rev-parse','HEAD'));
    }
    public function __construct() {
        global $installedRows,$externalMetadata;
        $installedRows = array('core'=>array('version'=>'1.0.0','enabled'=>true),'sipsettings'=>array('version'=>'1.0.0','enabled'=>true),'framework'=>array('version'=>'2.13.0.0','enabled'=>true));
        $externalMetadata = array();
        $this->root = sys_get_temp_dir().'/issabel-deploy-tests-'.bin2hex(random_bytes(6));
        mkdir($this->root,0700);
        $this->repo = $this->root.'/repo'; mkdir($this->repo);
        $this->git(array('init','-q'));
        foreach (array('core','sipsettings') as $module) {
            $this->file($module.'/module.xml','<module><rawname>'.$module.'</rawname><version>1.0.0</version><depends><module>'.($module === 'core' ? 'framework ge 2.13.0.0' : 'core ge 1.0.0').'</module></depends></module>');
            $this->file($module.'/functions.inc.php',"<?php return 'old';\n");
        }
        $this->file('core/remove.php',"<?php return true;\n");
        $this->file('core/etc/pjsip.conf',"[global]\ntype=global\n");
        $this->base = $this->commit();
        $source = new GitSource($this->repo);
        foreach (array('core','sipsettings') as $module) {
            $dest = $this->root.'/web/admin/modules/'.$module;
            mkdir($dest,0755,true);
            foreach ($source->tree($this->base,$module) as $path=>$meta) {
                $meta['uid'] = posix_geteuid(); $meta['gid'] = posix_getegid();
                Files::write(Files::path($dest,$path),$source->blob($meta['oid']),$meta);
            }
        }
        mkdir($this->root.'/etc');
        symlink('../web/admin/modules/core/etc/pjsip.conf',$this->root.'/etc/pjsip.conf');
        file_put_contents($this->root.'/etc/pjsip_custom_post.conf',"; local configuration\n");
        file_put_contents($this->root.'/etc/generated.conf',hash_file('sha256',$this->root.'/web/admin/modules/core/functions.inc.php'));
        $this->file('core/functions.inc.php',"<?php return 'new';\n");
        $this->file('core/pjsip.functions.php',"<?php return 'new helper';\n");
        $this->file('sipsettings/functions.inc.php',"<?php return 'new sipsettings';\n");
        $this->file('core/etc/pjsip.conf',"[global]\ntype=global\n#include pjsip_global_additional.conf\n");
        unlink($this->repo.'/core/remove.php');
        symlink('functions.inc.php',$this->repo.'/core/alias.php');
        $this->target = $this->commit();
        $this->bridge = new FixtureBridge($this->root);
        $this->deployer = new Deployer($this->bridge,$this->root.'/state');
    }
    public function plan(?string $base = null): array { return $this->deployer->plan($this->repo,$base ?? $this->base,$this->target,array('core','sipsettings')); }
    public function upgrade(): void {
        foreach (array('core','sipsettings') as $module) {
            $xml = file_get_contents($this->repo.'/'.$module.'/module.xml');
            $this->file($module.'/module.xml',str_replace('1.0.0','1.1.0',$xml));
            $this->file($module.'/install.php',"<?php return true;\n");
        }
        $this->target = $this->commit();
    }
}
function scenario(string $name,callable $test): void {
    $fixture = new Fixture();
    try { $test($fixture); echo 'PASS '.$name."\n"; }
    finally { removeTree($fixture->root); }
}
$checks = 0;
scenario('patch, committed content, additions/deletions/links, rollback and repeat',function ($f) {
    $before = Files::tree($f->root.'/etc');
    $f->file('core/functions.inc.php',"<?php return 'uncommitted';\n");
    file_put_contents($f->root.'/web/admin/modules/core/local-only.txt','local runtime file');
    $plan = $f->plan();
    check($f->bridge->reloads === 0,'Plan must not reload');
    check(!file_exists($f->root.'/web/admin/modules/core/pjsip.functions.php'),'Plan must not write PBX files');
    $applied = $f->deployer->apply($plan['plan']);
    check($applied['status'] === 'complete' && $f->bridge->reloads === 1,'Patch must have one reload');
    check(strpos(file_get_contents($f->root.'/web/admin/modules/core/functions.inc.php'),'uncommitted') === false,'Must deploy committed blob');
    check(!file_exists($f->root.'/web/admin/modules/core/remove.php'),'Deleted tracked file must be removed');
    check(is_link($f->root.'/web/admin/modules/core/alias.php'),'Committed safe symlink must be preserved');
    check($f->bridge->installed === array(),'Same-version patch must not run installer');
    check(file_get_contents($f->root.'/web/admin/modules/core/local-only.txt') === 'local runtime file','Untracked runtime files must survive deployment');
    check(file_get_contents($f->root.'/etc/pjsip_custom_post.conf') === "; local configuration\n",'Custom configuration must be preserved');
    $again = $f->deployer->plan($f->repo,null,$f->target,array('core','sipsettings'));
    check($again['changes'] === array() && $f->deployer->apply($again['plan'])['status'] === 'unchanged','Repeated target must be a no-op');
    $f->deployer->rollback($applied['deployment']);
    check(Files::tree($f->root.'/etc') === $before,'Rollback must restore generated configuration and links');
    check(!file_exists($f->root.'/web/admin/modules/core/pjsip.functions.php'),'Rollback must remove added files');
    check(file_exists($f->root.'/web/admin/modules/core/remove.php'),'Rollback must restore deleted file');
});
scenario('local customization',function ($f) {
    file_put_contents($f->root.'/web/admin/modules/core/functions.inc.php','local edit');
    rejects(function () use ($f) { $f->plan(); },'customization');
});
scenario('file changes after planning',function ($f) {
    $plan = $f->plan();
    file_put_contents($f->root.'/web/admin/modules/core/functions.inc.php','later edit');
    rejects(function () use ($f,$plan) { $f->deployer->apply($plan['plan']); },'conflict');
    check($f->bridge->reloads === 0,'Conflict must abort before reload');
});
scenario('conflict immediately before first write does not overwrite or reload',function ($f) {
    $plan = $f->plan(); $f->bridge->lateConflict = true;
    rejects(function () use ($f,$plan) { $f->deployer->apply($plan['plan']); },'aborted-before-write');
    check(file_get_contents($f->root.'/web/admin/modules/core/etc/pjsip.conf') === 'late operator edit' && $f->bridge->reloads === 0,'Late conflicting edit must survive untouched');
});
scenario('modified plan',function ($f) {
    $plan = $f->plan(); $data = json_decode(file_get_contents($plan['plan']),true); $data['target'] = $f->base;
    file_put_contents($plan['plan'],json_encode($data));
    rejects(function () use ($f,$plan) { $f->deployer->apply($plan['plan']); },'modified plan');
});
scenario('pending GUI changes',function ($f) {
    $f->bridge->pending = true;
    rejects(function () use ($f) { $f->plan(); },'pending changes');
});
scenario('missing dependency',function ($f) {
    global $installedRows; unset($installedRows['framework']);
    rejects(function () use ($f) { $f->plan(); },'Dependencies failed');
});
scenario('installer without version increase',function ($f) {
    $f->file('core/install.php',"<?php return true;\n"); $f->target = $f->commit();
    rejects(function () use ($f) { $f->plan(); },'version increase');
});
scenario('framework and missing baseline refused',function ($f) {
    rejects(function () use ($f) { $f->deployer->plan($f->repo,$f->base,$f->target,array('framework')); },'Unsupported module');
    rejects(function () use ($f) { $f->deployer->plan($f->repo,null,$f->target,array('core')); },'requires --from');
});
scenario('invalid PHP',function ($f) {
    $f->file('core/functions.inc.php','<?php invalid {'); $f->target = $f->commit();
    rejects(function () use ($f) { $f->plan(); },'failed');
});
scenario('backup failure before writes',function ($f) {
    $plan = $f->plan(); $f->bridge->failBackup = true;
    rejects(function () use ($f,$plan) { $f->deployer->apply($plan['plan']); },'aborted-before-write');
    check($f->bridge->reloads === 0 && !file_exists($f->root.'/web/admin/modules/core/pjsip.functions.php'),'Backup failure must leave installation untouched');
});
scenario('reload failure automatically restores patch',function ($f) {
    $before = Files::tree($f->root.'/etc');
    $plan = $f->plan(); $f->bridge->failReload = 1;
    rejects(function () use ($f,$plan) { $f->deployer->apply($plan['plan']); },'auto-rolled-back');
    check(Files::tree($f->root.'/etc') === $before && $f->bridge->reloads === 2,'Failed reload must restore and reload the old configuration');
});
scenario('upgrade follows dependency order and cannot roll back files only',function ($f) {
    $f->upgrade(); $plan = $f->plan();
    check($plan['upgrades'] === array('core','sipsettings'),'Upgrade list must include both modules');
    $applied = $f->deployer->apply($plan['plan']);
    check($f->bridge->installed === array('core','sipsettings') && $f->bridge->reloads === 1,'Upgrade order/reload count incorrect');
    rejects(function () use ($f,$applied) { $f->deployer->rollback($applied['deployment']); },'Migration started');
});
scenario('migration failure preserves state and blocks further deployments',function ($f) {
    $f->upgrade(); $plan = $f->plan(); $f->bridge->failInstall = true;
    rejects(function () use ($f,$plan) { $f->deployer->apply($plan['plan']); },'recovery-required');
    check(file_exists($f->root.'/web/admin/modules/core/pjsip.functions.php') && $f->bridge->reloads === 0,'Migration failure must retain deployed files and avoid reload');
    rejects(function () use ($f) { $f->plan(); },'Unresolved deployment');
});
scenario('rollback refuses subsequent operator edits',function ($f) {
    $plan = $f->plan(); $applied = $f->deployer->apply($plan['plan']);
    file_put_contents($f->root.'/etc/pjsip_custom_post.conf','new local customization');
    rejects(function () use ($f,$applied) { $f->deployer->rollback($applied['deployment']); },'Configuration changed');
});
scenario('rollback verifies backups before touching production',function ($f) {
    $plan = $f->plan(); $applied = $f->deployer->apply($plan['plan']);
    $files = glob($applied['backup'].'/files/*'); file_put_contents($files[0],'corrupted');
    $before = Files::tree($f->root.'/web/admin/modules/core');
    rejects(function () use ($f,$applied) { $f->deployer->rollback($applied['deployment']); },'checksum mismatch');
    check(Files::tree($f->root.'/web/admin/modules/core') === $before,'Corrupt backup must abort before restoring anything');
});
scenario('exclusive deployment lock',function ($f) {
    $lock = fopen($f->root.'/state/lock','c'); flock($lock,LOCK_EX);
    try { rejects(function () use ($f) { $f->plan(); },'Another deployment'); }
    finally { flock($lock,LOCK_UN); fclose($lock); }
});
scenario('unsafe symlinks and custom files',function ($f) {
    symlink('/etc/passwd',$f->repo.'/core/unsafe'); $f->target = $f->commit();
    rejects(function () use ($f) { $f->plan(); },'Unsafe repository symlink');
    unlink($f->repo.'/core/unsafe');
    $f->file('core/etc/pjsip_custom.conf','do not overwrite'); $f->target = $f->commit();
    rejects(function () use ($f) { $f->plan(); },'Custom file');
});
scenario('reverse dependency compatibility and downgrade',function ($f) {
    global $externalMetadata,$installedRows;
    $f->upgrade();
    $installedRows['consumer'] = array('version'=>'1.0.0','enabled'=>true);
    $externalMetadata['consumer'] = array('depends'=>array('module'=>'core lt 1.1.0'));
    rejects(function () use ($f) { $f->plan(); },'break installed module');
    $externalMetadata = array(); $installedRows['core']['version'] = '9.0.0';
    rejects(function () use ($f) { deploy_targets(array(array('rawname'=>'core','version'=>'1.1.0'))); },'Downgrade');
});
// Check the JSON/error contract of the real subprocess helper without a PBX.
$reply = json_decode(run(array(PHP_BINARY,dirname(__DIR__).'/native.php','unknown','/missing'),'{}'),true);
check($reply['ok'] === false && strpos($reply['error'],'Invalid native action') !== false,'Native helper must return structured errors');
$bootstrap = tempnam(sys_get_temp_dir(),'issabel-native-exit-');
try {
    file_put_contents($bootstrap,'<?php exit(0);');
    $reply = json_decode(run(array(PHP_BINARY,dirname(__DIR__).'/native.php','validate',$bootstrap),'{}'),true);
    check($reply['ok'] === false && strpos($reply['error'],'terminated unexpectedly') !== false,'An installer/bootstrap exit must not look like success');
} finally { unlink($bootstrap); }
echo "Passed $checks assertions\n";
