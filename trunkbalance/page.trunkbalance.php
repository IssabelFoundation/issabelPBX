<?php

isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';

//the item we are currently displaying
isset($_REQUEST['extdisplay'])?$extdisplay=$_REQUEST['extdisplay']:$extdisplay='';

// don't know what this line does - lg
$dispnum = "trunkbalance"; //used for switch on config.php

$tabindex = 0;



//if submitting form, update database
if(isset($_POST['action'])) {
	switch ($action) {
		case "add":
			trunkbalance_add($_POST);
			needreload();
            $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been added'));
            $_SESSION['msgtype']='success';
            $_SESSION['msgtstamp']=time();
			redirect_standard();
		break;
		case "delete":
			trunkbalance_del($extdisplay);
			needreload();
            $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been deleted'));
            $_SESSION['msgtype']='warning';
            $_SESSION['msgtstamp']=time();
			redirect_standard();
		break;
		case "edit":
			trunkbalance_edit($extdisplay,$_POST);
			needreload();
            $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
            $_SESSION['msgtype']='success';
            $_SESSION['msgtstamp']=time();
			redirect_standard('extdisplay');
		break;
	}
}

//get list of trunks
$rnaventries = array();
$trunkbalances = trunkbalance_list();
foreach ($trunkbalances as $trunkbalance) {
    if ($trunkbalance['trunkbalance_id'] != 0) {
        $rnaventries[] = array($trunkbalance['trunkbalance_id'],$trunkbalance['description'],'');
    }
}

drawListMenu($rnaventries, $type, $display, $extdisplay);
?>
<div class="content">
<?php


$helptext = __("Each Balanced Trunk is an outbound trunk associated with a set of parameters to define the maximum use you want to do with it. For instance you have a provider that gives you 100 minutes long distance calls per month. You can define here that after 100 minutes of local call during the month this trunk will become unavailable and your route will switch to the next trunk in line.");
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

if ($extdisplay){ 
	//get details for this source
    $thisItem = trunkbalance_get($extdisplay);
} else {
	$thisItem = Array( 'description' => null, 'desttrunk_id' => null, 'disabled' => null, 'dp_andor' => null, 'notdp_andor' => null, 'billing_cycle'=>null, 'billing_day'=>null, 'count_inbound'=>null, 'count_unanswered'=>null);
}

// get current verson of module
$module_local = trunkbalance_xml2array("admin/modules/trunkbalance/module.xml");
	
?>
<div class='is-flex'><h2><?php echo ($extdisplay ? __("Edit Balanced Trunk")." ". $extdisplay : __("Add Balanced Trunk")); ?></h2><?php echo $help;?></div>


<form id="mainform" autocomplete="off" name="edit" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return edit_onsubmit(this);">
	<input type="hidden" name="display" value="<?php echo $dispnum?>">
	<input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add') ?>">
    <table class='table is-borderless is-narrow'>
    <tr><td colspan="2"><h5><?php  echo _dgettext('amp','General Settings') ?></h5></td></tr>

<?php	if ($extdisplay){ ?>
		<input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
<?php	}?>

	<tr>
		<td><a href="#" class="info"><?php echo __("<b>Disable</b> Balanced Trunk")?><span><?php echo __("If selected, this trunk is disabled and will not allow calls regardless of rules that follow")?></span></a></td>
        <td>
<?php $checked = ($thisItem['disabled'] == "on") ? " checked='checked' " : ""; ?>
<div class='field'><input type='checkbox' class='switch' id='disabled' name='disabled' value='on' <?php echo $checked;?> tabindex='<?php echo ++$tabindex;?>'/><label style='height:auto; line-height:2em; padding-left:3em;' for='disabled'>&nbsp;</label></div>

        </td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Trunk Description")?><span><?php echo __("Enter a description for this balanced trunk.")?></span></a></td>
		<td><input type="text" name="description" value="<?php echo (isset($thisItem['description']) ? $thisItem['description'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class="w100 input"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Trunk Destination")?><span><?php echo __("Select the destination trunk")?></span></a></td>
        <td><SELECT id="desttrunk_id" name="desttrunk_id" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
        <OPTION VALUE="0"><?php echo __("Select...")?></option>
