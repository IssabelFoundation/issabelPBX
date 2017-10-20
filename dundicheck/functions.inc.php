<?php /* $Id: page.ringgroups.php 5340 2007-12-04 19:10:53Z p_lindheimer $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

function dundicheck_check_extensions($exten=true) {
	global $active_modules;
	global $astman;

	$extenlist = array();
	if ((is_array($exten) && empty($exten)) || $exten === true) {
		return $extenlist;
	}

	if ($astman) {

		// Get a list of the DUNDi trunks configured, if none then we just exit
		//
		$dundimap = array();	

		$trunklist = core_trunks_list(true);
		if (is_array($trunklist)) {
			foreach ($trunklist as $trunkprops) {
				if (trim($trunkprops['value']) == 'on') {
					// value of on is disabled and for zap we don't create a context
					continue;
				}
				switch ($trunkprops['tech']) {
					case 'DUNDI':
						$dundimap[] = $trunkprops['name'];
						break;
					default:
				}
			}
		}
		if (empty($dundimap)) {
			return array();
		}

		// Now look through the extensions and lookup to see if DUNDi knows about them
		//
		$results = array();
		foreach ($exten as $num) {
			$foundone = dundicheck_lookup($num, $dundimap);
			if (!empty($foundone)) {
				$results[] = array('exten' => $num,
				                   'description' => $foundone
												 );
			}
		}

		$type = isset($active_modules['customappsreg']['type'])?$active_modules['customappsreg']['type']:'tool';

		foreach ($results as $result) {
			$thisexten = $result['exten'];
			$extenlist[$thisexten]['description'] = _("DUNDi: ").$result['description'];
			$extenlist[$thisexten]['status'] = 'INUSE';
			$extenlist[$thisexten]['edit_url'] = 'config.php?display=dundicheck&dundiconflict=true&extdisplay='.urlencode($thisexten);
		}
		return $extenlist;
	} else {
		return array();
	}
}

function dundicheck_lookup($num, $map) {
	global $astman;
	if ($astman) {
		foreach ($map as $lookup) {
			$response = $astman->send_request('Command',array('Command'=>"dundi lookup $num@$lookup"));
			if (strstr($response['data'],$num.' (EXISTS)')) {
				$parser = explode("@",$response['data']);
				$parser = explode("\n",$parser[1]);
				return $parser[0];
			}
		}
		// if sound would have returned so return null
		return null;
	}
}

function dundicheck_lookup_all($num) {
	global $astman;

	$dundimap = array();	

	$trunklist = core_trunks_list(true);
	if (is_array($trunklist)) {
		foreach ($trunklist as $trunkprops) {
			if (trim($trunkprops['value']) == 'on') {
				// value of on is disabled and for zap we don't create a context
				continue;
			}
			switch ($trunkprops['tech']) {
				case 'DUNDI':
					$dundimap[] = $trunkprops['name'];
					break;
				default:
			}
		}
	}

	$results = array();
	if ($astman) {
		foreach ($dundimap as $lookup) {
			$response = $astman->send_request('Command',array('Command'=>"dundi lookup $num@$lookup"));
			if (strstr($response['data'],$num.' (EXISTS)')) {
				$results[$lookup] = $response['data'];
			}
		}
		// if sound would have returned so return null
		return $results;
	}
}

?>
