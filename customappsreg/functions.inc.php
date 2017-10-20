<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
function customappsreg_destinations() {
	// return an associative array with destination and description
	foreach (customappsreg_customdests_list() as $row) {
		$extens[] = array('destination' => $row['custom_dest'], 'description' => $row['description'], 'category' => _("Custom Destinations"), 'id' => 'customdests');
	}
	return isset($extens)?$extens:null;

}

/** the 'exten' is the same as the destination for this module
 */
function customappsreg_customdests_getdest($exten) {
	return array($exten);
}

/** If this is ours, we return it, otherwise we return false
 *  We use just use customappsreg and not the display because it
 *  is a per-module routine
 */
function customappsreg_getdestinfo($dest) {
	global $active_modules;

  $thisexten = customappsreg_customdests_get($dest);
	if (empty($thisexten)) {
		return false;
	} else {
		$type = isset($active_modules['customappsreg']['type'])?$active_modules['customappsreg']['type']:'tool';
		return array('description' => sprintf(_("Custom Destination: %s"),$thisexten['description']),
		             'edit_url' => 'config.php?display=customdests&type='.$type.'&extdisplay='.urlencode($dest),
							  );
	}
}

function customappsreg_check_extensions($exten=true) {
	global $active_modules;

	$extenlist = array();
	if (is_array($exten) && empty($exten)) {
		return $extenlist;
	}
	$sql = "SELECT custom_exten, description FROM custom_extensions ";
	if (is_array($exten)) {
		$sql .= "WHERE custom_exten in ('".implode("','",$exten)."')";
	}
	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

	$type = isset($active_modules['customappsreg']['type'])?$active_modules['customappsreg']['type']:'tool';

	foreach ($results as $result) {
		$thisexten = $result['custom_exten'];
		$extenlist[$thisexten]['description'] = _("Custom Extension: ").$result['description'];
		$extenlist[$thisexten]['status'] = 'INUSE';
		$extenlist[$thisexten]['edit_url'] = 'config.php?display=customextens&extdisplay='.urlencode($thisexten);
	}
	return $extenlist;
}

function customappsreg_customdests_list() {
	global $db;
	$sql = "SELECT custom_dest, description, notes FROM custom_destinations ORDER BY description";
	$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($results)) {
		die_issabelpbx($results->getMessage()."<br><br>Error selecting from custom_destinations");	
	}
	return $results;
}

function customappsreg_customextens_list() {
	global $db;
	$sql = "SELECT custom_exten, description, notes FROM custom_extensions ORDER BY custom_exten";
	$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($results)) {
		die_issabelpbx($results->getMessage()."<br><br>Error selecting from custom_extensions");	
	}
	return $results;
}

function customappsreg_customdests_get($custom_dest) {
	global $db;
	$sql = "SELECT custom_dest, description, notes FROM custom_destinations WHERE custom_dest = ".q($custom_dest);
	$row = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($row)) {
		die_issabelpbx($row->getMessage()."<br><br>Error selecting row from custom_destinations");	
	}
	return $row;
}

function customappsreg_customextens_get($custom_exten) {
	global $db;
	$sql = "SELECT custom_exten, description, notes FROM custom_extensions WHERE custom_exten = ".q($custom_exten);
	$row = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($row)) {
		die_issabelpbx($row->getMessage()."<br><br>Error selecting row from custom_extensions");	
	}
	return $row;
}

