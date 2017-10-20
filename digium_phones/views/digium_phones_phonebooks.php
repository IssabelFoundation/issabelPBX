<h2>Phonebooks</h2>
<hr />

<?php
if (isset($_GET['phonebook']) and !isset($_GET['deletephonebook_submit'])) {
	$editpb = htmlspecialchars($_GET['phonebook']);

	if (isset($_GET['edittype'])) {
		$edittype = htmlspecialchars($_GET['edittype']);
	}
}
?>

<form name="digium_phones_phonebooks" method="post" action="config.php?type=setup&display=digium_phones&digium_phones_form=phonebooks_edit<?php echo ($editpb != null && $editpb != 0)?"&phonebook=".$editpb:""?><?php echo $edittype != null?"&edittype=".$edittype:""?>">
<script>
$().ready(function() {
<?php
$phonebooks = $digium_phones->get_phonebooks();

if ($editpb != null) {
	if (isset($_GET['entry']) and !isset($_GET['deletephonebookentry_submit']) and !isset($_GET['movephonebookentry_submit'])) {
		$editentry = htmlspecialchars($_GET['entry']);

		if (isset($_GET['type'])) {
?>
			$('#type').val('<?php echo htmlspecialchars($_GET['type']); ?>');
<?php
		}
	}

	if ($editpb == 0) {
?>
		$('#phonebookname').val("New Phonebook");
<?php
	} else {
?>
		$('#phonebookname').val($('#phonebook<?php echo $editpb?>name').text());
<?php
	}
?>
	$('#phonebook').val(<?php echo $editpb?>);

<?php
	if ($edittype == "phonebook") {
?>
		$('div[id=editingphonebook]').show();
<?php
	} else if ($edittype == "entries") {
?>
		$('div[id=editingphonebookentries]').show();
<?php
		if ($editentry != null) {
?>
			$('#entry').val('<?php echo $editentry?>');
<?php
		}
	}
}

foreach ($phonebooks as $phonebookid=>$phonebook) {
	if ($phonebookid == -1) {
		continue;
	}
	if ($editpb == $phonebookid) {
		if (!empty($phonebook['entries'])) foreach ($phonebook['entries'] as $entryid=>$entry) {
			if ($editentry != null && $editentry == $entryid) {
?>
				$('#extension').val('<?php echo $entry['extension']?>');
<?php
				foreach ($entry['settings'] as $key=>$val) {
?>
					if ($('#<?php echo $key?>') != null) {
						if ($('#<?php echo $key?>').is(':checkbox')) {
							$('#<?php echo $key?>').attr('checked', '<?php echo $val?>');
						} else {
							$('#<?php echo $key?>').val('<?php echo $val?>');
						}
					}
<?php
				}
			}
		}
	}
}
?>
	if ($('#type').val() == "internal") {
		$('#internalextension').val($('#extension').val());
		$('div[id=editingphonebookentriesentryinternal]').show();
	} else if ($('#type').val() == "external") {
		$('#externalextension').val($('#extension').val());
		$('div[id=editingphonebookentriesentryexternal]').show();
	}
});
$('form').submit(function() {
<?php
	if ($edittype == "phonebook") {
?>
		if ($.trim($('#phonebookname').val()).length <= 0) {
			alert("Phonebook Name cannot be blank.");
			return false;
		}
<?php
	} else if ($edittype == "entries") {
?>
		if ($('#type').val() == "internal") {
			$('#extension').val($('#internalextension').val());
		} else if ($('#type').val() == "external") {
			$('#extension').val($('#externalextension').val());
		}

		if ($.trim($('#extension').val()).length <= 0) {
			alert("Extension cannot be blank.");
			return false;
		}
<?php
	}
?>
});
</script>
<input type="button" value="Add Phonebook" onclick="location.href='config.php?type=setup&display=digium_phones&digium_phones_form=phonebooks_edit&phonebook=0&edittype=phonebook'" />
<p>

<table style="border-collapse:collapse; border-style:outset; border-width: 1px; ">
<tr>
<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Phonebook Name<span>A name for this phonebook.</span></a></th>
<th style="border-style:inset; border-width:1px; width:75px; "><a href="#" class="info">Entries<span>The number of entries / contacts this phonebook contains.</span></a></th>
<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Actions<span>"Edit" provides additional editing control over a selected phonebook. "Delete" removes the specified phonebook.</span></a></th>
</tr>
<?php
foreach ($phonebooks as $phonebookid=>$phonebook) {
	if ($phonebookid == -1) {
		continue;
	}
?>
<tr>
<td style="vertical-align: middle; width: 200px; border-style:inset; border-width: 1px; ">
	<span id="phonebook<?php echo $phonebookid?>name"><?php echo $phonebook['name']?></span>
</td>
<td style="vertical-align: middle; border-style:inset; border-width:1px; ">
	<?php echo count($phonebook['entries'])?>
</td>
<td style="vertical-align: middle; border-style:inset; border-width:1px; white-space: nowrap; ">
	<input type="button" value="Edit Phonebook" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=phonebooks_edit&phonebook=<?php echo $phonebookid?>&edittype=phonebook'">
	<input type="button" value="Edit Entries" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=phonebooks_edit&phonebook=<?php echo $phonebookid?>&edittype=entries'">
	<input type="button" value="Delete" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=phonebooks_edit&deletephonebook_submit=Delete&phonebook=<?php echo $phonebookid?>'">
</td>
</tr>
<?php
}
?>
</table>

