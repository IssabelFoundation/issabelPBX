<?php /* $Id: function.inc.php  $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2018 Issabel Foundation
//	Copyright 2013 Schmooze Com Inc.
//  Copyright (C) 2010 Mikael Carlsson
//

define ('DEFAULT_MSG', -1);
define ('CONGESTION_TONE', -2);
define ('INFO_TONE', -3);

function outroutemsg_get_config($engine) {
	global $db;
	global $ext;
	global $version;

	switch($engine) {
		case "asterisk":

			/* here we add macro-outisbusy with the following actions:
			 * if ( EMERGENCYROUTE=YES ):
			 * 	choose Emergency Message over everything else, ANSWER CALL
			 * if ( INTRACOMPANYROUTE=YES ):
			 * 	choose Intracompany Message over default
			 * Use default
			 */

		$contextname = 'macro-outisbusy';

		$outroutemsg_ids = outroutemsg_get();
		$exten = 's';

		$ext->add($contextname, $exten, '', new ext_progress());
		$ext->add($contextname, $exten, '', new ext_gotoif('$["${EMERGENCYROUTE}" = "YES"]', 'emergency,1'));
		$ext->add($contextname, $exten, '', new ext_gotoif('$["${INTRACOMPANYROUTE}" = "YES"]', 'intracompany,1'));
		$ext->add($contextname, $exten, '', new ext_gotoif('$["${HANGUPCAUSE}" = "1"]',  'unallocated,1'));
		$ext->add($contextname, $exten, '', new ext_gotoif('$["${HANGUPCAUSE}" = "21"]', 'unallocated,1'));

		switch ($outroutemsg_ids['default_msg_id']) {
			case DEFAULT_MSG:
				$ext->add($contextname, $exten, '', new ext_playback("all-circuits-busy-now&pls-try-call-later, noanswer"));
				break;
			case CONGESTION_TONE:
				$ext->add($contextname, $exten, '', new ext_playtones("congestion"));
				break;
			case INFO_TONE:
				$ext->add($contextname, $exten, '', new ext_playtones("info"));
				break;
			default:
				$message = recordings_get_file($outroutemsg_ids['default_msg_id']);
				$message = ($message != "") ? $message : "all-circuits-busy-now&pls-try-call-later";
				$ext->add($contextname, $exten, '', new ext_playback("$message, noanswer"));
		}
		$ext->add($contextname, $exten, '', new ext_congestion());
		$ext->add($contextname, $exten, '', new ext_hangup());

		$exten = 'intracompany';
		switch ($outroutemsg_ids['intracompany_msg_id']) {
			case DEFAULT_MSG:
				$ext->add($contextname, $exten, '', new ext_playback("all-circuits-busy-now&pls-try-call-later, noanswer"));
				break;
			case CONGESTION_TONE:
				$ext->add($contextname, $exten, '', new ext_playtones("congestion"));
				break;
			case INFO_TONE:
				$ext->add($contextname, $exten, '', new ext_playtones("info"));
				break;
			default:
				$message = recordings_get_file($outroutemsg_ids['intracompany_msg_id']);
				$message = ($message != "") ? $message : "all-circuits-busy-now&pls-try-call-later";
				$ext->add($contextname, $exten, '', new ext_playback("$message, noanswer"));
		}
		$ext->add($contextname, $exten, '', new ext_congestion());
		$ext->add($contextname, $exten, '', new ext_hangup());

		$exten = 'emergency';
		switch ($outroutemsg_ids['emergency_msg_id']) {
			case DEFAULT_MSG:
				$ext->add($contextname, $exten, '', new ext_playback("all-circuits-busy-now&pls-try-call-later"));
				break;
			case CONGESTION_TONE:
				$ext->add($contextname, $exten, '', new ext_playtones("congestion"));
				break;
			case INFO_TONE:
				$ext->add($contextname, $exten, '', new ext_playtones("info"));
				break;
			default:
				$message = recordings_get_file($outroutemsg_ids['emergency_msg_id']);
				$message = ($message != "") ? $message : "all-circuits-busy-now&pls-try-call-later";
				$ext->add($contextname, $exten, '', new ext_playback("$message"));
		}
		$ext->add($contextname, $exten, '', new ext_congestion());
		$ext->add($contextname, $exten, '', new ext_hangup());

		$exten = 'unallocated';
		switch ($outroutemsg_ids['unallocated_msg_id']) {
			case DEFAULT_MSG:
				$ext->add($contextname, $exten, '', new ext_playback("you-dialed-wrong-number"));
				break;
			case CONGESTION_TONE:
				$ext->add($contextname, $exten, '', new ext_playtones("congestion"));
				break;
			case INFO_TONE:
				$ext->add($contextname, $exten, '', new ext_playtones("info"));
				break;
			default:
				$message = recordings_get_file($outroutemsg_ids['unallocated_msg_id']);
				$message = ($message != "") ? $message : "you-dialed-wrong-number";
				$ext->add($contextname, $exten, '', new ext_playback("$message"));
		}
		$ext->add($contextname, $exten, '', new ext_congestion());
		$ext->add($contextname, $exten, '', new ext_hangup());

	}
}

