<?php 
/* $Id: page.recordings.php 1137 2006-03-14 17:04:46Z mheydon1973 $ */
//Copyright (C) 2018 Issabel Foundation. (nicolas@issabel.com)
//Copyright (C) 2004 Coalescent Systems Inc. (info@coalescentsystems.ca)

if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
/* $Id$ */

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';

global $fc_check;

// Lite weight popup code here, don't need everything else below
//

switch ($action) {
    case 'popup':
    case 'audio':
      include_once("$action.php");
        exit;
        break;
    default:
        break;
}
$id          =  isset($_REQUEST['id'])?$_REQUEST['id']:'';
$notes       =  isset($_REQUEST['notes'])?$_REQUEST['notes']:'';
$rname       =  isset($_REQUEST['rname'])?$_REQUEST['rname']:'';
$usersnum    =  isset($_REQUEST['usersnum'])?$_REQUEST['usersnum']:'';
$sysrec      =  isset($_REQUEST['sysrec'])?$_REQUEST['sysrec']:'';
$suffix      =  isset($_REQUEST['suffix'])                                  &&  trim($_REQUEST['suffix']  !=  "")  ?  $_REQUEST['suffix']  :  'wav';
$fcode       =  isset($_REQUEST['fcode'])                                   &&  $_REQUEST['fcode']        !=  ''   ?  1                    :  0;
$fcode_pass  =  isset($_REQUEST['fcode_pass'])?$_REQUEST['fcode_pass']:'';
$fcbase = '*29';
$default_pos = 0;

$astsnd = isset($asterisk_conf['astvarlibdir'])?$asterisk_conf['astvarlibdir']:'/var/lib/asterisk';
$astsnd .= "/sounds/";

// check ctype_digit() to avoid very obscure vulnerability that can be made if certain proxy's are used
// with the PBX system
if (empty($usersnum) || !ctype_digit($usersnum)) {
    $dest = "unnumbered-";
} else {
    $dest = "{$usersnum}-";
}

// get feature codes for diplay purposes
$fcc      = new featurecode('recordings', 'record_save');
$fc_save  = $fcc->getCodeActive();
unset($fcc);

$fcc      = new featurecode('recordings', 'record_check');
$fc_check = $fcc->getCodeActive();
unset($fcc);

$fc_save  = ($fc_save  != '' ? $fc_save  : _('** MISSING FEATURE CODE **'));
$fc_check = ($fc_check != '' ? $fc_check : _('** MISSING FEATURE CODE **'));

