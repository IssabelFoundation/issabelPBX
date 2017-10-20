<?php

global $astman;
require_once dirname(__FILE__).'/classes/digium_phones.php';
$digium_phones = new digium_phones();
if (isset($_GET['digium_phones_form'])) {
	$page = $_GET['digium_phones_form'];
} else if (isset($_POST['digium_phones_form'])) {
	$page = $_POST['digium_phones_form'];
} else {
	$page = '';
}
$error = array();


/**
 * AJAX post will render devicephonebooks_-1 as devicephonebooks_[0]=1
 * instead of devicephonebooks[0]='-1'.  Duct taping can of worms here.
*/
if (!empty($_POST['devicephonebooks_'])) {

	if (empty($_POST['devicephonebooks']))
		$_POST['devicephonebooks']=array();

	array_unshift($_POST['devicephonebooks'],-1);
	unset($_POST['devicephonebooks_']);
}

if (!empty($_POST['devicenetworks_'])) {

	if (empty($_POST['devicenetworks']))
		$_POST['devicenetworks']=array();

	array_unshift($_POST['devicenetworks'],-1);
	unset($_POST['devicenetworks_']);
}


/**
 * The following if statements check for when a form has been submitted. There
 * are 2 possible forms: general, editline. These conditions
 * check for each form's submit button value. If none are true, then no form
 * has been submitted. Depending on if the information and updating are
 * successful, $_GET['digium_phones_form'] will be changed to reflect which page to
 * load. Properly submitted forms that update properly will result in returning
 * to the default digium_phones page. Errors with values or updating will result in
 * returning to the form page with the submitted information auto-filled.
 */
