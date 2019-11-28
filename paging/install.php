<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//for translation only
if (false) {
_("Intercom prefix");
_("User Intercom Allow");
_("User Intercom Disallow");
}

global $amp_conf;
// Enable intercom as a feature code
$fcc = new featurecode('paging', 'intercom-prefix');
$fcc->setDescription('Intercom prefix');
$fcc->setDefault('*80');
$fcc->update();
unset($fcc);

// User intercom enable code
$fcc = new featurecode('paging', 'intercom-on');
$fcc->setDescription('User Intercom Allow');
$fcc->setDefault('*54');
$fcc->update();
unset($fcc);

// User intercom disable 
$fcc = new featurecode('paging', 'intercom-off');
$fcc->setDescription('User Intercom Disallow');
$fcc->setDefault('*55');
$fcc->update();
unset($fcc);	

// Remove old tables that were never used
//
$sql = "DROP TABLE IF EXISTS paging_phones";
$result = $db->query($sql);
if(DB::IsError($result)) {
	die_issabelpbx($result->getDebugInfo());
}
$sql = "DROP TABLE IF EXISTS paging_overview";
$result = $db->query($sql);
if(DB::IsError($result)) {
	die_issabelpbx($result->getDebugInfo());
}

$sql = "CREATE TABLE IF NOT EXISTS paging_groups 
	( page_number VARCHAR(50), 
	  ext VARCHAR(25),
		PRIMARY KEY (page_number, ext)
	)";
$result = $db->query($sql);
if(DB::IsError($result)) {
	die_issabelpbx($result->getDebugInfo());
}

// Create table used to change defaults and customize
// for certain phone types
//
$sql = "
CREATE TABLE IF NOT EXISTS `paging_autoanswer` (
	`useragent` VARCHAR( 255 ) NOT NULL ,
	`var` VARCHAR( 20 ) NOT NULL ,
	`setting` VARCHAR( 255 ) NOT NULL ,
	PRIMARY KEY ( `useragent` , `var` )
) CHARACTER SET utf8 COLLATE utf8_unicode_ci";
$result = $db->query($sql);
if(DB::IsError($result)) {
	die_issabelpbx($result->getDebugInfo());
}

// version 1.6 upgrade
$sql = "SELECT page_group FROM paging_config";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	// this table wasn't used up to this point, replace it with the new one
	$sql = "DROP TABLE IF EXISTS paging_config;";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo());
	}
	
	$sql = "CREATE TABLE IF NOT EXISTS paging_config 
		( page_group VARCHAR(255), 
	  	force_page INTEGER(1) NOT NULL,
			duplex     INTEGER(1) NOT NULL default '0',
			description VARCHAR(255) NOT NULL default '',
			PRIMARY KEY (page_group)
		) CHARACTER SET utf8 COLLATE utf8_unicode_ci";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo());
	}

	// insert default values
	$sql = "INSERT INTO paging_config (page_group, force_page, duplex, description)  SELECT DISTINCT page_number, 0, 0, '' FROM paging_groups";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo());
	}
}

// Set the initial default values, if already 

// These are the three most common ways of auto answering.
// If the table is already populated then error will be ignored and user data will not get altered
//
$sql = "INSERT INTO paging_autoanswer (useragent, var, setting) VALUES ('default', 'CALLINFO', 'Call-Info: <uri>\\\\;answer-after=0')";
$result = $db->query($sql);
$sql = "INSERT INTO paging_autoanswer (useragent, var, setting) VALUES ('default', 'ALERTINFO', 'Alert-Info: Ring Answer')";
$result = $db->query($sql);
$sql = "INSERT INTO paging_autoanswer (useragent, var, setting) VALUES ('default', 'SIPURI', 'intercom=true')";
$result = $db->query($sql);
$sql = "INSERT INTO paging_autoanswer (useragent, var, setting) VALUES ('Mitel', 'CALLINFO', 'Call-Info: <sip:broadworks.net>\\\\;answer-after=0')";
$result = $db->query($sql);
$sql = "INSERT INTO paging_autoanswer (useragent, var, setting) VALUES ('Panasonic', 'ALERTINFO', 'Alert-Info: Intercom')";
$result = $db->query($sql);
//Migrate incorrect Polycom alert-info that was set by deleting then re-adding
$sql = "DELETE FROM paging_autoanswer WHERE useragent = 'Polycom' AND var = 'ALERTINFO' AND setting = 'Alert-Info: info-Auto Answer'";
$result = $db->query($sql);
$sql = "INSERT INTO paging_autoanswer (useragent, var, setting) VALUES ('Polycom', 'ALERTINFO', 'Alert-Info: info=Auto Answer')";
$result = $db->query($sql);
$sql = "INSERT INTO paging_autoanswer (useragent, var, setting) VALUES ('Digium', 'ALERTINFO', 'Alert-Info: ring-answer')";
$result = $db->query($sql);

// Add dulex field
//
$sql = "SELECT duplex FROM paging_config";
$result = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if (DB::IsError($result)) {
	$sql = "ALTER TABLE paging_config ADD duplex INTEGER(1) NOT NULL default '0'";
	$results = $db->query($sql);
	if(DB::IsError($results)) {
	        die_issabelpbx($results->getMessage());
	}
}

// Add description field
//
$sql = "SELECT description FROM paging_config";
$result = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if (DB::IsError($result)) {
	$sql = "ALTER TABLE paging_config ADD description VARCHAR(255) NOT NULL default ''";
	$results = $db->query($sql);
	if(DB::IsError($results)) {
	        die_issabelpbx($results->getMessage());
	}
}
// Make sure primary keys are set, they were not originally. Don't check for error,
// if they exist it will give an error
// sqlite3 does not support adding keys after the fact with ALTER.
// These keys are setup in the CREATE TABLE as of 2.5 anyway, so
// just ignore these queries for sqlite3
if($amp_conf["AMPDBENGINE"] != "sqlite3")  {
	$sql = "ALTER TABLE `paging_groups` ADD PRIMARY KEY ( `page_number` , `ext` )";
	$result = $db->query($sql);

	$sql = "ALTER TABLE `paging_config` ADD PRIMARY KEY ( `page_group` )";
	$result = $db->query($sql);
}
?>