switch ($action) {

    case "system":
        recording_sidebar(-1, null);
        recording_sysfiles();
        break;
    case "newsysrec":
        $sysrecs = recordings_readdir($astsnd, strlen($astsnd)+1);
        if (recordings_add($sysrecs[$sysrec], $sysrecs[$sysrec])) {
            $id = recordings_get_id($sysrecs[$sysrec]);
        } else {
            $id = 0;
        }
        recording_sidebar($id, null);
        recording_editpage($id, null);
        break;
    case "recorded":
        // Clean up the filename,suffix, take out any nasty characters
        $filename = escapeshellcmd(strtr($rname, '/ ', '__'));
        $suffix = escapeshellcmd(strtr($suffix, '/ ', '__'));
        if (!file_exists($astsnd."custom")) {
            if (!mkdir($astsnd."custom", 0775)) {
                echo '<div class="content"><h5>'._("Failed to create").' '.$astsnd.'custom'.'</h5>';
            }
        } else {
            // can't rename a file from one partition to another, must use mv or cp
            // rename($recordings_save_path."{$dest}ivrrecording.wav",$recordings_astsnd_path."custom/{$filename}.wav");
            if (!file_exists($recordings_save_path."{$dest}ivrrecording.$suffix")) {
                echo "<hr><h5>"._("[ERROR] The Recorded File Does Not exists:")."</h5>";
                echo $recordings_save_path."{$dest}ivrrecording.$suffix<br><br>";
                echo "make sure you uploaded or recorded a file with the entered extension<hr>";
            } else {
                exec("cp " . $recordings_save_path . "{$dest}ivrrecording.$suffix " . $astsnd."custom/{$filename}.$suffix 2>&1", $outarray, $ret);
                if (!$ret) {
                    $isok = recordings_add($rname, "custom/{$filename}.$suffix");
                } else {
                    echo "<hr><h5>"._("[ERROR] SAVING RECORDING:")."</h5>";
                    foreach ($outarray as $line) {
                        echo "$line<br>";
                    }
                    echo _("Make sure you have entered a proper name");
                    echo "<hr>";
                }
                exec("rm " . $recordings_save_path . "{$dest}ivrrecording.$suffix ", $outarray, $ret);
                if ($ret) {
                    echo "<hr><h5>"._("[ERROR] REMOVING TEMPORARY RECORDING:")."</h5>";
                    foreach ($outarray as $line) {
                        echo "$line<br>";
                    }
                    echo _("Make sure Asterisk is not running as root ");
                    echo "<hr>";
                }
            }

            recording_sidebar(null, $usersnum);
            recording_addpage($usersnum);
            if ($isok)
                echo '<div class="content"><h5>'._("System Recording").' "'.$rname.'" '._("Saved").'!</h5>';
        }
        break;

    case "edit":
        $arr = recordings_get($id);
        $filename=$arr['filename'];
        // Check all possibilities of uploaded file types.
        $valid = Array("au","g723","g723sf","g729","gsm","h263","ilbc","mp3","ogg","pcm","alaw","ulaw","al","ul","mu","sln","raw","vox","WAV","wav","wav49");
        $fileexists = false;
        if (strpos($filename, '&') === false) {
            foreach ($valid as $xtn) {
                $checkfile = $recordings_astsnd_path.$filename.".".$xtn;
                if (file_exists($checkfile)) {
                    $suffix = substr(strrchr($filename, "."), 1);
                    copy($checkfile, $recordings_save_path."{$dest}ivrrecording.".$suffix);
                    $fileexists = true;
                }
            }
            if ($fileexists === false) {
                echo '<div class="content"><h5>'._("Unable to locate").' '.$recordings_astsnd_path.$filename.' '._("with a a valid suffix").'</h5>';
            }
        }

        recording_sidebar($id, $usersnum);
        recording_editpage($id, $usersnum);
        break;

    case "edited":
        recordings_update($id, $rname, $notes, $_REQUEST, $fcode, $fcode_pass);
        recording_sidebar($id, $usersnum);
        recording_editpage($id, $usersnum);
        echo '<div class="content"><h5>'._("System Recording").' "'.$rname.'" '._("Updated").'!</h5></div>';
        needreload();
        break;

    case "delete";
        recordings_del($id);
        needreload();

    default:
        recording_sidebar($id, $usersnum);
        recording_addpage($usersnum);
        break;

}

