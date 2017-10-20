<?php /* $Id$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
class conferences_conf {
	private static $obj;

	// IssabelPBX magic ::create() call
	public static function create() {
		if (!isset(self::$obj))
			self::$obj = new conferences_conf();

		return self::$obj;
	}


  function __construct() {
		$this->_confbridge['general'] = array();
		$this->_confbridge['user'] = array();
		$this->_confbridge['user']['default_user'] = array();
		$this->_confbridge['bridge'] = array();
		$this->_confbridge['bridge']['default_bridge'] = array();
		$this->_confbridge['menu'] = array();

		self::$obj = $this;
	}

	// return the filename to write
	function get_filename() {
		global $amp_conf;

		$files = array(
			'meetme_additional.conf',
		 	'confbridge_additional.conf',
		);

		return $files;
	}

	function addMeetme($room, $userpin, $adminpin='') {
		$this->_meetmes[$room] = $userpin.($adminpin != '' ? ','.$adminpin : '');
	}

	function addConfUser($section, $key, $value) {
		$this->_confbridge['user'][$section][$key] = $value;
	}

	function addConfBridge($section, $key, $value) {
		$this->_confbridge['bridge'][$section][$key] = $value;
	}

	function addConfMenu($section, $key, $value) {
		$this->_confbridge['menu'][$section][$key] = $value;
	}

	// return the output that goes in the file
	function generateConf($file) {
		global $amp_conf;
		global $version;

		$output = "";

		switch ($file) {
		case 'meetme_additional.conf':
			if ($amp_conf['ASTCONFAPP'] == 'app_meetme' && !empty($this->_meetmes)) {
				foreach (array_keys($this->_meetmes) as $meetme) {
					$output .= 'conf => '.$meetme.",".$this->_meetmes[$meetme]."\n";
				}
			}
		break;
		case 'confbridge_additional.conf':
			if ($amp_conf['ASTCONFAPP'] != 'app_confbridge' || version_compare($version, '10', 'lt')) {
				break;
			}
			if (empty($this->_confbridge['general'])) {
				$output .= "[general]\n";
				$output .= ";This section reserved for future use\n";
				$output .= "\n";
			}
			// Default if nothing configured
			if (empty($this->_confbridge['menu']['admin_menu'])) {
				$this->_confbridge['menu']['admin_menu'] = array(
					'*'  => 'playback_and_continue(conf-adminmenu)',
					'*1' => 'toggle_mute',
					'1'  => 'toggle_mute',
					'*2' => 'admin_toggle_conference_lock',
					'2'  => 'admin_toggle_conference_lock',
					'*3' => 'admin_kick_last',
					'3'  => 'admin_kick_last',
					'*4' => 'decrease_listening_volume',
					'4'  => 'decrease_listening_volume',
					'*6' => 'increase_listening_volume',
					'6'  => 'increase_listening_volume',
					'*7' => 'decrease_talking_volume',
					'7'  => 'decrease_talking_volume',
					'*8' => 'no_op',
					'8'  => 'no_op',
					'*9' => 'increase_talking_volume',
					'9'  => 'increase_talking_volume',
				);
			}
			// Default if nothing configured
			if (empty($this->_confbridge['menu']['user_menu'])) {
				$this->_confbridge['menu']['user_menu'] = array(
					'*'  => 'playback_and_continue(conf-usermenu)',
					'*1' => 'toggle_mute',
					'1'  => 'toggle_mute',
					'*4' => 'decrease_listening_volume',
					'4'  => 'decrease_listening_volume',
					'*6' => 'increase_listening_volume',
					'6'  => 'increase_listening_volume',
					'*7' => 'decrease_talking_volume',
					'7'  => 'decrease_talking_volume',
					'*8' => 'leave_conference',
					'8'  => 'leave_conference',
					'*9' => 'increase_talking_volume',
					'9'  => 'increase_talking_volume',
				);
			}
			if (empty($this->_confbridge['menu']['user_menu'])) {
			}
			foreach (array('user','bridge','menu') as $type) {
				foreach ($this->_confbridge[$type] as $section => $settings) {
					$output .= "[" . $section . "]\n";
					$output .= "type = " . $type . "\n";
					foreach ($settings as $key => $value) {
						$output .= $key . " = " . $value . "\n";
					}
					$output .= "\n";
				}
			}
			if (empty($this->_confbridge['menu']['admin_menu'])) {
			}
			if (empty($this->_confbridge['menu']['user_menu'])) {
			}
		break;
		}
		return $output;
	}
}

// returns a associative arrays with keys 'destination' and 'description'
function conferences_destinations() {
	//get the list of meetmes
	$results = conferences_list();

	// return an associative array with destination and description
	if (isset($results)) {
		foreach($results as $result){
			$extens[] = array('destination' => 'ext-meetme,'.$result['0'].',1', 'description' => $result['1']." <".$result['0'].">");
		}
		return $extens;
	} else {
		return null;
	}
}

function conferences_getdest($exten) {
	return array('ext-meetme,'.$exten.',1');
}

function conferences_getdestinfo($dest) {
	global $active_modules;

	if (substr(trim($dest),0,11) == 'ext-meetme,') {
		$exten = explode(',',$dest);
		$exten = $exten[1];
		$thisexten = conferences_get($exten);
		if (empty($thisexten)) {
			return array();
		} else {
			//$type = isset($active_modules['announcement']['type'])?$active_modules['announcement']['type']:'setup';
			return array('description' => sprintf(_("Conference Room %s : %s"),$exten,$thisexten['description']),
			             'edit_url' => 'config.php?display=conferences&extdisplay='.urlencode($exten),
					);
		}
	} else {
		return false;
	}
}

function conferences_recordings_usage($recording_id) {
	global $active_modules;

	$results = sql("SELECT `exten`, `description` FROM `meetme` WHERE `joinmsg_id` = '$recording_id'","getAll",DB_FETCHMODE_ASSOC);
	if (empty($results)) {
		return array();
	} else {
		foreach ($results as $result) {
			$usage_arr[] = array(
				'url_query' => 'config.php?display=conferences&extdisplay='.urlencode($result['exten']),
				'description' => sprintf(_("Conference: %s"),$result['description']),
			);
		}
		return $usage_arr;
	}
}

/* 	Generates dialplan for conferences
	We call this with retrieve_conf
*/
function conferences_get_config($engine) {
	global $ext, $conferences_conf, $version, $amp_conf, $astman;
	
	$ast_ge_162 = version_compare($version, '1.6.2', 'ge');
	$ast_ge_10 = version_compare($version, '10', 'ge');
	
	switch($engine) {
		case "asterisk":
			$ext->addInclude('from-internal-additional','ext-meetme');
			$contextname = 'ext-meetme';
			if(is_array($conflist = conferences_list())) {

				$ast_ge_14 = version_compare($version, "1.4","ge");
				
				// Start the conference
				if ($ast_ge_14) {
					if ($amp_conf['ASTCONFAPP'] == 'app_confbridge' && $ast_ge_10) {
						$ext->add($contextname, 'STARTMEETME', '', new ext_execif('$["${MEETME_MUSIC}" != ""]','Set','CONFBRIDGE(user,music_on_hold_class)=${MEETME_MUSIC}'));
					} else {
						$ext->add($contextname, 'STARTMEETME', '', new ext_execif('$["${MEETME_MUSIC}" != ""]','Set','CHANNEL(musicclass)=${MEETME_MUSIC}'));
					}
				} else {
					$ext->add($contextname, 'STARTMEETME', '', new ext_execif('$["${MEETME_MUSIC}" != ""]','SetMusicOnHold','${MEETME_MUSIC}'));
				}
				$ext->add($contextname, 'STARTMEETME', '', new ext_setvar('GROUP(meetme)','${MEETME_ROOMNUM}'));
				$ext->add($contextname, 'STARTMEETME', '', new ext_gotoif('$[${MAX_PARTICIPANTS} > 0 && ${GROUP_COUNT(${MEETME_ROOMNUM}@meetme)}>${MAX_PARTICIPANTS}]','MEETMEFULL,1'));
				// No harm done if quietmode, these will just then be ignored
				//
				if ($amp_conf['ASTCONFAPP'] == 'app_confbridge' && !$ast_ge_10) {
					$ext->add($contextname, 'STARTMEETME', '', new ext_set('CONFBRIDGE_JOIN_SOUND','beep'));
					$ext->add($contextname, 'STARTMEETME', '', new ext_set('CONFBRIDGE_LEAVE_SOUND','beeperr'));
				}
				if ($amp_conf['ASTCONFAPP'] == 'app_confbridge' && $ast_ge_10) {
					$ext->add($contextname, 'STARTMEETME', '', new ext_meetme('${MEETME_ROOMNUM}',',','${MENU_PROFILE}'));
				} else {
					$ext->add($contextname, 'STARTMEETME', '', new ext_meetme('${MEETME_ROOMNUM}','${MEETME_OPTS}','${PIN}'));
				}

				$ext->add($contextname, 'STARTMEETME', '', new ext_hangup(''));

				//meetme full
				$ext->add($contextname, 'MEETMEFULL', '', new ext_playback('im-sorry&conf-full&goodbye'));
				$ext->add($contextname, 'MEETMEFULL', '', new ext_hangup(''));
				
				// hangup for whole context
				$ext->add($contextname, 'h', '', new ext_hangup(''));
				
				foreach($conflist as $item) {
					$room = conferences_get(ltrim($item['0']));
					
					$roomnum = ltrim($item['0']);
					$roomoptions = $room['options'];
					$roomusers = $room['users'];
					$roomuserpin = $room['userpin'];
					$roomadminpin = $room['adminpin'];
					if(isset($room['music']) && $room['music'] !='' && $room['music']!='inherit') {
						$music = $room['music'];
					} else {
						$music='${MOHCLASS}'; // inherit channel moh class
					}
					if (isset($room['joinmsg_id']) && $room['joinmsg_id'] != '') {
						$roomjoinmsg = recordings_get_file($room['joinmsg_id']);
					} else {
						$roomjoinmsg = '';
					}

					if ($ast_ge_14) {
						$roomoptions = str_replace('i','I',$roomoptions);
					}
					if (!$ast_ge_14) {
						$roomoptions = str_replace('o','',$roomoptions);
						$roomoptions = str_replace('T','',$roomoptions);
					}
					
					// Add optional hint
					if ($amp_conf['USEDEVSTATE']) {

						$hint_pre = $amp_conf['ASTCONFAPP'] == 'app_meetme' ? 'MeetMe' : 'confbridge';
						$ext->addHint($contextname, $roomnum, $hint_pre . ":" . $roomnum);
						$hints[] = $hint_pre . ":" . $roomnum;
					}
					// entry point
					$ext->add($contextname, $roomnum, '', new ext_macro('user-callerid'));
					$ext->add($contextname, $roomnum, '', new ext_setvar('MEETME_ROOMNUM',$roomnum));
					$ext->add($contextname, $roomnum, '', new ext_setvar('MAX_PARTICIPANTS', $roomusers));
					$ext->add($contextname, $roomnum, '', new ext_setvar('MEETME_MUSIC',$music));
          $ext->add($contextname, $roomnum, '', new ext_gosub('1','s','sub-record-check',"conf,$roomnum," . (strstr($room['options'],'r') !== false ? 'always' : 'never')));
					$ext->add($contextname, $roomnum, '', new ext_gotoif('$["${DIALSTATUS}" = "ANSWER"]',($roomuserpin == '' && $roomadminpin == '' ? 'USER' : 'READPIN')));	
					$ext->add($contextname, $roomnum, '', new ext_answer(''));
					$ext->add($contextname, $roomnum, '', new ext_wait(1));
					
					// Deal with PINs -- if exist
					if ($roomuserpin != '' || $roomadminpin != '') {
						$ext->add($contextname, $roomnum, '', new ext_setvar('PINCOUNT','0'));
						$ext->add($contextname, $roomnum, 'READPIN', new ext_read('PIN','enter-conf-pin-number'));
						
						// userpin -- must do always, otherwise if there is just an adminpin
						// there would be no way to get to the conference !
						$ext->add($contextname, $roomnum, '', new ext_gotoif('$[x${PIN} = x'.$roomuserpin.']','USER'));

						// admin pin -- exists
						if ($roomadminpin != '') {
							$ext->add($contextname, $roomnum, '', new ext_gotoif('$[x${PIN} = x'.$roomadminpin.']','ADMIN'));
						}

						// pin invalid
						$ext->add($contextname, $roomnum, '', new ext_setvar('PINCOUNT','$[${PINCOUNT}+1]'));
						$ext->add($contextname, $roomnum, '', new ext_gotoif('$[${PINCOUNT}>3]', "h,1"));
						$ext->add($contextname, $roomnum, '', new ext_playback('conf-invalidpin'));
						$ext->add($contextname, $roomnum, '', new ext_goto('READPIN'));
						
						// admin mode -- only valid if there is an admin pin
						if ($roomadminpin != '') {
							if ($amp_conf['ASTCONFAPP'] == 'app_confbridge' && $ast_ge_10) {
								conferences_get_config_confbridge_helper($contextname, $roomnum, $roomoptions, 'admin');
							} else {
								$ext->add($contextname, $roomnum, 'ADMIN', new ext_setvar('MEETME_OPTS','aA'.str_replace('m','',$roomoptions)));
							}
							if ($roomjoinmsg != '') {  // play joining message if one defined
								$ext->add($contextname, $roomnum, '', new ext_playback($roomjoinmsg));
							}
							$ext->add($contextname, $roomnum, '', new ext_goto('STARTMEETME,1'));							
						}
					}
					
					// user mode
					if ($amp_conf['ASTCONFAPP'] == 'app_confbridge' && $ast_ge_10) {
						conferences_get_config_confbridge_helper($contextname, $roomnum, $roomoptions, 'user');
					} else {
						$ext->add($contextname, $roomnum, 'USER', new ext_setvar('MEETME_OPTS',$roomoptions));
					}
					if ($roomjoinmsg != '') {  // play joining message if one defined
						$ext->add($contextname, $roomnum, '', new ext_playback($roomjoinmsg));
					}
					$ext->add($contextname, $roomnum, '', new ext_goto('STARTMEETME,1'));
					
					// add meetme config
					if ($amp_conf['ASTCONFAPP'] == 'app_meetme') {
						$conferences_conf->addMeetme($room['exten'],$room['userpin'],$room['adminpin']);
					}
				}

				$fcc = new featurecode('conferences', 'conf_status');
				$conf_code = $fcc->getCodeActive();
				unset($fcc);

				if ($conf_code != '') {
					$ext->add($contextname, $conf_code, '', new ext_hangup(''));
					if ($amp_conf['USEDEVSTATE']) {
						$ext->addHint($contextname, $conf_code, implode('&', $hints));
					}
				}
			}

		break;
	}
}

