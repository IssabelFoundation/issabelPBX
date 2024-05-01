<?php
$list = array(
			'backup' 			=> __('Backups'),
			'backup_restore'	=> __('Restore'),
			'backup_servers'	=> __('Servers'),
			'backup_templates'	=>  __('Templates')
		);
		
foreach ($list as $k => $v) {
	// If current user does not have access to this sub-menu then don't display it
	//
	if (is_object($_SESSION["AMP_user"]) && !$_SESSION["AMP_user"]->checkSection($k)) {
		continue;
	}
	$li[] = '<a href="config.php?display=' . $k . '"'
			. ( $display == $k ? ' class="current" ' : '')
			. '>' 
			. $v . '</a>';
}
$li[] = '<hr />';
