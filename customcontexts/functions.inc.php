<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
/* $Id:$ */

//used to get module information
function customcontexts_getmodulevalue($id) {
static $moduledisplayname = null;
static $moduleversion = null;
global $db;
	switch ($id) {
	  case 'moduledisplayname':
      if ($moduledisplayname === null) {
	      $module_info = module_getinfo('customcontexts');
	      $moduledisplayname = $module_info['customcontexts']['name'];
      }
      return $moduledisplayname;
	    break;
	  case 'moduleversion':
      if ($moduleversion === null) {
        $moduleversion = modules_getversion('customcontexts');
      }
      return $moduleversion;
	    break;
	  default:
		$sql = "select value from customcontexts_module where id = '$id'";
		$results = $db->getAll($sql);
		if(DB::IsError($results)) {
			$results = null;
		}
		return isset($results)?$results[0][0]:null;
	}
}

//used to get module information
function customcontexts_setmodulevalue($id,$value) {
	global $db;
	$sql = "update customcontexts_module set value = '$value' where id = '$id'";
	$db->query($sql);
}

//after dialplan is created and ready for injection, we grab the includes of any context the user added in admin
function customcontexts_hookGet_config($engine) {
	global $db;
	global $ext;
	switch($engine) {
		case 'asterisk':
			$sql = 'UPDATE customcontexts_includes_list SET missing = 1 WHERE context 
							NOT IN (SELECT context FROM customcontexts_contexts_list WHERE locked = 1)';
			$db->query($sql);
			$sql = 'SELECT context FROM customcontexts_contexts_list';
			$sections = $db->getAll($sql);
			if(DB::IsError($results)) {
 				$results = null;
			}
			foreach ($sections as $section) {
				$section = $section[0];
				$i = 0;
				if (isset($ext->_includes[$section])) {
					foreach ($ext->_includes[$section] as $include) {
						$i = $i + 1;
						if ($section == 'outbound-allroutes') {
							$sql = 'INSERT INTO customcontexts_includes_list 
											(context, include, description, missing, sort)
											VALUES ("'.$section.'", "'.$include['include'].'",
											"'.$include['comment'].'", "0", "'.($i+100).'") 
											ON DUPLICATE KEY UPDATE sort = "'.($i+100).'", missing = "0"';
							$db->query($sql);
						} else {
							$sql = 'UPDATE customcontexts_includes_list SET missing = "0", sort = "'.$i.'" 
											WHERE context = "'.$section.'" and include = "'.$include['include'].'"';
							$db->query($sql);
            }
						$sql = 'INSERT IGNORE INTO customcontexts_includes_list 
										(context, include, description, sort) 
										VALUES ("'.$section.'", "'.$include['include'].'", 
										"'.$include['include'].'", "'.$i.'")';
						$db->query($sql);
					}
				}
			}
			$sql = "delete from  customcontexts_includes_list where missing = 1";
			$db->query($sql);
		break;
	}
}

// provide hook for routing
function customcontexts_hook_core($viewing_itemid, $target_menuid) {
	switch ($target_menuid) {
		// only provide display for outbound routing
		case 'routing':
			/*$route = substr($viewing_itemid,4);$hookhtml = '';return $hookhtml;*/
			return '';
		break;
		default:
				return false;
		break;
	}
}

//this lists all includes from the sql database (for the requsted context) which we parsed out of the dialplan
//from any contexts the user entered in admin - information was saved to database on the last reload

function customcontexts_getincludeslist($context) {
	global $db;
//	$sql = "select include, description from customcontexts_includes_list where context = '".$context."' order by description";
	$sql = "SELECT include, customcontexts_includes_list.description, 
					COUNT(customcontexts_contexts_list.context) AS preemptcount  
					FROM customcontexts_includes_list 
					LEFT OUTER JOIN customcontexts_contexts_list 
					ON include = customcontexts_contexts_list.context 
					WHERE customcontexts_includes_list.context = '$context' 
					GROUP BY include, customcontexts_includes_list.description 
					ORDER BY customcontexts_includes_list.sort,
					customcontexts_includes_list.description";
	$results = $db->getAll($sql);
	if(DB::IsError($results)) {
		$results = null;
	}
	foreach ($results as $val) {
		$tmparray[] = array($val[0], $val[1], $val[2]);
	}
	return $tmparray;
}

//lists any contexts the user entered in admin for us to parse for includes to make available to his custom contexts
function customcontexts_getcontextslist() {
	global $db;
	$sql = "select context, description from customcontexts_contexts_list order by description";
	$results = $db->getAll($sql);
	if(DB::IsError($results)) {
		$results = null;
	}
	foreach ($results as $val) {
		$tmparray[] = array($val[0], $val[1]);
	}
	return $tmparray;
}

