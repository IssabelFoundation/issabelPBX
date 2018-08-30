<?php /* $Id: page.queues.php 1124 2006-03-13 21:39:16Z rcourtna $ */
//Copyright (C) 2004 Coalescent Systems Inc. (info@coalescentsystems.ca)
//

if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

//used for switch on config.php
$dispnum = 'queues';

isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';
//the extension we are currently displaying
isset($_REQUEST['extdisplay'])?$extdisplay=$_REQUEST['extdisplay']:$extdisplay='';
isset($_REQUEST['account'])?$account = $_REQUEST['account']:$account='';
isset($_REQUEST['name'])?$name = $_REQUEST['name']:$name='';
isset($_REQUEST['password'])?$password = $_REQUEST['password']:$password='';
isset($_REQUEST['agentannounce_id'])?$agentannounce_id = $_REQUEST['agentannounce_id']:$agentannounce_id='';
isset($_REQUEST['prefix'])?$prefix = $_REQUEST['prefix']:$prefix='';
isset($_REQUEST['alertinfo'])?$alertinfo = $_REQUEST['alertinfo']:$alertinfo='';
isset($_REQUEST['joinannounce_id'])?$joinannounce_id = $_REQUEST['joinannounce_id']:$joinannounce_id='';
$maxwait = isset($_REQUEST['maxwait'])?$_REQUEST['maxwait']:'';
$cwignore = isset($_REQUEST['cwignore'])?$_REQUEST['cwignore']:'0';
$queuewait = isset($_REQUEST['queuewait'])?$_REQUEST['queuewait']:'0';
$rtone = isset($_REQUEST['rtone'])?$_REQUEST['rtone']:'0';
$qregex = isset($_REQUEST['qregex'])?$_REQUEST['qregex']:'';
$weight = isset($_REQUEST['weight'])?$_REQUEST['weight']:'0';
$autofill = isset($_REQUEST['autofill'])?$_REQUEST['autofill']:'no';
$togglehint = isset($_REQUEST['togglehint'])?$_REQUEST['togglehint']:'0';
$dynmemberonly = isset($_REQUEST['dynmemberonly'])?$_REQUEST['dynmemberonly']:'no';
$use_queue_context = isset($_REQUEST['use_queue_context'])?$_REQUEST['use_queue_context']:'0';
$exten_context = "from-queue";
$qnoanswer = isset($_REQUEST['qnoanswer'])?$_REQUEST['qnoanswer']:'0';
$callconfirm = isset($_REQUEST['callconfirm'])?$_REQUEST['callconfirm']:'0';
$callconfirm_id = isset($_REQUEST['callconfirm_id'])?$_REQUEST['callconfirm_id']:'';
$monitor_type = isset($_REQUEST['monitor_type'])?$_REQUEST['monitor_type']:'';
$monitor_heard = isset($_REQUEST['monitor_heard'])?$_REQUEST['monitor_heard']:'0';
$monitor_spoken = isset($_REQUEST['monitor_spoken'])?$_REQUEST['monitor_spoken']:'0';
$answered_elsewhere = isset($_REQUEST['answered_elsewhere'])?$_REQUEST['answered_elsewhere']:'0';
$skip_joinannounce = isset($_REQUEST['skip_joinannounce'])?$_REQUEST['skip_joinannounce']:'';

//cron code
$cron_schedule = isset($_REQUEST['cron_schedule'])?$_REQUEST['cron_schedule']:'never';
$cron_minute = isset($_REQUEST['cron_minute'])?$_REQUEST['cron_minute']:array();
$cron_hour = isset($_REQUEST['cron_hour'])?$_REQUEST['cron_hour']:array();
$cron_dow = isset($_REQUEST['cron_dow'])?$_REQUEST['cron_dow']:array();
$cron_month = isset($_REQUEST['cron_month'])?$_REQUEST['cron_month']:array();
$cron_dom = isset($_REQUEST['cron_dom'])?$_REQUEST['cron_dom']:array();
$cron_random = isset($_REQUEST['cron_random'])?$_REQUEST['cron_random']:false;

$engineinfo = engine_getinfo();
$astver =  $engineinfo['version'];
$ast_ge_16 = version_compare($astver, '1.6', 'ge');
$ast_ge_162 = version_compare($astver, '1.6.2', 'ge');
$ast_ge_18 = version_compare($astver, '1.8', 'ge');
$ast_ge_11 = version_compare($astver, '11', 'ge');

if (isset($_REQUEST['goto0']) && isset($_REQUEST[$_REQUEST['goto0']."0"])) {
    $goto = $_REQUEST[$_REQUEST['goto0']."0"];
} else {
    $goto = '';
}
if (isset($_REQUEST['goto1']) && isset($_REQUEST[$_REQUEST['goto1']."1"])) {
        $gotocontinue = $_REQUEST[$_REQUEST['goto1']."1"];
} else {
        $gotocontinue = '';
}

if (isset($_REQUEST["members"])) {
    $members = explode("\n",$_REQUEST["members"]);

    if (!$members) {
        $members = null;
    }

    foreach (array_keys($members) as $key) {
        //trim it
        $members[$key] = trim($members[$key]);

        // check if an agent (starts with a or A)

        $exten_prefix = strtoupper(substr($members[$key],0,1));
        $this_member = preg_replace("/[^0-9#\,*]/", "", $members[$key]);
        switch ($exten_prefix) {
            case 'A':
                $exten_type = 'Agent';
                break;
            case 'S':
                $exten_type = 'SIP';
                break;
            case 'X':
                $exten_type = 'IAX2';
                break;
            case 'Z':
                $exten_type = 'ZAP';
                break;
            case 'D':
                $exten_type = 'DAHDI';
                break;
            default;
                $exten_type = 'Local';
        }

        $penalty_pos = strrpos($this_member, ",");
        if ( $penalty_pos === false ) {
            $penalty_val = 0;
        } else {
            $penalty_val = substr($this_member, $penalty_pos+1); // get penalty
            $this_member = substr($this_member,0,$penalty_pos); // clean up ext
            $this_member = preg_replace("/[^0-9#*]/", "", $this_member); //clean out other ,'s
            $penalty_val = preg_replace("/[^0-9*]/", "", $penalty_val); // get rid of #'s if there
            $penalty_val = ($penalty_val == "") ? 0 : $penalty_val;
        }

        // remove blanks // prefix with the channel
        if (empty($this_member))
            unset($members[$key]);
        else {
            switch($exten_type) {
                case 'Agent':
                    if(version_compare($astver, '13', 'ge')) {
                        $members[$key] = "Local/$this_member@agents,$penalty_val,Agent/$this_member,Agent:$this_member";
                        break;
                    }
                case 'SIP':
                case 'IAX2':
                case 'ZAP':
                case 'DAHDI':
                    $members[$key] = "$exten_type/$this_member,$penalty_val";
                    break;
                case 'Local':
                    $members[$key] = "$exten_type/$this_member@$exten_context/n,$penalty_val";
            }
        }
    }
    // check for duplicates, and re-sequence
    // $members = array_values(array_unique($members));
}

if (isset($_REQUEST["dynmembers"])) {
    $dynmembers=explode("\n",$_REQUEST["dynmembers"]);
    if (!$dynmembers) {
        $dynmembers = null;
    }
}


// do if we are submitting a form
if(isset($_REQUEST['action'])){
    //check if the extension is within range for this user
    if (isset($account) && !checkRange($account)){
        echo "<script>javascript:alert('"._("Warning! Extension")." $account "._("is not allowed for your account.")."');</script>";
    } else {

        //if submitting form, update database
        switch ($action) {
            case "add":
                $conflict_url = array();
                $usage_arr = framework_check_extension_usage($account);
                if (!empty($usage_arr)) {
                    $conflict_url = framework_display_extension_usage_alert($usage_arr);
                } else {
                    queues_add($account,$name,$password,$prefix,$goto,$gotocontinue,$agentannounce_id,$members,$joinannounce_id,$maxwait,$alertinfo,$cwignore,$qregex,$queuewait,$use_queue_context,$dynmembers,$dynmemberonly,$togglehint,$qnoanswer, $callconfirm, $callconfirm_id, $monitor_type, $monitor_heard, $monitor_spoken, $answered_elsewhere);
                    needreload();
          $_REQUEST['extdisplay'] = $account;
                    $this_dest = queues_getdest($account);
                    fwmsg::set_dest($this_dest[0]);
                    redirect_standard('extdisplay');
                }
            break;
            case "delete":
                queues_del($account);
                needreload();
                redirect_standard();
            break;
            case "edit":  //just delete and re-add
                queues_del($account);
                queues_add($account,$name,$password,$prefix,$goto,$gotocontinue,$agentannounce_id,$members,$joinannounce_id,$maxwait,$alertinfo,$cwignore,$qregex,$queuewait,$use_queue_context,$dynmembers,$dynmemberonly,$togglehint,$qnoanswer, $callconfirm, $callconfirm_id, $monitor_type, $monitor_heard, $monitor_spoken, $answered_elsewhere);
                needreload();
                redirect_standard('extdisplay');
            break;
        }
    }
}

