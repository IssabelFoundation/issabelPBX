<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
sql('DROP TABLE IF EXISTS ivr_details');
sql('DROP TABLE IF EXISTS ivr_entries');

if (function_exists('queues_ivr_delete_event')) {
	queues_ivr_delete_event();
}
?>
