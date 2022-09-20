<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//  License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Copyright 2022 Issabel Foundation

$tabindex = 0;
isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';

//the item we are currently displaying
isset($_REQUEST['extdisplay'])?$itemid=$_REQUEST['extdisplay']:$itemid='';

$dispnum = "callback"; //used for switch on config.php

//if submitting form, update database
if(isset($_POST['action'])) {
    switch ($action) {
        case "add":
            $_REQUEST['itemid'] = callback_add($_POST);
            needreload();
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
            $_SESSION['msgtype']='success';
            $_SESSION['msgtstamp']=time();
            redirect_standard();
        break;
        case "delete":
            callback_del($itemid);
            needreload();
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
            $_SESSION['msgtype']='warning';
            $_SESSION['msgtstamp']=time();
            redirect_standard();
        break;
        case "edit":
            callback_edit($itemid,$_POST);
            needreload();
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
            $_SESSION['msgtype']='success';
            $_SESSION['msgtstamp']=time();
            redirect_standard('extdisplay');
        break;
    }
}

//get list of callbacks
$rnavitems = array();
$callbacks = callback_list();
foreach ($callbacks as $callback) {
    $rnavitems[]=array($callback['callback_id'],$callback['description'],'','');
}
drawListMenu($rnavitems, $type, $display, $extdisplay);
?>

<!--div class="rnav"><ul>
    <li><a class="<?php echo ($itemid=='' ? 'current':'') ?>" href="config.php?display=<?php echo urlencode($dispnum)?>"><?php echo _("Add Callback")?></a></li>
<?php
if (isset($callbacks)) {
    foreach ($callbacks as $callback) {
        echo "<li><a class=\"".($itemid==$callback['callback_id'] ? 'current':'')."\" href=\"config.php?display=".urlencode($dispnum)."&itemid=".urlencode($callback['callback_id'])."\">{$callback['description']}</a></li>";
    }
}
?>
</ul></div-->

<div class='content'>

<?php
$helptext = _("A callback will hang up on the caller and then call them back, directing them to the selected destination. This is useful for reducing mobile phone charges as well as other applications. Outbound calls will proceed according to the dial patterns in Outbound Routes."); 
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

echo "<div class='is-flex'><h2>".($extdisplay ? _('Edit Callback').': '.$description : _("Add Callback"))."</h2>$help</div>\n";

if ($itemid) {
    //get details for this time condition
    $thisItem = callback_get($itemid);

    // show usage list
    $usage_list = framework_display_destination_usage(callback_getdest($extdisplay));
    if (!empty($usage_list)) {
        echo ipbx_usage_info($usage_list['text'],$usage_list['tooltip']);
    }
}
?>

<form id="mainform" autocomplete="off" name="edit" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return edit_onsubmit(this);">
    <input type="hidden" name="display" value="<?php echo $dispnum?>">
    <input type="hidden" name="action" value="<?php echo ($itemid ? 'edit' : 'add') ?>">
    <input type="hidden" name="deptname" value="<?php echo $_SESSION["AMP_user"]->_deptname ?>">
<?php if ($itemid) { ?>
    <input type="hidden" name="account" value="<?php echo $itemid; ?>">
<?php } ?>
    <table class='table is-borderless is-narrow'>
    <tr><td colspan="2"><h5><?php echo ($itemid ? _("Edit Callback") : _("Add Callback")) ?></h5></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Callback Description:")?><span><?php echo _("Enter a description for this callback.")?></span></a></td>
        <td><input autofocus type="text" name="description" value="<?php echo (isset($thisItem['description']) ? $thisItem['description'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class="input w100"></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Callback Number:")?><span><?php echo _("Optional: Enter the number to dial for the callback.  Leave this blank to just dial the incoming CallerID Number")?></span></a></td>
        <td><input type="text" name="callbacknum" value="<?php echo (isset($thisItem['callbacknum']) ? $thisItem['callbacknum'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class="input w100"></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Delay Before Callback:")?><span><?php echo _("Optional: Enter the number of seconds the system should wait before calling back.")?></span></a></td>
        <td><input size="3" type="text" name="sleep" value="<?php echo (isset($thisItem['sleep']) ? $thisItem['sleep'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class="input w100"></td>
    </tr>
    <tr><td colspan="2"><br><h5><?php echo _("Destination after Callback")?></h5></td></tr>

<?php 
//draw goto selects
if (isset($thisItem)) {
    echo drawselects($thisItem['destination'],0);
} else { 
    echo drawselects(null, 0);
}
?>

</table>
</form>
<script>

function edit_onsubmit(theForm) {
    setDestinations(edit,1);
    
    defaultEmptyOk = false;
    <?php if (function_exists('module_get_field_size')) { ?>
        var sizeDisplayName = "<?php echo module_get_field_size('callback', 'description', 50); ?>";    
    
    if (!isCorrectLength(theForm.description.value, sizeDisplayName)) {
        return warnInvalid(theForm.description, "<?php echo _('The callback description provided is too long.'); ?>")
    }
    <?php } ?>
    if (!isAlphanumeric(theForm.description.value)) {
        return warnInvalid(theForm.description, "Please enter a valid Description");
    }
        
    if (!validateDestinations(edit,1,true)) {
        return false;
    }
    
    $.LoadingOverlay('show');
    return true;
}

<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