//these are the users selections of includes for the selected custom context
function customcontexts_getincludes($context) {
	global $db;
//	$sql = "select customcontexts_contexts_list.context, customcontexts_contexts_list.description as contextdescription, customcontexts_includes_list.include, customcontexts_includes_list.description, if(saved.include is null, 'no', if(saved.timegroupid is null, 'yes', saved.timegroupid)) as allow, saved.sort from customcontexts_contexts_list inner join customcontexts_includes_list on customcontexts_contexts_list.context = customcontexts_includes_list.context left outer join (select * from customcontexts_includes where context = '$context') saved on customcontexts_includes_list.include = saved.include order by customcontexts_contexts_list.description, customcontexts_includes_list.description";
	$sql = "SELECT customcontexts_contexts_list.context, 
					customcontexts_contexts_list.description AS contextdescription, 
					customcontexts_includes_list.include, 
					customcontexts_includes_list.description, 
					IF(saved.include is null, 'no', 
						IF(saved.timegroupid is null, IF(saved.userules is null, 'yes', saved.userules),
					saved.timegroupid)) AS allow, 
					IF(saved.sort is null,customcontexts_includes_list.sort,saved.sort) AS sort, 
					COUNT(preemptcheck.context) AS preemptcount FROM customcontexts_contexts_list 
					INNER JOIN customcontexts_includes_list 
					ON customcontexts_contexts_list.context = customcontexts_includes_list.context 
					LEFT OUTER JOIN (SELECT * from customcontexts_includes WHERE context = '$context')  AS saved 
					ON customcontexts_includes_list.include = saved.include 
					LEFT OUTER JOIN customcontexts_contexts_list preemptcheck 
					ON customcontexts_includes_list.include = preemptcheck.context 
					LEFT OUTER JOIN  outbound_route_sequence 
					ON REPLACE(customcontexts_includes_list.include,'outrt-','') = outbound_route_sequence.route_id
					GROUP BY customcontexts_contexts_list.context, 
					customcontexts_contexts_list.description, 
					customcontexts_includes_list.include, 
					customcontexts_includes_list.description, 
					IF(saved.include is null, 'no', 
						IF(saved.timegroupid is null, 'yes', saved.timegroupid)), 
					saved.sort,  
					customcontexts_contexts_list.description
					ORDER BY 
					IF(saved.sort is null,201,saved.sort), 
					customcontexts_includes_list.sort,
          outbound_route_sequence.seq,
					customcontexts_contexts_list.description, 
					customcontexts_includes_list.description";
	$results = sql($sql,'getAll');
	foreach ($results as $val){
		$tmparray[] = array($val[0], $val[1], $val[2], $val[3], $val[4], $val[5], $val[6]);
	}
	//0-context 1-contextdescription  2-include  3-description 4-allow 5-sort	6-preemptcount
	return $tmparray;
}

//these are the users custom contexts
function customcontexts_getcontexts() {
	global $db;
	$tmparray = array();
	$sql = "select context, description from customcontexts_contexts order by description";
	$results = $db->getAll($sql);
	if(DB::IsError($results)) {
		$results = array();
	}
	foreach ($results as $val) {
		$tmparray[] = array($val[0], $val[1]);
	}
	return $tmparray;
}

/* 
 * allow reload to get our config
 * we add all user custom contexts ad include his selected includes
 * also maybe allow the user to specify invalid destination
 */ 
function customcontexts_get_config($engine) {
  global $ext;
  switch($engine) {
    case 'asterisk':
	global $db;
	$sql = "SELECT context, dialrules, faildestination, featurefaildestination, 
					failpin, featurefailpin FROM customcontexts_contexts";
	$results = $db->getAll($sql);
	if(DB::IsError($results)) { 
		$results = null;
	}
	foreach ($results as $val) {
		$context = $val[0];
		//$ext->_exts[$context][] = null;
		//partially stolen from outbound routing
		$dialpattern = explode("\n",$val[1]);
		if (!$dialpattern) {
			$dialpattern = array();
		}
		foreach (array_keys($dialpattern) as $key) {
			//trim it
			$dialpattern[$key] = trim($dialpattern[$key]);
			
			// remove blanks
			if ($dialpattern[$key] == "") {
				unset($dialpattern[$key]);
			}
			
			// remove leading underscores (we do that on backend)
			if ($dialpattern[$key][0] == "_") {
				$dialpattern[$key] = substr($dialpattern[$key],1);
			}
		}
		// check for duplicates, and re-sequence
		$dialpattern = array_values(array_unique($dialpattern));
		if (is_array($dialpattern)) {
			foreach ($dialpattern as $pattern) {
				if (false !== ($pos = strpos($pattern,"|"))) {
					// we have a | meaning to not pass the digits on
					// (ie, 9|NXXXXXX should use the pattern _9NXXXXXX but only pass NXXXXXX, not the leading 9)
					
					$pattern = str_replace("|","",$pattern); // remove all |'s
					$exten = "EXTEN:".$pos; // chop off leading digit
				} else {
					// we pass the full dialed number as-is
					$exten = "EXTEN"; 
				}
				
				if (!preg_match("/^[0-9*]+$/",$pattern)) { 
					// note # is not here, as asterisk doesn't recoginize it as a normal digit, thus it requires _ pattern matching
					
					// it's not strictly digits, so it must have patterns, so prepend a _
					$pattern = "_".$pattern;
				}
				$ext->add($context,$pattern, '', new ext_goto('1','${'.$exten.'}',$context.'_rulematch')); 
			}
		}
		//switch to first line to deny all access when time group deleted
		//$sql = "select include, time from customcontexts_includes left outer join customcontexts_timegroups_detail on  customcontexts_includes.timegroupid = customcontexts_timegroups_detail.timegroupid where context = '".$context."' and (customcontexts_includes.timegroupid is null or customcontexts_timegroups_detail.timegroupid is not null) order by sort";
		$sql  = "SELECT include, time, userules, seq FROM customcontexts_includes 
						LEFT OUTER JOIN timegroups_details
						ON  customcontexts_includes.timegroupid = timegroups_details.timegroupid 
						LEFT OUTER JOIN  outbound_route_sequence 
						ON REPLACE(include,'outrt-','') = outbound_route_sequence.route_id
						WHERE context = '$context' ORDER BY sort, seq";
		$results2 = $db->getAll($sql);
		if(DB::IsError($results2)) {
			$results2 = null;
		}
		foreach ($results2 as $inc) {
			$time = isset($inc[1])?','.$inc[1]:'';
			$time = str_replace("|", ",", $time);
			switch ($inc[2]) {
				case 'allowmatch':
					if (is_array($dialpattern)) {
						$ext->addInclude($context.'_rulematch',$inc[0].$time);
					}
				break;
				case 'denymatch':
					$ext->addInclude($context,$inc[0].$time);
				break;
				default:
					$ext->addInclude($context,$inc[0].$time);
					if (is_array($dialpattern)) {
						$ext->addInclude($context.'_rulematch',$inc[0].$time);
					}
				break;
			}
		}
		//these go in funny "exten => s,1,Macro(hangupcall,)"
		//i'd rather use the base extension class to type it normally, but there is a bug in the class see ticket http://www.issabel.org/trac/ticket/1453
		$ext->add($context,'s', '', new ext_macro('hangupcall')); 
		$ext->add($context,'h', '', new ext_macro('hangupcall'));
		$ext->addInclude($context,$context.'_bad-number');
		$ext->addInclude($context,'bad-number');
		if (is_array($dialpattern)) {
			$ext->add($context.'_rulematch','s', '', new ext_macro('hangupcall')); 
			$ext->add($context.'_rulematch','h', '', new ext_macro('hangupcall'));
			$ext->addInclude($context.'_rulematch',$context.'_bad-number');
			$ext->addInclude($context.'_rulematch','bad-number');
		}
		$ext->_exts[$context.'_bad-number'][] = null;
		if (isset($val[2]) && (!$val[2] == '')) {
			$goto = explode(',',$val[2]);
			if (isset($val[4]) && ($val[4] <> '')) {
				$ext->add($context.'_bad-number', '_X.', '', new ext_authenticate($val[4]));
			}
			$ext->add($context.'_bad-number', '_X.', '', new ext_goto($goto[2],$goto[1],$goto[0]));
		}
		if (isset($val[3]) && (!$val[3] == '')) {
			$goto = explode(',',$val[3]);
			if (isset($val[5]) && ($val[5] <> '')) {
				$ext->add($context.'_bad-number', '_*.', '', new ext_authenticate($val[5]));
			}
			$ext->add($context.'_bad-number', '_*.', '', new ext_goto($goto[2],$goto[1],$goto[0]));
		}
	}
  break;
  }
}

