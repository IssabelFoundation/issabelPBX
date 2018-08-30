<?php

/* 
 * Call Me constants
 */
define("CALLME_SUCCESS", "The call has been answered.");
define("CALLME_FAILURE", "The call failed.  Perhaps the line was busy.");
define("CALLME_ERROR", "System error.");

/*
 *
 * Include admin/functions.inc.php for our call to parse_amportal_conf()
 * Include admin/common/php-asmanager.php to use the AGI_AsteriskManager class for accessing the AMI
 *
 */
//require_once('../admin/functions.inc.php');
//require_once('../admin/common/php-asmanager.php');
//global $amp_conf;
//global $astman;
//$amp_conf       = parse_amportal_conf($AMPORTAL_CONF_FILE);
//$astman 		= new AGI_AsteriskManager();
// attempt to connect to asterisk manager proxy
/*
if (!isset($amp_conf["ASTMANAGERPROXYPORT"]) || !$res = $astman->connect("127.0.0.1:".$amp_conf["ASTMANAGERPROXYPORT"], $amp_conf["AMPMGRUSER"] , $amp_conf["AMPMGRPASS"], 'off'))
{
	// attempt to connect directly to asterisk, if no proxy or if proxy failed
	if (!$res = $astman->connect("127.0.0.1:".$amp_conf["ASTMANAGERPORT"], $amp_conf["AMPMGRUSER"] , $amp_conf["AMPMGRPASS"], 'off'))
	{
		// couldn't connect at all
		unset( $astman );
		$_SESSION['ari_error'] =
		_("ARI does not appear to have access to the Asterisk Manager.") . " ($errno)<br>" .
		_("Check the ARI 'main.conf.php' configuration file to set the Asterisk Manager Account.") . "<br>" .
		_("Check /etc/asterisk/manager.conf for a proper Asterisk Manager Account") . "<br>" .
		_("make sure [general] enabled = yes and a 'permit=' line for localhost or the webserver.");
	}
}
*/
function callme_close()
{
	global $astman;
	if (is_object($astman))
	{
		$astman->logoff();
		$astman->disconnect();
	}
	unset($astman);
}
/*
 * Call Me functions
 */
 /* Return the call me number stored in the database. */
function callme_getnum($exten)
{
        global $astman;
        $cmd 		= "database get AMPUSER $exten/callmenum";
	$callme_num 	= '';
        $results 	= $astman->Command($cmd);

	if (is_array($results))
	{
		foreach ($results as $results_elem)
		{
			if (preg_match('/Value: [^\s]*/', $results_elem, $matches) != 0)
			{
				$parts = preg_split('/\s/', trim($matches[0]));
				$callme_num = $parts[1];
			}

		}
	}

        return $callme_num;
}

/* Set the call me number to a new value.  No return value. */
function callme_setnum($exten, $callme_num)
{
        global $astman;

  			$callme_num = preg_replace("/[^0-9*#+]/", "", $callme_num);
        $cmd = "database put AMPUSER $exten/callmenum $callme_num";
        $astman->Command($cmd);
        return;
}

/* Perform the Originate action to the call me number for message playing. */
/* Return the result of the call (success/failure/error).                  */
function callme_startcall($to, $from, $new_path)
{
	global $astman;

  if (!preg_match("/^[0-9*#+]+$/",$to)) { 
		issabelpbx_log(IPBX_LOG_SECURITY, sprintf(_('Malformed callme number passed to callme_startcall $to field could be Security Breach: %s'), $to));
		return false;
	}
  if (!preg_match("/^[0-9]+$/",$from)) { 
		issabelpbx_log(IPBX_LOG_SECURITY, sprintf(_('Malformed callme number passed to callme_startcall $from field could be Security Breach: %s'), $to));
		return false;
	}
	// TODO: should I check that new_path is a valid sound file to play and bomb out if not as possible security protection?

	$channel	= "Local/$to@from-internal/n";
	$context	= "vm-callme";
	$extension	= "s";
	$priority	= "1";
	$callerid	= "VMAIL/$from";
	$engine_info = engine_getinfo();
        $version = $engine_info['version'];
        if (version_compare($version, "1.6", "ge")) {
                $variable       = "MSG=$new_path,MBOX=$from";
        } else {
                $variable       = "MSG=$new_path|MBOX=$from";
        }

	/* Arguments to Originate: channel, extension, context, priority, timeout, callerid, variable, account, application, data */
	$status = $astman->Originate($channel, $extension, $context, $priority, NULL, $callerid, $variable, NULL, NULL, NULL);
	if (is_array($status))
	{
		foreach ($status as $status_elem)
		{
			if (preg_match('/Originate successfully queued/', $status_elem, $matches) != 0)
			{
				return CALLME_SUCCESS;
			}
		}
	} 
	return CALLME_FAILURE;
}

function callme_eventsoff()
{
	global $astman;
	$astman->Events("off");
	return;
}

/* Returns boolean value for a call's success status. */
function callme_succeeded($status)
{
	if (strcmp($status, CALLME_SUCCESS) == 0)
		return true;
	else
		return false;
}

/* Hangs up an existing channel $exten is associated with.  No return value. */
function callme_hangup($exten)
{
	global $astman;
	$cmd 		= "local show channels";
  $chan_pat = '/[\s]*Local\/' . preg_quote(trim($exten)) . '@from\-internal\-[a-zA-Z0-9]*(,|;)(1|2)[\s]*/';
	$matches[0] 	= "";
	$response 	= "";
	$channel 	= "";
	$local_channels = $astman->Command($cmd);

	/* Look for our local channel. */
	if (is_array($local_channels))
	{
		foreach ($local_channels as $local_channels_elem)
		{
			preg_match($chan_pat, $local_channels_elem, $matches);
			if ($matches[0] != "")
			{
				$channel = $matches[0];
				break;
			}
		}
	} else
	{
		$channel = "";
	}

	/* If the channel was still up, hang it up. */ 
	if ($channel != "")
	{
		$astman->Hangup(trim($channel));
	}
	return; 
}
?>
