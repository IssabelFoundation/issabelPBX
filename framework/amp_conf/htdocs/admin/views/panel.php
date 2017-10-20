<?php

$template['amp_conf'] = &$amp_conf;
$template['title'] = $title;
$template['content'] = 
	'<div id="panelframe">'.
	'<iframe width="97%" height="600" frameborder="0" align="top" src="../panel/index_amp.php?context='.$deptname.'"></iframe>'.
	'</div>';
show_view($amp_conf['VIEW_ISSABELPBX'], $template);

?>
