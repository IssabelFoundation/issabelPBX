<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Copyright (C) 2006 Magnus Ullberg (magnus@ullberg.us)
//  Portions Copyright (C) 2010 Mikael Carlsson (mickecamino@gmail.com)
//	Copyright 2013 Schmooze Com Inc.


function blacklist_get_config($engine) {
	global $ext;
	global $version;
	global $astman;

	switch($engine) {
		case "asterisk":

			$id = "app-blacklist";
			$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal
			$ext->add($id, $c, '', new ext_macro('user-callerid'));

			$id = "app-blacklist-check";
			$c = "s";
			// LookupBlackList doesn't seem to match empty astdb entry for "blacklist/", so we
			// need to check for the setting and if set, send to the blacklisted area
			// The gotoif below is not a typo.  For some reason, we've seen the CID number set to Unknown or Unavailable
			// don't generate the dialplan if they are not using the function
			//
			if ($astman->database_get("blacklist","blocked") == '1') {
				$ext->add($id, $c, '', new ext_gotoif('$["${CALLERID(number)}" = "Unknown"]','check-blocked'));
				$ext->add($id, $c, '', new ext_gotoif('$["${CALLERID(number)}" = "Unavailable"]','check-blocked'));
				$ext->add($id, $c, '', new ext_gotoif('$["foo${CALLERID(number)}" = "foo"]','check-blocked','check'));
				$ext->add($id, $c, 'check-blocked', new ext_gotoif('$["${DB(blacklist/blocked)}" = "1"]','blacklisted'));
			}

			if (version_compare($version, "1.6", "ge")) {
				$ext->add($id, $c, 'check', new ext_gotoif('$["${BLACKLIST()}"="1"]', 'blacklisted'));
			} else {
				$ext->add($id, $c, 'check', new ext_lookupblacklist(''));
				$ext->add($id, $c, '', new ext_gotoif('$["${LOOKUPBLSTATUS}"="FOUND"]', 'blacklisted'));
			}
    			$ext->add($id, $c, '', new ext_setvar('CALLED_BLACKLIST','1'));
			$ext->add($id, $c, '', new ext_return(''));
			$ext->add($id, $c, 'blacklisted', new ext_answer(''));
			$ext->add($id, $c, '', new ext_wait(1));
			$ext->add($id, $c, '', new ext_zapateller(''));
			$ext->add($id, $c, '', new ext_playback('ss-noservice'));
			$ext->add($id, $c, '', new ext_hangup(''));

			$modulename = 'blacklist';

			if (is_array($featurelist = featurecodes_getModuleFeatures($modulename))) {
				foreach($featurelist as $item) {
					$featurename = $item['featurename'];
					$fname = $modulename.'_'.$featurename;
					if (function_exists($fname)) {
						$fcc = new featurecode($modulename, $featurename);
						$fc = $fcc->getCodeActive();
						unset($fcc);

						if ($fc != '') {
							$fname($fc);
						}
					} else {
						$ext->add('from-internal-additional', 'debug', '', new ext_noop($modulename.": No func $fname"));
						var_dump($item);
					}
				}
			}

			break;
	}
}

