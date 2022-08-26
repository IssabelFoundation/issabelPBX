<?php

$html = '<div class="content">';
$html .= heading(_('Restore'), 2);
$html .= form_open($_SERVER['REQUEST_URI'], array('id' => 'restore_browes_frm', 'class'=>'mx-2'));
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

$html .= form_submit('submit', _('Go!'), ' class="button is-rounded" ');
$html .= form_submit('submit', _('Download'), ' class="button is-rounded mx-2" ');
$html .= form_close();
$html .= br(15);
$html .= '<script type="text/javascript" src="modules/backup/assets/js/views/restore.js"></script>';
$html .= '<script type="text/javascript" src="modules/backup/assets/js/views/jquery.jstree.min.js"></script>';

include("frameworkmsg.php");

/*
$html.='<script>';
$html.='$(function() { ';
$html.= 'ipbx.msg.framework.backupstart = "'._('Starting backup')."\";\n";
$html.= '});';
$html.='</script>';
*/

echo $html;
