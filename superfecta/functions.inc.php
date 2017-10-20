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

        $html.='<tr><td colspan="2"><h5>' . _("Superfecta CID Lookup") . '<hr></h5></td></tr>';

        $html.='<tr><td><a href="#" class="info">' . _('Enable CID Superfecta') . '<span>' . _("Sources can be added/removed in CID Superfecta section") . '</span></a>:</td>';
        $html.='<td><input type="checkbox" name="enable_superfecta" value="yes" ' . $checked_status . '></td></tr>';

        $html.='<tr><td><a href="#" class="info">' . _('Scheme') . '<span>' . _("Setup Schemes in CID Superfecta section") . '</span></a>:</td>';
        $html.='<td><select name="superfecta_scheme">';
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