//get unique queues
$queues = queues_list();

?>
<link type="text/css" src="config.php?display=queues&handler=file&module=queues&file=assets/css/queues.css"></link>
<div class="rnav"><ul>
    <li><a id="<?php echo ($extdisplay=='' ? 'current':'') ?>" href="config.php?display=<?php echo urlencode($dispnum)?>"><?php echo _("Add Queue")?></a></li>
<?php
if (isset($queues)) {
    foreach ($queues as $queue) {
        echo "<li><a id=\"".($extdisplay==$queue[0] ? 'current':'')."\" href=\"config.php?display=".urlencode($dispnum)."&extdisplay=".urlencode($queue[0])."\">{$queue[0]}:{$queue[1]}</a></li>";
    }
}
?>
</ul></div>
<?php
if ($action == 'delete') {
    echo '<br><h3>'._("Queue").' '.$account.' '._("deleted").'!</h3><br><br><br><br><br><br><br><br>';
} else {
    $member = array();
    //get members in this queue
    $thisQ = queues_get($extdisplay);
    //create variables
    extract($thisQ);

    //cron
    //We check for an array in views/cron.php so make it one
    $cron_vars = array('cron_schedule', 'cron_minute', 'cron_hour', 'cron_dow', 'cron_month', 'cron_dom', 'cron_random');
    foreach ($cron_vars as $value) {
            $cronVars[$value] = array($$value);
    }

  $mem_array = array();
  foreach ($member as $mem) {
    if (preg_match("/^(Local|Agent|SIP|DAHDI|ZAP|IAX2)\/([\d]+).*,([\d]+)(.*)$/",$mem,$matches)) {
      switch ($matches[1]) {
        case 'Agent':
          $exten_prefix = 'A';
          break;
        case 'SIP':
          $exten_prefix = 'S';
          break;
        case 'IAX2':
          $exten_prefix = 'X';
          break;
        case 'ZAP':
          $exten_prefix = 'Z';
          break;
        case 'DAHDI':
          $exten_prefix = 'D';
          break;
        case 'Local':
          if(preg_match("/@agents/",$mem)) {
              // Asterisk 13 pseudo agents
              $exten_prefix = 'A';
          } else {
              $exten_prefix = '';
          }
          break;
      }
      $mem_array[] = $exten_prefix.$matches[2].','.$matches[3];
    }
  }

    $delButton = "
                <form name=delete action=\"{$_SERVER['PHP_SELF']}\" method=POST>
                    <input type=\"hidden\" name=\"display\" value=\"{$dispnum}\">
                    <input type=\"hidden\" name=\"account\" value=\"{$extdisplay}\">
                    <input type=\"hidden\" name=\"action\" value=\"delete\">
                    <input type=submit value=\""._("Delete Queue")."\">
                </form>";
?>

<?php if (!empty($conflict_url)) {
          echo "<h5>"._("Conflicting Extensions")."</h5>";
          echo implode('<br .>',$conflict_url);
      }
?>
<?php if ($extdisplay != '') { ?>
    <h2><?php echo _("Queue:")." ". $extdisplay; ?></h2>
<?php } else { ?>
    <h2><?php echo _("Add Queue"); ?></h2>
<?php } ?>

<?php        if ($extdisplay != '') {
                    echo $delButton;
                    $usage_list = framework_display_destination_usage(queues_getdest($extdisplay));
                    if (!empty($usage_list)) {
?>
                        <a href="#" class="info"><?php echo $usage_list['text']?>:<span><?php echo $usage_list['tooltip']?></span></a>
<?php
                    }
                }
?>
    <form class="popover-form" autocomplete="off" name="editQ" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkQ(editQ);">
    <input type="hidden" name="display" value="<?php echo $dispnum?>">
    <input type="hidden" name="action" value="<?php echo (($extdisplay != '') ? 'edit' : 'add') ?>">
    <table>
    <tr><td colspan="2"><h5><?php echo ($extdisplay ? _("Edit Queue") : _("Add Queue")) ?><hr></h5></td></tr>
    <tr>
<?php        if ($extdisplay != ''){ ?>
        <input type="hidden" name="account" value="<?php echo $extdisplay; ?>">
<?php        } else { ?>
        <td><a href="#" class="info"><?php echo _("Queue Number:")?><span><?php echo _("Use this number to dial into the queue, or transfer callers to this number to put them into the queue.<br><br>Agents will dial this queue number plus * to log onto the queue, and this queue number plus ** to log out of the queue.<br><br>For example, if the queue number is 123:<br><br><b>123* = log in<br>123** = log out</b>")?></span></a></td>
        <td><input type="text" name="account" value="" tabindex="<?php echo ++$tabindex;?>"></td>
<?php        } ?>
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Queue Name:")?><span><?php echo _("Give this queue a brief name to help you identify it.")?></span></a></td>
        <td><input type="text" name="name" value="<?php echo (isset($name) ? $name : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>

<?php
    if ($amp_conf['GENERATE_LEGACY_QUEUE_CODES']) {
?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Queue Password:")?><span><?php echo _("You can require agents to enter a password before they can log in to this queue.<br><br>This setting is optional.") . '<br /><br />' . _("The password is only used when logging in with the legacy queueno* code. When using the toggle codes, you must use the Restrict Dynamic Agents option in conjunction with the Dynamic Members list to control access.")?></span></a></td>
        <td><input type="text" name="password" value="<?php echo (isset($password) ? $password : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
<?php
    }

  // show it if checked so they know:
  //
  if ($qnoanswer || !$amp_conf['QUEUES_HIDE_NOANSWER']) {
?>
    <tr>
    <td><a href="#" class="info"><?php echo _("Queue No Answer:")?><span><?php echo _("If checked, the queue will not answer the call. Under most circumstance you should always have the queue answering calls. If not, then it's possible that recordings and MoH will not be heard by the waiting callers since early media capabilities vary and are inconsistent. Some cases where it may be desired to not answer a call is when using Strict Join Empty queue policies where the caller will not be admitted to the queue unless there is a queue member immediately available to take the call.")?></span></a></td>
    <td>
      <input name="qnoanswer" type="checkbox" value="1" <?php echo (isset($qnoanswer) && $qnoanswer == '1' ? 'checked' : ''); ?>  tabindex="<?php echo ++$tabindex;?>"/>
    </td>
  </tr>
<?php
  }
?>

<?php if ($ast_ge_18 || $amp_conf['USEDEVSTATE']) { ?>
    <tr>
  <td><a href="#" class="info"><?php echo _("Generate Device Hints:")?><span><?php echo _("If checked, individual hints and dialplan will be generated for each SIP and IAX2 device that could be part of this queue. These are used in conjunction with programmable BLF phone buttons to log into and out of a queue and generate BLF status as to the current state. The format of the hints is<br /><br />*45ddd*qqq<br /><br />where *45 is the currently defined toggle feature code, ddd is the device number (typically the same as the extension number) and qqq is this queue's number.")?></span></a></td>
        <td>
            <input name="togglehint" type="checkbox" value="1" <?php echo (isset($togglehint) && $togglehint == '1' ? 'checked' : ''); ?>  tabindex="<?php echo ++$tabindex;?>"/>
        </td>
    </tr>
<?php } ?>

    <tr>
            <td><a href="#" class="info"><?php echo _("Call Confirm:")?><span><?php echo _("If checked, any queue member that is actually an outside telephone number, or any extensions Follow-Me or call forwarding that are pursued and leave the PBX will be forced into Call Confirmation mode where the member must acknowledge the call before it is answered and delivered..")?></span></a></td>
            <td>
                  <input name="callconfirm" type="checkbox" value="1" <?php echo (isset($callconfirm) && $callconfirm == '1' ? 'checked' : ''); ?>  tabindex="<?php echo ++$tabindex;?>"/>
            </td>
      </tr>
<?php
    if(function_exists('recordings_list')) { //only include if recordings is enabled ?>
        <tr>
                <td><a href="#" class="info"><?php echo _("Call Confirm Announce:")?><span><?php echo _("Announcement played to the Queue Memeber announcing the Queue call and requesting confirmation prior to answering. If set to default, the standard call confirmation default message will be played unless the member is reached through a Follow-Me and there is an alternate message provided in the Follow-Me. This message will override any other message specified..<br><br>To add additional recordings please use the \"System Recordings\" MENU.")?></span></a></td>
                <td>
                        <select name="callconfirm_id" tabindex="<?php echo ++$tabindex;?>">
                        <?php
                                $tresults = recordings_list();
                                $default = (isset($callconfirm_id) ? $callconfirm_id : '');
                                echo '<option value="None">'._("Default");
                                if (isset($tresults[0])) {
                                        foreach ($tresults as $tresult) {
                                                echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $default ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
                                        }
                                }
                        ?>
                        </select>
                </td>
        </tr>
<?php } else { ?>
        <tr>
                <td><a href="#" class="info"><?php echo _("Call Confirm Announcement:")?><span><?php echo _("Announcement played to anyone using an external follow-me to receive the queue call.<br><br>You must install and enable the \"Systems Recordings\" Module to edit this option")?></span></a></td>
                <td>
                        <?php
                                $default = (isset($callconfirm_id) ? $callconfirm_id : '');
                        ?>
                        <input type="hidden" name="callconfirm_id" value="<?php echo $default; ?>"><?php echo ($default != '' ? $default : ''); ?>
                </td>
        </tr>
<?php
}
?>

    <tr>
        <td><a href="#" class="info"><?php echo _("CID Name Prefix:")?><span><?php echo _("You can optionally prefix the CallerID name of callers to the queue. ie: If you prefix with \"Sales:\", a call from John Doe would display as \"Sales:John Doe\" on the extensions that ring.")?></span></a></td>
        <td><input size="4" type="text" name="prefix" value="<?php echo (isset($prefix) ? $prefix : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Wait Time Prefix:")?><span><?php echo _("When set to Yes, the CID Name will be prefixed with the total wait time in the queue so the answering agent is aware how long they have waited. It will be rounded to the nearest minute, in the form of Mnn: where nn is the number of minutes.").'<br />'._("If the call is subsequently transferred, the wait time will reflect the time since it first entered the queue or reset if the call is transferred to another queue with this feature set.")?></span></a></td>
        <td>
            <select name="queuewait" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (isset($queuewait) ? $queuewait : '0');
                $items = array('1'=>_("Yes"),'0'=>_("No"));
                foreach ($items as $item=>$val) {
                    echo '<option value="'.$item.'" '. ($default == $item ? 'SELECTED' : '').'>'.$val;
                }
            ?>
            </select>
        </td>
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Alert Info")?><span><?php echo _('ALERT_INFO can be used for distinctive ring with SIP devices.')?></span></a>:</td>
        <td><input type="text" name="alertinfo" size="30" value="<?php echo (isset($alertinfo)?$alertinfo:'') ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>

    <tr>
    <td valign="top"><a href="#" class="info"><?php echo _("Static Agents") ?>:<span><br><?php echo _("Static agents are extensions that are assumed to always be on the queue.  Static agents do not need to 'log in' to the queue, and cannot 'log out' of the queue.<br><br>List extensions to ring, one per line.<br><br>You can include an extension on a remote system, or an external number (Outbound Routing must contain a valid route for external numbers). You can put a \",\" after the agent followed by a penalty value, see Asterisk documentation concerning penalties.<br /><br /> An advanced mode has been added which allows you to prefix an agent number with S, X, Z, D or A. This will force the agent number to be dialed as an Asterisk device of type SIP, IAX2, ZAP, DAHDi or Agent respectively. This mode is for advanced users and can cause known issues in IssabelPBX as you are by-passing the normal dialplan. If your 'Agent Restrictions' are not set to 'Extension Only' you will have problems with subsequent transfers to voicemail and other issues may also exist. (Channel Agent is deprecated starting with Asterisk 1.4 and gone in 1.6+.)") ?><br><br></span></a></td>
        <td valign="top">
            <textarea id="members" cols="15" rows="<?php  $rows = count($mem_array)+1; echo (($rows < 5) ? 5 : (($rows > 20) ? 20 : $rows) ); ?>" name="members" tabindex="<?php echo ++$tabindex;?>"><?php echo implode("\n",$mem_array) ?></textarea>
        </td>
    </tr>

    <tr>
        <td>
        <a href=# class="info"><?php echo _("Extension Quick Pick")?>
            <span>
                <?php echo _("Choose an extension to append to the end of the static agents list above.")?>
            </span>
        </a>
        </td>
        <td>
            <select onChange="insertExten('');" id="insexten" tabindex="<?php echo ++$tabindex;?>">
                <option value=""><?php echo _("(pick extension)")?></option>
    <?php
                $results = core_users_list();
                foreach ($results as $result) {
                    echo "<option value='".$result[0]."'>".$result[0]." (".$result[1].")</option>\n";
                }
    ?>
            </select>
        </td>
    </tr>

    <tr>
        <td valign="top"><a href="#" class="info"><?php echo _('Dynamic Members') ?>:<span><br><?php echo _("Dynamic Members are extensions or callback numbers that can log in and out of the queue. When a member logs in to a queue, their penalty in the queue will be as specified here. Extensions included here will NOT automatically be logged in to the queue.") ?><br><br></span></a></td>
        <td valign="top">
            <textarea id="dynmembers" cols="15" rows="<?php  $rows = count($dynmembers)+1; echo (($rows < 5) ? 5 : (($rows > 20) ? 20 : $rows) ); ?>" name="dynmembers" tabindex="<?php echo ++$tabindex;?>"><?php echo $dynmembers; ?></textarea>
        </td>
    </tr>

    <tr>
        <td>
        <a href=# class="info"><?php echo _("Extension Quick Pick")?>
            <span>
                <?php echo _("Choose an extension to append to the end of the dynamic member list above.")?>
            </span>
        </a>
        </td>
        <td>
            <select onChange="insertExten('dyn');" id="dyninsexten" tabindex="<?php echo ++$tabindex;?>">
                <option value=""><?php echo _("(pick extension)")?></option>
    <?php
                $results = core_users_list();
                foreach ($results as $result) {
                    echo "<option value='".$result[0]."'>".$result[0]." (".$result[1].")</option>\n";
                }
    ?>
            </select>
        </td>
    </tr>

    <tr>
      <td><a href="#" class="info"><?php echo _("Restrict Dynamic Agents")?><span><?php echo _('Restrict dynamic queue member logins to only those listed in the Dynamic Members list above. When set to Yes, members not listed will be DENIED ACCESS to the queue.')?></span></a></td>
    <td><span class="radioset"><input type="radio" id="dynmemberonly_yes" name="dynmemberonly" value="yes" <?php echo ($dynmemberonly=='yes'?'checked':'');?>><label for="dynmemberonly_yes"><?php echo _('Yes')?></label><input type="radio" name="dynmemberonly" id="dynmemberonly_no" value="no" <?php echo ($dynmemberonly!='yes'?'checked':'');?>><label for="dynmemberonly_no"><?php echo _('No'); ?></label></span>
        </td>
    </tr>

    <tr>
    <td><a href="#" class="info"><?php echo _("Agent Restrictions")?><span><?php echo _("When set to 'Call as Dialed' the queue will call an extension just as if the queue were another user. Any Follow-Me or Call Forward states active on the extension will result in the queue call following these call paths. This behavior has been the standard queue behavior on past IssabelPBX versions. <br />When set to 'No Follow-Me or Call Forward', all agents that are extensions on the system will be limited to ringing their extensions only. Follow-Me and Call Forward settings will be ignored. Any other agent will be called as dialed. This behavior is similar to how extensions are dialed in ringgroups. <br />When set to 'Extensions Only' the queue will dial Extensions as described for 'No Follow-Me or Call Forward'. Any other number entered for an agent that is NOT a valid extension will be ignored. No error checking is provided when entering a static agent or when logging on as a dynamic agent, the call will simply be blocked when the queue tries to call it. For dynamic agents, see the 'Agent Regex Filter' to provide some validation.")?></span></a></td>
        <td>
            <select name="use_queue_context" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (isset($use_queue_context) ? $use_queue_context : '0');
                echo '<option value="0"'. ($default == '0' ? ' SELECTED' : '').'>'._("Call as Dialed")."\n";
                echo '<option value="1"'. ($default == '1' ? ' SELECTED' : '').'>'._("No Follow-Me or Call Forward")."\n";
                echo '<option value="2"'. ($default == '2' ? ' SELECTED' : '').'>'._("Extensions Only")."\n";
            ?>
            </select>
        </td>
    </tr>

    <tr><td colspan="2"><br><h5><?php echo _("General Queue Options")?><hr></h5></td></tr>

    <tr>
        <td>
            <a href="#" class="info"><?php echo _("Ring Strategy:")?>
                <span>
                    <b><?php echo _("ringall")?></b>:  <?php echo _("ring all available agents until one answers (default)")?><br>
