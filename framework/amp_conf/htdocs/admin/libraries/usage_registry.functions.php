<?php

/** check if a specific extension is being used, or get a list of all extensions that are being used
 * @param mixed     an array of extension numbers to check against, or if boolean true then return list of all extensions
 * @param array     a hash of module names to search for callbacks, otherwise global $active_modules is used
 * @return array    returns an empty array if exten not in use, or any array with usage info, or of all usage 
 *                  if exten is boolean true
 * @description     Upon passing in an array of extension numbers, this api will query all modules to determine if any
 *                  are using those extension numbers. If so, it will return an array with the usage information
 *                  as described below, otherwise an empty array. If passed boolean true, it will return an array
 *                  of the same format with all extensions on the system that are being used.
 *
 *                  $exten_usage[$module][$exten]['description'] // description of the extension
 *                                               ['edit_url']    // a url that could be invoked to edit extension
 *                                               ['status']      // Status: INUSE, RESERVED, RESTRICTED
 */
function framework_check_extension_usage($exten=true, $module_hash=false, $report_conflicts=true) {
	global $active_modules;
	$exten_usage = array();

	if (!is_array($module_hash)) {
		$module_hash = $active_modules;
	}

	if (!is_array($exten) && $exten !== true) {
		$exten = array($exten);
	}

	foreach(array_keys($module_hash) as $mod) {
		$function = $mod."_check_extensions";
		if (function_exists($function)) {
			modgettext::push_textdomain($mod);
			$module_usage = $function($exten);
			if (!empty($module_usage)) {
				$exten_usage[$mod] = $module_usage;
			}
			modgettext::pop_textdomain();
		}
	}
	if ($exten === true) {
		return $exten_usage;
	} else {
    $exten_matches = array();
		foreach (array_keys($exten_usage) as $mod) {
			foreach ($exten as $test_exten) {
				if (isset($exten_usage[$mod][$test_exten])) {
					$exten_matches[$mod][$test_exten] = $exten_usage[$mod][$test_exten];
				}
			}
		}
	}
	if (!empty($exten_matches) && $report_conflicts) {
		fwmsg::set_error(_("Extension Numbering Duplicate Conflict Detected"));
	}
	return $exten_matches;
}

/** returns a hash of all extensions used on the system
 * @param boolean   Set to true if json should be returned, defaults to false
 * @return mixed    returns a hash of all extensions on system as array or json encoded
 * @description     returns a full extension map where the index is the extension number and the
 *                  value is what extension is using it. If there are duplicates defined, it will
 *                  only show one of the extensions as duplicates is an unacceptable error condition
 */
function framework_get_extmap($json=false) {
	global $amp_conf;
	static $extmap = array();
	$extmap_serialized = '';

	// If aggresive mode, we get it each time
	//
	if (!$amp_conf['AGGRESSIVE_DUPLICATE_CHECK']) {
  	$extmap_serialized = sql("SELECT `data` FROM `module_xml` WHERE `id` = 'extmap_serialized'","getOne");
	}
	// Now make sure there was something there
	//
	if ($extmap_serialized) {
		$extmap = unserialize($extmap_serialized);
	}
	// At this point in aggresive mode we haven't gotten it, if not aggressive but
	// not found in the DB then we still don't have it so try again.
	//
	if (!empty($extmap)) {
		return $json ? json_encode($extmap) : $extmap;
	} else {
		$full_list = framework_check_extension_usage(true);
		foreach ($full_list as $module => $entries) {
			foreach ($entries as $exten => $stuff) {
				$extmap[$exten] = $stuff['description'];
			}
		}
		return $json ? json_encode($extmap) : $extmap;
	}
}

/** creates the extmap and puts it into the db
 * @description     this calculates the extension map and stores it into the database, primarily
 *                  used by retrieve_conf
 */
function framework_set_extmap() {
	global $db;
	$full_list = framework_check_extension_usage(true);
	foreach ($full_list as $module => $entries) {
		foreach ($entries as $exten => $stuff) {
			$extmap[$exten] = $stuff['description'];
		}
	}
  $extmap_serialized = $db->escapeSimple(serialize($extmap));
  sql("REPLACE INTO `module_xml` (`id`, `time`, `data`) VALUES ('extmap_serialized', '".time()."','".$extmap_serialized."')");
}

