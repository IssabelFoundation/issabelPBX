<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';

//the item we are currently displaying
isset($_REQUEST['itemid'])?$itemid=$_REQUEST['itemid']:$itemid='';

$dispnum = "callback"; //used for switch on config.php

$tabindex = 0;

//if submitting form, update database
if(isset($_POST['action'])) {
	switch ($action) {
		case "add":
			$_REQUEST['itemid'] = callback_add($_POST);
			needreload();
			redirect_standard('itemid');
		break;
		case "delete":
			callback_del($itemid);
			needreload();
			redirect_standard();
		break;
		case "edit":
			callback_edit($itemid,$_POST);
			needreload();
			redirect_standard('itemid');
		break;
	}
}

//get list of time conditions
$callbacks = callback_list();
?>

<!-- right side menu -->
<div class="rnav"><ul>
    <li><a id="<?php echo ($itemid=='' ? 'current':'') ?>" href="config.php?display=<?php echo urlencode($dispnum)?>"><?php echo _("Add Callback")?></a></li>
<?php
if (isset($callbacks)) {
	foreach ($callbacks as $callback) {
		echo "<li><a id=\"".($itemid==$callback['callback_id'] ? 'current':'')."\" href=\"config.php?display=".urlencode($dispnum)."&itemid=".urlencode($callback['callback_id'])."\">{$callback['description']}</a></li>";
	}
}
?>
</ul></div>

<?php
if ($action == 'delete') {
	echo '<br><h3>'._("Callback ").' '.$itemid.' '._("deleted").'!</h3>';
} else {
	if ($itemid){ 
		//get details for this time condition
		$thisItem = callback_get($itemid);
	}

	$delURL = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&action=delete';
	$delButton = "
			<form name=delete action=\"{$_SERVER['PHP_SELF']}\" method=POST>
				<input type=\"hidden\" name=\"display\" value=\"{$dispnum}\">
				<input type=\"hidden\" name=\"itemid\" value=\"{$itemid}\">
				<input type=\"hidden\" name=\"action\" value=\"delete\">
				<input type=submit value=\""._("Delete Callback")."\">
			</form>";
	
?>

	<h2><?php echo ($itemid ? _("Callback:")." ". $itemid : _("Add Callback")); ?></h2>
	
	<p><?php echo ($itemid ? '' : _("A callback will hang up on the caller and then call them back, directing them to the selected destination. This is useful for reducing mobile phone charges as well as other applications. Outbound calls will proceed according to the dial patterns in Outbound Routes.")); ?></p>

<?php		if ($itemid) {
					echo $delButton; 	
					$usage_list = framework_display_destination_usage(callback_getdest($itemid));
					if (!empty($usage_list)) {
?>
						<a href="#" class="info"><?php echo $usage_list['text']?>:<span><?php echo $usage_list['tooltip']?></span></a>
<?php
					}
				}
?>


<form autocomplete="off" name="edit" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return edit_onsubmit();">
	<input type="hidden" name="display" value="<?php echo $dispnum?>">
	<input type="hidden" name="action" value="<?php echo ($itemid ? 'edit' : 'add') ?>">
	<input type="hidden" name="deptname" value="<?php echo $_SESSION["AMP_user"]->_deptname ?>">
	<table>
	<tr><td colspan="2"><h5><?php echo ($itemid ? _("Edit Callback") : _("Add Callback")) ?><hr></h5></td></tr>

<?php		if ($itemid){ ?>
		<input type="hidden" name="account" value="<?php echo $itemid; ?>">
<?php		}?>

	<tr>
		<td><a href="#" class="info"><?php echo _("Callback Description:")?><span><?php echo _("Enter a description for this callback.")?></span></a></td>
		<td><input type="text" name="description" value="<?php echo (isset($thisItem['description']) ? $thisItem['description'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Callback Number:")?><span><?php echo _("Optional: Enter the number to dial for the callback.  Leave this blank to just dial the incoming CallerID Number")?></span></a></td>
		<td><input type="text" name="callbacknum" value="<?php echo (isset($thisItem['callbacknum']) ? $thisItem['callbacknum'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Delay Before Callback:")?><span><?php echo _("Optional: Enter the number of seconds the system should wait before calling back.")?></span></a></td>
		<td><input size="3" type="text" name="sleep" value="<?php echo (isset($thisItem['sleep']) ? $thisItem['sleep'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr><td colspan="2"><br><h5><?php echo _("Destination after Callback")?>:<hr></h5></td></tr>

<?php 
//draw goto selects
if (isset($thisItem)) {
	echo drawselects($thisItem['destination'],0);
} else { 
	echo drawselects(null, 0);
}
?>

	<tr>
		<td colspan="2"><br><h6><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>"></h6></td>		
	</tr>
	</table>
<script language="javascript">
<!--

var theForm = document.edit;
theForm.description.focus();

function edit_onsubmit() {
	setDestinations(edit,1);
	
	defaultEmptyOk = false;
	<?php if (function_exists('module_get_field_size')) { ?>
		var sizeDisplayName = "<?php echo module_get_field_size('callback', 'description', 50); ?>";	
	
        	if (!isCorrectLength(theForm.description.value, sizeDisplayName))
        	        return warnInvalid(theForm.description, "<?php echo _('The callback description provided is too long.'); ?>")
	<?php } ?>
	if (!isAlphanumeric(theForm.description.value))
		return warnInvalid(theForm.description, "Please enter a valid Description");
		
	if (!validateDestinations(edit,1,true))
		return false;
	
	return true;
}


-->
</script>


	</form>
<?php		
} //end if action == delete
?>
