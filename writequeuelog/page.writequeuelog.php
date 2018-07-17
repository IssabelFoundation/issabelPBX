<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$type    = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'setup';
$action  = isset($_REQUEST['action']) ? $_REQUEST['action'] :  '';

if (isset($_REQUEST['delete'])) $action = 'delete';

$qlog_id       = isset($_REQUEST['qlog_id']) ? $_REQUEST['qlog_id'] :  false;
$description   = isset($_REQUEST['description']) ? $_REQUEST['description'] :  '';
$dest          = isset($_REQUEST['dest']) ? $_REQUEST['dest'] :  '';
$qlog_uniqueid = isset($_REQUEST['qlog_uniqueid']) ? $_REQUEST['qlog_uniqueid'] :  '';
$qlog_agent    = isset($_REQUEST['qlog_agent']) ? $_REQUEST['qlog_agent'] :  '';
$qlog_event    = isset($_REQUEST['qlog_event']) ? $_REQUEST['qlog_event'] :  '';
$qlog_queue    = isset($_REQUEST['qlog_queue']) ? $_REQUEST['qlog_queue'] :  '';
$qlog_extra    = isset($_REQUEST['qlog_extra']) ? $_REQUEST['qlog_extra'] :  '';

$add_field = _("Add Field");

if (isset($_REQUEST['goto0']) && $_REQUEST['goto0']) {
    $dest = $_REQUEST[ $_REQUEST['goto0'].'0' ];
}

switch ($action) {
    case 'add':
        writequeuelog_add($description, $qlog_uniqueid, $qlog_queue, $qlog_agent, $qlog_event, $qlog_extra, $dest);
        needreload();
        redirect_standard();
    break;
    case 'edit':
        writequeuelog_edit($qlog_id, $description, $qlog_uniqueid, $qlog_queue, $qlog_agent, $qlog_event, $qlog_extra, $dest);
        needreload();
        redirect_standard('extdisplay');
    break;
    case 'delete':
        writequeuelog_delete($qlog_id);
        needreload();
        redirect_standard();
    break;
}

?>
<div class="rnav"><ul>
<?php

echo '<li><a href="config.php?display=writequeuelog&amp;type='.$type.'">'._('Add Write Queue Log').'</a></li>';

foreach (writequeuelog_list() as $row) {
    echo '<li><a href="config.php?display=writequeuelog&amp;type='.$type.'&amp;extdisplay='.$row['qlog_id'].'" class="rnavdata" rnavdata="'.$row['description'].','.$row['qlog_uniqueid'].','.$row['qlog_queue'].','.$row['qlog_agent'].','.$row['qlog_event'].','.$row['dest'].'">'.$row['description'].'</a></li>';

}

?>
</ul></div>
<?php

if ($extdisplay) {
    // load
    $row = writequeuelog_get($extdisplay);
    $description = $row['description'];

    $qlog_uniqueid = $row['qlog_uniqueid'];
    $qlog_queue    = htmlspecialchars($row['qlog_queue']);
    $qlog_agent    = htmlspecialchars($row['qlog_agent']);
    $qlog_event    = htmlspecialchars($row['qlog_event']);
    $qlog_extra    = htmlspecialchars($row['qlog_extra']);
    $dest          = $row['dest'];

    echo "<h2>"._("Edit: ")."$description"."</h2>";

        $usage_list = framework_display_destination_usage(writequeuelog_getdest($extdisplay));
        if (!empty($usage_list)) {
        ?>
            <table><tr><td colspan="2">
            <a href="#" class="info"><?php echo $usage_list['text']?>:<span><?php echo $usage_list['tooltip']?></span></a>
            </td></tr></table><br /><br />
        <?php
        }

} else {
    echo "<h2>"._("Add Write Queue Log")."</h2>";
}

$helptext = _("Write queue log lets you append a line into the queue_log file for call center tracking purposes");
echo $helptext;

?>

<form name="editQueuelog" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkQueuelog(editQueuelog);">
    <input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="qlog_id" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
    <table>
    <tr><td colspan="2"><h5><?php  echo ($extdisplay ? _("Edit Queue Log Instance") : _("Add Queue Log Instance")) ?><hr></h5></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Description")?>:<span><?php echo _("The descriptive name of this queue log instance. For example \"new name here\"");?></span></a></td>
        <td><input size="30" type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Uniqueid")?>:<span><?php echo _("The uniqueid field for the queue log entry");?></span></a></td>
        <td><input size="30" type="text" name="qlog_uniqueid" value="<?php echo $qlog_uniqueid; ?>"  tabindex="<?php echo ++$tabindex;?>"/></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Queue")?>:<span><?php echo _("The queue field for the queue log entry");?></span></a></td>
        <td><input size="30" type="text" name="qlog_queue" value="<?php echo $qlog_queue; ?>"  tabindex="<?php echo ++$tabindex;?>"/></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Agent")?>:<span><?php echo _("The agent field for the queue log entry");?></span></a></td>
        <td><input size="30" type="text" name="qlog_agent" value="<?php echo $qlog_agent; ?>"  tabindex="<?php echo ++$tabindex;?>"/></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Event")?>:<span><?php echo _("The event field for the queue log entry");?></span></a></td>
        <td><input size="30" type="text" name="qlog_event" value="<?php echo $qlog_event; ?>"  tabindex="<?php echo ++$tabindex;?>"/></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Extra")?>:<span><?php echo _("The rest of extra data info1|info2|info3 for the queue log entry");?></span></a></td>
        <td><input size="30" type="text" name="qlog_extra" value="<?php echo $qlog_extra; ?>"  tabindex="<?php echo ++$tabindex;?>"/></td>
    </tr>

    <tr><td colspan="2"><br><h5><?php echo _("Destination")?>:<hr></h5></td></tr>

<?php
//draw goto selects
if($dest=='') { $dest='app-blackhole,hangup,1';  }
echo drawselects($dest,0);
?>

    <tr>
        <td colspan="2"><br><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>">
            <?php if ($extdisplay) { echo '&nbsp;<input name="delete" type="submit" value="'._("Delete").'">'; } ?>
        </td>

    </tr>
</table>
</form>

<script language="javascript">
<!--
$(document).ready(function () {

  if (!$('[name=description]').attr("value")) {
      $('[name=qlog_uniqueid]').attr({value: "${UNIQUEID}"});
      $('[name=qlog_queue]').attr({value: "${QUEUENUM}"});
  }

});


function checkQueuelog(theForm) {
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
