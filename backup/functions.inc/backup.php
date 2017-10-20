<?php

function backup_del_backup($id) {
	global $db;
	$data = backup_get_backup($id);

	//dont delete if deleting has been blocked
	if ($data['immortal'] == 'true') {
		return $id;
	}

	$sql = 'DELETE FROM backup WHERE id = ?';
	$ret = $db->query($sql, $id);
	if ($db->IsError($ret)){
		die_issabelpbx($ret->getDebugInfo());
	}

	$sql = 'DELETE FROM backup_details WHERE backup_id = ?';
	$ret = $db->query($sql, $id);
	if ($db->IsError($ret)){
		die_issabelpbx($ret->getDebugInfo());
	}

	//set backup cron
	backup_set_backup_cron();

	return '';
}

function backup_get_backup($id = '') {
	global $db;

	//return a blank if no id was set, all servers if 'all' was passed
	//otherwise, a specifc server

	switch ($id) {
		case '':
			$ret = array(
				'applyconfigs'		=> '',
				'bu_server'			=> '',
				'cron_dom'			=> array(),
				'cron_dow'			=> array(),
				'cron_hour'			=> array(),
				'cron_minute'		=> array(),
				'cron_month'		=> array(),
				'cron_random'		=> '',
				'cron_schedule'		=> '',
				'desc'				=> '',
				'delete_amount'		=> 0,
				'delete_time_type'	=> '',
				'delete_time'		=> 0,
				'disabletrunks'		=> '',
				'exclude'			=> '',
				'host'				=> '',
				'id'				=> '',
				'immortal'			=> '',
				'items'				=> array(),
				'name'				=> '',
				'email'				=> '',
				'path'				=> '',
				'postbu_hook'		=> '',
				'postre_hook'		=> '',
				'prebu_hook'		=> '',
				'prere_hook'		=> '',
				'restore'			=> '',
				'storage_servers'	=> array()
				);
			return $ret;
			break;
		case 'all':
		case 'all_detailed':
			$ret = sql('SELECT * FROM backup ORDER BY name', 'getAll', DB_FETCHMODE_ASSOC);
			$backups = array();
			//set index to server id for easy retrieval
			foreach ($ret as $s) {
				//set index to  id for easy retrieval
				$backups[$s['id']] = $s;

				//default name in one is missing
				if (!$backups[$s['id']]['name']) {
					$backups[$s['id']]['name'] = _('Backup') . ' ' . $s['id'];
				}

				//add details if requested
				if ($id == 'all_detailed') {
					$backups[$s['id']] = backup_get_backup($s['id']);
				}
			}
			return $backups;
			break;
		default:
			$sql = 'SELECT * FROM backup WHERE id = ?';
			$ret = $db->getAll($sql, array($id), DB_FETCHMODE_ASSOC);
			if ($db->IsError($ret)){
				die_issabelpbx($ret->getDebugInfo());
			}

			//return a blank set if an invalid id was entered
			if (!$ret) {
				return false;
			}

			//get details
			$ret = $ret[0];
			$sql = 'SELECT `key`, `index`, value FROM backup_details WHERE backup_id = ? ORDER BY `index`';
			$ret1 = $db->getAll($sql, array($id), DB_FETCHMODE_ASSOC);
			if ($db->IsError($ret1)){
				die_issabelpbx($ret1->getDebugInfo());
			}

			if ($ret1) {
				foreach ($ret1 as $r) {
					switch ($r['key']) {
						case 'storage_servers':
							$ret[$r['key']][] = $r['value'];
							break;
						default:
							$ret[$r['key']] = $r['value'];
							break;
					}

				}
			}

			//explode cron items
			$ret['cron_minute']	= isset($ret['cron_minute'])	? explode(',', $ret['cron_minute'])	: array();
			$ret['cron_dom']	= isset($ret['cron_dom'])		? explode(',', $ret['cron_dom'])	: array();
			$ret['cron_dow']	= isset($ret['cron_dow'])		? explode(',', $ret['cron_dow'])	: array();
			$ret['cron_hour']	= isset($ret['cron_hour'])		? explode(',', $ret['cron_hour'])	: array();
			$ret['cron_month']	= isset($ret['cron_month'])		? explode(',', $ret['cron_month'])	: array();

			//default a name
			$ret['name'] = $ret['name'] ? $ret['name'] : 'Backup ' . $ret['id'];

			//ensure bool's are initialized
			$ret['restore']			= isset($ret['restore'])		? $ret['restore'] : false;
			$ret['applyconfigs']	= isset($ret['applyconfigs'])	? $ret['applyconfigs'] : false;
			$ret['disabletrunks']	= isset($ret['disabletrunks'])	? $ret['disabletrunks'] : false;

			//get items
			$sql = 'SELECT type, path, exclude FROM backup_items WHERE backup_id = ?';
			$ret2 = $db->getAll($sql, array($id), DB_FETCHMODE_ASSOC);
			if ($db->IsError($ret2)){
				die_issabelpbx($ret2->getDebugInfo());
			}

			if ($ret2) {
				foreach ($ret2 as $res) {
					foreach($res as $key => $value) {
						$my[$key] = $value;
					}
					if ($my['exclude']) {
						$my['exclude'] = unserialize($my['exclude']);
					}
					$ret['items'][] = $my;
					unset($my);
				}
			} else {
				$ret['items'] = array();
			}

			return $ret;
			break;
	}
}

