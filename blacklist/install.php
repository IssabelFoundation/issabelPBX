<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Copyright (C) 2006 Magnus Ullberg (magnus@ullberg.us)
//	Copyright 2013 Schmooze Com Inc.
//
// For translations
if (false) {
_("Blacklist a number");
_("Remove a number from the blacklist");
_("Blacklist the last caller");
_("Blacklist");
}

$fcc = new featurecode('blacklist', 'blacklist_add');
$fcc->setDescription('Blacklist a number');
$fcc->setDefault('*30');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);

$fcc = new featurecode('blacklist', 'blacklist_remove');
$fcc->setDescription('Remove a number from the blacklist');
$fcc->setDefault('*31');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);

$fcc = new featurecode('blacklist', 'blacklist_last');
$fcc->setDescription('Blacklist the last caller');
$fcc->setDefault('*32');
$fcc->update();
unset($fcc);
?>
