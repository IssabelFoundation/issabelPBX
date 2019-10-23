<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$type    = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'setup';
$action  = isset($_REQUEST['action']) ? $_REQUEST['action'] :  '';

if (isset($_REQUEST['delete'])) $action = 'delete'; 

$cid_id      = isset($_REQUEST['cid_id']) ? $_REQUEST['cid_id'] :  false;
$description = isset($_REQUEST['description']) ? $_REQUEST['description'] :  '';
$cid_name    = isset($_REQUEST['cid_name']) ? $_REQUEST['cid_name'] :  '';
$cid_num     = isset($_REQUEST['cid_num']) ? $_REQUEST['cid_num'] :  '';
$dest        = isset($_REQUEST['dest']) ? $_REQUEST['dest'] :  '';

$custom_variables=array();

$p_idx = 0;
$n_idx = 0;
$var = array();
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

$add_field = _("Add Field");

if (isset($_REQUEST['goto0']) && $_REQUEST['goto0']) {
    $dest = $_REQUEST[ $_REQUEST['goto0'].'0' ];
}

switch ($action) {
    case 'add':
        setcid_add($description, $cid_name, $cid_num, $dest, $variables);
        needreload();
        redirect_standard();
    break;
    case 'edit':
        setcid_edit($cid_id, $description, $cid_name, $cid_num, $dest, $variables);
        needreload();
        redirect_standard('extdisplay');
    break;
    case 'delete':
        setcid_delete($cid_id);
        needreload();
        redirect_standard();
    break;
}

?> 
<div class="rnav"><ul>
<?php 

echo '<li><a href="config.php?display=setcid&amp;type='.$type.'">'._('Add CallerID').'</a></li>';

foreach (setcid_list() as $row) {
    echo '<li><a href="config.php?display=setcid&amp;type='.$type.'&amp;extdisplay='.$row['cid_id'].'" class="rnavdata" rnavdata="'.$row['description'].','.$row['cid_name'].','.$row['cid_num'].','.$row['dest'].'">'.$row['description'].'</a></li>';

}

?>
</ul></div>
<?php

if ($extdisplay) {
    // load
    $row = setcid_get($extdisplay);
    $description = $row['description'];
    $cid_name   = htmlspecialchars($row['cid_name']);
    $cid_num   = htmlspecialchars($row['cid_num']);
    $dest      = $row['dest'];

    $vars = explode(",",$row['variables']);
    $count=0;
    foreach($vars as $setvar) {
        list ($key, $val) = preg_split("/=/",$setvar);
        ${"variables_custom_key_".$count} = $key;
        ${"variables_custom_val_".$count} = $val;
        $count++;
    }

 
    echo "<h2>"._("Edit: ")."$description ($cid_name)"."</h2>";

        $usage_list = framework_display_destination_usage(setcid_getdest($extdisplay));
        if (!empty($usage_list)) {
        ?>
            <table><tr><td colspan="2">
            <a href="#" class="info"><?php echo $usage_list['text']?>:<span><?php echo $usage_list['tooltip']?></span></a>
            </td></tr></table><br /><br />
        <?php
        }

} else {
    echo "<h2>"._("Add CallerID")."</h2>";
}

$helptext = _("Set CallerID allows you to change the caller id of the call and then continue on to the desired destination. For example, you may want to change the caller id form \"John Doe\" to \"Sales: John Doe\". Please note, the text you enter is what the callerid is changed to. To append to the current callerid, use the proper asterisk variables, such as \"\${CALLERID(name)}\" for the currently set callerid name and \"\${CALLERID(num)}\" for the currently set callerid number.");
echo $helptext;
echo $row['dest'];



?>

<form name="editSetcid" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkSetcid(editSetcid);">
    <input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="cid_id" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
    <table>
    <tr><td colspan="2"><h5><?php  echo ($extdisplay ? _("Edit CallerID Instance") : _("Add CallerID Instance")) ?><hr></h5></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Description")?>:<span><?php echo _("The descriptive name of this CallerID instance. For example \"new name here\"");?></span></a></td>
        <td><input size="30" type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("CallerID Name")?>:<span><?php echo _("The CallerID Name that you want to change to. If you are appending to the current callerid, dont forget to include the appropriate asterisk variables. If you leave this box blank, the CallerID name will be blanked");?></span></a></td>
        <td><input size="30" type="text" name="cid_name" value="<?php echo $cid_name; ?>"  tabindex="<?php echo ++$tabindex;?>"/></td> </tr>
    <td><a href="#" class="info"><?php echo _("CallerID Number")?>:<span><?php echo _("The CallerID Number that you want to change to. If you are appending to the current callerid, dont forget to include the appropriate asterisk variables. If you leave this box blank, the CallerID number will be blanked");?></span></a></td>
        <td><input size="30" type="text" name="cid_num" value="<?php echo $cid_num; ?>"  tabindex="<?php echo ++$tabindex;?>"/></td> </tr>





  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Other Variables")?><span><?php echo _("You may set any other SIP settings not present here that are allowed to be configured in the General section of sip.conf. There will be no error checking against these settings so check them carefully. They should be entered as:<br /> [setting] = [value]<br /> in the boxes below. Click the Add Field box to add additional fields. Blank boxes will be deleted when submitted.")?></span></a>
    </td>
    <td>
      <input type="text" id="variables_custom_key_0" name="variables_custom_key_0" class="variables-custom" value="<?php echo $variables_custom_key_0 ?>" tabindex="<?php echo ++$tabindex;?>"> =
      <input type="text" id="variables_custom_val_0" name="variables_custom_val_0" value="<?php echo $variables_custom_val_0 ?>" tabindex="<?php echo ++$tabindex;?>">
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
      <input type="button" id="variables-custom-add"  value="<?php echo $add_field ?>" />
    </td>
  </tr>







    <tr><td colspan="2"><br><h5><?php echo _("Destination")?>:<hr></h5></td></tr>

<?php 
//draw goto selects
echo drawselects($dest,0);
?>
            
    <tr>
        <td colspan="2"><br><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>">
            <?php if ($extdisplay) { echo '&nbsp;<input name="delete" type="submit" value="'._("Delete").'">'; } ?>
        </td>        

    </tr>
</table>
</form>

<script language="javascript">
<!--
$(document).ready(function () {

  if (!$('[name=description]').attr("value")) {
  $('[name=cid_name]').attr({value: "${CALLERID(name)}"});
  $('[name=cid_num]').attr({value: "${CALLERID(num)}"});
    }
    
 // select rnav options - fake type = edit
 /*
  $("a.rnavdata").click(function(event){
  event.preventDefault();
  linktext = $(this).text();
  rnavdata = $(this).attr("rnavdata");
  arr = rnavdata.split(",");
  $('h2').text("<?php echo _("Edit") ?>: " + arr[0]);
    $('[name=description]').attr({value: arr[0]});
  $('[name=cid_name]').attr({value: arr[1]});
  $('[name=cid_num]').attr({value: arr[2]});
  });
  */
});

  /* Add a Custom Var / Val textbox */
  $("#variables-custom-add").click(function(){
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
      <input type="text" id="variables_custom_key_'+idx+'" name="variables_custom_key_'+idx+'" class="variables-custom" value="'+key+'" tabindex="'+tabindex+'"> =\
      <input type="text" id="variables_custom_val_'+idx+'" name="variables_custom_val_'+idx+'" value="'+val+'" tabindex="'+tabindexp+'">\
    </td>\
  </tr>\
  ');
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

    return true;
}
//-->
</script>
