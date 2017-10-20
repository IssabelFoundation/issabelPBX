<?php
/*************** Wakeup Calls Module  ***************
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

Currently maintained by the PBX Open Source Software Alliance
https://github.com/POSSA/Hotel-Style-Wakeup-Calls
Last modified Oct 15, 2012
**********************************************************/

/***** remove check for updates now that module is pushed from IssabelPBX repo ***
// check to see if user has automatic updates enabled in IssabelPBX settings
$cm =& cronmanager::create($db);
$online_updates = $cm->updates_enabled() ? true : false;

// check dev site to see if new version of module is available
if ($online_updates && $foo = hotelwakeup_vercheck()) {
	print "<br>A <b>new version of this module is available</b> from the <a target='_blank' href='http://pbxossa.org'>PBX Open Source Software Alliance</a><br>";
	}
******************************************************************************/

// Process form if button B1 is clicked
if (isset($_POST['B1'])){
	hotelwakeup_saveconfig();
	}

// Process form if delete button clicked
if(isset($_POST['DELETE'])) {
	if (file_exists($_POST['filename'])) {
		unlink($_POST['filename']);
	}
}

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
	$date = hotelwakeup_getconfig();  // module config provided by user
	$parm_application = 'AGI';
	$parm_data = 'wakeconfirm.php';

	$foo = array(
		time  => $time_wakeup,
		date => 'unused',
		ext => $Ext,
		maxretries => $date[maxretries],
		retrytime => $date[retrytime],
		waittime => $date[waittime],
		callerid => $date[cnam]." <".$date[cid].">",
		application => $parm_application,
		data => $parm_data,
	);

	hotelwakeup_gencallfile($foo);
	// Can't decide if I should clear the schedule variables ($HH, $MM, etc.) here to refresh schedule fields in GUI
	}
}

// Get module config info
$date = hotelwakeup_getconfig();
	$module_local = hotelwakeup_xml2array("modules/hotelwakeup/module.xml");

// Prepopulate date fields with current day if $_POST values unavailable
$w = getdate();
if (!$MON) { $MON  = $w['mon'];}
if (!$DD)  { $DD   = $w['mday'];}
if (!$YYYY){ $YYYY = $w['year'];}

?>
<h1><b>Wake Up Calls</b></h1>
<hr><br>
Wake Up calls can be used to schedule a reminder or wakeup call to any valid destination.<br>
To schedule a call, dial the feature code assigned in IssabelPBX Feature Codes or use the<br>
form below.<br><br>

<h2><b>Schedule a new call:</b></h2>

<?php
echo "<FORM NAME=\"InsertFORM\"  ACTION=\"\" METHOD=POST>Destination: <INPUT TYPE=\"TEXTBOX\" NAME=\"ExtBox\" VALUE=\"$Ext\" SIZE=\"12\" MAXLENGTH=\"20\">HH:MM <INPUT TYPE=\"TEXTBOX\" NAME=\"HH\" VALUE=\"$HH\" SIZE=\"2\" MAXLENGTH=\"2\">:\n";
echo "<INPUT TYPE=\"TEXTBOX\" NAME=\"MM\" VALUE=\"$MM\" SIZE=\"2\" MAXLENGTH=\"2\">DD / MM / YYYY <INPUT TYPE=\"TEXTBOX\" NAME=\"DD\" SIZE=\"2\" MAXLENGTH=\"2\" VALUE=\"$DD\">/\n";
echo "<INPUT TYPE=\"TEXTBOX\" NAME=\"MON\" SIZE=\"2\" MAXLENGTH=\"2\" VALUE=\"$MON\">/<INPUT TYPE=\"TEXTBOX\" NAME=\"YYYY\" SIZE=\"4\" MAXLENGTH=\"4\" VALUE=\"$YYYY\">\n";
echo "<INPUT TYPE=\"SUBMIT\" NAME=\"SCHEDULE\" VALUE=\"SCHEDULE\">\n";
echo "</FORM>\n";

