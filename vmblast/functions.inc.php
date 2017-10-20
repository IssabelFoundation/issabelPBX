<?php /* $Id: functions.inc.php 3396 2006-12-21 02:40:16Z p_lindheimer $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

// The destinations this module provides
// returns a associative arrays with keys 'destination' and 'description'
function vmblast_destinations() {
	//get the list of vmblast
	$results = vmblast_list();
	
	// return an associative array with destination and description
	if (isset($results)) {
		foreach($results as $result){
				$thisgrp = vmblast_get(ltrim($result['0']));
				$extens[] = array('destination' => 'vmblast-grp,'.ltrim($result['0']).',1', 'description' => $thisgrp['description'].' <'.ltrim($result['0']).'>');
		}
	}
	
	if (isset($extens)) 
		return $extens;
	else
		return null;
}

function vmblast_getdest($exten) {
	return array("vmblast-grp,$exten,1");
}

function vmblast_getdestinfo($dest) {
	if (substr(trim($dest),0,12) == 'vmblast-grp,') {
		$grp = explode(',',$dest);
		$grp = $grp[1];
		$thisgrp = vmblast_get($grp);
		if (empty($thisgrp)) {
			return array();
		} else {
			return array('description' => sprintf(_("Voicemail Group %s: %s"),$grp,$thisgrp['description']),
			             'edit_url' => 'config.php?display=vmblast&extdisplay=GRP-'.urlencode($grp),
								  );
		}
	} else {
		return false;
	}
}

/* 	Generates dialplan for vmblast We call this with retrieve_conf
*/
function vmblast_get_config($engine) {
	global $ext;  // is this the best way to pass this?
	switch($engine) {
		case "asterisk":
			$ext->addInclude('from-internal-additional','vmblast-grp');
			$contextname = 'vmblast-grp';
			$vmlist = vmblast_list();

			if (function_exists('recordings_list')) { 
				$recordings_installed = true;
				$got_recordings = false;
			} else {
				$recordings_installed = false;
			}

			if (is_array($vmlist)) {
				foreach($vmlist as $item) {
					$grpnum = ltrim($item['0']);
					$grp = vmblast_get($grpnum);
					$grplist = $grp['grplist'];
					$ext->add($contextname, $grpnum, '', new ext_macro('user-callerid'));
					$ext->add($contextname, $grpnum, '', new ext_answer(''));
					$ext->add($contextname, $grpnum, '', new ext_wait('1'));

					if (isset($grp['password']) && trim($grp['password']) != "" && ctype_digit(trim($grp['password']))) {
						$ext->add($contextname, $grpnum, '', new ext_authenticate($grp['password']));
					}

					$ext->add($contextname, $grpnum, '', new ext_setvar('GRPLIST',''));
					foreach ($grplist as $exten) {
						$ext->add($contextname, $grpnum, '', new ext_macro('get-vmcontext',$exten));
						$ext->add($contextname, $grpnum, '', new ext_setvar('GRPLIST','${GRPLIST}&'.$exten.'@${VMCONTEXT}'));
					}

					// Add a message and confirmation so they know what group they are in
					//
					if ($grp['audio_label'] == -2) {
						$ext->add($contextname, $grpnum, '', new ext_goto('1','1','app-vmblast'));
					} elseif ($grp['audio_label'] == -1 || !$recordings_installed) {
						$ext->add($contextname, $grpnum, '', new ext_setvar('DIGITS',$grpnum));
						$ext->add($contextname, $grpnum, '', new ext_goto('digits','vmblast','app-vmblast'));
					} else {
						if (!$got_recordings) {
							$recordings = recordings_list();
							$got_recordings = true;
							$recording_hash = array();
							foreach ($recordings as $recording) {
								$recording_hash[$recording[0]] = $recording[2];
							}
						}
						if (isset($recording_hash[$grp['audio_label']])) {
							$ext->add($contextname, $grpnum, '', new ext_setvar('VMBMSG',$recording_hash[$grp['audio_label']]));
							$ext->add($contextname, $grpnum, '', new ext_goto('msg','vmblast','app-vmblast'));
						} else {
							$ext->add($contextname, $grpnum, '', new ext_setvar('DIGITS',$grpnum));
							$ext->add($contextname, $grpnum, '', new ext_goto('digits','vmblast','app-vmblast'));
						}
					}
				}
				$contextname = 'app-vmblast';
				$ext->add($contextname, 'vmblast', 'digits', new ext_execif('$["${DIGITS}" != ""]','SayDigits','${DIGITS}'));
				$ext->add($contextname, 'vmblast', 'msg', new ext_execif('$["${VMBMSG}" != ""]','Background','${VMBMSG}'));
				$ext->add($contextname, 'vmblast', '', new ext_background('if-correct-press&digits/1'));
				$ext->add($contextname, 'vmblast', '', new ext_waitexten('20'));
				$ext->add($contextname, 'vmblast', '', new ext_playback('sorry-youre-having-problems&goodbye'));
				$ext->add($contextname, 'vmblast', '', new ext_hangup(''));

				$ext->add($contextname, '1', '', new ext_vm('${GRPLIST:1},s'));
				$ext->add($contextname, '1', '', new ext_hangup(''));
			}
		break;
	}
}

