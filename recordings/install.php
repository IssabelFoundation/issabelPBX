<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//for translation only
if (false) {
_("Recordings");
_("Save Recording");
_("Check Recording");
}

global $amp_conf;
global $db;

$recordings_astsnd_path = isset($amp_conf['ASTVARLIBDIR'])?$amp_conf['ASTVARLIBDIR']:'/var/lib/asterisk';
$recordings_astsnd_path .= "/sounds/";
$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";


require_once($amp_conf['AMPWEBROOT'] . '/admin/modules/recordings/functions.inc.php');

$fcc = new featurecode('recordings', 'record_save');
$fcc->setDescription('Save Recording');
$fcc->setDefault('*77');
$fcc->update();
unset($fcc);

$fcc = new featurecode('recordings', 'record_check');
$fcc->setDescription('Check Recording');
$fcc->setDefault('*99');
$fcc->update();
unset($fcc);

// Make sure table exists
if ($amp_conf["AMPDBENGINE"] == 'sqlite3') {
	$sql = "CREATE TABLE IF NOT EXISTS recordings ( 
		`id` integer NOT NULL PRIMARY KEY AUTOINCREMENT, 
		displayname VARCHAR(50) , filename BLOB, description 
		VARCHAR(254))
	;";
}  else  {
	$sql = "CREATE TABLE IF NOT EXISTS recordings ( 
		id INTEGER NOT NULL  PRIMARY KEY $autoincrement,
		displayname VARCHAR(50) , 
		filename BLOB, 
		description VARCHAR(254))
	;";
}
$result = $db->query($sql);
if(DB::IsError($result)) {
        die_issabelpbx($result->getDebugInfo());
}

// load up any recordings that might be in the directory
$recordings_directory = $recordings_astsnd_path."custom/";

if (!file_exists($recordings_directory)) { 
	mkdir ($recordings_directory);
}
if (!is_writable($recordings_directory)) {
	print "<h2>Error</h2><br />I can not access the directory $recordings_directory. ";
	print "Please make sure that it exists, and is writable by the web server.";
	return false;
}
$sql = "SELECT * FROM recordings where displayname = '__invalid'";
$results = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if (!isset($results['filename'])) {
  sql("INSERT INTO recordings (displayname, filename, description) VALUES ( '__invalid', 'install done', '');" );
	$dh = opendir($recordings_directory);
	while (false !== ($file = readdir($dh))) { // http://au3.php.net/readdir 
		if ($file[0] != "." && $file != "CVS" && $file != "svn" && !is_dir("$recordings_directory/$file")) {
			// Ignore the suffix..
      $fname = str_replace(array('.wav','.gsm'), array('',''), $file);
			if (recordings_get_id("custom/$fname") == null)
				recordings_add($fname, "custom/$file");
		}
	}
}

global $db;

// Upgrade to recordings 3.0
// Change filename from VARCHAR(80) to BLOB
// Upgrade to recordings 3.0
// Change filename from VARCHAR(80) to BLOB
// no need to add this if we are on sqlite, since the initial tables will
// include the correct columns already.
if  (($amp_conf["AMPDBENGINE"] != "sqlite") && ($amp_conf["AMPDBENGINE"] != "sqlite3")) 
{
	$sql = 'ALTER TABLE recordings CHANGE filename filename BLOB';
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die($result->getDebugInfo());
	}
 }

	// Version 2.5 upgrade
	outn(_("checking for fcode field.."));
	$sql = "SELECT `fcode` FROM recordings";
	$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($check)) {
		// add new field
		$sql = "ALTER TABLE recordings ADD `fcode` TINYINT( 1 ) DEFAULT 0 ;";
		$result = $db->query($sql);
		if(DB::IsError($result)) {
			die_issabelpbx($result->getDebugInfo());
		}
		out(_("OK"));
	} else {
		out(_("already exists"));
	}
	outn(_("checking for fcode_pass field.."));
	$sql = "SELECT `fcode_pass` FROM recordings";
	$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($check)) {
		// add new field
		$sql = "ALTER TABLE recordings ADD `fcode_pass` VARCHAR( 20 ) NULL ;";
		$result = $db->query($sql);
		if(DB::IsError($result)) {
			die_issabelpbx($result->getDebugInfo());
		}
		out(_("OK"));
	} else {
		out(_("already exists"));
	}

$issabelpbx_conf =& issabelpbx_conf::create();

  // AMPPLAYKEY
  //
  $set['value'] = '';
  $set['defaultval'] =& $set['value'];
  $set['readonly'] = 0;
  $set['hidden'] = 0;
  $set['level'] = 3;
  $set['module'] = 'recordings';
  $set['category'] = 'System Setup';
  $set['emptyok'] = 1;
  $set['name'] = 'Recordings Crypt Key';
  $set['description'] = 'Crypt key used by this recordings module when accessing the recording files. Change from the default of "moufdsuu3nma0" if desired.';
  $set['type'] = CONF_TYPE_TEXT;
  $issabelpbx_conf->define_conf_setting('AMPPLAYKEY',$set,true);

