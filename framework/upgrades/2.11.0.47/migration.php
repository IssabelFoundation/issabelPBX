<?php
global $amp_conf;
include_once ($amp_conf['AMPWEBROOT'].'/admin/libraries/issabelpbx_conf.class.php');
$issabelpbx_conf =& issabelpbx_conf::create();
$issabelpbx_conf->set_conf_values(array('JQUERYUI_VER' => '1.9.1'),true);
