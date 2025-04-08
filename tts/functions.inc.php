<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Issabel version 4.0                                                  |
  | http://www.issabel.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2021 Issabel Foundation                                |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+
*/
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

function tts_get_config($engine) {
    global $ext;

    $tts_cmd = array();
    $tts_template = array();

    $engines = ttsengine_list();
    foreach ($engines as $idx=>$data) {
         $tts_cmd[$data['ttsengine_engine']]=$data['ttsengine_cmd'];
         $tts_template[$data['ttsengine_engine']]=$data['ttsengine_template'];
    }

    switch ($engine) {
        case 'asterisk':
            foreach (tts_list() as $row) {
                $cur_engine = $row['tts_engine'];
                $text = $row['tts_text'];
                $text_without_newlines = str_replace(array("\r\n"), '. ', $text);
                $ext->add('app-tts',$row['tts_id'], '', new ext_answer());
                $ext->add('app-tts',$row['tts_id'], '', new ext_agi('issabel-tts.agi,"'.$text_without_newlines."\",$cur_engine,".base64_encode($tts_cmd[$cur_engine]).','.base64_encode($tts_template[$cur_engine])));
                $ext->add('app-tts',$row['tts_id'], '', new ext_goto($row['dest']));
            }
        break;
    }
}

/**  Get a list of all tts entries */
function tts_list() {
    global $db;
    $sql = "SELECT tts_id, tts_description, tts_engine, tts_text, dest FROM tts ORDER BY tts_description ";
    $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
    if($db->IsError($results)) {
        die_issabelpbx($results->getMessage()."<br><br>Error selecting from tts");
    }
    return $results;
}

/**  Get a list of all tts entries */
function ttsengine_list() {
    global $db;
    $sql = "SELECT ttsengine_id, ttsengine_description, ttsengine_engine, ttsengine_cmd, ttsengine_template FROM tts_engines ORDER BY ttsengine_description ";
    $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
    if($db->IsError($results)) {
        die_issabelpbx($results->getMessage()."<br><br>Error selecting from tts_engines");
    }
    return $results;
}


function tts_destinations() {
    global $module_page;
    $extens = array();

    // it makes no sense to point at another tts (and it can be an infinite loop)
    if ($module_page == 'tts') {
     //   return false;
    }

    // return an associative array with destination and description
    foreach (tts_list() as $row) {
        $extens[] = array('destination' => 'app-tts,' . $row['tts_id'] . ',1', 'description' => $row['tts_description']);
    }
    return $extens;
}

function tts_get($tts_id) {
    global $db;
    $sql = "SELECT tts_id, tts_description, tts_text, tts_engine, dest FROM tts WHERE tts_id = ".$db->escapeSimple($tts_id);
    $row = $db->getRow($sql, DB_FETCHMODE_ASSOC);
    if($db->IsError($row)) {
        die_issabelpbx($row->getMessage()."<br><br>Error selecting row from tts");
    }

    return $row;
}

function ttsengine_get($ttsengine_id) {
    global $db;
    $sql = "SELECT ttsengine_id, ttsengine_description, ttsengine_engine, ttsengine_cmd, ttsengine_template FROM tts_engines WHERE ttsengine_id = ".$db->escapeSimple($ttsengine_id);
    $row = $db->getRow($sql, DB_FETCHMODE_ASSOC);
    if($db->IsError($row)) {
        die_issabelpbx($row->getMessage()."<br><br>Error selecting row from tts");
    }

    return $row;
}


function tts_add($description, $tts_engine, $tts_text, $dest) {
    global $db;
    $sql = "INSERT INTO tts (tts_description, tts_engine, tts_text, dest) VALUES (".
        "'".$db->escapeSimple($description)."', ".
        "'".$db->escapeSimple($tts_engine)."', ".
        "'".$db->escapeSimple($tts_text)."',".
        "'".$db->escapeSimple($dest)."')";
    $result = $db->query($sql);
    if($db->IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}

function ttsengine_add($description, $ttsengine_engine, $ttsengine_cmd, $ttsengine_template) {
    global $db;
    $sql = "INSERT INTO tts_engines (ttsengine_description, ttsengine_engine, ttsengine_template, ttsengine_cmd) VALUES (".
        "'".$db->escapeSimple($description)."', ".
        "'".$db->escapeSimple($ttsengine_engine)."', ".
        "'".$db->escapeSimple($ttsengine_template)."',".
        "'".$db->escapeSimple($ttsengine_cmd)."')";
    $result = $db->query($sql);
    if($db->IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}

function ttsengine_delete($tts_id) {
    global $db;
    $sql = "DELETE FROM tts_engines WHERE ttsengine_id = ".$db->escapeSimple($tts_id);
    $result = $db->query($sql);
    if($db->IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}

function tts_delete($tts_id) {
    global $db;
    $sql = "DELETE FROM tts WHERE tts_id = ".$db->escapeSimple($tts_id);
    $result = $db->query($sql);
    if($db->IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}

function tts_edit($tts_id, $description, $tts_engine, $tts_text, $dest) {
    global $db;
    $sql = "UPDATE tts SET ".
        "tts_description = '".$db->escapeSimple($description)."', ".
        "tts_engine = '".$db->escapeSimple($tts_engine)."', ".
        "tts_text = '".$db->escapeSimple($tts_text)."', ".
        "dest = '".$db->escapeSimple($dest)."' ".
        "WHERE tts_id = ".$db->escapeSimple($tts_id);
    $result = $db->query($sql);
    if($db->IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}

function ttsengine_edit($ttsengine_id, $description, $ttsengine_engine, $ttsengine_cmd, $ttsengine_template) {
    global $db;
    $sql = "UPDATE tts_engines SET ".
        "ttsengine_description = '".$db->escapeSimple($description)."', ".
        "ttsengine_engine = '".$db->escapeSimple($ttsengine_engine)."', ".
        "ttsengine_template = '".$db->escapeSimple($ttsengine_template)."', ".
        "ttsengine_cmd = '".$db->escapeSimple($ttsengine_cmd)."' ".
        "WHERE ttsengine_id = ".$db->escapeSimple($ttsengine_id);
    $result = $db->query($sql);
    if($db->IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}

//-----------------------------
// Dynamic Destination Registry 

function tts_check_destinations($dest=true) {
    global $active_modules;

    $destlist = array();
    if (is_array($dest) && empty($dest)) {
        return $destlist;
    }
    $sql = "SELECT tts_id, tts_description, dest FROM tts ";
    if ($dest !== true) {
        $sql .= "WHERE dest in ('".implode("','",$dest)."')";
    }
    $results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

    $type = isset($active_modules['tts']['type'])?$active_modules['tts']['type']:'setup';

    foreach ($results as $result) {
        $thisdest = $result['dest'];
        $thisid   = $result['tts_id'];
        $destlist[] = array(
            'dest' => $thisdest,
            'description' => 'Text to Speech: '.$result['description'],
            'edit_url' => 'config.php?display=tts&type=tool&extdisplay='.urlencode($thisid),
        );
    }
    return $destlist;
}

function tts_getdest($id) {
    return array("app-tts,$id,1");
}

function tts_getdestinfo($dest) {
    if (substr(trim($dest),0,11) == 'app-tts,') {
        $grp = explode(',',$dest);
        $id = $grp[1];
        $thisqid = tts_get($id);
        if (empty($thisqid)) {
            return array();
        } else {
            return array('description' => sprintf(__("Text to Speech %s: "),$thisqid['description']),
                         'edit_url'    => 'config.php?display=tts&extdisplay='.urlencode($id),
                        );
        }
    } else {
        return false;
    }
}
