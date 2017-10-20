<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
/* $Id:$ */
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

// Use hookGet_config so that everyone (like core) will have written their
// IAX settings and then we can remove any that we are going to override
//

/* Field Values for type field */
define('IAX_NORMAL','0');
define('IAX_CODEC','1');
define('IAX_VIDEO_CODEC','2');
define('IAX_CUSTOM','9');

class iaxsettings_validate {
  var $errors = array();

  /* checks if value is an integer */
  function is_int($value, $item, $message, $negative=false) {
    $value = trim($value);
    if ($value != '' && $negative) {
      $tmp_value = substr($value,0,1) == '-' ? substr($value,1) : $value;
      if (!ctype_digit($tmp_value)) {
        $this->errors[] = array('id' => $item, 'value' => $value, 'message' => $message);
      }
    } elseif (!$negative) {
      if (!ctype_digit($value) || ($value < 0 )) {
        $this->errors[] = array('id' => $item, 'value' => $value, 'message' => $message);
      }
    }
    return $value;
  }

  /* checks if value is valid port between 1024 - 6 65535 */
  function is_ip_port($value, $item, $message) {
    $value = trim($value);
    if ($value != '' && (!ctype_digit($value) || $value < 1024 || $value > 65535)) {
      $this->errors[] = array('id' => $item, 'value' => $value, 'message' => $message);
    }
    return $value;
  }

  /* checks if value is valid ip format */
  function is_ip($value, $item, $message) {
    $value = trim($value);
    if ($value != '' && !preg_match('|^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$|',$value,$matches)) {
      $this->errors[] = array('id' => $item, 'value' => $value, 'message' => $message);
    }
    return $value;
  }

  /* checks if value is valid ip netmask format */
  function is_netmask($value, $item, $message) {
    $value = trim($value);
    if ($value != '' && !(preg_match('|^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$|',$value,$matches) || (ctype_digit($value) && $value >= 0 && $value <= 24))) {
      $this->errors[] = array('id' => $item, 'value' => $value, 'message' => $message);
    }
    return $value;
  }

  /* checks if value is valid alpha numeric format */
  function is_alphanumeric($value, $item, $message) {
    $value = trim($value);
	  if ($value != '' && !preg_match("/^\s*([a-zA-Z0-9.&\-@_!<>!\"\']+)\s*$/",$value,$matches)) {
      $this->errors[] = array('id' => $item, 'value' => $value, 'message' => $message);
    }
    return $value;
  }

  /* trigger a validation error to be appended to this class */
  function log_error($value, $item, $message) {
    $this->errors[] = array('id' => $item, 'value' => $value, 'message' => $message);
    return $value;
  }
}

