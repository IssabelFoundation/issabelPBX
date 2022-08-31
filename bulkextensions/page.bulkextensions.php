<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

//    License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Copyright 2008 sasargen
//  Portions Copyright 2009, 2010, 2011 Mikael Carlsson, mickecamino@gmail.com
//    Copyright 2013 Schmooze Com Inc.
//
include('bulkextensions.inc.php');

// This is a long running process, so extend time limit for execution.
// Typical PHP default is 30 seconds, but this only allows 100 to 200
// extensions to be processed. Setting time limit to 3000 seconds allows
// 10000 to 20000 extensions to be processed.
set_time_limit(3000);
// $change is used as a flag whether or not a reload is needed. If no changes
// are made, no reload will be prompted.
$change = false;
$output = "";
$action = isset($_REQUEST["csv_type"])?$_REQUEST["csv_type"]:'';
global $db;

if ($action == "output") {
    bulkextensions_exportextensions_allusers();
} elseif ($action == "input") {
    // Set email notification variables
    if (isset($_REQUEST["default_email"])) {
        $default_email = $_REQUEST["default_email"];
    } else {
        $default_email = "";
    }
    if (isset($_REQUEST["override_email"])) {
        $override_email = $_REQUEST["override_email"];
    } else {
        $override_email = "";
    }
    if (isset($_REQUEST["email_from"])) {
        $email_from = $_REQUEST["email_from"];
    } else {
        $email_from = "";
    }
    if (isset($_REQUEST["email_replyto"])) {
        $email_replyto = $_REQUEST["email_replyto"];
    } else {
        $email_replyto = "";
    }
    if (isset($_REQUEST["email_subject"])) {
        $email_subject = $_REQUEST["email_subject"];
    } else {
        $email_subject = "";
    }
    if (isset($_REQUEST["email_body_open"])) {
        $email_body_open = $_REQUEST["email_body_open"];
    } else {
        $email_body_open = "";
    }
    if (isset($_REQUEST["email_body_close"])) {
        $email_body_close = $_REQUEST["email_body_close"];
    } else {
        $email_body_close = "";
    }
    $line_end = "\n";
    $aFields = array (
        "action" => array(false, -1),
        "extension" => array(false, -1),
        "name" => array(false, -1),
        "cid_masquerade" => array(false, -1),
        "sipname" => array(false, -1),
        "outboundcid" => array(false, -1),
        "ringtimer" => array(false, -1),
        "callwaiting" => array(false, -1),
        "call_screen" => array(false, -1),
        "pinless" => array(false, -1),
        "password" => array(false, -1),
        "noanswer_dest" => array(false, -1),
        "noanswer_cid" => array(false, -1),
        "busy_dest" => array(false, -1),
        "busy_cid" => array(false, -1),
        "chanunavail_dest" => array(false, -1),
        "chanunavail_cid" => array(false, -1),
        "emergency_cid" => array(false, -1),
        "tech" => array(false, -1),
        "hardware" => array(false, -1),
        "devinfo_channel" => array(false, -1),                // for zap devices
        "devinfo_secret" => array(false, -1),
        "devinfo_notransfer" => array(false, -1),             // for iax2 devices
        "devinfo_dtmfmode" => array(false, -1),               // used in core\core_devices_add<sip|zap|iax2>()
        "devinfo_canreinvite" => array(false, -1),            // used in core\core_devices_add<sip|zap|iax2>()
        "devinfo_context" => array(false, -1),
        "devinfo_immediate" => array(false, -1),              // for zap devices
        "devinfo_signalling" => array(false, -1),                // for zap devices
        "devinfo_echocancel" => array(false, -1),                // for zap devices
        "devinfo_echocancelwhenbridged" => array(false, -1),  // for zap devices
        "devinfo_echotraining" => array(false, -1),           // for zap devices
        "devinfo_busydetect" => array(false, -1),             // for zap devices
        "devinfo_busycount" => array(false, -1),              // for zap devices
        "devinfo_callprogress" => array(false, -1),
        "devinfo_host" => array(false, -1),
        "devinfo_type" => array(false, -1),
        "devinfo_nat" => array(false, -1),
        "devinfo_port" => array(false, -1),
        "devinfo_qualify" => array(false, -1),
        "devinfo_callgroup" => array(false, -1),
        "devinfo_pickupgroup" => array(false, -1),
        "devinfo_disallow" => array(false, -1),
        "devinfo_allow" => array(false, -1),
        "devinfo_dial" => array(false, -1),
        "devinfo_accountcode" => array(false, -1),
        "devinfo_mailbox" => array(false, -1),
        "devinfo_deny" => array(false, -1),
        "devinfo_permit" => array(false, -1),
        "devicetype" => array(false, -1),
        "deviceid" => array(false, -1),
        "deviceuser" => array(false, -1),
        "description" => array(false, -1),
        "dictenabled" => array(false, -1),
        "dictformat" => array(false, -1),
        "dictemail" => array(false, -1),
        "langcode" => array(false, -1),
        "record_in" => array(false, -1),
        "record_out" => array(false, -1),
        "vm" => array(false, -1),
        "vmpwd" => array(false, -1),
        "email" => array(false, -1),
        "pager" => array(false, -1),
        "attach" => array(false, -1),
        "saycid" => array(false, -1),
        "envelope" => array(false, -1),
        "delete" => array(false, -1),
        "options" => array(false, -1),
        "vmcontext" => array(false, -1),
        "vmx_state" => array(false, -1),
        "vmx_unavail_enabled" => array(false, -1),
        "vmx_busy_enabled" => array(false, -1),
        "vmx_play_instructions" => array(false, -1),
        "vmx_option_0_system_default" => array(false, -1),
        "vmx_option_0_number" => array(false, -1),
        "vmx_option_1_system_default" => array(false, -1),
        "vmx_option_1_number" => array(false, -1),
        "vmx_option_2_number" => array(false, -1),
        "account" => array(false, -1),
        "ddial" => array(false, -1),
        "pre_ring" => array(false, -1),
        "strategy" => array(false, -1),
        "grptime" => array(false, -1),
        "grplist" => array(false, -1),
        "annmsg_id" => array(false, -1),
        "ringing" => array(false, -1),
        "grppre" => array(false, -1),
        "dring" => array(false, -1),
        "needsconf" => array(false, -1),
        "remotealert_id" => array(false, -1),
        "toolate_id" => array(false, -1),
        "postdest" => array(false, -1),
        "faxenabled" => array(false, -1),
        "faxemail" => array(false, -1),
        "cfringtimer" => array(false, -1),
        "concurrency_limit" => array(false, -1),
        "answermode" => array(false, -1),
        "qnostate" => array(false, -1),
        "devinfo_trustrpid" => array(false, -1),
        "devinfo_sendrpid" => array(false, -1),
        "devinfo_qualifyfreq" => array(false, -1),
        "devinfo_transport" => array(false, -1),
        "devinfo_encryption" => array(false, -1),
        "devinfo_vmexten" => array(false, -1),
        "cc_agent_policy" => array(false, -1),
        "cc_monitor_policy" => array(false, -1),
        "recording_in_external" => array(false, -1),
        "recording_out_external" => array(false, -1),
        "recording_in_internal" => array(false, -1),
        "recording_out_internal" => array(false, -1),
        "recording_ondemand" => array(false, -1),
        "recording_priority" => array(false, -1),
        "add_xactview" => array(false, -1),
        "xactview_autoanswer" => array(false, -1),
        "xactview_email" => array(false, -1),
        "xactview_cell" => array(false, -1),
        "jabber_host" => array(false, -1),
        "jabber_domain" => array(false, -1),
        "jabber_resource" => array(false, -1),
        "jabber_port" => array(false, -1),
        "jabber_username" => array(false, -1),
        "jabber_password" => array(false, -1),
        "xactview_createprofile" => array(false, -1),
        "xactview_profilepassword" => array(false, -1),
        "xmpp_user" => array(false, -1),
        "xmpp_pass" => array(false,-1)
    );

    $fh = fopen($_FILES["csvFile"]["tmp_name"], "r");
    if ($fh == NULL) {
        $file_ok = FALSE;
    } else {
        $file_ok = TRUE;
    }

    $k = 0;
    $i=0;
    while ($file_ok && (($aInfo = fgetcsv($fh, 2000, ",", "\"")) !== FALSE)) {
        $k++;
        if (empty($aInfo[0])) {
            continue;
        }

        // If this is the first row then we need to check each field listed (these are the headings)
        if ($i==0) {
            for ($j=0; $j<count($aInfo); $j++) {
                $aKeys = array_keys($aFields);
                foreach ($aKeys as $sKey) {
                    if ($aInfo[$j] == $sKey) {
                        $aFields[$sKey][0] = true;
                        $aFields[$sKey][1] = $j;
                    }
                }
            }
            $i++;
            $output .= sprintf(_("Row %s: Headers parsed."),$k)."<br>";
            continue;
        }

        //reset destvars array or else we end up with extensions sharing destinations
        $destvars = array();

        if ($aFields["action"][0]) {
            $vars["action"] = trim($aInfo[$aFields["action"][1]]);
        }

        if ($aFields["extension"][0]) {
            $vars["extension"]  = trim($aInfo[$aFields["extension"][1]]);
            $vars["extdisplay"] = trim($aInfo[$aFields["extension"][1]]);
        }

        if ($aFields["name"][0]) {
            $vars["name"] = trim($aInfo[$aFields["name"][1]]);
        }

        if ($aFields["cid_masquerade"][0]) {
            $vars["cid_masquerade"] = trim($aInfo[$aFields["cid_masquerade"][1]]);
        }

        if ($aFields["sipname"][0]) {
            $vars["sipname"] = trim($aInfo[$aFields["sipname"][1]]);
        }

        if ($aFields["outboundcid"][0]) {
            $vars["outboundcid"] = trim($aInfo[$aFields["outboundcid"][1]]);
        }

        if ($aFields["ringtimer"][0]) {
            $vars["ringtimer"] = trim($aInfo[$aFields["ringtimer"][1]]);
        }

        if ($aFields["callwaiting"][0]) {
            $vars["callwaiting"] = trim($aInfo[$aFields["callwaiting"][1]]);
        }

        if ($aFields["call_screen"][0]) {
            $vars["call_screen"] = trim($aInfo[$aFields["call_screen"][1]]);
        }

        if ($aFields["pinless"][0]) {
            $vars["pinless"] = trim($aInfo[$aFields["pinless"][1]]);
        }

        if ($aFields["password"][0]) {
            $vars["password"] = trim($aInfo[$aFields["password"][1]]);
        }

        if ($aFields["noanswer_dest"][0]) {
            if (!isset($aInfo[$aFields["noanswer_dest"][1]]) || ($aInfo[$aFields["noanswer_dest"][1]] == "")){
                unset($vars["noanswer_dest"]);
            }
            else {
                $destvars["noanswer_dest"] = trim($aInfo[$aFields["noanswer_dest"][1]]);
            }
        }

        if ($aFields["noanswer_cid"][0]) {
            $vars["noanswer_cid"] = trim($aInfo[$aFields["noanswer_cid"][1]]);
        }

        if ($aFields["busy_dest"][0]) {
            if (!isset($aInfo[$aFields["busy_dest"][1]]) || ($aInfo[$aFields["busy_dest"][1]] == "")){
                unset($vars["busy_dest"]);
            }
            else {
                $destvars["busy_dest"] = trim($aInfo[$aFields["busy_dest"][1]]);
            }
        }
        if ($aFields["busy_cid"][0]) {
            $vars["busy_cid"] = trim($aInfo[$aFields["busy_cid"][1]]);
        }

        if ($aFields["chanunavail_dest"][0]) {
            if (!isset($aInfo[$aFields["chanunavail_dest"][1]]) || ($aInfo[$aFields["chanunavail_dest"][1]] == "")){
                unset($vars["chanunavail_dest"]);
            }
            else {
                $destvars["chanunavail_dest"] = trim($aInfo[$aFields["chanunavail_dest"][1]]);
            }
        }

        if ($aFields["chanunavail_cid"][0]) {
            $vars["chanunavail_cid"] = trim($aInfo[$aFields["chanunavail_cid"][1]]);
        }

        if ($aFields["emergency_cid"][0]) {
            $vars["emergency_cid"] = trim($aInfo[$aFields["emergency_cid"][1]]);
        }

        if ($aFields["tech"][0]) {
            $vars["tech"] = trim($aInfo[$aFields["tech"][1]]);
        }

        if ($aFields["hardware"][0]) {
            $vars["hardware"] = trim($aInfo[$aFields["hardware"][1]]);
        }

        if ($aFields["devinfo_channel"][0]) {
            if (!isset($aInfo[$aFields["devinfo_channel"][1]]) || ($aInfo[$aFields["devinfo_channel"][1]] == "")){
                unset($vars["devinfo_channel"]);
            }
            else {
                $vars["devinfo_channel"] = trim($aInfo[$aFields["devinfo_channel"][1]]);
            }
        }

        if ($aFields["devinfo_secret"][0]) {
            $vars["devinfo_secret"] = trim($aInfo[$aFields["devinfo_secret"][1]]);
        }

        if ($aFields["devinfo_notransfer"][0]) {
            if (!isset($aInfo[$aFields["devinfo_notransfer"][1]]) || ($aInfo[$aFields["devinfo_notransfer"][1]] == "")){
                unset($vars["devinfo_notransfer"]);
            }
            else {
                $vars["devinfo_notransfer"] = trim($aInfo[$aFields["devinfo_notransfer"][1]]);
            }
        }

        if ($aFields["devinfo_dtmfmode"][0]) {
            $vars["devinfo_dtmfmode"] = trim($aInfo[$aFields["devinfo_dtmfmode"][1]]);
        }

        if ($aFields["devinfo_canreinvite"][0]) {
            $vars["devinfo_canreinvite"] = trim($aInfo[$aFields["devinfo_canreinvite"][1]]);
        }

        if ($aFields["devinfo_context"][0]) {
            $vars["devinfo_context"] = trim($aInfo[$aFields["devinfo_context"][1]]);
        }

        if ($aFields["devinfo_immediate"][0]) {
            if (!isset($aInfo[$aFields["devinfo_immediate"][1]]) || ($aInfo[$aFields["devinfo_immediate"][1]] == "")){
                unset($vars["devinfo_immediate"]);
            }
            else {
                $vars["devinfo_immediate"] = trim($aInfo[$aFields["devinfo_immediate"][1]]);
            }
        }

        if ($aFields["devinfo_signalling"][0]) {
            if (!isset($aInfo[$aFields["devinfo_signalling"][1]]) || ($aInfo[$aFields["devinfo_signalling"][1]] == "")){
                unset($vars["devinfo_signalling"]);
            }
            else {
                $vars["devinfo_signalling"] = trim($aInfo[$aFields["devinfo_signalling"][1]]);
            }
        }

        if ($aFields["devinfo_echocancel"][0]) {
            if (!isset($aInfo[$aFields["devinfo_echocancel"][1]]) || ($aInfo[$aFields["devinfo_echocancel"][1]] == "")){
                unset($vars["devinfo_echocancel"]);
            }
            else {
                $vars["devinfo_echocancel"] = trim($aInfo[$aFields["devinfo_echocancel"][1]]);
            }
        }

        if ($aFields["devinfo_echocancelwhenbridged"][0]) {
            if (!isset($aInfo[$aFields["devinfo_echocancelwhenbridged"][1]]) || ($aInfo[$aFields["devinfo_echocancelwhenbridged"][1]] == "")){
                unset($vars["devinfo_echocancelwhenbridged"]);
            }
            else {
                $vars["devinfo_echocancelwhenbridged"] = trim($aInfo[$aFields["devinfo_echocancelwhenbridged"][1]]);
            }
        }

        if ($aFields["devinfo_echotraining"][0]) {
            if (!isset($aInfo[$aFields["devinfo_echotraining"][1]]) || ($aInfo[$aFields["devinfo_echotraining"][1]] == "")){
                unset($vars["devinfo_echotraining"]);
            }
            else {
                $vars["devinfo_echotraining"] = trim($aInfo[$aFields["devinfo_echotraining"][1]]);
            }
        }

        if ($aFields["devinfo_busydetect"][0]) {
            if (!isset($aInfo[$aFields["devinfo_busydetect"][1]]) || ($aInfo[$aFields["devinfo_busydetect"][1]] == "")){
                unset($vars["devinfo_busydetect"]);
            }
            else {
                $vars["devinfo_busydetect"] = trim($aInfo[$aFields["devinfo_busydetect"][1]]);
            }
        }

        if ($aFields["devinfo_busycount"][0]) {
            if (!isset($aInfo[$aFields["devinfo_busycount"][1]]) || ($aInfo[$aFields["devinfo_busycount"][1]] == "")){
                unset($vars["devinfo_busycount"]);
            }
            else {
                $vars["devinfo_busycount"] = trim($aInfo[$aFields["devinfo_busycount"][1]]);
            }
        }

        if ($aFields["devinfo_callprogress"][0]) {
            if (!isset($aInfo[$aFields["devinfo_callprogress"][1]]) || ($aInfo[$aFields["devinfo_callprogress"][1]] == "")){
                unset($vars["devinfo_callprogress"]);
            }
            else {
                $vars["devinfo_callprogress"] = trim($aInfo[$aFields["devinfo_callprogress"][1]]);
            }
        }

        if ($aFields["devinfo_host"][0]) {
            $vars["devinfo_host"] = trim($aInfo[$aFields["devinfo_host"][1]]);
        }

        if ($aFields["devinfo_type"][0]) {
            $vars["devinfo_type"] = trim($aInfo[$aFields["devinfo_type"][1]]);
        }

        if ($aFields["devinfo_nat"][0]) {
            $vars["devinfo_nat"] = trim($aInfo[$aFields["devinfo_nat"][1]]);
        }

        if ($aFields["devinfo_port"][0]) {
            $vars["devinfo_port"] = trim($aInfo[$aFields["devinfo_port"][1]]);
        }

        if ($aFields["devinfo_qualify"][0]) {
            $vars["devinfo_qualify"] = trim($aInfo[$aFields["devinfo_qualify"][1]]);
        }

        if ($aFields["devinfo_callgroup"][0]) {
            if (!isset($aInfo[$aFields["devinfo_callgroup"][1]]) || ($aInfo[$aFields["devinfo_callgroup"][1]] == "")){
                unset($vars["devinfo_callgroup"]);
            }
            else {
                $vars["devinfo_callgroup"] = trim($aInfo[$aFields["devinfo_callgroup"][1]]);
            }
        }

        if ($aFields["devinfo_pickupgroup"][0]) {
            if (!isset($aInfo[$aFields["devinfo_pickupgroup"][1]]) || ($aInfo[$aFields["devinfo_pickupgroup"][1]] == "")){
                unset($vars["devinfo_pickupgroup"]);
            }
            else {
                $vars["devinfo_pickupgroup"] = trim($aInfo[$aFields["devinfo_pickupgroup"][1]]);
            }
        }

        if ($aFields["devinfo_disallow"][0]) {
            if (!isset($aInfo[$aFields["devinfo_disallow"][1]]) || ($aInfo[$aFields["devinfo_disallow"][1]] == "")){
                unset($vars["devinfo_disallow"]);
            }
            else {
                $vars["devinfo_disallow"] = trim($aInfo[$aFields["devinfo_disallow"][1]]);
            }
        }

        if ($aFields["devinfo_allow"][0]) {
            if (!isset($aInfo[$aFields["devinfo_allow"][1]]) || ($aInfo[$aFields["devinfo_allow"][1]] == "")){
                unset($vars["devinfo_allow"]);
            }
            else {
                $vars["devinfo_allow"] = trim($aInfo[$aFields["devinfo_allow"][1]]);
            }
        }

        if ($aFields["devinfo_dial"][0]) {
            $vars["devinfo_dial"] = trim($aInfo[$aFields["devinfo_dial"][1]]);
        }

        if ($aFields["devinfo_accountcode"][0]) {
            if (!isset($aInfo[$aFields["devinfo_accountcode"][1]]) || ($aInfo[$aFields["devinfo_accountcode"][1]] == "")){
                unset($vars["devinfo_accountcode"]);
            }
            else {
                $vars["devinfo_accountcode"] = trim($aInfo[$aFields["devinfo_accountcode"][1]]);
            }
        }

        if ($aFields["devinfo_mailbox"][0]) {
            $vars["devinfo_mailbox"] = trim($aInfo[$aFields["devinfo_mailbox"][1]]);
        }

        if ($aFields["devinfo_deny"][0]) {
            // If field is empty fill in default 0.0.0.0/0.0.0.0
            if (!isset($aInfo[$aFields["devinfo_deny"][1]]) || ($aInfo[$aFields["devinfo_deny"][1]] == "")){
                $vars["devinfo_deny"] = "0.0.0.0/0.0.0.0";    // default value
            }
            else {
                $vars["devinfo_deny"] = trim($aInfo[$aFields["devinfo_deny"][1]]);
            }
        }

        if ($aFields["devinfo_permit"][0]) {
            // If field is empty fill in default 0.0.0.0/0.0.0.0                
            if (!isset($aInfo[$aFields["devinfo_deny"][1]]) || ($aInfo[$aFields["devinfo_permit"][1]] == "")){
                $vars["devinfo_permit"] = "0.0.0.0/0.0.0.0"; // default value
            }
            else {
                $vars["devinfo_permit"] = trim($aInfo[$aFields["devinfo_permit"][1]]);
            }
        }

        if ($aFields["devicetype"][0]) {
            $vars["devicetype"] = trim($aInfo[$aFields["devicetype"][1]]);
        }

        if ($aFields["deviceid"][0]) {
            $vars["deviceid"] = trim($aInfo[$aFields["deviceid"][1]]);
        }

        if ($aFields["deviceuser"][0]) {
            $vars["deviceuser"] = trim($aInfo[$aFields["deviceuser"][1]]);
        }

        if ($aFields["description"][0]) {
            $vars["description"] = trim($aInfo[$aFields["description"][1]]);
        }

        if ($aFields["dictenabled"][0]) {
            $vars["dictenabled"] = trim($aInfo[$aFields["dictenabled"][1]]);
        }

        if ($aFields["dictformat"][0]) {
            $vars["dictformat"] = trim($aInfo[$aFields["dictformat"][1]]);
        }

        if ($aFields["dictemail"][0]) {
            $vars["dictemail"] = trim($aInfo[$aFields["dictemail"][1]]);
        }

        if ($aFields["langcode"][0]) {
            $vars["langcode"] = trim($aInfo[$aFields["langcode"][1]]);
        }

        if ($aFields["record_in"][0]) {
            $vars["record_in"] = trim($aInfo[$aFields["record_in"][1]]);
        }

        if ($aFields["record_out"][0]) {
            $vars["record_out"] = trim($aInfo[$aFields["record_out"][1]]);
        }

        if ($aFields["vm"][0]) {
            $vars["vm"] = trim($aInfo[$aFields["vm"][1]]);
        }

        if ($aFields["vmpwd"][0]) {
            $vars["vmpwd"] = trim($aInfo[$aFields["vmpwd"][1]]);
        }

        if ($aFields["email"][0]) {
            $vars["email"] = trim($aInfo[$aFields["email"][1]]);
        }

        if ($aFields["pager"][0]) {
            $vars["pager"] = trim($aInfo[$aFields["pager"][1]]);
        }

        if ($aFields["attach"][0]) {
            $vars["attach"] = trim($aInfo[$aFields["attach"][1]]);
        }

        if ($aFields["saycid"][0]) {
            $vars["saycid"] = trim($aInfo[$aFields["saycid"][1]]);
        }

        if ($aFields["envelope"][0]) {
            $vars["envelope"] = trim($aInfo[$aFields["envelope"][1]]);
        }

        if ($aFields["delete"][0]) {
            $vars["delete"] = trim($aInfo[$aFields["delete"][1]]);
        }

        if ($aFields["options"][0]) {
            $vars["options"] = trim($aInfo[$aFields["options"][1]]);
        }

        if ($aFields["vmcontext"][0]) {
            if (!isset($aInfo[$aFields["vmcontext"][1]]) || ($aInfo[$aFields["vmcontext"][1]] == "")){
                $vars["vmcontext"] = 'default';
            } else {
                $vars["vmcontext"] = trim($aInfo[$aFields["vmcontext"][1]]);
            }
        }

        if ($aFields["vmx_state"][0]) {
            $vars["vmx_state"] = trim($aInfo[$aFields["vmx_state"][1]]);
        }

        if ($aFields["vmx_unavail_enabled"][0]) {
            $vars["vmx_unavail_enabled"] = trim($aInfo[$aFields["vmx_unavail_enabled"][1]]);
        }

        if ($aFields["vmx_busy_enabled"][0]) {
            $vars["vmx_busy_enabled"] = trim($aInfo[$aFields["vmx_busy_enabled"][1]]);
        }

        if ($aFields["vmx_play_instructions"][0]) {
            $vars["vmx_play_instructions"] = trim($aInfo[$aFields["vmx_play_instructions"][1]]);
        }

        if ($aFields["vmx_option_0_system_default"][0]) {
            $vars["vmx_option_0_system_default"] = trim($aInfo[$aFields["vmx_option_0_system_default"][1]]);
        }

        if ($aFields["vmx_option_0_number"][0]) {
            $vars["vmx_option_0_number"] = trim($aInfo[$aFields["vmx_option_0_number"][1]]);
        }

        if ($aFields["vmx_option_1_system_default"][0]) {
            $vars["vmx_option_1_system_default"] = trim($aInfo[$aFields["vmx_option_1_system_default"][1]]);
        }

        if ($aFields["vmx_option_1_number"][0]) {
            $vars["vmx_option_1_number"] = trim($aInfo[$aFields["vmx_option_1_number"][1]]);
        }

        if ($aFields["vmx_option_2_number"][0]) {
            $vars["vmx_option_2_number"] = trim($aInfo[$aFields["vmx_option_2_number"][1]]);
        }

        if ($aFields["account"][0]) {
            $vars["account"] = trim($aInfo[$aFields["account"][1]]);
            if ($vars["account"] == $vars["extension"]) {
                $followme_set = TRUE;   /* indicate we have follow me settings to set */
            } else {
                $followme_set = FALSE;
            }
        }

        if ($aFields["ddial"][0]) {
            $vars["ddial"] = trim($aInfo[$aFields["ddial"][1]]);
        }

        if ($aFields["pre_ring"][0]) {
            $vars["pre_ring"] = trim($aInfo[$aFields["pre_ring"][1]]);
        }

        if ($aFields["strategy"][0]) {
            $vars["strategy"] = trim($aInfo[$aFields["strategy"][1]]);
        }

        if ($aFields["grptime"][0]) {
            $vars["grptime"] = trim($aInfo[$aFields["grptime"][1]]);
        }

        if ($aFields["grplist"][0]) {
            $vars["grplist"] = trim($aInfo[$aFields["grplist"][1]]);
        }

        if ($aFields["annmsg_id"][0]) {
            $vars["annmsg_id"] = trim($aInfo[$aFields["annmsg_id"][1]]);
        }

        if ($aFields["ringing"][0]) {
            $vars["ringing"] = trim($aInfo[$aFields["ringing"][1]]);
        }

        if ($aFields["grppre"][0]) {
            $vars["grppre"] = trim($aInfo[$aFields["grppre"][1]]);
        }

        if ($aFields["dring"][0]) {
            $vars["dring"] = trim($aInfo[$aFields["dring"][1]]);
        }

        if ($aFields["needsconf"][0]) {
            $vars["needsconf"] = trim($aInfo[$aFields["needsconf"][1]]);
        }

        if ($aFields["remotealert_id"][0]) {
            $vars["remotealert_id"] = trim($aInfo[$aFields["remotealert_id"][1]]);
        }

        if ($aFields["toolate_id"][0]) {
            $vars["toolate_id"] = trim($aInfo[$aFields["toolate_id"][1]]);
        }

        if ($aFields["postdest"][0]) {
            $vars["postdest"] = trim($aInfo[$aFields["postdest"][1]]);
        }

        if ($aFields["faxenabled"][0]) {
            if (!isset($aInfo[$aFields["faxenabled"][1]]) || ($aInfo[$aFields["faxenabled"][1]] == "")){
                unset($vars["faxenabled"]);
            } else {
                $vars["faxenabled"] = trim($aInfo[$aFields["faxenabled"][1]]);
            }
        }

        if ($aFields["faxemail"][0]) {
            if (!isset($aInfo[$aFields["faxemail"][1]]) || ($aInfo[$aFields["faxemail"][1]] == "")){
                unset($vars["faxemail"]);
            } else {
                $vars["faxemail"] = trim($aInfo[$aFields["faxemail"][1]]);
            }
        }

        if ($aFields["cfringtimer"][0]) {
            $vars["cfringtimer"] = trim($aInfo[$aFields["cfringtimer"][1]]);
        }
        if ($aFields["concurrency_limit"][0]) {
            $vars["concurrency_limit"] = trim($aInfo[$aFields["concurrency_limit"][1]]);
        }
        if ($aFields["answermode"][0]) {
            $vars["answermode"] = trim($aInfo[$aFields["answermode"][1]]);
        }
        if ($aFields["qnostate"][0]) {
            $vars["qnostate"] = trim($aInfo[$aFields["qnostate"][1]]);
        }
        if ($aFields["devinfo_trustrpid"][0]) {
            $vars["devinfo_trustrpid"] = trim($aInfo[$aFields["devinfo_trustrpid"][1]]);
        }
        if ($aFields["devinfo_sendrpid"][0]) {
            $vars["devinfo_sendrpid"] = trim($aInfo[$aFields["devinfo_sendrpid"][1]]);
        }
        if ($aFields["devinfo_qualifyfreq"][0]) {
            $vars["devinfo_qualifyfreq"] = trim($aInfo[$aFields["devinfo_qualifyfreq"][1]]);
        }
        if ($aFields["devinfo_transport"][0]) {
            $vars["devinfo_transport"] = trim($aInfo[$aFields["devinfo_transport"][1]]);
        }
        if ($aFields["devinfo_encryption"][0]) {
            $vars["devinfo_encryption"] = trim($aInfo[$aFields["devinfo_encryption"][1]]);
        }
        if ($aFields["devinfo_vmexten"][0]) {
            $vars["devinfo_vmexten"] = trim($aInfo[$aFields["devinfo_vmexten"][1]]);
        }
        if ($aFields["cc_agent_policy"][0]) {
            $vars["cc_agent_policy"] = trim($aInfo[$aFields["cc_agent_policy"][1]]);
        }
        if ($aFields["cc_monitor_policy"][0]) {
            $vars["cc_monitor_policy"] = trim($aInfo[$aFields["cc_monitor_policy"][1]]);
        }
        if ($aFields["recording_in_external"][0]) {
            if (isset($aInfo[$aFields["recording_in_external"][1]]) || ($aInfo[$aFields["recording_in_external"][1]] != "")){
                $vars["recording_in_external"] = 'recording_in_external='.trim($aInfo[$aFields["recording_in_external"][1]]);
            }
        }
        if ($aFields["recording_out_external"][0]) {
            if (isset($aInfo[$aFields["recording_out_external"][1]]) || ($aInfo[$aFields["recording_out_external"][1]] != "")){
                $vars["recording_out_external"] = 'recording_out_external='.trim($aInfo[$aFields["recording_out_external"][1]]);
            }
        }
        if ($aFields["recording_in_internal"][0]) {
            if (isset($aInfo[$aFields["recording_in_internal"][1]]) || ($aInfo[$aFields["recording_in_internal"][1]] != "")){
                $vars["recording_in_internal"] = 'recording_in_internal='.trim($aInfo[$aFields["recording_in_internal"][1]]);
            }
        }
        if ($aFields["recording_out_internal"][0]) {
            if (isset($aInfo[$aFields["recording_out_internal"][1]]) || ($aInfo[$aFields["recording_out_internal"][1]] != "")){
                $vars["recording_out_internal"] = 'recording_out_internal='.trim($aInfo[$aFields["recording_out_internal"][1]]);
            }
        }
        if ($aFields["recording_ondemand"][0]) {
            if (isset($aInfo[$aFields["recording_ondemand"][1]]) || ($aInfo[$aFields["recording_ondemand"][1]] != "")){
                $vars["recording_ondemand"] = 'recording_ondemand='.trim($aInfo[$aFields["recording_ondemand"][1]]);
            }
        }
        if ($aFields["recording_priority"][0]) {
            $vars["recording_priority"] = trim($aInfo[$aFields["recording_priority"][1]]);
        }
        if ($aFields["xactview_email"][0]) {
            $vars["xactview_email"] = trim($aInfo[$aFields["xactview_email"][1]]);
        }
        if ($aFields["xactview_cell"][0]) {
            $vars["xactview_cell"] = trim($aInfo[$aFields["xactview_cell"][1]]);
        }
        if ($aFields["jabber_host"][0]) {
            $vars["jabber_host"] = trim($aInfo[$aFields["jabber_host"][1]]);
        }
        if ($aFields["jabber_domain"][0]) {
            $vars["jabber_domain"] = trim($aInfo[$aFields["jabber_domain"][1]]);
        }
        if ($aFields["jabber_resource"][0]) {
            if (!isset($aInfo[$aFields["jabber_resource"][1]]) || ($aInfo[$aFields["jabber_resource"][1]] == "")){
                $vars["jabber_resource"] = "XactView"; //default
            } else {
                $vars["jabber_resource"] = trim($aInfo[$aFields["jabber_resource"][1]]);
            }
        }
        if ($aFields["jabber_port"][0]) {
            if (!isset($aInfo[$aFields["jabber_port"][1]]) || ($aInfo[$aFields["jabber_port"][1]] == "")){
                $vars["jabber_port"] = "5222"; //default
            } else {
                $vars["jabber_port"] = trim($aInfo[$aFields["jabber_port"][1]]);
            }
        }
        if ($aFields["jabber_username"][0]) {
            $vars["jabber_username"] = trim($aInfo[$aFields["jabber_username"][1]]);
        }
        if ($aFields["jabber_password"][0]) {
            $vars["jabber_password"] = trim($aInfo[$aFields["jabber_password"][1]]);
        }
        if ($aFields["xactview_createprofile"][0]) {
            if (!isset($aInfo[$aFields["xactview_createprofile"][1]]) || ($aInfo[$aFields["xactview_createprofile"][1]] == "")){
                $vars["xactview_createprofile"] = "0"; //default
            } else {
                $vars["xactview_createprofile"] = trim($aInfo[$aFields["xactview_createprofile"][1]]);
            }
        }
        if ($aFields["xactview_profilepassword"][0]) {
            $vars["xactview_profilepassword"] = trim($aInfo[$aFields["xactview_profilepassword"][1]]);
        }
        if ($aFields["add_xactview"][0]) {
            $vars["add_xactview"] = trim($aInfo[$aFields["add_xactview"][1]]);
        }
        if ($aFields["xactview_autoanswer"][0]) {
            $vars["xactview_autoanswer"] = trim($aInfo[$aFields["xactview_autoanswer"][1]]);
        }
        if ($aFields["xactview_email"][0]) {
            $vars["xactview_email"] = trim($aInfo[$aFields["xactview_email"][1]]);
        }
        if ($aFields["xactview_cell"][0]) {
            $vars["xactview_cell"] = trim($aInfo[$aFields["xactview_cell"][1]]);
        }
        if ($aFields["jabber_host"][0]) {
            $vars["jabber_host"] = trim($aInfo[$aFields["jabber_host"][1]]);
        }
        if ($aFields["jabber_domain"][0]) {
            $vars["jabber_domain"] = trim($aInfo[$aFields["jabber_domain"][1]]);
        }
        if ($aFields["jabber_resource"][0]) {
            $vars["jabber_resource"] = trim($aInfo[$aFields["jabber_resource"][1]]);
        }
        if ($aFields["jabber_port"][0]) {
            $vars["jabber_port"] = trim($aInfo[$aFields["jabber_port"][1]]);
        }
        if ($aFields["jabber_username"][0]) {
            $vars["jabber_username"] = trim($aInfo[$aFields["jabber_username"][1]]);
        }
        if ($aFields["jabber_password"][0]) {
            $vars["jabber_password"] = trim($aInfo[$aFields["jabber_password"][1]]);
        }
        if ($aFields["xmpp_user"][0]) {
            $vars["xmpp_user"] = trim($aInfo[$aFields["xmpp_user"][1]]);
        }
        if ($aFields["xmpp_pass"][0]) {
            $vars["xmpp_pass"] = trim($aInfo[$aFields["xmpp_pass"][1]]);
        }
        /* Needed fields for creating a Follow Me are account (aka grpnum), strategy, grptime,  */
        /* grplist and pre_ring.                                                                */
        if ($followme_set) {
            if (!isset($vars["strategy"]) || ($vars["strategy"] == "")) {
                $vars["strategy"] = "ringallv2";        // default value
            }

            if(!isset($vars["grptime"]) || ($vars["grptime"] == "")) {
                $vars["grptime"] = "20";                // default value
            }

            if(!isset($vars["grplist"]) || ($vars["grplist"] == "")) {
                $vars["grplist"] = $vars["extension"];  // default value
            }

            if(!isset($vars["pre_ring"]) || ($vars["pre_ring"] == "")) {
                $vars["pre_ring"] = "0";                    // default value
            }
        }

        if (!(isset($amp_conf["AMPEXTENSIONS"]) && ($amp_conf["AMPEXTENSIONS"] == "deviceanduser"))) {
            $vars["devicetype"] = "fixed";
            $vars["deviceid"] = $vars["deviceuser"] = $vars["extension"];
            $vars["description"] = $vars["name"];
        } else {
            /* deviceid is required; if issabelpbx is in devicesandusers mode, deviceid cannot be left blank. */
            if ($vars["deviceid"] == "") {
                $vars["deviceid"] = $vars["extension"];
            }
        }

        $vars["display"] = "bulkextensions";
        $vars["type"] = "tool";

        $_REQUEST = $vars;

        if (checkRange($vars["extension"])) {
            switch ($vars["action"]) {
            case "add":
                // Only add if no Voicemail, no user and no device entry already
                // exist for the extension we're trying to add.
                // Check the list of Voicemail entries.
                // user_vmexists == false means add  new Voicemail entry.
                $user_vmexists = FALSE;
                if ($vm_exists) {
                    $uservm = voicemail_getVoicemail();
                    $vmcontexts = array_keys($uservm);
                    foreach ($vmcontexts as $vmcontext) {
                        if (isset($uservm[$vmcontext][$vars["extension"]])) {
                            $user_vmexists = TRUE;      // DO NOT add.
                        }
                    }
                }
                if ($user_vmexists || core_users_get($vars["extension"]) || core_devices_get($vars["extension"])) {
                    $output .= sprintf(_("Row %s: Extension %s already exists."),$k,$vars['extension'])."<br>";
                } else {
                    if ($vm_exists) {
                        voicemail_mailbox_add($vars["extension"], $vars);
                    }
                    core_users_add($vars);
                    // This is to add destinations for extension, as the standard API core_users_add can't handle this
                    // a new function was needed.
                    bulkextensions_dest_add($destvars, $vars["extension"]);
                    core_devices_add($vars["deviceid"],$vars["tech"],$vars["devinfo_dial"],$vars["devicetype"],$vars["deviceuser"],$vars["description"],$vars["emergency_cid"]);

                    if ($lang_exists) {
                        languages_user_update($vars["extension"], $vars["langcode"]);
                    }
                    if ($dict_exists) {
                        dictate_update($vars["extension"], $vars["dictenabled"], $vars["dictformat"], $vars["dictemail"]);
                    }
                    if ($findme_exists && $followme_set) {
                        findmefollow_add($vars["account"], $vars["strategy"], $vars["grptime"], $vars["grplist"], $vars["postdest"], $vars["grppre"], $vars["annmsg_id"], $vars["dring"], $vars["needsconf"], $vars["remotealert_id"], $vars["toolate_id"], $vars["ringing"], $vars["pre_ring"], $vars["ddial"]);
                    }
                    if ($fax_exists) {
                        fax_save_user($vars["extension"], $vars["faxenabled"], $vars["faxemail"]);
                    }

                    if ($campon_exists) {
                        campon_update($vars["extension"], array('cc_agent_policy' => $vars["cc_agent_policy"],'cc_monitor_policy' => $vars["cc_monitor_policy"]));
                    }
                    if ($queue_exists) {
                        queues_set_qnostate($vars["extension"], $vars["qnostate"]);
                    }
                    if ($xmpp_exists) {
                        xmpp_users_put(array("user"=>$vars["extension"], "jabber_user" =>$vars["xmpp_user"],"jabber_pass"=>$vars["xmpp_pass"]));
                    }
                    if ($xactview_exists) {
                        xactview_user_add($vars["extension"],$vars["add_xactview"], $vars["xactview_createprofile"], $vars["xactview_profilepassword"], $vars["name"], $vars["devinfo_dial"], $vars["xactview_cell"], $vars["xactview_email"], $vars["xactview_autoanswer"], $vars["xactview_autoanswer"], $vars["jabber_host"], $vars["jabber_domain"], $vars["jabber_resource"], $vars["jabber_port"], $vars["jabber_username"], $vars["jabber_password"]); 
                    }
                    if ($extensionroutes_exists) {
                        $routes = core_routing_list();

                        foreach ($routes as $value) {
                            if (isset($value['route_id']) && !empty($value['route_id'])) {
                                $route_list[] = $value['route_id'];
                            }
                        }
                        extensionroutes_add_user($vars['extension'], $route_list);
                    }
                    // begin status output for this row
                    $output .= sprintf(_("Row %s: Added: %s"),$k,$vars['extension']);
                    // send notification email for new Voicemail account
                    $email_to = "";
                    // first use user email defined for Voicemail account
                    if (isset($vars["email"])) {
                        $email_to = $vars["email"];
                    }
                    // if no user email specified, use default email
                    if (isset($default_email) && ($email_to == "")) {
                        $email_to = $default_email;
                    }
                    // if an override email is specified, use it
                    // if "noemail" is set for override email
                    // set email_to = "" so that an email will not be sent
                    if (isset($override_email) && ($override_email != "")) {
                        if ($override_email == "noemail") {
                            $email_to = "";
                        } else {
                            $email_to = $override_email;
                        }
                    }
                    if ($email_to != "") {
                        // SUBJECT - set default subject if not set by user
                        if (!isset($email_subject) || $email_subject == "") {
                            $email_subject = _("Voicemail Account Activated");
                        }
                        // FROM - if specified, use that, otherwise leave blank
                        if (isset($email_from) && $email_from != "") {
                            $email_from_header = "From: " . $email_from . $line_end;
                        } else {
                            $email_from_header = "";
                        }
                        // REPLY-TO - if specified, use that, otherwise leave blank
                        if (isset($email_replyto) && $email_replyto != "") {
                            $email_replyto_header = "Reply-To: " . $email_replyto . $line_end;
                        } else {
                            $email_replyto_header = "";
                        }
                        // HEADERS
                        $email_headers = $email_from_header . $email_replyto_header;
                        // BODY
                        if (!isset($email_body_open) || $email_body_open == "") {
                            $email_body = _("Login information for your Voicemail account is as follows:"). "\n\n";
                        } else {
                            $email_body = $email_body_open . "\n\n";
                        }
                        $email_body .= "\t" . _("Account Name: ") . $vars["name"] . $line_end;
                        $email_body .= "\t" . _("Extension: ") . $vars["extension"] . $line_end;
                        $email_body .= "\t" . _("Voicemail Password: ") . $vars["vmpwd"] . $line_end;
                        if (isset($email_body_close) && $email_body_close != "") {
                            $email_body .= "\n\n" . $email_body_close . $line_end;
                        }

                        // Mail it!
                        if (mail($email_to, $email_subject, $email_body, $email_headers)) {
                            $output .= sprintf(_(", notification sent to: %s"),$email_to);;
                        } else {
                            $output .= sprintf(_(", notification failed to: %s"),$email_to);;
                        }
                    }
                    // close status output for this row with line break
                    $output .= "<br>";
                    $change = true;
                }
                break;
            case "edit":
                // Functions core_devices_del and core_users_del
                // do not check that the device or user actually
                // exists.
                // We check that the device or user exists before
                // deleting by looking them up by the extension.
                // Only if the device or user exists do we call
                // core_devices_del or core_users_del.
                if (core_devices_get($vars["extension"])) {
                    core_devices_del($vars["extension"]);
                    $change = true;
                }
                if (core_users_get($vars["extension"])) {
                    core_users_del($vars["extension"]);
                    core_users_cleanastdb($vars["extension"]);
                    if ($findme_exists) {
                        findmefollow_del($vars["extension"]);
                    }
                    if ($dict_exists) {
                        dictate_del($vars["extension"]);
                    }
                    if ($lang_exists) {
                        languages_user_del($vars["extension"]);
                    }
                    $change = true;
                }
                // The Voicemail functions have their own internal
                // checking.
                // If the Voicemail box in question does not exist,
                // the functions simply return.  No harm done.
                //
                // When editting an existing extension do not call
                // voicemail_mailbox_remove, it will delete existing
                // voicemail messages, which is undesirable.
                if ($vm_exists) {
                    voicemail_mailbox_del($vars["extension"]);
                }
                // Only add if no Voicemail, no user and no device entry already
                // exist for the extension we're trying to add.
                // Check the list of Voicemail entries.
                // user_vmexists == false means add new Voicemail entry.
                $user_vmexists = FALSE;
                if ($vm_exists) {
                    $uservm = voicemail_getVoicemail();
                    $vmcontexts = array_keys($uservm);
                    foreach ($vmcontexts as $vmcontext) {
                        if (isset($uservm[$vmcontext][$vars["extension"]])) {
                            $user_vmexists = TRUE;        // DO NOT add.
                        }
                    }
                }
                if ($user_vmexists || core_users_get($vars["extension"]) || core_devices_get($vars["extension"])) {
                    $output .= sprintf(_("Row %s: Extension %s already exists."),$k,$vars['extension'])."<br>";
                } else {
                    if ($vm_exists) {
                        voicemail_mailbox_add($vars["extension"], $vars);
                    }
                    core_users_add($vars);
                    // This is to add destinations for extension, as the standard API core_users_add can't handle this
                    // a new function was needed.
                    bulkextensions_dest_add($destvars, $vars["extension"]);
                    core_devices_add($vars["deviceid"],$vars["tech"],$vars["devinfo_dial"],$vars["devicetype"],$vars["deviceuser"],$vars["description"],$vars["emergency_cid"]);
                    if ($lang_exists) {
                        languages_user_update($vars["extension"], $vars["langcode"]);
                    }
                    if ($dict_exists) {
                        dictate_update($vars["extension"], $vars["dictenabled"], $vars["dictformat"], $vars["dictemail"]);
                    }
                    if ($findme_exists && $followme_set) {
                        findmefollow_add($vars["account"], $vars["strategy"], $vars["grptime"], $vars["grplist"], $vars["postdest"], $vars["grppre"], $vars["annmsg_id"], $vars["dring"], $vars["needsconf"], $vars["remotealert_id"], $vars["toolate_id"], $vars["ringing"], $vars["pre_ring"], $vars["ddial"]);
                    }
                    $change = true;
                }
                if ($fax_exists) {
                    // If there is no entry in faxenabled, then delete the user in the fax table
                    if (!isset($aInfo[$aFields["faxenabled"][1]]) || ($aInfo[$aFields["faxenabled"][1]] == "")){
                        fax_delete_user($vars["extension"]);
                    } else {
                        fax_save_user($vars["extension"], $vars["faxenabled"], $vars["faxemail"]);
                    }
                }
                if ($campon_exists) {
                    campon_update($vars["extension"], array('cc_agent_policy' => $vars["cc_agent_policy"],'cc_monitor_policy' => $vars["cc_monitor_policy"]));
                }
                if ($queue_exists) {
                    queues_set_qnostate($vars["extension"], $vars["qnostate"]);
                }
                if ($xmpp_exists) { 
                    xmpp_users_put(array("user"=>$vars["extension"], "jabber_user" =>$vars["xmpp_user"],"jabber_pass"=>$vars["xmpp_pass"]));
                }
                if ($xactview_exists) {
                    xactview_user_update($vars["extension"],$vars["add_xactview"], $vars["xactview_createprofile"], $vars["xactview_profilepassword"], $vars["name"], $vars["devinfo_dial"], $vars["xactview_cell"], $vars["xactview_email"], $vars["xactview_autoanswer"], $vars["xactview_autoanswer"], $vars["jabber_host"], $vars["jabber_domain"], $vars["jabber_resource"], $vars["jabber_port"], $vars["jabber_username"], $vars["jabber_password"]);
                }
                if ($extensionroutes_exists) {
                    $routes = core_routing_list();
                    extensionroutes_del_user($vars['extension']);
                    foreach ($routes as $value){
                        if (isset($value['route_id']) && !empty($value['route_id'])) {
                            $route_list[] = $value['route_id'];
                        }
                    }
                    extensionroutes_add_user($vars['extension'], $route_list);
                }
                $output .= sprintf(_("Row %s: Edited: %s"),$k,$vars['extension'])."<br>";
                break;
            case "del":
                // Functions core_devices_del and core_users_del
                // do not check that the device or user actually
                // exists.
                // We check that the device or user exists before
                // deleting by looking them up by the extension.
                // Only if the device or user exists do we call
                // core_devices_del or core_users_del.
                if (core_devices_get($vars["extension"])) {
                    core_devices_del($vars["extension"]);
                    $change = true;
                }
                if (core_users_get($vars["extension"])) {
                    core_users_del($vars["extension"]);
                    core_users_cleanastdb($vars["extension"]);
                    if ($findme_exists) {
                        findmefollow_del($vars["extension"]);
                    }
                    if ($dict_exists) {
                        dictate_del($vars["extension"]);
                    }
                    if ($lang_exists) {
                        languages_user_del($vars["extension"]);
                    }
                    $change = true;
                }
                // The Voicemail functions have their own internal checking.
                // If the Voicemail box in question does not exist,
                // the functions simply return. No harm done.
                //
                // call remove BEFORE del
                if ($vm_exists) {
                    voicemail_mailbox_remove($vars["extension"]);
                    voicemail_mailbox_del($vars["extension"]);
                }
                // Fax settings
                if ($fax_exists) {
                    fax_delete_user($vars["extension"]);
                }
                if ($campon_exists) {
                    campon_del($vars["extension"]);
                }
                if ($xmpp_exists) {
                    xmpp_users_del($vars["extension"]);
                }
                if ($xactview_exists) {
                    xactview_user_del($vars["extension"]);  
                }
                if ($extensionroutes_exists) {
                    extensionroutes_del_user($vars['extension']);
                }
                $output .= sprintf(_("Row %s: Deleted: %s"),$k,$vars['extension'])."<br>";
                break;
            default:
                $output .= sprintf(_("Row %s: Unrecognized action: the only actions recognized are add, edit, del."),$k)."<br>";
                break;
            }  // end switch

            if ($change) {
                needreload();
            }
        } else { // End if checkrange
            $output .= sprintf(_("Row $k: Access denied to extension %s. No action performed."),$vars['extension'])."<br>";
        }
    } // while loop
    print $output;

} else {

    $table_output = "";
    $table_rows = bulkextensions_generate_table_rows();
    if ($table_rows === NULL) {
        $table_output = "Table unavailable";
    } else {
        $table_output .= "<table class='rules table is-narrow is-borderless' rules='rows'>";
        
        $table_output .= "<colgroup>
            <col span=1 style='width:3%;'>
            <col span=1 xstyle='width:14%;'>
            <col span=1 xstyle='width:14%;'>
            <col span=1 xstyle='width:14%;'>
            <col span=1 xstyle='width:10%;'>
            <col span=1 style='width:30%;'>
            </colgroup>";
        $table_output .= "<tr valign='top'>
            <th align='left' valign='top' style='width:2em;'>#</th>
            <th align='left' valign='top'>"._('Name')."</th>
            <th align='left' valign='top'>"._('Default')."</th>
            <th align='left' valign='top'>"._('Allowed')."</th>
            <th align='left' valign='top'>"._('On Extensions page')."</th>
            <th align='left' valign='top'>"._('Details')."</th>
            </tr>";
        $i = 1;
        foreach ($table_rows as $row) {
            $table_output .= "<tr>";
            $table_output .= "<td valign='top'>" . $i . "</td>";
            $i++;
            foreach ($row as $col) {
                $text = ($col!='')?_($col):'';
                $table_output .= "<td valign='top'>" . $text . "</td>";
            }
            $table_output .= "</tr>";
        }
        $table_output .= "</table>";
    }

    echo "<div class='content'>";

    $helptext = _("Manage Extensions in bulk using CSV files.");
    $help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';
    echo "<div class='is-flex'><h2>"._("Bulk Extensions")."</h2>$help</div>";

    if($amp_conf['AMPEXTENSIONS'] == "deviceanduser") {

        echo "<div class='notification is-warning'>";
        echo _("You are running IssabelPBX in <b>deviceanduser</b> mode");
        echo "<br>"._("This module is only supported when IssabelPBX is in <b>extensions</b> mode");
        echo "</div>";

    } else {

        $blurb = "Start by downloading the %s Template CSV file %s (right-click > save as) or clicking the Export Extensions button.";
        $blurb2 = "Modify the CSV file to add, edit, or delete Extensions as desired. Then load the CSV file. After the CSV file is processed, the action taken for each row will be displayed.";
        $blurb3 = "Bulk extension changes can take a long time to complete. It can take 30-60 seconds to add 100 extensions on a small system. However, on a system with 2000 extensions it can take about 5 minutes to add 100 new extensions.";
?>
<div class='box'>
<?php echo "<p>".sprintf(_($blurb),'<a href="modules/bulkextensions/template.csv">','</a>').'</p>'; ?>
<form action="<?php $_SERVER["PHP_SELF"] ?>" name="uploadcsv" method="post" enctype="multipart/form-data">
<input id="csv_type" name="csv_type" type="hidden" value="none" />
<input type="submit" onclick="document.getElementById('csv_type').value='output';" value="<?php echo _("Export Extensions")?>" class="button is-rounded is-small"/>
<?php
    echo "<div class='mt-3'>"._($blurb2)."</div><div class='notification is-warning is-light'>"._($blurb3)."</div>\n";
?>

<div class="file has-name is-fullwidth has-addons">
  <label class="file-label">
    <input class="file-input" type="file" name="csvFile" id="csvFile">
    <span class="file-cta">
      <span class="file-icon">
        <i class="fa fa-upload"></i>
      </span>
      <span class="file-label">
<?php echo _('Choose a CSV file...')?>
      </span>
    </span>
    <span class="file-name" id="selected_file_name">
    </span>
  </label>
  <div class='control'><input type='submit' class='button is-info' value="<?php echo _("Upload")?>" onclick="document.getElementById('csv_type').value='input'; $.LoadingOverlay('show')" tabindex="<?php echo ++$tabindex;?>"/></div>
</div>

</div>

<!--&nbsp;&nbsp;<?php echo _("CSV File to Load")?>: <input name="csvFile" type="file" />
<input type="submit" onclick="document.getElementById('csv_type').value='input';"  value="<?php echo _("Load File")?>" />
<hr />
-->

<div class='box'>
<?php
echo "<h3>"._("Email Notification for New Accounts")."</h3>";
echo "<p>";
echo _("By default, a notification email will be sent to the Voicemail email address set for each account added.")."<br>";
echo _("The settings below can be used to control the content and destination of the notification emails.");
?>
<table>
  <tr>
    <td>
      <a href="#" class="info">
        <?php echo _("Default Address")?>
        <span><?php echo _("If a Default Address is specified, notification emails for new accounts without a Voicemail email address will be sent to the Default Address.")?>
        </span>
      </a>
    </td>
    <td>
      <input name="default_email" id="default_email" type="text" class="input" size="60" value="" />
    </td>
  </tr>
    <tr>
      <td>
        <a href="#" class="info">
        <?php echo _("Override Address")?>
          <span><?php echo _("If an Override Address is specified, all notification emails will be sent to the Override Address only. Type \"noemail\" (without the quotes) as the Override Address to stop notification emails from being sent.")?>
          </span>
        </a>
      </td>
      <td>
        <input name="override_email" id="override_email" type="text" class="input" size="60" value="" />
      </td>
    </tr>
    <tr>
      <td>
        <a href="#" class="info">
          <?php echo _("Email From")?>
          <span>
            <?php echo _("The Email From header may be specified. If left blank, the system default will be used.")?>
          </span>
        </a>
      </td>
      <td>
        <input name="email_from" id="email_from" type="text" class="input" size="60" value="" />
      </td>
    </tr>
    <tr>
      <td>
        <a href="#" class="info">
        <?php echo _("Email Reply-To")?>
          <span>
            <?php echo _("The Email Reply-To header may be specified. If left blank, the system default will be used.")?>
          </span>
        </a>
      </td>
      <td>
        <input name="email_replyto" id="email-replyto" type="text" class="input" size="60" value="" />
      </td>
    </tr>
    <tr>
      <td>
        <a href="#" class="info">
          <?php echo _("Email Subject")?>
          <span>
            <?php echo _("The Email Subject may be specified. If left blank, the default subject, \"Voicemail Account Activated\", will be used.")?>
          </span>
        </a>
      </td>
      <td>
        <input name="email_subject" id="email_subject" type="text" class="input" size="60" value="" />
      </td>
    </tr>
    <tr>
      <td>
        <a href="#" class="info">
        <?php echo _("Email Opening")?>
          <span>
          <?php echo _("The Email Opening may be specified. If left blank, the default opening, \"Login information for your Voicemail account is as follows:\", will be used.")?>
          <?php echo _("The account name, extension, and Voicemail password will automatically be inserted after the opening.")?> 
          </span>
        </a>
      </td>
      <td>
        <textarea class="textarea" name="email_body_open" id="email_body_open" rows="2" cols="60"></textarea>
      </td>
    </tr>
    <tr>
      <td>
        <a href="#" class="info">
        <?php echo _("Email Closing")?>
          <span>
          <?php echo _("The Email Closing may be specified. If any text is entered, it will be inserted at the end of the email.")?>
          </span>
        </a>
      </td>
      <td>
        <textarea class="textarea" name="email_body_close" id="email_body_close" rows="2" cols="60"></textarea>
      </td>
    </tr>
</table>
</form>
</div>
<div class='box'>
<?php 
echo "<h3>"._("Bulk Extensions CSV File Columns")."</h3><p>";
echo _("The table below explains each column in the CSV file. You can change the column order of the CSV file as you like, however, the column names must be preserved.")."<p>";
print $table_output;
}
}
?>

<script>
$(function(){
    const fileInput = document.querySelector("input[type=file]");
    if(fileInput!==null) {
      fileInput.onchange = () => {
        if (fileInput.files.length > 0) {
          const fileName = document.querySelector(".file-name");
          fileName.textContent = fileInput.files[0].name;
        }
      }
    }
})
</script>
</div>

