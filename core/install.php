<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

//for translation only
if (false) {
_("Core");
_("User Logon");
_("User Logoff");
_("ZapBarge");
_("ChanSpy");
_("Simulate Incoming Call");
_("Directed Call Pickup");
_("Asterisk General Call Pickup");
_("In-Call Asterisk Blind Transfer");
_("In-Call Asterisk Attended Transfer");
_("In-Call Asterisk Toggle Call Recording");
_("In-Call Asterisk Disconnect Code");
}

function did_migrate($incoming){
	global $db;

	foreach ($incoming as $key => $val) {
		${$key} = $db->escapeSimple($val);
	}

	// Check to make sure the did is not being used elsewhere
	//
	$sql = "SELECT * FROM incoming WHERE cidnum = '' AND extension = '$extension'";
	$existing = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($existing)) {
		outn(sprintf(_("ERROR: trying to check if %s already in use"),$extension));
		return false;
	}
	if (empty($existing)) {
		$sql="INSERT INTO incoming (cidnum,extension,destination,faxexten,faxemail,answer,wait,privacyman,alertinfo, ringing, mohclass, description, grppre) values ('$cidnum','$extension','$destination','$faxexten','$faxemail','$answer','$wait','$privacyman','$alertinfo', '$ringing', '$mohclass', '$description', '$grppre')";
		sql($sql);
		return true;
	} else {
		return false;
	}
}

$issabelpbx_conf =& issabelpbx_conf::create();

$fcc = new featurecode('core', 'userlogon');
$fcc->setDescription('User Logon');
$fcc->setDefault('*11');
$fcc->update();
unset($fcc);

$fcc = new featurecode('core', 'userlogoff');
$fcc->setDescription('User Logoff');
$fcc->setDefault('*12');
$fcc->update();
unset($fcc);

$fcc = new featurecode('core', 'zapbarge');
$fcc->setDescription('ZapBarge');
$fcc->setDefault('888');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);

$fcc = new featurecode('core', 'chanspy');
$fcc->setDescription('ChanSpy');
$fcc->setDefault('555');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);

$fcc = new featurecode('core', 'simu_pstn');
$fcc->setDescription('Simulate Incoming Call');
$fcc->setDefault('7777');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);

$fcc = new featurecode('core', 'pickup');
$fcc->setDescription('Directed Call Pickup');
$fcc->setDefault('**');
$fcc->update();
unset($fcc);

$fcc = new featurecode('core', 'pickupexten');
$fcc->setDescription('Asterisk General Call Pickup');
$fcc->setDefault('*8');
$fcc->update();
unset($fcc);

$fcc = new featurecode('core', 'blindxfer');
$fcc->setDescription('In-Call Asterisk Blind Transfer');
$fcc->setDefault('##');
$fcc->update();
unset($fcc);

$fcc = new featurecode('core', 'atxfer');
$fcc->setDescription('In-Call Asterisk Attended Transfer');
$fcc->setDefault('*2');
$fcc->update();
unset($fcc);

$fcc = new featurecode('core', 'automon');
$fcc->setDescription('In-Call Asterisk Toggle Call Recording');
$fcc->setDefault('*1');
$fcc->update();
unset($fcc);

$fcc = new featurecode('core', 'disconnect');
$fcc->setDescription('In-Call Asterisk Disconnect Code');
$fcc->setDefault('**');
$fcc->update();
unset($fcc);

// OUTBOUND_CID_UPDATE
//
$set['value'] = true;
$set['defaultval'] =& $set['value'];
$set['options'] = '';
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = '';
$set['category'] = 'Dialplan and Operational';
$set['emptyok'] = 0;
$set['name'] = 'Display CallerID on Calling Phone';
$set['description'] = "When set to true and when CONNECTEDLINE() capabilities are configured and supported by your handset, the CID value being transmitted on this call will be updated on your handset in the CNAM field prepended with CID: so you know what is being presented to the caller if the outbound trunk supports and honors setting the transmitted CID.";
$set['type'] = CONF_TYPE_BOOL;
$issabelpbx_conf->define_conf_setting('OUTBOUND_CID_UPDATE',$set);

// OUTBOUND_DIAL_UPDATE
//
$set['value'] = true;
$set['defaultval'] =& $set['value'];
$set['options'] = '';
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = '';
$set['category'] = 'Dialplan and Operational';
$set['emptyok'] = 0;
$set['name'] = 'Display Dialed Number on Calling Phone';
$set['description'] = "When set to true and when CONNECTEDLINE() capabilities are configured and supported by your handset, the number actually dialled will be updated on your handset in the CNUM field. This allows you to see the final manipulation of your number after outbound route and trunk dial manipulation rules have been applied. For example, if you have configured 7 digit dialing on a North America dialplan, the ultimate 10 or 11 digit transmission will be displayed back. Any 'Outbound Dial Prefixes' configured at the trunk level will NOT be shown as these are foten analog line pauses (w) or other characters that distort the CNUM field on updates.";
$set['type'] = CONF_TYPE_BOOL;
$issabelpbx_conf->define_conf_setting('OUTBOUND_DIAL_UPDATE',$set);

// Version 2.5 Upgrade needs to migrate directdid user info to incoming table
//
outn(_("Checking if directdids need migrating.."));
$sql = "SELECT `directdid` FROM `users`";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(!DB::IsError($check)) {
	out(_("starting migration"));
	$errors = 0;
	$sql = "SELECT * FROM `users` WHERE `directdid` != '' AND `directdid` IS NOT NULL";
	$direct_dids_arr = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	if(!DB::IsError($direct_dids_arr)) {
		foreach ($direct_dids_arr as $direct_dids) {
			$did_vars['destination'] = 'from-did-direct,'.$direct_dids['extension'].',1';
			$did_vars['extension']   = $direct_dids['directdid'];
			$did_vars['cidnum']      = '';
			$did_vars['faxexten']    = $direct_dids['faxexten'];
			$did_vars['faxemail']    = $direct_dids['faxemail'];
			$did_vars['answer']      = $direct_dids['answer'];
			$did_vars['wait']        = $direct_dids['wait'];
			$did_vars['privacyman']  = $direct_dids['privacyman'];
			$did_vars['alertinfo']   = $direct_dids['didalert'];
			$did_vars['ringing']     = '';
			$did_vars['mohclass']    = $direct_dids['mohclass'];
			$did_vars['description'] = _("User: ").$direct_dids['extension'];
			$did_vars['grppre']      = '';
			if (!did_migrate($did_vars)) {
				out(sprintf(_("ERROR: failed to insert %s for user %s"),$direct_dids['directdid'],$direct_dids['extension']));
				$errors++;
			}
		}
		if ($errors) {
			out(sprintf(_("There were %s failures migrating directdids, users table not being changed"),$errors));
		} else {
			$migrate_array = array('directdid', 'didalert', 'mohclass', 'faxexten', 'faxemail', 'answer', 'wait', 'privacyman');
			foreach ($migrate_array as $field) {
				outn(sprintf(_("Removing field %s from users table.."),$field));
				$sql = "ALTER TABLE `users` DROP `".$field."`";
				$results = $db->query($sql);
				if (DB::IsError($results)) {
					out(_("not present"));
				} else {
					out(_("removed"));
				}
			}
		}
	} else {
		out(_("ERROR: could not access user table to migrate directdids to incoming table, aborting"));
	}
} else {
	out(_("already done"));
}

// Add callgroup, pickupgroup to zap

outn(_("updating zap callgroup, pickupgroup.."));
$sql = "SELECT `id` FROM `devices` WHERE `tech` = 'zap'";
$results = $db->getCol($sql);
if(DB::IsError($results)) {
	$results = null;
}
$count_pickup = 0;
$count_callgroup = 0;
if (isset($results) && !empty($results)) {
	foreach ($results as $device) {
		// if the insert fails then it is already there since it will violate the primary key but that is ok
		//
		$sql = "INSERT INTO `zap` (`id`, `keyword`, `data`, `flags`) VALUES ('$device', 'callgroup', '', '0')";
		$try = $db->query($sql);
		if(!DB::IsError($try)) {
			$count_pickup++;
		}
		$sql = "INSERT INTO `zap` (`id`, `keyword`, `data`, `flags`) VALUES ('$device', 'pickupgroup', '', '0')";
		$try = $db->query($sql);
		if(!DB::IsError($try)) {
			$count_callgroup++;
		}
	}
}
if ($count_callgroup || $count_pickup) {
	out(sprintf(_("updated %s callgroups, %s pickupgroups"),$count_callgroup,$count_pickup));
} else {
	out(_("not needed"));
}

// 2.5 new field
//
outn(_("checking for delay_answer field .."));
$sql = "SELECT `delay_answer` FROM `incoming`";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	$sql = "ALTER TABLE `incoming` ADD `delay_answer` INT(2) DEFAULT NULL";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		out(_("fatal error"));
		die_issabelpbx($result->getDebugInfo());
	} else {
		out(_("added"));
	}
} else {
	out(_("already exists"));
}

outn(_("checking for pricid field .."));
$sql = "SELECT `pricid` FROM `incoming`";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	$sql = "ALTER TABLE `incoming` ADD `pricid` VARCHAR(20) DEFAULT NULL";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		out(_("fatal error"));
		die_issabelpbx($result->getDebugInfo());
	} else {
		out(_("added"));
	}
} else {
	out(_("already exists"));
}

// This next set of functions and code are used to migrate from the old
// global variable storage of trunk data to the new trunk table and trunk
// pattern table for localprefixes.conf
//

//Sort trunks for sqlite
function __sort_trunks($a,$b)  {
        global $unique_trunks;
        preg_match("/OUT_([0-9]+)/",$unique_trunks[$a][0],$trunk_num1);
        preg_match("/OUT_([0-9]+)/",$unique_trunks[$b][0],$trunk_num2);
        return ($trunk_num1[1] >= $trunk_num2[1]? 1:-1);
}

