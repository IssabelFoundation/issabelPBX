<?php
//    dynroute - Dynamic Route Module for IssabelPBX
//    Copyright (C) 2018 Issabel Foundation
//    Copyright (C) 2009-2014 John Fawcett john@voipsupport.it
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.

//    This file was originally a derived work of the issabelpbx ivr 
//    and calleridlookup modules in September 2009

if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

function dynroute_init() {
    global $db;
    global $amp_conf;

    // Check to make sure that install.sql has been run
    $sql = "SELECT displayname from dynroute where displayname='__install_done' LIMIT 1";

    $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);

    if (DB::IsError($results)) {
                    echo _("There is a problem with installation Contact support\n");
                    die;
    } else {
	$results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
    }
    
    if (!isset($results[0])) {
        // Note: There's an invalid entry created, __invalid, after this is run,
        // so as long as this has been run _once_, there will always be a result.

		$result = sql("INSERT INTO dynroute (displayname) VALUES ('__install_done')");
		needreload();
    }
}

// The destinations this module provides
// returns a associative arrays with keys 'destination' and 'description'
function dynroute_destinations() {
	//get the list of routes 
	$results = dynroute_list();

	// return an associative array with destination and description
	if (isset($results)) {
		foreach($results as $result){
			$extens[] = array('destination' => 'dynroute-'.$result['dynroute_id'].',s,1', 'description' => $result['displayname']);
		}
	}
	if (isset($extens)) 
		return $extens;
	else
		return null;
}

function dynroute_getdest($exten) {
	return array('dynroute-'.$exten.',s,1');
}

function dynroute_getdestinfo($dest) {
	global $active_modules;

	if (substr(trim($dest),0,9) == 'dynroute-') {
		$exten = explode(',',$dest);
		$exten = substr($exten[0],9);

		$thisexten = dynroute_get_details($exten);
		if (empty($thisexten)) {
			return array();
		} else {
			return array('description' => sprintf(_("Route: %s"),$thisexten['displayname']),
			             'edit_url' => 'config.php?display=dynroute&action=edit&id='.urlencode($exten),
								  );
		}
	} else {
		return false;
	}
}
function dynroute_recordings_usage($recording_id) {
        global $active_modules;

        $results = sql("SELECT `dynroute_id`, `displayname` FROM `dynroute` WHERE `announcement_id` = '$recording_id'","getAll",DB_FETCHMODE_ASSOC);
        if (empty($results)) {
                return array();
        } else {
                //$type = isset($active_modules['dynroute']['type'])?$active_modules['dynroute']['type']:'setup';
                foreach ($results as $result) {
                        $usage_arr[] = array(
                                'url_query' => 'config.php?display=dynroute&action=edit&id='.urlencode($result['dynroute_id']),
                                'description' => sprintf(_("Dynamic route: %s"),$result['displayname']),
                        );
                }
                return $usage_arr;
        }
}

