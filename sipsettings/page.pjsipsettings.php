<?php /* $Id:$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

  /* Determines how many columns per row for the codecs and formats the table */
  $cols_per_row   = 4;
  $width          = (100.0 / $cols_per_row);
  $tabindex       = 0;
  $dispnum        = "sipsettings";
  $error_displays = array();
  $action                              = isset($_POST['action'])?$_POST['action']:'';
  $pjsip_settings['nat']               = isset($_POST['nat']) ? $_POST['nat'] : 'yes';
  $pjsip_settings['nat_mode']          = isset($_POST['nat_mode']) ? $_POST['nat_mode'] : 'externip';
  $pjsip_settings['externip_val']      = isset($_POST['externip_val']) ? htmlspecialchars($_POST['externip_val']) : '';
  $pjsip_settings['externhost_val']    = isset($_POST['externhost_val']) ? htmlspecialchars($_POST['externhost_val']) : '';
  $pjsip_settings['externrefresh']     = isset($_POST['externrefresh']) ? htmlspecialchars($_POST['externrefresh']) : '120';
  $pjsip_settings['allowguest']        = isset($_POST['allowguest']) ? $_POST['allowguest'] : 'no';
  $pjsip_settings['ALLOW_SIP_ANON']    = isset($_POST['ALLOW_SIP_ANON']) ? $_POST['ALLOW_SIP_ANON'] : 'no';

  $post_codec = isset($_POST['codec']) ? $_POST['codec'] : array(); 

 $codecs = array(
    'ulaw'     => '',
    'alaw'     => '',
    'slin'     => '',
    'g726'     => '',
    'gsm'      => '',
    'g729'     => '',
    'ilbc'     => '',
    'g723'     => '',
    'g726aal2' => '',
    'adpcm'    => '',
    'lpc10'    => '',
    'speex'    => '',
    'g722'     => '',
    'siren7'   => '',
    'siren14'  => '',
    'speex16'  => '',
    'slin16'   => '',
    'g719'     => '',
    'speex32'  => '',
    'slin12'   => '',
    'slin24'   => '',
    'slin32'   => '',
    'slin44'   => '',
    'slin48'   => '',
    'slin96'   => '',
    'slin192'  => '',
    'opus'     => '',
    'silk8'    => '',
    'silk12'   => '',
    'silk16'   => '',
    'silk24'   => '',
    );

  // With the new sorting, the vars should come to us in the sorted order so just use that
  //
  $pri = 1;
  foreach (array_keys($post_codec) as $codec) {
    $codecs[$codec] = $pri++;
  }
  $pjsip_settings['codecs']=$codecs;


  // QaD fix for localization, xgettext does not pickup the localization string in the code
  $add_field = _("Add Field");
  $auto_configure = _("Auto Configure");
  $add_local_network_field = _("Add Local Network Field");
  $submit_changes = _("Submit Changes");

  $p_idx = 0;
  $n_idx = 0;
  while (isset($_POST["localnet_$p_idx"])) {
    if ($_POST["localnet_$p_idx"] != '') {
      $pjsip_settings["localnet_$n_idx"] = htmlspecialchars($_POST["localnet_$p_idx"]);
      $pjsip_settings["netmask_$n_idx"]  = htmlspecialchars($_POST["netmask_$p_idx"]);
      $n_idx++;
    } 
    $p_idx++;
  }
  function cmp($a, $b) {
    if ($a == $b) {
      return 0;
    }
    if ($a == '') {
      return 1;
    } elseif ($b == '') {
      return -1;
    } else {
      return ($a > $b) ? 1 : -1;
    }
  }

  $pjsip_settings['registertimeout']   = isset($_POST['registertimeout']) ? htmlspecialchars($_POST['registertimeout']) : '20';
  $pjsip_settings['registerattempts']  = isset($_POST['registerattempts']) ? htmlspecialchars($_POST['registerattempts']) : '0';
  $pjsip_settings['maxexpiry']         = isset($_POST['maxexpiry']) ? htmlspecialchars($_POST['maxexpiry']) : '3600';
  $pjsip_settings['minexpiry']         = isset($_POST['minexpiry']) ? htmlspecialchars($_POST['minexpiry']) : '60';
  $pjsip_settings['defaultexpiry']     = isset($_POST['defaultexpiry']) ? htmlspecialchars($_POST['defaultexpiry']) : '120';

  $pjsip_settings['sip_language']      = isset($_POST['sip_language']) ? htmlspecialchars($_POST['sip_language']) : '';
  $pjsip_settings['context']           = isset($_POST['context']) ? htmlspecialchars($_POST['context']) : '';
  $pjsip_settings['ALLOW_SIP_ANON']    = isset($_POST['ALLOW_SIP_ANON']) ? $_POST['ALLOW_SIP_ANON'] : 'no';
  $pjsip_settings['bindaddr']          = isset($_POST['bindaddr']) ? htmlspecialchars($_POST['bindaddr']) : '0.0.0.0';
  $pjsip_settings['bindport']          = isset($_POST['bindport']) ? htmlspecialchars($_POST['bindport']) : '5066';
  $pjsip_settings['tlsbindaddr']       = isset($_POST['tlsbindaddr']) ? htmlspecialchars($_POST['tlsbindaddr']) : '0.0.0.0';
  $pjsip_settings['tlsbindport']       = isset($_POST['tlsbindport']) ? htmlspecialchars($_POST['tlsbindport']) : '5067';
  $pjsip_settings['certfile']          = isset($_POST['certfile']) ? htmlspecialchars($_POST['certfile']) : '/etc/asterisk/keys/asterisk.pem';

  $p_idx = 0;
  $n_idx = 0;
  while (isset($_POST["sip_custom_key_$p_idx"])) {
    if ($_POST["sip_custom_key_$p_idx"] != '') {
      $pjsip_settings["sip_custom_key_$n_idx"] = htmlspecialchars($_POST["sip_custom_key_$p_idx"]);
      $pjsip_settings["sip_custom_val_$n_idx"] = htmlspecialchars($_POST["sip_custom_val_$p_idx"]);
      $n_idx++;
    } 
    $p_idx++;
  }

