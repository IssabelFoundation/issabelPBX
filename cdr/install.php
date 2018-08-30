<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Portions Copyright (C) 2011 Mikael Carlsson
//	Copyright 2013 Schmooze Com Inc.
//
// Update cdr database with did field
//
global $db;
global $amp_conf;

// Retrieve database and table name if defined, otherwise use IssabelPBX default
$db_name = !empty($amp_conf['CDRDBNAME'])?$amp_conf['CDRDBNAME']:"asteriskcdrdb";
$db_table_name = !empty($amp_conf['CDRDBTABLENAME'])?$amp_conf['CDRDBTABLENAME']:"cdr";

// if CDRDBHOST and CDRDBTYPE are not empty then we assume an external connection and don't use the default connection
//
if (!empty($amp_conf["CDRDBHOST"]) && !empty($amp_conf["CDRDBTYPE"])) {
	$db_hash = array('mysql' => 'mysql', 'postgres' => 'pgsql');
	$db_type = $db_hash[$amp_conf["CDRDBTYPE"]];
	$db_host = $amp_conf["CDRDBHOST"];
	$db_port = empty($amp_conf["CDRDBPORT"]) ? '' :  ':' . $amp_conf["CDRDBPORT"];
	$db_user = empty($amp_conf["CDRDBUSER"]) ? $amp_conf["AMPDBUSER"] : $amp_conf["CDRDBUSER"];
	$db_pass = empty($amp_conf["CDRDBPASS"]) ? $amp_conf["AMPDBPASS"] : $amp_conf["CDRDBPASS"];
	$datasource = $db_type . '://' . $db_user . ':' . $db_pass . '@' . $db_host . $db_port . '/' . $db_name;
	$dbcdr = DB::connect($datasource); // attempt connection
	if(DB::isError($dbcdr)) {
		die_issabelpbx($dbcdr->getDebugInfo()); 
	}
} else {
	$dbcdr = $db;
}

if (! function_exists("out")) {
        function out($text) {
                echo $text."<br />";
        }
}
out(_("Checking if field did is present in cdr table.."));
$sql = "SELECT did FROM $db_name.$db_table_name";
$confs = $dbcdr->getRow($sql, DB_FETCHMODE_ASSOC);
if (DB::IsError($confs)) { // no error... Already there
  out(_("Adding did field to cdr"));
  out(_("This might take a while......"));
  $sql = "ALTER TABLE $db_name.$db_table_name ADD did VARCHAR ( 50 ) NOT NULL DEFAULT ''";
  $results = $dbcdr->query($sql);
  if(DB::IsError($results)) {
    die($results->getMessage());
  }
  out(_("Added field did to cdr"));
} else {
  out(_("did field already present."));
}

out(_("Checking if field recordingfile is present in cdr table.."));
$sql = "SELECT recordingfile FROM $db_name.$db_table_name";
$confs = $dbcdr->getRow($sql, DB_FETCHMODE_ASSOC);
if (DB::IsError($confs)) { // no error... Already there
    out(_("Adding recordingfile field to cdr"));
    $sql = "ALTER TABLE $db_name.$db_table_name ADD recordingfile VARCHAR ( 255 ) NOT NULL DEFAULT ''";
    $results = $dbcdr->query($sql);
    if(DB::IsError($results)) {
        out(_('Unable to add recordingfile field to cdr table'));
        issabelpbx_log(IPBX_LOG_ERROR,"failed to add recordingfile field to cdr table");
    } else {
        out(_("Added field recordingfile to cdr"));
    }
} else {
      out(_("recordingfile field already present."));
}

