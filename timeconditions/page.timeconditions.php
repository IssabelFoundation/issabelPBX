<?php /* $Id */
//Copyright (C) 2004 Coalescent Systems Inc. (info@coalescentsystems.ca)
//
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';
//the item we are currently displaying
isset($_REQUEST['extdisplay'])?$itemid=$db->escapeSimple($_REQUEST['extdisplay']):$itemid='';

$dispnum = "timeconditions"; //used for switch on config.php
$tabindex = 0;

//if submitting form, update database
switch ($action) {
    case "add":
        $_REQUEST['itemid'] = timeconditions_add($_POST);
        needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been added'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        redirect_standard();
    break;
    case "delete":
        timeconditions_del($itemid);
        needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        $_SESSION['msgtstamp']=time();
        redirect_standard();
    break;
    case "edit":
        timeconditions_edit($itemid,$_POST);
        needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        redirect_standard('extdisplay');
    break;
}


//get list of time conditions
$data = timeconditions_list();
$groups=array();
foreach ($data as $idx=>$row) {
    $groups[] = array($row['timeconditions_id'],$row['displayname']);
}
drawListMenu($groups, $type, $display, $extdisplay);

?>


<div class='content' up-main>
<?php

    if ($itemid){
        $fcc = new featurecode('timeconditions', 'toggle-mode-'.$itemid);
        $code = $fcc->getCodeActive();
        unset($fcc);
    }

    $helptext = __("Creates a condition where calls will go to one of two destinations (eg, an extension, IVR, ring group..) based on the time and/or date. This can be used for example to ring a receptionist during the day, or go directly to an IVR at night.");
    $help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

    echo "<div class='is-flex'><h2>";
    echo ($itemid ? __("Edit Time Condition").": $code" : __("Add Time Condition"));
    echo "</h2>$help</div>";

    if($itemid) {

        $thisItem = timeconditions_get($itemid);

        $usage_list = framework_display_destination_usage(timeconditions_getdest($itemid));

        if (!empty($usage_list)) {
            echo ipbx_usage_info($usage_list['text'],$usage_list['tooltip']);
        }

        $tccode = $thisItem['tccode'] === false ? '' :  $thisItem['tccode'];

    } else {
        $tccode = '';
    }
    $tcval = isset($thisItem['tcval'])?$thisItem['tcval']:'';
?>
    <form autocomplete="off" id="mainform" name="edit" method="post" onsubmit="return edit_onsubmit(this);">
    <input type="hidden" name="display" value="<?php echo $dispnum?>">
    <input type="hidden" name="action" value="<?php echo ($itemid ? 'edit' : 'add') ?>">
    <input type="hidden" name="deptname" value="<?php echo $_SESSION["AMP_user"]->_deptname ?>">

<?php if ($itemid){ ?>
    <input type="hidden" name="account" value="<?php echo $itemid; ?>">
<?php } ?>

    <table class='table is-borderless is-narrow'>
    <tr><td colspan="2"><h5><?php echo _dgettext('amp','General Settings') ?></h5></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo __("Time Condition name")?><span><?php echo __("Give this Time Condition a brief name to help you identify it.")?></span></a></td>
        <td><input type="text" autofocus name="displayname" value="<?php echo (isset($thisItem['displayname']) ? $thisItem['displayname'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class='input w100'></td>
    </tr>
