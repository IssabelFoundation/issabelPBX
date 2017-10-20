<?php

$li[] = '<a href="config.php?display='. urlencode($display) . '&action=add">' . _("Add IVR") . '</a>';


if (isset($ivr_results)){
	foreach ($ivr_results as $r) {
		$r['name'] = $r['name'] ? $r['name'] : 'IVR ID: ' . $r['id'];
		$li[] = '<a id="' . ( $id == $r['id'] ? 'current' : '') 
			. '" href="config.php?display=ivr&amp;action=edit&amp;id=' 
			. $r['id'] . '">' 
			. $r['name'] .'</a>';
	}
}	

echo '<div class="rnav">' . ul($li) . '</div>';
?>