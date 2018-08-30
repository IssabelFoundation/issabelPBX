<?php
$html = '';
$html .= heading('Local Server', 3) . '<hr class="backup-hr"/>';
$html .= form_hidden('server_type', 'ssh');
$html .= form_open($_SERVER['REQUEST_URI']);
$html .= form_hidden('action', 'save');
$html .= form_hidden('id', $id);


$table = new CI_Table;

//name
$label	= ipbx_label(_('Server Name'));
$data 	= array(
			'name' => 'name', 
			'value' => $name
		);
$data = backup_server_writeable('name', $readonly, $data);
$table->add_row($label, form_input($data));

//decription
$label	= ipbx_label(_('Description'), _('Description or notes for this server'));
$data 	= array(
			'name' => 'desc', 
			'value' => $desc
		);
$data = backup_server_writeable('desc', $readonly, $data);
$table->add_row($label, form_input($data));


// directory
$label	= ipbx_label(_('Path'), _('Path where files are stored'));
$data 	= array(
			'name' => 'path', 
			'value' => $path
		);
$data = backup_server_writeable('path', $readonly, $data);
$table->add_row($label, form_input($data));

$html .= $table->generate();

if ($readonly != array('*')) {
	$html .= form_submit('submit', _('Save'));
}

if ($immortal != 'true') {
	$html .= form_submit('submit', _('Delete'));
}
$html .= form_close();

echo $html;