<?php
  if ($itemid && $thisItem['tcstate'] !== false) {
    $tcstate = $thisItem['tcstate'] == '' ? 'auto' : $thisItem['tcstate'];
    switch ($tcstate) {
      case 'auto':
        $state_msg = __('No Override');
      break;
      case 'true':
        $state_msg = __('Temporary Override matching state');
      break;
      case 'true_sticky':
        $state_msg = __('Permanent Override matching state');
      break;
      case 'false':
        $state_msg = __('Temporary Override unmatching state');
      break;
      case 'false_sticky':
        $state_msg = __('Permanent Override unmatching state');
      break;
      default:
        $state_msg = __('Unknown State');
      break;
    }
?>
  <tr>
        <td><a href="#" class="info"><?php echo __("Current Override")?><span><?php echo __("Indicates the current state of this Time Condition. If it is in a Temporary Override state, it will automatically resume at the next time transition based on the associated Time Group. If in a Permanent Override state, it will stay in that state until changed here or through other means such as external XML applications on your phone. If No Override then it functions normally based on the time schedule.")?></span></a></td>
    <td><?php echo $state_msg; ?></td>
    </tr>
  <tr>
        <td><a href="#" class="info"><?php echo __("Change Override")?><span><?php echo sprintf(__("This Time Condition can be set to Temporarily go to the 'matched' or 'unmatched' destination in which case the override will automatically reset once the current time span has elapsed. If set to Permanent it will stay overridden until manually reset. All overrides can be removed with the Reset Override option. Temporary Overrides can also be toggled with the %s feature code, which will also remove a Permanent Override if set but can not set a Permanent Override which must be done here or with other applications such as an XML based phone options."),$tcval)?></span></a></td>
    <td>
      <select name="tcstate_new" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
        <option value="unchanged" SELECTED><?php echo __("Unchanged");?></option>
        <option value="auto" ><?php echo __("Reset Override");?></option>
        <option value="true" ><?php echo __("Temporary matched");?></option>
        <option value="true_sticky" ><?php echo __("Permanently matched");?></option>
        <option value="false" ><?php echo __("Temporary unmatched");?></option>
        <option value="false_sticky" ><?php echo __("Permanently unmatched");?></option>
            </select>
        </td>
    </tr>
<?php } ?>
    <tr>
        <td><a href="#" class="info"><?php echo __("Time Group")?><span><?php echo __("Select a Time Group created under Time Groups. Matching times will be sent to matching destination. If no group is selected, call will always go to no-match destination.")?></span></a></td>
        <td><?php echo timeconditions_timegroups_drawgroupselect('time', (isset($thisItem['time']) ? $thisItem['time'] : ''), true, ''); ?></td>
    </tr>
<?php
    if (isset($thisItem['time']) && $thisItem['time'] != '') {

        $grpURL = $_SERVER['PHP_SELF'].'?display=timegroups&extdisplay='.$thisItem['time'];
        $tlabel = __("Goto Current Time Group");
        $label = "<a href='$grpURL' tabindex=".++$tabindex." class='button is-small is-rounded'> <span class='icon is-small'><i class='fa fa-clock-o'></i></span><span>$tlabel</span></a>";
?>
        <tr>
        <td colspan=2><?php echo $label;?></td>
        </tr>
<?php
    }
    // implementation of module hook
    // object was initialized in config.php
    echo process_tabindex($module_hook->hookHtml,$tabindex);
?>
    <tr><td colspan="2"><br><h5><?php echo __("Destination if time matches")?></h5></td></tr>
<?php
//draw goto selects
if (isset($thisItem)) {
    echo drawselects($thisItem['truegoto'],0);
} else {
    echo drawselects(null, 0);
}
?>

    <tr><td colspan="2"><br><h5><?php echo __("Destination if time does not match")?></h5></td></tr>

<?php
//draw goto selects
if (isset($thisItem)) {
    echo drawselects($thisItem['falsegoto'],1);
} else {
    echo drawselects(null, 1);
}

?>

    </table>
</form>

<script>

function edit_onsubmit(theForm) {
    var msgInvalidTimeCondName = "<?php echo __('Please enter a valid Time Conditions Name'); ?>";
    var msgInvalidTimeGroup = "<?php echo __('You have not selected a time group to associate with this timecondition. It will go to the un-matching destination until you update it with a valid group'); ?>";

    defaultEmptyOK = false;
    if (!isAlphanumeric(theForm.displayname.value))
        return warnInvalid(theForm.displayname, msgInvalidTimeCondName);

    if($('input[name="action"]').val() != 'delete') {
        if(typeof theForm.time == 'undefined') {
            return warnInvalid(theForm.time, msgInvalidTimeGroup);
        } else {
            if (isEmpty(theForm.time.value))
                return warnInvalid(theForm.time, msgInvalidTimeGroup);
        }
    }

    if (!validateDestinations(edit,2,true))
        return false;

    $.LoadingOverlay('show');
    return true;
}

<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->

<?php
echo form_action_bar($extdisplay);
?>
