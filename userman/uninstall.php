<?php
out('Remove all User Management tables');
$tables = array('issabelpbx_users', 'issabelpbx_users_settings');
foreach ($tables as $table) {
	$sql = "DROP TABLE IF EXISTS {$table}";
	$result = $db->query($sql);
	if (DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo());
	}
	unset($result);
}