function blacklist_blacklist_add($fc) {
	global $ext;

	$ext->add('app-blacklist', $fc, '', new ext_goto('1', 's', 'app-blacklist-add'));

	$id = "app-blacklist-add";
	$c = "s";
	$ext->add($id, $c, '', new ext_answer);
	$ext->add($id, $c, '', new ext_wait(1));
	$ext->add($id, $c, '', new ext_set('NumLoops', 0));
	$ext->add($id, $c, 'start', new ext_playback('enter-num-blacklist'));
	$ext->add($id, $c, '', new ext_digittimeout(5));
	$ext->add($id, $c, '', new ext_responsetimeout(60));
	$ext->add($id, $c, '', new ext_read('blacknr', 'then-press-pound'));
	$ext->add($id, $c, '', new ext_saydigits('${blacknr}'));
	$ext->add($id, $c, '', new ext_playback('if-correct-press&digits/1'));
	$ext->add($id, $c, '', new ext_noop('Waiting for input'));
	$ext->add($id, $c, 'end', new ext_waitexten(60));
	$ext->add($id, $c, '', new ext_playback('sorry-youre-having-problems&goodbye'));
	$c = "1";
	$ext->add($id, $c, '', new ext_gotoif('$[ "${blacknr}" != ""]','','app-blacklist-add-invalid,s,1'));
	$ext->add($id, $c, '', new ext_set('DB(blacklist/${blacknr})', 1));
	$ext->add($id, $c, '', new ext_playback('num-was-successfully&added'));
	$ext->add($id, $c, '', new ext_wait(1));
	$ext->add($id, $c, '', new ext_hangup);

	$id = "app-blacklist-add-invalid";
	$c = "s";
	$ext->add($id, $c, '', new ext_set('NumLoops','$[${NumLoops} + 1]'));
	$ext->add($id, $c, '', new ext_playback('pm-invalid-option'));
	$ext->add($id, $c, '', new ext_gotoif('$[${NumLoops} < 3]','app-blacklist-add,s,start'));
	$ext->add($id, $c, '', new ext_playback('goodbye'));
	$ext->add($id, $c, '', new ext_hangup);

}

function blacklist_blacklist_remove($fc) {
	global $ext;

	$ext->add('app-blacklist', $fc, '', new ext_goto('1', 's', 'app-blacklist-remove'));

	$id = "app-blacklist-remove";
	$c = "s";
	$ext->add($id, $c, '', new ext_answer);
	$ext->add($id, $c, '', new ext_wait(1));
	$ext->add($id, $c, '', new ext_playback('entr-num-rmv-blklist'));
	$ext->add($id, $c, '', new ext_digittimeout(5));
	$ext->add($id, $c, '', new ext_responsetimeout(60));
	$ext->add($id, $c, '', new ext_read('blacknr', 'then-press-pound'));
	$ext->add($id, $c, '', new ext_saydigits('${blacknr}'));
	$ext->add($id, $c, '', new ext_playback('if-correct-press&digits/1'));
	$ext->add($id, $c, '', new ext_noop('Waiting for input'));
	$ext->add($id, $c, 'end', new ext_waitexten(60));
	$ext->add($id, $c, '', new ext_playback('sorry-youre-having-problems&goodbye'));
	$c = "1";
 	$ext->add($id, $c, '', new ext_dbdel('blacklist/${blacknr}'));
 	$ext->add($id, $c, '', new ext_playback('num-was-successfully&removed'));
 	$ext->add($id, $c, '', new ext_wait(1));
 	$ext->add($id, $c, '', new ext_hangup);
}

function blacklist_blacklist_last($fc) {
	global $ext;

	$ext->add('app-blacklist', $fc, '', new ext_goto('1', 's', 'app-blacklist-last'));

	$id = "app-blacklist-last";
	$c = "s";
	$ext->add($id, $c, '', new ext_answer);
	$ext->add($id, $c, '', new ext_wait(1));
	$ext->add($id, $c, '', new ext_setvar('lastcaller', '${DB(CALLTRACE/${CALLERID(number)})}'));
	$ext->add($id, $c, '', new ext_gotoif('$[ $[ "${lastcaller}" = "" ] | $[ "${lastcaller}" = "unknown" ] ]', 'noinfo'));
 	$ext->add($id, $c, '', new ext_playback('privacy-to-blacklist-last-caller&telephone-number'));
	$ext->add($id, $c, '', new ext_saydigits('${lastcaller}'));
	$ext->add($id, $c, '', new ext_setvar('TIMEOUT(digit)', '3'));
	$ext->add($id, $c, '', new ext_setvar('TIMEOUT(response)', '7'));
	$ext->add($id, $c, '', new ext_playback('if-correct-press&digits/1'));
	$ext->add($id, $c, '', new ext_goto('end'));
	$ext->add($id, $c, 'noinfo', new ext_playback('unidentified-no-callback'));
	$ext->add($id, $c, '', new ext_hangup);
	$ext->add($id, $c, '', new ext_noop('Waiting for input'));
	$ext->add($id, $c, 'end', new ext_waitexten(60));
	$ext->add($id, $c, '', new ext_playback('sorry-youre-having-problems&goodbye'));
	$c = "1";
	$ext->add($id, $c, '', new ext_set('DB(blacklist/${lastcaller})', 1));
	$ext->add($id, $c, '', new ext_playback('num-was-successfully'));
	$ext->add($id, $c, '', new ext_playback('added'));
	$ext->add($id, $c, '', new ext_wait(1));
	$ext->add($id, $c, '', new ext_hangup);
}

