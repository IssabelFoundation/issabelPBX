<?php

function superfecta_hook_core($viewing_itemid, $target_menuid) {
    global $db;
    $sql = "SELECT * FROM superfectaconfig WHERE field ='order' ORDER BY value ASC";
    $schemes = $db->getAll($sql, array(), DB_FETCHMODE_ASSOC);
    $html = '';
    if ($target_menuid == 'did') {
        if (superfecta_did_get($viewing_itemid)) {
            $checked_status = 'checked';
        } else {
            $checked_status = '';
        }

        $html.='<tr><td colspan="2"><h5>' . _("Superfecta CID Lookup") . '</h5></td></tr>';

        $html.='<tr><td><a href="#" class="info">' . _('Enable CID Superfecta') . '<span>' . _("Sources can be added/removed in CID Superfecta section") . '</span></a></td>';
        $html.='<td><input type="checkbox" class="switch" name="enable_superfecta" id="enable_superfecta" value="yes" ' . $checked_status . '/><label style="height:auto; line-height:1em; padding-left:3em;" for="enable_superfecta">&nbsp;</label></td></tr>';
        $html.='<tr><td><a href="#" class="info">' . _('Scheme') . '<span>' . _("Setup Schemes in CID Superfecta section") . '</span></a></td>';
        $html.='<td><select name="superfecta_scheme" class="componentSelect">';
        $info = explode("/", $viewing_itemid);
        $sql = "SELECT scheme FROM superfecta_to_incoming WHERE extension = '" . $info[0] . "'";
        $scheme = $db->getOne($sql);

        $first = '<option value="ALL|ALL" {$selected}>ALL</option>';
        $has_selected = FALSE;
        foreach ($schemes as $data) {
            if ($scheme == $data['source']) {
                $selected = 'selected';
                $has_selected = TRUE;
            } else {
                $selected = '';
            }
            $name = explode("_", $data['source']);
            $last .= '<option value="' . $data['source'] . '" ' . $selected . '>' . $name[1] . '</option>';
        }
        $selected = ($has_selected) ? 'selected' : '';
        $first = str_replace('{$selected}', $selected, $first);
        $html .= $first . $last;
        $html.= '</select>
		</td></tr>';

        $html .= '</td></tr>';
    }
    return $html;
}

function superfecta_hookProcess_core($viewing_itemid, $request) {

    // TODO: move sql to functions superfecta_did_(add, del, edit)
    if (!isset($request['action']))
        return;

    switch ($request['action']) {
        case 'addIncoming':
            if ($request['enable_superfecta'] == 'yes') {
                $sql = "REPLACE INTO superfecta_to_incoming (extension, cidnum, scheme) values (" . q($request['extension']) . "," . q($request['cidnum']) . "," . q($request['superfecta_scheme']) . ")";
                $result = sql($sql);
            }
            break;
        case 'delIncoming':
            $extarray = explode('/', $request['extdisplay'], 2);
            if (count($extarray) == 2) {
                $sql = "DELETE FROM superfecta_to_incoming WHERE extension = " . q($extarray[0]) . " AND cidnum = " . q($extarray[1]);
                $result = sql($sql);
            }
            break;
        case 'edtIncoming': // deleting and adding as in core module
            $extarray = explode('/', $request['extdisplay'], 2);
            if (count($extarray) == 2) {
                $sql = "DELETE FROM superfecta_to_incoming WHERE extension = " . q($extarray[0]) . " AND cidnum = " . q($extarray[1]);
                $result = sql($sql);
            }
            if ($request['enable_superfecta'] == 'yes') {
                $sql = "REPLACE INTO superfecta_to_incoming (extension, cidnum, scheme) values (" . q($request['extension']) . "," . q($request['cidnum']) . "," . q($request['superfecta_scheme']) . ")";
                $result = sql($sql);
            }
            break;
    }
}

