<?php
/******************* Wakeup Calls Module  *********************
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

History:
Put into module format by tshif 2/17/2009
PHP Programming by Swordsteel 2/17/2009

Originally maintained by the PBX Open Source Software Alliance
Last modified Oct 15, 2012
**************************************************************/


$action = isset($_POST['action']) ? $_POST['action'] : '';
$tabindex=0;

switch($action) {
case "add":
    $originalfile = '';

case "edit":

    if(isset($_POST['extdisplay']) && $_POST['extdisplay']!='') {
        $uinput = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $_POST['extdisplay']);
        $uinput = preg_replace("([\.]{2,})", '', $uinput);
        $parts = preg_split("/-/",$uinput);
        $file = "wuc.".$parts[0].".ext.".$parts[1].".call";
        $originalfile = "/var/spool/asterisk/outgoing/".$file;
    } else {
        $originalfile = '';
    }

    $parts = preg_split("/T/",$_POST['datetime']);
    $date = $parts[0];
    $time = $parts[1];
    $dateparts = preg_split("/-/",$date);
    $timeparts = preg_split("/:/",$time);

    $HH   = $timeparts[0];
    $MM   = $timeparts[1];
    $ext  = $_POST['ExtBox'];
    $DD   = $dateparts[2];
    $MON  = $dateparts[1];
    $YYYY = $dateparts[0];

    //  check to prevent user from scheduling a call in the past
    if ($MM == "") {
        $MM = "0";
    }
    $time_wakeup = mktime( $HH , $MM, 0, $MON, $DD, $YYYY );
    $time_now = time( );
    $badtime = false;
    if ( $time_wakeup <= $time_now )  {
        $badtime = true;
    }

    // check for insufficient data
    if ($HH == "" || $ext == "" || $DD == "" || $MON == "" || $YYYY == "" || $badtime )  {

        $_SESSION['msg']=base64_encode(__('Cannot schedule the call, either due to insufficient data or the scheduled time was in the past'));
        $_SESSION['msgtype']='error';
        $_SESSION['msgtstamp']=time();
        redirect_standard('');

    } else {

        // Get module config info for writing the file $parm_application and $parm_data are used to define what the wakup call
        // does when answered.  Currently these are not part of the module config options but need to be to allow users to choose
        // their own destination
        
        $moduleconfig = hotelwakeup_getconfig();  // module config provided by user
        $parm_application = 'AGI';
        $parm_data = 'wakeconfirm.php';
    
        $foo = array(
            'time'        => $time_wakeup,
            'date'        => 'unused',
            'ext'         => $ext,
            'maxretries'  => $moduleconfig['maxretries'],
            'retrytime'   => $moduleconfig['retrytime'],
            'waittime'    => $moduleconfig['waittime'],
            'callerid'    => $moduleconfig['cnam']." <".$moduleconfig['cid'].">",
            'application' => $parm_application,
            'data'        => $parm_data,
            'originalfile'=> $originalfile
        );

        $newdisplay = hotelwakeup_gencallfile($foo);
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        $_SESSION['extdisplay']=$_POST['ExtBox'];
        $_REQUEST['extdisplay']=$newdisplay;
        redirect_standard('extdisplay');
    }
    break;
case "saveconfig":
    hotelwakeup_saveconfig();
    $_REQUEST['action']='';
    $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
    $_SESSION['msgtype']='success';
    $_SESSION['msgtstamp']=time();
    redirect_standard('extdisplay');
    break;
case "delete":
    $uinput = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $_POST['extdisplay']);
    $uinput = preg_replace("([\.]{2,})", '', $uinput);
    $parts = preg_split("/-/",$uinput);
    $file = "wuc.".$parts[0].".ext.".$parts[1].".call";
    $fullpath = "/var/spool/asterisk/outgoing/".$file;
    if (file_exists($fullpath)) {
        unlink($fullpath);
        $_REQUEST['action']='';
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        $_SESSION['msgtstamp']=time();
        redirect_standard('');
    } else {
        $_REQUEST['action']='';
        $_SESSION['msg']=base64_encode(__('Could not find file to remove'));
        $_SESSION['msgtype']='error';
        $_SESSION['msgtstamp']=time();
        redirect_standard('');
    }
}


