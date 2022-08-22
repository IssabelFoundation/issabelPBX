<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
$tabindex = 0;
isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';

//the item we are currently displaying
isset($_REQUEST['extdisplay'])?$extdisplay=$_REQUEST['extdisplay']:$extdisplay='';

$dispnum = "pinsets"; //used for switch on config.php

//if submitting form, update database
if(isset($_POST['action'])) {
    switch ($action) {
        case "add":
            pinsets_add($_POST);
            needreload();
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
            $_SESSION['msgtype']='success';
            redirect_standard();
        break;
        case "delete":
            pinsets_del($extdisplay);
            needreload();
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
            $_SESSION['msgtype']='warning';
            redirect_standard();
        break;
        case "edit":
            pinsets_edit($extdisplay,$_POST);
            needreload();
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
            $_SESSION['msgtype']='success';
            redirect_standard('extdisplay');
        break;
    }
}

//get list of time conditions

$rnaventries = array();
$pinsetss    = pinsets_list();
foreach ($pinsetss as $pinsets) {
    $rnaventries[] = array($pinsets['pinsets_id'],$pinsets['description'],'');
}
drawListMenu($rnaventries, $type, $display, $extdisplay);
?>

<!-- right side menu -->
<!--div class="rnav"><ul>
    <li><a class="<?php echo ($extdisplay=='' ? 'current':'') ?>" href="config.php?display=<?php echo urlencode($dispnum)?>"><?php echo _("Add PIN Set")?></a></li>
<?php
if (isset($pinsetss)) {
    foreach ($pinsetss as $pinsets) {
        echo "<li><a class=\"".($extdisplay==$pinsets['pinsets_id'] ? 'current':'')."\" href=\"config.php?display=".urlencode($dispnum)."&extdisplay=".urlencode($pinsets['pinsets_id'])."\">{$pinsets['description']}</a></li>";
    }
}
?>
</ul></div-->
<div class='content'>
<?php
$helptext = _("PIN Sets are used to manage lists of PINs that can be used to access restricted features such as Outbound Routes. The PIN can also be added to the CDR record's 'accountcode' field."); 
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

if ($extdisplay){
    //get details for this time condition
    $thisItem = pinsets_get($extdisplay);
    echo "<div class='is-flex'><h2>"._("Edit PIN Set").": ".$thisItem['description']."</h2>$help</div>";
} else {
    echo "<div class='is-flex'><h2>"._("Add PIN Set")."</h2>$help</div>";

}

?>

<form id="mainform" autocomplete="off" name="edit" action="" method="post" onsubmit="return edit_onsubmit(this);">
    <input type="hidden" name="display" value="<?php echo $dispnum?>">
    <input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add') ?>">
    <input type="hidden" name="deptname" value="<?php echo $_SESSION["AMP_user"]->_deptname ?>">

    <table class='table is-borderless is-narrow'>
    <tr><td colspan="2"><h5><?php echo dgettext('amp','General Settings')?></h5></td></tr>

<?php if ($extdisplay){ ?>
        <input type="hidden" name="account" value="<?php echo $extdisplay; ?>">
<?php } ?>

    <tr>
        <td><?php echo _("PIN Set Description:")?></td>
        <td><input type="text" class="input w100" name="description" value="<?php echo (isset($thisItem['description']) ? $thisItem['description'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Record In CDR?:")?><span><?php echo _("Select this box if you would like to record the PIN in the call detail records when used")?></span></a></td>
        <td><div class="field"><input type="checkbox" class="switch" id="chkaddtocdr" name="addtocdr" value="1" <?php echo (isset($thisItem['addtocdr']) && $thisItem['addtocdr'] == '1' ? 'CHECKED' : ''); ?> tabindex="<?php echo ++$tabindex;?>"><label for="chkaddtocdr"> </label></div></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("PIN List:")?><span><?php echo _("Enter a list of one or more PINs.  One PIN per line.")?></span></a></td>
        <td>
            <textarea class="textarea" name="passwords" tabindex="<?php echo ++$tabindex;?>"><?php echo (isset($thisItem['passwords']) ? $thisItem['passwords'] : ''); ?></textarea>
        </td>
    </tr>

    </table>
</form>
<script>

function edit_onsubmit(theForm) {

    defaultEmptyOK = false;

    <?php if (function_exists('module_get_field_size')) { ?>
        var sizeDisplayName = "<?php echo module_get_field_size('pinsets', 'description', 50); ?>";
        if (!isCorrectLength(theForm.description.value, sizeDisplayName))
            return warnInvalid(theForm.description, "<?php echo _('The PIN Set Description provided is too long.'); ?>")
    <?php } ?>

    if (!isAlphanumeric(theForm.description.value))
        return warnInvalid(theForm.description, "<?php _("Please enter a valid Description") ?>");

    $.LoadingOverlay('show');
    return true;
}

<?php echo js_display_confirmation_toasts(); ?>
</script>
</div>
<?php echo form_action_bar($extdisplay); ?>
</script>

