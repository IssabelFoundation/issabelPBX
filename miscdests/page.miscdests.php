<?php /* $Id: $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';
isset($_REQUEST['id'])?$extdisplay = $_REQUEST['id']:$extdisplay='';

$dispnum = "miscdests"; //used for switch on config.php

switch ($action) {
	case "add":
		$_REQUEST['id'] = miscdests_add($_REQUEST['description'],$_REQUEST['destdial']);
		needreload();
		redirect_standard('id');
	break;
	case "delete":
		miscdests_del($extdisplay);
		needreload();
		redirect_standard();
	break;
	case "edit":  //just delete and re-add
		miscdests_update($extdisplay,$_REQUEST['description'],$_REQUEST['destdial']);
		needreload();
		redirect_standard('id');
	break;
}

$miscdests = miscdests_list();

?>


<!-- right side menu -->
<div class="rnav"><ul>
    <li><a id="<?php echo ($extdisplay=='' ? 'current':'') ?>" href="config.php?display=<?php echo urlencode($dispnum)?>"><?php echo _("Add Misc Destination")?></a></li>
<?php
if (isset($miscdests)) {
	foreach ($miscdests as $miscdest) {
		echo "<li><a id=\"".($extdisplay==$miscdest[0] ? 'current':'')."\" href=\"config.php?display=".urlencode($dispnum)."&id=".urlencode($miscdest[0])."\">{$miscdest[1]}</a></li>";
	}
}
?>
</ul></div>

<?php
if ($action == 'delete') {
	echo '<br><h3>'._("Misc Destination").' '.$extdisplay.' '._("deleted").'!</h3><br><br><br><br><br><br><br><br>';
} else {
	if ($extdisplay){
		//get details for this meetme
		$thisMiscDest = miscdests_get($extdisplay);
		//create variables
		$description = "";
		$destdial = "";
		extract($thisMiscDest);
	}

	$helptext = _("Misc Destinations are for adding destinations that can be used by other IssabelPBX modules, generally used to route incoming calls. If you want to create feature codes that can be dialed by internal users and go to various destinations, please see the <strong>Misc Applications</strong> module.").' '._('If you need access to a Feature Code, such as *98 to dial voicemail or a Time Condition toggle, these destinations are now provided as Feature Code Admin destinations. For upgrade compatibility, if you previously had configured such a destination, it will still work but the Feature Code short cuts select list is not longer provided.');

		if ($extdisplay){ ?>
	<h2><?php echo _("Misc Destination:")." ". $description; ?></h2>
<?php
			$usage_list = framework_display_destination_usage(miscdests_getdest($extdisplay));
			$delURL = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&action=delete';
			$tlabel = sprintf(_("Delete Misc Destination %s"),$description);
			$label = '<span><img width="16" height="16" border="0" title="'.$tlabel.'" alt="" src="images/core_delete.png"/>&nbsp;'.$tlabel.'</span>';
?>
			<a href="<?php echo $delURL ?>"><?php echo $label; ?></a>
<?php
			if (!empty($usage_list)) {
?>
				<br /><a href="#" class="info"><?php echo $usage_list['text']?>:<span><?php echo $usage_list['tooltip']?></span></a>
<?php
			}
		} else {
			echo "<h2>"._("Add Misc Destination")."</h2>";
			echo $helptext;
		}
?>
	<form autocomplete="off" name="editMD" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return editMD_onsubmit();">
	<input type="hidden" name="display" value="<?php echo $dispnum?>">
	<input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add') ?>">
	<table>
	<tr><td colspan="2"><h5><?php echo ($extdisplay ? _("Edit Misc Destination") : _("Add Misc Destination")) ?><hr></h5></td></tr>
<?php		if ($extdisplay){ ?>
		<tr><td><input type="hidden" name="id" value="<?php echo $extdisplay; ?>"></td></tr>
<?php		} ?>
	<tr>
		<td><a href="#" class="info"><?php echo _("Description:")?><span><?php echo _("Give this Misc Destination a brief name to help you identify it.")?></span></a></td>
		<td><input type="text" name="description" value="<?php echo (isset($description) ? $description : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Dial:")?><span><?php echo _("Enter the number this destination will simulate dialing, exactly as you would dial it from an internal phone. When you route a call to this destination, it will be as if the caller dialed this number from an internal phone.") ?></span></a></td>
		<td>
			<input type="text" name="destdial" value="<?php echo (isset($destdial) ? $destdial : ''); ?>" tabindex="<?php echo ++$tabindex;?>">&nbsp;&nbsp;
		</td>
	</tr>
	<tr>
		<td colspan="2"><br><h6><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>"></h6>
		</td>
	</tr>
	</table>
<script language="javascript">
<!--

var theForm = document.editMD;

if (theForm.description.value == "") {
	theForm.description.focus();
} else {
	theForm.destdial.focus();
}

function editMD_onsubmit()
{
	var msgInvalidDescription = "<?php echo _('Please enter a valid Description'); ?>";
	var msgInvalidDial = "<?php echo _('Please enter a valid Dial string'); ?>";

	defaultEmptyOK = false;

	<?php if (function_exists('module_get_field_size')) { ?>
		var sizeDisplayName = "<?php echo module_get_field_size('miscdests', 'description', 100); ?>";
		if (!isCorrectLength(theForm.description.value, sizeDisplayName))
			return warnInvalid(theForm.description, "<?php echo _('The description provided is too long.'); ?>")
	<?php } ?>
	
	if (!isAlphanumeric(theForm.description.value))
		return warnInvalid(theForm.description, msgInvalidDescription);

	// go thru text and remove the {} bits so we only check the actual dial digits
	var fldText = theForm.destdial.value;
	var chkText = "";

	if ( (fldText.indexOf("{") > -1) && (fldText.indexOf("}") > -1) ) { // has one or more sets of {mod:fc}

		var inbraces = false;
		for (var i=0; i<fldText.length; i++) {
			if ( (fldText.charAt(i) == "{") && (inbraces == false) ) {
				inbraces = true;
			} else if ( (fldText.charAt(i) == "}") && (inbraces == true) ) {
				inbraces = false;
			} else if ( inbraces == false ) {
				chkText += fldText.charAt(i);
			}
		}

		// if there is nothing in chkText but something in fldText
		// then the field must contain a featurecode only, therefore
		// there really is something in thre!
		if ( (chkText == "") & (fldText != "") )
			chkText = "0";

	} else {
		chkText = fldText;
	}
	// now do the check using the chkText var made above
	if (!isDialDigits(chkText))
		return warnInvalid(theForm.destdial, msgInvalidDial);

	return true;
}
//-->
</script>
	</form>
<?php
} //end if action == delGRP
?>
