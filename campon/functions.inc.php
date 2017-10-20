<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
class campon_conf {
	// return the filename to write
	function get_filename() {
		return "ccss_general_additional.conf";
	}
  function addGeneralSetting($setting, $value) {
    $this->_ccss_general[$setting] = $value;
	}
	// return the output that goes in the file
	function generateConf() {
		$output = "";
		if (isset($this->_ccss_general) && is_array($this->_ccss_general)) {
			foreach ($this->_ccss_general as $setting => $value) {
				$output .= "$setting = $value\n";
			}
		}
		return $output;
	}
}

function campon_hookGet_config($engine) {
  global $ext;
  global $version;
  switch($engine) {
  case "asterisk":
    $priority = 'report';
    $ext->splice('macro-user-callerid', 's', $priority,new ext_gosubif('$[${LEN(${DB(AMPUSER/${AMPUSER}/ccss/cc_agent_policy)})} & "${DB(AMPUSER/${AMPUSER}/ccss/cc_agent_policy)}" != "never"]', 'sub-ccss,s,1',false,'${MACRO_CONTEXT},${CALLERID(dnid)}'));
  break;
  }
}

function campon_get_config($engine) {
	$modulename = 'campon';
	
	// This generates the dialplan
	global $ext;  
	global $amp_conf;
	global $campon_conf;
	switch($engine) {
		case "asterisk":

      $campon_conf->addGeneralSetting('cc_max_requests',$amp_conf['CC_MAX_REQUESTS_GLOBAL']);

      $campon_conf->addGeneralSetting('cc_available_devstate',$amp_conf['CC_BLF_OFFERED']);
      $campon_conf->addGeneralSetting('cc_offered_devstate',$amp_conf['CC_BLF_OFFERED']);
      $campon_conf->addGeneralSetting('cc_caller_requested_devstate',$amp_conf['CC_BLF_OFFERED']);
      $campon_conf->addGeneralSetting('cc_active_devstate',$amp_conf['CC_BLF_PENDING']);
      $campon_conf->addGeneralSetting('cc_callee_ready_devstate',$amp_conf['CC_BLF_PENDING']);
      $campon_conf->addGeneralSetting('cc_caller_busy_devstate',$amp_conf['CC_BLF_CALLER_BUSY']);
      $campon_conf->addGeneralSetting('cc_recalling_devstate',$amp_conf['CC_BLF_RECALL']);

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
					}	
				}
			}

      $mcontext = 'sub-ccss';
      $exten = 's';

			$ext->add($mcontext,$exten,'', new ext_execif('$[${LEN(${CCSS_SETUP})}]','Return'));
			$ext->add($mcontext,$exten,'', new ext_set('CCSS_SETUP', 'TRUE'));
      $ext->add($mcontext,$exten,'', new ext_noop_trace('AMPUSER: ${AMPUSER} Calling ${ARG2}:${ARG1} checking if all happy'));
      $ext->add($mcontext,$exten,'monitor', new ext_gosubif('$[${LEN(${DB(AMPUSER/${ARG2}/ccss/cc_monitor_policy)})}]','monitor_config,1','monitor_default,1','${ARG1},${ARG2}','${ARG1},${ARG2}'));
      $ext->add($mcontext,$exten,'agent', new ext_gosubif('$[${LEN(${DB(AMPUSER/${AMPUSER}/ccss/cc_agent_policy)})}]','agent_config,1','agent_default,1'));
      // If we got this far (vs. an early StackPop) then we save the number we are calling to play back as the callback
      //
			$ext->add($mcontext,$exten,'', new ext_set('DB(AMPUSER/${AMPUSER}/ccss/last_number)', '${ARG2}'));
			$ext->add($mcontext,$exten,'', new ext_return('${GOSUB_RETVAL}'));

      /**
       * If we are in this subroutine, it has been confirmed user doesn't have 'never' as a policy, but they could
       * have it empty, just need to TODO: double check that this couldn't be called if they are not a user, check spot
       * in the macro-user-callerid
       * 
       * if the called party does not allow monitoring, then we 'StackPop' and don't end up setting any agent settings
      **/
      // subroutine(monitor_config)
      $exten = 'monitor_config';
			$ext->add($mcontext,$exten,'', new ext_set('CALLCOMPLETION(cc_monitor_policy)', '${DB(AMPUSER/${ARG2}/ccss/cc_monitor_policy)}'));
      $ext->add($mcontext,$exten,'', new ext_gotoif('$["CALLCOMPLETION(cc_monitor_policy)" != "never"]','set_monitor'));
			$ext->add($mcontext,$exten,'', new ext_noop_trace('Callee has no settings and default disabled, returning'));
			$ext->add($mcontext,$exten,'', new ext_stackpop(''));
			$ext->add($mcontext,$exten,'', new ext_return('FALSE'));

      $ext->add($mcontext,$exten,'set_monitor', new ext_set('CALLCOMPLETION(cc_max_monitors)', '${DB(AMPUSER/${ARG2}/ccss/max_monitors)}'));
			$ext->add($mcontext,$exten,'', new ext_return('TRUE'));

      /**
       * if we are here, there is either no monitor policy set for the destination extension, or it may not be an extension
       * determing which one and what rules are. Either set defaults or abort at this point
      **/
      // subroutine(monitor_default)
      $exten = 'monitor_default';
      $ext->add($mcontext,$exten,'', new ext_gotoif('$["${DB(AMPUSER/${ARG2}/cidname)}" != ""]','is_exten'));

      If ($amp_conf['CC_NON_EXTENSION_POLICY'] == 'never') {
			  $ext->add($mcontext,$exten,'', new ext_noop_trace('calling a non-extesnion: [${ARG2} / ${DB(AMPUSER/${ARG2}/cidname)}], policy enabled , continuing',6));
			  $ext->add($mcontext,$exten,'', new ext_stackpop(''));
			  $ext->add($mcontext,$exten,'', new ext_return('FALSE'));
      } else {
			  $ext->add($mcontext,$exten,'', new ext_noop_trace('calling a non-extesnion: [${ARG2} / ${DB(AMPUSER/${ARG2}/cidname)}], policy enabled , continuing',6));
			  $ext->add($mcontext,$exten,'', new ext_set('CALLCOMPLETION(cc_monitor_policy)', $amp_conf['CC_NON_EXTENSION_POLICY']));
        $ext->add($mcontext,$exten,'', new ext_set('CALLCOMPLETION(cc_max_monitors)', $amp_conf['CC_MAX_MONITORS_DEFAULT']));
			  $ext->add($mcontext,$exten,'', new ext_return('TRUE'));
      }

      If ($amp_conf['CC_MONITOR_POLICY_DEFAULT'] == 'never') {
			  $ext->add($mcontext,$exten,'is_exten', new ext_noop_trace('Callee has no settings and default is never so disabling'));
			  $ext->add($mcontext,$exten,'is_exten', new ext_stackpop(''));
			  $ext->add($mcontext,$exten,'', new ext_return('FALSE'));
      } else {
			  $ext->add($mcontext,$exten,'is_exten', new ext_noop_trace('Callee has no settings and default enabled, continuing'));
			  $ext->add($mcontext,$exten,'is_exten', new ext_set('CALLCOMPLETION(cc_monitor_policy)', $amp_conf['CC_MONITOR_POLICY_DEFAULT']));
        $ext->add($mcontext,$exten,'', new ext_set('CALLCOMPLETION(cc_max_monitors)', $amp_conf['CC_MAX_MONITORS_DEFAULT']));
			  $ext->add($mcontext,$exten,'', new ext_return('TRUE'));
      }

      // subroutine(agent_config)
      $exten = 'agent_config';
			$ext->add($mcontext,$exten,'', new ext_set('CALLCOMPLETION(cc_agent_policy)', '${DB(AMPUSER/${AMPUSER}/ccss/cc_agent_policy)}'));
			$ext->add($mcontext,$exten,'', new ext_set('CALLCOMPLETION(cc_offer_timer)', '${DB(AMPUSER/${AMPUSER}/ccss/cc_offer_timer)}'));
			$ext->add($mcontext,$exten,'', new ext_set('CALLCOMPLETION(ccbs_available_timer)', '${DB(AMPUSER/${AMPUSER}/ccss/ccbs_available_timer)}'));
			$ext->add($mcontext,$exten,'', new ext_set('CALLCOMPLETION(ccnr_available_timer)', '${DB(AMPUSER/${AMPUSER}/ccss/ccnr_available_timer)}'));
      $ext->add($mcontext,$exten,'', new ext_set('CALLCOMPLETION(cc_callback_macro)', 'ccss-default'));
      $ext->add($mcontext, $exten,'', new ext_execif('$["${CALLCOMPLETION(cc_agent_policy)}" = "generic"]', 'Set', 'CALLCOMPLETION(cc_recall_timer)=${DB(AMPUSER/${AMPUSER}/ccss/cc_recall_timer)}'));
      $ext->add($mcontext, $exten,'', new ext_execif('$["${CALLCOMPLETION(cc_agent_policy)}" = "generic"]', 'Set', 'CALLCOMPLETION(cc_max_agents)=${DB(AMPUSER/${AMPUSER}/ccss/cc_max_agents)}'));
      $ext->add($mcontext, $exten,'', new ext_execif('$["${DB(AMPUSER/${AMPUSER}/ccss/cc_agent_dialstring)}" != ""]', 'Set', 'CALLCOMPLETION(cc_agent_dialstring)=Local/${AMPUSER}_${CALLERID(dnid)}@from-ccss-${DB(AMPUSER/${AMPUSER}/ccss/cc_agent_dialstring)}'));
      $ext->add($mcontext,$exten,'', new ext_set('CALLCOMPLETION(cc_callback_macro)', 'ccss-default'));
			$ext->add($mcontext,$exten,'', new ext_return(''));

      // subroutine(agent_default)
      $exten = 'agent_default';
      If ($amp_conf['CC_AGENT_POLICY_DEFAULT'] == 'never') {
			  $ext->add($mcontext,$exten,'', new ext_noop_trace('Caller has no settings and default is never so disabling'));
			  $ext->add($mcontext,$exten,'', new ext_stackpop(''));
			  $ext->add($mcontext,$exten,'', new ext_return('FALSE'));
      } else {
        // TODO: is it possible we are not an extension calling here and we need more logic to safeguard?
			  $ext->add($mcontext,$exten,'', new ext_noop_trace('Caller has no settings using default values since policy is enabled'));
        $ext->add($mcontext,$exten,'', new ext_set('CALLCOMPLETION(cc_agent_policy)', $amp_conf['CC_AGENT_POLICY_DEFAULT']));
        $ext->add($mcontext,$exten,'', new ext_set('CALLCOMPLETION(cc_offer_timer)', $amp_conf['CC_OFFER_TIMER_DEFAULT']));
        $ext->add($mcontext,$exten,'', new ext_set('CALLCOMPLETION(ccbs_available_timer)', $amp_conf['CCBS_AVAILABLE_TIMER_DEFAULT']));
        $ext->add($mcontext,$exten,'', new ext_set('CALLCOMPLETION(ccnr_available_timer)', $amp_conf['CCNR_AVAILABLE_TIMER_DEFAULT']));
        $ext->add($mcontext,$exten,'', new ext_set('CALLCOMPLETION(cc_callback_macro)','ccss-default'));
        If ($amp_conf['CC_AGENT_POLICY_DEFAULT'] == 'generic') {
          $ext->add($mcontext,$exten,'', new ext_set('CALLCOMPLETION(cc_recall_timer)', $amp_conf['CC_RECALL_TIMER_DEFAULT']));
        } else {
          $ext->add($mcontext,$exten,'', new ext_set('CALLCOMPLETION(cc_max_agents)', $amp_conf['CC_MAX_AGENTS_DEFAULT']));
        }

        If ($amp_conf['CC_AGENT_DIALSTRING_DEFAULT'] == 'extension') {
          $ext->add($mcontext, $exten, '', new ext_set('CALLCOMPLETION(cc_agent_dialstring)','Local/${AMPUSER}_${CALLERID(dnid)}@from-ccss-extension'));
        } else If ($amp_conf['CC_AGENT_DIALSTRING_DEFAULT'] == 'internal') {
          $ext->add($mcontext, $exten, '', new ext_set('CALLCOMPLETION(cc_agent_dialstring)','Local/${AMPUSER}_${CALLERID(dnid)}@from-ccss-internal'));
        }
			  $ext->add($mcontext,$exten,'', new ext_return('TRUE'));
      }


      $mcontext = 'macro-ccss-default';
      $exten = 's';

      $cpp = $amp_conf['CC_FORCE_DEFAULTS'] ? $amp_conf['CC_MONITOR_CID_PREPEND_DEFAULT'] : '${DB(AMPUSER/${CC_EXTEN}/ccss/cc_monitor_cid_prepend)}';

      if ($amp_conf['CC_FORCE_DEFAULTS'] && $amp_conf['CC_ANNOUNCE_MONITOR_DEFAULT']) {
        $ext->add($mcontext,$exten,'', new ext_playback('beep&calling&extension'));
        $ext->add($mcontext,$exten,'', new ext_saydigits('${CC_EXTEN}'));
      } elseif ($amp_conf['CC_FORCE_DEFAULTS']) {
        $ext->add($mcontext,$exten,'', new ext_answer(''));
      } else {
        $ext->add($mcontext,$exten,'', new ext_gotoif('$["${DB(AMPUSER/${AMPUSER}/ccss/cc_announce_monitor)}" = "silent"]','siprm'));
        $ext->add($mcontext,$exten,'', new ext_playback('beep&calling&extension'));
        $ext->add($mcontext,$exten,'', new ext_saydigits('${CC_EXTEN}'));
      }
      //TODO: should this be conditional if CC_HEADER is set??? - if not, it will remove all headers if not
      //
      $ext->add($mcontext,$exten,'siprm', new ext_sipremoveheader('${CC_HEADER}'));
		  $ext->add($mcontext,$exten,'', new ext_noop_trace('In ccmacro with: ${CC_INTERFACES} Header: ${CC_HEADER} Callback: ${CC_EXTEN}'));

      $ext->add($mcontext, $exten, '', new ext_set('CALLERID(dnid)','${CC_EXTEN}')); // TODO: this is technically a bug fix
      $ext->add($mcontext, $exten, '', new ext_set('CALLERID(name)',$cpp.'${CALLERID(name)}'));
      if ($amp_conf['CC_FORCE_DEFAULTS'] && $amp_conf['CC_MONITOR_ALERT_INFO_DEFAULT']) {
        $ext->add($mcontext, $exten, '', new ext_alertinfo($amp_conf['CC_MONITOR_ALERT_INFO_DEFAULT']));
      } elseif (!$amp_conf['CC_FORCE_DEFAULTS']) {
        $ext->add($mcontext, $exten, '', new ext_execif('$[!${LEN(${DB(AMPUSER/${AMPUSER}/ccss/cc_monitor_alert_info)})}]','MacroExit'));
        $ext->add($mcontext, $exten, '', new ext_alertinfo('${DB(AMPUSER/${CC_EXTEN}/ccss/cc_monitor_alert_info)}'));
      }
      $ext->add($mcontext,$exten,'', new ext_macroexit(''));


      $context = 'from-ccss-internal';
      $exten = '_X._X.';

      $ext->add($context, $exten, '', new ext_gosub(1,'s','sub-from-ccss','${CUT(EXTEN,_,2)}'));
      $ext->add($context, $exten, 'no_alert', new ext_goto('1','${CUT(EXTEN,_,1)}','from-internal'));

      $context = 'from-ccss-extension';
      $exten = '_X._X.';

      $ext->add($context, $exten, '', new ext_gosub(1,'s','sub-from-ccss','${CUT(EXTEN,_,2)}'));
      $ext->add($context, $exten, '', new ext_goto('1','${CUT(EXTEN,_,1)}','ext-local'));

      $mcontext = 'sub-from-ccss';
      $exten = 's';

      $cpp = $amp_conf['CC_FORCE_DEFAULTS'] ? $amp_conf['CC_AGENT_CID_PREPEND_DEFAULT'] : '${DB(AMPUSER/${AMPUSER}/ccss/cc_agent_cid_prepend)}';

      $ext->add($mcontext, $exten, '', new ext_macro('blkvm-set'));
      $ext->add($mcontext, $exten, '', new ext_set('__CC_RECALL','1'));
      $ext->add($mcontext, $exten, '', new ext_set('CALLERID(name)','${IF($[${LEN(${DB(AMPUSER/${ARG1}/cidname)})}]?'.$cpp.'${DB(AMPUSER/${ARG1}/cidname)}:CALLBACK)}')); //TODO: Make this configurable
      $ext->add($mcontext, $exten, '', new ext_set('CALLERID(number)','${ARG1}'));
      $ext->add($mcontext, $exten, '', new ext_noop_trace('CID INFO: ${CALLERID(name)} ${CALLERID(num)} EXTEN: ${ARG1}',5));
      if ($amp_conf['CC_FORCE_DEFAULTS'] && $amp_conf['CC_AGENT_ALERT_INFO_DEFAULT']) {
        $ext->add($mcontext, $exten, '', new ext_set('__CC_HEADER',$amp_conf['CC_AGENT_ALERT_INFO_DEFAULT']));
      } elseif ($amp_conf['CC_FORCE_DEFAULTS']) {
        $ext->add($mcontext, $exten, '', new ext_return(''));
      } else {
        $ext->add($mcontext, $exten, '', new ext_execif('$[!${LEN(${DB(AMPUSER/${AMPUSER}/ccss/cc_agent_alert_info)})}]','Return'));
        $ext->add($mcontext, $exten, '', new ext_set('__CC_HEADER','${DB(AMPUSER/${AMPUSER}/ccss/cc_agent_alert_info)}'));
      }
      $ext->add($mcontext, $exten, '', new ext_alertinfo('${CC_HEADER}'));
      $ext->add($mcontext, $exten, '', new ext_return(''));
		break;
	}
}