function recording_addpage($usersnum,$pepe) {
    global $fc_save;
    global $fc_check;
    global $recordings_save_path;

    ?>
    <div class="content">

    <h2><?php echo _("System Recordings")?></h2>
    <h3><?php echo _("Add Recording") ?></h3>


    <h5><?php echo _("Step 1: Record or upload")?></h5>

<div id="tabs" style='width:600px; overflow:hidden;'>
  <ul>
    <li><a href="#tabs-1"><?php echo _('Record using phone');?></a></li>
    <li><a href="#tabs-2"><?php echo _('Record using browser');?></a></li>
    <li><a href="#tabs-3"><?php echo _('Upload recording');?></a></li>
  </ul>
  <div id="tabs-1">

    <?php if (!empty($usersnum)) {
    echo '<p>';
        echo _("Using your phone,")."<a href=\"#\" class=\"info\">"._(" dial")."&nbsp;".$fc_save." <span>";
        echo _("Start speaking at the tone. Press # when finished.")."</span></a>";
        echo _("and speak the message you wish to record. Press # when finished.")."\n";
    echo '</p>';
    } else { ?>
        <form name="xtnprompt" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
        <input type="hidden" name="display" value="recordings">
        <?php
        echo _("If you wish to make and verify recordings from your phone, please enter your extension number here:"); ?>
        <input type="text" size="6" name="usersnum" tabindex="<?php echo ++$tabindex;?>"> <input name="Submit" type="submit" value="<?php echo _("Go"); ?>" tabindex="<?php echo ++$tabindex;?>">
        </form>
    <?php } ?>

  </div>
  <div id="tabs-2">
 
    <div id="controls">
         <button id="recordButton" class='audio'><?php echo _('Record');?></button>
         <button id="pauseButton"  class='audio' ><?php echo _('Pause');?></button>
         <button id="stopButton"  class='audio' ><?php echo _('Stop');?></button>
    </div>
    <div id="formats">Format: start recording to see sample rate</div>
        <h3><?php echo _('Recordings');?></h3>
        <ol id="recordingsList"></ol>

 
  </div>
  <div id="tabs-3">

    <form enctype="multipart/form-data" name="upload" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
        <?php echo _("Alternatively, upload a recording in any supported asterisk format. Note that if you're using .wav, (eg, recorded with Microsoft Recorder) the file <b>must</b> be PCM Encoded, 16 Bits, at 8000Hz")?>:
        <br/>
        <br/>
        <input type="hidden" name="display" value="recordings">
        <input type="hidden" name="action" value="recordings_start">
        <input type="hidden" name="usersnum" value="<?php echo $usersnum ?>">
        <input type="file" name="ivrfile" tabindex="<?php echo ++$tabindex;?>"/>
        <input type="button" value="<?php echo _("Upload")?>" onclick="document.upload.submit(upload);alert('<?php echo addslashes(_("Please wait until the page reloads."))?>');" tabindex="<?php echo ++$tabindex;?>"/>
    </form>
 
  </div>
</div>

<?php
    if (isset($_FILES['ivrfile']['tmp_name']) && is_uploaded_file($_FILES['ivrfile']['tmp_name'])) {

        if (empty($usersnum) || !ctype_digit($usersnum)) {
            $dest = "unnumbered-";
            $usersnum = '';
        } else {
            $dest = "{$usersnum}-";
        }
        $suffix = preg_replace('/[^0-9a-zA-Z]/','',substr(strrchr($_FILES['ivrfile']['name'], "."), 1));
        $destfilename = $recordings_save_path.$dest."ivrrecording.".$suffix;
        move_uploaded_file($_FILES['ivrfile']['tmp_name'], $destfilename);
        
        if(isset($_POST['fname'])) {
            // downsample
            $convertido = preg_replace("/\.wav/","-downsample.wav",$destfilename);
            exec("sox $destfilename -r 8000 $convertido");
            unlink($destfilename);
            copy($convertido, $destfilename);
            unlink($convertido);
        }

        system("chgrp " . $amp_conf['AMPASTERISKGROUP'] . " " . $destfilename);
        system("chmod g+rw ".$destfilename);
        echo "<h6>"._("Successfully uploaded")." ".$_FILES['ivrfile']['name']."</h6>";
        $rname = rtrim(basename($_FILES['ivrfile']['name'], $suffix), '.');
    } 

    if(isset($_GET['fname'])) { $rname = $_GET['fname']; }

   $self  = $_SERVER['PHP_SELF'];
   $query = $_SERVER['QUERY_STRING'];
   parse_str($query,$parameters);
   unset($parameters['fname']);
   $final_query = http_build_query($parameters);

//print_r($_SERVER);
?>
        
    <form name="prompt" action="<?php echo $self."?".$final_query;?>" method="post" onsubmit="return rec_onsubmit();">
    <input type="hidden" name="action" value="recorded">
    <input type="hidden" name="display" value="recordings">
    <input type="hidden" name="usersnum" value="<?php echo $usersnum ?>">
    <?php

    if (!empty($usersnum)) { ?>

        <h5><?php echo _("Step 2: Verify")?></h5>
        <p> <?php echo _("After recording or uploading,")."&nbsp;<em>"._("dial")."&nbsp;".$fc_check."</em> "._("to listen to your recording.")?> </p>
        <p> <?php echo _("If you wish to re-record your message, dial")."&nbsp;".$fc_save; ?></p>
        <h5><?php echo _("Step 3: Name")?> </h5> 

    <?php
    } else {
        if($rname<>'') {
            echo "<h5>"._("Step 2: Name")."</h5>";
        }
    } 
    ?>

    <?php if(isset($_GET['fname']) || !empty($usersnum) || isset($_FILES['ivrfile'])) {

?>


    <table style="text-align:right;">
        <tr valign="top">
            <td valign="top"><?php echo _("Name this Recording")?>: </td>
            <td style="text-align:left"><input type="text" name="rname" value="<?php echo $rname; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
        </tr>
    </table>

    <h6><?php
    echo _("Click \"SAVE\" when you are satisfied with your recording");
    echo "<input type=\"hidden\" name=\"suffix\" value=\"$suffix\">\n"; ?>
    <input name="Submit" type="submit" value="<?php echo _("Save")?>" tabindex="<?php echo ++$tabindex;?>"></h6>
    <?php recordings_form_jscript(); ?>
    </form>

<?php } ?>

    </div>
<?php
}

