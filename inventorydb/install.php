<?php

global $db;
global $amp_conf;

$autoincrement = ($amp_conf["AMPDBENGINE"] == "sqlite3") ? "AUTOINCREMENT":"AUTO_INCREMENT";

$sql = "CREATE TABLE IF NOT EXISTS inventorydb (
        id INTEGER NOT NULL PRIMARY KEY $autoincrement,
	empnum varchar(10) null,
	empname varchar(20) NOT NULL,
	building varchar(150) NULL,
	floor varchar(10) NULL,
	room varchar(10) NULL,
	section varchar(6) NULL,
	cubicle varchar(6) NULL,
	desk varchar(6) NULL,
	exten  varchar(8) NULL,
	phusername varchar(10) NULL, 
	phpassword varchar(10) NULL,
	mac varchar(18) NULL,
	serial varchar(20) NULL, 
	device varchar(20) NULL, 
	distdate varchar(10) NULL, 
	ip varchar(14) NULL, 
	pbxbox varchar(20) NULL,
	extrainfo varchar(256) NULL
);";

$check = $db->query($sql);
if(DB::IsError($check)) {
        die_issabelpbx("Can not create `inventorydb` table\n");
}

?>
