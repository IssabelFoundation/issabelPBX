<?php
global $db;
global $amp_conf;
global $asterisk_conf;

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

if($amp_conf["AMPDBENGINE"] == "mysql")  {
	$sql = "CREATE TABLE IF NOT EXISTS `motif` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`phonenum` varchar( 12 ) NOT NULL ,
	`username` varchar( 100 ) NOT NULL ,
	`password` varchar( 150 ) NOT NULL ,
	`type` varchar( 50 ) NOT NULL DEFAULT 'googlevoice' ,
	`settings` blob NOT NULL,
    `statusmessage` varchar( 50 ) NOT NULL,
	`priority` int( 4 ) NOT NULL default 127,
	PRIMARY KEY (`id`)
)
";
	$check = $db->query($sql);
	if(DB::IsError($check)) {
		die_issabelpbx(__("Can not create Google Voice/Motif table"));
	} else {
		out("Database table for Google Voice/Motif installed");
	}
} else {
	die_issabelpbx(__("Unknown/Not Supported database type: ".$amp_conf["AMPDBENGINE"]));
}

out('Updating Route Settings');
$sql = 'SELECT `id`, `settings` FROM `motif`';
$accounts = sql($sql, 'getAll', DB_FETCHMODE_ASSOC);

foreach($accounts as $list) {
    $data = unserialize($list['settings']);
    $tmp = array();
    $new = array();
    if(isset($data['route'])) {
        $tmp['obroute'] = $data['route'];
        $tmp['obroute_number'] = $data['route_number'];
        unset($data['route']);
        unset($data['route_number']);
        $new = serialize(array_merge($data,$tmp));
        $sql = "UPDATE `motif` SET `settings` = '".$db->escapeSimple($new)."' WHERE id = " . $list['id'];
        sql($sql);
    }
}

if (!$db->getAll('SHOW COLUMNS FROM motif WHERE FIELD = "statusmessage"')) {
	out("Adding status message field");
	sql('ALTER TABLE motif ADD statusmessage varchar( 50 ) NOT NULL default "I am available"');
}

if(file_exists($amp_conf['ASTETCDIR'].'/rtp.conf') && !is_link($amp_conf['ASTETCDIR'].'/rtp.conf')) {
	$rtp_contents = file_get_contents($amp_conf['ASTETCDIR'].'/rtp.conf');
	if(preg_match('/rtp settings are defined in the chan_motif issabelpbx module/i',$rtp_contents)) {
		out('Removing old motif controlled rtp.conf file');
		unlink($amp_conf['ASTETCDIR'].'/rtp.conf');
	}
}

out("Increase username and password size in database");
$sql = "ALTER TABLE motif CHANGE username username varchar( 100 ) NOT NULL";
sql($sql);

$sql = "ALTER TABLE motif CHANGE password password varchar( 150 ) NOT NULL";
sql($sql);

if (!$db->getAll('SHOW COLUMNS FROM motif WHERE FIELD = "priority"')) {
	out("Adding Priority field");
	sql('ALTER TABLE motif ADD priority int( 4 ) NOT NULL default 127');
}
