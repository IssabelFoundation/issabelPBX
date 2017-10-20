<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

function featurecodeadmin_update($req) {
	foreach ($req as $key => $item) {
		// Split up...
		// 0 - action
		// 1 - modulename
		// 2 - featurename
		$arr = explode("#", $key);
		if (count($arr) == 3) {
			$action = $arr[0];
			$modulename = $arr[1];
			$featurename = $arr[2];
			$fieldvalue = $item;
			
			// Is there a more efficient way of doing this?
			switch ($action)
			{
				case "ena":
					$fcc = new featurecode($modulename, $featurename);
					if ($fieldvalue == 1) {
						$fcc->setEnabled(true);
					} else {
						$fcc->setEnabled(false);
					}
					$fcc->update();
					break;
				case "custom":
					$fcc = new featurecode($modulename, $featurename);
					if ($fieldvalue == $fcc->getDefault()) {
						$fcc->setCode(''); // using default
					} else {
						$fcc->setCode($fieldvalue);
					}
					$fcc->update();
					break;
			}
		}
	}

	needreload();
}

function featurecodeadmin_check_extensions($exten=true) {
	$extenlist = array();
	if (is_array($exten) && empty($exten)) {
		return $extenlist;
	}
	$featurecodes = featurecodes_getAllFeaturesDetailed();

	foreach ($featurecodes as $result) {
		$thisexten = ($result['customcode'] != '')?$result['customcode']:$result['defaultcode'];

		// Ignore disabled codes, and modules, and any exten not being requested unless all (true)
		//
		if (($result['featureenabled'] == 1) && ($result['moduleenabled'] == 1) && ($exten === true || in_array($thisexten, $exten))) {
			$extenlist[$thisexten]['description'] = _("Featurecode: ").$result['featurename']." (".$result['modulename'].":".$result['featuredescription'].")";
			$extenlist[$thisexten]['status'] = 'INUSE';
			$extenlist[$thisexten]['edit_url'] = 'config.php?type=setup&display=featurecodeadmin';
		}
	}
	return $extenlist;
}


function featurecodeadmin_get_config($engine) {
	global $ext;  // is this the best way to pass this?

  switch($engine) {
    case "asterisk":

      $featurecodes = featurecodes_getAllFeaturesDetailed();

      $contextname = 'ext-featurecodes';
      foreach ($featurecodes as $result) {
        // Ignore disabled codes, and modules, and ones not providing destinations
        //
        if ($result['featureenabled'] == 1 && $result['moduleenabled'] == 1 && $result['providedest'] == 1) {
          $thisexten = ($result['customcode'] != '')?$result['customcode']:$result['defaultcode'];
          $ext->add($contextname, $result['defaultcode'], '', new ext_goto('1',$thisexten,'from-internal'));
        }
      }
    break;
  }
}

function featurecodeadmin_getdest($exten) {
	return array("ext-featurecodes,$exten,1");
}

function featurecodeadmin_getdestinfo($dest) {
	if (substr(trim($dest),0,17) == 'ext-featurecodes,') {
		$fcs = featurecodes_getAllFeaturesDetailed();
		$found = false;
		$dest = explode(',',$dest);
		foreach ($fcs as $fc) {
			if ($fc['defaultcode'] == $dest[1]) {
				$desc = $fc['featuredescription'];
				$found = true;
				break;
			}
		}
		if (!$found) {
			return array();
		} else {
			return array(
				'description' => $desc,
				'edit_url' => 'config.php?display=featurecodeadmin',
				);
		}
	} else {
		return false;
	}
}

function featurecodeadmin_check_destinations($dest=true) {
	global $active_modules;

	$fcs = featurecodeadmin_destinations();

	$destlist = array();
	if (is_array($dest) && empty($dest)) {
		return $destlist;
	}

	$results = array();
	if ($dest === true) {
		$results = $fcs;
	} else {
		foreach ($fcs as $fc) {
			if (in_array($fc['destination'], $dest)) {
				$results[] = $fc;
			}
		}
	}

	foreach ($results as $result) {
		$destlist[] = array(
			'dest' => $result['destination'],
			'description' => $result['description'],
			'edit_url' => 'config.php?display=featurecodeadmin',
		);
	}
	return $destlist;
}

function featurecodeadmin_destinations() {

  $featurecodes = featurecodes_getAllFeaturesDetailed();
	if (isset($featurecodes)) {
    $text_domain = Array();
    foreach ($featurecodes as $result) {
      // Ignore disabled codes, and modules, and ones not providing destinations
      //
      if ($result['featureenabled'] == 1 && $result['moduleenabled'] == 1 && $result['providedest'] == 1) {
        $modulename = $result['modulename'];

				$description = modgettext::_($result['featuredescription'], $modulename);
				// Just in case the translation was not found in either the module or amp, we will try to see
				// if they put it in the featurecode module i18n
        if ($description == $result['featuredescription']) {
            $description = _($description);
        }
        $thisexten = ($result['customcode'] != '')?$result['customcode']:$result['defaultcode'];
				$extens[] = array('destination' => 'ext-featurecodes,'.$result['defaultcode'].',1', 'description' => $description.' <'.$thisexten.'>');
      }
    }
  }
  if (isset($extens)) 
    return $extens;
  else
    return null;
}
?>
