<?php


// Return Array() of 'enabled' features for a specific module
function featurecodes_getModuleFeatures($modulename) {
	$s = "SELECT featurename, description ";
	$s .= "FROM featurecodes ";
	$s .= "WHERE modulename = ".sql_formattext($modulename)." AND enabled = 1 ";

	$results = sql($s, "getAll", DB_FETCHMODE_ASSOC);

	if (is_array($results)) {
		return $results;
	} else {
		return null;
		
	}
}

function featurecodes_getAllFeaturesDetailed($sort_module=true) {
	global $amp_conf;

	$fd = $amp_conf['ASTETCDIR'].'/issabelpbx_featurecodes.conf';
	$overridecodes = array();
	if (file_exists($fd)) {
		$overridecodes = parse_ini_file($fd,true);
	}
	$s = "SELECT featurecodes.modulename, featurecodes.featurename, featurecodes.description AS featuredescription, featurecodes.enabled AS featureenabled, featurecodes.defaultcode, featurecodes.customcode, ";
	$s .= "modules.enabled AS moduleenabled, featurecodes.providedest ";
	$s .= "FROM featurecodes ";
	$s .= "INNER JOIN modules ON modules.modulename = featurecodes.modulename ";
	$s .= ($sort_module ? "ORDER BY featurecodes.modulename, featurecodes.description " : "ORDER BY featurecodes.description ");
	
	$results = sql($s, "getAll", DB_FETCHMODE_ASSOC);
	if (is_array($results)) {
		$modules = module_getinfo(false, MODULE_STATUS_ENABLED);
		foreach ($results as $key => $item) {

			// get the module display name
			$results[$key]['moduledescription'] = (!empty($modules[ $item['modulename'] ]['name']) ? $modules[ $item['modulename'] ]['name'] : ucfirst($item['modulename']));
			if (isset($overridecodes[$item['modulename']][$item['featurename']]) && trim($overridecodes[$item['modulename']][$item['featurename']]) != '') {
				$results[$key]['defaultcode'] = $overridecodes[$item['modulename']][$item['featurename']];
			}
		}
		
		return $results;
	} else {
		return null;
	}
}

// removes all features for a specific module
function featurecodes_delModuleFeatures($modulename) {
       $s = "DELETE ";
       $s .= "FROM featurecodes ";
       $s .= "WHERE modulename = ".sql_formattext($modulename);

       sql($s, 'query');

       return true;
}

function featurecodes_getFeatureCode($modulename, $featurename) {
	$fc_code = '';
	
	$fcc = new featurecode($modulename, $featurename);
	$fc_code = $fcc->getCodeActive();
	unset($fcc);

	return $fc_code != '' ? $fc_code : _('** MISSING FEATURE CODE **');
}

function featurecodes_delFeatureCode($modulename, $featurename) {
       $s = "DELETE ";
       $s .= "FROM featurecodes ";
       $s .= "WHERE modulename = ".sql_formattext($modulename)." ";
       $s .= "AND featurename = ".sql_formattext($featurename);

       sql($s, 'query');

       return true;
}

?>