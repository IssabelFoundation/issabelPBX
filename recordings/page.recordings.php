<?php 
/* $Id: page.recordings.php 1137 2006-03-14 17:04:46Z mheydon1973 $ */
//Copyright (C) 2018 Issabel Foundation. (nicolas@issabel.com)
//Copyright (C) 2004 Coalescent Systems Inc. (info@coalescentsystems.ca)

if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$action = isset($_REQUEST['action'])&&!isset($_REQUEST['Cancel'])?$_REQUEST['action']:'';

global $fc_check;

$isSecure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $isSecure = true;
}

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

$id          =  isset($_REQUEST['id'])&&!isset($_REQUEST['Cancel'])?$_REQUEST['id']:'';
$extdisplay  =  isset($_REQUEST['extdisplay'])&&!isset($_REQUEST['Cancel'])?$_REQUEST['extdisplay']:'';
if($id=='' & $extdisplay !='') $id=$extdisplay;
if($extdisplay=='' & $id !='') $extdisplay=$id;

$notes       =  isset($_REQUEST['notes'])&&!isset($_REQUEST['Cancel'])?$_REQUEST['notes']:'';
$rname       =  isset($_REQUEST['rname'])&&!isset($_REQUEST['Cancel'])?$_REQUEST['rname']:'';
$usersnum    =  isset($_REQUEST['usersnum'])&&!isset($_REQUEST['Cancel'])?$_REQUEST['usersnum']:'';
$sysrec      =  isset($_REQUEST['sysrec'])&&!isset($_REQUEST['Cancel'])?$_REQUEST['sysrec']:'';
$suffix      =  isset($_REQUEST['suffix'])                                  &&  trim($_REQUEST['suffix']  !=  "")  ?  $_REQUEST['suffix']  :  'wav';
$fcode       =  isset($_REQUEST['fcode'])                                   &&  $_REQUEST['fcode']        !=  ''   ?  1                    :  0;
$fcode_pass  =  isset($_REQUEST['fcode_pass'])?$_REQUEST['fcode_pass']:'';
$fcbase      = '*29';
$default_pos = 0;

$astsnd = isset($asterisk_conf['astdatadir'])?$asterisk_conf['astdatadir']:'/var/lib/asterisk';
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

$fc_save  = ($fc_save  != '' ? $fc_save  : __('** MISSING FEATURE CODE **'));
$fc_check = ($fc_check != '' ? $fc_check : __('** MISSING FEATURE CODE **'));

