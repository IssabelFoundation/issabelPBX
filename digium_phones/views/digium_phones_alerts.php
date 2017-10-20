<h2>Alerts</h2>
<hr />

<script type="text/javascript">

<?php
$alerts = $digium_phones->get_alerts();
$ringtones = $digium_phones->get_ringtones();

$js_alerts= json_encode(array_values($alerts));
echo "var alerts = ". $js_alerts. ";\n";
?>

function add_alert_clicked()
{
	$('#diveditalert').slideUp('fast');
	$('#divaddalert').slideToggle('fast');
}
function edit_alert_clicked(id)
{
	$('#divaddalert').slideUp('fast');

	if ('undefined' == typeof id) { // cancel button
		$('#diveditalert').slideUp('fast');
	} else {
		$('#diveditalert').slideDown('fast');
	}

	for (var i=0; i < alerts.length; i++) {
		if (id == alerts[i]['id']) {
			$('#alertEditId').val(alerts[i]['id']);
			$('#alertEditName').val(alerts[i]['name']);
			$('#alertEditAlertinfo').val(alerts[i]['alertinfo']);
			$('#alertEditType').val(alerts[i]['type']);
			$('#alertEditRingtoneId').val(alerts[i]['ringtone_id']);
		}
	}
}
function createHiddenInput(parentId, name, value)
{
	var input = document.createElement("input");
	input.setAttribute("type", "hidden");
	input.setAttribute("name", name);
	input.setAttribute("value", value);
	$('#' + parentId).append(input);
}
function checkIsFilled(name, submit) {
	var x = $('#' + name).val();
        x = x.replace(/^\s+/,""); // strip leading spaces

        if (x.length > 0) {
                $('#' + submit).removeAttr("disabled");
                $('#' + submit).attr("style", "opacity: 1.0");
        } else {
                $('#' + submit).attr('disabled', 'disabled');
                $('#' + submit).attr("style", "opacity: 0.5");
        } // in case a field is filled then erased
}

function handleAlertTypeChange(select_name, hide_control_name) {
	if ($(select_name).val() == "answer" || $(select_name).val() == "visual") {
		$(hide_control_name).val("");
		$(hide_control_name).hide();
	} else {
		$(hide_control_name).show();
	}
}
</script>
<form name="digium_phones_alerts_del" id="digium_phones_alerts_del" method="post" enctype="multipart/form-data" action="config.php?type=setup&display=digium_phones&digium_phones_form=alerts_edit">
<table style="border-collapse:collapse; border-style:outset; border-width: 1px; margin-bottom: 20px; border-spacing: 0px">
<tr>
<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Alert Name<span>Named identifiers of configured Alerts.</span></a></th>
<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Ringing Type<span>Ringing Type of the Alert</span></a></th>
<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Ringtone<span>The ringing tone for the Alert</span></a></th>
<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Actions<span>"Edit" provides additional editing control over a selected Alert. "Delete" removes the specified Alert.</span></a></th>
</tr>
<?php
// we need or global $alerts above
if (empty($alerts)) {
	?><tr><td colspan="4">No custom alerts configured.</td></tr><?php
}
$i = 0;
foreach ($alerts as $alert) {
	?>
	<tr style="border:1px solid #D9E6EE;">
	<td><?php echo $alert['name']?></td>
	<td><?php echo $alert['type']?></td>
	<td><?php if ($alert['type'] !== 'answer' and $alert['type'] !== 'visual') {
		echo $alert['ringtone_name'];
	}?></td>
	<td style="vertical-align: middle; border-style:inset; border-width:0px; white-space: nowrap;">
		<input type="button" value="Edit" onclick="edit_alert_clicked(<?php echo $alert['id']?>);" />
		<input type="submit" name="alertDelSubmit" id="alertDelSubmit" value="Delete" onclick="createHiddenInput('digium_phones_alerts_del', 'hiddenIdDel', '<?php echo $alert['id']?>');" />
	</td>
</tr>
	<?php
}
?>
</table>
<input type="button" value="Add Alert" name="add_alert_submit" onclick="add_alert_clicked();"/>
</form>

