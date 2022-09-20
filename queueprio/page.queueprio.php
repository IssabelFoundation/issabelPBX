<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
$tabindex = 0;
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'setup';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] :  '';
if (isset($_REQUEST['delete'])) $action = 'delete'; 

$queueprio_id = isset($_REQUEST['queueprio_id']) ? $_REQUEST['queueprio_id'] :  false;
$description = isset($_REQUEST['description']) ? $_REQUEST['description'] :  '';
$queue_priority = isset($_REQUEST['queue_priority']) ? $_REQUEST['queue_priority'] :  '';
$dest = isset($_REQUEST['dest']) ? $_REQUEST['dest'] :  '';

if (isset($_REQUEST['goto0']) && $_REQUEST['goto0']) {
	$dest = $_REQUEST[ $_REQUEST['goto0'] ];
}

switch ($action) {
case 'add':
		$_REQUEST['extdisplay'] = queueprio_add($description, $queue_priority, $dest);
		needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
		redirect_standard('extdisplay');
	break;
	case 'edit':
		queueprio_edit($queueprio_id, $description, $queue_priority, $dest);
		needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
		redirect_standard('extdisplay');
	break;
	case 'delete':
		queueprio_delete($queueprio_id);
		needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        $_SESSION['msgtstamp']=time();
		redirect_standard();
	break;
}

$rnaventries = array();
$data        = queueprio_list();
foreach ($data as $idx=>$row) {
    $rnaventries[] = array($row['queueprio_id'],'<span class="tag is-info">'.$row['queue_priority'].'</span> '.$row['description'],'');
}
drawListMenu($rnaventries, $type, $display, $extdisplay);
?> 
<div class='content'>
<?php

$helptext = _("Queue Priority allows you to set a caller's priority in a queue. By default, a caller's priority is set to 0. Setting a higher priority will put the caller ahead of other callers already in a queue. The priority will apply to any queue that this caller is eventually directed to. You would typically set the destination to a queue, however that is not necessary. You might set the destination of a priority customer DID to an IVR that is used by other DIDs, for example, and any subsequent queue that is entered would be entered with this priority");
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

if ($extdisplay) {
	// load
	$row = queueprio_get($extdisplay);
	
	$description    = $row['description'];
	$queue_priority = $row['queue_priority'];
	$dest           = $row['dest'];

    echo "<div class='is-flex'><h2>"._("Edit Queue Priority").": ".$description."</h2>$help</div>";
} else {
	echo "<div class='is-flex'><h2>"._("Add Queue Priority")."</h2>$help</div>";
}

if ($extdisplay != '') {
    $usage_list = framework_display_destination_usage(queueprio_getdest($extdisplay));
    if (!empty($usage_list)) {
        echo ipbx_usage_info($usage_list['text'],$usage_list['tooltip']);
    }
}
?>

<form name="editQueuePriority" id="mainform" method="post" onsubmit="return checkQueuePriority(this);">
	<input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
	<input type="hidden" name="queueprio_id" value="<?php echo $extdisplay; ?>">
	<input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
    <table class='table is-borderless is-narrow'>
    <tr><td colspan="2"><h5><?php  echo dgettext('amp','General Settings') ?></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Description")?><span><?php echo _("The descriptive name of this Queue Priority instance.")?></span></a></td>
		<td><input class='input w100' type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Priority")?><span><?php echo _("The Queue Priority to set")?></span></a></td>
		<td>
			<select name="queue_priority" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
			<?php 
				$default = (isset($queue_priority) ? $queue_priority : 0);
				for ($i=0; $i <= 20; $i++) {
					echo '<option value="'.$i.'" '.($i == $default ? 'SELECTED' : '').'>'.$i.'</option>';
				}
			?>		
			</select>		
		</td>
	<tr><td colspan="2"><br><h5><?php echo _("Destination")?></h5></td></tr>

<?php 
//draw goto selects
echo drawselects($dest,0);
?>
			
</table>
</form>

<script>

function checkQueuePriority(theForm) {
	var msgInvalidDescription = "<?php echo _('Invalid description specified'); ?>";

	// set up the Destination stuff
	//setDestinations(theForm, '_post_dest');

	// form validation
	defaultEmptyOK = false;	
	if (isEmpty(theForm.description.value))
		return warnInvalid(theForm.description, msgInvalidDescription);

	if (!validateDestinations(theForm, 1, true))
		return false;

    $.LoadingOverlay('show');
	return true;
}
<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
