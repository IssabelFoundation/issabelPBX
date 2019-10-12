<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

class astinfo {
    var $astman;
       var $version;
    
    function astinfo(&$astman) {
        $this->astman =& $astman;
        $arr = engine_getinfo();
        $this->version = $arr['version'];
    }
    
    function get_channel_totals() {
        if (!$this->astman) {
            return array(
                'external_calls'=>0,
                'internal_calls'=>0,
                'total_calls'=>0,
                'total_channels'=>0,
            );
        }
        if (version_compare($this->version, "1.6", "ge")) {
            $response = $this->astman->send_request('Command',array('Command'=>"core show channels"));
        } else {
            $response = $this->astman->send_request('Command',array('Command'=>"show channels"));
        }
        $astout = explode("\n",$response['data']);
        
        $external_calls = 0;
        $internal_calls = 0;
        $total_calls    = 0;
        $total_channels = 0;
        
        foreach ($astout as $line) {
            if (preg_match('/s@macro-dialout/', $line)) {
                $external_calls++;
            } else if (preg_match('/s@macro-dial:/', $line)) {
                $internal_calls++;
            } else if (preg_match('/^(\d+) active channel/i', $line, $matches)) {
                $total_channels = $matches[1];
            } else if (preg_match('/^(\d+) active call/i', $line, $matches)) {
                $total_calls = $matches[1];
            }
        }
        return array(
            'external_calls'=>$external_calls,
            'internal_calls'=>$internal_calls,
            'total_calls'=>$total_calls,
            'total_channels'=>$total_channels,
        );
    }
    
    function get_connections($devices = false) {
        if (!$devices) {
            $devices = array();
        }
        
        $return = array(
            'sip_users_online' => 0,
            'sip_users_offline' => 0,
            'sip_users_total' => 0,
            'sip_trunks_online' => 0,
            'sip_trunks_offline' => 0,
            'sip_trunks_total' => 0,
            'sip_registrations_online' => 0,
            'sip_registrations_offline' => 0,
            'sip_registrations_total' => 0,
            
            'iax2_users_online' => 0,
            'iax2_users_offline' => 0,
            'iax2_users_total' => 0,
            'iax2_trunks_online' => 0,
            'iax2_trunks_offline' => 0,
            'iax2_trunks_total' => 0,
            'iax2_registrations_online' => 0,
            'iax2_registrations_offline' => 0,
            'iax2_registrations_total' => 0,

            //totals
            'users_online' => 0,
            'users_offline' => 0,
            'users_total' => 0,
            'trunks_online' => 0,
            'trunks_offline' => 0,
            'trunks_total' => 0,
            'registrations_online' => 0,
            'registrations_offline' => 0,
            'registrations_total' => 0,
        );

        if (!$this->astman) {
            return $return;
        }

        $response = $this->astman->send_request('Command',array('Command'=>"sip show peers"));
        $astout = explode("\n",$response['data']);    
        foreach ($astout as $line) {
            $line = trim(str_replace('//','/',$line),'/');
            if (preg_match('/^(([a-z0-9\-_]+)(\/([a-z0-9\-_]+))?)\s+(\([a-z]+\)|\d{1,3}(\.\d{1,3}){3})/i', $line, $matches)) {
                //matches: [2] = name, [4] = username, [5] = host, [6] = part of ip (if IP)

                // have an IP address listed, so its online
                $online = !empty($matches[6]); 

                if (!isset($devices[$matches[2]])) {
                    // this is a trunk
                    //TODO match trunk tech as well? 
                    $return['sip_trunks_'.($online?'online':'offline')]++;
                } else {
                    $return['sip_users_'.($online?'online':'offline')]++;
                }
            }
        }
        
        
        $response = $this->astman->send_request('Command',array('Command'=>"sip show registry"));
        $astout = explode("\n",$response['data']);
        $pos = false;
        foreach ($astout as $line) {
            if (trim($line) != '') {
                if ($pos===false) {
                    // find the position of "State" in the first line
                    $pos = strpos($line,"State");
                } else {
                    // subsequent lines, check if it says "Registered" at that position
                    if (substr($line,$pos,10) == "Registered") {
                        $return['sip_registrations_online']++;
                    } elseif (strlen($line) > $pos) {
                        $return['sip_registrations_offline']++;
                    }
                }
            }
        }

        $response = $this->astman->send_request('Command',array('Command'=>"iax2 show peers"));
        $astout = explode("\n",$response['data']);
        foreach ($astout as $line) {
            if (preg_match('/^(([a-z0-9\-_]+)(\/([a-z0-9\-_]+))?)\s+(\([a-z]+\)|\d{1,3}(\.\d{1,3}){3})/i', $line, $matches)) {
                //matches: [2] = name, [4] = username, [5] = host, [6] = part of ip (if IP)

                // have an IP address listed, so its online
                $online = !empty($matches[6]); 

                if (!isset($devices[$matches[2]])) {
                    // this is a trunk
                    //TODO match trunk tech as well? 
                    $return['iax2_trunks_'.($online?'online':'offline')]++;
                } else {
                    $return['iax2_users_'.($online?'online':'offline')]++;
                }
            }
        }
        
        
        $response = $this->astman->send_request('Command',array('Command'=>"iax2 show registry"));
        $astout = explode("\n",$response['data']);
        $pos = false;
        foreach ($astout as $line) {
            if (trim($line) != '') {
                if ($pos===false) {
                    // find the position of "State" in the first line
                    $pos = strpos($line,"State");
                } else {
                    // subsequent lines, check if it syas "Registered" at that position
                    if (substr($line,$pos,10) == "Registered") {
                        $return['iax2_registrations_online']++;
                    } elseif (strlen($line) > $pos) {
                        $return['iax2_registrations_offline']++;
                    }
                }
            }
        }

        $return['sip_users_total'] = $return['sip_users_online'] + $return['sip_users_offline'];
        $return['sip_trunks_total'] = $return['sip_trunks_online'] + $return['sip_trunks_offline'];
        $return['sip_registrations_total'] = $return['sip_registrations_online'] + $return['sip_registrations_offline'];

        $return['iax2_users_total'] = $return['iax2_users_online'] + $return['iax2_users_offline'];
        $return['iax2_trunks_total'] = $return['iax2_trunks_online'] + $return['iax2_trunks_offline'];
        $return['iax2_registrations_total'] = $return['iax2_registrations_online'] + $return['iax2_registrations_offline'];

        $return['users_online'] = $return['sip_users_online'] + $return['iax2_users_online'];
        $return['users_offline'] = $return['sip_users_offline'] + $return['iax2_users_offline'];
        $return['users_total'] = $return['users_online'] + $return['users_offline'];
        
        $return['trunks_online'] = $return['sip_trunks_online'] + $return['iax2_trunks_online'];
        $return['trunks_offline'] = $return['sip_trunks_offline'] + $return['iax2_trunks_offline'];
        $return['trunks_total'] = $return['trunks_online'] + $return['trunks_offline'];

        $return['registrations_online'] = $return['sip_registrations_online'] + $return['iax2_registrations_online'];
        $return['registrations_offline'] = $return['sip_registrations_offline'] + $return['iax2_registrations_offline'];
        $return['registrations_total'] = $return['registrations_online'] + $return['registrations_offline'];

        return $return;
    }
    
