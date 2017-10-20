<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed');}

if ( (isset($amp_conf['ASTVARLIBDIR'])?$amp_conf['ASTVARLIBDIR']:'') == '') {
	$astlib_path = "/var/lib/asterisk";
} else {
	$astlib_path = $amp_conf['ASTVARLIBDIR'];
}

if ( !file_exists($astlib_path."/agi-bin/propolys-tts.agi") ) {
	if ( !copy($amp_conf['AMPWEBROOT']."/admin/modules/tts/agi/propolys-tts.agi", $astlib_path."/agi-bin/propolys-tts.agi") ) {
		echo _("TTS AGI install failed. Module not usable.");
		return false;
	} else {
		chmod($astlib_path."/agi-bin/propolys-tts.agi", 0764);
	}
}

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";

$sql = "CREATE TABLE IF NOT EXISTS tts (
	id INTEGER NOT NULL $autoincrement,
	name VARCHAR( 100 ) NOT NULL,
	text VARCHAR( 250 ) NOT NULL,
	goto VARCHAR( 50 ),
	engine VARCHAR( 50 ),
	PRIMARY KEY (id)
	)";

$result = $db->query($sql);
if(DB::IsError($result)) {
	die_issabelpbx($result->getDebugInfo());
}
