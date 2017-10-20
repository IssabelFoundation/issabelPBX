<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'setup';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] :  '';
if (isset($_REQUEST['delete'])) $action = 'delete'; 

$callrecording_id = isset($_REQUEST['callrecording_id']) ? $_REQUEST['callrecording_id'] :  false;
$description = isset($_REQUEST['description']) ? $_REQUEST['description'] :  '';
$callrecording_mode = isset($_REQUEST['callrecording_mode']) ? $_REQUEST['callrecording_mode'] :  '';
$dest = isset($_REQUEST['dest']) ? $_REQUEST['dest'] :  '';

if (isset($_REQUEST['goto0']) && $_REQUEST['goto0']) {
	$dest = $_REQUEST[ $_REQUEST['goto0'].'0' ];
}

switch ($action) {
	case 'add':
		$_REQUEST['extdisplay'] = callrecording_add($description, $callrecording_mode, $dest);
		needreload();
		redirect_standard('extdisplay');
	break;
	case 'edit':
		callrecording_edit($callrecording_id, $description, $callrecording_mode, $dest);
		needreload();
		redirect_standard('extdisplay');
	break;
	case 'delete':
		callrecording_delete($callrecording_id);
		needreload();
		redirect_standard();
	break;
}

?> 

<div class="rnav"><ul>
<?php 

echo '<li><a href="config.php?display=callrecording&amp;type='.$type.'">'._('Add Call Recording').'</a></li>';

foreach (callrecording_list() as $row) {
	echo '<li><a href="config.php?display=callrecording&amp;type='.$type.'&amp;extdisplay='.$row['callrecording_id'].'" class="">'.$row['description'].'</a></li>';
}

?>
</ul></div>

<?php

if ($extdisplay) {
	// load
	$row = callrecording_get($extdisplay);
	
	$description = $row['description'];
	$callrecording_mode   = $row['callrecording_mode'];
	$dest        = $row['dest'];

	$cm_disp = $callrecording_mode ? $callrecording_mode : 'allow';
	echo "<h2>"._("Edit: ")."$description ($cm_disp)"."</h2>";
} else {
	echo "<h2>"._("Add Call Recording")."</h2>";
}

$helptext = _("Call Recordings provide the ability to force a call to be recorded or not recorded based on a call flow and override all other recording settings. If a call is to be recorded, it can start immediately which will incorporate any announcements, hold music, etc. prior to being answered, or it can have recording start at the time that call is answered.");
echo $helptext;
?>

<form name="editCallRecording" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkCallRecording(editCallRecording);">
	<input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
	<input type="hidden" name="callrecording_id" value="<?php echo $extdisplay; ?>">
	<input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
	<table>
	<tr><td colspan="2"><h5><?php  echo ($extdisplay ? _("Edit Call Recording Instance") : _("Add Call Recording Instance")) ?><hr></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Description")?>:<span><?php echo _("The descriptive name of this call recording instance. For example \"French Main IVR\"")?></span></a></td>
		<td><input size="30" type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>


	<tr>
    <td><a href="#" class="info"><?php echo _("Call Recording Mode")?>:<span><?php echo _("Controls or overrides the call recording behavior for calls continuing through this call flow. Allow will honor the normal downstream call recording settings. Record on Answer starts recording when the call would otherwise be recorded ignoring any settings that say otherwise. Record Immediately will start recording right away capturing ringing, announcements, MoH, etc. Never will disallow recording regardless of downstream settings.")?></span></a></td>
<?php
		$callrecording_html = '<td><select name="callrecording_mode" tabindex="' . ++$tabindex . '">'."\n";
    $callrecording_html.= '<option value=""' . ($callrecording_mode == ''  ? ' SELECTED' : '').'>'._("Allow")."\n";
    $callrecording_html.= '<option value="delayed"'. ($callrecording_mode == 'delayed' ? ' SELECTED' : '').'>'._("Record on Answer")."\n";
    $callrecording_html.= '<option value="force"'  . ($callrecording_mode == 'force'   ? ' SELECTED' : '').'>'._("Record Immediately")."\n";
    $callrecording_html.= '<option value="never"' . ($callrecording_mode == 'never'  ? ' SELECTED' : '').'>'._("Never")."\n";
    $callrecording_html.= "</select></td></tr>\n";
    echo $callrecording_html;
?>
	<tr><td colspan="2"><br><h5><?php echo _("Destination")?>:<hr></h5></td></tr>

<?php 
//draw goto selects
echo drawselects($dest,0);
?>
			
	<tr>
		<td colspan="2"><br><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>">
			<?php if ($extdisplay) { echo '&nbsp;<input name="delete" type="submit" value="'._("Delete").'">'; } ?>
		</td>		

		<?php
		if ($extdisplay) {
			$usage_list = framework_display_destination_usage(callrecording_getdest($extdisplay));
			if (!empty($usage_list)) {
			?>
				<tr><td colspan="2">
				<a href="#" class="info"><?php echo $usage_list['text']?>:<span><?php echo $usage_list['tooltip']?></span></a>
				</td></tr>
			<?php
			}
		}
		?>
	</tr>
</table>
</form>

<script language="javascript">
<!--

function checkCallRecording(theForm) {
	var msgInvalidDescription = "<?php echo _('Invalid description specified'); ?>";

	// set up the Destination stuff
	setDestinations(theForm, '_post_dest');

	// form validation
	defaultEmptyOK = false;	
	if (isEmpty(theForm.description.value))
		return warnInvalid(theForm.description, msgInvalidDescription);

	if (!validateDestinations(theForm, 1, true))
		return false;

	return true;
}
//-->
</script>
