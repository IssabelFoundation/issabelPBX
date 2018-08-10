<?php 
/* $Id: page.paging.php 1159 2006-03-16 14:36:27Z qldrob $ */
//Copyright (C) 2006 Rob Thomas (xrobau@gmail.com)
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$get_vars = array(
		'action'		=> '',
		'announce'		=> '',
		'conflict_url'	=> '',
		'default_group'	=> 0,
		'description'	=> '',
		'display'		=> 'paging',
		'duplex'		=> 0,
		'extdisplay'	=> '',
		'force_page'	=> 0,
		'pagegrp'		=> '',
		'pagelist'		=> '',
		'pagenbr'		=> '',
		'Submit'		=> '',
		'type'			=> 'tool',

);

foreach ($get_vars as $k => $v) {
	$vars[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
}
$vars['pagenbr'] = trim($vars['pagenbr']);
if ($vars['Submit'] == _('Delete')) {
	$vars['action'] = 'delete';
	$_REQUEST['action'] = 'delete';
}

//action actions
switch ($vars['action']) {
	case 'delete':
		paging_del($vars['extdisplay']);
		break;
	case 'submit':
		//TODO: issue, we are deleting and adding at the same time so remeber later to check
		//      if we are deleting a destination
		$usage_arr = array();
		if ($vars['pagegrp'] != $vars['pagenbr']) {
			$usage_arr = framework_check_extension_usage($vars['pagenbr']);
		}
		if ($usage_arr) {
			$vars['conflict_url'] = framework_display_extension_usage_alert($usage_arr);
			break;
		} else {
			//limit saved devices to PAGINGMAXPARTICIPANTS
			if (isset($amp_conf['PAGINGMAXPARTICIPANTS']) 
				&& $amp_conf['PAGINGMAXPARTICIPANTS']
			) {
				$vars['pagelist'] = array_slice(
									$vars['pagelist'], 
									0, 
									$amp_conf['PAGINGMAXPARTICIPANTS']);
			}

			paging_modify(
				$vars['pagegrp'], 
				$vars['pagenbr'], 
				$vars['pagelist'], 
				$vars['force_page'], 
				$vars['duplex'], 
				$vars['description'], 
				$vars['default_group']
			);
			$_REQUEST['action'] = $vars['action'] = 'modify';
			if ($vars['extdisplay'] === '') {
				$_REQUEST['extdisplay'] =
				$vars['extdisplay'] = $vars['pagenbr'];
			}
			redirect_standard('extdisplay', 'action');
		}
		break;
	case 'save_settings':
		$def = paging_get_autoanswer_defaults(true);
		$d = '';
		
		if (ctype_digit($vars['announce'])) {
			$r = recordings_get($vars['announce']);
			if ($r) {
				$vars['announce'] = $r['filename'];
			} else {
				$vars['announce'] = 'beep';
			}
			$a = 'A(' . $vars['announce'] . ')';
		} elseif ($vars['announce'] == 'none') {
			$a = 'A()';
		} elseif ($vars['announce'] == 'beep') {
			$a = 'A(beep)';
		}

		//if doptions is already set
		if (isset($def['DOPTIONS'])) {
			preg_match('/A\((.+?)\)/', $def['DOPTIONS'], $m);

			//if we already have an A() options, strip it out & replace it
			if (isset($m[0])) {
				$d = str_replace($m[0], $a, $def['DOPTIONS']);
			//otherwise, append it to whats already there
			} else {
				$d = $def['DOPTIONS'] . $a;
			}
		//if we dont have doptions, and the annoucement isnt 'beep'
		//(i.e. the defualt), set d
		} elseif ($vars['announce'] != 'beep') {
				$d = $a;
		}
		
		paging_set_autoanswer_defaults(array('DOPTIONS' => $a));
		needreload();
		break;
	default:
		break;
}

//rnav
$vars['groups'] = paging_list();
echo load_view(dirname(__FILE__) . '/views/rnav.php', $vars);

//view actions
switch ($vars['action']) {
	case 'add':
	case 'modify':
	case 'submit':
		if ($vars['extdisplay']) {
			$vars = array_merge($vars, paging_get_pagingconfig($vars['extdisplay']));
			$vars['devices'] = paging_get_devs($vars['extdisplay']);
		} else {
			$vars['devices'] = array();
		}
		$vars['hooks'] = $module_hook->hookHtml;
		foreach (core_devices_list() as $d) {
			$vars['device_list'][$d[0]] = $d[0] . ' - ' . $d[1];
		}
		$vars['amp_conf'] = $amp_conf;
		echo load_view(dirname(__FILE__) . '/views/page_group.php', $vars);
		break;
	case 'settings':
	case 'save_settings':
		//build recordings list
		$vars['rec_list']['none'] = _('None');
		$vars['rec_list']['beep'] = _('Default');
		
		if (!function_exists('recordings_list')) {
			$announce = 'default';
		} else {
			//build recordings list
			foreach (recordings_list() as $rec) {
				$vars['rec_list'][$rec['id']] = $rec['displayname'];
			}

			//get paging defaults
			$def = paging_get_autoanswer_defaults(true);
			$vars['announce'] = 'beep';//defaults to beep!
			
			if (isset($def['DOPTIONS'])) {
				preg_match('/A\((.*?)\)/', $def['DOPTIONS'], $m);
				//blank file? That would be 'none'
				if (isset($m[0]) && (!isset($m[1]) || !$m[1])) {
					$vars['announce'] = 'none';
				//otherwise, get the ID of the system recording
				} elseif(isset($m[0], $m[1])) {
					foreach (recordings_list() as $raw) {
						if ($raw['filename'] == $m[1]) {
							$vars['announce'] = $raw['id'];
							break;
						}
					}
				}
			}
		}
		echo load_view(dirname(__FILE__) . '/views/settings.php', $vars);
		break;
	case 'delete':
	default:
		$disabled = '(' . _('Disabled') . ')';

		$fcc = new featurecode('paging', 'intercom-prefix');
		$vars['intercom_code'] = $fcc->getCodeActive();
		unset($fcc);

		$fcc = new featurecode('paging', 'intercom-on');
		$vars['oncode'] = $fcc->getCodeActive();
		unset($fcc);
		if ($vars['oncode'] === '') {
			$vars['oncode'] = $disabled;
		}

		$fcc = new featurecode('paging', 'intercom-off');
		$vars['offcode'] = $fcc->getCodeActive();
		unset($fcc);
		if ($vars['offcode'] === '') {
			$vars['offcode'] = $disabled;
		}

		echo load_view(dirname(__FILE__) . '/views/overview.php', $vars);
		break;
}
?>
