<?php
// The destinations this module provides
// returns a associative arrays with keys 'destination' and 'description'
function queues_destinations() {
	//get the list of all exisiting
	$results = queues_list(true);
	
	//return an associative array with destination and description
	if (isset($results)) {
		foreach($results as $result){
				$extens[] = array('destination' => 'ext-queues,'.$result['0'].',1', 'description' => $result['1'].' <'.$result['0'].'>');
		}
	}
	
	if (isset($extens)) 
		return $extens;
	else
		return null;
}

function queues_getdest($exten) {
	return array('ext-queues,'.$exten.',1');
}

function queues_getdestinfo($dest) {
	global $active_modules;

	if (substr(trim($dest),0,11) == 'ext-queues,') {
		$exten = explode(',',$dest);
		$exten = $exten[1];
		$thisexten = queues_get($exten);
		if (empty($thisexten)) {
			return array();
		} else {
			//$type = isset($active_modules['announcement']['type'])?$active_modules['announcement']['type']:'setup';
			return array('description' => sprintf(_("Queue %s : %s"),$exten,$thisexten['name']),
			             'edit_url' => 'config.php?display=queues&extdisplay='.urlencode($exten),
								  );
		}
	} else {
		return false;
	}
}

function queues_recordings_usage($recording_id) {
	global $active_modules;

	$results = sql("SELECT `extension`, `descr` FROM `queues_config` WHERE `callconfirm_id` = '$recording_id' OR `agentannounce_id` = '$recording_id' OR `joinannounce_id` = '$recording_id'","getAll",DB_FETCHMODE_ASSOC);
	if (empty($results)) {
		return array();
	} else {
		//$type = isset($active_modules['queues']['type'])?$active_modules['queues']['type']:'setup';
		foreach ($results as $result) {
			$usage_arr[] = array(
			  'url_query' => 'config.php?display=queues&extdisplay='.urlencode($result['extension']),
				'description' => sprintf(_("Queue: %s"),$result['descr']),
			);
		}
		return $usage_arr;
	}
}

function queues_ivr_usage($ivr_id) {
	global $active_modules;

	$results = sql("SELECT `extension`, `descr` FROM `queues_config` WHERE `ivr_id` = '$ivr_id'","getAll",DB_FETCHMODE_ASSOC);
	if (empty($results)) {
		return array();
	} else {
		foreach ($results as $result) {
			$usage_arr[] = array(
			  'url_query' => 'config.php?display=queues&extdisplay='.urlencode($result['extension']),
				'description' => sprintf(_("Queue: %s"),$result['descr']),
			);
		}
		return $usage_arr;
	}
}

function queues_check_extensions($exten=true) {
	global $active_modules;

	$extenlist = array();
	if (is_array($exten) && empty($exten)) {
		return $extenlist;
	}
	$sql = "SELECT extension, descr FROM queues_config ";
	if (is_array($exten)) {
		$sql .= "WHERE extension in ('".implode("','",$exten)."')";
	}
	$sql .= " ORDER BY extension";
	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

	//$type = isset($active_modules['queues']['type'])?$active_modules['queues']['type']:'setup';
	foreach ($results as $result) {
		$thisexten = $result['extension'];
		$extenlist[$thisexten]['description'] = sprintf(_("Queue: %s"),$result['descr']);
		$extenlist[$thisexten]['status'] = _('INUSE');
		$extenlist[$thisexten]['edit_url'] = 'config.php?display=queues&extdisplay='.urlencode($thisexten);
	}
	return $extenlist;
}

function queues_check_destinations($dest=true) {
	global $active_modules;

	$destlist = array();
	if (is_array($dest) && empty($dest)) {
		return $destlist;
	}
	$sql = "SELECT extension, descr, dest FROM queues_config";
	if ($dest !== true) {
		$sql .= " WHERE dest in ('".implode("','",$dest)."')";
	}
	$sql .= " ORDER BY extension";

	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

	//$type = isset($active_modules['announcement']['type'])?$active_modules['announcement']['type']:'setup';

	foreach ($results as $result) {
		$thisdest = $result['dest'];
		$thisid   = $result['extension'];
		$destlist[] = array(
			'dest' => $thisdest,
			'description' => sprintf(_("Queue: %s (%s)"),$result['descr'],$thisid),
			'edit_url' => 'config.php?display=queues&extdisplay='.urlencode($thisid),
		);
	}
	return $destlist;
}

function queue_change_destination($old_dest, $new_dest) {
	$sql = 'UPDATE queues_config SET dest = "' . $new_dest . '" WHERE dest = "' . $old_dest . '"';
	sql($sql, "query");
}
?>
