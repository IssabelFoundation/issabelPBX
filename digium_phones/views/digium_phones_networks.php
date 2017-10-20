<h2>Networks</h2>
<hr />

<form name="digium_phones_networks" method="post" action="config.php?type=setup&display=digium_phones&digium_phones_form=networks_edit">
<script>
$().ready(function() {
<?php
$networks = $digium_phones->get_networks();

if (isset($_GET['network']) and !isset($_GET['deletenetwork_submit'])) {
	$editnetwork = htmlspecialchars($_GET['network']);
}

if ($editnetwork != null) {
	if ($editnetwork == 0) {
?>
		$('#networkname').val("New Network");
<?php
	} else {
?>
		$('#networkname').val($('#network<?php echo $editnetwork?>name').text());
<?php
	}
?>
	$('#network').val(<?php echo $editnetwork?>);

	$('div[id=editingnetwork]').show();
<?php
}

foreach ($networks as $networkid=>$network) {
	if ($editnetwork == $networkid) {
		foreach ($network['settings'] as $key=>$val) {
?>
			if ($('#<?php echo $key?>') != null) {
				$('#<?php echo $key?>').val('<?php echo $val?>');
			}
<?php
		}
	}
}
?>
});
$('form').submit(function() {
	if ($.trim($('#networkname').val()).length <= 0) {
		alert('Network Name cannot be blank.');
		return false;
	}
	if ($.trim($('#cidr').val()).length <= 0) {
		alert('CIDR cannot be blank.');
		return false;
	}
});
</script>
<input type="button" value="Add network" onclick="location.href='config.php?type=setup&display=digium_phones&digium_phones_form=networks_edit&network=0'" />
<p>

<table style="border-collapse:collapse; border-style:outset; border-width: 1px; ">
<tr>
<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Network Name<span>A Network's named identifier.  This will be present in the phone's preferences menu and is used to manually select a network from the phone, if required.
</span></a></th>
<th style="border-style:inset; border-width:1px; width:75px; "><a href="#" class="info">CIDR<span>A Network's address, represented using CIDR notation.  For more information about CIDR notation, please see http://en.wikipedia.org/wiki/CIDR_notation.  If the phone, when it boots, discovers that its IP address matches the CIDR of an Network it has been assigned, then it will use those Network settings for accessing the PBX.
</span></a></th>
<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Actions<span>"Edit" provides additional editing control over a selected network. "Delete" removes the specified network.</span></a></th>
</tr>
<?php
foreach ($networks as $networkid=>$network) {
?>
<tr>
<td style="width: 200px; border-style:inset; border-width: 1px; ">
	<span id="network<?php echo $networkid?>name"><?php echo $network['name']?></span>
</td>
<td style="border-style:inset; border-width:1px; ">
	<?php echo $network['settings']['cidr']?>
</td>
<td style="border-style:inset; border-width:1px; white-space: nowrap; ">
	<input type="button" value="Edit" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=networks_edit&network=<?php echo $networkid?>'">
<?php
	if ($networkid != -1) {
?>
	<input type="button" value="Delete" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=networks_edit&deletenetwork_submit=Delete&network=<?php echo $networkid?>'">
<?php
	}
?>
</td>
</tr>
<?php
}
?>
</table>

<hr />

