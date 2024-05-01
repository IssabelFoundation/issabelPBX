<?php
$html = '<div class="content">';

if($id=='') {
    $html .= heading(__('Add Backup Template'), 2);
} else {
    $html .= heading(__('Edit Backup Template').": ".$name, 2);
}

$html .= heading(_dgettext('amp','General Settings'), 5);

$html .= form_open($_SERVER['REQUEST_URI'],'id="mainform" onsubmit="return edit_onsubmit(this)" ');
$html .= form_hidden('action', 'save');
$html .= form_hidden('id', $id);

$table = new CI_Table;

//name
$label	= ipbx_label(__('Template Name'));
$data 	= array(
			'name' => 'name', 
			'value' => $name,
            'class' => 'input'
		);
$immortal ? $data['disabled'] = '' : '';
$table->add_row($label, form_input($data));

//description
$label	= ipbx_label(__('Description'), __('Description or notes for this server'));
$data 	= array(
			'name' => 'desc', 
            'value' => $desc,
            'class' => 'input'
		);
$immortal ? $data['disabled'] = '' : '';
$table->add_row($label, form_input($data));
$html .= $table->generate();
$html .= $table->clear();
//$html .= br(2);

$html .= heading(__('Items'), 5);

$html .= load_view(dirname(__FILE__) . '/../item_table.php', array('items' => $items, 'immortal' => $immortal));

//$html .= br(2);

/*
if ($immortal != 'true') {
	$html .= form_submit('submit', __('Save'), ' class="button is-rounded is-small" ');
	$html .= form_submit('submit', __('Delete'), ' class="button is-rounded is-small" ');
}
 */
$html	.= form_close(). PHP_EOL;
$html .= "
<script>
    function edit_onsubmit(theForm) {
        \$.LoadingOverlay('show');
        return true;
    }
";
$html.= js_display_confirmation_toasts();
$html.= "</script>";

$html .= '</div>';

if ($immortal != 'true') {
    $html.= form_action_bar($id);
}

echo $html;
