<?php

// Este script es un wrapper para que issabelPBX funcione sin mayores modificaciones
// Ha sido creado debido a que issabelPBX tiene muchos links quemados que referencian
// al script config.php
if(isset($_GET['display'])){
    if($_GET['display']=="fop2users" || $_GET['display']=="fop2groups" || $_GET['display']=="fop2buttons")
	$_GET['menu'] = $_GET['display'];
    else
	$_GET['menu'] = "pbxadmin";
}else
    $_GET['menu'] = "pbxadmin";

if( (isset($_GET['fw_popover']) && $_GET['fw_popover']==1) || (isset($_POST['fw_popover']) && $_POST['fw_popover']==1) ||
    isset($_GET['fw_popover_process']) || isset($_POST['fw_popover_process']) ){
    $_GET['rawmode']  = 'yes';
    $_POST['rawmode'] = 'yes';
}

include "/var/www/html/index.php";

?>
