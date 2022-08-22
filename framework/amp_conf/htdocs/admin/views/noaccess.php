<?php
$html = '';
$html .= '<h2>' . _('Not found') .'</h2>';
$html .= _('The section you requested does not exist or you do not have access '
	. 'to it.');
//show_view($amp_conf['VIEW_ISSABELPBX'], $template);
echo $html;
?>
