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
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        redirect_standard();
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
<div class='content'>
<h2><?php echo __("Route Congestion Messages")?></h2>
<form id="mainform" name="outroutemsg" action="config.php" method="post" onsubmit="return edit_onsubmit(this)">
<input type="hidden" name="display" value="<?php echo $dispnum ?>"/>
<input type="hidden" name="action" value="submit"/>
<table>
<tr><td colspan="2"><br><h4><?php echo __("No Routes Available")?></h4></td></tr>
<tr><td colspan="2"><h5><?php echo __("Standard Routes")?></h5></td></tr>
<tr>
	<td><a href="#" class="info"><?php echo __("Message or Tone")?><span><?php echo __("Message or tone to be played if no trunks are available.")?></span></a></td>
	<td align=right>
		<select name="default_msg_id" id="default_msg_id" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
		<?php
			echo '<option value="'.DEFAULT_MSG.'"'.(DEFAULT_MSG == $default_msg_id ? ' SELECTED' : '').'>'.__("Default Message")."</option>\n";
			echo '<option value="'.CONGESTION_TONE.'"'.(CONGESTION_TONE == $default_msg_id ? ' SELECTED' : '').'>'.__("Congestion Tones")."</option>\n";
			echo '<option value="'.INFO_TONE.'"'.(INFO_TONE == $default_msg_id ? ' SELECTED' : '').'>'.__("Info Tone")."</option>\n";
			if (isset($tresults[0])) {
				foreach ($tresults as $tresult) {
					echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $default_msg_id ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
				}
			}
		?>
		</select>
	</td>
</tr>

<tr><td colspan="2"><h5><?php echo __("Intra-Company Routes")?></h5></td></tr>
<tr>
	<td><a href="#" class="info"><?php echo __("Message or Tone")?><span><?php echo __("Message or tone to be played if no trunks are available. Used on routes marked as intra-company only.")?></span></a></td>
	<td align=right>
		<select name="intracompany_msg_id" id="intracompany_msg_id" tabindex="<?php echo ++$tabindex;?>"  class='componentSelect'>
		<?php
			echo '<option value="'.DEFAULT_MSG.'"'.(DEFAULT_MSG == $intracompany_msg_id ? ' SELECTED' : '').'>'.__("Default Message")."</option>\n";
			echo '<option value="'.CONGESTION_TONE.'"'.(CONGESTION_TONE == $intracompany_msg_id ? ' SELECTED' : '').'>'.__("Congestion Tones")."</option>\n";
			echo '<option value="'.INFO_TONE.'"'.(INFO_TONE == $intracompany_msg_id ? ' SELECTED' : '').'>'.__("Info Tone")."</option>\n";
			if (isset($tresults[0])) {
				foreach ($tresults as $tresult) {
					echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $intracompany_msg_id ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
				}
			}
		?>
		</select>
	</td>
</tr>


<tr><td colspan="2"><h5><?php echo __("Emergency Routes")?></h5></td></tr>
<tr>
	<td><a href="#" class="info"><?php echo __("Message or Tone")?><span><?php echo __("Message or tone to be played if no trunks are available. Used on all emergency routes. Consider a message instructing callers to find an alternative means of calling emergency services such as a cell phone or alarm system panel.")?></span></a></td>
	<td align=right>
		<select name="emergency_msg_id" id="emergency_msg_id" tabindex="<?php echo ++$tabindex;?>"  class='componentSelect'>
		<?php
			echo '<option value="'.DEFAULT_MSG.'"'.(DEFAULT_MSG == $emergency_msg_id ? ' SELECTED' : '').'>'.__("Default Message")."</option>\n";
			echo '<option value="'.CONGESTION_TONE.'"'.(CONGESTION_TONE == $emergency_msg_id ? ' SELECTED' : '').'>'.__("Congestion Tones")."</option>\n";
			echo '<option value="'.INFO_TONE.'"'.(INFO_TONE == $emergency_msg_id ? ' SELECTED' : '').'>'.__("Info Tone")."</option>\n";
			if (isset($tresults[0])) {
				foreach ($tresults as $tresult) {
					echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $emergency_msg_id ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
				}
			}
		?>
		</select>
	</td>
