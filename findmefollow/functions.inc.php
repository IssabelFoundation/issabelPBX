<?php /* $Id: functions.inc.php 175 2006-10-03 19:12:39Z plindheimer $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//  Copyright (C) 2006 Philippe Lindheimer
//

/* 	Generates dialplan for findmefollow
	We call this with retrieve_conf
*/

function findmefollow_destinations() {
	global $display;
	global $extdisplay;
	global $followme_exten;
	global $db;

	if ($display == 'findmefollow' && $followme_exten != '') {
		$extens[] = array('destination' => 'ext-local,'.$followme_exten.',dest', 'description' => _("Normal Extension Behavior"));
		return $extens;
	}
  if (($display != 'extensions' && $display != 'users') || !isset($extdisplay) || $extdisplay == '') {
		return null;
  }

  //TODO: need to do a join with user to get the displayname also

  //TODO: if extdisplay is set, sort such that this extension's follow-me is at the top of the list if they have one
  //      alternatively, only put this extension's follow-me since you should not be able to force to others and you can use their extension
	//$results = findmefollow_list();
	$grpnum = sql("SELECT grpnum FROM findmefollow WHERE grpnum = '".$db->escapeSimple($extdisplay)."'","getOne");
	
	// return an associative array with destination and description
	if ($grpnum != '') {
    $extens[] = array('destination' => 'ext-findmefollow,FM'.$grpnum.',1', 'description' => _("Force Follow Me"));
		return $extens;
	} else {
		return null;
  }
}

