<?php
/* $Id:$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

    global $amp_conf;
    $output = array();
    $fn = "http://myip.issabel.org:5060/whatismyip.php";

    $json_array['status'] = _('Failed to auto-detect settings');
    $json_array['externip'] = '';


        $ip_xml = file_get_contents_url($fn);

    preg_match('|^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$|',$ip_xml,$matches);
    if (isset($matches[1])) {
        $json_array['externip'] = $matches[1];
        $json_array['status'] = _('Failed to auto-detect local network settings');

        // TODO: Still find a better way to find patch to route command?
        //
        if (is_executable('/sbin/route')) {
            $routecmd = "/sbin/route -nv";
        } elseif (is_executable('/bin/route')) {
            $routecmd = "/bin/route -nv";
        } else {
            $routecmd = "route -nv";
        }
        exec($routecmd,$output,$retcode);
        foreach ($output as $line) {
            preg_match('/^\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/',$line,$matches);
            if (isset($matches[3]) && $matches[2] == '0.0.0.0' && substr($matches[1],0,4) != '169.') {
                $localnet[$matches[1]] = $matches[3];
                $json_array['status'] = 'success';
            }
        }
    } else {
        $json_array['status'] = _('Failed to auto-detect settings');
    }
    $json_array['localnet'] = $localnet;

    header("Content-type: application/json"); 
    echo json_encode($json_array);
