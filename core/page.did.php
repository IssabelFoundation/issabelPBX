<?php /* $Id: page.did.php 1139 2006-03-14 18:22:46Z mheydon1973 $ */
//Copyright (C) 2004 Coalescent Systems Inc. (info@coalescentsystems.ca)

if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$extdisplay= htmlspecialchars(isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:'');
$old_extdisplay = $extdisplay;
$dispnum = 'did'; //used for switch on config.php
$account = isset($_REQUEST['account'])?$_REQUEST['account']:'';
$goto = isset($_REQUEST['goto0'])?$_REQUEST['goto0']:'';
$ringing = isset($_REQUEST['ringing'])?$_REQUEST['ringing']:'';
$description = htmlspecialchars(isset($_REQUEST['description'])?$_REQUEST['description']:'');
$privacyman = isset($_REQUEST['privacyman'])?$_REQUEST['privacyman']:'0';
$pmmaxretries = isset($_REQUEST['pmmaxretries'])?$_REQUEST['pmmaxretries']:'';
$pmminlength = isset($_REQUEST['pmminlength'])?$_REQUEST['pmminlength']:'';
$alertinfo = htmlspecialchars(isset($_REQUEST['alertinfo'])?$_REQUEST['alertinfo']:'');
$mohclass = isset($_REQUEST['mohclass'])?$_REQUEST['mohclass']:'default';
$grppre = isset($_REQUEST['grppre'])?$_REQUEST['grppre']:'';
$delay_answer = isset($_REQUEST['delay_answer'])&&$_REQUEST['delay_answer']?$_REQUEST['delay_answer']:'';
$pricid = isset($_REQUEST['pricid'])?$_REQUEST['pricid']:'';
$rnavsort = isset($_REQUEST['rnavsort'])?$_REQUEST['rnavsort']:'description';
$didfilter = isset($_REQUEST['didfilter'])?$_REQUEST['didfilter']:'';

$tabindex = 0;

if ($_REQUEST['submitclear']==1 && isset($_REQUEST['goto0'])) {
	$_REQUEST[$_REQUEST['goto0']] = '';
}

if (isset($_REQUEST['extension']) && isset($_REQUEST['cidnum'])) {
	$extdisplay = $_REQUEST['extension']."/".$_REQUEST['cidnum'];
}


//update db if submiting form
switch ($action) {
	case 'addIncoming':
		//create variables from request
		extract($_REQUEST);
		//add details to the 'incoming' table
		if (core_did_add($_REQUEST)) {
			needreload();
            $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been added'));
            $_SESSION['msgtype']='success';
            $_SESSION['msgtstamp']=time();
			redirect_standard('extdisplay', 'extension', 'cidnum', 'didfilter', 'rnavsort');
		}
	break;
	case 'delete':
		$extarray=explode('/',$extdisplay,2);
		core_did_del($extarray[0],$extarray[1]);
		needreload();
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        $_SESSION['msgtstamp']=time();
		redirect_standard('didfilter', 'rnavsort');
	break;
	case 'edtIncoming':
		$extarray=explode('/',$old_extdisplay,2);
		if (core_did_edit($extarray[0],$extarray[1],$_REQUEST)) {
			needreload();
            $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
            $_SESSION['msgtype']='success';
            $_SESSION['msgtstamp']=time();
			redirect_standard('extdisplay', 'extension', 'cidnum', 'didfilter', 'rnavsort');
		}
	break;
}

$toggle_sort = __(" (toggle sort)");

$rnaventries = array();

if($rnavsort=='description') {
    $newsort='extension';
} else {
    $newsort='description';
}
/*
$rnaventries[] = array('',__("All DIDs").' '.$toggle_sort,'','','&rnavsort='.$newsort.'&didfilter=',0);
$rnaventries[] = array('directdid',__("User DIDs"),'','','&didfilter=directdid',0);
$rnaventries[] = array('incoming',__("General DIDs"),'','','&didfilter=incoming',0);
$rnaventries[] = array('unassigned',__("Unused DIDs"),'','','&didfilter=unnasigned',0);
 */

