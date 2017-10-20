<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

outn(_("dropping table asternic.."));
sql('DROP TABLE IF EXISTS `asternic_options`');
out(_("done"));

