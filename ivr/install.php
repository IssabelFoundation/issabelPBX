<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
require_once dirname(__FILE__)."/functions.inc.php";

global $db;
global $amp_conf;

if($amp_conf["AMPDBENGINE"] == "sqlite3")  {
	sql("CREATE TABLE IF NOT EXISTS `ivr_details` (
		`id` int(11) NOT NULL PRIMARY KEY AUTOINCREMENT,
		`name` varchar(50) default NULL,
		`description` varchar(150) default NULL,
		`announcement` int(11) default NULL,
		`directdial` varchar(50) default NULL,
		`invalid_loops` varchar(10) default NULL,
		`invalid_retry_recording` varchar(25) default NULL,
		`invalid_destination` varchar(50) default NULL,
		`timeout_enabled` varchar(50) default NULL,
		`invalid_recording` varchar(25) default NULL,
		`retvm` varchar(8) default NULL,
		`timeout_time` int(11) default NULL,
		`timeout_recording` varchar(25) default NULL,
		`timeout_retry_recording` varchar(25) default NULL,
		`timeout_destination` varchar(50) default NULL,
		`timeout_loops` varchar(10) default NULL,
		`timeout_append_announce` tinyint(1) NOT NULL default '1',
		`invalid_append_announce` tinyint(1) NOT NULL default '1',
		`timeout_ivr_ret` tinyint(1) NOT NULL default '0',
		`invalid_ivr_ret` tinyint(1) NOT NULL default '0')"
	);
} else {
	sql("CREATE TABLE IF NOT EXISTS `ivr_details` (
		`id` int(11) NOT NULL auto_increment,
		`name` varchar(50) default NULL,
		`description` varchar(150) default NULL,
		`announcement` int(11) default NULL,
		`directdial` varchar(50) default NULL,
		`invalid_loops` varchar(10) default NULL,
		`invalid_retry_recording` varchar(25) default NULL,
		`invalid_destination` varchar(50) default NULL,
		`timeout_enabled` varchar(50) default NULL,
		`invalid_recording` varchar(25) default NULL,
		`retvm` varchar(8) default NULL,
		`timeout_time` int(11) default NULL,
		`timeout_recording` varchar(25) default NULL,
		`timeout_retry_recording` varchar(25) default NULL,
		`timeout_destination` varchar(50) default NULL,
		`timeout_loops` varchar(10) default NULL,
		`timeout_append_announce` tinyint(1) NOT NULL default '1',
		`invalid_append_announce` tinyint(1) NOT NULL default '1',
		`timeout_ivr_ret` tinyint(1) NOT NULL default '0',
		`invalid_ivr_ret` tinyint(1) NOT NULL default '0',
		PRIMARY KEY  (`id`))"
	);
}


$ivr_modcurrentvers = modules_getversion('ivr');

