<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

function asternicivr_hookGet_config($engine) {
    global $ext;
    global $amp_conf;

    if (!isset($amp_conf['IVR_REGISTER_OPTIONS_ASTERNIC'])) {
        return;
    }
    switch($engine) {
      case "asterisk":
        if ($amp_conf['IVR_REGISTER_OPTIONS_ASTERNIC'] == 1) {
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
