<?php
require_once("elastix_fop_auth.php");

$bootstrap_settings['issabelpbx_auth'] = false;
// don't include any of the functions.inc.php files from modules, this CLI version of
// module_admin is somtimes the only way to recover from a bad module being loaded into
// a system.
//
$restrict_mods = true;
if (!@include_once(getenv('ISSABELPBX_CONF') ? getenv('ISSABELPBX_CONF') : '/etc/issabelpbx.conf')) {
	include_once('/etc/asterisk/issabelpbx.conf');
}

if ($amp_conf["AMPWEBADDRESS"] == "")
	{$amp_conf["AMPWEBADDRESS"] = $_SERVER["HTTP_HOST"];}
	
if ($_SERVER["HTTP_HOST"] != $amp_conf["AMPWEBADDRESS"]) {
	$proto = ((isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on")) ? "https" : "http");
	header("Location: ".$proto."://".$amp_conf["AMPWEBADDRESS"].$_SERVER["REQUEST_URI"]);
	exit;
}

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Flash Operator Panel</title>
<style>
<!--
html,body {
	margin: 0;
	padding: 0;
	height: 100%;
	width: 100%;
}

-->
</style>
</head>
<body bgcolor="#ffffff">
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="100%" height="100%" id="operator_panel" align="left">
<param name="WMode" value="transparent"/>
<param name="allowScriptAccess" value="sameDomain" />
<param name="movie" value="flash/operator_panel.swf" />
<param name="quality" value="high" />
<param name="bgcolor" value="#ffffff" />
<param name="scale" value="exactfit" />
<embed src="flash/operator_panel.swf" quality="high" scale="exactfit" bgcolor="#ffffff" width="100%" height="100%" name="operator_panel" align="left" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" wmode="transparent" />
</object>
</body>
</html>
