<?php

if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";
$sql[]="CREATE TABLE IF NOT EXISTS callrecording (
	callrecording_id INTEGER NOT NULL PRIMARY KEY $autoincrement,
	callrecording_mode VARCHAR( 50 ) ,
	description VARCHAR( 50 ) ,
	dest VARCHAR( 255 )
)";
$sql[]="CREATE TABLE IF NOT EXISTS callrecording_module (
			extension varchar(50),
			cidnum varchar(50) default '',
      callrecording varchar(10),
      display varchar(20)
			);";

foreach($sql as $s){
	$check = $db->query($s);
	if(DB::IsError($check)) {
		die_issabelpbx("Can not create callrecording table\n");
	}
}

?>
