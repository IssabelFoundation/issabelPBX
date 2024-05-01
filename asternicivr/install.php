<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$issabelpbx_conf =& issabelpbx_conf::create();
// Config Section
$set = array();
$set['value'] = false;
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = 'asternicivr';
$set['category'] = 'Asternic IVR Module';
$set['emptyok'] = 0;
$set['sortorder'] = 40;
$set['name'] = 'Register IVR Selections';
$set['description'] = "When enabled, IVR selections will be reported by Asternic Call Center Stats PRO in the URL field";
$set['type'] = CONF_TYPE_BOOL;
$issabelpbx_conf->define_conf_setting('IVR_REGISTER_OPTIONS_ASTERNIC',$set,true);

out(__("done"));

?>
