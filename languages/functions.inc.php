<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
function languages_destinations() {
	global $module_page;

	// it makes no sense to point at another queueprio (and it can be an infinite loop)
	if ($module_page == 'languages') {
		return false;
	}

	// return an associative array with destination and description
	foreach (languages_list() as $row) {
		$extens[] = array('destination' => 'app-languages,' . $row['language_id'] . ',1', 'description' => $row['description']);
	}
	return isset($extens)?$extens:null;
}

function languages_getdest($exten) {
	return array('app-languages,'.$exten.',1');
}

function languages_getdestinfo($dest) {
	global $active_modules;

	if (substr(trim($dest),0,14) == 'app-languages,') {
		$exten = explode(',',$dest);
		$exten = $exten[1];
		$thisexten = languages_get($exten);
		if (empty($thisexten)) {
			return array();
		} else {
			$type = isset($active_modules['languages']['type'])?$active_modules['languages']['type']:'setup';
			return array('description' => sprintf(_("Language: %s"),$thisexten['description']),
			             'edit_url' => 'config.php?display=languages&type='.$type.'&extdisplay='.urlencode($exten),
								  );
		}
	} else {
		return false;
	}
}

function languages_get_config($engine) {
	global $ext;
	switch ($engine) {
		case 'asterisk':
			foreach (languages_list() as $row) {
					$ext->add('app-languages',$row['language_id'], '', new ext_noop('Changing Channel to language: '.$row['lang_code'].' ('.$row['description'].')'));
					$ext->add('app-languages',$row['language_id'], '', new ext_setlanguage($row['lang_code']));
					$ext->add('app-languages',$row['language_id'], '', new ext_goto($row['dest']));
			}
		
		break;
	}
}

function languages_hookGet_config($engine) {
	global $ext;
	global $version;
	switch($engine) {
		case "asterisk":
			$priority = 'report';
			if (version_compare($version, "1.4", "ge")) { 
				$ext->splice('macro-user-callerid', 's', $priority,new ext_execif('$["${DB(AMPUSER/${AMPUSER}/language)}" != ""]', 'Set', 'CHANNEL(language)=${DB(AMPUSER/${AMPUSER}/language)}'));
			} else {
				$ext->splice('macro-user-callerid', 's', $priority,new ext_execif('$["${DB(AMPUSER/${AMPUSER}/language)}" != ""]', 'Set', 'LANGUAGE()=${DB(AMPUSER/${AMPUSER}/language)}'));
			}
			
			$routes=languages_incoming_get();
			foreach($routes as $current => $route){
				if($route['extension']=='' && $route['cidnum']){//callerID only
					$extension='s/'.$route['cidnum'];
					$context=$route['pricid']?'ext-did-0001':'ext-did-0002';
				}else{
					if(($route['extension'] && $route['cidnum'])||($route['extension']=='' && $route['cidnum']=='')){//callerid+did / any/any
						$context='ext-did-0001';
					}else{//did only
						$context='ext-did-0002';
					}
					$extension=($route['extension']!=''?$route['extension']:'s').($route['cidnum']==''?'':'/'.$route['cidnum']);
				}
				if(version_compare($version, "1.4", "ge")){ 
					$ext->splice($context, $extension, 1, new ext_setvar('CHANNEL(language)',$route['language']));
				}else{
					$ext->splice($context, $extension, 1, new ext_setvar('LANGUAGE',$route['language']));
				}
		}
		break;
	}
}

/**  Get a list of all languages
 */
function languages_list() {
	global $db;
	$sql = "SELECT language_id, description, lang_code, dest FROM languages ORDER BY description ";
	$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($results)) {
		die_issabelpbx($results->getMessage()."<br><br>Error selecting from languages");	
	}
	return $results;
}

function languages_get($language_id) {
	global $db;
	$sql = "SELECT language_id, description, lang_code, dest FROM languages WHERE language_id = ".$db->escapeSimple($language_id);
	$row = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($row)) {
		die_issabelpbx($row->getMessage()."<br><br>Error selecting row from languages");	
	}
	
	return $row;
}

