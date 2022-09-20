<?php /* $Id:$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

  /* Determines how many columns per row for the codecs and formats the table */
  $cols_per_row   = 4;
  $width          = (100.0 / $cols_per_row);
  $tabindex       = 0;
  $dispnum        = "iaxsettings";
  $error_displays = array();
  $action         = isset($_POST['action'])?$_POST['action']:'';
  $post_codec     = isset($_POST['codec']) ? $_POST['codec'] : array(); 
  $post_vcodec    = isset($_POST['vcodec']) ? $_POST['vcodec'] : array(); 
	
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
    );

  // With the new sorting, the vars should come to us in the sorted order so just use that
  //
  $pri = 1;
  foreach (array_keys($post_codec) as $codec) {
    $codecs[$codec] = $pri++;
  }
  $iax_settings['codecs']            = $codecs;

  $video_codecs = array(
    'h261'  => '',
    'h263'  => '',
    'h263p' => '',
    'h264'  => '',
    );

  // With the new sorting, the vars should come to us in the sorted order so just use that
  //
  $pri = 1;
  foreach (array_keys($post_vcodec) as $vcodec) {
    $video_codecs[$vcodec] = $pri++;
  }
  $iax_settings['codecpriority']     = isset($_POST['codecpriority']) ? $_POST['codecpriority'] : 'host';
  $iax_settings['bandwidth']         = isset($_POST['bandwidth']) ? $_POST['bandwidth'] : 'unset';
  $iax_settings['video_codecs']      = $video_codecs;
  $iax_settings['videosupport']      = isset($_POST['videosupport']) ? $_POST['videosupport'] : 'no';

  $iax_settings['maxregexpire']      = isset($_POST['maxregexpire']) ? htmlspecialchars($_POST['maxregexpire']) : '3600';
  $iax_settings['minregexpire']      = isset($_POST['minregexpire']) ? htmlspecialchars($_POST['minregexpire']) : '60';

  $iax_settings['jitterbuffer']      = isset($_POST['jitterbuffer']) ? $_POST['jitterbuffer'] : 'no';
  $iax_settings['forcejitterbuffer'] = isset($_POST['forcejitterbuffer']) ? $_POST['forcejitterbuffer'] : 'no';
  $iax_settings['maxjitterbuffer']   = isset($_POST['maxjitterbuffer']) ? htmlspecialchars($_POST['maxjitterbuffer']) : '200';
  $iax_settings['resyncthreshold']   = isset($_POST['resyncthreshold']) ? htmlspecialchars($_POST['resyncthreshold']) : '1000';
  $iax_settings['maxjitterinterps']  = isset($_POST['maxjitterinterps']) ? htmlspecialchars($_POST['maxjitterinterps']) : '10';

  $iax_settings['iax_language']      = isset($_POST['iax_language']) ? htmlspecialchars($_POST['iax_language']) : '';
  $iax_settings['bindaddr']          = isset($_POST['bindaddr']) ? htmlspecialchars($_POST['bindaddr']) : '';
  $iax_settings['bindport']          = isset($_POST['bindport']) ? htmlspecialchars($_POST['bindport']) : '';
  $iax_settings['delayreject']       = isset($_POST['delayreject']) ? htmlspecialchars($_POST['delayreject']) : 'yes';

  $p_idx = 0;
  $n_idx = 0;
  while (isset($_POST["iax_custom_key_$p_idx"])) {
    if ($_POST["iax_custom_key_$p_idx"] != '') {
      $iax_settings["iax_custom_key_$n_idx"] = htmlspecialchars($_POST["iax_custom_key_$p_idx"]);
      $iax_settings["iax_custom_val_$n_idx"] = htmlspecialchars($_POST["iax_custom_val_$p_idx"]);
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

switch ($action) {
  case "edit":  //just delete and re-add
    if (($errors = iaxsettings_edit($iax_settings)) !== true) {
      $error_displays = process_errors($errors);
    } else {
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        redirect_standard();
    }
  break;
  default:
    /* only get them if first time load, if they pressed submit, use values from POST */
    $iax_settings = iaxsettings_get();
}
$error_displays = array_merge($error_displays,iaxsettings_check_custom_files());

?>
<div class='content'>

  <h2><?php echo _("IAX Settings"); ?></h2>

<?php

  /* We massaged these above or they came from iaxsettings_get() if this is not
   * from and edit. So extract them after sorting out the codec sub arrays.
	 */
  $codecs = $iax_settings['codecs'];
  unset($iax_settings['codecs']);
  uasort($codecs, 'cmp');

  $video_codecs = $iax_settings['video_codecs'];
  unset($iax_settings['video_codecs']);
  uasort($video_codecs, 'cmp');

  /* EXTRACT THE VARIABLE HERE - MAKE SURE THEY ARE ALL MASSAGED ABOVE */
	//
  extract($iax_settings);

?>
  <form autocomplete="off" id="mainform" name="editIax" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
  <input type="hidden" name="action" value="edit">
  <table class='table is-borderless is-narrow'>

<?php
  /* if there were erros on the submit then create error box */
  if (!empty($error_displays)) {
?>
  <tr>
    <td colspan="2">
      <div class="iax-errors">
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
    <td colspan="2"><h5><?php echo _("Audio Codecs")?></h5></td>
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
	echo '<li class="draggable"><a href="javascript:void(0)">'
        . '<i class="fa fa-arrows-v mx-2"></i>'
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
      <a href="#" class="info"><?php echo _("Codec Priority")?><span><?php echo _("Asterisk: codecpriority. Controls the codec negotiation of an inbound IAX call. This option is inherited to all user entities.  It can also be defined in each user entity separately which will override the setting here. The valid values are:<br />host - Consider the host's preferred order ahead of the caller's.<br />caller - Consider the callers preferred order ahead of the host's.<br /> disabled - Disable the consideration of codec preference altogether. (this is the original behavior before preferences were added)<br />reqonly  - Same as disabled, only do not consider capabilities if the requested format is not available the call will only be accepted if the requested format is available.")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
<?php echo ipbx_radio('codecpriority',array(array('value'=>'host','text'=>'host'),array('value'=>'caller','text'=>'caller'),array('value'=>'disabled','text'=>'disabled'),array('value'=>'regonly','text'=>'regonly')),$codecpriority,false); ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Bandwidth")?><span><?php echo _("Asterisk: bandwidth. Specify bandwidth of low, medium, or high to control which codecs are used in general.")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
<?php echo ipbx_radio('bandwidth',array(array('value'=>'low','text'=>_('low')),array('value'=>'medium','text'=>_('medium')),array('value'=>'high','text'=>_('high')),array('value'=>'unset','text'=>_('unset'))),$bandwidth,false); ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td colspan="2"><h5><?php echo _("Video Codecs")?></h5></td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Video Support")?><span><?php echo _("Check to enable and then choose allowed codecs.")._(" If you clear each codec and then add them one at a time, submitting with each addition, they will be added in order which will effect the codec priority.")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
<?php echo ipbx_radio('videosupport',array(array('value'=>'yes','text'=>_('Enabled')),array('value'=>'no','text'=>_('Disabled'))),$videosupport,false); ?>
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
	echo '<li class="draggable"><a href="javascript:void(0)">'
        . '<i class="fa fa-arrows-v mx-2"></i>'
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

  <tr>
    <td colspan="2"><h5><?php echo _("Registration Settings") ?></h5></td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Registration Times")?><span><?php echo _("Asterisk: minregexpire, maxregexpire. Minimum and maximum length of time that IAX peers can request as a registration expiration interval (in seconds).")?></span></a>
    </td>
    <td>
      <input type="text" class='input' style='width:4em;' id="minregexpire" name="minregexpire" class="validate-int" value="<?php echo $minregexpire ?>" tabindex="<?php echo ++$tabindex;?>"><small>&nbsp;(minregexpire)</small>&nbsp;
      <input type="text" class='input' style='width:4em;' id="maxregexpire" name="maxregexpire" class="validate-int" value="<?php echo $maxregexpire ?>" tabindex="<?php echo ++$tabindex;?>"><small>&nbsp;(maxregexpire)</small>&nbsp;
    </td>
  </tr>

  <tr>
    <td colspan="2"><h5><?php echo _("Jitter Buffer Settings") ?></h5></td>
  </tr>

  <tr>
    <td>
       <a href="#" class="info"><?php echo _("Jitter Buffer")?><span><?php echo _("Asterisk: jitterbuffer. You can adjust several parameters relating to the jitter buffer. The jitter buffer's function is to compensate for varying network delay. The jitter buffer works for INCOMING audio - the outbound audio will be dejittered by the jitter buffer at the other end.")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
<?php echo ipbx_radio('jitterbuffer',array(array('value'=>'yes','text'=>_('Enabled')),array('value'=>'no','text'=>_('Disabled'))),$jitterbuffer,false); ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr class="jitter-buffer">
    <td>
      <a href="#" class="info"><?php echo _("Force Jitter Buffer")?><span><?php echo _("Asterisk: forcejitterbuffer. Forces the use of a jitterbuffer on the receive side of an IAX channel. Normally the jitter buffer will not be used if receiving a jittery channel but sending it off to another channel such as a SIP channel to an endpoint, since there is typically a jitter buffer at the far end. This will force the use of the jitter buffer before sending the stream on. This is not typically desired as it adds additional latency into the stream.")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
<?php echo ipbx_radio('forcejitterbuffer',array(array('value'=>'yes','text'=>_('Yes')),array('value'=>'no','text'=>_('No'))),$forcejitterbuffer,false); ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr class="jitter-buffer">
    <td>
      <a href="#" class="info"><?php echo _("Jitter Buffer Size")?><span><?php echo _("Asterisk: maxjitterbuffer. Max length of the jitterbuffer in milliseconds.<br /> Asterisk: resyncthreshold. When the jitterbuffer notices a significant change in delay that continues over a few frames, it will resync, assuming that the change in delay was caused by a timestamping mix-up. The threshold for noticing a change in delay is measured as twice the measured jitter plus this resync threshold. Resyncing can be disabled by setting this parameter to -1.")?></span></a>
    </td>
    <td>
      <input type="text" class="input" style="width:4em;" id="maxjitterbuffer" name="maxjitterbuffer" class="jitter-buffer validate-int" value="<?php echo $maxjitterbuffer ?>" tabindex="<?php echo ++$tabindex;?>"><small>&nbsp;(maxjitterbuffer)</small>&nbsp;
      <input type="text" class="input" style="width:4em;" id="resyncthreshold" name="resyncthreshold" class="jitter-buffer validate-int" value="<?php echo $resyncthreshold ?>" tabindex="<?php echo ++$tabindex;?>"><small>&nbsp;(resyncthreshold)</small>&nbsp;
    </td>
  </tr>

  <tr class="jitter-buffer">
    <td>
      <a href="#" class="info"><?php echo _("Max Interpolations")?><span><?php echo _("Asterisk: maxjitterinterps. The maximum number of interpolation frames the jitterbuffer should return in a row. Since some clients do not send CNG/DTX frames to indicate silence, the jitterbuffer will assume silence has begun after returning this many interpolations. This prevents interpolating throughout a long silence.")?></span></a>
    </td>
    <td>
      <input type="text" class="input" id="maxjitterinterps" name="maxjitterinterps" class="jitter-buffer validate-int" value="<?php echo $maxjitterinterps ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr>

  <tr>
    <td colspan="2"><h5><?php echo _("Advanced General Settings") ?></h5></td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Language")?><span><?php echo _("Default Language for a channel, Asterisk: language")?></span></a>
    </td>
    <td>
      <input type="text" class="input" id="iax_language" name="iax_language" class="validate-alphanumeric" value="<?php echo $iax_language ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Bind Address")?><span><?php echo _("Asterisk: bindaddr. The IP address to bind to and listen for calls on the Bind Port. If set to 0.0.0.0 Asterisk will listen on all addresses. To bind to multiple IP addresses or ports, use the Other 'IAX Settings' fields where you can put settings such as:<br /> bindaddr=192.168.10.100:4555.<br />  It is recommended to leave this blank.")?></span></a>
    </td>
    <td>
      <input type="text" class="input" id="bindaddr" name="bindaddr" class="validate-ip" value="<?php echo $bindaddr ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Bind Port")?><span><?php echo _("Asterisk: bindport. Local incoming UDP Port that Asterisk will bind to and listen for IAX messages. The IAX standard is 4569 and in most cases this is what you want. It is recommended to leave this blank.")?></span></a>
    </td>
    <td>
      <input type="text" class="input" id="bindport" name="bindport" class="validate-ip-port" value="<?php echo $bindport ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Delay Auth Rejects")?><span><?php echo _("Asterisk: delayreject. For increased security against brute force password attacks enable this which will delay the sending of authentication reject for REGREQ or AUTHREP if there is a password.")?></span></a>
    </td>
    <td>
      <table width="100%">
        <tr>
          <td>