function campon_request($c) {
	global $ext;
  global $amp_conf;

	$id = "app-campon-request"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal
	$ext->add($id, $c, '', new ext_answer(''));
  $ext->add($id, $c, '', new ext_set('CCSS_SETUP', 'TRUE')); // keep from calling normal sub-ccss
	$ext->add($id, $c, '', new ext_macro('user-callerid'));
	$ext->add($id, $c, '', new ext_callcompletionrequest(''));
	$ext->add($id, $c, '', new ext_noop_trace('CC_REQUEST_RESULT: ${CC_REQUEST_RESULT} CC_REQUEST_REASON: ${CC_REQUEST_REASON} LastNumber: ${DB(AMPUSER/${AMPUSER}/ccss/last_number)}'));
	$ext->add($id, $c, '', new ext_playback('beep'));
	$ext->add($id, $c, '', new ext_macro('hangupcall'));
}

function campon_cancel($c) {
	global $ext;
  global $amp_conf;

	$id = "app-campon-cancel"; // The context to be included
	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal
	$ext->add($id, $c, '', new ext_answer(''));
  $ext->add($id, $c, '', new ext_set('CCSS_SETUP', 'TRUE')); // keep from calling normal sub-ccss
	$ext->add($id, $c, '', new ext_macro('user-callerid'));
	$ext->add($id, $c, '', new ext_callcompletioncancel(''));
	$ext->add($id, $c, '', new ext_noop_trace('CC_CANCEL_RESULT: ${CC_CANCEL_RESULT} CC_CANCEL_REASON: ${CC_CANCEL_REASON}'));
	$ext->add($id, $c, '', new ext_playback('beep'));
	$ext->add($id, $c, '', new ext_macro('hangupcall'));
}

