<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//    License for all code of this IssabelPBX module can be found in the license file inside the module directory
//    Copyright 2006-2014 Schmooze Com Inc.
//
$action = isset($_POST['action']) ? $_POST['action'] :  '';
if (isset($_POST['delete'])) $action = 'delete'; 

$tabindex = 0;


$announcement_id = isset($_POST['announcement_id']) ? $_POST['announcement_id'] :  false;
$description = isset($_POST['description']) ? $_POST['description'] :  '';
$recording_id = isset($_POST['recording_id']) ? $_POST['recording_id'] :  '';
$allow_skip = isset($_POST['allow_skip']) ? $_POST['allow_skip'] :  0;
$return_ivr = isset($_POST['return_ivr']) ? $_POST['return_ivr'] :  0;
$noanswer = isset($_POST['noanswer']) ? $_POST['noanswer'] :  0;
$post_dest = isset($_POST['post_dest']) ? $_POST['post_dest'] :  '';
$repeat_msg = isset($_POST['repeat_msg']) ? $_POST['repeat_msg'] :  '';
$tts_lang = isset($_POST['tts_lang']) ? $_POST['tts_lang'] :  '';
$tts_text = isset($_POST['tts_text']) ? $_POST['tts_text'] :  '';


if (isset($_POST['goto0']) && $_POST['goto0']) {
    // 'ringgroup_post_dest'  'ivr_post_dest' or whatever
    $post_dest = $_POST[ $_POST['goto0'].'0' ];
}


switch ($action) {
    case 'add':
        $_REQUEST['extdisplay'] = 
        announcement_add($description, $recording_id, $allow_skip, $post_dest, $return_ivr, $noanswer, $repeat_msg, $tts_lang, $tts_text);
        needreload();
        redirect_standard('extdisplay');
    break;
    case 'edit':
        announcement_edit($announcement_id, $description, $recording_id, $allow_skip, $post_dest, $return_ivr, $noanswer, $repeat_msg, $tts_lang, $tts_text);
        needreload();
        redirect_standard('extdisplay');
    break;
    case 'delete':
        announcement_delete($announcement_id);
        needreload();
        redirect_standard();
    break;
}


?> 

<div class="rnav"><ul>
<?php 
// Eventually I recon the drawListMenu could be built into the new component class thus making
// the relevent page.php file unnessassary

echo '<li><a href="config.php?display=announcement&amp;type=setup">'._('Add Announcement').'</a></li>';

foreach (announcement_list() as $row) {
    echo '<li><a href="config.php?display=announcement&amp;type=setup&amp;extdisplay='.$row[0].'" class="">'.$row[1].'</a></li>';
}

?>
</ul></div>


<?php
if ($extdisplay) {
    // load
    $row = announcement_get($extdisplay);
    
    $description = $row['description'];
    $recording_id = $row['recording_id'];
    $allow_skip = $row['allow_skip'];
    $post_dest = $row['post_dest'];
    $return_ivr = $row['return_ivr'];
    $noanswer = $row['noanswer'];
    $repeat_msg = $row['repeat_msg'];
    $tts_lang = $row['tts_lang'];
    $tts_text = $row['tts_text'];

}

?>
<form name="editAnnouncement" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkAnnouncement(editAnnouncement);">
            <input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
            <input type="hidden" name="announcement_id" value="<?php echo $extdisplay; ?>">
            <input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
            <table>
            <tr><td colspan="2"><h5><?php  echo ($extdisplay ? _("Edit Announcement") : _("Add Announcement")) ?><hr></h5></td></tr>
            <tr>
                <td><a href="#" class="info"><?php echo _("Description")?>:<span><?php echo _("The name of this announcement")?></span></a></td>
                <td><input size="15" type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
            </tr>

