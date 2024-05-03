<?php
$html = '<div class="content">';

if($id=='') {
    $html .= heading(__('Add Local Server'), 2);
} else {
    $html .= heading(__('Edit Local Server').": ".$name, 2);
}

$html .= heading(_dgettext('amp','General Settings'), 5);

$html .= form_hidden('server_type', 'ssh');
$html .= form_open($_SERVER['REQUEST_URI'],'id="mainform" onsubmit="return edit_onsubmit(this)"');
$html .= form_hidden('action', 'save');
$html .= form_hidden('id', $id);


$table = new CI_Table;

//name
$label	= ipbx_label(__('Server Name'));
$data 	= array(
			'name' => 'name', 
            'value' => $name,
            'class' => 'input'
		);
$data = backup_server_writeable('name', $readonly, $data);
$table->add_row($label, form_input($data));

//decription
$label	= ipbx_label(__('Description'), __('Description or notes for this server'));
$data 	= array(
			'name' => 'desc', 
			'value' => $desc,
            'class' => 'input'
		);
$data = backup_server_writeable('desc', $readonly, $data);
$table->add_row($label, form_input($data));


// directory
$label	= ipbx_label(__('Path'), __('Path where files are stored'));
$data 	= array(
			'name' => 'path', 
			'value' => $path,
            'class' => 'input'
		);
$data = backup_server_writeable('path', $readonly, $data);
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