<?php
        if (!$ast_ge_16) {
?>
                    <b><?php echo _("roundrobin")?></b>: <?php echo _("take turns ringing each available agent")?><br>
<?php
        }
?>
                    <b><?php echo _("leastrecent")?></b>: <?php echo _("ring agent which was least recently called by this queue")?><br>
                    <b><?php echo _("fewestcalls")?></b>: <?php echo _("ring the agent with fewest completed calls from this queue")?><br>
                    <b><?php echo _("random")?></b>: <?php echo _("ring random agent")?><br>
                    <b><?php echo _("rrmemory")?></b>: <?php echo _("round robin with memory, remember where we left off last ring pass")?><br>
                    <b><?php echo _("rrordered")?></b>: <?php echo _("same as rrmemory, except the queue member order from config file is preserved")?><br>
<?php
        if ($ast_ge_16) {
?>
                    <b><?php echo _("linear")?></b>: <?php echo _("rings agents in the order specified, for dynamic agents in the order they logged in")?><br>
                    <b><?php echo _("wrandom")?></b>: <?php echo _("random using the member's penalty as a weighting factor, see asterisk documentation for specifics")?><br>
<?php
        }
?>
                </span>
            </a>
        </td>

        <td>
            <select name="strategy" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (isset($strategy) ? $strategy : 'ringall');
                $items = array('ringall','roundrobin','leastrecent','fewestcalls','random','rrmemory','rrordered');
        if ($ast_ge_16) {
                  $items[] = 'linear';
                  $items[] = 'wrandom';
          unset($items[array_search('roundrobin',$items)]);
        }
                foreach ($items as $item) {
                    echo '<option value="'.$item.'" '.($default == $item ? 'SELECTED' : '').'>'._($item);
                }
            ?>
            </select>
        </td>
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Autofill:")?><span><?php echo _("Starting with Asterisk 1.4, if this is checked, and multiple agents are available, Asterisk will send one call to each waiting agent (depending on the ring strategy). Otherwise, it will hold all calls while it tries to find an agent for the top call in the queue making other calls wait. This was the behavior in Asterisk 1.2 and has no effect in 1.2. See Asterisk documentation for more details of this feature.")?></span></a></td>
        <td>
            <input name="autofill" type="checkbox" value="1" <?php echo (isset($autofill) && $autofill == 'yes' ? 'checked' : ''); ?>  tabindex="<?php echo ++$tabindex;?>"/>
        </td>
    </tr>

    <tr>
    <td><a href="#" class="info"><?php echo _("Skip Busy Agents:")?><span><?php echo _("When set to 'Yes' agents who are on an occupied phone will be skipped as if the line were returning busy. This means that Call Waiting or multi-line phones will not be presented with the call and in the various hunt style ring strategies, the next agent will be attempted. <br />When set to 'Yes + (ringinuse=no)' the queue configuration flag 'ringinuse=no' is set for this queue in addition to the phone's device status being monitored. This results in the queue tracking remote agents (agents who are a remote PSTN phone, called through Follow-Me, and other means) as well as PBX connected agents, so the queue will not attempt to send another call if they are already on a call from any queue. <br />When set to 'Queue calls only (ringinuse=no)' the queue configuration flag 'ringinuse=no' is set for this queue also but the device status of locally connected agents is not monitored. The behavior is to limit an agent belonging to one or more queues to a single queue call. If they are occupied from other calls, such as outbound calls they initiated, the queue will consider them available and ring them since the device state is not monitored with this option. <br /><br />WARNING: When using the settings that set the 'ringinuse=no' flag, there is a NEGATIVE side effect. An agent who transfers a queue call will remain unavailable by any queue until that call is terminated as the call still appears as 'inuse' to the queue UNLESS 'Agent Restrictions' is set to 'Extensions Only'.")?></span></a></td>
        <td>
            <select name="cwignore" tabindex="<?php echo ++$tabindex;?>">