function campon_toggle($c) {
	global $ext;
  global $amp_conf;

	$id = "app-campon-toggle"; // The context to be included
  $hint_context = 'ext-local';

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal
	$ext->add($id, $c, '', new ext_answer(''));
  $ext->add($id, $c, '', new ext_set('CCSS_SETUP', 'TRUE')); // keep from calling normal sub-ccss
	$ext->add($id, $c, '', new ext_macro('user-callerid'));
	$ext->add($id, $c, '', new ext_execif('$["${EXTENSION_STATE(' . $c . '${AMPUSER}@' . $hint_context . ')}" = "INUSE"]','CallCompletionCancel','','CallCompletionRequest',''));
	$ext->add($id, $c, '', new ext_noop_trace('CC_REQUEST_RESULT: ${CC_REQUEST_RESULT} CC_REQUEST_REASON: ${CC_REQUEST_REASON} LastNumber: ${DB(AMPUSER/${AMPUSER}/ccss/last_number)}'));
	$ext->add($id, $c, '', new ext_noop_trace('CC_CANCEL_RESULT: ${CC_CANCEL_RESULT} CC_CANCEL_REASON: ${CC_CANCEL_REASON}'));
	$ext->add($id, $c, '', new ext_playback('beep'));
	$ext->add($id, $c, '', new ext_macro('hangupcall'));

  $ext->addGlobal('CAMPONTOGGLE',$c);

  $userlist = core_users_list();
  if (is_array($userlist)) {
    foreach($userlist as $item) {
      $hint_code = $c.$item[0];
      $ext->add($hint_context, $hint_code, '', new ext_goto('1',$c,$id));
      if (!$amp_conf['DYNAMICHINTS']) {
        $devices = core_hint_get($item[0]);
        $dev_arr = explode('&',$devices);
        $hint_val = 'ccss:'.implode('&ccss:',$dev_arr);
        $ext->addHint($hint_context, $hint_code, $hint_val);
      }
    }
  }
}