if (isset($_POST['general_submit'])) {
	$gen = array();
	foreach ($digium_phones->get_all_general() as $k=>$v) {
		if ( ! isset($_POST[$k])) {
			if (strpos($k, 'checkbox')) {
				$gen[$k] = 0;
			} else {
				$gen[$k] = $v;
			}
			continue;
		}

		$gen[$k] = htmlspecialchars($_POST[$k]);
	}

	$digium_phones->update_general($gen);
	$digium_phones->read_general();
} else if (isset($_POST['editdevice_submit'])) {
	$deviceid = $_POST['device'];

	$olddevice = $digium_phones->get_device($deviceid);

	$device = array();
	$device['settings'] = array();
	$device['lines'] = array();
	$device['phonebooks'] = array();
	$device['networks'] = array();
	$device['externallines'] = array();
	$device['logos'] = array();
	$device['alerts'] = array();
	$device['ringtones'] = array();
	$device['id'] = $deviceid;
	$device['name'] = $_POST['devicename'];
	$settings = array(
		'mac',
		'pin',
		'rapiddial',
		'name_format',
		'timezone',
		'web_ui_enabled',
		'record_own_calls',
		'blf_unused_linekeys',
		'ntp_resync',
		'active_ringtone',
		'login_password',
		'send_to_vm',
		'vm_require_pin',
		'accept_local_calls',
		'lock_preferences',
		'display_mc_notification',
		'brightness',
		'contrast',
		'backlight_dim_level',
		'dim_backlight',
		'backlight_timeout',
		'ringer_volume',
		'speaker_volume',
		'handset_volume',
		'headset_volume',
		'reset_call_volume',
		'headset_answer',
		'firmware_package_id',
		'active_locale',
		'default_fontsize',
		'call_waiting_tone'
	);

	foreach ($settings as $setting) {
		$device['settings'][$setting] = $_POST[$setting];
	}

	if (!empty($_POST['lines'])) foreach ($_POST['lines'] as $exten) {
		$line = array();
		if (substr($exten, 0, 9) == "external:") {
			$exten = substr($exten, 9);
			$line['externallineid'] = $exten;
			if (!empty($olddevice['externallines'])) foreach ($olddevice['externallines'] as $l) {
				if ($l['externallineid'] == $exten) {
					$line = $l;
					break;
				}
			}

			$device['externallines'][] = $line;
		} else {
			$line['extension'] = $exten;
			$line['settings'] = array();
			if (!empty($olddevice['lines'])) foreach ($olddevice['lines'] as $l) {
				if ($l['extension'] == $exten) {
					$line = $l;
					break;
				}
			}

			$device['lines'][] = $line;
		}
	}

	if (!empty($_POST['devicephonebooks'])) foreach ($_POST['devicephonebooks'] as $phonebookid) {
		$phonebook = array();
		$phonebook['phonebookid'] = $phonebookid;
		if (!empty($olddevice['phonebooks'])) foreach ($olddevice['phonebooks'] as $p) {
			if ($p['phonebookid'] == $phonebookid) {
				$phonebook = $p;
				break;
			}
		}
		$device['phonebooks'][] = $phonebook;
	}

	if (!empty($_POST['devicenetworks'])) foreach ($_POST['devicenetworks'] as $networkid) {
		$network = array();
		$network['networkid'] = $networkid;
		if (!empty($olddevice['networks'])) foreach ($olddevice['networks'] as $n) {
			if ($n['networkid'] == $networkid) {
				$network = $n;
				break;
			}
		}
		$device['networks'][] = $network;
	}

	if (!empty($_POST['devicelogos'])) foreach ($_POST['devicelogos'] as $logoid) {
		$logo = array();
		$logo['logoid'] = $logoid;
		if (!empty($olddevice['logos'])) foreach ($olddevice['logos'] as $l) {
			if ($l['logoid'] == $logoid) {
				$logo = $l;
				break;
			}
		}
		$device['logos'][] = $logo;
	}

	if (!empty($_POST['devicealerts'])) foreach ($_POST['devicealerts'] as $alertid) {
		$alert = array();
		$alert['alertid'] = $alertid;
		if (!empty($olddevice['alerts'])) foreach ($olddevice['alerts'] as $a) {
			if ($a['alertid'] == $alertid) {
				$alert = $a;
				break;
			}
		}
		$device['alerts'][] = $alert;
	}

	if (!empty($_POST['deviceringtones'])) foreach ($_POST['deviceringtones'] as $ringtoneid) {
		$ringtone = array();
		$ringtone['ringtoneid'] = $ringtoneid;
		if (!empty($olddevice['ringtones'])) foreach ($olddevice['ringtones'] as $a) {
			if ($a['ringtoneid'] == $ringtoneid) {
				$ringtone = $a;
				break;
			}
		}
		$device['ringtones'][] = $ringtone;
	}

	if (!empty($_POST['devicestatuses'])) foreach ($_POST['devicestatuses'] as $statusid) {
		$status = array();
		$status['statusid'] = $statusid;
		if (!empty($olddevice['statuses'])) foreach ($olddevice['statuses'] as $s) {
			if ($s['statusid'] == $statusid) {
				$status = $s;
				break;
			}
		}
		$device['statuses'][] = $status;
	}

	if (!empty($_POST['devicecustomapps'])) foreach ($_POST['devicecustomapps'] as $customappid) {
		$customapp = array();
		$customapp['customappid'] = $customappid;
		if (!empty($olddevice['customapps'])) foreach ($olddevice['customapps'] as $c) {
			if ($c['customappid'] == $customappid) {
				$customapp = $c;
				break;
			}
		}
		$device['customapps'][] = $customapp;
	}

	$digium_phones->update_device($device);
	$digium_phones->read_devices();
} else if (isset($_GET['deletedevice_submit'])) {
	$deviceid = $_GET['device'];

	$device = array();
	$device['id'] = $deviceid;
	$digium_phones->delete_device($device);
	$digium_phones->read_devices();
} else if (isset($_GET['reconfiguredevice_submit'])) {
	$deviceid = $_GET['device'];

	if ($deviceid == -1) {
		$response = $astman->send_request('Command',array('Command'=>"digium_phones reconfigure all"));
	} else {
		$response = $astman->send_request('Command',array('Command'=>"digium_phones reconfigure phone {$deviceid}"));
	}
} else if (isset($_POST['editphonebook_submit'])) {
	$phonebookid = $_POST['phonebook'];

	$oldphonebook = $digium_phones->get_phonebook($phonebookid);

	$phonebook = array();
	$phonebook['id'] = $phonebookid;
	$phonebook['name'] = $_POST['phonebookname'];
	$phonebook['entries'] = $oldphonebook['entries'];

	$digium_phones->update_phonebook($phonebook);
	$digium_phones->read_phonebooks();
} else if (isset($_POST['editphonebookentry_submit'])) {
	$phonebookid = $_POST['phonebook'];
	$entryid = $_POST['entry'];
	if ($entryid != null) {
		$phonebook = $digium_phones->get_phonebook($phonebookid);

		$e = $phonebook['entries'][$entryid];

		$e['extension'] = $_POST['extension'];

		$settings = array(
			'type',
		        'has_voicemail',
		        'can_intercom',
		        'can_monitor',
		        'subscribe_to',
		        'subscription_url',
		        'label'
		);
		foreach ($settings as $setting) {
			$e['settings'][$setting] = $_POST[$setting];
		}

		$phonebook['entries'][$entryid] = $e;

		$digium_phones->update_phonebook($phonebook);
		$digium_phones->read_phonebooks();
	}
} else if (isset($_GET['deletephonebook_submit'])) {
	$phonebookid = $_GET['phonebook'];

	$phonebook = array();
	$phonebook['id'] = $phonebookid;
	$digium_phones->delete_phonebook($phonebook);
	$digium_phones->read_phonebooks();
} else if (isset($_GET['deletephonebookentry_submit'])) {
	$phonebookid = $_GET['phonebook'];
	$entryid = $_GET['entry'];

	if ($entryid != null) {
		$phonebook = $digium_phones->get_phonebook($phonebookid);

		$phonebook['entries'][$entryid] = null;

		$digium_phones->update_phonebook($phonebook);
		$digium_phones->read_phonebooks();
	}
} else if (isset($_GET['movephonebookentry_submit'])) {
	$direction = $_GET['movephonebookentry_submit'];
	$phonebookid = $_GET['phonebook'];
	$entryid = $_GET['entry'];

	if ($entryid != null) {
		$phonebook = $digium_phones->get_phonebook($phonebookid);

		$entry = $phonebook['entries'][$entryid];
		if ($direction == 'up') {
			$upentry = $phonebook['entries'][$entryid - 1];
			$phonebook['entries'][$entryid - 1] = $entry;
			$phonebook['entries'][$entryid] = $upentry;
		} else {
			$downentry = $phonebook['entries'][$entryid + 1];
			$phonebook['entries'][$entryid + 1] = $entry;
			$phonebook['entries'][$entryid] = $downentry;
		}

		$digium_phones->update_phonebook($phonebook);
		$digium_phones->read_phonebooks();
	}
} else if (isset($_POST['editnetwork_submit'])) {
	$networkid = $_POST['network'];

	$network = array();
	$network['id'] = $networkid;
	$network['name'] = $_POST['networkname'];

	$settings = array(
		'cidr',
		'ntp_server',
		'registration_address',
		'registration_port',
		'file_url_prefix',
		'alternate_registration_address',
		'alternate_registration_port',
		'ntp_server',
		'syslog_level',
		'syslog_server',
		'syslog_port',
		'network_vlan_discovery_mode',
		'network_vlan_id',
		'network_vlan_qos',
		'pc_vlan_id',
		'pc_qos',
		'sip_dscp',
		'rtp_dscp'
	);
	foreach ($settings as $setting) {
		$network['settings'][$setting] = $_POST[$setting];
	}

	$digium_phones->update_network($network);
	$digium_phones->read_networks();
} else if (isset($_GET['deletenetwork_submit'])) {
	$networkid = $_GET['network'];

	$network = array();
	$network['id'] = $networkid;
	$digium_phones->delete_network($network);
	$digium_phones->read_networks();
} else if (isset($_POST['editqueue_submit'])) {
	$manager = explode(',', $_POST['tempManagers'][0]);

	foreach($manager as $managerID){
		$_POST['managers'][] = $managerID;
	}
	unset($_POST['tempManagers']);
	$queueid = $_POST['queue'];

	$queue = array();
	$queue['id'] = $queueid;

	if (!empty($_POST['managers'])) foreach ($_POST['managers'] as $manager) {
		$entry = array();
		$entry['deviceid'] = $manager;
		$entry['permission'] = "details";

		$queue['entries'][] = $entry;
	}

	if (!empty($_POST['permissions'])) foreach ($_POST['permissions'] as $deviceid=>$perm) {
		if ($perm == "none") {
			// There's no need to write this out.
			continue;
		}

		$entry = array();
		$entry['deviceid'] = $deviceid;
		$entry['permission'] = $perm;

		$queue['entries'][] = $entry;
	}

	$digium_phones->update_queue($queue);
	$digium_phones->read_queues();

} else if (isset($_POST['editstatus_submit'])) {
	$statusid = $_POST['statusid'];

	$status = array();
	$status['id'] = $statusid;
	$status['name'] = $_POST['statusname'];

	$status['entries'] = array();

	if (!empty($_POST['entries'])) foreach ($_POST['entries'] as $entry) {
		$status['entries'][] = $entry;
	}

	$settings = array(
		'status',
	        'send486'
	);
	foreach ($settings as $setting) {
		$status['settings'][$setting] = $_POST[$setting];
	}

	$digium_phones->update_status($status);
	$digium_phones->read_statuses();
} else if (isset($_GET['deletestatus_submit'])) {
	$statusid = $_GET['statusid'];

	$status = array();
	$status['id'] = $statusid;
	$digium_phones->delete_status($status);
	$digium_phones->read_statuses();
} else if (isset($_POST['editcustomapp_submit'])) {
	$customappid = $_POST['customappid'];

	$customapp = array();
	$customapp['id'] = $customappid;
	$customapp['name'] = $_POST['customappname'];
	$customapp['file'] = $_FILES['customappfile'];

	$settings = array(
	        'autostart'
	);
	foreach ($settings as $setting) {
		$customapp['settings'][$setting] = $_POST[$setting];
	}

	if (!empty($_POST['entries'])) foreach ($_POST['entries'] as $entry) {
		$kv = preg_split('/=/', $entry);

		if (in_array($kv[0], $settings)) {
			/* Don't let them override our setting names. */
			continue;
		}

		$customapp['settings'][$kv[0]] = $kv[1];
	}
	$digium_phones->update_customapp($customapp);
	$digium_phones->read_customapps();
} else if (isset($_GET['deletecustomapp_submit'])) {
	$customappid = $_GET['customappid'];

	$customapp = array();
	$customapp['id'] = $customappid;
	$digium_phones->delete_customapp($customapp);
	$digium_phones->read_customapps();
} else if (isset($_POST['logo_upload_submit'])) {
	$logo = array();
	$logo['logo_name'] = $_POST['logo_name'];
	$logo['logo_model'] = $_POST['logo_model'];
	$logo['logo_upload'] = $_POST['logo_upload'];
	$digium_phones->add_logo($logo);
	$digium_phones->read_logos();
} else if (isset($_POST['edit_logo_upload_submit'])) {
	$logo = array();
	$logo['logo_name'] = $_POST['edit_logo_name'];
	$logo['logo_model'] = $_POST['edit_logo_model'];
	$logo['logo_upload'] = $_POST['edit_logo_upload'];
	$logo['logo_id'] = $_POST['edit_logo_id'];
	$digium_phones->edit_logos($logo);
	$digium_phones->read_logos();
} else if (isset($_GET['deletepng'])) {
	$digium_phones->delete_logo(digium_phones_sanitize_filepath($_GET['deletepng']));
	$digium_phones->read_logos();
} else if (isset($_POST['ringtoneAddSubmit'])) {
        $ringtone = array();
        $ringtone['name'] = $_POST['ringtoneAddName'];
        $ringtone['file'] = $_FILES['ringtoneUpload'];
        $digium_phones->add_ringtone($ringtone);
        $digium_phones->read_ringtones();
} else if (isset($_POST['ringtoneEditSubmit'])) {
        $ringtone = array();
        $ringtone['id'] = $_POST['ringtoneEditId'];
        $ringtone['name'] = $_POST['ringtoneEditName'];
        $digium_phones->edit_ringtone($ringtone);
        $digium_phones->read_ringtones();
} else if (isset($_POST['ringtoneDelSubmit'])) {
        $digium_phones->delete_ringtone(digium_phones_sanitize_filepath($_POST['hiddenIdDel']));
        $digium_phones->read_ringtones();
} else if (isset($_POST['editexternalline_submit'])) {
	$externallineid = $_POST['externalline'];

	$externalline = array();
	$externalline['id'] = $externallineid;
	$externalline['name'] = $_POST['linename'];

	$settings = array(
	        'userid',
	        'authname',
	        'secret',
	        'server_address',
		'server_port',
		'server_transport',
		'callerid',
		'register',
		'secondary_server_address',
		'secondary_server_port',
		'secondary_server_transport'
	);
	foreach ($settings as $setting) {
		$externalline['settings'][$setting] = $_POST[$setting];
	}

	$digium_phones->update_externalline($externalline);
	$digium_phones->read_externallines();

} else if (isset($_GET['deleteexternalline_submit'])) {
	$externallineid = $_GET['externalline'];
	$externalline = array();
	$externalline['id'] = $externallineid;
	$digium_phones->delete_externalline($externalline);
	$digium_phones->read_externallines();
} else if (isset($_POST['alertAddSubmit'])) {
	$alert = array();
	$alert['name'] = $_POST['alertAddName'];
	$alert['alertinfo'] = $_POST['alertAddAlertinfo'];
	$alert['type'] = $_POST['alertAddType'];
	$alert['ringtone_id'] = $_POST['alertAddRingtone'];
	$digium_phones->add_alert($alert);
	$digium_phones->read_alerts();
} else if (isset($_POST['alertEditSubmit'])) {
	$alert = array();
	$alert['id'] = $_POST['alertEditId'];
	$alert['name'] = $_POST['alertEditName'];
	$alert['alertinfo'] = $_POST['alertEditAlertinfo'];
	$alert['type'] = $_POST['alertEditType'];
	$alert['ringtone_id'] = $_POST['alertEditRingtoneId'];
	$digium_phones->edit_alert($alert);
	$digium_phones->read_alerts();
} else if (isset($_POST['alertDelSubmit'])) {
	$digium_phones->delete_alert($_POST['hiddenIdDel']);
	$digium_phones->read_alerts();
} else if (isset($_POST['editfirmware_submit'])) {
	$firmware_manager = $digium_phones->get_firmware_manager();
	$unique_id = $_POST['firmware_package_id'];
	$package = $firmware_manager->get_package_by_id($unique_id);
	if ($package != NULL) {
		$package->set_name($_POST['firmware_name']);
	}
} else if (isset($_GET['digium_phones_form']) and $_GET['digium_phones_form'] == 'firmware_edit'
	and isset($_GET['optype']) and $_GET['optype'] == 'delete_package') {
	$firmware_manager = $digium_phones->get_firmware_manager();
	$unique_id = $_GET['firmware_package_id'];
	$package = $firmware_manager->get_package_by_id($unique_id);
	if ($package != NULL) {
		$firmware_manager->delete_package($package);
	}
}

