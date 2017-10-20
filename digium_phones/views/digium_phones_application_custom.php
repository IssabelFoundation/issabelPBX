<h2>Custom</h2>
<hr />

<form name="digium_phones_customapps" method="post" enctype="multipart/form-data" action="config.php?type=setup&display=digium_phones&digium_phones_form=application_custom_edit<?php echo ($editcustomapp != null && $editcustomapp != 0)?"&customappid=".$editcustomapp:""?>">
<script>
$().ready(function() {
<?php
$customapps = $digium_phones->get_customapps();

if (isset($_GET['customappid']) and !isset($_GET['deletecustomapp_submit'])) {
	$editcustomapp = htmlspecialchars($_GET['customappid']);
}

if ($editcustomapp != null) {
	if ($editcustomapp == 0) {
?>
		$('#customappname').val("New Custom Application");
<?php
	} else {
?>
		$('#customappname').val($('#customapp<?php echo $editcustomapp?>name').text());
<?php
	}
?>
	$('#customappid').val(<?php echo $editcustomapp?>);

	$('div[id=editingcustomapp]').show();
<?php
}

foreach ($customapps as $customappid=>$customapp) {
	if ($editcustomapp == $customappid) {
		foreach ($customapp['settings'] as $key=>$val) {
?>
			if ($('#<?php echo $key?>') != null && $('#<?php echo $key?>').html() != null) {
				$('#<?php echo $key?>').val('<?php echo $val?>');
			} else {
				addEntry("<?php echo $key?>=<?php echo $val?>");
			}
<?php
		}
	}
}
?>
});
$('form').submit(function() {
	if ($.trim($('#customappname').val()).length <= 0) {
		alert("Custom Application Name cannot be blank.");
		return false;
	}

	var files = $('#customappfile').prop('files');
	if (files[0] == null) {
		alert('You must specify a .zip file.');
		return false;
	}
	if (files[0].type != 'application/x-zip-compressed' && files[0].type!='application/zip') {
 		alert('You must specify a .zip format file.');
 		return false;
 	}
	if (files[0].size <= 0) {
		alert('The file specified is too small.');
		return false;
	}

	$('#entries').attr("multiple", "multiple");
 	$('#entries option').each(function() {
 		$(this).attr("selected", "selected");
	});
});

function addEntry(entry) {
	newentry = $('<option value="'+entry+'">'+entry+'</option>').appendTo('#entries');
	newentry.attr("selected", "selected");

	return true;
}
function delEntry(entry) {
	kv = entry.split('=');
	$('#settingkey').val(kv[0]);
	$('#settingval').val(kv[1]);

	$('#entries option[value="'+entry+'"]').remove()
	return true;
}
</script>
	<input type="button" value="Add Custom Application" onclick="location.href='config.php?type=setup&display=digium_phones&digium_phones_form=application_custom_edit&customappid=0'" />

<?php
function typeDisplay($type) {
	switch ($type) {
		case "available":
			return "Available";
		case "dnd":
			return "Do Not Disturb";
		case "away":
			return "Away";
		case "xa":
			return "Extended Away";
		case "chat":
			return "Prefer Chat";
		default:
			return "Unavailable";
	}
}
?>

	<table style="border-collapse:collapse; border-style:outset; border-width: 1px; ">
		<tr>
			<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Custom Application Name<span>A name for this Custom Application.</span></a></th>
			<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Actions<span>"Edit" provides additional editing control over a selected Custom Application. "Delete" removes the specified Custom Application.</span></a></th>
		</tr>
<?php
foreach ($customapps as $customappid=>$customapp) {
?>
		<tr>
			<td style="vertical-align: middle; width: 200px; border-style:inset; border-width: 1px; ">
				<span id="customapp<?php echo $customappid?>name"><?php echo $customapp['name']?></span>
			</td>
			<td style="vertical-align: middle; border-style:inset; border-width:1px; white-space: nowrap; ">
				<input type="button" value="Edit Custom Application" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=application_custom_edit&customappid=<?php echo $customappid?>'">
				<input type="button" value="Delete" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=application_custom_edit&deletecustomapp_submit=Delete&customappid=<?php echo $customappid?>'">
			</td>
		</tr>
<?php
}
?>
	</table>

	<hr />

<?php
$table = new CI_Table();

$table->add_row(array( 'data' => fpbx_label('Custom Application Name:', 'A named identifier for the Custom Application.  This name must match the name in app.json'), 'class' => 'template_name'),
	array( 'data' => '<input type="hidden" id="customappid" name="customappid" /><input type="text" id="customappname" name="customappname"/>')
	);
	
$table->add_row(array( 'data' => fpbx_label('File:', ''), 'class' => 'template_name'),
	array( 'data' => '<input type="file" id="customappfile" name="customappfile" />')
	);
	
$table->add_row(array( 'data' => fpbx_label('Automatically Start Application:', 'Controls whether the Custom Application starts on phone boot.'), 'class' => 'template_name'),
	array( 'data' => '<select id="autostart" name="autostart">
			<option value="no" selected>No (Default)</option>
			<option value="yes">Yes</option>
		</select>')
	);
	
echo $table->generate();
$table->clear();

?>
	<br />

	<table>
		<tr>
			<th><a href="#" class="info">Settings:<span>Allows for entry of custom keypair values to be applied to an application to be loaded onto a phone.  A weather application, for example, might include a key of postal_code and a value of 90210.</span></a></th>
			<th></th>
			<th><a href="#" class="info">Assigned Settings:<span>Displays a listing of settings currently assigned to a Custom Application.</span></a></th>
		</tr>
		<tr>
			<td>
				<div class="setting">
					<label for="settingkey"><a href="#" class="info">Custom Key:<span>A custom key to be sent to the specified Custom Application.</span></a>&nbsp;&nbsp;&nbsp;</label>
					<input type="text" id="settingkey" />
				</div>
				<div class="setting">
					<label for="settingval"><a href="#" class="info">Custom Value:<span>A custom value to be sent to the specified Custom Application.</span></a></label>
					<input type="text" id="settingval" />
				</div>
			</td>
			<td>
				<img alt="Remove" src="images/resultset_left.png" onclick="delEntry($('#entries').val());" style="width: 24px; height: 24px; "/>
				<img alt="Add" src="images/resultset_right.png" onclick="addEntry($('#settingkey').val()+'='+$('#settingval').val());$('#settingkey').val('');$('#settingval').val('');" style="width: 24px; height: 24px; " />
			</td>
			<td>
				<select size="8" id="entries" name="entries[]" style="width: 200px; " ondblclick="delEntry($('#entries').val());">
				</select>
			</td>
		</tr>
	</table>

	<br />

	<input type="button" value="Cancel" onclick="location.href='config.php?type=setup&display=digium_phones&digium_phones_form=application_custom_edit'"/>
	<input type="submit" name="editcustomapp_submit" value="Save"/>
</form>
