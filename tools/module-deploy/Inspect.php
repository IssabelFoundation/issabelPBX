<?php
namespace IssabelDeploy;

// Filesystem-only inspection. Do not bootstrap an unknown hybrid PBX or reuse
// deployment validation: neither DB compatibility nor a base commit is assumed.
class Inspector {
    private function write($stream,string $bytes): void {
        if (fwrite($stream,$bytes) !== strlen($bytes)) { throw new \RuntimeException('Cannot write the complete diff report.'); }
    }
    private function localTree(string $root): array {
        $entries = array();
        if (!is_dir($root)) { return $entries; }
        $filter = new \RecursiveCallbackFilterIterator(
            new \RecursiveDirectoryIterator($root,\FilesystemIterator::SKIP_DOTS),
            function ($entry) { return !in_array($entry->getFilename(),array('.git','.svn'),true); }
        );
        $iterator = new \RecursiveIteratorIterator($filter,\RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $entry) {
            $path = $entry->getPathname();
            $relative = substr($path,strlen($root)+1);
            $stat = lstat($path);
            $type = $entry->isLink() ? 'link' : ($entry->isDir() ? 'directory' : ($entry->isFile() ? 'file' : 'special'));
            $hash = null;
            if ($type === 'file') {
                if (!is_readable($path) || ($hash = hash_file('sha256',$path)) === false) { throw new \RuntimeException('Cannot read '.$path); }
            } elseif ($type === 'link') { $hash = hash('sha256',readlink($path)); }
            $entries[$relative] = array('type'=>$type,'sha256'=>$hash,'mode'=>$stat['mode'] & 0777,
                'uid'=>$stat['uid'],'gid'=>$stat['gid']);
        }
        ksort($entries);
        return $entries;
    }
    private function version(string $root,array $entries): ?string {
        if (($entries['module.xml']['type'] ?? null) !== 'file') { return null; }
        $text = file_get_contents($root.'/module.xml');
        if (stripos($text,'<!DOCTYPE') !== false) { return null; }
        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($text,'SimpleXMLElement',LIBXML_NONET);
            return $xml && isset($xml->version) ? trim((string)$xml->version) : null;
        } finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
    }
    private function blocked(string $path,array $entries): ?string {
        $parts = explode('/',$path); array_pop($parts); $prefix = '';
        foreach ($parts as $part) {
            $prefix = $prefix === '' ? $part : $prefix.'/'.$part;
            if (isset($entries[$prefix]) && $entries[$prefix]['type'] !== 'directory') { return $prefix; }
        }
        return null;
    }
    private function diff(GitSource $repo,string $root,string $scratch,string $module,string $path,?array $actual,?array $expected,int $limit): string {
        $live = null; $target = null;
        if ($actual && in_array($actual['type'],array('file','link'),true)) {
            if ($actual['type'] === 'file' && filesize($root.'/'.$path) > $limit) { return "Diff omitted: installed file exceeds the size limit.\n"; }
            $live = $actual['type'] === 'link' ? readlink($root.'/'.$path) : file_get_contents($root.'/'.$path);
            if (hash('sha256',$live) !== $actual['sha256']) { throw new \RuntimeException('File changed during inspection: '.$module.'/'.$path); }
        }
        if ($expected) {
            $target = $repo->blob($expected['oid']);
            if (strlen($target) > $limit) { return "Diff omitted: target file exceeds the size limit.\n"; }
        }
        $args = array();
        foreach (array('installed'=>$live,'target'=>$target) as $side=>$bytes) {
            if ($bytes === null) { $args[] = '/dev/null'; continue; }
            $relative = $side.'/'.$module.'/'.$path;
            $file = Files::path($scratch,$relative);
            if (!is_dir(dirname($file))) { mkdir(dirname($file),0700,true); }
            if (file_put_contents($file,$bytes) !== strlen($bytes)) { throw new \RuntimeException('Cannot write diff staging file.'); }
            chmod($file,0600);
            $args[] = $relative;
        }
        try {
            $result = process(array_merge(array('git','-c','core.hooksPath=/dev/null','diff','--no-index','--no-ext-diff','--no-textconv','--no-color','--no-prefix','--'),$args),'',$scratch);
            if (!in_array($result['exit'],array(0,1),true)) { throw new \RuntimeException('Cannot generate file diff.'); }
            return $result['stdout'];
        } finally {
            foreach ($args as $relative) { if ($relative !== '/dev/null') { unlink(Files::path($scratch,$relative)); } }
        }
    }
    private function removeScratch(string $path): void {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path,\FilesystemIterator::SKIP_DOTS),\RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $entry) { $entry->isDir() && !$entry->isLink() ? rmdir($entry->getPathname()) : unlink($entry->getPathname()); }
        rmdir($path);
    }
    public function inspect(string $repoPath,string $to,array $modules,string $modulesDir,string $output,int $limit = 1048576): array {
        if ($limit < 1 || $limit > 16777216) { throw new \RuntimeException('Diff limit must be between 1 and 16777216 bytes.'); }
        $directory = realpath($modulesDir);
        if (!$directory || !is_dir($directory)) { throw new \RuntimeException('Installed modules directory not found; use --modules-dir.'); }
        $repo = new GitSource($repoPath);
        $commit = $repo->commit($to);
        $modules = array_values(array_unique($modules));
        if (!$modules) { throw new \RuntimeException('Select at least one module.'); }
        foreach ($modules as $module) {
            if (!preg_match('/^[a-z][a-z0-9_-]*$/D',$module) || $module === 'framework') {
                throw new \RuntimeException('Select ordinary module directories. Framework system files require a separate path mapping.');
            }
        }
        $parent = realpath(dirname($output));
        if (!$parent || basename($output) === '.' || basename($output) === '..') { throw new \RuntimeException('Output parent must already exist.'); }
        $output = $parent.'/'.basename($output);
        if (file_exists($output) || is_link($output)) { throw new \RuntimeException('Output directory already exists; choose a new private directory.'); }
        // A report contains local code; keep it outside both modules and web root.
        $webRoot = basename($directory) === 'modules' && basename(dirname($directory)) === 'admin' ? dirname($directory,2) : $directory;
        foreach (array($directory,$webRoot,$repo->path) as $protected) {
            if ($output === $protected || strpos($output,$protected.'/') === 0) { throw new \RuntimeException('Reports must be outside the web/modules directory.'); }
        }
        umask(0077);
        if (!mkdir($output,0700)) { throw new \RuntimeException('Cannot create output directory.'); }
        if (file_put_contents($output.'/INCOMPLETE','Inspection is not complete. Do not use partial reports.') === false) { throw new \RuntimeException('Cannot create inspection marker.'); }
        $scratch = $output.'/.compare';
        if (!mkdir($scratch,0700)) { throw new \RuntimeException('Cannot create private diff staging directory.'); }
        $patch = fopen($output.'/differences.diff','xb');
        if (!$patch) { throw new \RuntimeException('Cannot create diff report.'); }
        $report = array('format'=>'issabel-module-inspection-1','created_at'=>gmdate('c'),'target'=>$commit,'repository'=>$repo->path,
            'installed_modules_dir'=>$directory,'database_checked'=>false,'modules'=>array(),'summary'=>array(),
            'note'=>'Read-only file comparison, not an installation plan or a database migration audit.');
        $ok = false;
        try {
            foreach ($modules as $module) {
                $expected = $repo->tree($commit,$module,false);
                $metadata = $repo->metadata($expected);
                if (($metadata['rawname'] ?? null) !== $module) { throw new \RuntimeException('Target module name mismatch.'); }
                $logicalRoot = $directory.'/'.$module;
                $root = realpath($logicalRoot);
                if ((file_exists($logicalRoot) || is_link($logicalRoot)) && (!$root || !is_dir($root))) { throw new \RuntimeException('Invalid installed module directory: '.$module); }
                $actual = $root ? $this->localTree($root) : array();
                $entry = array('destination'=>$root ?: null,'installed_file_version'=>$root ? $this->version($root,$actual) : null,
                    'target_version'=>$metadata['version'],'counts'=>array(),'differences'=>array());
                $paths = array_unique(array_merge(array_keys($expected),array_keys($actual))); sort($paths);
                foreach ($paths as $path) {
                    $left = $actual[$path] ?? null; $right = $expected[$path] ?? null;
                    if (!$right && $left['type'] === 'directory') { continue; }
                    $blocked = $this->blocked($path,$actual);
                    if ($blocked !== null) { $status = 'blocked_by_parent'; }
                    elseif (!$left) { $status = 'target_only'; }
                    elseif (!$right) { $status = 'installed_only'; }
                    elseif ($left['type'] !== $right['type']) { $status = 'type_changed'; }
                    elseif ($left['sha256'] !== $right['sha256']) { $status = 'content_changed'; }
                    elseif ($left['type'] === 'file' && $left['mode'] !== $right['mode']) { $status = 'permissions_changed'; }
                    else { $status = 'identical'; }
                    $entry['counts'][$status] = ($entry['counts'][$status] ?? 0)+1;
                    $report['summary'][$status] = ($report['summary'][$status] ?? 0)+1;
                    if ($status === 'identical') { continue; }
                    $detail = array('path'=>$path,'status'=>$status,'installed'=>$left,'target'=>$right,
                        'protected'=>preg_match('~(?:^|/)[^/]*custom[^/]*~i',$path) === 1);
                    if ($blocked !== null) { $detail['blocked_parent'] = $blocked; }
                    $entry['differences'][] = $detail;
                    $this->write($patch,"\n# ".$status.' '.json_encode($module.'/'.$path,JSON_UNESCAPED_SLASHES)."\n");
                    if ($blocked === null && $status !== 'permissions_changed' && (!$left || in_array($left['type'],array('file','link'),true))) {
                        $this->write($patch,$this->diff($repo,$root ?: $logicalRoot,$scratch,$module,$path,$left,$right,$limit));
                    }
                }
                // Detect concurrent content/permission changes instead of publishing
                // a clean-looking report assembled from different file states.
                if ($root && $this->localTree($root) !== $actual) { throw new \RuntimeException('Installed files changed during inspection: '.$module); }
                $report['modules'][$module] = $entry;
            }
            if (!fflush($patch)) { throw new \RuntimeException('Cannot flush the diff report.'); }
            $json = json_encode($report,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR)."\n";
            if (file_put_contents($output.'/report.json',$json) !== strlen($json)) { throw new \RuntimeException('Cannot save inspection report.'); }
            $ok = true;
        } finally {
            fclose($patch); $this->removeScratch($scratch);
            if (!$ok) { file_put_contents($output.'/INCOMPLETE','Inspection failed. Discard the partial report and retry after resolving the error.'); }
        }
        if (!unlink($output.'/INCOMPLETE')) { throw new \RuntimeException('Cannot finalize inspection report.'); }
        return array('status'=>'inspected','target'=>$commit,'summary'=>$report['summary'],
            'versions'=>array_map(function ($module) { return array('installed_file_version'=>$module['installed_file_version'],'target_version'=>$module['target_version']); },$report['modules']),
            'report'=>$output.'/report.json','diff'=>$output.'/differences.diff',
            'note'=>'No Issabel bootstrap, database access, installation or reload was performed. Keep reports private; diffs can contain local secrets.');
    }
}