function __migrate_trunks_to_table() {

	global $db;
	global $amp_conf;

	$sql = "
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
	) 
	";
	$check = $db->query($sql);
	if(DB::IsError($check)) {
		if($check->getCode() == DB_ERROR_ALREADY_EXISTS) {
			//echo ("already exists\n");
			return false;
		} else {
			die_issabelpbx($check->getDebugInfo());
		}
	}

	// sqlite doesn't support the syntax required for the SQL so we have to do it the hard way
	if (preg_match("/qlite/",$amp_conf["AMPDBENGINE"])) {
		$sqlstr = "SELECT variable, value FROM globals WHERE variable LIKE 'OUT\_%' ESCAPE '\'";
		$my_unique_trunks = sql($sqlstr,"getAll",DB_FETCHMODE_ASSOC);

		$sqlstr = "SELECT variable, value FROM globals WHERE variable LIKE 'OUTDISABLE\_%' ESCAPE '\'";
		$disable_states = sql($sqlstr,"getAll",DB_FETCHMODE_ASSOC);

		foreach($disable_states as $arr)  {
			$disable_states_assoc[$arr['variable']] = $arr['value'];
		}
		global $unique_trunks;
		$unique_trunks = array();

		foreach ($my_unique_trunks as $this_trunk) {

			$trunk_num = substr($this_trunk['variable'],4);
			$this_state = (isset($disable_states_assoc['OUTDISABLE_'.$trunk_num]) ? $disable_states_assoc['OUTDISABLE_'.$trunk_num] : 'off');
			$unique_trunks[] = array($this_trunk['variable'], $this_trunk['value'], $this_state);
		}
		// sort this array using a custom function __sort_trunks(), defined above
		uksort($unique_trunks,"__sort_trunks");
		// re-index the newly sorted array
		foreach($unique_trunks as $arr) {
			$unique_trunks_t[] = array($arr[0],$arr[1],$arr[2]);
		}
		$unique_trunks = $unique_trunks_t;

	} else {
		$sqlstr  = "SELECT t.variable, t.value, d.value state FROM `globals` t ";
		$sqlstr .= "JOIN (SELECT x.variable, x.value FROM globals x WHERE x.variable LIKE 'OUTDISABLE\_%') d ";
		$sqlstr .= "ON substring(t.variable,5) = substring(d.variable,12) WHERE t.variable LIKE 'OUT\_%' ";
		$sqlstr .= "UNION ALL ";
		$sqlstr .= "SELECT v.variable, v.value, concat(substring(v.value,1,0),'off') state  FROM `globals` v ";
		$sqlstr .= "WHERE v.variable LIKE 'OUT\_%' AND concat('OUTDISABLE_',substring(v.variable,5)) NOT IN ";
		$sqlstr .= " ( SELECT variable from globals WHERE variable LIKE 'OUTDISABLE\_%' ) ";
		$sqlstr .= "ORDER BY variable";
		$unique_trunks = sql($sqlstr,"getAll");
	}

	$trunkinfo = array();
	foreach ($unique_trunks as $trunk) {
		list($tech,$name) = explode('/',$trunk[1]);
		$trunkid = ltrim($trunk[0],'OUT_');

		$sqlstr = "
			SELECT `variable`, `value` FROM `globals` WHERE `variable` IN (
				'OUTCID_$trunkid', 'OUTFAIL_$trunkid', 'OUTKEEPCID_$trunkid',
				'OUTMAXCHANS_$trunkid', 'OUTPREFIX_$trunkid')
		";
		$trunk_attribs = sql($sqlstr,'getAll',DB_FETCHMODE_ASSOC);
		$trunk_attrib_hash = array();
		foreach ($trunk_attribs as $attribs) {
			$trunk_attrib_hash[$attribs['variable']] = $attribs['value'];
		}

		switch ($tech) {
			case 'SIP':
				$tech = 'sip';
				$user = sql("SELECT `data` FROM `sip` WHERE `id` = '99999$trunkid' AND `keyword` = 'account'",'getOne');
				break;
			case 'IAX':
			case 'IAX2':
				$tech = 'iax';
				$user = sql("SELECT `data` FROM `iax` WHERE `id` = '99999$trunkid' AND `keyword` = 'account'",'getOne');
				break;
			case 'ZAP':
			case 'DUNDI':
			case 'ENUM':
				$tech = strtolower($tech);
				$user = '';
				break;
			default:
				if (substr($tech,0,4) == 'AMP:') {
					$tech='custom';
					$name = substr($trunk[1],4);
				} else {
					$tech = strtolower($tech);
				}
				$user = '';
		}

		$trunkinfo[] = array(
			'trunkid' =>       $trunkid,
			'tech' =>          $tech,
			'outcid' =>        $trunk_attrib_hash['OUTCID_'.$trunkid],
			'keepcid' =>       $trunk_attrib_hash['OUTKEEPCID_'.$trunkid],
			'maxchans' =>      $trunk_attrib_hash['OUTMAXCHANS_'.$trunkid],
			'failscript' =>    $trunk_attrib_hash['OUTFAIL_'.$trunkid],
			'dialoutprefix' => $trunk_attrib_hash['OUTPREFIX_'.$trunkid],
			'channelid' =>     $name,
			'usercontext' =>   $user,
			'disabled' =>      $trunk[2], // disable state
		);

		$sqlstr = "INSERT INTO `trunks`
			( trunkid, tech, outcid, keepcid, maxchans, failscript, dialoutprefix, channelid, usercontext, disabled)
			VALUES (
				'".$db->escapeSimple($trunkid)."',
				'".$db->escapeSimple($tech)."',
				'".$db->escapeSimple($trunk_attrib_hash['OUTCID_'.$trunkid])."',
				'".$db->escapeSimple($trunk_attrib_hash['OUTKEEPCID_'.$trunkid])."',
				'".$db->escapeSimple($trunk_attrib_hash['OUTMAXCHANS_'.$trunkid])."',
				'".$db->escapeSimple($trunk_attrib_hash['OUTFAIL_'.$trunkid])."',
				'".$db->escapeSimple($trunk_attrib_hash['OUTPREFIX_'.$trunkid])."',
				'".$db->escapeSimple($name)."',
				'".$db->escapeSimple($user)."',
				'".$db->escapeSimple($trunk[2])."'
		  )
		";
		sql($sqlstr);
	}

	return $trunkinfo;
}

// __migrate_trunks_to_table will return false if the trunks table already exists and
// no migration is needed
//
outn(_("Checking if trunk table migration required.."));
$trunks = __migrate_trunks_to_table();
if ($trunks !== false) {
	outn(_("migrating.."));
	foreach ($trunks as $trunk) {
		$tech = $trunk['tech'];
		$trunkid = $trunk['trunkid'];
		switch ($tech) {
			case 'sip':
			case 'iax':
				$sql = "UPDATE `$tech` SET `id` = 'tr-peer-$trunkid' WHERE `id` = '9999$trunkid'";
				sql($sql);
				$sql = "UPDATE `$tech` SET `id` = 'tr-user-$trunkid' WHERE `id` = '99999$trunkid'";
				sql($sql);
				$sql = "UPDATE `$tech` SET `id` = 'tr-reg-$trunkid' WHERE `id` = '9999999$trunkid' AND `keyword` = 'register'";
				sql($sql);
				break;
			default:
				break;
		}
	}
	outn(_("removing globals.."));
	// Don't do this above, in case something goes wrong
	//
	// At this point we have created our trunks table and update the sip and iax files
	// time to get rid of the old globals which will not be auto-generated
	//
	foreach ($trunks as $trunk) {
		$trunkid = $trunk['trunkid'];

		$sqlstr = "
			DELETE FROM `globals` WHERE `variable` IN (
				'OUTCID_$trunkid', 'OUTFAIL_$trunkid', 'OUTKEEPCID_$trunkid',
				'OUTMAXCHANS_$trunkid', 'OUTPREFIX_$trunkid', 'OUT_$trunkid',
				'OUTDISABLE_$trunkid'
			)
		";
		sql($sqlstr);
	}
	out(_("done"));
} else {
	out(_("not needed"));
}

outn(_("Checking if privacy manager options exists.."));
$check = $db->query('SELECT pmmaxretries FROM incoming');
if(DB::IsError($check)){
	$result = $db->query('alter table incoming add pmmaxretries varchar(2), add pmminlength varchar(2);');
	if(DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo().'fatal error adding fields to incoming table');
	} else {
	  out(_("Added pmmaxretries and pmminlength"));
  }
}else{
	out(_("already exists"));
}

// This has already been done in the framework upgrades but is repeated
// here until confirmed there is no path where that code may not have been
// executed.
//
$new_cols = array('noanswer_cid','busy_cid','chanunavail_cid');
foreach ($new_cols as $col) {
  outn(sprintf(_("Checking for %s field.."),$col));
  $sql = "SELECT $col FROM `users`";
  $check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
  if(DB::IsError($check)) {
    // add new field
    $sql = "ALTER TABLE `users` ADD `$col` VARCHAR( 20 ) DEFAULT '';";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_issabelpbx($result->getDebugInfo()); }
    out(_("added"));
  } else {
    out(_("already exists"));
  }
}

$new_cols = array('noanswer_dest','busy_dest','chanunavail_dest');
foreach ($new_cols as $col) {
  outn(sprintf(_("Checking for %s field.."),$col));
  $sql = "SELECT $col FROM `users`";
  $check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
  if(DB::IsError($check)) {
    // add new field
    $sql = "ALTER TABLE `users` ADD `$col` VARCHAR( 255 ) DEFAULT '';";
    $result = $db->query($sql);
    if(DB::IsError($result)) { die_issabelpbx($result->getDebugInfo()); }
    out(_("added"));
  } else {
    out(_("already exists"));
  }
}

$sql = "SHOW KEYS FROM devices WHERE Key_name='tech'";
$check = $db->getOne($sql);
if (empty($check)) {
	$sql = "ALTER TABLE devices ADD PRIMARY KEY `id` (`id`), ADD KEY `tech` (`tech`)";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		out(_("Unable to add index to tech field in devices"));
		issabelpbx_log(IPBX_LOG_ERROR, "Failed to add index to tech field in the devices table");
	} else {
		out(_("Adding index to tech field in the devices"));
	}
}

$sql = "SHOW KEYS FROM users WHERE Key_name='extension'";
$check = $db->getOne($sql);
if (empty($check)) {
	$sql = "ALTER TABLE users ADD PRIMARY KEY `extension` (`extension`)";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		out(_("Unable to add index to extensions field in users"));
		issabelpbx_log(IPBX_LOG_ERROR, "Failed to add index to extensions field in the users table");
	} else {
		out(_("Adding index to extensions field in the users"));
	}
}

$result = $db->query("ALTER TABLE devices ADD PRIMARY KEY `id` (`id`)");
if(DB::IsError($result)) {
	out(_("No need to set primary index on devices table"));
	issabelpbx_log(IPBX_LOG_ERROR, "No need to set primary index on devices table");
} else {
	out(_("Adding primary index to id field on devices table"));
}

$result = $db->query("ALTER TABLE users ADD PRIMARY KEY `extension` (`extension`)");
if(DB::IsError($result)) {
	out(_("No need to set primary index on users table"));
	issabelpbx_log(IPBX_LOG_ERROR, "No need to set primary index on users table");
} else {
	out(_("Adding primary index to extension field on users table"));
}

// The following are from General Settings that may need to be migrated.
// We will first create them all, the define_conf_settings() method will
// not change the value if already set. We will update the settings
// to the currently configured values from the globals table afer defining
// them here and then remove them from the globals table.

$globals_convert['VMX_CONTEXT'] = 'from-internal';
$globals_convert['VMX_PRI'] = '1';
$globals_convert['VMX_TIMEDEST_CONTEXT'] = '';
$globals_convert['VMX_TIMEDEST_EXT'] = 'dovm';
$globals_convert['VMX_TIMEDEST_PRI'] = '1';
$globals_convert['VMX_LOOPDEST_CONTEXT'] = '';
$globals_convert['VMX_LOOPDEST_EXT'] = 'dovm';
$globals_convert['VMX_LOOPDEST_PRI'] = '1';
$globals_convert['MIXMON_DIR'] = '';
$globals_convert['MIXMON_POST'] = '';
$globals_convert['MIXMON_FORMAT'] = 'wav';

