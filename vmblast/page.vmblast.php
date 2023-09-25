<?php
// Copyright 2022 Issabel Foundation
//
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
$tabindex = 0;
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
		echo "<script>javascript:sweet_alert('". __("Warning! Extension")." ".$account." ".__("is not allowed for your account").".');</script>";
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
                $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been added'));
                $_SESSION['msgtype']='success';
                $_SESSION['msgtstamp']=time();
				redirect_standard('');
			}
		}

		//del group
		if ($action == 'delete') {
			vmblast_del($account);
			needreload();
            $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been deleted'));
            $_SESSION['msgtype']='warning';
            $_SESSION['msgtstamp']=time();
			redirect_standard();
		}

		//edit group - just delete and then re-add the extension
		if ($action == 'editGRP') {
			vmblast_del($account);
			vmblast_add($account,$vmblast_list,$description,$audio_label,$password,$default_group);
			needreload();
            $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
            $_SESSION['msgtype']='success';
            $_SESSION['msgtstamp']=time();
			redirect_standard('extdisplay');
		}
	}
}


$rnaventries = array();
$gresults    = vmblast_list();
$default_grp = vmblast_get_default_grp();
foreach ($gresults as $gresult) {
    $hl = $gresult[0] == $default_grp ? __(' [DEFAULT]') : '';
    $rnaventries[] = array($gresult[0],$gresult[1].$hl,$gresult[0]);
}
drawListMenu($rnaventries, $type, $display, $extdisplay);
?>

<!--div class="rnav"><ul>
    <li><a id="<?php  echo ($extdisplay=='' ? 'current':'') ?>" href="config.php?display=<?php echo urlencode($dispnum)?>"><?php echo __("Add VMBlast Group")?></a></li> <?php
//get unique ring groups
$gresults = vmblast_list();
$default_grp = vmblast_get_default_grp();
$gresult = array();

if(count($gresult)==0) {
    $gresult[0]='';
}

if (isset($gresults)) {
	foreach ($gresults as $gresult) {
    $hl = $gresult[0] == $default_grp ? __(' [DEFAULT]') : '';
		echo "<li><a class=\"".($extdisplay==$gresult[0] ? 'current':'')."\" href=\"config.php?display=".urlencode($dispnum)."&extdisplay=".urlencode("GRP-".$gresult[0])."\">".$gresult[1]." ({$gresult[0]})$hl</a></li>";
	}
}
?>
</ul></div-->
<div class='content'>
<?php

    $helptext = __("Creates a group of extensions that calls a group of voicemail boxes and allows you to leave a message for them all at once.");
    $help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

	if ($extdisplay != '') {
		// We need to populate grplist with the existing extension list.
		$thisgrp = vmblast_get($extdisplay);
		$grplist     = $thisgrp['grplist'];
		$description = $thisgrp['description'];
		$audio_label = $thisgrp['audio_label'];
		$password    = $thisgrp['password'];
		$default_group = $thisgrp['default_group'];
		unset($thisgrp);

		echo "<div class='is-flex'><h2>".__("VMBlast Group").": ".$extdisplay."</h2>$help</div>";

		$usage_list = framework_display_destination_usage(vmblast_getdest($extdisplay));
		if (!empty($usage_list)) {
            echo ipbx_usage_info($usage_list['text'],$usage_list['tooltip']);
		}

	} else {
		$grplist = array();
		$strategy = '';
		$ringing = '';

		if (!empty($conflict_url)) {
			echo "<h5>".__("Conflicting Extensions")."</h5>";
			echo implode('<br .>',$conflict_url);
		}
		echo "<div class='is-flex'><h2>".__("Add VMBlast Group")."</h2>$help</div>";
	}
	?>
	<form id="mainform" name="editGRP" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkGRP(editGRP);">
	<input type="hidden" name="display" value="<?php echo $dispnum?>">
	<input type="hidden" name="action" value="<?php echo ($extdisplay != '' ? 'editGRP' : 'addGRP'); ?>">
    <table class='table is-borderless is-narrow'>
    <tr><td colspan="2"><h5><?php  echo _dgettext('amp','General Settings') ?></h5></td></tr>
    <tr>
<?php
	if ($extdisplay != '') {
?>
				<input size="5" type="hidden" name="account" value="<?php  echo $extdisplay; ?>" tabindex="<?php echo ++$tabindex;?>">
<?php 	} else { ?>
				<td><a href="#" class="info"><?php echo __("VMBlast Number")?><span><?php echo __("The number users will dial to voicemail boxes in this VMBlast group")?></span></a></td>
				<td><input class='w100 input' type="text" name="account" data-extdisplay="" value="<?php  if ($gresult[0]==0) { echo "500"; } else { echo $gresult[0] + 1; } ?>" tabindex="<?php echo ++$tabindex;?>"></td>
<?php 		} ?>
			</tr>

			<tr>
				<td> <a href="#" class="info"><?php echo __("Group Description")?><span><?php echo __("Provide a descriptive title for this VMBlast Group.")?></span></a></td>
				<td><input class='w100 input' maxlength="35" type="text" name="description" value="<?php echo htmlspecialchars($description); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>

