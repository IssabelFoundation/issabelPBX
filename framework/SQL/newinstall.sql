CREATE TABLE `admin` (
`variable` varchar(20) PRIMARY KEY NOT NULL,
`value` varchar(80) NOT NULL
) DEFAULT CHARSET=utf8mb4;

INSERT INTO `admin` VALUES("need_reload","false");
INSERT INTO `admin` VALUES("version","2.11.0.49");
INSERT INTO `admin` VALUES("ALLOW_SIP_ANON","no");

CREATE TABLE `ampusers` (
`username` varchar(255) PRIMARY KEY NOT NULL,
`password_sha1` varchar(40) NOT NULL,
`extension_low` varchar(20) NOT NULL,
`extension_high` varchar(20) NOT NULL,
`deptname` varchar(20) NOT NULL,
`sections` TEXT NOT NULL
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cronmanager` (
`module` varchar(24) NOT NULL,
`id` varchar(24) NOT NULL,
`time` varchar(5) ,
`freq` INTEGER NOT NULL,
`lasttime` INTEGER NOT NULL,
`command` varchar(255) NOT NULL,
PRIMARY KEY (`module`,`id`)
) DEFAULT CHARSET=utf8mb4;

INSERT INTO `cronmanager` VALUES("module_admin","UPDATES","21","24","0","/var/lib/asterisk/bin/module_admin listonline");

CREATE TABLE `dahdi` (
`id` varchar(20) NOT NULL,
`keyword` varchar(30) NOT NULL,
`data` varchar(255) NOT NULL,
`flags` INTEGER NOT NULL,
PRIMARY KEY (`id`,`keyword`)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `devices` (
`id` varchar(20) PRIMARY KEY NOT NULL,
`tech` varchar(10) NOT NULL,
`dial` varchar(50) NOT NULL,
`devicetype` varchar(5) NOT NULL,
`user` varchar(50) ,
`description` varchar(50) ,
`emergency_cid` varchar(100) 
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `extensions` (
`context` varchar(45) NOT NULL,
`extension` varchar(45) NOT NULL,
`priority` varchar(5) NOT NULL,
`application` varchar(45) NOT NULL,
`args` varchar(255) ,
`descr` TEXT,
`flags` INTEGER NOT NULL,
PRIMARY KEY (`context`,`extension`,`priority`)
) DEFAULT CHARSET=utf8mb4;

INSERT INTO `extensions` VALUES("outrt-001-9_outside","_9.","1","Macro","dialout-trunk,1,${EXTEN:1}","","0");
INSERT INTO `extensions` VALUES("outrt-001-9_outside","_9.","2","Macro","outisbusy","No available circuits","0");
INSERT INTO `extensions` VALUES("outbound-allroutes","include","1","outrt-001-9_outside","","","2");

CREATE TABLE `featurecodes` (
`modulename` varchar(50) NOT NULL,
`featurename` varchar(50) NOT NULL,
`description` varchar(200) NOT NULL,
`defaultcode` varchar(20) ,
`customcode` varchar(20) ,
`enabled` tinyint(1) NOT NULL,
`providedest` tinyint(1) NOT NULL,
PRIMARY KEY (`modulename`,`featurename`)
) DEFAULT CHARSET=utf8mb4;

INSERT INTO `featurecodes` VALUES("core","userlogon","User Logon","*11","","1","0");
INSERT INTO `featurecodes` VALUES("core","userlogoff","User Logoff","*12","","1","0");
INSERT INTO `featurecodes` VALUES("core","zapbarge","ZapBarge","888","","1","1");
INSERT INTO `featurecodes` VALUES("core","simu_pstn","Simulate Incoming Call","7777","","1","1");
INSERT INTO `featurecodes` VALUES("fax","simu_fax","Dial System FAX","666","","1","1");
INSERT INTO `featurecodes` VALUES("core","chanspy","ChanSpy","555","","1","1");
INSERT INTO `featurecodes` VALUES("core","pickup","Directed Call Pickup","**","","1","0");
INSERT INTO `featurecodes` VALUES("core","pickupexten","Asterisk General Call Pickup","*8","","1","0");
INSERT INTO `featurecodes` VALUES("core","blindxfer","In-Call Asterisk Blind Transfer","##","","1","0");
INSERT INTO `featurecodes` VALUES("core","atxfer","In-Call Asterisk Attended Transfer","*2","","1","0");
INSERT INTO `featurecodes` VALUES("core","automon","In-Call Asterisk Toggle Call Recording","*1","","1","0");
INSERT INTO `featurecodes` VALUES("core","disconnect","In-Call Asterisk Disconnect Code","**","","1","0");
INSERT INTO `featurecodes` VALUES("queues","que_pause_toggle","Queue Pause Toggle","*46","","1","0");
INSERT INTO `featurecodes` VALUES("infoservices","calltrace","Call Trace","*69","","1","0");
INSERT INTO `featurecodes` VALUES("infoservices","echotest","Echo Test","*43","","1","1");
INSERT INTO `featurecodes` VALUES("infoservices","speakingclock","Speaking Clock","*60","","1","1");
INSERT INTO `featurecodes` VALUES("infoservices","speakextennum","Speak Your Exten Number","*65","","1","0");
INSERT INTO `featurecodes` VALUES("voicemail","myvoicemail","My Voicemail","*97","","1","0");
INSERT INTO `featurecodes` VALUES("voicemail","dialvoicemail","Dial Voicemail","*98","","1","1");
INSERT INTO `featurecodes` VALUES("recordings","record_save","Save Recording","*77","","1","0");
INSERT INTO `featurecodes` VALUES("recordings","record_check","Check Recording","*99","","1","0");
INSERT INTO `featurecodes` VALUES("callforward","cfon","Call Forward All Activate","*72","","1","0");
INSERT INTO `featurecodes` VALUES("callforward","cfoff","Call Forward All Deactivate","*73","","1","0");
INSERT INTO `featurecodes` VALUES("callforward","cfoff_any","Call Forward All Prompting Deactivate","*74","","1","0");
INSERT INTO `featurecodes` VALUES("callforward","cfbon","Call Forward Busy Activate","*90","","1","0");
INSERT INTO `featurecodes` VALUES("callforward","cfboff","Call Forward Busy Deactivate","*91","","1","0");
INSERT INTO `featurecodes` VALUES("callforward","cfboff_any","Call Forward Busy Prompting Deactivate","*92","","1","0");
INSERT INTO `featurecodes` VALUES("callforward","cfuon","Call Forward No Answer/Unavailable Activate","*52","","1","0");
INSERT INTO `featurecodes` VALUES("callforward","cfuoff","Call Forward No Answer/Unavailable Deactivate","*53","","1","0");
INSERT INTO `featurecodes` VALUES("callwaiting","cwon","Call Waiting - Activate","*70","","1","0");
INSERT INTO `featurecodes` VALUES("callwaiting","cwoff","Call Waiting - Deactivate","*71","","1","0");
INSERT INTO `featurecodes` VALUES("dictate","dodictate","Perform dictation","*34","","1","0");
INSERT INTO `featurecodes` VALUES("dictate","senddictate","Email completed dictation","*35","","1","0");
INSERT INTO `featurecodes` VALUES("donotdisturb","dnd_on","DND Activate","*78","","1","0");
INSERT INTO `featurecodes` VALUES("donotdisturb","dnd_off","DND Deactivate","*79","","1","0");
INSERT INTO `featurecodes` VALUES("donotdisturb","dnd_toggle","DND Toggle","*76","","1","0");
INSERT INTO `featurecodes` VALUES("findmefollow","fmf_toggle","Findme Follow Toggle","*21","","1","0");
INSERT INTO `featurecodes` VALUES("paging","intercom-prefix","Intercom prefix","*80","","0","0");
INSERT INTO `featurecodes` VALUES("paging","intercom-on","User Intercom Allow","*54","","0","0");
INSERT INTO `featurecodes` VALUES("paging","intercom-off","User Intercom Disallow","*55","","0","0");
INSERT INTO `featurecodes` VALUES("pbdirectory","app-pbdirectory","Phonebook dial-by-name directory","411","","1","1");
INSERT INTO `featurecodes` VALUES("blacklist","blacklist_add","Blacklist a number","*30","","1","1");
INSERT INTO `featurecodes` VALUES("blacklist","blacklist_remove","Remove a number from the blacklist","*31","","1","1");
INSERT INTO `featurecodes` VALUES("blacklist","blacklist_last","Blacklist the last caller","*32","","1","0");
INSERT INTO `featurecodes` VALUES("speeddial","callspeeddial","Speeddial prefix","*0","","1","0");
INSERT INTO `featurecodes` VALUES("speeddial","setspeeddial","Set user speed dial","*75","","1","0");
INSERT INTO `featurecodes` VALUES("queues","que_toggle","Queue Toggle","*45","","1","0");
INSERT INTO `featurecodes` VALUES("callforward","cf_toggle","Call Forward Toggle","*740","","1","0");
INSERT INTO `featurecodes` VALUES("parking","parkedcall","Pickup ParkedCall Prefix","*85","","1","1");
INSERT INTO `featurecodes` VALUES("voicemail","directdialvoicemail","Direct Dial Prefix","*","","1","0");
INSERT INTO `featurecodes` VALUES("callforward","cfpon","Call Forward All Prompting Activate","*720","","1","0");
INSERT INTO `featurecodes` VALUES("callforward","cfbpon","Call Forward Busy Prompting Activate","*900","","1","0");
INSERT INTO `featurecodes` VALUES("callforward","cfupon","Call Forward No Answer/Unavailable Prompting Activate","*520","","1","0");
INSERT INTO `featurecodes` VALUES("conferences","conf_status","Conference Status","*87","","1","0");
INSERT INTO `featurecodes` VALUES("daynight","toggle-mode-all","All: Call Flow Toggle","*28","","1","0");
INSERT INTO `featurecodes` VALUES("queues","que_callers","Queue Callers","*47","","1","0");
INSERT INTO `featurecodes` VALUES("timeconditions","toggle-mode-all","All: Time Condition Override","*27","","1","0");
INSERT INTO `featurecodes` VALUES("bosssecretary","bsc_toggle","Bosssecretary Toggle","*152","","1","0");
INSERT INTO `featurecodes` VALUES("bosssecretary","bsc_on","Bosssecretary On","*153","","1","0");
INSERT INTO `featurecodes` VALUES("bosssecretary","bsc_off","Bosssecretary Off","*154","","1","0");

CREATE TABLE `issabelpbx_log` (
`id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
`time` datetime NOT NULL,
`section` varchar(50) ,
`level` char(11) NOT NULL,
`status` INTEGER NOT NULL,
`message` TEXT NOT NULL
) DEFAULT CHARSET=utf8mb4;

