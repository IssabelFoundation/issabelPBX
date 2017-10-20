#!/usr/bin/php -q
<?php
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Copyright (C) 2012 HEHE Enterprises, LLC d.b.a. i9 Technologies
//	Copyright 2013 Schmooze Com Inc.
//
//Bootstrap IssabelPBX
$bootstrap_settings['issabelpbx_auth'] = false;
if(!@include_once(getenv('ISSABELPBX_CONF') ? getenv('ISSABELPBX_CONF') : '/etc/issabelpbx.conf')) {
	include_once('/etc/asterisk/issabelpbx.conf');
}

$ot_debug = true;

ot_debug("Starting...");
$channel = $argv[1];

$astman->SetVar($channel, "ONETOUCH_REC_SCRIPT_STATUS", "STARTED");

ot_debug("Channel: {$channel}");

//Attempt to determin the extension
ot_debug("Gathering variables");
$pickupExten = getVariable($channel, "PICKUP_EXTEN");
$callFileName = getVariable($channel, "CALLFILENAME");
$thisExtension = getVariable($channel, "THISEXTEN");
$realCallerIdNum = getVariable($channel, "REALCALLERIDNUM");
$fromExten = getVariable($channel, "FROMEXTEN");
if ($fromExten == '') {
	$fromExten = $realCallerIdNum;
}
$callFileNameParts = explode("-", $callFileName);
$callFileNameExten = $callFileNameParts[1];
$callFileNameType = $callFileNameParts[0];

ot_debug("PICKUP_EXTEN: {$pickupExten}");
ot_debug("CALLFILENAME: {$callFileName}");
ot_debug("THISEXTEN: {$thisExtension}");
ot_debug("REALCALLERIDNUM: {$realCallerIdNum}");
ot_debug("FROMEXTEN: {$fromExten}");
ot_debug("callFileNameExten: {$callFileNameExten}");
ot_debug("callFileNameType: {$callFileNameType}");

ot_debug("Checking pickup extension");
if($pickupExten != "") {
	ot_debug("Setting THISEXTEN to {$callFileNameExten}");
	$astman->SetVar($channel, "THISEXTEN", $callFileNameExten);
	$thisExtension = $callFileNameExten;
}

ot_debug("Checking this extension");
if($thisExtension == "") {
	$thisExtension = ($realCallerIdNum == "" ? $callFileNameExten : $fromExten);
	ot_debug("Setting THISEXTEN to {$thisExtension}");
	$astman->SetVar($channel, "THISEXTEN", $thisExtension);
}

// Attended Transfer Issues:
//
// Testing has uncovered some cases where thisExtension ends up being derrived to the extension that
// called this extension in a scenario where the orginal caller dose an attended transfer elsewhere.
// So ... we go through the added trouble of checking if the DEVICE object for the suspected user
// matches the connected channel.
//
// If it does NOT match, we search through ALL the DEVICE dial records to see if any of them match
// this channel and if so, check their assigned user to identify who this is.
//

// Let's make sure $thisExtension matches our device
//
$device = $astman->database_get("AMPUSER/{$thisExtension}", "device");
ot_debug("Checking to make sure thisExtension is correct, Got device(s) $device");
$devices = explode('&',$device);
$channelComponents = explode('-', $channel);
$baseChannel = $channelComponents[0];
$dev_confirmed=false;
// in case mutliple devices are defined in the form of 222&322
foreach ($devices as $dev) {
	$dial = $astman->database_get("DEVICE/{$dev}", "dial");
	ot_debug("checking device $dev got dial $dial");
	if (strcasecmp($dial, $baseChannel) == 0) {
		$dev_confirmed = true;
		ot_debug("Found $dev same as $channel so we are good");
		break;
	}
}
// If we have not confirmed, let's search all the DEVICE array and see if we can find a dial string that matches
//
if (!$dev_confirmed) {
	ot_debug("thisExension $thisExtension is suspicious, checking for a better match");
	$all_devices = $astman->database_show('DEVICE');
	foreach ($all_devices as $key => $dial) {
		$myvar = explode('/',trim($key,'/'));
		if (!empty($myvar[2]) && $myvar[2] == 'dial') {
			//ot_debug("Checking DEVICE/{$myvar[1]}");
			if (strcasecmp($dial, $baseChannel) == 0) {
				// we found a DEVICE who's dialstring matches, hopefully they have a user assigned
				$user = $astman->database_get("DEVICE/{$myvar[1]}", "user");
				ot_debug("We found device {$myvar[1]} to match our channel with user: $user");
				if ($user != '') {
					$dev_confirmed = true;
					$thisExtension = $user;
					ot_debug("Changed thisExtension to $thisExtension");
					// wasn't sure if we should change it back in the channel since it could create undeterministic behavior elsewhere
					break;
				} else {
					ot_debug("No user specified, unable to track a better user to this device");
					break;
				}
			}
		}
	}
}

