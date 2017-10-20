<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
class manager_conf {

	var $_managers = array();

	// return the filename to write
	function get_filename() {
		return "manager_additional.conf";
	}
	function addManager($name, $secret, $deny, $permit, $read, $write) {
		$this->_managers[$name]['secret'] = $secret;
		$this->_managers[$name]['deny'] = $deny;
		$this->_managers[$name]['permit'] = $permit;
		$this->_managers[$name]['read'] = $read;
		$this->_managers[$name]['write'] = $write;
	}
	// return the output that goes in the file
	function generateConf() {
		$output = "";
		foreach ($this->_managers as $name => $settings) {
			$output .= "[".$name."]\n";
			foreach ($settings as $key => $value) {
				switch ($key) {
				case 'secret':
				case 'read':
				case 'write':
					$output .= $key . " = " . $value . "\n";
					break;
				case 'permit':
				case 'deny':
					$tmp = explode("&", $value);
					foreach ($tmp as $addr) {
						if ($addr != '') {
							$output .= $key . "=" . $addr . "\n";
						}
					}
					break;
				}
			}
		}
		$output .= "\n";
		return $output;
	}
}

function manager_get_config($engine) {
	global $manager_conf;

	switch($engine) {
		case "asterisk":
			$managers = manager_list();
			if (is_array($managers)) {
				foreach ($managers as $manager) {
					$m = manager_get($manager['name']);
					$manager_conf->addManager($m['name'], $m['secret'], $m['deny'], $m['permit'], $m['read'], $m['write']);
				}
			}
			break;
	}
}

// Get the manager list
function manager_list() {
	global $db;
	$sql = "SELECT name, secret FROM manager ORDER BY name";
	$res = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($res)) {
		return null;
	}
	return $res;
}

// Get manager infos
function manager_get($p_name) {
	global $db;
	$sql = "SELECT name,secret,deny,permit,`read`,`write` FROM manager WHERE name = '$p_name'";
	$res = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	return $res;
}

// Used to set the correct values for the html checkboxes
function manager_format_out($p_tab) {
	$res['name'] = $p_tab['name'];
	$res['secret'] = $p_tab['secret'];
	$res['deny'] = $p_tab['deny'];
	$res['permit'] = $p_tab['permit'];

	$tmp = explode(',', $p_tab['read']);
	foreach($tmp as $item) {
		$res['r'.$item] = true;
	}

	$tmp = explode(',', $p_tab['write']);
	foreach($tmp as $item) {
		$res['w'.$item] = true;
	}

	return $res;
}

// Delete a manager
function manager_del($p_name) {
	$results = sql("DELETE FROM manager WHERE name = \"$p_name\"","query");
}

function manager_format_in($p_tab) {
	if (!isset($res['read'])) {
		$res['read'] = "";
	}
	if (!isset($res['write'])) {
		$res['write'] = "";
	}
	if (isset($p_tab['rsystem']))
		$res['read'] .= "system,";
	if (isset($p_tab['rcall']))
		$res['read'] .= "call,";
	if (isset($p_tab['rlog']))
		$res['read'] .= "log,";
	if (isset($p_tab['rverbose']))
		$res['read'] .= "verbose,";
	if (isset($p_tab['rcommand']))
		$res['read'] .= "command,";
	if (isset($p_tab['ragent']))
		$res['read'] .= "agent,";
	if (isset($p_tab['ruser']))
		$res['read'] .= "user,";

  // Added for 1.6+
	if (isset($p_tab['rconfig']))
		$res['read'] .= "config,";
	if (isset($p_tab['rdtmf']))
		$res['read'] .= "dtmf,";
	if (isset($p_tab['rreporting']))
		$res['read'] .= "reporting,";
	if (isset($p_tab['rcdr']))
		$res['read'] .= "cdr,";
	if (isset($p_tab['rdialplan']))
		$res['read'] .= "dialplan,";
	if (isset($p_tab['roriginate']))
		$res['read'] .= "originate,";

	if (isset($p_tab['rmessage']))
		$res['read'] .= "message,";

	if (isset($p_tab['wsystem']))
		$res['write'] .= "system,";
	if (isset($p_tab['wcall']))
		$res['write'] .= "call,";
	if (isset($p_tab['wlog']))
		$res['write'] .= "log,";
	if (isset($p_tab['wverbose']))
		$res['write'] .= "verbose,";
	if (isset($p_tab['wcommand']))
		$res['write'] .= "command,";
	if (isset($p_tab['wagent']))
		$res['write'] .= "agent,";
	if (isset($p_tab['wuser']))
		$res['write'] .= "user,";

  // Added for 1.6+
	if (isset($p_tab['wconfig']))
		$res['write'] .= "config,";
	if (isset($p_tab['wdtmf']))
		$res['write'] .= "dtmf,";
	if (isset($p_tab['wreporting']))
		$res['write'] .= "reporting,";
	if (isset($p_tab['wcdr']))
		$res['write'] .= "cdr,";
	if (isset($p_tab['wdialplan']))
		$res['write'] .= "dialplan,";
	if (isset($p_tab['woriginate']))
		$res['write'] .= "originate,";

	if (isset($p_tab['wmessage']))
		$res['write'] .= "message,";

  $res['read'] = rtrim($res['read'],',');
  $res['write'] = rtrim($res['write'],',');
	return $res;
}

// Add a manager
function manager_add($p_name, $p_secret, $p_deny, $p_permit, $p_read, $p_write) {
	global $amp_conf;
	$managers = manager_list();
	$ampuser = $amp_conf['AMPMGRUSER'];
	if($p_name == $ampuser) {
		echo "<script>javascript:alert('"._("This manager already exists")."');</script>";
		return false;
	}
	if (is_array($managers)) {
		foreach ($managers as $manager) {
			if ($manager['name'] === $p_name) {
				echo "<script>javascript:alert('"._("This manager already exists")."');</script>";
				return false;
			}
		}
	}
	$results = sql("INSERT INTO manager set name='$p_name' , secret='$p_secret' , deny='$p_deny' , permit='$p_permit' , `read`='$p_read' , `write`='$p_write'");
}


// Asterisk API Module hooking
// Input:
//   $p_manager = default selected user
//   $dummy = unused
// $viewing_itemid, $target_menuid
function manager_hook_phpagiconf($viewing_itemid, $target_menuid) {
        global $db;

	switch($target_menuid) {
		case 'phpagiconf':
			$sql = "SELECT asman_user FROM phpagiconf";
			$res = $db->getRow($sql, DB_FETCHMODE_ASSOC);
			if(DB::IsError($res)) {
				return null;
			}
			$selectedmanager = $res['asman_user'];
		break;
	}
	$output = "<tr><td><a href=\"#\" class=\"info\">"._("Choose Manager:")."<span>"._("Choose the user that PHPAGI will use to connect the Asterisk API.")."</span></a></td><td><select name=\"asmanager\">";
	$selected = "";
	$managers = manager_list();
	foreach ($managers as $manager) {
		($manager['name'] === $selectedmanager) ? $selected="selected=\"selected\"" : $selected="";
		$output .= "<option value=\"".$manager['name']."/".$manager['secret']."\" $selected>".$manager['name'];
	}
	$output .="</select></td></tr>";
	return $output;
}

?>
