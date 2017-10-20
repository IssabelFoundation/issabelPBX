<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

/**
 * @file
 * popup window for playing recording
 */

include_once("crypt.php");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<TITLE>IssabelPBX Recording Review</TITLE>
			<style type="text/css">
				.popup_download {
					color: #105D90; 
					margin: 5px; 
					font-size: 12px; 
					text-align: left;
				}
			</style>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	</head>
	<body>

<?php


  $crypt = new Crypt();

	$REC_CRYPT_PASSWORD = (isset($amp_conf['AMPPLAYKEY']) && trim($amp_conf['AMPPLAYKEY']) != "")?trim($amp_conf['AMPPLAYKEY']):'moufdsuu3nma0';

  $path = $crypt->decrypt($_REQUEST['recordingpath'],$REC_CRYPT_PASSWORD).$_REQUEST['recording'];

  // strip ".." from path for security
  $path = preg_replace('/\.\./','',$path);
	$ufile = basename($path);
  
  // See if the file exists, otherwise check for extensions
  if (is_file("$path.wav")) { $path="$path.wav"; }
  elseif (is_file("$path.Wav")) { $path="$path.Wav"; }
  elseif (is_file("$path.WAV")) { $path="$path.WAV"; }
  elseif (is_file("$path.mp3")) { $path="$path.mp3"; }
  elseif (is_file("$path.gsm")) { $path="$path.gsm"; }
  else {
		echo("<br /><h1 class='popup_download'>".sprintf(_("No compatible wav, mp3 or gsm format found to play:<br /><br />%s"),$ufile)."</h1><br>");
		exit;
	}

  $file = urlencode($crypt->encrypt($path,$REC_CRYPT_PASSWORD));

  if (isset($file)) {
    echo("<br>");
    echo("<embed src='".$_SERVER['PHP_SELF']."?display=recordings&action=audio&recording=$file' width=300, height=20 autoplay=true loop=false></embed><br>");
    echo("<br><h1 class='popup_download'>playing: $ufile</h1><br>");
  }
?>
  </body>
</html>

