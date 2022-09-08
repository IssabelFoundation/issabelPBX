<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//  License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Copyright 2022 Issabel Foundation

$tabindex = 0;
$type        = isset($_REQUEST['type'])        ? $_REQUEST['type']        : 'setup';
$action      = isset($_REQUEST['action'])      ? $_REQUEST['action']      : '';
$cid_id      = isset($_REQUEST['cid_id'])      ? $_REQUEST['cid_id']      : false;
$description = isset($_REQUEST['description']) ? $_REQUEST['description'] : '';
$cid_name    = isset($_REQUEST['cid_name'])    ? $_REQUEST['cid_name']    : '';
$cid_num     = isset($_REQUEST['cid_num'])     ? $_REQUEST['cid_num']     : '';
$dest        = isset($_REQUEST['dest'])        ? $_REQUEST['dest']        : '';

if (isset($_REQUEST['delete'])) $action = 'delete'; 

$custom_variables = array();
$var              = array();

$p_idx     = 0;
$n_idx     = 0;
$variables ='';

while (isset($_POST["variables_custom_key_$p_idx"])) {
  if ($_POST["variables_custom_key_$p_idx"] != '') {
    $custom_variables["variables_custom_key_$n_idx"] = htmlspecialchars($_POST["variables_custom_key_$p_idx"]);
    $custom_variables["variables_custom_val_$n_idx"] = htmlspecialchars($_POST["variables_custom_val_$p_idx"]);
    $var[] = htmlspecialchars($_POST["variables_custom_key_$p_idx"])."=".htmlspecialchars($_POST["variables_custom_val_$p_idx"]);
    $n_idx++;
  }
  $p_idx++;
}
if(count($var)>0) {
    $variables = implode(",",$var);
} else {
    $variables='';
}

$add_field = _("Add Variable");

if (isset($_REQUEST['goto0']) && $_REQUEST['goto0']) {
    $dest = $_REQUEST[ $_REQUEST['goto0'] ];
}

switch ($action) {
    case 'add':
        setcid_add($description, $cid_name, $cid_num, $dest, $variables);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
        $_SESSION['msgtype']='success';
        redirect_standard();
    break;
    case 'edit':
        setcid_edit($cid_id, $description, $cid_name, $cid_num, $dest, $variables);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        redirect_standard('extdisplay');
    break;
    case 'delete':
        setcid_delete($cid_id);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        redirect_standard();
    break;
}

$rnavitems = array();
$setcids   = setcid_list();
foreach ($setcids as $row) {
    $rnavitems[]=array($row['cid_id'],$row['description'],'','');
}
drawListMenu($rnavitems, $type, $display, $extdisplay);

?> 
<div class='content'>
<?php

if ($extdisplay) {
    // load
    $row = setcid_get($extdisplay);
    $description = $row['description'];
    $cid_name    = htmlspecialchars($row['cid_name']);
    $cid_num     = htmlspecialchars($row['cid_num']);
    $dest        = $row['dest'];

    $vars = explode(",",$row['variables']);
    $count=0;
    foreach($vars as $setvar) {
        list ($key, $val) = preg_split("/=/",$setvar);
        ${"variables_custom_key_".$count} = $key;
        ${"variables_custom_val_".$count} = $val;
        $count++;
    }
}

$helptext = _("Set CallerID allows you to change the caller id of the call and then continue on to the desired destination. For example, you may want to change the caller id form \"John Doe\" to \"Sales: John Doe\". Please note, the text you enter is what the callerid is changed to. To append to the current callerid, use the proper asterisk variables, such as \"\${CALLERID(name)}\" for the currently set callerid name and \"\${CALLERID(num)}\" for the currently set callerid number. You may also set any number of additional channel variables from here.");
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

echo "<div class='is-flex'><h2>".($extdisplay ? _('Edit CallerID').': '.$description : _("Add CallerID"))."</h2>$help</div>\n";

if ($extdisplay) {
    $usage_list = framework_display_destination_usage(setcid_getdest($extdisplay));
    if (!empty($usage_list)) {
        echo ipbx_usage_info($usage_list['text'],$usage_list['tooltip']);
    }
}

if(!isset($variables_custom_key_0)) { $variables_custom_key_0=''; }
if(!isset($variables_custom_val_0)) { $variables_custom_val_0=''; }

?>

