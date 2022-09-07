<?php
global $amp_conf;
include_once ($amp_conf['AMPWEBROOT'].'/admin/libraries/issabelpbx_conf.class.php');
$issabelpbx_conf =& issabelpbx_conf::create();
$issabelpbx_conf->set_conf_values(array('JQUERY_VER' => '3.6.0'),true);
if (!$issabelpbx_conf->conf_setting_exists('LANGUAGE')) {
    $value = $_COOKIE['lang'];
    $set['value'] = $value;
    $set['defaultval'] = 'en_US';
    $set['readonly'] = 0;
    $set['hidden'] = 0;
    $set['level'] = 3;
    $set['module'] = '';
    $set['category'] = 'GUI Behavior';
    $set['emptyok'] = 0;
    $set['sortorder'] = 10;
    $set['name'] = 'Language';
    $set['description'] = 'General Language Setting for Web Admin';
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('LANGUAGE',$set,true);
}
