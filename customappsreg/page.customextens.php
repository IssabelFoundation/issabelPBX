<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$tabindex = 0;
$display = 'customextens';

$type   = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'tool';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if (isset($_REQUEST['delete'])) $action = 'delete'; 

$custom_exten = preg_replace("/[^0-9*#]/" ,"", trim($custom_exten));

$old_custom_exten = isset($_REQUEST['old_custom_exten']) ? preg_replace("/[^0-9*#]/" ,"",$_REQUEST['old_custom_exten']) :  '';
$custom_exten     = isset($_REQUEST['extdisplay']) ? preg_replace("/[^0-9*#]/" ,"",$_REQUEST['extdisplay']) :  '';
$description     = isset($_REQUEST['description']) ? htmlentities($_REQUEST['description']) :  '';
$notes           = isset($_REQUEST['notes']) ? htmlentities($_REQUEST['notes']) :  '';

switch ($action) {
	case 'add':
		$conflict_url = array();
		$usage_arr = framework_check_extension_usage($custom_exten);
		if (!empty($usage_arr)) {
			$conflict_url = framework_display_extension_usage_alert($usage_arr);
			$custom_exten='';
		} else {
			if (customappsreg_customextens_add($custom_exten, $description, $notes)) {
				needreload();
				redirect_standard();
			} else {
				$custom_exten='';
			}
		}
	break;
	case 'edit':
		$conflict_url = array();
		if ($old_custom_exten != $custom_exten) {
			$usage_arr = framework_check_extension_usage($custom_exten);
			if (!empty($usage_arr)) {
				$conflict_url = framework_display_extension_usage_alert($usage_arr);
			}
		}
		if (empty($conflict_url)) {
			if (customappsreg_customextens_edit($old_custom_exten, $custom_exten, $description, $notes)) {
				needreload();
				redirect_standard('extdisplay');
			}
		}
	break;
	case 'delete':
		customappsreg_customextens_delete($custom_exten);
		needreload();
		redirect_standard();
	break;
}

?> 

<div class="rnav"><ul>
<?php 

echo '<li><a href="config.php?display='.$display.'&amp;type='.$type.'">'._('Add Custom Extension').'</a></li>';

foreach (customappsreg_customextens_list() as $row) {
	$descr = $row['description'] != '' ? $row['description'] : '('.$row['custom_exten'].')';
	echo '<li><a href="config.php?display='.$display.'&amp;type='.$type.'&amp;extdisplay='.$row['custom_exten'].'" class="">'.$descr.'</a></li>';
}

?>
</ul></div>

<?php

if ($custom_exten != '') {
	// load
	$row = customappsreg_customextens_get($custom_exten);
	
	$description = $row['description'];
	$notes       = $row['notes'];

	$disp_description = $row['description'] != '' ? '('.$row['custom_exten'].') '.$row['description'] : '('.$row['custom_exten'].')';
	echo "<h2>"._("Edit: ")."$disp_description"."</h2>";
} else {
	echo "<h2>"._("Add Custom Extension")."</h2>";
}

$helptext = _("Custom Extensions provides you with a facility to register any custom extensions or feature codes that you have created in a custom file and IssabelPBX doesn't otherwise know about them. This allows the Extension Registry to be aware of your own extensions so that it can detect conflicts or report back information about your custom extensions to other modules that may make use of the information. You should not put extensions that you create in the Misc Apps Module as those are not custom.");
echo $helptext;

if (!empty($conflict_url)) {
	echo "<h5>"._("Conflicting Extensions")."</h5>";
	echo implode('<br .>',$conflict_url);
}
?>

<form name="editCustomExten" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkCustomExten(editCustomExten);">
	<input type="hidden" name="extdisplay" value="<?php echo $custom_exten; ?>">
	<input type="hidden" name="old_custom_exten" value="<?php echo $custom_exten; ?>">
	<input type="hidden" name="action" value="<?php echo ($custom_exten != '' ? 'edit' : 'add'); ?>">
	<table>
	<tr><td colspan="2"><h5><?php  echo ($custom_exten ? _("Edit Custom Extension") : _("Add Custom Extension")) ?><hr></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Custom Extension")?>:<span><?php echo _("This is the Extension or Feature Code you are using in your dialplan that you want the IssabelPBX Extension Registry to be aware of.")?></span></a></td>
		<td><input size="10" type="text" name="extdisplay" id="extdisplay" value="<?php  echo $custom_exten; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>

	<tr>
		<td><a href="#" class="info"><?php echo _("Description")?>:<span><?php echo _("Brief description that will be published in the Extension Registry about this extension")?></span></a></td>
		<td><input size="30" type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td valign="top"><a href="#" class="info"><?php echo _("Notes")?>:<span><?php echo _("More detailed notes about this extension to help document it. This field is not used elsewhere.")?></span></a></td>
		<td><textarea name="notes" cols="23" rows="6" tabindex="<?php echo ++$tabindex;?>"><?php echo $notes; ?></textarea></td> 
	</tr>

	<tr>
		<td colspan="2"><br><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>">
		<?php if ($custom_exten != '') { echo '&nbsp;<input name="delete" type="submit" value="'._("Delete").'">'; } ?>
		</td>		
	</tr>
	</table>
	</form>
			
<script language="javascript">
<!--

function checkCustomExten(theForm) {

	var msgInvalidCustomExten = "<?php echo _('Invalid Extension, must not be blank'); ?>";
	var msgInvalidDescription = "<?php echo _('Invalid description specified, must not be blank'); ?>";

	// form validation
	defaultEmptyOK = false;	

	if (isEmpty(theForm.extdisplay.value)) {
		return warnInvalid(theForm.extdisplay, msgInvalidCustomExten);
	}
	if (isEmpty(theForm.description.value)) {
		return warnInvalid(theForm.description, msgInvalidDescription);
	}

	return true;
}
//-->
</script>
