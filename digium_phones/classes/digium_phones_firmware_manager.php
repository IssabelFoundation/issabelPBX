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

require_once dirname(__FILE__).'/digium_phones_firmware_package.php';

/**
 * An object that manages the firmware on the system.
 */
class digium_phones_firmware_manager {

	public function __construct($phones) {
		$this->packages = array();
		$this->versions = array();
		$this->phones = $phones;
		$this->error_msg = '';
	}

	/**
	 * Get the currently loaded firmware packages
	 * @return firmware_package An array of firmware package objects
	 */
	public function get_packages() {
		return $this->packages;
	}

	/**
	 * Refresh all firmware packages currently in the database
	 */
	public function refresh_packages() {
		global $db;

		$this->packages = digium_phones_firmware_package::retrieve_packages();
		foreach ($this->packages as $package) {
			if (!(in_array($package->get_version(), $this->versions))) {
				$this->versions[] = $package->get_version();
			}
		}
	}

	/**
	 * Create a new firmware package from a conf file.
	 * @param string $firmware_conf The full path and filename to the firmware conf file to load.
	 * @return A firmware object on success, NULL on failure
	 */
	public function create_package($firmware_conf) {
		global $db;

		$path_tokens = explode('/', $firmware_conf->get_directory());
		$package = new digium_phones_firmware_package($path_tokens[count($path_tokens) - 1],
			$firmware_conf->get_directory(),
			$firmware_conf->get_version(),
			'');
		foreach ($firmware_conf->get_contexts() as $key => $context) {
			$phone_model = trim(trim($key,"]"),"[");
			if (!($package->create_firmware($context['file'],
				$phone_model))) {
				echo 'Failed to create firmware for '.$context['file'];
				return NULL;
			}
		}
		$sql = "INSERT INTO digium_phones_firmware_packages (unique_id, name, version, file_path) VALUES(\"{$package->get_unique_id()}\", \"{$db->escapeSimple($package->get_name())}\", \"{$db->escapeSimple($package->get_version())}\", \"{$db->escapeSimple($package->get_file_path())}\")";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return NULL;
		}
		unset($result);
		$this->packages[] = $package;
		$this->versions[] = $package->get_version();
		needreload();
		return $package;
	}

	private function find_package_by_file_path($file_path) {
		foreach ($this->packages as $package) {
			if ($package->get_file_path() === $file_path) {
				return $package;
			}
		}
		return NULL;
	}

	/**
	 * Get a firmware by its unique id
	 * @return firmware_package NULL on error, firmware package object on success
	 */
	public function get_package_by_id($id) {
		foreach ($this->packages as $package) {
			if ($package->get_unique_id() === $id) {
				return $package;
			}
		}
		return NULL;
	}

	/**
	 * Get the package by its display name
	 * @param $name The name of the package
	 * @return firmware_package Package on success, NULL on failure
	 */
	public function get_package_by_name($name) {
		foreach ($this->packages as $package) {
			if ($package->get_name() === $name) {
				return $package;
			}
		}
		return NULL;
	}


	/**
	 * Take a location on the file system and synchronize the firmware residing there.
	 * Note that this assumes you have extracted a tarball containing a firmware conf
	 * file.
	 * @param $path Full path to the firmware
	 * @return boolean True on success, False on failure
	 */
	public function synchronize_file_location($path) {
		// See if we have a digium_phones_firmware.conf
		$conf_name = $path.'/digium_phones_firmware.conf';
		if (!file_exists($conf_name)) {
			$this->error_msg = 'Could not find digium_phones_firmware.conf in tarball';
			return false;
		}
		require_once dirname(__FILE__).'/digium_phones_firmware_conf.php';
		$conf_file = new digium_phones_firmware_conf($conf_name);
		if (!$conf_file) {
			$this->error_msg = 'Failed to load configuration file';
			return false;
		}
		$package = $this->create_package($conf_file);
		if (!$package) {
			$this->error_msg = 'Failed creating firmware package from configuration file';
			return false;
		}

		// move the firmware objects over to the downloadable path
		$http_path = digium_phones_get_http_path();
		$dest = $http_path . trim($package->get_name(), '/');
		if (!$package->set_file_path($dest)) {
			return false;
		}
		$this->error_msg = '';
		return true;
	}

	/**
	 * Delete a firmware package
	 * @param firmware_package $package The package object to delete
	 * @return True on success, False on failure
	 */
	public function delete_package($package) {
		global $db;

		$sql = "DELETE FROM digium_phones_firmware WHERE package_id=\"{$package->get_unique_id()}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		$sql = "DELETE FROM digium_phones_firmware_packages WHERE unique_id=\"{$package->get_unique_id()}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);

		// Remove the package from the devices
		$devices = $this->phones->get_devices();
		foreach ($devices as $device) {
			if (!in_array('settings', $device) or
				!in_array('firmware_package_id', $device['settings'])) {
				continue;
			}
			if ($device['settings']['firmware_package_id'] === $package->get_unique_id()) {
				unset($device['settings']['firmware_package_id']);
			}
		}

		foreach ($package->get_firmware() as $firmware) {
			unlink($package->get_file_path().'/'.$firmware->get_file_name());
		}
		rmdir($package->get_file_path());
		$pos = array_search($package, $this->packages);
		unset($this->packages[$pos]);
		needreload();
		return true;
	}

	public function version_exists($value) {
		foreach ($this->versions as $version) {
			if (strncmp($version, $value, strlen($value)) === 0) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Pull back the JSON object from the Digium server specifying
	 * what firmware is available
	 * @return array The JSON object.
	 */
	public function get_new_firmware_info() {
		$url = "http://downloads.digium.com/pub/telephony/res_digium_phone/firmware/dpma-firmware.json";
		$request = file_get_contents($url);
		$request = str_replace(array("\n", "\t"), "", $request);
		/* json_decode has been in PHP since 2006 */
		$json = json_decode($request, true);

		if ($json == null) {
			return null;
		}

		$json['tarball'] = str_replace('{version}', $json['version'], $json['tarball']);
		return $json;
	}

	/**
	 * Get the firmware version list, and remove versions already downloaded.
	 * This replaces get_new_firmware_info()
	 * @return contents of dpma-firmware.json as nested array
	 */
	public function get_firmware_version_info($dpma_version) {
		$url = "http://downloads.digium.com/pub/telephony/res_digium_phone/firmware/dpma-firmware.json";
		$request = file_get_contents($url);
		$json = json_decode($request, true);

		if (empty($json['versions'])) {
			$json['versions'] = null;
			return $json;
		}

		$dpma_version=explode('.', $dpma_version);
		$is_22_or_later = False;
		if ($dpma_version[0] > 2) {
			$is_22_or_later = True;
		} else if ($dpma_version[0] == 2 && $dpma_version[1] >= 2) {
			$is_22_or_later = True;
		}

		if ($is_22_or_later && !empty($json['versions2'])) {
			/* include 2.0 fw versions for 2.2 or later dpma */
			foreach (array_reverse($json['versions2']) as $version) {
				array_unshift($json['versions'], $version);
			}
		}

		foreach ($json['versions'] as $index => $version) {
			if ($this->version_exists($version['version'])) {
				unset($json['versions'][$index]);
			}
		}
		return $json;
	}


	/**
	 * Extract firmware archive and load (sync) it
	 * @param string $archive Path to the archive file (will be deleted)
	 * @return true if success, false and $this->error_msg if not
	 */
	public function untar_firmware_and_load($archive, $path = '/tmp') {
		$this->error_msg = '';
		$output = '';
		$exitcode = 0;

		$path = $path.'/digium_phones_'.time();
		mkdir($path);
		if (!is_dir($path)) {
			$this->error_msg = 'Failed to create temp directory '.$path;
			unlink($archive);
			return false;
		}

		exec('tar xf '.$archive.' -C '.$path.' 2>&1', $output, $exitcode);
		unlink($archive);
		if ($exitcode != 0) {
			$this->error_msg = 'Failed to extract archive: tar exited '.$exitcode;
			if ($output)
				$this->error_msg .= "\n".$output;
			return false;
		}

		// wack any combination of allowed extensions
		$expected_subdir = basename($archive, '.tar.gz');
		$expected_subdir = basename($expected_subdir, '.tgz');
		$expected_subdir = basename($expected_subdir, '.tar');
		$path_to_contents = $path . '/' . $expected_subdir;

		if (!is_dir($path_to_contents)) {
			$this->error_msg = 'Failed to find directory in archive: '.$expected_subdir;
			return false;
		}

		$synced = $this->synchronize_file_location($path_to_contents);

		// remove anything leftover
		exec('rm -rf '.$path);

		return $synced;
	}

	private $versions;
	private $packages;
	private $phones;
}