<hr />

<input type="hidden" id="phonebook" name="phonebook" />
<div id="editingphonebook" style="display: none;">
		<div>
			<a href="#" class="info">Phonebook Name:<span>A named identifier for the phonebook.</span></a>
			<input type="text" id="phonebookname" name="phonebookname"/>
		</div>

	<br />

	<input type="button" value="Cancel" onclick="location.href='config.php?type=setup&display=digium_phones&digium_phones_form=phonebooks_edit'"/>
	<input type="submit" name="editphonebook_submit" value="Save"/>
</div>

<div id="editingphonebookentries" style="display: none;">
	<input type="hidden" id="entry" name="entry" />
	<input type="hidden" id="extension" name="extension" />
	<input type="hidden" id="type" name="type" />

<?php
	foreach ($phonebooks as $phonebookid=>$phonebook) {
		if ($editpb == $phonebookid) {
?>
	<input type="button" value="Add Internal Entry" onclick="location.href='config.php?type=setup&display=digium_phones&digium_phones_form=phonebooks_edit&phonebook=<?php echo $phonebookid?>&edittype=entries&entry=<?php echo count($phonebook['entries'])?>&type=internal'" />
	<input type="button" value="Add External Entry" onclick="location.href='config.php?type=setup&display=digium_phones&digium_phones_form=phonebooks_edit&phonebook=<?php echo $phonebookid?>&edittype=entries&entry=<?php echo count($phonebook['entries'])?>&type=external'" />
<?php
		}
	}
?>
	<p>

	<table style="border-collapse:collapse; border-style:outset; border-width: 1px; ">
	<tr>
	<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Name<span>The name of this entry.</span></a></th>
	<th style="border-style:inset; border-width:1px; width:75px; "><a href="#" class="info">Number<span>The number of this entry.</span></a></th>
	<th style="border-style:inset; border-width:1px; width:75px; "><a href="#" class="info">Type<span>The type of this entry.</span></a></th>
	<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Actions<span>"Edit" provides additional editing control over a selected entry. "Delete" removes the specified entry.</span></a></th>
	</tr>
<?php
	foreach ($phonebooks as $phonebookid=>$phonebook) {
		if ($phonebookid != $editpb) {
			continue;
		}

		if (!empty($phonebook['entries'])) foreach ($phonebook['entries'] as $entryid=>$entry) {
?>
	<tr>
	<td style="vertical-align: middle; width: 200px; border-style:inset; border-width: 1px; ">
		<span id="entry<?php echo $entryid?>name">
<?php
		if ($entry['settings']['type'] == "internal") {
			$device = $digium_phones->get_core_device($entry['extension']);
?>
			<?php echo $device['description']?>
<?php
		} else {
?>
			<?php echo $entry['settings']['label']?>
<?php
		}
?>
		</span>
	</td>
	<td style="vertical-align: middle; border-style:inset; border-width:1px; ">
		<?php echo $entry['extension']?>
	</td>
	<td style="vertical-align: middle; border-style:inset; border-width:1px; ">
		<?php echo $entry['settings']['type'] == "internal" ? "Internal" : "External"?>
	</td>
	<td style="vertical-align: middle; float:right; border-style:inset; border-width:1px; white-space: nowrap; height: 24px; ">
<?php
		if ($entryid > 0) {
?>
			<img alt="Move Up" src="images/resultset_up.png" style="width: 24px; height: 24px; " onclick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=phonebooks_edit&movephonebookentry_submit=up&phonebook=<?php echo $phonebookid?>&edittype=entries&entry=<?php echo $entryid?>'"/>
<?php
		} else {
?>
			<img alt="Move Up" src="images/resultset_up.png" style="width: 24px; height: 24px; opacity:0.3; "/>		
<?php
		}
		if ($entryid < (count($phonebook['entries']) - 1)) {
?>
			<img alt="Move Down" src="images/resultset_down.png" style="width: 24px; height: 24px; " onclick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=phonebooks_edit&movephonebookentry_submit=down&phonebook=<?php echo $phonebookid?>&edittype=entries&entry=<?php echo $entryid?>'"/>
<?php
		} else {
?>
			<img alt="Move Down" src="images/resultset_down.png" style="width: 24px; height: 24px; opacity:0.3; "/>
<?php
		}
?>
		<input type="button" value="Edit Entry" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=phonebooks_edit&phonebook=<?php echo $phonebookid?>&edittype=entries&entry=<?php echo $entryid?>'">
		<input type="button" value="Delete" onClick="parent.location='config.php?type=setup&display=digium_phones&digium_phones_form=phonebooks_edit&deletephonebookentry_submit=Delete&phonebook=<?php echo $phonebookid?>&edittype=entries&entry=<?php echo $entryid?>'">
	</td>
	</tr>
<?php
		}
	}
