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
$rnaventries = array();
$contexts    = dialplaninjection_getinjections();
foreach ($contexts as $row) {
    $rnaventries[] = array($row[0],$row[1],'');
}
drawListMenu($rnaventries, $type, $display, $extdisplay);

//drawListMenu($contexts, $skip, $type, $display, $extdisplay, _("Injection"));
?>
<div class='content'>
