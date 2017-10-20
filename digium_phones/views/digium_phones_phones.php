<h2>Phones</h2>
<hr />
<script type="text/javascript" src="modules/digium_phones/assets/js/phones.js"></script>
<form id="digium_phones_editdevice" name="digium_phones_editdevice" action="config.php?type=setup&display=digium_phones&digium_phones_form=phones_edit" method="post">
<script>
function reconfiguredevice(id)
{
	if ($('#button_reload').is(":visible")) {
		alert("Please press Apply Config before reconfiguring phone");
		return;
	}
	parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=phones_edit&reconfiguredevice_submit=Reconfigure&device='+id;
}
$().ready(function() {
<?php
if (isset($_GET['reconfiguredevice_submit']) && $response != null) {
	$reconfigure = preg_split('/\n/', $response['data']);
	if ($reconfigure[1] != null) {
?>
		alert("<?php echo $reconfigure[1]?>");
<?php
	}
}


$devices = $digium_phones->get_devices();

$config_auth = $digium_phones->get_general('config_auth');

$editdev = null;

if (isset($_GET['device'])) {
	if (!isset($_GET['deletedevice_submit']) && !isset($_GET['reconfiguredevice_submit'])) {
		$editdev = htmlspecialchars($_GET['device']);
	}
}

if ($editdev != null) {
	if ($editdev == 0) {
?>
		$('#devicename').val("New Phone");
<?php
	} else {
?>
		$('#devicename').val($('#device<?php echo $editdev?>name').text());
<?php
	}
?>
	$('#device').val(<?php echo $editdev?>);

	$('div[id=editingdevice]').show();
<?php
}

if ($editdev == 0) {
	$general_locale = $digium_phones->get_general('active_locale');
	if ($general_locale != NULL) {
?>
		if ($('#active_locale') != null) {
			$('#active_locale').val('<?php echo $general_locale?>');
		}
<?php
	}
}


if (!empty($devices)) foreach ($devices as $deviceid=>$device) {
	if ($editdev == $deviceid) {
		if (isset($device['settings']['active_locale']) === FALSE) {
			$general_locale = $digium_phones->get_general('active_locale');
			if ($general_locale != NULL) {
				$device['settings']['active_locale'] = $general_locale;
			}
		}
		if (!empty($device['settings'])) foreach ($device['settings'] as $key=>$val) {
?>
			if ($('#<?php echo $key?>') != null) {
				$('#<?php echo $key?>').val('<?php echo $val?>');
			}
<?php
		}
		if ($device['settings']['pin'] == 'voicemail') {
?>
			$('#pin').prop('disabled', true);
			$('#pin_voicemail').prop('checked', true);
<?php
		}
	}
	if (!empty($device['lines'])) foreach ($device["lines"] as $line) {
		if ($editdev == $deviceid) {
?>
			addEntry("<?php echo $line['extension']?>");
<?php
		} else {
?>
			useEntry("<?php echo $line['extension']?>");
<?php
		}
	}
	if (!empty($device['externallines'])) foreach ($device["externallines"] as $externalline) {
		if ($editdev == $deviceid) {
?>
			addEntry("external:<?php echo $externalline['externallineid']?>");
<?php
		}
	}

	if (!empty($device['phonebooks'])) foreach ($device["phonebooks"] as $phonebook) {
		if ($editdev == $deviceid) {
?>
			addPhonebook("<?php echo $phonebook['phonebookid']?>");
<?php
		}
	}

	if (!empty($device['networks'])) foreach ($device["networks"] as $network) {
		if ($editdev == $deviceid) {
?>
			addNetwork("<?php echo $network['networkid']?>");
<?php
		}
	}

	if (!empty($device['logos'])) foreach ($device["logos"] as $logo) {
		if ($editdev == $deviceid) {
?>
			addLogo("<?php echo $logo['logoid']?>");
<?php
		}
	}

	if (!empty($device['alerts'])) foreach ($device["alerts"] as $alert) {
		if ($editdev == $deviceid) {
?>
			addAlert("<?php echo $alert['alertid']?>");
<?php
		}
	}
	if (!empty($device['ringtones'])) foreach ($device["ringtones"] as $ringtone) {
		if ($editdev == $deviceid) {
?>
			addAlert("<?php echo $ringtone['ringtoneid']?>");
<?php
		}
	}

	if (!empty($device['statuses'])) foreach ($device["statuses"] as $status) {
		if ($editdev == $deviceid) {
?>
			addStatus("<?php echo $status['statusid']?>");
<?php
		}
	}

	if (!empty($device['customapps'])) foreach ($device["customapps"] as $customapp) {
		if ($editdev == $deviceid) {
?>
			addCustomApp("<?php echo $customapp['customappid']?>");
<?php
		}
	}
}
?>
});

$('form').submit(function() {
	if ($.trim($('#devicename').val()).length <= 0) {
		alert("Phone Name cannot be empty.");
		return false;
	}
<?php
	if ($config_auth == "pin") {
?>
		if ($.trim($('#pin').val()).length <= 0) {
			alert("Phone PIN cannot be empty.");
			return false;
		}
<?php
	} else if ($config_auth == "mac") {
?>
		if ($.trim($('#mac').val()).length <= 0) {
			alert("Phone MAC cannot be empty.");
			return false;
		}
<?php
	}
?>

	$('#lines').attr("multiple", "multiple");
 	$('#lines option').each(function() {
 		$(this).attr("selected", "selected");
	});
	$('#devicephonebooks').attr("multiple", "multiple");
 	$('#devicephonebooks option').each(function() {
 		$(this).attr("selected", "selected");
	});
	$('#devicenetworks').attr("multiple", "multiple");
 	$('#devicenetworks option').each(function() {
 		$(this).attr("selected", "selected");
	});
	$('#devicelogos').attr("multiple", "multiple");
 	$('#devicelogos option').each(function() {
 		$(this).attr("selected", "selected");
	});
	$('#devicealerts').attr("multiple", "multiple");
 	$('#devicealerts option').each(function() {
 		$(this).attr("selected", "selected");
	});
	$('#deviceringtones').attr("multiple", "multiple");
 	$('#deviceringtones option').each(function() {
 		$(this).attr("selected", "selected");
	});
	$('#devicestatuses').attr("multiple", "multiple");
 	$('#devicestatuses option').each(function() {
 		$(this).attr("selected", "selected");
	});
	$('#devicecustomapps').attr("multiple", "multiple");
 	$('#devicecustomapps option').each(function() {
 		$(this).attr("selected", "selected");
	});
});

