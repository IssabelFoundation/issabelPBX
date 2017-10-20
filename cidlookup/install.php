<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;
global $amp_conf;

// create the tables
$sql = "CREATE TABLE IF NOT EXISTS cidlookup (
	cidlookup_id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
	description varchar(50) NOT NULL,
	sourcetype varchar(100) NOT NULL,
	cache tinyint(1) NOT NULL default '0',
	deptname varchar(30) default NULL,
	http_host varchar(30) default NULL,
	http_port varchar(30) default NULL,
	http_username varchar(30) default NULL,
	http_password varchar(30) default NULL,
	http_path varchar(100) default NULL,
	http_query varchar(100) default NULL,
	mysql_host varchar(60) default NULL,
	mysql_dbname varchar(60) default NULL,
	mysql_query text,
	mysql_username varchar(30) default NULL,
	mysql_password varchar(30) default NULL,
	mysql_charset varchar(30) default NULL,
	opencnam_account_sid varchar(34) default NULL,
	opencnam_auth_token varchar(34) default NULL
);";
$check = $db->query($sql);
if (DB::IsError($check)) {
	die_issabelpbx( "Can not create `cidlookup` table: " . $check->getMessage() .  "\n");
	return false;
} else {

	// Install a default OpenCNAM Caller ID lookup source, if we're installing this
	// module for the very first time.
	outn(_("Installing OpenCNAM CallerID Lookup Sources..."));
	$sql = "INSERT INTO cidlookup (description, sourcetype) VALUES ('OpenCNAM', 'opencnam')";
	$results = $db->query($sql);
	if (DB::IsError($results)) {
		out(_("Failed to add OpenCNAM CallerID Lookup Source: ").$results->getMessage());
		return false;
	} else {
		out(_("Done!"));
	}

}



$sql = "CREATE TABLE IF NOT EXISTS cidlookup_incoming (
	cidlookup_id INT NOT NULL,
	extension VARCHAR(50),
	cidnum VARCHAR(30)
);";
$check = $db->query($sql);
if (DB::IsError($check)) {
        die_issabelpbx( "Can not create `cidlookup_incomming` table: " . $check->getMessage() .  "\n");
}

// first update
$sql = "SELECT cache FROM cidlookup";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if (DB::IsError($check)) {
	// add new field
	$sql = "ALTER TABLE cidlookup ADD cache INTEGER NOT NULL DEFAULT 0;";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getMessage());
	}
}

outn(_("Migrating channel routing to Zap DID routing.."));
$sql = "SELECT channel FROM cidlookup_incoming";
$check = @$db->query($sql);
if (!DB::IsError($check)) {
	$chan_prefix = 'zapchan';
	$sql = "UPDATE cidlookup_incoming SET extension=CONCAT('$chan_prefix',channel), channel='' WHERE channel != ''";
	$results = $db->query($sql);
	if (DB::IsError($results)) {
 		out(_("FATAL: failed to transform old routes: ").$results->getMessage());
	} else {
		out(_("OK"));
		// ALTER...DROP is not supported by sqlite3.  This table was setup properly in the CREATE anyway
		if($amp_conf["AMPDBENGINE"] != "sqlite3")  {
			outn(_("Removing deprecated channel field from incoming.."));
			$sql = "ALTER TABLE cidlookup_incoming DROP channel";
			$results = $db->query($sql);
			if (DB::IsError($results)) {
 			out(_("ERROR: failed: ").$results->getMessage());
			} else {
				out(_("OK"));
			}
		}
	}
} else {
	out(_("Not Needed"));
}

// This field had been wrongfully added to incoming quite some time ago
// this should maybe be added to core as well.
// NOTE: ALTER / DROP isn't supported in SQLite3 prior to 3.1.3.
outn(_("Checking for cidlookup field in core's incoming table.."));
$sql = "ALTER TABLE incoming DROP cidlookup";
$results = $db->query($sql);
if (DB::IsError($results)) {
	out(_("not present"));
} else {
	out(_("removed"));
}

// Add the new opencnam_account_sid and opencnam_auth_token columns
// if they do not already exist. This makes backwards compatibiility work
// as OpenCNAM support was not included in the cidlookup module prior to
// 2.10.0.2.
$sql='describe cidlookup';
$fields=$db->getAssoc($sql);
if (!array_key_exists('opencnam_account_sid',$fields) && !array_key_exists('opencnam_auth_token',$fields)) {

	// NOTE: ALTER / DROP isn't supported in SQLite3 prior to 3.1.3.
	outn(_("Adding opencnam account columns to the cidlookup table..."));
	$sql = "ALTER TABLE cidlookup ADD opencnam_account_sid VARCHAR(34) DEFAULT NULL";
	$results = $db->query($sql);
	if (DB::IsError($results)) {
		out(_("Could not add opencnam_account_sid column to cidlookup table."));
	}

	$sql = "ALTER TABLE cidlookup ADD opencnam_auth_token VARCHAR(34) DEFAULT NULL";
	$results = $db->query($sql);
	if (DB::IsError($results)) {
		out(_("Could not add opencnam_auth_token column to cidlookup table."));
	}
	out(_("Done!"));

	outn(_("Installing OpenCNAM CallerID Lookup Sources..."));
	$sql = "INSERT INTO cidlookup (description, sourcetype) VALUES ('OpenCNAM', 'opencnam')";
	$results = @$db->query($sql);
	if (DB::IsError($results)) {
		out(_("Failed to add OpenCNAM CallerID Lookup Source: ").$results->getMessage());
	} else {
		out(_("Done!"));
	}
} else {
	outn(_("Cleaning up duplicate OpenCNAM CallerID Lookup Sources..."));
	$sql = "SELECT * FROM cidlookup WHERE description = 'OpenCNAM' AND sourcetype = 'opencnam'";
	$sources = sql($sql,'getAll',DB_FETCHMODE_ASSOC);

	$total = count($sources);
	for($i = 0;$i < $total;$i++) {
		//If we are in a pro-tier then skip the fix
		if(!empty($sources[$i]['opencnam_account_sid'])) {
			continue;
		}

		//don't delete the last remaining line!
		if($i != ($total - 1)) {
			$sql = "DELETE FROM cidlookup WHERE cidlookup_id=".$sources[$i]['cidlookup_id'];
			sql($sql);
		}
	}
	out(_("Done!"));
}

if (!$db->getAll('SHOW COLUMNS FROM cidlookup WHERE FIELD = "mysql_charset"')) {
	out("Adding MySQL charset field");
	sql('ALTER TABLE cidlookup ADD mysql_charset varchar(30) default NULL AFTER mysql_password');
}
