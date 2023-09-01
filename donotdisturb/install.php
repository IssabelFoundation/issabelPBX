<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//for translation only
if (false) {
__("Do-Not-Disturb (DND)");
__("DND Activate");
__("DND Deactivate");
__("DND Toggle");
}

// Register FeatureCode - Activate
$fcc = new featurecode('donotdisturb', 'dnd_on');
$fcc->setDescription('DND Activate');
$fcc->setDefault('*78');
$fcc->update();
unset($fcc);

// Register FeatureCode - Deactivate
$fcc = new featurecode('donotdisturb', 'dnd_off');
$fcc->setDescription('DND Deactivate');
$fcc->setDefault('*79');
$fcc->update();
unset($fcc);	

// Register FeatureCode - Activate
$fcc = new featurecode('donotdisturb', 'dnd_toggle');
$fcc->setDescription('DND Toggle');
$fcc->setDefault('*76');
$fcc->update();
unset($fcc);

?>
