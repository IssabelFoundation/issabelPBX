<h2>Status</h2>
<hr />

<?php
function type_display($type) {
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

if (function_exists('presencestate_list_get')) {
	echo '<h4>Using status from <a href="config.php?display=presencestate">Presence State</a> module ';
	echo '<input type="button" value="Edit" onClick="parent.location=\'config.php?display=presencestate\'">';
	echo '</h4>';

	$list = presencestate_list_get();

?>
	<table style="border-collapse:collapse; border-style:outset; border-width: 1px; ">
	<tr>
	<th style="border-style:inset; border-width:1px; width:75px; "><a href="#" class="info">Type<span>The type of this status.</span></a></th>
	<th style="border-style:inset; border-width:1px; width:250px; "><a href="#" class="info">Message<span>The optional message for this status.</span></a></th>
	</tr>

<?php
	foreach ($list as $status) {
?>
		<tr>
		<td style="vertical-align: top; width: 200px; border-style:inset; border-width: 1px; ">
			<span id="status<?php echo $statusid?>type"><?php echo type_display($status['type'])?></span>
		</td>
		<td style="vertical-align: top; border-style:inset; border-width:1px; ">
			<?php echo $status['message']?>
		</td>
		</tr>
<?php
	}
	echo '</table>';
	return;
}

?>

<form name="digium_phones_status" method="post" action="config.php?type=setup&display=digium_phones&digium_phones_form=application_status_edit<?php echo ($editstatus != null && $editstatus != 0)?"&statusid=".$editstatus:""?>">

<script>
$().ready(function() {
<?php
$statuses = $digium_phones->get_statuses();

if (isset($_GET['statusid']) and !isset($_GET['deletestatus_submit'])) {
	$editstatus = htmlspecialchars($_GET['statusid']);
}

if ($editstatus != null) {
	if ($editstatus == 0) {
?>
		$('#statusname').val("New Status");
<?php
	} else {
?>
		$('#statusname').val($('#status<?php echo $editstatus?>name').text());
<?php
	}
?>
	$('#statusid').val(<?php echo $editstatus?>);

	$('div[id=editingstatus]').show();
<?php
}

foreach ($statuses as $statusid=>$status) {
	if ($editstatus == $statusid) {
		foreach ($status['settings'] as $key=>$val) {
?>
			if ($('#<?php echo $key?>') != null) {
				$('#<?php echo $key?>').val('<?php echo $val?>');
			}
<?php
		}
	}

	foreach ($status["entries"] as $entry) {
		if ($editstatus == $statusid) {
?>
			addEntry("<?php echo $entry?>");
<?php
		}
	}
}
?>
});
$('form').submit(function() {
	if ($.trim($('#statusname').val()).length <= 0) {
		alert("Status Name cannot be blank.");
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
	$('#substatus').val(entry);
	$('#entries option[value='+entry+']').remove()
	return true;
}
</script>

	<input type="button" value="Add Status" onclick="location.href='config.php?type=setup&display=digium_phones&digium_phones_form=application_status_edit&statusid=0'" />

	<table style="border-collapse:collapse; border-style:outset; border-width: 1px; ">
		<tr>
			<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Status Name<span>A name for this status.</span></a></th>
			<th style="border-style:inset; border-width:1px; width:75px; "><a href="#" class="info">Type<span>The type of this status.</span></a></th>
			<th style="border-style:inset; border-width:1px; width:125px; "><a href="#" class="info">Sub Statuses<span>The number of sub statuses this status contains.</span></a></th>
			<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Actions<span>"Edit" provides additional editing control over a selected status. "Delete" removes the specified status.</span></a></th>
		</tr>

<?php
foreach ($statuses as $statusid=>$status) {
?>
		<tr>
			<td style="vertical-align: top; width: 200px; border-style:inset; border-width: 1px; ">
				<span id="status<?php echo $statusid?>name"><?php echo $status['name']?></span>
			</td>
			<td style="vertical-align: top; width: 200px; border-style:inset; border-width: 1px; ">
				<span id="status<?php echo $statusid?>type"><?php echo type_display($status['settings']['status'])?></span>
			</td>
			<td style="vertical-align: top; border-style:inset; border-width:1px; ">
				<?php echo count($status['entries'])?>
			</td>
			<td style="vertical-align: top; border-style:inset; border-width:1px; white-space: nowrap; ">
				<input type="button" value="Edit Status" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=application_status_edit&statusid=<?php echo $statusid?>'">
				<input type="button" value="Delete" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=application_status_edit&deletestatus_submit=Delete&statusid=<?php echo $statusid?>'">
			</td>
		</tr>
<?php
}
?>
	</table>

	<hr />

<?php
$table = new CI_Table();

$table->add_row(array( 'data' => fpbx_label('Status Name:', 'A named identifier for the status.'), 'class' => 'template_name'),
	array( 'data' => '<input type="hidden" id="statusid" name="statusid" /><input type="text" id="statusname" name="statusname"/>')
	);
$table->add_row(array( 'data' => fpbx_label('Status Type:', 'The type of status.'), 'class' => 'template_name'),
	array( 'data' => '<select id="status" name="status">
			<option value="available">' . type_display("available") . '</option>
			<option value="dnd">' . type_display("dnd") . '</option>
			<option value="away">' . type_display("away") . '</option>
			<option value="xa">' . type_display("xa") . '</option>
			<option value="chat">' . type_display("chat") . '</option>
		</select>')
	);
$table->add_row(array( 'data' => fpbx_label('Send 486:', 'Controls whether a phone sends a 486 response when in the specified status. Defaults to No.'), 'class' => 'template_name'),
	array( 'data' => '<select id="send486" name="send486">
			<option value="no" selected>No (Default)</option>
			<option value="yes">Yes</option>
		</select>')
	);
	
echo $table->generate();
$table->clear();
echo '<br />';

?>

	<table>
		<tr>
			<th><a href="#" class="info">Sub Status:<span>Allows for text entry of a Sub Status to be assigned to the specified status, e.g. "Available - Working with Customers" where "Available" is the Status Type and "Working with Customers" is the Sub Status.  If a Sub Status is defined for a given Status Type, it will be displayed in addition to the main Status Type.</span></a></th>
			<th><a href="#" class="info">Assigned Sub Statuses:<span>Displays a listing of sub statuses currently assigned to a status.</span></a></th>
		</tr>
		<tr>
			<td>
				<input type="text" id="substatus" />
			</td>
			<td>
				<img alt="Remove" src="images/resultset_left.png" onclick="delEntry($('#entries').val());" style="width: 24px; height: 24px; "/>
				<img alt="Add" src="images/resultset_right.png" onclick="addEntry($('#substatus').val());$('#substatus').val('');" style="width: 24px; height: 24px; " />
			</td>
			<td>
				<select size="8" id="entries" name="entries[]" style="width: 200px; " ondblclick="delEntry($('#entries').val());">
				</select>
			</td>
		</tr>
	</table>

	<br />
	<input type="button" value="Cancel" onclick="location.href='config.php?type=setup&display=digium_phones&digium_phones_form=application_status_edit'"/>
	<input type="submit" name="editstatus_submit" value="Save"/>
</form>