/*
// Process form if button B1 is clicked
if (isset($_POST['moduleconfig'])){
    hotelwakeup_saveconfig();
}

// Process form if delete button clicked
if(isset($_POST['DELETE'])) {
    if (file_exists($_POST['filename'])) {
        unlink($_POST['filename']);
    }
}

 */


//  Process form if Schedule button clicked
if(isset($_POST['SCHEDULE'])) {
    $HH=$_POST['HH'];
    $MM=$_POST['MM'];
    $Ext=$_POST['ExtBox'];
    $DD=$_POST['DD'];
    $MON = $_POST['MON'];
    $YYYY = $_POST['YYYY'];

    //  check to prevent user from scheduling a call in the past
    if ($MM == "") {
        $MM = "0";
    }
    $time_wakeup = mktime( $HH , $MM, 0, $MON, $DD, $YYYY );
    $time_now = time( );
    $badtime = false;
    if ( $time_wakeup <= $time_now )  {
        $badtime = true;
    }

    // check for insufficient data
    if ($HH == "" || $Ext == "" || $DD == "" || $MON == "" || $YYYY == "" || $badtime )  {
        // abandon .call file creation and pop up a js alert to the user
        echo "<script type='text/javascript'>\n";
        echo "alert('Cannot schedule the call, either due to insufficient data or the scheduled time was in the past');\n";
        echo "</script>";
    }
    else
    {

    // Get module config info for writing the file $parm_application and $parm_data are used to define what the wakup call
    // does when answered.  Currently these are not part of the module config options but need to be to allow users to choose
    // their own destination
    $moduleconfig = hotelwakeup_getconfig();  // module config provided by user
    $parm_application = 'AGI';
    $parm_data = 'wakeconfirm.php';

    $foo = array(
        time  => $time_wakeup,
        date => 'unused',
        ext => $Ext,
        maxretries => $moduleconfig['maxretries'],
        retrytime => $moduleconfig['retrytime'],
        waittime => $moduleconfig['waittime'],
        callerid => $moduleconfig['cnam']." <".$moduleconfig['cid'].">",
        application => $parm_application,
        data => $parm_data,
    );

    hotelwakeup_gencallfile($foo);
    // Can't decide if I should clear the schedule variables ($HH, $MM, etc.) here to refresh schedule fields in GUI
    }
}

$module_local = hotelwakeup_xml2array("modules/hotelwakeup/module.xml");

$count=0;
$rnaventries = array();
$allcalls    = array();
$files = glob("/var/spool/asterisk/outgoing/wuc*.call");
$rnaventries[] = array("config",__("Module configuration"),"");
foreach ($files as $file) {
    $myresult = checkWakeUpProp($file);
    $filetime = date('H',filemtime($file)).":".date('i',filemtime($file));   //create a time string to display from the file timestamp
    $filedate = strftime("%x",filemtime($file));
    if(count($myresult)>0) {
        $wucext = $myresult['ext'];
        $id = $myresult['tstamp']."-".$wucext;
        $label = '<span class="tag is-info is-small">'.$filedate." ".$filetime.'</span> '.$wucext.'';
        $rnaventries[] = array($id,$label,'');
        $allcalls[$id]=$myresult;
        $count++;
    }
}
drawListMenu($rnaventries, $type, $display, $extdisplay);
?>
<div class='content'>
<?php

$helptext = __("Wake Up calls can be used to schedule a reminder or wakeup call to any valid destination. To schedule a call, dial the feature code assigned in IssabelPBX Feature Codes or use the form below.");
$helptextconfig = __("By default, Wake Up calls are only made back to the Caller ID of the user which requests them. When the Operator Mode is enabled, certain extensions are identified to be able to request a Wake Up call for any valid internal or external destination.");

$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';
$helpconfig = '<div class="infohelp">?<span style="display:none;">'.$helptextconfig.'</span></div>';