function superfecta_hookGet_config($engine) {
    // TODO: integrating with direct extension <-> DID association
    // TODO: add option to avoid callerid lookup if the telco already supply a callerid name (GosubIf)
    global $ext;  // is this the best way to pass this?

    switch ($engine) {
        case "asterisk":
            $pairing = superfecta_did_list();
            if (is_array($pairing)) {
                foreach ($pairing as $item) {
                    if ($item['superfecta_to_incoming_id'] != 0) {
                        // Code from modules/core/functions.inc.php core_get_config inbound routes
                        $exten = trim($item['extension']);
                        $cidnum = trim($item['cidnum']);
                        $scheme = trim($item['scheme']);
                        if ($scheme == '') {
                            $scheme = 'base_Default';
                        }

                        if ($cidnum != '' && $exten == '') {
                            $exten = 's';
                            $pricid = ($item['pricid']) ? true : false;
                        } else if (($cidnum != '' && $exten != '') || ($cidnum == '' && $exten == '')) {
                            $pricid = true;
                        } else {
                            $pricid = false;
                        }
                        $context = ($pricid) ? "ext-did-0001" : "ext-did-0002";

                        $exten = (empty($exten) ? "s" : $exten);
                        $exten = $exten . (empty($cidnum) ? "" : "/" . $cidnum); //if a CID num is defined, add it

                        $ext->splice($context, $exten, 1, new ext_setvar('CIDSFSCHEME', base64_encode($scheme)));
                        $ext->splice($context, $exten, 2, new ext_setvar('CALLERID(name)', '${lookupcid}'));
                        $ext->splice($context, $exten, 2, new ext_agi(dirname(__FILE__) . '/agi/superfecta.agi'));
                    }
                }
            }
            break;
    }
}

function superfecta_did_get($did) {
    $extarray = explode('/', $did, 2);
    if (count($extarray) == 2) {
        $sql = "SELECT * FROM superfecta_to_incoming WHERE extension = " . q($extarray[0]) . " AND cidnum = " . q($extarray[1]);
        $result = sql($sql, "getAll", DB_FETCHMODE_ASSOC);
        if (is_array($result) && count($result)) {
            return true;
        }
    }
    return false;
}

function superfecta_did_list($id=false) {
    $sql = "
	SELECT superfecta_to_incoming_id, a.extension extension, a.cidnum cidnum, pricid, scheme FROM superfecta_to_incoming a 
	INNER JOIN incoming b
	ON a.extension = b.extension AND a.cidnum = b.cidnum
	";
    if ($id !== false && ctype_digit($id)) {
        $sql .= " WHERE superfecta_to_incoming_id = '" . q($id) . "'";
    }

    $results = sql($sql, "getAll", DB_FETCHMODE_ASSOC);
    return is_array($results) ? $results : array();
}

function superfecta_getConfig($scheme) {
    $return = array();
    $sql = "SELECT * FROM superfectaconfig WHERE source='$scheme'";
    $results = sql($sql, "getAll");
    foreach ($results as $val) {
        $return[$val[1]] = $val[2];
    }

    //set some default values for creating a new scheme
    if ($scheme == 'new') {
        $return['Curl_Timeout'] = 3;
        $return['SPAM_Text'] = 'SPAM';
    }

    if (!isset($return['multifecta_timeout'])) {
        $return['multifecta_timeout'] = '1.5';
    }
    if (!isset($return['enable_multifecta'])) {
        $return['enable_multifecta'] = '';
    }
    if (!isset($return['SPAM_threshold'])) {
        $return['SPAM_threshold'] = '3';
    }

    return $return;
}


function superfecta_delete_scheme($post) {
    global $db;

    $data = preg_replace('/^scheme_/i', '', $post['extdisplay']);
    $sql = "DELETE FROM superfectaconfig WHERE source = '".  $db->escapeSimple($data)."'";
    sql($sql);

    //We now have to reorder the array. Well, we don't -have- to. But it's prettier
    $sql = "SELECT * FROM superfectaconfig WHERE field LIKE 'order' ORDER BY value ASC";
    $scheme_list = sql($sql, 'getAll', DB_FETCHMODE_ASSOC);
    $order = 1;
    foreach($scheme_list as $data) {
        $sql = "REPLACE INTO superfectaconfig (value, source, field) VALUES('".$db->escapeSimple($order)."', '".$db->escapeSimple($data['source'])."', 'order')";
        sql($sql);
        $order++;
    }
}

