<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright (C) 2005, Xorcom
//	Copyright 2013 Schmooze Com Inc.
function cli_runcommand($txtCommand) {
	global $astman;

	if ($astman) {

		$html_out = "<pre>";
		$response = $astman->send_request('Command',array('Command'=>"$txtCommand"));
		$response = explode("\n",$response['data']);
		unset($response[0]); //remove the Priviledge Command line
		$response = implode("\n",$response);
		$html_out .= htmlspecialchars($response);
		$html_out .= "</pre>";
		return $html_out;
	}
}
?>