function useEntry(exten) {
	$('#extensions option[value="'+exten+'"]').remove();
}
function addEntry(exten) {
	if ($.trim(exten).length <= 0) {
		alert("Cannot add empty extensions.");
		return false;
	}

	entry = $('#extensions option[value="'+exten+'"]');
	newentry = entry.appendTo('#lines optgroup[label="'+entry.parent().attr('label')+'"]');
	$('#lines').attr('selectedIndex', newentry.index());
	$('#extensions').attr('selectedIndex', '0');
	useEntry(exten);

	return true;
}
function delEntry(exten) {
	entry = $('#lines option[value="'+exten+'"]');

	eopt = entry.appendTo('#extensions optgroup[label="'+entry.parent().attr('label')+'"]');
	$('#extensions').attr('selectedIndex', eopt.index());

	return true;
}
function usePhonebook(phonebookid) {
	$('#phonebooks option[value='+phonebookid+']').remove();
}
function addPhonebook(phonebookid) {
	phonebook = $('#phonebooks option[value='+phonebookid+']');
	if (phonebook.val() == phonebookid) {
		newphonebook = phonebook.clone();
		newphonebook.appendTo('#devicephonebooks');
		$('#devicephonebooks').attr('selectedIndex', newphonebook.index());
		$('#phonebooks').attr('selectedIndex', '0');
		usePhonebook(phonebookid);
	}
	return true;
}
function delPhonebook(phonebookid) {
	phonebook = $('#devicephonebooks option[value='+phonebookid+']');
	popt = phonebook.appendTo('#phonebooks');
	if (popt) {
		$('#phonebooks').attr('selectedIndex', popt.index());
	} else {
		$('#phonebooks').attr('selectedIndex', '0');
	}
	return true;
}
function useNetwork(networkid) {
	$('#networks option[value='+networkid+']').remove();
}
function addNetwork(networkid) {
	network = $('#networks option[value='+networkid+']');
	if (network.val() == networkid) {
		newnetwork = network.clone();
		newnetwork.appendTo('#devicenetworks');
		$('#devicenetworks').attr('selectedIndex', newnetwork.index());
		$('#networks').attr('selectedIndex', '0');
		useNetwork(networkid);
	}
	return true;
}
function delNetwork(networkid) {
	network = $('#devicenetworks option[value='+networkid+']');
	nopt = network.appendTo('#networks');
	if (nopt) {
		$('#networks').attr('selectedIndex', nopt.index());
	} else {
		$('#networks').attr('selectedIndex', '0');
	}
	return true;
}
function useLogo(logoid) {
	$('#logos option[value='+logoid+']').remove();
}
function addLogo(logoid) {
	logo = $('#logos option[value='+logoid+']');
	if (logo.val() == logoid) {
		newlogo = logo.clone();
		newlogo.appendTo('#devicelogos');
		$('#devicelogos').attr('selectedIndex', newlogo.index());
		$('#logos').attr('selectedIndex', '0');
		useLogo(logoid);
	}
	return true;
}
function delLogo(logoid) {
	logo = $('#devicelogos option[value='+logoid+']');
	lopt = logo.appendTo('#logos');
	if (lopt) {
		$('#logos').attr('selectedIndex', lopt.index());
	} else {
		$('#logos').attr('selectedIndex', '0');
	}
	return true;
}
function useAlert(alertid) {
	$('#alerts option[value='+alertid+']').remove();
}
function addAlert(alertid) {
	alert = $('#alerts option[value='+alertid+']');
	if (alert.val() == alertid) {
		newalert = alert.clone();
		newalert.appendTo('#devicealerts');
		$('#devicealerts').attr('selectedIndex', newalert.index());
		$('#alerts').attr('selectedIndex', '0');
		useAlert(alertid);
	}
	return true;
}
function delAlert(alertid) {
	alert = $('#devicealerts option[value='+alertid+']');
	aopt = alert.appendTo('#alerts');
	if (aopt) {
		$('#alerts').attr('selectedIndex', aopt.index());
	} else {
		$('#alerts').attr('selectedIndex', '0');
	}
	return true;
}
function useAlert(ringtoneid) {
	$('#ringtones option[value='+ringtoneid+']').remove();
}
function addAlert(ringtoneid) {
	ringtone = $('#ringtones option[value='+ringtoneid+']');
	if (ringtone.val() == ringtoneid) {
		newringtone = ringtone.clone();
		newringtone.appendTo('#deviceringtones');
		$('#deviceringtones').attr('selectedIndex', newringtone.index());
		$('#ringtones').attr('selectedIndex', '0');
		useAlert(ringtoneid);
	}
	return true;
}
function delAlert(ringtoneid) {
	ringtone = $('#deviceringtones option[value='+ringtoneid+']');
	aopt = ringtone.appendTo('#ringtones');
	if (aopt) {
		$('#ringtones').attr('selectedIndex', aopt.index());
	} else {
		$('#ringtones').attr('selectedIndex', '0');
	}
	return true;
}
function useStatus(statusid) {
	$('#statuses option[value='+statusid+']').remove();
}
function addStatus(statusid) {
	statusentry = $('#availableStatuses option[value='+statusid+']');
	if (statusentry.val() == statusid) {
		newstatus = statusentry.clone();
		newstatus.appendTo('#devicestatuses');
		$('#devicestatuses').attr('selectedIndex', newstatus.index());
		$('#statuses').attr('selectedIndex', '0');
		useStatus(statusid);
	}
	return true;
}
function delStatus(statusid) {
	statusentry = $('#devicestatuses option[value='+statusid+']');
	sopt = statusentry.appendTo('#statuses');
	if (sopt) {
		$('#statuses').attr('selectedIndex', sopt.index());
	} else {
		$('#statuses').attr('selectedIndex', '0');
	}
	return true;
}
function useCustomApp(customappid) {
	$('#customapps option[value='+customappid+']').remove();
}
function addCustomApp(customappid) {
	customappentry = $('#customapps option[value='+customappid+']');
	if (customappentry.val() == customappid) {
		newcustomapp = customappentry.clone();
		newcustomapp.appendTo('#devicecustomapps');
		$('#devicecustomapps').attr('selectedIndex', newcustomapp.index());
		$('#customapps').attr('selectedIndex', '0');
		useCustomApp(customappid);
	}
	return true;
}
function delCustomApp(customappid) {
	customappentry = $('#devicecustomapps option[value='+customappid+']');
	copt = customappentry.appendTo('#customapps');
	if (copt) {
		$('#customapps').attr('selectedIndex', copt.index());
	} else {
		$('#customapps').attr('selectedIndex', '0');
	}
	return true;
}
</script>

<?php
if (!$easymode) {
?>
<input type="button" value="Add Phone" onclick="location.href='config.php?type=setup&display=digium_phones&digium_phones_form=phones_edit&device=0'" />
<?php
} else {
?>
<h3>Easy mode is enabled.  Configured extensions will automatically create Digium Phone configuration.</h3>
<?php
}
?>
<input type="button" value="Reconfigure All" onClick="reconfiguredevice(-1);">
<p>

<table style="border-collapse:collapse; border-style:outset; border-width: 1px; ">
<tr>
	<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Phone Name<span>A name for this phone, usually the name of the person who's using it.</span></a></th>
	<th style="border-style:inset; border-width:1px; "><a href="#" class="info"><?php echo ($easymode)?'Ext. #':'Extension(s)'?><span>The IssabelPBX extension that is assigned to a particular line key.</span></a></th>
	<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Phone Info<span>Information about this device, such as IP address, model, and MAC address.</span></a></th>
	<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Presence<span>The current user presence information for this device, indicating availability.</span></a></th>
	<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Actions<span>Three actions are available. "Edit" allows customization of a particular phone. "Delete" will delete a phone, but not the underlying extension. "Reconfigure" will notify the phone that it should request new configuration information from Asterisk.</span></a></th>
