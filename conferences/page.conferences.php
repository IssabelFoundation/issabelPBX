<?php /* $Id$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$dispnum = "conferences"; //used for switch on config.php
$tabindex = 0;

isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';
//the extension we are currently displaying

$account = isset($_REQUEST['account']) ? $_REQUEST['account'] : '';
$extdisplay = isset($_REQUEST['extdisplay']) && $_REQUEST['extdisplay'] != '' ? $_REQUEST['extdisplay'] : $account;

$orig_account = isset($_REQUEST['orig_account']) ? $_REQUEST['orig_account'] : '';
$music = isset($_REQUEST['music']) ? $_REQUEST['music'] : '';
$users = isset($_REQUEST['users']) ? $_REQUEST['users'] : '0';

//check if the extension is within range for this user
if ($account != "" && !checkRange($account)){
    echo "<script>\$( function() {  sweet_alert('"._("Warning! Extension")." $account "._("is not allowed for your account.")."');});</script>";
} else {

    //if submitting form, update database
    switch ($action) {
        case "add":

            $conflict_url = array();
            $usage_arr = framework_check_extension_usage($account);
            if (!empty($usage_arr)) {
                $conflict_url = framework_display_extension_usage_alert($usage_arr);
            } elseif (conferences_add($account,$_REQUEST['name'],$_REQUEST['userpin'],$_REQUEST['adminpin'],$_REQUEST['options'],$_REQUEST['joinmsg_id'],$music,$users) !== false) {
                needreload();
                $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
                $_SESSION['msgtype']='success';
                redirect_standard();
            }
        break;
        case "delete":
            conferences_del($extdisplay);
            needreload();
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
            $_SESSION['msgtype']='warning';
            redirect_standard();
        break;
        case "edit":  //just delete and re-add
            //check to see if the room number has changed
            if ($orig_account != '' && $orig_account != $account) {
                $conflict_url = array();
                $usage_arr = framework_check_extension_usage($account);
                if (!empty($usage_arr)) {
                    $conflict_url = framework_display_extension_usage_alert($usage_arr);
                    break;
                } else {
                    conferences_del($orig_account);
                    $_REQUEST['extdisplay'] = $account;//redirect to the new ext
                    $old = conferences_getdest($orig_account);
                    $new = conferences_getdest($account);
                    framework_change_destination($old[0], $new[0]);
                }
            } else {
                conferences_del($account);
            }

            conferences_add($account,$_REQUEST['name'],$_REQUEST['userpin'],$_REQUEST['adminpin'],$_REQUEST['options'],$_REQUEST['joinmsg_id'],$music,$users);
            needreload();
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
            $_SESSION['msgtype']='success';
            redirect_standard('extdisplay');
        break;
    }
}

//Check to see if conference application is only confbridge
global $amp_conf;
global $astman;
if (!isset($astver)) {
    $engineinfo = engine_getinfo();
    $astver =  $engineinfo['version'];
}

//get meetme rooms
//this function needs to be available to other modules (those that use goto destinations)
//therefore we put it in globalfunctions.php
$meetmes = conferences_list();

drawListMenu($meetmes, $type, $display, $extdisplay);

?>
<div class='content'>
<?php
    if ($extdisplay != ""){
        //get details for this meetme
        $thisMeetme = conferences_get($extdisplay);
        $options     = $thisMeetme['options'];
        $userpin     = $thisMeetme['userpin'];
        $adminpin    = $thisMeetme['adminpin'];
        $description = $thisMeetme['description'];
        $joinmsg_id  = $thisMeetme['joinmsg_id'];
        $music       = $thisMeetme['music'];
        $users       = $thisMeetme['users'];
    } else {
        $options     = "";
        $userpin     = "";
        $adminpin    = "";
        $description = "";
        $joinmsg_id  = "";
        $music       = "";
        $users       = "0";
    }

?>

<?php if ($extdisplay != '') { ?>
    <h2><?php echo _("Edit Conference").": ". $extdisplay; ?></h2>
<?php } else { ?>
    <h2><?php echo _("Add Conference"); ?></h2>
<?php } ?>

<?php
if (!empty($conflict_url)) {
    echo ipbx_extension_conflict($conflict_url);
}

if ($extdisplay != '') {
    $usage_list = framework_display_destination_usage(conferences_getdest($extdisplay));

    if (!empty($usage_list)) {
        echo ipbx_usage_info($usage_list['text'],$usage_list['tooltip']);
    }
}

?>
    <form autocomplete="off" id="mainform" name="editMM" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkConf(this);">
    <input type="hidden" name="display" value="<?php echo $dispnum?>">
    <input type="hidden" name="action" value="<?php echo ($extdisplay != '' ? 'edit' : 'add') ?>">
    <input type="hidden" name="options" value="<?php echo $options; ?>">
<?php if ($extdisplay != "") { ?>
        <input type="hidden" name="orig_account" value="<?php echo $extdisplay; ?>">
<?php } ?>
    <table class='table is-borderless is-narrow'>
    <tr><td colspan="2"><h5><?php echo dgettext("amp","General Settings"); ?></h5></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Conference Number")?><span><?php echo _("Use this number to dial into the conference.")?></span></a></td>
        <td><input autofocus type="text" name="account" value="<?php echo $extdisplay ?>" tabindex="<?php echo ++$tabindex;?>" class='input w100'></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Conference Name")?><span><?php echo _("Give this conference a brief name to help you identify it.")?></span></a></td>
        <td><input type="text" name="name" value="<?php echo $description; ?>" tabindex="<?php echo ++$tabindex;?>" class='input w100'></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("User PIN")?><span><?php echo _("You can require callers to enter a password before they can enter this conference.<br><br>This setting is optional.<br><br>If either PIN is entered, the user will be prompted to enter a PIN.")?></span></a></td>
        <td><input size="8" type="text" name="userpin" value="<?php echo $userpin; ?>" tabindex="<?php echo ++$tabindex;?>" class='input w100'></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Admin PIN")?><span><?php echo _("Enter a PIN number for the admin user.<br><br>This setting is optional unless the 'leader wait' option is in use, then this PIN will identify the leader.")?></span></a></td>
        <td><input size="8" type="text" name="adminpin" value="<?php echo $adminpin; ?>" tabindex="<?php echo ++$tabindex;?>" class='input w100'></td>
    </tr>

    <tr><td colspan="2"><br><h5><?php echo _("Conference Options")?></h5></td></tr>
<?php if(function_exists('recordings_list')) { //only include if recordings is enabled?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Join Message")?><span><?php echo _("Message to be played to the caller before joining the conference.<br><br>To add additional recordings please use the \"System Recordings\" MENU to the left")?></span></a></td>
        <td>
            <select name="joinmsg_id" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
            <?php
                $tresults = recordings_list();
                echo '<option value="">'._("None")."</option>";
                if (isset($tresults[0])) {
                    foreach ($tresults as $tresult) {
                        echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $joinmsg_id ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
                    }
                }
            ?>
            </select>
        </td>
    </tr>
<?php }    else { ?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Join Message")?><span><?php echo _("Message to be played to the caller before joining the conference.<br><br>You must install and enable the \"Systems Recordings\" Module to edit this option")?></span></a></td>
        <td>
            <input type="hidden" name="joinmsg_id" value="<?php echo $joinmsg_id; ?>"><?php echo ($joinmsg_id != '' ? $joinmsg_id : 'None'); ?>
        </td>
    </tr>
<?php } ?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Leader Wait")?><span><?php echo _("Wait until the conference leader (admin user) arrives before starting the conference")?></span></a></td>
        <td>
        <?php
            if(strpos($options, 'w')!==false) { $optselect='w'; } else { $optselect=''; }
            echo ipbx_radio('opt#w',array(array('value'=>'w','text'=>dgettext('amp','Yes')),array('value'=>'','text'=>dgettext('amp','No'))),$optselect,false);
        ?>
        </td>
    </tr>

<?php
$engineinfo = engine_getinfo();
$astver =  $engineinfo['version'];
$ast_ge_10 = version_compare($astver, '10', 'ge');
if (version_compare($astver, '1.4', 'ge') && $amp_conf['ASTCONFAPP']=='app_meetme' || $ast_ge_10) {
?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Talker Optimization")?><span><?php echo _("Turns on talker optimization. With talker optimization, Asterisk treats talkers who
are not speaking as being muted, meaning that no encoding is done on transmission
and that received audio that is not registered as talking is omitted, causing no
buildup in background noise.")?></span></a></td>
        <td>
        <?php
            if(strpos($options, 'o')!==false) { $optselect='o'; } else { $optselect=''; }
            echo ipbx_radio('opt#o',array(array('value'=>'o','text'=>dgettext('amp','Yes')),array('value'=>'','text'=>dgettext('amp','No'))),$optselect,false);
        ?>
        </td>
    </tr>


    <tr>
        <td><a href="#" class="info"><?php echo _("Talker Detection")?><span><?php echo _("Sets talker detection. Asterisk will sends events on the Manager Interface identifying
the channel that is talking. The talker will also be identified on the output of
the meetme list CLI command.")?></span></a></td>
        <td>
        <?php
            if(strpos($options, 'T')!==false) { $optselect='T'; } else { $optselect=''; }
            echo ipbx_radio('opt#T',array(array('value'=>'T','text'=>dgettext('amp','Yes')),array('value'=>'','text'=>dgettext('amp','No'))),$optselect,false);
        ?>
        </td>
    </tr>
<?php
} else {//when using confbridge, hide option, but save it anyway
    echo '<input type="hidden" name="opt#T" value="' . (strpos($options, "T") !== false ? 'T' : '') . '"';
    echo '<input type="hidden" name="opt#o" value="' . (strpos($options, "o") !== false ? 'o' : '') . '"';
}?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Quiet Mode")?><span><?php echo _("Quiet mode (do not play enter/leave sounds)")?></span></a></td>
        <td>
        <?php
            if(strpos($options, 'q')!==false) { $optselect='q'; } else { $optselect=''; }
            echo ipbx_radio('opt#q',array(array('value'=>'q','text'=>dgettext('amp','Yes')),array('value'=>'','text'=>dgettext('amp','No'))),$optselect,false);
        ?>
        </td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("User Count")?><span><?php echo _("Announce user(s) count on joining conference")?></span></a></td>
        <td>
        <?php
            if(strpos($options, 'c')!==false) { $optselect='c'; } else { $optselect=''; }
            echo ipbx_radio('opt#c',array(array('value'=>'c','text'=>dgettext('amp','Yes')),array('value'=>'','text'=>dgettext('amp','No'))),$optselect,false);
        ?>
        </td>
    </tr>
    <?php
        if ($amp_conf['ASTCONFAPP']=='app_meetme' || $ast_ge_10) {
    ?>
    <tr>
        <td><a href="#" class="info"><?php echo _("User join/leave")?><span><?php echo _("Announce user join/leave")?></span></a></td>
        <td>
        <?php
            if(strpos($options, 'i')!==false) { $optselect='i'; } else { $optselect=''; }
            echo ipbx_radio('opt#i',array(array('value'=>'i','text'=>dgettext('amp','Yes')),array('value'=>'','text'=>dgettext('amp','No'))),$optselect,false);
        ?>
        </td>
    </tr>
    <?php } else {//when using confbridge, hide option, but save it anyway
        echo '<input type="hidden" name="opt#i" value="' . (strpos($options, "i") !== false ? 'i' : '') . '"';
    }?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Music on Hold")?><span><?php echo _("Enable Music On Hold when the conference has a single caller")?></span></a></td>
        <td>
        <?php
            if(strpos($options, 'M')!==false) { $optselect='M'; } else { $optselect=''; }
            echo ipbx_radio('opt#M',array(array('value'=>'M','text'=>dgettext('amp','Yes')),array('value'=>'','text'=>dgettext('amp','No'))),$optselect,false);
        ?>
        </td>
    </tr>

<?php if(function_exists('music_list')) { //only include if music module is enabled?>
                <tr>
                                <td><a href="#" class="info"><?php echo _("Music on Hold Class")?><span><?php echo _("Music (or Commercial) played to the caller while they wait in line for the conference to start. Choose \"inherit\" if you want the MoH class to be what is currently selected, such as by the inbound route.<br><br>  This music is defined in the \"Music on Hold\" to the left.")?></span></a></td>
                                <td>
                                    <select name="music" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
                                    <?php
                                        $tresults = music_list();
                                        array_unshift($tresults,'inherit');
                                        $default = (isset($music) ? $music : 'inherit');
                                        if (isset($tresults)) {
                                            foreach ($tresults as $tresult) {
                                                $searchvalue="$tresult";
                                                if($tresult == 'inherit') {  
                                                    $ttext = _("inherit");
                                                } else if($tresult == 'default' ) {
                                                    $ttext = _("default");
                                                } else if($tresult == 'none' ) {
                                                    $ttext = _("none");
                                                } else {
                                                    $ttext = $tresult;
                                                }
                                                echo '<option value="'.$tresult.'" '.($searchvalue == $default ? 'SELECTED' : '').'>'.$ttext;
                                            }
                                        }
                                    ?>
                                    </select>
                                </td>
                </tr>
<?php } ?>

    <tr>
        <td><a href="#" class="info"><?php echo _("Allow Menu")?><span><?php echo _("Present Menu (user or admin) when '*' is received ('send' to menu)")?></span></a></td>
        <td>
        <?php
            if(strpos($options, 's')!==false) { $optselect='s'; } else { $optselect=''; }
            echo ipbx_radio('opt#s',array(array('value'=>'s','text'=>dgettext('amp','Yes')),array('value'=>'','text'=>dgettext('amp','No'))),$optselect,false);
        ?>
        </td>
    </tr>
    <?php
        if ($amp_conf['ASTCONFAPP'] == 'app_meetme' || $ast_ge_10) {
    ?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Record Conference")?><span><?php echo _("Record the conference call")?></span></a></td>
        <td>
        <?php
            if(strpos($options, 'r')!==false) { $optselect='r'; } else { $optselect=''; }
            echo ipbx_radio('opt#r',array(array('value'=>'r','text'=>dgettext('amp','Yes')),array('value'=>'','text'=>dgettext('amp','No'))),$optselect,false);
        ?>
        </td>
    </tr>
    <?php } else {//when using confbridge, hide option, but save it anyway
        echo '<input type="hidden" name="opt#r" value="' . (strpos($options, "r") !== false ? 'r' : '') . '"';
    }?>
    <?php //Begin Maximum Participants Code ?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Maximum Participants")?><span><?php echo _("Maximum Number of users allowed to join this conference.")?></span></a></td>
        <td>
          <select name="users" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
            <?php
            $default = (($users) ? $users : 0);
            echo '<option value="0" '.($i == $default ? 'SELECTED' : '').'>'._("No Limit").'</option>';
            for ($i=2; $i <= 20; $i++) {
              echo '<option value="'.$i.'" '.($i == $default ? 'SELECTED' : '').'>'.$i.'</option>';
            }
            ?>
          </select>
        </td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Mute on Join")?><span><?php echo _("Mute everyone when they initially join the conference. Please note that if you do not have 'Leader Wait' set to yes you must have 'Allow Menu' set to Yes to unmute yourself")?></span></a></td>
        <td>
        <?php
            if(strpos($options, 'm')!==false) { $optselect='m'; } else { $optselect=''; }
            echo ipbx_radio('opt#m',array(array('value'=>'m','text'=>dgettext('amp','Yes')),array('value'=>'','text'=>dgettext('amp','No'))),$optselect,false);
        ?>
        </td>
        </tr>

    </table>
<?php
    // implementation of module hook
    // object was initialized in config.php
    echo process_tabindex($module_hook->hookHtml,$tabindex);
?>
    </form>
<script>

function checkConf(theForm) {

    var msgInvalidConfNumb = "<?php echo _('Please enter a valid Conference Number'); ?>";
    var msgInvalidConfName = "<?php echo _('Please enter a valid Conference Name'); ?>";
    var msgNeedAdminPIN = "<?php echo _('You must set an admin PIN for the Conference Leader when selecting the leader wait option'); ?>";
    var msgInvalidMuteOnJoin = "<?php echo _('You must set Allow Menu to Yes when not using a Leader or Admin in your conference, otherwise you will be unable to unmute yourself'); ?>";

    defaultEmptyOK = false;
    if (!isInteger(theForm.account.value))
        return warnInvalid(theForm.account, msgInvalidConfNumb);

    <?php if (function_exists('module_get_field_size')) { ?>
        var sizeDisplayName = "<?php echo module_get_field_size('meetme', 'description', 50); ?>";
        if (!isCorrectLength(theForm.name.value, sizeDisplayName))
            return warnInvalid(theForm.name, "<?php echo _('The Conference Name provided is too long.'); ?>")
    <?php } ?>
    
    if (!isAlphanumeric(theForm.name.value))
        return warnInvalid(theForm.name, msgInvalidConfName);

    // update $options
    var theOptionsFld = theForm.options;
    theOptionsFld.value = "";
    for (var i = 0; i < theForm.elements.length; i++)
    {
        var theEle = theForm.elements[i];
        var theEleName = theEle.name;
        if (theEleName.indexOf("#") > 1) {
            var arr = theEleName.split("#");
            if (arr[0] == "opt") {
                if(theEle.checked==true) {
                    theOptionsFld.value += theEle.value;
                }
            }
        }
    }

    // not possible to have a 'leader' conference with no adminpin
    if (theForm.options.value.indexOf("w") > -1 && theForm.adminpin.value == "")
        return warnInvalid(theForm.adminpin, msgNeedAdminPIN);

    // should not have a conference with no 'leader', mute on join, and no allow menu, so let's complain
    if ($('[name=opt\\#m]').val() != '' && $('[name=adminpin]').val() == '' && !$('[name=opt\\#s]').val())
        return warnInvalid(theForm.options, msgInvalidMuteOnJoin);

    $.LoadingOverlay('show');
    return true;
}

<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->

<?php
echo form_action_bar($extdisplay);
?>
