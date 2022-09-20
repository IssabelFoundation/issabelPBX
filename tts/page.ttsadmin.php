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

$ttsengine_id          = isset($_REQUEST['ttsengine_id']) ? $_REQUEST['ttsengine_id'] :  false;
$description           = isset($_REQUEST['description']) ? $_REQUEST['description'] :  '';
$ttsengine_engine      = isset($_REQUEST['ttsengine_engine']) ? $_REQUEST['ttsengine_engine'] :  '';
$ttsengine_cmd         = isset($_REQUEST['ttsengine_cmd']) ? $_REQUEST['ttsengine_cmd'] :  '';
$ttsengine_template    = isset($_REQUEST['ttsengine_template']) ? $_REQUEST['ttsengine_template'] :  '';


$templates = array(
'polly'=>'{
    "accessKeyId" :"",
    "secretAccessKey": "",
    "region": "us-east-1",
    "Engine": "neural|standard",
    "VoiceId": ""
}',
'pico'=>'{
      "language": "es-ES"
}',
'azure'=>'{
    "gender":"Female",
    "lang":"es-MX",
    "voice":"es-MX-DaliaNeural",
    "azurekey":"",
    "region":"eastus",
}',
'google'=>'{
    "languageCode":"es-US",
    "name":"es-US-Wavenet-A",
    "ssmlGender":"FEMALE",
    "credentials":"/path/to/google/credentials.json"
}',
'flite'=>'',
'custom'=>''
);

$commands = array(
'polly'=>'/usr/bin/node /opt/aws-nodejs/polly.js',
'pico'=>'/usr/bin/pico2wave',
'flite'=>'/usr/bin/flite',
'azure'=>$amp_conf['ASTDATADIR'].'/agi-bin/azuretts.php',
'google'=>$amp_conf['ASTDATADIR'].'/agi-bin/googlewave.php',
'custom'=>$amp_conf['ASTDATADIR'].'/agi-bin/googletts.pl "{TEXT}" en 1.2 {OUTPUTFILE}'
);

echo "<script>";
echo "var ttstemplate = {};\n";
foreach($templates as $kname=>$tval) {
echo "ttstemplate['$kname']='".base64_encode($tval)."';\n";
}
echo "var ttscommand = {};\n";
foreach($commands as $kname=>$tval) {
echo "ttscommand['$kname']='".base64_encode($tval)."';\n";
}

echo "</script>";

switch ($action) {
    case 'add':
        ttsengine_add($description, $ttsengine_engine, $ttsengine_cmd, $ttsengine_template);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        redirect_standard();
    break;
    case 'edit':
        ttsengine_edit($ttsengine_id, $description, $ttsengine_engine, $ttsengine_cmd, $ttsengine_template);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        redirect_standard('extdisplay');
    break;
    case 'delete':
        ttsengine_delete($ttsengine_id);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        $_SESSION['msgtstamp']=time();
        redirect_standard();
    break;
}

$rnaventries = array();
$engines     = ttsengine_list();
foreach($engines as $row) {
    $rnaventries[] = array($row['ttsengine_id'],$row['ttsengine_description'],'','');
}
drawListMenu($rnaventries, $type, $display, $extdisplay);
?>
<div class='content'>
<?php
if ($extdisplay) {
    // load
    $row = ttsengine_get($extdisplay);
    $description = $row['ttsengine_description'];
    $ttsengine_engine     = htmlspecialchars($row['ttsengine_engine']);
    $ttsengine_template   = htmlspecialchars($row['ttsengine_template']);
    $ttsengine_cmd        = htmlspecialchars($row['ttsengine_cmd']);
}

$helptext = _("The Text To Speech Engine module allows you to add different engines to convert text to speech");
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';
echo "<div class='is-flex'><h2>";
echo ($extdisplay ? _("Edit Text to Speech Engine")." $description" : _("Add Text to Speech Engine"));
echo "</h2>$help</div>";

?>

<form id="mainform" name="editTexttospeechadmin" method="post" onsubmit="return checkTexttospeechadmin(this);">
    <input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="ttsengine_id" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
    <table class='table is-fullwidth is-narrow is-borderless'>
    <tr><td colspan="2"><h5><?php echo dgettext('amp','General Settings');?></h5></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Description")?><span><?php echo _("The descriptive name of this text to speech engine. For example \"new name here\"");?></span></a></td>
        <td><input class="input w100" type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Engine")?><span><?php echo _("The TTS engine to use for the text to speech entry");?></span></a></td>
        <td>
            <select name="ttsengine_engine" id="ttsengine_engine" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
            <?php
                $engines = array('pico'=>'Pico TTS','polly'=>'Amazon Polly','azure'=>'Microsoft Azure TTS','flite'=>'FLite','google'=>'Google Cloud TTS', 'custom'=>'Custom');
                foreach ($engines as $key=>$name) {
                    echo '<option value="'.$key.'"'.($key == $ttsengine_engine ? ' SELECTED' : '').'>'.$name."</option>\n";
                }
            ?>
            </select>

        </td>
    </tr>
    <tr id='templatecell'>
        <td colspan=2><a href="#" class="info"><?php echo _("Template")?><span><?php echo _("Set of key=value pairs needed for the TTS engine. Usually API credentials, voice name, etc.");?></span></a></td>
   </tr>
   <tr>
        <td colspan=2><textarea id="ttstext" class='textarea' style='width:100px; height:10rem;' name="ttsengine_template" tabindex="<?php echo ++$tabindex;?>"><?php echo $ttsengine_template; ?></textarea></td>
    </tr>
    <tr>
        <td colspan=2><a href="#" class="info"><?php echo _("Command")?><span><?php echo _("The actual command line to run the engine.<br/><br/>There are two variables you must use within the command if you select a <strong>custom</strong> engine: <dl><dt>{TEXT}</dt><dd>The actual text to convert to speech</dd><dt>{OUTPUTFILE}</dt><dd>The file name where the sound file will be saved.</dd></dl>");?></span></a></td>
    </tr>
    <tr>
        <td colspan=2><textarea class='textarea' id='ttsenginecmd'  name="ttsengine_cmd" tabindex="<?php echo ++$tabindex;?>"><?php echo $ttsengine_cmd; ?></textarea></td>
    </tr>
</table>
</form>

<script>
                
$(function () {

  if (!$('[name=description]').attr("value")) {
      $('[name=ttsengine_engine]').attr({value: "pico"});
  }
  //$('#container').width($('#container').width() - $('.rnav').width() - 20).css('margin-left','-10px');

  
});

function checkTexttospeechadmin(theForm) {
    var msgInvalidDescription = "<?php echo _('Invalid description specified'); ?>";

    // set up the Destination stuff
    //setDestinations(theForm, '_post_dest');

    // form validation
    defaultEmptyOK = false;
    if (isEmpty(theForm.description.value))
        return warnInvalid(theForm.description, msgInvalidDescription);

    //if (!validateDestinations(theForm, 1, true))
    //    return false;

    $.LoadingOverlay('show');
    return true;
}
<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
