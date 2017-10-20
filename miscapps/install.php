<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;
global $amp_conf;

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";
$sql = "CREATE TABLE IF NOT EXISTS miscapps (
	miscapps_id INTEGER NOT NULL PRIMARY KEY $autoincrement,
	ext VARCHAR( 50 ) ,
	description VARCHAR( 50 ) ,
	dest VARCHAR( 255 )
)";

$check = $db->query($sql);
if(DB::IsError($check)) {
	die_issabelpbx("Can not create miscdests table\n");
}
$results = array();
$sql = "SELECT miscapps_id, dest FROM miscapps";
$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
if (!DB::IsError($results)) { // error - table must not be there
	foreach ($results as $result) {
		$old_dest    = $result['dest'];
		$miscapps_id = $result['miscapps_id'];

		$new_dest = merge_ext_followme(trim($old_dest));
		if ($new_dest != $old_dest) {
			$sql = "UPDATE miscapps SET dest = '$new_dest' WHERE miscapps_id = $miscapps_id  AND dest = '$old_dest'";
			$results = $db->query($sql);
			if(DB::IsError($results)) {
				die_issabelpbx($results->getMessage());
			}
		}
	}
}

?>
