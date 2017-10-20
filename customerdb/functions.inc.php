<?php

function customerdb_list(){
	$sql = "SELECT id, name FROM customerdb";
	$results= sql($sql, "getAll");

	foreach($results as $result){
		$customers[] = array($result[0],$result[1]);
	}
	return isset($customers)?$customers:null;
}

function customerdb_get($extdisplay){
	$sql="SELECT * FROM customerdb where id=$extdisplay";
	$results=sql($sql, "getRow", DB_FETCHMODE_ASSOC);
	return isset($results)?$results:null;
}

function customerdb_add($name, $addr1, $addr2, $city, $state, $zip, $sip, $did, $device, $ip, $serial, $account, $email, $username, $password){
	$sql="INSERT INTO customerdb (name, addr1, addr2, city, state, zip, sip, did, device, ip, serial, account, email, username, password) values ('$name', '$addr1', '$addr2', '$city', '$state', '$zip', '$sip', '$did', '$device', '$ip', '$serial', '$account', '$email', '$username', '$password')";
	sql($sql);
}

function customerdb_del($extdisplay){
	$sql="DELETE FROM customerdb where id=$extdisplay";
	sql($sql);
}

function customerdb_edit($extdisplay, $name, $addr1, $addr2, $city, $state, $zip, $sip, $did, $device, $ip, $serial, $account, $email, $username, $password){
	$sql="UPDATE customerdb set name='$name' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE customerdb set addr1='$addr1' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE customerdb set addr2='$addr2' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE customerdb set city='$city' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE customerdb set state='$state' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE customerdb set zip='$zip' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE customerdb set sip='$sip' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE customerdb set did='$did' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE customerdb set device='$device' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE customerdb set serial='$serial' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE customerdb set ip='$ip' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE customerdb set account='$account' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE customerdb set email='$email' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE customerdb set username='$username' where id='$extdisplay'";
	sql($sql);
	$sql="UPDATE customerdb set password='$password' where id='$extdisplay'";
	sql($sql);
}

function customerdb_getsip(){
	$sql="SELECT DISTINCT id from sip order by id";
	$results=sql($sql, "getAll");
	return isset($results)?$results:null;
}

function customerdb_getdid(){
	$sql="SELECT extension from incoming order by extension";
	$results=sql($sql, "getAll");
	return isset($results)?$results:null;
}	
?>