$globals_convert['DIAL_OPTIONS'] = 'tr';
$globals_convert['TRUNK_OPTIONS'] = 't';
$globals_convert['RINGTIMER'] = '15';
$globals_convert['TONEZONE'] = 'us';

// VMX_CONTEXT
//
$set['value'] = $globals_convert['VMX_CONTEXT'];
$set['defaultval'] =& $set['value'];
$set['options'] = '';
$set['readonly'] = 1;
$set['hidden'] = 0;
$set['level'] = 9;
$set['module'] = '';
$set['category'] = 'VmX Locater';
$set['emptyok'] = 0;
$set['name'] = 'VMX Default Context';
$set['description'] = 'Used to do extremely advanced and customized changes to the macro-vm VmX locater. Check the dialplan for a thorough understanding of how to use this.';
$set['type'] = CONF_TYPE_TEXT;
$issabelpbx_conf->define_conf_setting('VMX_CONTEXT',$set);

// VMX_PRI
//
$set['value'] = $globals_convert['VMX_PRI'];
$set['defaultval'] =& $set['value'];
$set['options'] = array(1,1000);
$set['readonly'] = 1;
$set['hidden'] = 0;
$set['level'] = 9;
$set['module'] = '';
$set['category'] = 'VmX Locater';
$set['emptyok'] = 0;
$set['name'] = 'VMX Default Priority';
$set['description'] = 'Used to do extremely advanced and customized changes to the macro-vm VmX locater. Check the dialplan for a thorough understanding of how to use this.';
$set['type'] = CONF_TYPE_INT;
$issabelpbx_conf->define_conf_setting('VMX_PRI',$set);

// VMX_TIMEDEST_CONTEXT
//
$set['value'] = $globals_convert['VMX_TIMEDEST_CONTEXT'];
$set['defaultval'] =& $set['value'];
$set['options'] = '';
$set['readonly'] = 1;
$set['hidden'] = 0;
$set['level'] = 9;
$set['module'] = '';
$set['category'] = 'VmX Locater';
$set['emptyok'] = 1;
$set['name'] = 'VMX Default Timeout Context';
$set['description'] = "Used to do extremely advanced and customized changes to the macro-vm VmX locater. Check the dialplan for a thorough understanding of how to use this. The default location that a caller will be sent if they don't press any key (timeout) or press # which is interpreted as a timeout. Set this to 'dovm' to go to voicemail (default).";
$set['type'] = CONF_TYPE_TEXT;
$issabelpbx_conf->define_conf_setting('VMX_TIMEDEST_CONTEXT',$set);

// VMX_TIMEDEST_EXT
//
$set['value'] = $globals_convert['VMX_TIMEDEST_EXT'];
$set['defaultval'] =& $set['value'];
$set['options'] = '';
$set['readonly'] = 1;
$set['hidden'] = 0;
$set['level'] = 9;
$set['module'] = '';
$set['category'] = 'VmX Locater';
$set['emptyok'] = 0;
$set['name'] = 'VMX Default Timeout Extension';
$set['description'] = "Used to do extremely advanced and customized changes to the macro-vm VmX locater. Check the dialplan for a thorough understanding of how to use this. The default location that a caller will be sent if they don't press any key (timeout) or press # which is interpreted as a timeout. Set this to 'dovm' to go to voicemail (default).";
$set['type'] = CONF_TYPE_TEXT;
$issabelpbx_conf->define_conf_setting('VMX_TIMEDEST_EXT',$set);

// VMX_TIMEDEST_PRI
//
$set['value'] = $globals_convert['VMX_TIMEDEST_PRI'];
$set['defaultval'] =& $set['value'];
$set['options'] = array(1,1000);
$set['readonly'] = 1;
$set['hidden'] = 0;
$set['level'] = 9;
$set['module'] = '';
$set['category'] = 'VmX Locater';
$set['emptyok'] = 0;
$set['name'] = 'VMX Default Timeout Priority';
$set['description'] = "Used to do extremely advanced and customized changes to the macro-vm VmX locater. Check the dialplan for a thorough understanding of how to use this. The default location that a caller will be sent if they don't press any key (timeout) or press # which is interpreted as a timeout. Set this to 'dovm' to go to voicemail (default).";
$set['type'] = CONF_TYPE_INT;
$issabelpbx_conf->define_conf_setting('VMX_TIMEDEST_PRI',$set);

// VMX_LOOPDEST_CONTEXT
//
$set['value'] = $globals_convert['VMX_LOOPDEST_CONTEXT'];
$set['defaultval'] =& $set['value'];
$set['options'] = '';
$set['readonly'] = 1;
$set['hidden'] = 0;
$set['level'] = 9;
$set['module'] = '';
$set['category'] = 'VmX Locater';
$set['emptyok'] = 1;
$set['name'] = 'VMX Default Loop Exceed Context';
$set['description'] = "Used to do extremely advanced and customized changes to the macro-vm VmX locater. Check the dialplan for a thorough understanding of how to use this. The default location that a caller will be sent if they press an invalid options too many times, as defined by the Maximum Loops count.";
$set['type'] = CONF_TYPE_TEXT;
$issabelpbx_conf->define_conf_setting('VMX_LOOPDEST_CONTEXT',$set);

// VMX_LOOPDEST_EXT
//
$set['value'] = $globals_convert['VMX_LOOPDEST_EXT'];
$set['defaultval'] =& $set['value'];
$set['options'] = '';
$set['readonly'] = 1;
$set['hidden'] = 0;
$set['level'] = 9;
$set['module'] = '';
$set['category'] = 'VmX Locater';
$set['emptyok'] = 0;
$set['name'] = 'VMX Default Loop Exceed Extension';
$set['description'] = "Used to do extremely advanced and customized changes to the macro-vm VmX locater. Check the dialplan for a thorough understanding of how to use this. The default location that a caller will be sent if they press an invalid options too many times, as defined by the Maximum Loops count.";
$set['type'] = CONF_TYPE_TEXT;
$issabelpbx_conf->define_conf_setting('VMX_LOOPDEST_EXT',$set);

// VMX_LOOPDEST_PRI
//
$set['value'] = $globals_convert['VMX_LOOPDEST_PRI'];
$set['defaultval'] =& $set['value'];
$set['options'] = array(1,1000);
$set['readonly'] = 1;
$set['hidden'] = 0;
$set['level'] = 9;
$set['module'] = '';
$set['category'] = 'VmX Locater';
$set['emptyok'] = 0;
$set['name'] = 'VMX Default Loop Exceed Priority';
$set['description'] = "Used to do extremely advanced and customized changes to the macro-vm VmX locater. Check the dialplan for a thorough understanding of how to use this. The default location that a caller will be sent if they press an invalid options too many times, as defined by the Maximum Loops count.";
$set['type'] = CONF_TYPE_INT;
$issabelpbx_conf->define_conf_setting('VMX_LOOPDEST_PRI',$set);

// MIXMON_DIR
//
$set['value'] = $globals_convert['MIXMON_DIR'];
$set['defaultval'] =& $set['value'];
$set['options'] = '';
$set['readonly'] = 1;
$set['hidden'] = 0;
$set['level'] = 9;
$set['module'] = '';
$set['category'] = 'Directory Layout';
$set['emptyok'] = 1;
$set['name'] = 'Override Call Recording Location';
$set['description'] = "Override the default location where asterisk will store call recordings. Be sure to set proper permissions on the directory for the asterisk user.";
$set['type'] = CONF_TYPE_DIR;
$issabelpbx_conf->define_conf_setting('MIXMON_DIR',$set);

// MIXMON_POST
//
$set['value'] = $globals_convert['MIXMON_POST'];
$set['defaultval'] =& $set['value'];
$set['options'] = '';
$set['readonly'] = 1;
$set['hidden'] = 0;
$set['level'] = 9;
$set['module'] = '';
$set['category'] = 'Developer and Customization';
$set['emptyok'] = 1;
$set['name'] = 'Post Call Recording Script';
$set['description'] = "An optional script to be run after the call is hangup. You can include channel and MixMon variables like \${CALLFILENAME}, \${MIXMON_FORMAT} and \${MIXMON_DIR}. To ensure that you variables are properly escaped, use the following notation: ^{MY_VAR}";
$set['type'] = CONF_TYPE_TEXT;
$issabelpbx_conf->define_conf_setting('MIXMON_POST',$set);


// MIXMON_FORMAT
$set['value'] = $globals_convert['MIXMON_FORMAT'];
$set['defaultval'] =& $set['value'];
$set['options'] = array('wav','WAV','ulaw','ulaw','alaw','sln','gsm','g729');
$set['name'] = 'Call Recording Format';
$set['description'] = "Format to save recoreded calls for most call recording unless specified differently in specific applications.";
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = '';
$set['category'] = 'System Setup';
$set['emptyok'] = 0;
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('MIXMON_FORMAT',$set);

// DIAL_OPTIONS
//
$set['value'] = $globals_convert['DIAL_OPTIONS'];
$set['defaultval'] =& $set['value'];
$set['options'] = '';
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = '';
$set['category'] = 'Dialplan and Operational';
$set['emptyok'] = 1;
$set['name'] = 'Asterisk Dial Options';
$set['description'] = "Options to be passed to the Asterisk Dial Command when making internal calls or for calls ringing internal phones. The options are documented in Asterisk documentation, a subset of which are described here. The default options T and t allow the calling and called users to transfer a call with ##. The r option allows Asterisk to generate ringing back to the calling phones which is needed by some phones and sometimes needed in complex dialplan features that may otherwise result in silence to the caller.";
$set['type'] = CONF_TYPE_TEXT;
$issabelpbx_conf->define_conf_setting('DIAL_OPTIONS',$set);

// TRUNK_OPTIONS
//
$set['value'] = $globals_convert['TRUNK_OPTIONS'];
$set['defaultval'] =& $set['value'];
$set['options'] = '';
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = '';
$set['category'] = 'Dialplan and Operational';
$set['emptyok'] = 1;
$set['name'] = 'Asterisk Outbound Trunk Dial Options';
$set['description'] = "Options to be passed to the Asterisk Dial Command when making outbound calls on your trunks when not part of an Intra-Company Route. The options are documented in Asterisk documentation, a subset of which are described here. The default options T and t allow the calling and called users to transfer a call with ##. It is HIGHLY DISCOURAGED to use the r option here as this will prevent early media from being delivered from the PSTN and can result in the inability to interact with some external IVRs";
$set['type'] = CONF_TYPE_TEXT;
$issabelpbx_conf->define_conf_setting('TRUNK_OPTIONS',$set);

