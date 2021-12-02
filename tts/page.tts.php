<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Issabel version 4.0                                                  |
  | http://www.issabel.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2021 Issabel Foundation                                |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+
*/
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$type    = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'setup';
$action  = isset($_REQUEST['action']) ? $_REQUEST['action'] :  '';

if (isset($_REQUEST['delete'])) $action = 'delete';

$tts_id         = isset($_REQUEST['tts_id']) ? $_REQUEST['tts_id'] :  false;
$description    = isset($_REQUEST['description']) ? $_REQUEST['description'] :  '';
$tts_engine     = isset($_REQUEST['tts_engine']) ? $_REQUEST['tts_engine'] :  '';
$tts_text       = isset($_REQUEST['tts_text']) ? $_REQUEST['tts_text'] :  '';
$dest           = isset($_REQUEST['dest']) ? $_REQUEST['dest'] :  '';

if (isset($_REQUEST['goto0']) && $_REQUEST['goto0']) {
    $dest = $_REQUEST[ $_REQUEST['goto0'].'0' ];
}

switch ($action) {
    case 'add':
        tts_add($description, $tts_engine, $tts_text, $dest);
        needreload();
        redirect_standard();
    break;
    case 'edit':
        tts_edit($tts_id, $description, $tts_engine, $tts_text, $dest);
        needreload();
        redirect_standard('extdisplay');
    break;
    case 'delete':
        tts_delete($tts_id);
        needreload();
        redirect_standard();
    break;
}

?>
<div class="rnav"><ul>
<?php

echo '<li><a href="config.php?display=tts&amp;type='.$type.'">'._('Add Text to Speech').'</a></li>';

foreach (tts_list() as $row) {
    echo '<li><a href="config.php?display=tts&amp;type='.$type.'&amp;extdisplay='.$row['tts_id'].'" class="rnavdata" rnavdata="'.$row['tts_description'].'">'.$row['tts_description'].'</a></li>';
}

?>
</ul></div>
<?php

    echo "<h2>"._("Text to Speech")."</h2>";

if ($extdisplay) {
    // load
    $row = tts_get($extdisplay);
    $description    = $row['tts_description'];
    $tts_engine     = htmlspecialchars($row['tts_engine']);
    $tts_text       = htmlspecialchars($row['tts_text']);
    $dest           = $row['dest'];


        $usage_list = framework_display_destination_usage(tts_getdest($extdisplay));

        if (!empty($usage_list)) {
        ?>
            <table><tr><td colspan="2">
            <a href="#" class="info"><?php echo $usage_list['text']?>:<span><?php echo $usage_list['tooltip']?></span></a>
            </td></tr></table><br /><br />
        <?php
        }

} 

$helptext = _("The Text to Speech module allows you to add text to speech (TTS) instances on your PBX. You enter text to be read by a computer voice. When a TTS instance is entered as a destination in your call path, the system will play the text entered using the selected TTS engine. Then the call will then continue on to the target destination defined in the instance.");

echo $helptext;

?>

<form name="editTexttospeech" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkTexttospeech(editTexttospeech);">
    <input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="tts_id" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
    <table>
    <tr><td colspan="2"><h5><?php  echo ($extdisplay ? _("Edit Text to Speech Instance") : _("Add Text to Speech Instance")) ?></h5></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Description")?>:<span><?php echo _("The descriptive name of this text to speech instance. For example \"new name here\"");?></span></a></td>
        <td><input size="30" type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
    <tr>

        <td><a href="#" class="info"><?php echo _("Engine")?>:<span><?php echo _("The TTS engine to use for the text to speech entry");?></span></a></td>
        <td>

            <select name="tts_engine"  tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
            <?php
                $engines = ttsengine_list();
                foreach ($engines as $idx=>$data) {
                    echo '<option value="'.$data['ttsengine_engine'].'"'.($data['ttsengine_engine'] == $tts_engine ? ' SELECTED' : '').'>'.$data['ttsengine_description']."</option>\n";
                }
            ?>
            </select>


</td>


    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Text")?>:<span><?php echo _("The actual text to be spoken by the engine. You can use channel variables in the format \%{variable}.");?></span></a></td>
        <td><textarea name="tts_text" style='width:98%; height: 10rem;' tabindex="<?php echo ++$tabindex;?>"/><?php echo $tts_text; ?></textarea></td>
    </tr>

    <tr><td colspan="2"><br><h5><?php echo _("Destination")?>:</h5></td></tr>

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
      $('[name=tts_engine]').attr({value: "picotts"});
  }

});


function checkTexttospeech(theForm) {
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
