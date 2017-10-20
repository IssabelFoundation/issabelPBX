<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$getvars = array(
	'action'		=> '',
	'dateformat'	=> '',
	'display' 		=> '',
	'appendhostname'=> '',
	'logfile'		=> '',
	'logfiles'		=> '',
	'lines'			=> '',
	'queue_log'		=> '',
	'rotatestrategy'=> '',
	'view'			=> ''
);

foreach ($getvars as $k => $v) {
	$var[$k]	= isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
}

//sanitize input
$var['lines'] = preg_replace("/[^0-9]/", "", $var['lines']);
$var['logfile'] = preg_replace("/[^0-9]/", "", $var['logfile']);

//echo logfiles_rnav();

//respond to ajax requests
if ($var['lines']) {
	while (ob_get_level()) {
		ob_end_clean();
	}
	echo logfiles_get_logfile($var['lines'], $var['logfile']);
	exit();

}

switch ($var['action']) {
	case 'save':
		logfiles_put_opts($var);
		break;
}