/** check if a specific destination is being used, or get a list of all destinations that are being used
 * @param mixed     an array of destinations to check against, or if boolean true then return list of all destinations in use
 * @param array     a hash of module names to search for callbacks, otherwise global $active_modules is used
 * @return array    returns an empty array if destination not in use, or any array with usage info, or of all usage 
 *                  if dest is boolean true
 * @description     Upon passing in an array of destinations, this api will query all modules to determine if any
 *                  are using that destination. If so, it will return an array with the usage information
 *                  as described below, otherwise an empty array. If passed boolean true, it will return an array
 *                  of the same format with all destinations on the system that are being used.
 *
 *                  $dest_usage[$module][]['dest']        // The destination being used
 *                                        ['description'] // Description of who is using it
 *                                        ['edit_url']    // a url that could be invoked to edit the using entity
 *                                               
 */
function framework_check_destination_usage($dest=true, $module_hash=false) {
	global $active_modules;

	$dest_usage = array();
	$dest_matches = array();

	if (!is_array($module_hash)) {
		$module_hash = $active_modules;
	}

	if (!is_array($dest) && $dest !== true) {
		$dest = array($dest);
	}

	foreach(array_keys($module_hash) as $mod) {
		$function = $mod."_check_destinations";
		if (function_exists($function)) {
			modgettext::push_textdomain($mod);
			$module_usage = $function($dest);
			if (!empty($module_usage)) {
				$dest_usage[$mod] = $module_usage;
			}
			modgettext::pop_textdomain();
		}
	}
	if ($dest === true) {
		return $dest_usage;
	} else {
		/*
		$destlist[] = array(
			'dest' => $thisdest,
			'description' => 'Annoucement: '.$result['description'],
			'edit_url' => 'config.php?display=announcement&type='.$type.'&extdisplay='.urlencode($thisid),
		);
		*/
		foreach (array_keys($dest_usage) as $mod) {
			foreach ($dest as $test_dest) {
				foreach ($dest_usage[$mod] as $dest_item) {
					if ($dest_item['dest'] == $test_dest) {
						$dest_matches[$mod][] = $dest_item;
					}
				}
			}
		}
	}
	return $dest_matches;
}

/** provide optional alert() box and formatted url info for extension conflicts
 * @param array     an array of extensions that are in conflict obtained from framework_check_extension_usage
 * @param boolean   default false. True if url and descriptions should be split, false to combine (see return)
 * @param boolean   default true. True to echo an alert() box, false to bypass the alert box
 * @return array    returns an array of formatted URLs with descriptions. If $split is true, retuns an array
 *                  of the URLs with each element an array in the format of array('label' => 'description, 'url' => 'a url')
 * @description     This is used upon detecting conflicting extension numbers to provide an optional alert box of the issue
 *                  by a module which should abort the attempt to create the extension. It also returns an array of
 *                  URLs that can be displayed by the module to show the conflicting extension(s) and links to edit
 *                  them or further interogate. The resulting URLs are returned in an array either formatted for immediate
 *                  display or split into a description and the raw URL to provide more fine grained control (or use with guielements).
 */
function framework_display_extension_usage_alert($usage_arr=array(),$split=false,$alert=true) {
	$url = array();
	if (!empty($usage_arr)) {
		$conflicts=0;
		foreach($usage_arr as $rawmodule => $properties) {
			foreach($properties as $exten => $details) {
				$conflicts++;
				if ($conflicts == 1) {
					switch ($details['status']) {
						case 'INUSE':
							$str = "Extension $exten not available, it is currently used by ".htmlspecialchars($details['description']).".";
							if ($split) {
								$url[] =  array('label' => "Edit: ".htmlspecialchars($details['description']),
								                 'url'  =>  $details['edit_url'],
								               );
							} else {
								$url[] =  "<a href='".$details['edit_url']."'>Edit: ".htmlspecialchars($details['description'])."</a>";
							}
							break;
						default:
						$str = "This extension is not available: ".htmlspecialchars($details['description']).".";
					}
				} else {
					if ($split) {
						$url[] =  array('label' => "Edit: ".htmlspecialchars($details['description']),
						                 'url'  =>  $details['edit_url'],
													 );
					} else {
						$url[] =  "<a href='".$details['edit_url']."'>Edit: ".htmlspecialchars($details['description'])."</a>";
					}
				}
			}
		}
		if ($conflicts > 1) {
			$str .= sprintf(" There are %s additonal conflicts not listed",$conflicts-1);
		}
	}
	if ($alert) {
		echo "<script>javascript:alert('$str')</script>";
	}
	return($url);
}

/** check if a specific destination is being used, or get a list of all destinations that are being used
 * @param mixed     an array of destinations to check against
 * @param array     a hash of module names to search for callbacks, otherwise global $active_modules is used
 * @return array    array with a message and tooltip to display usage of this destination
 * @description     This is called to generate a label and tooltip which summarized the usage of this
 *                  destination and a tooltip listing all the places that use it
 *
 */
