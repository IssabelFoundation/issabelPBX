<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

echo "dropping table setcid..";
sql("DROP TABLE IF EXISTS `setcid`");
echo "done<br>\n";

?>
