<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
/* 	Generates dialplan for "queues" components (extensions & inbound routing)
	We call this with retrieve_conf
*/
function queues_get_config($engine) {
	global $ext;  // is this the best way to pass this?
	global $amp_conf;
	global $version;

	$queues_conf = queues_conf::create();

	switch($engine) {
		case "asterisk":
			global $astman;

			//set our reset cron
			queues_set_backup_cron();

			$ast_ge_14 = version_compare($version,'1.4','ge');
			$ast_ge_16 = version_compare($version,'1.6','ge');
			$ast_ge_14_25 = version_compare($version,'1.4.25','ge');
			$ast_ge_18 = version_compare($version,'1.8','ge');
			$ast_ge_11 = version_compare($version,'11','ge');

			$has_extension_state = $ast_ge_16;
			if ($ast_ge_14 && !$ast_ge_16) {
				$response = $astman->send_request('Command', array('Command' => 'module show like func_extstate'));
				if (preg_match('/1 modules loaded/', $response['data'])) {
					$has_extension_state = true;
					
				}
			}
			
			if (isset($queues_conf) && is_a($queues_conf, "queues_conf")) {
				$queues_conf->addQueuesGeneral('persistentmembers',$amp_conf['QUEUES_PESISTENTMEMBERS'] ? 'yes' : 'no');
					if ($ast_ge_16) {
					$queues_conf->addQueuesGeneral('shared_lastcall',$amp_conf['QUEUES_SHARED_LASTCALL'] ? 'yes' : 'no');
					$queues_conf->addQueuesGeneral('updatecdr',$amp_conf['QUEUES_UPDATECDR'] ? 'yes' : 'no');
				}
				if ($amp_conf['QUEUES_MIX_MONITOR']) {
					$queues_conf->addQueuesGeneral('monitor-type', 'MixMonitor');
				}
			}

			/* queue extensions */
			$ext->addInclude('from-internal-additional','ext-queues');
			/* Trial DEVSTATE */
			if ($amp_conf['USEDEVSTATE']) {
				$ext->addGlobal('QUEDEVSTATE','TRUE');
			}

			$qlist = queues_list(true);
			if (empty($qlist)) {
				return; //nothing to do if we dont have any queues
			}
			
			// $que_code = '*45';
			$fcc = new featurecode('queues', 'que_toggle');
			$que_code = $fcc->getCodeActive();
			unset($fcc);
			if ($que_code != '') {
				queue_app_toggle();
				queue_app_all_toggle();
				queue_agent_del_toggle();
				queue_agent_add_toggle();
				$ext->addGlobal('QUEUETOGGLE',$que_code);
			}

			// $que_pause_code = '*46';
			$fcc = new featurecode('queues', 'que_pause_toggle');
			$que_pause_code = $fcc->getCodeActive();
			unset($fcc);
			if ($que_pause_code != '') {
				app_queue_pause_toggle();
				app_all_queue_pause_toggle();
				$ext->addGlobal('QUEUEPAUSETOGGLE',$que_pause_code);
			}

			// $que_callers_code = '*47';
			$fcc = new featurecode('queues', 'que_callers');
			$que_callers_code = $fcc->getCodeActive();
			unset($fcc);

			$from_queue_exten_only = 'from-queue-exten-only';
			$from_queue_exten_internal = 'from-queue-exten-internal';

			$qmembers = array();
			foreach($qlist as $item) {
				$exten = $item[0];
				$q = queues_get($exten);
				$c = 'ext-queues';
				
				$grppre = (isset($q['prefix'])?$q['prefix']:'');
				$alertinfo = (isset($q['alertinfo'])?$q['alertinfo']:'');

				// Not sure why someone would ever have a ; in the regex, but since Asterisk has problems with them
				// it would need to be escaped
				$qregex = (isset($q['qregex'])?$q['qregex']:'');
				str_replace(';','\;',$qregex);
			
				$ext->add($c, $exten, '', new ext_macro('user-callerid'));
			
				if (isset($q['qnoanswer']) && $q['qnoanswer'] == FALSE) {
					$ext->add($c, $exten, '', new ext_answer(''));
				} else {
					// TODO: should this only be set if noanswer + (!ringtones || joinannounce)???
					$ext->add($c, $exten, '', new ext_progress());
				}

				// block voicemail until phone is answered at which point a macro should be called on the answering
				// line to clear this flag so that subsequent transfers can occur.
				if ($q['queuewait']) {
					$ext->add($c, $exten, '', new ext_execif('$["${QUEUEWAIT}" = ""]', 'Set', '__QUEUEWAIT=${EPOCH}'));
				}
				// If extension_only don't do this and CFIGNORE
				if($q['use_queue_context'] != '2') {
					$ext->add($c, $exten, '', new ext_macro('blkvm-set', 'reset'));
					$ext->add($c, $exten, '', new ext_execif('$["${REGEX("(M[(]auto-blkvm[)])" ${DIAL_OPTIONS})}" != "1"]', 'Set', '_DIAL_OPTIONS=${DIAL_OPTIONS}M(auto-blkvm)'));
				}

				// Inform all the children NOT to send calls to destinations or voicemail
				//
				$ext->add($c, $exten, '', new ext_setvar('__NODEST', '${EXTEN}'));

				/*
				 * Virtual Queue Settings, dialplan designed so these can be changed by other modules and those changes
				 * will override the configured changes here.
				 */

				// deal with group CID prefix
				$ext->add($c, $exten, '', new ext_set('QCIDPP', '${IF($[${LEN(${VQ_CIDPP})}>0]?${VQ_CIDPP}' . ':' . ($grppre == '' ? ' ':$grppre) . ')}'));
				$ext->add($c, $exten, '', new ext_set('VQ_CIDPP', ''));
				$ext->add($c, $exten, '', new ext_execif('$["${QCIDPP}"!=""]', 'Macro', 'prepend-cid,${QCIDPP}'));

				// Set Alert_Info
				$ainfo = $alertinfo != '' ? str_replace(';', '\;', $alertinfo) : ' ';
				$ext->add($c, $exten, '', new ext_set('QAINFO', '${IF($[${LEN(${VQ_AINFO})}>0]?${VQ_AINFO}:' . $ainfo . ')}'));
				$ext->add($c, $exten, '', new ext_set('VQ_AINFO', ''));
				$ext->add($c, $exten, '', new ext_execif('$["${QAINFO}"!=""]', 'Set', '__ALERT_INFO=${QAINFO}'));

				$joinannounce_id = (isset($q['joinannounce_id'])?$q['joinannounce_id']:'');
				$joinannounce = $joinannounce_id ? recordings_get_file($joinannounce_id) : ' ';
				$joinansw = isset($q['qnoanswer']) && $q['qnoanswer'] == TRUE ? 'noanswer' : '';
				$cplay = $q['skip_joinannounce'] ? ' && ${QUEUE_MEMBER(' . $exten . ',' . $q['skip_joinannounce'] . ')}<1' : '';
				$ext->add($c, $exten, '', new ext_set('QJOINMSG', '${IF($[${LEN(${VQ_JOINMSG})}>0]?${IF($["${VQ_JOINMSG}"!="0"]?${VQ_JOINMSG}: )}:' . $joinannounce . ')}'));
				$ext->add($c, $exten, '', new ext_set('VQ_JOINMSG', ''));

				$qmoh = isset($q['music']) && $q['music'] != '' ? $q['music'] : ' ';
				$ext->add($c, $exten, '', new ext_set('QMOH', '${IF($["${VQ_MOH}"!=""]?${VQ_MOH}:' . $qmoh . ')}'));
				$ext->add($c, $exten, '', new ext_set('VQ_MOH', ''));
				$ext->add($c, $exten, '', new ext_execif('$["${QMOH}"!=""]', 'Set', '__MOHCLASS=${QMOH}'));
				$ext->add($c, $exten, '', new ext_execif('$["${MOHCLASS}"!=""]', 'Set', 'CHANNEL(musicclass)=${MOHCLASS}'));

				$options = 't';
				if (isset($q['answered_elsewhere']) && $q['answered_elsewhere'] == '1'){
					$ext->add($c, $exten, '', new ext_set('QCANCELMISSED', 'C'));
				}
				if ($q['rtone'] == 1) {
					$qringopts = 'r';
				} else if ($q['rtone'] == 2) {
					$qringopts = 'R';
				} else {
					$qringopts = '';
				}
				if ($qringopts) {
					$ext->add($c, $exten, '', new ext_set('QRINGOPTS', $qringopts));
				}
				$qretry = $q['retry'] == 'none' ? 'n' : ' ';
				$ext->add($c, $exten, '', new ext_set('QRETRY', '${IF($[${LEN(${VQ_RETRY})}>0]?${VQ_RETRY}:' . $qretry . ')}'));
				$ext->add($c, $exten, '', new ext_set('VQ_RETRY', ''));

				if (strlen($q['gotocontinue']) > 0){
					$ext->add($c, $exten, '', new ext_set('VQ_CONTINUE', 'c'));
				}
				$ext->add($c, $exten, 'qoptions', new ext_set('QOPTIONS', '${IF($[${LEN(${VQ_OPTIONS})}>0]?${VQ_OPTIONS}:' . ($options != '' ? $options : ' ') . ')}${QCANCELMISSED}${QRINGOPTS}${QRETRY}${VQ_CONTINUE}'));
				$ext->add($c, $exten, '', new ext_set('VQ_OPTIONS', ''));

				// Set these up to be easily spliced into if we want to configure ability in queue modules
				//
				$ext->add($c, $exten, 'qgosub', new ext_set('QGOSUB', '${IF($[${LEN(${VQ_GOSUB})}>0]?${VQ_GOSUB}:${QGOSUB})}'));
				$ext->add($c, $exten, '', new ext_set('VQ_GOSUB', ''));
				$ext->add($c, $exten, 'qagi', new ext_set('QAGI', '${IF($[${LEN(${VQ_AGI})}>0]?${VQ_AGI}:${QAGI})}'));
				$ext->add($c, $exten, '', new ext_set('VQ_AGI', ''));
				$ext->add($c, $exten, 'qrule', new ext_set('QRULE', '${IF($[${LEN(${VQ_RULE})}>0]?${IF($["${VQ_RULE}"!="0"]?${VQ_RULE}: )}:${QRULE})}'));
				$ext->add($c, $exten, '', new ext_set('VQ_RULE', ''));
				$ext->add($c, $exten, 'qposition', new ext_set('QPOSITION', '${IF($[${LEN(${VQ_POSITION})}>0]?${VQ_POSITION}:${QPOSITION})}'));
				$ext->add($c, $exten, '', new ext_set('VQ_POSITION', ''));

				$record_mode = $q['monitor-format'] ? 'always' : 'dontcare';
				if ($q['monitor-format']) {
					$ext->add($c, $exten, '', new ext_set('__MIXMON_FORMAT', $q['monitor-format']));
				}

				if ($amp_conf['QUEUES_MIX_MONITOR']) {
					$monitor_options = '';
					if (isset($q['monitor_type']) && $q['monitor_type'] != '') {
						$monitor_options .= 'b';
					}
					if (isset($q['monitor_spoken']) && $q['monitor_spoken'] != 0) {
						$monitor_options .= 'V('.$q['monitor_spoken'].')';
					}
					if (isset($q['monitor_heard']) && $q['monitor_heard'] != 0) {
						$monitor_options .= 'v('.$q['monitor_heard'].')';
					}
					if ($monitor_options != '') {
						$ext->add($c, $exten, '', new ext_setvar('MONITOR_OPTIONS', $monitor_options ));
					}
				}
                
                $ext->add($c, $exten, '', new ext_gosub('1','s','sub-record-check',"q,$exten,$record_mode"));

				// Set CWIGNORE  if enabled so that busy agents don't have another line key ringing and
				// stalling the ACD.
				if ($q['cwignore'] == 1 || $q['cwignore'] == 2 ) {
					$ext->add($c, $exten, '', new ext_setvar('__CWIGNORE', 'TRUE'));
				}
				if ($q['use_queue_context']) {
					$ext->add($c, $exten, '', new ext_setvar('__CFIGNORE', 'TRUE'));
					$ext->add($c, $exten, '', new ext_setvar('__FORWARD_CONTEXT', 'block-cf'));
				}
				$agentannounce_id = (isset($q['agentannounce_id'])?$q['agentannounce_id']:'');
				if ($agentannounce_id) {
					$agentannounce = recordings_get_file($agentannounce_id);
				} else {
					$agentannounce = ' ';
				}
			
				if ($q['callconfirm'] == 1) {
					$ext->add($c, $exten, '', new ext_setvar('__FORCE_CONFIRM', '${CHANNEL}'));
					if ($amp_conf['AST_FUNC_SHARED']) {
						$ext->add($c, $exten, '', new ext_setvar('SHARED(ANSWER_STATUS)','NOANSWER'));
					}
					$ext->add($c, $exten, '', new ext_setvar('__CALLCONFIRMCID', '${CALLERID(number)}'));
					$callconfirm_id = (isset($q['callconfirm_id']))?$q['callconfirm_id']:'';
					if ($callconfirm_id) {	
						$callconfirm = recordings_get_file($callconfirm_id);
					} else {
						$callconfirm = ' ';
					}
					$ext->add($c, $exten, '', new ext_set('__ALT_CONFIRM_MSG', '${IF($[${LEN(${VQ_CONFIRMMSG})}>0]?${IF($["${VQ_CONFIRMMSG}"!="0"]?${VQ_CONFIRMMSG}: )}:' . $callconfirm . ')}'));
					$ext->add($c, $exten, '', new ext_set('VQ_CONFIRMMSG', ''));
				}
				$ext->add($c, $exten, '', new ext_execif('$["${QJOINMSG}"!=""' . $cplay . ']', 'Playback', '${QJOINMSG}, ' . $joinansw));
				$ext->add($c, $exten, '', new ext_queuelog($exten,'${UNIQUEID}','NONE','DID', '${FROM_DID}')); 

				$ext->add($c, $exten, '', new ext_set('QAANNOUNCE', '${IF($[${LEN(${VQ_AANNOUNCE})}>0]?${IF($["${VQ_AANNOUNCE}"!="0"]?${VQ_AANNOUNCE}: )}:' . $agentannounce . ')}'));
				$ext->add($c, $exten, '', new ext_set('VQ_AANNOUNCE', ''));
				$agnc = '${QAANNOUNCE}';

				$ext->add($c, $exten, '', new ext_set('QMAXWAIT', '${IF($[${LEN(${VQ_MAXWAIT})}>0]?${VQ_MAXWAIT}:' . ($q['maxwait'] != '' ? $q['maxwait'] : ' ') . ')}'));
				$ext->add($c, $exten, '', new ext_set('VQ_MAXWAIT', ''));

				$ext->add($c, $exten, '', new ext_set('QUEUENUM', $exten));
				$ext->add($c, $exten, '', new ext_set('QUEUEJOINTIME', '${EPOCH}'));

				$qmaxwait = '${QMAXWAIT}';
				$options = '${QOPTIONS}';
				$qagi = '${QAGI}';
				$qmacro = '';
				$qgosub = '${QGOSUB}';
				$qrule = '${QRULE}';
				$qposition = '${QPOSITION}';
				$qurl = '${QURL}';

				// Queue(queuename[,options[,URL[,announceoverride[,timeout[,AGI[,macro[,gosub[,rule[,position]]]]]]]]])
				//
				$ext->add($c, $exten, 'qcall', new ext_queue($exten, $options, $qurl, $agnc, $qmaxwait, $qagi, $qmacro, $qgosub, $qrule, $qposition));

				if($q['use_queue_context'] != '2') {
					$ext->add($c, $exten, '', new ext_macro('blkvm-clr'));
				}

				// Reset CALLFILENAME if QUEUE CONTINUE to avoid delete the file  (venturinog)
				$ext->add($c, $exten, '', new ext_execif('$["${QUEUESTATUS}"="CONTINUE"]','Set','CALLFILENAME=""'));

				// cancel any recording previously requested
				$ext->add($c, $exten, '', new ext_gosub('1','s','sub-record-cancel'));

				// If we are here, disable the NODEST as we want things to resume as normal
				$ext->add($c, $exten, '', new ext_setvar('__NODEST', ''));
				$ext->add($c, $exten, '', new ext_setvar('_QUEUE_PRIO', '0'));
			
				if ($q['callconfirm'] == 1) {
					if ($amp_conf['AST_FUNC_SHARED']) {
						$ext->add($c, $exten, '', new ext_setvar('SHARED(ANSWER_STATUS)', ''));
					}
					$ext->add($c, $exten, '', new ext_setvar('__FORCE_CONFIRM', ''));
					$ext->add($c, $exten, '', new ext_setvar('__ALT_CONFIRM_MSG', ''));				
				}

				if($monitor_options != '') {
					$ext->add($c, $exten, '', new ext_setvar('MONITOR_OPTIONS', ''));
				}
				if ($q['cwignore'] == 1 || $q['cwignore'] == 2 ) {
					$ext->add($c, $exten, '', new ext_setvar('__CWIGNORE', '')); 
				}
				if ($q['use_queue_context']) {
					$ext->add($c, $exten, '', new ext_setvar('__CFIGNORE', ''));
					$ext->add($c, $exten, '', new ext_setvar('__FORWARD_CONTEXT', 'from-internal'));
				}
				if ($qringopts) {
					$ext->add($c, $exten, '', new ext_set('QRINGOPTS', ''));
				}

				//VQ_DEST = str_replace(',','^',$vq['goto'])
				$ext->add($c, $exten, '', new ext_set('QDEST', '${VQ_DEST}'));
				$ext->add($c, $exten, '', new ext_set('VQ_DEST', ''));
				if (strlen($q['gotocontinue']) > 0){
					$ext->add($c, $exten, 'gotocontinue', new ext_gotoif('$["${QUEUESTATUS}"="CONTINUE"]',$q['gotocontinue']));
				}
				$ext->add($c, $exten, 'gotodest', new ext_gotoif('$["${QDEST}"=""]',$q['goto'],'${CUT(QDEST,^,1)},${CUT(QDEST,^,2)},${CUT(QDEST,^,3)}'));
			
				//dynamic agent login/logout
				if (trim($qregex) != '') {
					$ext->add($c, $exten."*", '', new ext_setvar('QREGEX', $qregex));
				}
				if ($amp_conf['GENERATE_LEGACY_QUEUE_CODES']) {
					if($q['use_queue_context'] == '2') {
						$ext->add($c, $exten."*", '', new ext_macro('agent-add',$exten.",".$q['password'].",EXTEN"));
					} else {
						$ext->add($c, $exten."*", '', new ext_macro('agent-add',$exten.",".$q['password']));
					}
					$ext->add($c, $exten."**", '', new ext_macro('agent-del',"$exten"));
				}
				if ($que_code != '') {
					$ext->add($c, $que_code.$exten, '', new ext_setvar('QUEUENO',$exten));
					$ext->add($c, $que_code.$exten, '', new ext_goto('start','s','app-queue-toggle'));
				}
				if ($que_pause_code != '') {
					$ext->add($c, $que_pause_code.$exten, '', new ext_gosub('1','s','app-queue-pause-toggle',$exten));
				}
				/* Trial Devstate */
				// Create Hints for Devices and Add Astentries for Users
				// Clean up the Members array
				if ($q['togglehint'] && $amp_conf['USEDEVSTATE'] && $que_code != '') {
					if (!isset($device_list)) {
				  	$device_list = core_devices_list("all", 'full', true);
					}
					if ($astman) {
						if (($dynmemberonly = strtolower($astman->database_get('QPENALTY/'.$exten,'dynmemberonly')) == 'yes') == true) {
							$get=$astman->database_show('QPENALTY/'.$exten.'/agents');
							if($get){
								$mem = array();
								foreach($get as $key => $value){
									$key=explode('/',$key);
									$mem[$key[4]]=$value;
								}
							}
						}
					} else {
						fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
					}
					foreach ($device_list as $device) {
						if (
							(!$dynmemberonly || $device['devicetype'] == 'adhoc' || isset($mem[$device['user']]))
							&& ($device['tech'] == 'sip' || $device['tech'] == 'iax2')
							) {
							$ext->add($c, $que_code.$device['id'].'*'.$exten, '', new ext_setvar('QUEUENO',$exten));
							$ext->add($c, $que_code.$device['id'].'*'.$exten, '', new ext_goto('start','s','app-queue-toggle'));
							$ext->addHint($c, $que_code.$device['id'].'*'.$exten, "Custom:QUEUE".$device['id'].'*'.$exten);
						}
					}
				}

				// Add routing vector to direct which context call should go
				//
				$agent_context = isset($q['use_queue_context']) && $q['use_queue_context'] && isset($queue_context) ? $queue_context : 'from-internal';
				switch ($q['use_queue_context']) {
					case 1:
						$agent_context = $from_queue_exten_internal;
						break;
					case 2:
						$agent_context = $from_queue_exten_only;
						break;
					case 0:
					default:
						$agent_context = 'from-internal';
						break;
				}
				$ext->add('from-queue', $exten, '', new ext_goto('1','${QAGENT}',$agent_context));

				foreach ($q['member'] as $qm) {
					if (strtoupper(substr($qm,0,1)) == 'L') {
						$tm = preg_replace("/[^0-9#\,*]/", "", $qm);
						$tma = explode(',',$tm);
						$qmembers[$exten][] = $tma[0];
					}
				}

			}
			
			if (!$amp_conf['DYNAMICHINTS'] && ($que_code != '' || $que_pause_code != '' || $que_callers_code != '')) {
				$qpenalty=$astman->database_show('QPENALTY');
				$qc = array();
				foreach(array_keys($qpenalty) as $key) {
					$key = explode('/', $key);
					if ($key[3] == 'agents') {
						$qc[$key[4]][] = $key[2]; }
				}

				// Make sure we have all the devices
				//
				if (!isset($device_list)) {
					$device_list = core_devices_list("all", 'full', true);
				}
			}

			// Create *45 all queue toggle
			//
			if ($que_code != '') {
				$ext->add($c, $que_code, '', new ext_goto('start','s','app-all-queue-toggle'));

				// create a generic one for any phones that don't get a specific one created since we only
				// create them for phones we know have queues but who knows what is provisioned on the phones
				//
				$ext->add($c, '_' . $que_code . '*X.', '', new ext_goto('start','s','app-all-queue-toggle'));

				// generate with #exec if we are using dynamic hints
				//
				if ($amp_conf['DYNAMICHINTS']) {
					$ext->addExec($c,$amp_conf['AMPBIN'].'/generate_queue_hints.php '.$que_code);
				} else {
					foreach ($device_list as $device) {
						if ($device['tech'] == 'sip' || $device['tech'] == 'iax2') {
							$ext->add($c, $que_code . '*' . $device['id'], '', new ext_goto('start','s','app-all-queue-toggle'));
							if ($device['user'] != '' &&  isset($qc[$device['user']])) {
								$hlist = 'Custom:QUEUE' . $device['id'] . '*' . implode('&Custom:QUEUE' . $device['id'] . '*', $qc[$device['user']]);
								$ext->addHint($c, $que_code . '*' . $device['id'], $hlist);
							}
						}
					}
				}
			}

			// Add the static members now since so far it only has dynamic members
			foreach ($qmembers as $q => $mems) {
				foreach ($mems as $m) {
					// If $m is not in qc already then add them, thus avoiding duplicates
					if (!isset($qc[$m]) || !in_array($q, $qc[$m])) {
						$qc[$m][] = (string)$q;
					}
				}
			}

			// Create *46 codes/hints
			//
			if ($que_pause_code != '') {
				$ext->add($c, $que_pause_code, '', new ext_goto('1','s','app-all-queue-pause-toggle'));

				// create a generic one for any phones that don't get a specific one created since we only
				// create them for phones we know have queues but who knows what is provisioned on the phones
				//
				$ext->add($c, '_' . $que_pause_code . '*X.', '', new ext_goto('1','s','app-all-queue-pause-toggle'));

				foreach ($device_list as $device) {
					if ($device['user'] != '') {
						$pause_all_hints = array();
						if (isset($qc[$device['user']])) foreach($qc[$device['user']] as $q) {
							$ext->add($c, $que_pause_code . '*' . $device['id'] . '*' . $q, '', new ext_gosub('1','s','app-queue-pause-toggle',$q));
							if (!$amp_conf['DYNAMICHINTS'] && ($device['tech'] == 'sip' || $device['tech'] == 'iax2')) {
								$hint = "qpause:$q:Local/{$device['user']}@from-queue/n";
								$ext->addHint($c, $que_pause_code . '*' . $device['id'] . '*' . $q, $hint);
								$pause_all_hints[] = $hint;
							}
						}
						$ext->add($c, $que_pause_code . '*' . $device['id'], '', new ext_goto('1','s','app-all-queue-pause-toggle'));
						if (!empty($pause_all_hints)) {
							$ext->addHint($c, $que_pause_code . '*' . $device['id'], implode('&', $pause_all_hints));
						}
					}
				}
			}
			// Create *47 codes/hints
			//
			if ($que_callers_code != '') {
				$id = "app-queue-caller-count";
				$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

				$ext->add($id, 's', '', new ext_answer());
				$ext->add($id, 's', '', new ext_wait(1));

				$ext->add($id, 's', '', new ext_setvar('QUEUES', '${ARG1}'));
				$ext->add($id, 's', '', new ext_setvar('COUNT', '0'));

				$ext->add($id, 's', '', new ext_setvar('LOOPCNT', '${FIELDQTY(QUEUES,&)}'));
				$ext->add($id, 's', '', new ext_setvar('ITER', '1'));
				$ext->add($id, 's', 'begin1', new ext_setvar('QUEUE', '${CUT(QUEUES,&,${ITER})}'));

				$ext->add($id, 's', '', new ext_setvar('COUNT', '$[${COUNT} + ${QUEUE_WAITING_COUNT(${QUEUE})}]'));

				$ext->add($id, 's', 'end1', new ext_setvar('ITER', '$[${ITER} + 1]'));
				$ext->add($id, 's', '', new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin1'));

				$ext->add($id, 's', '', new ext_saynumber('${COUNT}'));
				$ext->add($id, 's', '', new ext_playback('queue-quantity2'));
				$ext->add($id, 's', '', new ext_return());


				$userQueues = array();
				if ($bmo && $bmo->Cos && $bmo->Cos->isLicensed()) {
					$cos = $bmo->Cos;
				} else if (function_exists('cos_islicenced') && cos_islicenced()) {
					$cos = Cos::create();
				}

				if ($cos) {
					$allCos = $cos->getAllCos();
					foreach ($allCos as $cos_name) {
						$all = $cos->getAll($cos_name);

						foreach ($all['members'] as $key => $val) {
							$userQueues[$key] = ($userQueues[$key] ? $userQueues[$key] + $all['queuesallow'] : $all['queuesallow']);
						}
					}
				}

				foreach ($device_list as $device) {
					if ($device['user'] != '') {
						$callers_all = array();
						$callers_all_hints = array();

						foreach ($qlist as $item) {
							if (count($userQueues) > 1 && (!isset($userQueues[$device['user']]) || !isset($userQueues[$device['user']][$item[0]]))) {
								continue;
							}

							if (isset($qc[$device['user']]) && in_array($item[0], $qc[$device['user']], true)) {
								$callers_all[] = $item[0];

								$ext->add($c, $que_callers_code . '*' . $device['id'] . '*' . $item[0], '', new ext_gosub('1', 's', 'app-queue-caller-count', $item[0]));
								$ext->add($c, $que_callers_code . '*' . $device['id'] . '*' . $item[0], '', new ext_hangup());

								if ($ast_ge_11 && !$amp_conf['DYNAMICHINTS'] && ($device['tech'] == 'sip' || $device['tech'] == 'iax2')) {
									$hint = "Queue:$item[0]";
									$ext->addHint($c, $que_callers_code . '*' . $device['id'] . '*' . $item[0], $hint);
									$callers_all_hints[] = $hint;
								}
							}
						}

						if (!empty($callers_all_hints)) {
							$ext->add($c, $que_callers_code . '*' . $device['id'], '', new ext_gosub('1', 's', 'app-queue-caller-count', implode('&', $callers_all)));
							$ext->add($c, $que_callers_code . '*' . $device['id'], '', new ext_hangup());

							$ext->addHint($c, $que_callers_code . '*' . $device['id'], implode('&', $callers_all_hints));
						}
					}
				}
			}

			// We need to have a hangup here, if call is ended by the caller during Playback it will end in the
			// h context and do a proper hangup and clean the blkvm if set, see #4671
			$ext->add($c, 'h', '', new ext_macro('hangupcall'));
			
			
			// NODEST will be the queue that this came from, so we will vector though an entry to determine the context the
			// agent should be delivered to. All queue calls come here, this decides if the should go direct to from-internal
			// or indirectly through from-queue-exten-only to trap extension calls and avoid their follow-me, etc.
			//
			$ext->add('from-queue', '_.', '', new ext_setvar('QAGENT','${EXTEN}'));
			$ext->add('from-queue', '_.', '', new ext_goto('1','${NODEST}'));

			$ext->addInclude($from_queue_exten_internal,$from_queue_exten_only);
			$ext->addInclude($from_queue_exten_internal,'from-internal');
			$ext->add($from_queue_exten_internal, 'foo', '', new ext_noop('bar'));

			/* create a context, from-queue-exten-only, that can be used for queues that want behavir similar to
			 * ringgroup where only the agent's phone will be rung, no follow-me will be pursued.
			 */
			$userlist = core_users_list();
			if (is_array($userlist)) {
				foreach($userlist as $item) {
					$ext->add($from_queue_exten_only, $item[0], '', new ext_set('RingGroupMethod', 'none'));

					$ext->add($from_queue_exten_only, $item[0], '', new ext_set('QDOPTS', '${IF($["${CALLER_DEST}"!=""]?g)}${IF($["${AGENT_DEST}"!=""]?F(${AGENT_DEST}))}'));

					$ext->add($from_queue_exten_only, $item[0], 'checkrecord', new ext_gosub('1','s','sub-record-check',"exten," . $item[0]));
					if ($has_extension_state) {
						$ext->add($from_queue_exten_only, $item[0], '', new ext_macro('dial-one',',${DIAL_OPTIONS}${QDOPTS},'.$item[0]));
					} else {
						$ext->add($from_queue_exten_only, $item[0], '', new ext_macro('dial',',${DIAL_OPTIONS}${QDOPTS},'.$item[0]));
					}
					$ext->add($from_queue_exten_only, $item[0], '', new ext_gotoif('$["${CALLER_DEST}"!=""]','${CUT(CALLER_DEST,^,1)},${CUT(CALLER_DEST,^,2)},${CUT(CALLER_DEST,^,3)}'));
 					$ext->add($from_queue_exten_only, $item[0], '', new ext_hangup());
				}
 				$ext->add($from_queue_exten_only, 'h', '', new ext_macro('hangupcall'));
			}

			/*
			 * Adds a dynamic agent/member to a Queue
			 * Prompts for call-back number - in not entered, uses CIDNum
			 */

			if ($amp_conf['GENERATE_LEGACY_QUEUE_CODES']) {

			$c = 'macro-agent-add';
			$exten = 's';
			
			$ext->add($c, $exten, '', new ext_wait(1));
			$ext->add($c, $exten, '', new ext_set('QUEUENO', '${ARG1}'));
			$ext->add($c, $exten, '', new ext_macro('user-callerid', 'SKIPTTL'));
			$ext->add($c, $exten, 'a3', new ext_read('CALLBACKNUM', 'agent-login'));  // get callback number from user
			$ext->add($c, $exten, '', new ext_gotoif('$[${LEN(${CALLBACKNUM})}=0]','a5','a7'));  // if user just pressed # or timed out, use cidnum
			$ext->add($c, $exten, 'a5', new ext_set('CALLBACKNUM', '${IF($[${LEN(${AMPUSER})}=0]?${CALLERID(number)}:${AMPUSER})}'));

			if ($ast_ge_14_25) {
				$ext->add($c, $exten, '', new ext_set('THISDEVICE', '${DB(DEVICE/${REALCALLERIDNUM}/dial)}'));
			}
			$ext->add($c, $exten, '', new ext_gotoif('$["${CALLBACKNUM}" = ""]', 'a3'));  // if still no number, start over
			$ext->add($c, $exten, 'a7', new ext_gotoif('$["${CALLBACKNUM}" = "${QUEUENO}"]', 'invalid'));  // Error, they put in the queue number

			// If this is an extension only queue then EXTEN is passed as ARG3 and we make sure this is a valid extension being entered
			$ext->add($c, $exten, '', new ext_gotoif('$["${ARG3}" = "EXTEN" & ${DB_EXISTS(AMPUSER/${CALLBACKNUM}/cidname)} = 0]', 'invalid'));

			// If this is a restricted dynamic agent queue then check to make sure they are allowed
			$ext->add($c, $exten, '', new ext_gotoif('$["${DB(QPENALTY/${QUEUENO}/dynmemberonly)}" = "yes" & ${DB_EXISTS(QPENALTY/${QUEUENO}/agents/${CALLBACKNUM})} != 1]', 'invalid'));

			$ext->add($c, $exten, '', new ext_execif('$["${QREGEX}" != ""]', 'GotoIf', '$["${REGEX("${QREGEX}" ${CALLBACKNUM})}" = "0"]?invalid'));
			$ext->add($c, $exten, '', new ext_execif('$["${ARG2}" != ""]', 'Authenticate', '${ARG2}'));

			if ($amp_conf['USEDEVSTATE']) {
				$ext->add($c, $exten, '', new ext_set('STATE', 'INUSE'));
				$ext->add($c, $exten, '', new ext_gosub('1', 'sstate', 'app-queue-toggle'));
			}

			if ($ast_ge_18 || $amp_conf['USEQUEUESTATE']) {
			  $ext->add($c, $exten, '', new ext_execif('$[${DB_EXISTS(AMPUSER/${CALLBACKNUM}/cidname)} = 1 & "${DB(AMPUSER/${CALLBACKNUM}/queues/qnostate)}" != "ignorestate"]', 'AddQueueMember', '${QUEUENO},Local/${CALLBACKNUM}@from-queue/n,${DB(QPENALTY/${QUEUENO}/agents/${CALLBACKNUM})},,${DB(AMPUSER/${CALLBACKNUM}/cidname)},hint:${CALLBACKNUM}@ext-local'));
			  $ext->add($c, $exten, '', new ext_execif('$[${DB_EXISTS(AMPUSER/${CALLBACKNUM}/cidname)} = 1 & "${DB(AMPUSER/${CALLBACKNUM}/queues/qnostate)}" = "ignorestate"]', 'AddQueueMember', '${QUEUENO},Local/${CALLBACKNUM}@from-queue/n,${DB(QPENALTY/${QUEUENO}/agents/${CALLBACKNUM})},,${DB(AMPUSER/${CALLBACKNUM}/cidname)}'));
			  $ext->add($c, $exten, '', new ext_execif('$[${DB_EXISTS(AMPUSER/${CALLBACKNUM}/cidname)} = 0]', 'AddQueueMember', '${QUEUENO},Local/${CALLBACKNUM}@from-queue/n,${DB(QPENALTY/${QUEUENO}/agents/${CALLBACKNUM})}'));
	      } else if ($ast_ge_14_25) {
				  $ext->add($c, $exten, '', new ext_set('THISDEVICE', '${IF($[${LEN(${THISDEVICE})}=0]?${DB(DEVICE/${CUT(DB(AMPUSER/${CALLBACKNUM}/device),&,1)}/dial)}:${THISDEVICE})}'));
				  $ext->add($c, $exten, '', new ext_execif('$[${LEN(${THISDEVICE})}!=0 & "${DB(AMPUSER/${CALLBACKNUM}/queues/qnostate)}" != "ignorestate"]', 'AddQueueMember', '${QUEUENO},Local/${CALLBACKNUM}@from-queue/n,${DB(QPENALTY/${QUEUENO}/agents/${CALLBACKNUM})},,${DB(AMPUSER/${CALLBACKNUM}/cidname)},${THISDEVICE}'));
				  $ext->add($c, $exten, '', new ext_execif('$[${LEN(${THISDEVICE})}!=0 & "${DB(AMPUSER/${CALLBACKNUM}/queues/qnostate)}" = "ignorestate"]', 'AddQueueMember', '${QUEUENO},Local/${CALLBACKNUM}@from-queue/n,${DB(QPENALTY/${QUEUENO}/agents/${CALLBACKNUM})},,${DB(AMPUSER/${CALLBACKNUM}/cidname)}'));
				  $ext->add($c, $exten, '', new ext_execif('$[${LEN(${THISDEVICE})}=0]', 'AddQueueMember', '${QUEUENO},Local/${CALLBACKNUM}@from-queue/n,${DB(QPENALTY/${QUEUENO}/agents/${CALLBACKNUM})}'));
			} else {
				$ext->add($c, $exten, 'a9', new ext_addqueuemember('${QUEUENO}', 'Local/${CALLBACKNUM}@from-queue/n,${DB(QPENALTY/${QUEUENO}/agents/${CALLBACKNUM})}'));
			}
			$ext->add($c, $exten, '', new ext_userevent('Agentlogin', 'Agent: ${CALLBACKNUM}'));
			$ext->add($c, $exten, '', new ext_wait(1));
			$ext->add($c, $exten, '', new ext_playback('agent-loginok&with&extension'));
			$ext->add($c, $exten, '', new ext_saydigits('${CALLBACKNUM}'));
			$ext->add($c, $exten, '', new ext_hangup());
			$ext->add($c, $exten, '', new ext_macroexit());
			$ext->add($c, $exten, 'invalid', new ext_playback('pbx-invalid'));
			$ext->add($c, $exten, '', new ext_goto('a3'));

			/*
			 * Removes a dynamic agent/member from a Queue
			 * Prompts for call-back number - in not entered, uses CIDNum 
			 */

			$c = 'macro-agent-del';
			
			$ext->add($c, $exten, '', new ext_wait(1));
			$ext->add($c, $exten, '', new ext_set('QUEUENO', '${ARG1}'));
			$ext->add($c, $exten, '', new ext_macro('user-callerid', 'SKIPTTL'));
			$ext->add($c, $exten, 'a3', new ext_read('CALLBACKNUM', 'agent-logoff'));  // get callback number from user
			$ext->add($c, $exten, '', new ext_gotoif('$[${LEN(${CALLBACKNUM})}=0]','a5','a7'));  // if user just pressed # or timed out, use cidnum
			$ext->add($c, $exten, 'a5', new ext_set('CALLBACKNUM', '${IF($[${LEN(${AMPUSER})}=0]?${CALLERID(number)}:${AMPUSER})}'));
			$ext->add($c, $exten, '', new ext_gotoif('$["${CALLBACKNUM}" = ""]', 'a3'));  // if still no number, start over

			if ($amp_conf['USEDEVSTATE']) {
				$ext->add($c, $exten, '', new ext_set('STATE', 'NOT_INUSE'));
				$ext->add($c, $exten, '', new ext_gosub('1', 'sstate', 'app-queue-toggle'));
			}

			// remove from both contexts in case left over dynamic agents after an upgrade
			$ext->add($c, $exten, 'a7', new ext_removequeuemember('${QUEUENO}', 'Local/${CALLBACKNUM}@from-queue/n'));
			$ext->add($c, $exten, '', new ext_removequeuemember('${QUEUENO}', 'Local/${CALLBACKNUM}@from-internal/n'));
			$ext->add($c, $exten, '', new ext_userevent('RefreshQueue'));
			$ext->add($c, $exten, '', new ext_wait(1));
			$ext->add($c, $exten, '', new ext_playback('agent-loggedoff'));
			$ext->add($c, $exten, '', new ext_hangup());

			} // GENERATE_LEGACY_QUEUE_CODES
		break;
	}
}

// TODO: need better sound files recorded for this
//
function app_all_queue_pause_toggle() {
	global $ext;
	global $amp_conf;

	$c = "app-all-queue-pause-toggle"; // The context to be included
	$e = 's';

	$ext->add($c, $e, 'start', new ext_answer(''));
	$ext->add($c, $e, '', new ext_wait('1'));
	$ext->add($c, $e, '', new ext_macro('user-callerid'));
	$ext->add($c, $e, '', new ext_agi('queue_devstate.agi,toggle-pause-all,${AMPUSER}'));
	$ext->add($c, $e, '', new ext_playback('dictate/pause&${IF($[${TOGGLEPAUSED}]?activated:de-activated)}'));
	$ext->add($c, $e, '', new ext_macro('hangupcall'));
}

function app_queue_pause_toggle() {
	global $ext;
	global $amp_conf;

	$c = "app-queue-pause-toggle"; // The context to be included
	$e = 's';

	$ext->add($c, $e, 'start', new ext_answer(''));
	$ext->add($c, $e, '', new ext_wait('1'));
	$ext->add($c, $e, '', new ext_macro('user-callerid'));
	$ext->add($c, $e, '', new ext_set('MEMBR', 'Local/${AMPUSER}@from-queue/n'));
	$ext->add($c, $e, '', new ext_set('PAUSE_STATE', '${QUEUE_MEMBER(${ARG1},paused,${MEMBR})}'));
	$ext->add($c, $e, '', new ext_set('QUEUE_MEMBER(${ARG1},paused,${MEMBR})', '${IF($[${PAUSE_STATE}]?0:1)}'));
	$ext->add($c, $e, '', new ext_playback('dictate/pause&${IF($[${PAUSE_STATE}]?de-activated:activated)}'));
	$ext->add($c, $e, '', new ext_execif('$[${ARG2}]', 'Return'));
	$ext->add($c, $e, '', new ext_macro('hangupcall'));
}

function queue_app_all_toggle() {
	global $ext;
	global $amp_conf;

	$c = "app-all-queue-toggle"; // The context to be included
	$e = 's';

	$ext->add($c, $e, 'start', new ext_answer(''));
	$ext->add($c, $e, '', new ext_wait('1'));
	$ext->add($c, $e, '', new ext_macro('user-callerid'));
	$ext->add($c, $e, '', new ext_agi('queue_devstate.agi,getall,${AMPUSER}'));
	$ext->add($c, $e, '', new ext_gotoif('$["${QUEUESTAT}" = "NOQUEUES"]', 'skip'));
	$ext->add($c, $e, '', new ext_set('TOGGLE_MACRO', '${IF($["${QUEUESTAT}"="LOGGEDOUT"]?toggle-add-agent:toggle-del-agent)}'));
	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($c, $e, '', new ext_set('STATE', '${IF($["${QUEUESTAT}"="LOGGEDOUT"]?INUSE:NOT_INUSE)}'));
	}
	$ext->add($c, $e, '', new ext_set('LOOPCNTALL', '${FIELDQTY(USERQUEUES,-)}'));
	$ext->add($c, $e, '', new ext_set('ITERALL', '1'));
	$ext->add($c, $e, 'begin', new ext_set('QUEUENO', '${CUT(USERQUEUES,-,${ITERALL})}'));
	$ext->add($c, $e, '', new ext_set('ITERALL', '$[${ITERALL}+1]'));
	$ext->add($c, $e, '', new ext_macro('${TOGGLE_MACRO}'));
	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($c, $e, '', new ext_gosub('1', 'sstate', 'app-queue-toggle'));
	}
	$ext->add($c, $e, '', new ext_gotoif('$[${ITERALL} <= ${LOOPCNTALL}]', 'begin'));
	$ext->add($c, $e, 'skip', new ext_execif('$["${QUEUESTAT}"="LOGGEDIN" | "${QUEUESTAT}"="NOQUEUES"]', 'Playback', 'agent-loggedoff'));
	$ext->add($c, $e, '', new ext_execif('$["${QUEUESTAT}"="LOGGEDOUT"]', 'Playback', 'agent-loginok'));
	$ext->add($c, $e, '', new ext_execif('$["${QUEUESTAT}"="LOGGEDOUT"]', 'SayDigits', '${AMPUSER}'));
	$ext->add($c, $e, '', new ext_macro('hangupcall'));
}

