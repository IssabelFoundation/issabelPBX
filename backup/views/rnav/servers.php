<?php
require(dirname(__FILE__) . '/main.php');

if (isset($servers)){
	foreach ($servers as $s) {
		$li[] = '<a ' 
				. ( $id == $s['id'] ? ' class="current" ' : '') 
				. '" href="config.php?display=backup_servers&action=edit&id=' 
				. $s['id'] . '">' 
				. $s['name'] 
				. ' (' . $s['type'] . ')'
				.'</a>';
	}

}	

echo '<div class="rnav">' . ul($li) . '</div>';