<div id="divaddalert" style="display: none;">
<hr style="margin-top: 30px;"/>
<h2>Add New Alert</h2>
<form name="digium_phones_alerts_add" method="post" enctype="multipart/form-data" action="config.php?type=setup&display=digium_phones&digium_phones_form=alerts_edit">
<table>
<tr>
	<td><a href="#" class="info">Alert Name<span>The named identifier for this Alert.</span></a></td>
	<td><input type="text" id="alertAddName" name="alertAddName" onkeyup="checkIsFilled('alertAddName', 'alertAddSubmit')" /></td>
</tr>
<tr>
	<td><a href="#" class="info">Alert Info<span>The string the phone should expect encapsulated in the Alert-Info header, e.g. Alert-Info: &#60;my string&#62;</span></a></td>
	<td><input type="text" id="alertAddAlertinfo" name="alertAddAlertinfo" /></td>
</tr>
<tr>
	<td><a href="#" class="info">Ringing Type<span>The type of ringing behavior assigned for the alert.  Normal is a normal ring, where the phone plays a tone and waits to be answered.  Answer causes the phone to immediately go off hook into speaker or headset mode and answer the call.  Ring-Answer causes the phone to play a short version of the ring tone and to then go off-hook into speaker or headset mode and answer the call.  Visual causes the phone to silently ring, no tone is played.</span></a></td>
	<td>
		<select id="alertAddType" name="alertAddType" onchange="handleAlertTypeChange('#alertAddType', '#alertAddRingtone');">
			<option value="normal">normal</option>
			<option value="answer">answer</option>
			<option value="ring-answer">ring-answer</option>
			<option value="visual">visual</option>
		</select>
	</td>
</tr>
<tr>
	<td><a href="#" class="info">Ringtone<span>The ringtone, one of the phone's built-in defaults or one of the user-specified custom ringtones, to be played for the Alert.</span></a></td>
	<td>
                <select id="alertAddRingtone" name="alertAddRingtone">
		<?php
                foreach ($ringtones as $row) {
			?>
			<option value="<?php echo $row['id']?>"><?php echo $row['name']?></option>
			<?php
                }
		?>
                </select>
	</td>
</tr>
<tr>
	<td colspan="2">
	<input type="submit" name="alertAddSubmit" id="alertAddSubmit" value="Save" disabled />
	<button type="button" onclick="add_alert_clicked();">Cancel</button>
	</td>
</tr>
</table>
</form>
</div>

<div id="diveditalert" style="display: none;">
<hr style="margin-top: 30px;"/>
<h2>Edit Alert</h2>
<form name="digium_phones_alerts_edit" method="post" enctype="multipart/form-data" action="config.php?type=setup&display=digium_phones&digium_phones_form=alerts_edit">
<table>
<tr>
	<td><a href="#" class="info">Alert Name<span>Name given to this alert file for easier reference.</span></a></td>
	<td><input type="text" id="alertEditName" name="alertEditName" onkeyup="checkIsFilled('alertEditName', 'alertEditSubmit')" /></td>
</tr>
<tr>
	<td><a href="#" class="info">Alert Info<span></span></a></td>
	<td><input type="text" id="alertEditAlertinfo" name="alertEditAlertinfo" /></td>
</tr>
<tr>
        <td><a href="#" class="info">Ringing Type<span>Something descriptive should go here.</span></a></td>
        <td>
                <select id="alertEditType" name="alertEditType" onchange="handleAlertTypeChange('#alertEditType', '#alertEditRingtoneId');">
                        <option value="normal">normal</option>
                        <option value="answer">answer</option>
                        <option value="ring-answer">ring-answer</option>
                        <option value="visual">visual</option>
                </select>
        </td>
</tr>
<tr>
        <td><a href="#" class="info">Ringtone<span>Something descriptive should go here.</span></a></td>
        <td>
                <select id="alertEditRingtoneId" name="alertEditRingtoneId">
		<?php
                foreach ($ringtones as $row) {
			?>
			<option value="<?php echo $row['id']?>"><?php echo $row['name']?></option>
			<?php
                }
		?>
                </select>
        </td>
</tr>
<tr>
	<td colspan="2">
	<input type="hidden" name="alertEditId" id="alertEditId" />
	<input type="submit" name="alertEditSubmit" id="alertEditSubmit" value="Save" />
	<button type="button" onclick="edit_alert_clicked();">Cancel</button>
	</td>
</tr>
</table>
</form>
</div>
