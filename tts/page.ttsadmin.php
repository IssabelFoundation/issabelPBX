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
'flite'=>'',
'custom'=>''
);

$commands = array(
'polly'=>'/usr/bin/node /opt/aws-nodejs/polly.js',
'pico'=>'/usr/bin/pico2wave',
'flite'=>'/usr/bin/flite',
'azure'=>'/var/lib/asterisk/agi-bin/azuretts.php',
'custom'=>'path/to/custom/script "{TEXT}" {OUTPUTFILE}'
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
        redirect_standard();
    break;
    case 'edit':
        ttsengine_edit($ttsengine_id, $description, $ttsengine_engine, $ttsengine_cmd, $ttsengine_template);
        needreload();
        redirect_standard('extdisplay');
    break;
    case 'delete':
        ttsengine_delete($ttsengine_id);
        needreload();
        redirect_standard();
    break;
}

?>
<div class="rnav"><ul>
<?php

echo '<li><a href="config.php?display=ttsadmin&amp;type='.$type.'">'._('Add Text to Speech Engine').'</a></li>';

foreach (ttsengine_list() as $row) {
    echo '<li><a href="config.php?display=ttsadmin&amp;type='.$type.'&amp;extdisplay='.$row['ttsengine_id'].'" class="rnavdata" rnavdata="'.$row['ttsengine_description'].'">'.$row['ttsengine_description'].'</a></li>';
}

?>
</ul></div>
<?php
echo "<div id='container'>";
if ($extdisplay) {
    // load
    $row = ttsengine_get($extdisplay);
    $description = $row['ttsengine_description'];
    $ttsengine_engine     = htmlspecialchars($row['ttsengine_engine']);
    $ttsengine_template   = htmlspecialchars($row['ttsengine_template']);
    $ttsengine_cmd        = htmlspecialchars($row['ttsengine_cmd']);

//    echo "<h2>"._("Edit: ")."$description"."</h2>";

} else {
//    echo "<h2>"._("Add Text to Speech Engine")."</h2>";
}

    echo "<h2>"._("Text to Speech Engines")."</h2>";
$helptext = _("The Text To Speech Engine module allows you to add different engines to convert text to speech");

echo $helptext;

?>

<form name="editTexttospeechadmin" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkTexttospeechadmin(editTexttospeechadmin);">
    <input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="ttsengine_id" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
    <table style='width:100%;'>
    <tr><td colspan="2"><h5><?php  echo ($extdisplay ? _("Edit Text to Speech Engine")." $description" : _("Add Text to Speech Engine")) ?></h5></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Description")?>:<span><?php echo _("The descriptive name of this text to speech engine. For example \"new name here\"");?></span></a></td>
        <td><input size="30" type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Engine")?>:<span><?php echo _("The TTS engine to use for the text to speech entry");?></span></a></td>
        <td>
            <select name="ttsengine_engine" id="ttsengine_engine" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
            <?php
                $engines = array('pico'=>'Pico TTS','polly'=>'Amazon Polly','azure'=>'Microsoft Azure TTS','flite'=>'FLite','custom'=>'Custom');
                foreach ($engines as $key=>$name) {
                    echo '<option value="'.$key.'"'.($key == $ttsengine_engine ? ' SELECTED' : '').'>'.$name."</option>\n";
                }
            ?>
            </select>

        </td>
    </tr>
    <tr id='templatecell'>
        <td colspan=2><a href="#" class="info"><?php echo _("Template")?>:<span><?php echo _("Set of key=value pairs needed for the TTS engine. Usually API credentials, voice name, etc.");?></span></a></td>
   </tr>
   <tr>
        <td colspan=2><textarea id="ttstext" type="text" class='form-control' style='width:100px; height:10rem;' name="ttsengine_template" tabindex="<?php echo ++$tabindex;?>"/><?php echo $ttsengine_template; ?></textarea></td>
    </tr>
    <tr>
        <td colspan=2><a href="#" class="info"><?php echo _("Command")?>:<span><?php echo _("The actual command line to run the engine.<br/><br/>There are two variables you must use within the command if you select a <strong>custom</strong> engine: <dl><dt>{TEXT}</dt><dd>The actual text to convert to speech</dd><dt>{OUTPUTFILE}</dt><dd>The file name where the sound file will be saved.</dd></dl>");?></span></a></td>
    </tr>
    <tr>
        <td colspan=2><textarea class='form-control' style='xwidth:99%; height:3rem;' id='ttsenginecmd'  name="ttsengine_cmd" tabindex="<?php echo ++$tabindex;?>"/><?php echo $ttsengine_cmd; ?></textarea></td>
    </tr>
    <tr>
        <td colspan="2"><br><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>">
            <?php if ($extdisplay) { echo '&nbsp;<input name="delete" type="submit" value="'._("Delete").'">'; } ?>
        </td>

    </tr>
</table>
</form>
</div>

<script language="javascript">
<!--
$(document).ready(function () {

  if (!$('[name=description]').attr("value")) {
      $('[name=ttsengine_engine]').attr({value: "pico"});
  }
  $('#container').width($('#container').width() - $('.rnav').width() - 20).css('margin-left','-10px');

  
});

function checkTexttospeechadmin(theForm) {
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