function languages_add($description, $lang_code, $dest) {
	global $db, $amp_conf;
	$sql = "INSERT INTO languages (description, lang_code, dest) VALUES (".
		"'".$db->escapeSimple($description)."', ".
		"'".$db->escapeSimple($lang_code)."', ".
		"'".$db->escapeSimple($dest)."')";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getMessage().$sql);
	}
	if(method_exists($db,'insert_id')) {
		$id = $db->insert_id();
	} else {
		$id = $amp_conf["AMPDBENGINE"] == "sqlite3" ? sqlite_last_insert_rowid($db->connection) : mysql_insert_id($db->connection);
	}
	return($id);
}

function languages_delete($language_id) {
	global $db;
	$sql = "DELETE FROM languages WHERE language_id = ".$db->escapeSimple($language_id);
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getMessage().$sql);
	}
}

function languages_edit($language_id, $description, $lang_code, $dest) { 
	global $db;
	$sql = "UPDATE languages SET ".
		"description = '".$db->escapeSimple($description)."', ".
		"lang_code = '".$db->escapeSimple($lang_code)."', ".
		"dest = '".$db->escapeSimple($dest)."' ".
		"WHERE language_id = ".$db->escapeSimple($language_id);
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getMessage().$sql);
	}
}

function languages_configpageinit($pagename) {
	global $currentcomponent;

	$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$extension = isset($_REQUEST['extension'])?$_REQUEST['extension']:null;
	$tech_hardware = isset($_REQUEST['tech_hardware'])?$_REQUEST['tech_hardware']:null;

	// We only want to hook 'users' or 'extensions' pages.
	if ($pagename != 'users' && $pagename != 'extensions') 
		return true;
	// On a 'new' user, 'tech_hardware' is set, and there's no extension. Hook into the page.
	if ($tech_hardware != null || $pagename == 'users') {
		language_applyhooks();
		$currentcomponent->addprocessfunc('languages_configprocess', 8);
	} elseif ($action=="add") {
		// We don't need to display anything on an 'add', but we do need to handle returned data.
		$currentcomponent->addprocessfunc('languages_configprocess', 8);
	} elseif ($extdisplay != '') {
		// We're now viewing an extension, so we need to display _and_ process.
		language_applyhooks();
		$currentcomponent->addprocessfunc('languages_configprocess', 8);
	}
}

function language_applyhooks() {
	global $currentcomponent;

	// Add the 'process' function - this gets called when the page is loaded, to hook into 
	// displaying stuff on the page.
	$currentcomponent->addguifunc('languages_configpageload');
}

// This is called before the page is actually displayed, so we can use addguielem().
function languages_configpageload() {
	global $currentcomponent;

	// Init vars from $_REQUEST[]
	$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	
	// Don't display this stuff it it's on a 'This xtn has been deleted' page.
	if ($action != 'del') {
		$langcode = languages_user_get($extdisplay);

		$section = _('Language');
		$msgInvalidLanguage = _('Please enter a valid Language Code');
		$currentcomponent->addguielem($section, new gui_textbox('langcode', $langcode, _('Language Code'), _('This will cause all messages and voice prompts to use the selected language if installed.'), "!isFilename()", $msgInvalidLanguage, true));
	}
}

function languages_configprocess() {
	//create vars from the request
	$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$ext = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$extn = isset($_REQUEST['extension'])?$_REQUEST['extension']:null;
	$langcode = isset($_REQUEST['langcode'])?$_REQUEST['langcode']:null;

	if ($ext==='') { 
		$extdisplay = $extn; 
	} else {
		$extdisplay = $ext;
	} 
	if ($action == "add" || $action == "edit") {
		if (!isset($GLOBALS['abort']) || $GLOBALS['abort'] !== true) {
			languages_user_update($extdisplay, $langcode);
		}
	} elseif ($action == "del") {
		languages_user_del($extdisplay);
	}
}

function languages_user_get($xtn) {
	global $astman;

	// Retrieve the language configuraiton from this user from ASTDB
	$langcode = $astman->database_get("AMPUSER",$xtn."/language");

	return $langcode;
}

function languages_user_update($ext, $langcode) {
	global $astman;
	// Update the settings in ASTDB
	$astman->database_put("AMPUSER",$ext."/language",$langcode);
}

function languages_user_del($ext) {
	global $astman;

	// Clean up the tree when the user is deleted
	$astman->database_deltree("AMPUSER/$ext/language");
}

	//inbound route language settings
