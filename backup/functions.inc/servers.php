<?php
//blank server arrays, used by backup_get_server()
$backup_server_blanks['email'] =
	array(
		'id'		=> '',
		'name'		=> '',
		'desc'		=> '',
		'addr'		=> '',
		'maxsize'	=> '26200000',
		'type'		=> 'email',
		'readonly'	=> array(),
		'immortal'	=> ''
		);
		
$backup_server_blanks['ftp'] =
	array(
		'id'		=> '',
		'name'		=> '',
		'desc'		=> '',
		'host'		=> '',
		'port'		=> '21',
		'user'		=> '',
		'password'	=> '',
		'path'		=> '',
		'transfer'	=> 'passive',
		'type'		=> 'ftp',
		'readonly'	=> array(),
		'immortal'	=> ''
		);

$backup_server_blanks['local'] =
	array(
		'id'		=> '',
		'name'		=> '',
		'desc'		=> '',
		'path'		=> '',
		'type'		=> 'local',
		'readonly'	=> array(),
		'immortal'	=> ''
		);
		
$backup_server_blanks['mysql'] =
	array(
		'id'		=> '',
		'name'		=> '',
		'desc'		=> '',
		'host'		=> '',
		'port'		=> '3306',
		'user'		=> '',
		'password'	=> '',
		'db'		=> '',
		'type'		=> 'mysql',
		'readonly'	=> array(),
		'immortal'	=> ''
		);
					
$backup_server_blanks['ssh'] =
	array(
		'id'		=> '',
		'name'		=> '',
		'desc'		=> '',
		'host'		=> '',
		'port'		=> '22',
		'user'		=> '',
		'key'		=> '',
		'path'		=> '',
		'type'		=> 'ssh',
		'readonly'	=> array(),
		'immortal'	=> ''
		);
		
		
function backup_del_server($id) {
	global $db;

	//dont delete if deleting has been blocked
	$immortal = $db->getOne('SELECT immortal FROM backup_servers WHERE id = ?', $id);
	db_e($immortal);
	if ($immortal && $immortal == 'true') {
		return $id;
	}
	
	$sql = 'DELETE FROM backup_servers WHERE id = ?';
	$ret = $db->query($sql, $id);
	db_e($ret);
	
	$sql = 'DELETE FROM backup_server_details WHERE server_id = ?';
	$ret = $db->query($sql, $id);
	db_e($ret);
	
	//delete from backups_details
	$sql = 'DELETE FROM backup_details WHERE `key` = "storage_servers" and value = ?';
	$ret = $db->query($sql, $id);
	db_e($ret);
	
	//delete from backups_items
	$sql = 'DELETE FROM backup_items WHERE type = "mysql" AND path = CONCAT("server-", ?)';
	$ret = $db->query($sql, $id);
	db_e($ret);
	
	//delete from templates
	$sql = 'DELETE FROM backup_template_details WHERE type = "mysql" AND path = CONCAT("server-", ?)';
	$ret = $db->query($sql, $id);
	//dbug('temp', $db->last_query);
	db_e($ret);
	
	return '';
}