function dynroute_get_config($engine) {
        global $ext;
        global $conferences_conf;
	global $version;

	switch($engine) {
		case "asterisk":
			$dynroutelist = dynroute_list();
			if(is_array($dynroutelist)) {
				foreach($dynroutelist as $item) {
					$id = "dynroute-".$item['dynroute_id'];
					$details = dynroute_get_details($item['dynroute_id']);	
					if ($item['sourcetype']=='mysql') {
						if (version_compare($version, "1.6", "lt")) {
		                                                  //Escaping MySQL query - thanks to http://www.asteriskgui.com/index.php?get=utilities-mysqlscape
		                                                  $replacements = array (
		                                                        '\\' => '\\\\',
		                                                        '"' => '\\"',
		                                                        '\'' => '\\\'',
		                                                        ' ' => '\\ ',
		                                                        ',' => '\\,',
		                                                        '(' => '\\(',
		                                                        ')' => '\\)',
		                                                        '.' => '\\.',
		                                                        '|' => '\\|'
		                                                  );
							$query = str_replace(array_keys($replacements), array_values($replacements), $item['mysql_query']);
						} else {
							$query = str_replace('"','\"',$item['mysql_query']);
						}
					}
					if ($item['sourcetype']=='odbc') {
						$query = str_replace('"','\"',$item['odbc_query']);
					}
					if ($item['sourcetype']=='url') {
						$query = str_replace('"','\"',$item['url_query']);
					}
					if ($item['sourcetype']=='agi') {
						$query = str_replace('"','\"',$item['agi_query']);
					}
					// above quote substitution is not done for astvar - the user must input the exact syntax
					if ($item['sourcetype']=='astvar') {
						$query = $item['astvar_query'];
					}

                                        $query = str_replace('[NUMBER]', '${CALLERID(num)}', $query);
                                        $query = str_replace('[INPUT]', '${dtmfinput}', $query);
                                        $query = str_replace('[DID]', '${FROM_DID}', $query);
					$query = preg_replace('/\[([^\]]*)\]/','${DYNROUTE_$1}',$query);
					$announcement_id = (isset($details['announcement_id']) ? $details['announcement_id'] : '');
					if ($item['enable_dtmf_input']=='CHECKED')
					{
                                        	if ($announcement_id) {
                                              		$announcement_msg = recordings_get_file($announcement_id);
                                        	} else {
							$announcement_msg = '';
                                        	}	
						$ext->add($id, 's', '', new ext_read('dtmfinput',$announcement_msg,'','','',$item['timeout']));
						if ($item['chan_var_name'] != '')
							$ext->add($id, 's', '', new ext_setvar('__DYNROUTE_'.$item['chan_var_name'], '${dtmfinput}'));
                                        }
					if ($item['sourcetype']=='mysql' && $item['mysql_host']!='' && $item['mysql_query']!='')
					{
						$ext->add($id, 's', '', new ext_setvar('connid', '""'));
                                        	$ext->add($id, 's', '', new ext_mysql_connect('connid', $item['mysql_host'],  $item['mysql_username'],  $item['mysql_password'],  $item['mysql_dbname']));
						$ext->add($id, 's', '', new ext_gotoif('$["${connid}" = ""]',$id.',1,1'));
                                        	$ext->add($id, 's', '', new ext_mysql_query('resultid', 'connid', $query));
                                        	$ext->add($id, 's', '', new ext_mysql_fetch('fetchid', 'resultid', 'dynroute')); 
                                        	$ext->add($id, 's', '', new ext_mysql_clear('resultid'));                            
                                        	$ext->add($id, 's', '', new ext_mysql_disconnect('connid'));
						if ($item['chan_var_name_res'] != '')
							$ext->add($id, 's', '', new ext_setvar('__DYNROUTE_'.$item['chan_var_name_res'], '${dynroute}'));
						$ext->add($id, 's', '', new ext_gotoif('$[${fetchid} = 0]',$id.',1,1'));
                                        }
					if ($item['sourcetype']=='url' && $item['url_query']!='')
					{
						$ext->add($id, 's', '', new ext_setvar('CURLOPT(dnstimeout)','5'));
						$ext->add($id, 's', '', new ext_setvar('CURLOPT(conntimeout)','5'));
						$ext->add($id, 's', '', new ext_setvar('CURLOPT(ftptimeout)','5'));
						$ext->add($id, 's', '', new ext_setvar('CURLOPT(httptimeout)','5'));
						$ext->add($id, 's', '', new ext_setvar('CURLOPT(ssl_verifyhost)','0'));
						$ext->add($id, 's', '', new ext_setvar('CURLOPT(ssl_verifypeer)','0'));
						$ext->add($id, 's', '', new ext_setvar('dynroute', '${CURL'.'("'.$query.'")}'));
						$ext->add($id, 's', '', new ext_gotoif('$["${dynroute}" = ""]',$id.',1,1'));
						if ($item['chan_var_name_res'] != '')
							$ext->add($id, 's', '', new ext_setvar('__DYNROUTE_'.$item['chan_var_name_res'], '${dynroute}'));
                                        }
					if ($item['sourcetype']=='agi' && $item['agi_query']!='')
					{
						$ext->add($id, 's', '', new ext_agi($query));
						if ($item['agi_var_name_res'] != '')
							$ext->add($id, 's', '', new ext_setvar('dynroute', '${'.$item['agi_var_name_res'].'}'));
						$ext->add($id, 's', '', new ext_gotoif('$["${dynroute}" = ""]',$id.',1,1'));
						if ($item['chan_var_name_res'] != '')
							$ext->add($id, 's', '', new ext_setvar('__DYNROUTE_'.$item['chan_var_name_res'], '${dynroute}'));
                                        }
					if ($item['sourcetype']=='odbc' && $item['odbc_func']!='')
					{
						$ext->add($id, 's', '', new ext_setvar('dynroute', '${ODBC_'.$item['odbc_func'].'("'.$query.'")}'));
						if ($item['chan_var_name_res'] != '')
							$ext->add($id, 's', '', new ext_setvar('__DYNROUTE_'.$item['chan_var_name_res'], '${dynroute}'));
                                        }
					if ($item['sourcetype']=='astvar' && $item['astvar_query']!='')
					{
						$ext->add($id, 's', '', new ext_setvar('dynroute', $query));
						if ($item['chan_var_name_res'] != '')
							$ext->add($id, 's', '', new ext_setvar('__DYNROUTE_'.$item['chan_var_name_res'], '${dynroute}'));
                                        }
					if ($item['sourcetype']=='none' && $item['enable_dtmf_input']=='CHECKED')
                                        {
                                                $ext->add($id, 's', '', new ext_setvar('dynroute','${dtmfinput}'));
                                        }
					$dests = dynroute_get_dests($item['dynroute_id'],'n');
					if (!empty($dests)) {
						foreach($dests as $dest) {
							$ext->add($id, 's', '', new ext_gotoif('$["${dynroute}" = "'.$dest['selection'].'"]',$dest['dest']));
						}
					}
					$ext->add($id, 's', '', new ext_goto($id.',1,1'));
					$dests = dynroute_get_dests($item['dynroute_id'],'y');
					if (!empty($dests) && $dests[0]['dest'] != '') $ext->add($id, '1', '', new ext_goto($dests[0]['dest']));
					$ext->add($id, '1', '', new ext_hangup(''));
				}
			}
		break;
	}
}



