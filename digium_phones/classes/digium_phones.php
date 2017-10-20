<?php

/**
 * \file
 * IssabelPBX Digium Phones Config Module
 *
 * Copyright (c) 2011, Digium, Inc.
 *
 * Author: Jason Parker <jparker@digium.com>
 *
 * This program is free software, distributed under the terms of
 * the GNU General Public License Version 2. 
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * This module is included by module_admin prior to retrieve_conf
 * generating new configuration files.
 */

require_once dirname(__FILE__).'/digium_phones_firmware_manager.php';

/**
 * main class for handling digium_phones configuration
 */
class digium_phones {
	/**
	 * Constructor
	 */
	public function digium_phones () {
		$this->load();
	}

	/**
	 * Load
	 *
	 * Load all the information from the database
	 */
	public function load() {
		global $db;

		$this->cache_core_devices_list();
		$this->cache_core_users_list();

		$this->read_general();
		$this->read_devices();
		$this->read_extension_settings();
		$this->read_phonebooks();
		$this->read_queues();
		$this->read_statuses();
		$this->read_customapps();
		$this->read_logos();
		$this->read_networks();
		$this->read_alerts();
		$this->read_ringtones();
		$this->read_externallines();
		$this->read_firmware();
	}
	private $core_devices = array();
	private $core_users = array();

	private $general = array();
	private $devices = array();
	private $extension_settings = array();
	private $phonebooks = array();
	private $queues = array();
	private $statuses = array();
	private $customapps = array();
	private $logos = array();
	private $networks = array();
	private $alerts = array();
	private $ringtones = array();
	private $externallines = array();
	private $voicemail_translations = array();
	private $locales = NULL;
	private $firmware_manager = NULL;

	private $error_msg = '';		// The latest error message

	private $dpma_version = '';

	public function get_dpma_version() {
		global $astman;

		if (!$this->dpma_version) {
			$this->dpma_version = 'unknown';
			$response = $astman->send_request('Command',
				array('Command' => 'digium_phones show version'));
			if (preg_match('/Version [0-9.]+_([0-9.]+)/', $response['data'], $matches)) {
				$this->dpma_version = $matches[1];
			}
		}
		return $this->dpma_version;
	}

	public function cache_core_devices_list() {
		$devices = core_devices_list('all', 'full');
		if (!empty($devices) && is_array($devices)) {
			foreach($devices as $device) {
				$this->core_devices[$device['id']] = $device;
			}
		}
	}

	public function get_core_devices() {
		return $this->core_devices;
	}

	public function get_core_device($param) {
		if (array_key_exists($param,$this->core_devices))
			return($this->core_devices[$param]);
		return null;
	}

	public function cache_core_users_list() {
		foreach(core_users_list() as $user) {
			$newuser['extension'] = $user[0];
			$newuser['name'] = $user[1];
			$newuser['voicemail'] = $user[2];
			$this->core_users[$newuser['extension']] = $newuser;
		}
	}

	public function get_core_users() {
		return $this->core_users;
	}

	public function get_core_user($param) {
		if (array_key_exists($param,$this->core_users))
			return($this->core_users[$param]);
		return null;
	}

	/**
	 * Get general
	 *
	 * Get a general parameter
	 */
	 public function get_general($param) {
		return $this->general[$param];
	 }

	/**
	 * Get All general
	 *
	 * Get all general parameters
	 */
	 public function get_all_general() {
		return $this->general;
	 }

	/**
	 * Get devices
	 *
	 * Get the devices
	 *
	 * @access public
	 * @return array
	 */
	public function get_devices() {
		return $this->devices;
	}

	/**
	 * Get device
	 * 
	 * Get a device and all its info
	 */
	public function get_device($deviceid) {
		return $this->devices[$deviceid];
	}

	/**
	 * Get extension settings
	 *
	 * Get the extension settings
	 *
	 * @access public
	 * @return array
	 */
	public function get_extensions_settings() {
		return $this->extensions_settings;
	}

	/**
	 * Get extension settings
	 * 
	 * Get an extension and all its settings
	 */
	public function get_extension_settings($extension) {
		return $this->extension_settings[$extension];
	}

	/**
	 * Get phonebooks
	 *
	 * Get the phonebooks
	 *
	 * @access public
	 * @return array
	 */
	public function get_phonebooks() {
		if (empty($this->phonebooks)) {
			return(array());
		}
		return $this->phonebooks;
	}

	/**
	 * Get phonebook
	 * 
	 * Get a phonebook and all its extensions
	 */
	public function get_phonebook($id) {
		return $this->phonebooks[$id];
	}

	/**
	 * Get queues
	 *
	 * Get the queues
	 *
	 * @access public
	 * @return array
	 */
	public function get_queues() {
		return $this->queues;
	}

	/**
	 * Get queue
	 * 
	 * Get a queue and all its settings
	 */
	public function get_queue($id) {
		return $this->queues[$id];
	}

	/**
	 * Get statuses
	 *
	 * Get the statuses
	 *
	 * @access public
	 * @return array
	 */
	public function get_statuses() {
		return $this->statuses;
	}

	/**
	 * Get status
	 * 
	 * Get a status and all its settings and entries
	 */
	public function get_status($id) {
		return $this->statuses[$id];
	}

	/**
	 * Get customapps
	 *
	 * Get the customapps
	 *
	 * @access public
	 * @return array
	 */
	public function get_customapps() {
		return $this->customapps;
	}

	/**
	 * Get customapp
	 * 
	 * Get a customapp and all its settings
	 */
	public function get_customapp($id) {
		return $this->customapps[$id];
	}

	/**
	 * Get networks
	 *
	 * Get the networks
	 *
	 * @access public
	 * @return array
	 */
	public function get_networks() {
		return $this->networks;
	}

	/**
	 * Get network
	 * 
	 * Get a network and all its settings
	 */
	public function get_network($id) {
		return $this->networks[$id];
	}

	/**
	 * Get external lines
	 *
	 * Get the external lines
	 *
	 * @access public
	 * @return array
	 */
	public function get_externallines() {
		return $this->externallines;
	}

	/**
	 * Get external line
	 *
	 * Get an external line and all its settings
	 */
	public function get_externalline($id) {
		return $this->externallines[$id];
	}

	/**
	 * Get logos
	 *
	 * Get the logos
	 *
	 * @access public
	 * @return array
	 */
	public function get_logos() {
		return $this->logos;
	}

	/**
	 * Get logo
	 * 
	 * Get a logo and all its settings
	 */
	public function get_logo($id) {
		return $this->logos[$id];
	}

	/**
	 *
	 * Get alerts
	 *
	 * Get the alerts
	 */
	public function get_alerts() {
		return $this->alerts;
	}

	/**
	 * Get alert
	 * 
	 * Get an alert
	 */
	public function get_alert($id) {
		return $this->alerts[$id];
	}

	/**
	 *
	 * Get all ringtones
	 *
	 * Get a list of built-in and user defined ringtones
	 */
	public function get_ringtones() {
		return $this->ringtones;
	}

