<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
global $asterisk_conf;
global $amp_conf;
require_once(dirname(__FILE__).'/functions.inc.php');

// In case there is an old version as part of the upgrade process, we will derive the current path
//
$moh_subdir = isset($amp_conf['MOHDIR']) ? trim(trim($amp_conf['MOHDIR']),'/') : 'mohmp3';
$path_to_moh_dir = $amp_conf['ASTVARLIBDIR']."/$moh_subdir";

$File_Write="";
$tresults = music_list($path_to_moh_dir);
if (isset($tresults)) {
	foreach ($tresults as $tresult)  {
		if ($tresult == "default" ) {
			$dir = $path_to_moh_dir;
		} elseif ($tresult == "none") {
      $dir = $path_to_moh_dir."/.nomusic_reserved";
      if (!is_dir($dir)) {
        mkdir("$dir", 0755,true); 
      }
      touch($dir."/silence.wav");
    } else {
      $dir = $path_to_moh_dir."/{$tresult}/";
		}
		if (file_exists("{$dir}.random")) {
			$File_Write.="[{$tresult}]\nmode=files\ndirectory={$dir}\nrandom=yes\n";
		} else {
			$File_Write.="[{$tresult}]\nmode=files\ndirectory={$dir}\n";
		}
	}
}
$handle = fopen($amp_conf['ASTETCDIR']."/musiconhold_additional.conf", "w");

if (fwrite($handle, $File_Write) === FALSE) {
	echo _("Cannot write to file")." ($tmpfname)";
	exit;
}

fclose($handle);


$issabelpbx_conf =& issabelpbx_conf::create();

  // AMPMPG123
  //
  $set['value'] = true;
  $set['defaultval'] =& $set['value'];
  $set['readonly'] = 0;
  $set['hidden'] = 0;
  $set['level'] = 3;
  $set['module'] = 'music';
  $set['category'] = 'System Setup';
  $set['emptyok'] = 0;
  $set['name'] = 'Convert Music Files to WAV';
  $set['description'] = 'When set to false, the MP3 files can be loaded and WAV files converted to MP3 in the MoH module. The default behavior of true assumes you have mpg123 loaded as well as sox and will convert MP3 files to WAV. This is highly recommended as MP3 files heavily tax the system and can cause instability on a busy phone system';
  $set['type'] = CONF_TYPE_BOOL;
  $issabelpbx_conf->define_conf_setting('AMPMPG123',$set,true);

needreload();