function conferences_get_config_confbridge_helper($contextname, $roomnum, $roomoptions, $user_type) {
	global $ext, $conferences_conf, $version, $amp_conf;

	$user_type - strtolower($user_type);
	if ($user_type == 'admin') {
		$ext->add($contextname, $roomnum, 'ADMIN', new ext_set('CONFBRIDGE(user,admin)','yes'));
		$ext->add($contextname, $roomnum, '', new ext_set('CONFBRIDGE(user,marked)','yes'));
	} else {
		$ext->add($contextname, $roomnum, 'USER', new ext_noop('User Options:'));
	}
	$options = str_split($roomoptions);
	foreach ($options as $opt) {
		switch ($opt) {
		case 'w':
			if ($user_type != 'admin') {
				$ext->add($contextname, $roomnum, '', new ext_set('CONFBRIDGE(user,wait_marked)','yes'));
				$ext->add($contextname, $roomnum, '', new ext_set('CONFBRIDGE(user,end_marked)','yes'));
			}
			break;
		case 'q':
			$ext->add($contextname, $roomnum, '', new ext_set('CONFBRIDGE(user,quiet)','yes'));
			break;
		case 'c':
			$ext->add($contextname, $roomnum, '', new ext_set('CONFBRIDGE(user,announce_user_count)','yes'));
			break;
		case 'i':
		case 'I':
			$ext->add($contextname, $roomnum, '', new ext_set('CONFBRIDGE(user,announce_join_leave)','yes'));
			break;
		case 'o':
			$ext->add($contextname, $roomnum, '', new ext_set('CONFBRIDGE(user,dsp_drop_silence)','yes'));
			break;
		case 'T':
			$ext->add($contextname, $roomnum, '', new ext_set('CONFBRIDGE(user,talk_detection_events)','yes'));
			break;
		case 'M':
			$ext->add($contextname, $roomnum, '', new ext_set('CONFBRIDGE(user,music_on_hold_when_empty)','yes'));
			break;
		case 's':
			if ($user_type == 'admin') {
				$ext->add($contextname, $roomnum, '', new ext_set('MENU_PROFILE','admin_menu'));
			} else {
				$ext->add($contextname, $roomnum, '', new ext_set('MENU_PROFILE','user_menu'));
			}
			break;
		case 'r':
			// Set by sub-record-check
			break;
		case 'm':
			if ($user_type != 'admin') {
				$ext->add($contextname, $roomnum, '', new ext_set('CONFBRIDGE(user,startmuted)','yes'));
			}
			break;
		}
	}
}