	/**
	 * Get ringtone
	 * 
	 * Get a ringtone
	 */
	public function get_ringtone($id) {
		return $this->ringtones[$id];
	}

	/**
	 * Read Digium Phones general section
	 */
	public function read_general() {
		global $db;

		$sql = 'SELECT * FROM digium_phones_general';

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach($results as $result) {
			if ($result['keyword'] == 'mdns_address' && $result['val'] == '') {
				// We don't have an mDNS address set.  Default it to the address the user connected to.
				if (isset($_SERVER['SERVER_ADDR'])) {
					$result['val'] = $_SERVER['SERVER_ADDR'];
					$this->update_general(array('mdns_address'=>$_SERVER['SERVER_ADDR']));
				}
			}
			$this->general[$result['keyword']] = ($result['val']) ? $result['val'] : $result['default_val'];
		}
	}

	/**
	 * Update Digium Phones general section
	 *
	 * @access public
	 * @param array $params An array of parameters
	 * @return bool
	 */
	public function update_general($params) {
		global $db;

		foreach ($params as $keyword=>$val) {
			if ($val === null) {
				$sql = "UPDATE digium_phones_general SET val=null WHERE keyword=\"{$db->escapeSimple($keyword)}\"";
				$this->general[$keyword] = null;
			} else {
				$sql = "UPDATE digium_phones_general SET val=\"{$db->escapeSimple($val)}\" WHERE keyword=\"{$db->escapeSimple($keyword)}\"";
				$this->general[$keyword] = $val;
			}
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		needreload();
	}

	public function read_devices() {
		global $db;

		$devices = array();
		$this->devices = array();
		
		// Get all devices.
		$sql = "SELECT id as deviceid, name FROM digium_phones_devices ORDER BY id";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$d = $devices[$row['deviceid']];

			$d['id'] = $row['deviceid'];
			$d['name'] = $row['name'];

			$d['settings'] = array();
			$d['lines'] = array();
			$d['externallines'] = array();
			$d['phonebooks'] = array();
			$d['queues'] = array();
			$d['statuses'] = array();
			$d['customapps'] = array();

			$devices[$row['deviceid']] = $d;
		}

		// Get settings on devices.
		$sql = "SELECT ds.id as deviceid, dss.keyword, dss.val FROM digium_phones_devices AS ds ";
		$sql = $sql . "  LEFT JOIN digium_phones_device_settings AS dss ON (ds.id = dss.deviceid) ";
		$sql = $sql . "ORDER BY ds.id ";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$d = $devices[$row['deviceid']];

			if ($row['keyword'] != null) {
				$d['settings'][$row['keyword']] = $row['val'];
			}

			$devices[$row['deviceid']] = $d;
		}


		// Get lines on devices.
		$sql = "SELECT ds.id as deviceid, ls.id as lineid, ls.extension FROM digium_phones_devices AS ds ";
		$sql = $sql . "  LEFT JOIN digium_phones_lines AS ls ON (ds.id = ls.deviceid) ";
		$sql = $sql . "ORDER BY ds.id, ls.id ";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$d = $devices[$row['deviceid']];

			if ($row['lineid'] != null) {
				$l = $d['lines'][$row['lineid']];
				$l['id'] = $row['lineid'];
				$l['extension'] = $row['extension'];
				$l['settings'] = array();

				$d['lines'][$row['lineid']] = $l;
			}

