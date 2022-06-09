<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

if($amp_conf["AMPDBENGINE"]=="mysql" || $amp_conf["AMPDBENGINE"]=="mysqli") {

    $sql[]="CREATE TABLE IF NOT EXISTS `dynamicfeatures` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(200) NOT NULL DEFAULT '',
        `dtmf` varchar(10) NOT NULL DEFAULT '',
        `activate_on` enum('self','peer') DEFAULT 'peer',
        `application` varchar(100) DEFAULT '',
        `arguments` varchar(200) DEFAULT '',
        `moh_class` varchar(200) DEFAULT '',
        PRIMARY KEY (`id`)
            ) ";

} else {

    $sql[]="CREATE TABLE IF NOT EXISTS `dynamicfeatures` (
        `id` INTEGER PRIMARY KEY AUTOINCREMENT,
        `name` varchar(200) NOT NULL DEFAULT '',
        `dtmf` varchar(10) NOT NULL DEFAULT '',
        `activate_on` TEXT CHECK( activate_on IN ('self','peer')) DEFAULT 'peer',
        `application` varchar(100) DEFAULT '',
        `arguments` varchar(200) DEFAULT '',
        `moh_class` varchar(200) DEFAULT ''
            ) ";


}

foreach($sql as $s){
    $check = $db->query($s);
    if(DB::IsError($check)) {
        die_issabelpbx("Can not create dynamicfeatures table\n");
    }
}

?>
