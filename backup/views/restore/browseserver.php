<?php

$html = '<div class="content">';
$html .= heading(__('Restore'), 2);
$html .= form_open($_SERVER['REQUEST_URI'], array('id' => 'restore_browes_frm', 'class'=>'mx-2'));
$html .= form_hidden('restore_source', 'file');
$html .= form_hidden('restore_path', '');
$html .= form_hidden('action', 'backup_list');


$table = new CI_Table;
$html .= __('Select a file and click go');
$html .= br(2);

$data = '';
$data .= '<div id="list_data">';
$data .= __('Name') . ': <span id="picker_name"></span>' . br();
$data .= __('Created') . ': <span id="picker_ctime"></span>' . br();
$data .= __('Files') . ': <span id="picker_nfiles"></span>' . br();
$data .= __('Mysql DB\'s') . ': <span id="picker_nmdb"></span>' . br();
$data .= __('AstDB\'s') . ': <span id="picker_nadb"></span>' . br();
$data .= '</div>';
$table->add_row(array('data' => '', 'id' => 'list_tree'), $data);
$html .= $table->generate();


$html .= br(2);

$html .= form_submit('submit', __('Go!'), ' class="button is-rounded" ');
$html .= form_submit('submit', __('Download'), ' class="button is-rounded mx-2" ');
$html .= form_close();
$html .= br(15);
$html .= '<script type="text/javascript" src="modules/backup/assets/js/views/restore.js"></script>';
$html .= '<script type="text/javascript" src="modules/backup/assets/js/views/jquery.jstree.min.js"></script>';

include("frameworkmsg.php");

/*
$html.='<script>';
$html.='$(function() { ';
$html.= 'ipbx.msg.framework.backupstart = "'.__('Starting backup')."\";\n";
$html.= '});';
$html.='</script>';
*/

echo $html;
