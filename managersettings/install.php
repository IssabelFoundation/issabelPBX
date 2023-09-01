<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
/* $Id:$ */

global $db;
global $amp_conf;

$sql = <<< END
CREATE TABLE IF NOT EXISTS `managersettings` (
  `keyword` VARCHAR (50) NOT NULL default '',
  `data`    VARCHAR (255) NOT NULL default '',
  `seq`     TINYINT (1),
  `type`    TINYINT (1) NOT NULL default '0',
  PRIMARY KEY (`keyword`,`seq`,`type`)
)
END;

outn(__("checking for managersettings table.."));
$tsql = "SELECT * FROM `managersettings` limit 1";
$check = $db->getRow($tsql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	out(__("none, creating table"));
	// table does not exist, create it
	sql($sql);

	outn(__("populating default values.."));
  $sip_settings =  array(
    array('webenabled'    ,'no' , '1'),
    array('channelvars'    ,'' , '2'),
    array('displayconnects'    ,'no' , '3'),
    array('timestampevents'    ,'no' , '4'),
    );

	// Now insert minimal codec rows
	$compiled = $db->prepare("INSERT INTO managersettings (keyword, data, seq, type) values (?,?,?,'1')");
	$result = $db->executeMultiple($compiled,$sip_settings);
	if(DB::IsError($result)) {
		out(__("fatal error occurred populating defaults, check module"));
	} else {
		out(__("webenabled, displayconnects, timestampevents added"));
	}
} else {
	out(__("already exists"));
}
