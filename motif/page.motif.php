<?php
// Modified July 13, 2017 by commenting out the next line to support Issabel 4
// by Ward Mundy & Associates, LLC. Contact: support@incrediblepbx.com
//if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
isset($_REQUEST['action']) ? $action = $_REQUEST['action'] : $action = 'add';

global $astman;

if($action == 'delete') {
	//Get settings from DB and see if we created a trunk
	$sql = 'SELECT * FROM `motif` WHERE `id` = '.$db->escapeSimple($_REQUEST['id']);
	$a = sql($sql, 'getRow', DB_FETCHMODE_ASSOC);
	$s = unserialize($a['settings']);

	//If we created a trunk then delete it
	if(isset($s['trunk_number'])) {
		core_trunks_del($s['trunk_number']);
	}

	//If we created a route then delete it
	if(isset($s['obroute_number'])) {
		core_routing_delbyid($s['obroute_number']);
	}

	//Delete our settings from our own DB
	$sql = "DELETE FROM `motif` WHERE id = ".$db->escapeSimple($_REQUEST['id']);
	sql($sql);
	$action = 'add';
	needreload();
}

//Check to see if Asterisk is running along with chan_motif and res_xmpp
if($astman && $astman->connected() && $astman->mod_loaded('motif') && $astman->mod_loaded('xmpp')) {
	if(isset($_REQUEST['username'])) {
		$pn = isset($_REQUEST['number']) ? $db->escapeSimple($_REQUEST['number']) : '';
		$un = isset($_REQUEST['username']) ? $db->escapeSimple($_REQUEST['username']) : '';
		$pw = isset($_REQUEST['password']) ? $db->escapeSimple($_REQUEST['password']) : '';
		$priority = isset($_REQUEST['priority']) ? $db->escapeSimple($_REQUEST['priority']) : '127';
		$priority = ($priority > 127) ? 127 : $priority;
		$priority = ($priority < -128) ? -128 : $priority;
		
        $statusmessage = isset($_REQUEST['statusmessage']) ? $db->escapeSimple($_REQUEST['statusmessage']) : 'I am Available';

		//Add '@gmail.com' if not already appended.
		$un = preg_match('/@/i',$un) ? $un : $un . '@gmail.com';

		$settings = array();
		//Check trunk/Routes values
		$settings['trunk'] = isset($_REQUEST['trunk']) ? true : null;
		$settings['ibroute'] = isset($_REQUEST['ibroute']) ? true : null;
		$settings['obroute'] = isset($_REQUEST['obroute']) ? true : null;
		$settings['gvm'] = isset($_REQUEST['gvm']) ? true : null;
		$settings['greeting'] = isset($_REQUEST['greeting']) ? true : null;

		//Check to make sure all values are set and not empty
		if(!empty($pn) && !empty($un) && !empty($pw)) {
			//Add/Remove Trunk Values
			//The dial String
			$dialstring = 'Motif/g'.str_replace('@','',str_replace('.','',$un)).'/$OUTNUM$@voice.google.com';
			if($settings['trunk'] && $action == 'add') {
				$trunknum = core_trunks_add('custom', $dialstring, '', '', $pn, '', 'notneeded', '', '', 'off', '', 'off', 'GVM_' . $pn, '', 'off', 'r');
				$settings['trunk_number'] = $trunknum;
			} elseif($settings['trunk'] && $action == 'edit') {
				$sql = 'SELECT * FROM `motif` WHERE `id` = '.$db->escapeSimple($_REQUEST['id']);
				$a = sql($sql, 'getRow', DB_FETCHMODE_ASSOC);
				$s = unserialize($a['settings']);
				if(isset($s['trunk_number']) && core_trunks_getTrunkTrunkName($s['trunk_number'])) {
					core_trunks_edit($s['trunk_number'], $dialstring, '', '', $pn, '', 'notneeded', '', '', 'off', '', 'off', 'GVM_' . $pn, '', 'off', 'r');
					$settings['trunk_number'] = $s['trunk_number'];
				} else {
					$trunknum = core_trunks_add('custom', $dialstring, '', '', $pn, '', 'notneeded', '', '', 'off', '', 'off', 'GVM_' . $pn, '', 'off', 'r');
					$settings['trunk_number'] = $trunknum;
				}
			} elseif(!$settings['trunk'] && $action == 'edit') {
				$sql = 'SELECT * FROM `motif` WHERE `id` = '.$db->escapeSimple($_REQUEST['id']);
				$a = sql($sql, 'getRow', DB_FETCHMODE_ASSOC);
				$s = unserialize($a['settings']);
				if(isset($s['trunk_number'])) {
					core_trunks_del($s['trunk_number']);
				}
			}

			//Add/Remove Route Values
			$dialpattern[] = array(
	            'prepend_digits' => '1',
	            'match_pattern_prefix' => '',
	            'match_pattern_pass' => 'NXXNXXXXXX',
	            'match_cid' => ''
	        );
			$dialpattern[] = array(
                    'prepend_digits' => '',
                    'match_pattern_prefix' => '',
                    'match_pattern_pass' => '1NXXNXXXXXX',
                    'match_cid' => ''
            );      
	        //Replace all non-standard characters for route names.
			$routename = str_replace('@','',str_replace('.','',$un));
			
			if($action == 'add') {
			    //Outbound Routes add section
			    if($settings['obroute']) {
			        if(isset($settings['trunk_number'])) {
    					$routenum = core_routing_addbyid($routename, '', '', '', '', '', 'default', '', $dialpattern, array($settings['trunk_number']));
    					$settings['obroute_number'] = $routenum;
    				}
			    }
			    if($settings['ibroute']) {
			        
			    }
			} elseif($action == 'edit') {
			    //Outbound Routes add section
			    if($settings['obroute']) {    			    
			        $sql = 'SELECT * FROM `motif` WHERE `id` = '.$db->escapeSimple($_REQUEST['id']);
    				$a = sql($sql, 'getRow', DB_FETCHMODE_ASSOC);
    				$s = unserialize($a['settings']);
    				if(isset($s['trunk_number']) && isset($s['obroute_number'])) {
    					core_routing_editbyid($s['obroute_number'], $routename, '', '', '', '', '', 'default', '', $dialpattern, array($s['trunk_number']));
    					$settings['obroute_number'] = $s['obroute_number'];
    				} elseif(isset($settings['trunk_number'])) {
    					$routenum = core_routing_addbyid($routename, '', '', '', '', '', 'default', '', $dialpattern, array($settings['trunk_number']));
    					$settings['obroute_number'] = $routenum;
    				}
    			//Outbound Routes delete section
			    } elseif(!$settings['obroute']) {
			        $sql = 'SELECT * FROM `motif` WHERE `id` = '.$db->escapeSimple($_REQUEST['id']);
    				$a = sql($sql, 'getRow', DB_FETCHMODE_ASSOC);
    				$s = unserialize($a['settings']);
    				if(isset($s['obroute_number'])) {
    					core_routing_delbyid($s['obroute_number']);
    				}
			    }
			    
			    //Inbound Routes add section
			    if($settings['ibroute']) {
			        
			    //Inbound Routes add section
			    } elseif(!$settings['ibroute']) {
			        
		        }
			}

			//Prepare settings to be stored in the database
			$settings = serialize($settings);

            $statusmessage = isset($statusmessage) && !empty($statusmessage) ? $statusmessage : 'I am Available';

			if($action == 'add') {
                $sql = "INSERT INTO `motif` (`phonenum`, `username`, `password`, `settings`, `statusmessage`, `priority`) VALUES ('" . $pn . "', '" . $un . "', '" . $pw . "', '" . $settings . "', '" . $statusmessage . "', '" . $priority . "')";
			} elseif($action == 'edit') {
                $sql = "UPDATE `motif` SET `phonenum` = '".$pn."', `username` = '".$un."', `password` = '".$pw."', `settings` = '".$settings."', `statusmessage` = '".$statusmessage."', `priority` = '".$priority."' WHERE id = " . $db->escapeSimple($_REQUEST['id']);
			}
			sql($sql);
			needreload();
		}
	}
	
	$form_statusmessage = isset($form_statusmessage) ? $form_statusmessage : 'I am Available';
	
	$sql = 'SELECT * FROM `motif`';
	$accounts = sql($sql, 'getAll', DB_FETCHMODE_ASSOC);

	//If editing then let's get some important data back
	if($action == 'edit') {
		$sql = 'SELECT * FROM `motif` WHERE `id` = '.$db->escapeSimple($_REQUEST['id']);
		$account = sql($sql, 'getRow', DB_FETCHMODE_ASSOC);
		//print_r($account);
		$form_password = $account['password'];
		$form_username = $account['username'];
		$form_number = $account['phonenum'];

		$settings = unserialize($account['settings']);
		$form_trunk = isset($settings['trunk']) ? true : false;
		$form_obroute = isset($settings['obroute']) ? true : false;
		$form_ibroute = isset($settings['ibroute']) ? true : false;
		$form_gvm = isset($settings['gvm']) ? true : false;
		$form_greeting = isset($settings['greeting']) ? true : false;
		$id = $account['id'];
        
        $form_statusmessage = $account['statusmessage'];
		
		$form_priority = $account['priority'];

		$r = $astman->command("xmpp show connections");
		$status['connected'] = false;
		$context = str_replace('@','',str_replace('.','',$account['username']));
		if(preg_match('/\[g'.$context.'\] '.$account['username'].'.* (Connected)/i',$r['data'],$matches)) {
			$status['connected'] = true;
		};

		//$r = $astman->command("xmpp show buddies");
		//preg_match_all('/Client: g'.$context.'\n(?:.|\n)*/i',$r['data'],$client);
		/*
		preg_match_all('/Buddy:(.*)/i',$client[0][0],$matches);
		$buddies = array();
		foreach($matches[1] as $data) {
			if(!preg_match('/@public.talk.google.com/i',$data)) {
				$buddies[] = $data;
			}
		}
		*/
		$buddies = array('Removed for Debugging Purposes');

	}
	include('views/main.php');
	include('views/edit.php');
} else {
	echo "<h3>This module requires Asterisk chan_motif & res_xmpp to be installed and loaded</h3>";
}

/* List of command conversions from jabber to xmpp
jabber list nodes=xmpp list nodes
jabber purge nodes=xmpp purge nodes
jabber delete node=xmpp delete node
jabber create collection=xmpp create collection
jabber create leaf=xmpp create leaf
jabber set debug=xmpp set debug
jabber show connections=xmpp show connections
jabber show buddies=xmpp show buddies
*/
