<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

//    License for all code of this IssabelPBX module can be found in the license file inside the module directory
//    Copyright 2018 Issabel Foundation

function writequeuelog_get_config($engine) {
    global $ext;
    switch ($engine) {
        case 'asterisk':
            foreach (writequeuelog_list() as $row) {
                $ext->add('app-writequeuelog',$row['qlog_id'], '', new ext_noop('('.$row['description'].') Writing queue_log '.$row['qlog_uniqueid'].', '. $row['qlog_queue'].', '.$row['qlog_agent'].', '.$row['qlog_event']));
                $ext->add('app-writequeuelog',$row['qlog_id'], '', new ext_queuelog($row['qlog_queue'],$row['qlog_uniqueid'],$row['qlog_agent'],$row['qlog_event'],$row['qlog_extra']));
                $ext->add('app-writequeuelog',$row['qlog_id'], '', new ext_goto($row['dest']));
            }
        break;
    }
}

/**  Get a list of all write queuelog entries */
function writequeuelog_list() {
    global $db;
    $sql = "SELECT qlog_id, description, qlog_uniqueid, qlog_queue, qlog_agent, qlog_event, qlog_extra, dest FROM writequeuelog ORDER BY description ";
    $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
    if($db->IsError($results)) {
        die_issabelpbx($results->getMessage()."<br><br>Error selecting from writequeuelog");
    }
    return $results;
}

function writequeuelog_destinations() {
    global $module_page;
    $extens = array();

    // it makes no sense to point at another write queue log (and it can be an infinite loop)
    if ($module_page == 'writequeuelog') {
        return false;
    }

    // return an associative array with destination and description
    foreach (writequeuelog_list() as $row) {
        $extens[] = array('destination' => 'app-writequeuelog,' . $row['qlog_id'] . ',1', 'description' => $row['description']);
    }
    return $extens;
}

function writequeuelog_get($qlog_id) {
    global $db;
    $sql = "SELECT qlog_id, description, qlog_uniqueid, qlog_event, qlog_queue, qlog_agent, qlog_extra, dest FROM writequeuelog WHERE qlog_id = ".$db->escapeSimple($qlog_id);
    $row = $db->getRow($sql, DB_FETCHMODE_ASSOC);
    if($db->IsError($row)) {
        die_issabelpbx($row->getMessage()."<br><br>Error selecting row from writequeuelog");
    }

    return $row;
}

function writequeuelog_add($description, $qlog_uniqueid, $qlog_queue, $qlog_agent, $qlog_event, $qlog_extra, $dest) {
    global $db;
    $sql = "INSERT INTO writequeuelog (description, qlog_uniqueid, qlog_queue, qlog_agent, qlog_event, qlog_extra, dest) VALUES (".
        "'".$db->escapeSimple($description)."', ".
        "'".$db->escapeSimple($qlog_uniqueid)."', ".
        "'".$db->escapeSimple($qlog_queue)."', ".
        "'".$db->escapeSimple($qlog_agent)."',".
        "'".$db->escapeSimple($qlog_event)."',".
        "'".$db->escapeSimple($qlog_extra)."',".
        "'".$db->escapeSimple($dest)."')";
    $result = $db->query($sql);
    if($db->IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}

function writequeuelog_delete($qlog_id) {
    global $db;
    $sql = "DELETE FROM writequeuelog WHERE qlog_id = ".$db->escapeSimple($qlog_id);
    $result = $db->query($sql);
    if($db->IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}

function writequeuelog_edit($qlog_id, $description, $qlog_uniqueid, $qlog_queue, $qlog_agent, $qlog_event, $qlog_extra, $dest) {
    global $db;
    $sql = "UPDATE writequeuelog SET ".
        "description = '".$db->escapeSimple($description)."', ".
        "qlog_uniqueid = '".$db->escapeSimple($qlog_uniqueid)."', ".
        "qlog_queue = '".$db->escapeSimple($qlog_queue)."', ".
        "qlog_agent = '".$db->escapeSimple($qlog_agent)."', ".
        "qlog_event = '".$db->escapeSimple($qlog_event)."', ".
        "qlog_extra = '".$db->escapeSimple($qlog_extra)."', ".
        "dest = '".$db->escapeSimple($dest)."' ".
        "WHERE qlog_id = ".$db->escapeSimple($qlog_id);
    $result = $db->query($sql);
    if($db->IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}

//-----------------------------
// Dynamic Destination Registry 

function writequeuelog_check_destinations($dest=true) {
    global $active_modules;

    $destlist = array();
    if (is_array($dest) && empty($dest)) {
        return $destlist;
    }
    $sql = "SELECT qlog_id, description, dest FROM writequeuelog ";
    if ($dest !== true) {
        $sql .= "WHERE dest in ('".implode("','",$dest)."')";
    }
    $results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

    $type = isset($active_modules['writequeuelog']['type'])?$active_modules['writequeuelog']['type']:'setup';

    foreach ($results as $result) {
        $thisdest = $result['dest'];
        $thisid   = $result['qlog_id'];
        $destlist[] = array(
            'dest' => $thisdest,
            'description' => 'Write Queue Log: '.$result['description'],
            'edit_url' => 'config.php?display=writequeuelog&type=tool&id='.urlencode($thisid),
        );
    }
    return $destlist;
}

function writequeuelog_getdest($id) {
    return array("app-writequeuelog,$id,1");
}

function writequeuelog_getdestinfo($dest) {
    if (substr(trim($dest),0,11) == 'app-writequeuelog,') {
        $grp = explode(',',$dest);
        $id = $grp[1];
        $thisqid = writequeuelog_get($id);
        if (empty($thisqid)) {
            return array();
        } else {
            return array('description' => sprintf(_("Write Queue Log %s: "),$thisqid['description']),
                         'edit_url'    => 'config.php?display=writequeuelog&id='.urlencode($id),
                        );
        }
    } else {
        return false;
    }
}

