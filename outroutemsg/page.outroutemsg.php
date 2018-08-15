<?php /* $Id: page.outroutemsg.php  $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

define (DEFAULT_MSG, -1);
define (CONGESTION_TONE, -2);

$dispnum = 'outroutemsg'; //used for switch on config.php
$tabindex = 0;

$action  = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$type  = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'tool';
$tresults = recordings_list();

// do if we are submitting a form
if($action){
	$default_msg_id      = isset($_REQUEST['default_msg_id'])      ? trim($_REQUEST['default_msg_id'])      : DEFAULT_MSG;
	$intracompany_msg_id = isset($_REQUEST['intracompany_msg_id']) ? trim($_REQUEST['intracompany_msg_id']) : DEFAULT_MSG;
	$emergency_msg_id    = isset($_REQUEST['emergency_msg_id'])    ? trim($_REQUEST['emergency_msg_id'])    : DEFAULT_MSG;
	$unallocated_msg_id  = isset($_REQUEST['unallocated_msg_id'])  ? trim($_REQUEST['unallocated_msg_id'])  : DEFAULT_MSG;
	$no_answer_msg_id    = isset($_REQUEST['no_answer_msg_id'])    ? trim($_REQUEST['no_answer_msg_id'])    : DEFAULT_MSG;	
	$invalidnmbr_msg_id  = isset($_REQUEST['invalidnmbr_msg_id'])  ? trim($_REQUEST['invalidnmbr_msg_id'])  : DEFAULT_MSG;

	if ($action == 'submit') {
		outroutemsg_add($default_msg_id, $intracompany_msg_id, $emergency_msg_id, $no_answer_msg_id, $invalidnmbr_msg_id, $unallocated_msg_id);
		needreload();
	}
}


// get the outroutemsg settings if not a submit
//
if ($action != 'submit') {
	$outroutemsg_settings = outroutemsg_get();
	$default_msg_id      = $outroutemsg_settings['default_msg_id'];
	$intracompany_msg_id = $outroutemsg_settings['intracompany_msg_id'];
	$emergency_msg_id    = $outroutemsg_settings['emergency_msg_id'];
	$unallocated_msg_id  = $outroutemsg_settings['unallocated_msg_id'];
	$no_answer_msg_id    = $outroutemsg_settings['no_answer_msg_id'];
	$invalidnmbr_msg_id  = $outroutemsg_settings['invalidnmbr_msg_id'];
}

?>
<h2><?php echo _("Route Congestion Messages")?></h2>
<h4><?php echo _("No Routes Available")?></h4>
<form name="outroutemsg" action="config.php" method="post">
<input type="hidden" name="display" value="<?php echo $dispnum ?>"/>
<input type="hidden" name="action" value="submit"/>
<table>
<tr><td colspan="2"><h5><?php echo _("Standard Routes")?></h5></td></tr>
<tr>
	<td><a href="#" class="info"><?php echo _("Message or Tone")?><span><?php echo _("Message or tone to be played if no trunks are available.")?></span></a></td>
	<td align=right>
		<select name="default_msg_id" id="default_msg_id" tabindex="<?php echo ++$tabindex;?>">
		<?php
			echo '<option value="'.DEFAULT_MSG.'"'.(DEFAULT_MSG == $default_msg_id ? ' SELECTED' : '').'>'._("Default Message")."</option>\n";
			echo '<option value="'.CONGESTION_TONE.'"'.(CONGESTION_TONE == $default_msg_id ? ' SELECTED' : '').'>'._("Congestion Tones")."</option>\n";
			echo '<option value="'.INFO_TONE.'"'.(INFO_TONE == $default_msg_id ? ' SELECTED' : '').'>'._("Info Tone")."</option>\n";
			if (isset($tresults[0])) {
				foreach ($tresults as $tresult) {
					echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $default_msg_id ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
				}
			}
		?>
		</select>
	</td>
</tr>

<tr><td colspan=2><hr/></td></tr>

<tr><td colspan="2"><h5><?php echo _("Intra-Company Routes")?></h5></td></tr>
<tr>
	<td><a href="#" class="info"><?php echo _("Message or Tone")?><span><?php echo _("Message or tone to be played if no trunks are available. Used on routes marked as intra-company only.")?></span></a></td>
	<td align=right>
		<select name="intracompany_msg_id" id="intracompany_msg_id" tabindex="<?php echo ++$tabindex;?>">
		<?php
			echo '<option value="'.DEFAULT_MSG.'"'.(DEFAULT_MSG == $intracompany_msg_id ? ' SELECTED' : '').'>'._("Default Message")."</option>\n";
			echo '<option value="'.CONGESTION_TONE.'"'.(CONGESTION_TONE == $intracompany_msg_id ? ' SELECTED' : '').'>'._("Congestion Tones")."</option>\n";
			echo '<option value="'.INFO_TONE.'"'.(INFO_TONE == $intracompany_msg_id ? ' SELECTED' : '').'>'._("Info Tone")."</option>\n";
			if (isset($tresults[0])) {
				foreach ($tresults as $tresult) {
					echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $intracompany_msg_id ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
				}
			}
		?>
		</select>
	</td>
</tr>

<tr><td colspan=2><hr/></td></tr>

<tr><td colspan="2"><h5><?php echo _("Emergency Routes")?></h5></td></tr>
<tr>
	<td><a href="#" class="info"><?php echo _("Message or Tone")?><span><?php echo _("Message or tone to be played if no trunks are available. Used on all emergency routes. Consider a message instructing callers to find an alternative means of calling emergency services such as a cell phone or alarm system panel.")?></span></a></td>
	<td align=right>
		<select name="emergency_msg_id" id="emergency_msg_id" tabindex="<?php echo ++$tabindex;?>">
		<?php
			echo '<option value="'.DEFAULT_MSG.'"'.(DEFAULT_MSG == $emergency_msg_id ? ' SELECTED' : '').'>'._("Default Message")."</option>\n";
			echo '<option value="'.CONGESTION_TONE.'"'.(CONGESTION_TONE == $emergency_msg_id ? ' SELECTED' : '').'>'._("Congestion Tones")."</option>\n";
			echo '<option value="'.INFO_TONE.'"'.(INFO_TONE == $emergency_msg_id ? ' SELECTED' : '').'>'._("Info Tone")."</option>\n";
			if (isset($tresults[0])) {
				foreach ($tresults as $tresult) {
					echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $emergency_msg_id ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
				}
			}
		?>
		</select>
	</td>
</tr>

<tr><td colspan=2><hr/></td></tr>

<tr><td colspan="2"><br><h4><?php echo _("Trunk Failures")?></h4></td></tr>

<tr><td colspan="2"><h5><?php echo _("Unallocated Numbers")?></h5></td></tr>
<tr>
	<td><a href="#" class="info"><?php echo _("Message or Tone")?><span><?php echo _("Message or tone to be played if destination number is unallocated/does not exists.")?></span></a></td>
	<td align=right>
		<select name="unallocated_msg_id" id="unallocated_msg_id" tabindex="<?php echo ++$tabindex;?>">
		<?php
			echo '<option value="'.DEFAULT_MSG.'"'.(DEFAULT_MSG == $unallocated_msg_id ? ' SELECTED' : '').'>'._("Default Message")."</option>\n";
			echo '<option value="'.CONGESTION_TONE.'"'.(CONGESTION_TONE == $unallocated_msg_id ? ' SELECTED' : '').'>'._("Congestion Tones")."</option>\n";
			echo '<option value="'.INFO_TONE.'"'.(INFO_TONE == $unallocated_msg_id ? ' SELECTED' : '').'>'._("Info Tone")."</option>\n";
			if (isset($tresults[0])) {
				foreach ($tresults as $tresult) {
					echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $unallocated_msg_id ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
				}
			}
		?>
		</select>
	</td>
</tr>

<tr><td colspan=2><hr/></td></tr>

<tr><td colspan="2"><h5><?php echo _("No Answer")?></h5></td></tr>
<tr>
	<td><a href="#" class="info"><?php echo _("Message or Tone")?><span><?php echo _("Message or tone to be played if there was no answer. Default message is:<br>\"The number is not answering.\"<br> Hangupcause is 18 or 19")?></span></a></td>
	<td align=right>
		<select name="no_answer_msg_id" id="no_answer_msg_id" tabindex="<?php echo ++$tabindex;?>">
		<?php
			echo '<option value="'.DEFAULT_MSG.'"'.(DEFAULT_MSG == $no_answer_msg_id ? ' SELECTED' : '').'>'._("Default Message")."</option>\n";
			echo '<option value="'.CONGESTION_TONE.'"'.(CONGESTION_TONE == $no_answer_msg_id ? ' SELECTED' : '').'>'._("Congestion Tones")."</option>\n";
			if (isset($tresults[0])) {
				foreach ($tresults as $tresult) {
					echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $no_answer_msg_id ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
				}
			}
		?>
		</select>
	</td>
</tr>

<tr><td colspan=2><hr/></td></tr>

<tr><td colspan="2"><h5><?php echo _("Number or Address Incomplete")?></h5></td></tr>
<tr>
	<td><a href="#" class="info"><?php echo _("Message or Tone")?><span><?php echo _("Message or tone to be played if trunk reports Number or Address Incomplete. Usually this means that the number you have dialed is to short. Default message is:<br>\"The number you have dialed is not in service. Please check the number and try again.\"<br>Hangupcause is 28")?></span></a></td>
	<td align=right>
		<select name="invalidnmbr_msg_id" id="invalidnmbr_msg_id" tabindex="<?php echo ++$tabindex;?>">
		<?php
			echo '<option value="'.DEFAULT_MSG.'"'.(DEFAULT_MSG == $invalidnmbr_msg_id ? ' SELECTED' : '').'>'._("Default Message")."</option>\n";
			echo '<option value="'.CONGESTION_TONE.'"'.(CONGESTION_TONE == $invalidnmbr_msg_id ? ' SELECTED' : '').'>'._("Congestion Tones")."</option>\n";
			if (isset($tresults[0])) {
				foreach ($tresults as $tresult) {
					echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $invalidnmbr_msg_id ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
				}
			}
		?>
		</select>
	</td>
</tr>

<tr><td colspan=2><hr/></td></tr>

<tr>
	<td colspan="2"><br><h6><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>"></h6></td>
</tr>

</table>

</form>