// RINGTIMER
$opts = array();
for ($i=0;$i<=120;$i++) {
	$opts[]=$i;
}
$set['value'] = $globals_convert['RINGTIMER'];
$set['defaultval'] =& $set['value'];
$set['options'] = $opts;
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = '';
$set['category'] = 'Dialplan and Operational';
$set['name'] = 'Ringtime Default';
$set['description'] = 'Default number of seconds to ring phones before sending callers to voicemail or other extension destinations. This can be set per extension/user. Phones with no voicemail or other destination options will ring indefinitely.';
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('RINGTIMER',$set);
unset($opts);

// CONNECTEDLINE_PRESENCESTATE
//
$set['value'] = true;
$set['defaultval'] =& $set['value'];
$set['options'] = '';
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = '';
$set['category'] = 'Dialplan and Operational';
$set['emptyok'] = 0;
$set['name'] = 'Display Presence State of Callee';
$set['description'] = "When set to true and when CONNECTEDLINE() capabilities are configured and supported by your handset, the name displayed will include the presence state of the callee.";
$set['type'] = CONF_TYPE_BOOL;
$issabelpbx_conf->define_conf_setting('CONNECTEDLINE_PRESENCESTATE',$set);

// TONEZONE
// This function will assure the table is there and then create/setup the Advanced Setting
//
_core_create_update_tonezones($globals_convert['TONEZONE'],false);

// Get all the globals that need to be migrated, then prepare the
// update array to set the current settings in issabelpbx_conf before
// deleting them.
//
$sql = "SELECT `variable`, `value`";
$sql_where = " FROM globals WHERE `variable` IN ('".implode("','",array_keys($globals_convert))."')";
$sql .= $sql_where;
$globals = $db->getAll($sql,DB_FETCHMODE_ASSOC);
if(DB::IsError($globals)) {
  die_issabelpbx($globals->getMessage());
}
outn(_("Checking for General Setting migrations.."));
if (count($globals)) {
  out(_("preparing"));
  foreach ($globals as $global) {
    $update_arr[trim($global['variable'])] = $global['value'];
    out(sprintf(_("%s prepared"),$global['variable']));
  }
  // Now set the values differently from the defaults, and commit
  $issabelpbx_conf->set_conf_values($update_arr,true);
} else {
  out(_("not needed"));
  // commit the previous defines if we didn't upate anything
  $issabelpbx_conf->commit_conf_settings();
}

// Add any globals that need to be deleted here while we are
// othewise cleaning up the ones migrated. These would be ones
// no longer used. They will be deleted if other migrations
// occured above.
//
$globals_convert['RECORDING_STATE'] = true;
$globals_convert['DIAL_OUT'] = true;
$globals_convert['REGTIME'] = true;
$globals_convert['REGDAYS'] = true;
$globals_convert['DIALOUTIDS'] = true;
$globals_convert['IN_OVERRIDE'] = true;
$globals_convert['AFTER_INCOMING'] = true;
$globals_convert['DIRECTORY_OPTS'] = true;
$globals_convert['OPERATOR'] = true;
$globals_convert['TRANSFER_CONTEXT'] = true;
$globals_convert['NULL'] = true;
$globals_convert['PARKNOTIFY'] = true;
$globals_convert['CALLFILENAME'] = true;
$globals_convert['FAX'] = true;
$globals_convert['INCOMING'] = true;
$globals_convert['DIRECTORY'] = true;
$globals_convert['RECORDEXTEN'] = true;

// Re-compute the where clause to pull in the new ones added and then Delete The Globals
//
$sql_where = " FROM globals WHERE `variable` IN ('".implode("','",array_keys($globals_convert))."')";

if (count($globals)) {
	out(_("General Settings migrated"));
}
outn(_("Deleting unused globals.."));
$sql = "DELETE".$sql_where;
$globals = $db->query($sql);
if(DB::IsError($globals)) {
	out(_("Fatal DB error trying to delete globals, trying to carry on"));
} else {
	out(_("done"));
}

// It's possible that SQL, LOG_SQL values could still bein in AMPSYSLOGLEVEL if amportal.conf
// remained writable. Once changed, this will set it properly next time core is upgraded since
// Framework upgrade scripts only run based on current version.
//
$log_level = strtoupper($amp_conf['AMPSYSLOGLEVEL']);
if ($log_level == 'SQL' || $log_level == 'LOG_SQL') {
  outn(sprintf(_("Discontinued logging type %s changing to %s.."),$log_level,'FILE'));
  $issabelpbx_conf->set_conf_values(array('AMPSYSLOGLEVEL' => 'FILE'));
  out(_("ok"));
}
// AMPSYSLOGLEVEL
unset($set);
$set['value'] = 'FILE';
$set['options'] = 'FILE, LOG_EMERG, LOG_ALERT, LOG_CRIT, LOG_ERR, LOG_WARNING, LOG_NOTICE, LOG_INFO, LOG_DEBUG';
$issabelpbx_conf->define_conf_setting('AMPSYSLOGLEVEL',$set,true);

// Convert IAX notransfer to transfer (since 1.4)
//
outn(_("Converting IAX notransfer to transfer if needed.."));
$affected_rows = 0;
sql("UPDATE iax SET keyword = 'transfer', data = 'yes' WHERE keyword = 'notransfer' AND LOWER(data) = 'no'");
$affected_rows .= $db->affectedRows();
sql("UPDATE iax SET keyword = 'transfer', data = 'no' WHERE keyword = 'notransfer' AND LOWER(data) = 'yes'");
$affected_rows .= $db->affectedRows();
sql("UPDATE iax SET keyword = 'transfer' WHERE keyword = 'notransfer' AND LOWER(data) = 'mediaonly'");
$affected_rows .= $db->affectedRows();
$affected_rows ? out(sprintf(_("updated %s records"),$affected_rows)) : out(_("not needed"));


$tables = array('sip', 'iax', 'zap', 'dahdi');
outn(_("deleting obsoleted record_in and record_out entries.."));
foreach ($tables as $table) {
  $sql = "DELETE FROM `$table` WHERE `keyword` in ('record_in', 'record_out')";
  $db->query($sql);
}
out(_("ok"));

// Added 2.11
//
outn(_("checking for dest field in outbound_routes.."));
$sql = "SELECT `dest` FROM `outbound_routes`";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	$sql = "ALTER TABLE `outbound_routes` ADD `dest` VARCHAR(255) DEFAULT NULL";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		out(_("fatal error trying to add field"));
		die_issabelpbx($result->getDebugInfo());
	} else {
		out(_("added"));
	}
} else {
	out(_("already exists"));
}

outn(_("checking for continue field in trunks.."));
$sql = "SELECT `continue` FROM `trunks`";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
	$sql = "ALTER TABLE `trunks` ADD `continue` VARCHAR( 4 ) DEFAULT 'off'";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		out(_("fatal error trying to add field"));
		die_issabelpbx($result->getDebugInfo());
	} else {
		out(_("added"));
	}
} else {
	out(_("already exists"));
}

// Migrate ALLOW_SIP_ANON from globals if needed
//
$sql = "SELECT `value` FROM globals WHERE `variable` = 'ALLOW_SIP_ANON'";
$globals = $db->getAll($sql,DB_FETCHMODE_ASSOC);
if(!DB::IsError($globals)) {
	if (count($globals)) {
		$allow_sip_anon = trim($globals[0]['value']);
		$sql = "DELETE FROM globals WHERE `variable` = 'ALLOW_SIP_ANON'";
		out(_("migrated ALLOW_SIP_ANON Value: $allow_sip_anon to admin table"));
		outn(_("deleting ALLOW_SIP_ANON from globals.."));
		$res = $db->query($sql);
		if(!DB::IsError($globals)) {
			out(_("done"));
		} else {
			out(_("could not delete"));
		}
	}
}
if (!empty($allow_sip_anon)) {
	$result = $db->query("INSERT INTO `admin` (`variable`, `value`) VALUES ('ALLOW_SIP_ANON', '$allow_sip_anon')");
	if(DB::IsError($result)) {
		out(_("ERROR: could not insert previous value for ALLOW_SIP_ANON, it may already exist"));
	} else {
		out(_("Inserted ALLOW_SIP_ANON fine"));
	}
}

// zapchandids to dahdichandids table rename
$dahditbl_res = $db->getAll("SELECT * FROM dahdichandids");
if (DB::IsError($dahditbl_res)) {
	$sql = (preg_match("/qlite/",$amp_conf["AMPDBENGINE"])) ?
		'ALTER TABLE zapchandids RENAME TO dahdichandids' :
		'RENAME TABLE zapchandids to dahdichandids';
	outn(_("renaming table zapchandids to dahdichandids.."));
	$result = $db->query($sql);
	if (!DB::IsError($result)) {
		out(_("ok"));
	} else {
		out(_("CRITICAL ERROR"));
		out(_("Could not rename table, if no dahdichandids table present FATAL errors will occur"));
	}
}

// migrate from zap table. If empty, remove table. If not empty AND dahdi table empty, then
// migrate data to dahdi table, otherwise just leave it be.
//
$zaptbl_size = $db->getOne("SELECT COUNT(*) FROM zap");
if (!DB::IsError($zaptbl_size)) {
	if ($zaptbl_size == 0) {
		outn(_("removing zap table.."));
		$res = $db->query("DROP TABLE zap");
		if (!DB::IsError($res)) {
			out(_("ok"));
		} else {
			out(_("error dropping table"));
		}
	} else {
		$dahditbl_size = $db->getOne("SELECT COUNT(*) FROM dahdi");
		if (DB::IsError($dahditbl_size)) {
			out(_("error checking dahdi table size to determine if zap table contents can be migrated"));
		} else {
			if ($dahditbl_size > 0) {
				out(_("dahdi table not empty, can't migrate zap data there"));
			} else {
				outn(_("migrating zap table contents to dahdi table.."));
				$res = $db->query("INSERT INTO dahdi (id, keyword, data, flags) (SELECT id, keyword, data, flags FROM zap)");
				if (!DB::IsError($res)) {
					out(_("ok"));
					outn(_("removing zap table.."));
					$res = $db->query("DROP TABLE zap");
					if (!DB::IsError($res)) {
						out(_("ok"));
					} else {
						out(_("error dropping table"));
					}
					// Now migrate devices table and update AstDB DEVICES
					//
					$zap_devices = $db->getAll("SELECT id, dial FROM devices WHERE lower(tech) = 'zap'", DB_FETCHMODE_ASSOC);
					if (DB::IsError($zap_devices)) {
						out(_("Error converting zap to dahdi in devices table and AstDB"));
					} else if (count($zap_devices) > 0) {
						$dahdi_update = array();
						foreach ($zap_devices as $dev) {
							$chan = explode($dev['dial'],2);
							$dial = 'DAHDI/' . $chan[1];
							out(sprintf(_("preparing device %s dial to %s"), $dev['id'], $dial));
							$dahdi_update[] = array($dial, $dev['id']);
							$astman->database_put("DEVICE", $dev['id'] . "/dial", $dial);
						}
						$compiled = $db->prepare("UPDATE devices SET tech = 'dahdi', dial = ? WHERE id = ?");
						$result = $db->executeMultiple($compiled, $dahdi_update);
						if (!DB::IsError($result)) {
							out(_("zap devices migrated"));
						} else {
							out(_("error occured updating devices table"));
						}
					}
				} else {
					out(_("error migrating table"));
				}
			}
		}
	}
}
// migrate any zap trunks to dahdi
outn(_("upgrading any zap trunks to dahdi if found"));
$res = $db->query("UPDATE trunks set tech = 'dahdi' WHERE lower(tech) = 'zap'");
if (!DB::IsError($res)) {
	out(_("ok"));
} else {
	out(_("error occured"));
}

