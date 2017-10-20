<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;
global $amp_conf;

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";

$sql = "CREATE TABLE IF NOT EXISTS disa (
	disa_id INTEGER NOT NULL PRIMARY KEY $autoincrement,
	displayname VARCHAR( 50 ),
	pin VARCHAR ( 50 ),
	cid VARCHAR ( 50 ),
	context VARCHAR ( 50 ),
	digittimeout INTEGER,
	resptimeout INTEGER,
	needconf VARCHAR( 10 ),
	hangup VARCHAR( 10 ),
	keepcid TINYINT(1) NOT NULL DEFAULT 1
);";

$check = $db->query($sql);
if (DB::IsError($check)) {
	die( "Can not create `disa` table: " . $check->getMessage() .  "\n");
}


// Manage upgrade from DISA 1.0
// r2.0 Add Timeouts and add wait for confirmation
$sql = "SELECT digittimeout FROM disa";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new fields - Digit Timeout
	$sql = 'ALTER TABLE disa ADD COLUMN digittimeout INT DEFAULT "5"';
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo());
	}
	// Response Timeout
	$sql = 'ALTER TABLE disa ADD COLUMN resptimeout INT DEFAULT "10"';
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo());
	}
	$sql = 'ALTER TABLE disa ADD COLUMN needconf VARCHAR ( 10 )  DEFAULT ""';
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo());
	}
}

//update to 2.5, add hangup
//  ALTER TABLE `disa` CHANGE `hangup` `hangup` VARCHAR( 10 )

$sql = "SELECT hangup FROM disa";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	$sql = 'ALTER TABLE `disa` ADD COLUMN `hangup` VARCHAR( 10 )';
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo());
	}
}

$sql = "SELECT keepcid FROM disa";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
        // add new fields - KEEPCID
        $sql = 'ALTER TABLE disa ADD COLUMN keepcid TINYINT(1) NOT NULL DEFAULT "1"';
        $result = $db->query($sql);
        if(DB::IsError($result)) {
                die_issabelpbx($result->getDebugInfo());
        }
}
?>