/* Trial DEVSTATE */
function queue_app_toggle() {
	global $ext;
	global $amp_conf;
	global $version;

	$id = "app-queue-toggle"; // The context to be included
	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$c = 's';

	$ext->add($id, $c, 'start', new ext_answer(''));
	$ext->add($id, $c, '', new ext_wait('1'));
	$ext->add($id, $c, '', new ext_macro('user-callerid'));
	$ext->add($id, $c, '', new ext_setvar('QUEUESTAT', 'LOGGEDOUT'));
	$ext->add($id, $c, '', new ext_agi('queue_devstate.agi,getqueues,${AMPUSER}'));

	$ext->add($id, $c, '', new ext_gotoif('$["${QUEUESTAT}" = "LOGGEDOUT"]', 'activate'));
	$ext->add($id, $c, '', new ext_gotoif('$["${QUEUESTAT}" = "LOGGEDIN"]', 'deactivate'));
	$ext->add($id, $c, '', new ext_gotoif('$["${QUEUESTAT}" = "STATIC"]', 'static','end'));
	$ext->add($id, $c, 'deactivate', new ext_noop('Agent Logged out'));
	$ext->add($id, $c, '', new ext_macro('toggle-del-agent'));
	$logout_label = 'logout';
	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($id, $c, $logout_label, new ext_setvar('STATE', 'NOT_INUSE'));
		$ext->add($id, $c, '', new ext_gosub('1', 'sstate'));
		$logout_label = '';
	}
	$ext->add($id, $c, $logout_label, new ext_playback('agent-loggedoff'));
	$ext->add($id, $c, '', new ext_macro('hangupcall'));

	$ext->add($id, $c, 'activate', new ext_noop('Agent Logged In'));
	$ext->add($id, $c, '', new ext_macro('toggle-add-agent'));
	$ext->add($id, $c, '', new ext_gotoif('$["${QAGENT_UNAUTHORIZED}"="1"]', 'logout'));

	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($id, $c, '', new ext_setvar('STATE', 'INUSE'));
		$ext->add($id, $c, '', new ext_gosub('1', 'sstate'));
	}
	$ext->add($id, $c, '', new ext_playback('agent-loginok'));
	$ext->add($id, $c, '', new ext_saydigits('${CALLBACKNUM}'));
	$ext->add($id, $c, '', new ext_macro('hangupcall'));

	$ext->add($id, $c, 'static', new ext_noop('User is a Static Agent'));
	if ($amp_conf['USEDEVSTATE']) {
		$ext->add($id, $c, '', new ext_setvar('STATE', 'INUSE'));
		$ext->add($id, $c, '', new ext_gosub('1', 'sstate'));
	}
	$ext->add($id, $c, '', new ext_playback('agent-loginok'));
	$ext->add($id, $c, '', new ext_macro('hangupcall'));

	if ($amp_conf['USEDEVSTATE']) {
		$c = 'sstate';
		$ext->add($id, $c, '', new ext_dbget('DEVICES','AMPUSER/${AMPUSER}/device'));
		$ext->add($id, $c, '', new ext_gotoif('$["${DEVICES}" = "" ]', 'return'));
		$ext->add($id, $c, '', new ext_setvar('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '1'));
		$ext->add($id, $c, 'begin', new ext_setvar($amp_conf['AST_FUNC_DEVICE_STATE'].'(Custom:QUEUE${CUT(DEVICES,&,${ITER})}*${QUEUENO})','${STATE}'));
		$ext->add($id, $c, '', new ext_setvar('ITER', '$[${ITER} + 1]'));
		$ext->add($id, $c, '', new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin'));
		$ext->add($id, $c, 'return', new ext_return());
		}
}

function queue_agent_add_toggle() {
	global $ext, $amp_conf, $version;

	$ast_ge_14_25 = version_compare($version,'1.4.25','ge');
	$ast_ge_18 = version_compare($version,'1.8','ge');
	$id = "macro-toggle-add-agent"; // The context to be included

	$c = 's';

	$ext->add($id, $c, '', new ext_wait('1'));
	$ext->add($id, $c, '', new ext_macro('user-callerid,SKIPTTL'));
	$ext->add($id, $c, '', new ext_setvar('CALLBACKNUM','${AMPUSER}'));
	//TODO: check if it's not a user for some reason and abort?
	$ext->add($id, $c, '', new ext_gotoif('$["${DB(QPENALTY/${QUEUENO}/dynmemberonly)}" = "yes" & ${DB_EXISTS(QPENALTY/${QUEUENO}/agents/${CALLBACKNUM})} != 1]', 'invalid'));
	if ($ast_ge_18 || $amp_conf['USEQUEUESTATE']) {
		$ext->add($id, $c, '', new ext_execif('$["${DB(AMPUSER/${CALLBACKNUM}/queues/qnostate)}" != "ignorestate"]', 'AddQueueMember', '${QUEUENO},Local/${CALLBACKNUM}@from-queue/n,${DB(QPENALTY/${QUEUENO}/agents/${CALLBACKNUM})},,${DB(AMPUSER/${CALLBACKNUM}/cidname)},hint:${CALLBACKNUM}@ext-local'));
		$ext->add($id, $c, '', new ext_execif('$["${DB(AMPUSER/${CALLBACKNUM}/queues/qnostate)}" = "ignorestate"]', 'AddQueueMember', '${QUEUENO},Local/${CALLBACKNUM}@from-queue/n,${DB(QPENALTY/${QUEUENO}/agents/${CALLBACKNUM})},,${DB(AMPUSER/${CALLBACKNUM}/cidname)}'));

	} else if ($ast_ge_14_25) {
		$ext->add($id, $c, '', new ext_execif('$["${DB(AMPUSER/${CALLBACKNUM}/queues/qnostate)}" != "ignorestate"]', 'AddQueueMember', '${QUEUENO},Local/${CALLBACKNUM}@from-queue/n,${DB(QPENALTY/${QUEUENO}/agents/${CALLBACKNUM})},,${DB(AMPUSER/${CALLBACKNUM}/cidname)},${DB(DEVICE/${REALCALLERIDNUM}/dial)}'));
		$ext->add($id, $c, '', new ext_execif('$["${DB(AMPUSER/${CALLBACKNUM}/queues/qnostate)}" = "ignorestate"]', 'AddQueueMember', '${QUEUENO},Local/${CALLBACKNUM}@from-queue/n,${DB(QPENALTY/${QUEUENO}/agents/${CALLBACKNUM})},,${DB(AMPUSER/${CALLBACKNUM}/cidname)}'));
	} else {
		$ext->add($id, $c, '', new ext_addqueuemember('${QUEUENO}','Local/${CALLBACKNUM}@from-queue/n,${DB(QPENALTY/${QUEUENO}/agents/${CALLBACKNUM})}'));
	}

	$ext->add($id, $c, '', new ext_userevent('AgentLogin','Agent: ${CALLBACKNUM}'));
	$ext->add($id, $c, '', new ext_queuelog('${QUEUENO}','MANAGER','${IF($[${LEN(${AMPUSERCIDNAME})}>0]?${AMPUSERCIDNAME}:${AMPUSER})}','ADDMEMBER'));
	$ext->add($id, $c, '', new ext_macroexit());
	$ext->add($id, $c, 'invalid', new ext_playback('pbx-invalid'));
	$ext->add($id, $c, '', new ext_set('QAGENT_UNAUTHORIZED','1'));
	$ext->add($id, $c, '', new ext_macroexit());
}

function queue_agent_del_toggle() {
	global $ext;
	global $amp_conf;

	$id = "macro-toggle-del-agent"; // The context to be included

	$c = 's';

	$ext->add($id, $c, '', new ext_wait('1'));
	$ext->add($id, $c, '', new ext_macro('user-callerid,SKIPTTL'));
	$ext->add($id, $c, '', new ext_setvar('CALLBACKNUM','${AMPUSER}'));
	$ext->add($id, $c, '', new ext_removequeuemember('${QUEUENO}','Local/${CALLBACKNUM}@from-queue/n'));
	$ext->add($id, $c, '', new ext_removequeuemember('${QUEUENO}','Local/${CALLBACKNUM}@from-internal/n'));
	$ext->add($id, $c, '', new ext_userevent('RefreshQueue'));
	$ext->add($id, $c, '', new ext_queuelog('${QUEUENO}','MANAGER','${IF($[${LEN(${AMPUSERCIDNAME})}>0]?${AMPUSERCIDNAME}:${AMPUSER})}','REMOVEMEMBER'));
	$ext->add($id, $c, '', new ext_macroexit());
}

?>