<?php echo ipbx_radio('delayreject',array(array('value'=>'yes','text'=>_('Enable')),array('value'=>'no','text'=>_('Disable'))),$delayreject,false); ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <tr><td colspan="2"><br /></td></tr>

  <tr>
    <td>
      <a href="#" class="info"><?php echo _("Other IAX Settings")?><span><?php echo _("You may set any other IAX settings not present here that are allowed to be configured in the General section of iax.conf. There will be no error checking against these settings so check them carefully. They should be entered as:<br /> [setting] = [value]<br /> in the boxes below. Click the Add Field box to add additional fields. Blank boxes will be deleted when submitted.")?></span></a>
    </td>
    <td>
      <input type="text" class="input iax-custom" style="width:10em;" id="iax_custom_key_0" name="iax_custom_key_0" value="<?php echo $iax_custom_key_0 ?>" tabindex="<?php echo ++$tabindex;?>"> =
      <input type="text" class="input" style="width:10em;" id="iax_custom_val_0" name="iax_custom_val_0" value="<?php echo $iax_custom_val_0 ?>" tabindex="<?php echo ++$tabindex;?>">
    </td>
  </tr>

<?php
  $idx = 1;
  $var_iax_custom_key = "iax_custom_key_$idx";
  $var_iax_custom_val = "iax_custom_val_$idx";
  while (isset($$var_iax_custom_key)) {
    if ($$var_iax_custom_key != '') {
      $tabindex++;
      echo <<< END
  <tr>
    <td>
    </td>
    <td>
      <input type="text" class="input iax-custom" id="iax_custom_key_$idx" name="iax_custom_key_$idx" value="{$$var_iax_custom_key}" tabindex="$tabindex"> =
END;
      $tabindex++;
      echo <<< END
      <input type="text" class="input" id="iax_custom_val_$idx" name="iax_custom_val_$idx" value="{$$var_iax_custom_val}" tabindex="$tabindex">
    </td>
  </tr>
END;
    }
    $idx++;
    $var_iax_custom_key = "iax_custom_key_$idx";
    $var_iax_custom_val = "iax_custom_val_$idx";
  }
  $tabindex += 60; // make room for dynamic insertion of new fields
