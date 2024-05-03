<?php /* $Id:$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//  Xavier Ourciere xourciere[at]propolys[dot]com
//

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$phpagiid = isset($_REQUEST['phpagiid'])?$_REQUEST['phpagiid']:'';
$id = isset($_REQUEST['id'])?$_REQUEST['id']:'';
$debug = isset($_REQUEST['debug'])?$_REQUEST['debug']:'0';
$error_handler = isset($_REQUEST['error_handler'])?$_REQUEST['error_handler']:'0';
$err_email = isset($_REQUEST['err_email'])?$_REQUEST['err_email']:'admin@example.com';
$hostname = isset($_REQUEST['hostname'])?$_REQUEST['hostname']:'issabelpbx.example.com';
$tempdir = isset($_REQUEST['tempdir'])?$_REQUEST['tempdir']:'/tmp';
$festival_text2wave = isset($_REQUEST['festival_text2wave'])?$_REQUEST['festival_text2wave']:'/usr/bin/text2wave';
$asman_server = isset($_REQUEST['asman_server'])?$_REQUEST['asman_server']:'localhost';
$asman_port = isset($_REQUEST['asman_port'])?$_REQUEST['asman_port']:'5038';
$asmanager = isset($_REQUEST['asmanager'])?$_REQUEST['asmanager']:''; // This comes from the API module
$cepstral_swift = isset($_REQUEST['cepstral_swift'])?$_REQUEST['cepstral_swift']:'/opt/swift/bin/swift';
$cepstral_voice = isset($_REQUEST['cepstral_voice'])?$_REQUEST['cepstral_voice']:'David';
$setuid = isset($_REQUEST['setuid'])?$_REQUEST['setuid']:'0';
$basedir = isset($_REQUEST['basedir'])?$_REQUEST['basedir']:'/var/lib/asterisk/agi-bin/';

$dispnum = "phpagiconf"; //used for switch on config.php

switch ($action) {
	case "edit":
		phpagiconf_update($id, $debug, $error_handler, $err_email, $hostname, $tempdir,
				$festival_text2wave, $asman_server, $asman_port, $asmanager,
				$cepstral_swift, $cepstral_voice, $setuid, $basedir);
        phpagiconf_gen_conf();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        redirect_standard('id');
		//needreload();
	break;
	case "add":
		phpagiconf_add($debug, $error_handler, $err_email, $hostname, $tempdir,
				$festival_text2wave, $asman_server, $asman_port, $asmanager,
				$cepstral_swift, $cepstral_voice, $setuid, $basedir);
		phpagiconf_gen_conf();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been added'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        redirect_standard('');
        //needreload();
	break;
}

//get details for this phpagiconf text
$tabindex = 0;
$thisConfig = $phpagiconf = phpagiconf_get();
//create variables
if (is_array($thisConfig)) {
	extract($thisConfig);
}
?>
<div class='content'>
	<h2><?php echo __("PHPAGI Config"); ?></h2>
	<form id="mainform" autocomplete="on" name="editAGIConf" action="config.php?type=tool&amp;display=phpagiconf" method="post" onsubmit="$.LoadingOverlay('show')">
	<input type="hidden" name="display" value="<?php echo $dispnum?>">
	<input type="hidden" name="action" value="<?php echo (is_array($thisConfig) ? 'edit' : 'add') ?>">
	<table class='table is-narrow is-borderless'>
	<tr><td colspan="2"><h5><?php echo __("Main config"); ?></h5></td></tr>
	<tr><td><input type="hidden" name="id" value="<?php echo $phpagiid; ?>"></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Debug")?><span><?php echo __("Enable PHPAGI debugging.")?></span></a></td>
		<td><select name="debug" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
			<option value="0" <?php echo (($debug==0) ? 'selected="selected"' : ''); ?>><?php echo __("false"); ?>
			<option value="1" <?php echo (($debug==1) ? 'selected="selected"' : ''); ?>><?php echo __("true"); ?>
		</select></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Error handler")?><span><?php echo __("Use internal error handler.")?></span></a></td>
		<td><select name="error_handler" tabindex="<?php echo ++$tabindex;?>"  class='componentSelect'>
			<option value="0" <?php echo (($error_handler==0) ? 'selected="selected"' : ''); ?>><?php echo __("false");?>
			<option value="1" <?php echo (($error_handler==1) ? 'selected="selected"' : ''); ?>><?php echo __("true");?>
		</select></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Mail errors to")?><span><?php echo __("Email where the errors will be sent.")?></span></a></td>
		<td><input type="text" class="input" name="err_email" value="<?php echo $err_email; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Hostname of the server")?><span><?php echo __("Hostname of this server.")?></span></a></td>
		<td><input type="text" class="input" name="hostname" value="<?php echo $hostname; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Temporary directory")?><span><?php echo __("Temporary directory for storing temporary output.")?></span></a></td>
		<td><input size=40 type="text" class="input" name="tempdir" value="<?php echo $tempdir; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr><td colspan="2"><h5><?php echo __("Festival config"); ?></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Path to text2wave")?><span><?php echo __("Path to text2wave binary.")?></span></a></td>
		<td><input type="text" class="input" name="festival_text2wave" value="<?php echo $festival_text2wave; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr><td colspan="2"><h5><?php echo __("Asterisk API settings"); ?></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Server")?><span><?php echo __("Server to connect to.")?></span></a></td>
		<td><input type="text" class="input" name="asman_server" value="<?php echo $asman_server; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Port")?><span><?php echo __("Port to connect to manager.")?></span></a></td>
		<td><input type="text" class="input" name="asman_port" value="<?php echo $asman_port; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
<?php echo $module_hook->hookHtml; ?>
	<tr><td colspan="2"><h5><?php echo __("Fast AGI config"); ?></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("setuid")?><span><?php echo __("Drop privileges to owner of script.")?></span></a></td>
		<td><select name="setuid" tabindex="<?php echo ++$tabindex;?>"  class='componentSelect'>
			<option value="0" <?php echo (($setuid==0) ? 'selected="selected"' : ''); ?>><?php echo __("false");?>
			<option value="1" <?php echo (($setuid==1) ? 'selected="selected"' : ''); ?>><?php echo __("true");?>
		</select></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Basedir")?><span><?php echo __("Path to AGI scripts folder.")?></span></a></td>
		<td><input size=40 type="text" class="input" name="basedir" value="<?php echo $basedir; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr><td colspan="2"><h5><?php echo __("Cepstral config"); ?></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Swift path")?><span><?php echo __("Path to cepstral TTS binary.")?></span></a></td>
		<td><input type="text" class="input" name="cepstral_swift" value="<?php echo $cepstral_swift; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Cepstral voice")?><span><?php echo __("TTS Voice used.")?></span></a></td>
		<td><input type="text" class="input" name="cepstral_voice" value="<?php echo $cepstral_voice; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>

	</table>
</form>
</div>
<script>
<?php echo js_display_confirmation_toasts(); ?>
</script>
<?php echo form_action_bar($extdisplay); ?>