//migrate the username field in ampusers
$res = $db->getAll('SHOW COLUMNS FROM ampusers WHERE FIELD = "username"', DB_FETCHMODE_ASSOC);
if ($res[0]['Type'] == 'varchar(20)') {
        sql('ALTER TABLE ampusers CHANGE username username varchar(255) NOT NULL');
		outn(_("migrated username column to allow for longer usernames"));
}

function _core_create_update_tonezones($tz = 'us', $commit = true) {
	global $db, $issabelpbx_conf;

	$sql = " CREATE TABLE IF NOT EXISTS `indications_zonelist` (
  	`name` VARCHAR (80) NOT NULL,
  	`iso` VARCHAR (20) NOT NULL,
  	`conf` BLOB,
  	PRIMARY KEY (`iso`)
	);";
	sql($sql);

	// If we still have a function that supplies the initial zones, spit them out and update the DB
	//
	$zonelist = _initialize_zonelist();
	$compiled = $db->prepare('REPLACE INTO `indications_zonelist` (`name`, `iso`, `conf`) values (?,?,?)');
	$result = $db->executeMultiple($compiled,$zonelist);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo()."<br><br>".'error initializing indications_zonelist');
	}

	// Now get what ever we have and update it in the IssabelPBX Settings choices in case the DB has been modified
	//
	$sql = 'SELECT `name`, `iso` FROM `indications_zonelist` ORDER BY `name`';
	$zl = sql($sql, 'getAll', DB_FETCHMODE_ASSOC);
	$zlist = array();
	foreach ($zl as $z) {
		$zlist[$z['iso']] = $z['name'];
	}

	// Now define the setting for the first time if needed, if already defined the $tz won't matter but this
	// will update the options list if it has changed
	//

	// TONEZONE
	//
	$set['value'] = $tz;
	$set['defaultval'] = 'us';
	$set['options'] = $zlist;
	$set['readonly'] = 0;
	$set['hidden'] = 0;
	$set['level'] = 0;
	$set['module'] = '';
	$set['category'] = 'Dialplan and Operational';
	$set['name'] = 'Country Indication Tones';
	$set['description'] = 'Choose the country tonezone that you would like Asterisk to use when creating the different standard telephony tones such as ringing, busy, congetstion, etc.';
	$set['type'] = CONF_TYPE_FSELECT;
	$issabelpbx_conf->define_conf_setting('TONEZONE',$set,$commit);
	unset($zlist);
}

