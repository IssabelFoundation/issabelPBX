<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

$dispnum = 'vmblast'; //used for switch on config.php

$action         = isset($_REQUEST['action'])        ? $_REQUEST['action']      : '';

//the extension we are currently displaying
$account        = isset($_REQUEST['account'])       ? $_REQUEST['account']     : '';
$extdisplay     = isset($_REQUEST['extdisplay'])    ? ltrim($_REQUEST['extdisplay'],'GRP-')  : (($account != '')?$account:'');
$description    = isset($_REQUEST['description'])   ? $_REQUEST['description'] : '';
$audio_label    = isset($_REQUEST['audio_label'])   ? $_REQUEST['audio_label'] : -1;
$password       = isset($_REQUEST['password'])      ? $_REQUEST['password']    : '';
$default_group  = isset($_REQUEST['default_group']) ? $_REQUEST['default_group'] : '0';
$vmblast_list   = isset($_REQUEST['vmblast_list'])  ? $_REQUEST['vmblast_list']  : '';

// do if we are submitting a form
if(isset($_REQUEST['action'])){
	//check if the extension is within range for this user
	if (isset($account) && !checkRange($account)){
		echo "<script>javascript:alert('". _("Warning! Extension")." ".$account." "._("is not allowed for your account").".');</script>";
	} else {
		//add group
		if ($action == 'addGRP') {

			$conflict_url = array();
			$usage_arr = framework_check_extension_usage($account);
			if (!empty($usage_arr)) {
				$conflict_url = framework_display_extension_usage_alert($usage_arr);
			} else if (vmblast_add($account,$vmblast_list,$description,$audio_label,$password,$default_group)) {
				$_REQUEST['action'] = 'delGRP';
				needreload();
				redirect_standard('extdisplay');
			}
		}

		//del group
		if ($action == 'delGRP') {
			vmblast_del($account);
			needreload();
			redirect_standard();
		}

		//edit group - just delete and then re-add the extension
		if ($action == 'editGRP') {
			vmblast_del($account);
			vmblast_add($account,$vmblast_list,$description,$audio_label,$password,$default_group);
			needreload();
			redirect_standard('extdisplay');
		}
	}
}
?>

<div class="rnav"><ul>
    <li><a id="<?php  echo ($extdisplay=='' ? 'current':'') ?>" href="config.php?display=<?php echo urlencode($dispnum)?>"><?php echo _("Add VMBlast Group")?></a></li> <?php
//get unique ring groups
$gresults = vmblast_list();
$default_grp = vmblast_get_default_grp();

