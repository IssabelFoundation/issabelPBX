<h2>Ringtones</h2>
<hr />

<script type="text/javascript">

<?php
	$ringtones = $digium_phones->get_ringtones();

	# Remove the built-in ringtones while still using the
	# built-in get_ringtones() function.
	foreach ($ringtones as $i => $a) {
		if (is_int(abs($i)) && $i < 0) {
			unset($ringtones[$i]);
		}
	}

	$js_ringtones= json_encode($ringtones);
	echo "var ringtones = ". $js_ringtones. ";\n";
?>

function add_ringtone_clicked() {
	$('#diveditringtone').slideUp('fast');
	$('#divaddringtone').slideToggle('fast');
}
function edit_ringtone_clicked(id) {
	$('#divaddringtone').slideUp('fast');
	
	if ('undefined' == typeof id) { // cancel button
		$('#diveditringtone').slideUp('fast');
	} else {
		$('#diveditringtone').slideDown('fast');
	}
	$('#ringtoneEditId').val(ringtones[id]['id']);
	$('#ringtoneEditName').val(ringtones[id]['name']);
	$('#ringtoneEditType').val(ringtones[id]['type']);
	$('#ringtoneEditRingtoneId').val(ringtones[id]['ringtone_id']);
}
function changeOpacity(element, status, level) {
	if (status == 'enabled') {
		$('#' + element).removeAttr("disabled");
	} else if (status == 'disabled') {
		$('#' + element).attr('disabled', 'disabled');
	}
	$('#' + element).attr("style", "opacity: " + level);
}

function createHiddenInput(parentId, name, value) {
	var input = document.createElement("input");
	input.setAttribute("type", "hidden");
	input.setAttribute("name", name);
	input.setAttribute("value", value);
	$('#' + parentId).append(input);
}
function checkIsFilled(nameArray, submit) {
	for (var i = 0; i < nameArray.length; i++) {
		var x = $('#' + nameArray[i]).val();
		x = x.replace(/^\s+/,""); // strip leading spaces

		if (x.length > 0) {
			changeOpacity(submit, 'enabled', '1.0');
		} else {
			changeOpacity(submit, 'disabled', '0.5');
			return false;
		} // in case a field is filled then erased
	}
	return true;
}
function checkFileSpecified(nameArray, submit) {
	for (var i = 0; i < nameArray.length; i++) {
                if ($('#' + nameArray[i]).val() == "") {
			changeOpacity(submit, 'disabled', '0.5');
			return false;
                } else {
			var fileName = $('#' + nameArray[i]).prop('files')[0].name;
			if (!fileName || !fileName.match(/\.sln|\.sln16|\.raw$/)) {
				changeOpacity(submit, 'disabled', '0.5');
				alert("You must specify a '.sln', '.sln16', or '.raw' audio file!");
				return false;
			}
			var fileSize = $('#' + nameArray[i]).prop('files')[0].size;
			if (!fileSize || fileSize <= 0) {
				changeOpacity(submit, 'disabled', '0.5');
                                alert('The file specified is too small!');
                                return false;
			}
			changeOpacity(submit, 'enabled', '1.0');
                }
	}
}
function checkFields(inputsArray, filesArray, submit) {
	if (inputsArray) {
		if (!checkIsFilled(inputsArray, submit)) {
			return;
		}
	}
	if (filesArray) {
                if (!checkFileSpecified(filesArray, submit)) {
                        return;
                }
	}
}
</script>

<form name="digium_phones_ringtones_del" id="digium_phones_ringtones_del" method="post" enctype="multipart/form-data" action="config.php?type=setup&display=digium_phones&digium_phones_form=ringtones_edit">
	<table style="border-collapse:collapse; border-style:outset; border-width: 1px; margin-bottom: 20px; border-spacing: 5px;">
		<tr>
			<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Ringtone Name<span>Named identifiers of available, user-loaded, ringtones.</span></a></th>
			<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Filename<span>The filename of the Ringtone</span></a></th>
			<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Actions<span>"Edit" provides additional editing control over a selected ringtone. "Delete" removes the specified ringtone.</span></a></th>
		</tr>

<?php
// we need or global $ringtones above
if (empty($ringtones)) {
	?><tr><td colspan="4">No custom ringtones configured.</td></tr><?php
}
$i = 0;
foreach ($ringtones as $ringtone) {
	?>
		<tr style="border:1px solid #D9E6EE;">
			<td><?php echo $ringtone['name']?></td>
			<td><?php echo $ringtone['filename']?></td>
			<td style="vertical-align: middle; border-style:inset; border-width:0px; white-space: nowrap;">
				<input type="button" value="Edit" onclick="edit_ringtone_clicked(<?php echo $ringtone['id']?>);" />
				<input type="submit" name="ringtoneDelSubmit" id="ringtoneDelSubmit" value="Delete" onclick="createHiddenInput('digium_phones_ringtones_del', 'hiddenIdDel', '<?php echo $ringtone['id']?>');" />
			</td>
		</tr>
<?php
}
?>
	</table>
	<input type="button" name="add_ringtone_submit" value="Add Ringtone" onclick="add_ringtone_clicked();"/>
</form>

<div id="divaddringtone" style="display: none;">
	<hr style="margin-top: 30px;"/>
	<h2>Add New Ringtone</h2>
	<form name="digium_phones_ringtones_add" id="digium_phones_ringtones_add" method="post" enctype="multipart/form-data" action="config.php?type=setup&display=digium_phones&digium_phones_form=ringtones_edit">
		<table style="border-spacing: 5px">
			<tr>
				<td><a href="#" class="info">Ringtone Name<span>The named identifier for this ringtone..</span></a></td>
				<td><input type="text" id="ringtoneAddName" name="ringtoneAddName" onkeyup="checkFields(['ringtoneAddName'], ['ringtoneUpload'], 'ringtoneAddSubmit');" /></td>
			</tr>
			<tr>
				<td><a href="#" class="info">Upload Ringtone<span>Ringtones should be 16-bit 16kHz mono raw signed linear audio files less than 1MB in size.</span></a></td>
				<td><input type="file" id="ringtoneUpload" name="ringtoneUpload" onchange="checkFields(['ringtoneAddName'], ['ringtoneUpload'], 'ringtoneAddSubmit');" />
			</tr>
			<tr>
				<td colspan="2">
				<input type="submit" name="ringtoneAddSubmit" id="ringtoneAddSubmit" value="Save" disabled />
				<button type="button" onclick="add_ringtone_clicked();">Cancel</button>
				</td>
			</tr>
		</table>
	</form>
</div>

<div id="diveditringtone" style="display: none;">
	<hr style="margin-top: 30px;"/>
	<h2>Edit Ringtone</h2>
	<form name="digium_phones_ringtones_edit" method="post" enctype="multipart/form-data" action="config.php?type=setup&display=digium_phones&digium_phones_form=ringtones_edit">
		<table style="border-spacing: 5px">
			<tr>
				<td><a href="#" class="info">Ringtone Name<span>Name given to this ringtone file for easier reference.</span></a></td>
				<td><input type="text" id="ringtoneEditName" name="ringtoneEditName" onkeyup="checkIsFilled('ringtoneEditName', 'ringtoneEditSubmit')" /></td>
			</tr>
			<tr>
				<td><input type="hidden" id="ringtoneEditId" name="ringtoneEditId" value=""></td>
				<td></td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="submit" name="ringtoneEditSubmit" id="ringtoneEditSubmit" value="Save" />
					<button type="button" onclick="edit_ringtone_clicked();">Cancel</button>
				</td>
			</tr>
		</table>
	</form>
</div>
