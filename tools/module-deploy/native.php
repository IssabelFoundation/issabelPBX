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
    if (!chdir($amp_conf['AMPWEBROOT'].'/admin')) { throw new RuntimeException('Cannot enter the installed admin directory.'); }
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
            'bin_dir'=>realpath($amp_conf['AMPBIN']),'agi_dir'=>realpath($asterisk_conf['astagidir'] ?? $amp_conf['ASTAGIDIR']),
            'webroot'=>realpath($amp_conf['AMPWEBROOT']), 'user'=>$account['name'],'uid'=>$account['uid'],'gid'=>$group['gid'],
            'php_version'=>PHP_VERSION, 'asterisk_version'=>$engine['version'],'pbx_version'=>getversion(),
            'bootstrap_sha256'=>hash_file('sha256',$bootstrap),
            'pending_reload'=>sql("SELECT value FROM admin WHERE variable='need_reload'",'getOne') === 'true', 'modules'=>deploy_modules()));
    } elseif ($action === 'validate') {
        deploy_reply(deploy_targets($input['targets'],!empty($input['interactive'])));
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
            deploy_mysql_backup($amp_conf,$directory);
        }
        \IssabelDeploy\run(array('tar','--acls','--xattrs','--selinux','-cpf',$directory.'/asterisk-config.tar','-C',$amp_conf['ASTETCDIR'],'.'));
        foreach ($input['modules'] as $name=>$path) {
            if (!isset(deploy_modules()[$name]) && !file_exists($path)) { continue; }
            if (!preg_match('/^[a-z][a-z0-9_-]*$/D',$name) || realpath($amp_conf['AMPWEBROOT'].'/admin/modules/'.$name) !== $path) { throw new RuntimeException('Module backup path mismatch.'); }
            \IssabelDeploy\run(array('tar','--acls','--xattrs','--selinux','-cpf',$directory.'/module-'.$name.'.tar','-C',$path,'.'));
        }
        if (isset($input['modules']['framework'])) {
            foreach (array('web'=>$amp_conf['AMPWEBROOT'],'bin'=>$amp_conf['AMPBIN'],'agi'=>$asterisk_conf['astagidir'] ?? $amp_conf['ASTAGIDIR']) as $label=>$root) {
                \IssabelDeploy\run(array('tar','--acls','--xattrs','--selinux','-cpf',$directory.'/framework-'.$label.'.tar','-C',$root,'.'));
            }
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
                if (!empty($input['interactive']) && isset($input['modules']['framework'])) {
                    foreach (array($amp_conf['AMPWEBROOT'],$amp_conf['AMPBIN'],$asterisk_conf['astagidir'] ?? $amp_conf['ASTAGIDIR']) as $root) {
                        $root = realpath($root);
                        if ($root && strpos($path,$root.'/') === 0) { $contained = true; }
                    }
                }
                if (!$contained) { throw new RuntimeException('Invalid permission target.'); }
                if (file_exists($path) || is_link($path)) { \IssabelDeploy\run(array('restorecon','--',$path)); }
            }
        }
        deploy_reply(array('status'=>'permissions-checked'));
    } elseif ($action === 'install') {
        if (!preg_match('/^[a-z][a-z0-9_-]*$/D',$input['module']) || ($input['module'] === 'framework' && empty($input['interactive']))) { throw new RuntimeException('Invalid module.'); }
        $force = !empty($input['interactive']) && !empty($input['reinstall']);
        if ($force) {
            $info = module_getinfo($input['module'],false,true);
            $errors = module_checkdepends($info[$input['module']]);
            if (is_array($errors) && $errors) { throw new RuntimeException('Dependency check failed before reinstall: '.strip_tags(implode('; ',$errors))); }
        }
        $result = module_install($input['module'],$force);
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
            $expectedEnabled = $input['enabled'][$target['rawname']] ?? true;
            if (($installed[$target['rawname']]['version'] ?? null) !== $target['version'] || $installed[$target['rawname']]['enabled'] !== $expectedEnabled) { throw new RuntimeException('Installed version/state check failed: '.$target['rawname']); }
            if ($target['rawname'] === 'framework' && preg_match('/^(\d+\.\d+)/',$target['version'],$branch) && version_compare_issabel(getversion(),$branch[1].'.0.0','lt')) {
                throw new RuntimeException('Framework installer did not advance the PBX schema version.');
            }
        }
        deploy_reply(array('status'=>'healthy'));
    }
} catch (Throwable $error) { deploy_reply(array('ok'=>false,'error'=>$error->getMessage())); }
