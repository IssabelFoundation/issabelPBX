<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
 /* $Id: functions.inc.php 4024 2007-06-09 03:09:16Z p_lindheimer $ */
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
// Class To Create, Access and Change DAYNIGHT objects in the dialplan
//
class dayNightObject {

	var $id;

	// contstructor
	function dayNightObject($item) {
		global $amp_conf;

		$this->id = $item;

		if ($amp_conf['USEDEVSTATE']) {
			$engine_info = engine_getinfo();
			$version = $engine_info['version'];
			$this->DEVSTATE = version_compare($version, "1.6", "ge") ? "DEVICE_STATE" : "DEVSTATE";
		} else {
			$this->DEVSTATE = DEVICE_STATE;
		}
	}
		
	function getState() {
		global $astman;

		if ($astman != null) {
			$mode = $astman->database_get("DAYNIGHT","C".$this->id);
			if ($mode != "DAY" && $mode != "NIGHT") {
				// TODO: should this return an error?
				return false;
			} else {
				return $mode;
			}
		} else {
			die_issabelpbx("No open connection to asterisk manager, can not access object.");
		}
	}

	function setState($state) {
		global $astman;

		if ($this->getState() === false) {
			die_issabelpbx("You must create the object before setting the state.");
			return false;
		} else {
			switch ($state) {
				case "DAY":
				case "NIGHT":
					if ($astman != null) {
						$astman->database_put("DAYNIGHT","C".$this->id,$state);
						if ($this->DEVSTATE) {
							$value_opt = ($state  == 'DAY') ? 'NOT_INUSE' : 'INUSE';
							$astman->set_global($this->DEVSTATE."(Custom:DAYNIGHT".$this->id.")", $value_opt);
						}
					} else {
						die_issabelpbx("No open connection to asterisk manager, can not access object.");
					}
					break;
				default:
					die_issabelpbx("Invalid state: $state");
					break;
			}
		}
	}

	function create($state="DAY") {
		global $astman;

		$current_state = $this->getState();
		if ($current_state !== false) {
			die_issabelpbx("Object already exists and is in state: $current_state, you must delete it first");
			return false;
		} else {
			switch ($state) {
				case "DAY":
				case "NIGHT":
					if ($astman != null) {
						$astman->database_put("DAYNIGHT","C".$this->id,$state);
						if ($this->DEVSTATE) {
							$value_opt = ($state  == 'DAY') ? 'NOT_INUSE' : 'INUSE';
							$astman->set_global($this->DEVSTATE."(Custom:DAYNIGHT".$this->id.")", $value_opt);
						}
					} else {
						die_issabelpbx("No open connection to asterisk manager, can not access object.");
					}
					break;
				default:
					die_issabelpbx("Invalid state: $state");
					break;
			}
		}
	}

	function del() {
		global $astman;

		if ($astman != null) {
			$astman->database_del("DAYNIGHT","C".$this->id);
		} else {
			die_issabelpbx("No open connection to asterisk manager, can not access object.");
		}
	}
}

// The destinations this module provides
// returns a associative arrays with keys 'destination' and 'description'
function daynight_destinations() {

	$list = daynight_list();
	foreach ($list as $item) {
		$dests = daynight_get_obj($item['ext']);
		if (!isset($dests['day']) || !isset($dests['night'])) {
			continue;
		}
		$description = $item['dest'] != ""?$item['dest']:"Call Flow Toggle";
		$description = "(".$item['ext'].") ".$description;
		$extens[] = array('destination' => 'app-daynight,'.$item['ext'].',1', 'description' => $description);
	}

	// return an associative array with destination and description
	if (isset($extens)) 
		return $extens;
	else
		return null;
}

function daynight_getdest($exten) {
	return array('app-daynight,'.$exten.',1');
}

function daynight_getdestinfo($dest) {
	global $active_modules;

	if (substr(trim($dest),0,13) == 'app-daynight,') {
		$exten = explode(',',$dest);
		$exten = $exten[1];

		$thisexten = array();
		$thislist = daynight_list($exten);
		foreach ($thislist as $item) {
			if ($item['ext'] == $exten) {
				$thisexten = $item;
				break;
			}
		}
		if (empty($thisexten)) {
			return array();
		} else {
			//$type = isset($active_modules['announcement']['type'])?$active_modules['announcement']['type']:'setup';
			return array('description' => sprintf(_("Call Flow Toggle (%s) : %s"),$exten,$thisexten['dest']),
			             'edit_url' => 'config.php?display=daynight&itemid='.urlencode($exten).'&action=edit',
								  );
		}
	} else {
		return false;
	}
}

