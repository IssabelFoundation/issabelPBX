<?php
//This file is part of IssabelPBX.
//
//

global $db;
global $amp_conf;


// need to remove the trunk created in the 'trunk' table and remove the reference made in the outbound routes.


$sql = "DROP TABLE IF EXISTS `trunkbalance`";
$check = $db->query($sql);
if (DB::IsError($check)) {
        die_issabelpbx( "Can not remove `trunkbalance` table: " . $check->getMessage() .  "\n");
}
$sql = "DELETE FROM `trunks` WHERE tech='custom' and name LIKE 'BAL_%' and channelid LIKE 'Balancedtrunk%'";
$check = $db->query($sql);
if (DB::IsError($check)) {
        die_issabelpbx( "Can not remove balanced trunk from `trunks` table: " . $check->getMessage() .  "\n");
}




needreload();

?>