function findmefollow_get_config($engine) {
	global $ext;  // is this the best way to pass this?
	global $amp_conf;
	switch($engine) {
		case "asterisk":
			if ($amp_conf['USEDEVSTATE']) {
				$ext->addGlobal('FMDEVSTATE','TRUE');
			}

			$fcc = new featurecode('findmefollow', 'fmf_toggle');
			$fmf_code = $fcc->getCodeActive();
			unset($fcc);

			if ($fmf_code != '') {
				findmefollow_fmf_toggle($fmf_code);
			}

			$ext->addInclude('from-internal-additional','ext-findmefollow');
			$ext->addInclude('from-internal-additional','fmgrps');
			$contextname = 'ext-findmefollow';

			// Before creating all the contexts, let's make a list of hints if needed
			//
			if ($amp_conf['USEDEVSTATE'] && $fmf_code != '') {
				$device_list = core_devices_list("all", 'full', true);
				foreach ($device_list as $device) {
          if ($device['tech'] == 'sip' || $device['tech'] == 'iax2') {
					  $ext->add($contextname, $fmf_code.$device['id'], '', new ext_goto("1",$fmf_code,"app-fmf-toggle"));
					  $ext->addHint($contextname, $fmf_code.$device['id'], "Custom:FOLLOWME".$device['id']);
          }
				}
			}

			$ringlist = findmefollow_full_list();
			if (is_array($ringlist)) {
				foreach($ringlist as $item) {
					$grpnum = ltrim($item['0']);
					if ($grpnum == "") {
						continue;
					}
					$grp = findmefollow_get($grpnum);
					
					$strategy = $grp['strategy'];
					$grptime = $grp['grptime'];
					$grplist = $grp['grplist'];
					$postdest = $grp['postdest'];
					$grppre = (isset($grp['grppre'])?$grp['grppre']:'');
					$annmsg_id = $grp['annmsg_id'];
					$dring = $grp['dring'];

					$needsconf = $grp['needsconf'];
					$remotealert_id = $grp['remotealert_id'];
					$toolate_id = $grp['toolate_id'];
					$ringing = $grp['ringing'];
					$pre_ring = $grp['pre_ring'];

					if($ringing == 'Ring' || empty($ringing) ) {
						$dialopts = '${DIAL_OPTIONS}';
					} else {
						// We need the DIAL_OPTIONS variable
            if (!isset($sops)) {
						  $sops = sql("SELECT value from globals where variable='DIAL_OPTIONS'", "getRow");
            }
						$dialopts = "m(${ringing})".str_replace('r', '', $sops[0]);
					}

					// Direct target to Follow-Me come here bypassing the followme/ddial conditional check
					//
					$ext->add($contextname, 'FM'.$grpnum, '', new ext_goto("FM$grpnum","$grpnum"));

					//
					// If the followme is configured for extension dialing to go to the the extension and not followme then
					// go there. This is often used in VmX Locater functionality when the user does not want the followme
					// to automatically be called but only if chosen by the caller as an alternative to going to voicemail
					//
					$ext->add($contextname, $grpnum, '', new ext_gotoif('$[ "${DB(AMPUSER/'.$grpnum.'/followme/ddial)}" = "EXTENSION" ]', 'ext-local,'.$grpnum.',1'));
					$ext->add($contextname, $grpnum, 'FM'.$grpnum, new ext_macro('user-callerid'));

					$ext->add($contextname, $grpnum, '', new ext_set('DIAL_OPTIONS','${DIAL_OPTIONS}I'));
					$ext->add($contextname, $grpnum, '', new ext_set('CONNECTEDLINE(num)', $grpnum));
					$cidnameval = '${DB(AMPUSER/' . $grpnum . '/cidname)}';
					if ($amp_conf['AST_FUNC_PRESENCE_STATE'] && $amp_conf['CONNECTEDLINE_PRESENCESTATE']) {
						$ext->add($contextname, $grpnum, '', new ext_gosub('1', 's', 'sub-presencestate-display', $grpnum));
						$cidnameval.= '${PRESENCESTATE_DISPLAY}';
					}
					$ext->add($contextname, $grpnum, '', new ext_set('CONNECTEDLINE(name,i)', $cidnameval));
					$ext->add($contextname, $grpnum, '', new ext_set('FM_DIALSTATUS','${EXTENSION_STATE(' .$grpnum. '@ext-local)}'));
					$ext->add($contextname, $grpnum, '', new ext_set('__EXTTOCALL','${EXTEN}'));
					$ext->add($contextname, $grpnum, '', new ext_set('__PICKUPMARK','${EXTEN}'));

					// block voicemail until phone is answered at which point a macro should be called on the answering
					// line to clear this flag so that subsequent transfers can occur, if already set by a the caller
					// then don't change.
					//
					$ext->add($contextname, $grpnum, '', new ext_macro('blkvm-setifempty'));
					$ext->add($contextname, $grpnum, '', new ext_gotoif('$["${GOSUB_RETVAL}" = "TRUE"]', 'skipov'));
					$ext->add($contextname, $grpnum, '', new ext_macro('blkvm-set','reset'));
					$ext->add($contextname, $grpnum, '', new ext_setvar('__NODEST', ''));

					// Remember if NODEST was set later, but clear it in case the call is answered so that subsequent
					// transfers work.
					//
					$ext->add($contextname, $grpnum, 'skipov', new ext_setvar('RRNODEST', '${NODEST}'));
					$ext->add($contextname, $grpnum, 'skipvmblk', new ext_setvar('__NODEST', '${EXTEN}'));

					$ext->add($contextname, $grpnum, '', new ext_gosubif('$[${DB_EXISTS(AMPUSER/'.$grpnum.'/followme/changecid)} = 1 & "${DB(AMPUSER/'.$grpnum.'/followme/changecid)}" != "default" & "${DB(AMPUSER/'.$grpnum.'/followme/changecid)}" != ""]', 'sub-fmsetcid,s,1'));


					// deal with group CID prefix
					if ($grppre != '') {
						$ext->add($contextname, $grpnum, '', new ext_macro('prepend-cid', $grppre));
					}
					// recording stuff
					$ext->add($contextname, $grpnum, '', new ext_setvar('RecordMethod','Group'));

          // Note there is no cancel later as the special case of follow-me, if they say record, it should stick
          $ext->add($contextname, $grpnum, 'checkrecord', new ext_gosub('1','s','sub-record-check',"exten,$grpnum,"));

					// MODIFIED (PL)
					// Add Alert Info if set but don't override and already set value (could be from ringgroup, directdid, etc.)
					//
					if ((isset($dring) ? $dring : '') != '') {
						// If ALERTINFO is set, then skip to the next set command. This was modified to two lines because the previous
						// IF() couldn't handle ':' as part of the string. The jump to PRIORITY+2 allows for now destination label
						// which is needed in the 2.3 version.
						$ext->add($contextname, $grpnum, '', new ext_gotoif('$["x${ALERT_INFO}"!="x"]','$[${PRIORITY}+2])}'));
						$ext->add($contextname, $grpnum, '', new ext_setvar("__ALERT_INFO", str_replace(';', '\;', $dring) ));
					}
					// If pre_ring is set, then ring this number of seconds prior to moving on
					if ((isset($strategy) ? substr($strategy,0,strlen('ringallv2')) : '') != 'ringallv2') {
						$ext->add($contextname, $grpnum, '', new ext_gotoif('$[$[ "${DB(AMPUSER/'.$grpnum.'/followme/prering)}" = "0" ] | $[ "${DB(AMPUSER/'.$grpnum.'/followme/prering)}" = "" ]] ', 'skipsimple'));
						$ext->add($contextname, $grpnum, '', new ext_macro('simple-dial',$grpnum.',${DB(AMPUSER/'."$grpnum/followme/prering)}"));
					}

					// group dial
					$ext->add($contextname, $grpnum, 'skipsimple', new ext_setvar('RingGroupMethod',$strategy));
					$ext->add($contextname, $grpnum, '', new ext_setvar('_FMGRP',$grpnum));

                                        if ((isset($annmsg_id) ? $annmsg_id : '')) {
						$annmsg = recordings_get_file($annmsg_id);
						// should always answer before playing anything, shouldn't we ?
						$ext->add($contextname, $grpnum, '', new ext_gotoif('$[$["${DIALSTATUS}" = "ANSWER"] | $["foo${RRNODEST}" != "foo"]]','DIALGRP'));			
						$ext->add($contextname, $grpnum, '', new ext_answer(''));
						$ext->add($contextname, $grpnum, '', new ext_wait(1));
						$ext->add($contextname, $grpnum, '', new ext_playback($annmsg));
					}

					// Create the confirm target
					$len=strlen($grpnum)+4;
					$remotealert = recordings_get_file($remotealert_id);
					$toolate = recordings_get_file($toolate_id);
					$ext->add("fmgrps", "_RG-${grpnum}.", '', new ext_nocdr(''));
					$ext->add("fmgrps", "_RG-${grpnum}.", '', new ext_macro('dial','${DB(AMPUSER/'."$grpnum/followme/grptime)},$dialopts" . "M(confirm^${remotealert}^${toolate}^${grpnum})".',${EXTEN:'.$len.'}'));

					// If grpconf == ENABLED call with confirmation ELSE call normal
					$ext->add($contextname, $grpnum, 'DIALGRP', new 
					    ext_gotoif('$[("${DB(AMPUSER/'.$grpnum.'/followme/grpconf)}"="ENABLED") | ("${FORCE_CONFIRM}"!="") ]', 'doconfirm'));

					// Normal call
					if ((isset($strategy) ? substr($strategy,0,strlen('ringallv2')) : '') != 'ringallv2') {
						$ext->add($contextname, $grpnum, '', new ext_macro('dial','${DB(AMPUSER/'."$grpnum/followme/grptime)},$dialopts,".'${DB(AMPUSER/'."$grpnum/followme/grplist)}"));
					} else {
						$ext->add($contextname, $grpnum, '', new ext_macro('dial','$[ ${DB(AMPUSER/'.$grpnum.'/followme/grptime)} + ${DB(AMPUSER/'.$grpnum.'/followme/prering)} ],'.$dialopts.',${DB(AMPUSER/'.$grpnum.'/followme/grplist)}'));
					}
					$ext->add($contextname, $grpnum, '', new ext_goto('nextstep'));

					// Call Confirm call
					if ((isset($strategy) ? substr($strategy,0,strlen('ringallv2')) : '') != 'ringallv2') {
						$ext->add($contextname, $grpnum, 'doconfirm', new ext_macro('dial-confirm','${DB(AMPUSER/'."$grpnum/followme/grptime)},$dialopts,".'${DB(AMPUSER/'."$grpnum/followme/grplist)},".$grpnum));
					} else {
						$ext->add($contextname, $grpnum, 'doconfirm', new ext_macro('dial-confirm','$[ ${DB(AMPUSER/'.$grpnum.'/followme/grptime)} + ${DB(AMPUSER/'.$grpnum.'/followme/prering)} ],'.$dialopts.',${DB(AMPUSER/'.$grpnum.'/followme/grplist)},'.$grpnum));
					}

					$ext->add($contextname, $grpnum, 'nextstep', new ext_setvar('RingGroupMethod',''));

					// Did the call come from a queue or ringgroup, if so, don't go to the destination, just end and let
					// the queue or ringgroup decide what to do next
					//
					$ext->add($contextname, $grpnum, '', new ext_gotoif('$["foo${RRNODEST}" != "foo"]', 'nodest'));
					$ext->add($contextname, $grpnum, '', new ext_setvar('__NODEST', ''));
					$ext->add($contextname, $grpnum, '', new ext_set('__PICKUPMARK',''));
					$ext->add($contextname, $grpnum, '', new ext_macro('blkvm-clr'));

					/* NOANSWER:    NOT_INUSE 
					 * CHANUNAVAIL: UNAVAILABLE, UNKNOWN, INVALID (or DIALSTATUS=CHANUNAVAIL)
					 * BUSY:        BUSY, INUSE, RINGING, RINGINUSE, HOLDINUSE, ONHOLD
					 */
					$ext->add($contextname, $grpnum, '', new ext_noop_trace('FM_DIALSTATUS: ${FM_DIALSTATUS} DIALSTATUS: ${DIALSTATUS}'));
					$ext->add($contextname, $grpnum, '', new ext_set('DIALSTATUS',
						'${IF($["${FM_DIALSTATUS}"="NOT_INUSE"&"${DIALSTATUS}"!="CHANUNAVAIL"]?NOANSWER:'
						. '${IF($["${DIALSTATUS}"="CHANUNAVAIL"|"${FM_DIALSTATUS}"="UNAVAILABLE"|"${FM_DIALSTATUS}"="UNKNOWN"|"${FM_DIALSTATUS}"="INVALID"]?'
					 	. 'CHANUNAVAIL:BUSY)})}'));

					// where next?
					if ((isset($postdest) ? $postdest : '') != '') {
						$ext->add($contextname, $grpnum, '', new ext_goto($postdest));
					} else {
						$ext->add($contextname, $grpnum, '', new ext_hangup(''));
					}
					$ext->add($contextname, $grpnum, 'nodest', new ext_noop('SKIPPING DEST, CALL CAME FROM Q/RG: ${RRNODEST}'));
				}

        /*
          ASTDB Settings:
          AMPUSER/nnn/followme/changecid default | did | fixed | extern
          AMPUSER/nnn/followme/fixedcid XXXXXXXX

          changecid:
            default   - works as always, same as if not present
            fixed     - set to the fixedcid
            extern    - set to the fixedcid if the call is from the outside only
            did       - set to the DID that the call came in on or leave alone, treated as foreign
            forcedid  - set to the DID that the call came in on or leave alone, not treated as foreign
          
          EXTTOCALL   - has the exten num called, hoaky if that goes away but for now use it
        */
        if (count($ringlist)) {
          $contextname = 'sub-fmsetcid';
          $exten = 's';
          $ext->add($contextname, $exten, '', new ext_goto('1','s-${DB(AMPUSER/${EXTTOCALL}/followme/changecid)}'));

          $exten = 's-fixed';
			    $ext->add($contextname, $exten, '', new ext_execif('$["${REGEX("^[\+]?[0-9]+$" ${DB(AMPUSER/${EXTTOCALL}/followme/fixedcid)})}" = "1"]', 'Set', '__TRUNKCIDOVERRIDE=${DB(AMPUSER/${EXTTOCALL}/followme/fixedcid)}'));
			    $ext->add($contextname, $exten, '', new ext_return(''));

          $exten = 's-extern';
			    $ext->add($contextname, $exten, '', new ext_execif('$["${REGEX("^[\+]?[0-9]+$" ${DB(AMPUSER/${EXTTOCALL}/followme/fixedcid)})}" == "1" & "${FROM_DID}" != ""]', 'Set', '__TRUNKCIDOVERRIDE=${DB(AMPUSER/${EXTTOCALL}/followme/fixedcid)}'));
			    $ext->add($contextname, $exten, '', new ext_return(''));

          $exten = 's-did';
			    $ext->add($contextname, $exten, '', new ext_execif('$["${REGEX("^[\+]?[0-9]+$" ${FROM_DID})}" = "1"]', 'Set', '__REALCALLERIDNUM=${FROM_DID}'));
			    $ext->add($contextname, $exten, '', new ext_return(''));

          $exten = 's-forcedid';
			    $ext->add($contextname, $exten, '', new ext_execif('$["${REGEX("^[\+]?[0-9]+$" ${FROM_DID})}" = "1"]', 'Set', '__TRUNKCIDOVERRIDE=${FROM_DID}'));
			    $ext->add($contextname, $exten, '', new ext_return(''));

          $exten = '_s-.';
					$ext->add($contextname, $exten, '', new ext_noop('Unknown value for AMPUSER/${EXTTOCALL}/followme/changecid of ${DB(AMPUSER/${EXTTOCALL}/followme/changecid)} set to "default"'));
					$ext->add($contextname, $exten, '', new ext_setvar('DB(AMPUSER/${EXTTOCALL}/followme/changecid)', 'default'));
			    $ext->add($contextname, $exten, '', new ext_return(''));
        }
			}
		break;
	}
}

