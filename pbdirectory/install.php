<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//for translation only
if (false) {
_("Phonebook dial-by-name directory");
}

// Enable phonebook directory as a feature code
$fcc = new featurecode('pbdirectory', 'app-pbdirectory');
$fcc->setDescription('Phonebook dial-by-name directory');
$fcc->setDefault('411');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);

?>
