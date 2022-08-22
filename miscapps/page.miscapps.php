<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2006-2014 Schmooze Com Inc.
//  Copyright 2022 Issabel Foundation

$tabindex = 0;

$action      = isset($_POST['action']) ? $_POST['action']             : '';
$miscapp_id  = isset($_POST['miscapp_id']) ? $_POST['miscapp_id']     : false;
$description = isset($_POST['description']) ? $_POST['description']   : '';
$ext         = isset($_POST['ext']) ? $_POST['ext']                   : '';
$dest        = isset($_POST['dest']) ? $_POST['dest']                 : '';
$enabled     = isset($_POST['enabled']) ? (!empty($_POST['enabled'])) : true;

if (isset($_POST['delete'])) $action = 'delete'; 

if (isset($_POST['goto0']) && $_POST['goto0']) {
	$dest = $_POST[ $_POST['goto0'] ];
}

switch ($action) {
	case 'add':
		$conflict_url = array();
		$usage_arr = framework_check_extension_usage($ext);
		if (!empty($usage_arr)) {
			$conflict_url = framework_display_extension_usage_alert($usage_arr);
		} else {
			miscapps_add($description, $ext, $dest);
            needreload();
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
            $_SESSION['msgtype']='success';
			redirect_standard();
		}
	break;
	case 'edit':
		$fc = new featurecode('miscapps', 'miscapp_'.$miscapp_id);
		$conflict_url = array();
		if ($fc->getDefault() != $ext) {
			$usage_arr = framework_check_extension_usage($ext);
			if (!empty($usage_arr)) {
				$conflict_url = framework_display_extension_usage_alert($usage_arr);
			}
		}
		if (empty($conflict_url)) {
			miscapps_edit($miscapp_id, $description, $ext, $dest, $enabled);
			needreload();
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
            $_SESSION['msgtype']='success';
			redirect_standard('extdisplay');
		}
	break;
	case 'delete':
		miscapps_delete($miscapp_id);
		needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
		redirect_standard();
	break;
}

$rnaventries = array();
$data        = miscapps_list();
foreach ($data as $idx=>$row) {
    $rnaventries[] = array($row['miscapps_id'],$row['description'],'');
}
drawListMenu($rnaventries, $type, $display, $extdisplay);
?> 

<div class='content'>
<?php

$helptext = _("Misc Applications are for adding feature codes that you can dial from internal phones that go to various destinations available in IssabelPBX. This is in contrast to the <strong>Misc Destinations</strong> module, which is for creating destinations that can be used by other IssabelPBX modules to dial internal numbers or feature codes.");
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

if ($extdisplay) {
	// load
	$row = miscapps_get($extdisplay);
	
	$description = $row['description'];
	$ext         = $row['ext'];
	$dest        = $row['dest'];
	$enabled     = $row['enabled'];

    echo "<div class='is-flex'><h2>"._("Edit Misc Application").": ".$description."</h2>$help</div>";

} else {
	echo "<div class='is-flex'><h2>"._("Add Misc Application")."</h2>$help</div>";
}

if (!empty($conflict_url)) {
    echo ipbx_extension_conflict($conflict_url);
}
?>

<form name="editMiscapp" id="mainform" method="post" onsubmit="return checkMiscapp(editMiscapp);">
	<input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
	<input type="hidden" name="miscapp_id" value="<?php echo $extdisplay; ?>">
	<input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">

    <table class='table is-borderless is-narrow'>
    <tr><td colspan="2"><h5><?php  echo dgettext('amp','General Settings') ?></h5></td></tr>

	<tr>
        <td>
            <a href="#" class="info"><?php echo _("Description")?>:<span><?php echo _("The name of this application")?></span></a>
        </td>
        <td>
            <input class='input w100' autofocus type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>">
        </td>
    </tr>

	<tr>
        <td>
            <a href="#" class="info"><?php echo _("Feature Code")?>:<span><?php echo _("The feature code/extension users can dial to access this application. This can also be modified on the Feature Codes page.")?></span></a>
        </td>
        <td>
            <input type="text" class="extdisplay input w100" name="ext" value="<?php echo $ext; ?>"  tabindex="<?php echo ++$tabindex;?>"/>
        </td>
    </tr>

	<tr>
        <td>
            <a href="#" class="info"><?php echo _("Feature Status")?>:<span><?php echo _("If this code is enabled or not.")?></span></a>
        </td>
        <td>
            <select name="enabled" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
			   <option value="1" <?php if ($enabled) echo "SELECTED"; ?>><?php echo _("Enabled");?></option>
			   <option value="0" <?php if (!$enabled) echo "SELECTED"; ?>><?php echo _("Disabled");?></option>
		    </select>
        </td>
	</tr>
	
	<tr><td colspan="2"><br><h5><?php echo _("Destination")?></h5></td></tr>

<?php 
//draw goto selects
echo drawselects($dest,0);
?>
			
	</table>
</form>
			
<script>

function checkMiscapp(theForm) {
	var msgInvalidDescription = "<?php echo _('Invalid description specified'); ?>";

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
</div>
<?php echo form_action_bar($extdisplay); ?>
