<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

/************************************************************************************************************
 * Hook Exentions/Users to allow an extension to indicate if the Queue should ignore it's state information
 * when it is acting as a Queue Member (Agent).
 */
function queues_get_qnostate($exten) {
	global $astman;

	// Retrieve the qnostate configuraiton from this user from ASTDB
	if ($astman) {
		$qnostate = $astman->database_get("AMPUSER",$exten."/queues/qnostate");
	} else {
		fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
	}

	// If it's blank, set it to 'usestate'
	return ($qnostate == 'ignorestate' ? $qnostate : 'usestate');
}

function queues_set_qnostate($exten,$qnostate) {
	global $astman;
	
	// Update the settings in ASTDB
	if ($astman) {
		$astman->database_put("AMPUSER",$exten."/queues/qnostate",$qnostate);
	} else {
		fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
	}
}

function queues_applyhooks() {
	global $currentcomponent;

	$currentcomponent->addoptlistitem('qnostate', 'usestate', _('Use State'));
	$currentcomponent->addoptlistitem('qnostate', 'ignorestate',_('Ignore State'));
	$currentcomponent->setoptlistopts('qnostate', 'sort', false);

	// Add the 'process' function - this gets called when the page is loaded, to hook into 
	// displaying stuff on the page.
	$currentcomponent->addguifunc('queues_configpageload',9);

}

// This is called before the page is actually displayed, so we can use addguielem().
function queues_configpageload() {
	global $currentcomponent;

	// Init vars from $_REQUEST[]
	$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	
	// Don't display this stuff it it's on a 'This xtn has been deleted' page.
	if ($action != 'del') {

		$qnostate = queues_get_qnostate($extdisplay);

		$section = _('Extension Options');
		$currentcomponent->addguielem($section, 
			new gui_selectbox('qnostate', 
				$currentcomponent->getoptlist('qnostate'), 
				$qnostate, 
				_('Queue State Detection'), 
				_("If this extension is part of a Queue then the Queue will "
				. "attempt to use the user's extension state or device state "
				. "information when determining if this queue member should be "
				. "called. In some uncommon situations such as a Follow-Me with no "
				. "physical device, or some virtual extension scenarios, the "
				. "state information will indicate that this member is not "
				. "available when they are. Setting this to 'Ignore State' will "
				. "make the Queue ignore all state information thus always trying "
				. "to contact this member. Certain side affects can occur when "
				. "this route is taken due to the nature of how Queues handle "
				. "Local channels, such as subsequent transfers will continue to "
				. "show the member as busy until the original call is terminated. "
				. "In most cases, this SHOULD BE set to 'Use State'."), false));
	}
}

function queues_configprocess() {
	//create vars from the request
	$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$id = isset($_REQUEST['id'])?$_REQUEST['id']:null;
	$ext = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$extn = isset($_REQUEST['extension'])?$_REQUEST['extension']:null;
	$qnostate = isset($_REQUEST['qnostate'])?$_REQUEST['qnostate']:null;
	

	if ($ext==='') { 
		$extdisplay = $extn; 
	} else {
		$extdisplay = $ext;
	} 
	if ($action == "add" || $action == "edit") {
		if (!isset($GLOBALS['abort']) || $GLOBALS['abort'] !== true) {
			queues_set_qnostate($extdisplay, $qnostate);
		}
	} // if 'del' then core will remove the entire tree
}

function queues_hook_core($viewing_itemid, $request) {
// This is empty. Need to be here for the queues_hookProcess_core function to work
}

function queues_hookProcess_core($viewing_itemid, $request) {
		global $db;

	if (!isset($request['action']))
		return;
	switch ($request['action']) {
		case 'del':
			// Get all members that exists in any queue, if any
			$members = queues_get_members();
			// We need to track the array index
			$item = 0;
			// Scan all members until we find a match
			foreach ($members as $member) {
				preg_match("/^Local\/([\d]+)\@*/",$member,$matches);
				if($matches[1] == $viewing_itemid) {
					// We got a match, now delete that member from all queues
					// Strip the penalty from the member
					$member_to_delete = explode(',',$members[$item]);
					$sql = "DELETE FROM queues_details WHERE data LIKE '$member_to_delete[0]%'";
					$result = $db->query($sql);
					if($db->IsError($result)) {
						die_issabelpbx($result->getMessage().$sql);
					}
					// Now we are done, no need to scan the rest of the entries
					// Break out of the foreach loop
					// TODO: I think it is neccessary to sort the flag value??
					break;
				}
				$item ++;
			}
		break;
	}
}
?>