INSERT INTO `issabelpbx_log` VALUES("1","2006-11-06 01:55:36","retrieve_conf","devel-debug","0","Started retrieve_conf, DB Connection OK");
INSERT INTO `issabelpbx_log` VALUES("2","2006-11-06 01:55:36","retrieve_conf","devel-debug","0","Writing extensions_additional.conf");

CREATE TABLE `issabelpbx_settings` (
`keyword` varchar(50) PRIMARY KEY NOT NULL,
`value` varchar(255) ,
`name` varchar(80) ,
`level` tinyint(1) ,
`description` TEXT,
`type` varchar(25) ,
`options` TEXT,
`defaultval` varchar(255) ,
`readonly` tinyint(1) ,
`hidden` tinyint(1) ,
`category` varchar(50) ,
`module` varchar(25) ,
`emptyok` tinyint(1) ,
`sortorder` INTEGER
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `globals` (
`variable` varchar(255) PRIMARY KEY NOT NULL,
`value` varchar(255) NOT NULL
) DEFAULT CHARSET=utf8mb4;

INSERT INTO `globals` VALUES("FAX_RX","system");
INSERT INTO `globals` VALUES("FAX_RX_EMAIL","fax@mydomain.com");
INSERT INTO `globals` VALUES("FAX_RX_FROM","fax@issabel.org");

CREATE TABLE `iax` (
`id` varchar(20) NOT NULL,
`keyword` varchar(30) NOT NULL,
`data` varchar(255) NOT NULL,
`flags` INTEGER NOT NULL,
PRIMARY KEY (`id`,`keyword`)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `incoming` (
`cidnum` varchar(20) ,
`extension` varchar(50) NOT NULL,
`destination` varchar(50) ,
`faxexten` varchar(20) ,
`faxemail` varchar(50) ,
`answer` tinyint(1) ,
`wait` INTEGER,
`privacyman` tinyint(1) ,
`alertinfo` varchar(255) ,
`ringing` varchar(20) ,
`mohclass` varchar(80) NOT NULL,
`description` varchar(80) ,
`grppre` varchar(80) ,
`delay_answer` INTEGER,
`pricid` varchar(20) ,
`pmmaxretries` varchar(2) ,
`pmminlength` varchar(2) 
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `module_xml` (
`id` varchar(20) PRIMARY KEY NOT NULL,
`time` INTEGER NOT NULL,
`data` MEDIUMTEXT NOT NULL
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `modules` (
`id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
`modulename` varchar(50) NOT NULL,
`version` varchar(20) NOT NULL,
`enabled` tinyint(1) NOT NULL
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `notifications` (
`module` varchar(24) NOT NULL,
`id` varchar(24) NOT NULL,
`level` INTEGER NOT NULL,
`display_text` varchar(255) NOT NULL,
`extended_text` TEXT NOT NULL,
`link` varchar(255) NOT NULL,
`reset` tinyint(1) NOT NULL,
`candelete` tinyint(1) NOT NULL,
`timestamp` INTEGER NOT NULL,
PRIMARY KEY (`module`,`id`)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `outbound_route_patterns` (
`route_id` INTEGER NOT NULL,
`match_pattern_prefix` varchar(60) NOT NULL,
`match_pattern_pass` varchar(60) NOT NULL,
`match_cid` varchar(60) NOT NULL,
`prepend_digits` varchar(100) NOT NULL,
PRIMARY KEY (`route_id`,`match_pattern_prefix`,`match_pattern_pass`,`match_cid`,`prepend_digits`)
) DEFAULT CHARSET=utf8mb4;

INSERT INTO `outbound_route_patterns` VALUES("1","9",".","","");

CREATE TABLE `outbound_route_sequence` (
`route_id` INTEGER NOT NULL,
`seq` INTEGER NOT NULL,
PRIMARY KEY (`route_id`,`seq`)
) DEFAULT CHARSET=utf8mb4;

INSERT INTO `outbound_route_sequence` VALUES("1","0");

CREATE TABLE `outbound_route_trunks` (
`route_id` INTEGER NOT NULL,
`trunk_id` INTEGER NOT NULL,
`seq` INTEGER NOT NULL,
PRIMARY KEY (`route_id`,`trunk_id`,`seq`)
) DEFAULT CHARSET=utf8mb4;

INSERT INTO `outbound_route_trunks` VALUES("1","1","0");

CREATE TABLE `outbound_routes` (
`route_id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
`name` varchar(40) ,
`outcid` varchar(40) ,
`outcid_mode` varchar(20) ,
`password` varchar(30) ,
`emergency_route` varchar(4) ,
`intracompany_route` varchar(4) ,
`mohclass` varchar(80) ,
`time_group_id` INTEGER,
`dest` varchar(255) 
) DEFAULT CHARSET=utf8mb4;

INSERT INTO `outbound_routes` VALUES("1","9_outside","","","","","","","0","");

CREATE TABLE `sip` (
`id` varchar(20) NOT NULL,
`keyword` varchar(30) NOT NULL,
`data` varchar(255) NOT NULL,
`flags` INTEGER NOT NULL,
PRIMARY KEY (`id`,`keyword`)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `trunk_dialpatterns` (
`trunkid` INTEGER NOT NULL,
`match_pattern_prefix` varchar(50) NOT NULL,
`match_pattern_pass` varchar(50) NOT NULL,
`prepend_digits` varchar(50) NOT NULL,
`seq` INTEGER NOT NULL,
PRIMARY KEY (`trunkid`,`match_pattern_prefix`,`match_pattern_pass`,`prepend_digits`,`seq`)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `users` (
`extension` varchar(20) PRIMARY KEY NOT NULL,
`password` varchar(20) ,
`name` varchar(50) ,
`voicemail` varchar(50) ,
`ringtimer` INTEGER,
`noanswer` varchar(100) ,
`recording` varchar(50) ,
`outboundcid` varchar(50) ,
`sipname` varchar(50) ,
`mohclass` varchar(80) ,
`noanswer_cid` varchar(20) ,
`busy_cid` varchar(20) ,
`chanunavail_cid` varchar(20) ,
`noanswer_dest` varchar(255) ,
`busy_dest` varchar(255) ,
`chanunavail_dest` varchar(255) 
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `dahdichandids` (
`channel` INTEGER PRIMARY KEY NOT NULL,
`description` varchar(40) NOT NULL,
`did` varchar(60) NOT NULL
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `pjsipsettings` (
`keyword` varchar(50) NOT NULL,
`data` varchar(255) NOT NULL,
`seq` tinyint(1) NOT NULL,
`type` tinyint(1) DEFAULT '0' NOT NULL,
PRIMARY KEY (`keyword`,`seq`,`type`)
) DEFAULT CHARSET=utf8mb4;

INSERT INTO `pjsipsettings` VALUES("allowguest","no","10","0");