function blacklist_hookGet_config($engine) {
	global $ext;
	switch($engine) {
		case "asterisk":
			// Code from modules/core/functions.inc.php core_get_config inbound routes
			$didlist = core_did_list();
			if (is_array($didlist)) {
				foreach ($didlist as $item) {

					$exten = trim($item['extension']);
					$cidnum = trim($item['cidnum']);

					if ($cidnum != '' && $exten == '') {
						$exten = 's';
						$pricid = ($item['pricid']) ? true:false;
					} else if (($cidnum != '' && $exten != '') || ($cidnum == '' && $exten == '')) {
						$pricid = true;
					} else {
						$pricid = false;
					}
					$context = ($pricid) ? "ext-did-0001":"ext-did-0002";

                    if (function_exists("empty_issabelpbx")) {
                        $exten = empty_issabelpbx($exten)?"s":$exten;
                    } else {
                        $exten = (empty($exten)?"s":$exten);
                    }

					$exten = $exten.(empty($cidnum)?"":"/".$cidnum); //if a CID num is defined, add it

					$ext->splice($context, $exten, 1, new ext_gosub('1', 's', 'app-blacklist-check'));
				}
			} // else no DID's defined. Not even a catchall.
			break;
	}
}

function blacklist_list() {
	global $amp_conf;
	global $astman;

$ast_ge_16 =  version_compare($amp_conf['ASTVERSION'], "1.6", "ge");
        if ($astman) {
		$list = $astman->database_show('blacklist');
		if($ast_ge_16) {
		    foreach ($list as $k => $v) {
			$numbers = substr($k, 11);
			$blacklisted[] = array('number' => $numbers, 'description' => $v);
			}
		    if (isset($blacklisted) && is_array($blacklisted))
			// Why this sorting? When used it does not yield the result I want
			//    natsort($blacklisted);
		    return isset($blacklisted)?$blacklisted:null;
		} else {
		    foreach ($list as $k => $v) {
			$numbers[substr($k, 11)] = substr($k, 11);
			}
			if (isset($numbers) && is_array($numbers))
			    natcasesort($numbers);
			return isset($numbers)?$numbers:null;
			}
        } else {
                fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
        }
}

function blacklist_del($number){
	global $amp_conf;
	global $astman;
	if ($astman) {
		$astman->database_del("blacklist",$number);
	} else {
		fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
	}
}

function blacklist_add($post){
	global $amp_conf;
	global $astman;

$ast_ge_16 =  version_compare($amp_conf['ASTVERSION'], "1.6", "ge");

	if(!blacklist_chk($post))
		return false;

	extract($post);
	if ($astman) {
		if ($ast_ge_16) {
		$post['description']==""?$post['description'] = '1':$post['description'];
		$astman->database_put("blacklist",$post['number'], '"'.$post['description'].'"');
		    } else {
		    	    $astman->database_put("blacklist",$number, '1');
		    	    }
		// Remove filtering for blocked/unknown cid
		$astman->database_del("blacklist","blocked");
		// Add it back if it's checked
		if($post['blocked'] == "1")  {
			$astman->database_put("blacklist","blocked", "1");
			needreload();
		}
	} else {
		fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
	}
}


// ensures post vars is valid
function blacklist_chk($post){
	return true;
}

?>
