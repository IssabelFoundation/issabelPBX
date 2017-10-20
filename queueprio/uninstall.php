<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

echo "dropping table queueprio..";
sql("DROP TABLE IF EXISTS `queueprio`");
echo "done<br>\n";

?>