switch ($action) {
  case "edit":  //just delete and re-add
    if (($errors = pjsipsettings_edit($pjsip_settings)) !== true) {
      $error_displays = process_errors($errors);
    } else {
      needreload();
      //redirect_standard();
    }
  break;
  default:
    /* only get them if first time load, if they pressed submit, use values from POST */
    $pjsip_settings = pjsipsettings_get();
}
$error_displays = array_merge($error_displays,sipsettings_check_custom_files());

?>
  <h2><?php echo _("Edit PJSIP Settings"); ?></h2>

<?php

  /* We massaged these above or they came from sipsettings_get() if this is not
   * from and edit. So extract them after sorting out the codec sub arrays.
   */
  $codecs = $pjsip_settings['codecs'];
  unset($pjsip_settings['codecs']);
  uasort($codecs, 'cmp');

  /* EXTRACT THE VARIABLE HERE - MAKE SURE THEY ARE ALL MASSAGED ABOVE */
  extract($pjsip_settings);

?>
  <form autocomplete="off" name="editSip" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
  <input type="hidden" name="action" value="edit">
  <table width="690px">

<?php
  /* if there were erros on the submit then create error box */
  if (!empty($error_displays)) {
?>
  <tr>
    <td colspan="2">
      <div class="sip-errors">
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
    <td colspan="2"><h5><?php echo _("NAT Settings") ?><hr></h5></td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("NAT")?><span><?php echo _("Asterisk NAT setting:<br /> yes = Always ignore info and assume NAT<br /> no = Use NAT mode only according to RFC3581 <br /> never = Never attempt NAT mode or RFC3581 <br /> route = Assume NAT, don't send rport")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
			<span class="radioset">
            <input id="nat-yes" type="radio" name="nat" value="yes" tabindex="<?php echo ++$tabindex;?>"<?php echo $nat=="yes"?"checked=\"yes\"":""?>/>
            <label for="nat-yes">yes</label>
            <input id="nat-no" type="radio" name="nat" value="no" tabindex="<?php echo ++$tabindex;?>"<?php echo $nat=="no"?"checked=\"no\"":""?>/>
            <label for="nat-no">no</label>
			</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("IP Configuration")?><span><?php echo _("Indicate whether the box has a public IP or requires NAT settings. Automatic configuration of what is often put in sip_nat.conf")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
			<span class="radioset">
            <input id="nat-none" type="radio" name="nat_mode" value="public" tabindex="<?php echo ++$tabindex;?>"<?php echo $nat_mode=="public"?"checked=\"public\"":""?>/>
            <label for="nat-none"><?php echo _("Public IP") ?></label>
            <input id="externip" type="radio" name="nat_mode" value="externip" tabindex="<?php echo ++$tabindex;?>"<?php echo $nat_mode=="externip"?"checked=\"externip\"":""?>/>
            <label for="externip"><?php echo _("Static IP") ?></label>
            <input id="externhost" type="radio" name="nat_mode" value="externhost" tabindex="<?php echo ++$tabindex;?>"<?php echo $nat_mode=="externhost"?"checked=\"externhost\"":""?>/>
            <label for="externhost"><?php echo _("Dynamic IP") ?></label>
			</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr class="nat-settings externip">
    <td><a href="#" class="info"><?php echo _("External IP")?><span><?php echo _("External Static IP or FQDN as seen on the WAN side of the router. (asterisk: externip)")?></span></a></td>
    <td><input type="text" id="externip_val" name="externip_val" value="<?php echo $externip_val ?>" tabindex="<?php echo ++$tabindex;?>"></td>
  </tr>

  <tr class="nat-settings externhost">
    <td>
      <a href="#" class="info"><?php echo _("Dynamic Host")?><span><?php echo _("External FQDN as seen on the WAN side of the router and updated dynamically, e.g. mydomain.dyndns.com. (asterisk: externhost)")?></span></a>
    </td>
    <td>
      <input type="text" id="externhost_val" name="externhost_val" size="30" value="<?php echo $externhost_val ?>" tabindex="<?php echo ++$tabindex;?>">
      <!--input type="text" id="externrefresh" name="externrefresh" size="3" class="validate-int" value="<?php echo $externrefresh ?>" tabindex="<?php echo ++$tabindex;?>">
      <a href="#" class="info"><small><?php echo _("Refresh Rate")?><span><?php echo _("Asterisk: externrefresh. How often to lookup and refresh the External Host FQDN, in seconds.")?></span></small></a-->
    </td>
  </tr>
  <tr class="nat-settings">
    <td>
      <a href="#" class="info"><?php echo _("Local Networks")?><span><?php echo _("Local network settings (Asterisk: localnet) in the form of ip/mask such as 192.168.1.0/255.255.255.0. For networks with more 1 lan subnets, use the Add Local Network Field button for more fields. Blank fields will be removed upon submitting.")?></span></a>
    </td>
    <td>
      <input type="text" id="localnet_0" name="localnet_0" class="localnet validate=ip" value="<?php echo $localnet_0 ?>" tabindex="<?php echo ++$tabindex;?>"> /
      <input type="text" id="netmask_0" name="netmask_0" class="netmask validate-netmask" value="<?php echo $netmask_0 ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr>

<?php
  $idx = 1;
  $var_localnet = "localnet_$idx";
  $var_netmask = "netmask_$idx";
  while (isset($$var_localnet)) {
    if ($$var_localnet != '') {
      $tabindex++;
      echo <<< END
  <tr class="nat-settings">
    <td>
    </td>
    <td>
      <input type="text" id="localnet_$idx" name="localnet_$idx" class="localnet validate-ip" value="{$$var_localnet}" tabindex="$tabindex"> /
END;
      $tabindex++;
      echo <<< END
      <input type="text" id="netmask_$idx" name="netmask_$idx" class="netmask validate-netmask" value="{$$var_netmask}" tabindex="$tabindex">
    </td>
  </tr>
END;
    }
    $idx++;
    $var_localnet = "localnet_$idx";
    $var_netmask = "netmask_$idx";
  }
  $tabindex += 40; // make room for dynamic insertion of new fields so we can add tabindexes
?>
  <tr class="nat-settings" id="auto-configure-buttons">
    <td></td>
    <td><br \>
      <input type="button" id="nat-auto-configure"  value="<?php echo $auto_configure ?>" class="nat-settings" />
      <input type="button" id="localnet-add"  value="<?php echo $add_local_network_field ?>" class="nat-settings" />
    </td>
  </tr>

  <tr>
    <td colspan="2"><h5><?php echo _("TCP/UDP Transport") ?><hr></h5></td>
  </tr>


<?php
$tt = _("Asterisk: bindaddr. The IP address to bind to and listen for calls on the Bind Port. If set to 0.0.0.0 Asterisk will listen on all addresses. It is recommended to leave this blank.");
?>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Bind Address")?><span><?php echo $tt?></span></a>
    </td>
    <td>
      <input type="text" id="bindaddr" name="bindaddr" class="validate-ip" value="<?php echo $bindaddr ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Bind Port")?><span><?php echo _("Asterisk: bindport. Local incoming UDP Port that Asterisk will bind to and listen for SIP messages. The SIP standard is 5060 and in most cases this is what you want. It is recommended to leave this blank.")?></span></a>
    </td>
    <td>
      <input type="text" id="bindport" name="bindport" class="validate-ip-port" value="<?php echo $bindport ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr>

  <tr>
    <td colspan="2"><h5><?php echo _("TLS Transport") ?><hr></h5></td>
  </tr>

