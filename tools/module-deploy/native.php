<?php
// Deliberately separate processes: modules/installers can define globals or exit.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require __DIR__.'/Deploy.php';
ob_start();
$answered = false;
register_shutdown_function(function () use (&$answered) {
    if (!$answered) {
        while (ob_get_level()) { ob_end_clean(); }
        echo json_encode(array('ok'=>false,'error'=>'Native Issabel operation terminated unexpectedly. Check PBX logs; deployment backups are retained.'))."\n";
    }
});
function deploy_reply(array $value): void {
    global $answered, $amp_conf;
    if (isset($value['error'])) {
        foreach (array('AMPDBPASS','AMPMGRPASS') as $secret) {
            if (!empty($amp_conf[$secret])) { $value['error'] = str_replace($amp_conf[$secret],'[redacted]',$value['error']); }
        }
    }
    $output = ob_get_contents();
    while (ob_get_level()) { ob_end_clean(); }
    $answered = true;
    echo json_encode(array_merge(array('ok'=>true,'output'=>$output),$value), JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR)."\n";
}
require __DIR__.'/NativeSupport.php';

try {
    $action = $argv[1] ?? '';
    $bootstrap = $argv[2] ?? '';
    if (!in_array($action,array('inspect','validate','backup-check','backup','install','reload','health','permissions'),true)) { throw new RuntimeException('Invalid native action.'); }
    if (in_array($action,array('inspect','backup-check','backup','permissions'),true) && posix_geteuid() !== 0) { throw new RuntimeException('Root is required for inspection/backups/permissions.'); }
    $input = json_decode(stream_get_contents(STDIN),true,512,JSON_THROW_ON_ERROR);
    $bootstrap_settings = array('issabelpbx_auth'=>false, 'issabelpbx_error_handler'=>false);
    $restrict_mods = !in_array($action,array('install','reload'),true);
    if (!is_file($bootstrap)) { throw new RuntimeException('Bootstrap not found.'); }
    require $bootstrap;
    if (function_exists('ipbx_framework_autoloader')) { ipbx_framework_autoloader(true); }
    elseif (function_exists('fpbx_framework_autoloader')) { fpbx_framework_autoloader(true); }
    deploy_db();
    deploy_manager();
    if ($action === 'inspect') {
        $account = posix_getpwnam($amp_conf['AMPASTERISKUSER']);
        if (!$account || $account['uid'] === 0) { throw new RuntimeException('Configured Asterisk account must exist and be non-root.'); }
        $group = isset($amp_conf['AMPASTERISKGROUP']) ? posix_getgrnam($amp_conf['AMPASTERISKGROUP']) : posix_getgrgid($account['gid']);
        if (!$group) { throw new RuntimeException('Configured Asterisk group was not found.'); }
        $engine = engine_getinfo();
        deploy_reply(array('modules_dir'=>$amp_conf['AMPWEBROOT'].'/admin/modules','config_dir'=>$amp_conf['ASTETCDIR'],
            'webroot'=>realpath($amp_conf['AMPWEBROOT']), 'user'=>$account['name'],'uid'=>$account['uid'],'gid'=>$group['gid'],
            'php_version'=>PHP_VERSION, 'asterisk_version'=>$engine['version'],'pbx_version'=>getversion(),
            'bootstrap_sha256'=>hash_file('sha256',$bootstrap),
            'pending_reload'=>sql("SELECT value FROM admin WHERE variable='need_reload'",'getOne') === 'true', 'modules'=>deploy_modules()));
    } elseif ($action === 'validate') {
        deploy_reply(deploy_targets($input['targets']));
    } elseif ($action === 'backup-check') {
        deploy_reply(deploy_backup_check());
    } elseif ($action === 'backup') {
        $check = deploy_backup_check();
        $directory = realpath($input['directory']);
        if (!$directory || (stat($directory)['mode'] & 0077) || stat($directory)['uid'] !== 0) { throw new RuntimeException('Backup directory must be root-owned and private.'); }
        deploy_sqlite_backup($check['astdb'],$directory.'/astdb.sqlite3');
        if ($amp_conf['AMPDBENGINE'] === 'sqlite3') {
            deploy_sqlite_backup($amp_conf['AMPDBFILE'],$directory.'/database.sqlite3');
        } else {
            $defaults = tempnam($directory,'mysql-');
            try {
                $quote = function ($value) { return '"'.str_replace(array('\\','"',"\n","\r"),array('\\\\','\\"','\\n','\\r'),(string)$value).'"'; };
                file_put_contents($defaults,"[client]\nuser=".$quote($amp_conf['AMPDBUSER'])."\npassword=".$quote($amp_conf['AMPDBPASS'])."\nhost=".$quote($amp_conf['AMPDBHOST'])."\n");
                chmod($defaults,0600);
                // Lock all tables: this also covers legacy MyISAM installations.
                $output = fopen($directory.'/database.sql','xb');
                $errors = tmpfile();
                $process = proc_open(array('mysqldump','--defaults-extra-file='.$defaults,'--lock-all-tables','--hex-blob','--routines','--events','--triggers','--databases',$amp_conf['AMPDBNAME']),array(0=>array('file','/dev/null','r'),1=>$output,2=>$errors),$pipes);
                if (!is_resource($process) || proc_close($process) !== 0) { throw new RuntimeException('mysqldump failed. Verify backup privileges; no files have been deployed.'); }
                fclose($output);
                fclose($errors);
                if (filesize($directory.'/database.sql') === 0) { throw new RuntimeException('Empty database backup.'); }
            } finally { unlink($defaults); }
        }
        \IssabelDeploy\run(array('tar','--acls','--xattrs','--selinux','-cpf',$directory.'/asterisk-config.tar','-C',$amp_conf['ASTETCDIR'],'.'));
        foreach ($input['modules'] as $name=>$path) {
            if (!preg_match('/^[a-z][a-z0-9_-]*$/D',$name) || realpath($amp_conf['AMPWEBROOT'].'/admin/modules/'.$name) !== $path) { throw new RuntimeException('Module backup path mismatch.'); }
            \IssabelDeploy\run(array('tar','--acls','--xattrs','--selinux','-cpf',$directory.'/module-'.$name.'.tar','-C',$path,'.'));
        }
        $manifest = array();
        foreach (glob($directory.'/*') as $path) {
            if (is_file($path) && basename($path) !== 'record.json') { $manifest[basename($path)] = hash_file('sha256',$path); }
        }
        file_put_contents($directory.'/backup-checksums.json',json_encode($manifest,JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        deploy_reply(array('files'=>array_keys($manifest)));
    } elseif ($action === 'permissions') {
        if (file_exists('/sys/fs/selinux/enforce')) {
            foreach ($input['paths'] as $path) {
                $contained = false;
                foreach ($input['modules'] as $module=>$root) {
                    if (realpath($amp_conf['AMPWEBROOT'].'/admin/modules/'.$module) === $root && strpos($path,$root.'/') === 0) { $contained = true; }
                }
                if (!$contained) { throw new RuntimeException('Invalid permission target.'); }
                if (file_exists($path) || is_link($path)) { \IssabelDeploy\run(array('restorecon','--',$path)); }
            }
        }
        deploy_reply(array('status'=>'permissions-checked'));
    } elseif ($action === 'install') {
        if (!preg_match('/^[a-z][a-z0-9_-]*$/D',$input['module']) || $input['module'] === 'framework') { throw new RuntimeException('Invalid module.'); }
        $result = module_install($input['module'],false);
        if ($result !== true) { throw new RuntimeException('Native module install failed: '.strip_tags(implode('; ',(array)$result))); }
        deploy_reply(array('module'=>$input['module']));
    } elseif ($action === 'reload') {
        $astman = new DeployManagerProxy($astman);
        needreload();
        $result = do_reload();
        if (empty($result['status']) || !empty($result['num_errors']) ||
            strtolower($astman->reloadResponse['Response'] ?? '') !== 'success') {
            needreload();
            throw new RuntimeException('Configuration regeneration/reload failed. Check PBX notifications and retrieve_conf logs.');
        }
        deploy_manager();
        deploy_reply(array('status'=>'success'));
    } else {
        $installed = deploy_modules();
        foreach ($input['targets'] as $target) {
            if (($installed[$target['rawname']]['version'] ?? null) !== $target['version'] || empty($installed[$target['rawname']]['enabled'])) { throw new RuntimeException('Installed version/state check failed: '.$target['rawname']); }
        }
        deploy_reply(array('status'=>'healthy'));
    }
} catch (Throwable $error) { deploy_reply(array('ok'=>false,'error'=>$error->getMessage())); }
