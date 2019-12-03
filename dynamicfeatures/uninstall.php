<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

echo "dropping table dynamicfeatures..";
sql("DROP TABLE IF EXISTS `dynamicfeatures`");
echo "done<br>\n";


?>