echo "<br><h2><b>Scheduled Calls:</b></h2>\n";
// Page is static, so add button to refresh table
echo "<FORM NAME=\"refresh\" ACTION=\"\" METHOD=POST><INPUT NAME=\"RefreshTable\" TYPE=\"SUBMIT\" VALUE=\"Refresh Table\"></form>\n";
echo "<TABLE cellSpacing=1 cellPadding=1 width=900 border=1 >\n" ;
echo "<TD>Time</TD><TD>Date</TD><TD>Destination</TD><TD>Delete</TD></TR>\n" ;

// check spool directory and create a table listing all .call files created by this module
$count = 0;
$files = glob("/var/spool/asterisk/outgoing/wuc*.call");
foreach($files as $file) {
	$myresult = CheckWakeUpProp($file);
	$filedate = date(M,filemtime($file))." ".date(d,filemtime($file))." ".date(Y,filemtime($file))  ; //create a date string to display from the file timestamp
	$filetime = date(H,filemtime($file)).":".date(i,filemtime($file));   //create a time string to display from the file timestamp
	If ($myresult <> '') {
		$h = substr($myresult[0],0,2);
		$m = substr($myresult[0],2,3);
		$wucext = $myresult[1];
 		echo "<TR><TD><FORM NAME=\"UpdateFORM\" ACTION=\"\" METHOD=POST><FONT face=verdana,sans-serif>" . $filetime . "</TD><TD>".$filedate."</TD><TD>" .$wucext ."</TD><TD><input type=\"hidden\" id=\"filename\" name=\"filename\" value=\"$file\"><INPUT NAME=\"DELETE\" TYPE=\"SUBMIT\" VALUE=\"Delete\"></TD></FORM>\n";
	}
	$count++;
}
echo "</TABLE>\n";
if (!$count){
	print "No scheduled calls";
        }
?>
<br><br>

<form NAME="SAVECONFIG" id="SAVECONFIG" method="POST" action="">
<h2><b>Module Configuration:</b></h2>
By default, Wake Up calls are only made back to the Caller ID of the user which requests them.<br>
When the Operator Mode is enabled, certain extensions are identified to be able to request a <br>
Wake Up call for any valid internal or external destination.<br><br>
<table border="0" width="430" id="table1">
  <tr>
    <td width="153"><a href="javascript: return false;" class="info">Operator Mode: <span><u>ENABLE</u> Operator Mode to allow designated extentions to create wake up calls for any valid destination.<br><u>DISABLE</u> Calls can only be placed back to the caller ID of the user scheduling the wakeup call.</span></a></td>
    <td width="129">
<?php 
echo "<input type=\"radio\" value=\"0\" name=\"operator_mode\"".(($date[operator_mode]==0)?' checked':'').">\n";
?> 
Disabled&nbsp;</td>
    <td>
<?php
echo "<input type=\"radio\" value=\"1\" name=\"operator_mode\"".(($date[operator_mode]==1)?' checked':'').">\n";
?>
&nbsp; Enabled</td>
  </tr>
  <tr>
    <td width="180"><a href="javascript: return false;" class="info">Max Dest. Length: <span>This controls the maximum number of digits an operator can send a wakeup call to. Set to 10 or 11 to allow wake up calls to outside numbers.</span></a></td>
    <td width="129">&nbsp;
<?php
echo "<input type=\"text\" name=\"extensionlength\" size=\"8\" value=\"{$date[extensionlength]}\" style=\"text-align: right\">Digits\n ";
?>
</td>
    <td> &nbsp;</td>
  </tr>
  <tr>
    <td width="180"><a href="javascript: return false;" class="info">Operator Extensions: <span>Enter the Caller ID's of each telephone you wish to be recognized as an `Operator`.  Operator extensions are allowed to create wakeup calls for any valid destination. Numbers can be extension numbers, full caller ID numbers or Asterisk dialing patterns.</span></a></td>
    <td colspan="2">
<?php
echo "<input type=\"text\" name=\"operator_extensions\" size=\"37\" value=\"{$date[operator_extensions]}\">\n";
?>
    </td>
  </tr>
  <tr>
    <td width="153">&nbsp;</td>
    <td colspan="2">(Use a comma separated list)</td>
  </tr>
</table>

