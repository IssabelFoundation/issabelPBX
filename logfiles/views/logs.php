<?php
$html = '';
$html = '<div id="logfiles_header">';
$html .= heading(_('Asterisk Log Files'), 2);

$logs = array('1'  => '/var/log/asterisk/full');
$html .= form_dropdown('logfile', $files, $full);

$lines = array(
			'name'			=> 'lines',
			'id'			=> 'lines',
			'value'			=> 500,
			'placeholder'	=> _('lines')
);
$html .= form_input($lines);

$show = array(
		'name'		=> 'show',
		'content'	=> _('Show'),
		'id'		=> 'show',
);
$html .= form_button($show);
$html .= '</div>';
$html .= '<div id="log_view" class="pre"></div>';
$html .= '<script type="text/javascript" src="/admin/modules/logfiles/assets/js/views/logs.js"></script>';
echo $html;
?>