/*
	<li><a <?php echo ($didfilter=='' ? 'class="current"':'') ?> href="<?php echo ($didfilter==''?$display_link_current:$display_link)?>"><?php echo __("All DIDs").($didfilter==''?$toggle_sort:"")?></a></li>
	<li><a <?php echo ($didfilter=='directdid' ? 'class="current"':'') ?> href="<?php echo ($didfilter=='directdid'?$display_link_current:$display_link).'&didfilter=directdid'?>"><?php echo __("User DIDs").($didfilter=='directdid'?$toggle_sort:"")?></a></li>
	<li><a <?php echo ($didfilter=='incoming' ? 'class="current"':'') ?> href="<?php echo ($didfilter=='incoming'?$display_link_current:$display_link).'&didfilter=incoming'?>"><?php echo __("General DIDs").($didfilter=='incoming'?$toggle_sort:"")?></a></li>
    <li><a <?php echo ($didfilter=='unassigned' ? 'class="current"':'') ?> href="<?php echo ($didfilter=='unassigned'?$display_link_current:$display_link).'&didfilter=unassigned'?>"><?php echo __("Unused DIDs").($didfilter=='unassigned'?$toggle_sort:"")?></a></li>
 */

$inroutes = core_did_list($rnavsort);
switch ($didfilter) {
	case 'directdid':
		foreach ($inroutes as $key => $did_items) {
			$did_dest = explode(',',$did_items['destination']);
			if (!isset($did_dest[0]) || $did_dest[0] != 'from-did-direct') {
				unset($inroutes[$key]);
			}
		}
		break;
	case 'incoming':
		foreach ($inroutes as $key => $did_items) {
			$did_dest = explode(',',$did_items['destination']);
			if (!isset($did_dest[0]) || $did_dest[0] == 'from-did-direct') {
				unset($inroutes[$key]);
			}
		}
		break;
    case 'unassigned':
        die('perro');
        foreach ($inroutes as $key => $did_items) {
            print_r($did_items);
			if (isset($did_items['destination']) && $did_items['destination'] != '') {
				unset($inroutes[$key]);
			}
		}
		break;
	default:
}
if (isset($inroutes)) {
    foreach ($inroutes as $inroute) {
        $did_dest = explode(',',$inroute['destination']);
        $didtype='incoming';
        if (isset($did_dest[0]) && $did_dest[0] == 'from-did-direct') {
            $didtype = 'userdid';
        }
        if (isset($did_dest[0]) && $did_dest[0] != 'from-did-direct') {
            $didtype = 'generaldid';
        }
        if (isset($did_dest[0]) && $did_dest[0] == '') {
            $didtype = 'unused';
        }
        
		$displaydid = ( (trim($inroute['extension']) == "") ? __("any DID") : $inroute['extension'] );
 		$displaycid = ( (trim($inroute['cidnum']) == "") ? __("any CID") : $inroute['cidnum'] );
        $desc = ( empty($inroute['description'])? "" : htmlspecialchars($inroute['description'])."<br />" );
        $rnaventries[]=array(urlencode($inroute['extension'])."/".urlencode($inroute['cidnum']),"{$desc} <span class='is-size-7'>{$displaydid} / {$displaycid}</span><span class='is-hidden'>${didtype}</span>","");
//		echo "\t<li><a ".($extdisplay==$inroute['extension']."/".$inroute['cidnum'] ? 'class="current"':'')." href=\"config.php?display=$dispnum&didfilter=$didfilter&rnavsort=$rnavsort&extdisplay=".urlencode($inroute['extension'])."/".urlencode($inroute['cidnum'])."\">{$desc} {$displaydid} / {$displaycid} </a></li>\n";
	}
}

drawListMenu($rnaventries, $type, $display, $extdisplay);
?>

<?php
$display_link = "config.php?display=$dispnum";
$display_add = $display_link;
$display_link .= (isset($extdisplay) && $extdisplay != '') ? "&extdisplay=".$extdisplay : '';
$display_link_current = $display_link.(($rnavsort == "description") ? "&rnavsort=extension" : "&rnavsort=description");
$rnav_add = ($rnavsort == "extension") ? "&rnavsort=extension" : "&rnavsort=description";
$display_link .= $rnav_add;
$display_add .= $rnav_add."&didfilter=$didfilter";
$toggle_sort = __(" (toggle sort)");
?>
<!--div class="rnav">
<ul>
	<li><a <?php echo ($didfilter=='' ? 'class="current"':'') ?> href="<?php echo ($didfilter==''?$display_link_current:$display_link)?>"><?php echo __("All DIDs").($didfilter==''?$toggle_sort:"")?></a></li>
	<li><a <?php echo ($didfilter=='directdid' ? 'class="current"':'') ?> href="<?php echo ($didfilter=='directdid'?$display_link_current:$display_link).'&didfilter=directdid'?>"><?php echo __("User DIDs").($didfilter=='directdid'?$toggle_sort:"")?></a></li>
	<li><a <?php echo ($didfilter=='incoming' ? 'class="current"':'') ?> href="<?php echo ($didfilter=='incoming'?$display_link_current:$display_link).'&didfilter=incoming'?>"><?php echo __("General DIDs").($didfilter=='incoming'?$toggle_sort:"")?></a></li>
	<li><a <?php echo ($didfilter=='unassigned' ? 'class="current"':'') ?> href="<?php echo ($didfilter=='unassigned'?$display_link_current:$display_link).'&didfilter=unassigned'?>"><?php echo __("Unused DIDs").($didfilter=='unassigned'?$toggle_sort:"")?></a></li><hr>
