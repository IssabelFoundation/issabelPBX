<?php

/* If displaying popOver, we add a js script to prepare the page such as removing
 * rnav, adding fw_popover_process hidden field, etc. If processing the page
 * we need to get the latest drawselects with thosen goto target if we have it
 * and send it back. This could be more efficient by only getting the options for
 * the target and possibly related categories but the overhead is minimal to get
 * a single copy of the select box structure.
 */
$html = '';
switch($popover_mode) {
case 'display':
	$html .= "<script>popOverDisplay();</script>";
	break;
case 'process':
	// Before calling drawselects we need to:
	//  - set the 'environment' like the parent page it is being generated for
	//  - tell it to dump it's cached internal structures and re-generate them
	// This is necessary because callback functions that drawselects uses can
	// generate context dependent results based on the calling module/display page
	//
	global $module_name, $module_page;
	$module_name_bak = $module_name;
	$module_page_bak = $module_page;
	$module_name = $_SESSION['module_name'];
	$module_page = $_SESSION['module_page'];
	$gotodest = fwmsg::get_dest();
	$drawselects_json = json_encode(drawselects($gotodest, 0, false, false, '', false, false, true));
	$html .= '<script>parent.closePopOver(' . $drawselects_json . ');</script>';
	$module_name = $module_name_bak;
	$module_page = $module_page_bak;
	break;
}
$html .= "\n";
echo $html;

