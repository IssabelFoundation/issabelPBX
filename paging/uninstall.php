<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

// Don't bother uninstalling feature codes, now module_uninstall does it

echo "dropping table paging_overview..";
$sql = "DROP TABLE IF EXISTS paging_overview";
$result = $db->query($sql);
if(DB::IsError($result)) {
	echo "ERROR DELETING TABLE: ".$result->getDebugInfo();
}
echo "done<br>\n";

echo "dropping table paging_groups..";
$sql = "DROP TABLE IF EXISTS paging_groups";
$result = $db->query($sql);
if(DB::IsError($result)) {
	echo "ERROR DELETING TABLE: ".$result->getDebugInfo();
}
echo "done<br>\n";

echo "dropping table paging_phones..";
$sql = "DROP TABLE IF EXISTS paging_phones";
$result = $db->query($sql);
if(DB::IsError($result)) {
	echo "ERROR DELETING TABLE: ".$result->getDebugInfo();
}
echo "done<br>\n";

echo "dropping table paging_config..";
$sql = "DROP TABLE IF EXISTS paging_config";
$result = $db->query($sql);
if(DB::IsError($result)) {
	echo "ERROR DELETING TABLE: ".$result->getDebugInfo();
}
echo "done<br>\n";

echo "dropping table paging_autoanswer..";
$sql = "DROP TABLE IF EXISTS paging_autoanswer";
$result = $db->query($sql);
if(DB::IsError($result)) {
	echo "ERROR DELETING TABLE: ".$result->getDebugInfo();
}
echo "done<br>\n";

?>
