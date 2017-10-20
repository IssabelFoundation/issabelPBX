<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;
global $amp_conf;

$sql = "
CREATE TABLE IF NOT EXISTS `outroutemsg` 
(
	`keyword` varchar(40) NOT NULL default '',
	`data` varchar(10) NOT NULL,
	PRIMARY KEY  (`keyword`)
)
";
$check = $db->query($sql);
if(DB::IsError($check)) {
	die_issabelpbx(_("Can not create outroutemsg table"));
}

?>