function campon_configpageinit($pagename) {
	global $currentcomponent;

	$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$extension = isset($_REQUEST['extension'])?$_REQUEST['extension']:null;
	$tech_hardware = isset($_REQUEST['tech_hardware'])?$_REQUEST['tech_hardware']:null;

	// We only want to hook 'users' or 'extensions' pages.
	if ($pagename != 'users' && $pagename != 'extensions') 
		return true;
	// On a 'new' user, 'tech_hardware' is set, and there's no extension. Hook into the page.
	if ($tech_hardware != null || $pagename == 'users') {
		campon_applyhooks();
		$currentcomponent->addprocessfunc('campon_configprocess', 8);
	} elseif ($action=="add") {
		// We don't need to display anything on an 'add', but we do need to handle returned data.
		$currentcomponent->addprocessfunc('campon_configprocess', 8);
	} elseif ($extdisplay != '') {
		// We're now viewing an extension, so we need to display _and_ process.
		campon_applyhooks();
		$currentcomponent->addprocessfunc('campon_configprocess', 8);
	}
}

function campon_applyhooks() {
	global $currentcomponent;

	$currentcomponent->addoptlistitem('cc_agent_policy', 'never', _('Disable Camp-On'));
	$currentcomponent->addoptlistitem('cc_agent_policy', 'generic', _('Generic Device'));
	$currentcomponent->addoptlistitem('cc_agent_policy', 'native', _('Native Technology Support'));
	$currentcomponent->setoptlistopts('cc_agent_policy', 'sort', false);

	$currentcomponent->addoptlistitem('cc_offer_timer', '20', _('20 sec'));
	$currentcomponent->addoptlistitem('cc_offer_timer', '30', _('30 sec'));
	$currentcomponent->addoptlistitem('cc_offer_timer', '45', _('45 sec'));
	$currentcomponent->addoptlistitem('cc_offer_timer', '60', _('1 min'));
	$currentcomponent->addoptlistitem('cc_offer_timer', '120', _('2 min'));
	$currentcomponent->addoptlistitem('cc_offer_timer', '180', _('3 min'));
	$currentcomponent->addoptlistitem('cc_offer_timer', '240', _('4 min'));
	$currentcomponent->addoptlistitem('cc_offer_timer', '300', _('5 min'));
	$currentcomponent->addoptlistitem('cc_offer_timer', '600', _('10 min'));
	$currentcomponent->setoptlistopts('cc_offer_timer', 'sort', false);

	$currentcomponent->addoptlistitem('ccbs_available_timer', '1200', _('20 min'));
	$currentcomponent->addoptlistitem('ccbs_available_timer', '2400', _('40 min'));
	$currentcomponent->addoptlistitem('ccbs_available_timer', '3600', _('60 min'));
	$currentcomponent->addoptlistitem('ccbs_available_timer', '4800', _('80 min'));
	$currentcomponent->addoptlistitem('ccbs_available_timer', '6000', _('100 min'));
	$currentcomponent->addoptlistitem('ccbs_available_timer', '7200', _('2 hrs'));
	$currentcomponent->addoptlistitem('ccbs_available_timer', '10800', _('3 hrs'));
	$currentcomponent->addoptlistitem('ccbs_available_timer', '14400', _('4 hrs'));
	$currentcomponent->addoptlistitem('ccbs_available_timer', '18000', _('5 hrs'));
	$currentcomponent->addoptlistitem('ccbs_available_timer', '21600', _('5 hrs'));
	$currentcomponent->addoptlistitem('ccbs_available_timer', '25200', _('6 hrs'));
	$currentcomponent->addoptlistitem('ccbs_available_timer', '28800', _('7 hrs'));
	$currentcomponent->addoptlistitem('ccbs_available_timer', '32400', _('8 hrs'));
	$currentcomponent->setoptlistopts('ccbs_available_timer', 'sort', false);

	$currentcomponent->addoptlistitem('ccnr_available_timer', '1200', _('20 min'));
	$currentcomponent->addoptlistitem('ccnr_available_timer', '2400', _('40 min'));
	$currentcomponent->addoptlistitem('ccnr_available_timer', '3600', _('60 min'));
	$currentcomponent->addoptlistitem('ccnr_available_timer', '4800', _('80 min'));
	$currentcomponent->addoptlistitem('ccnr_available_timer', '6000', _('100 min'));
	$currentcomponent->addoptlistitem('ccnr_available_timer', '7200', _('2 hrs'));
	$currentcomponent->addoptlistitem('ccnr_available_timer', '10800', _('3 hrs'));
	$currentcomponent->addoptlistitem('ccnr_available_timer', '14400', _('4 hrs'));
	$currentcomponent->addoptlistitem('ccnr_available_timer', '18000', _('5 hrs'));
	$currentcomponent->addoptlistitem('ccnr_available_timer', '21600', _('5 hrs'));
	$currentcomponent->addoptlistitem('ccnr_available_timer', '25200', _('6 hrs'));
	$currentcomponent->addoptlistitem('ccnr_available_timer', '28800', _('7 hrs'));
	$currentcomponent->addoptlistitem('ccnr_available_timer', '32400', _('8 hrs'));
	$currentcomponent->setoptlistopts('ccnr_available_timer', 'sort', false);

  for ($i=5;$i<=60;$i++) {
	  $currentcomponent->addoptlistitem('cc_recall_timer', $i, sprintf(_('%s sec'),$i));
  }
	$currentcomponent->setoptlistopts('cc_recall_timer', 'sort', false);

  for ($i=1;$i<=20;$i++) {
	  $currentcomponent->addoptlistitem('cc_max_agents', $i, sprintf(_('%s Outgoing Requests'),$i));
  }
	$currentcomponent->setoptlistopts('cc_max_agents', 'sort', false);

	$currentcomponent->addoptlistitem('cc_agent_dialstring', '', _('Callback Device Directly'));
	$currentcomponent->addoptlistitem('cc_agent_dialstring', 'internal', _('Callback Standard'));
	$currentcomponent->addoptlistitem('cc_agent_dialstring', 'extension', _('Callback Extension (no Follow-Me)'));
	$currentcomponent->setoptlistopts('cc_agent_dialstring', 'sort', false);

	$currentcomponent->addoptlistitem('cc_monitor_policy', 'never', _('Disable Camp-On'));
	$currentcomponent->addoptlistitem('cc_monitor_policy', 'generic', _('Generic Device'));
	$currentcomponent->addoptlistitem('cc_monitor_policy', 'native', _('Native Technology Support'));
	$currentcomponent->addoptlistitem('cc_monitor_policy', 'always', _('Native Where Available'));
	$currentcomponent->setoptlistopts('cc_monitor_policy', 'sort', false);

	$currentcomponent->addoptlistitem('cc_announce_monitor', 'announce', _('Announce'));
	$currentcomponent->addoptlistitem('cc_announce_monitor', 'silent', _('Silent'));
	$currentcomponent->setoptlistopts('cc_announce_monitor', 'sort', false);

  for ($i=1;$i<=20;$i++) {
	  $currentcomponent->addoptlistitem('cc_max_monitors', $i, sprintf(_('%s Incoming Requests'),$i));
  }
	$currentcomponent->setoptlistopts('cc_max_monitors', 'sort', false);

	// Add the 'process' function - this gets called when the page is loaded, to hook into 
	// displaying stuff on the page.
	$currentcomponent->addguifunc('campon_configpageload');
}

