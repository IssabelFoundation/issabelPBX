<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
$get_vars = array(
				'action'	=> '',
				'desc'		=> '',
				'display'	=> '',
				'exclude'	=> '',
				'id'		=> '',
				'name'		=> '',
				'path'		=> '',
				'submit'	=> '',
				'type'		=> ''		
				);

foreach ($get_vars as $k => $v) {
	$var[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
}

//set action to delete if delete was pressed instead of submit
if ($var['submit'] == _('Delete') && $var['action'] == 'save') {
	$var['action'] = 'delete';
}

//action actions
switch ($var['action']) {
	case 'save':
		$var['id'] = backup_put_template($var);
		break;
	case 'delete':
		$var['id'] = backup_del_template($var['id']);
		break;
}

//rnav
$var['templates'] = backup_get_template('all');
echo load_view(dirname(__FILE__) . '/views/rnav/templates.php', $var);


//view actions
switch ($var['action']) {
	case 'edit':
	case 'save':
		$var = array_merge($var, backup_get_template($var['id']));
		
		//template id's are all prefixed by their module name for hooking reasons. Clear that past this point
		if (strpos($var['id'], 'backup-') === 0) {
			$var['id'] = substr($var['id'], 7);
		}

		echo load_view(dirname(__FILE__) . '/views/templates/template.php', $var);
		break;
	default:
		echo load_view(dirname(__FILE__) . '/views/templates/templates.php', $var);
		break;
}
?>