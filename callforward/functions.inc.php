<?php
// vim: set ai ts=4 sw=4 ft=php:
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
function callforward_get_config($engine) {
	$modulename = 'callforward';

	// This generates the dialplan
	global $ext;  
	global $amp_conf;  
	switch($engine) {
	case "asterisk":
		// If Using CF then set this so AGI scripts can determine
		//
		if ($amp_conf['USEDEVSTATE']) {
			$ext->addGlobal('CFDEVSTATE','TRUE');
		}
		if (is_array($featurelist = featurecodes_getModuleFeatures($modulename))) {
			foreach($featurelist as $item) {
				$featurename = $item['featurename'];
				$fname = $modulename.'_'.$featurename;
				if (function_exists($fname)) {
					$fcc = new featurecode($modulename, $featurename);
					$fc = $fcc->getCodeActive();
					unset($fcc);

					if ($fc != '')
						$fname($fc);
				} else {
					$ext->add('from-internal-additional', 'debug', '', new ext_noop($modulename.": No func $fname"));
					var_dump($item);
				}	
			}
		}

		// Create hints context for CF codes so a device can subscribe to the DND state
		//
		$fcc = new featurecode($modulename, 'cf_toggle');
		$cf_code = $fcc->getCodeActive();
		unset($fcc);

		if ($amp_conf['USEDEVSTATE'] && $cf_code != '') {
			$ext->addInclude('from-internal-additional','ext-cf-hints');
			$contextname = 'ext-cf-hints';
			$device_list = core_devices_list("all", 'full', true);
			$base_offset = strlen($cf_code);
			foreach ($device_list as $device) {
				if ($device['tech'] == 'sip' || $device['tech'] == 'iax2') {
					$offset = $base_offset + strlen($device['id']);
					$ext->add($contextname, $cf_code.$device['id'], '', new ext_goto("1",$cf_code,"app-cf-toggle"));
					$ext->add($contextname, '_'.$cf_code.$device['id'].'.', '', new ext_set("toext",'${EXTEN:'.$offset.'}'));
					$ext->add($contextname, '_'.$cf_code.$device['id'].'.', '', new ext_goto("setdirect",$cf_code,"app-cf-toggle"));
					$ext->addHint($contextname, $cf_code.$device['id'], "Custom:DEVCF".$device['id']);
				}
			}
		}

		break;
	}
}

