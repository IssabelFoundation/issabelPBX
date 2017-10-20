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

require_once dirname(__FILE__).'/digium_phones_firmware.php';

/**
 * A firmware package object. Note that a firmware package object
 * always consists of multiple firmware objects.
 */
class digium_phones_firmware_package {

	/**
	 * Constructor
	 * @param string $name The name of the firmware package.
	 * @param string $file_path The full path to the directory that contains the firmware
	 * @param string $version The version of firmware for this package
	 * @param string $uid A unique identifier. If the empty string or NULL, a unique ID will be generated.
	 */
	public function __construct($name, $file_path, $version, $uid) {
		if ($uid === '' or $uid === null) {
			$uid = uniqid('package_', true);
		}
		$this->unique_id = $uid;
		$this->name = $name;
		$this->file_path = $file_path;
		$this->version = $version;
	}

	/**
	 * Retrieve all packages from the database
	 * @return array The packages in the database
	 */
	public static function retrieve_packages() {
		global $db;
		$packages = array();

		$sql = "SELECT * FROM digium_phones_firmware_packages ORDER BY name";
		$presults = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		if (DB::IsError($presults)) {
			die_issabelpbx($presults->getDebugInfo());
			return false;
		}

		foreach ($presults as $pindex => $prow) {
			$package = new digium_phones_firmware_package($prow['name'], $prow['file_path'], $prow['version'], $prow['unique_id']);

			// Bail if the location doesn't exist.
			if (!file_exists($package->get_file_path())) {

				// instead of complaining, just delete the missing package
				//echo "The firmware package location ".$package->get_file_path()." does not exist.";
				$sql = "DELETE from digium_phones_firmware_packages where unique_id=\"{$package->unique_id}\";";
				$result = $db->query($sql);
				if (DB::IsError($presults)) {
					die_issabelpbx($presults->getDebugInfo());
					return false;
				}
				$sql = "DELETE from digium_phones_firmware where package_id=\"{$package->unique_id}\";";
				$result = $db->query($sql);
				if (DB::IsError($presults)) {
					die_issabelpbx($presults->getDebugInfo());
					return false;
				}
				unset($package);
				unset($presults[$pindex]);
				continue;
			}

			$sql = "SELECT * FROM digium_phones_firmware WHERE package_id=\"{$package->unique_id}\" ORDER BY file_name";
			$fresults = $db->getAll($sql, DB_FETCHMODE_ASSOC);
			if (DB::IsError($fresults)) {
				die_issabelpbx($fresults->getDebugInfo());
				return array();
			}
			foreach ($fresults as $frow) {
				$firmware = new digium_phones_firmware($frow['file_name'], $frow['phone_model'], $frow['unique_id'], $frow['package_id']);
				$package->firmware[] = $firmware;
			}
			$packages[] = $package;
			unset($fresults);
		}
		unset($presults);
		return $packages;
	}

	public function get_firmware() {
		return $this->firmware;
	}

	/**
	 * Create a firmware and add it to this package
	 * @param string $name The name of the firmware.
	 * @param string $file_path The full path to the firmware file.
	 * @param string $phone_model The type of phone model this firmware corresponds to.
	 * @return True on success, false on error
	 */
	public function create_firmware($file_name, $phone_model) {
		global $db;

		$file_path = $this->file_path.'/'.$file_name;
		if (!file_exists($file_path)) {
			echo "The firmware ".$file_path." does not exist.";
			return false;
		}
		$firmware = new digium_phones_firmware($file_name, $phone_model, '', $this->unique_id);
		$sql = "INSERT INTO digium_phones_firmware (unique_id, file_name, phone_model, package_id) VALUES(\"{$firmware->get_unique_id()}\", \"{$db->escapeSimple($firmware->get_file_name())}\", \"{$db->escapeSimple($firmware->get_phone_model())}\", \"{$this->get_unique_id()}\")";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);
		$this->firmware[] = $firmware;
		needreload();
		return true;
	}

	public function get_unique_id() {
		return $this->unique_id;
	}

	public function get_file_path() {
		return $this->file_path;
	}

	public function set_file_path($value) {
		global $db;

		if ($value === $this->file_path) {
			return true;
		}

		if (!file_exists($value)) {
			if (!mkdir($value)) {
				echo "Could not create directory $value";
				return false;
			}
		}
		foreach ($this->firmware as $firmware) {
			$new_path = $value.'/'.$firmware->get_file_name();
			$old_path = $this->file_path.'/'.$firmware->get_file_name();
			if (rename($old_path, $new_path) === false) {
				echo "Failed to move firmware ".$firmware->get_file_name();
				return false;
			}
		}

		$sql = "UPDATE digium_phones_firmware_packages SET file_path=\"{$db->escapeSimple($value)}\" WHERE unique_id=\"{$this->unique_id}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);
		@rmdir($this->file_path);
		$this->file_path = $value;
		needreload();
		return true;
	}

	public function get_name() {
		return $this->name;
	}

	public function set_name($value) {
		global $db;

		if ($value === $this->name) {
			return true;
		}

		$sql = "UPDATE digium_phones_firmware_packages SET name=\"{$db->escapeSimple($value)}\" WHERE unique_id=\"{$this->unique_id}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);
		$this->name = $value;
		needreload();
		return true;
	}

	public function get_version() {
		return $this->version;
	}

	public function set_version($value) {
		global $db;

		if ($value === $this->version) {
			return true;
		}

		$sql = "UPDATE digium_phones_firmware SET version=\"{$db->escapeSimple($value)}\" WHERE unique_id=\"{$this->unique_id}\"";
		$result = $db->query($sql);
		if (DB::IsError($result)) {
			echo $result->getDebugInfo();
			return false;
		}
		unset($result);
		$this->version = $value;
		needreload();
		return true;
	}

	/**
	 * Convert this package into conf file format
	 * @return string The package contents formatted as a sequence of [firmware] contexts
	 */
	public function to_conf() {
		$output = array();
		foreach ($this->firmware as $firmware) {
			$output[] = "[".$firmware->get_phone_model()."-".$this->version."]";
			$output[] = "type=firmware";
			$output[] = "model=".$firmware->get_phone_model();
			$output[] = "version=".$this->version;
			$output[] = "file=".basename($this->file_path).'/'.$firmware->get_file_name();
			$output[] = "\n";
		}
		return implode("\n", $output);
	}

	/**
	 * Return the firmware keys for a device
	 * @return string The firmware key/value pairs for a device
	 */
	public function to_device_conf() {
		$output = array();
		foreach ($this->firmware as $firmware) {
			// Note that this assumes that the phone firmware is stored
			// as a subdirectory of the firmware_package directory. If this
			// gets more complex, we'll need to parse this out more.
			$output[] = "firmware=".$firmware->get_phone_model()."-".$this->version;
		}
		return implode("\n", $output);
	}

	private $unique_id;
	private $name;
	private $version;
	private $file_path;
	private $firmware;
}

