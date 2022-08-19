<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
$tabindex = 0;
$display     = 'dahdichandids';
$type        = isset($_REQUEST['type'])        ? $_REQUEST['type']        :'setup';
$action      = isset($_REQUEST['action'])      ? $_REQUEST['action']      : '';
$extdisplay  = isset($_REQUEST['extdisplay'])  ? $_REQUEST['extdisplay']  : '';
$channel     = isset($_REQUEST['channel'])     ? $_REQUEST['channel']     : false;
$description = isset($_REQUEST['description']) ? $_REQUEST['description'] :  '';
$did         = isset($_REQUEST['did']) ? $_REQUEST['did']                 : '';
if (isset($_REQUEST['delete'])) $action = 'delete'; 

switch ($action) {
    case 'add':
        if (core_dahdichandids_add($description, $channel, $did)) {
            needreload();
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
            $_SESSION['msgtype']='success';
            redirect_standard();
        }
    break;
    case 'edit':
        if (core_dahdichandids_edit($description, $channel, $did)) {
            needreload();
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
            $_SESSION['msgtype']='success';
            redirect_standard('extdisplay');
        }
    break;
    case 'delete':
        core_dahdichandids_delete($channel);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        redirect_standard();
    break;
}

$dahdidids = array();
foreach (core_dahdichandids_list() as $row) {
    $dahdidids[] = array($row['channel'],$row['description'],$row['channel'],'');
}
drawListMenu($dahdidids, $type, $display, $extdisplay);
?>
<div class='content'>
<?php

$helptext = _("DAHDI Channel DIDs allow you to assign a DID to specific DAHDI Channels. You can supply the same DID to multiple channels. This would be a common scenario if you have multiple POTS lines that are on a hunt group from your provider. You MUST assign the channel's context to from-analog for these settings to have effect. It will be a line that looks like:<br /><br />context = from-analog<br /><br />in your chan_dahdi.conf configuration effecting the specified channel(s). Once you have assigned DIDs you can use standard Inbound Routes with the specified DIDs to route your calls.");
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

if ($extdisplay != '') {
    // load
    $row = core_dahdichandids_get($extdisplay);
    
    $description = $row['description'];
    $channel     = $row['channel'];
    $did         = $row['did'];

    echo "<div class='is-flex'><h2>"._("Edit DAHDI Channel: ").$channel."</h2>$help</div>\n";
} else {
    echo "<div class='is-flex'><h2>"._("Add DAHDI Channel")."</h2>$help</div>\n";
}

?>
<form id="mainform" name="editDAHDIchandid" method="post" onsubmit="return checkDAHDIchandid(this);">
    <input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
<?php if ($extdisplay != '') { ?>
    <input type="hidden" name="channel" value="<?php echo $extdisplay; ?>">
<?php } ?>
    <input type="hidden" name="action" value="<?php echo ($extdisplay != '' ? 'edit' : 'add'); ?>">
    <table class='table is-borderless is-narrow'>
    <tr><td colspan="2"><h5><?php echo dgettext('amp','General Settings');?></h5></td></tr>
<?php
if ($extdisplay == '') {
?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Channel")?><span><?php echo _("The DAHDI Channel number to map to a DID")?></span></a></td>
        <td><input class='input w100' type="text" name="channel" value="<?php  echo $channel; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
<?php
}
?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Description")?><span><?php echo _("A useful description describing this channel")?></span></a></td>
        <td><input class='input w100' type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("DID")?><span><?php echo _("The DID that this channel represents. The incoming call on this channel will be treated as if it came in with this DID and can be managed with Inbound Routing on DIDs")?></span></a></td>
        <td><input class='input w100' type="text" name="did" value="<?php echo $did; ?>" tabindex="<?php echo ++$tabindex;?>"/></td>
    </tr>

    </table>
</form>

<script>

var actionDelete = false;

function checkDAHDIchandid(theForm) {
    var msgInvalidChannel = "<?php echo _('Invalid Channel Number, must be numeric and not blank'); ?>";
    var msgInvalidDID = "<?php echo _('Invalid DID, must be a non-blank DID'); ?>";
    var msgConfirmDIDNonStd = "<?php echo _('DID information is normally just an incoming telephone number.\n\nYou have entered a non standard DID pattern.\n\nAre you sure this is correct?'); ?>";
    var msgConfirmConvertDID = "<?php echo _('You appear to be using a converted DID in the form of zapchanNN that was automatically generated during an upgrade. You should consider assigning the DID that is normally associated with this channel to take full advantage of the inbound routing abilities. Changing the DID here will require you to make changes in the Inbound Routes tab. Do you want to continue?'); ?>";

    // If deleting we don't care what is in the elements
    if (actionDelete) {
        actionDelete = false;
        return true;
    }
    // form validation

    defaultEmptyOK = false;
    if (!isInteger(theForm.channel.value)) {
        return warnInvalid(theForm.channel, msgInvalidChannel);
    }
    if (isEmpty(theForm.did.value)) {
        return warnInvalid(theForm.did, msgInvalidDID);
    }
    if (theForm.did.value.substring(0,7) == "zapchan") {
        if (!confirm(msgConfirmConvertDID)) {
            return false;
        }

    } else if (!isDialpattern(theForm.did.value)) {
        if (!confirm(msgConfirmDIDNonStd)) {
            return false;
        }
    }

    return true;
}
<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
