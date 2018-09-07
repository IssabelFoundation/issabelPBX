<?php
/* $Id:$ */

//used to get module information
function dialplaninjection_getmodulevalue($id) {
	global $db;
	$sql = "select value from dialplaninjection_module where id = '$id'";
	$results = $db->getAll($sql);
	if(DB::IsError($results))
		$results = null;
	return isset($results)?$results[0][0]:null;
}

//used to get module information
function dialplaninjection_setmodulevalue($id,$value) {
	global $db;
	$sql = "update dialplaninjection_module set value = '$value' where id = '$id'";
	$db->query($sql);
}

//after dialplan is created
function dialplaninjection_hookGet_config($engine) {
	global $db;
	global $ext;
	switch($engine) {
		case 'asterisk':

		break;
	}
}

//
function dialplaninjection_getinjections() {
	global $db;
	$sql = "select id, description, exten from dialplaninjection_dialplaninjections order by description";
	$results = $db->getAll($sql);
	if(DB::IsError($results))
		$results = null;
	foreach ($results as $val) {
		$exten = ($val[2])?'('.$val[2].') ':'';
		$tmparray[] = array($val[0], $exten.$val[1]);
		}
	return $tmparray;
}

//
function dialplaninjection_getcommands($injectionid) {
	global $db;
	$sql = "select id, command, sort from dialplaninjection_commands where injectionid = $injectionid order by sort";
	$results = $db->getAll($sql);
	if(DB::IsError($results))
		$results = null;
	foreach ($results as $val)
		$tmparray[] = array($val[0], $val[1], $val[2]);
	return $tmparray;
}

//get line labels for specified injection for destinations
function dialplaninjection_getcommandlabels($injectionid) {
	global $db;
	$sql = "SELECT substring(command,2,instr(command,'),')-2) as label FROM dialplaninjection_commands WHERE dialplaninjection_commands.injectionid = $injectionid and substring(command,1,1) = '(' and  substring(command,2,instr(command,'),')-2) <> ''";
	$results = $db->getAll($sql);
	if(DB::IsError($results))
		$results = null;
	foreach ($results as $val)
		$tmparray[] = array($val[0]);
	return $tmparray;
}

//
function dialplaninjection_commandtypes() {
	global $db;
	$sql = "select command, description from dialplaninjection_commands_list order by description";
	$results = $db->getAll($sql);
	if(DB::IsError($results))
		$results = null;
	foreach ($results as $val)
		$tmparray[] = array($val[0], $val[1]);
	return $tmparray;
}

//allow reload to get our config
//
function dialplaninjection_get_config($engine) {
  global $ext;
  switch($engine) {
    case 'asterisk':
	global $db;
	$ext->addInclude('from-internal-additional','ext-injections');
	$sql = "select id, description, destination, exten from dialplaninjection_dialplaninjections";
	$results = $db->getAll($sql);
	if(DB::IsError($results))
		$results = null;
	foreach ($results as $val) {
		$injection = $val[0];

		$ext->_exts['ext-injections'][] = null;
//		$ext->_exts['dialplaninjections'][] = null;
//		$ext->addInclude('dialplaninjections','injection-'.$injection);
		if ($val[3]) {
			$pattern = trim($val[3]);
			// remove blanks
			if ($pattern <> "") {
				// remove leading underscores (we do that on backend)
				if ($pattern[0] == "_") $pattern = substr($pattern,1);
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
//				$ext->add('ext-injections', $val[3], '', new ext_goto('1','injection-'.$injection, 'dialplaninjections'));
				$ext->addInclude('ext-injections','ext-injection-'.$injection);
				$ext->add('ext-injection-'.$injection,$pattern, '', new ext_goto('1','${'.$exten.'}','injection-'.$injection));
			}
		}
//		$ext->add('dialplaninjections', 'injection-'.$injection, '', new ext_noop('Entering Injection: '.$val[1]));
		$ext->add('injection-'.$injection, '_.', '', new ext_noop('Entering Injection: '.$val[1]));
		$sql = "select command from dialplaninjection_commands where injectionid = $injection order by sort";
		$results2 = $db->getAll($sql);
		if(DB::IsError($results2))
			$results2 = null;
		foreach ($results2 as $command) {
			if (substr($command[0],0,1) == '(') {
				$pos = strpos($command[0],'),');
				if ($pos === false) {
					$cmdtext = $command[0];
					$cmdlabel = '';
				} else {
					$cmdtext = substr($command[0],$pos + 2);
					$cmdlabel = substr($command[0],1,$pos - 1);
				}
			} else {
				$cmdtext = $command[0];
				$cmdlabel = '';
			}
//			$ext->add('dialplaninjections', 'injection-'.$injection, '', new extension($command[0]));
			$ext->add('injection-'.$injection, '_.', $cmdlabel, new extension($cmdtext));
		}
		$goto = explode(',',$val[2]);
//		$ext->add('dialplaninjections', 'injection-'.$injection, '', new ext_goto($goto[2],$goto[1],$goto[0]));
		$ext->add('injection-'.$injection, '_.', '', new ext_goto($goto[2],$goto[1],$goto[0]));
		$ext->add('injection-'.$injection,'h', '', new ext_macro('hangupcall'));
	}
  break;
  }
}