function daynight_get_config($engine) {
	global $ext;

	switch($engine) {
		case "asterisk":

			$id = "app-daynight"; // The context to be included

			$list = daynight_list();

			foreach ($list as $item) {
				$dests = daynight_get_obj($item['ext']);
				$ext->add($id, $item['ext'], '', new ext_gotoif('$["${DB(DAYNIGHT/C${EXTEN})}" = "NIGHT"]',$dests['night'],$dests['day']));
			}

			daynight_toggle();

			break;
	}
}

function daynight_toggle() {
	global $ext;
	global $amp_conf;
	global $version;

	$list = daynight_list();
	$passwords = daynight_passwords();
	$got_code = false;

	$day_recording = daynight_recording('day');
	$night_recording = daynight_recording('night');
	
	$id = "app-daynight-toggle"; // The context to be included
	foreach ($list as $item) {
		$index = $item['ext'];
		$fcc = new featurecode('daynight', 'toggle-mode-'.$index);
		$c = $fcc->getCodeActive();
		unset($fcc);
		if (!$c) {
			continue;
		}
		$got_code = true;
		$ext->addHint($id, $c, 'Custom:DAYNIGHT'.$index);
		$ext->add($id, $c, '', new ext_macro('user-callerid'));
		$ext->add($id, $c, '', new ext_answer(''));
		$ext->add($id, $c, '', new ext_wait('1'));
		if (isset($passwords[$index]) && trim($passwords[$index]) != "" && ctype_digit(trim($passwords[$index]))) {
			$ext->add($id, $c, '', new ext_authenticate($passwords[$index]));
		}
		$ext->add($id, $c, '', new ext_setvar('INDEXES', $index));
    // Depends on featurecode.sln which is provided in core's sound files
    //
		$day_file = "beep&silence/1&featurecode&digits/${index}&de-activated";
		$night_file = "beep&silence/1&featurecode&digits/${index}&activated";
		if (function_exists('recordings_get_file')) {
		  if ($day_recording[$index] != 0 ) { $day_file = recordings_get_file ($day_recording[$index]); }
		  if ($night_recording[$index] != 0 ) { $night_file = recordings_get_file ($night_recording[$index]); }
		  }
		$ext->add($id, $c, '', new ext_setvar('DAYREC', $day_file));	
		$ext->add($id, $c, '', new ext_setvar('NIGHTREC', $night_file));	
		$ext->add($id, $c, '', new ext_goto($id.',s,1'));
	}

	if ($got_code) {
		$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

		$fcc = new featurecode('daynight', 'toggle-mode-all');
		$c = $fcc->getCodeActive();
		unset($fcc);
		if ($c) {
			$ext->add($id, $c, '', new ext_macro('user-callerid'));
			$ext->add($id, $c, '', new ext_goto($id.',${EXTEN}*${AMPUSER},1'));

			$userFCs = array();
			if ($bmo && $bmo->Cos && $bmo->Cos->isLicensed()) {
				$cos = $bmo->Cos;
			} else if (function_exists('cos_islicenced') && cos_islicenced()) {
				$cos = Cos::create();
			}

			if ($cos) {
				$allCos = $cos->getAllCos();
				foreach ($allCos as $cos_name) {
					$all = $cos->getAll($cos_name);

					foreach ($all['members'] as $key => $val) {
						$userFCs[$key] = array_merge(($userFCs[$key] ? $userFCs[$key] : array()), $all['fcallow']);
					}
				}
			}

			$users = core_users_list();
			foreach ($users as $user) {
				$exten = $user[0];

				$indexes = '';
				$hint = '';
				foreach ($list as $item) {
					if (count($userFCs) > 1 && (!isset($userFCs[$exten]) || !isset($userFCs[$exten]['toggle-mode-' . $item['ext']]))) {
						continue;
					}
					$indexes.= '&' . $item['ext'];
					$hint.= '&Custom:DAYNIGHT' . $item['ext'];
				}
				$indexes = ltrim($indexes, '&');
				$hint = ltrim($hint, '&');

				$ext->addHint($id, $c . '*' . $exten, $hint);

				if (strlen($indexes) == 0) {
					$ext->add($id, $c . '*' . $exten, '', new ext_hangup(''));
					continue;
				}

				$ext->add($id, $c . '*' . $exten, '', new ext_setvar('INDEXES', $indexes));

				$day_file = "beep&silence/1&featurecode&de-activated";
				$night_file = "beep&silence/1&featurecode&activated";
				$ext->add($id, $c . '*' . $exten, '', new ext_setvar('DAYREC', $day_file));
				$ext->add($id, $c . '*' . $exten, '', new ext_setvar('NIGHTREC', $night_file));
				$ext->add($id, $c . '*' . $exten, '', new ext_goto($id.',s,1'));
			}
		}

		$c='s';
		/* If any are on, all will be turned off.
		 * Otherwise, all will be turned on.
		 */
		$ext->add($id, $c, '', new ext_setvar('LOOPCNT', '${FIELDQTY(INDEXES,&)}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '1'));
		$ext->add($id, $c, 'begin1', new ext_setvar('INDEX', '${CUT(INDEXES,&,${ITER})}'));

		$ext->add($id, $c, '', new ext_setvar('MODE', '${DB(DAYNIGHT/C${INDEX})}'));
		$ext->add($id, $c, '', new ext_gotoif('$["${MODE}" != "NIGHT"]', 'end1'));

		$ext->add($id, $c, '', new ext_setvar('DAYNIGHTMODE', 'NIGHT'));

		$ext->add($id, $c, 'end1', new ext_setvar('ITER', '$[${ITER} + 1]'));
		$ext->add($id, $c, '', new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin1'));

		$ext->add($id, $c, '', new ext_setvar('LOOPCNT', '${FIELDQTY(INDEXES,&)}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '1'));
		$ext->add($id, $c, 'begin2', new ext_setvar('INDEX', '${CUT(INDEXES,&,${ITER})}'));

		$ext->add($id, $c, '', new ext_gotoif('$["${DAYNIGHTMODE}" = "NIGHT"]', 'day', 'night'));

		$ext->add($id, $c, 'day', new ext_setvar('DB(DAYNIGHT/C${INDEX})', 'DAY'));
		$ext->add($id, $c, '', new ext_setvar($amp_conf['AST_FUNC_DEVICE_STATE'].'(Custom:DAYNIGHT${INDEX})', 'NOT_INUSE'));
		$ext->add($id, $c, 'hook_day', new ext_goto('end2'));

		$ext->add($id, $c, 'night', new ext_setvar('DB(DAYNIGHT/C${INDEX})', 'NIGHT'));
		$ext->add($id, $c, '', new ext_setvar($amp_conf['AST_FUNC_DEVICE_STATE'].'(Custom:DAYNIGHT${INDEX})', 'INUSE'));
		$ext->add($id, $c, 'hook_night', new ext_goto('end2'));

		$ext->add($id, $c, 'end2', new ext_setvar('ITER', '$[${ITER} + 1]'));
		$ext->add($id, $c, '', new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin2'));

		if ($amp_conf['FCBEEPONLY']) {
			$ext->add($id, $c, '', new ext_playback('beep')); // $cmd,n,Playback(...)
		} else {
			$ext->add($id, $c, '', new ext_execif('$["${DAYNIGHTMODE}" = "NIGHT"]', 'Playback', '${DAYREC}', 'Playback', '${NIGHTREC}'));
		}
		$ext->add($id, $c, '', new ext_hangup(''));
	}
}

function daynight_get_avail() {
	global $db;

	$sql = "SELECT ext FROM daynight ORDER BY ext";
	$results = $db->getCol($sql);
	if(DB::IsError($results)) {
		$results = array();
	}

	for ($i=0;$i<=9;$i++) {
		if (!in_array($i,$results)) {
			$list[]=$i;
		}
	}
	return $list;
}

//get the existing daynight codes
function daynight_list() {
	$results = sql("SELECT ext, dest FROM daynight WHERE dmode = 'fc_description' ORDER BY ext","getAll",DB_FETCHMODE_ASSOC);
	if(is_array($results)){
		foreach($results as $result){
			$list[] = $result;
		}
	}
	if (isset($list)) {
		return $list;
	} else { 
		return array();
	}
}

//get the existing password codes
function daynight_passwords() {
	$results = sql("SELECT ext, dest FROM daynight WHERE dmode = 'password'","getAll",DB_FETCHMODE_ASSOC);
	if(is_array($results)){
		foreach($results as $result){
			$list[$result['ext']] = $result['dest'];
		}
	}
	if (isset($list)) {
		return $list;
	} else { 
		return array();
	}
}

//get the existing daynight recordings
//$mode is either 'day' or 'night'
function daynight_recording($mode) {
	$results = sql("SELECT ext, dest FROM daynight WHERE dmode = '".$mode."_recording_id'","getAll",DB_FETCHMODE_ASSOC);
	if(is_array($results)){
		foreach($results as $result){
			$list[$result['ext']] = $result['dest'];
		}
	}
	if (isset($list)) {
		return $list;
	} else { 
		return array();
	}
}

function daynight_edit($post, $id=0) {
	global $db;

	// TODO: Probably have separate add and edit (and change in page.daynight.php also)
	//       Need to set the day/night mode in the system if new

	// Delete all the old dests
	sql("DELETE FROM daynight WHERE dmode IN ('day', 'night', 'password', 'fc_description','day_recording_id','night_recording_id') AND ext = '$id'");

	$day   = isset($post[$post['goto0'].'0'])?$post[$post['goto0'].'0']:'';
	$night = isset($post[$post['goto1'].'1'])?$post[$post['goto1'].'1']:'';

	sql("INSERT INTO daynight (ext, dmode, dest) VALUES ('$id', 'day', '$day')");
	sql("INSERT INTO daynight (ext, dmode, dest) VALUES ('$id', 'night', '$night')");

	if (isset($post['password']) && trim($post['password'] != "")) {
		$password = trim($post['password']);
		sql("INSERT INTO daynight (ext, dmode, dest) VALUES ('$id', 'password', '$password')");
	}
	$fc_description = isset($post['fc_description']) ? trim($post['fc_description']) : "";
	sql("INSERT INTO daynight (ext, dmode, dest) VALUES ('$id', 'fc_description', '".$db->escapeSimple($fc_description)."')");

	$day_recording_id = isset($post['day_recording_id']) ? trim($post['day_recording_id']) : "";
	sql("INSERT INTO daynight (ext, dmode, dest) VALUES ('$id', 'day_recording_id', '$day_recording_id')");
	$night_recording_id = isset($post['night_recording_id']) ? trim($post['night_recording_id']) : "";
	sql("INSERT INTO daynight (ext, dmode, dest) VALUES ('$id', 'night_recording_id', '$night_recording_id')");

	$dn = new dayNightObject($id);
	$dn->del();
	$dn->create($post['state']);

	$fcc = new featurecode('daynight', 'toggle-mode-'.$id);
	if ($fc_description) {
		$fcc->setDescription("$id: $fc_description");
	} else {
		$fcc->setDescription("$id: Call Flow Toggle Control");
	}
	$fcc->setDefault('*28'.$id);
  $fcc->setProvideDest();
	$fcc->update();
	unset($fcc);	

	needreload();
}

function daynight_del($id){

	// TODO: delete ASTDB entry when deleting the mode
	//
	$results = sql("DELETE FROM daynight WHERE ext = \"$id\"","query");

	$fcc = new featurecode('daynight', 'toggle-mode-'.$id);
	$fcc->delete();
	unset($fcc);	

	$dn = new dayNightObject($id);
	$dn->del();
	unset($dn);	
}

function daynight_get_obj($id=0) {
	global $db;

	$sql = "SELECT dmode, dest FROM daynight WHERE dmode IN ('day', 'night', 'password', 'fc_description','day_recording_id','night_recording_id') AND ext = '$id' ORDER BY dmode";
	$res = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($res)) {
		return null;
	}
		foreach($res as $pair) {
			$dmodes[$pair['dmode']] = $pair['dest'];
		}
		$dn = new dayNightObject($id);
		$dmodes['state'] = $dn->getState();

		return $dmodes;
}

/*
SELECT s1.ext ext, dest, dmode, s2.description descirption FROM daynight s1 
INNER JOIN
    (
			      SELECT ext, dest description FROM daynight WHERE dmode = 'fc_description') s2 
						ON s1.ext = s2.ext WHERE dmode in ('day','night')
						AND dest = '$dest'

Provides: ext, dest, dmode, description
*/
function daynight_check_destinations($dest=true) {
	global $active_modules;

	$destlist = array();
	if (is_array($dest) && empty($dest)) {
		return $destlist;
	}
	$sql = "
		SELECT s1.ext ext, dest, dmode, s2.description description FROM daynight s1 
		INNER JOIN
    		(
					SELECT ext, dest description FROM daynight WHERE dmode = 'fc_description') s2 
					ON s1.ext = s2.ext WHERE dmode in ('day','night') 
		";
	if ($dest !== true) {
		$sql .= "AND dest in ('".implode("','",$dest)."')";
	}
	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

	//$type = isset($active_modules['announcement']['type'])?$active_modules['announcement']['type']:'setup';

	foreach ($results as $result) {
		$thisdest = $result['dest'];
		$thisid   = $result['ext'];
		$destlist[] = array(
			'dest' => $thisdest,
			'description' => sprintf(_("Call Flow Toggle: %s (%s)"),$result['description'],$result['dmode']),
			'edit_url' => 'config.php?display=daynight&itemid='.urlencode($thisid).'&action=edit',
		);
	}
	return $destlist;
}

function daynight_change_destination($old_dest, $new_dest) {
	$sql = 'UPDATE daynight SET dest = "' . $new_dest . '" WHERE dest = "' . $old_dest . '"';
	sql($sql, "query");
}

//-----------------------------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------------------------
// TIMECONDITIONS HOOK:
//
// Helper Functions
//

// Note only one of these should be set, a feature code can't be associated with both the day and night mode
function daynight_get_timecondition($id=0) {
	global $db;

	$sql = "SELECT ext, dmode FROM daynight WHERE dmode IN ('timeday', 'timenight') AND dest = '$id' ORDER BY dmode";
	$res = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($res)) {
		return null;
	}
	// we will start the loop but only return the first occurence since there should only be one
	if (empty($res)) {
		return array('ext' => '', 'dmode' => '');
	} else {
		foreach($res as $pair) {
			return $pair;
		}
	}
}

function daynight_list_timecondition($daynight_id='all') {
	global $db;

	if ($daynight_id == 'all') {
		$results = sql("SELECT ext, dmode, dest FROM daynight WHERE dmode IN ('timeday', 'timenight') ORDER BY dest","getAll",DB_FETCHMODE_ASSOC);
	} else {
		$results = sql("SELECT ext, dmode, dest FROM daynight WHERE dmode IN ('timeday', 'timenight') AND `ext` = '$daynight_id' ORDER BY CAST(dest AS UNSIGNED)","getAll",DB_FETCHMODE_ASSOC);
	}
	return $results;
}

function daynight_edit_timecondition($viewing_itemid, $daynight_ref) {
	global $db;

	$sql = "DELETE FROM `daynight` WHERE `dmode` IN ('timeday', 'timenight') AND dest = '$viewing_itemid'";
	$res = $db->getAll($sql, DB_FETCHMODE_ASSOC);

	if ($daynight_ref != '') {
		$daynight_vals = explode(',',$daynight_ref,2);
		$sql = "INSERT INTO `daynight` (`ext`, `dmode`, `dest`) VALUES ('".$daynight_vals[0]."', '".$daynight_vals[1]."', '$viewing_itemid')";
		sql($sql);
	}
}

function daynight_add_timecondition($daynight_ref) { 
	global $db;

	// We don't know what the new timecondition id is yet so we will put a place holder and check it when the page reloads
	//
	daynight_edit_timecondition('add', $daynight_ref);
}

function daynight_checkadd_timecondition() { 
	global $db;

	$sql = "SELECT ext FROM daynight WHERE dmode IN ('timeday', 'timenight') AND dest = 'add'";
	$res = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($res)) {
		return null;
	}

	// If we find anything, then we get the highest timeconditions_id which should be the last one inserted
	//
	if (! empty($res)) {

		$timeconditions_arr = timeconditions_list();

		foreach ($timeconditions_arr as $item) {
			$timeconditions_ids[] = $item['timeconditions_id'];
		}
		rsort($timeconditions_ids);
		$viewing_itemid = $timeconditions_ids[0];

		$sql = "UPDATE `daynight` SET `dest` = '$viewing_itemid' WHERE `dest` = 'add'";
		sql($sql);
	}
}

function daynight_del_timecondition($viewing_itemid) {
	global $db;

	$sql = "DELETE FROM `daynight` WHERE `dmode` IN ('timeday', 'timenight') AND dest = '$viewing_itemid'";
	$res = $db->getAll($sql, DB_FETCHMODE_ASSOC);
}

// -----------------------------------------------------------------
// Hooks to associate a daynight featurecode with a timecondition
//
function daynight_hook_timeconditions($viewing_itemid, $target_menuid) {
	global $tabindex;
	global $amp_conf;
	switch ($target_menuid) {
		// only provide display for timeconditions
		case 'timeconditions':
			$current = daynight_get_timecondition($viewing_itemid);
      if (!$amp_conf['DAYNIGHTTCHOOK'] && $current['ext'] == '') {
        break;
      }
			$daynightcodes = daynight_list();

			$html = '';
			$html = '<tr><td colspan="2"><h5>';
			$html .= _("Call Flow Toggle Mode Association");
			$html .= '<hr></h5></td></tr>';
			$html .= '<tr>';
			$html .= '<td><a href="#" class="info">';
			$html .= _("Associate with").'<span>'._("If a selection is made, this timecondition will be associated with the specified call flow toggle  featurecode. This means that if the Call Flow Feature code is set to override (Red/BLF on) then this time condition will always go to its True destination if the chosen association is to 'Force Time Condition True Destination' and it will always go to its False destination if the association is with the 'Force Time Condition False Destination'. When the associated Call Flow Control Feature code is in its Normal mode (Green/BLF off), then then this Time Condition will operate as normal based on the current time. The Destinations that are part of any Associated Call Flow Control Feature Code will have no affect on where a call will go if passing through this time condition. The only thing that is done when making an association is allowing the override state of a Call Flow Toggle to force this time condition to always follow one of its two destinations when that associated Call Flow Toggle is in its override (Red/BLF on) state.").'.</span></a>:</td>';
			$html .= '<td><select tabindex="'.++$tabindex.'" name="daynight_ref">';
			$html .= "\n";
			$html .= sprintf('<option value="" %s>%s</option>',$current['ext'] == '' ?'selected':'', _("No Association"));
			$html .= "\n";
			foreach ($daynightcodes as $dn_item) {
				$html .= sprintf('<option value="%d,timeday" %s>%s</option>', $dn_item['ext'], ($current['ext'].','.$current['dmode'] == $dn_item['ext'].',timeday'?'selected':''), $dn_item['dest']._(" - Force Time Condition True Destination"));
				$html .= "\n";
				$html .= sprintf('<option value="%d,timenight" %s>%s</option>', $dn_item['ext'], ($current['ext'].','.$current['dmode'] == $dn_item['ext'].',timenight'?'selected':''), $dn_item['dest']._(" - Force Time Condition False Destination"));
				$html .= "\n";
			}
			$html .= '</select></td></tr>';

			return $html;
			break;
		default:
			return false;
			break;
	}
}

function daynight_hookProcess_timeconditions($viewing_itemid, $request) {

	$daynight_ref = isset($request['daynight_ref']) ? $request['daynight_ref'] : '';

	// Do the un-natural act of checking to see if the last call was an add
	// in which case we left a place holder for the timeconditions_id and we
	// need to go up-date it
	//
	// This is necessary because this process hook is called prior to the
	// creation of the timecondition from the timeconditions module
	//
	if(!isset($request['action']) ) {
		daynight_checkadd_timecondition($daynight_ref);
	} else {
		switch ($request['action'])	{
			case 'add':
				// we don't have an viewing_itemid at this point
				daynight_add_timecondition($daynight_ref);
				break;
			case 'delete':
				daynight_del_timecondition($viewing_itemid);
				break;
			case 'edit':
				daynight_edit_timecondition($viewing_itemid, $daynight_ref);
				break;
		}
	}
}

// Splice into the timecondition dialplan and put an override if associated with a daynight mode code
//
function daynight_hookGet_config($engine) {
	global $ext;  // is this the best way to pass this?
	switch($engine) {
		case "asterisk":
			if (! function_exists('timeconditions_get')) {
				return true;
			}
			$overrides = daynight_list_timecondition();
			$context = "timeconditions";

			if(is_array($overrides)) {
				foreach($overrides as $item) {
					$daynight_id     = $item['ext'];
					$mode            = ($item['dmode'] == 'timeday') ? 'DAY' : 'NIGHT';
					$timecondition_id = $item['dest'];
					$timeconditions_arr = timeconditions_get($timecondition_id);
					if (is_array($timeconditions_arr)) {
						$dest = ($mode == 'DAY') ? 'truestate' : 'falsestate';
						$ext->splice($context, $timecondition_id, 0, new ext_gotoif('$["${DB(DAYNIGHT/C'.$daynight_id.')}" = "'.$mode.'"]',$dest));
					}
				}
			}
		break;
	}
}
?>
