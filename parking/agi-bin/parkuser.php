#!/usr/bin/php -q
<?php
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

require_once "phpagi.php";
require_once "phpagi-asmanager.php";
$AGI = new AGI();

$ampmgruser  = get_var( $AGI, "AMPMGRUSER" );
$ampmgrpass  = get_var( $AGI, "AMPMGRPASS" );
$astman = $AGI->new_AsteriskManager();
if (!$astman->connect("127.0.0.1", $ampmgruser , $ampmgrpass,'off')) {
	exit (1);
}

/*
[sub-park-user]
exten => s,1,Set(UEXTEN=UNKNOWN)
exten => s,n,Set(UNAME=UNKNOWN)
exten => s,n,Set(DEVS=${DB_KEYS(DEVICE)})
exten => s,n,While($["${SET(DEV=${POP(DEVS)})}" != ""])
exten => s,n,GotoIf($["${DB(DEVICE/${DEV}/dial)}" = "${CUT(CHANNEL(name),-,1)}"]?found)
exten => s,n,EndWhile
exten => s,n,Return()
exten => s,n(found),ExecIf($[${LEN(${DB(DEVICE/${DEV}/user)})} > 0]?Set(UEXTEN=${DB(DEVICE/${DEV}/user)}))
exten => s,n,ExecIf($[${LEN(${UEXTEN})} > 0]?Set(UNAME=${DB(AMPUSER/${UEXTEN}/cidname)}))
exten => s,n,Return()
*/

$chan_full  = get_var( $AGI, "CHANNEL(name)" );
$chan_arr = explode('-',$chan_full,2);
$channel = $chan_arr[0];

$all_devices = $astman->database_show('DEVICE');
foreach ($all_devices as $key => $dial) {
	$myvar = explode('/',trim($key,'/'));
	if (!empty($myvar[2]) && $myvar[2] == 'dial') {
		pu_verbose("checking " . $myvar[1], 6);
		if (strcasecmp($dial, $channel) == 0) {
			// we found a DEVICE who's dialstring matches, hopefully they have a user assigned
			$uexten = $astman->database_get("DEVICE/{$myvar[1]}", "user");
			if ($uexten != '') {
				$uname = $astman->database_get("AMPUSER/$uexten", "cidname");
				break;
			} else {
				$uexten = 'UNKNOWN';
				$uname = 'UNKNOWN';
				break;
			}
		}
	}
}
$AGI->set_variable('UEXTEN',$uexten);
$AGI->set_variable('UNAME',$uname);

function get_var( $agi, $value) {
	$r = $agi->get_variable( $value );
	
	if ($r['result'] == 1) {
		$result = $r['data'];
		return $result;
	}
	return '';
}

function pu_verbose($string, $level=3) {
	global $AGI;
	$AGI->verbose($string, $level);
}