<?php
                $default = (isset($cwignore) ? $cwignore : 'no');
                $items = array('0' => _("No"),
                               '1'=>_("Yes"),
                                             '2'=>_("Yes + (ringinuse=no)"),
                                             '3'=>_("Queue calls only (ringinuse=no)"),
                                         );
                foreach ($items as $item=>$val) {
                    echo '<option value="'.$item.'" '. ($default == $item ? 'SELECTED' : '').'>'.$val;
                }
?>
            </select>
        </td>
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Queue Weight")?>:<span><?php echo _("Gives queues a 'weight' option, to ensure calls waiting in a higher priority queue will deliver its calls first if there are agents common to both queues.")?></span></a></td>
        <td>
            <select name="weight" tabindex="<?php echo ++$tabindex;?>">
<?php
                $default = (isset($weight) ? $weight : 0);
                for ($i=0; $i <= 10; $i++) {
                    echo '<option value="'.$i.'" '.($i == $default ? 'SELECTED' : '').'>'.$i.'</option>';
                }
?>
            </select>
        </td>
    </tr>

<?php
if(function_exists('music_list')) { //only include if music module is enabled?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Music on Hold Class:")?><span><?php echo _("Music (MoH) played to the caller while they wait in line for an available agent. Choose \"inherit\" if you want the MoH class to be what is currently selected, such as by the inbound route. MoH Only will play music until the agent answers. Agent Ringing will play MoH until an agent's phone is presented with the call and is ringing. If they don't answer, MoH will return.  Ring Only makes callers hear a ringing tone instead of MoH ignoring any MoH Class selected as well as any configured periodic announcements. This music is defined in the \"Music on Hold\" Menu.")?></span></a></td>
        <td>
            <select name="music" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $tresults = music_list();
                array_unshift($tresults,'inherit');
                $default = (isset($music) ? $music : 'inherit');
                if (isset($tresults)) {
                    foreach ($tresults as $tresult) {
                        $searchvalue="$tresult";
                        $ttext = $tresult;
                        if($tresult == 'inherit') $ttext = _("inherit");
                        if($tresult == 'none') $ttext = _("none");
                        if($tresult == 'default') $ttext = _("default");
                        echo '<option value="'.$tresult.'" '.($searchvalue == $default ? 'SELECTED' : '').'>'.$ttext;
                    }
                }
            ?>
            </select>&nbsp;
        <span class="radioset">
            <input type="radio" id="rtone-no" name="rtone" value="0" <?php echo ($rtone=='0'?'checked':'');?>><label for="rtone-no"><?php echo _('MoH Only')?></label>
            <input type="radio" id="rtone-agent" name="rtone" value="2" <?php echo ($rtone=='2'?'checked':'');?>><label for="rtone-agent"><?php echo _('Agent Ringing')?></label>
            <input type="radio" id="rtone-yes" name="rtone" value="1" <?php echo ($rtone=='1'?'checked':'');?>><label for="rtone-yes"><?php echo _('Ring Only')?></label>
        </span>
        </td>
    </tr>
<?php } ?>
<?php
if(function_exists('recordings_list')) { //only include if recordings is enabled ?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Join Announcement:")?><span><?php echo _("Announcement played to callers prior to joining the queue. This can be skipped if there are agents ready to answer a call (meaning they still may be wrapping up from a previous call) or when they are free to answer the call right now. To add additional recordings please use the \"System Recordings\" MENU.")?></span></a></td>
        <td>
            <select name="joinannounce_id" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $tresults = recordings_list();
                $default = (isset($joinannounce_id) ? $joinannounce_id : '');
                echo '<option value="None">'._("None");
                if (isset($tresults[0])) {
                    foreach ($tresults as $tresult) {
                        echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $default ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
                    }
                }
            ?>
            </select>&nbsp;
            <span class="radioset">
                <input type="radio" id="skip_joinannounce-no" name="skip_joinannounce" value="" <?php echo ($skip_joinannounce==''?'checked':'');?>><label for="skip_joinannounce-no"><?php echo _('Always')?></label>
                <input type="radio" id="skip_joinannounce-free" name="skip_joinannounce" value="free" <?php echo ($skip_joinannounce=='free'?'checked':'');?>><label for="skip_joinannounce-free"><?php echo _('When No Free Agents')?></label>
                <input type="radio" id="skip_joinannounce-ready" name="skip_joinannounce" value="ready" <?php echo ($skip_joinannounce=='ready'?'checked':'');?>><label for="skip_joinannounce-ready"><?php echo _('When No Ready Agents')?></label>
            </span>
        </td>
    </tr>
<?php } else { ?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Join Announcement:")?><span><?php echo _("Announcement played to callers once prior to joining the queue.<br><br>You must install and enable the \"Systems Recordings\" Module to edit this option")?></span></a></td>
        <td>
            <?php
                $default = (isset($joinannounce_id) ? $joinannounce_id : '');
            ?>
            <input type="hidden" name="joinannounce_id" value="<?php echo $default; ?>"><?php echo ($default != '' ? $default : ''); ?>
        </td>
    </tr>