<?php
$tt = _("Asterisk: bindaddr. The IP address to bind to and listen for calls on the Bind Port. If set to 0.0.0.0 Asterisk will listen on all addresses. It is recommended to leave this blank.");
?>
  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Bind Address")?><span><?php echo $tt?></span></a>
    </td>
    <td>
      <input type="text" id="tlsbindaddr" name="tlsbindaddr" class="validate-ip" value="<?php echo $tlsbindaddr ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Bind Port")?><span><?php echo _("Asterisk: bindport. Local incoming Port for TLS connections in PJSIP, must be different than regular UDP/TCP bind port")?></span></a>
    </td>
    <td>
      <input type="text" id="tlsbindport" name="tlsbindport" class="validate-ip-port" value="<?php echo $tlsbindport ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Certificate")?><span><?php echo _("TLS Certificate file to use")?></span></a>
    </td>
    <td>
      <input type="text" id="certfile" name="certfile" style="width:30em;" value="<?php echo $certfile ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr>

  <tr>
    <td colspan="2"><h5><?php echo _("Audio Codecs")?><hr></h5></td>
  </tr>
  <tr>
    <td valign='top'><a href="#" class="info"><?php echo _("Codecs")?><span><?php echo _("Check the desired codecs, all others will be disabled unless explicitly enabled in a device or trunks configuration. Drag to re-order.")?></span></a></td>
    <td>