function outroutemsg_add($default_msg_id, $intracompany_msg_id, $emergency_msg_id, $no_answer_msg_id, $invalidnmbr_msg_id, $unallocated_msg_id) {
	global $db;

	$default_msg_id      = $db->escapeSimple($default_msg_id);
	$intracompany_msg_id = $db->escapeSimple($intracompany_msg_id);
	$emergency_msg_id    = $db->escapeSimple($emergency_msg_id);
	$unallocated_msg_id  = $db->escapeSimple($unallocated_msg_id);
	$no_answer_msg_id    = $db->escapeSimple($no_answer_msg_id);
	$invalidnmbr_msg_id  = $db->escapeSimple($invalidnmbr_msg_id);
	
	// in future will do in a outroutemsg_del but not needed for now
	//
	$sql = "DELETE FROM outroutemsg WHERE `keyword` IN  ('default_msg_id', 'intracompany_msg_id', 'emergency_msg_id', 'no_answer_msg_id', 'invalidnmbr_msg_id', 'unallocated_msg_id')";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getMessage().$sql);
	}

	$insert_fields =array(
		array('default_msg_id', "$default_msg_id"),
		array('intracompany_msg_id', "$intracompany_msg_id"),
		array('emergency_msg_id', "$emergency_msg_id"),
		array('unallocated_msg_id', "$unallocated_msg_id"),
		array('no_answer_msg_id', "$no_answer_msg_id"),
		array('invalidnmbr_msg_id', "$invalidnmbr_msg_id"),
		);

	$compiled = $db->prepare('INSERT INTO outroutemsg (keyword, data) values (?,?)');
	$result = $db->executeMultiple($compiled,$insert_fields);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo()."<br><br>".'error adding to outroutemsg table');
	}
}

function outroutemsg_get() {
	global $db;
	$sql = "SELECT keyword, data FROM outroutemsg";
	$results = $db->getAssoc($sql);
	if(DB::IsError($results)) {
		$results = array();
	}
	$results['default_msg_id']      = isset($results['default_msg_id'])      ? $results['default_msg_id']      : DEFAULT_MSG;
	$results['intracompany_msg_id'] = isset($results['intracompany_msg_id']) ? $results['intracompany_msg_id'] : DEFAULT_MSG;
	$results['emergency_msg_id']    = isset($results['emergency_msg_id'])    ? $results['emergency_msg_id']    : DEFAULT_MSG;
	$results['unallocated_msg_id']  = isset($results['unallocated_msg_id'])  ? $results['unallocated_msg_id']  : DEFAULT_MSG;
	$results['no_answer_msg_id']    = isset($results['no_answer_msg_id'])    ? $results['no_answer_msg_id']    : DEFAULT_MSG;
	$results['invalidnmbr_msg_id']  = isset($results['invalidnmbr_msg_id'])  ? $results['invalidnmbr_msg_id']  : DEFAULT_MSG;
	return $results;
}

function outroutemsg_recordings_usage($recording_id) {
	global $active_modules;

	$my_id = sql("SELECT `data` FROM `outroutemsg` WHERE `data` = '$recording_id'","getOne");
	if (!isset($my_id) || $my_id == '') {
		return array();
	} else {
		$type = isset($active_modules['outroutemsg']['type'])?$active_modules['outroutemsg']['type']:'tool';
		$usage_arr[] = array(
			'url_query' => 'config.php?type='.$type.'&display=outroutemsg',
			'description' => _("Route Congestion Messages"),
		);
		return $usage_arr;
	}
}

?>
