<?php
$li[] = '<a href="config.php?display=paging">'
		. _('Overview')
		. '</a>';
$li[] = '<a href="config.php?display=paging&action=settings">'
		. _('General Settings')
		. '</a>';
$li[] = '<a href="config.php?display=paging&action=add">'
		. _('New Paging Group')
		. '</a>';
$li[] = '<hr />';

foreach ($groups as $group) {
	$li[] = '<a href="config.php?display=paging&'
			. 'extdisplay=' . $group['page_group'] . '&'
			. 'action=modify"'
			. ( $extdisplay == $group['page_group'] 
				? ' class="current" ' 
				: '')
			. '>' 
			. ($group['description'] 
				? $group['description'] 
				: _('Page Group ') . $group['page_group'])
			. ($group['is_default']
				? ' [' . _('Default') . ']'
				: '')
			. '</a>';
}

//echo '<div class="rnav">' . ul($li) . '</div>';



$rnaventries = array();
//$rnaventries[] = array('-1',_('Overview'),'','','');
$rnaventries[] = array('',_('General Settings'),'','','&action=settings',false);

foreach ($groups as $group) {
    $rnaventries[] = array($group['page_group'],$group['description'],'');
}

if($extdisplay=="") { $extdisplay='-1'; }
drawListMenu($rnaventries, $type, $display, $extdisplay, '&action=add');


?>
<div class='content'>
