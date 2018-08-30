<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$get_vars = array(
	'ecm'				=> '', 
	'fax_rx_email'		=> '',
	'force_detection'	=> 'no',
	'headerinfo'		=> '', 
	'legacy_mode'		=> 'no',
	'localstationid'	=> '', 
	'maxrate'			=> '', 
	'minrate'			=> '', 
	'modem'				=> '', 
	'sender_address'	=> '', 
);
foreach($get_vars as $k => $v){
	$fax[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
}


$tabindex = 0;
// get/put options
if (isset($_REQUEST['action']) &&  $_REQUEST['action'] == 'edit'){
	fax_save_settings($fax);
}
$fax = array_merge($fax, fax_get_settings());
$action = '';//no action to do

$fax_detect = fax_detect();
$trans_rates = array(
					'2400'	=> '2400',
					'4800'	=> '4800',	
					'7200'	=> '7200',	
					'9600'	=> '9600',	
					'12000'	=> '12000',	
					'14400'	=> '14400'
					);
?>

<h2><?php echo _("Fax Options")?></h2>
<form name="edit" action="<?php echo $_SERVER['PHP_SELF']; ?>" method=POST>
	<table id="faxoptionstable">		
		<tbody>			
			<tr><td colspan="3"><h5><?php echo _("Fax Presentation Options")?><hr/></h5></td></tr>			
			<tr>
				<td><a href="#" class="info"><?php echo _("Default Fax header")?>:<span><?php echo _("Header information that is passed to remote side of the fax transmission and is printed on top of every page. This usually contains the name of the person or entity sending the fax.")?></span></a></td>
				<td><input size="30" type="text" name="headerinfo" value="<?php  echo $fax['headerinfo']; ?>" tabindex="<?php echo ++$tabindex;?>"></td>	
			</tr>			<tr>
				<td><a href="#" class="info"><?php echo _("Default Local Station Identifier")?>:<span><?php echo _("The outgoing Fax Machine Identifier. This is usually your fax number.")?></span></a></td>
				<td><input size="30" type="text" name="localstationid" value="<?php  echo $fax['localstationid']; ?>" tabindex="<?php echo ++$tabindex;?>"></td>					
			</tr>
			<tr>
				<td><a class="info" href="#"><?php echo _("Outgoing Email address:")?><span><?php echo _("Email address that faxes appear to come from if 'system default' has been chosen as the default fax extension.")?></span></a></td>
				<td><input type="text" size="30" name="sender_address" value="<?php  echo htmlspecialchars($fax['sender_address'])?>" tabindex="<?php echo ++$tabindex;?>"/></td>
			</tr>
			<tr><td colspan="3"><h5><?php echo _("Fax Feature Code Options")?><hr/></h5></td></tr>			
			<tr>
				<td><a class="info" href="#"><?php echo _("Email address:")?><span><?php echo _("Email address that faxes are sent to when using the \"Dial System Fax\" feature code. This is also the default email for fax detection in legacy mode, if there are routes still running in this mode that do not have email addresses specified.")?></span></a></td>
				<td><input type="text" size="30" name="fax_rx_email" value="<?php  echo htmlspecialchars($fax['fax_rx_email'])?>" tabindex="<?php echo ++$tabindex;?>"/></td>
			</tr>			
			
			<tr><td colspan="3"><h5><?php echo _("Fax Transport Options")?><hr/></h5></td></tr>
			<tr><td><?php echo ipbx_label(_("Error Correction Mode"), 
			_("Error Correction Mode (ECM) option is used to specify whether
			 to use ecm mode or not.")); ?></td>
       <td><span class="radioset"><input type="radio" name="ecm" id="ecm_yes" value="yes" <?php echo (($fax['ecm'] == 'yes')?'checked':''); ?> tabindex="<?php echo ++$tabindex;?>"><label for="ecm_yes"><?php echo _("Yes")?></label>
       <input type="radio" name="ecm" value="no" id="ecm_no"  <?php echo (($fax['ecm'] == 'no')?'checked':''); ?> tabindex="<?php echo ++$tabindex;?>"><label for="ecm_no"><?php echo _("No")?></label></span></td>
			</tr>				
			<tr>
				<td><?php echo ipbx_label(_("Maximum transfer rate"), 
				_("Maximum transfer rate used during fax rate negotiation.")); ?></td>
				<td><?php echo form_dropdown('maxrate', $trans_rates, $fax['maxrate']); ?></td>		
			</tr>	
			<tr>
				<td><?php echo ipbx_label(_("Minimum transfer rate"), 
				_("Minimum transfer rate used during fax rate negotiation.")); ?></td>
				<td><?php echo form_dropdown('minrate', $trans_rates, $fax['minrate']); ?></td>				
			</tr>
		
			<tr><td colspan="3"><h5><?php echo _("Fax Module Options")?><hr/></h5></td></tr>
			<tr>
				<td><a href="#" class="info"><?php echo _("Always Allow Legacy Mode")?>:<span><?php echo _("In earlier versions, it was possible to provide an email address with the incoming FAX detection to route faxes that were being handled by fax-to-email detection. This has been deprecated in favor of Extension/User FAX destinations where an email address can be provided. During migration, the old email address remains present for routes configured this way but goes away once 'properly' configured. This options forces the Legacy Mode to always be present as an option.")?></span></a></td>
        <td><span class="radioset"><input type="radio" name="legacy_mode" id="legacy_mode_yes" value="yes" <?php echo (($fax['legacy_mode'] == 'yes')?'checked':''); ?> tabindex="<?php echo ++$tabindex;?>"><label for="legacy_mode_yes"><?php echo _("Yes")?></label>
        <input type="radio" name="legacy_mode" value="no" id="legacy_mode_no" <?php echo (($fax['legacy_mode'] == 'no')?'checked':''); ?> tabindex="<?php echo ++$tabindex;?>"><label for="legacy_mode_no"><?php echo _("No")?></label></td>			
			</tr>				

<?php if(!$fax_detect['module']){ ?>
			<tr>
				<td><a href="#" class="info"><?php echo _("Always Generate Detection Code")?>:<span><?php echo _("When no fax modules are detected the module will not generate any detection dialplan by default. If the system is being used with phyical FAX devices, hylafax + iaxmodem, or other outside fax setups you can force the dialplan to be generated here.")?></span></a></td>
        <td><span class="radioset"><input type="radio" name="force_detection" id="force_detection_yes" value="yes" <?php echo (($fax['force_detection'] == 'yes')?'checked':''); ?> tabindex="<?php echo ++$tabindex;?>"><label for="force_detection_yes"><?php echo _("Yes")?></label>
        <input type="radio" name="force_detection" value="no" id="force_detection_no" <?php echo (($fax['force_detection'] == 'no')?'checked':''); ?> tabindex="<?php echo ++$tabindex;?>"><label for="force_detection_no"><?php echo _("No")?></label></span></td>			
			</tr>				
<?php } ?>
	</tbody>
	</table>
	<br />

	<input type="hidden" value="fax" name="display"/>
	<input type="hidden" name="action" value="edit">
	<input type=submit value="<?php echo _("Submit")?>">

</form>
<?php
//add hooks
echo $module_hook->hookHtml;
?>