<?php } ?>

    <tr>
        <td><a href="#" class="info"><?php echo _("Call Recording:")?><span><?php echo _("Incoming calls to agents can be recorded. (saved to /var/spool/asterisk/monitor)")?></span></a></td>
        <td>
            <select name="monitor-format" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (empty($thisQ['monitor-format']) ? "no" : $thisQ['monitor-format']);
                echo '<option value="wav49" '.($default == "wav49" ? 'SELECTED' : '').'>'._("wav49").'</option>';
                echo '<option value="wav" '.($default == "wav" ? 'SELECTED' : '').'>'._("wav").'</option>';
                echo '<option value="gsm" '.($default == "gsm" ? 'SELECTED' : '').'>'._("gsm").'</option>';
                echo '<option value="" '.($default == "no" ? 'SELECTED' : '').'>'._("No").'</option>';
            ?>
            </select>
        </td>
    </tr>

    <tr>
  <td><a href="#" class="info"><?php echo _("Recording Mode:")?><span><?php echo _("Choose to 'Include Hold Time' in the recording so it starts as soon as they enter the queue, or to defer recording until 'After Answered' and the call is bridged with a queue member.")?></span></a></td>
  <td>
    <select name="monitor_type" tabindex="<?php echo ++$tabindex;?>">
    <?php
    echo '<option value="" '.($monitor_type == "" ? 'SELECTED' : '').'>'._("Include Hold Time").'</option>';
    echo '<option value="b" '.($monitor_type == "b" ? 'SELECTED' : '').'>'._("After Answered").'</option>';
    ?>
    </select>
  </td>
  </tr>

    <tr>
  <td><a href="#" class="info"><?php echo _("Caller Volume Adjustment:")?><span><?php echo _("Adjust the recording volume of the caller.")?></span></a></td>
  <td>
    <select name="monitor_heard" tabindex="<?php echo ++$tabindex;?>">
    <?php
    for($i=-4;$i<=-1;$i++) {
      echo '<option value="'.$i.'" '.($monitor_heard == "$i" ? 'SELECTED' : '').'>'."$i".'</option>';
    }
    echo '<option value="0" '.(!$monitor_heard ? 'SELECTED' : '').'>'._("No Adjustment").'</option>';
    for($i=1;$i<=4;$i++) {
      echo '<option value="'.$i.'" '.($monitor_heard == "$i" ? 'SELECTED' : '').'>'."+$i".'</option>';
    }
    ?>
    </select>
  </td>
  </tr>

    <tr>
  <td><a href="#" class="info"><?php echo _("Agent Volume Adjustment:")?><span><?php echo _("Adjust the recording volume of the queue member (Agent).")?></span></a></td>
  <td>
    <select name="monitor_spoken" tabindex="<?php echo ++$tabindex;?>">
    <?php
    for($i=-4;$i<=-1;$i++) {
      echo '<option value="'.$i.'" '.($monitor_spoken == "$i" ? 'SELECTED' : '').'>'."$i".'</option>';
    }
    echo '<option value="0" '.(!$monitor_spoken ? 'SELECTED' : '').'>'._("No Adjustment").'</option>';
    for($i=1;$i<=4;$i++) {
      echo '<option value="'.$i.'" '.($monitor_spoken == "$i" ? 'SELECTED' : '').'>'."+$i".'</option>';
    }
    ?>
    </select>
  </td>
  </tr>

<?php
    if ($ast_ge_18) {
?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Mark calls answered elsewhere:")?><span><?php echo _("Enabling this option, all calls are marked as 'answered elsewhere' when cancelled. The effect is that missed queue calls are *not* shown on the phone (if the phone supports it)")?></span></a></td>
        <td>
            <input name="answered_elsewhere" type="checkbox" value="1" <?php echo (isset($answered_elsewhere) && $answered_elsewhere == 1 ? 'checked' : ''); ?>  tabindex="<?php echo ++$tabindex;?>"/>
        </td>
    </tr>
<?php
    }
?>

    <tr><td colspan="2"><br><h5><?php echo _("Timing & Agent Options")?><hr></h5></td></tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Max Wait Time:")?><span><?php echo _("The maximum number of seconds a caller can wait in a queue before being pulled out.  (0 for unlimited).")?></span></a></td>
        <td>
            <select name="maxwait" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (isset($maxwait) ? $maxwait : 0);
                for ($i=0; $i < 30; $i++) {
                    if ($i == 0)
                        echo '<option value="">'._("Unlimited").'</option>';
                    else
                        echo '<option value="'.$i.'"'.($i == $maxwait ? ' SELECTED' : '').'>'.$i.' '._("seconds").'</option>';
                }
                for ($i=30; $i < 60; $i+=5) {
                    echo '<option value="'.$i.'"'.($i == $maxwait ? ' SELECTED' : '').'>'.$i.' '._("seconds").'</option>';
                }
                for ($i=60; $i < 300; $i+=20) {
                    echo '<option value="'.$i.'"'.($i == $maxwait ? ' SELECTED' : '').'>'.queues_timeString($i,true).'</option>';
                }
                for ($i=300; $i < 1200; $i+=60) {
                    echo '<option value="'.$i.'"'.($i == $maxwait ? ' SELECTED' : '').'>'.queues_timeString($i,true).'</option>';
                }
                for ($i=1200; $i <= 7200; $i+=300) {
                    echo '<option value="'.$i.'"'.($i == $maxwait ? ' SELECTED' : '').'>'.queues_timeString($i,true).'</option>';
                }
            ?>
            </select>
        </td>
    </tr>
<?php
if ($ast_ge_16) {
?>
  <tr>
    <td><a href="#" class="info"><?php echo _("Max Wait Time Mode:")?><span><?php echo _("Asterisk timeoutpriority. In 'Strict' mode, when the 'Max Wait Time' of a caller is hit, they will be pulled out of the queue immediately. In 'Loose' mode, if a queue member is currently ringing with this call, then we will wait until the queue stops ringing this queue member or otherwise the call is rejected by the queue member before taking the caller out of the queue. This means that the 'Max Wait Time' could be as long as 'Max Wait Time' + 'Agent Timeout' combined.")?></span></a></td>
    <td>
      <select name="timeoutpriority" tabindex="<?php echo ++$tabindex;?>">
<?php
        $default = (isset($timeoutpriority) ? $timeoutpriority : "app");
        echo '<option value="app" '.($default == "app" ? 'SELECTED' : '').'>'._("Strict").'</option>';
        echo '<option value="conf" '.($default == "conf" ? 'SELECTED' : '').'>'._("Loose").'</option>';
?>
      </select>
    </td>
  </tr>
<?php
}
?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Agent Timeout:")?><span><?php echo _("The number of seconds an agent's phone can ring before we consider it a timeout. Unlimited or other timeout values may still be limited by system ringtime or individual extension defaults.")?></span></a></td>
        <td>
            <select name="timeout" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (isset($timeout) ? $timeout : 15);
                echo '<option value="0" '.(0 == $default ? 'SELECTED' : '').'>'._("Unlimited").'</option>';
                for ($i=1; $i <= 120; $i++) {
                    echo '<option value="'.$i.'" '.($i == $default ? ' SELECTED' : '').'>'.queues_timeString($i,true).'</option>';
                }
            ?>
            </select>
        </td>
    </tr>

    <tr>
    <td><a href="#" class="info"><?php echo _("Agent Timeout Restart:")?><span><?php echo _("If timeoutrestart is set to yes, then the time out for an agent to answer is reset if a BUSY or CONGESTION is received. This can be useful if agents are able to cancel a call with reject or similar.")?></span></a></td>
    <td>
      <select name="timeoutrestart" tabindex="<?php echo ++$tabindex;?>">
<?php
      $default = (isset($timeoutrestart) ? $timeoutrestart : "no");
      echo '<option value=yes '.($default == "yes" ? 'SELECTED' : '').'>'._("Yes").'</option>';
      echo '<option value=no '.($default == "no" ? 'SELECTED' : '').'>'._("No").'</option>';
?>
      </select>
    </td>
  </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Retry:")?><span><?php echo _("The number of seconds we wait before trying all the phones again. Choosing \"No Retry\" will exit the Queue and go to the fail-over destination as soon as the first attempted agent times-out, additional agents will not be attempted.")?></span></a></td>
        <td>
            <select name="retry" tabindex="<?php echo ++$tabindex;?>">
      <?php
                $default = (isset($retry) ? $retry : 5);
                echo '<option value="none" '.(($default == "none") ? 'SELECTED' : '').'>'._("No Retry").'</option>';
                for ($i=0; $i <= 60; $i++) {
                    echo '<option value="'.$i.'" '.(("$i" == "$default") ? 'SELECTED' : '').'>'.$i.' '._("seconds").'</option>';
                }
            ?>
            </select>
        </td>
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Wrap-Up-Time:")?><span><?php echo _("After a successful call, how many seconds to wait before sending a potentially free agent another call (default is 0, or no delay) If using Asterisk 1.6+, you can also set the 'Honor Wrapup Time Across Queues' setting (Asterisk: shared_lastcall) on the Advanced Settings page so that this is honored across queues for members logged on to multiple queues.")?></span></a></td>
        <td>
            <select name="wrapuptime" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (isset($wrapuptime) ? $wrapuptime : 0);
                for ($i=0; $i < 60; $i++) {
                    echo '<option value="'.$i.'" '.($i == $default ? 'SELECTED' : '').'>'.$i.' '._("seconds").'</option>';
                }
                for ($i=60; $i <= 3600; $i+=30) {
                    echo '<option value="'.$i.'" '.($i == $default ? ' SELECTED' : '').'>'.queues_timeString($i,true).'</option>';
                }
            ?>
            </select>
        </td>
    </tr>

    <tr>
    <td><a href="#" class="info"><?php echo _("Member Delay:")?><span><?php echo _("If you wish to have a delay before the member is connected to the caller (or before the member hears any announcement messages), set this to the number of seconds to delay.")?></span></a></td>
    <td>
      <select name="memberdelay" tabindex="<?php echo ++$tabindex;?>">