<?php
if (!$easymode) {
?>
	<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Notes<span>Any important information about this device.  Examples include missing MAC address or PIN if the Phone Authentication Method requires them.</span></a></th>
<?php
}
?>
</tr>
<?php
// get session information for use later
$response = $astman->send_request('Command',array('Command'=>"digium_phones show sessions"));
$sessions = null;
if ($response && array_key_exists('data', $response)) {
	$sessions = preg_split('/\n/', $response['data']);
}

if (!empty($devices)) foreach ($devices as $deviceid=>$device) {
?>
<tr>
	<td style="border-style:inset; border-width: 1px; ">
		<span id="device<?php echo $deviceid?>name"><?php echo $device['name']?></span>
	</td>
	<td style="border-style:inset; border-width:1px; ">
		<table style="border-collapse:collapse; ">
<?php
	$first_internal_line = null;
	$linecount=0;
	if (!empty($device['lines'])) foreach ($device['lines'] as $line) {
		$linecount++;
?>
		<tr>
			<td style="border-style:inset; border-width:0px; ">
<?php
		if (!$easymode) {
			if (!$first_internal_line) {
				$first_internal_line = $line['extension'];
			}
?>
			Line-Key <?php echo ($linecount)?>: <?php echo $line['user']['description']?> &lt;<?php echo $line['extension']?>&gt;
<?php
		} else {
			if (!$first_internal_line) {
				$first_internal_line = $line['extension'];
			}
?>
			<?php echo $line['extension']?>
<?php
		}
?>
			</td>
		</tr>
<?php
	}

	if (!empty($device['externallines'])) foreach ($device['externallines'] as $externalline) {
		$el = $digium_phones->get_externalline($externalline['externallineid']);

		if ($el == null) {
			continue;
		}

		$linecount++;
?>
		<tr>
			<td style="border-style:inset; border-width:0px; ">
			Line-Key <?php echo ($linecount)?>: <?php echo $el['name']?>
			</td>
		</tr>
<?php
	}
?>



		</table>
	</td>
	<td style="border-style:inset; border-width:1px; white-space: nowrap; ">
<?php
	// phone info column
	$addr = null;
	$model = '';
	$response = null;
	// XXX When DPMA-273 gets resolved, this needs to be updated accordingly
	if ($first_internal_line) {
		$response = $astman->send_request('SIPshowpeer',array('Peer'=>"{$first_internal_line}"));
	}
	if ($response != null && $response['Response'] == 'Success') {
		$addr = $response['Address-IP'];
		$model = $response['SIP-Useragent'];
		$matches = null;
		if (preg_match('/(Digium D\d+).*/', $model, $matches)) {
			$model = $matches[1];
		} else {
			$model = 'Unknown';
		}
	}
	if ($addr == '(null)') {
		$addr = null;
	}
	if ($addr) {
		$mac = '';
		if ($sessions) foreach ($sessions as $session_line) {
			$matches = null;
			if (preg_match('/:'.$addr.':.*MAC:([a-fA-F0-9]{12})$/', $session_line, $matches)) {
				$mac = $matches[1];
				break;
			}
		}
?>
		<table style="border-collapse:collapse; ">
		<tr>
			<td style="border-style:inset; border-width:0px; ">
				Model: <?php echo $model?>
			</td>
		</tr>
		<tr>
			<td style="border-style:inset; border-width:0px; ">
				IP Address: <?php echo $addr?>
			</td>
		</tr>
		<tr>
			<td style="border-style:inset; border-width:0px; ">
				MAC: <?php echo $mac?>
			</td>
		</tr>
		</table>
<?php
	} else {
?>
		-
<?php
	}
?>
	</td>
<?php
	$presence = '-';
	if ($addr) {
		$response = $astman->send_request('Getvar',array('Variable'=>"PRESENCE_STATE(CustomPresence:{$first_internal_line},value)"));
		if ($response && array_key_exists('Response', $response) && $response['Response'] == "Success") {
			$presence = $response['Value'];
		}
	}
	// user presence column
?>
	<td style="border-style:inset; border-width:1px; white-space: nowrap; "><?php echo $presence?></td>
	<td style="border-style:inset; border-width:1px; white-space: nowrap; ">
<?php
	// actions column
	if (!$easymode) {
?>
		<input type="button" value="Edit" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=phones_edit&device=<?php echo $deviceid?>'">
		<input type="button" value="Delete" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=phones_edit&deletedevice_submit=Delete&device=<?php echo $deviceid?>'">
<?php
	}
?>
		<input type="button" value="Reconfigure" onClick="reconfiguredevice(<?php echo $deviceid?>)">

	</td>
<?php
	if (!$easymode) {
		// notes column
?>
	<td style="border-style:inset; border-width:1px; white-space: nowrap; ">
<?php
		if ($device['settings']['pin'] == null && $config_auth == "pin") {
?>
		<span style="color: red; ">No device PIN set.  Device will be unusable.</span>
<?php
		} else if ($device['settings']['mac'] == null && $config_auth == "mac") {
?>
		<span style="color: red; ">No device MAC address set.  Device will be unusable.</span>
<?php
		} else {
?>
		-
<?php
		}
?>
	</td>
<?php
	}
?>
</tr>
<?php
}
?>
</table>

<hr />
<div id="editingdevice" style="display: none;">
<?php

if ($digium_phones->get_dpma_version() < '2.1.') {
	$pin_voicemail = '';
} else {
	$pin_voicemail = '<input type="checkbox" id="pin_voicemail" name="pin_voicemail" /><label for="pin_voicemail"><small>use Voicemail PIN</small></label>';
}

$table = new CI_Table();
$table->add_row(array( 'data' => '<input type="hidden" id="device" name="device" />'), array());
$table->add_row(array( 'data' => fpbx_label('Phone Name:', 'A named identifier for the phone, such as the person using it.')),
				array( 'data' => '<input type="text" id="devicename" name="devicename" />'));
$table->add_row(array( 'data' => fpbx_label('Phone PIN:', 'A numeric identifier associated with this phone. When set, and when enabled in the General Settings page, one must enter this PIN on the phone in order to use this configuration.')),
				array( 'data' => '<input type="text" id="pin" name="pin" />'.$pin_voicemail));
$table->add_row(array( 'data' => fpbx_label('Phone MAC Address:', 'When set, and when enabled in the General Settings page, the phone configuration is only available to the device matching this MAC Address.')),
				array( 'data' => '<input type="text" id="mac" name="mac" '.($config_auth == "mac" ? "" : "readonly ").'/>'));

				
				
echo $table->generate();
$table->clear();

$devices = $digium_phones->get_device($editdev);
if (!empty($devices['lines'])) foreach( $devices['lines'] as $device){
	$used[$device['extension']] = $device['extension'];
}
if (!empty($devices['externallines'])) foreach( $devices['externallines'] as $device){
	$usedE[$device['externallineid']] = $device['externallineid'];
}

