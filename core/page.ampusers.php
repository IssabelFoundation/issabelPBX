<?php /* $Id: page.ampusers.php 1166 2006-03-17 04:29:23Z qldrob $ */
//  routing.php Copyright (C) 2004 Greg MacLellan (greg@mtechsolutions.ca)
//  Asterisk Management Portal Copyright (C) 2004 Coalescent Systems Inc. (info@coalescentsystems.ca)
//    Copyright 2006-2014 Schmooze Com Inc.
//    Copyright 2022 Issabel Foundation

if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:'';
$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$tech = isset($_REQUEST['tech'])?$_REQUEST['tech']:'';

$tabindex = 0;
// populate some global variables from the request string
$set_globals = array("username","password","extension_high","extension_low","deptname");
foreach ($set_globals as $var) {
    if (isset($_REQUEST[$var])) {
        $$var = stripslashes( $_REQUEST[$var] );
    }
}
$form_password_sha1 = stripslashes(isset($_REQUEST['password_sha1'])?$_REQUEST['password_sha1']:'');

//Search ALL active modules while generating admin access list
$active_modules = module_getinfo(false, MODULE_STATUS_ENABLED);

if(is_array($active_modules)){
    foreach($active_modules as $key => $module) {
        //create an array of module sections to display
        if (isset($module['items']) && is_array($module['items'])) {
            foreach($module['items'] as $itemKey => $item) {
                $listKey = (!empty($item['display']) ? $item['display'] : $itemKey);
                $item['rawname'] = $module['rawname'];
                $module_list[ $listKey ] = $item;
            }
        }
    }
}

// extensions vs device/users ... module_list setting
if (isset($amp_conf["AMPEXTENSIONS"]) && ($amp_conf["AMPEXTENSIONS"] == "deviceanduser")) {
       unset($module_list["extensions"]);
} else {
       unset($module_list["devices"]);
       unset($module_list["users"]);
}

// no more adding the APPLY Changes bar to module list because array_multisort messes up integer array keys
// $module_list['99'] = array('category' => NULL, 'name' => _("Apply Changes Bar"));

// changed from $module_name to $admin_module_name because the former is used by framework
foreach ($module_list as $key => $row) {
    $module_category[$key] = $row['category'];
    //$admin_module_name[$key] = $row['name'];
    $admin_module_name[$key] = dgettext($row['rawname'],$row['name']);
}
array_multisort($module_category, SORT_ASC, $admin_module_name, SORT_ASC, $module_list);

$sections = array();
if (isset($_REQUEST["sections"])) {
    if (is_array($_REQUEST["sections"])) {
        $sections = $_REQUEST["sections"];
    } else {
        //TODO do we even need this??
        $sections = explode(";",$_REQUEST["sections"]);
    }
}

//if submitting form, update database
switch ($action) {
    case "addampuser":
        core_ampusers_add($username, $password, $extension_low, $extension_high, $deptname, $sections);
        //indicate 'need reload' link in footer.php
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
        $_SESSION['msgtype']='success';
        redirect_standard();
    break;
    case "editampuser":
        // Check to make sure the hidden var is sane, and that they haven't changed the password field
        if (strlen($form_password_sha1)==40 && $password == "******") {
            // Password unchanged
            core_ampusers_del($extdisplay);
            core_ampusers_add($username, $form_password_sha1, $extension_low, $extension_high, $deptname, $sections);
        } elseif ($password != "******") {
            // Password has been changed
            core_ampusers_del($extdisplay);
            core_ampusers_add($username, $password, $extension_low, $extension_high, $deptname, $sections);
        }
        //indicate 'need reload' link in footer.php
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        redirect_standard('extdisplay');
    break;
    case "delete":
        core_ampusers_del($extdisplay);
        //indicate 'need reload' link in footer.php
        needreload();
        $extdisplay = ""; // go "add" screen
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        //redirect_standard();
    break;
}

$rnaventries = array();
$users       = core_ampusers_list();
foreach($users as $row) {
    $rnaventries[] = array($row[0],$row[0],'','');
}
drawListMenu($rnaventries, $type, $display, $extdisplay);
?>


<!--div class="rnav">
<ul>
    <li><a <?php  echo ($extdisplay=='' ? 'class="current"':'') ?> href="config.php?display=<?php echo urlencode($display)?>"><?php echo _("Add User")?></a></li>
<?php
//get existing trunk info
$tresults = core_ampusers_list();

foreach ($tresults as $tresult) {
    echo "\t<li><a ".($extdisplay==$tresult[0] ? 'class="current"':'')." href=\"config.php?display=".urlencode($display)."&amp;extdisplay=".urlencode($tresult[0])."\">".$tresult[0]."</a></li>\n";
}
?>
</ul>
</div-->
<div class='content'>
<?php

    if ($extdisplay) {
        echo "<h2>"._("Edit Administrator").": ".$extdisplay."</h2>";
        
        $user = getAmpUser($extdisplay);
        
        $username = $user["username"];
        $password = "******";
        $password_sha1 = $user["password_sha1"];
        $extension_high = $user["extension_high"];
        $extension_low = $user["extension_low"];
        $deptname = $user["deptname"];
        $sections = $user["sections"];
        $myaction = 'editampuser';        

    } else {
        // set defaults
        $username = "";
        $password = "";
        $deptname = "";
        
        $extension_low = "";
        $extension_high = "";
        
        $sections = array("*");
        $myaction = 'addampuser';        
        
    
        echo "<h2>"._("Add Administrator")."</h2>";
    }
?>
    