function findmefollow_add($grpnum,$strategy,$grptime,$grplist,$postdest,$grppre='',$annmsg_id='',$dring,$needsconf,$remotealert_id,$toolate_id,$ringing,$pre_ring,$ddial,$changecid='default',$fixedcid='') {
	global $amp_conf;
	global $astman;
	global $db;

	if (empty($postdest)) {
		$postdest = "ext-local,$grpnum,dest";
	}

	$sql = "INSERT INTO findmefollow (grpnum, strategy, grptime, grppre, grplist, annmsg_id, postdest, dring, needsconf, remotealert_id, toolate_id, ringing, pre_ring) VALUES ('".$db->escapeSimple($grpnum)."', '".$db->escapeSimple($strategy)."', ".$db->escapeSimple($grptime).", '".$db->escapeSimple($grppre)."', '".$db->escapeSimple($grplist)."', '".$db->escapeSimple($annmsg_id)."', '".$db->escapeSimple($postdest)."', '".$db->escapeSimple($dring)."', '$needsconf', '$remotealert_id', '$toolate_id', '$ringing', '$pre_ring')";
	$results = sql($sql);

	if ($astman) {
		$astman->database_put("AMPUSER",$grpnum."/followme/prering",isset($pre_ring)?$pre_ring:'');
		$astman->database_put("AMPUSER",$grpnum."/followme/grptime",isset($grptime)?$grptime:'');
		$astman->database_put("AMPUSER",$grpnum."/followme/grplist",isset($grplist)?$grplist:'');

		$needsconf = isset($needsconf)?$needsconf:'';
		$confvalue = ($needsconf == 'CHECKED')?'ENABLED':'DISABLED';
		$astman->database_put("AMPUSER",$grpnum."/followme/grpconf",$confvalue);

		$ddial      = isset($ddial)?$ddial:'';
		$ddialvalue = ($ddial == 'CHECKED')?'EXTENSION':'DIRECT';
		$astman->database_put("AMPUSER",$grpnum."/followme/ddial",$ddialvalue);
		if ($amp_conf['USEDEVSTATE']) {
			$ddialstate = ($ddial == 'CHECKED')?'NOT_INUSE':'BUSY';

			$devices = $astman->database_get("AMPUSER", $grpnum . "/device");
			$device_arr = explode('&', $devices);
			foreach ($device_arr as $device) {
				$astman->set_global($amp_conf['AST_FUNC_DEVICE_STATE'] . "(Custom:FOLLOWME$device)", $ddialstate);
			}
		}

		$astman->database_put("AMPUSER",$grpnum."/followme/changecid",$changecid);
	  $fixedcid = preg_replace("/[^0-9\+]/" ,"", trim($fixedcid));
		$astman->database_put("AMPUSER",$grpnum."/followme/fixedcid",$fixedcid);
	} else {
		fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
	}
}