</tr>


<tr><td colspan="2"><br><h4><?php echo __("Trunk Failures")?></h4></td></tr>

<tr><td colspan="2"><h5><?php echo __("Unallocated Numbers")?></h5></td></tr>
<tr>
	<td><a href="#" class="info"><?php echo __("Message or Tone")?><span><?php echo __("Message or tone to be played if destination number is unallocated/does not exists.")?></span></a></td>
	<td align=right>
		<select name="unallocated_msg_id" id="unallocated_msg_id" tabindex="<?php echo ++$tabindex;?>"  class='componentSelect'>
		<?php
			echo '<option value="'.DEFAULT_MSG.'"'.(DEFAULT_MSG == $unallocated_msg_id ? ' SELECTED' : '').'>'.__("Default Message")."</option>\n";
			echo '<option value="'.CONGESTION_TONE.'"'.(CONGESTION_TONE == $unallocated_msg_id ? ' SELECTED' : '').'>'.__("Congestion Tones")."</option>\n";
			echo '<option value="'.INFO_TONE.'"'.(INFO_TONE == $unallocated_msg_id ? ' SELECTED' : '').'>'.__("Info Tone")."</option>\n";
			if (isset($tresults[0])) {
				foreach ($tresults as $tresult) {
					echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $unallocated_msg_id ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
				}
			}
		?>
		</select>
	</td>
</tr>


<tr><td colspan="2"><h5><?php echo __("No Answer")?></h5></td></tr>
<tr>
	<td><a href="#" class="info"><?php echo __("Message or Tone")?><span><?php echo __("Message or tone to be played if there was no answer. Default message is:<br>\"The number is not answering.\"<br> Hangupcause is 18 or 19")?></span></a></td>
	<td align=right>
		<select name="no_answer_msg_id" id="no_answer_msg_id" tabindex="<?php echo ++$tabindex;?>"  class='componentSelect'>
		<?php
			echo '<option value="'.DEFAULT_MSG.'"'.(DEFAULT_MSG == $no_answer_msg_id ? ' SELECTED' : '').'>'.__("Default Message")."</option>\n";
			echo '<option value="'.CONGESTION_TONE.'"'.(CONGESTION_TONE == $no_answer_msg_id ? ' SELECTED' : '').'>'.__("Congestion Tones")."</option>\n";
			if (isset($tresults[0])) {
				foreach ($tresults as $tresult) {
					echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $no_answer_msg_id ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
				}
			}
		?>
		</select>
	</td>
</tr>


<tr><td colspan="2"><h5><?php echo __("Number or Address Incomplete")?></h5></td></tr>
<tr>
	<td><a href="#" class="info"><?php echo __("Message or Tone")?><span><?php echo __("Message or tone to be played if trunk reports Number or Address Incomplete. Usually this means that the number you have dialed is to short. Default message is:<br>\"The number you have dialed is not in service. Please check the number and try again.\"<br>Hangupcause is 28")?></span></a></td>
	<td align=right>
		<select name="invalidnmbr_msg_id" id="invalidnmbr_msg_id" tabindex="<?php echo ++$tabindex;?>"  class='componentSelect'>
		<?php
			echo '<option value="'.DEFAULT_MSG.'"'.(DEFAULT_MSG == $invalidnmbr_msg_id ? ' SELECTED' : '').'>'.__("Default Message")."</option>\n";
			echo '<option value="'.CONGESTION_TONE.'"'.(CONGESTION_TONE == $invalidnmbr_msg_id ? ' SELECTED' : '').'>'.__("Congestion Tones")."</option>\n";
			if (isset($tresults[0])) {
				foreach ($tresults as $tresult) {
					echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $invalidnmbr_msg_id ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
				}
			}
		?>
		</select>
	</td>
</tr>


</table>

</form>
<script>
function edit_onsubmit(theForm) {
    $.LoadingOverlay('show','mainform',true,true);
}
<?php echo js_display_confirmation_toasts(); ?>
</script>
</div>
<?php echo form_action_bar(''); ?>