<form id="mainform" name="editSetcid" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkSetcid(this);">
    <input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="cid_id" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
    <table class='table is-borderless is-narrow'>
    <tr><td colspan="2"><h5><?php echo dgettext('amp','General Settings');?></h5></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Description")?><span><?php echo _("The descriptive name of this CallerID instance. For example \"new name here\"");?></span></a></td>
        <td><input autofocus class="input w100" type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("CallerID Name")?><span><?php echo _("The CallerID Name that you want to change to. If you are appending to the current callerid, dont forget to include the appropriate asterisk variables. If you leave this box blank, the CallerID name will be blanked");?></span></a></td>
        <td><input class="input w100" type="text" name="cid_name" value="<?php echo $cid_name; ?>"  tabindex="<?php echo ++$tabindex;?>"/></td> 
    </tr>
        <td><a href="#" class="info"><?php echo _("CallerID Number")?><span><?php echo _("The CallerID Number that you want to change to. If you are appending to the current callerid, dont forget to include the appropriate asterisk variables. If you leave this box blank, the CallerID number will be blanked");?></span></a></td>
        <td><input class="input w100" type="text" name="cid_num" value="<?php echo $cid_num; ?>"  tabindex="<?php echo ++$tabindex;?>"/></td> 
    </tr>
    <tr>
        <td>
            <a href="#" class="info"><?php echo _("Other Variables")?><span><?php echo _("You may set any other variables that will be set for the channel, with any name and value you want, as using Set() directly from the dialplan. They should be entered as:<br /> [variable] = [value]<br /> in the boxes below. Click the Add Variable box to add additional variables. Blank boxes will be deleted when submitted.")?></span></a>
        </td>
        <td>
            <input type="text" id="variables_custom_key_0" name="variables_custom_key_0" class="valueinput variables-custom" value="<?php echo $variables_custom_key_0 ?>" tabindex="<?php echo ++$tabindex;?>"> =
            <input type="text" id="variables_custom_val_0" name="variables_custom_val_0" class="valueinput" value="<?php echo $variables_custom_val_0 ?>" tabindex="<?php echo ++$tabindex;?>">
        </td>
    </tr>

<?php
  $idx = 1;
  $var_variables_custom_key = "variables_custom_key_$idx";
  $var_variables_custom_val = "variables_custom_val_$idx";
  while (isset($$var_variables_custom_key)) {
    if ($$var_variables_custom_key != '') {
      $tabindex++;
      echo <<< END
  <tr>
    <td>
    </td>
    <td>
      <input type="text" id="variables_custom_key_$idx" name="variables_custom_key_$idx" class="variables-custom" value="{$$var_variables_custom_key}" tabindex="$tabindex"> =
END;
      $tabindex++;
      echo <<< END
      <input type="text" id="variables_custom_val_$idx" name="variables_custom_val_$idx" value="{$$var_variables_custom_val}" tabindex="$tabindex">
    </td>
  </tr>
END;
    }
    $idx++;
    $var_variables_custom_key = "variables_custom_key_$idx";
    $var_variables_custom_val = "variables_custom_val_$idx";
  }
  $tabindex += 60; // make room for dynamic insertion of new fields
?>
    <tr id="variables-custom-buttons">
        <td></td>
        <td><br \>
            <input type="button" id="variables-custom-add"  tabindex="<?php echo ++$tabindex;?>" class="button is-small is-rounded" value="<?php echo $add_field ?>" />
        </td>
    </tr>

    <tr><td colspan="2"><br><h5><?php echo _("Destination")?></h5></td></tr>

<?php 
//draw goto selects
echo drawselects($dest,0);
?>
            
</table>
</form>

<script>

$(document).ready(function () {
    if (!$('[name=description]').attr("value")) {
        $('[name=cid_name]').attr({value: "${CALLERID(name)}"});
        $('[name=cid_num]').attr({value: "${CALLERID(num)}"});
    }
});

/* Add a Custom Var / Val textbox */
$("#variables-custom-add").on('click',function(){
    addCustomField("","");
});

function addCustomField(key, val) {
  var idx = $(".variables-custom").size();
  var idxp = idx - 1;
  var tabindex = parseInt($("#variables_custom_val_"+idxp).attr('tabindex')) + 1;
  var tabindexp = tabindex + 1;

  $("#variables-custom-buttons").before('\
  <tr>\
    <td>\
    </td>\
    <td>\
      <input type="text" id="variables_custom_key_'+idx+'" name="variables_custom_key_'+idx+'" class="valueinput variables-custom" value="'+key+'" tabindex="'+tabindex+'"> =\
      <input type="text" id="variables_custom_val_'+idx+'" name="variables_custom_val_'+idx+'" class="valueinput" value="'+val+'" tabindex="'+tabindexp+'">\
    </td>\
  </tr>\
  ');
  $('#variables_custom_key_'+idx).focus();
}

function checkSetcid(theForm) {
    var msgInvalidDescription = "<?php echo _('Invalid description specified'); ?>";

    // set up the Destination stuff
    setDestinations(theForm, '_post_dest');

    // form validation
    defaultEmptyOK = false;    
    if (isEmpty(theForm.description.value))
        return warnInvalid(theForm.description, msgInvalidDescription);

    if (!validateDestinations(theForm, 1, true))
        return false;

    $.LoadingOverlay('show');
    return true;
}

<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
