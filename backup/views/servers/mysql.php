<?php
$html = '<div class="content">';

if($id=='') {
    $html .= heading(__('Add Mysql Server'), 2);
} else {
    $html .= heading(__('Edit Mysql Server').": ".$name, 2);
}

$html .= heading(_dgettext('amp','General Settings'), 5);

$html .= form_hidden('server_type', 'mysql');
$html .= form_open($_SERVER['REQUEST_URI'],'id="mainform" onsubmit="return edit_onsubmit(this)"');
$html .= form_hidden('action', 'save');
$html .= form_hidden('id', $id);


$table = new CI_Table;

//name
$label	= ipbx_label(__('Server Name'));
$data 	= array(
			'name'		=> 'name', 
            'value'		=> $name,
            'class'     => 'input'
		);	
$data = backup_server_writeable('name', $readonly, $data);
$table->add_row($label, form_input($data));

//decription
$label	= ipbx_label(__('Description'), __('Description or notes for this server'));
$data 	= array(
			'name'		=> 'desc', 
			'value'		=> $desc,
            'class'     => 'input'
		);
$data = backup_server_writeable('desc', $readonly, $data);
$table->add_row($label, form_input($data));

//hostname
$label = ipbx_label(__('Hostname'), __('IP address or FQDN of remote mysql host'));
$data  = array(
			'name'		=> 'host', 
			'value'		=> $host,
			'required'	=> '',
            'class'     => 'input'
		);
$data = backup_server_writeable('host', $readonly, $data);
$table->add_row($label, form_input($data));
		
//port
$data = array(
			'name'		=> 'port', 
			'value'		=> $port,
			'required'	=> '',
            'class'     => 'input'
		);
$data = backup_server_writeable('port', $readonly, $data);
$table->add_row(ipbx_label(__('Port'), __('remote mysql port')), form_input($data));
		
//user name
$data = array(
			'name'		=> 'user', 
			'value'		=> $user,
			'required'	=> '',
            'class'     => 'input'
		);
$data = backup_server_writeable('user', $readonly, $data);
$table->add_row(ipbx_label(__('User Name')), form_input($data));
		
//mysql password
$label	= ipbx_label(__('Password'));
$data 	= array(
			'name'		=> 'password', 
			'value'		=> $password,
			'required'	=> '',
            'class'     => 'input'
		);
$data = backup_server_writeable('password', $readonly, $data);
$table->add_row($label, form_input($data));


//remote directory
$label	= ipbx_label(__('DB Name'), __('Database name'));
$data 	= array(
			'name'		=> 'dbname', 
			'value'		=> $dbname,
			'required'	=> '',
            'class'     => 'input'
		);
$data = backup_server_writeable('dbname', $readonly, $data);
$table->add_row($label, form_input($data));

$html .= $table->generate();

$html .= form_close();
$html .= '<script>';
$html .="
    function edit_onsubmit(theForm) {
        \$.LoadingOverlay('show');
        return true;
    }
";
$html .= js_display_confirmation_toasts();
$html .= '</script>';

$html .= '</div>';

$disable_save=true;
if ($immortal != 'true') {
    $disable_delete=false;
}

if ($readonly != array('*')) {
    $html.= form_action_bar($id,'',$disable_delete);
}

echo $html;
