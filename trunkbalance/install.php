<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

//This file is part of FreePBX.
//
//    This is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 2 of the License, or
//    (at your option) any later version.
//
//    This module is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    see <http://www.gnu.org/licenses/>.
//
out(_("Installing Trunk Balance"));

global $db;
global $amp_conf;

// set auto increment depending on engine
$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";

// list of column definitions for this module. Add/remove from list as necessary and table(s) will be built
// automatically by the code that follows
$tablename = "trunkbalance";
$cols['desttrunk_id'] = "INTEGER default '0'";
$cols['disabled'] = "varchar(50) default NULL";
$cols['description'] = "varchar(50) default NULL";
$cols['dialpattern'] = "varchar(255) default NULL";
$cols['dp_andor'] = "varchar(50) default NULL";
$cols['notdialpattern'] = "varchar(255) default NULL";
$cols['notdp_andor'] = "varchar(50) default NULL";
$cols['billing_cycle'] = "varchar(50) default NULL";
$cols['billingtime'] = "time default NULL";
$cols['billing_day'] = "varchar(50) default NULL";
$cols['billingdate'] = "SMALLINT default '0'";
$cols['billingperiod'] = "INT default '0'";
$cols['endingdate'] = "datetime default NULL";
$cols['count_inbound'] = "varchar(50) default NULL";
$cols['count_unanswered'] = "varchar(50) default NULL";
$cols['loadratio'] = "INTEGER default '1'";
$cols['maxtime'] = "INTEGER default '-1'";
$cols['maxnumber'] = "INTEGER default '-1'";
$cols['maxidentical'] = "INTEGER default '-1'";
$cols['timegroup_id'] = "INTEGER default '-1'";
$cols['url'] = "varchar(250) default NULL";
$cols['url_timeout'] = "INTEGER default '10'";
$cols['regex'] = "varchar(250) default NULL";

// create the table and populate with the key auto increment column
$sql = "CREATE TABLE IF NOT EXISTS `$tablename` (
	trunkbalance_id INTEGER NOT NULL PRIMARY KEY $autoincrement)";
$check = $db->query($sql);
if (DB::IsError($check)) {
        die_issabelpbx( "Can not create `$tablename` table: " . $check->getMessage() .  "\n");
}

//check to see that columns are defined properly
$curret_cols = array();
$sql = "DESC `$tablename`";
$res = $db->query($sql);
while($row = $res->fetchRow()) {
	if(array_key_exists($row[0],$cols))  {
		$curret_cols[] = $row[0];
		//make sure column has the latest definition
		$sql = "ALTER TABLE `$tablename` MODIFY ".$row[0]." ".$cols[$row[0]];
		$check = $db->query($sql);
		if (DB::IsError($check)) {
			die_issabelpbx( "Can not update column ".$row[0].": " . $check->getMessage() .  "<br>");
		}
	}
/*	else {
		//remove unused column
		$sql = "ALTER TABLE `$tablename` DROP COLUMN ".$row[0];
		$check = $db->query($sql);
		if(DB::IsError($check)) {
			echo "Can not remove unneeded column ".$row[0].": " . $check->getMessage() .  "<br>"; //non fatal error
		}
		else {
			print 'Removed unneeded column '.$row[0].' from $tablename table.<br>';
		}
	}*/
}

//add any missing columns that are not already in the table
foreach($cols as $key=>$val) {
	if(!in_array($key,$curret_cols))
	{
		$sql = "ALTER TABLE `$tablename` ADD ".$key." ".$val;
		$check = $db->query($sql);
		if (DB::IsError($check)) {
			die_issabelpbx( "Can not add column ".$key.": " . $check->getMessage() .  "<br>");
		}
		else {
            out(_("Added column $key to $tablename table"));
		}
	}
}

// no default values for this module
?>
