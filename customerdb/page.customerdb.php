<?php
//CustomerDB 1.00 written by Keith Dowell 2006-04-07
//Copyright (C) 2006 Keith Dowell (snowolfex@yahoo.com)
//
//This program is free software; you can redistribute it and/or
//modify it under the terms of the GNU General Public License
//as published by the Free Software Foundation; either version 2
//of the License, or (at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.

//Set all the vars so there arent a ton of errors in the httpd error_log

$display = isset($_REQUEST['display'])?$_REQUEST['display']:'customerdb';
$type = isset($_REQUEST['type'])?$_REQUEST['type']:'tool';

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$name = isset($_REQUEST['name'])?$_REQUEST['name']:'';
$addr1 = isset($_REQUEST['addr1'])?$_REQUEST['addr1']:'';
$addr2 = isset($_REQUEST['addr2'])?$_REQUEST['addr2']:'';
$city = isset($_REQUEST['city'])?$_REQUEST['city']:'';
$state = isset($_REQUEST['state'])?$_REQUEST['state']:'LA';
$zip = isset($_REQUEST['zip'])?$_REQUEST['zip']:'';
$sip = isset($_REQUEST['sip'])?$_REQUEST['sip']:'';
$did = isset($_REQUEST['did'])?$_REQUEST['did']:'';
$device = isset($_REQUEST['device'])?$_REQUEST['device']:'';
$ip = isset($_REQUEST['ip'])?$_REQUEST['ip']:'';
$serial = isset($_REQUEST['serial'])?$_REQUEST['serial']:'';
$account = isset($_REQUEST['account'])?$_REQUEST['account']:'';
$email = isset($_REQUEST['email'])?$_REQUEST['email']:'';
$username = isset($_REQUEST['username'])?$_REQUEST['username']:'';
$password = isset($_REQUEST['password'])?$_REQUEST['password']:'';

extract($_REQUEST);

$dispnum='customerdb';

if(!isset($action))
	$action='';
switch($action) {
	case "add":
		customerdb_add($name, $addr1, $addr2, $city, $state, $zip, $sip, $did, $device, $ip, $serial, $account, $email, $username, $password);
		$name='';
		$addr1='';
		$addr2='';
		$city='';
		$state='';
		$zip='';
		$sip='';
		$did='';
		$ip='';
		$serial='';
		$account='';
		$email='';
		$device='';
		$username='';
		$password='';
		//needreload(); 
		//right now... not writing config files... don't need to reload
		redirect_standard();
	break;
	case "del":
		customerdb_del($extdisplay);
		//needreload();
		redirect_standard();
	break;
	case "edit":
		customerdb_edit($extdisplay, $name, $addr1, $addr2, $city, $state, $zip, $sip, $did, $device, $ip, $serial, $account, $email, $username, $password);
		//needreload();
		redirect_standard('extdisplay');
	break;
	
}
?>
</div>
<div class="rnav">
<?php
$customers=customerdb_list();
drawListMenu($customers, $skip, $type, $dispnum, $extdisplay, _("Customer"));
?>
</div>


<div class="content">
<?php
if($action=='del'){
	echo "<br><h3>ID ".$extdisplay." "._("deleted")."!</h3><br><Br><br><br><br><br><br>";
}
else if(!isset($extdisplay)) {

		
	echo "<h2>"._("Add a user")."</h2>";
//	echo "<li><a href=\"".$_SERVER['PHP_SELF']."?$action=add\";>Add</a><br>";

}
else {
	$delURL = $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."&action=del&extdisplay=$extdisplay";

	//If we have some data, load it up... this means we are editing.
	if($extdisplay!=""){
		$customerInfo=customerdb_get($extdisplay);
		$name=$customerInfo['name'];
		$addr1=$customerInfo['addr1'];
		$addr2=$customerInfo['addr2'];
		$city=$customerInfo['city'];
		$state=$customerInfo['state'];
		$zip=$customerInfo['zip'];
		$sip=$customerInfo['sip'];
		$did=$customerInfo['did'];
		$device=$customerInfo['device'];
		$serial=$customerInfo['serial'];
		$ip=$customerInfo['ip'];
		$account=$customerInfo['account'];
		$email=$customerInfo['email'];
		$username=$customerInfo['username'];
		$password=$customerInfo['password'];
	}
		
	
	if(isset($customerInfo) && is_array($customerInfo)){
		$action="edit";
		echo "<h2> ".$extdisplay." ".$name."</h2>";
		echo "<p><a href=\"".$delURL."\">"._("Delete Customer")."</a></p>";
	}
	else {
		echo "<h2>"._("Add Customer")."</h2>";
	}

}

