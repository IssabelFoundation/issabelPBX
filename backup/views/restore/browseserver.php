<?php

$html = '';
$html .= heading(_('Restore'), 3) . '<hr class="backup-hr"/>';
$html .= form_open($_SERVER['REQUEST_URI'], array('id' => 'restore_browes_frm'));
$html .= form_hidden('restore_source', 'file');
$html .= form_hidden('restore_path', '');
$html .= form_hidden('action', 'backup_list');


$table = new CI_Table;
$html .= _('Select a file and click go');
$html .= br(2);

$data = '';
$data .= '<div id="list_data">';
$data .= _('Name') . ': <span id="picker_name"></span>' . br();
$data .= _('Created') . ': <span id="picker_ctime"></span>' . br();
$data .= _('Files') . ': <span id="picker_nfiles"></span>' . br();
$data .= _('Mysql DB\'s') . ': <span id="picker_nmdb"></span>' . br();
$data .= _('AstDB\'s') . ': <span id="picker_nadb"></span>' . br();
$data .= '</div>';
$table->add_row(array('data' => '', 'id' => 'list_tree'), $data);
$html .= $table->generate();


$html .= br(2);

$html .= form_submit('submit', _('Go!'));
$html .= form_submit('submit', _('Download'));
$html .= form_close();
$html .= br(15);
$html .= '<script type="text/javascript" src="modules/backup/assets/js/views/restore.js"></script>';
$html .= '<script type="text/javascript" src="modules/backup/assets/js/views/jquery.jstree.min.js"></script>';
echo $html;
