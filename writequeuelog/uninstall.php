<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

echo "dropping table writequeuelog..";
sql("DROP TABLE IF EXISTS `writequeuelog`");
echo "done<br>\n";

?>