<?php
      $default = (isset($memberdelay) ? $memberdelay : 0);
      for ($i=0; $i <= 60; $i++) {
        echo '<option value="'.$i.'" '.($i == $default ? 'SELECTED' : '').'>'.$i.' '._("seconds").'</option>';
      }
?>
      </select>
    </td>
  </tr>

<?php if(function_exists('recordings_list')) { //only include if recordings is enabled?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Agent Announcement:")?><span><?php echo _("Announcement played to the Agent prior to bridging in the caller <br><br> Example: \"the Following call is from the Sales Queue\" or \"This call is from the Technical Support Queue\".<br><br>To add additional recordings please use the \"System Recordings\" MENU. Compound recordings composed of 2 or more sound files are not displayed as options since this feature can not accept such recordings.")?></span></a></td>
        <td>
            <select name="agentannounce_id" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $tresults = recordings_list(false);
                $default = (isset($agentannounce_id) ? $agentannounce_id : '');

                echo '<option value="">'._("None").'</option>';
                if (isset($tresults[0])) {
                    foreach ($tresults as $tresult) {
                        echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $default ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
                    }
                }
            ?>
            </select>
        </td>
    </tr>
<?php } else { ?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Agent Announcement:")?><span><?php echo _("Announcement played to the Agent prior to bridging in the caller <br><br> Example: \"the Following call is from the Sales Queue\" or \"This call is from the Technical Support Queue\".<br><br>You must install and enable the \"Systems Recordings\" Module to edit this option")?></span></a></td>
        <td>
            <?php
                $default = (isset($agentannounce_id) ? $agentannounce_id : '');
            ?>
            <input type="hidden" name="agentannounce_id" value="<?php echo $default; ?>"><?php echo ($default != '' ? $default : ''); ?>
        </td>
    </tr>
<?php } ?>

    <tr>
        <td><a href="#" class="info"><?php echo _("Report Hold Time:")?><span><?php echo _("If you wish to report the caller's hold time to the member before they are connected to the caller, set this to yes.")?></span></a></td>
        <td>
            <select name="reportholdtime" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (isset($reportholdtime) ? $reportholdtime : 'no');
                $items = array('yes'=>_("Yes"),'no'=>_("No"));
                foreach ($items as $item=>$val) {
                    echo '<option value="'.$item.'" '. ($default == $item ? 'SELECTED' : '').'>'.$val;
                }
            ?>
            </select>
        </td>
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Auto Pause:")?><span><?php echo _("Auto Pause an agent in this queue (or all queues they are a member of) if they don't answer a call. Specific behavior can be modified by the Auto Pause Delay as well as Auto Pause Busy/Unavailable settings if supported on this version of Asterisk.")?></span></a></td>
        <td>
            <select name="autopause" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (isset($autopause) ? $autopause : 'no');
                $items = array('yes'=>_("Yes in this queue only"),'all'=>_('Yes in all queues'),'no'=>_("No"));
                foreach ($items as $item=>$val) {
                    echo '<option value="'.$item.'" '. ($default == $item ? 'SELECTED' : '').'>'.$val;
                }
            ?>
            </select>
        </td>
    </tr>
<?php
                if ($ast_ge_11) {
?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Auto Pause on Busy:")?><span><?php echo _("When set to Yes agents devices that report busy upon a call attempt will be considered as a missed call and auto paused immediately or after the auto pause delay if configured")?></span></a></td>
        <td>
            <select name="autopausebusy" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (isset($autopausebusy) ? $autopausebusy : 'no');
                $items = array('yes'=>_("Yes"),'no'=>_("No"));
                foreach ($items as $item=>$val) {
                    echo '<option value="'.$item.'" '. ($default == $item ? 'SELECTED' : '').'>'.$val;
                }
            ?>
            </select>
        </td>
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Auto Pause on Unavailable:")?><span><?php echo _("When set to Yes agents devices that report congestion upon a call attempt will be considered as a missed call and auto paused immediately or after the auto pause delay if configured")?></span></a></td>
        <td>
            <select name="autopauseunavail" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (isset($autopauseunavail) ? $autopauseunavail : 'no');
                $items = array('yes'=>_("Yes"),'no'=>_("No"));
                foreach ($items as $item=>$val) {
                    echo '<option value="'.$item.'" '. ($default == $item ? 'SELECTED' : '').'>'.$val;
                }
            ?>
            </select>
        </td>
    </tr>
<?php
                }
?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Auto Pause Delay:")?><span><?php echo _("This setting will delay the auto pause of an agent by auto pause delay seconds from when it last took a call. For example, if this were set to 120 seconds, and a new call is presented to the agent 90 seconds after they last took a call, they will not be auto paused if they don't answer the call. If presented with a call 120 seconds or later after answering the last call, they will then be auto paused. If they have taken no calls, this will have no affect.")?></span></a></td>
        <td>
            <input type="number" name="autopausedelay" size="8" min="0" max="3600" value="<?php echo (isset($autopausedelay)?$autopausedelay:'0') ?>" tabindex="<?php echo ++$tabindex;?>">
        </td>
    </tr>

    <tr><td colspan="2"><br><h5><?php echo _("Capacity Options")?><hr></h5></td></tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Max Callers:")?><span><?php echo _("Maximum number of people waiting in the queue (0 for unlimited)")?></span></a></td>
        <td>
            <select name="maxlen" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (isset($maxlen) ? $maxlen : 0);
                echo '<option value="0" '.(!$default ? 'SELECTED' : '').'>'._("No Max").'</option>';
                for ($i=0; $i <= 50; $i++) {
                    echo '<option value="'.$i.'" '.($i == $default ? 'SELECTED' : '').'>'.$i.'</option>';
                }
            ?>
            </select>
        </td>
    </tr>

<?php
        $tt = _("Determines if new callers will be admitted to the Queue, if not, the failover destination will be immediately pursued. The options include:");
        $tt .= '<ul>';
        $tt .= '<li><b>'._("Yes").'</b> '._("Always allows the caller to join the Queue.").'</li>';
        $tt .= '<li><b>'._("Strict").'</b> '._("Same as Yes but more strict.  Simply speaking, if no agent could answer the phone then don't admit them. If agents are inuse or ringing someone else, caller will still be admitted.").'</li>';
        if ($ast_ge_162) {
          $tt .= '<li><b>'._("Ultra Strict").'</b> '._("Same as Strict plus a queue member must be able to answer the phone 'now' to let them in. Simply speaking, any 'available' agents that could answer but are currently on the phone or ringing on behalf of another caller will be considered unavailable.").'</li>';
        }
        $tt .= '<li><b>'._("No").'</b> '._("Callers will not be admitted if all agents are paused, show an invalid state for their device, or have penalty values less then QUEUE_MAX_PENALTY (not currently set in IssabelPBX dialplan).").'</li>';
        if ($ast_ge_16) {
          $tt .= '<li><b>'._("Loose").'</b> '._("Same as No except Callers will be admitted if their are paused agents who could become available.").'</li>';
        }
        $tt .= '</ul>';
?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Join Empty:")?><span><?php echo $tt?></span></a></td>
        <td>
            <select name="joinempty" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (isset($joinempty) ? $joinempty : 'yes');

        $items['yes'] = _("Yes");
        $items['strict'] = _("Strict");
        if ($ast_ge_162) {
          $items['penalty,paused,invalid,unavailable,inuse,ringing'] = _("Ultra Strict");
        }
        $items['no'] = _("No");
        if ($ast_ge_16) {
          $items['loose'] = _("Loose");
        }

                foreach ($items as $item=>$val) {
                    echo '<option value="'.$item.'" '. ($default == $item ? 'SELECTED' : '').'>'.$val;
                }
            ?>
            </select>
        </td>
    </tr>

<?php
        $tt = _("Determines if callers should be exited prematurely from the queue in situations where it appears no one is currently available to take the call. The options include:");
        $tt .= '<ul>';
        $tt .= '<li><b>'._("Yes").'</b> '._("Callers will exit if all agents are paused, show an invalid state for their device or have penalty values less then QUEUE_MAX_PENALTY (not currently set in IssabelPBX dialplan)..").'</li>';
        $tt .= '<li><b>'._("Strict").'</b> '._("Same as Yes but more strict.  Simply speaking, if no agent could answer the phone then have them leave the queue. If agents are inuse or ringing someone else, caller will still be held.").'</li>';
        if ($ast_ge_162) {
          $tt .= '<li><b>'._("Ultra Strict").'</b> '._("Same as Strict plus a queue member must be able to answer the phone 'now' to let them remain. Simply speaking, any 'available' agents that could answer but are currently on the phone or ringing on behalf of another caller will be considered unavailable.").'</li>';
        }
        if ($ast_ge_16) {
          $tt .= '<li><b>'._("Loose").'</b> '._("Same as Yes except Callers will remain in the Queue if their are paused agents who could become available.").'</li>';
        }
        $tt .= '<li><b>'._("No").'</b> '._("Never have a caller leave the Queue until the Max Wait Time has expired.").'</li>';
        $tt .= '</ul>';
