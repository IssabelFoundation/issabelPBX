<?php 
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
/* $Id */


// This is the hook for 'destinations'
function disa_destinations() {
	$results = disa_list();
	// return an associative array with destination and description
	if (isset($results)) {
		foreach($results as $result){
			$extens[] = array('destination' => 'disa,'.$result['disa_id'].',1', 'description' => $result['displayname']);
		}
		return $extens;
	} else {
		return null;
	}
}

function disa_getdest($exten) {
	return array('disa,'.$exten.',1');
}

function disa_getdestinfo($dest) {
	global $active_modules;

	if (substr(trim($dest),0,5) == 'disa,') {
		$exten = explode(',',$dest);
		$exten = $exten[1];
		$thisexten = disa_get($exten);
		if (empty($thisexten)) {
			return array();
		} else {
			//$type = isset($active_modules['announcement']['type'])?$active_modules['announcement']['type']:'setup';
			return array('description' => sprintf(_("DISA: %s"),$thisexten['displayname']),
			             'edit_url' => 'config.php?display=disa&itemid='.urlencode($exten),
								  );
		}
	} else {
		return false;
	}
}

// This actually generates the dialplan
function disa_get_config($engine) {
	global $ext;
	switch($engine) {
	case "asterisk":
		$disalist = disa_list();
		if(is_array($disalist)) {
			foreach($disalist as $item) {
				$nopass = false;

				// delete it incase there was one from before (of course if it was deleted???
				// this should all be done properly in class, see pinsets, but for now ...
				//
				$filename = "/etc/asterisk/disa-".$item['disa_id'].".conf";
				if (file_exists($filename)) {
					unlink($filename);
				}
				if (isset($item['pin']) && !empty($item['pin']) && (strtolower($item['pin']) != 'no-password')) {
					// Create the disa-$id.conf file
					$fh = fopen($filename, "w+");
					$pinarr = explode(',' , $item['pin'] );
					if (count($pinarr) > 1) {
						$is_file = true;
						foreach($pinarr as $pin) {
							// Don't support remote MWI, too easy for users to break.
							fwrite($fh, "$pin\n");
						}
						fclose($fh);
						chmod($filename, 0660);
					} else {
						$is_file = false;
					}
				} else {
					$nopass = true;
				}

				$thisitem = disa_get(ltrim($item['disa_id']));
				// add dialplan

				if ($thisitem['needconf'] == 'CHECKED') {
					$ext->add('disa', $item['disa_id'], '', new ext_setvar('RESCOUNT', '1'));
					$ext->add('disa', $item['disa_id'], 'loop',  new ext_gotoif('$[ ${RESCOUNT} > 5]', 'end'));
					$ext->add('disa', $item['disa_id'], '',  new ext_read('RRES', 'press-1', '1', ',1,3'));
					$ext->add('disa', $item['disa_id'], '', new ext_setvar('RESCOUNT', '$[${RESCOUNT}+1]'));
					$ext->add('disa', $item['disa_id'], '', new ext_gotoif('$["x${RRES}"="x"]', 'loop'));
				}
				if (!$nopass) {
					if ($is_file) {
						$ext->add('disa', $item['disa_id'], '', new ext_authenticate('/etc/asterisk/disa-'.$item['disa_id'].'.conf'));
					} else {
						$ext->add('disa', $item['disa_id'], '', new ext_authenticate($item['pin']));
					}
				} else {
				  $ext->add('disa', $item['disa_id'], '', new ext_answer(''));
        }
				$ext->add('disa', $item['disa_id'], '', new ext_setvar('_DISA', 'disa^'.$item['disa_id'].'^newcall'));
				$ext->add('disa', $item['disa_id'], 'newcall', new ext_setvar('_DISACONTEXT', $thisitem['context']));
				$ext->add('disa', $item['disa_id'], '', new ext_setvar('_KEEPCID', (!$thisitem['keepcid'])?'TRUE':'FALSE'));
				if ($thisitem['hangup'] == 'CHECKED') {
					$ext->add('disa', $item['disa_id'], '', new ext_setvar('_HANGUP', '${TRUNK_OPTIONS}Hg'));
				} else {
					$ext->add('disa', $item['disa_id'], '', new ext_setvar('_HANGUP', '${TRUNK_OPTIONS}'));
        }
				$ext->add('disa', $item['disa_id'], '', new ext_setvar('TIMEOUT(digit)', $thisitem['digittimeout']));
				$ext->add('disa', $item['disa_id'], '', new ext_setvar('TIMEOUT(response)', $thisitem['resptimeout']));

				if ($item['cid']) {
					$ext->add('disa', $item['disa_id'], '', new ext_setvar('CALLERID(all)', $item['cid']));
					$ext->add('disa', $item['disa_id'], '', new ext_setvar('__REALCALLERIDNUM','${CALLERID(number):0:40}'));
				}
				$ext->add('disa', $item['disa_id'], '', new ext_disa('no-password,disa-dial'));

				$ext->add('disa', $item['disa_id'], 'end', new ext_hangup(''));
			}


			$context = 'disa-dial';

			foreach (array('_[0-9a-zA-Z]','_[0-9a-zA-Z*#].') as $exten) {
				$ext->add($context, $exten, '', new ext_noop('called ${EXTEN} in ${DISACONTEXT} by ID: ${CUT(DISA,^,2)}'));
				$ext->add($context, $exten, '', new ext_dial('Local/${EXTEN}@${DISACONTEXT}', '300,${HANGUP}'));  // Regular Trunk Dial
				$ext->add($context, $exten, '', new ext_gosub('1', 's-${DIALSTATUS}'));
				$ext->add($context, $exten, '', new ext_goto('${CUT(DISA,^,1)},${CUT(DISA,^,2)},${CUT(DISA,^,3)}'));
			}

			$exten = 's-ANSWER';
			$ext->add($context, $exten, '', new ext_return());

			$exten = 's-CANCEL';
			$ext->add($context, $exten, '', new ext_return());

			$exten = 's-BUSY';
			$ext->add($context, $exten, '', new ext_playtones('busy'));
			$ext->add($context, $exten, '', new ext_wait('3'));
			$ext->add($context, $exten, '', new ext_return());

			$exten = '_s-.';
			$ext->add($context, $exten, '', new ext_noop('DISA Dial failed due to ${DIALSTATUS} - returning to dial tone'));
			$ext->add($context, $exten, '', new ext_playtones('congestion'));
			$ext->add($context, $exten, '', new ext_wait('3'));
			$ext->add($context, $exten, '', new ext_stopplaytones());
			$ext->add($context, $exten, '', new ext_return());
		}
		break;
	}
}