<?php if(function_exists('recordings_list')) { //only include if recordings is enabled?>
			<tr>
				<td><a href="#" class="info"><?php echo __("Audio Label")?><span><?php echo __("Play this message to the caller so they can confirm they have dialed the proper voice mail group number, or have the system simply read the group number.")?></span></a></td>
				<td>
					<select name="audio_label" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
					<?php
						$tresults = recordings_list();
						$default = (isset($audio_label) ? $audio_label : -1);
						echo '<option value="-1">'.__("Read Group Number")."</option>";
						echo '<option value="-2"'.(($default == -2) ? ' SELECTED':'').'>'.__("Beep Only - No Confirmation")."</option>";
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
				<td><a href="#" class="info"><?php echo __("Audio Label")?><span><?php echo __("The group number will be played to the caller so they can confirm they have dialed the proper voice mail group number.<br><br>You must install and enable the \"Systems Recordings\" Module to edit this option and choose from recordings.")?></span></a></td>
				<td>
					<?php
						$default = (isset($audio_label) ? $audio_label : -1);
					?>
					<input type="hidden" name="audio_label" value="<?php echo $default; ?>"><?php echo ($default != -1 ? $default : __('Read Group Number')); ?>
				</td>
			</tr>
<?php }
?>
			<tr>
				<td><a href="#" class="info"><?php echo __("Optional Password")?><span><?php echo __('You can optionally include a password to authenticate before providing access to this group voicemail list.')?></span></a></td>
				<td><input class='w100 input' type="text" name="password" value="<?php  echo $password ?>" tabindex="<?php echo ++$tabindex;?>">
				</td>
			</tr>

			<tr>
				<td valign='top'><a href='#' class='info'><?php echo __("Voicemail Box List")."<span><br>".__("Select voice mail boxes to add to this group.") ?>
	<br><br></span></a>
				</td>
				<td valign="top">
					<select multiple="multiple" name="vmblast_list[]" id="xtnlist"  tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
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
					<a href='#' class='info'><?php echo __("Default VMBlast Group") ?>
						<span> <?php echo __("Each PBX system can have a single Default Voicemail Blast Group. If specified, extensions can be automatically added (or removed) from this default group in the Extensions (or Users) tab.<br />Making this group the default will uncheck the option from the current default group if specified.") ?> </span>
					</a>
				</td>
				<td>
                    <!--input type='checkbox' name='default_group' id="default_group" value='1' <?php if ($default_group) { echo 'CHECKED'; } ?> tabindex="<?php echo ++$tabindex;?>"-->

<?php $checked = ($default_group)?' checked="checked" ':''; ?>
<div class='field'><input type='checkbox' class='switch' id='default_group' name='default_group' value='1' <?php echo $checked;?> tabindex='<?php echo ++$tabindex;?>'/><label style='height:auto; line-height:1em; padding-left:3em;' for='default_group'>&nbsp;</label></div>


				</td>
			</tr>

<?php
			// implementation of module hook
			// object was initialized in config.php
            echo process_tabindex($module_hook->hookHtml,$tabindex);
?>
	</table>
</form>
<script>

function checkGRP(theForm) {
	var msgInvalidGrpNum = "<?php echo __('Invalid Group Number specified'); ?>";
	var msgInvalidGrpNumStartWithZero = "<?php echo __('Group numbers with more than one digit cannot begin with 0'); ?>";
	var msgInvalidExtList = "<?php echo __('Please enter an extension list.'); ?>";
	var msgInvalidDescription = "<?php echo __('Please enter a valid Group Description'); ?>";
	var msgInvalidPassword = "<?php echo __('Please enter a valid numeric password, only numbers are allowed'); ?>";
	var msgInvalidExtList = "<?php echo __('Please select at least one extension'); ?>";

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
			return warnInvalid(theForm.description, "<?php echo __('The Group Description provided is too long.'); ?>")
	<?php } ?>

	if (!isAlphanumeric(theForm.description.value))
		return warnInvalid(theForm.description, msgInvalidDescription);

	var selected = 0;
	for (var i=0; i < theForm.xtnlist.options.length; i++) {
		if (theForm.xtnlist.options[i].selected) selected += 1;
	}
	if (selected < 1) {
    theForm.xtnlist.focus();
		sweet_alert(msgInvalidExtList);
		return false;
	}

	return true;
}

<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