//Check on demand setting for the extension
ot_debug("Checking on demand setting");
$extenRecordingOnDemand = $astman->database_get("AMPUSER/{$thisExtension}/recording", "ondemand");
ot_debug("AMPUSER/{$thisExtension}/recording/ondemand: {$extenRecordingOnDemand}");
if($callFileNameType == "exten" && $extenRecordingOnDemand != "enabled") {
	ot_debug("On demand setting off exiting");
	$astman->SetVar($channel, "ONETOUCH_REC_SCRIPT_STATUS", "ON_DEMAND_SETTING_DISABLED");
	exit(0);
}

//Grab the bridge peer
$bridgePeer = getVariable($channel, "BRIDGEPEER");
ot_debug("BRIDGEPEER: {$bridgePeer}");

$myMaster = getVariable($channel, "MASTER_CHANNEL(CHANNEL(name))");
//ot_debug("myMaster: {$myMaster}");
$theirMaster = getVariable($bridgePeer, "MASTER_CHANNEL(CHANNEL(name))");
//ot_debug("theirMaster: {$theirMaster}");

// Figure out who is 'MasterChannel' - in the simplest case, there is a real
// master channel. Otherwise let's see if we've already run into this dilema
// and tagged someone with the MASERONETOUCH variable set. If not, then let's
// see which channel inheritted the ONETOUCH_RECFILE variable if any and lastly
// let's just choose 'us' if all else fails.
//
if (($myMaster == $theirMaster) && ($myMaster == $channel) || ($myMaster == $brdigePeer)) {
	// Since these agree, there was a real master channel relationship, otherwise these
	// would have just been the channel from each side of the bridge. Maybe there is
	// a more CONCLUSIVE way to deterine such a relationship???
	//
	$masterChannel = $myMaster;
	ot_debug("There is a real Master Channel: {$masterChannel}");
} else {
	$masterOneTouchUs = getVariable($channel, "MASTERONETOUCH");
	$masterOneTouchThem = getVariable($bridgePeer, "MASTERONETOUCH");
	if ($masterOneTouchUs) {
		$masterChannel = $channel;
		ot_debug("We were previsously designaged Master because of MASTERONETOUCH");
	} else if ($masterOneTouchThem) {
		$masterChannel = $bridgePeer;
		ot_debug("They were previsously designaged Master because of MASTERONETOUCH");
	} else if (getVariable($bridgePeer, "ONETOUCH_RECFILE") != '') {
		$masterChannel = $bridgePeer;
		$astman->SetVar($masterChannel, "MASTERONETOUCH", 'TRUE');
		ot_debug("No conclusive master but they have ONETOUCH_RECFILE defined so let's designate them");
	} else {
		// Either ONETOUCH_RECFILE is set for us or not, either way ... tag, we're it!
		//
		$masterChannel = $channel;
		$astman->SetVar($masterChannel, "MASTERONETOUCH", 'TRUE');
		ot_debug("No conclusive master so either we have MASTERONETOUCH defined or no one does, so we do now:)");
	}
}

//If channel is already being recoreded if so stop the recording
ot_debug("Checking if channel is already recording");
$masterChannelOneTouchRec = getVariable($masterChannel, "ONETOUCH_REC");
ot_debug("MASTER_CHANNEL(ONETOUCH_REC): {$masterChannelOneTouchRec}");
if($masterChannelOneTouchRec == "RECORDING") {
	ot_debug("Stop recording channel");
	$astman->stopmixmonitor($channel, rand());
	// Setting in both channels in case a subsequent park or attended transfer of one
	$astman->SetVar($channel, "ONETOUCH_REC", "PAUSED");
	$astman->SetVar($bridgePeer, "ONETOUCH_REC", "PAUSED");
	$astman->SetVar($channel, "REC_STATUS", "PAUSED");
	$astman->SetVar($bridgePeer, "REC_STATUS", "PAUSED");

	// TODO: Can it be blank at this point
	//
	if($thisExtension == "") {
		$dialPeerNumber = getVariable($channel, "DIALEDPEERNUMBER");
		ot_debug("DIALEDPEERNUMBER: {$dialPeerNumber}");
		$thisExtension = ($realCallerIdNum == "" ? $dialPeerNumber : $fromExten);
		$astman->SetVar($channel, "THISEXTEN", $thisExtension);
	}
	$astman->SetVar($channel, "ONETOUCH_REC_SCRIPT_STATUS", "RECORDING_PAUSED");
	exit(0);
}