switch ($action) {

    case "system":
        recording_sidebar(-1, null);
        recording_sysfiles();
        break;
    case "newsysrec":
        $sysrecs = recordings_readdir($astsnd, strlen($astsnd)+1);
        if(strpos($sysrecs[$sysrec],'/') != null) {
            $recname = preg_split("/\//",$sysrecs[$sysrec])[1];
        } else {
            $recname = $sysrecs[$sysrec];
        }
        if (recordings_add($recname, $sysrecs[$sysrec])) {
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
                $error = __("Failed to create").' '.$astsnd.'custom';
                $_SESSION['msg']=base64_encode($error);
                $_SESSION['msgtype']='error';
                $_SESSION['msgtstamp']=time();
                redirect_standard();
            }
        } else {
            // can't rename a file from one partition to another, must use mv or cp
            // rename($recordings_save_path."{$dest}ivrrecording.wav",$recordings_astsnd_path."custom/{$filename}.wav");
            if (!file_exists($recordings_save_path."{$dest}ivrrecording.$suffix")) {
                $error = __("[ERROR] The Recorded File Does Not exists:")."<br/><br/>";
                $error.= $recordings_save_path."{$dest}ivrrecording.$suffix<br><br>";
                $error.= __("make sure you uploaded or recorded a file with the entered extension");
                $_SESSION['msg']=base64_encode($error);
                $_SESSION['msgtype']='error';
                $_SESSION['msgtstamp']=time();
                $_SESSION['msgtimer']=6000;
                redirect_standard();
            } else {
                exec("cp " . $recordings_save_path . "{$dest}ivrrecording.$suffix " . $astsnd."custom/{$filename}.$suffix 2>&1", $outarray, $ret);
                if (!$ret) {
                    $isok = recordings_add($rname, "custom/{$filename}.$suffix");
                } else {

                    $error = __("[ERROR] SAVING RECORDING:")."<br/><br/>";
                    foreach ($outarray as $line) {
                        $error.= "$line<br>";
                    }
                    $error .= __("Make sure you have entered a proper name");
                    $_SESSION['msg']=base64_encode($error);
                    $_SESSION['msgtype']='error';
                    $_SESSION['msgtstamp']=time();
                    $_SESSION['msgtimer']=6000;
                    redirect_standard();
                }
                exec("rm " . $recordings_save_path . "{$dest}ivrrecording.$suffix ", $outarray, $ret);
                if ($ret) {
                    $error = __("[ERROR] REMOVING TEMPORARY RECORDING:")."<br/><br/>";
                    foreach ($outarray as $line) {
                        $error.="$line<br/>";
                    }
                    $error .= __("Make sure Asterisk is not running as root ");
                    $_SESSION['msg']=base64_encode($error);
                    $_SESSION['msgtype']='error';
                    $_SESSION['msgtstamp']=time();
                    $_SESSION['msgtimer']=6000;
                    redirect_standard();
                }
            }

            recording_sidebar(null, $usersnum);
            recording_addpage($usersnum);
            if ($isok) {
                $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
                $_SESSION['msgtype']='success';
                $_SESSION['msgtstamp']=time();
                redirect_standard();
            }
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
                //echo '<div class="content" style="display:table;"><h5>'.__("Unable to locate").' '.$recordings_astsnd_path.$filename.' '.__("with a a valid suffix").'</h5>';
                $msg = sprintf(__("File %s does not have a valid sound extension"),$recordings_astsnd_path.$filename); 
                $warn_msg = "<article class='message is-warning'><div class='message-body'>$msg</div></article>";
            }
        }

        recording_sidebar($id, $usersnum);
        recording_editpage($id, $usersnum, $warn_msg);
        break;

    case "edited":
        recordings_update($id, $rname, $notes, $_REQUEST, $fcode, $fcode_pass);
        recording_sidebar($id, $usersnum);
        recording_editpage($id, $usersnum);
        //echo '<div class="content" style="display:table;"><h5>'.__("System Recording").' "'.$rname.'" '.__("Updated").'!</h5></div>';
        needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        $_REQUEST['action']='edit';
        $action = 'edit';
        //redirect_standard('id','action');
        break;

    case "delete";
        recordings_del($id);
        needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        $_SESSION['msgtstamp']=time();
        redirect_standard();

    default:
        recording_sidebar($id, $usersnum);
        recording_addpage($usersnum);
        break;

}

