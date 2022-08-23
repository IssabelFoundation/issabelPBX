<?php /* $Id: page.bosssecretary.php   $ */
// Copyright (C) 2008 TI Soluciones (msalazar at solucionesit dot com dot ve) and Ing. David Hrbaty
// Copyright 2022 Issabel Foundation
// 
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

$extensionsCleaned = array();
$dispnum           = 'bosssecretary';
$title             = _("Boss Secretary");
$params            = array();
$extdisplay        = isset($_REQUEST['extdisplay'])?htmlspecialchars($_REQUEST['extdisplay']):'';
$action            = isset($_REQUEST['action'])?htmlspecialchars($_REQUEST['action']):'';

if($action=='' && $extdisplay!='') $action='showedit';
if($action=='' && $extdisplay=='') $action='showadd';

if ($action=='add' || $action=='edit') {
    $extensionsCleaned = bosssecretary_clean_remove_duplicates($_POST["bosses_extensions"], $_POST["secretaries_extensions"], $extdisplay);
}

if ($action=='add') {
    $group_number = bosssecretary_get_group_number_free();
    $chiefs = bosssecretary_str_extensions_to_array($_POST["chiefs_extensions"]);
    $errors = bosssecretary_group_add($group_number, $_POST["group_label"], $extensionsCleaned["bosses"],$extensionsCleaned["secretaries"], $chiefs);
    if (empty($errors))
    {
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
        $_SESSION['msgtype']='success';
        redirect_standard();
    }
    else
    {
        $_SESSION['msg']=base64_encode($errors[0]);
        $_SESSION['msgtype']='error';
        $params["group_label"] = $_POST["group_label"];
        $params["chiefs"] = $_POST["chiefs"];
        $params["bosses"] = $extensionsCleaned["bosses"];
        $params["secretaries"] = $extensionsCleaned["secretaries"];
        $content = bosssecretary_get_form_add( $params);
    }
}
elseif($action=='edit') {

    $chiefs = bosssecretary_str_extensions_to_array($_POST["chiefs_extensions"]);
    $errors = bosssecretary_group_edit($extdisplay, $_POST["group_label"], $extensionsCleaned["bosses"],$extensionsCleaned["secretaries"], $chiefs);
    if (empty($errors))
    {
        $group_label = htmlentities($_POST['group_label']);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp',sprintf(_("Group %s was edited successfully"),$group_label)));
        $_SESSION['msgtype']='success';
        redirect_standard('extdisplay');
    }
    else
    {
        $_SESSION['msg']=base64_encode(dgettext('amp',$errors[0]));
        $_SESSION['msgtype']='error';
        $params["extdisplay"]    = $_POST["extdisplay"];
        $params["group_label"]   = $_POST["group_label"];
        $params["chiefs"]        = $chiefs;
        $params["bosses"]        = $extensionsCleaned["bosses"];
        $params["secretaries"]   = $extensionsCleaned["secretaries"];
        $content = bosssecretary_get_form_edit( $params);
    }
}
elseif ($action=='showedit' || $action=='showadd') {

    if(bosssecretary_group_exists($extdisplay)) {
        $params = bosssecretary_set_params_to_edit(bosssecretary_get_data_of_group($extdisplay));
        $title  = _('Edit Boss Secretary Group').": ".$params['group_label'];
        $content = bosssecretary_get_form_edit($params);
    } else {
        //add
        $title  = _('Add Boss Secretary Group');
        $params['action']=$action;
        $content = bosssecretary_get_form_add($params);
    }
}
elseif ($action=='delete') {
    if (bosssecretary_group_exists($extdisplay)) {
        if (bosssecretary_group_delete($extdisplay)) {
            needreload();
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
            $_SESSION['msgtype']='warning';
            redirect_standard();
        }
    }
} else {
    die('no debe llegar aqui');
}
    
$groups      = bosssecretary_get_groups();
$linksGroups = bosssecretary_create_nav_groups_links($groups, $dispnum);
    
bosssecretary_show_nav_users($linksGroups);
bosssecretary_content($title, $content);
