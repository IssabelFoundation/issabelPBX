<?php
$easymode = ($digium_phones->get_general('easy_mode') == "yes"?true:false);
$show['Phones']		= '<li><a ' 
				. ($_REQUEST['digium_phones_form'] == 'phones_edit' ? 'class="current ui-state-highlight" ' : '') 
				. 'href="config.php?type=setup&display=digium_phones&digium_phones_form=phones_edit">' . __("Phones") . '</a></li>'."\n";

if (!$easymode) {
	$show['Phonebooks']		= '<li><a ' 
				. ($_REQUEST['digium_phones_form'] == 'phonebooks_edit' ? 'class="current ui-state-highlight" ' : '') 
				. 'href="config.php?type=setup&display=digium_phones&digium_phones_form=phonebooks_edit">' . __("Phonebooks") . '</a></li>'."\n";

$show['Alerts']		= '<li><a ' 
				. ($_REQUEST['digium_phones_form'] == 'alerts_edit' ? 'class="current ui-state-highlight" ' : '') 
				. 'href="config.php?type=setup&display=digium_phones&digium_phones_form=alerts_edit">' . __("Alerts") . '</a></li>'."\n";

$show['Ringtones']		= '<li><a ' 
				. ($_REQUEST['digium_phones_form'] == 'ringtones_edit' ? 'class="current ui-state-highlight" ' : '') 
				. 'href="config.php?type=setup&display=digium_phones&digium_phones_form=ringtones_edit">' . __("Ringtones") . '</a></li>'."\n";

$show['Phone Applications']		= '<li><a ' 
				. ($_REQUEST['digium_phones_form'] == 'applications_edit' ? 'class="current ui-state-highlight" ' : '') 
				. 'href="config.php?type=setup&display=digium_phones&digium_phones_form=applications_edit">' . __("Phone Applications") . '</a></li>'."\n";

$show['Logos']		= '<li><a ' 
				. ($_REQUEST['digium_phones_form'] == 'logos_edit' ? 'class="current ui-state-highlight" ' : '') 
				. 'href="config.php?type=setup&display=digium_phones&digium_phones_form=logos_edit">' . __("Logos") . '</a></li>'."\n";
}
$show['Networks']		= '<li><a ' 
				. ($_REQUEST['digium_phones_form'] == 'networks_edit' ? 'class="current ui-state-highlight" ' : '') 
				. 'href="config.php?type=setup&display=digium_phones&digium_phones_form=networks_edit">' . __("Networks") . '</a></li>'."\n";
	
$show['External Lines']		= '<li><a ' 
				. ($_REQUEST['digium_phones_form'] == 'externallines_edit' ? 'class="current ui-state-highlight" ' : '') 
				. 'href="config.php?type=setup&display=digium_phones&digium_phones_form=externallines_edit">' . __("External Lines") . '</a></li>'."\n";

$show['General Settings']		= '<li><a ' 
				. ($_REQUEST['digium_phones_form'] == 'general_edit' ? 'class="current ui-state-highlight" ' : '') 
				. 'href="config.php?type=setup&display=digium_phones&digium_phones_form=general_edit">' . __("General Settings") . '</a></li>'."\n";

$show['Firmware']		= '<li><a ' 
				. ($_REQUEST['digium_phones_form'] == 'firmware_edit' ? 'class="current ui-state-highlight" ' : '') 
				. 'href="config.php?type=setup&display=digium_phones&digium_phones_form=firmware_edit">' . __("Firmware") . '</a></li>'."\n";


//show the page
echo '<div class="rnav"><ul>';
foreach ($show as $s) {
	echo $s;
}
echo '</ul><div style="width:251px; padding-top:220px;" >';
?>

</div></div>
