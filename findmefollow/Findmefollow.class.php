<?php
// vim: set ai ts=4 sw=4 ft=php:

class Findmefollow implements BMO {

	public function __construct($issabelpbx = null) {
		if ($issabelpbx == null) {
			throw new Exception("Not given a IssabelPBX Object");
		}

		$this->IssabelPBX = $issabelpbx;
		$this->db = $issabelpbx->Database;
	}

	public function doConfigPageInit($page) {

	}

	public function install() {

	}
	public function uninstall() {

	}
	public function backup(){

	}
	public function restore($backup){

	}
	public function genConfig() {

	}


	/*
	 * Gets Follow Me Confirmation Setting
	 *
	 * @param string $exten Extension to get information about
	 * @return bool True is confirmed, False is not
	 */
	function getConfirm($exten) {
		$response = $this->IssabelPBX->astman->database_get("AMPUSER","$exten/followme/grpconf");
		return preg_match("/ENABLED/",$response);
	}

	/*
	 * Sets Follow Confirmation Setting
	 *
	 * @param string $exten Extension to modify
	 * @param bool $follow_me_cofirm Follow Me Confirm Setting
	 */
	function setConfirm($exten,$follow_me_confirm) {
		$value = ($follow_me_confirm)?'ENABLED':'DISABLED';
		$this->IssabelPBX->astman->database_put('AMPUSER', "$exten/followme/grpconf", $value);
	}

	/*
	 * Sets Follow Me List
	 *
	 * @param $exten Extension to modify
	 * @param $follow_me_list Follow Me List
	 */
	function setList($exten,$follow_me_list) {
		$this->IssabelPBX->astman->database_put('AMPUSER', "$exten/followme/grplist", $follow_me_list);
	}

	/*
	 * Gets Follow Me List if set
	 *
	 * @param $exten Extension to get information about
	 * @return $data follow me list if set
	 */
	function getList($exten) {
		$response = $this->IssabelPBX->astman->database_get("AMPUSER","$exten/followme/grplist");
		return preg_replace("/[^0-9*\-+]/", "", $response);
	}

	/*
	 * Sets Follow Me List Ring Time
	 *
	 * @param $exten Extension to modify
	 * @param $follow_me_listring_time List Ring Time to ring
	 */
	function setListRingTime($exten,$follow_me_listring_time) {
		$this->IssabelPBX->astman->database_put('AMPUSER', "$exten/followme/grptime", $follow_me_listring_time);
	}

	/*
	 * Gets Follow Me List-Ring Time if set
	 *
	 * @param $exten Extension to get information about
	 * @return $number follow me list-ring time returned if set
	 */
	function getListRingTime($exten) {
		$response = $this->IssabelPBX->astman->database_get("AMPUSER","$exten/followme/grptime");
		return is_numeric($response) ? $response : '';
	}

	/*
	 * Sets Follow Me Pre-Ring Time
	 *
	 * @param $exten Extension to modify
	 * @param $follow_me_prering_time Pre-Ring Time to ring
	 */
	function setPreRingTime($exten,$follow_me_prering_time) {
		$this->IssabelPBX->astman->database_put('AMPUSER', "$exten/followme/prering", $follow_me_prering_time);
	}

	/*
	 * Gets Follow Me Pre-Ring Time if set
	 *
	 * @param $exten Extension to get information about
	 * @return $number follow me pre-ring time returned if set
	 */
	function getPreRingTime($exten) {
		$response = $this->IssabelPBX->astman->database_get("AMPUSER","$exten/followme/prering");
		return is_numeric($response) ? $response : '';
	}

	/*
	 * Sets Follow Ddial Setting
	 *
	 * @param $exten Extension to modify
	 * @param $follow_me_ddial Follow Me Ddial Setting
	 */
	function setDDial($exten,$follow_me_ddial) {
		$value_opt = ($follow_me_ddial)?'DIRECT':'EXTENSION';
		$response = $this->IssabelPBX->astman->database_put('AMPUSER',"$exten/followme/ddial",$value_opt);

		// Now that we have set the state (DIRECT is enabled, EXTENSION is disabled)
		// Get the devices associated with this user first and then we will set them all as needed
		//
		//
		if ($this->IssabelPBX->Config->get_conf_setting('USEDEVSTATE')) {
			$value_opt = ($follow_me_ddial)?'BUSY':'NOT_INUSE';
			$devices = $this->IssabelPBX->astman->database_get("AMPUSER",$exten."/device");
			$device_arr = explode('&',$devices);
			foreach ($device_arr as $device) {
				$this->IssabelPBX->astman->set_global($this->IssabelPBX->Config->get_conf_setting('AST_FUNC_DEVICE_STATE') . "(Custom:FOLLOWME$device)", $value_opt);
			}
		}
	}

