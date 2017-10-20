<?php
 /* $Id:$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
function speeddial_get_config($engine) {
        $modulename = 'speeddial';

        // This generates the dialplan
        global $ext;
        switch($engine) {
		case "asterisk":
			$fcc = new featurecode('speeddial', 'callspeeddial');
			$callcode = $fcc->getCodeActive();
			unset($fcc);

			$fcc = new featurecode('speeddial', 'setspeeddial');
			$setcode = $fcc->getCodeActive();
			unset($fcc);

			if (!empty($code)) {
				$ext->add('app-pbdirectory', $code, '', new ext_answer(''));
				$ext->add('app-pbdirectory', $code, '', new ext_wait(1));
				$ext->add('app-pbdirectory', $code, '', new ext_goto(1,'pbdirectory'));
			}

			// [macro-speeddial-lookup]
			// arg1 is speed dial location,  arg2 (optional) is user caller ID
			$ext->add('macro-speeddial-lookup', 's', '', new ext_gotoif('$["${ARG2}"=""]]','lookupsys'));
			$ext->add('macro-speeddial-lookup', 's', '', new ext_set('SPEEDDIALNUMBER',''));
			$ext->add('macro-speeddial-lookup', 's', 'lookupuser', new ext_dbget('SPEEDDIALNUMBER','AMPUSER/${ARG2}/speeddials/${ARG1}'));
			$ext->add('macro-speeddial-lookup', 's', '', new ext_gotoif('$["${SPEEDDIALNUMBER}"=""]','lookupsys'));
			$ext->add('macro-speeddial-lookup', 's', '', new ext_noop('Found speeddial ${ARG1} for user ${ARG2}: ${SPEEDDIALNUMBER}'));
			$ext->add('macro-speeddial-lookup', 's', '', new ext_goto('end'));
			$ext->add('macro-speeddial-lookup', 's', 'lookupsys', new ext_dbget('SPEEDDIALNUMBER','sysspeeddials/${ARG1}'), 'lookupuser',101);
			$ext->add('macro-speeddial-lookup', 's', '', new ext_gotoif('$["${SPEEDDIALNUMBER}"=""]','failed'));
			$ext->add('macro-speeddial-lookup', 's', '', new ext_noop('Found system speeddial ${ARG1}: ${SPEEDDIALNUMBER}'));
			$ext->add('macro-speeddial-lookup', 's', '', new ext_goto('end'));
			$ext->add('macro-speeddial-lookup', 's', 'failed', new ext_noop('No system or user speeddial found'), 'lookupsys',101);
			$ext->add('macro-speeddial-lookup', 's', 'end', new ext_noop('End of Speeddial-lookup'));

			if (!empty($callcode)) {
				$ext->add('app-speeddial', '_'.$callcode.'.', '', new ext_macro('user-callerid',''));
				$ext->add('app-speeddial', '_'.$callcode.'.', '', new ext_set('SPEEDDIALLOCATION','${EXTEN:'.(strlen($callcode)).'}'));
				$ext->add('app-speeddial', '_'.$callcode.'.', 'lookup', new ext_macro('speeddial-lookup','${SPEEDDIALLOCATION},${AMPUSER}'));
				$ext->add('app-speeddial', '_'.$callcode.'.', '', new ext_gotoif('$["${SPEEDDIALNUMBER}"=""]','failed'));
				$ext->add('app-speeddial', '_'.$callcode.'.', '', new ext_goto('1','${SPEEDDIALNUMBER}','from-internal'));

				$ext->add('app-speeddial', '_'.$callcode.'.', 'failed', new ext_playback('speed-dial-empty'), 'lookup',101);
				$ext->add('app-speeddial', '_'.$callcode.'.', '', new ext_congestion(''));

			}

			if (!empty($setcode)) {
				$ext->add('app-speeddial', $setcode, '', new ext_goto(1, 's', 'app-speeddial-set'));
			}

			// for i18n playback in multiple languages
		
			$ext->add('app-speeddial-set', 'lang-playback', '', new ext_gosubif('$[${DIALPLAN_EXISTS('.'app-speeddial-set'.',${CHANNEL(language)})}]', 'app-speeddial-set'.',${CHANNEL(language)},${ARG1}', 'app-speeddial-set'.',en,${ARG1}'));
			$ext->add('app-speeddial-set', 'lang-playback', '', new ext_return());

			$ext->add('app-speeddial-set', 's', '', new ext_macro('user-callerid',''));
			// "enter speed dial location number"
			$ext->add('app-speeddial-set', 's', 'setloc', new ext_read('newlocation','speed-enterlocation'));
			$ext->add('app-speeddial-set', 's', 'lookup', new ext_macro('speeddial-lookup','${newlocation},${AMPUSER}'));
			$ext->add('app-speeddial-set', 's', '', new ext_gotoif('$["${SPEEDDIALNUMBER}"!=""]', 'conflicts'));

			// "enter phone number"
			$ext->add('app-speeddial-set', 's', 'setnum', new ext_read('newnum','speed-enternumber'));

			$ext->add('app-speeddial-set', 's', 'success', new ext_dbput('AMPUSER/${AMPUSER}/speeddials/${newlocation}','${newnum}'));
			$ext->add('app-speeddial-set', 's', '', new ext_gosub('1', 'lang-playback', 'app-speeddial-set', 'hook_0'));
			$ext->add('app-speeddial-set', 's', '', new ext_hangup(''));

			$ext->add('app-speeddial-set', 's', 'conflicts', new ext_gosub('1', 'lang-playback', 'app-speeddial-set', 'hook_1'));
			$ext->add('app-speeddial-set', 's', '', new ext_waitexten('60'));

			$ext->add('app-speeddial-set', '1', '', new ext_gosub('1', 'lang-playback', 'app-speeddial-set', 'hook_2'));
			$ext->add('app-speeddial-set', '1', '', new ext_goto('conflicts','s'));

			$ext->add('app-speeddial-set', '2', '', new ext_goto('setloc','s'));

			$ext->add('app-speeddial-set', '3', '', new ext_goto('setnum','s'));

			$ext->add('app-speeddial-set', 't', '', new ext_congestion(''));

		        $lang = 'en'; // English
			$ext->add('app-speeddial-set', $lang, 'hook_0', new ext_playback('speed-dial'));
			$ext->add('app-speeddial-set', $lang, '', new ext_saydigits('${newlocation}'));
			$ext->add('app-speeddial-set', $lang, '', new ext_playback('is-set-to'));
			$ext->add('app-speeddial-set', $lang, '', new ext_saydigits('${newnum}'));
			$ext->add('app-speeddial-set', $lang, '', new ext_return());
			$ext->add('app-speeddial-set', $lang, 'hook_1', new ext_playback('speed-dial'));
			$ext->add('app-speeddial-set', $lang, '', new ext_saydigits('${newlocation}'));
			$ext->add('app-speeddial-set', $lang, '', new ext_playback('is-in-use'));
			$ext->add('app-speeddial-set', $lang, '', new ext_background('press-1&to-listen-to-it&press-2&to-enter-a-diff&location&press-3&to-change&telephone-number'));
			$ext->add('app-speeddial-set', $lang, '', new ext_return());
			$ext->add('app-speeddial-set', $lang, 'hook_2', new ext_playback('speed-dial'));
			$ext->add('app-speeddial-set', $lang, '', new ext_saydigits('${newlocation}'));
			$ext->add('app-speeddial-set', $lang, '', new ext_playback('is-set-to'));
			$ext->add('app-speeddial-set', $lang, '', new ext_saydigits('${SPEEDDIALNUMBER}'));
			$ext->add('app-speeddial-set', $lang, '', new ext_return());
		        $lang = 'ja'; // Japanese
			$ext->add('app-speeddial-set', $lang, 'hook_0', new ext_playback('speed-dial'));
			$ext->add('app-speeddial-set', $lang, '', new ext_saydigits('${newlocation}'));
			$ext->add('app-speeddial-set', $lang, '', new ext_playback('jp-wo'));
			$ext->add('app-speeddial-set', $lang, '', new ext_saydigits('${newnum}'));
			$ext->add('app-speeddial-set', $lang, '', new ext_playback('is-set-to'));
			$ext->add('app-speeddial-set', $lang, '', new ext_return());
			$ext->add('app-speeddial-set', $lang, 'hook_1', new ext_playback('speed-dial'));
			$ext->add('app-speeddial-set', $lang, '', new ext_saydigits('${newlocation}'));
			$ext->add('app-speeddial-set', $lang, '', new ext_playback('jp-wa&is-in-use'));
			$ext->add('app-speeddial-set', $lang, '', new ext_background('list&press-1&to-enter-a-diff&location&jp-wo&to-enter&press-2&telephone-number&jp-wo&to-change&press-3'));
			$ext->add('app-speeddial-set', $lang, '', new ext_return());
			$ext->add('app-speeddial-set', $lang, 'hook_2', new ext_playback('speed-dial'));
			$ext->add('app-speeddial-set', $lang, '', new ext_saydigits('${newlocation}'));
			$ext->add('app-speeddial-set', $lang, '', new ext_playback('jp-wa'));
			$ext->add('app-speeddial-set', $lang, '', new ext_saydigits('${SPEEDDIALNUMBER}'));
			$ext->add('app-speeddial-set', $lang, '', new ext_playback('is-set-to-2'));
			$ext->add('app-speeddial-set', $lang, '', new ext_return());


			$ext->addInclude('from-internal-additional', 'app-speeddial');

		break;
        }
}

?>