<?php
  $seq = 1;
echo '<ul class="sortable">';
  foreach ($codecs as $codec => $codec_state) {
    $tabindex++;
    $codec_trans = _($codec);
    $codec_checked = $codec_state ? 'checked' : '';
        echo '<li><a href="#">'
                . '<img src="assets/'.$dispnum.'/images/arrow_up_down.png" height="16" width="16" border="0" alt="move" style="float:none; margin-left:-6px; margin-bottom:-3px;cursor:move" /> '
                . '<input type="checkbox" '
                . ($codec_checked ? 'value="'. $seq++ . '" ' : '')
                . 'name="codec[' . $codec . ']" '
                . 'id="'. $codec . '" '
                . 'class="audio-codecs" tabindex="' . $tabindex. '" '
                . $codec_checked
                . ' />'
                . '<label for="'. $codec . '"> '
                . '<small>' . $codec_trans . '</small>'
                . ' </label></a></li>';
  }
echo '</ul>';
?>

    </td>
  </tr>

  <tr>
    <td colspan="2"><h5><hr></h5></td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Allow SIP Guests")?><span><?php echo _("Asterisk: allowguest. When set Asterisk will allow Guest SIP calls and send them to the Default SIP context. Turning this off will keep anonymous SIP calls from entering the system. Doing such will also stop 'Allow Anonymous Inbound SIP Calls' from functioning. Allowing guest calls but rejecting the Anonymous SIP calls below will enable you to see the call attempts and debug incoming calls that may be mis-configured and appearing as guests.")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
                        <span class="radioset">
            <input id="allowguest-yes" type="radio" name="allowguest" value="yes" tabindex="<?php echo ++$tabindex;?>"<?php echo $allowguest=="yes"?"checked=\"yes\"":""?>/>
            <label for="allowguest-yes"><?php echo _("Yes") ?></label>
            <input id="allowguest-no" type="radio" name="allowguest" value="no" tabindex="<?php echo ++$tabindex;?>"<?php echo $allowguest=="no"?"checked=\"no\"":""?>/>
            <label for="allowguest-no"><?php echo _("No") ?></label>
                        </span>
          </td>
        </tr>
      </table>
    </td>
  </tr>



  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Allow Anonymous Inbound SIP Calls")?><span><?php echo _("Allowing Inbound Anonymous SIP calls means that you will allow any call coming in form an un-known IP source to be directed to the 'from-pstn' side of your dialplan. This is where inbound calls come in. Although IssabelPBX severely restricts access to the internal dialplan, allowing Anonymous SIP calls does introduced additional security risks. If you allow SIP URI dialing to your PBX or use services like ENUM, you will be required to set this to Yes for Inbound traffic to work. This is NOT an Asterisk sip.conf setting, it is used in the dialplan in conjuction with the Default Context. If that context is changed above to something custom this setting may be rendered useless as well as if 'Allow SIP Guests' is set to no.")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
			<span class="radioset">
            <input id="ALLOW_SIP_ANON-YES" type="radio" name="ALLOW_SIP_ANON" value="yes" tabindex="<?php echo ++$tabindex;?>"<?php echo $ALLOW_SIP_ANON=="yes"?"checked=\"yes\"":""?>/>
            <label for="ALLOW_SIP_ANON-YES"><?php echo _("Yes") ?></label>
            <input id="ALLOW_SIP_ANON-NO" type="radio" name="ALLOW_SIP_ANON" value="no" tabindex="<?php echo ++$tabindex;?>"<?php echo $ALLOW_SIP_ANON=="no"?"checked=\"no\"":""?>/>
            <label for="ALLOW_SIP_ANON-NO"><?php echo _("No") ?></label>
			</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Language")?><span><?php echo _("Default Language for a channel, Asterisk: language")?></span></a>
    </td>
    <td>
      <input type="text" id="sip_language" name="sip_language" class="validate-alphanumeric" value="<?php echo $sip_language ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr>



  <tr><td colspan="2"><br /></td></tr>

