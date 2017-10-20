<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//for translation only
if (false) {
_("Info Services");
_("Directory");
_("Call Trace");
_("Echo Test");
_("Speaking Clock");
_("Speak Your Exten Number");
}

//removed # to call directory in 2.10, this doesnt make sense with the current directory module
$fcc = new featurecode('infoservices', 'directory');
if ($fcc->getCode()) {
	$fcc->delete();
}
unset($fcc);

$fcc = new featurecode('infoservices', 'calltrace');
$fcc->setDescription('Call Trace');
$fcc->setDefault('*69');
$fcc->update();
unset($fcc);	

$fcc = new featurecode('infoservices', 'echotest');
$fcc->setDescription('Echo Test');
$fcc->setDefault('*43');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);	

$fcc = new featurecode('infoservices', 'speakingclock');
$fcc->setDescription('Speaking Clock');
$fcc->setDefault('*60');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);	

$fcc = new featurecode('infoservices', 'speakextennum');
$fcc->setDescription('Speak Your Exten Number');
$fcc->setDefault('*65');
$fcc->update();
unset($fcc);

// Migrate TIMEFORMAT from globals if needed
//
$current_format = 'IMp';
$sql = "SELECT `value` FROM globals WHERE `variable` = 'TIMEFORMAT'";
$globals = $db->getAll($sql,DB_FETCHMODE_ASSOC);
if(!DB::IsError($globals)) {
	if (count($globals)) {
		$current_format = trim($globals[0]['value']);
		$sql = "DELETE FROM globals WHERE `variable` = 'TIMEFORMAT'";
		out(_("migrated TIMEFORMAT to Advanced Settings"));
		outn(_("deleting TIMEFORMAT from globals.."));
		$res = $db->query($sql);
		if(!DB::IsError($globals)) {
			out(_("done"));
		} else {
			out(_("could not delete"));
		}
	}
}

//"IMp" => "12 Hour"
//"kM"  => "24 Hour"
$val = $current_format == 'kM' ? '24 Hour Format' : '12 Hour Format';

$issabelpbx_conf =& issabelpbx_conf::create();

// TIMEFORMAT
$set['value'] = $val;
$set['defaultval'] = '12 Hour Format';
$set['options'] = array('12 Hour Format','24 Hour Format');
$set['name'] = 'Speaking Clock Time Format';
$set['description'] = "Time format to use with the Speaking Clock.";
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = 'infoservices';
$set['category'] = 'Dialplan and Operational';
$set['emptyok'] = 0;
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('TIMEFORMAT',$set,true);
