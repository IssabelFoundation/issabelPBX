<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Copyright 2008 sasargen
//  Portions Copyright 2009, 2010, 2011 Mikael Carlsson, mickecamino@gmail.com
//	Copyright 2013 Schmooze Com Inc.
//
/* Verify existence of Voicemail, dictate, languages and findmefollow functions. */
if (function_exists("voicemail_mailbox_get") && function_exists("voicemail_mailbox_add") && function_exists("voicemail_mailbox_del") && function_exists("voicemail_mailbox_remove") && class_exists("vmxObject")) {
	$vm_exists	= TRUE;
} else {
	$vm_exists	= FALSE;
}
if (function_exists("dictate_get") && function_exists("dictate_update") && function_exists("dictate_del")) {
	$dict_exists	= TRUE;
} else {
	$dict_exists	= FALSE;
}
if (function_exists("languages_user_get") && function_exists("languages_user_update") && function_exists("languages_user_del")) {
	$lang_exists	= TRUE;
} else {
	$lang_exists	= FALSE;
}
if (function_exists("findmefollow_get") && function_exists("findmefollow_add") && function_exists("findmefollow_del")) {
	$findme_exists	= TRUE;
} else {
	$findme_exists	= FALSE;
}
if (function_exists("fax_get_user") && function_exists("fax_save_user") && function_exists("fax_delete_user")) {
       $fax_exists     = TRUE;
} else {
       $fax_exists     = FALSE;
}
if (function_exists("campon_get") && function_exists("campon_update") && function_exists("campon_del")) {
        $campon_exists  = TRUE;
} else {
        $campon_exists  = FALSE;
}
if (function_exists("queues_get_qnostate") && function_exists("queues_set_qnostate")) {
	$queue_exists = TRUE;
} else {
	$queue_exists = FALSE;
}