echo '<div class="dragdropFrame">';
echo '<div class="dragdrop">';
echo fpbx_label('Available Extensions', 'Displays IssabelPBX extensions that may be assigned to a phone. Extensions that are greyed out are in use by another phone already and may not be re-assigned without being first unassigned.');
echo '<ul id="availableExtensions" class="ext ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';
foreach ($digium_phones->get_core_devices() as $user) {
	if (strtolower($user['tech']) == 'sip' && empty($used[$user[0]])) {
		echo '<li id="lines_' . $user[0] . '">' . $user[0] . '</li>';
	}
}

foreach ($digium_phones->get_externallines() as $externalline) {
	if(empty($usedE[$externalline['id']])){
		echo '<li id="lines_external:' . $externalline['id'] . '">' . $externalline['name'] . '</li>';
echo "\n";
	}
}
echo '</ul>';
echo '</div>'."\n";

echo '<div class="dragdrop">';
echo fpbx_label('Assigned Extensions', 'Displays a listing of extensions, ordered by Line number, beginning with the first line, assigned to the phone.');
echo '<ul id="lines" class="ext ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';
if (!empty($devices['lines'])) foreach( $devices['lines'] as $device){
	echo '<li id="lines_' . $device['extension'] . '">' . $device['extension'] . '</li>';
}


$externals = $digium_phones->get_externallines();

if (!empty($devices['externallines'])) foreach($devices['externallines'] as $external){
	foreach($externals as $vals){
		if($vals['id'] == $external['externallineid']){$name = $vals['name'];}
	}
	echo '<li id="lines_external:' . $external['externallineid'] . '">' . $name . '</li>';
}
echo '</ul>';
echo '</div>';
echo '</div>'."\n"; // dragdropFrame
echo '<div style="clear:both;"></div>'."\n";

//phonebooks
$phonebooks = $digium_phones->get_phonebooks();
if (!empty($devices['phonebooks'])) foreach($devices['phonebooks'] as $pb){
	$phonebooksSelected[$pb['phonebookid']] = $pb['phonebookid'];
}
echo '<div class="dragdropFrame">';
echo '<div class="dragdrop">';
echo fpbx_label('Available Phonebooks', 'Displays a listing of phonebooks that may be assigned to the Phone\'s Contacts lists. More than one phonebook may be assigned to a phone.');
echo '<ul id="availablePhonebooks" class="pb ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';
foreach ($phonebooks as $pb){
	if(empty($phonebooksSelected[$pb['id']])){
		echo '<li id="devicephonebooks_' . $pb['id'] . '">' . $pb['name'] . '</li>';
	}
}
echo '</ul>';
echo '</div>'."\n";
echo '<div class="dragdrop">';
echo fpbx_label('Assigned Phonebooks', 'Displays a listing of phonebooks currently assigned to a Phone. By default, an Internal Phonebook, containing a listing of all system extensions, is assigned to the phone.');
echo '<ul id="devicephonebooks" class="pb ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';
if(isset($phonebooksSelected)){
	foreach( $phonebooksSelected as $phonebook){
		echo '<li id="devicephonebooks_' . $phonebook . '">' . $phonebooks[$phonebook]['name'] . '</li>';
	}
}
echo '</ul>';
echo '</div>';
echo '</div>'."\n"; // dragdropFrame
echo '<div style="clear:both;"></div>'."\n";

$rapiddial = '<select id="rapiddial" name="rapiddial"><option value="">(none)</option>';
foreach($phonebooks as $pb){
	$rapiddial .= '<option value="' . $pb['id'] . '" ' . ($devices['settings']['rapiddial'] == $pb['id'] ? 'selected' : '') . '>' . $pb['name'] . '</option>';
}
$rapiddial .= '</select>';
$table->add_row(array( 'data' => fpbx_label('Rapid Dial Key Phonebook:', 'Sets which Phonebook will be assigned to a Phone\'s Rapid Dial (BLF) keys.')),
				array( 'data' => $rapiddial));
