<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

class music_conf {

	// return the filename to write
	function get_filename() {
		return "musiconhold_additional.conf";
	}
	// return the output that goes in the file
	function generateConf() {
		global $amp_conf;
		global $version; //asterisk version
		$path_to_moh_dir = $amp_conf['ASTVARLIBDIR'].'/'.$amp_conf['MOHDIR'];
		$output = "";

		$File_Write="";
		$tresults = music_list();
		if (isset($tresults)) {

			if (version_compare($version, "1.6.0", "ge")) {
				$random = "sort=random\n";
				$alpha = "sort=alpha\n";
			} else {
				$random = "random=yes\n";
				$alpha = "";
			}

			foreach ($tresults as $tresult)  {
				// hack - but his is all a hack until redone, in functions, etc.
				// this puts a none category to allow no music to be chosen
				//
				if ($tresult == "none") {
					$dir = $path_to_moh_dir."/.nomusic_reserved";
					if (!is_dir($dir)) {
						music_makemusiccategory($dir);
					}
					touch($dir."/silence.wav");
				} elseif ($tresult != "default" ) {
					$dir = $path_to_moh_dir."/{$tresult}/";
				} else {
					$dir = $path_to_moh_dir.'/';
				}
				if (file_exists("{$dir}.custom")) {
					$application = file_get_contents("{$dir}.custom");
					$File_Write.="[{$tresult}]\nmode=custom\napplication=$application\n";
				} else if (file_exists("{$dir}.random")) {
					$File_Write.="[{$tresult}]\nmode=files\ndirectory={$dir}\n$random";
				} else {
					$File_Write.="[{$tresult}]\nmode=files\ndirectory={$dir}\n$alpha";
				}
			}
		}
		return $File_Write;
	}
}

function music_makemusiccategory($path_to_dir) {
	mkdir("$path_to_dir", 0755); 
}
 
function music_list($path=null) {
  if ($path === null) {
    global $amp_conf;
    // to get through possible upgrade gltiches, check if set
    if (!isset($amp_conf['MOHDIR'])) {
      $amp_conf['MOHDIR'] = '/mohmp3';
    }
    $path = $amp_conf['ASTVARLIBDIR'].'/'.$amp_conf['MOHDIR'];
  }
	$i = 1;
	$arraycount = 0;
	$filearray = Array("default");

	if (is_dir($path)){
		if ($handle = opendir($path)){
			while (false !== ($file = readdir($handle))){ 
				if ( ($file != ".") && ($file != "..") && ($file != "CVS") && ($file != ".svn") && ($file != ".nomusic_reserved" ) )
				{
					if (is_dir("$path/$file"))
						$filearray[($i++)] = "$file";
				}
			}
		closedir($handle); 
		}
	}
	if (isset($filearray)) {
		sort($filearray);
		// add a none categoy for no music
		if (!in_array("none",$filearray)) {
			$filearray[($i++)] = "none";
		}
		return ($filearray);
	} else {
		return null;
	}
}

function music_rmdirr($dirname)
{
	// Sanity check
	if (!file_exists($dirname)) {
		print "$dirname Doesn't exist\n";
		return false;
	}
 
	// Simple delete for a file
	if (is_file($dirname)) {
		return unlink($dirname);
	}
 
	// Loop through the folder
	$dir = dir($dirname);
	while (false !== $entry = $dir->read()) {
		// Skip pointers
		if ($entry == '.' || $entry == '..') {
			continue;
		}
 
		// Recurse
		music_rmdirr("$dirname/$entry");
	}
 
	// Clean up
	$dir->close();
	return rmdir($dirname);
}

?>
