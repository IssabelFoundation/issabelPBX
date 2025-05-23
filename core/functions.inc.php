<?php
// vim: :set filetype=php tabstop=4 shiftwidth=4 autoindent smartindent:
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//    License for all code of this IssabelPBX module can be found in the license file inside the module directory
//    Copyright 2006-2014 Schmooze Com Inc.
//    Copyright 2018 Issabel Foundation
//

class core_conf {
    var $_sip_general    = array();
    var $_sip_additional = array();
    var $_sip_notify     = array();
    var $_iax_general    = array();
    var $_iax_additional = array();
    var $_dahdi_additional = array();
    var $_featuregeneral = array();
    var $_featuregeneralsection = array();
    var $_parkinggeneral = array();
    var $_parkinggeneralsection = array();
    var $_featuremap     = array();
    var $_applicationmap = array();
    var $_res_odbc       = array();
    var $_rtp_additional    = array();
    var $_manager_general    = array();
    var $dev_user_map;

    // map the actual vmcontext and user devicename if the device is fixed
    private function map_dev_user($account, $keyword, $data) {
        global $amp_conf;

        if (!isset($this->dev_user_map)) {
            $this->dev_user_map = core_devices_get_user_mappings();
        }

        if (!empty($this->dev_user_map[$account]) && $this->dev_user_map[$account]['devicetype'] == 'fixed') {
            switch (strtolower($keyword)) {
                case 'callerid':
                    $user_option = $this->dev_user_map[$account]['description'] . ' <' . $account . '>';
                    break;
                case 'mailbox':
                    if ((empty($this->dev_user_map[$account]['vmcontext']) || $this->dev_user_map[$account]['vmcontext'] == 'novm')
                        && strtolower($data) == "$account" . "@device" && $amp_conf['DEVICE_REMOVE_MAILBOX']) {
                        // they have no vm so don't put a mailbox=line
                        return "";
                    } elseif (strtolower($data) == "$account" . "@device"
                        && !empty($this->dev_user_map[$account]['vmcontext']) &&
                        $this->dev_user_map[$account]['vmcontext'] != 'novm') {
                        $user_option = $this->dev_user_map[$account]['user'] . "@" . $this->dev_user_map[$account]['vmcontext'];
                    } else {
                        $user_option = $data;
                    }
                }
                $output = $keyword . "=" . $user_option . "\n";
            } else {
                $output = $keyword . "=" . $data . "\n";
            }
        return $output;
    }

    // return an array of filenames to write
    function get_filename() {
        global $chan_dahdi;

        $files = array(
            'pjsip_additional.conf',
            'pjsip_transport_additional.conf',
            'sip_additional.conf',
            'sip_registrations.conf',
            'iax_additional.conf',
            'iax_registrations.conf',
            'sip_general_additional.conf',
            'iax_general_additional.conf',
            'features_general_additional.conf',
            'manager_general_additional.conf',
            'res_parking_additional.conf',
            'features_applicationmap_additional.conf',
            'features_featuremap_additional.conf',
            'localprefixes.conf',
            'sip_notify_additional.conf',
            'res_odbc_additional.conf',
            'chan_dahdi_additional.conf',
            'rtp_additional.conf',
            'http_additional.conf'
        );
        return $files;
    }

    // return the output that goes in each of the files
    function generateConf($file) {
        global $version;
        global $amp_conf;

        switch ($file) {
            case 'sip_general_additional.conf':
                return $this->generate_sip_general_additional($version);
                break;
            case 'sip_additional.conf':
                return $this->generate_sip_additional($version);
                break;
            case 'pjsip_additional.conf':
                return $this->generate_pjsip_additional($version);
                break;
            case 'pjsip_transport_additional.conf':
                return $this->generate_pjsip_transport_additional($version);
                break;
            case 'sip_registrations.conf':
                return $this->generate_sip_registrations($version);
                break;
            case 'sip_notify_additional.conf':
                return $this->generate_sip_notify_additional($version);
                break;
            case 'iax_general_additional.conf':
                return $this->generate_iax_general_additional($version);
                break;
            case 'iax_additional.conf':
                return $this->generate_iax_additional($version);
                break;
            case 'iax_registrations.conf':
                return $this->generate_iax_registrations($version);
                break;
            case 'chan_dahdi_additional.conf':
                return $this->generate_zapata_additional($version, 'dahdi').$this->generate_zapata_additional($version);
                break;
            case 'zapata_additional.conf':
                return $this->generate_zapata_additional($version);
                break;
            case 'res_parking_additional.conf':
                return $this->generate_parkinggeneral_additional($version);
                break;
            case 'manager_general_additional.conf':
                return $this->generate_managergeneral_additional($version);
                break;
            case 'features_general_additional.conf':
                return $this->generate_featuregeneral_additional($version);
                break;
            case 'features_applicationmap_additional.conf':
                return $this->generate_applicationmap_additional($version);
                break;
            case 'features_featuremap_additional.conf':
                return $this->generate_featuremap_additional($version);
                break;
            case 'res_odbc_additional.conf':
                return $this->generate_res_odbc_additional($version);
                break;
            case 'rtp_additional.conf':
                return $this->generate_rtp_additional($version);
                break;
            case 'http_additional.conf':
                return $this->generate_http_additional($version);
                break;
        }
    }

    // If sipsetting isn't there this will try to set the rtp.conf value
    //
    function setDefaultRtp() {
        // if we have sipsettings then we don't need to do anything
        // it will be done there
        if (function_exists('sipsettings_hookGet_config')) {
            return true;
        }

        $sql = "SELECT value FROM admin WHERE variable = 'RTPSTART'";
        $rtpstart = sql($sql,'getOne');
        $rtpstart = $rtpstart ? $rtpstart : '10000';

        $sql = "SELECT value FROM admin WHERE variable = 'RTPEND'";
        $rtpend = sql($sql,'getOne');
        $rtpend = $rtpend ? $rtpend : '20000';

        $this->addRtpAdditional('general', array('rtpstart' => $rtpstart));
        $this->addRtpAdditional('general', array('rtpend' => $rtpend));
        return true;
    }

    function addRtpAdditional($section,$entries) {
        $this->_rtp_additional[$section][] = $entries;
    }

    function generate_rtp_additional($ast_version) {
        $output = '';
        if (empty($this->_rtp_additional)) {
            $this->setDefaultRtp();
        }
        foreach ($this->_rtp_additional as $section => $entries) {
            $output .= "[".$section."]\n";
            foreach ($entries as $key => $entry) {
                foreach ($entry as $ekey => $value) {
                    $output .= "$ekey=$value\n";
                }
            }
            $output .= "\n";
        }
        return $output;
    }

    function generate_http_additional($ast_version) {
        $issabelpbx_conf =& issabelpbx_conf::create();

        $output = "[general]\n";
        $output .= "enabled=".($issabelpbx_conf->get_conf_setting('HTTPENABLED') ? 'yes' : 'no')."\n";
        $output .= "enablestatic=".($issabelpbx_conf->get_conf_setting('HTTPENABLESTATIC') ? 'yes' : 'no')."\n";
        $output .= "bindaddr=".$issabelpbx_conf->get_conf_setting('HTTPBINDADDRESS')."\n";
        $output .= "bindport=".$issabelpbx_conf->get_conf_setting('HTTPBINDPORT')."\n";
        $output .= "prefix=".$issabelpbx_conf->get_conf_setting('HTTPPREFIX')."\n";
        $output .= "tlsenable=".($issabelpbx_conf->get_conf_setting('HTTPSENABLED') ? 'yes' : 'no')."\n";
        $output .= "tlsbindaddr=".$issabelpbx_conf->get_conf_setting('HTTPSBINDADDRESS')."\n";
        $output .= "tlscertfile=".$issabelpbx_conf->get_conf_setting('HTTPSCERTFILE')."\n";
        $output .= "tlsprivatekey=".$issabelpbx_conf->get_conf_setting('HTTPSPRIVATEKEY')."\n";
        return $output;
    }

    function addSipNotify($section,$entries) {
        $this->_sip_notify[] = array('section' => $section, 'entries' => $entries);
    }

    function generate_sip_notify_additional($ast_version) {
        $output = '';
        if (isset($this->_sip_notify) && is_array($this->_sip_notify)) {
            foreach ($this->_sip_notify as $section) {
                $output .= "[".$section['section']."]\n";
                foreach ($section['entries'] as $key => $value) {
                    if (strtolower($key) == 'content-length') {
                        continue;
                    }
                    $output .= "$key=>$value\n";
                }
                $output .= "\n";
            }
        }
        return $output;
    }

    function addResOdbc($section,$entries) {
        $this->_res_odbc[$section][] = $entries;
    }

    function generate_res_odbc_additional($ast_version) {
        $output = '';
        if (!empty($this->_res_odbc)) {
            foreach ($this->_res_odbc as $section => $entries) {
                $output .= "[".$section."]\n";
                foreach ($entries as $key => $entry) {
                    foreach ($entry as $key => $value) {
                        $output .= "$key=>$value\n";
                    }
                }
                $output .= "\n";
            }
        }
        return $output;
    }

    function addSipAdditional($section, $key, $value) {
        $this->_sip_additional[$section][] = array('key' => $key, 'value' => $value);
    }

    function addSipGeneral($key, $value) {
        foreach($this->_sip_general as $idx => $arreglo) {
            if($arreglo['key']==$key && ($key=='allowguest' || $key=='notifyringing')) {
                unset($this->_sip_general[$idx]);
            }
        }
        $this->_sip_general[] = array('key' => $key, 'value' => $value);
    }

    function generate_sip_general_additional($ast_version) {
        $output = '';

        if (isset($this->_sip_general) && is_array($this->_sip_general)) {
            foreach ($this->_sip_general as $values) {
                $output .= $values['key']."=".$values['value']."\n";
            }
        }
        return $output;
    }

    function addIaxGeneral($key, $value) {
        $this->_iax_general[] = array('key' => $key, 'value' => $value);
    }

    function generate_iax_general_additional($ast_version) {
        $output = '';

        if (isset($this->_iax_general) && is_array($this->_iax_general)) {
            foreach ($this->_iax_general as $values) {
                $output .= $values['key']."=".$values['value']."\n";
            }
        }
        return $output;
    }

    function addParkingGeneral($key, $value) {
        $this->_parkinggeneral[] = array('key' => $key, 'value' => $value);
    }

    function addParkingGeneralSection($section, $key, $value) {
        $this->_parkinggeneralsection[$section][] = array('key' => $key, 'value' => $value);
    }

    function generate_parkinggeneral_additional($ast_version) {
        $output = '';

        if (isset($this->_parkinggeneral) && is_array($this->_parkinggeneral)) {
            foreach ($this->_parkinggeneral as $values) {
                $output .= $values['key']."=".$values['value']."\n";
            }
            foreach ($this->_parkinggeneralsection as $section => $values) {
                $output .= "\n[$section]\n";
                foreach ($values as $value) {
                    $output .= $value['key'] . "=" . $value['value'] . "\n";
                }
            }
        }
        return $output;
    }

    function addManagerGeneral($key, $value) {
        $this->_manager_general[] = array('key' => $key, 'value' => $value);
    }

    function generate_managergeneral_additional($ast_version) {
        $output = '';

        if (isset($this->_manager_general) && is_array($this->_manager_general)) {
            foreach ($this->_manager_general as $values) {
                $output .= $values['key']."=".$values['value']."\n";
            }
        }
        return $output;
    }


    function addFeatureGeneral($key, $value) {
        $this->_featuregeneral[] = array('key' => $key, 'value' => $value);
    }

    function addFeatureGeneralSection($section, $key, $value) {
        $this->_featuregeneralsection[$section][] = array('key' => $key, 'value' => $value);
    }

    function generate_featuregeneral_additional($ast_version) {
        $output = '';

        if (isset($this->_featuregeneral) && is_array($this->_featuregeneral)) {
            foreach ($this->_featuregeneral as $values) {
                $output .= $values['key']."=".$values['value']."\n";
            }
        }
        foreach ($this->_featuregeneralsection as $section => $values) {
            $output .= "\n[$section]\n";
            foreach ($values as $value) {
                $output .= $value['key'] . "=" . $value['value'] . "\n";
            }
        }
        return $output;
    }

    function addFeatureMap($key, $value) {
        $this->_featuremap[] = array('key' => $key, 'value' => $value);
    }

    function generate_featuremap_additional($ast_version) {
        $output = '';

        if (isset($this->_featuremap) && is_array($this->_featuremap)) {
            foreach ($this->_featuremap as $values) {
                $output .= $values['key']."=".$values['value']."\n";
            }
        }
        return $output;
    }

    function addApplicationMap($key, $value, $add_to_dynamic_features=false) {
        global $ext;
        $this->_applicationmap[] = array('key' => $key, 'value' => $value);
        //
        // Now add it to the DYNAMIC_FEATURES
        // TODO: one caveat, if we ever want to make such an application conditional, we will have to change
        // this as for now it makes it for everyone.
        //
        if ($add_to_dynamic_features) {
            $ext->_globals['DYNAMIC_FEATURES'] = empty($ext->_globals['DYNAMIC_FEATURES']) ? $key : $ext->_globals['DYNAMIC_FEATURES'] . '#' . $key;
        }
    }

    function generate_applicationmap_additional($ast_version) {
        $output = '';

        if (isset($this->_applicationmap) && is_array($this->_applicationmap)) {
            foreach ($this->_applicationmap as $values) {
                $output .= $values['key']."=>".$values['value']."\n";
            }
        }
        return $output;
    }

    function generate_pjsip_transport_additional($ast_version) {
        global $db, $amp_conf;

        $certlistfile = "/etc/pki/tls/certs/ca-bundle.crt";
        if(isset($amp_conf['CERTLISTFILE'])) {
            $certlistfile = $amp_conf['CERTLISTFILE'];
        }

        $output1 = array();
        $output2 = array();
        $output3 = array();
        $output4 = array();

        $output1[] = "[transport-udp]";
        $output1[] = "type=transport";
        $output1[] = "protocol=udp";
        $output1[] = "allow_reload=true";
           
        $output2[] = "[transport-tcp]";
        $output2[] = "type=transport";
        $output2[] = "protocol=tcp";
        $output2[] = "allow_reload=true";

        if(file_exists($certlistfile)) {
            $output3[] = "[transport-tls]";
            $output3[] = "type=transport";
            $output3[] = "protocol=tls";
            $output3[] = "allow_reload=true";
            $output3[] = "ca_list_file=$certlistfile";
        }

        $output4[] = "[transport-ws]";
        $output4[] = "type=transport";
        $output4[] = "protocol=ws";
        $output4[] = "allow_reload=true";
 
        $output5[] = "[transport-wss]";
        $output5[] = "type=transport";
        $output5[] = "protocol=wss";
        $output5[] = "allow_reload=true";
 
        $sql = "SELECT keyword,data FROM pjsipsettings";

        $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
        if(DB::IsError($results)) {
            die($results->getMessage());
        }

        $bindaddr_df     = '0.0.0.0';
        $bindport_df     = '5066';
        $tlsbindaddr_df  = '0.0.0.0';
        $tlsbindport_df  = '5067';

        $localnet        = array();
        $netmask         = array();

        $cert_file       = '';
        $nat             = '';

        foreach($results as $result) {
            $kw     = strtolower($result['keyword']);
            $value  = strtolower($result['data']);
            if($kw=='bindaddr') {
                $bindaddr = ($value<>'')?$value:$bindaddr_df;
            } else
            if($kw=='bindport') {
                $bindport = ($value<>'')?$value:$bindport_df;
            } else
            if($kw=='tlsbindaddr') {
                $tlsbindaddr = ($value<>'')?$value:$tlsbindaddr_df;
            } else
            if($kw=='tlsbindport') {
                $tlsbindport = ($value<>'')?$value:$tlsbindport_df; 
            } else 
            if($kw=='method') {
               $output3[]="method=$value";
            } else
            if($kw=='certfile' && file_exists($certlistfile)) {
                $output3[]="cert_file=$value";
                $output3[]="priv_key_file=$value";
                $cert_file=$value;
            } else
            if(substr($kw,0,8)=="localnet") {
                $parts = preg_split("/_/",$kw);
                $localnet[$parts[1]]=$value;
            } else
            if(substr($kw,0,7)=="netmask") {
                $parts = preg_split("/_/",$kw);
                $netmask[$parts[1]]=$value;
            } else 
            if($kw=='externip_val') {
                $externip = $value;
            } else 
            if($kw=='externhost_val') {
                $externhost = $value;
            } else 
            if($kw=='nat_mode') {
                $natmode = $value;
            } else
            if($kw=='nat') {
                $nat=$value;
            }
        }

        if(!isset($bindaddr)) $bindaddr = $bindaddr_df;
        if(!isset($bindport)) $bindport = $bindport_df;

        if(!isset($tlsbindaddr)) $tlsbindaddr = $tlsbindaddr_df;
        if(!isset($tlsbindport)) $tlsbindport = $tlsbindport_df;

        if($cert_file=='' && file_exists($certlistfile)) {
            $output3[]="cert_file=/etc/asterisk/keys/asterisk.pem";
            $output3[]="priv_key_file=/etc/asterisk/keys/asterisk.key";
        }

        $output1[]="bind=$bindaddr:$bindport";
        $output2[]="bind=$bindaddr:$bindport";
        if(file_exists($certlistfile)) {
            $output3[]="bind=$tlsbindaddr:$tlsbindport";
        }
        $output4[]="bind=$bindaddr:$bindport";
        $output5[]="bind=$bindaddr:$bindport";

        if($nat=='yes') {
            if($natmode=='externhost') { 
                $output1[]="external_media_address=".$externhost;
                $output1[]="external_signaling_address=".$externhost;
                $output2[]="external_media_address=".$externhost;
                $output2[]="external_signaling_address=".$externhost;
                $output3[]="external_media_address=".$externhost;
                $output3[]="external_signaling_address=".$externhost;
                $output4[]="external_media_address=".$externhost;
                $output4[]="external_signaling_address=".$externhost;
                $output5[]="external_media_address=".$externhost;
                $output5[]="external_signaling_address=".$externhost;
            } else 
            if($natmode=='externip') {
                $output1[]="external_media_address=".$externip;
                $output1[]="external_signaling_address=".$externip;
                $output2[]="external_media_address=".$externip;
                $output2[]="external_signaling_address=".$externip;
                $output3[]="external_media_address=".$externip;
                $output3[]="external_signaling_address=".$externip;
                $output4[]="external_media_address=".$externip;
                $output4[]="external_signaling_address=".$externip;
                $output5[]="external_media_address=".$externip;
                $output5[]="external_signaling_address=".$externip;
            }

            foreach($localnet as $idx=>$value) {
                $output1[]="local_net=".$value."/".$netmask[$idx];
                $output2[]="local_net=".$value."/".$netmask[$idx];
                $output3[]="local_net=".$value."/".$netmask[$idx];
                $output4[]="local_net=".$value."/".$netmask[$idx];
                $output5[]="local_net=".$value."/".$netmask[$idx];
            }
        }

        $output= implode("\n",$output1);
        $output.= "\n";
        $output.= "\n";
        $output.= implode("\n",$output2);
        $output.= "\n";
        $output.= "\n";
        $output.= implode("\n",$output3);
        $output.= "\n";
        $output.= "\n";
        $output.= implode("\n",$output4);
        $output.= "\n";
        $output.= "\n";
        $output.= implode("\n",$output5);
        $output.= "\n";
        $output.= "\n";
        $output.= "\n";

        return $output; 
    }

    function generate_pjsip_additional($ast_version) {

        global $db;
        global $astman;
        global $amp_conf;

        $additional = "";
        $output1 = array();
        $output2 = array();
        $output3 = array();

        $allowguest = sql("SELECT `data` FROM `pjsipsettings` WHERE `keyword` = 'allowguest'",'getOne');
        $output0 = array();
        if($allowguest=='yes') {
            $output0[]="[anonymous]";
            $output0[]="type=endpoint";
            $output0[]="context=from-sip-external";
            $output0[]="allow=all";
            $output0[]="transport=udp,tcp,ws,wss";
            $output0[]="";
        }

        $deflang = sql("SELECT `data` FROM `pjsipsettings` WHERE `keyword` = 'sip_language'",'getOne');
        $default_language = ($deflang<>'')?$deflang:'';

        $sql = "SELECT data,sip.id from sip LEFT JOIN devices ON devices.id=sip.id where tech='pjsip' and keyword='account' group by data";

        $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
        if(DB::IsError($results)) {
            die($results->getMessage());
        }

        $output='';
        // allow guest - anonymous
        $output.= implode("\n",$output0);
        $output.= "\n";

        foreach ($results as $result) {

            $account = $result['data'];
            $id      = $result['id'];

            if($astman) {
                $endpoint_lang = $astman->database_get("AMPUSER",$account."/language");
                $endpoint_parkinglot = $astman->database_get("AMPUSER",$account."/parkinglot");
            }
            $lang = ($endpoint_lang<>'')?$endpoint_lang:$default_language;

            $output1 = array();
            $output2 = array();
            $output3 = array();

            $output1[] = "[$account]";
            $output1[] = "auth=auth$account";
            $output1[] = "aors=$account";
            $output1[] = "type=endpoint";
            if($lang<>'') {
                $output1[] = "language=$lang";
            }
            if($endpoint_parkinglot<>'') {
                $output1[] = "set_var=CHANNEL(parkinglot)=$endpoint_parkinglot";
            }
           
            $output2[] = "[auth$account]";
            $output2[] = "type=auth";
            $output2[] = "auth_type=userpass"; 
            $output2[] = "username=$account";

            $output3[] = "[$account]";
            $output3[] = "type=aor";

            $cod=array();
            $sql = "SELECT keyword FROM pjsipsettings WHERE type=1 AND data<>'' ORDER BY data";
            $res = $db->getAll($sql, DB_FETCHMODE_ASSOC);
            if(DB::IsError($res)) {
                die($res->getMessage());
            }

            if(count($res)==0) {
                $codecs='ulaw,alaw,gsm';
            } else {
                foreach ($res as $element) {
                    $cod[]=$element['keyword'];
                }
                $codecs = implode(",",$cod);
            }

            $sql = "SELECT keyword,data from sip where id='$id' and keyword <> 'account' and flags <> 1 order by flags, keyword DESC";
            $results2_pre = $db->getAll($sql, DB_FETCHMODE_ASSOC);
            if(DB::IsError($results2_pre)) {
                die($results2->getMessage());
            }

            $results2 = array();
            foreach ($results2_pre as $element) {
                if (strtolower(trim($element['keyword'])) != 'secret') {
                    $options = explode("&", $element['data']);
                    foreach ($options as $option) {
                        if (($element['keyword'] == 'disallow' && $option == 'all') | ($element['keyword'] == 'deny')) {
                            array_unshift($results2,array('keyword'=>$element['keyword'],'data'=>$option));
                        } else {
                            $results2[] = array('keyword'=>$element['keyword'],'data'=>$option);
                        }
                    }
                } else {
                    $results2[] = array('keyword'=>$element['keyword'],'data'=>str_replace(';','\;',$element['data']));
                }
            }
            unset($results2_pre);

            $context='';
            foreach ($results2 as $result2) {
                $option = strtolower($result2['data']);
                $kw     = strtolower($result2['keyword']);
                switch ($kw) {
                        case 'dtmfmode':
                        case 'insecure':
                        case 'canreinvite':
                        case 'port':
                        case 'qualify':
                        case 'dtlsenable':
                        case 'dial':
                        case 'encryption':
                        case 'parkinglot':
                        case 'host':
                            break;
                        case 'sendrpid':
                            $output1[]='send_rpid='.$result2['data'];
                            break;
                        case 'trustrpid':
                            $output1[]='trust_id_inbound='.$result2['data'];
                            break;
                        case 'disallow':
                            if($option<>'') {
                                $output1[]='disallow='.$option;
                            } else {
                                $output1[]='disallow=all';
                            }
                            break;
                        case 'allow':
                            if($option<>'') {
                                $output1[]='allow='.$option;
                            } else {
                                $output1[]='allow='.$codecs;
                            }
                            break;
                        case 'transport':
                            $output1[]='transport='.$result2['data'];
                            break;
                        case 'allow':
                            if($result2['data']=='') $result2['data']='all';
                            $output1[]='allow='.$result2['data'];
                            break;
                        case 'pickupgroup':
                            $output1[]='pickup_group='.$result2['data'];
                            break;
                        case 'callgroup':
                            $output1[]='call_group='.$result2['data'];
                            break;
                        case 'dtmfmode':
                            $output1[]='dtmf_mode='.$result2['data'];
                            break;
                        case 'ice_support':
                        case 'icesupport':
                            $output1[]='ice_support='.$result2['data'];
                            break;
                        case 'use_avpf':
                        case 'avpf':
                            $output1[]='use_avpf='.$result2['data'];
                            break;
                        case 'dtls_verify':
                        case 'dtlsverify':
                            $output1[]='dtls_verify='.$result2['data'];
                            break;
                        case 'dtls_setup':
                        case 'dtlssetup':
                            $output1[]='dtls_setup='.$result2['data'];
                            break;
                        case 'dtlscertfile':
                            $output1[]='dtls_cert_file='.$result2['data'];
                            break;
                        case 'dtlsprivatekey':
                            $output1[]='dtls_private_key='.$result2['data'];
                            break;
                        case 'qualify_frequency':
                        case 'qualifyfreq':
                            $output3[] = "qualify_frequency=".$result2['data'];
                            break;
                        case 'outbound_proxy':
                            if(trim($result2['data'])!='') {
                                $output1[] = "outbound_proxy=sip:".$result2['data'].'\;lr';
                                $output3[] = "outbound_proxy=sip:".$result2['data'].'\;lr';
                            }
                            break;
                        case 'type':
                            $output1[] = "type=endpoint";
                            break;
                        case 'secret':
                            $output2[] = "password=".$result2['data'];
                            break;
                        case 'nat':
                            if ($option=='yes') {
                                $output1[]="rtp_symmetric=yes";
                                $output1[]="force_rport=yes";
                                $output1[]="rewrite_contact=yes";
                            } else if($option=='no' || $option=='never') {
                                $output1[]="rtp_symmetric=no";
                                $output1[]="force_rport=no";
                                $output1[]="rewrite_contact=no";
                            } else {
                                $output1[]="rtp_symmetric=no";
                                $output1[]="force_rport=yes";
                                $output1[]="rewrite_contact=yes";
                            }
                            break;
                        case 'accountcode':
                            if ($option != '')
                                $output1[] = $result2['keyword']."=".$result2['data'];
                            break;
                        case 'callerid':
                        case 'mailbox':
                            if(!preg_match('/@/',$result2['data'])) {
                                $result2['data']=$account.'@'.'device';
                            }
                            $text = substr($this->map_dev_user($account, $result2['keyword'], $result2['data']),0,-1);
                            $text = preg_replace("/mailbox=/","mailboxes=",$text);
                            $output1[] = $text;
                            break;
                        case 'max_contacts':
                            $output3[]=$kw."=".intval($option);
                            break;
                        case 'qualify_timeout':
                            $output3[]=$kw."=".$option;
                            break;
                        case 'authenticate_qualify':
                            $output3[]=$kw."=".$option;
                            break;
                        case 'remove_existing':
                            $output3[]=$kw."=".$option;
                            break;
                        case 'stir_shaken':
                            if(file_exists("/etc/asterisk/stir_shaken.conf")) {
                                $output1[] = $result2['keyword']."=".$result2['data'];
                            }
                            break;
                        default:
                            $output1[] = $result2['keyword']."=".$result2['data'];
                    }
            }

            $output.= implode("\n",$output1);
            $output.= "\n";
            $output.= "\n";
            $output.= implode("\n",$output2);
            $output.= "\n";
            $output.= "\n";
            $output.= implode("\n",$output3);
            $output.= "\n";
            $output.= "\n";

        }


        //trunks

        $username = array();
        $server   = array();
        $aor      = array();
        $auth     = array();
        $reg      = array();
        $endp     = array();
        $match    = array();
        $authentication = array();
        $registration = array();
        $server = array();

        if(preg_match("/qlite/",$amp_conf['AMPDBENGINE'])){
            $sql = "SELECT channelid,keyword,data FROM trunks LEFT JOIN sip ON 'pjsip-' || trunkid = sip.id WHERE tech='pjsip' UNION ";
            $sql .= "SELECT channelid,keyword,data FROM trunks LEFT JOIN sip ON 'tr-reg-' || trunkid = sip.id WHERE tech='pjsip' ";
        } else {
            $sql = "SELECT channelid,keyword,data FROM trunks LEFT JOIN sip ON CONCAT('pjsip-',trunkid)=sip.id WHERE tech='pjsip' UNION ";
            $sql .= "SELECT channelid,keyword,data FROM trunks LEFT JOIN sip ON CONCAT('tr-reg-',trunkid)=sip.id WHERE tech='pjsip' ";
        }
        $res = $db->getAll($sql, DB_FETCHMODE_ASSOC);
        if(DB::IsError($res)) {
            die($res->getMessage());
        }

        foreach ($res as $element) {
           $channelid = $element['channelid'];
           $keyword   = $element['keyword'];
           $data      = $element['data'];
           if($keyword=='registration') {
               $registration[$channelid]=$data;
               if($data=='receive' ) {
                   $aor[$channelid]['max_contacts']="1";
               } 
               if($data=='none') {
                   $aor[$channelid]['contact']="sip:#SERVER#";
               } else {
                   $aor[$channelid]['contact']="sip:#USER#@#SERVER#";
               }
           } else if ($keyword == "authentication") {
               $authentication[$channelid]=$data;
               if($data=='outbound') {
                   $endp[$channelid]['outbound_auth']=$channelid;
               } else if($data=='inbound') {
                   $endp[$channelid]['auth']=$channelid;
               } else if($data=='both') {
                   $endp[$channelid]['outbound_auth']=$channelid;
                   $endp[$channelid]['auth']=$channelid;
               } else if($data=='none') {
                   $aor[$channelid]['contact']="sip:#SERVER#";
               }
           } else if ($keyword == "username") {
               if($data<>'') {
                   $auth[$channelid]['username']=$data;
               } else {
                   $auth[$channelid]['username']=$channelid;
               }
           } else if ($keyword == "secret") {
               $auth[$channelid]['secret']=$data;
           } else if ($keyword == "server") {
               $server[$channelid]=$data;
           } else if ($keyword == "qualify_frequency") {
               $aor[$channelid]['qualify_frequency']=$data;
           } else if ($keyword == "support_path") {
               $aor[$channelid]['support_path']=$data;
           } else if ($keyword == "outbound_proxy") {
               if(trim($data)!='') {
                   $reg[$channelid]['outbound_proxy']="sip:".$data.'\;lr';
               }
           } else if ($keyword == "match") {
               $match[$channelid]=$data;
           } else if ($keyword == "retry_interval") {
               $reg[$channelid]['retry_interval']=$data;
           } else if ($keyword == "max_retries") {
               $reg[$channelid]['max_retries']=$data;
           } else if ($keyword == "expiration") {
               $reg[$channelid]['expiration']=$data;
           } else if ($keyword == "transport") {
               $reg[$channelid]['transport']=$data;
               $endp[$channelid]['transport']=$data;
           } else if ($keyword == "auth_rejection_permanent") {
               $reg[$channelid]['auth_rejection_permanent']=$data;
           } else if ($keyword == "context") {
               $endp[$channelid]['context']=$data;
           } else if ($keyword == "direct_media") {
               $endp[$channelid]['direct_media']=$data;
           } else if ($keyword == "rtp_symmetric") {
               $endp[$channelid]['rtp_symmetric']=$data;
           } else if ($keyword == "rewrite_contact") {
               $endp[$channelid]['rewrite_contact']=$data;
           } else if ($keyword == "dtmf_mode") {
               $endp[$channelid]['dtmf_mode']=$data;
           } else if ($keyword == "trust_id_inbound") {
               $endp[$channelid]['trust_id_inbound']=$data;
           } else if ($keyword == "fax_detect") {
               $endp[$channelid]['fax_detect']=$data;
           } else if ($keyword == "t38_udptl") {
               $endp[$channelid]['t38_udptl']=$data;
           } else if ($keyword == "t38_udptl_nat") {
               $endp[$channelid]['t38_udptl_nat']=$data;
           } else if ($keyword == "t38_udptl_ec") {
               $endp[$channelid]['t38_udptl_ec']=$data;
           } else if ($keyword == "codecs") {
               $endp[$channelid]['allow']=$data;
           } else if ($keyword == "from_domain") {
               $endp[$channelid]['from_domain']=$data;
           } else if ($keyword == "from_user") {
               $endp[$channelid]['from_user']=$data;
           } else if ($keyword == "media_encryption") {
               $endp[$channelid]['media_encryption']=$data;
           } else if ($keyword == "contact_user") {
               $reg[$channelid]['contact_user']=$data;
               $endp[$channelid]['contact_user']=$data;
           } else if ($keyword == "stir_shaken") {
               if(file_exists("/etc/asterisk/stir_shaken.conf")) {
                   $endp[$channelid]['stir_shaken'] = $data;
               }
           }
        }

        $output_aor = array();
        foreach($aor as $id=>$data) {
            $output_aor[]="[$id]";
            $output_aor[]="type=aor";
            foreach($data as $key=>$val) {
                if($key=='contact') {
                    if($registration[$id]=='receive') {
                        $val=''; // we do not want contact in aor  
                    } else {
                        $val = preg_replace("/#SERVER#/",$server[$id],$val);
                        if($authentication[$id]=='outbound') {
                            $val = preg_replace("/#USER#/",$auth[$id]['username'],$val);
                        } else {
                            $val = preg_replace("/#USER#/",$id,$val);
                        }
                    }
                } 
                if(trim($val)<>'') {
                    $output_aor[]="$key=$val";
                }
            }
            $output_aor[]="";
        }

        $output_auth = array();
        foreach($auth as $id=>$data) {
            $output_auth[]="[$id]";
            $output_auth[]="type=auth";
            $output_auth[]="auth_type=userpass";
            foreach($data as $key=>$val) {
                if($key=='secret') {
                    if($authentication[$id]=='none') {
                         $output_auth[]="password=";
                    } else {
                         $output_auth[]="password=$val";
                    }
                } else {
                    if(trim($val)<>'') {
                        $output_auth[]="$key=$val";
                    } else {
                        if($key=='username') {
                            $output_auth[]="username=$id";
                        }
                    }
                }
            }
            if($authentication[$id]<>'outbound') {
                $output_auth[]="username=$id";
            }
            $output_auth[]="";
        }
 
        $output_identify = array();
        foreach($server as $id=>$value) {
            $output_identify[]="[$id]";
            $output_identify[]="type=identify";
            $output_identify[]="endpoint=$id";

            $thismatch = $value;
            if(isset($match[$id])) {
                if(trim($match[$id])<>"") {
                    $thismatch = $match[$id];
                }
            }
            $output_identify[]="match=".$thismatch;
            $output_identify[]="";
        }
 
        $output_registration = array();
        foreach($registration as $id=>$value) {
            if($value=='send') {
                $output_registration[]="[$id]";
                $output_registration[]="type=registration";
                $output_registration[]="endpoint=$id";
                $output_registration[]="line=yes";
                $output_registration[]="outbound_auth=$id";
                $output_registration[]="server_uri=sip:".$server[$id];
                if($authentication[$id]=='outbound') {
                    $output_registration[]="client_uri=sip:".$auth[$id]['username']."@".$server[$id];
                } else {
                    $output_registration[]="client_uri=sip:".$id."@".$server[$id];
                }
                foreach($reg[$id] as $key=>$val) {
                    if(trim($val)<>'') {
                        $output_registration[]="$key=$val";
                    }
                }
            }
            $output_registration[]="";
        }

        $output_endpoint = array();
        foreach($endp as $id=>$data) {
            $output_endpoint[]="[$id]";
            $output_endpoint[]="type=endpoint";
            $output_endpoint[]="aors=$id";
            $output_endpoint[]="disallow=all";
            foreach($data as $key=>$val) {
                if(trim($val)<>'') {
                    $output_endpoint[]="$key=$val";
                }
            }
            $output_endpoint[]="";
        }

        $output.= "; Trunks\n";
        $output.= "\n";
        $output.= "\n";
        $output.= implode("\n",$output_aor);
        $output.= "\n";
        $output.= implode("\n",$output_auth);
        $output.= "\n";
        $output.= implode("\n",$output_identify);
        $output.= "\n";
        $output.= implode("\n",$output_registration);
        $output.= "\n";
        $output.= implode("\n",$output_endpoint);
        $output.= "\n";

        return $output;
    }

    function generate_sip_additional($ast_version) {
        global $db, $astman;

        $table_name = "sip";
        $additional = "";
        $output = "";

        // Asterisk 1.4 requires call-limit be set for hints to work properly
        //
        if (version_compare($ast_version, "1.6.1", "ge")) {
            $call_limit = "callcounter=yes\n";
            $ver12 = false;
        } else if (version_compare($ast_version, "1.4", "ge")) {
            $call_limit = "call-limit=1\n";
            $ver12 = false;
        } else {
            $call_limit = "";
            $ver12 = true;
        }
        if (version_compare($ast_version, "1.6", "ge")) {
            $faxdetect = "faxdetect=no\n";
            $ver16 = true;
        } else {
            $faxdetect = "";
            $ver16 = false;
        }
        // TODO: Temporary Kludge until CCSS is fixed
        //
        if (function_exists('campon_get_config') && version_compare($ast_version, "1.8", "ge")) {
            $cc_monitor_policy = "cc_monitor_policy=generic\n";
        } else {
            $cc_monitor_policy = "";
        }

        $sql = "SELECT keyword,data from $table_name where id=-1 and keyword <> 'account' and flags <> 1";
        $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
        if(DB::IsError($results)) {
            die($results->getMessage());
        }
        foreach ($results as $result) {
            if ($ver12) {
                $additional .= $result['keyword']."=".$result['data']."\n";
            } else {
                $option = $result['data'];
                switch (strtolower($result['keyword'])) {
                    case 'insecure':
                        if ($option == 'very')
                            $additional .= "insecure=port,invite\n";
                        else if ($option == 'yes')
                            $additional .= "insecure=port\n";
                        else
                            $additional .= $result['keyword']."=$option\n";
                        break;
                    case 'allow':
                    case 'disallow':
                    case 'accountcode':
                        if ($option != '')
                            $additional .= $result['keyword']."=$option\n";
                        break;
                    default:
                        $additional .= $result['keyword']."=$option\n";
                }
            }
        }

        $sql = "SELECT data,$table_name.id from $table_name LEFT JOIN devices ON devices.id=$table_name.id where keyword='account' and flags <> 1 AND (tech='sip' OR (tech is null AND sip.id NOT LIKE 'pjsip%')) group by data";
        $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
        if(DB::IsError($results)) {
           die($results->getMessage());
        }

        foreach ($results as $result) {
            $account = $result['data'];
            $id = $result['id'];
            $output .= "[$account]\n";

            $sql = "SELECT keyword,data from $table_name where id='$id' and keyword <> 'account' and flags <> 1 order by flags, keyword DESC";
            $results2_pre = $db->getAll($sql, DB_FETCHMODE_ASSOC);
            if(DB::IsError($results2_pre)) {
               die($results2->getMessage());
            }

            // Move all 'disallow=all' and 'deny' to the top to avoid errors
            //
            $results2 = array();
            foreach ($results2_pre as $element) {
                if (strtolower(trim($element['keyword'])) != 'secret') {
                    $options = explode("&", $element['data']);
                    foreach ($options as $option) {
                        if (($element['keyword'] == 'disallow' && $option == 'all') | ($element['keyword'] == 'deny')) {
                            array_unshift($results2,array('keyword'=>$element['keyword'],'data'=>$option));
                        } else {
                            $results2[] = array('keyword'=>$element['keyword'],'data'=>$option);
                        }
                    }
                } else {
                    $results2[] = array('keyword'=>$element['keyword'],'data'=>str_replace(';','\;',$element['data']));
                }
            }
            unset($results2_pre);

            $context='';
            foreach ($results2 as $result2) {
                $option = strtolower($result2['data']);
                if ($ver12) {
                    switch (strtolower($result2['keyword'])) {
                        case 'context':
                            $context = $option;
                            //fall-through
                        default:
                            $output .= $result2['keyword']."=".$result2['data']."\n";
                    }
                } else {
                    switch (strtolower($result2['keyword'])) {
                        case 'insecure':
                            if ($option == 'very')
                                $output .= "insecure=port,invite\n";
                            else if ($option == 'yes')
                                $output .= "insecure=port\n";
                            else
                                $output .= $result2['keyword']."=".$result2['data']."\n";
                            break;
                        case 'allow':
                        case 'disallow':
                        case 'accountcode':
                            if ($option != '')
                                $output .= $result2['keyword']."=".$result2['data']."\n";
                            break;
                        case 'callerid':
                        case 'mailbox':
                            $output .= $this->map_dev_user($account, $result2['keyword'], $result2['data']);
                            break;
                        case 'context':
                            $context = $result2['data'];
                            //fall-through
                        default:
                            $output .= $result2['keyword']."=".$result2['data']."\n";
                    }
                }
            }

            if($astman) {
                $endpoint_parkinglot = $astman->database_get("AMPUSER",$account."/parkinglot");
                if($endpoint_parkinglot<>'') {
                    $output .= "setvar=CHANNEL(parkinglot)=$endpoint_parkinglot\n";
                }
            }

            switch (substr($id,0,8)) {
                case 'tr-peer-':
                    if ($context == '') {
                        $output .= "context=from-trunk-sip-$account\n";
                    }
                    break;
                case 'tr-user-':
                    if ($context == '') {
                        $tn = substr($id, 8);
                        // this is a 'user' trunk, we need to get the name of the corresponding 'peer'
                        // trunk so we can set the context appropriately for the group count
                        //
                        $td = core_trunks_getDetails($tn);
                        if (isset($td['channelid'])) {
                            $output .= "context=from-trunk-sip-".$td['channelid']."\n";
                        }
                    }
                    break;
                default:
                    if ($call_limit) {
                        $output .= $call_limit;
                    }
                    if ($faxdetect) {
                        $output .= $faxdetect;
                    }
                    if ($cc_monitor_policy) {
                        $output .= $cc_monitor_policy;
                    }
            }
            if (isset($this->_sip_additional[$account])) {
                foreach ($this->_sip_additional[$account] as $asetting) {
                    $output .= $asetting['key'] . "=" . $asetting['value'] . "\n";
                }
            }
            $output .= $additional."\n";
        }
        return $output;
    }

    function generate_sip_registrations($ast_version) {
        global $db;

        $table_name = "sip";
        $output = "";

        $sql = "SELECT keyword,data FROM $table_name LEFT JOIN trunks ON substr(id,8)=trunks.trunkid WHERE `id` LIKE 'tr-reg-%' AND keyword <> 'account' AND flags <> 1 AND tech<>'pjsip'";
        $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
        if(DB::IsError($results)) {
            die($results->getMessage());
        }

        foreach ($results as $result) {
            $output .= $result['keyword']."=".$result['data']."\n";
        }

        return $output;
    }

    function addIaxAdditional($section, $key, $value) {
        $this->_iax_additional[$section][] = array('key' => $key, 'value' => $value);
    }

    function generate_iax_additional($ast_version) {
        global $db;

        $table_name = "iax";
        $additional = "";
        $output = "";

        $ver12 = version_compare($ast_version, '1.4', 'lt');

        $sql = "SELECT keyword,data from $table_name where id=-1 and keyword <> 'account' and flags <> 1";
        $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
        if(DB::IsError($results)) {
           die($results->getMessage());
        }
        foreach ($results as $result) {
            if ($ver12) {
                $additional .= $result['keyword']."=".$result['data']."\n";
            } else {
                $option = $result['data'];
                switch ($result['keyword']) {
                    case 'notransfer':
                        if (strtolower($option) == 'yes') {
                            $additional .= "transfer=no\n";
                        } else if (strtolower($option) == 'no') {
                            $additional .= "transfer=yes\n";
                        } else if (strtolower($option) == 'mediaonly') {
                            $additional .= "transfer=mediaonly\n";
                        } else {
                            $additional .= $result['keyword']."=$option\n";
                        }
                        break;
                    case 'allow':
                    case 'disallow':
                    case 'accountcode':
                        if ($option != '')
                            $additional .= $result['keyword']."=$option\n";
                        break;
                    case 'requirecalltoken':
                        if ($option != '')
                            $additional .= $result['keyword']."=$option\n";
                        break;
                    default:
                        $additional .= $result['keyword']."=$option\n";
                }
            }
        }

        $sql = "SELECT data,id from $table_name where keyword='account' and flags <> 1 group by data";
        $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
        if(DB::IsError($results)) {
            die($results->getMessage());
        }

        foreach ($results as $result) {
            $account = $result['data'];
            $id = $result['id'];
            $output .= "[$account]\n";

            $sql = "SELECT keyword,data from $table_name where id='$id' and keyword <> 'account' and flags <> 1 order by flags, keyword DESC";
            $results2_pre = $db->getAll($sql, DB_FETCHMODE_ASSOC);
            if(DB::IsError($results2_pre)) {
                die($results2_pre->getMessage());
            }

            // Move all 'disallow=all' and 'deny=' to the top to avoid errors
            //
            $results2 = array();
            foreach ($results2_pre as $element) {
                if (strtolower(trim($element['keyword'])) != 'secret') {
                    $options = explode("&", $element['data']);
                    foreach ($options as $option) {
                        if (($element['keyword'] == 'disallow' && $option == 'all') | ($element['keyword'] == 'deny')) {
                            array_unshift($results2,array('keyword'=>$element['keyword'],'data'=>$option));
                        } else {
                            $results2[] = array('keyword'=>$element['keyword'],'data'=>$option);
                        }
                    }
                } else {
                    $results2[] = array('keyword'=>$element['keyword'],'data'=>str_replace(';','\;',$element['data']));
                }
            }
            unset($results2_pre);

            $context='';
            foreach ($results2 as $result2) {
                $option = strtolower($result2['data']);
                if ($ver12) {
                    switch (strtolower($result2['keyword'])) {
                        case 'context':
                            $context = $result2['data'];
                            //fall-through
                        default:
                            $output .= $result2['keyword']."=".$result2['data']."\n";
                    }
                } else {
                    switch ($result2['keyword']) {
                        case 'notransfer':
                            if (strtolower($option) == 'yes') {
                                $output .= "transfer=no\n";
                            } else if (strtolower($option) == 'no') {
                                $output .= "transfer=yes\n";
                            } else if (strtolower($option) == 'mediaonly') {
                                $output .= "transfer=mediaonly\n";
                            } else {
                                $output .= $result2['keyword']."=".$result2['data']."\n";
                            }
                            break;
                        case 'allow':
                        case 'disallow':
                        case 'accountcode':
                            if ($option != '')
                                $output .= $result2['keyword']."=".$result2['data']."\n";
                            break;
                        case 'requirecalltoken':
                            if ($option != '')
                                $output .= $result2['keyword']."=".$result2['data']."\n";
                            break;
                        case 'callerid':
                        case 'mailbox':
                            $output .= $this->map_dev_user($account, $result2['keyword'], $result2['data']);
                            break;
                        case 'context':
                            $context = $option;
                            //fall-through
                        default:
                            $output .= $result2['keyword']."=".$result2['data']."\n";
                    }
                }
            }
            switch (substr($id,0,8)) {
                case 'tr-peer-':
                    if ($context == '') {
                        $output .= "context=from-trunk-iax2-$account\n";
                    }
                    break;
                case 'tr-user-':
                    if ($context == '') {
                        $tn = substr($id, 8);
                        // this is a 'user' trunk, we need to get the name of the corresponding 'peer'
                        // trunk so we can set the context appropriately for the group count
                        //
                        $td = core_trunks_getDetails($tn);
                        if (isset($td['channelid'])) {
                            $output .= "context=from-trunk-iax2-".$td['channelid']."\n";
                        }
                    }
                    break;
                default:
            }
            if (isset($this->_iax_additional[$account])) {
                foreach ($this->_iax_additional[$account] as $asetting) {
                    $output .= $asetting['key'] . "=" . $asetting['value'] . "\n";
                }
            }
            $output .= $additional."\n";
        }
        return $output;
    }

    function generate_iax_registrations($ast_version) {
        global $db;

        $table_name = "iax";
        $output = "";

        $sql = "SELECT keyword,data FROM $table_name WHERE `id` LIKE 'tr-reg-%' AND keyword <> 'account' AND flags <> 1";
        $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
        if(DB::IsError($results)) {
           die($results->getMessage());
        }

        foreach ($results as $result) {
            $output .= $result['keyword']."=".$result['data']."\n";
        }

        return $output;
    }

    function addDahdiAdditional($section, $key, $value) {
        $this->_dahdi_additional[$section][] = array('key' => $key, 'value' => $value);
    }

    function generate_zapata_additional($ast_version, $table_name = 'zap') {
        global $db;

        $additional = "";
        $output = '';

        $sql = "SELECT keyword,data from $table_name where id=-1 and keyword <> 'account' and flags <> 1";
        $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
        if(DB::IsError($results)) {
            if($table_name == 'zap') {
                return '';
            } else {
               die($results->getMessage());
            }
        }
        foreach ($results as $result) {
            $additional .= $result['keyword']."=".$result['data']."\n";
        }

        $sql = "SELECT data,id from $table_name where keyword='account' and flags <> 1 group by data";
        $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
        if(DB::IsError($results)) {
           die($results->getMessage());
        }

        foreach ($results as $result) {
            $account = $result['data'];
            $id = $result['id'];
            $output .= ";;;;;;[$account]\n";

            $sql = "SELECT keyword,data from $table_name where id=$id and keyword <> 'account' and flags <> 1 order by keyword DESC";
            $results2 = $db->getAll($sql, DB_FETCHMODE_ASSOC);
            if(DB::IsError($results2)) {
               die($results2->getMessage());
            }
            $zapchannel="";
            foreach ($results2 as $result2) {
                switch ($result2['keyword']) {
                    case 'channel':
                        $zapchannel = $result2['data'];
                        break;

                    // These are not zapata.conf variables so keep out of file
                    case 'dial':
                        break;
                    case 'callerid':
                    case 'mailbox':
                        $output .= $this->map_dev_user($account, $result2['keyword'], $result2['data']);
                        break;
                    default:
                        $output .= $result2['keyword']."=".$result2['data']."\n";
                }
            }
            if (isset($this->_dahdi_additional[$account])) {
                foreach ($this->_dahdi_additional[$account] as $asetting) {
                    $output .= $asetting['key'] . "=" . $asetting['value'] . "\n";
                }
            }
            $output .= $additional ? $additional."\n" : '';
            $output .= "channel=>$zapchannel\n";
        }
        return $output;
    }
}

function core_destination_popovers() {
    global $amp_conf;
    if ($amp_conf['AMPEXTENSIONS'] == "deviceanduser") {
        $ret['users'] = _dgettext('amp','Users');
    } else {
        $ret['extensions'] = _dgettext('amp','Extensions');
    }
    return $ret;
}

// The destinations this module provides
// returns a associative arrays with keys 'destination' and 'description'
function core_destinations() {
    global $amp_conf;
    //static destinations
    $extens = array();
    //$td = textdomain();
    textdomain("amp");
    $category = __("Terminate Call");
    $ds_id = 'blackhole';
    $extens[] = array('destination' => 'app-blackhole,hangup,1', 'description' => __("Hangup"), 'category' => $category, 'id' => $ds_id);
    $extens[] = array('destination' => 'app-blackhole,congestion,1', 'description' => __("Congestion"), 'category' => $category, 'id' => $ds_id);
    $extens[] = array('destination' => 'app-blackhole,busy,1', 'description' => __("Busy"), 'category' => $category, 'id' => $ds_id);
    $extens[] = array('destination' => 'app-blackhole,zapateller,1', 'description' => __("Play SIT Tone (Zapateller)"), 'category' => $category, 'id' => $ds_id);
    $extens[] = array('destination' => 'app-blackhole,musiconhold,1', 'description' => __("Put caller on hold forever"), 'category' => $category, 'id' => $ds_id);
    $extens[] = array('destination' => 'app-blackhole,ring,1', 'description' => __("Play ringtones to caller until they hangup"), 'category' => $category, 'id' => $ds_id);
    //textdomain($td);

    //get the list of meetmes
    $results = core_users_list();

    if (isset($results) && function_exists('voicemail_getVoicemail')) {
        //get voicemail
        $uservm = voicemail_getVoicemail();
        $vmcontexts = array_keys($uservm);
        foreach ($results as $thisext) {
            $extnum = $thisext[0];
            // search vm contexts for this extensions mailbox
            foreach ($vmcontexts as $vmcontext) {
                if(isset($uservm[$vmcontext][$extnum])){
                    //$vmname = $uservm[$vmcontext][$extnum]['name'];
                    //$vmboxes[$extnum] = array($extnum, '"' . $vmname . '" <' . $extnum . '>');
                    $vmboxes[$extnum] = true;
                }
            }
        }
    }

    // return an associative array with destination and description
    // core provides both users and voicemail boxes as destinations
    if (isset($results)) {
        $cat_id = ($amp_conf['AMPEXTENSIONS'] == "deviceanduser")?'users':'extensions';
        $cat    = ($amp_conf['AMPEXTENSIONS'] == "deviceanduser")?'Users':'Extensions';
        foreach($results as $result) {
            $extens[] = array('destination' => 'from-did-direct,'.$result['0'].',1', 'description' => ' &lt;'.$result['0'].'&gt; '.$result['1'], 'category' => _dgettext('amp',$cat), 'id' => $cat_id);
            if(isset($vmboxes[$result['0']])) {
                $extens[] = array('destination' => 'ext-local,vmb'.$result['0'].',1', 'description' => '&lt;'.$result[0].'&gt; '.$result[1].' '.__('(busy)'), 'category' => _dgettext('voicemail','Voicemail'), 'id' => 'voicemail');
                $extens[] = array('destination' => 'ext-local,vmu'.$result['0'].',1', 'description' => '&lt;'.$result[0].'&gt; '.$result[1].' '.__('(unavail)'), 'category' => _dgettext('voicemail','Voicemail'), 'id' => 'voicemail');
                $extens[] = array('destination' => 'ext-local,vms'.$result['0'].',1', 'description' => '&lt;'.$result[0].'&gt; '.$result[1].' '.__('(no-msg)'), 'category' => _dgettext('voicemail','Voicemail'), 'id' => 'voicemail');
            }
        }
    }

    $trunklist = core_trunks_listbyid();
    if (is_array($trunklist)) foreach ($trunklist as $trunk) {
        switch($trunk['tech']) {
            case 'enum':
                break;
            default:
                $extens[] = array('destination' => 'ext-trunk,'.$trunk['trunkid'].',1', 'description' => $trunk['name'].' ('.$trunk['tech'].')', 'category' => __('Trunks'), 'id' => 'trunks');
                break;
        }
    }

    if (isset($extens)) {
        return $extens;
    } else {
        return null;
    }
}

function core_getdest($exten) {
    $dests[] = 'from-did-direct,'.$exten.',1';
    $dests[] = 'ext-trunk,'.$exten.',1';
    if (!function_exists('voicemail_mailbox_get')) {
        return $dests;
    }
    $box = voicemail_mailbox_get($exten);
    if ($box == null) {
        return $dests;
    }
    $dests[] = 'ext-local,vmb'.$exten.',1';
    $dests[] = 'ext-local,vmu'.$exten.',1';
    $dests[] = 'ext-local,vms'.$exten.',1';

    return $dests;
}

function core_getdestinfo($dest) {
    global $amp_conf;
    global $active_modules;

    // Check for Extension Number Destinations
    //
    if (substr(trim($dest),0,16) == 'from-did-direct,') {
        $exten = explode(',',$dest);
        $exten = $exten[1];
        $thisexten = core_users_get($exten);
        if (empty($thisexten)) {
            return array();
        } else {
            //$type = isset($active_modules['announcement']['type'])?$active_modules['announcement']['type']:'setup';
            $display = ($amp_conf['AMPEXTENSIONS'] == "deviceanduser")?'users':'extensions';
            return array('description' => sprintf(__("User Extension %s: %s"),$exten,$thisexten['name']),
                         'edit_url' => "config.php?type=setup&display=$display&extdisplay=".urlencode($exten)."&skip=0",
            );
        }

    } else if (substr(trim($dest),0,10) == 'ext-trunk,') {

        $exten = explode(',',$dest);
        $exten = $exten[1];
        $thisexten = core_trunks_getDetails($exten);
        if (empty($thisexten)) {
            return array();
        } else {
            $display = 'trunks';
            $name = isset($thisexten['name']) && $thisexten['name'] ? $thisexten['name'] : '';
            return array('description' => sprintf(__('Trunk: %s (%s)'),$name,$thisexten['tech']),
                         'edit_url' => "config.php?type=setup&display=$display&extdisplay=OUT_".urlencode($exten),
            );

        }

    // Check for voicemail box destinations
    //
    } else if (substr(trim($dest),0,12) == 'ext-local,vm') {
        $exten = explode(',',$dest);
        $exten = substr($exten[1],3);
        if (!function_exists('voicemail_mailbox_get')) {
            return array();
        }
        $thisexten = core_users_get($exten);
        if (empty($thisexten)) {
            return array();
        }
        $box = voicemail_mailbox_get($exten);
        if ($box == null) {
            return array();
        }
        $display = ($amp_conf['AMPEXTENSIONS'] == "deviceanduser")?'users':'extensions';
        return array('description' => 'User Extension '.$exten.': '.$thisexten['name'],
                     'edit_url' => "config.php?type=setup&display=$display&extdisplay=".urlencode($exten)."&skip=0",
        );

    // Check for blackhole Termination Destinations
    //
    } else if (substr(trim($dest),0,14) == 'app-blackhole,') {
        $exten = explode(',',$dest);
        $exten = $exten[1];

        switch ($exten) {
            case 'hangup':
                $description = 'Hangup';
                break;
            case 'congestion':
                $description = 'Congestion';
                break;
            case 'busy':
                $description = 'Busy';
                break;
            case 'zapateller':
                $description = 'Play SIT Tone (Zapateller)';
                break;
            case 'musiconhold':
                $description = 'Put caller on hold forever';
                break;
            case 'ring':
                $description = 'Play ringtones to caller';
                break;
            default:
                $description = false;
        }
        if ($description) {
            return array('description' => 'Core: '.$description,
                          'edit_url' => false,
            );
        } else {
            return array();
        }

    // None of the above, so not one of ours
    //
    } else {
        return false;
    }
}
/*     
    Generates dialplan for "core" components (extensions & inbound routing)
    We call this with retrieve_conf
*/
function core_do_get_config($engine) {
    global $ext;  // is this the best way to pass this?
    global $version;  // this is not the best way to pass this, this should be passetd together with $engine
    global $engineinfo;
    global $amp_conf;
    global $core_conf;
    global $chan_dahdi;
    global $chan_dahdi_loaded;
    global $astman;

    $modulename = "core";

    $callrecording = 'callrecording';
    $callrecording_uid = 'MISSING_CALLRECORDINGS';
    $getCallRecordingModInfo = module_getinfo($callrecording, MODULE_STATUS_ENABLED);
    $nt =& notifications::create($db);
    if (!isset($getCallRecordingModInfo[$callrecording]) || ($getCallRecordingModInfo[$callrecording]['status'] !== MODULE_STATUS_ENABLED)) {
        if(!$nt->exists($modulename, $callrecording_uid)) {
            $nt->add_notice($modulename, $callrecording_uid, __('Call Recording Module Not Enabled'), __('The Call Recording module is not enabled. Since this feature is required for call recording you may not be able to record calls until the module is installed and enabled.'), '', true, true);
        }
    } else {
        if($nt->exists($modulename, $callrecording_uid)) {
            $nt->delete($modulename, $callrecording_uid);
        }
    }

    switch($engine) {
        case "asterisk":

            $ast_ge_14 = version_compare($version, '1.4', 'ge');
            $ast_lt_16 = version_compare($version, '1.6', 'lt');
            $ast_lt_161 = version_compare($version, '1.6.1', 'lt');
            $ast_ge_162 = version_compare($version, '1.6.2', 'ge');
            $ast_ge_10 = version_compare($version, '10', 'ge');
            $ast_ge_18 = version_compare($version, '18', 'ge');

            if (isset($core_conf) && is_a($core_conf, "core_conf")) {
                $section = 'asterisk';
                $core_conf->addResOdbc($section, array('enabled' => 'yes'));
                $core_conf->addResOdbc($section, array('dsn' => 'asterisk'));
                $core_conf->addResOdbc($section, array('pooling' => 'no'));
                $core_conf->addResOdbc($section, array('limit' => '1'));
                $core_conf->addResOdbc($section, array('pre-connect' => 'yes'));
                $core_conf->addResOdbc($section, array('username' => $amp_conf['AMPDBUSER']));
                $core_conf->addResOdbc($section, array('password' => $amp_conf['AMPDBPASS']));

                $section = 'asteriskcdrdb';
                $core_conf->addResOdbc($section, array('enabled' => 'yes'));
                $core_conf->addResOdbc($section, array('dsn' => 'MySQL-asteriskcdrdb'));
                $core_conf->addResOdbc($section, array('pooling' => 'no'));
                $core_conf->addResOdbc($section, array('limit' => '1'));
                $core_conf->addResOdbc($section, array('pre-connect' => 'yes'));
                $core_conf->addResOdbc($section, array('username' => $amp_conf['AMPDBUSER']));
                $core_conf->addResOdbc($section, array('password' => $amp_conf['AMPDBPASS']));
            }

            // Now add to sip_general_addtional.conf
            //
            if (isset($core_conf) && is_a($core_conf, "core_conf")) {
                $core_conf->setDefaultRtp();

                $core_conf->addSipGeneral('disallow','all');
                $core_conf->addSipGeneral('allow','ulaw');
                $core_conf->addSipGeneral('allow','alaw');
                $core_conf->addSipGeneral('context','from-sip-external');
                $core_conf->addSipGeneral('callerid','Unknown');
                $core_conf->addSipGeneral('notifyringing','yes');
                if ($ast_ge_14) {
                    $core_conf->addSipGeneral('notifyhold','yes');
                    $core_conf->addSipGeneral('tos_sip','cs3');    // Recommended setting from doc/ip-tos.txt
                    $core_conf->addSipGeneral('tos_audio','ef');   // Recommended setting from doc/ip-tos.txt
                    $core_conf->addSipGeneral('tos_video','af41'); // Recommended setting from doc/ip-tos.txt
                    $core_conf->addSipGeneral('alwaysauthreject','yes');
                    $core_conf->addSipGeneral('allowguest','no');
                    if ($ast_lt_161) {
                        $core_conf->addSipGeneral('limitonpeers','yes');
                    }
                } else {
                    $core_conf->addSipGeneral('tos','0x68'); // This really doesn't do anything with astersk not running as root
                }
                $useragent = $amp_conf['SIPUSERAGENT'] . '-' . getversion() . "($version)";
                $core_conf->addSipGeneral('useragent',$useragent);
                $core_conf->addIaxGeneral('disallow','all');
                $core_conf->addIaxGeneral('allow','ulaw');
                $core_conf->addIaxGeneral('allow','alaw');
                $core_conf->addIaxGeneral('allow','gsm');
                $core_conf->addIaxGeneral('mailboxdetail','yes');
                if ($ast_ge_14) {
                    $core_conf->addIaxGeneral('tos','ef'); // Recommended setting from doc/ip-tos.txt
                }

                $fcc = new featurecode($modulename, 'blindxfer');
                $code = $fcc->getCodeActive();
                unset($fcc);
                $core_conf->addFeatureMap('blindxfer',$code);

                $fcc = new featurecode($modulename, 'atxfer');
                $code = $fcc->getCodeActive();
                unset($fcc);
                $core_conf->addFeatureMap('atxfer',$code);

                $fcc = new featurecode($modulename, 'automon');
                $code = $fcc->getCodeActive();
                unset($fcc);
                // $automon = $amp_conf['AUTOMIXMON'] && !$ast_lt_16 ? 'automixmon' : 'automon';
                if ($code != '') {
                    // was this for automixmon
                    // $core_conf->addFeatureMap($automon,$code);
                    $core_conf->addApplicationMap('apprecord', $code . ',caller,Macro,one-touch-record', true);

                    /* At this point we are not using hints since we have not found a good way to be always
                     * consistent on both sides of the channel
                     *
                     * $ext->addInclude('from-internal-additional', 'device-hints');
                     * $device_list = core_devices_list("all", 'full', true);
                     * foreach ($device_list as $device) {
                     *     if ($device['tech'] == 'sip' || $device['tech'] == 'iax2') {
                     *    $ext->add('device-hints', $code.$device['id'], '', new ext_noop("AutoMixMon Hint for: ".$device['id']));
                     *    $ext->addHint('device-hints', $code.$device['id'], "Custom:RECORDING".$device['id']);
                     *   }
                     * }
                     */
                }

                $fcc = new featurecode($modulename, 'disconnect');
                $code = $fcc->getCodeActive();
                unset($fcc);
                $core_conf->addFeatureMap('disconnect',$code);

                $fcc = new featurecode($modulename, 'pickupexten');
                $code = $fcc->getCodeActive();
                unset($fcc);
                $core_conf->addFeatureGeneral('pickupexten',$code);
            }

            // FeatureCodes
            $fcc = new featurecode($modulename, 'userlogon');
            $fc_userlogon = $fcc->getCodeActive();
            unset($fcc);

            $fcc = new featurecode($modulename, 'userlogoff');
            $fc_userlogoff = $fcc->getCodeActive();
            unset($fcc);

            $fcc = new featurecode($modulename, 'zapbarge');
            $fc_zapbarge = $fcc->getCodeActive();
            unset($fcc);

            $fcc = new featurecode($modulename, 'chanspy');
            $fc_chanspy = $fcc->getCodeActive();
            unset($fcc);

            $fcc = new featurecode($modulename, 'simu_pstn');
            $fc_simu_pstn = $fcc->getCodeActive();
            unset($fcc);

            $fcc = new featurecode($modulename, 'pickup');
            $fc_pickup = $fcc->getCodeActive();
            unset($fcc);

            // Log on / off -- all in one context
            if ($fc_userlogoff != '' || $fc_userlogon != '') {
                $ext->addInclude('from-internal-additional', 'app-userlogonoff', _dgettext('amp','User Logon Logoff')); // Add the include from from-internal

                if ($fc_userlogoff != '') {
                    $ext->add('app-userlogonoff', $fc_userlogoff, '', new ext_macro('user-logoff'));
                    $ext->add('app-userlogonoff', $fc_userlogoff, 'hook_off', new ext_hangup(''));
                }

                if ($fc_userlogon != '') {
                    $ext->add('app-userlogonoff', $fc_userlogon, '', new ext_macro('user-logon'));
                    $ext->add('app-userlogonoff', $fc_userlogon, 'hook_on_1', new ext_hangup(''));

                    $clen = strlen($fc_userlogon);
                    $fc_userlogon = "_$fc_userlogon.";
                    $ext->add('app-userlogonoff', $fc_userlogon, '', new ext_macro('user-logon,${EXTEN:'.$clen.'}'));
                    $ext->add('app-userlogonoff', $fc_userlogon, 'hook_on_2', new ext_hangup(''));
                }
            }

            // sip header functions
            $c = 'func-set-sipheader'; // Context
            $e = 's'; // Exten

            $ext->add($c,$e,'', new \ext_noop('Sip Add Header function called. Adding ${ARG1} = ${ARG2}'));
            $ext->add($c,$e,'', new \ext_set('HASH(__SIPHEADERS,${ARG1})', '${ARG2}'));
            $ext->add($c,$e,'', new \ext_return());

            /*
            * Apply a SIP Header to the call that's about to be made
            */

            $c = 'func-apply-sipheaders';

            $ext->add($c,$e,'', new \ext_noop('Applying SIP Headers to channel ${CHANNEL}'));
            $ext->add($c,$e,'', new \ext_set('TECH', '${CUT(CHANNEL,/,1)}'));
            $ext->add($c,$e,'', new \ext_set('SIPHEADERKEYS', '${HASHKEYS(SIPHEADERS)}'));
            $ext->add($c,$e,'', new \ext_while('$["${SET(sipkey=${SHIFT(SIPHEADERKEYS)})}" != ""]'));
            $ext->add($c,$e,'', new \ext_set('sipheader', '${HASH(SIPHEADERS,${sipkey})}'));
            $ext->add($c,$e,'', new \ext_execif('$["${sipheader}" = "unset" & "${TECH}" = "SIP"]','SIPRemoveHeader','${sipkey}:'));
            $ext->add($c,$e,'', new \ext_execif('$["${sipheader}" = "unset" & "${TECH}" = "PJSIP"]','Set','PJSIP_HEADER(remove,${sipkey})='));

            // rfc7462
            $ext->add($c,$e,'', new \ext_execif('$["${sipkey}" = "Alert-Info" & ${REGEX("^<[^>]*>" ${sipheader})} != 1 & ${REGEX("\;info=" ${sipheader})} != 1]', 'Set', 'sipheader=<http://127.0.0.1>\;info=${sipheader}'));
            $ext->add($c,$e,'', new \ext_execif('$["${sipkey}" = "Alert-Info" & ${REGEX("^<[^>]*>" ${sipheader})} != 1]', 'Set', 'sipheader=<http://127.0.0.1>${sipheader}'));

            $ext->add($c,$e,'', new \ext_execif('$["${TECH}" = "SIP" & "${sipheader}" != "unset"]','SIPAddHeader','${sipkey}:${sipheader}'));
            $ext->add($c,$e,'', new \ext_execif('$["${TECH}" = "PJSIP" & "${sipheader}" != "unset"]','Set','PJSIP_HEADER(add,${sipkey})=${sipheader}'));
            $ext->add($c,$e,'', new \ext_endwhile(''));
            $ext->add($c,$e,'', new \ext_return());



            /* This needs to be before outbound-routes since they can have a wild-card in them
             *
              ;------------------------------------------------------------------------
              ; [ext-local-confirm]
              ;------------------------------------------------------------------------
              ; If call confirm is being used in a ringgroup, then calls that do not require confirmation are sent
              ; to this extension instead of straight to the device.
              ;
              ; The sole purpose of sending them here is to make sure we run Macro(auto-confirm) if this
              ; extension answers the line. This takes care of clearing the database key that is used to inform
              ; other potential late comers that the extension has been answered by someone else.
              ;
              ; ALERT_INFO is deprecated in Asterisk 1.4 but still used throughout the IssabelPBX dialplan and
              ; usually set by dialparties.agi. This allows inheritance. Since no dialparties.agi here, set the
              ; header if it is set.
              ;
              ;------------------------------------------------------------------------
            */
            $context = 'ext-local-confirm';
            $ext->addInclude('from-internal-additional', $context); // Add the include from from-internal
            $exten = '_LC-.';
            $ext->add($context, $exten, '', new ext_noop_trace('IN '.$context.' with - RT: ${RT}, RG_IDX: ${RG_IDX}'));
            //dont allow inbound callers to transfer around inside the system
            $ext->add($context, $exten, '', new ext_execif('$["${DIRECTION}" = "INBOUND"]', 'Set', 'DIAL_OPTIONS=${STRREPLACE(DIAL_OPTIONS,T)}'));
            $ext->add($context, $exten,'', new ext_set('THISDIAL', '${DB(DEVICE/${EXTEN:3}/dial)}'));
            $ext->add($context, $exten,'', new ext_gotoif('$["${THISDIAL:0:5}"!="PJSIP"]', 'dial'));
            $ext->add($context, $exten,'', new ext_noop('Debug: Found PJSIP Destination ${THISDIAL}, updating with PJSIP_DIAL_CONTACTS'));
            $ext->add($context, $exten,'', new ext_set('THISDIAL', '${PJSIP_DIAL_CONTACTS(${EXTEN:3})}'));
            $ext->add($context, $exten, 'dial', new ext_dial('${THISDIAL}', '${RT},${DIAL_OPTIONS}M(auto-confirm^${RG_IDX})b(func-apply-sipheaders^s^1)'));

            /* This needs to be before outbound-routes since they can have a wild-card in them
             *
              ;------------------------------------------------------------------------
              ; [findmefollow-ringallv2]
              ;------------------------------------------------------------------------
              ; This context, to be included in from-internal, implements the PreRing part of findmefollow
              ; as well as the GroupRing part. It also communicates between the two so that if DND is set
              ; on the primary extension, and mastermode is enabled, then the other extensions will not ring
              ;
              ;------------------------------------------------------------------------
             */
            $context = 'findmefollow-ringallv2';
            $ext->addInclude('from-internal-additional', $context, _dgettext('amp','Findme Follow Ringall')); // Add the include from from-internal
            $exten = '_FMPR-.';

            $fm_dnd = $amp_conf['AST_FUNC_SHARED'] ? 'SHARED(FM_DND,${FMUNIQUE})' : 'DB(FM/DND/${FMGRP}/${FMUNIQUE})';

            $ext->add($context, $exten, '', new ext_nocdr(''));
            $ext->add($context, $exten, '', new ext_noop_trace('In FMPR ${FMGRP} with ${EXTEN:5}'));
            $ext->add($context, $exten, '', new ext_set('RingGroupMethod',''));
            $ext->add($context, $exten, '', new ext_set('USE_CONFIRMATION',''));
            $ext->add($context, $exten, '', new ext_set('RINGGROUP_INDEX',''));
            $ext->add($context, $exten, '', new ext_macro('simple-dial','${EXTEN:5},${FMREALPRERING}'));
            $ext->add($context, $exten, '', new ext_execif('$["${DIALSTATUS}" = "BUSY"]', 'Set', "$fm_dnd=DND"));
            $ext->add($context, $exten, '', new ext_noop_trace('Ending FMPR ${FMGRP} with ${EXTEN:5} and dialstatus ${DIALSTATUS}'));
            $ext->add($context, $exten, '', new ext_hangup(''));

            $exten = '_FMGL-.';
            $ext->add($context, $exten, '', new ext_nocdr(''));
            $ext->add($context, $exten, '', new ext_noop_trace('In FMGL ${FMGRP} with ${EXTEN:5}'));
            $ext->add($context, $exten, '', new ext_set('ENDLOOP', '$[${EPOCH} + ${FMPRERING} + 2]'));
            $ext->add($context, $exten, 'start', new ext_gotoif('$["${' .$fm_dnd. '}" = "DND"]','dodnd'));
            $ext->add($context, $exten, '', new ext_wait('1'));
            $ext->add($context, $exten, '', new ext_noop_trace('FMGL wait loop: ${EPOCH} / ${ENDLOOP}', 6));
            $ext->add($context, $exten, '', new ext_gotoif('$[${EPOCH} < ${ENDLOOP}]','start'));

            if ($amp_conf['AST_FUNC_SHARED']) {
                $ext->add($context, $exten, '', new ext_set($fm_dnd, ''));
            } else {
                $ext->add($context, $exten, '', new ext_dbdel($fm_dnd));
            }
            $ext->add($context, $exten, 'dodial', new ext_macro('dial','${FMGRPTIME},${DIAL_OPTIONS},${EXTEN:5}'));
            $ext->add($context, $exten, '', new ext_noop_trace('Ending FMGL ${FMGRP} with ${EXTEN:5} and dialstatus ${DIALSTATUS}'));
            $ext->add($context, $exten, '', new ext_hangup(''));
            // n+10(dodnd):
            if ($amp_conf['AST_FUNC_SHARED']) {
                $ext->add($context, $exten, 'dodnd', new ext_set($fm_dnd, ''), 'n', 10);
            } else {
                $ext->add($context, $exten, 'dodnd', new ext_dbdel($fm_dnd), 'n', 10);
            }
            $ext->add($context, $exten, '', new ext_gotoif('$["${FMPRIME}" = "FALSE"]','dodial'));
            $ext->add($context, $exten, '', new ext_noop_trace('Got DND in FMGL ${FMGRP} with ${EXTEN:5} in ${RingGroupMethod} mode, aborting'));
            $ext->add($context, $exten, '', new ext_hangup(''));


            // Call pickup using app_pickup - Note that '**xtn' is hard-coded into the GXPs and SNOMs as a number to dial
            // when a user pushes a flashing BLF.
            //
            // We need to add ringgoups to this so that if an extension is part of a ringgroup, we can try to pickup that
            // extension by trying the ringgoup which is what the pickup application is going to respond to.
            //
            // NOTICE: this may be confusing, we check if this is a BRI build of Asterisk and use dpickup instead of pickup
            //         if it is. So we simply assign the varaible $ext_pickup which one it is, and use that variable when
            //         creating all the extnesions below. So those are "$ext_pickup" on purpose!
            //
            if ($fc_pickup != '' && $ast_ge_14) {
                $ext->addInclude('from-internal-additional', 'app-pickup', _dgettext('amp','Call Pickup'));
                $fclen = strlen($fc_pickup);
                $ext_pickup = (strstr($engineinfo['raw'], 'BRI')) ? 'ext_dpickup' : 'ext_pickup';

                $fcc = new featurecode('paging', 'intercom-prefix');
                $intercom_code = $fcc->getCodeActive();
                unset($fcc);

                $picklist = '${EXTEN:'.$fclen.'}';
                $picklist .= '&${EXTEN:'.$fclen.'}@PICKUPMARK';
                $ext->add('app-pickup', "_$fc_pickup.", '', new ext_macro('user-callerid'));
                $ext->add('app-pickup', "_$fc_pickup.", '', new ext_set('PICKUP_EXTEN','${AMPUSER}'));
                $ext->add('app-pickup', "_$fc_pickup.", '', new $ext_pickup($picklist));
                $ext->add('app-pickup', "_$fc_pickup.", '', new ext_hangup(''));

                if ($intercom_code != '') {
                    $len = strlen($fc_pickup.$intercom_code);
                    $picklist  = '${EXTEN:'.$len.'}';
                    $picklist .= '&${EXTEN:'.$len.'}@PICKUPMARK';
                  $ext->add('app-pickup', "_{$fc_pickup}{$intercom_code}.", '', new ext_macro('user-callerid'));
                  $ext->add('app-pickup', "_{$fc_pickup}{$intercom_code}.", '', new ext_set('PICKUP_EXTEN','${AMPUSER}'));
                    $ext->add('app-pickup', "_{$fc_pickup}{$intercom_code}.", '', new $ext_pickup($picklist));
                    $ext->add('app-pickup', "_{$fc_pickup}{$intercom_code}.", '', new ext_hangup(''));
                }
                // In order to do call pickup in ringgroups, we will need to try the ringgoup number
                // when doing call pickup for that ringgoup so we must see who is a member of what ringgroup
                // and then generate the dialplan
                //
                $rg_members = array();
                if (function_exists('ringgroups_list')) {
                    $rg_list = ringgroups_list(true);
                    foreach ($rg_list as $item) {
                        $thisgrp = ringgroups_get($item['grpnum']);
                        $grpliststr = $thisgrp['grplist'];
                        $grplist = explode("-", $grpliststr);
                        foreach ($grplist as $exten) {
                            if (strpos($exten,"#") === false) {
                                $rg_members[$exten][] = $item['grpnum'];
                            }
                        }
                    }
                }
                // Now we have a hash of extensions and what ringgoups they are members of
                // so we need to generate the callpickup dialplan for these specific extensions
                // to try the ringgoup.
                foreach ($rg_members as $exten => $grps) {
                    $picklist  = $exten;
                    $picklist .= '&'.$exten.'@PICKUPMARK';

                    foreach ($grps as $grp) {
                        $picklist .= '&'.$grp.'@from-internal';
                        $picklist .= '&'.$grp.'@from-internal-xfer';
                        $picklist .= '&'.$grp.'@ext-group';
                    }
                    $ext->add('app-pickup', "$fc_pickup".$exten, '', new ext_macro('user-callerid'));
                    $ext->add('app-pickup', "$fc_pickup".$exten, '', new ext_set('PICKUP_EXTEN','${AMPUSER}'));
                    $ext->add('app-pickup', "$fc_pickup".$exten, '', new $ext_pickup($picklist));
                    $ext->add('app-pickup', "$fc_pickup".$exten, '', new ext_hangup(''));
                    if ($intercom_code != '') {
                      $ext->add('app-pickup', "$fc_pickup".$intercom_code.$exten, '', new ext_macro('user-callerid'));
                      $ext->add('app-pickup', "$fc_pickup".$intercom_code.$exten, '', new ext_set('PICKUP_EXTEN','${AMPUSER}'));
                        $ext->add('app-pickup', "$fc_pickup".$intercom_code.$exten, '', new $ext_pickup($picklist));
                        $ext->add('app-pickup', "$fc_pickup".$intercom_code.$exten, '', new ext_hangup(''));
                    }
                }
            } elseif ($fc_pickup != '') {
                $ext->addInclude('from-internal-additional', 'app-pickup', _dgettext('amp','Call Pickup'));
                $fclen = strlen($fc_pickup);
                $ext_pickup = (strstr($engineinfo['raw'], 'BRI')) ? 'ext_dpickup' : 'ext_pickup';

                $fcc = new featurecode('paging', 'intercom-prefix');
                $intercom_code = $fcc->getCodeActive();
                unset($fcc);


                $ext->add('app-pickup', "_$fc_pickup.", '', new ext_NoOp('Attempt to Pickup ${EXTEN:'.$fclen.'} by ${CALLERID(num)}'));
                $ext->add('app-pickup', "_$fc_pickup.", '', new $ext_pickup('${EXTEN:'.$fclen.'}'));
                $ext->add('app-pickup', "_$fc_pickup.", '', new $ext_pickup('${EXTEN:'.$fclen.'}@ext-local'));
                $ext->add('app-pickup', "_$fc_pickup.", '', new $ext_pickup('${EXTEN:'.$fclen.'}@from-internal'));
                $ext->add('app-pickup', "_$fc_pickup.", '', new $ext_pickup('${EXTEN:'.$fclen.'}@from-internal-xfer'));
                $ext->add('app-pickup', "_$fc_pickup.", '', new $ext_pickup('${EXTEN:'.$fclen.'}@from-did-direct'));
                $ext->add('app-pickup', "_$fc_pickup.", '', new $ext_pickup('FMPR-${EXTEN:'.$fclen.'}'));
                $ext->add('app-pickup', "_$fc_pickup.", '', new $ext_pickup('LC-${EXTEN:'.$fclen.'}@from-internal'));
                $ext->add('app-pickup', "_$fc_pickup.", '', new $ext_pickup('LC-${EXTEN:'.$fclen.'}@from-internal-xfer'));
                $ext->add('app-pickup', "_$fc_pickup.", '', new $ext_pickup('FMPR-${EXTEN:'.$fclen.'}@from-internal'));
                $ext->add('app-pickup', "_$fc_pickup.", '', new $ext_pickup('FMPR-${EXTEN:'.$fclen.'}@from-internal-xfer'));
                $ext->add('app-pickup', "_$fc_pickup.", '', new $ext_pickup('FMPR-${EXTEN:'.$fclen.'}@from-did-direct'));
                if ($intercom_code != '') {
                    $ext->add('app-pickup', "_{$fc_pickup}{$intercom_code}.", '', new $ext_pickup('${EXTEN:'.strlen($fc_pickup.$intercom_code).'}'));
                    $ext->add('app-pickup', "_{$fc_pickup}{$intercom_code}.", '', new $ext_pickup('${EXTEN:'.strlen($fc_pickup.$intercom_code).'}@from-internal'));
                    $ext->add('app-pickup', "_{$fc_pickup}{$intercom_code}.", '', new $ext_pickup('${EXTEN:'.strlen($fc_pickup.$intercom_code).'}@from-internal-xfer'));
                    $ext->add('app-pickup', "_{$fc_pickup}{$intercom_code}.", '', new $ext_pickup('${EXTEN:'.strlen($fc_pickup.$intercom_code).'}@from-did-direct'));
                    $ext->add('app-pickup', "_{$fc_pickup}{$intercom_code}.", '', new $ext_pickup('FMPR-${EXTEN:'.strlen($fc_pickup.$intercom_code).'}'));
                    $ext->add('app-pickup', "_{$fc_pickup}{$intercom_code}.", '', new $ext_pickup('FMPR-${EXTEN:'.strlen($fc_pickup.$intercom_code).'}@from-internal'));
                    $ext->add('app-pickup', "_{$fc_pickup}{$intercom_code}.", '', new $ext_pickup('FMPR-${EXTEN:'.strlen($fc_pickup.$intercom_code).'}@from-internal-xfer'));
                    $ext->add('app-pickup', "_{$fc_pickup}{$intercom_code}.", '', new $ext_pickup('FMPR-${EXTEN:'.strlen($fc_pickup.$intercom_code).'}@from-did-direct'));
                }
                $ext->add('app-pickup', "_$fc_pickup.", '', new ext_hangup(''));
                // In order to do call pickup in ringgroups, we will need to try the ringgoup number
                // when doing call pickup for that ringgoup so we must see who is a member of what ringgroup
                // and then generate the dialplan
                //
                $rg_members = array();
                if (function_exists('ringgroups_list')) {
                    $rg_list = ringgroups_list(true);
                    foreach ($rg_list as $item) {
                        $thisgrp = ringgroups_get($item['grpnum']);
                        $grpliststr = $thisgrp['grplist'];
                        $grplist = explode("-", $grpliststr);
                        foreach ($grplist as $exten) {
                            if (strpos($exten,"#") === false) {
                                $rg_members[$exten][] = $item['grpnum'];
                            }
                        }
                    }
                }
                // Now we have a hash of extensions and what ringgoups they are members of
                // so we need to generate the callpickup dialplan for these specific extensions
                // to try the ringgoup.
                foreach ($rg_members as $exten => $grps) {
                    $ext->add('app-pickup', "$fc_pickup".$exten, '', new $ext_pickup($exten));
                    $ext->add('app-pickup', "$fc_pickup".$exten, '', new $ext_pickup($exten.'@ext-local'));
                    $ext->add('app-pickup', "$fc_pickup".$exten, '', new $ext_pickup($exten.'@from-internal'));
                    $ext->add('app-pickup', "$fc_pickup".$exten, '', new $ext_pickup($exten.'@from-internal-xfer'));
                    $ext->add('app-pickup', "$fc_pickup".$exten, '', new $ext_pickup($exten.'@from-did-direct'));
                    $ext->add('app-pickup', "$fc_pickup".$exten, '', new $ext_pickup('LC-'.$exten.'@from-internal'));
                    $ext->add('app-pickup', "$fc_pickup".$exten, '', new $ext_pickup('LC-'.$exten.'@from-internal-xfer'));
                    $ext->add('app-pickup', "$fc_pickup".$exten, '', new $ext_pickup('FMPR-'.$exten));
                    $ext->add('app-pickup', "$fc_pickup".$exten, '', new $ext_pickup('FMPR-'.$exten.'@from-internal'));
                    $ext->add('app-pickup', "$fc_pickup".$exten, '', new $ext_pickup('FMPR-'.$exten.'@from-internal-xfer'));
                    $ext->add('app-pickup', "$fc_pickup".$exten, '', new $ext_pickup('FMPR-'.$exten.'@from-did-direct'));
                    foreach ($grps as $grp) {
                        $ext->add('app-pickup', "$fc_pickup".$exten, '', new $ext_pickup($grp.'@from-internal'));
                        $ext->add('app-pickup', "$fc_pickup".$exten, '', new $ext_pickup($grp.'@from-internal-xfer'));
                        $ext->add('app-pickup', "$fc_pickup".$exten, '', new $ext_pickup($grp.'@ext-group'));
                    }
                    $ext->add('app-pickup', "$fc_pickup".$exten, '', new ext_hangup(''));
                }
            }


            // zap barge
            if ($fc_zapbarge != '') {
                $ext->addInclude('from-internal-additional', 'app-zapbarge', _dgettext('amp','Zap Barge')); // Add the include from from-internal

                $ext->add('app-zapbarge', $fc_zapbarge, '', new ext_macro('user-callerid'));
                $ext->add('app-zapbarge', $fc_zapbarge, '', new ext_setvar('GROUP()','${CALLERID(number)}'));
                $ext->add('app-zapbarge', $fc_zapbarge, '', new ext_answer(''));
                $ext->add('app-zapbarge', $fc_zapbarge, '', new ext_wait(1));
                $ext->add('app-zapbarge', $fc_zapbarge, '', new ext_zapbarge(''));
                $ext->add('app-zapbarge', $fc_zapbarge, '', new ext_hangup(''));
            }

            // chan spy
            if ($fc_chanspy != '') {
                $ext->addInclude('from-internal-additional', 'app-chanspy', _dgettext('amp','Spy Calls')); // Add the include from from-internal
                $ext->add('app-chanspy', $fc_chanspy, '', new ext_macro('user-callerid'));
                $ext->add('app-chanspy', $fc_chanspy, '', new ext_answer(''));
                $ext->add('app-chanspy', $fc_chanspy, '', new ext_wait(1));
                $ext->add('app-chanspy', $fc_chanspy, '', new ext_read('spyee','please-enter-the&extension&number&followed_pound'));
                $ext->add('app-chanspy', $fc_chanspy, '', new ext_goto('1','${spyee}','targeted-chanspy'));
                $ext->add('app-chanspy', '_'.$fc_chanspy.'.', '', new ext_macro('user-callerid'));
                $ext->add('app-chanspy', '_'.$fc_chanspy.'.', '', new ext_answer(''));
                $ext->add('app-chanspy', '_'.$fc_chanspy.'.', '', new ext_goto('1','${EXTEN:3}','targeted-chanspy'));

                $ext->add('targeted-chanspy', '_.', '', new ext_set('TIMEOUT(absolute)','3600'));
                $ext->add('targeted-chanspy', '_.', '', new ext_answer(''));
                $ext->add('targeted-chanspy', '_.', 'once-upon-a-time', new ext_wait(1));
                $ext->add('targeted-chanspy', '_.', '', new ext_set('spy_target',''));
                $ext->add('targeted-chanspy', '_.', '', new ext_set('DEVS','${DB(AMPUSER/${EXTEN}/device)}'));
                $ext->add('targeted-chanspy', '_.', '', new ext_set('DEVS','${STRREPLACE(DEVS,&,\,)}'));
                $ext->add('targeted-chanspy', '_.', '', new ext_while('$["${SET(DEV=${POP(DEVS)})}" != ""]'));
                $ext->add('targeted-chanspy', '_.', '', new ext_noop('dev: ${DEV}'));
                $ext->add('targeted-chanspy', '_.', '', new ext_set('CHAN','${DB(DEVICE/${DEV}/dial)}'));
                $ext->add('targeted-chanspy', '_.', '', new ext_set('spy_target','${CHANNELS(${CHAN}-)}'));
                $ext->add('targeted-chanspy', '_.', '', new ext_execif('$["${spy_target}"!=""]','ExitWhile'));
                $ext->add('targeted-chanspy', '_.', '', new ext_endwhile(''));
                $ext->add('targeted-chanspy', '_.', '', new ext_execif('$["${spy_target}"!=""]','ChanSpy(${spy_target},dnqE))'));
                $ext->add('targeted-chanspy', '_.', '', new ext_hangup(''));
                //$ext->add('targeted-chanspy', '_.', '', new ext_goto('once-upon-a-time');
                $ext->add('targeted-chanspy', 'h', '', new ext_hangup(''));
                $ext->add('targeted-chanspy', 's', '', new ext_hangup(''));
                $ext->add('targeted-chanspy', 'T', '', new ext_hangup(''));

            }

            // Simulate External call. (ext-test)
            if ($fc_simu_pstn != '') {
                $ext->addInclude('from-internal-additional', 'ext-test', _dgettext('amp','Test')); // Add the include from from-internal
                $ext->add('ext-test', $fc_simu_pstn, '', new ext_macro('user-callerid'));

                if (ctype_digit($fc_simu_pstn)) {
                    $ext->add('ext-test', $fc_simu_pstn, '', new ext_goto('1', '${EXTEN}', 'from-pstn'));
                } else {
                    $ext->add('ext-test', $fc_simu_pstn, '', new ext_goto('1', 's', 'from-pstn'));
                }
                $ext->add('ext-test', 'h', '', new ext_macro('hangupcall'));
            }

            $ext->addInclude('ext-did', 'ext-did-0001'); // Add the include from from-internal
            $ext->addInclude('ext-did', 'ext-did-0002'); // Add the include from from-internal
            $ext->add('ext-did', 'foo','', new ext_noop('bar'));

            /* inbound routing extensions */
            $didlist = core_did_list();
            if(is_array($didlist)){
                $catchall = false;
                $catchall_context='ext-did-catchall';
                foreach($didlist as $item) {
                    if (trim($item['destination']) == '') {
                        continue;
                    }
                    $exten = trim($item['extension']);
                    $cidnum = trim($item['cidnum']);

                    // If the user put in just a cid number for routing, we add _. pattern to catch
                    // all DIDs with that CID number. Asterisk will complain about _. being dangerous
                    // but we don't want to limit this to just numberic as someone may be trying to
                    // route a non-numeric did
                    //
                    $cidroute = false;
                    if ($cidnum != '' && $exten == '') {
                        $exten = '_.';
                        $pricid = ($item['pricid']) ? true:false;
                        $cidroute = true;
                    } else if (($cidnum != '' && $exten != '') || ($cidnum == '' && $exten == '')) {
                        $pricid = true;
                    } else {
                        $pricid = false;
                    }
                    $context = ($pricid) ? "ext-did-0001":"ext-did-0002";

                    $exten = (($exten == "")?"s":$exten);
                    $exten = $exten.(($cidnum == "")?"":"/".$cidnum); //if a CID num is defined, add it

                    $ext->add($context, $exten, '', new ext_setvar('__DIRECTION',($amp_conf['INBOUND_NOTRANS'] ? 'INBOUND' : '')));

                    if ($cidroute) {
                        $ext->add($context, $exten, '', new ext_setvar('__FROM_DID','${EXTEN}'));
                        $ext->add($context, $exten, '', new ext_goto('1','s'));
                        $exten = "s/$cidnum";
                        $ext->add($context, $exten, '', new ext_execif('$["${FROM_DID}" = ""]','Set','__FROM_DID=${EXTEN}'));
                    } else {
                        if ($exten == 's'){
                            $ext->add($context, $exten, '', new ext_execif('$["${FROM_DID}" = ""]','Set','__FROM_DID=${EXTEN}'));
                        }else{
                            $ext->add($context, $exten, '', new ext_setvar('__FROM_DID','${EXTEN}'));
                        }
                    }
                    // always set CallerID name
                    $ext->add($context, $exten, '', new ext_set('CDR(did)','${FROM_DID}'));
                    $ext->add($context, $exten, '', new ext_execif('$[ "${CALLERID(name)}" = "" ] ','Set','CALLERID(name)=${CALLERID(num)}'));

                    // if VQA present and configured call it
                    if ($amp_conf['AST_APP_VQA'] && $amp_conf['DITECH_VQA_INBOUND']) {
                        $ext->add($context, $exten, '', new ext_vqa($amp_conf['DITECH_VQA_INBOUND']));
                    }

                    // Always set __MOHCLASS and moh.
                    if (empty($item['mohclass'])) {
                        // Should never happen
                        $item['mohclass'] = "default";
                    }
                    $ext->add($context, $exten, '', new ext_setmusiconhold($item['mohclass']));
                    $ext->add($context, $exten, '', new ext_setvar('__MOHCLASS',$item['mohclass']));

                    // If we require RINGING, signal it as soon as we enter.
                    if ($item['ringing'] === "CHECKED") {
                        $ext->add($context, $exten, '', new ext_ringing(''));
                    }
                    if ($item['delay_answer']) {
                        $ext->add($context, $exten, '', new ext_wait($item['delay_answer']));
                    }

                    if ($exten == "s") {
                        //if the exten is s, then also make a catchall for undefined DIDs
                        $catchaccount = "_.".(empty($cidnum)?"":"/".$cidnum);
                        if ($catchaccount =="_." && ! $catchall) {
                            $catchall = true;
                            $ext->add($catchall_context, $catchaccount, '', new ext_NoOp('Catch-All DID Match - Found ${EXTEN} - You probably want a DID for this.'));
                            $ext->add($catchall_context, $catchaccount, '', new ext_set('__FROM_DID', '${EXTEN}'));
                            $ext->add($catchall_context, $catchaccount, '', new ext_goto('1','s','ext-did'));
                        }
                    }
                    if ($item['privacyman'] == "1") {
                        $ext->add($context, $exten, '', new ext_macro('privacy-mgr',$item['pmmaxretries'].','.$item['pmminlength']));
                    } else {
                        // if privacymanager is used, this is not necessary as it will not let blocked/anonymous calls through
                        // otherwise, we need to save the caller presence to set it properly if we forward the call back out the pbx
                        // note - the indirect table could go away as of 1.4.20 where it is fixed so that SetCallerPres can take
                        // the raw format.
                        //
                        if ($ast_lt_16) {
                            $ext->add($context, $exten, '', new ext_setvar('__CALLINGPRES_SV','${CALLINGPRES_${CALLINGPRES}}'));
                        } else {
                            if($ast_ge_18) {
                                $ext->add($context, $exten, '', new ext_setvar('__CALLINGPRES_SV','${CALLERID(pres)}'));
                            } else {
                                $ext->add($context, $exten, '', new ext_setvar('__CALLINGPRES_SV','${CALLERPRES()}'));
                            }
                        }
                        $ext->add($context, $exten, '', new ext_setcallerpres('allowed_not_screened'));
                    }
                    if (!empty($item['alertinfo'])) {
                        $ext->add($context, $exten, '', new ext_setvar("__ALERT_INFO", str_replace(';', '\;', $item['alertinfo'])));
                    }
                    if (!empty($item['grppre'])) {
                        // $ext->add($context, $exten, '', new ext_macro('prepend-cid', $item['grppre'])); MACRO DEPRECATION
                        $ext->add($context, $exten, '', new ext_gosub('1','s','sub-prepend-cid', $item['grppre']));
                    }

                    //the goto destination
                    // destination field in 'incoming' database is backwards from what ext_goto expects
                    $goto_context = strtok($item['destination'],',');
                    $goto_exten = strtok(',');
                    $goto_pri = strtok(',');
                    $ext->add($context, $exten, 'dest-ext', new ext_goto($goto_pri,$goto_exten,$goto_context));

                }
                // If there's not a catchall, make one with an error message
                if (!$catchall) {
                    $ext->add($catchall_context, 's', '', new ext_noop("No DID or CID Match"));
                    $ext->add($catchall_context, 's', 'a2', new ext_answer(''));
                    $ext->add($catchall_context, 's', '', new ext_wait('2'));
                    $ext->add($catchall_context, 's', '', new ext_playback('ss-noservice'));
                    $ext->add($catchall_context, 's', '', new ext_sayalpha('${FROM_DID}'));
                    $ext->add($catchall_context, 's', '', new ext_hangup(''));
                    $ext->add($catchall_context, '_.', '', new ext_setvar('__FROM_DID', '${EXTEN}'));
                    $ext->add($catchall_context, '_.', '', new ext_noop('Received an unknown call with DID set to ${EXTEN}'));
                    $ext->add($catchall_context, '_.', '', new ext_goto('a2','s'));
                    $ext->add($catchall_context, 'h', '', new ext_hangup(''));
                }

            }

            // Now create macro-from-zaptel-nnn or macro-from-dahdi-nnn for each defined channel to route it to the DID routing
            // Send it to from-trunk so it is handled as other dids would be handled.
            //
            // to this point we have both zap and dahdi configuration options. At generation though they can't co-exists. If compatibility
            // mode then it's still from-zaptel, otherwise it is which ever is present. We cant use ast_with_dahdi() (chan_dadi) because
            // it is for detection with compatibility mode. We need to actually determine if chan_dahdi is present or not at this point
            //
            if (!isset($chan_dahdi_loaded)) {
                if (isset($astman) && $astman->connected()) {
                    $chan_dahdi_loaded = $astman->mod_loaded('chan_dahdi');
                }
            }
            foreach (core_dahdichandids_list() as $row) {
                $channel = $row['channel'];
                $did     = $row['did'];

                $this_context = "macro-from-dahdi-$channel";
                $ext->add($this_context, 's', '', new ext_noop('Entering '.$this_context.' with DID = ${DID} and setting to: '.$did));
                $ext->add($this_context, 's', '', new ext_setvar('__FROM_DID',$did));
                $ext->add($this_context, 's', '', new ext_goto('1',$did,'from-trunk'));
            }

            /* user extensions */
            $ext->addInclude('from-internal-additional','ext-local',_dgettext('amp','Extensions'));

            // If running in Dynamic mode, this will insert the hints through an Asterisk #exec call.
            // which require "execincludes=yes" to be set in the [options] section of asterisk.conf
            //

            $fcc = new featurecode('paging', 'intercom-prefix');
            $intercom_code = $fcc->getCodeActive();
            unset($fcc);

            $intercom_code = ($intercom_code == '') ? 'nointercom' : $intercom_code;

            $fcc = new featurecode('campon', 'toggle');
            $campon_toggle = $fcc->getCodeActive();
            unset($fcc);

            $campon_toggle = ($campon_toggle == '') ? 'nocampon' : $campon_toggle;

            // Pass the code so agi scripts like user_login_logout know to generate hints
            //
            $ext->addGlobal('INTERCOMCODE',$intercom_code);

            if ($amp_conf['DYNAMICHINTS']) {
                if ($amp_conf['USEDEVSTATE'] && function_exists('donotdisturb_get_config')) {
                    $add_dnd = 'dnd';
                } else {
                    $add_dnd = '';
                }
                $ext->addExec('ext-local',$amp_conf['AMPBIN'].'/generate_hints.php '.$intercom_code.' '.$campon_toggle .' '.$add_dnd);
            }
            $userlist = core_users_list();
            if (is_array($userlist)) {
                foreach($userlist as $item) {
                    $exten = core_users_get($item[0]);
                    $vm = ((($exten['voicemail'] == "novm") || ($exten['voicemail'] == "disabled") || ($exten['voicemail'] == "")) ? "novm" : $exten['extension']);

                    $ext->add('ext-local', $exten['extension'], '', new ext_set('__RINGTIMER', '${IF($[${DB(AMPUSER/'.$exten['extension'].'/ringtimer)} > 0]?${DB(AMPUSER/'.$exten['extension'].'/ringtimer)}:${RINGTIMER_DEFAULT})}'));

                    $dest_args = ','.($exten['noanswer_dest']==''?'0':'1').','.($exten['busy_dest']==''?'0':'1').','.($exten['chanunavail_dest']==''?'0':'1');
                    $ext->add('ext-local', $exten['extension'], '', new ext_macro('exten-vm',$vm.",".$exten['extension'].$dest_args));
                    $ext->add('ext-local', $exten['extension'], 'dest', new ext_set('__PICKUPMARK',''));
                    if ($exten['noanswer_dest']) {
                        if ($exten['noanswer_cid'] != '') {
                            $ext->add('ext-local', $exten['extension'], '', new ext_execif('$["${DIALSTATUS}"="NOANSWER"]','Set','CALLERID(name)='.$exten['noanswer_cid'].'${CALLERID(name)}'));
                        }
                        $ext->add('ext-local', $exten['extension'], '', new ext_gotoif('$["${DIALSTATUS}"="NOANSWER"]',$exten['noanswer_dest']));
                    }
                    if ($exten['busy_dest']) {
                        if ($exten['busy_cid'] != '') {
                            $ext->add('ext-local', $exten['extension'], '', new ext_execif('$["${DIALSTATUS}"="BUSY"]','Set','CALLERID(name)='.$exten['busy_cid'].'${CALLERID(name)}'));
                        }
                        $ext->add('ext-local', $exten['extension'], '', new ext_gotoif('$["${DIALSTATUS}"="BUSY"]',$exten['busy_dest']));
                    }
                    if ($exten['chanunavail_dest']) {
                        if ($exten['chanunavail_cid'] != '') {
                            $ext->add('ext-local', $exten['extension'], '', new ext_execif('$["${DIALSTATUS}"="CHANUNAVAIL"]','Set','CALLERID(name)='.$exten['chanunavail_cid'].'${CALLERID(name)}'));
                        }
                        $ext->add('ext-local', $exten['extension'], '', new ext_gotoif('$["${DIALSTATUS}"="CHANUNAVAIL"]',$exten['chanunavail_dest']));
                    }

                    if($vm != "novm") {
                        // This usually gets called from macro-exten-vm but if follow-me destination need to go this route
                        $ext->add('ext-local', $exten['extension'], '', new ext_macro('vm',$vm.',${DIALSTATUS},${IVR_RETVM}'));
                        $ext->add('ext-local', $exten['extension'], '', new ext_goto('1','vmret'));
                        $ext->add('ext-local', 'vmb'.$exten['extension'], '', new ext_macro('vm',$vm.',BUSY,${IVR_RETVM}'));
                        $ext->add('ext-local', 'vmb'.$exten['extension'], '', new ext_goto('1','vmret'));
                        $ext->add('ext-local', 'vmu'.$exten['extension'], '', new ext_macro('vm',$vm.',NOANSWER,${IVR_RETVM}'));
                        $ext->add('ext-local', 'vmu'.$exten['extension'], '', new ext_goto('1','vmret'));
                        $ext->add('ext-local', 'vms'.$exten['extension'], '', new ext_macro('vm',$vm.',NOMESSAGE,${IVR_RETVM}'));
                        $ext->add('ext-local', 'vms'.$exten['extension'], '', new ext_goto('1','vmret'));
                    } else {
                        // If we return from teh macro, it means we are suppose to return to the IVR
                        //
                        $ext->add('ext-local', $exten['extension'], '', new ext_goto('1','return','${IVR_CONTEXT}'));
                    }

                    // Create the hints if running in normal mode
                    //
                    if (!$amp_conf['DYNAMICHINTS']) {
                        $hint = core_hint_get($exten['extension']);
                        $dnd_string = ($amp_conf['USEDNDSTATE'] && function_exists('donotdisturb_get_config')) ? "&Custom:DND".$exten['extension'] : '';
                        $presence_string = $amp_conf['AST_FUNC_PRESENCE_STATE'] ? ",CustomPresence:".$exten['extension'] : '';
                        $hint_string = (!empty($hint) ? $hint : '') . $dnd_string . $presence_string;
                        if ($hint_string) {
                            $ext->addHint('ext-local', $exten['extension'], $hint_string);
                            if ($intercom_code != '') {
                                $ext->addHint('ext-local', $intercom_code.$exten['extension'], $hint_string);
                            }
                        }
                    }

                    if ($exten['sipname']) {
                        $ext->add('ext-local', $exten['sipname'], '', new ext_goto('1',$item[0],'from-internal'));
                    }
                    // Now make a special context for the IVR inclusions of local extension dialing so that
                    // when people use the Queues breakout ability, and break out to someone's extensions, voicemail
                    // works.
                    //
                    $ivr_context = 'from-did-direct-ivr';
                    $ext->add($ivr_context, $exten['extension'],'', new ext_macro('blkvm-clr'));
                    $ext->add($ivr_context, $exten['extension'],'', new ext_setvar('__NODEST', ''));
                    $ext->add($ivr_context, $exten['extension'],'', new ext_goto('1',$exten['extension'],'from-did-direct'));
                }
                $ext->add('ext-local', 'vmret', '', new ext_gotoif('$["${IVR_RETVM}" = "RETURN" & "${IVR_CONTEXT}" != ""]','playret'));
                $ext->add('ext-local', 'vmret', '', new ext_hangup(''));
                $ext->add('ext-local', 'vmret', 'playret', new ext_playback('exited-vm-will-be-transfered&silence/1'));
                $ext->add('ext-local', 'vmret', '', new ext_goto('1','return','${IVR_CONTEXT}'));

                $ext->add('ext-local', 'h', '', new ext_macro('hangupcall'));
            }

            /* Create the from-trunk-tech-chanelid context that can be used for inbound group counting
             * Create the DUNDI macros for DUNDI trunks
             * Create the ext-trunk context for direct trunk dialing TODO: should this be its own module?
             */
            $trunklist = core_trunks_listbyid();
            if (is_array($trunklist) && count($trunklist)) {

                $tcontext = 'ext-trunk';
                $texten = 'tdial';
                $tcustom = 'tcustom';
                $generate_texten = false;
                $generate_tcustom = false;

                foreach ($trunklist as $trunkprops) {
                    if (trim($trunkprops['disabled']) == 'on') {
                        continue;
                    }
                    $trunkgroup = 'OUT_'.$trunkprops['trunkid'];
                    switch ($trunkprops['tech']) {
                        case 'dundi':
                            $macro_name = 'macro-dundi-'.$trunkprops['trunkid'];
                            $ext->addSwitch($macro_name,'DUNDI/'.$trunkprops['channelid']);
                            $ext->add($macro_name, 's', '', new ext_goto('1','${ARG1}'));

                            $trunkcontext  = "from-trunk-".$trunkprops['tech']."-".$trunkprops['channelid'];
                            $ext->add($trunkcontext, '_.', '', new ext_set('GROUP()',$trunkgroup));
                            $ext->add($trunkcontext, '_.', '', new ext_goto('1','${EXTEN}','from-trunk'));

                            $ext->add($tcontext,$trunkprops['trunkid'],'',new ext_set('OUTBOUND_GROUP', $trunkgroup));
                            $ext->add($tcontext,$trunkprops['trunkid'],'',new ext_gotoif('$["${OUTMAXCHANS_'.$trunkprops['trunkid'].'}" = ""]', 'nomax'));
                            $ext->add($tcontext,$trunkprops['trunkid'],'',new ext_gotoif('$[${GROUP_COUNT('.$trunkgroup.')} >= ${OUTMAXCHANS_${DIAL_TRUNK}}]', 'hangit'));
                            if ($ast_lt_16) {
                              $ext->add($tcontext,$trunkprops['trunkid'],'nomax',new ext_execif('$["${CALLINGPRES_SV}" != ""]', 'SetCallerPres', '${CALLINGPRES_SV}'));
                            } else {
                              if($ast_ge_18) {
                                  $ext->add($tcontext,$trunkprops['trunkid'],'nomax',new ext_execif('$["${CALLINGPRES_SV}" != ""]', 'Set', 'CALLERID(pres)=${CALLINGPRES_SV}'));
                              } else {
                                  $ext->add($tcontext,$trunkprops['trunkid'],'nomax',new ext_execif('$["${CALLINGPRES_SV}" != ""]', 'Set', 'CALLERPRES()=${CALLINGPRES_SV}'));
                              }
                            }
                            $ext->add($tcontext,$trunkprops['trunkid'],'',new ext_set('DIAL_NUMBER','${FROM_DID}'));
                            $ext->add($tcontext,$trunkprops['trunkid'],'',new ext_gosubif('$["${PREFIX_TRUNK_'.$trunkprops['trunkid'].'}" != ""]','sub-flp-'.$trunkprops['trunkid'].',s,1'));
                            $ext->add($tcontext,$trunkprops['trunkid'],'',new ext_set('OUTNUM', '${OUTPREFIX_${DIAL_TRUNK}}${DIAL_NUMBER}'));  // OUTNUM is the final dial number
                            $ext->add($tcontext,$trunkprops['trunkid'],'',new ext_macro('dundi-${DIAL_TRUNK}','${OUTNUM}'));
                            $ext->add($tcontext,$trunkprops['trunkid'],'hangit',new ext_hangup());
                            break;

                        case 'iax':
                            $trunkprops['tech'] = 'iax2';
                            // fall-through
                        case 'iax2':
                        case 'pjsip':
                        case 'sip':
                            $trunkcontext  = "from-trunk-".$trunkprops['tech']."-".$trunkprops['channelid'];
                            $ext->add($trunkcontext, '_.', '', new ext_set('GROUP()',$trunkgroup));
                            $ext->add($trunkcontext, '_.', '', new ext_goto('1','${EXTEN}','from-trunk'));
                            // fall-through
                        case 'zap':
                        case 'dahdi':
                            $ext->add($tcontext, $trunkprops['trunkid'], '', new ext_set('TDIAL_STRING',strtoupper($trunkprops['tech']).'/'.$trunkprops['channelid']));
                            $ext->add($tcontext, $trunkprops['trunkid'], '', new ext_set('DIAL_TRUNK',$trunkprops['trunkid'] ));
                            $ext->add($tcontext, $trunkprops['trunkid'], '', new ext_goto('1',$texten,'ext-trunk'));
                            $generate_texten = true;
                            break;

                          // TODO we don't have the OUTNUM until later so fix this...
                        case 'custom':
                            $dial_string = str_replace('$OUTNUM$','${SS}{OUTNUM}',$trunkprops['channelid']);
                            $ext->add($tcontext, $trunkprops['trunkid'], '', new ext_set('SS','$'));
                            $ext->add($tcontext, $trunkprops['trunkid'], '', new ext_set('TDIAL_STRING',$dial_string));
                            $ext->add($tcontext, $trunkprops['trunkid'], '', new ext_set('DIAL_TRUNK',$trunkprops['trunkid'] ));
                            $ext->add($tcontext, $trunkprops['trunkid'], '', new ext_goto('1',$tcustom,'ext-trunk'));
                            $generate_tcustom = true;
                            break;

                        case 'enum':
                            // Not Supported
                            break;
                        default:
                    }
                }

                if ($generate_tcustom) {
                    $ext->add($tcontext,$tcustom,'',new ext_set('OUTBOUND_GROUP', 'OUT_${DIAL_TRUNK}'));
                    $ext->add($tcontext,$tcustom,'',new ext_gotoif('$["${OUTMAXCHANS_${DIAL_TRUNK}}" = ""]', 'nomax'));
                    $ext->add($tcontext,$tcustom,'',new ext_gotoif('$[${GROUP_COUNT(OUT_${DIAL_TRUNK})} >= ${OUTMAXCHANS_${DIAL_TRUNK}}]', 'hangit'));
                    if ($ast_lt_16) {
                      $ext->add($tcontext,$tcustom,'nomax',new ext_execif('$["${CALLINGPRES_SV}" != ""]', 'SetCallerPres', '${CALLINGPRES_SV}'));
                    } else {
                      if($ast_ge_18) {
                          $ext->add($tcontext,$tcustom,'nomax',new ext_execif('$["${CALLINGPRES_SV}" != ""]', 'Set', 'CALLERID(pres)=${CALLINGPRES_SV}'));
                      } else {
                          $ext->add($tcontext,$tcustom,'nomax',new ext_execif('$["${CALLINGPRES_SV}" != ""]', 'Set', 'CALLERPRES()=${CALLINGPRES_SV}'));
                      }
                    }
                    $ext->add($tcontext,$tcustom,'',new ext_set('DIAL_NUMBER','${FROM_DID}'));
                    $ext->add($tcontext,$tcustom,'',new ext_gosubif('$["${PREFIX_TRUNK_${DIAL_TRUNK}}" != ""]','sub-flp-${DIAL_TRUNK},s,1'));
                    $ext->add($tcontext,$tcustom,'',new ext_set('OUTNUM', '${OUTPREFIX_${DIAL_TRUNK}}${DIAL_NUMBER}'));  // OUTNUM is the final dial number

                    // Address Security Vulnerability in many earlier versions of Asterisk from an external source tranmitting a
                    // malicious CID that can cause overflows in the Asterisk code.
                    //
                    $ext->add($tcontext, $tcustom, '', new ext_set('CALLERID(number)','${CALLERID(number):0:40}'));
                    $ext->add($tcontext, $tcustom, '', new ext_set('CALLERID(name)','${CALLERID(name):0:40}'));

                    $ext->add($tcontext,$tcustom,'',new ext_set('DIAL_TRUNK_OPTIONS', '${IF($["${DB_EXISTS(TRUNK/${DIAL_TRUNK}/dialopts)}" = "1"]?${DB_RESULT}:${TRUNK_OPTIONS})}'));
                    $ext->add($tcontext,$tcustom,'',new ext_dial('${EVAL(${TDIAL_STRING})}', '${TRUNK_RING_TIMER},${DIAL_TRUNK_OPTIONS}'));
                    $ext->add($tcontext,$tcustom,'hangit',new ext_hangup());
                }

                if ($generate_texten) {
                    $ext->add($tcontext,$texten,'',new ext_set('OUTBOUND_GROUP', 'OUT_${DIAL_TRUNK}'));
                    $ext->add($tcontext,$texten,'',new ext_gotoif('$["${OUTMAXCHANS_${DIAL_TRUNK}}" = ""]', 'nomax'));
                    $ext->add($tcontext,$texten,'',new ext_gotoif('$[${GROUP_COUNT(OUT_${DIAL_TRUNK})} >= ${OUTMAXCHANS_${DIAL_TRUNK}}]', 'hangit'));
                    if ($ast_lt_16) {
                        $ext->add($tcontext,$texten,'nomax',new ext_execif('$["${CALLINGPRES_SV}" != ""]', 'SetCallerPres', '${CALLINGPRES_SV}'));
                    } else {
                      if($ast_ge_18) {
                        $ext->add($tcontext,$texten,'nomax',new ext_execif('$["${CALLINGPRES_SV}" != ""]', 'Set', 'CALLERID(pres)=${CALLINGPRES_SV}'));
                      } else {
                        $ext->add($tcontext,$texten,'nomax',new ext_execif('$["${CALLINGPRES_SV}" != ""]', 'Set', 'CALLERPRES()=${CALLINGPRES_SV}'));
                      }
                    }
                    $ext->add($tcontext,$texten,'',new ext_set('DIAL_NUMBER','${FROM_DID}'));
                    $ext->add($tcontext,$texten,'',new ext_gosubif('$["${PREFIX_TRUNK_${DIAL_TRUNK}}" != ""]','sub-flp-${DIAL_TRUNK},s,1'));
                    $ext->add($tcontext,$texten,'',new ext_set('OUTNUM', '${OUTPREFIX_${DIAL_TRUNK}}${DIAL_NUMBER}'));  // OUTNUM is the final dial number

                    $ext->add($tcontext,$texten,'',new ext_set('DIAL_TRUNK_OPTIONS', '${IF($["${DB_EXISTS(TRUNK/${DIAL_TRUNK}/dialopts)}" = "1"]?${DB_RESULT}:${TRUNK_OPTIONS})}'));
                    $ext->add($tcontext,$texten,'',new ext_dial('${TDIAL_STRING}/${OUTNUM}', '${TRUNK_RING_TIMER},${DIAL_TRUNK_OPTIONS}'));
                    // Address Security Vulnerability in many earlier versions of Asterisk from an external source tranmitting a
                    // malicious CID that can cause overflows in the Asterisk code.
                    //
                    $ext->add($tcontext, $texten, '', new ext_set('CALLERID(number)','${CALLERID(number):0:40}'));
                    $ext->add($tcontext, $texten, '', new ext_set('CALLERID(name)','${CALLERID(name):0:40}'));

                    $ext->add($tcontext,$texten,'hangit',new ext_hangup());
                }
            }


            /* dialplan globals */
            // modules should NOT use the globals table to store anything!
            // modules should use $ext->addGlobal("testvar","testval"); in their module_get_config() function instead
            // I'm cheating for core functionality - do as I say, not as I do ;-)

            // Auto add these globals to give access to agi scripts and other needs, unless defined in the global table.
            //
            $amp_conf_globals = array(
                "ASTETCDIR",
                "ASTMODDIR",
                "ASTVARLIBDIR",
                "ASTDATADIR",
                "ASTAGIDIR",
                "ASTSPOOLDIR",
                "ASTRUNDIR",
                "ASTLOGDIR",
                "CWINUSEBUSY",
                "AMPMGRUSER",
                "AMPMGRPASS",
                // Before you get upset about these being exposed to the dialplan,
                // they are ALREADY readable from /etc/amportal.conf - adding them
                // here means that AGI's and Dialplan can connect to the database
                // without resorting to re-parsing amportal.conf.
                "AMPDBENGINE",
                "AMPDBHOST",
                "AMPDBNAME",
                "AMPDBUSER",
                "AMPDBPASS",
                "AMPDBFILE",

        // Used to be globals migrated to issabelpbx_conf
        "VMX_CONTEXT",
        "VMX_PRI",
        "VMX_TIMEDEST_CONTEXT",
        "VMX_TIMEDEST_EXT",
        "VMX_TIMEDEST_PRI",
        "VMX_LOOPDEST_CONTEXT",
        "VMX_LOOPDEST_EXT",
        "VMX_LOOPDEST_PRI",
            );

            $sql = "SELECT * FROM globals";
            $globals = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
            foreach($globals as $global) {
                $value = $global['value'];

                // Ticket # 5477 Create a default value that can't be polluted
                if ($global['variable'] == 'RINGTIMER') {
                    $ext->addGlobal('RINGTIMER_DEFAULT', $value);
                    continue;
                }
                $ext->addGlobal($global['variable'],$value);

                // now if for some reason we have a variable in the global table
                // that is in our $amp_conf_globals list, then remove it so we
                // don't duplicate, the sql table will take precedence
                //
                if (array_key_exists($global['variable'],$amp_conf_globals)) {
                    $rm_keys = array_keys($amp_conf_globals,$global['variable']);
                    foreach ($rm_keys as $index) {
                        unset($amp_conf_globals[$index]);
                    }
                }
            }
            foreach ($amp_conf_globals as $global) {
                if (isset($amp_conf[$global])) {
                    $value = $amp_conf[$global];
                    if ($value === true || $value === false) {
                        $value = ($value) ? 'true':'false';
                    }
                    $ext->addGlobal($global, $value);
                    out(sprintf(__("Added to globals: %s"),"$global = $value"));
                }
            }

      // Put the MIXMON_DIR, it needs a trailing / so is special cased here
      $mixmon_dir = $amp_conf['MIXMON_DIR'] != '' ? $amp_conf['MIXMON_DIR'].'/' : '';
            $ext->addGlobal('MIXMON_DIR', $mixmon_dir);
            out(sprintf(__("Added to globals: %s"),"MIXMON_DIR = $mixmon_dir"));

            // Add some globals that are used by the dialplan
            //
            $add_globals = array(
                'MIXMON_POST' => 'MIXMON_POST',
                'DIAL_OPTIONS' => 'DIAL_OPTIONS',
                'TRUNK_OPTIONS' => 'TRUNK_OPTIONS',
                'TRUNK_RING_TIMER' => 'TRUNK_RING_TIMER',
                'MIXMON_FORMAT' => 'MIXMON_FORMAT',
                'REC_POLICY' => 'REC_POLICY',
                'RINGTIMER' => 'RINGTIMER_DEFAULT',
                'TRANSFER_CONTEXT' => 'TRANSFER_CONTEXT',
            );
            foreach ($add_globals as $g => $v) {
                $ext->addGlobal($v, $amp_conf[$g]);
                out(sprintf(__("Added to globals: %s"),"$v = ".$amp_conf[$g]));
            }
            unset($add_globals);

            // Put the asterisk version in a global for agi etc.
            $ext->addGlobal('ASTVERSION', $version);
            // Put the use of chan_dahdi in a global for dialparties
            $ext->addGlobal('ASTCHANDAHDI', $chan_dahdi ? '1' : '0');
            // Create constant NULL in globals
            $ext->addGlobal('NULL', '""');

            // Create CallingPresTable to deal with difference that ${CALINGPRES} returns vs. what
            // SetCallerPres() accepts. This is a workaround that gets resolved in 1.6 where
            // function CALLINGPRES() is consistent.
            // This should be fixed in 1.4.20 but for now we keep it in until 1.6
            //
            if ($ast_lt_16) {
                $ext->addGlobal('CALLINGPRES_0', 'allowed_not_screened');
                $ext->addGlobal('CALLINGPRES_1', 'allowed_passed_screen');
                $ext->addGlobal('CALLINGPRES_2', 'allowed_failed_screen');
                $ext->addGlobal('CALLINGPRES_3', 'allowed');
                $ext->addGlobal('CALLINGPRES_32', 'prohib_not_screened');
                $ext->addGlobal('CALLINGPRES_33', 'prohib_passed_screen');
                $ext->addGlobal('CALLINGPRES_34', 'prohib_failed_screen');
                $ext->addGlobal('CALLINGPRES_35', 'prohib');
                $ext->addGlobal('CALLINGPRES_67', 'unavailable');
            }

            // ALLOW_SIP_ANON:
            //
            // This is normally handled in the SIP Settings Module but if that module is not
            // installed as an interim we will handle it here.
            //
            if (!function_exists('sipsettings_get')) {
                $var = 'ALLOW_SIP_ANON';
                $allow_sip_anon = sql("SELECT `value` FROM `admin` WHERE `variable` = '$var'", 'getOne');
                if (empty($allow_sip_anon)) {
                    $allow_sip_anon = 'no';
                }
                $ext->addGlobal($var, $allow_sip_anon);
                out("Core set $var because Sip Settings Module not enabled");
            }

            // Now let's create the required globals for the trunks so outbound routes work. These used to
            // be stored in the globals table but are not generated by retrieve conf and pulled from the
            // trunks table
            //
            $sqlstr = "
                SELECT `trunkid`, `tech`, `outcid`, `keepcid`, `maxchans`, `failscript`, `dialoutprefix`, `channelid`, `disabled`
                FROM `trunks` ORDER BY `trunkid`
            ";
            $trunks = sql($sqlstr,"getAll",DB_FETCHMODE_ASSOC);
      $trunk_hash = core_trunks_list_dialrules();

      // $has_keepcid_cnum is used when macro-outbound-callerid is generated to determine if we need to insert the
      // final execif() statement so it is important to be set before then and here
      //
      $has_keepcid_cnum = false;
            foreach ($trunks as $trunk) {
                $tid = $trunk['trunkid'];
                $tech = strtoupper($trunk['tech']);
                if ($tech == 'IAX') {
                    $tech = 'IAX2';
                } elseif ($tech == 'ZAP' && $chan_dahdi) {
                    $tech = 'DAHDI';
                }
                if ($tech == 'CUSTOM') {
                    $ext->addGlobal('OUT_'.$tid, 'AMP:'.$trunk['channelid']);
                } else {
                    $ext->addGlobal('OUT_'.$tid, $tech."/".$trunk['channelid']);
                }
                $ext->addGlobal('OUTCID_'.$tid,      $trunk['outcid']);
                $ext->addGlobal('OUTMAXCHANS_'.$tid, $trunk['maxchans']);
                $ext->addGlobal('OUTFAIL_'.$tid,     $trunk['failscript']);
                $ext->addGlobal('OUTPREFIX_'.$tid,   $trunk['dialoutprefix']);
                $ext->addGlobal('OUTDISABLE_'.$tid,  $trunk['disabled']);
                $ext->addGlobal('OUTKEEPCID_'.$tid,  $trunk['keepcid']);
        $ext->addGlobal('FORCEDOUTCID_'.$tid, ($trunk['keepcid'] == 'all' ? $trunk['outcid'] : ""));
        if ($trunk['keepcid'] == 'cnum') {
          $has_keepcid_cnum = true;
        }

        // Generate PREFIX_TRUNK_$tid even if 0 since globals will persist and cause crashes
        if (isset($trunk_hash[$tid]) && count($trunk_hash)) {
          $patterns = $trunk_hash[$tid];
          // First, generate the global referencing how many there are
                    $ext->addGlobal("PREFIX_TRUNK_$tid",count($patterns));

          $context = 'sub-flp-'.$tid;
          $target = 'TARGET_FLP_'.$tid;
          $exten = 's';
          foreach ($patterns as $pattern) {
            $prepend = $pattern['prepend_digits'];
            $offset =  strlen(preg_replace('/(\[[^\]]*\])/','X',$pattern['match_pattern_prefix']));

            $regex_base = $pattern['match_pattern_prefix'].$pattern['match_pattern_pass'];

              // convert asterisk pattern matching into perl regular expression
            //  - two steps, use $ in place of +
            //  - next replace $ with +
            // if you don't do this, the str_replace() walks over itself
              $regex_intermediate = str_replace(
                    array(
                        'X',
                        'Z',
                        'N',
                        '.',
                        '*',
                        '+',
                    ),
                    array(
                        '[0-9]',
                        '[1-9]',
                        '[2-9]',
                        '[0-9#*\\\$]$',
                        '\\\*',
                        '\\\$',
                    ),
              $pattern['match_pattern_prefix'].$pattern['match_pattern_pass']
            );
            $regex = strtr($regex_intermediate,"$","+");

            if ($pattern['prepend_digits'] == '' && $offset == 0) {
              $ext->add($context, $exten, '', new ext_execif('$[${REGEX("^'.$regex.'$" ${DIAL_NUMBER})} = 1]','Return'));
            } else {
              $offset = $offset?':'.$offset:'';
              $ext->add($context, $exten, '', new ext_execif('$[${REGEX("^'.$regex.'$" ${DIAL_NUMBER})} = 1]','Set',$target.'='.$pattern['prepend_digits'].'${DIAL_NUMBER'.$offset.'}'));
              $ext->add($context, $exten, '', new ext_gotoif('$[${LEN(${'.$target.'})} != 0]', 'match'));
            }

          }
          $ext->add($context, $exten, '', new ext_return(''));
          $ext->add($context, $exten, 'match', new ext_set('DIAL_NUMBER','${'.$target.'}'));
          $ext->add($context, $exten, '', new ext_return(''));

        } else {
          $ext->addGlobal("PREFIX_TRUNK_$tid",'');
        }
            }

/*
; ARG1: type
;       exten, out, rg, q, conf
; ARG2: called_exten
; ARG3: action (if we know it)
;       always, never (note dontcare only applies to extensions, group, etc. must specify yes/no)
;
 */
      $context = 'sub-record-cancel';
      $exten = 's';

      $ext->add($context, $exten, '', new ext_set('__REC_POLICY_MODE', '${REC_POLICY_MODE_SAVE}'));
      $ext->add($context, $exten, '', new ext_execif('$["${REC_STATUS}"!="RECORDING"]','Return'));
      $ext->add($context, $exten, '', new ext_stopmixmonitor());
      $ext->add($context, $exten, '', new ext_set('__REC_STATUS',''));
      $ext->add($context, $exten, '', new ext_set('MON_BASE','${IF($[${LEN(${MIXMON_DIR})}]?${MIXMON_DIR}:${ASTSPOOLDIR}/monitor/)}${YEAR}/${MONTH}/${DAY}/'));
      $ext->add($context, $exten, '', new ext_set('__MON_FMT','${IF($[${LEN(${MIXMON_FORMAT})}]?${IF($["${MIXMON_FORMAT}"="wav49"]?WAV:${MIXMON_FORMAT})}:wav)}'));
      $ext->add($context, $exten, '', new ext_execif('$[${LEN(${CALLFILENAME})} & ${STAT(f,${MON_BASE}${CALLFILENAME}.${MON_FMT})}]','System','rm -f ${MON_BASE}${CALLFILENAME}.${MON_FMT}'));
      $ext->add($context, $exten, '', new ext_set('__CALLFILENAME',''));
      $ext->add($context, $exten, '', new ext_set('CDR(recordingfile)',''));
      $ext->add($context, $exten, '', new ext_return(''));


      $context = 'sub-record-check';
      $exten = 's';

      $ext->add($context, $exten, '', new ext_set('REC_POLICY_MODE_SAVE','${REC_POLICY_MODE}'));
      $ext->add($context, $exten, '', new ext_gotoif('$["${BLINDTRANSFER}" = ""]', 'check'));
      $ext->add($context, $exten, '', new ext_resetcdr(''));
      $ext->add($context, $exten, '', new ext_gotoif('$["${REC_STATUS}" != "RECORDING"]', 'check'));
      if (version_compare($amp_conf['ASTVERSION'],'11','le')) {
          $ext->add($context, $exten, '', new ext_set('AUDIOHOOK_INHERIT(MixMonitor)','yes'));
      }
      $ext->add($context, $exten, '', new ext_mixmonitor('${MIXMON_DIR}${YEAR}/${MONTH}/${DAY}/${CALLFILENAME}.${MIXMON_FORMAT}','a','${MIXMON_POST}'));
      $ext->add($context, $exten, 'check', new ext_set('__MON_FMT','${IF($["${MIXMON_FORMAT}"="wav49"]?WAV:${MIXMON_FORMAT})}'));
      $ext->add($context, $exten, '', new ext_gotoif('$["${REC_STATUS}"!="RECORDING"]', 'next'));
      $ext->add($context, $exten, '', new ext_set('CDR(recordingfile)','${CALLFILENAME}.${MON_FMT}'));
      $ext->add($context, $exten, '', new ext_return(''));
      $ext->add($context, $exten, 'next', new ext_execif('$[!${LEN(${ARG1})}]','Return'));
      $ext->add($context, $exten, '', new ext_execif('$["${REC_POLICY_MODE}"="" & "${ARG3}"!=""]','Set','__REC_POLICY_MODE=${ARG3}'));
      $ext->add($context, $exten, '', new ext_gotoif('$["${REC_STATUS}"!=""]','${ARG1},1'));
      $ext->add($context, $exten, '', new ext_set('__REC_STATUS','INITIALIZED'));
      $ext->add($context, $exten, '', new ext_set('NOW','${EPOCH}'));
      $ext->add($context, $exten, '', new ext_set('__DAY','${STRFTIME(${NOW},,%d)}'));
      $ext->add($context, $exten, '', new ext_set('__MONTH','${STRFTIME(${NOW},,%m)}'));
      $ext->add($context, $exten, '', new ext_set('__YEAR','${STRFTIME(${NOW},,%Y)}'));
      $ext->add($context, $exten, '', new ext_set('__TIMESTR','${YEAR}${MONTH}${DAY}-${STRFTIME(${NOW},,%H%M%S)}'));
      $ext->add($context, $exten, '', new ext_set('__FROMEXTEN','${IF($[${LEN(${AMPUSER})}]?${AMPUSER}:${IF($[${LEN(${REALCALLERIDNUM})}]?${REALCALLERIDNUM}:${CALLERID(num)})})}'));
      $ext->add($context, $exten, '', new ext_set('__CALLFILENAME','${ARG1}-${ARG2}-${FROMEXTEN}-${TIMESTR}-${UNIQUEID}'));
      $ext->add($context, $exten, '', new ext_goto('1','${ARG1}'));

      $exten = 'rg';
      $ext->add($context, $exten, '', new ext_noop_trace('Recording Check ${EXTEN} ${ARG2}'));
      $ext->add($context, $exten, '', new ext_gosubif('$["${REC_POLICY_MODE}"="always"]','record,1',false,'${EXTEN},${REC_POLICY_MODE},${FROMEXTEN}'));
      $ext->add($context, $exten, '', new ext_return(''));

      $exten = 'force';
      $ext->add($context, $exten, '', new ext_noop_trace('Recording Check ${EXTEN} ${ARG2}'));
      $ext->add($context, $exten, '', new ext_gosubif('$["${REC_POLICY_MODE}"="always"]','record,1',false,'${EXTEN},${REC_POLICY_MODE},${FROMEXTEN}'));
      $ext->add($context, $exten, '', new ext_return(''));

      $exten = 'q';
      $ext->add($context, $exten, '', new ext_noop_trace('Recording Check ${EXTEN} ${ARG2}'));
      $ext->add($context, $exten, '', new ext_gosubif('$["${REC_POLICY_MODE}"="always"]','recq,1',false,'${EXTEN},${ARG2},${FROMEXTEN}'));
      $ext->add($context, $exten, '', new ext_return(''));

      $exten = 'out';
      $ext->add($context, $exten, '', new ext_noop_trace('Recording Check ${EXTEN} ${ARG2}'));
      $ext->add($context, $exten, '', new ext_execif('$["${REC_POLICY_MODE}"=""]','Set','__REC_POLICY_MODE=${DB(AMPUSER/${FROMEXTEN}/recording/out/external)}'));
      $ext->add($context, $exten, '', new ext_gosubif('$["${REC_POLICY_MODE}"="always"]','record,1',false,'exten,${ARG2},${FROMEXTEN}'));
      $ext->add($context, $exten, '', new ext_return(''));

      $exten = 'exten';
      $ext->add($context, $exten, '', new ext_noop_trace('Recording Check ${EXTEN} ${ARG2}'));
      $ext->add($context, $exten, '', new ext_gotoif('$["${REC_POLICY_MODE}"!=""]','callee'));
      $ext->add($context, $exten, '', new ext_set('__REC_POLICY_MODE','${IF($[${LEN(${FROM_DID})}]?${DB(AMPUSER/${ARG2}/recording/in/external)}:${DB(AMPUSER/${ARG2}/recording/in/internal)})}'));
      /* TODO: this appears to be a bug, ARG3 should never be set. This may be in here because of on-demand recording,
       *       testing will have to tell. If it needs to be in here it probably was suppose to be REC_POLICY_MODE and
       *       that should be tried. For now remove and do some testing to flush it out.
       *
      $ext->add($context, $exten, '', new ext_execif('$[!${LEN(${ARG3})}]','Return'));
       */

      /* If callee doesn't care, then go to caller to make decision
       * Otherwise, if caller doesn't care, the go to callee to make decision
       * Otherwise, if relative priorities are equal, use the global REC_POLICY
       * Otherwise, use whomever has a higher priority
       */
      $ext->add($context, $exten, '', new ext_gotoif('$["${REC_POLICY_MODE}"="dontcare"]', 'caller'));
      // If FROM_DID is set it's external so it's always the callee policy that rules
      $ext->add($context, $exten, '', new ext_gotoif('$["${DB(AMPUSER/${FROMEXTEN}/recording/out/internal)}"="dontcare" | "${FROM_DID}"!=""]', 'callee'));

      $ext->add($context, $exten, '', new ext_execif('$[${LEN(${DB(AMPUSER/${FROMEXTEN}/recording/priority)})}]','Set','CALLER_PRI=${DB(AMPUSER/${FROMEXTEN}/recording/priority)}','Set','CALLER_PRI=0'));
      $ext->add($context, $exten, '', new ext_execif('$[${LEN(${DB(AMPUSER/${ARG2}/recording/priority)})}]','Set','CALLEE_PRI=${DB(AMPUSER/${ARG2}/recording/priority)}','Set','CALLEE_PRI=0'));
      $ext->add($context, $exten, '', new ext_gotoif('$["${CALLER_PRI}"="${CALLEE_PRI}"]', '${REC_POLICY}','${IF($[${CALLER_PRI}>${CALLEE_PRI}]?caller:callee)}'));

      $ext->add($context, $exten, 'callee', new ext_gosubif('$["${REC_POLICY_MODE}"="always"]','record,1',false,'${EXTEN},${ARG2},${FROMEXTEN}'));
      $ext->add($context, $exten, '', new ext_return(''));
      $ext->add($context, $exten, 'caller', new ext_set('__REC_POLICY_MODE','${DB(AMPUSER/${FROMEXTEN}/recording/out/internal)}'));
      $ext->add($context, $exten, '', new ext_gosubif('$["${REC_POLICY_MODE}"="always"]','record,1',false,'${EXTEN},${ARG2},${FROMEXTEN}'));
      $ext->add($context, $exten, '', new ext_return(''));

      // For confernecing we will set the variables (since the actual meetme does the recording) in case an option were to exist to do on-demand recording
      // of the conference which doesn't currenly seem like it is supported but might.
      //
      $exten = 'conf';
      $ext->add($context, $exten, '', new ext_noop_trace('Recording Check ${EXTEN} ${ARG2}'));
      $ext->add($context, $exten, '', new ext_gosub('1','recconf',false,'${EXTEN},${ARG2},${ARG2}'));
      $ext->add($context, $exten, '', new ext_return(''));

      $exten = 'page';
      $ext->add($context, $exten, '', new ext_noop_trace('Recording Check ${EXTEN} ${ARG2}'));
      $ext->add($context, $exten, '', new ext_gosubif('$["${REC_POLICY_MODE}"="always"]','recconf,1',false,'${EXTEN},${ARG2},${FROMEXTEN}'));
      $ext->add($context, $exten, '', new ext_return(''));

      $exten = 'record';
      $ext->add($context, $exten, '', new ext_noop_trace('Setting up recording: ${ARG1}, ${ARG2}, ${ARG3}'));
      if (version_compare($amp_conf['ASTVERSION'],'11','le')) {
          $ext->add($context, $exten, '', new ext_set('AUDIOHOOK_INHERIT(MixMonitor)','yes'));
      }
      $ext->add($context, $exten, '', new ext_mixmonitor('${MIXMON_DIR}${YEAR}/${MONTH}/${DAY}/${CALLFILENAME}.${MIXMON_FORMAT}','','${MIXMON_POST}'));
      $ext->add($context, $exten, '', new ext_set('__REC_STATUS','RECORDING'));
      $ext->add($context, $exten, '', new ext_set('CDR(recordingfile)','${CALLFILENAME}.${MON_FMT}'));
      $ext->add($context, $exten, '', new ext_return(''));

      /* Queue Recording Section */
      $exten = 'recq';
      $ext->add($context, $exten, '', new ext_noop_trace('Setting up recording: ${ARG1}, ${ARG2}, ${ARG3}'));
      if (version_compare($amp_conf['ASTVERSION'],'11','le')) {
          $ext->add($context, $exten, '', new ext_set('AUDIOHOOK_INHERIT(MixMonitor)','yes'));
      }
      $ext->add($context, $exten, '', new ext_set('MONITOR_FILENAME','${MIXMON_DIR}${YEAR}/${MONTH}/${DAY}/${CALLFILENAME}'));
      $ext->add($context, $exten, '', new ext_mixmonitor('${MONITOR_FILENAME}.${MIXMON_FORMAT}','${MONITOR_OPTIONS}','${MIXMON_POST}'));
      $ext->add($context, $exten, '', new ext_set('__REC_STATUS','RECORDING'));
      $ext->add($context, $exten, '', new ext_set('CDR(recordingfile)','${CALLFILENAME}.${MON_FMT}'));
      $ext->add($context, $exten, '', new ext_return(''));

      $exten = 'recconf';
      $ext->add($context, $exten, '', new ext_noop_trace('Setting up recording: ${ARG1}, ${ARG2}, ${ARG3}'));
      if ($amp_conf['ASTCONFAPP'] == 'app_confbridge' && $ast_ge_10) {
          $ext->add($context, $exten, '', new ext_set('__CALLFILENAME','${IF($[${CONFBRIDGE_INFO(parties,${ARG2})}]?${DB(RECCONF/${ARG2})}:${ARG1}-${ARG2}-${ARG3}-${TIMESTR}-${UNIQUEID})}'));
          $ext->add($context, $exten, '', new ext_execif('$[!${CONFBRIDGE_INFO(parties,${ARG2})}]','Set','DB(RECCONF/${ARG2})=${CALLFILENAME}'));
          $ext->add($context, $exten, '', new ext_set('CONFBRIDGE(bridge,record_file)','${MIXMON_DIR}${YEAR}/${MONTH}/${DAY}/${CALLFILENAME}.${MON_FMT}'));
      } else {
          // Conferencing must set the path to MIXMON_DIR explicitly since unlike other parts of Asterisk
          // Meetme does not default to the defined monitor directory.
          //
          $ext->add($context, $exten, '', new ext_set('__CALLFILENAME','${IF($[${MEETME_INFO(parties,${ARG2})}]?${DB(RECCONF/${ARG2})}:${ARG1}-${ARG2}-${ARG3}-${TIMESTR}-${UNIQUEID})}'));
          $ext->add($context, $exten, '', new ext_execif('$[!${MEETME_INFO(parties,${ARG2})}]','Set','DB(RECCONF/${ARG2})=${CALLFILENAME}'));
          $ext->add($context, $exten, '', new ext_set('MEETME_RECORDINGFILE','${IF($[${LEN(${MIXMON_DIR})}]?${MIXMON_DIR}:${ASTSPOOLDIR}/monitor/)}${YEAR}/${MONTH}/${DAY}/${CALLFILENAME}'));
          $ext->add($context, $exten, '', new ext_set('MEETME_RECORDINGFORMAT','${MIXMON_FORMAT}'));
      }
      $ext->add($context, $exten, '', new ext_execif('$["${REC_POLICY_MODE}"!="always"]','Return'));
      if ($amp_conf['ASTCONFAPP'] == 'app_confbridge' && $ast_ge_10) {
          $ext->add($context, $exten, '', new ext_set('CONFBRIDGE(bridge,record_conference)','yes'));
          $ext->add($context, $exten, '', new ext_set('CONFBRIDGE(bridge,record_file_timestamp)','no'));
      }
      $ext->add($context, $exten, '', new ext_set('__REC_STATUS','RECORDING'));
      $ext->add($context, $exten, '', new ext_set('CDR(recordingfile)','${CALLFILENAME}.${MON_FMT}'));
      $ext->add($context, $exten, '', new ext_return(''));

      /* macro-one-touch-record */

      $context = 'macro-one-touch-record';
      $exten = 's';

      $ext->add($context, $exten, '', new ext_set('ONETOUCH_REC_SCRIPT_STATUS', ''));
      $ext->add($context, $exten, '', new ext_system($amp_conf['ASTVARLIBDIR'] . '/bin/one_touch_record.php ${CHANNEL(name)}'));
      $ext->add($context, $exten, '', new ext_noop('ONETOUCH_REC_SCRIPT_STATUS: [${ONETOUCH_REC_SCRIPT_STATUS}]'));
      $ext->add($context, $exten, '', new ext_noop_trace('ONETOUCH_REC: [${ONETOUCH_REC}] REC_STATUS: [${REC_STATUS}]'));
      $ext->add($context, $exten, '', new ext_noop_trace('ONETOUCH_RECFILE: [${ONETOUCH_RECFILE}] CDR(recordingfile): [${CDR(recordingfile)}]'));
      $ext->add($context, $exten, '', new ext_execif('$["${ONETOUCH_REC}"="RECORDING"]','Playback','beep'));
      $ext->add($context, $exten, '', new ext_execif('$["${ONETOUCH_REC}"="PAUSED"]','Playback','beep&beep'));
      $ext->add($context, $exten, '', new ext_macroexit());

      /* macro-prepend-cid */
      // prepend a cid and if set to replace previous prepends, do so, otherwise stack them
      // MACRO DEPRECATION
      $mcontext = 'macro-prepend-cid';
      $exten = 's';

      if ($amp_conf['CID_PREPEND_REPLACE']) {
        $ext->add($mcontext, $exten, '', new ext_gotoif('$["${RGPREFIX}" = ""]', 'REPCID'));
        $ext->add($mcontext, $exten, '', new ext_gotoif('$["${RGPREFIX}" != "${CALLERID(name):0:${LEN(${RGPREFIX})}}"]', 'REPCID'));
        $ext->add($mcontext, $exten, '', new ext_noop_trace('Current RGPREFIX is ${RGPREFIX}....stripping from CallerID'));
        $ext->add($mcontext, $exten, '', new ext_set('CALLERID(name)', '${CALLERID(name):${LEN(${RGPREFIX})}}'));
        $ext->add($mcontext, $exten, '', new ext_set('_RGPREFIX', ''));
      }
      $ext->add($mcontext, $exten, 'REPCID', new ext_set('_RGPREFIX', '${ARG1}'));
      $ext->add($mcontext, $exten, '', new ext_set('CALLERID(name)','${RGPREFIX}${CALLERID(name)}'));

      /* sub-prepend-cid */
      // prepend a cid and if set to replace previous prepends, do so, otherwise stack them
      //
      $mcontext = 'sub-prepend-cid';
      $exten = 's';

      if ($amp_conf['CID_PREPEND_REPLACE']) {
        $ext->add($mcontext, $exten, '', new ext_gotoif('$["${RGPREFIX}" = ""]', 'REPCID'));
        $ext->add($mcontext, $exten, '', new ext_gotoif('$["${RGPREFIX}" != "${CALLERID(name):0:${LEN(${RGPREFIX})}}"]', 'REPCID'));
        $ext->add($mcontext, $exten, '', new ext_noop_trace('Current RGPREFIX is ${RGPREFIX}....stripping from CallerID'));
        $ext->add($mcontext, $exten, '', new ext_set('CALLERID(name)', '${CALLERID(name):${LEN(${RGPREFIX})}}'));
        $ext->add($mcontext, $exten, '', new ext_set('_RGPREFIX', ''));
        $ext->add($mcontext, $exten, '', new ext_return(''));
      }
      $ext->add($mcontext, $exten, 'REPCID', new ext_set('_RGPREFIX', '${ARG1}'));
      $ext->add($mcontext, $exten, '', new ext_set('CALLERID(name)','${RGPREFIX}${CALLERID(name)}'));
      $ext->add($mcontext, $exten, '', new ext_return(''));


            /* outbound routes */

            $ext->addInclude('from-internal-additional','outbound-allroutes');
            //$ext->add('outbound-allroutes', '_!', '', new ext_macro('user-callerid,SKIPTTL'));
      $ext->add('outbound-allroutes', 'foo', '', new ext_noop('bar'));
      $routes = core_routing_list();
      $trunk_table = core_trunks_listbyid();
            $trunk_type_needed = array(); // track which macros need to be generated
      $delim = $ast_lt_16 ? '|' : ',';
      foreach ($routes as $route) {
        $add_extra_pri1 = array();
        $context = 'outrt-'.$route['route_id'];
        $comment = $route['name'];
        $ext->addSectionComment($context, $comment);

        if (function_exists('timeconditions_timegroups_get_times') && $route['time_group_id'] !== null) {
          $times = timeconditions_timegroups_get_times($route['time_group_id'],true);
          if (is_array($times) && count($times)) {
            foreach ($times as $time) {
              $ext->addInclude('outbound-allroutes',$context.$delim.$time[1],$comment);
            }
          } else {
            $ext->addInclude('outbound-allroutes',$context,$comment);
          }
        } else {
          $ext->addInclude('outbound-allroutes',$context,$comment);
        }

        $patterns = core_routing_getroutepatternsbyid($route['route_id']);
        $trunks = core_routing_getroutetrunksbyid($route['route_id']);

        foreach ($patterns as $pattern) {
          // returns:
          // array('prepend_digits' => $pattern['prepend_digits'], 'dial_pattern' => $exten, 'offset' => $pos);
          //
          $fpattern = core_routing_formatpattern($pattern);
          $exten = $fpattern['dial_pattern'];
          $offset = $fpattern['offset'] == 0 ? '':':'.$fpattern['offset'];

          // This will not get called, but it fixes some things like custom-context or other possible custom uses of these
          // generated contexts that don't have an 'outbound-allroutes' wrapper around them, of course in those cases the
          // CID part of the dialplan will not get executed
          if (!isset($add_extra_pri1[$fpattern['base_pattern']])) {
            if ($route['intracompany_route'] != '') {
              $ext->add($context, $fpattern['base_pattern'], '', new ext_macro('user-callerid,LIMIT'));
            } else {
              $ext->add($context, $fpattern['base_pattern'], '', new ext_macro('user-callerid,LIMIT,EXTERNAL'));
            }
            $add_extra_pri1[$fpattern['base_pattern']] = true;
          }
          if ($fpattern['base_pattern'] != $exten) {
            if ($route['intracompany_route'] != '') {
              $ext->add($context, $exten, '', new ext_macro('user-callerid,LIMIT'));
            } else {
              $ext->add($context, $exten, '', new ext_macro('user-callerid,LIMIT,EXTERNAL'));
            }
          }
          $ext->add($context, $exten, '', new ext_noop_trace(sprintf(__('Calling Out Route: %s'),'${SET(OUTBOUND_ROUTE_NAME='.$route['name'].')}'),1));
                    if ($route['dest']) {
                        $ext->add($context, $exten, '', new ext_set("ROUTE_CIDSAVE",'${CALLERID(all)}'));
                    }

                    // Conditionally Add Divesion Header if the call was diverted
                    if ($amp_conf['DIVERSIONHEADER']) {
                        $ext->add($context, $exten, '', new ext_gosubif('$[${LEN(${FROM_DID})}>0 & "${FROM_DID}"!="s"]','sub-diversion-header,s,1'));
                    }

          // if VQA present and configured call it
          if ($amp_conf['AST_APP_VQA'] && $amp_conf['DITECH_VQA_OUTBOUND']) {
                        $ext->add($context, $exten, '', new ext_vqa($amp_conf['DITECH_VQA_OUTBOUND']));
          }

          if ($route['emergency_route'] != '') {
                        $ext->add($context, $exten, '', new ext_set("EMERGENCYROUTE",$route['emergency_route']));
          }
          if ($route['intracompany_route'] != '') {
                        $ext->add($context, $exten, '', new ext_set("INTRACOMPANYROUTE",$route['intracompany_route']));
          }
          if ($route['mohclass'] != '') {
                        $ext->add($context, $exten, '', new ext_set("MOHCLASS", '${IF($["${MOHCLASS}"=""]?'.$route['mohclass'].':${MOHCLASS})}' ));
          }
          if ($route['outcid'] != '') {
            if ($route['outcid_mode'] != '') {
                          $ext->add($context, $exten, '', new ext_execif('$["${KEEPCID}"!="TRUE" & ${LEN(${TRUNKCIDOVERRIDE})}=0]','Set','TRUNKCIDOVERRIDE='.$route['outcid']));
            } else {
                          $ext->add($context, $exten, '', new ext_execif('$["${KEEPCID}"!="TRUE" & ${LEN(${DB(AMPUSER/${AMPUSER}/outboundcid)})}=0 & ${LEN(${TRUNKCIDOVERRIDE})}=0]','Set','TRUNKCIDOVERRIDE='.$route['outcid']));
            }
          }
          $ext->add($context, $exten, '', new ext_set("_NODEST",""));
          $ext->add($context, $exten, '', new ext_gosub('1','s','sub-record-check','out,${EXTEN},'));

          $password = $route['password'];
          $TrunkOrder = 1;
          $ArrFailoverTrunks = $trunks;
          $TrunksTotal = count($trunks);
          foreach ($trunks as $trunk_id) {
            if (isset($trunk_table[$trunk_id])) {
              switch(strtolower($trunk_table[$trunk_id]['tech'])) {
                case 'dundi':
                  $trunk_macro = 'dialout-dundi';
                  break;
                case 'enum':
                  $trunk_macro = 'dialout-enum';
                  break;
                default:
                  $trunk_macro = 'dialout-trunk';
                  break;
          }
          array_shift($ArrFailoverTrunks);
          $FailoverTrunks = implode(",",$ArrFailoverTrunks);
          $ext->add($context, $exten, '', new ext_set('__TrunkOrder',$TrunkOrder++));
          $ext->add($context, $exten, '', new ext_set('__TrunksTotal',$TrunksTotal));
          $ext->add($context, $exten, '', new ext_set('__FAILOVERTRUNKS',$FailoverTrunks));
              $ext->add($context, $exten, '', new ext_macro($trunk_macro, $trunk_id . ',' . $pattern['prepend_digits'] . '${EXTEN' . $offset . '},' . $password . ',' . $trunk_table[$trunk_id]['continue']));
              $password = '';
              $trunk_type_needed['macro-' . $trunk_macro] = true;
            }
          }
                    if ($route['dest']) {
                        // Put back the saved CID since each trunk attempt screws with it and set KEEPCID since this is
                        // a form of forwarding at this point. We could use REALCALLERIDNUM but that doesn't preserve CNAM
                        // which may be wiped out and we may want it.
                        //
                        $ext->add($context, $exten, '', new ext_noop_trace('All trunks failed calling ${EXTEN}, going to destination'));
                        $ext->add($context, $exten, '', new ext_set('CALLERID(all)','${ROUTE_CIDSAVE}'));
                        $ext->add($context, $exten, '', new ext_set('_KEEPCID','TRUE'));
                        $ext->add($context, $exten, '', new ext_goto($route['dest']));
                    } else {
                        $ext->add($context, $exten, '', new ext_noop_trace('All trunks failed calling ${EXTEN}, playing default congestion'));
              $ext->add($context, $exten, '', new ext_macro("outisbusy"));
                    }
        }
        unset($add_extra_pri1);
      }

            general_generate_indications();

            // "blackhole" destinations
            $ext->add('app-blackhole', 'hangup', '', new ext_noop('Blackhole Dest: Hangup'));
            $ext->add('app-blackhole', 'hangup', '', new ext_hangup());

            $ext->add('app-blackhole', 'zapateller', '', new ext_noop('Blackhole Dest: Play SIT Tone'));
            $ext->add('app-blackhole', 'zapateller', '', new ext_answer());
            $ext->add('app-blackhole', 'zapateller', '', new ext_zapateller());
            // Should hangup ?
            // $ext->add('app-blackhole', 'zapateller', '', new ext_hangup());

            $ext->add('app-blackhole', 'musiconhold', '', new ext_noop('Blackhole Dest: Put caller on hold forever'));
            $ext->add('app-blackhole', 'musiconhold', '', new ext_answer());
            $ext->add('app-blackhole', 'musiconhold', '', new ext_musiconhold());

            $ext->add('app-blackhole', 'congestion', '', new ext_noop('Blackhole Dest: Congestion'));
            $ext->add('app-blackhole', 'congestion', '', new ext_playtones('congestion'));
            $ext->add('app-blackhole', 'congestion', '', new ext_congestion());
            $ext->add('app-blackhole', 'congestion', '', new ext_hangup());

            $ext->add('app-blackhole', 'busy', '', new ext_noop('Blackhole Dest: Busy'));
            $ext->add('app-blackhole', 'busy', '', new ext_busy());
            $ext->add('app-blackhole', 'busy', '', new ext_hangup());

            $ext->add('app-blackhole', 'ring', '', new ext_noop('Blackhole Dest: Ring'));
            $ext->add('app-blackhole', 'ring', '', new ext_answer());
            $ext->add('app-blackhole', 'ring', '', new ext_playtones('ring'));
            $ext->add('app-blackhole', 'ring', '', new ext_wait(300));
            $ext->add('app-blackhole', 'ring', '', new ext_hangup());

            if ($amp_conf['AMPBADNUMBER'] !== false) {
                $context = 'bad-number';
                $exten = '_X.';
                $ext->add($context, $exten, '', new extension('ResetCDR()'));
                $ext->add($context, $exten, '', new extension('NoCDR()'));
                $ext->add($context, $exten, '', new ext_progress());
                $ext->add($context, $exten, '', new ext_wait('1'));
                $ext->add($context, $exten, '', new ext_progress());
                $ext->add($context, $exten, '', new ext_playback('silence/1&cannot-complete-as-dialed&check-number-dial-again,noanswer'));
                $ext->add($context, $exten, '', new ext_wait('1'));
                $ext->add($context, $exten, '', new ext_congestion('20'));
                $ext->add($context, $exten, '', new ext_hangup());
            }

            if ($amp_conf['AST_FUNC_PRESENCE_STATE']) {
                $states = array(
                    'available' => 'Available',
                    'chat' => 'Chatty',
                    'away' => 'Away',
                    'dnd' => 'DND',
                    'xa' => 'Extended Away',
                    'unavailable' => 'Unavailable'
                );

                $context = 'sub-presencestate-display';

                $exten = 's';
                $ext->add($context, $exten, '', new ext_goto(1, 'state-${TOLOWER(${PRESENCE_STATE(CustomPresence:${ARG1},value)})}'));

                foreach ($states as $state => $display) {
                    $exten = 'state-' . $state;
                    $ext->add($context, $exten, '', new ext_setvar('PRESENCESTATE_DISPLAY', '(' . $display . ')'));
                    $ext->add($context, $exten, '', new ext_return(''));
                }

                // Don't display anything if presencestate is unknown (Coding bug)
                $exten = '_state-.';
                $ext->add($context, $exten, '', new ext_setvar('PRESENCESTATE_DISPLAY', ''));
                $ext->add($context, $exten, '', new ext_return(''));

                // Don't display anything if presencestate is empty (not set).
                $exten = 'state-';
                $ext->add($context, $exten, '', new ext_setvar('PRESENCESTATE_DISPLAY', ''));
                $ext->add($context, $exten, '', new ext_return(''));
            }

            /*
            ;------------------------------------------------------------------------
            ; [macro-confirm]
            ;------------------------------------------------------------------------
            ; CONTEXT:      macro-confirm
            ; PURPOSE:      added default message if none supplied
            ;
            ; Follom-Me and Ringgroups provide an option to supply a message to be
            ; played as part of the confirmation. These changes have added a default
            ; message if none is supplied.
            ;
            ;------------------------------------------------------------------------
            */
            $context = 'macro-confirm';
            $exten = 's';

            $ext->add($context, $exten, '', new ext_setvar('LOOPCOUNT','0'));
            $ext->add($context, $exten, '', new ext_setvar('__MACRO_RESULT','ABORT'));
            $ext->add($context, $exten, '', new ext_setvar('MSG1','${IF($["${ARG1}${ALT_CONFIRM_MSG}"=""]?incoming-call-1-accept-2-decline:${IF($[${LEN(${ALT_CONFIRM_MSG})}>0]?${ALT_CONFIRM_MSG}:${ARG1})})}'));
            if ($ast_ge_14) {
                $ext->add($context, $exten, 'start', new ext_background('${MSG1},m,${CHANNEL(language)},macro-confirm'));
            } else {
                $ext->add($context, $exten, 'start', new ext_background('${MSG1},m,${LANGUAGE},macro-confirm'));
            }
            $ext->add($context, $exten, '', new ext_read('INPUT', '', 1, '', '', 4));
            $ext->add($context, $exten, '', new ext_gotoif('$[${LEN(${INPUT})} > 0]', '${INPUT},1', 't,1'));

            $exten = '1';
            if ($amp_conf['AST_FUNC_SHARED']) {
                $ext->add($context, $exten, '', new ext_gotoif('$["${DB_EXISTS(RG/${ARG3}/${UNIQCHAN})}"="0" & "${SHARED(ANSWER_STATUS,${FORCE_CONFIRM})}"=""]', 'toolate,1'));
            } else {
                $ext->add($context, $exten, '', new ext_gotoif('$["${FORCE_CONFIRM}" != ""]', 'skip'));
                $ext->add($context, $exten, '', new ext_gotoif('$["${DB_EXISTS(RG/${ARG3}/${UNIQCHAN})}"="0"]', 'toolate,1'));
            }
            $ext->add($context, $exten, '', new ext_dbdel('RG/${ARG3}/${UNIQCHAN}'));
            $ext->add($context, $exten, '', new ext_macro('blkvm-clr'));
            if ($amp_conf['AST_FUNC_SHARED']) {
                $ext->add($context, $exten, '', new ext_setvar('SHARED(ANSWER_STATUS,${FORCE_CONFIRM})',''));
            }
            $ext->add($context, $exten, 'skip', new ext_setvar('__MACRO_RESULT',''));
            $ext->add($context, $exten, '', new ext_execif('$[("${MOHCLASS}"!="default") & ("${MOHCLASS}"!="")]', 'Set', 'CHANNEL(musicclass)=${MOHCLASS}'));
            $ext->add($context, $exten, 'exitopt1', new ext_macroexit());

            $exten = '2';
            $ext->add($context, $exten, '', new ext_goto(1, 'noanswer'));

            $exten = '3';
            $ext->add($context, $exten, '', new ext_saydigits('${CALLCONFIRMCID}'));
            if ($amp_conf['AST_FUNC_SHARED']) {
                $ext->add($context, $exten, '', new ext_gotoif('$["${DB_EXISTS(RG/${ARG3}/${UNIQCHAN})}"="0" & "${SHARED(ANSWER_STATUS,${FORCE_CONFIRM})}"=""]', 'toolate,1','s,start'));
            } else {
                $ext->add($context, $exten, '', new ext_gotoif('$["${DB_EXISTS(RG/${ARG3}/${UNIQCHAN})}"="0" & "${FORCE_CONFIRM}"=""]', 'toolate,1','s,start'));
            }

            $exten = 't';
            if ($amp_conf['AST_FUNC_SHARED']) {
                $ext->add($context, $exten, '', new ext_gotoif('$["${DB_EXISTS(RG/${ARG3}/${UNIQCHAN})}"="0" & "${SHARED(ANSWER_STATUS,${FORCE_CONFIRM})}"=""]', 'toolate,1'));
            } else {
                $ext->add($context, $exten, '', new ext_gotoif('$["${DB_EXISTS(RG/${ARG3}/${UNIQCHAN})}"="0" & "${FORCE_CONFIRM}"=""]', 'toolate,1'));
            }
            $ext->add($context, $exten, '', new ext_setvar('LOOPCOUNT','$[ ${LOOPCOUNT} + 1 ]'));
            $ext->add($context, $exten, '', new ext_gotoif('$[ ${LOOPCOUNT} < 5 ]', 's,start','noanswer,1'));

            $exten = '_X';
            if ($ast_ge_14) {
                $ext->add($context, $exten, '', new ext_background('invalid,m,${CHANNEL(language)},macro-confirm'));
            } else {
                $ext->add($context, $exten, '', new ext_background('invalid,m,${LANGUAGE},macro-confirm'));
            }
            if ($amp_conf['AST_FUNC_SHARED']) {
                $ext->add($context, $exten, '', new ext_gotoif('$["${DB_EXISTS(RG/${ARG3}/${UNIQCHAN})}"="0" | "${SHARED(ANSWER_STATUS,${FORCE_CONFIRM})}"=""]', 'toolate,1'));
            } else {
                $ext->add($context, $exten, '', new ext_gotoif('$["${DB_EXISTS(RG/${ARG3}/${UNIQCHAN})}"="0" & "${FORCE_CONFIRM}"=""]', 'toolate,1'));
            }
            $ext->add($context, $exten, '', new ext_setvar('LOOPCOUNT','$[ ${LOOPCOUNT} + 1 ]'));
            $ext->add($context, $exten, '', new ext_gotoif('$[ ${LOOPCOUNT} < 5 ]', 's,start','noanswer,1'));

            $exten = 'noanswer';
            $ext->add($context, $exten, '', new ext_setvar('__MACRO_RESULT','ABORT'));
            $ext->add($context, $exten, 'exitnoanswer', new ext_macroexit());

            $exten = 'toolate';
            $ext->add($context, $exten, '', new ext_setvar('MSG2','${IF($["foo${ARG2}" != "foo"]?${ARG2}:"incoming-call-no-longer-avail")}'));
            $ext->add($context, $exten, '', new ext_playback('${MSG2}'));
            $ext->add($context, $exten, '', new ext_setvar('__MACRO_RESULT','ABORT'));
            $ext->add($context, $exten, 'exittoolate', new ext_macroexit());

            $exten = 'h';
            $ext->add($context, $exten, '', new ext_macro('hangupcall'));

            /*
            ;------------------------------------------------------------------------
            ; [macro-auto-confirm]
            ;------------------------------------------------------------------------
            ; This macro is called from ext-local-confirm to auto-confirm a call so that other extensions
            ; are aware that the call has been answered.
            ;
            ;------------------------------------------------------------------------
            */
            $context = 'macro-auto-confirm';
            $exten = 's';
            $ext->add($context, $exten, '', new ext_setvar('__MACRO_RESULT',''));
            $ext->add($context, $exten, '', new ext_set('CFIGNORE',''));
            $ext->add($context, $exten, '', new ext_set('MASTER_CHANNEL(CFIGNORE)',''));
            $ext->add($context, $exten, '', new ext_set('FORWARD_CONTEXT','from-internal'));
            $ext->add($context, $exten, '', new ext_set('MASTER_CHANNEL(FORWARD_CONTEXT)','from-internal'));
            $ext->add($context, $exten, '', new ext_macro('blkvm-clr'));
            $ext->add($context, $exten, '', new ext_dbdel('RG/${ARG1}/${UNIQCHAN}'));
            $ext->add($context, $exten, '', new ext_noop_trace('DIALEDPEERNUMBER: ${DIALEDPEERNUMBER} CID: ${CALLERID(all)}'));
            if ($amp_conf['AST_FUNC_MASTER_CHANNEL'] && $amp_conf['AST_FUNC_CONNECTEDLINE']) {
                // Check that it is numeric so we don't pollute it with odd dialplan stuff like FMGL-blah from followme
                $ext->add($context, $exten, '', new ext_execif('$[!${REGEX("[^0-9]" ${DIALEDPEERNUMBER})} && "${DB(AMPUSER/${AMPUSER}/cidname)}" != ""]', 'Set', 'MASTER_CHANNEL(CONNECTEDLINE(num))=${DIALEDPEERNUMBER}'));
                $ext->add($context, $exten, '', new ext_execif('$[!${REGEX("[^0-9]" ${DIALEDPEERNUMBER})} && "${DB(AMPUSER/${AMPUSER}/cidname)}" != ""]', 'Set', 'MASTER_CHANNEL(CONNECTEDLINE(name))=${DB(AMPUSER/${DIALEDPEERNUMBER}/cidname)}'));
            }

            /*
            ;------------------------------------------------------------------------
            ; [macro-auto-blkvm]
            ;------------------------------------------------------------------------
            ; This macro is called for any extension dialed form a queue, ringgroup
            ; or followme, so that the answering extension can clear the voicemail block
            ; override allow subsequent transfers to properly operate.
            ;
            ;------------------------------------------------------------------------
            */
            $context = 'macro-auto-blkvm';
            $exten = 's';
            $ext->add($context, $exten, '', new ext_setvar('__MACRO_RESULT',''));
            $ext->add($context, $exten, '', new ext_set('CFIGNORE',''));
            $ext->add($context, $exten, '', new ext_set('MASTER_CHANNEL(CFIGNORE)',''));
            $ext->add($context, $exten, '', new ext_set('FORWARD_CONTEXT','from-internal'));
            $ext->add($context, $exten, '', new ext_set('MASTER_CHANNEL(FORWARD_CONTEXT)','from-internal'));
            $ext->add($context, $exten, '', new ext_macro('blkvm-clr'));
            $ext->add($context, $exten, '', new ext_noop_trace('DIALEDPEERNUMBER: ${DIALEDPEERNUMBER} CID: ${CALLERID(all)}'));
            if ($amp_conf['AST_FUNC_MASTER_CHANNEL'] && $amp_conf['AST_FUNC_CONNECTEDLINE']) {
                // Check that it is numeric so we don't pollute it with odd dialplan stuff like FMGL-blah from followme
                $ext->add($context, $exten, '', new ext_execif('$[!${REGEX("[^0-9]" ${DIALEDPEERNUMBER})} && "${DB(AMPUSER/${AMPUSER}/cidname)}" != ""]', 'Set', 'MASTER_CHANNEL(CONNECTEDLINE(num))=${DIALEDPEERNUMBER}'));
                $ext->add($context, $exten, '', new ext_execif('$[!${REGEX("[^0-9]" ${DIALEDPEERNUMBER})} && "${DB(AMPUSER/${AMPUSER}/cidname)}" != ""]', 'Set', 'MASTER_CHANNEL(CONNECTEDLINE(name))=${DB(AMPUSER/${DIALEDPEERNUMBER}/cidname)}'));
            }

            /*
            ;------------------------------------------------------------------------
            ; [sub-pincheck]
            ;------------------------------------------------------------------------
            ; This subroutine checks the pincode and then resets the CDR from that point
            ; if the pincode passes. This way the billsec and duration fields are set
            ; properly for pin dialing.
            ;
            ; ${ARG3} is the pincode if this was called, used by dialout-trunk, dialout-enum
            ; and dialout-dundi
            ;
            ;------------------------------------------------------------------------
            */
            $context = 'sub-pincheck';
            $exten = 's';
            $ext->add($context, $exten, '', new ext_authenticate('${ARG3}'));
            $ext->add($context, $exten, '', new ext_resetcdr(''));
            $ext->add($context, $exten, '', new ext_return(''));

            // Subroutine to add diversion header with reason code "no-answer" unless provided differently elsewhere in the dialplan to indicate
            // the reason for the diversion (e.g. CFB could set it to busy)
            //
            if ($amp_conf['DIVERSIONHEADER']) {
                $context = 'sub-diversion-header';
                $exten = 's';
                $ext->add($context, $exten, '', new ext_set('DIVERSION_REASON', '${IF($[${LEN(${DIVERSION_REASON})}=0]?no-answer:${DIVERSION_REASON})}'));
                $ext->add($context, $exten, '', new ext_sipaddheader('Diversion', '<tel:${FROM_DID}>\;reason=${DIVERSION_REASON}\;screen=no\;privacy=off'));
                $ext->add($context, $exten, '', new ext_return(''));
            }

            /*
             * dialout using a trunk, using pattern matching (don't strip any prefix)
             * arg1 = trunk number, arg2 = number, arg3 = route password
             *
             * MODIFIED (PL)
             *
             * Modified both Dial() commands to include the new TRUNK_OPTIONS from the general
             * screen of AMP
             */
            if (function_exists('outroutemsg_get')) {
                $trunkreportmsg_ids = outroutemsg_get();
            } else {
                if (!defined('DEFAULT_MSG')) define('DEFAULT_MSG', -1);
                if (!defined('CONGESTION_TONE')) define('CONGESTION_TONE', -2);
                $trunkreportmsg_ids = array('no_answer_msg_id' => -1, 'invalidnmbr_msg_id' => -1);
            }

            // Since rarely used only generate this dialplan if are using this feature
            //
            $generate_trunk_monitor_failure = false;
            foreach ($trunk_table as $tid => $tdetails) {
                // assign and if true no need to continue
                if ($generate_trunk_monitor_failure = $generate_trunk_monitor_failure || $tdetails['failscript']) {
                    break;
                }
            }

            $context = 'macro-dialout-trunk';
            if (!empty($trunk_type_needed[$context])) {
            $exten = 's';
            $ext->add($context, $exten, '', new ext_set('DIAL_TRUNK', '${ARG1}'));
            $ext->add($context, $exten, '', new ext_execif('$["${DIRECTION}" = "INBOUND"]', 'Set', 'DIAL_OPTIONS=${STRREPLACE(DIAL_OPTIONS,T)}'));
            $ext->add($context, $exten, '', new ext_gosubif('$[$["${ARG3}" != ""] & $["${DB(AMPUSER/${AMPUSER}/pinless)}" != "NOPASSWD"]]','sub-pincheck,s,1'));
            $ext->add($context, $exten, '', new ext_gotoif('$["x${OUTDISABLE_${DIAL_TRUNK}}" = "xon"]', 'disabletrunk,1'));
            $ext->add($context, $exten, '', new ext_set('DIAL_NUMBER', '${ARG2}')); // fixlocalprefix depends on this
            $ext->add($context, $exten, '', new ext_set('DIAL_TRUNK_OPTIONS', '${DIAL_OPTIONS}')); // will be reset to TRUNK_OPTIONS if not intra-company
            $ext->add($context, $exten, '', new ext_set('OUTBOUND_GROUP', 'OUT_${DIAL_TRUNK}'));
            $ext->add($context, $exten, '', new ext_gotoif('$["${OUTMAXCHANS_${DIAL_TRUNK}}foo" = "foo"]', 'nomax'));
            $ext->add($context, $exten, '', new ext_gotoif('$[ ${GROUP_COUNT(OUT_${DIAL_TRUNK})} >= ${OUTMAXCHANS_${DIAL_TRUNK}} ]', 'chanfull'));
            $ext->add($context, $exten, 'nomax', new ext_gotoif('$["${INTRACOMPANYROUTE}" = "YES"]', 'skipoutcid'));  // Set to YES if treated like internal
            $ext->add($context, $exten, '', new ext_set('DIAL_TRUNK_OPTIONS', '${IF($["${DB_EXISTS(TRUNK/${DIAL_TRUNK}/dialopts)}" = "1"]?${DB_RESULT}:${TRUNK_OPTIONS})}'));
            $ext->add($context, $exten, '', new ext_macro('outbound-callerid', '${DIAL_TRUNK}'));
            $ext->add($context, $exten, 'skipoutcid', new ext_gosubif('$["${PREFIX_TRUNK_${DIAL_TRUNK}}" != ""]','sub-flp-${DIAL_TRUNK},s,1'));  // this sets DIAL_NUMBER to the proper dial string for this trunk
            $ext->add($context, $exten, '', new ext_set('OUTNUM', '${OUTPREFIX_${DIAL_TRUNK}}${DIAL_NUMBER}'));  // OUTNUM is the final dial number
            $ext->add($context, $exten, '', new ext_set('custom', '${CUT(OUT_${DIAL_TRUNK},:,1)}'));  // Custom trunks are prefixed with "AMP:"

            // Back to normal processing, whether intracompany or not.
            // But add the macro-setmusic if we don't want music on this outbound call
            // if FORCE_CONFIRM then that macro will set any necessary MOHCLASS, and we will also call the confirm macro
            $ext->add($context, $exten, '', new ext_execif('$["${MOHCLASS}"!="default" & "${MOHCLASS}"!="" & "${FORCE_CONFIRM}"="" ]', 'Set', 'DIAL_TRUNK_OPTIONS=M(setmusic^${MOHCLASS})${DIAL_TRUNK_OPTIONS}'));
            $ext->add($context, $exten, '', new ext_execif('$["${FORCE_CONFIRM}"!="" ]', 'Set', 'DIAL_TRUNK_OPTIONS=${DIAL_TRUNK_OPTIONS}M(confirm)'));

            // This macro call will always be blank and is provided as a hook for customization required prior to making a call
            // such as adding SIP header information or other requirements. All the channel variables from above are present

            $ext->add($context, $exten, 'gocall', new ext_macro('dialout-trunk-predial-hook'));
            $ext->add($context, $exten, '', new ext_gotoif('$["${PREDIAL_HOOK_RET}" = "BYPASS"]', 'bypass,1'));

            if ($amp_conf['AST_FUNC_CONNECTEDLINE'] && $amp_conf['OUTBOUND_DIAL_UPDATE']) {
                $ext->add($context, $exten, '', new ext_execif('$["${DB(AMPUSER/${AMPUSER}/cidname)}" != ""]','Set','CONNECTEDLINE(num,i)=${DIAL_NUMBER}'));
            }
            if ($amp_conf['AST_FUNC_CONNECTEDLINE'] && $amp_conf['OUTBOUND_CID_UPDATE']) {
                $ext->add($context, $exten, '', new ext_execif('$["${DB(AMPUSER/${AMPUSER}/cidname)}" != ""]','Set','CONNECTEDLINE(name,i)=CID:${CALLERID(number)}'));
            }

            $ext->add($context, $exten, '', new ext_gotoif('$["${custom}" = "AMP"]', 'customtrunk'));
            $ext->add($context, $exten, '', new ext_execif('$["${DIRECTION}" = "INBOUND"]', 'Set', 'DIAL_TRUNK_OPTIONS=${STRREPLACE(DIAL_TRUNK_OPTIONS,T)}'));

            $ext->add($context, $exten, '', new ext_set('DIALSTR', '${OUT_${DIAL_TRUNK}}/${OUTNUM}')); 
            $ext->add($context, $exten, '', new ext_gosubif('$["${DIALSTR:0:5}" = "PJSIP"]','pjsipdial,1'));
            $ext->add($context, $exten, '', new ext_dial('${DIALSTR}', '${TRUNK_RING_TIMER},${DIAL_TRUNK_OPTIONS}'));  // Regular Trunk Dial
            $ext->add($context, $exten, '', new ext_noop('Dial failed for some reason with DIALSTATUS = ${DIALSTATUS} and HANGUPCAUSE = ${HANGUPCAUSE}'));
            $ext->add($context, $exten, '', new ext_gotoif('$["${ARG4}" = "on"]','continue,1', 's-${DIALSTATUS},1'));

            $ext->add($context, $exten, 'customtrunk', new ext_set('pre_num', '${CUT(OUT_${DIAL_TRUNK},$,1)}'));
            $ext->add($context, $exten, '', new ext_set('the_num', '${CUT(OUT_${DIAL_TRUNK},$,2)}'));  // this is where we expect to find string OUTNUM
            $ext->add($context, $exten, '', new ext_set('post_num', '${CUT(OUT_${DIAL_TRUNK},$,3)}'));
            $ext->add($context, $exten, '', new ext_gotoif('$["${the_num}" = "OUTNUM"]', 'outnum', 'skipoutnum'));  // if we didn't find "OUTNUM", then skip to Dial
            $ext->add($context, $exten, 'outnum', new ext_set('the_num', '${OUTNUM}'));  // replace "OUTNUM" with the actual number to dial
            $ext->add($context, $exten, 'skipoutnum', new ext_dial('${pre_num:4}${the_num}${post_num}', '${TRUNK_RING_TIMER},${DIAL_TRUNK_OPTIONS}'));
            $ext->add($context, $exten, '', new ext_noop('Dial failed for some reason with DIALSTATUS = ${DIALSTATUS} and HANGUPCAUSE = ${HANGUPCAUSE}'));
            $ext->add($context, $exten, '', new ext_gotoif('$["${ARG4}" = "on"]','continue,1', 's-${DIALSTATUS},1'));
            $ext->add($context, $exten, 'chanfull', new ext_noop('max channels used up'));

            $exten = "pjsipdial";
            $ext->add($context,$exten,'', new ext_set('PJ', '${CUT(DIALSTR,/,2)}'));
            $ext->add($context,$exten,'', new ext_set('DIALSTR', 'PJSIP/${OUTNUM}@${PJ}'));
            $ext->add($context,$exten,'', new ext_execif('$["${THISDIAL}" = ""]','Set','THISDIAL=PJSIP/${PJ}'));
            $ext->add($context,$exten,'', new ext_return(''));


            $exten = 's-BUSY';
            /*
            * HANGUPCAUSE 17 = Busy, or SIP 486 Busy everywhere
            */
            $ext->add($context, $exten, '', new ext_noop('Dial failed due to trunk reporting BUSY - giving up'));
            $ext->add($context, $exten, '', new ext_playtones('busy'));
            $ext->add($context, $exten, '', new ext_busy(20));

            /*
            * There are reported bugs in Asterisk Blind Trasfers that result in Dial() returning and continuing
            * execution with a status of ANSWER. So we hangup at this point
            */
            $exten = 's-ANSWER';
            $ext->add($context, $exten, '', new ext_noop('Call successfully answered - Hanging up now'));
            $ext->add($context, $exten, '', new ext_macro('hangupcall'));

            $exten = 's-NOANSWER';
            /*
            * HANGUPCAUSE 18 = No User Responding, or SIP 408 Request Timeout
            * HANGUPCAUSE 19 = No Answer From The User, or SIP 480 Temporarily unavailable, SIP 483 To many hops
            */
            $ext->add($context, $exten, '', new ext_noop('Dial failed due to trunk reporting NOANSWER - giving up'));
            $ext->add($context, $exten, '', new ext_progress());
            switch ($trunkreportmsg_ids['no_answer_msg_id']) {
              case DEFAULT_MSG:
                  $ext->add($context, $exten, '', new ext_playback('number-not-answering,noanswer'));
                  break;
              case CONGESTION_TONE:
                  $ext->add($context, $exten, '', new ext_playtones('congestion'));
                  break;
              default:
                $message = recordings_get_file($trunkreportmsg_ids['no_answer_msg_id']);
                $message = ($message != "") ? $message : "number-not-answering";
                $ext->add($context, $exten, '', new ext_playback("$message, noanswer"));
            }
            $ext->add($context, $exten, '', new ext_congestion(20));

            $exten = 's-INVALIDNMBR';
            /*
            * HANGUPCAUSE 28 = Address Incomplete, or SIP 484 Address Incomplete
            */
            $ext->add($context, $exten, '', new ext_noop('Dial failed due to trunk reporting Address Incomplete - giving up'));
            $ext->add($context, $exten, '', new ext_progress());
            switch ($trunkreportmsg_ids['invalidnmbr_msg_id']) {
            case DEFAULT_MSG:
                $ext->add($context, $exten, '', new ext_playback('ss-noservice,noanswer'));
                break;
            case CONGESTION_TONE:
                $ext->add($context, $exten, '', new ext_playtones('congestion'));
                break;
            default:
                $message = recordings_get_file($trunkreportmsg_ids['invalidnmbr_msg_id']);
                $message = ($message != "") ? $message : "ss-noservice";
                $ext->add($context, $exten, '', new ext_playback("$message, noanswer"));
            }
            $ext->add($context, $exten, '', new ext_busy(20));

            $exten = "s-CHANGED";
            $ext->add($context, $exten, '', new ext_noop('Dial failed due to trunk reporting Number Changed - giving up'));
            $ext->add($context, $exten, '', new ext_playtones('busy'));
            $ext->add($context, $exten, '', new ext_busy(20));

            $exten = '_s-.';
            $ext->add($context, $exten, '', new ext_set('RC', '${IF($[${ISNULL(${HANGUPCAUSE})}]?0:${HANGUPCAUSE})}'));
            $ext->add($context, $exten, '', new ext_goto('1','${RC}'));

            $ext->add($context, '17', '', new ext_goto('1','s-BUSY'));
            $ext->add($context, '18', '', new ext_goto('1','s-NOANSWER'));
            $ext->add($context, '22', '', new ext_goto('1','s-CHANGED'));
            $ext->add($context, '23', '', new ext_goto('1','s-CHANGED'));
            $ext->add($context, '28', '', new ext_goto('1','s-INVALIDNMBR'));
            $ext->add($context, '_X', '', new ext_goto('1','continue'));
            $ext->add($context, '_X.', '', new ext_goto('1','continue'));

            $exten = 'continue';
            if ($generate_trunk_monitor_failure) {
                $ext->add($context, $exten, '', new ext_gotoif('$["${OUTFAIL_${ARG1}}" = ""]', 'noreport'));
                $ext->add($context, $exten, '', new ext_agi('${OUTFAIL_${ARG1}}'));
            }
            $ext->add($context, $exten, 'noreport', new ext_noop('TRUNK Dial failed due to ${DIALSTATUS} HANGUPCAUSE: ${HANGUPCAUSE} - failing through to other trunks'));
            $ext->add($context, $exten, '', new ext_set('CALLERID(number)', '${AMPUSER}'));

            $ext->add($context, 'disabletrunk', '', new ext_noop('TRUNK: ${OUT_${DIAL_TRUNK}} DISABLED - falling through to next trunk'));
            $ext->add($context, 'bypass', '', new ext_noop('TRUNK: ${OUT_${DIAL_TRUNK}} BYPASSING because dialout-trunk-predial-hook'));

            $ext->add($context, 'h', '', new ext_macro('hangupcall'));
            } // if trunk_type_needed


            $context = 'macro-dialout-dundi';
            if (!empty($trunk_type_needed[$context])) {
            $exten = 's';

            /*
             * Dialout Dundi Trunk
             */
            $ext->add($context, $exten, '', new ext_set('DIAL_TRUNK', '${ARG1}'));
            $ext->add($context, $exten, '', new ext_gosubif('$[$["${ARG3}" != ""] & $["${DB(AMPUSER/${AMPUSER}/pinless)}" != "NOPASSWD"]]','sub-pincheck,s,1'));
            $ext->add($context, $exten, '', new ext_gotoif('$["x${OUTDISABLE_${DIAL_TRUNK}}" = "xon"]', 'disabletrunk,1'));
            $ext->add($context, $exten, '', new ext_set('DIAL_NUMBER', '${ARG2}')); // fixlocalprefix depends on this
            $ext->add($context, $exten, '', new ext_set('DIAL_TRUNK_OPTIONS', '${DIAL_OPTIONS}')); // will be reset to TRUNK_OPTIONS if not intra-company
            $ext->add($context, $exten, '', new ext_set('OUTBOUND_GROUP', 'OUT_${DIAL_TRUNK}'));
            $ext->add($context, $exten, '', new ext_gotoif('$["${OUTMAXCHANS_${DIAL_TRUNK}}foo" = "foo"]', 'nomax'));
            $ext->add($context, $exten, '', new ext_gotoif('$[ ${GROUP_COUNT(OUT_${DIAL_TRUNK})} >= ${OUTMAXCHANS_${DIAL_TRUNK}} ]', 'chanfull'));
            $ext->add($context, $exten, 'nomax', new ext_gotoif('$["${INTRACOMPANYROUTE}" = "YES"]', 'skipoutcid'));  // Set to YES if treated like internal
            $ext->add($context, $exten, '', new ext_set('DIAL_TRUNK_OPTIONS', '${IF($["${DB_EXISTS(TRUNK/${DIAL_TRUNK}/dialopts)}" = "1"]?${DB_RESULT}:${TRUNK_OPTIONS})}'));
            $ext->add($context, $exten, '', new ext_macro('outbound-callerid', '${DIAL_TRUNK}'));
            $ext->add($context, $exten, 'skipoutcid', new ext_gosubif('$["${PREFIX_TRUNK_${DIAL_TRUNK}}" != ""]','sub-flp-${DIAL_TRUNK},s,1'));  // manipulate DIAL_NUMBER
            $ext->add($context, $exten, '', new ext_set('OUTNUM', '${OUTPREFIX_${DIAL_TRUNK}}${DIAL_NUMBER}'));  // OUTNUM is the final dial number

            // Back to normal processing, whether intracompany or not.
            // But add the macro-setmusic if we don't want music on this outbound call
            $ext->add($context, $exten, '', new ext_execif('$["${MOHCLASS}"!="default" & "${MOHCLASS}"!="" & "${FORCE_CONFIRM}"="" ]', 'Set', 'DIAL_TRUNK_OPTIONS=M(setmusic^${MOHCLASS})${DIAL_TRUNK_OPTIONS}'));
            $ext->add($context, $exten, '', new ext_execif('$["${FORCE_CONFIRM}"!="" ]', 'Set', 'DIAL_TRUNK_OPTIONS=${DIAL_TRUNK_OPTIONS}M(confirm)'));

            // This macro call will always be blank and is provided as a hook for customization required prior to making a call
            // such as adding SIP header information or other requirements. All the channel variables from above are present

            $ext->add($context, $exten, 'gocall', new ext_macro('dialout-dundi-predial-hook'));
            $ext->add($context, $exten, '', new ext_gotoif('$["${PREDIAL_HOOK_RET}" = "BYPASS"]', 'bypass,1'));

            if ($amp_conf['AST_FUNC_CONNECTEDLINE'] && $amp_conf['OUTBOUND_DIAL_UPDATE']) {
                $ext->add($context, $exten, '', new ext_execif('$["${DB(AMPUSER/${AMPUSER}/cidname)}" != ""]','Set','CONNECTEDLINE(num,i)=${DIAL_NUMBER}'));
            }
            if ($amp_conf['AST_FUNC_CONNECTEDLINE'] && $amp_conf['OUTBOUND_CID_UPDATE']) {
                $ext->add($context, $exten, '', new ext_execif('$["${DB(AMPUSER/${AMPUSER}/cidname)}" != ""]','Set','CONNECTEDLINE(name,i)=CID:${CALLERID(number)}'));
            }

            $ext->add($context, $exten, '', new ext_macro('dundi-${DIAL_TRUNK}','${OUTNUM}'));
            $ext->add($context, $exten, '', new ext_gotoif('$["${ARG4}" = "on"]','continue,1', 's-${DIALSTATUS},1'));
            $ext->add($context, $exten, 'chanfull', new ext_noop('max channels used up'));

            $exten = 's-BUSY';
            /*
            * HANGUPCAUSE 17 = Busy, or SIP 486 Busy everywhere
            */
            $ext->add($context, $exten, '', new ext_noop('Dial failed due to trunk reporting BUSY - giving up'));
            $ext->add($context, $exten, '', new ext_playtones('busy'));
            $ext->add($context, $exten, '', new ext_busy(20));

            /*
            * There are reported bugs in Asterisk Blind Trasfers that result in Dial() returning and continuing
            * execution with a status of ANSWER. So we hangup at this point
            */
            $exten = 's-ANSWER';
            $ext->add($context, $exten, '', new ext_noop('Call successfully answered - Hanging up now'));
            $ext->add($context, $exten, '', new ext_macro('hangupcall'));

            $exten = 's-NOANSWER';
            /*
            * HANGUPCAUSE 18 = No User Responding, or SIP 408 Request Timeout
            * HANGUPCAUSE 19 = No Answer From The User, or SIP 480 Temporarily unavailable, SIP 483 To many hops
            */
            $ext->add($context, $exten, '', new ext_noop('Dial failed due to trunk reporting NOANSWER - giving up'));
            $ext->add($context, $exten, '', new ext_progress());
            switch ($trunkreportmsg_ids['no_answer_msg_id']) {
            case DEFAULT_MSG:
                $ext->add($context, $exten, '', new ext_playback('number-not-answering,noanswer'));
                break;
            case CONGESTION_TONE:
                $ext->add($context, $exten, '', new ext_playtones('congestion'));
                break;
            default:
                $message = recordings_get_file($trunkreportmsg_ids['no_answer_msg_id']);
                $message = ($message != "") ? $message : "number-not-answering";
                $ext->add($context, $exten, '', new ext_playback("$message, noanswer"));
            }
            $ext->add($context, $exten, '', new ext_congestion(20));

            $exten = 's-INVALIDNMBR';
            /*
            * HANGUPCAUSE 28 = Address Incomplete, or SIP 484 Address Incomplete
            */
            $ext->add($context, $exten, '', new ext_noop('Dial failed due to trunk reporting Address Incomplete - giving up'));
            $ext->add($context, $exten, '', new ext_progress());
            switch ($trunkreportmsg_ids['invalidnmbr_msg_id']) {
            case DEFAULT_MSG:
                $ext->add($context, $exten, '', new ext_playback('ss-noservice,noanswer'));
                break;
            case CONGESTION_TONE:
                $ext->add($context, $exten, '', new ext_playtones('congestion'));
                break;
            default:
                $message = recordings_get_file($trunkreportmsg_ids['invalidnmbr_msg_id']);
                $message = ($message != "") ? $message : "ss-noservice";
                $ext->add($context, $exten, '', new ext_playback("$message, noanswer"));
            }
            $ext->add($context, $exten, '', new ext_busy(20));

            $exten = "s-CHANGED";
            $ext->add($context, $exten, '', new ext_noop('Dial failed due to trunk reporting Number Changed - giving up'));
            $ext->add($context, $exten, '', new ext_playtones('busy'));
            $ext->add($context, $exten, '', new ext_busy(20));

            $exten = '_s-.';
            $ext->add($context, $exten, '', new ext_set('RC', '${IF($[${ISNULL(${HANGUPCAUSE})}]?0:${HANGUPCAUSE})}'));
            $ext->add($context, $exten, '', new ext_goto('1','${RC}'));

            $ext->add($context, '17', '', new ext_goto('1','s-BUSY'));
            $ext->add($context, '18', '', new ext_goto('1','s-NOANSWER'));
            $ext->add($context, '22', '', new ext_goto('1','s-CHANGED'));
            $ext->add($context, '23', '', new ext_goto('1','s-CHANGED'));
            $ext->add($context, '28', '', new ext_goto('1','s-INVALIDNMBR'));
            $ext->add($context, '_X', '', new ext_goto('1','continue'));
            $ext->add($context, '_X.', '', new ext_goto('1','continue'));

            $exten = 'continue';
            if ($generate_trunk_monitor_failure) {
                $ext->add($context, $exten, '', new ext_gotoif('$["${OUTFAIL_${ARG1}}" = ""]', 'noreport'));
                $ext->add($context, $exten, '', new ext_agi('${OUTFAIL_${ARG1}}'));
            }
            $ext->add($context, $exten, 'noreport', new ext_noop('TRUNK Dial failed due to ${DIALSTATUS} HANGUPCAUSE: ${HANGUPCAUSE} - failing through to other trunks'));
            $ext->add($context, $exten, '', new ext_set('CALLERID(number)', '${AMPUSER}'));

            $ext->add($context, 'disabletrunk', '', new ext_noop('TRUNK: ${OUT_${DIAL_TRUNK}} DISABLED - falling through to next trunk'));
            $ext->add($context, 'bypass', '', new ext_noop('TRUNK: ${OUT_${DIAL_TRUNK}} BYPASSING because dialout-dundi-predial-hook'));

            $ext->add($context, 'h', '', new ext_macro('hangupcall'));
            } // if trunk_type_needed


            /*
                ;-------------------------------------------------------------------------------
                ; macro-privacy-mgr:
                ;
                ; Privacy Manager Macro makes sure that any calls that don't pass the privacy manager are presented
                ; with congestion since there have been observed cases of the call continuing if not stopped with a
                ; congestion, and this provides a slightly more friendly 'sorry' message in case the user is
                ; legitimately trying to be cooperative.
                ;
                ; Note: the following options are configurable in privacy.conf:
                ;
                ;    maxretries = 3 ; default value, number of retries before failing
                ;    minlength = 10 ; default value, number of digits to be accepted as valid CID
                ;
                ;-------------------------------------------------------------------------------
             */
            $context = 'macro-privacy-mgr';
            $exten = 's';

            $ext->add($context, $exten, '', new ext_set('KEEPCID', '${CALLERID(num)}'));
            $ext->add($context, $exten, '', new ext_set('TESTCID', '${IF($["${CALLERID(num):0:1}"="+"]?${MATH(1+${CALLERID(num):1})}:${MATH(1+${CALLERID(num)})})}'));
            $ext->add($context, $exten, '', new ext_execif('$[${LEN(${TESTCID})}=0]', 'Set', 'CALLERID(num)='));
            $ext->add($context, $exten, '', new ext_privacymanager('${ARG1},${ARG2}'));
            $ext->add($context, $exten, '', new ext_gotoif('$["${PRIVACYMGRSTATUS}"="FAILED"]', 'fail'));
            $ext->add($context, $exten, '', new ext_gosubif('$["${CALLED_BLACKLIST}"="1"]','app-blacklist-check,s,1'));
            $ext->add($context, $exten, '', new ext_set('CALLERID(num-pres)', 'allowed_passed_screen'));
            $ext->add($context, $exten, '', new ext_macroexit());

            $ext->add($context, $exten, 'fail', new ext_noop('STATUS: ${PRIVACYMGRSTATUS} CID: ${CALLERID(num)} ${CALLERID(name)} CALLPRES: ${CALLLINGPRES}'));
            $ext->add($context, $exten, '', new ext_playback('sorry-youre-having-problems&goodbye'));
            $ext->add($context, $exten, '', new ext_playtones('congestion'));
            $ext->add($context, $exten, '', new ext_congestion(20));
            $ext->add($context, 'h', '', new ext_hangup());

            /*
             * sets the CallerID of the device to that of the logged in user
             *
             * ${AMPUSER} is set upon return to the real user despite any aliasing that may
             * have been set as a result of the AMPUSER/<nnn>/cidnum field. This is used by
             * features like DND, CF, etc. to set the proper structure on aliased instructions
             */
            $context = 'macro-user-callerid';
            $exten = 's';

            //$ext->add($context, $exten, '', new ext_noop('user-callerid: ${CALLERID(name)} ${CALLERID(number)}'));

            $ext->add($context, $exten, '', new ext_set('TOUCH_MONITOR','${UNIQUEID}'));
            // make sure AMPUSER is set if it doesn't get set below
            $ext->add($context, $exten, '', new ext_set('AMPUSER', '${IF($["${AMPUSER}" = ""]?${CALLERID(number)}:${AMPUSER})}'));

            if ($amp_conf['QUEUES_LOG_TRANSFERS'] !== false) {
                // Log TRANSFER in queue_log if a BLINDTRANSFER is detected from a queue call
                $ext->add($context, $exten, '', new ext_gotoif('$["${BLINDTRANSFER}" != "" & "${FROMQ}" != ""]', 'trq'));
                $ext->add($context, $exten, '', new ext_gotoif('$["x" = "x"]', 'resume'));
                $ext->add($context, $exten, 'trq', new ext_gotoif('$["x${NODEST}" = "x"]', 'resume'));
                $ext->add($context, $exten, '', new ext_set('AGCHAN', '${CUT(BLINDTRANSFER,-,1)}'));
                $ext->add($context, $exten, '', new ext_set('VIRTUAL', '${CUT(AGCHAN,/,2)}'));
                $ext->add($context, $exten, '', new ext_set('__AGNAME', '${DB(AMPUSER/${VIRTUAL}/cidname)}'));
                $ext->add($context, $exten, '', new ext_gotoif('$["x${AGNAME}" = "x"]', 'resume'));
                $ext->add($context, $exten, '', new ext_set('TDEST', '${IF($["${MACRO_EXTEN}" = "s"]?${ARG2}:${MACRO_EXTEN})}'));
                $ext->add($context, $exten, '', new ext_queuelog('${NODEST}','${CHANNEL(LINKEDID)}','${AGNAME}','TRANSFER','${TDEST}'));
            }

            $ext->add($context, $exten, 'resume', new ext_gotoif('$["${CUT(CHANNEL,@,2):5:5}"="queue" | ${LEN(${AMPUSERCIDNAME})}]', 'report'));
            $ext->add($context, $exten, '', new ext_execif('$["${REALCALLERIDNUM:1:2}" = ""]', 'Set', 'REALCALLERIDNUM=${CALLERID(number)}'));
            $ext->add($context, $exten, '', new ext_set('AMPUSER', '${DB(DEVICE/${REALCALLERIDNUM}/user)}'));

            // Device & User: If they're not signed in, then they can't do anything.
            $ext->add($context, $exten, '', new ext_gotoif('$["${AMPUSER}" = "none"]', 'limit'));

            $ext->add($context, $exten, '', new ext_set('AMPUSERCIDNAME', '${DB(AMPUSER/${AMPUSER}/cidname)}'));
            $ext->add($context, $exten, '', new ext_gotoif('$["${AMPUSERCIDNAME:1:2}" = ""]', 'report'));

            // user may masquerade as a different user internally, so set the internal cid as indicated
            // but keep the REALCALLERID which is used to determine their true identify and lookup info
            // during outbound calls.
            $ext->add($context, $exten, '', new ext_set('AMPUSERCID', '${IF($["${ARG2}" != "EXTERNAL" & "${DB_EXISTS(AMPUSER/${AMPUSER}/cidnum)}" = "1"]?${DB_RESULT}:${AMPUSER})}'));

            // If there is a defined dialopts then use it, otherwise use the global default
            //
            $ext->add($context, $exten, '', new ext_set('__DIAL_OPTIONS', '${IF($["${DB_EXISTS(AMPUSER/${AMPUSER}/dialopts)}" = "1"]?${DB_RESULT}:${DIAL_OPTIONS})}'));

            $ext->add($context, $exten, '', new ext_set('CALLERID(all)', '"${AMPUSERCIDNAME}" <${AMPUSERCID}>'));

            $ext->add($context, $exten, '', new ext_noop_trace('Current Concurrency Count for ${AMPUSER}: ${GROUP_COUNT(${AMPUSER}@concurrency_limit)}, User Limit: ${DB(AMPUSER/${AMPUSER}/concurrency_limit)}'));
            $ext->add($context, $exten, '', new ext_gotoif('$["${ARG1}"="LIMIT" & ${LEN(${AMPUSER})} & "${DB(AMPUSER/${AMPUSER}/concurrency_limit)}">"0" & "${GROUP_COUNT(${AMPUSER}@concurrency_limit)}">="${DB(AMPUSER/${AMPUSER}/concurrency_limit)}"]', 'limit'));
            $ext->add($context, $exten, '', new ext_execif('$["${ARG1}"="LIMIT" & ${LEN(${AMPUSER})}]', 'Set', 'GROUP(concurrency_limit)=${AMPUSER}'));
            /*
             * This is where to splice in things like setting the language based on a user's astdb setting,
             * or where you might set the CID account code based on a user instead of the device settings.
             */

            $ext->add($context, $exten, 'report', new ext_gotoif('$[ "${ARG1}" = "SKIPTTL" | "${ARG1}" = "LIMIT" ]', 'continue'));
            $ext->add($context, $exten, 'report2', new ext_set('__TTL', '${IF($["foo${TTL}" = "foo"]?64:$[ ${TTL} - 1 ])}'));
            $ext->add($context, $exten, '', new ext_gotoif('$[ ${TTL} > 0 ]', 'continue'));
            $ext->add($context, $exten, '', new ext_wait('${RINGTIMER}'));  // wait for a while, to give it a chance to be picked up by voicemail
            $ext->add($context, $exten, '', new ext_answer());
            $ext->add($context, $exten, '', new ext_wait('1'));
            $ext->add($context, $exten, '', new ext_playback('im-sorry&an-error-has-occured&with&call-forwarding'));
            $ext->add($context, $exten, '', new ext_macro('hangupcall'));
            $ext->add($context, $exten, 'limit', new ext_answer());
            $ext->add($context, $exten, '', new ext_wait('1'));
            $ext->add($context, $exten, '', new ext_playback('beep&im-sorry&your&simul-call-limit-reached&goodbye'));
            $ext->add($context, $exten, '', new ext_macro('hangupcall'));
            $ext->add($context, $exten, '', new ext_congestion(20));

            // Address Security Vulnerability in many earlier versions of Asterisk from an external source tranmitting a
            // malicious CID that can cause overflows in the Asterisk code.
            //
            $ext->add($context, $exten, 'continue', new ext_set('CALLERID(number)','${CALLERID(number):0:40}'));
            $ext->add($context, $exten, '', new ext_set('CALLERID(name)','${CALLERID(name):0:40}'));
            $ext->add($context, $exten, '', new ext_set('CDR(cnum)','${CALLERID(num)}'));
            $ext->add($context, $exten, '', new ext_set('CDR(cnam)','${CALLERID(name)}'));
            // CHANNEL(language) does not get inherited (which seems like an Asterisk bug as musicclass does)
            // so if whe have MASTER_CHANNEL() available to us let's rectify that
            //
            if ($amp_conf['AST_FUNC_MASTER_CHANNEL']) {
                $ext->add($context, $exten, '', new ext_set('CHANNEL(language)', '${MASTER_CHANNEL(CHANNEL(language))}'));
            }
            $ext->add($context, $exten, '', new ext_noop_trace('Using CallerID ${CALLERID(all)}'));
            $ext->add($context, 'h', '', new ext_macro('hangupcall'));

            /*
             * arg1 = trunk number, arg2 = number
             *
             * Re-written to use enumlookup.agi
             */

            // Is this the best place to put it in?
            // Check if we are using Google DNS for ENUM-lookups,
            // enable it as a global variable so we can use it in the agi
            if($amp_conf['USEGOOGLEDNSFORENUM']) {
                $ext->addGlobal('ENUMUSEGOOGLEDNS', 'TRUE');
            }

            $context = 'macro-dialout-enum';
            if (!empty($trunk_type_needed[$context])) {
                $exten = 's';

                $ext->add($context, $exten, '', new ext_gosubif('$[$["${ARG3}" != ""] & $["${DB(AMPUSER/${AMPUSER}/pinless)}" != "NOPASSWD"]]','sub-pincheck,s,1'));
                $ext->add($context, $exten, '', new ext_gotoif('$["x${OUTDISABLE_${DIAL_TRUNK}}" = "xon"]', 'disabletrunk,1'));
                $ext->add($context, $exten, '', new ext_set('DIAL_TRUNK_OPTIONS', '${IF($["${DB_EXISTS(TRUNK/${DIAL_TRUNK}/dialopts)}" = "1"]?${DB_RESULT}:${TRUNK_OPTIONS})}'));
                $ext->add($context, $exten, '', new ext_set('OUTBOUND_GROUP', 'OUT_${ARG1}'));
                $ext->add($context, $exten, '', new ext_gotoif('$["${OUTMAXCHANS_${ARG1}}foo" = "foo"]', 'nomax'));
                $ext->add($context, $exten, '', new ext_gotoif('$[ ${GROUP_COUNT(OUT_${ARG1})} >= ${OUTMAXCHANS_${ARG1}} ]', 'nochans'));
                $ext->add($context, $exten, 'nomax', new ext_set('DIAL_NUMBER', '${ARG2}'));
                $ext->add($context, $exten, '', new ext_set('DIAL_TRUNK', '${ARG1}'));
                $ext->add($context, $exten, '', new ext_gotoif('$["${INTRACOMPANYROUTE}" = "YES"]', 'skipoutcid'));  // Set to YES if treated like internal
                $ext->add($context, $exten, '', new ext_set('DIAL_TRUNK_OPTIONS', '${DIAL_OPTIONS}')); // will be reset to TRUNK_OPTIONS if not intra-company
                $ext->add($context, $exten, '', new ext_macro('outbound-callerid', '${DIAL_TRUNK}'));
                $ext->add($context, $exten, 'skipoutcid', new ext_gosubif('$["${PREFIX_TRUNK_${DIAL_TRUNK}}" != ""]','sub-flp-${DIAL_TRUNK},s,1'));  // manimpulate DIAL_NUMBER
                //  Replacement for asterisk's ENUMLOOKUP function
                $ext->add($context, $exten, '', new ext_agi('enumlookup.agi'));

                if ($amp_conf['AST_FUNC_CONNECTEDLINE'] && $amp_conf['OUTBOUND_DIAL_UPDATE']) {
                    $ext->add($context, $exten, '', new ext_execif('$["${DB(AMPUSER/${AMPUSER}/cidname)}" != ""]','Set','CONNECTEDLINE(num,i)=${DIAL_NUMBER}'));
                }
                if ($amp_conf['AST_FUNC_CONNECTEDLINE'] && $amp_conf['OUTBOUND_CID_UPDATE']) {
                    $ext->add($context, $exten, '', new ext_execif('$["${DB(AMPUSER/${AMPUSER}/cidname)}" != ""]','Set','CONNECTEDLINE(name,i)=CID:${CALLERID(number)}'));
                }

                // Now we have the variable DIALARR set to a list of URI's that can be called, in order of priority
                // Loop through them trying them in order.
                $ext->add($context, $exten, 'dialloop', new ext_gotoif('$["foo${DIALARR}"="foo"]', 's-${DIALSTATUS},1'));
                $ext->add($context, $exten, '', new ext_execif('$["${MOHCLASS}"!="default" & "${MOHCLASS}"!="" & "${FORCE_CONFIRM}"="" ]', 'Set', 'DIAL_TRUNK_OPTIONS=M(setmusic^${MOHCLASS})${DIAL_TRUNK_OPTIONS}'));
                $ext->add($context, $exten, '', new ext_execif('$["${FORCE_CONFIRM}"!="" ]', 'Set', 'DIAL_TRUNK_OPTIONS=M(confirm)${DIAL_TRUNK_OPTIONS}'));
                $ext->add($context, $exten, '', new ext_set('TRYDIAL', '${CUT(DIALARR,%,1)}'));
                $ext->add($context, $exten, '', new ext_set('DIALARR', '${CUT(DIALARR,%,2-)}'));
                $ext->add($context, $exten, '', new ext_dial('${TRYDIAL}', '${DIAL_TRUNK_OPTIONS}'));
                // Now, if we're still here, that means the Dial failed for some reason.
                // If it's CONGESTION or CHANUNAVAIL we want to try again on a different
                // different channel. If there's no more left, the dialloop tag will exit.
                $ext->add($context, $exten, '', new ext_gotoif('$[ $[ "${DIALSTATUS}" = "CHANUNAVAIL" ] | $[ "${DIALSTATUS}" = "CONGESTION" ] ]', 'dialloop'));
                $ext->add($context, $exten, '', new ext_gotoif('$["${ARG4}" = "on"]','continue,1', 's-${DIALSTATUS},1'));
                // Here are the exit points for the macro.
                $ext->add($context, $exten, 'nochans', new ext_noop('max channels used up'));

                $exten = 's-BUSY';
                /*
                * HANGUPCAUSE 17 = Busy, or SIP 486 Busy everywhere
                */
                $ext->add($context, $exten, '', new ext_noop('Dial failed due to trunk reporting BUSY - giving up'));
                $ext->add($context, $exten, '', new ext_playtones('busy'));
                $ext->add($context, $exten, '', new ext_busy(20));

                /*
                * There are reported bugs in Asterisk Blind Trasfers that result in Dial() returning and continuing
                * execution with a status of ANSWER. So we hangup at this point
                */
                $exten = 's-ANSWER';
                $ext->add($context, $exten, '', new ext_noop('Call successfully answered - Hanging up now'));
                $ext->add($context, $exten, '', new ext_macro('hangupcall'));

                $exten = 's-NOANSWER';
                /*
                * HANGUPCAUSE 18 = No User Responding, or SIP 408 Request Timeout
                * HANGUPCAUSE 19 = No Answer From The User, or SIP 480 Temporarily unavailable, SIP 483 To many hops
                */
                $ext->add($context, $exten, '', new ext_noop('Dial failed due to trunk reporting NOANSWER - giving up'));
                $ext->add($context, $exten, '', new ext_progress());
                switch ($trunkreportmsg_ids['no_answer_msg_id']) {
                case DEFAULT_MSG:
                    $ext->add($context, $exten, '', new ext_playback('number-not-answering,noanswer'));
                    break;
                case CONGESTION_TONE:
                    $ext->add($context, $exten, '', new ext_playtones('congestion'));
                    break;
                default:
                    $message = recordings_get_file($trunkreportmsg_ids['no_answer_msg_id']);
                    $message = ($message != "") ? $message : "number-not-answering";
                    $ext->add($context, $exten, '', new ext_playback("$message, noanswer"));
                }
                $ext->add($context, $exten, '', new ext_congestion(20));

                $exten = 's-INVALIDNMBR';
                /*
                * HANGUPCAUSE 28 = Address Incomplete, or SIP 484 Address Incomplete
                */
                $ext->add($context, $exten, '', new ext_noop('Dial failed due to trunk reporting Address Incomplete - giving up'));
                $ext->add($context, $exten, '', new ext_progress());
                switch ($trunkreportmsg_ids['invalidnmbr_msg_id']) {
                case DEFAULT_MSG:
                    $ext->add($context, $exten, '', new ext_playback('ss-noservice,noanswer'));
                    break;
                case CONGESTION_TONE:
                    $ext->add($context, $exten, '', new ext_playtones('congestion'));
                    break;
                default:
                    $message = recordings_get_file($trunkreportmsg_ids['invalidnmbr_msg_id']);
                    $message = ($message != "") ? $message : "ss-noservice";
                    $ext->add($context, $exten, '', new ext_playback("$message, noanswer"));
                }
                $ext->add($context, $exten, '', new ext_busy(20));

                $exten = "s-CHANGED";
                $ext->add($context, $exten, '', new ext_noop('Dial failed due to trunk reporting Number Changed - giving up'));
                $ext->add($context, $exten, '', new ext_playtones('busy'));
                $ext->add($context, $exten, '', new ext_busy(20));

                $exten = '_s-.';
                $ext->add($context, $exten, '', new ext_set('RC', '${IF($[${ISNULL(${HANGUPCAUSE})}]?0:${HANGUPCAUSE})}'));
                $ext->add($context, $exten, '', new ext_goto('1','${RC}'));

                $ext->add($context, '17', '', new ext_goto('1','s-BUSY'));
                $ext->add($context, '18', '', new ext_goto('1','s-NOANSWER'));
                $ext->add($context, '22', '', new ext_goto('1','s-CHANGED'));
                $ext->add($context, '23', '', new ext_goto('1','s-CHANGED'));
                $ext->add($context, '28', '', new ext_goto('1','s-INVALIDNMBR'));
                $ext->add($context, '_X', '', new ext_goto('1','continue'));
                $ext->add($context, '_X.', '', new ext_goto('1','continue'));

                $exten = 'continue';
                if ($generate_trunk_monitor_failure) {
                    $ext->add($context, $exten, '', new ext_gotoif('$["${OUTFAIL_${ARG1}}" = ""]', 'noreport'));
                    $ext->add($context, $exten, '', new ext_agi('${OUTFAIL_${ARG1}}'));
                }
                $ext->add($context, $exten, 'noreport', new ext_noop('TRUNK Dial failed due to ${DIALSTATUS} HANGUPCAUSE: ${HANGUPCAUSE} - failing through to other trunks'));
                $ext->add($context, $exten, '', new ext_set('CALLERID(number)', '${AMPUSER}'));

                $ext->add($context, 'disabletrunk', '', new ext_noop('TRUNK: ${OUT_${DIAL_TRUNK}} DISABLED - falling through to next trunk'));
                $ext->add($context, 'bypass', '', new ext_noop('TRUNK: ${OUT_${DIAL_TRUNK}} BYPASSING because dialout-trunk-predial-hook'));

                $ext->add($context, 'h', '', new ext_macro('hangupcall'));
            } // if trunk_type_needed


            /*
             * overrides CallerID out trunks
             * arg1 is trunk
             * macro-user-callerid should be called _before_ using this macro
             */

            $context = 'macro-outbound-callerid';
            $exten = 's';

            // If we modified the caller presence, set it back. This allows anonymous calls to be internally prepended but keep
            // their status if forwarded back out. Not doing this can result in the trunk CID being displayed vs. 'blocked call'
            //
            if ($ast_lt_16) {
                $ext->add($context, $exten, '', new ext_execif('$["${CALLINGPRES_SV}" != ""]', 'SetCallerPres', '${CALLINGPRES_SV}'));
            } else {
                if($ast_ge_18) {
                  $ext->add($context, $exten, '', new ext_execif('$["${CALLINGPRES_SV}" != ""]', 'Set', 'CALLERID(pres)=${CALLINGPRES_SV}'));
                } else {
                  $ext->add($context, $exten, '', new ext_execif('$["${CALLINGPRES_SV}" != ""]', 'Set', 'CALLERPRES()=${CALLINGPRES_SV}'));
                }
            }

            // Keep the original CallerID number, for failover to the next trunk.

            $ext->add($context, $exten, '', new ext_execif('$["${REALCALLERIDNUM:1:2}" = ""]', 'Set', 'REALCALLERIDNUM=${CALLERID(number)}'));
            // If this came through a ringgroup or CF, then we want to retain original CID unless
            // OUTKEEPCID_${trunknum} is set.
            // Save then CIDNAME while it is still intact in case we end up sending out this same CID
            $ext->add($context, $exten, 'start', new ext_gotoif('$[ $["${REALCALLERIDNUM}" = ""] | $["${KEEPCID}" != "TRUE"] | $["${OUTKEEPCID_${ARG1}}" = "on"] ]', 'normcid'));  // Set to TRUE if coming from ringgroups, CF, etc.
            $ext->add($context, $exten, '', new ext_set('USEROUTCID', '${REALCALLERIDNUM}'));
            //$ext->add($context, $exten, '', new ext_set('REALCALLERIDNAME', '${CALLERID(name)}'));

            // We now have to make sure the CID is valid. If we find an AMPUSER with the same CID, we assume it is an internal
            // call (would be quite a conincidence if not) and go through the normal processing to get that CID. If a device
            // is set for this CID, then it must be internal
            // If we end up using USEROUTCID at the end, it may still be the REALCALLERIDNUM we saved above. That is determined
            // if the two are equal, AND there is no CALLERID(name) present since it has been removed by the CALLERID(all)=${USEROUTCID}
            // setting. If this is the case, then we put the orignal name back in to send out. Although the CNAME is not honored by most
            // carriers, there are cases where it is so this preserves that information to be used by those carriers who do honor it.
            $ext->add($context, $exten, '', new ext_gotoif('$["foo${DB(AMPUSER/${REALCALLERIDNUM}/device)}" = "foo"]', 'bypass'));

            $ext->add($context, $exten, 'normcid', new ext_set('USEROUTCID', '${DB(AMPUSER/${AMPUSER}/outboundcid)}'));
            $ext->add($context, $exten, 'bypass', new ext_set('EMERGENCYCID', '${DB(DEVICE/${REALCALLERIDNUM}/emergency_cid)}'));
            $ext->add($context, $exten, '', new ext_set('TRUNKOUTCID', '${OUTCID_${ARG1}}'));
            $ext->add($context, $exten, '', new ext_gotoif('$["${EMERGENCYROUTE:1:2}" = "" | "${EMERGENCYCID:1:2}" = ""]', 'trunkcid'));  // check EMERGENCY ROUTE
            $ext->add($context, $exten, '', new ext_set('CALLERID(all)', '${EMERGENCYCID}'));  // emergency cid for device
            $ext->add($context, $exten, '', new ext_set('CDR(outbound_cnum)','${CALLERID(num)}'));
            $ext->add($context, $exten, '', new ext_set('CDR(outbound_cnam)','${CALLERID(name)}'));
            $ext->add($context, $exten, 'exit', new ext_macroexit());


            $ext->add($context, $exten, 'trunkcid', new ext_execif('$[${LEN(${TRUNKOUTCID})} != 0]', 'Set', 'CALLERID(all)=${TRUNKOUTCID}'));
            $ext->add($context, $exten, 'usercid', new ext_execif('$[${LEN(${USEROUTCID})} != 0]', 'Set', 'CALLERID(all)=${USEROUTCID}'));  // check CID override for extension
            /* TRUNKCIDOVERRIDE is used by followme and can be used by other functions. It forces the specified CID except for the case of
             * an Emergency CID on an Emergency Route
             */
            $ext->add($context, $exten, '', new ext_execif('$[${LEN(${TRUNKCIDOVERRIDE})} != 0 | ${LEN(${FORCEDOUTCID_${ARG1}})} != 0]', 'Set', 'CALLERID(all)=${IF($[${LEN(${FORCEDOUTCID_${ARG1}})}=0]?${TRUNKCIDOVERRIDE}:${FORCEDOUTCID_${ARG1}})}'));
            if ($ast_lt_16) {
                $ext->add($context, $exten, 'hidecid', new ext_execif('$["${CALLERID(name)}"="hidden"]', 'SetCallerPres', 'prohib_passed_screen'));
            } else {
                if($ast_ge_18) {
                  $ext->add($context, $exten, 'hidecid', new ext_execif('$["${CALLERID(name)}"="hidden"]', 'Set', 'CALLERID(pres)=prohib_passed_screen'));
                } else {
                  $ext->add($context, $exten, 'hidecid', new ext_execif('$["${CALLERID(name)}"="hidden"]', 'Set', 'CALLERPRES()=prohib_passed_screen'));
                }
            }
            // $has_keepcid_cnum is checked and set when the globals are being generated above
            //
            if ($has_keepcid_cnum || $amp_conf['BLOCK_OUTBOUND_TRUNK_CNAM']) {
                if ($amp_conf['BLOCK_OUTBOUND_TRUNK_CNAM']) {
                    $ext->add($context, $exten, '', new ext_set('CALLERID(name)', ''));
                } else {
                    $ext->add($context, $exten, '', new ext_execif('$["${OUTKEEPCID_${ARG1}}" = "cnum"]', 'Set', 'CALLERID(name)='));
                }
            }
            $ext->add($context, $exten, '', new ext_set('CDR(outbound_cnum)','${CALLERID(num)}'));
            $ext->add($context, $exten, '', new ext_set('CDR(outbound_cnam)','${CALLERID(name)}'));


            // Combined from-zpatel / from-dahdi and all macros now from-dahdi-channum
            //
            $ext->addInclude('from-zaptel', 'from-dahdi', _dgettext('amp','From DAHDI'));
            $ext->add('from-zaptel', 'foo','', new ext_noop('bar'));

            $context = 'from-dahdi';
            $exten = '_X.';

            $ext->add($context, $exten, '', new ext_set('DID', '${EXTEN}'));
            $ext->add($context, $exten, '', new ext_goto(1, 's'));

            $exten = 's';
            $ext->add($context, $exten, '', new ext_noop('Entering from-dahdi with DID == ${DID}'));
            // Some trunks _require_ a RINGING be sent before an Answer.
            $ext->add($context, $exten, '', new ext_ringing());
            // If ($did == "") { $did = "s"; }
            $ext->add($context, $exten, '', new ext_set('DID', '${IF($["${DID}"= ""]?s:${DID})}'));
            $ext->add($context, $exten, '', new ext_noop('DID is now ${DID}'));
            $ext->add($context, $exten, '', new ext_gotoif('$["${CHANNEL:0:5}"="DAHDI"]', 'dahdiok', 'checkzap'));
            $ext->add($context, $exten, 'checkzap', new ext_gotoif('$["${CHANNEL:0:3}"="Zap"]', 'zapok', 'neither'));
            $ext->add($context, $exten, 'neither', new ext_goto('1', '${DID}', 'from-pstn'));
            // If there's no ext-did,s,1, that means there's not a no did/no cid route. Hangup.
            $ext->add($context, $exten, '', new ext_macro('Hangupcall', 'dummy'));

            $ext->add($context, $exten, 'dahdiok', new ext_noop('Is a DAHDi Channel'));
            $ext->add($context, $exten, '', new ext_set('CHAN', '${CHANNEL:6}'));
            $ext->add($context, $exten, '', new ext_set('CHAN', '${CUT(CHAN,-,1)}'));
            $ext->add($context, $exten, '', new ext_macro('from-dahdi-${CHAN}', '${DID},1'));
            // If nothing there, then treat it as a DID
            $ext->add($context, $exten, '', new ext_noop('Returned from Macro from-dahdi-${CHAN}'));
            $ext->add($context, $exten, '', new ext_goto(1, '${DID}', 'from-pstn'));

            $ext->add($context, $exten, 'zapok', new ext_noop('Is a Zaptel Channel'));
            $ext->add($context, $exten, '', new ext_set('CHAN', '${CHANNEL:4}'));
            $ext->add($context, $exten, '', new ext_set('CHAN', '${CUT(CHAN,-,1)}'));
            $ext->add($context, $exten, '', new ext_macro('from-dahdi-${CHAN}', '${DID},1'));
            $ext->add($context, $exten, '', new ext_noop('Returned from Macro from-dahdi-${CHAN}'));
            $ext->add($context, $exten, '', new ext_goto(1, '${DID}', 'from-pstn'));

            /*
            * vm-callme context plays voicemail over telephone for web click-to-call
            * MSG and MBOX are channel variables that must be set when originating the call
            */

            $context = 'vm-callme';

            $ext->add($context, 's', '', new ext_answer());
            $ext->add($context, 's', '', new ext_wait(1));
            $ext->add($context, 's', 'repeat', new ext_background('${MSG}&silence/2&vm-repeat&vm-starmain'));
            $ext->add($context, 's', '', new ext_waitexten(15));

            $ext->add($context, '5', '', new ext_goto('repeat', 's'));

            $ext->add($context, '#', '', new ext_playback('vm-goodbye'));
            $ext->add($context, '#', '', new ext_hangup());

            $ext->add($context, '*', '', new ext_macro('get-vmcontext', '${MBOX}'));
            $ext->add($context, '*', '', new ext_vmmain('${MBOX}@${VMCONTEXT},s'));

            $ext->add($context, 'i', '', new ext_playback('pm-invalid-option'));
            $ext->add($context, 'i', '', new ext_goto('repeat', 's'));

            $ext->add($context, 't', '', new ext_playback('vm-goodbye'));
            $ext->add($context, 't', '', new ext_hangup());

            $ext->add($context, 'h', '', new ext_hangup());

            /* end vm-callme context  */


            /*
             ;------------------------------------------------------------------------
             ; [macro-dial-confirm]
             ;------------------------------------------------------------------------
             ; This has now been incorporated into dialparties. It still only works with ringall
             ; and ringall-prim strategies. Have not investigated why it doesn't work with
             ; hunt and memory hunt.
             ;
             ;------------------------------------------------------------------------
             [macro-dial-confirm]
             ; This was written to make it easy to use macro-dial-confirm instead of macro-dial in generated dialplans.
             ; This takes the same parameters, with an additional parameter of the ring group Number
             ; ARG1 is the timeout
             ; ARG2 is the DIAL_OPTIONS
             ; ARG3 is a list of xtns to call - 203-222-240-123123123#-211
             ; ARG4 is the ring group number
            */

            $mcontext = 'macro-dial-confirm';
            $exten = 's';

            // set to ringing so confirm macro can keep from passing the channel during confirmation if
            // someone beat them to it.
            //
            $ext->add($mcontext, $exten, '', new ext_set('DB(RG/${ARG4}/${CHANNEL})','RINGING'));
            $ext->add($mcontext, $exten, '', new ext_set('__UNIQCHAN','${CHANNEL}'));

            // Tell dialparites to place the call through the [grps] context
            //
            $ext->add($mcontext, $exten, '', new ext_set('USE_CONFIRMATION','TRUE'));
            $ext->add($mcontext, $exten, '', new ext_set('RINGGROUP_INDEX','${ARG4}'));

            $ext->add($mcontext, $exten, '', new ext_set('FORCE_CONFIRM',''));
            $ext->add($mcontext, $exten, '', new ext_set('ARG4',''));
            $ext->add($mcontext, $exten, '', new ext_macro('dial','${ARG1},${ARG2},${ARG3}'));
            $ext->add($mcontext, $exten, '', new ext_dbdel('RG/${RINGGROUP_INDEX}/${CHANNEL}'));
            $ext->add($mcontext, $exten, '', new ext_set('USE_CONFIRMATION',''));
            $ext->add($mcontext, $exten, '', new ext_set('RINGGROUP_INDEX',''));

      /*
        ;------------------------------------------------------------------------
        ; [macro-setmusic]
        ;------------------------------------------------------------------------
        ; CONTEXT:      macro-setmusic
        ; PURPOSE:      to turn off moh on routes where it is not desired
        ;
        ;------------------------------------------------------------------------
        [macro-setmusic]
        exten => s,1,NoOp(Setting Outbound Route MoH To: ${ARG1})
        exten => s,2,Set(CHANNEL(musicclass)=${ARG1}) ; this won't work in 1.2 anymore, could fix in auto-generate if we wanted...
        ;------------------------------------------------------------------------
       */
      $mcontext = 'macro-setmusic';
      $exten = 's';

            $ext->add($mcontext, $exten, '', new ext_noop_trace('Setting Outbound Route MoH To: ${ARG1}'));
            $ext->add($mcontext, $exten, '', new ext_setmusiconhold('${ARG1}'));


      /*
        ;------------------------------------------------------------------------
        ; [block-cf]
        ;------------------------------------------------------------------------
        ; This context is set as a target with FORWARD_CONTEXT when Call Forwarding is set to be
        ; ignored in a ringgroup or other features that may take advantage of this. Server side
        ; CF is done in dialparties.agi but if a client device forwards a call, it will be caught
        ; and blocked here.
        ;------------------------------------------------------------------------
        [block-cf]
        exten => _X.,1,Noop(Blocking callforward to ${EXTEN} because CF is blocked)
        exten => _X.,n,Hangup()

        ;------------------------------------------------------------------------
       */
      $context = 'macro-block-cf';
      $exten = '_X.';

            $ext->add($context, $exten, '', new ext_noop_trace('Blocking callforward to ${EXTEN} because CF is blocked'));
            $ext->add($context, $exten, '', new ext_hangup(''));


            /*
            * macro-vm
            */

            /*
                        ;------------------------------------------------------------------------
                        ; [macro-vm]
                        ;------------------------------------------------------------------------
                        ; CONTEXT:      macro-vm
                        ; PURPOSE:      call voicemail system and extend with personal ivr
                        ;
                        ; Under normal use, this macro will call the voicemail system with the extension and
                        ; desired greeting mode of busy, unavailable or as specified with direct voicemail
                        ; calls (usually unavailable) when entered from destinations.
                        ;
                        ; The voicemail system's two greetings have been 'hijacked' as follows to extend the
                        ; system by giving the option of a private 'ivr' for each voicemail user. The following
                        ; applies to both the busy and unavailable modes of voicemail and can be applied to one
                        ; or both, and differently.
                        ;
                        ; Global Defaults:
                        ;
                        ; The following are default values, used in both busy and unavail modes if no specific
                        ; values are specified.
                        ;
                        ; VMX_REPEAT
                        ;                                       The number of times to repeat the users message if no option is pressed.
                        ; VMX_TIMEOUT
                        ;                                       The timeout to wait after playing message before repeating or giving up.
                        ; VMX_LOOPS
                        ;                                       The number of times it should replay the message and check for an option when
                        ;                                       an invalid option is pressed.
                        ;
                        ; VMX_OPTS_DOVM
                        ;                                       Default voicemail option to use if vm is chosen as an option. No options will
                        ;                                       cause Allison's generic message, 's' will go straight to beep.
                        ; VMX_OPTS_TIMEOUT
                        ;                                       Default voicemail option to use if it times out with no options. No options will
                        ;                                       cause Allison's generic message, 's' will go straight to beep.
                        ;                                       IF THE USER PRESSES # - it will look like a timeout as well since no option will
                        ;                                       be presented. If the user wishes to enable a mode where a caller can press #
                        ;                                       during their message and it goes straight to voicemail with only a 'beep' then
                        ;                                       this should be set to 's'.
                        ; VMX_OPTS_LOOP
                        ;                                       Default voicemail option to use if to many wrong options occur. No options will
                        ;                                       cause Allison's generic message, 's' will go straight to beep.
                        ;
                        ; VMX_CONTEXT
                        ;                                       Default context for user destinations if not supplied in the user's settings
                        ; VMX_PRI
                        ;                                       Default priority for user destinations if not supplied in the user's settings
                        ;
                        ; VMX_TIMEDEST_CONTEXT
                        ;                                       Default context for timeout destination if not supplied in the user's settings
                        ; VMX_TIMEDEST_EXT
                        ;                                       Default extension for timeout destination if not supplied in the user's settings
                        ; VMX_TIMEDEST_PRI
                        ;                                       Default priority for timeout destination if not supplied in the user's settings
                        ;
                        ; VMX_LOOPDEST_CONTEXT
                        ;                                       Default context for loops  destination if not supplied in the user's settings
                        ; VMX_LOOPDEST_EXT
                        ;                                       Default extension for loops  destination if not supplied in the user's settings
                        ; VMX_LOOPDEST_PRI
                        ;                                       Default priority for loops  destination if not supplied in the user's settings
                        ;
                        ;
                        ; The AMPUSER database variable has been extended with a 'vmx' tree (vm-extension). A
                        ; duplicate set is included for both unavail and busy. You could choose for to have an
                        ; ivr when unavail is taken, but not with busy - or a different once with busy.
                        ; The full list is below, each specific entry is futher described:
                        ;
                        ; state:                Whether teh current mode is enabled or disabled. Anything but 'enabled' is
                        ;                                               treated as disabled.
                        ; repeat:               This is the number of times that the users message should be played after the
                        ;                                               timeout if the user has not entered anything. It is just a variable to the
                        ;                                               Read() function which will do the repeating.
                        ; timeout:      This is how long to wait after the message has been read for a response from
                        ;                                               the user. A caller can enter a digit any time during the playback.
                        ; loops:                This is the number of loops that the system will allow a caller to retry if
                        ;                                               they enter a bad menu choice, before going to the loop failover destination
                        ; vmxopts:      This is the vm options to send to the voicemail command used when a specific
                        ;                                               voicemail destination is chosen (inidcated by 'dovm' in the ext field). This is
                        ;                                               typically either set to 's' or left blank. When set to 's' there will be no
                        ;                                               message played when entering the voicemail, just a beep. When blank, you will
                        ;                                               have Allison's generic message played. It is not typical to play the greetings
                        ;                                               since they have been 'hijacked' for these IVR's and from a caller's perspecitive
                        ;                                               this system appears interconnected with the voicemail so instructions can be
                        ;                                               left there.
                        ; timedest: The three variables: ext, context and pri are the goto destination if the caller
                        ;                                               enters no options and it timesout. None have to be set and a system default
                        ;                                               will be used. If just ext is set, then defaults will be used for context and
                        ;                                               pri, etc.
                        ; loopdest:     This is identical to timedest but used if the caller exceeds the maximum invalid
                        ;                                               menu choices.
                        ; [0-9*]:               The user can specify up to 11 ivr options, all as single digits from 0-9 or *. The
                        ;                                               # key can not be used since it is used as a terminator key for the Read command
                        ;                                               and will never be returned. A minimum of the ext must be specified for each valid
                        ;                                               option and as above, the context and priority can also be specified if the default
                        ;                                               is not to be used.
                        ;                                               Option '0' takes on a special meaning. Since a user is able to break out of the
                        ;                                               voicemail command once entering it with a 0, if specified, the 0 destination will
                        ;                                               be used.
                        ;                                               Option '*' can also be used to breakout. It is undecided at this point whether
                        ;                                               providing that option will be used as well. (probably should).
                        ;
                        ;
                        ; /AMPUSER/<ext>/vmx/[busy|unavail]/state:                                                              enabled|disabled
                        ; /AMPUSER/<ext>/vmx/[busy|unavail]/repeat:                                                             n (times to repeat message)
                        ; /AMPUSER/<ext>/vmx/[busy|unavail]/timeout:                                                    n (timeout to wait for digit)
                        ; /AMPUSER/<ext>/vmx/[busy|unavail]/loops:                                                              n (loop returies for invalid entries)
                        ; /AMPUSER/<ext>/vmx/[busy|unavail]/vmxopts/dovm:                                       vmoptions (if ext is dovm)
                        ; /AMPUSER/<ext>/vmx/[busy|unavail]/vmxopts/timeout:                    vmoptions (if timeout)
                        ; /AMPUSER/<ext>/vmx/[busy|unavail]/vmxopts/loops:                              vmoptions (if loops)
                        ; /AMPUSER/<ext>/vmx/[busy|unavail]/timedest/ext:                                       extension (if timeout)
                        ; /AMPUSER/<ext>/vmx/[busy|unavail]/timedest/context:                   context (if timeout)
                        ; /AMPUSER/<ext>/vmx/[busy|unavail]/timedest/pri:                                       priority (if timeout)
                        ; /AMPUSER/<ext>/vmx/[busy|unavail]/loopdest/ext:                                       extension (if too many failures)
                        ; /AMPUSER/<ext>/vmx/[busy|unavail]/loopdest/context:                   context (if too many failures)
                        ; /AMPUSER/<ext>/vmx/[busy|unavail]/loopdest/pri:                                       priority (if too many failures)
                        ; /AMPUSER/<ext>/vmx/[busy|unavail]/[0-9*]/ext:                                         extension (dovm for vm access)
                        ; /AMPUSER/<ext>/vmx/[busy|unavail]/[0-9*]/context:                             context
                        ; /AMPUSER/<ext>/vmx/[busy|unavail]/[0-9*]/pri:                                         priority
                        ;------------------------------------------------------------------------

            */
            // ARG1 - extension
            // ARG2 - DIRECTDIAL/BUSY
            // ARG3 - RETURN makes macro return, otherwise hangup
            //
            $ext->add('macro-vm', 's', '', new ext_macro('user-callerid', 'SKIPTTL'));
            $ext->add('macro-vm','s', '', new ext_setvar("VMGAIN", '${IF($["foo${VM_GAIN}"!="foo"]?"g(${VM_GAIN})": )}'));

            // If blkvm-check is set TRUE, then someone told us to block calls from going to
            // voicemail. This variable is reset by the answering channel so subsequent
            // transfers will properly function.
            //
            $ext->add('macro-vm','s', '', new ext_macro('blkvm-check'));
            $ext->add('macro-vm','s', '', new ext_gotoif('$["${GOSUB_RETVAL}" != "TRUE"]','vmx,1'));

            // we didn't branch so block this from voicemail
            //
            $ext->add('macro-vm','s', '', new ext_noop_trace('CAME FROM: ${NODEST} - Blocking VM macro-blkvm-check returned TRUE'));
            $ext->add('macro-vm','s', '', new ext_hangup(''));

            // If vmx not enabled for the current mode,then jump to normal voicemail behavior
            // also - if not message (no-msg) is requested, straight to voicemail
            //

            $ext->add('macro-vm','vmx', '', new ext_setvar("MEXTEN", '${ARG1}'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("MMODE", '${ARG2}'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("RETVM", '${ARG3}'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("MODE", '${IF($["${MMODE}"="BUSY"]?busy:unavail)}'));

      // If this use has individual option set for playing standardized message, then override the global option
      // but only if the vmx state is 'enabled'
            //
            $ext->add('macro-vm','vmx', '', new ext_gotoif('$["${DB(AMPUSER/${MEXTEN}/vmx/${MODE}/state)}" != "enabled"]','chknomsg'));
            /* Replaced
            $ext->add('macro-vm','vmx', '', new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/vmxopts/timeout)}" = "0"]','chknomsg'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("VM_OPTS", '${DB_RESULT}'));
             */
            $ext->add('macro-vm','vmx', '', new ext_set('VM_OPTS', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/vmxopts/timeout)}" = "1"]?${DB_RESULT}:${VM_OPTS})}'));
            $ext->add('macro-vm','vmx', 'chknomsg', new ext_gotoif('$["${MMODE}"="NOMESSAGE"]','s-${MMODE},1'));
            $ext->add('macro-vm','vmx', '', new ext_gotoif('$["${MMODE}" != "DIRECTDIAL"]','notdirect'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("MODE", '${IF($["${REGEX("[b]" ${VM_DDTYPE})}" = "1"]?busy:${MODE})}'));
            $ext->add('macro-vm','vmx', 'notdirect', new ext_NoOp('Checking if ext ${MEXTEN} is enabled: ${DB(AMPUSER/${MEXTEN}/vmx/${MODE}/state)}'));
            $ext->add('macro-vm','vmx', '', new ext_gotoif('$["${DB(AMPUSER/${MEXTEN}/vmx/${MODE}/state)}" != "enabled"]','s-${MMODE},1'));

            // If the required voicemail file does not exist, then abort and go to normal voicemail behavior
            //
            // If 1.4 or above, use the STAT function to check for the file. Prior to 1.4, use the AGI script since the System() command tried
            // in the past had errors.
            //
            $ext->add('macro-vm', 'vmx', '', new ext_macro('get-vmcontext', '${MEXTEN}'));
            //$ext->add('macro-vm', 'vmx', '', new ext_trysystem('/bin/ls ${ASTSPOOLDIR}/voicemail/${VMCONTEXT}/${MEXTEN}/${MODE}.[wW][aA][vV]'));
            if ($ast_ge_14) {
                $ext->add('macro-vm','vmx', '', new ext_gotoif('$[(${STAT(f,${ASTSPOOLDIR}/voicemail/${VMCONTEXT}/${MEXTEN}/temp.wav)} = 1) || (${STAT(f,${ASTSPOOLDIR}/voicemail/${VMCONTEXT}/${MEXTEN}/temp.WAV)} = 1)]','tmpgreet'));
                $ext->add('macro-vm','vmx', '', new ext_gotoif('$[(${STAT(f,${ASTSPOOLDIR}/voicemail/${VMCONTEXT}/${MEXTEN}/${MODE}.wav)} = 0) && (${STAT(f,${ASTSPOOLDIR}/voicemail/${VMCONTEXT}/${MEXTEN}/${MODE}.WAV)} = 0)]','nofile'));
            } else {
                $ext->add('macro-vm', 'vmx', '',new ext_agi('checksound.agi,${ASTSPOOLDIR}/voicemail/${VMCONTEXT}/${MEXTEN}/temp'));
                $ext->add('macro-vm','vmx', '', new ext_gotoif('$["${SYSTEMSTATUS}" = "SUCCESS"]','tmpgreet'));
                $ext->add('macro-vm', 'vmx', '',new ext_agi('checksound.agi,${ASTSPOOLDIR}/voicemail/${VMCONTEXT}/${MEXTEN}/${MODE}'));
                $ext->add('macro-vm','vmx', '', new ext_gotoif('$["${SYSTEMSTATUS}" != "SUCCESS"]','nofile'));
            }

            $ext->add('macro-vm','vmx', '', new ext_setvar("LOOPCOUNT", '0'));
            /* Replaced
            $ext->add('macro-vm','vmx', '', new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/repeat)}" = "0"]','vmxtime'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("VMX_REPEAT", '${DB_RESULT}'));
             */
            $ext->add('macro-vm','vmx', '', new ext_set('VMX_REPEAT', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/repeat)}" = "1"]?${DB_RESULT}:${VMX_REPEAT})}'));

            /* Replaced
            $ext->add('macro-vm','vmx', 'vmxtime', new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/timeout)}" = "0"]','vmxloops'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("VMX_TIMEOUT", '${DB_RESULT}'));
             */
            $ext->add('macro-vm','vmx', 'vmxtime', new ext_set('VMX_TIMEOUT', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/timeout)}" = "1"]?${DB_RESULT}:${VMX_TIMEOUT})}'));

            /* Replaced
            $ext->add('macro-vm','vmx', 'vmxloops', new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/loops)}" = "0"]','vmxanswer'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("VMX_LOOPS", '${DB_RESULT}'));
             */
            $ext->add('macro-vm','vmx', 'vmxloops', new ext_set('VMX_LOOPS', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/loops)}" = "1"]?${DB_RESULT}:${VMX_LOOPS})}'));
            $ext->add('macro-vm','vmx','vmxanswer',new ext_answer(''));

            // Now play the users voicemail recording as the basis for their ivr, the Read command will repeat as needed and if it timesout
            // then we go to the timeout. Otherwise handle invalid options by looping until the limit until a valid option is played.
            //
            $ext->add('macro-vm','vmx','loopstart',new ext_read('ACTION', '${ASTSPOOLDIR}/voicemail/${VMCONTEXT}/${MEXTEN}/${MODE}', 1, 'skip', '${VMX_REPEAT}', '${VMX_TIMEOUT}'));
            $ext->add('macro-vm','vmx', '', new ext_gotoif('$["${EXISTS(${ACTION})}" = "1"]','checkopt'));

            // If we are here we timed out, go to the required destination
            //
            $ext->add('macro-vm','vmx', 'noopt', new ext_NoOp('Timeout: going to timeout dest'));
            // this is always set, if not it will default to no options
            $ext->add('macro-vm','vmx', '', new ext_set('VMX_OPTS', '${DB(AMPUSER/${MEXTEN}/vmx/${MODE}/vmxopts/timeout)}'));

            // TODO should we just go do the other sets and skip the complexity, will have to if we remove the globals since they will be gonein dotime
            $ext->add('macro-vm','vmx', 'chktime', new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/timedest/ext)}" = "0"]','dotime'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("VMX_TIMEDEST_EXT",'${DB_RESULT}'));
            /* this is the alternative if re the above TODO
            $ext->add('macro-vm','vmx', 'chktime', new ext_set('VMX_TIMEDEST_EXT', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/timedest/ext)}" = "1"]?${DB_RESULT}:${VMX_TIMEDEST_EXT})}'));
             */

            /* Replaced
            $ext->add('macro-vm','vmx', '', new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/timedest/context)}" = "0"]','timepri'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("VMX_TIMEDEST_CONTEXT",'${DB_RESULT}'));
             */
            $ext->add('macro-vm','vmx', '', new ext_set('VMX_TIMEDEST_CONTEXT', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/timedest/context)}" = "1"]?${DB_RESULT}:${VMX_TIMEDEST_CONTEXT})}'));

            /* Replaced
            $ext->add('macro-vm','vmx', 'timepri', new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/timedest/pri)}" = "0"]','dotime'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("VMX_TIMEDEST_PRI",'${DB_RESULT}'));
             */
            $ext->add('macro-vm','vmx', 'timepri', new ext_set('VMX_TIMEDEST_PRI', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/timedest/pri)}" = "1"]?${DB_RESULT}:${VMX_TIMEDEST_PRI})}'));

            $ext->add('macro-vm','vmx','dotime',new ext_goto('${VMX_TIMEDEST_PRI}', '${VMX_TIMEDEST_EXT}', '${VMX_TIMEDEST_CONTEXT}'));

            // We got an option, check if the option is defined, or one of the system defaults
            //
            $ext->add('macro-vm','vmx', 'checkopt', new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/${ACTION}/ext)}" = "1"]','doopt'));
            $ext->add('macro-vm','vmx', '', new ext_gotoif('$["${ACTION}" = "0"]','o,1'));
            $ext->add('macro-vm','vmx', '', new ext_gotoif('$["${ACTION}" = "*"]','adef,1'));

            // Got invalid option loop until the max
            //
            $ext->add('macro-vm','vmx', '', new ext_setvar("LOOPCOUNT",'$[${LOOPCOUNT} + 1]'));
            $ext->add('macro-vm','vmx', '', new ext_gotoif('$[${LOOPCOUNT} > ${VMX_LOOPS}]','toomany'));
            $ext->add('macro-vm','vmx','',new ext_playback('pm-invalid-option&please-try-again'));
            $ext->add('macro-vm','vmx','',new ext_goto('loopstart'));

            // tomany: to many invalid options, go to the specified destination
            //
            $ext->add('macro-vm','vmx', 'toomany', new ext_NoOp('Too Many invalid entries, got to invalid dest'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("VMX_OPTS",'${VMX_OPTS_LOOP}'));
            /* Replaced
            $ext->add('macro-vm','vmx', '', new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/vmxopts/loops)}" = "0"]','chkloop'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("VMX_OPTS",'${DB_RESULT}'));
             */
            $ext->add('macro-vm','vmx', '', new ext_set('VMX_OPTS', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/vmxopts/loops)}" = "1"]?${DB_RESULT}:${VMX_OPTS})}'));

            // TODO: same as above, if we just set them then we don't depend on the globals at doloop
            $ext->add('macro-vm','vmx', 'chkloop', new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/loopdest/ext)}" = "0"]','doloop'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("VMX_LOOPDEST_EXT",'${DB_RESULT}'));
            /* this would go with the above TODO
            $ext->add('macro-vm','vmx', 'chkloop', new ext_set('VMX_LOOPDEST_EXT', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/loopdest/ext)}" = "1"]?${DB_RESULT}:${VMX_LOOPDEST_EXT})}'));
             */

            /* Replaced
            $ext->add('macro-vm','vmx', '', new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/loopdest/context)}" = "0"]','looppri'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("VMX_LOOPDEST_CONTEXT",'${DB_RESULT}'));
             */
            $ext->add('macro-vm','vmx', '', new ext_set('VMX_LOOPDEST_CONTEXT', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/loopdest/context)}" = "1"]?${DB_RESULT}:${VMX_LOOPDEST_CONTEXT})}'));

            /* Replaced
            $ext->add('macro-vm','vmx', 'looppri', new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/loopdest/pri)}" = "0"]','doloop'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("VMX_LOOPDEST_PRI",'${DB_RESULT}'));
             */
            $ext->add('macro-vm','vmx', 'looppri', new ext_set('VMX_LOOPDEST_PRI', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/loopdest/pri)}" = "1"]?${DB_RESULT}:${VMX_LOOPDEST_PRI})}'));

            $ext->add('macro-vm','vmx','doloop',new ext_goto('${VMX_LOOPDEST_PRI}','${VMX_LOOPDEST_EXT}','${VMX_LOOPDEST_CONTEXT}'));

            // doopt: execute the valid option that was chosen
            //
            $ext->add('macro-vm','vmx', 'doopt', new ext_NoOp('Got a valid option: ${DB_RESULT}'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("VMX_EXT",'${DB_RESULT}'));

            // Special case, if this option was to go to voicemail, set options and go
            //
            $ext->add('macro-vm','vmx', '', new ext_gotoif('$["${VMX_EXT}" != "dovm"]','getdest'));
            /* Replaced
            $ext->add('macro-vm','vmx', 'vmxopts', new ext_setvar("VMX_OPTS",'${VMX_OPTS_DOVM}'));
            $ext->add('macro-vm','vmx', '', new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/vmxopts/dovm)}" = "0"]','vmxdovm'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("VMX_OPTS",'${DB_RESULT}'));
             */
            $ext->add('macro-vm','vmx', 'vmxopts', new ext_set('VMX_OPTS', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/vmxopts/dovm)}" = "1"]?${DB_RESULT}:${VMX_OPTS_DOVM})}'));

            $ext->add('macro-vm','vmx','vmxdovm',new ext_goto('1','dovm'));

            // General case, setup the goto destination and go there (no error checking, its up to the GUI's to assure
            // reasonable values
            //
            /* Replaced
            $ext->add('macro-vm','vmx', 'getdest', new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/${ACTION}/context)}" = "0"]','vmxpri'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("VMX_CONTEXT",'${DB_RESULT}'));
             */
            $ext->add('macro-vm','vmx', 'getdest', new ext_set('VMX_CONTEXT', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/${ACTION}/context)}" = "1"]?${DB_RESULT}:${VMX_CONTEXT})}'));

            /* Replaced
            $ext->add('macro-vm','vmx', 'vmxpri', new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/${ACTION}/pri)}" = "0"]','vmxgoto'));
            $ext->add('macro-vm','vmx', '', new ext_setvar("VMX_PRI",'${DB_RESULT}'));
             */
            $ext->add('macro-vm','vmx', 'vmxpri', new ext_set('VMX_PRI', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/${ACTION}/pri)}" = "1"]?${DB_RESULT}:${VMX_PRI})}'));

            $ext->add('macro-vm','vmx','vmxgoto',new ext_goto('${VMX_PRI}','${VMX_EXT}','${VMX_CONTEXT}'));

            // If the required voicemail file is not present, then revert to normal voicemail
            // behavior treating as if it was not set
            //
            $ext->add('macro-vm','vmx', 'nofile', new ext_NoOp('File for mode: ${MODE} does not exist, SYSTEMSTATUS: ${SYSTEMSTATUS}, going to normal voicemail'));
            $ext->add('macro-vm','vmx','',new ext_goto('1','s-${MMODE}'));
            $ext->add('macro-vm','vmx', 'tmpgreet', new ext_NoOp('Temporary Greeting Detected, going to normal voicemail'));
            $ext->add('macro-vm','vmx','',new ext_goto('1','s-${MMODE}'));

            // Drop into voicemail either as a direct destination (in which case VMX_OPTS might be set to something) or
            // if the user timed out or broke out of the loop then VMX_OPTS is always cleared such that an Allison
            // message is played and the caller know's what is going on.
            //
            $ext->add('macro-vm','dovm', '', new ext_NoOp('VMX Timeout - go to voicemail'));
            $ext->add('macro-vm','dovm', '',new ext_vm('${MEXTEN}@${VMCONTEXT},${VMX_OPTS}${VMGAIN}'));
            $ext->add('macro-vm','dovm', '',new ext_goto('1','exit-${VMSTATUS}'));

            $ext->add('macro-vm','s-BUSY','',new ext_NoOp('BUSY voicemail'));
            $ext->add('macro-vm','s-BUSY','',new ext_macro('get-vmcontext','${MEXTEN}'));
            $ext->add('macro-vm','s-BUSY', '',new ext_vm('${MEXTEN}@${VMCONTEXT},${VM_OPTS}b${VMGAIN}'));
            $ext->add('macro-vm','s-BUSY', '',new ext_goto('1','exit-${VMSTATUS}'));

            $ext->add('macro-vm','s-NOMESSAGE','',new ext_NoOp('NOMESSAGE (beeb only) voicemail'));
            $ext->add('macro-vm','s-NOMESSAGE','',new ext_macro('get-vmcontext','${MEXTEN}'));
            $ext->add('macro-vm','s-NOMESSAGE','',new ext_vm('${MEXTEN}@${VMCONTEXT},s${VM_OPTS}${VMGAIN}'));
            $ext->add('macro-vm','s-NOMESSAGE','',new ext_goto('1','exit-${VMSTATUS}'));

            $ext->add('macro-vm','s-DIRECTDIAL','',new ext_NoOp('DIRECTDIAL voicemail'));
            $ext->add('macro-vm','s-DIRECTDIAL','',new ext_macro('get-vmcontext','${MEXTEN}'));
            $ext->add('macro-vm','s-DIRECTDIAL','',new ext_vm('${MEXTEN}@${VMCONTEXT},${VM_OPTS}${VM_DDTYPE}${VMGAIN}'));
            $ext->add('macro-vm','s-DIRECTDIAL','',new ext_goto('1','exit-${VMSTATUS}'));

            $ext->add('macro-vm','_s-.','',new ext_macro('get-vmcontext','${MEXTEN}'));
            $ext->add('macro-vm','_s-.','',new ext_vm('${MEXTEN}@${VMCONTEXT},${VM_OPTS}u${VMGAIN}'));
            $ext->add('macro-vm','_s-.','',new ext_goto('1','exit-${VMSTATUS}'));

            // If the user has a 0 option defined, use that for operator zero-out from within voicemail
            // as well to keep it consistant with the menu structure
            //
            $ext->add('macro-vm','o','',new ext_playback('one-moment-please'));
            $ext->add('macro-vm','o','',new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/0/ext)}" = "0"]','doopdef'));
            $ext->add('macro-vm','o','',new ext_setvar("VMX_OPDEST_EXT",'${DB_RESULT}'));

            /* Replaced
            $ext->add('macro-vm','o','',new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/0/context)}" = "1"]','opcontext'));
            $ext->add('macro-vm','o','',new ext_setvar("DB_RESULT",'${VMX_CONTEXT}'));
            $ext->add('macro-vm','o','opcontext',new ext_setvar("VMX_OPDEST_CONTEXT",'${DB_RESULT}'));
             */
            $ext->add('macro-vm','o', 'opcontext', new ext_set('VMX_OPDEST_CONTEXT', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/0/context)}" = "1"]?${DB_RESULT}:${VMX_CONTEXT})}'));

            /* Replaced
            $ext->add('macro-vm','o','',new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/0/pri)}" = "1"]','oppri'));
            $ext->add('macro-vm','o','',new ext_setvar("DB_RESULT",'${VMX_PRI}'));
            $ext->add('macro-vm','o','oppri',new ext_setvar("VMX_OPDEST_PRI",'${DB_RESULT}'));
             */
            $ext->add('macro-vm','o', 'oppri', new ext_set('VMX_OPDEST_PRI', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/0/pri)}" = "1"]?${DB_RESULT}:${VMX_PRI})}'));

            $ext->add('macro-vm','o','',new ext_goto('${VMX_OPDEST_PRI}','${VMX_OPDEST_EXT}','${VMX_OPDEST_CONTEXT}'));
            $ext->add('macro-vm','o','doopdef',new ext_gotoif('$["x${OPERATOR_XTN}"="x"]','nooper','from-internal,${OPERATOR_XTN},1'));
            $ext->add('macro-vm','o','nooper',new ext_gotoif('$["x${FROM_DID}"="x"]','nodid'));
            $ext->add('macro-vm','o','',new ext_dial('Local/${FROM_DID}@from-pstn',''));
            $ext->add('macro-vm','o','',new ext_macro('hangup'));
            $ext->add('macro-vm','o','nodid',new ext_dial('Local/s@from-pstn',''));
            $ext->add('macro-vm','o','',new ext_macro('hangup'));

            // If the user has a * option defined, use that for the * out from within voicemail
            // as well to keep it consistant with the menu structure
            //
            $ext->add('macro-vm','a','',new ext_macro('get-vmcontext','${MEXTEN}'));
            $ext->add('macro-vm','a','',new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/*/ext)}" = "0"]','adef,1'));
            $ext->add('macro-vm','a','',new ext_setvar("VMX_ADEST_EXT",'${DB_RESULT}'));

            // Replaced
            //$ext->add('macro-vm','a','',new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/*/context)}" = "1"]','acontext'));
            //$ext->add('macro-vm','a','',new ext_setvar("DB_RESULT",'${VMX_CONTEXT}'));
            //$ext->add('macro-vm','a','acontext',new ext_setvar("VMX_ADEST_CONTEXT",'${DB_RESULT}'));
            $ext->add('macro-vm','a','acontext', new ext_set('VMX_ADEST_CONTEXT', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/*/context)}" = "1"]?${DB_RESULT}:${VMX_CONTEXT})}'));

            // Replaced
            //$ext->add('macro-vm','a','',new ext_gotoif('$["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/*/pri)}" = "1"]','apri'));
            //$ext->add('macro-vm','a','',new ext_setvar("DB_RESULT",'${VMX_PRI}'));
            //$ext->add('macro-vm','a','apri',new ext_setvar("VMX_ADEST_PRI",'${DB_RESULT}'));
            $ext->add('macro-vm','a','apri', new ext_set('VMX_ADEST_PRI', '${IF($["${DB_EXISTS(AMPUSER/${MEXTEN}/vmx/${MODE}/*/pri)}" = "1"]?${DB_RESULT}:${VMX_PRI})}'));
            $ext->add('macro-vm','a','',new ext_goto('${VMX_ADEST_PRI}','${VMX_ADEST_EXT}','${VMX_ADEST_CONTEXT}'));
            $ext->add('macro-vm','adef','',new ext_vmmain('${MEXTEN}@${VMCONTEXT}'));
            $ext->add('macro-vm','adef','',new ext_gotoif('$["${RETVM}" = "RETURN"]','exit-RETURN,1'));
            $ext->add('macro-vm','adef','',new ext_hangup(''));

            $ext->add('macro-vm','exit-FAILED','',new ext_playback('im-sorry&an-error-has-occured'));
            $ext->add('macro-vm','exit-FAILED','',new ext_gotoif('$["${RETVM}" = "RETURN"]','exit-RETURN,1'));
            $ext->add('macro-vm','exit-FAILED','',new ext_hangup(''));

            $ext->add('macro-vm','exit-SUCCESS','',new ext_gotoif('$["${RETVM}" = "RETURN"]','exit-RETURN,1'));
            $ext->add('macro-vm','exit-SUCCESS','',new ext_playback('goodbye'));
            $ext->add('macro-vm','exit-SUCCESS','',new ext_hangup(''));

            $ext->add('macro-vm','exit-USEREXIT','',new ext_gotoif('$["${RETVM}" = "RETURN"]','exit-RETURN,1'));
            $ext->add('macro-vm','exit-USEREXIT','',new ext_playback('goodbye'));
            $ext->add('macro-vm','exit-USEREXIT','',new ext_hangup(''));

            $ext->add('macro-vm','exit-RETURN','',new ext_noop('Returning From Voicemail because macro'));
            $ext->add('macro-vm','t','',new ext_hangup(''));

            /* end macro-vm  */

      /*
       * ARG1: VMBOX
       * ARG2: EXTTOCALL
       * ARG3: If NOANSWER dest exists 1, otherwise 0
       * ARG4: If BUSY dest exists 1, otherwise 0
       * ARG5: If CHANUNAVAIL dest exists 1, otherwise 0
       */
      $mcontext = 'macro-exten-vm';
      $exten = 's';

            $ext->add($mcontext,$exten,'', new ext_macro('user-callerid'));
            $ext->add($mcontext,$exten,'', new ext_set("RingGroupMethod", 'none'));
            $ext->add($mcontext,$exten,'', new ext_set("__EXTTOCALL", '${ARG2}'));
            $ext->add($mcontext,$exten,'', new ext_set("__PICKUPMARK", '${ARG2}'));
            $ext->add($mcontext,$exten,'', new ext_set("RT", '${IF($["${ARG1}"!="novm" | "${DB(CFU/${EXTTOCALL})}"!="" | "${DB(CFB/${EXTTOCALL})}"!="" | "${ARG3}"="1" | "${ARG4}"="1" | "${ARG5}"="1"]?${RINGTIMER}:)}'));
            $ext->add($mcontext,$exten,'checkrecord', new ext_gosub('1','s','sub-record-check','exten,${EXTTOCALL},'));

      // If paging module is not present, then what happens?
      // TODO: test with no paging module
            $fcc = new featurecode('paging', 'intercom-prefix');
            $intercom_code = $fcc->getCodeActive();
            unset($fcc);

      // I think it is adequate that if AMPUSER is blank, it's not internal (don't think FROM_DID has to be checked though I don't think it hurts)
      $macrodial = 'macrodial';
      if ($intercom_code != '') {
        if ($amp_conf['AST_FUNC_EXTENSION_STATE']) {
          $ext->add($mcontext,$exten,'', new ext_noop_trace('AMPUSER: ${AMPUSER}, FROM_DID: ${FROM_DID}, answermode: ${DB(AMPUSER/${EXTTOCALL}/answermode)}, BLINDTXF: ${BLINDTRANSFER}, EXT_STATE: ${EXTENSION_STATE(${EXTTOCALL})}, CC_RECALL: ${CC_RECALL}'));
          if ($amp_conf['FORCE_INTERNAL_AUTO_ANSWER_ALL']) {
            $ext->add($mcontext,$exten,'',new ext_gotoif('$["${AMPUSER}"=""|${LEN(${FROM_DID})}|${LEN(${BLINDTRANSFER})}|"${EXTENSION_STATE(${EXTTOCALL})}"!="NOT_INUSE"|"${CC_RECALL}"!=""]','macrodial'));
          } else {
            $ext->add($mcontext,$exten,'',new ext_gotoif('$["${AMPUSER}"=""|${LEN(${FROM_DID})}|"${DB(AMPUSER/${EXTTOCALL}/answermode)}"!="intercom"|${LEN(${BLINDTRANSFER})}|"${EXTENSION_STATE(${EXTTOCALL})}"!="NOT_INUSE"|"${CC_RECALL}"!=""]','macrodial'));
          }
        } else {
          $ext->add($mcontext,$exten,'', new ext_noop_trace('AMPUSER: ${AMPUSER}, FROM_DID: ${FROM_DID}, answermode: ${DB(AMPUSER/${EXTTOCALL}/answermode)}, BLINDTXF: ${BLINDTRANSFER}, CC_RECALL: ${CC_RECALL}'));
          if ($amp_conf['FORCE_INTERNAL_AUTO_ANSWER_ALL']) {
            $ext->add($mcontext,$exten,'',new ext_gotoif('$["${AMPUSER}"=""|${LEN(${FROM_DID})}|${LEN(${BLINDTRANSFER})}]','macrodial'));
          } else {
            $ext->add($mcontext,$exten,'',new ext_gotoif('$["${AMPUSER}"=""|${LEN(${FROM_DID})}|"${DB(AMPUSER/${EXTTOCALL}/answermode)}"!="intercom"|${LEN(${BLINDTRANSFER})}]','macrodial'));
          }
        }
        $ext->add($mcontext,$exten,'', new ext_set("INTERCOM_EXT_DOPTIONS", '${DIAL_OPTIONS}'));
        $ext->add($mcontext,$exten,'', new ext_set("INTERCOM_RETURN", 'TRUE'));
              $ext->add($mcontext,$exten,'', new ext_gosub('1',$intercom_code.'${EXTTOCALL}','ext-intercom'));
        $ext->add($mcontext,$exten,'', new ext_set("INTERCOM_RETURN", ''));
        $ext->add($mcontext,$exten,'', new ext_set("INTERCOM_EXT_DOPTIONS", ''));

        // If it was a blind transfer and there was a previous auto-answer, then we cleanup all the auto-answer headers left in the channel
        // It won't be from this call because we don't ever intercom in a blind transfer scenario (hmm unless it was blind transfered to a
        // specific intercom code but in that case, they won't have been able to subsequently transfered the call
        //
        $ext->add($mcontext,$exten,$macrodial, new ext_gosubif('$["${INTERCOM_CALL}"="TRUE" & ${LEN(${BLINDTRANSFER})}]','clrheader,1'));
        $macrodial = '';
      }
      if ($amp_conf['AST_FUNC_EXTENSION_STATE']) {
              $ext->add($mcontext,$exten,$macrodial, new ext_macro('dial-one','${RT},${DIAL_OPTIONS},${EXTTOCALL}'));
      } else {
              $ext->add($mcontext,$exten,$macrodial, new ext_macro('dial','${RT},${DIAL_OPTIONS},${EXTTOCALL}'));
      }
            $ext->add($mcontext,$exten,'', new ext_set("SV_DIALSTATUS", '${DIALSTATUS}'));

            $ext->add($mcontext,$exten,'calldocfu', new ext_gosubif('$[("${SV_DIALSTATUS}"="NOANSWER"|"${SV_DIALSTATUS}"="CHANUNAVAIL") & "${DB(CFU/${EXTTOCALL})}"!="" & "${SCREEN}"=""]','docfu,1'));
            $ext->add($mcontext,$exten,'calldocfb', new ext_gosubif('$["${SV_DIALSTATUS}"="BUSY" & "${DB(CFB/${EXTTOCALL})}"!="" & "${SCREEN}"=""]','docfb,1'));
            $ext->add($mcontext,$exten,'', new ext_set("DIALSTATUS", '${SV_DIALSTATUS}'));

            $ext->add($mcontext,$exten,'', new ext_execif('$[("${DIALSTATUS}"="NOANSWER"&"${ARG3}"="1")|("${DIALSTATUS}"="BUSY"&"${ARG4}"="1")|("${DIALSTATUS}"="CHANUNAVAIL"&"${ARG5}"="1")]','MacroExit'));

            $ext->add($mcontext,$exten,'', new ext_noop_trace('Voicemail is \'${ARG1}\'',1));
            $ext->add($mcontext,$exten,'',new ext_gotoif('$["${ARG1}"="novm"]','s-${DIALSTATUS},1'));
            $ext->add($mcontext,$exten,'', new ext_noop_trace('Sending to Voicemail box ${EXTTOCALL}',1));
            $ext->add($mcontext,$exten,'', new ext_macro('vm','${ARG1},${DIALSTATUS},${IVR_RETVM}'));

      $exten = 'docfu';
            if ($amp_conf['DIVERSIONHEADER']) $ext->add($mcontext,$exten,'', new ext_set('__DIVERSION_REASON', 'unavailable'));
      $ext->add($mcontext,$exten,'docfu', new ext_execif('$["${DB(AMPUSER/${EXTTOCALL}/cfringtimer)}"="-1"|("${ARG1}"="novm"&"${ARG3}"="1")]', 'StackPop'));
      $ext->add($mcontext,$exten,'', new ext_gotoif('$["${DB(AMPUSER/${EXTTOCALL}/cfringtimer)}"="-1"|("${ARG1}"="novm"&"${ARG3}"="1")]', 'from-internal,${DB(CFU/${EXTTOCALL})},1'));
      $ext->add($mcontext,$exten,'', new ext_set("RTCF", '${IF($["${DB(AMPUSER/${EXTTOCALL}/cfringtimer)}"="0"]?${RT}:${DB(AMPUSER/${EXTTOCALL}/cfringtimer)})}'));
            $ext->add($mcontext,$exten,'', new ext_execif('$["${DIRECTION}" = "INBOUND"]', 'Set', 'DIAL_OPTIONS=${STRREPLACE(DIAL_OPTIONS,T)}'));
            $ext->add($mcontext,$exten,'', new ext_dial('Local/${DB(CFU/${EXTTOCALL})}@from-internal/n', '${RTCF},${DIAL_OPTIONS}'));
            if ($amp_conf['DIVERSIONHEADER']) $ext->add($mcontext,$exten,'', new ext_set('__DIVERSION_REASON', ''));
            $ext->add($mcontext,$exten,'', new ext_return(''));

      $exten = 'docfb';
            if ($amp_conf['DIVERSIONHEADER']) $ext->add($mcontext,$exten,'', new ext_set('__DIVERSION_REASON', 'user-busy'));
      $ext->add($mcontext,$exten,'docfu', new ext_execif('$["${DB(AMPUSER/${EXTTOCALL}/cfringtimer)}"="-1"|("${ARG1}"="novm"&"${ARG4}"="1")]', 'StackPop'));
      $ext->add($mcontext,$exten,'', new ext_gotoif('$["${DB(AMPUSER/${EXTTOCALL}/cfringtimer)}"="-1"|("${ARG1}"="novm"&"${ARG4}"="1")]', 'from-internal,${DB(CFB/${EXTTOCALL})},1'));
      $ext->add($mcontext,$exten,'', new ext_set("RTCF", '${IF($["${DB(AMPUSER/${EXTTOCALL}/cfringtimer)}"="0"]?${RT}:${DB(AMPUSER/${EXTTOCALL}/cfringtimer)})}'));
            $ext->add($mcontext,$exten,'', new ext_execif('$["${DIRECTION}" = "INBOUND"]', 'Set', 'DIAL_OPTIONS=${STRREPLACE(DIAL_OPTIONS,T)}'));
            $ext->add($mcontext,$exten,'', new ext_dial('Local/${DB(CFB/${EXTTOCALL})}@from-internal/n', '${RTCF},${DIAL_OPTIONS}'));
            if ($amp_conf['DIVERSIONHEADER']) $ext->add($mcontext,$exten,'', new ext_set('__DIVERSION_REASON', ''));
            $ext->add($mcontext,$exten,'', new ext_return(''));

      // If we are here it was determined that there had been intercom sip headers left over in the channel. If 1.6.2+ then we can use
      // the SIPRemoveHeader() option to remove the specific headers. We are trying to be careful not to remove similar headers that
      // may be used for 'distinctive ring' type reasons from elsewhere in the dialplan. Thus only if we detected the intercom situation
      // do we do it here.
      //
      // If we are pre 1.6.2 then some experimentation on 1.4.X has shown that we are able to clear the SIPADDHEADERnn channel variables
      // that result from setting the headers so we start from 1 (the first) and iterate up until we find one. In some weird situations
      // if a header had been removed, we could miss out since it is not possible to detect the existence of a blank channel variable
      //
      if ($intercom_code != '') {
        $exten = 'clrheader';
        $ext->add($mcontext, $exten, '', new ext_execif('$[${LEN(${SIPURI})}&"${SIPURI}"="${SIP_URI_OPTIONS}"]', 'Set','SIP_URI_OPTIONS='));
        if ($ast_ge_162) {
          $ext->add($mcontext, $exten, '', new ext_execif('$[${LEN(${ALERTINFO})}]', 'SIPRemoveHeader','${ALERTINFO}'));
          $ext->add($mcontext, $exten, '', new ext_execif('$[${LEN(${CALLINFO})}]', 'SIPRemoveHeader','${CALLINFO}'));
        } else {
          $ext->add($mcontext, $exten, '', new ext_set('SP', '0'));
          $ext->add($mcontext, $exten, '', new ext_set('ITER', '1'));
          $ext->add($mcontext, $exten, 'begin', new ext_execif('$[${ITER} > 9]', 'Set','SP=' ));
          $ext->add($mcontext, $exten, '', new ext_execif('$[${LEN(${SIPADDHEADER${SP}${ITER}})}=0]', 'Return'));
          $ext->add($mcontext, $exten, '', new ext_execif('$["${SIPADDHEADER${SP}${ITER}}"="${ALERTINFO}"|"${SIPADDHEADER${SP}${ITER}}"="${CALLINFO}"]', 'Set','SIPADDHEADER${SP}${ITER}='));
          $ext->add($mcontext, $exten, '', new ext_setvar('ITER', '$[${ITER} + 1]'));
          $ext->add($mcontext, $exten, '', new ext_gotoif('$[${ITER} < 100]', 'begin'));
        }
              $ext->add($mcontext,$exten,'', new ext_return(''));
      }

      $exten = 's-BUSY';
            $ext->add($mcontext,$exten,'', new ext_noop_trace('Extension is reporting BUSY and not passing to Voicemail',1));
            $ext->add($mcontext,$exten,'', new ext_gotoif('$["${IVR_RETVM}"="RETURN" & "${IVR_CONTEXT}"!=""]','exit,1'));
            $ext->add($mcontext,$exten, '', new ext_playtones('busy'));
            $ext->add($mcontext,$exten, '', new ext_busy(20));

      $exten = '_s-!';
            $ext->add($mcontext,$exten,'', new ext_noop_trace('IVR_RETVM: ${IVR_RETVM} IVR_CONTEXT: ${IVR_CONTEXT}',1));
            $ext->add($mcontext,$exten,'', new ext_gotoif('$["${IVR_RETVM}"="RETURN" & "${IVR_CONTEXT}"!=""]','exit,1'));
            $ext->add($mcontext,$exten,'', new ext_playtones('congestion'));
      $ext->add($mcontext,$exten,'', new ext_congestion('10'));

      $exten = 'exit';
      $ext->add($mcontext,$exten,'', new ext_playback('beep&line-busy-transfer-menu&silence/1'));
      $ext->add($mcontext,$exten,'', new ext_macroexit());

            /* macro-exten-vm  */


      /*
      ;------------------------------------------------------------------------
      ; [macro-simple-dial]
      ;------------------------------------------------------------------------
      ; This macro was derived from macro-exten-vm, which is what is normally used to
      ; ring an extension. It has been simplified and designed to never go to voicemail
      ; and always return regardless of the DIALSTATUS for any incomplete call.
      ;
      ; It's current primary purpose is to allow findmefollow ring an extension prior
      ; to trying the follow-me ringgroup that is provided.
      ;
      ; Ring an extension, if the extension is busy or there is no answer, return
      ; ARGS: $EXTENSION, $RINGTIME
      ;------------------------------------------------------------------------
      */
      $mcontext = 'macro-simple-dial';
      $exten = 's';
            $ext->add($mcontext,$exten,'', new ext_set("__EXTTOCALL", '${ARG1}'));
            $ext->add($mcontext,$exten,'', new ext_set("RT", '${ARG2}'));
            $ext->add($mcontext,$exten,'', new ext_set("CFUEXT", '${DB(CFU/${EXTTOCALL})}'));
            $ext->add($mcontext,$exten,'', new ext_set("CFBEXT", '${DB(CFB/${EXTTOCALL})}'));
            $ext->add($mcontext,$exten,'', new ext_set("CWI_TMP", '${CWIGNORE}'));
      if ($amp_conf['AST_FUNC_EXTENSION_STATE']) {
              $ext->add($mcontext,$exten,'macrodial', new ext_macro('dial-one','${RT},${DIAL_OPTIONS},${EXTTOCALL}'));
      } else {
              $ext->add($mcontext,$exten,'macrodial', new ext_macro('dial','${RT},${DIAL_OPTIONS},${EXTTOCALL}'));
      }
            $ext->add($mcontext,$exten,'', new ext_set("__CWIGNORE", '${CWI_TMP}'));
            $ext->add($mcontext,$exten,'', new ext_set("PR_DIALSTATUS", '${DIALSTATUS}'));
            $ext->add($mcontext,$exten,'calldocfu', new ext_gosubif('$["${PR_DIALSTATUS}"="NOANSWER" & "${CFUEXT}"!=""]','docfu,1'));
            $ext->add($mcontext,$exten,'calldocfb', new ext_gosubif('$["${PR_DIALSTATUS}"="BUSY" & "${CFBEXT}"!=""]','docfb,1'));
            $ext->add($mcontext,$exten,'', new ext_set("DIALSTATUS", '${PR_DIALSTATUS}'));
            $ext->add($mcontext,$exten,'',new ext_goto('1','s-${DIALSTATUS}'));

      /*
      ; Try the Call Forward on No Answer / Unavailable number.
      ; We want to try CFU if set, but we want the same ring timer as was set to our call (or do we want the
      ; system ringtimer? - probably not). Then if no answer there (assuming it doesn't drop into their vm or
      ; something we return, which will have the net effect of returning to the followme setup.)
      ;
      ; want to avoid going to other follow-me settings here. So check if the CFUEXT is a user and if it is
      ; then direct it straight to ext-local (to avoid getting intercepted by findmefollow) otherwise send it
      ; to from-internal since it may be an outside line.
      ;
      */
      $exten = 'docfu';
            $ext->add($mcontext,$exten,'', new ext_gotoif('$["${DB(AMPUSER/${CFUEXT}/device)}" = "" ]','chlocal'));
            $ext->add($mcontext,$exten,'', new ext_dial('Local/${CFUEXT}@ext-local', '${RT},${DIAL_OPTIONS}'));
            $ext->add($mcontext,$exten,'', new ext_return(''));
            $ext->add($mcontext,$exten,'chlocal', new ext_execif('$["${DIRECTION}" = "INBOUND"]', 'Set', 'DIAL_OPTIONS=${STRREPLACE(DIAL_OPTIONS,T)}'));
            $ext->add($mcontext,$exten,'', new ext_dial('Local/${CFUEXT}@from-internal/n', '${RT},${DIAL_OPTIONS}'));
            $ext->add($mcontext,$exten,'', new ext_return(''));

      $exten = 'docfb';
            $ext->add($mcontext,$exten,'', new ext_gotoif('$["${DB(AMPUSER/${CFBEXT}/device)}" = "" ]','chlocal'));
            $ext->add($mcontext,$exten,'', new ext_dial('Local/${CFBEXT}@ext-local', '${RT},${DIAL_OPTIONS}'));
            $ext->add($mcontext,$exten,'', new ext_return(''));
            $ext->add($mcontext,$exten,'chlocal', new ext_execif('$["${DIRECTION}" = "INBOUND"]', 'Set', 'DIAL_OPTIONS=${STRREPLACE(DIAL_OPTIONS,T)}'));
            $ext->add($mcontext,$exten,'', new ext_dial('Local/${CFBEXT}@from-internal/n', '${RT},${DIAL_OPTIONS}'));
            $ext->add($mcontext,$exten,'', new ext_return(''));

      /*
      ; In all cases of no connection, come here and simply return, since the calling dialplan will
      ; decide what to do next
      */
      $exten = '_s-.';
            $ext->add($mcontext,$exten,'', new ext_noop('Extension is reporting ${EXTEN}'));

            /* macro-simple-dial */


      /* macro-blkvm-setifempty
       * macro-blkvm-set
       * macro-blkvm-clr
       * macro-blkvm-check
       *
       * These macros are used to tell the voicemail system if it should answer a call or kill the call.
       * They are also used by modules like findmefollow and ringgroups to determine if a destination
       * if noanswer should be pursued, or if they should just end because they were called by a higher
       * level module who's destination should be honored. (Thus if vm should be blocked, so should
       * such destinations.
       *
       * In the past, it was necessary to create and track unique AstDB variables to track this since
       * it is necessary for a call that is answered, for example a queue memeber who answers a queue
       * call, to clr the block so that subsequent transfers to voicemail or user extensions which might
       * hit voicemail could succeed and the nature of Asterisk inheritable variable did not allow
       * this. This also meant that these needed to be cleaned up when the master channel who 'started
       * it all' ended, which is attempted in macro-hangupcall. There are still cases where cleanup
       * does not happen which can result in an accumulation of these.
       *
       * With the advent of the SHARED() channel variable starting in 1.6, we can achieve the same
       * thing with such a SHARED() channel variable which should be more efficient since it does not
       * hit the DB, but more importantly, there is no cleanup because the variable will die with the
       * owner channel.
       *
       * We check if the SHARED function is available and if so, we use that in our macro. If not, we
       * fall back to the shared DB variable and keep our cleanup code in hangupcall.
       *
       * Note that we have chosen to use a Macro() in place of a GoSub() because in the legacy DB
       * mode we must have the owning ${EXTEN} to create our unique key. Since GoSub() does not support
       * passing arguments until 1.6 this would not be possible in 1.4 which is still mainstream.
       * We have chosen to use the GOSUB_RETVAL in anticipation of a future point where we move to
       * a GoSub() call which would be slightly more efficient.
       */

      $exten = 's';
      if ($amp_conf['AST_FUNC_SHARED']) {

        // If it BLKVM_CHANNEL exists, return it's value. If not, then set it and return TRUE
        //
        $mcontext = 'macro-blkvm-setifempty';
        $ext->add($mcontext,$exten,'', new ext_gotoif('$[!${EXISTS(${BLKVM_CHANNEL})}]', 'init'));
        $ext->add($mcontext,$exten,'', new ext_set('GOSUB_RETVAL','${SHARED(BLKVM,${BLKVM_CHANNEL})}'));
        $ext->add($mcontext,$exten,'', new ext_macroexit(''));
        $ext->add($mcontext,$exten,'init', new ext_set('__BLKVM_CHANNEL','${CHANNEL}'));
        $ext->add($mcontext,$exten,'', new ext_set('SHARED(BLKVM,${BLKVM_CHANNEL})','TRUE'));
        $ext->add($mcontext,$exten,'', new ext_set('GOSUB_RETVAL','TRUE'));
        $ext->add($mcontext,$exten,'', new ext_macroexit(''));

        // If BLKVM_CHANNEL not set or 'reset' is passed, then initialize it to this channel then set and retrun TRUE
        //
        $mcontext = 'macro-blkvm-set';
        $ext->add($mcontext,$exten,'', new ext_execif('$[!${EXISTS(${BLKVM_CHANNEL})} | "{ARG1}" = "reset"]', 'Set','__BLKVM_CHANNEL=${CHANNEL}'));
        $ext->add($mcontext,$exten,'', new ext_set('SHARED(BLKVM,${BLKVM_CHANNEL})','TRUE'));
        $ext->add($mcontext,$exten,'', new ext_set('GOSUB_RETVAL','TRUE'));
        $ext->add($mcontext,$exten,'', new ext_macroexit(''));

        // if clearing, BLKVM_CHANNEL should already exist (if not, we clear our channel's copy)
        //
        $mcontext = 'macro-blkvm-clr';
        $ext->add($mcontext,$exten,'', new ext_set('SHARED(BLKVM,${BLKVM_CHANNEL})',''));
        $ext->add($mcontext,$exten,'', new ext_set('GOSUB_RETVAL',''));
        $ext->add($mcontext,$exten,'', new ext_macroexit(''));

        // if checking, BLKVM_CHANNEL should already exist (if not, we check our channel's copy)
                // CC_RECALL was originally used for CallCompletion but is used elsewhere as well for recall automated
                // calls that should therefore not go to voicemail, for example a wakeup call
                //
        $mcontext = 'macro-blkvm-check';
        $ext->add($mcontext,$exten,'', new ext_set('GOSUB_RETVAL','${SHARED(BLKVM,${BLKVM_CHANNEL})}'));
        $ext->add($mcontext,$exten,'', new ext_execif('$["${GOSUB_RETVAL}"="" & "${CC_RECALL}"="1"]', 'Set','GOSUB_RETVAL=TRUE'));
        $ext->add($mcontext,$exten,'', new ext_macroexit(''));

      } else { // NO SHARED()

        // If it BLKVM_OVERRIDE exists, return it's value. If not, then set it and return TRUE
        //
        $mcontext = 'macro-blkvm-setifempty';
        $ext->add($mcontext,$exten,'', new ext_gotoif('$[!${EXISTS(${BLKVM_OVERRIDE})}]', 'init'));
        $ext->add($mcontext,$exten,'', new ext_set('GOSUB_RETVAL','${DB(${BLKVM_OVERRIDE})}'));
        $ext->add($mcontext,$exten,'', new ext_macroexit(''));
        $ext->add($mcontext,$exten,'init', new ext_set('__BLKVM_OVERRIDE','BLKVM/${MACRO_EXTEN}/${CHANNEL}'));
        $ext->add($mcontext,$exten,'', new ext_set('__BLKVM_BASE','${MACRO_EXTEN}'));
        $ext->add($mcontext,$exten,'', new ext_set('DB(${BLKVM_OVERRIDE})','TRUE'));

        $ext->add($mcontext,$exten,'', new ext_set('GOSUB_RETVAL','TRUE'));
        $ext->add($mcontext,$exten,'', new ext_macroexit(''));

        // If BLKVM_OVERRIDE not set or 'reset' is passed, then initialize it to this channel then set and retrun TRUE
        //
        $mcontext = 'macro-blkvm-set';
        $ext->add($mcontext,$exten,'', new ext_execif('$[!${EXISTS(${BLKVM_OVERRIDE})} | "{ARG1}" = "reset"]', 'Set','__BLKVM_BASE=${MACRO_EXTEN}'));
        $ext->add($mcontext,$exten,'', new ext_execif('$[!${EXISTS(${BLKVM_OVERRIDE})} | "{ARG1}" = "reset"]', 'Set','__BLKVM_OVERRIDE=BLKVM/${MACRO_EXTEN}/${CHANNEL}'));
        $ext->add($mcontext,$exten,'', new ext_set('DB(${BLKVM_OVERRIDE})','TRUE'));
        $ext->add($mcontext,$exten,'', new ext_set('GOSUB_RETVAL','TRUE'));
        $ext->add($mcontext,$exten,'', new ext_macroexit(''));

        // if clearing, BLKVM_OVERRIDE should already exist (if not, it's already cleared anyhow)
        //
        $mcontext = 'macro-blkvm-clr';
        $ext->add($mcontext,$exten,'', new ext_gotoif('$[!${EXISTS(${BLKVM_OVERRIDE})}]', 'ret'));
        $ext->add($mcontext,$exten,'', new ext_dbdel('${BLKVM_OVERRIDE}'));
        $ext->add($mcontext,$exten,'ret', new ext_set('GOSUB_RETVAL',''));
        $ext->add($mcontext,$exten,'', new ext_macroexit(''));

        // if checking, BLKVM_OVERRIDE should already exist (if not, '' will be returned)
        //
        $mcontext = 'macro-blkvm-check';
        $ext->add($mcontext,$exten,'', new ext_set('GOSUB_RETVAL','${DB(${BLKVM_OVERRIDE})}'));
        $ext->add($mcontext,$exten,'', new ext_macroexit(''));
      }

      $mcontext = 'macro-hangupcall';
      $exten = 's';
      /*
      ; Cleanup any remaining RG flag
      */
      $skip_label = $amp_conf['AST_FUNC_SHARED'] ? 'theend' : 'skiprg';
            $ext->add($mcontext,$exten,'start', new ext_gotoif('$["${USE_CONFIRMATION}"="" | "${RINGGROUP_INDEX}"="" | "${CHANNEL}"!="${UNIQCHAN}"]',$skip_label));
            $ext->add($mcontext,$exten,'', new ext_noop_trace('Cleaning Up Confirmation Flag: RG/${RINGGROUP_INDEX}/${CHANNEL}'));
            $ext->add($mcontext,$exten,'delrgi', new ext_dbdel('RG/${RINGGROUP_INDEX}/${CHANNEL}'));

      if (!$amp_conf['AST_FUNC_SHARED']) {
        // only clr it if we were the originating channel
        //
              $ext->add($mcontext,$exten,'skiprg', new ext_gotoif('$["${BLKVM_BASE}"="" | "BLKVM/${BLKVM_BASE}/${CHANNEL}"!="${BLKVM_OVERRIDE}"]', 'skipblkvm'));
        $ext->add($mcontext,$exten,'', new ext_noop_trace('Cleaning Up Block VM Flag: ${BLKVM_OVERRIDE}'));
        $ext->add($mcontext,$exten,'', new ext_macro('blkvm-clr'));
        /*
        ; Cleanup any remaining FollowMe DND flags
        */
        $ext->add($mcontext,$exten,'skipblkvm', new ext_gotoif('$["${FMGRP}"="" | "${FMUNIQUE}"="" | "${CHANNEL}"!="${FMUNIQUE}"]','theend'));
        $ext->add($mcontext,$exten,'delfmrgp', new ext_dbdel('FM/DND/${FMGRP}/${CHANNEL}'));

        $skip_label = $next_label;
      }

            // Work around Asterisk issue: https://issues.asterisk.org/jira/browse/ASTERISK-19853
            $ext->add($mcontext, $exten,'theend', new ext_execif('$["${ONETOUCH_RECFILE}"!="" & "${CDR(recordingfile)}"=""]','Set','CDR(recordingfile)=${ONETOUCH_RECFILE}'));

      $ext->add($mcontext, $exten,'', new ext_hangup()); // TODO: once Asterisk issue fixed label as theend
      $ext->add($mcontext, $exten,'', new ext_macroexit(''));
      /*
      $ext->add($mcontext, $exten, 'theend', new ext_gosubif('$["${ONETOUCH_REC}"="RECORDING"]', 'macro-one-touch-record,s,sstate', false, '${FROMEXTEN},NOT_INUSE'));
      $ext->add($mcontext, $exten, '', new ext_gosubif('$["${ONETOUCH_REC}"="RECORDING"&"${MASTER_CHANNEL(CLEAN_DIALEDPEERNUMBER)}"="${CUT(CALLFILENAME,-,2)}"]', 'macro-one-touch-record,s,sstate', false, '${IF($["${EXTTOCALL}"!=""]?${EXTTOCALL}:${CUT(CALLFILENAME,-,2)})},NOT_INUSE'));
      $ext->add($mcontext, $exten, '', new ext_gosubif('$["${ONETOUCH_REC}"="RECORDING"&"${MASTER_CHANNEL(CLEAN_DIALEDPEERNUMBER)}"!="${CUT(CALLFILENAME,-,2)}"]','macro-one-touch-record,s,sstate',false,'${MASTER_CHANNEL(CLEAN_DIALEDPEERNUMBER)},NOT_INUSE'));
      $ext->add($mcontext,$exten,'', new ext_noop_trace('ONETOUCH_REC: ${ONETOUCH_REC}',5));
       */

      /* Now generate a clean DIALEDPEERNUMBER if ugly followme/ringgroup extensions dialplans were engaged
       * doesn't seem like this is need with some of the NoCDRs() but leave for now and keep an eye on it
       *
      $ext->add($mcontext, $exten, '', new ext_execif('$["${CLEAN_DIALEDPEERNUMBER}"=""]','Set','CLEAN_DIALEDPEERNUMBER=${IF($[${FIELDQTY(DIALEDPEERNUMBER,-)}=1]?${DIALEDPEERNUMBER}:${CUT(CUT(DIALEDPEERNUMBER,-,2),@,1)})}'));
      $ext->add($mcontext, $exten, '', new ext_set('CDR(clean_dst)','${CLEAN_DIALEDPEERNUMBER}'));
       */


      /* macro-hangupcall */

      /*
      ; macro-dial-one
      ;
      */
      if ($amp_conf['AST_FUNC_EXTENSION_STATE']) {

        $mcontext = 'macro-dial-one';
        $exten = 's';

        $ext->add($mcontext,$exten,'', new ext_set('DEXTEN', '${ARG3}'));
        $ext->add($mcontext,$exten,'', new ext_set('DIALSTATUS_CW', ''));
        $ext->add($mcontext,$exten,'', new ext_gosubif('$["${FROM_DID}"!="" & "${SCREEN}"="" & "${DB(AMPUSER/${DEXTEN}/screen)}"!=""]','screen,1'));
        $ext->add($mcontext,$exten,'', new ext_gosubif('$["${DB(CF/${DEXTEN})}"!=""]','cf,1'));
        $ext->add($mcontext,$exten,'', new ext_gotoif('$["${DEXTEN:-1}"="#" | "${DB(DND/${DEXTEN})}"=""]','skip1'));
        $ext->add($mcontext,$exten,'', new ext_set('DEXTEN', ''));
        $ext->add($mcontext,$exten,'', new ext_set('DIALSTATUS', 'BUSY'));
        $ext->add($mcontext,$exten,'skip1', new ext_gotoif('$["${DEXTEN}"=""]','nodial'));
        $ext->add($mcontext,$exten,'', new ext_gotoif('$["${DEXTEN:-1}"="#"]','continue'));
        $ext->add($mcontext,$exten,'', new ext_set('EXTHASCW', '${IF($["${CWIGNORE}"!=""]? :${DB(CW/${DEXTEN})})}'));
        $ext->add($mcontext,$exten,'', new ext_gotoif('$["${EXTHASCW}"="" | "${DB(CFB/${DEXTEN})}"!="" | "${DB(CFU/${DEXTEN})}"!=""]','next1','cwinusebusy'));

        $ext->add($mcontext,$exten,'next1', new ext_gotoif('$["${DB(CFU/${DEXTEN})}"!="" & ("${EXTENSION_STATE(${DEXTEN})}"="UNAVAILABLE" | "${EXTENSION_STATE(${DEXTEN})}"="UNKNOWN")]','docfu','skip3'));
        $ext->add($mcontext,$exten,'docfu', new ext_set('DEXTEN', ''));
        $ext->add($mcontext,$exten,'', new ext_set('DIALSTATUS', 'NOANSWER'));
        $ext->add($mcontext,$exten,'',new ext_goto('nodial'));
        $ext->add($mcontext,$exten,'skip3', new ext_gotoif('$["${EXTHASCW}"="" | "${DB(CFB/${DEXTEN})}"!=""]','next2','continue'));
        $ext->add($mcontext,$exten,'next2', new ext_gotoif('$["${EXTENSION_STATE(${DEXTEN})}"="NOT_INUSE" | "${EXTENSION_STATE(${DEXTEN})}"="UNAVAILABLE" | "${EXTENSION_STATE(${DEXTEN})}"="UNKNOWN"]','continue'));
        $ext->add($mcontext,$exten,'', new ext_execif('$["${DB(CFB/${DEXTEN})}"!="" & "${CFIGNORE}"=""]', 'Set', 'DIALSTATUS=BUSY'));
        $ext->add($mcontext,$exten,'', new ext_gotoif('$["${EXTHASCW}"!="" | "${DEXTEN:-1}"="#"]','cwinusebusy'));
        $ext->add($mcontext,$exten,'', new ext_set('DEXTEN', ''));
        $ext->add($mcontext,$exten,'', new ext_set('DIALSTATUS', 'BUSY'));
        $ext->add($mcontext,$exten,'', new ext_goto('nodial'));
        //TODO: we know about CWINUSEBUSY at generation time, so change this and above corresponding targets to streamline at generation
        $ext->add($mcontext,$exten,'cwinusebusy', new ext_gotoif('$["${EXTHASCW}"!="" & "${CWINUSEBUSY}"="true"]','next3','continue'));
        $ext->add($mcontext,$exten,'next3', new ext_execif('$["${EXTENSION_STATE(${DEXTEN})}"!="UNAVAILABLE" & "${EXTENSION_STATE(${DEXTEN})}"!="NOT_INUSE" & "${EXTENSION_STATE(${DEXTEN})}"!="UNKNOWN"]', 'Set', 'DIALSTATUS_CW=BUSY'));
        $ext->add($mcontext,$exten,'continue', new ext_gotoif('$["${DEXTEN}"=""]','nodial'));
        $ext->add($mcontext,$exten,'', new ext_gosubif('$["${DEXTEN:-1}"!="#"]','dstring,1','dlocal,1'));
        $ext->add($mcontext,$exten,'', new ext_gotoif('$[${LEN(${DSTRING})}=0]','nodial'));
        $ext->add($mcontext,$exten,'', new ext_gotoif('$["${DEXTEN:-1}"="#"]','skiptrace'));
        $ext->add($mcontext,$exten,'', new ext_gosubif('$[${REGEX("^[\+]?[0-9]+$" ${CALLERID(number)})} = 1]','ctset,1','ctclear,1'));
        //TODO: do we need to check for anything beyond auto-blkvm in this call path?
        $ext->add($mcontext,$exten,'skiptrace', new ext_set('D_OPTIONS', '${IF($["${NODEST}"!="" & ${REGEX("(M[(]auto-blkvm[)])" ${ARG2})} != 1]?${ARG2}M(auto-blkvm):${ARG2})}'));
        //$ext->add($mcontext,$exten,'', new ext_execif('$["${ALERT_INFO}"!=""]', 'SIPAddHeader', 'Alert-Info: ${ALERT_INFO}'));
        $ext->add($mcontext,$exten,'', new ext_gosubif('$["${ALERT_INFO}"!="" & "${HASH(SIPHEADERS,Alert-Info)}"=""]', 'func-set-sipheader,s,1',false,'Alert-Info,${ALERT_INFO}',false));
        //TODO: Do I need to  re-propagage anything from ${SIPADDHEADER} ?
        //$ext->add($mcontext,$exten,'', new ext_execif('$["${SIPADDHEADER}"!=""]', 'SIPAddHeader', '${SIPADDHEADER}'));
        if ($ast_ge_14) {
          $ext->add($mcontext,$exten,'', new ext_execif('$["${MOHCLASS}"!=""]', 'Set', 'CHANNEL(musicclass)=${MOHCLASS}'));
        } else {
          $ext->add($mcontext,$exten,'', new ext_execif('$["${MOHCLASS}"!=""]', 'SetMusicOnHold', '${MOHCLASS}'));
        }
        $ext->add($mcontext,$exten,'', new ext_gosubif('$["${QUEUEWAIT}"!=""]','qwait,1'));
        $ext->add($mcontext,$exten,'', new ext_set('__CWIGNORE', '${CWIGNORE}'));
        $ext->add($mcontext,$exten,'', new ext_set('__KEEPCID', 'TRUE'));

        // Use goto if no timelimit set from CF
        $ext->add($mcontext,$exten,'', new ext_gotoif('$["${USEGOTO}"="1"]','usegoto,1'));

        // Once setting CONNECTEDLINE(), add the I option to Dial() so the device doesn't further update the value with the
        // "device" <devicenum> data from device CID information, don't send an update if the calling party is not an extension it breaks some providers
        //
        if ($amp_conf['AST_FUNC_CONNECTEDLINE']) {
          $ext->add($mcontext,$exten,'', new ext_gotoif('$["${DB(AMPUSER/${EXTTOCALL}/cidname)}" = "" || "${DB(AMPUSER/${AMPUSER}/cidname)}" = ""]','godial'));
          $cidnameval = '${DB(AMPUSER/${EXTTOCALL}/cidname)}';
          if ($amp_conf['AST_FUNC_PRESENCE_STATE'] && $amp_conf['CONNECTEDLINE_PRESENCESTATE']) {
            $ext->add($mcontext,$exten,'', new ext_gosub('1','s','sub-presencestate-display','${EXTTOCALL}'));
            $cidnameval.= '${PRESENCESTATE_DISPLAY}';
          }
          $ext->add($mcontext,$exten,'', new ext_set('CONNECTEDLINE(name,i)', $cidnameval));
          $ext->add($mcontext,$exten,'', new ext_set('CONNECTEDLINE(num)', '${EXTTOCALL}'));
          $ext->add($mcontext,$exten,'', new ext_set('D_OPTIONS', '${D_OPTIONS}I'));
        }

        $ext->add($mcontext,$exten,'godial', new \ext_execif('$["${DIRECTION}" = "INBOUND"]', 'Set', 'D_OPTIONS=${STRREPLACE(D_OPTIONS,T)}'));
        $ext->add($mcontext,$exten,'', new ext_dial('${DSTRING}', '${ARG1},${D_OPTIONS}b(func-apply-sipheaders^s^1)'));
        $ext->add($mcontext,$exten,'', new ext_execif('$["${DIALSTATUS}"="ANSWER" & "${CALLER_DEST}"!=""]', 'MacroExit'));

        $ext->add($mcontext,$exten,'', new ext_execif('$["${DIALSTATUS_CW}"!=""]', 'Set', 'DIALSTATUS=${DIALSTATUS_CW}'));
        $ext->add($mcontext,$exten,'', new ext_gosubif('$[("${SCREEN}"!=""&("${DIALSTATUS}"="TORTURE"|"${DIALSTATUS}"="DONTCALL"))|"${DIALSTATUS}"="ANSWER"]','s-${DIALSTATUS},1'));

        $ext->add($mcontext,$exten,'', new ext_macroexit());
        $ext->add($mcontext,$exten,'nodial', new ext_execif('$["${DIALSTATUS}" = ""]', 'Set', 'DIALSTATUS=NOANSWER'));
        $ext->add($mcontext,$exten,'', new ext_noop('Returned from dial-one with nothing to call and DIALSTATUS: ${DIALSTATUS}'));
        $ext->add($mcontext,$exten,'', new ext_macroexit());

        $exten = 'h';
        $ext->add($mcontext, $exten, '', new ext_macro('hangupcall'));

        $exten = 'usegoto';
        $ext->add($mcontext,$exten,'', new ext_set('USEGOTO', ''));
        $ext->add($mcontext,$exten,'', new ext_goto('1','${DSTRING}','from-internal'));

        $exten = 'screen';
        $ext->add($mcontext,$exten,'', new ext_gotoif('$["${DB(AMPUSER/${DEXTEN}/screen)}"!="nomemory" | "${CALLERID(number)}"=""]','memory'));
        $ext->add($mcontext,$exten,'', new ext_execif('$[${REGEX("^[0-9a-zA-Z ]+$" ${CALLERID(number)})} = 1]', 'System', 'rm -f ${ASTDATADIR}/sounds/priv-callerintros/${CALLERID(number)}.*'));
        $ext->add($mcontext,$exten,'memory', new ext_set('__SCREEN', '${DB(AMPUSER/${DEXTEN}/screen)}'));
        $ext->add($mcontext,$exten,'', new ext_set('__SCREEN_EXTEN', '${DEXTEN}'));
        $ext->add($mcontext,$exten,'', new ext_set('ARG2', '${ARG2}p'));
        $ext->add($mcontext,$exten,'', new ext_return(''));

        $exten = 'cf';
        $ext->add($mcontext,$exten,'', new ext_set('CFAMPUSER', '${IF($["${AMPUSER}"=""]?${CALLERID(number)}:${AMPUSER})}'));
        $ext->add($mcontext,$exten,'', new ext_execif('$["${DB(CF/${DEXTEN})}"="${CFAMPUSER}" | "${DB(CF/${DEXTEN})}"="${REALCALLERIDNUM}" | "${CUT(CUT(BLINDTRANSFER,-,1),/,1)}" = "${DB(CF/${DEXTEN})}" | "${DEXTEN}"="${DB(CF/${DEXTEN})}"]', 'Return'));
        $ext->add($mcontext,$exten,'', new ext_execif('$["${DB(AMPUSER/${DEXTEN}/cfringtimer)}" != "0" & "${DB(AMPUSER/${DEXTEN}/cfringtimer)}" != ""]', 'Set', 'ARG1=${IF($["${DB(AMPUSER/${DEXTEN}/cfringtimer)}"="-1"]? : ${DB(AMPUSER/${DEXTEN}/cfringtimer)})}'));
        $ext->add($mcontext,$exten,'', new ext_set('DEXTEN', '${IF($["${CFIGNORE}"=""]?"${DB(CF/${DEXTEN})}#": )}'));
                if ($amp_conf['DIVERSIONHEADER']) $ext->add($mcontext,$exten,'', new ext_set('__DIVERSION_REASON', '${IF($["${DEXTEN}"!=""]?"unconditional": )}'));
        $ext->add($mcontext,$exten,'', new ext_execif('$["${DEXTEN}"!=""]', 'Return'));
        $ext->add($mcontext,$exten,'', new ext_set('DIALSTATUS', 'NOANSWER'));
        $ext->add($mcontext,$exten,'', new ext_return(''));

        $exten = 'qwait';
        $ext->add($mcontext,$exten,'', new ext_execif('$["${SAVEDCIDNAME}" = ""]', 'Set', '__SAVEDCIDNAME=${CALLERID(name)}'));
        $ext->add($mcontext,$exten,'', new ext_set('ELAPSED', '${MATH($[${EPOCH}+30-${QUEUEWAIT}]/60,int)}'));
        $ext->add($mcontext,$exten,'', new ext_set('CALLERID(name)', 'M${ELAPSED}:${SAVEDCIDNAME}'));
        $ext->add($mcontext,$exten,'', new ext_return(''));

        $exten = 'ctset';
        $ext->add($mcontext,$exten,'', new ext_set('DB(CALLTRACE/${DEXTEN})', '${CALLERID(number)}'));
        $ext->add($mcontext,$exten,'', new ext_return(''));

        $exten = 'ctclear';
        $ext->add($mcontext,$exten,'', new ext_dbdel('CALLTRACE/${DEXTEN}'));
        $ext->add($mcontext,$exten,'', new ext_return(''));

        $exten = 'dstring';
        $ext->add($mcontext,$exten,'', new ext_set('DSTRING', ''));
        $ext->add($mcontext,$exten,'', new ext_set('DEVICES', '${DB(AMPUSER/${DEXTEN}/device)}'));
        $ext->add($mcontext,$exten,'', new ext_execif('$["${DEVICES}"=""]', 'Return'));
        $ext->add($mcontext,$exten,'', new ext_execif('$["${DEVICES:0:1}"="&"]', 'Set', 'DEVICES=${DEVICES:1}'));
        $ext->add($mcontext,$exten,'', new ext_set('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));
        $ext->add($mcontext,$exten,'', new ext_set('ITER', '1'));
        $ext->add($mcontext,$exten,'begin', new ext_set('THISDIAL', '${DB(DEVICE/${CUT(DEVICES,&,${ITER})}/dial)}'));

        $ext->add($mcontext,$exten,'', new ext_gosubif('$["${THISDIAL:0:5}" = "PJSIP"]','pjsipdial,1'));

        if ($chan_dahdi) {
          $ext->add($mcontext,$exten,'', new ext_gosubif('$["${ASTCHANDAHDI}" = "1"]','zap2dahdi,1'));
        }
        $ext->add($mcontext,$exten,'', new ext_set('DSTRING', '${DSTRING}${THISDIAL}&'));
        $ext->add($mcontext,$exten,'', new ext_set('ITER', '$[${ITER}+1]'));
        $ext->add($mcontext,$exten,'', new ext_gotoif('$[${ITER}<=${LOOPCNT}]','begin'));
        $ext->add($mcontext,$exten,'', new ext_set('DSTRING', '${DSTRING:0:$[${LEN(${DSTRING})}-1]}'));
        $ext->add($mcontext,$exten,'', new ext_return(''));

        $exten = 'dlocal';
        //$ext->add($mcontext,$exten,'', new ext_set('DSTRING', 'Local/${DEXTEN:0:${MATH(${LEN(${DEXTEN})}-1,int)}}@from-internal/n'));
        $ext->add($mcontext,$exten,'', new ext_set('DSTRING', '${IF($["${ARG1}"=""]?${DEXTEN:0:${MATH(${LEN(${DEXTEN})}-1,int)}}:Local/${DEXTEN:0:${MATH(${LEN(${DEXTEN})}-1,int)}}@from-internal/n)}'));
        $ext->add($mcontext,$exten,'', new ext_set('USEGOTO', '${IF($["${ARG1}"=""]?1:0)}'));
        $ext->add($mcontext,$exten,'', new ext_return(''));

        if ($chan_dahdi) {
          $exten = 'zap2dahdi';
          $ext->add($mcontext,$exten,'', new ext_execif('$["${THISDIAL}" = ""]', 'Return'));
          $ext->add($mcontext,$exten,'', new ext_set('NEWDIAL', ''));
          $ext->add($mcontext,$exten,'', new ext_set('LOOPCNT2', '${FIELDQTY(THISDIAL,&)}'));
          $ext->add($mcontext,$exten,'', new ext_set('ITER2', '1'));
          $ext->add($mcontext,$exten,'begin2', new ext_set('THISPART2', '${CUT(THISDIAL,&,${ITER2})}'));
          $ext->add($mcontext,$exten,'', new ext_execif('$["${THISPART2:0:3}" = "ZAP"]', 'Set','THISPART2=DAHDI${THISPART2:3}'));
          $ext->add($mcontext,$exten,'', new ext_set('NEWDIAL', '${NEWDIAL}${THISPART2}&'));
          $ext->add($mcontext,$exten,'', new ext_set('ITER2', '$[${ITER2} + 1]'));
          $ext->add($mcontext,$exten,'', new ext_gotoif('$[${ITER2} <= ${LOOPCNT2}]','begin2'));
          $ext->add($mcontext,$exten,'', new ext_set('THISDIAL', '${NEWDIAL:0:$[${LEN(${NEWDIAL})}-1]}'));
          $ext->add($mcontext,$exten,'', new ext_return(''));
        }

        $exten = "pjsipdial";
        $ext->add($mcontext,$exten,'', new ext_set('PJ', '${CUT(THISDIAL,/,2)}'));
        $ext->add($mcontext,$exten,'', new ext_set('THISDIAL', '${PJSIP_DIAL_CONTACTS(${PJ})}'));
        $ext->add($mcontext,$exten,'', new ext_execif('$["${THISDIAL}" = ""]','Set','THISDIAL=PJSIP/${PJ}'));
        $ext->add($mcontext,$exten,'', new ext_return(''));

            /*
             * There are reported bugs in Asterisk Blind Trasfers that result in Dial() returning and continuing
             * execution with a status of ANSWER. So we hangup at this point
             */
            $exten = 's-ANSWER';
            $ext->add($context, $exten, '', new ext_noop('Call successfully answered - Hanging up now'));
            $ext->add($context, $exten, '', new ext_macro('hangupcall'));

            $exten = 's-TORTURE';
            $ext->add($mcontext,$exten,'', new ext_goto('1','musiconhold','app-blackhole'));
            $ext->add($mcontext,$exten,'', new ext_macro('hangupcall'));

            $exten = 's-DONTCALL';
            $ext->add($mcontext,$exten,'', new ext_answer(''));
            $ext->add($mcontext,$exten,'', new ext_wait('1'));
            $ext->add($mcontext,$exten,'', new ext_zapateller(''));
            $ext->add($mcontext,$exten,'', new ext_playback('ss-noservice'));
            $ext->add($mcontext,$exten,'', new ext_macro('hangupcall'));

            /*
             * If an endpoint is offline, app_dial returns with CHANUNAVAIL, we deal with this the same way
             * as we do with NOANSWER
             */

            foreach (array('s-CHANUNAVAIL', 's-NOANSWER', 's-BUSY') as $exten) {
                $ext->add($mcontext,$exten,'', new ext_macro('vm','${SCREEN_EXTEN},BUSY,${IVR_RETVM}'));
                $ext->add($mcontext,$exten,'', new ext_execif('$["${IVR_RETVM}"!="RETURN" | "${IVR_CONTEXT}"=""]','Hangup'));
                $ext->add($mcontext,$exten,'', new ext_return(''));
            }
            /* macro-dial-one */
        }
        break;
    }
}

/* begin page.ampusers.php functions */

function core_ampusers_add($username, $password, $extension_low, $extension_high, $deptname, $sections) {
    global $db;

    $username = $db->escapeSimple($username);
    $password = $db->escapeSimple($password);
    $extension_low = $db->escapeSimple($extension_low);
    $extension_high = $db->escapeSimple($extension_high);
    $deptname = $db->escapeSimple($deptname);
    $sections = $db->escapeSimple(implode(";",$sections));

    $sql = "INSERT INTO ampusers (username, password_sha1, extension_low, extension_high, deptname, sections) VALUES (";
    $sql .= "'".$username."',";
    if (strlen($password) == 40) {
        // It's already a hash
        $sql .= "'".$password."',";
    } else {
        // Hash it.
        $sql .= "'".sha1($password)."',";
    }
    $sql .= "'".$extension_low."',";
    $sql .= "'".$extension_high."',";
    $sql .= "'".$deptname."',";
    $sql .= "'".$sections."');";

    sql($sql,"query");
}

function core_ampusers_del($username) {
    global $db;

    $username = $db->escapeSimple($username);
    
    $sql = "DELETE FROM ampusers WHERE username = '".$username."'";
    sql($sql,"query");
}

function core_ampusers_list() {
    $sql = "SELECT username FROM ampusers ORDER BY username";
    return sql($sql,"getAll");
}

/* end page.ampusers.php functions */









/* begin page.did.php functions */

function core_did_list($order='extension'){
    switch ($order) {
        case 'description':
            $sql = "SELECT * FROM incoming ORDER BY description,extension,cidnum";
            break;
        case 'extension':
        default:
            $sql = "SELECT * FROM incoming ORDER BY extension,cidnum";
    }
    return sql($sql,"getAll",DB_FETCHMODE_ASSOC);
}

function core_did_get($extension="",$cidnum=""){
    global $db;
    $cidnum = $db->escapeSimple($cidnum);
    $sql = "SELECT * FROM incoming WHERE cidnum = \"$cidnum\" AND extension = \"$extension\"";
    return sql($sql,"getRow",DB_FETCHMODE_ASSOC);
}

function core_did_del($extension,$cidnum){
    global $db;
    $cidnum = $db->escapeSimple($cidnum);
    $sql="DELETE FROM incoming WHERE cidnum = \"$cidnum\" AND extension = \"$extension\"";
    sql($sql);
}

function core_did_edit($old_extension,$old_cidnum, $incoming){
    global $db;

    $old_extension = $db->escapeSimple(trim($old_extension));
    $old_cidnum = $db->escapeSimple(trim($old_cidnum));

    $incoming['extension'] = trim($incoming['extension']);
    $incoming['cidnum'] = trim($incoming['cidnum']);

    $extension = $db->escapeSimple($incoming['extension']);
    $cidnum = $db->escapeSimple($incoming['cidnum']);

    // if did or cid changed, then check to make sure that this pair is not already being used.
    //
    if (($extension != $old_extension) || ($cidnum != $old_cidnum)) {
        $existing=core_did_get($extension,$cidnum);
    }

    if (empty($existing)) {
        core_did_del($old_extension,$old_cidnum);
        core_did_add($incoming);
        return true;
    } else {
        echo "<script>javascript:alert('".__("A route for this DID/CID already exists!")." => ".$existing['extension']."/".$existing['cidnum']."')</script>";
    }
    return false;
}

/* Create a new did with values passed into $did_vars and defaults used otherwise
*/
function core_did_create_update($did_vars) {
  $did_create['extension'] = isset($did_vars['extension']) ? $did_vars['extension'] : '';
  $did_create['cidnum']    = isset($did_vars['cidnum']) ? $did_vars['cidnum'] : '';

  if (count(core_did_get($did_create['extension'], $did_create['$cidnum']))) {
    return core_did_edit_properties($did_vars); //already exists so just edit properties
  } else {
      $did_create['privacyman']  = isset($did_vars['privacyman'])  ? $did_vars['privacyman']  : '';
      $did_create['pmmaxretries']  = isset($did_vars['pmmaxretries'])  ? $did_vars['pmmaxretries']  : '';
      $did_create['pmminlength']  = isset($did_vars['pmminlength'])  ? $did_vars['pmminlength']  : '';
      $did_create['alertinfo']   = isset($did_vars['alertinfo'])   ? $did_vars['alertinfo']   : '';
      $did_create['ringing']     = isset($did_vars['ringing'])     ? $did_vars['ringing']     : '';
      $did_create['mohclass']    = isset($did_vars['mohclass'])    ? $did_vars['mohclass']    : 'default';
      $did_create['description'] = isset($did_vars['description']) ? $did_vars['description'] : '';
      $did_create['grppre']      = isset($did_vars['grppre'])      ? $did_vars['grppre']      : '';
      $did_create['delay_answer']= isset($did_vars['delay_answer'])? $did_vars['delay_answer']: '0';
      $did_create['pricid']      = isset($did_vars['pricid'])      ? $did_vars['pricid']      : '';

      $did_dest                  = isset($did_vars['destination']) ? $did_vars['destination'] : '';
      return core_did_add($did_vars, $did_dest);
 }
}


/* Edits the poperties of a did, but not the did or cid nums since those could of course be
   in conflict
*/
function core_did_edit_properties($did_vars) {
  global $db;

  if (!is_array($did_vars)) {
    return false;
  }

  $extension = $db->escapeSimple(isset($did_vars['extension']) ? $did_vars['extension'] : '');
  $cidnum    = $db->escapeSimple(isset($did_vars['cidnum']) ? $did_vars['cidnum'] : '');
  $sql = "";
  foreach ($did_vars as $key => $value) {
    switch ($key) {
      case 'privacyman':
      case 'pmmaxretries':
      case 'pmminlength':
      case 'alertinfo':
      case 'ringing':
      case 'mohclass':
      case 'description':
      case 'grppre':
      case 'delay_answer':
      case 'pricid':
      case 'destination':
        $sql_value = $db->escapeSimple($value);
        $sql .= " `$key` = '$sql_value',";
      break;
    default:
    }
  }
  if ($sql == '') {
    return false;
  }
  $sql = substr($sql,0,(strlen($sql)-1)); //strip off tailing ','
  $sql_update = "UPDATE `incoming` SET"."$sql WHERE `extension` = '$extension' AND `cidnum` = '$cidnum'";
  return sql($sql_update);
}

function core_did_add($incoming,$target=false){
    global $db;
    if($incoming['privacyman']=='') $incoming['privacyman']=0;
    foreach ($incoming as $key => $val) { ${$key} = $db->escapeSimple($val); } // create variables from request

    // Check to make sure the did is not being used elsewhere
    //
    $existing=core_did_get($extension,$cidnum);

    if(!isset($ringing)) $ringing='';
    if(!isset($pricid)) $pricid='';

    if (empty($existing)) {
        //Strip <> just to be on the safe side otherwise this is not deleteable from the GUI
             $invalidDIDChars = array('<','>');
             $extension = trim(str_replace($invalidDIDChars,"",$extension));
             $cidnum = trim(str_replace($invalidDIDChars,"",$cidnum));

        $destination= ($target) ? $target : ${$goto0};
        $sql="INSERT INTO incoming (cidnum,extension,destination,privacyman,pmmaxretries,pmminlength,alertinfo, ringing, mohclass, description, grppre, delay_answer, pricid) values ('$cidnum','$extension','$destination','$privacyman','$pmmaxretries','$pmminlength','$alertinfo', '$ringing', '$mohclass', '$description', '$grppre', ".intval($delay_answer).", '$pricid')";
        sql($sql);
        return true;
    } else {
        echo "<script>javascript:alert('".__("A route for this DID/CID already exists!")." => ".$existing['extension']."/".$existing['cidnum']."')</script>";
        return false;
    }
}

/* end page.did.php functions */


/* begin page.devices.php functions */

//get the existing devices
function core_devices_list($tech="all",$detail=false,$get_all=false) {
  static $extens;
  static $last_tech, $last_detail, $last_get_all;

  if (isset($extens) && $tech == $last_tech && $detail === $last_detail && $get_all === $last_get_all) {
    return $extens;
  }

    if (strtolower($detail) == 'full') {
        $sql = "SELECT * FROM devices";
    } else {
        $sql = "SELECT id,description FROM devices";
    }
    switch (strtoupper($tech)) {
        case "IAX":
            $sql .= " WHERE tech = 'iax2'";
            break;
        case "IAX2":
        case "SIP":
        case "ZAP":
        case "DAHDI":
        case 'CUSTOM':
            $sql .= " WHERE tech = '".strtolower($tech)."'";
            break;
        case "ALL":
        default:
    }
    $sql .= ' ORDER BY id';
    $results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

    $extens = null;
    foreach($results as $result){
        if ($get_all || checkRange($result['id'])){

            $record = array();
            $record[0] = $result['id'];  // for backwards compatibility
            $record[1] = $result['description'];  // for backwards compatibility
            foreach ($result as $key => $value) {
                $record[$key] = $value;
            }
            $extens[] = $record;
            /*
            $extens[] = array(
                0=>$result[0],  // for backwards compatibility
                1=>$result[1],
                'id'=>$result[0], // FETCHMODE_ASSOC emulation
                'description'=>$result[1],
            );
            */
        }
    }
    return $extens;
}

// get a mapping of the devices to user description and vmcontext
// used for fixed devices when generating tech.conf files to
// override some of the mailbox options or remove them if novm
//
function core_devices_get_user_mappings() {
    static $devices;

    if (isset($devices)) {
        return $devices;
    }
    foreach (core_devices_list("all",'full', true) as $device) {
        $devices[$device['id']] = $device;
    }
    foreach (core_users_list(true) as $user) {
        $users[$user[0]]['description'] = $user[1];
        $users[$user[0]]['vmcontext'] = $user[2];
    }
    foreach ($devices as $id => $device) {
        if ($device['devicetype'] == 'fixed') {
            $devices[$id]['vmcontext'] = isset($users[$device['user']])?$users[$device['user']]['vmcontext']:'';
            $devices[$id]['description'] = isset($users[$device['user']])?$users[$device['user']]['description']:'';
        }
    }
    return $devices;
}

function core_devices_add($id,$tech,$dial,$devicetype,$user,$description,$emergency_cid=null,$editmode=false){
    global $amp_conf;
    global $astman;
    global $db;

    if ($tech == '' || trim($tech) == 'virtual') {
        return true;
    }
    if($tech=='webrtc') { $tech='sip'; }
    if($tech=='webrtcpjsip') { $tech='pjsip'; }

    $display = isset($_REQUEST['display'])?$_REQUEST['display']:'';

    if (trim($id) == '' ) {
        if ($display != 'extensions') {
            echo "<script>javascript:alert('".__("You must put in a device id")."');</script>";
        }
        return false;
    }

    //ensure this id is not already in use
    $devices = core_devices_list();
    if (is_array($devices)) {
        foreach($devices as $device) {
            if ($device[0] === $id) {
                if ($display <> 'extensions') echo "<script>javascript:alert('".__("This device id is already in use")."');</script>";
                return false;
            }
        }
    }
    //unless defined, $dial is TECH/id
    if ($dial == '') {
        //zap, dahdi are exceptions
        if (strtolower($tech) == "zap" || strtolower($tech) == 'dahdi') {
            $thischan = $_REQUEST['devinfo_channel'] != '' ? $_REQUEST['devinfo_channel'] : $_REQUEST['channel'];
            $dial = strtoupper($tech).'/'.$thischan;
    //-------------------------------------------------------------------------------------------------
    // Added to enable the unsupported misdn module
    //
        } else if (strtolower($tech) == "misdn") {
            $dial = $_REQUEST['devinfo_port'].'/'.($_REQUEST['devinfo_msn'] ? $_REQUEST['devinfo_msn'] : $id);
    //-------------------------------------------------------------------------------------------------
        } else {
            $dial = strtoupper($tech)."/".$id;
        }
    }

    //check to see if we are requesting a new user
    if ($user == "new") {
        $user = $id;
        $jump = true;
    }

    $emergency_cid = trim($emergency_cid);
    if (version_compare(phpversion(),'7.0.0','>')) {
        if(!empty($emergency_cid)) $emergency_cid = $db->escapeSimple($emergency_cid);
        if(!empty($description)) $description = $db->escapeSimple($description);
    } else {
        if(is_callable('get_magic_quotes_gpc')) {
            if(!get_magic_quotes_gpc()) {
                if(!empty($emergency_cid)) $emergency_cid = $db->escapeSimple($emergency_cid);
                if(!empty($description)) $description = $db->escapeSimple($description);
            }
        } else {
            if(!empty($emergency_cid)) $emergency_cid = $db->escapeSimple($emergency_cid);
            if(!empty($description)) $description = $db->escapeSimple($description);
        }
    }

    //insert into devices table
    $sql="INSERT INTO devices (id,tech,dial,devicetype,user,description,emergency_cid) values (\"$id\",\"$tech\",\"$dial\",\"$devicetype\",\"$user\",\"$description\",\"$emergency_cid\")";
    sql($sql);

    //add details to astdb
    if ($astman) {
        // if adding or editting a fixed device, user property should always be set
        if ($devicetype == 'fixed' || !$editmode) {
            $astman->database_put("DEVICE",$id."/user",$user);
        }
        // If changing from a fixed to an adhoc, the user property should be intialized
        // to the new default, not remain as the previous fixed user
        if ($editmode) {
            $previous_type = $astman->database_get("DEVICE",$id."/type");
            if ($previous_type == 'fixed' && $devicetype == 'adhoc') {
                $astman->database_put("DEVICE",$id."/user",$user);
            }
        }
        $astman->database_put("DEVICE",$id."/dial",$dial);
        $astman->database_put("DEVICE",$id."/type",$devicetype);
        $astman->database_put("DEVICE",$id."/default_user",$user);
        if($emergency_cid != '') {
            $astman->database_put("DEVICE",$id."/emergency_cid","\"".$emergency_cid."\"");
    } else {
            $astman->database_del("DEVICE",$id."/emergency_cid");
    }

        $apparent_connecteduser = ($editmode && $user != "none") ? $astman->database_get("DEVICE",$id."/user") : $user;
        if ($user != "none" && $apparent_connecteduser == $user)  {
            $existingdevices = $astman->database_get("AMPUSER",$user."/device");
            if (empty($existingdevices)) {
                $astman->database_put("AMPUSER",$user."/device",$id);
            } else {
                $existingdevices_array = explode('&',$existingdevices);
                if (!in_array($id, $existingdevices_array)) {
                    $existingdevices_array[]=$id;
                    $existingdevices = implode('&',$existingdevices_array);
                    $astman->database_put("AMPUSER",$user."/device",$existingdevices);
                }
            }
        }

    } else {
        die_issabelpbx("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
    }

    // create a voicemail symlink if needed
    $thisUser = core_users_get($user);
    if(isset($thisUser['voicemail']) && ($thisUser['voicemail'] != "novm")) {
        if(empty($thisUser['voicemail'])) {
            $vmcontext = "default";
        } else {
            $vmcontext = $thisUser['voicemail'];
        }

        //voicemail symlink
        exec("rm -f /var/spool/asterisk/voicemail/device/".$id);
        exec("/bin/ln -s /var/spool/asterisk/voicemail/".$vmcontext."/".$user."/ /var/spool/asterisk/voicemail/device/".$id);
    }

    $tech = strtolower($tech);

    //take care of sip/iax/zap config
    $funct = "core_devices_add".$tech;

  // before calling device specifc funcitions, get rid of any bogus fields in the REQUEST array
  //
  if (isset($_REQUEST['devinfo_secret_origional'])) {
    unset($_REQUEST['devinfo_secret_origional']);
  }
    if(function_exists($funct)){
        $funct($id);
    }

/*    if($user != "none") {
        core_hint_add($user);
    }*/

    //if we are requesting a new user, let's jump to users.php
    if (isset($jump)) {
        echo("<script language=\"JavaScript\">window.location=\"config.php?display=users&extdisplay={$id}&name={$description}\";</script>");
    }
    return true;
}

function core_devices_del($account,$editmode=false){
    global $amp_conf;
    global $astman;

    //get all info about device
    $devinfo = core_devices_get($account);
  if (empty($devinfo)) {
    return true;
  }

    //delete details to astdb
    if ($astman) {
        // If a user was selected, remove this device from the user
        $deviceuser = $astman->database_get("DEVICE",$account."/user");
        if (isset($deviceuser) && $deviceuser != "none") {
            // Remove the device record from the user's device list
            $userdevices = $astman->database_get("AMPUSER",$deviceuser."/device");

            // We need to remove just this user and leave the rest alone
            $userdevicesarr = explode("&", $userdevices);
            $userdevicesarr_hash = array_flip($userdevicesarr);
            unset($userdevicesarr_hash[$account]);
            $userdevicesarr = array_flip($userdevicesarr_hash);
            $userdevices = implode("&", $userdevicesarr);

            if (empty($userdevices)) {
                    $astman->database_del("AMPUSER",$deviceuser."/device");
            } else {
                    $astman->database_put("AMPUSER",$deviceuser."/device",$userdevices);
            }
        }
        if (! $editmode) {
            $astman->database_del("DEVICE",$account."/dial");
            $astman->database_del("DEVICE",$account."/type");
            $astman->database_del("DEVICE",$account."/user");
            $astman->database_del("DEVICE",$account."/default_user");
            $astman->database_del("DEVICE",$account."/emergency_cid");
        }

        //delete from devices table
        $sql="DELETE FROM devices WHERE id = \"$account\"";
        sql($sql);

        //voicemail symlink
        exec("rm -f /var/spool/asterisk/voicemail/device/".$account);
    } else {
        die_issabelpbx("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
    }

    //take care of sip/iax/zap config
    $tech = strtolower($devinfo['tech']);
    if($tech=='pjsip') { $tech='sip'; }
    $funct = "core_devices_del".$tech;
    if(function_exists($funct)){
        $funct($account);
    }
}

function core_devices_get($account){
    //get all the variables for the meetme
    $sql = "SELECT * FROM devices WHERE id = '$account'";
    $results = sql($sql,"getRow",DB_FETCHMODE_ASSOC);
    if (empty($results)) {
        return array();
    }

    //take care of sip/iax/zap config
    $tech = strtolower($results['tech']);
    if($tech=='pjsip') { $tech='sip'; }
    $funct = "core_devices_get".$tech;
    if (!empty($results['tech']) && function_exists($funct)) {
        $devtech = $funct($account);
        if (is_array($devtech)){
            $results = array_merge($results,$devtech);
        }
    }

    return $results;
}

// this function rebuilds the astdb based on device table contents
// used on devices.php if action=resetall
function core_devices2astdb(){
    global $astman;
    global $amp_conf;

    $sql = "SELECT * FROM devices";
    $devresults = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

    //add details to astdb
    if ($astman) {
        $astman->database_deltree("DEVICE");
        foreach ($devresults as $dev) {
            extract($dev);
            $astman->database_put("DEVICE",$id."/dial",$dial);
            $astman->database_put("DEVICE",$id."/type",$devicetype);
            $astman->database_put("DEVICE",$id."/user",$user);
            $astman->database_put("DEVICE",$id."/default_user",$user);
            if(trim($emergency_cid) != '') {
                $astman->database_put("DEVICE",$id."/emergency_cid","\"".$emergency_cid."\"");
            }
            // If a user is selected, add this device to the user
            if ($user != "none") {
                $existingdevices = $astman->database_get("AMPUSER",$user."/device");
                if (empty($existingdevices)) {
                    $astman->database_put("AMPUSER",$user."/device",$id);
                } else {
                    $existingdevices_array = explode('&',$existingdevices);
                    if (!in_array($id, $existingdevices_array)) {
                        $existingdevices_array[]=$id;
                        $existingdevices = implode('&',$existingdevices_array);
                        $astman->database_put("AMPUSER",$user."/device",$existingdevices);
                    }
                }
            }


            // create a voicemail symlink if needed
            $thisUser = core_users_get($user);
            if(isset($thisUser['voicemail']) && ($thisUser['voicemail'] != "novm")) {
                if(empty($thisUser['voicemail']))
                    $vmcontext = "default";
                else
                    $vmcontext = $thisUser['voicemail'];
                //voicemail symlink
                exec("rm -f /var/spool/asterisk/voicemail/device/".$id);
                exec("/bin/ln -s /var/spool/asterisk/voicemail/".$vmcontext."/".$user."/ /var/spool/asterisk/voicemail/device/".$id);
            }
        }
        return true;
    } else {
        return false;
    }
}

// this function rebuilds the astdb based on users table contents
// used on devices.php if action=resetall
function core_users2astdb(){
    global $amp_conf;
    global $astman;
    global $db;

    $sql = "SELECT * FROM users";
    $userresults = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

    //add details to astdb
    if ($astman) {
        foreach($userresults as $usr) {
            extract($usr);
            $astman->database_put("AMPUSER",$extension."/password",$password);
            $astman->database_put("AMPUSER",$extension."/ringtimer",$ringtimer);
            $astman->database_put("AMPUSER",$extension."/noanswer",$noanswer);
            $astman->database_put("AMPUSER",$extension."/recording",$recording);
            $astman->database_put("AMPUSER",$extension."/outboundcid","\"".$db->escapeSimple($outboundcid)."\"");
            $astman->database_put("AMPUSER",$extension."/cidname","\"".$db->escapeSimple($name)."\"");
            $astman->database_put("AMPUSER",$extension."/voicemail","\"".$voicemail."\"");
        }
        return true;
    } else {
        return false;
    }

//    TODO: this was...
//     return $astman->disconnect();
//    is "true" the correct value...?
}

function core_devices_addpjsip($account) {
    core_devices_addsip($account,'pjsip');
}

//add to sip table
function core_devices_addsip($account,$pjsip='') {
    global $db;
    global $amp_conf;

    $flag = 2;
    foreach ($_REQUEST as $req=>$data) {
        if ( substr($req, 0, 8) == 'devinfo_' ) {
            $keyword = substr($req, 8);
            $data = trim($data);
            if ( $keyword == 'dial' && $data == '' ) {
                if($pjsip=='pjsip') {
                    $sipfields[] = array($account, $keyword, 'PJSIP/'.$account, $flag++);
                } else {
                    $sipfields[] = array($account, $keyword, 'SIP/'.$account, $flag++);
                }
            } elseif ($keyword == 'mailbox' && $data == '') {
                if($pjsip=='pjsip') {
                    $sipfields[] = array($account,'mailbox',$account, $flag++);
                } else {
                    $sipfields[] = array($account,'mailbox',$account.'@device', $flag++);
                }
            } elseif ($keyword == 'vmexten' && $data == '') {
                // don't add it
            } elseif (in_array($keyword, array('dtlscertfile', 'dtlsprivatekey')) && $data == '') {
                // don't add it
            } else {
                $sipfields[] = array($account, $keyword, $data, $flag++);
            }
        }
    }

    if ( !is_array($sipfields) ) { // left for compatibilty....lord knows why !
        $sipfields[] = array($account,'accountcode',(isset($_REQUEST['accountcode'])?$_REQUEST['accountcode']:''),$flag++);
        $sipfields[] = array($account,'secret',(isset($_REQUEST['secret'])?$_REQUEST['secret']:''),$flag++);
        $sipfields[] = array($account,'canreinvite',(isset($_REQUEST['canreinvite'])?$_REQUEST['canreinvite']:$amp_conf['DEVICE_SIP_CANREINVITE']),$flag++);
        $sipfields[] = array($account,'trustrpid',(isset($_REQUEST['trustrpid'])?$_REQUEST['trustrpid']:$amp_conf['DEVICE_SIP_TRUSTRPID']),$flag++);
        $sipfields[] = array($account,'sendrpid',(isset($_REQUEST['sendrpid'])?$_REQUEST['sendrpid']:$amp_conf['DEVICE_SIP_SENDRPID']),$flag++);
        $sipfields[] = array($account,'context',(isset($_REQUEST['context'])?$_REQUEST['context']:'from-internal'),$flag++);
        $sipfields[] = array($account,'dtmfmode',(isset($_REQUEST['dtmfmode'])?$_REQUEST['dtmfmode']:''),$flag++);
        $sipfields[] = array($account,'host',(isset($_REQUEST['host'])?$_REQUEST['host']:'dynamic'),$flag++);
        $sipfields[] = array($account,'type',(isset($_REQUEST['type'])?$_REQUEST['type']:'friend'),$flag++);
        $sipfields[] = array($account,'mailbox',(isset($_REQUEST['mailbox']) && !empty($_REQUEST['mailbox'])?$_REQUEST['mailbox']:$account.'@device'),$flag++);
        $sipfields[] = array($account,'username',(isset($_REQUEST['username'])?$_REQUEST['username']:$account),$flag++);
        $sipfields[] = array($account,'nat',(isset($_REQUEST['nat'])?$_REQUEST['nat']:$amp_conf['DEVICE_SIP_NAT']),$flag++);
        $sipfields[] = array($account,'port',(isset($_REQUEST['port'])?$_REQUEST['port']:'5060'),$flag++);
        $sipfields[] = array($account,'qualify',(isset($_REQUEST['qualify'])?$_REQUEST['qualify']:$amp_conf['DEVICE_QUALIFY']),$flag++);
        if (version_compare($amp_conf['ASTVERSION'],'1.6','ge')) {
          $sipfields[] = array($account,'qualifyfreq',(isset($_REQUEST['qualifyfreq'])?$_REQUEST['qualifyfreq']:$amp_conf['DEVICE_SIP_QUALIFYFREQ']),$flag++);
        }
        if (version_compare($amp_conf['ASTVERSION'],'1.8','ge')) {
          $sipfields[] = array($account,'transport',(isset($_REQUEST['transport'])?$_REQUEST['transport']:'udp'),$flag++);
          $sipfields[] = array($account,'encryption',(isset($_REQUEST['encryption'])?$_REQUEST['encryption']:$amp_conf['DEVICE_SIP_ENCRYPTION']),$flag++);
        }
        $sipfields[] = array($account,'callgroup',(isset($_REQUEST['callgroup'])?$_REQUEST['callgroup']:$amp_conf['DEVICE_CALLGROUP']),$flag++);
        $sipfields[] = array($account,'pickupgroup',(isset($_REQUEST['pickupgroup'])?$_REQUEST['pickupgroup']:$amp_conf['DEVICE_PICKUPGROUP']),$flag++);
        $sipfields[] = array($account,'deny',(isset($_REQUEST['deny'])?$_REQUEST['deny']:''),$flag++);
        $sipfields[] = array($account,'permit',(isset($_REQUEST['permit'])?$_REQUEST['permit']:''),$flag++);
        $sipfields[] = array($account,'disallow',(isset($_REQUEST['disallow'])?$_REQUEST['disallow']:$amp_conf['DEVICE_DISALLOW']),$flag++);
        $sipfields[] = array($account,'allow',(isset($_REQUEST['allow'])?$_REQUEST['allow']:$amp_conf['DEVICE_ALLOW']),$flag++);

        if (version_compare($amp_conf['ASTVERSION'],'11','ge')) {
            $sipfields[] = array($account,'avpf',(isset($_REQUEST['avpf'])?$_REQUEST['avpf']:'no'),$flag++);
            $sipfields[] = array($account,'force_avp',(isset($_REQUEST['force_avp'])?$_REQUEST['force_avp']:'no'),$flag++);
            $sipfields[] = array($account,'icesupport',(isset($_REQUEST['icesupport'])?$_REQUEST['icesupport']:'no'),$flag++);
            
            // 2015-12-16 Alex: fields for dtls support
            $sipfields[] = array($account,'dtlsenable',(isset($_REQUEST['dtlsenable'])?$_REQUEST['dtlsenable']:'no'),$flag++);
            $sipfields[] = array($account,'dtlsverify',(isset($_REQUEST['dtlsverify'])?$_REQUEST['dtlsverify']:'no'),$flag++);
            $sipfields[] = array($account,'dtlssetup',(isset($_REQUEST['dtlssetup'])?$_REQUEST['dtlssetup']:'actpass'),$flag++);
            $sipfields[] = array($account,'rtcp_mux',(isset($_REQUEST['rtcp_mux'])?$_REQUEST['rtcp_mux']:'no'),$flag++);
            $dtlscertfile = isset($_REQUEST['dtlscertfile'])?trim($_REQUEST['dtlscertfile']):'';
            if ($dtlscertfile != '') $sipfields[] = array($account,'dtlscertfile',$dtlscertfile,$flag++);
            $dtlsprivatekey = isset($_REQUEST['dtlsprivatekey'])?trim($_REQUEST['dtlsprivatekey']):'';
            if ($dtlsprivatekey != '') $sipfields[] = array($account,'dtlsprivatekey',$dtlsprivatekey,$flag++);
        }

        $vmexten = isset($_REQUEST['vmexten'])?trim($_REQUEST['vmexten']):'';
        if ($vmexten != '') {
          $sipfields[] = array($account,'vmexten',$vmexten,$flag++);
        }
    }

    // Very bad
    $sipfields[] = array($account,'account',$account,$flag++);
    $sipfields[] = array($account,'callerid',(isset($_REQUEST['description']) && $_REQUEST['description'])?$_REQUEST['description']." <".$account.'>':'device'." <".$account.'>',$flag++);

    $compiled = $db->prepare('INSERT INTO sip (id, keyword, data, flags) values (?,?,?,?)');
    $result = $db->executeMultiple($compiled,$sipfields);
    if(DB::IsError($result)) {
        die_issabelpbx($result->getDebugInfo()."<br><br>".'error adding to SIP table');
    }
}

function core_devices_delsip($account) {
    global $db;

    $sql = "DELETE FROM sip WHERE id = '$account'";
    $result = $db->query($sql);

    if(DB::IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}

function core_devices_getsip($account) {
    global $db;
    $sql = "SELECT keyword,data FROM sip WHERE id = '$account'";
    $results = $db->getAssoc($sql);
    if(DB::IsError($results)) {
        $results = null;
    }

    return $results;
}

//add to iax table
function core_devices_addiax2($account) {
    global $db;
    global $amp_conf;

    $flag = 2;
    foreach ($_REQUEST as $req=>$data) {
        if ( substr($req, 0, 8) == 'devinfo_' ) {
            $keyword = substr($req, 8);
            if ( $keyword == 'dial' && $data == '' ) {
                $iaxfields[] = array($account, $keyword, 'IAX2/'.$account, $flag++);
            } elseif ($keyword == 'mailbox' && $data == '') {
                $iaxfields[] = array($account,'mailbox',$account.'@device', $flag++);
            } else {
                $iaxfields[] = array($account, $keyword, $data, $flag++);
            }
        }
    }

    if ( !is_array($iaxfields) ) { // left for compatibilty....lord knows why !
        $iaxfields = array(
            array($account,'secret',$db->escapeSimple(($_REQUEST['secret'])?$_REQUEST['secret']:''),$flag++),
            array($account,'transfer',$db->escapeSimple(($_REQUEST['transfer'])?$_REQUEST['transfer']:'yes'),$flag++),
            array($account,'context',$db->escapeSimple(($_REQUEST['context'])?$_REQUEST['context']:'from-internal'),$flag++),
            array($account,'host',$db->escapeSimple(($_REQUEST['host'])?$_REQUEST['host']:'dynamic'),$flag++),
            array($account,'type',$db->escapeSimple(($_REQUEST['type'])?$_REQUEST['type']:'friend'),$flag++),
            array($account,'mailbox',$db->escapeSimple(($_REQUEST['mailbox'])?$_REQUEST['mailbox']:$account.'@device'),$flag++),
            array($account,'username',$db->escapeSimple(($_REQUEST['username'])?$_REQUEST['username']:$account),$flag++),
            array($account,'port',$db->escapeSimple(($_REQUEST['port'])?$_REQUEST['port']:'4569'),$flag++),
            array($account,'qualify',$db->escapeSimple(($_REQUEST['qualify'])?$_REQUEST['qualify']:$amp_conf['DEVICE_QUALIFY']),$flag++),
            array($account,'deny',$db->escapeSimple((isset($_REQUEST['deny']))?$_REQUEST['deny']:''),$flag++),
            array($account,'permit',$db->escapeSimple((isset($_REQUEST['permit']))?$_REQUEST['permit']:''),$flag++),
            array($account,'disallow',$db->escapeSimple(($_REQUEST['disallow'])?$_REQUEST['disallow']:$amp_conf['DEVICE_DISALLOW']),$flag++),
            array($account,'allow',$db->escapeSimple(($_REQUEST['allow'])?$_REQUEST['allow']:$amp_conf['DEVICE_ALLOW']),$flag++),
            array($account,'accountcode',$db->escapeSimple(($_REQUEST['accountcode'])?$_REQUEST['accountcode']:''),$flag++),
            array($account,'requirecalltoken',$db->escapeSimple(($_REQUEST['requirecalltoken'])?$_REQUEST['requirecalltoken']:''),$flag++)
        );
    }

    // Very bad
    $iaxfields[] = array($account,'account',$db->escapeSimple($account),$flag++);
    $iaxfields[] = array($account,'callerid',$db->escapeSimple((isset($_REQUEST['description']) && $_REQUEST['description'] != '')?$_REQUEST['description']." <".$account.'>':'device'." <".$account.'>'),$flag++);
    // Asterisk treats no CallerID from an IAX device as 'hide CallerID', and ignores the CallerID
    // set in iax.conf. As we rely on this for pretty much everything, we need to specify the
    // CallerID as a variable which gets picked up in macro-callerid.
    // Ref - http://bugs.digium.com/view.php?id=456
    $iaxfields[] = array($account,'setvar',$db->escapeSimple("REALCALLERIDNUM=$account"),$flag++);

    $compiled = $db->prepare('INSERT INTO iax (id, keyword, data, flags) values (?,?,?,?)');
    $result = $db->executeMultiple($compiled,$iaxfields);
    if(DB::IsError($result)) {
        die_issabelpbx($result->getMessage()."<br><br>error adding to IAX table");
    }
}

function core_devices_deliax2($account) {
    global $db;

    $sql = "DELETE FROM iax WHERE id = '$account'";
    $result = $db->query($sql);

    if(DB::IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}

function core_devices_getiax2($account) {
    global $db;
    $sql = "SELECT keyword,data FROM iax WHERE id = '$account'";
    $results = $db->getAssoc($sql);
    if(DB::IsError($results)) {
        $results = null;
    }

    return $results;
}

function core_devices_addzap($account) {
    global $db;
    global $amp_conf;

    foreach ($_REQUEST as $req=>$data) {
        if ( substr($req, 0, 8) == 'devinfo_' ) {
            $keyword = substr($req, 8);
            if ( $keyword == 'dial' && $data == '' ) {
                $zapchan = $_REQUEST['devinfo_channel'] != '' ? $_REQUEST['devinfo_channel'] : $_REQUEST['channel'];
                $zapfields[] = array($account, $keyword, 'ZAP/'.$zapchan);
            } elseif ($keyword == 'mailbox' && $data == '') {
                $zapfields[] = array($account,'mailbox',$account.'@device');
            } else {
                $zapfields[] = array($account, $keyword, $data);
            }
        }
    }

    if ( !is_array($zapfields) ) { // left for compatibilty....lord knows why !
        $zapfields = array(
            array($account,'context',$db->escapeSimple(($_REQUEST['context'])?$_REQUEST['context']:'from-internal')),
            array($account,'mailbox',$db->escapeSimple(($_REQUEST['mailbox'])?$_REQUEST['mailbox']:$account.'@device')),
            array($account,'immediate',$db->escapeSimple(($_REQUEST['immediate'])?$_REQUEST['immediate']:'no')),
            array($account,'signalling',$db->escapeSimple(($_REQUEST['signalling'])?$_REQUEST['signalling']:'fxo_ks')),
            array($account,'echocancel',$db->escapeSimple(($_REQUEST['echocancel'])?$_REQUEST['echocancel']:'yes')),
            array($account,'echocancelwhenbridged',$db->escapeSimple(($_REQUEST['echocancelwhenbridged'])?$_REQUEST['echocancelwhenbridged']:'no')),
            array($account,'echotraining',$db->escapeSimple(($_REQUEST['echotraining'])?$_REQUEST['echotraining']:'800')),
            array($account,'busydetect',$db->escapeSimple(($_REQUEST['busydetect'])?$_REQUEST['busydetect']:'no')),
            array($account,'busycount',$db->escapeSimple(($_REQUEST['busycount'])?$_REQUEST['busycount']:'7')),
            array($account,'callprogress',$db->escapeSimple(($_REQUEST['callprogress'])?$_REQUEST['callprogress']:'no')),
            array($account,'accountcode',$db->escapeSimple((isset($_REQUEST['accountcode']))?$_REQUEST['accountcode']:'')),
            array($account,'callgroup',$db->escapeSimple((isset($_REQUEST['callgroup']))?$_REQUEST['callgroup']:$amp_conf['DEVICE_CALLGROUP'])),
            array($account,'pickupgroup',$db->escapeSimple((isset($_REQUEST['pickupgroup']))?$_REQUEST['pickupgroup']:$amp_conf['DEVICE_PICKUPGROUP'])),
            array($account,'group',$db->escapeSimple((isset($_REQUEST['group']))?$_REQUEST['group']:'63')),
            array($account,'channel',$db->escapeSimple(($_REQUEST['channel'])?$_REQUEST['channel']:''))
        );
    }

    // Very bad
    $zapfields[] = array($account,'account',$db->escapeSimple($account));
    $zapfields[] = array($account,'callerid',$db->escapeSimple(($_REQUEST['description'])?$_REQUEST['description']." <".$account.'>':'device'." <".$account.'>'));

    $compiled = $db->prepare('INSERT INTO zap (id, keyword, data) values (?,?,?)');
    $result = $db->executeMultiple($compiled,$zapfields);
    if(DB::IsError($result)) {
        die_issabelpbx($result->getMessage()."<br><br>error adding to ZAP table");
    }
}

function core_devices_adddahdi($account) {
    global $db;
    global $amp_conf;

    foreach ($_REQUEST as $req=>$data) {
        if ( substr($req, 0, 8) == 'devinfo_' ) {
            $keyword = substr($req, 8);
            if ( $keyword == 'dial' && $data == '' ) {
                $dahdichan = $_REQUEST['devinfo_channel'] != '' ? $_REQUEST['devinfo_channel'] : $_REQUEST['channel'];
                $dahdifields[] = array($account, $keyword, 'DAHDI/'.$dahdichan);
            } elseif ($keyword == 'mailbox' && $data == '') {
                $dahdifields[] = array($account,'mailbox',$account.'@device');
            } else {
                $dahdifields[] = array($account, $keyword, $data);
            }
        }
    }

    if ( !is_array($dahdifields) ) { // left for compatibilty....lord knows why !
        $dahdifields = array(
            array($account,'context',$db->escapeSimple(($_REQUEST['context'])?$_REQUEST['context']:'from-internal')),
            array($account,'mailbox',$db->escapeSimple(($_REQUEST['mailbox'])?$_REQUEST['mailbox']:$account.'@device')),
            array($account,'immediate',$db->escapeSimple(($_REQUEST['immediate'])?$_REQUEST['immediate']:'no')),
            array($account,'signalling',$db->escapeSimple(($_REQUEST['signalling'])?$_REQUEST['signalling']:'fxo_ks')),
            array($account,'echocancel',$db->escapeSimple(($_REQUEST['echocancel'])?$_REQUEST['echocancel']:'yes')),
            array($account,'echocancelwhenbridged',$db->escapeSimple(($_REQUEST['echocancelwhenbridged'])?$_REQUEST['echocancelwhenbridged']:'no')),
            array($account,'echotraining',$db->escapeSimple(($_REQUEST['echotraining'])?$_REQUEST['echotraining']:'800')),
            array($account,'busydetect',$db->escapeSimple(($_REQUEST['busydetect'])?$_REQUEST['busydetect']:'no')),
            array($account,'busycount',$db->escapeSimple(($_REQUEST['busycount'])?$_REQUEST['busycount']:'7')),
            array($account,'callprogress',$db->escapeSimple(($_REQUEST['callprogress'])?$_REQUEST['callprogress']:'no')),
            array($account,'accountcode',$db->escapeSimple((isset($_REQUEST['accountcode']))?$_REQUEST['accountcode']:'')),
            array($account,'callgroup',$db->escapeSimple((isset($_REQUEST['callgroup']))?$_REQUEST['callgroup']:$amp_conf['DEVICE_CALLGROUP'])),
            array($account,'pickupgroup',$db->escapeSimple((isset($_REQUEST['pickupgroup']))?$_REQUEST['pickupgroup']:$amp_conf['DEVICE_PICKUPGROUP'])),
            array($account,'group',$db->escapeSimple((isset($_REQUEST['group']))?$_REQUEST['group']:'63')),
            array($account,'channel',$db->escapeSimple(($_REQUEST['channel'])?$_REQUEST['channel']:''))
        );
    }

    // Very bad
    $dahdifields[] = array($account,'account',$db->escapeSimple($account));
    $dahdifields[] = array($account,'callerid',$db->escapeSimple(($_REQUEST['description'])?$_REQUEST['description']." <".$account.'>':'device'." <".$account.'>'));

    $compiled = $db->prepare('INSERT INTO dahdi (id, keyword, data) values (?,?,?)');
    $result = $db->executeMultiple($compiled,$dahdifields);
    if(DB::IsError($result)) {
        die_issabelpbx($result->getMessage()."<br><br>error adding to DAHDi table");
    }
}

function core_devices_delzap($account) {
    global $db;

    $sql = "DELETE FROM zap WHERE id = '$account'";
    $result = $db->query($sql);
    if(DB::IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}

function core_devices_deldahdi($account) {
    global $db;

    $sql = "DELETE FROM dahdi WHERE id = '$account'";
    $result = $db->query($sql);
    if(DB::IsError($result)) {
        die_issabelpbx($result->getMessage().$sql);
    }
}

function core_devices_getzap($account) {
    global $db;
    $sql = "SELECT keyword,data FROM zap WHERE id = '$account'";
    $results = $db->getAssoc($sql);
    if(DB::IsError($results)) {
        $results = null;
    }
    return $results;
}

function core_devices_getdahdi($account) {
    global $db;
    $sql = "SELECT keyword,data FROM dahdi WHERE id = '$account'";
    $results = $db->getAssoc($sql);
    if(DB::IsError($results)) {
        $results = null;
    }
    return $results;
}
/* end page.devices.php functions */




function core_hint_get($account){
    global $astman;

    $chan_dahdi = ast_with_dahdi();
    // We should always check the AMPUSER in case they logged into a device
    // but we will fall back to the old methond if $astman not open although
    // I'm pretty sure everything else will puke anyhow if not running
    //
    if ($astman) {
        $device=$astman->database_get("AMPUSER",$account."/device");
        $device_arr = explode('&',$device);
        $sql = "SELECT dial from devices where id in ('".implode("','",$device_arr)."')";
    } else {
        $sql = "SELECT dial from devices where user = '{$account}'";
    }
    $results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

    //create an array of strings
    if (is_array($results)){
        foreach ($results as $result) {
            if ($chan_dahdi) {
                $dial[] = str_replace('ZAP', 'DAHDI', $result['dial']);
            } else {
                $dial[] = $result['dial'];
            }
        }
    }

    //create a string with & delimiter
    if (isset($dial) && is_array($dial)){
        $hint = implode("&",$dial);
    } else {
        if (isset($results[0]['dial'])) {
            $hint = $results[0]['dial'];
        } else {
            $hint = null;
        }
    }

    return $hint;
}



/* begin page.users.php functions */

// get the existing extensions
// the returned arrays contain [0]:extension [1]:name
function core_users_list($get_all=false) {
    $results = sql("SELECT extension,name,voicemail FROM users ORDER BY extension","getAll");

    //only allow extensions that are within administrator's allowed range
    foreach($results as $result){
        if ($get_all || checkRange($result[0])){
            $extens[] = array($result[0],$result[1],$result[2]);
        }
    }

    if (isset($extens)) {
        sort($extens);
        return $extens;
    } else {
        return null;
    }
}

function core_check_extensions($exten=true) {
    global $amp_conf;

    $extenlist = array();
    if (is_array($exten) && empty($exten)) {
        return $extenlist;
    }
    $sql = "SELECT extension, name FROM users ";
    if (is_array($exten)) {
        $sql .= "WHERE extension in ('".implode("','",$exten)."')";
    }
    $sql .= " ORDER BY CAST(extension AS UNSIGNED)";
    $results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

    $display = ($amp_conf['AMPEXTENSIONS'] == "deviceanduser")?'users':'extensions';
    foreach ($results as $result) {
        $thisexten = $result['extension'];
        $extenlist[$thisexten]['description'] = _dgettext("amp","User Extension: ").$result['name'];
        $extenlist[$thisexten]['status'] = 'INUSE';
        $extenlist[$thisexten]['edit_url'] = "config.php?display=$display&extdisplay=".urlencode($thisexten)."&skip=0";
    }
    return $extenlist;
}

function core_check_destinations($dest=true) {
    global $active_modules;
  global $amp_conf;

    $destlist = array();
    if (is_array($dest) && empty($dest)) {
        return $destlist;
    }
  // Check Inbound Routes
  //
    $sql = "SELECT extension, cidnum, description, destination FROM incoming ";
    if ($dest !== true) {
        $sql .= "WHERE destination in ('".implode("','",$dest)."')";
    }
    $sql .= "ORDER BY extension, cidnum";
    $results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

    //$type = isset($active_modules['announcement']['type'])?$active_modules['announcement']['type']:'setup';

    foreach ($results as $result) {
        $thisdest = $result['destination'];
        $thisid   = $result['extension'].'/'.$result['cidnum'];
        $destlist[] = array(
            'dest' => $thisdest,
            'description' => sprintf(__("Inbound Route: %s (%s)"),$result['description'],$thisid),
            'edit_url' => 'config.php?display=did&extdisplay='.urlencode($thisid),
        );
    }

  // Check Extension/User Destinations
  //
    $sql = "SELECT extension, name, busy_dest, noanswer_dest, chanunavail_dest FROM users ";
    if ($dest !== true) {
        $sql .= "WHERE (busy_dest in ('".implode("','",$dest)."')) OR (noanswer_dest in ('".implode("','",$dest)."')) OR (chanunavail_dest in ('".implode("','",$dest)."'))";
    }
    $results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

    $display = ($amp_conf['AMPEXTENSIONS'] == "deviceanduser")?'users':'extensions';
    $label   = ($amp_conf['AMPEXTENSIONS'] == "deviceanduser")?'User':'Exten';
    foreach ($results as $result) {
        $thisdest    = $result['busy_dest'];
        $thisid      = $result['extension'];
        $description = sprintf("%s: %s (%s)",$label,$result['name'],$thisid);
        $thisurl     = 'config.php?display='.$display.'&extdisplay='.urlencode($thisid);
        if (($dest === true && $thisdest != '') || $dest = $thisdest) {
            $destlist[] = array(
                'dest' => $thisdest,
                'description' => $description,
                'edit_url' => $thisurl,
            );
        }
        $thisdest = $result['noanswer_dest'];
        if (($dest === true && $thisdest != '') || $dest = $thisdest) {
            $destlist[] = array(
                'dest' => $thisdest,
                'description' => $description,
                'edit_url' => $thisurl,
            );
        }
        $thisdest = $result['chanunavail_dest'];
        if (($dest === true && $thisdest != '') || $dest = $thisdest) {
            $destlist[] = array(
                'dest' => $thisdest,
                'description' => $description,
                'edit_url' => $thisurl,
            );
        }
    }

    return $destlist;
}

function core_change_destination($old_dest, $new_dest) {
    $sql = 'UPDATE users SET noanswer_dest = "' . $new_dest . '" WHERE noanswer_dest = "' . $old_dest . '"';
    sql($sql, "query");

    $sql = 'UPDATE users SET busy_dest = "' . $new_dest . '" WHERE busy_dest = "' . $old_dest . '"';
    sql($sql, "query");

    $sql = 'UPDATE users SET chanunavail_dest = "' . $new_dest . '" WHERE chanunavail_dest = "' . $old_dest . '"';
    sql($sql, "query");

    $sql = 'UPDATE incoming SET destination = "' . $new_dest . '" WHERE destination = "' . $old_dest . '"';
    sql($sql, "query");
}


function core_sipname_check($sipname, $extension) {
    global $db;
    if (!isset($sipname) || trim($sipname)=='')
        return true;

    $sql = "SELECT sipname FROM users WHERE sipname = '$sipname' AND extension != '$extension'";
    $results = $db->getRow($sql,DB_FETCHMODE_ASSOC);
    if(DB::IsError($results)) {
        die_issabelpbx($results->getMessage().$sql);
    }

    if (isset($results['sipname']) && trim($results['sipname']) == $sipname)
        return false;
    else
        return true;
}

function core_users_add($vars, $editmode=false) {
    extract($vars);

    global $db;
    global $amp_conf;
    global $astman;

    $thisexten = isset($thisexten) ? $thisexten : '';

    if (trim($extension) == '' ) {
        echo "<script>javascript:alert('".__("You must put in an extension (or user) number")."');</script>";
        return false;
    }

    if (!ctype_digit($extension)) {
        echo "<script>javascript:alert('".__("Please enter a valid extension number.")."');</script>";
        return false;
    }

    //ensure this id is not already in use
    $extens = core_users_list();
    if(is_array($extens)) {
        foreach($extens as $exten) {
            if ($exten[0]===$extension) {
                echo "<script>javascript:alert('".sprintf(__("This user/extension %s is already in use"),$extension)."');</script>";
                return false;
            }
        }
    }

    $newdid_name = isset($newdid_name) ? $db->escapeSimple($newdid_name) : '';
    $newdid = isset($newdid) ? $newdid : '';
    $newdid = preg_replace("/[^0-9._XxNnZz\[\]\-\+]/" ,"", trim($newdid));

    $newdidcid = isset($newdidcid) ? trim($newdidcid) : '';
    if (!preg_match('/^priv|^block|^unknown|^restrict|^unavail|^anonym/',strtolower($newdidcid))) {
        $newdidcid = preg_replace("/[^0-9._XxNnZz\[\]\-\+]/" ,"", $newdidcid);
    }

    // Well more ugliness since the javascripts are already in here
    if ($newdid != '' || $newdidcid != '') {
        $existing = core_did_get($newdid, $newdidcid);
        if (! empty($existing)) {
            echo "<script>javascript:alert('".sprintf(__("A route with this DID/CID: %s/%s already exists"),$existing['extension'],$existing['cidnum'])."')</script>";
            return false;
        }
    }

    $sipname = isset($sipname) ? preg_replace("/\s/" ,"", trim($sipname)) : '';
    if (! core_sipname_check($sipname, $extension)) {
        echo "<script>javascript:alert('".__("This sipname: {$sipname} is already in use")."');</script>";
        return false;
    }


  // strip the ugly return of the gui radio funciton which comes back as "recording_out_internal=always" for example
  //
  if (isset($recording_in_external)) {
    $rec_tmp = explode('=',$recording_in_external,2);
    $recording_in_external = count($rec_tmp) == 2 ? $rec_tmp[1] : 'dontcare';
  } else {
    $recording_in_external = 'dontcare';
  }
  if (isset($recording_out_external)) {
    $rec_tmp = explode('=',$recording_out_external,2);
    $recording_out_external = count($rec_tmp) == 2 ? $rec_tmp[1] : 'dontcare';
  } else {
    $recording_out_external = 'dontcare';
  }
  if (isset($recording_in_internal)) {
    $rec_tmp = explode('=',$recording_in_internal,2);
    $recording_in_internal = count($rec_tmp) == 2 ? $rec_tmp[1] : 'dontcare';
  } else {
    $recording_in_internal = 'dontcare';
  }
  if (isset($recording_out_internal)) {
    $rec_tmp = explode('=',$recording_out_internal,2);
    $recording_out_internal = count($rec_tmp) == 2 ? $rec_tmp[1] : 'dontcare';
  } else {
    $recording_out_internal = 'dontcare';
  }
  if (isset($recording_ondemand)) {
    $rec_tmp = explode('=',$recording_ondemand,2);
    $recording_ondemand = count($rec_tmp) == 2 ? $rec_tmp[1] : 'disabled';
  } else {
    $recording_ondemand = 'disabled';
  }

  //escape quotes and any other bad chars:
  if (version_compare(phpversion(),'7.0.0','>')) {
      $outboundcid = isset($outboundcid) ? $db->escapeSimple($outboundcid) : '';
      $outboundcid_db = str_replace('\"','"',$outboundcid);
      $name = isset($name) ? $db->escapeSimple($name) : '';
      $name_db = str_replace('\"','"',$name);
  } else {
      if(is_callable('get_magic_quotes_gpc')) {
          if(!get_magic_quotes_gpc()) {
              $outboundcid = isset($outboundcid) ? $db->escapeSimple($outboundcid) : '';
              $outboundcid_db = str_replace('\"','"',$outboundcid);
              $name = isset($name) ? $db->escapeSimple($name) : '';
              $name_db = str_replace('\"','"',$name);
          }
      } else {
          $outboundcid = isset($outboundcid) ? $db->escapeSimple($outboundcid) : '';
          $outboundcid_db = str_replace('\"','"',$outboundcid);
          $name = isset($name) ? $db->escapeSimple($name) : '';
          $name_db = str_replace('\"','"',$name);
      }
  }

    //if voicemail is enabled, set the box@context to use
    //havn't checked but why is voicemail needed on users anyway?  Doesn't exactly make it modular !
    if ( function_exists('voicemail_mailbox_get') ) {
        $vmbox = voicemail_mailbox_get($extension);
        if ( $vmbox == null ) {
            $voicemail = "novm";
        } else {
            $voicemail = $vmbox['vmcontext'];
        }
    }

    // Clean replace any <> with () in display name - should have javascript stopping this but ...
    //
    $name = preg_replace(array('/</','/>/'), array('(',')'), trim($name));

    $vars[$vars[$noanswer_dest]] = isset($vars[$vars[$noanswer_dest]])?$vars[$vars[$noanswer_dest]]:'';
    $vars[$vars[$busy_dest]] = isset($vars[$vars[$busy_dest]])?$vars[$vars[$busy_dest]]:'';
    $vars[$vars[$chanunavail_dest]] = isset($vars[$vars[$chanunavail_dest]])?$vars[$vars[$chanunavail_dest]]:'';

    $noanswer_dest = !empty($noanswer_dest) && $vars[$vars[$noanswer_dest]] != '' ? q($vars[$vars[$noanswer_dest]]) : "''";
    $noanswer_cid = isset($noanswer_cid) ? q($noanswer_cid) : "''";
    $busy_dest = !empty($busy_dest) && $vars[$vars[$busy_dest]] != '' ? q($vars[$vars[$busy_dest]]) : "''";
    $busy_cid = isset($busy_cid) ? q($busy_cid) : "''";
    $chanunavail_dest = !empty($chanunavail_dest) && $vars[$vars[$chanunavail_dest]] != '' ? q($vars[$vars[$chanunavail_dest]]) : "''";
    $chanunavail_cid = isset($chanunavail_cid) ? q($chanunavail_cid) : "''";

    $sql="INSERT INTO users (extension,password,name,voicemail,ringtimer,noanswer,recording,outboundcid,sipname,noanswer_cid,busy_cid,chanunavail_cid,noanswer_dest,busy_dest,chanunavail_dest) values (";
    $sql .= implode(", ", array_map('q', array(
        $extension,
        isset($password)?$password:'',
        isset($name_db)?$name_db:'',
        isset($voicemail)?$voicemail:'default',
        isset($ringtimer)?$ringtimer:'',
        isset($noanswer)?$noanswer:'',
        isset($recording)?$recording:'',
        isset($outboundcid_db)?$outboundcid_db:'',
        isset($sipname)?$sipname:'',
    )));
  $sql .= ", $noanswer_cid, $busy_cid, $chanunavail_cid, $noanswer_dest, $busy_dest, $chanunavail_dest)";
    sql($sql);

    //write to astdb
    if ($astman) {
        $cid_masquerade = (isset($cid_masquerade) && trim($cid_masquerade) != "")?trim($cid_masquerade):$extension;
        $astman->database_put("AMPUSER",$extension."/password",isset($password)?$password:'');
        $astman->database_put("AMPUSER",$extension."/ringtimer",isset($ringtimer)?$ringtimer:'');
        $astman->database_put("AMPUSER",$extension."/cfringtimer",isset($cfringtimer)?$cfringtimer:0);
        $astman->database_put("AMPUSER",$extension."/concurrency_limit",isset($concurrency_limit)?$concurrency_limit:0);
        $astman->database_put("AMPUSER",$extension."/noanswer",isset($noanswer)?$noanswer:'');
        $astman->database_put("AMPUSER",$extension."/recording",isset($recording)?$recording:'');
        $astman->database_put("AMPUSER",$extension."/outboundcid",isset($outboundcid)?"\"".$outboundcid."\"":'');
        $astman->database_put("AMPUSER",$extension."/cidname",isset($name)?"\"".$name."\"":'');
        $astman->database_put("AMPUSER",$extension."/cidnum",$cid_masquerade);
        $astman->database_put("AMPUSER",$extension."/voicemail","\"".(isset($voicemail)?$voicemail:'')."\"");
    $astman->database_put("AMPUSER",$extension."/answermode","\""
        . (isset($answermode)?$answermode:'disabled')
        . "\"");

    $astman->database_put("AMPUSER",$extension."/recording/in/external","\"".$recording_in_external."\"");
    $astman->database_put("AMPUSER",$extension."/recording/out/external","\"".$recording_out_external."\"");
    $astman->database_put("AMPUSER",$extension."/recording/in/internal","\"".$recording_in_internal."\"");
    $astman->database_put("AMPUSER",$extension."/recording/out/internal","\"".$recording_out_internal."\"");
    $astman->database_put("AMPUSER",$extension."/recording/ondemand","\"".$recording_ondemand."\"");
    $astman->database_put("AMPUSER",$extension."/recording/priority","\""
        .(isset($recording_priority)?$recording_priority:'10')
        ."\"");

        // If not set then we are using system default so delete the tree all-together
        //
        if (isset($dialopts)) {
            $astman->database_put("AMPUSER",$extension."/dialopts", $dialopts);
        } else {
            $astman->database_del("AMPUSER",$extension."/dialopts");
        }

    $call_screen = isset($call_screen) ? $call_screen : '0';
        switch ($call_screen) {
            case '0':
                $astman->database_del("AMPUSER",$extension."/screen");
                break;
            case 'nomemory':
                $astman->database_put("AMPUSER",$extension."/screen","\"nomemory\"");
                break;
            case 'memory':
                $astman->database_put("AMPUSER",$extension."/screen","\"memory\"");
                break;
            default:
                break;
        }

        if (!$editmode) {
            $astman->database_put("AMPUSER",$extension."/device","\"".((isset($device))?$device:'')."\"");
        }

        if (trim($callwaiting) == 'enabled') {
            $astman->database_put("CW",$extension,"\"ENABLED\"");
        } else if (trim($callwaiting) == 'disabled') {
            $astman->database_del("CW",$extension);
        } else {
            echo "ERROR: this state should not exist<br>";
        }

        if (trim($pinless) == 'enabled') {
            $astman->database_put("AMPUSER",$extension."/pinless","\"NOPASSWD\"");
        } else if (trim($pinless) == 'disabled') {
            $astman->database_del("AMPUSER",$extension."/pinless");
        } else {
            echo "ERROR: this state should not exist<br>";
        }

        if(isset($parkinglot)) {
            if (trim($parkinglot) <> '') {
                $astman->database_put("AMPUSER",$extension."/parkinglot","$parkinglot");
            } else {
                $astman->database_del("AMPUSER",$extension."/parkinglot");
            }
        } else {
            $astman->database_del("AMPUSER",$extension."/parkinglot");
        }

        // Moved VmX setup to voicemail module since it is part of voicemail
        //
    } else {
        die_issabelpbx("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
    }

    // OK - got this far, if they entered a new inbound DID/CID let's deal with it now
    // remember - in the nice and ugly world of this old code, $vars has been extracted
    // newdid and newdidcid

    // Now if $newdid is set we need to add the DID to the routes
    //
    if ($newdid != '' || $newdidcid != '') {
        $did_dest                = 'from-did-direct,'.$extension.',1';
        $did_vars['extension']   = $newdid;
        $did_vars['cidnum']      = $newdidcid;
        $did_vars['privacyman']  = '';
        $did_vars['alertinfo']   = '';
        $did_vars['ringing']     = '';
        $did_vars['mohclass']    = 'default';
        $did_vars['description'] = $newdid_name;
        $did_vars['grppre']      = '';
        $did_vars['delay_answer']= '0';
        $did_vars['pricid']= '';
        core_did_add($did_vars, $did_dest);
    }

    return true;
}

function core_users_get($extension){
    global $db;
    global $amp_conf;
    global $astman;
    //get all the variables for the meetme
    $sql = "SELECT * FROM users WHERE extension = '$extension'";
    $results = $db->getRow($sql,DB_FETCHMODE_ASSOC);
    if(DB::IsError($results)) {
        die_issabelpbx($results->getMessage().$sql);
    }
    if (empty($results)) {
        return array();
    }

    if ($astman) {

    if (function_exists('paging_get_config')) {
          $answermode=$astman->database_get("AMPUSER",$extension."/answermode");
      $results['answermode'] = (trim($answermode) == '') ? 'disabled' : $answermode;
    }

        $cw = $astman->database_get("CW",$extension);
        $results['callwaiting'] = (trim($cw) == 'ENABLED') ? 'enabled' : 'disabled';
        $cid_masquerade=$astman->database_get("AMPUSER",$extension."/cidnum");
        $results['cid_masquerade'] = (trim($cid_masquerade) != "")?$cid_masquerade:$extension;

        $call_screen=$astman->database_get("AMPUSER",$extension."/screen");
        $results['call_screen'] = (trim($call_screen) != "")?$call_screen:'0';

        $pinless=$astman->database_get("AMPUSER",$extension."/pinless");
        $results['pinless'] = (trim($pinless) == 'NOPASSWD') ? 'enabled' : 'disabled';

        $results['parkinglot'] = $astman->database_get("AMPUSER",$extension."/parkinglot");

        $results['ringtimer'] = (int) $astman->database_get("AMPUSER",$extension."/ringtimer");

        $results['cfringtimer'] = (int) $astman->database_get("AMPUSER",$extension."/cfringtimer");
        $results['concurrency_limit'] = (int) $astman->database_get("AMPUSER",$extension."/concurrency_limit");

        $results['dialopts'] = $astman->database_get("AMPUSER",$extension."/dialopts");

        $results['recording_in_external'] = strtolower($astman->database_get("AMPUSER",$extension."/recording/in/external"));
        $results['recording_out_external'] = strtolower($astman->database_get("AMPUSER",$extension."/recording/out/external"));
        $results['recording_in_internal'] = strtolower($astman->database_get("AMPUSER",$extension."/recording/in/internal"));
        $results['recording_out_internal'] = strtolower($astman->database_get("AMPUSER",$extension."/recording/out/internal"));
        $results['recording_ondemand'] = strtolower($astman->database_get("AMPUSER",$extension."/recording/ondemand"));
        $results['recording_priority'] = (int) $astman->database_get("AMPUSER",$extension."/recording/priority");

    } else {
        die_issabelpbx("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
    }

    return $results;
}

function core_users_del($extension, $editmode=false){
    global $db;
    global $amp_conf;
    global $astman;

    //delete from devices table
    $sql="DELETE FROM users WHERE extension = ".q($extension);
    $results = $db->query($sql);
    if(DB::IsError($results)) {
        die_issabelpbx($results->getMessage().$sql);
    }

    //delete details to astdb
    if($astman)  {
        $astman->database_del("AMPUSER",$extension."/screen");
    }
    if ($astman && !$editmode) {
        // TODO just change this to delete everything
        $astman->database_deltree("AMPUSER/".$extension);
    }

    return true;
}

function core_users_directdid_get($directdid=""){
    return array();
}

function core_users_cleanastdb($extension) {
    // This is called to remove any ASTDB traces of the user after a deletion. Otherwise,
    // call forwarding, call waiting settings could hang around and bite someone if they
    // recycle an extension. Is called from page.xtns and page.users.
    global $amp_conf;
    global $astman;

    if ($astman) {
        $astman->database_del("CW",$extension);
        $astman->database_del("CF",$extension);
        $astman->database_del("CFB",$extension);
        $astman->database_del("CFU",$extension);
        $astman->database_del("DND",$extension);
    } else {
        die_issabelpbx("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
    }
}

function core_users_edit($extension, $vars){
    global $db;
    global $amp_conf;
    global $astman;

    //I we are editing, we need to remember existing user<->device mapping, so we can delete and re-add
    if ($astman) {
        $ud = $astman->database_get("AMPUSER",$extension."/device");
        $current_vmcontext = $astman->database_get("AMPUSER",$extension."/voicemail");
        $new_vmcontext = isset($vars['vmcontext']) ? $vars['vmcontext'] : 'novm';
        $vars['device'] = $ud;
    } else {
        die_issabelpbx("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
    }

    // clean and check the did to make sure it is not being used by another extension or in did routing
    //
    $newdid_name = isset($newdid_name) ? $db->escapeSimple($newdid_name) : '';
    $newdid = isset($vars['newdid']) ? $vars['newdid'] : '';
    $newdid = preg_replace("/[^0-9._XxNnZz\[\]\-\+]/" ,"", trim($newdid));

    $newdidcid = isset($vars['newdidcid']) ? trim($vars['newdidcid']) : '';
    if (!preg_match('/^priv|^block|^unknown|^restrict|^unavail|^anonym/',strtolower($newdidcid))) {
        $newdidcid = preg_replace("/[^0-9._XxNnZz\[\]\-\+]/" ,"", $newdidcid);
    }

    // Well more ugliness since the javascripts are already in here
    if ($newdid != '' || $newdidcid != '') {
        $existing = core_did_get($newdid, $newdidcid);
        if (! empty($existing)) {
            echo "<script>javascript:alert('".sprintf(__("A route with this DID/CID: %s/%s already exists"),$existing['extension'],$existing['cidnum'])."')</script>";
            return false;
        }
    }

    //delete and re-add
    if (core_sipname_check($vars['sipname'],$extension)) {
        core_users_del($extension, true);
        core_users_add($vars, true);

        // If the vmcontext has changed, we need to change all the links. In extension mode, the link
        // to the current fixed device will get changed, but none others will
        //
        if ($current_vmcontext != $new_vmcontext) {
            $user_devices = explode('&',$ud);
            foreach ($user_devices as $user_device) {
                exec("rm -f /var/spool/asterisk/voicemail/device/".$user_device);
                if ($new_vmcontext != 'novm') {
                    exec("/bin/ln -s /var/spool/asterisk/voicemail/".$new_vmcontext."/".$extension."/ /var/spool/asterisk/voicemail/device/".$user_device);
                }
            }
        }
    }
    return true;
}

function core_directdid_list(){
    return array();
}

function core_dahdichandids_add($description, $channel, $did) {
    global $db;


    if (!ctype_digit(trim($channel)) || trim($channel) == '') {
        echo "<script>javascript:alert('".__("Invalid Channel Number, must be numeric and not blank")."')</script>";
        return false;
    }
    if (trim($did) == '') {
        echo "<script>javascript:alert('".__("Invalid DID, must be a non-blank DID")."')</script>";
        return false;
    }

    $description = q($description);
    $channel     = q($channel);
    $did         = q($did);

    $sql = "INSERT INTO dahdichandids (channel, description, did) VALUES ($channel, $description, $did)";
    $results = $db->query($sql);
    if (DB::IsError($results)) {
        if ($results->getCode() == DB_ERROR_ALREADY_EXISTS) {
            echo "<script>javascript:alert('".__("Error Duplicate Channel Entry")."')</script>";
            return false;
        } else {
            die_issabelpbx($results->getMessage()."<br><br>".$sql);
        }
    }
    return true;
}

function core_dahdichandids_edit($description, $channel, $did) {
    global $db;

    $description = q($description);
    $channel     = q($channel);
    $did         = q($did);

    $sql = "UPDATE dahdichandids SET description = $description, did = $did WHERE channel = $channel";
    $results = $db->query($sql);
    if (DB::IsError($results)) {
        die_issabelpbx($results->getMessage()."<br><br>".$sql);
    }
    return true;
}

function core_dahdichandids_delete($channel) {
    global $db;

    $channel     = q($channel);

    $sql = "DELETE FROM dahdichandids WHERE channel = $channel";
    $results = $db->query($sql);
    if (DB::IsError($results)) {
        die_issabelpbx($results->getMessage()."<br><br>".$sql);
    }
    return true;
}

function core_dahdichandids_list() {
    global $db;

    $sql = "SELECT * FROM dahdichandids ORDER BY channel";
    return sql($sql,"getAll",DB_FETCHMODE_ASSOC);
}

function core_dahdichandids_get($channel) {
    global $db;

    $channel     = q($channel);

    $sql = "SELECT * FROM dahdichandids WHERE channel = $channel";
    return sql($sql,"getRow",DB_FETCHMODE_ASSOC);
}

/* end page.users.php functions */



/* begin page.trunks.php functions */

/**
 * @pram string; can be a trunk id, all or *, or registered/reg for just trunks that are registered
 * @pram boolean; true disables trunk, false is enables trunk
 */
function core_trunks_disable($trunk, $switch) {
    switch ($trunk) {
        case 'all':
        case '*':
            $trunks = core_trunks_getDetails();
            break;
        case 'reg':
        case 'registered':
            foreach (core_trunks_getDetails() as $t) {
                if($reg = core_trunks_getTrunkRegister($t['trunkid'])) {
                    $trunks[] = $t;
                }
            }
            break;
        case '':
            return false;//cannot call without a trunk
            break;
        default:
            $trunks[] = core_trunks_getDetails($trunk);
            break;
    }

    //return if no trunks!
    if (empty($trunks)) {
        return false;
    }

    foreach ($trunks as $t) {
        $trunk            = core_trunks_getDetails($t['trunkid']);
        $regstring         = core_trunks_getTrunkRegister($t['trunkid']);
        $userconfig        = core_trunks_getTrunkUserConfig($t['trunkid']);
        $peerdetails    = core_trunks_getTrunkPeerDetails($t['trunkid']);
        $disabled        = $switch ? 'on' : 'off';

        core_trunks_edit(
            $trunk['trunkid'],
            $trunk['channelid'],
            $trunk['dialoutprefix'],
            $trunk['maxchans'],
            $trunk['outcid'],
            $peerdetails,
            $trunk['usercontext'],
            $userconfig,
            $regstring,
            $trunk['keepcid'],
            $trunk['failscript'],
            $disabled,
            $trunk['name'],
            $trunk['provider'],
            $trunk['continue']
        );

    }
}

// we're adding ,don't require a $trunknum
function core_trunks_add($tech, $channelid, $dialoutprefix, $maxchans, $outcid, $peerdetails, $usercontext, $userconfig, $register, $keepcid, $failtrunk, $disabletrunk, $name="", $provider="", $continue="off", $dialopts=false) {
    global $db;
    $name = trim($name) == "" ? $channelid : $name;

    // find the next available ID
    $trunknum = 1;

    // This is pretty ugle, will fix when we redo trunks and routes with proper uniqueids.
    // get the list, sort them, then look for a hole and use it, or overflow to the end if
    // not and use that
    //
    $trunk_hash = array();
    foreach(core_trunks_list() as $trunk) {
        $trunknum = ltrim($trunk[0],"OUT_");
        $trunk_hash[] = $trunknum;
    }
    sort($trunk_hash);
    $trunknum = 1;
    foreach ($trunk_hash as $trunk_id) {
        if ($trunk_id != $trunknum) {
            break;
        }
        $trunknum++;
    }

    core_trunks_backendAdd($trunknum, $tech, $channelid, $dialoutprefix, $maxchans, $outcid, $peerdetails, $usercontext, $userconfig, $register, $keepcid, $failtrunk, $disabletrunk, $name, $provider, $continue, $dialopts);
    return $trunknum;
}

function core_trunks_del($trunknum, $tech = null) {
    global $db, $astman;

    if ($tech === null) { // in EditTrunk, we get this info anyways
        $tech = core_trunks_getTrunkTech($trunknum);
    }

    // conditionally, delete from iax or sip
    switch (strtolower($tech)) {
        case "iax2":
      $tech = "iax";
      // fall through
        case "iax":
        case "sip":
            sql("DELETE FROM `$tech` WHERE `id` IN ('tr-peer-$trunknum', 'tr-user-$trunknum', 'tr-reg-$trunknum')");
            break;
        case "pjsip":
            $tech='sip';
            sql("DELETE FROM `$tech` WHERE `id` IN ('pjsip-$trunknum', 'tr-reg-$trunknum')");

        break;
    }
    sql("DELETE FROM `trunks` WHERE `trunkid` = '$trunknum'");
    if ($astman) {
        $astman->database_del("TRUNK", $trunknum . '/dialopts');
    }
}

function core_trunks_edit($trunknum, $channelid, $dialoutprefix, $maxchans, $outcid, $peerdetails, $usercontext, $userconfig, $register, $keepcid, $failtrunk, $disabletrunk, $name="", $provider="", $continue='off', $dialopts = false) {
    global $db;
    $name = trim($name) == "" ? $channelid : $name;

    $tech = core_trunks_getTrunkTech($trunknum);
  if ($tech == "") {
    return false;
  }
    core_trunks_del($trunknum, $tech);
    core_trunks_backendAdd($trunknum, $tech, $channelid, $dialoutprefix, $maxchans, $outcid, $peerdetails, $usercontext, $userconfig, $register, $keepcid, $failtrunk, $disabletrunk, $name, $provider, $continue, $dialopts);
}

// just used internally by addTrunk() and editTrunk()
//obsolete
function core_trunks_backendAdd($trunknum, $tech, $channelid, $dialoutprefix, $maxchans, $outcid, $peerdetails, $usercontext, $userconfig, $register, $keepcid, $failtrunk, $disabletrunk, $name, $provider, $continue, $dialopts=false) {
    global $db, $astman;

    if  (is_null($dialoutprefix)) $dialoutprefix = ""; // can't be NULL

    //echo  "backendAddTrunk($trunknum, $tech, $channelid, $dialoutprefix, $maxchans, $outcid, $peerdetails, $usercontext, $userconfig, $register)";

    // change iax to "iax2" (only spot we actually store iax2, since its used by Dial()..)
    $techtemp = ((strtolower($tech) == "iax") ? "iax2" : $tech);
    $outval = (($techtemp == "custom") ? "AMP:".$channelid : strtoupper($techtemp).'/'.$channelid);
    unset($techtemp);

    $disable_flag = ($disabletrunk == "on")?1:0;

    switch (strtolower($tech)) {
        case "iax":
        case "iax2":
            core_trunks_addSipOrIax($peerdetails,'iax',$channelid,$trunknum,$disable_flag,'peer');
            if ($usercontext != ""){
                core_trunks_addSipOrIax($userconfig,'iax',$usercontext,$trunknum,$disable_flag,'user');
            }
            if ($register != ""){
                core_trunks_addRegister($trunknum,'iax',$register,$disable_flag);
            }
        break;
        case "sip":
            core_trunks_addSipOrIax($peerdetails,'sip',$channelid,$trunknum,$disable_flag,'peer');
            if ($usercontext != ""){
                core_trunks_addSipOrIax($userconfig,'sip',$usercontext,$trunknum,$disable_flag,'user');
            }
            if ($register != ""){
                core_trunks_addRegister($trunknum,'sip',$register,$disable_flag);
            }
        break;
        case "pjsip":
            core_trunks_addSipOrIax($userconfig,'sip',$channelid,$trunknum,$disable_flag,'pjsip');
            if ($register == "send") {
                $pjuser   = '';
                $pjsecret = '';
                $pjserver = '';
                $pjport   = '';

                $lineas = explode("\n",$userconfig);
                foreach($lineas as $linea) {
                    $partes = preg_split("/=/",$linea);
                    if($partes[0]=='username') {
                        $pjuser=$partes[1];
                    } else if($partes[0]=='secret') {
                        $pjsecret=$partes[1];
                    } else if($partes[0]=='server') {
                        $pjserver=$partes[1];
                    } else if($partes[0]=='port') {
                        $pjport=$partes[1];
                    }
                }

                $register_string = $pjuser.":".$pjsecret."@".$pjserver;
                if($pjport<>'') { $register_string.=":".$pjport; }
 
                core_trunks_addRegister($trunknum,'pjsip',$register_string,$disable_flag);
            }
        break;

    }

    $sql = "
        INSERT INTO `trunks`
        (`trunkid`, `name`, `tech`, `outcid`, `keepcid`, `maxchans`, `failscript`, `dialoutprefix`, `channelid`, `usercontext`, `provider`, `disabled`, `continue`)
        VALUES (
            '$trunknum',
            '".$db->escapeSimple($name)."',
            '".$db->escapeSimple($tech)."',
            '".$db->escapeSimple($outcid)."',
            '".$db->escapeSimple($keepcid)."',
            '".$db->escapeSimple($maxchans)."',
            '".$db->escapeSimple($failtrunk)."',
            '".$db->escapeSimple($dialoutprefix)."',
            '".$db->escapeSimple($channelid)."',
            '".$db->escapeSimple($usercontext)."',
            '".$db->escapeSimple($provider)."',
            '".$db->escapeSimple($disabletrunk)."',
            '".$db->escapeSimple($continue)."'
        )";
    sql($sql);

    if ($astman) {
        if ($dialopts !== false) {
            $astman->database_put("TRUNK", $trunknum . '/dialopts',$dialopts);
        } else {
            $astman->database_del("TRUNK", $trunknum . '/dialopts');
        }
    }
}

//TODO: replace with NEW table
//
function core_trunks_getTrunkTech($trunknum) {
    $tech = sql("SELECT `tech` FROM `trunks` WHERE `trunkid` = $trunknum", "getOne");
    if (!$tech) {
        return false;
    }
    $tech = strtolower($tech);
    if ($tech == "iax2") {
         $tech = "iax"; // same thing, here
    }
    return $tech;
}

//add trunk info to sip or iax table
function core_trunks_addSipOrIax($config,$table,$channelid,$trunknum,$disable_flag=0,$type='peer') {
    global $db;
    switch ($type) {
        case 'peer':
            $trunknum = 'tr-peer-'.$trunknum;
            break;
        case 'user':
            $trunknum = 'tr-user-'.$trunknum;
            break;
        case 'pjsip':
            $trunknum = 'pjsip-'.$trunknum;
           break;
    }

    $confitem['account'] = $channelid;
    $gimmieabreak = nl2br($config);
    $lines = preg_split('#<br />#',$gimmieabreak);
    foreach ($lines as $line) {
        $line = trim($line);
        if (count(preg_split('/=/',$line)) > 1) {
            $tmp = preg_split('/=/',$line,2);
            $key=trim($tmp[0]);
            $value=trim($tmp[1]);
            if (isset($confitem[$key]) && !empty($confitem[$key]))
                $confitem[$key].="&".$value;
            else
                $confitem[$key]=$value;
        }
    }
    // rember 1=disabled so we start at 2 (1 + the first 1)
    $seq = 1;
    foreach($confitem as $k=>$v) {
        $seq = ($disable_flag == 1) ? 1 : $seq+1;
        $dbconfitem[]=array($db->escapeSimple($k),$db->escapeSimple($v),$seq);
    }
    $compiled = $db->prepare("INSERT INTO $table (id, keyword, data, flags) values ('$trunknum',?,?,?)");
    $result = $db->executeMultiple($compiled,$dbconfitem);
    if(DB::IsError($result)) {
        die_issabelpbx($result->getMessage()."<br><br>INSERT INTO $table (id, keyword, data, flags) values ('$trunknum',?,?,'$disable_flag')");
    }
}

//get unique trunks
function core_trunks_getDetails($trunkid='') {
    global $db;
    global $amp_conf;
    global $astman;

    if ($trunkid != '') {
        $sql = "SELECT * FROM `trunks` WHERE `trunkid` = ?";
        $trunk = $db->getRow($sql,array($trunkid),DB_FETCHMODE_ASSOC);
        $tech = strtolower($trunk['tech']);
        switch ($tech) {
            case 'iax2':
                $trunk['tech'] = 'iax';
                break;
            default:
                $trunk['tech'] = $tech;
                break;
        }
        if ($astman) {
            $trunk['dialopts'] = $astman->database_get("TRUNK",$trunkid . "/dialopts");
        }
    } else {
        $sql = "SELECT * FROM `trunks` ORDER BY tech, name";
        $trunk = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
        if ($astman) {
            $tops = $astman->database_show('TRUNK');
            foreach ($trunk as $i => $t) {
                if (isset($tops['/TRUNK/' . $t['trunkid'] . '/dialopts'])) {
                    $trunk[$i]['dialopts'] = $tops['/TRUNK/' . $t['trunkid'] . '/dialopts'];
                } else {
                    $trunk[$i]['dialopts'] = false;
                }
            }
        }
    }
    return $trunk;
}

function core_trunks_listbyid() {
  $result = sql('SELECT * from `trunks` ORDER BY `trunkid`','getAll',DB_FETCHMODE_ASSOC);
  $trunk_list = array();
  foreach ($result as $trunk) {
    if ($trunk['name'] == '') {
          $tech = strtoupper($trunk['tech']);
          switch ($tech) {
              case 'IAX':
                  $trunk['name'] = 'IAX2/'.$trunk['channelid'];
                  break;
              case 'CUSTOM':
                  $trunk['name'] = 'AMP:'.$trunk['channelid'];
                  break;
              default:
                  $trunk['name'] = $tech.'/'.$trunk['channelid'];
                  break;
          }
    }
    $trunk_list[$trunk['trunkid']] = $trunk;
  }
  return $trunk_list;
}

function core_trunks_list($assoc = false) {
    // TODO: $assoc default to true, eventually..

    global $db;
    global $amp_conf;

    $sql = "SELECT `trunkid` , `tech` , `channelid` , `disabled` FROM `trunks` ORDER BY `trunkid`";
    $trunks = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

    $unique_trunks = array();
    foreach ($trunks as $trunk) {
        $trunk_id = "OUT_".$trunk['trunkid'];
        $disabled = $trunk['disabled'];
        $tech = strtoupper($trunk['tech']);
        switch ($tech) {
            case 'IAX':
                $dialstring = 'IAX2/'.$trunk['channelid'];
                break;
            case 'CUSTOM':
                $dialstring = 'AMP:'.$trunk['channelid'];
                break;
            default:
                $dialstring = $tech.'/'.$trunk['channelid'];
                break;
        }
        $unique_trunks[] = array($trunk_id, $dialstring, $disabled);
    }

    if ($assoc) {
        $trunkinfo = array();

        foreach ($unique_trunks as $trunk) {
            list($tech,$name) = explode('/',$trunk[1]);
            $trunkinfo[$trunk[1]] = array(
                'name' => $name,
                'tech' => $tech,
                'globalvar' => $trunk[0], // ick
                'value' => $trunk[2], // ??  no idea what this is.
            );
        }

        return $trunkinfo;
    } else {
        return $unique_trunks;
    }
}

function core_trunks_addRegister($trunknum,$tech,$reg,$disable_flag=0) {
    global $db;
    $reg = $db->escapeSimple(trim($reg));
    if($tech=='pjsip') { 
        $tech='sip';
    }
    $trunkreg = "tr-reg-$trunknum";
    sql("INSERT INTO $tech (id, keyword, data, flags) values (".q($trunkreg).", 'register', ".q($reg).', '.q($disable_flag).')');
}


function core_trunks_update_dialrules($trunknum, &$patterns, $delete = false) {
  global $db;

  $trunknum =  $db->escapeSimple($trunknum);
  $filter_prepend = '/[^0-9+*#wW]/';
  $filter_prefix = '/[^0-9*#+xnzXNZ\-\[\]]/';
  $filter_match =  '/[^0-9.*#+xnzXNZ\-\[\]]/';

  $insert_pattern = array();
  $seq = 0;
  foreach ($patterns as $pattern) {
    $match_pattern_prefix = $db->escapeSimple(preg_replace($filter_prefix,'',strtoupper(trim($pattern['match_pattern_prefix']))));
    $match_pattern_pass = $db->escapeSimple(preg_replace($filter_match,'',strtoupper(trim($pattern['match_pattern_pass']))));
    $prepend_digits = $db->escapeSimple(str_replace('W', 'w', preg_replace($filter_prepend,'',strtoupper(trim($pattern['prepend_digits'])))));
    if ($match_pattern_prefix.$match_pattern_pass == '') {
      continue;
    }
    // if duplicate prepend, get rid of subsequent since they will never be checked
    $hash_index = md5($match_pattern_prefix.$match_pattern_pass);
    if (!isset($insert_pattern[$hash_index])) {
      $insert_pattern[$hash_index] = array($match_pattern_prefix, $match_pattern_pass, $prepend_digits, $seq);
      $seq++;
    }
  }

  if ($delete) {
    sql('DELETE FROM `trunk_dialpatterns` WHERE `trunkid`='.q($trunknum));
  }
    $compiled = $db->prepare('INSERT INTO `trunk_dialpatterns` (`trunkid`, `match_pattern_prefix`, `match_pattern_pass`, `prepend_digits`, `seq`) VALUES ('.$trunknum.',?,?,?,?)');
    $result = $db->executeMultiple($compiled,$insert_pattern);
    if(DB::IsError($result)) {
        die_issabelpbx($result->getDebugInfo()."<br><br>".'error updating trunk_dialpatterns');
    }
}

function core_trunks_list_dialrules() {
  $rule_hash = array();

  $patterns = core_trunks_get_dialrules();
    foreach ($patterns as $pattern) {
      //$rule_hash[$pattern['trunkid']][] = $pattern['prepend_digits'].'^'.$pattern['match_pattern_prefix'].'|'.$pattern['match_pattern_pass'];
      $rule_hash[$pattern['trunkid']][] = $pattern;
    }
    return $rule_hash;
}

/* THIS HAS BEEN DEPRECATED BUT WILL REMAIN IN FOR A FEW RELEASES */
function core_trunks_parse_conf($filename, &$conf, &$section) {
    if (is_null($conf)) {
        $conf = array();
    }
    if (is_null($section)) {
        $section = "general";
    }

    if (file_exists($filename)) {
        $fd = fopen($filename, "r");
        while ($line = fgets($fd, 1024)) {
            if (preg_match("/^\s*([a-zA-Z0-9-_]+)\s*=\s*(.*?)\s*([;#].*)?$/",$line,$matches)) {
                // name = value
                // option line
                $conf[$section][ $matches[1] ] = $matches[2];
            } else if (preg_match("/^\s*\[(.+)\]/",$line,$matches)) {
                // section name
                $section = strtolower($matches[1]);
            } else if (preg_match("/^\s*#include\s+(.*)\s*([;#].*)?/",$line,$matches)) {
                // include another file

                if ($matches[1][0] == "/") {
                    // absolute path
                    $filename = $matches[1];
                } else {
                    // relative path
                    $filename =  dirname($filename)."/".$matches[1];
                }

                core_trunks_parse_conf($filename, $conf, $section);
            }
        }
    }
}

function core_trunks_getTrunkTrunkName($trunknum) {
    $name = sql("SELECT `name` FROM `trunks` WHERE `trunkid` = $trunknum", "getOne");
    return $name;
}

function core_trunks_getTrunkPeerDetails($trunknum) {
    global $db;

    $tech = core_trunks_getTrunkTech($trunknum);

    if($tech=='pjsip') { $tech='sip'; }

    if (!core_trunk_has_registrations($tech)) {
        return '';
    }
    $results = sql("SELECT keyword,data FROM $tech WHERE `id` = 'tr-peer-$trunknum' ORDER BY flags, keyword DESC","getAll");

    foreach ($results as $result) {
        if ($result[0] != 'account') {
            if (isset($confdetail))
                $confdetail .= $result[0] .'='. $result[1] . "\n";
            else
                $confdetail = $result[0] .'='. $result[1] . "\n";
        }
    }
    return $confdetail;
}

function core_trunks_getTrunkUserContext($trunknum) {
    $usercontext = sql("SELECT `usercontext` FROM `trunks` WHERE `trunkid` = $trunknum", "getOne");
    return ((isset($usercontext)) ? $usercontext : '');
}

function core_trunks_getTrunkUserConfig($trunknum) {
    global $db;

    $tech = core_trunks_getTrunkTech($trunknum);
    if (!core_trunk_has_registrations($tech)) {
        return '';
    }

    if($tech=='pjsip') {
        $results = sql("SELECT keyword,data FROM sip WHERE `id` = 'pjsip-$trunknum' ORDER BY flags, keyword DESC","getAll");
    } else {
        $results = sql("SELECT keyword,data FROM $tech WHERE `id` = 'tr-user-$trunknum' ORDER BY flags, keyword DESC","getAll");
    }

    foreach ($results as $result) {
        if ($result[0] != 'account') {
            if (isset($confdetail))
                $confdetail .= $result[0] .'='. $result[1] . "\n";
            else
                $confdetail = $result[0] .'='. $result[1] . "\n";
        }
    }
    return isset($confdetail)?$confdetail:null;
}

//get trunk account register string
function core_trunks_getTrunkRegister($trunknum) {
    $tech = core_trunks_getTrunkTech($trunknum);
    if($tech=='pjsip') { $tech='sip'; }
    if (!core_trunk_has_registrations($tech)){
        return '';
    }

    $results = sql("SELECT `keyword`, `data` FROM $tech WHERE `id` = 'tr-reg-$trunknum'","getAll");

    foreach ($results as $result) {
            $register = $result[1];
    }
    return isset($register)?$register:null;
}

function core_trunks_get_dialrules($trunknum = false) {
  global $db;
  if ($trunknum === false) {
    $sql = "SELECT * FROM `trunk_dialpatterns` ORDER BY `trunkid`, `seq`";
  } else {
    $trunknum = $db->escapeSimple($trunknum);
    $trunknum = q($trunknum);
    $sql = "SELECT * FROM `trunk_dialpatterns` WHERE `trunkid` = $trunknum  ORDER BY `seq`";
  }
  $patterns = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
  return $patterns;
}


//get outbound routes for a given trunk
function core_trunks_gettrunkroutes($trunknum) {
  $sql = 'SELECT a.seq, b.name FROM outbound_route_trunks a JOIN outbound_routes b ON a.route_id = b.route_id WHERE trunk_id = '.$trunknum;
  $results = sql( $sql, "getAll" ,DB_FETCHMODE_ASSOC);

  $routes = array();
  foreach ($results as $entry) {
    $routes[$entry['name']] = $entry['seq'];
  }
  return $routes;
}

function core_trunks_delete_dialrules($trunknum) {
  global $db;
  $trunknum = q($db->escapeSimple($trunknum));
    sql("DELETE FROM `trunk_dialpatterns` WHERE `trunkid` = $trunknum");
}


//-----------------------------------------------------------------------------------------------------------------------------------------
// The following APIs have all been removed and will result in crashes with traceback to obtain calling tree information

function core_trunks_addDialRules($trunknum, $dialrules) {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}

function core_trunk_has_registrations($type = ''){
    $types = array(
                'zap',
                'dahdi',
                'custom',
                ''
            );
    return !in_array($type, $types);

}

function core_trunks_deleteDialRules($trunknum) {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}

function core_trunks_getDialRules($trunknum) {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}

function core_trunks_readDialRulesFile() {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}


/* end page.trunks.php functions */


/* begin page.routing.php functions */

//-----------------------------------------------------------------------------------------------------------------------------------------

// function core_routing_getroutepassword($route)
// function core_routing_getrouteemergency($route)
// function core_routing_getrouteintracompany($route)
// function core_routing_getroutemohsilence($route)
// function core_routing_getroutecid($route)
function core_routing_get($route_id) {
  global $db;
  $rid = $db->escapeSimple($route_id);
  $rid = q($rid);
  $sql = 'SELECT a.*, b.seq FROM `outbound_routes` a JOIN `outbound_route_sequence` b ON a.route_id = b.route_id WHERE a.route_id='.$rid;
  $route = sql($sql,"getRow",DB_FETCHMODE_ASSOC);
  return $route;
}

// function core_routing_getroutenames()
function core_routing_list() {
  $sql = "SELECT a.*, b.seq FROM `outbound_routes` a JOIN `outbound_route_sequence` b ON a.route_id = b.route_id ORDER BY `seq`";
  $routes = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
  return $routes;
}

// function core_routing_setroutepriority($routepriority, $reporoutedirection, $reporoutekey)
function core_routing_setrouteorder($route_id, $seq) {
    global $db;
    $sql = "SELECT `route_id` FROM `outbound_route_sequence` ORDER BY `seq`";
    $sequence = $db->getCol($sql);
    if(DB::IsError($sequence)) {
        die_issabelpbx($sequence->getDebugInfo());
    }

    if ($seq != 'new') {
        $key = array_search($route_id,$sequence);
        if ($key === false) {
            return(false);
        }
    }
    switch ("$seq") {
    case 'up':
        if (!isset($sequence[$key-1])) break;
        $previous = $sequence[$key-1];
        $sequence[$key-1] = $route_id;
        $sequence[$key] = $previous;
        break;
    case 'down':
        if (!isset($sequence[$key+1])) break;
        $previous = $sequence[$key+1];
        $sequence[$key+1] = $route_id;
        $sequence[$key] = $previous;
        break;
    case 'top':
        unset($sequence[$key]);
        array_unshift($sequence,$route_id);
        break;
    case 'bottom':
        unset($sequence[$key]);
    case 'new':
        // fallthrough, no break
        $sequence[]=$route_id;
        break;
    case '0':
        unset($sequence[$key]);
        array_unshift($sequence,$route_id);
        break;
    default:
        if (!ctype_digit($seq)) {
            return false;
        }
        if ($seq >= count($sequence)-1) {
            unset($sequence[$key]);
            $sequence[] = $route_id;
            break;
        }
        if ($sequence[$seq] == $route_id) {
            break;
        }
        $sequence[$key] = "bookmark";
        $remainder = array_slice($sequence,$seq);
        array_unshift($remainder,$route_id);
        $sequence = array_merge(array_slice($sequence,0,$seq), $remainder);
        unset($sequence[array_search("bookmark",$sequence)]);
        break;
    }
    $insert_array = array();
    $seq = 0;
    $final_seq = false;
    foreach($sequence as $rid) {
        $insert_array[] = array($rid, $seq);
        if ($rid === $route_id) {
            $final_seq = $seq;
        }
        $seq++;
    }
    sql('DELETE FROM `outbound_route_sequence` WHERE 1');
    $compiled = $db->prepare('INSERT INTO `outbound_route_sequence` (`route_id`, `seq`) VALUES (?,?)');
    $result = $db->executeMultiple($compiled,$insert_array);
    if(DB::IsError($result)) {
        die_issabelpbx($result->getDebugInfo()."<br><br>".'error reordering outbound_route_sequence');
    }
    return $final_seq;
}

// function core_routing_del($name)
function core_routing_delbyid($route_id) {
  global $db;
  $route_id = q($db->escapeSimple($route_id));
  sql('DELETE FROM `outbound_routes` WHERE `route_id` ='.$route_id);
  sql('DELETE FROM `outbound_route_patterns` WHERE `route_id` ='.$route_id);
  sql('DELETE FROM `outbound_route_trunks` WHERE `route_id` ='.$route_id);
  sql('DELETE FROM `outbound_route_sequence` WHERE `route_id` ='.$route_id);
}

// function core_routing_trunk_del($trunknum)
function core_routing_trunk_delbyid($trunk_id) {
  global $db;
  $trunk_id = q($db->escapeSimple($trunk_id));
  sql('DELETE FROM `outbound_route_trunks` WHERE `trunk_id` ='.$trunk_id);
}

// function core_routing_rename($oldname, $newname)
function core_routing_renamebyid($route_id, $new_name) {
  global $db;
  $route_id = q($db->escapeSimple($route_id));
  $new_name = $db->escapeSimple($new_name);
  sql("UPDATE `outbound_routes` SET `name = '$new_name'  WHERE `route_id` = $route_id");
}

// function core_routing_getroutepatterns($route)
function core_routing_getroutepatternsbyid($route_id) {
  global $db;
  $tmp = $db->escapeSimple($route_id);
  $route_id = q($tmp);
  $sql = "SELECT * FROM `outbound_route_patterns` WHERE `route_id` = $route_id ORDER BY `match_pattern_prefix`, `match_pattern_pass`";
  $patterns = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
  return $patterns;
}

/* Utility function to determine required dialpattern and offsets for a specific dialpattern record.
 * Used when generating the dialplan and can be used by other modules that may be splicing into the
 * dialplan such as pinsets or others.
 */
function core_routing_formatpattern($pattern) {
  $exten = $pattern['match_pattern_prefix'].$pattern['match_pattern_pass'];
  $cid = $pattern['match_cid'];
  if (!preg_match("/^[0-9*+]+$/",$exten)) {
    // if # is detected above (as not in the list of acceptable patterns) then _ is appended due to Asterisk
    // particulars in dealing with #
    $exten = "_".$exten;
  }
  if ($cid != '' && !preg_match("/^[0-9*+]+$/",$cid)) {
    // same comment as above wrt to #
    $cid = "_".$cid;
  }
  $full_exten = $cid != '' ? $exten.'/'.$cid : $exten;
  // ticket #3998: the $pos is incorrect if a range is included such as 9[0-3]|NXX in the prefix.
  // in this example we end up with EXTEN:6 instead of the correct EXTEN:2
  //
  $pos = strlen(preg_replace('/(\[[^\]]*\])/','X',$pattern['match_pattern_prefix']));
  return array('prepend_digits' => $pattern['prepend_digits'], 'dial_pattern' => $full_exten, 'base_pattern' => $exten, 'offset' => $pos);
}

// function core_routing_getroutetrunks($route)
function core_routing_getroutetrunksbyid($route_id) {
  global $db;
  $tmp = $db->escapeSimple($route_id);
  $route_id = q($tmp);
  $sql = "SELECT `trunk_id` FROM `outbound_route_trunks` WHERE `route_id` = $route_id ORDER BY `seq`";
    $trunks = $db->getCol($sql);
    if(DB::IsError($trunks)) {
        die_issabelpbx($trunks->getDebugInfo());
    }
  return $trunks;
}

// function core_routing_edit($name,$patterns,$trunks,$pass,$emergency="",$intracompany="",$mohsilence="",$routecid="",$routecid_mode)
function core_routing_editbyid($route_id, $name, $outcid, $outcid_mode, $password, $emergency_route, $intracompany_route, $mohclass, $time_group_id, $patterns, $trunks, $seq = '', $dest = '') {
  global $db;

  $route_id = $db->escapeSimple($route_id);
  $name = $db->escapeSimple($name);
  $outcid = $db->escapeSimple($outcid);
  $outcid_mode = trim($outcid) == '' ? '' : $db->escapeSimple($outcid_mode);
  $password = $db->escapeSimple($password);
  $emergency_route = strtoupper($db->escapeSimple($emergency_route));
  $intracompany_route = strtoupper($db->escapeSimple($intracompany_route));
  $mohclass = $db->escapeSimple($mohclass);
  $seq = $db->escapeSimple($seq);
  $time_group_id = $time_group_id == ''? 'NULL':$db->escapeSimple($time_group_id);
  $dest = $db->escapeSimple($dest);
  $sql = "UPDATE `outbound_routes` SET
    `name`='$name', `outcid`='$outcid', `outcid_mode`='$outcid_mode', `password`='$password',
    `emergency_route`='$emergency_route', `intracompany_route`='$intracompany_route', `mohclass`='$mohclass',
    `time_group_id`=$time_group_id, `dest`='$dest' WHERE `route_id` = ".q($route_id);
  sql($sql);
  core_routing_updatepatterns($route_id, $patterns, true);
  core_routing_updatetrunks($route_id, $trunks, true);
  if ($seq != '') {
    core_routing_setrouteorder($route_id, $seq);
  }
}

// function core_routing_add($name,$patterns,$trunks,$method,$pass,$emergency="",$intracompany="",$mohsilence="",$routecid="",$routecid_mode="")
function core_routing_addbyid($name, $outcid, $outcid_mode, $password, $emergency_route, $intracompany_route, $mohclass, $time_group_id, $patterns, $trunks, $seq = 'new', $dest = '') {
  global $amp_conf;
  global $db;

  $name = $db->escapeSimple($name);
  $outcid = $db->escapeSimple($outcid);
  $outcid_mode = $db->escapeSimple($outcid_mode);
  $password = $db->escapeSimple($password);
  $emergency_route = strtoupper($db->escapeSimple($emergency_route));
  $intracompany_route = strtoupper($db->escapeSimple($intracompany_route));
  $mohclass = $db->escapeSimple($mohclass);
  $time_group_id = $time_group_id == ''? 'NULL':$db->escapeSimple($time_group_id);
  $dest = $db->escapeSimple($dest);
  $sql = "INSERT INTO `outbound_routes` (`name`, `outcid`, `outcid_mode`, `password`, `emergency_route`, `intracompany_route`, `mohclass`, `time_group_id`, `dest`)
    VALUES ('$name', '$outcid', '$outcid_mode', '$password', '$emergency_route', '$intracompany_route', '$mohclass', $time_group_id, '$dest')";
  sql($sql);

  if(method_exists($db,'insert_id')) {
      $route_id = $db->insert_id();
  } else {
      $route_id = $amp_conf["AMPDBENGINE"] == "sqlite3" ? sqlite_last_insert_rowid($db->connection) : mysql_insert_id($db->connection);
  }

  core_routing_updatepatterns($route_id, $patterns);
  core_routing_updatetrunks($route_id, $trunks);
  core_routing_setrouteorder($route_id, 'new');
  // this is lame, should change to do as a single call but for now this expects route_id to be in array for anything but new
  if ($seq != 'new') {
    core_routing_setrouteorder($route_id, $seq);
  }

  return ($route_id);
}

/* TODO: duplicate prepend_patterns is a problem as only one will win. We need to catch this and filter it out. We can silently trap it
         by hashing without the prepend (since a blank prepend is similar to no prepend) at a minimum and decide if we want to catch
         this and throw an error...
 */
function core_routing_updatepatterns($route_id, &$patterns, $delete = false) {
  global $db;

  $route_id =  $db->escapeSimple($route_id);
  $filter = '/[^0-9\*\#\+\-\.\[\]xXnNzZ]/';
  $insert_pattern = array();
  foreach ($patterns as $pattern) {
    $match_pattern_prefix = $db->escapeSimple(preg_replace($filter,'',strtoupper(trim($pattern['match_pattern_prefix']))));
    $match_pattern_pass = $db->escapeSimple(preg_replace($filter,'',strtoupper(trim($pattern['match_pattern_pass']))));
    $match_cid = $db->escapeSimple(preg_replace($filter,'',strtoupper(trim($pattern['match_cid']))));
    $prepend_digits = $db->escapeSimple(preg_replace($filter,'',strtoupper(trim($pattern['prepend_digits']))));

    if ($match_pattern_prefix.$match_pattern_pass.$match_cid == '') {
      continue;
    }

    $hash_index = md5($match_pattern_prefix.$match_pattern_pass.$match_cid);
    if (!isset($insert_pattern[$hash_index])) {
      $insert_pattern[$hash_index] = array($match_pattern_prefix, $match_pattern_pass, $match_cid, $prepend_digits);
    }
  }

  if ($delete) {
    sql('DELETE FROM `outbound_route_patterns` WHERE `route_id`='.q($route_id));
  }
    $compiled = $db->prepare('INSERT INTO `outbound_route_patterns` (`route_id`, `match_pattern_prefix`, `match_pattern_pass`, `match_cid`, `prepend_digits`) VALUES ('.$route_id.',?,?,?,?)');
    $result = $db->executeMultiple($compiled,$insert_pattern);
    if(DB::IsError($result)) {
        die_issabelpbx($result->getDebugInfo()."<br><br>".'error updating outbound_route_patterns');
    }
}

function core_routing_updatetrunks($route_id, &$trunks, $delete = false) {
  global $db;

  $route_id = $db->escapeSimple($route_id);
  $insert_trunk = array();
  $seq = 0;
  foreach ($trunks as $trunk) {
    $insert_trunk[] = array ($db->escapeSimple($trunk), $seq);
    $seq++;
  }
  if ($delete) {
    sql('DELETE FROM `outbound_route_trunks` WHERE `route_id`='.q($route_id));
  }
    $compiled = $db->prepare("INSERT INTO `outbound_route_trunks` (`route_id`, `trunk_id`, `seq`) VALUES ($route_id,?,?)");
    $result = $db->executeMultiple($compiled,$insert_trunk);
    if(DB::IsError($result)) {
        die_issabelpbx($result->getDebugInfo()."<br><br>".'error updating outbound_route_trunks');
    }
}

/* callback to Time Groups Module so it can display usage information
   of specific groups
 */
function core_timegroups_usage($group_id) {

  $group_id = q($group_id);
    $results = sql("SELECT route_id, name FROM outbound_routes WHERE time_group_id = $group_id","getAll",DB_FETCHMODE_ASSOC);
    if (empty($results)) {
        return array();
    } else {
        foreach ($results as $result) {
            $usage_arr[] = array(
                "url_query" => "display=routing&extdisplay=".$result['route_id'],
                "description" => sprintf(__("Outbound Route: %s"),$result['name']),
            );
        }
        return $usage_arr;
    }
}

//-----------------------------------------------------------------------------------------------------------------------------------------
// The following APIs have all been removed and will result in crashes with traceback to obtain calling tree information

function core_routing_getroutenames() {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}
function core_routing_setroutepriority($routepriority, $reporoutedirection, $reporoutekey) {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}
function core_routing_setroutepriorityvalue($key)
{
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}
function core_routing_add($name, $patterns, $trunks, $method, $pass, $emergency = "", $intracompany = "", $mohsilence = "", $routecid = "", $routecid_mode = "") {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}
function core_routing_edit($name, $patterns, $trunks, $pass, $emergency="", $intracompany = "", $mohsilence="", $routecid = "", $routecid_mode="") {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}
function core_routing_del($name) {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}
function core_routing_trunk_del($trunknum) {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}
function core_routing_rename($oldname, $newname) {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}
function core_routing_getroutepatterns($route) {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}
function core_routing_getroutetrunks($route) {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}
function core_routing_getroutepassword($route) {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}
function core_routing_getrouteemergency($route) {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}
function core_routing_getrouteintracompany($route) {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}
function core_routing_getroutemohsilence($route) {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}
function core_routing_getroutecid($route) {
  $trace = debug_backtrace();
  $function = $trace[0]['function'];
  die_issabelpbx("function: $function has been deprecated and removed");
}

/* end of outbound routes */

function core_indications_get($zone=false) {
    global $db;

    $sql = 'SELECT `name`, `iso`, `conf` FROM `indications_zonelist`';
    $orderby = ' ' . 'ORDER BY `name`';
    if ($zone === false) {
        return sql($sql.$orderby, 'getAll', DB_FETCHMODE_ASSOC);
    } else {
        $z = $db->escapeSimple($zone);
        $sql .= ' ' . "WHERE `iso` = '$z'";
        return sql($sql.$orderby, 'getRow', DB_FETCHMODE_ASSOC);
    }
}

function general_generate_indications() {
    global $db;
    global $amp_conf;

    $notify =& notifications::create($db);

    $country = $amp_conf['TONEZONE'];
    $filename = $amp_conf['ASTETCDIR'] . "/indications.conf";

    $zonelist = core_indications_get($country);
    $fd = fopen($filename, "w");

    if (empty($zonelist) || $fd === false) {
        $desc = sprintf(__("Failed to open %s for writing, aborting attempt to write the country indications. The file may be readonly or the permissions may be incorrect."), $filename);
        $notify->add_error('core','INDICATIONS',__("Failed to write indications.conf"), $desc);
        return;
    }
    $notify->delete('core', 'INDICATIONS');

    $indication_warning = ";--------------------------------------------------------------------------------;
; Do NOT edit this file as it is auto-generated by IssabelPBX. All modifications    ;
; to this file must be done via the web gui.                                        ;
;--------------------------------------------------------------------------------;\n\n";
     fwrite($fd, $indication_warning);
    fwrite($fd, "[general]\ncountry=".$country."\n\n[".$country."]\n");
    fwrite($fd, "description = {$zonelist['name']}\n");
    fwrite($fd, "{$zonelist['conf']}\n\n");

    fclose($fd);
}
/* end page.routing.php functions */



// init registered 'your' config load and config process functions
function core_users_configpageinit($dispnum) {
    global $currentcomponent;
    global $amp_conf;

    if ( $dispnum == 'users' || $dispnum == 'extensions' ) {
        // Setup option list we need
        $currentcomponent->addoptlistitem('recordoptions', 'Adhoc', __("On Demand"));
        $currentcomponent->addoptlistitem('recordoptions', 'Always', __("Always"));
        $currentcomponent->addoptlistitem('recordoptions', 'Never', __("Never"));
        $currentcomponent->setoptlistopts('recordoptions', 'sort', false);

        $currentcomponent->addoptlistitem('recording_options', 'always', __("Always"));
        $currentcomponent->addoptlistitem('recording_options', 'dontcare', __("Don't Care"));
        $currentcomponent->addoptlistitem('recording_options', 'never', __("Never"));
        $currentcomponent->setoptlistopts('recording_options', 'sort', false);

        for ($i=0; $i <= 20; $i++) {
            $currentcomponent->addoptlistitem('recording_priority_options', "$i", "$i");
        }

        $currentcomponent->addoptlistitem('recording_ondemand_options', 'disabled', __("Disable"));
        $currentcomponent->addoptlistitem('recording_ondemand_options', 'enabled', __("Enable"));
        $currentcomponent->setoptlistopts('recording_ondemand_options', 'sort', false);

        $currentcomponent->addoptlistitem('callwaiting', 'enabled', __("Enable"));
        $currentcomponent->addoptlistitem('callwaiting', 'disabled', __("Disable"));
        $currentcomponent->setoptlistopts('callwaiting', 'sort', false);

    if (function_exists('paging_get_config')) {
      $currentcomponent->addoptlistitem('answermode', 'disabled', __("Disable"));
      $currentcomponent->addoptlistitem('answermode', 'intercom', __("Intercom"));
      $currentcomponent->setoptlistopts('answermode', 'sort', false);
    }

        $currentcomponent->addoptlistitem('pinless', 'disabled', __("Disable"));
        $currentcomponent->addoptlistitem('pinless', 'enabled', __("Enable"));
        $currentcomponent->setoptlistopts('pinless', 'sort', false);

        $currentcomponent->addoptlistitem('call_screen', '0', __("Disable"));
        $currentcomponent->addoptlistitem('call_screen', 'nomemory', __("Screen Caller: No Memory"));
        $currentcomponent->addoptlistitem('call_screen', 'memory', __("Screen Caller: Memory"));
        $currentcomponent->setoptlistopts('call_screen', 'sort', false);

        $currentcomponent->addoptlistitem('ringtime', '0', __("Default"));
        $currentcomponent->addoptlistitem('cfringtime', '0', __("Default"));
        $currentcomponent->addoptlistitem('cfringtime', '-1', __("Always"));
        $currentcomponent->addoptlistitem('concurrency_limit', '0', __("No Limit"));
        for ($i=1; $i <= 120; $i++) {
            $currentcomponent->addoptlistitem('ringtime', "$i", "$i");
            $currentcomponent->addoptlistitem('cfringtime', "$i", "$i");
            $currentcomponent->addoptlistitem('concurrency_limit', "$i", "$i");
        }
        $currentcomponent->setoptlistopts('ringtime', 'sort', false);
        $currentcomponent->setoptlistopts('cfringtime', 'sort', false);
        $currentcomponent->setoptlistopts('concurrency_limit', 'sort', false);

        // Special CID handling to deal with Private, etc.
        //
        $js =
        'var mycid = thiscid.toLowerCase();
        if (isDialpattern(thiscid) || mycid.substring(0,4) == "priv" || mycid.substring(0,5) == "block" || mycid == "unknown" || mycid.substring(0,8) == "restrict" || mycid.substring(0,7) == "unavail" || mycid.substring(0,6) == "anonym") { return true } else { return false };
        ';
        $currentcomponent->addjsfunc('isValidCID(thiscid)', $js);

        // Add the 'proces' functions
        $currentcomponent->addguifunc('core_users_configpageload');
        // Ensure users is called in middle order ($sortorder = 5), this is to allow
        // other modules to call stuff before / after the processing of users if needed
        // e.g. Voicemail module needs to create mailbox BEFORE the users as the mailbox
        // context is needed by the add users function
        $currentcomponent->addprocessfunc('core_users_configprocess', 5);
    }
}

// Used below in usort
function dev_grp($a, $b) {
    if ($a['devicetype'] == $b['devicetype']) {
        return ($a['id'] < $b['id']) ? -1 : 1;
    } else {
        return ($a['devicetype'] > $b['devicetype']) ? -1 : 1;
    }
}

function core_users_configpageload() {
    global $currentcomponent;
    global $amp_conf;
    global $active_modules;

    // Ensure variables possibly extracted later exist
    $name = $outboundcid = $sipname = $cid_masquerade = $newdid_name = $newdid = $newdidcid = $call_screen = $pinless = null;

    // Init vars from $_REQUEST[]
    $display = isset($_REQUEST['display'])?$_REQUEST['display']:null;;
    $action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
    $extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
    $tech_hardware = isset($_REQUEST['tech_hardware'])?$_REQUEST['tech_hardware']:null;

    if ( $action == 'del' ) { // Deleted

        $currentcomponent->addguielem('_top', new gui_subheading('del', $extdisplay.' '.__("deleted"), false));

    } elseif ( $display == 'extensions' && ($extdisplay == '' && $tech_hardware == '') ) { // Adding

        // do nothing as you want the Devices to handle this bit

    } else {

        if ( is_string($extdisplay) ) {

            if (!isset($GLOBALS['abort']) || $GLOBALS['abort'] !== true) {
                $extenInfo=core_users_get($extdisplay);
                extract($extenInfo);
            }
            if (isset($deviceInfo) && is_array($deviceInfo))
                extract($deviceInfo);

            if ( $display == 'extensions' ) {
                $currentcomponent->addguielem('_top', new gui_pageheading('title', __("Edit Extension").": $extdisplay", false), 0);
                if (!isset($GLOBALS['abort']) || $GLOBALS['abort'] !== true) {

                    $usage_list = framework_display_destination_usage(core_getdest($extdisplay));
                    if (!empty($usage_list)) {
                        $currentcomponent->addguielem('_top', new gui_link_label('dests', $usage_list['text'], $usage_list['tooltip'], true), 0);
                    }
                }
            } else {
                $currentcomponent->addguielem('_top', new gui_pageheading('title', __("User").": $extdisplay", false), 0);
                if (!isset($GLOBALS['abort']) || $GLOBALS['abort'] !== true) {
                    //$tlabel = sprintf(__("Delete User %s"),$extdisplay);
                    //$label = '<span><img title="'.$tlabel.'" alt="" src="images/user_delete.png"/>&nbsp;'.$tlabel.'</span>';
                    //$currentcomponent->addguielem('_top', new gui_link('del', $label, $delURL, true, false), 0);

                    $usage_list = framework_display_destination_usage(core_getdest($extdisplay));
                    if (!empty($usage_list)) {
                        $currentcomponent->addguielem('_top', new gui_link_label('dests', $usage_list['text'], $usage_list['tooltip'], true), 0);
                    }
                }
            }

        } elseif ( $display != 'extensions' ) {
            $currentcomponent->addguielem('_top', new gui_pageheading('title', __("Add User/Extension")), 0);
        }

        // Setup vars for use in the gui later on
        $fc_logon = featurecodes_getFeatureCode('core', 'userlogon');
        $fc_logoff = featurecodes_getFeatureCode('core', 'userlogoff');

        $msgInvalidExtNum = __("Please enter a valid extension number.");
        $msgInvalidCidNum = __("Please enter a valid CID Num Alias (must be a valid number).");
        $msgInvalidExtPwd = __("Please enter valid User Password using numbers only");
        $msgInvalidDispName = __("Please enter a valid Display Name");
        $msgInvalidOutboundCID = __("Please enter a valid Outbound CID");
        $msgInvalidPause = __("Please enter a valid pause time in seconds, using digits only");
        $msgInvalidDIDNum = __("You have entered a non-standard dialpattern for your DID. You can only enter standard dialpatterns. You must use the inbound routing form to enter non-standard patterns");
        $msgInvalidCIDNum = __("Please enter a valid CallerID Number or leave it blank for your Assigned DID/CID pair");

        // This is the actual gui stuff
        $currentcomponent->addguielem('_top', new gui_hidden('action', ($extdisplay ? 'edit' : 'add')));
        $currentcomponent->addguielem('_top', new gui_hidden('extdisplay', $extdisplay));

        if ( $display == 'extensions' ) {
            $section = ($extdisplay ? __("Edit Extension") : __("Add Extension"));
        } else {
            $section = ($extdisplay ? __("Edit User") : __("Add User"));
        }
        if ( trim($extdisplay) != '' ) {
            $currentcomponent->addguielem($section, new gui_hidden('extension', $extdisplay), 2);
        } else {
            $currentcomponent->addguielem($section, new gui_textbox('extension', $extdisplay, __("User Extension"), __("The extension number to dial to reach this user."), '!isInteger()', $msgInvalidExtNum, false), 3);
        }
        if ( $display != 'extensions' ) {
            $currentcomponent->addguielem($section, new gui_password('password', $password, __("User Password"), __("A user will enter this password when logging onto a device.").' '.$fc_logon.' '.__("logs into a device.").' '.$fc_logoff.' '.__("logs out of a device."), '!isInteger() && !isWhitespace()', $msgInvalidExtPwd, true));
            // extra JS function check required for blank password warning -- call last in the onsubmit() function
            $currentcomponent->addjsfunc('onsubmit()', "\treturn checkBlankUserPwd();\n", 9);
        }
        if ($action == 'add') {
            $currentcomponent->addjsfunc('onsubmit()', "
                var exten = $('#extension').val();
            var ajax_result = true;
            $('#error').remove();
            $.ajax({
                type: 'POST',
                    url: 'config.php',
                    data: 'handler=api&function=framework_get_conflict_url_helper&args=' + exten,
                    dataType: 'json',
                    cache: false,
                    async: false,
                    success: function(data, textStatus, XMLHttpRequest) {
                        if (data.length !== 0) {
                            $('#title').after('<div id=\"error\"><h5>".__("Conflicting Extensions")."</h5>' + data + '</div>');
                            ajax_result = false;
        }
        },
            error: function(data) {
                console.log('an error was recevied: ' + data);
                // TODO: Should we stop the submital and do something here?
        }
        });
        if (!ajax_result) {
            alert('". __("Extension number conflict, please choose another.") . "');
            $('#extension').focus();
            return false;
        }", 9);
        }
        $currentcomponent->addguielem($section, new gui_textbox('name', $name, __("Display Name"), __("The CallerID name for calls from this user will be set to this name. Only enter the name, NOT the number."),  '!isAlphanumeric() || isWhitespace()', $msgInvalidDispName, false));
        $cid_masquerade = (trim($cid_masquerade) == $extdisplay)?"":$cid_masquerade;
        $currentcomponent->addguielem($section, new gui_textbox('cid_masquerade', $cid_masquerade, __("CID Num Alias"), __("The CID Number to use for internal calls, if different from the extension number. This is used to masquerade as a different user. A common example is a team of support people who would like their internal CallerID to display the general support number (a ringgroup or queue). There will be no effect on external calls."), '!isWhitespace() && !isInteger()', $msgInvalidCidNum, false));
        $currentcomponent->addguielem($section, new gui_textbox('sipname', $sipname, __("SIP Alias"), __("If you want to support direct sip dialing of users internally or through anonymous sip calls, you can supply a friendly name that can be used in addition to the users extension to call them.")));

        // If user mode, list devices associated with this user
        //
        if ($display == 'users' && trim($extdisplay != '')) {
            $section = __("User Devices");
            $device_list = core_devices_list('all','full');

            usort($device_list,'dev_grp');

            $link_count = 0;
            foreach ($device_list as $device_item) {
                if ($device_item['user'] == $extdisplay) {
                    $editURL = $_SERVER['PHP_SELF'].'?display=devices&skip=0&extdisplay='.$device_item['id'];
                    $device_icon = ($device_item['devicetype'] == 'fixed') ? 'images/telephone_key.png' : 'images/telephone_edit.png';
                    $device_label  = '&nbsp;';
                    $device_label .=  __("Edit:");
                    $device_label .= '&nbsp;'.$device_item['id'].'&nbsp;'.$device_item['description'];

                    $device_label = '<span>
                        <img width="16" height="16" border="0" title="Edit Device" alt="Edit Device" src="'.$device_icon.'"/>'.$device_label.
                        '</span> ';

                    $currentcomponent->addguielem($section, new gui_link('dev'.$link_count++, $device_label, $editURL, true, false), 2);
                }
            }
        }

        $section = __("Extension Options");

        $currentcomponent->addguielem($section, new gui_textbox('outboundcid', $outboundcid, __("Outbound CID"), __("Overrides the CallerID when dialing out a trunk. Any setting here will override the common outbound CallerID set in the Trunks admin.<br><br>Format: <b>\"caller name\" &lt;#######&gt;</b><br><br>Leave this field blank to disable the outbound CallerID feature for this user."), '!isCallerID()', $msgInvalidOutboundCID, true),3);

        $has_park = isset($active_modules['parking'])?1:0;
        if($has_park==1) {
            $plots = parking_get('all');
            if(count($plots)>1) {
                $select_parking_lot=array();
                foreach($plots as $plot) {
                    if($plot['name']=='Default Lot') {
                        $currentcomponent->addoptlistitem('parkinglots', '', __("Default Lot"));
                    } else {
                        $currentcomponent->addoptlistitem('parkinglots', 'parkinglot_'.$plot['name'], $plot['name']);
                    }
                } 
                $currentcomponent->addguielem($section, new gui_selectbox('parkinglot', $currentcomponent->getoptlist('parkinglots'), $parkinglot, __("Parkinglot"), __("Parking lot assigned to this extension."), false));
            }
        }

        $ringtimer = (isset($ringtimer) ? $ringtimer : '0');
        $dialopts = isset($dialopts) ? $dialopts : false;
        $disable_dialopts = $dialopts === false;
        $currentcomponent->addguielem($section, new gui_textbox_check('dialopts', $dialopts, __("Asterisk Dial Options"), __("Cryptic Asterisk Dial Options, check to customize for this extension or un-check to use system defaults set in Advanced Options. These will not apply to trunk options which are configured with the trunk."), '', '', true, 0, $disable_dialopts, '<small>' . __("Override") . '</small>', $amp_conf['DIAL_OPTIONS'], true));

        $currentcomponent->addguielem($section, new gui_selectbox('ringtimer', $currentcomponent->getoptlist('ringtime'), $ringtimer, __("Ring Time"), __("Number of seconds to ring prior to going to voicemail. Default will use the value set in Advanced Settings. If no voicemail is configured this will be ignored."), false));

        if (!isset($cfringtimer)) {
            if ($amp_conf['CFRINGTIMERDEFAULT'] < 0 || ctype_digit($amp_conf['CFRINGTIMERDEFAULT'])) {
                $cfringtimer = $amp_conf['CFRINGTIMERDEFAULT'] < 0 ? -1 : ($amp_conf['CFRINGTIMERDEFAULT'] > 120 ? 120 : $amp_conf['CFRINGTIMERDEFAULT']);
            } else {
                $cfringtimer = 0;
            }
        }
        $currentcomponent->addguielem($section, new gui_selectbox('cfringtimer', $currentcomponent->getoptlist('cfringtime'), $cfringtimer, __("Call Forward Ring Time"), __("Number of seconds to ring during a Call Forward, Call Forward Busy or Call Forward Unavailable call prior to continuing to voicemail or specified destination. Setting to Always will not return, it will just continue to ring. Default will use the current Ring Time. If voicemail is disabled and their is not destination specified, it will be forced into Always mode"), false));
        if (!isset($callwaiting)) {
            if ($amp_conf['ENABLECW']) {
                $callwaiting = 'enabled';
            } else {
                $callwaiting = 'disabled';
            }
        }

        $concurrency_limit = isset($concurrency_limit) ? $concurrency_limit : $amp_conf['CONCURRENCYLIMITDEFAULT'];
        $currentcomponent->addguielem($section, new gui_selectbox('concurrency_limit', $currentcomponent->getoptlist('concurrency_limit'), $concurrency_limit, __("Outbound Concurrency Limit"), __("Maximum number of outbound simultaneous calls that an extension can make. This is also very useful as a Security Protection against a system that has been compromised. It will limit the number of simultaneous calls that can be made on the compromised extension."), false));

        $currentcomponent->addguielem($section, new gui_selectbox('callwaiting', $currentcomponent->getoptlist('callwaiting'), $callwaiting, __("Call Waiting"), __("Set the initial/current Call Waiting state for this user's extension"), false));
        if (function_exists('paging_get_config')) {
            $answermode = isset($answermode) ? $answermode : $amp_conf['DEFAULT_INTERNAL_AUTO_ANSWER'];
            $currentcomponent->addguielem($section, new gui_selectbox('answermode', $currentcomponent->getoptlist('answermode'), $answermode, __("Internal Auto Answer"), __("When set to Intercom, calls to this extension/user from other internal users act as if they were intercom calls meaning they will be auto-answered if the endpoint supports this feature and the system is configured to operate in this mode. All the normal white list and black list settings will be honored if they are set. External calls will still ring as normal, as will certain other circumstances such as blind transfers and when a Follow Me is configured and enabled. If Disabled, the phone rings as a normal phone."), false));
        }
        $currentcomponent->addguielem($section, new gui_selectbox('call_screen', $currentcomponent->getoptlist('call_screen'), $call_screen, __("Call Screening"),__("Call Screening requires external callers to say their name, which will be played back to the user and allow the user to accept or reject the call.  Screening with memory only verifies a caller for their CallerID once. Screening without memory always requires a caller to say their name. Either mode will always announce the caller based on the last introduction saved with that CallerID. If any user on the system uses the memory option, when that user is called, the caller will be required to re-introduce themselves and all users on the system will have that new introduction associated with the caller's CallerID."), false));
        $currentcomponent->addguielem($section, new gui_selectbox('pinless', $currentcomponent->getoptlist('pinless'), $pinless, __("Pinless Dialing"), __("Enabling Pinless Dialing will allow this extension to bypass any pin codes normally required on outbound calls"), false));

        $section = __("Assigned DID/CID");
        $currentcomponent->addguielem($section, new gui_textbox('newdid_name', $newdid_name, __("DID Description"), __("A description for this DID, such as \"Fax\"")), 4);
        $currentcomponent->addguielem($section, new gui_textbox('newdid', $newdid, __("Add Inbound DID"), __("A direct DID that is associated with this extension. The DID should be in the same format as provided by the provider (e.g. full number, 4 digits for 10x4, etc).<br><br>Format should be: <b>XXXXXXXXXX</b><br><br>.An optional CID can also be associated with this DID by setting the next box"),'!isDialpattern()',$msgInvalidDIDNum,true), 4);
        $currentcomponent->addguielem($section, new gui_textbox('newdidcid', $newdidcid, __("Add Inbound CID"), __("Add a CID for more specific DID + CID routing. A DID must be specified in the above Add DID box. In addition to standard dial sequences, you can also put Private, Blocked, Unknown, Restricted, Anonymous and Unavailable in order to catch these special cases if the Telco transmits them."),"!frm_${display}_isValidCID()",$msgInvalidCIDNum,true), 4);

        $dids = core_did_list('extension');
        $did_count = 0;
        foreach ($dids as $did) {
            $did_dest = preg_split('/,/',$did['destination']);
            if (isset($did_dest[1]) && $did_dest[1] === $extdisplay) {

                $did_title = ($did['description'] != '') ? $did['description'] : __("DID / CID");

                $addURL = $_SERVER['PHP_SELF'].'?display=did&&extdisplay='.$did['extension'].'/'.$did['cidnum'];
                $did_icon = 'images/email_edit.png';
                $did_label = trim($did['extension']) == '' ? ' '.__("Any DID") : ' '.$did['extension'];
                if (trim($did['cidnum']) != '') {
                    $did_label .= ' / '.$did['cidnum'];
                }
                if (trim($did['description']) != '') {
                    $did_label .= ' ('.$did['description'].')';
                }

                $did_icon = 'fa-envelope-o';
                $final_did_label = '<span class="icon mr-1"><i class="fa '.$did_icon.'" title="'.$did_title.'"></i></span>'.$did_label;

                $currentcomponent->addguielem($section, new gui_link('did_'.$did_count++, $final_did_label, $addURL, true, false), 4);
            }
        }

        $section = __("Recording Options");

        $recording_in_external = isset($recording_in_external) ? $recording_in_external : 'dontcare';
        $recording_out_external = isset($recording_out_external) ? $recording_out_external : 'dontcare';
        $recording_in_internal = isset($recording_in_internal) ? $recording_in_internal : 'dontcare';
        $recording_out_internal = isset($recording_out_internal) ? $recording_out_internal : 'dontcare';
        $recording_ondemand = isset($recording_ondemand) ? $recording_ondemand : 'disabled';
        $recording_priority = isset($recording_priority) ? $recording_priority : '10';
        $currentcomponent->addguielem($section, new gui_radio('recording_in_external', $currentcomponent->getoptlist('recording_options'), $recording_in_external, __('Inbound External Calls'), __("Recording of inbound calls from external sources.")));
        $currentcomponent->addguielem($section, new gui_radio('recording_out_external', $currentcomponent->getoptlist('recording_options'), $recording_out_external, __('Outbound External Calls'), __("Recording of outbound calls to external sources.")));
        $currentcomponent->addguielem($section, new gui_radio('recording_in_internal', $currentcomponent->getoptlist('recording_options'), $recording_in_internal, __('Inbound Internal Calls'), __("Recording of calls received from other extensions on the system.")));
        $currentcomponent->addguielem($section, new gui_radio('recording_out_internal', $currentcomponent->getoptlist('recording_options'), $recording_out_internal, __('Outbound Internal Calls'), __("Recording of calls made to other extensions on the system.")));
        $currentcomponent->addguielem($section, new gui_radio('recording_ondemand', $currentcomponent->getoptlist('recording_ondemand_options'), $recording_ondemand, __('On Demand Recording'), __("Enable or disable the ability to do on demand (one-touch) recording. The overall calling policy rules still apply and if calls are already being recorded they can not be paused.")));
        $currentcomponent->addguielem($section, new gui_selectbox('recording_priority', $currentcomponent->getoptlist('recording_priority_options'), $recording_priority, __("Record Priority Policy"), __("Call recording policy priority relative to other extensions when there is a conflict between an extension wanting recording and the other not wanting it. The higher of the two determines the policy, on a tie the global policy (caller or callee) determines the policy."), false));

        $section = __("Optional Destinations");
        $noanswer_dest = isset($noanswer_dest) ? $noanswer_dest : '';
        $busy_dest = isset($busy_dest) ? $busy_dest : '';
        $chanunavail_dest = isset($chanunavail_dest) ? $chanunavail_dest : '';

        $noanswer_cid = isset($noanswer_cid) ? $noanswer_cid : '';
        $busy_cid = isset($busy_cid) ? $busy_cid : '';
        $chanunavail_cid = isset($chanunavail_cid) ? $chanunavail_cid : '';

        if ($amp_conf['CWINUSEBUSY']) {
            $helptext = __('Optional destination call is routed to when the call is not answered on an otherwise idle phone. If the phone is in use and the call is simply ignored, then the busy destination will be used.');
        } else {
            $helptext = __('Optional destination call is routed to when the call is not answered.');
        }
        $nodest_msg = __('Unavail Voicemail if Enabled');
        $currentcomponent->addguielem($section, new gui_drawselects('noanswer_dest', '0', $noanswer_dest, __('No Answer'), $helptext, false, '', $nodest_msg),5,9);
        $currentcomponent->addguielem($section, new gui_textbox('noanswer_cid', $noanswer_cid, '&nbsp;&nbsp;'.__("CID Prefix"), __("Optional CID Prefix to add before sending to this no answer destination.")),5,9);

        if ($amp_conf['CWINUSEBUSY']) {
            $helptext = __('Optional destination the call is routed to when the phone is busy or the call is rejected by the user. This destination is also used on an unanswered call if the phone is in use and the user chooses not to pickup the second call.');
        } else {
            $helptext = __('Optional destination the call is routed to when the phone is busy or the call is rejected by the user.');
        }
        $nodest_msg = __('Busy Voicemail if Enabled');
        $currentcomponent->addguielem($section, new gui_drawselects('busy_dest', '1', $busy_dest, __('Busy'), $helptext, false, '', $nodest_msg),5,9);
        $currentcomponent->addguielem($section, new gui_textbox('busy_cid', $busy_cid, '&nbsp;&nbsp;'.__("CID Prefix"), __("Optional CID Prefix to add before sending to this busy destination.")),5,9);

        $helptext = __('Optional destination the call is routed to when the phone is offline, such as a softphone currently off or a phone unplugged.');
        $nodest_msg = __('Unavail Voicemail if Enabled');
        $currentcomponent->addguielem($section, new gui_drawselects('chanunavail_dest', '2', $chanunavail_dest, __('Not Reachable'), $helptext, false, '', $nodest_msg),5,9);
        $currentcomponent->addguielem($section, new gui_textbox('chanunavail_cid', $chanunavail_cid, '&nbsp;&nbsp;'.__("CID Prefix"), __("Optional CID Prefix to add before sending to this not reachable destination.")),5,9);

    }
}

function core_users_configprocess() {

    //create vars from the request
    extract($_REQUEST);

    //make sure we can connect to Asterisk Manager
    if (!checkAstMan()) {
        return false;
    }

    //check if the extension is within range for this user
    if (isset($extension) && !checkRange($extension)){
        echo "<script>javascript:alert('". __("Warning! Extension")." ".$extension." ".__("is not allowed for your account").".');</script>";
        $GLOBALS['abort'] = true;
    } else {
        //if submitting form, update database
        if (!isset($action)) $action = null;
        switch ($action) {
            case "add":
                if (core_users_add($_REQUEST)) {
                    // TODO: Check this if it's the same in device and user mode, and in fact we can't support this in that
                    //       mode at least without fixing the complexities of adding the devices which gets ugly!
                    //
                    $this_dest = core_getdest($_REQUEST['extension']);
                    fwmsg::set_dest($this_dest[0]);
                    needreload();
                    $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been added'));
                    $_SESSION['msgtype']='success';
                    $_SESSION['msgtstamp']=time();
                    redirect_standard_continue();
                } else {
                    // really bad hack - but if core_users_add fails, want to stop core_devices_add
                    // Comment, this does not help everywhere. Other hooks functions can hook before
                    // this like voicemail!
                    //
                    $GLOBALS['abort'] = true;
                }
            break;
            case "delete":
            case "del":
                core_users_del($extdisplay);
                core_users_cleanastdb($extdisplay);
                if (function_exists('findmefollow_del')) {
                    findmefollow_del($extdisplay);
                }
                $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been deleted'));
                $_SESSION['msgtype']='warning';
                $_SESSION['msgtstamp']=time();
                needreload();
                redirect_standard_continue();
            break;
            case "edit":
                if (core_users_edit($extdisplay,$_REQUEST)) {
                    needreload();
                    $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
                    $_SESSION['msgtype']='success';
                    $_SESSION['msgtstamp']=time();
                    redirect_standard_continue('extdisplay');
                } else {
                    // really bad hack - but if core_users_edit fails, want to stop core_devices_edit
                    $GLOBALS['abort'] = true;
                }
            break;
        }
    }
    return true;
}


function core_devices_configpageinit($dispnum) {
    global $currentcomponent, $amp_conf;

    $engineinfo = engine_getinfo();
    $astver =  $engineinfo['version'];
    $pjsip_enabled = version_compare($astver, '13.00.00', 'ge');
    $sip_deprecated = version_compare($astver, '22.00.00', 'ge');

    if ( $dispnum == 'devices' || $dispnum == 'extensions' ) {

        // We don't call: $currentcomponent->addgeneralarray('devtechs') because the first
        // call to addgeneralarrayitem will initiate the array and this allows other modules
        // to add a new device type.

        // Some errors for the validation bits
        $msgInvalidChannel = __("Please enter the channel for this device");
        $msgConfirmSecret = __("You have not entered a Secret for this device, although this is possible it is generally bad practice to not assign a Secret to a device. Are you sure you want to leave the Secret empty?");
        $msgInvalidSecret = __("Please enter a Secret for this device");

        $secret_validation = '(isEmpty() && !confirm("'.$msgConfirmSecret.'"))';
        if ($amp_conf['DEVICE_STRONG_SECRETS']) {
            $secret_validation .= ' || (!isEmpty() && weakSecret())';
        }

        // zap
        $tmparr = array();
        $tt = __("The Zap channel number for this port.");
        $tmparr['channel'] = array('value' => '', 'tt' => $tt, 'level' => 0, 'jsvalidation' => 'isEmpty()', 'failvalidationmsg' => $msgInvalidChannel);
        $tt = __("Asterisk context this device will send calls to. Only change this is you know what you are doing.");
        $tmparr['context'] = array('value' => 'from-internal', 'tt' => $tt, 'level' => 1);
        unset($select);
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'no', 'text' => __('No'));
        $tt = __("Zap immediate mode setting, see Zap documentation for details.");
        $tmparr['immediate'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);
        $tt = __("Zap signaling, usually fxo_ks when connected to an analog phone. Some special applications or channel bank connections may require fxs_ks or other valid settings. See Zap and card documentation for details.");
        $tmparr['signalling'] = array('value' => 'fxo_ks', 'tt' => $tt, 'level' => 1);
        $tt = __("Zap echocancel setting, see Zap documentation for details.");
        $tmparr['echocancel'] = array('value' => 'yes', 'tt' => $tt, 'level' => 1);
        unset($select);
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'no', 'text' => __('No'));
        $tt = __("Whether to turn on echo cancellation when bridging between Zap channels. See Zap documentation for details.");
        $tmparr['echocancelwhenbridged'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);
        $tt = __("Echo training requirements of this card. See Zap documentation for details.");
        $tmparr['echotraining'] = array('value' => '800', 'tt' => $tt, 'level' => 1);
        unset($select);
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'no', 'text' => __('No'));
        $tt = __("Experimental and un-reliable setting to try and detect a busy signal. See Zap documentation for details.");
        $tmparr['busydetect'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);
        $tt = __("Experimental and un-reliable setting to try and detect a busy signal, number of iterations to conclude busy. See Zap documentation for details.");
        $tmparr['busycount'] = array('value' => '7', 'tt' => $tt, 'level' => 1);
        unset($select);
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'no', 'text' => __('No'));
        $tt = __("Experimental and un-reliable setting to try and detect call progress tones. See Zap documentation for details.");
        $tmparr['callprogress'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);
        $tt = __("How to dial this device, this should not be changed unless you know what you are doing.");
        $tmparr['dial'] = array('value' => '', 'tt' => $tt, 'level' => 2);
        $tt = __("Accountcode for this device.");
        $tmparr['accountcode'] = array('value' => '', 'tt' => $tt, 'level' => 1);
        $tt = __("Callgroup(s) that this device is part of, can be one or more callgroups, e.g. '1,3-5' would be in groups 1,3,4,5.");
        $tmparr['callgroup'] = array('value' => $amp_conf['DEVICE_CALLGROUP'], 'tt' => $tt, 'level' => 1);
        $tt = __("Pickupgroups(s) that this device can pickup calls from, can be one or more groups, e.g. '1,3-5' would be in groups 1,3,4,5. Device does not have to be in a group to be able to pickup calls from that group.");
        $tmparr['pickupgroup'] = array('value' => $amp_conf['DEVICE_PICKUPGROUP'], 'tt' => $tt, 'level' => 1);
        $tt = __("Channel group that this device is part from, so you can dial the group via DAHDI/gX where X is the group number (Max 63)");
        $tmparr['group'] = array('value' => 63, 'tt' => $tt, 'level' => 1);
        $tt = __("Mailbox for this device. This should not be changed unless you know what you are doing.");
        $tmparr['mailbox'] = array('value' => '', 'tt' => $tt, 'level' => 2);
        $currentcomponent->addgeneralarrayitem('devtechs', 'zap', $tmparr);
        unset($tmparr);

        // dahdi
        $tmparr = array();
        $tt = __("The DAHDi channel number for this port.");
        $tmparr['channel'] = array('value' => '', 'tt' => $tt, 'level' => 0, 'jsvalidation' => 'isEmpty()', 'failvalidationmsg' => $msgInvalidChannel);
        $tt = __("Asterisk context this device will send calls to. Only change this is you know what you are doing.");
        $tmparr['context'] = array('value' => 'from-internal', 'tt' => $tt, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'no', 'text' => __('No'));
        $tt = __("DAHDi immediate mode setting, see DAHDi documentation for details.");
        $tmparr['immediate'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);

        $tt = __("DAHDi signalling, usually fxo_ks when connected to an analog phone. Some special applications or channel bank connections may require fxs_ks or other valid settings. See DAHDi and card documentation for details.");
        $tmparr['signalling'] = array('value' => 'fxo_ks', 'tt' => $tt, 'level' => 1);
        $tt = __("DAHDi echocancel setting, see DAHDi documentation for details.");
        $tmparr['echocancel'] = array('value' => 'yes', 'tt' => $tt, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'no', 'text' => __('No'));
        $tt = __("Whether to turn on echo cancellation when bridging between DAHDi channels. See DAHDi documentation for details.");
        $tmparr['echocancelwhenbridged'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);

        $tt = __("Echo training requirements of this card. See DAHDi documentation for details.");
        $tmparr['echotraining'] = array('value' => '800', 'tt' => $tt, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'no', 'text' => __('No'));
        $tt = __("Experimental and un-reliable setting to try and detect a busy signal. See DAHDi documentation for details.");
        $tmparr['busydetect'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);

        $tt = __("Experimental and un-reliable setting to try and detect a busy signal, number of iterations to conclude busy. See DAHDi documentation for details.");
        $tmparr['busycount'] = array('value' => '7', 'tt' => $tt, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'no', 'text' => __('No'));
        $tt = __("Experimental and un-reliable setting to try and detect call progress tones. See DAHDi documentation for details.");
        $tmparr['callprogress'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);

        $tt = __("How to dial this device, this should not be changed unless you know what you are doing.");
        $tmparr['dial'] = array('value' => '', 'level' => 2);
        $tt = __("Accountcode for this device.");
        $tmparr['accountcode'] = array('value' => '', 'tt' => $tt, 'level' => 1);
        $tt = __("Callgroup(s) that this device is part of, can be one or more callgroups, e.g. '1,3-5' would be in groups 1,3,4,5.");
        $tmparr['callgroup'] = array('value' => $amp_conf['DEVICE_CALLGROUP'], 'tt' => $tt, 'level' => 1);
        $tt = __("Pickupgroups(s) that this device can pickup calls from, can be one or more groups, e.g. '1,3-5' would be in groups 1,3,4,5. Device does not have to be in a group to be able to pickup calls from that group.");
        $tmparr['pickupgroup'] = array('value' => $amp_conf['DEVICE_PICKUPGROUP'], 'tt' => $tt, 'level' => 1);
        $tt = __("Channel group that this device is part from, so you can dial the group via DAHDI/gX where X is the group number (Max 63)");
        $tmparr['group'] = array('value' => 63, 'tt' => $tt, 'level' => 1);
        $tt = __("Mailbox for this device. This should not be changed unless you know what you are doing.");
        $tmparr['mailbox'] = array('value' => '', 'tt' => $tt, 'level' => 2);
        $currentcomponent->addgeneralarrayitem('devtechs', 'dahdi', $tmparr);
        unset($tmparr);

        // iax2
        $tmparr = array();
        $tt = __("Password (secret) configured for the device. Should be alphanumeric with at least 2 letters and numbers to keep secure.");
        $tmparr['secret'] = array('value' => '', 'tt' => $tt, 'level' => 0, 'jsvalidation' => $secret_validation, 'failvalidationmsg' => $msgInvalidSecret);

        unset($select);
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'no', 'text' => __('No'));
        $select[] = array('value' => 'mediaonly', 'text' => __('Media Only'));
        $tt = __("IAX transfer capabilities, see the Asterisk documentation for details.");
        $tmparr['transfer'] = array('value' => 'yes', 'tt' => $tt, 'select' => $select, 'level' => 1);

        $tt = __("Asterisk context this device will send calls to. Only change this is you know what you are doing.");
        $tmparr['context'] = array('value' => 'from-internal', 'tt' => $tt, 'level' => 1);
        $tt = __("Host settings for this device, almost always dynamic for endpoints.");
        $tmparr['host'] = array('value' => 'dynamic', 'tt' => $tt, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'friend', 'text' => 'friend');
        $select[] = array('value' => 'peer', 'text' => 'peer');
        $select[] = array('value' => 'user', 'text' => 'user');
        $tt = __("Asterisk connection type, usually friend for endpoints.");
        $tmparr['type'] = array('value' => 'friend', 'tt' => $tt, 'select' => $select, 'level' => 1);

        $tt = __("Endpoint port number to use, usually 4569.");
        $tmparr['port'] = array('value' => '4569', 'tt' => $tt, 'level' => 1);
        $tt = __("Setting to yes (equivalent to 2000 msec) will send an OPTIONS packet to the endpoint periodically (default every minute). Used to monitor the health of the endpoint. If delays are longer then the qualify time, the endpoint will be taken offline and considered unreachable. Can be set to a value which is the msec threshold. Setting to no will turn this off. Can also be helpful to keep NAT pinholes open.");
        $tmparr['qualify'] = array('value' => $amp_conf['DEVICE_QUALIFY'], 'tt' => $tt, 'level' => 1);
        $tt = __("Disallowed codecs. Set this to all to remove all codecs defined in the general settings and then specify specific codecs separated by '&' on the 'allow' setting, or just disallow specific codecs separated by '&'.");
        $tmparr['disallow'] = array('value' => $amp_conf['DEVICE_DISALLOW'], 'tt' => $tt, 'level' => 1);
        $tt = __("Allow specific codecs, separated by the '&' sign and in priority order. E.g. 'ulaw&g729'. Codecs allowed in the general settings will also be allowed unless removed with the 'disallow' directive.");
        $tmparr['allow'] = array('value' => $amp_conf['DEVICE_ALLOW'], 'tt' => $tt, 'level' => 1);
        $tt = __("How to dial this device, this should not be changed unless you know what you are doing.");
        $tmparr['dial'] = array('value' => '', 'tt' => $tt, 'level' => 2);
        $tt = __("Accountcode for this device.");
        $tmparr['accountcode'] = array('value' => '', 'tt' => $tt, 'level' => 1);
        $tt = __("Mailbox for this device. This should not be changed unless you know what you are doing.");
        $tmparr['mailbox'] = array('value' => '', 'tt' => $tt, 'level' => 2);
        $tt = __("IP Address range to deny access to, in the form of network/netmask.");
        $tmparr['deny'] = array('value' => '0.0.0.0/0.0.0.0', 'tt' => $tt, 'level' => 1);
        $tt = __("IP Address range to allow access to, in the form of network/netmask. This can be a very useful security option when dealing with remote extensions that are at a known location (such as a branch office) or within a known ISP range for some home office situations.");
        $tmparr['permit'] = array('value' => '0.0.0.0/0.0.0.0', 'tt' => $tt, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'no', 'text' => __('No'));
        $select[] = array('value' => 'auto', 'text' => __('Auto'));
        $tt = __("IAX security setting. See IAX documentation and device compatibility for details.");
        $tmparr['requirecalltoken'] = array('value' => 'yes', 'tt' => $tt, 'select' => $select, 'level' => 1);

        $currentcomponent->addgeneralarrayitem('devtechs', 'iax2', $tmparr);
        unset($tmparr);

        // sip
        $tt = __("Password (secret) configured for the device. Should be alphanumeric with at least 2 letters and numbers to keep secure.");
        $tmparr = array();
        $tmparr['secret'] = array('value' => '', 'tt' => $tt, 'level' => 0, 'jsvalidation' => $secret_validation, 'failvalidationmsg' => $msgInvalidSecret);


        unset($select);
        $select[] = array('value' => 'rfc2833', 'text' => __('RFC 2833'));
        $select[] = array('value' => 'inband', 'text' => __('In band audio'));
        $select[] = array('value' => 'auto', 'text' => __('Auto'));
        $select[] = array('value' => 'info', 'text' => __('SIP INFO (application/dtmf-relay'));
        $select[] = array('value' => 'shortinfo', 'text' => __('SIP INFO (application/dtmf)'));
        $tt = __("The DTMF signaling mode used by this device, usually rfc2833 for most phones.");
        $tmparr['dtmfmode'] = array('value' => 'rfc2833', 'tt' => $tt, 'select' => $select, 'level' => 0);
        // $amp_conf['DEVICE_SIP_CANREINVITE']
        // $amp_conf['DEVICE_SIP_TRUSTRPID']
        // $amp_conf['DEVICE_SIP_SENDRPID']
        // $amp_conf['DEVICE_SIP_NAT']
        // $amp_conf['DEVICE_SIP_ENCRYPTION']
        // $amp_conf['DEVICE_SIP_QUALIFYFREQ']
        // $amp_conf['DEVICE_QUALIFY']
        // $amp_conf['DEVICE_DISALLOW']
        // $amp_conf['DEVICE_ALLOW']
        // $amp_conf['DEVICE_CALLGROUP']
        // $amp_conf['DEVICE_PICKUPGROUP']

        unset($select);
        $tt = __("Re-Invite policy for this device, see Asterisk documentation for details.");
        $select[] = array('value' => 'no', 'text' => __('No'));
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'nonat', 'text' => 'nonat');
        $select[] = array('value' => 'update', 'text' => 'update');
        $tmparr['canreinvite'] = array('value' => $amp_conf['DEVICE_SIP_CANREINVITE'], 'tt' => $tt, 'select' => $select, 'level' => 1);

        $tt = __("Asterisk context this device will send calls to. Only change this is you know what you are doing.");
        $tmparr['context'] = array('value' => 'from-internal', 'tt' => $tt, 'level' => 1);

        $tt = __("Host settings for this device, almost always dynamic for endpoints.");
        $tmparr['host'] = array('value' => 'dynamic', 'tt' => $tt, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'no', 'text' => __('No'));
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $tt = __("Whether Asterisk should trust the RPID settings from this device. Usually should be yes for CONNECTEDLINE() functionality to work if supported by the endpoint.");
        $tmparr['trustrpid'] = array('value' => $amp_conf['DEVICE_SIP_TRUSTRPID'], 'tt' => $tt, 'select' => $select, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'no', 'text' => __('No'));
        $select[] = array('value' => 'yes', 'text' => __('Send Remote-Party-ID header'));

        if (version_compare($amp_conf['ASTVERSION'],'1.8','ge')) {
            $select[] = array('value' => 'pai', 'text' => __('Send P-Asserted-Identity header'));
        }
        $tt = __("Whether Asterisk should send RPID (or PAI) info to the device. Usually should be enabled to the settings used by your device for CONNECTEDLINE() functionality to work if supported by the endpoint.");
        $tmparr['sendrpid'] = array('value' => $amp_conf['DEVICE_SIP_SENDRPID'], 'tt' => $tt, 'select' => $select, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'friend', 'text' => 'friend');
        $select[] = array('value' => 'peer', 'text' => 'peer');
        $select[] = array('value' => 'user', 'text' => 'user');
        $tt = __("Asterisk connection type, usually friend for endpoints.");
        $tmparr['type'] = array('value' => 'friend', 'tt' => $tt, 'select' => $select, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'no', 'text' => __('No - RFC3581'));
        $select[] = array('value' => 'never', 'text' => __('never - no RFC3581'));
        $select[] = array('value' => 'route', 'text' => __('route - NAT no rport'));
        $tt = __("NAT setting, see Asterisk documentation for details. Yes usually works for both internal and external devices. Set to No if the device will always be internal.");
        $tmparr['nat'] = array('value' => $amp_conf['DEVICE_SIP_NAT'], 'tt' => $tt, 'select' => $select, 'level' => 0);

        $tt = __("Endpoint port number to use, usually 5060. Some 2 ports devices such as ATA may used 5061 for the second port.");
        $tmparr['port'] = array('value' => '5060', 'tt' => $tt, 'level' => 1);
        $tt = __("Setting to yes (equivalent to 2000 msec) will send an OPTIONS packet to the endpoint periodically (default every minute). Used to monitor the health of the endpoint. If delays are longer then the qualify time, the endpoint will be taken offline and considered unreachable. Can be set to a value which is the msec threshold. Setting to no will turn this off. Can also be helpful to keep NAT pinholes open.");
        $tmparr['qualify'] = array('value' => $amp_conf['DEVICE_QUALIFY'], 'tt' => $tt, 'level' => 1);
        if (version_compare($amp_conf['ASTVERSION'],'1.6','ge')) {
            $tt = __("Frequency in seconds to send qualify messages to the endpoint.");
            $tmparr['qualifyfreq'] = array('value' => $amp_conf['DEVICE_SIP_QUALIFYFREQ'], 'tt' => $tt, 'level' => 1);
        }
        if (version_compare($amp_conf['ASTVERSION'],'1.8','ge')) {
            unset($select);
            $select[] = array('value' => 'udp,tcp,tls', 'text' => __('All - UDP Primary'));
            $select[] = array('value' => 'tcp,udp,tls', 'text' => __('All - TCP Primary'));
            $select[] = array('value' => 'tls,udp,tcp', 'text' => __('All - TLS Primary'));
            if (version_compare($amp_conf['ASTVERSION'],'11','ge')) {
                $select[] = array('value' => 'wss,ws,udp,tcp,tls', 'text' => __('All - WS Primary'));
            }
            $select[] = array('value' => 'udp', 'text' => __('UDP Only'));
            $select[] = array('value' => 'tcp', 'text' => __('TCP Only'));
            $select[] = array('value' => 'tls', 'text' => __('TLS Only'));
            if (version_compare($amp_conf['ASTVERSION'],'11','ge')) {
                $select[] = array('value' => 'wss,ws', 'text' => __('WS Only'));
            }
            $tt = __("This sets the allowed transport settings for this device and the default (Primary) transport for outgoing. The default transport is only used for outbound messages until a registration takes place.  During the peer registration the transport type may change to another supported type if the peer requests so. In most common cases, this does not have to be changed as most devices register in conjunction with the host=dynamic setting. If you are using TCP and/or TLS you need to make sure the general SIP Settings are configured for the system to operate in those modes and for TLS, proper certificates have been generated and configured. If you are using websockets (such as WebRTC) then you must select an option that includes WS");
            $tmparr['transport'] = array('value' => 'udp', 'tt' => $tt, 'select' => $select, 'level' => 1);

            if (version_compare($amp_conf['ASTVERSION'],'11','ge')) {
                unset($select);
                $select[] = array('value' => 'no', 'text' => __('No'));
                $select[] = array('value' => 'yes', 'text' => __('Yes'));
                $tt = __("Whether to Enable AVPF. Defaults to no. The WebRTC standard has selected AVPF as the audio video profile to use for media streams. This is not the default profile in use by Asterisk. As a result the following must be enabled to use WebRTC");
                $tmparr['avpf'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);
                $tt = __("Force Asterisk to use avp");
                $tmparr['force_avp'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);
            }

            if (version_compare($amp_conf['ASTVERSION'],'11','ge')) {
                unset($select);
                $select[] = array('value' => 'no', 'text' => __('No'));
                $select[] = array('value' => 'yes', 'text' => __('Yes'));
                $tt = __("Whether to Enable ICE Support. Defaults to no. ICE (Interactive Connectivity Establishment) is a protocol for Network Address Translator(NAT) traversal for UDP-based multimedia sessions established with the offer/answer model. This option is commonly enabled in WebRTC setups");
                $tmparr['icesupport'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);
            }

            // 2015-12-16 Alex: fields for dtls support
            if (version_compare($amp_conf['ASTVERSION'],'11','ge')) {
                unset($select);
                $select[] = array('value' => 'no', 'text' => __('No'));
                $select[] = array('value' => 'yes', 'text' => __('Yes'));
                $tt = __("Whether to Enable DTLS for this peer. Defaults to no.");
                $tmparr['dtlsenable'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);

                unset($select);
                $select[] = array('value' => 'no', 'text' => __('No'));
                $select[] = array('value' => 'yes', 'text' => __('Yes'));
                $select[] = array('value' => 'fingerprint', 'text' => __('Fingerprint'));
                $tt = __("Whether to verify that the provided peer cerificate is valid. Defaults to no.");
                $tmparr['dtlsverify'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);

                unset($select);
                $select[] = array('value' => 'actpass', 'text' => __('Incoming and Outgoing'));
                $select[] = array('value' => 'active',  'text' => __('Outgoing only'));
                $select[] = array('value' => 'passive', 'text' => __('Incoming only'));
                $tt = __("Behavior on DTLS incoming and outgoing connections. Defaults to actpass.");
                $tmparr['dtlssetup'] = array('value' => 'actpass', 'tt' => $tt, 'select' => $select, 'level' => 1);

                $tt = __("Path to certificate file to present");
                $tmparr['dtlscertfile'] = array('value' => '', 'tt' => $tt, 'level' => 1);

                $tt = __("Path to private key for certificate file");
                $tmparr['dtlsprivatekey'] = array('value' => '', 'tt' => $tt, 'level' => 1);

            }

            if (version_compare($amp_conf['ASTVERSION'],'13','ge')) {
                unset($select);
                $select[] = array('value' => 'no', 'text' => __('No'));
                $select[] = array('value' => 'yes', 'text' => __('Yes'));
                $tt = __("Enable rtcp-mux for working with Chrome >= 57");
                $tmparr['rtcp_mux'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);
            }

            unset($select);
            $select[] = array('value' => 'no', 'text' => __('No'));
            $select[] = array('value' => 'yes', 'text' => __('Yes (SRTP only)'));
            $tt = __("Whether to offer SRTP encrypted media (and only SRTP encrypted media) on outgoing calls to a peer. Calls will fail with HANGUPCAUSE=58 if the peer does not support SRTP. Defaults to no.");
            $tmparr['encryption'] = array('value' => $amp_conf['DEVICE_SIP_ENCRYPTION'], 'tt' => $tt, 'select' => $select, 'level' => 1);
        }

        $tt = __("Callgroup(s) that this device is part of, can be one or more callgroups, e.g. '1,3-5' would be in groups 1,3,4,5.");
        $tmparr['callgroup'] = array('value' => $amp_conf['DEVICE_CALLGROUP'], 'tt' => $tt, 'level' => 1);
        $tt = __("Pickupgroups(s) that this device can pickup calls from, can be one or more groups, e.g. '1,3-5' would be in groups 1,3,4,5. Device does not have to be in a group to be able to pickup calls from that group.");
        $tmparr['pickupgroup'] = array('value' => $amp_conf['DEVICE_PICKUPGROUP'], 'tt' => $tt, 'level' => 1);
        $tt = __("Disallowed codecs. Set this to all to remove all codecs defined in the general settings and then specify specific codecs separated by '&' on the 'allow' setting, or just disallow specific codecs separated by '&'.");
        $tmparr['disallow'] = array('value' => $amp_conf['DEVICE_DISALLOW'], 'tt' => $tt, 'level' => 1);
        $tt = __("Allow specific codecs, separated by the '&' sign and in priority order. E.g. 'ulaw&g729'. Codecs allowed in the general settings will also be allowed unless removed with the 'disallow' directive.");
        $tmparr['allow'] = array('value' => $amp_conf['DEVICE_ALLOW'], 'tt' => $tt, 'level' => 1);
        $tt = __("How to dial this device, this should not be changed unless you know what you are doing.");
        $tmparr['dial'] = array('value' => '', 'tt' => $tt, 'level' => 2);
        $tt = __("Accountcode for this device.");
        $tmparr['accountcode'] = array('value' => '', 'tt' => $tt, 'level' => 1);
        $tt = __("Mailbox for this device. This should not be changed unless you know what you are doing.");
        $tmparr['mailbox'] = array('value' => '', 'tt' => $tt, 'level' => 2);
        $tt = __("Asterisk dialplan extension to reach voicemail for this device. Some devices use this to auto-program the voicemail button on the endpoint. If left blank, the default vmexten setting is automatically configured by the voicemail module. Only change this on devices that may have special needs.");
        $tmparr['vmexten'] = array('value' => '', 'tt' => $tt, 'level' => 1);
        $tt = __("IP Address range to deny access to, in the form of network/netmask.");
        $tmparr['deny'] = array('value' => '0.0.0.0/0.0.0.0', 'tt' => $tt, 'level' => 1);
        $tt = __("IP Address range to allow access to, in the form of network/netmask. This can be a very useful security option when dealing with remote extensions that are at a known location (such as a branch office) or within a known ISP range for some home office situations.");
        $tmparr['permit'] = array('value' => '0.0.0.0/0.0.0.0', 'tt' => $tt, 'level' => 1);
        $currentcomponent->addgeneralarrayitem('devtechs', 'sip', $tmparr);

        // start pjsip, reuse sip settings, remove not used, add new
        unset($tmparr['insecure']);
        unset($tmparr['canreinvite']);
        unset($tmparr['port']);
        unset($tmparr['qualify']);
        //unset($tmparr['dial']);
        unset($tmparr['dtlsenable']);
        unset($tmparr['dtlscertfile']);
        unset($tmparr['dtlsverify']);
        unset($tmparr['dtlssetup']);
        unset($tmparr['dtlsprivatekey']);
        unset($tmparr['icesupport']);
        unset($tmparr['encryption']);
        unset($tmparr['host']);
        unset($tmparr['nat']);
        unset($tmparr['type']);
        unset($tmparr['vmexten']);
        unset($tmparr['avpf']);
        unset($tmparr['force_avp']);

        if (version_compare($amp_conf['ASTVERSION'],'11','ge')) {
            unset($select);
            $select[] = array('value' => 'no', 'text' => __('No'));
            $select[] = array('value' => 'yes', 'text' => __('Yes'));
            $tt = __("Whether to Enable ICE Support. Defaults to no. ICE (Interactive Connectivity Establishment) is a protocol for Network Address Translator(NAT) traversal for UDP-based multimedia sessions established with the offer/answer model. This option is commonly enabled in WebRTC setups");
            $tmparr['ice_support'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);
        }
 
        if (version_compare($amp_conf['ASTVERSION'],'11','ge')) {
            unset($select);
            $select[] = array('value' => 'no', 'text' => __('No'));
            $select[] = array('value' => 'yes', 'text' => __('Yes'));
            $tt = __("Whether to Enable AVPF. Defaults to no. The WebRTC standard has selected AVPF as the audio video profile to use for media streams. This is not the default profile in use by Asterisk. As a result the following must be enabled to use WebRTC");
            $tmparr['use_avpf'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);
        }

 
        $tt = __("Path to certificate file to present");
        $tmparr['dtls_cert_file'] = array('value' => '', 'tt' => $tt, 'level' => 1);

        $tt = __("Path to private key for certificate file");
        $tmparr['dtls_private_key'] = array('value' => '', 'tt' => $tt, 'level' => 1);

        $tt = __("Path to certificate authority file to present");
        $tmparr['dtls_ca_file'] = array('value' => '', 'tt' => $tt, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'actpass', 'text' => __('Incoming and Outgoing'));
        $select[] = array('value' => 'active',  'text' => __('Outgoing only'));
        $select[] = array('value' => 'passive', 'text' => __('Incoming only'));
        $tt = __("Behavior on DTLS incoming and outgoing connections. Defaults to actpass.");
        $tmparr['dtls_setup'] = array('value' => 'actpass', 'tt' => $tt, 'select' => $select, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'no', 'text' => __('No'));
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'fingerprint', 'text' => __('Fingerprint'));
        $tt = __("Whether to verify that the provided peer cerificate is valid. Defaults to no.");
        $tmparr['dtls_verify'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'dtls', 'text' => __('dtls'));
        $select[] = array('value' => 'sdes', 'text' => __('sdes'));
        $select[] = array('value' => 'no', 'text' => __('No'));
        $tt = __("Media Encryption");
        $tmparr['media_encryption'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);

        $tt = __("Message Context");
        $tmparr['message_context'] = array('value' => '', 'tt' => $tt, 'level' => 1);

        $tt = __("Subscribe Context");
        $tmparr['subscribe_context'] = array('value' => '', 'tt' => $tt, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'no', 'text' => __('No'));
        $tt = __("Allow subscribe");
        $tmparr['allow_subscribe'] = array('value' => 'yes', 'tt' => $tt, 'select' => $select, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'on', 'text' => _('Yes'));
        $select[] = array('value' => 'off', 'text' => _('No'));
        $select[] = array('value' => 'attest', 'text' => _('Attest'));
        $select[] = array('value' => 'verify', 'text' => _('Verify'));
        $tt = __("Enable Stir Shaken for this endpoint");
        $tmparr['stir_shaken'] = array('value' => 'off', 'tt' => $tt, 'select' => $select, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'transport-udp', 'text' => __('UDP'));
        $select[] = array('value' => 'transport-tcp', 'text' => __('TCP'));
        $select[] = array('value' => 'transport-tls', 'text' => __('TLS'));
        $select[] = array('value' => 'transport-ws', 'text' => __('WS'));
        $select[] = array('value' => 'transport-wss', 'text' => __('WSS'));
        $tt = __("Transport setting: if phone is behind some kind of NAT, chose transport NAT");
        $tmparr['transport'] = array('value' => 'transport-udp', 'tt' => $tt, 'select' => $select, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'no', 'text' => __('No'));
        $select[] = array('value' => 'route', 'text' => __('Route'));
        $tt = __("NAT setting, see Asterisk documentation for details. Yes usually works for both internal and external devices. Set to No if the device will always be internal.");
        $tmparr['nat'] = array('value' => $amp_conf['DEVICE_SIP_NAT'], 'tt' => $tt, 'select' => $select, 'level' => 0);

        $tt = __("Accountcode for this device.");
        $tmparr['accountcode'] = array('value' => '', 'tt' => $tt, 'level' => 1);

        $tt = __("Maximum number of SIP devices that can register to this extension.");
        $tmparr['max_contacts'] = array('value' => '1', 'tt' => $tt, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'no', 'text' => __('No'));
        $tt = __("Allow a registration to succeed by displacing any existing contacts that now exceed the max_contacts count. Enable this when you have max_contacts value to be greater than one and rewrite_contact is set to yes. The removed contact is likely the old contact created by rewrite_contact that the device is refreshing.");
        $tmparr['remove_existing'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);

        $tt= __("Qualify timeout in fractional seconds (default: '3.0')");
        $tmparr['qualify_timeout'] = array('value' => '3.0', 'tt' => $tt, 'level' => 2);

        $tt= __("Authenticates a qualify request if needed");
        $tmparr['authenticate_qualify'] = array('value' => 'no', 'tt' => $tt, 'level' => 2);

        unset($select);
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'no', 'text' => __('No'));
        $tt = __("Determines whether media may flow directly between endpoints.");
        $tmparr['direct_media'] = array('value' => $amp_conf['DEVICE_SIP_CANREINVITE'], 'tt' => $tt, 'select' => $select, 'level' => 1);

        unset($select);
        $select[] = array('value' => 'yes', 'text' => __('Yes'));
        $select[] = array('value' => 'no', 'text' => __('No'));
        $tt = __("Determines whether res_pjsip will use the media transport received in the offer SDP in the corresponding answer SDP.");
        $tmparr['media_use_received_transport'] = array('value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);

        $tt = __("Outbound Proxy.");
        $tmparr['outbound_proxy'] = array('value' => '', 'tt' => $tt, 'level' => 1);

        $currentcomponent->addgeneralarrayitem('devtechs', 'pjsip', $tmparr);
        unset($tmparr);

        // custom
        $tmparr = array();
        $tt = __("How to dial this device. This will be device specific. For example, a custom device which is really a remote SIP URI might be configured such as SIP/joe@somedomain.com");
        $tmparr['dial'] = array('value' => '', 'tt' => $tt, 'level' => 0);
        $currentcomponent->addgeneralarrayitem('devtechs', 'custom', $tmparr);
        unset($tmparr);

        // Devices list
        if ($_SESSION["AMP_user"]->checkSection('999')) {
            if($pjsip_enabled) {
                $pjsip_second = false;
                $sql = "SELECT data FROM pjsipsettings WHERE keyword = 'bindport'";
                $pjsip_port = sql($sql,'getOne');
                if ($pjsip_port == '5060') {
                    $currentcomponent->addoptlistitem('devicelist', 'pjsip_generic', __("Generic PJSIP Device").__(" - Port:").$pjsip_port);
                } else { 
                    $pjsip_second = true;
                }
            }

	    if($sip_deprecated==false) {
                $sql = "SELECT data FROM sipsettings WHERE keyword = 'bindport'";
                $sip_port = sql($sql,'getOne');
                if ($sip_port == '') {
                    $sip_port = "5060";
                }
	        $currentcomponent->addoptlistitem('devicelist', 'sip_generic', __("Generic SIP Device").__(" - Port:").$sip_port);
	    }

            if($pjsip_enabled and $pjsip_second) {
                $currentcomponent->addoptlistitem('devicelist', 'pjsip_generic', __("Generic PJSIP Device").__(" - Port:").$pjsip_port);
            }

            if(isset($amp_conf['HTTPSCERTFILE'])) {
                if($amp_conf['HTTPSCERTFILE']<>'') {
                    if ($pjsip_port != '5060' && $sip_deprecated==false) {
                        $currentcomponent->addoptlistitem('devicelist', 'webrtc_generic', __("SIP WebRTC Device"));
                    }
                    if($pjsip_enabled and $sip_port != '5060') {
                        $currentcomponent->addoptlistitem('devicelist', 'webrtcpjsip_generic', __("PJSIP WebRTC Device"));
                    }
                }
            }

            $sql = "SELECT data FROM iaxsettings WHERE keyword = 'bindport'";
            $iax_port = sql($sql,'getOne');
            if ($iax_port == '') {
                $iax_port = "4569";
            }
            $currentcomponent->addoptlistitem('devicelist', 'iax2_generic', __("Generic IAX2 Device").__(" - Port:").$iax_port);
            $currentcomponent->addoptlistitem('devicelist', 'dahdi_generic', __("Generic DAHDi Device"));
            $currentcomponent->addoptlistitem('devicelist', 'custom_custom', __("Other (Custom) Device"));
        }
        if ( $dispnum != 'devices' ) {
            $currentcomponent->addoptlistitem('devicelist', 'virtual', __("None (virtual exten)"));
        }
        $currentcomponent->setoptlistopts('devicelist', 'sort', false);


        // Option lists used by the gui
        $currentcomponent->addoptlistitem('devicetypelist', 'fixed', __("Fixed"));
        $currentcomponent->addoptlistitem('devicetypelist', 'adhoc', __("Adhoc"));
        $currentcomponent->setoptlistopts('devicetypelist', 'sort', false);

        $currentcomponent->addoptlistitem('deviceuserlist', 'none', __("none"));
        $currentcomponent->addoptlistitem('deviceuserlist', 'new', __("New User"));
        $users = core_users_list();
        if (isset($users)) {
            foreach ($users as $auser) {
                $currentcomponent->addoptlistitem('deviceuserlist', $auser[0], $auser[0] . " (" . $auser[1] . ")");
            }
        }
        $currentcomponent->setoptlistopts('deviceuserlist', 'sort', false);

        // Add the 'proces' functions
        $currentcomponent->addguifunc('core_devices_configpageload');
        $currentcomponent->addprocessfunc('core_devices_configprocess');
    }
}

function core_devices_configpageload() {
    global $currentcomponent;
    global $amp_conf;

    $tech_hardware = isset($_REQUEST['tech_hardware'])?$_REQUEST['tech_hardware']:null;
    if ($tech_hardware == 'virtual') {
        return true;
    }

    // Init vars from $_REQUEST[]
    $display = isset($_REQUEST['display'])?$_REQUEST['display']:null;;
    $action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
    $extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;

    if ( $action == 'del' ) { // Deleted

        if ( $display != 'extensions' ) {
            $currentcomponent->addguielem('_top', new gui_subheading('del', $extdisplay.' '.__("deleted"), false));
        }

    } elseif ( $extdisplay == '' && $tech_hardware == '' ) { // Adding

        if ( $display != 'extensions') {
            $currentcomponent->addguielem('_top', new gui_pageheading('title', __("Add Device")), 0);
        } else {
            $currentcomponent->addguielem('_top', new gui_pageheading('title', __("Add an Extension")), 0);
        }
        $currentcomponent->addguielem('_top', new gui_label('instructions', __("Please select your Device below then click Submit")));
        $currentcomponent->addguielem('Device', new gui_selectbox('tech_hardware', $currentcomponent->getoptlist('devicelist'), '', __("Device"), '', false));

    } else {

        $deviceInfo = array();
        if ( $extdisplay ) { // Editing

            $deviceInfo = core_devices_get($extdisplay);

            if ( $display != 'extensions' ) {
                $currentcomponent->addguielem('_top', new gui_pageheading('title', __("Device").": $extdisplay", false), 0);

                /*
                $delURL = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&action=del';
                $tlabel = sprintf(__("Delete Device %s"),$extdisplay);
                $label = '<span><img width="16" height="16" border="0" title="'.$tlabel.'" alt="" src="images/telephone_delete.png"/>&nbsp;'.$tlabel.'</span>';
                $currentcomponent->addguielem('_top', new gui_link('del', $label, $delURL, true, false), 0);
                 */

                if ($deviceInfo['device_user'] != 'none') {
                    $editURL = $_SERVER['PHP_SELF'].'?display=users&skip=0&extdisplay='.$deviceInfo['user'];
                    $tlabel =  $deviceInfo['devicetype'] == 'adhoc' ? sprintf(__("Edit Default User: %s"),$deviceInfo['user']) : sprintf(__("Edit Fixed User: %s"),$deviceInfo['user']);
                    $label = '<span><img width="16" height="16" border="0" title="'.$tlabel.'" alt="" src="images/user_edit.png"/>&nbsp;'.$tlabel.'</span>';
                    $currentcomponent->addguielem('_top', new gui_link('edit_user', $label, $editURL, true, false), 0);
                }
            }
        } else {

            $tmparr = explode('_', $tech_hardware);
            $deviceInfo['tech'] = $tmparr[0];
            $deviceInfo['hardware'] = $tmparr[1];
            unset($tmparr);

            if ( $display != 'extensions' ) {
                $currentcomponent->addguielem('_top', new gui_pageheading('title', sprintf(__("Add %s Device"), strtoupper($deviceInfo['tech'])) ), 0);
            } else {
                $currentcomponent->addguielem('_top', new gui_pageheading('title', sprintf(__("Add %s Extension"), strtoupper($deviceInfo['tech'])) ), 0);
            }
        }

        // Ensure they exist before the extract
        $devinfo_description = $devinfo_emergency_cid = null;
        $devinfo_devicetype = $devinfo_user = $devinfo_hardware = null;
        $devinfo_tech = null;
        if ( is_array($deviceInfo) ) {
            extract($deviceInfo, EXTR_PREFIX_ALL, 'devinfo');
        }

        // Setup vars for use in the gui later on
        $fc_logon = featurecodes_getFeatureCode('core', 'userlogon');
        $fc_logoff = featurecodes_getFeatureCode('core', 'userlogoff');

        $msgInvalidDevID = __("Please enter a device id.");
        $msgInvalidDevDesc = __("Please enter a valid Description for this device");
        $msgInvalidEmergCID = __("Please enter a valid Emergency CID");
        $msgInvalidExtNum = __("Please enter a valid extension number.");

        // Actual gui
        //$currentcomponent->addguielem('_top', new gui_hidden('action', ($extdisplay ? 'edit' : 'add')));
        //$currentcomponent->addguielem('_top', new gui_hidden('extdisplay', $extdisplay));

        if ( $display != 'extensions' ) {
            $section = __("Device Info");
            if ( $extdisplay ) { // Editing
                $currentcomponent->addguielem($section, new gui_hidden('deviceid', $extdisplay));
            } else { // Adding
                $currentcomponent->addguielem($section, new gui_textbox('deviceid', $extdisplay, __("Device ID"), __("Give your device a unique integer ID.  The device will use this ID to authenticate to the system."), '!isInteger()', $msgInvalidDevID, false));
            }
            $currentcomponent->addguielem($section, new gui_textbox('description', $devinfo_description, __("Description"), __("The CallerID name for this device will be set to this description until it is logged into."), '!isAlphanumeric() || isWhitespace()', $msgInvalidDevDesc, false));
            $currentcomponent->addguielem($section, new gui_textbox('emergency_cid', $devinfo_emergency_cid, __("Emergency CID"), __("This CallerID will always be set when dialing out an Outbound Route flagged as Emergency.  The Emergency CID overrides all other CallerID settings."), '!isCallerID()', $msgInvalidEmergCID));
            $currentcomponent->addguielem($section, new gui_selectbox('devicetype', $currentcomponent->getoptlist('devicetypelist'), $devinfo_devicetype, __("Device Type"), __("Devices can be fixed or adhoc. Fixed devices are always associated to the same extension/user. Adhoc devices can be logged into and logged out of by users.").' '.$fc_logon.' '.__("logs into a device.").' '.$fc_logoff.' '.__("logs out of a device."), false));
            $currentcomponent->addguielem($section, new gui_selectbox('deviceuser', $currentcomponent->getoptlist('deviceuserlist'), $devinfo_user, __("Default User"), __("Fixed devices will always mapped to this user.  Adhoc devices will be mapped to this user by default.<br><br>If selecting 'New User', a new User Extension of the same Device ID will be set as the Default User."), false));
        } else {
            $section = __("Extension Options");
            $currentcomponent->addguielem($section, new gui_textbox('emergency_cid', $devinfo_emergency_cid, __("Emergency CID"), __("This CallerID will always be set when dialing out an Outbound Route flagged as Emergency.  The Emergency CID overrides all other CallerID settings."), '!isCallerID()', $msgInvalidEmergCID));
        }
        $currentcomponent->addguielem($section, new gui_hidden('tech', $devinfo_tech));
        $currentcomponent->addguielem($section, new gui_hidden('hardware', $devinfo_hardware));

        if ($devinfo_tech && $devinfo_tech != "virtual") {
            $section = __("Device Options");

            $device_uses = sprintf(__("This device uses %s technology."),$devinfo_tech).(strtoupper($devinfo_tech) == 'ZAP' && ast_with_dahdi()?" (".__("Via DAHDi compatibility mode").")":"");
            $currentcomponent->addguielem($section, new gui_label('techlabel', $device_uses, true, 'px-2 mb-3 has-background-info has-text-white' ),4);

            if($devinfo_tech=='webrtc') {
                $devopts = $currentcomponent->getgeneralarrayitem('devtechs', 'sip');

                $devopts['transport']['value']='wss,ws,udp,tcp,tls';
                $devopts['encryption']['value']='yes';
                $devopts['avpf']['value']='yes';
                $devopts['force_avp']['value']='yes';
                $devopts['icesupport']['value']='yes';
                $devopts['dtlsenable']['value']='yes';
                $devopts['dtlsverify']['value']='fingerprint';
                $devopts['dtlssetup']['value']='actpass';
                $devopts['rtcp_mux']['value']='yes';

                if(isset($amp_conf['HTTPSPRIVATEKEY'])) {
                   $privkey = ($amp_conf['HTTPSPRIVATEKEY']<>'')?$amp_conf['HTTPSPRIVATEKEY']:'/etc/asterisk/keys/asterisk.pem';
                } else {
                   $privkey = '/etc/asterisk/keys/asterisk.pem';
                }

                if(isset($amp_conf['HTTPSCERTFILE'])) {
                   $certfile = ($amp_conf['HTTPSCERTFILE']<>'')?$amp_conf['HTTPSCERTFILE']:'/etc/asterisk/keys/asterisk.pem';
                } else {
                   $certfile = '/etc/asterisk/keys/asterisk.pem';
                }

                $devopts['dtlscertfile']['value']=$certfile;
                $devopts['dtlsprivatekey']['value']=$privkey;

                $devinfo_tech='sip';
            
            } elseif($devinfo_tech=='webrtcpjsip') {
                $devopts = $currentcomponent->getgeneralarrayitem('devtechs', 'pjsip');
                $devopts['transport']['value']='transport-wss';
                $devopts['use_avpf']['value']='yes'; 
                $devopts['rtcp_mux']['value']='yes';
                $devopts['ice_support']['value']='yes';
                $devopts['media_encryption']['value']='dtls';
                $devopts['dtls_verify']['value']='fingerprint';
                $devopts['media_use_received_transport']['value']='yes';
                $devopts['disallow']['value']='all';
                $devopts['allow']['value']='ulaw,alaw,g722,gsm,vp9,vp8,h264,opus';
                $devopts['dtls_setup']['value']='actpass';
                if(isset($amp_conf['HTTPSPRIVATEKEY'])) {
                   $privkey = ($amp_conf['HTTPSPRIVATEKEY']<>'')?$amp_conf['HTTPSPRIVATEKEY']:'/etc/asterisk/keys/asterisk.pem';
                } else {
                   $privkey = '/etc/asterisk/keys/asterisk.pem';
                }

                if(isset($amp_conf['HTTPSCERTFILE'])) {
                   $certfile = ($amp_conf['HTTPSCERTFILE']<>'')?$amp_conf['HTTPSCERTFILE']:'/etc/asterisk/keys/asterisk.pem';
                } else {
                   $certfile = '/etc/asterisk/keys/asterisk.pem';
                }
                $devopts['dtls_cert_file']['value']=$certfile;
                $devopts['dtls_private_key']['value']=$privkey;
                $devinfo_tech='pjsip';

            } else {
                $devopts = $currentcomponent->getgeneralarrayitem('devtechs', $devinfo_tech);
            }

            if (is_array($devopts)) {
                foreach ($devopts as $devopt=>$devoptarr) {
                    $devopname = 'devinfo_'.$devopt;
                    $devoptcurrent = isset($$devopname) ? $$devopname : $devoptarr['value'];
                    $devoptjs = isset($devoptarr['jsvalidation']) ? $devoptarr['jsvalidation'] : '';
                    $devoptfailmsg = isset($devoptarr['failvalidationmsg']) ? $devoptarr['failvalidationmsg'] : '';

                    // We compare the existing secret against what might be in the put to detect changes when validating
                    if ($devopt == "secret") {
                        $currentcomponent->addguielem($section, new gui_hidden($devopname . "_origional", $devoptcurrent), 4);
                        if ($devoptcurrent == '' && empty($extdisplay)) {
                            $devoptcurrent = md5(uniqid());
                        }
                    }

                    if ( $devoptarr['level'] == 0 || $amp_conf['ALWAYS_SHOW_DEVICE_DETAILS'] && $devoptarr['level'] < 2 || $extdisplay != '') { // editing to show advanced as well
                        // Added optional selectbox to enable the unsupported misdn module
                        $tooltip = isset($devoptarr['tt']) ? $devoptarr['tt'] : '';
                        if (isset($devoptarr['select'])) {
                            $currentcomponent->addguielem($section, new gui_selectbox($devopname, $devoptarr['select'], $devoptcurrent, $devopt, $tooltip, $devoptjs, $devoptfailmsg), 4);
                        } else {
                            $currentcomponent->addguielem($section, new gui_textbox($devopname, $devoptcurrent, $devopt, $tooltip, $devoptjs, $devoptfailmsg), 4);
                        }
                    } else { // add so only basic
                        $currentcomponent->addguielem($section, new gui_hidden($devopname, $devoptcurrent), 4);
                    }
                }
            }
        }
    }
}

function core_devices_configprocess() {

    if ( !class_exists('agi_asteriskmanager') )
        include 'common/php-asmanager.php';

    //make sure we can connect to Asterisk Manager
    if (!checkAstMan()) {
        return false;
    }

    //create vars from the request
    $tech = $action = null;
    extract($_REQUEST);

    if ($tech == "virtual" || $action == "edit" && $tech == '') {
        return true;
    }
    $extension = isset($extension)?$extension:null;
    $deviceid = isset($deviceid)?$deviceid:null;
    $name = isset($name)?$name:null;
    $action = isset($action)?$action:null;

    // fixed users only in extensions mode
    if ( $display == 'extensions' ) {
        $devicetype = 'fixed';
        $deviceid = $deviceuser = $extension;
        $description = $name;
    }

    //if submitting form, update database
    switch ($action) {
        case "add":
        // really bad hack - but if core_users_add fails, want to stop core_devices_add

        if (!isset($GLOBALS['abort']) || $GLOBALS['abort'] !== true || !$_SESSION["AMP_user"]->checkSection('999')) {
            if (core_devices_add($deviceid,$tech,$devinfo_dial,$devicetype,$deviceuser,$description,$emergency_cid)) {
                needreload();
                if ($deviceuser != 'new') {
                    redirect_standard_continue();
                }
            }
        } else {
            // This is a bit messy, because by this time, other modules may have added the device but this tries to block
            // the user who does not have add permission from adding a new extension.
            //
            $GLOBALS['abort'] = true;
        }
        break;
        case "delete":
        case "del":
            core_devices_del($extdisplay);
            needreload();
            redirect_standard_continue();
        break;
        case "edit":  //just delete and re-add
            // really bad hack - but if core_users_edit fails, want to stop core_devices_edit
            if (!isset($GLOBALS['abort']) || $GLOBALS['abort'] !== true) {
                core_devices_del($extdisplay,true);
                core_devices_add($deviceid,$tech,$devinfo_dial,$devicetype,$deviceuser,$description,$emergency_cid,true);
                $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
                $_SESSION['msgtype']='success';
                $_SESSION['msgtstamp']=time();
                needreload();
                redirect_standard_continue('extdisplay');
            }
            break;
            case "resetall":  //form a url with this option to nuke the AMPUSER & DEVICE trees and start over.
                core_users2astdb();
                core_devices2astdb();
            break;
    }
    return true;
}
?>
