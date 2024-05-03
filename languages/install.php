<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

$autoincrement=(preg_match("/qlite/",$amp_conf["AMPDBENGINE"])) ? "AUTOINCREMENT":"AUTO_INCREMENT";

$sql = array();

$sql['languages']="CREATE TABLE IF NOT EXISTS languages (
	language_id INTEGER NOT NULL PRIMARY KEY $autoincrement,
	lang_code VARCHAR( 50 ) ,
	description VARCHAR( 50 ) ,
	dest VARCHAR( 255 )
)";

$sql['language_incoming']='CREATE TABLE IF NOT EXISTS language_incoming (
    extension varchar(50),
    cidnum varchar(50),
    language varchar(10)
)';

foreach($sql as $t=>$s){
    if(preg_match("/mysql/",$amp_conf["AMPDBENGINE"]))  { $s.=" DEFAULT CHARSET=utf8mb4";   }
	$check = $db->query($s);
	if(DB::IsError($check)) {
		die_issabelpbx("Can not create $t table\n");
	}
}

?>