echo "<form name=\"addNew\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" onsubmit=\"return addNew_onsubmit();\">";
echo "<input type=hidden name=type value='tool'>\n";
echo "<input type=hidden name=extdisplay value=$extdisplay>\n";
echo "<input type=hidden name=action value=\"";
echo ($action=="" ? "add" : $action);
echo "\">\n";
echo "<input type=hidden name=display value=\"customerdb\">";

echo "<table>";
	
echo "<tr><td colspan=2><h5>";
echo ($extdisplay ? _('Edit Customer') : _('Add Customer'));
echo "</h5></td></tr>\n";

//Name
echo "<tr ";
echo ($extdisplay ? '' : '');
echo "><td>";
echo "<a href=\"#\" class=\"info\" >"._("Name")."\n";
echo "<span>"._("Name of business or person (REQUIRED)")."</span></a>\n";
echo "</td>";
echo "<td>";
echo "<input type=text name=\"name\" value=\"$name\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//Address Line 1
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">"._("Address 1")."\n";
echo "<span>"._("Address Line 1 (REQUIRED)")."</span></a>\n";
echo "</td><td>\n";
echo "<input type=text tabindex=".++$tabindex." name=\"addr1\" value=\"$addr1\"\n";
echo "</td></tr>\n";

//Address Line 2
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">"._("Address 2")."\n";
echo "<span>"._("Address Line 2")."</span></a>\n";
echo "</td><td>\n";
echo "<input type=text tabindex=".++$tabindex." name=\"addr2\" value=\"$addr2\">\n";
echo "</td><tr>\n";

