<?php
require_once(dirname(__FILE__) .'/header.php');
$var['files'] = logfiles_list();

//find 'full' file
foreach ($var['files'] as $k => $v) {
	if($v == 'full') {
		$var['full'] = $k;
		break;
	}
}
echo load_view(dirname(__FILE__) . '/views/logs.php', $var);