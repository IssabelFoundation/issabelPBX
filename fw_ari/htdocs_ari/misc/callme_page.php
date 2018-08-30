<?php

/**
 * @file
 * for making call to play message
 */

chdir("..");
$restrict_mods = true;
include_once("./includes/bootstrap.php");

$pageaction = $_REQUEST['action'];

if (!isset($_SESSION['ari_user']['extension']) || $pageaction === 'c' && !isset($_SESSION['ari_user']['recfiles'][$_REQUEST['recindex']])) {
	issabelpbx_log(IPBX_LOG_SECURITY, _("Potential malicious access blocked in the User Portal (ARI)"));
	die; // Disable anonymous or malicious access.
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <TITLE>Voicemail Message Call Me Control</TITLE>
    <link rel="stylesheet" href="../theme/main.css" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  </head>
	<body>

<?php

  // login to database

	if ($bootstrap_settings['astman_connected']) {
	$extension  = $_SESSION['ari_user']['extension'];
	$path       = $_SESSION['ari_user']['recfiles'][$_REQUEST['recindex']];
	$to         =  callme_getnum($extension);
	$new_path   = substr($path, 0, -4);		/* Without the sound file extension. */
	/* Either start or end the call me call */
	switch($pageaction)
	{
		case "c":
			/* Call me. */
			$call_status = callme_startcall($to, $extension, $new_path);
			echo("<table class='voicemail' style='width: 100%; height: 100%; margin: 0 0 0 0; border: 0px; padding: 0px'><tr><td valign='middle' style='border: 0px'>");
			/* if successful, display hang-up button */
			if (callme_succeeded($call_status))
			{
				echo("<a href='callme_page.php?action=h'>Click here to hang up.</a>");
			}
			echo("</td></tr></table>");
			echo("<script language='javascript'>parent.document.getElementById('callme_status').innerHTML = '" . _("$call_status") . "';</script>");
			echo("<script language='javascript'>parent.document.getElementById('pb_load_inprogress').value='false';</script>");
	                echo("<script language='javascript'>parent.document.getElementById('callme_status').parentNode.style.backgroundColor = 'white';</script>");
			break;
		case "h":
			/* Hang up. */
			/* Find the channel and hang it up if it still exists. */
			callme_hangup($to);
			echo("<script language='javascript'>parent.document.getElementById('callme_status').innerHTML = '" . _("The call was terminated.") . "';</script>");
			break;
	}
  }
  else {
	echo("Unable to connect to Asterisk Manager Interface");
  }
?>
  </body>
</html>

