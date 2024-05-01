<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//  License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Copyright 2022 Issabel Foundation

$tabindex = 0;
isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';
isset($_REQUEST['extdisplay'])?$extdisplay = $_REQUEST['extdisplay']:$extdisplay='';

$dispnum = "miscdests"; //used for switch on config.php

switch ($action) {
    case "add":
        $_REQUEST['extdisplay'] = miscdests_add($_REQUEST['description'],$_REQUEST['destdial']);
        needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been added'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        redirect_standard();
    break;
    case "delete":
        miscdests_del($extdisplay);
        needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        $_SESSION['msgtstamp']=time();
        redirect_standard();
    break;
    case "edit":  //just delete and re-add
        miscdests_update($extdisplay,$_REQUEST['description'],$_REQUEST['destdial']);
        needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        redirect_standard('extdisplay');
    break;
}
$rnavitems = array();
$miscdests = miscdests_list();
foreach ($miscdests as $miscdest) {
    $rnavitems[]=array($miscdest[0],$miscdest[1],$miscdest[0],'');
}
drawListMenu($rnavitems, $type, $display, $extdisplay);
?>
<div class='content'>
<?php
if ($extdisplay){
    //get details for this meetme
    $thisMiscDest = miscdests_get($extdisplay);
    //create variables
    $description = "";
    $destdial = "";
    extract($thisMiscDest);
}

$helptext = __("Misc Destinations are for adding destinations that can be used by other IssabelPBX modules, generally used to route incoming calls. If you want to create feature codes that can be dialed by internal users and go to various destinations, please see the <strong>Misc Applications</strong> module.").' '.__('If you need access to a Feature Code, such as *98 to dial voicemail or a Time Condition toggle, these destinations are now provided as Feature Code Admin destinations. For upgrade compatibility, if you previously had configured such a destination, it will still work but the Feature Code short cuts select list is not longer provided.');
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

echo "<div class='is-flex'><h2>".($extdisplay ? __('Edit Misc Destination').': '.$description : __("Add Misc Destination"))."</h2>$help</div>\n";

if ($extdisplay) { 
    $usage_list = framework_display_destination_usage(miscdests_getdest($extdisplay));
    if (!empty($usage_list)) {
        echo ipbx_usage_info($usage_list['text'],$usage_list['tooltip']);
    }
}
?>
<form autocomplete="off" id="mainform" name="editMD" method="post" onsubmit="return editMD_onsubmit(this);">
    <input type="hidden" name="display" value="<?php echo $dispnum?>">
    <input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add') ?>">

<?php if ($extdisplay){ ?>
    <input type="hidden" name="id" value="<?php echo $extdisplay; ?>">
<?php } ?>

<table class='table is-borderless is-narrow'>
<tr><td colspan="2"><h5><?php echo _dgettext('amp','General Settings');?></h5></td></tr>
<tr>
    <td><a href="#" class="info"><?php echo __("Description")?><span><?php echo __("Give this Misc Destination a brief name to help you identify it.")?></span></a></td>
    <td><input autofocus type="text" name="description" value="<?php echo (isset($description) ? $description : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class='input w100'></td>
</tr>
<tr>
    <td><a href="#" class="info"><?php echo __("Dial")?><span><?php echo __("Enter the number this destination will simulate dialing, exactly as you would dial it from an internal phone. When you route a call to this destination, it will be as if the caller dialed this number from an internal phone.") ?></span></a></td>
    <td>
        <input type="text" name="destdial" value="<?php echo (isset($destdial) ? $destdial : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class='input w100'>&nbsp;&nbsp;
    </td>
</tr>
</table>
</form>

<script>

function editMD_onsubmit(theForm) {

    var msgInvalidDescription = "<?php echo __('Please enter a valid Description'); ?>";
    var msgInvalidDial = "<?php echo __('Please enter a valid Dial string'); ?>";

    defaultEmptyOK = false;

    <?php if (function_exists('module_get_field_size')) { ?>
        var sizeDisplayName = "<?php echo module_get_field_size('miscdests', 'description', 100); ?>";
        if (!isCorrectLength(theForm.description.value, sizeDisplayName))
            return warnInvalid(theForm.description, "<?php echo __('The description provided is too long.'); ?>")
    <?php } ?>
    
    if (!isAlphanumeric(theForm.description.value))
        return warnInvalid(theForm.description, msgInvalidDescription);

    // go thru text and remove the {} bits so we only check the actual dial digits
    var fldText = theForm.destdial.value;
    var chkText = "";

    if ( (fldText.indexOf("{") > -1) && (fldText.indexOf("}") > -1) ) { // has one or more sets of {mod:fc}

        var inbraces = false;
        for (var i=0; i<fldText.length; i++) {
            if ( (fldText.charAt(i) == "{") && (inbraces == false) ) {
                inbraces = true;
            } else if ( (fldText.charAt(i) == "}") && (inbraces == true) ) {
                inbraces = false;
            } else if ( inbraces == false ) {
                chkText += fldText.charAt(i);
            }
        }

        // if there is nothing in chkText but something in fldText
        // then the field must contain a featurecode only, therefore
        // there really is something in thre!
        if ( (chkText == "") & (fldText != "") )
            chkText = "0";

    } else {
        chkText = fldText;
    }
    // now do the check using the chkText var made above
    if (!isDialDigits(chkText))
        return warnInvalid(theForm.destdial, msgInvalidDial);

    $.LoadingOverlay('show');
    return true;
}
<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