?>
  <tr id="iax-custom-buttons">
    <td></td>
    <td><br \>
      <input type="button" id="iax-custom-add" class="button is-small is-rounded" value="<?php echo _("Add Field")?>" />
    </td>
  </tr>

</table>
</form>
<script>
$(function(){

  /* Add a Custom Var / Val textbox */
  $("#iax-custom-add").on('click',function(){
    addCustomField("","");
  });

  /* Initialize Video Support settings and show/hide */
  if (document.getElementById("videosupport1").checked) {
    $(".video-codecs").hide();
  }
  $("#videosupport0").on('click',function(){
    $(".video-codecs").show();
  });
  $("#videosupport1").on('click',function(){
    $(".video-codecs").hide();
  });

  /* Initialize Jitter Buffer settings and show/hide */
  if (document.getElementById("jitterbuffer1").checked) {
    $(".jitter-buffer").hide();
  }
  $("#jitterbuffer0").on('click',function(){
    $(".jitter-buffer").show();
  });
  $("#jitterbuffer1").on('click',function(){
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

var theForm = document.editIax;

/* Insert a iax_setting/iax_value pair of text boxes */
function addCustomField(key, val) {
  var idx = $(".iax-custom").length;
  var idxp = idx - 1;
  var tabindex = parseInt($("#iax_custom_val_"+idxp).attr('tabindex')) + 1;
  var tabindexp = tabindex + 1;

  $("#iax-custom-buttons").before('\
  <tr>\
    <td>\
    </td>\
    <td>\
      <input class="input iax-custom" style="width:10em;" type="text" id="iax_custom_key_'+idx+'" name="iax_custom_key_'+idx+'" value="'+key+'" tabindex="'+tabindex+'"> =\
      <input class="input" style="width:10em;" type="text" id="iax_custom_val_'+idx+'" name="iax_custom_val_'+idx+'" value="'+val+'" tabindex="'+tabindexp+'">\
    </td>\
  </tr>\
  ');
}
<?php echo js_display_confirmation_toasts(); ?>
</script>

</div>
<?php echo form_action_bar(''); ?>
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

function iaxsettings_check_custom_files() {
  global $amp_conf;
  $errors = array();

  $custom_files[] = $amp_conf['ASTETCDIR']."/iax_general_custom.conf";
  $custom_files[] = $amp_conf['ASTETCDIR']."/iax_custom.conf";

  foreach ($custom_files as $file) {
    if (file_exists($file)) {
      $iax_conf = @parse_ini_file($file,true);
      $main = true; // 1 is iax.conf, after that don't care
      foreach ($iax_conf as $section => $item) {
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