// returns a associative arrays with keys 'destination' and 'description'
// it allows dialplaninjection to be chosen as destinations
function dialplaninjection_destinations() {
	$injections =  dialplaninjection_getinjections();
	if (is_array($injections)) {
		foreach ($injections as $r) {
//			$extens[] = array('destination' => 'dialplaninjections,injection-'.$r[0].',1', 'description' => $r[1]);
			$extens[] = array('destination' => 'injection-'.$r[0].',${EXTEN},1', 'description' => $r[1]);
			$labels = dialplaninjection_getcommandlabels($r[0]);
			foreach ($labels as $label) {
				$extens[] = array('destination' => 'injection-'.$r[0].',${EXTEN},'.$label[0], 'description' => $r[1].'-'.$label[0]);
			}
		}
	}

	return $extens;
}


//---------------------------------------------

//dialplaninjection page helper
//we are using gui styles so there is very little on the page
//
function dialplaninjection_configpageinit($dispnum) {
global $currentcomponent;
	switch ($dispnum) {
		case 'dialplaninjection':
			$commandslist = dialplaninjection_commandtypes();
			foreach ($commandslist as $val) {
				$currentcomponent->addoptlistitem('commandslist', $val[0], $val[1]);
			}
			$currentcomponent->setoptlistopts('commandslist', 'sort', false);
			$currentcomponent->addguifunc('dialplaninjection_configpageload');
			$currentcomponent->addprocessfunc('dialplaninjection_configprocess', 5);
		break;
	}
}

