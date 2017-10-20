<?php

function inventorydb_list(){
	$sql = "SELECT id, empname FROM inventorydb";
	$results= sql($sql, "getAll");

	foreach($results as $result){
		$inventorys[] = array($result[0],$result[1]);
	}
	return isset($inventorys)?$inventorys:null;
}

function inventorydb_get($extdisplay){
	$sql="SELECT * FROM inventorydb where id=$extdisplay";
	$results=sql($sql, "getRow", DB_FETCHMODE_ASSOC);
	return isset($results)?$results:null;
}

function inventorydb_add($empnum, $empname, $building, $floor, $room, $section, $cubicle, $desk, $exten, $phusername, $phpassword, $mac, $serial, $device, $distdate, $ip, $pbxbox, $extrainfo){
	$sql  = "INSERT INTO inventorydb(empnum, empname, building, floor, room, section,";
	$sql .= "cubicle, desk, exten, phusername, phpassword, mac, serial, device,";
	$sql .= "distdate, ip, pbxbox, extrainfo)"; 
		
	$sql .= "VALUES ('$empnum', '$empname', '$building', '$floor', '$room', '$section',";
	$sql .= "'$cubicle', '$desk', '$exten', '$phusername', '$phpassword', '$mac', '$serial',";
	$sql .= "'$device', '$distdate', '$ip', '$pbxbox', '$extrainfo')";
	sql($sql);
}

function inventorydb_del($extdisplay){
	$sql="DELETE FROM inventorydb where id=$extdisplay";
	sql($sql);
}

function inventorydb_edit($extdisplay, $empnum, $empname, $building, $floor, $room, $section, $cubicle, $desk, $exten, $phusername, $phpassword, $mac, $serial, $device, $distdate, $ip, $pbxbox, $extrainfo){
	$sql="UPDATE inventorydb set empnum='$empnum' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE inventorydb set empname='$empname' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE inventorydb set building='$building' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE inventorydb set floor='$floor' where id='$extdisplay'";
	sql($sql);
        $sql="UPDATE inventorydb set room='$room' where id='$extdisplay'";
        sql($sql);
	$sql="UPDATE inventorydb set section='$section' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE inventorydb set cubicle='$cubicle' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE inventorydb set desk='$desk' where id='$extdisplay'";
	sql($sql);	
	$sql="UPDATE inventorydb set exten='$exten' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE inventorydb set phusername='$phusername' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE inventorydb set phpassword='$phpassword' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE inventorydb set mac='$mac' where id='$extdisplay'";
	sql($sql);	
	$sql="UPDATE inventorydb set serial='$serial' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE inventorydb set device='$device' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE inventorydb set distdate='$distdate' where id='$extdisplay'";
	sql($sql);	
	$sql="UPDATE inventorydb set ip='$ip' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE inventorydb set pbxbox='$pbxbox' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE inventorydb set extrainfo='$extrainfo' where id='$extdisplay'";
	sql($sql);

}

?>