function dynroute_get_dynroute_id($name) {
	global $db;
	$res = $db->getRow("SELECT dynroute_id from dynroute where displayname='$name'");
	if (count($res) == 0) {
		// It's not there. Create it and return the ID
		sql("INSERT INTO dynroute (displayname )  values('$name')");
		$res = $db->getRow("SELECT dynroute_id from dynroute where displayname='$name'");

                sql("INSERT INTO dynroute_dests (dynroute_id,selection,default_dest,dest) VALUES ($res[0],'','y','app-blackhole,hangup,1')");

	}

	return ($res[0]);
	
}

function dynroute_add_command($id, $cmd, $dest, $default_dest) {
	global $db;
	// Does it already exist?
	$res = $db->getRow("SELECT * from dynroute_dests where dynroute_id='$id' and selection='$cmd' and default_dest='$default_dest'");
	if (count($res) == 0) {
		// Just add it.
		sql("INSERT INTO dynroute_dests (dynroute_id, selection, default_dest, dest) VALUES('$id', '$cmd', '$default_dest', '$dest')");
	} else {
		// Update it.
		sql("UPDATE dynroute_dests SET dest='$dest' where dynroute_id='$id' and selection='$cmd' and default_dest='$default_dest'");
	}
}
function dynroute_do_edit($id, $post) {
	global $db;
        $displayname = $db->escapeSimple($post['displayname']);
        $sourcetype = $db->escapeSimple($post['sourcetype']);
        $mysql_host = $db->escapeSimple($post['mysql_host']);
        $mysql_dbname = $db->escapeSimple($post['mysql_dbname']);
        $mysql_query = $db->escapeSimple($post['mysql_query']);
        $mysql_username = $db->escapeSimple($post['mysql_username']);
        $mysql_password = $db->escapeSimple($post['mysql_password']);
        $odbc_func = $db->escapeSimple($post['odbc_func']);
        $odbc_query = $db->escapeSimple($post['odbc_query']);
        $url_query = $db->escapeSimple($post['url_query']);
        $agi_query = $db->escapeSimple($post['agi_query']);
        $agi_var_name_res = $db->escapeSimple($post['agi_var_name_res']);
        $astvar_query = $db->escapeSimple($post['astvar_query']);
        $annmsg_id = isset($post['annmsg_id'])?$post['annmsg_id']:'';
        $enable_dtmf_input = isset($post['enable_dtmf_input'])?$post['enable_dtmf_input']:'';

        if (!empty($enable_dtmf_input)) {
                $enable_dtmf_input='CHECKED';
        }
        $timeout = isset($post['timeout'])?$post['timeout']:'';
        $chan_var_name = isset($post['chan_var_name'])?$post['chan_var_name']:'';
        $chan_var_name_res = isset($post['chan_var_name_res'])?$post['chan_var_name_res']:'';
 
	
	$sql = "
	UPDATE dynroute 
	SET 
		displayname='$displayname', 
		sourcetype='$sourcetype', 
		mysql_host='$mysql_host', 
		mysql_dbname='$mysql_dbname', 
		mysql_username='$mysql_username', 
		mysql_password='$mysql_password', 
		mysql_query='$mysql_query',
		odbc_func='$odbc_func',
		odbc_query='$odbc_query',
		url_query='$url_query',
		agi_query='$agi_query',
		agi_var_name_res='$agi_var_name_res',
		astvar_query='$astvar_query',
		announcement_id='$annmsg_id',  
		enable_dtmf_input='$enable_dtmf_input',  
		timeout='$timeout',  
		chan_var_name='$chan_var_name',  
		chan_var_name_res='$chan_var_name_res'  
	WHERE dynroute_id='$id'
	";
	sql($sql);

	// Delete all the old dests
	sql("DELETE FROM dynroute_dests where dynroute_id='$id'");
	// Now, lets find all the goto's in the post. Destinations return gotoN => foo and get fooN for the dest.
	// Is that right, or am I missing something?

	$first_option=true;
	foreach(array_keys($post) as $var) {
		if (preg_match('/goto(\d+)/', $var, $match)) {
			// This is a really horrible line of code. take N, and get value of fooN. See above. Note we
			// get match[1] from the preg_match above
			$dest = $post[$post[$var].$match[1]];
			$cmd = $post['option'.$match[1]];
			// Debugging if it all goes pear shaped.
			// print "I think pushing $cmd does $dest<br>\n";
			if ($first_option)  {
				dynroute_add_command($id, $cmd, $dest, 'y');
				$first_option=false;
			}
			if (strlen($cmd))
				dynroute_add_command($id, $cmd, $dest, 'n');
		}
	}
}


