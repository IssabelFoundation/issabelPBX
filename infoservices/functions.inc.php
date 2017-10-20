<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
function infoservices_get_config($engine) {
	$modulename = 'infoservices';
	
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

function infoservices_directory($c) {
	global $ext;
	global $db;

	$oxtn = $db->getOne("SELECT value from globals where variable='OPERATOR_XTN'");	//this needs to be here!

	$id = "app-directory"; // The context to be included. This must be unique.

	// Start creating the dialplan
	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal
	// Build the context
	$ext->add($id, $c, '', new ext_answer(''));
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,1,Wait(1)
	$ext->add($id, $c, '', new ext_agi('directory,${DIR-CONTEXT},from-did-direct,${DIRECTORY:0:1}${DIRECTORY_OPTS}'.($oxtn != '' ? 'o' : '') ));
	$ext->add($id, $c, '', new ext_playback('vm-goodbye')); // $cmd,n,Playback(vm-goodbye)
	$ext->add($id, $c, '', new ext_hangup('')); // hangup
	if ($oxtn != '') {
		$ext->add($id, 'o', '', new ext_goto('from-internal,${OPERATOR_XTN},1')); 
	} else {
		$ext->add($id, 'o', '', new ext_playback('privacy-incorrect'));
	}
}

function infoservices_calltrace($c) {
	global $ext;

	$id = "app-calltrace"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_goto('1', 's', 'app-calltrace-perform')); 

	// Create the calltrace application, which we are doing a 'Goto' to above.
	// I just reset these for ease of copying and pasting. 
	$id = 'app-calltrace-perform';
	$c = 's';
	$ext->add($id, $c, '', new ext_answer(''));
	$ext->add($id, $c, '', new ext_wait('1'));
	$ext->add($id, $c, '', new ext_macro('user-callerid')); 
	$ext->add($id, $c, '', new ext_playback('info-about-last-call&telephone-number'));
	$ext->add($id, $c, '', new ext_setvar('lastcaller', '${DB(CALLTRACE/${AMPUSER})}'));
	$ext->add($id, $c, '', new ext_gotoif('$[ $[ "${lastcaller}" = "" ] | $[ "${lastcaller}" = "unknown" ] ]', 'noinfo'));
	$ext->add($id, $c, '', new ext_saydigits('${lastcaller}'));
	$ext->add($id, $c, '', new ext_setvar('TIMEOUT(digit)', '3'));
	$ext->add($id, $c, '', new ext_setvar('TIMEOUT(response)', '7'));
	$ext->add($id, $c, '', new ext_background('to-call-this-number&press-1'));
	$ext->add($id, $c, '', new ext_goto('fin'));
	$ext->add($id, $c, 'noinfo', new ext_playback('from-unknown-caller'));
	$ext->add($id, $c, '', new ext_macro('hangupcall')); 
	$ext->add($id, $c, 'fin', new ext_noop('Waiting for input'));
	$ext->add($id, $c, '', new ext_waitexten(60));
	$ext->add($id, $c, '', new ext_Playback('sorry-youre-having-problems&goodbye'));
	$ext->add($id, '1', '', new ext_goto('1', '${lastcaller}', 'from-internal'));
	$ext->add($id, 'i', '', new ext_playback('vm-goodbye')); 
	$ext->add($id, 'i', '', new ext_macro('hangupcall')); 
	$ext->add($id, 't', '', new ext_playback('vm-goodbye')); 
	$ext->add($id, 't', '', new ext_macro('hangupcall')); 

}

function infoservices_echotest($c) {
	global $ext;

	$id = "app-echo-test"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_macro('user-callerid')); 
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_playback('demo-echotest')); // $cmd,n,Macro(user-callerid)
	$ext->add($id, $c, '', new ext_echo('')); 
	$ext->add($id, $c, '', new ext_playback('demo-echodone')); // $cmd,n,Playback(...)
	$ext->add($id, $c, '', new ext_hangup('')); // $cmd,n,Macro(user-callerid)
}

