<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
/* $Id: page.ivr.php 3790 2007-02-16 18:52:53Z p_lindheimer $ */

$dispnum = "daynight"; //used for switch on config.php
$tabindex = 0;

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$password = isset($_REQUEST['password'])?$_REQUEST['password']:'';
$fc_description = isset($_REQUEST['fc_description'])?$_REQUEST['fc_description']:'';
$day_recording_id = isset($_POST['day_recording_id']) ? $_POST['day_recording_id'] :  '';
$night_recording_id = isset($_POST['night_recording_id']) ? $_POST['night_recording_id'] :  '';

isset($_REQUEST['itemid'])?$itemid=$db->escapeSimple($_REQUEST['itemid']):$itemid='';

$daynightcodes = daynight_list();
?><div class="rnav"><ul>
    <li><a id="<?php echo ($itemid=='' ? 'current':'') ?>" href="config.php?display=<?php echo urlencode($dispnum)?>&action=add"><?php echo _("Add Call Flow Toggle Code")?></a></li>
<?php
if (isset($daynightcodes)) {
	foreach ($daynightcodes as $code) {
		$fcc = new featurecode('daynight', 'toggle-mode-'.$code['ext']);
		$fc = $fcc->getCode();
		unset($fcc);

		$dnobj = daynight_get_obj($code['ext']);
		$color = $dnobj['state'] == 'DAY' ? "style='color:green'" : "style='color:red'";
		echo "<li><a $color id=\"".($itemid==$code['ext'] ? 'current':'')."\" href=\"config.php?display=".urlencode($dispnum)."&itemid=".urlencode($code['ext'])."&action=edit\">($fc) {$code['dest']}</a></li>";
	}
}
?>
</ul></div>
<?php

switch ($action) {
	case "add":
		daynight_show_edit($_POST,'add');
		break;
	case "edit":
		daynight_show_edit($_POST);
		break;
	case "edited":
			daynight_edit($_POST,$itemid);
			redirect_standard('itemid');
			break;
	case "delete":
			daynight_del($itemid);
			redirect_standard();
			break;
	default:
		daynight_show_edit($_POST,'add');
		break;
}