//actually render the dialplaninjection page
function dialplaninjection_configpageload() {
global $currentcomponent;
	$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$action= isset($_REQUEST['action'])?$_REQUEST['action']:null;
	if ($action == 'del') {
		$currentcomponent->addguielem('_top', new gui_pageheading('title', _("Injection").": $extdisplay"." deleted!", false), 0);
	}
	else
	{
//need to get page name/type dynamically
		$descerr = _('Description must be alpha-numeric and may not be left blank!');
//exten should have js function to check range too. - on hold for now because i allow patterns now.
		$extenerr = 'Extension must be a dial pattern!';
		$query = ($_SERVER['QUERY_STRING'])?$_SERVER['QUERY_STRING']:'type=setup&display=dialplaninjection&extdisplay='.$extdisplay;
		$delURL = $_SERVER['PHP_SELF'].'?'.$query.'&action=del';
		$info = '';
		if (!$extdisplay) {
			$currentcomponent->addguielem('_top', new gui_pageheading('title', _("Add Injection"), false), 0);
			$currentcomponent->addguielem('_top', new gui_subheading('subtitle', _("Injection"), false), 0);
			$currentcomponent->addguielem('_top', new gui_textbox('description', '', _('Description'), _('This will display as the name of this injection.'), '!isAlphanumeric() || isWhitespace()', $descerr, false), 3);
			$currentcomponent->addguielem('_top', new gui_textbox('exten', '', _('Extension'), _('If selected, will allow dialing this injection directly. (May be left blank and may be a pattern. You may use a pipe | to strip the preceeding digits.)'), '!isDialpattern()', $extenerr, true), 3);
			$selhtml = drawselects(null,0);
			$currentcomponent->addguielem('_bottom', new gui_subheading('desttitle', _("Destination"), false), 0);
			$currentcomponent->addguielem('_bottom', new guielement('dest0', $selhtml, ''),0);
		}
		else
		{
			$savedinjection = dialplaninjection_get($extdisplay);
			$description = $savedinjection[1];
			$injection = $savedinjection[0];
			$destination = $savedinjection[2];
			$exten = $savedinjection[3];
			$selhtml = drawselects($destination ,0);
			$currentcomponent->addguielem('_bottom', new gui_subheading('desttitle', _("Destination"), false), 0);
			$currentcomponent->addguielem('_bottom', new guielement('dest0', $selhtml, ''),0);
			$currentcomponent->addguielem('_top', new gui_hidden('extdisplay', $injection));
			$currentcomponent->addguielem('_top', new gui_pageheading('title', _("Edit Injection").": $description", false), 0);
			$currentcomponent->addguielem('_top', new gui_link('del', _("Delete Injection")." $injection", $delURL, true, false), 0);
			$currentcomponent->addguielem('_top', new gui_subheading('subtitle', _("Injection"), false), 0);
			$currentcomponent->addguielem('_top', new gui_textbox('description', $description, _('Description'), _('This will display as the name of this injection.'), '!isAlphanumeric() || isWhitespace()', $descerr, false), 3);
			$currentcomponent->addguielem('_top', new gui_textbox('exten', $exten, _('Extension'), _('If selected, will allow dialing this injection directly. (May be left blank and may be a pattern. You may use a pipe | to strip the preceeding digits.)'), '!isDialpattern()', $extenerr, true), 3);
			$cmdlist = dialplaninjection_getcommands($injection);
			$cmdtext='';
			foreach ($cmdlist as $val) {
//				$currentcomponent->addguielem('Commands', new gui_textbox('commands['.$val[0].']', $val[1], 'Command', 'This command will be injected into the dialplan.', '','', false),3);
				$cmdtext .= $val[1]."\n";
			}
			$commandsdesc = _('These command will be injected into the dialplan. There is no need to type the extension or priority, just type the commands.');
			$commandshtml = '<tr><td valign="top"><a href="#" class="info">'._('Commands').'<span>'.$commandsdesc.'</span></a></td><td><textarea cols="50" rows="5" wrap="off" id="commands" name="commands">'.$cmdtext.'</textarea></td></tr>';
			$currentcomponent->addguielem(_('Commands'), new guielement('commandsbox',$commandshtml,''), 3);
			$currentcomponent->addguielem(_('Commands'), new gui_selectbox('newcommand', $currentcomponent->getoptlist('commandslist'), '', _('New Command'), _('Choose a command type from the list and submit to add a new command.'),true,"javascript:document.frm_dialplaninjection.commands.value += '\\n' + document.frm_dialplaninjection.newcommand.options[document.frm_dialplaninjection.newcommand.selectedIndex].value;"));
		}
		$currentcomponent->addguielem('_bottom', new gui_link('link', _(dialplaninjection_getmodulevalue('moduledisplayname')." v".dialplaninjection_getmodulevalue('moduleversion')), 'http://www.issabel.org', true, false), 9);
	}
       $currentcomponent->addguielem('_top', new gui_hidden('action', ($extdisplay ? 'edit' : 'add')));
}