$cid_fields = array('cnum', 'cnam', 'outbound_cnum', 'outbound_cnam', 'dst_cnam');
foreach($cid_fields as $cf) {
	out(_("Checking if field $cf is present in cdr table.."));
	$sql = "SELECT $cf FROM $db_name.$db_table_name";
	$confs = $dbcdr->getRow($sql, DB_FETCHMODE_ASSOC);
	if (DB::IsError($confs)) { // no error... Already there
    	out(_("Adding $cf field to cdr"));
    	$sql = "ALTER TABLE $db_name.$db_table_name ADD $cf VARCHAR ( 40 ) NOT NULL DEFAULT ''";
    	$results = $dbcdr->query($sql);
    	if(DB::IsError($results)) {
        	out(_("Unable to add $cf field to cdr table"));
        	issabelpbx_log(IPBX_LOG_ERROR,"failed to add $cf field to cdr table");
    	} else {
        	out(_("Added field $cf to cdr"));
					// TODO: put onetime notification about old src field searches and query that could be
					// done if user wants to get that into cnum field.
    	}
	} else {
      	out(_("$cf field already present."));
	}
}


$db_cel_name = !empty($amp_conf['CELDBNAME'])?$amp_conf['CELDBNAME']:$db_name;
$db_cel_table_name = !empty($amp_conf['CELDBTABLENAME'])?$amp_conf['CELDBTABLENAME']:"cel";
outn(_("Creating $db_cel_table_name if needed.."));
$sql = "
CREATE TABLE IF NOT EXISTS `" . $db_cel_name . "`.`" . $db_cel_table_name . "` (
  `id` int(11) NOT NULL auto_increment,
  `eventtype` varchar(30) NOT NULL,
  `eventtime` datetime NOT NULL,
  `cid_name` varchar(80) NOT NULL,
  `cid_num` varchar(80) NOT NULL,
  `cid_ani` varchar(80) NOT NULL,
  `cid_rdnis` varchar(80) NOT NULL,
  `cid_dnid` varchar(80) NOT NULL,
  `exten` varchar(80) NOT NULL,
  `context` varchar(80) NOT NULL,
  `channame` varchar(80) NOT NULL,
  `src` varchar(80) NOT NULL,
  `dst` varchar(80) NOT NULL,
  `channel` varchar(80) NOT NULL,
  `dstchannel` varchar(80) NOT NULL,
  `appname` varchar(80) NOT NULL,
  `appdata` varchar(80) NOT NULL,
  `amaflags` int(11) NOT NULL,
  `accountcode` varchar(20) NOT NULL,
  `uniqueid` varchar(32) NOT NULL,
  `linkedid` varchar(32) NOT NULL,
  `peer` varchar(80) NOT NULL,
  `userdeftype` varchar(255) NOT NULL,
  `eventextra` varchar(255) NOT NULL,
  `userfield` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `uniqueid_index` (`uniqueid`),
  KEY `linkedid_index` (`linkedid`)
)
";
$check = $dbcdr->query($sql);
if(DB::IsError($check)) {
	die_issabelpbx("Can not create $db_cel_table_name table");
} else {
	out(_("OK"));
}

$issabelpbx_conf =& issabelpbx_conf::create();
if (!$issabelpbx_conf->conf_setting_exists('CEL_ENABLED')) {
	// CEL_ENABLED
	//
	//$value = $dbcdr->getOne("SELECT count(*) FROM $db_cel_name.$db_cel_table_name") > 0 ? true : false;
	$value = true;
	$set['value'] = $value;
	$set['defaultval'] = false;
	$set['readonly'] = 0;
	$set['hidden'] = 0;
	$set['level'] = 3;
	$set['module'] = 'cdr';
	$set['category'] = 'CDR Report Module';
	$set['emptyok'] = 0;
	$set['sortorder'] = 10;
	$set['name'] = 'Enable CEL Reporting';
	$set['description'] = 'Setting this true will enable the CDR module to drill down on CEL data for each CDR. Although the CDR module will assure there is a CEL table available, the reporting functionality in Asterisk and associated ODBC database and CEL configuration must be done outside of IssabelPBX either by the user or at the Distro level.';
	$set['type'] = CONF_TYPE_BOOL;
	$issabelpbx_conf->define_conf_setting('CEL_ENABLED',$set,true);
}
