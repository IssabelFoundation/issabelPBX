<?php
require_once(dirname(__FILE__) .'/header.php');
$opts = logfiles_get_opts();
$opts['logfiles'][] = 	//always add a blank row, will work as default when nothing is set
	$logfiles[] = array(
						'name'		=> '', 
						'debug'		=> '', 
						'dtmf'		=> 'off', 
						'error'		=> '', 
						'fax'		=> 'off', 
						'notice'	=> '', 
						'verbose'	=> '', 
						'warning'	=> '',
						'security'	=> 'off'
			);
$var = array_merge($var, $opts);
//dbug('passing to view', $var);
echo load_view(dirname(__FILE__) . '/views/settings.php', $var);
