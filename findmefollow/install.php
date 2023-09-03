<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//for translation only
if (false) {
__("Findme Follow Toggle");
}

global $db;
global $amp_conf;
global $astman;

$sql = "
CREATE TABLE IF NOT EXISTS `findmefollow` 
( 
	`grpnum` VARCHAR( 20 ) NOT NULL , 
	`strategy` VARCHAR( 50 ) NOT NULL , 
	`grptime` SMALLINT NOT NULL , 
	`grppre` VARCHAR( 100 ) NULL , 
	`grplist` VARCHAR( 255 ) NOT NULL , 
	`annmsg_id` INTEGER,
	`postdest` VARCHAR( 255 ) NULL , 
	`dring` VARCHAR ( 255 ) NULL , 
	`remotealert_id` INTEGER,
	`needsconf` VARCHAR ( 10 ), 
	`toolate_id` INTEGER,
	`pre_ring` SMALLINT NOT NULL DEFAULT 0, 
	PRIMARY KEY  (`grpnum`) 
) ";

if(preg_match("/mysql/",$amp_conf["AMPDBENGINE"]))  { $sql.=" DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";  }

$check = $db->query($sql);
if(DB::IsError($check)) {
	die_issabelpbx("Can not create annoucment table");
}

//TODO: Also need to create all the states if enabled

$fcc = new featurecode('findmefollow', 'fmf_toggle');
$fcc->setDescription('Findme Follow Toggle'); 
$fcc->setDefault('*21');
$fcc->update();
unset($fcc);

// Adding support for a pre_ring before follow-me group
$sql = "SELECT pre_ring FROM findmefollow";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE findmefollow ADD pre_ring SMALLINT( 6 ) NOT NULL DEFAULT 0 ;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_issabelpbx($result->getDebugInfo()); }
}
// If there is no needsconf then this is a really old upgrade. We create the 2 old fields
// here  and then the migration code below will change them as needed but will work properly
// since it now has the fields it is expecting
//
$sql = "SELECT needsconf FROM findmefollow";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE findmefollow ADD remotealert VARCHAR( 80 ) NULL ;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_issabelpbx($result->getDebugInfo()); }

    $sql = "ALTER TABLE findmefollow ADD needsconf VARCHAR( 10 ) NULL ;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_issabelpbx($result->getDebugInfo()); }

    $sql = "ALTER TABLE findmefollow ADD toolate VARCHAR( 80 ) NULL ;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_issabelpbx($result->getDebugInfo()); }
}
// Version 2.1 upgrade. Add support for ${DIALOPTS} override, playing MOH
$sql = "SELECT ringing FROM findmefollow";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// add new field
    $sql = "ALTER TABLE findmefollow ADD ringing VARCHAR( 80 ) NULL ;";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_issabelpbx($result->getDebugInfo()); }
}
// increase size for older installs, ignore sqlite3, doesn't support ALTER...CHANGE syntax, table created properly above
if(!preg_match("/qlite/",$amp_conf["AMPDBENGINE"]))  {
	$db->query("ALTER TABLE findmefollow CHANGE dring dring VARCHAR( 255 ) NULL");
}
$results = array();
$sql = "SELECT grpnum, postdest FROM findmefollow";
$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
if (!DB::IsError($results)) { // error - table must not be there
	foreach ($results as $result) {
		$old_dest  = $result['postdest'];
		$grpnum    = $result['grpnum'];

		$new_dest = merge_ext_followme(trim($old_dest));
		if ($new_dest != $old_dest) {
			$sql = "UPDATE findmefollow SET postdest = '$new_dest' WHERE grpnum = '$grpnum'  AND postdest = '$old_dest'";
			$results = $db->query($sql);
			if(DB::IsError($results)) {
				die_issabelpbx($results->getMessage());
			}
		}
	}
}

// this function builds the AMPUSER/<grpnum>/followme tree for each user who has a group number
// it's purpose is to convert after an upgrade


// TODO, is this needed...?
// is this global...? what if we include this files
// from a function...?

$sql = "SELECT * FROM findmefollow";
$userresults = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
	
//add details to astdb
if ($astman) {
	foreach($userresults as $usr) {
		extract($usr);

		$astman->database_put("AMPUSER",$grpnum."/followme/prering",isset($pre_ring)?$pre_ring:'');
		$astman->database_put("AMPUSER",$grpnum."/followme/grptime",isset($grptime)?$grptime:'');
		$astman->database_put("AMPUSER",$grpnum."/followme/grplist",isset($grplist)?$grplist:'');
		$confvalue = ($needsconf == 'CHECKED')?'ENABLED':'DISABLED';
		$astman->database_put("AMPUSER",$grpnum."/followme/grpconf",isset($needsconf)?$confvalue:'');
		$ddial = $astman->database_get("AMPUSER",$grpnum."/followme/ddial");                                     
		$ddial = ($ddial == 'EXTENSION' || $ddial == 'DIRECT')?$ddial:'DIRECT';
		$astman->database_put("AMPUSER",$grpnum."/followme/ddial",$ddial);
	}	
} else {
	echo __("Cannot connect to Asterisk Manager with ").$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"];
}