function vmblast_check_extensions($exten=true) {
	$extenlist = array();
	if (is_array($exten) && empty($exten)) {
		return $extenlist;
	}
	$sql = "SELECT grpnum, description FROM vmblast ";
	if (is_array($exten)) {
		$sql .= "WHERE grpnum in ('".implode("','",$exten)."')";
	}
	$sql .= " ORDER BY CAST(grpnum AS UNSIGNED)";
	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

	foreach ($results as $result) {
		$thisexten = $result['grpnum'];
		$extenlist[$thisexten]['description'] = _("Voicemail Group: ").$result['description'];
		$extenlist[$thisexten]['status'] = 'INUSE';
		$extenlist[$thisexten]['edit_url'] = 'config.php?display=vmblast&extdisplay=GRP-'.urlencode($thisexten);
	}
	return $extenlist;
}

function vmblast_add($grpnum,$grplist,$description,$audio_label= -1, $password = '', $default_group=0) {
	global $db;

	if (is_array($grplist)) {
		$xtns = $grplist;
	} else {
		$xtns = explode("\n",$grplist);
	}

	foreach ($xtns as $key => $value) {
		$xtns[$key] = $db->escapeSimple(trim($value));
	}
		// Sanity check input.

	$compiled = $db->prepare("INSERT INTO vmblast_groups (grpnum, ext) values ('$grpnum',?)");
	$result   = $db->executeMultiple($compiled,$xtns);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo()."<br><br>".'error adding to vmblast_groups table');	
	}
	$sql = "INSERT INTO vmblast (grpnum, description, audio_label, password) VALUES (".$grpnum.", '".$db->escapeSimple($description)."', '$audio_label', '".$db->escapeSimple($password)."')";
	$results = sql($sql);

	if ($default_group) {
		sql("DELETE FROM `admin` WHERE variable = 'default_vmblast_grp'");
		sql("INSERT INTO `admin` (variable, value) VALUES ('default_vmblast_grp', '$grpnum')");
	} else {
		sql("DELETE FROM `admin` WHERE variable = 'default_vmblast_grp' AND value = '$grpnum'");
	}
	return true;
}

function vmblast_del($grpnum) {
	$results = sql("DELETE FROM vmblast WHERE grpnum = '$grpnum'","query");
	$results = sql("DELETE FROM vmblast_groups WHERE grpnum = '$grpnum'","query");
	sql("DELETE FROM `admin` WHERE variable = 'default_vmblast_grp' AND value = '$grpnum'");
}

function vmblast_list() {
	$results = sql("SELECT grpnum, description FROM vmblast ORDER BY grpnum","getAll",DB_FETCHMODE_ASSOC);
	foreach ($results as $result) {
		if (isset($result['grpnum']) && checkRange($result['grpnum'])) {
			$grps[] = array($result['grpnum'], $result['description']);
		}
	}
	if (isset($grps))
		return $grps;
	else
		return null;
}

function vmblast_get_default_grp() {
	$grp = sql("SELECT value FROM admin WHERE variable='default_vmblast_grp' LIMIT 1",'getOne');
  return $grp;
}

function vmblast_get($grpnum) {
	global $db;

	$results = sql("SELECT grpnum, description, audio_label, password FROM vmblast WHERE grpnum = '$grpnum'","getRow",DB_FETCHMODE_ASSOC);
	$grplist = $db->getCol("SELECT ext FROM vmblast_groups WHERE grpnum = '$grpnum'");
	if(DB::IsError($grplist)) {
		die_issabelpbx($grplist->getDebugInfo()."<br><br>".'selecting from vmblast_groups table');	
	}
	$results['grplist'] = $grplist;

	$sql = "SELECT * FROM admin WHERE variable='default_vmblast_grp' AND value='$grpnum'";
	$default_group = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($default_group)) {
		$results['default_group'] = 0;
	} else {
		$results['default_group'] = empty($default_group) ? 0 : $default_group['value'];
	}
	
	return $results;
}

