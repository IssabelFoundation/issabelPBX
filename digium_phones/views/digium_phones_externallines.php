<h2>External Lines</h2>
<hr />

<form name="digium_phones_externallines" method="post" action="config.php?type=setup&display=digium_phones&digium_phones_form=externallines_edit">
<script>
$().ready(function() {
<?php
$externallines = $digium_phones->get_externallines();

if (isset($_GET['externalline']) and !isset($_GET['deleteexternalline_submit'])) {
	$editexternalline = htmlspecialchars($_GET['externalline']);
}

if ($editexternalline != null) {
	if ($editexternalline == 0) {
?>
		$('#linename').val("New External Line");
<?php
	} else {
?>
		$('#linename').val($('#line<?php echo $editexternalline?>name').text());
<?php
	}
?>
	$('#externalline').val(<?php echo $editexternalline?>);

	$('div[id=editingexternalline]').show();
<?php
}

foreach ($externallines as $externallineid=>$externalline) {
	if ($editexternalline == $externallineid) {
		foreach ($externalline['settings'] as $key=>$val) {
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
	if ($.trim($('#linename').val()).length <= 0) {
		alert('Line Name cannot be blank.');
		return false;
	}
	if ($.trim($('#userid').val()).length <= 0) {
		alert('User ID cannot be blank.');
		return false;
	}
});
</script>
<p>

<table style="border-collapse:collapse; border-style:outset; border-width: 1px; ">
<tr>
<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Line Name<span>Sets an identifier for the line.  This will be present in the phone's preferences menu and is used to manually select a line from the phone, if required.
</span></a></th>
<th style="border-style:inset; border-width:1px; "><a href="#" class="info">User ID<span>The user ID for this line.
</span></a></th>
<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Server Address<span>The address of the server currently in use for this external line.
</span></a></th>
<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Actions<span>"Edit" provides additional editing control over a selected line. "Delete" removes the specified line.</span></a></th>
</tr>
<?php
foreach ($externallines as $externallineid=>$externalline) {
?>
<tr>
<td style="width: 200px; border-style:inset; border-width: 1px; ">
	<span id="line<?php echo $externallineid?>name"><?php echo $externalline['name']?></span>
</td>
<td style="border-style:inset; border-width:1px; ">
	<?php echo $externalline['settings']['userid']?>
</td>
<td style="border-style:inset; border-width:1px; ">
	<?php echo $externalline['settings']['server_address']?>
</td>
<td style="border-style:inset; border-width:1px; white-space: nowrap; ">
	<input type="button" value="Edit" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=externallines_edit&externalline=<?php echo $externallineid?>'">
	<input type="button" value="Delete" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=externallines_edit&deleteexternalline_submit=Delete&externalline=<?php echo $externallineid?>'">
</td>
</tr>
<?php
}
?>
</table>

<p>
<input type="button" value="Add External Line" onclick="location.href='config.php?type=setup&display=digium_phones&digium_phones_form=externallines_edit&externalline=0'" />
<hr />
<div id="editingexternalline" style="display: none;">
<?php
$table = new CI_Table();
$table->add_row(array( 'data' => fpbx_label('Line Name', 'Sets an identifier for the line.  This will be present in the phone\'s preferences menu and is used to manually select a line from the phone, if required.'), 'class' => 'template_name'),
	array( 'data' => '<input type="text" id="linename" name="linename" />	<input type="hidden" id="externalline" name="externalline" />')
	);
$table->add_row(array( 'data' => fpbx_label('User ID', 'The user identifier to be associated with this external line when contacting the remote server.'), 'class' => 'template_name'),
	array( 'data' => '<input type="text" id="userid" name="userid" />')
	);
$table->add_row(array( 'data' => fpbx_label('Auth Name', 'The name used as an additional authentication parameter to the remote server for this external line. This can normally be left blank.'), 'class' => 'template_name'),
	array( 'data' => '<input type="text" id="authname" name="authname" />')
	);
$table->add_row(array( 'data' => fpbx_label('Secret', 'The password to use for authenticating to the remote server for this external line.'), 'class' => 'template_name'),
	array( 'data' => '<input type="text" id="secret" name="secret" />')
	);
$table->add_row(array( 'data' => fpbx_label('Server Address', 'The address of the server this external line will use to place calls.'), 'class' => 'template_name'),
	array( 'data' => '<input type="text" id="server_address" name="server_address" />')
	);
$table->add_row(array( 'data' => fpbx_label('Server Port', 'The port on which to contact the server for this external line.'), 'class' => 'template_name'),
	array( 'data' => '<input type="text" id="server_port" name="server_port" value="5060"/>')
	);
$table->add_row(array( 'data' => fpbx_label('Transport', 'The SIP transport to use for this external line.'), 'class' => 'template_name'),
	array( 'data' => '<select id="server_transport" name="server_transport"><option value="udp" selected>UDP (Default)</option><option value="tcp">TCP</option></select>')
	);
$table->add_row(array( 'data' => fpbx_label('Caller ID', 'The Caller ID to be presented to the called party when using this external line.'), 'class' => 'template_name'),
	array( 'data' => '<input type="text" id="callerid" name="callerid" />')
	);
$table->add_row(array( 'data' => fpbx_label('Register', 'Whether to register this external line to the specified server.'), 'class' => 'template_name'),
	array( 'data' => '<select id="register" name="register"><option value="yes" selected>Yes (Default)</option><option value="no">No</option></select>')
	);
	
	
echo $table->generate();
$table->clear();

echo '<hr>';
echo '<table style="border-spacing: 4px;"><tbody>';
echo '<tr class="guielToggle" data-toggle_class="advanced"><td><h5><span class="guielToggleBut">+ </span>Advanced</h5><hr></td><td></td></tr>';

echo '<tr class="advanced"><td><a href="#" class="info">Secondary Server Address<span>The address of the secondary server this external line will use to place calls in the event that the server configured above can not be reached.</span></a></td><td>
			<input type="text" id="secondary_server_address" name="secondary_server_address" /></td></tr>';
echo '<tr class="advanced"><td><a href="#" class="info">Secondary Server Port<span>The port on which to contact the secondary server for this external line.</span></a></td><td>
			<input type="text" id="secondary_server_port" name="secondary_server_port" value="5060"/></td></tr>';
echo '<tr class="advanced"><td><a href="#" class="info">Secondary Server Transport<span>The SIP transport to use for the secondary server for this external line.</span></a></td><td>
			<select id="secondary_server_transport" name="secondary_server_transport">
				<option value="udp" selected>UDP (Default)</option>
				<option value="tcp">TCP</option>
			</select></td></tr></table>';
?>
	<br />

	<input type="button" value="Cancel" onclick="location.href='config.php?type=setup&display=digium_phones&digium_phones_form=externallines_edit'"/>
	<input type="submit" name="editexternalline_submit" value="Save"/>
</div>
</form>
