<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;
global $amp_conf;

$autoincrement=(preg_match("/qlite/",$amp_conf["AMPDBENGINE"])) ? "AUTOINCREMENT":"AUTO_INCREMENT";

$sql = "CREATE TABLE IF NOT EXISTS miscdests (
	id INTEGER NOT NULL PRIMARY KEY $autoincrement,
	description VARCHAR( 100 ) NOT NULL ,
	destdial VARCHAR( 100 ) NOT NULL
)";

$check = $db->query($sql);
if(DB::IsError($check)) {
	die_issabelpbx("Can not create miscdests table\n");
}

?>