if (function_exists("xactview_user_get") && function_exists("xactview_user_update") && function_exists("xactview_user_del")) {
        $xactview_exists = TRUE;
} else {
        $xactview_exists = FALSE;
}
if (function_exists("xmpp_users_get") && function_exists("xmpp_users_put") && function_exists("xmpp_users_del")) {
	$xmpp_exists = TRUE;
} else {
	$xmpp_exists = FALSE;
}
if (function_exists("extensionroutes_list") && function_exists("extensionroutes_add_user") && function_exists("extensionroutes_edit_user") && function_exists("extensionroutes_del_user")) {
	$extensionroutes_exists = TRUE;
} else {
	$extensionroutes_exists = FALSE;
}
function bulkextensions_exportextensions_allusers() {
	global $db;
	global $vm_exists;
	global $dict_exists;
	global $lang_exists;
	global $findme_exists;
	global $fax_exists;
	global $campon_exists;
	global $queue_exists;
	global $xactview_exists;
	global $xmpp_exists;

	$action		= "edit";
	$fname		= "bulkext__" .  (string) time() . $_SERVER["SERVER_NAME"] . ".csv";
	$csv_header 	= "action,extension,name,cid_masquerade,sipname,outboundcid,ringtimer,callwaiting,call_screen,pinless,password,noanswer_dest,noanswer_cid,busy_dest,busy_cid,chanunavail_dest,chanunavail_cid,emergency_cid,tech,hardware,devinfo_channel,devinfo_secret,devinfo_notransfer,devinfo_dtmfmode,devinfo_canreinvite,devinfo_context,devinfo_immediate,devinfo_signalling,devinfo_echocancel,devinfo_echocancelwhenbrdiged,devinfo_echotraining,devinfo_busydetect,devinfo_busycount,devinfo_callprogress,devinfo_host,devinfo_type,devinfo_nat,devinfo_port,devinfo_qualify,devinfo_callgroup,devinfo_pickupgroup,devinfo_disallow,devinfo_allow,devinfo_dial,devinfo_accountcode,devinfo_mailbox,devinfo_deny,devinfo_permit,devicetype,deviceid,deviceuser,description,dictenabled,dictformat,dictemail,langcode,vm,vmpwd,email,pager,attach,saycid,envelope,delete,options,vmcontext,vmx_state,vmx_unavail_enabled,vmx_busy_enabled,vmx_play_instructions,vmx_option_0_sytem_default,vmx_option_0_number,vmx_option_1_system_default,vmx_option_1_number,vmx_option_2_number,account,ddial,pre_ring,strategy,grptime,grplist,annmsg_id,ringing,grppre,dring,needsconf,remotealert_id,toolate_id,postdest,faxenabled,faxemail,cfringtimer,concurrency_limit,answermode,qnostate,devinfo_trustrpid,devinfo_sendrpid,devinfo_qualifyfreq,devinfo_transport,devinfo_encryption,devinfo_vmexten,cc_agent_policy,cc_monitor_policy,recording_in_external,recording_out_external,recording_in_internal,recording_out_internal,recording_ondemand,recording_priority,add_xactview,xactview_autoanswer,xactview_email,xactview_cell,jabber_host,jabber_domain,jabber_resource,jabber_port,jabber_username,jabber_password,xactview_createprofile,xactview_profilepassword,xmpp_user,xmpp_pass\n";

	$data 		= $csv_header;
	$exts 		= bulkextensions_get_all_exts();

	foreach ($exts as $ext) {
		$e 	= $ext[0];
		$u_info = core_users_get($e);
		$d_info = core_devices_get($e);
		if ($vm_exists) {
			$v_info	= voicemail_mailbox_get($e);
		} else {
			$v_info = NULL;
		}
		/* To properly obtain Voicemail information, detect enabled/disabled vm value.   */
		/* Parse extra Voicemail options.						 */
		if ($v_info == NULL) {
			$v_enabled	= "disabled";
		} else {
			$v_enabled 	= "enabled";
			$v_options 	= isset($v_info["options"])?$v_info["options"]:"";
			$vm_other_opts 	= "";
			$i 		= 0;
			$first 		= TRUE;
			$c 		= count($v_options);
			reset($v_options);
			while ($i < $c) {
				if ((key($v_options) != "attach") && (key($v_options) != "saycid") && (key($v_options) != "envelope") && (key($v_options) != "delete")) {
					if ($first) {
						$vm_other_opts	= key($v_options) . "=" . $v_options[key($v_options)];
						$first 		= false;
					} else {
						$vm_other_opts .=  "|" . key($v_options) . "=" . $v_options[key($v_options)];
					}
				}
				$i++;
				next($v_options);
			}
		}
		/* Obtain vmx settings. */
		if ($vm_exists) {
			$vmxobj		= new vmxObject($e);
		} else {
			$vmxobj		= NULL;
		}

		if (is_object($vmxobj)) {
			$vmx_state 		= ($vmxobj->isEnabled())?"checked":"";
			$vmx_unavail_enabled 	= ($vmxobj->getState("unavail")=="enabled")?"checked":"";
			$vmx_busy_enabled 	= ($vmxobj->getState("busy")=="enabled")?"checked":"";
			$vmx_play_instructions 	= ($vmxobj->getVmPlay())?"checked":"";
			$vmx_option_0_number 	= $vmxobj->getMenuOpt(0);
			if ($vmx_option_0_number == "") {
				$vmx_option_0_system_default = "checked";
			} else {
				$vmx_option_0_system_default = "";
			}
			if (is_object($vmxobj)) {
				if ($vmxobj->hasFollowMe() && $vmxobj->isFollowMe()) {
					$vmx_option_1_system_default 	= "checked";
					$vmx_option_1_number 		= "";
				} else {
					$vmx_option_1_system_default 	= "";
					$vmx_option_1_number 		= $vmxobj->getMenuOpt(1);
				}
				$vmx_option_2_number 			= $vmxobj->getMenuOpt(2);
			}
		}

		/* Obtain dictation services settings. */
		if ($dict_exists) {
			$dictate_settings = dictate_get($e);
		}

		/* Obtain language code. */
		if ($lang_exists) {
			$langcode = languages_user_get($e);
		}

		/* Obtain follow me settings. */
		if ($findme_exists) {
			$followme_settings = findmefollow_get($u_info["extension"], TRUE);
		}
		if (isset($followme_settings)) {
			$account	= isset($followme_settings["grpnum"])?$followme_settings["grpnum"]:"";
			$strategy	= isset($followme_settings["strategy"])?$followme_settings["strategy"]:"";
			$grptime	= isset($followme_settings["grptime"])?$followme_settings["grptime"]:"";
			$grppre		= isset($followme_settings["grppre"])?$followme_settings["grppre"]:"";
			$grplist	= isset($followme_settings["grplist"])?$followme_settings["grplist"]:"";
			$annmsg_id	= isset($followme_settings["annmsg_id"])?$followme_settings["annmsg_id"]:"";
			$postdest	= isset($followme_settings["postdest"])?$followme_settings["postdest"]:"";
			$dring 		= isset($followme_settings["dring"])?$followme_settings["dring"]:"";
			$needsconf 	= isset($followme_settings["needsconf"])?$followme_settings["needsconf"]:"";
			$remotealert_id = isset($followme_settings["remotealert_id"])?$followme_settings["remotealert_id"]:"";
			$toolate_id 	= isset($followme_settings["toolate_id"])?$followme_settings["toolate_id"]:"";
			$ringing 	= isset($followme_settings["ringing"])?$followme_settings["ringing"]:"";
			$pre_ring 	= isset($followme_settings["pre_ring"])?$followme_settings["pre_ring"]:"";
			$ddial 		= isset($followme_settings["ddial"])?$followme_settings["ddial"]:"";
		}

		/* Obtain fax settings */
		if ($fax_exists) {
		    $fax_settings = fax_get_user($e);
		}
		if (isset($fax_settings)) {
			$faxenabled     = isset($fax_settings["faxenabled"])?$fax_settings["faxenabled"]:"";
			$faxemail       = isset($fax_settings["faxemail"])?$fax_settings["faxemail"]:"";
		}
		if ($campon_exists) {
		    $campon_settings = campon_get($e);
		}
		if ($queue_exists) {
			$q_info = queues_get_qnostate($e);
		}

		//SHMZ
		/* Obtain xactview settings */
		if($xactview_exists) {
			$xactview_settings = xactview_user_get($e);
		}
		if(isset($xactview_settings)) {
			$add_xactview 		= isset($xactview_settings["add_extension"])?$xactview_settings["add_extension"]:"0";
			$xactview_autoanswer 	= isset($xactview_settings["auto_answer"])?$xactview_settings["auto_answer"]:"0";
			$xactview_email		= isset($xactview_settings["email"])?$xactview_settings["email"]:"";
			$xactview_cell		= isset($xactview_settings["cell_phone"])?$xactview_settings["cell_phone"]:"";
			$jabber_host		= isset($xactview_settings["jabber_host"])?$xactview_settings["jabber_host"]:"";
			$jabber_domain		= isset($xactview_settings["jabber_domain"])?$xactview_settings["jabber_domain"]:"";
			$jabber_resource	= isset($xactview_settings["jabber_resource"])?$xactview_settings["jabber_resource"]:"XactView";
			$jabber_port		= isset($xactview_settings["jabber_port"])?$xactview_settings["jabber_port"]:"5222";
			$jabber_username	= isset($xactview_settings["jabber_user_name"])?$xactview_settings["jabber_user_name"]:"";
			$jabber_password	= isset($xactview_settings["jabber_password"])?$xactview_settings["jabber_password"]:"";
			$xactview_createprofile	= isset($xactview_settings["add_profile"])?$xactview_settings["add_profile"]:"0";
			$xactview_profilepassword = isset($xactview_settings["password"])?$xactview_settings["password"]:"";
		}
		if (isset($xmpp_exists) && $xmpp_exists) {
			$xmpp_settings = xmpp_users_get($e);
		}
		//number our columns
		$csvi = 1;

		$csv_line[$csvi] 	= $action;
		$csv_line[$csvi++] 	= isset($u_info["extension"])?$u_info["extension"]:"";
		$csv_line[$csvi++] 	= isset($u_info["name"])?$u_info["name"]:"";
		$csv_line[$csvi++] 	= isset($u_info["cid_masquerade"])?$u_info["cid_masquerade"]:"";
		$csv_line[$csvi++] 	= isset($u_info["sipname"])?$u_info["sipname"]:"";
		$csv_line[$csvi++] 	= isset($u_info["outboundcid"])?$u_info["outboundcid"]:"";
		$csv_line[$csvi++] 	= isset($u_info["ringtimer"])?$u_info["ringtimer"]:"";
		$csv_line[$csvi++]	= isset($u_info["callwaiting"])?$u_info["callwaiting"]:"";
		$csv_line[$csvi++]	= isset($u_info["call_screen"])?$u_info["call_screen"]:"0";
		$csv_line[$csvi++]	= isset($u_info["pinless"])?$u_info["pinless"]:"";
		$csv_line[$csvi++]	= isset($u_info["password"])?$u_info["password"]:"";
		$csv_line[$csvi++]   	= isset($u_info["noanswer_dest"])?$u_info["noanswer_dest"]:"";
		$csv_line[$csvi++]   	= isset($u_info["noanswer_cid"])?$u_info["noanswer_cid"]:"";
		$csv_line[$csvi++]   	= isset($u_info["busy_dest"])?$u_info["busy_dest"]:"";
		$csv_line[$csvi++]   	= isset($u_info["busy_cid"])?$u_info["busy_cid"]:"";
		$csv_line[$csvi++] 	= isset($u_info["chanunavail_dest"])?$u_info["chanunavail_dest"]:"";
		$csv_line[$csvi++]  	= isset($u_info["chanunavail_cid"])?$u_info["chanunavail_cid"]:"";
		$csv_line[$csvi++]	= isset($d_info["emergency_cid"])?$d_info["emergency_cid"]:"";
		$csv_line[$csvi++]	= isset($d_info["tech"])?$d_info["tech"]:"";
		$csv_line[$csvi++]	= ""; 	// hardware
		$csv_line[$csvi++]	= isset($d_info["channel"])?$d_info["channel"]:"";
		$csv_line[$csvi++]	= isset($d_info["secret"])?$d_info["secret"]:"";
		$csv_line[$csvi++]	= isset($d_info["notransfer"])?$d_info["notransfer"]:"";
		$csv_line[$csvi++]	= isset($d_info["dtmfmode"])?$d_info["dtmfmode"]:"";
		$csv_line[$csvi++]	= isset($d_info["canreinvite"])?$d_info["canreinvite"]:"";
		$csv_line[$csvi++]	= isset($d_info["context"])?$d_info["context"]:"";
		$csv_line[$csvi++]	= isset($d_info["immediate"])?$d_info["immediate"]:"";
		$csv_line[$csvi++]	= isset($d_info["signalling"])?$d_info["signalling"]:"";
		$csv_line[$csvi++]	= isset($d_info["echocancel"])?$d_info["echocancel"]:"";
		$csv_line[$csvi++]	= isset($d_info["echocancelwhenbridged"])?$d_info["echocancelwhenbridged"]:"";
		$csv_line[$csvi++]	= isset($d_info["echotraining"])?$d_info["echotraining"]:"";
		$csv_line[$csvi++]	= isset($d_info["busydetect"])?$d_info["busydetect"]:"";
		$csv_line[$csvi++]	= isset($d_info["busycount"])?$d_info["busycount"]:"";
		$csv_line[$csvi++]	= isset($d_info["callprogress"])?$d_info["callprogress"]:"";
		$csv_line[$csvi++]	= isset($d_info["host"])?$d_info["host"]:"";
		$csv_line[$csvi++]	= isset($d_info["type"])?$d_info["type"]:"";
		$csv_line[$csvi++]	= isset($d_info["nat"])?$d_info["nat"]:"";
		$csv_line[$csvi++]	= isset($d_info["port"])?$d_info["port"]:"";
		$csv_line[$csvi++]	= isset($d_info["qualify"])?$d_info["qualify"]:"";
		$csv_line[$csvi++]	= isset($d_info["callgroup"])?$d_info["callgroup"]:"";
		$csv_line[$csvi++]	= isset($d_info["pickupgroup"])?$d_info["pickupgroup"]:"";
		$csv_line[$csvi++]	= isset($d_info["disallow"])?$d_info["disallow"]:"";
		$csv_line[$csvi++]	= isset($d_info["allow"])?$d_info["allow"]:"";
		$csv_line[$csvi++]	= isset($d_info["dial"])?$d_info["dial"]:"";
		$csv_line[$csvi++]	= isset($d_info["accountcode"])?$d_info["accountcode"]:"";
		$csv_line[$csvi++]	= isset($d_info["mailbox"])?$d_info["mailbox"]:"";
		$csv_line[$csvi++]	= isset($d_info["deny"])?$d_info["deny"]:"";
		$csv_line[$csvi++]	= isset($d_info["permit"])?$d_info["permit"]:"";
		$csv_line[$csvi++]	= isset($d_info["devicetype"])?$d_info["devicetype"]:"fixed";
		$csv_line[$csvi++]	= (isset($d_info["deviceid"]) || ($d_info["deviceid"]==""))?$d_info["deviceid"]:(isset($u_info["extension"])?$u_info["extension"]:"");
		$csv_line[$csvi++]	= (isset($d_info["deviceuser"]) && ($d_info["deviceuser"] != ""))?$d_info["deviceuser"]:(isset($u_info["extension"])?$u_info["extension"]:"none");
		$csv_line[$csvi++]	= isset($d_info["description"])?$d_info["description"]:(isset($u_info["name"])?$u_info["name"]:"");

		$csv_line[$csvi++]	= isset($dictate_settings["enabled"])?$dictate_settings["enabled"]:"disabled";	// dictenabled
		$csv_line[$csvi++]	= isset($dictate_settings["format"])?$dictate_settings["format"]:"ogg";		// dictformat (ogg is default)
		$csv_line[$csvi++]	= isset($dictate_settings["email"])?$dictate_settings["email"]:""; 		// dictemail
		$csv_line[$csvi++]	= isset($langcode)?$langcode:"";
		$csv_line[$csvi++]	= $v_enabled; // vm
		$csv_line[$csvi++]	= isset($v_info["pwd"])?$v_info["pwd"]:"";
		$csv_line[$csvi++]	= isset($v_info["email"])?$v_info["email"]:"";
		$csv_line[$csvi++]	= isset($v_info["pager"])?$v_info["pager"]:"";
		$csv_line[$csvi++]	= isset($v_info["options"]["attach"])?("attach=" . $v_info["options"]["attach"]):"attach=no";
		$csv_line[$csvi++]	= isset($v_info["options"]["saycid"])?("saycid=" . $v_info["options"]["saycid"]):"saycid=no";
		$csv_line[$csvi++]	= isset($v_info["options"]["envelope"])?("envelope=" . $v_info["options"]["envelope"]):"envelope=no";
		$csv_line[$csvi++]	= isset($v_info["options"]["delete"])?("delete=" . $v_info["options"]["delete"]):"delete=no";
		$csv_line[$csvi++]	= isset($vm_other_opts)?$vm_other_opts:""; // additional options
		$csv_line[$csvi++]	= isset($v_info["vmcontext"])?$v_info["vmcontext"]:"";
		$csv_line[$csvi++]	= isset($vmx_state)?$vmx_state:"";
		$csv_line[$csvi++]	= isset($vmx_unavail_enabled)?$vmx_unavail_enabled:"";
		$csv_line[$csvi++]	= isset($vmx_busy_enabled)?$vmx_busy_enabled:"";
		$csv_line[$csvi++]	= isset($vmx_play_instructions)?$vmx_play_instructions:"";
		$csv_line[$csvi++]	= isset($vmx_option_0_system_default)?$vmx_option_0_system_default:"";
		$csv_line[$csvi++]	= isset($vmx_option_0_number)?$vmx_option_0_number:"";
		$csv_line[$csvi++]	= isset($vmx_option_1_system_default)?$vmx_option_1_system_default:"";
		$csv_line[$csvi++]	= isset($vmx_option_1_number)?$vmx_option_1_number:"";
		$csv_line[$csvi++]	= isset($vmx_option_2_number)?$vmx_option_2_number:"";
		$csv_line[$csvi++]	= isset($account)?$account:"";
		$csv_line[$csvi++]	= isset($ddial)?$ddial:"";
		$csv_line[$csvi++]	= isset($pre_ring)?$pre_ring:"";
		$csv_line[$csvi++]	= isset($strategy)?$strategy:"";
		$csv_line[$csvi++]	= isset($grptime)?$grptime:"";
		$csv_line[$csvi++]	= isset($grplist)?$grplist:"";
		$csv_line[$csvi++]	= isset($annmsg_id)?$annmsg_id:"";
		$csv_line[$csvi++]	= isset($ringing)?$ringing:"";
		$csv_line[$csvi++]	= isset($grppre)?$grppre:"";
		$csv_line[$csvi++]	= isset($dring)?$dring:"";
		$csv_line[$csvi++]	= isset($needsconf)?$needsconf:"";
		$csv_line[$csvi++]	= isset($remotealert_id)?$remotealert_id:"";
		$csv_line[$csvi++]	= isset($toolate_id)?$toolate_id:"";
		$csv_line[$csvi++]	= isset($postdest)?$postdest:"";
		$csv_line[$csvi++]   	= isset($faxenabled)?$faxenabled:"";
		$csv_line[$csvi++]   	= isset($faxemail)?$faxemail:"";
		//missing extension options
		$csv_line[$csvi++]   	= isset($u_info["cfringtimer"])?$u_info["cfringtimer"]:0;
		$csv_line[$csvi++]   	= isset($u_info["concurrency_limit"])?$u_info["concurrency_limit"]:0;
		$csv_line[$csvi++]   	= isset($u_info["answermode"])?$u_info["answermode"]:"disabled";
		$csv_line[$csvi++]   	= isset($q_info)?$q_info:"usestate";
		//missing device info
		$csv_line[$csvi++]   	= isset($d_info["trustrpid"])?$d_info["trustrpid"]:"yes";
		$csv_line[$csvi++]   	= isset($d_info["sendrpid"])?$d_info["sendrpid"]:"no";
		$csv_line[$csvi++]   	= isset($d_info["qualifyfreq"])?$d_info["qualifyfreq"]:"60";
		$csv_line[$csvi++]  	= isset($d_info["transport"])?$d_info["transport"]:"udp";
		$csv_line[$csvi++]  	= isset($d_info["encryption"])?$d_info["encryption"]:"no";
		$csv_line[$csvi++]  	= isset($d_info["vmexten"])?$d_info["vmexten"]:"";
		//campon
		$csv_line[$csvi++]  	= isset($campon_settings['cc_agent_policy'])?$campon_settings['cc_agent_policy']:"generic";
		$csv_line[$csvi++]  	= isset($campon_settings['cc_monitor_policy'])?$campon_settings['cc_monitor_policy']:"generic";
		//call recordings
		$csv_line[$csvi++]  	= isset($u_info['recording_in_external'])?$u_info['recording_in_external']:"dontcare";
		$csv_line[$csvi++]  	= isset($u_info['recording_out_external'])?$u_info['recording_out_external']:"dontcare";
		$csv_line[$csvi++]  	= isset($u_info['recording_in_internal'])?$u_info['recording_in_internal']:"dontcare";
		$csv_line[$csvi++]  	= isset($u_info['recording_out_internal'])?$u_info['recording_out_internal']:"dontcare";
		$csv_line[$csvi++]  	= isset($u_info['recording_ondemand'])?$u_info['recording_ondemand']:"disabled";
		$csv_line[$csvi++]  	= isset($u_info['recording_priority'])?$u_info['recording_priority']:"10";
		$csv_line[$csvi++] 	= isset($add_xactview)?$add_xactview:"0";
		$csv_line[$csvi++]   	= isset($xactview_autoanswer)?$xactview_autoanswer:"0";
		$csv_line[$csvi++]   	= isset($xactview_email)?$xactview_email:"";
		$csv_line[$csvi++]   	= isset($xactview_cell)?$xactview_cell:"";
		$csv_line[$csvi++]   	= isset($jabber_host)?$jabber_host:"";
		$csv_line[$csvi++]   	= isset($jabber_domain)?$jabber_domain:"";
		$csv_line[$csvi++]  	= isset($jabber_resource)?$jabber_resource:"";
		$csv_line[$csvi++]  	= isset($jabber_port)?$jabber_port:"5222";
		$csv_line[$csvi++]  	= isset($jabber_username)?$jabber_username:"";
		$csv_line[$csvi++]  	= isset($jabber_password)?$jabber_password:"";
		$csv_line[$csvi++]  	= isset($xactview_createprofile)?$xactview_createprofile:"0";
		$csv_line[$csvi++]  	= isset($xactview_profilepassword)?$xactview_profilepassword:"";
		$csv_line[$csvi++]  	= isset($xmpp_settings["username"])?$xmpp_settings["username"]:"";
		$csv_line[$csvi++]  	= isset($xmpp_settings["password"])?$xmpp_settings["password"]:"";

		for ($i = 0; $i < count($csv_line); $i++) {
			/* If the string contains a comma, enclose it in double-quotes. */
			if (strpos($csv_line[$i], ",") !== FALSE) {
				$csv_line[$i] = "\"" . $csv_line[$i] . "\"";
			}
			if ($i != count($csv_line) - 1) {
				$data = $data . $csv_line[$i] . ",";
			} else {
				$data = $data . $csv_line[$i];
			}
		}
		$data = $data . "\n";
		unset($csv_line);
	}
	bulkextensions_force_download($data, $fname);
	return;
}

