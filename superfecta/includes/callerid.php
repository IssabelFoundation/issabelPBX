<?php

/**
 * @author Tony Shiffer, Jerry Swordsteel, jjacobs, patrick_elx
 * @version 3
 */
require_once(dirname(__FILE__) . "/config.php");
//Determine CLI or HTTP
if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
    $cli = true;
    /**
     * CLI Options:
     * d = Debug
     * s = Scheme
     * m = multifecta ID
     * r = Source (For Multifecta)
     * t = trunk info (from agi)
     * 
     * Trunk Info:
     * channel = Channel Call came in from
     * language = language of channel
     * type = SIP/IAX/DAHDI/ZAP
     * uniqueid = Asterisk Unique Value
     * callerid = CID From Asterisk
     * calleridname = CNAME From Asterisk
     * did = DID of Channel
     * context = context of channel
     */
    $shortopts = "d:s:m:r:t:";
    $options = getopt($shortopts);
    if (isset($options)) {
        //Add the "base_" to anything but ALL
        $scheme_name_request = ($options['s'] == 'ALL') ? 'ALL' : "base_" . $options['s'];
        //Debug goes to 0 if not set
        $debug = isset($options['d']) ? $options['d'] : 0;

        //AGI Trunk values
        $trunk_info_temp = isset($options['t']) ? unserialize(base64_decode($options['t'])) : array();
        if (!isset($options['m'])) {
            $trunk_info = array(
                'channel' => $trunk_info_temp['agi_channel'],
                'language' => $trunk_info_temp['agi_language'],
                'type' => $trunk_info_temp['agi_type'],
                'uniqueid' => $trunk_info_temp['agi_uniqueid'],
                'callerid' => $trunk_info_temp['agi_callerid'],
                'calleridname' => $trunk_info_temp['agi_calleridname'],
                'did' => $trunk_info_temp['agi_extension'],
                'context' => $trunk_info_temp['agi_context']
            );
        } else {
            $trunk_info = isset($options['t']) ? unserialize(base64_decode($options['t'])) : array();
        }
        
        //Multifecta only 
        $multifecta_id = isset($options['m']) ? $options['m'] : false;
        $source = isset($options['r']) ? $options['r'] : false;
    }
} else {
    $cli = false;
    if (isset($_REQUEST['scheme'])) {
        if ($_REQUEST['scheme'] == 'ALL') {
            $scheme_name_request = 'ALL';
        } else {
            $_REQUEST['scheme'] = str_replace('base_', '', $_REQUEST['scheme']);
            $scheme_name_request = "base_" . trim($_REQUEST['scheme']);
        }
    } else {
        $scheme_name_request = '';
    }
    $debug = isset($_REQUEST['debug']) ? $_REQUEST['debug'] : 0;

    $trunk_info = array(
        'channel' => 'NA',
        'language' => 'NA',
        'type' => 'NA',
        'uniqueid' => rand(),
        'callerid' => (isset($_REQUEST['thenumber'])) ? $_REQUEST['thenumber'] : '',
        'calleridname' => 'unknown',
        'did' => (isset($_REQUEST['thedid'])) ? $_REQUEST['thedid'] : '',
        'context' => 'from-superfecta'
    );
}

//Remove all invalid characters from number! (\D = Anything other than a digit)
$trunk_info['callerid'] = preg_replace('/\D/i', '', $trunk_info['callerid']);
$trunk_info['did'] = preg_replace('/\D/i', '', $trunk_info['did']);

//Die on Scheme unknown
if ((trim($scheme_name_request) == '') OR ($scheme_name_request == 'ALL')) {
    if ((!$cli) OR ($scheme_name_request == 'ALL')) {
        $sql = 'SELECT source, value FROM superfectaconfig WHERE field =  \'order\' and value+0 > 0 ORDER BY  abs(`superfectaconfig`.`value`) ASC';
        $data = $db->getAll($sql, array(), DB_FETCHMODE_ASSOC);
        foreach ($data as $list) {
            $scheme_name_array[$i] = $list['source'];
            $i++;
        }
    } else {
        die('No Scheme Assigned/Known!');
    }
} else {
    $scheme_name_array[0] = $scheme_name_request;
}

