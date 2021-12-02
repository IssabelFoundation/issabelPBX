<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;

echo "dropping table tts..";
sql("DROP TABLE IF EXISTS `tts`");
sql("DROP TABLE IF EXISTS `tts_engines`");
echo "done<br>\n";

?>
