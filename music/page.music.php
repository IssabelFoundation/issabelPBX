<?php /* $Id$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$tabindex = 0;
$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$randon = isset($_REQUEST['randon'])?$_REQUEST['randon']:'';
$randoff = isset($_REQUEST['randoff'])?$_REQUEST['randoff']:'';
$category = isset($_REQUEST['category'])?htmlspecialchars(strtr($_REQUEST['category']," ./\"\'\`", "------")):'';
$volume = isset($_REQUEST['volume']) && is_numeric($_REQUEST['volume']) ? $_REQUEST['volume'] : '';

// Determine default path to music directory, old default was mohmp3, now settable
$path_to_moh_dir = $amp_conf['ASTVARLIBDIR'].'/'.$amp_conf['MOHDIR'];

global $display;

if ($category == null) $category = 'default';
$display='music';

global $amp_conf;

if ($category == "default") {
	$path_to_dir = $path_to_moh_dir; //path to directory u want to read.
} else {
	$path_to_dir = $path_to_moh_dir."/$category"; //path to directory u want to read.
}


if (strlen($randon)) {
	touch($path_to_dir."/.random");
	needreload();
}
if (strlen($randoff)) {
	unlink($path_to_dir."/.random");
	needreload();
}
switch ($action) {
	case "addednewstream":
	case "editednewstream":
		$stream = isset($_REQUEST['stream'])?$_REQUEST['stream']:'';
		$format = isset($_REQUEST['format'])?trim($_REQUEST['format']):'';
		if ($format != "") {
			$stream .= "\nformat=$format";
		}
		makestreamcatergory($path_to_dir,$stream);
		needreload();
		redirect_standard();
	case "addednew":
		music_makemusiccategory($path_to_dir);
		needreload();
		redirect_standard();
	break;
	case "addedfile":
		needreload();
//		redirect_standard();
	break;
	case "delete":
		//$fh = fopen("/tmp/music.log","a");
		//fwrite($fh,print_r($_REQUEST,true));
		music_rmdirr("$path_to_dir");
		$path_to_dir = $path_to_moh_dir;
		$category='default';
		needreload();
		redirect_standard();
	break;
}


?>
<div class="rnav"><ul>
    <li><a href="config.php?display=<?php echo urlencode($display)?>&action=add"><?php echo _("Add Music Category")?></a></li>
    <li><a href="config.php?display=<?php echo urlencode($display)?>&action=addstream"><?php echo _("Add Streaming Category")?></a></li>

<?php
//get existing trunk info
$tresults = music_list();
if (isset($tresults)) {
	foreach ($tresults as $tresult) {
		if ($tresult != "none") {
		    ( $tresult == 'default' ? $ttext = _("default") : $ttext = $tresult );
			echo "<li><a id=\"".($category==$tresult ? 'current':'')."\" href=\"config.php?display=".urlencode($display)."&category=".urlencode($tresult)."&action=edit\">{$ttext}</a></li>";
		}
	}
}
?>
</ul></div>


<?php
function makestreamcatergory($path_to_dir,$stream) {
	if (!is_dir($path_to_dir)) {
		music_makemusiccategory($path_to_dir);
	}
	$fh=fopen("$path_to_dir/.custom","w");
	fwrite($fh,$stream);
	fclose($fh);
}

function build_list() {
	global $path_to_dir;
	$pattern = '';
	$handle=opendir($path_to_dir) ;
	$extensions = array('mp3','MP3','wav','WAV'); // list of extensions to match

	//generate the pattern to look for.
	$pattern = '/(\.'.implode('|\.',$extensions).')$/i';

	//store file names that match pattern in an array
	$i = 0;
	while (($file = readdir($handle))!==false) {
		if ($file != "." && $file != "..") {
			if(preg_match($pattern,$file)) {
				$file_array[$i] = $file; //pattern is matched store it in file_array.
				$i++;
			}
		}
	}
	closedir($handle);

	return (isset($file_array))?$file_array:null;  //return the size of the array
}

function draw_list($file_array, $path_to_dir, $category) {
	global $display;
	//list existing mp3s and provide delete buttons
	if ($file_array) {
		foreach ($file_array as $thisfile) {
			print "<div style=\"text-align:right;width:550px;border: 1px solid;padding:2px;\">";
			print "<b style=\"float:left;margin-left:5px;\" >".$thisfile."</b>";

			$delURL = $_SERVER['SCRIPT_NAME']."?display=".(isset($display)?$display:'')."&del=".urlencode($thisfile)."&category=".$category;
			$tlabel = _("Delete");
			$label = '<span><img width="16" height="16" border="0" title="'.$tlabel.'" alt="'.$tlabel.'" src="images/core_delete.png"/>&nbsp;</span>';
			echo "<a style=\"margin-right:5px;\" href=".$delURL.">".$label."</a></div><br />";
		}
	}
}

function process_mohfile($mohfile,$onlywav=false,$volume=false) {
	global $path_to_dir;
	global $amp_conf;

	$output = 0;
	$returncode = 0;
  $mohfile = escapeshellcmd($mohfile);
	$origmohfile=$path_to_dir."/orig_".$mohfile;
	if ($amp_conf['AMPMPG123']) {
		if($onlywav) {
			$newname = substr($mohfile,0,strrpos($mohfile,"."));

			// If we are dealing with an MP3, we need to decode it to a wav file. mpg123 -w writes the converted output to $origmohfile.wav
			if (strtoupper(substr($origmohfile,-4)) == '.MP3') {
				$mpg123cmd = "mpg123 -w \"".substr($origmohfile,0,strrpos($origmohfile,".")).".wav\" \"".$origmohfile."\" 2>&1 ";
				exec($mpg123cmd, $output, $returncode);
			}
			$newmohfile = $path_to_dir."/wav_".$newname.".wav";
			//We need to take the output of mpg123 to use in the sox conversion. If we used $origmohfile directly then we would be bypassing mpg123. The mpg123 might not be needed on some systems if we had the sox version with mp3 compiled in. The standard rpmforge sox rpm does not have mp3 included.
			//$soxcmd = "sox \"".$origmohfile."\"";
      $source_file = substr($origmohfile,0,strrpos($origmohfile,".")).".wav";
			$soxcmd = "sox \"".$source_file."\"";
			$soxcmd .= " -r 8000 -c 1 \"".$newmohfile."\"";
			if($volume){
				$soxcmd .= " vol ".$volume;
			}
			$soxresample = " rate -ql ";
			exec($soxcmd.$soxresample."2>&1", $output, $returncode);
			if ($returncode != 0) {
				// try it again without the resample in case the input sample rate was the same
				//
        $output = array();
        $returncode = 0;
				exec("rm -rf \"".$newmohfile."\"");
				exec($soxcmd."2>&1", $output, $returncode);
        // if sox prints no warnings, then despite the return code we will assume it is good
        if (empty($output)) {
          if (copy($source_file,$newmohfile)) {
            $returncode = 0;
          } else {
            $returncode = 1;
            $output[] = _("sox failed to convert file and original could not be copied as a fall back");
          }
        }
			}
		}
	} else { // AMPMPG123
		$newname = strtr($mohfile,"&", "_");
		if(strstr($newname,".mp3")) {
			$onlywav = false;
		}

		if(!$onlywav) {
			$newmohfile=$path_to_dir."/". ((strpos($newname,'.mp3') === false) ? $newname.".mp3" : $newname);
			$lamecmd="lame --cbr -m m -t -F \"".$origmohfile."\" \"".$newmohfile."\" 2>&1 ";
			if (strpos($newmohfile,'.mp3') !== false) {
				exec($lamecmd, $output, $returncode);
			}
		} else {
			$newmohfile = $path_to_dir."/wav_".$newname;
			$soxcmd = "sox \"".$origmohfile."\" -r 8000 -c 1 \"".$newmohfile."\" ";
			$soxresample = "rate -ql ";
			exec($soxcmd.$soxresample."2>&1", $output, $returncode);
			if ($returncode != 0) {
				// try it again without the resample in case the input sample rate was the same
				//
				exec("rm -rf \"".$newmohfile."\"");
				exec($soxcmd."2>&1", $output, $returncode);
			}
		}
	} // AMPMPG123

	if ($returncode != 0) {
		return join("<br>\n", $output);
	}
	$rmcmd="rm -f \"". $origmohfile."\"";
	exec($rmcmd);
	if ($amp_conf['AMPMPG123']) {
		// If this started as an mp3, we converted it to a wav and then transcoded it from there,
		// so we have two "original" files to delete
		//
		if (strpos($origmohfile,'.mp3') | strpos($origmohfile,'.MP3') !== false)  {
			$rmcmd="rm -f \"". substr($origmohfile,0,strrpos($origmohfile,".")).".wav\"";
			exec($rmcmd);
		}
	} // AMPMPG123
	return null;
}

?>

<h2><?php echo _("On Hold Music")?></h2>

<?php
if ($action == 'add') {
	?>
	<form name="addcategory" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return addcategory_onsubmit();">
	<input type="hidden" name="display" value="<?php echo $display?>">
	<input type="hidden" name="action" value="addednew">
	<table>
	<tr><td colspan="2"><h5><?php echo _("Add Music Category")?><hr></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Category Name:")?><span><?php echo _("Allows you to Set up Different Categories for music on hold.  This is useful if you would like to specify different Hold Music or Commercials for various ACD Queues.")?> </span></a></td>
		<td><input type="text" name="category" value=""></td>
	</tr>
	<tr>
		<td colspan="2"><br><h6><input name="Submit" type="submit" value='<?php echo _("Submit Changes")?>' ></h6></td>
	</tr>
	</table>
<script language="javascript">
<!--

var theForm = document.addcategory;
theForm.category.focus();

function addcategory_onsubmit() {
	var msgInvalidCategoryName = "<?php echo _('Please enter a valid Category Name'); ?>";
	var msgReservedCategoryName = "<?php echo _('Categories: \"none\" and \"default\" are reserved names. Please enter a different name'); ?>";

	defaultEmptyOK = false;
	if (!isAlphanumeric(theForm.category.value))
		return warnInvalid(theForm.category, msgInvalidCategoryName);
	if (theForm.category.value == "default" || theForm.category.value == "none" || theForm.category.value == ".nomusic_reserved")
		return warnInvalid(theForm.category, msgReservedCategoryName);

	return true;
}

//-->
</script>

	</form>
	<br><br><br><br><br>

<?php
	} else if ($action == 'addstream') {
	?>
	<form name="addstream" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return addstream_onsubmit();">
	<input type="hidden" name="display" value="<?php echo $display?>">
	<input type="hidden" name="action" value="addednewstream">
	<table>
	<tr><td colspan="2"><h5><?php echo _("Add Streaming Category")?><hr></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Category Name:")?><span><?php echo _("Allows you to Set up Different Categories for music on hold.  This is useful if you would like to specify different Hold Music or Commercials for various ACD Queues.")?> </span></a></td>
		<td><input type="text" name="category" value=""></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Application:")?><span><?php echo _("This is the \"application=\" line used to provide the streaming details to Asterisk. See information on musiconhold.conf configuration for different audio and Internet streaming source options.")?> </span></a></td>
		<td><input type="text" name="stream" size="80" value=""></td>
	</tr>
	<tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Optional Format:")?><span><?php echo _("Optional value for \"format=\" line used to provide the format to Asterisk. This should be a format understood by Asterisk such as ulaw, and is specific to the streaming application you are using. See information on musiconhold.conf configuration for different audio and Internet streaming source options.")?> </span></a></td>
		<td><input type="text" name="format" size="6" value=""></td>
	</tr>
	<tr>
		<td colspan="2"><br><h6><input name="Submit" type="submit" value='<?php echo _("Submit Changes")?>' ></h6></td>
	</tr>
	</table>
<script language="javascript">
<!--

var theForm = document.addstream;
theForm.category.focus();

function addstream_onsubmit() {
	var msgInvalidCategoryName = "<?php echo _('Please enter a valid Category Name'); ?>";
	var msgInvalidStreamName = "<?php echo _('Please enter a streaming application command and arguments'); ?>";
	var msgReservedCategoryName = "<?php echo _('Categories: \"none\" and \"default\" are reserved names. Please enter a different name'); ?>";

	defaultEmptyOK = false;
	if (!isAlphanumeric(theForm.category.value))
		return warnInvalid(theForm.category, msgInvalidCategoryName);
	if (theForm.category.value == "default" || theForm.category.value == "none" || theForm.category.value == ".nomusic_reserved")
		return warnInvalid(theForm.category, msgReservedCategoryName);
	if (isEmpty(theForm.stream.value))
		return warnInvalid(theForm.stream, msgInvalidStreamName);

	return true;
}

//-->
</script>

	</form>
	<br><br><br><br><br>

<?php
} else {
?>

	<h5><?php echo _("Category:")?> <?php echo $category=="default"?_("default"):$category;?></h5>
<?php
	if (file_exists("{$path_to_dir}/.custom")) {
		$application = file_get_contents("{$path_to_dir}/.custom");
		$application = explode("\n",$application);
		if (isset($application[1])) {
			$format = explode('=',$application[1],2);
			$format = $format[1];
		} else {
			$format = "";
		}
	} else {
		$application = false;
	}
	if ($category!="default") {
		$delURL = $_SERVER['PHP_SELF'].'?display='.urlencode($display).'&action=delete&category='.urlencode($category);
		$tlabel = sprintf(($application === false)?_("Delete Music Category %s"):_("Delete Streaming Category"),$category);
		$label = '<span><img width="16" height="16" border="0" title="'.$tlabel.'" alt="" src="images/core_delete.png"/>&nbsp;'.$tlabel.'</span>';
?>
		<p><a href="<?php echo $delURL ?>"><?php echo $label; ?></a></p>
<?php
	}
	if ($application !== false) {
	?>
		<form name="editstream" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return editstream_onsubmit();">
		<input type="hidden" name="display" value="<?php echo $display?>">
		<input type="hidden" name="action" value="editednewstream">
		<table>
		<tr><td colspan="2"><h5><?php echo _("Edit Streaming Category").": $category"?><hr></h5></td></tr>
		<tr>
			<td><a href="#" class="info"><?php echo _("Application:")?><span><?php echo _("This is the \"application=\" line used to provide the streaming details to Asterisk. See information on musiconhold.conf configuration for different audio and Internet streaming source options.")?> </span></a></td>
			<td><input type="text" name="stream" size="80" value="<?php echo $application[0]?>"></td>
		</tr>
		<tr>
			<td><a href="#" class="info"><?php echo _("Optional Format:")?><span><?php echo _("Optional value for \"format=\" line used to provide the format to Asterisk. This should be a format understood by Asterisk such as ulaw, and is specific to the streaming application you are using. See information on musiconhold.conf configuration for different audio and Internet streaming source options.")?> </span></a></td>
			<td><input type="text" name="format" size="6" value="<?php echo htmlentities($format)?>"></td>
		</tr>
		<tr>
			<td colspan="2"><br><h6><input name="Submit" type="submit" value='<?php echo _("Submit Changes")?>' ></h6></td>
		</tr>
		</table>
<script language="javascript">
<!--

var theForm = document.editstream;
theForm.stream.focus();

function editstream_onsubmit() {
	var msgInvalidStreamName = "<?php echo _('Please enter a streaming application command and arguments'); ?>";

	defaultEmptyOK = false;
	if (isEmpty(theForm.stream.value))
		return warnInvalid(theForm.stream, msgInvalidStreamName);

	return true;
}
//-->
</script>

		</form>
		<br><br><br><br><br>

<?php
	} else { // normal moh dir
?>

	<form enctype="multipart/form-data" name="upload" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
		<?php echo _("Upload a .wav or .mp3 file:")?><br>
		<input type="hidden" name="display" value="<?php echo $display?>">
		<input type="hidden" name="category" value="<?php echo "$category" ?>">
		<input type="hidden" name="action" value="addedfile">
		<input type="file" name="mohfile" tabindex="<?php echo ++$tabindex;?>"/>
		<input type="button" value="<?php echo _("Upload")?>" onclick="document.upload.submit(upload);alert('<?php echo addslashes(_("Please wait until the page loads. Your file is being processed."))?>');" tabindex="<?php echo ++$tabindex;?>"/>
		<br />
<?php
	if ($amp_conf['AMPMPG123']) {
?>
		<select name="volume" tabindex="<?php echo ++$tabindex;?>">
			<option value="1.50"><?php echo _("Volume 150%")?></option>
			<option value="1.25"><?php echo _("Volume 125%")?></option>
			<option value="" selected><?php echo _("Volume 100%")?></option>
			<option value=".75"><?php echo _("Volume 75%")?></option>
			<option value=".5"><?php echo _("Volume 50%")?></option>
			<option value=".25"><?php echo _("Volume 25%")?></option>
			<option value=".1"><?php echo _("Volume 10%")?></option>
		</select>
		<a href="#" class="info"><?php echo "&nbsp;"._("Volume Adjustment")?><span> <?php echo _("The volume adjustment is a linear value. Since loudness is logarithmic, the linear level will be less of an adjustment. You should test out the installed music to assure it is at the correct volume. This feature will convert MP3 files to WAV files. If you do not have mpg123 installed, you can set the parameter: <strong>Convert Music Files to WAV</strong> to false in Advanced Settings") ?></span></a>
<?php
	} else { // AMPMPG123
?>
		<input type="checkbox" name="onlywav" checked="checked"><small><?php echo _("Do not encode wav to mp3"); ?></small>
<?php
	} // AMPMPG123
?>
	</form>
	<br />
	<form name="randomon" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
	<?php
		if (file_exists("{$path_to_dir}/.random")) {
			?> <input type="submit" name="randoff" value="<?php echo _("Disable Random Play");?>"> <?php
		} else {
			?> <input type="submit" name="randon" value="<?php echo _("Enable Random Play");?>"> <?php
		}
	?>
	</form>
	<br />
	<?php

	// Check to see if the upload failed for some reason
	if (isset($_FILES['mohfile']['name']) && !is_uploaded_file($_FILES['mohfile']['tmp_name'])) {
		if (strlen($_FILES['mohfile']['name']) == 0) {
			echo "<h5> PHP "._("Error Processing")."! "._("No file provided")." "._("Please select a file to upload")."</h5>";
		} else {
			echo "<h5> PHP "._("Error Processing")." ".htmlentities($_FILES['mohfile']['name'])."! "._("Check")." upload_max_filesize "._("in")." /etc/php.ini</h5>";
		}
	}
	if (isset($_FILES['mohfile']['tmp_name']) && is_uploaded_file($_FILES['mohfile']['tmp_name'])) {
		//echo $_FILES['mohfile']['name']." uploaded OK";
		move_uploaded_file($_FILES['mohfile']['tmp_name'], $path_to_dir."/orig_".$_FILES['mohfile']['name']);

		if ($amp_conf['AMPMPG123']) {
			$process_err = process_mohfile($_FILES['mohfile']['name'],true,$volume);
		} else {
			$process_err = process_mohfile($_FILES['mohfile']['name'],($_REQUEST['onlywav'] != ''));
		}

		if (isset($process_err)) {
			echo "<h5>"._("Error Processing").": \"$process_err\" for ".htmlentities($_FILES['mohfile']['name'])."!</h5>\n";
			echo "<h5>"._("This is not a fatal error, your Music on Hold may still work.")."</h5>\n";
		} else {
			echo "<h5>"._("Completed processing")." ".htmlentities($_FILES['mohfile']['name'])."!</h5>";
		}
		needreload();
	}

	//build the array of files
	$file_array = build_list();
	$numf = count($file_array);
	} // normal moh dir


	if (isset($_REQUEST['del'])) {
		$del = $_REQUEST['del'];
		if (strpos($del, "\"") || strpos($del, "\'") || strpos($del, "\;")) {
			print "You're trying to use an invalid character. Please don't.\n";
			exit;
		}
		if (($numf == 1) && ($category == "default") ){
			echo "<h5>"._("You must have at least one file for On Hold Music.  Please upload one before deleting this one.")."</h5>";
		} else {
			if (@unlink($path_to_dir."/".$del)) {
				echo "<h5>"._("Deleted")." ".$del."!</h5>";
			} else {
				echo "<h5>".sprintf(_("Error Deleting %s"),$del)."!</h5>";
			}
			//kill_mpg123();
			needreload();
		}
	}
	if ($application === false) {
		$file_array = build_list();
		draw_list($file_array, $path_to_dir, $category);
	}
	?>
	<br><br><br><br><br><br>
<?php
}
?>