function backup_put_backup($var) {
	global $db, $amp_conf;
	//dont save protected backups
	if ($var['id']) {
		$stale = backup_get_backup($var['id']);
		if ($stale['immortal'] == 'true') {
			return false;
		}
	}

	//save server
	if (!empty($var['id'])) {
	  $sql = 'UPDATE backup SET name = ?, description = ?, email = ? WHERE id = ?';
	  $sql_params = array($var['name'], $var['desc'], $var['email'], $var['id']);
	} else {
		$sql = 'INSERT INTO backup (name, description, email) VALUES (?, ?, ?)';
		$sql_params = array($var['name'], $var['desc'], $var['email']);
	}
	$ret = $db->query($sql, $sql_params);
	if ($db->IsError($ret)){
		die_issabelpbx($ret->getDebugInfo());
	}

	$sql = ($amp_conf["AMPDBENGINE"] == "sqlite3") ? 'SELECT last_insert_rowid()' : 'SELECT LAST_INSERT_ID()';
	$var['id'] = $var['id'] ? $var['id'] : $db->getOne($sql);

	//save server details
	//first delete stale
	$sql = 'DELETE FROM backup_details WHERE backup_id = ?';
	$ret = $db->query($sql, $var['id']);
	if ($db->IsError($ret)){
		die_issabelpbx($ret->getDebugInfo());
	}

	//prepare details array for insertion
	//enasure that were not setting a random cron for events that cannot be randomized
	switch ($var['cron_schedule']) {
		case 'never':
		case 'reboot':
		case 'custom':
			$var['cron_random'] = '';
			break;
	}

	foreach ($var as $key => $value) {
		switch ($key) {
			case 'cron_minute':
			case 'cron_dom':
			case 'cron_dow':
			case 'cron_hour':
			case 'cron_month':
				//only save if we have a value
				if ($value !== '') {
					$data[] = array($var['id'],  $key, '', implode(',', $value));
				}
				break;
			case 'bu_server':
			case 'cron_random':
			case 'cron_schedule':
			case 'desc':
			case 'delete_amount':
			case 'delete_time_type':
			case 'delete_time':
			case 'postbu_hook':
			case 'postre_hook':
			case 'prebu_hook':
			case 'prere_hook':
				if ($value !== '') {
					$data[] = array($var['id'],  $key, '', $value);
				}
				break;
			case 'email':
				if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL)) {
						$data[] = array($var['id'],  $key, '', $value);
				}
				break;
			case 'restore':
				//only save if we have a value and we didnt select the local server
				if ($value !== '' && $var['bu_server'] > 0) {
					$data[] = array($var['id'],  $key, '', $value);
				}
				break;
			case 'disabletrunks':
			case 'applyconfigs':
				//only save if we have a value, we didnt select the local server, and were doing a restore
				if ($value !== '' && $var['bu_server'] > 0 && $var['restore'] == 'true') {
					$data[] = array($var['id'],  $key, '', $value);
				}
				break;
			case 'storage_servers':
				$index = 0;
				foreach ($value as $v) {
					$data[] = array($var['id'],  $key, $index++, $v);
				}
				break;
		}
	}

	//then insert fresh
	$sql = $db->prepare('INSERT INTO backup_details (backup_id, `key`, `index`, value) VALUES (?, ?, ?, ?)');
	$ret = $db->executeMultiple($sql, $data);
	if ($db->IsError($ret)){
		die_issabelpbx($ret->getDebugInfo());
	}


	//save server items
	//first delete stale
	unset($data);
	$sql = 'DELETE FROM backup_items WHERE backup_id = ?';
	$ret = $db->query($sql, $var['id']);
	if ($db->IsError($ret)){
		die_issabelpbx($ret->getDebugInfo());
	}

	//prepare items array for insertion
	$saved = array();
	if (is_array($var['type'])) {
		foreach ($var['type'] as $e_id => $type) {
			if (!isset($saved[$type], $saved[$type][$var['path'][$e_id]])) {
				//mark row as saved so that we can check for dups
				$saved[$type][$var['path'][$e_id]] = true;

				//ensure excludes are unique and clean, dont explode if the string is blank
				$excludes = trim($var['exclude'][$e_id])
							? explode("\n", $var['exclude'][$e_id])
							: array();

				foreach ($excludes as $my => $e) {
					$excludes[$my] = trim($e);
				}
				$excludes  = array_unique($excludes);
				$data[] = array($var['id'],  $type, $var['path'][$e_id], serialize($excludes));
			}
		}

		//then insert fresh
		$sql = $db->prepare('INSERT INTO backup_items (backup_id, type, path, exclude) VALUES (?, ?, ?, ?)');
		$ret = $db->executeMultiple($sql, $data);
		if ($db->IsError($ret)){
			die_issabelpbx($ret->getDebugInfo());
		}
	}

	//set backup cron
	backup_set_backup_cron();

	return $var['id'];
}

