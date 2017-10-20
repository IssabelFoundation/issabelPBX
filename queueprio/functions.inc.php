<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//  Copyright 2006 Philippe Lindheimer
//
function queueprio_destinations() {
	global $module_page;
	$extens = array();

	// it makes no sense to point at another queueprio (and it can be an infinite loop)
	if ($module_page == 'queueprio') {
		return false;
	}

	// return an associative array with destination and description
	foreach (queueprio_list() as $row) {
		$extens[] = array('destination' => 'app-queueprio,' . $row['queueprio_id'] . ',1', 'description' => $row['description']);
	}
	return $extens;
}

function queueprio_getdest($exten) {
	return array('app-queueprio,'.$exten.',1');
}

function queueprio_getdestinfo($dest) {
	global $active_modules;

	if (substr(trim($dest),0,14) == 'app-queueprio,') {
		$exten = explode(',',$dest);
		$exten = $exten[1];
		$thisexten = queueprio_get($exten);
		if (empty($thisexten)) {
			return array();
		} else {
			$type = isset($active_modules['queueprio']['type'])?$active_modules['queueprio']['type']:'setup';
			return array('description' => sprintf(_("Queue Priority: %s"),$thisexten['description']),
			             'edit_url' => 'config.php?display=queueprio&type='.$type.'&extdisplay='.urlencode($exten),
								  );
		}
	} else {
		return false;
	}
}

function queueprio_get_config($engine) {
	global $ext;
	switch ($engine) {
		case 'asterisk':
			foreach (queueprio_list() as $row) {
					$ext->add('app-queueprio',$row['queueprio_id'], '', new ext_noop('Changing Channel to queueprio: '.$row['queue_priority'].' ('.$row['description'].')'));
					$ext->add('app-queueprio',$row['queueprio_id'], '', new ext_setvar('_QUEUE_PRIO',$row['queue_priority']));
					$ext->add('app-queueprio',$row['queueprio_id'], '', new ext_goto($row['dest']));
			}
		break;
	}
}

/**  Get a list of all queueprio
 */
function queueprio_list() {
	global $db;
	$sql = "SELECT queueprio_id, description, queue_priority, dest FROM queueprio ORDER BY description ";
	$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($results)) {
		die_issabelpbx($results->getMessage()."<br><br>Error selecting from queueprio");	
	}
	return $results;
}

function queueprio_get($queueprio_id) {
	global $db;
	$sql = "SELECT queueprio_id, description, queue_priority, dest FROM queueprio WHERE queueprio_id = ".$db->escapeSimple($queueprio_id);
	$row = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($row)) {
		die_issabelpbx($row->getMessage()."<br><br>Error selecting row from queueprio");	
	}
	
	return $row;
}

function queueprio_add($description, $queue_priority, $dest) {
	global $db, $amp_conf;
	$sql = "INSERT INTO queueprio (description, queue_priority, dest) VALUES (".
		"'".$db->escapeSimple($description)."', ".
		"'".$db->escapeSimple($queue_priority)."', ".
		"'".$db->escapeSimple($dest)."')";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getMessage().$sql);
	}
	if(method_exists($db,'insert_id')) {
		$id = $db->insert_id();
	} else {
		$id = $amp_conf["AMPDBENGINE"] == "sqlite3" ? sqlite_last_insert_rowid($db->connection) : mysql_insert_id($db->connection);
	}
	return($id);
}

function queueprio_delete($queueprio_id) {
	global $db;
	$sql = "DELETE FROM queueprio WHERE queueprio_id = ".$db->escapeSimple($queueprio_id);
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getMessage().$sql);
	}
}

function queueprio_edit($queueprio_id, $description, $queue_priority, $dest) { 
	global $db;
	$sql = "UPDATE queueprio SET ".
		"description = '".$db->escapeSimple($description)."', ".
		"queue_priority = '".$db->escapeSimple($queue_priority)."', ".
		"dest = '".$db->escapeSimple($dest)."' ".
		"WHERE queueprio_id = ".$db->escapeSimple($queueprio_id);
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getMessage().$sql);
	}
}

function queueprio_check_destinations($dest=true) {
	global $active_modules;

	$destlist = array();
	if (is_array($dest) && empty($dest)) {
		return $destlist;
	}
	$sql = "SELECT queueprio_id, dest, description FROM queueprio ";
	if ($dest !== true) {
		$sql .= "WHERE dest in ('".implode("','",$dest)."')";
	}
	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

	$type = isset($active_modules['queueprio']['type'])?$active_modules['queueprio']['type']:'setup';

	foreach ($results as $result) {
		$thisdest = $result['dest'];
		$thisid   = $result['queueprio_id'];
		$destlist[] = array(
			'dest' => $thisdest,
			'description' => sprintf(_("Queue Priority: %s"),$result['description']),
			'edit_url' => 'config.php?display=queueprio&type='.$type.'&extdisplay='.urlencode($thisid),
		);
	}
	return $destlist;
}
function queueprio_change_destination($old_dest, $new_dest) {
	$sql = 'UPDATE queueprio SET dest = "' . $new_dest . '" WHERE dest = "' . $old_dest . '"';
	sql($sql, "query");
}
?>
