<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'setup';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] :  '';
if (isset($_REQUEST['delete'])) $action = 'delete'; 

$queueprio_id = isset($_REQUEST['queueprio_id']) ? $_REQUEST['queueprio_id'] :  false;
$description = isset($_REQUEST['description']) ? $_REQUEST['description'] :  '';
$queue_priority = isset($_REQUEST['queue_priority']) ? $_REQUEST['queue_priority'] :  '';
$dest = isset($_REQUEST['dest']) ? $_REQUEST['dest'] :  '';

if (isset($_REQUEST['goto0']) && $_REQUEST['goto0']) {
	$dest = $_REQUEST[ $_REQUEST['goto0'].'0' ];
}

switch ($action) {
	case 'add':
		$_REQUEST['extdisplay'] = queueprio_add($description, $queue_priority, $dest);
		needreload();
		redirect_standard('extdisplay');
	break;
	case 'edit':
		queueprio_edit($queueprio_id, $description, $queue_priority, $dest);
		needreload();
		redirect_standard('extdisplay');
	break;
	case 'delete':
		queueprio_delete($queueprio_id);
		needreload();
		redirect_standard();
	break;
}

?> 
<div class="rnav"><ul>
<?php 

echo '<li><a href="config.php?display=queueprio&amp;type='.$type.'">'._('Add Queue Priority').'</a></li>';

foreach (queueprio_list() as $row) {
	echo '<li><a href="config.php?display=queueprio&amp;type='.$type.'&amp;extdisplay='.$row['queueprio_id'].'" class="">'.$row['description'].'</a></li>';
}

?>
</ul></div>

<?php

if ($extdisplay) {
	// load
	$row = queueprio_get($extdisplay);
	
	$description = $row['description'];
	$queue_priority   = $row['queue_priority'];
	$dest        = $row['dest'];

	echo "<h2>"._("Edit: ")."$description ($queue_priority)"."</h2>";
} else {
	echo "<h2>"._("Add Queue Priority")."</h2>";
}

$helptext = _("Queue Priority allows you to set a caller's priority in a queue. By default, a caller's priority is set to 0. Setting a higher priority will put the caller ahead of other callers already in a queue. The priority will apply to any queue that this caller is eventually directed to. You would typically set the destination to a queue, however that is not necessary. You might set the destination of a priority customer DID to an IVR that is used by other DIDs, for example, and any subsequent queue that is entered would be entered with this priority");
echo $helptext;
?>

<form name="editQueuePriority" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkQueuePriority(editQueuePriority);">
	<input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
	<input type="hidden" name="queueprio_id" value="<?php echo $extdisplay; ?>">
	<input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
	<table>
	<tr><td colspan="2"><h5><?php  echo ($extdisplay ? _("Edit Queue Priority Instance") : _("Add Queue Priority Instance")) ?><hr></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Description")?>:<span><?php echo _("The descriptive name of this Queue Priority instance.")?></span></a></td>
		<td><input size="30" type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Priority")?>:<span><?php echo _("The Queue Priority to set")?></span></a></td>
		<td>
			<select name="queue_priority" tabindex="<?php echo ++$tabindex;?>">
			<?php 
				$default = (isset($queue_priority) ? $queue_priority : 0);
				for ($i=0; $i <= 20; $i++) {
					echo '<option value="'.$i.'" '.($i == $default ? 'SELECTED' : '').'>'.$i.'</option>';
				}
			?>		
			</select>		
		</td>
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
			$usage_list = framework_display_destination_usage(queueprio_getdest($extdisplay));
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

<script queueprio="javascript">
<!--

function checkQueuePriority(theForm) {
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
