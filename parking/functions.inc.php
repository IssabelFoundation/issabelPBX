<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed');}
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

include_once(dirname(__FILE__) . '/functions.inc/registries.php');
include_once(dirname(__FILE__) . '/functions.inc/geters_seters.php');
include_once(dirname(__FILE__) . '/functions.inc/dialplan.php');
    
function parking_views($view,$data) {
    return load_view(dirname(__FILE__).'/views/'.$view.'.php',$data);
}

function parking_delete($id) {
    global $db;
    $sql = "DELETE FROM parkplus WHERE id = ".$db->escapeSimple($id);
    $result = $db->query($sql);
    if($db->IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}
