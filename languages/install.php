<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";
$sql[]="CREATE TABLE IF NOT EXISTS languages (
	language_id INTEGER NOT NULL PRIMARY KEY $autoincrement,
	lang_code VARCHAR( 50 ) ,
	description VARCHAR( 50 ) ,
	dest VARCHAR( 255 )
)";
$sql[]='CREATE TABLE IF NOT EXISTS language_incoming (
			extension varchar(50),
			cidnum varchar(50),
			language varchar(10)
			);';

foreach($sql as $s){
	$check = $db->query($s);
	if(DB::IsError($check)) {
		die_issabelpbx("Can not create languages table\n");
	}
}

?>
