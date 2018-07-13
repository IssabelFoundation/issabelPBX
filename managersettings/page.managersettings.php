<?php /* $Id:$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

  /* Determines how many columns per row for the codecs and formats the table */
  $cols_per_row   = 4;
  $width          = (100.0 / $cols_per_row);
  $tabindex       = 0;
  $dispnum        = "managersettings";
  $error_displays = array();
  $action         = isset($_POST['action'])?$_POST['action']:'';
	
  // With the new sorting, the vars should come to us in the sorted order so just use that
  //
  $pri = 1;
  $manager_settings['webenabled']        = isset($_POST['webenabled']) ? $_POST['webenabled'] : 'no';
  $manager_settings['displayconnects']   = isset($_POST['displayconnects']) ? $_POST['displayconnects'] : 'no';
  $manager_settings['timestampevents']   = isset($_POST['timestampevents']) ? $_POST['timestampevents'] : 'no';
  //$manager_settings['port']              = isset($_POST['port']) ? $_POST['port'] : '5038';
  //$manager_settings['bindaddr']          = isset($_POST['bindaddr']) ? $_POST['bindaddr'] : '5038';
  $manager_settings['channelvars']       = isset($_POST['channelvars']) ? $_POST['channelvars'] : 'no';

  $p_idx = 0;
  $n_idx = 0;
  while (isset($_POST["manager_custom_key_$p_idx"])) {
    if ($_POST["manager_custom_key_$p_idx"] != '') {
      $manager_settings["manager_custom_key_$n_idx"] = htmlspecialchars($_POST["manager_custom_key_$p_idx"]);
      $manager_settings["manager_custom_val_$n_idx"] = htmlspecialchars($_POST["manager_custom_val_$p_idx"]);
      $n_idx++;
    }
    $p_idx++;
  }

  switch ($action) {
    case "edit":  //just delete and re-add
      if (($errors = managersettings_edit($manager_settings)) !== true) {
        $error_displays = process_errors($errors);
      } else {
        needreload();
        //redirect_standard();
      }
      break;
    default:
      /* only get them if first time load, if they pressed submit, use values from POST */
      $manager_settings = managersettings_get();
  }

?>
  <h2><?php echo _("Edit Settings"); ?></h2>

<?php

  /* EXTRACT THE VARIABLE HERE - MAKE SURE THEY ARE ALL MASSAGED ABOVE */
  extract($manager_settings);

?>
  <form autocomplete="off" name="editMgr" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
  <input type="hidden" name="action" value="edit">
  <table width="690px">

<?php
  /* if there were erros on the submit then create error box */
  if (!empty($error_displays)) {
?>
  <tr>
    <td colspan="2">
      <div class="manager-errors">
        <p><?php echo _("ERRORS") ?></p>
        <ul>
<?php
    foreach ($error_displays as $div_disp) {
      echo "<li>".$div_disp['div']."</li>";
    }
?>
        </ul>
      </div>
    </td>
  </tr>
<?php
  }