function languages_hook_core($viewing_itemid, $target_menuid){
	$extension	= isset($_REQUEST['extension'])		? $_REQUEST['extension']	:'';
	$cidnum		= isset($_REQUEST['cidnum'])		? $_REQUEST['cidnum']		:'';
	$extdisplay	= isset($_REQUEST['extdisplay'])	? $_REQUEST['extdisplay']	:'';
	$action		= isset($_REQUEST['action'])		? $_REQUEST['action']		:'';
	$language	= isset($_REQUEST['language'])		? $_REQUEST['language']		:'';
	//set $extension,$cidnum if we dont already have them
	if(!$extension && !$cidnum){
		$opts		= explode('/', $extdisplay);
		$extension	= $opts['0'];
		$cidnum		= isset($opts['1']) ? $opts['1'] : '';
	}else{
		$extension 	= $extension;
		$cidnum		= $cidnum;
	}
	
	//update if we have enough info
	if($action == 'edtIncoming' || ( $extension != '' || $cidnum != '') && $language != ''){
		languages_incoming_update($language=$language,$extension,$cidnum);
	}
	if($action=='delIncoming'){
		languages_incoming_delete($extension,$cidnum);
	}
	$html = '';
	if ($target_menuid == 'did'){
		$html.='<tr><td colspan="2"><h5>'._("Language").'<hr></h5></td></tr>';
		$html.='<tr><td><a href="#" class="info">'._('Language').'<span>'._("Allows you to set the language for this DID.").'</span></a>:</td>';
		$html.='<td><input type="text" name="language" value="'.languages_incoming_get($extension,$cidnum).'"></td></tr>';
	}
	return $html;
}

function languages_incoming_get($extension=null,$cidnum=null){
	global $db;
	if($extension || $cidnum || (isset($_REQUEST['extdisplay']) && $_REQUEST['extdisplay']=='/') || (isset($_REQUEST['display']) && $_REQUEST['display']=='did')){
		$sql='SELECT language FROM language_incoming WHERE extension = ? AND cidnum = ?';
		$lang=$db->getOne($sql, array($extension, $cidnum));
	}else{
		$sql='SELECT language_incoming.*,incoming.pricid FROM language_incoming, incoming WHERE language_incoming.cidnum=incoming.cidnum and language_incoming.extension=incoming.extension';
		$lang=$db->getAll($sql, DB_FETCHMODE_ASSOC);
	}
	return $lang;
}

function languages_incoming_update($language=null,$extension=null,$cidnum=null){
	global $db;
	$sql='DELETE FROM language_incoming WHERE extension = ? AND cidnum = ?';
	$db->query($sql,array($extension,$cidnum));
	if(isset($language) && $language!=''){//no need to keep a record if were not setting the language
		$sql='INSERT INTO language_incoming (extension,cidnum,language) VALUES (?, ?, ?)';
		$db->query($sql,array($extension,$cidnum,$language));
	};
}

function languages_incoming_delete($extension=null,$cidnum=null){
	global $db;
	$sql='DELETE FROM language_incoming WHERE extension = ? AND cidnum = ?';
	$db->query($sql,array($extension,$cidnum));
}

function languages_check_destinations($dest=true) {
	global $active_modules;

	$destlist = array();
	if (is_array($dest) && empty($dest)) {
		return $destlist;
	}
	$sql = "SELECT language_id, dest, description FROM languages ";
	if ($dest !== true) {
		$sql .= "WHERE dest in ('".implode("','",$dest)."')";
	}
	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

	$type = isset($active_modules['languages']['type'])?$active_modules['languages']['type']:'setup';

	foreach ($results as $result) {
		$thisdest = $result['dest'];
		$thisid   = $result['language_id'];
		$destlist[] = array(
			'dest' => $thisdest,
			'description' => sprintf(_("Language Change: %s"),$result['description']),
			'edit_url' => 'config.php?display=languages&type='.$type.'&extdisplay='.urlencode($thisid),
		);
	}
	return $destlist;
}

function languages_change_destination($old_dest, $new_dest) {
	$sql = 'UPDATE languages SET dest = "' . $new_dest . '" WHERE dest = "' . $old_dest . '"';
	sql($sql, "query");
}
?>
