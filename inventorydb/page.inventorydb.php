<?php
//inventoryDB 1.0.0 written by Richard Neese 2006-05-24
//Copyright (C) 2006 Richard Neese (r.neese@gmail.com)
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

$display = isset($_REQUEST['display'])?$_REQUEST['display']:'inventorydb';
$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:'';
$type = isset($_REQUEST['type'])?$_REQUEST['type']:'tool';
$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$empnum = isset($_REQUEST['empnum'])?$_REQUEST['empnum']:'';
$empname = isset($_REQUEST['empname'])?$_REQUEST['empname']:'';
$building = isset($_REQUEST['building'])?$_REQUEST['building']:'';
$floor = isset($_REQUEST['floor'])?$_REQUEST['floor']:'';
$room = isset($_REQUEST['room'])?$_REQUEST['room']:'';
$section = isset($_REQUEST['section'])?$_REQUEST['section']:'';
$cubicle = isset($_REQUEST['cubicle'])?$_REQUEST['cubicle']:'';
$desk = isset($_REQUEST['desk'])?$_REQUEST['desk']:'';
$exten = isset($_REQUEST['exten'])?$_REQUEST['exten']:'';
$phusername = isset($_REQUEST['phusername'])?$_REQUEST['phusername']:'';
$phpassword = isset($_REQUEST['phpassword'])?$_REQUEST['phpassword']:'';
$mac = isset($_REQUEST['mac'])?$_REQUEST['mac']:'';
$serial = isset($_REQUEST['serial'])?$_REQUEST['serial']:'';
$device = isset($_REQUEST['device'])?$_REQUEST['device']:'';
$distdate = isset($_REQUEST['distdate'])?$_REQUEST['distdate']:'';
$ip = isset($_REQUEST['ip'])?$_REQUEST['ip']:'';
$pbxbox = isset($_REQUEST['pbxbox'])?$_REQUEST['pbxbox']:'';
$extrainfo = isset($_REQUEST['extrainfo'])?$_REQUEST['extrainfo']:'';

extract($_REQUEST);

$dispnum='inventorydb';

if(!isset($action))
	$action='';
switch($action) {
	case "add":
		inventorydb_add($empnum, $empname, $building, $floor, $room, $section, $cubicle, $desk, $exten, $phusername, $phpassword, $mac, $serial, $device, $distdate, $ip, $pbxbox, $extrainfo);
		$empnum='';
		$empname='';
		$building='';
		$floor='';
		$room='';
		$section='';
		$cubicle='';
		$desk='';
		$exten='';
		$phusername='';
		$phpassword='';
		$mac='';
		$serial='';
		$device='';
		$distdate='';
		$ip='';
		$pbxbox='';
		$extrainfo='';
		//needreload();
		//right now... not writing config files... don't need to reload
		redirect_standard();
	break;
	case "del":
		inventorydb_del($extdisplay);
		//needreload();
		redirect_standard();
	break;
	case "edit":
		inventorydb_edit($extdisplay, $empnum, $empname, $building, $floor, $room, $section, $cubicle, $desk, $exten, $phusername, $phpassword, $mac, $serial, $device, $distdate, $ip, $pbxbox, $extrainfo);
		//needreload();
		redirect_standard('extdisplay');
	break;

}
?>
</div>
<div class="rnav">
<?php
// Dirty fix for localization
_("Add");
$inventorys=inventorydb_list();
drawListMenu($inventorys, $skip, $type, $dispnum, $extdisplay, _("inventory"));
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
		$inventoryInfo=inventorydb_get($extdisplay);
		$empnum=$inventoryInfo['empnum'];
		$empname=$inventoryInfo['empname'];
		$building=$inventoryInfo['building'];
		$floor=$inventoryInfo['floor'];
		$room=$inventoryInfo['room'];
		$section=$inventoryInfo['section'];
		$cubicle=$inventoryInfo['cubicle'];
		$desk=$inventoryInfo['desk'];
		$exten=$inventoryInfo['exten'];
		$phusername=$inventoryInfo['phusername'];
		$phpassword=$inventoryInfo['phpassword'];
		$mac=$inventoryInfo['mac'];
		$serial=$inventoryInfo['serial'];
		$device=$inventoryInfo['device'];
		$distdate=$inventoryInfo['distdate'];		
		$ip=$inventoryInfo['ip'];
		$pbxbox=$inventoryInfo['pbxbox'];
		$extrainfo=$inventoryInfo['extrainfo'];
	}

	if(isset($inventoryInfo) && is_array($inventoryInfo)){
		$action="edit";
		echo "<h2> ".$extdisplay." ".$empname."</h2>";
		echo "<p><a href=\"".$delURL."\">"._("Delete inventory")."</a></p>";
	}
	else {
		echo "<h2>"._("Add inventory")."</h2>";
	}

}

echo "<form name=\"addNew\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" onsubmit=\"return addNew_onsubmit();\">";
echo "<input type=hidden name=extdisplay value=$extdisplay>\n";
echo "<input type=hidden name=action value=\"";
echo ($action=="" ? "add" : $action);
echo "\">\n";
echo "<input type=hidden name=display value=\"inventorydb\">";
echo "<input type=hidden name=type value=\"tool\">";