function recording_addpage($usersnum) {
    global $fc_save;
    global $fc_check;
    global $recordings_save_path;
    global $isSecure;
    global $amp_conf;

    $tabindex = 0;
    $step = 0;
    ?>
    <div class="content">

    <h2><?php echo __("Add System Recording") ?></h2>


    <h5><?php echo __("Step ".++$step).": ".__("Record or upload")?></h5>

<?php
    $showtabs = 1;
    if (isset($_FILES['ivrfile']['tmp_name']) && is_uploaded_file($_FILES['ivrfile']['tmp_name'])) { $showtabs=0; }
    if (!empty($usersnum)) {  $showtabs=0; }
    if (isset($_REQUEST['fname'])) {  $showtabs=0; }

    if($showtabs==1) {
    
?>
    

<div id="tabs" class="tabs is-boxed">
  <ul>
<?php 
    $tab_content_active_1 = ' class="is-active" ';
    $tab_content_active_2 = ' class="is-hidden" ';
    if($isSecure) { 
        $tab_content_active_2 = ' class="is-active" ';
        $tab_content_active_1 = ' class="is-hidden" ';
?>
    <li data-tab="2" class="is-active"><a><?php echo __('Record using browser');?></a></li>
    <li data-tab="1"><a><?php echo __('Record using phone');?></a></li>
<?php } else { ?>
    <li class="is-active" data-tab="1"><a><?php echo __('Record using phone');?></a></li>
<?php }?>
    <li data-tab="3"><a><?php echo __('Upload recording');?></a></li>
  </ul>
</div>
<div id="tab-content">
<div <?php echo $tab_content_active_1?> data-content="1">

    <?php if (!empty($usersnum)) {
        echo '<div>';
        echo __("Using your phone,")."<a href=\"#\" class=\"info\">".__(" dial")."&nbsp;".$fc_save." <span>";
        echo __("Start speaking at the tone. Press # when finished.")."</span></a>";
        echo __("and speak the message you wish to record. Press # when finished.")."\n";
        echo '</div>';
    } else { ?>
        <form name="xtnprompt" method="post">
        <input type="hidden" name="display" value="recordings">
        <?php
        echo '<p>'.__("If you wish to make and verify recordings from your phone, please enter your extension number here:").'</p>'; 
?>

<div class="field has-addons">
  <div class="control">
    <input class="input" type="text" name="usersnum" tabindex="<?php echo ++$tabindex;?>" autofocus>
  </div>
  <div class="control">
    <input class="button is-info" type="submit" value="<?php echo __("Go")?>" tabindex="<?php echo ++$tabindex;?>" />
  </div>
</div>
        </form>
    <?php } ?>

  </div>

  <div <?php echo $tab_content_active_2;?> data-content="2">
 
    <div id="controls">
         <button id="recordButton" class='audio'><?php echo __('Record');?></button>
         <button id="pauseButton"  class='audio' ><?php echo __('Pause');?></button>
         <button id="stopButton"  class='audio' ><?php echo __('Stop');?></button>
    </div>
    <div id="formats"><?php echo __('Format: start recording to see sample rate')?></div>
    <ol id="recordingsList"></ol>
  </div>

  <div data-content="3" class="is-hidden">

    <form enctype="multipart/form-data" name="upload" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
        <?php echo __("Alternatively, upload a recording in any supported asterisk format. Note that if you're using .wav, (eg, recorded with Microsoft Recorder) the file <b>must</b> be PCM Encoded, 16 Bits, at 8000Hz")?>:
        <br/>
        <br/>
        <input type="hidden" name="display" value="recordings">
        <input type="hidden" name="action" value="recordings_start">
        <input type="hidden" name="usersnum" value="<?php echo $usersnum ?>">
        <!--input type="file" name="ivrfile" tabindex="<?php echo ++$tabindex;?>"/-->

<div class="file has-name is-fullwidth has-addons">
  <label class="file-label">
    <input class="file-input" type="file" name="ivrfile" id="ivrfile">
    <span class="file-cta">
      <span class="file-icon">
        <i class="fa fa-upload"></i>
      </span>
      <span class="file-label">
<?php echo __('Choose a file...')?>
      </span>
    </span>
    <span class="file-name" id="selected_file_name">
    </span>
  </label>
  <div class='control'><input type='button' class='button is-info' value="<?php echo __("Upload")?>" onclick="document.upload.submit(upload);$.LoadingOverlay('show');" tabindex="<?php echo ++$tabindex;?>"/></div>
</div>

    </form>
 
  </div>
</div>

<?php } ?>

<!--/div-->

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
        $msg = sprintf(__("File %s successfully uploaded"),$_FILES['ivrfile']['name']); 
        echo "<article class='message is-success'><div class='message-body'>$msg</div></article>";

        $rname = rtrim(basename($_FILES['ivrfile']['name'], $suffix), '.');
    } 

    if(isset($_GET['fname'])) { $rname = $_GET['fname']; } else { $rname=''; }

    $self  = $_SERVER['PHP_SELF'];
    $query = $_SERVER['QUERY_STRING'];
    parse_str($query,$parameters);
    unset($parameters['fname']);
    $final_query = http_build_query($parameters);