// This is called before the page is actually displayed, so we can use addguielem().
function campon_configpageload() {
  global $amp_conf;
	global $currentcomponent;

	// Init vars from $_REQUEST[]
	$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	
	// Don't display this stuff it it's on a 'This xtn has been deleted' page.
	if ($action != 'del') {
		$ccss = campon_get($extdisplay);

    $cc_agent_policy =      $ccss['cc_agent_policy'];
    $cc_monitor_policy =    $ccss['cc_monitor_policy'];

    $cc_agent_policy_label =      _("Caller Policy");
    $cc_monitor_policy_label =    _("Callee Policy");

    $cc_offer_timer_label =       _("Caller Timeout to Request");
    $ccbs_available_timer_label = _("Max Camp-On Life Busy");
    $ccnr_available_timer_label = _("Max Camp-On Life No Answer");
    $cc_recall_timer_label =      _("Time to Ring Back Caller");
    $cc_max_agents_label =        _("Max Camped-On Extensions");
    $cc_agent_dialstring_label =  _("Caller Callback Mode");
    $cc_max_monitors_label =      _("Max Queued Callers");
    $cc_announce_monitor_label =  _("Announce Callback Extension");
    $cc_agent_alert_info_label =        _("Callback Alert-Info");
    $cc_agent_cid_prepend_label =       _("Callback CID Prepend");
    $cc_monitor_alert_info_label =        _("Callee Alert-Info");
    $cc_monitor_cid_prepend_label =       _("Callee CID Prepend");

    $cc_agent_policy_tt =      _("Asterisk: cc_agent_policy. Used to enable Camp-On for this user and set the Technology Mode that will be used when engaging the feature. In most cases 'Generic Device' should be chosen unless you have phones designed to work with channel specific capabilities.");
    $cc_monitor_policy_tt =    _("Asterisk: cc_monitor_policy. Used to control if other phones are allowed to Camp On to this extension. If so, it sets the technology mode used to monitor the availability of the extension. If no specific technology support is available then it should be set to a 'Generic Device'. In this mode, a callback will be initiated to this extension when it changes from an InUse state to NotInUse. If it was busy when first attempted, this will be when the current call has ended. If it simply did not answer, then this will be the next time this phone is used to make or answer a call and then hangs up. It is possible to set this to take advantage of 'Native Technology Support' if available and automatically fallback to the 'Generic Mode' when not.");

		$section = _('Call Camp-On Services');
    // If we are forcing defaults, don't bother showing other settings
    if ($amp_conf['CC_FORCE_DEFAULTS']) {
      $cc_default_settings_label = _("Forcing default settings");
      $cc_default_settings_tt = _("The following settings are being used for all extensions. To configure individually set 'Only Use Default Camp-On Settings' to false on the Advanced Settings page. Active settings:") . 
       "<ul>
          <li>$cc_offer_timer_label: " . $amp_conf['CC_OFFER_TIMER_DEFAULT'] . "</li> 
          <li>$ccnr_available_timer_label: " . $amp_conf['CCNR_AVAILABLE_TIMER_DEFAULT'] . "</li>
          <li>$ccbs_available_timer_label: " . $amp_conf['CCBS_AVAILABLE_TIMER_DEFAULT'] . "</li>
          <li>$cc_recall_timer_label: " . $amp_conf['CC_RECALL_TIMER_DEFAULT'] . "</li>
          <li>$cc_max_agents_label: " . $amp_conf['CC_MAX_AGENTS_DEFAULT'] . "</li>
          <li>$cc_agent_dialstring_label: " . $amp_conf['CC_AGENT_DIALSTRING_DEFAULT'] . "</li>
          <li>$cc_max_monitors_label: " . $amp_conf['CC_MAX_MONITORS_DEFAULT'] . "</li>
          <li>$cc_announce_monitor_label: " . $amp_conf['CC_ANNOUNCE_MONITOR_DEFAULT'] . "</li>
          <li>$cc_agent_alert_info_label: " . $amp_conf['CC_AGENT_ALERT_INFO_DEFAULT'] . "</li>
          <li>$cc_agent_cid_prepend_label: " . $amp_conf['CC_AGENT_CID_PREPEND_DEFAULT'] . "</li>
          <li>$cc_monitor_alert_info_label: " . $amp_conf['CC_MONITOR_ALERT_INFO_DEFAULT'] . "</li>
          <li>$cc_monitor_cid_prepend_label: " . $amp_conf['CC_MONITOR_CID_PREPEND_DEFAULT'] . "</li>
        </ul>";
      $currentcomponent->addguielem($section, new gui_link_label('cc_default_settings', $cc_default_settings_label, $cc_default_settings_tt, true));
    }
		$currentcomponent->addguielem($section, new gui_selectbox('cc_agent_policy', $currentcomponent->getoptlist('cc_agent_policy'), $cc_agent_policy, $cc_agent_policy_label, $cc_agent_policy_tt, '', false));
		$currentcomponent->addguielem($section, new gui_selectbox('cc_monitor_policy', $currentcomponent->getoptlist('cc_monitor_policy'), $cc_monitor_policy, $cc_monitor_policy_label, $cc_monitor_policy_tt, '', false));
    if ($amp_conf['CC_FORCE_DEFAULTS']) {
      return;
    }


    $cc_offer_timer =         $ccss['cc_offer_timer'];
    $ccbs_available_timer =   $ccss['ccbs_available_timer'];
    $ccnr_available_timer =   $ccss['ccnr_available_timer'];
    $cc_recall_timer =        $ccss['cc_recall_timer'];
    $cc_max_agents =          $ccss['cc_max_agents'];
    $cc_agent_dialstring =    $ccss['cc_agent_dialstring'];
    $cc_max_monitors =        $ccss['cc_max_monitors'];
    $cc_announce_monitor =    $ccss['cc_announce_monitor'];
    $cc_agent_alert_info =    $ccss['cc_agent_alert_info'];
    $cc_agent_cid_prepend =   $ccss['cc_agent_cid_prepend'];
    $cc_monitor_alert_info =  $ccss['cc_monitor_alert_info'];
    $cc_monitor_cid_prepend = $ccss['cc_monitor_cid_prepend'];

    $cc_offer_timer_tt =       _("Asterisk: cc_offer_timer. How long after dialing an extension a user has to make a call completion request.");
    $ccbs_available_timer_tt = _("Asterisk: ccbs_available_timer. How long a call completion request will remain active before expiring if the phone rang busy when first attempting the call.");
    $ccnr_available_timer_tt = _("Asterisk: ccnr_available_timer. How long a call completion request will remain active before expiring if the phone was simply unanswered when first attempting the call.");
    $cc_recall_timer_tt =      _("Asterisk: cc_recall_timer. How long to ring back a caller who's Caller Policy is set to 'Generic Device'. This has no affect if to any other setting.");
    $cc_max_agents_tt =        _("Asterisk: cc_max_agents. Only valid for when using 'Native Technology Support' for Caller Policy. This is the number of outstanding Call Completion requests that can be pending to different extensions. With 'Generic Device' mode you can only have a single request outstanding and this will be ignored.");
    $cc_agent_dialstring_tt =  _("Affects Asterisk: cc_agent_dialstring. With 'Callback Device Directly' a callback request will be dialed straight to the specific device that made the call. If using 'Native Technology Support' this may be the preferred mode. The 'Callback Standard' option will initiate a call back to the caller just as if someone else on the system placed the call, which means the call can pursue Follow-Me. To avoid Follow-Me setting, choose 'Callback Extension'.");

    $cc_max_monitors_tt =      _("Asterisk: cc_max_monitors. This is the maximum number of callers that are allowed to queue up call completion requests against this extension.");

    $cc_announce_monitor_tt =        _("Whether or not to announce the extension that is being called back when the phone is picked up.");
    $cc_agent_alert_info_tt =        _("An optional Alert-Info setting that can be used when initiating a callback. Only valid when 'Caller Policy' is set to a 'Generic Device' and 'Caller Callback Mode' is not set to 'Callback Device Directly'.");
    $cc_agent_cid_prepend_tt =       _("An optional CID Prepend setting that can be used when initiating a callback. Only valid when 'Caller Policy' is set to a 'Generic Device' and 'Caller Callback Mode' is not set to 'Callback Device Directly'.");

    $cc_monitor_alert_info_tt =        _("An optional Alert-Info setting that can be used to send to the extension being called back.");
    $cc_monitor_cid_prepend_tt =       _("An optional CID Prepend setting that can be used to send to the extension being called back.");

		$msgInvalidAlertInfo = _('Please enter a valid Alert-Info');
		$msgInvalidCIDPrefix = _('Please enter a valid CID Prefix');


		$currentcomponent->addguielem($section, new gui_selectbox('cc_offer_timer', $currentcomponent->getoptlist('cc_offer_timer'), $cc_offer_timer, $cc_offer_timer_label, $cc_offer_timer_tt, '', false));
		$currentcomponent->addguielem($section, new gui_selectbox('ccbs_available_timer', $currentcomponent->getoptlist('ccbs_available_timer'), $ccbs_available_timer, $ccbs_available_timer_label, $ccbs_available_timer_tt, '', false));
		$currentcomponent->addguielem($section, new gui_selectbox('ccnr_available_timer', $currentcomponent->getoptlist('ccnr_available_timer'), $ccnr_available_timer, $ccnr_available_timer_label, $ccnr_available_timer_tt, '', false));
		$currentcomponent->addguielem($section, new gui_selectbox('cc_recall_timer', $currentcomponent->getoptlist('cc_recall_timer'), $cc_recall_timer, $cc_recall_timer_label, $cc_recall_timer_tt, '', false));
		$currentcomponent->addguielem($section, new gui_selectbox('cc_max_agents', $currentcomponent->getoptlist('cc_max_agents'), $cc_max_agents, $cc_max_agents_label, $cc_max_agents_tt, '', false));
		$currentcomponent->addguielem($section, new gui_selectbox('cc_agent_dialstring', $currentcomponent->getoptlist('cc_agent_dialstring'), $cc_agent_dialstring, $cc_agent_dialstring_label, $cc_agent_dialstring_tt, '', false));
		$currentcomponent->addguielem($section, new gui_selectbox('cc_max_monitors', $currentcomponent->getoptlist('cc_max_monitors'), $cc_max_monitors, $cc_max_monitors_label, $cc_max_monitors_tt, '', false));

    $currentcomponent->addguielem($section, new gui_radio('cc_announce_monitor', $currentcomponent->getoptlist('cc_announce_monitor'), $cc_announce_monitor, $cc_announce_monitor_label, $cc_announce_monitor_tt, false));

    //TODO: put in validation functions after the tt
		$currentcomponent->addguielem($section, new gui_textbox('cc_agent_alert_info', $cc_agent_alert_info, $cc_agent_alert_info_label, $cc_agent_alert_info_tt, '', $msgInvalidAlertInfo, true));
		$currentcomponent->addguielem($section, new gui_textbox('cc_agent_cid_prepend', $cc_agent_cid_prepend, $cc_agent_cid_prepend_label, $cc_agent_cid_prepend_tt, '', $msgInvalidCIDPrefix, true));

		$currentcomponent->addguielem($section, new gui_textbox('cc_monitor_alert_info', $cc_monitor_alert_info, $cc_monitor_alert_info_label, $cc_monitor_alert_info_tt, '', $msgInvalidAlertInfo, true));
		$currentcomponent->addguielem($section, new gui_textbox('cc_monitor_cid_prepend', $cc_monitor_cid_prepend, $cc_monitor_cid_prepend_label, $cc_monitor_cid_prepend_tt, '', $msgInvalidCIDPrefix, true));
	}
}

