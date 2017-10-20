<?php /* $Id: install.php $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

	function _timeconditions_timegroups_add_group_timestrings($description,$timestrings) {
		global $db;

		$sql = "insert timegroups_groups(description) VALUES ('$description')";
		$db->query($sql);
		if(method_exists($db,'insert_id')) {
			$timegroup = $db->insert_id();
		} else {
			$timegroup = $amp_conf["AMPDBENGINE"] == "sqlite3" ? sqlite_last_insert_rowid($db->connection) : mysql_insert_id($db->connection);
		}
		_timeconditions_timegroups_edit_timestrings($timegroup,$timestrings);
		return $timegroup;
	}

	function _timeconditions_timegroups_get_times($timegroup) {
		global $db;

		$sql = "select id, time from timegroups_details where timegroupid = $timegroup";
		$results = $db->getAll($sql);
		if(DB::IsError($results)) {
			$results = null;
		}
		foreach ($results as $val) {
			$tmparray[] = array($val[0], $val[1]);
		}
		return $tmparray;
	}

	function _timeconditions_timegroups_edit_timestrings($timegroup,$timestrings) {
		global $db;

		$sql = "delete from timegroups_details where timegroupid = $timegroup";
		$db->query($sql);
		foreach ($timestrings as $key=>$val) {
			$time = $val;
			if (isset($time) && $time != '' && $time <> '*|*|*|*') {
				$sql = "insert timegroups_details (timegroupid, time) values ($timegroup, '$time')";
				$db->query($sql);
			}
		}
	}

if($amp_conf["AMPDBENGINE"] == "sqlite3")  {
	$sql = "
	CREATE TABLE IF NOT EXISTS timeconditions (
		`timeconditions_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
		`displayname` VARCHAR( 50 ) ,
		`time` INT( 11 ) ,
		`truegoto` VARCHAR( 50 ) ,
		`falsegoto` VARCHAR( 50 ),
		`deptname` VARCHAR( 50 ),
    `generate_hint` TINYINT( 1 ) DEFAULT 0,
	`priority` VARCHAR( 50 )
	)
	";
}
else  {
	$sql = "
	CREATE TABLE IF NOT EXISTS timeconditions (
		`timeconditions_id` INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
		`displayname` VARCHAR( 50 ) ,
		`time` INT( 11 ) ,
		`truegoto` VARCHAR( 50 ) ,
		`falsegoto` VARCHAR( 50 ),
		`deptname` VARCHAR( 50 ),
    `generate_hint` TINYINT( 1 ) DEFAULT 0,
	`priority` VARCHAR( 50 )
	)
	";
}
$check = $db->query($sql);
if(DB::IsError($check)) {
		die_issabelpbx("Can not create `timeconditions` table: " .  $check->getMessage() .  "\n");
}
if($amp_conf["AMPDBENGINE"] == "sqlite3")  {

	$sql = "
	CREATE TABLE IF NOT EXISTS `timegroups_groups` (
		`id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
		`description` varchar(50) NOT NULL default '',
		UNIQUE (`description`)
	)	
	";
}
else  {
	$sql = "
	CREATE TABLE IF NOT EXISTS `timegroups_groups` (
  		`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  		`description` varchar(50) NOT NULL default '',
 		 UNIQUE KEY `display` (`description`)
	) AUTO_INCREMENT = 1 
	";
}
$check = $db->query($sql);
if(DB::IsError($check)) {
	die_issabelpbx("Can not create `timeconditions` table: " .  $check->getMessage() .  "\n");
}

if($amp_conf["AMPDBENGINE"] == "sqlite3")  {

	$sql = "
	CREATE TABLE IF NOT EXISTS `timegroups_details` (
		`id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
		`timegroupid` int(11) NOT NULL default '0',
		`time` varchar(100) NOT NULL default ''
	) 
	";
}
else  {
	$sql = "
	CREATE TABLE IF NOT EXISTS `timegroups_details` (
		`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
		`timegroupid` int(11) NOT NULL default '0',
		`time` varchar(100) NOT NULL default ''
	)
	";
}
$check = $db->query($sql);
if(DB::IsError($check)) {
	die_issabelpbx("Can not create `timeconditions` table: " .  $check->getMessage() .  "\n");
}

// Merge old findmefollow destinations to extension
//
$results = array();
$sql = "SELECT timeconditions_id, truegoto, falsegoto FROM timeconditions";
$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
if (!DB::IsError($results)) { // error - table must not be there
	foreach ($results as $result) {
		$old_false_dest    = $result['falsegoto'];
		$old_true_dest     = $result['truegoto'];
		$timeconditions_id = $result['timeconditions_id'];

		$new_false_dest = merge_ext_followme(trim($old_false_dest));
		$new_true_dest  = merge_ext_followme(trim($old_true_dest));
		if (($new_true_dest != $old_true_dest) || ($new_false_dest != $old_false_dest)) {
			$sql = "UPDATE timeconditions SET truegoto = '$new_true_dest', falsegoto = '$new_false_dest' WHERE timeconditions_id = $timeconditions_id  AND truegoto = '$old_true_dest' AND falsegoto ='$old_false_dest'";
			$results = $db->query($sql);
			if(DB::IsError($results)) {
				die_issabelpbx($results->getMessage());
			}
		}
	}
}

/* Upgrade to 2.5
 * Migrate time conditions to new time conditions groups
 */
timeconditions_updatedb();