	/*
	 * Gets Follow Me Ddial Setting
	 *
	 * @param $exten Extension to get information about
	 * @return $data follow me ddial setting
	 */
	function getDDial($exten) {
		$response = $this->IssabelPBX->astman->database_get("AMPUSER",$exten."/followme/ddial");
		if (trim($response) == 'EXTENSION') {
			return true;
		} elseif (trim($response) == 'DIRECT') {
			return false;
		} else {
			// If here then followme must not be set so use default
			return $this->IssabelPBX->Config->get_conf_setting('FOLLOWME_DISABLED') ? true : false;
		}
	}

	/*
	 * Sets Follow-Me Settings in IssabelPBX MySQL Database
	 *
	 * @param $exten Extension to modify
	 * @param $follow_me_prering_time Pre-Ring Time to ring
	 * @param $follow_me_listring_time List Ring Time to ring
	 * @param $follow_me_list Follow Me List
	 * @param $follow_me_list Follow Me Confirm Setting
	 *
	 */
	function setMySQL($exten, $follow_me_prering_time, $follow_me_listring_time, $follow_me_list, $follow_me_confirm) {
		$db = $this->db;

		//format for SQL database
		$follow_me_confirm = ($follow_me_confirm)?'CHECKED':'';

		$sql = "UPDATE findmefollow SET grptime = '" . $follow_me_listring_time . "', grplist = '".
		$db->escapeSimple(trim($follow_me_list)) . "', pre_ring = '" . $follow_me_prering_time .
		"', needsconf = '" . $follow_me_confirm . "' WHERE grpnum = $exten LIMIT 1";
		$results = $db->query($sql);


		return 1;
	}

	function getSettingsById($grpnum, $check_astdb=0) {
		$db = $this->db;
		$sql = "SELECT grpnum, strategy, grptime, grppre, grplist, annmsg_id, postdest, dring, needsconf, remotealert_id, toolate_id, ringing, pre_ring, voicemail FROM findmefollow INNER JOIN `users` ON `extension` = `grpnum` WHERE grpnum = ?";
		$sth = $db->prepare($sql);
		$sth->execute(array($grpnum));
		$results = $sth->fetch(\PDO::FETCH_ASSOC);

		if (empty($results)) {
			return array();
		}

		if (!isset($results['voicemail'])) {
			$sql = "SELECT `voicemail` FROM `users` WHERE `extension` = ?";
			$sth = $db->prepare($sql);
			$sth->execute(array($grpnum));
			$results['voicemail'] = $sth->fetchColumn();
		}

		if (!isset($results['strategy'])) {
			$results['strategy'] = $this->IssabelPBX->Config->get_conf_setting('FOLLOWME_RG_STRATEGY');
		}

		if ($check_astdb) {
			if ($this->IssabelPBX->astman->Connected()) {
				$astdb_prering = $this->getPreRingTime($grpnum);
				$astdb_grptime = $this->getListRingTime($grpnum);
				$astdb_grplist = $this->getList($grpnum);
				$astdb_grpconf = $this->getConfirm($grpnum);

				$astdb_changecid = strtolower($this->IssabelPBX->astman->database_get("AMPUSER",$grpnum."/followme/changecid"));
				switch($astdb_changecid) {
					case 'default':
					case 'did':
					case 'forcedid':
					case 'fixed':
					case 'extern':
					break;
					default:
						$astdb_changecid = 'default';
				}
				$results['changecid'] = $astdb_changecid;
				$fixedcid = $this->IssabelPBX->astman->database_get("AMPUSER",$grpnum."/followme/fixedcid");
				$results['fixedcid'] = preg_replace("/[^0-9\+]/" ,"", trim($fixedcid));
			}
			$astdb_ddial = $this->getDDial($grpnum);
			// If the values are different then use what is in astdb as it may have been changed.
			// If sql returned no results for pre_ring/grptime then it's not configued so we reset
			// the astdb defaults as well
			//
			$changed=0;
			if (!isset($results['pre_ring'])) {
				$results['pre_ring'] = $astdb_prering = $this->IssabelPBX->Config->get_conf_setting('FOLLOWME_PRERING');
			}
			if (!isset($results['grptime'])) {
				$results['grptime'] = $astdb_grptime = $this->IssabelPBX->Config->get_conf_setting('FOLLOWME_TIME');
			}
			if (!isset($results['grplist'])) {
				$results['grplist'] = '';
			}
			if (!isset($results['needsconf'])) {
				$results['needsconf'] = '';
			}
			if (($astdb_prering != $results['pre_ring']) && ($astdb_prering >= 0)) {
				$results['pre_ring'] = $astdb_prering;
				$changed=1;
			}
			if (($astdb_grptime != $results['grptime']) && ($astdb_grptime > 0)) {
				$results['grptime'] = $astdb_grptime;
				$changed=1;
			}
			if ((trim($astdb_grplist) != trim($results['grplist'])) && (trim($astdb_grplist) != '')) {
				$results['grplist'] = $astdb_grplist;
				$changed=1;
			}

			$confvalue = ($astdb_grpconf) ? 'CHECKED' : '';
			if ($confvalue != trim($results['needsconf'])) {
				$results['needsconf'] = $confvalue;
				$changed=1;
			}

			$results['ddial'] = ($astdb_ddial) ? 'CHECKED' : '';

			if ($changed) {
				$sql = "UPDATE findmefollow SET grptime = '".$results['grptime']."', grplist = '".
				$db->escapeSimple(trim($results['grplist']))."', pre_ring = '".$results['pre_ring'].
				"', needsconf = '".$results['needsconf']."' WHERE grpnum = '".$db->escapeSimple($grpnum)."' LIMIT 1";
				$sql_results = $db->query($sql);
			}
		} // if check_astdb
	return $results;
}

