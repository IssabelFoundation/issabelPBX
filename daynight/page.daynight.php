<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
/* $Id: page.daynight.php 3790 2007-02-16 18:52:53Z p_lindheimer $ */
// Copyright 2022 Issabel Foundation

$dispnum = "daynight"; //used for switch on config.php

$action             = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$password           = isset($_REQUEST['password'])?$_REQUEST['password']:'';
$fc_description     = isset($_REQUEST['fc_description'])?$_REQUEST['fc_description']:'';
$day_recording_id   = isset($_POST['day_recording_id']) ? $_POST['day_recording_id'] :  '';
$night_recording_id = isset($_POST['night_recording_id']) ? $_POST['night_recording_id'] :  '';

isset($_REQUEST['extdisplay'])?$extdisplay=$db->escapeSimple($_REQUEST['extdisplay']):$extdisplay='';

$rnaventries   = array();
$daynightcodes = daynight_list();
foreach($daynightcodes as $code) {
    $fcc = new featurecode('daynight', 'toggle-mode-'.$code['ext']);
    $fc = $fcc->getCode();
    unset($fcc);
    $dnobj = daynight_get_obj($code['ext']);
    $class = $dnobj['state'] == 'DAY' ? "has-text-success'" : "has-text-danger'";
    $rnaventries[] = array($code['ext'],$code['dest'],$fc,$class);
}
drawListMenu($rnaventries, $type, $display, $extdisplay);
?>

<div class='content'>

<?php

switch ($action) {
    case "add":
        daynight_show_edit($_POST,'add');
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        break;
    case "edit":
        daynight_show_edit($_POST);
        break;
    case "edited":
            daynight_edit($_POST,$extdisplay);
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
            $_SESSION['msgtype']='success';
            $_SESSION['msgtstamp']=time();
            redirect_standard('extdisplay');
            break;
    case "delete":
            daynight_del($extdisplay);
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
            $_SESSION['msgtype']='warning';
            $_SESSION['msgtstamp']=time();
            redirect_standard();
            break;
    default:
        daynight_show_edit($_POST,'add');
        break;
}