$table->add_row(array( 'data' => fpbx_label('Name Format:', 'Sets the name format used for Rapid Dial keys and Contacts application. Defaults to "FirstName LastName" but may be changed to "LastName, FirstName.')),
				array( 'data' => '<select id="name_format" name="name_format">
				<option value="first_last" ' . ($devices['settings']['name_format'] == 'first_last' ? 'selected' : '') . '>FirstName LastName (Default)</option>
				<option value="last_first" ' . ($devices['settings']['name_format'] == 'last_first' ? 'selected' : '') . '>LastName, FirstName</option></select>'));

$tz = '<select id="timezone" name="timezone">';
/* DateTimeZone::listIdentifiers has been in PHP since 2006 */
foreach (DateTimeZone::listIdentifiers() as $tzid) {
	$tz .= '<option value="'.$tzid.'">'.$tzid.'</option>'."\n";
}
$tz .= '<option value="' . $devices['settings']['timezone'] . '">' . $devices['settings']['timezone'] . '&nbsp;</option>';
$tz .= '</select>';
$table->add_row(array( 'data' => fpbx_label('Timezone:', 'Sets the timezone to be used for the phone.')),
				array( 'data' => $tz));

echo $table->generate();
$table->clear();

//Networks
foreach ($digium_phones->get_networks() as $network) {
	$networks[$network['id']] = $network['name'];
}
if (!empty($devices['networks'])) foreach($devices['networks'] as $net){
	$networksSelected[$net['networkid']] = $net['networkid'];
}
echo '<div class="dragdropFrame">';
echo '<div class="dragdrop">';
echo fpbx_label('Available Networks', 'Displays a listing of networks that may be assigned to the phone. More than one network may be assigned to a phone.');
echo '<ul id="availableNetworks" class="networks ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';
foreach ($networks as $id=>$net){
	if(empty($networksSelected[$id])){
		echo '<li id="devicenetworks_' . $id . '">' . $net . '</li>';
	}
}
echo '</ul>';
echo '</div>'."\n";
echo '<div class="dragdrop">';
echo fpbx_label('Assigned Networks', 'Displays a listing of networks currently assigned to a phone. By default, a Default Network is assigned to the phone.');
echo '<ul id="devicenetworks" class="networks ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';
if ($networksSelected) foreach( $networksSelected as $net){
	echo '<li id="devicenetworks_' . $net . '">' . $networks[$net] . '</li>';
}
echo '</ul>';
echo '</div>';
echo '</div>'."\n"; // dragdropFrame
echo '<div style="clear:both;"></div>'."\n";

//Logos
foreach ($digium_phones->get_logos() as $logo) {
	$logos[$logo['id']] = $logo['name'];
}
if(isset($devices['logos'])){
	foreach($devices['logos'] as $logo){
		$logosSelected[$logo['logoid']] = $logo['logoid'];
	}
}
echo '<div class="dragdropFrame">';
echo '<div class="dragdrop">';
echo fpbx_label('Available Logos', 'Displays a listing of logos that may be assigned to the phone. More than one logo may be assigned to a phone.');
echo '<ul id="availableLogos" class="logos ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';
if ($logos) foreach ($logos as $id=>$logo){
	if(empty($logosSelected[$id])){
		echo '<li id="devicelogos_' . $id . '">' . $logo . '</li>';
	}
}
echo '</ul>';
echo '</div>'."\n";
echo '<div class="dragdrop">';
echo fpbx_label('Assigned Logos', 'Displays a listing of logos currently assigned to a phone.');
echo '<ul id="devicelogos" class="logos ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';
if(isset($logosSelected)){
	foreach( $logosSelected as $logo){
		echo '<li id="devicelogos_' . $logo . '">' . $logos[$logo] . '</li>';
	}
}
echo '</ul>';
echo '</div>';
echo '</div>'."\n"; // dragdropFrame
echo '<div style="clear:both;"></div>'."\n";

//Alerts
foreach ($digium_phones->get_alerts() as $data) {
	$alerts[$data['id']] = $data['name'];
}
if(isset($devices['alerts'])){
	foreach($devices['alerts'] as $data){
		$alertsSelected[$data['alertid']] = $data['alertid'];
	}
}
echo '<div class="dragdropFrame">';
echo '<div class="dragdrop">';
echo fpbx_label('Available Alerts', 'Displays a listing of alerts that may be assigned to the phone. More than one alert may be assigned to a phone.');
echo '<ul id="availableAlerts" class="alerts ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';
if ($alerts) foreach ($alerts as $id=>$name){
	if(empty($alertsSelected[$id])){
		echo '<li id="devicealerts_' . $id . '">' . $name . '</li>';
	}
}
echo '</ul>';
echo '</div>'."\n";
echo '<div class="dragdrop">';
echo fpbx_label('Assigned Alerts', 'Displays a listing of alerts currently assigned to a phone.');
echo '<ul id="devicealerts" class="alerts ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';
if (isset($alertsSelected)){
	foreach( $alertsSelected as $id){
		echo '<li id="devicealerts_' . $id . '">' . $alerts[$id] . '</li>';
	}
}
echo '</ul>';
echo '</div>';
echo '</div>'."\n"; // dragdropFrame
echo '<div style="clear:both;"></div>'."\n";

//Ringtones
foreach ($digium_phones->get_ringtones() as $data) {
	if ($data['id'] > 0 ) {
		$ringtones[$data['id']] = $data['name'];
	}
}
if(isset($devices['ringtones'])){
	foreach($devices['ringtones'] as $data){
		$ringtonesSelected[$data['ringtoneid']] = $data['ringtoneid'];
	}
}
echo '<div class="dragdropFrame">';
echo '<div class="dragdrop">';
echo fpbx_label('Available Ringtones', 'Displays a listing of ringtones that may be assigned to the phone. More than one ringtone may be assigned to a phone.');
echo '<ul id="availableringtones" class="ringtones ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';
if ($ringtones) foreach ($ringtones as $id=>$name){
	if(empty($ringtonesSelected[$id])){
		echo '<li id="deviceringtones_' . $id . '">' . $name . '</li>';
	}
}
echo '</ul>';
echo '</div>'."\n";
echo '<div class="dragdrop">';
echo fpbx_label('Assigned Ringtones', 'Displays a listing of ringtones currently assigned to a phone.');
echo '<ul id="deviceringtones" class="ringtones ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';
if (isset($ringtonesSelected)){
	foreach( $ringtonesSelected as $id){
		echo '<li id="deviceringtones_' . $id . '">' . $ringtones[$id] . '</li>';
	}
}
echo '</ul>';
echo '</div>';
echo '</div>'."\n"; // dragdropFrame
echo '<div style="clear:both;"></div>'."\n";

if (!function_exists('presencestate_list_get')) {
	//Statuses
	foreach ($digium_phones->get_statuses() as $data) {
		$statuses[$data['id']] = $data['name'];
	}
	if (!empty($devices['statuses'])) {
		foreach($devices['statuses'] as $data){
			$statusesSelected[$data['statusid']] = $data['statusid'];
		}
	}
	echo '<div class="dragdropFrame">';
	echo '<div class="dragdrop">';
	echo fpbx_label('Available Statuses', 'Displays a listing of statuses that may be assigned to the phone. More than one status should be assigned to a phone.');
	echo '<ul id="availableStatuses" class="statuses ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';
	if (isset($statuses)) {
		foreach ($statuses as $id=>$name){
			if(empty($statusesSelected[$id])){
				echo '<li id="devicestatuses_' . $id . '">' . $name . '</li>';
			}
		}
	}
	echo '</ul>';
	echo '</div>'."\n";
	echo '<div class="dragdrop">';
	echo fpbx_label('Assigned Statuses', 'Displays a listing of statuses currently assigned to a phone.');
	echo '<ul id="devicestatuses" class="statuses ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';
	if(isset($statusesSelected)){
		foreach( $statusesSelected as $id){
			echo '<li id="devicestatuses_' . $id . '">' . $statuses[$id] . '</li>';
		}
	}
	echo '</ul>';
	echo '</div>';
	echo '</div>'."\n"; // dragdropFrame
	echo '<div style="clear:both;"></div>'."\n";
}

//Custom Apps
foreach ($digium_phones->get_customapps() as $data) {
	$customapps[$data['id']] = $data['name'];
}
if (!empty($devices['customapps'])) foreach($devices['customapps'] as $data){
	$customappsSelected[$data['customappid']] = $data['customappid'];
}
echo '<div class="dragdropFrame">';
echo '<div class="dragdrop">';
echo fpbx_label('Available Custom Apps', 'Displays a listing of custom applications that may be assigned to the phone. More than one custom application may be assigned to a phone.');
echo '<ul id="availableCustomapps" class="customapps ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';
if(isset($customapps)){
	foreach ($customapps as $id=>$name){
		if(empty($customappsSelected[$id])){
			echo '<li id="devicecustomapps_' . $id . '">' . $name . '</li>';
		}
	}
}
echo '</ul>';
echo '</div>'."\n";
echo '<div class="dragdrop">';
echo fpbx_label('Assigned Custom Apps', 'Displays a listing of custom applications currently assigned to a phone.');
echo '<ul id="devicecustomapps" class="customapps ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';
if(isset($customappsSelected)){
	foreach( $customappsSelected as $id){
		echo '<li id="devicecustomapps_' . $id . '">' . $customapps[$id] . '</li>';
	}
}
echo '</ul>';
echo '</div>';
echo '</div>'."\n"; // dragdropFrame
echo '<div style="clear:both;"></div>'."\n";

$table->add_row(array( 'data' => fpbx_label('Enable Call Recording:', 'Enables or Disables Call Recording. If disabled, the Record softkey will not show for in-progress calls.')),
				array( 'data' => '<select id="record_own_calls" name="record_own_calls">
					<option value="yes" ' . ($devices['settings']['record_own_calls'] == 'yes' ? 'selected' : '') . '>Enabled (Default)</option>
					<option value="no" ' . ($devices['settings']['record_own_calls'] == 'no' ? 'selected' : '') . '>Disabled</option></select>'));

$table->add_row(array( 'data' => fpbx_label('Enable Send to Voicemail:', 'Enables or Disables the Send VM softkey. If disabled, the Send VM softkey will not show for incoming calls.')),
				array( 'data' => '<select id="send_to_vm" name="send_to_vm">
					<option value="yes" ' . ($devices['settings']['send_to_vm'] == 'yes' ? 'selected' : '') . '>Enabled (Default)</option>
					<option value="no" ' . ($devices['settings']['send_to_vm'] == 'no' ? 'selected' : '') . '>Disabled</option></select>'));

$table->add_row(array( 'data' => fpbx_label('Require Pin for Voicemail:', 'Enables or Disables requiring the Phone PIN to access Voicemail. If disabled, Voicemail messages can be viewed and played without entering the PIN first.')),
				array( 'data' => '<select id="vm_require_pin" name="vm_require_pin">
					<option value="no" ' . ($devices['settings']['vm_require_pin'] == 'no' ? 'selected' : '') . '>Disabled (Default)</option>
					<option value="yes" ' . ($devices['settings']['vm_require_pin'] == 'yes' ? 'selected' : '') . '>Enabled</option></select>'));

$table->add_row(array( 'data' => fpbx_label('Rapid Dials on Unused Line Keys:', 'By default, Line keys that do not have an extension assigned to them will be configured with Rapid Dial keys. This behavior may be disabled such that Rapid Dial keys begin assignment on the sidecar keys.')),
				array( 'data' => '<select id="blf_unused_linekeys" name="blf_unused_linekeys">
					<option value="yes" ' . ($devices['settings']['blf_unused_linekeys'] == 'yes' ? 'selected' : '') . '>Enabled</option>
					<option value="no" ' . ($devices['settings']['blf_unused_linekeys'] == 'no' ? 'selected' : '') . '>Disabled (Default)</option></select>'));

$table->add_row(array( 'data' => fpbx_label('Seconds between NTP sync:', 'Defines the interval (in seconds) in which time is resynchronized via NTP. Defaults to "86400".')),
				array( 'data' => '<input type="text" id="ntp_resync" name="ntp_resync" value="' . $devices['settings']['ntp_resync'] . '" />'));

$table->add_row(array( 'data' => fpbx_label('Enable Web UI:', 'By default, when using the Digium Phone Module for Asterisk, the phone\'s built-in Web UI is disabled. To override this and enable the Web UI anyway, which may result in unpredictable behavior if user settings conflict with the settings provided by the DPMA, enable this option. Do not enable this unless you know what you\'re doing.')),
				array( 'data' => '<select id="web_ui_enabled" name="web_ui_enabled">
					<option value="no" ' . ($devices['settings']['web_ui_enabled'] == 'no' ? 'selected' : '') . '>Disabled (Default)</option>
					<option value="yes" ' . ($devices['settings']['web_ui_enabled'] == 'yes' ? 'selected' : '') . '>Enabled</option></select>'));

$packages = $digium_phones->get_firmware_manager()->get_packages();

$firmware = '';
$selected = ' selected';
foreach ($packages as $package) {
	$id = $package->get_unique_id();
	$firmware .= '<option value="' . $id . '"';
	if ($id==$devices['settings']['firmware_package_id']) {
		$firmware .= $selected;
		$selected = '';
	}
	$firmware .='>' . $package->get_name() . '</option>';
}
$firmware = '<select id="firmware_package_id" name="firmware_package_id"><option value=""'.$selected.'>None</option>'.$firmware.'</select>';			

$table->add_row(array( 'data' => fpbx_label('Select Firmware:', 'Pick the firmware to load on the phone.')),
				array( 'data' => $firmware));

$localeOptions = '<select id="active_locale" name="active_locale"><option value="" selected>&nbsp;</option>';
$locales = $digium_phones->get_locales();
foreach ($locales as $locale) {
	$localeOptions .= '<option value="' . $locale . '">' . $locale . '</option>';
}
$localeOptions .= '</select>';
$table->add_row(array( 'data' => fpbx_label('Active Locale:', 'Set the default active locale')),
				array( 'data' => $localeOptions));

$ringtoneOptions = '<select id="active_ringtone" name="active_ringtone"><option value="">&nbsp;</option>';
foreach ($digium_phones->get_ringtones() as $ringtone) {
	$ringtoneOptions .= '<option value="' . $ringtone['id'] . '">' . $ringtone['name'] . '</option>';
}
$ringtoneOptions .= '</select>';
$table->add_row(array( 'data' => fpbx_label('Default Ringtone:', 'Sets the default ringtone for a phone. In order to play a custom Ringtone by default, that Ringtone must be loaded onto a phone using the Ringtones option.')),
				array( 'data' => $ringtoneOptions));
	
$table->add_row(array( 'data' => fpbx_label('Phone Admin Password:', 'Sets a custom password for the phone\'s web interface, if enabled. Note that the web interface is disabled by default, as its use can run in conflict to use of the phones with the DPMA and result in unexpected behavior. Do not enable the phone\'s web interface unless you know what you\'re doing.')),
				array( 'data' => '<input type="text" id="login_password" name="login_password"/>'));
				
$table->add_row(array( 'data' => fpbx_label('Accept Only Local Calls:', 'Sets whether the phone will accept calls only from the PBX, or instead from any address.')),
				array( 'data' => '<select id="accept_local_calls" name="accept_local_calls">
					<option value="host" ' . ($devices['settings']['accept_local_calls'] == 'host' ? 'selected' : '') . '>Enabled (Default)</option>
					<option value="any" ' . ($devices['settings']['accept_local_calls'] == 'any' ? 'selected' : '') . '>Disabled</option></select>'));

$table->add_row(array( 'data' => fpbx_label('Enable Call Waiting Tone:', 'If disabled, the phone will not play a call waiting tone when it receives a new call while already on a call.')),
                                array( 'data' => '<select id="call_waiting_tone" name="call_waiting_tone">
                                        <option value="yes" ' . ($devices['settings']['call_waiting_tone'] == 'yes' ? 'selected' : '') . '>Enabled (Default)</option>
                                        <option value="no" ' . ($devices['settings']['call_waiting_tone'] == 'no' ? 'selected' : '') . '>Disabled</option>
				</select>'));

$table->add_row(array( 'data' => fpbx_label('Phone Can Override Preferences:', 'Defines whether or not the phone will be able to override any server-set preferences. By default, any non-default phone preferences set by the server can be overridden by the phone. This option enables the administrator to disable that capability by removing the user\'s preference option for items that the administrator sets to a non-default.')),
				array( 'data' => '<select id="lock_preferences" name="lock_preferences">
					<option value="no" ' . ($devices['settings']['lock_preferences'] == 'no' ? 'selected' : '') . '>Enabled (Default)</option>
					<option value="yes" ' . ($devices['settings']['lock_preferences'] == 'yes' ? 'selected' : '') . '>Disabled</option></select>'));
				
$table->add_row(array( 'data' => fpbx_label('Brightness Level:', 'Sets the LCD screen brightness, defaults to 5.')),
				array( 'data' => '<select id="brightness" name="brightness">
					<option value="" ' . ($devices['settings']['brightness'] == '' ? 'selected' : '') . '>Default</option>
					<option value="0" ' . ($devices['settings']['brightness'] == '0' ? 'selected' : '') . '>0</option>
					<option value="1" ' . ($devices['settings']['brightness'] == '1' ? 'selected' : '') . '>1</option>
					<option value="2" ' . ($devices['settings']['brightness'] == '2' ? 'selected' : '') . '>2</option>
					<option value="3" ' . ($devices['settings']['brightness'] == '3' ? 'selected' : '') . '>3</option>
					<option value="4" ' . ($devices['settings']['brightness'] == '4' ? 'selected' : '') . '>4</option>
					<option value="5" ' . ($devices['settings']['brightness'] == '5' ? 'selected' : '') . '>5</option>
					<option value="6" ' . ($devices['settings']['brightness'] == '6' ? 'selected' : '') . '>6</option>
					<option value="7" ' . ($devices['settings']['brightness'] == '7' ? 'selected' : '') . '>7</option>
					<option value="8" ' . ($devices['settings']['brightness'] == '8' ? 'selected' : '') . '>8</option>
					<option value="9" ' . ($devices['settings']['brightness'] == '9' ? 'selected' : '') . '>9</option>
					<option value="10" ' . ($devices['settings']['brightness'] == '10' ? 'selected' : '') . '>10</option></select>'));
				
				
$table->add_row(array( 'data' => fpbx_label('Contrast Level:', 'Sets the LCD screen contrast, defaults to 5.')),
				array( 'data' => '<select id="contrast" name="contrast">
					<option value="" ' . ($devices['settings']['contrast'] == '' ? 'selected' : '') . '>Default</option>
					<option value="0" ' . ($devices['settings']['contrast'] == '0' ? 'selected' : '') . '>0</option>
					<option value="1" ' . ($devices['settings']['contrast'] == '1' ? 'selected' : '') . '>1</option>
					<option value="2" ' . ($devices['settings']['contrast'] == '2' ? 'selected' : '') . '>2</option>
					<option value="3" ' . ($devices['settings']['contrast'] == '3' ? 'selected' : '') . '>3</option>
					<option value="4" ' . ($devices['settings']['contrast'] == '4' ? 'selected' : '') . '>4</option>
					<option value="5" ' . ($devices['settings']['contrast'] == '5' ? 'selected' : '') . '>5</option>
					<option value="6" ' . ($devices['settings']['contrast'] == '6' ? 'selected' : '') . '>6</option>
					<option value="7" ' . ($devices['settings']['contrast'] == '7' ? 'selected' : '') . '>7</option>
					<option value="8" ' . ($devices['settings']['contrast'] == '8' ? 'selected' : '') . '>8</option>
					<option value="9" ' . ($devices['settings']['contrast'] == '9' ? 'selected' : '') . '>9</option>
					<option value="10" ' . ($devices['settings']['contrast'] == '10' ? 'selected' : '') . '>10</option></select>'));

$table->add_row(array( 'data' => fpbx_label('Dim Backlight After Timeout:', 'Enables backlight dimming. When enabled, dims the screen after backlight timeout has been reached and phone is otherwise idle. Defaults to enabled.')),
				array( 'data' => '<select id="dim_backlight" name="dim_backlight">
					<option value="" ' . ($devices['settings']['dim_backlight'] == '' ? 'selected' : '') . '>Disabled (Default)</option>
					<option value="yes" ' . ($devices['settings']['dim_backlight'] == 'yes' ? 'selected' : '') . '>Enabled</option></select>'));
			
$table->add_row(array( 'data' => fpbx_label('Backlight Dim Level:', 'Sets the brightness level to which the phone dims when when Dim Backlight After Timeout is enabled, defaults to 2.')),
				array( 'data' => '<select id="backlight_dim_level" name="backlight_dim_level">
					<option value="" ' . ($devices['settings']['backlight_dim_level'] == '' ? 'selected' : '') . '>Default</option>
					<option value="0" ' . ($devices['settings']['backlight_dim_level'] == '0' ? 'selected' : '') . '>0</option>
					<option value="1" ' . ($devices['settings']['backlight_dim_level'] == '1' ? 'selected' : '') . '>1</option>
					<option value="2" ' . ($devices['settings']['backlight_dim_level'] == '2' ? 'selected' : '') . '>2</option>
					<option value="3" ' . ($devices['settings']['backlight_dim_level'] == '3' ? 'selected' : '') . '>3</option>
					<option value="4" ' . ($devices['settings']['backlight_dim_level'] == '4' ? 'selected' : '') . '>4</option>
					<option value="5" ' . ($devices['settings']['backlight_dim_level'] == '5' ? 'selected' : '') . '>5</option>
					<option value="6" ' . ($devices['settings']['backlight_dim_level'] == '6' ? 'selected' : '') . '>6</option>
					<option value="7" ' . ($devices['settings']['backlight_dim_level'] == '7' ? 'selected' : '') . '>7</option>
					<option value="8" ' . ($devices['settings']['backlight_dim_level'] == '8' ? 'selected' : '') . '>8</option>
					<option value="9" ' . ($devices['settings']['backlight_dim_level'] == '9' ? 'selected' : '') . '>9</option>
					<option value="10" ' . ($devices['settings']['backlight_dim_level'] == '10' ? 'selected' : '') . '>10</option></select>'));

$table->add_row(array( 'data' => fpbx_label('Backlight Timeout:', 'Sets the time, in seconds, before the backlight is set to Backlight Dim Level while phone is idle; setting to 0 disables backlight timeout, defaults to 0.')),
				array( 'data' => '<input type="text" id="backlight_timeout" name="backlight_timeout"/>'));
				
$table->add_row(array( 'data' => fpbx_label('Default Font Size:', 'Sets the default font size for the phone. Caution should be exercised when using this option as larger sizes will cause labels to overrun their allowed space. D40, D45 and D50 default to 10. D70 defaults to 11.')),
                                array( 'data' => '<input type="text" id="default_fontsize" name="default_fontsize"/>'));

$table->add_row(array( 'data' => fpbx_label('Display Missed Call Notifications:', 'Defines whether the phone will display a notification for missed calls or not.')),
				array( 'data' => '<select id="display_mc_notification" name="display_mc_notification">
					<option value="yes" ' . ($devices['settings']['display_mc_notification'] == 'yes' ? 'selected' : '') . '>Enabled (Default)</option>
					<option value="no" ' . ($devices['settings']['display_mc_notification'] == 'no' ? 'selected' : '') . '>Disabled</option></select>'));

$table->add_row(array( 'data' => fpbx_label('Ringer Volume:', 'Sets the ringer volume, defaults to 5.')),
				array( 'data' => '<select id="ringer_volume" name="ringer_volume">
					<option value="" ' . ($devices['settings']['ringer_volume'] == '' ? 'selected' : '') . '>Default</option>
					<option value="0" ' . ($devices['settings']['ringer_volume'] == '0' ? 'selected' : '') . '>0</option>
					<option value="1" ' . ($devices['settings']['ringer_volume'] == '1' ? 'selected' : '') . '>1</option>
					<option value="2" ' . ($devices['settings']['ringer_volume'] == '2' ? 'selected' : '') . '>2</option>
					<option value="3" ' . ($devices['settings']['ringer_volume'] == '3' ? 'selected' : '') . '>3</option>
					<option value="4" ' . ($devices['settings']['ringer_volume'] == '4' ? 'selected' : '') . '>4</option>
					<option value="5" ' . ($devices['settings']['ringer_volume'] == '5' ? 'selected' : '') . '>5</option>
					<option value="6" ' . ($devices['settings']['ringer_volume'] == '6' ? 'selected' : '') . '>6</option>
					<option value="7" ' . ($devices['settings']['ringer_volume'] == '7' ? 'selected' : '') . '>7</option>
					<option value="8" ' . ($devices['settings']['ringer_volume'] == '8' ? 'selected' : '') . '>8</option>
					<option value="9" ' . ($devices['settings']['ringer_volume'] == '9' ? 'selected' : '') . '>9</option>
					<option value="10" ' . ($devices['settings']['ringer_volume'] == '10' ? 'selected' : '') . '>10</option></select>'));
							
$table->add_row(array( 'data' => fpbx_label('Speaker Volume:', 'Sets the speaker volume, defaults to 5.')),
				array( 'data' => '<select id="speaker_volume" name="speaker_volume">
					<option value="" ' . ($devices['settings']['speaker_volume'] == '' ? 'selected' : '') . '>Default</option>
					<option value="0" ' . ($devices['settings']['speaker_volume'] == '0' ? 'selected' : '') . '>0</option>
					<option value="1" ' . ($devices['settings']['speaker_volume'] == '1' ? 'selected' : '') . '>1</option>
					<option value="2" ' . ($devices['settings']['speaker_volume'] == '2' ? 'selected' : '') . '>2</option>
					<option value="3" ' . ($devices['settings']['speaker_volume'] == '3' ? 'selected' : '') . '>3</option>
					<option value="4" ' . ($devices['settings']['speaker_volume'] == '4' ? 'selected' : '') . '>4</option>
					<option value="5" ' . ($devices['settings']['speaker_volume'] == '5' ? 'selected' : '') . '>5</option>
					<option value="6" ' . ($devices['settings']['speaker_volume'] == '6' ? 'selected' : '') . '>6</option>
					<option value="7" ' . ($devices['settings']['speaker_volume'] == '7' ? 'selected' : '') . '>7</option>
					<option value="8" ' . ($devices['settings']['speaker_volume'] == '8' ? 'selected' : '') . '>8</option>
					<option value="9" ' . ($devices['settings']['speaker_volume'] == '9' ? 'selected' : '') . '>9</option>
					<option value="10" ' . ($devices['settings']['speaker_volume'] == '10' ? 'selected' : '') . '>10</option></select>'));				
				
$table->add_row(array( 'data' => fpbx_label('Handset Volume:', 'Sets the handset volume, defaults to 5.')),
				array( 'data' => '<select id="handset_volume" name="handset_volume">
					<option value="" ' . ($devices['settings']['handset_volume'] == '' ? 'selected' : '') . '>Default</option>
					<option value="0" ' . ($devices['settings']['handset_volume'] == '0' ? 'selected' : '') . '>0</option>
					<option value="1" ' . ($devices['settings']['handset_volume'] == '1' ? 'selected' : '') . '>1</option>
					<option value="2" ' . ($devices['settings']['handset_volume'] == '2' ? 'selected' : '') . '>2</option>
					<option value="3" ' . ($devices['settings']['handset_volume'] == '3' ? 'selected' : '') . '>3</option>
					<option value="4" ' . ($devices['settings']['handset_volume'] == '4' ? 'selected' : '') . '>4</option>
					<option value="5" ' . ($devices['settings']['handset_volume'] == '5' ? 'selected' : '') . '>5</option>
					<option value="6" ' . ($devices['settings']['handset_volume'] == '6' ? 'selected' : '') . '>6</option>
					<option value="7" ' . ($devices['settings']['handset_volume'] == '7' ? 'selected' : '') . '>7</option>
					<option value="8" ' . ($devices['settings']['handset_volume'] == '8' ? 'selected' : '') . '>8</option>
					<option value="9" ' . ($devices['settings']['handset_volume'] == '9' ? 'selected' : '') . '>9</option>
					<option value="10" ' . ($devices['settings']['handset_volume'] == '10' ? 'selected' : '') . '>10</option></select>'));

$table->add_row(array( 'data' => fpbx_label('Headset Volume:', 'Sets the headset volume, defaults to 5.')),
				array( 'data' => '<select id="headset_volume" name="headset_volume">
					<option value="" ' . ($devices['settings']['headset_volume'] == '' ? 'selected' : '') . '>Default</option>
					<option value="0" ' . ($devices['settings']['headset_volume'] == '0' ? 'selected' : '') . '>0</option>
					<option value="1" ' . ($devices['settings']['headset_volume'] == '1' ? 'selected' : '') . '>1</option>
					<option value="2" ' . ($devices['settings']['headset_volume'] == '2' ? 'selected' : '') . '>2</option>
					<option value="3" ' . ($devices['settings']['headset_volume'] == '3' ? 'selected' : '') . '>3</option>
					<option value="4" ' . ($devices['settings']['headset_volume'] == '4' ? 'selected' : '') . '>4</option>
					<option value="5" ' . ($devices['settings']['headset_volume'] == '5' ? 'selected' : '') . '>5</option>
					<option value="6" ' . ($devices['settings']['headset_volume'] == '6' ? 'selected' : '') . '>6</option>
					<option value="7" ' . ($devices['settings']['headset_volume'] == '7' ? 'selected' : '') . '>7</option>
					<option value="8" ' . ($devices['settings']['headset_volume'] == '8' ? 'selected' : '') . '>8</option>
					<option value="9" ' . ($devices['settings']['headset_volume'] == '9' ? 'selected' : '') . '>9</option>
					<option value="10" ' . ($devices['settings']['headset_volume'] == '10' ? 'selected' : '') . '>10</option></select>'));

$table->add_row(array( 'data' => fpbx_label('Call Volume Persistent Across Calls:', 'If enabled, volume changes made during a call do not persist to the next call, defaults to disabled.')),
				array( 'data' => '<select id="reset_call_volume" name="reset_call_volume">
					<option value="" ' . ($devices['settings']['reset_call_volume'] == '' ? 'selected' : '') . '>Disabled (Default)</option>
					<option value="yes" ' . ($devices['settings']['reset_call_volume'] == 'yes' ? 'selected' : '') . '>Enabled</option></select>'));
	
$table->add_row(array( 'data' => fpbx_label('Prefer Handset to Headset:', 'Sets whether to use the headset, rather than the speaker, for answering all calls, defaults to disabled.')),
				array( 'data' => '<select id="headset_answer" name="headset_answer">
			<option value="" ' . ($devices['settings']['headset_answer'] == '' ? 'selected' : '') . '>Disabled (Default)</option>
			<option value="yes" ' . ($devices['settings']['headset_answer'] == 'yes' ? 'selected' : '') . '>Enabled</option></select>'));					
echo $table->generate();
$table->clear();

?>
	<input type="button" value="Cancel" onclick="location.href='config.php?type=setup&display=digium_phones&digium_phones_form=phones_edit'"/>
	<input type="hidden" name="editdevice_submit" value="Save"/>
	<input type="submit" name="editdevice_submit" value="Save"/>

</div>
</form>
