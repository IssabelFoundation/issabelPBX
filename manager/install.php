<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
global $db;
global $amp_conf;

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";

$sql = "CREATE TABLE IF NOT EXISTS manager (
	`manager_id` INTEGER NOT NULL PRIMARY KEY $autoincrement,
	`name` VARCHAR( 15 ) NOT NULL ,
	`secret` VARCHAR( 50 ) ,
	`deny` VARCHAR( 255 ) ,
	`permit` VARCHAR( 255 ) ,
	`read` VARCHAR( 255 ) ,
	`write` VARCHAR( 255 )
)";

$check = $db->query($sql);
if (DB::IsError($check)) {
	die_issabelpbx("Can not create `manager` table" .  $check->getMessage() .  "\n");
}

outn(_("Increasing read field size if needed.."));
$sql = "ALTER TABLE `manager` CHANGE `read` `read` VARCHAR( 255 )";
$result = $db->query($sql);
if(DB::IsError($check)){
	out(_("error encountered, not altered"));
} else {
	out(_("ok"));
}

outn(_("Increasing write field size if needed.."));
$sql = "ALTER TABLE `manager` CHANGE `write` `write` VARCHAR( 255 )";
$result = $db->query($sql);
if(DB::IsError($check)){
	out(_("error encountered, not altered"));
} else {
	out(_("ok"));
}
?>
