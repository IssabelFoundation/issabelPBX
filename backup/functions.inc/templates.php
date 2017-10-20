<?php

function backup_del_template($id) {
	global $db;
	$data = backup_get_template($id);
	
	//dont delete if deleting has been blocked
	if ($data['immortal'] == 'true') {
		return $id;
	}
	
	$sql = 'DELETE FROM backup_templates WHERE id = ?';
	$ret = $db->query($sql, $id);
	db_e($ret);
	
	$sql = 'DELETE FROM backup_template_details WHERE template_id = ?';
	$ret = $db->query($sql, $id);
	db_e($ret);
	
	/*todo: select servers from backups
	$sql = 'DELETE FROM backup_details WHERE server = ?';
	$ret = $db->query($sql, $id);
	if ($db->IsError($ret)){
		die_issabelpbx($ret->getDebugInfo());
	}*/
	
	return '';
}

function backup_get_template($id = '') {
	global $db;
	
	//return a blank if no id was set, all templates if 'all' was passed
	//otherwise, a specifc template

	switch ($id) {
		case '':
			return array(
					'id'		=> '',
					'name'		=> '',
					'data'		=> array(),
					'desc'		=> '',
					'items'		=> array(),
					'immortal'	=> ''
					);
			break;
		case 'all':
		case 'all_detailed':
			$templates = array();
			$ret = sql('SELECT * FROM backup_templates ORDER BY name', 'getAll', DB_FETCHMODE_ASSOC);
		
			//get hooks from other modules
			$hook_temps = mod_func_iterator('hook_backup_get_template');
			
			//sanatizes hooks and push them in to the stack
			foreach ($hook_temps as $mod => $template) {
				foreach ($template as $my => $temp) {
					$ret[] = backup_template_sanitize($temp, $mod);
				}
				
			}
		
			//sanatize/argument templates
			foreach ($ret as $temp) {
				if ($id == 'all_detailed') {
					//backup_get_template() dose its own sanatization
					$templates[$temp['id']] = backup_get_template($temp['id']);
				} else {
					$templates[$temp['id']] = backup_template_sanitize($temp);
				}
			}
		
			//this ugliness is here pending requiring php-53
			//to migrate, just copy the functions in place of the string of same name
			 function _anon_func1($val) {
				return $val;
			}
			
			 function _anon_func2($a, $b) {
				if ($a['name'] == $b['name']) {
					return 0;
				} else {
					return $a['name'] > $b['name'] ? 1 : -1;
				}
			}
			
			//remove any false values (templates that didnt pass sanitization)
			$templates = array_filter($templates, '_anon_func1');
		
			//sort templaes based on template name
			uasort($templates, '_anon_func2');
	
			return $templates;
			break;
		default:
			//if the id is preceded by a module name, ask that module for the details
			if (strpos($id, '-') !== false) {
				
				//get data by breaking up backup id
				list($mod, $id) = explode('-', $id);
		
				//return template data if $mod's function exsists
				if (function_exists($mod . '_hook_backup_get_template')) {
					return backup_template_sanitize(
								call_user_func($mod . '_hook_backup_get_template', $id),
								$mod
							);
				}
				
			}
			
			$sql = 'SELECT * FROM backup_templates WHERE id = ?';
			$ret = $db->getAll($sql, array($id), DB_FETCHMODE_ASSOC);
			if ($db->IsError($ret)){
				die_issabelpbx($ret->getDebugInfo());
			}
			
			//return a blank set if an invalid id was entered
			if (!$ret) {
				return backup_get_template('');
			}
			
			$ret = $ret[0];
			$sql = 'SELECT type, path, exclude FROM backup_template_details WHERE template_id = ?';
			$ret1 = $db->getAll($sql, array($id), DB_FETCHMODE_ASSOC);
			if ($db->IsError($ret1)){
				die_issabelpbx($ret1->getDebugInfo());
			}
			
			if ($ret1) {
				foreach ($ret1 as $res) {
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

			$ret = backup_template_sanitize($ret);

			return $ret;
			break;
	}
}

function backup_put_template($var) {
	global $db, $amp_conf;
	
	//dont save protected templates
	if ($var['id']) {
		$stale = backup_get_template($var['id']);
		if ($stale['immortal'] == 'true') {
			return false;
		}
	}
	
	//save server
	if (!empty($var['id'])) {
		$sql = 'UPDATE backup_templates SET name = ?, `desc` = ? WHERE id = ?';
		$sql_params = array($var['name'], $var['desc'], $var['id']);
	} else {
		$sql = 'INSERT INTO backup_templates (name, `desc`) VALUES (?, ?)';
		$sql_params = array($var['name'], $var['desc']);
	}
	$ret = $db->query($sql, $sql_params);
	if ($db->IsError($ret)){
		die_issabelpbx($ret->getDebugInfo());
	}
	
	$sql = ($amp_conf["AMPDBENGINE"] == "sqlite3") ? 'SELECT last_insert_rowid()' : 'SELECT LAST_INSERT_ID()';
	$var['id'] = $var['id'] ? $var['id'] : $db->getOne($sql);

	//save server details
	//first delete stale
	$sql = 'DELETE FROM backup_template_details WHERE template_id = ?';
	$ret = $db->query($sql, $var['id']);
	if ($db->IsError($ret)){
		die_issabelpbx($ret->getDebugInfo());
	}
	
	//prepare array for insertion
	$saved = array();
	if (is_array($var['type'])) {
		foreach ($var['type'] as $e_id => $type) {
			if (!isset($saved[$type], $saved[$type][$var['path'][$e_id]])) {
				//mark row as saved so that we can check for dups
				$saved[$type][$var['path'][$e_id]] = true;
				
				//ensure excludes are unique and clean
				if (!is_array($var['exclude'][$e_id])) {
					$excludes = explode("\n", $var['exclude'][$e_id]);
				} else {
					$excludes = $var['exclude'][$e_id];
				}
				foreach ($excludes as $my => $e) {
					$excludes[$my] = trim($e);
				}
				$excludes  = array_unique($excludes);
				$data[] = array($var['id'],  $type, $var['path'][$e_id], serialize($excludes));
			}
		}
		
		//then insert fresh
		$sql = $db->prepare('INSERT INTO backup_template_details (template_id, type, path, exclude) VALUES (?, ?, ?, ?)');
		$ret = $db->executeMultiple($sql, $data);
		if ($db->IsError($ret)){
			die_issabelpbx($ret->getDebugInfo());
		}
	}

	
	return $var['id'];
}

 /**
 * $c is count, $i is item
 */
function backup_template_generate_tr($c, $i, $immortal = 'false', $build_tr = false) {
	$type			= '';
	$path			= '';
	$exclude		= '';
	$server_list	= array();
	static $servers;
	
	switch ($i['type']) {
		case 'file':
			$type		= _('File') . form_hidden('type[' . $c . ']', 'file');
			$path 		= array(
							'name'			=> 'path[' . $c . ']', 
							'value'			=> $i['path'],
							'required'		=> '',
							'placeholder'	=> _('/path/to/file')
						);
			$immortal ? $path['disabled'] = '' : '';
			$path		= form_input($path);
			$exclude	= form_hidden('exclude[' . $c . ']', '');
			break;
		
		case 'dir':
			$type		= _('Directory') . form_hidden('type[' . $c . ']', 'dir');
			$path 		= array(
							'name'			=> 'path[' . $c . ']', 
							'value'			=> $i['path'],
							'required'		=> '',
							'placeholder'	=> _('/path/to/dir')
						);
			$immortal ? $path['disabled'] = '' : '';
			$path		= form_input($path);
			$exclude 	= array(
							'name'			=> 'exclude[' . $c . ']', 
							'value'			=> implode("\n", $i['exclude']),
							'rows'			=> count($i['exclude']),
							'cols'			=> 20,
							'placeholder'	=> _('PATTERNs, one per line')
						);
			$immortal ? $exclude['disabled'] = '' : '';
			$exclude	= form_textarea($exclude);
			break;
		
		case 'mysql':
			$type		= _('Mysql') . form_hidden('type[' . $c . ']', 'mysql');
			$servers	= backup_get_Server('all');
			
			//draw list of mysql servers for dorpdown
			foreach ($servers as $s) {
				if ($s['type'] == 'mysql') {
					$server_list['server-' . $s['id']] = $s['name'];
				}
			}
			
			if ($server_list) {
				$more 		= $immortal ? ' disabled ' : '';
				$path		= form_dropdown('path[' . $c . ']', $server_list, $i['path'], $more);
			} else {
				$path		= _('{no servers available}');
			}

			$exclude 	= array(
							'name'			=> 'exclude[' . $c . ']', 
							'value'			=> implode("\n", $i['exclude']),
							'rows'			=> count($i['exclude']),
							'cols'			=> 20,
							'placeholder'	=> _('table names, one per line')
						);
			$immortal || !$server_list ? $exclude['disabled'] = '' : '';
			$exclude	= form_textarea($exclude);
			break;
		
		case 'astdb':
			$type		= _('Asterisk DB') . form_hidden('type[' . $c . ']', 'astdb');
			$path 		= form_hidden('path[' . $c . ']', '');
			$exclude 	= array(
							'name'			=> 'exclude[' . $c . ']', 
							'value'			=> implode("\n", $i['exclude']),
							'rows'			=> count($i['exclude']),
							'cols'			=> 20,
							'placeholder'	=> _('Family, one per line')
						);
			$immortal ? $exclude['disabled'] = '' : '';
			$exclude	= form_textarea($exclude);
			break;
	}
	
	$del_txt	= _('Delete this entry. Don\'t forget to click Submit to save changes!');
	$delete		= $immortal == 'true' ? ''
				: '<img src="images/trash.png" style="cursor:pointer" title="' 
				. $del_txt . '" class="delete_entrie">';
				
	if($build_tr) {
		return '<tr><td>'	
				. $type 	. '</td><td>' 
				. $path		. '</td><td>' 
				. $exclude	. '</td><td>' 
				. $delete	. '</td></tr>';
	} else {
		return array('type' => $type, 'path' => $path, 'exclude' => $exclude, 'delete' => $delete);
	}
 	
}

/**
 *
 * Sanatize a template and ensures it is in format we excpet
 * @pram array - a template
 * @pram string - module name, default to backup
 *
 * @returns mixed - the sanatized template or fals on error
 */
function backup_template_sanitize($temp, $mod = 'backup') {

	//backup's id's are just a number. Add the module name for consistancy with hooks
	//this can get a bit tricky - make sure we dotn already have a mod name set!
	if ($mod == 'backup' && strpos($temp['id'], '-') === false) {
		$temp['id'] = 'backup-' . $temp['id'];
	}
	$id = explode('-', $temp['id']);

	//ensure we have a name
	$temp['name'] = $temp['name'] ? $temp['name'] : $id[0] . ' ' . $id[1];
							
	//hooked templates are ALWAYS immortal
	//partly as we dont have delete hooks, and partly because there is no
	//need for modules to create templates that are NOT hard coded
	if ($mod != 'backup') {
		$temp['immortal'] = 'true';
	}
	
	//unserialize data
	if (!isset($temp['data'])) {
		$temp['data'] = array();
	} elseif(is_string($temp['data'])) {
		$temp['data'] = unserialize($temp['data']);
	} 

	return $temp;
}
