<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$tabindex = 0;
$display = 'customdests';

$type   = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'tool';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if (isset($_REQUEST['delete'])) $action = 'delete'; 

$old_custom_dest = isset($_REQUEST['old_custom_dest']) ? $_REQUEST['old_custom_dest'] :  '';
$custom_dest     = isset($_REQUEST['extdisplay']) ? $_REQUEST['extdisplay'] :  '';
$description     = isset($_REQUEST['description']) ? htmlentities($_REQUEST['description']) :  '';
$notes           = isset($_REQUEST['notes']) ? htmlentities($_REQUEST['notes']) :  '';

switch ($action) {
	case 'add':
		if (customappsreg_customdests_add($custom_dest, $description, $notes)) {
			needreload();
			redirect_standard('extdisplay');
		} else {
			$custom_dest='';
		}
	break;
	case 'edit':
		if (customappsreg_customdests_edit($old_custom_dest, $custom_dest, $description, $notes)) {
			needreload();
			redirect_standard('extdisplay');
		}
	break;
	case 'delete':
		customappsreg_customdests_delete($custom_dest);
		needreload();
		redirect_standard();
	break;
}

?> 
<div class="rnav"><ul>
<?php 

echo '<li><a href="config.php?display='.$display.'&amp;type='.$type.'">'._('Add Custom Destination').'</a></li>';

foreach (customappsreg_customdests_list() as $row) {
	$descr = $row['description'] != '' ? $row['description'] : '('.$row['custom_dest'].')';
	echo '<li><a href="config.php?display='.$display.'&amp;type='.$type.'&amp;extdisplay='.$row['custom_dest'].'" class="">'.$descr.'</a></li>';
}

?>
</ul></div>

<?php

if ($custom_dest != '') {
	// load
	$usage_list = framework_display_destination_usage(customappsreg_customdests_getdest($custom_dest));

	$row = customappsreg_customdests_get($custom_dest);
	
	$description = $row['description'];
	$notes       = $row['notes'];

	$disp_description = $row['description'] != '' ? $row['description'] : '('.$row['custom_dest'].')';
	echo "<h2>"._("Edit: ")."$disp_description"."</h2>";
} else {
	echo "<h2>"._("Add Custom Destination")."</h2>";
}

$helptext = _("Custom Destinations allows you to register your custom destinations that point to custom dialplans and will also 'publish' these destinations as available destinations to other modules. This is an advanced feature and should only be used by knowledgeable users. If you are getting warnings or errors in the notification panel about CUSTOM destinations that are correct, you should include them here. The 'Unknown Destinations' chooser will allow you to choose and insert any such destinations that the registry is not aware of into the Custom Destination field.");
echo $helptext;
?>

<form name="editCustomDest" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkCustomDest(editCustomDest);">
<?php
if (!empty($usage_list)) {
?>
	<input type="hidden" name="extdisplay" value="<?php echo $custom_dest; ?>">
<?php
}
?>
	<input type="hidden" name="old_custom_dest" value="<?php echo $custom_dest; ?>">
	<input type="hidden" name="action" value="<?php echo ($custom_dest != '' ? 'edit' : 'add'); ?>">
	<table>
	<tr><td colspan="2"><h5><?php  echo ($custom_dest ? _("Edit Custom Destination") : _("Add Custom Destination")) ?><hr></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Custom Destination")?>:
			<span>
				<?php 
				echo _("This is the Custom Destination to be published. It should be formatted exactly as you would put it in a goto statement, with context, exten, priority all included. An example might look like:<br />mycustom-app,s,1");
				if (!empty($usage_list)) {
					echo "<br />"._("READONLY WARNING: Because this destination is being used by other module objects it can not be edited. You must remove those dependencies in order to edit this destination, or create a new destination to use");
				}
				?>
			</span></a></td>
	<?php
	if (!empty($usage_list)) {
	?>
		<td><b><?php echo htmlentities($custom_dest); ?></b></td>
	<?php
	} else {
	?>
		<td><input size="30" type="text" name="extdisplay" id="extdisplay" value="<?php  echo $custom_dest; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	<?php
	}
	?>
	</tr>

	<?php
	if (empty($usage_list)) {
	?>
	<tr>
		<td>
		<a href=# class="info"><?php echo _("Destination Quick Pick")?>
			<span>
				<?php echo _("Choose un-identified destinations on your system to add to the Custom Destination Registry. This will insert the chosen entry into the Custom Destination box above.")?>
			</span>
		</a>
		</td>
		<td>
			<select onChange="insertDest();" id="insdest" tabindex="<?php echo ++$tabindex;?>">
				<option value=""><?php echo _("(pick destination)")?></option>
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
		<td><a href="#" class="info"><?php echo _("Description")?>:<span><?php echo _("Brief Description that will be published to modules when showing destinations. Example: My Weather App")?></span></a></td>
		<td><input size="30" type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td valign="top"><a href="#" class="info"><?php echo _("Notes")?>:<span><?php echo _("More detailed notes about this destination to help document it. This field is not used elsewhere.")?></span></a></td>
		<td><textarea name="notes" cols="23" rows="6" tabindex="<?php echo ++$tabindex;?>"><?php echo $notes; ?></textarea></td> 
	</tr>

	<tr>
		<td colspan="2"><br><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>">
		<?php if ($custom_dest != '') { echo '&nbsp;<input name="delete" type="submit" value="'._("Delete").'">'; } ?>
		</td>		

		<?php
		if ($custom_dest != '') {
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

	var msgInvalidCustomDest = "<?php echo _('Invalid Destination, must not be blank, must be formatted as: context,exten,pri'); ?>";
	var msgInvalidDescription = "<?php echo _('Invalid description specified, must not be blank'); ?>";

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

	return true;
}
//-->
</script>