?>
        
    <form id="formprompt2" data-target="formprompt2" name="formprompt2" action="<?php echo $self."?".$final_query;?>" method="post" onsubmit="return rec_onsubmit(this);">
    <input type="hidden" name="action" value="recorded">
    <input type="hidden" name="display" value="recordings">
    <!--input type="hidden" name="usersnum" value="<?php echo $usersnum ?>"-->
    <?php

    if (!empty($usersnum)) { 

        echo '<div>';
        echo __("Using your phone,")."<a href=\"#\" class=\"info\">".__(" dial")."&nbsp;".$fc_save." <span>";
        echo __("Start speaking at the tone. Press # when finished.")."</span></a>";
        echo __("and speak the message you wish to record. Press # when finished.")."\n";
        echo '</div>';
    ?>
        <h5><?php echo __("Step ".++$step).": ".__("Verify")?></h5>
        <p> <?php echo __("After recording or uploading,")."&nbsp;<em>".__("dial")."&nbsp;".$fc_check."</em> ".__("to listen to your recording.")?> </p>
        <p> <?php echo __("If you wish to re-record your message, dial")."&nbsp;".$fc_save; ?></p>

    <?php
    } else {
        if($rname<>'') {
            $msg = sprintf(__("File %s successfully uploaded"),$_FILES['ivrfile']['name']); 
            echo "<article class='message is-success'><div class='message-body'>$msg</div></article>";
    //        echo "<h5>".__("Step ".++$step).": ".__("Name")."</h5>";
        }
    } 
    ?>

    <?php if(isset($_GET['fname']) || !empty($usersnum) || isset($_FILES['ivrfile'])) {
            echo "<h5>".__("Step ".++$step).": ".__("Name")."</h5>";
?>


<div class="field">
  <label class="label"><?php echo __("Name this Recording")?></label>
  <div class="control">
    <input autofocus class="input" type="text" name="rname" value="<?php echo $rname; ?>" tabindex="<?php echo ++$tabindex;?>">
  </div>
</div>

    <h5><?php echo __("Step ".++$step).": ".__("Save")?> </h5> 
    <div><?php
    echo __("Click \"SAVE\" when you are satisfied with your recording");
    echo "<input type=\"hidden\" name=\"suffix\" value=\"$suffix\">\n"; ?>
    </div>
<div class='my-2'>
<input name="Cancel" type="submit" class="button is-info" value="<?php echo _dgettext('amp','Cancel')?>" tabindex="<?php echo ++$tabindex;?>">
<input name="Submit" type="submit" class="button is-primary" value="<?php echo __('Save')?>" tabindex="<?php echo ++$tabindex;?>">
</div>
    <?php recordings_form_jscript(); ?>
    </form>

<?php } ?>

    </div>
    <script>

    $(function() {
        ipbx.msg.framework.format_one_channel = '<?php echo __("Format: 1 channel pcm @");?>'
        ipbx.msg.framework.pause = '<?php echo __("Pause");?>'
        ipbx.msg.framework.resume = '<?php echo __("Resume");?>'
        <?php echo js_display_confirmation_toasts(); ?>
    });
    </script>
<?php
}

function recording_editpage($id, $num, $warn_message='') {
    global $fcbase;
    global $default_pos;
    global $fcode;
    global $fcode_pass;
    global $recordings_astsnd_path;
    global $tabindex;
    global $asterisk_conf;
    $extdisplay=$id;
    $tabindex=1;
?>

    <div class="content" style="display:table;">
    <h2><?php echo __("Edit System Recording") ?></h2>

<?php

    if($warn_message!='') { echo $warn_message;  }

    $this_recording = recordings_get($id);
    if (!$this_recording) {
        echo "<tr><td colspan=2><h2>Error reading Recording ID $id - Aborting</h2></td></tr></table>";
        return;
    }?>
    <?php
    $usage_list = recordings_list_usage($id);
    if (count($usage_list)) {
?>
        <a href="#" class="info"><?php echo __("Usage List");?><span><?php echo __("This recording is being used in the following instances. You can not remove this recording while being used. To re-record, you can enable and use the feature code below if allowed.");?></span></a>
<?php
        $count = 0;


        foreach ($usage_list as $link) {
            $label = '<span><i class="fa fa-link mr-1"></i>'.$link['description'].'</span>';
            echo "<br /><a href=".$link['url_query'].">".$label."</a>";
        }
    } else {
        /*
        $delURL = "config.php?display=recordings&amp;action=delete&amp;usersnum=".urlencode($num)."&amp;id=$id";
        $tlabel = __("Remove Recording");
        $label = '<span><img width="16" height="16" border="0" title="'.$tlabel.'" alt="" src="assets/recordings/images/sound_delete.png"/>&nbsp;'.$tlabel.'</span>';
        echo "<a href='".$delURL."'>".$label."</a>";
        echo "<i style='font-size: x-small'>&nbsp;(";
        echo __("Note, does not delete file from computer");
        echo ")</i>";
         */
    }
    ?>
    <form data-target="formprompt" id="formprompt" name="formprompt" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" onsubmit="return rec_onsubmit(this);">
    <input type="hidden" name="action" value="edited">
    <input type="hidden" name="display" value="recordings">
    <input type="hidden" name="usersnum" value="<?php echo $num ?>"> 
    <input type="hidden" name="id" value="<?php echo $id ?>">
    <input type="hidden" name="extdisplay" value="<?php echo $extdisplay ?>">

    <table class='table is-borderless is-narrow'>
    <tr><td colspan="2"><h5><?php echo _dgettext('amp','General Settings');?></h5></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo __("Change Name");?><span><?php echo __("This changes the short name, visible on the right, of this recording");?></span></a></td>
        <td><input type="text" name="rname" value="<?php echo $this_recording['displayname'] ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
    <tr>
            <td><a href="#" class="info"><?php echo __("Descriptive Name");?><span><?php echo __("This is displayed, as a hint, when selecting this recording in Queues, Digital Receptionist, etc");?></span></a></td>
            <td><textarea name="notes" class="textarea" tabindex="<?php echo ++$tabindex;?>"><?php echo $this_recording['description'] ?></textarea></td>
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
                $("#sysrec'.$counter.'").trigger("chosen:updated");
                $(this).hide();
            });
        });
        ';
    }
    $jq_autofill.='
    $(function() {
        $optlist = $("#sysrec0 option");
        $(".autofill").each(function() {
            pos = $(this).attr("id").substr(6);
            val = $("#sysrecval"+pos).val();
            if(val!="" && typeof val != "undefined") {
                $("#sysrec"+pos).empty().append($optlist.clone()).val($("#sysrecval"+pos).val());
                $("#sysrec"+pos).trigger("chosen:updated");
            }
        });
    });
    ';
 
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
        <td><a class="info" href="#"><?php echo __("Link to Feature Code")?><span><?php echo __("Check this box to create an options feature code that will allow this recording to be changed directly.")?></span></a>
        </td>
        <td>
    <input type='checkbox' tabindex="<?php echo ++$tabindex;?>" name='fcode' id="fcode" <?php if ($rec['fcode']=="1") { echo 'CHECKED'; }?> onclick="resetDefaultSound();"><?php echo sprintf(__("Optional Feature Code %s"),$rec_code)?>
        </td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo __("Feature Code Password");?><span><?php echo __("Optional password to protect access to this feature code which allows a user to re-record it.");?></span></a></td>
        <td><input type="text" name="fcode_pass" id="fcode_pass" value="<?php echo $rec['fcode_pass'] ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
