<?php /*$Id*/
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
/* 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of version 2 the GNU General Public
 * License as published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 */
?>
<table>
<tr>
<td>
<div id="phpinfo">
<?php 
    ob_start () ;
    phpinfo () ;
    $pinfo = ob_get_contents () ;
    ob_end_clean () ;
    echo ( preg_replace ( '%^.*<body>(.*)</body>.*$%ms', '$1', $pinfo ) ) ;
?>
</div>
</td>
</tr>
</table>