function framework_display_destination_usage($dest, $module_hash=false) {

	if (!is_array($dest)) {
		$dest = array($dest);
	}
	$usage_list = framework_check_destination_usage($dest, $module_hash);
	if (!empty($usage_list)) {
		$usage_count = 0;
		$str = null;
		foreach ($usage_list as $mod_list) {
			foreach ($mod_list as $details) {
				$usage_count++;
				$str .= $details['description']."<br />";
			}
		}
		$object = $usage_count > 1 ? _("Objects"):_("Object");
		return array('text' => '&nbsp;'.sprintf(dgettext('amp',"Used as Destination by %s %s"),$usage_count, dgettext('amp',$object)),
		             'tooltip' => $str,
							 	);
	} else {
		return array();
	}
}

/** determines which module a list of destinations belongs to, and if the destination object exists
 * @param mixed     an array of destinations to check against
 * @param array     a hash of module names to search for callbacks, otherwise global $active_modules is used
 * @return array    an array structure with informaiton about the destinations (see code)
 * @description     Mainly used by framework_list_problem_destinations. This function will find the module
 *                  that a destination belongs to and determine if the object still exits. This allow it to
 *                  either obtain the identify, identify it as an object that has been deleted, or identify
 *                  it as an unknown destination, usually a custom destination.
 *
 */
function framework_identify_destinations($dest, $module_hash=false) {
	global $active_modules;
	static $dest_cache = array();

	$dest_results = array();

	$dest_usage = array();
	$dest_matches = array();

	if (!is_array($module_hash)) {
		$module_hash = $active_modules;
	}

	if (!is_array($dest)) {
		$dest = array($dest);
	}

	foreach ($dest as $target) {
		if (isset($dest_cache[$target])) {
			$dest_results[$target] = $dest_cache[$target];
		} else {
			$found_owner = false;
			foreach(array_keys($module_hash) as $mod) {
				$function = $mod."_getdestinfo";
				if (function_exists($function)) {
					modgettext::push_textdomain($mod);
					$check_module = $function($target);
					modgettext::pop_textdomain();
					if ($check_module !== false) {
						$found_owner = true;
						$dest_cache[$target] = array($mod => $check_module);
						$dest_results[$target] = $dest_cache[$target];
						break;
					}
				}
			}
			if (! $found_owner) {
				//echo "Not Found: $target\n";
				$dest_cache[$target] = false;
				$dest_results[$target] = $dest_cache[$target];
			}
		}
	}
	return $dest_results;
}

/** create a comprehensive list of all destinations that are problematic
 * @param array     an array of destinations to check against
 * @param bool      set to true if custome (unknown) destinations should be reported
 * @return array    an array of the destinations that are empty, orphaned or custom
 * @description     This function will scan the entire system and identify destinations
 *                  that are problematic. Either empty, orphaned or an unknow custom
 *                  destinations. An orphaned destination is one that should belong
 *                  to a module but the object it would have pointed to does not exist
 *                  because it was probably deleted.
 */
function framework_list_problem_destinations($module_hash=false, $ignore_custom=false) {
	global $active_modules;

	if (!is_array($module_hash)) {
		$module_hash = $active_modules;
	}

	$my_dest_arr = array();
	$problem_dests = array();

	$all_dests = framework_check_destination_usage(true, $module_hash);

	foreach ($all_dests as $dests) {
		foreach ($dests as $adest) {
			if (!empty($adest['dest'])) {
				$my_dest_arr[] = $adest['dest'];
			}
		}
	}
	$my_dest_arr = array_unique($my_dest_arr);

	$identities = framework_identify_destinations($my_dest_arr, $module_hash);

	foreach ($all_dests as $dests) {
		foreach ($dests as $adest) {
			if (empty($adest['dest'])) {
				$problem_dests[] = array('status' => 'EMPTY', 
					                       'dest' => $adest['dest'],
					                       'description' => $adest['description'],
					                       'edit_url' => $adest['edit_url'],
															  );
			} else if ($identities[$adest['dest']] === false){
				if ($ignore_custom) {
					continue;
				}
				$problem_dests[] = array('status' => 'CUSTOM', 
					                       'dest' => $adest['dest'],
					                       'description' => $adest['description'],
					                       'edit_url' => $adest['edit_url'],
															  );
			} else if (is_array($identities[$adest['dest']])){
				foreach ($identities[$adest['dest']] as $details) {
					if (empty($details)) {
						$problem_dests[] = array('status' => 'ORPHAN', 
						                         'dest' => $adest['dest'],
						                         'description' => $adest['description'],
						                         'edit_url' => $adest['edit_url'],
						                        );

					}
					break; // there is only one set per array
				}
			} else {
				echo "ERROR?\n";
				var_dump($adest);
			}
		}
	}
	return $problem_dests;
}