if (isset($gresults)) {
	foreach ($gresults as $gresult) {
    $hl = $gresult[0] == $default_grp ? _(' [DEFAULT]') : '';
		echo "<li><a class=\"".($extdisplay==$gresult[0] ? 'current':'')."\" href=\"config.php?display=".urlencode($dispnum)."&extdisplay=".urlencode("GRP-".$gresult[0])."\">".$gresult[1]." ({$gresult[0]})$hl</a></li>";
	}
}
?>
</ul></div>
<?php
if ($action == 'delGRP') {
	echo '<br><h3>'._("VMBlast Group").' '.$account.' '._("deleted").'!</h3><br><br><br><br><br><br><br><br>';
} else {
	if ($extdisplay != '') {
		// We need to populate grplist with the existing extension list.
		$thisgrp = vmblast_get($extdisplay);
		$grplist     = $thisgrp['grplist'];
		$description = $thisgrp['description'];
		$audio_label = $thisgrp['audio_label'];
		$password    = $thisgrp['password'];
		$default_group = $thisgrp['default_group'];
		unset($thisgrp);

		$delButton = "
			<form name=delete action=\"{$_SERVER['PHP_SELF']}\" method=POST>
				<input type=\"hidden\" name=\"display\" value=\"{$dispnum}\">
				<input type=\"hidden\" name=\"account\" value=\"".$extdisplay."\">
				<input type=\"hidden\" name=\"action\" value=\"delGRP\">
				<input type=submit value=\""._("Delete Group")."\">
			</form>";

		echo "<h2>"._("VMBlast Group").": ".$extdisplay."</h2>";
		echo "<p>".$delButton."</p>";

		$usage_list = framework_display_destination_usage(vmblast_getdest($extdisplay));
		if (!empty($usage_list)) {
		?>
			<a href="#" class="info"><?php echo $usage_list['text']?>:<span><?php echo $usage_list['tooltip']?></span></a>
		<?php
		}

	} else {
		$grplist = array();
		$strategy = '';
		$ringing = '';

		if (!empty($conflict_url)) {
			echo "<h5>"._("Conflicting Extensions")."</h5>";
			echo implode('<br .>',$conflict_url);
		}
		echo "<h2>"._("Add VMBlast Group")."</h2>";
	}
	?>
			<form name="editGRP" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkGRP(editGRP);">
			<input type="hidden" name="display" value="<?php echo $dispnum?>">
			<input type="hidden" name="action" value="<?php echo ($extdisplay != '' ? 'editGRP' : 'addGRP'); ?>">
			<table>
			<tr>
				<td colspan="2"><h5><?php  echo ($extdisplay != '' ? _("Edit VMBlast Group") : _("Add VMBlast Group")) ?><hr></h5>
				</td>
			</tr>
			<tr>
<?php
				if ($extdisplay != '') {

?>
				<input size="5" type="hidden" name="account" value="<?php  echo $extdisplay; ?>" tabindex="<?php echo ++$tabindex;?>">
<?php 	} else { ?>
				<td><a href="#" class="info"><?php echo _("VMBlast Number")?>:<span><?php echo _("The number users will dial to voicemail boxes in this VMBlast group")?></span></a></td>
				<td><input size="5" type="text" name="account" data-extdisplay="" value="<?php  if ($gresult[0]==0) { echo "500"; } else { echo $gresult[0] + 1; } ?>" tabindex="<?php echo ++$tabindex;?>"></td>
<?php 		} ?>
			</tr>

			<tr>
				<td> <a href="#" class="info"><?php echo _("Group Description:")?>:<span><?php echo _("Provide a descriptive title for this VMBlast Group.")?></span></a></td>
				<td><input size="20" maxlength="35" type="text" name="description" value="<?php echo htmlspecialchars($description); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>

<?php if(function_exists('recordings_list')) { //only include if recordings is enabled?>
			<tr>
				<td><a href="#" class="info"><?php echo _("Audio Label:")?><span><?php echo _("Play this message to the caller so they can confirm they have dialed the proper voice mail group number, or have the system simply read the group number.")?></span></a></td>
				<td>
					<select name="audio_label" tabindex="<?php echo ++$tabindex;?>">
					<?php
						$tresults = recordings_list();
						$default = (isset($audio_label) ? $audio_label : -1);
						echo '<option value="-1">'._("Read Group Number")."</option>";
						echo '<option value="-2"'.(($default == -2) ? ' SELECTED':'').'>'._("Beep Only - No Confirmation")."</option>";
						if (isset($tresults[0])) {
							foreach ($tresults as $tresult) {
								echo '<option value="'.$tresult[0].'"'.($tresult[0] == $default ? ' SELECTED' : '').'>'.$tresult[1]."</option>\n";
							}
						}
					?>
					</select>
				</td>
			</tr>
<?php }	else { ?>
			<tr>
				<td><a href="#" class="info"><?php echo _("Audio Label:")?><span><?php echo _("The group number will be played to the caller so they can confirm they have dialed the proper voice mail group number.<br><br>You must install and enable the \"Systems Recordings\" Module to edit this option and choose from recordings.")?></span></a></td>
				<td>
					<?php
						$default = (isset($audio_label) ? $audio_label : -1);
					?>
					<input type="hidden" name="audio_label" value="<?php echo $default; ?>"><?php echo ($default != -1 ? $default : _('Read Group Number')); ?>
				</td>
			</tr>
<?php }
?>
			<tr>
				<td><a href="#" class="info"><?php echo _("Optional Password")?>:<span><?php echo _('You can optionally include a password to authenticate before providing access to this group voicemail list.')?></span></a></td>
				<td><input size="12" type="text" name="password" value="<?php  echo $password ?>" tabindex="<?php echo ++$tabindex;?>">
				</td>
			</tr>

			<tr>
				<td valign='top'><a href='#' class='info'><?php echo _("Voicemail Box List:")."<span><br>"._("Select voice mail boxes to add to this group. Use Ctrl key to select multiple..") ?>
	<br><br></span></a>
				</td>
				<td valign="top">
					<select multiple="multiple" name="vmblast_list[]" id="xtnlist"  tabindex="<?php echo ++$tabindex;?>">
						<?php
						$results = core_users_list();
						if (!is_array($results)) $results = array();
						foreach ($results as $result) {
							if ($result[2] != 'novm') {
								echo '<option value="'.$result[0].'" ';
								if (array_search($result[0], $grplist) !== false) echo ' selected="selected" ';
								echo '>'.$result[0].' ('.$result[1].')</option>';
							}
						}
						?>
					</select>
				<br>
				</td>
			</tr>

			<tr>
				<td>
					<a href='#' class='info'><?php echo _("Default VMBlast Group") ?>
						<span> <?php echo _("Each PBX system can have a single Default Voicemail Blast Group. If specified, extensions can be automatically added (or removed) from this default group in the Extensions (or Users) tab.<br />Making this group the default will uncheck the option from the current default group if specified.") ?> </span>
					</a>
				</td>
				<td>
					<input type='checkbox' name='default_group' id="default_group" value='1' <?php if ($default_group) { echo 'CHECKED'; } ?> tabindex="<?php echo ++$tabindex;?>">
				</td>
			</tr>

<?php
			// implementation of module hook
			// object was initialized in config.php
			echo $module_hook->hookHtml;
?>
			<tr>
			<td colspan="2"><br><h6><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>"></h6></td>

			</tr>
			</table>
			</form>
<?php
		} //end if action == delGRP


