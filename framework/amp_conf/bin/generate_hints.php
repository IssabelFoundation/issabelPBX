#!/usr/bin/php -q
<?php
//include bootstrap
$restrict_mods = true;
$bootstrap_settings['issabelpbx_auth'] = false;
if (!@include_once(getenv('ISSABELPBX_CONF') ? getenv('ISSABELPBX_CONF') : '/etc/issabelpbx.conf')) {
	include_once('/etc/asterisk/issabelpbx.conf');
}

// If set to nointercom then don't generate any hints
//
$intercom_code = isset($argv[1]) ? $argv[1] : '';
$campon_toggle = isset($argv[2]) ? $argv[2] : '';
$dnd_mode      = isset($argv[3]) ? $argv[3] : '';

$ast_with_dahdi = ast_with_dahdi();

$var = $astman->database_show('AMPUSER');
foreach ($var as $key => $value) {
	$myvar = explode('/',trim($key,'/'));
	$user_hash[$myvar[1]] = true;
}

foreach (array_keys($user_hash) as $user) {
	if ($user != 'none' && $user != '') {
		$devices = get_devices($user);
		//debug("Set hints for user: $user for devices:  ".$devices,5);
		set_hint($user, $devices);
	}
}

//---------------------------------------------------------------------

// Set the hint for a user based on the devices in their AMPUSER object
//
function set_hint($user, $devices) {
	//debug("set_hint: user: $user, devices: $devices",8);
	global $amp_conf;
	global $astman;
	global $dnd_mode;
	global $intercom_code;
	global $campon_toggle;

	$dnd_string = ($dnd_mode == 'dnd')?"&Custom:DND$user":'';
	$presence_string = $amp_conf['AST_FUNC_PRESENCE_STATE']?",CustomPresence:$user":'';

	if ($devices) {
		$dial_string = get_dial_string($devices);
		echo "exten => $user,hint,".$dial_string.$dnd_string.$presence_string."\n";
		if ($intercom_code != 'nointercom' && $intercom_code != '') {
			echo "exten => $intercom_code"."$user,hint,".$dial_string.$dnd_string.$presence_string."\n";
		}
		if ($campon_toggle != 'nocampon' && $campon_toggle != '') {

      $dev_arr = explode('&',$dial_string);
      $hint_val = 'ccss:'.implode('&ccss:',$dev_arr);
			echo "exten => $campon_toggle"."$user,hint,$hint_val"."\n";
		}
	} else if ($dnd_mode == 'dnd') {
		echo "exten => $user,hint,Custom:DND".$user.$presence_string."\n";
		if ($intercom_code != 'nointercom' && $intercom_code != '') {
			echo "exten => $intercom_code"."$user,hint,Custom:DND".$user.$presence_string."\n";
		}
	}
}

// Get the actual technology dialstrings from the DEVICE objects (used
// to create proper hints)
//
function get_dial_string($devices) {
	//debug("get_dial_string: devices: $devices",8);
	global $astman;
	global $ast_with_dahdi;

	$device_array = explode( '&', $devices );
	$dialstring = ''; 
	foreach ($device_array as $adevice) {
		$dds = $astman->database_get('DEVICE',$adevice.'/dial');
		$dialstring .= $dds.'&';
	}
	if ($ast_with_dahdi) {
		$dialstring = str_replace('ZAP/', 'DAHDI/', $dialstring);
	}
	return trim($dialstring," &");
}

// Get the list of current devices for this user
//
function get_devices($user) {
	//debug("get_devices: user: $user", 8);
	global $astman;

	$devices = $astman->database_get('AMPUSER',$user.'/device');
	return trim($devices);
}
