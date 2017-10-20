BEGIN TRANSACTION;

DROP TABLE IF EXISTS `issabelpbx_settings`;
CREATE TABLE `issabelpbx_settings` (
  `keyword` varchar(50) default NULL,
  `value` varchar(255) default NULL,
  `name` varchar(80) default NULL,
  `level` tinyint(1) default 0,
  `description` text default NULL,
  `type` varchar(25) default NULL,
  `options` text default NULL,
  `defaultval` varchar(255) default NULL,
  `readonly` tinyint(1) default 0,
  `hidden` tinyint(1) default 0,
  `category` varchar(50) default NULL,
  `module` varchar(25) default NULL,
  `emptyok` tinyint(1) default 1,
	`sortorder` int(11) default 0,
  PRIMARY KEY  (`keyword`)
 );
CREATE TABLE `admin` (
  `variable` varchar(20) NOT NULL default '',
  `value` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`variable`)
);
INSERT INTO "admin" VALUES('need_reload', 'true');
INSERT INTO "admin" VALUES('version', '2.6.0beta1');
CREATE TABLE `ampusers` (
  `username` varchar(20) NOT NULL default '',
  `password_sha1` varchar(20) NOT NULL default '',
  `extension_low` varchar(20) NOT NULL default '',
  `extension_high` varchar(20) NOT NULL default '',
  `deptname` varchar(20) NOT NULL default '',
  `sections` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`username`)
);
CREATE TABLE `devices` (
  `id` varchar(20) NOT NULL default '',
  `tech` varchar(10) NOT NULL default '',
  `dial` varchar(50) NOT NULL default '',
  `devicetype` varchar(5) NOT NULL default '',
  `user` varchar(50) default NULL,
  `description` varchar(50) default NULL,
  `emergency_cid` varchar(100) default NULL
);
CREATE TABLE `extensions` (
  `context` varchar(45) NOT NULL default 'default',
  `extension` varchar(45) NOT NULL default '',
  `priority` varchar(5) NOT NULL default '1',
  `application` varchar(45) NOT NULL default '',
  `args` varchar(255) default NULL,
  `descr` text,
  `flags` int(1) NOT NULL default '0',
  PRIMARY KEY  (`context`,`extension`,`priority`)
);
INSERT INTO "extensions" VALUES('outrt-001-9_outside', '_9.', '1', 'Macro', 'dialout-trunk,1,${EXTEN:1}', NULL, 0);
INSERT INTO "extensions" VALUES('outrt-001-9_outside', '_9.', '2', 'Macro', 'outisbusy', 'No available circuits', 0);
INSERT INTO "extensions" VALUES('outbound-allroutes', 'include', '1', 'outrt-001-9_outside', '', '', 2);
CREATE TABLE `featurecodes` (
  `modulename` varchar(50) NOT NULL default '',
  `featurename` varchar(50) NOT NULL default '',
  `description` varchar(200) NOT NULL default '',
  `defaultcode` varchar(20) default NULL,
  `customcode` varchar(20) default NULL,
  `enabled` tinyint(4) NOT NULL default '0',
  `providedest` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`modulename`,`featurename`)
