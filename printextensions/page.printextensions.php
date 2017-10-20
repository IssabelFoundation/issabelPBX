<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$dispnum = 'printextensions';
$tabindex = isset($tabindex) && $tabindex ? $tabindex : 0;
$exact = false;
if (isset($_POST['search_pattern'])) {
	if (isset($_POST['exact'])) {
		$search_pattern = $_POST['search_pattern'];
		$exact = true;
		} else if (isset($_POST['bounded'])) {
			$search_pattern = '/^'.$_POST['search_pattern'].'$/';
			} else if (isset($_POST['regex'])) {
				$search_pattern = '/'.$_POST['search_pattern'].'/';
			}
} else {
	$search_pattern = '';
}
if (!$quietmode) {
	?>
	<br /><br />
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" name="exten_search">
		<input type="hidden" name="display" value="<?php echo $dispnum ?>">
		<input type="hidden" name="type" value="<?php echo $type ?>">
		<table>
			<tr>
				<td class="label" align="right"><a href="#" class="info"><?php echo _("Search:")?>
				<span><?php echo _("You can narrow the list of extensions based on a search criteria. If you search for an exact extension number the page will redirect to the edit page for the given number. You can also do a bounded or unbounded regex search. The bounded search simply encloses you search criteria between a '^' and '$' where as an unbounded one is completely free form. All normal regex patterns are acceptable in your search. So for example, a bounded search of 20\d\d would search for all extensions of the form 20XX. The resulting lists of numbers all contain links to go directly to the edit pages and the Printer Friendly page will reflect the filtered list of numbers.") ?></span>
				</a></td>
				<td class="type">
					<input name="search_pattern" type="search" size="30" 
						value="<?php echo htmlspecialchars(isset($_POST['search_pattern']) ? $_POST['search_pattern'] : '');?>" 
						tabindex="<?php echo ++$tabindex;?>">
				</td>
				<td valign="top"></td>
				<td valign="top" class="label">
					<input type="submit" name="exact" class="button" 
					value="<?php echo _("Search Exact Exten")?>" tabindex="<?php echo ++$tabindex;?>">
					<input type="submit" name="bounded" class="button" 
					value="<?php echo _("Search Bounded Regex")?>" tabindex="<?php echo ++$tabindex;?>">
					<input type="submit" name="regex" class="button" 
					value="<?php echo _("Search Unbounded Regex")?>" tabindex="<?php echo ++$tabindex;?>">
				</td>
			</tr>
		</table>
	</form>
	<?php
}

global $active_modules;

$html_txt = '<div class="content">';

if (!$extdisplay) {
	$html_txt .= '<br><h2>'._("IssabelPBX Extension Layout").'</h2>';
}

$full_list = framework_check_extension_usage(true);

//add did's
if (function_exists('core_did_list')) {
	$destination = array();
	$dids_list = core_did_list();
	foreach ($dids_list as $dl) {
		$exten = ($dl['extension'] ? $dl['extension'] : '') . ($dl['cidnum'] != '' ? '/' . $dl['cidnum'] : '') ;
		if ($exten == '') {
			$exten = 'Catchall';
		}
		if ($dl['destination']) {
			$destination[$exten] = $dl['destination'];
		}
		$description = $dl['description'] ? $dl['description'] : $exten;
		$full_list['did'][" $exten"] = array(
									'description'	=> $description,
									'status'		=> 'INUSE',
									'edit_url'		=> 'config.php?type=setup&display=did&extdisplay=' . ($exten == 'Catchall' ? '/' : $exten),
										);
	}
	// fake out the code below so ids and classes get set properly
	$active_modules['did']['rawname'] = 'did';
	if (!empty($destination)) {
		$dusage = framework_identify_destinations($destination);
	}
}

if ($search_pattern != '') {
	$found=0;
	foreach ($full_list as $module => $entries) {
		$this_module = $module;
		foreach (array_keys($entries) as $exten) {
			if (($exact === true && $search_pattern != $exten) 
			|| ($exact === false && !preg_match($search_pattern,$exten))) {
				unset($full_list[$module][$exten]);
			} else {
				$found++;
				if ($exact && $found == 1) {
					$found_url = $full_list[$module][$exten]['edit_url'];
				}
			}
			if (!count($full_list[$this_module])) {
				unset($full_list[$this_module]);
			}
		}
	}
}
if ($exact && $found ==1) {
	redirect($found_url);
}

