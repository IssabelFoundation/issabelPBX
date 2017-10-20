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
 * generate res_digium_phone_applications.conf file
 */
function res_digium_phone_applications($conf) {

	global $amp_conf;

	$output = array();
	$vm_apps = array();
	$translations = array();

	foreach ($conf->digium_phones->get_devices() as $deviceid=>$device) {
		if (isset($device['settings']['active_locale']) === FALSE) {
			$locale = $conf->digium_phones->get_general('active_locale');
		} else {
			$locale = $device['settings']['active_locale'];
		}

		$vm_app = 'voicemail';
		$require_password = FALSE;
		if (!empty($device['settings']['vm_require_pin']) && $device['settings']['vm_require_pin'] == 'yes') {
			$vm_app .= '-pin';
			$require_password = 'yes';
		}

		$table = $conf->digium_phones->get_voicemail_translations($locale);
		if ($table !== NULL) {
			$vm_app .= "-{$locale}";
		}

		// Output a voicemail app and its corresponding translation table only once.
		if (in_array($vm_app, $vm_apps)) {
			unset($table);
			continue;
		}
		$vm_apps[] = $vm_app;

		$output[] = "[{$vm_app}]";
		$output[] = "type=application";
		$output[] = "application=voicemail";
		if ($require_password) {
			$output[] = "require_password={$require_password}";
		}
		$translation = "translation-{$locale}";
		$output[] = "translation={$translation}";
		$output[] = "";

		if (!in_array($translation, $translations)) {
			$translations[] = $translation;
			$output[] = "[translation-{$locale}]";
			$output[] = "type=translation";
			if ($table !== NULL) {
				foreach ($table as $key=>$value) {
					$output[] = "{$key}={$value}";
				}
			}
			$output[] = "";
		}
		unset($table);
	}

	foreach ($conf->digium_phones->get_queues() as $queueid=>$queue) {
		if (empty($queue['entries'])) {
			continue;
		}
		foreach($queue['entries'] as $entry) {
			if ($entry['deviceid'] == null) {
				continue;
			}
			$output[] = "[queue-{$queueid}-{$entry['deviceid']}]";
			$output[] = "type=application";
			$output[] = "application=queue";
			$output[] = "queue={$queueid}";
			$output[] = "permission={$entry['permission']}";
			if ($entry['member'] == null) {
				$output[] = "member=false";
			} else {
				if ($entry['location'] != null) {
					$output[] = "location={$entry['location']}";
				}
				/* Try to find the toggle feature code and use that */
				$fcc = new featurecode('queues', 'que_toggle');
				$toggle = $fcc->getCodeActive();
				unset($fcc);
				if ($toggle != "") {
					$output[] = "login_exten={$toggle}{$queueid}@ext-queues";
					$output[] = "logout_exten={$toggle}{$queueid}@ext-queues";
				} else if ($amp_conf['GENERATE_LEGACY_QUEUE_CODES']) {
					$output[] = "login_exten={$queueid}*@ext-queues";
					$output[] = "logout_exten={$queueid}**@ext-queues";
				}
			}
			$output[] = "";
		}
	}

	if (function_exists('presencestate_list_get')) {
		foreach (digium_phones_presencestate_list() as $type => $status) {
			$busy = "no";
			if ($type == "dnd" ) {
				$busy = "yes";
			}
			$output[] = "[status-{$type}]";
			$output[] = "type=application";
			$output[] = "application=status";
			$output[] = "send486={$busy}";
			$output[] = "status={$type}";
			foreach ($status as $message) {
				$output[] = "substatus={$message}";
			}
			$output[] = "";
		}
	} else {
		foreach ($conf->digium_phones->get_statuses() as $statusid=>$status) {
			$output[] = "[status-{$statusid}]";
			$output[] = "type=application";
			$output[] = "application=status";

			foreach ($status['settings'] as $key=>$val) {
				$output[] = "{$key}={$val}";
			}

			foreach ($status['entries'] as $entry) {
				$output[] = "substatus={$entry}";
			}

			$output[] = "";
		}
	}

	$http_path = digium_phones_get_http_path();
	foreach ($conf->digium_phones->get_customapps() as $customappid=>$customapp) {
		$output[] = "[customapp-{$customappid}]";
		$output[] = "type=application";
		$output[] = "application=custom";
		$output[] = "name={$customapp['name']}";
		$output[] = "filename=application_{$customappid}.zip";
		$output[] = "md5sum=".md5_file($http_path . "application_{$customappid}.zip");

		foreach ($customapp['settings'] as $key=>$val) {
			$output[] = "{$key}={$val}";
		}

		$output[] = "";
	}

	return implode("\n", $output);
}

