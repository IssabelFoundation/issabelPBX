<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
/* $Id:$ */
//    License for all code of this IssabelPBX module can be found in the license file inside the module directory
//    Copyright 2018 Issabel Foundation
//

// Use hookGet_config so that everyone (like core) will have written their
// Manager settings and then we can remove any that we are going to override
//

/* Field Values for type field */
define('MANAGER_NORMAL','1');
define('MANAGER_CUSTOM','9');

class managersettings_validate {
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

function managersettings_hookGet_config($engine) {
  global $core_conf;

  switch($engine) {
    case "asterisk":
      if (isset($core_conf) && is_a($core_conf, "core_conf")) {
        $raw_settings = managersettings_get(true);

        /* TODO: This is example concept code

           The only real conflicts are codecs (mainly cause
           it will look ugly. So we should strip those but
           leave the rest. If we overrite it, oh well

                 */
        $idx = 0;
        foreach ($core_conf->_manager_general as $entry) {
          switch (strtolower($entry['key'])) {
            case 'allow':
            case 'disallow':
              unset($core_conf->_manager_general[$idx]);
            break;
          default:
            // do nothing
          }
          $idx++;
        }

        foreach ($raw_settings as $var) {
          switch ($var['type']) {
            case MANAGER_NORMAL:
              $interim_settings[$var['keyword']] = $var['data'];
            break;

            case MANAGER_CUSTOM:
              $manager_settings[] = array($var['keyword'], $var['data']);
            break;
          default:
            // Error should be above
          }
        }
        unset($raw_settings);

        if (isset($interim_settings) && is_array($interim_settings)){
            foreach ($interim_settings as $key => $value) {
              $manager_settings[] = array($key, $value);
            }
        }
        unset($interim_settings);
          if (isset($manager_settings) && is_array($manager_settings)){
            foreach ($manager_settings as $entry) {
            if ($entry[1] != '') {
              $core_conf->addManagerGeneral($entry[0],$entry[1]);
            }
          }
        } 
      }
    break;
  }

  return true;
}

function managersettings_get($raw=false) {

  $sql = "SELECT `keyword`, `data`, `type`, `seq` FROM `managersettings` ORDER BY `type`, `seq`";
  $raw_settings = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

  /* Just give the SQL table if more convenient (such as in hookGet_config */
  if ($raw) {
    return $raw_settings;
  }

  /* Initialize first, then replace with DB, to make sure we have defaults */

  $manager_settings['webenabled']       = 'no';
  $manager_settings['displayconnects']  = 'no';
  $manager_settings['timestampevents']  = 'no';
  //$manager_settings['bindaddr']         = '0.0.0.0';
  //$manager_settings['port']             = '5038';
  $manager_settings['channelvars']      = '';

  foreach ($raw_settings as $var) {
    switch ($var['type']) {
      case MANAGER_NORMAL:
        $manager_settings[$var['keyword']]                 = $var['data'];
      break;

      case MANAGER_CUSTOM:
        $manager_settings['manager_custom_key_'.$var['seq']]   = $var['keyword'];
        $manager_settings['manager_custom_val_'.$var['seq']]   = $var['data'];
      break;

    default:
      // Error should be above
    }
  }
  unset($raw_settings);

  return $manager_settings;
}

// Add a managersettings
function managersettings_edit($manager_settings) {
  global $db;
  $save_settings = array();
  $vd = new  managersettings_validate();

  $integer_msg = _("%s must be a non-negative integer");
  foreach ($manager_settings as $key => $val) {
    switch ($key) {
      case 'bindaddr':
        $msg = _("Bind Address (bindaddr) must be an IP address.");
        $save_settings[] = array($key,$db->escapeSimple($vd->is_ip($val,$key,$msg)),'2',MANAGER_NORMAL);
      break;

      case 'port':
        $msg = _("Port (port) must be between 1024..65535, default 5038");
        $save_settings[] = array($key,$db->escapeSimple($vd->is_ip_port($val, $key, $msg)),'1',MANAGER_NORMAL);
      break;

    default:
      if (substr($key,0,19) == "manager_custom_key_") {
        $seq = substr($key,19);
        $save_settings[] = array($db->escapeSimple($val),$db->escapeSimple($manager_settings["manager_custom_val_$seq"]),($seq),MANAGER_CUSTOM); 
      } else if (substr($key,0,19) == "manager_custom_val_") {
        // skip it, we will seek it out when we see the manager_custom_key
      } else {
        $save_settings[] = array($key,$val,'0',MANAGER_NORMAL);
      }
    }
  }

  /* if there were any validation errors, we will return them and not proceed with saving */
  if (count($vd->errors)) {
    return $vd->errors;
  } else {
    $seq = 0;
    foreach ($codecs as $key => $val) {
      $save_settings[] = array($db->escapeSimple($key),$db->escapeSimple($val),$seq++,Manager_CODEC);
    }
    $seq = 0;
    foreach ($video_codecs as $key => $val) {
      $save_settings[] = array($db->escapeSimple($key),$db->escapeSimple($val),$seq++,Manager_VIDEO_CODEC); 
    }

    // TODO: normally don't like doing delete/insert but otherwise we would have do update for each
    //       individual setting and then an insert if there was nothing to update. So this is cleaner
    //       this time around.
      //
    sql("DELETE FROM `managersettings` WHERE 1");
    $compiled = $db->prepare('INSERT INTO `managersettings` (`keyword`, `data`, `seq`, `type`) VALUES (?,?,?,?)');
    $result = $db->executeMultiple($compiled,$save_settings);
    if(DB::IsError($result)) {
      die_issabelpbx($result->getDebugInfo()."<br><br>".'error adding to managersettings table');    }
    return true;
  }
}