<?php 
//get unique incoming routes
$inroutes = core_did_list($rnavsort);
switch ($didfilter) {
	case 'directdid':
		foreach ($inroutes as $key => $did_items) {
			$did_dest = explode(',',$did_items['destination']);
			if (!isset($did_dest[0]) || $did_dest[0] != 'from-did-direct') {
				unset($inroutes[$key]);
			}
		}
		break;
	case 'incoming':
		foreach ($inroutes as $key => $did_items) {
			$did_dest = explode(',',$did_items['destination']);
			if (!isset($did_dest[0]) || $did_dest[0] == 'from-did-direct') {
				unset($inroutes[$key]);
			}
		}
		break;
	case 'unassigned':
		foreach ($inroutes as $key => $did_items) {
			if (isset($did_items['destination']) && $did_items['destination'] != '') {
				unset($inroutes[$key]);
			}
		}
		break;
	default:
}
if (isset($inroutes)) {
	foreach ($inroutes as $inroute) {
		$displaydid = ( (trim($inroute['extension']) == "") ? __("any DID") : $inroute['extension'] );
 		$displaycid = ( (trim($inroute['cidnum']) == "") ? __("any CID") : $inroute['cidnum'] );
		$desc = ( empty($inroute['description'])? "" : htmlspecialchars($inroute['description'])."<br />" );
		echo "\t<li><a ".($extdisplay==$inroute['extension']."/".$inroute['cidnum'] ? 'class="current"':'')." href=\"config.php?display=$dispnum&didfilter=$didfilter&rnavsort=$rnavsort&extdisplay=".urlencode($inroute['extension'])."/".urlencode($inroute['cidnum'])."\">{$desc} {$displaydid} / {$displaycid} </a></li>\n";
	}
}
?>
</ul>
</div-->
<div class='content'>
<?php 
    if ($extdisplay) {	
      //create variables for the selected route's settings
      $extarray=explode('/',$extdisplay,2);
      $ininfo=core_did_get($extarray[0],$extarray[1]);
      if (is_array($ininfo) && !empty($ininfo)) {
          extract($ininfo);
//echo "<pre>";          print_r($ininfo); echo "</pre>";
        $description = htmlspecialchars($description);
        $extension   = htmlspecialchars($extension);
        $cidnum      = htmlspecialchars($cidnum);
        $alertinfo   = htmlspecialchars($alertinfo);
        $grppre      = htmlspecialchars($grppre);

?>
		<h2><?php echo __("Edit Incoming Route")?>: <?php echo !empty($description)?$description:$extdisplay; ?></h2>
<?php
      } else {
        $extension = $extarray[0];
        $cidnum    = $extarray[1];
        $extdisplay = '';
?>
    <h2><?php echo __("Add Incoming Route")?></h2>
<?php
      }

      /*
    if ($delete_url) {
		  $delURL = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']."&action=delete&didfilter=$didfilter&rnavsort=$rnavsort";
		  $tlabel = sprintf(__("Delete Route %s"),!empty($description)?$description:$extdisplay);
		  $label = '<span><img width="16" height="16" border="0" title="'.$tlabel.'" alt="" src="images/core_delete.png"/>&nbsp;'.$tlabel.'</span>';
		  echo "<p><a href=".$delURL.">".$label."</a></p>";
    }*/

		// If this is a direct did, e.g. from-did-direct,nnn,1 then make a link to the extension
		//
		$did_dest = explode(',',$destination);
		if (isset($did_dest[0]) && $did_dest[0] == 'from-did-direct') {

			if (isset($amp_conf["AMPEXTENSIONS"]) && ($amp_conf["AMPEXTENSIONS"] == "deviceanduser")) {
				$editURL = $_SERVER['PHP_SELF'].'?display=users&extdisplay='.$did_dest[1];
				$EXTorUSER = __("User");
			}
			else {
				$editURL = $_SERVER['PHP_SELF'].'?display=extensions&extdisplay='.$did_dest[1];
				$EXTorUSER = __("Extension");
			}
			$result = core_users_get($did_dest[1]);
            //$label = '<span><img width="16" height="16" border="0" title="'.sprintf(__("Edit %s"),$EXTorUSER).'" alt="" src="images/user_edit.png"/>&nbsp;';
            $label= sprintf(__("Edit %s %s (%s)"),$EXTorUSER, $did_dest[1],$result['name']);
			echo "<a class='button is-small is-rounded' href=".$editURL.">".$label."</a></p>";
		}
?>
<?php 
	} else {

    echo "<h2>".__("Add Incoming Route")."</h2>\n";
 
	} 
