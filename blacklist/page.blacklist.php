<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//  License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Copyright (C) 2006 Magnus Ullberg (magnus@ullberg.us)
//  Portions Copyright (C) 2010 Mikael Carlsson (mickecamino@gmail.com)
//  Copyright 2013 Schmooze Com Inc.
//  Copyright 2022 Issabel Foundation

$ast_ge_16 = version_compare($amp_conf['ASTVERSION'], "1.6", "ge");

isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';
isset($_REQUEST['number'])?$number = $_REQUEST['number']:$number='';

$filter_blocked = blacklist_getblocked();

if($ast_ge_16) {
    isset($_REQUEST['description'])?$description = $_REQUEST['description']:$description='';
}

isset($_REQUEST['editnumber'])?$editnumber = $_REQUEST['editnumber']:$editnumber='';

$dispnum = "blacklist"; //used for switch on config.php
    
//if submitting form, update database

if(isset($_REQUEST['action'])) {
    switch ($action) {
        case "add":
            blacklist_add($_POST);
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
            $_SESSION['msgtype']='success';
            redirect_standard();
        break;
        case "delete":
            blacklist_del($extdisplay);
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
            $_SESSION['msgtype']='warning';
            redirect_standard();
        break;
    case "edit":
            blacklist_del($extdisplay);
            blacklist_add($_POST);
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
            $_SESSION['msgtype']='success';
            redirect_standard('extdisplay');
            break;
    case "editgeneral":
        $blockblocked = isset($_REQUEST['blockblocked'])?$_REQUEST['blockblocked']:0;
        if(blacklist_blockblocked($blockblocked)) {
            die("OK");
        } else {
            die("ERROR");
        }
    }
}

$rnaventries = array();
$numbers     = blacklist_list();
$numberedit  = array();
foreach($numbers as $num) {
    if($num['number']=='blocked') continue;
    $rnaventries[] = array($num['number'],$num['description'],$num['number'],'');
    $numberedit[$num['number']]=$num['description'];
}
drawListMenu($rnaventries, $type, $display, $extdisplay);

?>
<div class='content'>
<?php

$myaction='add';

if ($extdisplay) {
    // load
    if(isset($numberedit[$extdisplay])) {
        $description = $numberedit[$extdisplay];
        $number = $extdisplay;
        $myaction='edit';
    } else {
        $description ='';
        unset($extdisplay);
        $myaction='add';
    }
}


echo "<h2>";
echo ($extdisplay ? _("Edit Blacklist").": $description" : _("Add Blacklist"));
echo "</h2>";
?>
<form id="mainform" autocomplete="off" name="edit" method="post" onsubmit="return edit_onsubmit(this);">
    <input type="hidden" name="display" value="<?php echo $dispnum?>">
    <input type="hidden" name="action" value="<?php echo $myaction?>">
    <input type="hidden" name="editnumber" value="">

    <?php if($ast_ge_16) {
        echo "<input type=\"hidden\" name=\"editdescription\" value=\"\">";
    }?>

    <table class='table is-narrow is-borderless'>
        <tr><td colspan="2"><h5><?php echo dgettext('amp','General Settings');?></h5></td></tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Block Unknown/Blocked Caller ID")?>
                <span><?php echo _("Check here to catch Unknown/Blocked Caller ID")?></span></a>
            </td>
            <td>
                <?php echo ipbx_yesno_checkbox("blocked",$filter_blocked,false); ?>
                <!--input type="checkbox" name="blocked" value="1" <?php echo ($filter_blocked === true?" checked=1":"");?> -->
            </td>
        </tr>
 
        <tr>
        <td colspan="2"><h5>
            <?php echo ($extdisplay ? _("Edit Blacklist").": $description" : _("Add Blacklist")); ?>
        </h5></td>
        </tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Number/CallerID")?>
                <span><?php echo _("Enter the number/CallerID you want to block")?></span></a>
            </td>
            <td><input autofocus class="input w100" type="text" name="number" value="<?php echo $number;?>" tabindex="<?php echo ++$tabindex;?>"></td>
        </tr>
        <?php if($ast_ge_16) {
            echo "<tr>";
                echo "<td><a href=\"#\" class=\"info\">"._("Description");
                echo "<span>"._("Enter a description for the number you want to block")."</span></a></td>";
                echo "<td><input class=\"input w100\" type=\"text\" name=\"description\" value=\"$description\" tabindex=\"".++$tabindex."\"></td>";
        echo "</tr>";        
        }?>

   </table>
    <?php echo process_tabindex($module_hook->hookHtml,$tabindex); ?>
</form>

<script>

    $('input[name=blocked]').on('change', function() {
        console.log('set general setting'); 

        url = window.location.href.split('?')[0] + '?type=&display=<?php echo $dispnum;?>&action=editgeneral&quietmode=1&blockblocked=' + $(this).val();
        fetch(url).then(response => { 
            if(response.ok) {
                response.text().then(text=> {
                    if(text=='OK') {
                        sweet_toast('success',ipbx.msg.framework.item_modified);
                    } else {
                        sweet_alert(ipbx.msg.framework.invalid_response);
                    }
                });
            } else {
                sweet_alert(ipbx.msg.framework.invalid_response);
            }
        });
    });

    function isDialDigitsPlus(s)
    {
        var i;

        if (isEmpty(s)) {
            return false;
        }

        for (i = 0; i < s.length; i++) {
            var c = s.charAt(i);

            if (!isCallerIDChar(c) && (c != "+")) return false;
        }
        return true;
    }


    function edit_onsubmit(theForm) {
        defaultEmptyOK = false;
        if (theForm.number.value && !isDialDigitsPlus(theForm.number.value)) {
            return warnInvalid(theForm.number, "Please enter a valid Number");
        }
        $.LoadingOverlay('show');
        return true;
    }

<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