$res = $db->getAll('SELECT * from ivr');
if($db->IsError($res)) {
	sql("CREATE TABLE IF NOT EXISTS `ivr_entries` (
		`ivr_id` int(11) NOT NULL,
		`selection` varchar(10) default NULL,
		`dest` varchar(50) default NULL,
		`ivr_ret` tinyint(1) NOT NULL default '0')");
	out('Migration 2.10 not needed');
} else {
	// Now, we need to check for upgrades.
	// V1.0, old IVR. You shouldn't see this, but check for it anyway, and assume that it's 2.0
	// V2.0, Original Release
	// V2.1, added 'directorycontext' to the schema
	// v2.2, announcement changed to support filenames instead of ID's from recordings table
	//
	if($amp_conf["AMPDBENGINE"] != "sqlite3")  { // As of 2.5 these are all in the sqlite3 schema
		// Add the col
		$sql = "SELECT dircontext FROM ivr";
		$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
		if($db->IsError($check)) {
			// add new field
			$sql = 'ALTER TABLE ivr ADD COLUMN dircontext VARCHAR ( 50 ) DEFAULT "default"';
			$result = $db->query($sql);
			if($db->IsError($result)) {
				die_issabelpbx($result->getDebugInfo());
			}
		}

		if ($ivr_modcurrentvers !== null && version_compare($ivr_modcurrentvers, "2.2", "<")) {
			// Change existing records
			$existing = sql("SELECT DISTINCT announcement FROM ivr WHERE displayname <> '__install_done' AND announcement IS NOT NULL", "getAll");
			foreach ($existing as $item) {
				$recid = $item[0];
				$sql = "SELECT filename FROM recordings WHERE id = '$recid' AND displayname <> '__invalid'";
				$recordings = sql($sql, "getRow");
				if (is_array($recordings)) {
					$filename = (isset($recordings[0]) ? $recordings[0] : '');
					if ($filename != '') {
						$sql = "UPDATE ivr SET announcement = '".str_replace("'", "''", $filename)."' WHERE announcement = '$recid'";
						$upcheck = $db->query($sql);
						if($db->IsError($upcheck))
						die_issabelpbx($upcheck->getDebugInfo());
					}
				}
			}
		}
	}
	// Version 2.5.7 adds auto-return to IVR
	$sql = "SELECT ivr_ret FROM ivr_dests";
	$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if($db->IsError($check)) {
		// add new field
		$sql = "ALTER TABLE ivr_dests ADD ivr_ret TINYINT(1) NOT NULL DEFAULT 0;";
		$result = $db->query($sql);
		if($db->IsError($result)) { die_issabelpbx($result->getDebugInfo()); }
	}

	$results = array();
	$sql = "SELECT ivr_id, selection, dest FROM ivr_dests";
	$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	if (!$db->IsError($results)) { // error - table must not be there
		foreach ($results as $result) {
			$old_dest  = $result['dest'];
			$ivr_id    = $result['ivr_id'];
			$selection = $result['selection'];

			$new_dest = merge_ext_followme(trim($old_dest));
			if ($new_dest != $old_dest) {
				$sql = "UPDATE ivr_dests SET dest = '$new_dest' WHERE ivr_id = $ivr_id AND selection = '$selection' AND dest = '$old_dest'";
				$results = $db->query($sql);
				if($db->IsError($results)) {
					die_issabelpbx($results->getMessage());
				}
			}
		}
	}

	// Version 2.5.17 adds improved i and t destination handling
	$sql = "SELECT alt_timeout FROM ivr";
	$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if($db->IsError($check)) {
		// add new field
		$sql = "ALTER TABLE ivr ADD alt_timeout VARCHAR(8);";
		$result = $db->query($sql);
		if($db->IsError($result)) { die_issabelpbx($result->getDebugInfo()); }
	}
	$sql = "SELECT alt_invalid FROM ivr";
	$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if($db->IsError($check)) {
		// add new field
		$sql = "ALTER TABLE ivr ADD alt_invalid VARCHAR(8);";
		$result = $db->query($sql);
		if($db->IsError($result)) { die_issabelpbx($result->getDebugInfo()); }
	}
	$sql = "SELECT `loops` FROM ivr";
	$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if($db->IsError($check)) {
		// add new field
		$sql = "ALTER TABLE ivr ADD `loops` TINYINT(1) NOT NULL DEFAULT 2;";
		$result = $db->query($sql);
		if($db->IsError($result)) { die_issabelpbx($result->getDebugInfo()); }
	}



	// Version 2.5 migrate to recording ids
	//
	outn(_("Checking if announcements need migration.."));
	$sql = "SELECT announcement_id FROM ivr";
	$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if($db->IsError($check)) {
		//  Add announcement_id field
		//
		out(_("migrating"));
		outn(_("adding announcement_id field.."));
		$sql = "ALTER TABLE ivr ADD announcement_id INTEGER";
		$result = $db->query($sql);
		if($db->IsError($result)) {
			out(_("fatal error"));
			die_issabelpbx($result->getDebugInfo());
		} else {
			out(_("ok"));
		}

		// Get all the valudes and replace them with announcement_id
		//
		outn(_("migrate to recording ids.."));
		$sql = "SELECT `ivr_id`, `announcement` FROM `ivr`";
		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if($db->IsError($results)) {
			out(_("fatal error"));
			die_issabelpbx($results->getDebugInfo());
		}
		$migrate_arr = array();
		$count = 0;
		foreach ($results as $row) {
			if (trim($row['announcement']) != '') {
				$rec_id = recordings_get_or_create_id($row['announcement'], 'ivr');
				$migrate_arr[] = array($rec_id, $row['ivr_id']);
				$count++;
			}
		}
		if ($count) {
			$compiled = $db->prepare('UPDATE `ivr` SET `announcement_id` = ? WHERE `ivr_id` = ?');
			$result = $db->executeMultiple($compiled,$migrate_arr);
			if($db->IsError($result)) {
				out(_("fatal error"));
				die_issabelpbx($result->getDebugInfo());
			}
		}
		out(sprintf(_("migrated %s entries"),$count));

		// Now remove the old recording field replaced by new id field
		//
		outn(_("dropping announcement field.."));
		$sql = "ALTER TABLE `ivr` DROP `announcement`";
		$result = $db->query($sql);
		if($db->IsError($result)) {
			out(_("no announcement field???"));
		} else {
			out(_("ok"));
		}

	} else {
		out(_("already migrated"));
	}

	// Version 2.5.19 add invalid and timeout messages
	//
	outn(_("Checking for timeout_id.."));
	$sql = "SELECT timeout_id FROM ivr";
	$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if($db->IsError($check)) {
		//  Add timeout_id field
		//
		$sql = "ALTER TABLE ivr ADD timeout_id INTEGER DEFAULT null";
		$result = $db->query($sql);
		if($db->IsError($result)) {
			out(_("fatal error"));
			die_issabelpbx($result->getDebugInfo());
		} else {
			out(_("added"));
		}
	} else {
		out(_("not needed"));
	}
	outn(_("Checking for invalid_id.."));
	$sql = "SELECT invalid_id FROM ivr";
	$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if($db->IsError($check)) {
		//  Add invalid_id field
		//
		$sql = "ALTER TABLE ivr ADD invalid_id INTEGER DEFAULT null";
		$result = $db->query($sql);
		if($db->IsError($result)) {
			out(_("fatal error"));
			die_issabelpbx($result->getDebugInfo());
		} else {
			out(_("added"));
		}
	} else {
		out(_("not needed"));
	}

	outn(_("Checking for retvm.."));
	$sql = "SELECT retvm FROM ivr";
	$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if($db->IsError($check)) {
		//  Add retvm field
		//
		$sql = "ALTER TABLE ivr ADD retvm VARCHAR(8);";
		$result = $db->query($sql);
		if($db->IsError($result)) {
			out(_("fatal error"));
			die_issabelpbx($result->getDebugInfo());
		} else {
			out(_("added"));
		}
	} else {
		out(_("not needed"));
	}

	$count = sql('SELECT COUNT(*) FROM `ivr` WHERE `enable_directory` = "CHECKED"','getOne');
	if ($count) {
	  global $db;
	  $notifications =& notifications::create($db); 
	  $extext = sprintf(_("There are %s IVRs that have the legacy Directory dialing enabled. This has been deprecated and will be removed from future releases. You should convert your IVRs to use the Directory module for this functionality and assign an IVR destination to a desired Directory. You can install the Directory module from the Online Module Repository"),$count);
	  $notifications->add_notice('ivr', 'DIRECTORY_DEPRECATED', sprintf(_('Deprecated Directory used by %s IVRs'),$count), $extext, '', true, true);
		out(_("posting notice about deprecated functionality"));
	}

	//migrate to 2.10 tables
	out('Begining migration 2.10...');

	//this is really superflorous, but the field may be lingering from the 2.5 days, so delete it just in case
	$check = $db->getRow('SELECT announcement FROM ivr', DB_FETCHMODE_ASSOC);
	if(!$db->IsError($check)) {
		sql("ALTER TABLE `ivr` DROP `announcement`");
	}
	
	 //this was installed perviously, but we perfer to use our old table when migrating
	sql('DROP TABLE ivr_details');
	sql('RENAME TABLE ivr TO ivr_details');
	sql('RENAME TABLE ivr_dests TO ivr_entries');
	sql('ALTER TABLE ivr_details 
	CHANGE ivr_id id int(11) NOT NULL AUTO_INCREMENT, 
	CHANGE displayname name varchar(50), 
	ADD description varchar(150) AFTER name,
	CHANGE announcement_id announcement varchar(25) AFTER description,
	CHANGE enable_directdial directdial varchar(50) AFTER announcement,
	CHANGE retvm retvm varchar(8) AFTER directdial,
	CHANGE alt_invalid invalid_enabled varchar(50) AFTER retvm,
	CHANGE alt_timeout timeout_enabled varchar(50) AFTER retvm,
	CHANGE loops invalid_loops varchar(10) AFTER directdial, 
	CHANGE invalid_id invalid_recording varchar(25) AFTER invalid_loops,
	ADD invalid_retry_recording varchar(25) AFTER invalid_loops,
	ADD invalid_destination varchar(50) AFTER invalid_retry_recording,
	CHANGE timeout timeout_time int(11),
	CHANGE timeout_id timeout_recording varchar(25), 
	ADD timeout_retry_recording varchar(25),
	ADD timeout_destination varchar(50),
	ADD timeout_loops varchar(11),
	ADD timeout_append_announce tinyint(1) NOT NULL default \'1\',
	ADD invalid_append_announce tinyint(1) NOT NULL default \'1\',
	ADD timeout_ivr_ret tinyint(1) NOT NULL default \'0\',
	ADD invalid_ivr_ret tinyint(1) NOT NULL default \'0\',
	DROP deptname, 
	DROP enable_directory, 
	DROP dircontext');
	
	sql('DELETE FROM ivr_details WHERE name = "__install_done"');
	//copy loops from invalid to timeout
	sql('UPDATE ivr_details SET timeout_loops = invalid_loops');
	$ivr = $db->getAll('SELECT * FROM ivr_details', DB_FETCHMODE_ASSOC);
	if($db->IsError($ivr)) {
		die_issabelpbx($ivr->getDebugInfo());
	}

	$entires = $db->getAll('SELECT * FROM ivr_entries', DB_FETCHMODE_ASSOC);
	if($db->IsError($ivr)) {
		die_issabelpbx($ivr->getDebugInfo());
	}
	foreach ($entires as $entrie) {
		$e[$entrie['ivr_id']][$entrie['selection']] = 
				array('dest' => $entrie['dest'], 'ivr_ret' => $entrie['ivr_ret']);
	}
	dbug('e', $e);
	dbug('ivr', $ivr);
	
	if ($ivr) {
		foreach ($ivr as $my => $i) {

			//INVALID
			//if we have an invalid destination in entires, move it here
			if (isset($e[$i['id']]['i']) && $e[$i['id']]['i']) {
				$ivr[$my]['invalid_destination'] = $e[$i['id']]['i']['dest'];
				$ivr[$my]['invalid_ivr_ret'] = $e[$i['id']]['i']['ivr_ret'];

				//if there are no invalid loops, set to disabled
				if ($i['invalid_loops'] < 0) {
					$ivr[$my]['invalid_loops'] = 'disabled';
					$ivr[$my]['invalid_retry_recording'] = '';
					$ivr[$my]['invalid_recording'] = '';
					$ivr[$my]['invalid_destination'] = '';
					$ivr[$my]['invalid_ivr_ret'] = '0';

				//if there are zero disabled loops, we dont need a retry recording
				} elseif ($i['invalid_loops'] === 0) {
					$ivr[$my]['invalid_retry_recording'] = '';
					$ivr[$my]['invalid_recording'] = 'default';

				//otherwise, set invalid retry to the save as invalid_recording
				} elseif ($i['invalid_loops'] > 0) {
					$ivr[$my]['invalid_retry_recording'] = $i['invalid_recording'];
					$ivr[$my]['invalid_recording'] = 'default';
					$ivr[$my]['invalid_ivr_ret'] = '0';
				}

			//if we DONT have an invalid destination, set everything to disbaled
			} else {
				$ivr[$my]['invalid_loops'] = 'disabled';
				$ivr[$my]['invalid_retry_recording'] = '';
				$ivr[$my]['invalid_recording'] = '';
				$ivr[$my]['invalid_destination'] = '';
			}

			//TIMEOUT
			//if we have an invalid destination in entires, move it here
			if (isset($e[$i['id']]['t']) && $e[$i['id']]['t']) {
				$ivr[$my]['timeout_destination'] = $e[$i['id']]['t']['dest'];
				$ivr[$my]['timeout_ivr_ret'] = $e[$i['id']]['t']['ivr_ret'];

				//if there are no timeout loops, set to disabled
				if ($i['timeout_loops'] < 0) {
					$ivr[$my]['timeout_loops'] = 'disabled';
					$ivr[$my]['timeout_retry_recording'] = '';
					$ivr[$my]['timeout_recording'] = '';
					$ivr[$my]['timeout_destination'] = '';
					$ivr[$my]['timeout_ivr_ret'] = '0';

				//if there are zero disabled loops, we dont need a retry recording
				} elseif ($i['timeout_loops'] === 0) {
					$ivr[$my]['timeout_retry_recording'] = '';
					$ivr[$my]['timeout_recording'] = 'default';
					
				//otherwise, set timeout retry to the save as invalid_recording
				} elseif ($i['timeout_loops'] > 0) {
					$ivr[$my]['timeout_retry_recording'] = $i['timeout_recording'];
					$ivr[$my]['timeout_recording'] = 'default';
				}

			//if we DONT have an timeout destination, set everything to disbaled
			} else {
				$ivr[$my]['timeout_loops'] = 'disabled';
				$ivr[$my]['timeout_retry_recording'] = '';
				$ivr[$my]['timeout_recording'] = '';
				$ivr[$my]['timeout_destination'] = '';
				$ivr[$my]['timeout_ivr_ret'] = '0';
			}
			
			//direct dial
			if ($i['directdial'] == 'CHECKED') {
				$ivr[$my]['directdial'] = 'ext-local';
			}
			
			//remove unneeded array entires
			unset($ivr[$my]['invalid_enabled'], $ivr[$my]['timeout_enabled']);
		}
	}
		dbug('ivr for insert', $ivr);
	$sql = $db->prepare('REPLACE INTO ivr_details (id, name, description, announcement,
				directdial, invalid_loops, invalid_retry_recording,
				invalid_destination, invalid_recording,
				retvm, timeout_time, timeout_recording,
				timeout_retry_recording, timeout_destination, timeout_loops, timeout_append_announce, invalid_append_announce, timeout_ivr_ret, invalid_ivr_ret)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
	$res = $db->executeMultiple($sql, $ivr);
	if ($db->IsError($res)){
		print_r($db->last_query);
		die_issabelpbx($res->getDebugInfo());
	}
	
	//remove all legacy t or i dests
	sql('DELETE FROM ivr_entries WHERE selection IN("t", "i")');
	sql('ALTER TABLE ivr_details DROP invalid_enabled, DROP timeout_enabled');
	
	out('Migration 2.10 done!');
} 

// Add timeout/invalid_append_announce if not there
//
outn(_("Checking for timeout_append_announce.."));
$sql = "SELECT timeout_append_announce FROM ivr_details";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if($db->IsError($check)) {
	//  Add timeout_append_announce field
	//
	$sql = "ALTER TABLE ivr_details ADD timeout_append_announce tinyint(1) NOT NULL DEFAULT '1'";
	$result = $db->query($sql);
	if($db->IsError($result)) {
		out(_("fatal error"));
		die_issabelpbx($result->getDebugInfo());
	} else {
		out(_("added"));
	}
} else {
	out(_("not needed"));
}

outn(_("Checking for invalid_append_announce.."));
$sql = "SELECT invalid_append_announce FROM ivr_details";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if($db->IsError($check)) {
	//  Add invalid_append_announce field
	//
	$sql = "ALTER TABLE ivr_details ADD invalid_append_announce tinyint(1) NOT NULL DEFAULT '1'";
	$result = $db->query($sql);
	if($db->IsError($result)) {
		out(_("fatal error"));
		die_issabelpbx($result->getDebugInfo());
	} else {
		out(_("added"));
	}
} else {
	out(_("not needed"));
}

// Add timeout/invalid_ivr_ret if not there
//
outn(_("Checking for timeout_ivr_ret.."));
$sql = "SELECT timeout_ivr_ret FROM ivr_details";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if($db->IsError($check)) {
	//  Add timeout_ivr_ret field
	//
	$sql = "ALTER TABLE ivr_details ADD timeout_ivr_ret tinyint(1) NOT NULL DEFAULT '0'";
	$result = $db->query($sql);
	if($db->IsError($result)) {
		out(_("fatal error"));
		die_issabelpbx($result->getDebugInfo());
	} else {
		out(_("added"));
	}
} else {
	out(_("not needed"));
}

outn(_("Checking for invalid_ivr_ret.."));
$sql = "SELECT invalid_ivr_ret FROM ivr_details";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if($db->IsError($check)) {
	//  Add invalid_ivr_ret field
	//
	$sql = "ALTER TABLE ivr_details ADD invalid_ivr_ret tinyint(1) NOT NULL DEFAULT '0'";
	$result = $db->query($sql);
	if($db->IsError($result)) {
		out(_("fatal error"));
		die_issabelpbx($result->getDebugInfo());
	} else {
		out(_("added"));
	}
} else {
	out(_("not needed"));
}

?>
