<?php
/* $Id:$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//    License for all code of this IssabelPBX module can be found in the license file inside the module directory
//    Copyright 2018 Issabel Foundation.
//    Copyright 2013 Schmooze Com Inc.
//
// Use hookGet_config so that everyone (like core) will have written their
// SIP settings and then we can remove any that we are going to override
//

/* Field Values for type field */
define('SIP_NORMAL','0');
define('SIP_CODEC','1');
define('SIP_VIDEO_CODEC','2');
define('SIP_CUSTOM','9');

class sipsettings_validate {
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
    function is_ip($value, $item, $message, $ipv6_ok=false) {
        $value = trim($value);
        if ($value != '' && !preg_match('|^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$|',$value,$matches)) {
            $regex = '/^\s*((?=.*::.*)(::)?([0-9A-F]{1,4}(:(?=[0-9A-F])|(?!\2)(?!\5)(::)|\z)){0,7}|((?=.*::.*)(::)?([0-9A-F]{1,4}(:(?=[0-9A-F])|(?!\7)(?!\10)(::))){0,5}|([0-9A-F]{1,4}:){6})((25[0-5]|(2[0-4]|1[0-9]|[1-9]?)[0-9])(\.(?=.)|\z)){4}|([0-9A-F]{1,4}:){7}[0-9A-F]{1,4})\s*$/i';
            if ($ipv6_ok && ($value == '::' || preg_match($regex,$value, $matches))) {
                return $value;
            } else {
                $this->errors[] = array('id' => $item, 'value' => $value, 'message' => $message);
            }
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

function sipsettings_hookGet_config($engine) {
    global $core_conf;
    global $ext;  // is this the best way to pass this?
    $sip_settings=array();

    switch($engine) {
    case "asterisk":
        if (isset($core_conf) && is_a($core_conf, "core_conf")) {
            $raw_settings = sipsettings_get(true);

        /* TODO: This is example concept code

           The only real conflicts are codecs (mainly cause
           it will look ugly. So we should strip those but
           leave the rest. If we overrite it, oh well

         */
            $idx = 0;
            foreach ($core_conf->_sip_general as $entry) {
                switch (strtolower($entry['key'])) {
                case 'allow':
                case 'disallow':
                    unset($core_conf->_sip_general[$idx]);
                    break;
                default:
                    // do nothing
                }
                $idx++;
            }

            foreach ($raw_settings as $var) {
                switch ($var['type']) {
                case SIP_NORMAL:
                    $interim_settings[$var['keyword']] = $var['data'];
                    break;

                case SIP_CODEC:
                    $codecs[$var['keyword']] = $var['data'];
                    break;

                case SIP_VIDEO_CODEC:
                    $video_codecs[$var['keyword']] = $var['data'];
                    break;

                case SIP_CUSTOM:
                    $sip_settings[] = array($var['keyword'], $var['data']);
                    break;
                default:
                    // Error should be above
                }
            }
            unset($raw_settings);

            /* Codecs First */
            $core_conf->addSipGeneral('disallow','all');
            asort($codecs);
            foreach ($codecs as $codec => $enabled) {
                if ($enabled != '') {
                    $core_conf->addSipGeneral('allow',$codec);
                }
            }
            unset($codecs);

            if(isset($interim_settings['videosupport'])) {
                if ($interim_settings['videosupport'] == 'yes') {
                    asort($video_codecs);
                    foreach ($video_codecs as $codec => $enabled) {
                        if ($enabled != '') {
                            $core_conf->addSipGeneral('allow',$codec);
                        }
                    }
                }
            }
            unset($video_codecs);

            /* next figure out what we need to write out (deal with things like nat combos, etc. */

            $nat_mode = isset($interim_settings['nat_mode'])?$interim_settings['nat_mode']:'';
            $jbenable = isset($interim_settings['jbenable'])?$interim_settings['jbenable']:'';

            if (is_array($interim_settings)) foreach ($interim_settings as $key => $value) {
                switch ($key) {
                case 'nat_mode':
                    break;

                case 'externhost_val':
                    if ($nat_mode == 'externhost' && $value != '') {
                        $sip_settings[] = array('externhost', $value);
                    }
                    break;

                case 'externrefresh':
                    if ($nat_mode == 'externhost' && $value != '') {
                        $sip_settings[] = array($key, $value);
                    }
                    break;

                case 'externip_val':
                    if ($nat_mode == 'externip' && $value != '') {
                        $sip_settings[] = array('externip', $value);
                    }
                    break;

                case 'rtpstart':
                case 'rtpend':
                    $core_conf->addRtpAdditional('general', array($key => $value));
                    break;

                case 'jbforce':
                case 'jbimpl':
                case 'jbmaxsize':
                case 'jbresyncthreshold':
                case 'jblog':
                    if ($jbenable == 'yes' && $value != '') {
                        $sip_settings[] = array($key, $value);
                    }
                    break;

                case 'sip_language':
                    if ($key != '') {
                        $sip_settings[] = array('language', $value);
                        $ext->addGlobal('SIPLANG',$value);
                    }
                    break;
                case 't38pt_udptl':
                    if ($value != 'no') {
                        $sip_settings[] = array('t38pt_udptl', 'yes,redundancy,maxdatagram=400');
                    }
                    break;
                case 'ALLOW_SIP_ANON':
                    $ext->addGlobal($key,$value);
                    break;

                default:
                    if (substr($key,0,9) == "localnet_" && $value != '') {
                        if ($nat_mode != 'public') {
                            $seq = substr($key,9);
                            $network = "$value/".$interim_settings["netmask_$seq"];
                            $sip_settings[] = array('localnet', $network);
                        }
                    } else if (substr($key,0,8) == "netmask_") {
                        // do nothing, handled above
                    } else {
                        $sip_settings[] = array($key, $value);
                    }
                }
            }
            unset($interim_settings);
            if (is_array($sip_settings)) foreach ($sip_settings as $entry) {
                if ($entry[1] != '') {
                    $core_conf->addSipGeneral($entry[0],$entry[1]);
                }
            }
        }
        break;
    }

    return true;
}

function sipsettings_get($raw=false) {

    $sql = "SELECT `keyword`, `data`, `type`, `seq` FROM `sipsettings` ORDER BY `type`, `seq`";
    $raw_settings = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

    // Pull this out of admin table where it is special cased because of migration from General Settings
    //
    $sql = "SELECT `variable` keyword, `value` data FROM `admin` WHERE `variable` = 'ALLOW_SIP_ANON'";
    $sip_anon = sql($sql,"getRow",DB_FETCHMODE_ASSOC);
    if (!empty($sip_anon)) {
        $sip_anon['type'] = SIP_NORMAL;
        $sip_anon['seq'] = 10;
        $raw_settings[] = $sip_anon;
    }

    /* Just give the SQL table if more convenient (such as in hookGet_config */
    if ($raw) {
        return $raw_settings;
    }

    /* Initialize first, then replace with DB, to make sure we have defaults */

    $sip_settings['nat']               = 'yes';
    $sip_settings['nat_mode']          = 'externip';
    $sip_settings['externip_val']      = '';
    $sip_settings['externhost_val']    = '';
    $sip_settings['externrefresh']     = '120';
    $sip_settings['localnet_0']        = '';
    $sip_settings['netmask_0']         = '255.255.255.0';

    $sip_settings['codecs']            =  array(
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

    $sip_settings['g726nonstandard']   = 'no';
    $sip_settings['t38pt_udptl']       = 'no';

    $sip_settings['video_codecs']      = array(
        'h261'  => '',
        'h263'  => '',
        'h263p' => '',
        'h264'  => '',
        'mpeg4' => '',
        'vp8'   => '',
    );

    $sip_settings['videosupport']      = 'no';
    $sip_settings['maxcallbitrate']    = '384';

    $sip_settings['canreinvite']       = 'no';
    $sip_settings['rtptimeout']        = '30';
    $sip_settings['rtpholdtimeout']    = '300';
    $sip_settings['rtpkeepalive']      = '0';

    $sip_settings['checkmwi']          = '10';
    $sip_settings['notifyringing']     = 'yes';
    $sip_settings['notifyhold']        = 'yes';

    $sip_settings['registertimeout']   = '20';
    $sip_settings['registerattempts']  = '0';
    $sip_settings['maxexpiry']         = '3600';
    $sip_settings['minexpiry']         = '60';
    $sip_settings['defaultexpiry']     = '120';

    $sip_settings['jbenable']          = 'no';
    $sip_settings['jbforce']           = 'no';
    $sip_settings['jbimpl']            = 'fixed';
    $sip_settings['jbmaxsize']         = '200';
    $sip_settings['jbresyncthreshold'] = '1000';
    $sip_settings['jblog']             = 'no';

    $sip_settings['sip_language']      = '';
    $sip_settings['context']           = '';
    $sip_settings['ALLOW_SIP_ANON']    = 'no';
    $sip_settings['bindaddr']          = '';
    $sip_settings['bindport']          = '';
    $sip_settings['allowguest']        = 'no';
    $sip_settings['srvlookup']         = 'no';
    $sip_settings['callevents']        = 'yes';

    $sip_settings['sip_custom_key_0']  = '';
    $sip_settings['sip_custom_val_0']  = '';

    foreach ($raw_settings as $var) {
        switch ($var['type']) {
        case SIP_NORMAL:
            $sip_settings[$var['keyword']]                 = $var['data'];
            break;

        case SIP_CODEC:
            $sip_settings['codecs'][$var['keyword']]       = $var['data'];
            break;

        case SIP_VIDEO_CODEC:
            $sip_settings['video_codecs'][$var['keyword']] = $var['data'];
            break;

        case SIP_CUSTOM:
            $sip_settings['sip_custom_key_'.$var['seq']]   = $var['keyword'];
            $sip_settings['sip_custom_val_'.$var['seq']]   = $var['data'];
            break;

        default:
            // Error should be above
        }
    }
    unset($raw_settings);

    return $sip_settings;
}

// Add a sipsettings
function sipsettings_edit($sip_settings) {
    global $db;
    global $amp_conf;
    $save_settings = array();
    $save_to_admin = array(); // Used only by ALLOW_SIP_ANON for now
    $vd = new  sipsettings_validate();

    $codecs = $sip_settings['codecs'];
    $video_codecs = $sip_settings['video_codecs'];
    unset($sip_settings['codecs']);
    unset($sip_settings['video_codecs']);

    // TODO: this is where I will build validation before saving
    //
    $integer_msg = _("%s must be a non-negative integer");
    foreach ($sip_settings as $key => $val) {
        switch ($key) {
        case 'bindaddr':
            $msg = _("Bind Address (bindaddr) must be an IP address.");
            $ipv6_ok = version_compare($amp_conf['ASTVERSION'],'1.8','ge');
            $save_settings[] = array($key,$db->escapeSimple($vd->is_ip($val,$key,$msg,$ipv6_ok)),'2',SIP_NORMAL);
            break;

        case 'bindport':
            $msg = _("Bind Port (bindport) must be between 1024..65535, default 5060");
            $save_settings[] = array($key,$db->escapeSimple($vd->is_ip_port($val, $key, $msg)),'1',SIP_NORMAL);
            break;

        case 'rtpholdtimeout':
            // validation: must be > $sip_settings['rtptimeout'] (and of course a proper number)
            //$vd->log_error();
            if ($val < $sip_settings['rtptimeout']) {
                $msg = _("rtpholdtimeout must be higher than rtptimeout");
                $vd->log_error($val, $key, $msg);
            }
            $msg = sprintf($integer_msg,$key);
            $save_settings[] = array($key,$db->escapeSimple($vd->is_int($val, $key, $msg)),'10',SIP_NORMAL);
            break;

        case 'rtptimeout':
        case 'rtpkeepalive':
        case 'checkmwi':
        case 'registertimeout':
        case 'minexpiry':
        case 'maxexpiry':
        case 'defaultexpiry':
            $msg = sprintf($integer_msg,$key);
            $save_settings[] = array($key,$db->escapeSimple($vd->is_int($val,$key,$msg)),'10',SIP_NORMAL);
            break;

        case 'maxcallbitrate':
        case 'registerattempts':
            $msg = sprintf($integer_msg,$key);
            $save_settings[] = array($key,$db->escapeSimple($vd->is_int($val,$key,$msg)),'10',SIP_NORMAL);
            break;


        case 'sip_language':
            $msg = ("Language must be alphanumeric and installed");
            $save_settings[] = array($key,$db->escapeSimple($vd->is_alphanumeric($val,$key,$msg)),'0',SIP_NORMAL);
            break;

        case 'context':
            $msg = sprintf(_("%s must be alphanumeric"),$key);
            $save_settings[] = array($key,$db->escapeSimple($vd->is_alphanumeric($val,$key,$msg)),'0',SIP_NORMAL);
            break;

        case 'externrefresh':
            $msg = sprintf($integer_msg,$key);
            $save_settings[] = array($key,$db->escapeSimple($vd->is_int($val,$key,$msg)),'41',SIP_NORMAL);
            break;

        case 'nat':
            $save_settings[] = array($key,$val,'39',SIP_NORMAL);
            break;

        case 'externip_val':
            if (trim($val) == '' && $sip_settings['nat_mode'] == 'externip') {
                $msg = _("External IP can not be blank");
                $vd->log_error($val, $key, $msg);
            }
            $save_settings[] = array($key,$val,'40',SIP_NORMAL);
            break;

        case 'externhost_val':
            if (trim($val) == '' && $sip_settings['nat_mode'] == 'externhost') {
                $msg = _("Dynamic Host can not be blank");
                $vd->log_error($val, $key, $msg);
            }
            $save_settings[] = array($key,$val,'40',SIP_NORMAL);
            break;

        case 'jbenable':
            $save_settings[] = array($key,$val,'4',SIP_NORMAL);
            break;

        case 'jbforce':
        case 'jbimpl':
        case 'jblog':
            $save_settings[] = array($key,$val,'5',SIP_NORMAL);
            break;

        case 'jbmaxsize':
        case 'jbresyncthreshold':
            $msg = sprintf($integer_msg,$key);
            $save_settings[] = array($key,$db->escapeSimple($vd->is_int($val,$key,$msg)),'5',SIP_NORMAL);
            break;

        case 'nat_mode':
        case 'g726nonstandard':
        case 't38pt_udptl':
        case 'videosupport':
        case 'canreinvite':
        case 'notifyringing':
        case 'notifyhold':
        case 'allowguest':
        case 'srvlookup':
            $save_settings[] = array($key,$val,'10',SIP_NORMAL);
            break;

        case 'ALLOW_SIP_ANON':
            $save_to_admin[] = array($key,$val);
            break;
        default:
            if (substr($key,0,9) == "localnet_") {
                // ip validate this and store
                $seq = substr($key,9);
                $msg = _("Localnet setting must be an IP address");
                $save_settings[] = array($key,$db->escapeSimple($vd->is_ip($val,$key,$msg)),(42+$seq),SIP_NORMAL);
            } else if (substr($key,0,8) == "netmask_") {
                // ip validate this and store
                $seq = substr($key,8);
                $msg = _("Localnet netmask must be formatted properly (e.g. 255.255.255.0 or 24)");
                $save_settings[] = array($key,$db->escapeSimple($vd->is_netmask($val,$key,$msg)),$seq,SIP_NORMAL);
            } else if (substr($key,0,15) == "sip_custom_key_") {
                $seq = substr($key,15);
                $save_settings[] = array($db->escapeSimple($val),$db->escapeSimple($sip_settings["sip_custom_val_$seq"]),($seq),SIP_CUSTOM);
            } else if (substr($key,0,15) == "sip_custom_val_") {
                // skip it, we will seek it out when we see the sip_custom_key
            } else {
                $save_settings[] = array($key,$val,'0',SIP_NORMAL);
            }
        }
    }

    /* if there were any validation errors, we will return them and not proceed with saving */
    if (count($vd->errors)) {
        return $vd->errors;
    } else {
        $seq = 0;
        foreach ($codecs as $key => $val) {
            $save_settings[] = array($db->escapeSimple($key),$db->escapeSimple($val),$seq++,SIP_CODEC);
        }
        $seq = 0;
        foreach ($video_codecs as $key => $val) {
            $save_settings[] = array($db->escapeSimple($key),$db->escapeSimple($val),$seq++,SIP_VIDEO_CODEC);
        }

        // TODO: normally don't like doing delete/insert but otherwise we would have do update for each
        //       individual setting and then an insert if there was nothing to update. So this is cleaner
        //       this time around.
        //
        sql("DELETE FROM `sipsettings` WHERE 1");
        $compiled = $db->prepare('INSERT INTO `sipsettings` (`keyword`, `data`, `seq`, `type`) VALUES (?,?,?,?)');
        $result = $db->executeMultiple($compiled,$save_settings);
        if(DB::IsError($result)) {
            die_issabelpbx($result->getDebugInfo()."<br><br>".'error adding to sipsettings table');
        }
        if (!empty($save_to_admin)) {
            $compiled = $db->prepare("REPLACE INTO `admin` (`variable`, `value`) VALUES (?,?)");
            $result = $db->executeMultiple($compiled,$save_to_admin);
            if(DB::IsError($result)) {
                die_issabelpbx($result->getDebugInfo()."<br><br>".'error adding to sipsettings table');
            }
        }
        return true;
    }
}

