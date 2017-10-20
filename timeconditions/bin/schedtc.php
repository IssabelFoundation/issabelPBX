#!/usr/bin/php -q
<?php

//include bootstrap
$restrict_mods = true;
$bootstrap_settings['issabelpbx_auth'] = false;
if (!@include_once(getenv('ISSABELPBX_CONF') ? getenv('ISSABELPBX_CONF') : '/etc/issabelpbx.conf')) {
    include_once('/etc/asterisk/issabelpbx.conf');
}

$tmp = $amp_conf['ASTSPOOLDIR'] . '/tmp';

if (isset($argv[1]) && ctype_digit($argv[1])) {
    $time_offset = $argv[1];
} else {
    $time_offset = 60;
}

if (isset($argv[2]) && is_dir($argv[2])) {
    $call_spool = $argv[2];
} else {
    $call_spool = $amp_conf['ASTSPOOLDIR'] . '/outgoing';
}

if (isset($argv[3]) && ($argv[3] == '0' || $argv[3] == '1')) {
    $file_index = $argv[3];
    $next_index = $file_index ? '0' : '1';
} else {
    $file_index = 0;
    $next_index = 1;
}

$call_file = "schedtc.$file_index.call";

$now = time();
$next_time = $now+$time_offset;

// Now try to have the call file go off 'on the minute'
//
$remainder = $next_time % 60;
if ($remainder < 30) {
    $next_time -= $remainder;
} else {
    $next_time += 60 - $remainder;
}
if ($next_time < ($now + 30)) {
    $next_time += 60;
}


// Pass in the file index not being used into the CID field to be used by the dialplan when launching
// the next call file. You can't just use the same name over, even changing the modificaiton time since
// as soon as the call file is processed it is deleted
//
$sched_script = "Channel: Local/s@tc-maint\nCallerID: \"$next_index\" <$next_index>\nApplication: NoCDR\n";

if (file_put_contents("$tmp/$call_file", $sched_script) === false) {
    error_log("FATAL: IssabelPBX Time Conditions {$argv[0]} failed to create temporary file: $tmp/$call_file");
    exit(1);
}
if (touch("$tmp/$call_file",$next_time, $next_time) === false) {
    error_log("ERROR: IssabelPBX Time Conditions {$argv[0]} failed to set time on temporary file: $tmp/$call_file");
    exit(1);
}
if (rename("$tmp/$call_file","$call_spool/$call_file") === false) {
    error_log("FATAL: IssabelPBX Time Conditions {$argv[0]} failed to install call file: $call_spool/$call_file");
    exit(1);
}
exit(0);
