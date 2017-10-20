<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

$sql = "
	CREATE TABLE IF NOT EXISTS `custom_destinations` (
		`custom_dest` varchar(80) NOT NULL default '',
		`description` varchar(40) NOT NULL default '',
		`notes` varchar(255) NOT NULL default '',
		PRIMARY KEY  (`custom_dest`)
	)";

$check = $db->query($sql);
if(DB::IsError($check)) {
	die_issabelpbx("Can not create custom_destinations table\n");
}


$sql = "
	CREATE TABLE IF NOT EXISTS `custom_extensions` 
	(
		`custom_exten` varchar(80) NOT NULL default '',
		`description` varchar(40) NOT NULL default '',
		`notes` varchar(255) NOT NULL default '',
		PRIMARY KEY  (`custom_exten`)
	)";

$check = $db->query($sql);
if(DB::IsError($check)) {
	die_issabelpbx("Can not create custom_extensions table\n");
}

?>