if ($search_pattern != '' && $found == 0) {
	$html_txt .= '<br /><h3>'._("No Matches for the Requested Search").'</h3><br /><br /><br /><br />';
}


foreach ($full_list as $key => $value) {

	$sub_heading_id = $txtdom = $active_modules[$key]['rawname'];

	// did domain doesn't really exist, faked out above, make it core
	if ($txtdom == 'did') {
		$txtdom = 'core';
	}
	
	if (isset($active_modules[$key]['rawname']) && $active_modules[$key]['rawname'] == 'featurecodeadmin' 
		|| ($quietmode && !isset($_REQUEST[$sub_heading_id]))) {
		continue; // featurecodes are fetched below
	}
	if ($key == 'did') {
		$active_modules[$key]['name'] = 'Inbound Routes';
		$core_heading = $sub_heading =  modgettext::_($active_modules[$key]['name'], $txtdom);
	} elseif ($txtdom == 'core') {
		$active_modules[$key]['name'] = 'Extensions';
		$core_heading = $sub_heading =  modgettext::_($active_modules[$key]['name'], $txtdom);
	} else {
		$sub_heading =  modgettext::_($active_modules[$key]['name'], $txtdom);
	}

	$module_select[$sub_heading_id] = $sub_heading;
	$textext = $key != 'did' ? _("Extension") : _("Destination");
	$html_txt_arr[$sub_heading] = "<div class=\"$sub_heading_id\"><table border=\"0\" width=\"75%\"><tr width='90%'><td><br><strong><a href=\"config.php?display=printextensions&sort_table=$sub_heading_id&sort_col=1\">".sprintf("%s",$sub_heading)."</a></strong></td><td width=\"10%\" align=\"right\"><br><strong><a href=\"config.php?display=printextensions&sort_table=$sub_heading_id&sort_col=2\">".$textext."</a></strong></td></tr>\n";
	if ($_GET["sort_table"] == $sub_heading_id || $_POST["sort_table"] == $sub_heading_id) {
		if ($_GET["sort_col"] == 1 || $_POST["sort_col"] == 1) {
			asort($value);
		} else {
		}
	}
	foreach ($value as $exten => $item) {
		$description = explode(":",$item['description'],2);
		$label_desc = count($description) <= 1 || trim($description[1]) == '' ? $exten : $description[1];

		if ($key == 'did') {
			foreach ($dusage[$destination[trim($exten)]] as $mod => $parts) {
				$description = $parts['description'];
				$edit_url = $parts['edit_url'];
				break;
			}
			if ($quietmode) {
				$label_exten = $description;
			} else {
				$label_exten = "<a href='".$edit_url."'>$description</a>";
				$label_desc = "<a href='".$item['edit_url']."'>$exten</a>";
			}
		} else {
			if ($quietmode) {
				$label_exten = $exten;
			} else {
				$label_exten = "<a href='".$item['edit_url']."'>$exten</a>";
				$label_desc = "<a href='".$item['edit_url']."'>$label_desc</a>";
			}
		}
		$html_txt_arr[$sub_heading] .= "<tr width=\"65%\"><td>$label_desc</td><td width=\"35%\" align=\"right\">".$label_exten."</td></tr>\n";
	}
	$html_txt_arr[$sub_heading] .= "</table></div>";
}

function core_top($a, $b) {
	global $core_heading;

	if ($a == $core_heading) {
		return -1;
	} elseif ($b == $core_heading) {
		return 1;
	} elseif ($a != $b) {
		return $a < $b ? -1 : 1;
	} else {
		return 0;
	}
}

if (is_array($html_txt_arr)) {
	uksort($html_txt_arr, 'core_top');
}
if (!$quietmode) {
	//asort($module_select);
	if (is_array($module_select)) uasort($module_select, 'core_top');
}

