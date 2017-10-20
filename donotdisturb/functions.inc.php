<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
function donotdisturb_get_config($engine) {
	$modulename = 'donotdisturb';
	
	// This generates the dialplan
	global $ext;  
	global $amp_conf;

	switch($engine) {
		case "asterisk":

			// If Using DND then set this so AGI scripts can determine
			//
			if ($amp_conf['USEDEVSTATE']) {
				$ext->addGlobal('DNDDEVSTATE','TRUE');
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

			$fcc = new featurecode($modulename, 'dnd_toggle');
			$dnd_code = $fcc->getCodeActive();
			unset($fcc);

			// Create hints context for DND codes so a device can subscribe to the DND state
			//
			if ($amp_conf['USEDEVSTATE'] && $dnd_code != '') {
				$ext->addInclude('from-internal-additional','ext-dnd-hints');
				$contextname = 'ext-dnd-hints';
				$device_list = core_devices_list("all", 'full', true);
				foreach ($device_list as $device) {
          if ($device['tech'] == 'sip' || $device['tech'] == 'iax2') {
					  $ext->add($contextname, $dnd_code.$device['id'], '', new ext_goto("1",$dnd_code,"app-dnd-toggle"));
					  $ext->addHint($contextname, $dnd_code.$device['id'], "Custom:DEVDND".$device['id']);
          }
				}
			}

		break;
	}
}

function donotdisturb_dnd_on($c) {
	global $ext;
	global $amp_conf;
	global $version;

	$DEVSTATE = $amp_conf['AST_FUNC_DEVICE_STATE'];

	$id = "app-dnd-on"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_macro('user-callerid')); // $cmd,n,Macro(user-callerid)
	$ext->add($id, $c, '', new ext_setvar('DB(DND/${AMPUSER})', 'YES')); // $cmd,n,Set(...=YES)
	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($id, $c, '', new ext_setvar('STATE', 'BUSY'));
		$ext->add($id, $c, '', new ext_gosub('1', 'sstate', $id));
	}
	if ($amp_conf['FCBEEPONLY']) {
		$ext->add($id, $c, 'hook_1', new ext_playback('beep')); // $cmd,n,Playback(...)
	} else {
		$ext->add($id, $c, 'hook_1', new ext_playback('do-not-disturb&activated')); // $cmd,n,Playback(...)
	}
	$ext->add($id, $c, '', new ext_macro('hangupcall')); // $cmd,n,Macro(user-callerid)
	if ($amp_conf['USEDEVSTATE']) {
		$c = 'sstate';
		$ext->add($id, $c, '', new ext_setvar($DEVSTATE.'(Custom:DND${AMPUSER})', '${STATE}'));
		$ext->add($id, $c, '', new ext_dbget('DEVICES','AMPUSER/${AMPUSER}/device'));
		$ext->add($id, $c, '', new ext_gotoif('$["${DEVICES}" = "" ]', 'return'));
		$ext->add($id, $c, '', new ext_setvar('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '1'));
		$ext->add($id, $c, 'begin', new ext_setvar($DEVSTATE.'(Custom:DEVDND${CUT(DEVICES,&,${ITER})})','${STATE}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '$[${ITER} + 1]'));
		$ext->add($id, $c, '', new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin'));
		$ext->add($id, $c, 'return', new ext_return());
	}
}
		
function donotdisturb_dnd_off($c) {
	global $ext;
	global $amp_conf;
	global $version;

	$DEVSTATE = $amp_conf['AST_FUNC_DEVICE_STATE'];

	$id = "app-dnd-off"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_macro('user-callerid')); // $cmd,n,Macro(user-callerid)
	$ext->add($id, $c, '', new ext_dbdel('DND/${AMPUSER}')); // $cmd,n,DBdel(..)
	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($id, $c, '', new ext_setvar('STATE', 'NOT_INUSE'));
		$ext->add($id, $c, '', new ext_gosub('1', 'sstate', $id));
	}
	if ($amp_conf['FCBEEPONLY']) {
		$ext->add($id, $c, 'hook_1', new ext_playback('beep')); // $cmd,n,Playback(...)
	} else {
		$ext->add($id, $c, 'hook_1', new ext_playback('do-not-disturb&de-activated')); // $cmd,n,Playback(...)
	}
	$ext->add($id, $c, '', new ext_macro('hangupcall')); // $cmd,n,Macro(user-callerid)
	if ($amp_conf['USEDEVSTATE']) {
		$c = 'sstate';
		$ext->add($id, $c, '', new ext_setvar($DEVSTATE.'(Custom:DND${AMPUSER})', '${STATE}'));
		$ext->add($id, $c, '', new ext_dbget('DEVICES','AMPUSER/${AMPUSER}/device'));
		$ext->add($id, $c, '', new ext_gotoif('$["${DEVICES}" = "" ]', 'return'));
		$ext->add($id, $c, '', new ext_setvar('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '1'));
		$ext->add($id, $c, 'begin', new ext_setvar($DEVSTATE.'(Custom:DEVDND${CUT(DEVICES,&,${ITER})})','${STATE}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '$[${ITER} + 1]'));
		$ext->add($id, $c, '', new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin'));
		$ext->add($id, $c, 'return', new ext_return());
	}
}

function donotdisturb_dnd_toggle($c) {
	global $ext;
	global $amp_conf;
	global $version;

	$DEVSTATE = $amp_conf['AST_FUNC_DEVICE_STATE'];

	$id = "app-dnd-toggle"; // The context to be included
	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_answer(''));
	$ext->add($id, $c, '', new ext_wait('1'));
	$ext->add($id, $c, '', new ext_macro('user-callerid'));

	$ext->add($id, $c, '', new ext_gotoif('$["${DB(DND/${AMPUSER})}" = ""]', 'activate', 'deactivate'));

	$ext->add($id, $c, 'activate', new ext_setvar('DB(DND/${AMPUSER})', 'YES'));
	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($id, $c, '', new ext_setvar('STATE', 'BUSY'));
		$ext->add($id, $c, '', new ext_gosub('1', 'sstate', $id));
	}
	if ($amp_conf['FCBEEPONLY']) {
		$ext->add($id, $c, 'hook_on', new ext_playback('beep')); // $cmd,n,Playback(...)
	} else {
		$ext->add($id, $c, 'hook_on', new ext_playback('do-not-disturb&activated'));
	}
	$ext->add($id, $c, '', new ext_macro('hangupcall'));

	$ext->add($id, $c, 'deactivate', new ext_dbdel('DND/${AMPUSER}'));
	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($id, $c, '', new ext_setvar('STATE', 'NOT_INUSE'));
		$ext->add($id, $c, '', new ext_gosub('1', 'sstate', $id));
	}
	if ($amp_conf['FCBEEPONLY']) {
		$ext->add($id, $c, 'hook_off', new ext_playback('beep')); // $cmd,n,Playback(...)
	} else {
		$ext->add($id, $c, 'hook_off', new ext_playback('do-not-disturb&de-activated'));
	}
	$ext->add($id, $c, '', new ext_macro('hangupcall'));
	if ($amp_conf['USEDEVSTATE']) {
		$c = 'sstate';
		$ext->add($id, $c, '', new ext_setvar($DEVSTATE.'(Custom:DND${AMPUSER})', '${STATE}'));
		$ext->add($id, $c, '', new ext_dbget('DEVICES','AMPUSER/${AMPUSER}/device'));
		$ext->add($id, $c, '', new ext_gotoif('$["${DEVICES}" = "" ]', 'return'));
		$ext->add($id, $c, '', new ext_setvar('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '1'));
		$ext->add($id, $c, 'begin', new ext_setvar($DEVSTATE.'(Custom:DEVDND${CUT(DEVICES,&,${ITER})})','${STATE}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '$[${ITER} + 1]'));
		$ext->add($id, $c, '', new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin'));
		$ext->add($id, $c, 'return', new ext_return());
	}
}

function donotdisturb_set($extension, $state = '') {
	global $amp_conf, $astman;

	if ($state != "") {
        	$r = $astman->database_put('DND', $extension, $state);
                $value_opt = 'BUSY';
        } else {
                $astman->database_del('DND', $extension);
                $value_opt = 'NOT_INUSE';
        }
        
	// This is a kludge but ... check if the is DND and if so do additional processing
        //
	if ($amp_conf['USEDEVSTATE']) {
        	$version = $amp_conf['ASTVERSION'];
                if (version_compare($version, "1.6", "ge")) {
                	$DEVSTATE = "DEVICE_STATE";
                } else {
                        $DEVSTATE = "DEVSTATE";
                }

                $devices = $astman->database_get("AMPUSER", $extension . "/device");

                $device_arr = explode('&', $devices);
                foreach ($device_arr as $device) {
                	$ret = $astman->set_global($amp_conf['AST_FUNC_DEVICE_STATE'] . "(Custom:DEVDND$device)", $value_opt);
                }
                // And also handle the state associated with the user
                $ret = $astman->set_global($amp_conf['AST_FUNC_DEVICE_STATE'] . "(Custom:DND$extension)", $value_opt);
        }
	return true;
}

function donotdisturb_get($extension = '') {
	global $astman;
	                
        $result = false;
        if ($extension) {
		$result = $astman->database_get("DND", $extension);
	} else {
		$result = $astman->database_show("DND"); 
	}	

	return $result;
}

?>
