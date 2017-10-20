<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

//remove all crons
edit_crontab($amp_conf['AMPBIN'] . '/backup.php');

sql('DROP TABLE backup');
sql('DROP TABLE backup_cache');
sql('DROP TABLE backup_details');
sql('DROP TABLE backup_items');
sql('DROP TABLE backup_server_details');
sql('DROP TABLE backup_servers');
sql('DROP TABLE backup_template_details');
sql('DROP TABLE backup_templates');

?>