function disa_list() {
	$results = sql("SELECT * FROM disa","getAll",DB_FETCHMODE_ASSOC);
	if(is_array($results)){
		foreach($results as $result){
			// check to see if we have a dept match for the current AMP User.
			if (!isset($results['deptname']) || checkDept($result['deptname'])){
				// return this item's dialplan destination, and the description
				$allowed[] = $result;
			}
		}
	}
	if (isset($allowed)) {
		return $allowed;
	} else {
		return null;
	}
}

function disa_get($id){
	//get all the variables for the meetme
	$results = sql("SELECT * FROM disa WHERE disa_id = '$id'","getRow",DB_FETCHMODE_ASSOC);
	return $results;
}

function disa_chk($post) {
	return true;
}

function disa_add($post) {
	global $db;
	global $amp_conf;
	if(!disa_chk($post)) {
		return null;
	}
	extract($post);
	if (!isset($needconf)) {
		$needconf = '';
	}
	if(empty($displayname)) {
		$displayname = "unnamed";
	}
	if (!isset($keepcid)) {
		$keepcid = 0;
	}
	$results = sql("INSERT INTO disa (displayname,pin,cid,context,resptimeout,digittimeout,needconf,hangup,keepcid) values ('".$db->escapeSimple($displayname)."','".$db->escapeSimple($pin)."','".$db->escapeSimple($cid)."','".$db->escapeSimple($context)."', '".$db->escapeSimple($resptimeout)."', '".$db->escapeSimple($digittimeout)."', '$needconf', '$hangup', '".$db->escapeSimple($keepcid)."')");
	if(method_exists($db,'insert_id')) {
		$id = $db->insert_id();
	} else {
		$id = $amp_conf["AMPDBENGINE"] == "sqlite3" ? sqlite_last_insert_rowid($db->connection) : mysql_insert_id($db->connection);
	}
	return($id);
}

function disa_del($id) {
	$results = sql("DELETE FROM disa WHERE disa_id = \"$id\"","query");
	unlink("/etc/asterisk/disa-{$id}.conf");
}

function disa_edit($id, $post) {
	global $db;
	if (!disa_chk($post)) {
		return null;
	}
	extract($post);
	if (!isset($needconf)) {
		$needconf = '';
	}
	if(empty($displayname)) {
	 	$displayname = "unnamed";
	}
	if (!isset($keepcid)) {
		$keepcid = 0;
	}
	$results = sql("UPDATE disa  set displayname = '".$db->escapeSimple($displayname)."', pin = '".$db->escapeSimple($pin)."', cid = '".$db->escapeSimple($cid)."', context = '".$db->escapeSimple($context)."', resptimeout = '".$db->escapeSimple($resptimeout)."', digittimeout = '".$db->escapeSimple($digittimeout)."', needconf = \"$needconf\", hangup = \"$hangup\", keepcid = '".$db->escapeSimple($keepcid)."' where disa_id = '$id'");
}
?>
