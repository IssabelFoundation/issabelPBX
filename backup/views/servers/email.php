<?php
$html = '';
$html .= heading('Email Server', 3) . '<hr class="backup-hr"/>';
$html .= form_hidden('server_type', 'email');
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
$label = ipbx_label(_('Email Address'), _('Email address where backups should be emailed to'));
$data  = array(
			'name' 		=> 'addr', 
			'value'		=> $addr,
			'type'		=> 'email',
			'required'	=> ''
		);
$data = backup_server_writeable('addr', $readonly, $data);
$table->add_row($label, form_input($data));

//size
$label = ipbx_label(
			_('Max Email Size'), 
			_('The maximum size a backup can be and still be emailed. '
			. 'Some email servers limit the size of email attachments, '
			. 'this will make sure that files larger than the max size '
			. 'are not sent.')
		);
for ($i = 1; $i < 21; $i++){
	$sizes[$i] = $i;
}
for ($i = 25; $i < 51; $i += 5) {
	$sizes[$i] = $i;
}
for ($i = 60; $i < 101; $i += 10) {
	$sizes[$i] = $i;
}
$types		= array('b' => 'B', 'kb' => 'KB', 'mb' => 'MB', 'gb' => 'GB');
$disabled	= in_array('maxsize', $readonly) || $readonly == array('*') ? 'disabled' : '';
$maxsize	= explode(' ', bytes2string($maxsize));
$table->add_row(
		$label, 
		form_dropdown('maxsize', $sizes, $maxsize[0], $disabled) . 
		form_dropdown('maxtype', $types, $maxsize[1], $disabled)
	);
		


$html .= $table->generate();

if($readonly != array('*')) {
	$html .= form_submit('submit', _('Save'));
}

if ($immortal != 'true') {
	$html .= form_submit('submit', _('Delete'));
}
$html .= form_close();

echo $html;
