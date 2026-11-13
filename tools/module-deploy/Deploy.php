<?php
namespace IssabelDeploy;

interface Bridge {
    public function call(string $action, array $data = array()): array;
}

function process(array $command, string $input = '', ?string $cwd = null): array {
    $stdin = tmpfile(); $stdout = tmpfile(); $stderr = tmpfile();
    try {
        fwrite($stdin,$input); rewind($stdin);
        $process = proc_open($command, array(0=>$stdin,1=>$stdout,2=>$stderr), $pipes, $cwd);
        if (!is_resource($process)) { throw new \RuntimeException('Cannot start '.$command[0]); }
        $code = proc_close($process);
        rewind($stdout); rewind($stderr);
        return array('exit'=>$code,'stdout'=>stream_get_contents($stdout),'stderr'=>stream_get_contents($stderr));
    } finally { fclose($stdin); fclose($stdout); fclose($stderr); }
}
function run(array $command, string $input = '', ?string $cwd = null): string {
    $result = process($command,$input,$cwd);
    if ($result['exit'] !== 0) { throw new \RuntimeException(basename($command[0]).' failed (exit '.$result['exit'].'): '.substr($result['stderr'],0,2000)); }
    return $result['stdout'];
}

class NativeBridge implements Bridge {
    private $bootstrap;
    private $user;
    private $logDirectory;
    private $helperDirectory;
    public function logTo(string $directory): void { $this->logDirectory = $directory; }
    public function __construct(string $bootstrap) {
        $this->bootstrap = realpath($bootstrap);
        if (!$this->bootstrap) { throw new \RuntimeException('Issabel bootstrap not found: '.$bootstrap); }
    }
    private function worker(): string {
        if (!$this->helperDirectory) {
            $path = tempnam(sys_get_temp_dir(),'issabel-deploy-helper-');
            unlink($path);
            if (!mkdir($path,0700)) { throw new \RuntimeException('Cannot stage the native helper.'); }
            $this->helperDirectory = $path;
            foreach (array('Deploy.php','NativeSupport.php','native.php','Interactive.php') as $file) {
                $bytes = file_get_contents(__DIR__.'/'.$file);
                if (file_put_contents($path.'/'.$file,$bytes) !== strlen($bytes) || !chmod($path.'/'.$file,0644)) { throw new \RuntimeException('Cannot stage native helper code.'); }
            }
            // Public code only. Credentials/requests/backups never go here.
            if (!chmod($path,0755)) { throw new \RuntimeException('Cannot expose helper code to the Asterisk account.'); }
        }
        return $this->helperDirectory.'/native.php';
    }
    public function __destruct() {
        if ($this->helperDirectory) {
            foreach (glob($this->helperDirectory.'/*') as $file) { @unlink($file); }
            @rmdir($this->helperDirectory);
        }
    }
    public function call(string $action, array $data = array()): array {
        $command = array(PHP_BINARY, __DIR__.'/native.php', $action, $this->bootstrap);
        if (in_array($action, array('validate','install','reload','health'), true)) {
            if (!$this->user) { throw new \RuntimeException('Inspect installation first.'); }
            $command[1] = $this->worker();
            $command = array_merge(array('runuser','-u',$this->user,'--'), $command);
        }
        $result = process($command,json_encode($data,JSON_THROW_ON_ERROR));
        $response = json_decode($result['stdout'],true);
        if (!is_array($response)) { $response = array('ok'=>false,'error'=>'Native '.$action.' returned no structured result.','output'=>$result['stdout']); }
        $response['stderr'] = $result['stderr'];
        if ($result['exit'] !== 0) { $response['ok'] = false; $response['error'] = 'Native '.$action.' process failed (exit '.$result['exit'].'). Check protected logs.'; }
        if ($this->logDirectory) {
            $log = tempnam($this->logDirectory,'native-'.$action.'-');
            if (file_put_contents($log,json_encode($response,JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR)) === false) { throw new \RuntimeException('Cannot save native operation log.'); }
        }
        unset($response['output'],$response['stderr']);
        if (!isset($response['ok']) || !$response['ok']) { throw new \RuntimeException($response['error'] ?? 'Native helper failed.'); }
        if ($action === 'inspect') { $this->user = $response['user']; }
        return $response;
    }
}