function _initialize_zonelist() {
        return array(
 array ( "name" => "Angola",  "iso" => "ao", "conf" => "ringcadence = 1000,5000\nbusy = 425/500,0/500\ncongestion = 500/500,0500\ndial = 425\nringing = 25/1000,0/5000\ncallwaiting = 400/1000,0/5000\n"),
 array ( "name" => "Argentina",  "iso" => "ar", "conf" => "ringcadence = 1000,4500\ndial = 425\nbusy = 425/300,0/300\nring = 425/1000,0/4500\ncongestion = 425/200,0/300\ncallwaiting = 425/200,0/9000\ndialrecall = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425/330,0/330,425/660,0/660\nrecord = 1400/500,0/14000\ninfo = 425/100,0/100\nstutter = 425/450,0/50\n"),
 array ( "name" => "Australia",  "iso" => "au", "conf" => "ringcadence = 400,200,400,2000\ndial = 413+438\nbusy = 425/375,0/375\nring = 413+438/400,0/200,413+438/400,0/2000\ncongestion = 425/375,0/375,420/375,0/375\ncallwaiting = 425/200,0/200,425/200,0/4400\ndialrecall = 413+438\nrecord = !425/1000,!0/15000,425/360,0/15000\ninfo = 425/2500,0/500\nstd = !525/100,!0/100,!525/100,!0/100,!525/100,!0/100,!525/100,!0/100,!525/100\nfacility = 425\nstutter = 413+438/100,0/40\nringmobile = 400+450/400,0/200,400+450/400,0/2000\n"),
 array ( "name" => "Austria",  "iso" => "at", "conf" => "ringcadence = 1000,5000\ndial = 420\nbusy = 420/400,0/400\nring = 420/1000,0/5000\ncongestion = 420/200,0/200\ncallwaiting = 420/40,0/1960\ndialrecall = 420\nrecord = 1400/80,0/14920\ninfo = 950/330,1450/330,1850/330,0/1000\nstutter = 380+420\n"),
 array ( "name" => "Belgium",  "iso" => "be", "conf" => "ringcadence = 1000,3000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/3000\ncongestion = 425/167,0/167\ncallwaiting = 1400/175,0/175,1400/175,0/3500\ndialrecall = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\nrecord = 1400/500,0/15000\ninfo = 900/330,1400/330,1800/330,0/1000\nstutter = 425/1000,0/250\n"),
 array ( "name" => "Brazil",  "iso" => "br", "conf" => "ringcadence = 1000,4000\ndial = 425\nbusy = 425/250,0/250\nring = 425/1000,0/4000\ncongestion = 425/250,0/250,425/750,0/250\ncallwaiting = 425/50,0/1000\ndialrecall = 350+440\nrecord = 425/250,0/250\ninfo = 950/330,1400/330,1800/330\nstutter = 350+440\n"),
 array ( "name" => "Bulgaria",  "iso" => "bg", "conf" => "ringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/250,0/250\ncallwaiting = 425/150,0/150,425/150,0/4000\ndialrecall = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\nrecord = 1400/425,0/15000\ninfo = 950/330,1400/330,1800/330,0/1000\nstutter = 425/1500,0/100\n"),
 array ( "name" => "Chile",  "iso" => "cl", "conf" => "ringcadence = 1000,3000\ndial = 400\nbusy = 400/500,0/500\nring = 400/1000,0/3000\ncongestion = 400/200,0/200\ncallwaiting = 400/250,0/8750\ndialrecall = !400/100,!0/100,!400/100,!0/100,!400/100,!0/100,400\nrecord = 1400/500,0/15000\ninfo = 950/333,1400/333,1800/333,0/1000\nstutter = !400/100,!0/100,!400/100,!0/100,!400/100,!0/100,!400/100,!0/100,!400/100,!0/100,!400/100,!0/100,400\n"),
 array ( "name" => "China",  "iso" => "cn", "conf" => "ringcadence = 1000,4000\ndial = 450\nbusy = 450/350,0/350\nring = 450/1000,0/4000\ncongestion = 450/700,0/700\ncallwaiting = 450/400,0/4000\ndialrecall = 450\nrecord = 950/400,0/10000\ninfo = 450/100,0/100,450/100,0/100,450/100,0/100,450/400,0/400\nstutter = 450+425\n"),
 array ( "name" => "Colombia (Republic of)", "iso" => "co", "conf" => "ringcadance = 1000,4000\ndial = 425\nbusy = 425/250,0/250\nring = 425/1000,0/4500\ncongestion = 425/100,0/250,425/350,0/250,425/650,0/250\ncallwaiting = 400+450/300,0/6000\ndialrecall = 425\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0/1000\n"),
 array ( "name" => "Costa Rica",  "iso" => "cr", "conf" => "ringcadence = 1203,4797\ndial = 450\nbusy = 450/330,0/330\nring = 450/1200,0/4900\ncongestion = 450/330,0/330\ncallwaiting = 450/150,0/150,450/150,0/8000\dialrecall = !450/100,!0/100,!450/100,!0/100,!450/100,!0/100,450\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter = !450/100,!0/100,!450/100,!0/100,!450/100,!0/100,!450/100,!0/100,!42\n"),
 array ( "name" => "Czech Republic",  "iso" => "cz", "conf" => "ringcadence = 1000,4000\ndial = 425/330,0/330,425/660,0/660\nbusy = 425/330,0/330\nring = 425/1000,0/4000\ncongestion = 425/165,0/165\ncallwaiting = 425/330,0/9000\ndialrecall = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425/330,0/330,425/660,0/660\nrecord = 1400/500,0/14000\ninfo = 950/330,0/30,1400/330,0/30,1800/330,0/1000\nstutter = 425/450,0/50\n"),
 array ( "name" => "Denmark",  "iso" => "dk", "conf" => "ringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/200,0/200\ncallwaiting = !425/200,!0/600,!425/200,!0/3000,!425/200,!0/200,!425/200,0\ndialrecall = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\nrecord = 1400/80,0/15000\ninfo = 950/330,1400/330,1800/330,0/1000\nstutter = 425/450,0/50\n"),
 array ( "name" => "Estonia",  "iso" => "ee", "conf" => "ringcadence = 1000,4000\ndial = 425\nbusy = 425/300,0/300\nring = 425/1000,0/4000\ncongestion = 425/200,0/200\ncallwaiting = 950/650,0/325,950/325,0/30,1400/1300,0/2600\ndialrecall = 425/650,0/25\nrecord = 1400/500,0/15000\ninfo = 950/650,0/325,950/325,0/30,1400/1300,0/2600\nstutter = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\n"),
 array ( "name" => "Finland",  "iso" => "fi", "conf" => "ringcadence = 1000,4000\ndial = 425\nbusy = 425/300,0/300\nring = 425/1000,0/4000\ncongestion = 425/200,0/200\ncallwaiting = 425/150,0/150,425/150,0/8000\ndialrecall = 425/650,0/25\nrecord = 1400/500,0/15000\ninfo = 950/650,0/325,950/325,0/30,1400/1300,0/2600\nstutter = 425/650,0/25\n"),
 array ( "name" => "France",  "iso" => "fr", "conf" => "ringcadence = 1500,3500\ndial = 440\nbusy = 440/500,0/500\nring = 440/1500,0/3500\ncongestion = 440/250,0/250\ncallwait = 440/300,0/10000\ndialrecall = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330\nstutter = !440/100,!0/100,!440/100,!0/100,!440/100,!0/100,!440/100,!0/100,!440/100,!0/100,!440/100,!0/100,440\n"),
 array ( "name" => "Germany",  "iso" => "de", "conf" => "ringcadence = 1000,4000\ndial = 425\nbusy = 425/480,0/480\nring = 425/1000,0/4000\ncongestion = 425/240,0/240\ncallwaiting = !425/200,!0/200,!425/200,!0/5000,!425/200,!0/200,!425/200,!0/5000,!425/200,!0/200,!425/200,!0/5000,!425/200,!0/200,!425/200,!0/5000,!425/200,!0/200,!425/200,0\ndialrecall = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\nrecord = 1400/80,0/15000\ninfo = 950/330,1400/330,1800/330,0/1000\nstutter = 425+400\n"),
 array ( "name" => "Greece",  "iso" => "gr", "conf" => "ringcadence = 1000,4000\ndial = 425/200,0/300,425/700,0/800\nbusy = 425/300,0/300\nring = 425/1000,0/4000\ncongestion = 425/200,0/200\ncallwaiting = 425/150,0/150,425/150,0/8000\ndialrecall = 425/650,0/25\nrecord = 1400/400,0/15000\ninfo = !950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,0\nstutter = 425/650,0/25\n"),
 array ( "name" => "Hong Kong", "iso" => "hk", "conf" => "ringcadence = 400,200,400,3000\ndial = 350+440\nbusy = 480+620/500,0/500\nring = 440+480/400,0/200,440+480/400,0/3000\ncongestion = 480+620/250,0/250\ncallwaiting = 440/300,0/10000\ndialrecall = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\n"),
 array ( "name" => "Hungary",  "iso" => "hu", "conf" => "ringcadence = 1250,3750\ndial = 425\nbusy = 425/300,0/300\nring = 425/1250,0/3750\ncongestion = 425/300,0/300\ncallwaiting = 425/40,0/1960\ndialrecall = 425+450\nrecord = 1400/400,0/15000\ninfo = !950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,0\nstutter = 350+375+400\n"),
 array ( "name" => "India",  "iso" => "in", "conf" => "ringcadence = 400,200,400,2000\ndial = 400*25\nbusy = 400/750,0/750\nring = 400*25/400,0/200,400*25/400,0/2000\ncongestion = 400/250,0/250\ncallwaiting = 400/200,0/100,400/200,0/7500\ndialrecall = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0/1000\nstutter = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\n"),
 array ( "name" => "Iran",  "iso" => "ir", "conf" => "ringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/240,0/240\ncallwaiting = 425/200,0/200,425/200,0/10000\ndialrecall = 425 record = 1400/80,0/15000\ninfo = 950/330,1400/330,1800/330,0/1000\nstutter = 400+425\n"),
 array ( "name" => "Israel",  "iso" => "il", "conf" => "ringcadence = 1000,3000\ndial = 414\nbusy = 414/500,0/500\nring = 414/1000,0/3000\ncongestion = 414/250,0/250\ncallwaiting = 414/100,0/100,414/100,0/100,414/600,0/3000 \ndialrecall = !414/100,!0/100,!414/100,!0/100,!414/100,!0/100,414\nrecord = 1400/500,0/15000\ninfo = 1000/330,1400/330,1800/330,0/1000\nstutter = !414/160,!0/160,!414/160,!0/160,!414/160,!0/160,!414/160,!0/160,!414/160,!0/160,!414/160,!0/160,!414/160,!0/160,!414/160,!0/160,!414/160,!0/160,!414/160,!0/160,414 \n"),
 array ( "name" => "Italy",  "iso" => "it", "conf" => "ringcadence = 1000,4000\ndial = 425/200,0/200,425/600,0/1000\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/200,0/200\ncallwaiting = 425/400,0/100,425/250,0/100,425/150,0/14000\ndialrecall = 470/400,425/400\nrecord = 1400/400,0/15000\ninfo = !950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,0\nstutter = 470/400,425/400\n"),
 array ( "name" => "Japan",  "iso" => "jp", "conf" => "ringcadence = 1000,2000\ndial = 400\nbusy = 400/500,0/500\nring = 400+415/1000,0/2000\ncongestion = 400/500,0/500\ncallwaiting = 400+16/500,0/8000\ndialrecall = !400/200,!0/200,!400/200,!0/200,!400/200,!0/200,400\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter =!400/100,!0/100,!400/100,!0/100,!400/100,!0/100,!400/100,!0/100,!400/100,!0/100,!400/100,!0/100,400\n"),
 array ( "name" => "Kenya (Republic of)",  "iso" => "ke", "conf" => "ringcadence = 670,3000,1500,5000\nbusy = 425/200,0/600,425/200,0/600\ncongestion = 425/200,0/600\ndial = 425\nringing = 425/670,0/3000,425/1500,0/5000\ninfo = 900/750,1400/750,1800/750,0/1250\ncallwaiting = 425\n"),
 array ( "name" => "Lithuania",  "iso" => "lt", "conf" => "ringcadence = 1000,4000\ndial = 425\nbusy = 425/350,0/350\nring = 425/1000,0/4000\ncongestion = 425/200,0/200\ncallwaiting = 425/150,0/150,425/150,0/4000\ndialrecall = 425/500,0/50\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,0\nstutter = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\n"),
 array ( "name" => "Macao",  "iso" => "mo", "conf" => "ringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/250,0/250\ncallwaiting = 425/200,0/600\nrecord = 1400/400,0/15000\ninfo = 950/333,1400/333,1800/333,0/1000\nstutter = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\n"),
 array ( "name" => "Malaysia",  "iso" => "my", "conf" => "ringcadence = 400,200,400,2000\ndial = 425\nbusy = 425/500,0/500\nring = 425/400,0/200,425/400,0/2000\ncongestion = 425/500,0/500\ncallwaiting = 425/100,0/4000\ndialrecall = 350+440\nrecord = 1400/500,0/60000\ninfo = 950/330,0/15,1400/330,0/15,1800/330,0/1000\nstutter = 450+425\n"),
 array ( "name" => "Mexico",  "iso" => "mx", "conf" => "ringcadence = 2000,4000\ndial = 425\nbusy = 425/250,0/250\nring = 425/1000,0/4000\ncongestion = 425/250,0/250\ncallwaiting = 425/200,0/600,425/200,0/10000\ndialrecall = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\nrecord = 1400/500,0/15000\ninfo = 950/330,0/30,1400/330,0/30,1800/330,0/1000\nstutter = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\n"),
 array ( "name" => "Netherlands",  "iso" => "nl", "conf" => "ringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/250,0/250\ncallwaiting = 425/500,0/9500\ndialrecall = 425/500,0/50\nrecord = 1400/500,0/15000\ninfo = 950/330,1400/330,1800/330,0/1000\nstutter = 425/500,0/50\n"),
 array ( "name" => "New Zealand",  "iso" => "nz", "conf" => "ringcadence = 400,200,400,2000\ndial = 400\nbusy = 400/250,0/250\nring = 400+450/400,0/200,400+450/400,0/2000\ncongestion = 400/375,0/375\ncallwaiting = !400/200,!0/3000,!400/200,!0/3000,!400/200,!0/3000,!400/200\ndialrecall = !400/100!0/100,!400/100,!0/100,!400/100,!0/100,400\nrecord = 1400/425,0/15000\ninfo = 400/750,0/100,400/750,0/100,400/750,0/100,400/750,0/400\nstutter = !400/100!0/100,!400/100,!0/100,!400/100,!0/100,!400/100!0/100,!400/100,!0/100,!400/100,!0/100,400\n"),
 array ( "name" => "Norway",  "iso" => "no", "conf" => "ringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/200,0/200\ncallwaiting = 425/200,0/600,425/200,0/10000\ndialrecall = 470/400,425/400\nrecord = 1400/400,0/15000\ninfo = !950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,0\nstutter = 470/400,425/400\n"),
 array ( "name" => "Pakistan",  "iso" => "pk", "conf" => "ringcadence = 400,1000,0,2000\nbusy = 400/500,0/500\nring = 400/1000,0/2000\ncongestion = 400/250,0/250\n"),
 array ( "name" => "Panama",  "iso" => "pa", "conf" => "ringcadence = 2000,4000\ndial = 425\nbusy = 425/320,0/320\nring = 425/1200,0/4650\ncongestion = 425/320,0/320\ncallwaiting = 425/180,0/180,425/180\dialrecall = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!42\n"),
 array ( "name" => "Philippines",  "iso" => "phl", "conf" => "ringcadence = 1000,4000\ndial = 425\nbusy = 480+620/500,0/500\nring = 425+480/1000,0/4000\ncongestion = 480+620/250,0/250\ncallwaiting = 440/300,0/10000\ndialrecall = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\n"),
 array ( "name" => "Poland",  "iso" => "pl", "conf" => "ringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/500,0/500\ncallwaiting = 425/150,0/150,425/150,0/4000\ndialrecall = 425/500,0/50\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000\nstutter = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\n"),
 array ( "name" => "Portugal",  "iso" => "pt", "conf" => "ringcadence = 1000,5000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/5000\ncongestion = 425/200,0/200\ncallwaiting = 440/300,0/10000\ndialrecall = 425/1000,0/200\nrecord = 1400/500,0/15000\ninfo = 950/330,1400/330,1800/330,0/1000\nstutter = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\n"),
 array ( "name" => "Romania", "iso" => "ro", "conf" => "ringcadence = 1850,4150\ndial = 450\nbusy = 450/167,0/167\nring = 450*25/1850,0/4150\ncongestion = 450/500,0/500\ncallwaiting = 450/150,0/150,450/150,0/8000\ndialrecall = !450/100,!0/100,!450/100,!0/100,!450/100,!0/100,450\nrecord = 1400/400,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter = !450/100,!0/100,!450/100,!0/100,!450/100,!0/100,!450/100,!0/100,!450/100,!0/100,!450/100,!0/100,450\nfacility = 450\nhowler = 3000\ndialout = 600\nintrusion = 450/150,0/4950\nspecialdial = 450*25/400,0/40\nunobtainable = !450/92,!0/110,!450/92,!0/110,!450/92,!0/110,450/362,0/110\n"),
 array ( "name" => "Russian Federation",  "iso" => "ru", "conf" => "ringcadence = 1000,4000\ndial = 425\nbusy = 425/350,0/350\nring = 425/800,0/3200\ncongestion = 425/350,0/350\ncallwaiting = 425/200,0/5000\ndialrecall = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\n"),
 array ( "name" => "Singapore",  "iso" => "sg", "conf" => "ringcadence = 400,200,400,2000\ndial = 425\nring = 425*24/400,0/200,425*24/400,0/2000 ; modulation should be 100%, not 90%\nbusy = 425/750,0/750\ncongestion = 425/250,0/250\ncallwaiting = 425*24/300,0/200,425*24/300,0/3200\nstutter = !425/200,!0/200,!425/600,!0/200,!425/200,!0/200,!425/600,!0/200,!425/200,!0/200,!425/600,!0/200,!425/200,!0/200,!425/600,!0/200,425\ninfo = 950/330,1400/330,1800/330,0/1000 ; not currently in use acc. to reference\ndialrecall = 425*24/500,0/500,425/500,0/2500 ; unspecified in IDA reference, use repeating Holding Tone A,B\nrecord = 1400/500,0/15000 ; unspecified in IDA reference, use 0.5s tone every 15s\nnutone = 425/2500,0/500\nintrusion = 425/250,0/2000\nwarning = 425/624,0/4376 ; end of period tone, warning\nacceptance = 425/125,0/125\nholdinga = !425*24/500,!0/500 ; followed by holdingb\nholdingb = !425/500,!0/2500\n"),
 array ( "name" => "South Africa",  "iso" => "za", "conf" => "ringcadence = 400,200,400,2000\ndial = 400*33\nbusy = 400/500,0/500\nring = 400*33/400,0/200,400*33/400,0/2000\ncongestion = 400/250,0/250\ncallwaiting = 400*33/250,0/250,400*33/250,0/250,400*33/250,0/250,400*33/250,0/250\ndialrecall = 350+440\nrecord = 1400/500,0/10000\ninfo = 950/330,1400/330,1800/330,0/330\nstutter =!400*33/100,!0/100,!400*33/100,!0/100,!400*33/100,!0/100,!400*33/100,!0/100,!400*33/100,!0/100,!400*33/100,!0/100,400*33 \n"),
 array ( "name" => "Spain",  "iso" => "es", "conf" => "ringcadence = 1500,3000\ndial = 425\nbusy = 425/200,0/200\nring = 425/1500,0/3000\ncongestion = 425/200,0/200,425/200,0/200,425/200,0/600\ncallwaiting = 425/175,0/175,425/175,0/3500\ndialrecall = !425/200,!0/200,!425/200,!0/200,!425/200,!0/200,425\nrecord = 1400/500,0/15000\ninfo = 950/330,0/1000\ndialout = 500\n"),
 array ( "name" => "Sweden",  "iso" => "se", "conf" => "ringcadence = 1000,5000\ndial = 425\nbusy = 425/250,0/250\nring = 425/1000,0/5000\ncongestion = 425/250,0/750\ncallwaiting = 425/200,0/500,425/200,0/9100\ndialrecall = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\nrecord = 1400/500,0/15000\ninfo = !950/332,!0/24,!1400/332,!0/24,!1800/332,!0/2024,!950/332,!0/24,!1400/332,!0/24,!1800/332,!0/2024,!950/332,!0/24,!1400/332,!0/24,!1800/332,!0/2024,!950/332,!0/24,!1400/332,!0/24,!1800/332,!0/2024,!950/332,!0/24,!1400/332,!0/24,!1800/332,0\nstutter = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\n"),
 array ( "name" => "Switzerland",  "iso" => "ch", "conf" => "ringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/200,0/200\ncallwaiting = 425/200,0/200,425/200,0/4000\ndialrecall = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\nrecord = 1400/80,0/15000\ninfo = 950/330,1400/330,1800/330,0/1000\nstutter = 425+340/1100,0/1100\n"),
 array ( "name" => "Taiwan",  "iso" => "tw", "conf" => "ringcadence = 1000,4000\ndial = 350+440\nbusy = 480+620/500,0/500\nring = 440+480/1000,0/2000\ncongestion = 480+620/250,0/250\ncallwaiting = 350+440/250,0/250,350+440/250,0/3250\ndialrecall = 300/1500,0/500\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\n"),
 array ( "name" => "Tanzania (United Republic of)",  "iso" => "tz", "conf" => "ringcadence = 1000,4000\nbusy = 425/1000,0/1000\ncongestion = 425/375,0/375\ndial = 425+400\nringing = 425/1000,0/4000\ninfo = 950/375,1400/375,1800/375,0/30,950/375,1400/375,1800/375,0/30,950/375,1400/375,1800/375callwaiting = 425/500,0/200\n"),
 array ( "name" => "Thailand", "iso" => "th", "conf" => "ringcadence = 1000,4000\dial = 400*50\nbusy = 400/500,0/500\nring = 420/1000,0/5000\ncongestion = 400/300,0/300\ncallwaiting = 1000/400,10000/400,1000/400\ndialrecall = 400*50/400,0/100,400*50/400,0/100\nrecord = 1400/500,0/15000\ninfo = 950/330,1400/330,1800/330\nstutter = !400/200,!0/200,!400/600,!0/200,!400/200,!0/200,!400/600,!0/200,!400/200,!0/200,!400/600,!0/200,!400/200,!0/200,!400/600,!0/200,400\n"),
array ( "name" => "Turkey", "iso" => "tr", "conf" => "ringcadence = 2000,4000\ndial = 450\nbusy = 450/500,0/500\nring = 450/2000,0/4000\ncongestion = 450/200,0/200,450/200,0/200,450/200,0/200,450/600,0/200\ndisconnect = 450/200,0/200,450/200,0/200,450/200,0/200,450/600,0/200\ncallwaiting = 450/200,0/600,450/200,0/8000\ndialrecall = 450/1000,0/250\nrecord = 1400/500,0/15000\ninfo = !950/300,!1400/300,!1800/300,!0/1000,!950/300,!1400/300,!1800/300,!0/1000,!950/300,!1400/300,!1800/300,!0/1000,0\nstutter = 450/1000,0/250\n"),
 array ( "name" => "Uganda (Republic of)",  "iso" => "ug", "conf" => "ringcadence = 1000,4000\nbusy = 425/500,0/500\ncongestion = 425/250,0/250\ndial = 425\nringing = 425/1000,0/4000\ncallwaiting = 425/150,0/150,425/150,0/8000\n"),
 array ( "name" => "United Kingdom",  "iso" => "uk", "conf" => "ringcadence = 400,200,400,2000\ndial = 350+440\nspecialdial = 350+440/750,440/750\nbusy = 400/375,0/375\ncongestion = 400/400,0/350,400/225,0/525\nspecialcongestion = 400/200,1004/300\nunobtainable = 400\nring = 400+450/400,0/200,400+450/400,0/2000\ncallwaiting = 400/100,0/4000\nspecialcallwaiting = 400/250,0/250,400/250,0/250,400/250,0/5000\ncreditexpired = 400/125,0/125\nconfirm = 1400\nswitching = 400/200,0/400,400/2000,0/400\ninfo = 950/330,0/15,1400/330,0/15,1800/330,0/1000\nrecord = 1400/500,0/60000\nstutter = 350+440/750,440/750\n"),
 array ( "name" => "United States / North America",  "iso" => "us", "conf" => "ringcadence = 2000,4000\ndial = 350+440\nbusy = 480+620/500,0/500\nring = 440+480/2000,0/4000\ncongestion = 480+620/250,0/250\ncallwaiting = 440/300,0/10000\ndialrecall = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\n"),
 array ( "name" => "United States Circa 1950/ North America",  "iso" => "us-old", "conf" => "ringcadence = 2000,4000\ndial = 600*120\nbusy = 500*100/500,0/500\nring = 420*40/2000,0/4000\ncongestion = 500*100/250,0/250\ncallwaiting = 440/300,0/10000\ndialrecall = !600*120/100,!0/100,!600*120/100,!0/100,!600*120/100,!0/100,600*120\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter = !600*120/100,!0/100,!600*120/100,!0/100,!600*120/100,!0/100,!600*120/100,!0/100,!600*120/100,!0/100,!600*120/100,!0/100,600*120\n"),
 array ( "name" => "Venezuela / South America",  "iso" => "ve", "conf" => "; Tone definition source for ve found on\n; Reference: http://www.itu.int/ITU-T/inr/forms/files/tones-0203.pdf\nringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/250,0/250\ncallwaiting = 400+450/300,0/6000\ndialrecall = 425\nrecord =  1400/500,0/15000\ninfo = !950/330,!1440/330,!1800/330,0/1000\n"),);
}