?>
  <tr>
    <td colspan="2"><h5><?php echo _("General Asterisk Manager Settings")?><hr></h5></td>
  </tr>
  <tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Web Enabled")?><span><?php echo _("You can  make the manager interface available over http/https if Asterisk's http server is enabled in  http.conf")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
            <span class="radioset">
            <input id="webenabled-yes" type="radio" name="webenabled" value="yes" tabindex="<?php echo ++$tabindex;?>"<?php echo $webenabled=="yes"?"checked=\"yes\"":""?>/>
            <label for="webenabled-yes">yes</label>
            <input id="webenabled-no" type="radio" name="webenabled" value="no" tabindex="<?php echo ++$tabindex;?>"<?php echo $webenabled=="no"?"checked=\"no\"":""?>/>
            <label for="webenabled-no">no</label>
            </span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Display Connects")?><span><?php echo _("If enabled, any AMI connection will display a message in the Asterisk CLI")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
            <span class="radioset">
            <input id="displayconnects-yes" type="radio" name="displayconnects" value="yes" tabindex="<?php echo ++$tabindex;?>"<?php echo $displayconnects=="yes"?"checked=\"yes\"":""?>/>
            <label for="displayconnects-yes">yes</label>
            <input id="displayconnects-no" type="radio" name="displayconnects" value="no" tabindex="<?php echo ++$tabindex;?>"<?php echo $displayconnects=="no"?"checked=\"no\"":""?>/>
            <label for="displayconnects-no">no</label>
            </span>
          </td>
        </tr>
      </table>
    </td>
  </tr>
 
  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Timestamp Events")?><span><?php echo _("Add a Unix epoch timestamp to events (not action responses)")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
            <span class="radioset">
            <input id="timestampevents-yes" type="radio" name="timestampevents" value="yes" tabindex="<?php echo ++$tabindex;?>"<?php echo $timestampevents=="yes"?"checked=\"yes\"":""?>/>
            <label for="timestampevents-yes">yes</label>
            <input id="timestampevents-no" type="radio" name="timestampevents" value="no" tabindex="<?php echo ++$tabindex;?>"<?php echo $timestampevents=="no"?"checked=\"no\"":""?>/>
            <label for="timestampevents-no">no</label>
            </span>
          </td>
        </tr>
      </table>
    </td>
  </tr>
 
  <!--tr>
    <td>
      <a href="#" class="info"><?php echo _("Bind Address")?><span><?php echo _("Asterisk: bindaddr. The IP address to bind to and listen for AMI. If set to 0.0.0.0 Asterisk will listen on all addresses. To bind to multiple IP addresses or ports, use the Other 'Manager Settings' fields where you can put settings such as:<br /> bindaddr=192.168.10.100:4555.<br />")?></span></a>
    </td>
    <td>
      <input type="text" id="bindaddr" name="bindaddr" class="validate-ip" value="<?php echo $bindaddr ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Bind Port")?><span><?php echo _("Asterisk: bindport. TCP Port that Asterisk will bind to and listen for Manager users. The Manager standard is 5038 and in most cases this is what you want. It is recommended to leave this as default.")?></span></a>
    </td>
    <td>
      <input type="text" id="port" name="port" class="validate-ip-port" value="<?php echo $port ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr-->

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Channel Variables")?><span><?php echo _("Comma separated list of channel variables to broadcast inside AMI events")?></span></a>
    </td>
    <td>
      <textarea id="channelvars" name="channelvars" class='form-control' tabindex="<?php echo ++$tabindex;?>"><?php echo $channelvars; ?></textarea>
    </td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Other Manager Settings")?><span><?php echo _("You may set any other Manager settings not present here that are allowed to be configured in the General section of manager.conf. There will be no error checking against these settings so check them carefully. They should be entered as:<br /> [setting] = [value]<br /> in the boxes below. Click the Add Field box to add additional fields. Blank boxes will be deleted when submitted.")?></span></a>
    </td>
    <td>
      <input type="text" id="manager_custom_key_0" name="manager_custom_key_0" class="manager-custom" value="<?php echo $manager_custom_key_0 ?>" tabindex="<?php echo ++$tabindex;?>"> =
      <input type="text" id="manager_custom_val_0" name="manager_custom_val_0" value="<?php echo $manager_custom_val_0 ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr>

<?php
  $idx = 1;
  $var_manager_custom_key = "manager_custom_key_$idx";
  $var_manager_custom_val = "manager_custom_val_$idx";
  while (isset($$var_manager_custom_key)) {
    if ($$var_manager_custom_key != '') {
      $tabindex++;
      echo <<< END
  <tr>
    <td>
    </td>
    <td>
      <input type="text" id="manager_custom_key_$idx" name="manager_custom_key_$idx" class="manager-custom" value="{$$var_manager_custom_key}" tabindex="$tabindex"> =
END;
      $tabindex++;
      echo <<< END
      <input type="text" id="manager_custom_val_$idx" name="manager_custom_val_$idx" value="{$$var_manager_custom_val}" tabindex="$tabindex">
    </td>
  </tr>
END;
    }
    $idx++;
    $var_manager_custom_key = "manager_custom_key_$idx";
    $var_manager_custom_val = "manager_custom_val_$idx";
  }
  $tabindex += 60; // make room for dynamic insertion of new fields
?>
  <tr id="manager-custom-buttons">
    <td></td>
    <td><br \>
      <input type="button" id="manager-custom-add"  value="<?php echo _("Add Field")?>" />
    </td>
  </tr>

  <tr>
    <td colspan="2"><br><h6><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>"></h6></td>
  </tr>
</table>
<script language="javascript">
<!--
$(document).ready(function(){

  /* Add a Custom Var / Val textbox */
  $("#manager-custom-add").click(function(){
    addCustomField("","");
  });

<?php
  /* this will insert the addClass jquery calls to all id's in error */
  if (!empty($error_displays)) {
    foreach ($error_displays as $js_disp) {
      echo "  ".$js_disp['js'];
    }
  }
?>
});

var theForm = document.editMgr;

/* Insert a manager_setting/manager_value pair of text boxes */
function addCustomField(key, val) {
  var idx = $(".manager-custom").size();
  var idxp = idx - 1;
  var tabindex = parseInt($("#manager_custom_val_"+idxp).attr('tabindex')) + 1;
  var tabindexp = tabindex + 1;

  $("#manager-custom-buttons").before('\
  <tr>\
    <td>\
    </td>\
    <td>\
      <input type="text" id="manager_custom_key_'+idx+'" name="manager_custom_key_'+idx+'" class="manager-custom" value="'+key+'" tabindex="'+tabindex+'"> =\
      <input type="text" id="manager_custom_val_'+idx+'" name="manager_custom_val_'+idx+'" value="'+val+'" tabindex="'+tabindexp+'">\
    </td>\
  </tr>\
  ');
}
//-->
</script>
</form>
<?php		

/********** UTILITY FUNCTIONS **********/

function process_errors($errors) {
  foreach($errors as $error) {
    $error_display[] = array(
      'js' => "$('#".$error['id']."').addClass('validation-error');\n",
      'div' => $error['message'],
    );
  }
  return $error_display;
}


?>