<?php
    } else {
?>
    <tr>
    <td colspan="2"><a class="info" href="#"><?php echo __("Direct Access Feature Code Not Available")?><span><?php echo __("Direct Access Feature Codes for recordings are not available for built in system recordings or compound recordings made of multiple individual ones.")?></span></a>
    </td>
    </tr>
<?php
    }
?>

    <tr><td colspan="2"><hr /></td></tr>
    </table>
    <?php echo __("Files");?>:<br />
    <table>
    <?php
    // globals seem to busted in PHP5 define here for now
    $recordings_astsnd_path = isset($asterisk_conf['astdatadir'])?$asterisk_conf['astdatadir']:'/var/lib/asterisk';
    $recordings_astsnd_path .= "/sounds/";

    // recordings_display_sndfile functions need to be run above so we have $default_pos set
    //
    echo $sndfile_html;
    ?>
    </table>

    <?php recordings_popup_jscript(); ?>
    <?php recordings_form_jscript(); ?>
    <script>
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

    $(function() {
        $('#sysrec0').css('width','250px');
        var $reclist = $("#sysrec0");
        var $optlist = $("#sysrec0 option");
        //$(".slclass").css({ visibility: "visible" }).hide();
        $(".slclass").css("visibility", "visible").hide();
        $(".autofill").width($reclist.width()).chosen({search_contains: true, no_results_text: '<?php echo __("No Recordings Found")?>', allow_single_deselect: true, placeholder_text_single: ipbx.msg.framework.selectoption});
        <?php echo $jq_autofill; ?>
    });

     <?php echo js_display_confirmation_toasts(); ?>

    </script>
    </form>
    </div>
<?php

    $warn_msg = __("Note, does not delete file from computer");
    echo form_action_bar($extdisplay,'formprompt',false,true,$warn_msg); 
}

function recording_sidebar($id, $num) {
    $display='recordings';
    $extdisplay=$id;
    $type='';
    $rnaventries = array();
    $rnaventries[] = array(-1,__("Built-in Recordings"),'','',"&usersnum=".urlencode($num)."&action=system");
    $tresults   = recordings_list();
    foreach($tresults as $tresult) {
        // result[0] = record id
        // result[1] = record name
        // result[2] = id to print
        // result[3] = extra css class
        // result[4] = custom param
        $rnaventries[] = array($tresult[0],$tresult[1],'','',"&usersnum=".urlencode($num)."&action=edit");
    }
    drawListMenu($rnaventries, $type, $display, $extdisplay);
}

