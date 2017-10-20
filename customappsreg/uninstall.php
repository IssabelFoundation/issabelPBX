<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

echo "dropping table custom_destinations..";
sql("DROP TABLE IF EXISTS `custom_destinations`");
echo "done<br>\n";

echo "dropping table custom_extensions..";
sql("DROP TABLE IF EXISTS `custom_extensions`");
echo "done<br>\n";

?>
