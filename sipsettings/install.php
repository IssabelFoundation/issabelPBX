<?php
/* $Id:$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;
global $amp_conf;

$sql = <<< END
CREATE TABLE IF NOT EXISTS `sipsettings` (
  `keyword` VARCHAR (50) NOT NULL default '',
  `data`    VARCHAR (255) NOT NULL default '',
  `seq`     TINYINT (1),
  `type`    TINYINT (1) NOT NULL default '0',
  PRIMARY KEY (`keyword`,`seq`,`type`)
)
END;

outn(_("checking for sipsettings table.."));
$tsql = "SELECT * FROM `sipsettings` limit 1";
$check = $db->getRow($tsql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	out(_("none, creating table"));
	// table does not exist, create it
	sql($sql);

	outn(_("populating default codecs.."));
  $sip_settings =  array(
    array('ulaw'    ,'1', '0'),
    array('alaw'    ,'2', '1'),
    array('slin'    ,'' , '2'),
    array('g726'    ,'' , '3'),
    array('gsm'     ,'3', '4'),
    array('g729'    ,'' , '5'),
    array('ilbc'    ,'' , '6'),
    array('g723'    ,'' , '7'),
    array('g726aal2','' , '8'),
    array('adpcm'   ,'' , '9'),
    array('lpc10'   ,'' ,'10'),
    array('speex'   ,'' ,'11'),
    array('g722'    ,'' ,'12'),
    );

	// Now insert minimal codec rows
	$compiled = $db->prepare("INSERT INTO sipsettings (keyword, data, seq, type) values (?,?,?,'1')");
	$result = $db->executeMultiple($compiled,$sip_settings);
	if(DB::IsError($result)) {
		out(_("fatal error occurred populating defaults, check module"));
	} else {
		out(_("ulaw, alaw, gsm added"));
	}
} else {
	out(_("already exists"));
}

out(_("Migrate rtp.conf values if needed and initialize"));

$sql = "SELECT data FROM sipsettings WHERE keyword = 'rtpstart'";
$rtpstart = sql($sql,'getOne');
if (!$rtpstart) {
	$sql = "SELECT value FROM admin WHERE variable = 'RTPSTART'";
	$rtpstart = sql($sql,'getOne');
	if ($rtpstart) {
		out(sprintf(_("saving previous value of %s"), $rtpstart));
	} else {
		$rtpstart = '10000';
		out(sprintf(_("setting default value of %s"), $rtpstart));
	}
	sql("REPLACE INTO sipsettings (keyword, data, seq, type) VALUES ('rtpstart','$rtpstart','0','0')");
}

$sql = "SELECT data FROM sipsettings WHERE keyword = 'rtpend'";
$rtpend = sql($sql,'getOne');
if (!$rtpend) {
	$sql = "SELECT value FROM admin WHERE variable = 'RTPEND'";
	$rtpend = sql($sql,'getOne');
	if ($rtpend) {
		out(sprintf(_("saving previous value of %s"), $rtpend));
	} else {
		$rtpend = '20000';
		out(sprintf(_("setting default value of %s"), $rtpend));
	}
 	sql("REPLACE INTO sipsettings (keyword, data, seq, type) values ('rtpend','$rtpend','1','0')");
}

// One way or another we've converted so we remove the interim variable from admin
sql("DELETE FROM admin WHERE variable IN ('RTPSTART', 'RTPEND')");
