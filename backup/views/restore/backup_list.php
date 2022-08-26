<?php

$html = '<div class="content">';
$html .= heading(_('Restore'), 2);
$html .= form_open($_SERVER['REQUEST_URI'], array('id' => 'files_browes_frm', 'class'=>'mx-2'));
$html .= form_hidden('action', 'restore');
$table = new CI_Table;
//files


$template_list = '<ul id="templates" class="sortable">';
foreach ($templates as $t) {
	$template_list .= '<li data-template="' . rawurlencode(json_encode($t['items'])) . '"'
					. ' title="' . $t['desc'] . '"'
					.'>' 
					. '<a class="button is-small" href="javascript:void(0);">'
					. '<span class="dragable"></span>'
					. _($t['name'])
					. '</a>'
					. '</li>';
}
$template_list .= '</ul>';

$files = '';
$files .= '<div id="restore_items">';
$files .= '<script type="text/javascript">var FILE_LIST=';
$files .= json_encode(backup_jstree_json_backup_files($manifest['file_list']));
$files .= '</script>';
$files .= '<div id="backup_files_container"><div id="backup_files">';
$files .= '</div></div>';

//databases
if ($manifest['fpbx_db'] || $manifest['astdb']) {
	$files .= br(2);
	$files .= ipbx_label(_('PBX Settings'), _('Restore all setting stored in the database'));
	$files .= ' ' . form_checkbox('restore[settings]', 'true');
}

//cdr's
if ($manifest['fpbx_cdrdb']) {
	$files .= br(2);
	$files .= ipbx_label(_('CDR\'s'), _('Restore CDR records stored in this backup'));
	$files .= ' ' . form_checkbox('restore[cdr]', 'true');
}
$files .= '</div>';
$files .= '<div id="items_over">' . _('drop zone') . '</div>';

$html .= "<div class='columns is-8'>";
$html .= "<div class='column is-three-quarters'>";
$html .= ipbx_label(_('Select files and databases to restore')). br(2);
$html .= $files;
$html .= "</div>";
$html .= "<div class='column'>";
$html .= ipbx_label(_('Templates'));
$html .= $template_list;
$html .= "</div>";
$html .= "</div>";


/*
$table->set_heading(
			_('Select files and databases to restore'), _('Templates'));
$table->add_row($files, array('data' => $template_list, 'style' => 'padding-left: 100px;padding-right: 100px'));
$html .= $table->generate();
$html .= $table->clear();
 */

$html .= br(2);
$html .= form_submit(array(
	'name'  => 'submit',
	'value' => _('Restore'),
    'id'    => 'run_restore',
    'class' => 'button is-rounded'
)); 

$html .= form_close();
$html .= br(15);
$html .= '<script type="text/javascript" src="modules/backup/assets/js/views/restore.js"></script>';
$html .= '<script type="text/javascript" src="modules/backup/assets/js/views/jquery.jstree.min.js"></script>';

include("frameworkmsg.php");
$html .= "</div>";
echo $html;