<!--
  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Other SIP Settings")?><span><?php echo _("You may set any other SIP settings not present here that are allowed to be configured in the General section of sip.conf. There will be no error checking against these settings so check them carefully. They should be entered as:<br /> [setting] = [value]<br /> in the boxes below. Click the Add Field box to add additional fields. Blank boxes will be deleted when submitted.")?></span></a>
    </td>
    <td>
      <input type="text" id="sip_custom_key_0" name="sip_custom_key_0" class="sip-custom" value="<?php echo $sip_custom_key_0 ?>" tabindex="<?php echo ++$tabindex;?>"> =
      <input type="text" id="sip_custom_val_0" name="sip_custom_val_0" value="<?php echo $sip_custom_val_0 ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr>

<?php
  $idx = 1;
  $var_sip_custom_key = "sip_custom_key_$idx";
  $var_sip_custom_val = "sip_custom_val_$idx";
  while (isset($$var_sip_custom_key)) {
    if ($$var_sip_custom_key != '') {
      $tabindex++;
      echo <<< END
  <tr>
    <td>
    </td>
    <td>
      <input type="text" id="sip_custom_key_$idx" name="sip_custom_key_$idx" class="sip-custom" value="{$$var_sip_custom_key}" tabindex="$tabindex"> =
END;
      $tabindex++;
      echo <<< END
      <input type="text" id="sip_custom_val_$idx" name="sip_custom_val_$idx" value="{$$var_sip_custom_val}" tabindex="$tabindex">
    </td>
  </tr>
END;
    }
    $idx++;
    $var_sip_custom_key = "sip_custom_key_$idx";
    $var_sip_custom_val = "sip_custom_val_$idx";
  }
  $tabindex += 60; // make room for dynamic insertion of new fields
?>
-->

<!--
  <tr id="sip-custom-buttons">
    <td></td>
    <td><br \>
      <input type="button" id="sip-custom-add"  value="<?php echo $add_field ?>" />
    </td>
  </tr>
-->

  <tr>
    <td colspan="2"><br><h6><input name="Submit" type="submit" value="<?php echo $submit_changes ?>" tabindex="<?php echo ++$tabindex;?>"></h6></td>
  </tr>
