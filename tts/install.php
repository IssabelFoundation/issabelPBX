<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";
$sql['tts'] = "CREATE TABLE IF NOT EXISTS tts (
    tts_id INTEGER NOT NULL PRIMARY KEY $autoincrement,
    tts_description VARCHAR(250),
    tts_engine VARCHAR(200),
    tts_parameters text,
    tts_text text,
    dest VARCHAR(250)
)";

$sql['tts_engines'] = "CREATE TABLE IF NOT EXISTS tts_engines (
    ttsengine_id INTEGER NOT NULL PRIMARY KEY $autoincrement,
    ttsengine_description VARCHAR(250) ,
    ttsengine_engine VARCHAR(100),
    ttsengine_cmd text,
    ttsengine_template text
)";

foreach ($sql as $tablename=>$q) {
    $check = $db->query($q);
    if($db->IsError($check)) {
        die_issabelpbx("Can not create $tablename table\n");
    }
}