// returns a associative arrays with keys 'destination' and 'description'
// it allows custom contexts to be chosen as destinations
//this may seem a bit strange, but it works simply it sends the user to the EXTEN he dialed (or IVR option) within the selected context
function customcontexts_destinations() {
	$contexts =  customcontexts_getcontexts();
	$extens[] = array('destination' => 'from-internal,${EXTEN},1', 'description' => 'Full Internal Access');
	if (is_array($contexts)) {
		foreach ($contexts as $r) {
			$extens[] = array('destination' => $r[0].',${EXTEN},1', 'description' => $r[1]);
		}
	}

	return $extens;
}

//brute force hoook to devices and extensions pages to inform the user that they can place these devices in their custom contexts
function customcontexts_configpageinit($dispnum) {

  global $currentcomponent;
  $extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
  if ($extdisplay == '') {
    return true;
  }

  if ($dispnum == 'devices' || $dispnum == 'extensions') {
    $device_info = core_devices_get($extdisplay);
    if (empty($device_info)) {
        return true;
    } else {
      $tech = $device_info['tech'];
      switch ($tech) {
        case 'iax2':
        case 'iax':
        case 'sip':
        case 'pjsip':
        case 'dahdi':
        case 'zap':
          $_REQUEST['tech'] = $tech;
          $_REQUEST['customcontext'] = $device_info['context'];
        break;
        default:
          return true;
      }
    }
  } else {
    return true;
  }

	$contextssel  = customcontexts_getcontexts();
	$currentcomponent->addoptlistitem('contextssel', 'from-internal', 'ALLOW ALL (Default)');
	foreach ($contextssel as $val) {
		$currentcomponent->addoptlistitem('contextssel', $val[0], $val[1]);
	}
	$currentcomponent->setoptlistopts('contextssel', 'sort', false);

	switch ($dispnum) {
		case 'devices':
			$currentcomponent->addguifunc('customcontexts_devices_configpageload');
		break;
		case 'extensions':
		  $currentcomponent->addguifunc('customcontexts_extensions_configpageload');
		break;
	}
}

//hook gui function
function customcontexts_devices_configpageload() {
  global $currentcomponent;

	$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
  $tech = $_REQUEST['tech'];
  $curcontext = $_REQUEST['customcontext'];

	$currentcomponent->addguielem('Device Options', new gui_selectbox('customcontext', $currentcomponent->getoptlist('contextssel'), $curcontext, 'Class of Service', 'You have the '.customcontexts_getmodulevalue('moduledisplayname').' Module installed! You can select a class of service from this list to limit this user to portions of the dialplan you defined in the '.customcontexts_getmodulevalue('moduledisplayname').' module.',true, "javascript:if (document.frm_devices.customcontext.value) {document.frm_devices.devinfo_context.value = document.frm_devices.customcontext.value} else {document.frm_devices.devinfo_context.value = 'from-internal'}"));

  $js = '<script type="text/javascript">$(document).ready(function(){$("#devinfo_context").parent().parent().hide();});</script>';
  $currentcomponent->addguielem('Device Options', new guielement('test-html', $js, ''));
}