function vmblast_check_default($extension) {
	$sql = "SELECT ext FROM vmblast_groups WHERE ext = '$extension' AND grpnum = (SELECT value FROM admin WHERE variable = 'default_vmblast_grp' limit 1)";
	$results = sql($sql,"getAll");
	return (count($results) ? 1 : 0);
}

function vmblast_set_default($extension, $value) {
	$default_group = sql("SELECT value FROM `admin` WHERE variable = 'default_vmblast_grp' limit 1", "getOne");
	if ($default_group == '') {
		return false;
	}
	sql("DELETE FROM vmblast_groups WHERE ext = '$extension' AND grpnum = '$default_group'");
	if ($value == 1) {
		sql("INSERT INTO vmblast_groups (grpnum, ext) VALUES ('$default_group', '$extension')");
	}
}

function vmblast_configpageinit($pagename) {
	global $currentcomponent;

	$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$extension = isset($_REQUEST['extension'])?$_REQUEST['extension']:null;
	$tech_hardware = isset($_REQUEST['tech_hardware'])?$_REQUEST['tech_hardware']:null;

	// We only want to hook 'users' or 'extensions' pages.
	if ($pagename != 'users' && $pagename != 'extensions') {
		return true;
	}

	if ($tech_hardware != null || $pagename == 'users') {
		vmblast_applyhooks();
		$currentcomponent->addprocessfunc('vmblast_configprocess', 8);
	} elseif ($action=="add") {
		// We don't need to display anything on an 'add', but we do need to handle returned data.
		$currentcomponent->addprocessfunc('vmblast_configprocess', 8);
	} elseif ($extdisplay != '') {
		// We're now viewing an extension, so we need to display _and_ process.
		vmblast_applyhooks();
		$currentcomponent->addprocessfunc('vmblast_configprocess', 8);
	}
}

function vmblast_applyhooks() {
	global $currentcomponent;

	// Add the 'process' function - this gets called when the page is loaded, to hook into 
	// displaying stuff on the page.
	$currentcomponent->addoptlistitem('vmblast_group', '0', _("Exclude"));
	$currentcomponent->addoptlistitem('vmblast_group', '1', _("Include"));
	$currentcomponent->setoptlistopts('vmblast_group', 'sort', false);

	$currentcomponent->addguifunc('vmblast_configpageload');
}

// This is called before the page is actually displayed, so we can use addguielem().
function vmblast_configpageload() {
	global $currentcomponent;

	// Init vars from $_REQUEST[]
	$action = isset($_REQUEST['action']) ? $_REQUEST['action']:null;
	$extdisplay = isset($_REQUEST['extdisplay']) ? $_REQUEST['extdisplay']:null;
	
	// Don't display this stuff it it's on a 'This xtn has been deleted' page.
	if ($action != 'del') {

		$default_group = sql("SELECT value FROM `admin` WHERE variable = 'default_vmblast_grp'", "getOne");
		$section = _("Default Group Inclusion");
		if ($default_group != "") {
			$in_default_vmblast_grp = vmblast_check_default($extdisplay);
			$currentcomponent->addguielem($section, new gui_selectbox('in_default_vmblast_grp', $currentcomponent->getoptlist('vmblast_group'), $in_default_vmblast_grp, _('Default VMblast Group'), _('You can include or exclude this extension/user from being part of the default voicemail blast group when creating or editing. Choosing this option will be ignored if the user does not have a voicemail box.'), false));
		} 
	}
}

function vmblast_configprocess() {
	global $db;

	//create vars from the request
	//
	$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$ext = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$extn = isset($_REQUEST['extension'])?$_REQUEST['extension']:null;
	$vm_enabled = isset($_REQUEST['vm']) && $_REQUEST['vm'] == 'enabled' ? true : false;
	$in_default_vmblast_grp = isset($_REQUEST['in_default_vmblast_grp'])?$_REQUEST['in_default_vmblast_grp']:false;

	$extdisplay = ($ext==='') ? $extn : $ext;
	
	if (($action == "add" || $action == "edit") && $vm_enabled) {
		if (!isset($GLOBALS['abort']) || $GLOBALS['abort'] !== true) {
			if ($in_default_vmblast_grp !== false) {
				vmblast_set_default($extdisplay, $in_default_vmblast_grp);
			}
		}
	} elseif ($extdisplay != '' && ($action == "del" || ($action == "edit" && !$vm_enabled))) {
		$sql = "DELETE FROM vmblast_groups WHERE ext = '$extdisplay'";
		sql($sql);
	}
}
?>
