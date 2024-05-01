<?php /* $Id: page.findmefollow.php 1197 2006-03-19 17:59:02Z mheydon1973 $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
$tabindex = 0;

global $followme_exten;
$followme_exten = '';

$dispnum = 'findmefollow'; //used for switch on config.php

isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';
//the extension we are currently displaying
isset($_REQUEST['extdisplay'])?$extdisplay=$_REQUEST['extdisplay']:$extdisplay='';
isset($_REQUEST['account'])?$account = $_REQUEST['account']:$account='';
isset($_REQUEST['grptime'])?$grptime = $_REQUEST['grptime']:$grptime=$amp_conf['FOLLOWME_TIME'];
isset($_REQUEST['grppre'])?$grppre = $_REQUEST['grppre']:$grppre='';
isset($_REQUEST['strategy'])?$strategy = $_REQUEST['strategy']:$strategy=$amp_conf['FOLLOWME_RG_STRATEGY'];
isset($_REQUEST['annmsg_id'])?$annmsg_id = $_REQUEST['annmsg_id']:$annmsg_id='';
isset($_REQUEST['dring'])?$dring = $_REQUEST['dring']:$dring='';
isset($_REQUEST['needsconf'])?$needsconf = $_REQUEST['needsconf']:$needsconf='';
isset($_REQUEST['remotealert_id'])?$remotealert_id = $_REQUEST['remotealert_id']:$remotealert_id='';
isset($_REQUEST['toolate_id'])?$toolate_id = $_REQUEST['toolate_id']:$toolate_id='';
isset($_REQUEST['ringing'])?$ringing = $_REQUEST['ringing']:$ringing='';
isset($_REQUEST['pre_ring'])?$pre_ring = $_REQUEST['pre_ring']:$pre_ring=$amp_conf['FOLLOWME_PRERING'];
isset($_REQUEST['changecid'])?$changecid = $_REQUEST['changecid']:$changecid='default';
isset($_REQUEST['fixedcid'])?$fixedcid = $_REQUEST['fixedcid']:$fixedcid='';

if($action=='delete') $action='delGRP';

if (isset($_REQUEST['ddial'])) {
    $ddial =    $_REQUEST['ddial'];
}    else {
    $ddial = isset($_REQUEST['ddial_value']) ? $_REQUEST['ddial_value'] : ($amp_conf['FOLLOWME_DISABLED'] ? 'CHECKED' : '');
}

if (isset($_REQUEST['goto0']) && isset($_REQUEST[$_REQUEST['goto0']])) {
    $goto = $_REQUEST[$_REQUEST['goto0']];
} else {
    $goto = "ext-local,$extdisplay,dest";
}

if (isset($_REQUEST["grplist"])) {
    $grplist = explode("\n",$_REQUEST["grplist"]);

    if (!$grplist) {
        $grplist = null;
    }
    
    foreach (array_keys($grplist) as $key) {
        //trim it
        $grplist[$key] = trim($grplist[$key]);
        
        // remove invalid chars
        $grplist[$key] = preg_replace("/[^0-9#*+]/", "", $grplist[$key]);

        if ($grplist[$key] == ltrim($extdisplay,'GRP-').'#')
            $grplist[$key] = rtrim($grplist[$key],'#');
        
        // remove blanks
        if ($grplist[$key] == "") unset($grplist[$key]);
    }
    
    // check for duplicates, and re-sequence
    $grplist = array_values(array_unique($grplist));
}

// do if we are submitting a form
if(isset($_POST['action'])){
    //check if the extension is within range for this user
    if (isset($account) && !checkRange($account)){
        echo "<script>javascript:alert('". __("Warning! Extension")." ".$account." ".__("is not allowed for your account").".');</script>";
    } else {
        //add group
        if ($action == 'addGRP') {
            findmefollow_add($account,$strategy,$grptime,implode("-",$grplist),$goto,$grppre,$annmsg_id,$dring,$needsconf,$remotealert_id,$toolate_id,$ringing,$pre_ring,$ddial,$changecid,$fixedcid);

            needreload();
            redirect_standard();
        }
        
        //del group
        if ($action == 'delGRP') {
            findmefollow_del($account);
            needreload();
            redirect_standard();
        }
        
        //edit group - just delete and then re-add the extension
        if ($action == 'edtGRP') {
            findmefollow_del($account);    
            findmefollow_add($account,$strategy,$grptime,implode("-",$grplist),$goto,$grppre,$annmsg_id,$dring,$needsconf,$remotealert_id,$toolate_id,$ringing,$pre_ring,$ddial,$changecid,$fixedcid);

            needreload();
            redirect_standard('extdisplay');
        }
    }
}


$gresults    = findmefollow_allusers();
$set_users   = findmefollow_list();
$rnaventries = array();
if (isset($gresults)) {
    foreach($gresults as $gresult) {
        $defined = is_array($set_users) ? (in_array($gresult[0], $set_users) ? __("(edit)") : __("(add)")) : __("(add)");
        $rnaventries[] = array('GRP-'.$gresult[0],$gresult[1].' '.$defined,$gresult[0],'');
    }
}
$disable_add_button=true;
drawListMenu($rnaventries, $type, $display, $extdisplay, '', $disable_add_button);
?>


<!--div class="rnav"><ul>
<?php 
//get unique ring groups
$gresults = findmefollow_allusers();
$set_users = findmefollow_list();

if (isset($gresults)) {
    foreach ($gresults as $gresult) {
        $defined = is_array($set_users) ? (in_array($gresult[0], $set_users) ? __("(edit)") : __("(add)")) : __("(add)");
        echo "<li><a class=\"".($extdisplay=='GRP-'.$gresult[0] ? 'current':'')."\" href=\"config.php?display=".urlencode($dispnum)."&extdisplay=".urlencode("GRP-".$gresult[0])."\">".__("$gresult[1]")." <{$gresult[0]}> $defined  </a></li>";

    }
}
?>
</ul></div-->
<div class='content'>
<?php 

if ($extdisplay == "") {
    echo '<h2>'.__("Follow Me").'</h2><div class="mx-1">'.__('Choose a user/extension from the navigation menu').'</div>';
    echo "
    <script>
        setTimeout(function() { $('.rnav').addClass('animate__animated').addClass('animate__headShake');  },3000);
    </script>
    ";
} else {
    if ($extdisplay != "") {
        // We need to populate grplist with the existing extension list.
        $extdisplay = ltrim($extdisplay,'GRP-');
        $followme_exten = $extdisplay;

        $thisgrp = findmefollow_get($extdisplay, 1);
        $grpliststr = isset($thisgrp['grplist']) ? $thisgrp['grplist'] : '';
        $grplist = explode("-", $grpliststr);

        $strategy    = isset($thisgrp['strategy'])    ? $thisgrp['strategy']    : '';
        $grppre      = isset($thisgrp['grppre'])      ? $thisgrp['grppre']      : '';
        $grptime     = isset($thisgrp['grptime'])     ? $thisgrp['grptime']     : '';
        $annmsg_id   = isset($thisgrp['annmsg_id'])      ? $thisgrp['annmsg_id']      : '';
        $dring       = isset($thisgrp['dring'])       ? $thisgrp['dring']       : '';
        $remotealert_id = isset($thisgrp['remotealert_id']) ? $thisgrp['remotealert_id'] : '';
        $needsconf   = isset($thisgrp['needsconf'])   ? $thisgrp['needsconf']   : '';
        $toolate_id  = isset($thisgrp['toolate_id'])     ? $thisgrp['toolate_id']     : '';
        $ringing     = isset($thisgrp['ringing'])     ? $thisgrp['ringing']     : '';
        $pre_ring    = isset($thisgrp['pre_ring'])    ? $thisgrp['pre_ring']    : '';
        $ddial       = isset($thisgrp['ddial'])       ? $thisgrp['ddial']       : '';
        $changecid   = isset($thisgrp['changecid'])   ? $thisgrp['changecid']   : 'default';
        $fixedcid    = isset($thisgrp['fixedcid'])    ? $thisgrp['fixedcid']    : '';
        $goto = isset($thisgrp['postdest'])?$thisgrp['postdest']:((isset($thisgrp['voicemail']) && $thisgrp['voicemail'] != 'novm')?"ext-local,vmu$extdisplay,1":'');
        unset($grpliststr);
        unset($thisgrp);
        
        $delButton = "
            <form name=delete action=\"{$_SERVER['PHP_SELF']}\" method=POST>
                <input type=\"hidden\" name=\"display\" value=\"{$dispnum}\">
                <input type=\"hidden\" name=\"account\" value=\"{$extdisplay}\">
                <input type=\"hidden\" name=\"action\" value=\"delGRP\">
                <input type=submit value=\"".__("Delete Entries")."\">
            </form>";

        $title = is_array($set_users) ? (in_array($extdisplay, $set_users) ? __("Edit Follow Me").': '.$extdisplay : __("Add Follow Me"). ': '.$extdisplay ): __("Add Follow Me"). ': '.$extdisplay;

        echo "<h2>$title</h2>";


        // Copied straight out of old code,let's see if it works?
        //
        if (isset($amp_conf["AMPEXTENSIONS"]) && ($amp_conf["AMPEXTENSIONS"] == "deviceanduser")) {
            $editURL = $_SERVER['PHP_SELF'].'?display=users&extdisplay='.$extdisplay;
            $EXTorUSER = __("User");
        }
        else {
            $editURL = $_SERVER['PHP_SELF'].'?display=extensions&extdisplay='.$extdisplay;
            $EXTorUSER = __("Extension");
        }

        $label = '<span><img width="16" height="16" border="0" title="'.sprintf(__("Edit %s"),$EXTorUSER).'" alt="" src="images/user_edit.png"/>&nbsp;'.sprintf(__("Edit %s %s"),$EXTorUSER, $extdisplay).'</span>';
        $label = sprintf(__("Edit %s %s"),$EXTorUSER, $extdisplay);
        echo "<p><a href=".$editURL." class='button is-small is-rounded'>".$label."</a></p>";
    } 
    ?>
            <form name="editGRP" id="mainform" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkGRP(editGRP);">
            <input type="hidden" name="display" value="<?php echo $dispnum?>">
            <input type="hidden" name="action" value="<?php echo (($extdisplay != "") ? 'edtGRP' : 'addGRP'); ?>">
            <input type="hidden" name="account" value="<?php  echo $extdisplay; ?>">
            <table class='table is-borderless is-narrow'>
            <tr><td colspan="2"><h5><?php  echo _dgettext('amp','General Settings') ?></h5></td></tr>
            <tr>
                <td><a href="#" class="info"><?php echo __("Disable")?><span><?php echo __('By default (not checked) any call to this extension will go to this Follow-Me instead, including directory calls by name from IVRs. If checked, calls will go only to the extension.<BR>However, destinations that specify FollowMe will come here.<BR>Checking this box is often used in conjunction with VmX Locater, where you want a call to ring the extension, and then only if the caller chooses to find you do you want it to come here.')?></span></a></td>
                <td>
                <!--input type="checkbox" name="ddial" value="CHECKED" <?php echo $ddial ?>   tabindex="<?php echo ++$tabindex;?>"/-->
                <?php echo ipbx_radio('ddial',array(array('value'=>'CHECKED','text'=>_dgettext('amp','Yes')),array('value'=>'','text'=>_dgettext('amp','No'))),$ddial,false);?>
                <input type="hidden" name="ddial_value" value="<?php  echo $ddial; ?>">
                </td>
            </tr>

            <tr>
                <td><a href="#" class="info"><?php echo __("Initial Ring Time")?>
                <span><?php echo __("This is the number of seconds to ring the primary extension prior to proceeding to the follow-me list. The extension can also be included in the follow-me list. A 0 setting will bypass this.")?>
                </span></a>
                </td>
                <td>
                    <select name="pre_ring" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
                    <?php
                        $default = (isset($pre_ring) ? $pre_ring : 0);
                        for ($i=0; $i <= 60; $i++) {
                            echo '<option value="'.$i.'" '.($i == $default ? 'SELECTED' : '').'>'.$i.'</option>';
                        }
                    ?>
                    </select>
                </td>
            </tr>

            <tr>
                <td>
                <a href="#" class="info"><?php echo __("Ring Strategy")?>
                <span>
                    <b><?php echo __("ringallv2")?></b>:  <?php echo __("ring Extension for duration set in Initial Ring Time, and then, while continuing call to extension, ring Follow-Me List for duration set in Ring Time.")?><br>
                    <b><?php echo __("ringall")?></b>:  <?php echo __("ring Extension for duration set in Initial Ring Time, and then terminate call to Extension and ring Follow-Me List for duration set in Ring Time.")?><br>
                    <b><?php echo __("hunt")?></b>: <?php echo __("take turns ringing each available extension")?><br>
                    <b><?php echo __("memoryhunt")?></b>: <?php echo __("ring first extension in the list, then ring the 1st and 2nd extension, then ring 1st 2nd and 3rd extension in the list.... etc.")?><br>
                    <b><?php echo __("*-prim")?></b>:  <?php echo __("these modes act as described above. However, if the primary extension (first in list) is occupied, the other extensions will not be rung. If the primary is IssabelPBX DND, it won't be rung. If the primary is IssabelPBX CF unconditional, then all will be rung")?><br>
                    <b><?php echo __("firstavailable")?></b>:  <?php echo __("ring only the first available channel")?><br>
                    <b><?php echo __("firstnotonphone")?></b>:  <?php echo __("ring only the first channel which is not off hook - ignore CW")?><br>
                </span>
                </a>
                </td>
                <td>
                    <select name="strategy" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
                    <?php
                        $default = (isset($strategy) ? $strategy : 'ringall');
                                                $items = array('ringallv2','ringallv2-prim','ringall','ringall-prim','hunt','hunt-prim','memoryhunt','memoryhunt-prim','firstavailable','firstnotonphone');
                        foreach ($items as $item) {
                            echo '<option value="'.$item.'" '.($default == $item ? 'SELECTED' : '').'>'.__($item);
                        }
                    ?>        
                    </select>
                </td>
            </tr>

            <tr>
                <td>
                    <a href=# class="info"><?php echo __("Ring Time (max 60 sec)")?>
                        <span>
                            <?php echo __("Time in seconds that the phones will ring. For all hunt style ring strategies, this is the time for each iteration of phone(s) that are rung")?>
                        </span>
                    </a>
                </td>
                <td><input type="text" name="grptime" value="<?php  echo $grptime?$grptime:20 ?>" tabindex="<?php echo ++$tabindex;?>" class='input'></td>
            </tr>

            <tr>
                <td valign="top"><a href="#" class="info"><?php echo __("Follow-Me List")?><span><?php echo __("List extensions to ring, one per line, or use the Extension Quick Pick below.<br><br>You can include an extension on a remote system, or an external number by suffixing a number with a pound (#).  ex:  2448089# would dial 2448089 on the appropriate trunk (see Outbound Routing).")?><br><br></span></a></td>
                <td valign="top">
<?php
        $rows = count($grplist)+1; 
        if ($rows <= 2 && trim($grplist[0]) == "") {
            $grplist[0] = $extdisplay;
        }
?>
                    <textarea id="grplist" class="textarea" onkeyup="textAreaAdjust(this)" name="grplist" tabindex="<?php echo ++$tabindex;?>" style='width:100%;height:3em;'><?php echo implode("\n",$grplist);?></textarea>
                </td>
            </tr>

            <tr>
                <td>
                <a href=# class="info"><?php echo __("Extension Quick Pick")?>
                    <span>
                        <?php echo __("Choose an extension to append to the end of the extension list above.")?>
                    </span>
                </a>
                </td>
                <td>
                    <select onChange="insertExten();" id="insexten" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
                        <option value=""><?php echo __("(pick extension)")?></option>
    <?php
                        $results = core_users_list();
                        foreach ($results as $result) {
                            echo "<option value='".$result[0]."'>".$result[0]." (".$result[1].")</option>\n";
                        }
    ?>
                    </select>
                </td>
            </tr>

<?php if(function_exists('recordings_list')) { //only include if recordings is enabled?>
            <tr>
                <td><a href="#" class="info"><?php echo __("Announcement")?><span><?php echo __("Message to be played to the caller before dialing this group.<br><br>To add additional recordings please use the \"System Recordings\" MENU to the left")?></span></a></td>
                <td>
                    <select name="annmsg_id" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
                    <?php
                        $tresults = recordings_list();
                        $default = (isset($annmsg_id) ? $annmsg_id : '');
                        echo '<option value="">'.__("None");
                        if (isset($tresults)) {
                            foreach ($tresults as $tresult) {
                                echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $default ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
                            }
                        }
                    ?>        
                    </select>        
                </td>
            </tr>
<?php }    else { ?>
            <tr>
                <td><a href="#" class="info"><?php echo __("Announcement")?><span><?php echo __("Message to be played to the caller before dialing this group.<br><br>You must install and enable the \"Systems Recordings\" Module to edit this option")?></span></a></td>
                <td>
                    <?php
                        $default = (isset($annmsg_id) ? $annmsg_id : '');
                    ?>
                    <input type="hidden" name="annmsg_id" value="<?php echo $default; ?>"><?php echo ($default != '' ? $default : 'None'); ?>
                </td>
            </tr>

<?php } if (function_exists('music_list')) { ?>
            <tr>
                <td><a href="#" class="info"><?php echo __("Play Music On Hold?")?><span><?php echo __("If you select a Music on Hold class to play, instead of 'Ring', they will hear that instead of Ringing while they are waiting for someone to pick up.")?></span></a></td>
                <td>
                    <select name="ringing" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
                    <?php
                        $tresults = music_list();
                        $cur = (isset($ringing) ? $ringing : 'Ring');
                        echo '<option value="Ring">'.__("Ring")."</option>";
                        if (isset($tresults[0])) {
                            foreach ($tresults as $tresult) {
                                echo '<option value="'.$tresult.'"'.($tresult == $cur ? ' SELECTED' : '').'>'.$tresult."</option>\n";
                            }
                        }
                    ?>
                    </select>
                    </td>
                </tr>
<?php } ?>

            <tr>
                <td><a href="#" class="info"><?php echo __("CID Name Prefix")?><span><?php echo __('You can optionally prefix the Caller ID name when ringing extensions in this group. ie: If you prefix with "Sales:", a call from John Doe would display as "Sales:John Doe" on the extensions that ring.')?></span></a></td>
                <td><input type="text" name="grppre" value="<?php  echo $grppre ?>" tabindex="<?php echo ++$tabindex;?>" class='input'></td>
            </tr>

            <tr>
                <td><a href="#" class="info"><?php echo __("Alert Info")?><span><?php echo __('You can optionally include an Alert Info which can create distinctive rings on SIP phones.')?></span></a></td>
                <td><input type="text" name="dring" value="<?php  echo $dring ?>" tabindex="<?php echo ++$tabindex;?>" class='input'></td>
            </tr>

            <tr><td colspan="2"><h5><?php echo __("Call Confirmation Configuration") ?></h5></td></tr>

            <tr>
                <td><a href="#" class="info"><?php echo __("Confirm Calls")?><span><?php echo __('Enable this if you\'re calling external numbers that need confirmation - eg, a mobile phone may go to voicemail which will pick up the call. Enabling this requires the remote side push 1 on their phone before the call is put through. This feature only works with the ringall/ringall-prim  ring strategy')?></span></a></td>
                <td> 
                    <!--input type="checkbox" name="needsconf" value="CHECKED" <?php echo $needsconf ?>   tabindex="<?php echo ++$tabindex;?>"/-->
                    <?php echo ipbx_radio('needsconf',array(array('value'=>'CHECKED','text'=>_dgettext('amp','Yes')),array('value'=>'','text'=>_dgettext('amp','No'))),$needsconf,false);?>
                </td>
            </tr>

<?php if(function_exists('recordings_list')) { //only include if recordings is enabled?>
            <tr>
                <td><a href="#" class="info"><?php echo __("Remote Announce")?><span><?php echo __("Message to be played to the person RECEIVING the call, if 'Confirm Calls' is enabled.<br><br>To add additional recordings use the \"System Recordings\" MENU to the left")?></span></a></td>
                <td>
                    <select name="remotealert_id" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
                    <?php
                        $tresults = recordings_list();
                        $default = (isset($remotealert_id) ? $remotealert_id : '');
                        echo '<option value="">'.__("Default")."</option>";
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
                <td><a href="#" class="info"><?php echo __("Too-Late Announce")?><span><?php echo __("Message to be played to the person RECEIVING the call, if the call has already been accepted before they push 1.<br><br>To add additional recordings use the \"System Recordings\" MENU to the left")?></span></a></td>
                <td>
                <select name="toolate_id" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
                    <?php
                        $tresults = recordings_list();
                        $default = (isset($toolate_id) ? $toolate_id : '');
                        echo '<option value="">'.__("Default")."</option>";
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

            <tr><td colspan="2"><h5><?php echo __("Change External CID Configuration") ?></h5></td></tr>
            <tr>
                <td>
                <a href="#" class="info"><?php echo __("Mode")?>
                <span>
                    <b><?php echo __("Default")?></b>:  <?php echo __("Transmits the Callers CID if allowed by the trunk.")?><br>
                    <b><?php echo __("Fixed CID Value")?></b>:  <?php echo __("Always transmit the Fixed CID Value below.")?><br>
                    <b><?php echo __("Outside Calls Fixed CID Value")?></b>: <?php echo __("Transmit the Fixed CID Value below on calls that come in from outside only. Internal extension to extension calls will continue to operate in default mode.")?><br>
                    <b><?php echo __("Use Dialed Number")?></b>: <?php echo __("Transmit the number that was dialed as the CID for calls coming from outside. Internal extension to extension calls will continue to operate in default mode. There must be a DID on the inbound route for this. This will be BLOCKED on trunks that block foreign CallerID")?><br>
                    <b><?php echo __("Force Dialed Number")?></b>: <?php echo __("Transmit the number that was dialed as the CID for calls coming from outside. Internal extension to extension calls will continue to operate in default mode. There must be a DID on the inbound route for this. This WILL be transmitted on trunks that block foreign CallerID")?><br>
                </span>
                </a>
                </td>
                <td>
                    <select name="changecid" id="changecid" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
                    <?php
                        $default = (isset($changecid) ? $changecid : 'default');
                        echo '<option value="default" '.($default == 'default' ? 'SELECTED' : '').'>'.__("Default");
                        echo '<option value="fixed" '.($default == 'fixed' ? 'SELECTED' : '').'>'.__("Fixed CID Value");
                        echo '<option value="extern" '.($default == 'extern' ? 'SELECTED' : '').'>'.__("Outside Calls Fixed CID Value");
                        echo '<option value="did" '.($default == 'did' ? 'SELECTED' : '').'>'.__("Use Dialed Number");
                        echo '<option value="forcedid" '.($default == 'forcedid' ? 'SELECTED' : '').'>'.__("Force Dialed Number");
                        $fixedcid_disabled = ($default != 'fixed' && $default != 'extern') ? 'disabled = "disabled"':'';
                    ?>        
                    </select>
                </td>
            </tr>

            <tr>
                <td><a href="#" class="info"><?php echo __("Fixed CID Value")?><span><?php echo __('Fixed value to replace the CID with used with some of the modes above. Should be in a format of digits only with an option of E164 format using a leading "+".')?></span></a></td>
        <td><input type="text" name="fixedcid" id="fixedcid" value="<?php  echo $fixedcid ?>" tabindex="<?php echo ++$tabindex;?>" class='input' <?php echo $fixedcid_disabled ?>></td>
            </tr>
            
            <tr><td colspan="2"><br><h5><?php echo __("Destination if no answer")?></h5></td></tr>

<?php 
//draw goto selects
if (empty($goto)) {
    $goto = "ext-local,$extdisplay,dest";
}
echo drawselects($goto,0);
?>
            
            </table>
            </form>
<?php         
        } //end if action == delGRP
        
?>
<script>

$(function(){
    $("#changecid").on('change',function(){
        state = (this.value == "fixed" || this.value == "extern") ? "" : "disabled";
        if (state == "disabled") {
          $("#fixedcid").attr("disabled",state);
        } else {
          $("#fixedcid").removeAttr("disabled");
        }
    });
    if($('#grplist').length>0) {
        textAreaAdjust(document.getElementById('grplist'));
    }
});

function textAreaAdjust(element) {
  element.style.height = "1px";
  element.style.height = (5+element.scrollHeight)+"px";
}

function insertExten() {
    exten = document.getElementById('insexten').value;

    grpList=document.getElementById('grplist');
    if (grpList.value.length == 0 || grpList.value[ grpList.value.length - 1 ] == "\n") {
        grpList.value = grpList.value + exten;
    } else {
        grpList.value = grpList.value + '\n' + exten;
    }

    textAreaAdjust(document.getElementById('grplist'));
    // reset element
    document.getElementById('insexten').value = '';
}

function checkGRP(theForm) {
    var msgInvalidExtList      = "<?php echo __('Please enter an extension list.'); ?>";
    var msgInvalidTime         = "<?php echo __('Invalid time specified'); ?>";
    var msgInvalidGrpTimeRange = "<?php echo __('Time must be between 1 and 60 seconds'); ?>";
    var msgInvalidRingStrategy = "<?php echo __('Only ringall, ringallv2, hunt and the respective -prim versions are supported when confirmation is checked'); ?>";
    var msgInvalidCID          = "<?php echo __('Invalid CID Number. Must be in a format of digits only with an option of E164 format using a leading "+"'); ?>";

    // set up the Destination stuff
    setDestinations(theForm, 1);

    // form validation
    defaultEmptyOK = false;    
    if (isEmpty(theForm.grplist.value))
        return warnInvalid(theForm.grplist, msgInvalidExtList);

    if (!theForm.fixedcid.disabled) {
      fixedcid = $.trim(theForm.fixedcid.value);
      if (!fixedcid.match('^[+]{0,1}[0-9]+$')) {
          return warnInvalid(theForm.fixedcid, msgInvalidCID);
      }
    }

    if (!isInteger(theForm.grptime.value)) {
        return warnInvalid(theForm.grptime, msgInvalidTime);
    } else {
        var grptimeVal = theForm.grptime.value;
        if (grptimeVal < 1 || grptimeVal > 60) {
            return warnInvalid(theForm.grptime, msgInvalidGrpTimeRange);
        }
    }

    if (theForm.needsconf.checked && (theForm.strategy.value.substring(0,7) != "ringall" && theForm.strategy.value.substring(0,4) != "hunt")) {
        return warnInvalid(theForm.needsconf, msgInvalidRingStrategy);
    }

    defaultEmptyOK = true;

    if (!validateDestinations(theForm, 1, true)) {
        return false;
    }

    $.LoadingOverlay('show');
    return true;
}
<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
