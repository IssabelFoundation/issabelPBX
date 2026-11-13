<?php
function deploy_modules(): array {
    $result = array();
    foreach (sql('SELECT modulename,version,enabled FROM modules','getAll',DB_FETCHMODE_ASSOC) as $row) {
        $result[$row['modulename']] = array('version'=>(string)$row['version'],'enabled'=>(bool)$row['enabled']);
    }
    ksort($result);
    return $result;
}
function deploy_manager(): void {
    global $astman;
    if (!$astman) { throw new RuntimeException('Asterisk Manager is unavailable.'); }
    $reply = $astman->send_request('Ping');
    if (!is_array($reply) || !in_array(strtolower($reply['Response'] ?? ''),array('success','pong'),true)) {
        throw new RuntimeException('Asterisk Manager health check failed.');
    }
}
function deploy_db(): void {
    if ((string)sql('SELECT 1','getOne') !== '1') { throw new RuntimeException('Database health check failed.'); }
}
function deploy_directory_access(string $path,string $purpose,bool $writable = false): void {
    clearstatcache(true,$path);
    $account = posix_getpwuid(posix_geteuid());
    $user = $account['name'] ?? (string)posix_geteuid();
    $missing = array();
    if (!is_dir($path)) { $missing[] = 'directorio inexistente o inaccesible'; }
    else {
        if (!is_readable($path)) { $missing[] = 'lectura'; }
        if (!is_executable($path)) { $missing[] = 'acceso al directorio'; }
        if ($writable && !is_writable($path)) { $missing[] = 'escritura'; }
    }
    if ($missing) { throw new RuntimeException('Permisos insuficientes para '.$purpose.': '.$path.'; usuario '.$user.' (uid '.posix_geteuid().'); falta: '.implode(', ',$missing).'. No se modificaron los permisos.'); }
}
function deploy_targets(array $targets,bool $interactive = false): array {
    global $amp_conf;
    $targetsByName = array();
    $installed = deploy_modules();
    foreach ($targets as $target) {
        $name = $target['rawname'] ?? '';
        if (!is_string($name) || !preg_match('/^[a-z][a-z0-9_-]*$/D',$name) || ($name === 'framework' && !$interactive) || !is_string($target['version'] ?? null)) {
            throw new RuntimeException('Invalid target metadata.');
        }
        if ((!isset($installed[$name]) || !$installed[$name]['enabled']) && !$interactive) { throw new RuntimeException('Module not installed/enabled: '.$name); }
        if (isset($amp_conf['AMPWEBROOT'])) {
            $modulePath = $amp_conf['AMPWEBROOT'].'/admin/modules/'.$name;
            // A registered module can have no package directory (notably framework).
            // Interactive deployment prepares that directory as root before installation.
            if (isset($installed[$name]) && (!$interactive || file_exists($modulePath) || is_link($modulePath))) {
                deploy_directory_access($modulePath,'el módulo '.$name);
            } elseif ($interactive) {
                deploy_directory_access(dirname($modulePath),'el directorio de módulos');
            }
            deploy_directory_access((string)($amp_conf['ASTETCDIR'] ?? ''),'la configuración Asterisk',true);
        }
        if (isset($installed[$name]) && version_compare_issabel($target['version'],$installed[$name]['version'],'lt')) { throw new RuntimeException('Downgrade refused: '.$name); }
        $targetsByName[$name] = $target;
    }
    $edges = array();
    $upgrades = array();
    foreach ($targetsByName as $name=>$target) {
        $edges[$name] = array();
        $external = array();
        $deps = $target['depends']['module'] ?? array();
        foreach (is_array($deps) ? $deps : array($deps) as $dependency) {
            if (!is_string($dependency) || !preg_match('/^([a-z0-9_-]+)(?:\s+(?:(lt|le|gt|ge|==|=|eq|!=|ne)\s*)?([0-9][^\s]*))?$/iD',trim($dependency),$m)) {
                throw new RuntimeException('Unrecognized module dependency on '.$name);
            }
            if (isset($targetsByName[$m[1]])) {
                if (isset($installed[$m[1]]) && !$installed[$m[1]]['enabled']) { throw new RuntimeException('Dependency is disabled: '.$m[1]); }
                if (!empty($m[3]) && !version_compare_issabel($targetsByName[$m[1]]['version'],$m[3],strtolower($m[2] ?: 'ge'))) {
                    throw new RuntimeException('Selected target does not satisfy '.$dependency);
                }
                $edges[$name][] = $m[1];
            } else {
                if (empty($installed[$m[1]]['enabled']) || (!empty($m[3]) && !version_compare_issabel($installed[$m[1]]['version'],$m[3],strtolower($m[2] ?: 'ge')))) {
                    throw new RuntimeException('Dependencies failed for '.$name.': '.$dependency);
                }
                $external[] = $dependency;
            }
        }
        $candidate = $target;
        $candidate['depends']['module'] = $external;
        if ($interactive && isset($targetsByName['framework'])) {
            foreach ((array)($candidate['depends']['version'] ?? array()) as $required) {
                if (!is_string($required) || !preg_match('/^(?:(lt|le|gt|ge|==|=|eq|!=|ne)\s*)?([0-9][^\s]*)$/iD',trim($required),$m) ||
                    !version_compare_issabel($targetsByName['framework']['version'],$m[2],strtolower($m[1] ?: 'ge'))) { throw new RuntimeException('Target framework does not satisfy the PBX version required by '.$name); }
            }
            unset($candidate['depends']['version']);
            if ($name !== 'framework') { $edges[$name][] = 'framework'; }
        }
        $errors = module_checkdepends($candidate);
        if (is_array($errors) && count($errors)) { throw new RuntimeException('Dependencies failed for '.$name.': '.strip_tags(implode('; ',$errors))); }
        foreach ((array)($target['requirements']['file'] ?? array()) as $required) {
            if (is_string($required) && !file_exists($required)) { throw new RuntimeException('Required file missing: '.$required); }
        }
        if (!isset($installed[$name]) || version_compare_issabel($target['version'],$installed[$name]['version'],'gt')) { $upgrades[] = $name; }
    }
    $order = array();
    $visiting = array();
    $visit = function ($module) use (&$visit,&$order,&$visiting,$edges) {
        if (in_array($module,$order,true)) { return; }
        if (isset($visiting[$module])) { throw new RuntimeException('Cyclic module dependencies.'); }
        $visiting[$module] = true;
        foreach ($edges[$module] as $dependency) { $visit($dependency); }
        unset($visiting[$module]);
        $order[] = $module;
    };
    foreach (array_keys($targetsByName) as $name) { $visit($name); }
    // Upgrading a selected module must not invalidate an unselected module.
    foreach (module_getinfo(false,false,true) as $name=>$info) {
        if (isset($targetsByName[$name]) || empty($installed[$name]['enabled'])) { continue; }
        $requirements = $info['depends']['module'] ?? array();
        foreach (is_array($requirements) ? $requirements : array($requirements) as $requirement) {
            if (is_string($requirement) && preg_match('/^([a-z0-9_-]+)\s+(?:(lt|le|gt|ge|==|=|eq|!=|ne)\s*)?([0-9][^\s]*)$/iD',trim($requirement),$m) &&
                isset($targetsByName[$m[1]]) && !version_compare_issabel($targetsByName[$m[1]]['version'],$m[3],strtolower($m[2] ?: 'ge'))) {
                throw new RuntimeException('Target would break installed module '.$name.': '.$requirement);
            }
        }
    }
    return array('order'=>$order,'upgrades'=>$upgrades);
}
function deploy_sqlite_backup(string $source, string $destination): void {
    if (!class_exists('SQLite3') || !method_exists('SQLite3','backup')) { throw new RuntimeException('PHP SQLite3 online backup support is required.'); }
    $db = new SQLite3($source, SQLITE3_OPEN_READONLY);
    $db->busyTimeout(30000);
    $copy = new SQLite3($destination);
    if (!$db->backup($copy) || $copy->querySingle('PRAGMA integrity_check') !== 'ok') { throw new RuntimeException('SQLite backup/integrity check failed.'); }
    $copy->close();
    $db->close();
    chmod($destination,0600);
}
function deploy_mysql_backup(array $config,string $directory,array $command = array('mysqldump')): void {
    $defaults = tempnam($directory,'mysql-');
    if ($defaults === false) { throw new RuntimeException('Cannot prepare private database credentials.'); }
    $output = null; $errors = null; $complete = false;
    try {
        $quote = function ($value) { return '"'.str_replace(array('\\','"',"\n","\r"),array('\\\\','\\"','\\n','\\r'),(string)$value).'"'; };
        $credentials = "[client]\nuser=".$quote($config['AMPDBUSER'])."\npassword=".$quote($config['AMPDBPASS'])."\nhost=".$quote($config['AMPDBHOST'])."\n";
        if (!chmod($defaults,0600) || file_put_contents($defaults,$credentials) !== strlen($credentials)) { throw new RuntimeException('Cannot write private database credentials.'); }
        $output = fopen($directory.'/database.sql','xb');
        $errors = tmpfile();
        if (!is_resource($output) || !is_resource($errors)) { throw new RuntimeException('Cannot prepare database backup output.'); }
        if (!chmod($directory.'/database.sql',0600)) { throw new RuntimeException('Cannot protect database backup output.'); }
        // Only one database is exported. Lock its tables, including legacy MyISAM,
        // without requiring the global RELOAD privilege or blocking other databases.
        $process = proc_open(array_merge($command,array('--defaults-extra-file='.$defaults,'--skip-lock-all-tables','--lock-tables','--hex-blob','--routines','--events','--triggers','--databases',$config['AMPDBNAME'])),array(0=>array('file','/dev/null','r'),1=>$output,2=>$errors),$pipes);
        $code = is_resource($process) ? proc_close($process) : -1;
        fflush($output);
        rewind($errors);
        $detail = stream_get_contents($errors,65536);
        foreach (array('AMPDBPASS','AMPMGRPASS') as $key) {
            if (!empty($config[$key])) { $detail = str_replace($config[$key],'[redacted]',$detail); }
        }
        $log = $directory.'/mysqldump.stderr';
        if (file_put_contents($log,$detail) !== strlen($detail) || !chmod($log,0600)) { throw new RuntimeException('Cannot save protected mysqldump diagnostics.'); }
        if ($code !== 0) {
            $summary = trim(preg_replace('/[\x00-\x1f\x7f]+/',' ',$detail));
            throw new RuntimeException('mysqldump failed (exit '.$code.'): '.($summary !== '' ? substr($summary,0,2000) : 'No diagnostic output.').'; log: '.$log.'; no files have been deployed.');
        }
        if (fstat($output)['size'] === 0) { throw new RuntimeException('Empty database backup; log: '.$log); }
        $complete = true;
    } finally {
        if (is_resource($output)) {
            fclose($output);
            if (!$complete) { unlink($directory.'/database.sql'); }
        }
        if (is_resource($errors)) { fclose($errors); }
        unlink($defaults);
    }
}
function deploy_backup_check(): array {
    global $amp_conf, $asterisk_conf;
    if (PHP_OS_FAMILY !== 'Linux') { throw new RuntimeException('Native deployment supports Linux PBXs only.'); }
    \IssabelDeploy\run(array('runuser','--version'));
    \IssabelDeploy\run(array('tar','--version'));
    if (file_exists('/sys/fs/selinux/enforce')) { \IssabelDeploy\run(array('restorecon','-n','--',$amp_conf['ASTETCDIR'])); }
    $engine = $amp_conf['AMPDBENGINE'];
    if (!in_array($engine,array('mysql','mysqli','sqlite3'),true)) { throw new RuntimeException('Unsupported database engine for backup: '.$engine); }
    if ($engine !== 'sqlite3') { \IssabelDeploy\run(array('mysqldump','--version')); }
    if (!class_exists('SQLite3') || !method_exists('SQLite3','backup')) { throw new RuntimeException('PHP SQLite3 extension with online backup is required for AstDB.'); }
    $astdb = ($asterisk_conf['astdbdir'] ?? $asterisk_conf['astvarlibdir'] ?? $amp_conf['ASTVARLIBDIR'] ?? '/var/lib/asterisk').'/astdb.sqlite3';
    if (!is_readable($astdb)) { throw new RuntimeException('Cannot back up AstDB: '.$astdb); }
    $databaseBytes = $engine === 'sqlite3' ? filesize($amp_conf['AMPDBFILE']) :
        (int)sql("SELECT COALESCE(SUM(DATA_LENGTH+INDEX_LENGTH),0) FROM information_schema.TABLES WHERE TABLE_SCHEMA='".$GLOBALS['db']->escapeSimple($amp_conf['AMPDBNAME'])."'",'getOne');
    return array('astdb'=>$astdb,'estimated_bytes'=>($databaseBytes+filesize($astdb))*2);
}
// do_reload() in this framework does not validate the AMI Reload response.
class DeployManagerProxy {
    private $manager;
    public $reloadResponse;
    public function __construct($manager) { $this->manager = $manager; }
    public function Reload(...$args) { return $this->reloadResponse = $this->manager->Reload(...$args); }
    public function __call($name,$args) { return $this->manager->$name(...$args); }
}
