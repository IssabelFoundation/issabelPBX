<?php

global $db;
global $amp_conf;
global $asterisk_conf;

if (version_compare_issabel(getVersion(), '12', '>=')) {
	IssabelPBX::create()->ModulesConf->removenoload('res_digium_phone.so');
}

$sql = "CREATE TABLE IF NOT EXISTS digium_phones_general (
	`keyword` VARCHAR(50) NOT NULL PRIMARY KEY,
	`val` VARCHAR(255),
	`default_val` VARCHAR(255)
);";

$result = $db->query($sql);
if (DB::IsError($result)) {
	die_issabelpbx($result->getDebugInfo());
}
unset($result);

$entries = array(
	'globalpin'=>'',
	'userlist_auth'=>'disabled',
	'config_auth'=>'disabled',
	'mdns_address'=>'',
	'mdns_port'=>'5060',
	'service_name'=>'Asterisk',
	'easy_mode'=>'yes',
	'firmware_version'=>'',
	'internal_phonebook_sort'=>'extension',
	'active_locale'=>'en_US'
);

foreach ($entries as $entry=>$default_val) {
	$sql = "INSERT INTO digium_phones_general (keyword, default_val) VALUES ('{$entry}', '{$default_val}')";

	$result = $db->query($sql);
	if (DB::IsError($result)) {
		unset($result);
		continue;
	}

	unset($result);
};

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_devices (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(30),
	PRIMARY KEY (`id`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_device_settings (
	`deviceid` INT NOT NULL,
	`keyword` VARCHAR(30),
	`val` VARCHAR(255),
	PRIMARY KEY (`deviceid`, `keyword`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_lines (
	`id` INT NOT NULL,
	`deviceid` INT NOT NULL,
	`extension` VARCHAR(45),
	PRIMARY KEY (`deviceid`, `extension`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_extension_settings (
	`extension` INT NOT NULL,
	`keyword` VARCHAR(30),
	`val` VARCHAR(255),
	PRIMARY KEY (`extension`, `keyword`)
);";

$queries[] = "DROP TABLE IF EXISTS digium_phones_line_settings";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_phonebooks (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(30),
	PRIMARY KEY (`id`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_phonebook_entry_settings (
	`phonebookid` INT NOT NULL,
	`phonebookentryid` INT NOT NULL,
	`keyword` VARCHAR(30),
	`val` VARCHAR(255),
	PRIMARY KEY (`phonebookid`, `phonebookentryid`, `keyword`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_phonebook_entries (
	`id` INT NOT NULL,
	`phonebookid` INT NOT NULL,
	`extension` VARCHAR(45),
	PRIMARY KEY (`id`, `phonebookid`)
);";
$queries[] = "ALTER TABLE digium_phones_phonebook_entries DROP PRIMARY KEY";
$queries[] = "ALTER TABLE digium_phones_phonebook_entries ADD PRIMARY KEY (`id`, `phonebookid`)";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_device_phonebooks (
	`id` INT NOT NULL,
	`deviceid` INT NOT NULL,
	`phonebookid` INT NOT NULL,
	PRIMARY KEY (`deviceid`, `phonebookid`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_statuses (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(30),
	PRIMARY KEY (`id`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_status_settings (
	`statusid` INT NOT NULL,
	`keyword` VARCHAR(30),
	`val` VARCHAR(255),
	PRIMARY KEY (`statusid`, `keyword`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_status_entries (
	`id` INT NOT NULL,
	`statusid` INT NOT NULL,
	`text` VARCHAR(255),
	PRIMARY KEY (`id`, `statusid`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_device_statuses (
	`id` INT NOT NULL,
	`deviceid` INT NOT NULL,
	`statusid` INT NOT NULL,
	PRIMARY KEY (`deviceid`, `statusid`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_customapps (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(30),
	PRIMARY KEY (`id`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_customapp_settings (
	`customappid` INT NOT NULL,
	`keyword` VARCHAR(30),
	`val` VARCHAR(255),
	PRIMARY KEY (`customappid`, `keyword`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_device_customapps (
	`id` INT NOT NULL,
	`deviceid` INT NOT NULL,
	`customappid` INT NOT NULL,
	PRIMARY KEY (`deviceid`, `customappid`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_networks (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(30),
	PRIMARY KEY (`id`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_network_settings (
	`networkid` INT NOT NULL,
	`keyword` VARCHAR(30),
	`val` VARCHAR(255),
	PRIMARY KEY (`networkid`, `keyword`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_device_networks (
	`id` INT NOT NULL,
	`deviceid` INT NOT NULL,
	`networkid` INT NOT NULL,
	PRIMARY KEY (`deviceid`, `networkid`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_externallines (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(30),
	PRIMARY KEY (`id`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_externalline_settings (
	`externallineid` INT NOT NULL,
	`keyword` VARCHAR(30),
	`val` VARCHAR(255),
	PRIMARY KEY (`externallineid`, `keyword`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_device_externallines (
	`id` INT NOT NULL,
	`deviceid` INT NOT NULL,
	`externallineid` INT NOT NULL,
	PRIMARY KEY (`deviceid`, `externallineid`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_queues (
	`queueid` INT NOT NULL,
	`deviceid` INT NOT NULL,
	`permission` VARCHAR(8),
	PRIMARY KEY (`queueid`, `deviceid`, `permission`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_logos (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(30),
	`model` VARCHAR(30),
	PRIMARY KEY (`id`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_device_logos (
	`id` INT NOT NULL,
	`deviceid` INT NOT NULL,
	`logoid` INT NOT NULL,
	PRIMARY KEY (`deviceid`, `logoid`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_alerts (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(30),
	`alertinfo` VARCHAR(30),
	`type` VARCHAR(30),
	`ringtone` INT,
	PRIMARY KEY (`id`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_device_alerts (
	`id` INT NOT NULL,
	`deviceid` INT NOT NULL,
	`alertid` INT NOT NULL,
	PRIMARY KEY (`deviceid`, `alertid`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_ringtones (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(30),
	`builtin` TINYINT(1) DEFAULT 0,
	`filename` VARCHAR(50),
	PRIMARY KEY (`id`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_device_ringtones (
	`id` INT NOT NULL,
	`deviceid` INT NOT NULL,
	`ringtoneid` INT NOT NULL,
	PRIMARY KEY (`deviceid`, `ringtoneid`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_firmware (
	`unique_id` VARCHAR(50) NOT NULL,
	`file_name` VARCHAR(50) NOT NULL,
	`phone_model` VARCHAR(30) NOT NULL,
	`package_id` VARCHAR(50) NOT NULL,
	PRIMARY KEY (`unique_id`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_firmware_packages (
	`unique_id` VARCHAR(50) NOT NULL,
	`name` VARCHAR(50) NOT NULL,
	`file_path` VARCHAR(512) NOT NULL,
	`version` VARCHAR(30) NOT NULL,
	PRIMARY KEY (`unique_id`)
);";

$queries[] = "CREATE TABLE IF NOT EXISTS digium_phones_voicemail_translations (
	`locale` VARCHAR(10) NOT NULL,
	`keyword` VARCHAR(50) NOT NULL,
	`val` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`locale`, `keyword`)
);";

foreach ($queries as $sql) {
	$result = $db->query($sql);
	if (DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo());
	}
	unset($result);
}

$sql = "INSERT INTO digium_phones_phonebooks (id, name) VALUES (-1, 'Internal Phonebook')";
$result = $db->query($sql);
unset($result);

$sql = "SELECT * FROM digium_phones_phonebook_entries WHERE extension LIKE '%;%'";
$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
if (DB::IsError($results)) {
	die_issabelpbx($results->getDebugInfo());
	return false;
}

foreach ($results as $row) {
	$split = preg_split('/;/', $row['extension']);
	$exten = $split[0];
	$label = $split[1];
	$subscribe = $split[2];

	if ($label == null) {
		continue;
	}

	$sql = "UPDATE digium_phones_phonebook_entries SET extension = '{$exten}' WHERE id = '{$row['id']}' AND phonebookid = '{$row['phonebookid']}'";
	$result = $db->query($sql);
	unset($result);
	
	$sql = "INSERT INTO digium_phones_phonebook_entry_settings VALUES ";
	$sql.= "({$row['phonebookid']}, {$row['id']}, 'type', 'external'), ";
	$sql.= "({$row['phonebookid']}, {$row['id']}, 'label', '{$label}'), ";
	$sql.= "({$row['phonebookid']}, {$row['id']}, 'subscribe', '{$subscribe}')";
	$result = $db->query($sql);
	unset($result);
}


$sql = "INSERT INTO digium_phones_networks (id, name) VALUES (-1, 'Default Network')";
$result = $db->query($sql);
if (!DB::IsError($result)) {
	/* Default Network didn't exist.  Add some defaults to it. */
	$entries = array(
		'cidr'=>'0.0.0.0/0',
		'ntp_server'=>'0.digium.pool.ntp.org',
		'registration_address'=>'',
		'registration_port'=>'',
		'file_url_prefix'=>''
	);

	foreach ($entries as $entry=>$val) {
		$sql = "INSERT INTO digium_phones_network_settings VALUES (-1, '{$entry}', '{$val}')";
		$result = $db->query($sql);
		unset($result);
	};
}
unset($result);

$vmtables = array(
	'en_AU' => array(
		'IGNOREME' => 'IGNOREME'),
	'en_CA' => array(
		'IGNOREME' => 'IGNOREME'),
	'en_GB' => array(
		'IGNOREME' => 'IGNOREME'),
	'en_US' => array(
		'IGNOREME' => 'IGNOREME'),
	'de_DE' => array(
		'INBOX' => 'INBOX',
		'Work' => 'Arbeit',
		'Family' => 'Familie',
		'Friends' => 'Freunde',
		'Recordings' => 'Aufnahmen'),
	'nl_NL' => array(
		'INBOX' => 'INBOX',
		'Work' => 'Werk',
		'Family' => 'Familie',
		'Friends' => 'Vrienden',
		'Recordings' => 'Opnamen'),
	'nl_BE' => array(
		'INBOX' => 'INBOX',
		'Work' => 'Werk',
		'Family' => 'Familie',
		'Friends' => 'Vrienden',
		'Recordings' => 'Opnamen'),
	'fr_FR' => array(
		'INBOX' => 'INBOX',
		'Work' => 'Travail',
		'Family' => 'Famille',
		'Friends' => 'Amis',
		'Recordings' => 'Enregistrements'),
	'fr_CA' => array(
		'INBOX' => 'INBOX',
		'Work' => 'Travail',
		'Family' => 'Famille',
		'Friends' => 'Amis',
		'Recordings' => 'Enregistrements'),
	'fr_BE' => array(
		'INBOX' => 'INBOX',
		'Work' => 'Travail',
		'Family' => 'Famille',
		'Friends' => 'Amis',
		'Recordings' => 'Enregistrements'),
	'it_IT' => array(
		'INBOX' => 'INBOX',
		'Work' => 'Lavori',
		'Family' => 'Famiglia',
		'Friends' => 'Amici',
		'Recordings' => 'Registrazioni'),
	'es_ES' => array(
		'INBOX' => 'INBOX',
		'Work' => 'Trabajo',
		'Family' => 'Familia',
		'Friends' => 'Amigos',
		'Recordings' => 'Grabaciones'),
	'es_MX' => array(
		'INBOX' => 'INBOX',
		'Work' => 'Trabajo',
		'Family' => 'Familia',
		'Friends' => 'Amigos',
		'Recordings' => 'Grabaciones'),
	'pt_PT' => array(
		'INBOX' => 'INBOX',
		'Work' => 'Trabalho',
		'Family' => 'Família',
		'Friends' => 'Amigos',
		'Recordings' => 'Gravações'),
	'pt_BR' => array(
		'INBOX' => 'INBOX',
		'Work' => 'Trabalho',
		'Family' => 'Família',
		'Friends' => 'Amigos',
		'Recordings' => 'Gravações'),
);

// Blow away the old values
$dropsql = "TRUNCATE TABLE digium_phones_voicemail_translations";
$result = $db->query($dropsql);
unset($result);

foreach ($vmtables as $keyid=>$valarray) {
	$sql = "INSERT INTO digium_phones_voicemail_translations VALUES ('{$keyid}',";
	foreach ($valarray as $key=>$value) {
		$execsql = $sql."'{$key}','{$value}')";
		$result = $db->query($execsql);
		unset($result);
	}
}

$entries = array(
	'-1'=>'Alarm',
	'-2'=>'Chimes',
	'-3'=>'Digium',
	'-4'=>'GuitarStrum',
	'-5'=>'Jingle',
	'-6'=>'Office',
	'-7'=>'Office2',
	'-8'=>'RotaryPhone',
	'-9'=>'SteelDrum',
	'-10'=>'Techno',
	'-11'=>'Theme',
	'-12'=>'Tweedle',
	'-13'=>'Twinkle',
	'-14'=>'Vibe',
);
$ringtonesql = "UPDATE digium_phones_device_settings SET val = CASE ";
foreach ($entries as $keyid=>$tonename) {
	$sql = "INSERT INTO digium_phones_ringtones VALUES ({$keyid}, '{$tonename}', 1, NULL)";
	$result = $db->query($sql);
	unset($result);

	$ringtonesql .= "WHEN val = '{$tonename}' THEN '{$keyid}' ";
};
$ringtonesql .= "ELSE val END WHERE keyword='active_ringtone'";
$result = $db->query($ringtonesql);
unset($result);

//end of file
