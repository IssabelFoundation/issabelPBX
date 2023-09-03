<?php

if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

$autoincrement=(preg_match("/qlite/",$amp_conf["AMPDBENGINE"])) ? "AUTOINCREMENT":"AUTO_INCREMENT";

$sql = array();

$sql['callrecording']="CREATE TABLE IF NOT EXISTS callrecording (
    callrecording_id INTEGER NOT NULL PRIMARY KEY $autoincrement,
    callrecording_mode VARCHAR( 50 ) ,
    description VARCHAR( 50 ) ,
    dest VARCHAR( 255 )
)";

$sql['callrecording_module']="CREATE TABLE IF NOT EXISTS callrecording_module (
    extension varchar(50),
    cidnum varchar(50) default '',
    callrecording varchar(10),
    display varchar(20)
)";

foreach($sql as $t=>$s){
    if(preg_match("/mysql/",$amp_conf["AMPDBENGINE"]))  { $s.=" DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";   }
    $check = $db->query($s);
    if(DB::IsError($check)) {
        die_issabelpbx("Can not create $t table\n");
    }
}

?>
