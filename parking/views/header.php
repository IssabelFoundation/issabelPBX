<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); } 

$type        = '';
$display     = 'parking';
$rnaventries = array();
foreach ($lots as $l) {
    $extra='';
    if($l['defaultlot']=='yes') {
        $extra=' <span class="tag is-info">'._('Default').'</span>';
    }
    $rnaventries[] = array($l['id'],$l['name'].$extra,$l['parkext']);
}
drawListMenu($rnaventries, $type, $display, $extdisplay);

?>
<!--div class="rnav">
	<ul>
        <li><a href="config.php?display=parking&type=setup&action=add"><?php echo _('Add Park Slot');?></a></li>

        <li><hr></li>
        <?php foreach($lots as $l) {?>
        <li><a href="config.php?display=parking&amp;id=<?php echo $l['id']?>&amp;action=modify"><?php echo $l['defaultlot'] == 'yes' ? '<strong>[D]</strong> ' : ''?><?php echo $l['name']?></a></li>
        <?php } ?>
	</ul>
</div-->
<div class='content'>
<?php

$helptext = _("This module is used to configure Parking Lot(s) in Asterisk.");
$helptext.= "<br/><br/>";
$helptext.= _("Simply transfer the call to said parking lot extension. Asterisk will then read back the parking lot number the call has been placed in. To retrieve the call simply dial that number back.");
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

if($extdisplay) {
     echo "<div class='is-flex'><h2>"._("Edit Parking Lot").": ".$description."</h2>$help</div>";
}else {
     echo "<div class='is-flex'><h2>"._("Add Parking Lot")."</h2>$help</div>";

}