function dynroute_list() {
	global $db;

	$sql = "SELECT * FROM dynroute where displayname <> '__install_done' ORDER BY displayname";
        $res = $db->getAll($sql, DB_FETCHMODE_ASSOC);
        if(DB::IsError($res)) {
		return null;
        }
        return $res;
}

function dynroute_get_details($id) {
	global $db;

	$sql = "SELECT * FROM dynroute where dynroute_id='$id'";
        $res = $db->getAll($sql, DB_FETCHMODE_ASSOC);
        if(DB::IsError($res)) {
		return null;
        }
        return $res[0];
}

function dynroute_get_dests($id,$scope) {
	global $db;
	switch($scope) {
		case 'y':
			$sql = "SELECT selection, dest FROM dynroute_dests where dynroute_id='$id' AND default_dest='y' ORDER BY selection";
		break;
		case 'n':
			$sql = "SELECT selection, dest FROM dynroute_dests where dynroute_id='$id' AND default_dest='n' ORDER BY selection";
		break;
		case 'a':
		default:
			$sql = "SELECT selection, dest FROM dynroute_dests where dynroute_id='$id' ORDER BY selection";
		break;
	}

        $res = $db->getAll($sql, DB_FETCHMODE_ASSOC);
        if(DB::IsError($res)) {
                return null;
        }
        return $res;
}
	
function dynroute_get_name($id) {
	$res = dynroute_get_details($id);
	if (isset($res['displayname'])) {
		return $res['displayname'];
	} else {
		return null;
	}
}

function dynroute_check_destinations($dest=true) {
	global $active_modules;

	$destlist = array();
	if (is_array($dest) && empty($dest)) {
		return $destlist;
	}
	$sql = "SELECT dest, default_dest, displayname, selection, a.dynroute_id dynroute_id FROM dynroute a INNER JOIN dynroute_dests d ON a.dynroute_id = d.dynroute_id  ";
	if ($dest !== true) {
		$sql .= "WHERE dest in ('".implode("','",$dest)."')";
	}
	$sql .= "ORDER BY displayname";
	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

	foreach ($results as $result) {
		$thisdest = $result['dest'];
		$thisid   = $result['dynroute_id'];
		if ($result['default_dest']=='y') $sel='Default'; else $sel=$result['selection'];
		$destlist[] = array(
			'dest' => $thisdest,
			'description' => sprintf(_("Route: %s / Destination: %s"),$result['displayname'],$sel),
			'edit_url' => 'config.php?display=dynroute&action=edit&id='.urlencode($thisid),
		);
	}
	return $destlist;
}