function campon_configprocess() {
  global $amp_conf;

	$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$ext = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$extn = isset($_REQUEST['extension'])?$_REQUEST['extension']:null;

  $ccss['cc_agent_policy'] =      isset($_REQUEST['cc_agent_policy']) ? $_REQUEST['cc_agent_policy'] : $amp_conf['CC_AGENT_POLICY_DEFAULT'];
  $ccss['cc_offer_timer'] =       isset($_REQUEST['cc_offer_timer']) ? $_REQUEST['cc_offer_timer'] : $amp_conf['CC_OFFER_TIMER_DEFAULT'];

  if (($action == 'add' || $action == 'edit') && !$amp_conf['CC_FORCE_DEFAULTS']) {
    $ccss['ccbs_available_timer'] = isset($_REQUEST['ccbs_available_timer']) ? $_REQUEST['ccbs_available_timer'] : $amp_conf['CCBS_AVAILABLE_TIMER_DEFAULT'];
    $ccss['ccnr_available_timer'] = isset($_REQUEST['ccnr_available_timer']) ? $_REQUEST['ccnr_available_timer'] : $amp_conf['CCNR_AVAILABLE_TIMER_DEFAULT'];
    $ccss['cc_recall_timer'] =      isset($_REQUEST['cc_recall_timer']) ? $_REQUEST['cc_recall_timer'] : $amp_conf['CC_RECALL_TIMER_DEFAULT'];
    $ccss['cc_max_agents'] =        isset($_REQUEST['cc_max_agents']) ? $_REQUEST['cc_max_agents'] : $amp_conf['CC_MAX_AGENTS_DEFAULT'];
    $ccss['cc_agent_dialstring'] =  isset($_REQUEST['cc_agent_dialstring']) ? $_REQUEST['cc_agent_dialstring'] : $amp_conf['CC_AGENT_DIALSTRING_DEFAULT'];
    $ccss['cc_monitor_policy'] =    isset($_REQUEST['cc_monitor_policy']) ? $_REQUEST['cc_monitor_policy'] : $amp_conf['CC_MONITOR_POLICY_DEFAULT'];
    $ccss['cc_max_monitors'] =      isset($_REQUEST['cc_max_monitors']) ? $_REQUEST['cc_max_monitors'] : $amp_conf['CC_MAX_MONITORS_DEFAULT'];
    $ccss['cc_agent_alert_info'] =        isset($_REQUEST['cc_agent_alert_info']) ? $_REQUEST['cc_agent_alert_info'] : $amp_conf['CC_AGENT_ALERT_INFO_DEFAULT'];
    $ccss['cc_agent_cid_prepend'] =       isset($_REQUEST['cc_agent_cid_prepend']) ? $_REQUEST['cc_agent_cid_prepend'] : $amp_conf['CC_AGENT_CID_PREPEND_DEFAULT'];
    $ccss['cc_monitor_alert_info'] =        isset($_REQUEST['cc_monitor_alert_info']) ? $_REQUEST['cc_monitor_alert_info'] : $amp_conf['CC_AGENT_ALERT_INFO_DEFAULT'];
    $ccss['cc_monitor_cid_prepend'] =       isset($_REQUEST['cc_monitor_cid_prepend']) ? $_REQUEST['cc_monitor_cid_prepend'] : $amp_conf['CC_AGENT_CID_PREPEND_DEFAULT'];
  }

	if ($ext==='') { 
		$extdisplay = $extn; 
	} else {
		$extdisplay = $ext;
	} 

	if ($action == "add" || $action == "edit") {
		if (!isset($GLOBALS['abort']) || $GLOBALS['abort'] !== true) {
			campon_update($extdisplay, $ccss);
		}
	} elseif ($action == "del") {
		campon_del($extdisplay);
	}
}

