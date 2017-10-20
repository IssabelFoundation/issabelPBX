<?php

require(dirname(__FILE__) . '/main.php');

if (isset($backup)){
	foreach ($backup as $b) {
		$li[] = '<a ' 
			. ( $id == $b['id'] ? ' class="current" ' : '') 
			. '" href="config.php?display=backup&action=edit&id=' 
			. $b['id'] . '">' 
			. $b['name'] 
			.'</a>';
	}
}	

echo '<div class="rnav">' . ul($li) . '</div>';
