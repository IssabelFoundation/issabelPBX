<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
// a class for generating passwdfile
class pinsets_conf {
	// return an array of filenames to write
	// files named like pinset_N
	var $_pinsets = array();

	private static $obj;

	// IssabelPBX magic ::create() call
	public static function create() {
		if (!isset(self::$obj))
			self::$obj = new pinsets_conf();

		return self::$obj;
	}

	public function __construct() {
		self::$obj = $this;
	}

	function get_filename() {
		$files = array();
		foreach (array_keys($this->_pinsets) as $pinset) {
			$files[] = 'pinset_'.$pinset;
		}
		return $files;
	}

	function addPinsets($setid, $pins) {
		$this->_pinsets[$setid] = $pins;
	}

	// return the output that goes in each of the files
	function generateConf($file) {
		$setid = ltrim($file,'pinset_');
		$output = $this->_pinsets[$setid];
		return $output;
	}
}

/* 	Generates passwd files for pinsets
	We call this with retrieve_conf
 */
function pinsets_get_config($engine) {
	global $ext;  // is this the best way to pass this?
	global $asterisk_conf;

	$pinsets_conf = pinsets_conf::create();

	$allpinsets = pinsets_list(true);
	if(is_array($allpinsets)) {
		foreach($allpinsets as $item) {
			// write our own pin list files
			$pinsets_conf->addPinsets($item['pinsets_id'],$item['passwords']);
		}

		// write out a macro that handles the authenticate
		$ext->add('macro-pinsets', 's', '', new ext_gotoif('${ARG2} = 1','cdr,1'));
		$ext->add('macro-pinsets', 's', '', new ext_execif('$["${DB(AMPUSER/${AMPUSER}/pinless)}" != "NOPASSWD"]', 'Authenticate',$asterisk_conf['astetcdir'].'/pinset_${ARG1}'));
		$ext->add('macro-pinsets', 's', '', new ext_execif('$["${DB(AMPUSER/${AMPUSER}/pinless)}" != "NOPASSWD"]', 'ResetCDR'));
		// authenticate with the CDR option (a)
		$ext->add('macro-pinsets', 'cdr', '', new ext_execif('$["${DB(AMPUSER/${AMPUSER}/pinless)}" != "NOPASSWD"]', 'Authenticate',$asterisk_conf['astetcdir'].'/pinset_${ARG1},a'));
		$ext->add('macro-pinsets', 'cdr', '', new ext_execif('$["${DB(AMPUSER/${AMPUSER}/pinless)}" != "NOPASSWD"]', 'ResetCDR'));
	}

	$usage_list = pinsets_list_usage('routing');
	if (is_array($usage_list) && count($usage_list)) {
		$pinsets = pinsets_list(true);
		$addtocdr = array();
		foreach ($pinsets as $pinset) {
			$addtocdr[$pinset['pinsets_id']] = $pinset['addtocdr'];
		}
		foreach ($usage_list as $thisroute) {
			$context = 'outrt-'.$thisroute['foreign_id'];
			$patterns = core_routing_getroutepatternsbyid($thisroute['foreign_id']);
			foreach ($patterns as $pattern) {
				$fpattern = core_routing_formatpattern($pattern);
				$exten = $fpattern['dial_pattern'];
				$ext->splice($context, $exten, 1, new ext_macro('pinsets', $thisroute['pinsets_id'].','.$addtocdr[$thisroute['pinsets_id']]), 'pinsets');
			}
		}
	}
}

function pinsets_list_usage($dispname=true) {
  $sql = 'SELECT * FROM `pinset_usage`';
  if ($dispname !== true) {
    $sql .= " WHERE `dispname` = '$dispname'";
  }
  return sql($sql,'getAll',DB_FETCHMODE_ASSOC);
}

//get the existing meetme extensions
function pinsets_list($getAll=false) {
	$results = sql("SELECT * FROM pinsets","getAll",DB_FETCHMODE_ASSOC);
	if(is_array($results)){
		foreach($results as $result){
			// check to see if we have a dept match for the current AMP User.
			if ($getAll || checkDept($result['deptname'])){
				// return this item's dialplan destination, and the description
				$allowed[] = $result;
			}
		}
	}
	if (isset($allowed)) {
		return $allowed;
	} else {
		return null;
	}
}

function pinsets_get($id){
	$results = sql("SELECT * FROM pinsets WHERE pinsets_id = '$id'","getRow",DB_FETCHMODE_ASSOC);
	return $results;
}

function pinsets_del($id){
	global $amp_conf;

	$filename = $amp_conf['ASTETCDIR'].'/pinset_'.$id;
	if (file_exists($filename)) {
		unlink($filename);
	}
	$results = sql("DELETE FROM pinsets WHERE pinsets_id = '$id'","query");
	$results = sql("DELETE FROM pinset_usage WHERE pinsets_id = '$id'","query");
}

function pinsets_add($post){
	if(!pinsets_chk($post))
		return false;
	extract($post);
	$passwords = pinsets_clean($passwords);
	if(empty($description)) $description = _('Unnamed');
	$results = sql("INSERT INTO pinsets (description,passwords,addtocdr,deptname) values (\"$description\",\"$passwords\",\"$addtocdr\",\"$deptname\")");
}