function daynight_show_edit($post, $add="") {
    global $db;
    global $extdisplay;
    global $tabindex;

    $tabindex = 0;
    $fcc = new featurecode('daynight', 'toggle-mode-'.$extdisplay);
    $code = $fcc->getCodeActive();
    unset($fcc);

    $dests = daynight_get_obj($extdisplay);
    $password = isset($dests['password'])?$dests['password']:'';
    $fc_description = isset($dests['fc_description'])?$dests['fc_description']:'';
    $state = isset($dests['state'])?$dests['state']:'DAY';
    $day_recording_id = isset($dests['day_recording_id'])?$dests['day_recording_id']:'';
    $night_recording_id = isset($dests['night_recording_id'])?$dests['night_recording_id']:'';

    if ($extdisplay != "") { 
        echo "<h2>"._("Edit Call Flow Toggle Control").": ".$fc_description."</h2>\n";
    } else {
        echo "<h2>"._("Add Call Flow Toggle Control")."</h2>\n";
    }

?>
<?php
        $usage_list = framework_display_destination_usage(daynight_getdest($extdisplay));
        if (!empty($usage_list)) {
?>
            <a href="#" class="info"><?php echo $usage_list['text'].'<br />'?><span><?php echo $usage_list['tooltip']?></span></a>
<?php
        }
        $timeconditions_refs = daynight_list_timecondition($extdisplay);
        if (!empty($timeconditions_refs)) {
            echo "<br />";
            foreach($timeconditions_refs as $ref) {
                $dmode = ($ref['dmode'] == 'timeday') ? _("Forces to Normal Mode (Green/BLF off)") : _("Forces to Override Mode (Red/BLF on)");
                $timecondition_id = $ref['dest'];
                $tcURL = $_SERVER['PHP_SELF'].'?'."display=timeconditions&extdisplay=$timecondition_id";
                $label = '<span><img width="16" height="16" border="0" title="'.sprintf(_("Linked to Time Condition %s - %s"),$timecondition_id,$dmode).'" alt="" src="images/clock_link.png"/>&nbsp;'.sprintf(_("Linked to Time Condition %s - %s"),$timecondition_id,$dmode).'</span>';
?>
                <a href="<?php echo $tcURL ?>"><?php echo $label; ?></a><br />
<?php
            }
        }
?>
    <form name="prompt" id="mainform" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return prompt_onsubmit(this);">
    <input type="hidden" name="action" value="edited" />
    <input type="hidden" name="display" value="daynight" />
    <input name="Submit" type="submit" style="display:none;" value="save" />
    <table class='table is-borderless is-narrow'>
    <tr>
        <td colspan="2">    
<?php 
    if ($extdisplay != '') {
        echo "<tr><td colspan=2>";
        echo "<div class='box'>".sprintf(_("Use feature code: %s to toggle the call flow mode"),"<strong>".$code."</strong>")."</div>";
        echo "</td></tr>";
    }
?>
<tr>
<td colspan=2>
<h5><?php echo dgettext('amp','General Settings');?></h5>
</td>
</tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Call Flow Toggle Feature Code Index:")?>
        <span><?php echo _("There are a total of 10 Feature code objects, 0-9, each can control a call flow and be toggled using the call flow toggle feature code plus the index.")?>
        </span></a>
        </td>
        <td>
<?php
if ($add == "add" && $extdisplay =="") {
?>
            <select name="extdisplay" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
            <?php
                $ids = daynight_get_avail();
                foreach ($ids as $id) {
                    echo '<option value="'.$id.'" >'.$id.'</option>';
                }
            ?>
            </select>
<?php
} else {
?>
        <input class="input" readonly="yes" size="1" type="text" name="extdisplay" value="<?php  echo $extdisplay ?>" tabindex="<?php echo ++$tabindex;?>">
<?php
}
?>
        </td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Description")?>:<span><?php echo _("Description for this Call Flow Toggle Control")?></span></a></td>
        <td><input class="input w100" autofocus type="text" name="fc_description" value="<?php  echo $fc_description ?>" tabindex="<?php echo ++$tabindex;?>">
        </td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Current Mode:")?>
        <span><?php echo _("This will change the current state for this Call Flow Toggle Control, or set the initial state when creating a new one.")?>
        </span></a>
        </td>
        <td>
            <select name="state" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
                <option value="DAY" <?php echo ($state == 'DAY' ? 'SELECTED':'') ?> ><?php echo _("Normal (Green/BLF off)");?></option> 
                <option value="NIGHT" <?php echo ($state == 'NIGHT' ? 'SELECTED':'') ?> ><?php echo _("Override (Red/BLF on)");?></option> 
            </select>
        </td>
    </tr>

<?php if(function_exists('recordings_list')) { //only include if recordings are enabled ?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Recording for Normal Mode")?><span><?php echo _("Message to be played in normal mode (Green/BLF off).<br>To add additional recordings use the \"System Recordings\" MENU to the left")?></span></a></td>
        <td>
            <select name="day_recording_id"  tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
            <?php
                $tresults = recordings_list();
                $default = (isset($day_recording_id) ? $day_recording_id : '');
                echo '<option value="0">' ._("Default") ."</option>\n";
                if (isset($tresults[0])) {
                    foreach ($tresults as $tresult) {
                        echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $default ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
                    }
                }
            ?>
            </select>
        </td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Recording for Override Mode")?><span><?php echo _("Message to be played in override mode (Red/BLF on).<br>To add additional recordings use the \"System Recordings\" MENU to the left")?></span></a></td>
        <td>
            <select name="night_recording_id"  tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
            <?php
                $default = (isset($night_recording_id) ? $night_recording_id : '');
                echo '<option value="0">' ._("Default") ."</option>\n";
                if (isset($tresults[0])) {
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
        <td><a href="#" class="info"><?php echo _("Optional Password")?>:<span><?php echo _('You can optionally include a password to authenticate before toggling the call flow. If left blank anyone can use the feature code and it will be un-protected')?></span></a></td>
        <td><input class="input w100" type="text" name="password" value="<?php  echo $password ?>" tabindex="<?php echo ++$tabindex;?>">
        </td>
    </tr>

    <tr><td colspan=2><hr /></td></tr>
<?php
    // Draw the destinations
    // returns an array, $dest['day'], $dest['night']
    // and puts null if nothing set

    drawdestinations(0, _("Normal Flow (Green/BLF off)"),   (isset($dests['day'])?$dests['day']:''));
    drawdestinations(1, _("Override Flow (Red/BLF on)"), (isset($dests['night'])?$dests['night']:''));

    //TODO: Check to make sure a destination radio button was checked, and if custom, that it was not blank
    //
?>

    </table>
    </form>

<script>
function prompt_onsubmit(theForm) {
    var msgInvalidPassword = "<?php echo _('Please enter a valid numeric password, only numbers are allowed'); ?>";
    defaultEmptyOK = true;
    if (!isInteger(theForm.password.value))
        return warnInvalid(theForm.password, msgInvalidPassword);

    $.LoadingOverlay('show');
    return true;
}

<?php echo js_display_confirmation_toasts(); ?>
    </script>

<?php echo form_action_bar($extdisplay); ?>

<?php
} //daynight function


// count is for the unique identifier
// dest is the target
//

function drawdestinations($count, $mode, $dest) { ?>
    <tr> 
        <td>
        <a href="#" class="info"><strong><?php echo $mode?></strong><span><?php echo sprintf(_("Destination to use when set to %s mode"),$mode);?></span></a>
        </td>
        <td> 
            <table> <?php echo drawselects($dest,$count); ?> 
            </table> 
        </td>
    </tr>
    <tr><td colspan=2><hr /></td></tr>
<?php
}
?>
