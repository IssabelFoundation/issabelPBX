<?php

function queues_set_backup_cron() {
	global $amp_conf;

	//remove all stale backup's
	edit_crontab($amp_conf['AMPBIN'] . '/queue_reset_stats.php');

	//get our list of queues
	$queues_list = queues_list(true);
    $queues = array();
    foreach($queues_list as $key => $value) {
		//get queue details
		$queues[$value[0]] = queues_get($value[0],false);
	}

	foreach ($queues as $qid => $q) {


		$cron_vars = array('cron_minute', 'cron_hour', 'cron_dow', 'cron_month', 'cron_dom');
        	foreach ($cron_vars as $value) {
        	        if (isset($q[$value])) {
				$q[$value] = array($q[$value]);
			}
        	}
		if (!isset($q['cron_schedule'])) {
                        $q['cron_schedule'] = 'never';
                }

		$cron = '';
		$cron['command'] = $amp_conf['AMPBIN'] . '/queue_reset_stats.php --id=' . $qid;
		if (!isset($q['cron_random']) || $q['cron_random'] != 'true') {
			switch ($q['cron_schedule']) {
				case 'never':
					$cron = '';
					break;
				case 'hourly':
				case 'daily':
				case 'weekly':
				case 'monthly':
				case 'annually':
				case 'reboot':
					$cron['event']		= $q['cron_schedule'];
					break;
				case 'custom':
					$cron['minute']		= isset($q['cron_minute'])	? implode(',', $q['cron_minute'])	: '*';
					$cron['dom']		= isset($q['cron_dom'])		? implode(',', $q['cron_dom'])		: '*';
					$cron['dow']		= isset($q['cron_dow'])		? implode(',', $q['cron_dow'])		: '*';
					$cron['hour']		= isset($q['cron_hour'])	? implode(',', $q['cron_hour'])		: '*';
					$cron['month']		= isset($q['cron_month'])	? implode(',', $q['cron_month'])	: '*';
					break;
				default:
					$cron = '';
					break;
			}
		} else {
			switch ($q['cron_schedule']) {
				case 'annually':
					$cron['month']		= rand(1, 12);
				case 'monthly':
					$cron['dom']		= rand(1, 31);
				case 'weekly':
					if(!in_array(array('annually', 'monthly'), $q['cron_schedule'])) {
						$cron['dow']	= rand(0, 6);
					}
				case 'daily':
					$hour				= rand(0, 7) + 21;
					$cron['hour']		= $hour > 23 ? $hour - 23 : $hour;
				case 'hourly':
					$cron['minute']		= rand(0, 59);
					break;
				default:
					$cron = '';
					break;
			}
		}

		if ($cron) {
			//dbug('calling cron with ', $cron);
			edit_crontab('', $cron);
		}

	}
}



?>