function pinsets_edit($id,$post){
	if(!pinsets_chk($post))
		return false;
	extract($post);
	$passwords = pinsets_clean($passwords);
	if(empty($description)) $description = _('Unnamed');
	$results = sql("UPDATE pinsets SET description = \"$description\", passwords = \"$passwords\", addtocdr = \"$addtocdr\", deptname = \"$deptname\" WHERE pinsets_id = \"$id\"");
}

// clean and remove duplicates
function pinsets_clean($passwords) {

	$passwords = explode("\n",$passwords);

	if (!$passwords) {
		$passwords = null;
	}

	foreach (array_keys($passwords) as $key) {
		//trim it
		$passwords[$key] = trim($passwords[$key]);

		// remove invalid chars
		$passwords[$key] = preg_replace("/[^0-9#*]/", "", $passwords[$key]);

		// remove blanks
		if ($passwords[$key] == "") unset($passwords[$key]);
	}

	// check for duplicates, and re-sequence
	$passwords = array_values(array_unique($passwords));

	if (is_array($passwords))
		return implode($passwords,"\n");
	else
		return "";
}

// ensures post vars is valid
function pinsets_chk($post){
	return true;
}

//removes a pinset from a route and shifts priority for all outbound routing pinsets
function pinsets_adjustroute($route_id,$action,$routepinset='') {
  global $db;
  $dispname = 'routing';
  $route_id = $db->escapeSimple($route_id);
  $routepinset = $db->escapeSimple($routepinset);

  switch ($action) {
  case 'delroute':
    sql('DELETE FROM pinset_usage WHERE foreign_id ='.q($route_id)." AND dispname = '$dispname'");
    break;
  case 'addroute';
    if ($routepinset != '') {
      // we don't have the route_id yet, it hasn't been inserted yet :(, put it in the session
      // and when returned it will be available on the redirect_standard
      $_SESSION['pinsetsAddRoute'] = $routepinset;
    }
    break;
  case 'delayed_insert_route';
    if ($routepinset != '') {
		sql("INSERT INTO pinset_usage (pinsets_id, dispname, foreign_id) VALUES ($routepinset, '$dispname', '$route_id')");
    }
    break;
  case 'editroute';
    if ($routepinset != '') {
      sql("REPLACE INTO pinset_usage (pinsets_id, dispname, foreign_id) VALUES ($routepinset, '$dispname', '$route_id')");
    } else {
      sql('DELETE FROM pinset_usage WHERE foreign_id ='.q($route_id)." AND dispname = '$dispname'");
    }
    break;
  }
}

// provide hook for routing
function pinsets_hook_core($viewing_itemid, $target_menuid) {
  global $db;

	switch ($target_menuid) {
		case 'routing':
			//create a selection of available pinsets
			$pinsets = pinsets_list();
      if ($viewing_itemid == '') {
        $selected_pinset = '';
      } else {
        // if this is set, we just added it so get it out of the session
        if (isset($_SESSION['pinsetsAddRoute']) && $_SESSION['pinsetsAddRoute'] != '') {
          $selected_pinset = $_SESSION['pinsetsAddRoute'];
        } else {
          $selected_pinset = $db->getOne("SELECT pinsets_id FROM pinset_usage WHERE dispname='routing' AND foreign_id='".$db->escapeSimple($viewing_itemid)."'");
          if(DB::IsError($selected_pinset)) {
            die_issabelpbx($selected_pinset->getMessage());
          }
        }
      }

			$hookhtml = '
        <tr>
          <td><a href="#" class="info">'._("PIN Set").'<span>'._('Optional: Select a PIN set to use. If using this option, leave the Route Password field blank.').'</span></a>:</td>
          <td>
            <select name="pinsets">
              <option value="">'._('None').'</option>
      ';
      if (is_array($pinsets)) {
        foreach($pinsets as $item) {
          $selected = $selected_pinset == $item['pinsets_id'] ? 'selected' : '';
          $hookhtml .= "<option value={$item['pinsets_id']} ".$selected.">{$item['description']}</option>";
        }
      }
      $hookhtml .= '
						</select>
					</td>
				</tr>
			';
      return $hookhtml;
    break;
    default:
      return false;
    break;
  }
}

function pinsets_hookProcess_core($viewing_itemid, $request) {

	// Record any hook selections made by target modules
	// We'll add these to the pinset's "used_by" column in the format <targetmodule>_<viewing_itemid>
	// multiple targets could select a single pinset, so we'll comma delimiter them

	// this is really a crappy way to store things.
	// Any module that is hooked by pinsets when submitted will result in all the "used_by" fields being re-written
	switch ($request['display']) {
  case 'routing':
    $action = (isset($request['action']))?$request['action']:null;
    $route_id = $viewing_itemid;
    if (isset($request['Submit']) ) {
      $action = (isset($action))?$action:'editroute';
    }

    // $action won't be set on the redirect but pinsetsAddRoute will be in the session
    //
    if (!$action && isset($_SESSION['pinsetsAddRoute']) && $_SESSION['pinsetsAddRoute'] != '') {
      pinsets_adjustroute($route_id,'delayed_insert_route',$_SESSION['pinsetsAddRoute']);
      unset($_SESSION['pinsetsAddRoute']);
    } elseif ($action){
      pinsets_adjustroute($route_id,$action,$request['pinsets']);
    }
    break;
	}
}
?>
