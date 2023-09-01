<?php
$html = '<div class="content">';

if($id=='') {
    $html .= heading(__('Add Email Server'), 2);
} else {
    $html .= heading(__('Edit Email Server').": ".$name, 2);
}

$html .= heading(_dgettext('amp','General Settings'), 5);

$html .= form_hidden('server_type', 'email');
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
$label = ipbx_label(__('Email Address'), __('Email address where backups should be emailed to'));
$data  = array(
			'name' 		=> 'addr', 
			'value'		=> $addr,
			'type'		=> 'email',
            'required'	=> '',
            'class'     => 'input'
		);
$data = backup_server_writeable('addr', $readonly, $data);
$table->add_row($label, form_input($data));

//size
$label = ipbx_label(
			__('Max Email Size'), 
			__('The maximum size a backup can be and still be emailed. '
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
		form_dropdown('maxsize', $sizes, $maxsize[0], $disabled. ' class="componentSelectAutoWidthNoSearch" ') . 
		form_dropdown('maxtype', $types, $maxsize[1], $disabled. ' class="componentSelectAutoWidthNoSearch" ')
	);
		


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