<?php     
			$trunklist = trunkbalance_listtrunk();
			   foreach ($trunklist as $trunk){
			if ($trunk['trunkid']!=0){
			   echo __("<OPTION VALUE=\"");
				   echo ($trunk['trunkid']);
				  echo __("\"");
			   if ($thisItem['desttrunk_id']==$trunk['trunkid']) echo __("selected=\"selected\"");
			   echo __(">");
				  echo($trunk['name'].' ('.$trunk['tech'].')');
				  echo __("</OPTION>");
			} 
			}?>
		</SELECT></td>
	</tr>
		<tr><td><a href="#" class="info"><?php echo __("Time Group")?><span><?php echo __("Trunk is only active during the times specified in the selected time group.")?></span></a></td>
        <td><SELECT id="timegroup_id" name="timegroup_id" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
        <OPTION VALUE="-1"><?php echo __('none selected')?></option>
			   <?php     
			$timegrouplist = trunkbalance_listtimegroup();
			   foreach ($timegrouplist as $timegroup){
			if ($timegroup['id']!=0){
			   echo __("<OPTION VALUE=\"");
				   echo ($timegroup['id']);
				  echo __("\"");
			   if ($thisItem['timegroup_id']==$timegroup['id']) echo __("selected=\"selected\"");
			   echo __(">");
				  echo($timegroup['description']);
				  echo __("</OPTION>");
			} 
			}?>
		</SELECT></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Matching Rule")?><span><?php echo __("Enter the SQL matching pattern that will be applied to the CDR to calculate your rules on this trunk, separate multiple rules by commas. It will be inserted as WHERE dst LIKE 'your pattern'. For instance if you want to match all numbers starting by 0033 or 0044 you will enter 0033%, 0044%.")?></span></a></td>
		<td><input type="textarea" rows="5" cols="25" style="width:100%; height:4em;" name="dialpattern" value="<?php echo (isset($thisItem['dialpattern']) ? $thisItem['dialpattern'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Apply all matching rules")?><span><?php echo __("By default, this module will apply ANY of the multiple matching rules. Select this option if you want to apply ALL rules. This setting has no affect unless multiple rules are specified")?></span></a></td>
        <td>
<!--input type="checkbox"  name="dp_andor" <?php echo (($thisItem['dp_andor'] == "on") ? "checked" : "") ; ?> tabindex="<?php echo ++$tabindex;?>"-->

<?php $checked = ($thisItem['dp_andor'] == "on") ? " checked='checked' " : ""; ?>
<div class='field'><input type='checkbox' class='switch' id='dp_andor' name='dp_andor' value='on' <?php echo $checked;?> tabindex='<?php echo ++$tabindex;?>'/><label style='height:auto; line-height:2em; padding-left:3em;' for='dp_andor'>&nbsp;</label></div>
</td>

	</tr>	
	<tr>
		<td><a href="#" class="info"><?php echo __("Not Matching Rule")?><span><?php echo __("Enter the matching pattern that will be excluded from the CDR matching to calculate your rules on this trunk, separate multiple rules by commas. It will be inserted as WHERE dst NOT LIKE 'your pattern'. For instance if you want to exclude all numbers starting by 0033 or 0044 you will enter 0033%, 0044%.")?></span></a></td>
		<td><input type="textarea" rows="5" cols="25" style="width:100%; height:4em;" name="notdialpattern" value="<?php echo (isset($thisItem['notdialpattern']) ? $thisItem['notdialpattern'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Apply all non-matching rules")?><span><?php echo __("By default, this module will apply ANY of the multiple matching rules. Select this option if you want to apply ALL rules. This setting has no affect unless multiple rules are specified")?></span></a></td>
        <td>
