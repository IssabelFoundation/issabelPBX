<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
function miscapps_contexts() {
	// return an associative array with context and description
	foreach (miscapps_list() as $row) {
		$contexts[] = array(
			'context' => 'app-miscapps-'.$row['miscapps_id'], 
			'description'=> 'Misc Application: '.$row['description'],
			'source' => 'Misc Applications',
		);
	}
	return $contexts;
}

function miscapps_get_config($engine) {
	global $ext;
	switch ($engine) {
		case 'asterisk':
      $addit = false;
			foreach (miscapps_list(true) as $row) {
				if ($row['enabled']) {
          $ext->add('app-miscapps', $row['ext'], '', new ext_noop('Running miscapp '.$row['miscapps_id'].': '.$row['description']));
          $ext->add('app-miscapps', $row['ext'], '', new ext_macro('user-callerid'));
          $ext->add('app-miscapps', $row['ext'], '', new ext_goto($row['dest']));
          $addit = true;
				}
			}
      if ($addit) {
        $ext->addInclude('from-internal-additional', 'app-miscapps');
      }
		break;
	}
}


/**  Get a list of all miscapps
 * Optional parameter is get_ext. Potentially slow, because each row is extracted from the featurecodes table
 * one-by-one
 */
function miscapps_list($get_ext = false) {
	global $db;
	$sql = "SELECT miscapps_id, description, dest FROM miscapps ORDER BY description ";
	$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($results)) {
		die_issabelpbx($results->getMessage()."<br><br>Error selecting from miscapps");	
	}
	
	if ($get_ext) {
		foreach (array_keys($results) as $idx) {
			$fc = new featurecode('miscapps', 'miscapp_'.$results[$idx]['miscapps_id']);
			$results[$idx]['ext'] = $fc->getDefault();
			$results[$idx]['enabled'] = $fc->isEnabled();
		}
	}
	
	return $results;
}

function miscapps_get($miscapps_id) {
	global $db;
	$sql = "SELECT miscapps_id, description, ext, dest FROM miscapps WHERE miscapps_id = ".$db->escapeSimple($miscapps_id);
	$row = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($row)) {
		die_issabelpbx($row->getMessage()."<br><br>Error selecting row from miscapps");	
	}
	
	// we want to get the ext from featurecodes
	$fc = new featurecode('miscapps', 'miscapp_'.$row['miscapps_id']);
	$row['ext'] = $fc->getDefault();
	$row['enabled'] = $fc->isEnabled();

	return $row;
}

function miscapps_add($description, $ext, $dest) {
	global $db;
	$sql = "INSERT INTO miscapps (description, ext, dest) VALUES (".
		"'".$db->escapeSimple($description)."', ".
		"'".$db->escapeSimple($ext)."', ".
		"'".$db->escapeSimple($dest)."')";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getMessage().$sql);
	}
	//get id..
	$miscapps_id = $db->getOne('SELECT LAST_INSERT_ID()');
	if (DB::IsError($miscapps_id)) {
		//TODO -- handle this
	}
	
	$fc = new featurecode('miscapps', 'miscapp_'.$miscapps_id);
	$fc->setDescription($description);
	$fc->setDefault($ext, true);
	$fc->update();
}

function miscapps_delete($miscapps_id) {
	global $db;
	$sql = "DELETE FROM miscapps WHERE miscapps_id = ".$db->escapeSimple($miscapps_id);
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getMessage().$sql);
	}
	
	$fc = new featurecode('miscapps', 'miscapp_'.$miscapps_id);
	$fc->delete();
}

function miscapps_edit($miscapps_id, $description, $ext, $dest, $enabled=true) { 
	global $db;
	$sql = "UPDATE miscapps SET ".
		"description = '".$db->escapeSimple($description)."', ".
		"ext = '".$db->escapeSimple($ext)."', ".
		"dest = '".$db->escapeSimple($dest)."' ".
		"WHERE miscapps_id = ".$db->escapeSimple($miscapps_id);
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getMessage().$sql);
	}
	
	$fc = new featurecode('miscapps', 'miscapp_'.$miscapps_id);
	$fc->setDescription($description);
	$fc->setDefault($ext, true);
	$fc->setEnabled($enabled);
	$fc->update();
}

function miscapps_check_destinations($dest=true) {
	global $active_modules;

	$destlist = array();
	if (is_array($dest) && empty($dest)) {
		return $destlist;
	}
	$sql = "SELECT miscapps_id, dest, description FROM miscapps ";
	if ($dest !== true) {
		$sql .= "WHERE dest in ('".implode("','",$dest)."')";
	}
	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

	$type = isset($active_modules['miscapps']['type'])?$active_modules['miscapps']['type']:'setup';

	foreach ($results as $result) {
		$thisdest = $result['dest'];
		$thisid   = $result['miscapps_id'];
		$destlist[] = array(
			'dest' => $thisdest,
			'description' => sprintf(_("Misc Application: %s"),$result['description']),
			'edit_url' => 'config.php?display=miscapps&type='.$type.'&extdisplay='.urlencode($thisid),
		);
	}
	return $destlist;
}

function miscapps_change_destination($old_dest, $new_dest) {
	$sql = 'UPDATE miscapps SET dest = "' . $new_dest . '" WHERE dest = "' . $old_dest . '"';
	sql($sql, "query");
}
?>
