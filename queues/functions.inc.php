<?php /* $id:$ */
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
include_once(dirname(__FILE__) . '/functions.inc/destination_registry.php');
include_once(dirname(__FILE__) . '/functions.inc/dialplan.php');
include_once(dirname(__FILE__) . '/functions.inc/hook_core.php');
include_once(dirname(__FILE__) . '/functions.inc/hook_ivr.php');
include_once(dirname(__FILE__) . '/functions.inc/geters_seters.php');
include_once(dirname(__FILE__) . '/functions.inc/queue_conf.php');
include_once(dirname(__FILE__) . '/functions.inc/cron.php');

function queues_timeString($seconds, $full = false) {
	if ($seconds == 0) {
		return "0 ".($full ? _("seconds") : "s");
	}

	$minutes = floor($seconds / 60);
	$seconds = $seconds % 60;

	$hours = floor($minutes / 60);
	$minutes = $minutes % 60;

	$days = floor($hours / 24);
	$hours = $hours % 24;

	if ($full) {
 		return substr(
		              ($days ? $days." "._("day").(($days == 1) ? "" : "s").", " : "").
		              ($hours ? $hours." ".(($hours == 1) ? _("hour") : _("hours")).", " : "").
		              ($minutes ? $minutes." ".(($minutes == 1) ? _("minute") : _("minutes")).", " : "").
		              ($seconds ? $seconds." ".(($seconds == 1) ? _("second") : _("seconds")).", " : ""),
		              0, -2);
	} else {
		return substr(($days ? $days."d, " : "").($hours ? $hours."h, " : "").($minutes ? $minutes."m, " : "").($seconds ? $seconds."s, " : ""), 0, -2);
	}
}


function queues_check_compoundrecordings() {
	global $db;

	$compound_recordings = array();
	$sql = "SELECT extension, descr, agentannounce_id, ivr_id FROM queues_config WHERE (ivr_id != 'none' AND ivr_id != '') OR agentannounce_id != ''";
	$results = sql($sql, "getAll",DB_FETCHMODE_ASSOC);

	if (function_exists('ivr_get_details')) {
		$ivr_details = ivr_get_details();
		foreach ($ivr_details as $item) {
			$ivr_hash[$item['id']] = $item;
		}
		$check_ivr = true;
	} else {
		$check_ivr = false;
	}

	foreach ($results as $result) {
		$agentannounce = $result['agentannounce_id'] ? recordings_get_file($result['agentannounce_id']):'';
		if (strpos($agentannounce,"&") !== false) {
			$compound_recordings[] = array(
				                       	'extension' => $result['extension'],
															 	'descr' => $result['descr'],
															 	'error' => _("Agent Announce Msg"),
														 	);
		}
		if ($result['ivr_id'] != 'none' && $result['ivr_id'] != '' && $check_ivr) {
			$id = $ivr_hash[$result['ivr_id']]['announcement_id'];
			$announce = $id ? recordings_get_file($id) : '';
			if (strpos($announce,"&") !== false) {
				$compound_recordings[] = array(
				                       		'extension' => $result['extension'],
															 		'descr' => $result['descr'],
															 		'error' => sprintf(_("IVR Announce: %s"),$ivr_hash[$result['ivr_id']]['displayname']),
														 		);
			}
		}
	}
	return $compound_recordings;
}


//attach hooks
function queues_configpageinit($pagename) {
	global $currentcomponent;

	$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$extension = isset($_REQUEST['extension'])?$_REQUEST['extension']:null;
	$tech_hardware = isset($_REQUEST['tech_hardware'])?$_REQUEST['tech_hardware']:null;
	$display = isset($_REQUEST['display'])?$_REQUEST['display']:null;
	
	//hook in to ivr's
	if ($display == 'ivr') {
		$currentcomponent->addprocessfunc('queues_configprocess_ivr');
		// We only want to hook 'users' or 'extensions' pages.
	} elseif ($pagename != 'users' && $pagename != 'extensions') {
		return true;
	}
	// On a 'new' user, 'tech_hardware' is set, and there's no extension. Hook into the page.
	if ($tech_hardware != null || $pagename == 'users') {
		queues_applyhooks();
		$currentcomponent->addprocessfunc('queues_configprocess', 8);
	} elseif ($action=="add") {
		// We don't need to display anything on an 'add', but we do need to handle returned data.
		$currentcomponent->addprocessfunc('queues_configprocess', 8);
	} elseif ($extdisplay != '') {
		// We're now viewing an extension, so we need to display _and_ process.
		queues_applyhooks();
		$currentcomponent->addprocessfunc('queues_configprocess', 8);
	}
}

/* Get a list of all members that exists for any queue, if any */
function queues_get_members() {
		global $db;
		$sql = "SELECT data FROM queues_details WHERE keyword = 'member' order by flags";
		$results = $db->getCol($sql);
		return $results;
}