function backup_put_server($var) {
	global $db, $amp_conf;
	
	//reset protected values
	if ($var['id'] && $var['type']) {
		$stale = backup_get_server($var['id']);
		
		//dont save if readonly == *
		if ($stale['readonly'] == array('*')) {
			return false;
		}
		
		foreach ((array)$stale['readonly'] as $k => $v) {
			$var[$v] = $stale[$v];
		}
	}
	
	//type could replace server_type
	if(!isset($var['server_type']) || !$var['server_type'] && $var['type']) {
		$var['server_type'] = $var['type'];
	}
	
	//save server
	if (!empty($var['id'])) {
		$sql = 'UPDATE backup_servers SET name = ?, `desc` = ?, type = ? WHERE id = ?';
		$sql_params = array($var['name'], $var['desc'], $var['server_type'], $var['id']);
	} else {
		$sql = 'INSERT INTO backup_servers (name, `desc`, type) VALUES (?, ?, ?)';
		$sql_params = array($var['name'], $var['desc'], $var['server_type']);
	}
	$ret = $db->query($sql, $sql_params);
	if ($db->IsError($ret)){
		die_issabelpbx($ret->getDebugInfo());
	}

	$sql = ($amp_conf["AMPDBENGINE"] == "sqlite3") ? 'SELECT last_insert_rowid()' : 'SELECT LAST_INSERT_ID()';
	$var['id'] = $var['id'] ? $var['id'] : $db->getOne($sql);

	//save server details
	//first delete stale
	$sql = 'DELETE FROM backup_server_details WHERE server_id = ?';
	$ret = $db->query($sql, $var['id']);
	if ($db->IsError($ret)){
		die_issabelpbx($ret->getDebugInfo());
	}

	
	//prepare array for insertion
	switch ($var['server_type']) {
		
		case 'email':
			$data = array(
						array($var['id'], 'addr', $var['addr']),
						array($var['id'], 'maxsize', $var['maxsize'])
					);
			break;
		
		case 'ftp':
			$data = array(
						array($var['id'], 'host', $var['host']),
						array($var['id'], 'port', $var['port']),
						array($var['id'], 'user', $var['user']),
						array($var['id'], 'path', $var['path']),
						array($var['id'], 'password', $var['password']),
						array($var['id'], 'transfer', $var['transfer'])
					);
			break;
		
		case 'local':
			$data = array(
						array($var['id'], 'path', $var['path'])
					);
			break;
		
		case 'mysql':
			$data = array(
						array($var['id'], 'host', $var['host']),
						array($var['id'], 'port', $var['port']),
						array($var['id'], 'user', $var['user']),
						array($var['id'], 'dbname', $var['dbname']),
						array($var['id'], 'password', $var['password'])
					);
			break;
		
		case 'ssh':
			$data = array(
						array($var['id'], 'host', $var['host']),
						array($var['id'], 'port', $var['port']),
						array($var['id'], 'user', $var['user']),
						array($var['id'], 'path', $var['path']),
						array($var['id'], 'key', $var['key'])
					);
			break;
		default:
			return false;
	}


	//then insert fresh
	$sql = $db->prepare('INSERT INTO backup_server_details (server_id, `key`, value) VALUES (?, ?, ?)');
	$ret = $db->executeMultiple($sql, $data);
	if ($db->IsError($ret)){
		die_issabelpbx($ret->getDebugInfo());
	}
	
	return $var['id'];
}

function backup_get_server($id = '') {
	global $db;
	
	//return a blank if no id was set, all servers if 'all' was passed
	//otherwise, a specifc server
	switch ($id) {
		case 'email':
		case 'ftp':
		case 'local':
		case 'mysql':
		case 'ssh':
			global $backup_server_blanks;
			return $backup_server_blanks[$id];
			break;
		case 'all':
		case 'all_detailed':
			$ret = sql('SELECT * FROM backup_servers ORDER BY name', 'getAll', DB_FETCHMODE_ASSOC);
			foreach ($ret as $s) {
				//set index to server id for easy retrieval
				$servers[$s['id']] = $s;
				
				//default name in one is missing
				if (!$servers[$s['id']]['name']) {
					switch ($s['type']) {
						case 'email':
							$sname = _('Email Server ');
							break;
						case 'ftp':
							$sname = _('FTP Server ');
							break;
						case 'local':
							$sname = _('Local Server ');
							break;
						case 'mysql':
							$sname = _('Mysql Server ');
							break;
						case 'ssh':
							$sname = _('SSH Server ');
							break;
					}
					$servers[$s['id']]['name'] = $sname . $s['id'];
				}
				
				//add details if requested
				if ($id == 'all_detailed') {
					$servers[$s['id']] = backup_get_server($s['id']);
				}
			}
			
			return $servers;
			break;
		default:
			$sql = 'SELECT * FROM backup_servers WHERE id = ?';
			$ret = $db->getAll($sql, array($id), DB_FETCHMODE_ASSOC);
			if ($db->IsError($ret)){
				die_issabelpbx($ret->getDebugInfo());
			}
			
			//return a blank set if an invalid id was entered
			if (!$ret) {
				return backup_get_server('');
			}
			
			$ret = $ret[0];
			$sql = 'SELECT `key`, value FROM backup_server_details WHERE server_id = ?';
			$ret1 = $db->getAll($sql, array($id), DB_FETCHMODE_ASSOC);
			if ($db->IsError($ret1)){
				die_issabelpbx($ret1->getDebugInfo());
			}

			foreach ($ret1 as $key => $r) {
				$ret[$r['key']] = $r['value'];
			}

			//default a name
			switch ($ret['type']) {
				case 'email':
					$sname = _('Email Server ');
					break;
				case 'ftp':
					$sname = _('FTP Server ');
					break;
				case 'local':
					$sname = _('Local Server ');
					break;
				case 'mysql':
					$sname = _('Mysql Server ');
					break;
				case 'ssh':
					$sname = _('SSH Server ');
					break;
			}
			
			$ret['name'] = $ret['name'] ? $ret['name'] : $sname . $ret['id'];
			
			//unserialize readonly
			$ret['readonly'] = $ret['readonly'] ? unserialize($ret['readonly']) : array();
			
			return $ret;
			break;
	}
}

function backup_server_writeable($item, $rule, $data) {
	in_array($item, $rule) || $rule == array('*')
	? $data['disabled'] = 'disabled'
	: '';
	
	return $data;
}
