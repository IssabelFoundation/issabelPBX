<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Copyright (C) 2005 mheydon1973
//	Copyright 2013 Schmooze Com Inc.
//
//for translation only
if (false) {
_("Call Waiting");
_("Call Waiting - Activate");
_("Call Waiting - Deactivate");
}

// Register FeatureCode - Activate
$fcc = new featurecode('callwaiting', 'cwon');
$fcc->setDescription('Call Waiting - Activate');
$fcc->setDefault('*70');
$fcc->update();
unset($fcc);

// Register FeatureCode - Deactivate
$fcc = new featurecode('callwaiting', 'cwoff');
$fcc->setDescription('Call Waiting - Deactivate');
$fcc->setDefault('*71');
$fcc->update();
unset($fcc);	
?>