function recording_editpage($id, $num) {
    global $fcbase;
    global $default_pos;
    global $fcode;
    global $fcode_pass;
    global $recordings_astsnd_path;
?>
    <div class="content">
    <h2><?php echo _("System Recordings")?></h2>
    <h3><?php echo _("Edit Recording") ?></h3>
    <?php
    $this_recording = recordings_get($id);
    if (!$this_recording) {
        echo "<tr><td colspan=2><h2>Error reading Recording ID $id - Aborting</h2></td></tr></table>";
        return;
    }?>
    <?php
    $usage_list = recordings_list_usage($id);
    if (count($usage_list)) {
?>
        <a href="#" class="info"><?php echo _("Usage List");?><span><?php echo _("This recording is being used in the following instances. You can not remove this recording while being used. To re-record, you can enable and use the feature code below if allowed.");?></span></a>
<?php
        $count = 0;
        foreach ($usage_list as $link) {
            $label = '<span><img width="16" height="16" border="0" title="'.$link['description'].'" alt="" src="assets/recordings/images/application_link.png"/>&nbsp;'.$link['description'].'</span>';
            echo "<br /><a href=".$link['url_query'].">".$label."</a>";
        }
    } else {
        $delURL = "config.php?display=recordings&amp;action=delete&amp;usersnum=".urlencode($num)."&amp;id=$id";
        $tlabel = _("Remove Recording");
        $label = '<span><img width="16" height="16" border="0" title="'.$tlabel.'" alt="" src="assets/recordings/images/sound_delete.png"/>&nbsp;'.$tlabel.'</span>';
        echo "<a href=".$delURL.">".$label."</a>";
        echo "<i style='font-size: x-small'>&nbsp;(";
        echo _("Note, does not delete file from computer");
        echo ")</i>";
    }
    ?>
    <form name="prompt"  action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return rec_onsubmit();">
    <input type="hidden" name="action" value="edited">
    <input type="hidden" name="display" value="recordings">
    <input type="hidden" name="usersnum" value="<?php echo $num ?>">
    <input type="hidden" name="id" value="<?php echo $id ?>">
    <table>
    <tr><td colspan=2><hr></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Change Name");?><span><?php echo _("This changes the short name, visible on the right, of this recording");?></span></a></td>
        <td><input type="text" name="rname" value="<?php echo $this_recording['displayname'] ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
    <tr>
            <td><a href="#" class="info"><?php echo _("Descriptive Name");?><span><?php echo _("This is displayed, as a hint, when selecting this recording in Queues, Digital Receptionist, etc");?></span></a></td>
            <td>&nbsp;<textarea name="notes" rows="3" cols="40" tabindex="<?php echo ++$tabindex;?>"><?php echo $this_recording['description'] ?></textarea></td>
    </tr>

<?php
    // This was being called twice: $rec = recordings_get($id);
    $rec = $this_recording;
    $fn = $rec['filename'];
    $files = explode('&', $fn);
    $counter = 0;
    $arraymax = count($files)-1;
    $sndfile_html = "";
    $jq_autofill = "";
    foreach ($files as $item) {
        $sndfile_html .=  recordings_display_sndfile($item, $counter, $arraymax, $recordings_astsnd_path, $rec['fcode']);
        $counter++;

        // create the jquery autofill statements in advance of the next iteration or the blank one at the end
        // on mouseover to the <td> element (since select doesn't have mouseover event), we clone the populated
        // select options and put them into this one which is created just with the selected tag. Then set the
        // selected value based on what is in the hidden tag. (we skip the hidden tag but for now ...)
        //
        $jq_autofill .= '
        $("#sysrec'.$counter.'").parent().one("mouseover", function(){
            $selectload = $("#selectload'.$counter.'").show(80,function(){
                $("#sysrec'.$counter.'").empty().append($optlist.clone()).val($("#sysrecval'.$counter.'").val());
                $("#sysrec'.$counter.'").chosen({search_contains: true, no_results_text: "No Recordings Found", allow_single_deselect: true});
                $(this).hide();
            });
        });
        ';
    }
    $sndfile_html .=  recordings_display_sndfile('', $counter, $arraymax, $recordings_astsnd_path, $rec['fcode']);
    if ($arraymax == 0 && isset($files[0]) && substr($files[0],0,7) == 'custom/') {
        if ($rec['fcode']) {
            $fcc = new featurecode("recordings", 'edit-recording-'.$id);
            $rec_code = $fcc->getCode();
            unset($fcc);
            if ($rec_code == '') {
                $rec_code = $fcbase.$id;
            }
        } else {
                $rec_code = $fcbase.$id;
        }
?>
    <tr>
        <td><a class="info" href="#"><?php echo _("Link to Feature Code")?><span><?php echo _("Check this box to create an options feature code that will allow this recording to be changed directly.")?></span></a>:
        </td>
        <td>
    <input type='checkbox' tabindex="<?php echo ++$tabindex;?>"name='fcode' id="fcode" <?php if ($rec['fcode']=="1") { echo 'CHECKED'; }?> OnClick="resetDefaultSound();"; return true;'><?php echo sprintf(_("Optional Feature Code %s"),$rec_code)?>
        </td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Feature Code Password");?><span><?php echo _("Optional password to protect access to this feature code which allows a user to re-record it.");?></span></a></td>
        <td><input type="text" name="fcode_pass" id="fcode_pass" value="<?php echo $rec['fcode_pass'] ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
<?php
    } else {
?>
    <tr>
    <td colspan="2"><a class="info" href="#"><?php echo _("Direct Access Feature Code Not Available")?><span><?php echo _("Direct Access Feature Codes for recordings are not available for built in system recordings or compound recordings made of multiple individual ones.")?></span></a>:
    </td>
    </tr>
<?php
    }
?>

    <tr><td colspan="2"><hr /></td></tr>
    </table>
    <?php echo _("Files");?>:<br />
    <table>
    <?php
    // globals seem to busted in PHP5 define here for now
    $recordings_astsnd_path = isset($asterisk_conf['astvarlibdir'])?$asterisk_conf['astvarlibdir']:'/var/lib/asterisk';
    $recordings_astsnd_path .= "/sounds/";

    // recordings_display_sndfile functions need to be run above so we have $default_pos set
    //
    echo $sndfile_html;
    ?>
    </table>
    <input name="Submit" type="submit" value="<?php echo _("Save")?>" tabindex="<?php echo ++$tabindex;?>"></h6>
    <?php recordings_popup_jscript(); ?>
    <?php recordings_form_jscript(); ?>
    <script language="javascript">
    <!-- Begin
    var sysrec0_idx;
    function initPage() {
        sysrec0_idx = document.getElementById("sysrec0").selectedIndex;
        alert('Got here with sysrec0_idx as:'.sysrec0_idx);
    }
    function resetDefaultSound() {
        if (document.getElementById('fcode').checked) {
            document.getElementById('sysrec0').selectedIndex=<?php echo $default_pos ?>;
            document.getElementById('sysrec1').selectedIndex=0;

            document.getElementById('sysrec0').disabled=true;
            document.getElementById('sysrec1').disabled=true;
            document.getElementById('play1').style.visibility='hidden';
            document.getElementById('down0').style.visibility='hidden';
            document.getElementById('up1').style.visibility='hidden';
            document.getElementById('del0').style.visibility='hidden';
            document.getElementById('del1').style.visibility='hidden';
        } else {
            document.getElementById('sysrec0').disabled=false;
            document.getElementById('sysrec1').disabled=false;
            document.getElementById('play1').style.visibility='visible';
            document.getElementById('down0').style.visibility='visible';
            document.getElementById('up1').style.visibility='visible';
            document.getElementById('del0').style.visibility='visible';
            document.getElementById('del1').style.visibility='visible';
        }
    }

    $(document).ready(function(){
        var $reclist = $("#sysrec0");
        var $optlist = $("#sysrec0 option");
        //$(".slclass").css({ visibility: "visible" }).hide();
        $(".slclass").css("visibility", "visible").hide();
        $(".autofill").width($reclist.width());
        <?php echo $jq_autofill; ?>
    });


    // End -->
    </script>
    </form>
    </div>
<?php
}

