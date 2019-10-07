<?php

/*** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** 

PBX Open Source Software Allinace

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

This script works in conjunction with the IssabelPBX Trunk Balancing Module version 1.1.2 and higher,
the module must be installed and enabled with defined balanced trunks. Running this script on the 
IssabelPBX host, will allow a user to enable or disable a defined balanced trunk.

Usage:
First argument: 	Balanced Trunk #, integer as reported in GUI - REQUIRED
Second argument:	Action, acceptable actions are enable, disable or toggle - Default is toggle



Last edited by lgaetz August 30, 2013
Script version: 0.0.2

/*** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** ***/


// include IssabelPBX bootstrap, requires IssabelPBX 2.9+
if (!@include_once(getenv('ISSABELPBX_CONF') ? getenv('ISSABELPBX_CONF') : '/etc/issabelpbx.conf')) { 
      include_once('/etc/asterisk/issabelpbx.conf'); 
}

If ($argc == 1) {
	// send to asterisk log line saying insufficient arguments
	exit;
}


$id = $argv[1];  //trunkbalance ID passed as argument
$action = $argv[2];  //optional action passed as argument


$tb = trunkbalance_get($id);
$tbchange = false;


if (is_array($tb)) {
	switch ($action) {
		
		case "enable":
			if ($tb['disabled'] != "on") {
				$tb['disabled'] = 'on';
				$tbchange = true;
			}
		break;
		
		case "disable":
			if ($tb['disabled'] == "on") {
				$tb['disabled'] = null;
				$tbchange = true;
			}
		break;
		
		default:
			if ($tb['disabled'] == "on") {
				$tb['disabled'] = null;
				$tbchange = true;
			}
			else {
				$tb['disabled'] = "on";
				$tbchange = true;
			}
		break;
	}
}

// if changes to the trunk are required, they get written here
if ($tbchange) {
	trunkbalance_edit($id,$tb);
	// send to asterisk log that balanced trunk $id status has changed 
}
?>