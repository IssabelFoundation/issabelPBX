<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

outn(_("dropping table queuemetrics.."));
sql('DROP TABLE IF EXISTS `queuemetrics_options`');
out(_("done"));

