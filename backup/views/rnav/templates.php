<?php

require(dirname(__FILE__) . '/main.php');

if (isset($templates)){
	foreach ($templates as $t) {
		$li[] = '<a ' 
			. ( $id == $t['id'] ? ' class="current" ' : '') 
			. '" href="config.php?display=backup_templates&action=edit&id=' 
			. $t['id'] . '">' 
			. $t['name'] 
			.'</a>';
	}
}	

echo '<div class="rnav">' . ul($li) . '</div>';