?>
		<form id="mainform" name="editGRP" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return editGRP_onsubmit(this);">
		<input type="hidden" name="display" value="<?php echo $dispnum?>">
		<input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edtIncoming' : 'addIncoming') ?>">
		<input type="hidden" name="extdisplay" value="<?php echo $extdisplay ?>">
		<input type="hidden" name="didfilter" value="<?php echo $didfilter ?>">
		<input type="hidden" name="rnavsort" value="<?php echo $rnavsort ?>">
		<input type="hidden" name="submitclear" value="">
		<table class='table is-narrow is-borderless'>
        <tr><td colspan="2"><h5><?php echo _dgettext('amp','General Settings')?></h5></td></tr>
		<tr>
			<td><a href="#" class="info"><?php echo __("Description")?><span><?php echo __('Provide a meaningful description of what this incoming route is')?></span></a></td>
			<td><input autofocus type="text" name="description" value="<?php echo isset($description)?$description:''; ?>" tabindex="<?php echo ++$tabindex;?>" class='input w100'></td>
		</tr>
		<tr>
			<td><a href="#" class="info"><?php echo __("DID Number")?><span><?php echo __('Define the expected DID Number if your trunk passes DID on incoming calls. <br><br>Leave this blank to match calls with any or no DID info.<br><br>You can also use a pattern match (eg _2[345]X) to match a range of numbers')?></span></a></td>
			<td><input type="text" name="extension" autocomplete="cc-csc" class="input w100 noextmap" value="<?php echo isset($extension)?$extension:''; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
		</tr>
		<tr>
			<td><a href="#" class="info"><?php echo __("CallerID Number")?><span><?php echo __('Define the CallerID Number to be matched on incoming calls.<br><br>Leave this field blank to match any or no CID info. In addition to standard dial sequences, you can also put Private, Blocked, Unknown, Restricted, Anonymous and Unavailable in order to catch these special cases if the Telco transmits them.')?></span></a></td>
			<td><input type="text" name="cidnum" value="<?php echo isset($cidnum)?$cidnum:'' ?>" tabindex="<?php echo ++$tabindex;?>" class='input w100'></td>
		</tr>

		<tr>
			<td><a href="#" class="info"><?php echo __("CID Priority Route")?><span><?php echo __('This effects CID ONLY routes where no DID is specified. If checked, calls with this CID will be routed to this route, even if there is a route to the DID that was called. Normal behavior is for the DID route to take the calls. If there is a specific DID/CID route for this CID, that route will still take the call when that DID is called.')?></span></a></td>
            <td>
            <?php echo "<div class='field'><input type='checkbox' class='switch' id='pricid' name='pricid' value='CHECKED' $pricid tabindex='".++$tabindex."'/><label style='height:auto; line-height:1em; padding-left:3em;' for='pricid'>&nbsp;</label></div>\n"; ?>
            </td>
		</tr>

		<tr><td colspan="2"><h5><?php echo __("Options")?></h5></td></tr>
		<tr>
			<td><a href="#" class="info"><?php echo __("Alert Info")?><span><?php echo __('ALERT_INFO can be used for distinctive ring with SIP devices.')?></span></a></td>
			<td><input type="text" name="alertinfo" size="10" value="<?php echo $alertinfo ?>" tabindex="<?php echo ++$tabindex;?>" class='input w100'></td>
		</tr>
		<tr>
			<td><a href="#" class="info"><?php echo __("CID name prefix")?><span><?php echo __('You can optionally prefix the CallerID name. ie: If you prefix with "Sales:", a call from John Doe would display as "Sales:John Doe" on the extensions that ring.')?></span></a></td>
			<td><input type="text" name="grppre" value="<?php echo $grppre ?>" tabindex="<?php echo ++$tabindex;?>" class='input w100'></td>
		</tr>