if (empty($trunk_info['callerid']) && !is_int($trunk_info['callerid'])) {
    if (!$cli) {
        die('Invalid Number');
    } else {
        echo base64_encode(serialize(array(
            'message' => 'Invalid Number'
        )));
    }
}
foreach ($scheme_name_array as $list) {
    $scheme_name = $list;

    //Get Scheme Params
    $param = array();
    $query = "SELECT * FROM superfectaconfig";

    $res = $db->getAll($query,DB_FETCHMODE_ASSOC);
    foreach($res as $idx=>$row) {
        $param[$row['source']][$row['field']] = $row['value'];
    }

    if (!array_key_exists($scheme_name, $param)) {
        die('Scheme Does not Exist!');
    }

    $scheme_param = $param[$scheme_name];

    require_once(dirname(__FILE__) . '/superfecta_base.php');

    $options = array(
        'db' => $db,
        'amp_conf' => $amp_conf,
        'astman' => $astman,
        'debug' => $debug,
        'scheme_name' => $scheme_name,
        'scheme_parameters' => $scheme_param,
        'path_location' => dirname(dirname(__FILE__)) . '/sources',
        'trunk_info' => $trunk_info
    );
	
    switch ($scheme_param['processor']) {
        case 'superfecta_multi.php':
            require_once(dirname(__FILE__) . '/processors/superfecta_multi.php');
            $options['multifecta_id'] = isset($multifecta_id) ? $multifecta_id : null;
            $options['source'] = isset($source) ? $source : null;
            $superfecta = NEW superfecta_multi($options);
            break;
        case 'superfecta_single.php':
        default:
            require_once(dirname(__FILE__) . '/processors/superfecta_single.php');
            $superfecta = NEW superfecta_single($options);
            break;
    }
    $superfecta->setCLI($cli);
    $superfecta->setDID($trunk_info['did']);

    //We only want to run all of this if it's a parent-multifecta or the original code (single-fecta), No need to run this for every child
    if (($superfecta->isDebug()) && ($superfecta->is_master())) {
        // If debugging, report all errors
        if ($superfecta->isDebug(3)) {
            error_reporting(E_ALL | E_NOTICE); //strict is way too much information! :-( 
        } else {
            error_reporting(E_ALL); // -1 was not letting me see the wood for the trees.
        }
        ini_set('display_errors', '1');
        $superfecta->outn("<strong>Debug is on and set at level " . $superfecta->getDebug() . "</strong>");
        $superfecta->outn("<strong>The Original Number: </strong>" . $trunk_info['callerid']);
        $superfecta->outn("<strong>The Scheme: </strong>" . $superfecta->scheme_name);
        $superfecta->outn("<strong>Scheme Type: </strong>" . $superfecta->type . "FECTA");
        $superfecta->outn("<strong>SPAM Destination: </strong>" . $scheme_param['spam_destination']);
        $superfecta->out("<strong>is CLI: </strong>");
        $superfecta->outn($cli ? 'true' : 'false');
        $start_time_whole = $superfecta->mctime_float();
        $end_time_whole = 0;
        $superfecta->outn("<strong>Debugging Enabled, will not stop after first result.</strong>");
        $superfecta->outn("<strong>Scheme Variables:</strong><pre>" . print_r($superfecta->scheme_param, TRUE) . "</pre>");
        $superfecta->outn("<strong>Trunk Variables:</strong><pre>" . print_r($trunk_info, TRUE) . "</pre>");
        $superfecta->debug_log[] = "[". time()."][0] Trunk Values:".print_r($trunk_info, TRUE);
    } elseif ($superfecta->is_master()) {
        $superfecta->debug_log[] = "[". time()."][0] Trunk Values:".print_r($trunk_info, TRUE);
    }
    
    //Strip +1 or +2 or etc....
    $trunk_info['callerid'] = preg_replace('/^\+[1-9]/', '', $trunk_info['callerid']);
    
    $superfecta->set_CurlTimeout($scheme_param['Curl_Timeout']);

    $run_this_scheme = true;

    //We only want to run all of this if it's a parent-multifecta or the original code (single-fecta), No need to run this for every child
    if ($superfecta->is_master()) {
        // Determine if this is the correct DID, if this scheme is limited to a DID.
        $rule_match = $superfecta->match_pattern_all((isset($scheme_param['DID'])) ? $scheme_param['DID'] : '', $trunk_info['did']);
        if ($rule_match['number']) {
            $superfecta->outn("Matched DID Rule: '" . $rule_match['pattern'] . "' with '" . $rule_match['number'] . "'");
        } elseif ($rule_match['status']) {
            $superfecta->outn("No matching DID rules.");
            $run_this_scheme = false;
        }

        // Determine if the CID matches any patterns defined for this scheme
        $rule_match = $superfecta->match_pattern_all((isset($scheme_param['CID_rules'])) ? $scheme_param['CID_rules'] : '', $trunk_info['callerid']);
        if ($rule_match['number'] && $run_this_scheme) {
            $superfecta->outn("Matched CID Rule: '" . $rule_match['pattern'] . "' with '" . $rule_match['number'] . "'");
            $trunk_info['callerid'] = $rule_match['number'];
        } elseif ($rule_match['status'] && $run_this_scheme) {
            $superfecta->outn("No matching CID rules.");
            $run_this_scheme = false;
        }

        //if a prefix lookup is enabled, look it up, and truncate the result to 10 characters
        ///Clean these up, set NULL values instead of blanks then don't check for ''
        $superfecta->set_Prefix('');
        if ((isset($scheme_param['Prefix_URL'])) && (trim($scheme_param['Prefix_URL']) != '')) {
            $start_time = $superfecta->mctime_float();

            $superfecta->set_Prefix($superfecta->get_url_contents(str_replace("[thenumber]", $trunk_info['callerid'], $scheme_param['Prefix_URL'])));

            $superfecta->outn("Prefix Url defined ...");
            if ($superfecta->prefix != '') {
                $superfecta->outn("returned: " . $superfecta->get_Prefix());
            } else {
                $superfecta->outn("result was empty");
            }
            $superfecta->outn("result <img src='images/scrollup.gif'> took " . number_format((mctime_float() - $start_time), 4) . " seconds.");
        }

        $superfecta->set_TrunkInfo($trunk_info);
        if ($run_this_scheme) {
            if (!$cli) {
                $callerid = $superfecta->web_debug();
            } else {
                $callerid = $superfecta->get_results();
            }

            if (!empty($callerid)) {
                //$first_caller_id = _utf8_decode($first_caller_id);
                $callerid = trim(strip_tags($callerid));
                if ($superfecta->isCharSetIA5()) {
                    $callerid = $superfecta->stripAccents($callerid);
                }
                //Why?
                $callerid = preg_replace("/[\";']/", "", $callerid);
                //limit caller id to the first 60 char
                $callerid = substr($callerid, 0, 60);
            }

            //Send the result to the post processors for each module
            $superfecta->send_results($callerid);

            //Set Spam text
            $spam_text = ($superfecta->isSpam()) ? $scheme_param['SPAM_Text'] : '';

            //Set Spam Destination
            $spam_dest = (!empty($scheme_param['spam_interceptor']) && ($scheme_param['spam_interceptor'] == 'Y')) ? $scheme_param['spam_destination'] : '';
            $spam_dest = ($superfecta->get_SpamCount() >= $scheme_param['SPAM_threshold']) ? $spam_dest : '';

            //Send out final data
            if (!$superfecta->isDebug()) {
                if ($cli) {
                    if ($callerid != '') {
                        $final_data['cid'] = $spam_text . " " . $superfecta->get_Prefix() . $callerid;
                        $final_data['destination'] = $spam_dest;
                        $final_data['success'] = TRUE;
                        echo base64_encode(serialize($final_data));
                        if(!empty($superfecta->debug_log)) {
                            file_put_contents(dirname(dirname(__FILE__))."/logs/debug-log-".time(), implode("\n", $superfecta->debug_log));
                        }
                        //This takes us out of the loop so that we don't get multiple returned results like: AndrewNAGY,ANDREWAndrewWIRELESS CALLER
                        break;
                    }
                } else {
                    //We are still web-bing it up, just don't want any crap to be shown. so lets only show the scheme
                    $superfecta->outn($scheme_name . ": " . $spam_text . " " . $superfecta->get_Prefix() . $callerid);
                }
            } else {
                if (!empty($spam_dest)) {
                    $superfecta->outn("<b>SPAM Call sent to:</b> " . $spam_dest);
                }
                $superfecta->out("<b>Returned Result would be: ");
                $callerid = utf8_encode($spam_text . " " . $superfecta->get_Prefix() . $callerid);
                $superfecta->outn($callerid);
                $end_time_whole = ($end_time_whole == 0) ? $superfecta->mctime_float() : $end_time_whole;
                $superfecta->outn("result <img src='images/scrollup.gif'> took " . number_format(($end_time_whole - $start_time_whole), 4) . " seconds.</b>");
                $superfecta->outn("<hr>");
            }
            
            //Debug log
            if(!empty($superfecta->debug_log)) {
				if(!file_exists(dirname(dirname(__FILE__))."/logs")) {
					mkdir(dirname(dirname(__FILE__))."/logs");
				}
                file_put_contents(dirname(dirname(__FILE__))."/logs/debug-log-".time(), implode("\n", $superfecta->debug_log));
            }
        }
    } elseif (($superfecta->type == 'MULTI') && ($superfecta->multi_type == 'CHILD')) {
        if (!$cli) {
            $callerid = $superfecta->web_debug();
        } else {
            $callerid = $superfecta->get_results();
        }
    }
	//cleanup
	if(file_exists(dirname(dirname(__FILE__))."/logs")) {
		$files = glob(dirname(dirname(__FILE__))."/logs/*"); // get all file names
		foreach($files as $file){ // iterate files
		  if(is_file($file))
		    unlink($file); // delete file
		}
	}
	if(file_exists(dirname(dirname(__FILE__)).'/log')) {
		unlink(dirname(dirname(__FILE__)).'/log');
	}
}

function FnDeprecated($fnName) {
    die("<strong>Error - </strong>Function <strong>{$fnName}</strong> is deprecated.");
}

function cisf_find_area($area_array, $full_number) {
    FnDeprecated(__FUNCTION__);
}

function cisf_url_encode_array($arr) {
    FnDeprecated(__FUNCTION__);
}

function get_url_contents($url, $post_data=false, $referrer=false, $cookie_file=false, $useragent=false) {
    FnDeprecated(__FUNCTION__);
}

function mctime_float() {
    FnDeprecated(__FUNCTION__);
}

function match_pattern_all($array, $number) {
    FnDeprecated(__FUNCTION__);
}

function match_pattern($pattern, $number) {
    FnDeprecated(__FUNCTION__);
}

function stripAccents($string) {
    FnDeprecated(__FUNCTION__);
}

function isutf8($string) {
    FnDeprecated(__FUNCTION__);
}

function _utf8_decode($string) {
    FnDeprecated(__FUNCTION__);
}
