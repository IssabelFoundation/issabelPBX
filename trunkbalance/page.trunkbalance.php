<?php

isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';

//the item we are currently displaying
isset($_REQUEST['itemid'])?$itemid=$_REQUEST['itemid']:$itemid='';

// don't know what this line does - lg
$dispnum = "trunkbalance"; //used for switch on config.php

$tabindex = 0;



//if submitting form, update database
if(isset($_POST['action'])) {
	switch ($action) {
		case "add":
			trunkbalance_add($_POST);
			needreload();
			redirect_standard();
		break;
		case "delete":
			trunkbalance_del($itemid);
			needreload();
			redirect_standard();
		break;
		case "edit":
			trunkbalance_edit($itemid,$_POST);
			needreload();
			redirect_standard('itemid');
		break;
	}
}

//get list of trunks
$trunkbalances = trunkbalance_list();
?>



<!-- right side menu -->
<div class="rnav"><ul>
    <li><a id="<?php echo ($itemid=='' ? 'current':'') ?>" href="config.php?display=<?php echo urlencode($dispnum)?>"><?php echo _("Add Load balanced Trunk")?></a></li>
<?php
if (isset($trunkbalances)) {
	foreach ($trunkbalances as $trunkbalance) {
		if ($trunkbalance['trunkbalance_id'] != 0)
			echo "<li><a id=\"".($itemid==$trunkbalance['trunkbalance_id'] ? 'current':'')."\" href=\"config.php?display=".urlencode($dispnum)."&itemid=".urlencode($trunkbalance['trunkbalance_id'])."\">{$trunkbalance['description']}</a></li>";
	}
}
?>
</ul></div>

<div class="content">
<?php
if ($action == 'delete') {
	echo '<br><h3>'._("Balanced Trunk").' '.$itemid.' '._("deleted").'!</h3>';
} else {
	if ($itemid){ 
		//get details for this source
		$thisItem = trunkbalance_get($itemid);
	} else {
		$thisItem = Array( 'description' => null, 'desttrunk_id' => null);
	}

	$delURL = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&action=delete';
	$delButton = "
			<form name=delete action=\"{$_SERVER['PHP_SELF']}\" method=POST>
				<input type=\"hidden\" name=\"display\" value=\"{$dispnum}\">
				<input type=\"hidden\" name=\"itemid\" value=\"{$itemid}\">
				<input type=\"hidden\" name=\"action\" value=\"delete\">
				<input type=submit value=\""._("Delete Balanced Trunk: $thisItem[description]")."\">
			</form>";
	

// removed check for updates for 14+ compatibility
// check to see if user has automatic updates enabled
//$cm =& cronmanager::create($db);
//$online_updates = $cm->updates_enabled() ? true : false;

// check if new version of module is available
//if ($online_updates && $foo = trunkbalance_vercheck()) {
//	print "<br>A <b>new version</b> of this module is available from the <a target='_blank' href='http://pbxossa.org'>PBX Open Source Software Alliance</a><br>";
//	}

// get current verson of module
$module_local = trunkbalance_xml2array("modules/trunkbalance/module.xml");
	
?>
	<h2><?php echo ($itemid ? _("Balanced Trunk:")." ". $itemid : _("Add Balanced Trunk")); ?></h2>

	<p style="width: 80%"><?php echo ($itemid ? '' : _("Each Balanced Trunk is an outbound trunk associated with a set of parameters to define the maximum use you want to do with it. For instance you have a provider that gives you 100 minutes long distance calls per month. You can define here that after 100 minutes of local call during the month this trunk will become unavailable and your route will switch to the next trunk in line.")); ?></p>

<?php		if ($itemid){  echo $delButton; 	} ?>

<form autocomplete="off" name="edit" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return edit_onsubmit();">
	<input type="hidden" name="display" value="<?php echo $dispnum?>">
	<input type="hidden" name="action" value="<?php echo ($itemid ? 'edit' : 'add') ?>">
<table>
	<tr><td colspan="2"><h3><?php echo ($itemid ? _("Edit Trunk") : _("Add Trunk")) ?><hr></h3></td></tr>

<?php		if ($itemid){ ?>
		<input type="hidden" name="itemid" value="<?php echo $itemid; ?>">
<?php		}?>

	<tr>
		<td><a href="#" class="info"><?php echo _("<b>Disable</b> Balanced Trunk $itemid:")?><span><?php echo _("If selected, this trunk is disabled and will not allow calls regardless of rules that follow")?></span></a></td>
		<td><input type="checkbox"  name="disabled" <?php echo (($thisItem['disabled'] == "on") ? "checked" : "") ; ?> tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Trunk Description:")?><span><?php echo _("Enter a description for this balanced trunk.")?></span></a></td>
		<td><input type="text" name="description" value="<?php echo (isset($thisItem['description']) ? $thisItem['description'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Trunk Destination:")?><span><?php echo _("Select the destination trunk")?></span></a></td>
		<td><SELECT id="desttrunk_id" name="desttrunk_id" tabindex="<?php echo ++$tabindex;?>"><OPTION VALUE="0">Select...</option>