<?php   if (function_exists('music_list')) { ?>
		<tr>
			<td><a href="#" class="info"><?php echo __("Music On Hold")?><span><?php echo __("Set the MoH class that will be used for calls that come in on this route. For example, choose a type appropriate for routes coming in from a country which may have announcements in their language.")?></span></a></td>
			<td>
				<select name="mohclass" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
				<?php
					$tresults = music_list();
					$cur = (isset($mohclass) && $mohclass != "" ? $mohclass : 'default');
//					echo '<option value="none">'.__("No Music")."</option>";
					if (isset($tresults[0])) {
						foreach ($tresults as $tresult) {
							($tresult == 'none' ? $ttext = __("No Music") : $ttext = $tresult); 
	    						($tresult == 'default' ? $ttext = __("Default") : $ttext = $tresult);
							echo '<option value="'.$tresult.'"'.($tresult == $cur ? ' SELECTED' : '').'>'.__($ttext)."</option>\n";
						}
					}
				?>		
				</select>		
			</td>
		</tr>
<?php } ?>
		<tr>
			<td><a href="#" class="info"><?php echo __("Signal RINGING")?><span><?php echo __('Some devices or providers require RINGING to be sent before ANSWER. You\'ll notice this happening if you can send calls directly to a phone, but if you send it to an IVR, it won\'t connect the call.')?></span></a></td>
            <td>
            <?php echo "<div class='field'><input type='checkbox' class='switch' id='ringing' name='ringing' value='CHECKED' $ringing tabindex='".++$tabindex."'/><label style='height:auto; line-height:1em; padding-left:3em;' for='ringing'>&nbsp;</label></div>\n"; ?>
            </td>

		</tr>
		<tr>
			<td><a href="#" class="info"><?php echo __("Pause Before Answer")?><span><?php echo __("An optional delay to wait before processing this route. Setting this value will delay the channel from answering the call. This may be handy if external fax equipment or security systems are installed in parallel and you would like them to be able to seize the line.")?></span></a></td>
			<td><input type="text" name="delay_answer" size="3" value="<?php echo ($delay_answer != '0')?$delay_answer:'' ?>" tabindex="<?php echo ++$tabindex;?>"></td>
		</tr>

		<tr><td colspan="2"><h5><?php echo __("Privacy")?></h5></td></tr>

		<tr>
			<td><a href="#" class="info"><?php echo __("Privacy Manager")?><span><?php echo __('If no CallerID has been received, Privacy Manager will ask the caller to enter their phone number. If an user/extension has Call Screening enabled, the incoming caller will be be prompted to say their name when the call reaches the user/extension.')?></span></a></td>
			<td>
				<select name="privacyman" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
					<option value="0" <?php  echo ($privacyman == '0' ? 'SELECTED' : '')?>><?php echo __("No")?>
					<option value="1" <?php  echo ($privacyman == '1' ? 'SELECTED' : '')?>><?php echo __("Yes")?>
				</select>
			</td>
		</tr>
		<tr class="pm_opts" <?php echo $privacyman == '0' ? 'style="display:none"':''?>>
			<td><a href="#" class="info"><?php echo __("Max attempts")?><span><?php echo __('Number of attempts the caller has to enter a valid CallerID')?></span></a></td>
			<td>
				<select name="pmmaxretries" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
					<?php
						for($i=1;$i<11;$i++){
							if(!isset($pmmaxretries)||$pmmaxretries==''){$pmmaxretries=3;}//set defualts
							echo '<option value="'.$i.'"'.($pmmaxretries == $i ? 'SELECTED' : '').' >'.$i.'</option>';
						}
					?>
				</select> 
			</td>
		</tr>	
		<tr class="pm_opts" <?php echo $privacyman == '0' ? 'style="display:none"':''?>>
			<td><a href="#" class="info"><?php echo __("Min Length")?><span><?php echo __('Minimum amount of digits CallerID needs to contain in order to be considered valid')?></span></a></td>
			<td>
				<select name="pmminlength" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
					<?php
						if(!isset($pmminlength)||$pmminlength==''){$pmminlength=10;}//set USA defaults
						for($i=1;$i<16;$i++){
							echo '<option value="'.$i.'"'.($pmminlength == $i ? 'SELECTED' : '').' >'.$i.'</option>';
						}
					?>
				</select> 
			</td>
		</tr>	
