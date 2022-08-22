<?php

$rtype = 'http'.(isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=''?'s':'');

switch ($rtype) {
	case 'http':
		$port = ($_SERVER['SERVER_PORT'] == '80') ? '' : ':'.$_SERVER['SERVER_PORT'];
		break;
	case 'https':
		$port = ($_SERVER['SERVER_PORT'] == '443') ? '' : ':'.$_SERVER['SERVER_PORT'];
		break;
}

$html = 
	"<div style='margin-left:20px;margin-right:20px'>\n".
	"<h2>"._("Potential Security Breach")."</h2>\n".
	"<p>"._("You are attempting to modify settings from a URL that does not appear to have come from a IssabelPBX page link or button. This can occur if you manually typed in the URL below. This action has been blocked because the HTTP_REFERER does not match your current SERVER. If you require this access, you can set Check Server Referrer=false in Advanced Settings to disable this security check")."</p>\n".
	"<p>"._("The suspect URL is listed below. If this action is intended, you can click this link and your action will be processed. Do not proceed with this if you did not intended to execute this command as it may result in changes to your configuration.")."</p>\n".
	"<p>"._("SUSPECT LINK:")." &nbsp;<b><a href='".$_SERVER['REQUEST_URI']."'>"."$rtype://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].$port."</a></b>"."</p>\n".
	"</div>\n";
echo $html;

?>
