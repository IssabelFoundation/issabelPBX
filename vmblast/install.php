<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

// TODO:
// TODO: MOVE TABLE CREATIONS INTO HERE
// TODO:

global $db;

outn(_("Upgrading vmblast to add audio_label field.."));
$sql = "SELECT audio_label FROM vmblast";
$confs = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if (!DB::IsError($confs)) { // no error... Already done
	out(_("Not Required"));
} else {
	$sql = "ALTER TABLE vmblast ADD audio_label INT ( 11 ) NOT NULL DEFAULT -1";
	$results = $db->query($sql);
	if(DB::IsError($results)) {
	        die_issabelpbx($results->getMessage());
	}
	out(_("Done"));
}

outn(_("Upgrading vmblast to add password field.."));
$sql = "SELECT password FROM vmblast";
$confs = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if (!DB::IsError($confs)) { // no error... Already done
	out(_("Not Required"));
} else {
	$sql = "ALTER TABLE vmblast ADD password VARCHAR ( 20 ) NOT NULL";
	$results = $db->query($sql);
	if(DB::IsError($results)) {
	        die_issabelpbx($results->getMessage());
	}
	out(_("Done"));
}

// Drop grplist field but first pull it's data and put in new table
//
outn(_("Dropping grplist.."));
$sql = 'SELECT grpnum, grplist FROM vmblast';
$confs = $db->getAll($sql, DB_FETCHMODE_ASSOC);
if (!DB::IsError($confs)) { 
	$list = array();
	foreach ($confs as $group) {
		$grplist = explode('&',$group['grplist']);
		foreach ($grplist as $exten) {
			$list[] = array($group['grpnum'],addslashes(trim($exten)));
		}
	}
	$compiled = $db->prepare("INSERT INTO vmblast_groups (grpnum, ext) values (?,?)");
	$result   = $db->executeMultiple($compiled, $list);
	if(DB::IsError($result)) {
		out(_("error populating vmblast_groups table"));
		return false;
	} else {
		out(_("populated new table"));
		outn(_("Dropping old grplist field.."));
		$sql = "ALTER TABLE `vmblast` DROP `grplist`";
		$results = $db->query($sql);
		if(DB::IsError($results)) {
			out(_("failed to drop field"));
		} else {
			out(_("OK"));
		}
	}
} else {
	out(_("Not Needed"));
}

?>