// Now, get all featurecodes.
//
$sub_heading_id =  'featurecodeadmin';
if ((!$quietmode || isset($_REQUEST[$sub_heading_id])) && isset($full_list['featurecodeadmin'])) {
	$featurecodes = featurecodes_getAllFeaturesDetailed(false);
	$sub_heading =  modgettext::_($active_modules['featurecodeadmin']['name'], $txtdom);
	$module_select[$sub_heading_id] = $sub_heading;
	$html_txt_arr[$sub_heading] =  "<div class=\"$sub_heading_id\"><table border=\"0\" width=\"75%\"><tr colspan=\"2\" width='100%'><td><br /><strong>".sprintf("%s",$sub_heading)."</strong></td></tr>\n";
	foreach ($featurecodes as $item) {
		$moduleena = ($item['moduleenabled'] == 1 ? true : false);
		$featureena = ($item['featureenabled'] == 1 ? true : false);
		$featurecodedefault = (isset($item['defaultcode']) ? $item['defaultcode'] : '');
		$featurecodecustom = (isset($item['customcode']) ? $item['customcode'] : '');
		$thiscode = ($featurecodecustom != '') ? $featurecodecustom : $featurecodedefault;

		if ($search_pattern != '') {
			if (!isset($full_list['featurecodeadmin'][$thiscode])) {
				continue;
			}
		}

		$txtdom = $item['modulename'];
		modgettext::textdomain($txtdom);
		if ($featureena && $moduleena) {
			$label_desc = sprintf(modgettext::_($item['featuredescription'],$txtdom));
			if (!$quietmode) {
				$thiscode = "<a href='config.php?type=setup&display=featurecodeadmin'>$thiscode</a>";
				$label_desc = "<a href='config.php?type=setup&display=featurecodeadmin'>$label_desc</a>";
			}
			$html_txt_arr[$sub_heading] .= "<tr width=\"90%\"><td>$label_desc</td><td width=\"10%\" align=\"right\">".$thiscode."</td></tr>\n";
		}
	}
}

$html_txt_arr[$sub_heading] .= "</table></div>";
$html_txt .= implode("\n",$html_txt_arr);

if (!$quietmode && ($search_pattern == '' || $found > 0)) {
	$rnav_txt = '<div class="rnav"><form name="print" action="'.$_SERVER['PHP_SELF'].'" target="_blank" method="post">';
	$rnav_txt .= '<input type="hidden" name="quietmode" value="on">';
	$rnav_txt .= '<input type="hidden" name="display" value="'.$dispnum.'">';
	$rnav_txt .= '<input type="hidden" name="type" value="'.$type.'">';
	if ($_GET["sort_table"]) {
		$rnav_txt .= '<input type="hidden" name="sort_table" value="'.$_GET["sort_table"].'">';
		$rnav_txt .= '<input type="hidden" name="sort_col" value="'.$_GET["sort_col"].'">';
	}
	if ($search_pattern != '') {
		$rnav_txt .= '<input type="hidden" name="search_pattern" value="'.$_POST['search_pattern'].'">';
		if (isset($_POST['exact'])) {
			$rnav_txt .= '<input type="hidden" name="exact" value="'.$_POST['exact'].'">';
			} else if (isset($_POST['bounded'])) {
				$rnav_txt .= '<input type="hidden" name="bounded" value="'.$_POST['bounded'].'">';
				} else if (isset($_POST['regex'])) {
					$rnav_txt .= '<input type="hidden" name="regex" value="'.$_POST['regex'].'">';
				}
			}

			$rnav_txt .= '<ul>';
			if (is_array($module_select)) foreach ($module_select as $id => $sub) {
				$rnav_txt .= "<li><input type=\"checkbox\" value=\"$id\" name=\"$id\" id=\"$id\" class=\"disp_filter\" CHECKED /><label id=\"lab_$id\" name=\"lab_$id\" for=\"$id\">$sub</label></li>\n";
			}
			$rnav_txt .= "</ul><hr><div style=\"text-align:center\"><input type=\"submit\" value=\"".sprintf(modgettext::_("Printer Friendly Page", $dispnum))."\" /></div>\n";
			echo $rnav_txt;
			?>
			<script language="javascript">
			<!-- Begin

			$(document).ready(function(){
				$(".disp_filter").click(function(){
					$("."+this.id).slideToggle();
				});	
			});

			// End -->
			</script>
			</form></div>
			<?php
	}
	echo $html_txt."</div>";
?>
