<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;
global $amp_conf;

$autoincrement = ($amp_conf["AMPDBENGINE"] == "sqlite3") ? "AUTOINCREMENT":"AUTO_INCREMENT";

$sql = "CREATE TABLE IF NOT EXISTS callback (
	callback_id INTEGER NOT NULL PRIMARY KEY $autoincrement,
	description VARCHAR( 50 ) ,
	callbacknum VARCHAR( 100 ) ,
	destination VARCHAR( 50 ) ,
	sleep INTEGER,
	deptname VARCHAR( 50 )
);";

$check = $db->query($sql);
if (DB::IsError($check)) {
	die_issabelpbx( "Can not create `callback` table: " . $check->getMessage() .  "\n");
}


// Version 1.1 upgrade - add sleep time.
$sql = "SELECT sleep FROM callback";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
	sql('ALTER TABLE callback ADD COLUMN sleep INT DEFAULT 0');
	}

$results = array();
$sql = "SELECT callback_id, destination FROM callback";
$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
if (!DB::IsError($results)) { // error - table must not be there
	foreach ($results as $result) {
		$old_dest  = $result['destination'];
		$callback_id    = $result['callback_id'];

		$new_dest = merge_ext_followme(trim($old_dest));
		if ($new_dest != $old_dest) {
			$sql = "UPDATE callback SET destination = '$new_dest' WHERE callback_id = $callback_id  AND destination = '$old_dest'";
			$results = $db->query($sql);
			if(DB::IsError($results)) {
				die_issabelpbx($results->getMessage());
			}
		}
	}
}

?>
