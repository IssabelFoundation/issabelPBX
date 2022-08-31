<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$settings = queuemetrics_get_details();

foreach ($settings as $key => $val) {
	$var[$val['keyword']] = isset($_REQUEST[$val['keyword']]) ? $_REQUEST[$val['keyword']] : $val['value'];
}

$checked = (isset($var['ivr_logging']) && $var['ivr_logging'] == 'true')?'CHECKED':'';

echo '<div class="content">';
echo '<h2 id="title">QueueMetrics</h2>';
echo '<form id="mainform" method="post" onsubmit="$.LoadingOverlay(\'show\')">';
echo '<input type="hidden" name="action" value="save">'; 

$table = new CI_Table();
$table->add_row(array('colspan' => 2, 'data' => heading(dgettext('amp','General Settings'), 5) ));

$checkbox = "<div class='field'><input type='checkbox' class='switch' id='ivr_logging' name='ivr_logging' value='true' $checked/><label style='height:auto; line-height:2em; padding-left:3em;' for='ivr_logging'>&nbsp;</label></div>";

//$table->add_row('<a href="javascript:void(null)" class="info">Log IVR Selections <span style="left: -18px; display: none; ">' . _('When checked, IVR selections will be reported by QueueMetrics') . '</span></a>', '<input type="checkbox" name="ivr_logging" value="true" ' . $checked . '>');	
$table->add_row('<a href="javascript:void(null)" class="info">'._('Log IVR Selections').' <span style="left: -18px; display: none; ">' . _('When checked, IVR selections will be reported by QueueMetrics') . '</span></a>', $checkbox);	
echo $table->generate();	

echo '</form></div>';
echo '<script>';
echo js_display_confirmation_toasts();
echo '</script>';
echo form_action_bar(''); 
