<?php
namespace IssabelDeploy;

class InteractiveUpdater {
    private $bridge;
    private $state;
    public function __construct(Bridge $bridge,string $state) { $this->bridge = $bridge; $this->state = $state; }
    public static function protectedFile(string $path): bool {
        return preg_match('~(?:^|/)[^/]*custom[^/]*\.conf$~i',$path) === 1;
    }
    public static function installerFile(string $path,array $metadata): bool {
        return in_array($path,array_merge(array('install.php','install.sql'),(array)($metadata['fileinclude']['install'] ?? array())),true) ||
            preg_match('~(?:^|/)(?:install|upgrades?|migrations?)/|\.sql$~i',$path) === 1;
    }
    public static function change(string $module,string $path,array $target,string $root,?string $relative = null): ?array {
        $relative = $relative ?? $path;
        $file = Files::path($root,$relative);
        $before = Files::snapshot($file);
        if (Files::contentMatches($before,$target)) { return null; }
        return array('module'=>$module,'path'=>$path,'destination_root'=>$root,'destination_path'=>$relative,
            'before'=>$before,'before_token'=>Files::changeToken($file),'after'=>$target);
    }
    public static function frameworkRoots(array $live): array {
        $roots = array('amp_conf/htdocs/'=>$live['webroot'], 'amp_conf/bin/'=>$live['bin_dir'] ?? '', 'amp_conf/agi-bin/'=>$live['agi_dir'] ?? '');
        foreach ($roots as $prefix=>$root) {
            if (!$root || !is_dir($root)) { throw new \RuntimeException('No se pudo detectar un destino de framework: '.$prefix); }
            $roots[$prefix] = realpath($root);
        }
        return $roots;
    }
    public static function frameworkTemporary(string $path): bool {
        return strpos($path,'amp_conf/') === 0 || strpos($path,'upgrades/') === 0 || $path === 'libissabelpbx.install.php';
    }
    private function dependencies(array $scan,array $selected,callable $question,callable $write): ?array {
        for ($index = 0; $index < count($selected); $index++) {
            $module = $selected[$index];
            $metadata = $scan['modules'][$module]['metadata'];
            $needed = array();
            foreach ((array)($metadata['depends']['module'] ?? array()) as $requirement) {
                if (!is_string($requirement) || !preg_match('/^([a-z0-9_-]+)(?:\s+(?:(lt|le|gt|ge|==|=|eq|!=|ne)\s*)?([0-9][^\s]*))?$/iD',trim($requirement),$m)) { continue; }
                $installed = $scan['installation']['modules'][$m[1]] ?? null;
                if (!$installed || !$installed['enabled'] || (!empty($m[3]) && !version_compare($installed['version'],$m[3],strtolower($m[2] ?: 'ge')))) { $needed[$m[1]] = $requirement; }
            }
            foreach ((array)($metadata['depends']['version'] ?? array()) as $requirement) {
                if (is_string($requirement) && preg_match('/^(?:(lt|le|gt|ge|==|=|eq|!=|ne)\s*)?([0-9][^\s]*)$/iD',trim($requirement),$m) &&
                    !version_compare($scan['installation']['pbx_version'],$m[2],strtolower($m[1] ?: 'ge'))) { $needed['framework'] = 'versión PBX '.$requirement; }
            }
            foreach ($needed as $dependency=>$reason) {
                if (in_array($dependency,$selected,true)) { continue; }
                if (!isset($scan['modules'][$dependency]) || isset($scan['modules'][$dependency]['error'])) { throw new \RuntimeException($module.' necesita '.$reason.', que no está disponible en el clon.'); }
                $write($module.' necesita actualizar/instalar '.$dependency.' ('.$reason.").\n");
                if (!in_array(strtolower(trim($question('¿Incluir '.$dependency.'? [s/N] '))),array('s','si','sí'),true)) { return null; }
                $selected[] = $dependency;
            }
        }
        return $selected;
    }
    public function scan(string $repoPath,string $ref = 'HEAD'): array {
        $live = $this->bridge->call('inspect');
        foreach (array('modules_dir','config_dir','webroot') as $key) { $live[$key] = realpath($live[$key]) ?: $live[$key]; }
        $repo = new GitSource($repoPath);
        $commit = $repo->commit($ref);
        $rows = array();
        foreach ($repo->modules($commit) as $module) {
            try {
                $tree = $repo->tree($commit,$module);
                $metadata = $repo->metadata($tree);
                if (($metadata['rawname'] ?? null) !== $module) { throw new \RuntimeException('module.xml no coincide con la carpeta.'); }
                $logical = $live['modules_dir'].'/'.$module;
                $root = realpath($logical) ?: $logical;
                if ((file_exists($logical) || is_link($logical)) && !is_dir($logical)) { throw new \RuntimeException('El destino no es un directorio.'); }
                if ($root === $repo->path || strpos($root,$repo->path.'/') === 0) { throw new \RuntimeException('El módulo instalado apunta al clon de origen; usá otro clon para actualizar.'); }
                $changes = array(); $protected = 0;
                $install = !isset($live['modules'][$module]) || $live['modules'][$module]['version'] !== $metadata['version'];
                if ($module === 'framework') {
                    if (preg_match('/^(\d+\.\d+)/',$metadata['version'],$branch) && version_compare($live['pbx_version'],$branch[1].'.0.0','<')) { $install = true; }
                    foreach (self::frameworkRoots($live) as $prefix=>$destination) {
                        foreach ($tree as $path=>$target) {
                            if (strpos($path,$prefix) !== 0) { continue; }
                            $relative = substr($path,strlen($prefix));
                            if (self::protectedFile($relative)) { throw new \RuntimeException('El paquete framework incluye configuración custom activa; requiere revisión.'); }
                            $target = Files::nativeCopyTarget($target,Files::snapshot(Files::path($destination,$relative)));
                            $change = self::change($module,'@'.$path,$target,$destination,$relative);
                            if ($change) { $change['installer_managed'] = true; $changes[] = $change; }
                        }
                    }
                    foreach (array('module.xml','install.php') as $path) {
                        if (isset($tree[$path]) && ($change = self::change($module,$path,$tree[$path],$root))) { $changes[] = $change; }
                    }
                    if ($changes) { $install = true; }
                } else {
                    foreach ($tree as $path=>$target) {
                        if (self::protectedFile($path)) { $protected++; continue; }
                        $change = self::change($module,$path,$target,$root);
                        if ($change) {
                            $changes[] = $change;
                            if (self::installerFile($path,$metadata)) { $install = true; }
                        }
                    }
                }
                $rows[$module] = array('metadata'=>$metadata,'destination'=>$root,'new'=>!is_dir($root),
                    'installed'=>$live['modules'][$module] ?? null,'changes'=>$changes,'install_required'=>$install,'protected_files'=>$protected);
            } catch (\Throwable $error) { $rows[$module] = array('error'=>$error->getMessage()); }
        }
        return array('repository'=>$repo->path,'target'=>$commit,'installation'=>$live,'modules'=>$rows);
    }
    // Question callback returns s/n/t/d/q. Kept separate from stdin for tests.
    public function update(string $repoPath,callable $question,callable $write): array {
        $write("Buscando la instalación y comparando el clon...\n");
        $scan = $this->scan($repoPath);
        $write('Clon: '.$scan['repository']."\nCommit: ".$scan['target']."\nComparando todos los módulos...\n");
        if (!empty($scan['installation']['pending_reload'])) {
            $write("Issabel tiene cambios de configuración pendientes. Se aplicarán junto con los módulos en la recarga final.\n");
            if (!in_array(strtolower(trim($question('¿Incluir esos cambios pendientes? [s/N] '))),array('s','si','sí'),true)) {
                return array('status'=>'cancelled');
            }
            $scan['pending_approved'] = true;
        }
        $selected = array(); $notInstalled = array(); $all = false;
        foreach ($scan['modules'] as $module=>$row) {
            if (!isset($scan['installation']['modules'][$module])) { $notInstalled[] = $module; continue; }
            if (isset($row['error'])) { $write($module.': no disponible: '.$row['error']."\n"); continue; }
            if (!$row['changes'] && !$row['install_required']) { continue; }
            $description = $module.': '.($row['installed']['version'] ?? 'no instalado').' → '.$row['metadata']['version'].'; '.count($row['changes']).' archivos diferentes'.
                ($row['install_required'] ? '; ejecutar instalador' : '; actualizar archivos').
                (isset($row['installed']) && !$row['installed']['enabled'] ? '; seguirá deshabilitado' : '');
            $write($description."\n");
            if (!$all) {
                do {
                    $answer = strtolower(trim($question('¿Actualizar '.$module.'? [s/N/t=todos los instalados/d=detalle/q=salir] ')));
                    if ($answer === 'd') {
                        foreach ($row['changes'] as $change) { $write('  '.($change['before'] ? 'modificado ' : 'nuevo ').$change['path']."\n"); }
                    }
                } while ($answer === 'd');
                if ($answer === 'q') { return array('status'=>'cancelled'); }
                if ($answer === 't') { $all = true; }
                elseif ($answer !== 's' && $answer !== 'si' && $answer !== 'sí') { continue; }
            }
            $selected[] = $module;
        }
        if (!$selected) {
            $write("No hay módulos seleccionados para actualizar.\n");
            $this->reportNotInstalled($notInstalled,$write);
            return array('status'=>'unchanged');
        }
        $selected = $this->dependencies($scan,$selected,$question,$write);
        if ($selected === null) { return array('status'=>'cancelled'); }
        $notInstalled = array_values(array_diff($notInstalled,$selected));
        $write('Seleccionados: '.implode(', ',$selected)."\nSe reemplazarán sus archivos diferentes, incluidas modificaciones locales; habrá respaldo y una recarga al finalizar.\n");
        if (!in_array(strtolower(trim($question('¿Aplicar la actualización? [s/N] '))),array('s','si','sí'),true)) { return array('status'=>'cancelled'); }
        $deployer = new Deployer($this->bridge,$this->state);
        $plan = $deployer->planInstalled($scan,$selected);
        $write("Guardando respaldo y actualizando...\n");
        $result = $deployer->apply($plan['plan']);
        $write('Actualización terminada. Respaldo: '.($result['backup'] ?? '')."\n");
        $this->reportNotInstalled($notInstalled,$write);
        return $result;
    }
    private function reportNotInstalled(array $modules,callable $write): void {
        if ($modules) { $write('Módulos disponibles en el clon que no están instalados (omitidos): '.implode(', ',$modules)."\n"); }
    }
}
