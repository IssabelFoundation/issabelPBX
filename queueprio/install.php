<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

$autoincrement=(preg_match("/qlite/",$amp_conf["AMPDBENGINE"])) ? "AUTOINCREMENT":"AUTO_INCREMENT";
$sql = "CREATE TABLE IF NOT EXISTS queueprio (
	queueprio_id INTEGER NOT NULL PRIMARY KEY $autoincrement,
	queue_priority VARCHAR( 50 ) ,
	description VARCHAR( 50 ) ,
	dest VARCHAR( 255 )
)";

$check = $db->query($sql);
if(DB::IsError($check)) {
	die_issabelpbx("Can not create queueprio table\n");
}

?>
