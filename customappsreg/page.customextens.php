<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$tabindex = 0;
$display = 'customextens';

$type   = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'tool';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if (isset($_REQUEST['delete'])) $action = 'delete'; 

$old_custom_exten = isset($_REQUEST['old_custom_exten']) ? preg_replace("/[^0-9*#]/" ,"",$_REQUEST['old_custom_exten']) :  '';
$extdisplay       = isset($_REQUEST['extdisplay']) ? preg_replace("/[^0-9*#]/" ,"",$_REQUEST['extdisplay']) :  '';
$description      = isset($_REQUEST['description']) ? htmlentities($_REQUEST['description']) :  '';
$notes            = isset($_REQUEST['notes']) ? htmlentities($_REQUEST['notes']) :  '';

switch ($action) {
    case 'add':
        $conflict_url = array();
        $usage_arr = framework_check_extension_usage($extdisplay);
        if (!empty($usage_arr)) {
            $conflict_url = framework_display_extension_usage_alert($usage_arr);
            $extdisplay='';
        } else {
            if (customappsreg_customextens_add($extdisplay, $description, $notes)) {
                needreload();
                $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
                $_SESSION['msgtype']='success';
                redirect_standard();
            } else {
                $extdisplay='';
            }
        }
    break;
    case 'edit':
        $conflict_url = array();
        if ($old_custom_exten != $extdisplay) {
            $usage_arr = framework_check_extension_usage($extdisplay);
            if (!empty($usage_arr)) {
                $conflict_url = framework_display_extension_usage_alert($usage_arr);
            }
        }
        if (empty($conflict_url)) {
            if (customappsreg_customextens_edit($old_custom_exten, $extdisplay, $description, $notes)) {
                needreload();
                $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
                $_SESSION['msgtype']='success';
                redirect_standard('extdisplay');
            }
        }
    break;
    case 'delete':
        customappsreg_customextens_delete($extdisplay);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        redirect_standard();
    break;
}
$rnaventries = array();
$list   = customappsreg_customextens_list();
foreach($list as $row) {
	$descr = $row['description'] != '' ? $row['description'] : '('.$row['custom_exten'].')';
    $rnaventries[] = array($row['custom_exten'],$descr,$row['custom_exten'],'');
}
drawListMenu($rnaventries, $type, $display, $extdisplay);

?> 
<div class='content'>
<?php

if ($extdisplay != '') {
    // load
    $row = customappsreg_customextens_get($extdisplay);
    $description = $row['description'];
    $notes       = $row['notes'];
    $disp_description = $row['description'] != '' ? $row['description'] : $row['custom_exten'];
}

$helptext = _("Custom Extensions provides you with a facility to register any custom extensions or feature codes that you have created in a custom file and IssabelPBX doesn't otherwise know about them. This allows the Extension Registry to be aware of your own extensions so that it can detect conflicts or report back information about your custom extensions to other modules that may make use of the information. You should not put extensions that you create in the Misc Apps Module as those are not custom.");
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

echo "<div class='is-flex'><h2>".($extdisplay ? _('Edit Custom Extension').': '.$disp_description : _("Add Custom Extension"))."</h2>$help</div>\n";

if (!empty($conflict_url)) {
    echo ipbx_extension_conflict($conflict_url);
}

?>

<form id="mainform" name="editCustomExten" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkCustomExten(this);">
    <input type="hidden" name="old_custom_exten" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="action" value="<?php echo ($extdisplay != '' ? 'edit' : 'add'); ?>">
	<table>
	<tr><td colspan="2"><h5><?php  echo dgettext("amp","General Settings")?></h5></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Custom Extension")?><span><?php echo _("This is the Extension or Feature Code you are using in your dialplan that you want the IssabelPBX Extension Registry to be aware of.")?></span></a></td>
        <td><input class="input w100" type="text" name="extdisplay" id="extdisplay" value="<?php  echo $extdisplay; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Description")?><span><?php echo _("Brief description that will be published in the Extension Registry about this extension")?></span></a></td>
        <td><input class="input w100" type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
    <tr>
        <td valign="top"><a href="#" class="info"><?php echo _("Notes")?><span><?php echo _("More detailed notes about this extension to help document it. This field is not used elsewhere.")?></span></a></td>
        <td><textarea class="textarea" name="notes" cols="23" rows="6" tabindex="<?php echo ++$tabindex;?>"><?php echo $notes; ?></textarea></td> 
    </tr>

    </table>
    </form>
            
<script>

function checkCustomExten(theForm) {

    var msgInvalidCustomExten = "<?php echo _('Invalid Extension, must not be blank'); ?>";
    var msgInvalidDescription = "<?php echo _('Invalid description specified, must not be blank'); ?>";

    // form validation
    defaultEmptyOK = false;    

    if (isEmpty(theForm.extdisplay.value)) {
        return warnInvalid(theForm.extdisplay, msgInvalidCustomExten);
    }
    if (isEmpty(theForm.description.value)) {
        return warnInvalid(theForm.description, msgInvalidDescription);
    }
    $.LoadingOverlay('show');
    return true;
}
<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