</table>
<script language="javascript">
<!--
$(document).ready(function(){
  /* On click ajax to pbx and determine external network and localnet settings */
  $.ajaxSetup({
    timeout:10000
  });
  $("#nat-auto-configure").click(function(){
    $.ajax({
      type: 'POST',
      url: "<?php echo $_SERVER["PHP_SELF"]; ?>",
      data: "quietmode=1&skip_astman=1&handler=file&module=sipsettings&file=natget.html.php",
      dataType: 'json',
      success: function(data) {
        if (data.status == 'success') {
          $('.netmask').attr("value","");
          $('.localnet').attr("value","");
          $('#externip_val').attr("value",data.externip);
          /*  Iterate through each localnet:netmask pair. Put them into any fields on the form
           *  until we have no more, than create new ones
					 */
          var fields = $(".localnet").size();
          var cnt = 0;
          $.each(data.localnet, function(loc,mask){
            if (cnt < fields) {
              $('#localnet_'+cnt).attr("value",loc);
              $('#netmask_'+cnt).attr("value",mask);
            } else {
              addLocalnet(loc,mask);
            }
            cnt++;
          });
        } else {
          alert(data.status);
        }
      },
      error: function(data) {
        alert("<?php echo _("An Error occurred trying fetch network configuration and external IP address")?>");
      },
    });
    return false;
  });

  /* Add a Local Network / Mask textbox */
  $("#localnet-add").click(function(){
    addLocalnet("","");
  });

  /* Add a Custom Var / Val textbox */
  $("#sip-custom-add").click(function(){
    addCustomField("","");
  });

  /* Initialize Nat GUI and respond to radio button presses */
  if (document.getElementById("externhost").checked) {
    $(".externip").hide();
  } else if (document.getElementById("externip").checked) {
    $(".externhost").hide();
  } else {
    $(".nat-settings").hide();
  }
  $("#nat-none").click(function(){
    $(".nat-settings").hide();
  });
  $("#externip").click(function(){
    $(".nat-settings").show();
    $(".externhost").hide();
  });
  $("#externhost").click(function(){
    $(".nat-settings").show();
    $(".externip").hide();
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

var theForm = document.editSip;

/* Insert a localnet/netmask pair of text boxes */
function addLocalnet(localnet, netmask) {
  var idx = $(".localnet").size();
  var idxp = idx - 1;
  var tabindex = parseInt($("#netmask_"+idxp).attr('tabindex')) + 1;
  var tabindexp = tabindex + 1;

  $("#auto-configure-buttons").before('\
  <tr class="nat-settings">\
    <td>\
    </td>\
    <td>\
      <input type="text" id="localnet_'+idx+'" name="localnet_'+idx+'" class="localnet" value="'+localnet+'" tabindex="'+tabindex+'"> /\
      <input type="text" id="netmask_'+idx+'" name="netmask_'+idx+'" class="netmask validate-netmask" value="'+netmask+'" tabindex="'+tabindexp+'">\
    </td>\
  </tr>\
  ');
}

/* Insert a sip_setting/sip_value pair of text boxes */
function addCustomField(key, val) {
  var idx = $(".sip-custom").size();
  var idxp = idx - 1;
  var tabindex = parseInt($("#sip_custom_val_"+idxp).attr('tabindex')) + 1;
  var tabindexp = tabindex + 1;

  $("#sip-custom-buttons").before('\
  <tr>\
    <td>\
    </td>\
    <td>\
      <input type="text" id="sip_custom_key_'+idx+'" name="sip_custom_key_'+idx+'" class="sip-custom" value="'+key+'" tabindex="'+tabindex+'"> =\
      <input type="text" id="sip_custom_val_'+idx+'" name="sip_custom_val_'+idx+'" value="'+val+'" tabindex="'+tabindexp+'">\
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

function sipsettings_check_custom_files() {
  global $amp_conf;
  $errors = array();

  $custom_files[] = $amp_conf['ASTETCDIR']."/sip.conf";
  $custom_files[] = $amp_conf['ASTETCDIR']."/sip_nat.conf";
  $custom_files[] = $amp_conf['ASTETCDIR']."/sip_general_custom.conf";
  $custom_files[] = $amp_conf['ASTETCDIR']."/sip_custom.conf";

  foreach ($custom_files as $file) {
    if (file_exists($file)) {
      $sip_conf = @parse_ini_file($file,true);
      $main = true; // 1 is sip.conf, after that don't care
      foreach ($sip_conf as $section => $item) {
        // If setting is an array, then it is a subsection
        //
        if (!is_array($item)) {
          $msg =  sprintf(_("Settings in %s may override these. Those settings should be removed."),"<b>$file</b>");
          $errors[] = array( 'js' => '', 'div' => $msg);
          break;
        } elseif ($main && is_array($item) && strtolower($section) == 'general' && !empty($item)) {
          $msg =  sprintf(_("File %s should not have any settings in it. Those settings should be removed."),"<b>$file</b>");
          $errors[] = array( 'js' => '', 'div' => $msg);
          break;
        }
        $main = false;
      }
    }
  }
  return $errors;
}


?>
