<?php
$html = '';

$html .= heading('Backup Template', 3) . '<hr class="backup-hr"/>';
$html .= form_open($_SERVER['REQUEST_URI']);
$html .= form_hidden('action', 'save');
$html .= form_hidden('id', $id);

$table = new CI_Table;

//name
$label	= ipbx_label(_('Template Name'));
$data 	= array(
			'name' => 'name', 
			'value' => $name
		);
$immortal ? $data['disabled'] = '' : '';
$table->add_row($label, form_input($data));

//description
$label	= ipbx_label(_('Description'), _('Description or notes for this server'));
$data 	= array(
			'name' => 'desc', 
			'value' => $desc
		);
$immortal ? $data['disabled'] = '' : '';
$table->add_row($label, form_input($data));
$html .= $table->generate();
$html .= $table->clear();
$html .= br(2);
$html .= load_view(dirname(__FILE__) . '/../item_table.php', array('items' => $items, 'immortal' => $immortal));

$html .= br(2);

if ($immortal != 'true') {
	$html .= form_submit('submit', _('Save'));
	$html .= form_submit('submit', _('Delete'));
}
$html	.= form_close(). PHP_EOL;

echo $html;
