<?php
/* $Id:$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;
global $amp_conf;

$sql = <<< END
CREATE TABLE IF NOT EXISTS `sipsettings` (
  `keyword` VARCHAR (50) NOT NULL default '',
  `data`    VARCHAR (255) NOT NULL default '',
  `seq`     TINYINT (1) NOT NULL default '0',
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

	sql("INSERT IGNORE INTO sipsettings (keyword,data,seq) values ('callevents','yes',0)");

} else {
	out(_("already exists"));
}


$sql = <<< END
CREATE TABLE IF NOT EXISTS `pjsipsettings` (
  `keyword` VARCHAR (50) NOT NULL default '',
  `data`    VARCHAR (255) NOT NULL default '',
  `seq`     TINYINT (1) NOT NULL default '0',
  `type`    TINYINT (1) NOT NULL default '0',
  PRIMARY KEY (`keyword`,`seq`,`type`)
)
END;

outn(_("checking for pjsipsettings table.."));
$tsql = "SELECT * FROM `pjsipsettings` limit 1";
$check = $db->getRow($tsql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	out(_("none, creating table"));
	// table does not exist, create it
	sql($sql);

	sql("INSERT INTO pjsipsettings (keyword,data,seq) values ('bindport','5066',1)");
	sql("INSERT INTO pjsipsettings (keyword,data,seq) values ('tlsbindport','5067',1)");
	sql("INSERT INTO pjsipsettings (keyword,data,seq) values ('certtfile','/etc/asterisk/keys/asterisk.pem',1)");
	sql("INSERT INTO pjsipsettings (keyword,data,seq) values ('ALLOW_SIP_ANON','no',1)");

} 

out(_("Check for pjsipsettings values.."));
$sql = "SELECT data FROM pjsipsettings WHERE keyword = 'bindport'";
$PJbindport = sql($sql,'getOne');
if (!$PJbindport) {
	sql("INSERT INTO pjsipsettings (keyword,data,seq) values ('bindport','5066',1)");
	out(_("Add bindport.."));
}
$sql = "SELECT data FROM pjsipsettings WHERE keyword = 'tlsbindport'";
$PJtlsbindport = sql($sql,'getOne');
if (!$PJtlsbindport) {
	sql("INSERT INTO pjsipsettings (keyword,data,seq) values ('tlsbindport','5067',1)");
	out(_("Add tlsbindport.."));
}
$sql = "SELECT data FROM pjsipsettings WHERE keyword = 'certtfile'";
$PJcerttfile = sql($sql,'getOne');
if (!$PJcerttfile) {
	sql("INSERT INTO pjsipsettings (keyword,data,seq) values ('certtfile','/etc/asterisk/keys/asterisk.pem',1)");
	out(_("Add certfile.."));
}
$sql = "SELECT data FROM pjsipsettings WHERE keyword = 'ALLOW_SIP_ANON'";
$PJALLOW_SIP_ANON = sql($sql,'getOne');
if (!$PJALLOW_SIP_ANON) {
	sql("INSERT INTO pjsipsettings (keyword,data,seq) values ('ALLOW_SIP_ANON','no',1)");
	out(_("Add ALLOW_SIP_ANON.."));
}

$sql = "SELECT data FROM pjsipsettings WHERE data = '1' and type = 1";
$PJcodecs = sql($sql,'getOne');
if (!$PJcodecs) {
	sql("INSERT INTO pjsipsettings (keyword,data,seq,type) values ('ulaw', '1', 0, 1), 
	('alaw', '1', 1, 1), ('slin', '', 2, 1), ('g726', '', 3, 1), ('gsm', '1', 4, 1), 
	('g729', '', 5, 1), ('ilbc', '1', 6, 1), ('g723', '', 7, 1), ('g726aal2', '', 8, 1), 
	('adpcm', '', 9, 1), ('lpc10', '', 10, 1), ('speex', '', 11, 1), ('g722', '', 12, 1)");
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
