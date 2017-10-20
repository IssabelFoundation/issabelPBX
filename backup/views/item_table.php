<?php
$html = '';
$table = new CI_Table;

//item table
$table->set_template(array('table_open' => '<table class="alt_table" id="template_table">'));
$table->set_heading(_('Type'), _('Path/DB'), _('Exclude'), _('Delete'));

$table->add_row('', '', '', '');
$c = 0;
foreach($items as $i) {
	$c++;
	$d	= backup_template_generate_tr($c, $i, $immortal);
	$table->add_row($d['type'], $d['path'], $d['exclude'], $d['delete']);
}

$html .= $table->generate();
$html .= $table->clear();
$html .= br() . PHP_EOL;
if ($immortal != 'true') {
	$html .= '<img src="modules/backup/assets/images/add.png" style="cursor:pointer" title="Add Entry" id="add_entry" />';
}



//include javascript variables for add button
$html	.= '<script type="text/javascript">';
$file	= backup_template_generate_tr('TR_UID', array('type' => 'file', 'path' => '', 'exclude' => array()), '', true);
$dir	= backup_template_generate_tr('TR_UID', array('type' => 'dir', 'path' => '', 'exclude' => array()), '', true);
$mysql	= backup_template_generate_tr('TR_UID', array('type' => 'mysql', 'path' => '', 'exclude' => array()), '', true);
$astdb	= backup_template_generate_tr('TR_UID', array('type' => 'astdb', 'path' => '', 'exclude' => array()), '', true);

$html	.= 'template_tr = new Array();';
$html	.= 'template_tr["file"] = '		. json_encode($file)	. PHP_EOL;
$html	.= 'template_tr["dir"] = '		. json_encode($dir)		. PHP_EOL;
$html	.= 'template_tr["mysql"] = '	. json_encode($mysql)	. PHP_EOL;
$html	.= 'template_tr["astdb"] = '	. json_encode($astdb)	. PHP_EOL;
$html	.= '</script>'. PHP_EOL;
$data 	= array(
			''		=> '== ' . _('chose') . ' ==',
			'file'	=> 'File',
			'dir'	=> 'Directory',
			'mysql'	=> 'Mysql',
			'astdb'	=> 'Asterisk Database',
			);
$html	.= form_dropdown('add_tr_select', $data, '', 'style="display:none"');

$html .= '<script type="text/javascript" src="modules/backup/assets/js/views/templates.js"></script>';
echo $html;