function findmefollow_del($grpnum) {
	global $amp_conf;
	global $astman;
	global $db;

	$results = sql("DELETE FROM findmefollow WHERE grpnum = '".$db->escapeSimple($grpnum)."'","query");

	if ($astman) {
		$astman->database_deltree("AMPUSER/".$grpnum."/followme");
	} else {
		fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
	}
}

function findmefollow_full_list() {
	$results = sql("SELECT grpnum FROM findmefollow ORDER BY CAST(grpnum as UNSIGNED)","getAll",DB_FETCHMODE_ASSOC);
	foreach ($results as $result) {
		if (isset($result['grpnum']) && checkRange($result['grpnum'])) {
			$grps[] = array($result['grpnum']);
		}
	}
	if (isset($grps))
		return $grps;
	else
		return null;
}

function findmefollow_list($get_all=false) {

	global $db;
	$sql = "SELECT grpnum FROM findmefollow ORDER BY CAST(grpnum as UNSIGNED)";
	$results = $db->getCol($sql);
	if(DB::IsError($results)) {
		$results = null;
	}
	if (isset($results)) {
		foreach($results as $result) {
			if ($get_all || checkRange($result)){
				$grps[] = $result;
			}
		}
	}
	if (isset($grps)) {
		return $grps;
	}
	else {
		return null;
	}
}