//hook gui function
function customcontexts_extensions_configpageload() {
  global $currentcomponent;

	$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
  $tech = $_REQUEST['tech'];
  $curcontext = $_REQUEST['customcontext'];

	$currentcomponent->addguielem('Device Options', new gui_selectbox('customcontext', $currentcomponent->getoptlist('contextssel'), $curcontext, 'Class of Service', 'You have the '.customcontexts_getmodulevalue('moduledisplayname').' Module installed! You can select a class of service from this list to limit this user to portions of the dialplan you defined in the '.customcontexts_getmodulevalue('moduledisplayname').' module.',true, "javascript:if (document.frm_extensions.customcontext.value) {document.frm_extensions.devinfo_context.value = document.frm_extensions.customcontext.value} else {document.frm_extensions.devinfo_context.value = 'from-internal'}"));

  $js = '<script type="text/javascript">$(document).ready(function(){$("#devinfo_context").parent().parent().hide();});</script>';
  $currentcomponent->addguielem('Device Options', new guielement('test-html', $js, ''));
}

/*
 * admin page helper
 * we are using gui styles so there is very little on the page
 * the admin page is used to list _existing_ contexts for us to parse for includes
 * these contexts/includes can be tagged with a description for the user to select on the custom contexts page
 */ 
function customcontexts_customcontextsadmin_configpageinit($dispnum) {
global $currentcomponent;
	switch ($dispnum) {
		case 'customcontextsadmin':
			$currentcomponent->addguifunc('customcontexts_customcontextsadmin_configpageload');
			$currentcomponent->addprocessfunc('customcontexts_customcontextsadmin_configprocess', 5);  
		break;
	}
}

//this is the dirty work displaying the admin page
function customcontexts_customcontextsadmin_configpageload() {
global $currentcomponent;
	$contexterr = 'Class may not be left blank and must contain only letters, numbers and a few select characters!';
	$descerr = 'Description must be alpha-numeric, and may not be left blank';
	$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$action= isset($_REQUEST['action'])?$_REQUEST['action']:null;
	if ($action == 'del') {
		$currentcomponent->addguielem('_top', new gui_pageheading('title', _("Class").": $extdisplay"." deleted!", false), 0);
	} else {
		//need to get module name/type dynamically
		$query = ($_SERVER['QUERY_STRING'])?$_SERVER['QUERY_STRING']:'type=tool&display=customcontextsadmin&extdisplay='.$extdisplay;
		$delURL = $_SERVER['PHP_SELF'].'?'.$query.'&action=del';
		$info = 'The context which contains includes which you would like to make available to '.customcontexts_getmodulevalue('moduledisplayname').'. Any context you enter here will be parsed for includes and you can then include them in your own '.customcontexts_getmodulevalue('moduledisplayname').'. Removing them here does NOT delete the context, rather makes them unavailable to your '.customcontexts_getmodulevalue('moduledisplayname').'.';
	  $currentcomponent->addguielem('_top', new gui_hidden('action', ($extdisplay ? 'edit' : 'add')));
//		$currentcomponent->addguielem('_bottom', new gui_link('help', _(customcontexts_getmodulevalue('moduledisplayname')." v".customcontexts_getmodulevalue('moduleversion')), 'http://www.issabel.org/support/documentation/module-documentation/classofservice', true, false), 0);
		if (!$extdisplay) {
			$currentcomponent->addguielem('_top', new gui_pageheading('title', _("Add Class"), false), 0);
			$currentcomponent->addguielem('Class', new gui_textbox('extdisplay', '', _('Class'), $info, 'isWhitespace() || !isFilename()', $contexterr, false), 3);
			$currentcomponent->addguielem('Class', new gui_textbox('description', '', _('Description'), 'This will display as a heading for the available includes on the '.customcontexts_getmodulevalue('moduledisplayname').' page.', '!isAlphanumeric() || isWhitespace()', $descerr, false), 3);
		}	else {
			$savedcontext = customcontexts_customcontextsadmin_get($extdisplay);
			$context = $savedcontext[0];
			$description = $savedcontext[1];
			$locked = $savedcontext[2];
			$currentcomponent->addguielem('_top', new gui_hidden('extdisplay', $extdisplay));
			$currentcomponent->addguielem('_top', new gui_pageheading('title', _("Edit Class").": $description", false), 0);
			if ($locked == false) {			
				$currentcomponent->addguielem('_top', new gui_link('del', _("Remove Class").": $context", $delURL, true, false), 0);
			}
			else
			{
				$currentcomponent->addguielem('_top', new gui_label('del', _("Class").": $context can not be removed!", $delURL, true, false), 0);
			}
			$currentcomponent->addguielem('Class', new gui_textbox('description', $description, _('Description'), 'This will display as a heading for the available includes on the '.customcontexts_getmodulevalue('moduledisplayname').' page.', '!isAlphanumeric() || isWhitespace()', $descerr, false), 3);
			$inclist = customcontexts_getincludeslist($extdisplay);
			foreach ($inclist as $val) {
				if ($val[2] > 0) {
					$currentcomponent->addguielem('Includes Descriptions', new gui_textbox('includes['.$val[0].']', $val[1], '<font color="red"><strong>'.$val[0].'</strong></font>', 'This will display as the name of the include on the '.customcontexts_getmodulevalue('moduledisplayname').' page.<BR><font color="red"><strong>NOTE: This include should have a description denoting the fact that allowing it may allow another ENTIRE context!</strong></font>', '!isAlphanumeric() || isWhitespace()', $descerr, false), 3);
				} else {
					$currentcomponent->addguielem('Includes Descriptions', new gui_textbox('includes['.$val[0].']', $val[1], $val[0], 'This will display as the name of the include on the '.customcontexts_getmodulevalue('moduledisplayname').' page.', '!isAlphanumeric() || isWhitespace()', $descerr, false), 3);
				}

			}
		}
	}
}


