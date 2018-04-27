<?php /* $Id: page.dialplaninjection.php $ */
//
//This program is free software; you can redistribute it and/or
//modify it under the terms of the GNU General Public License
//as published by the Free Software Foundation; either version 2
//of the License, or (at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.

$dispnum = 'dialplaninjection'; //used for switch on config.php

?>


<div class="rnav">
<?php 
$contexts = dialplaninjection_getinjections();
drawListMenu($contexts, $skip, $type, $display, $extdisplay, _("Injection"));
?>
</div>

<!--
			<tr><td colspan="2"><br><h5><?php echo _("Destination if no answer")?>:<hr></h5></td></tr>
-->
<?php 
//draw goto selects
//echo drawselects($goto,0);
?>