// This gets the list of all active users so that the Find Me Follow display can limit the options to only created users.
// the returned arrays contain [0]:extension [1]:name
//
// This was pulled straight out of previous 1.x version, might need cleanup laster
//
function findmefollow_allusers() {
        global $db;
        $sql = "SELECT extension,name FROM users ORDER BY extension";
        $results = $db->getAll($sql);
        if(DB::IsError($results)) {
                $results = null;
        }
        foreach($results as $result){
                if (checkRange($result[0])){
                        $users[] = array($result[0],$result[1]);
                }
        }
        if (isset($users)) sort($users);
        return $users;
}

// Only check astdb if check_astdb is not 0. For some reason, this fails if the asterisk manager code
// is included (executed) by all calls to this function. This results in silently not generating the
// extensions_additional.conf file. page.findmefollow.php does set it to 1 which means that when running
// the GUI, any changes not reflected in SQL will be detected and written back to SQL so that they are
// in sync. Ideally, anything that changes the astdb should change SQL. (in some ways, these should both
// not be here but ...
//
// Need to go back and confirm at some point that the $check_astdb error is still there and deal with it.
// as variables like $ddial get introduced to only be in astdb, the result array will not include them
// if not able to get to astdb. (I suspect in 2.2 and beyond this may all be fixed).
//
function findmefollow_get($grpnum, $check_astdb=0) {
	global $amp_conf;
	global $astman;
	global $db;

	$results = sql("SELECT grpnum, strategy, grptime, grppre, grplist, annmsg_id, postdest, dring, needsconf, remotealert_id, toolate_id, ringing, pre_ring, voicemail FROM findmefollow INNER JOIN `users` ON `extension` = `grpnum` WHERE grpnum = '".$db->escapeSimple($grpnum)."'","getRow",DB_FETCHMODE_ASSOC);
	if (empty($results)) {
		return array();
	}
	if (!isset($results['voicemail'])) {
		$results['voicemail'] = sql("SELECT `voicemail` FROM `users` WHERE `extension` = '".$db->escapeSimple($grpnum)."'","getOne");
	}
	if (!isset($results['strategy'])) {
		$results['strategy'] = $amp_conf['FOLLOWME_RG_STRATEGY'];
	}

	if ($check_astdb) {
		if ($astman) {
			$astdb_prering = $astman->database_get("AMPUSER",$grpnum."/followme/prering");
			$astdb_grptime = $astman->database_get("AMPUSER",$grpnum."/followme/grptime");
			$astdb_grplist = $astman->database_get("AMPUSER",$grpnum."/followme/grplist");
			$astdb_grpconf = $astman->database_get("AMPUSER",$grpnum."/followme/grpconf");

      $astdb_changecid = strtolower($astman->database_get("AMPUSER",$grpnum."/followme/changecid"));
      switch($astdb_changecid) {
        case 'default':
        case 'did':
        case 'forcedid':
        case 'fixed':
        case 'extern':
          break;
        default:
          $astdb_changecid = 'default';
      }
      $results['changecid'] = $astdb_changecid;
      $fixedcid = $astman->database_get("AMPUSER",$grpnum."/followme/fixedcid");
	    $results['fixedcid'] = preg_replace("/[^0-9\+]/" ,"", trim($fixedcid));
		} else {
			fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
		}
			$astdb_ddial   = $astman->database_get("AMPUSER",$grpnum."/followme/ddial");                                     
		// If the values are different then use what is in astdb as it may have been changed.
		// If sql returned no results for pre_ring/grptime then it's not configued so we reset
		// the astdb defaults as well
		//
		$changed=0;
    if (!isset($results['pre_ring'])) {
      $results['pre_ring'] = $astdb_prering = $amp_conf['FOLLOWME_PRERING'];
    }
    if (!isset($results['grptime'])) {
      $results['grptime'] = $astdb_grptime = $amp_conf['FOLLOWME_TIME'];
    }
    if (!isset($results['grplist'])) {
      $results['grplist'] = '';
    }
    if (!isset($results['needsconf'])) {
      $results['needsconf'] = '';
    }
		if (($astdb_prering != $results['pre_ring']) && ($astdb_prering >= 0)) {
			$results['pre_ring'] = $astdb_prering;
			$changed=1;
		}
		if (($astdb_grptime != $results['grptime']) && ($astdb_grptime > 0)) {
			$results['grptime'] = $astdb_grptime;
			$changed=1;
		}
		if ((trim($astdb_grplist) != trim($results['grplist'])) && (trim($astdb_grplist) != '')) {
			$results['grplist'] = $astdb_grplist;
			$changed=1;
		}

		if (trim($astdb_grpconf) == 'ENABLED') {
			$confvalue = 'CHECKED';
		} elseif (trim($astdb_grpconf) == 'DISABLED') {
			$confvalue = '';
		} else {
			//Bogus value, should not get here but treat as disabled
			$confvalue = '';
		}
		if ($confvalue != trim($results['needsconf'])) {
			$results['needsconf'] = $confvalue;
			$changed=1;
		}

		// Not in sql so no sanity check needed
		//
		if (trim($astdb_ddial) == 'EXTENSION') {
			$ddial = 'CHECKED';
		} elseif (trim($astdb_ddial) == 'DIRECT') {
			$ddial = '';
		} else {
			// If here then followme must not be set so use default
			$ddial = $amp_conf['FOLLOWME_DISABLED'] ? 'CHECKED' : '';
		}
		$results['ddial'] = $ddial;

		if ($changed) {
			$sql = "UPDATE findmefollow SET grptime = '".$results['grptime']."', grplist = '".
				$db->escapeSimple(trim($results['grplist']))."', pre_ring = '".$results['pre_ring'].
				"', needsconf = '".$results['needsconf']."' WHERE grpnum = '".$db->escapeSimple($grpnum)."' LIMIT 1";
			$sql_results = sql($sql);
		}
	} // if check_astdb

	return $results;
}

