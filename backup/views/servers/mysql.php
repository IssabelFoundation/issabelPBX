<?php
$html = '';
$html .= heading('Mysql Server', 3) . '<hr class="backup-hr"/>';
$html .= form_hidden('server_type', 'mysql');
$html .= form_open($_SERVER['REQUEST_URI']);
$html .= form_hidden('action', 'save');
$html .= form_hidden('id', $id);


$table = new CI_Table;

//name
$label	= ipbx_label(_('Server Name'));
$data 	= array(
			'name'		=> 'name', 
			'value'		=> $name
		);	
$data = backup_server_writeable('name', $readonly, $data);
$table->add_row($label, form_input($data));

//decription
$label	= ipbx_label(_('Description'), _('Description or notes for this server'));
$data 	= array(
			'name'		=> 'desc', 
			'value'		=> $desc
		);
$data = backup_server_writeable('desc', $readonly, $data);
$table->add_row($label, form_input($data));

//hostname
$label = ipbx_label(_('Hostname'), _('IP address or FQDN of remote mysql host'));
$data  = array(
			'name'		=> 'host', 
			'value'		=> $host,
			'required'	=> ''
		);
$data = backup_server_writeable('host', $readonly, $data);
$table->add_row($label, form_input($data));
		
//port
$data = array(
			'name'		=> 'port', 
			'value'		=> $port,
			'required'	=> ''
		);
$data = backup_server_writeable('port', $readonly, $data);
$table->add_row(ipbx_label(_('Port'), _('remote mysql port')), form_input($data));
		
//user name
$data = array(
			'name'		=> 'user', 
			'value'		=> $user,
			'required'	=> ''
		);
$data = backup_server_writeable('user', $readonly, $data);
$table->add_row(ipbx_label(_('User Name')), form_input($data));
		
//mysql password
$label	= ipbx_label(_('Password'));
$data 	= array(
			'name'		=> 'password', 
			'value'		=> $password,
			'required'	=> ''
		);
$data = backup_server_writeable('password', $readonly, $data);
$table->add_row($label, form_input($data));


//remote directory
$label	= ipbx_label(_('DB Name'), _('Database name'));
$data 	= array(
			'name'		=> 'dbname', 
			'value'		=> $dbname,
			'required'	=> ''
		);
$data = backup_server_writeable('dbname', $readonly, $data);
$table->add_row($label, form_input($data));

$html .= $table->generate();

//if readonly == * than nothing is saveable anyway
if ($readonly != array('*')) {
	$html .= form_submit('submit', _('Save'));
}

if ($immortal != 'true') {
	$html .= form_submit('submit', _('Delete'));
}
$html .= form_close();

echo $html;