//handle dialplaninjections page submit button
function dialplaninjection_configprocess() {
	$action= isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$injection= isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$description= isset($_REQUEST['description'])?$_REQUEST['description']:null;
	$exten= isset($_REQUEST['exten'])?$_REQUEST['exten']:null;
	$commands= isset($_REQUEST['commands'])?$_REQUEST['commands']:null;
	$destination= isset($_REQUEST["goto0"])?$_REQUEST[$_REQUEST['goto0'].'0']:null;

//addslashes
	switch ($action) {
	case 'add':
		dialplaninjection_add($description,$destination, $exten);
	break;
	case 'edit':
		dialplaninjection_edit($injection,$description,$destination, $exten);
//		$commands = isset($_REQUEST['commands'])?$_REQUEST['commands']:null;
		$arraycommands = explode("\n",$commands);
		dialplaninjection_editcommands($injection,$arraycommands);
//		$newcommand = isset($_REQUEST['newcommand'])?$_REQUEST['newcommand']:null;
// 		if ($newcommand) {
//		dialplaninjection_addcommand($injection,$newcommand);
//		}
	break;
	case 'del':
		dialplaninjection_del($injection);
	break;
	}
}

//retrieve a single dialplaninjection for the dialplaninjection page
function dialplaninjection_get($injection) {
	global $db;
	$sql = "select id, description, destination, exten from dialplaninjection_dialplaninjections where id = $injection";
	$results = $db->getAll($sql);
	if(DB::IsError($results)) {
 		$results = null;
	}
	$tmparray = array($results[0][0], $results[0][1], $results[0][2], $results[0][3]);
	return $tmparray;
}

//add a new dialplaninjection for dialplaninjection page
function dialplaninjection_add($description, $destination, $exten) {
	global $db;
	if (!get_magic_quotes_gpc()) {
	$description = addslashes($description);
	}
	$sql = "insert dialplaninjection_dialplaninjections (description, destination, exten) values ('$description', '$destination', '$exten')";
	$db->query($sql);
	needreload();
}

//delete a single dialplaninjection from the dialplaninjection page
function dialplaninjection_del($injection) {
	global $db;
	$sql = "delete from dialplaninjection_dialplaninjections where id = $injection";
	$db->query($sql);
	$sql = "delete from dialplaninjection_commands where injectionid = $injection";
	$db->query($sql);
	needreload();
}

//update a single dialplaninjection from the dialplaninjection page
function dialplaninjection_edit($injection,$description,$destination,$exten) {
	global $db;
	if (!get_magic_quotes_gpc()) {
	$description = addslashes($description);
	$exten = ((!isset($exten)) || ($exten == ''))?null:$exten;
	}
	$sql = "update dialplaninjection_dialplaninjections set description = '$description', destination = '$destination', exten = '$exten' where id = $injection";
	$db->query($sql);
	needreload();
}

//add a new command for dialplaninjection page
function dialplaninjection_addcommand($injection,$command) {
	global $db;
	if (!get_magic_quotes_gpc()) {
	$command= addslashes($command);
	}
	$sql = "insert dialplaninjection_commands (injectionid, command) values ($injection, '$command')";
	$db->query($sql);
	needreload();
}

//update the commands under a single dialplaninjection from the dialplaninjection page
function dialplaninjection_editcommands($injection,$commands) {
	global $db;
	$sql = "delete from dialplaninjection_commands where injectionid = $injection;";
	$db->query($sql);
	$sort=1;
	foreach ($commands as $val) {
		$val = trim($val);
		if (isset($val) && $val <> '') {
			if (!get_magic_quotes_gpc()) {
			$val= addslashes($val);
			}
			$sql = "insert dialplaninjection_commands (injectionid, command, sort) VALUES ($injection, '$val', $sort);";
			$db->query($sql);
			$sort=$sort+1;
		}
	}
	needreload();
}

?>