    function get_uptime() {
        /*
        System uptime: 1 week, 4 days, 22 hours, 29 minutes, 21 seconds
        Last reload: 1 week, 1 day, 6 hours, 14 minutes, 49 seconds
        */
        $output = array(
            'system' => '',
            'reload' => '',
        );

        if (!$this->astman) {
            return $output;
        }

        if (version_compare($this->version, "1.6", "ge")) {
            $response = $this->astman->send_request('Command',array('Command'=>"core show uptime"));
        } else {
            $response = $this->astman->send_request('Command',array('Command'=>"show uptime"));
        }
        $astout = explode("\n",$response['data']);


        // Only translate and do the preg_replace if in another language, since it is a somewhat expensive operation
        //
        if (!isset($_COOKIE['lang']) || $_COOKIE['lang'] == "en_US") {
            $translate = false;
        } else {
            $translate = true;
            $units = array(
                '/\bseconds\b/', '/\bsecond\b/',
                '/\bminutes\b/', '/\bminute\b/',
                '/\bhours\b/', '/\bhour\b/',
                '/\bdays\b/', '/\bday\b/',
                '/\bweeks\b/', '/\bweek\b/',
                '/\byears\b/', '/\byear\b/',
            );
            $tunits = array(
                _('seconds'), _('second'),
                _('minutes'), _('minute'),
                _('hours'), _('hour'),
                _('days'), _('day'),
                _('weeks'), _('week'),
                _('years'), _('year'),
            );
        }
        foreach ($astout as $line) {
            if (preg_match('/^System uptime: (.*)$/i',$line,$matches)) {
                $output["system"] = preg_replace('/,\s+(\d+ seconds?)?\s*$/', '', $matches[1]);                
                if ($translate) {
                    $output["system"] = preg_replace($units,$tunits,$output["system"]);
                }
            } else if (preg_match('/^Last reload: (.*)$/i',$line,$matches)) {
                $output["reload"] = preg_replace('/,\s+(\d+ seconds?)?\s*$/', '', $matches[1]);
                if ($translate) {
                    $output["reload"] = preg_replace($units,$tunits,$output["reload"]);
                }
            }
        }
        
        return $output;
    }
    
    function check_asterisk() {
        if (!$this->astman) {
            return false;
        }
        if (version_compare($this->version, "1.6", "ge")) {
            $response = $this->astman->send_request('Command',array('Command'=>"core show version"));
        } else {
            $response = $this->astman->send_request('Command',array('Command'=>"show version"));
        }
        $astout = explode("\n",$response['data']);
        if(count($astout)==1) { $astout[1]=$astout[0]; } // asterisk 16 has one line only
        if (!preg_match('/^Asterisk /i', $astout[1])) {
            return false;
        } else {
            return $astout[1];
        }
    }
}

?>
