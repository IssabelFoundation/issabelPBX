<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//    License for all code of this IssabelPBX module can be found in the license file inside the module directory
//    Copyright 2018 Issabel Foundation
//    Copyright 2013 Schmooze Com Inc.
//    Copyright 2008 by Moshe Brevda mbrevda=>gmail[com]

function setcid_get_config($engine) {
    global $ext;
    switch ($engine) {
        case 'asterisk':
            //Below removed in ISSABELPBX-6740, Please reference before adding it back
            //$ext->addInclude('from-internal-additional', 'app-setcid');
            foreach (setcid_list() as $row) {
                    $ext->add('app-setcid',$row['cid_id'], '', new ext_noop('('.$row['description'].') Changing cid to '.$row['cid_name'].' <'. $row['cid_num'].'>'));
                    $ext->add('app-setcid',$row['cid_id'], '', new ext_set('CALLERID(name)', $row['cid_name']));
                    $ext->add('app-setcid',$row['cid_id'], '', new ext_set('CALLERID(num)', $row['cid_num']));

                    $vars = explode(",",$row['variables']);
                    foreach($vars as $setvar) {
                        list ($key, $val) = preg_split("/=/",$setvar);
                        $ext->add('app-setcid',$row['cid_id'], '', new ext_set($key,$val));
                    }

                    $ext->add('app-setcid',$row['cid_id'], '', new ext_goto($row['dest']));
            }
        break;
    }
}

/**  Get a list of all cid
 */
function setcid_list() {
    global $db;

    // check if variables field is on db, if not create it
    $sql = "SELECT `variables` FROM setcid";
    $check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($check)) {
        $sql = "ALTER TABLE setcid ADD `variables` TEXT NOT NULL DEFAULT ''";
        $result = $db->query($sql);
    }

    $sql = "SELECT cid_id, description, cid_name, cid_num, dest, variables FROM setcid ORDER BY description ";
    $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
    if($db->IsError($results)) {
        die_issabelpbx($results->getMessage()."<br><br>Error selecting from setcid");
    }
    return $results;
}

function setcid_destinations() {
    global $module_page;
    $extens = array();

    // it makes no sense to point at another callerid (and it can be an infinite loop)
    if ($module_page == 'setcid') {
        return false;
    }

    // return an associative array with destination and description
    foreach (setcid_list() as $row) {
        $extens[] = array('destination' => 'app-setcid,' . $row['cid_id'] . ',1', 'description' => $row['description']);
    }
    return $extens;
}

function setcid_get($cid_id) {
    global $db;
    $sql = "SELECT cid_id, description, cid_name, cid_num, dest, variables FROM setcid WHERE cid_id = ".$db->escapeSimple($cid_id);
    $row = $db->getRow($sql, DB_FETCHMODE_ASSOC);
    if($db->IsError($row)) {
        die_issabelpbx($row->getMessage()."<br><br>Error selecting row from setcid");
    }

    return $row;
}

function setcid_add($description, $cid_name, $cid_num, $dest, $variables) {
    global $db;
    $sql = "INSERT INTO setcid (description, cid_name, cid_num, variables, dest) VALUES (".
        "'".$db->escapeSimple($description)."', ".
        "'".$db->escapeSimple($cid_name)."', ".
        "'".$db->escapeSimple($cid_num)."', ".
        "'".$db->escapeSimple($variables)."',".
        "'".$db->escapeSimple($dest)."')";
    $result = $db->query($sql);
    if($db->IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}

function setcid_delete($cid_id) {
    global $db;
    $sql = "DELETE FROM setcid WHERE cid_id = ".$db->escapeSimple($cid_id);
    $result = $db->query($sql);
    if($db->IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}

function setcid_edit($cid_id, $description, $cid_name, $cid_num, $dest, $variables) {
    global $db;
    $sql = "UPDATE setcid SET ".
        "description = '".$db->escapeSimple($description)."', ".
        "cid_name = '".$db->escapeSimple($cid_name)."', ".
        "cid_num = '".$db->escapeSimple($cid_num)."', ".
        "variables = '".$db->escapeSimple($variables)."', ".
        "dest = '".$db->escapeSimple($dest)."' ".
        "WHERE cid_id = ".$db->escapeSimple($cid_id);
    $result = $db->query($sql);
    if($db->IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}



//----------------------------------------------------------------------------
// Dynamic Destination Registry and Recordings Registry Functions

function setcid_check_destinations($dest=true) {
    global $active_modules;

    $destlist = array();
    if (is_array($dest) && empty($dest)) {
        return $destlist;
    }
    $sql = "SELECT cid_id, description, dest FROM setcid ";
    if ($dest !== true) {
        $sql .= "WHERE dest in ('".implode("','",$dest)."')";
    }
    $results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

    $type = isset($active_modules['setcid']['type'])?$active_modules['setcid']['type']:'setup';

    foreach ($results as $result) {
        $thisdest = $result['dest'];
        $thisid   = $result['cid_id'];
        $destlist[] = array(
            'dest' => $thisdest,
            'description' => 'Set CallerID: '.$result['description'],
            'edit_url' => 'config.php?display=setcid&type=tool&id='.urlencode($thisid),
        );
    }
    return $destlist;
}

function setcid_getdest($id) {
    return array("app-setcid,$id,1");
}

function setcid_getdestinfo($dest) {
    if (substr(trim($dest),0,11) == 'app-setcid,') {
        $grp = explode(',',$dest);
        $id = $grp[1];
        $thiscid = setcid_get($id);
        if (empty($thiscid)) {
            return array();
        } else {
            return array('description' => sprintf(_("Set CallerID %s: "),$thiscid['description']),
                         'edit_url' => 'config.php?display=setcid&id='.urlencode($id),
                                  );
        }
    } else {
        return false;
    }
}

?>