/** sort the hash based on the inner key
 */
function _framework_sort_exten($a, $b) {
	$a_key = array_keys($a);
	$a_key = $a_key[0];
	$b_key = array_keys($b);
	$b_key = $b_key[0];
	if ($a_key == $b_key) {
		return 0;
	} else {
		return ($a_key < $b_key) ? -1 : 1;
	}
}

/** create a comprehensive list of all extensions conflicts
 * @return array    an array of the destinations that are empty, orphaned or custom
 * @description     This returns an array structure with information about all
 *                  extension numbers that are in conflict. This means the same number
 *                  is being used by 2 or more modules and the results will be ambiguous
 *                  which one will be ignored when dialed. See the code for the
 *                  structure of the retured array.
 */
function framework_list_extension_conflicts($module_hash=false) {
	global $active_modules;

	if (!is_array($module_hash)) {
		$module_hash = $active_modules;
	}

	$exten_list = framework_check_extension_usage(true,$module_hash);

	/** Bookkeeping hashes
 	*  full_hash[]     will contain the first extension encountered
 	*  conflict_hash[] will contain any subsequent extensions if conflicts
 	*
 	*  If there are conflicts, the full set is what is in conflict_hash + the
 	*  first extension encoutnered in full_hash[]
 	*/
	$full_hash = array();
	$conflict_hash = array();

	foreach ($exten_list as $mod => $mod_extens) {
		foreach ($mod_extens as $exten => $details) {
			if (!isset($full_hash[$exten])) {
				$full_hash[$exten] = $details;
			} else {
				$conflict_hash[] = array($exten => $details);
			}
		}
	}

	// extract conflicting remaining extension from full_hash but needs to be unique
	//
	if (!empty($conflict_hash)) {
		$other_conflicts = array();
		foreach ($conflict_hash as $item)  {
			foreach (array_keys($item) as $exten) {
				$other_conflicts[$exten] = $full_hash[$exten];
			}
		}
		foreach ($other_conflicts as $exten => $details) {
			$conflict_hash[] = array($exten => $details);
		}
		usort($conflict_hash, "_framework_sort_exten");
		return $conflict_hash;
	}
}

/** check if a specific destination is being used, or get a list of all destinations that are being used
 * @param string    the old destination that is being changed
 * @param string    the new destination that is replacing the old
 * @param array     a hash of module names to search for callbacks, otherwise global $active_modules is used
 * @return integer  returns the number of records that were updated
 * @description     has each module replace their destination information with another one, used if you are
 *                  assigning a new number to something such as a conference room that may be used as a destination
 *                                               
 */
function framework_change_destination($old_dest, $new_dest, $module_hash=false) {
	global $db, $active_modules;

	$old_dest = $db->escapeSimple($old_dest);
	$new_dest = $db->escapeSimple($new_dest);

	if (!is_array($module_hash)) {
		$module_hash = $active_modules;
	}

	$total_updated = 0;
	$mods = array_keys($module_hash);
	unset($mods[array_search('framework',$mods)]);

	foreach($mods as $mod) {
		$function = $mod."_change_destination";
		if (function_exists($function)) {
			$total_updated += $function($old_dest, $new_dest);
		}
	}
	return $total_update;
}

/**
 * Search through all active modules for a function that ends in $func.
 * Pass it $opts and return whatever is returned in to an array with the
 * retuning module name as the key
 * Takes:
 * @func variable	the function name that we are searching for. The module name
 * 					will be appened to this
 * @opts mixed		a variable or array that will be passed to the function being 
 * 					called , if its found
 *
 */
function mod_func_iterator($func, &$opts = '') {
	global $active_modules;
	$res = array();
	
	foreach ($active_modules as $active => $mod) {
		$funct = $mod['rawname'] . '_' . $func;
		if (function_exists($funct)) {
			$res[$mod['rawname']] = $funct($opts);
		}
	}
	
	return $res;
}

/**
 * returns a list of URLs that represent a conflict with the past in extension or null if none
 * @param string  extension number to check for conflicts
 * @return mixed  returns a string with one or more URLs to the conflicting extesion(s) or null
 */
function framework_get_conflict_url_helper($account) {

  $usage_arr = framework_check_extension_usage($account);
  if (!empty($usage_arr)) {
    $conflict_url = framework_display_extension_usage_alert($usage_arr, false, false);
    return implode('<br />',$conflict_url);
  } else {
    return null;
  }
}
?>