			$devices[$row['deviceid']] = $d;
		}

		// Get settings on lines.
		$sql = "SELECT ds.id as deviceid, ls.id as lineid, ls.extension, lss.keyword, lss.val FROM digium_phones_devices AS ds ";
		$sql = $sql . "  LEFT JOIN digium_phones_lines AS ls ON (ds.id = ls.deviceid) ";
		$sql = $sql . "  LEFT JOIN digium_phones_extension_settings AS lss ON (ls.extension = lss.extension) ";
		$sql = $sql . "ORDER BY ds.id, ls.id";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$d = $devices[$row['deviceid']];

			if ($row['keyword'] != null) {
				$l = $d['lines'][$row['lineid']];

				$l['settings'][$row['keyword']] = $row['val'];

				$d['lines'][$row['lineid']] = $l;
			}

			$devices[$row['deviceid']] = $d;
		}

		// Get phonebooks on devices.
		$sql = "SELECT dps.id, ds.id as deviceid, dps.phonebookid FROM digium_phones_devices AS ds ";
		$sql = $sql . "  LEFT JOIN digium_phones_device_phonebooks AS dps ON (ds.id = dps.deviceid) ";
		$sql = $sql . "  LEFT JOIN digium_phones_phonebooks AS ps ON (dps.phonebookid = ps.id) ";
		$sql = $sql . "ORDER BY ds.id, dps.id ";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$d = $devices[$row['deviceid']];

			if ($row['id'] != null) {
				$p = $d['phonebooks'][$row['id']];
				$p['phonebookid'] = $row['phonebookid'];
				$d['phonebooks'][$row['id']] = $p;
			}

			$devices[$row['deviceid']] = $d;
		}

		// Get networks on devices.
		$sql = "SELECT dns.id, ds.id as deviceid, dns.networkid FROM digium_phones_devices AS ds ";
		$sql = $sql . "  LEFT JOIN digium_phones_device_networks AS dns ON (ds.id = dns.deviceid) ";
		$sql = $sql . "  LEFT JOIN digium_phones_networks AS ns ON (dns.networkid = ns.id) ";
		$sql = $sql . "ORDER BY ds.id, dns.id ";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$d = $devices[$row['deviceid']];

			if ($row['id'] != null) {
				$n = $d['networks'][$row['id']];
				$n['networkid'] = $row['networkid'];
				$d['networks'][$row['id']] = $n;
			} else {
				$n = $d['networks'][-1];
				$n['networkid'] = -1;
				$d['networks'][-1] = $n;
			}

			$devices[$row['deviceid']] = $d;
		}

		// Get external lines on devices.
		$sql = "SELECT dels.id, ds.id as deviceid, dels.externallineid FROM digium_phones_devices AS ds ";
		$sql = $sql . "  LEFT JOIN digium_phones_device_externallines AS dels ON (ds.id = dels.deviceid) ";
		$sql = $sql . "  LEFT JOIN digium_phones_externallines AS ns ON (dels.externallineid = ns.id) ";
		$sql = $sql . "ORDER BY ds.id, dels.id ";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$d = $devices[$row['deviceid']];

			if ($row['id'] != null) {
				$el = $d['externallines'][$row['id']];
				$el['externallineid'] = $row['externallineid'];
				$d['externallines'][$row['id']] = $el;
			}

			$devices[$row['deviceid']] = $d;
		}

		// Get logos on devices.
		$sql = "SELECT dls.id, ds.id as deviceid, dls.logoid FROM digium_phones_devices AS ds ";
		$sql = $sql . "  LEFT JOIN digium_phones_device_logos AS dls ON (ds.id = dls.deviceid) ";
		$sql = $sql . "  LEFT JOIN digium_phones_logos AS ls ON (dls.logoid = ls.id) ";
		$sql = $sql . "ORDER BY ds.id, dls.id ";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$d = $devices[$row['deviceid']];

			if ($row['id'] != null) {
				$l = $d['logos'][$row['id']];
				$l['logoid'] = $row['logoid'];
				$d['logos'][$row['id']] = $l;
			}

			$devices[$row['deviceid']] = $d;
		}

		// Get alerts on devices.
		$sql = "SELECT das.id, ds.id as deviceid, das.alertid FROM digium_phones_devices AS ds ";
		$sql = $sql . "  LEFT JOIN digium_phones_device_alerts AS das ON (ds.id = das.deviceid) ";
		$sql = $sql . "  LEFT JOIN digium_phones_alerts AS alerts ON (das.alertid = alerts.id) ";
		$sql = $sql . "ORDER BY ds.id, das.id ";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$d = $devices[$row['deviceid']];

			if ($row['id'] != null) {
				$a = $d['alerts'][$row['id']];
				$a['alertid'] = $row['alertid'];
				$d['alerts'][$row['id']] = $a;
			}

			$devices[$row['deviceid']] = $d;
		}

		// Get ringtones on devices.
		$sql = "SELECT das.id, ds.id as deviceid, das.ringtoneid FROM digium_phones_devices AS ds ";
		$sql = $sql . "  LEFT JOIN digium_phones_device_ringtones AS das ON (ds.id = das.deviceid) ";
		$sql = $sql . "  LEFT JOIN digium_phones_ringtones AS ringtones ON (das.ringtoneid = ringtones.id) ";
		$sql = $sql . "ORDER BY ds.id, das.id ";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$d = $devices[$row['deviceid']];

			if ($row['id'] != null) {
				$a = $d['ringtones'][$row['id']];
				$a['ringtoneid'] = $row['ringtoneid'];
				$d['ringtones'][$row['id']] = $a;
			}

			$devices[$row['deviceid']] = $d;
		}

		// Get statuses on devices.
		$sql = "SELECT dss.id, ds.id as deviceid, dss.statusid FROM digium_phones_devices AS ds ";
		$sql = $sql . "  LEFT JOIN digium_phones_device_statuses AS dss ON (ds.id = dss.deviceid) ";
		$sql = $sql . "  LEFT JOIN digium_phones_statuses AS statuses ON (dss.statusid = statuses.id) ";
		$sql = $sql . "ORDER BY ds.id, dss.id ";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$d = $devices[$row['deviceid']];

			if ($row['id'] != null) {
				$a = $d['statuses'][$row['id']];
				$a['statusid'] = $row['statusid'];
				$d['statuses'][$row['id']] = $a;
			}

			$devices[$row['deviceid']] = $d;
		}

		// Get customapps on devices.
		$sql = "SELECT dcs.id, ds.id as deviceid, dcs.customappid FROM digium_phones_devices AS ds ";
		$sql = $sql . "  LEFT JOIN digium_phones_device_customapps AS dcs ON (ds.id = dcs.deviceid) ";
		$sql = $sql . "  LEFT JOIN digium_phones_customapps AS customapps ON (dcs.customappid = customapps.id) ";
		$sql = $sql . "ORDER BY ds.id, dcs.id ";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$d = $devices[$row['deviceid']];

			if ($row['id'] != null) {
				$a = $d['customapps'][$row['id']];
				$a['customappid'] = $row['customappid'];
				$d['customapps'][$row['id']] = $a;
			}

			$devices[$row['deviceid']] = $d;
		}

		foreach ($devices as $device) {
			$d = $device;
			$d['lines'] = array();
			foreach ($device['lines'] as $line) {
				$l = $line;
				$l['user'] = $this->get_core_device($line['extension']);
				$d['lines'][] = $l;
			}
			$this->devices[$d['id']] = $d;
		}
	}

	/**
	 * Update Digium Phones device
	 *
	 * @access public
	 * @param array $device The device to update.
	 * @return bool
	 */
	public function update_device($device) {
		$this->delete_device($device);
		$this->add_device($device);
	}

	public function delete_device($device) {
		global $db;

		$deviceid = $device['id'];

		$this->devices[$deviceid] = null;

		$sql = "DELETE FROM digium_phones_device_phonebooks WHERE deviceid = \"{$db->escapeSimple($device['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_device_networks WHERE deviceid = \"{$db->escapeSimple($device['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_device_externallines WHERE deviceid = \"{$db->escapeSimple($device['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_device_logos WHERE deviceid = \"{$db->escapeSimple($device['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_device_alerts WHERE deviceid = \"{$db->escapeSimple($device['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_device_ringtones WHERE deviceid = \"{$db->escapeSimple($device['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_queues WHERE deviceid = \"{$db->escapeSimple($device['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_device_statuses WHERE deviceid = \"{$db->escapeSimple($device['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_device_customapps WHERE deviceid = \"{$db->escapeSimple($device['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_lines WHERE deviceid = \"{$db->escapeSimple($device['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_device_settings WHERE deviceid = \"{$db->escapeSimple($device['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_devices WHERE id = \"{$db->escapeSimple($device['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		needreload();
	}


	public function add_device($device) {
		global $db;

		$deviceid = $device['id'];

		$lineid = False;
		if (!empty($device['lines'][0]['extension'])) {
			$lineid = $device['lines'][0]['extension'];
		}
		if ($lineid && $deviceid != $lineid) {
			// can we reassign the deviceid to match without a conflict?
			$sql = "SELECT * FROM digium_phones_devices WHERE id=\"{$db->escapeSimple($lineid)}\"";
			$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
			if (DB::IsError($results)) {
				die_issabelpbx($results->getDebugInfo());
				return false;
			}
			if (!$results) {
				// yes, $lineid does not exist, use it
				$deviceid = $lineid;
				$device['id'] = $deviceid;
			}
		}

		// Devices
		$sql = "INSERT INTO digium_phones_devices (id, name) VALUES(\"{$db->escapeSimple($device['id'])}\", \"{$db->escapeSimple($device['name'])}\")";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		if ($deviceid == 0) {
			$sql = "SELECT LAST_INSERT_ID()";

			$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
			if (DB::IsError($results)) {
				die_issabelpbx($results->getDebugInfo());
				return false;
			}

			foreach ($results as $row) {
				$deviceid = $row['LAST_INSERT_ID()'];
			}
		}

		$this->devices[$id] = $device;

		// Device settings
		$devicesettings = array();
		if (!empty($device['settings'])) foreach ($device['settings'] as $key=>$val) {
			if ($val != '') {
				$devicesettings[] = '\''.$db->escapeSimple($deviceid).'\',\''.$db->escapeSimple($key).'\',\''.$db->escapeSimple($val).'\'';
			}
		}

		if (count($devicesettings) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_device_settings (deviceid, keyword, val) VALUES (" . implode('),(', $devicesettings) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		// Lines
		$lines = array();
		if (!empty($device['lines'])) foreach ($device['lines'] as $lineid=>$line) {
			$lines[] = '\''.$db->escapeSimple($lineid).'\',\''.$db->escapeSimple($deviceid).'\',\''.$db->escapeSimple($line['extension']).'\'';
		}

		if (count($lines) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_lines (id, deviceid, extension) VALUES (" . implode('),(', $lines) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		// Device phonebooks
		$phonebooks = array();
		if (!empty($device['phonebooks'])) foreach ($device['phonebooks'] as $phonebookentryid=>$phonebook) {
			$phonebooks[] = '\''.$db->escapeSimple($phonebookentryid).'\',\''.$db->escapeSimple($deviceid).'\',\''.$db->escapeSimple($phonebook['phonebookid']).'\'';
		}

		if (count($phonebooks) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_device_phonebooks (id, deviceid, phonebookid) VALUES (" . implode('),(', $phonebooks) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		// Device networks
		$networks = array();
		if (!empty($device['networks'])) foreach ($device['networks'] as $networkentryid=>$network) {
			$networks[] = '\''.$db->escapeSimple($networkentryid).'\',\''.$db->escapeSimple($deviceid).'\',\''.$db->escapeSimple($network['networkid']).'\'';
		}

		if (count($networks) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_device_networks (id, deviceid, networkid) VALUES (" . implode('),(', $networks) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		// Device external lines
		$externallines = array();
		if (!empty($device['externallines'])) foreach ($device['externallines'] as $externallineentryid=>$externalline) {
			$externallines[] = '\''.$db->escapeSimple($externallineentryid).'\',\''.$db->escapeSimple($deviceid).'\',\''.$db->escapeSimple($externalline['externallineid']).'\'';
		}

		if (count($externallines) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_device_externallines (id, deviceid, externallineid) VALUES (" . implode('),(', $externallines) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		// Device logos
		$logos = array();
		if (!empty($device['logos'])) foreach ($device['logos'] as $logoentryid=>$logo) {
			$logos[] = '\''.$db->escapeSimple($logoentryid).'\',\''.$db->escapeSimple($deviceid).'\',\''.$db->escapeSimple($logo['logoid']).'\'';
		}

		if (count($logos) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_device_logos (id, deviceid, logoid) VALUES (" . implode('),(', $logos) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		// Device alerts
		$alerts = array();
		if (!empty($device['alerts'])) foreach ($device['alerts'] as $alertentryid=>$alert) {
			$alerts[] = '\''.$db->escapeSimple($alertentryid).'\',\''.$db->escapeSimple($deviceid).'\',\''.$db->escapeSimple($alert['alertid']).'\'';
		}

		if (count($alerts) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_device_alerts (id, deviceid, alertid) VALUES (" . implode('),(', $alerts) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		// Device ringtones
		$ringtones = array();
		if (!empty($device['ringtones'])) foreach ($device['ringtones'] as $ringtoneentryid=>$ringtone) {
			$ringtones[] = '\''.$db->escapeSimple($ringtoneentryid).'\',\''.$db->escapeSimple($deviceid).'\',\''.$db->escapeSimple($ringtone['ringtoneid']).'\'';
		}

		if (count($ringtones) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_device_ringtones (id, deviceid, ringtoneid) VALUES (" . implode('),(', $ringtones) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		// Device statuses
		$statuses = array();
		if (!empty($device['statuses'])) foreach ($device['statuses'] as $statusentryid=>$status) {
			$statuses[] = '\''.$db->escapeSimple($statusentryid).'\',\''.$db->escapeSimple($deviceid).'\',\''.$db->escapeSimple($status['statusid']).'\'';
		}

		if (count($statuses) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_device_statuses (id, deviceid, statusid) VALUES (" . implode('),(', $statuses) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		// Device customapps
		$customapps = array();
		if (!empty($device['customapps'])) foreach ($device['customapps'] as $customappentryid=>$customapp) {
			$customapps[] = '\''.$db->escapeSimple($customappentryid).'\',\''.$db->escapeSimple($deviceid).'\',\''.$db->escapeSimple($customapp['customappid']).'\'';
		}

		if (count($customapps) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_device_customapps (id, deviceid, customappid) VALUES (" . implode('),(', $customapps) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		needreload();
	}

	public function read_extension_settings() {
		global $db;

		$extension_settings = array();
		$this->extension_settings = array();

		// Get extension settings;
		$sql = "SELECT extension, keyword, val FROM digium_phones_extension_settings ";
		$sql = $sql . "ORDER BY extension, keyword";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$l = $extension_settings[$row['extension']];
			$l['extension'] = $row['extension'];

			if ($row['keyword'] != null) {
				$l['settings'][$row['keyword']] = $row['val'];
			}

			$extension_settings[$row['extension']] = $l;
		}

		$this->extension_settings = $extension_settings;
	}

	public function update_extension_settings($line) {
		global $db;

		$sql = "DELETE FROM digium_phones_extension_settings WHERE extension = \"{$db->escapeSimple($line['extension'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$linesettings = array();
		foreach ($line['settings'] as $key=>$val) {
			if ($val != '') {
				$linesettings[] = '\''.$db->escapeSimple($line['extension']).'\',\''.$db->escapeSimple($key).'\',\''.$db->escapeSimple($val).'\'';
			}
		}

		if (count($linesettings) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_extension_settings (extension, keyword, val) VALUES (" . implode('),(', $linesettings) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		needreload();
	}

	/**
	 * Read in all the phonebook info from the database
	 */
	public function read_phonebooks() {
		global $db;

		$phonebooks = array();
		$this->phonebooks = array();
		
		$sql = "SELECT ps.id AS phonebookid, ps.name, pes.id AS entryid, pes.extension, pess.keyword, pess.val FROM digium_phones_phonebooks AS ps ";
		$sql = $sql . "  LEFT JOIN digium_phones_phonebook_entries AS pes ON (ps.id = pes.phonebookid) ";
		$sql = $sql . "  LEFT JOIN digium_phones_phonebook_entry_settings AS pess ON (pes.id = pess.phonebookentryid AND ps.id = pess.phonebookid) ";
		$sql = $sql . "ORDER BY ps.id, pes.id ";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$p = $this->phonebooks[$row['phonebookid']];

			$p['id'] = $row['phonebookid'];
			$p['name'] = $row['name'];
			if ($row['entryid'] != null) {
				$e = $p['entries'][$row['entryid']];

				$e['extension'] = $row['extension'];
				if ($row['keyword'] != null) {
					$e['settings'][$row['keyword']] = $row['val'];
				}

				$p['entries'][$row['entryid']] = $e;
			}

			$this->phonebooks[$row['phonebookid']] = $p;
		}
	}
	public function update_phonebook($phonebook) {
		$this->delete_phonebook($phonebook, false);
		$this->add_phonebook($phonebook);
	}

	public function delete_phonebook($phonebook, $deletefromdevice = true) {
		global $amp_conf;
		global $db;

		$phonebookid = $phonebook['id'];

		$this->phonebooks[$id] = $phonebook;

		if ($deletefromdevice) {
			$sql = "DELETE FROM digium_phones_device_phonebooks WHERE phonebookid = \"{$db->escapeSimple($phonebook['id'])}\"";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		$sql = "DELETE FROM digium_phones_phonebook_entry_settings WHERE phonebookid = \"{$db->escapeSimple($phonebook['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_phonebook_entries WHERE phonebookid = \"{$db->escapeSimple($phonebook['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_phonebooks WHERE id = \"{$db->escapeSimple($phonebook['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		if ($deletefromdevice) {
			unlink("{$amp_conf['ASTETCDIR']}/digium_phones/contacts-{$db->escapeSimple($phonebook['id'])}.xml");
		}
		needreload();
	}

	public function add_phonebook($phonebook) {
		global $db;

		$phonebookid = $phonebook['id'];

		// Phonebooks
		$sql = "INSERT INTO digium_phones_phonebooks (id, name) VALUES(\"{$db->escapeSimple($phonebook['id'])}\", \"{$db->escapeSimple($phonebook['name'])}\")";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		if ($phonebookid == 0) {
			$sql = "SELECT LAST_INSERT_ID()";

			$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
			if (DB::IsError($results)) {
				die_issabelpbx($results->getDebugInfo());
				return false;
			}

			foreach ($results as $row) {
				$phonebookid = $row['LAST_INSERT_ID()'];
			}
		}

		$this->phonebooks[$id] = $phonebook;

		// Phonebook entries
		$entries = array();
		$settings = array();
		$newid = 0;
		if (!empty($phonebook['entries'])) foreach ($phonebook['entries'] as $entryid=>$entry) {
			if ($entry == null) {
				continue;
			}

			$entries[] = '\''.$db->escapeSimple($newid).'\',\''.$db->escapeSimple($phonebookid).'\',\''.$db->escapeSimple($entry['extension']).'\'';

			foreach ($entry['settings'] as $key=>$val) {
				if ($val != '') {
					$settings[] = '\''.$db->escapeSimple($phonebookid).'\',\''.$db->escapeSimple($newid).'\',\''.$db->escapeSimple($key).'\',\''.$db->escapeSimple($val).'\'';
				}
			}

			$newid++;
		}

		if (count($entries) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_phonebook_entries (id, phonebookid, extension) VALUES (" . implode('),(', $entries) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);

			if (count($settings) > 0) {
				$sql = "INSERT INTO digium_phones_phonebook_entry_settings (phonebookid, phonebookentryid, keyword, val) VALUES (" . implode('),(', $settings) . ")";
				$result = $db->query($sql);
				if (DB::IsError($result)) {
					echo $result->getDebugInfo();
					return false;
				}
				unset($result);
			}
		}

		needreload();
	}

	public function read_ringtones() {
		global $db;

		$ringtones = array();
		$this->ringtones = array();

		$sql = "(SELECT * FROM digium_phones_ringtones WHERE builtin = 1 ORDER BY id ASC) ";
		$sql.= "UNION ";
		$sql.= "(SELECT * FROM digium_phones_ringtones WHERE builtin = 0 ORDER BY id DESC)";
		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		$http_path = digium_phones_get_http_path();
		foreach ($results as $row) {
			if (!$row['builtin'] && !file_exists($http_path. 'user_ringtone_'.$row['id'].'.raw')) {
				$sql = 'DELETE FROM digium_phones_ringtones WHERE id = "'.$db->escapeSimple($row['id']).'"';
				$db->query($sql);
				continue;
			}
			$this->ringtones[$row['id']]['id'] = $row['id'];
			$this->ringtones[$row['id']]['name'] = $row['name'];
			$this->ringtones[$row['id']]['filename'] = $row['filename'];
		}

		unset($results);
	}

	public function add_ringtone($ringtone) {
		global $db;
		global $amp_conf;

		$sql = "INSERT INTO digium_phones_ringtones (id, name, filename) ";
		$sql.= "VALUES (NULL, '{$db->escapeSimple($ringtone['name'])}', '{$db->escapeSimple($ringtone['file']['name'])}')";
		$results = $db->query($sql);
		if(method_exists($db,'insert_id')) {
			$id = $db->insert_id();
		} else {
			$id = $amp_conf["AMPDBENGINE"] == "sqlite3" ? sqlite_last_insert_rowid($db->connection) : mysql_insert_id($db->connection);
		}
		if (DB::IsError($results)) {
			echo $results->getDebugInfo();
			return false;
		}
		unset($results);

		$http_path = digium_phones_get_http_path();
		if (!move_uploaded_file($ringtone['file']['tmp_name'], $http_path . "user_ringtone_".$id.".raw")) {
			?>
			<br>
			<span style="color: red; ">Uploaded file is not valid.</span>
			<br>
			<?php
		}

		needreload();
	}

	public function edit_ringtone($ringtone) {
		global $db;

		$sql = "UPDATE digium_phones_ringtones ";
		$sql.= "SET name = '{$db->escapeSimple($ringtone['name'])}' ";
		$sql.= "WHERE id = '{$db->escapeSimple($ringtone['id'])}'";
		$results = $db->query($sql);
		if (DB::IsError($results)) {
			echo $results->getDebugInfo();
			return false;
		}
		unset($results);

		needreload();
	}

	public function delete_ringtone($id) {
		global $amp_conf;
		global $db;

		$http_path = digium_phones_get_http_path();
		unlink($http_path . "user_ringtone_{$db->escapeSimple($id)}.raw");

		$sql = "DELETE FROM digium_phones_ringtones WHERE id = '{$db->escapeSimple($id)}'";
		$results = $db->query($sql);
		if (DB::IsError($results)) {
			echo $results->getDebugInfo();
			return false;
		}
		unset($results);

		needreload();
	}

	/**
	 * Initialize the firmware
	 */
	public function read_firmware() {
		if ($this->firmware_manager === NULL) {
			$this->firmware_manager = new digium_phones_firmware_manager($this);
		}
		$this->firmware_manager->refresh_packages();
	}

	/**
	 * Returns the firmware manager
	 */
	public function get_firmware_manager() {
		if ($this->firmware_manager === NULL) {
			$this->read_firmware();
		}
		return $this->firmware_manager;
	}

	public function get_locales() {
		global $db;

		if ($this->locales !== NULL) {
			return $this->locales;
		}

		$sql = "SELECT DISTINCT(`locale`) FROM digium_phones_voicemail_translations ORDER BY locale";
		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
		}

		$this->locales = array();
		foreach ($results as $row) {
			$this->locales[] = $row['locale'];
		}
		unset($results);
		return $this->locales;
	}

	public function get_voicemail_translations($locale) {
		global $db;

		if (isset($this->voicemail_translations[$locale])) {
			return $this->voicemail_translations[$locale];
		}

		$sql = "SELECT locale, keyword, val FROM digium_phones_voicemail_translations ";
		$sql .= "WHERE locale='{$db->escapeSimple($locale)}'";
		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return NULL;
		}

		if (count($results) === 0) {
			unset($results);
			return NULL;
		}

		$this->voicemail_translations[$locale] = array();
		foreach ($results as $row) {
			if ($row['keyword'] === 'IGNOREME') {
				// An ignored locale should never be returned as valid for the purposes
				// of voicemail translation tables
				unset($results);
				unset($this->voicemail_translations[$locale]);
				return NULL;
			}
			$this->voicemail_translations[$locale][$row['keyword']] = $row['val'];
		}
		unset($results);
		return $this->voicemail_translations[$locale];
	}

	public function read_alerts() {
		global $db;

		$alerts = array();
		$this->alerts = array();

		$sql = "SELECT alerts.id, alerts.name, alerts.alertinfo, alerts.type, alerts.ringtone AS ringtone_id, ringtones.name AS ringtone_name ";
		$sql.= "FROM digium_phones_alerts AS alerts ";
		$sql.= "LEFT OUTER JOIN digium_phones_ringtones AS ringtones ON alerts.ringtone = ringtones.id ";
		$sql.= "ORDER BY alerts.id";
		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$this->alerts[$row['id']]['id'] = $row['id'];
			$this->alerts[$row['id']]['name'] = $row['name'];
			$this->alerts[$row['id']]['alertinfo'] = $row['alertinfo'];
			$this->alerts[$row['id']]['type'] = $row['type'];
			$this->alerts[$row['id']]['ringtone_id'] = $row['ringtone_id'];
			$this->alerts[$row['id']]['ringtone_name'] = $row['ringtone_name'];
		}

		unset($results);
	}

	public function add_alert($alert) {
		global $db;

		$sql = "INSERT INTO digium_phones_alerts (name, alertinfo, type, ringtone) ";
		$sql.= "VALUES ('{$db->escapeSimple($alert['name'])}', '{$db->escapeSimple($alert['alertinfo'])}', '{$db->escapeSimple($alert['type'])}', '{$db->escapeSimple($alert['ringtone_id'])}')";
		$results = $db->query($sql);
		if (DB::IsError($results)) {
			echo $results->getDebugInfo();
			return false;
		}
		unset($results);

		needreload();
	}

	public function edit_alert($alert) {
		global $db;

		$sql = "UPDATE digium_phones_alerts SET ";
		$sql.= "name = '{$db->escapeSimple($alert['name'])}', ";
		$sql.= "alertinfo = '{$db->escapeSimple($alert['alertinfo'])}', ";
		$sql.= "type = '{$db->escapeSimple($alert['type'])}', ";
		$sql.= "ringtone = '{$db->escapeSimple($alert['ringtone_id'])}' ";
		$sql.= "WHERE id = '{$db->escapeSimple($alert['id'])}'";
		$results = $db->query($sql);
		if (DB::IsError($results)) {
			echo $results->getDebugInfo();
			return false;
		}
		unset($results);

		needreload();
	}

	public function delete_alert($id) {
		global $amp_conf;
		global $db;

		$sql = "DELETE FROM digium_phones_alerts WHERE id = '{$db->escapeSimple($id)}'";
		$results = $db->query($sql);
		if (DB::IsError($results)) {
			echo $results->getDebugInfo();
			return false;
		}
		unset($results);

		needreload();
	}

	/**
	 * Read in all the queue info from the database
	 */
	public function read_queues() {
		global $db;

		$queues = array();
		$this->queues = array();

		if (!function_exists('queues_list')) {
			return false;
		}
		$fqueues = queues_list();

		foreach ($fqueues as $queue) {
			$q = $this->queues[$queue[0]];
			$results = queues_get($queue[0]);
			if (empty($results)) {
				continue;
			}

			if ($q['id'] == null) {
				$q['id'] = $queue[0];
			}

			$q['name'] = $queue[1];

			foreach ($results['member'] as $member) {
				if (preg_match("/^(Local|Agent|SIP|DAHDI|ZAP|IAX2)\/([\d]+)(.*),([\d]+)$/", $member, $matches)) {
					$entry = $q['entries'][$matches[2]];
					$entry['location'] = $matches[1].'/'.$matches[2].$matches[3];
					$entry['dynamic'] = false;
					$entry['member'] = true;
					$q['entries'][$matches[2]] = $entry;
				}
			}

			$dynmembers = explode("\n", $results['dynmembers']);
			foreach ($dynmembers as $member) {
				if (preg_match("/^([\d]+),([\d]+)$/", $member, $matches)) {
					$entry = $q['entries'][$matches[1]];
					$entry['location'] = 'Local/'.$matches[1].'@from-queue/n';
					$entry['dynamic'] = true;
					$entry['member'] = true;
					$q['entries'][$matches[1]] = $entry;
				}
			}
			$this->queues[$queue[0]] = $q;
		}

		$sql = "SELECT * FROM digium_phones_queues ";
		$sql = $sql . "ORDER BY queueid, deviceid ";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$q = $this->queues[$row['queueid']];
			$q['id'] = $row['queueid'];

			$entry = $q['entries'][$row['deviceid']];
			$entry['deviceid'] = $row['deviceid'];
			$entry['permission'] = $row['permission'];
			$q['entries'][$row['deviceid']] = $entry;

			$this->queues[$row['queueid']] = $q;
		}
	}

	public function update_queue($queue) {
		$this->delete_queue($queue);
		$this->add_queue($queue);
	}

	public function delete_queue($queue) {
		global $amp_conf;
		global $db;

		$queueid = $queue['id'];
		$this->queues[$queueid] = $queue;

		$sql = "DELETE FROM digium_phones_queues WHERE queueid = \"{$db->escapeSimple($queue['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		needreload();
	}

	public function add_queue($queue) {
		global $db;

		$queueid = $queue['id'];
		$this->queues[$queueid] = $queue;

		$entries = array();
		if (!empty($queue['entries'])) {
			foreach ($queue['entries'] as $entryid=>$entry) {
				$entries[] = '\''.$db->escapeSimple($queueid).'\',\''.$db->escapeSimple($entry['deviceid']).'\',\''.$db->escapeSimple($entry['permission']).'\'';
			}
		}

		if (count($entries) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_queues (queueid, deviceid, permission) VALUES (" . implode('),(', $entries) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		needreload();
	}

	public function read_statuses() {
		global $db;

		$statuses = array();
		$this->statuses = array();

		$sql = "SELECT ss.id AS statusid, ss.name, sss.keyword, sss.val FROM digium_phones_statuses AS ss ";
		$sql = $sql . "  LEFT JOIN digium_phones_status_settings AS sss ON (ss.id = sss.statusid)";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$s = $this->statuses[$row['statusid']];
			$s['id'] = $row['statusid'];
			$s['name'] = $row['name'];
			if ($row['keyword'] != null) {
				$s['settings'][$row['keyword']] = $row['val'];
			}
			$this->statuses[$row['statusid']] = $s;
		}

		$sql = "SELECT ss.id AS statusid, ss.name, ses.id AS entryid, ses.text FROM digium_phones_statuses AS ss ";
		$sql = $sql . "  LEFT JOIN digium_phones_status_entries AS ses ON (ss.id = ses.statusid)";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$s = $this->statuses[$row['statusid']];
			$s['id'] = $row['statusid'];
			$s['name'] = $row['name'];
			if ($row['entryid'] != null) {
				$e = $row['text'];

				$s['entries'][$row['entryid']] = $e;
			}

			$this->statuses[$row['statusid']] = $s;
		}
	}

	public function update_status($status) {
		$this->delete_status($status, false);
		$this->add_status($status);
	}

	public function delete_status($status, $deletefromdevice = true) {
		global $amp_conf;
		global $db;

		$statusid = $status['id'];

		$this->statuses[$id] = $status;

		if ($deletefromdevice) {
			$sql = "DELETE FROM digium_phones_device_statuses WHERE statusid = \"{$db->escapeSimple($status['id'])}\"";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		$sql = "DELETE FROM digium_phones_status_settings WHERE statusid = \"{$db->escapeSimple($status['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_status_entries WHERE statusid = \"{$db->escapeSimple($status['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_statuses WHERE id = \"{$db->escapeSimple($status['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		needreload();
	}

	public function add_status($status) {
		global $db;

		$statusid = $status['id'];

		// Statuses
		$sql = "INSERT INTO digium_phones_statuses (id, name) VALUES(\"{$db->escapeSimple($status['id'])}\", \"{$db->escapeSimple($status['name'])}\")";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		if ($statusid == 0) {
			$sql = "SELECT LAST_INSERT_ID()";

			$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
			if (DB::IsError($results)) {
				die_issabelpbx($results->getDebugInfo());
				return false;
			}

			foreach ($results as $row) {
				$statusid = $row['LAST_INSERT_ID()'];
			}
		}

		$this->statuses[$id] = $status;

		// Status settings
		$statussettings = array();
		foreach ($status['settings'] as $key=>$val) {
			if ($val != '') {
				$statussettings[] = '\''.$db->escapeSimple($statusid).'\',\''.$db->escapeSimple($key).'\',\''.$db->escapeSimple($val).'\'';
			}
		}

		if (count($statussettings) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_status_settings (statusid, keyword, val) VALUES (" . implode('),(', $statussettings) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		$newid = 0;
		foreach ($status['entries'] as $entryid=>$entry) {
			if ($entry == null) {
				continue;
			}

			$entries[] = '\''.$db->escapeSimple($newid).'\',\''.$db->escapeSimple($statusid).'\',\''.$db->escapeSimple($entry).'\'';
			$newid++;
		}

		if (count($entries) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_status_entries (id, statusid, text) VALUES (" . implode('),(', $entries) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		needreload();
	}

	public function read_customapps() {
		global $db;

		$customapps = array();
		$this->customapps = array();

		$sql = "SELECT cs.id AS customappid, cs.name, css.keyword, css.val FROM digium_phones_customapps AS cs ";
		$sql = $sql . "  LEFT JOIN digium_phones_customapp_settings AS css ON (cs.id = css.customappid)";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		$http_path = digium_phones_get_http_path();
		foreach ($results as $row) {
			if (!file_exists($http_path . 'application_'.$row['customappid'].'.zip')) {
				$sql = 'DELETE FROM digium_phones_customapps WHERE id = "'.$db->escapeSimple($row['customappid']).'"';
				$db->query($sql);
				continue;
			}
			$s = $this->customapps[$row['customappid']];
			$s['id'] = $row['customappid'];
			$s['name'] = $row['name'];
			if ($row['keyword'] != null) {
				$s['settings'][$row['keyword']] = $row['val'];
			}
			$this->customapps[$row['customappid']] = $s;
		}
	}

	public function update_customapp($customapp) {
		$this->delete_customapp($customapp, false);
		$this->add_customapp($customapp);
	}

	public function delete_customapp($customapp, $deletefromdevice = true) {
		global $amp_conf;
		global $db;

		$customappid = $customapp['id'];

		$this->customapps[$id] = $customapp;

		if ($deletefromdevice) {
			unlink(digium_phones_get_http_path() . 'application_'.$customappid.'.zip');

			$sql = "DELETE FROM digium_phones_device_customapps WHERE customappid = \"{$db->escapeSimple($customapp['id'])}\"";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		$sql = "DELETE FROM digium_phones_customapp_settings WHERE customappid = \"{$db->escapeSimple($customapp['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_customapps WHERE id = \"{$db->escapeSimple($customapp['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		needreload();
	}

	public function add_customapp($customapp) {
		global $db;
		global $amp_conf;

		$customappid = $customapp['id'];

		// Custom Applications
		$sql = "INSERT INTO digium_phones_customapps (id, name) VALUES(\"{$db->escapeSimple($customapp['id'])}\", \"{$db->escapeSimple($customapp['name'])}\")";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		if ($customappid == 0) {
			$sql = "SELECT LAST_INSERT_ID()";

			$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
			if (DB::IsError($results)) {
				die_issabelpbx($results->getDebugInfo());
				return false;
			}

			foreach ($results as $row) {
				$customappid = $row['LAST_INSERT_ID()'];
			}
		}

		$this->customapps[$customappid] = $customapp;

		// Custom Application settings
		$customappsettings = array();
		foreach ($customapp['settings'] as $key=>$val) {
			if ($val != '') {
				$customappsettings[] = '\''.$db->escapeSimple($customappid).'\',\''.$db->escapeSimple($key).'\',\''.$db->escapeSimple($val).'\'';
			}
		}

		if (count($customappsettings) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_customapp_settings (customappid, keyword, val) VALUES (" . implode('),(', $customappsettings) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		$http_path = digium_phones_get_http_path();
		if (!move_uploaded_file($customapp['file']['tmp_name'], $http_path . "application_".$customappid.".zip")) {
			?>
			<br>
			<span style="color: red; ">Uploaded file is not valid.</span>
			<br>
			<?php
		}

		needreload();
	}

	public function read_networks() {
		global $db;

		$networks = array();
		$this->networks = array();

		$sql = "SELECT ns.id as networkid, ns.name, nss.keyword, nss.val FROM digium_phones_networks AS ns ";
		$sql = $sql . "  LEFT JOIN digium_phones_network_settings AS nss ON (ns.id = nss.networkid)";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$n = $this->networks[$row['networkid']];
			$n['id'] = $row['networkid'];
			$n['name'] = $row['name'];
			if ($row['keyword'] != null) {
				$n['settings'][$row['keyword']] = $row['val'];
			}

			if ($n['settings']['registration_address'] == '') {
				$n['settings']['registration_address'] = $this->get_general('mdns_address');
			}
			if ($n['settings']['registration_port'] == '') {
				$n['settings']['registration_port'] = $this->get_general('mdns_port');
			}
			if ($n['settings']['file_url_prefix'] == '' || 
				// also update deprecated path
				strstr($n['settings']['file_url_prefix'], '/admin/modules/digium_phones/firmware_package/')) {
				$n['settings']['file_url_prefix'] = digium_phones_get_http_path('http://' . $this->get_general('mdns_address'));
			}
			if ($n['settings']['ntp_server'] == '') {
				$n['settings']['ntp_server'] = "0.digium.pool.ntp.org";
			}
			if ($n['settings']['syslog_server'] == '') {
				$n['settings']['syslog_server'] = $this->get_general('mdns_address');
			}
			if ($n['settings']['syslog_port'] == '') {
				$n['settings']['syslog_port'] = "514";
			}
			if ($n['settings']['sip_dscp'] == '') {
				$n['settings']['sip_dscp'] = "24";
			}
			if ($n['settings']['rtp_dscp'] == '') {
				$n['settings']['rtp_dscp'] = "46";
			}

			$this->networks[$row['networkid']] = $n;
		}
	}

	public function update_network($network) {
		$this->delete_network($network, false);
		$this->add_network($network);
	}

	public function delete_network($network, $deletefromdevice = true) {
		global $amp_conf;
		global $db;

		$networkid = $network['id'];

		$this->networks[$networkid] = $network;

		if ($deletefromdevice) {
			$sql = "DELETE FROM digium_phones_device_networks WHERE networkid = \"{$db->escapeSimple($network['id'])}\"";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		$sql = "DELETE FROM digium_phones_network_settings WHERE networkid = \"{$db->escapeSimple($network['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_networks WHERE id = \"{$db->escapeSimple($network['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		needreload();
	}

	public function add_network($network) {
		global $db;

		$networkid = $network['id'];

		// networks
		$sql = "INSERT INTO digium_phones_networks (id, name) VALUES(\"{$db->escapeSimple($network['id'])}\", \"{$db->escapeSimple($network['name'])}\")";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		if ($networkid == 0) {
			$sql = "SELECT LAST_INSERT_ID()";

			$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
			if (DB::IsError($results)) {
				die_issabelpbx($results->getDebugInfo());
				return false;
			}

			foreach ($results as $row) {
				$networkid = $row['LAST_INSERT_ID()'];
			}
		}

		$this->networks[$networkid] = $network;

		// Network settings
		$networksettings = array();
		foreach ($network['settings'] as $key=>$val) {
			if ($val != '') {
				$networksettings[] = '\''.$db->escapeSimple($networkid).'\',\''.$db->escapeSimple($key).'\',\''.$db->escapeSimple($val).'\'';
			}
		}

		if (count($networksettings) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_network_settings (networkid, keyword, val) VALUES (" . implode('),(', $networksettings) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		needreload();
	}

	public function read_externallines() {
		global $db;

		$externallines = array();
		$this->externallines = array();

		$sql = "SELECT ns.id as externallineid, ns.name, elss.keyword, elss.val FROM digium_phones_externallines AS ns ";
		$sql = $sql . "  LEFT JOIN digium_phones_externalline_settings AS elss ON (ns.id = elss.externallineid)";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$n = $this->externallines[$row['externallineid']];
			$n['id'] = $row['externallineid'];
			$n['name'] = $row['name'];
			if ($row['keyword'] != null) {
				$n['settings'][$row['keyword']] = $row['val'];
			}

			$this->externallines[$row['externallineid']] = $n;
		}
	}

	public function update_externalline($externalline) {
		$this->delete_externalline($externalline, false);
		$this->add_externalline($externalline);
	}

	public function delete_externalline($externalline, $deletefromdevice = true) {
		global $amp_conf;
		global $db;

		$externallineid = $externalline['id'];

		$this->externallines[$externallineid] = $externalline;

		if ($deletefromdevice) {
			$sql = "DELETE FROM digium_phones_device_externallines WHERE externallineid = \"{$db->escapeSimple($externalline['id'])}\"";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		$sql = "DELETE FROM digium_phones_externalline_settings WHERE externallineid = \"{$db->escapeSimple($externalline['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_externallines WHERE id = \"{$db->escapeSimple($externalline['id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		needreload();
	}

	public function add_externalline($externalline) {
		global $db;

		$externallineid = $externalline['id'];

		// external lines
		$sql = "INSERT INTO digium_phones_externallines (id, name) VALUES(\"{$db->escapeSimple($externalline['id'])}\", \"{$db->escapeSimple($externalline['name'])}\")";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		if ($externallineid == 0) {
			$sql = "SELECT LAST_INSERT_ID()";

			$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
			if (DB::IsError($results)) {
				die_issabelpbx($results->getDebugInfo());
				return false;
			}

			foreach ($results as $row) {
				$externallineid = $row['LAST_INSERT_ID()'];
			}
		}

		$this->externallines[$externallineid] = $externalline;

		// externalline settings
		$externalline_settings = array();
		foreach ($externalline['settings'] as $key=>$val) {
			if ($val != '') {
				$externalline_settings[] = '\''.$db->escapeSimple($externallineid).'\',\''.$db->escapeSimple($key).'\',\''.$db->escapeSimple($val).'\'';
			}
		}

		if (count($externalline_settings) > 0) {
			/* Multiple INSERT */
			$sql = "INSERT INTO digium_phones_externalline_settings (externallineid, keyword, val) VALUES (" . implode('),(', $externalline_settings) . ")";
			$result = $db->query($sql);
			if (DB::IsError($result)) {
				echo $result->getDebugInfo();
				return false;
			}
			unset($result);
		}

		needreload();
	}

	public function read_logos() {
		global $db;

		$logos = array();
		$this->logos = array();

		$sql = "SELECT * FROM digium_phones_logos ORDER BY id";

		$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($results)) {
			die_issabelpbx($results->getDebugInfo());
			return false;
		}

		foreach ($results as $row) {
			$s = $this->logos[$row['id']];
			$s['id'] = $row['id'];
			$s['name'] = $row['name'];
			$s['model'] = $row['model'];

			$this->logos[$row['id']] = $s;
		}
	}

	public function add_logo($logo) {
		global $db;

		$sql = "INSERT INTO digium_phones_logos (name, model) VALUES(\"{$db->escapeSimple($logo['logo_name'])}\", \"{$db->escapeSimple($logo['logo_model'])}\")";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		// logo is moved to ASTETCDIR/digium_phones in digium_phones/views/digium_phones_logos.php

		needreload();
	}

	public function edit_logos($logo) {
		global $db;

		$sql = "UPDATE digium_phones_logos SET name=\"{$db->escapeSimple($logo['logo_name'])}\", model=\"{$db->escapeSimple($logo['logo_model'])}\" WHERE id=\"{$db->escapeSimple($logo['logo_id'])}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		// logo is moved to ASTETCDIR/digium_phones in digium_phones/views/digium_phones_logos.php

		needreload();
	}

	public function delete_logo($logo_id) {
		global $amp_conf;
		global $db;

		// remove from db
		$sql = "DELETE FROM digium_phones_logos WHERE id = \"{$db->escapeSimple($logo_id)}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		// remove from disk
		unlink($amp_conf['ASTETCDIR']."/digium_phones/user_image_{$db->escapeSimple($logo_id)}.png");

		needreload();

	}
}

