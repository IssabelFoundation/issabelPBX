<?php 
/* $Id: */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

//Both of these are used for switch on config.php
$display = isset($_REQUEST['display'])?$_REQUEST['display']:'restart';

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$restartlist = isset($_REQUEST['restartlist'])?$_REQUEST['restartlist']:'';

switch ($action) {
	case "restart":
		$restarted = false;
		if(is_array($restartlist) && sizeof($restartlist))  {
			foreach($restartlist as $device)  {
				restart_device($device);
			}
			$restarted = true;
		}
		break;
}

?>
<p>
<?php
	echo "<form name='restart' action='".$_SERVER['PHP_SELF']."' method='post'>\n";
	echo "<input type='hidden' name='action' value='restart'>\n";
	echo "<input type='hidden' name='display' value='${display}'>\n";

	echo "<table><tr><td><div class='content'><h2>"._("Restart Phones")."</h2></td></tr>\n";
	if($restarted)  {
		echo "<tr><td><b>"._("Restart requests sent!")."</b><br/><br/></td></tr>";
	}
	else  {
		echo "<tr><td><b>"._("Warning: The restart mechanism behavior is vendor specific.  Some vendors only restart the phone if there is a change to the phone configuration or if an updated firmware is available via tftp/ftp/http"). "</b><br/><br/></td></tr>";
		
	}

?>
	<tr><td valign='top'><a href='#' class='info'><?php echo _("Device List:")."<span><br>"._("Select Device(s) to restart.  Currently, only Aastra, Snom, Polycom, Grandstream and Cisco devices are supported.  All other devices will not show up in this list.  Click the \"Select All\" button to restart all supported devices. ") ?> 
	<br><br></span></a></td>
	<tr>
	<td valign="top"> 
	
	<select multiple="multiple" name="restartlist[]" id="xtnlist"  tabindex="<?php echo ++$tabindex;?>">
	<?php 
	if (is_null($selected)) $selected = array();
	foreach (core_devices_list() as $device) {
		if($ua = get_device_useragent($device[0]))  {
			echo '<option value="'.$device[0].'" ';
			if (array_search($device[0], $selected) !== false) echo ' selected="selected" ';
			echo '>'.$device[0].' - '.$device[1].' - '.ucfirst($ua).' Device</option>';
		}
	}
	?>
	</select>		
	</td></tr>
	<tr><td><input type="button" name="Button" value="<?php echo _('SELECT ALL'); ?>" onclick="selectAll('xtnlist',true)" /></td></tr>

<?php
			// implementation of module hook
			// object was initialized in config.php
			echo $module_hook->hookHtml;
?>
	
	<tr>
	<td colspan="2"><br><h6><input type="submit" name="Submit" type="button" value="<?php echo _("Restart Phones")?>" tabindex="<?php echo ++$tabindex;?>"></h6></td>
	</tr>
	</table>
	</form>
	<script language="javascript">
	<!-- hide script from older browsers

	function selectAll(selectBox,selectAll) {
		// have we been passed an ID
		if (typeof selectBox == "string") {
			selectBox = document.getElementById(selectBox);
		}
		// is the select box a multiple select box?
		if (selectBox.type == "select-multiple") {
			for (var i = 0; i < selectBox.options.length; i++) {
				selectBox.options[i].selected = selectAll;
			}
		}
	}
	// end of hiding script -->
	</script>
