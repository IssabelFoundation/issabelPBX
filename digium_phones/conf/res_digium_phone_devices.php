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
 * generate res_digium_phone_devices.conf file
 */
function res_digium_phone_devices($conf) {

	global $amp_conf;

	$queues = $conf->digium_phones->get_queues();
	$firmware_manager = $conf->digium_phones->get_firmware_manager();
	$default_locale = $conf->digium_phones->get_general('active_locale');
	$output = array();
	$doutput = array();
	$loutput = array();

	foreach ($conf->digium_phones->get_devices() as $deviceid=>$device) {
		$doutput[] = "[{$deviceid}]";
		$doutput[] = "type=phone";
		$doutput[] = "full_name={$device['name']}";

		/* collect which custom ringtones need to be loaded for this device */
		$ringtones = array();

		$parkext = "";
		if (function_exists('parking_getconfig')) {
			// Old and busted parking module
			$parking = parking_getconfig();
			if ($parking != null && $parking['parkingenabled'] != "" && $parking['parkext'] != "") {
				$parkext = $parking['parkext'];
			}
		} else if (function_exists('parking_get')) {
			// Fancy new 'park plus' module
			$parking = parking_get();
			if ($parking != null && $parking['parkext'] != "") {
				$parkext = $parking['parkext'];
			}
		}
		$doutput[] = "parking_exten={$parkext}";
		$doutput[] = "parking_transfer_type=blind";

		if (isset($device['settings']['active_locale']) === FALSE) {
			$locale = $default_locale;
		} else {
			$locale = $device['settings']['active_locale'];
		}
		$doutput[] = "active_locale={$locale}";

		$vm_app = 'voicemail';
		if (!empty($device['settings']['vm_require_pin']) && $device['settings']['vm_require_pin'] == 'yes') {
			$vm_app .= '-pin';
		}
		$table = $conf->digium_phones->get_voicemail_translations($locale);
		if ($table !== NULL) {
			$vm_app .= "-{$locale}";
			unset($table);
		}
		$doutput[] = "application={$vm_app}";

		if (!empty($device['settings'])) foreach ($device['settings'] as $key=>$val) {
			if ($key == 'rapiddial') {
				if ($val == '') {
					continue;
				}
				foreach ($conf->digium_phones->get_phonebooks() as $phonebook) {
					if ($val == $phonebook['id']) {
						if ($phonebook['id'] == -1) {
							$doutput[] = "contact=contacts-internal-{$device['id']}.xml";
							$doutput[] = "blf_contact_group=internal-{$device['id']}";
						} else {
							$doutput[] = "contact=contacts-{$phonebook['id']}.xml";
							$doutput[] = "blf_contact_group={$phonebook['name']}";
						}
						break;
					}
				}
				continue;
			} elseif ($key == 'firmware_package_id') {
				$package = $firmware_manager->get_package_by_id($val);
				if ($package !== null) {
					$doutput[] = $package->to_device_conf();
				} else {
					unset($device['settings'][$key]);
				}
				continue;
			} elseif ($key == 'active_ringtone') {
				$ringtone = $conf->digium_phones->get_ringtone($val);
				if ($ringtone != null) {
					if ($val < 0) {
						/* Builtin ringtone */
						$doutput[] = "active_ringtone={$ringtone['name']}";
					} else {
						$ringtones[$ringtone['id']] = true;
						$doutput[] = "active_ringtone=ringtone-{$ringtone['id']}";
					}
					continue;
				}
			} elseif ($key == 'vm_require_pin') {
				continue;
			}

			$doutput[] = "{$key}={$val}";
		}

		$doutput[] = "use_local_storage=yes";
		if (!empty($device['phonebooks'])) foreach ($device['phonebooks'] as $phonebook) {
			if ($phonebook['phonebookid'] == $device['settings']['rapiddial']) {
				continue;
			}
			if ($phonebook['phonebookid'] == -1) {
				$doutput[] = "contact=contacts-internal-{$device['id']}.xml";
			} else {
				$doutput[] = "contact=contacts-{$phonebook['phonebookid']}.xml";
			}
		}

		if (!empty($device['networks'])) foreach ($device['networks'] as $network) {
			$doutput[] = "network=network-{$network['networkid']}";
		}

		if (!empty($device['logos'])) foreach ($device['logos'] as $dl) {
			$logo = $conf->digium_phones->get_logo($dl['logoid']);

			$doutput[] = "{$logo['model']}_logo_file=user_image_{$logo['id']}.png";
		}
		if (!empty($device['alerts'])) foreach ($device['alerts'] as $alert) {
			$doutput[] = "alert=alert-{$alert['alertid']}";
			$alerts = $conf->digium_phones->get_alerts();
			$ringtone_id = $alerts[$alert['alertid']]['ringtone_id'];
			if ($ringtone_id > 0 ) {
				$ringtones[$alerts[$alert['alertid']]['ringtone_id']] = true;
			}
		}
		if (!empty($device['ringtones'])) foreach ($device['ringtones'] as $ringtone) {
			$ringtones[$ringtone['ringtoneid']] = true;
		}
		foreach ($ringtones as $id => $istrue) {
			$doutput[] = "ringtone=ringtone-{$id}";
		}
/*
		if ($conf->digium_phones->get_general('easy_mode') == "yes") {
			$doutput[] = "contact=contacts-internal-{$device['id']}.xml";
			$doutput[] = "blf_contact_group=internal-{$device['id']}";
		}
*/
		if (!empty($device['lines'])) foreach ($device['lines'] as $lineid=>$line) {
			$doutput[] = "line={$line['extension']}";
			$loutput[] = "[{$line['extension']}]";
			$loutput[] = "type=line";

			if ($line['user']['devicetype'] == "fixed") {
				$user = $conf->digium_phones->get_core_user($line['user']['user']);
				if ($user != null && $user['voicemail'] != null && $user['voicemail'] != "novm") {
					$loutput[] = "mailbox={$user['extension']}@{$user['voicemail']}";
				}
			}

			foreach ($line['settings'] as $key=>$val) {
				$loutput[] = "{$key}={$val}";
			}
			$loutput[] = "";
		}

		if (!empty($device['externallines'])) foreach ($device['externallines'] as $externalline) {
			$doutput[] = "external_line=externalline-{$externalline['externallineid']}";
		}

		foreach ($queues as $queueid=>$queue) {
			if (empty($queue['entries'])) {
				continue;
			}
			foreach ($queue['entries'] as $entry) {
				if ($entry['deviceid'] == $deviceid) {
					$doutput[] = "application=queue-{$queueid}-{$deviceid}";
				}
			}
		}

		if (function_exists('presencestate_list_get')) {
			foreach (digium_phones_presencestate_list() as $type => $status) {
				$doutput[] = "application=status-{$type}";
			}
		} else {
			if (!empty($device['statuses'])) foreach ($device['statuses'] as $status) {
				$doutput[] = "application=status-{$status['statusid']}";
			}
		}

		if (!empty($device['customapps'])) foreach ($device['customapps'] as $customapp) {
			$doutput[] = "application=customapp-{$customapp['customappid']}";
		}

		$doutput[] = "";
	}

	foreach ($conf->digium_phones->get_externallines() as $externallineid=>$externalline) {
		$loutput[] = "[externalline-{$externallineid}]";
		$loutput[] = "type=external_line";

		foreach ($externalline['settings'] as $key=>$val) {
			if ($key=='server_transport') {
				$key='transport';
			}
			$loutput[] = "{$key}={$val}";
		}
		$loutput[] = "";
	}
	foreach ($conf->digium_phones->get_networks() as $networkid=>$network) {
		$output[] = "[network-{$networkid}]";
		$output[] = "type=network";
		$output[] = "alias={$network['name']}";

		foreach ($network['settings'] as $key=>$val) {
			$output[] = "{$key}={$val}";
		}

		$output[] = "";
	}

	foreach ($conf->digium_phones->get_alerts() as $alertid=>$alert) {
		$output[] = "[alert-{$alertid}]";
		$output[] = "type=alert";
		$output[] = "alert_info={$alert['alertinfo']}";
		$output[] = "ring_type={$alert['type']}";
		if ($alert['ringtone_id'] < 0) {
			/* Builtin ringtone */
			$output[] = "ringtone={$alert['ringtone_name']}";
		} else {
			$output[] = "ringtone=ringtone-{$alert['ringtone_id']}";
		}

		$output[] = "";
	}

	foreach ($conf->digium_phones->get_ringtones() as $ringtoneid=>$ringtone) {
		if ($ringtoneid < 0) {
			continue;
		}

		$output[] = "[ringtone-{$ringtoneid}]";
		$output[] = "type=ringtone";
		$output[] = "alias={$ringtone['name']}";
		$output[] = "filename=user_ringtone_{$ringtoneid}.raw";

		$output[] = "";
	}

	return implode("\n", $doutput) . "\n" . implode("\n", $loutput) . "\n" . implode("\n", $output);
}

