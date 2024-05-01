<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
$tabindex = 0;
$type        = isset($_REQUEST['type'])        ? $_REQUEST['type']        : 'setup';
$action      = isset($_REQUEST['action'])      ? $_REQUEST['action']      : '';
$language_id = isset($_REQUEST['language_id']) ? $_REQUEST['language_id'] :  false;
$description = isset($_REQUEST['description']) ? $_REQUEST['description'] : '';
$lang_code   = isset($_REQUEST['lang_code'])   ? $_REQUEST['lang_code']   : '';
$dest        = isset($_REQUEST['dest'])        ? $_REQUEST['dest']        : '';
if (isset($_REQUEST['delete'])) $action = 'delete'; 

if (isset($_REQUEST['goto0']) && $_REQUEST['goto0']) {
	$dest = $_REQUEST[ $_REQUEST['goto0'] ];
}

switch ($action) {
	case 'add':
		$_REQUEST['extdisplay'] = languages_add($description, $lang_code, $dest);
        needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been added'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
		redirect_standard('extdisplay');
	break;
	case 'edit':
		languages_edit($language_id, $description, $lang_code, $dest);
        needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
		redirect_standard('extdisplay');
	break;
	case 'delete':
		languages_delete($language_id);
        needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        $_SESSION['msgtstamp']=time();
		redirect_standard();
	break;
}

$rnavitems = array();
$languages = languages_list();
foreach ($languages as $row) {
    $rnavitems[]=array($row['language_id'],$row['description'],'','');
}
drawListMenu($rnavitems, $type, $display, $extdisplay);
?> 
<div class='content'>
<?php

if ($extdisplay) {
	// load
	$row = languages_get($extdisplay);
	
	$description = $row['description'];
	$lang_code   = $row['lang_code'];
	$dest        = $row['dest'];

}

$helptext = __("Languages allow you to change the language of the call flow and then continue on to the desired destination. For example, you may have an IVR option that says \"For French Press 5 now\". You would then create a French language instance and point it's destination at a French IVR. The language of the call's channel will now be in French. This will result in French sounds being chosen if installed.");
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';
echo "<div class='is-flex'><h2>".($extdisplay ? __('Edit Language').': '.$description.' ('.$lang_code.')': __("Add Language"))."</h2>$help</div>\n";

if ($extdisplay) {
    $usage_list = framework_display_destination_usage(languages_getdest($extdisplay));
    if (!empty($usage_list)) {
        echo ipbx_usage_info($usage_list['text'],$usage_list['tooltip']);
    }
}

?>

<form id="mainform" name="editLanguage" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkLanguage(this);">
	<input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
	<input type="hidden" name="language_id" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
    <table class='table is-borderless is-narrow'>
    <tr><td colspan="2"><h5><?php echo _dgettext('amp','General Settings');?></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Description")?><span><?php echo __("The descriptive name of this language instance. For example \"French Main IVR\"")?></span></a></td>
		<td><input autofocus class='input w100' type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Language Code")?><span><?php echo __("The Asterisk language code you want to change to. For example \"fr\" for French, \"de\" for German")?></span></a></td>
		<td><input class="input w100" type="text" name="lang_code" value="<?php echo $lang_code; ?>"  tabindex="<?php echo ++$tabindex;?>"/></td> </tr>
	<tr><td colspan="2"><br><h5><?php echo __("Destination")?></h5></td></tr>

<?php 
//draw goto selects
echo drawselects($dest,0);
?>
			
</table>
</form>

<script>

function checkLanguage(theForm) {
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