<form id="mainform" autocomplete="off" name="ampuserEdit" action="config.php" method="post" onsubmit="return checkAmpUser(ampuserEdit)">
    <input type="hidden" name="display" value="<?php echo $display?>"/>
    <input type="hidden" name="extdisplay" value="<?php echo $extdisplay ?>"/>
    <input type="hidden" name="action" value="<?php echo $myaction?>"/>
    <input type="hidden" name="tech" value="<?php echo $tech?>"/>
    <input type="hidden" name="password_sha1" value="<?php echo isset($password_sha1)?$password_sha1:'' ?>"/>

    <table class='table is-narrow is-borderless'>
    <tr><td colspan="2"><h5><?php echo dgettext('amp','General Settings');?></h5></td></tr>

<?php if (($amp_conf["AUTHTYPE"] != "database") && ($amp_conf["AUTHTYPE"] != "webserver")) { ?>            
    <tr>
        <td colspan="2">
    <?php echo '<b>'._("NOTE:").'</b>'._("Authorization Type is not set to 'database' in Advanced Setting - note that this module is not currently providing access control, and changing passwords here or adding users will have no effect unless Authorization Type is set to 'database'.") ?><br /><br />
        </td>
    </tr>
<?php } ?>
    <tr>
        <td>
            <a href=# class="info"><?php echo _("Username<span>Create a unique username for this new user</span>")?></a>
        </td><td>
            <input autofocus type="text" class="input w100" name="username" value="<?php echo $username;?>" tabindex="<?php echo ++$tabindex;?>"/>
        </td>
    </tr>
    <tr>
        <td>
            <a href=# class="info"><?php echo _("Password<span>Create a password for this new user</span>")?></a>
        </td><td>
            <input type="password" class="input w100" name="password" value="<?php echo $password; ?>" tabindex="<?php echo ++$tabindex;?>"/>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <h5><?php echo _("Access Restrictions")?></h5>
        </td>
    </tr>
    <tr>
        <td>
            <a href=# class="info"><?php echo _("Department Name<span>Restrict this user's view of Digital Receptionist menus and System Recordings to only those for this department.</span>")?></a>
        </td><td>
            <input type="text" class="input w100" name="deptname" value="<?php echo htmlspecialchars($deptname);?>" tabindex="<?php echo ++$tabindex;?>"/>
        </td>
    </tr>
    <tr>
        <td>
            <a href=# class="info"><?php echo _("Extension Range<span>Restrict this user's view to only Extensions, Ring Groups, and Queues within this range.</span>")?></a>
        </td><td>
            <input type="text" class="input input-short" name="extension_low" value="<?php echo htmlspecialchars($extension_low);?>" tabindex="<?php echo ++$tabindex;?>"/>
            &nbsp;<?php echo _('to');?>
            <input type="text" class="input input-short" name="extension_high" value="<?php echo htmlspecialchars($extension_high);?>" tabindex="<?php echo ++$tabindex;?>"/>
        </td>
    </tr>
    <tr>
        <td valign="top">
            <a href=# class="info"><?php echo _("Admin Access<span>Select the Admin Sections this user should have access to.</span>")?></a>
        </td><td>
            <select multiple class="componentSelect" name="sections[]" tabindex="<?php echo ++$tabindex;?>" size="15">
            <option></option>
<?php
    $prev_category = NULL;
    foreach ($module_list as $key => $row) {
        if ($row['category'] != $prev_category) {
            if ($prev_category)
                echo "</optgroup>\n";
            echo "<optgroup label=\""._($row['category'])."\">\n";
            $prev_category = $row['category'];
        }

        echo "<option value=\"".$key."\"";
        if (in_array($key, $sections)) echo " SELECTED";
        $label = modgettext::_($row['name'],$row['rawname']);
        //echo ">"._($row['name'])."</option>\n";
        echo ">".$label."</option>\n";
    }
    echo "</optgroup>\n";

    // Apply Changes Bar
    echo "<option value=\"99\"";
    if (in_array("99", $sections)) echo " SELECTED";
    echo ">"._("Apply Changes Bar")."</option>\n";

    // Apply Changes Bar
    echo "<option value=\"999\"";
    if (in_array("999", $sections)) echo " SELECTED";
    echo ">".(($amp_conf['AMPEXTENSIONS'] == 'deviceanduser')?_("Add Device"):_("Add Extension"))."</option>\n";

    // All Sections
    echo "<option value=\"*\"";
    if (in_array("*", $sections)) echo " SELECTED";
    echo ">"._("ALL SECTIONS")."</option>\n";
?>                    
            </select>
        </td>
    </tr>
    </table>
</form>

<script>

function checkAmpUser(theForm) {

    $username = theForm.username.value;
    $deptname = theForm.deptname.value;

    if ($username == "") {
        <?php echo "return warnInvalid(theForm.username,'"._("Username must not be blank")."')"?>;
    } else if (!$username.match('^[a-zA-Z][a-zA-Z0-9]+$')) {
        <?php echo "return warnInvalid(theForm.username,'"._("Username cannot start with a number, and can only contain letters and numbers")."')"?>;
    } else if ($deptname == "default") {
        <?php echo "return warnInvalid(theForm.deptname,'"._("For security reasons, you cannot use the department name default")."')"?>;
    } else if ($deptname != "" && !$deptname.match('^[a-zA-Z0-9]+$')) {
        <?php echo "return warnInvalid(theForm.deptname,'"._("Department name cannot have a space")."')"?>;
    }
    $.LoadingOverlay('show');
    return true;
}

<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
