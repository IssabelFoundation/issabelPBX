<?php
if (! function_exists("out")) {
	function out($text) {
		echo $text."<br />";
	}
}

if (! function_exists("outn")) {
	function outn($text) {
		echo $text;
	}
}

global $db;
global $amp_conf;

if($amp_conf["AMPDBENGINE"] == "sqlite3")  {
	$sql = "
	CREATE TABLE IF NOT EXISTS customerdb 
	(
		`id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
		`name` varchar(45) not null, 
		`addr1` varchar(150) not null,  
		`addr2` varchar(150) null, 
		`city` varchar(50) not null, 
		`state` varchar(5) not null, 
		`zip` varchar(12) null, 
		`sip` varchar(20) null, 
		`did` varchar(45) null, 
		`device` varchar(50) null, 
		`ip` varchar(20) null, 
		`serial` varchar(50) null, 
		`account` varchar(6) null, 
		`email` varchar(150) null, 
		`username` varchar(25) null, 
		`password` varchar(25) null
	);
	";
}
else  {
	$sql = "
	CREATE TABLE IF NOT EXISTS customerdb 
	(
		`id` int UNIQUE AUTO_INCREMENT,	 
		`name` varchar(45) not null, 
		`addr1` varchar(150) not null,  
		`addr2` varchar(150) null, 
		`city` varchar(50) not null, 
		`state` varchar(5) not null, 
		`zip` varchar(12) null, 
		`sip` varchar(20) null, 
		`did` varchar(45) null, 
		`device` varchar(50) null, 
		`ip` varchar(20) null, 
		`serial` varchar(50) null, 
		`account` varchar(6) null, 
		`email` varchar(150) null, 
		`username` varchar(25) null, 
		`password` varchar(25) null
	);
	";
}
$check = $db->query($sql);
if(DB::IsError($check)) {
	die_issabelpbx("Can not create annoucment table");
}

?>
