<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";
$sql[]="CREATE TABLE IF NOT EXISTS `dynamicfeatures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `dtmf` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `activate_on` enum('self','peer') COLLATE utf8mb4_unicode_ci DEFAULT 'peer',
  `application` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `arguments` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `moh_class` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  PRIMARY KEY (`id`)
) ";

foreach($sql as $s){
    $check = $db->query($s);
    if(DB::IsError($check)) {
        die_issabelpbx("Can not create dynamicfeatures table\n");
    }
}

?>