//handle the admin submit button
function customcontexts_customcontextsadmin_configprocess() {
	$action= isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$context= isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$description= isset($_REQUEST['description'])?$_REQUEST['description']:null;
//addslashes	
	switch ($action) {
	case 'add':
		customcontexts_customcontextsadmin_add($context,$description);
	break;
	case 'edit':
		customcontexts_customcontextsadmin_edit($context,$description);
		$includes = isset($_REQUEST['includes'])?$_REQUEST['includes']:null;
		customcontexts_customcontextsadmin_editincludes($context,$includes);
	break;
	case 'del':
		customcontexts_customcontextsadmin_del($context);
	break;
	}
}


//retrieve a single context for the admin page
function customcontexts_customcontextsadmin_get($context) {
	global $db;
	$sql = "select context, description, locked from customcontexts_contexts_list where context = '$context'";
	$results = $db->getAll($sql);
	if(DB::IsError($results)) {
 		$results = null;
	}
	$tmparray = array($results[0][0], $results[0][1], $results[0][2]);
	return $tmparray;
}

//add a single context for admin
function customcontexts_customcontextsadmin_add($context,$description) {
	global $db;
	$sql = "insert customcontexts_contexts_list (context, description) VALUES ('$context','$description')";
	$db->query($sql);
	needreload();
}

//del a single context from admin
function customcontexts_customcontextsadmin_del($context) {
	global $db;
	$sql = "delete from customcontexts_includes_list where context = '$context'";
	$db->query($sql);
	$sql = "delete from customcontexts_contexts_list where context = '$context'";
	$db->query($sql);
	needreload();
}

//update a single context for admin
function customcontexts_customcontextsadmin_edit($context,$description) {
	global $db;
	$sql = "update customcontexts_contexts_list set description = '$description' where context = '$context'";
	$db->query($sql);
	needreload();
}

//edit the includes under a single admin context
function customcontexts_customcontextsadmin_editincludes($context,$includes) {
	global $db;
	$sql = "delete from customcontexts_includes_list  where context = '$context'";
	$db->query($sql);
	foreach ($includes as $key=>$val) {
		$sql = "insert customcontexts_includes_list (context, include, description) values ('$context','$key','$val')";
		$db->query($sql);
	}
	needreload();
}

//---------------------------------------------

/* custom contexts page helper
 * we are using gui styles so there is very little on the page
 * the custom contexts page is used to create _new_ contexts for use in the dialplan
 * these contexts can include any includes which were made available from admin
 */ 
function customcontexts_customcontexts_configpageinit($dispnum) {
global $currentcomponent;
	switch ($dispnum) {
		case 'customcontexts':
			$currentcomponent->addoptlistitem('includeyn', 'yes', 'Allow');
			$currentcomponent->addoptlistitem('includeyn', 'no', 'Deny');
			$currentcomponent->addoptlistitem('includeyn', 'allowmatch', 'Allow Rules');
			$currentcomponent->addoptlistitem('includeyn', 'denymatch', 'Deny Rules');
			$timegroups = timeconditions_timegroups_list_groups();
			foreach ($timegroups as $val) {
				$currentcomponent->addoptlistitem('includeyn', $val[0], $val[1]);
			}
			$currentcomponent->setoptlistopts('includeyn', 'sort', false);
			for($i = 0; $i <= 300; $i++) { 
				$currentcomponent->addoptlistitem('includesort', $i - 50, $i);
			}
			$currentcomponent->addguifunc('customcontexts_customcontexts_configpageload');
			$currentcomponent->addprocessfunc('customcontexts_customcontexts_configprocess', 5);  
		break;
	}
}

