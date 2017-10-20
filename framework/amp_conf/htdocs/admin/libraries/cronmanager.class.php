<?php
if(!class_exists('notifications')) {
	include('notifications.class.php');
}
class cronmanager {
	/**
	 * note: time is the hour time of day a job should run, -1 indicates don't care
	 */

	function &create(&$db) {
		static $obj;
		if (!isset($obj)) {
			$obj = new cronmanager($db);
		}
		return $obj;
	}

	function cronmanager(&$db) {
		$this->_db =& $db;
	}

	function save_email($address) {
		$address = q($address);
		sql("DELETE FROM admin WHERE variable = 'email'");
		sql("INSERT INTO admin (variable, value) VALUES ('email', $address)");
	}

	function get_email() {
		$sql = "SELECT value FROM admin WHERE variable = 'email'";
		return sql($sql, 'getOne');
	}

	function save_hash($id, &$string) {
		$hash = md5($string);
		$id = q($id);
		sql("DELETE FROM admin WHERE variable = $id");
		sql("INSERT INTO admin (variable, value) VALUE ($id, '$hash')");
	}

	function check_hash($id, &$string) {
		$id = q($id);
		$sql = "SELECT value FROM admin WHERE variable = $id LIMIT 1";
		$hash = sql($sql, "getOne");
		return ($hash == md5($string));
	}

	function enable_updates($freq=24) {
		global $amp_conf;

		$night_time = array(19,20,21,22,23,0,1,2,3,4,5);
		$run_time = $night_time[rand(0,10)];
		$command = $amp_conf['AMPBIN']."/module_admin listonline";
		$lasttime = 0;

		$sql = "SELECT * FROM cronmanager WHERE module = 'module_admin' AND id = 'UPDATES'";
		$result = sql($sql, "getAll",DB_FETCHMODE_ASSOC);
		if (count($result)) {
			$sql = "UPDATE cronmanager SET
			          freq = '$freq',
							  command = '$command'
						  WHERE
						    module = 'module_admin' AND id = 'UPDATES'
			       ";
		} else {
			$sql = "INSERT INTO cronmanager
		        	(module, id, time, freq, lasttime, command)
							VALUES
							('module_admin', 'UPDATES', '$run_time', $freq, 0, '$command')
						";
		}
		sql($sql);
		$nt =& notifications::create($db);
		$nt->delete('core', 'UPDATES_OFF');

		$issabelpbx_conf =& issabelpbx_conf::create();
		//Make sure we don't set the value again because we dont need to do that
		//also to prevent against loops
		if(!$issabelpbx_conf->get_conf_setting('CRONMAN_UPDATES_CHECK')) {
			$issabelpbx_conf->set_conf_values(array('CRONMAN_UPDATES_CHECK' => true),true,true);
		}
	}

	function disable_updates() {
		global $db;
		sql("DELETE FROM cronmanager WHERE module = 'module_admin' AND id = 'UPDATES'");

		$nt =& notifications::create($db);
		$text = _("Online Updates are Disabled");
		$extext = _("Online updates are disabled in Advanced Settings. When disabled, you will not be notified of bug fixes and Security issues without manually checking for updates online. You are advised to enable the update checking. Updates are never downloaded automatically, they are only checked and reported in the notification panel and log if enabled.");
		$nt->add_notice('core', 'UPDATES_OFF', $text, $extext, '', true, true);

		$issabelpbx_conf =& issabelpbx_conf::create();
		//Make sure we don't set the value again because we dont need to do that
		//also to prevent against loops
		if($issabelpbx_conf->get_conf_setting('CRONMAN_UPDATES_CHECK')) {
			$issabelpbx_conf->set_conf_values(array('CRONMAN_UPDATES_CHECK' => false),true,true);
		}
	}

	function updates_enabled() {
		global $amp_conf;
		return !empty($amp_conf['CRONMAN_UPDATES_CHECK']) ? true : false;
	}

	/** run_jobs()
	 *  select all entries that need to be run now and run them, then update the times.
	 *
	 *  1. select all entries
	 *  2. foreach entry, if its paramters indicate it should be run, then run it and
	 *     update it was run in the time stamp.
	 */
	function run_jobs() {

		$errors = 0;
		$error_arr = array();

		$now = time();
		$jobs = sql("SELECT * FROM cronmanager","getAll", DB_FETCHMODE_ASSOC);
		foreach ($jobs as $job) {
			$nexttime = $job['lasttime'] + $job['freq']*3600;
			if ($nexttime <= $now) {
				if ($job['time'] >= 0 && $job['time'] < 24) {
					$date_arr = getdate($now);
					// Now if lasttime is 0, then we want this kicked off at the proper hour
					// after wich the frequencey will set the pace for same time each night
					//
					if (($date_arr['hours'] != $job['time']) && !$job['lasttime']) {
						continue;
					}
				}
			} else {
				// no need to run job, time is not up yet
				continue;
			}
			// run the job
			exec($job['command'],$job_out,$ret);
			if ($ret) {
				$errors++;
				$error_arr[] = array($job['command'],$ret);

				// If there where errors, let's print them out in case the script is being debugged or running
				// from cron which will then put the errors out through the cron system.
				//
				foreach ($job_out as $line) {
					echo $line."\n";
				}
			} else {
				$module = $job['module'];
				$id =     $job['id'];
				$sql = "UPDATE cronmanager SET lasttime = $now WHERE module = '$module' AND id = '$id'";
				sql($sql);
			}
		}
		if ($errors) {
			$nt =& notifications::create($db);
			$text = sprintf(_("Cronmanager encountered %s Errors"),$errors);
			$extext = _("The following commands failed with the listed error");
			foreach ($error_arr as $item) {
				$extext .= "<br />".$item[0]." (".$item[1].")";
			}
			$nt->add_error('cron_manager', 'EXECFAIL', $text, $extext, '', true, true);
		}
	}
}