//
// CATEGORY: Asterisk Manager
//
unset($set);
$set['module'] = '';
$set['category'] = 'Asterisk Builtin mini-HTTP server';

if(!$issabelpbx_conf->conf_setting_exists('HTTPENABLED')) {
	// HTTPENABLED
	$set['value'] = false;
	$set['defaultval'] =& $set['value'];
	$set['options'] = '';
	$set['name'] = 'Enable the mini-HTTP Server';
	$set['description'] = 'Whether the Asterisk HTTP interface is enabled or not. This is for Asterisk, it is not directly related for IssabelPBX usage and the value of this setting is irrelevant for accessing core IssabelPBX settings. Default is no.';
	$set['emptyok'] = 0;
	$set['level'] = 1;
	$set['readonly'] = 0;
	$set['type'] = CONF_TYPE_BOOL;
	$issabelpbx_conf->define_conf_setting('HTTPENABLED',$set);

	// HTTPENABLESTATIC
	$set['value'] = false;
	$set['defaultval'] =& $set['value'];
	$set['options'] = '';
	$set['name'] = 'Enable Static Content';
	$set['description'] = 'Whether Asterisk should serve static content from http-static (HTML pages, CSS, javascript, etc.). Default is no.';
	$set['emptyok'] = 0;
	$set['level'] = 2;
	$set['readonly'] = 0;
	$set['type'] = CONF_TYPE_BOOL;
	$issabelpbx_conf->define_conf_setting('HTTPENABLESTATIC',$set);

	// HTTPBINDADDRESS
	$set['value'] = '0.0.0.0';
	$set['defaultval'] =& $set['value'];
	$set['options'] = '';
	$set['name'] = 'HTTP Bind Address';
	$set['description'] = 'Address to bind to. Default is 0.0.0.0';
	$set['emptyok'] = 0;
	$set['type'] = CONF_TYPE_TEXT;
	$set['level'] = 2;
	$set['readonly'] = 0;
	$issabelpbx_conf->define_conf_setting('HTTPBINDADDRESS',$set);

	// HTTPBINDPORT
	$set['value'] = '8088';
	$set['defaultval'] =& $set['value'];
	$set['options'] = '';
	$set['name'] = 'HTTP Bind Port';
	$set['description'] = 'Port to bind to. Default is 8088';
	$set['emptyok'] = 0;
	$set['options'] = array(10,65536);
	$set['type'] = CONF_TYPE_INT;
	$set['level'] = 2;
	$set['readonly'] = 0;
	$issabelpbx_conf->define_conf_setting('HTTPBINDPORT',$set);

	// HTTPPREFIX
	$set['value'] = '';
	$set['defaultval'] =& $set['value'];
	$set['options'] = '';
	$set['name'] = 'HTTP Prefix';
	$set['description'] = 'HTTP Prefix allows you to specify a prefix for all requests to the server. For example, if the prefix is set to "asterisk" then all requests must begin with /asterisk. If this field is blank it is akin to saying all requests must being with /, essentially no prefix';
	$set['emptyok'] = 1;
	$set['type'] = CONF_TYPE_TEXT;
	$set['level'] = 2;
	$set['readonly'] = 0;
	$issabelpbx_conf->define_conf_setting('HTTPPREFIX',$set);

	$issabelpbx_conf->commit_conf_settings();

	if(file_exists($amp_conf['ASTETCDIR'].'/http.conf')) {
		$settings = array();
		$httpcontents = file_get_contents($amp_conf['ASTETCDIR'].'/http.conf');
		if(preg_match('/^enabled=(.*)/im',$httpcontents,$matches)) {
			$settings['HTTPENABLED'] = ($matches[1] == 'yes') ? true : false;
		}

		if(preg_match('/^bindaddr=(.*)/im',$httpcontents,$matches)) {
			$settings['HTTPBINDADDRESS'] = !empty($matches[1]) ? $matches[1] : '0.0.0.0';
		}

		if(preg_match('/^bindport=(.*)/im',$httpcontents,$matches)) {
			$settings['HTTPBINDPORT'] = !empty($matches[1]) ? $matches[1] : '8088';
		}

		if(preg_match('/^prefix=(.*)/im',$httpcontents,$matches)) {
			$settings['HTTPPREFIX'] = !empty($matches[1]) ? $matches[1] : '';
		}

		if(!empty($settings)) {
			$issabelpbx_conf->set_conf_values($settings,true);
		}
	}
} else {
	//For security, if the file is not symlinked then we need to keep reading the settings and applying them to advanced settings just to be safe
	if(file_exists($amp_conf['ASTETCDIR'].'/http.conf') && !is_link($amp_conf['ASTETCDIR'].'/http.conf')) {
		$settings = array();
		$httpcontents = file_get_contents($amp_conf['ASTETCDIR'].'/http.conf');
		if(preg_match('/^enabled=(.*)/im',$httpcontents,$matches)) {
			$settings['HTTPENABLED'] = ($matches[1] == 'yes') ? true : false;
		}
		if(preg_match('/^bindaddr=(.*)/im',$httpcontents,$matches)) {
			$settings['HTTPBINDADDRESS'] = !empty($matches[1]) ? $matches[1] : '0.0.0.0';
		}

		if(preg_match('/^bindport=(.*)/im',$httpcontents,$matches)) {
			$settings['HTTPBINDPORT'] = !empty($matches[1]) ? $matches[1] : '8088';
		}

		if(preg_match('/^prefix=(.*)/im',$httpcontents,$matches)) {
			$settings['HTTPPREFIX'] = !empty($matches[1]) ? $matches[1] : '';
		}

		if(!empty($settings)) {
			$issabelpbx_conf->set_conf_values($settings,true);
		}
	}
}