// Unconditional Call Forwarding Toggle
function callforward_cf_toggle($c) {
	global $ext;
	global $amp_conf;

	$id = "app-cf-toggle"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_answer(''));
	$ext->add($id, $c, '', new ext_wait('1'));
	$ext->add($id, $c, '', new ext_macro('user-callerid'));
	$ext->add($id, $c, '', new ext_setvar('fromext', '${AMPUSER}'));

	$ext->add($id, $c, '', new ext_gotoif('$["${DB(CF/${fromext})}" = ""]', 'activate', 'deactivate'));

	$ext->add($id, $c, 'activate', new ext_read('toext', 'ent-target-attendant&then-press-pound'));
	$ext->add($id, $c, '', new ext_gotoif('$["${toext}"=""]', 'activate'));
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, 'toext', new ext_setvar('DB(CF/${fromext})', '${toext}')); 
	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($id, $c, '', new ext_setvar('STATE', 'BUSY'));
		$ext->add($id, $c, '', new ext_gosub('1', 'sstate', $id));
	}
	if ($amp_conf['FCBEEPONLY']) {
		$ext->add($id, $c, 'hook_on', new ext_playback('beep')); // $cmd,n,Playback(...)
	} else {
		$ext->add($id, $c, 'hook_on', new ext_playback('call-fwd-unconditional&for&extension'));
		$ext->add($id, $c, '', new ext_saydigits('${fromext}'));
		$ext->add($id, $c, '', new ext_playback('is-set-to'));
		$ext->add($id, $c, '', new ext_saydigits('${toext}'));
	}
	$ext->add($id, $c, '', new ext_macro('hangupcall'));
	$ext->add($id, $c, 'setdirect', new ext_answer(''));
	$ext->add($id, $c, '', new ext_wait('1'));
	$ext->add($id, $c, '', new ext_macro('user-callerid'));
	$ext->add($id, $c, '', new ext_goto('toext'));

	$ext->add($id, $c, 'deactivate', new ext_dbdel('CF/${fromext}')); 
	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($id, $c, '', new ext_setvar('STATE', 'NOT_INUSE'));
		$ext->add($id, $c, '', new ext_gosub('1', 'sstate', $id));
	}
	if ($amp_conf['FCBEEPONLY']) {
		$ext->add($id, $c, 'hook_off', new ext_playback('beep')); // $cmd,n,Playback(...)
	} else {
		$ext->add($id, $c, 'hook_off', new ext_playback('call-fwd-unconditional&de-activated')); // $cmd,n,Playback(...)
	}
	$ext->add($id, $c, '', new ext_macro('hangupcall'));

	if ($amp_conf['USEDEVSTATE']) {
		$c = 'sstate';
		$ext->add($id, $c, '', new ext_setvar($amp_conf['AST_FUNC_DEVICE_STATE'].'(Custom:CF${fromext})', '${STATE}'));
		$ext->add($id, $c, '', new ext_dbget('DEVICES','AMPUSER/${fromext}/device'));
		$ext->add($id, $c, '', new ext_gotoif('$["${DEVICES}" = "" ]', 'return'));
		$ext->add($id, $c, '', new ext_setvar('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '1'));
		$ext->add($id, $c, 'begin', new ext_setvar($amp_conf['AST_FUNC_DEVICE_STATE'].'(Custom:DEVCF${CUT(DEVICES,&,${ITER})})','${STATE}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '$[${ITER} + 1]'));
		$ext->add($id, $c, '', new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin'));
		$ext->add($id, $c, 'return', new ext_return());
	}
}

// Unconditional Call Forwarding, this extension
function callforward_cfon($c) {
	callforward_add_cfon($c, false);
}

// Unconditional Call Forwarding, any extension
function callforward_cfpon($c) {
	callforward_add_cfon($c, true);
}

function callforward_add_cfon($c, $prompt = false) {
	global $ext;
	global $amp_conf;

	if ($prompt) {
		$id = "app-cf-prompting-on";
	} else {
		$id = "app-cf-on";
	}

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_macro('user-callerid')); // $cmd,n,Macro(user-callerid)

	if ($prompt) {
		$ext->add($id, $c, '', new ext_read('fromext', 'call-fwd-unconditional&please-enter-your&extension&then-press-pound'));
		$ext->add($id, $c, '', new ext_setvar('fromext', '${IF($["foo${fromext}"="foo"]?${AMPUSER}:${fromext})}'));	
		$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	} else {
		$ext->add($id, $c, '', new ext_setvar('fromext', '${AMPUSER}'));	
		$ext->add($id, $c, '', new ext_gotoif('$["${fromext}"!=""]', 'startread'));
		$ext->add($id, $c, '', new ext_playback('agent-loggedoff'));
		$ext->add($id, $c, '', new ext_macro('hangupcall'));
	}
	$ext->add($id, $c, 'startread', new ext_read('toext', 'ent-target-attendant&then-press-pound'));
	$ext->add($id, $c, '', new ext_gotoif('$["foo${toext}"="foo"]', 'startread'));
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_setvar('DB(CF/${fromext})', '${toext}')); 
	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($id, $c, '', new ext_setvar('STATE', 'BUSY'));
		$ext->add($id, $c, '', new ext_gosub('1', 'sstate', $id));
	}
	if ($amp_conf['FCBEEPONLY']) {
		$ext->add($id, $c, 'hook_1', new ext_playback('beep')); // $cmd,n,Playback(...)
	} else {
		$ext->add($id, $c, 'hook_1', new ext_playback('call-fwd-unconditional&for&extension'));
		$ext->add($id, $c, '', new ext_saydigits('${fromext}'));
		$ext->add($id, $c, '', new ext_playback('is-set-to'));
		$ext->add($id, $c, '', new ext_saydigits('${toext}'));
	}
	$ext->add($id, $c, '', new ext_macro('hangupcall')); // $cmd,n,Macro(user-callerid)

	$clen = strlen($c);
	$c = "_$c.";
	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_macro('user-callerid')); // $cmd,n,Macro(user-callerid)
	$ext->add($id, $c, '', new ext_setvar('fromext', '${AMPUSER}'));
	$ext->add($id, $c, '', new ext_setvar('toext', '${EXTEN:'.$clen.'}'));
	$ext->add($id, $c, '', new ext_setvar('DB(CF/${fromext})', '${toext}')); 
	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($id, $c, '', new ext_setvar('STATE', 'BUSY'));
		$ext->add($id, $c, '', new ext_gosub('1', 'sstate', $id));
	}
	if ($amp_conf['FCBEEPONLY']) {
		$ext->add($id, $c, 'hook_2', new ext_playback('beep')); // $cmd,n,Playback(...)
	} else {
		$ext->add($id, $c, 'hook_2', new ext_playback('call-fwd-unconditional&for&extension'));
		$ext->add($id, $c, '', new ext_saydigits('${fromext}'));
		$ext->add($id, $c, '', new ext_playback('is-set-to'));
		$ext->add($id, $c, '', new ext_saydigits('${toext}'));
	}
	$ext->add($id, $c, '', new ext_macro('hangupcall')); // $cmd,n,Macro(user-callerid)

	if ($amp_conf['USEDEVSTATE']) {
		$c = 'sstate';
		$ext->add($id, $c, '', new ext_setvar($amp_conf['AST_FUNC_DEVICE_STATE'].'(Custom:CF${fromext})', '${STATE}'));
		$ext->add($id, $c, '', new ext_dbget('DEVICES','AMPUSER/${fromext}/device'));
		$ext->add($id, $c, '', new ext_gotoif('$["${DEVICES}" = "" ]', 'return'));
		$ext->add($id, $c, '', new ext_setvar('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '1'));
		$ext->add($id, $c, 'begin', new ext_setvar($amp_conf['AST_FUNC_DEVICE_STATE'].'(Custom:DEVCF${CUT(DEVICES,&,${ITER})})','${STATE}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '$[${ITER} + 1]'));
		$ext->add($id, $c, '', new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin'));
		$ext->add($id, $c, 'return', new ext_return());
	}
}

function callforward_cfoff_any($c) {
	global $ext;
	global $amp_conf;

	$id = "app-cf-off-any"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_macro('user-callerid')); // $cmd,n,Macro(user-callerid)
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)

	$ext->add($id, $c, '', new ext_read('fromext', 'please-enter-your&extension&then-press-pound'));
	$ext->add($id, $c, '', new ext_setvar('fromext', '${IF($["foo${fromext}"="foo"]?${AMPUSER}:${fromext})}'));	
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_dbdel('CF/${fromext}')); 
	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($id, $c, '', new ext_setvar('STATE', 'NOT_INUSE'));
		$ext->add($id, $c, '', new ext_gosub('1', 'sstate', $id));
	}
	$ext->add($id, $c, 'hook_1', new ext_playback('call-fwd-unconditional&for&extension'));
	$ext->add($id, $c, '', new ext_saydigits('${fromext}'));
	$ext->add($id, $c, '', new ext_playback('cancelled'));
	$ext->add($id, $c, '', new ext_macro('hangupcall')); // $cmd,n,Macro(user-callerid)

	if ($amp_conf['USEDEVSTATE']) {
		$c = 'sstate';
		$ext->add($id, $c, '', new ext_setvar($amp_conf['AST_FUNC_DEVICE_STATE'].'(Custom:CF${fromext})', '${STATE}'));
		$ext->add($id, $c, '', new ext_dbget('DEVICES','AMPUSER/${fromext}/device'));
		$ext->add($id, $c, '', new ext_gotoif('$["${DEVICES}" = "" ]', 'return'));
		$ext->add($id, $c, '', new ext_setvar('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '1'));
		$ext->add($id, $c, 'begin', new ext_setvar($amp_conf['AST_FUNC_DEVICE_STATE'].'(Custom:DEVCF${CUT(DEVICES,&,${ITER})})','${STATE}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '$[${ITER} + 1]'));
		$ext->add($id, $c, '', new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin'));
		$ext->add($id, $c, 'return', new ext_return());
	}
}

function callforward_cfoff($c) {
	global $ext;
	global $amp_conf;

	$id = "app-cf-off"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	// for this extension
	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_macro('user-callerid')); // $cmd,n,Macro(user-callerid)
	$ext->add($id, $c, '', new ext_setvar('fromext', '${AMPUSER}'));
	$ext->add($id, $c, '', new ext_dbdel('CF/${fromext}')); 
	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($id, $c, '', new ext_setvar('STATE', 'NOT_INUSE'));
		$ext->add($id, $c, '', new ext_gosub('1', 'sstate', $id));
	}
	if ($amp_conf['FCBEEPONLY']) {
		$ext->add($id, $c, 'hook_1', new ext_playback('beep')); // $cmd,n,Playback(...)
	} else {
		$ext->add($id, $c, 'hook_1', new ext_playback('call-fwd-unconditional&de-activated')); // $cmd,n,Playback(...)
	}
	$ext->add($id, $c, '', new ext_macro('hangupcall')); // $cmd,n,Macro(user-callerid)

	// for any extension, dial *XX<exten>
	$clen = strlen($c);
	$c = "_$c.";
	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_setvar('fromext', '${EXTEN:'.$clen.'}'));
	$ext->add($id, $c, '', new ext_dbdel('CF/${fromext}')); 
	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($id, $c, '', new ext_setvar('STATE', 'NOT_INUSE'));
		$ext->add($id, $c, '', new ext_gosub('1', 'sstate', $id));
	}
	if ($amp_conf['FCBEEPONLY']) {
		$ext->add($id, $c, 'hook_2', new ext_playback('beep')); // $cmd,n,Playback(...)
	} else {
		$ext->add($id, $c, 'hook_2', new ext_playback('call-fwd-unconditional&for&extension'));
		$ext->add($id, $c, '', new ext_saydigits('${fromext}'));
		$ext->add($id, $c, '', new ext_playback('cancelled'));
	}
	$ext->add($id, $c, '', new ext_macro('hangupcall')); // $cmd,n,Macro(user-callerid)

	if ($amp_conf['USEDEVSTATE']) {
		$c = 'sstate';
		$ext->add($id, $c, '', new ext_setvar($amp_conf['AST_FUNC_DEVICE_STATE'].'(Custom:CF${fromext})', '${STATE}'));
		$ext->add($id, $c, '', new ext_dbget('DEVICES','AMPUSER/${fromext}/device'));
		$ext->add($id, $c, '', new ext_gotoif('$["${DEVICES}" = "" ]', 'return'));
		$ext->add($id, $c, '', new ext_setvar('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '1'));
		$ext->add($id, $c, 'begin', new ext_setvar($amp_conf['AST_FUNC_DEVICE_STATE'].'(Custom:DEVCF${CUT(DEVICES,&,${ITER})})','${STATE}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '$[${ITER} + 1]'));
		$ext->add($id, $c, '', new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin'));
		$ext->add($id, $c, 'return', new ext_return());
	}
}

// Call Forward on Busy
function callforward_cfbon($c) {
	callforward_add_cfbon($c, false);
}

// Call Forward on Busy Prompting
function callforward_cfbpon($c) {
	callforward_add_cfbon($c, true);
}

function callforward_add_cfbon($c, $prompt = false) {
	global $ext;
	global $amp_conf;

	if ($prompt) {
		$id = "app-cf-busy-prompting-on";
	} else {
		$id = "app-cf-busy-on";
	}

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_macro('user-callerid')); // $cmd,n,Macro(user-callerid)
	if ($prompt) {
		$ext->add($id, $c, '', new ext_read('fromext', 'call-fwd-on-busy&please-enter-your&extension&then-press-pound'));
		$ext->add($id, $c, '', new ext_setvar('fromext', '${IF($["${fromext}"=""]?${AMPUSER}:${fromext})}'));
		$ext->add($id, $c, '', new ext_wait('1'));
	} else {
		$ext->add($id, $c, '', new ext_setvar('fromext', '${AMPUSER}'));
		$ext->add($id, $c, '', new ext_gotoif('$["${fromext}"!=""]', 'startread'));
		$ext->add($id, $c, '', new ext_playback('agent-loggedoff'));
		$ext->add($id, $c, '', new ext_macro('hangupcall'));
	}

	$ext->add($id, $c, 'startread', new ext_read('toext', 'ent-target-attendant&then-press-pound'));
	$ext->add($id, $c, '', new ext_gotoif('$["${toext}"=""]', 'startread'));
	$ext->add($id, $c, '', new ext_wait('1'));
	$ext->add($id, $c, '', new ext_setvar('DB(CFB/${fromext})', '${toext}')); 
	$ext->add($id, $c, 'hook_1', new ext_playback('call-fwd-on-busy&for&extension'));
	$ext->add($id, $c, '', new ext_saydigits('${fromext}'));
	$ext->add($id, $c, '', new ext_playback('is-set-to'));
	$ext->add($id, $c, '', new ext_saydigits('${toext}'));
	$ext->add($id, $c, '', new ext_macro('hangupcall'));

	$clen = strlen($c);
	$c = "_$c.";
	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_macro('user-callerid')); // $cmd,n,Macro(user-callerid)
	$ext->add($id, $c, '', new ext_setvar('fromext', '${AMPUSER}'));
	$ext->add($id, $c, '', new ext_setvar('toext', '${EXTEN:'.$clen.'}'));
	$ext->add($id, $c, '', new ext_setvar('DB(CFB/${fromext})', '${toext}')); 
	$ext->add($id, $c, 'hook_2', new ext_playback('call-fwd-on-busy&for&extension'));
	$ext->add($id, $c, '', new ext_saydigits('${fromext}'));
	$ext->add($id, $c, '', new ext_playback('is-set-to'));
	$ext->add($id, $c, '', new ext_saydigits('${toext}'));
	$ext->add($id, $c, '', new ext_macro('hangupcall')); // $cmd,n,Macro(user-callerid)
}

function callforward_cfboff_any($c) {
	global $ext;
	global $amp_conf;

	$id = "app-cf-busy-off-any"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_macro('user-callerid')); // $cmd,n,Macro(user-callerid)
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_read('fromext', 'please-enter-your&extension&then-press-pound'));
	$ext->add($id, $c, '', new ext_setvar('fromext', '${IF($["foo${fromext}"="foo"]?${AMPUSER}:${fromext})}'));	
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_dbdel('CFB/${fromext}')); 
	$ext->add($id, $c, 'hook_1', new ext_playback('call-fwd-on-busy&for&extension'));
	$ext->add($id, $c, '', new ext_saydigits('${fromext}'));
	$ext->add($id, $c, '', new ext_playback('cancelled'));
	$ext->add($id, $c, '', new ext_macro('hangupcall')); // $cmd,n,Macro(user-callerid)
}

function callforward_cfboff($c) {
	global $ext;
	global $amp_conf;

	$id = "app-cf-busy-off"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	// for this extension
	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_macro('user-callerid')); // $cmd,n,Macro(user-callerid)
	$ext->add($id, $c, '', new ext_setvar('fromext', '${AMPUSER}'));
	$ext->add($id, $c, '', new ext_dbdel('CFB/${fromext}'));
	$ext->add($id, $c, 'hook_1', new ext_playback('call-fwd-on-busy&de-activated')); // $cmd,n,Playback(...)
	$ext->add($id, $c, '', new ext_macro('hangupcall')); // $cmd,n,Macro(user-callerid)

	// for any extension, dial *XX<exten>
	$clen = strlen($c);
	$c = "_$c.";
	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_setvar('fromext', '${EXTEN:'.$clen.'}'));
	$ext->add($id, $c, '', new ext_dbdel('CFB/${fromext}')); 
	$ext->add($id, $c, 'hook_2', new ext_playback('call-fwd-on-busy&for&extension'));
	$ext->add($id, $c, '', new ext_saydigits('${fromext}'));
	$ext->add($id, $c, '', new ext_playback('cancelled'));
	$ext->add($id, $c, '', new ext_macro('hangupcall')); // $cmd,n,Macro(user-callerid)

}