<?php
	// implementation of module hook
    // object was initialized in config.php
    echo process_tabindex($module_hook->hookHtml,$tabindex);
?>
		<tr><td colspan="2"><h5><?php echo __("Set Destination")?></h5></td></tr>

<?php 
//draw goto selects
echo drawselects(isset($destination)?$destination:null,0);
?>
		<tr>
			<td colspan="2">
				<button type='button' name="submitclear" class='button is-rounded is-small' onclick='dosubmitclear(this)'><?php echo __("Clear Destination & Submit")?></button>
			</td>		
		</tr>
		</table>

	</form>
<script>

var destrequired = true;

function dosubmitclear(evt) {
    destrequired = false;
    $('input[name=submitclear]').val('1');
    $('#mainform').trigger('submit');
}

function editGRP_onsubmit(theForm) {

	var msgInvalidDIDNumb = "<?php echo __('Please enter a valid DID Number'); ?>";
	var msgInvalidCIDNum = "<?php echo __('Please enter a valid CallerID Number'); ?>";
	var msgInvalidFaxEmail = "<?php echo __('Please enter a valid Fax Email or leave it empty to use the default'); ?>";
	var msgInvalidPause = "<?php echo __('Please enter a valid number for Pause after answer'); ?>";
	var msgInvalidPauseBefore = "<?php echo __('Please enter a valid number for Pause Before Answer field'); ?>";
	var msgConfirmDIDCIDBlank = "<?php echo __('Leaving the DID Number AND the CallerID Number empty will match all incoming calls received not routed using any other defined Incoming Route.\n\nAre you sure?'); ?>";
	var msgConfirmDIDNonStd = "<?php echo __('DID information is normally just an incoming telephone number or for advanced users, a valid Asterisk Dial Pattern\n\nYou have entered a non standard DID pattern.\n\nAre you sure this is correct?'); ?>";
	var msgConfirmDIDNoSlash = "<?php echo __('A Slash (\'/\') is never a valid DID. Please remove it and try again'); ?>";
	var msgInvalidGrpPrefix = "<?php echo __('Invalid CallerID prefix.'); ?>";
	
	setDestinations(theForm,1);
	
	defaultEmptyOK = true;
	if (!isDialpattern($('input[name=extension]').val())) {
		// warn the user that DID is normally numbers
		if (!confirm(msgConfirmDIDNonStd))
			return false;
	}
	
	if (isInside($('input[name=extension]').val(), "/")) {
		warnInvalid(theForm.extension, msgConfirmDIDNoSlash);
		return false;
	}

    var mycid = theForm.cidnum.value.toLowerCase();
    console.log(mycid);
	if (!isDialpattern(mycid) && mycid.substring(0,4) != "priv" && mycid.substring(0,5) != "block" && mycid != "unknown" && mycid.substring(0,8) != "restrict" && mycid.substring(0,7) != "unavail" && mycid.substring(0,6) != "anonym" && mycid.substring(0,7) != "withheld")
		return warnInvalid(theForm.cidnum, msgInvalidCIDNum);
	
	if (!isInteger(theForm.delay_answer.value))
		return warnInvalid(theForm.delay_answer, msgInvalidPauseBefore);
	
	if (!validateDestinations(theForm,1,destrequired))
		return false;
	
	// warning about 'any DID / any CID'
	if ($('input[name=extension]').val() == "" && $('input[name=cidnum]').val() == "" &&  $('input[name=channel]').val()== "" ) {
		if (!confirm(msgConfirmDIDCIDBlank))
			return false;
	}
	defaultEmptyOK = true;
	if (!isCallerID(theForm.grppre.value))
        return warnInvalid(theForm.grppre, msgInvalidGrpPrefix);

    $.LoadingOverlay('show');	
	return true;
}

$(function() {
	//show/hide privacy manager options
	$('select[name=privacyman]').on('change',function(){
		if($(this).val()==0){$('.pm_opts').fadeOut();}
		if($(this).val()==1){$('.pm_opts').fadeIn();}
	});
});

<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
