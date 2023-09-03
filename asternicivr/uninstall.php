<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$issabelpbx_conf =& issabelpbx_conf::create();
$issabelpbx_conf->remove_conf_setting('IVR_REGISTER_OPTIONS_ASTERNIC');
out(__("done"));

