<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//    License for all code of this IssabelPBX module can be found in the license file inside the module directory
//    Copyright 2013 Schmooze Com Inc.
//

function dynamicfeatures_get_config($engine) {
    global $ext;
    global $core_conf;
    switch ($engine) {
        case 'asterisk':
            //    ext->add something
            foreach(dynamicfeatures_list() as $row) {

                $name              = $row['name'];
                $dtmf              = $row['dtmf'];
                $activate_on       = $row['activate_on'];
                $application       = $row['application'];
                $arguments         = $row['arguments'];
                $moh_class         = $row['moh_class'];
                $core_conf->addApplicationMap($name, $dtmf . ",$activate_on,$application,$arguments,$moh_class",true);
            }

        break;
    }
}

function dynamicfeatures_list() {
    global $db;
    $sql = "SELECT * FROM dynamicfeatures ORDER BY name";
    $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($results)) {
        die_issabelpbx($results->getMessage()."<br><br>Error selecting from dynamicfeatures");    
    }
    return $results;
}

function dynamicfeatures_get($id) {
    global $db;
    $sql = "SELECT * FROM dynamicfeatures WHERE id = ".$db->escapeSimple($id);
    $row = $db->getRow($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($row)) {
        die_issabelpbx($row->getMessage()."<br><br>Error selecting row from dynamicfeatures");    
    }
    
    return $row;
}

function dynamicfeatures_add($name, $dtmf, $activate_on, $application, $arguments, $moh_class) {
    global $db, $amp_conf;

    $sql = "INSERT INTO dynamicfeatures (name, dtmf,activate_on,application,arguments,moh_class) VALUES (".
        "'".$db->escapeSimple($name)."', ".
        "'".$db->escapeSimple($dtmf)."', ".
        "'".$db->escapeSimple($activate_on)."', ".
        "'".$db->escapeSimple($application)."', ".
        "'".$db->escapeSimple($arguments)."', ".
        "'".$db->escapeSimple($moh_class)."')";
    $result = $db->query($sql);
    if(DB::IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
    if(method_exists($db,'insert_id')) {
        $id = $db->insert_id();
    } else {
        $id = $amp_conf["AMPDBENGINE"] == "sqlite3" ? sqlite_last_insert_rowid($db->connection) : mysql_insert_id($db->connection);
    }
    return($id);
}

function dynamicfeatures_delete($id) {
    global $db;
    $sql = "DELETE FROM dynamicfeatures WHERE id = ".$db->escapeSimple($id);
    $result = $db->query($sql);
    if(DB::IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}

function dynamicfeatures_edit($id, $name, $dtmf, $activate_on, $application, $arguments, $moh_class) { 
    global $db;
    $sql = "UPDATE dynamicfeatures SET ".
        "name = '".$db->escapeSimple($name)."', ".
        "dtmf = '".$db->escapeSimple($dtmf)."', ".
        "activate_on = '".$db->escapeSimple($activate_on)."', ".
        "application = '".$db->escapeSimple($application)."', ".
        "arguments = '".$db->escapeSimple($arguments)."', ".
        "moh_class = '".$db->escapeSimple($moh_class)."' ".
        "WHERE id = ".$db->escapeSimple($id);
    $result = $db->query($sql);
    if(DB::IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}

function dynamicfeatures_configpageinit($pagename) {
    global $currentcomponent;

    $action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
    $extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
    $extension = isset($_REQUEST['extension'])?$_REQUEST['extension']:null;
    $tech_hardware = isset($_REQUEST['tech_hardware'])?$_REQUEST['tech_hardware']:null;

    return true;
}


?>
