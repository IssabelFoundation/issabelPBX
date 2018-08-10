<?php /* $Id: page.ivr.php 1003 2006-03-01 17:05:10Z diego_iastrubni $ */
//Copyright (C) 2004 Coalescent Systems Inc. (info@coalescentsystems.ca)
//
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

/* $Id$ */

$get_vars = array(
				'action' 		=> '',
				'id'			=> '',
				'display'		=> ''
);
foreach ($get_vars as $k => $v) {
	$var[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
	$$k = $var[$k];//todo: legacy support, needs to GO!
}

echo load_view(dirname(__FILE__) . '/views/rnav.php', array('ivr_results' => ivr_get_details()) + $var);

if (!$action && !$id) {
?>
<h2><?php echo _("IVR"); ?></h2>
<br/><br/>
<a href="config.php?type=setup&display=ivr&action=add">
	<input type="button" value="<?php echo _("Add a new IVR")?>" id="new_dir">
</a>
<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
<br/><br/><br/><br/><br/><br/><br/>

<?php
}


?>
