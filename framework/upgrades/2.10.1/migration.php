<?php
//add setting for buffer compression callback
global $amp_conf;
include_once ($amp_conf['AMPWEBROOT'].'/admin/libraries/issabelpbx_conf.class.php');
$issabelpbx_conf =& issabelpbx_conf::create();

outn(_("removing deprecated Advanced settings if needed.."));
//depricated views
//
$issabelpbx_conf->remove_conf_settings('BRAND_ISSABELPBX_ALT_RIGHT');
$issabelpbx_conf->remove_conf_settings('BRAND_IMAGE_ISSABELPBX_LINK_RIGHT');
$issabelpbx_conf->remove_conf_settings('BRAND_HIDE_LOGO_RIGHT');
$issabelpbx_conf->remove_conf_settings('BRAND_HIDE_HEADER_VERSION');
$issabelpbx_conf->remove_conf_settings('BRAND_HIDE_HEADER_MENUS');
$issabelpbx_conf->remove_conf_settings('AMPADMINLOGO');
$issabelpbx_conf->remove_conf_settings('BRAND_IMAGE_HIDE_NAV_BACKGROUND');
$issabelpbx_conf->remove_conf_settings('BRAND_IMAGE_SHADOW_SIDE_BACKGROUND');
$issabelpbx_conf->remove_conf_settings('BRAND_IMAGE_ISSABELPBX_RIGHT');
$issabelpbx_conf->remove_conf_settings('BRAND_IMAGE_RELOAD_LOADING');

//commit all settings
$issabelpbx_conf->commit_conf_settings();
out(_("ok"));

/* Check and add if necessary the writetimeout setting to the manager configuration
 */

// Read in manager.conf and strip out any #includes to avoid warnings
//
$orig_manager = file($amp_conf['ASTETCDIR'] . '/manager.conf');
if (is_array($orig_manager) && !empty($orig_manager)) {
	$manager = array();
	foreach ($orig_manager as $l) {
		$tl = trim($l);
		if ($tl[0] != '#') {
			$manager[] = $l;
		}
	}

	// check if we already have writetimeout by parsing as an ini file
	//
	$manager_ini = parse_ini_string(implode("", $manager), true);
	unset($manager);
	if (!isset($manager_ini[$amp_conf['AMPMGRUSER']]['writetimeout'])) {
		out(_("writetimeout not present, adding"));

		// add the setting right after the section heading
		//
		foreach ($orig_manager as $l) {
			$new_manager[] = $l;
			if (trim($l) == '[' . $amp_conf['AMPMGRUSER'] . ']') {
				$new_manager[] = 'writetimeout = ' . $amp_conf['ASTMGRWRITETIMEOUT'] . "\n";
			}
		}
		if (file_put_contents($amp_conf['ASTETCDIR'] . '/manager.conf', $new_manager)) {
			out(_("writetimeout added to manager.conf"));
		} else {
			out(_("an error occurred trying to write out manager.conf changes"));
		}
		unset($new_manager);
	} else {
		out(_("writetimeout already exists"));
	}
	unset($manager_ini);
} else {
	out(_("Failed to read manager file to add writetimeout"));
}
unset($orig_manager);

