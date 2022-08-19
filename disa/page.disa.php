 <?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//  License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Copyright 2013 Schmooze Com Inc.
//  Copyright 2022 Issabel Foundation

$tabindex = 0;
$action   = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$itemid   = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:'';
$dispnum  = "disa";

switch ($action) {
    case "add":
        $_REQUEST['itemid'] = disa_add($_POST);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
        $_SESSION['msgtype']='success';
        redirect_standard();
    break;
    case "delete":
        $oldItem = disa_get($itemid);
        disa_del($itemid);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        redirect_standard();
    break;
    case "edit":  //just delete and re-add
        disa_edit($itemid,$_POST);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        redirect_standard('extdisplay');
    break;
}

$disas = disa_list();
$rnavitems=array();
foreach($disas as $d) {
    $rnavitems[]=array($d['disa_id'],$d['displayname'],'');
}
drawListMenu($rnavitems, $type, $display, $extdisplay);
?>

<div class='content'>

<?php

$thisItem = disa_get($itemid);

if(is_null($thisItem)) {
    $thisItem['hangup']='';
    $thisItem['needconf']='';
    $thisItem['keepcid']=1;
}

$helptext = _('DISA is used to allow people from the outside world to call into your PBX and then be able to dial out of the PBX so it appears that their call is coming from the office which can be handy when traveling. You can set a destination in an IVR that points to the DISA or set a DID. Make sure you password protect this to keep people from dialing in and using your PBX to make calls out.');
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

echo "<div class='is-flex'><h2>".($itemid ? _('Edit DISA').': '.$thisItem["displayname"] : _("Add DISA"))."</h2>$help</div>\n";

if ($itemid) {
    $usage_list = framework_display_destination_usage(disa_getdest($itemid));
    if (!empty($usage_list)) {
        echo "<div class='tag is-success is-light ml-2'><a href='#' class='info'>${usage_list['text']}:<span>${usage_list['tooltip']}</span></a></div>";
    }
}
// Get hangup code for tooltip
//
$fcc = new featurecode('core', 'disconnect');
$hangup_code = $fcc->getCodeActive();
unset($fcc);
if ($hangup_code == "") {
    $hangup_code = '*';
}
?>
<form autocomplete="off" id="mainform" name="edit" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return edit_onsubmit(this);">
<input type="hidden" name="display" value="<?php echo $dispnum?>">
<input type="hidden" name="action" value="<?php echo ($itemid ? 'edit' : 'add') ?>">
<input type="hidden" name="deptname" value="<?php echo $_SESSION["AMP_user"]->_deptname ?>">
<table class='table is-borderless is-narrow'>

<tr><td colspan="2"><h5><?php echo dgettext('amp','General Settings');?></h5></td></tr>

<tr>
    <td><a href="#" class="info"><?php echo _("DISA name:")?><span><?php echo _("Give this DISA a brief name to help you identify it.")?></span></a></td>
    <td><input autofocus type="text" name="displayname" value="<?php echo htmlspecialchars(isset($thisItem['displayname']) ? $thisItem['displayname'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class='input w100'></td>
</tr>
<tr>
    <td><a href="#" class="info"><?php echo _("PIN"); ?><span><?php echo _("The user will be prompted for this number.")." "._("If you wish to have multiple PIN's, separate them with commas"); ?></span></a></td>
    <td><input type="text" name="pin" value="<?php echo htmlspecialchars(isset($thisItem['pin']) ? $thisItem['pin'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class='input w100'></td>
</tr>
<tr>
    <td><a href="#" class="info"><?php echo _("Response Timeout"); ?><span><?php echo _("The maximum amount of time it will wait before hanging up if the user has dialed an incomplete or invalid number. Default of 10 seconds"); ?></span></a></td>
    <td><input type="text" name="resptimeout" value="<?php echo htmlspecialchars(isset($thisItem['resptimeout']) ? $thisItem['resptimeout'] : '10'); ?>" tabindex="<?php echo ++$tabindex;?>" class='input w100'></td>
