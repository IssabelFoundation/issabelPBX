<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
global $db;
global $amp_conf;

if($amp_conf["AMPDBENGINE"] == "sqlite3")  {
	$sql = "
	CREATE TABLE IF NOT EXISTS `phpagiconf` (
		`phpagiid` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
		`debug` BOOL ,
		`error_handler` BOOL ,
		`err_email` VARCHAR( 50 ) ,
		`hostname` VARCHAR( 255 ) ,
		`tempdir` VARCHAR( 255 ) ,
		`festival_text2wave` VARCHAR( 255 ) ,
		`asman_server` VARCHAR( 255 ) ,
		`asman_port` INT NOT NULL ,
		`asman_user` VARCHAR( 50 ) ,
		`asman_secret` VARCHAR( 255 ) ,
		`cepstral_swift` VARCHAR( 255 ) ,
		`cepstral_voice` VARCHAR( 50 ) ,
		`setuid` BOOL ,
		`basedir` VARCHAR( 255 )
	);
	";
}
else  {
	$sql = "
	CREATE TABLE IF NOT EXISTS `phpagiconf` (
		`phpagiid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`debug` BOOL ,
		`error_handler` BOOL ,
		`err_email` VARCHAR( 50 ) ,
		`hostname` VARCHAR( 255 ) ,
		`tempdir` VARCHAR( 255 ) ,
		`festival_text2wave` VARCHAR( 255 ) ,
		`asman_server` VARCHAR( 255 ) ,
		`asman_port` INT NOT NULL ,
		`asman_user` VARCHAR( 50 ) ,
		`asman_secret` VARCHAR( 255 ) ,
		`cepstral_swift` VARCHAR( 255 ) ,
		`cepstral_voice` VARCHAR( 50 ) ,
		`setuid` BOOL ,
		`basedir` VARCHAR( 255 )
	) ENGINE = MYISAM ;
	";
}
sql($sql);

?>
