<?php /* $Id: page.ringgroups.php 1124 2006-03-13 21:39:16Z rcourtna $ */
//Copyright (C) 2004 Coalescent Systems Inc. (info@coalescentsystems.ca)
//
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$tabindex = 0;
$dispnum = 'ringgroups'; //used for switch on config.php
isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';
//the extension we are currently displaying
isset($_REQUEST['extdisplay'])?$extdisplay=$_REQUEST['extdisplay']:$extdisplay='';
isset($_REQUEST['account'])?$account = $_REQUEST['account']:$account='';
isset($_REQUEST['grptime'])?$grptime = $_REQUEST['grptime']:$grptime='';
isset($_REQUEST['grppre'])?$grppre = $_REQUEST['grppre']:$grppre='';
isset($_REQUEST['strategy'])?$strategy = $_REQUEST['strategy']:$strategy='';
isset($_REQUEST['annmsg_id'])?$annmsg_id = $_REQUEST['annmsg_id']:$annmsg_id='';
isset($_REQUEST['description'])?$description = $_REQUEST['description']:$description='';
isset($_REQUEST['alertinfo'])?$alertinfo = $_REQUEST['alertinfo']:$alertinfo='';
isset($_REQUEST['needsconf'])?$needsconf = $_REQUEST['needsconf']:$needsconf='';
isset($_REQUEST['cwignore'])?$cwignore = $_REQUEST['cwignore']:$cwignore='';
isset($_REQUEST['cpickup'])?$cpickup = $_REQUEST['cpickup']:$cpickup='';
isset($_REQUEST['cfignore'])?$cfignore = $_REQUEST['cfignore']:$cfignore='';
isset($_REQUEST['remotealert_id'])?$remotealert_id = $_REQUEST['remotealert_id']:$remotealert_id='';
isset($_REQUEST['toolate_id'])?$toolate_id = $_REQUEST['toolate_id']:$toolate_id='';
isset($_REQUEST['ringing'])?$ringing = $_REQUEST['ringing']:$ringing='';

isset($_REQUEST['changecid'])?$changecid = $_REQUEST['changecid']:$changecid='default';
isset($_REQUEST['fixedcid'])?$fixedcid = $_REQUEST['fixedcid']:$fixedcid='';
isset($_REQUEST['recording'])?$recording = $_REQUEST['recording']:$recording='dontcare';

if (isset($_REQUEST['goto0']) && isset($_REQUEST[$_REQUEST['goto0']."0"])) {
				$goto = $_REQUEST[$_REQUEST['goto0']."0"];
} else {
				$goto = '';
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
		$grplist[$key] = preg_replace("/[^0-9#*]/", "", $grplist[$key]);

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
		echo "<script>javascript:alert('". _("Warning! Extension")." ".$account." "._("is not allowed for your account").".');</script>";
	} else {
		//add group
		if ($action == 'addGRP') {

			$conflict_url = array();
			$usage_arr = framework_check_extension_usage($account);
			if (!empty($usage_arr)) {
				$conflict_url = framework_display_extension_usage_alert($usage_arr);

			} elseif (ringgroups_add($account,$strategy,$grptime,implode("-",$grplist),$goto,$description,$grppre,$annmsg_id,$alertinfo,$needsconf,$remotealert_id,$toolate_id,$ringing,$cwignore,$cfignore,$changecid,$fixedcid,$cpickup,$recording)) {

				// save the most recent created destination which will be picked up by
				//
				$this_dest = ringgroups_getdest($account);
				fwmsg::set_dest($this_dest[0]);
				needreload();
				redirect_standard();
			}
		}

		//del group
		if ($action == 'delGRP') {
			ringgroups_del($account);
			needreload();
			redirect_standard();
		}

		//edit group - just delete and then re-add the extension
		if ($action == 'edtGRP') {
			ringgroups_del($account);
			ringgroups_add($account,$strategy,$grptime,implode("-",$grplist),$goto,$description,$grppre,$annmsg_id,$alertinfo,$needsconf,$remotealert_id,$toolate_id,$ringing,$cwignore,$cfignore,$changecid,$fixedcid,$cpickup,$recording);
			needreload();
			redirect_standard('extdisplay');
		}
	}
}
?>

<div class="rnav"><ul>
		<li><a class="<?php  echo ($extdisplay=='' ? 'current':'') ?>" href="config.php?display=<?php echo urlencode($dispnum)?>"><?php echo _("Add Ring Group")?></a></li>
<?php
//get unique ring groups
$gresults = ringgroups_list();

