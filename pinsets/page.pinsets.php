<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';

//the item we are currently displaying
isset($_REQUEST['itemid'])?$itemid=$_REQUEST['itemid']:$itemid='';

$dispnum = "pinsets"; //used for switch on config.php

//if submitting form, update database
if(isset($_POST['action'])) {
	switch ($action) {
		case "add":
			pinsets_add($_POST);
			needreload();
			redirect_standard();
		break;
		case "delete":
			pinsets_del($itemid);
			needreload();
			redirect_standard();
		break;
		case "edit":
			pinsets_edit($itemid,$_POST);
			needreload();
			redirect_standard('itemid');
		break;
	}
}

//get list of time conditions
$pinsetss = pinsets_list();
?>

<!-- right side menu -->
<div class="rnav"><ul>
    <li><a id="<?php echo ($itemid=='' ? 'current':'') ?>" href="config.php?display=<?php echo urlencode($dispnum)?>"><?php echo _("Add PIN Set")?></a></li>
<?php
if (isset($pinsetss)) {
	foreach ($pinsetss as $pinsets) {
		echo "<li><a id=\"".($itemid==$pinsets['pinsets_id'] ? 'current':'')."\" href=\"config.php?display=".urlencode($dispnum)."&itemid=".urlencode($pinsets['pinsets_id'])."\">{$pinsets['description']}</a></li>";
	}
}
?>
</ul></div>
<?php
if ($action == 'delete') {
	echo '<br><h3>'._("PIN Set ").' '.$itemid.' '._("deleted").'!</h3>';
} else {
	if ($itemid){
		//get details for this time condition
		$thisItem = pinsets_get($itemid);
	}

	$delURL = '?'.$_SERVER['QUERY_STRING'].'&action=delete';
	$delButton = "
			<form name=delete action=\"\" method=POST>
				<input type=\"hidden\" name=\"display\" value=\"{$dispnum}\">
				<input type=\"hidden\" name=\"itemid\" value=\"{$itemid}\">
				<input type=\"hidden\" name=\"action\" value=\"delete\">
				<input type=submit value=\""._("Delete PIN Set")."\">
			</form>";

?>

	<h2><?php echo ($itemid ? _("PIN Set:")." ". $itemid : _("Add PIN Set")); ?></h2>

	<p><?php echo ($itemid ? '' : _("PIN Sets are used to manage lists of PINs that can be used to access restricted features such as Outbound Routes. The PIN can also be added to the CDR record's 'accountcode' field.")); ?></p>

<?php		if ($itemid){  echo $delButton; 	} ?>

<form autocomplete="off" name="edit" action="" method="post" onsubmit="return edit_onsubmit();">
	<input type="hidden" name="display" value="<?php echo $dispnum?>">
	<input type="hidden" name="action" value="<?php echo ($itemid ? 'edit' : 'add') ?>">
	<input type="hidden" name="deptname" value="<?php echo $_SESSION["AMP_user"]->_deptname ?>">

	<table>
	<tr><td colspan="2"><h5><?php echo ($itemid ? _("Edit PIN Set") : _("New PIN Set")) ?><hr></h5></td></tr>

<?php		if ($itemid){ ?>
		<input type="hidden" name="account" value="<?php echo $itemid; ?>">
<?php		}?>

	<tr>
		<td><?php echo _("PIN Set Description:")?></td>
		<td><input type="text" size=23 name="description" value="<?php echo (isset($thisItem['description']) ? $thisItem['description'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Record In CDR?:")?><span><?php echo _("Select this box if you would like to record the PIN in the call detail records when used")?></span></a></td>
		<td><input type="checkbox" name="addtocdr" value="1" <?php echo (isset($thisItem['addtocdr']) && $thisItem['addtocdr'] == '1' ? 'CHECKED' : ''); ?> tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("PIN List:")?><span><?php echo _("Enter a list of one or more PINs.  One PIN per line.")?></span></a></td>
		<td>
			<textarea rows=15 cols=20 name="passwords" tabindex="<?php echo ++$tabindex;?>"><?php echo (isset($thisItem['passwords']) ? $thisItem['passwords'] : ''); ?></textarea>
		</td>
	</tr>

	<tr>
		<td colspan="2"><br><h6><input name="submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>"></h6></td>
	</tr>
	</table>
<script language="javascript">
<!--

var theForm = document.edit;
theForm.description.focus();

function edit_onsubmit() {

	defaultEmptyOK = false;

	<?php if (function_exists('module_get_field_size')) { ?>
		var sizeDisplayName = "<?php echo module_get_field_size('pinsets', 'description', 50); ?>";
		if (!isCorrectLength(theForm.description.value, sizeDisplayName))
			return warnInvalid(theForm.description, "<?php echo _('The PIN Set Description provided is too long.'); ?>")
	<?php } ?>

	if (!isAlphanumeric(theForm.description.value))
		return warnInvalid(theForm.description, "<?php _("Please enter a valid Description") ?>");

	return true;
}

-->
</script>

	</form>
<?php
} //end if action == delete
?>
