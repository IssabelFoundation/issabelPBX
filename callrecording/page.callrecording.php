<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//  License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Copyright 2022 Issabel Foundation

$tabindex = 0;
$type               = isset($_REQUEST['type'])               ? $_REQUEST['type']               :'setup';
$action             = isset($_REQUEST['action'])             ? $_REQUEST['action']             : '';
$callrecording_id   = isset($_REQUEST['callrecording_id'])   ? $_REQUEST['callrecording_id']   : false;
$description        = isset($_REQUEST['description'])        ? $_REQUEST['description']        : '';
$callrecording_mode = isset($_REQUEST['callrecording_mode']) ? $_REQUEST['callrecording_mode'] : '';
$dest               = isset($_REQUEST['dest'])               ? $_REQUEST['dest']               : '';

if (isset($_REQUEST['delete'])) $action = 'delete'; 

if (isset($_REQUEST['goto0']) && $_REQUEST['goto0']) {
	$dest = $_REQUEST[ $_REQUEST['goto0'] ];
}

switch ($action) {
	case 'add':
		$_REQUEST['extdisplay'] = callrecording_add($description, $callrecording_mode, $dest);
        needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been added'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
		redirect_standard();
	break;
	case 'edit':
		callrecording_edit($callrecording_id, $description, $callrecording_mode, $dest);
        needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
		redirect_standard('extdisplay');
	break;
	case 'delete':
		callrecording_delete($callrecording_id);
        needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        $_SESSION['msgtstamp']=time();
		redirect_standard();
	break;
}

$rnavitems = array();
$callrec   = callrecording_list();
foreach ($callrec as $row) {
    $rnavitems[]=array($row['callrecording_id'],$row['description'],'','');
}
drawListMenu($rnavitems, $type, $display, $extdisplay);
?>
<div class='content'>
<?php
if ($extdisplay) {
	// load
	$row = callrecording_get($extdisplay);
	
	$description        = $row['description'];
	$callrecording_mode = $row['callrecording_mode'];
	$dest               = $row['dest'];
	$cm_disp = $callrecording_mode ? $callrecording_mode : 'allow';
}

$helptext = __("Call Recordings provide the ability to force a call to be recorded or not recorded based on a call flow and override all other recording settings. If a call is to be recorded, it can start immediately which will incorporate any announcements, hold music, etc. prior to being answered, or it can have recording start at the time that call is answered.");
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

echo "<div class='is-flex'><h2>".($extdisplay ? __('Edit Call Recording').': '.$description : __("Add Call Recording"))."</h2>$help</div>\n";

if ($extdisplay) {
    $usage_list = framework_display_destination_usage(callrecording_getdest($extdisplay));
    if (!empty($usage_list)) {
        echo ipbx_usage_info($usage_list['text'],$usage_list['tooltip']);
    }
}
?>

<form id="mainform" name="editCallRecording" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkCallRecording(this);">
	<input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
	<input type="hidden" name="callrecording_id" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
    <table class='table is-borderless is-narrow'>
    <tr><td colspan="2"><h5><?php echo _dgettext('amp','General Settings');?></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Description")?><span><?php echo __("The descriptive name of this call recording instance. For example \"French Main IVR\"")?></span></a></td>
		<td><input autofocus class="input w100" type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>


	<tr>
    <td><a href="#" class="info"><?php echo __("Call Recording Mode")?><span><?php echo __("Controls or overrides the call recording behavior for calls continuing through this call flow. Allow will honor the normal downstream call recording settings. Record on Answer starts recording when the call would otherwise be recorded ignoring any settings that say otherwise. Record Immediately will start recording right away capturing ringing, announcements, MoH, etc. Never will disallow recording regardless of downstream settings.")?></span></a></td>
<?php
	$callrecording_html = '<td><select class="componentSelect" name="callrecording_mode" tabindex="' . ++$tabindex . '">'."\n";
    $callrecording_html.= '<option value=""' . ($callrecording_mode == ''  ? ' SELECTED' : '').'>'.__("Allow")."\n";
    $callrecording_html.= '<option value="delayed"'. ($callrecording_mode == 'delayed' ? ' SELECTED' : '').'>'.__("Record on Answer")."\n";
    $callrecording_html.= '<option value="force"'  . ($callrecording_mode == 'force'   ? ' SELECTED' : '').'>'.__("Record Immediately")."\n";
    $callrecording_html.= '<option value="never"' . ($callrecording_mode == 'never'  ? ' SELECTED' : '').'>'.__("Never")."\n";
    $callrecording_html.= "</select></td></tr>\n";
    echo $callrecording_html;
?>
	<tr><td colspan="2"><br><h5><?php echo __("Destination")?></h5></td></tr>

<?php 
//draw goto selects
echo drawselects($dest,0);
?>
			
</table>
</form>

<script>

function checkCallRecording(theForm) {
	var msgInvalidDescription = "<?php echo __('Invalid description specified'); ?>";

	// set up the Destination stuff
	setDestinations(theForm, '_post_dest');

	// form validation
	defaultEmptyOK = false;	
	if (isEmpty(theForm.description.value))
		return warnInvalid(theForm.description, msgInvalidDescription);

	if (!validateDestinations(theForm, 1, true))
		return false;

    $.LoadingOverlay('show');
	return true;
}
<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
