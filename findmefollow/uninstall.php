<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

// Delete all the followme trees. This function selects from the users table
// and not the findmefollow table because the uninstall code deletes the tables
// prior to running the uninstall script. (probably should be the opposite but...)
// It is probably better this way anyhow, as there is no harm done if the user
// has not followme settings and who knows ... maybe some stray ones got left
// behind somehow.


// TODO, is this needed...?
// is this global...? what if we include this files
// from a function...?
global $astman;
global $amp_conf;

// Don't bother uninstalling feature codes, now module_uninstall does it

$sql = "SELECT * FROM users";
$userresults = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
	
//add details to astdb
if ($astman) {
	foreach($userresults as $usr) {
		extract($usr);
		$astman->database_deltree("AMPUSER/".$grpnum."/followme");
	}	
} else {
	echo _("Cannot connect to Asterisk Manager with ").$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"];
}

sql('DROP TABLE IF EXISTS findmefollow');

?>
