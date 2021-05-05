<?php /* $Id: $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed');}
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//  Xavier Ourciere xourciere[at]propolys[dot]com
//

// This module REQUIRES the 'ttsengines' module. But as IssabelPBX
// doesn't handle circular dependancies, we have to force one.
// This is the one that's forced. Sorry.
if (!function_exists('ttsengines_get_all_engines')) {
	print "<h2>"._("Text To Speech")."<br/><hr></h2>";
	print "<p>TTS Requires that the ttsmodules engines be installed, and it doesn't appear to be. Sorry!</p>";
	return;
}

isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';
isset($_REQUEST['id'])?$ttsid = $_REQUEST['id']:$ttsid='';

if (isset($_REQUEST['goto0']) && isset($_REQUEST[$_REQUEST['goto0']."0"])) {
	$goto = $_REQUEST[$_REQUEST['goto0']."0"];
} else {
	$goto = '';
}

$dispnum = "tts"; //used for switch on config.php

switch ($action) {
	case "add":
		tts_add($_REQUEST['name'], $_REQUEST['text'], $goto, $_REQUEST['engine']);
		needreload();
	break;
	case "delete":
		tts_del($ttsid);
		needreload();
	break;
	case "edit":
		tts_update($ttsid, $_REQUEST['name'], $_REQUEST['text'], $goto, $_REQUEST['engine']);
		needreload();
	break;
}

//this function needs to be available to other modules (those that use goto destinations)
//therefore we put it in globalfunctions.php
$tts_list = tts_list();
?>
<div class="rnav">
<ul>
    <li><a id="<?php echo ($ttsid=='' ? 'current':'') ?>" href="config.php?display=<?php echo urlencode($dispnum)?>"><?php echo _("Add a Text To Speech item")?></a></li>
<?php
if (isset($tts_list)) {
	foreach ($tts_list as $item) {
		echo "<li><a id=\"".($ttsid==$item['id'] ? 'current':'')."\" href=\"config.php?display=".urlencode($dispnum)."&id=".urlencode($item['id'])."\">{$item['name']}</a></li>";
	}
}
?>
</ul>
</div>
<?php
if ($action == 'delete') {
	echo '<br><h3>'._("Text To Speech").' '.$ttsid.' '._("deleted").'!</h3><br><br><br><br><br><br><br><br>';
} else {
	if ($ttsid){
		//get details for this tts text
		$thisTTS = tts_get($ttsid);
		//create variables
		extract($thisTTS);
	}
	$delURL = '?'.$_SERVER['QUERY_STRING'].'&action=delete';
?>


<?php		if ($ttsid){ ?>
	<h2><?php echo _("Text To Speech").": ". $name; ?></h2>
	<p><a href="<?php echo $delURL ?>"><?php echo _("Delete text to speech")?> '<?php echo $name; ?>'</a><i style='font-size: x-small'>(<?php echo _("Note, does not delete the files from the server.")?><?php echo $tts_astsnd_path; ?>)</i></p>
<?php		} else { ?>
	<h2><?php echo _("Add a Text To Speech item"); ?></h2>
	<p></p>
<?php		}
?>
	<form class="popover-form" autocomplete="off" name="editTTS" action="" method="post" return editTTS_submit();">
	<input type="hidden" name="display" value="<?php echo $dispnum?>">
	<input type="hidden" name="action" value="<?php echo ($ttsid ? 'edit' : 'add') ?>">
	<table>
	<tr><td colspan="2"><h5><?php echo _("Main settings"); ?>:</h5></td></tr>
<?php		if ($ttsid){ ?>
		<tr><td><input type="hidden" name="id" value="<?php echo $ttsid; ?>"></td></tr>
<?php		} ?>
	<tr>
		<td><a href="#" class="info"><?php echo _("Name")?>:<span><?php echo _("Give this TTS Destination a brief name to help you identify it.")?></span></a></td>
		<td><input type="text" name="name" value="<?php echo (isset($name) ? $name : ''); ?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Text")?>:<span><?php echo _("Enter the text you want to synthetize.")?></span></a></td>
		<td><textarea name="text" cols=50 rows=5><?php echo (isset($text) ? $text : ''); ?></textarea></td>
	</tr>

	<tr><td colspan="2"><br><h5><?php echo _("TTS Engine")?>:</h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Choose an engine")?>:<span><?php echo _("List of TTS engines detected on the server. Choose the one you want to use for the current sentence.")?></span></a></td>
		<td>
		<?php if( !isset($tts_agi_error) ) { ?>
			<select name="engine">
				<?php
					$engines = ttsengines_get_all_engines();

					foreach ($engines as $engine)
					{
						if ($engine['name'] == $thisTTS['engine'])
						{
							echo '<option value="' . $engine['name'] . '" selected=1>' . $engine['name'] . '</option>';
						}
						else
						{
							echo '<option value="' . $engine['name'] . '">' . $engine['name'] . '</option>';
						}
					}
				?>
			</select>
		<?php } else { ?>
			<i><?php echo $tts_agi_error; ?></i>
		<?php } ?>
		</td>
	</tr>

	<tr><td colspan="2"><br><h5><?php echo _("After the Text To Speech was played go to")?>:</h5></td></tr>
<?php
//draw goto selects
if (isset($thisTTS)) {
	echo drawselects($thisTTS['goto'],0);
} else {
        echo drawselects(null, 0);
}
?>
	<tr>
		<td colspan="2"><br><h6><input name="Submit" type="submit" <?php echo (isset($tts_agi_error) ? 'disabled="disabled"' : ''); ?> value="<?php echo _("Submit Changes")?>"></h6></td>
	</tr>
	</table>
<script language="javascript">
<!--

var theForm = document.editTTS;

if (theForm.description.value == "") {
	theForm.name.focus();
} else {
	theForm.text.focus();
}

function editTTS_submit()
{
	// No longer using this function, but saving it to convert for specific engines, if need be. (for example, if a "," should be replaced with "   "
	return true;
}

//-->
</script>
	</form>
<?php
} //end if action == delGRP
?>
