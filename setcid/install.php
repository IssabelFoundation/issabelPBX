<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";
$sql[] = "CREATE TABLE IF NOT EXISTS setcid (
    cid_id INTEGER NOT NULL PRIMARY KEY $autoincrement,
    cid_name VARCHAR( 150 ) ,
    cid_num VARCHAR( 150 ) ,
    description VARCHAR( 50 ) ,
    dest VARCHAR( 255 ),
    variables text
)";
$sql[] = 'alter table setcid change column cid_name cid_name varchar(150);';
$sql[] = 'alter table setcid change column cid_num cid_num varchar(150);';

foreach ($sql as $q) {
    $check = $db->query($q);
    if($db->IsError($check)) {
        die_issabelpbx("Can not create setcid table\n");
    }
}

// We do not want to catch errors if the field already exist
$db->query('alter table setcid add variables text;');
