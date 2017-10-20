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
 * generate xml contacts lists
 */
function res_digium_phone_general($conf) {

	global $amp_conf;

	$output = array();

	$output[] = "file_directory={$amp_conf['ASTETCDIR']}/digium_phones/";
	$output[] = "globalpin={$conf->digium_phones->get_general('globalpin')}";
	$output[] = "userlist_auth={$conf->digium_phones->get_general('userlist_auth')}";
	$output[] = "config_auth={$conf->digium_phones->get_general('config_auth')}";
	$output[] = "mdns_address={$conf->digium_phones->get_general('mdns_address')}";
	$output[] = "mdns_port={$conf->digium_phones->get_general('mdns_port')}";
	$output[] = "service_name={$conf->digium_phones->get_general('service_name')}";
	/* note: option firmware_package_directory is deprecated in dpma, but leaving this for now */
	$output[] = "firmware_package_directory=" . digium_phones_get_http_path();

	$output[] = "";

	return implode("\n", $output);
}