function recording_sidebar($id, $num) {
?>
        <div class="rnav"><ul>
        <li><a id="<?php echo empty($id)?'current':'nul' ?>" href="config.php?display=recordings&amp;usersnum=<?php echo urlencode($num) ?>"><?php echo _("Add Recording")?></a></li>
        <li><a id="<?php echo ($id===-1)?'current':'nul' ?>" href="config.php?display=recordings&amp;action=system"><?php echo _("Built-in Recordings")?></a></li>
<?php
        $wrapat = 18;
        $tresults = recordings_list();
        if (isset($tresults)){
                foreach ($tresults as $tresult) {
                        echo "<li>";
                        echo "<a id=\"".($id==$tresult[0] ? 'current':'nul')."\" href=\"config.php?display=recordings&amp;";
                        echo "action=edit&amp;";
                        echo "usersnum=".urlencode($num)."&amp;";
//                        echo "filename=".urlencode($tresult[2])."&amp;";
                        echo "id={$tresult[0]}\">";
                        $dispname = $tresult[1];
                        while (strlen($dispname) > (1+$wrapat)) {
                            $part = substr($dispname, 0, $wrapat);
                            echo htmlspecialchars($part);
                            $dispname = substr($dispname, $wrapat);
                            if ($dispname != '')
                                echo "<br>";
                        }
                        echo htmlspecialchars($dispname);
                        echo "</a>";
                        echo "</li>\n";
                }
        }
        echo "</ul></div>\n";
}