//actually render the custom contexts page
function customcontexts_customcontexts_configpageload() {
global $currentcomponent;
	$contexterr = 'Class may not be left blank and must contain only letters, numbers and a few select characters!';
	$descerr = 'Description must be alpha-numeric, and may not be left blank';
	$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$action= isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$showsort = isset($_REQUEST['showsort'])?$_REQUEST['showsort']:null;
	if (isset($showsort) && $showsort <> customcontexts_getmodulevalue('displaysortforincludes')) {
		customcontexts_setmodulevalue('displaysortforincludes', $showsort);
	}
	if ($action == 'del') {
		$currentcomponent->addguielem('_top', new gui_pageheading('title', _("Class").": $extdisplay"." deleted!", false), 0);
	} else {
		//need to get page name/type dynamically
		//caused trouble on dup or del after dup or rename
		//$query = ($_SERVER['QUERY_STRING'])?$_SERVER['QUERY_STRING']:'type=setup&display=customcontexts&extdisplay='.$extdisplay;
		$query = 'type=setup&display=customcontexts&extdisplay='.$extdisplay;
		$delURL = $_SERVER['PHP_SELF'].'?'.$query.'&action=del';
		$dupURL = $_SERVER['PHP_SELF'].'?'.$query.'&action=dup';
		$info = 'Class of service name. It will be available in your dialplan. These classes of service can be used as a context for a device/extension to allow them limited access to your dialplan.';
//		$currentcomponent->addguielem('_bottom', new gui_link('ver', _(customcontexts_getmodulevalue('moduledisplayname')." v".customcontexts_getmodulevalue('moduleversion')), 'http://www.issabel.org/support/documentation/module-documentation/classofservice', true, false), 0);
		if (!$extdisplay) {
			$currentcomponent->addguielem('_top', new gui_pageheading('title', _("Add Class"), false), 0);
			$currentcomponent->addguielem('Class', new gui_textbox('extdisplay', '', _('Class'), $info, 'isWhitespace() || !isFilename()', $contexterr, false), 3);
			$currentcomponent->addguielem('Class', new gui_textbox('description', '', _('Description'), 'This will display as the name of this class of service.', '!isAlphanumeric() || isWhitespace()', $descerr, false), 3);
		}	else {
			$savedcontext = customcontexts_customcontexts_get($extdisplay);
			$context = $savedcontext[0];
			$description = $savedcontext[1];
			$rulestext = $savedcontext[2];
			$faildest  = $savedcontext[3];
			$featurefaildest  = $savedcontext[4];
			$failpin  = $savedcontext[5];
			$featurefailpin  = $savedcontext[6];
			$currentcomponent->addguielem('_top', new gui_hidden('extdisplay', $extdisplay));
			$currentcomponent->addguielem('_top', new gui_pageheading('title', _("Edit Class").": $description", false), 0);
			//$currentcomponent->addguielem('_top', new gui_link('del', _("Delete Class")." $context", $delURL, true, false), 0);
			$currentcomponent->addguielem('_top', new guielement('del', '<tr><td colspan ="2"><a href="'.$delURL.'" onclick="return confirm(\'Are you sure you want to delete '.$context.'?\')">'._('Delete Class').' '.$context.'</a></td></tr>', ''),3);
			$currentcomponent->addguielem('_top', new gui_link('dup', _("Duplicate Class")." $context", $dupURL, true, false), 3);
			$showsort = customcontexts_getmodulevalue('displaysortforincludes');
			if ($showsort == 1) {
			//$sortURL = $_SERVER['PHP_SELF'].'?'.$query.'&showsort=0';
			//$currentcomponent->addguielem('_top', new gui_link('showsort', "Hide Sort Option", $sortURL, true, false), 0);
			} else {
				$sortURL = $_SERVER['PHP_SELF'].'?'.$query.'&showsort=1';
				$currentcomponent->addguielem('_top', new gui_link('showsort', "Show Sort Option", $sortURL, true, false), 0);
			}
			$currentcomponent->addguielem('Class', new gui_textbox('newcontext', $extdisplay, _('Class'), $info, 'isWhitespace() || !isFilename()', $contexterr, false), 2);
			$currentcomponent->addguielem('Class', new gui_textbox('description', $description, _('Description'), 'This will display as the name of this class of service.', '', '', false), 2);
			$ruledesc = 'If defined, you will have the option for each portion of the dialplan to Allow Rule, and that inclued will only be available if the number dialed matches these rules, or Deny Rule, and that include will only be available if the dialed number does NOT match these rules. You may use a pipe | to strip the preceeding digits.';
			$ruleshtml = '<tr><td valign="top"><a href="#" class="info">'._('Dial Rules').'<span>'.$ruledesc.'</span></a></td><td><textarea cols="20" rows="5" id="dialpattern" name="dialpattern">'.$rulestext.'</textarea></td></tr>';
			$currentcomponent->addguielem('Class', new guielement('rulesbox',$ruleshtml,''), 3);

			$currentcomponent->addguielem('Failover Destination', new gui_textbox('failpin', $failpin, 'PIN', 'Enter a numeric PIN to require authentication before continuing to destination.', '!isPINList()', 'PIN must be numeric!', true), 4);
			$currentcomponent->addguielem('Feature Code Failover Destination', new gui_textbox('featurefailpin', $featurefailpin, 'PIN', 'Enter a numeric PIN to require authentication before continuing to destination.', '!isPINList()', 'PIN must be numeric!', true), 4);
			$currentcomponent->addguielem('Failover Destination', new gui_drawselects('dest0', 0, $faildest, 'Failover Destination'));
			$currentcomponent->addguielem('Feature Code Failover Destination', new gui_drawselects('dest1', 1, $featurefaildest, 'Failover Destination'));
			$currentcomponent->addguielem('Set All', new gui_selectbox('setall', $currentcomponent->getoptlist('includeyn'), '', 'Set All To:', 'Choose allow to allow access to all includes, choose deny to deny access.',true,'javascript:for (i=0;i<document.forms[\'frm_customcontexts\'].length;i++) {if(document.forms[\'frm_customcontexts\'][i].type==\'select-one\' && document.forms[\'frm_customcontexts\'][i].name.indexOf(\'[allow]\') >= 0 ) {document.forms[\'frm_customcontexts\'][i].selectedIndex = document.forms[\'frm_customcontexts\'][\'setall\'].selectedIndex-1;}}'),2);
			$inclist = customcontexts_getincludes($extdisplay);
			foreach ($inclist as $val) {
				if ($showsort == 1) {
					if ($val[6] > 0) {
						//$currentcomponent->addguielem($val[1], new gui_selectbox('includes['.$val[2].'][allow]', $currentcomponent->getoptlist('includeyn'), $val[4], '<font color="red"><strong>'.$val[3].'</strong></font>', $val[2].': Choose allow to allow access to this include, choose deny to deny access.<BR><font color="red"><strong>NOTE: Allowing this include may automatically allow another ENTIRE context!</strong></font>',false));
						$gui1 = new gui_selectbox('includes['.$val[2].'][allow]', 
												$currentcomponent->getoptlist('includeyn'), $val[4], 
												'<font color="red"><strong>'.$val[3].'</strong></font>', 
												$val[2].': Choose allow to allow access to this include, choose deny to deny access.<BR><font color="red"><strong>NOTE: Allowing this include may automatically allow another ENTIRE context!</strong></font>',false);
					} else {
						//$currentcomponent->addguielem($val[1], new gui_selectbox('includes['.$val[2].'][allow]', $currentcomponent->getoptlist('includeyn'), $val[4], $val[3], $val[2].': Choose allow to allow access to this include, choose deny to deny access.',false));
						$gui1 = new gui_selectbox('includes['.$val[2].'][allow]', 
										$currentcomponent->getoptlist('includeyn'), $val[4], $val[3], 
										$val[2].': Choose allow to allow access to this include, choose deny to deny access.',false);
					}
					//$currentcomponent->addguielem($val[1], new gui_selectbox('includes['.$val[2].'][sort]', $currentcomponent->getoptlist('includesort'), $val[5], '<div align="right">Priority</div>', 'Choose a priority with which to sort this option. Lower numbers have a higher priority.',false));
					$guisort = new gui_selectbox('includes['.$val[2].'][sort]', $currentcomponent->getoptlist('includesort'), $val[5], '<div align="right">Priority</div>', 'Choose a priority with which to sort this option. Lower numbers have a higher priority.',false);
					$inchtml = '<tr><td colspan="2"><table width="100%"><tr><td></td><td width="50"></td></tr>'.$gui1->generatehtml().'</table></td><td><table>'.$guisort->generatehtml().'</table></td></tr>';
					$currentcomponent->addguielem($val[1], new guielement('$val[0]',$inchtml,''),3);
				} else {
					if ($val[6] > 0) {
						$currentcomponent->addguielem($val[1], new gui_selectbox('includes['.$val[2].'][allow]', 
															$currentcomponent->getoptlist('includeyn'), $val[4], 
															'<font color="red"><strong>'.$val[3].'</strong></font>', $val[2].': Choose allow to allow access to this include, choose deny to deny access.<BR><font color="red"><strong>NOTE: Allowing this include may automatically allow another ENTIRE context!</strong></font>',false));
					} else {
						$currentcomponent->addguielem($val[1], new gui_selectbox('includes['.$val[2].'][allow]', 
															$currentcomponent->getoptlist('includeyn'), $val[4], 
															$val[3], $val[2].': Choose allow to allow access to this include, choose deny to deny access.',false));
					}
				}
			}
		}
	}
       $currentcomponent->addguielem('_top', new gui_hidden('action', ($extdisplay ? 'edit' : 'add')));
}