//City
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">"._("City")."\n";
echo "<span>"._("City (REQUIRED)")."</span></a>\n";
echo "</td><td>\n";
echo "<input type=text name=\"city\" value=\"$city\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//State
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">"._("State")."\n";
echo "<span>"._("State (REQUIRED)")."</span></a>\n";
echo "</td><td>\n";
$state=($extdisplay ? $state : "N/A");
$states = array('N/A','AL', 'AK', 'AR', 'AZ', 'CA', 'CO', 'CT', 'DC', 'DE', 'FL', 'GA', 'HI', 'IA', 'ID', 'IL', 'IN', 'KS', 'KY', 'LA', 'MA','MD', 'ME', 'MI', 'MN', 'MO', 'MS', 'MT', 'NC', 'ND', 'NE', 'NH', 'NJ', 'NM', 'NV', 'NY', 'OH', 'OK', 'OR', 'PA', 'PR', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VA', 'VT', 'WA', 'WV', 'WI', 'WY', 'TAS', 'VIC', 'NSW', 'ACT', 'QLD', 'NT', 'SA'); 
echo "&nbsp;&nbsp;<select name=\"state\" tabindex=".++$tabindex.">\n";
foreach ($states as $s){
	echo "<option value=\"$s\"";
	if($state==$s) echo " SELECTED";
	echo ">$s</option>\n";
}
echo "</select>\n";
echo "</td></tr>\n";

//Zip
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">"._("Zip/Post Code")."\n";
echo "<span>"._("Zip (REQUIRED)")."</span></a>\n";
echo "</td><td>\n";
echo "<input type=text name=\"zip\" value=\"$zip\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//Sip
echo "<tr><td align=left>\n";
echo "<input type=radio checked name=\"sipbtn\" onclick=\"switchit_sip();return true;\" tabindex=".++$tabindex."><a 
href=\"#\" class=\"info\">"._("Sip Account")."\n";
echo "<span>"._("Sip Account (must have this or a DID tied to the account)")."</span></a>\n";
echo "</td><td>\n";
$sips=customerdb_getsip();
echo "&nbsp;&nbsp;<select name=\"sip\" onchange=\"switchit_sip(); return true;\">\n";
echo "<option value=\"\">";
foreach ($sips as $sipid){
	echo "<option value=\"$sipid[0]\"";
	if($sip==$sipid[0]) echo " SELECTED";
	echo ">$sipid[0]</option>\n";
}
echo "</select>\n";
echo "</td></tr>\n";

//Did
echo "<tr><td>\n";
echo "<input type=radio name=\"didbtn\" onclick=\"switchit_did();return true;\"><a href=\"#\" class=\"info\">"._("DID Number")."\n";
echo "<span>"._("DID Number (must have this or sip tied to the account)")."</span></a>\n";
echo "</td><td>\n";
$dids=customerdb_getdid();
echo "&nbsp;&nbsp;<select name=\"did\" onchange=\"switchit_did(); return true;\">\n";
echo "<option value=\"\">";
foreach ($dids as $didnum){
	echo "<option value=\"$didnum[0]\"";
	if($did==$didnum[0]) echo " SELECTED";
	echo ">$didnum[0]</option>\n";
}
echo "</select>\n";
echo "</td></tr>\n";

//Device
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">"._("Device")."\n";
echo "<span>"._("Device (example... Linksys PAP-2, Sipura)")."</span></a>\n";
echo "</td><td>\n";
echo "<input type=text name=\"device\" value=\"$device\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//Serial
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">"._("Serial")."\n";
echo "<span>"._("Serial Number")."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"serial\" value=\"$serial\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//IP
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">"._("IP Address")."\n";
echo "<span>"._("IP Address")."</span></a>\n";
echo "</td><td>\n";
echo "<input type=text name=\"ip\" value=\"$ip\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//Account
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">"._("Account")."\n";
echo "<span>"._("Account Number (internal use)")."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"account\" value=\"$account\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//Email
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">"._("Email")."\n";
echo "<span>"._("Email Address")."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"email\" value=\"$email\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//Username
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">"._("Username")."\n";
echo "<span>"._("Username for the device")."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"username\" value=\"$username\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//Password
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">"._("Password")."\n";
echo "<span>"._("Password for device")."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"password\" value=\"$password\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

?>
<tr><td></td><td><input type=submit Value="Submit Changes" tabindex="<?php echo ++$tabindex;?>"></td></tr></table>

<script language="javascript">
var cform = document.addNew;
if(cform.name.value == ""){
	cform.name.focus();
}

if(cform.did.selectedIndex>0){
	cform.sipbtn.checked=false;
	cform.didbtn.checked=true;
}
else{
	if(cform.sip.selectedIndex>0){
		cform.sipbtn.checked=true;
		cform.didbtn.checked=false;
	}
	else{
		cform.sipbtn.checked=false;
		cform.didbtn.selected=false;
	}
}


function addNew_onsubmit() {

	var msgInvalidName = "<?php echo _("Please enter a name for this customer");?>";
	var msgInvalidAddr1 = "<?php echo  _("Please enter an address for this customer");?>";
	var msgInvalidCity = "<?php echo _("Pleast enter a city for this customer");?>";
	var msgInvalidZip = "<?php echo _("Please enter a zip for this customer");?>";
	var msgInvalidSipDid = "<?php echo _("You must choose either a sip or did number for this customer.");?>";

	if(isEmpty(cform.name.value)){
		return warnInvalid(cform.name, msgInvalidName);
	}
	if(isEmpty(cform.addr1.value)){
		return warnInvalid(cform.addr1, msgInvalidAddr1);
	}
	if(isEmpty(cform.city.value)){
		return warnInvalid(cform.city, msgInvalidCity);
	}
	if(isEmpty(cform.zip.value)){
		return warnInvalid(cform.zip, msgInvalidZip);
	}
	if(cform.sip.selectedIndex==0 && cform.did.selectedIndex==0){
		return warnInvalid(cform.sipbtn, msgInvalidSipDid);
	}
}

function switchit_sip() {
	cform.sipbtn.checked=true;
	cform.didbtn.checked=false;
	cform.did[0].selected=true;
}

function switchit_did() {
	cform.sipbtn.checked=false;
	cform.didbtn.checked=true;
	cform.sip[0].selected=true;
}

</script>



</form>