<?php     
			$trunklist = trunkbalance_listtrunk();
			   foreach ($trunklist as $trunk){
			if ($trunk['trunkid']!=0){
			   echo _("<OPTION VALUE=\"");
				   echo ($trunk['trunkid']);
				  echo _("\"");
			   if ($thisItem['desttrunk_id']==$trunk['trunkid']) echo _("selected=\"selected\"");
			   echo _(">");
				  echo($trunk['name'].' ('.$trunk['tech'].')');
				  echo _("</OPTION>");
			} 
			}?>
		</SELECT></td>
	</tr>
		<tr><td><a href="#" class="info"><?php echo _("Time Group:")?><span><?php echo _("Trunk is only active during the times specified in the selected time group.")?></span></a></td>
		<td><SELECT id="timegroup_id" name="timegroup_id" tabindex="<?php echo ++$tabindex;?>"><OPTION VALUE="-1">none selected</option>
			   <?php     
			$timegrouplist = trunkbalance_listtimegroup();
			   foreach ($timegrouplist as $timegroup){
			if ($timegroup['id']!=0){
			   echo _("<OPTION VALUE=\"");
				   echo ($timegroup['id']);
				  echo _("\"");
			   if ($thisItem['timegroup_id']==$timegroup['id']) echo _("selected=\"selected\"");
			   echo _(">");
				  echo($timegroup['description']);
				  echo _("</OPTION>");
			} 
			}?>
		</SELECT></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Matching Rule:")?><span><?php echo _("Enter the SQL matching pattern that will be applied to the CDR to calculate your rules on this trunk, separate multiple rules by commas. It will be inserted as WHERE dst LIKE 'your pattern'. For instance if you want to match all numbers starting by 0033 or 0044 you will enter 0033%, 0044%.")?></span></a></td>
		<td><input type="textarea" rows="5" cols="25" name="dialpattern" value="<?php echo (isset($thisItem['dialpattern']) ? $thisItem['dialpattern'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Apply all matching rules:")?><span><?php echo _("By default, this module will apply ANY of the multiple matching rules. Select this option if you want to apply ALL rules. This setting has no affect unless multiple rules are specified")?></span></a></td>
		<td><input type="checkbox"  name="dp_andor" <?php echo (($thisItem['dp_andor'] == "on") ? "checked" : "") ; ?> tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>	
	<tr>
		<td><a href="#" class="info"><?php echo _("Not Matching Rule:")?><span><?php echo _("Enter the matching pattern that will be excluded from the CDR matching to calculate your rules on this trunk, separate multiple rules by commas. It will be inserted as WHERE dst NOT LIKE 'your pattern'. For instance if you want to exclude all numbers starting by 0033 or 0044 you will enter 0033%, 0044%.")?></span></a></td>
		<td><input type="textarea" rows="5" cols="25" name="notdialpattern" value="<?php echo (isset($thisItem['notdialpattern']) ? $thisItem['notdialpattern'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Apply all non-matching rules:")?><span><?php echo _("By default, this module will apply ANY of the multiple matching rules. Select this option if you want to apply ALL rules. This setting has no affect unless multiple rules are specified")?></span></a></td>
		<td><input type="checkbox"  name="notdp_andor" <?php echo (($thisItem['notdp_andor'] == "on") ? "checked" : "") ; ?> tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr><td colspan="2"><h5>Billing Cycle Configuration<hr></h5></td></tr>
		<td><a href="#" class="info"><?php echo _("Choose billing cycle:")?><span><?php echo _("Choose the time period that the billing cycle will resest to")?></span></a></td>
		<td><SELECT id="billing_cycle" name="billing_cycle" tabindex="<?php echo ++$tabindex;?>"><OPTION VALUE="-1">none selected</option>
		<OPTION VALUE="floating" <?php if ($thisItem['billing_cycle']=="floating") echo _("selected=\"selected\""); ?>  >Floating</OPTION>
		<OPTION VALUE="day" <?php if ($thisItem['billing_cycle']=="day") echo _("selected=\"selected\""); ?>  >Day</OPTION>
		<OPTION VALUE="week" <?php if ($thisItem['billing_cycle']=="week") echo _("selected=\"selected\""); ?>  >Week</OPTION>
		<OPTION VALUE="month" <?php if ($thisItem['billing_cycle']=="month") echo _("selected=\"selected\""); ?>  >Month</OPTION>
		</SELECT></td>
    </tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Billing Time:")?><span><?php echo _("Enter the time of day to reset the counter. Used for all non floating billing cycles.")?></span></a></td>
		<td><input type="text" name="billingtime" value="<?php echo (isset($thisItem['billingtime']) ? $thisItem['billingtime'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Billing Day:")?><span><?php echo _("Enter the day of the week to reset the counter. Only used for weekly billing cycle.")?></span></a></td>
		
		<td><SELECT id="billing_day" name="billing_day" tabindex="<?php echo ++$tabindex;?>">
			<OPTION VALUE="-1">none selected</option>
			<OPTION VALUE="Monday"  <?php if ($thisItem['billing_day']=="Monday") echo _("selected=\"selected\""); ?> >Monday</OPTION>
			<OPTION VALUE="Tuesday" <?php if ($thisItem['billing_day']=="Tuesday") echo _("selected=\"selected\""); ?>  >Tuesday</OPTION>
			<OPTION VALUE="Wednesday"  <?php if ($thisItem['billing_day']=="Wednesday") echo _("selected=\"selected\""); ?> >Wednesday</OPTION>
			<OPTION VALUE="Thursday"  <?php if ($thisItem['billing_day']=="Thursday") echo _("selected=\"selected\""); ?> >Thursday</OPTION>
			<OPTION VALUE="Friday" <?php if ($thisItem['billing_day']=="Friday") echo _("selected=\"selected\""); ?>  >Friday</OPTION>
			<OPTION VALUE="Saturday"  <?php if ($thisItem['billing_day']=="Saturday") echo _("selected=\"selected\""); ?> >Saturday</OPTION>
			<OPTION VALUE="Sunday" <?php if ($thisItem['billing_day']=="Sunday") echo _("selected=\"selected\""); ?>  >Sunday</OPTION>
		</SELECT></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Billing Date:")?><span><?php echo _("Enter the day of the month to reset the counter. Only used for Monthly billing cycle.")?></span></a></td>
		<td><input type="number" name="billingdate" value="<?php echo (isset($thisItem['billingdate']) ? $thisItem['billingdate'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Floating Billing Time:")?><span><?php echo _("Enter the number of floating hours that should be included in the count. 0 to include all. This is only used for the floating billing cycle.")?></span></a></td>
		<td><input type="number" name="billingperiod" value="<?php echo (isset($thisItem['billingperiod']) ? $thisItem['billingperiod'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Ending Date:")?><span><?php echo _("Enter the date when this balanced trunk should expire. YYYY-MM-DD HH:mm - Keep empty to disable")?></span></a></td>
		<td><input type="text" name="endingdate" value="<?php echo (isset($thisItem['endingdate']) ? $thisItem['endingdate'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr><td colspan="2"><h5>Usage Limits Configuration<hr></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Include Inbound Calls:")?><span><?php echo _("Outbound calls are counted automatically, enable this setting to include inbound calls when determining usage limits.")?></span></a></td>
		<td><input type="checkbox"  name="count_inbound" <?php echo (($thisItem['count_inbound'] == "on") ? "checked" : "") ; ?> tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Include Unanswered Calls:")?><span><?php echo _("Answered calls are counted automatically, enable this setting to include unanswered calls when determining usage limits.")?></span></a></td>
		<td><input type="checkbox" name="count_unanswered" <?php echo (($thisItem['count_unanswered'] == "on") ? "checked" : "") ; ?> tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>	
	<tr>
		<td><a href="#" class="info"><?php echo _("Maximum inbound/outbound Calling Time:")?><span><?php echo _("Enter the maximum total number of calling minutes per billing period. Be aware that the test is performed before the begining of the call and it will not break an active call.")?></span></a></td>
		<td><input type="number" name="maxtime" value="<?php echo (isset($thisItem['maxtime']) ? $thisItem['maxtime'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Maximum Number of inbound/outbound Calls:")?><span><?php echo _("Enter the maximum number of calls per billing period.")?></span></a></td>
		<td><input type="number" name="maxnumber" value="<?php echo (isset($thisItem['maxnumber']) ? $thisItem['maxnumber'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Max. Number of Different outbound Calls:")?><span><?php echo _("Enter the maximum number of different outbound phone numbers allowed per billing period. The include inbound calls and include unanswered calls settings do not apply to this item.")?></span></a></td>
		<td><input type="number" name="maxidentical" value="<?php echo (isset($thisItem['maxidentical']) ? $thisItem['maxidentical'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr><td colspan="2"><h5>Load Balancing Configuration<hr></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Load Ratio:")?><span><?php echo _("Enter the ratio of calls that this trunk should accept. For instance to allow 1/3 of outbound calls to complete, you should enter 3 to let this trunk accept 1 out of 3 calls.")?></span></a></td>
		<td><input type="number" name="loadratio" value="<?php echo (isset($thisItem['loadratio']) ? $thisItem['loadratio'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>

	<tr><td colspan="2"><h5>URL Configuration<hr></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("URL:")?><span><?php echo _("Enter a URL to load, substitute the string \$OUTNUM\$ in place of the outbound dialled digits.")?></span></a></td>
		<td><textarea name="url" tabindex="<?php echo ++$tabindex;?>" style="width:250px;height:150px;"><?php echo (isset($thisItem['url']) ? $thisItem['url'] : ''); ?></textarea></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("URL Timeout:")?><span><?php echo _("Enter max seconds to wait for URL to respond.")?></span></a></td>
		<td><input type="number" name="url_timeout" value="<?php echo (isset($thisItem['url_timeout']) ? $thisItem['url_timeout'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("regex:")?><span><?php echo _("Enter PCRE regex with delimiters to search the URL contents, substitute the string \$OUTNUM\$ in place of the outbound dialled digits. Separate multiple regexs on each line")?></span></a></td>
		<td><textarea name="regex" tabindex="<?php echo ++$tabindex;?>" style="width:250px;height:150px;"><?php echo (isset($thisItem['regex']) ? $thisItem['regex'] : ''); ?></textarea></td>
		</tr>
	<tr>
		<td colspan="2"><br><h6><input name="submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>"></h6></td>		
	</tr>
</table>

<p align="center" style="font-size:11px;"><br>
Trunk Balance module verserion <?php echo $module_local[module][version]?> is maintained by the developer community at the <a target="_blank" href="https://github.com/POSSA/freepbx-trunk-balancing"> PBX Open Source Software Alliance</a><br></p>

<script language="javascript">
<!--


var theForm = document.edit;
theForm.description.focus();

//displaySourceParameters(document.getElementById('sourcetype'), document.getElementById('sourcetype').selectedIndex);

function edit_onsubmit() {
	
	if (isEmpty(theForm.description.value)) return warnInvalid(theForm.description, "Please enter a valid description");
	if (!isAlphanumeric(theForm.description.value)) return warnInvalid(theForm.description, "Please enter a valid description");

	if ((theForm.desttrunk_id.value)=="0") return warnInvalid(theForm.desttrunk_id, "Please select a valid trunk");

	
		
	return true;
}


-->
</script>
</form>


<?php		
} //end if action == delete
?>
