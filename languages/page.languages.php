<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'setup';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] :  '';
if (isset($_REQUEST['delete'])) $action = 'delete'; 

$language_id = isset($_REQUEST['language_id']) ? $_REQUEST['language_id'] :  false;
$description = isset($_REQUEST['description']) ? $_REQUEST['description'] :  '';
$lang_code = isset($_REQUEST['lang_code']) ? $_REQUEST['lang_code'] :  '';
$dest = isset($_REQUEST['dest']) ? $_REQUEST['dest'] :  '';

if (isset($_REQUEST['goto0']) && $_REQUEST['goto0']) {
	$dest = $_REQUEST[ $_REQUEST['goto0'].'0' ];
}

switch ($action) {
	case 'add':
		$_REQUEST['extdisplay'] = languages_add($description, $lang_code, $dest);
		needreload();
		redirect_standard('extdisplay');
	break;
	case 'edit':
		languages_edit($language_id, $description, $lang_code, $dest);
		needreload();
		redirect_standard('extdisplay');
	break;
	case 'delete':
		languages_delete($language_id);
		needreload();
		redirect_standard();
	break;
}

?> 

<div class="rnav"><ul>
<?php 

echo '<li><a href="config.php?display=languages&amp;type='.$type.'">'._('Add Language').'</a></li>';

foreach (languages_list() as $row) {
	echo '<li><a href="config.php?display=languages&amp;type='.$type.'&amp;extdisplay='.$row['language_id'].'" class="">'.$row['description'].'</a></li>';
}

?>
</ul></div>

<?php

if ($extdisplay) {
	// load
	$row = languages_get($extdisplay);
	
	$description = $row['description'];
	$lang_code   = $row['lang_code'];
	$dest        = $row['dest'];

	echo "<h2>"._("Edit: ")."$description ($lang_code)"."</h2>";
} else {
	echo "<h2>"._("Add Language")."</h2>";
}

$helptext = _("Languages allow you to change the language of the call flow and then continue on to the desired destination. For example, you may have an IVR option that says \"For French Press 5 now\". You would then create a French language instance and point it's destination at a French IVR. The language of the call's channel will now be in French. This will result in French sounds being chosen if installed.");
echo $helptext;
?>

<form name="editLanguage" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkLanguage(editLanguage);">
	<input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
	<input type="hidden" name="language_id" value="<?php echo $extdisplay; ?>">
	<input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
	<table>
	<tr><td colspan="2"><h5><?php  echo ($extdisplay ? _("Edit Language Instance") : _("Add Language Instance")) ?><hr></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Description")?>:<span><?php echo _("The descriptive name of this language instance. For example \"French Main IVR\"")?></span></a></td>
		<td><input size="30" type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Language Code")?>:<span><?php echo _("The Asterisk language code you want to change to. For example \"fr\" for French, \"de\" for German")?></span></a></td>
		<td><input size="14" type="text" name="lang_code" value="<?php echo $lang_code; ?>"  tabindex="<?php echo ++$tabindex;?>"/></td> </tr>
	<tr><td colspan="2"><br><h5><?php echo _("Destination")?>:<hr></h5></td></tr>

<?php 
//draw goto selects
echo drawselects($dest,0);
?>
			
	<tr>
		<td colspan="2"><br><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>">
			<?php if ($extdisplay) { echo '&nbsp;<input name="delete" type="submit" value="'._("Delete").'">'; } ?>
		</td>		

		<?php
		if ($extdisplay) {
			$usage_list = framework_display_destination_usage(languages_getdest($extdisplay));
			if (!empty($usage_list)) {
			?>
				<tr><td colspan="2">
				<a href="#" class="info"><?php echo $usage_list['text']?>:<span><?php echo $usage_list['tooltip']?></span></a>
				</td></tr>
			<?php
			}
		}
		?>
	</tr>
</table>
</form>

<script language="javascript">
<!--

function checkLanguage(theForm) {
	var msgInvalidDescription = "<?php echo _('Invalid description specified'); ?>";

	// set up the Destination stuff
	setDestinations(theForm, '_post_dest');

	// form validation
	defaultEmptyOK = false;	
	if (isEmpty(theForm.description.value))
		return warnInvalid(theForm.description, msgInvalidDescription);

	if (!validateDestinations(theForm, 1, true))
		return false;

	return true;
}
//-->
</script>
