<?php

$template['amp_conf'] = &$amp_conf;

$template['title'] = $title;
$template['content'] = '';

$template['content'] .= "<div id=\"reportnav\" ><ul>";
foreach ($menu  as $key=>$value) {
	$template['content'] .= "<li><nobr><a id=\"".(($display==$key) ? 'current':'')."\" href=\"reports.php?display=".$key."\">".$value."</a><nobr></li>";
}
$template['content'] .= "</ul></div>";

$template['content'] .= 
	'<div id="reportframe">'.
	'<iframe width="97%" height="2000" frameborder="0" align="top" scrolling="auto" src="config.php?handler=cdr&s='.$display.($display=='1' ? '&posted=1' : '').'"></iframe>'.
	'</div>';

show_view($amp_conf['VIEW_ISSABELPBX'], $template);

?>
