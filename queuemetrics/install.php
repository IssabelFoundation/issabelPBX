<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

$sql = "SELECT * FROM `queuemetrics_options` LIMIT 1";
$check = $db->query($sql);
if(!DB::IsError($check)) {
	out(_("queuemetrics table already exists, exiting"));
} else {

	unset($sql);
	$sql[] = "CREATE TABLE IF NOT EXISTS `queuemetrics_options` (
	                `keyword` VARCHAR(25),
	                `value` TEXT,
	                UNIQUE KEY `keyword` (`keyword`)
	                )";
	
	foreach ($sql as $q) {
	        $result = $db->query($q);
	        if($db->IsError($result)){
	                die_issabelpbx($result->getDebugInfo());
	        }
	}

	outn(_("creating queuemetrics...ok"));
}

// sysadmin migration
outn(_("checking for ivr_logging field..."));
$sql = "SELECT `keyword` FROM `queuemetrics_options` where `keyword` = 'ivr_logging'";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if($db->IsError($check) || empty($check)) {
        // add new field
        $sql = "INSERT INTO `queuemetrics_options` (`keyword`, value) VALUES ('ivr_logging', '0');";
        $result = $db->query($sql);
        if($db->IsError($result)) {
                die_issabelpbx($result->getDebugInfo());
        }
        out(_("OK"));
} else {
        out(_("already exists"));
}
?>
