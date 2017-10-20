#!/usr/bin/php -q
<?php
//include bootstrap
$restrict_mods = array('queues' => true);
$bootstrap_settings['issabelpbx_auth'] = false;
if (!@include_once(getenv('ISSABELPBX_CONF') ? getenv('ISSABELPBX_CONF') : '/etc/issabelpbx.conf')) {
	include_once('/etc/asterisk/issabelpbx.conf');
}

if (isset($argv[1])) {
	$queue_toggle_code = $argv[1];
} else {
	$fcc = new featurecode('queues', 'que_toggle');
	$queue_toggle_code = $fcc->getCodeActive();
	unset($fcc);
}

if (isset($argv[2])) {
	$queue_pause_code = $argv[2];
} else {
	$fcc = new featurecode('queues', 'que_pause_toggle');
	$queue_pause_code = $fcc->getCodeActive();
	unset($fcc);
}

if ($queue_pause_code == '' && $queue_toggle_code == '') {
	exit(0);
}

$var = $astman->database_show('AMPUSER');
foreach ($var as $key => $value) {
	$myvar = explode('/',trim($key,'/'));
	$user_hash[$myvar[1]] = true;
}

foreach (array_keys($user_hash) as $user) {
	if ($user == '') {
		unset($user_hash[$user]);
		continue;
	}
	$user_hash[$user] = generate_queue_hints_get_devices($user);
}

$qpenalty=$astman->database_show('QPENALTY');
$qc = array();
foreach(array_keys($qpenalty) as $key) {
	$key = explode('/', $key);
	if ($key[3] == 'agents') {
		$qc[$key[4]][] = $key[2];
	}
}

if ($queue_toggle_code != '') foreach ($user_hash as $user => $devices) {
	if (!isset($qc[$user])) {
		continue;
	}
	$device_list = explode('&',$devices);
	foreach ($device_list as $device) {
		generate_queue_hints_set_login_hint($device, $qc[$user]);
	}
}

if ($queue_toggle_code == '') {
	exit(0);
}

// generate device hash
//
$var = $astman->database_show('DEVICE');
foreach ($var as $key => $value) {
	$myvar = explode('/',trim($key,'/'));
	$dev_hash[$myvar[1]][$myvar[2]] = $value;
}

// extract the static members in the form of Local/nnn@from-queue/n or similar
// determine the queue member 'nnn', and add this queue to them if not
// already there.
//
$qmr = queues_get_static_members();
foreach ($qmr as $q => $qmg) {
	foreach ($qmg as $qm) {
		if (strtoupper(substr($qm,0,1)) == 'L') {
			$tm = preg_replace("/[^0-9#\,*]/", "", $qm);
			$tma = explode(',',$tm);
			if (!isset($qc[$tma[0]]) || !in_array($q, $qc[$tma[0]])) {
				$qc[$tma[0]][] = $q;
			}
		}
	}
}

/* hash looks like this
 * [89222] => Array
 *         (
 *           [default_user] => 89222
 *           [dial] => SIP/89222
 *           [type] => fixed
 *           [user] => 89222
 *         )
 */
foreach ($dev_hash as $id => $device) {
	$tech = explode('/',$device['dial'],2);
	$tech = strtolower($tech[0]);
	if ($device['user'] != '' && ($tech == 'sip' || $tech == 'iax2')) {
		$pause_all_hints = array();
		foreach($qc[$device['user']] as $q) {
			$hint = "qpause:$q:Local/{$device['user']}@from-queue/n";
			$pause_all_hints[] = $hint;
			out("exten => $queue_pause_code*$id*$q,hint,$hint");
		}
		if (!empty($pause_all_hints)) {
			out("exten => $queue_pause_code*$id,hint," . implode('&', $pause_all_hints));
		}
	}
}

//---------------------------------------------------------------------

// Set the hint for a user based on the devices in their AMPUSER object
//
function generate_queue_hints_set_login_hint($device, $queues) {
	global $queue_toggle_code;

	if (trim($device) == '') {
		return;
	}
	$hlist = 'Custom:QUEUE' . $device . '*' . implode('&Custom:QUEUE' . $device . '*', $queues);
	out("exten => $queue_toggle_code*$device,hint,$hlist");
}


// Get the list of current devices for this user
//
function generate_queue_hints_get_devices($user) {
	global $astman;

	$devices = $astman->database_get('AMPUSER',$user.'/device');
	return trim($devices);
}
