<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
//the extension we are currently displaying
$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:'';
$name = isset($_REQUEST['name'])?$_REQUEST['name']:'';
$secret = isset($_REQUEST['secret'])?$_REQUEST['secret']:'';
$deny = isset($_REQUEST['deny'])?$_REQUEST['deny']:'0.0.0.0/0.0.0.0';
$permit = isset($_REQUEST['permit'])?$_REQUEST['permit']:'127.0.0.1/255.255.255.0';
$dispnum = "manager"; //used for switch on config.php

$engineinfo = engine_getinfo();
$astver =  $engineinfo['version'];
$ast_ge_16 = version_compare($astver, '1.6', 'ge');
$ast_ge_11 = version_compare($astver, '11', 'ge');

$tabindex = 0;

//if submitting form, update database
global $amp_conf;

if($action == 'add' || $action == 'delete') {
    $ampuser = $amp_conf['AMPMGRUSER'];
    if($ampuser == $name) {
        $action = 'conflict';
    }
}

switch ($action) {
    case "add":
        $rights = manager_format_in($_REQUEST);
        manager_add($name,$secret,$deny,$permit,$rights['read'],$rights['write']);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        redirect_standard();
    break;
    case "delete":
        manager_del($extdisplay);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        $_SESSION['msgtstamp']=time();
        redirect_standard();
    break;
    case "edit":  //just delete and re-add
        manager_del($name);
        $rights = manager_format_in($_REQUEST);
        manager_add($name,$secret,$deny,$permit,$rights['read'],$rights['write']);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        redirect_standard('extdisplay');
    break;
    case "conflict":
        //do nothing we are conflicting with the IssabelPBX Asterisk Manager User
    break;
}

$managers = manager_list();

$rnaventries = array();
foreach($managers as $manager) {
    $rnaventries[] = array($manager['name'],$manager['name'],'','');
}
drawListMenu($rnaventries, $type, $display, $extdisplay);
?>
<div class='content'>
<?php

if ($extdisplay){
    //get details for this manager
    $thisManager = manager_get($extdisplay);
    extract(manager_format_out($thisManager));
    echo "<h2>"._("Edit Manager").": ".$extdisplay."</h2>\n";
} else {
    echo "<h2>"._("Add Manager")."</h2>\n";
}

?>
    <form id="mainform" autocomplete="off" name="editMan" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkConf(this);">
    <input type="hidden" name="display" value="<?php echo $dispnum?>">
    <input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add') ?>">
    <table class='table is-narrow is-borderless'>
    <tr><td colspan="2"><h5><?php echo dgettext('amp','General Settings');?></h5></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Manager name")?><span><?php echo _("Name of the manager without space.")?></span></a></td>
        <td><input class="input w100" autofocus type="text" name="name" value="<?php echo (isset($name) ? $name : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Manager secret")?><span><?php echo _("Password for the manager.")?></span></a></td>
        <td><input class="input w100" type="text" name="secret" value="<?php echo (isset($secret) ? $secret : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Deny")?><span><?php echo _("If you want to deny many hosts or networks, use & char as separator.<br/><br/>Example: 192.168.1.0/255.255.255.0&10.0.0.0/255.0.0.0")?></span></a></td>
        <td><input class="input w100" type="text" name="deny" value="<?php echo (isset($deny) ? $deny : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Permit")?><span><?php echo _("If you want to permit many hosts or networks, use & char as separator. Look at deny example.")?></span></a></td>
        <td><input class="input w100" type="text" name="permit" value="<?php echo (isset($permit) ? $permit : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
    <tr>
        <td colspan="2"><h5><?php echo _("Rights")?></h5></td>
    </tr>
    <tr>
        <td colspan="2">
        <table class='table is-narrow notfixed'>
            <tr><th></th><th><?php echo _("Read")?></th><th><?php echo _("Write")?></th></tr>

<?php 
    $mgrperms = array('system','call','log','verbose','command','agent','user');
    if ($ast_ge_16) {
        $mgrperms = array_merge($mgrperms,array('config','dtmf','reporting','cdr','dialplan','originate'));
    }
    if ($ast_ge_11) {
        $mgrperms = array_merge($mgrperms,array('message'));
    }

    foreach($mgrperms as $perm) {
         $rname = "r$perm";
         $wname = "w$perm";

         echo "<tr>\n";
         echo "  <td><a href='javascript:void(0)' class='info'>$perm<span>"._("Check Asterisk documentation.")."</span></a></td>\n";
         echo "  <td>";
         echo form_switch($rname, 'true', isset($$rname));
         echo "  </td>\n";
         echo "  <td>";
         echo form_switch($wname, 'true', isset($$wname));
         echo "  </td>\n";
         echo "</tr>\n";
    }
?>
            <tr>
                <td><a href="#" class="info">ALL<span><?php echo _("Check All/None.")?></span></a></td>
                <td><?php echo form_switch('allnoner',false); ?></td>
                <td><?php echo form_switch('allnonew',false); ?></td>
            </tr>
        </table>
        </td>
    </tr>

    </table>
    </form>
    <script>
    var theForm = document.editMan;
    
    $('input[name=allnonew]').on('change',function() {
        that = this;
        $('input[type=checkbox]').each(function() { 
            type = $(this).attr('name').substr(0,1); 
            if(type=='w') { $(this).prop('checked',$(that).is(':checked')); }
        });
    });

    $('input[name=allnoner]').on('change',function() {
        that = this;
        $('input[type=checkbox]').each(function() { 
            type = $(this).attr('name').substr(0,1); 
            if(type=='r') { $(this).prop('checked',$(that).is(':checked')); }
        });
    });

    function checkConf(theForm)
    {
        var errName = "<?php echo _('The manager name cannot be empty or may not have any space in it.'); ?>";
        var errSecret = "<?php echo _('The manager secret cannot be empty.'); ?>";
        var errDeny = "<?php echo _('The manager deny is not well formatted.'); ?>";
        var errPermit = "<?php echo _('The manager permit is not well formatted.'); ?>";
        var errRead = "<?php echo _('The manager read field is not well formatted.'); ?>";
        var errWrite = "<?php echo _('The manager write field is not well formatted.'); ?>";

        defaultEmptyOK = false;
        if ((theForm.name.value.search(/\s/) >= 0) || (theForm.name.value.length == 0))
            return warnInvalid(theForm.name, errName);
        if (theForm.secret.value.length == 0)
            return warnInvalid(theForm.name, errSecret);
        // Only IP/MASK format are checked
        if (theForm.deny.value.search(/\b(?:\d{1,3}\.){3}\d{1,3}\b\/\b(?:\d{1,3}\.){3}\d{1,3}\b(&\b(?:\d{1,3}\.){3}\d{1,3}\b\/\b(?:\d{1,3}\.){3}\d{1,3}\b)*$/))
            return warnInvalid(theForm.name, errDeny);
        if (theForm.permit.value.search(/\b(?:\d{1,3}\.){3}\d{1,3}\b\/\b(?:\d{1,3}\.){3}\d{1,3}\b(&\b(?:\d{1,3}\.){3}\d{1,3}\b\/\b(?:\d{1,3}\.){3}\d{1,3}\b)*$/))
            return warnInvalid(theForm.name, errPermit);

        $.LoadingOverlay('show');
        return true;
    }

<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
