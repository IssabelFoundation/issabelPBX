<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//  License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Copyright 2006-2014 Schmooze Com Inc.
//  Copyright 2022 Issabel Foundation

$tabindex = 0;

$action          = isset($_POST['action'])          ? $_POST['action']          : '';
$announcement_id = isset($_POST['announcement_id']) ? $_POST['announcement_id'] : false;
$description     = isset($_POST['description'])     ? $_POST['description']     : '';
$recording_id    = isset($_POST['recording_id'])    ? $_POST['recording_id']    : '';
$allow_skip      = isset($_POST['allow_skip'])      ? $_POST['allow_skip']      : 0;
$return_ivr      = isset($_POST['return_ivr'])      ? $_POST['return_ivr']      : 0;
$noanswer        = isset($_POST['noanswer'])        ? $_POST['noanswer']        : 0;
$post_dest       = isset($_POST['post_dest'])       ? $_POST['post_dest']       : '';
$repeat_msg      = isset($_POST['repeat_msg'])      ? $_POST['repeat_msg']      : '';
$tts_lang        = isset($_POST['tts_lang'])        ? $_POST['tts_lang']        : '';
$tts_text        = isset($_POST['tts_text'])        ? $_POST['tts_text']        : '';

if (isset($_POST['goto0']) && $_POST['goto0']) {
    $post_dest = $_POST[ $_POST['goto0'] ];
}

switch ($action) {
    case 'add':
        $_REQUEST['extdisplay'] = '';
        announcement_add($description, $recording_id, $allow_skip, $post_dest, $return_ivr, $noanswer, $repeat_msg, $tts_lang, $tts_text);
        needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been added'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        redirect_standard();
    break;
    case 'edit':
        announcement_edit($announcement_id, $description, $recording_id, $allow_skip, $post_dest, $return_ivr, $noanswer, $repeat_msg, $tts_lang, $tts_text);
        needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        redirect_standard('extdisplay');
    break;
    case 'delete':
        announcement_delete($announcement_id);
        needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        $_SESSION['msgtstamp']=time();
        redirect_standard();
    break;
}

$rnaventries = array();
$announces   = announcement_list();
foreach($announces as $key=>$val) {
    $rnaventries[] = array($val['announcement_id'],$val['description'],'','');
}
drawListMenu($rnaventries, $type, $display, $extdisplay);
?> 

<div class='content'>

<?php

if ($extdisplay) {
    // load
    $row = announcement_get($extdisplay);
    
    $description  = $row['description'];
    $recording_id = $row['recording_id'];
    $allow_skip   = $row['allow_skip'];
    $post_dest    = $row['post_dest'];
    $return_ivr   = $row['return_ivr'];
    $noanswer     = $row['noanswer'];
    $repeat_msg   = $row['repeat_msg'];
    $tts_lang     = $row['tts_lang'];
    $tts_text     = $row['tts_text'];
}

$helptext = __("Plays back one of the system recordings (optionally allowing the user to skip it) and then goes to another destination.");
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

echo "<div class='is-flex'><h2>";
echo ($extdisplay ? __("Edit Announcement").": $description" : __("Add Announcement"));
echo "</h2>$help</div>";

if ($extdisplay) {
    $usage_list = framework_display_destination_usage(announcement_getdest($extdisplay));
    if (!empty($usage_list)) {
        echo ipbx_usage_info($usage_list['text'],$usage_list['tooltip']);
    }
}

?>
<form name="editAnnouncement" id="mainform" method="post" onsubmit="return checkAnnouncement(this);">
    <input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="announcement_id" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
    <table class='table is-narrow is-borderless'>
    <tr><td colspan="2"><h5><?php echo _dgettext('amp','General Settings');?></h5></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo __("Description")?>:<span><?php echo __("The name of this announcement")?></span></a></td>
        <td><input type="text" class='input w100' name="description" value="<?php  echo $description; ?>" autofocus tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>