/**
 * Download the selected firmware and install package.
 * Shows progress indicator in iframe window.
 * @return error string describing problem or null
*/
function download_firmware($selected, $version_info, $firmware_manager) {
	$version = null;
	foreach ($version_info['versions'] as $entry) {
		if ($entry['version'] == $selected) {
			$version = $entry;
			break;
		}
	}
	if (!$version) {
		return 'selected version '.$selected.' not found in version table';
	}

	$tarball = str_replace('{version}', $version['version'], $version_info['tarball']);
	$url = $version_info['path'].$tarball;
	$md5sum = $version['md5sum'];

	if ($time_limit = ini_get('max_execution_time')) {
		set_time_limit($time_limit);
	}

	echo 'Downloading: <span id="downloadprogress"></span>';
	@ ob_flush();
	flush();

	$filename = digium_phones_get_http_path() . "/" . basename($url);
	$filedata = '';
	$download_chunk_size = 12 * 1024;

	$needdownload = true;

	if (file_exists($filename)) {
		if ($md5sum == md5_file($filename)) {
			$needdownload = false;
		} else {
			unlink($filename);
		}
	}

	if ($needdownload) {
		$headers = get_headers_assoc($url);
		if (empty($headers)) {
			return sprintf(_("Error opening %s for reading"), $url);
		}

		if (!$dp = @fopen($url,'r')) {
			return sprintf(_("Error opening %s for reading"), $url);
		}

		if (!($fp = @fopen($filename,"w"))) {
			return sprintf(_("Error opening %s for writing"), $filename);
		}

		$totalread = 0;
		$last_percent = -1;
		$max = $headers['content-length'];
		while (!feof($dp)) {
			$data = fread($dp, $download_chunk_size);
			fwrite($fp, $data);
			$totalread += strlen($data);

			$percent = round($totalread/$max*100);
			if ($percent != $last_percent) {
				$last_percent = $percent;
				$progress = $totalread.' of '.$max.' ('.$percent.'%)';
				echo '<script>document.getElementById(\'downloadprogress\').innerHTML = \''.$progress.'\';</script>';
				@ ob_flush();
				flush();
			}
		}
		fclose($dp);
		fclose($fp);

		if ($md5sum != md5_file($filename)) {
			unlink($filename);
			return sprintf(_("Checksum error retrieving %s"), $url);
		}
	}

	if (!$firmware_manager->untar_firmware_and_load($filename)) {
		return $firmware_manager->error_msg;
	}
	return;
}