$disable_delete = false;
if($extdisplay!='') {

    if($extdisplay!='config') {

        $current_hour  = isset($allcalls[$extdisplay])?$allcalls[$extdisplay]['hour']:date('H',strtotime('+1 hour'));
        $current_min   = isset($allcalls[$extdisplay])?$allcalls[$extdisplay]['minute']:date('i',strtotime('+1 hour'));
        $current_day   = isset($allcalls[$extdisplay])?$allcalls[$extdisplay]['day']:date('d',strtotime('+1 hour'));
        $current_month = isset($allcalls[$extdisplay])?$allcalls[$extdisplay]['month']:date('m',strtotime('+1 hour'));
        $current_year  = isset($allcalls[$extdisplay])?$allcalls[$extdisplay]['year']:date('Y',strtotime('+1 hour'));
        $current_ext   = isset($allcalls[$extdisplay])?$allcalls[$extdisplay]['ext']:'';
        $action        = "edit";

        echo "<div class='is-flex'><h2>".__("Edit Wake Up Call").": ".$current_ext."</h2>$help</div>";

    } else {

        // Get module config info
        $disable_delete=true;
        $moduleconfig = hotelwakeup_getconfig();
        echo "<div class='is-flex'><h2>".__("Hotel Wake Up Configuration")."</h2>$helpconfig</div>";
    }

}  else {
    $current_hour  = date('H',strtotime('+1 hour'));
    $current_min   = date('i',strtotime('+1 hour'));
    $current_day   = date('d',strtotime('+1 hour'));
    $current_month = date('m',strtotime('+1 hour'));
    $current_year  = date('Y',strtotime('+1 hour'));
    $current_ext   = "";
    $action        = "add";

    echo "<div class='is-flex'><h2>".__("Add Wake Up Call")."</h2>$help</div>";
}

if($extdisplay!='config') {
?>

<form name="editWakeup" id="mainform" method="post" onsubmit="return edit_onsubmit(this)">
    <input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="action" value="<?php echo $action; ?>">

    <table class='table is-borderless is-narrow'>

    <tr><td colspan="2"><h5><?php  echo _dgettext('amp','General Settings') ?></h5></td></tr>

    <tr>
        <td>
            <a href="#" class="info"><?php echo __("Destination")?><span><?php echo __("The destination extension for this call")?></span></a>
        </td>
        <td>
            <input class='input w100' autofocus type="text" name="ExtBox" id="ExtBox" value="<?php  echo $current_ext; ?>" tabindex="<?php echo ++$tabindex;?>">
        </td>
    </tr>
    <tr>
        <td>
            <a href="#" class="info"><?php echo __("Date/Time")?><span><?php echo __("The date")?></span></a>
        </td>
        <td>
            <input class='input' type="datetime-local" name="datetime" value="<?php  echo "$current_year-$current_month-${current_day}T$current_hour:$current_min"; ?>" tabindex="<?php echo ++$tabindex;?>">
        </td>
    </tr>

    <tr><td colspan="2"><h5><?php  echo __('System Settings') ?></h5></td></tr>
    <tr><td colspan="2"><?php echo __("For scheduled calls to be delivered at the correct time, the system time zone and current time must be set properly. The system is reporting the following time zone and time:")?></td></tr>
    <tr><td><?php echo __("Time zone");?></td><td><?php echo date_default_timezone_get() ?></td></tr>
    <tr><td><?php echo __("System time");?></td><td><span id="idTime">00:00:00</span></td></tr>
</table>
</form>

<?php } else { ?>

<form id="mainform" NAME="saveconfig" id="saveconfig" method="post" >
  <input type=hidden name=action value='saveconfig'>

  <table class='table is-borderless is-narrow'>
    <tr><td colspan="2"><h5><?php  echo _dgettext('amp','General Settings') ?></h5></td></tr>
    <tr>
      <td>
        <a href="#" class="info"><?php echo __("Operator Mode")?><span><?php echo __("<u>ENABLE</u> Operator Mode to allow designated extentions to create wake up calls for any valid destination.<br><u>DISABLE</u> Calls can only be placed back to the caller ID of the user scheduling the wakeup call.")?></span></a>
      </td>
      <td>
          <?php echo ipbx_radio('operator_mode',array(array('value'=>'1','text'=>__('Enabled')),array('value'=>'0','text'=>__('Disabled'))),$moduleconfig['operator_mode'],false); ?>
      </td>
    </tr>
    <tr>
      <td>
        <a href="#" class="info"><?php echo __("Max Dest. Length")?><span><?php echo __("This controls the maximum number of digits an operator can send a wakeup call to. Set to 10 or 11 to allow wake up calls to outside numbers.")?></span></a>
      </td>
      <td>
          <input type="text" name="extensionlength" class="input" value="<?php echo $moduleconfig['extensionlength']?>">
      </td>
    </tr>
    <tr>
      <td>
        <a href="#" class="info"><?php echo __("Operator Extensions")?><span><?php echo __("Enter the Caller ID's of each telephone you wish to be recognized as an `Operator`.  Operator extensions are allowed to create wakeup calls for any valid destination. Numbers can be extension numbers, full caller ID numbers or Asterisk dialing patterns.")?></span></a>
      <td >
          <input type="text" name="operator_extensions" class="input" value="<?php echo $moduleconfig['operator_extensions']?>">
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td><?php echo __('(Use a comma separated list)');?></td>
    </tr>
    <tr>
      <td>
        <a href="#" class="info"><?php echo __("Ring Time")?><span><?php echo __("The number of seconds for the phone to ring. Consider setting lower than the voicemail threshold or the wakeup call can end up going to voicemail.")?></span></a>
    </td>
      <td>
        <input type="number" name="waittime" class="input" value="<?php echo $moduleconfig['waittime']?>">
    </td>
  </tr>
  <tr>
    <td>
      <a href="#" class="info"><?php echo __("Retry Time")?><span><?php echo __("The number of seconds to wait between retrys.  A 'retry' happens if the wakeup call is not answered.")?></span></a>
    </td>
    <td>
        <input type="number" name="retrytime" class="input" value="<?php echo $moduleconfig['retrytime']?>">
    </td>
  </tr>
  <tr>
    <td>
      <a href="#" class="info"><?php echo __("Max Retries")?><span><?php echo __("The maximum number of times the system should attempt to deliver the wakeup call when there is no answer.  Zero retries means only one call will be placed.")?></span></a>
    </td>
    <td>
        <input type="number" name="maxretries" class="input" value="<?php echo $moduleconfig['maxretries']?>">
    </td>
  </tr>
  <tr>
    <td>
        <a href="#" class="info"><?php echo __("Caller ID")?><span><?php echo __("<u>First Box:</u> Enter the CNAM (Caller ID Name) to be sent by the system when placing the wakeup calls.  Enclose this string with \" if required by your system.<br><u>Second Box:</u> Enter the CID (Caller ID number) of the Caller ID to be sent when the system places wake up calls.")?></span></a>
    <td>
      <input type="text" name="calleridtext" class="myinput" size="13" value="<?php echo $moduleconfig['cnam'];?>">
      &lt; <input type="text" name="calleridnumber" class="myinput" size="13" value="<?php echo $moduleconfig['cid'];?>"> &gt;
    </td>
  </tr>
</table>
<small><?php echo __('*Some systems require quote marks around the textual caller ID. You may include the " " if needed by your system.');?></small>
</form>
<?php } ?>

