<?php
?>

<h2>Firmware Management</h2>
<hr />

<form name="digium_phones_firmware" method="post"
	action="config.php?type=setup&display=digium_phones&digium_phones_form=firmware_edit"
	enctype="multipart/form-data">
<script>
$().ready(function() {
<?php
	$firmware_package_id = null;
	$op_type = null;
	$selected_package = null;
	$firmware_manager = $digium_phones->get_firmware_manager();
	$firmware_manager->refresh_packages();
	if (count($error) > 0) {
?>
		alert("<?php echo implode('\n', $error)?>");
<?php
	}

	if (isset($_GET['firmware_package_id'])) {
		$firmware_package_id = $_GET['firmware_package_id'];
		$selected_package = $firmware_manager->get_package_by_id($firmware_package_id);
	}
	if (isset($_GET['optype'])) {
		$op_type = htmlspecialchars($_GET['optype']);
	}
	// Check if we need to display a form
	if ($op_type != null and $op_type !== 'delete_firmware') {
?>
		$('div[id=<?php echo $op_type?>]').show();
<?php
	}
?>
});
$('form').submit(function() {
<?php
	if ($selected_package != null and $op_type == "edit_firmware") {
?>
	if ($.trim($('#firmware_name').val()).length <= 0) {
		alert("Firmware Package name cannot be empty.");
		return false;
	}
<?php
	} else if ($op_type == 'upload_firmware') {
?>
	if ($.trim($('#upload_firmware_name').val()).length <= 0) {
		alert("Firmware Package name cannot be empty.");
		return false;
	}
<?php
	}
?>
});

function process_update_firmware() {
	urlStr = 'config.php?type=setup&display=digium_phones&update_firmware=check&quietmode=1';
	box = $('<div></div>')
		.html('<iframe frameBorder="0" src="'+urlStr+'"></iframe>')
		.dialog({
			title: 'Status',
			resizable: false,
			modal: true,
			position: ['center', 50],
			width: '400px',
			close: function (e) {
				close_update_firmware(true);
			}
		});
}

function close_update_firmware(goback) {
	box.dialog("destroy").remove();
	if (goback) {
		location.href = 'config.php?display=digium_phones&type=setup&digium_phones_form=firmware_edit';
	}
}

function perform_download(version) {
	box.dialog("destroy").remove();
	urlStr = 'config.php?type=setup&display=digium_phones&update_firmware=download&quietmode=1';
	urlStr = urlStr + '&version=' + version;
	box = $('<div></div>')
		.html('<iframe frameBorder="0" src="'+urlStr+'"></iframe>')
		.dialog({
			title: 'Status',
			resizable: false,
			modal: true,
			position: ['center', 50],
			width: '400px',
			close: function (e) {
				close_update_firmware(true);
			}
		});
}

</script>

<table style="width:450px; border-collapse:collapse; border-style:outset; border-width: 1px; ">
	<tr>
		<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Firmware Package<span>The name of the available firmware package.</span></a></th>
		<th style="border-style:inset; border-width:1px; width:75px; "><a href="#" class="info">Actions<span>"Edit" renames the specified firmware. "Info" shows information about the firmware. "Delete" removes the specified firmware.</span></a></th>
	</tr>
<?php
	$packages = $firmware_manager->get_packages();
	foreach ($packages as $package) {
?>
	<tr>
		<td style="vertical-align: top; width: 250px; border-style:inset; border-width: 1px; ">
			<span id="firmware_<?php echo $package->get_name()?>"><?php echo $package->get_name()?></span>
		</td>
		<td style="vertical-align: top; border-style:inset; border-width:1px; white-space: nowrap; ">
			<input type="button" value="Edit" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=firmware_edit&firmware_package_id=<?php echo $package->get_unique_id()?>&optype=edit_firmware'">
			<input type="button" value="Info" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=firmware_edit&firmware_package_id=<?php echo $package->get_unique_id()?>&optype=info_firmware'">
			<input type="button" value="Delete" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=firmware_edit&firmware_package_id=<?php echo $package->get_unique_id()?>&optype=delete_package'">
		</td>
	</tr>
<?php
	}
?>
</table>

<div class="btn_container">
	<input type="button" id="update_firmware" value="Check for Updates" onclick="process_update_firmware();"/>
	<!-- <input type="button" id="upload_firmware" value="Upload Firmware" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=firmware_edit&optype=upload_firmware'"/> -->
</div>

<!-- Hidden edit/info forms -->

<div id="edit_firmware" style="display: none; margin-right:auto;">
	<?php
	if ($selected_package !== NULL) {
	?>
	<input type="hidden" id="old_name" name="old_name" value="<?php echo $selected_package->get_name();?>"/>
	<input type="hidden" id="firmware_package_id" name="firmware_package_id" value="<?php echo $selected_package->get_unique_id();?>"/>
	<table>
		<tr>
			<td>
				<label style="float:left;width:80px;" for="firmware_name"><a href="#" class="info">Name:<span>The name of the firmware package.</span></a></label>
			</td>
			<td>
				<input type="text" id="firmware_name" name="firmware_name" value="<?php echo $selected_package->get_name();?>"/>
			</td>
		</tr>
		<tr/>
		<tr>
			<td/>
			<td>
				<div class="btn_container">
					<input type="submit" name="editfirmware_submit" value="Save"/>
				</div>
			</td>
		</tr>
	</table>
	<?php
	}
	?>
</div>

<div id="info_firmware" style="display: none;">
	<?php
	if ($selected_package !== NULL) {
	?>
	<table style="border-collapse:collapse; border-style:outset; border-width: 1px; ">
	<tr>
		<th style="border-style:inset; border-width:1px; ">Property</th>
		<th style="border-style:inset; border-width:1px; ">Value</th>
	</tr>
	<tr>
		<td>Name:</td>
		<td><?php echo $selected_package->get_name();?></td>
	</tr>
	<tr>
		<td>Location:</td>
		<td><?php echo $selected_package->get_file_path();?></td>
	</tr>
	<tr>
		<td>Version:</td>
		<td><?php echo $selected_package->get_version();?></td>
	</tr>
	<tr>
		<td>Devices:</td>
	<?php
		$devices = $digium_phones->get_devices();
		foreach ($devices as $device) {
			if ($device['settings']['firmware_package_id'] == $firmware_package_id) {
				?>
				<td><a href="config.php?type=setup&display=digium_phones&digium_phones_form=phones_edit&device=<?php echo $device['id']?>"><?php echo $device['name']?></a></td>
				<?php
			} else {
				?>
				<td/>
				<?php
			}
			?>
			</tr>
			<tr>
			<td/>
			<?php
		}
	?>
	<td/>
	</tr>
	<?php
	}
	?>
</div>

<div id="upload_firmware" style="display: none;">
	<div>
		<label for="upload_firmware_name"><a href="#" class="info">Firmware Name:<span>The name of the firmware being uploaded.</span></a></label>
		<input type="text" id="upload_firmware_name" name="upload_firmware_name" style="width:400px; "/>
	</div>
	<div>
		<label for="upload_firmware_location"><a href="#" class="info">Location:<span>The firmware package (.tar.gz) to upload.</span></a></label>
		<input type="file" id="upload_firmware_location" name="upload_firmware_location" style="width:400px; "/>
	</div>
	<div class="btn_container">
		<input type="submit" name="uploadfirmware_submit" value="Upload"/>
	</div>
</div>

</form>
