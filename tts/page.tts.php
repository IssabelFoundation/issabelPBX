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

if(!isset($tabindex)) $tabindex=1;

if (isset($_REQUEST['goto0']) && $_REQUEST['goto0']) {
    $dest = $_REQUEST[ $_REQUEST['goto0'] ];
}

switch ($action) {
    case 'add':
        tts_add($description, $tts_engine, $tts_text, $dest);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        redirect_standard();
    break;
    case 'edit':
        tts_edit($tts_id, $description, $tts_engine, $tts_text, $dest);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        redirect_standard('extdisplay');
    break;
    case 'delete':
        tts_delete($tts_id);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        $_SESSION['msgtstamp']=time();
        redirect_standard();
    break;
}

$rnaventries = array();
$engines     = tts_list();
foreach($engines as $row) {
    $rnaventries[] = array($row['tts_id'],$row['tts_description'],'','');
}
drawListMenu($rnaventries, $type, $display, $extdisplay);
?>
<div class='content'>
<?php

if ($extdisplay) {
    // load
    $row = tts_get($extdisplay);
    $description    = $row['tts_description'];
    $tts_engine     = htmlspecialchars($row['tts_engine']);
    $tts_text       = htmlspecialchars($row['tts_text']);
    $dest           = $row['dest'];
} 

$helptext = _("The Text to Speech module allows you to add text to speech (TTS) instances on your PBX. You enter text to be read by a computer voice. When a TTS instance is entered as a destination in your call path, the system will play the text entered using the selected TTS engine. Then the call will then continue on to the target destination defined in the instance.");
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';
echo "<div class='is-flex'><h2>";
echo ($extdisplay ? _("Edit Text to Speech").": $description" : _("Add Text to Speech"));
echo "</h2>$help</div>";

$usage_list = framework_display_destination_usage(tts_getdest($extdisplay));
if (!empty($usage_list)) {
    echo ipbx_usage_info($usage_list['text'],$usage_list['tooltip']);
}

?>
<form name="editTexttospeech" id="mainform" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkTexttospeech(this);">
    <input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="tts_id" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
    <table class='table is-fullwidth is-narrow is-borderless'>
    <tr><td colspan="2"><h5><?php echo dgettext('amp','General Settings');?></h5></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Description")?><span><?php echo _("The descriptive name of this text to speech instance. For example \"new name here\"");?></span></a></td>
        <td><input class="input w100" type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
    <tr>

        <td><a href="#" class="info"><?php echo _("Engine")?><span><?php echo _("The TTS engine to use for the text to speech entry");?></span></a></td>
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
        <td><a href="#" class="info"><?php echo _("Text")?><span><?php echo _("The actual text to be spoken by the engine. You can use channel variables in the format %{variable}.");?></span></a></td>
        <td><textarea name="tts_text" class="textarea" tabindex="<?php echo ++$tabindex;?>"/><?php echo $tts_text; ?></textarea></td>
    </tr>

    <tr><td colspan="2"><br><h5><?php echo _("Destination")?></h5></td></tr>

<?php
//draw goto selects
if($dest=='') { $dest='app-blackhole,hangup,1';  }
echo drawselects($dest,0);
?>

</table>
</form>

<script>
$(function () {

  if (!$('[name=description]').attr("value")) {
      $('[name=tts_engine]').attr({value: "picotts"});
  }

});

function checkTexttospeech(theForm) {
    var msgInvalidDescription = "<?php echo _('Invalid description specified'); ?>";

    // form validation
    defaultEmptyOK = false;
    if (isEmpty(theForm.description.value))
        return warnInvalid(theForm.description, msgInvalidDescription);

    if (!validateDestinations(theForm, 1, true))
        return false;

    $.LoadingOverlay('show');
    return true;
}
<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