<script>

var hour = <?php $l = localtime(); echo $l[2]?>;
var min  = <?php $l = localtime(); echo $l[1]?>;
var sec  = <?php $l = localtime(); echo $l[0]?>;
var mytimeout;
//wakeupcalls stole this from timegroups
//who stole this from timeconditions
//who stole it from http://www.aspfaq.com/show.asp?id=2300
function PadDigits(n, totalDigits) 
{ 
    n = n.toString(); 
    var pd = ''; 
    if (totalDigits > n.length) 
    { 
        for (i=0; i < (totalDigits-n.length); i++) 
        { 
            pd += '0'; 
        } 
    } 
    return pd + n.toString(); 
} 

function updateTime()
{
    sec++;
    if (sec==60)
    {
        min++;
        sec = 0;
    }    
        
    if (min==60)
    {
        hour++;
        min = 0;
    }

    if (hour==24)
    {
        hour = 0;
    }
    
    if($('#idTime').length>0) {
        document.getElementById("idTime").innerHTML = PadDigits(hour,2)+":"+PadDigits(min,2)+":"+PadDigits(sec,2);
        mytimeout = setTimeout('updateTime()',1000);
    }
}

function edit_onsubmit(theForm) {

    var msgInvalid = "<?php echo __('Please enter a valid extension');?>";

    if (isEmpty(theForm.ExtBox.value))
        return warnInvalid(theForm.ExtBox, msgInvalid);

    $.LoadingOverlay('show');
    return true;
}

up.compiler('.content', function() {
    updateTime();
    $("#ExtBox").numeric();
});

<?php echo js_display_confirmation_toasts(); ?>
</script>
</div>
<?php echo form_action_bar($extdisplay,'',$disable_delete); ?>