function backup_set_backup_cron() {
	global $amp_conf;

	//remove all stale backup's
	edit_crontab($amp_conf['AMPBIN'] . '/backup.php');

	$backups = backup_get_backup('all_detailed');
	foreach ($backups as $b) {
		$cron = '';
		// The ID porition of the command was added to better support other cron daemons (#7374)
		// We should be using the format of ID=[vendor]_[module raw name]_[id]
		$cron['command'] = 'ID=issabelpbx_backup_' . $b['id'] . ' ' . $amp_conf['AMPBIN'] . '/backup.php --id=' . $b['id'];
		if (!isset($b['cron_random']) || $b['cron_random'] != 'true') {
			switch ($b['cron_schedule']) {
				case 'never':
					$cron = '';
					break;
				case 'hourly':
				case 'daily':
				case 'weekly':
				case 'monthly':
				case 'annually':
				case 'reboot':
					$cron['event']		= $b['cron_schedule'];
					break;
				case 'custom':
					$cron['minute']		= isset($b['cron_minute'])	? implode(',', $b['cron_minute'])	: '*';
					$cron['dom']		= isset($b['cron_dom'])		? implode(',', $b['cron_dom'])		: '*';
					$cron['dow']		= isset($b['cron_dow'])		? implode(',', $b['cron_dow'])		: '*';
					$cron['hour']		= isset($b['cron_hour'])	? implode(',', $b['cron_hour'])		: '*';
					$cron['month']		= isset($b['cron_month'])	? implode(',', $b['cron_month'])	: '*';
					break;
			}
		} else {
			switch ($b['cron_schedule']) {
				case 'annually':
					$cron['month']		= rand(1, 12);
				case 'monthly':
					$cron['dom']		= rand(1, 31);
				case 'weekly':
					if(!in_array(array('annually', 'monthly'), $b['cron_schedule'])) {
						$cron['dow']	= rand(0, 6);
					}
				case 'daily':
					$hour				= rand(0, 7) + 21;
					$cron['hour']		= $hour > 23 ? $hour - 23 : $hour;
				case 'hourly':
					$cron['minute']		= rand(0, 59);
					break;
			}
		}

		if ($cron) {
			//dbug('calling cron with ', $cron);
			edit_crontab('', $cron);
		}

	}
}
