<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$settings = asternicivr_get_details();

foreach ($settings as $key => $val) {
    $var[$val['keyword']] = isset($_REQUEST[$val['keyword']]) ? $_REQUEST[$val['keyword']] : $val['value'];
    $$val['keyword'] = $var[$val['keyword']];
}

$checked = (isset($ivr_logging) && $ivr_logging == 'true')?'CHECKED':'';

echo '<h2 id="title">Asternic IVR</h2>';
echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
echo '<input type="hidden" name="action" value="save">'; 
echo __('This module allows you to define if you want IVR options to be set in the URL variable passed to the Queue application. In this way you can use Asternic Call Center Stats PRO to run reports with IVR selections by installing Asternic URL plugin.');

$table = new CI_Table();
$table->add_row(array('colspan' => 2, 'data' => heading(__('Settings'), 5) ));
$table->add_row('<a href="javascript:void(null)" class="info">'.__('Log IVR Selections').' <span style="left: -18px; display: none; ">' . __('When checked, IVR selections will be reported by Asternic in the URL field') . '</span></a>', '&nbsp;<input type="checkbox" name="ivr_logging" value="true" ' . $checked . '>');    
$table->add_row('&nbsp;');
$table->add_row('&nbsp;');
$table->add_row('<input type="submit" value="' . __("Submit Changes"). '">');

echo $table->generate();    

echo '</form><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />';
