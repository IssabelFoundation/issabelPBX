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

function digium_phones_contacts($conf, $internal, $extension) {

	global $amp_conf;

	$output = array();

	if ($internal == "internal-") {
		$phonebook = array();
		$phonebook['entries'] = array();
		$phonebook['name'] = $internal . $extension;

		foreach ($conf->sorted_users as $user) {
			$hasline = false;
			$device = $conf->digium_phones->get_device($extension);
			foreach ($device['lines'] as $lineid=>$line) {
				if ($line['extension'] == $user['id']) {
					$hasline = true;
					break;
				}
			}
			if (!$hasline) {
				$e = array();
				$e['extension'] = $user['id'];
				$e['settings']['type'] = 'internal';
				$phonebook['entries'][] = $e;
			}
		}
	} else {
		$phonebooks = $conf->digium_phones->get_phonebooks();
		$phonebook = $phonebooks[$extension];
	}

	$output[] = '<contacts ';
	$output[] = '  group_name="' . $phonebook['name'] . '"';
	$output[] = '  editable="0"';
	$output[] = '>';

	if (!empty($phonebook['entries']))
	foreach ($phonebook['entries'] as $entryid=>$entry) {
		$extension=$entry['extension'];

		if (!array_key_exists($extension,$conf->autohint)) {
			$conf->autohint[$extension] = false;
			foreach ($conf->digium_phones->get_devices() as $device) {
				foreach ($device['lines'] as $l) {
					if ($entry['extension'] == $l['extension']) {
						/* This is a Digium Phone. */
						$conf->autohint[$extension] = true;
					}
				}
			}
		}
		$customexten = false;

		if ($entry['settings']['type'] == 'internal') {
			$user = $conf->digium_phones->get_core_device($entry['extension']);
			if ($user != null) {
				$label = $user['description'];
			} else {
				$label = '';
			}
		} else {
			$customexten = true;

			$label = $entry['settings']['label'];
		}

		$line = $conf->digium_phones->get_extension_settings($entry['extension']);

		$output[] = '  <contact';

		if ($line != null) {
			$output[] = '    prefix="' . htmlspecialchars($line['settings']['prefix']) . '"';
			$output[] = '    first_name="' . htmlspecialchars(($line['settings']['first_name'] != null)?$line['settings']['first_name']:$label) . '"';
			$output[] = '    second_name="' . htmlspecialchars($line['settings']['second_name']) . '"';
			$output[] = '    last_name="' . htmlspecialchars($line['settings']['last_name']) . '"';
			$output[] = '    suffix="' . htmlspecialchars($line['settings']['suffix']) . '"';
			$output[] = '    organization="' . htmlspecialchars($line['settings']['organization']) . '"';
			$output[] = '    job_title="' . htmlspecialchars($line['settings']['job_title']) . '"';
			$output[] = '    location="' . htmlspecialchars($line['settings']['location']) . '"';
			$output[] = '    notes="' . htmlspecialchars($line['settings']['notes']) . '"';
		} else {
			$output[] = '    first_name="' . htmlspecialchars($label) . '"';
			$output[] = '    last_name=""';
			$output[] = '    organization=""';
		}

		if ($customexten == false) {
			// TODO: Not all contacts are SIP.  Or maybe it doesn't matter because it's SIP to Asterisk.  Who knows?
			$output[] = '    contact_type="sip"';
			$output[] = '    account_id="' . htmlspecialchars($entry['extension']) . '"';
			if ($conf->autohint[$extension]) {
				$output[] = '    subscribe_to="auto_hint_' . htmlspecialchars($entry['extension']) . '"';
			} else {
				$output[] = '    subscribe_to="' . htmlspecialchars($entry['extension']) . '"';
			}

			$user = $conf->digium_phones->get_core_user($entry['extension']);
			if ($user != null && $user['voicemail'] != null && $user['voicemail'] != "novm") {
				$output[] = '    has_voicemail="1"';
			}
                    if ($entry['settings']['can_intercom'] != null) {
                        $output[] = '    can_intercom="1"';
                    }
                    if ($entry['settings']['can_monitor'] != null) {
                        $output[] = '    can_monitor="1"';
                    }
                } else {
                    $output[] = '    contact_type="sip|external"';
                    if ($entry['settings']['subscribe_to'] != null && $entry['settings']['subscribe_to'] == 'on') {
                        if ($entry['settings']['subscription_url'] != null) {
                            $output[] = '    subscribe_to="' . htmlspecialchars($entry['settings']['subscription_url']) . '"';
                        } else {
                            $output[] = '    subscribe_to="' . htmlspecialchars($entry['extension']) . '"';
                        }
                    }
                }
                $output[] = '  >';

                $output[] = '    <numbers>';
                $output[] = '      <number dial="' . htmlspecialchars($entry['extension']) . '" label="Extension" primary="1" />';
                $output[] = '    </numbers>';

                if ($line != null) {
                    $output[] = '    <emails>';
                    $output[] = '      <email address="' . htmlspecialchars($line['settings']['email']) . '" label="Primary" primary="1" />';
                    $output[] = '    </emails>';
                }

                $output[] = '  </contact>';
            }

            $output[] = '</contacts>';
            $output[] = '';

            return implode("\n", $output);

}
