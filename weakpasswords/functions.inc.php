<?php 
/* $Id: */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
function weakpasswords_get_config($engine) {
	switch($engine) {
		case "asterisk":
			// Clear all weak password notifications
			$nt = notifications::create($db);
			$security_notifications = $nt->list_security();
			foreach($security_notifications as $notification)  {
				if($notification['module'] == "weakpasswords")  {
					$nt->delete($notification['module'],$notification['id']);
				}
			}
			// Generate new notifications
			$weak = weakpasswords_get_users();
			if(sizeof($weak) > 0)  {
				$extended_text = _("Warning: The use of weak SIP/IAX passwords can compromise this system resulting in toll theft of your telephony service.  You should change the reported devices and trunks to use strong secrets.")."<br /><br />"; 
				$count = 0;
				foreach($weak as $details)  {
					$extended_text .= sprintf(_("%s: %s / %s<br>"), $details['deviceortrunk'], $details['name'], $details['message']);
					$count++;
				}
				if ($count == 1) {
					$nt->add_security("weakpasswords", "all", $count." "._("extension/trunk has weak secret"),$extended_text);
				} else {
					$nt->add_security("weakpasswords", "all", $count." "._("extensions/trunks have weak secrets"),$extended_text);
				}
			}
		break;
	}
}

function weakpasswords_get_users()  {
	global $db;

	$sql = "SELECT 'SIP' as tech,s.id as id, s2.data as device,s.data as secret FROM sip s LEFT JOIN sip s2 ON s.id=s2.id AND s2.keyword='account' WHERE s.keyword='secret'";
	$sipsecrets = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
	$sql = "SELECT 'IAX' as tech,s.id as id, s2.data as device,s.data as secret FROM iax s LEFT JOIN iax s2 ON s.id=s2.id AND s2.keyword='account' WHERE s.keyword='secret'";
	$iaxsecrets = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
	$secrets = array_merge($sipsecrets,$iaxsecrets);
	$weak = array();
	foreach($secrets as $arr)  {
		$name = $arr['device'];
		$id = $arr['id'];
		$secret = $arr['secret'];
		$tech = $arr['tech'];

		if($id == $name)  {
			$deviceortrunk = _("Extension");
		}
		else  {
			$deviceortrunk = sprintf(_("%s Trunk"), $tech);
		}
		$reversed = strrev($secret);
		$match = "0123456789";
		if($secret == '')
		{
			$weak[] = array("deviceortrunk" => $deviceortrunk, "name" => $name, "message" => _("Secret is empty"), "secret" => $secret);
		}
		else if(strpos($match,$secret) || strpos($match,$reversed) && strlen($secret) < 10)  {
			$weak[] = array("deviceortrunk" => $deviceortrunk, "name" => $name, "message" => _("Secret has sequential digits"), "secret" => $secret);
		}
		else if(isset($device) && $device == $secret)  {
			$weak[] = array("deviceortrunk" => $deviceortrunk, "name" => $name, "message" => _("Secret same as device"), "secret" => $secret);
		}
		else if(preg_match("/(.)\\1{3,}/",$secret,$regs) && strlen($secret) < 10)  {
			$weak[] = array("deviceortrunk" => $deviceortrunk, "name" => $name, "message" => _("Secret has consecutive digit ").$regs[1], "secret" => $secret);
		}
		else if(strlen($secret) < 6)  {
			$weak[] = array("deviceortrunk" => $deviceortrunk, "name" => $name, "message" => _("Secret less than 6 digits"), "secret" => $secret);
		}
	}
	return $weak;
}
?>