<table border="0" width="428" id="table2">
  <tr>
    <td width="155"><a href="javascript: return false;" class="info">Ring Time:<span>The number of seconds for the phone to ring. Consider setting lower than the voicemail threshold or the wakeup call can end up going to voicemail.</span></a></td>
    <td>
<?php
echo "<input type=\"text\" name=\"waittime\" size=\"13\" value=\"{$date[waittime]}\" style=\"text-align: right\">\n";
?> Seconds
    </td>
  </tr>
  <tr>
    <td width="155"><a href="javascript: return false;" class="info">Retry Time:<span>The number of seconds to wait between retrys.  A 'retry' happens if the wakeup call is not answered.</span></a></td>
    <td>
<?php
echo "<input type=\"text\" name=\"retrytime\" size=\"13\" value=\"{$date[retrytime]}\" style=\"text-align: right\">\n";
?> Seconds
    </td>
  </tr>
  <tr>
    <td width="155"><a href="javascript: return false;" class="info">Max Retries:<span>The maximum number of times the system should attempt to deliver the wakeup call when there is no answer.  Zero retries means only one call will be placed.</span></a></td>
    <td>
<?php
echo "<input type=\"text\" name=\"maxretries\" size=\"13\" value=\"{$date[maxretries]}\" style=\"text-align: right\">\n";
?> Tries
    </td>
  </tr>

  <tr>
    <td width="155"><a href="javascript: return false;" class="info">Wake Up Caller ID:<span><u>First Box: </u>Enter the CNAM (Caller ID Name) to be sent by the system when placing the wakeup calls.  Enclose this string with " if required by your system.<br><u>Second Box: </u>Enter the CID (Caller ID number) of the Caller ID to be sent when the system places wake up calls.</span></a></td>
    <td>
<?php
//echo "&quot;<input type=\"text\" name=\"calleridtext\" size=\"10\" value=\"{$date[cnam]}\" style=\"text-align: center\">&quot;\n";
echo "<input type=\"text\" name=\"calleridtext\" size=\"13\" value=\"{$date[cnam]}\" style=\"text-align: center\">\n";
echo "&lt;<input type=\"text\" name=\"calleridnumber\" size=\"5\" value=\"{$date[cid]}\" style=\"text-align: center\">&gt;\n";
?>
    </td>
  </tr>
</table>
<small>*Some systems require quote marks around the textual caller ID. You may include the " " if needed by your system.</small>

<br><input type="submit" value="Submit" name="B1"><br><br>
</FORM>

<h2><b>System Settings:</b></h2>
For scheduled calls to be delivered at the correct time, the system time zone and current time must be set properly.<br>
The system is reporting the following time zone and time:<br>
<b>Time zone:</b>  <?php echo date_default_timezone_get() ?><br>
<?php echo _("<b>System time:</b> ")?> <span id="idTime">00:00:00</span>

<script>
var hour = <?php $l = localtime(); echo $l[2]?>;
var min  = <?php $l = localtime(); echo $l[1]?>;
var sec  = <?php $l = localtime(); echo $l[0]?>;

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
	
	document.getElementById("idTime").innerHTML = PadDigits(hour,2)+":"+PadDigits(min,2)+":"+PadDigits(sec,2);
	setTimeout('updateTime()',1000);
}

updateTime();
$(document).ready(function(){
	$(".remove_section").click(function(){
    if (confirm('<?php echo _("This section will be removed from this time group and all current settings including changes will be updated. OK to proceed?") ?>')) {
      $(this).parent().parent().prev().remove();
      $(this).closest('form').submit();
    }
  });
});
</script>

<?php
print '<p align="center" style="font-size:11px;">Wake Up Calls Module version '.$module_local['module']['version'];
print '<br>The module is maintained by the developer community at the <a target="_blank" href="http://pbxossa.org"> PBX Open Source Software Alliance</a><br></p>';


	function CheckWakeUpProp($file) {
		$myresult = '';
		$file =basename($file);
			$WakeUpTmp = explode(".", $file);
			$myresult[0] = $WakeUpTmp[1];
			$myresult[1] = $WakeUpTmp[3];
		return $myresult;
   	}
?>