<?php if(function_exists('recordings_list')) { //only include if recordings is enabled?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Recording")?><span><?php echo _("Message to be played.<br>To add additional recordings use the \"System Recordings\" MENU to the left")?></span></a></td>
        <td>
            <select id="recording_id" name="recording_id"  tabindex="<?php echo ++$tabindex;?>">
            <?php
                $tresults = recordings_list();
                $default = (isset($recording_id) ? $recording_id : '');
                                if($recording_id==-1) { $picoselected=' selected '; } else { $picoselected=''; }
                if (isset($tresults[0])) {
                    echo '<option value="">'._("None")."</option>\n";
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
        <td><a href="#" class="info"><?php echo _("Repeat")?><span><?php echo _("Key to press that will allow for the message to be replayed. If you choose this option there will be a short delay inserted after the message. If a longer delay is needed it should be incorporated into the recording.")?></span></a></td>
        <td>
            <select name="repeat_msg"  tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = isset($repeat_msg) ? $repeat_msg : '';
                for ($i=0; $i<=9; $i++ ) {
                    $digits[]="$i";
                }
                $digits[] = '*';
                $digits[] = '#';
                echo '<option value=""'.($default == '' ? ' SELECTED' : '').'>'._("Disable")."</option>";
                foreach ($digits as $digit) {
                    echo '<option value="'.$digit.'"'.($digit == $default ? ' SELECTED' : '').'>'.$digit."</option>\n";
                }
            ?>
            </select>
        </td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Allow Skip")?><span><?php echo _("If the caller is allowed to press a key to skip the message.")?></span></a></td>
        <td><input type="checkbox" id="allow_skip" name="allow_skip" value="1" tabindex="<?php echo ++$tabindex;?>" <?php echo ($allow_skip ? 'CHECKED' : ''); ?> /></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Return to IVR")?><span><?php echo _("If this announcement came from an IVR and this box is checked, the destination below will be ignored and instead it will return to the calling IVR. Otherwise, the destination below will be taken. Don't check if not using in this mode. <br>The IVR return location will be to the last IVR in the call chain that was called so be careful to only check when needed. For example, if an IVR directs a call to another destination which eventually calls this announcement and this box is checked, it will return to that IVR which may not be the expected behavior.")?></span></a></td>
        <td><input type="checkbox" name="return_ivr" value="1" tabindex="<?php echo ++$tabindex;?>" <?php echo ($return_ivr ? 'CHECKED' : ''); ?> /></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Don't Answer Channel")?><span><?php echo _("Check this to keep the channel from explicitly being answered. When checked, the message will be played and if the channel is not already answered it will be delivered as early media if the channel supports that. When not checked, the channel is answered followed by a 1 second delay. When using an announcement from an IVR or other sources that have already answered the channel, that 1 second delay may not be desired.")?></span></a></td>
        <td><input type="checkbox" name="noanswer" value="1" tabindex="<?php echo ++$tabindex;?>" <?php echo ($noanswer ? 'CHECKED' : ''); ?> /></td>
    </tr>

<?php

if(tts_enabled()) {
?>

<tr><td colspan="2"><br><h5><?php echo _("Text to Speech")?>:<hr></h5></td></tr>

<?php
     $arrOptionsLang = array( 'en-US'=>'en-US', 'es-ES'=>'es-ES', 'fr-FR'=>'fr-FR', 'it-IT'=>'it-IT','de-DE'=>'de-DE','en-GB'=>'en-GB'  );
?>
<tr>
<td><?php echo _('Language');?></td>
<td>
<select name='tts_lang' id='tts_lang'>
<?php
foreach($arrOptionsLang as $key=>$val) {
    echo "<option val='$key' ";
    if($tts_lang==$key) echo " selected ";
    echo ">$val</option>\n";
}
?>
</select>
</td>
</tr>

<tr>
<td><?php echo _('Text');?></td>
<td><textarea name=tts_text id=tts_text><?php echo $tts_text;?></textarea></td>
</tr>

<?php
}
?>

</tr>    
    <tr><td colspan="2"><br><h5><?php echo _("Destination after playback")?>:<hr></h5></td></tr>

<?php 
//draw goto selects
echo drawselects($post_dest,0);
?>
            
            <tr>
            <td colspan="2"><br><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>">
            <?php if ($extdisplay) { echo '&nbsp;<input name="delete" type="submit" value="'._("Delete").'">'; } ?>
            </td>        
            
            </tr>
            <?php

            if ($extdisplay) {
                $usage_list = framework_display_destination_usage(announcement_getdest($extdisplay));
                if (!empty($usage_list)) {
                ?>
                    <tr><td colspan="2">
                    <a href="#" class="info"><?php echo $usage_list['text']?>:<span><?php echo $usage_list['tooltip']?></span></a>
                    </td></tr>
                <?php
                }
            }
            ?>
            </table>
            </form>
            
            
<script language="javascript">
<!--

function checkAnnouncement(theForm) {
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

function checkid() {
    if($('#recording_id').val()==0 || $('#recording_id').val() == null) {
        $('#tts_lang').prop('disabled',false);
        $('#tts_text').prop('disabled',false);
        $('#allow_skip').prop('disabled',true);
    } else {
        $('#tts_lang').prop('disabled',true);
        $('#tts_text').prop('disabled',true);
        $('#allow_skip').prop('disabled',false);
    }
}

$(document).ready(function() {
   checkid();
   $('#recording_id').on('change',function() {
      checkid();
   });
});
//-->
</script>