// Call Forward on No Answer/Unavailable (i.e. phone not registered)
function callforward_cfuon($c) {
	callforward_add_cfuon($c, false);
}

function callforward_cfupon($c) {
	callforward_add_cfuon($c, true);
}

function callforward_add_cfuon($c, $prompt=false) {
	global $ext;
	global $amp_conf;

	if ($prompt) {
		$id = "app-cf-unavailable-prompt-on";
	} else {
		$id = "app-cf-unavailable-on";
	}

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_answer(''));
	$ext->add($id, $c, '', new ext_wait('1'));
	$ext->add($id, $c, '', new ext_macro('user-callerid'));
	if ($prompt) {
		$ext->add($id, $c, '', new ext_read('fromext', 'call-fwd-no-ans&please-enter-your&extension&then-press-pound'));
		$ext->add($id, $c, '', new ext_setvar('fromext', '${IF($["${fromext}"=""]?${AMPUSER}:${fromext})}'));
		$ext->add($id, $c, '', new ext_wait('1'));
	} else {
		$ext->add($id, $c, '', new ext_setvar('fromext', '${AMPUSER}'));
		$ext->add($id, $c, '', new ext_gotoif('$["${fromext}"!=""]', 'startread'));
		$ext->add($id, $c, '', new ext_playback('agent-loggedoff'));
		$ext->add($id, $c, '', new ext_macro('hangupcall'));
	}
	$ext->add($id, $c, 'startread', new ext_read('toext', 'ent-target-attendant&then-press-pound'));
	$ext->add($id, $c, '', new ext_gotoif('$["${toext}"=""]', 'startread'));
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_setvar('DB(CFU/${fromext})', '${toext}')); 
	$ext->add($id, $c, 'hook_1', new ext_playback('call-fwd-no-ans&for&extension'));
	$ext->add($id, $c, '', new ext_saydigits('${fromext}'));
	$ext->add($id, $c, '', new ext_playback('is-set-to'));
	$ext->add($id, $c, '', new ext_saydigits('${toext}'));
	$ext->add($id, $c, '', new ext_macro('hangupcall')); // $cmd,n,Macro(user-callerid)

	// assume this extension and forward to number after the feature code
	$clen = strlen($c);
	$c = "_$c.";
	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_macro('user-callerid')); // $cmd,n,Macro(user-callerid)
	$ext->add($id, $c, '', new ext_setvar('fromext', '${AMPUSER}'));
	$ext->add($id, $c, '', new ext_setvar('toext', '${EXTEN:'.$clen.'}'));
	$ext->add($id, $c, '', new ext_setvar('DB(CFU/${fromext})', '${toext}'));
	$ext->add($id, $c, 'hook_2', new ext_playback('call-fwd-no-ans&for&extension'));
	$ext->add($id, $c, '', new ext_saydigits('${fromext}'));
	$ext->add($id, $c, '', new ext_playback('is-set-to'));
	$ext->add($id, $c, '', new ext_saydigits('${toext}'));
	$ext->add($id, $c, '', new ext_macro('hangupcall')); // $cmd,n,Macro(user-callerid)
}

