<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;
global $asterisk_conf;

out('Remove all dahdi tables');
$tables = array('dahdi_advanced', 'dahdi_configured_locations', 'dahdi_spans', 'dahdi_scans', 'dahdi_analog', 'dahdi_advanced_modules', 'dahdi_modules');
foreach ($tables as $table) {
	$sql = "DROP TABLE IF EXISTS {$table}";
	$result = $db->query($sql);
	if (DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo());
	}
	unset($result);
}

out('Remove IssabelPBX Advanced Setting');
//Remove IssabelPBX Advanced Setting
$issabelpbx_conf =& issabelpbx_conf::create();
$issabelpbx_conf->remove_conf_settings('DAHDISHOWDIGITALCHANS');
$issabelpbx_conf->remove_conf_settings('DAHDIDISABLEWRITE');
if(file_exists($amp_conf['AMPBIN'].'/issabelpbx_engine_hook_dahdiconfig')) {
    unlink($amp_conf['AMPBIN'].'/issabelpbx_engine_hook_dahdiconfig');
}