//
// CATEGORY: Asterisk Manager (HTTPS update)
//
unset($set);
$set['module'] = '';
$set['category'] = 'Asterisk Builtin mini-HTTP server';

$issabelpbx_conf->set_conf_values(array('HTTPENABLED' => true),true,true);

if(!$issabelpbx_conf->conf_setting_exists('HTTPSENABLED')) {
    // HTTPSENABLED
    $set['value'] = false;
    $set['defaultval'] =& $set['value'];
    $set['options'] = '';
    $set['name'] = 'Enable HTTPS support for the mini-HTTP Server';
    $set['description'] = 'Whether to enable HTTPS support for the Asterisk HTTP interface. Default is no.';
    $set['emptyok'] = 0;
    $set['level'] = 1;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('HTTPSENABLED',$set);
} else {
    $issabelpbx_conf->set_conf_values(array('HTTPSENABLED' => true),true,true);
}

if(!$issabelpbx_conf->conf_setting_exists('HTTPSBINDADDR')) {
    // HTTPSBINDADDRESS
    $set['value'] = '0.0.0.0:8089';
    $set['defaultval'] =& $set['value'];
    $set['options'] = '';
    $set['name'] = 'HTTPS Bind Address/Port';
    $set['description'] = 'Address and port to bind to for HTTPS. Default is 0.0.0.0:8089';
    $set['emptyok'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $set['level'] = 2;
    $set['readonly'] = 0;
    $issabelpbx_conf->define_conf_setting('HTTPSBINDADDRESS',$set);
} else {
    $issabelpbx_conf->set_conf_values(array('HTTPSBINDADDR' => '0.0.0.0:8089'),true,true);
}

if(!$issabelpbx_conf->conf_setting_exists('HTTPSCERTFILE')) {
    // HTTPSCERTFILE
    $set['value'] = '/etc/asterisk/keys/asterisk.pem';
    $set['defaultval'] =& $set['value'];
    $set['options'] = '';
    $set['name'] = 'Certificate file';
    $set['description'] = 'Full path to certificate file for HTTPS.';
    $set['emptyok'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $set['level'] = 2;
    $set['readonly'] = 0;
    $issabelpbx_conf->define_conf_setting('HTTPSCERTFILE',$set);
} else {
    $issabelpbx_conf->set_conf_values(array('HTTPSCERTFILE' => '/etc/asterisk/keys/asterisk.pem'),true,true);
}

if(!$issabelpbx_conf->conf_setting_exists('HTTPSPRIVATEKEY')) {
    // HTTPSPRIVATEKEY
    $set['value'] = '';
    $set['defaultval'] =& $set['value'];
    $set['options'] = '';
    $set['name'] = 'Private key file';
    $set['description'] = 'Full path to private key file for HTTPS. If empty, default is to look into certificate file for private key.';
    $set['emptyok'] = 1;
    $set['type'] = CONF_TYPE_TEXT;
    $set['level'] = 2;
    $set['readonly'] = 0;
    $issabelpbx_conf->define_conf_setting('HTTPSPRIVATEKEY',$set);
} else {
    $issabelpbx_conf->set_conf_values(array('HTTPSPRIVATEKEY' => ''),true,true);
}

if(!$issabelpbx_conf->conf_setting_exists('CERTLISTFILE')) {
    // CERTLISTFILE
    $set['value'] = '/etc/pki/tls/certs/ca-bundle.crt';
    $set['defaultval'] =& $set['value'];
    $set['options'] = '';
    $set['name'] = 'Certificate List file';
    $set['description'] = 'Full path to certificate list bundle file.';
    $set['emptyok'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $set['level'] = 2;
    $set['readonly'] = 0;
    $issabelpbx_conf->define_conf_setting('CERTLISTFILE',$set);
} else {
    $issabelpbx_conf->set_conf_values(array('CERTLISTFILE' => '/etc/pki/tls/certs/ca-bundle.crt'),true,true);
}


$issabelpbx_conf->commit_conf_settings();


if (preg_match("/mysql/",$amp_conf["AMPDBENGINE"])) {
$sql='SELECT default_character_set_name FROM information_schema.SCHEMATA S WHERE schema_name = "asterisk"';
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if($check['default_character_set_name']=='latin1') {
    out(_("converting tables to utf8"));
    $sql = "ALTER DATABASE asterisk CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci";
    $res = $db->query($sql);
    if (DB::IsError($res)) {
	out(_("error occured"));
    }

$tables = array(
'admin,utf8mb4',
'ampusers,utf8',
'announcement,utf8mb4',
'backup,utf8mb4',
'backup_cache,utf8mb4',
'backup_details,utf8mb4',
'backup_items,utf8mb4',
'backup_old,utf8mb4',
'backup_server_details,utf8mb4',
'backup_servers,utf8mb4',
'backup_template_details,utf8mb4',
'backup_templates,utf8mb4',
'bosssecretary_boss,utf8mb4',
'bosssecretary_chief,utf8mb4',
'bosssecretary_group,utf8mb4',
'bosssecretary_group_numbers_free,utf8mb4',
'bosssecretary_secretary,utf8mb4',
'callback,utf8mb4',
'callrecording,utf8mb4',
'callrecording_module,utf8mb4',
'cidlookup,utf8mb4',
'cidlookup_incoming,utf8mb4',
'cronmanager,utf8mb4',
'custom_destinations,utf8mb4',
'custom_extensions,utf8mb4',
'customcontexts_contexts,utf8mb4',
'customcontexts_contexts_list,utf8mb4',
'customcontexts_includes,utf8mb4',
'customcontexts_includes_list,utf8mb4',
'customcontexts_module,utf8mb4',
'customerdb,utf8mb4',
'dahdi,utf8mb4',
'dahdichandids,utf8mb4',
'daynight,utf8',
'devices,utf8mb4',
'dialplaninjection_commands,utf8mb4',
'dialplaninjection_commands_list,utf8mb4',
'dialplaninjection_dialplaninjections,utf8mb4',
'dialplaninjection_module,utf8mb4',
'disa,utf8mb4',
'dynroute,utf8mb4',
'dynroute_dests,utf8mb4',
'extensions,utf8mb4',
'fax_details,utf8mb4',
'fax_incoming,utf8mb4',
'fax_users,utf8mb4',
'featurecodes,utf8mb4',
'findmefollow,utf8mb4',
'gabcast,utf8mb4',
'globals,utf8',
'iax,utf8mb4',
'iaxsettings,utf8mb4',
'incoming,utf8mb4',
'indications_zonelist,utf8mb4',
'inventorydb,utf8mb4',
'issabelpbx_log,utf8mb4',
'issabelpbx_settings,utf8mb4',
'ivr_details,utf8mb4',
'ivr_entries,utf8mb4',
'language_incoming,utf8mb4',
'languages,utf8mb4',
'logfile_logfiles,utf8mb4',
'logfile_settings,utf8mb4',
'manager,utf8mb4',
'managersettings,utf8mb4',
'meetme,utf8mb4',
'miscapps,utf8mb4',
'miscdests,utf8mb4',
'module_xml,utf8mb4',
'modules,utf8mb4',
'notifications,utf8mb4',
'outbound_route_patterns,utf8',
'outbound_route_sequence,utf8mb4',
'outbound_route_trunks,utf8mb4',
'outbound_routes,utf8mb4',
'outroutemsg,utf8mb4',
'paging_autoanswer,utf8',
'paging_config,utf8',
'paging_groups,utf8mb4',
'parkplus,utf8mb4',
'phpagiconf,utf8mb4',
'pinset_usage,utf8mb4',
'pinsets,utf8mb4',
'queueprio,utf8mb4',
'queues_config,utf8mb4',
'queues_details,utf8mb4',
'recordings,utf8mb4',
'ringgroups,utf8mb4',
'setcid,utf8mb4',
'sip,utf8mb4',
'sippeers,utf8mb4',
'sipsettings,utf8mb4',
'texttospeech,utf8mb4',
'timeconditions,utf8mb4',
'timegroups_details,utf8mb4',
'timegroups_groups,utf8mb4',
'trunk_dialpatterns,utf8mb4',
'trunks,utf8',
'users,utf8mb4',
'vmblast,utf8mb4',
'vmblast_groups,utf8mb4',
'voicemail_admin,utf8mb4',
'writequeuelog,utf8mb4'
);

    foreach($tables as $element) {
        $parts = preg_split("/,/",$element);
        $tbl = $parts[0];
        $chr = $parts[1];
        $res = $db->query("DESC $tbl");
        if (!DB::IsError($res)) {
            $sql = "ALTER TABLE $tbl CONVERT TO CHARACTER SET $chr COLLATE ${chr}_unicode_ci";
            $res2 = $db->query($sql);
            if (DB::IsError($res2)) {
                out(_("error occured converting table $tbl"));
            }
        }
    }


}
}