function campon_get($xtn, $supply_overrides=false) {
	global $amp_conf;
	global $astman;

  if ($astman) {
    $cc_agent_policy = $astman->database_get("AMPUSER",$xtn."/ccss/cc_agent_policy");
    $ccss['cc_agent_policy'] = $cc_agent_policy ? $cc_agent_policy : $amp_conf['CC_AGENT_POLICY_DEFAULT'];
    $cc_monitor_policy = $astman->database_get("AMPUSER",$xtn."/ccss/cc_monitor_policy");
    $ccss['cc_monitor_policy'] = $cc_monitor_policy ? $cc_monitor_policy : $amp_conf['CC_MONITOR_POLICY_DEFAULT'];

    // If we are forcing defaults and are not asked to supply overrides, then we don't need to fetch anything else
    //
    if ($amp_conf['CC_FORCE_DEFAULTS'] && !$supply_overrides) {
	    return $ccss;
    }

    // If ccss has been set (meaning $cc_agent_policy is not blank) and we are either not supplying override values
    // or we are supplying them but we are not in CC_FORCE_DEFAULTS mode meaning there are no overrides.
    //
    if ($cc_agent_policy && (!$supply_overrides || $supply_override && !$amp_conf['CC_FORCE_DEFAULTS'])) {
      $ccss['cc_offer_timer'] = $astman->database_get("AMPUSER",$xtn."/ccss/cc_offer_timer");
      $ccss['ccbs_available_timer'] = $astman->database_get("AMPUSER",$xtn."/ccss/ccbs_available_timer");
      $ccss['ccnr_available_timer'] = $astman->database_get("AMPUSER",$xtn."/ccss/ccnr_available_timer");
      $ccss['cc_recall_timer'] = $astman->database_get("AMPUSER",$xtn."/ccss/cc_recall_timer");
      $ccss['cc_max_agents'] = $astman->database_get("AMPUSER",$xtn."/ccss/cc_max_agents");
      $ccss['cc_agent_dialstring'] = $astman->database_get("AMPUSER",$xtn."/ccss/cc_agent_dialstring");
      $ccss['cc_max_monitors'] = $astman->database_get("AMPUSER",$xtn."/ccss/cc_max_monitors");
      $ccss['cc_announce_monitor'] = $astman->database_get("AMPUSER",$xtn."/ccss/cc_announce_monitor");
      if (!$ccss['cc_announce_monitor']) {
        $ccss['cc_announce_monitor'] = $amp_conf['CC_ANNOUNCE_MONITOR_DEFAULT'] ? 'announce' : 'silent';
      }
      $ccss['cc_agent_alert_info'] = $astman->database_get("AMPUSER",$xtn."/ccss/cc_agent_alert_info");
      $ccss['cc_agent_cid_prepend'] = $astman->database_get("AMPUSER",$xtn."/ccss/cc_agent_cid_prepend");
      $ccss['cc_monitor_alert_info'] = $astman->database_get("AMPUSER",$xtn."/ccss/cc_monitor_alert_info");
      $ccss['cc_monitor_cid_prepend'] = $astman->database_get("AMPUSER",$xtn."/ccss/cc_monitor_cid_prepend");
    } else {
      $ccss['cc_offer_timer'] =         $amp_conf['CC_OFFER_TIMER_DEFAULT'];
      $ccss['ccbs_available_timer'] =   $amp_conf['CCBS_AVAILABLE_TIMER_DEFAULT'];
      $ccss['ccnr_available_timer'] =   $amp_conf['CCNR_AVAILABLE_TIMER_DEFAULT'];
      $ccss['cc_recall_timer'] =        $amp_conf['CC_RECALL_TIMER_DEFAULT'];
      $ccss['cc_max_agents'] =          $amp_conf['CC_MAX_AGENTS_DEFAULT'];
      $ccss['cc_agent_dialstring'] =    $amp_conf['CC_AGENT_DIALSTRING_DEFAULT'];
      $ccss['cc_max_monitors'] =        $amp_conf['CC_MAX_MONITORS_DEFAULT'];
      $ccss['cc_announce_monitor'] =    $amp_conf['CC_ANNOUNCE_MONITOR_DEFAULT'] ? 'announce' : 'silent';
      $ccss['cc_agent_alert_info'] =    $amp_conf['CC_AGENT_ALERT_INFO_DEFAULT'];
      $ccss['cc_agent_cid_prepend'] =   $amp_conf['CC_AGENT_CID_PREPEND_DEFAULT'];
      $ccss['cc_monitor_alert_info'] =  $amp_conf['CC_MONITOR_ALERT_INFO_DEFAULT'];
      $ccss['cc_monitor_cid_prepend'] = $amp_conf['CC_MONITOR_CID_PREPEND_DEFAULT'];
    }
	} else {
		fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
	}
	return $ccss;
}

function campon_update($ext, $ccss) {
  global $astman;
  global $amp_conf;

  if ($astman) {
    foreach ($ccss as $key => $value) {
      $astman->database_put("AMPUSER",$ext."/ccss/$key",$value);
    }
  } else {
    fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
  }
}

function campon_del($ext) {
  global $astman;
  global $amp_conf;

  // Clean up the tree when the user is deleted
  if ($astman) {
    $astman->database_deltree("AMPUSER/$ext/ccss");
  } else {
    fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
  }
}

/* TODO: NOTES/ISSUES
 *
 * CallCompletionRequest
 * CallCompletionCancel
 *
 * These simply fail if request can't be made or canceled. Can't have any intellignent dialplan to deal with it, really
 * bad!
 * 
 * It would be useful to put some information in channel variables when using the dialplan mode, who called, to avoid the
 * ugly hoops of XXX_XXX format we are doing to retain the information.
 */