//If the recording poicy is never exit
ot_debug("Checking recording polcy");
$masterChannelRecPolicyMode = getVariable($masterChannel, "REC_POLICY_MODE");
ot_debug("MASTER_CHANNEL(REC_POLICY_MODE): {$masterChannelRecPolicyMode}");
if($masterChannelRecPolicyMode == "never") {
	ot_debug("Recording polcy is never exiting");
	$astman->SetVar($channel, "ONETOUCH_REC_SCRIPT_STATUS", "POLICY_IS_NEVER");
	exit(0);
}

//Check one touch recording setting
ot_debug("Checking one touch recording");
$masterChannelRecStatus = getVariable($masterChannel, "REC_STATUS");
ot_debug("MASTER_CHANNEL(REC_STATUS): {$masterChannelRecStatus}");
if($masterChannelOneTouchRec == "" && $masterChannelRecStatus == "RECORDING") {
	ot_debug("One touch recording is blank exiting");
	$astman->SetVar($channel, "ONETOUCH_REC_SCRIPT_STATUS", "MASTER_CHANNEL_STATUS_IS_RECORDING");
	exit(0);
}

//Start recording the channel
ot_debug("Recording Channel");
$mixMonDir = getVariable($channel, "MIXMON_DIR");
$year = getVariable($channel, "YEAR");
$month = getVariable($channel, "MONTH");
$day = getVariable($channel, "DAY");
$mixMonFormat = getVariable($channel, "MIXMON_FORMAT");
$mixMonPost = getVariable($channel, "MIXMON_POST");
ot_debug("MIXMON_DIR: {$mixMonDir}");
ot_debug("YEAR: {$year}");
ot_debug("MONTH: {$month}");
ot_debug("DAY: {$day}");
ot_debug("MIXMON_FORMAT: {$mixMonFormat}");
ot_debug("MIXMON_POST: {$mixMonPost}");

// Setting in both channels in case a subsequent park or attended transfer of one
$astman->SetVar($bridgePeer, "ONETOUCH_REC", "RECORDING");
$astman->SetVar($channel, "ONETOUCH_REC", "RECORDING");
$astman->SetVar($bridgePeer, "REC_STATUS", "RECORDING");
$astman->SetVar($channel, "REC_STATUS", "RECORDING");
$astman->SetVar($channel, "AUDIOHOOK_INHERIT(MixMonitor)", "yes");
$astman->SetVar($bridgePeer, "AUDIOHOOK_INHERIT(MixMonitor)", "yes");
$astman->mixmonitor($channel, "{$mixMonDir}{$year}/{$month}/{$day}/{$callFileName}.{$mixMonFormat}", "a", $mixMonPost, rand());

//Set the monitor format and file name for the cdr entry
ot_debug("Setting CDR info");
$monFmt = ($mixMonFormat != "" ? $mixMonFormat : "wav");
$astman->SetVar($channel, "MON_FMT", $monFmt);
$astman->SetVar($bridgePeer, "CDR(recordingfile)", "{$callFileName}.{$monFmt}");
$astman->SetVar($channel, "CDR(recordingfile)", "{$callFileName}.{$monFmt}");

// The following 2 lines are to deal with a bug in Asterisk 1.8 not setting the CDR rec file after hangup
$astman->SetVar($bridgePeer, "ONETOUCH_RECFILE", "{$callFileName}.{$monFmt}");
$astman->SetVar($channel, "ONETOUCH_RECFILE", "{$callFileName}.{$monFmt}");

$astman->SetVar($channel, "ONETOUCH_REC_SCRIPT_STATUS", "RECORDING_STARTED");

//Get variable function
function getVariable($channel, $varName) {
	global $astman;

	$results = $astman->GetVar($channel, $varName, rand());

	if($results["Response"] != "Success"){
		ot_debug("Failed to get var {$varName} exiting");
		exit(1);
	}

	return $results["Value"];
}

function ot_debug($string) {
	global $ot_debug;
	if ($ot_debug) {
		dbug($string);
		echo "$string\n";
	}
}

?>