	function add($grpnum,$strategy,$grptime,$grplist,$postdest,$grppre='',$annmsg_id='',$dring,$needsconf,$remotealert_id,$toolate_id,$ringing,$pre_ring,$ddial,$changecid='default',$fixedcid='') {
	global $amp_conf;
	global $astman;
	global $db;

	if (empty($postdest)) {
	$postdest = "ext-local,$grpnum,dest";
	}

	$sql = "INSERT INTO findmefollow (grpnum, strategy, grptime, grppre, grplist, annmsg_id, postdest, dring, needsconf, remotealert_id, toolate_id, ringing, pre_ring) VALUES ('".$db->escapeSimple($grpnum)."', '".$db->escapeSimple($strategy)."', ".$db->escapeSimple($grptime).", '".$db->escapeSimple($grppre)."', '".$db->escapeSimple($grplist)."', '".$db->escapeSimple($annmsg_id)."', '".$db->escapeSimple($postdest)."', '".$db->escapeSimple($dring)."', '$needsconf', '$remotealert_id', '$toolate_id', '$ringing', '$pre_ring')";
	$results = sql($sql);

	if ($astman) {
	$astman->database_put("AMPUSER",$grpnum."/followme/prering",isset($pre_ring)?$pre_ring:'');
	$astman->database_put("AMPUSER",$grpnum."/followme/grptime",isset($grptime)?$grptime:'');
	$astman->database_put("AMPUSER",$grpnum."/followme/grplist",isset($grplist)?$grplist:'');

	$needsconf = isset($needsconf)?$needsconf:'';
	$confvalue = ($needsconf == 'CHECKED')?'ENABLED':'DISABLED';
	$astman->database_put("AMPUSER",$grpnum."/followme/grpconf",$confvalue);

	$ddial      = isset($ddial)?$ddial:'';
	$ddialvalue = ($ddial == 'CHECKED')?'EXTENSION':'DIRECT';
	$astman->database_put("AMPUSER",$grpnum."/followme/ddial",$ddialvalue);
	if ($amp_conf['USEDEVSTATE']) {
	$ddialstate = ($ddial == 'CHECKED')?'NOT_INUSE':'BUSY';

	$devices = $astman->database_get("AMPUSER", $grpnum . "/device");
	$device_arr = explode('&', $devices);
	foreach ($device_arr as $device) {
	$astman->set_global($amp_conf['AST_FUNC_DEVICE_STATE'] . "(Custom:FOLLOWME$device)", $ddialstate);
	}
	}

	$astman->database_put("AMPUSER",$grpnum."/followme/changecid",$changecid);
	$fixedcid = preg_replace("/[^0-9\+]/" ,"", trim($fixedcid));
	$astman->database_put("AMPUSER",$grpnum."/followme/fixedcid",$fixedcid);
	} else {
	fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
	}
	}

	function del($grpnum) {
		$sql = "DELETE FROM findmefollow WHERE grpnum = ?";
		$sth = $db->prepare($sql);
		$sth->execute(array($grpnum));


		if ($this->IssabelPBX->astman->Connected()) {
			$this->IssabelPBX->astman->database_deltree("AMPUSER/".$grpnum."/followme");
		}
	}
}