class Files {
    public static function changePath(array $plan,array $change): string {
        return self::path($change['destination_root'] ?? $plan['modules'][$change['module']]['destination'],
            $change['destination_path'] ?? $change['path']);
    }
    public static function path(string $root, string $relative): string {
        if ($relative === '' || $relative[0] === '/' || strpos($relative, "\0") !== false ||
            preg_match('~(?:^|/)\.\.?(/|$)~', $relative)) { throw new \RuntimeException('Unsafe path: '.$relative); }
        $parts = explode('/', $relative);
        array_pop($parts);
        $parent = $root;
        foreach ($parts as $part) {
            $parent .= '/'.$part;
            if (is_link($parent) || (file_exists($parent) && !is_dir($parent))) {
                throw new \RuntimeException('Unsafe parent: '.$parent);
            }
        }
        return $root.'/'.$relative;
    }
    public static function snapshot(string $path): ?array {
        clearstatcache(true, $path);
        $stat = @lstat($path);
        if ($stat === false) { return null; }
        $type = is_link($path) ? 'link' : (is_file($path) ? 'file' : 'other');
        if ($type === 'other') { throw new \RuntimeException('Expected a file or link: '.$path); }
        return array('type'=>$type, 'sha256'=>$type === 'link' ? hash('sha256',readlink($path)) : hash_file('sha256',$path),
            'mode'=>$stat['mode'] & 0777, 'uid'=>$stat['uid'], 'gid'=>$stat['gid']);
    }
    public static function contentMatches(?array $actual, ?array $expected): bool {
        return $actual === null || $expected === null ? $actual === $expected :
            $actual['type'] === $expected['type'] && $actual['sha256'] === $expected['sha256'] &&
            ($actual['type'] !== 'file' || (($actual['mode'] & 0111) !== 0) === (($expected['mode'] & 0111) !== 0));
    }
    public static function nativeCopyTarget(array $target,?array $before): array {
        // cp -rf preserves the mode of an existing regular destination file.
        if ($target['type'] === 'file' && ($before['type'] ?? null) === 'file') { $target['mode'] = $before['mode']; }
        return $target;
    }
    public static function changeToken(string $path): ?string {
        clearstatcache(true,$path);
        if (@lstat($path) === false) { return null; }
        // Nanosecond ctime detects ACL/xattr/ownership changes even if the
        // content and ordinary permission bits did not change.
        if (PHP_OS_FAMILY === 'Linux') { return run(array('stat','--printf=%z|%y|%i','--',$path)); }
        $stat = lstat($path);
        return $stat['ctime'].'|'.$stat['mtime'].'|'.$stat['ino'];
    }
    public static function treeTokens(string $root,array $tree): array {
        $result = array();
        foreach ($tree as $path=>$meta) { $result[$path] = self::changeToken(self::path($root,$path)); }
        return $result;
    }
    public static function backupFile(string $source, string $destination): void {
        if (PHP_OS_FAMILY === 'Linux') { run(array('cp','--preserve=all','--',$source,$destination)); }
        elseif (!copy($source,$destination)) { throw new \RuntimeException('Cannot back up file.'); }
    }
    public static function write(string $path, string $bytes, array $meta, ?string $preserve = null): void {
        $parents = array();
        for ($parent = dirname($path); !is_dir($parent); $parent = dirname($parent)) { $parents[] = $parent; }
        foreach (array_reverse($parents) as $parent) {
            if (!mkdir($parent,0755) || !chown($parent,$meta['uid']) || !chgrp($parent,$meta['gid'])) { throw new \RuntimeException('Cannot create parent directory with correct ownership.'); }
        }
        $temp = tempnam(dirname($path), '.issabel-deploy-');
        try {
            if ($meta['type'] === 'link') {
                unlink($temp);
                if (!symlink($bytes, $temp)) { throw new \RuntimeException('Cannot stage link.'); }
                if (function_exists('lchown')) { lchown($temp, $meta['uid']); lchgrp($temp, $meta['gid']); }
            } else {
                if ($preserve !== null) { self::backupFile($preserve,$temp); }
                elseif (is_file($path) && !is_link($path)) { self::backupFile($path,$temp); }
                if (file_put_contents($temp, $bytes) === false || !chmod($temp, $meta['mode']) ||
                    !chown($temp, $meta['uid']) || !chgrp($temp, $meta['gid'])) { throw new \RuntimeException('Cannot stage file or set ownership.'); }
            }
            if (!rename($temp, $path)) { throw new \RuntimeException('Cannot replace '.$path); }
        } finally { if (file_exists($temp) || is_link($temp)) { unlink($temp); } }
    }
    public static function tree(string $root): array {
        $result = array();
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $entry) {
            if ($entry->isDir() && !$entry->isLink()) { continue; }
            $relative = substr($entry->getPathname(), strlen($root)+1);
            $result[$relative] = self::snapshot($entry->getPathname());
        }
        ksort($result);
        return $result;
    }
}

