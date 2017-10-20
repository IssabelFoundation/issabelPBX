<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

/**
 * @file
 * plays recording file
 */

if (isset($_REQUEST['recording'])) {

  include_once("crypt.php");

	$REC_CRYPT_PASSWORD = (isset($amp_conf['AMPPLAYKEY']) && trim($amp_conf['AMPPLAYKEY']) != "")?trim($amp_conf['AMPPLAYKEY']):'moufdsuu3nma0';

  $crypt = new Crypt();

  $opath = $_REQUEST['recording'];
  $path = $crypt->decrypt($opath,$REC_CRYPT_PASSWORD);

  // Gather relevent info about file
  $size = filesize($path);
  $name = basename($path);
  $extension = strtolower(substr(strrchr($name,"."),1));

  // This will set the Content-Type to the appropriate setting for the file
  $ctype ='';
  switch( $extension ) {
    case "mp3": $ctype="audio/mpeg"; break;
    case "wav": $ctype="audio/x-wav"; break;
    case "Wav": $ctype="audio/x-wav"; break;
    case "WAV": $ctype="audio/x-wav"; break;
    case "gsm": $ctype="audio/x-gsm"; break;

    // not downloadable
    default: die_issabelpbx("<b>404 File not found! foo</b>"); break ;
  }

  // need to check if file is mislabeled or a liar.
  $fp=fopen($path, "rb");
  if ($size && $ctype && $fp) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: wav file");
    header("Content-Type: " . $ctype);
    header("Content-Disposition: attachment; filename=" . $name);
    header("Content-Transfer-Encoding: binary");
    header("Content-length: " . $size);
    fpassthru($fp);
  } 
}

?>