function findmefollow_configpageinit($dispnum) {
	global $currentcomponent;
	global $amp_conf;

	if ( ($dispnum == 'users' || $dispnum == 'extensions') ) {
		$currentcomponent->addguifunc('findmefollow_configpageload');

		if ($amp_conf['FOLLOWME_AUTO_CREATE']) {
			$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
			if ($action=="add") {
				$currentcomponent->addprocessfunc('findmefollow_configprocess', 8);
			}
		}
	}
}

function findmefollow_configpageload() {
	global $currentcomponent;

	$viewing_itemid =  isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$action =  isset($_REQUEST['action'])?$_REQUEST['action']:null;
	if ( $viewing_itemid != '' && $action != 'del') {
		$set_findmefollow = findmefollow_list();
		$grpURL = $_SERVER['PHP_SELF'].'?'.'display=findmefollow&extdisplay=GRP-'.$viewing_itemid;
		if (is_array($set_findmefollow)) {
			if (in_array($viewing_itemid,$set_findmefollow)) {
				$grpTEXT = _("Edit Follow Me Settings");
				$icon = "images/user_go.png";
			} else {
				$grpTEXT = _("Add Follow Me Settings");
				$icon = "images/user_add.png";
			}
		} else {
			$grpTEXT = _("Add Follow Me Settings");
			$icon = "images/user_add.png";
		}
		$label = '<span><img width="16" height="16" border="0" title="'.$grpTEXT.'" alt="" src="'.$icon.'"/>&nbsp;'.$grpTEXT.'</span>';
		$currentcomponent->addguielem('_top', new gui_link('findmefollowlink', $label, $grpURL));
	}	
}

