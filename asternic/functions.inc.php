<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//    License for all code of this IssabelPBX module can be found in the license file inside the module directory
//    Copyright 2013 Schmooze Com Inc.
//
function asternic_hookGet_config($engine) {
    global $ext;
    global $db;

    switch($engine) {
        case "asterisk":

            $ivr_logging = asternic_get_details('ivr_logging');
            if ($ivr_logging['value'] == 'true') {

                //get all ivrs
                $ivrlist = ivr_get_details(); 
                if(is_array($ivrlist)) {

                    foreach($ivrlist as $item) {
                        //splice into ivr to set the ivr selection var and append if already defined
                        $context = 'ivr-'.$item['id'];

                        //get ivr selection
                        $ivrentrieslist = ivr_get_entries($item['id']);
                        if (is_array($ivrentrieslist)) {

                            foreach($ivrentrieslist as $selection) {
                                //splice into ivr selection
                                $ext->splice($context, $selection['selection'], 'ivrsel-'.$selection['selection'], new ext_setvar("IVRSELECTION", '${EXTEN}|${IVR_CONTEXT}'));
                                $ext->splice($context, $selection['selection'], 'ivrsel-'.$selection['selection'], new ext_queuelog('NONE','${UNIQUEID}','NONE','IVROPTION', '${IVRSELECTION}'));
                            }
                        }
                    }
                }
            }
        break;
    }
}

function asternic_configpageinit($pagename) {
        global $currentcomponent;

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
    
        if($pagename == 'asternic'){
                $currentcomponent->addprocessfunc('asternic_configprocess');

            return true;
        }
}

//process received arguments
function asternic_configprocess(){
        if (isset($_REQUEST['display']) && $_REQUEST['display'] == 'asternic'){
                
        //get variables 
        $get_var = array('ivr_logging');
        
        foreach($get_var as $var){
                        $vars[$var] = isset($_REQUEST[$var])    ? $_REQUEST[$var]               : '';
                }

                $action = isset($_REQUEST['action'])    ? $_REQUEST['action']   : '';

                switch ($action) {
                        case 'save':
                asternic_put_details($vars);
                                needreload();
                                redirect_standard_continue();
                        break;
                }
        }
}

function asternic_put_details($options) {
    global $db;
    
    foreach ($options as $key => $item) {
        $data[] = array($key, $item); 
    }

    $sql = $db->prepare('REPLACE INTO asternic_options (`keyword`, `value`) VALUES (?, ?)');
        $ret = $db->executeMultiple($sql, $data);
    
    if($db->IsError($ret)) {
            die_issabelpbx($ret->getDebugInfo()."\n".$ret->getUserInfo()."\n".$db->last_query);
        }
    return TRUE;
}

function asternic_get_details($keyword = '') {
    global $db;

        $sql = "SELECT * FROM asternic_options";

    if (!empty($keyword)) {
        $sql .= " WHERE `keyword` = '" . $keyword . "'";        
    }

    $res = $db->getAll($sql, DB_FETCHMODE_ASSOC);
        if($db->IsError($res)) {
                die_issabelpbx($res->getDebugInfo());
        }

        return (isset($keyword) && $keyword != '') ? $res[0] : $res;    
}
?>