function daynight_show_edit($post, $add="") {
	global $db;
	global $itemid;

	$fcc = new featurecode('daynight', 'toggle-mode-'.$itemid);
	$code = $fcc->getCodeActive();
	unset($fcc);

	$dests = daynight_get_obj($itemid);
	$password = isset($dests['password'])?$dests['password']:'';
	$fc_description = isset($dests['fc_description'])?$dests['fc_description']:'';
	$state = isset($dests['state'])?$dests['state']:'DAY';
	$day_recording_id = isset($dests['day_recording_id'])?$dests['day_recording_id']:'';
	$night_recording_id = isset($dests['night_recording_id'])?$dests['night_recording_id']:'';
?>
	<h2><?php echo _("Call Flow Toggle Control"); ?></h2>
<?php		
	if ($itemid != ""){ 
		$delURL = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&action=delete';
		$tlabel = sprintf(_("Delete Call Flow Toggle Feature Code %s"),$code);
		$label = '<span><img width="16" height="16" border="0" title="'.$tlabel.'" alt="" src="images/core_delete.png"/>&nbsp;'.$tlabel.'</span>';
?>
		<a href="<?php echo $delURL ?>"><?php echo $label; ?></a><br />
<?php
		$usage_list = framework_display_destination_usage(daynight_getdest($itemid));
		if (!empty($usage_list)) {
?>
			<a href="#" class="info"><?php echo $usage_list['text'].'<br />'?><span><?php echo $usage_list['tooltip']?></span></a>
<?php
		}
		$timeconditions_refs = daynight_list_timecondition($itemid);
		if (!empty($timeconditions_refs)) {
			echo "<br />";
			foreach($timeconditions_refs as $ref) {
				$dmode = ($ref['dmode'] == 'timeday') ? _("Forces to Normal Mode (Green/BLF off)") : _("Forces to Override Mode (Red/BLF on)");
				$timecondition_id = $ref['dest'];
				$tcURL = $_SERVER['PHP_SELF'].'?'."display=timeconditions&itemid=$timecondition_id";
				$label = '<span><img width="16" height="16" border="0" title="'.sprintf(_("Linked to Time Condition %s - %s"),$timecondition_id,$dmode).'" alt="" src="images/clock_link.png"/>&nbsp;'.sprintf(_("Linked to Time Condition %s - %s"),$timecondition_id,$dmode).'</span>';
?>
				<a href="<?php echo $tcURL ?>"><?php echo $label; ?></a><br />
<?php
			}
		}
	} 
?>
	<form name="prompt" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return prompt_onsubmit();">
	<input type="hidden" name="action" value="edited" />
	<input type="hidden" name="display" value="daynight" />
	<input name="Submit" type="submit" style="display:none;" value="save" />
	<table>
	<tr>
		<td colspan=2><hr />
		</td>
	</tr>
	<tr>
		<td colspan="2">	
		<input name="Submit" type="submit" value="<?php echo _("Save")?>">
		<?php if ($itemid != '') echo "&nbsp ".sprintf(_("Use feature code: %s to toggle the call flow mode"),"<strong>".$code."</strong>")?>
		</td>
	</tr>
	<tr>
		<td colspan=2>
		<hr />
		</td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Call Flow Toggle Feature Code Index:")?>
		<span><?php echo _("There are a total of 10 Feature code objects, 0-9, each can control a call flow and be toggled using the call flow toggle feature code plus the index.")?>
		</span></a>
		</td>
		<td>
<?php
			if ($add == "add" && $itemid =="") {
?>
			<select name="itemid" tabindex="<?php echo ++$tabindex;?>">
			<?php
				$ids = daynight_get_avail();
				foreach ($ids as $id) {
					echo '<option value="'.$id.'" >'.$id.'</option>';
				}
			?>
			</select>
<?php
			} else {
?>
		<input readonly="yes" size="1" type="text" name="itemid" value="<?php  echo $itemid ?>">
<?php
		}
?>
		</td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Description")?>:<span><?php echo _("Description for this Call Flow Toggle Control")?></span></a></td>
		<td><input size="40" type="text" name="fc_description" value="<?php  echo $fc_description ?>" tabindex="<?php echo ++$tabindex;?>">
		</td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Current Mode:")?>
		<span><?php echo _("This will change the current state for this Call Flow Toggle Control, or set the initial state when creating a new one.")?>
		</span></a>
		</td>
		<td>
			<select name="state" tabindex="<?php echo ++$tabindex;?>">
				<option value="DAY" <?php echo ($state == 'DAY' ? 'SELECTED':'') ?> ><?php echo _("Normal (Green/BLF off)");?></option> 
				<option value="NIGHT" <?php echo ($state == 'NIGHT' ? 'SELECTED':'') ?> ><?php echo _("Override (Red/BLF on)");?></option> 
			</select>
		</td>
	</tr>

<?php if(function_exists('recordings_list')) { //only include if recordings are enabled ?>
	<tr>
		<td><a href="#" class="info"><?php echo _("Recording for Normal Mode")?><span><?php echo _("Message to be played in normal mode (Green/BLF off).<br>To add additional recordings use the \"System Recordings\" MENU to the left")?></span></a></td>
		<td>
			<select name="day_recording_id"  tabindex="<?php echo ++$tabindex;?>">
			<?php
				$tresults = recordings_list();
				$default = (isset($day_recording_id) ? $day_recording_id : '');
				echo '<option value="0">' ._("Default") ."</option>\n";
				if (isset($tresults[0])) {
					foreach ($tresults as $tresult) {
						echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $default ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
					}
				}
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Recording for Override Mode")?><span><?php echo _("Message to be played in override mode (Red/BLF on).<br>To add additional recordings use the \"System Recordings\" MENU to the left")?></span></a></td>
		<td>
			<select name="night_recording_id"  tabindex="<?php echo ++$tabindex;?>">
			<?php
				$default = (isset($night_recording_id) ? $night_recording_id : '');
				echo '<option value="0">' ._("Default") ."</option>\n";
				if (isset($tresults[0])) {
					foreach ($tresults as $tresult) {
						echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $default ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
					}
				}
			?>
			</select>
		</td>
	</tr>

<?php } ?>

	<tr>
		<td><a href="#" class="info"><?php echo _("Optional Password")?>:<span><?php echo _('You can optionally include a password to authenticate before toggling the call flow. If left blank anyone can use the feature code and it will be un-protected')?></span></a></td>
		<td><input size="12" type="text" name="password" value="<?php  echo $password ?>" tabindex="<?php echo ++$tabindex;?>">
		</td>
	</tr>
	<tr>
		<td colspan=2>
		<hr />
		</td>
	</tr>
<?php
	// Draw the destinations
	// returns an array, $dest['day'], $dest['night']
	// and puts null if nothing set

	drawdestinations(0, _("Normal Flow (Green/BLF off)"),   (isset($dests['day'])?$dests['day']:''));
	drawdestinations(1, _("Override Flow (Red/BLF on)"), (isset($dests['night'])?$dests['night']:''));

	//TODO: Check to make sure a destination radio button was checked, and if custom, that it was not blank
	//
?>
	<tr>
		<td colspan=2>	
		<input name="Submit" type="submit" value="<?php echo _("Save")?>">
		<?php if ($itemid != '') echo "&nbsp ".sprintf(_("Use feature code: %s to toggle the call flow mode"),"<strong>".$code."</strong>")?>
		</td>
	</tr>
	<tr>
		<td colspan=2>
		<hr />
		</td>
	</tr>
	</table>

	<script language="javascript">
	<!--
	var theForm = document.prompt;

	function prompt_onsubmit() {
		var msgInvalidPassword = "<?php echo _('Please enter a valid numeric password, only numbers are allowed'); ?>";

		defaultEmptyOK = true;
		if (!isInteger(theForm.password.value))
			return warnInvalid(theForm.password, msgInvalidPassword);
		return true;
	}
	//-->
	</script>

	</form>
<?php
} //daynight function


// count is for the unique identifier
// dest is the target
//
function drawdestinations($count, $mode, $dest) { ?>
	<tr> 
		<td style="text-align:right;">
		<a href="#" class="info"><strong><?php echo $mode?></strong><span><?php echo sprintf(_("Destination to use when set to %s mode"),$mode);?></span></a>
		</td>
		<td> 
			<table> <?php echo drawselects($dest,$count); ?> 
			</table> 
		</td>
	</tr>
	<tr><td colspan=2><hr /></td></tr>
<?php
}
?>
