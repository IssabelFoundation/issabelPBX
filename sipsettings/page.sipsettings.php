<?php /* $Id:$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

  /* Determines how many columns per row for the codecs and formats the table */
  $cols_per_row   = 4;
  $width          = (100.0 / $cols_per_row);
  $tabindex       = 0;
  $dispnum        = "sipsettings";
  $error_displays = array();
  $action                            = isset($_POST['action'])?$_POST['action']:'';
  $sip_settings['nat']               = isset($_POST['nat']) ? $_POST['nat'] : 'yes';
  $sip_settings['nat_mode']          = isset($_POST['nat_mode']) ? $_POST['nat_mode'] : 'externip';
  $sip_settings['externip_val']      = isset($_POST['externip_val']) ? htmlspecialchars($_POST['externip_val']) : '';
  $sip_settings['externhost_val']    = isset($_POST['externhost_val']) ? htmlspecialchars($_POST['externhost_val']) : '';
  $sip_settings['externrefresh']     = isset($_POST['externrefresh']) ? htmlspecialchars($_POST['externrefresh']) : '120';
  // QaD fix for localization, xgettext does not pickup the localization string in the code
	$add_field = _("Add Field");
	$auto_configure = _("Auto Configure");
	$add_local_network_field = _("Add Local Network Field");
	$submit_changes = _("Submit Changes");

  $p_idx = 0;
  $n_idx = 0;
  while (isset($_POST["localnet_$p_idx"])) {
    if ($_POST["localnet_$p_idx"] != '') {
      $sip_settings["localnet_$n_idx"] = htmlspecialchars($_POST["localnet_$p_idx"]);
      $sip_settings["netmask_$n_idx"]  = htmlspecialchars($_POST["netmask_$p_idx"]);
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

	$post_codec = isset($_POST['codec']) ? $_POST['codec'] : array(); 
	$post_vcodec = isset($_POST['vcodec']) ? $_POST['vcodec'] : array(); 
	
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
  $sip_settings['codecs']            = $codecs;
  $sip_settings['g726nonstandard']   = isset($_POST['g726nonstandard']) ? $_POST['g726nonstandard'] : 'no';
  $sip_settings['t38pt_udptl']       = isset($_POST['t38pt_udptl']) ? $_POST['t38pt_udptl'] : 'no';

  $video_codecs = array(
    'h261'  => '',
    'h263'  => '',
    'h263p' => '',
    'h264'  => '',
    'mpeg4' => '',
    'vp8'   => '',
    );

  // With the new sorting, the vars should come to us in the sorted order so just use that
  //
  $pri = 1;
  foreach (array_keys($post_vcodec) as $vcodec) {
    $video_codecs[$vcodec] = $pri++;
  }
  $sip_settings['video_codecs']      = $video_codecs;
  $sip_settings['videosupport']      = isset($_POST['videosupport']) ? $_POST['videosupport'] : 'no';
  $sip_settings['maxcallbitrate']    = isset($_POST['maxcallbitrate']) ? htmlspecialchars($_POST['maxcallbitrate']) : '384';

  $sip_settings['canreinvite']       = isset($_POST['canreinvite']) ? $_POST['canreinvite'] : 'no';
  $sip_settings['rtptimeout']        = isset($_POST['rtptimeout']) ? htmlspecialchars($_POST['rtptimeout']) : '30';
  $sip_settings['rtpholdtimeout']    = isset($_POST['rtpholdtimeout']) ? htmlspecialchars($_POST['rtpholdtimeout']) : '300';
  $sip_settings['rtpkeepalive']      = isset($_POST['rtpkeepalive']) ? htmlspecialchars($_POST['rtpkeepalive']) : '0';
  $sip_settings['rtpstart']      = isset($_POST['rtpstart']) ? htmlspecialchars($_POST['rtpstart']) : '10000';
  $sip_settings['rtpend']      = isset($_POST['rtpend']) ? htmlspecialchars($_POST['rtpend']) : '20000';

  $sip_settings['checkmwi']          = isset($_POST['checkmwi']) ? htmlspecialchars($_POST['checkmwi']) : '10';
  $sip_settings['notifyringing']     = isset($_POST['notifyringing']) ? $_POST['notifyringing'] : 'yes';
  $sip_settings['notifyhold']        = isset($_POST['notifyhold']) ? $_POST['notifyhold'] : 'yes';

  $sip_settings['registertimeout']   = isset($_POST['registertimeout']) ? htmlspecialchars($_POST['registertimeout']) : '20';
  $sip_settings['registerattempts']  = isset($_POST['registerattempts']) ? htmlspecialchars($_POST['registerattempts']) : '0';
  $sip_settings['maxexpiry']         = isset($_POST['maxexpiry']) ? htmlspecialchars($_POST['maxexpiry']) : '3600';
  $sip_settings['minexpiry']         = isset($_POST['minexpiry']) ? htmlspecialchars($_POST['minexpiry']) : '60';
  $sip_settings['defaultexpiry']     = isset($_POST['defaultexpiry']) ? htmlspecialchars($_POST['defaultexpiry']) : '120';

  $sip_settings['jbenable']          = isset($_POST['jbenable']) ? $_POST['jbenable'] : 'no';
  $sip_settings['jbforce']           = isset($_POST['jbforce']) ? $_POST['jbforce'] : 'no';
  $sip_settings['jbimpl']            = isset($_POST['jbimpl']) ? $_POST['jbimpl'] : 'fixed';
  $sip_settings['jbmaxsize']         = isset($_POST['jbmaxsize']) ? htmlspecialchars($_POST['jbmaxsize']) : '200';
  $sip_settings['jbresyncthreshold'] = isset($_POST['jbresyncthreshold']) ? htmlspecialchars($_POST['jbresyncthreshold']) : '1000';
  $sip_settings['jblog']             = isset($_POST['jblog']) ? $_POST['jblog'] : 'no';

  $sip_settings['sip_language']      = isset($_POST['sip_language']) ? htmlspecialchars($_POST['sip_language']) : '';
  $sip_settings['context']           = isset($_POST['context']) ? htmlspecialchars($_POST['context']) : '';
  $sip_settings['ALLOW_SIP_ANON']    = isset($_POST['ALLOW_SIP_ANON']) ? $_POST['ALLOW_SIP_ANON'] : 'no';
  $sip_settings['bindaddr']          = isset($_POST['bindaddr']) ? htmlspecialchars($_POST['bindaddr']) : '';
  $sip_settings['bindport']          = isset($_POST['bindport']) ? htmlspecialchars($_POST['bindport']) : '';
  $sip_settings['allowguest']        = isset($_POST['allowguest']) ? $_POST['allowguest'] : 'no';
  $sip_settings['srvlookup']         = isset($_POST['srvlookup']) ? $_POST['srvlookup'] : 'no';
  $sip_settings['callevents']        = isset($_POST['callevents']) ? $_POST['callevents'] : 'no'; 

  $p_idx = 0;
  $n_idx = 0;
  while (isset($_POST["sip_custom_key_$p_idx"])) {
    if ($_POST["sip_custom_key_$p_idx"] != '') {
      $sip_settings["sip_custom_key_$n_idx"] = htmlspecialchars($_POST["sip_custom_key_$p_idx"]);
      $sip_settings["sip_custom_val_$n_idx"] = htmlspecialchars($_POST["sip_custom_val_$p_idx"]);
      $n_idx++;
    } 
    $p_idx++;
  }

switch ($action) {
  case "edit":  //just delete and re-add
    if (($errors = sipsettings_edit($sip_settings)) !== true) {
      $error_displays = process_errors($errors);
    } else {
      needreload();
      //redirect_standard();
    }
  break;
  default:
    /* only get them if first time load, if they pressed submit, use values from POST */
    $sip_settings = sipsettings_get();
}
$error_displays = array_merge($error_displays,sipsettings_check_custom_files());

?>
  <h2><?php echo _("Edit Settings"); ?></h2>

<?php

  /* We massaged these above or they came from sipsettings_get() if this is not
   * from and edit. So extract them after sorting out the codec sub arrays.
	 */
  $codecs = $sip_settings['codecs'];
  unset($sip_settings['codecs']);
  uasort($codecs, 'cmp');

  $video_codecs = $sip_settings['video_codecs'];
  unset($sip_settings['video_codecs']);
  uasort($video_codecs, 'cmp');

  /* EXTRACT THE VARIABLE HERE - MAKE SURE THEY ARE ALL MASSAGED ABOVE */
	//
  extract($sip_settings);

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
            <input id="nat-never" type="radio" name="nat" value="never" tabindex="<?php echo ++$tabindex;?>"<?php echo $nat=="never"?"checked=\"never\"":""?>/>
            <label for="nat-never">never</label>
            <input id="nat-route" type="radio" name="nat" value="route" tabindex="<?php echo ++$tabindex;?>"<?php echo $nat=="route"?"checked=\"route\"":""?>/>
            <label for="nat-route">route</label>
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
      <input type="text" id="externrefresh" name="externrefresh" size="3" class="validate-int" value="<?php echo $externrefresh ?>" tabindex="<?php echo ++$tabindex;?>">
      <a href="#" class="info"><small><?php echo _("Refresh Rate")?><span><?php echo _("Asterisk: externrefresh. How often to lookup and refresh the External Host FQDN, in seconds.")?></span></small></a>
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
    <td>
      <a href="#" class="info"><?php echo _("Non-Standard g726")?><span><?php echo _("Asterisk: g726nonstandard. If the peer negotiates G726-32 audio, use AAL2 packing order instead of RFC3551 packing order (this is required for Sipura and Grandstream ATAs, among others). This is contrary to the RFC3551 specification, the peer _should_ be negotiating AAL2-G726-32 instead.")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td >
			<span class="radioset">
            <input id="g726nonstandard-yes" type="radio" name="g726nonstandard" value="yes" tabindex="<?php echo ++$tabindex;?>"<?php echo $g726nonstandard=="yes"?"checked=\"yes\"":""?>/>
            <label for="g726nonstandard-yes"><?php echo _("Yes") ?></label>
            <input id="g726nonstandard-no" type="radio" name="g726nonstandard" value="no" tabindex="<?php echo ++$tabindex;?>"<?php echo $g726nonstandard=="no"?"checked=\"no\"":""?>/>
            <label for="g726nonstandard-no"><?php echo _("No") ?></label>
			</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("T38 Pass-Through")?><span><?php echo _("Asterisk: t38pt_udptl. Enables T38 passthrough if enabled. This SIP channels that support sending/receiving T38 Fax codecs to pass the call. Asterisk can not process the media.")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
			<span class="radioset">
            <input id="t38pt_udptl-yes" type="radio" name="t38pt_udptl" value="yes" tabindex="<?php echo ++$tabindex;?>"<?php echo $t38pt_udptl=="yes"?"checked=\"yes\"":""?>/>
            <label for="t38pt_udptl-yes"><?php echo _("Yes") ?></label>
            <input id="t38pt_udptl-no" type="radio" name="t38pt_udptl" value="no" tabindex="<?php echo ++$tabindex;?>"<?php echo $t38pt_udptl=="no"?"checked=\"no\"":""?>/>
            <label for="t38pt_udptl-no"><?php echo _("No") ?></label>
			</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td colspan="2"><h5><?php echo _("Video Codecs")?><hr></h5></td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Video Support")?><span><?php echo _("Check to enable and then choose allowed codecs.")._(" If you clear each codec and then add them one at a time, submitting with each addition, they will be added in order which will effect the codec priority.")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
			<span class="radioset">
            <input id="videosupport-yes" type="radio" name="videosupport" value="yes" tabindex="<?php echo ++$tabindex;?>"<?php echo $videosupport=="yes"?"checked=\"yes\"":""?>/>
            <label for="videosupport-yes"><?php echo _("Enabled") ?></label>
            <input id="videosupport-no" type="radio" name="videosupport" value="no" tabindex="<?php echo ++$tabindex;?>"<?php echo $videosupport=="no"?"checked=\"no\"":""?>/>
            <label for="videosupport-no"><?php echo _("Disabled") ?></label>
			</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr class="video-codecs">
    <td></td>
    <td>
      <table width="100%">
        <tr>
<?php
echo '<ul  class="sortable video-codecs">';
   foreach ($video_codecs as $codec => $codec_state) {
    $tabindex++;
    $codec_trans = _($codec);
    $codec_checked = $codec_state ? 'checked' : '';
	echo '<li><a href="#">'
		. '<img src="assets/'.$dispnum.'/images/arrow_up_down.png" height="16" width="16" border="0" alt="move" style="float:none; margin-left:-6px; margin-bottom:-3px;cursor:move" /> '
		. '<input type="checkbox" '
		. ($codec_checked ? 'value="'. $seq++ . '" ' : '')
		. 'name="vcodec[' . $codec . ']" '
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
        </tr>
      </table>
    </td>
  </tr>

  <tr class="video-codecs">
    <td>
      <a href="#" class="info"><?php echo _("Max Bit Rate")?><span><?php echo _("Maximum bitrate for video calls in kb/s")?></span></a>
    </td>
    <td><input type="text" size="3" id="maxcallbitrate" name="maxcallbitrate" class="video-codecs validate-int" value="<?php echo $maxcallbitrate ?>" tabindex="<?php echo ++$tabindex;?>"> <small><?php echo _("kb/s") ?></small></td>
  </tr>

  <tr>
    <td colspan="2"><h5><?php echo _("MEDIA & RTP Settings") ?><hr></h5></td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Reinvite Behavior")?><span><?php echo _("Asterisk: canreinvite. yes: standard reinvites; no: never; nonat: An additional option is to allow media path redirection (reinvite) but only when the peer where the media is being sent is known to not be behind a NAT (as the RTP core can determine it based on the apparent IP address the media arrives from; update: use UPDATE for media path redirection, instead of INVITE. (yes = update + nonat)")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
			<span class="radioset">
            <input id="canreinvite-yes" type="radio" name="canreinvite" value="yes" tabindex="<?php echo ++$tabindex;?>"<?php echo $canreinvite=="yes"?"checked=\"yes\"":""?>/>
            <label for="canreinvite-yes"><?php echo _("yes") ?></label>
            <input id="canreinvite-no" type="radio" name="canreinvite" value="no" tabindex="<?php echo ++$tabindex;?>"<?php echo $canreinvite=="no"?"checked=\"no\"":""?>/>
            <label for="canreinvite-no"><?php echo _("no") ?></label>
            <input id="canreinvite-nonat" type="radio" name="canreinvite" value="nonat" tabindex="<?php echo ++$tabindex;?>"<?php echo $canreinvite=="nonat"?"checked=\"nonat\"":""?>/>
            <label for="canreinvite-nonat">nonat</label>
            <input id="canreinvite-update" type="radio" name="canreinvite" value="update" tabindex="<?php echo ++$tabindex;?>"<?php echo $canreinvite=="update"?"checked=\"update\"":""?>/>
            <label for="canreinvite-update">update</label>
			</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("RTP Timers")?><span><?php echo _("Asterisk: rtptimeout. Terminate call if rtptimeout seconds of no RTP or RTCP activity on the audio channel when we're not on hold. This is to be able to hangup a call in the case of a phone disappearing from the net, like a powerloss or someone tripping over a cable.<br /> Asterisk: rtpholdtimeout. Terminate call if rtpholdtimeout seconds of no RTP or RTCP activity on the audio channel when we're on hold (must be > rtptimeout). <br /> Asterisk: rtpkeepalive. Send keepalives in the RTP stream to keep NAT open during periods where no RTP stream may be flowing (like on hold).")?></span></a>
    </td>
    <td>
      <input type="text" size="2" id="rtptimeout" name="rtptimeout" class="validate-int" value="<?php echo $rtptimeout ?>" tabindex="<?php echo ++$tabindex;?>"><small>&nbsp;(rtptimeout)</small>&nbsp;
      <input type="text" size="2" id="rtpholdtimeout" name="rtpholdtimeout" class="validate-int" value="<?php echo $rtpholdtimeout ?>" tabindex="<?php echo ++$tabindex;?>"><small>&nbsp;(rtpholdtimeout)</small>&nbsp;
      <input type="text" size="2" id="rtpkeepalive" name="rtpkeepalive" class="validate-int" value="<?php echo $rtpkeepalive ?>" tabindex="<?php echo ++$tabindex;?>"><small>&nbsp;(rtpkeepalive)</small>
    </td>
  </tr>
  
  <tr>
	  <td>
		  <a href="#" class="info"><?php echo _("RTP Port Ranges")?><span><?php echo _("Asterisk: rtpstart. The starting RTP port range.<br /> Asterisk: rtpend. The ending RTP port range.")?></span></a>
	  </td>
	  <td>
		  <input type="text" size="3" id="rtpstart" name="rtpstart" class="validate-int" value="<?php echo !empty($rtpstart) ? $rtpstart : '10000' ?>" tabindex="<?php echo ++$tabindex;?>"><small>&nbsp;(rtpstart)</small>&nbsp;
		  <input type="text" size="3" id="rtpend" name="rtpend" class="validate-int" value="<?php echo !empty($rtpend) ? $rtpend : '20000' ?>" tabindex="<?php echo ++$tabindex;?>"><small>&nbsp;(rtpend)</small>&nbsp;
	  </td>
  </tr>

  <tr>
    <td colspan="2"><h5><?php echo _("Notification & MWI")?><hr></h5></td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("MWI Polling Freq")?><span><?php echo _("Frequency in seconds to check if MWI state has changed and inform peers.")?></span></a>
    </td>
    <td><input type="text" size="3" id="checkmwi" name="checkmwi" class="validate-int" value="<?php echo $checkmwi ?>" tabindex="<?php echo ++$tabindex;?>"></td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Notify Ringing")?><span><?php echo _("Control whether subscriptions already INUSE get sent RINGING when another call is sent. Useful when using BLF.")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
			<span class="radioset">
            <input id="notifyringing-yes" type="radio" name="notifyringing" value="yes" tabindex="<?php echo ++$tabindex;?>"<?php echo $notifyringing=="yes"?"checked=\"yes\"":""?>/>
            <label for="notifyringing-yes"><?php echo _("Yes") ?></label>
            <input id="notifyringing-no" type="radio" name="notifyringing" value="no" tabindex="<?php echo ++$tabindex;?>"<?php echo $notifyringing=="no"?"checked=\"no\"":""?>/>
            <label for="notifyringing-no"><?php echo _("No") ?></label>
			</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Notify Hold")?><span><?php echo _("Control whether subscriptions INUSE get sent ONHOLD when call is placed on hold. Useful when using BLF.")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
			<span class="radioset">
            <input id="notifyhold-yes" type="radio" name="notifyhold" value="yes" tabindex="<?php echo ++$tabindex;?>"<?php echo $notifyhold=="yes"?"checked=\"yes\"":""?>/>
            <label for="notifyhold-yes"><?php echo _("Yes") ?></label>
            <input id="notifyhold-no" type="radio" name="notifyhold" value="no" tabindex="<?php echo ++$tabindex;?>"<?php echo $notifyhold=="no"?"checked=\"no\"":""?>/>
            <label for="notifyhold-no"><?php echo _("No") ?></label>
			</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td colspan="2"><h5><?php echo _("Registration Settings") ?><hr></h5></td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Registrations")?><span><?php echo _("Asterisk: registertimeout. Retry registration attempts every registertimeout seconds until successful or until registrationattempts tries have been made.<br /> Asterisk: registrationattempts. Number of times to try and register before giving up. A value of 0 means keep trying forever. Normally this should be set to 0 so that Asterisk will continue to register until successful in the case of network or gateway outages.")?></span></a>
    </td>
    <td>
      <input type="text" size="2" id="registertimeout" name="registertimeout" class="validate-int" value="<?php echo $registertimeout ?>" tabindex="<?php echo ++$tabindex;?>"><small>&nbsp;(registertimeout)</small>&nbsp;
      <input type="text" size="2" id="registerattempts" name="registerattempts" class="validate-int" value="<?php echo $registerattempts ?>" tabindex="<?php echo ++$tabindex;?>"><small>&nbsp;(registerattempts)</small>
    </td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Registration Times")?><span><?php echo _("Asterisk: minexpiry. Minimum length of registrations/subscriptions.<br /> Asterisk: maxepiry. Maximum allowed time of incoming registrations<br /> Asterisk: defaultexpiry. Default length of incoming and outgoing registrations.")?></span></a>
    </td>
    <td>
      <input type="text" size="2" id="minexpiry" name="minexpiry" class="validate-int" value="<?php echo $minexpiry ?>" tabindex="<?php echo ++$tabindex;?>"><small>&nbsp;(minexpiry)</small>&nbsp;
      <input type="text" size="3" id="maxexpiry" name="maxexpiry" class="validate-int" value="<?php echo $maxexpiry ?>" tabindex="<?php echo ++$tabindex;?>"><small>&nbsp;(maxexpiry)</small>&nbsp;
      <input type="text" size="3" id="defaultexpiry" name="defaultexpiry" class="validate-int" value="<?php echo $defaultexpiry ?>" tabindex="<?php echo ++$tabindex;?>"><small>&nbsp;(defaultexpiry)</small>
    </td>
  </tr>

  <tr>
    <td colspan="2"><h5><?php echo _("Jitter Buffer Settings") ?><hr></h5></td>
  </tr>

  <tr>
    <td><a href="#" class="info"><?php echo _("Jitter Buffer")?><span><?php echo _("Asterisk: jbenable. Enables the use of a jitterbuffer on the receiving side of a SIP channel. An enabled jitterbuffer will be used only if the sending side can create and the receiving side can not accept jitter. The SIP channel can accept jitter, thus a jitterbuffer on the receive SIP side will be used only if it is forced and enabled. An example is if receiving from a jittery channel to voicemail, the jitter buffer will be used if enabled. However, it will not be used when sending to a SIP endpoint since they usually have their own jitter buffers. See jbforce to force it's use always.")?></span></a></td>
    <td>
      <table width="100%">
        <tr>
          <td>
			<span class="radioset">
            <input id="jbenable-yes" type="radio" name="jbenable" value="yes" tabindex="<?php echo ++$tabindex;?>"<?php echo $jbenable=="yes"?"checked=\"yes\"":""?>/>
            <label for="jbenable-yes"><?php echo _("Enabled") ?></label>
            <input id="jbenable-no" type="radio" name="jbenable" value="no" tabindex="<?php echo ++$tabindex;?>"<?php echo $jbenable=="no"?"checked=\"no\"":""?>/>
            <label for="jbenable-no"><?php echo _("Disabled") ?></label>
			</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr class="jitter-buffer">
    <td>
      <a href="#" class="info"><?php echo _("Force Jitter Buffer")?><span><?php echo _("Asterisk: jbforce. Forces the use of a jitterbuffer on the receive side of a SIP channel. Normally the jitter buffer will not be used if receiving a jittery channel but sending it off to another channel such as another SIP channel to an endpoint, since there is typically a jitter buffer at the far end. This will force the use of the jitter buffer before sending the stream on. This is not typically desired as it adds additional latency into the stream.")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
			<span class="radioset">
            <input id="jbforce-yes" type="radio" name="jbforce" class="jitter-buffer" value="yes" tabindex="<?php echo ++$tabindex;?>"<?php echo $jbforce=="yes"?"checked=\"yes\"":""?>/>
            <label for="jbforce-yes"><?php echo _("Yes") ?></label>
            <input id="jbforce-no" type="radio" name="jbforce" class="jitter-buffer" value="no" tabindex="<?php echo ++$tabindex;?>"<?php echo $jbforce=="no"?"checked=\"no\"":""?>/>
            <label for="jbforce-no"><?php echo _("No") ?></label>
			</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr class="jitter-buffer">
    <td>
      <a href="#" class="info"><?php echo _("Implementation")?><span><?php echo _("Asterisk: jbimpl. Jitterbuffer implementation, used on the receiving side of a SIP channel. Two implementations are currently available:<br /> fixed: size always equals to jbmaxsize;<br /> adaptive: with variable size (the new jb of IAX2).")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
			<span class="radioset">
            <input id="jbimpl-fixed" type="radio" name="jbimpl" class="jitter-buffer" value="fixed" tabindex="<?php echo ++$tabindex;?>"<?php echo $jbimpl=="fixed"?"checked=\"fixed\"":""?>/>
            <label for="jbimpl-fixed"><?php echo _("Fixed") ?></label>
            <input id="jbimpl-adaptive" type="radio" name="jbimpl" class="jitter-buffer" value="adaptive" tabindex="<?php echo ++$tabindex;?>"<?php echo $jbimpl=="adaptive"?"checked=\"adaptive\"":""?>/>
            <label for="jbimpl-adaptive"><?php echo _("Adaptive") ?></label>
			</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr class="jitter-buffer">
    <td>
      <a href="#" class="info"><?php echo _("Jitter Buffer Logging")?><span><?php echo _("Asterisk: jblog. Enables jitter buffer frame logging.")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
			<span class="radioset">
            <input id="jblog-yes" type="radio" name="jblog" class="jitter-buffer" value="yes" tabindex="<?php echo ++$tabindex;?>"<?php echo $jblog=="yes"?"checked=\"yes\"":""?>/>
            <label for="jblog-yes"><?php echo _("Enable") ?></label>
            <input id="jblog-no" type="radio" name="jblog" class="jitter-buffer" value="no" tabindex="<?php echo ++$tabindex;?>"<?php echo $jblog=="no"?"checked=\"no\"":""?>/>
            <label for="jblog-no"><?php echo _("Disable") ?></label>
			</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr class="jitter-buffer">
    <td>
      <a href="#" class="info"><?php echo _("Jitter Buffer Size")?><span><?php echo _("Asterisk: jbmaxsize. Max length of the jitterbuffer in milliseconds.<br /> Asterisk: jbresyncthreshold. Jump in the frame timestamps over which the jitterbuffer is resynchronized. Useful to improve the quality of the voice, with big jumps in/broken timestamps, usually sent from exotic devices and programs. Can be set to -1 to disable.")?></span></a>
    </td>
    <td>
      <input type="text" size="4" id="jbmaxsize" name="jbmaxsize" class="jitter-buffer validate-int" value="<?php echo $jbmaxsize ?>" tabindex="<?php echo ++$tabindex;?>"><small>&nbsp;(jbmaxsize)</small>&nbsp;
      <input type="text" size="4" id="jbresyncthreshold" name="jbresyncthreshold" class="jitter-buffer validate-int" value="<?php echo $jbresyncthreshold ?>" tabindex="<?php echo ++$tabindex;?>"><small>&nbsp;(jbresyncthreshold)</small>&nbsp;
    </td>
  </tr>

  <tr>
    <td colspan="2"><h5><?php echo _("Advanced General Settings") ?><hr></h5></td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Language")?><span><?php echo _("Default Language for a channel, Asterisk: language")?></span></a>
    </td>
    <td>
      <input type="text" id="sip_language" name="sip_language" class="validate-alphanumeric" value="<?php echo $sip_language ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Default Context")?><span><?php echo _("Asterisk: context. Default context for incoming calls if not specified. IssabelPBX sets this to from-sip-external which is used in conjunction with the Allow Anonymous SIP calls. If you change this you will effect that behavior. It is recommended to leave this blank.")?></span></a>
    </td>
    <td>
      <input type="text" id="default-context" name="context" class="validate-alphanumeric" value="<?php echo $context ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr>

<?php
$tt = _("Asterisk: bindaddr. The IP address to bind to and listen for calls on the Bind Port. If set to 0.0.0.0 Asterisk will listen on all addresses. It is recommended to leave this blank.");
if (version_compare($amp_conf['ASTVERSION'],'1.8','ge')) {
  $tt .= ' ' . _("Asterisk 1.8 all supports IPv6. An address of '::' will listen on both IPv4 and IPv6.");
}

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
      <a href="#" class="info"><?php echo _("SRV Lookup")?><span><?php echo _("Enable Asterisk srvlookup. See current version of Asterisk for limitations on SRV functionality.")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
			<span class="radioset">
            <input id="srvlookup-yes" type="radio" name="srvlookup" value="yes" tabindex="<?php echo ++$tabindex;?>"<?php echo $srvlookup=="yes"?"checked=\"yes\"":""?>/>
            <label for="srvlookup-yes"><?php echo _("Enabled") ?></label>
            <input id="srvlookup-no" type="radio" name="srvlookup" value="no" tabindex="<?php echo ++$tabindex;?>"<?php echo $srvlookup=="no"?"checked=\"no\"":""?>/>
            <label for="srvlookup-no"><?php echo _("Disabled") ?></label>
			</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  
  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Call Events")?><span><?php echo _("Generate manager events when sip ua performs events (e.g. hold).")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
			<span class="radioset">
            <input id="callevents-yes" type="radio" name="callevents" value="yes" tabindex="<?php echo ++$tabindex;?>"<?php echo $callevents=="yes"?"checked=\"yes\"":""?>/>
            <label for="callevents-yes"><?php echo _("Yes") ?></label>
            <input id="callevents-no" type="radio" name="callevents" value="no" tabindex="<?php echo ++$tabindex;?>"<?php echo $callevents=="no"?"checked=\"no\"":""?>/>
            <label for="callevents-no"><?php echo _("No") ?></label>
			</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr><td colspan="2"><br /></td></tr>

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
  <tr id="sip-custom-buttons">
    <td></td>
    <td><br \>
      <input type="button" id="sip-custom-add"  value="<?php echo $add_field ?>" />
    </td>
  </tr>

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

  /* Initialize Video Support settings and show/hide */
  if (document.getElementById("videosupport-no").checked) {
    $(".video-codecs").hide();
  }
  $("#videosupport-yes").click(function(){
    $(".video-codecs").show();
  });
  $("#videosupport-no").click(function(){
    $(".video-codecs").hide();
  });

  /* Initialize Jitter Buffer settings and show/hide */
  if (document.getElementById("jbenable-no").checked) {
    $(".jitter-buffer").hide();
  }
  $("#jbenable-yes").click(function(){
    $(".jitter-buffer").show();
  });
  $("#jbenable-no").click(function(){
    $(".jitter-buffer").hide();
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