echo "<table>";

echo "<tr><td colspan=2><h5>";
echo ($extdisplay ? _('Edit inventory') : _('Add inventory'));
echo "</h5></td></tr>\n";

//empnum
$employee = _("Employee #");
$employeehelp = _("Employee Number");
echo "<tr ";
echo ($extdisplay ? '' : '');
echo "><td>";
echo "<a href=\"#\" class=\"info\">".$employee."\n";
echo "<span>".$employeehelp."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"empnum\" value=\"$empnum\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//empname
$employeename = _("Employee Name");
$employeenamehelp = _("The Name for the Employee");
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">".$employeename."\n";
echo "<span>".$employeenamehelp."</span></a>\n";
echo "</td>";
echo "<td>";
echo "<input type=text name=\"empname\" value=\"$empname\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//building
$buildingloc = _("Building Located");
$buildinglochelp = _("Building where the phone is located");
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">".$buildingloc."\n";
echo "<span>".$buildinglochelp."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"building\" value=\"$building\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//floor
$floorno = _("Floor #");
$floornohelp = _("Floor # phone is on"); 
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">".$floorno."\n";
echo "<span>".$floornohelp."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"floor\" value=\"$floor\" tabindex=".++$tabindex.">\n";
echo "</td><tr>\n";

//room
$roomno = _("Room #");
$roomnohelp = _("Room phone is in");
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">".$roomno."\n";
echo "<span>".$roomnohelp."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"room\" value=\"$room\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//section
$sectionno = _("Floor Section #");
$sectionnohelp = _("Floor Section # the phone is in");
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">".$sectionno."\n";
echo "<span>".$sectionnohelp."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"section\" value=\"$section\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//cubicle
$cubicleno = _("Cubicle #");
$cubiclenohelp = _("Cubicle phone is in");
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">".$cubicleno."\n";
echo "<span>".$cubiclenohelp."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"cubicle\" value=\"$cubicle\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//desk
$deskno = _("Desk #");
$desknohelp = _("Desk Number phone is on");
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">".$deskno."\n";
echo "<span>".$desknohelp."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"desk\" value=\"$desk\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//exten
$extenno = _("Extension #");
$extennohelp = _("Exten Assigned to the phone");
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">".$extenno."\n";
echo "<span>".$extennohelp."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"exten\" value=\"$exten\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//phusername
$phuser = _("Phone UserName");
$phuserhelp = _("Phone Admin Username");
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">".$phuser."\n";
echo "<span>".$phuserhelp."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"phusername\" value=\"$phusername\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//phpassword
$phpass = _("Phone Password");
$phpasshelp = _(" Phone Admin Password");
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">".$phpass."\n";
echo "<span>".$phpasshelp."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"phpassword\" value=\"$phpassword\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//mac
$macaddr = _("MAC Address");
$macaddrhelp = _("MAC Address of the phone");
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">".$macaddr."\n";
echo "<span>".$macaddrhelp."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"mac\" value=\"$mac\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//Serial
$serialno = _("Serial #");
$serialnohelp = _("Serial Number of the phone");
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">".$serialno."\n";
echo "<span>".$serialnohelp."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"serial\" value=\"$serial\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//Device
$phdevice = _("Phone/Device");
$phdevicehelp = _("Device, example... Linksys PAP-2, Sipura");
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">".$phdevice."\n";
echo "<span>".$phdevicehelp."</span></a>\n";
echo "</td><td>\n";
echo "<input type=text name=\"device\" value=\"$device\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//DistDate
$dateinst = _("Distributed Date");
$dateinsthelp = _("Distribution Date");
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">".$dateinst."\n";
echo "<span>".$dateinsthelp."</font></span></a>\n";
echo "</td><td>\n";
echo "<input name=\"distdate\" value=\"$distdate\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//IP
$ipaddr = _("IP Address");
$ipaddrhelp = _("IP Address Assigned If not DHCP");
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">".$ipaddr."\n";
echo "<span>".$ipaddrhelp."</span></a>\n";
echo "</td><td>\n";
echo "<input name=\"ip\" value=\"$ip\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//pbxbox
$pbxname = _("PBX Box Name");
$pbxnamehelp = _("PBX Box Name");
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">".$pbxname."\n";
echo "<span>".$pbxnamehelp."</span></a>\n";
echo "</td><td>\n";
echo "<input type=text name=\"pbxbox\" value=\"$pbxbox\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";

//extrainfo
$extra = _("Extra Info");
$extrahelp = _("Extra Information");
echo "<tr><td>\n";
echo "<a href=\"#\" class=\"info\">".$extra."\n";
echo "<span>".$extrahelp."</span></span></a>\n";
echo "</td><td>\n";
echo "<input name=\"extrainfo\" value=\"$extrainfo\" tabindex=".++$tabindex.">\n";
echo "</td></tr>\n";


?>
<tr><td></td><td><input type=submit Value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>"></td></tr></table>

</script>



</form>
