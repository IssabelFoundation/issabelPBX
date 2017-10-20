<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
$get_vars = array(
				'action'		=> '',
				'addr'			=> '',
				'dbname'		=> '',
				'desc'			=> '',
				'dir'			=> '',
				'display'		=> '',
				'host'			=> '',
				'id'			=> '',
				'key'			=> '',
				'maxsize'		=> '',
				'maxtype'		=> '',
				'menu'			=> '',
				'name'			=> '',
				'password'		=> '',
				'path'			=> '',
				'port'			=> '',
				'user'			=> '',
				'server_type'	=> '',
				'submit'		=> '',
				'transfer'		=> '',
				'type'			=> ''	
				);

foreach ($get_vars as $k => $v) {
	$var[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
}

//set action to delete if delete was pressed instead of submit
if ($var['submit'] == _('Delete') && $var['action'] == 'save') {
	$var['action'] = 'delete';
}

$var['servers'] = backup_get_server('all');

//server type
if ($var['id'] && !$var['server_type']) {
	$var['server_type'] = $var['servers'][$var['id']]['type'];
}

//action actions
switch ($var['action']) {
	case 'save':
		$var['maxsize'] = string2bytes($var['maxsize'], $var['maxtype']);
		unset($var['maxtype']);
		$var['id'] = backup_put_server($var);
		break;
	case 'delete':
		$var['id'] = backup_del_server($var['id']);
		break;
}

//rnav
//this needs to be he so that we can display rnav's reflecting any actions in the 'action actions' switch statement
$var['servers'] = backup_get_server('all');
echo load_view(dirname(__FILE__) . '/views/rnav/servers.php', $var);

//view action
switch ($var['action']) {
	case 'edit':
	case 'save':
		if (!$var['id']) {
			$var['id'] = $var['server_type'];
		}
		$var = array_merge($var, backup_get_server($var['id']));
		echo load_view(dirname(__FILE__) . '/views/servers/' . $var['type'] . '.php', $var);
		break;
	default:
		echo load_view(dirname(__FILE__) . '/views/servers/servers.php', $var);
		break;
}


?>
