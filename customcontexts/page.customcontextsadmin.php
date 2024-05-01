<?php /* $Id: page.customcontextsadmin.php $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
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

$dispnum = 'customcontextsadmin'; //used for switch on config.php

//isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';


?>

<?php 
$contexts = customcontexts_getcontextslist();
$rnavitems=array();
foreach($contexts as $idx=>$context) {
   $rnavitems[]=array($context[0],$context[1],'');
}
drawListMenu($rnavitems, $type, $display, $extdisplay);
echo "<div class='content'>";
?>