// Version 2.4.13 change (#1961)
// Ignore sqlite3, doesn't support ALTER...CHANGE syntax, table created properly above
if(!preg_match("/qlite/",$amp_conf["AMPDBENGINE"]))  {
	$sql = "ALTER TABLE `findmefollow` CHANGE `grpnum` `grpnum` VARCHAR( 20 ) NOT NULL";
	$results = $db->query($sql);
	if(DB::IsError($results)) {
		echo $results->getMessage();
		return false;
	}
}

// Version 2.5 migrate to recording ids
//
// Do not do upgrades for sqlite3.  Assume full support begins in 2.5 and our CREATE syntax is correct
if(!preg_match("/qlite/",$amp_conf["AMPDBENGINE"]))  {
	outn(__("Checking if recordings need migration.."));
	$sql = "SELECT annmsg_id FROM findmefollow";
	$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($check)) {
		//  Add recording_id field
		//
		out(__("migrating"));	
		outn(__("adding annmsg_id field.."));
  	$sql = "ALTER TABLE findmefollow ADD annmsg_id INTEGER";
  	$result = $db->query($sql);
  	if(DB::IsError($result)) {
			out(__("fatal error"));
			die_issabelpbx($result->getDebugInfo()); 	
		} else {
			out(__("ok"));
		}
		outn(__("adding remotealert_id field.."));
  	$sql = "ALTER TABLE findmefollow ADD remotealert_id INTEGER";
  	$result = $db->query($sql);
  	if(DB::IsError($result)) {
			out(__("fatal error"));
			die_issabelpbx($result->getDebugInfo()); 
		} else {
			out(__("ok"));
		}
		outn(__("adding toolate_id field.."));
 	 	$sql = "ALTER TABLE findmefollow ADD toolate_id INTEGER";
		  $result = $db->query($sql);
 		 if(DB::IsError($result)) {
				out(__("fatal error"));
				die_issabelpbx($result->getDebugInfo()); 
			} else {
				out(__("ok"));
			}

		// Get all the valudes and replace them with recording_id
		//
		outn(__("migrate annmsg to ids.."));
 	 $sql = "SELECT `grpnum`, `annmsg` FROM `findmefollow`";
		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if(DB::IsError($results)) {
			out(__("fatal error"));
			die_issabelpbx($results->getDebugInfo());	
		}
		$migrate_arr = array();
		$count = 0;
		foreach ($results as $row) {
			if (trim($row['annmsg']) != '') {
				$rec_id = recordings_get_or_create_id($row['annmsg'], 'findmefollow');
				$migrate_arr[] = array($rec_id, $row['grpnum']);
				$count++;
			}
		}
		if ($count) {
			$compiled = $db->prepare('UPDATE `findmefollow` SET `annmsg_id` = ? WHERE `grpnum` = ?');
			$result = $db->executeMultiple($compiled,$migrate_arr);
			if(DB::IsError($result)) {
				out(__("fatal error"));
				die_issabelpbx($result->getDebugInfo());	
			}
		}
		out(sprintf(__("migrated %s entries"),$count));

		outn(__("migrate remotealert to ids.."));
  	$sql = "SELECT `grpnum`, `remotealert` FROM `findmefollow`";
		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if(DB::IsError($results)) {
			out(__("fatal error"));
			die_issabelpbx($results->getDebugInfo());	
		}
		$migrate_arr = array();
		$count = 0;
		foreach ($results as $row) {
			if (trim($row['remotealert']) != '') {
				$rec_id = recordings_get_or_create_id($row['remotealert'], 'findmefollow');
				$migrate_arr[] = array($rec_id, $row['grpnum']);
				$count++;
			}
		}
		if ($count) {
			$compiled = $db->prepare('UPDATE `findmefollow` SET `remotealert_id` = ? WHERE `grpnum` = ?');
			$result = $db->executeMultiple($compiled,$migrate_arr);
			if(DB::IsError($result)) {
				out(__("fatal error"));
				die_issabelpbx($result->getDebugInfo());	
			}
		}
		out(sprintf(__("migrated %s entries"),$count));	

		outn(__("migrate toolate to  ids.."));
 	 $sql = "SELECT `grpnum`, `toolate` FROM `findmefollow`";
		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if(DB::IsError($results)) {
			out(__("fatal error"));
			die_issabelpbx($results->getDebugInfo());	
		}
		$migrate_arr = array();
		$count = 0;
		foreach ($results as $row) {
			if (trim($row['toolate']) != '') {
				$rec_id = recordings_get_or_create_id($row['toolate'], 'findmefollow');
				$migrate_arr[] = array($rec_id, $row['grpnum']);
				$count++;
			}
		}
		if ($count) {
			$compiled = $db->prepare('UPDATE `findmefollow` SET `toolate_id` = ? WHERE `grpnum` = ?');
			$result = $db->executeMultiple($compiled,$migrate_arr);
			if(DB::IsError($result)) {
				out(__("fatal error"));
				die_issabelpbx($result->getDebugInfo());	
			}
		}
		out(sprintf(__("migrated %s entries"),$count));

		// Now remove the old recording field replaced by new id field
		//
		outn(__("dropping annmsg field.."));
  	$sql = "ALTER TABLE `findmefollow` DROP `annmsg`";
  	$result = $db->query($sql);
  	if(DB::IsError($result)) { 
			out(__("no annmsg field???"));
		} else {
			out(__("ok"));
		}
		outn(__("dropping remotealert field.."));
	  $sql = "ALTER TABLE `findmefollow` DROP `remotealert`";
 	 $result = $db->query($sql);
  	if(DB::IsError($result)) { 
			out(__("no remotealert field???"));
		} else {
			out(__("ok"));
		}
		outn(__("dropping toolate field.."));
 	 $sql = "ALTER TABLE `findmefollow` DROP `toolate`";
  	$result = $db->query($sql);
  	if(DB::IsError($result)) { 
			out(__("no toolate field???"));
		} else {
			out(__("ok"));
		}

	} else {
		out(__("already migrated"));
	}
}

