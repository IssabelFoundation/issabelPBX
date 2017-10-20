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

/**
 * A firmware object. This controls the name of the actual firmware
 * file, which Digium phone model it applies to, and associates it with
 * a package.
 */
class digium_phones_firmware {

	/**
	 * Constructor
	 * @param string $name The name of the firmware
	 * @param string $file_path Full path to the firmware file
	 * @param string $phone_model The type of Digium phone this firmware applies to
	 * @param string $uid Unique identifier for the firmware. If '' or null, one will be generated.
	 * @param string $package_id Unique identifier for the package this firmware belongs to.
	 */
	public function __construct($file_name, $phone_model, $uid, $package_id) {
		if ($uid === '' or $uid === NULL) {
			$uid = uniqid('firmware_', true);
		}
		$this->unique_id = $uid;
		$this->file_name = $file_name;
		$this->phone_model = $phone_model;
	}

	public function get_file_name() {
		return $this->file_name;
	}

	public function get_phone_model() {
		return $this->phone_model;
	}

	public function get_unique_id() {
		return $this->unique_id;
	}

	public function get_package_id() {
		return $this->package_id;
	}

	private $file_name;
	private $unique_id;
	private $phone_model;
	private $package_id;
}

