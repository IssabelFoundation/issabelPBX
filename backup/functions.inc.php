<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
$dir = dirname(__FILE__);
require_once($dir . '/functions.inc/class.backup.php');
require_once($dir . '/functions.inc/backup.php');
require_once($dir . '/functions.inc/servers.php');
require_once($dir . '/functions.inc/templates.php');
require_once($dir . '/functions.inc/restore.php');


/**
* do variable substitution 
*/
function backup__($var) {
	global $amp_conf;
	/*
	 * substitution string can look like: __STRING__
	 * find the two parimiter positions and search $this->amp_conf for the stringg
	 * return the origional string if substitution is not found
	 *
	 * for now, ONLY MATCHES UPERCASE in both $var and amp_conf
	 */
	
	//get first position
	$pos1 = strpos($var, '__');
	if ($pos1 === false) {
		return $var;
	}
	
	//get second position
	$pos2 = strpos($var, '__', $pos1 + 2);
	if ($pos2 === false) {
		return $var;
	}
	
	//get actual string, sans _'s
	$v = trim(substr($var, $pos1, $pos2 + 2), '_');

	//return a value if we have match, otherwise the origional string
	if (isset($amp_conf[$v])) {
		return str_replace('__' . $v . '__', $amp_conf[$v], $var);
	} else {
		return $var;
	}
}


function backup_log($msg) {
	$tmp = (function_exists('sys_get_temp_dir')) ? sys_get_temp_dir() : '/tmp';
	$cli = php_sapi_name() == 'cli' ? true : false;
	$str = '';
	$str .= $cli ? '' : 'data: ';
	$str .= $msg;
	$str .= $cli ? "\n" : "\n\n";
	echo $str;
	$logmsg = date("F j, Y, g:i a").' - '. $str;
	file_put_contents($tmp.'/backup.log', trim($logmsg)."\r\n", FILE_APPEND);
	if (!$cli) {
		ob_flush();
		flush();
	}
	
}

function backup_email_log($to, $from, $subject) {
	$tmp = (function_exists('sys_get_temp_dir')) ? sys_get_temp_dir() : '/tmp';
	$email_options = array('useragent' => 'issabelpbx', 'protocol' => 'mail');
	$email = new CI_Email();
	$msg[] = _('BACKUP LOG ATTACHED');
	$email->from($from);
	$email->to($to);
	$email->subject(_('Backup Log:') . $subject);
	$email->message(implode("\n", $msg));
	$email->attach($tmp.'/backup.log');
	$email->send();
	
	unset($msg);
}

function backup_clear_log() {
	$tmp = (function_exists('sys_get_temp_dir')) ? sys_get_temp_dir() : '/tmp';
	$fh = fopen($tmp.'/backup.log', 'w');
	fclose($fh);
}