$issabelpbx_conf =& issabelpbx_conf::create();

// FOLLOWME_AUTO_CREATE
//
$set['value'] = false;
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'findmefollow';
$set['category'] = 'Follow Me Module';
$set['emptyok'] = 0;
$set['sortorder'] = 30;
$set['name'] = 'Create Follow Me at Extension Creation Time';
$set['description'] = 'When creating a new user or extension, setting this to true will automatically create a new Follow Me for that user using the default settings listed below';
$set['type'] = CONF_TYPE_BOOL;
$issabelpbx_conf->define_conf_setting('FOLLOWME_AUTO_CREATE',$set);

// FOLLOWME_DISABLED
//
$set['value'] = true;
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'findmefollow';
$set['category'] = 'Follow Me Module';
$set['emptyok'] = 0;
$set['sortorder'] = 40;
$set['name'] = 'Disable Follow Me Upon Creation';
$set['description'] = 'This is the default value for the Follow Me "Disable" setting. When first creating a Follow Me or if auto-created with a new extension, setting this to true will disable the Follow Me setting which can be changed by the user or admin in multiple locations.';
$set['type'] = CONF_TYPE_BOOL;
$issabelpbx_conf->define_conf_setting('FOLLOWME_DISABLED',$set);

// FOLLOWME_TIME
//
unset($options);
for ($i=5;$i<=120;$i++) {
  $options[] = $i;
}
$set['value'] = '20';
$set['defaultval'] =& $set['value'];
$set['options'] = $options;
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'findmefollow';
$set['category'] = 'Follow Me Module';
$set['emptyok'] = 0;
$set['sortorder'] = 50;
$set['name'] = "Default Follow Me Ring Time";
$set['description'] = "The default Ring Time for a Follow Me set upon creation and used if auto-created with a new extension.";
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('FOLLOWME_TIME',$set);

// FOLLOWME_PRERING
//
unset($options);
for ($i=5;$i<=60;$i++) {
  $options[] = $i;
}
$set['value'] = '7';
$set['defaultval'] =& $set['value'];
$set['options'] = $options;
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'findmefollow';
$set['category'] = 'Follow Me Module';
$set['emptyok'] = 0;
$set['sortorder'] = 60;
$set['name'] = "Default Follow Me Initial Ring Time";
$set['description'] = "The default Initial Ring Time for a Follow Me set upon creation and used if auto-created with a new extension.";
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('FOLLOWME_PRERING',$set);

// FOLLOWME_RG_STRATEGY
//
$set['value'] = 'ringallv2-prim';
$set['defaultval'] =& $set['value'];
$set['options'] = array('ringallv2','ringallv2-prim','ringall','ringall-prim','hunt','hunt-prim','memoryhunt','memoryhunt-prim','firstavailable','firstnotonphone');
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'findmefollow';
$set['category'] = 'Follow Me Module';
$set['emptyok'] = 0;
$set['sortorder'] = 70;
$set['name'] = 'Default Follow Me Ring Strategy';
$set['description'] = "The default Ring Strategy selected for a Follow Me set upon creation and used if auto-created with an extension.";
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('FOLLOWME_RG_STRATEGY',$set);

$issabelpbx_conf->commit_conf_settings();
