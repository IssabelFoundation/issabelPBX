<h2>Queues</h2>
<hr />
<script type="text/javascript" src="modules/digium_phones/assets/js/digium_phone_queues.js"></script>
<form name="digium_phones_queues" method="post" action="config.php?type=setup&display=digium_phones&digium_phones_form=application_queues_edit">
<script>
$().ready(function() {
<?php
$queues = $digium_phones->get_queues();
$devices = $digium_phones->get_devices();

if (isset($_GET['queue']) and !isset($_GET['deletequeue_submit'])) {
	$editqueue = htmlspecialchars($_GET['queue']);
}

if ($editqueue != null) {
?>
	$('#queuename').val($('#queue<?php echo $editqueue?>name').text());
	$('#queue').val(<?php echo $editqueue?>);

	$('div[id=editingqueue]').show();
<?php
}
foreach ($queues as $queueid=>$queue) {
	if ($editqueue == $queueid && !empty($queue['entries'])) {
		foreach ($queue['entries'] as $entryid=>$entry) {
			if ($devices[$entryid] == null) {
				continue;
			}
			if ($entry['member'] == true) {
?>
				addMember("<?php echo $entryid?>", "<?php echo $entry['permission']?>");
<?php
			} else {
?>
				addManager("<?php echo $entryid?>");
<?php
			}
		}
	}
}
?>
});

function addMember(deviceid, permission) {
	if ($.trim(deviceid).length <= 0) {
		alert("Cannot add empty device.");
		return false;
	}

	if (permission == null || permission == "") {
		permission = "none";
	}

	$device = $('#availableDevices li[value="' + deviceid + '"]');
	tr = $('<tr id="member' + $device.val() + '"></tr>');
	tr.append($('<td>' + deviceid + '</td>'));
	select = $('<select name="permissions[' + deviceid + ']" style="width: 100px; "></select>');
	select.append($('<option value="none" ' + (permission == "none" ? "selected" : "") + '>None</option>'));
	select.append($('<option value="status" ' + (permission == "status" ? "selected" : "") + '>Status</option>'));
	select.append($('<option value="overview" ' + (permission == "overview" ? "selected" : "") + '>Overview</option>'));
	select.append($('<option value="details" ' + (permission == "details" ? "selected" : "") + '>Details</option>'));
	tr.append(select);
	$('#members').append(tr);

	useDevice(deviceid);

	return true;
}

function useDevice(deviceid) {
	$('#devices option[value="'+deviceid+'"]').remove();
}
function addManager(deviceid) {
	if ($.trim(deviceid).length <= 0) {
		alert("Cannot add empty device.");
		return false;
	}

	entry = $('#devices option[value="'+deviceid+'"]');
	newentry = entry.appendTo('#managers');
	$('#managers').attr('selectedIndex', newentry.index());
	$('#devices').attr('selectedIndex', '-1');
	useDevice(deviceid);
	return true;
}
function delManager(deviceid) {
	entry = $('#managers option[value="'+deviceid+'"]');

	eopt = entry.appendTo('#devices');
	$('#devices').attr('selectedIndex', eopt.index());

	return true;
}
</script>

	<table style="border-collapse:collapse; border-style:outset; border-width: 1px; ">
		<tr>
			<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Extension<span>The extension of this Queue.</span></a></th>
			<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Name<span>The name of this Queue.</span></a></th>
			<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Entries<span>The number of members and managers this queue contains.</span></a></th>
			<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Actions<span>"Edit" provides additional editing control over a selected queue.</span></a></th>
		</tr>
<?php
foreach ($queues as $queueid=>$queue) {
?>
		<tr>
			<td style="border-style:inset; border-width: 1px; ">
				<span id="queue<?php echo $queueid?>id"><?php echo $queue['id']?></span>
			</td>
			<td style="border-style:inset; border-width: 1px; ">
				<span id="queue<?php echo $queueid?>name"><?php echo $queue['name']?></span>
			</td>
			<td style="border-style:inset; border-width:1px; ">
<?php
		$count = 0;
		if (!empty($queue['entries'])) {
			foreach ($queue['entries'] as $entryid=>$entry) {
				if ($devices[$entryid] == null) {
					continue;
				}

				$count++;
			}
		}
		print $count;
?>
			</td>
			<td style="border-style:inset; border-width:1px; white-space: nowrap; ">
				<input type="button" value="Edit" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=application_queues_edit&queue=<?php echo $queueid?>'">
			</td>
		</tr>
<?php
}
?>
	</table>

	<hr />

	<div id="editingqueue" style="display: none;">
		<input type="hidden" id="queue" name="queue" />
		<div style="width:300px; text-align: right;">
			<div>
				<a href="#" class="info">Queue Name:<span>The name of this queue.</span></a>
				<input type="text" id="queuename" name="queuename" disabled="disabled"/>
			</div>
		</div>

		<br />

		<table id="members">
			<tr>
				<th style="width: 200px; "><a href="#" class="info">Members<span>Devices that are members of this queue.</span></a></th>
				<th style="width: 100px; "><a href="#" class="info">Permission<span>Sets the permission level for this user. Status provides only login/out/pause capabilities. Overview also provides statistical information about a queue. Details also provides information about waiting callers and on-call members.</span></a></th>
			</tr>
		</table>

		<br />
		<br />

		<hr>
		<div class="dragdropFrame">
			<div class="dragdrop">
<?php
	echo fpbx_label('Available Devices', 'Displays a listing of devices.');
	echo '<ul id="availableDevices" class="devices ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';
	
	foreach ($devices as $deviceid=>$device) {
		if($queues[$editqueue]['entries'][$device['id']]['member'] != '1'){
			if($queues[$editqueue]['entries'][$device['id']]['permission'] != 'details'){
				echo '<li id="' . $device['id'] . '">' . $device['name'] . '</li>';
			}
		}
	}
	echo '</ul>';
	echo '</div><div class="dragdrop">';
	echo fpbx_label('Managers', 'Managers are devices that have permission to view queue details, but are not themselves members of the queue.');
	echo '<ul id="managersS" class="devices ui-menu ui-widget ui-widget-content ui-corner-all ui-sortable">';

	
	if (!empty($queues[$editqueue]['entries'])) foreach( $queues[$editqueue]['entries'] as $id=>$details){
		if($details['permission'] == 'details'){echo '<li id="' . $id . '">' . $devices[$id]['name'] . '</li>';}
	}

	echo '</ul>';
?>
			</div>
		</div>


		<input type="hidden" id="tempManagers" name="tempManagers[]" value="" />
		<input type="button" value="Cancel" onclick="location.href='config.php?type=setup&display=digium_phones&digium_phones_form=application_queues_edit'"/>
		<input type="submit" name="editqueue_submit" value="Save"/>
	</div>
</form>