if (isset($gresults)) {
	foreach ($gresults as $gresult) {
		echo "<li><a class=\"".($extdisplay=='GRP-'.$gresult[0] ? 'current':'')."\" href=\"config.php?display=".urlencode($dispnum)."&extdisplay=".urlencode("GRP-".$gresult[0])."\">".$gresult[1]." ({$gresult[0]})</a></li>";
	}
}
?>
</ul></div>

<?php
if ($action == 'delGRP') {
	echo '<br><h3>'._("Ring Group").' '.$account.' '._("deleted").'!</h3><br><br><br><br><br><br><br><br>';
} else {
	if ($extdisplay) {
		// We need to populate grplist with the existing extension list.
		$thisgrp = ringgroups_get(ltrim($extdisplay,'GRP-'));
		$grpliststr = $thisgrp['grplist'];
		$grplist = explode("-", $grpliststr);
		$strategy = $thisgrp['strategy'];
		$grppre = $thisgrp['grppre'];
		$grptime = $thisgrp['grptime'];
		$goto = $thisgrp['postdest'];
		$annmsg_id = $thisgrp['annmsg_id'];
		$description = $thisgrp['description'];
		$alertinfo = $thisgrp['alertinfo'];
		$remotealert_id = $thisgrp['remotealert_id'];
		$needsconf = $thisgrp['needsconf'];
		$cwignore = $thisgrp['cwignore'];
		$cpickup = $thisgrp['cpickup'];
		$cfignore = $thisgrp['cfignore'];
		$toolate_id = $thisgrp['toolate_id'];
		$ringing = $thisgrp['ringing'];
		$changecid   = isset($thisgrp['changecid'])   ? $thisgrp['changecid']   : 'default';
		$fixedcid    = isset($thisgrp['fixedcid'])    ? $thisgrp['fixedcid']    : '';
		$recording = $thisgrp['recording'];
		unset($grpliststr);
		unset($thisgrp);

		$delButton = "
			<form name=delete action=\"{$_SERVER['PHP_SELF']}\" method=POST>
				<input type=\"hidden\" name=\"display\" value=\"{$dispnum}\">
				<input type=\"hidden\" name=\"account\" value=\"".ltrim($extdisplay,'GRP-')."\">
				<input type=\"hidden\" name=\"action\" value=\"delGRP\">
				<input type=submit value=\""._("Delete Group")."\">
			</form>";

		echo "<h2>"._("Ring Group").": ".ltrim($extdisplay,'GRP-')."</h2>";
		echo "<p>".$delButton."</p>";

		$usage_list = framework_display_destination_usage(ringgroups_getdest(ltrim($extdisplay,'GRP-')));
		if (!empty($usage_list)) {
		?>
			<a href="#" class="info"><?php echo $usage_list['text']?>:<span><?php echo $usage_list['tooltip']?></span></a>
		<?php
		}

	} else {
		$grplist = explode("-", '');;
		$strategy = '';
		$grppre = '';
		$grptime = '';
		$goto = '';
		$annmsg_id = '';
		$alertinfo = '';
		$remotealert_id = '';
		$needsconf = '';
		$cwignore = '';
		$cpickup = '';
		$cfignore = '';
		$toolate_id = '';
		$ringing = '';

		if (!empty($conflict_url)) {
			echo "<h5>"._("Conflicting Extensions")."</h5>";
			echo implode('<br .>',$conflict_url);
		}

		echo "<h2>"._("Add Ring Group")."</h2>";
	}
	?>
			<form class="popover-form" name="editGRP" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkGRP(editGRP);">
			<input type="hidden" name="display" value="<?php echo $dispnum?>">
			<input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edtGRP' : 'addGRP'); ?>">
			<table>
			<tr><td colspan="2"><h5><?php  echo ($extdisplay ? _("Edit Ring Group") : _("Add Ring Group")) ?><hr></h5></td></tr>
			<tr>
<?php
	if ($extdisplay) {

?>
				<input size="5" type="hidden" name="account" value="<?php  echo ltrim($extdisplay,'GRP-'); ?>" tabindex="<?php echo ++$tabindex;?>">
<?php 		} else { ?>
				<td><a href="#" class="info"><?php echo _("Ring-Group Number")?>:<span><?php echo _("The number users will dial to ring extensions in this ring group")?></span></a></td>
				<td><input size="5" type="text" data-extdisplay="" name="account" value="<?php  if ($gresult[0]==0) { echo "600"; } else { echo $gresult[0] + 1; } ?>" tabindex="<?php echo ++$tabindex;?>"></td>
<?php 		} ?>
			</tr>

			<tr>
		    <td> <a href="#" class="info"><?php echo _("Group Description")?>:<span><?php echo _("Provide a descriptive title for this Ring Group.")?></span></a></td>
				<td><input size="20" maxlength="35" type="text" name="description" value="<?php echo htmlspecialchars($description); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>

			<tr>
				<td> <a href="#" class="info"><?php echo _("Ring Strategy:")?>
				<span>
					<b><?php echo _("ringall")?></b>:  <?php echo _("Ring all available channels until one answers (default)")?><br>
					<b><?php echo _("hunt")?></b>: <?php echo _("Take turns ringing each available extension")?><br>
					<b><?php echo _("memoryhunt")?></b>: <?php echo _("Ring first extension in the list, then ring the 1st and 2nd extension, then ring 1st 2nd and 3rd extension in the list.... etc.")?><br>
					<b><?php echo _("*-prim")?></b>:  <?php echo _("These modes act as described above. However, if the primary extension (first in list) is occupied, the other extensions will not be rung. If the primary is IssabelPBX DND, it won't be rung. If the primary is IssabelPBX CF unconditional, then all will be rung")?><br>
					<b><?php echo _("firstavailable")?></b>:  <?php echo _("ring only the first available channel")?><br>
					<b><?php echo _("firstnotonphone")?></b>:  <?php echo _("ring only the first channel which is not offhook - ignore CW")?><br>
				</span>
				</a></td>
				<td>
					<select name="strategy" tabindex="<?php echo ++$tabindex;?>">
					<?php
						$default = (isset($strategy) ? $strategy : 'ringall');
																								$items = array('ringall','ringall-prim','hunt','hunt-prim','memoryhunt','memoryhunt-prim','firstavailable','firstnotonphone');
						foreach ($items as $item) {
							echo '<option value="'.$item.'" '.($default == $item ? 'SELECTED' : '').'>'._($item);
						}
					?>
					</select>
				</td>
			</tr>

			<tr>
				<td>
					<a href=# class="info"><?php echo _("Ring Time (max 300 sec)")?>
						<span>
							<?php echo _("Time in seconds that the phones will ring. For all hunt style ring strategies, this is the time for each iteration of phone(s) that are rung")?>
						</span>
					</a>
				</td>
				<td><input size="4" type="number" min="0" max="300" name="grptime" value="<?php  echo $grptime?$grptime:20 ?>" tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>

			<tr>
				<td valign="top"><a href="#" class="info"><?php echo _("Extension List")?>:<span><br><?php echo _("List extensions to ring, one per line, or use the Extension Quick Pick below to insert them here.<br><br>You can include an extension on a remote system, or an external number by suffixing a number with a '#'.  ex:  2448089# would dial 2448089 on the appropriate trunk (see Outbound Routing)<br><br>Extensions without a '#' will not ring a user's Follow-Me. To dial Follow-Me, Queues and other numbers that are not extensions, put a '#' at the end.")?><br><br></span></a></td>
				<td valign="top">
<?php
		$rows = count($grplist)+1;
		($rows < 5) ? 5 : (($rows > 20) ? 20 : $rows);
?>
					<textarea id="grplist" cols="15" rows="<?php  echo $rows ?>" name="grplist" tabindex="<?php echo ++$tabindex;?>"><?php echo implode("\n",$grplist);?></textarea>
				</td>
			</tr>

			<tr>
				<td>
				<a href=# class="info"><?php echo _("Extension Quick Pick")?>
					<span>
						<?php echo _("Choose an extension to append to the end of the extension list above.")?>
					</span>
				</a>
				</td>
				<td>
					<select onChange="insertExten();" id="insexten" tabindex="<?php echo ++$tabindex;?>">
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

<?php if(function_exists('recordings_list')) { //only include if recordings is enabled?>
			<tr>
				<td><a href="#" class="info"><?php echo _("Announcement:")?><span><?php echo _("Message to be played to the caller before dialing this group.<br><br>To add additional recordings please use the \"System Recordings\" MENU to the left")?></span></a></td>
				<td>
					<select name="annmsg_id" tabindex="<?php echo ++$tabindex;?>">
					<?php
						$tresults = recordings_list();
						$default = (isset($annmsg_id) ? $annmsg_id : '');
						echo '<option value="">'._("None")."</option>";
						if (isset($tresults[0])) {
							foreach ($tresults as $tresult) {
								echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $default ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
							}
						}
					?>
					</select>
				</td>
			</tr>
<?php }	else { ?>
			<tr>
				<td><a href="#" class="info"><?php echo _("Announcement:")?><span><?php echo _("Message to be played to the caller before dialing this group.<br><br>You must install and enable the \"Systems Recordings\" Module to edit this option")?></span></a></td>
				<td>
					<?php
						$default = (isset($annmsg_id) ? $annmsg_id : '');
					?>
					<input type="hidden" name="annmsg_id" value="<?php echo $default; ?>"><?php echo ($default != '' ? $default : 'None'); ?>
				</td>
			</tr>
<?php } if (function_exists('music_list')) { ?>
			<tr>
				<td><a href="#" class="info"><?php echo _("Play Music On Hold?")?><span><?php echo _("If you select a Music on Hold class to play, instead of 'Ring', they will hear that instead of Ringing while they are waiting for someone to pick up.")?></span></a></td>
				<td>
					<select name="ringing" tabindex="<?php echo ++$tabindex;?>">
					<?php
						$tresults = music_list();
						$cur = (isset($ringing) ? $ringing : 'Ring');
						echo '<option value="Ring">'._("Ring")."</option>";
						if (isset($tresults[0])) {
							foreach ($tresults as $tresult) {
							    ( $tresult == 'none' ? $ttext = _("none") : $ttext = $tresult );
							    ( $tresult == 'default' ? $ttext = _("default") : $ttext = $tresult );
								echo '<option value="'.$tresult.'"'.($tresult == $cur ? ' SELECTED' : '').'>'._($ttext)."</option>\n";
							}
						}
					?>
					</select>
				</td>
			</tr>
<?php } ?>

			<tr>
				<td><a href="#" class="info"><?php echo _("CID Name Prefix")?>:<span><?php echo _('You can optionally prefix the CallerID name when ringing extensions in this group. ie: If you prefix with "Sales:", a call from John Doe would display as "Sales:John Doe" on the extensions that ring.')?></span></a></td>
				<td><input size="4" type="text" name="grppre" value="<?php  echo $grppre ?>" tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>

			<tr>
				<td><a href="#" class="info"><?php echo _("Alert Info")?><span><?php echo _('ALERT_INFO can be used for distinctive ring with SIP devices.')?></span></a>:</td>
				<td><input type="text" name="alertinfo" size="20" value="<?php echo ($alertinfo)?$alertinfo:'' ?>" tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>

			<tr>
		<td><a href="#" class="info"><?php echo _("Ignore CF Settings")?><span> <?php echo _("When checked, agents who attempt to Call Forward will be ignored, this applies to CF, CFU and CFB. Extensions entered with '#' at the end, for example to access the extension's Follow-Me, might not honor this setting .") ?></span></a>:</td>
				<td>
					<input type="checkbox" name="cfignore" value="CHECKED" <?php echo $cfignore ?>   tabindex="<?php echo ++$tabindex;?>"/>
				</td>
			</tr>

			<tr>
		<td><a href="#" class="info"><?php echo _("Skip Busy Agent")?><span> <?php echo _("When checked, agents who are on an occupied phone will be skipped as if the line were returning busy. This means that Call Waiting or multi-line phones will not be presented with the call and in the various hunt style ring strategies, the next agent will be attempted.") ?></span></a>:</td>
				<td>
					<input type="checkbox" name="cwignore" value="CHECKED" <?php echo $cwignore ?>   tabindex="<?php echo ++$tabindex;?>"/>
				</td>
			</tr>

			<tr>
				<td><a href="#" class="info"><?php echo _("Enable Call Pickup")?><span> <?php echo _("Checking this will allow calls to the Ring Group to be picked up with the directed call pickup feature using the group number. When not checked, individual extensions that are part of the group can still be picked up by doing a directed call pickup to the ringing extension, which works whether or not this is checked.") ?></span></a>:</td>
				<td>
					<input type="checkbox" name="cpickup" value="CHECKED" <?php echo $cpickup ?>   tabindex="<?php echo ++$tabindex;?>"/>
				</td>
			</tr>

			<tr>
				<td><a href="#" class="info"><?php echo _("Confirm Calls")?><span><?php echo _('Enable this if you\'re calling external numbers that need confirmation - eg, a mobile phone may go to voicemail which will pick up the call. Enabling this requires the remote side push 1 on their phone before the call is put through. This feature only works with the ringall ring strategy')?></span></a>:</td>
				<td>
					<input type="checkbox" name="needsconf" value="CHECKED" <?php echo $needsconf ?>   tabindex="<?php echo ++$tabindex;?>"/>
				</td>
			</tr>

<?php if(function_exists('recordings_list')) { //only include if recordings is enabled?>
			<tr>
				<td><a href="#" class="info"><?php echo _("Remote Announce:")?><span><?php echo _("Message to be played to the person RECEIVING the call, if 'Confirm Calls' is enabled.<br><br>To add additional recordings use the \"System Recordings\" MENU to the left")?></span></a></td>
				<td>
					<select name="remotealert_id" tabindex="<?php echo ++$tabindex;?>">
					<?php
						$tresults = recordings_list();
						$default = (isset($remotealert_id) ? $remotealert_id : '');
						echo '<option value="">'._("Default")."</option>";
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
				<td><a href="#" class="info"><?php echo _("Too-Late Announce:")?><span><?php echo _("Message to be played to the person RECEIVING the call, if the call has already been accepted before they push 1.<br><br>To add additional recordings use the \"System Recordings\" MENU to the left")?></span></a></td>
				<td>
					<select name="toolate_id" tabindex="<?php echo ++$tabindex;?>">
					<?php
						$tresults = recordings_list();
						$default = (isset($toolate_id) ? $toolate_id : '');
						echo '<option value="">'._("Default")."</option>";
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
			<tr><td colspan="2"><h5><?php echo _("Change External CID Configuration") ?><hr></h5></td></tr>
			<tr>
				<td>
				<a href="#" class="info"><?php echo _("Mode")?>:
				<span>
					<b><?php echo _("Default")?></b>:  <?php echo _("Transmits the Callers CID if allowed by the trunk.")?><br>
					<b><?php echo _("Fixed CID Value")?></b>:  <?php echo _("Always transmit the Fixed CID Value below.")?><br>
					<b><?php echo _("Outside Calls Fixed CID Value")?></b>: <?php echo _("Transmit the Fixed CID Value below on calls that come in from outside only. Internal extension to extension calls will continue to operate in default mode.")?><br>
					<b><?php echo _("Use Dialed Number")?></b>: <?php echo _("Transmit the number that was dialed as the CID for calls coming from outside. Internal extension to extension calls will continue to operate in default mode. There must be a DID on the inbound route for this. This will be BLOCKED on trunks that block foreign CallerID")?><br>
					<b><?php echo _("Force Dialed Number")?></b>: <?php echo _("Transmit the number that was dialed as the CID for calls coming from outside. Internal extension to extension calls will continue to operate in default mode. There must be a DID on the inbound route for this. This WILL be transmitted on trunks that block foreign CallerID")?><br>
				</span>
				</a>
				</td>
				<td>
					<select name="changecid" id="changecid" tabindex="<?php echo ++$tabindex;?>">
					<?php
						$default = (isset($changecid) ? $changecid : 'default');
						echo '<option value="default" '.($default == 'default' ? 'SELECTED' : '').'>'._("Default");
						echo '<option value="fixed" '.($default == 'fixed' ? 'SELECTED' : '').'>'._("Fixed CID Value");
						echo '<option value="extern" '.($default == 'extern' ? 'SELECTED' : '').'>'._("Outside Calls Fixed CID Value");
						echo '<option value="did" '.($default == 'did' ? 'SELECTED' : '').'>'._("Use Dialed Number");
						echo '<option value="forcedid" '.($default == 'forcedid' ? 'SELECTED' : '').'>'._("Force Dialed Number");
						$fixedcid_disabled = ($default != 'fixed' && $default != 'extern') ? 'disabled = "disabled"':'';
					?>
					</select>
				</td>
			</tr>

			<tr>
				<td><a href="#" class="info"><?php echo _("Fixed CID Value")?>:<span><?php echo _('Fixed value to replace the CID with used with some of the modes above. Should be in a format of digits only with an option of E164 format using a leading "+".')?></span></a></td>
				<td><input size="30" type="text" name="fixedcid" id="fixedcid" value="<?php  echo $fixedcid ?>" tabindex="<?php echo ++$tabindex;?>" <?php echo $fixedcid_disabled ?>></td>
			</tr>

			<tr><td colspan="2"><h5><?php echo _("Call Recording") ?><hr></h5></td></tr>
			<tr>
				<td><a href="#" class="info"><?php echo _("Record Calls")?><span><?php echo _('You can always record calls that come into this ring group, never record them, or allow the extension that answers to do on-demand recording. If recording is denied then one-touch on demand recording will be blocked.')?></span></a></td>
				<td><span class="radioset">
					<input type="radio" id="record_always" name="recording" value="always" <?php echo ($recording=='always'?'checked':'');?>><label for="record_always"><?php echo _('Always'); ?></label>
					<input type="radio" id="record_dontcare" name="recording" value="dontcare" <?php echo ($recording=='dontcare'?'checked':'');?>><label for="record_dontcare"><?php echo _('On Demand')?></label>
					<input type="radio" id="record_never" name="recording" value="never" <?php echo ($recording=='never'?'checked':'');?>><label for="record_never"><?php echo _('Never'); ?></label>
				</span></td>
			</tr>

<?php
			// implementation of module hook
			// object was initialized in config.php
			echo $module_hook->hookHtml;
?>

			<tr><td colspan="2"><br><h5><?php echo _("Destination if no answer")?>:<hr></h5></td></tr>

<?php
//draw goto selects
echo drawselects($goto,0);
?>

			<tr>
			<td colspan="2"><br><h6><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>"></h6></td>

			</tr>
			</table>
			</form>
<?php
		} //end if action == delGRP


?>
<script language="javascript">
<!--

$(document).ready(function(){
	$("#changecid").change(function(){
				state = (this.value == "fixed" || this.value == "extern") ? "" : "disabled";
		if (state == "disabled") {
	  $("#fixedcid").attr("disabled",state);
		} else {
			$("#fixedcid").removeAttr("disabled");
		}
	});
});

function insertExten() {
	exten = document.getElementById('insexten').value;

	grpList=document.getElementById('grplist');
	if (grpList.value[ grpList.value.length - 1 ] == "\n") {
		grpList.value = grpList.value + exten;
	} else {
		grpList.value = grpList.value + '\n' + exten;
	}

	// reset element
	document.getElementById('insexten').value = '';
}

function checkGRP(theForm) {
	var msgInvalidGrpNum = "<?php echo _('Invalid Group Number specified'); ?>";
	var msgInvalidExtList = "<?php echo _('Please enter an extension list.'); ?>";
	var msgInvalidTime = "<?php echo _('Invalid time specified'); ?>";
	var msgInvalidGrpTimeRange = "<?php echo _('Time must be between 1 and 300 seconds'); ?>";
	var msgInvalidDescription = "<?php echo _('Please enter a valid Group Description'); ?>";
	var msgInvalidRingStrategy = "<?php echo _('Only ringall, ringallv2, hunt and the respective -prim versions are supported when confirmation is checked'); ?>";

	// set up the Destination stuff
	setDestinations(theForm, 1);

	// form validation
	defaultEmptyOK = false;
	if (!isInteger(theForm.account.value)) {
		return warnInvalid(theForm.account, msgInvalidGrpNum);
	}

	defaultEmptyOK = false;

	<?php if (function_exists('module_get_field_size')) { ?>
	var sizeDisplayName = "<?php echo module_get_field_size('ringgroups', 'description', 35); ?>";
	if (!isCorrectLength(theForm.description.value, sizeDisplayName))
		return warnInvalid(theForm.description, "<?php echo _('The Group Description provided is too long.'); ?>")
	<?php } ?>
	
	if (!isAlphanumeric(theForm.description.value))
		return warnInvalid(theForm.description, msgInvalidDescription);

	if (isEmpty(theForm.grplist.value))
		return warnInvalid(theForm.grplist, msgInvalidExtList);

	if (!theForm.fixedcid.disabled) {
		fixedcid = $.trim(theForm.fixedcid.value);
	  if (!fixedcid.match('^[+]{0,1}[0-9]+$')) {
		  return warnInvalid(theForm.fixedcid, msgInvalidCID);
		}
	}

	defaultEmptyOK = false;
	if (!isInteger(theForm.grptime.value)) {
		return warnInvalid(theForm.grptime, msgInvalidTime);
	} else {
		var grptimeVal = theForm.grptime.value;
		if (grptimeVal < 1 || grptimeVal > 300)
			return warnInvalid(theForm.grptime, msgInvalidGrpTimeRange);
	}

	if (theForm.needsconf.checked && (theForm.strategy.value.substring(0,7) != "ringall" && theForm.strategy.value.substring(0,4) != "hunt")) {
		return warnInvalid(theForm.needsconf, msgInvalidRingStrategy);
	}

	if (!validateDestinations(theForm, 1, true))
		return false;

	return true;
}
//-->
</script>
