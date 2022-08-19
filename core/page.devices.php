<?php /* $Id$ */

if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
?>
<?php 
$devices = core_devices_list();
drawListMenu($devices, $type, $display, $extdisplay);
?>
