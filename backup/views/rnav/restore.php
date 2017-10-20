<?php

require(dirname(__FILE__) . '/main.php');
$allowed = array('ftp', 'local', 'ssh');
if (isset($servers)){
	foreach ($servers as $s) {
		//only allow servers in $allowed
		if (!in_array($s['type'], $allowed)) { 
			continue;
		}
		
		$li[] = '<a ' 
			. ( $id == $s['id'] ? ' class="current" ' : '') 
			. '" href="config.php?display=backup_restore&id=' 
			. $s['id'] . '">' 
			. $s['name'] 
			. ' (' . $s['type'] . ')'
			.'</a>';
	}

}	

echo '<div class="rnav">' . ul($li) . '</div>';
