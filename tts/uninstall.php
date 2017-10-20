<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed');}

global $db;

if ( (isset($amp_conf['ASTVARLIBDIR'])?$amp_conf['ASTVARLIBDIR']:'') == '') {
	$astlib_path = "/var/lib/asterisk";
} else {
	$astlib_path = $amp_conf['ASTVARLIBDIR'];
}

if ( file_exists($astlib_path."/agi-bin/propolys-tts.agi") ) {
	if ( !unlink($astlib_path."/agi-bin/propolys-tts.agi") ) {
		echo _("TTS AGI script cannot be removed.");
	}
}

echo "dropping table tts..";
sql("DROP TABLE IF EXISTS `tts`");
echo "done<br>\n";

