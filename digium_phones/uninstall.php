<?php

global $db;
global $asterisk_conf;

$tables = array('digium_phones_general', 'digium_phones_devices', 'digium_phones_device_settings', 'digium_phones_lines', 'digium_phones_line_settings', 'digium_phones_phonebooks', 'digium_phones_phonebook_entries', 'digium_phones_device_phonebooks');
foreach ($tables as $table) {
	$sql = "DROP TABLE IF EXISTS {$table}";
	$result = $db->query($sql);
	if (DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo());
	}
	unset($result);
}

// end of file
