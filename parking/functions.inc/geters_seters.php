<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

/* Parking APIs
 */

/** parking_get
 * Short get parking settings
 * Long get the parking lot settings
 *
 * @author Philippe Lindheimer
 * @param mixed $id
 * @return array
 */
function parking_get($id='') {
	global $db;

	$sql = "SELECT * FROM parkplus";
	if ($id == 'all' || $id == '') {
		$res = sql($sql,'getAll',DB_FETCHMODE_ASSOC);
		foreach($res as $vq) {
			$results[$vq['id']] = $vq;
		}
	} else {
	    $sql = "SELECT * FROM parkplus WHERE id=$id";
		$results = sql($sql,'getRow',DB_FETCHMODE_ASSOC);
	}
	return $results;
}

/** parking_save
 * Short insert or update parking settings
 * Long takes array of settings to update, missing settings will 
 * get default values, if id not present it will insert a new row.
 * Returns the id of the current or newly inserted record or 
 * boolean false upon a failure.
 *
 * @author Philippe Lindheimer
 * @param array $parms
 * @return mixed
 */
function parking_save($parms=array()) {
	global $db, $amp_conf;

	if (!empty($parms['id'])) {
		$var['id'] = $db->escapeSimple($parms['id']);
	}
	$var['name'] = "Parking Lot";
	$var['type'] = 'public';
	$var['parkext'] = '';
	$var['parkpos'] = '';
	$var['numslots'] = 4;
	$var['parkingtime'] = 45;
	$var['parkedmusicclass'] = 'default';
	$var['generatehints'] = 'yes';
	$var['generatefc'] = 'yes';
	$var['findslot'] = 'first';
	$var['parkedplay'] = 'both';
	$var['parkedcalltransfers'] = 'caller';
	$var['parkedcallreparking'] = 'caller';
	$var['alertinfo'] = '';
	$var['cidpp'] = '';
	$var['autocidpp'] = 'none';
	$var['announcement_id'] = null;
	$var['comebacktoorigin'] = 'yes';
	$var['dest'] = '';

	foreach ($var as $k => $v) {
		if (isset($parms[$k])) {
			$var[$k] = $db->escapeSimple($parms[$k]);
		}
	}
	$var['defaultlot'] = isset($var['id']) && $var['id'] == 1 ? 'yes' : 'no';

	$fields = "name, type, parkext, parkpos, numslots, parkingtime, parkedmusicclass, generatehints, generatefc, findslot, parkedplay, 
		parkedcalltransfers, parkedcallreparking, alertinfo, cidpp, autocidpp, announcement_id, comebacktoorigin, dest, defaultlot";
	$holders = "?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";

	if (empty($var['id'])) {
		$sql = "INSERT INTO parkplus ($fields) VALUES ($holders)";
	} else {
		$sql = "REPLACE INTO parkplus (id, $fields) VALUES (?,$holders)";
	}	

	$res = $db->query($sql,$var);
	if (DB::IsError($res)) {
		$id = false;
		// TODO log error
	} elseif (empty($var['id'])) {
		if(method_exists($db,'insert_id')) {
			$id = $db->insert_id();
		} else {
			$id = $amp_conf["AMPDBENGINE"] == "sqlite3" ? sqlite_last_insert_rowid($db->connection) : mysql_insert_id($db->connection);
		}
        needreload();
	} else {
		$id = $var['id'];
        needreload();
	}
	return $id;
}