--   KEY `enabled` (`enabled`)
);
CREATE TABLE `issabelpbx_log` (
  `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `section` varchar(50) default NULL,
--   `level` enum('error','warning','debug','devel-debug') NOT NULL default 'error',
--  `level` enum('error','warning','debug','devel-debug') NOT NULL,
 `level` varchar(10),
  `status` int(11) NOT NULL default '0',
  `message` text NOT NULL
-- ,
--   PRIMARY KEY  (`id`),
--   KEY `time` (`time`,`level`)
);
INSERT INTO "issabelpbx_log" VALUES(1, '2006-11-06 01:55:36', 'retrieve_conf', 'devel-debug', 0, 'Started retrieve_conf, DB Connection OK');
INSERT INTO "issabelpbx_log" VALUES(2, '2006-11-06 01:55:36', 'retrieve_conf', 'devel-debug', 0, 'Writing extensions_additional.conf');
DELETE FROM sqlite_sequence;
INSERT INTO "sqlite_sequence" VALUES('issabelpbx_log', 2);
INSERT INTO "sqlite_sequence" VALUES('modules', 8);
INSERT INTO "sqlite_sequence" VALUES('recordings', 20);
CREATE TABLE `globals` (
  `variable` varchar(255) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`variable`)
);
CREATE TABLE `iax` (
  `id` varchar(20) NOT NULL default '-1',
  `keyword` varchar(30) NOT NULL default '',
  `data` varchar(255) NOT NULL default '',
  `flags` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`,`keyword`)
);
CREATE TABLE `incoming` (
  `cidnum` varchar(20) default NULL,
  `extension` varchar(50) default NULL,
  `destination` varchar(50) default NULL,
  `faxexten` varchar(20) default NULL,
  `faxemail` varchar(50) default NULL,
  `answer` tinyint(1) default NULL,
  `wait` int(2) default NULL,
  `privacyman` tinyint(1) default NULL,
  `alertinfo` varchar(255) default NULL,
  `ringing` varchar(20) default NULL,
  `mohclass` varchar(80) NOT NULL default 'default',
  `description` varchar(80) default NULL,
	`grppre` varchar(80) default NULL ,
	`delay_answer` int (2) default NULL
, `pricid` VARCHAR(20) DEFAULT NULL);
CREATE TABLE `dahdichandids` (
	`channel` int(11) NOT NULL default '0',
	`description` varchar(40) NOT NULL default '',
	`did` varchar(60) NOT NULL default '',
	PRIMARY KEY  (channel)
);
CREATE TABLE `modules` (
  `id` INTEGER NOT NULL  PRIMARY KEY AUTOINCREMENT,
  `modulename` varchar(50) NOT NULL default '',
  `version` varchar(20) NOT NULL default '',
  `enabled` tinyint(4) NOT NULL default '0'
);
INSERT INTO "modules" VALUES(1, 'framework', '2.6.0.alpha1.2', 1);
INSERT INTO "modules" VALUES(2, 'core', '2.6.0beta1.0', 1);
INSERT INTO "modules" VALUES(3, 'dashboard', '2.5.0.7', 1);
INSERT INTO "modules" VALUES(4, 'featurecodeadmin', '2.5.0.4', 1);
INSERT INTO "modules" VALUES(5, 'voicemail', '2.5.1.6', 1);
INSERT INTO "modules" VALUES(6, 'recordings', '3.3.9.0', 1);
INSERT INTO "modules" VALUES(7, 'music', '2.5.1.3', 1);
INSERT INTO "modules" VALUES(8, 'conferences', '2.6.0.0', 1);
CREATE TABLE `module_xml` (
	`id` varchar(20) NOT NULL default 'xml',
	`time` int(11) NOT NULL default '0',
	`data` mediumblob NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `sip` (
  `id` varchar(20) NOT NULL default '-1',
  `keyword` varchar(30) NOT NULL default '',
  `data` varchar(255) NOT NULL default '',
  `flags` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`,`keyword`)
);
CREATE TABLE `users` (
  `extension` varchar(20) NOT NULL default '',
  `password` varchar(20) default NULL,
  `name` varchar(50) default NULL,
  `voicemail` varchar(50) default NULL,
  `ringtimer` int(3) default NULL,
  `noanswer` varchar(100) default NULL,
  `recording` varchar(50) default NULL,
  `outboundcid` varchar(50) default NULL,
	`mohclass` VARCHAR ( 80 ) DEFAULT "default", 
	`sipname` VARCHAR ( 50 ) NULL,
  `noanswer_cid` varchar(20) NOT NULL default '',
  `busy_cid` varchar(20) NOT NULL default '',
  `chanunavail_cid` varchar(20) NOT NULL default '',
  `noanswer_dest` varchar(255) NOT NULL default '',
  `busy_dest` varchar(255) NOT NULL default '',
  `chanunavail_dest` varchar(255) NOT NULL default ''
);
CREATE TABLE `dahdi` (
  `id` varchar(20) NOT NULL default '-1',
  `keyword` varchar(30) NOT NULL default '',
  `data` varchar(255) NOT NULL default '',
  `flags` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`,`keyword`)
);
CREATE TABLE notifications (
  module varchar(24) NOT NULL default '',
  id varchar(24) NOT NULL default '',
  `level` int(11) NOT NULL default '0',
  display_text varchar(255) NOT NULL default '',
  extended_text blob NOT NULL,
  link varchar(255) NOT NULL default '',
  `reset` tinyint(4) NOT NULL default '0',
	candelete tinyint(4) NOT NULL default '0',
  `timestamp` int(11) NOT NULL default '0',
  PRIMARY KEY  (module,id)
);
CREATE TABLE `cronmanager` (
  `module` varchar(24) NOT NULL default '',
  `id` varchar(24) NOT NULL default '',
  `time` varchar(5) default NULL,
  `freq` int(11) NOT NULL default '0',
  `lasttime` int(11) NOT NULL default '0',
  `command` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`module`,`id`)
);
INSERT INTO "cronmanager" VALUES('module_admin', 'UPDATES', '4', 24, 0, '/var/lib/asterisk/bin/module_admin listonline');
CREATE TABLE `trunks` 
	( 
		`trunkid` INTEGER,
		`name` VARCHAR( 50 ) NOT NULL DEFAULT '', 
		`tech` VARCHAR( 20 ) NOT NULL , 
		`outcid` VARCHAR( 40 ) NOT NULL DEFAULT '', 
		`keepcid` VARCHAR( 4 ) DEFAULT 'off',
		`maxchans` VARCHAR( 6 ) DEFAULT '',
		`failscript` VARCHAR( 255 ) NOT NULL DEFAULT '', 
		`dialoutprefix` VARCHAR( 255 ) NOT NULL DEFAULT '', 
		`channelid` VARCHAR( 255 ) NOT NULL DEFAULT '', 
		`usercontext` VARCHAR( 255 ) NULL, 
		`provider` VARCHAR( 40 ) NULL, 
		`disabled` VARCHAR( 4 ) DEFAULT 'off',
		`continue` VARCHAR( 4 ) DEFAULT 'off',
	
		PRIMARY KEY  (`trunkid`, `tech`, `channelid`) 
	);