function infoservices_speakingclock($c) {
	global $ext, $amp_conf;

	// Generate the needed subroutine for the speaking clock and allow for different formats
	// based on the channel language.
	//
	// To add another language follow the pattern done for German. You should also be able to use
	// extensions_custom.conf for this.
	//
	switch ($amp_conf['TIMEFORMAT']) {
	case '24 Hour Format':
		// 24 hr format default if no language provided
		//
		$sub = "sub-hr24format";
		$ex = 'i';
		$ext->add($sub, 's', '', new ext_goto('1', '${CHANNEL(language)}'));
		$ext->add($sub, $ex, '', new ext_playback('at-tone-time-exactly'));
		$ext->add($sub, $ex, '', new ext_sayunixtime('${FutureTime},,kM \\\'and\\\' S \\\'seconds\\\''));
		$ext->add($sub, $ex, '', new ext_return(''));

		// German specific language format
		$ex = 'de';
		$ext->add($sub, $ex, '', new ext_playback('at-tone-time-exactly'));
		$ext->add($sub, $ex, '', new ext_sayunixtime('${FutureTime},,kMS'));
		$ext->add($sub, $ex, '', new ext_return(''));

	break;

	case '12 Hour Format':
	default:

		// 12 hr format default if no language provided
		//
		$sub = "sub-hr12format";
		$ex = 'i';
		$ext->add($sub, 's', '', new ext_goto('1', '${CHANNEL(language)}'));
		$ext->add($sub, $ex, '', new ext_playback('at-tone-time-exactly'));
		$ext->add($sub, $ex, '', new ext_sayunixtime('${FutureTime},,IM \\\'and\\\' S \\\'seconds\\\' p'));
		$ext->add($sub, $ex, '', new ext_return(''));

		// German specific language format
		$ex = 'de';
		$ext->add($sub, $ex, '', new ext_playback('at-tone-time-exactly'));
		$ext->add($sub, $ex, '', new ext_sayunixtime('${FutureTime},,IMSp'));
		$ext->add($sub, $ex, '', new ext_return(''));
	break;
	}

	$id = "app-speakingclock"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_macro('user-callerid')); 
	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_setvar('NumLoops','0'));
	
	$ext->add($id, $c, 'start', new ext_setvar('FutureTime','$[${EPOCH} + 11]'));  // 10 seconds to try this out
	$ext->add($id, $c, '', new ext_gosub('1','s',$sub));

	$ext->add($id, $c, 'waitloop', new ext_set('TimeLeft', '$[${FutureTime} - ${EPOCH}]'));
	$ext->add($id, $c, '', new ext_gotoif('$[${TimeLeft} < 1]','playbeep'));
	$ext->add($id, $c, '', new ext_wait(1));
	$ext->add($id, $c, '', new ext_goto('waitloop'));
	$ext->add($id, $c, 'playbeep', new ext_playback('beep'));
	$ext->add($id, $c, '', new ext_wait(5));
	$ext->add($id, $c, '', new ext_setvar('NumLoops','$[${NumLoops} + 1]'));
	$ext->add($id, $c, '', new ext_gotoif('$[${NumLoops} < 5]','start')); // 5 is maximum number of times to repeat
	$ext->add($id, $c, '', new ext_playback('goodbye'));
	$ext->add($id, $c, '', new ext_hangup(''));


	// 24 hr format default if no language provided
	//
	$id = "sub-hr24format";
	$ex = 'en';
	$ext->add($id, 's', '', new ext_gotoif('$[${DIALPLAN_EXISTS('.$id.',${CHANNEL(language)},1)}]', '${CHANNEL(language)},1',$ex.',1'));
	$ext->add($id, $ex, '', new ext_playback('at-tone-time-exactly'));
	$ext->add($id, $ex, '', new ext_sayunixtime('${FutureTime},,kM \\\'and\\\' S \\\'seconds\\\''));
	$ext->add($id, $ex, '', new ext_return(''));

	// German specific language format
	$ex = 'de';
	$ext->add($id, $ex, '', new ext_playback('at-tone-time-exactly'));
	$ext->add($id, $ex, '', new ext_sayunixtime('${FutureTime},,kMS'));
	$ext->add($id, $ex, '', new ext_return(''));


	// 12 hr format default if no language provided
	//
	$id = "sub-hr12format";
	$ex = 'en';
	$ext->add($id, 's', '', new ext_gotoif('$[${DIALPLAN_EXISTS('.$id.',${CHANNEL(language)},1)}]', '${CHANNEL(language)},1',$ex.',1'));
	$ext->add($id, $ex, '', new ext_playback('at-tone-time-exactly'));
	$ext->add($id, $ex, '', new ext_sayunixtime('${FutureTime},,IM \\\'and\\\' S \\\'seconds\\\' p'));
	$ext->add($id, $ex, '', new ext_return(''));

	// German specific language format
	$ex = 'de';
	$ext->add($id, $ex, '', new ext_playback('at-tone-time-exactly'));
	$ext->add($id, $ex, '', new ext_sayunixtime('${FutureTime},,IMSp'));
	$ext->add($id, $ex, '', new ext_return(''));

	// To add another language follow the pattern done for German. You should also be able to use
	// extensions_custom.conf for this
}

function infoservices_speakextennum($c) {
	global $ext;
	
	$id = "app-speakextennum";
	
	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_answer('')); // $cmd,1,Answer
	$ext->add($id, $c, '', new ext_wait('1')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_macro('user-callerid')); // $cmd,n,Macro(user-callerid)
	$ext->add($id, $c, '', new ext_playback('your'));
	$ext->add($id, $c, '', new ext_playback('extension'));
	$ext->add($id, $c, '', new ext_playback('number'));
	$ext->add($id, $c, '', new ext_playback('is'));
	$ext->add($id, $c, '', new ext_saydigits('${AMPUSER}'));
	$ext->add($id, $c, '', new ext_wait('2')); // $cmd,n,Wait(1)
	$ext->add($id, $c, '', new ext_hangup(''));
}
?>