function customappsreg_customdests_add($custom_dest, $description, $notes) {
	global $db;

  if (!preg_match("/[^,]+,[^,]+,[^,]+/",$custom_dest)) {
		echo "<script>javascript:alert('"._('Invalid Destination, must not be blank, must be formatted as: context,exten,pri')."')</script>";
		return false;
	}
	if (trim($description) == '') {
		echo "<script>javascript:alert('"._('Invalid description specified, must not be blank')."')</script>";
		return false;
	}
	$usage_list = framework_identify_destinations($custom_dest, $module_hash=false);
	if (!empty($usage_list[$custom_dest])) {
		echo "<script>javascript:alert('"._('DUPLICATE Destination: This destination is already in use')."')</script>";
		return false;
	}

	$custom_dest = sql_formattext($custom_dest);
	$description = sql_formattext($description);
	$notes       = sql_formattext($notes);
	$sql = "INSERT INTO custom_destinations (custom_dest, description, notes) VALUES ($custom_dest, $description, $notes)";
	$results = $db->query($sql);
	if (DB::IsError($results)) {
		if ($results->getCode() == DB_ERROR_ALREADY_EXISTS) {
			echo "<script>javascript:alert('"._('DUPLICATE Destination: This destination is in use or potentially used by another module')."')</script>";
			return false;
		} else {
			die_issabelpbx($results->getMessage()."<br><br>".$sql);
		}
	}
	return true;
}

function customappsreg_customextens_add($custom_exten, $description, $notes) {
	global $db;

	if ($custom_exten == '') {
		echo "<script>javascript:alert('"._('Invalid Extension, must not be blank')."')</script>";
		return false;
	}
	if (trim($description) == '') {
		echo "<script>javascript:alert('"._('Invalid description specified, must not be blank')."')</script>";
		return false;
	}

	$custom_exten = sql_formattext($custom_exten);
	$description  = sql_formattext($description);
	$notes        = sql_formattext($notes);
	$sql = "INSERT INTO custom_extensions (custom_exten, description, notes) VALUES ($custom_exten, $description, $notes)";
	$results = $db->query($sql);
	if (DB::IsError($results)) {
		if ($results->getCode() == DB_ERROR_ALREADY_EXISTS) {
			echo "<script>javascript:alert('"._('DUPLICATE Extension: This extension already in use')."')</script>";
			return false;
		} else {
			die_issabelpbx($results->getMessage()."<br><br>".$sql);
		}
	}
	return true;
}

function customappsreg_customdests_delete($custom_dest) {
	global $db;

	$sql = "DELETE FROM custom_destinations WHERE custom_dest = ".q($custom_dest);
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getMessage().$sql);
	}
}

function customappsreg_customextens_delete($custom_exten) {
	global $db;

	$sql = "DELETE FROM custom_extensions WHERE custom_exten = ".q($custom_exten);
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getMessage().$sql);
	}
}

function customappsreg_customdests_edit($old_custom_dest, $custom_dest,  $description, $notes) { 
	global $db;

	if ($old_custom_dest != $custom_dest) {
		$usage_list = framework_identify_destinations($custom_dest, $module_hash=false);
		if (!empty($usage_list[$custom_dest])) {
			echo "<script>javascript:alert('"._('DUPLICATE Destination: This destination is in use or potentially used by another module')."')</script>";
			return false;
		}
	}

	$sql = "UPDATE custom_destinations SET ".
		"custom_dest = ".sql_formattext($custom_dest).", ".
		"description = ".sql_formattext($description).", ".
		"notes = ".sql_formattext($notes)." ".
		"WHERE custom_dest = ".sql_formattext($old_custom_dest);
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getMessage().$sql);
	}
}

function customappsreg_customextens_edit($old_custom_exten, $custom_exten,  $description, $notes) { 
	global $db;

	$sql = "UPDATE custom_extensions SET ".
		"custom_exten = ".sql_formattext($custom_exten).", ".
		"description = ".sql_formattext($description).", ".
		"notes = ".sql_formattext($notes)." ".
		"WHERE custom_exten = ".sql_formattext($old_custom_exten);
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getMessage().$sql);
	}
}

function customappsreg_customdests_getunknown() {

	$results = array();

	$my_probs = framework_list_problem_destinations($my_hash, false);

	if (!empty($my_probs)) {
		foreach ($my_probs as $problem) {
			if ($problem['status'] == 'CUSTOM') {
				$results[] = $problem['dest'];
			}
		}
	}
	return array_unique($results);
}

?>
