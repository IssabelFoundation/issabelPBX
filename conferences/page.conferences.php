<?php /* $Id$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$dispnum = "conferences"; //used for switch on config.php
$tabindex = 0;

isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';
//the extension we are currently displaying

$account = isset($_REQUEST['account']) ? $_REQUEST['account'] : '';
$extdisplay = isset($_REQUEST['extdisplay']) && $_REQUEST['extdisplay'] != '' ? $_REQUEST['extdisplay'] : $account;

$orig_account = isset($_REQUEST['orig_account']) ? $_REQUEST['orig_account'] : '';
$music = isset($_REQUEST['music']) ? $_REQUEST['music'] : '';
$users = isset($_REQUEST['users']) ? $_REQUEST['users'] : '0';

//check if the extension is within range for this user
if ($account != "" && !checkRange($account)){
	echo "<script>javascript:alert('"._("Warning! Extension")." $account "._("is not allowed for your account.")."');</script>";
} else {

	//if submitting form, update database
	switch ($action) {
		case "add":

			$conflict_url = array();
			$usage_arr = framework_check_extension_usage($account);
			if (!empty($usage_arr)) {
				$conflict_url = framework_display_extension_usage_alert($usage_arr);
			} elseif (conferences_add($account,$_REQUEST['name'],$_REQUEST['userpin'],$_REQUEST['adminpin'],$_REQUEST['options'],$_REQUEST['joinmsg_id'],$music,$users) !== false) {
				needreload();
				redirect_standard('account');
			}
		break;
		case "delete":
			conferences_del($extdisplay);
			needreload();
			redirect_standard();
		break;
		case "edit":  //just delete and re-add
			//check to see if the room number has changed
			if ($orig_account != '' && $orig_account != $account) {
				$conflict_url = array();
				$usage_arr = framework_check_extension_usage($account);
				if (!empty($usage_arr)) {
					$conflict_url = framework_display_extension_usage_alert($usage_arr);
					break;
				} else {
					conferences_del($orig_account);
					$_REQUEST['extdisplay'] = $account;//redirect to the new ext
					$old = conferences_getdest($orig_account);
					$new = conferences_getdest($account);
					framework_change_destination($old[0], $new[0]);
				}
			} else {
				conferences_del($account);
			}

			conferences_add($account,$_REQUEST['name'],$_REQUEST['userpin'],$_REQUEST['adminpin'],$_REQUEST['options'],$_REQUEST['joinmsg_id'],$music,$users);
			needreload();
			redirect_standard('extdisplay');
		break;
	}
}

//Check to see if conference application is only confbridge
global $amp_conf;
global $astman;
if ($astver === null) {
	$engineinfo = engine_getinfo();
	$astver =  $engineinfo['version'];
}

//get meetme rooms
//this function needs to be available to other modules (those that use goto destinations)
//therefore we put it in globalfunctions.php
$meetmes = conferences_list();
?>


<!-- right side menu -->
<div class="rnav"><ul>
		<li><a id="<?php echo ($extdisplay=='' ? 'current':'') ?>" href="config.php?display=<?php echo urlencode($dispnum)?>"><?php echo _("Add Conference")?></a></li>
<?php
if (isset($meetmes)) {
	foreach ($meetmes as $meetme) {
		echo "<li><a id=\"".($extdisplay==$meetme[0] ? 'current':'')."\" href=\"config.php?display=".urlencode($dispnum)."&extdisplay=".urlencode($meetme[0])."\">{$meetme[0]}:{$meetme[1]}</a></li>";
	}
}
?>
</ul></div>

<?php
if ($action == 'delete') {
	echo '<br><h3>'._("Conference").' '.$extdisplay.' '._("deleted").'!</h3><br><br><br><br><br><br><br><br>';
} else {
	if ($extdisplay != ""){
		//get details for this meetme
		$thisMeetme = conferences_get($extdisplay);
		$options     = $thisMeetme['options'];
		$userpin     = $thisMeetme['userpin'];
		$adminpin    = $thisMeetme['adminpin'];
		$description = $thisMeetme['description'];
		$joinmsg_id  = $thisMeetme['joinmsg_id'];
		$music       = $thisMeetme['music'];
		$users       = $thisMeetme['users'];
	} else {
		$options     = "";
		$userpin     = "";
		$adminpin    = "";
		$description = "";
		$joinmsg_id  = "";
		$music       = "";
		$users	      = "0";
	}

?>
<?php		if ($extdisplay != ""){ ?>
	<h2><?php echo _("Conference:")." ". $extdisplay; ?></h2>
<?php
					$delURL = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&action=delete';
					$tlabel = sprintf(_("Delete Conference %s"),$extdisplay);
					$label = '<span><img width="16" height="16" border="0" title="'.$tlabel.'" alt="" src="images/core_delete.png"/>&nbsp;'.$tlabel.'</span>';
?>
					<a href="<?php echo $delURL ?>"><?php echo $label; ?></a><br />
<?php
					$usage_list = framework_display_destination_usage(conferences_getdest($extdisplay));
					if (!empty($usage_list)) {
?>
						<a href="#" class="info"><?php echo $usage_list['text']?>:<span><?php echo $usage_list['tooltip']?></span></a>
<?php
					}
?>

<?php		} else { ?>
	<h2><?php echo _("Add Conference"); ?></h2>
<?php		}
				if (!empty($conflict_url)) {
					echo "<h5>"._("Conflicting Extensions")."</h5>";
					echo implode('<br .>',$conflict_url);
				}
?>
	<form autocomplete="off" name="editMM" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkConf();">
	<input type="hidden" name="display" value="<?php echo $dispnum?>">
	<input type="hidden" name="action" value="<?php echo ($extdisplay != '' ? 'edit' : 'add') ?>">
	<input type="hidden" name="options" value="<?php echo $options; ?>">
<?php		if ($extdisplay != ""){ ?>
		<input type="hidden" name="orig_account" value="<?php echo $extdisplay; ?>">
<?php		}?>
	<table>
	<tr><td colspan="2"><h5><?php echo ($extdisplay != "" ? _("Edit Conference") : _("Add Conference")) ?><hr></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Conference Number:")?><span><?php echo _("Use this number to dial into the conference.")?></span></a></td>
		<td><input type="text" name="account" value="<?php echo $extdisplay ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Conference Name:")?><span><?php echo _("Give this conference a brief name to help you identify it.")?></span></a></td>
		<td><input type="text" name="name" value="<?php echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("User PIN:")?><span><?php echo _("You can require callers to enter a password before they can enter this conference.<br><br>This setting is optional.<br><br>If either PIN is entered, the user will be prompted to enter a PIN.")?></span></a></td>
		<td><input size="8" type="text" name="userpin" value="<?php echo $userpin; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Admin PIN:")?><span><?php echo _("Enter a PIN number for the admin user.<br><br>This setting is optional unless the 'leader wait' option is in use, then this PIN will identify the leader.")?></span></a></td>
		<td><input size="8" type="text" name="adminpin" value="<?php echo $adminpin; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>

	<tr><td colspan="2"><br><h5><?php echo _("Conference Options")?><hr></h5></td></tr>
<?php if(function_exists('recordings_list')) { //only include if recordings is enabled?>
	<tr>
		<td><a href="#" class="info"><?php echo _("Join Message:")?><span><?php echo _("Message to be played to the caller before joining the conference.<br><br>To add additional recordings please use the \"System Recordings\" MENU to the left")?></span></a></td>
		<td>
			<select name="joinmsg_id" tabindex="<?php echo ++$tabindex;?>">
			<?php
				$tresults = recordings_list();
				echo '<option value="">'._("None")."</option>";
				if (isset($tresults[0])) {
					foreach ($tresults as $tresult) {
						echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $joinmsg_id ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
					}
				}
			?>
			</select>
		</td>
	</tr>
<?php }	else { ?>
	<tr>
		<td><a href="#" class="info"><?php echo _("Join Message:")?><span><?php echo _("Message to be played to the caller before joining the conference.<br><br>You must install and enable the \"Systems Recordings\" Module to edit this option")?></span></a></td>
		<td>
			<input type="hidden" name="joinmsg_id" value="<?php echo $joinmsg_id; ?>"><?php echo ($joinmsg_id != '' ? $joinmsg_id : 'None'); ?>
		</td>
	</tr>
<?php } ?>
	<tr>
		<td><a href="#" class="info"><?php echo _("Leader Wait:")?><span><?php echo _("Wait until the conference leader (admin user) arrives before starting the conference")?></span></a></td>
		<td>
			<select name="opt#w" tabindex="<?php echo ++$tabindex;?>">
			<?php
				$optselect = strpos($options, "w");
				echo '<option value=""' . ($optselect === false ? ' SELECTED' : '') . '>'._("No") . '</option>';
				echo '<option value="w"'. ($optselect !== false ? ' SELECTED' : '') . '>'._("Yes"). '</option>';
			?>
			</select>
		</td>
	</tr>

<?php
$engineinfo = engine_getinfo();
$astver =  $engineinfo['version'];
$ast_ge_10 = version_compare($astver, '10', 'ge');
if (version_compare($astver, '1.4', 'ge') && $amp_conf['ASTCONFAPP']=='app_meetme' || $ast_ge_10) {
?>
	<tr>
		<td><a href="#" class="info"><?php echo _("Talker Optimization:")?><span><?php echo _("Turns on talker optimization. With talker optimization, Asterisk treats talkers who
are not speaking as being muted, meaning that no encoding is done on transmission
and that received audio that is not registered as talking is omitted, causing no
buildup in background noise.")?></span></a></td>
		<td>
			<select name="opt#o">
			<?php
				$optselect = strpos($options, "o");
				echo '<option value=""' . ($optselect === false ? ' SELECTED' : '') . '>'._("No") . '</option>';
				echo '<option value="o"'. ($optselect !== false ? ' SELECTED' : '') . '>'._("Yes"). '</option>';
			?>
			</select>
		</td>
	</tr>


	<tr>
		<td><a href="#" class="info"><?php echo _("Talker Detection:")?><span><?php echo _("Sets talker detection. Asterisk will sends events on the Manager Interface identifying
the channel that is talking. The talker will also be identified on the output of
the meetme list CLI command.")?></span></a></td>
		<td>
			<select name="opt#T">
			<?php
				$optselect = strpos($options, "T");
				echo '<option value=""' . ($optselect === false ? ' SELECTED' : '') . '>'._("No") . '</option>';
				echo '<option value="T"'. ($optselect !== false ? ' SELECTED' : '') . '>'._("Yes"). '</option>';
			?>
			</select>
		</td>
	</tr>
<?php
} else {//when using confbridge, hide option, but save it anyway
	echo '<input type="hidden" name="opt#T" value="' . (strpos($options, "T") !== false ? 'T' : '') . '"';
	echo '<input type="hidden" name="opt#o" value="' . (strpos($options, "o") !== false ? 'o' : '') . '"';
}?>
	<tr>
		<td><a href="#" class="info"><?php echo _("Quiet Mode:")?><span><?php echo _("Quiet mode (do not play enter/leave sounds)")?></span></a></td>
		<td>
			<select name="opt#q" tabindex="<?php echo ++$tabindex;?>">
			<?php
				$optselect = strpos($options, "q");
				echo '<option value=""' . ($optselect === false ? ' SELECTED' : '') . '>'._("No") . '</option>';
				echo '<option value="q"'. ($optselect !== false ? ' SELECTED' : '') . '>'._("Yes"). '</option>';
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("User Count:")?><span><?php echo _("Announce user(s) count on joining conference")?></span></a></td>
		<td>
			<select name="opt#c" tabindex="<?php echo ++$tabindex;?>">
			<?php
				$optselect = strpos($options, "c");
				echo '<option value=""' . ($optselect === false ? ' SELECTED' : '') . '>'._("No") . '</option>';
				echo '<option value="c"'. ($optselect !== false ? ' SELECTED' : '') . '>'._("Yes"). '</option>';
			?>
			</select>
		</td>
	</tr>
	<?php
		if ($amp_conf['ASTCONFAPP']=='app_meetme' || $ast_ge_10) {
	?>
	<tr>
		<td><a href="#" class="info"><?php echo _("User join/leave:")?><span><?php echo _("Announce user join/leave")?></span></a></td>
		<td>
			<select name="opt#i" tabindex="<?php echo ++$tabindex;?>">
			<?php
				$optselect = strpos($options, "i");
				echo '<option value=""' . ($optselect === false ? ' SELECTED' : '') . '>'._("No") . '</option>';
				echo '<option value="i"'. ($optselect !== false ? ' SELECTED' : '') . '>'._("Yes"). '</option>';
			?>
			</select>
		</td>
	</tr>
	<?php } else {//when using confbridge, hide option, but save it anyway
		echo '<input type="hidden" name="opt#i" value="' . (strpos($options, "i") !== false ? 'i' : '') . '"';
	}?>
	<tr>
		<td><a href="#" class="info"><?php echo _("Music on Hold:")?><span><?php echo _("Enable Music On Hold when the conference has a single caller")?></span></a></td>
		<td>
			<select name="opt#M" tabindex="<?php echo ++$tabindex;?>">
			<?php
				$optselect = strpos($options, "M");
				echo '<option value=""' . ($optselect === false ? ' SELECTED' : '') . '>'._("No") . '</option>';
				echo '<option value="M"'. ($optselect !== false ? ' SELECTED' : '') . '>'._("Yes"). '</option>';
			?>
			</select>
		</td>
	</tr>

<?php if(function_exists('music_list')) { //only include if music module is enabled?>
				<tr>
								<td><a href="#" class="info"><?php echo _("Music on Hold Class:")?><span><?php echo _("Music (or Commercial) played to the caller while they wait in line for the conference to start. Choose \"inherit\" if you want the MoH class to be what is currently selected, such as by the inbound route.<br><br>  This music is defined in the \"Music on Hold\" to the left.")?></span></a></td>
								<td>
												<select name="music" tabindex="<?php echo ++$tabindex;?>">
												<?php
																$tresults = music_list();
																array_unshift($tresults,'inherit');
																$default = (isset($music) ? $music : 'inherit');
																if (isset($tresults)) {
																				foreach ($tresults as $tresult) {
																								$searchvalue="$tresult";
																								( $tresult == 'inherit' ? $ttext = _("inherit") : $ttext = $tresult );
// there is a separate flag for turning off moh - just leaving this in case it should be unified to the way this is managed for queues (via "none" selection)
//                                              ( $tresult == 'none' ? $ttext = _("none") : $ttext = $tresult );
																								( $tresult == 'default' ? $ttext = _("default") : $ttext = $tresult );
																								echo '<option value="'.$tresult.'" '.($searchvalue == $default ? 'SELECTED' : '').'>'.$ttext;
																				}
																}
												?>
												</select>
								</td>
				</tr>
<?php } ?>

	<tr>
		<td><a href="#" class="info"><?php echo _("Allow Menu:")?><span><?php echo _("Present Menu (user or admin) when '*' is received ('send' to menu)")?></span></a></td>
		<td>
			<select name="opt#s" tabindex="<?php echo ++$tabindex;?>">
			<?php
				$optselect = strpos($options, "s");
				echo '<option value=""' . ($optselect === false ? ' SELECTED' : '') . '>'._("No") . '</option>';
				echo '<option value="s"'. ($optselect !== false ? ' SELECTED' : '') . '>'._("Yes"). '</option>';
			?>
			</select>
		</td>
	</tr>
	<?php
		if ($amp_conf['ASTCONFAPP'] == 'app_meetme' || $ast_ge_10) {
	?>
	<tr>
		<td><a href="#" class="info"><?php echo _("Record Conference:")?><span><?php echo _("Record the conference call")?></span></a></td>
		<td>
			<select name="opt#r" tabindex="<?php echo ++$tabindex;?>">
				<?php
				$optselect = strpos($options, "r");
				echo '<option value=""' . ($optselect === false ? ' SELECTED' : '') . '>'._("No") . '</option>';
 				echo '<option value="r"'. ($optselect !== false ? ' SELECTED' : '') . '>'._("Yes"). '</option>';
				?>
			</select>
		</td>
	</tr>
	<?php } else {//when using confbridge, hide option, but save it anyway
		echo '<input type="hidden" name="opt#r" value="' . (strpos($options, "r") !== false ? 'r' : '') . '"';
	}?>
	<?php //Begin Maximum Participants Code ?>
	<tr>
		<td><a href="#" class="info"><?php echo _("Maximum Participants:")?><span><?php echo _("Maximum Number of users allowed to join this conference.")?></span></a></td>
		<td>
		  <select name="users" tabindex="<?php echo ++$tabindex;?>">
			<?php
			$default = (($users) ? $users : 0);
			echo '<option value="0" '.($i == $default ? 'SELECTED' : '').'>'._("No Limit").'</option>';
			for ($i=2; $i <= 20; $i++) {
			  echo '<option value="'.$i.'" '.($i == $default ? 'SELECTED' : '').'>'.$i.'</option>';
			}
			?>
		  </select>
		</td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Mute on Join:")?><span><?php echo _("Mute everyone when they initially join the conference. Please note that if you do not have 'Leader Wait' set to yes you must have 'Allow Menu' set to Yes to unmute yourself")?></span></a></td>
		<td>
				<select name="opt#m" tabindex="<?php echo ++$tabindex;?>">
				<?php
						$optselect = strpos($options, "m");
						echo '<option value=""' . ($optselect === false ? ' SELECTED' : '') . '>'._("No") . '</option>';
						echo '<option value="m"'. ($optselect !== false ? ' SELECTED' : '') . '>'._("Yes"). '</option>';
				?>
				</select>
		</td>
		</tr>
	</table>
<?php
	// implementation of module hook
	// object was initialized in config.php
	echo $module_hook->hookHtml;
?>
	<h6><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>"></h6>
<script language="javascript">
<!--

var theForm = document.editMM;

if (theForm.account.value == "") {
	theForm.account.focus();
} else {
	theForm.name.focus();
}

function checkConf()
{
	var msgInvalidConfNumb = "<?php echo _('Please enter a valid Conference Number'); ?>";
	var msgInvalidConfName = "<?php echo _('Please enter a valid Conference Name'); ?>";
	var msgNeedAdminPIN = "<?php echo _('You must set an admin PIN for the Conference Leader when selecting the leader wait option'); ?>";
	var msgInvalidMuteOnJoin = "<?php echo _('You must set Allow Menu to Yes when not using a Leader or Admin in your conference, otherwise you will be unable to unmute yourself'); ?>";

	defaultEmptyOK = false;
	if (!isInteger(theForm.account.value))
		return warnInvalid(theForm.account, msgInvalidConfNumb);

	<?php if (function_exists('module_get_field_size')) { ?>
		var sizeDisplayName = "<?php echo module_get_field_size('meetme', 'description', 50); ?>";
		if (!isCorrectLength(theForm.name.value, sizeDisplayName))
			return warnInvalid(theForm.name, "<?php echo _('The Conference Name provided is too long.'); ?>")
	<?php } ?>
	
	if (!isAlphanumeric(theForm.name.value))
		return warnInvalid(theForm.name, msgInvalidConfName);

	// update $options
	var theOptionsFld = theForm.options;
	theOptionsFld.value = "";
	for (var i = 0; i < theForm.elements.length; i++)
	{
		var theEle = theForm.elements[i];
		var theEleName = theEle.name;
		if (theEleName.indexOf("#") > 1)
		{
			var arr = theEleName.split("#");
			if (arr[0] == "opt")
				theOptionsFld.value += theEle.value;
		}
	}

	// not possible to have a 'leader' conference with no adminpin
	if (theForm.options.value.indexOf("w") > -1 && theForm.adminpin.value == "")
		return warnInvalid(theForm.adminpin, msgNeedAdminPIN);

	// should not have a conference with no 'leader', mute on join, and no allow menu, so let's complain
	if ($('[name=opt\\#m]').val() != '' && $('[name=adminpin]').val() == '' && !$('[name=opt\\#s]').val())
		return warnInvalid(theForm.options, msgInvalidMuteOnJoin);

	return true;
}

//-->
</script>
	</form>
<?php
} //end if action == delGRP
?>
