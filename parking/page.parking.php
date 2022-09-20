<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed');}

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$extdisplay = isset($_REQUEST['extdisplay']) ? $_REQUEST['extdisplay'] : '';
if($extdisplay!='' && $id=='')  $id=$extdisplay;

//die($action);

$parking_defaults = array(
    "name" => "",
    "type" => "public",
    "parkext" => "",
    "parkpos" => "",
    "numslots" => 4,
    "parkingtime" => 45,
    "parkedmusicclass" => "default",
    "generatehints" => "yes",
    "generatefc" => "yes",
    "findslot" => "first",
    "parkedplay" => "both",
    "parkedcalltransfers" => "caller",
    "parkedcallreparking" => "caller",
    "alertinfo" => "",
    "cidpp" => "",
    "autocidpp" => "",
    "announcement_id" => null,
    "comebacktoorigin" => "yes",
    "dest" => ""
);

$data = array();

if(!isset($action)) $action='add';

switch ($action) {
    case "add":
    case "update":
        $vars = array();
        foreach(array_keys($parking_defaults) as $k) {
            if(isset($_POST[$k]))
                $vars[$k] = $_POST[$k];
        }
        if(!empty($vars)) {
            $vars['dest'] = (isset($_POST['goto0']) && isset($_POST[$_POST['goto0']])) ? $_POST[$_POST['goto0']] : '';
            if($action == 'update') {
                needreload();
                $vars['id'] = $_REQUEST['extdisplay'];
                $id = parking_save($vars);
                $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
                $_SESSION['msgtype']='success';
                $_SESSION['msgtstamp']=time();
                redirect_standard('extdisplay');
            } else {
                needreload();
                $id = parking_save($vars);
                $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
                $_SESSION['msgtype']='success';
                $_SESSION['msgtstamp']=time();
                redirect_standard();
            }
        }
        break;
    case "delete":
        parking_delete($extdisplay);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        $_SESSION['msgtstamp']=time();
        redirect_standard();
        break;
}

/*
if(!empty($action) && ($action == 'update' || $action == 'add')) {
    $vars = array();
    foreach(array_keys($parking_defaults) as $k) {
        if(isset($_POST[$k]))
            $vars[$k] = $_POST[$k];
    }
    if(!empty($vars)) {
        $vars['dest'] = (isset($_POST['goto0']) && isset($_POST[$_POST['goto0']])) ? $_POST[$_POST['goto0']] : '';
        if($action == 'update') {
            $vars['id'] = $_REQUEST['id'];
        }
        $id = parking_save($vars);
        if($id !== false){
            $action = 'modify';
        }
    }
} else if($action=='delete' && $id<>'') {
    parking_delete($id);
    needreload();
    redirect_standard();
}
*/

$all_pl = array();
$all_pl['extdisplay']=$extdisplay;
$all_pl['lots'] = parking_get('all');
echo parking_views('header',$all_pl);
if(!empty($extdisplay)) {
    $data = parking_get($extdisplay);
} else {
    $data = $parking_defaults;
}
echo parking_views('lot',$data);

/*
if(!empty($action) && !empty($id)) {
    $data = !empty($id) ? parking_get($id) : parking_get('default');
    echo parking_views('lot',$data);
} elseif(!empty($action) && $action == 'add') {
    echo parking_views('lot',$parking_defaults);
} elseif(!empty($action)) {
    $o = parking_views($action,$data);
    if(!$o) {
        $m = "paging";
        $d = module_getinfo($m);
        $data['modules']['paging'] = $d[$m]['status'] == "2" ? TRUE : FALSE;
        echo parking_views('overview',$data);
    }
} else {
    $m = "paging";
    $d = module_getinfo($m);
    $data['modules']['paging'] = $d[$m]['status'] == "2" ? TRUE : FALSE;
    echo parking_views('overview',$data);
}

*/


