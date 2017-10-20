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
 * A firmware configuration file that comes with a tarball.
 */
class digium_phones_firmware_conf {

	/**
	 * Constructor
	 * @param string @path Full path to the firmware config file to load
	 */
	public function __construct($path) {
		$this->path = $path;
		$this->contexts = array();
		if (file_exists($path)) {
			$this->read_file();
		}
	}

	/**
	 * Get the directory location of the conf file
	 * @return string The path to the conf file
	 */
	public function get_directory() {
		return dirname($this->path);
	}

	/**
	 * Get the last version read from the file.
	 * @return string The version of the config file.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Add firmware for some digium phone type
	 * @param string $phone_type The model of phone the firmware is for
	 * @param string $version The version of firmware
	 * @param string $file The name of the firmware file
	 */
	public function add_firmware($phone_type, $version, $file) {
		if (in_array($phone_type, $this->contexts)) {
			unset($this->contexts[$phone_type]);
		}
		$context = array();
		$context['version'] = $version;
		$context['file'] = $file;
		$this->contexts[$phone_type] = $context;
		$this->version = $version;
	}

	/**
	 * Get the conf file contexts
	 * @return string The file contexts
	 */
	public function get_contexts() {
		return $this->contexts;
	}

	/**
	 * Synchronize the current contexts to disk
	 * @return boolean True on success, false on error
	 */
	public function synchronize() {
		if (count($this->contexts) != 0) {
			write_file();
			return true;
		}
		return false;
	}

	private function write_file() {
		$output = array();
		foreach ($this->contexts as $key => $context) {
			$output[] = $key;
			$output[] = 'version='.$context['version'];
			$output[] = 'file='.$context['file'];
			$output[] = '\n';
		}
		file_put_contents($this->path, implode('\n', $output));
	}

	private function read_file() {
		$file_contents = file($this->path, FILE_IGNORE_NEW_LINES);
		$context = '';
		$version = '';
		$file = '';
		foreach ($file_contents as $line) {
			if (strpos($line, '[') !== false) {
				$context = $line;
				continue;
			} elseif (($ind = strpos($line, '=')) !== false) {
				$type = substr($line, 0, $ind);
				if ($type == 'file') {
					$file = substr($line, $ind + 1);
				} elseif ($type == 'version') {
					$version = substr($line, $ind + 1);
				}
			}
			if ($context != '' and $version != '' and $file != '') {
				$this->add_firmware($context, $version, $file);
				$context = '';
				$version = '';
				$file = '';
			}
		}
	}

	private $contexts;
	private $path;
	private $version;
}

