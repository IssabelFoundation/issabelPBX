<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

//for translation only
if (false) {
__("Call Forward");
__("Call Forward All Activate");
__("Call Forward All Deactivate");
__("Call Forward All Prompting Deactivate");
__("Call Forward Busy Activate");
__("Call Forward Busy Deactivate");
__("Call Forward Busy Prompting Deactivate");
__("Call Forward No Answer/Unavailable Activate");
__("Call Forward No Answer/Unavailable Deactivate");
__("Call Forward Toggle");
}

// Unconditional Call Forwarding
$fcc = new featurecode('callforward', 'cfon');
$fcc->setDescription(__('Call Forward All Activate'));
$fcc->setDefault('*72');
$fcc->update();
unset($fcc);

$fcc = new featurecode('callforward', 'cfpon');
$fcc->setDescription(__('Call Forward All Prompting Activate'));
$fcc->setDefault('*720');
$fcc->update();
unset($fcc);

$fcc = new featurecode('callforward', 'cfoff');
$fcc->setDescription(__('Call Forward All Deactivate'));
$fcc->setDefault('*73');
$fcc->update();
unset($fcc);

$fcc = new featurecode('callforward', 'cfoff_any');
$fcc->setDescription(__('Call Forward All Prompting Deactivate'));
$fcc->setDefault('*74');
$fcc->update();
unset($fcc);

// Call Forward on Busy
$fcc = new featurecode('callforward', 'cfbon');
$fcc->setDescription(__('Call Forward Busy Activate'));
$fcc->setDefault('*90');
$fcc->update();
unset($fcc);

$fcc = new featurecode('callforward', 'cfbpon');
$fcc->setDescription(__('Call Forward Busy Prompting Activate'));
$fcc->setDefault('*900');
$fcc->update();
unset($fcc);

$fcc = new featurecode('callforward', 'cfboff');
$fcc->setDescription(__('Call Forward Busy Deactivate'));
$fcc->setDefault('*91');
$fcc->update();
unset($fcc);

$fcc = new featurecode('callforward', 'cfboff_any');
$fcc->setDescription(__('Call Forward Busy Prompting Deactivate'));
$fcc->setDefault('*92');
$fcc->update();
unset($fcc);

// Call Forward on No Answer/Unavailable (i.e. phone not registered)
$fcc = new featurecode('callforward', 'cfuon');
$fcc->setDescription(__('Call Forward No Answer/Unavailable Activate'));
$fcc->setDefault('*52');
$fcc->update();
unset($fcc);

$fcc = new featurecode('callforward', 'cfupon');
$fcc->setDescription(__('Call Forward No Answer/Unavailable Prompting Activate'));
$fcc->setDefault('*520');
$fcc->update();
unset($fcc);

$fcc = new featurecode('callforward', 'cfuoff');
$fcc->setDescription(__('Call Forward No Answer/Unavailable Deactivate'));
$fcc->setDefault('*53');
$fcc->update();
unset($fcc);

$fcc = new featurecode('callforward', 'cf_toggle');
$fcc->setDescription(__('Call Forward Toggle'));
$fcc->setDefault('*740');
$fcc->update();
unset($fcc);

?>
