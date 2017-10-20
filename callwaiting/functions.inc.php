<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Copyright (C) 2005 mheydon1973
//	Copyright 2013 Schmooze Com Inc.
//
function callwaiting_get_config($engine) {
	$modulename = 'callwaiting';
	
	// This generates the dialplan
	global $ext;  
	switch($engine) {
		case "asterisk":
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
		break;
	}
}

function callwaiting_cwon($c) {
	global $ext;

	$id = "app-callwaiting-cwon"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_macro('user-callerid')); // $cmd,n,Macro(user-callerid)
	$ext->add($id, $c, '', new ext_setvar('DB(CW/${AMPUSER})', 'ENABLED')); 
	$ext->add($id, $c, 'hook_1', new ext_playback('call-waiting&activated')); // $cmd,n,Playback(...)
	$ext->add($id, $c, '', new ext_macro('hangupcall')); // $cmd,n,Macro(user-callerid)
}


function callwaiting_cwoff($c) {
	global $ext;

	$id = "app-callwaiting-cwoff"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_macro('user-callerid')); // $cmd,n,Macro(user-callerid)
	$ext->add($id, $c, '', new ext_dbdel('CW/${AMPUSER}')); 
	$ext->add($id, $c, 'hook_1', new ext_playback('call-waiting&de-activated')); // $cmd,n,Playback(...)
	$ext->add($id, $c, '', new ext_macro('hangupcall')); // $cmd,n,Macro(user-callerid)
}

function callwaiting_set($extension, $state = '') {
	global $astman;

	if ($state != "") {
        	$ret = $astman->database_put('CW',$extension,$state);
        } else {
                $ret = $astman->database_del('CW',$extension);
        	$ret = $ret['result'];
	}
	
	return $ret;
}

function callwaiting_get($extension = '') {
	global $astman;

	if ($extension) {
		return $astman->database_get('CW', $extension);
	} else {
		return $astman->database_show('CW');
	}
}
?>