?>
	</table>

	<hr />

	<div id="editingphonebookentriesentryinternal" style="display: none;">
		<?php
		$table = new CI_Table();

		$extension = '<select id="internalextension">
				<option value="">&nbsp;</option>';
		foreach ($digium_phones->get_core_devices() as $user) {
			$extension .= '<option value="' . $user[0] . '">' . $user[0] . ' | ' . $user[1] . '</option>';
		}
		$extension .= '</select>';
		$table->add_row(array( 'data' => fpbx_label('Extension:', 'The local extension for this phonebook entry.'), 'class' => 'template_name'),
			array( 'data' => $extension)
			);
		$table->add_row(array( 'data' => fpbx_label('Has Voicemail:', 'If set to yes, then when viewing this contact, a soft "to VM" key will be present when dialing or transferring to this contact.  Defaults to No.'), 'class' => 'template_name'),
			array( 'data' => '<select id="has_voicemail" name="has_voicemail">
				<option value="" selected>No (Default)</option>
				<option value="yes">Yes</option>
			</select>')
			);
		$table->add_row(array( 'data' => fpbx_label('Can Intercom:', 'If set to yes, and if this contact is a member of a phone\'s Rapid Dial keys, then this contact will display a "Intercom" key when the contact is not on a call and the contact\'s details are viewed inside the Contacts application.  Defaults to no.'), 'class' => 'template_name'),
			array( 'data' => '<select id="can_intercom" name="can_intercom">
				<option value="" selected>No (Default)</option>
				<option value="yes">Yes</option>
			</select>')
			);
		$table->add_row(array( 'data' => fpbx_label('Can Monitor:', 'If set to yes, and if this contact is a member of a phone\'s Rapid Dial keys, then this contact will display a "Monitor" key when the contact is on a call and the contact\'s details are viewed inside the Contacts application.  Defaults to no.'), 'class' => 'template_name'),
			array( 'data' => '<select id="can_monitor" name="can_monitor">
				<option value="" selected>No (Default)</option>
				<option value="yes">Yes</option>
			</select>')
			);
		
		echo $table->generate();
		$table->clear();

		?>
		<p>

		<input type="button" value="Cancel" onclick="location.href='config.php?type=setup&display=digium_phones&digium_phones_form=phonebooks_edit'"/>
		<input type="submit" name="editphonebookentry_submit" value="Save"/>
	</div>
	<div id="editingphonebookentriesentryexternal" style="display: none;">
		
		<?php
		$table->add_row(array( 'data' => fpbx_label('Number:', 'Sets the number to be dialled for this contact.'), 'class' => 'template_name'),
			array( 'data' => '<input type="text" id="externalextension" name="externalextension" />')
			);

		$table->add_row(array( 'data' => fpbx_label('Label:', 'Sets the label for this contact.'), 'class' => 'template_name'),
			array( 'data' => '<input type="text" id="label" name="label" />')
			);

		$table->add_row(array( 'data' => fpbx_label('Subscribe:', 'If checked, and if this Phonebook is marked for a phone\'s Rapid Dial keys, then a SIP SUBSCRIBE will be made for this contact.'), 'class' => 'template_name'),
			array( 'data' => '<input type="checkbox" id="subscribe_to" name="subscribe_to" />')
			);

		$table->add_row(array( 'data' => fpbx_label('Subscription URL:', 'If this contact is marked for Subscribe and if it is part of a Phonebook that is marked for a phone\'s Rapid Dial keys, sets the subscription URI'), 'class' => 'template_name'),
			array( 'data' => '<input type="text" id="subscription_url" name="subscription_url" />')
			);
		
		echo $table->generate();
		$table->clear();
		?>

		<p>

		<input type="button" value="Cancel" onclick="location.href='config.php?type=setup&display=digium_phones&digium_phones_form=phonebooks_edit'"/>
		<input type="submit" name="editphonebookentry_submit" value="Save"/>
	</div>
</div>
</form>
