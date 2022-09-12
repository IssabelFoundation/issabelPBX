<?php
global $db;
global $amp_conf;
global $astman;

echo "Caller ID Superfecta is being uninstalled.<br>";

if(!function_exists("out"))
{
	function out($text)
	{
		echo $text."<br />";
	}
}

if (! function_exists("outn")) {
	function outn($text) {
		echo $text;
	}
}

// drop the tables
$sql = "DROP TABLE IF EXISTS superfectaconfig";
$check = $db->query($sql);
if (DB::IsError($check))
{
	die_issabelpbx( "Can not delete superfectaconfig table: " . $check->getMessage() .  "\n");
}

$sql = "DROP TABLE IF EXISTS superfectacache";
$check = $db->query($sql);
if (DB::IsError($check))
{
	die_issabelpbx( "Can not delete superfectacache table: " . $check->getMessage() .  "\n");
}

$sql = "DROP TABLE IF EXISTS superfecta_to_incoming";
$check = $db->query($sql);
if (DB::IsError($check))
{
	die_issabelpbx( "Can not delete superfecta_to_incoming table: " . $check->getMessage() .  "\n");
}

$sql = "DROP TABLE IF EXISTS superfecta_mf";
$check = $db->query($sql);
if (DB::IsError($check))
{
	die_issabelpbx( "Can not delete superfecta_mf table: " . $check->getMessage() .  "\n");
}

$sql = "DROP TABLE IF EXISTS superfecta_mf_child";
$check = $db->query($sql);
if (DB::IsError($check))
{
	die_issabelpbx( "Can not delete superfecta_mf_child table: " . $check->getMessage() .  "\n");
}
?>