class GitSource {
    public $path;
    public function __construct(string $path) {
        $this->path = realpath($path);
        if (!$this->path) { throw new \RuntimeException('Repository not found.'); }
        $root = trim($this->git(array('rev-parse','--show-toplevel')));
        if (realpath($root) !== $this->path) { throw new \RuntimeException('--repo must be the repository root.'); }
    }
    private function git(array $args): string {
        return run(array_merge(array('git','--no-replace-objects','-c','core.hooksPath=/dev/null','-c','safe.directory='.$this->path,'-C',$this->path), $args));
    }
    public function commit(string $ref): string {
        if ($ref === '' || $ref[0] === '-') { throw new \RuntimeException('Invalid commit reference.'); }
        return trim($this->git(array('rev-parse','--verify',$ref.'^{commit}')));
    }
    public function blob(string $oid): string {
        if (!preg_match('/^[a-f0-9]{40,64}$/D', $oid)) { throw new \RuntimeException('Invalid Git object.'); }
        return $this->git(array('cat-file','blob',$oid));
    }
    public function modules(string $commit): array {
        $result = array();
        foreach (explode("\n",trim($this->git(array('ls-tree','-d','--name-only',$commit)))) as $name) {
            if (preg_match('/^[a-z][a-z0-9_-]*$/D',$name) && trim($this->git(array('ls-tree',$commit,'--',$name.'/module.xml'))) !== '') { $result[] = $name; }
        }
        sort($result);
        return $result;
    }
    public function tree(string $commit, string $module, bool $validateLinks = true): array {
        $result = array();
        foreach (explode("\0", $this->git(array('ls-tree','-rz',$commit,'--',$module.'/'))) as $record) {
            if ($record === '') { continue; }
            if (!preg_match('/^(100644|100755|120000) blob ([a-f0-9]+)\t(.+)$/sD', $record, $m)) {
                throw new \RuntimeException('Unsupported Git entry (submodules are not deployable).');
            }
            $relative = substr($m[3], strlen($module)+1);
            Files::path('/unused', $relative);
            $bytes = $this->blob($m[2]);
            $type = $m[1] === '120000' ? 'link' : 'file';
            if ($type === 'link' && $validateLinks) {
                if ($bytes === '' || $bytes[0] === '/' || strpos($bytes, "\0") !== false) { throw new \RuntimeException('Unsafe repository symlink.'); }
                $parts = explode('/', dirname($relative).'/'.$bytes);
                $depth = 0;
                foreach ($parts as $part) {
                    if ($part === '..') { $depth--; } elseif ($part !== '.' && $part !== '') { $depth++; }
                    if ($depth < 0) { throw new \RuntimeException('Repository symlink escapes module.'); }
                }
                // Reject parent traversal through another link, which lexical
                // normalization alone cannot prove stays inside the module.
                if (in_array('..',explode('/',$bytes),true)) { throw new \RuntimeException('Parent-traversing repository symlinks are not supported.'); }
            }
            $result[$relative] = array('oid'=>$m[2], 'type'=>$type, 'sha256'=>hash('sha256',$bytes), 'mode'=>$m[1] === '100755' ? 0755 : 0644);
        }
        return $result;
    }
    public function metadata(array $tree): array {
        if (!isset($tree['module.xml'])) { throw new \RuntimeException('Missing module.xml.'); }
        $xml = $this->blob($tree['module.xml']['oid']);
        if (stripos($xml, '<!DOCTYPE') !== false) { throw new \RuntimeException('DOCTYPE is not permitted in module.xml.'); }
        $element = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NONET);
        if (!$element) { throw new \RuntimeException('Invalid module.xml.'); }
        return json_decode(json_encode($element, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }
}

class Deployer {
    private $bridge;
    private $state;
    private $lock;
    public function __construct(Bridge $bridge, string $state) {
        $this->bridge = $bridge;
        if (is_link($state)) { throw new \RuntimeException('State directory cannot be a symlink.'); }
        if (!is_dir($state) && !mkdir($state, 0700, true)) { throw new \RuntimeException('Cannot create state directory.'); }
        $this->state = realpath($state);
        $stat = stat($this->state);
        if ($stat['uid'] !== posix_geteuid() || ($stat['mode'] & 0077)) { throw new \RuntimeException('State directory must be private and owned by the operator.'); }
        umask(0077);
        foreach (array('plans','deployments') as $dir) { if (!is_dir($this->state.'/'.$dir)) { mkdir($this->state.'/'.$dir, 0700); } }
        if (!file_exists($this->state.'/key')) {
            $key = @fopen($this->state.'/key','x');
            if ($key) { fwrite($key,random_bytes(32)); fclose($key); }
        }
    }
    private function acquire(): void {
        $path = $this->bridge instanceof NativeBridge ? '/var/lock/issabelpbx-deploy.lock' : $this->state.'/lock';
        if (is_link($path)) { throw new \RuntimeException('Lock path cannot be a symlink.'); }
        $this->lock = fopen($path, 'c');
        if (!flock($this->lock, LOCK_EX | LOCK_NB)) { throw new \RuntimeException('Another deployment is running.'); }
    }
    private function ensureRecovered(): void {
        foreach ($this->history() as $record) {
            if (!in_array($record['status'],array('complete','rolled-back','auto-rolled-back','aborted-before-write','manually-recovered'),true)) {
                throw new \RuntimeException('Unresolved deployment '.$record['id'].' ('.$record['status'].'). Recover it before another deployment.');
            }
        }
    }
    private function release(): void {
        if ($this->lock) { flock($this->lock, LOCK_UN); fclose($this->lock); $this->lock = null; }
    }
    private function save(string $path, array $data): void {
        $temp = tempnam(dirname($path), '.state-');
        try {
            $bytes = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
            if (file_put_contents($temp,$bytes) !== strlen($bytes) || !chmod($temp,0600) || !rename($temp,$path)) { throw new \RuntimeException('Cannot persist deployment state.'); }
        } finally { if (file_exists($temp)) { unlink($temp); } }
    }
    private function sign(array $plan): string {
        unset($plan['signature']);
        return hash_hmac('sha256', json_encode($plan, JSON_THROW_ON_ERROR), file_get_contents($this->state.'/key'));
    }
    private function toolHash(): string {
        return hash('sha256',file_get_contents(__FILE__).file_get_contents(__DIR__.'/native.php').file_get_contents(__DIR__.'/NativeSupport.php').file_get_contents(__DIR__.'/Interactive.php'));
    }
    private function inventory(bool $allowPending = false): array {
        $live = $this->bridge->call('inspect');
        if (!empty($live['pending_reload']) && !$allowPending) { throw new \RuntimeException('Issabel has pending changes. Apply/review them before deployment.'); }
        foreach (array('modules_dir','config_dir') as $key) {
            if (!isset($live[$key]) || !is_dir($live[$key])) { throw new \RuntimeException('Invalid installation path: '.$key); }
            $live[$key] = realpath($live[$key]);
        }
        if ($this->state === $live['webroot'] || strpos($this->state.'/', rtrim($live['webroot'],'/').'/') === 0) {
            throw new \RuntimeException('State/backups must be outside the web directory.');
        }
        return $live;
    }
    private function current(): array {
        return is_file($this->state.'/current.json') ? json_decode(file_get_contents($this->state.'/current.json'), true, 512, JSON_THROW_ON_ERROR) : array();
    }
    private function verify(array $plan, array $live): void {
        foreach (array('modules_dir','config_dir','webroot','bin_dir','agi_dir','user','uid','gid','php_version','asterisk_version','pbx_version','bootstrap_sha256','pending_reload') as $key) {
            if (($live[$key] ?? null) !== ($plan['installation'][$key] ?? null)) { throw new \RuntimeException('Installation changed since plan: '.$key); }
        }
        foreach ($plan['modules'] as $module => $details) {
            if (($live['modules'][$module] ?? null) !== ($plan['installation']['modules'][$module] ?? null)) {
                throw new \RuntimeException('Installed module changed since plan: '.$module);
            }
            if (!empty($details['new'])) {
                if (file_exists($details['destination']) || is_link($details['destination'])) { throw new \RuntimeException('New module destination appeared: '.$module); }
            } elseif (realpath($live['modules_dir'].'/'.$module) !== $details['destination']) { throw new \RuntimeException('Module destination changed: '.$module); }
        }
        foreach ($plan['changes'] as $change) {
            $path = Files::changePath($plan,$change);
            if (Files::snapshot($path) !== $change['before'] || Files::changeToken($path) !== $change['before_token']) { throw new \RuntimeException('Local change/conflict: '.$path); }
        }
        $this->bridge->call('validate', array('targets'=>array_column($plan['modules'], 'metadata'),'interactive'=>!empty($plan['interactive'])));
    }
    public function planInstalled(array $scan,array $selected): array {
        $this->acquire();
        try {
            $this->ensureRecovered();
            $allowPending = !empty($scan['pending_approved']) && !empty($scan['installation']['pending_reload']);
            $live = $this->inventory($allowPending);
            foreach (array('modules_dir','config_dir','webroot','bin_dir','agi_dir','uid','gid','php_version','asterisk_version','pbx_version','pending_reload') as $key) {
                if (($live[$key] ?? null) !== ($scan['installation'][$key] ?? null)) { throw new \RuntimeException('La instalación cambió mientras elegías módulos. Volvé a ejecutar el comando.'); }
            }
            $repo = new GitSource($scan['repository']);
            $plan = array('format'=>1,'interactive'=>true,'allow_pending_reload'=>$allowPending,'tool_sha256'=>$this->toolHash(),'created_at'=>gmdate('c'),
                'repository'=>$repo->path,'target'=>$scan['target'],'installation'=>$live,'modules'=>array(),'changes'=>array(),'previous'=>$this->current());
            $install = array(); $roots = array();
            foreach ($selected as $module) {
                $row = $scan['modules'][$module];
                if (isset($row['error']) || ($live['modules'][$module] ?? null) !== $row['installed']) { throw new \RuntimeException('El módulo cambió: '.$module); }
                if (isset($roots[$row['destination']])) { throw new \RuntimeException('Dos módulos comparten el mismo destino.'); }
                $roots[$row['destination']] = true;
                $plan['modules'][$module] = array('base'=>null,'destination'=>$row['destination'],'metadata'=>$row['metadata'],'new'=>$row['new']);
                foreach ($row['changes'] as $change) { $plan['changes'][] = $change; }
                if ($row['install_required']) { $install[] = $module; }
                if ($module === 'framework') {
                    $tree = $repo->tree($scan['target'],$module);
                    if (is_dir($row['destination'])) {
                        foreach (Files::tree($row['destination']) as $path=>$original) {
                            if (InteractiveUpdater::frameworkTemporary($path) && !isset($tree[$path])) { throw new \RuntimeException('Framework tiene archivos locales dentro del paquete temporal: '.$path); }
                        }
                    }
                    foreach ($tree as $path=>$target) {
                        $change = InteractiveUpdater::change($module,$path,$target,$row['destination']);
                        // module.xml/install.php were already included in the scan.
                        if ($change && !in_array($path,array('module.xml','install.php'),true)) {
                            if (InteractiveUpdater::frameworkTemporary($path)) { $change['removed_by_installer'] = true; }
                            $plan['changes'][] = $change;
                        }
                    }
                }
            }
            $validation = $this->bridge->call('validate',array('targets'=>array_column($plan['modules'],'metadata'),'interactive'=>true));
            $plan['order'] = $validation['order'];
            $plan['upgrades'] = array_values(array_unique(array_merge($validation['upgrades'],$install)));
            $this->verify($plan,$live);
            foreach ($plan['changes'] as $change) {
                if ($change['after']['type'] === 'file' && substr($change['path'],-4) === '.php') {
                    $temp = tempnam($this->state,'lint-');
                    try { file_put_contents($temp,$repo->blob($change['after']['oid'])); run(array(PHP_BINARY,'-l',$temp)); }
                    catch (\Throwable $error) { throw new \RuntimeException('PHP inválido en '.$change['module'].'/'.$change['path'].': '.$error->getMessage()); }
                    finally { unlink($temp); }
                }
            }
            $this->space($plan);
            $plan['signature'] = $this->sign($plan);
            $path = $this->state.'/plans/'.gmdate('Ymd\THis').'-'.substr($plan['signature'],0,12).'.json';
            $this->save($path,$plan);
            return array('plan'=>$path);
        } finally { $this->release(); }
    }
    public function plan(string $repoPath, ?string $from, string $to, array $modules): array {
        $this->acquire();
        try {
            $this->ensureRecovered();
            $live = $this->inventory();
            $repo = new GitSource($repoPath);
            $target = $repo->commit($to);
            $current = $this->current();
            $plan = array('format'=>1, 'tool_sha256'=>$this->toolHash(), 'created_at'=>gmdate('c'), 'repository'=>$repo->path, 'target'=>$target,
                'installation'=>$live, 'modules'=>array(), 'changes'=>array(), 'previous'=>$current);
            $seen = array();
            foreach (array_unique($modules) as $module) {
                if (!preg_match('/^[a-z][a-z0-9_-]*$/D',$module) || $module === 'framework') { throw new \RuntimeException('Unsupported module: '.$module); }
                if (!isset($live['modules'][$module]) || !$live['modules'][$module]['enabled']) { throw new \RuntimeException('Module must already be installed and enabled: '.$module); }
                $base = $from !== null ? $repo->commit($from) : ($current[$module]['commit'] ?? null);
                if (!$base) { throw new \RuntimeException('First deployment requires --from for '.$module); }
                $base = $repo->commit($base);
                $old = $repo->tree($base,$module);
                $new = $repo->tree($target,$module);
                $oldMeta = $repo->metadata($old);
                $metadata = $repo->metadata($new);
                if (($metadata['rawname'] ?? null) !== $module || ($oldMeta['rawname'] ?? null) !== $module ||
                    ($oldMeta['version'] ?? null) !== $live['modules'][$module]['version']) { throw new \RuntimeException('Base metadata does not match installed module: '.$module); }
                $dest = realpath($live['modules_dir'].'/'.$module);
                if (!$dest || !is_dir($dest) || !is_writable($dest) || isset($seen[$dest])) { throw new \RuntimeException('Invalid/shared destination: '.$module); }
                $seen[$dest] = true;
                $plan['modules'][$module] = array('base'=>$base, 'metadata'=>$metadata, 'destination'=>$dest);
                foreach (array_unique(array_merge(array_keys($old),array_keys($new))) as $path) {
                    if (($old[$path] ?? null) === ($new[$path] ?? null)) { continue; }
                    if (preg_match('~(?:^|/)[^/]*custom[^/]*~i',$path)) { throw new \RuntimeException('Custom file cannot be deployed: '.$module.'/'.$path); }
                    $actual = Files::snapshot(Files::path($dest,$path));
                    if (!Files::contentMatches($actual,$old[$path] ?? null)) { throw new \RuntimeException('Local customization or incorrect --from: '.$module.'/'.$path); }
                    $plan['changes'][] = array('module'=>$module,'path'=>$path,'before'=>$actual,'before_token'=>Files::changeToken(Files::path($dest,$path)),'after'=>$new[$path] ?? null);
                }
                $installers = array('install.php','install.sql','uninstall.php');
                foreach (array($oldMeta,$metadata) as $meta) {
                    foreach ((array)($meta['fileinclude']['install'] ?? array()) as $include) { if (is_string($include)) { $installers[] = $include; } }
                }
                if ($oldMeta['version'] === $metadata['version']) {
                    foreach ($plan['changes'] as $change) {
                        if ($change['module'] === $module && (in_array($change['path'],$installers,true) ||
                            preg_match('~(?:^|/)(?:install|upgrades?|migrations?)/|\.sql$~i',$change['path']))) { throw new \RuntimeException('Installer changed without a module version increase: '.$module); }
                    }
                }
            }
            $validation = $this->bridge->call('validate', array('targets'=>array_column($plan['modules'],'metadata')));
            $plan['order'] = $validation['order'];
            $plan['upgrades'] = $validation['upgrades'];
            // Validate committed PHP with the same interpreter used on this PBX.
            foreach ($plan['changes'] as $change) {
                if ($change['after'] && $change['after']['type'] === 'file' && substr($change['path'],-4) === '.php') {
                    $temp = tempnam($this->state,'lint-');
                    try { file_put_contents($temp,$repo->blob($change['after']['oid'])); run(array(PHP_BINARY,'-l',$temp)); }
                    finally { unlink($temp); }
                }
            }
            $this->space($plan);
            $plan['signature'] = $this->sign($plan);
            $id = gmdate('Ymd\THis').'-'.substr($plan['signature'],0,12);
            $path = $this->state.'/plans/'.$id.'.json';
            $this->save($path,$plan);
            $review = array();
            foreach ($plan['modules'] as $module=>$details) { $review[] = array('git','-C',$repo->path,'diff','--no-ext-diff','--no-textconv',$details['base'],$target,'--',$module.'/'); }
            return array('plan'=>$path, 'target'=>$target, 'modules'=>$plan['order'], 'upgrades'=>$plan['upgrades'], 'changes'=>$plan['changes'], 'review_commands'=>$review, 'note'=>'Review the committed diff before apply. No PBX files or database were changed.');
        } finally { $this->release(); }
    }
    private function space(array $plan): void {
        $required = 64*1024*1024;
        $required += $this->bridge->call('backup-check',array('directory'=>$this->state))['estimated_bytes'] ?? 0;
        foreach ($plan['modules'] as $details) {
            if (!is_dir($details['destination'])) { continue; }
            foreach (Files::tree($details['destination']) as $path=>$meta) {
                if ($meta['type'] === 'file') { $required += filesize($details['destination'].'/'.$path)*2; }
            }
        }
        foreach (Files::tree($plan['installation']['config_dir']) as $path=>$meta) {
            if ($meta['type'] === 'file') { $required += filesize($plan['installation']['config_dir'].'/'.$path)*2; }
        }
        if (!empty($plan['interactive']) && isset($plan['modules']['framework'])) {
            foreach (InteractiveUpdater::frameworkRoots($plan['installation']) as $root) {
                foreach (Files::tree($root) as $path=>$meta) { if ($meta['type'] === 'file') { $required += filesize($root.'/'.$path)*2; } }
            }
        }
        foreach (array_unique(array_merge(array($this->state),array_column($plan['modules'],'destination'))) as $path) {
            while (!is_dir($path)) { $path = dirname($path); }
            if (disk_free_space($path) < $required) { throw new \RuntimeException('Insufficient free space: '.$path); }
        }
    }
    private function restoreFiles(array $plan, string $directory): void {
        foreach (array_reverse($plan['changes']) as $i=>$change) {
            $path = Files::changePath($plan,$change);
            if ($change['before'] === null) {
                if ((file_exists($path) || is_link($path)) && !unlink($path)) { throw new \RuntimeException('Cannot remove '.$path); }
            } else {
                $key = hash('sha256',$change['module'].'/'.$change['path']);
                if (hash_file('sha256',$directory.'/files/'.$key) !== $change['before']['sha256']) { throw new \RuntimeException('Backup checksum mismatch: '.$change['path']); }
                Files::write($path,file_get_contents($directory.'/files/'.$key),$change['before'], $change['before']['type'] === 'file' ? $directory.'/files/'.$key : null);
            }
        }
    }
    private function verifyBackups(array $record, string $directory): void {
        foreach ($record['plan']['changes'] as $change) {
            if ($change['before'] && hash_file('sha256',$directory.'/files/'.hash('sha256',$change['module'].'/'.$change['path'])) !== $change['before']['sha256']) {
                throw new \RuntimeException('File backup checksum mismatch.');
            }
        }
        foreach ($record['config_before'] as $path=>$meta) {
            if (hash_file('sha256',$directory.'/config/'.hash('sha256',$path)) !== $meta['sha256']) { throw new \RuntimeException('Configuration backup checksum mismatch.'); }
        }
    }
    private function restoreConfig(array $record, string $directory): void {
        $root = $record['plan']['installation']['config_dir'];
        $before = $record['config_before'];
        $now = Files::tree($root);
        foreach (array_unique(array_merge(array_keys($before),array_keys($now))) as $path) {
            if (preg_match('~(?:^|/)[^/]*custom[^/]*~i',$path) && ($before[$path] ?? null) !== ($now[$path] ?? null)) {
                throw new \RuntimeException('Custom configuration changed; manual recovery required: '.$path);
            }
        }
        foreach (array_diff_key($now,$before) as $path=>$meta) { unlink(Files::path($root,$path)); }
        foreach ($before as $path=>$meta) {
            if (($now[$path] ?? null) === $meta) { continue; }
            $backup = $directory.'/config/'.hash('sha256',$path);
            if (hash_file('sha256',$backup) !== $meta['sha256']) { throw new \RuntimeException('Configuration backup checksum mismatch: '.$path); }
            Files::write(Files::path($root,$path),file_get_contents($backup),$meta,$meta['type'] === 'file' ? $backup : null);
        }
    }
    private function recordPath(string $id): string {
        if (!preg_match('/^[0-9]{8}T[0-9]{6}-[a-f0-9]{12}$/D',$id)) { throw new \RuntimeException('Invalid deployment ID.'); }
        return $this->state.'/deployments/'.$id;
    }
    public function apply(string $path): array {
        $this->acquire();
        try {
            $this->ensureRecovered();
            $plan = json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
            if (($plan['format'] ?? null) !== 1 || !isset($plan['signature']) || !hash_equals($this->sign($plan),$plan['signature'])) { throw new \RuntimeException('Untrusted or modified plan. Generate a new plan on this PBX.'); }
            if (($plan['tool_sha256'] ?? '') !== $this->toolHash()) { throw new \RuntimeException('Deployment tool changed; generate a new plan.'); }
            if ($this->current() !== $plan['previous']) { throw new \RuntimeException('Deployment history changed; generate a new plan.'); }
            if (!$plan['changes'] && !$plan['upgrades']) { return array('status'=>'unchanged','target'=>$plan['target']); }
            $live = $this->inventory(!empty($plan['interactive']) && !empty($plan['allow_pending_reload']));
            $this->verify($plan,$live);
            $this->space($plan);
            $repo = new GitSource($plan['repository']);
            $id = gmdate('Ymd\THis').'-'.bin2hex(random_bytes(6));
            $directory = $this->recordPath($id);
            mkdir($directory,0700);
            mkdir($directory.'/files',0700);
            mkdir($directory.'/staged',0700);
            mkdir($directory.'/config',0700);
            $record = array('id'=>$id,'status'=>'preparing','plan'=>$plan,'migration_started'=>false,'written'=>array());
            $this->save($directory.'/record.json',$record);
            if ($this->bridge instanceof NativeBridge) { $this->bridge->logTo($directory); }
            try {
                $destinations = array_map(function ($details) { return $details['destination']; },$plan['modules']);
                $this->bridge->call('backup',array('directory'=>$directory,'modules'=>$destinations));
                $record['config_before'] = Files::tree($live['config_dir']);
                foreach ($record['config_before'] as $relative=>$meta) {
                    $source = Files::path($live['config_dir'],$relative);
                    if ($meta['type'] === 'file') { Files::backupFile($source,$directory.'/config/'.hash('sha256',$relative)); }
                    else { file_put_contents($directory.'/config/'.hash('sha256',$relative),readlink($source)); }
                }
                foreach ($plan['changes'] as $change) {
                    $key = hash('sha256',$change['module'].'/'.$change['path']);
                    $source = Files::changePath($plan,$change);
                    if ($change['before']) {
                        if ($change['before']['type'] === 'file') { Files::backupFile($source,$directory.'/files/'.$key); }
                        else { file_put_contents($directory.'/files/'.$key,readlink($source)); }
                    }
                    if ($change['after']) {
                        $bytes = $repo->blob($change['after']['oid']);
                        if (hash('sha256',$bytes) !== $change['after']['sha256']) { throw new \RuntimeException('Source object changed.'); }
                        file_put_contents($directory.'/staged/'.$key,$bytes);
                    }
                }
                // Verify staging and backups before any production write.
                foreach ($plan['changes'] as $change) {
                    $key = hash('sha256',$change['module'].'/'.$change['path']);
                    if (($change['before'] && hash_file('sha256',$directory.'/files/'.$key) !== $change['before']['sha256']) ||
                        ($change['after'] && hash_file('sha256',$directory.'/staged/'.$key) !== $change['after']['sha256'])) { throw new \RuntimeException('Staging/backup checksum mismatch.'); }
                }
                $this->verify($plan,$this->inventory(!empty($plan['interactive']) && !empty($plan['allow_pending_reload'])));
                $record['status'] = 'writing';
                $this->save($directory.'/record.json',$record);
                foreach ($plan['changes'] as $index=>$change) {
                    if (!empty($change['installer_managed'])) { continue; }
                    $dest = Files::changePath($plan,$change);
                    if (Files::snapshot($dest) !== $change['before'] || Files::changeToken($dest) !== $change['before_token']) { throw new \RuntimeException('File changed immediately before write: '.$dest); }
                    if (!$change['after']) { if (!unlink($dest)) { throw new \RuntimeException('Cannot delete '.$dest); } }
                    else {
                        $meta = $change['after'];
                        $meta['uid'] = $change['before']['uid'] ?? $live['uid'];
                        $meta['gid'] = $change['before']['gid'] ?? $live['gid'];
                        if ($change['before'] && $change['before']['type'] === $meta['type']) { $meta['mode'] = ($change['before']['mode'] & ~0111) | ($meta['mode'] & 0111); }
                        Files::write($dest,file_get_contents($directory.'/staged/'.hash('sha256',$change['module'].'/'.$change['path'])),$meta);
                    }
                    $record['written'][] = $index;
                    $this->save($directory.'/record.json',$record);
                }
                $this->bridge->call('permissions',array('modules'=>$destinations,'interactive'=>!empty($plan['interactive']),'paths'=>array_map(function ($change) use ($plan) {
                    return Files::changePath($plan,$change);
                },array_filter($plan['changes'],function ($change) { return $change['before'] === null && $change['after'] !== null; }))));
                foreach ($plan['order'] as $module) {
                    if (!in_array($module,$plan['upgrades'],true)) { continue; }
                    $record['migration_started'] = true;
                    $record['status'] = 'installing:'.$module;
                    $this->save($directory.'/record.json',$record);
                    $installed = $plan['installation']['modules'][$module]['version'] ?? null;
                    $this->bridge->call('install',array('module'=>$module,'interactive'=>!empty($plan['interactive']),
                        'reinstall'=>!empty($plan['interactive']) && ($installed === $plan['modules'][$module]['metadata']['version'] ||
                            (isset($plan['installation']['modules'][$module]) && !$plan['installation']['modules'][$module]['enabled']))));
                }
                $record['status'] = 'reloading';
                $this->save($directory.'/record.json',$record);
                $record['reload'] = $this->bridge->call('reload');
                $enabled = array();
                foreach ($plan['modules'] as $module=>$details) { $enabled[$module] = $plan['installation']['modules'][$module]['enabled'] ?? true; }
                $this->bridge->call('health',array('targets'=>array_column($plan['modules'],'metadata'),'enabled'=>$enabled));
                foreach ($plan['changes'] as $change) {
                    $this->verifyDeployedFile($plan,$change);
                }
                return $this->completeDeployment($record,$directory,$live);
            } catch (\Throwable $error) {
                $record['error'] = $error->getMessage();
                if ($record['migration_started']) {
                    $record['status'] = 'recovery-required';
                } elseif ($record['status'] === 'writing' && !$record['written']) {
                    $record['status'] = 'aborted-before-write';
                } elseif (in_array($record['status'],array('writing','reloading'),true)) {
                    try {
                        $this->verifyBackups($record,$directory);
                        $partial = $plan;
                        $partial['changes'] = array_intersect_key($plan['changes'],array_flip($record['written']));
                        $this->restoreFiles($partial,$directory);
                        $this->restoreConfig($record,$directory);
                        $this->bridge->call('reload');
                        $record['status'] = 'auto-rolled-back';
                    } catch (\Throwable $recovery) { $record['status'] = 'recovery-required'; $record['recovery_error'] = $recovery->getMessage(); }
                } else { $record['status'] = 'aborted-before-write'; }
                $this->save($directory.'/record.json',$record);
                throw new \RuntimeException($record['status'].': '.$error->getMessage().'; backup: '.$directory);
            }
        } finally { $this->release(); }
    }
    private function verifyDeployedFile(array $plan,array $change): void {
        $expected = !empty($change['removed_by_installer']) ? null : $change['after'];
        if ($expected && !empty($change['installer_managed']) && $change['module'] === 'framework') {
            $expected = Files::nativeCopyTarget($expected,$change['before']);
        }
        $file = Files::changePath($plan,$change);
        $actual = Files::snapshot($file);
        if (!Files::contentMatches($actual,$expected)) {
            throw new \RuntimeException('Post-deployment file mismatch: '.$change['path'].'; destination: '.$file.
                '; expected: '.json_encode($expected).'; actual: '.json_encode($actual));
        }
    }
    private function completeDeployment(array $record,string $directory,array $live): array {
        $plan = $record['plan']; $id = $record['id'];
        $record['config_after'] = Files::tree($live['config_dir']);
        $record['config_after_tokens'] = Files::treeTokens($live['config_dir'],$record['config_after']);
        $record['files_after'] = array(); $record['files_after_tokens'] = array();
        foreach ($plan['changes'] as $change) {
            $file = Files::changePath($plan,$change);
            $record['files_after'][] = Files::snapshot($file);
            $record['files_after_tokens'][] = Files::changeToken($file);
        }
        $record['status'] = 'complete'; $record['completed_at'] = gmdate('c');
        $this->save($directory.'/record.json',$record);
        $current = $plan['previous'];
        foreach ($plan['modules'] as $module=>$details) { $current[$module] = array('commit'=>$plan['target'],'deployment'=>$id); }
        $this->save($this->state.'/current.json',$current);
        return array('status'=>'complete','deployment'=>$id,'backup'=>$directory,'modules'=>$plan['order']);
    }
    public function recover(string $id): array {
        $this->acquire();
        try {
            $directory = $this->recordPath($id);
            $record = json_decode(file_get_contents($directory.'/record.json'),true,512,JSON_THROW_ON_ERROR);
            $plan = $record['plan'];
            if (($record['id'] ?? null) !== $id || !isset($plan['signature']) || !hash_equals($this->sign($plan),$plan['signature'])) { throw new \RuntimeException('Untrusted recovery plan.'); }
            if ($record['status'] !== 'recovery-required' || strpos($record['error'] ?? '','Post-deployment file mismatch: ') !== 0 || ($record['reload']['status'] ?? '') !== 'success') {
                throw new \RuntimeException('Recovery verification is only available after a file verification failure following a successful reload.');
            }
            foreach ($this->history() as $other) {
                if ($other['id'] !== $id && !in_array($other['status'],array('complete','rolled-back','auto-rolled-back','aborted-before-write','manually-recovered'),true)) { throw new \RuntimeException('Another deployment requires recovery: '.$other['id']); }
            }
            if ($this->current() !== $plan['previous']) { throw new \RuntimeException('Deployment history changed; manual reconciliation required.'); }
            $live = $this->inventory();
            foreach (array('modules_dir','config_dir','webroot','bin_dir','agi_dir','user','uid','gid','bootstrap_sha256') as $key) {
                if (($live[$key] ?? null) !== ($plan['installation'][$key] ?? null)) { throw new \RuntimeException('Recovery installation path/account changed: '.$key); }
            }
            $this->verifyBackups($record,$directory);
            foreach ($plan['modules'] as $module=>$details) {
                if (realpath($live['modules_dir'].'/'.$module) !== $details['destination']) { throw new \RuntimeException('Recovery module destination changed: '.$module); }
            }
            foreach ($plan['changes'] as $change) { $this->verifyDeployedFile($plan,$change); }
            $enabled = array();
            foreach ($plan['modules'] as $module=>$details) { $enabled[$module] = $plan['installation']['modules'][$module]['enabled'] ?? true; }
            if ($this->bridge instanceof NativeBridge) { $this->bridge->logTo($directory); }
            $this->bridge->call('health',array('targets'=>array_column($plan['modules'],'metadata'),'enabled'=>$enabled));
            // Verify again after health checks, before updating the deployment journal.
            foreach ($plan['changes'] as $change) { $this->verifyDeployedFile($plan,$change); }
            $this->save($directory.'/record-before-recovery.json',$record);
            $record['recovery'] = array('verified_at'=>gmdate('c'),'previous_error'=>$record['error'],'tool_sha256'=>$this->toolHash());
            unset($record['error']);
            return $this->completeDeployment($record,$directory,$live);
        } finally { $this->release(); }
    }
    public function history(): array {
        $result = array();
        foreach (glob($this->state.'/deployments/*/record.json') as $path) {
            $record = json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
            $result[] = array('id'=>$record['id'],'status'=>$record['status'],'target'=>$record['plan']['target'],'migration_started'=>$record['migration_started']);
        }
        return $result;
    }
    public function rollback(string $id): array {
        $this->acquire();
        try {
            $directory = $this->recordPath($id);
            $record = json_decode(file_get_contents($directory.'/record.json'),true,512,JSON_THROW_ON_ERROR);
            if ($record['migration_started']) { throw new \RuntimeException('Migration started: database/AstDB recovery must be performed explicitly using '.$directory.'. File-only rollback is refused.'); }
            if ($record['status'] !== 'complete') { throw new \RuntimeException('Only a completed patch deployment can be rolled back automatically.'); }
            if ($this->bridge instanceof NativeBridge) { $this->bridge->logTo($directory); }
            $this->ensureRecovered();
            $plan = $record['plan'];
            $live = $this->inventory();
            if ($live['config_dir'] !== $plan['installation']['config_dir'] || Files::tree($live['config_dir']) !== $record['config_after']) { throw new \RuntimeException('Configuration changed since deployment; manual reconciliation required.'); }
            if (Files::treeTokens($live['config_dir'],$record['config_after']) !== $record['config_after_tokens']) { throw new \RuntimeException('Configuration metadata changed since deployment; manual reconciliation required.'); }
            $current = $this->current();
            foreach ($plan['modules'] as $module=>$details) {
                if (($current[$module]['deployment'] ?? null) !== $id || realpath($live['modules_dir'].'/'.$module) !== $details['destination']) { throw new \RuntimeException('Later deployment or changed module destination: '.$module); }
            }
            foreach ($plan['changes'] as $index=>$change) {
                $file = Files::changePath($plan,$change);
                if (Files::snapshot($file) !== $record['files_after'][$index] || Files::changeToken($file) !== $record['files_after_tokens'][$index]) { throw new \RuntimeException('Files changed since deployment: '.$change['path']); }
            }
            $this->verifyBackups($record,$directory);
            $record['status'] = 'rolling-back';
            $this->save($directory.'/record.json',$record);
            try {
                $this->restoreFiles($plan,$directory);
                $this->restoreConfig($record,$directory);
                $this->bridge->call('reload');
                foreach ($plan['modules'] as $module=>$details) {
                    if (isset($plan['previous'][$module])) { $current[$module] = $plan['previous'][$module]; } else { unset($current[$module]); }
                }
                $this->save($this->state.'/current.json',$current);
                $record['status'] = 'rolled-back';
            } catch (\Throwable $error) {
                $record['status'] = 'recovery-required';
                $record['error'] = $error->getMessage();
                throw $error;
            } finally { $this->save($directory.'/record.json',$record); }
            return array('status'=>'rolled-back','deployment'=>$id);
        } finally { $this->release(); }
    }
}
