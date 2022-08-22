<?php 
/* $Id: page.ivr.php 1003 2006-03-01 17:05:10Z diego_iastrubni $ */
//  License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Copyright (C) 2004 Coalescent Systems Inc. (info@coalescentsystems.ca)
//  Copyright 2017 Issabel Foundation

if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$get_vars = array(
				'action' 		=> '',
				'id'			=> '',
				'extdisplay'	=> '',
				'display'		=> ''
);

foreach ($get_vars as $k => $v) {
	$var[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
	$$k = $var[$k];//todo: legacy support, needs to GO!
}

$id = $extdisplay;

echo load_view(dirname(__FILE__) . '/views/rnav.php', array('ivr_results' => ivr_get_details()) + $var);
