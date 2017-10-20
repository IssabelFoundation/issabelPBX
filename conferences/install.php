<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
global $db;
global $amp_conf;

$fcc = new featurecode('conferences', 'conf_status');
$fcc->setDescription('Conference Status'); 
$fcc->setDefault('*87');
$fcc->update();
unset($fcc);

$sql = "
CREATE TABLE IF NOT EXISTS `meetme` 
( 
	`exten` VARCHAR( 50 ) NOT NULL , 
	`options` VARCHAR( 15 ) , 
	`userpin` VARCHAR( 50 ) , 
	`adminpin` VARCHAR( 50 ) , 
	`description` VARCHAR( 50 ) , 
	`joinmsg_id` INTEGER, 
	`music` VARCHAR(80), 
	`users` TINYINT DEFAULT 0
)
";
$check = $db->query($sql);
if(DB::IsError($check)) {
	die_issabelpbx("Can not create meetme table");
}
outn(_("Checking if music field present.."));
$sql = "SELECT music FROM meetme";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	outn(_("adding music field.."));
  $sql = "ALTER TABLE meetme ADD music VARCHAR(80)";
  $result = $db->query($sql);
  if(DB::IsError($result)) {
		out(_("fatal error"));
		die_issabelpbx($result->getDebugInfo()); 
	} else {
		out(_("ok"));
	}
} else {
	out(_("already present"));
}

// Version 2.5 migrate to recording ids
//
outn(_("Checking if recordings need migration.."));
$sql = "SELECT joinmsg_id FROM meetme";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	//  Add recording_id field
	//
	out(_("migrating"));
	outn(_("adding joinmsg_id field.."));
  $sql = "ALTER TABLE meetme ADD joinmsg_id INTEGER";
  $result = $db->query($sql);
  if(DB::IsError($result)) {
		out(_("fatal error"));
		die_issabelpbx($result->getDebugInfo()); 
	} else {
		out(_("ok"));
	}

	// Get all the valudes and replace them with joinmsg_id
	//
	outn(_("migrate to recording ids.."));
  $sql = "SELECT `exten`, `joinmsg` FROM `meetme`";
	$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($results)) {
		out(_("fatal error"));
		die_issabelpbx($results->getDebugInfo());	
	}
	$migrate_arr = array();
	$count = 0;
	foreach ($results as $row) {
		if (trim($row['joinmsg']) != '') {
			$rec_id = recordings_get_or_create_id($row['joinmsg'], 'conference');
			$migrate_arr[] = array($rec_id, $row['exten']);
			$count++;
		}
	}
	if ($count) {
		$compiled = $db->prepare('UPDATE `meetme` SET `joinmsg_id` = ? WHERE `exten` = ?');
		$result = $db->executeMultiple($compiled,$migrate_arr);
		if(DB::IsError($result)) {
			out(_("fatal error"));
			die_issabelpbx($result->getDebugInfo());	
		}
	}
	out(sprintf(_("migrated %s entries"),$count));

	// Now remove the old recording field replaced by new id field
	//
	outn(_("dropping joinmsg field.."));
  $sql = "ALTER TABLE `meetme` DROP `joinmsg`";
  $result = $db->query($sql);
  if(DB::IsError($result)) { 
		out(_("no joinmsg field???"));
	} else {
		out(_("ok"));
	}

} else {
	out(_("already migrated"));
}
// Migration for Maximum Participant Count
outn(_("Checking for users field.."));
$sql = "SELECT users FROM meetme";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	outn(_("adding.."));
  $sql = "ALTER TABLE meetme ADD users TINYINT DEFAULT 0";
  $result = $db->query($sql);
  if(DB::IsError($result)) {
		out(_("FATAL error"));
		out($result->getDebugInfo()); 
	} else {
		out(_("ok"));
	}
} else {
	out(_("already present"));
}


?>