// If we are auto-creating a followme for each extension then add the hook funcitons for
// extensions and users.
//
if ($amp_conf['FOLLOWME_AUTO_CREATE']) {
	function findmefollow_configprocess() {
		global $amp_conf;

		//create vars from the request
		$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
		$ext = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
		$extn = isset($_REQUEST['extension'])?$_REQUEST['extension']:null;

		if ($ext=='') { 
			$extdisplay = $extn; 
		} else {
			$extdisplay = $ext;
		} 
		if ($action == "add") {
			if (!isset($GLOBALS['abort']) || $GLOBALS['abort'] !== true) {

				$ddial = $amp_conf['FOLLOWME_DISABLED'] ? 'CHECKED' : '';
				findmefollow_add($extdisplay, $amp_conf['FOLLOWME_RG_STRATEGY'], $amp_conf['FOLLOWME_TIME'],
					$extdisplay, "", "", "", "", "", "", "","", $amp_conf['FOLLOWME_PRERING'], $ddial,'default','');
			}
		}
	}
}

// we only return the destination that other modules might use, e.g. extenions/users
function findmefollow_getdest($exten) {
	return array('ext-findmefollow,FM' . $exten . ',1');
}

function findmefollow_getdestinfo($dest) {
	if (substr(trim($dest),0,17) == 'ext-findmefollow,' || substr(trim($dest),0,10) == 'ext-local,' && substr(trim($dest),-4) == 'dest') {
		$grp = explode(',',$dest);
		$grp = ltrim($grp[1],'FM');
		$thisgrp = findmefollow_get($grp);
		if (empty($thisgrp)) {
			return array();
		} else {
			return array('description' => sprintf(_("Follow Me: %s"),urlencode($grp)),
			             'edit_url' => 'config.php?display=findmefollow&extdisplay=GRP-'.urlencode($grp),
								  );
		}
	} else {
		return false;
	}
}

