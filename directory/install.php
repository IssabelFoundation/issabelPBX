<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
global $db, $amp_conf;

$autoincrement=(preg_match("/qlite/",$amp_conf["AMPDBENGINE"])) ? "AUTOINCREMENT":"AUTO_INCREMENT";

out(_('Adding directory tables if needed'));

$sql[] = "CREATE TABLE IF NOT EXISTS directory_details (
    id INT NOT NULL PRIMARY KEY $autoincrement,
    dirname varchar(50),
    description varchar(150),    
    announcement INT,
    callid_prefix varchar(10),
    alert_info varchar(50),
    repeat_loops varchar(3),
    repeat_recording INT,
    invalid_recording INT,
    invalid_destination varchar(50),
    retivr varchar(5),
    say_extension varchar(5)
)";


$sql[] = "CREATE TABLE IF NOT EXISTS directory_entries (
    id INT NOT NULL,
    name varchar(50),
    type varchar(25),
    foreign_id varchar(25),
    audio varchar(50),
    dial varchar(50) default ''
	)";

foreach ($sql as $s) {
	$do = $db->query($s);
	if (DB::IsError($do)) {
		out(_('Can not create table: ') . $check->getMessage());
		return false;
	}
}

$sql = "SELECT say_extension FROM directory_details";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
  // add new field
  outn(_("adding say_extension field to directory_details.."));
  $sql = "ALTER TABLE directory_details ADD say_extension VARCHAR(5)";
  $result = $db->query($sql);
  if(DB::IsError($result)) { 
    out(_("fatal error"));
    die_issabelpbx($result->getDebugInfo()); 
  } else {
    out(_("ok"));
  }
}

$sql = "SELECT valid_recording FROM directory_details";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(!DB::IsError($check)) {
	outn(_("dropping valid_details field.."));
	$sql = "ALTER TABLE `directory_details` DROP `valid_recording`";
 	$result = $db->query($sql);
 	if(DB::IsError($result)) { 
		out(_("no valid_recording field???"));
	} else {
		out(_("ok"));
	}
}

//
//add e_id field if it doesnt already exists
//
$sql = 'SHOW COLUMNS FROM directory_entries LIKE "e_id"';
$res = $db->getAll($sql);
//check to see if the field already exists
if (count($res) == 0) {
	//if not add it
	$sql = 'ALTER TABLE directory_entries ADD COLUMN e_id INT AFTER id';
	$do = $db->query($sql);
	if(DB::IsError($do)) { 
		out(_("cannot add field e_id to table directory_entries \n" . $do->getDebugInfo()));
	} else {
		out(_("e_id added to table directory_entries"));
	}
	//get ALL directory entires
	$sql = 'SELECT * FROM directory_entries';
	$de = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	$count = array();
	foreach($de as $d => $e) {
		if (!isset($count[$e['id']]) || $count[$e['id']] == '') {
			//set id and delete all entires for this ivr. This only gets run once per ivr
			$count[$e['id']] = 1;
			sql('DELETE FROM directory_entries WHERE id = "' . $e['id'] . '"');
		} else {
			$count[$e['id']]++;
		}
		$de[$d]['e_id'] = $count[$e['id']];
		//update entire
		$sql = 'INSERT INTO directory_entries (id, e_id, name, type, foreign_id, audio, dial) VALUES (?, ?, ?, ?, ?, ?, ?)';
		$do = $db->query($sql, $de[$d]);
		if(DB::IsError($do)) { 
			out(_('cannot set e_id for directory_id = ' . $e['id'] . '. Please resubmit this directory manually to correct this issue.'));
		}
	}
}

//check to see if there is a need to migrate from legacy directory
$migrated = $db->getOne("SELECT value FROM `admin` WHERE `variable` = 'directory28_migrated'");

// TODO: restrucutre without the die_issabelpbx() where it is not critical (e.g. creating new directory failures
//       should not kill the install
if (!$migrated) {
	//migrate legacy directories to new directory
	//get a list of vm users
	$vmconf = null;
	$section = null;
	$vmusers = array();
	parse_voicemailconf(rtrim($amp_conf["ASTETCDIR"],"/")."/voicemail.conf", $vmconf, $section);
	if (isset($vmconf) && $vmconf) {
		foreach ($vmconf['default'] as $ext => $vm) {
			$vmusers[$ext] = $vm['name'];
		}
	}


	//create a new directory if we have voicemail users
	if (isset($vmusers) && $vmusers) {
		out(_("Migrating Directory"));
		//TODO: make this the default directory
		$vals = array('Migrated Directory', '', '0', '', '', '2', 
						'0', '0', 'app-blackhole,hangup,1', '', '1');
		$sql = 'INSERT INTO directory_details (dirname, description, announcement,
					callid_prefix, alert_info, repeat_loops, repeat_recording,
					invalid_recording, invalid_destination, retivr, say_extension)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$new = $db->query($sql, $vals);
		if(DB::IsError($new)) { 
			die_issabelpbx(_('Error migrating to new directory! ERROR: Could not create new Directory.' . $new->getDebugInfo()));
		}
		//get the id of the new directory
		$sql = ((preg_match("/qlite/",$amp_conf["AMPDBENGINE"])) ? 'SELECT last_insert_rowid()' : 'SELECT LAST_INSERT_ID()');
		$newdir = $db->getOne($sql);
		$dirdest = 'directory,' . $newdir  . ',1';
	
		//insert all system users to the new directory, 
		//Just insert their ext. number, everythign else will be handeled automatically by directory
		$e_id = 0;
		foreach ($vmusers as $ext => $user) {
			$vals = array($newdir, $e_id++, 'user', $ext, 'vm');
			$sql = 'INSERT INTO directory_entries (id, e_id, type, foreign_id, audio) 
					VALUES (?, ?, ?, ?, ?)';
			$q = $db->query($sql, $vals);
			if(DB::IsError($q)) { 
				die_issabelpbx(_('Error migrating to new directory! ERROR: Could not populate new Directory ' . $q->getDebugInfo()));
			}
		}
	
		//set as default directory
		if (!isset($def_dir) || !$def_dir) {
			out(_("Setting migrated directory as default"));
			$sql = 'REPLACE INTO `admin` (`variable`, value) VALUES ("default_directory", ?)';
			$db->query($sql, $newdir);
		}
	}

	//Seem where done with migration - mark that in the database
	$migrated_dir = (isset($newdir) && $newdir != "") ? $newdir : 'true';
	$q = $db->query("REPLACE INTO `admin` (`variable`, value) VALUES ('directory28_migrated', '$migrated_dir')");
	if(DB::IsError($q)) { 
		die_issabelpbx(_('Error migrating to new directory! ERROR: Unable to mark Directory as migrated. Migration will probably be run again at next install/upgrade of this module. ' . $q->getDebugInfo()));
	}
	out(_('Migration Complete!'));
}
?>