function bulkextensions_get_all_exts() {
	$sql 	= "SELECT extension FROM users ORDER BY extension";
	$extens = sql($sql,"getAll");
	if (isset($extens)) {
		return $extens;
	} else {
		return null;
	}
}

function bulkextensions_force_download ($data, $name, $mimetype="", $filesize=false) {
    // File size not set?
    if ($filesize == false OR !is_numeric($filesize)) {
        $filesize = strlen($data);
    }
    // Mimetype not set?
    if (empty($mimetype)) {
        $mimetype = "application/octet-stream";
    }
    // Make sure there's not anything else left
    bulkextensions_ob_clean_all();
    // Start sending headers
    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false); // required for certain browsers
    header("Content-Transfer-Encoding: binary");
    header("Content-Type: " . $mimetype);
    header("Content-Length: " . $filesize);
    header("Content-Disposition: attachment; filename=\"" . $name . "\";" );
    // Send data
    echo $data;
    die();
}

function bulkextensions_ob_clean_all () {
    $ob_active = ob_get_length () !== false;
    while($ob_active) {
        ob_end_clean();
        $ob_active = ob_get_length () !== false;
    }
    return true;
}

function bulkextensions_generate_table_rows() {
	$langcookie =  $_COOKIE['lang'];
	if (file_exists("modules/bulkextensions/i18n/$langcookie/LC_MESSAGES/table.csv")) {		// check if translated file exists
		$fh = fopen("modules/bulkextensions/i18n/$langcookie/LC_MESSAGES/table.csv", "r");	// open it
    		} else { 										// nope, no translated file was found, open the default one
	        $fh = fopen("modules/bulkextensions/table.csv", "r");
    		}
        if ($fh == NULL) {
                return NULL;
	}
	$k = 0;
	while (($csv_data = fgetcsv($fh, 1000, ",", "\"")) !== FALSE) {
		$k++;
		/* Name,Default,Allowed,On Extensions page,Details */
		for ($i = 0; $i < 5; $i++) {
			if (isset($csv_data[$i])) {
    				$table[$k][$i] = $csv_data[$i];
			} else {
				$table[$k][$i] = "";
			}
		}
	}
	fclose($fh);
	return $table;
}

// Function to add extensions destination.
// Takes two parameters:
// $destvars = array of the three destinations
// $extension = the extension to add the destination
function bulkextensions_dest_add($destvars, $extension)
{
extract ($destvars);
$sql="UPDATE `users` set `noanswer_dest`='$noanswer_dest', `busy_dest`='$busy_dest', `chanunavail_dest`='$chanunavail_dest' WHERE `extension`='$extension'";
sql($sql);
}
?>