/* Alter the time field to int now that it refernces the id field in groups
 */
// sqlite3 support as of 2.5 has correct table structure built into the CREATE
if($amp_conf["AMPDBENGINE"] != "sqlite3")  {
	outn(_("converting timeconditions time field to int.."));
	$sql = "ALTER TABLE `timeconditions` CHANGE `time` `time` INT (11)";
	$results = $db->query($sql);
	if(DB::IsError($results)) {
		out(_("ERROR: failed to convert field ").$results->getMessage());
	} else {
		out(_("OK"));
	}
}

outn(_("checking for generate_hint field.."));
$sql = "SELECT `generate_hint` FROM timeconditions";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
	$sql = "ALTER TABLE timeconditions ADD `generate_hint` TINYINT( 1 ) DEFAULT 0";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo());
	}
	out(_("OK"));
} else {
	out(_("already exists"));
}

  // Generate feature codes for all configured time conditions in case this is being transitioned
  //
outn(_("generating feature codes if needed.."));
$results = sql("SELECT timeconditions_id, displayname FROM timeconditions","getAll",DB_FETCHMODE_ASSOC);
if (is_array($results)) foreach ($results as $item) {
  $id = $item['timeconditions_id'];
  $displayname = $item['displayname'];
  $fcc = new featurecode('timeconditions', 'toggle-mode-'.$id);
  if ($displayname) {
    $fcc->setDescription("$id: $displayname");
  } else {
    $fcc->setDescription($id._(": Time Condition Override"));
  }
  $fcc->setDefault('*27'.$id);
  $fcc->update();
  unset($fcc);	
}

$fcc = new featurecode('timeconditions', 'toggle-mode-all');
$fcc->setDescription("All: Time Condition Override");
$fcc->setDefault('*27');
$fcc->update();
unset($fcc);	
out(_("OK"));


// bring db up to date on install/upgrade
//
function timeconditions_updatedb() {
	$ver = modules_getversion('timeconditions');
	if ($ver !== null && version_compare_issabel($ver,'2.5','lt')) { 
		outn(_("Checking for old timeconditions to upgrade.."));
		$upgradelist = timeconditions_list_forupgrade();
		if (isset($upgradelist)) { 
			// we have old conditions to upgrade
			//
			out(_("starting migration"));
			foreach($upgradelist as $upgrade) {
				$times[] = $upgrade['time'];
				$newid = _timeconditions_timegroups_add_group_timestrings('migrated-'.$upgrade['displayname'],$times);
				timeconditions_set_timegroupid($upgrade['timeconditions_id'],$newid);
				$newtimes = _timeconditions_timegroups_get_times($newid);
				out(sprintf(_("Upgraded %s and created group %s"), $upgrade['displayname'], 'migrated-'.$upgrade['displayname']));
				if (!is_array($newtimes)) {
					out(sprintf(_("%sWARNING:%s No time defined for this condition, please review"),"<font color='red'>","</font>"));
				}
				unset($times);
			}
		} else {
			out(_("no upgrade needed"));
		}
	}
}

function timeconditions_list_forupgrade() {
	$results = sql("SELECT * FROM timeconditions","getAll",DB_FETCHMODE_ASSOC);
	if(is_array($results)){
		foreach($results as $result){
			$list[] = $result;
		}
	}
	if (isset($list)) {
		return $list;
	} else { 
		return null;
	}
}

function timeconditions_set_timegroupid($id, $timegroup) {
	sql("UPDATE timeconditions SET time = $timegroup WHERE timeconditions_id = $id;");
}

$issabelpbx_conf =& issabelpbx_conf::create();

// TCINTERVAL
//
$set['value'] = '60';
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'timeconditions';
$set['category'] = 'Time Condition Module';
$set['emptyok'] = 0;
$set['name'] = 'Maintenance Polling Interval';
$set['description'] = 'The polling interval in seconds used by the Time Conditions manintenace task, launched by an Asterisk call file used to update Time Conditions override states as well as keep custom device state hint values up-to-date when being used with BLF. A shorter interval will assure that BLF keys states are accurate. The interval should be less than the shortest configured span between two time condition states, so that a manual overide during such a period is properly reset when the new period starts.';
$set['type'] = CONF_TYPE_SELECT;
$set['options'] = '60, 120, 180, 240, 300, 600, 900';
$issabelpbx_conf->define_conf_setting('TCINTERVAL',$set);

// TCMAINT
//
$set['value'] = true;
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'timeconditions';
$set['category'] = 'Time Condition Module';
$set['emptyok'] = 0;
$set['name'] = 'Enable Maintenance Polling';
$set['description'] = 'If set to false, this will override the execution of the Time Conditons maintenace task launched by call files. If all the feature codes for time conditions are disabled, the maintenance task will not be launched anyhow. Setting this to false would be fairly un-common. You may want to set this temporarily if debugging a system to avoid the periodic dialplan running through the CLI that the maintenance task launches and can be distracting.';
$set['type'] = CONF_TYPE_BOOL;
$issabelpbx_conf->define_conf_setting('TCMAINT',$set);

$issabelpbx_conf->commit_conf_settings();

if (!$db->getAll('SHOW COLUMNS FROM timeconditions WHERE FIELD = "priority"')) {
	out("Adding Time Conditions Priority");
    $sql = "ALTER TABLE `timeconditions` ADD COlUMN `priority` VARCHAR( 50 ) NOT NULL DEFAULT '0'";
    $result = $db->query($sql);
}