?>
<script language="javascript">
<!--

function checkGRP(theForm) {
	var msgInvalidGrpNum = "<?php echo _('Invalid Group Number specified'); ?>";
	var msgInvalidGrpNumStartWithZero = "<?php echo _('Group numbers with more than one digit cannot begin with 0'); ?>";
	var msgInvalidExtList = "<?php echo _('Please enter an extension list.'); ?>";
	var msgInvalidDescription = "<?php echo _('Please enter a valid Group Description'); ?>";
	var msgInvalidPassword = "<?php echo _('Please enter a valid numeric password, only numbers are allowed'); ?>";
	var msgInvalidExtList = "<?php echo _('Please select at least one extension'); ?>";

	// form validation
	defaultEmptyOK = false;
	if (!isInteger(theForm.account.value)) {
		return warnInvalid(theForm.account, msgInvalidGrpNum);
	} else if (theForm.account.value.indexOf('0') == 0 && theForm.account.value.length > 1) {
		return warnInvalid(theForm.account, msgInvalidGrpNumStartWithZero);
	}

	defaultEmptyOK = true;
	if (!isInteger(theForm.password.value))
		return warnInvalid(theForm.password, msgInvalidPassword);

	defaultEmptyOK = false;

	<?php if (function_exists('module_get_field_size')) { ?>
		var sizeDisplayName = "<?php echo module_get_field_size('vmblast', 'description', 35); ?>";
		if (!isCorrectLength(theForm.description.value, sizeDisplayName))
			return warnInvalid(theForm.description, "<?php echo _('The Group Description provided is too long.'); ?>")
	<?php } ?>

	if (!isAlphanumeric(theForm.description.value))
		return warnInvalid(theForm.description, msgInvalidDescription);

	var selected = 0;
	for (var i=0; i < theForm.xtnlist.options.length; i++) {
		if (theForm.xtnlist.options[i].selected) selected += 1;
	}
	if (selected < 1) {
    theForm.xtnlist.focus();
		alert(msgInvalidExtList);
		return false;
	}

	return true;
}

//-->
</script>