function callforward_cfuoff($c) {
	global $ext;
	global $amp_conf;

	$id = "app-cf-unavailable-off"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	// for this extension
	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_macro('user-callerid')); // $cmd,n,Macro(user-callerid)
	$ext->add($id, $c, '', new ext_setvar('fromext', '${AMPUSER}'));
	$ext->add($id, $c, '', new ext_dbdel('CFU/${fromext}'));
	$ext->add($id, $c, 'hook_1', new ext_playback('call-fwd-no-ans&de-activated')); // $cmd,n,Playback(...)
	$ext->add($id, $c, '', new ext_macro('hangupcall')); // $cmd,n,Macro(user-callerid)

	// for any extension, dial *XX<exten>
	$clen = strlen($c);
	$c = "_$c.";
	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_setvar('fromext', '${EXTEN:'.$clen.'}'));
	$ext->add($id, $c, '', new ext_dbdel('CFU/${fromext}')); 
	$ext->add($id, $c, 'hook_2', new ext_playback('call-fwd-no-ans&for&extension'));
	$ext->add($id, $c, '', new ext_saydigits('${fromext}'));
	$ext->add($id, $c, '', new ext_playback('cancelled'));
	$ext->add($id, $c, '', new ext_macro('hangupcall')); // $cmd,n,Macro(user-callerid)
}