<?php if(function_exists('recordings_list')) { //only include if recordings is enabled ?>

    <tr>
        <td><a href="#" class="info"><?php echo __("Recording")?><span><?php echo __("Message to be played.<br>To add additional recordings use the \"System Recordings\" MENU to the left")?></span></a></td>
        <td>
            <select id="recording_id" name="recording_id"  tabindex="<?php echo ++$tabindex;?>" class="componentSelect" xxonchange="checkid()">
            <?php
                $tresults = recordings_list();
                $default = (isset($recording_id) ? $recording_id : '');
                if($recording_id==-1) { $picoselected=' selected '; } else { $picoselected=''; }
                if (isset($tresults[0])) {
                    echo '<option value="">'.__("None")."</option>\n";
                    foreach ($tresults as $tresult) {
                        echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $default ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
                    }
                }
            ?>
            </select>
        </td>
    </tr>

<?php } ?>

    <tr>
        <td><a href="#" class="info"><?php echo __("Repeat")?><span><?php echo __("Key to press that will allow for the message to be replayed. If you choose this option there will be a short delay inserted after the message. If a longer delay is needed it should be incorporated into the recording.")?></span></a></td>
        <td>
            <select name="repeat_msg"  tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
            <?php
                $default = isset($repeat_msg) ? $repeat_msg : '';
                for ($i=0; $i<=9; $i++ ) {
                    $digits[]="$i";
                }
                $digits[] = '*';
                $digits[] = '#';
                echo '<option value=""'.($default == '' ? ' SELECTED' : '').'>'.__("Disable")."</option>";
                foreach ($digits as $digit) {
                    echo '<option value="'.$digit.'"'.($digit == $default ? ' SELECTED' : '').'>'.$digit."</option>\n";
                }
            ?>
            </select>
        </td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo __("Allow Skip")?><span><?php echo __("If the caller is allowed to press a key to skip the message.")?></span></a></td>
        <td>
          <?php echo ipbx_yesno_checkbox("allow_skip",$allow_skip,false); ?>
        </td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo __("Return to IVR")?><span><?php echo __("If this announcement came from an IVR and this box is checked, the destination below will be ignored and instead it will return to the calling IVR. Otherwise, the destination below will be taken. Don't check if not using in this mode. <br>The IVR return location will be to the last IVR in the call chain that was called so be careful to only check when needed. For example, if an IVR directs a call to another destination which eventually calls this announcement and this box is checked, it will return to that IVR which may not be the expected behavior.")?></span></a></td>
        <td>
          <?php echo ipbx_yesno_checkbox("return_ivr",$return_ivr,false); ?>
        </td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo __("Don't Answer Channel")?><span><?php echo __("Check this to keep the channel from explicitly being answered. When checked, the message will be played and if the channel is not already answered it will be delivered as early media if the channel supports that. When not checked, the channel is answered followed by a 1 second delay. When using an announcement from an IVR or other sources that have already answered the channel, that 1 second delay may not be desired.")?></span></a></td>
        <td>
          <?php echo ipbx_yesno_checkbox("noanswer",$noanswer,false); ?>
        </td>
    </tr>

<?php
if(tts_enabled()) {
    $arrOptionsLang = array( 'en-US'=>'en-US', 'es-ES'=>'es-ES', 'fr-FR'=>'fr-FR', 'it-IT'=>'it-IT','de-DE'=>'de-DE','en-GB'=>'en-GB'  );
?>

    <tr><td colspan="2"><br><h5><?php echo __("Text to Speech")?></h5></td></tr>
    <tr>
        <td><?php echo __('Language');?></td>
        <td>
            <select name='tts_lang' id='tts_lang' tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
            <?php
            foreach($arrOptionsLang as $key=>$val) {
                echo "<option value='$key' ";
                if($tts_lang==$key) echo " selected ";
                echo ">$val</option>\n";
            }
            ?>
            </select>
        </td>
    </tr>
    <tr>
        <td><?php echo __('Text');?></td>
        <td><textarea name=tts_text id=tts_text class='textarea' tabindex="<?php echo ++$tabindex;?>"><?php echo $tts_text;?></textarea></td>
    </tr>

<?php } ?>

    <tr>
        <td colspan="2"><br><h5><?php echo __("Destination after playback")?></h5></td>
    </tr>

<?php 
//draw goto selects
echo drawselects($post_dest,0);

?>
            
</table>
</form>
<script>
function checkAnnouncement(theForm) {

    var msgInvalidDescription = "<?php echo __('Invalid description specified'); ?>";

    // set up the Destination stuff
    setDestinations(theForm, '_post_dest');

    // form validation
    defaultEmptyOK = false;    
    if (isEmpty(theForm.description.value)) {
        $.LoadingOverlay('hide');
        return warnInvalid(theForm.description, msgInvalidDescription);
    }

    if (!validateDestinations(theForm, 1, true)) {
        $.LoadingOverlay('hide');
        return false;
    }

    $.LoadingOverlay('show');
    return true;
}

function checkid() {
    if($('#recording_id').val()==0 || $('#recording_id').val() == null) {
        $('#tts_lang').prop('disabled',false).trigger('chosen:updated');
        $('#tts_text').prop('disabled',false);
        $('#allow_skip').prop('disabled',true);
    } else {
        $('#tts_lang').prop('disabled',true).trigger('chosen:updated');
        $('#tts_text').prop('disabled',true);
        $('#allow_skip').prop('disabled',false);
    }
}

$('#recording_id:not(.bound)').addClass('bound').on('change', checkid);

checkid();

<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
