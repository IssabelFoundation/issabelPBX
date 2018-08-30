<?php

$html = '';
$html .= heading(_('Paging and Intercom settings'), 3) 
		. '<hr class="paging-hr"/>';

$html .= form_open($_SERVER['REQUEST_URI']);
$html .= form_hidden('action', 'save_settings');

$table = new CI_Table;
$table->add_row(array('colspan' => 2,
	'data' => heading(_('Auto-answer defaults'), 5) . '<hr />'));

$label = ipbx_label(_('Announcement'),
			_('Annoucement to be played to remote part. Default is a beep'));
$table->add_row($label, form_dropdown('announce', $rec_list, $announce));

$html .= $table->generate();
$html .= br(2) . form_submit('submit', _('Save'));

echo $html;
?>
