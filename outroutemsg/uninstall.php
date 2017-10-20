<?php /* $Id: uninstall.php  $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

sql("DROP TABLE IF EXISTS `outroutemsg`");

?>
