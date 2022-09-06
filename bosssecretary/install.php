<?php


$fcc = new featurecode('bosssecretary', 'bsc_toggle');
$fcc->setDescription('Bosssecretary Toggle');
$fcc->setDefault('*152');
$fcc->update();
unset($fcc);

$fcc = new featurecode('bosssecretary', 'bsc_on');
$fcc->setDescription('Bosssecretary On');
$fcc->setDefault('*153');
$fcc->update();
unset($fcc);


$fcc = new featurecode('bosssecretary', 'bsc_off');
$fcc->setDescription('Bosssecretary Off');
$fcc->setDefault('*154');
$fcc->update();
unset($fcc);



$sql = " DROP TABLE IF EXISTS  `bosssecretary_config`";
$check = $db->query($sql);
if(DB::IsError($check)) {
	die_issabelpbx("Can not drop bosssecretary_config table");
}

 


$sql = "
CREATE TABLE IF NOT EXISTS `bosssecretary_chief` (
  `id_group` INTEGER NOT NULL,
  `chief_extension` varchar(20) NOT NULL,
  PRIMARY KEY (`id_group`,`chief_extension`)
); 
";
$check = $db->query($sql);
if(DB::IsError($check)) {
	die_issabelpbx("Can not create bosssecretary_chief table");
}

$sql = "
CREATE TABLE IF NOT EXISTS `bosssecretary_boss` (
  `id_group` INTEGER NOT NULL,
  `boss_extension` varchar(20) NOT NULL,
  PRIMARY KEY (`id_group`,`boss_extension`)
);
";
$check = $db->query($sql);
if(DB::IsError($check)) {
        die_issabelpbx("Can not create bosssecretary_boss table");
}


$autoincrement=(preg_match("/qlite/",$amp_conf["AMPDBENGINE"])) ? "AUTOINCREMENT":"AUTO_INCREMENT";

$sql = "CREATE TABLE IF NOT EXISTS `bosssecretary_group` (  `id_group` INTEGER PRIMARY KEY $autoincrement ,  `label` varchar(20) NOT NULL);";
$check = $db->query($sql);
if(DB::IsError($check)) {
	die_issabelpbx("Can not create bosssecretary_group table");
}


if(!preg_match("/qlite/",$amp_conf["AMPDBENGINE"]))  {

$sql = "SHOW COLUMNS FROM `bosssecretary_group`";
$results = $db->getAll($sql, array(), DB_FETCHMODE_ASSOC);
if(DB::IsError($results)) {
	die_issabelpbx("Can not check bosssecretary_group table");
}


foreach ($results as $column)
{

	if (trim(strtolower($column["Field"])) == "dring")
	{
		$sql = "ALTER TABLE `bosssecretary_group` DROP `".$column["Field"]."` ";
		$check = $db->query($sql);
		if(DB::IsError($check)) {
			die_issabelpbx("Can not alter bosssecretary_group table");
		}
	}
	if (trim(strtolower($column["Field"])) == "ringtime")
	{
		$sql = "ALTER TABLE `bosssecretary_group` DROP `".$column["Field"]."` ";
		$check = $db->query($sql);
		if(DB::IsError($check)) {
			die_issabelpbx("Can not alter bosssecretary_group table");
		}
	}
}

$sql = "ALTER TABLE bosssecretary_group MODIFY id_group INT(10) NOT NULL";
$results = $db->query($sql);
if(DB::IsError($results)) {
	die_issabelpbx("Can not modify bosssecretary_group.id_group column");
}

}

$sql = "
CREATE TABLE IF NOT EXISTS `bosssecretary_secretary` (
  `id_group` INTEGER NOT NULL,
  `secretary_extension` varchar(20) NOT NULL,
  PRIMARY KEY (`id_group`,`secretary_extension`)
); 
";
$check = $db->query($sql);
if(DB::IsError($check)) {
	die_issabelpbx("Can not create bosssecretary_secretary table");
}


$sql = "
CREATE TABLE IF NOT EXISTS `bosssecretary_group_numbers_free` (
  `group_number` INTEGER NOT NULL,
  PRIMARY KEY (`group_number`)
);
";

$check = $db->query($sql);
if(DB::IsError($check)) {
	die_issabelpbx("Can not create bosssecretary_group_numbers_free");
}



?>
