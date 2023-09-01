<?php

$li[] = '<a class="'.($id=='' ? 'current':'').'" href="config.php?display='. urlencode($display) . '&action=add">' . __("Add IVR") . '</a>';


if (isset($ivr_results)){
	foreach ($ivr_results as $r) {
		$r['name'] = $r['name'] ? $r['name'] : 'IVR ID: ' . $r['id'];
		$li[] = '<a class="' . ( $id == $r['id'] ? 'current' : '') 
			. '" href="config.php?display=ivr&amp;action=edit&amp;id=' 
			. $r['id'] . '">' 
			. $r['name'] .'</a>';
	}
}	

//echo '<div class="rnav">' . ul($li) . '</div>';




$rnaventries = array();
foreach ($ivr_results as $r) {
	$r['name'] = $r['name'] ? $r['name'] : 'IVR ID: ' . $r['id'];
    $rnaventries[] = array($r['id'],$r['name'],'');
}
drawListMenu($rnaventries, $type, $display, $extdisplay);


?>
<div class='content'>