function recordings_popup_jscript() {
?>
        <script language="javascript">
    <!-- Begin
    function popUp(URL,optionId) {
        var selIndex=optionId.selectedIndex
        var file=encodeURIComponent(optionId.options[selIndex].value)

        /*alert(selIndex);*/
        if (file != "")
            popup = window.open(URL+file, 'play', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=320,height=110');
    }
    // End -->
    </script>
<?php
}

function recordings_form_jscript() {
?>
    <script language="javascript">
    <!--

    var theForm = document.prompt;

    function rec_onsubmit() {
        var msgInvalidFilename = "<?php echo _("Please enter a valid Name for this System Recording"); ?>";

        defaultEmptyOK = false;
        if (!isFilename(theForm.rname.value))
            return warnInvalid(theForm.rname, msgInvalidFilename);

        return true;
    }

    //-->
    </script>

<?php
}

function recording_sysfiles() {
    $astsnd = isset($asterisk_conf['astvarlibdir'])?$asterisk_conf['astvarlibdir']:'/var/lib/asterisk';
    $astsnd .= "/sounds/";
    $sysrecs = recordings_readdir($astsnd, strlen($astsnd)+1);
?>
    <div class="content">
    <h2><?php echo _("System Recordings")?></h2>
    <h3><?php echo _("Built-in Recordings") ?></h3>
    <h5><?php echo _("Select System Recording:")?></h5>
    <form name="xtnprompt" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
    <input type="hidden" name="action" value="newsysrec">
    <input type="hidden" name="display" value="recordings">
    <select name="sysrec" class="autocomplete-combobox">
<?php
    foreach ($sysrecs as $srcount => $sr) {
        // echo '<option value="'.$vmc.'"'.($vmc == $ivr_details['dircontext'] ? ' SELECTED' : '').'>'.$vmc."</option>\n";
        echo "<option value=\"$srcount\">$sr</option>\n";
        }
    ?>
    </select>
    <input name="Submit" type="submit" value="<?php echo _("Go"); ?>">
    <p />
    </div>
<?php
}

function recordings_display_sndfile($item, $count, $max, $astpath, $fcode) {
    global $default_pos;
    global $amp_conf;

    $disabled_state = $fcode == 0 ? "" : "disabled='true' ";
    $hidden_state = $fcode == 0 ? "" : "style='visibility:hidden' ";

    $html_text = "";
    // Note that when using this, it needs a <table> definition around it.

    if ($count == 0) {
        $astsnd = isset($asterisk_conf['astvarlibdir'])?$asterisk_conf['astvarlibdir']:'/var/lib/asterisk';
        $astsnd .= "/sounds/";
        $sysrecs = recordings_readdir($astsnd, strlen($astsnd)+1);
        $html_txt .=  "<tr><td><select $disabled_state id='sysrec$count' name='sysrec$count' class='autofill autocomplete-combobox'>\n";
        $html_txt .=  '<option value=""'.($item == '' ? ' SELECTED' : '')."></option>\n";
        $index=0;
        foreach ($sysrecs as $sr) {
            // value= not needed since text and value are same
            //
            $html_txt .=  '<option '.($sr == $item ? ' SELECTED' : '').">$sr</option>\n";
            if ($sr == $item) {
                $default_pos = $index+1;
            }
            $index++;
        }
        $html_txt .=  "</select></td>\n";
    } else {
        $html_txt .=  "<tr><td>";
        $html_txt .=  "<input type='hidden' id='sysrecval$count' value='$item' />";
        $html_txt .=  "<select $disabled_state id='sysrec$count' name='sysrec$count' class='autofill'>\n";
        $html_txt .=  "<option  SELECTED>$item</option>\n";
        $html_txt .=  "</select></td>\n";
    }

    $html_txt .=  "<td>";
    $audio=$astpath;

    include_once("crypt.php");
  $crypt = new Crypt();
    $REC_CRYPT_PASSWORD = (isset($amp_conf['AMPPLAYKEY']) && trim($amp_conf['AMPPLAYKEY']) != "")?trim($amp_conf['AMPPLAYKEY']):'moufdsuu3nma0';
  $audio = urlencode($crypt->encrypt($audio,$REC_CRYPT_PASSWORD));
    $recurl=$_SERVER['PHP_SELF']."?display=recordings&action=popup&recordingpath=$audio&recording=";

    $html_txt .=  "<a href='#' ".(($count)?$hidden_state:'')." type='submit' id='play$count' onClick=\"javascript:popUp('$recurl',document.prompt.sysrec$count); return false;\" input='foo'>";
    $html_txt .=  "<img border='0' width='20'  height='20' src='assets/recordings/images/play.png' title='"._("Click here to play this recording")."' />";
    $html_txt .=  "</img></td>";

    if ($count==0) {
         $html_txt .=  "<td></td>\n";
    } else {
        $html_txt .=  "<td class='action'>";
        $html_txt .=  "<button $hidden_state name='up$count' id='up$count' value='Move Up' style='border:0'> <img src='images/scrollup.gif' alt='"._('Move Up')."' title='"._('Move Up')."'/> </button>\n";
        $html_txt .=  "</td>\n";
    } if ($count > $max) {
        $html_txt .=  "<td></td>\n";
    } else {
        $html_txt .=  "<td class='action'>";
        $html_txt .=  "<button $hidden_state name='down$count' id='down$count' value='Move Down' style='border:0'> <img src='images/scrolldown.gif' alt='"._('Move Down')."' title='"._('Move Down')."'/> </button>\n";
        $html_txt .=  "</td>\n";
    }
    $html_txt .=  "<td class='action'><button $hidden_state name='del$count' id='del$count' value='Delete' style='border:0'> <img src='images/trash.png' alt='"._('Delete')."' title='"._('Delete')."'/> </button>\n";
    $html_txt .=  "</td><td id='selectload$count' class='slclass' style='visibility:hidden' width='16'><img border='0' style='float: none; margin-left: 0px; margin-bottom: 0px;' src='assets/recordings/images/rec_hourglass.png'></td>\n";

    $html_txt .=  "</tr>\n";
    return $html_txt;
}

?>
