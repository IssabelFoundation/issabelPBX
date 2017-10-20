#!/usr/bin/php
<?php
//include issabelpbx configuration 
$restrict_mods = array('fax' => true);
if (!@include_once(getenv('ISSABELPBX_CONF') ? getenv('ISSABELPBX_CONF') : '/etc/issabelpbx.conf')) {
    include_once('/etc/asterisk/issabelpbx.conf');
}

$var['hostname'] 	= gethostname();
$var['from']		= sql('SELECT value FROM fax_details WHERE `key` = "sender_address"','getOne');
$var['from']		= $var['from'] ? $var['from'] : 'fax@issabel.pbx';
$var['subject']		= '';
$var 				= array_merge($var, get_opt());
$var['callerid']	= $var['callerid'] === true ? '' : $var['callerid'];//prevent callerid from being blank
$var['keep_file']	= $var['delete'] == 'true' ? false : true;
$var['attachformat']	= $var['attachformat'] ? $var['attachformat'] : 'pdf';

//double check some of the options
foreach ($var as $k => $v) {
	switch ($k) {
		case 'file':
			if (!file_exists($var['file'])) {
				die_fax('email-fax dying, file ' . $file . ' not found!');
			}
			break;
		case 'to':
			if(!$var['to']) {
				die_fax('email-fax dying, no destination found ($var[\'to\'] is empty)');
			}
			break;
		case 'subject':
			if (!$var['subject']) {
				if (isset($var['direction']) && $var['direction'] == 'outgoing') {
					$var['subject'] = 'Outgoing fax results';
				} else {
					if ($var['callerid']) {
						$var['subject'] = 'New fax from: ' . $var['callerid'];
					} else {
						$var['subject'] = 'New fax received';
					}
				}
				
			}
			break;
	}
}

if (isset($var['direction']) && $var['direction'] == 'outgoing') {
	$msg = 'Sent to ' . $var['dest'] . "\n";
	$msg .= 'Status: ' . $var['status'] . "\n";
	$msg .= 'At: ' . date('r') . "\n";
	$msg .= 'On: ' . $var['hostname'] . "\n";
	if ($var['exten']) {
		$msg .= 'For extension: ' . $var['exten'] . "\n";
	}
} else {
	$msg = 'Enclosed, please find a new fax ';
	if ($var['callerid']) {
		$msg .= 'from: ' . $var['callerid'] ;
	} 
	$msg .= "\n";
	$msg .= 'Received & processed: ' . date('r') . "\n";
	$msg .= 'On: ' . $var['hostname'] . "\n";
	$msg .= 'Via: ' . $var['dest'] . "\n";
	if ($var['exten']) {
		$msg .= 'For extension: ' . $var['exten'] . "\n";
	}
}


//build email
$email = new CI_Email();

$email->from($var['from']);
$email->to($var['to']);
$email->subject($var['subject']);
$email->message($msg);

$tif = $var['file'];
switch ($var['attachformat']) {
case 'both':
	$pdf = fax_file_convert('tif2pdf', $var['file'], '', true);
	$email->attach($pdf);
	$email->attach($tif);
	break;
case 'tif':
	$email->attach($tif);
	break;
case 'pdf':
	$pdf = fax_file_convert('tif2pdf', $var['file'], '', true);
	$email->attach($pdf);
	break;
}

$email->send();

if ($var['keep_file'] === false) {
	unlink($tif);
}

function die_fax($error) {
	dbug('email-fax', $error);
	die($error);
}

/**
 * Parses $GLOBALS['argv'] for parameters and assigns them to an array.
 *
 * Supports:
 * -e
 * -e <value>
 * --long-param
 * --long-param=<value>
 * --long-param <value>
 * <value>
 *
 * @param array $noopt List of parameters without values
 */
function get_opt($noopt = array()) {
	$result = array();
	$params = $GLOBALS['argv'];

	while (list($tmp, $p) = each($params)) {
		if ($p{0} == '-') {
			$pname = substr($p, 1);
			$value = true;
			if ($pname{0} == '-') {
				// long-opt (--<param>)
				$pname = substr($pname, 1);
				if (strpos($p, '=') !== false) {
					// value specified inline (--<param>=<value>)
					list($pname, $value) = explode('=', substr($p, 2), 2);
				}
			}
			// check if next parameter is a descriptor or a value
			$nextparm = current($params);
			if (!in_array($pname, $noopt) && $value === true && $nextparm !== false && $nextparm{0} != '-') {
				list($tmp, $value) = each($params);
			}
			$result[$pname] = $value;
		} else {
			// param doesn't belong to any option
			$result[] = $p;
		}
	}
	return $result;
}

?>
