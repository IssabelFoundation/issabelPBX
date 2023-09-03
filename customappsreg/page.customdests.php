<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$tabindex = 0;
$display = 'customdests';

$type   = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'tool';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if (isset($_REQUEST['delete'])) $action = 'delete'; 

$old_extdisplay = isset($_REQUEST['old_extdisplay']) ? $_REQUEST['old_extdisplay'] :  '';
$extdisplay     = isset($_REQUEST['extdisplay']) ? $_REQUEST['extdisplay'] :  '';
$description     = isset($_REQUEST['description']) ? htmlentities($_REQUEST['description']) :  '';
$notes           = isset($_REQUEST['notes']) ? htmlentities($_REQUEST['notes']) :  '';

switch ($action) {
	case 'add':
		if (customappsreg_customdests_add($extdisplay, $description, $notes)) {
			needreload();
            $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been added'));
            $_SESSION['msgtype']='success';
            $_SESSION['msgtstamp']=time();
			redirect_standard('extdisplay');
		} else {
			$extdisplay='';
		}
	break;
	case 'edit':
		if (customappsreg_customdests_edit($old_extdisplay, $extdisplay, $description, $notes)) {
			needreload();
            $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
            $_SESSION['msgtype']='success';
            $_SESSION['msgtstamp']=time();
			redirect_standard('extdisplay');
		}
	break;
	case 'delete':
		customappsreg_customdests_delete($extdisplay);
		needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        $_SESSION['msgtstamp']=time();
		redirect_standard();
	break;
}

$rnaventries = array();
$list   = customappsreg_customdests_list();
foreach($list as $row) {
	$descr = $row['description'] != '' ? $row['description'] : '('.$row['custom_dest'].')';
    $rnaventries[] = array($row['custom_dest'],$descr,'','');
}
drawListMenu($rnaventries, $type, $display, $extdisplay);
?> 
<div class='content'>
<?php

if ($extdisplay != '') {
	// load
	$row = customappsreg_customdests_get($extdisplay);
	$description = $row['description'];
	$notes       = $row['notes'];
	$disp_description = $row['description'] != '' ? $row['description'] : '('.$row['custom_dest'].')';
} 

$helptext = __("Custom Destinations allows you to register your custom destinations that point to custom dialplans and will also 'publish' these destinations as available destinations to other modules. This is an advanced feature and should only be used by knowledgeable users. If you are getting warnings or errors in the notification panel about CUSTOM destinations that are correct, you should include them here. The 'Unknown Destinations' chooser will allow you to choose and insert any such destinations that the registry is not aware of into the Custom Destination field.");

$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

echo "<div class='is-flex'><h2>".($extdisplay ? __('Edit Custom Destination').': '.$disp_description : __("Add Custom Destination"))."</h2>$help</div>\n";

if ($extdisplay) {
	$usage_list = framework_display_destination_usage(customappsreg_customdests_getdest($extdisplay));
    if (!empty($usage_list)) {
        echo ipbx_usage_info($usage_list['text'],$usage_list['tooltip']);
    }
}


?>

<form id="mainform" name="editCustomDest" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkCustomDest(this);">
<?php
if (!empty($usage_list)) {
?>
	<input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
<?php
}
?>
	<input type="hidden" name="old_extdisplay" value="<?php echo $extdisplay; ?>">
	<input type="hidden" name="action" value="<?php echo ($extdisplay != '' ? 'edit' : 'add'); ?>">
	<table>
	<tr><td colspan="2"><h5><?php  echo _dgettext("amp","General Settings")?></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Custom Destination")?>
			<span>
				<?php 
				echo __("This is the Custom Destination to be published. It should be formatted exactly as you would put it in a goto statement, with context, exten, priority all included. An example might look like:<br />mycustom-app,s,1");
				if (!empty($usage_list)) {
					echo "<br />".__("READONLY WARNING: Because this destination is being used by other module objects it can not be edited. You must remove those dependencies in order to edit this destination, or create a new destination to use");
				}
				?>
			</span></a></td>
	<?php
	if (!empty($usage_list)) {
	?>
		<td><b><?php echo htmlentities($extdisplay); ?></b></td>
	<?php
	} else {
	?>
		<td><input class="input w100" type="text" name="extdisplay" id="extdisplay" value="<?php  echo $extdisplay; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	<?php
	}
	?>
	</tr>

	<?php
	if (empty($usage_list)) {
	?>
	<tr>
		<td>
		<a href=# class="info"><?php echo __("Destination Quick Pick")?>
			<span>
				<?php echo __("Choose un-identified destinations on your system to add to the Custom Destination Registry. This will insert the chosen entry into the Custom Destination box above.")?>
			</span>
		</a>
		</td>
		<td>
			<select class='componentSelect' onChange="insertDest();" id="insdest" tabindex="<?php echo ++$tabindex;?>">
				<option value=""><?php echo __("(pick destination)")?></option>
	<?php
				$results = customappsreg_customdests_getunknown();
				foreach ($results as $thisdest) {
					echo "<option value='$thisdest'>$thisdest</option>\n";
				}
	?>
			</select>
		</td>
	</tr>
	<?php
	}
	?>

	<tr>
		<td><a href="#" class="info"><?php echo __("Description")?><span><?php echo __("Brief Description that will be published to modules when showing destinations. Example: My Weather App")?></span></a></td>
		<td><input class="input w100" type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td valign="top"><a href="#" class="info"><?php echo __("Notes")?><span><?php echo __("More detailed notes about this destination to help document it. This field is not used elsewhere.")?></span></a></td>
		<td><textarea class="textarea" name="notes" cols="23" rows="6" tabindex="<?php echo ++$tabindex;?>"><?php echo $notes; ?></textarea></td> 
	</tr>

	</table>
	</form>
			
<script>

function insertDest() {

	dest = document.getElementById('insdest').value;
	customDest=document.getElementById('extdisplay');

	if (dest != '') {
		customDest.value = dest;
	}

	// reset element
	document.getElementById('insdest').value = '';
}

function checkCustomDest(theForm) {

	var msgInvalidCustomDest = "<?php echo __('Invalid Destination, must not be blank, must be formatted as: context,exten,pri'); ?>";
	var msgInvalidDescription = "<?php echo __('Invalid description specified, must not be blank'); ?>";

	// Make sure the custom dest is in the form "context,exten,pri"
	var re = /[^,]+,[^,]+,[^,]+/;

	// form validation
	defaultEmptyOK = false;	

	if (isEmpty(theForm.extdisplay.value) || !re.test(theForm.extdisplay.value)) {
		return warnInvalid(theForm.extdisplay, msgInvalidCustomDest);
	}
	if (isEmpty(theForm.description.value)) {
		return warnInvalid(theForm.description, msgInvalidDescription);
	}
    $.LoadingOverlay('show');
	return true;
}
<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