function iaxsettings_hookGet_config($engine) {
  global $core_conf;

  switch($engine) {
    case "asterisk":
      if (isset($core_conf) && is_a($core_conf, "core_conf")) {
        $raw_settings = iaxsettings_get(true);

        /* TODO: This is example concept code

           The only real conflicts are codecs (mainly cause
           it will look ugly. So we should strip those but
           leave the rest. If we overrite it, oh well

				 */
        $idx = 0;
        foreach ($core_conf->_iax_general as $entry) {
          switch (strtolower($entry['key'])) {
            case 'allow':
            case 'disallow':
              unset($core_conf->_iax_general[$idx]);
            break;
          default:
            // do nothing
          }
          $idx++;
        }

        foreach ($raw_settings as $var) {
          switch ($var['type']) {
            case IAX_NORMAL:
              $interim_settings[$var['keyword']] = $var['data'];
            break;

            case IAX_CODEC:
              $codecs[$var['keyword']] = $var['data'];
            break;

            case IAX_VIDEO_CODEC:
              $video_codecs[$var['keyword']] = $var['data'];
            break;

            case IAX_CUSTOM:
              $iax_settings[] = array($var['keyword'], $var['data']);
            break;
          default:
            // Error should be above
          }
        }
        unset($raw_settings);

        /* Codecs First */
        $core_conf->addIaxGeneral('disallow','all');
        asort($codecs);
        foreach ($codecs as $codec => $enabled) {
          if ($enabled != '') {
            $core_conf->addIaxGeneral('allow',$codec);
          }
        }
        unset($codecs);

        if (isset($interim_settings['videosupport']) && $interim_settings['videosupport'] == 'yes') {
          asort($video_codecs);
          foreach ($video_codecs as $codec => $enabled) {
            if ($enabled != '') {
              $core_conf->addIaxGeneral('allow',$codec);
            }
          }
        }
        unset($video_codecs);

        /* next figure out what we need to write out (deal with things like nat combos, etc. */

        $jitterbuffer = isset($interim_settings['jitterbuffer']) && $interim_settings['jitterbuffer'] 
						? $interim_settings['jitterbuffer'] : '';
        if (isset($interim_settings) && is_array($interim_settings)){
			 foreach ($interim_settings as $key => $value) {
	          switch ($key) {
	            case 'videosupport':
	            break;

	            case 'maxjitterbuffer':
	            case 'maxjitterinterps':
	            case 'resyncthreshold':
	            case 'forcejitterbuffer':
	              if ($jitterbuffer == 'yes' && $value != '') {
	                $iax_settings[] = array($key, $value);
	              }
	            break;

	            case 'bandwidth':
	              if ($value != 'unset') {
	                $iax_settings[] = array($key, $value);
	              }
	            break;

	            case 'iax_language':
	              if ($value != '') {
	                $iax_settings[] = array('language', $value);
	              }
	            break;

	            default:
	              $iax_settings[] = array($key, $value);
	            }
	          }
		}
        unset($interim_settings);
          if (isset($iax_settings) && is_array($iax_settings)){
			foreach ($iax_settings as $entry) {
            if ($entry[1] != '') {
              $core_conf->addIaxGeneral($entry[0],$entry[1]);
            }
          }
		} 
      }
    break;
  }

  return true;
}

function iaxsettings_get($raw=false) {

  $sql = "SELECT `keyword`, `data`, `type`, `seq` FROM `iaxsettings` ORDER BY `type`, `seq`";
  $raw_settings = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

  /* Just give the SQL table if more convenient (such as in hookGet_config */
  if ($raw) {
    return $raw_settings;
  }

  /* Initialize first, then replace with DB, to make sure we have defaults */

  $iax_settings['codecs']            =  array(
    'ulaw'     => '1',
    'alaw'     => '2',
    'slin'     => '',
    'g726'     => '',
    'gsm'      => '3',
    'g729'     => '',
    'ilbc'     => '',
    'g723'     => '',
    'g726aal2' => '',
    'adpcm'    => '',
    'lpc10'    => '',
    'speex'    => '',
    'g722'     => '',
    'siren7'   => '',
    'siren14' 	=> '',
    );

  $iax_settings['video_codecs']      = array(
    'h261'  => '',
    'h263'  => '',
    'h263p' => '',
    'h264'  => '',
    );

  $iax_settings['codecpriority']     = 'host';
  $iax_settings['bandwidth']         = 'unset';
  $iax_settings['videosupport']      = 'no';

  $iax_settings['minregexpire']      = '60';
  $iax_settings['maxregexpire']      = '3600';

  $iax_settings['jitterbuffer']      = 'no';
  $iax_settings['forcejitterbuffer'] = 'no';
  $iax_settings['maxjitterbuffer']   = '200';
  $iax_settings['resyncthreshold']   = '1000';
  $iax_settings['maxjitterinterps']  = '10';

  $iax_settings['iax_language']      = '';
  $iax_settings['bindaddr']          = '';
  $iax_settings['bindport']          = '';
  $iax_settings['delayreject']       = 'yes';

  $iax_settings['iax_custom_key_0']  = '';
  $iax_settings['iax_custom_val_0']  = '';

  foreach ($raw_settings as $var) {
    switch ($var['type']) {
      case IAX_NORMAL:
        $iax_settings[$var['keyword']]                 = $var['data'];
      break;

      case IAX_CODEC:
        $iax_settings['codecs'][$var['keyword']]       = $var['data'];
      break;

      case IAX_VIDEO_CODEC:
        $iax_settings['video_codecs'][$var['keyword']] = $var['data'];
      break;

      case IAX_CUSTOM:
        $iax_settings['iax_custom_key_'.$var['seq']]   = $var['keyword'];
        $iax_settings['iax_custom_val_'.$var['seq']]   = $var['data'];
      break;

    default:
      // Error should be above
    }
  }
  unset($raw_settings);

  return $iax_settings;
}

