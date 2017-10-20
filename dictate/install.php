<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//for translation only
if (false) {
_("Dictation");
_("Perform dictation");
_("Email completed dictation");
}

// Register Feature Code - Perform Dictation
$fcc = new featurecode('dictate', 'dodictate');
$fcc->setDescription('Perform dictation');
$fcc->setDefault('*34');
$fcc->update();
unset($fcc);

// Email dictation to user
$fcc = new featurecode('dictate', 'senddictate');
$fcc->setDescription('Email completed dictation');
$fcc->setDefault('*35');
$fcc->update();
unset($fcc);


?>
