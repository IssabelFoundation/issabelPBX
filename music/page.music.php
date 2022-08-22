<?php /* $Id$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$tabindex = 0;
$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$extdisplay = isset($_REQUEST['extdisplay'])?htmlspecialchars(strtr($_REQUEST['extdisplay']," ./\"\'\`", "------")):'';
$volume = isset($_REQUEST['volume']) && is_numeric($_REQUEST['volume']) ? $_REQUEST['volume'] : '';

if(!isset($_REQUEST['action']) && $extdisplay=='') {
    $action='add';
}

// Determine default path to music directory, old default was mohmp3, now settable
$path_to_moh_dir = $amp_conf['ASTVARLIBDIR'].'/'.$amp_conf['MOHDIR'];

global $display;

if ($extdisplay == null) $extdisplay = 'default';
$display='music';

global $amp_conf;

if ($extdisplay == "default") {
    $path_to_dir = $path_to_moh_dir; //path to directory u want to read.
} else {
    $path_to_dir = $path_to_moh_dir."/$extdisplay"; //path to directory u want to read.
}

switch ($action) {
    case "randon":
    case "randoff":
        if($action=='randon') {
            touch($path_to_dir."/.random");
        } else {
            unlink($path_to_dir."/.random");
        }
        die('OK');
        break;
    case "addednewstream":
    case "editednewstream":
        $stream = isset($_REQUEST['stream'])?$_REQUEST['stream']:'';
        $format = isset($_REQUEST['format'])?trim($_REQUEST['format']):'';
        if ($format != "") {
            $stream .= "\nformat=$format";
        }
        makestreamcatergory($path_to_dir,$stream);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        redirect_standard();
    case "addednew":
        music_makemusiccategory($path_to_dir);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
        $_SESSION['msgtype']='success';
        redirect_standard();
        break;
    case "deletefile":

        if (isset($_REQUEST['del'])) {

            $file_array = build_list();
            $numf = count($file_array);

            $del = $_REQUEST['del'];
            if($del=='') {
                $_SESSION['msg']=base64_encode(_('No file specified'));
                $_SESSION['msgtype']='error';
                redirect_standard('extdisplay');
            }
            if (strpos($del, "\"") || strpos($del, "\'") || strpos($del, "\;")) {
                $html =
                "<div class='content'>
                <article class='message is-warning'>
                  <div class='message-header'>
                    <p>"._("Potential Security Breach")."</p>
                  </div>
                  <div class='message-body'>
                    <p>"._("You are trying to use an invalid character.").
                  "</div>
                  </article>
                </div></div></body></html>";
                die($html);
            }
            if (($numf == 1) && ($extdisplay == "default") ){
                $_SESSION['msg']=base64_encode(_("You must have at least one file for On Hold Music.  Please upload one before deleting this one."));
                $_SESSION['msgtype']='warning';
                redirect_standard('extdisplay');
            } else {
                if (@unlink($path_to_dir."/".$del)) {
                    $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
                    $_SESSION['msgtype']='success';
                    needreload();
                } else {
                    $_SESSION['msg']=base64_encode(_('Could not delete file'));
                    $_SESSION['msgtype']='error';
                }
                redirect_standard('extdisplay');
            }
        }

        break;
    case "addedfile":

        // Check to see if the upload failed for some reason
        if (isset($_FILES['mohfile']['name']) && !is_uploaded_file($_FILES['mohfile']['tmp_name'])) {
            if (strlen($_FILES['mohfile']['name']) == 0) {
                $msg = _("Error Processing")."! "._("No file provided")." "._("Please select a file to upload");
            } else {
                $msg = _("Error Processing")." ".htmlentities($_FILES['mohfile']['name'])."! "._("Check")." upload_max_filesize "._("in")." /etc/php.ini";
            }
            $_SESSION['msg']=base64_encode(dgettext('amp',$msg));
            $_SESSION['msgtype']='error';
            redirect_standard('extdisplay');

        }

        if (isset($_FILES['mohfile']['tmp_name']) && is_uploaded_file($_FILES['mohfile']['tmp_name'])) {
            //echo $_FILES['mohfile']['name']." uploaded OK";
            move_uploaded_file($_FILES['mohfile']['tmp_name'], $path_to_dir."/orig_".$_FILES['mohfile']['name']);
    
            if ($amp_conf['AMPMPG123']) {
                $process_err = process_mohfile($_FILES['mohfile']['name'],true,$volume);
            } else {
                $process_err = process_mohfile($_FILES['mohfile']['name'],($_REQUEST['onlywav'] != ''));
            }
    
            if (isset($process_err)) {
                $msg = _("Error Processing").": \"$process_err\" for ".htmlentities($_FILES['mohfile']['name'])."! ";
                $msg.= _("This is not a fatal error, your Music on Hold may still work.");
                $_SESSION['msg']=base64_encode(dgettext('amp',$msg));
                $_SESSION['msgtype']='warning';
            } else {
                $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
                $_SESSION['msgtype']='success';
            }
            redirect_standard('extdisplay');
        }

    break;
    case "delete":
        //$fh = fopen("/tmp/music.log","a");
        //fwrite($fh,print_r($_REQUEST,true));
        if($extdisplay!='') {
            music_rmdirr("$path_to_dir");
            $path_to_dir = $path_to_moh_dir;
            $extdisplay='default';
            needreload();
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
            $_SESSION['msgtype']='warning';
            redirect_standard();
        }
    break;
}


$rnaventries = array();
$tresults    = music_list();
$rnaventries[] = array('',_("Add Streaming Category"),'','','&action=addstream');
foreach ($tresults as $tresult) {
    if ($tresult != "none") {
        ( $tresult == 'default' ? $ttext = _("default") : $ttext = $tresult );
        $rnaventries[] = array($tresult,$ttext,'');
    }
}
drawListMenu($rnaventries, $type, $display, $extdisplay);
?>
<div class='content'>

<?php
function makestreamcatergory($path_to_dir,$stream) {
    if (!is_dir($path_to_dir)) {
        music_makemusiccategory($path_to_dir);
    }
    $fh=fopen("$path_to_dir/.custom","w");
    fwrite($fh,$stream);
    fclose($fh);
}

function build_list() {
    global $path_to_dir;
    $pattern = '';
    $handle=opendir($path_to_dir) ;
    $extensions = array('mp3','MP3','wav','WAV'); // list of extensions to match

    //generate the pattern to look for.
    $pattern = '/(\.'.implode('|\.',$extensions).')$/i';

    //store file names that match pattern in an array
    $i = 0;
    while (($file = readdir($handle))!==false) {
        if ($file != "." && $file != "..") {
            if(preg_match($pattern,$file)) {
                $file_array[$i] = $file; //pattern is matched store it in file_array.
                $i++;
            }
        }
    }
    closedir($handle);
    $emtpy = array();
    return (isset($file_array))?$file_array:$empty;
}

function draw_list($file_array, $path_to_dir, $extdisplay) {
    global $display;
    //list existing mp3s and provide delete buttons
    if ($file_array) {
        echo "<tr><td colspan=2><h5>"._('Music Files')."</h5></td></tr>";
        echo "<tr><td colspan=2>";
        echo "<table class='table is-striped'>";
        $cont=0;
        foreach ($file_array as $thisfile) {
            $cont++;

            echo "<tr><td>$thisfile</td>";
            echo "<td class='has-text-right'><button type='button' name='del$cont' id='del$cont' value='".urlencode($thisfile)."' class='button is-small is-danger' data-tooltip='"._('Delete')."' onclick='edit_onsubmit()'><span class='icon is-small'><i class='fa fa-trash'></i></span></button></td></tr>";

        }
        echo "</table>";
        echo "</td></tr>";
    }
}

function process_mohfile($mohfile,$onlywav=false,$volume=false) {
    global $path_to_dir;
    global $amp_conf;

    $output = 0;
    $returncode = 0;
    $mohfile = escapeshellcmd($mohfile);
    $origmohfile=$path_to_dir."/orig_".$mohfile;
    if ($amp_conf['AMPMPG123']) {
        if($onlywav) {
            $newname = substr($mohfile,0,strrpos($mohfile,"."));

            // If we are dealing with an MP3, we need to decode it to a wav file. mpg123 -w writes the converted output to $origmohfile.wav
            if (strtoupper(substr($origmohfile,-4)) == '.MP3') {
                $mpg123cmd = "mpg123 -w \"".substr($origmohfile,0,strrpos($origmohfile,".")).".wav\" \"".$origmohfile."\" 2>&1 ";
                exec($mpg123cmd, $output, $returncode);
            }
            $newmohfile = $path_to_dir."/wav_".$newname.".wav";
            //We need to take the output of mpg123 to use in the sox conversion. If we used $origmohfile directly then we would be bypassing mpg123. The mpg123 might not be needed on some systems if we had the sox version with mp3 compiled in. The standard rpmforge sox rpm does not have mp3 included.
            //$soxcmd = "sox \"".$origmohfile."\"";
            $source_file = substr($origmohfile,0,strrpos($origmohfile,".")).".wav";
            $soxcmd = "sox \"".$source_file."\"";
            $soxcmd .= " -r 8000 -c 1 \"".$newmohfile."\"";
            if($volume){
                $soxcmd .= " vol ".$volume;
            }
            $soxresample = " rate -ql ";
            exec($soxcmd.$soxresample."2>&1", $output, $returncode);

            if ($returncode != 0) {
                // try it again without the resample in case the input sample rate was the same
                //
                $output = array();
                $returncode = 0;
                exec("rm -rf \"".$newmohfile."\"");
                exec($soxcmd."2>&1", $output, $returncode);
                // if sox prints no warnings, then despite the return code we will assume it is good
                if (empty($output)) {
                    if (copy($source_file,$newmohfile)) {
                        $returncode = 0;
                    } else {
                        $returncode = 1;
                        $output[] = _("sox failed to convert file and original could not be copied as a fall back");
                    }
                }
            }
        }
    } else { // AMPMPG123
        $newname = strtr($mohfile,"&", "_");
        if(strstr($newname,".mp3")) {
            $onlywav = false;
        }

        if(!$onlywav) {
            $newmohfile=$path_to_dir."/". ((strpos($newname,'.mp3') === false) ? $newname.".mp3" : $newname);
            $lamecmd="lame --cbr -m m -t -F \"".$origmohfile."\" \"".$newmohfile."\" 2>&1 ";
            if (strpos($newmohfile,'.mp3') !== false) {
                exec($lamecmd, $output, $returncode);
            }
        } else {
            $newmohfile = $path_to_dir."/wav_".$newname;
            $soxcmd = "sox \"".$origmohfile."\" -r 8000 -c 1 \"".$newmohfile."\" ";
            $soxresample = "rate -ql ";
            exec($soxcmd.$soxresample."2>&1", $output, $returncode);
            if ($returncode != 0) {
                // try it again without the resample in case the input sample rate was the same
                //
                exec("rm -rf \"".$newmohfile."\"");
                exec($soxcmd."2>&1", $output, $returncode);
            }
        }
    } // AMPMPG123

    if ($returncode != 0) {
        return join("<br>\n", $output);
    }
    $rmcmd="rm -f \"". $origmohfile."\"";
    exec($rmcmd);
    if ($amp_conf['AMPMPG123']) {
        // If this started as an mp3, we converted it to a wav and then transcoded it from there,
        // so we have two "original" files to delete
        //
        if (strpos($origmohfile,'.mp3') | strpos($origmohfile,'.MP3') !== false)  {
            $rmcmd="rm -f \"". substr($origmohfile,0,strrpos($origmohfile,".")).".wav\"";
            exec($rmcmd);
        }
    } // AMPMPG123
    return null;
}

?>

<?php
if ($action == 'add') {
    ?>
    <h2><?php echo _("Add Music on Hold Category")?></h2>
    <form id="mainform" name="addcategory" method="post" onsubmit="return addcategory_onsubmit(this);">
    <input type="hidden" name="display" value="<?php echo $display?>">
    <input type="hidden" name="action" value="addednew">
    <table class='table is-narrow is-borderless'>
    <tr><td colspan="2"><h5><?php echo dgettext('amp','General Settings')?></h5></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Category Name")?><span><?php echo _("Allows you to Set up Different Categories for music on hold.  This is useful if you would like to specify different Hold Music or Commercials for various ACD Queues.")?> </span></a></td>
        <td><input autofocus class="input w100" type="text" name="extdisplay" value=""></td>
    </tr>
    </table>
    </form>
<script>

function addcategory_onsubmit(theForm) {
    var msgInvalidCategoryName = "<?php echo _('Please enter a valid Category Name'); ?>";
    var msgReservedCategoryName = "<?php echo _('Categories: \"none\" and \"default\" are reserved names. Please enter a different name'); ?>";

    defaultEmptyOK = false;
    if (!isAlphanumeric(theForm.extdisplay.value))
        return warnInvalid(theForm.extdisplay, msgInvalidCategoryName);
    if (theForm.extdisplay.value == "default" || theForm.extdisplay.value == "none" || theForm.extdisplay.value == ".nomusic_reserved")
        return warnInvalid(theForm.extdisplay, msgReservedCategoryName);

    return true;
}

<?php echo js_display_confirmation_toasts(); ?>
</script>
</div>
<?php echo form_action_bar($extdisplay); ?>

<?php
} else if ($action == 'addstream') {
?>
    <h2><?php echo _("Add Streaming Category")?></h2>
    <form id="mainform" name="addstream" method="post" onsubmit="return addstream_onsubmit(this);">
    <input type="hidden" name="display" value="<?php echo $display?>">
    <input type="hidden" name="action" value="addednewstream">
    <table class='table is-narrow is-borderless'>
    <tr><td colspan="2"><h5><?php echo dgettext('amp','General Settings')?></h5></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Category Name")?><span><?php echo _("Allows you to Set up Different Categories for music on hold.  This is useful if you would like to specify different Hold Music or Commercials for various ACD Queues.")?> </span></a></td>
        <td><input autofocus class="input w100" type="text" name="extdisplay" value=""></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Application:")?><span><?php echo _("This is the \"application=\" line used to provide the streaming details to Asterisk. See information on musiconhold.conf configuration for different audio and Internet streaming source options.")?> </span></a></td>
        <td><input type="text" name="stream" class="input w100" value=""></td>
    </tr>
    <tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Optional Format:")?><span><?php echo _("Optional value for \"format=\" line used to provide the format to Asterisk. This should be a format understood by Asterisk such as ulaw, and is specific to the streaming application you are using. See information on musiconhold.conf configuration for different audio and Internet streaming source options.")?> </span></a></td>
        <td><input type="text" name="format" class="input w100" value=""></td>
    </tr>
    </table>
</form>
<script>

function addstream_onsubmit(theForm) {
    var msgInvalidCategoryName = "<?php echo _('Please enter a valid Category Name'); ?>";
    var msgInvalidStreamName = "<?php echo _('Please enter a streaming application command and arguments'); ?>";
    var msgReservedCategoryName = "<?php echo _('Categories: \"none\" and \"default\" are reserved names. Please enter a different name'); ?>";

    defaultEmptyOK = false;
    if (!isAlphanumeric(theForm.extdisplay.value))
        return warnInvalid(theForm.extdisplay, msgInvalidCategoryName);
    if (theForm.extdisplay.value == "default" || theForm.extdisplay.value == "none" || theForm.extdisplay.value == ".nomusic_reserved")
        return warnInvalid(theForm.extdisplay, msgReservedCategoryName);
    if (isEmpty(theForm.stream.value))
        return warnInvalid(theForm.stream, msgInvalidStreamName);

    return true;
}

<?php echo js_display_confirmation_toasts(); ?>
</script>
</div>
<?php echo form_action_bar($extdisplay); ?>

<?php
} else {
?>
<?php
    if (file_exists("{$path_to_dir}/.custom")) {
        $application = file_get_contents("{$path_to_dir}/.custom");
        $application = explode("\n",$application);
        if (isset($application[1])) {
            $format = explode('=',$application[1],2);
            $format = $format[1];
        } else {
            $format = "";
        }
    } else {
        $application = false;
    }
    if(is_array($application)) {
        echo "<h2>"._("Edit Streaming Category").": $extdisplay</h2>";
    } else {
        echo "<h2>"._("Edit Music on Hold Category").": ".($extdisplay=="default"?_("default"):$extdisplay)."</h2>\n";
    }
    $disabledelete=true;
    if ($extdisplay!="default") {
        $disabledelete=false;
        //$delURL = $_SERVER['PHP_SELF'].'?display='.urlencode($display).'&action=delete&extdisplay='.urlencode($extdisplay);
        //$tlabel = sprintf(($application === false)?_("Delete Music Category %s"):_("Delete Streaming Category"),$extdisplay);
        //$label = '<span><img width="16" height="16" border="0" title="'.$tlabel.'" alt="" src="images/core_delete.png"/>&nbsp;'.$tlabel.'</span>';
?>
        <!--p><a href="<?php echo $delURL ?>"><?php echo $label; ?></a></p-->
<?php
    }
    if ($application !== false) {
?>
        <form id="mainform" name="editstream" method="post" onsubmit="return editstream_onsubmit(this);">
        <input type="hidden" name="display" value="<?php echo $display?>">
        <input type="hidden" name="action" value="editednewstream">
        <table class='table is-narrow is-borderless'>
        <tr><td colspan="2"><h5><?php echo dgettext('amp','General Settings')?></h5></td></tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Application:")?><span><?php echo _("This is the \"application=\" line used to provide the streaming details to Asterisk. See information on musiconhold.conf configuration for different audio and Internet streaming source options.")?> </span></a></td>
            <td><input type="text" autofocus name="stream" class="input w100" value="<?php echo $application[0]?>"></td>
        </tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Optional Format:")?><span><?php echo _("Optional value for \"format=\" line used to provide the format to Asterisk. This should be a format understood by Asterisk such as ulaw, and is specific to the streaming application you are using. See information on musiconhold.conf configuration for different audio and Internet streaming source options.")?> </span></a></td>
            <td><input type="text" name="format" size="6" value="<?php echo htmlentities($format)?>"></td>
        </tr>
        </table>

</form>
<script>

function editstream_onsubmit(theForm) {
    var msgInvalidStreamName = "<?php echo _('Please enter a streaming application command and arguments'); ?>";

    defaultEmptyOK = false;
    if (isEmpty(theForm.stream.value))
        return warnInvalid(theForm.stream, msgInvalidStreamName);

    $.LoadingOverlay('show');
    return true;
}
<?php echo js_display_confirmation_toasts(); ?>
</script>
</div>
<?php echo form_action_bar($extdisplay); ?>

<?php
    } else { // normal moh dir
?>

    <form id="mainform" enctype="multipart/form-data" name="upload" method="POST">
        <input type="hidden" name="display" value="<?php echo $display?>">
        <input type="hidden" name="extdisplay" value="<?php echo "$extdisplay" ?>">
        <input type="hidden" name="action" value="addedfile">
        <input type="hidden" name="del" value="">

        <table class='table is-narrow is-borderless'>
        <tr><td colspan="2"><h5><?php echo dgettext('amp','General Settings')?></h5></td></tr>

<?php
    if (file_exists("{$path_to_dir}/.random")) {
        $checked=' checked="checked" ';
    } else {
        $checked='';
    }
    echo "<tr>\n<td>";
    echo _("Enable Random Play");
    echo "</td>\n<td class='has-text-right'>";

    echo "<div class='field'><input type='checkbox' class='switch' id='enablerandom' name='enablerandom' value='1' $checked tabindex='".++$tabindex."' onclick='enable_random(this)'/><label style='height:auto; line-height:1em; padding-left:3em;' for='enablerandom'>&nbsp;</label></div>\n";
    echo "</td>\n</tr>";

    echo "<tr><td colspan=2><h5>";
    echo _("Upload a .wav or .mp3 file");
    echo "</h5></td></tr>";
?>

<tr><td colspan=2>
<div class="file has-name is-fullwidth has-addons">
  <label class="file-label">
    <input class="file-input" type="file" name="mohfile" id="mohfile">
    <span class="file-cta">
      <span class="file-icon">
        <i class="fa fa-upload"></i>
      </span>
      <span class="file-label">
<?php echo _('Choose a file...')?>
      </span>
    </span>
    <span class="file-name" id="selected_file_name">
    </span>
  </label>
  <div class='control'><input type='button' style='font-size:0.85em;' class='button is-small is-info' value="<?php echo _("Upload")?>" onclick="document.upload.submit(upload);$.LoadingOverlay('show');" tabindex="<?php echo ++$tabindex;?>"/></div>
</div>
</td></tr>

<?php
    if ($amp_conf['AMPMPG123']) {
?>
<tr><td>
        <a href="#" class="info"><?php echo "&nbsp;"._("Volume Adjustment")?><span> <?php echo _("The volume adjustment is a linear value. Since loudness is logarithmic, the linear level will be less of an adjustment. You should test out the installed music to assure it is at the correct volume. This feature will convert MP3 files to WAV files. If you do not have mpg123 installed, you can set the parameter: <strong>Convert Music Files to WAV</strong> to false in Advanced Settings") ?></span></a>
</td><td>
        <select name="volume" tabindex="<?php echo ++$tabindex;?>" class="componentSelect">
            <option value="1.50"><?php echo _("Volume 150%")?></option>
            <option value="1.25"><?php echo _("Volume 125%")?></option>
            <option value="" selected><?php echo _("Volume 100%")?></option>
            <option value=".75"><?php echo _("Volume 75%")?></option>
            <option value=".5"><?php echo _("Volume 50%")?></option>
            <option value=".25"><?php echo _("Volume 25%")?></option>
            <option value=".1"><?php echo _("Volume 10%")?></option>
        </select>
</td></tr>
<?php
    } else { // AMPMPG123
?>
<tr>
<td>
<?php echo _("Do not encode wav to mp3")?>
</td>
<td class='has-text-right'>
<?php echo "<div class='field'><input type='checkbox' class='switch' id='onlywav' name='onlywav' value='1' checked='checked' tabindex='".++$tabindex."'/><label style='height:auto; line-height:1em; padding-left:3em;' for='onlywav'>&nbsp;</label></div>\n"; ?>
</td>
</tr>
<?php
    } // AMPMPG123
?>


    <?php

        //build the array of files
        $file_array = build_list();
        $numf = count($file_array);
    } // normal moh dir



    if ($application === false) {
        $file_array = build_list();
        draw_list($file_array, $path_to_dir, $extdisplay);
    }
?>
</table>
</form>
<script>

    up.compiler('.content',function() {
        $('#mohfile').on('change',function() { $('input[name=action]').val('addedfile'); $('#selected_file_name').text(this.value.replace(/.*[\/\\]/, '')); });
    })

    function edit_onsubmit() {
        button_id = this.document.activeElement.getAttribute("id");
        myfile = $('#'+button_id).val();
        $('input[name=del]').val(myfile);
        confirm_delete($('#mainform'),ipbx.msg.framework.wontrevert,'deletefile');
    }

    function enable_random(element) {
        if($(element).prop('checked')==true) {
            action='randon';
        } else {
            action='randoff';

        }

        fetch(window.location.href+'&quietmode=1&action='+action).then(response=>{ 
            if(response.ok) {
                if(response.statusText=='OK') {
                    sweet_toast('success',ipbx.msg.framework.item_modified)
                } else {
                    sweet_toast('error',ipbx.msg.framework.invalid_response)
                }
            } else {
                sweet_toast('error',ipbx.msg.framework.invalid_response)
            }
        });
    }

<?php echo js_display_confirmation_toasts(); ?>


</script>
</div>
<?php 
    echo form_action_bar($extdisplay,'',$disabledelete); 
?>
<?php
}
?>