if (isset($_GET['user_image'])) {
	global $amp_conf;
	$png_file = "{$amp_conf['ASTETCDIR']}/digium_phones/user_image_".basename($_GET['user_image']).".png";
	download_file($png_file);
} else if (isset($_POST['uploadfirmware_submit'])) {
	$allowed_exts = array('tar', 'gz', 'tgz');
	$original_filename = digium_phones_sanitize_filepath($_FILES['upload_firmware_location']['name']);
	$http_path = digium_phones_get_http_path();
	$archive  = digium_phones_sanitize_filepath($http_path . $original_filename);
	$temp_file = digium_phones_sanitize_filepath($_FILES['upload_firmware_location']['tmp_name']);
	$ext = end(explode('.', $original_filename));
	if ($_FILES["upload_firmware_location"]["error"] > 0) {
		echo "Error uploading file: " . htmlspecialchars($_FILES["upload_firmware_location"]["error"]);
	} else if (!in_array($ext, $allowed_exts)) {
		echo 'Error uploading file: '.htmlspecialchars($ext).' is not a valid extension.';
	} else if (!move_uploaded_file($temp_file, $archive)) {
		echo 'Error moving uploaded file to '.$archive;
	} else {
		$firmware_manager = $digium_phones->get_firmware_manager();
		if (!$firmware_manager->untar_firmware_and_load($archive)) {
			echo $firmware_manager->error_msg;
		} else {
			echo 'Firmware uploaded succesfully';
		}
	}
	echo '<p><a href="config.php?type=setup&display=digium_phones&digium_phones_form=firmware_edit">Return</a></p>';
} else if (isset($_GET['update_firmware'])) {
	$firmware_manager = $digium_phones->get_firmware_manager();
	$version_info = $firmware_manager->get_firmware_version_info($digium_phones->get_dpma_version());
	$versions = $version_info['versions'];

	if ($versions === null) {
		echo '<div><span class="failure">An error occurred while getting firmware list.</span></div>';
	} else if (!$versions) {
		echo '<div><span class="success">All available firmware versions already installed.</span></div>';
	} else if ($_GET['update_firmware'] == 'check') {
		echo '<div><span>Select firmware to download:<select id="firmware_select" name="firmware_select">';
		foreach ($versions as $version) {
			echo '<option value="'.$version['version'].'">'.$version['version'].' '.$version['date'].' for: '.$version['models'].'</option>';
		}
		echo '</select><br />';
		// jquery is not loaded inside iframe, so go oldschool javascript to get value
		echo '<input type="button" value="Download" onClick="parent.perform_download(document.getElementById(\'firmware_select\').value);"/>';
		echo '</div>';
	} else if ($_GET['update_firmware'] == 'download') {
		$error = download_firmware($_GET['version'], $version_info, $firmware_manager);
		if ($error) {
			echo '<div><span class="failure">'.$error.'</span></div>';
		} else {
			echo '<div><span class="success">Firmware downloaded successfully</span></div>';
		}
	} else {
		echo '<div><span class="failure">An error occurred while getting firmware list.</span></div>';
	}

	echo '<div class="btn_container">';
	echo '<input type="button" value="Close" onClick="parent.close_update_firmware(true);"/>';
	echo '</div>';

	flush();
} else {
?>
	<style scoped type="text/css">
		/*label { clear: both; display: block; float: left; margin-right: 5px;  text-align: right; width: 255px; }*/
		th { background: #7aa8f9; }
		tr.odd td { background: #fde9d1; }
		.alert { background: #fde9d1; border: 2px dashed red; margin: 5px; padding: 5px; }
		hr { width: 80%; margin-left: 0px; }
	</style>
	<script>
	function ChangeSelectByValue(dom_id, value, change) {
		var dom = document.getElementById(dom_id);
		if (!dom) return;
		for (var i = 0; i < dom.options.length; i++) {
			if (dom.options[i].value == value) {
				if (dom.selectedIndex != i) {
					dom.selectedIndex = i;
					//if (change)
					//	dom.onchange();
				}
				break;
			}
		}
	}
	</script>
<?php
	$easymode = ($digium_phones->get_general('easy_mode') == "yes"?true:false);
	include('views/rnav.php');
	echo '<div id="content">';
	$dpmalicensestatus = $astman->send_request('DPMALicenseStatus');
	if ($dpmalicensestatus == null || $dpmalicensestatus['Response'] != "Success") {
?>
		<br />
		<br />
		A valid license for res_digium_phones.so was not found.
		<br />
		<a href="config.php?type=setup&display=digiumaddons&page=add-license-form&addon=dpma">Obtain/register a license.</a>
<?php
	} else {
		/**
		 * The following switch statement determines what to render. This
		 * determination is dependent on the digium_phones_form variable.
		 */
		switch($page) {
		case 'phones_edit':
		default:
			require 'modules/digium_phones/views/digium_phones_phones.php';
			break;
		case 'phonebooks_edit':
			require 'modules/digium_phones/views/digium_phones_phonebooks.php';
			break;
		case 'networks_edit':
			require 'modules/digium_phones/views/digium_phones_networks.php';
			break;
		case 'externallines_edit':
			require 'modules/digium_phones/views/digium_phones_externallines.php';
			break;
		case 'applications_edit':
			require 'modules/digium_phones/views/digium_phones_applications.php';
			break;
		case 'application_queues_edit':
			require 'modules/digium_phones/views/digium_phones_applications.php';
			require 'modules/digium_phones/views/digium_phones_application_queues.php';
			break;
		case 'application_status_edit':
			require 'modules/digium_phones/views/digium_phones_applications.php';
			require 'modules/digium_phones/views/digium_phones_application_status.php';
			break;
		case 'application_custom_edit':
			require 'modules/digium_phones/views/digium_phones_applications.php';
			require 'modules/digium_phones/views/digium_phones_application_custom.php';
			break;
		case 'general_edit':
			require 'modules/digium_phones/views/digium_phones_general_settings.php';
			break;
		case 'firmware_edit':
			require 'modules/digium_phones/views/digium_phones_firmware.php';
			break;
		case 'logos_edit':
			require 'modules/digium_phones/views/digium_phones_logos.php';
			break;
		case 'alerts_edit':
			require 'modules/digium_phones/views/digium_phones_alerts.php';
			break;
		case 'ringtones_edit':
			require 'modules/digium_phones/views/digium_phones_ringtones.php';
			break;
		}
	}
	echo '</div>'; // content
}
?>