?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Leave Empty:")?><span><?php echo $tt?></span></a></td>
        <td>
            <select name="leavewhenempty" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (isset($leavewhenempty) ? $leavewhenempty : 'no');

        $items['yes'] = _("Yes");
        $items['strict'] = _("Strict");
        if ($ast_ge_162) {
          $items['penalty,paused,invalid,unavailable,inuse,ringing'] = _("Ultra Strict");
        }
        if ($ast_ge_16) {
          $items['loose'] = _("Loose");
        }
        $items['no'] = _("No");

                foreach ($items as $item=>$val) {
                    echo '<option value="'.$item.'" '. ($default == $item ? 'SELECTED' : '').'>'.$val;
                }
            ?>
            </select>
        </td>
    </tr>
<?php
if ($ast_ge_16) {
?>
    <tr>
    <td><a href="#" class="info"><?php echo _("Penalty Members Limit:")?><span><?php echo _("Asterisk: penaltymemberslimit. A limit can be set to disregard penalty settings, allowing all members to be tried, when the queue has too few members.  No penalty will be weighed in if there are only X or fewer queue members.")?></span></a></td>
    <td>
      <select name="penaltymemberslimit" tabindex="<?php echo ++$tabindex;?>">
<?php
      $default = (isset($penaltymemberslimit) ? $penaltymemberslimit : 0);
      echo '<option value="0" '.(!$default ? 'SELECTED' : '').'>'._("Honor Penalties").'</option>';
      for ($i=1; $i <= 20; $i++) {
        echo '<option value="'.$i.'" '.($i == $default ? 'SELECTED' : '').'>'.$i.'</option>';
      }
?>
      </select>
    </td>
  </tr>
<?php
}
?>

    <tr><td colspan="2"><br><h5><?php echo _("Caller Position Announcements")?><hr></h5></td></tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Frequency:")?><span><?php echo _("How often to announce queue position and estimated holdtime (0 to Disable Announcements).")?></span></a></td>
        <td>
            <select name="announcefreq" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (isset($thisQ['announce-frequency']) ? $thisQ['announce-frequency'] : 0);
                for ($i=0; $i <= 1200; $i+=15) {
                    echo '<option value="'.$i.'" '.($i == $default ? 'SELECTED' : '').'>'.queues_timeString($i,true).'</option>';
                }
            ?>
            </select>
        </td>
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Announce Position:")?><span><?php echo _("Announce position of caller in the queue?")?></span></a></td>
        <td>
            <select name="announceposition" tabindex="<?php echo ++$tabindex;?>">
            <?php //setting to "no" will override sounds queue-youarenext, queue-thereare, queue-callswaitingÊ
                $default = (isset($thisQ['announce-position']) ? $thisQ['announce-position'] : "no");
                    echo '<option value=yes '.($default == "yes" ? 'SELECTED' : '').'>'._("Yes").'</option>';
                    echo '<option value=no '.($default == "no" ? 'SELECTED' : '').'>'._("No").'</option>';
            ?>
            </select>
        </td>
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Announce Hold Time:")?><span><?php echo _("Should we include estimated hold time in position announcements?  Either yes, no, or only once; hold time will not be announced if <1 minute")?> </span></a></td>
        <td>
            <select name="announceholdtime" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (isset($thisQ['announce-holdtime']) ? $thisQ['announce-holdtime'] : "no");
                echo '<option value=yes '.($default == "yes" ? 'SELECTED' : '').'>'._("Yes").'</option>';
                echo '<option value=no '.($default == "no" ? 'SELECTED' : '').'>'._("No").'</option>';
                echo '<option value=once '.($default == "once" ? 'SELECTED' : '').'>'._("Once").'</option>';
            ?>
            </select>
        </td>
    </tr>

    <tr><td colspan="2"><br><h5><?php echo _("Periodic Announcements")?><hr></h5></td></tr>

<?php if(function_exists('vqplus_callback_get') && function_exists('ivr_get_details')) {
    if (isset($callback) && $callback != 'none') {
        $breakouttype = 'callback';
    } else {
        $breakouttype = 'announcemenu';
    }
?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Break Out Type")?><span> <?php echo _("Whether this queue uses an IVR Break Out Menu or a Queue Callback.  Queue Callbacks can also be achieved through an IVR, but requires extra configuration.")?></span></a></td>
        <td>
            <select name="breakouttype" id="breakouttype" tabindex="<?php echo ++$tabindex;?>" onChange="breakoutDisable()">
            <option value="announcemenu" <?php echo ($breakouttype == 'announcemenu' ? 'SELECTED' : '') ?>><?php echo _("IVR Break Out Menu")?></option>
            <option value="callback" <?php echo ($breakouttype == 'callback' ? 'SELECTED' : '') ?>><?php echo _("Queue Callback")?></option>
            </select>
        </td>
    </tr>
<?php } else if(function_exists('ivr_get_details')) {
    $breakouttype = 'announcemenu';
    echo "<input type=\"hidden\" name=\"breakouttype\" value=\"announcemenu\">";
} else if(function_exists('vqplus_callback_get')) {
    $breakouttype = 'callback';
    echo "<input type=\"hidden\" name=\"breakouttype\" value=\"callback\">";
}
?>

<?php if(function_exists('ivr_get_details')) { //only include if IVR module is enabled ?>
    <tr>
        <td><a href="#" class="info"><?php echo _("IVR Break Out Menu:")?><span> <?php echo _("You can optionally present an existing IVR as a 'break out' menu.<br><br>This IVR must only contain single-digit 'dialed options'. The Recording set for the IVR will be played at intervals specified in 'Repeat Frequency', below.")?></span></a></td>
        <td>
            <select name="announcemenu" id="announcemenu" tabindex="<?php echo ++$tabindex;?>" <?php echo($breakouttype == 'announcemenu' ? '' : 'disabled')?>>
            <?php // setting this will set the context= option
            $default = (isset($announcemenu) ? $announcemenu : "none");

            echo '<option value="none" '.($default == "none" ? 'SELECTED' : '').'>'._("None").'</option>';

            //query for exisiting aa_N contexts
            //
            // If a previous bogus IVR was listed, we will leave it in with an error but will no longer show such IVRs as valid options.
            $unique_aas = ivr_get_details();

            $compound_recordings = false;
            $is_error = false;
            if (isset($unique_aas)) {
                foreach ($unique_aas as $unique_aa) {
                    $menu_id = $unique_aa['id'];
                    $menu_name = $unique_aa['name'] ? $unique_aa['name'] : 'IVR ' . $unique_aa['id'];

                    $unique_aa['announcement'] = recordings_get_file($unique_aa['announcement']);
                    if (strpos($unique_aa['announcement'],"&") === false) {
                        echo '<option value="'.$menu_id.'" '.($default == $menu_id ? 'SELECTED' : '').'>'.($menu_name ? $menu_name : _("Menu ID ").$menu_id)."</option>\n";
                    }
                    else {
                        $compound_recordings = true;
                        if ($menu_id == $default) {
                            echo '<option style="color:red" value="'.$menu_id.'" '.($default == $menu_id ? 'SELECTED' : '').'>'.($menu_name ? $menu_name : _("Menu ID ").$menu_id)." (**)</option>\n";
                            $is_error = true;
                        }
                    }
                }
            }
            ?>
            </select>
            <?php
            if ($is_error) {
            ?>
                <small><a style="color:red"  href="#" class="info"><?php echo ($is_error ? _("(**) ERRORS") : _("(**) Warning Potential Errors"))?>
                    <span>
                        <?php
                            if ($is_error) {
                                echo _("ERROR: You have selected an IVR that uses Announcements created from compound sound files. The Queue is not able to play these announcements. This IVRs recording will be truncated to use only the first sound file. You can correct the problem by selecting a different announcement for this IVR that is not from a compound sound file. The IVR itself can play such files, but the Queue subsystem can not").'<br />'._("Earlier versions of this module allowed such queues to be chosen, once changing this setting, it will no longer appear as an option");
                            }
                        ?>
                    </span></small>
                </a>
            <?php
            }
            ?>

        </td>
    </tr>

<?php } else {
    echo "<input type=\"hidden\" name=\"announcemenu\" value=\"none\">";
    }
