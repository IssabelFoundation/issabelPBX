<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
?>
<?php
$devices = core_devices_list();
if($devices===null) $devices=array();
$rnaventries = array();
foreach($devices as $idx=>$data) {
    $midev = core_devices_get($data[0]);
    $rnaventries[]=array($data[0],$data[1],'<span class="tag is-white tagfixed">'.$midev['tech'].'</span> '.$data[0]);
}
drawListMenu($rnaventries, $type, $display, $extdisplay);
?>
<div class='content'>
