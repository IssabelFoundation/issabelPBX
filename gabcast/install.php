<?php
//for translation only
if (false) {
_("Connect to Gabcast");
}

// Enable direct dial to Gabcast as a feature code
$connecttogabcast = _("Connect to Gabcast");
$fcc = new featurecode('gabcast', 'gabdial');
$fcc->setDescription('Connect to Gabcast');
$fcc->setDefault('*422');
$fcc->update();
unset($fcc);

?>
