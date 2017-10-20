<?php

function parse_amportal_conf($filename) {
	$file = file($filename);
	foreach ($file as $line) {
		if (preg_match("/^\s*([a-zA-Z0-9_]+)=([a-zA-Z0-9 .&-@=_!<>\"\']+)\s*$/",$line,$matches)) {
			$conf[ $matches[1] ] = $matches[2];
		}
	}
	return $conf;
}

$amp_conf = parse_amportal_conf("/etc/amportal.conf");

if ($amp_conf["AMPWEBADDRESS"] == "") {
	$amp_conf["AMPWEBADDRESS"] = $_SERVER["HTTP_HOST"];
}
 
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
<param name="allowScriptAccess" value="sameDomain" />
<param name="movie" value="flash/operator_panel.swf?context=<?php echo $_REQUEST['context'] ?>" />
<param name="quality" value="high" />
<param name="bgcolor" value="#ffffff" />
<param name="scale" value="exactfit" />
<embed src="flash/operator_panel.swf?context=<?php echo $_REQUEST['context'] ?>" quality="high" scale="exactfit" bgcolor="#ffffff" width="100%" height="100%" name="operator_panel" align="left" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>
</body>
</html>
