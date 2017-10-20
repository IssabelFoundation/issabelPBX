<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
// Remove all weak password notifications
$nt = notifications::create($db);
$security_notifications = $nt->list_security();
foreach($security_notifications as $notification)  {
	if($notification['module'] == "weakpasswords")  {
		$nt->delete($notification['module'],$notification['id']);
	}
}

?>
