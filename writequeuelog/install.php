<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";
$sql[] = "CREATE TABLE IF NOT EXISTS writequeuelog (
    qlog_id INTEGER NOT NULL PRIMARY KEY $autoincrement,
    qlog_description VARCHAR( 250 ) ,
    qlog_uniqueid VARCHAR( 150 ) ,
    qlog_queue VARCHAR( 250 ) ,
    qlog_agent VARCHAR( 250 ) ,
    qlog_event VARCHAR( 150 ) ,
    description VARCHAR ( 100 ),
    dest VARCHAR ( 255 ),
    qlog_extra text
)";

foreach ($sql as $q) {
    $check = $db->query($q);
    if($db->IsError($check)) {
        die_issabelpbx("Can not create writequeuelog table\n");
    }
}