function superfecta_update_scheme($post) {
    global $db;

//    $enable_interceptor   = $db->escapeSimple(utf8_decode($post['enable_interceptor']));
    $enable_interceptor   = (isset($post['enable_interceptor']) && $post['enable_interceptor'] == 'Y') ? TRUE : FALSE;
    $scheme_name          = $db->escapeSimple(preg_replace('/\s/i', '_', preg_replace('/\+/i', '_', trim($post['scheme_name']))));
    $scheme_name_orig     = $db->escapeSimple($post['scheme_name_orig']);
    $DID                  = $db->escapeSimple($post['DID']);
    $CID_rules            = $db->escapeSimple($post['CID_rules']);
    $Prefix_URL           = $db->escapeSimple($post['Prefix_URL']);
    $Curl_Timeout         = $db->escapeSimple($post['Curl_Timeout']);
    $http_password        = isset($post['http_password'])?$db->escapeSimple(utf8_decode($post['http_password'])):'';
    $http_username        = isset($post['http_username'])?$db->escapeSimple(utf8_decode($post['http_username'])):'';
    $SPAM_Text            = $db->escapeSimple($post['SPAM_Text']);
    $SPAM_Text_Substitute = (isset($post['SPAM_Text_Substitute'])) ? $db->escapeSimple($post['SPAM_Text_Substitute']) : 'N';
    $processor            = $db->escapeSimple(utf8_decode($post['processor']));
    $multifecta_timeout   = $db->escapeSimple(utf8_decode($post['multifecta_timeout']));
    $SPAM_threshold       = isset($post['SPAM_threshold'])?$db->escapeSimple($post['SPAM_threshold']):3;
    $status               = $db->escapeSimple($post['status']);
    $type                 = $db->escapeSimple($post['goto0']);
    $destination          = isset($post[$type])?$db->escapeSimple($post[$type]):'';

    $error = false;
    $error_text = '';

    //see if the scheme name has changed, and make sure that there isn't already one named the new name.
    if($scheme_name == "") {
        $error = true;
        $error_text = _('Name cannot be blank');
    }

    if(($scheme_name != $scheme_name_orig) && !$error) {
        $sql = "SELECT * FROM superfectaconfig WHERE source='base_".$scheme_name."'";
        $results = sql($sql, "getAll");

        if(!empty($results)) {
            $error = true;
            $error_text = _('Scheme name already used');
        } else {
            $sql = "UPDATE superfectaconfig SET source = 'base_".$scheme_name."' WHERE source = 'base_".$scheme_name_orig."'";
            sql($sql);
            $sql = "UPDATE superfecta_to_incoming SET scheme = 'base_".$scheme_name."' WHERE scheme = 'base_".$scheme_name_orig."'";
            sql($sql);
        }
    }

    if(!$error) {
        //update database
        if($enable_interceptor) {
            $sql = "REPLACE INTO superfectaconfig (source,field,value) VALUES('base_".$scheme_name."','spam_interceptor','Y')";
        } else {
            $sql = "REPLACE INTO superfectaconfig (source,field,value) VALUES('base_".$scheme_name."','spam_interceptor','N')";
        }
        sql($sql);
        $sql = "REPLACE INTO superfectaconfig (source,field,value) VALUES('base_".$scheme_name."','spam_destination','$destination')";
        sql($sql);
        $sql = "REPLACE INTO superfectaconfig (source,field,value) VALUES('base_".$scheme_name."','Prefix_URL','$Prefix_URL')";
        sql($sql);
        $sql = "REPLACE INTO superfectaconfig (source,field,value) VALUES('base_".$scheme_name."','Curl_Timeout','$Curl_Timeout')";
        sql($sql);
        $sql = "REPLACE INTO superfectaconfig (source,field,value) VALUES('base_".$scheme_name."','processor','$processor')";
        sql($sql);
        $sql = "REPLACE INTO superfectaconfig (source,field,value) VALUES('base_".$scheme_name."','multifecta_timeout','$multifecta_timeout')";
        sql($sql);
        $sql = "REPLACE INTO superfectaconfig (source,field,value) VALUES('base_".$scheme_name."','SPAM_Text','$SPAM_Text')";
        sql($sql);
        $sql = "REPLACE INTO superfectaconfig (source,field,value) VALUES('base_".$scheme_name."','SPAM_Text_Substitute','$SPAM_Text_Substitute')";
        sql($sql);
        $sql = "REPLACE INTO superfectaconfig (source,field,value) VALUES('base_".$scheme_name."','DID','$DID')";
        sql($sql);
        $sql = "REPLACE INTO superfectaconfig (source,field,value) VALUES('base_".$scheme_name."','CID_rules','$CID_rules')";
        sql($sql);
        $sql = "REPLACE INTO superfectaconfig (source,field,value) VALUES('base_".$scheme_name."','SPAM_threshold','$SPAM_threshold')";
        sql($sql);
        $sql = "REPLACE INTO superfectaconfig (source,field,value) VALUES('base_".$scheme_name."','status','$status')";
        sql($sql);

        //add ordering information to database if this scheme doesn't have it
        $highest_order = 0;
        $already_has_order = false;
        $sql = "SELECT source,ABS(value) FROM superfectaconfig WHERE field = 'order' ORDER BY ABS(value)";
        $results = sql($sql, "getAll");

        foreach($results as $val) {
            if($val[0] == "base_".$scheme_name) {
                $already_has_order = true;
                break;
            }
            $highest_order = $val[1];
        }

        if(!$already_has_order) {
            $sql = "REPLACE INTO superfectaconfig (source,field,value) VALUES('base_".$scheme_name."','order',".($highest_order+1).")";
            sql($sql);
        }
    }
    return $error_text;
}

