<?php

global $db;

if (! function_exists("out")) {
    function out($text) {
        echo $text."<br />";
    }
}

if (! function_exists("outn")) {
    function outn($text) {
        echo $text;
    }
}

$sql = 'SELECT * FROM `motif`';
$accounts = sql($sql, 'getAll', DB_FETCHMODE_ASSOC);

foreach($accounts as $list) {
	//Get settings from DB and see if we created a trunk
	$sql = 'SELECT * FROM `motif` WHERE `id` = '.$db->escapeSimple($list['id']);
	$a = sql($sql, 'getRow', DB_FETCHMODE_ASSOC);
	$s = unserialize($a['settings']);

	//If we created a trunk then delete it
	if(isset($s['trunk_number'])) {
		core_trunks_del($s['trunk_number']);
	}

	//If we created a route then delete it
	if(isset($s['obroute_number'])) {
		core_routing_delbyid($s['obroute_number']);
	}

	//Delete our settings from our own DB
	$sql = "DELETE FROM `motif` WHERE id = ".$db->escapeSimple($list['id']);
	sql($sql);
}

out("Dropping all relevant tables");
$sql = "DROP TABLE `motif`";
$result = $db->query($sql);