function recordings_popup_jscript() {
?>
    <script>
    function popUp(URL,optionId) {
        var selIndex=optionId.selectedIndex
        var file=encodeURIComponent(optionId.options[selIndex].value)

        /*alert(selIndex);*/
        if (file != "")
            popup = window.open(URL+file, 'play', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=320,height=110');
    }
    </script>
<?php
}

function recordings_form_jscript() {
?>
    <script>

    function rec_onsubmit(theForm) {

        if(theForm.action.value=='delete') { return true; }

        var msgInvalidFilename = "<?php echo __("Please enter a valid Name for this System Recording"); ?>";

        defaultEmptyOK = false;
        if(typeof theForm.rname != 'undefined') {
            if (!isFilename(theForm.rname.value)) {
                return warnInvalid(theForm.rname, msgInvalidFilename);
            }
        }
        $.LoadingOverlay('show');
        return true;
    }

    </script>

<?php
}

function recording_sysfiles() {
    global $asterisk_conf;
    $astsnd = isset($asterisk_conf['astdatadir'])?$asterisk_conf['astdatadir']:'/var/lib/asterisk';
    $astsnd .= "/sounds/";
    $sysrecs = recordings_readdir($astsnd, strlen($astsnd)+1);
?>
    <div class="content" style="display:table;">
    <h2><?php echo __("Built-in Recordings") ?></h2>
    <h5><?php echo __("Select System Recording")?></h5>
    <form name="xtnprompt" method="post">
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
    <input class="button is-small is-info" name="Submit" type="submit" value="<?php echo __("Go"); ?>">
    <p />
    </div>
<?php
}

function recordings_display_sndfile($item, $count, $max, $astpath, $fcode) {
    global $default_pos;
    global $amp_conf;
    global $asterisk_conf;

    $disabled_state = $fcode == 0 ? "" : "disabled='true' ";
    $hidden_state = $fcode == 0 ? "" : "style='visibility:hidden' ";

    $html_text = "";
    // Note that when using this, it needs a <table> definition around it.

    if ($count == 0) {
        $astsnd = isset($asterisk_conf['astdatadir'])?$asterisk_conf['astdatadir']:'/var/lib/asterisk';
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
        $html_txt .=  "<input type='hidden' id='sysrecval$count' value='$item' />\n";
        $html_txt .=  "<select $disabled_state id='sysrec$count' name='sysrec$count' class='autofill'>\n";
        //$html_txt .=  "<option SELECTED>$item</option>\n";
        $html_txt .=  "</select></td>\n";
    }

    $html_txt .=  "<td>";
    $audio=$astpath;

    include_once("crypt.php");
    $crypt = new Crypt();
    $REC_CRYPT_PASSWORD = (isset($amp_conf['AMPPLAYKEY']) && trim($amp_conf['AMPPLAYKEY']) != "")?trim($amp_conf['AMPPLAYKEY']):'moufdsuu3nma0';
    $audio = urlencode($crypt->encrypt($audio,$REC_CRYPT_PASSWORD));
    $recurl=$_SERVER['PHP_SELF']."?display=recordings&action=popup&recordingpath=$audio&recording=";

    $html_txt .=  "<button type='submit' class='button is-small is-info' ".(($count)?$hidden_state:'')." id='play$count' onClick=\"javascript:popUp('$recurl',document.formprompt.sysrec$count); return false;\" ><span class='icon is-small'><i class='fa fa-play'></i></span></button>";

    $html_txt .=  "</td>\n";

    if ($count==0) {
        $html_txt .=  "<td></td>\n";
    } else {
        $html_txt .=  "<td class='action'>";
        $html_txt .=  "<button $hidden_state name='up$count' id='up$count' value='Move Up' class='button is-small is-link' data-tooltip='".__('Move Up')."'><span class='icon is-small'><i class='fa fa-arrow-up'></i></span></button>\n";
        $html_txt .=  "</td>\n";
    } if ($count > $max) {
    $html_txt .=  "<td></td>\n";
        } else {
            $html_txt .=  "<td class='action'>";
            $html_txt .=  "<button $hidden_state name='down$count' id='down$count' value='Move Down' class='button is-small is-link' data-tooltip='".__('Move Down')."'><span class='icon is-small'><i class='fa fa-arrow-down'></i></span></button>\n";
            $html_txt .=  "</td>\n";
        }
    $html_txt .=  "<td class='action'><button $hidden_state name='del$count' id='del$count' value='Delete' class='button is-small is-danger' data-tooltip='".__('Delete')."'><span class='icon is-small'><i class='fa fa-trash'></i></span></button>\n";
    $html_txt .=  "</td><td class='action'><i id='selectload$count' class='fa fa-spinner fa-spin' style='display:none;'></i></td>\n";

    $html_txt .=  "</tr>\n";
    return $html_txt;
}

?>