function callforward_get_extension($extension = '') {
	global $astman;

	$cf_type = array('CF','CFU','CFB');
	$users = array();

	foreach ($cf_type as $value) {
		if ($extension) {
			$users[$value] = $astman->database_get($value, $extension);
		} else {
			$users[] = $astman->database_show($value);
		}
	}

	return $users;
}

function callforward_get_number($extension = '', $type = 'CF') {
	global $astman;

	switch ($type) {
	case 'CFU':
		$cf_type = 'CFU';
		break;
	case 'CFB':
		$cf_type = 'CFB';
		break;
	case 'CF':
	default:
		$cf_type = 'CF';
		break;
	}

	$number = $astman->database_get($cf_type, $extension);
	if (is_numeric($number)) {
		return $number;	
	}
}

function callforward_set_number($extension, $number, $type = "CF") {
	global $astman;
	global $amp_conf;

	switch ($type) {
	case 'CFU':
		$cf_type = 'CFU';
		break;
	case 'CFB':
		$cf_type = 'CFB';
		break;
	case 'CF':
	default:
		$cf_type = 'CF';
		break;
	}

	if ($cf_type == 'CF' && $amp_conf['USEDEVSTATE']) {
		$state = $number ? 'BUSY' : 'NOT_INUSE';

		$astman->set_global($amp_conf['AST_FUNC_DEVICE_STATE'] . "(Custom:" . $cf_type . $extension . ")", $state);

		$devices = $astman->database_get("AMPUSER", $extension . "/device");
		$device_arr = explode('&', $devices);
		foreach ($device_arr as $device) {
			$astman->set_global($amp_conf['AST_FUNC_DEVICE_STATE'] . "(Custom:DEV" . $cf_type . $device . ")", $state);
		}
	}

	if ($number) {
		$ret = $astman->database_put($cf_type, $extension, $number);
	} else {
		$ret = $astman->database_del($cf_type, $extension);
	}

	return $ret;
}

function callforward_set_ringtimer($extension, $ringtimer = 0) {
	global $astman;

	if ($ringtimer > 120) {
		$ringtimer = 120;
	} else if ($ringtimer < -1) {
		$ringtimer = -1;
	}

	return $astman->database_put("AMPUSER", $extension . '/cfringtimer', $ringtimer);
}

function callforward_get_ringtimer($extension) {
	global $astman;

	return $astman->database_get('AMPUSER', $extension . '/cfringtimer');
}
?>
