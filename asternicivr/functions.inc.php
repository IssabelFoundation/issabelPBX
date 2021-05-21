<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

function asternicivr_hookGet_config($engine) {
    global $ext;
    global $db;

    switch($engine) {
    case "asterisk":

        $ivr_logging = asternicivr_get_details('ivr_logging');
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
                            $ext->splice($context, $selection['selection'], 'ivrsel-'.$selection['selection'], new ext_execif('$["${QURL}" != ""]', 'Set', '__QURL=${QURL}~${EXTEN}'));
                            $ext->splice($context, $selection['selection'], 'ivrsel-'.$selection['selection'], new ext_execif('$["${QURL}" = ""]', 'Set', '__QURL=${EXTEN}'));
                        }
                    }
                }
            }
        }
        break;
    }
}

function asternicivr_configpageinit($pagename) {
    global $currentcomponent;

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

    if($pagename == 'asternic'){
        $currentcomponent->addprocessfunc('asternicivr_configprocess');

        return true;
    }
}

//process received arguments
function asternicivr_configprocess(){
    if (isset($_REQUEST['display']) && $_REQUEST['display'] == 'asternic'){

        //get variables 
        $get_var = array('ivr_logging');

        foreach($get_var as $var){
            $vars[$var] = isset($_REQUEST[$var])    ? $_REQUEST[$var]               : '';
        }

        $action = isset($_REQUEST['action'])    ? $_REQUEST['action']   : '';

        switch ($action) {
        case 'save':
            asternicivr_put_details($vars);
            needreload();
            redirect_standard_continue();
            break;
        }
    }
}

function asternicivr_put_details($options) {
    global $db;

    foreach ($options as $key => $item) {
        $data[] = array($key, $item); 
    }

    $sql = $db->prepare('REPLACE INTO asternicivr_options (`keyword`, `value`) VALUES (?, ?)');
    $ret = $db->executeMultiple($sql, $data);

    if($db->IsError($ret)) {
        die_issabelpbx($ret->getDebugInfo()."\n".$ret->getUserInfo()."\n".$db->last_query);
    }
    return TRUE;
}

function asternicivr_get_details($keyword = '') {
    global $db;

    $sql = "SELECT * FROM asternicivr_options";

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
