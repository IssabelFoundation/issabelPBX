<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;
global $amp_conf;

$pinsets = pinsets_list();
foreach ($pinsets as $item) {
	echo "removing ".$item['description']."..";
	pinsets_del($item['pinsets_id']);
	echo "done<br>\n";
}

echo "dropping table pinsets..";
sql('DROP TABLE IF EXISTS `pinsets`');
echo "done<br>\n";

echo "dropping table pinset_usage..";
sql('DROP TABLE IF EXISTS `pinset_usage`');
echo "done<br>\n";

?>