//handle custom contexts page submit button
function customcontexts_customcontexts_configprocess() {
	$action= isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$context= isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$newcontext= isset($_REQUEST['newcontext'])?$_REQUEST['newcontext']:null;
	$description= isset($_REQUEST['description'])?$_REQUEST['description']:null;
	$dialrules= isset($_REQUEST['dialpattern'])?$_REQUEST['dialpattern']:null;
	$faildest= isset($_REQUEST["goto0"])?$_REQUEST[$_REQUEST['goto0'].'0']:null;
	$featurefaildest= isset($_REQUEST["goto1"])?$_REQUEST[$_REQUEST['goto1'].'1']:null;
	$failpin= isset($_REQUEST['failpin'])?$_REQUEST['failpin']:null;
	$featurefailpin= isset($_REQUEST['featurefailpin'])?$_REQUEST['featurefailpin']:null;

//addslashes	
	switch ($action) {
	case 'add':
		customcontexts_customcontexts_add($context,$description,$dialrules,$faildest,$featurefaildest,$failpin,$featurefailpin);
	break;
	case 'edit':
		if ($context <> $newcontext) {
			$_REQUEST['extdisplay'] = isset($_REQUEST['extdisplay'])?$newcontext:null;
		}
		customcontexts_customcontexts_edit($context,$newcontext,$description,$dialrules,$faildest,$featurefaildest,$failpin,$featurefailpin);
		$includes = isset($_REQUEST['includes'])?$_REQUEST['includes']:null;
		customcontexts_customcontexts_editincludes($context,$includes,$newcontext);
	break;
	case 'del':
		customcontexts_customcontexts_del($context);
		$_REQUEST['extdisplay'] = null;
	break;
	case 'dup':
		$newcontext = customcontexts_customcontexts_duplicatecontext($context);
		if ($context <> $newcontext) {
			$_REQUEST['extdisplay'] = isset($_REQUEST['extdisplay'])?$newcontext:null;
		}
	break;
	}
}

//retrieve a single custom context for the custom contexts page
function customcontexts_customcontexts_get($context) {
	global $db;
	$sql = "select context, description, dialrules, faildestination, featurefaildestination, failpin, featurefailpin from customcontexts_contexts where context = '$context'";
	$results = $db->getAll($sql);
	if(DB::IsError($results)) {
 		$results = null;
	}
	$tmparray = array($results[0][0], $results[0][1], $results[0][2], $results[0][3], $results[0][4], $results[0][5], $results[0][6]);
	return $tmparray;
}

//add a new custom context for custom contexts page
function customcontexts_customcontexts_add($context,$description,$dialrules,$faildest,$featurefaildest,$failpin,$featurefailpin) {
	global $db;
	$sql = "insert customcontexts_contexts (context, description, dialrules, faildestination, featurefaildestination, failpin, featurefailpin) VALUES ('$context','$description','$dialrules','$faildest','$featurefaildest','$failpin','$featurefailpin')";
	$db->query($sql);
	needreload();
}