function conferences_check_extensions($exten=true) {
	$extenlist = array();
	if (is_array($exten) && empty($exten)) {
		return $extenlist;
	}
	$sql = "SELECT exten, description FROM meetme ";
	if (is_array($exten)) {
		$sql .= "WHERE exten in ('".implode("','",$exten)."')";
	}
	$sql .= " ORDER BY exten";
	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

	foreach ($results as $result) {
		$thisexten = $result['exten'];
		$extenlist[$thisexten]['description'] = _("Conference: ").$result['description'];
		$extenlist[$thisexten]['status'] = 'INUSE';
		$extenlist[$thisexten]['edit_url'] = 'config.php?display=conferences&extdisplay='.urlencode($thisexten);
	}
	return $extenlist;
}

//get the existing meetme extensions
function conferences_list() {
	$results = sql("SELECT exten,description FROM meetme ORDER BY exten","getAll",DB_FETCHMODE_ASSOC);
	foreach($results as $result){
		// check to see if we are in-range for the current AMP User.
		if (isset($result['exten']) && checkRange($result['exten'])){
			// return this item's dialplan destination, and the description
			$extens[] = array($result['exten'],$result['description']);
		}
	}
	if (isset($extens)) {
		return $extens;
	} else {
		return null;
	}
}

function conferences_get($account){
  global $db;
	//get all the variables for the meetme
	$results = sql("SELECT exten,options,userpin,adminpin,description,joinmsg_id,music,users FROM meetme WHERE exten = '".$db->escapeSimple($account)."'","getRow",DB_FETCHMODE_ASSOC);
	return $results;
}

function conferences_del($account){
	global $db;
	$results = sql("DELETE FROM meetme WHERE exten = '".$db->escapeSimple($account)."'","query");
}

function conferences_add($account,$name,$userpin,$adminpin,$options,$joinmsg_id=null,$music='',$users=0){
	global $active_modules;
	global $db;
	$account    = $db->escapeSimple($account);
	$name       = $db->escapeSimple($name);
	$userpin    = $db->escapeSimple($userpin);
	$adminpin   = $db->escapeSimple($adminpin);
	$options    = $db->escapeSimple($options);
	$joinmsg_id = $db->escapeSimple($joinmsg_id);
	$music      = $db->escapeSimple($music);
	$users      = $db->escapeSimple($users);
	$results = sql("INSERT INTO meetme (exten,description,userpin,adminpin,options,joinmsg_id,music,users) values (\"$account\",\"$name\",\"$userpin\",\"$adminpin\",\"$options\",\"$joinmsg_id\",\"$music\",\"$users\")");
}
?>