function findmefollow_check_destinations($dest=true) {
	global $active_modules;

	$destlist = array();
	if (is_array($dest) && empty($dest)) {
		return $destlist;
	}
	$sql = "SELECT grpnum, postdest, name FROM findmefollow INNER JOIN users ON grpnum = extension ";
	if ($dest !== true) {
		$sql .= "WHERE postdest in ('".implode("','",$dest)."')";
	}
	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

	//$type = isset($active_modules['announcement']['type'])?$active_modules['announcement']['type']:'setup';

	foreach ($results as $result) {
		$thisdest = $result['postdest'];
		$thisid   = $result['grpnum'];
		$destlist[] = array(
			'dest' => $thisdest,
			'description' => sprintf(_("Follow-Me: %s (%s)"),$thisid,$result['name']),
			'edit_url' => 'config.php?display=findmefollow&extdisplay=GRP-'.urlencode($thisid),
		);
	}
	return $destlist;
}

function findmefollow_change_destination($old_dest, $new_dest) {
	$sql = 'UPDATE findmefollow SET postdest = "' . $new_dest . '" WHERE postdest = "' . $old_dest . '"';
	sql($sql, "query");
}


function findmefollow_recordings_usage($recording_id) {
	global $active_modules;

	$results = sql("SELECT `grpnum` FROM `findmefollow` WHERE `annmsg_id` = '$recording_id' OR `remotealert_id` = '$recording_id' OR `toolate_id` = '$recording_id'","getAll",DB_FETCHMODE_ASSOC);
	if (empty($results)) {
		return array();
	} else {
		//$type = isset($active_modules['ivr']['type'])?$active_modules['ivr']['type']:'setup';
		foreach ($results as $result) {
			$usage_arr[] = array(
				'url_query' => 'config.php?display=findmefollow&extdisplay=GRP-'.urlencode($result['grpnum']),
				'description' => sprintf(_("Follow-Me User: %s"),$result['grpnum']),
			);
		}
		return $usage_arr;
	}
}

function findmefollow_fmf_toggle($c) {
	global $ext;
	global $amp_conf;
	global $version;

	$id = "app-fmf-toggle"; // The context to be included
	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_goto('start','s',$id));
	$c = 's';

	$ext->add($id, $c, 'start', new ext_answer(''));
	$ext->add($id, $c, '', new ext_wait('1'));
	$ext->add($id, $c, '', new ext_macro('user-callerid'));

	$ext->add($id, $c, '', new ext_gotoif('$["${DB(AMPUSER/${AMPUSER}/followme/ddial)}" = "EXTENSION"]', 'activate'));
	$ext->add($id, $c, '', new ext_gotoif('$["${DB(AMPUSER/${AMPUSER}/followme/ddial)}" = "DIRECT"]', 'deactivate','end'));

	$ext->add($id, $c, 'deactivate', new ext_setvar('DB(AMPUSER/${AMPUSER}/followme/ddial)', 'EXTENSION'));
	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($id, $c, '', new ext_setvar('STATE', 'NOT_INUSE'));
		$ext->add($id, $c, '', new ext_gosub('1', 'sstate', $id));
	}
	if ($amp_conf['FCBEEPONLY']) {
		$ext->add($id, $c, 'hook_off', new ext_playback('beep')); // $cmd,n,Playback(...)
	} else {
		$ext->add($id, $c, 'hook_off', new ext_playback('followme&de-activated'));
	}
	$ext->add($id, $c, 'end', new ext_macro('hangupcall'));

	$ext->add($id, $c, 'activate', new ext_setvar('DB(AMPUSER/${AMPUSER}/followme/ddial)', 'DIRECT'));
	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($id, $c, '', new ext_setvar('STATE', 'INUSE'));
		$ext->add($id, $c, '', new ext_gosub('1', 'sstate', $id));
	}
	if ($amp_conf['FCBEEPONLY']) {
		$ext->add($id, $c, 'hook_on', new ext_playback('beep')); // $cmd,n,Playback(...)
	} else {
		$ext->add($id, $c, 'hook_on', new ext_playback('followme&activated'));
	}
	$ext->add($id, $c, '', new ext_macro('hangupcall'));

	if ($amp_conf['USEDEVSTATE']) {
	$c = 'sstate';
		$ext->add($id, $c, '', new ext_dbget('DEVICES','AMPUSER/${AMPUSER}/device'));
		$ext->add($id, $c, '', new ext_gotoif('$["${DEVICES}" = "" ]', 'return'));
		$ext->add($id, $c, '', new ext_setvar('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '1'));
    $ext->add($id, $c, 'begin', new ext_setvar($amp_conf['AST_FUNC_DEVICE_STATE'].'(Custom:FOLLOWME${CUT(DEVICES,&,${ITER})})','${STATE}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '$[${ITER} + 1]'));
		$ext->add($id, $c, '', new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin'));
		$ext->add($id, $c, 'return', new ext_return());
	}
}
?>
