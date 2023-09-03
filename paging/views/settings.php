<?php

$html = '';
$html .= heading(__('Paging and Intercom settings'), 3);

$html .= form_open($_SERVER['REQUEST_URI'],'id="mainform" data-target="frm_settings" name="frm_settings"');
$html .= form_hidden('action', 'save_settings');

$table = new CI_Table;

$table->add_row(array('colspan' => 2, 'data' => heading(__('Auto-answer defaults'), 5) ));

$label = ipbx_label(__('Announcement'), __('Annoucement to be played to remote part. Default is a beep'));

$table->add_row($label, form_dropdown('announce', $rec_list, $announce,'class="componentSelect"'));

$html .= $table->generate();

$html .= "</div>";
$html .= form_action_bar('','',true,true);


echo $html;
?>