//delete a single custom context from the custom contexts page
function customcontexts_customcontexts_del($context) {
	global $db;
	$sql = "delete from customcontexts_includes where context = '$context'";
	$db->query($sql);
	$sql = "delete from customcontexts_contexts where context = '$context'";
	$db->query($sql);
	needreload();
}

//update a single custom context from the custom contexts page
function customcontexts_customcontexts_edit($context,$newcontext,$description,$dialrules,$faildest,$featurefaildest,$failpin,$featurefailpin) {
	global $db;
	if (!isset($newcontext) || ($newcontext == '')) {
		$newcontext = $context;
	}
	$sql = "update customcontexts_contexts set context = '$newcontext', description = '$description', dialrules = '$dialrules', faildestination = '$faildest', featurefaildestination = '$featurefaildest', failpin = '$failpin', featurefailpin = '$featurefailpin' where context = '$context'";
	$db->query($sql);
	needreload();
}

//update the includes under a single custom context from the custom contexts page
function customcontexts_customcontexts_editincludes($context,$includes,$newcontext) {
	global $db;
	$sql = "delete from customcontexts_includes  where context = '$context'";
	$db->query($sql);
	if (!isset($newcontext) || ($newcontext == '')) {
		$newcontext = $context;
	}
	foreach ($includes as $key=>$val) {
		if ($val[allow] <> 'no') {
			$timegroup = 'null';
			$sort = 0;
			$userules = null;
			if (is_numeric($val[allow])) {
				$timegroup = $val[allow];
			} else {
				if ($val[allow] <> 'yes') {
					$userules = $val[allow];
				}
			}
			if (is_numeric($val[sort])) {
				$sort = $val[sort];
			}
			$sql = "insert customcontexts_includes (context, include, timegroupid, sort, userules) values ('$newcontext','$key', $timegroup, $sort, '$userules')";
			$db->query($sql);
		}
	}
	needreload();
}

function customcontexts_customcontexts_duplicatecontext($context) {
	global $db;
	$suffix = '_2';
	$counter = 2;
	$sql = "select description, dialrules, faildestination, featurefaildestination, failpin, featurefailpin from customcontexts_contexts  where context = '$context'";
	$results = $db->getAll($sql);
	if(DB::IsError($results)) {
 		$results = null;
		return;
	}
	$description = $results[0][0];
	$dialrules = $results[0][1];
	$faildest = $results[0][2];
	$featurefaildest = $results[0][3];
	$failpin = $results[0][4];
	$featurefailpin = $results[0][5];
	$sql = "select count(*) from customcontexts_contexts  where context = '".$context.$suffix."' or description = '".$description.$suffix."'";
	$results = $db->getAll($sql);
	if(DB::IsError($results)) {
 		$results = null;
	}
	while ($results[0][0] > 0) {
		$counter = $counter + 1;
		$suffix = '_'.$counter;
		$sql = "select count(*) from customcontexts_contexts  where context = '".$context.$suffix."' or description = '".$description.$suffix."'";
		$results = $db->getAll($sql);
		if(DB::IsError($results)) {
	 		$results = null;
		}
	}
	customcontexts_customcontexts_add($context.$suffix,$description.$suffix,$dialrules,$faildest,$featurefaildest,$failpin,$featurefailpin);
	$includes = customcontexts_getincludes($context);
	foreach ($includes as $val) {
		$newincludes[$val[2]] = array("allow"=>"$val[4]", "sort"=>"$val[5]");
	}
	customcontexts_customcontexts_editincludes($context.$suffix,$newincludes,$context.$suffix);
	needreload();
	return $context.$suffix;
}

/* callback to Time Groups Module so it can display usage information
   of specific groups
 */
function customcontexts_timegroups_usage($group_id) {

  $group_id = q($group_id);
  $results = sql("SELECT DISTINCT context, timegroupid FROM customcontexts_includes WHERE timegroupid = $group_id","getAll",DB_FETCHMODE_ASSOC);
  if (empty($results)) {
    return array();
  } else {
    foreach ($results as $result) {
      $usage_arr[] = array(
        "url_query" => "display=customcontexts&extdisplay=".$result['context'],
        "description" => sprintf(_("Class of Service: %s"),$result['context']),
      );
    }
    return $usage_arr;
  }
}

function customcontexts_check_destinations($dest=true) {
	global $active_modules;

	$destlist = array();
	if (is_array($dest) && empty($dest)) {
		return $destlist;
	}
	$sql = "SELECT context, description, faildestination, featurefaildestination FROM customcontexts_contexts";
	if ($dest !== true) {
		$sql .= " WHERE (faildestination in ('".implode("','",$dest)."') ) OR (featurefaildestination in ('".implode("','",$dest)."') )";
	}
	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

	$type = isset($active_modules['customcontexts']['type'])?$active_modules['customcontexts']['type']:'setup';

	foreach ($results as $result) {
		$thisdest    = $result['faildestination'];
    // blank destinations in custom context are valid
    if (!$thisdest) {
      continue;
    }
		$thisid      = $result['context'];
		$description = sprintf(_("Class of Service: %s (%s)"),$result['description'],$result['context']);
		$thisurl     = 'config.php?display=customcontexts&extdisplay='.urlencode($thisid);
		if ($dest === true || $dest = $thisdest) {
			$destlist[] = array(
				'dest' => $thisdest,
				'description' => $description,
				'edit_url' => $thisurl,
			);
		}
		$thisdest = $result['featurefaildestination'];
		if ($dest === true || $dest = $thisdest) {
			$destlist[] = array(
				'dest' => $thisdest,
				'description' => $description,
				'edit_url' => $thisurl,
			);
		}
	}
	return $destlist;
}
?>
