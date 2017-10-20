<h2>General Settings</h2>
<hr />

<form name="digium_phones_general_settings" method="post" action="config.php?type=setup&display=digium_phones&digium_phones_form=general_edit" style="float:left;">
<script>
$('form').submit(function() {
	if ($('#easy_mode option:selected').val() == "yes") {
		switch ($('#config_auth option:selected').val()) {
		case "mac":
		case "pin":
			$('#config_auth option[value=disabled]').attr('selected', true);
		default:
			break;
		}
	}
});
</script>
<input type="hidden" name="display" value="digium_phones" />
<input type="hidden" name="action" value="edit" />
<?php
$table = new CI_Table();
$table ->add_row(fpbx_label('DPMA Version:','The version number of the res_digium_phone.so module loaded by Asterisk.'),
	'<input type="text" readonly value="'.$digium_phones->get_dpma_version().'" />');

$table->add_row(fpbx_label('Easy Mode:', 'When Easy Mode is enabled, defaults are used for many options, a number of options are not configurable, and Phone Configurations will be automatically created or deleted based on IssabelPBX extensions, and lines will be mapped to those Phone Configurations.  Disabling Easy Mode provides all configuration options; but, when Easy Mode is disabled, Phone Configurations are not automatically managed.  Defaults to Yes (enabled).'), 
	'<span class="radioset">
	<input type="radio" name="easy_mode" id="easy_mode-enable" value="yes" ' . ($digium_phones->get_general('easy_mode') == "yes" ? 'checked' : '') . ' />
	<label for="easy_mode-enable">Enable</label>

	<input type="radio" name="easy_mode" id="easy_mode-disable" value="no" ' . ($digium_phones->get_general('easy_mode') == "no" ? 'checked' : '') . ' />
	<label for="easy_mode-disable">Disable</label></span>'
	);


$table->add_row(array( 'data' => fpbx_label('Global Pin:', 'A numeric identifier that, depending on the setting of User List and Config Authorization requirements, can be used to assign the configuration of any phone.'), 'class' => 'input'),
	array( 'data' => '<input type="text" id="globalpin" name="globalpin" value="' . $digium_phones->get_general('globalpin') . '" placeholder="Global Pin"/>')
	);

$table->add_row(array( 'data' => fpbx_label('Require Global PIN for user list?:', 'Defines whether the Global PIN is required in order to pull a listing of available phone configurations. Defaults to "No," meaning any phone can pull a listing of all other available phone configurations.')),
	array( 'data' => '<select id="userlist_auth" name="userlist_auth">
			<option value="disabled">No (Default)</option>
			<option value="globalpin"' . ($digium_phones->get_general('userlist_auth') == "globalpin" ? 'selected' : '') . '>Yes</option></select>')
	);
	
$config_auth = '<select id="config_auth" name="config_auth">';
if (!$easymode) {
	$config_auth .= '<option value="mac">Phone MAC Address</option><option value="pin">Phone Pin</option>';
}
$config_auth .= '<option value="globalpin">Global PIN</option><option value="disabled">None (Default)</option></select>';	
$table->add_row(array( 'data' => fpbx_label('Phone Authentication Method:', 'This is the authentication type required for a user to take ownership of a phone. By default, no authentication is required and any user can claim any phone configuration. Other available options are: Phone PIN - user must enter the phone\'s PIN in order to claim the phone; Phone MAC Address - the phone\'s actual MAC address must match the configured MAC address for the phone; Global PIN - the user must enter the Global PIN in order to claim the phone.')),
	array( 'data' => $config_auth));

$table->add_row(array( 'data' => fpbx_label('Internal Phonebook Sort Order:', 'Defines the order in which internal phonebooks are sorted.  Default is by extension.')),
	array( 'data' => '<select id="internal_phonebook_sort" name="internal_phonebook_sort">
			<option value="extension">Extension (Default)</option>
			<option value="description"' . ($digium_phones->get_general('internal_phonebook_sort') == "description" ? 'selected' : '') . '>Name</option></select>')
	);

echo $table->generate();
$table->clear();

$selected_locale = $digium_phones->get_general('active_locale');
$locale = '<select id="active_locale" name="active_locale"><option value="" ' . ($selected_locale === NULL ? ' selected ' : '') . '>&nbsp;</option>';
$locales = $digium_phones->get_locales();

foreach ($locales as $localeT) {
	$locale .= '<option ' . ($selected_locale === $localeT ? 'selected ' : '') . 'value="' . $localeT . '">' . $localeT . '</option>';
}

$locale .=	'</select>';
//hacking as there is no way to add a class to a row using table
echo '<table style="border-style: none; border-spacing: 4px;"><tbody>';
echo '<tr class="guielToggle" data-toggle_class="advanced"><td><h5><span class="guielToggleBut">+ </span>Advanced</h5><hr></td><td>&nbsp;</td></tr>'."\n";
echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">Active Locale:<span>Set the default active locale</span></a></td><td>' . $locale . '</td></tr>'."\n";
echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">mDNS Service Name:<span>Defines the registration server name for this server, provided to the phone during its mDNS server discovery.</span></a></td><td><input type="text" id="service_name" name="service_name" value="' . $digium_phones->get_general('service_name') . '" placeholder="mDNS Service Name"/></td></tr>'."\n";
echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">mDNS Discovery Address:<span>Defines the SIP UDP signaling address or hostname of this server, provided to the phone during its mDNS server discovery.</span></a></td><td><input type="text" id="mdns_address" name="mdns_address" value="' . $digium_phones->get_general('mdns_address') . '" placeholder="mDNS Discovery Address"/></td></tr>'."\n";
echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">mDNS Discovery Port:<span>Defines the SIP UDP signaling port for this server, provided to the phone during its mDNS server discovery. Defaults to "5060".</span></a></td><td><input type="text" id="mdns_port" name="mdns_port" value="' . $digium_phones->get_general('mdns_port') . '" placeholder="mDNS Discovery Port"/></td></tr>'."\n".'</tbody></table>'."\n";
?>

<div class="btn_container">
	<input type="submit" id="general_submit" name="general_submit" value="Save" />
</div>
</form>
<br />

<script>
	//ChangeSelectByValue('easy_mode', '<?php echo $digium_phones->get_general('easy_mode')?>', true);
	ChangeSelectByValue('userlist_auth', '<?php echo $digium_phones->get_general('userlist_auth')?>', true);
	ChangeSelectByValue('config_auth', '<?php echo $digium_phones->get_general('config_auth')?>', true);
	ChangeSelectByValue('internal_phonebook_sort', '<?php echo $digium_phones->get_general('internal_phonebook_sort')?>', true);
</script>
