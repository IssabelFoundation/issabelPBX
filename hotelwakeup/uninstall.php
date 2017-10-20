<?php
print "Hotel Style Wakeups are being uninstalled.<br>";

// drop the hotelwakup table
$sql = "DROP TABLE IF EXISTS hotelwakeup";

$check = $db->query($sql);
if (DB::IsError($check)) {
        die_issabelpbx( "Can not delete `hotelwakeup` table: " . $check->getMessage() .  "\n");
}

// drop the hotelwakup_calls table
$sql = "DROP TABLE IF EXISTS hotelwakeup_calls";

$check = $db->query($sql);
if (DB::IsError($check)) {
        die_issabelpbx( "Can not delete `hotelwakeup_calls` table: " . $check->getMessage() .  "\n");
}

// Consider adding code here to scan thru the spool/asterisk/outgoing directory and removing 
// already wakeup calls that have been scheduled

?>