</tr>
<tr>
    <td><a href="#" class="info"><?php echo _("Digit Timeout"); ?><span><?php echo _("The maximum amount of time permitted between digits when the user is typing in an extension. Default of 5"); ?></span></a></td>
    <td><input type="text" name="digittimeout" value="<?php echo htmlspecialchars(isset($thisItem['digittimeout']) ? $thisItem['digittimeout'] : '5'); ?>" tabindex="<?php echo ++$tabindex;?>" class='input w100'></td>
</tr>
<tr>
    <td><a href="#" class="info"><?php echo _("Require Confirmation"); ?><span><?php echo _("Require Confirmation before prompting for password. Used when your PSTN connection appears to answer the call immediately"); ?></span></a></td>
    <td>
        <?php echo ipbx_yesno_checkbox("needconf",$thisItem['needconf'],false); ?>
    </td>
</tr>
<tr>
    <td><a href="#" class="info"><?php echo _("Caller ID"); ?><span><?php echo _("(Optional) When using this DISA, the users CallerID will be set to this. Format is \"User Name\" <5551234>"); ?></span></a></td>
    <td><input type="text" name="cid" value="<?php echo htmlspecialchars(isset($thisItem['cid']) ? $thisItem['cid'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class='input w100'></td>
</tr>
<tr>
    <td><a href="#" class="info"><?php echo _("Context"); ?><span><?php echo _("(Experts Only) Sets the context that calls will originate from. Leave this as from-internal unless you know what you're doing."); ?></span></a></td>
    <td><input type="text" name="context" value="<?php echo htmlspecialchars(isset($thisItem['context']) ? $thisItem['context'] : 'from-internal'); ?>" tabindex="<?php echo ++$tabindex;?>" class='input w100'></td>
</tr>
<tr>
    <td><a href="#" class="info"><?php echo _("Allow Hangup"); ?><span><?php echo sprintf(_("Allow the current call to be disconnected and dial tone presented for a new call by pressing the Hangup feature code: %s while in a call"),$hangup_code); ?></span></a></td>
    <td>
        <?php echo ipbx_yesno_checkbox("hangup",$thisItem['hangup'],false); ?>
    </td>
</tr>
<tr>
    <td><a href="#" class="info"><?php echo _("Caller ID Override"); ?><span><?php echo _("Determine if we keep the Caller ID being presented or if we override it. Default is Enable"); ?></span></a></td>
    <td>
        <?php echo ipbx_radio('keepcid',array(array('value'=>'0','text'=>_('Disable')),array('value'=>'1','text'=>_('Enable'))),$thisItem['keepcid'],false); ?>
    </td>
</tr>
</table>
</form>

<script>

function edit_onsubmit(theForm) {
    var msgInvalidDISAName = "<?php echo _('Please enter a valid DISA Name'); ?>";
    var msgInvalidDISAPIN = "<?php echo _('Please enter a valid DISA PIN'); ?>";
    var msgInvalidCID = "<?php echo _('Please enter a valid Caller ID or leave it blank'); ?>";
    var msgInvalidContext = "<?php echo _('Context cannot be blank'); ?>";

    defaultEmptyOK = false;

    <?php if (function_exists('module_get_field_size')) { ?>
        var sizeDisplayName = "<?php echo module_get_field_size('disa', 'displayname', 50); ?>";
        if (!isCorrectLength(theForm.displayname.value, sizeDisplayName))
            return warnInvalid(theForm.displayname, "<?php echo _('The DISA Name provided is too long.'); ?>")
    <?php } ?>

    if (!isAlphanumeric(theForm.displayname.value))
        return warnInvalid(theForm.displayname, msgInvalidDISAName);

    defaultEmptyOK = true;
    if (!isPINList(theForm.pin.value))
        return warnInvalid(theForm.pin, msgInvalidDISAPIN);

    defaultEmptyOK = true;
    if (!isCallerID(theForm.cid.value))
        return warnInvalid(theForm.cid, msgInvalidCID);

    defaultEmptyOK = false;
    if (isEmpty(theForm.context.value))
        return warnInvalid(theForm.context, msgInvalidContext);

    $.LoadingOverlay('show');
    return true;
}

<?php echo js_display_confirmation_toasts(); ?>
</script>

</div> <!-- end div content, be sure to include script tags before -->

<?php echo form_action_bar($extdisplay); ?>
