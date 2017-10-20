<?php /* $Id: install.php $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
  
global $db;
global $amp_conf;

if (! function_exists("out")) {
	function out($text) {
		echo $text."<br />";
	}
}

if (! function_exists("outn")) {
	function outn($text) {
		echo $text;
	}
}

// TODO: returning false will fail the install with #4345 checked in
//
if (!function_exists('timeconditions_timegroups_add_group_timestrings')) {
  out(_('Time Conditions Module required and not present .. aborting install'));
  return false;
}

$sql[] ="CREATE TABLE IF NOT EXISTS `customcontexts_contexts` (
				`context` varchar(100) NOT NULL default '',
				`description` varchar(100) NOT NULL default '',
				PRIMARY KEY  (`context`),
				UNIQUE KEY `description` (`description`)
			)";


$sql[] ="CREATE TABLE IF NOT EXISTS `customcontexts_contexts_list` (
				`context` varchar(100) NOT NULL default '',
				`description` varchar(100) NOT NULL default '',
				`locked` tinyint(1) NOT NULL default '0',
				PRIMARY KEY  (`context`),
				UNIQUE KEY `description` (`description`)
				)";

$sql[] ="INSERT IGNORE INTO `customcontexts_contexts_list` 
				(`context`, `description`, `locked`) 
				VALUES ('from-internal', 'Default Internal Context', 1),
				('from-internal-additional', 'Internal Dialplan', 0),
				('outbound-allroutes', 'Outbound Routes', 0)";

$sql[] ="CREATE TABLE IF NOT EXISTS `customcontexts_includes` (
				`context` varchar(100) NOT NULL default '',
				`include` varchar(100) NOT NULL default '',
				`timegroupid` int(11) default NULL,
				`sort` int(11) NOT NULL default '0',
				PRIMARY KEY  (`context`,`include`),
				KEY `sort` (`sort`)
)";

$sql[] ="ALTER IGNORE TABLE `customcontexts_includes` ADD `timegroupid` INT NULL AFTER `include`";

$sql[] ="CREATE TABLE IF NOT EXISTS `customcontexts_includes_list` (
				`context` varchar(100) NOT NULL default '',
				`include` varchar(100) NOT NULL default '',
				`description` varchar(100) NOT NULL default '',
				PRIMARY KEY  (`context`,`include`)
				)";

$sql[] ="ALTER IGNORE TABLE `customcontexts_includes_list` ADD `missing` BOOL NOT NULL DEFAULT '0'";


$sql[] ="INSERT IGNORE INTO `customcontexts_includes_list` (`context`, `include`, `description`) VALUES ('from-internal', 'parkedcalls', 'Call Parking'),
				('from-internal', 'from-internal-custom', 'Custom Internal Dialplan')";

$sql[] ="INSERT IGNORE INTO `customcontexts_includes_list` 
					(`context`, `include`, `description`) 
					VALUES ('from-internal-additional', 'outbound-allroutes', 'ALL OUTBOUND ROUTES'),
					('from-internal', 'from-internal-additional', 'ENTIRE Basic Internal Dialplan')";

$sql[] ="UPDATE `customcontexts_includes_list` SET `description` = 'ALL OUTBOUND ROUTES' WHERE  `context` = 'from-internal-additional' AND `include` = 'outbound-allroutes'";

$sql[] ="CREATE TABLE IF NOT EXISTS `customcontexts_module` (
				`id` varchar(50) NOT NULL default '',
				`value` varchar(100) NOT NULL default '',
				PRIMARY KEY  (`id`)
				)";

$sql[] ="INSERT IGNORE INTO `customcontexts_module` (`id`, `value`) VALUES ('modulerawname', 'customcontexts'),
				('moduledisplayname', 'Class of Service'),
				('moduleversion', '0.3.2'),
				('displaysortforincludes', 1)";

$sql[] ="UPDATE `customcontexts_module` set `value` = '0.3.2' where `id` = 'moduleversion';";

foreach ($sql as $q){
	$db->query($q);
		if(DB::IsError($q)) { 
			out("FATAL: ".$q->getDebugInfo()."\n");	
		}
}

customcontexts_updatedb();

//bring db up to date on install/upgrade
function customcontexts_updatedb() {
	global $db;
	$sql = "ALTER IGNORE TABLE `customcontexts_includes` ADD `timegroupid` INT NULL AFTER `include` ;";
	$db->query($sql);
	$sql = "ALTER IGNORE TABLE `customcontexts_includes_list` ADD `missing` BOOL NOT NULL DEFAULT '0';";
	$db->query($sql);
	$sql = "ALTER IGNORE TABLE `customcontexts_contexts` ADD `dialrules` VARCHAR( 1000 ) NULL;";
	$db->query($sql);
	$sql = "ALTER IGNORE TABLE `customcontexts_includes` ADD `userules` VARCHAR( 10 ) NULL ;";
	$db->query($sql);
	$sql = "ALTER IGNORE TABLE `customcontexts_contexts` ADD `faildestination` VARCHAR( 250 ) NULL , ADD `featurefaildestination` VARCHAR( 250 ) NULL ;";
	$db->query($sql);
//0.3.0
	$sql = "ALTER IGNORE TABLE `customcontexts_contexts` ADD `failpin` VARCHAR( 100 ) NULL , ADD `failpincdr` BOOL NOT NULL DEFAULT '0', ADD `featurefailpin` VARCHAR( 100 ) NULL , ADD `featurefailpincdr` BOOL NOT NULL DEFAULT '0';";
	$db->query($sql);
//0.3.2
	$sql = "ALTER IGNORE TABLE `customcontexts_includes_list` ADD `sort` INT NOT NULL DEFAULT '0';";
	$db->query($sql);
}

$tgs = $db->getAll('SELECT * FROM customcontexts_timegroups',DB_FETCHMODE_ASSOC);
if(!DB::IsError($tgs)) {
  outn(_("migrating customcontexts_timegroups if needed.."));			    
  foreach ($tgs as $tg) {
    $tg_strings = sql('SELECT time FROM customcontexts_timegroups_detail WHERE timegroupid = '.$tg['id'].' ORDER BY id','getCol','time');
    $tg_id = timeconditions_timegroups_add_group_timestrings($tg['description'],$tg_strings);
    sql("UPDATE customcontexts_includes set timegroupid = $tg_id WHERE timegroupid = {$tg['id']}");
  }
  out(_("done"));			    
  outn(_("removing customcontexts_timegroups and customcontexts_tiemgroups_detail tables.."));			    
  unset($sql);
  $sql[] = "DROP TABLE IF EXISTS `customcontexts_timegroups`";
  $sql[] = "DROP TABLE IF EXISTS `customcontexts_timegroups_detail`";
  foreach ($sql as $q){
	  $db->query($q);
  }
  out(_("done"));			    
}
?>
