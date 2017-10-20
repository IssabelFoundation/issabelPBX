<?php

define("UPDATE_SERVER", "https://raw.github.com/tm1000/Caller-ID-Superfecta/v3.x/sources/");
define("ROOT_PATH", dirname(__FILE__) . '/');

//fix in the future
$version = preg_replace('/(alpha|beta)/i', '.0.', $module_info['module']['version']);
preg_match('/^(\d\.(:?\d*)\.(:?\d*))/i', $version, $matches);
$major_version = $matches[1];

//Get the enabled sources from this scheme
$sql = "SELECT value FROM superfectaconfig WHERE source='$scheme' AND field='sources'";
$enabled_sources = explode(',', $db->getOne($sql));

//get a list of the files that are on this local server
$tpl_sources = array();
$i = 0;
foreach (glob(ROOT_PATH . "sources/source-*.module") as $filename) {
    if (file_exists($filename)) {
        $source_desc = '';
        $source_param = array();

        require_once($filename);

        preg_match('/source-(.*)\.module/i', $filename, $matches);
        $this_source_name = $matches[1];

        $this_source_class = new $this_source_name();
        
        if ($major_version >= $this_source_class->version_requirement) {

            $j = !in_array($this_source_name, $enabled_sources) ? ($j = $i + 200) : ($j = $i);
            if (in_array($this_source_name, $enabled_sources)) {
                $j = array_search($this_source_name, $enabled_sources);
            } else {
                $j = $i + 200;
            }
            
            $tpl_sources[$j]['showup'] = FALSE;
            $tpl_sources[$j]['showdown'] = FALSE;
            $tpl_sources[$j]['showupdate'] = FALSE;
            $tpl_sources[$j]['pretty_source_name'] = str_replace("_", " ", $this_source_name);
            $tpl_sources[$j]['source_name'] = $this_source_name;
            $tpl_sources[$j]['enabled'] = in_array($this_source_name, $enabled_sources) ? TRUE : FALSE;
            $tpl_sources[$j]['status'] = in_array($this_source_name, $enabled_sources) ? 'enabled' : 'disabled';
            $tpl_sources[$j]['description'] = isset($this_source_class->description) ? preg_replace('/(<a>|<\/a>)/i', '', $this_source_class->description) : 'N/A';
            $tpl_sources[$j]['show_link'] = isset($this_source_class->source_param) ? TRUE : FALSE;
            
            //Simplify please
            if (in_array($this_source_name, $enabled_sources)) {
                if ($enabled_sources[0] != $this_source_name) {
                    $tpl_sources[$j]['showup'] = TRUE;
                }
                $c = count($enabled_sources) - 1;
                if ($enabled_sources[$c] != $this_source_name) {
                    $tpl_sources[$j]['showdown'] = TRUE;
                }
            }
            $i++;
        }
        unset($this_source_class);
    }
}

ksort($tpl_sources);

$supertpl->assign("scheme", $scheme);

$supertpl->assign("sources", $tpl_sources);

$supertpl->assign("web_path", 'http://' . $_SERVER['SERVER_NAME'] . '/admin/modules/superfecta/tpl/js/jquery.form.js');

echo $supertpl->draw('sources');
