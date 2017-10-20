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
    <TITLE>CDR Viewer</TITLE>
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

	$REC_CRYPT_PASSWORD = (isset($amp_conf['AMPPLAYKEY']) && trim($amp_conf['AMPPLAYKEY']) != "")?trim($amp_conf['AMPPLAYKEY']):'TheWindCriesMary';
	$path = $crypt->decrypt($_REQUEST['recordingpath'],$REC_CRYPT_PASSWORD);
	$file = urlencode($crypt->encrypt($path,$REC_CRYPT_PASSWORD));
	if (isset($file)) {
		echo("<embed width='100%' type='audio/basic' src='config.php?skip_astman=1&quietmode=1&handler=file&module=cdr&file=cdr_audio.php&cdr_file=" .$file. "' width=300, height=25 autoplay=true loop=false></embed><br>");
	}
?>
  </body>
</html>
