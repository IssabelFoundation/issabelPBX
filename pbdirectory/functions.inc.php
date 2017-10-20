<?php
 /* $Id:$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
// returns a associative arrays with keys 'destination' and 'description'
function pbdirectory_destinations() {
	// return an associative array with destination and description
	$extens[] = array('destination' => 'app-pbdirectory,pbdirectory,1', 'description' => 'Phonebook Directory');
	return $extens;
}

function pbdirectory_getdestinfo($dest) {
	global $active_modules;

	if (trim($dest) == 'app-pbdirectory,pbdirectory,1') {
		//$type = isset($active_modules['announcement']['type'])?$active_modules['announcement']['type']:'setup';
		return array('description' => _("Phonebook Directory"),
		             'edit_url' => false,
							  );
	} else {
		return false;
	}
}

function pbdirectory_get_config($engine) {
        $modulename = 'pbdirectory';

        // This generates the dialplan
        global $ext;
        switch($engine) {
		case "asterisk":
			$fcc = new featurecode('pbdirectory', 'app-pbdirectory');
			$code = $fcc->getCodeActive();
			unset($fcc);

			if (!empty($code)) {
				$ext->add('app-pbdirectory', $code, '', new ext_goto(1,'pbdirectory'));
			}
			
			$ext->add('app-pbdirectory', 'pbdirectory', '', new ext_answer());
			$ext->add('app-pbdirectory', 'pbdirectory', '', new ext_wait(1));
			$ext->add('app-pbdirectory', 'pbdirectory', '', new ext_macro('user-callerid'));
			$ext->add('app-pbdirectory', 'pbdirectory', '', new ext_agi('pbdirectory'));
			$ext->add('app-pbdirectory', 'pbdirectory', '', new ext_gotoif('$["${dialnumber}"=""]','hangup,1'));
			$ext->add('app-pbdirectory', 'pbdirectory', '', new ext_noop('Got number to dial: ${dialnumber}'));
			$ext->add('app-pbdirectory', 'pbdirectory', '', new ext_dial('Local/${dialnumber}@from-internal/n','',''));
			$ext->add('app-pbdirectory', 'hangup', '', new ext_hangup());
			
			$ext->addInclude('from-internal-additional', 'app-pbdirectory');
			
		break;
        }
}

?>