<!--input type="checkbox"  name="notdp_andor" <?php echo (($thisItem['notdp_andor'] == "on") ? "checked" : "") ; ?> tabindex="<?php echo ++$tabindex;?>"-->
<?php $checked = ($thisItem['notdp_andor'] == "on") ? " checked='checked' " : ""; ?>
<div class='field'><input type='checkbox' class='switch' id='notdp_andor' name='notdp_andor' value='on' <?php echo $checked;?> tabindex='<?php echo ++$tabindex;?>'/><label style='height:auto; line-height:2em; padding-left:3em;' for='notdp_andor'>&nbsp;</label></div>
</td>
	</tr>
	<tr><td colspan="2"><h5><?php echo __("Billing Cycle Configuration");?></h5></td></tr>
		<td><a href="#" class="info"><?php echo __("Choose billing cycle")?><span><?php echo __("Choose the time period that the billing cycle will resest to")?></span></a></td>
        <td><SELECT id="billing_cycle" name="billing_cycle" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
        <OPTION VALUE="-1"><?php echo __('none selected')?></option>
		<OPTION VALUE="floating" <?php if ($thisItem['billing_cycle']=="floating") echo __("selected=\"selected\""); ?>  ><?php echo __('Floating');?></OPTION>
		<OPTION VALUE="day" <?php if ($thisItem['billing_cycle']=="day") echo __("selected=\"selected\""); ?>  ><?php echo __('Day');?></OPTION>
		<OPTION VALUE="week" <?php if ($thisItem['billing_cycle']=="week") echo __("selected=\"selected\""); ?>  ><?php echo __('Week');?></OPTION>
		<OPTION VALUE="month" <?php if ($thisItem['billing_cycle']=="month") echo __("selected=\"selected\""); ?>  ><?php echo __('Month');?></OPTION>
		</SELECT></td>
    </tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Billing Time")?><span><?php echo __("Enter the time of day to reset the counter. Used for all non floating billing cycles.")?></span></a></td>
		<td><input type="text" name="billingtime" value="<?php echo (isset($thisItem['billingtime']) ? $thisItem['billingtime'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class="w100 input"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Billing Day")?><span><?php echo __("Enter the day of the week to reset the counter. Only used for weekly billing cycle.")?></span></a></td>
		
        <td><SELECT id="billing_day" name="billing_day" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
            <OPTION VALUE="-1"><?php echo __('none selected')?></option>
			<OPTION VALUE="Monday"  <?php if ($thisItem['billing_day']=="Monday") echo __("selected=\"selected\""); ?> ><?php echo __('Monday');?></OPTION>
			<OPTION VALUE="Tuesday" <?php if ($thisItem['billing_day']=="Tuesday") echo __("selected=\"selected\""); ?>  ><?php echo __('Tuesday');?></OPTION>
			<OPTION VALUE="Wednesday"  <?php if ($thisItem['billing_day']=="Wednesday") echo __("selected=\"selected\""); ?> ><?php echo __('Wednesday');?></OPTION>
			<OPTION VALUE="Thursday"  <?php if ($thisItem['billing_day']=="Thursday") echo __("selected=\"selected\""); ?> ><?php echo __('Thursday');?></OPTION>
			<OPTION VALUE="Friday" <?php if ($thisItem['billing_day']=="Friday") echo __("selected=\"selected\""); ?>  ><?php echo __('Friday');?></OPTION>
			<OPTION VALUE="Saturday"  <?php if ($thisItem['billing_day']=="Saturday") echo __("selected=\"selected\""); ?> ><?php echo __('Saturday');?></OPTION>
			<OPTION VALUE="Sunday" <?php if ($thisItem['billing_day']=="Sunday") echo __("selected=\"selected\""); ?>  ><?php echo __('Sunday');?></OPTION>
		</SELECT></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Billing Date")?><span><?php echo __("Enter the day of the month to reset the counter. Only used for Monthly billing cycle.")?></span></a></td>
		<td><input type="number" name="billingdate" value="<?php echo (isset($thisItem['billingdate']) ? $thisItem['billingdate'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class="w100 input"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Floating Billing Time")?><span><?php echo __("Enter the number of floating hours that should be included in the count. 0 to include all. This is only used for the floating billing cycle.")?></span></a></td>
		<td><input type="number" name="billingperiod" value="<?php echo (isset($thisItem['billingperiod']) ? $thisItem['billingperiod'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class="w100 input"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Ending Date")?><span><?php echo __("Enter the date when this balanced trunk should expire. YYYY-MM-DD HH:mm - Keep empty to disable")?></span></a></td>
		<td><input type="text" name="endingdate" value="<?php echo (isset($thisItem['endingdate']) ? $thisItem['endingdate'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class="w100 input"></td>
	</tr>
	<tr><td colspan="2"><h5><?php echo __("Usage Limits Configuration");?></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Include Inbound Calls")?><span><?php echo __("Outbound calls are counted automatically, enable this setting to include inbound calls when determining usage limits.")?></span></a></td>
		<td><input type="checkbox"  name="count_inbound" <?php echo (($thisItem['count_inbound'] == "on") ? "checked" : "") ; ?> tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Include Unanswered Calls")?><span><?php echo __("Answered calls are counted automatically, enable this setting to include unanswered calls when determining usage limits.")?></span></a></td>
		<td><input type="checkbox" name="count_unanswered" <?php echo (($thisItem['count_unanswered'] == "on") ? "checked" : "") ; ?> tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>	
	<tr>
		<td><a href="#" class="info"><?php echo __("Maximum inbound/outbound Calling Time")?><span><?php echo __("Enter the maximum total number of calling minutes per billing period. Be aware that the test is performed before the begining of the call and it will not break an active call.")?></span></a></td>
		<td><input type="number" name="maxtime" value="<?php echo (isset($thisItem['maxtime']) ? $thisItem['maxtime'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class="w100 input"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Maximum Number of inbound/outbound Calls")?><span><?php echo __("Enter the maximum number of calls per billing period.")?></span></a></td>
		<td><input type="number" name="maxnumber" value="<?php echo (isset($thisItem['maxnumber']) ? $thisItem['maxnumber'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class="w100 input"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Max. Number of Different outbound Calls")?><span><?php echo __("Enter the maximum number of different outbound phone numbers allowed per billing period. The include inbound calls and include unanswered calls settings do not apply to this item.")?></span></a></td>
		<td><input type="number" name="maxidentical" value="<?php echo (isset($thisItem['maxidentical']) ? $thisItem['maxidentical'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class="w100 input"></td>
	</tr>
	<tr><td colspan="2"><h5><?php echo __("Load Balancing Configuration"); ?></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("Load Ratio")?><span><?php echo __("Enter the ratio of calls that this trunk should accept. For instance to allow 1/3 of outbound calls to complete, you should enter 3 to let this trunk accept 1 out of 3 calls.")?></span></a></td>
		<td><input type="number" name="loadratio" value="<?php echo (isset($thisItem['loadratio']) ? $thisItem['loadratio'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class="w100 input"></td>
	</tr>

	<tr><td colspan="2"><h5><?php echo __("URL Configuration");?></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("URL")?><span><?php echo __("Enter a URL to load, substitute the string \$OUTNUM\$ in place of the outbound dialled digits.")?></span></a></td>
		<td><textarea class='textarea' name="url" tabindex="<?php echo ++$tabindex;?>"><?php echo (isset($thisItem['url']) ? $thisItem['url'] : ''); ?></textarea></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("URL Timeout")?><span><?php echo __("Enter max seconds to wait for URL to respond.")?></span></a></td>
		<td><input type="number" name="url_timeout" value="<?php echo (isset($thisItem['url_timeout']) ? $thisItem['url_timeout'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>" class="w100 input"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo __("regex")?><span><?php echo __("Enter PCRE regex with delimiters to search the URL contents, substitute the string \$OUTNUM\$ in place of the outbound dialled digits. Separate multiple regexs on each line")?></span></a></td>
		<td><textarea class="textarea" name="regex" tabindex="<?php echo ++$tabindex;?>"><?php echo (isset($thisItem['regex']) ? $thisItem['regex'] : ''); ?></textarea></td>
		</tr>
</table>

<p align="center" style="font-size:11px;"><br>
Trunk Balance module version <?php echo $module_local['module']['version']?> is maintained by the developer community at the <a target="_blank" href="https://github.com/POSSA/freepbx-trunk-balancing"> PBX Open Source Software Alliance</a><br></p>

</form>
<script>


function edit_onsubmit(theForm) {
	if (isEmpty(theForm.description.value)) return warnInvalid(theForm.description, "<?php echo __("Please enter a valid Description") ?>");
	if (!isAlphanumeric(theForm.description.value)) return warnInvalid(theForm.description, "<?php echo __("Please enter a valid Description") ?>");
    if ((theForm.desttrunk_id.value)=="0") return warnInvalid($('#desttrunk_id')[0], "<?php echo __("Please select a valid trunk")?>");
    $.LoadingOverlay('show');
	return true;
}


<?php echo js_display_confirmation_toasts(); ?>
</script>
</div>
<?php echo form_action_bar($extdisplay); ?>