<div id="editingnetwork" style="display: none;">
	<input type="hidden" id="network" name="network" />
	<?php
	dbug($networks[$editnetwork]);
	$table = new CI_Table();
	$table->add_row(fpbx_label('Network Name:', 'Sets an identifier for the network.  This will be present in the phone\'s preferences menu and is used to manually select a network from the phone, if required.'),
			array( 'data' => '<input type="text" id="networkname" name="networkname" value="' . ($editnetwork == -1 ? "readonly" : $networks[$editnetwork]['name']) . '" />'));
	$table->add_row(fpbx_label('Network CIDR:', 'Defines a network address, represented using CIDR notation.  For more information about CIDR notation, please see http://en.wikipedia.org/wiki/CIDR_notation.  If the phone, when it boots, discovers that its IP address matches the CIDR of an Network it has been assigned, then it will use those Network settings for accessing the PBX.'),
			array( 'data' => '<input type="text" id="cidr" name="cidr" value="' . ($editnetwork == -1 ? "readonly" : $networks[$editnetwork]['settings']['cidr']) . '" />'));
	$table->add_row(fpbx_label('Registration Address:', 'Sets the SIP hostname or IP address used by the phone to access the PBX.'),
			array( 'data' => '<input type="text" id="registration_address" name="registration_address" value="' . ($editnetwork == -1 ? "readonly" : $networks[$editnetwork]['settings']['registration_address']) . '" />'));
	$table->add_row(fpbx_label('Registration Port:', 'Sets the port used by the phone to access the PBX.'),
			array( 'data' => '<input type="text" id="registration_port" name="registration_port" value="' . ($editnetwork == -1 ? "readonly" : $networks[$editnetwork]['settings']['registration_port']) . '" />'));
	
	
	
	echo $table->generate();
	$table->clear();
	echo '<hr>';
	echo '<table style="border-spacing: 4px;"><tbody>';
	echo '<tr class="guielToggle" data-toggle_class="advanced"><td><h5><span class="guielToggleBut">+ </span>Advanced</h5><hr></td><td></td></tr>';
	
	echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">File URL Prefix:<span>Defines the URL prefix used by the phone to retrieve firmware and ringtones.</span></a></td><td>
		<input type="text" id="file_url_prefix" name="file_url_prefix" /></td></tr>';
	echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">Alternate Registration Address:<span>Optional.  Sets an alternate host to which the phone will register itself simultaneously.  DPMA Application function is not maintained with the alternate host, but basic call functionality is maintained.</span></a></td><td>
		<input type="text" id="alternate_registration_address" name="alternate_registration_address" /></td></tr>';	
	echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">Alternate Registration Port:<span>Optional. Sets the port for the Alternate Registration Address.</span></a></td><td>
		<input type="text" id="alternate_registration_port" name="alternate_registration_port" /></td></tr>';
	echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">NTP Server:<span>Defines the NTP server the phone will synchronize to in order to maintain its time.</span></a></td><td>
		<input type="text" id="ntp_server" name="ntp_server" /></td></tr>';
		
	echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">Syslog Level:<span>If enabled, sets a logging level used by the phone to output syslog messages.</span></a></td><td>
		<select id="syslog_level" name="syslog_level">
				<option value="" selected>Disabled (Default)</option>
				<option value="debug">Debug</option>
				<option value="error">Error</option>
				<option value="warn">Warning</option>
				<option value="information">Infomation</option>
			</select></td></tr>';	
	echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">Syslog Server:<span>If Syslog is enabled, sets the server to which syslog messages are sent by the phone.</span></a></td><td>
		<input type="text" id="syslog_server" name="syslog_server" /></td></tr>';
	echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">Syslog Port:<span>If Syslog is enabled, sets the port to which syslog messages are sent by the phone.</span></a></td><td>
		<input type="text" id="syslog_port" name="syslog_port" /></td></tr>';
	echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">Network VLAN Discovery:<span>Digium phones default to VLAN discovery using LLDP.  If LLDP is not available on your switch, you may elect for Manual VLAN configuration, or VLANs may be disabled.</span></a></td><td>
		<select id="network_vlan_discovery_mode" name="network_vlan_discovery_mode">
				<option value="LLDP" selected>LLDP (Default)</option>
				<option value="NONE">None</option>
				<option value="MANUAL">Manual</option>
			</select></td></tr>';
	echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">Network VLAN ID:<span>If a Digium phone is configured for manual VLAN Discovery, sets the VLAN ID to which the phone will bind.</span></a></td><td>
		<input type="text" id="network_vlan_id" name="network_vlan_id" /></td></tr>';
	echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">Network QoS:<span>If a Digium phone is configured for manual VLAN Discovery, sets the QoS bit for the phones traffic to the network.</span></a></td><td>
			<input type="text" id="network_vlan_qos" name="network_vlan_qos" /></td></tr>';
	echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">PC VLAN ID:<span>Sets the VLAN ID to which the phone will bind, for the PC port.</span></a></td><td>
		<input type="text" id="pc_vlan_id" name="pc_vlan_id" /></td></tr>';
	echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">PC QoS:<span>If a Digium phone is configured for manual VLAN Discovery, sets the QoS bit for traffic from the PC port to the network.</span></a></td><td>
		<input type="text" id="pc_qos" name="pc_qos" /></td></tr>';
	echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">Signalling DSCP:<span>Specifies the DSCP field of the DiffServ byte for SIP Signaling QoS, defaults to 24.</span></a></td><td>
		<input type="text" id="sip_dscp" name="sip_dscp" /></td></tr>';
	echo '<tr class="advanced"><td><a href="#" class="info" tabindex="-1">Media DSCP:<span>Specifies the DSCP field of the DiffServ byte for RTP Media QoS, defaults to 24.</span></a></td><td>
		<input type="text" id="rtp_dscp" name="rtp_dscp" /></td></tr>';
	echo '</table>';
	
	
	?>


	<input type="button" value="Cancel" onclick="location.href='config.php?type=setup&display=digium_phones&digium_phones_form=networks_edit'"/>
	<input type="submit" name="editnetwork_submit" value="Save"/>
</div>
</form>