INSERT INTO "trunks" VALUES(1, '', 'dahdi', '', '', '', '', '', 'g0', '', NULL, 'off', 'off');
CREATE TABLE `trunks_dialpatterns` 
( 
	`trunkid` INTEGER,
	`rule` VARCHAR( 255 ) NOT NULL, 
	`seq` INTEGER,
	PRIMARY KEY  (`trunkid`, `rule`, `seq`) 
);
INSERT INTO "trunks_dialpatterns" VALUES(4, '500+1|.', 1);
CREATE TABLE recordings ( 
		`id` integer NOT NULL PRIMARY KEY AUTOINCREMENT, 
		displayname VARCHAR(50) , filename BLOB, description 
		VARCHAR(254), `fcode` TINYINT( 1 ) DEFAULT 0, `fcode_pass` VARCHAR( 20 ) NULL);
CREATE TABLE `meetme` ( 
	`exten` VARCHAR( 50 ) NOT NULL , 
	`options` VARCHAR( 15 ) , 
	`userpin` VARCHAR( 50 ) , 
	`adminpin` VARCHAR( 50 ) , 
	`description` VARCHAR( 50 ) , 
	`joinmsg` VARCHAR( 255 ) , 
	joinmsg_id INTEGER
);

DROP  TABLE IF EXISTS `outbound_routes`;
CREATE TABLE `outbound_routes` (
	`route_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	`name` VARCHAR( 40 ),
	`outcid` VARCHAR( 40 ),
	`outcid_mode` VARCHAR( 20 ),
	`password` VARCHAR( 30 ),
	`emergency_route` VARCHAR( 4 ),
	`intracompany_route` VARCHAR( 4 ),
	`mohclass` VARCHAR( 80 ),
	`time_group_id` INTEGER DEFAULT NULL,
	`dest` VARCHAR(255) DEFAULT NULL
);

DROP TABLE IF EXISTS `outbound_route_patterns`;
CREATE TABLE `outbound_route_patterns` (
	`route_id` INTEGER NOT NULL,
	`match_pattern_prefix` VARCHAR( 60 ),
	`match_pattern_pass` VARCHAR( 60 ),
	`match_cid` VARCHAR( 60 ),
	`prepend_digits` VARCHAR( 100 ),
  PRIMARY KEY (`route_id`, `match_pattern_prefix`, `match_pattern_pass`,`match_cid`,`prepend_digits`)
);

DROP TABLE IF EXISTS `outbound_route_trunks`;
CREATE TABLE `outbound_route_trunks` (
	`route_id` INTEGER NOT NULL,
	`trunk_id` INTEGER NOT NULL,
	`seq` INTEGER NOT NULL,
  PRIMARY KEY  (`route_id`, `trunk_id`, `seq`) 
);

DROP TABLE IF EXISTS `outbound_route_sequence`;
CREATE TABLE `outbound_route_sequence` (
	`route_id` INTEGER NOT NULL,
	`seq` INTEGER NOT NULL,
  PRIMARY KEY  (`route_id`, `seq`) 
);
COMMIT;
