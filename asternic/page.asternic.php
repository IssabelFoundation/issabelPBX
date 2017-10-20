<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$settings = asternic_get_details();

foreach ($settings as $key => $val) {
	$var[$val['keyword']] = isset($_REQUEST[$val['keyword']]) ? $_REQUEST[$val['keyword']] : $val['value'];
	$$val['keyword'] = $var[$val['keyword']];
}

$checked = (isset($ivr_logging) && $ivr_logging == 'true')?'CHECKED':'';

echo '<h2 id="title">Asternic</h2>';
echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
echo '<input type="hidden" name="action" value="save">'; 
echo '<br /><br />';

$table = new CI_Table();

$table->add_row( _('Settings'));
$table->add_row('<hr class="qmhr">');
$table->add_row('<a href="javascript:void(null)" class="info">Log IVR Selections <span style="left: -18px; display: none; ">' . _('When checked, IVR selections will be reported by Asternic') . '</span></a>', '<input type="checkbox" name="ivr_logging" value="true" ' . $checked . '>');	
$table->add_row('');
$table->add_row('');
$table->add_row('<input type="submit" name="' . _("Submit Changes"). '">');

echo $table->generate();	

echo '</form><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />';
