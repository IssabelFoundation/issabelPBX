<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$dispnum = "featurecodeadmin"; //used for switch on config.php

$tabindex = 0;

//if submitting form, update database
switch ($action) {
  case "save":
  	featurecodeadmin_update($_REQUEST);
  	needreload();
  break;
}

$featurecodes = featurecodes_getAllFeaturesDetailed();
?>

	<form autocomplete="off" name="frmAdmin" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return frmAdmin_onsubmit();">
	<input type="hidden" name="display" value="<?php echo $dispnum?>">
	<input type="hidden" name="action" value="save">

	<?php
			$exten_conflict_arr = array();
			$conflict_url = array();
			$exten_arr = array();
			foreach ($featurecodes as $result) {
				/* if the feature code starts with "In-Call Asterisk" then it is not conflicting with normal feature codes. This would be featuremap and future
				 * application map type codes. This is a real kludge and instead there should be a category associated with these codes when the feature code
				 * is created. However, the logic would be the same, thus my willingness to put in such a kludge for now. When the schema changes to add this
				 * then this can be updated to reflect that
				 */
				if (($result['featureenabled'] == 1) && ($result['moduleenabled'] == 1) && substr($result['featuredescription'],0,16) != 'In-Call Asterisk') {
					$exten_arr[] = ($result['customcode'] != '')?$result['customcode']:$result['defaultcode'];
				}
			}
			$usage_arr = framework_check_extension_usage($exten_arr);
			unset($usage_arr['featurecodeadmin']);
			if (!empty($usage_arr)) {
				$conflict_url = framework_display_extension_usage_alert($usage_arr,false,false);
			}
			if (!empty($conflict_url)) {
				$str = _("You have feature code conflicts with extension numbers in other modules. This will result in unexpected and broken behavior.");
				echo "<script>javascript:alert('$str')</script>";
      	echo "<h4>"._("Feature Code Conflicts with other Extensions")."</h4>";
      	echo implode('<br .>',$conflict_url);

				// Create hash of conflicting extensions
				//
				foreach ($usage_arr as $module_name => $details) {
					foreach (array_keys($details) as $exten_conflict) {
						$exten_conflict_arr[$exten_conflict] = true;
					}
				}

				// Now check for conflicts within featurecodes page
				//
				$unique_exten_arr = array_unique($exten_arr);
				$feature_conflict_arr = array_diff_assoc($exten_arr, $unique_exten_arr);
				foreach ($feature_conflict_arr as $value) {
					$exten_conflict_arr[$value] = true;
				}
      }
	?>
	<table>
	<tr><td colspan="4"><h3><?php echo _("Feature Code Admin"); ?><hr></h3></td></tr>
	<tr>
		<td colspan="2">&nbsp;</td>
		<td align="center"><b><?php echo _("Use"); ?><br><?php echo _("Default"); ?>?</b></td>
		<td align="center"><b><?php echo _("Feature"); ?><br><?php echo _("Status"); ?></b></td>
	</tr>
	<?php 
	$currentmodule = "(none)";
	foreach($featurecodes as $item) {

		$moduledesc = isset($item['moduledescription']) ? modgettext::_($item['moduledescription'], $item['modulename']) : null;
		// just in case the translator put the translation in featurcodes module:
		if (($moduledesc !== null) && ($moduledesc == $item['moduledescription'])) {
			$moduledesc = _($moduledesc);
		}

		$featuredesc = modgettext::_($item['featuredescription'], $item['modulename']);
		// just in case the translator put the translation in featurcodes module:
		if ($featuredesc == $item['featuredescription']) {
			$featuredesc = _($featuredesc);
		}

		$moduleena = ($item['moduleenabled'] == 1 ? true : false);
		$featureid = $item['modulename'] . '#' . $item['featurename'];
		$featureena = ($item['featureenabled'] == 1 ? true : false);
		$featurecodedefault = (isset($item['defaultcode']) ? $item['defaultcode'] : '');
		$featurecodecustom = (isset($item['customcode']) ? $item['customcode'] : '');

		$thiscode = ($featurecodecustom != '') ? $featurecodecustom : $featurecodedefault;
		
		if ($currentmodule != $moduledesc) {
			$currentmodule = $moduledesc;
			?>
			<tr>
				<td colspan="4">
					<h4>
					<?php echo $currentmodule; ?>
					<?php if ($moduleena == false) {?>
					<i>(<?php echo _("Disabled"); ?>)</i>
					<?php } ?>
					</h4>
				</td>
			</tr>
			<?php
		}
		?> 	
		<tr>
		<?php 
			if (array_key_exists($thiscode, $exten_conflict_arr)) { 
				$style = "style='color:red'"; 
				$background = "style='background:red'"; 
				$strong = "<strong>";
				$endstrong = "</strong>";
			} else {
				$style = ""; 
				$background = ""; 
				$strong = "";
				$endstrong = "";
			} 
		?>
			<td <?php echo $style ?>> 
				<?php echo $strong.$featuredesc.$endstrong; ?>
			</td>
			<td>
				<input type="text" name="custom#<?php echo $featureid; ?>" value="<?php echo $featurecodecustom; ?>" <?php echo $background; ?> size="4" tabindex="<?php echo ++$tabindex;?>">
			</td>
			<td align="center">
				<input type="checkbox" onclick="usedefault_onclick(this);" name="usedefault_<?php echo $featureid; ?>"<?php if ($featurecodecustom == '') echo "checked"; ?>>
				<input type="hidden" name="default_<?php echo $featureid; ?>" value="<?php echo $featurecodedefault; ?>">
				<input type="hidden" name="origcustom_<?php echo $featureid; ?>" value="<?php echo $featurecodecustom; ?>">
			</td>
			<td>
				<select name="ena#<?php echo $featureid; ?>">
				<option <?php if ($featureena == true) echo ("selected "); ?>value="1"><?php echo _("Enabled"); ?></option>
				<option <?php if ($featureena == false) echo ("selected "); ?>value="0"><?php echo _("Disabled"); ?></option>
				</select>
			</td>
		</tr>	
		<?php
	}
 ?>
	<tr>
		<td colspan="4"><br><h6><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>"></h6></td>		
	</tr>
	</table>

	<script language="javascript">
	<!--
	
	var theForm = document.frmAdmin;
	
	callallusedefaults();
	
	// call the onclick function for all the Use Default boxes
	function callallusedefaults() {
		for (var i=0; i<theForm.elements.length; i++) {
			var theFld = theForm.elements[i];
			if (theFld.name.substring(0,11) == "usedefault_") {
				usedefault_onclick(theFld);
			}
		}
	}
		
	// disabled the custom code box if using default and also puts the default number in the box
	function usedefault_onclick(chk) {
		var featureid = chk.name.substring(11);
		if (chk.checked) {
			theForm.elements['origcustom_' + featureid].value = theForm.elements['custom#' + featureid].value;			
			theForm.elements['custom#' + featureid].value = theForm.elements['default_' + featureid].value;
		} else {
			theForm.elements['custom#' + featureid].value = theForm.elements['origcustom_' + featureid].value;
		}
		theForm.elements['custom#' + featureid].readOnly = chk.checked;
	}
	
	// form validation
	function frmAdmin_onsubmit() {
                var msgErrorMissingFC = "<?php echo addslashes(_("Please enter a Feature Code or check Use Default for all Enabled Feature Codes")); ?>";
		var msgErrorDuplicateFC = "<?php echo _("Feature Codes have been duplicated"); ?>";
		var msgErrorProceedOK = "<?php echo _("Are you sure you wish to proceed?"); ?>";
		
		for (var i=0; i<theForm.elements.length; i++) {
			var theFld = theForm.elements[i];
			if (theFld.name.substring(0,7) == "custom#") {
				var featureid = theFld.name.substring(7);
				// check that every non default has a custom code
				if (!theForm.elements['usedefault_' + featureid].checked) {
					defaultEmptyOK = false;
					if (!isDialDigits(theFld.value))
						return warnInvalid(theFld, msgErrorMissingFC);
						
					if (isDuplicated(theFld.name, theFld.value))
						return confirm(msgErrorDuplicateFC+".  "+msgErrorProceedOK);
				}
			}
		}
		
		
		return true;
	}

	function isDuplicated(firstfldname, firstfc) {
		for (var i=0; i<theForm.elements.length; i++) {
			var theFld = theForm.elements[i];
			if (theFld.name.substring(0,7) == "custom#" && theFld.name != firstfldname) {
				if (theFld.value == firstfc)
					return true;
			}
		}
	}
	
	//-->
	</script>
	
	</form>
