<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require dirname(__DIR__).'/Deploy.php';
require dirname(__DIR__).'/Inspect.php';
require dirname(__DIR__).'/Interactive.php';
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
    public $pbxVersion = '2.13.0.0';
    public $installRequests = array();
    public function __construct($root) { $this->root = $root; }
    public function call(string $action,array $data = array()): array {
        global $installedRows;
        if ($action === 'inspect') {
            return array('modules_dir'=>$this->root.'/web/admin/modules','config_dir'=>$this->root.'/etc', 'webroot'=>$this->root.'/web',
                'user'=>posix_getpwuid(posix_geteuid())['name'],'uid'=>posix_geteuid(),'gid'=>posix_getegid(),
                'bin_dir'=>$this->root.'/bin','agi_dir'=>$this->root.'/agi',
                'php_version'=>PHP_VERSION,'asterisk_version'=>'22.9.0','pbx_version'=>$this->pbxVersion,'modules'=>$installedRows,'pending_reload'=>$this->pending);
        }
        if ($action === 'validate') {
            $this->validateCalls++;
            if ($this->lateConflict && $this->validateCalls === 3) { file_put_contents($this->root.'/web/admin/modules/core/etc/pjsip.conf','late operator edit'); }
            return deploy_targets($data['targets'],!empty($data['interactive']));
        }
        if ($action === 'backup-check') { return array('estimated_bytes'=>1024); }
        if ($action === 'permissions') { return array('ok'=>true); }
        if ($action === 'backup') {
            if ($this->failBackup) { throw new RuntimeException('Backup failed'); }
            foreach ($data['modules'] as $name=>$path) { check(strpos($path,$this->root.'/web/admin/modules/') === 0,'Backup must name installed module destinations'); }
            file_put_contents($data['directory'].'/database.sql','fixture database snapshot');
            file_put_contents($data['directory'].'/astdb.sqlite3','fixture astdb snapshot');
            return array('ok'=>true);
        }
        if ($action === 'install') {
            $this->installed[] = $data['module'];
            $this->installRequests[] = $data;
            if ($this->failInstall) { throw new RuntimeException('Simulated migration failure'); }
            $xml = simplexml_load_file($this->root.'/web/admin/modules/'.$data['module'].'/module.xml');
            if ($data['module'] === 'framework') {
                $package = $this->root.'/web/admin/modules/framework';
                foreach (array('htdocs'=>$this->root.'/web','bin'=>$this->root.'/bin','agi-bin'=>$this->root.'/agi') as $kind=>$destination) {
                    if (!is_dir($package.'/amp_conf/'.$kind)) { continue; }
                    foreach (Files::tree($package.'/amp_conf/'.$kind) as $path=>$meta) {
                        $meta = Files::nativeCopyTarget($meta,Files::snapshot(Files::path($destination,$path)));
                        Files::write(Files::path($destination,$path),file_get_contents($package.'/amp_conf/'.$kind.'/'.$path),$meta);
                    }
                }
                foreach (array('amp_conf','upgrades') as $dir) { if (is_dir($package.'/'.$dir)) { removeTree($package.'/'.$dir); } }
                if (is_file($package.'/libissabelpbx.install.php')) { unlink($package.'/libissabelpbx.install.php'); }
                $this->pbxVersion = (string)$xml->version;
            }
            if (!isset($installedRows[$data['module']])) { $installedRows[$data['module']] = array('enabled'=>true); }
            $installedRows[$data['module']]['version'] = (string)$xml->version;
            return array('ok'=>true);
        }
        if ($action === 'reload') {
            $this->reloads++;
            file_put_contents($this->root.'/etc/generated.conf',hash_file('sha256',$this->root.'/web/admin/modules/core/functions.inc.php'));
            if ($this->failReload-- > 0) { throw new RuntimeException('Simulated reload failure'); }
            $this->pending = false;
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
        $this->root = realpath($this->root);
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
        mkdir($this->root.'/bin'); mkdir($this->root.'/agi');
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
scenario('mysqldump preserves diagnostics and removes failed dumps and credentials',function ($f) {
    $directory = $f->root.'/mysql-backup'; mkdir($directory,0700);
    $config = array('AMPDBUSER'=>'asteriskuser','AMPDBPASS'=>'private-test-secret','AMPDBHOST'=>'localhost','AMPDBNAME'=>'asterisk');
    $script = $f->root.'/fake-dump.php';
    file_put_contents($script,"<?php fwrite(STDOUT, '-- incomplete dump'); fwrite(STDERR, 'Access denied; RELOAD privilege required; private-test-secret'); exit(2);");
    try {
        deploy_mysql_backup($config,$directory,array(PHP_BINARY,$script));
        throw new RuntimeException('Expected mysqldump failure');
    } catch (RuntimeException $error) {
        check(strpos($error->getMessage(),'RELOAD privilege required') !== false && strpos($error->getMessage(),'exit 2') !== false,'The actual dump failure and exit status must be reported');
        check(strpos($error->getMessage(),'private-test-secret') === false && strpos($error->getMessage(),'[redacted]') !== false,'Backup errors must redact configured secrets');
    }
    check(!file_exists($directory.'/database.sql') && !glob($directory.'/mysql-*'),'Failed SQL dumps and temporary credentials must be removed');
    check(strpos(file_get_contents($directory.'/mysqldump.stderr'),'RELOAD privilege required') !== false,'Diagnostics must survive a failed dump');
    check(strpos(file_get_contents($directory.'/mysqldump.stderr'),'private-test-secret') === false && (fileperms($directory.'/mysqldump.stderr') & 0777) === 0600,'Diagnostic logs must be private and redact secrets');
    file_put_contents($script, <<<'PHP'
<?php
$defaults = substr($argv[1],strlen('--defaults-extra-file='));
if ((fileperms($defaults) & 0777) !== 0600 || strpos(file_get_contents($defaults),'private-test-secret') === false) { exit(3); }
foreach (array('--skip-lock-all-tables','--lock-tables','--routines','--events','--triggers','--databases','asterisk') as $option) { if (!in_array($option,$argv,true)) { exit(4); } }
foreach (array('--lock-all-tables','--skip-lock-tables','--single-transaction','--all-databases') as $option) { if (in_array($option,$argv,true)) { fwrite(STDERR,'Unexpected global or unlocked dump'); exit(6); } }
if (strpos(implode(' ',$argv),'private-test-secret') !== false) { exit(5); }
fwrite(STDOUT,'-- complete fixture SQL dump');
fwrite(STDERR,'fixture warning');
PHP
    );
    deploy_mysql_backup($config,$directory,array(PHP_BINARY,$script));
    check(file_get_contents($directory.'/database.sql') === '-- complete fixture SQL dump','Successful SQL output must be retained with all consistency options');
    check((fileperms($directory.'/database.sql') & 0777) === 0600 && !glob($directory.'/mysql-*'),'Successful backup must also remove credentials and protect SQL');
    check(file_get_contents($directory.'/mysqldump.stderr') === 'fixture warning','Warnings from a successful dump must be retained');
    unlink($directory.'/database.sql');
    file_put_contents($script,'<?php exit(0);');
    rejects(function () use ($config,$directory,$script) { deploy_mysql_backup($config,$directory,array(PHP_BINARY,$script)); },'Empty database backup');
    check(!file_exists($directory.'/database.sql') && !glob($directory.'/mysql-*'),'Empty dumps must be rejected and cleaned up');
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
scenario('inspection compares a hybrid install without a base or native operations',function ($f) {
    global $installedRows;
    $installedRows['core']['version'] = '0.12.0';
    $f->bridge->pending = true;
    $f->bridge->failBackup = true;
    $root = $f->root.'/web/admin/modules';
    file_put_contents($root.'/core/functions.inc.php',"<?php return 'local hybrid edit';\n");
    file_put_contents($root.'/core/local-only.php',"<?php return 'preserve local';\n");
    file_put_contents($root.'/core/local-binary',"\x00\x01\x02");
    chmod($root.'/core/module.xml',0600);
    $f->file('core/functions.inc.php',"<?php return 'dirty clone, not target';\n");
    $before = Files::tree($root);
    $stateBefore = Files::tree($f->root.'/state');
    $output = $f->root.'/inspection';
    $result = (new IssabelDeploy\Inspector())->inspect($f->repo,$f->target,array('core','sipsettings'),$root,$output);
    check(Files::tree($root) === $before && $f->bridge->reloads === 0 && $f->bridge->installed === array(),'Inspection must not change installed files or use native operations');
    check(Files::tree($f->root.'/state') === $stateBefore,'Inspection must not write deployment state');
    $report = json_decode(file_get_contents($result['report']),true);
    check($report['database_checked'] === false && $result['versions']['core']['installed_file_version'] === '1.0.0','Inspection must distinguish file version from database checks');
    $differences = array_column($report['modules']['core']['differences'],null,'path');
    check($differences['functions.inc.php']['status'] === 'content_changed','Local hybrid edits must be reported');
    check($differences['local-only.php']['status'] === 'installed_only','Installed-only files must be reported');
    check($differences['pjsip.functions.php']['status'] === 'target_only','Target additions must be reported');
    check($differences['module.xml']['status'] === 'permissions_changed','Permission differences must be reported');
    $diff = file_get_contents($result['diff']);
    check(strpos($diff,"-<?php return 'local hybrid edit';") !== false && strpos($diff,"+<?php return 'new';") !== false,'Diff direction must be installed to target');
    check(strpos($diff,'dirty clone') === false,'Inspection must read the committed target');
    check((fileperms($output)&0777) === 0700 && (fileperms($result['diff'])&0077) === 0,'Reports must be private');
    check(!file_exists($output.'/.compare'),'Diff staging contents must be removed');
    rejects(function () use ($f,$root,$output) { (new IssabelDeploy\Inspector())->inspect($f->repo,$f->target,array('core'),$root,$output); },'already exists');
});
scenario('interactive declines every update without writing PBX files',function ($f) {
    $before = Files::tree($f->root.'/web');
    $result = (new IssabelDeploy\InteractiveUpdater($f->bridge,$f->root.'/state'))->update($f->repo,function ($question) { return 'n'; },function ($text) {});
    check($result['status'] === 'unchanged' && Files::tree($f->root.'/web') === $before && $f->bridge->reloads === 0,'Declining interactive updates must leave the PBX alone');
});
scenario('interactive includes approved pending changes in the one final reload',function ($f) {
    $f->bridge->pending = true;
    $questions = array();
    $question = function ($text) use (&$questions) {
        $questions[] = $text;
        return strpos($text,'¿Incluir esos cambios') === 0 || strpos($text,'¿Actualizar core?') === 0 || strpos($text,'¿Aplicar') === 0 ? 's' : 'n';
    };
    $result = (new IssabelDeploy\InteractiveUpdater($f->bridge,$f->root.'/state'))->update($f->repo,$question,function ($text) {});
    check($result['status'] === 'complete' && $f->bridge->reloads === 1 && !$f->bridge->pending,'Approved pending changes must use the single final reload');
    check(strpos($questions[0],'¿Incluir esos cambios') === 0,'Pending changes must be disclosed before module selection');
    $record = json_decode(file_get_contents($result['backup'].'/record.json'),true);
    check($record['plan']['allow_pending_reload'] === true,'Pending-change approval must be recorded in the signed plan');
});
scenario('interactive declining pending changes cancels without writes',function ($f) {
    $f->bridge->pending = true;
    $before = Files::tree($f->root.'/web');
    $result = (new IssabelDeploy\InteractiveUpdater($f->bridge,$f->root.'/state'))->update($f->repo,function ($text) { return 'n'; },function ($text) {});
    check($result['status'] === 'cancelled' && Files::tree($f->root.'/web') === $before && $f->bridge->reloads === 0,'Declined pending changes must not be applied or written');
});
scenario('interactive rejects newly pending changes that were not approved',function ($f) {
    $question = function ($text) use ($f) {
        if (strpos($text,'¿Aplicar') === 0) { $f->bridge->pending = true; return 's'; }
        return strpos($text,'¿Actualizar core?') === 0 ? 's' : 'n';
    };
    rejects(function () use ($f,$question) { (new IssabelDeploy\InteractiveUpdater($f->bridge,$f->root.'/state'))->update($f->repo,$question,function ($text) {}); },'pending changes');
    check($f->bridge->reloads === 0,'Unapproved pending changes must abort before reload');
});
scenario('interactive accepts local changes without requiring an installed commit',function ($f) {
    $file = $f->root.'/web/admin/modules/core/functions.inc.php';
    file_put_contents($file,"<?php return 'my local modification';\n");
    $question = function ($text) { return strpos($text,'¿Actualizar core?') === 0 || strpos($text,'¿Aplicar') === 0 ? 's' : 'n'; };
    $result = (new IssabelDeploy\InteractiveUpdater($f->bridge,$f->root.'/state'))->update($f->repo,$question,function ($text) {});
    check($result['status'] === 'complete' && $f->bridge->reloads === 1,'Interactive patch must reload once');
    check(file_get_contents($file) === "<?php return 'new';\n",'Accepted local code must be replaced with the selected commit');
    check(file_exists($f->root.'/web/admin/modules/core/remove.php'),'Unknown installed-only files must be retained');
    $f->deployer->rollback($result['deployment']);
    check(file_get_contents($file) === "<?php return 'my local modification';\n",'Rollback must restore the actual local baseline');
});
scenario('interactive catches edits made during confirmation',function ($f) {
    $file = $f->root.'/web/admin/modules/core/functions.inc.php';
    $question = function ($text) use ($file) {
        if (strpos($text,'¿Aplicar') === 0) { file_put_contents($file,'operator changed it'); return 's'; }
        return strpos($text,'¿Actualizar core?') === 0 ? 's' : 'n';
    };
    rejects(function () use ($f,$question) { (new IssabelDeploy\InteractiveUpdater($f->bridge,$f->root.'/state'))->update($f->repo,$question,function ($text) {}); },'conflict');
    check(file_get_contents($file) === 'operator changed it' && $f->bridge->reloads === 0,'Late interactive edits must not be overwritten');
});
scenario('interactive reruns changed installers at the same version',function ($f) {
    $f->file('core/install.php',"<?php return true;\n"); $f->target = $f->commit();
    $answers = array('t','s');
    $result = (new IssabelDeploy\InteractiveUpdater($f->bridge,$f->root.'/state'))->update($f->repo,function ($text) use (&$answers) { return array_shift($answers) ?? 'n'; },function ($text) {});
    check($result['status'] === 'complete' && $f->bridge->installRequests[0]['reinstall'] === true,'Changed same-version installer must be explicitly reinstalled');
    rejects(function () use ($f,$result) { $f->deployer->rollback($result['deployment']); },'Migration started');
});
scenario('interactive all omits and reports uninstalled modules before and after selection',function ($f) {
    foreach (array('aaa','feature') as $module) {
        $f->file($module.'/module.xml','<module><rawname>'.$module.'</rawname><version>1.0.0</version></module>');
        $f->file($module.'/install.php',"<?php return true;\n");
    }
    // Extracted package files do not mean that a module is registered as installed.
    mkdir($f->root.'/web/admin/modules/aaa');
    file_put_contents($f->root.'/web/admin/modules/aaa/local.txt','keep');
    $f->target = $f->commit();
    $questions = array(); $output = '';
    $result = (new IssabelDeploy\InteractiveUpdater($f->bridge,$f->root.'/state'))->update($f->repo,function ($text) use (&$questions) {
        $questions[] = $text;
        return strpos($text,'¿Aplicar') === 0 ? 's' : 't';
    },function ($text) use (&$output) { $output .= $text; });
    check($result['status'] === 'complete' && $f->bridge->reloads === 1,'All installed modules must still be updated in one reload');
    check(!is_dir($f->root.'/web/admin/modules/feature') && !file_exists($f->root.'/web/admin/modules/aaa/module.xml'),'All must never copy uninstalled module packages');
    check($f->bridge->installed === array(),'All must never run installers for absent modules');
    check(count($questions) === 2 && strpos($questions[0],'¿Actualizar core?') === 0,'Uninstalled modules must not be offered individually');
    check(strpos($output,'no están instalados (omitidos): aaa, feature') !== false,'The final output must list absent modules');
});
scenario('interactive reports absent modules even when no updates are selected',function ($f) {
    $f->file('feature/module.xml','<module><rawname>feature</rawname><version>1.0.0</version></module>'); $f->target = $f->commit();
    $output = '';
    $result = (new IssabelDeploy\InteractiveUpdater($f->bridge,$f->root.'/state'))->update($f->repo,function ($text) { return 'n'; },function ($text) use (&$output) { $output .= $text; });
    check($result['status'] === 'unchanged' && strpos($output,'no están instalados (omitidos): feature') !== false,'Absent modules must also be reported without a deployment');
});
scenario('interactive installs an absent dependency only after explicit approval',function ($f) {
    $f->file('feature/module.xml','<module><rawname>feature</rawname><version>1.0.0</version></module>');
    $f->file('feature/install.php',"<?php return true;\n");
    $xml = file_get_contents($f->repo.'/core/module.xml');
    $f->file('core/module.xml',str_replace('</depends>','<module>feature ge1.0</module></depends>',$xml)); $f->target = $f->commit();
    $updater = new IssabelDeploy\InteractiveUpdater($f->bridge,$f->root.'/state');
    $declined = $updater->update($f->repo,function ($text) { return strpos($text,'¿Incluir feature?') === 0 ? 'n' : 't'; },function ($text) {});
    check($declined['status'] === 'cancelled' && !is_dir($f->root.'/web/admin/modules/feature') && $f->bridge->reloads === 0,'All must not approve missing dependencies implicitly');
    $question = function ($text) { return strpos($text,'¿Actualizar core?') === 0 || strpos($text,'¿Incluir feature?') === 0 || strpos($text,'¿Aplicar') === 0 ? 's' : 'n'; };
    $result = (new IssabelDeploy\InteractiveUpdater($f->bridge,$f->root.'/state'))->update($f->repo,$question,function ($text) {});
    check($result['status'] === 'complete' && file_exists($f->root.'/web/admin/modules/feature/module.xml'),'New module must be copied and installed');
    check($f->bridge->installed === array('feature') && $f->bridge->reloads === 1,'New module must use the native installer and one reload');
});
scenario('native validation accepts compact version operators and rejects incompatible versions',function ($f) {
    global $installedRows,$externalMetadata;
    $installedRows['timeconditions'] = array('version'=>'2.12.0.0','enabled'=>true);
    $bulk = array('rawname'=>'bulkextensions','version'=>'2.13.0.0','depends'=>array('version'=>'ge2.11','module'=>array('timeconditions ge2.6')));
    $framework = array('rawname'=>'framework','version'=>'2.13.0.0');
    $validation = deploy_targets(array($bulk,$framework),true);
    check($validation['order'] === array('framework','bulkextensions'),'Compact PBX and module requirements must accept compatible framework upgrades');
    $bulk['depends']['version'] = 'GE 2.11';
    check(deploy_targets(array($bulk,$framework),true)['order'] === $validation['order'],'Spaced and uppercase native version operators must also work');
    $bulk['depends']['version'] = 'lt2.11';
    rejects(function () use ($bulk,$framework) { deploy_targets(array($bulk,$framework),true); },'Target framework');
    $bulk['depends']['version'] = 'ge2.11'; $bulk['depends']['module'] = array('timeconditions gt9.0');
    rejects(function () use ($bulk,$framework) { deploy_targets(array($bulk,$framework),true); },'Dependencies failed');
    $bulk['depends']['version'] = 'invalid'; $bulk['depends']['module'] = array();
    rejects(function () use ($bulk,$framework) { deploy_targets(array($bulk,$framework),true); },'Target framework');
    $externalMetadata['other'] = array('depends'=>array('module'=>array('framework lt2.13')));
    $installedRows['other'] = array('version'=>'1.0','enabled'=>true);
    rejects(function () use ($framework) { deploy_targets(array($framework),true); },'break installed module other');
});
scenario('native access checks allow missing interactive packages and identify inaccessible paths',function ($f) {
    global $amp_conf;
    $amp_conf = array('AMPWEBROOT'=>$f->root.'/web','ASTETCDIR'=>$f->root.'/etc');
    try {
        $framework = array('rawname'=>'framework','version'=>'2.13.0.0');
        check(deploy_targets(array($framework),true)['order'] === array('framework'),'A registered framework with no package directory must be prepared by interactive deployment');
        $core = array('rawname'=>'core','version'=>'1.0.0');
        rename($f->root.'/web/admin/modules/core',$f->root.'/web/admin/modules/core-saved');
        rejects(function () use ($core) { deploy_targets(array($core)); },'el módulo core: '.$f->root.'/web/admin/modules/core');
        // An existing bad destination must not be treated as an absent package.
        file_put_contents($f->root.'/web/admin/modules/core','not a directory');
        rejects(function () use ($core) { deploy_targets(array($core),true); },'el módulo core:');
        unlink($f->root.'/web/admin/modules/core');
        symlink($f->root.'/missing',$f->root.'/web/admin/modules/core');
        rejects(function () use ($core) { deploy_targets(array($core),true); },'el módulo core:');
        unlink($f->root.'/web/admin/modules/core');
        rename($f->root.'/web/admin/modules/core-saved',$f->root.'/web/admin/modules/core');
        $amp_conf['ASTETCDIR'] = $f->root.'/missing-config';
        rejects(function () use ($core) { deploy_targets(array($core),true); },'la configuración Asterisk: '.$f->root.'/missing-config');
        rejects(function () use ($core) { deploy_targets(array($core),true); },'uid '.posix_geteuid());
        check(!file_exists($amp_conf['ASTETCDIR']),'Access validation must not create directories or change permissions');
    } finally { unset($GLOBALS['amp_conf']); }
});
scenario('interactive compact PBX requirement asks to include the framework upgrade',function ($f) {
    global $installedRows;
    $installedRows['framework']['version'] = '2.12.0.0'; $f->bridge->pbxVersion = '2.12.0.0';
    mkdir($f->root.'/web/admin/modules/framework');
    $f->file('framework/module.xml','<module><rawname>framework</rawname><version>2.13.0.0</version></module>');
    $f->file('framework/install.php',"<?php return true;\n");
    $f->file('core/module.xml','<module><rawname>core</rawname><version>1.0.0</version><depends><version>ge2.13</version></depends></module>');
    $f->target = $f->commit();
    $included = false;
    $result = (new IssabelDeploy\InteractiveUpdater($f->bridge,$f->root.'/state'))->update($f->repo,function ($text) use (&$included) {
        if (strpos($text,'¿Incluir framework?') === 0) { $included = true; return 's'; }
        return strpos($text,'¿Actualizar core?') === 0 || strpos($text,'¿Aplicar') === 0 ? 's' : 'n';
    },function ($text) {});
    check($result['status'] === 'complete' && $included,'Compact PBX version requirements must prompt for an unselected framework upgrade');
    check($f->bridge->installed === array('framework') && $f->bridge->reloads === 1,'The approved framework upgrade must finish before the one final reload');
});
scenario('interactive upgrades disabled modules without enabling them',function ($f) {
    global $installedRows;
    $installedRows['core']['enabled'] = false;
    $f->upgrade();
    $question = function ($text) { return strpos($text,'¿Actualizar core?') === 0 || strpos($text,'¿Aplicar') === 0 ? 's' : 'n'; };
    $result = (new IssabelDeploy\InteractiveUpdater($f->bridge,$f->root.'/state'))->update($f->repo,$question,function ($text) {});
    check($result['status'] === 'complete' && $installedRows['core']['enabled'] === false,'Disabled modules must remain disabled');
    check($f->bridge->installRequests[0]['reinstall'] === true,'Disabled module installs must not be silently skipped by native status checks');
});
scenario('interactive framework upgrade uses its native payload and cleans staging',function ($f) {
    global $installedRows;
    $f->upgrade();
    $installedRows['framework']['version'] = '2.12.0.0'; $f->bridge->pbxVersion = '2.12.0.0';
    mkdir($f->root.'/web/admin/modules/framework');
    file_put_contents($f->root.'/web/admin/modules/framework/module.xml','<module><rawname>framework</rawname><version>2.12.0.0</version></module>');
    file_put_contents($f->root.'/etc/manager.conf','keep production credentials');
    $f->file('framework/module.xml','<module><rawname>framework</rawname><version>2.13.0.0</version></module>');
    $f->file('framework/install.php',"<?php return true;\n");
    $f->file('framework/libissabelpbx.install.php',"<?php return true;\n");
    $f->file('framework/upgrades/2.13.0.0/migration.php',"<?php return true;\n");
    $f->file('framework/amp_conf/htdocs/admin/framework-marker.php',"<?php return 'framework target';\n");
    $f->file('framework/amp_conf/bin/helper','framework bin');
    $f->file('framework/amp_conf/agi-bin/helper.agi','framework agi');
    $f->file('framework/amp_conf/astetc/manager.conf','do not deploy template');
    $f->target = $f->commit();
    $answers = array('t','s');
    $updater = new IssabelDeploy\InteractiveUpdater($f->bridge,$f->root.'/state');
    $result = $updater->update($f->repo,function ($text) use (&$answers) { return array_shift($answers) ?? 'n'; },function ($text) {});
    check($result['status'] === 'complete' && $f->bridge->installed === array('framework','core','sipsettings'),'Framework must be installed before dependent modules');
    check(file_exists($f->root.'/web/admin/framework-marker.php') && file_exists($f->root.'/bin/helper') && file_exists($f->root.'/agi/helper.agi'),'Native framework payload must reach its deployed destinations');
    check(file_get_contents($f->root.'/etc/manager.conf') === 'keep production credentials','Framework configuration templates must not replace active credentials');
    check(!is_dir($f->root.'/web/admin/modules/framework/amp_conf'),'Framework installer may remove its packaging');
    check($f->bridge->reloads === 1,'Combined framework/module update must reload only once');
    $again = $updater->update($f->repo,function ($text) { throw new RuntimeException('Unchanged source should not ask for updates'); },function ($text) {});
    check($again['status'] === 'unchanged','Framework packaging cleanup must not cause repeated updates');
    $f->bridge->pbxVersion = '2.12.0.0';
    $repair = $updater->update($f->repo,function ($text) { return 's'; },function ($text) {});
    check($repair['status'] === 'complete' && $f->bridge->pbxVersion === '2.13.0.0','Hybrid framework must run outstanding PBX migrations even when its module version was changed manually');
    check(end($f->bridge->installRequests)['reinstall'] === true,'Same-version framework migration must invoke the installer');
});
scenario('framework preserves native copy modes and recovers legacy post-check failures',function ($f) {
    $file = $f->root.'/bin/libissabelpbx.confgen.php';
    file_put_contents($file,"<?php return 'old';\n"); chmod($file,0775);
    $f->file('framework/module.xml','<module><rawname>framework</rawname><version>2.13.0.0</version></module>');
    $f->file('framework/install.php',"<?php return true;\n");
    $bytes = "<?php return 'target';\n";
    $f->file('framework/amp_conf/bin/libissabelpbx.confgen.php',$bytes); $f->target = $f->commit();
    // Exercise real cp too: its overwrite behavior differs from writing a new file.
    run(array('cp','-f',$f->repo.'/framework/amp_conf/bin/libissabelpbx.confgen.php',$file));
    check((Files::snapshot($file)['mode'] & 0111) !== 0,'Real cp must preserve executable destination permissions');
    file_put_contents($file,"<?php return 'old';\n");
    $updater = new IssabelDeploy\InteractiveUpdater($f->bridge,$f->root.'/state');
    $result = $updater->update($f->repo,function ($text) { return strpos($text,'¿Aplicar') === 0 ? 's' : 't'; },function ($text) {});
    check($result['status'] === 'complete' && file_get_contents($file) === $bytes && Files::snapshot($file)['mode'] === 0775,'Framework copy must preserve installed modes while verifying target content');
    check($updater->update($f->repo,function ($text) { throw new RuntimeException('Unchanged copy modes must not trigger another update'); },function ($text) {})['status'] === 'unchanged','Preserved executable modes must not cause repeated updates');
    $directory = $result['backup']; $path = $directory.'/record.json';
    $record = json_decode(file_get_contents($path),true);
    // Reproduce the signed plan and journal produced by the older verifier.
    foreach ($record['plan']['changes'] as &$change) {
        if ($change['path'] === '@amp_conf/bin/libissabelpbx.confgen.php') { $change['after']['mode'] = 0644; }
    }
    unset($change);
    unset($record['plan']['signature']);
    $record['plan']['signature'] = hash_hmac('sha256',json_encode($record['plan'],JSON_THROW_ON_ERROR),file_get_contents($f->root.'/state/key'));
    $record['status'] = 'recovery-required'; $record['error'] = 'Post-deployment file mismatch: @amp_conf/bin/libissabelpbx.confgen.php';
    file_put_contents($path,json_encode($record));
    file_put_contents($f->root.'/state/current.json',json_encode($record['plan']['previous']));
    file_put_contents($file,'unexpected contents');
    rejects(function () use ($f,$result) { $f->deployer->recover($result['deployment']); },'Post-deployment file mismatch');
    check(json_decode(file_get_contents($path),true)['status'] === 'recovery-required','Content mismatch must leave recovery blocked');
    file_put_contents($file,$bytes); chmod($file,0644);
    rejects(function () use ($f,$result) { $f->deployer->recover($result['deployment']); },'Post-deployment file mismatch');
    chmod($file,0775); $f->bridge->pending = true;
    rejects(function () use ($f,$result) { $f->deployer->recover($result['deployment']); },'pending changes');
    $f->bridge->pending = false; $f->bridge->failHealth = true;
    rejects(function () use ($f,$result) { $f->deployer->recover($result['deployment']); },'health failure');
    $f->bridge->failHealth = false;
    $installs = $f->bridge->installed; $reloads = $f->bridge->reloads;
    $recovered = $f->deployer->recover($result['deployment']);
    check($recovered['status'] === 'complete' && $f->bridge->installed === $installs && $f->bridge->reloads === $reloads,'Verified recovery must not rerun installers or reload');
    check(is_file($directory.'/record-before-recovery.json') && isset(json_decode(file_get_contents($path),true)['recovery']),'Recovery must preserve the original error and record its verification');
    check(json_decode(file_get_contents($f->root.'/state/current.json'),true)['framework']['deployment'] === $result['deployment'],'Recovery must update deployment history');
    rejects(function () use ($f,$result) { $f->deployer->recover($result['deployment']); },'only available');
});
scenario('inspection never follows local symlink directories',function ($f) {
    $root = $f->root.'/web/admin/modules';
    mkdir($f->root.'/outside');
    file_put_contents($f->root.'/outside/secret','not included in report');
    symlink($f->root.'/outside',$root.'/core/external');
    mkdir($root.'/core/pjsip.functions.php');
    file_put_contents($root.'/core/pjsip.functions.php/local.txt','local contents');
    symlink('/root/never-open-source-link',$f->repo.'/core/absolute-source-link');
    $f->file('core/external/secret','target code'); $f->target = $f->commit();
    $result = (new IssabelDeploy\Inspector())->inspect($f->repo,$f->target,array('core'),$root,$f->root.'/links-inspection');
    $report = json_decode(file_get_contents($result['report']),true);
    $diffs = array_column($report['modules']['core']['differences'],null,'path');
    check($diffs['external/secret']['status'] === 'blocked_by_parent','Source path through a local symlink must be blocked');
    check($diffs['pjsip.functions.php']['status'] === 'type_changed','Directory/file type conflicts must be reported');
    check($diffs['absolute-source-link']['target']['type'] === 'link','Inspection must compare source link bytes without following them');
    check(strpos(file_get_contents($result['diff']),'not included in report') === false,'External linked contents must never be read');
});
scenario('inspection supports missing modules and limits large diffs',function ($f) {
    $root = $f->root.'/web/admin/modules';
    removeTree($root.'/sipsettings');
    $result = (new IssabelDeploy\Inspector())->inspect($f->repo,$f->target,array('core','sipsettings'),$root,$f->root.'/missing-inspection',10);
    check($result['versions']['sipsettings']['installed_file_version'] === null,'Missing module must have no installed version');
    check(strpos(file_get_contents($result['diff']),'exceeds the size limit') !== false,'Large file diffs must be bounded');
    rejects(function () use ($f,$root) { (new IssabelDeploy\Inspector())->inspect($f->repo,$f->target,array('core'),$root,$f->root.'/web/public-report'); },'outside');
    rejects(function () use ($f,$root) { (new IssabelDeploy\Inspector())->inspect($f->repo,$f->target,array('core'),$root,$f->repo.'/private-report'); },'outside');
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