?>
<?php if(function_exists('vqplus_callback_get')) { ?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Queue Callback")?><span> <?php echo _("Callback to use when caller presses 1.")?></span></a></td>
        <td>
            <select name="callback" id="callback" tabindex="<?php echo ++$tabindex;?>" <?php echo($breakouttype == 'callback' ? '' : 'disabled')?>>
                <option value="none" <?php echo ($callback == "" ? 'SELECTED' : '')?>><?php echo _("None")?></option>
                <?php
                $cbs = vqplus_callback_get();
                foreach ($cbs as $cb) {
                        echo '<option value="'.$cb['id'].'" '.($callback == $cb['id'] ? 'SELECTED' : '').'>'.$cb['name']."</option>";
                }
                ?>
            </select>
        </td>
    </tr>
<?php } else {
    echo "<input type=\"hidden\" name=\"callback\" value=\"none\">";
    }
?>

<?php if(function_exists('vqplus_callback_get') || function_exists('ivr_get_details')) { ?>
    <tr>
        <td><a href="#" class="info"><?php echo _("Repeat Frequency:")?><span><?php echo _("How often to announce a voice menu to the caller (0 to Disable Announcements).")?></span></a></td>
        <td>
            <select name="pannouncefreq" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (isset($thisQ['periodic-announce-frequency']) ? $thisQ['periodic-announce-frequency'] : 0);
                for ($i=0; $i <= 1200; $i+=15) {
                    echo '<option value="'.$i.'" '.($i == $default ? 'SELECTED' : '').'>'.queues_timeString($i,true).'</option>';
                }
            ?>
            </select>
        </td>
    </tr>
<?php } ?>

    <tr><td colspan="2"><br><h5><?php echo _("Events, Stats and Advanced")?><hr></h5></td></tr>

    <tr>
        <td><?php echo ipbx_label(_("Event When Called"), _("When this option is set to YES, the following manager events will be generated: AgentCalled, AgentDump, AgentConnect and AgentComplete."));?></td>
        <td>
            <?php
                $agentevents_true_label = form_label(_('Enabled'), 'agentevents_true');
                $agentevents_true = array(
                            'name'        => 'eventwhencalled',
                            'tabindex'    => ++$tabindex,
                            'id'        => 'agentevents_true',
                            'value'        => 'yes'

                );

                $agentevents_false_label = form_label(_('Disabled'), 'agentevents_false');
                $agentevents_false = array(
                            'name'        => 'eventwhencalled',
                            'tabindex'    => ++$tabindex,
                            'id'        => 'agentevents_false',
                            'value'        => 'no'

                );
                $eventwhencalled = isset($eventwhencalled) && $eventwhencalled
                                    ? $eventwhencalled
                                    : $amp_conf['QUEUES_EVENTS_WHEN_CALLED_DEFAULT'];
                if (in_array($eventwhencalled, array('yes', 1, true), true)) {
                    $agentevents_true['checked'] = true;
                } elseif (in_array($eventwhencalled, array('no', 0, false), true)) {
                    $agentevents_false['checked'] = true;
                }
                echo '<span class="radioset">'
                    . form_radio($agentevents_true) . $agentevents_true_label
                    . form_radio($agentevents_false) . $agentevents_false_label
                    . '</span>'
            ?>
        </td>
    </tr>

    <tr>
        <td><?php echo ipbx_label(_("Member Status Event"), _("When set to YES, the following manager event will be generated: QueueMemberStatus"));?></td>
        <td>
            <?php
                $memberevents_true_label = form_label(_('Enabled'), 'memberevents_true');
                $memberevents_true = array(
                            'name'        => 'eventmemberstatus',
                            'tabindex'    => ++$tabindex,
                            'id'        => 'memberevents_true',
                            'value'        => 'yes'

                );

                $memberevents_false_label = form_label(_('Disabled'), 'memberevents_false');
                $memberevents_false = array(
                            'name'        => 'eventmemberstatus',
                            'tabindex'    => ++$tabindex,
                            'id'        => 'memberevents_false',
                            'value'        => 'no'

                );
                $eventmemberstatus = isset($eventmemberstatus)
                                    ? $eventmemberstatus
                                    : $amp_conf['QUEUES_EVENTS_MEMEBER_STATUS_DEFAULT'];
                if (in_array($eventmemberstatus, array('yes', 1, true), true)) {
                    $memberevents_true['checked'] = true;
                } elseif (in_array($eventmemberstatus, array('no', 0, false), true)) {
                    $memberevents_false['checked'] = true;
                }
                echo '<span class="radioset">'
                    . form_radio($memberevents_true) . $memberevents_true_label
                    . form_radio($memberevents_false) . $memberevents_false_label
                    . '</span>'
            ?>
        </td>
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Service Level:")?><span><?php echo _("Used for service level statistics (calls answered within service level time frame)")?></span></a></td>
        <td>
            <select name="servicelevel" tabindex="<?php echo ++$tabindex;?>">
            <?php
                $default = (isset($servicelevel) ? $servicelevel : 60);
                for ($i=15; $i <= 300; $i+=15) {
                    echo '<option value="'.$i.'" '.($i == $default ? ' SELECTED' : '').'>'.queues_timeString($i,true).'</option>';
                }
            ?>
            </select>
        </td>
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Agent Regex Filter")?><span><?php echo _("Provides an optional regex expression that will be applied against the agent callback number. If the callback number does not pass the regex filter then it will be treated as invalid. This can be used to restrict agents to extensions within a range, not allow callbacks to include keys like *, or any other use that may be appropriate. An example input might be:<br />^([2-4][0-9]{3})$<br />This would restrict agents to extensions 2000-4999. Or <br />^([0-9]+)$ would allow any number of any length, but restrict the * key.<br />WARNING: make sure you understand what you are doing or otherwise leave this blank!")?></span></a></td>
        <td><input type="text" name="qregex" value="<?php echo (isset($qregex) ? $qregex : ''); ?>"></td>
    </tr>
<?php
    // implementation of module hook
    // object was initialized in config.php
    echo $module_hook->hookHtml;
?>

    <tr><td colspan="2"><br><h5><?php echo _("Fail Over Destination")?><hr></h5></td></tr>
    <?php
    echo drawselects($goto,0);
    ?>
    </table>
    
    <table>
    <tr><td colspan="2"><br><h5><?php echo _("Queue Continue Destination")?><hr></h5></td></tr>
        <?php
        echo drawselects($gotocontinue,1);
        ?>
        </table>
    

    <table>
        <tr><td colspan="2"><br><h5><?php echo _("Reset Queue Stats")?><hr></h5></td></tr>
        <tr><td colspan="2">
            <?php echo load_view(dirname(__FILE__) . '/views/cron.php', $cronVars); ?>
        </td></tr>
    </table>

    <table>
    <tr>
        <td colspan="2"><br><h6><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>"></h6></td>
    </tr>
    </table>

<script language="javascript">
<!--

function insertExten(type) {
    exten = document.getElementById(type+'insexten').value;

    grpList=document.getElementById(type+'members');
    if (grpList.value[ grpList.value.length - 1 ] == "\n") {
        grpList.value = grpList.value + exten + ',0';
    } else {
        grpList.value = grpList.value + '\n' + exten + ',0';
    }

    // reset element
    document.getElementById(type+'insexten').value = '';
}

function checkQ(theForm) {
    var bad = false;
    var msgWarnRegex = "<?php echo _("Using a Regex filter is fairly advanced, please confirm you know what you are doing or leave this blank"); ?>";

    var whichitem = 0;
    while (whichitem < theForm.goto0.length) {
        if (theForm.goto0[whichitem].checked) {
            theForm.goto0.value=theForm.goto0[whichitem].value;
        }
        whichitem++;
    }
    var whichitem = 0;
        while (whichitem < theForm.goto1.length) {
                if (theForm.goto1[whichitem].checked) {
                        theForm.goto1.value=theForm.goto1[whichitem].value;
                }
                whichitem++;
        }
    if (!isInteger(theForm.account.value)) {
        <?php echo "alert('"._("Queue Number must not be blank")."')"?>;
        bad=true;
    }

    defaultEmptyOK = false;

    <?php if (function_exists('module_get_field_size')) { ?>
        var sizeDisplayName = "<?php echo module_get_field_size('queues_config', 'descr', 35); ?>";
        if (!isCorrectLength(theForm.name.value, sizeDisplayName))
            return warnInvalid(theForm.name, "<?php echo _('The Queue Name provided is too long.'); ?>")
    <?php } ?>
    
    if (!isAlphanumeric(theForm.name.value)) {
        <?php echo "alert('"._("Queue name must not be blank and must contain only alpha-numeric characters")."')"?>;
        bad=true;
    }
    if (!isEmpty(theForm.qregex.value)) {
        if (!confirm(msgWarnRegex)) {
            bad=true;
        }
    }

    return !bad;
}

function breakoutDisable() {
    breakouttype = document.getElementById('breakouttype');

    for (var i = 0; i < breakouttype.length; i++) {
        /* Disable everything */
        document.getElementById(breakouttype.options[i].value).disabled = true;
    }

    /* Re-enable the active one */
    document.getElementById(breakouttype.value).disabled = false;
}

//-->
</script>

    </form>
<?php
} //end if action == delGRP
?>