// Add a iaxsettings
function iaxsettings_edit($iax_settings) {
  global $db;
  $save_settings = array();
  $vd = new  iaxsettings_validate();

  $codecs = $iax_settings['codecs'];
  $video_codecs = $iax_settings['video_codecs'];
  unset($iax_settings['codecs']);
  unset($iax_settings['video_codecs']);



  $integer_msg = _("%s must be a non-negative integer");
  foreach ($iax_settings as $key => $val) {
    switch ($key) {
      case 'bindaddr':
        $msg = _("Bind Address (bindaddr) must be an IP address.");
        $save_settings[] = array($key,$db->escapeSimple($vd->is_ip($val,$key,$msg)),'2',IAX_NORMAL);
      break;

      case 'bindport':
        $msg = _("Bind Port (bindport) must be between 1024..65535, default 4569");
        $save_settings[] = array($key,$db->escapeSimple($vd->is_ip_port($val, $key, $msg)),'1',IAX_NORMAL);
      break;

      case 'minregexpire':
      case 'maxregexpire':
        $msg = sprintf($integer_msg,$key);
        $save_settings[] = array($key,$db->escapeSimple($vd->is_int($val,$key,$msg)),'10',IAX_NORMAL);
      break;

      case 'iax_language':
        $msg = ("Language must be alphanumeric and installed");
        $save_settings[] = array($key,$db->escapeSimple($vd->is_alphanumeric($val,$key,$msg)),'0',IAX_NORMAL);
      break;

      case 'codecpriority':
      case 'delayreject':
      case 'bandwidth':
        $save_settings[] = array($key,$val,'0',IAX_NORMAL);
      break;

      case 'jitterbuffer':
        $save_settings[] = array($key,$val,'4',IAX_NORMAL);
      break;

      case 'forcejitterbuffer':
        $save_settings[] = array($key,$val,'5',IAX_NORMAL);
      break;

      case 'maxjitterbuffer':
      case 'maxjitterinterps':
        $msg = sprintf($integer_msg,$key);
        $save_settings[] = array($key,$db->escapeSimple($vd->is_int($val,$key,$msg)),'5',IAX_NORMAL);
      break;

      case 'resyncthreshold':
        $msg = _("resyncthreshold must be a non-negative integer or -1 to disable");
        $save_settings[] = array($key,$db->escapeSimple($vd->is_int($val,$key,$msg,true)),'5',IAX_NORMAL);
      break;

      case 'videosupport':
        $save_settings[] = array($key,$val,'10',IAX_NORMAL);
      break;

    default:
      if (substr($key,0,15) == "iax_custom_key_") {
        $seq = substr($key,15);
        $save_settings[] = array($db->escapeSimple($val),$db->escapeSimple($iax_settings["iax_custom_val_$seq"]),($seq),IAX_CUSTOM); 
      } else if (substr($key,0,15) == "iax_custom_val_") {
        // skip it, we will seek it out when we see the iax_custom_key
      } else {
        $save_settings[] = array($key,$val,'0',IAX_NORMAL);
      }
    }
  }

  /* if there were any validation errors, we will return them and not proceed with saving */
  if (count($vd->errors)) {
    return $vd->errors;
  } else {
    $seq = 0;
    foreach ($codecs as $key => $val) {
      $save_settings[] = array($db->escapeSimple($key),$db->escapeSimple($val),$seq++,IAX_CODEC);
    }
    $seq = 0;
    foreach ($video_codecs as $key => $val) {
      $save_settings[] = array($db->escapeSimple($key),$db->escapeSimple($val),$seq++,IAX_VIDEO_CODEC); 
    }

    // TODO: normally don't like doing delete/insert but otherwise we would have do update for each
    //       individual setting and then an insert if there was nothing to update. So this is cleaner
    //       this time around.
	  //
    sql("DELETE FROM `iaxsettings` WHERE 1");
    $compiled = $db->prepare('INSERT INTO `iaxsettings` (`keyword`, `data`, `seq`, `type`) VALUES (?,?,?,?)');
    $result = $db->executeMultiple($compiled,$save_settings);
    if(DB::IsError($result)) {
      die_issabelpbx($result->getDebugInfo()."<br><br>".'error adding to iaxsettings table');	}
    return true;
  }
}

