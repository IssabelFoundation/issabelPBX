<?php

defined('DEBUG_INFO') or define('DEBUG_INFO', 1);
defined('DEBUG_WARN') or define('DEBUG_WARN', 2);
defined('DEBUG_ALL') or define('DEBUG_ALL', 3);

class superfecta_base {

    protected $cli = FALSE;
    protected $DID = '';
    protected $spam = false;
    protected $debug = 0; // Default to OFF
    protected $trunk_info = array();
    protected $db; //The database
    protected $astman; //Asterisk Manager Object
    protected $amp_conf; //Amp Conf array
    protected $caller_id = '';
    protected $charsetIA5 = true;
    protected $first_caller_id = '';
    protected $prefix = '';
    protected $spam_text = '';
    protected $cache_found = false;
    protected $single_source = false;
    protected $winning_source = '';
    protected $usage_mode = 'get caller id';
    protected $src_array = array();
    protected $multifecta_id = false;
    protected $multifecta_parent_id = false;
    protected $curl_timeout = '3.5';
    protected $spam_count = 0;
    public $debug_log = array(); //Send all log information here

    function isCLI() {
        return $this->cli;
    }

    function isSpam() {
        return $this->spam;
    }

    function isDebug($level=DEBUG_INFO) {
        return ((intval($this->debug) >= intval($level)) ? true : false);
    }

    function getDebug() {
        return $this->debug;
    }

    function isCharSetIA5() {
        return $this->charsetIA5;
    }

    function isCacheFound() {
        return $this->cache_found;
    }

    function get_TrunkInfo() {
        return $this->trunk_info;
    }

    function get_CurlTimeout() {
        return $this->curl_timeout;
    }

    function get_Prefix() {
        return $this->prefix;
    }

    function get_AmpConf() {
        return $this->amp_conf;
    }

    function get_DB() {
        return $this->db;
    }

    function get_AsteriskManager() {
        return $this->astman;
    }

    function get_SpamCount() {
        return $this->spam_count;
    }

    function get_DebugLevel() {
        return intval($this->debug);
    }

    function get_DID() {
        return $this->DID;
    }

    function setCLI($bValue) {
        $this->cli = $bValue;
    }

    function setDID($bValue) {
        $this->DID = $bValue;
    }

    function setSpam($bValue) {
        $this->spam = $bValue;
    }

    function setDebug($nLevel) {
        $this->debug = ((intval($nLevel) > 0) ? intval($nLevel) : 0);
    }

    function set_TrunkInfo($sValue) {
        $this->trunk_info = $sValue;
    }

    function set_CurlTimeout($sValue) {
        $this->curl_timeout = $sValue;
    }

    function set_Prefix($sValue) {
        $this->prefix = $sValue;
    }

    function set_AmpConf($sValue) {
        $this->amp_conf = $sValue;
    }

    function set_DB($sValue) {
        $this->db = $sValue;
    }

    function set_AsteriskManager($sValue) {
        $this->astman = $sValue;
    }

    function set_CharSetIA5($sValue) {
        $this->charsetIA5 = $sValue;
    }

    function set_SpamCount($nValue) {
        $this->spam_count = $nValue;
    }

    function set_CacheFound($bValue) {
        $this->cache_found = $bValue;
    }

    //public $thenumber_orig = (isset($_REQUEST['thenumber'])) ? trim($_REQUEST['thenumber']) : '';
    //public $DID = (isset($_REQUEST['testdid'])) ? trim($_REQUEST['testdid']) : '';
    //public $scheme = (isset($_REQUEST['scheme'])) ? trim($_REQUEST['scheme']) : '';

    function post_processing($cache_found, $winning_source, $first_caller_id, $run_param, $thenumber) {
        return($thenumber);
    }

    function get_caller_id($thenumber, $run_param=array()) {
        $this->DebugPrint("Searching " . str_replace("_", " ", get_class($this)) . " ...");
        //Is this the best way to do this?
        $caller_id = NULL;
        return($caller_id);
    }

    function settings() {
        //Is this the best way to do this?
        $settings = array();
        return($settings);
    }

    function out($message) {
        if ($this->isDebug()) {
            if (!$this->cli) {
                echo $message;
            } else {
                echo strip_tags($message);
            }
            $this->flush_buffers();
        } else {
            $final_data['message'] = strip_tags($message);
            echo base64_encode(serialize($final_data)).',';
        }
    }

    function outn($message) {
        if ($this->isDebug()) {
            if (!$this->cli) {
                echo "{$message}<br/>";
            } else {
                echo strip_tags($message) . "\n";
            }
            $this->flush_buffers();
        } else {
            $final_data['message'] = strip_tags($message);
            echo base64_encode(serialize($final_data)).',';
        }
    }

    function in_array_recursive($needle, $haystack) {
        $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($needle));
        foreach ($it AS $element) {
            if (in_array($element, $haystack)) {
                return true;
            }
        }
        return false;
    }

    /**
      Search an array of area codes against phone number to find one that matches.
      Return an array with the area code, area name and remaining phone number
     */
    function cisf_find_area($area_array, $full_number) {
        $largest_match = 0;
        $match = false;
        foreach ($area_array as $area => $area_code) {
            $area_length = strlen($area_code);
            if ((substr($full_number, 0, $area_length) == $area_code) && ($area_length > $largest_match)) {
                $match = array(
                    'area' => $area,
                    'area_code' => $area_code,
                    'number' => substr($full_number, $area_length)
                );
                $largest_match = $area_length;
            }
        }
        return $match;
    }

    /**
      Encode an array for transmission in http request
     */
    function cisf_url_encode_array($arr) {
        $string = "";
        foreach ($arr as $key => $value) {
            $string .= $key . "=" . urlencode($value) . "&";
        }
        trim($string, "&");
        return $string;
    }

    /**
      Returns the content of a URL.
     */
    function get_url_contents($url, $post_data=false, $referrer=false, $cookie_file=false, $useragent=false) {
        $crl = curl_init();
        if (!$useragent) {
            // Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 ( .NET CLR 3.5.30729)
            $useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";
        }
        if ($referrer) {
            curl_setopt($crl, CURLOPT_REFERER, $referrer);
        }
        curl_setopt($crl, CURLOPT_USERAGENT, $useragent);
        curl_setopt($crl, CURLOPT_URL, $url);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $this->curl_timeout);
        curl_setopt($crl, CURLOPT_FAILONERROR, true);
        curl_setopt($crl, CURLOPT_TIMEOUT, $this->curl_timeout);
        if ($cookie_file) {
            curl_setopt($crl, CURLOPT_COOKIEJAR, $cookie_file);
            curl_setopt($crl, CURLOPT_COOKIEFILE, $cookie_file);
        }
        if ($post_data) {
            curl_setopt($crl, CURLOPT_POST, 1); // set POST method
            curl_setopt($crl, CURLOPT_POSTFIELDS, $this->cisf_url_encode_array($post_data)); // add POST fields
        }

        $ret = trim(curl_exec($crl));
        if (curl_error($crl)) {
            $this->DebugPrint(" " . curl_error($crl) . " ");
        }

        //if debug is turned on, return the error number if the page fails.
        if ($ret === false) {
            $ret = '';
        }
        //something in curl is causing a return of "1" if the page being called is valid, but completely empty.
        //to get rid of this, I'm doing a nasty hack of just killing results of "1".
        if ($ret == '1') {
            $ret = '';
        }
        curl_close($crl);
        $this->DebugPrint("Orignal Raw Returned Data: </br><textarea>".$ret."</textarea></br>",DEBUG_ALL);
        return $ret;
    }

    function mctime_float() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    /**
      Match a phone number against an array of patterns
      return array containing
      'pattern' = the pattern that matched
      'number' = the number that matched, after applying rules
      'status' = true if a valid array was supplied, false if not

     */
    function match_pattern_all($array, $number) {

        // If we did not get an array, it's probably a list. Convert it to an array.
        if (!is_array($array)) {
            $array = explode("\n", trim($array));
        }

        $match = false;
        $pattern = false;

        // Search for a match
        foreach ($array as $pattern) {
            // Strip off any leading underscore
            $pattern = (substr($pattern, 0, 1) == "_") ? trim(substr($pattern, 1)) : trim($pattern);
            if ($match = $this->match_pattern($pattern, $number)) {
                break;
            } elseif ($pattern == $number) {
                $match = $number;
                break;
            }
        }

        // Return an array with our results
        return array(
            'pattern' => $pattern,
            'number' => $match,
            'status' => (isset($array[0]) && (strlen($array[0]) > 0))
        );
    }

    /**
      Parses Asterisk dial patterns and produces a resulting number if the match is successful or false if there is no match.
     */
    function match_pattern($pattern, $number) {
        $pattern = trim($pattern);
        $p_array = str_split($pattern);
        $tmp = "";
        $expression = "";
        $new_number = false;
        $remove = NULL;
        $insert = "";
        $error = false;
        $wildcard = false;
        $match = $pattern ? true : false;
        $regx_num = "/^\[[0-9]+(\-*[0-9])[0-9]*\]/i";
        $regx_alp = "/^\[[a-z]+(\-*[a-z])[a-z]*\]/i";

        // Try to build a Regular Expression from the dial pattern
        $i = 0;
        while (($i < strlen($pattern)) && (!$error) && ($pattern)) {
            switch (strtolower($p_array[$i])) {
                case 'x':
                    // Match any number between 0 and 9
                    $expression .= $tmp . "[0-9]";
                    $tmp = "";
                    break;
                case 'z':
                    // Match any number between 1 and 9
                    $expression .= $tmp . "[1-9]";
                    $tmp = "";
                    break;
                case 'n':
                    // Match any number between 2 and 9
                    $expression .= $tmp . "[2-9]";
                    $tmp = "";
                    break;
                case '[':
                    // Find out if what's between the brackets is a valid expression.
                    // If so, add it to the regular expression.
                    if (preg_match($regx_num, substr($pattern, $i), $matches)
                            || preg_match($regx_alp, substr(strtolower($pattern), $i), $matches)) {
                        $expression .= $tmp . "" . $matches[0];
                        $i = $i + (strlen($matches[0]) - 1);
                        $tmp = "";
                    } else {
                        $error = "Invalid character class";
                    }
                    break;
                case '.':
					// Match one or more occurrences of any number
					if(!$wildcard){
						$wildcard = true;
						$expression .= $tmp."[0-9]+";
						$tmp = "";
					}else{
						$error = "Cannot have more than one wildcard";
					}
                break;
                case '!':
                    // zero or more occurrences of any number
                    if (!$wildcard) {
                        $wildcard = true;
                        $expression .= $tmp . "[0-9]*";
                        $tmp = "";
                    } else {
                        $error = "Cannot have more than one wildcard";
                    }
                    break;
                case '+':
                    // Prepend any numbers before the '+' to the final match
                    // Store the numbers that will be prepended for later use
                    if (!$wildcard) {
                        if ($insert) {
                            $error = "Cannot have more than one '+'";
                        } elseif ($expression) {
                            $error = "Cannot use '+' after X,Z,N or []'s";
                        } else {
                            $insert = $tmp;
                            $tmp = "";
                        }
                    } else {
                        $error = "Cannot have '+' after wildcard";
                    }
                    break;
                case '|':
                    // Any numbers/expression before the '|' will be stripped

                    if (!$wildcard) {
                        if ($remove) {
                            $error = "Cannot have more than one '|'";
                        } else {
                            // Move any existing expression to the "remove" expression
                            $remove = $tmp . "" . $expression;
                            $tmp = "";
                            $expression = "";
                        }
                    } else {
                        $error = "Cannot have '|' after wildcard";
                    }
                    break;
                default:
                    // If it's not any of the above, is it a number betwen 0 and 9?
                    // If so, store in a temp buffer.  Depending on what comes next
                    // we may use in in an expression, or a prefix, or a removal expression
                    if (preg_match("/[0-9]/i", strtoupper($p_array[$i]))) {
                        $tmp .= strtoupper($p_array[$i]);
                    } else {
                        $error = "Invalid character '" . $p_array[$i] . "' in pattern";
                    }
            }
            $i++;
        }
        $expression .= $tmp;
        $tmp = "";
        if ($error) {
            // If we had any error, report them
            $match = false;
            $this->DebugPrint($error . " - position $i<br>\n");
        } else {
            // Else try out the regular expressions we built
            if (isset($remove)) {
                // If we had a removal expression, se if it works
                if (preg_match("/^" . $remove . "/i", $number, $matches)) {
                    $number = substr($number, strlen($matches[0]));
                } else {
                    $match = false;
                }
            }
            // Check the expression for the rest of the number
            if (preg_match("/^" . $expression . "$/i", $number, $matches)) {
                $new_number = $matches[0];
            } else {
                $match = false;
            }
            // If there was a prefix defined, add it.
            $new_number = $insert . "" . $new_number;
        }
        if (!$match) {
            // If our match failed, return false
            $new_number = false;
        }
        return $new_number;
    }

    function stripAccents($string) {
        $string = html_entity_decode($string);
        $string = strtr($string, "äåéöúûü•µ¿¡¬√ƒ≈∆«»… ÀÃÕŒœ–—“”‘’÷ÿŸ⁄€‹›ﬂ‡·‚„‰ÂÊÁËÈÍÎÏÌÓÔÒÚÛÙıˆ¯˘˙˚¸˝ˇ", "SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy");
        $string = str_replace(chr(160), ' ', $string);
        return $string;
    }

    function isutf8($string) {
        if (!function_exists('mb_detect_encoding')) {
            return false;
        } else {
            return (mb_detect_encoding($string . "e") == "UTF-8"); // added a character to the string to avoid the mb detect bug
        }
    }

    function _utf8_decode($string) {
        $string = html_entity_decode($string);
        $tmp = $string;
        $count = 0;
        while ($this->isutf8($tmp)) {
            $tmp = utf8_decode($tmp);
            $count++;
        }

        for ($i = 0; $i < $count - 1; $i++) {
            $string = utf8_decode($string);
        }
        return $string;
    }

    function IsValidNumber($country, $thenumber, &$rPart1=null, &$rPart2=null, &$rPart3=null) {
        $number_error = false;
        $thenumber = preg_replace('/[^0-9^\.]/', "", $thenumber); // strip non-digits 
        // If we did not get an array, it's probably a list. Convert it to an array.
        if ((!is_array($country)) && strpos($country, ",")) {
            $country = array_map('trim', explode(",", $country));
        }

        if (is_array($country)) {
            // loop through each country in the array.
            foreach ($country as $region) {
                if ($this->IsValidNumber($region, $thenumber, $rPart1, $rPart2, $rPart3)) {
                    return true;
                }
            }
            return false;
        }

        // Process the Country passed as variable.
        switch ($country) {
            case "US" :
            case "CA" :
            case "DO" : {
                    //check for the correct 11 digits in US/CAN phone numbers in international format.
                    // country code + number
                    if (strlen($thenumber) == 11) {
                        if (substr($thenumber, 0, 1) == 1) {
                            $thenumber = substr($thenumber, 1);
                        } else {
                            return false;
                        }
                    }
                    // international dialing prefix + country code + number
                    if (strlen($thenumber) > 11) {
                        if (substr($thenumber, 0, 3) == '001') {
                            $thenumber = substr($thenumber, 3);
                        } else {
                            if (substr($thenumber, 0, 4) == '0111') {
                                $thenumber = substr($thenumber, 4);
                            } else {
                                return false;
                            }
                        }
                    }

                    // number
                    if (strlen($thenumber) < 10) {
                        return false;
                    }

                    $validnpaUS = false;
                    $validnpaCAN = false;
                    $validnpaDO = false;

                    $TFnpa = false;

                    $npa = substr($thenumber, 0, 3);
                    $nxx = substr($thenumber, 3, 3);
                    $station = substr($thenumber, 6, 4);

                    // Check for Toll-Free numbers
                    if ($npa == '800' || $npa == '866' || $npa == '877' || $npa == '888') {
                        $TFnpa = true;
                    }

                    if ($country == 'US' && !$number_error) {
                        // Check for valid US NPA
                        $npalistUS = array(
                            "201", "202", "203", "205", "206", "207", "208", "209", "210", "212",
                            "213", "214", "215", "216", "217", "218", "219", "224", "225", "228",
                            "229", "231", "234", "239", "240", "242", "246", "248", "251", "252",
                            "253", "254", "256", "260", "262", "264", "267", "268", "269", "270",
                            "276", "281", "284", "301", "302", "303", "304", "305", "307", "308",
                            "309", "310", "312", "313", "314", "315", "316", "317", "318", "319",
                            "320", "321", "323", "325", "330", "331", "334", "336", "337", "339",
                            "340", "345", "347", "351", "352", "360", "361", "386", "401", "402",
                            "404", "405", "406", "407", "408", "409", "410", "412", "413", "414",
                            "415", "417", "419", "423", "424", "425", "430", "432", "434", "435",
                            "440", "441", "443", "456", "469", "473", "478", "479", "480", "484",
                            "500", "501", "502", "503", "504", "505", "507", "508", "509", "510",
                            "512", "513", "515", "516", "517", "518", "520", "530", "540", "541",
                            "551", "559", "561", "562", "563", "567", "570", "571", "573", "574",
                            "575", "580", "585", "586", "600", "601", "602", "603", "605", "606",
                            "607", "608", "609", "610", "612", "614", "615", "616", "617", "618",
                            "619", "620", "623", "626", "630", "631", "636", "641", "646", "649",
                            "650", "651", "660", "661", "662", "664", "670", "671", "678", "682",
                            "684", "700", "701", "702", "703", "704", "706", "707", "708", "710",
                            "712", "713", "714", "715", "716", "717", "718", "719", "720", "724",
                            "727", "731", "732", "734", "740", "754", "757", "758", "760", "762",
                            "763", "765", "767", "769", "770", "772", "773", "774", "775", "779",
                            "781", "784", "785", "786", "787", "801", "802", "803", "804", "805",
                            "806", "808", "809", "810", "812", "813", "814", "815", "816", "817",
                            "818", "828", "829", "830", "831", "832", "843", "845", "847", "848",
                            "850", "856", "857", "858", "859", "860", "862", "863", "864", "865",
                            "868", "869", "870", "876", "878", "900", "901", "903", "904", "906",
                            "907", "908", "909", "910", "912", "913", "914", "915", "916", "917",
                            "918", "919", "920", "925", "928", "931", "936", "937", "939", "940",
                            "941", "947", "949", "951", "952", "954", "956", "970", "971", "972",
                            "973", "978", "979", "980", "985", "989",
                            "800", "866", "877", "888"
                        );

                        $validnpaUS = in_array($npa, $npalistUS);
                    }

                    if ($country == 'CA' && !$number_error) {
                        // Check for valid CAN NPA
                        $npalistCAN = array(
                            "204", "226", "249", "250", "289", "306", "343", "365", "403", "416", "418", "438", "450",
                            "506", "514", "519", "581", "587", "579", "604", "613", "647", "705", "709",
                            "778", "780", "807", "819", "867", "873", "902", "905",
                            "800", "866", "877", "888"
                        );

                        $validnpaCAN = in_array($npa, $npalistCAN);
                    }

                    if ($country == 'DO' && !$number_error) {
                        // Check for valid DO NPA
                        $validnpaDO = in_array($npa, array("809", "829", "849"));
                    }

                    if (!$TFnpa && ((!$validnpaUS) && (!$validnpaCAN) && (!$validnpaDO))) {
                        return false;
                    }

                    // Set the NPA, NXX & Station if passed by reference.
                    if (isset($rPart1)) {
                        $rPart1 = $npa;
                    }
                    if (isset($rPart2)) {
                        $rPart2 = $nxx;
                    }
                    if (isset($rPart3)) {
                        $rPart3 = $station;
                    }
                } // end US/CA/DO
                break;

            case "UK" : {
                    //check for the correct 8 ~ 13 digits in UK phone numbers. leading digits before the 44 international code will be ignored.
                    // check international format
                    if (strlen($thenumber) > 10) {
                        if (substr($thenumber, -11, 2) == 44) {
                            $thenumber = substr($thenumber, -11);
                        } else {
                            if (strlen($thenumber) > 11) {
                                if (substr($thenumber, -12, 2) == 44) {
                                    $thenumber = substr($thenumber, -12);
                                } else {
                                    return false;
                                }
                            }
                        }
                    }

                    //check for 11 digits national format.
                    if (strlen($thenumber) == 11) {
                        if (substr($thenumber, -11, 1) == 0) {
                            $number_error = false;
                        }
                    }

                    if (strlen($thenumber) < 8) {
                        return false;
                    }

                    if (!$number_error) {
                        // Initialise $validSTD and $validNGN
                        $validSTD = false;
                        $validNGN = false;

                        // Convert 441xxx to 01xxx if delivered in International Format
                        $thenumber = (substr($thenumber, 0, 2) == 44) ? "0" . substr($thenumber, 2) : $thenumber;
                        $prefix2 = substr($thenumber, 0, 5);

                        if ($prefix2 < 3000) {
                            // Check for valid UK STD
                            $STD = array(
                                "01130", "01131", "01132", "01133", "01140", "01141", "01142", "01143", "01150", "01151",
                                "01158", "01159", "01160", "01161", "01162", "01163", "01170", "01171", "01173", "01179",
                                "01180", "01181", "01183", "01189", "01200", "01202", "01204", "01205", "01206", "01207",
                                "01208", "01209", "01210", "01211", "01212", "01213", "01214", "01215", "01216", "01217",
                                "01218", "01219", "01223", "01224", "01225", "01226", "01228", "01229", "01233", "01234",
                                "01235", "01236", "01237", "01239", "01241", "01242", "01243", "01244", "01245", "01246",
                                "01248", "01249", "01250", "01252", "01253", "01254", "01255", "01256", "01257", "01258",
                                "01259", "01260", "01261", "01262", "01263", "01264", "01267", "01268", "01269", "01270",
                                "01271", "01273", "01274", "01275", "01276", "01277", "01278", "01279", "01280", "01282",
                                "01283", "01284", "01285", "01286", "01287", "01288", "01289", "01290", "01291", "01292",
                                "01293", "01294", "01295", "01296", "01297", "01298", "01299", "01300", "01301", "01302",
                                "01303", "01304", "01305", "01306", "01307", "01308", "01309", "01310", "01311", "01312",
                                "01313", "01314", "01315", "01316", "01317", "01318", "01320", "01322", "01323", "01324",
                                "01325", "01326", "01327", "01328", "01329", "01330", "01332", "01333", "01334", "01335",
                                "01337", "01339", "01340", "01341", "01342", "01343", "01344", "01346", "01347", "01348",
                                "01349", "01350", "01352", "01353", "01354", "01355", "01356", "01357", "01358", "01359",
                                "01360", "01361", "01362", "01363", "01364", "01366", "01367", "01368", "01369", "01371",
                                "01372", "01373", "01375", "01376", "01377", "01379", "01380", "01381", "01382", "01383",
                                "01384", "01386", "01387", "01388", "01389", "01392", "01394", "01395", "01397", "01398",
                                "01403", "01404", "01405", "01406", "01407", "01408", "01409", "01410", "01411", "01412",
                                "01413", "01414", "01415", "01416", "01417", "01418", "01419", "01420", "01422", "01423",
                                "01424", "01425", "01427", "01428", "01429", "01430", "01431", "01432", "01433", "01434",
                                "01435", "01436", "01437", "01438", "01439", "01440", "01442", "01443", "01444", "01445",
                                "01446", "01449", "01450", "01451", "01452", "01453", "01454", "01455", "01456", "01457",
                                "01458", "01460", "01461", "01462", "01463", "01464", "01465", "01466", "01467", "01469",
                                "01470", "01471", "01472", "01473", "01474", "01475", "01476", "01477", "01478", "01479",
                                "01480", "01481", "01482", "01483", "01484", "01485", "01487", "01488", "01489", "01490",
                                "01491", "01492", "01493", "01494", "01495", "01496", "01497", "01499", "01501", "01502",
                                "01503", "01505", "01506", "01507", "01508", "01509", "01510", "01511", "01512", "01513",
                                "01514", "01515", "01516", "01517", "01518", "01519", "01520", "01522", "01524", "01525",
                                "01526", "01527", "01528", "01529", "01530", "01531", "01534", "01535", "01536", "01538",
                                "01539", "01540", "01542", "01543", "01544", "01545", "01546", "01547", "01548", "01549",
                                "01550", "01551", "01553", "01554", "01555", "01556", "01557", "01558", "01559", "01560",
                                "01561", "01562", "01563", "01564", "01565", "01566", "01567", "01568", "01569", "01570",
                                "01571", "01572", "01573", "01575", "01576", "01577", "01578", "01579", "01580", "01581",
                                "01582", "01583", "01584", "01586", "01588", "01590", "01591", "01592", "01593", "01594",
                                "01595", "01597", "01598", "01599", "01600", "01603", "01604", "01606", "01608", "01610",
                                "01611", "01612", "01613", "01614", "01615", "01616", "01617", "01618", "01619", "01620",
                                "01621", "01622", "01623", "01624", "01625", "01626", "01628", "01629", "01630", "01631",
                                "01633", "01634", "01635", "01636", "01637", "01638", "01639", "01641", "01642", "01643",
                                "01644", "01646", "01647", "01650", "01651", "01652", "01653", "01654", "01655", "01656",
                                "01659", "01661", "01663", "01664", "01665", "01666", "01667", "01668", "01669", "01670",
                                "01671", "01672", "01673", "01674", "01675", "01676", "01677", "01678", "01680", "01681",
                                "01683", "01684", "01685", "01686", "01687", "01688", "01689", "01690", "01691", "01692",
                                "01694", "01695", "01697", "01698", "01700", "01702", "01704", "01706", "01707", "01708",
                                "01709", "01721", "01722", "01723", "01724", "01725", "01726", "01727", "01728", "01729",
                                "01730", "01731", "01732", "01733", "01736", "01737", "01738", "01740", "01743", "01744",
                                "01745", "01746", "01747", "01748", "01749", "01750", "01751", "01752", "01753", "01754",
                                "01756", "01757", "01758", "01759", "01760", "01761", "01763", "01764", "01765", "01766",
                                "01767", "01768", "01769", "01770", "01771", "01772", "01773", "01775", "01776", "01777",
                                "01778", "01779", "01780", "01782", "01784", "01785", "01786", "01787", "01788", "01789",
                                "01790", "01792", "01793", "01794", "01795", "01796", "01797", "01798", "01799", "01803",
                                "01805", "01806", "01807", "01808", "01809", "01821", "01822", "01823", "01824", "01825",
                                "01827", "01828", "01829", "01830", "01832", "01833", "01834", "01835", "01837", "01838",
                                "01840", "01841", "01842", "01843", "01844", "01845", "01847", "01848", "01851", "01852",
                                "01854", "01855", "01856", "01857", "01858", "01859", "01862", "01863", "01864", "01865",
                                "01866", "01869", "01870", "01871", "01872", "01873", "01874", "01875", "01876", "01877",
                                "01878", "01879", "01880", "01882", "01883", "01884", "01885", "01886", "01887", "01888",
                                "01889", "01890", "01892", "01895", "01896", "01899", "01900", "01902", "01903", "01904",
                                "01905", "01908", "01909", "01912", "01913", "01914", "01915", "01920", "01922", "01923",
                                "01924", "01925", "01926", "01928", "01929", "01931", "01932", "01933", "01934", "01935",
                                "01937", "01938", "01939", "01942", "01943", "01944", "01945", "01946", "01947", "01948",
                                "01949", "01950", "01951", "01952", "01953", "01954", "01955", "01957", "01959", "01962",
                                "01963", "01964", "01967", "01968", "01969", "01970", "01971", "01972", "01974", "01975",
                                "01977", "01978", "01980", "01981", "01982", "01983", "01984", "01985", "01986", "01988",
                                "01989", "01992", "01993", "01994", "01995", "01997", "02030", "02031", "02032", "02033",
                                "02034", "02035", "02036", "02037", "02038", "02039", "02070", "02071", "02072", "02073",
                                "02074", "02075", "02076", "02077", "02078", "02079", "02080", "02081", "02082", "02083",
                                "02084", "02085", "02086", "02087", "02088", "02089", "02380", "02392", "02476", "02820",
                                "02821", "02825", "02827", "02828", "02829", "02830", "02837", "02838", "02840", "02841",
                                "02842", "02843", "02844", "02866", "02867", "02868", "02870", "02871", "02877", "02879",
                                "02880", "02881", "02882", "02885", "02886", "02887", "02889", "02890", "02891", "02892",
                                "02893", "02894", "02897", "02900"
                            );

                            $validSTD = in_array($prefix2, $STD);
                        } else {
                            // Check for valid UK NGN
                            $NGN = array(
                                "03000", "03001", "03002", "03003", "03004", "03005", "03006", "03007", "03008", "03009",
                                "03440", "03441", "03442", "03443", "03444", "03445", "03446", "03447", "03448", "03449",
                                "03450", "03451", "03452", "03453", "03454", "03455", "03456", "03457", "03458", "03459",
                                "03700", "03701", "03702", "03703", "03704", "03705", "03706", "03707", "03708", "03709",
                                "03710", "03711", "03712", "03713", "03714", "03715", "03716", "03717", "03718", "03719",
                                "05000", "05001", "05002", "05003", "05004", "05005", "05006", "05007", "05008", "05009",
                                "08000", "08001", "08002", "08003", "08004", "08005", "08006", "08007", "08008", "08009",
                                "08440", "08441", "08442", "08443", "08444", "08445", "08446", "08447", "08448", "08449",
                                "08450", "08451", "08452", "08453", "08454", "08455", "08456", "08457", "08458", "08459",
                                "08700", "08701", "08702", "08703", "08704", "08705", "08706", "08707", "08708", "08709",
                                "08710", "08711", "08712", "08713", "08714", "08715", "08716", "08717", "08718", "08719",
                                "04088"
                            );

                            $validNGN = in_array($prefix2, $NGN);
                        }

                        if ((!$validSTD) && (!$validNGN)) {
                            return false;
                        }
                    }
                } //end UK
                break;

            case "CH" : {
                    //check for the correct 11 digits Swiss phone numbers in international format.
                    if (strlen($thenumber) == 10) {
                        if (substr($thenumber, 0, 1) != '0') {
                            $number_error = true;
                        }
                    }
                    // country code + number
                    if (strlen($thenumber) == 11) {
                        if (substr($thenumber, 0, 2) == '41') {
                            $thenumber = '0' . substr($thenumber, 2);
                        } else {
                            $number_error = true;
                        }
                    }
                    // international dialing prefix + country code + number
                    if (strlen($thenumber) > 11) {
                        if (substr($thenumber, 0, 4) == '0041') {
                            $thenumber = '0' . substr($thenumber, 4);
                        } else {
                            if (substr($thenumber, 0, 5) == '01141') {
                                $thenumber = '0' . substr($thenumber, 5);
                            } else {
                                $number_error = true;
                            }
                        }
                    }
                    // number
                    if (strlen($thenumber) < 10) {
                        $number_error = true;
                    }
                } //end CH
                break;

            case "SE": {
                    // international dialing prefix + country code + number
                    if (strlen($thenumber) > 8) {
                        if (substr($thenumber, 0, 2) == '46') {
                            $thenumber = '0' . substr($thenumber, 2);
                        } else if (substr($thenumber, 0, 4) == '0046') {
                            $thenumber = '0' . substr($thenumber, 4);
                        } else if (substr($thenumber, 0, 5) == '01146') {
                            $thenumber = '0' . substr($thenumber, 5);
                        } else {
                            $number_error = true;
                        }
                    }
                    // number
                    if (strlen($thenumber) < 11) {
                        if (substr($thenumber, 0, 1) == '0') {
                            $number_error = false;
                        } else {
                            $number_error = true;
                        }
                    }
                }
                break; // end SE

            case "AU": {
                    // Validate number
                    if ($match = $this->match_pattern("0[2356789]XXXXXXXX", $thenumber)) {
                        // Land line
                        $num1 = substr($thenumber, 0, 2);
                        $num2 = substr($thenumber, 2, 4);
                        $num3 = substr($thenumber, 6, 4);
                    } elseif ($match = $this->match_pattern("04XXXXXXXX", $thenumber)) {
                        // Mobile number
                        $num1 = substr($thenumber, 0, 4);
                        $num2 = substr($thenumber, 4, 3);
                        $num3 = substr($thenumber, 7, 3);
                    } else {
                        return false;
                    }
                    // Set the number parts if passed by reference.
                    if (isset($rPart1)) {
                        $rPart1 = $num1;
                    }
                    if (isset($rPart2)) {
                        $rPart2 = $num2;
                    }
                    if (isset($rPart3)) {
                        $rPart3 = $num3;
                    }
                }
                break; // end AU

            case "IT": {
                    // Test for Italy
                    if (strlen($thenumber) > 10) {
                        if (substr($thenumber, 0, 2) == '39') {
                            $thenumber = substr($thenumber, 2);
                        } else if (substr($thenumber, 0, 4) == '0039') {
                            $thenumber = substr($thenumber, 4);
                        } else if (substr($thenumber, 0, 5) == '01139') {
                            $thenumber = substr($thenumber, 5);
                        } else {
                            return false;
                        }
                    }
                }
                break; // end IT

            case "AR": {
                    //  All Argentina area codes must be listed in this arrary - taken from http://www.cnc.gov.ar/infotecnica/numeracion/indicativosinter.asp on December 17, 2010
                    $npalist = array(
                        "011", "0220", "02202", "0221", "02221", "02223", "02224", "02225", "02226", "02227", "02229",
                        "0223", "02241", "02242", "02243", "02244", "02245", "02246", "02252", "02254", "02255", "02257",
                        "02261", "02262", "02264", "02265", "02266", "02271", "02272", "02273", "02274", "02281", "02283",
                        "02284", "02285", "02286", "02291", "02292", "02293", "02314", "02316", "02317", "02320", "02322",
                        "02323", "02324", "02325", "02326", "02337", "02342", "02343", "02344", "02345", "02346", "02352",
                        "02353", "02354", "02355", "02356", "02357", "02358", "02268", "02296", "02297", "02362", "02267",
                        "0237", "02392", "02393", "02394", "02395", "02396", "02473", "02474", "02475", "02477", "02478",
                        "0291", "02921", "02922", "02923", "02924", "02925", "02926", "02927", "02928", "02929", "02932",
                        "02933", "02935", "02936", "02982", "02983", "03327", "03329", "03382", "03388", "03407", "03461",
                        "03487", "03488", "03489", "03832", "03833", "03835", "03837", "03838", "03711", "03715", "03721",
                        "03722", "03725", "03731", "03732", "03734", "03735", "03877", "0297", "02965", "02945", "02903",
                        "03385", "03387", "02336", "03472", "03463", "03467", "03468", "0351", "03521", "03522", "03524",
                        "03525", "0353", "03532", "03533", "03534", "03541", "03542", "03543", "03544", "03546", "03547",
                        "03548", "03549", "03562", "03563", "03564", "03571", "03572", "03573", "03574", "03575", "03576",
                        "0358", "03582", "03583", "03585", "03584", "03756", "03772", "03773", "03774", "03775", "03777",
                        "03781", "03782", "03783", "03786", "0345", "03454", "03455", "03456", "03458", "0343", "03435",
                        "03436", "03437", "03438", "03442", "03444", "03445", "03446", "03447", "03716", "03718", "03717",
                        "0388", "03884", "03885", "03886", "03887", "02941", "02338", "02333", "02334", "02335", "02331",
                        "02302", "02952", "02953", "02954", "03821", "03822", "03825", "03826", "03827", "0261", "02622",
                        "02623", "02624", "02625", "02626", "02627", "03741", "03743", "03758", "03757", "03755", "03754",
                        "03751", "03752", "02942", "02948", "0299", "02972", "02944", "02946", "02940", "02934", "02931",
                        "02920", "03878", "03876", "03868", "0387", "03875", "0264", "02646", "02647", "02648", "02651",
                        "02652", "02658", "02655", "02656", "02657", "02902", "02962", "02963", "02966", "0342", "03408",
                        "03406", "03409", "0341", "03400", "03401", "03402", "03404", "03405", "03462", "03460", "03469",
                        "03471", "03464", "03465", "03466", "03476", "03482", "03483", "03491", "03492", "03493", "03498",
                        "03496", "03497", "03857", "03858", "03861", "03844", "03845", "03846", "0385", "03854", "03855",
                        "03856", "03841", "03843", "02964", "02901", "0381", "03862", "03863", "03865", "03867", "03869",
                        "03894", "03891", "03892");

                    // Check for supported npa
                    $validnpa = $this->cisf_find_area($npalist, $thenumber);
                    if ($validnpa === false) {
                        return false;
                    }
                    $areacode = $validnpa['area_code'];
                    $subscriber = $validnpa['number'];

                    // Separate remaining digits into 2-4 or 3-4 or 4-4
                    if (strlen($subscriber) == 6) {
                        $number2 = substr($subscriber, 0, 2);
                        $number3 = substr($subscriber, 2, 4);
                    } else if (strlen($subscriber) == 7) {
                        $number2 = substr($subscriber, 0, 3);
                        $number3 = substr($subscriber, 3, 4);
                    } else if (strlen($subscriber) == 8) {
                        $number2 = substr($subscriber, 0, 4);
                        $number3 = substr($subscriber, 4, 4);
                    } else {
                        return false;
                    }

                    // Set the number parts if passed by reference.
                    if (isset($rPart1)) {
                        $rPart1 = $areacode;
                    }
                    if (isset($rPart2)) {
                        $rPart2 = $number2;
                    }
                    if (isset($rPart3)) {
                        $rPart3 = $number3;
                    }
                }
                break; // end AR

            case 'RU': {
                    //check for the correct 11 digits in RU phone numbers in international format.
                    // country code + number
                    if (strlen($thenumber) == 11) {
                        if (substr($thenumber, 0, 1) != 7) {
                            return false;
                        }
                    } elseif (strlen($thenumber) == 10) {
                        $thenumber = '7'.$thenumber;
                    } else {
                        return false;
                    }
                }
                break; // end RU

            default:
                $this->DebugPrint("Unknown Country Code ${country} passed to IsValidNumber: ${country}");
                $number_error = true;
                break;
        } // end Country switch
        // Set the corrected number		
        if (!$number_error) {
            $this->trunk_info['callerid'] = $thenumber;
        }

        return ($number_error ? false : true);
    }

    function html2text($badStr) {
        //remove PHP if it exists
        while (substr_count($badStr, '<' . '?') && substr_count($badStr, '?' . '>') && strpos($badStr, '?' . '>', strpos($badStr, '<' . '?')) > strpos($badStr, '<' . '?')) {
            $badStr = substr($badStr, 0, strpos($badStr, '<' . '?')) . substr($badStr, strpos($badStr, '?' . '>', strpos($badStr, '<' . '?')) + 2);
        }

        //remove comments
        while (substr_count($badStr, '<!--') && substr_count($badStr, '-->') && strpos($badStr, '-->', strpos($badStr, '<!--')) > strpos($badStr, '<!--')) {
            $badStr = substr($badStr, 0, strpos($badStr, '<!--')) . substr($badStr, strpos($badStr, '-->', strpos($badStr, '<!--')) + 3);
        }

        //now make sure all HTML tags are correctly written (> not in between quotes)
        for ($x = 0, $goodStr = '', $is_open_tb = false, $is_open_sq = false, $is_open_dq = false; isset($badStr{$x}) && strlen($chr = $badStr{$x}); $x++) {
            //take each letter in turn and check if that character is permitted there
            switch ($chr) {
                case '<':
                    if (!$is_open_tb && strtolower(substr($badStr, $x + 1, 5)) == 'style') {
                        $badStr = substr($badStr, 0, $x) . substr($badStr, strpos(strtolower($badStr), '</style>', $x) + 7);
                        $chr = '';
                    } elseif (!$is_open_tb && strtolower(substr($badStr, $x + 1, 6)) == 'script') {
                        $badStr = substr($badStr, 0, $x) . substr($badStr, strpos(strtolower($badStr), '</script>', $x) + 8);
                        $chr = '';
                    } elseif (!$is_open_tb) {
                        $is_open_tb = true;
                    } else {
                        $chr = '&lt;';
                    }
                    break;
                case '>':
                    if (!$is_open_tb || $is_open_dq || $is_open_sq) {
                        $chr = '&gt;';
                    } else {
                        $is_open_tb = false;
                    }
                    break;
                case '"':
                    if ($is_open_tb && !$is_open_dq && !$is_open_sq) {
                        $is_open_dq = true;
                    } elseif ($is_open_tb && $is_open_dq && !$is_open_sq) {
                        $is_open_dq = false;
                    } else {
                        $chr = '&quot;';
                    }
                    break;
                case "'":
                    if ($is_open_tb && !$is_open_dq && !$is_open_sq) {
                        $is_open_sq = true;
                    } elseif ($is_open_tb && !$is_open_dq && $is_open_sq) {
                        $is_open_sq = false;
                    }
            }
            $goodStr .= $chr;
        }

        //now that the page is valid (I hope) for strip_tags, strip all unwanted tags
        $goodStr = strip_tags($goodStr, '<title><hr><h1><h2><h3><h4><h5><h6><div><p><pre><sup><ul><ol><br><dl><dt><table><caption><tr><li><dd><th><td><a><area><img><form><input><textarea><button><select><option>');

        //strip extra whitespace except between <pre> and <textarea> tags
        $badStr = preg_split("/<\/?pre[^>]*>/i", $goodStr);
        for ($x = 0; isset($badStr[$x]) && is_string($badStr[$x]); $x++) {
            if ($x % 2) {
                $badStr[$x] = '<pre>' . $badStr[$x] . '</pre>';
            } else {
                $goodStr = preg_split("/<\/?textarea[^>]*>/i", $badStr[$x]);
                for ($z = 0; isset($goodStr[$z]) && is_string($goodStr[$z]); $z++) {
                    if ($z % 2) {
                        $goodStr[$z] = '<textarea>' . $goodStr[$z] . '</textarea>';
                    } else {
                        $goodStr[$z] = preg_replace("/\s+/", ' ', $goodStr[$z]);
                    }
                }
                $badStr[$x] = implode('', $goodStr);
            }
        }
        $goodStr = implode('', $badStr);
        //remove all options from select inputs
        $goodStr = preg_replace("/<option[^>]*>[^<]*/i", '', $goodStr);
        //replace all tags with their text equivalents
        $goodStr = preg_replace("/<(\/title|hr)[^>]*>/i", "\n          --------------------\n", $goodStr);
        $goodStr = preg_replace("/<(h|div|p)[^>]*>/i", "\n\n", $goodStr);
        $goodStr = preg_replace("/<sup[^>]*>/i", '^', $goodStr);
        $goodStr = preg_replace("/<(ul|ol|br|dl|dt|table|caption|\/textarea|tr[^>]*>\s*<(td|th))[^>]*>/i", "\n", $goodStr);
        $goodStr = preg_replace("/<li[^>]*>/i", "\n� ", $goodStr);
        $goodStr = preg_replace("/<dd[^>]*>/i", "\n\t", $goodStr);
        $goodStr = preg_replace("/<(th|td)[^>]*>/i", "\t", $goodStr);
        $goodStr = preg_replace("/<a[^>]* href=(\"((?!\"|#|javascript:)[^\"#]*)(\"|#)|'((?!'|#|javascript:)[^'#]*)('|#)|((?!'|\"|>|#|javascript:)[^#\"'> ]*))[^>]*>/i", "[LINK: $2$4$6] ", $goodStr);
        $goodStr = preg_replace("/<img[^>]* alt=(\"([^\"]+)\"|'([^']+)'|([^\"'> ]+))[^>]*>/i", "[IMAGE: $2$3$4] ", $goodStr);
        $goodStr = preg_replace("/<form[^>]* action=(\"([^\"]+)\"|'([^']+)'|([^\"'> ]+))[^>]*>/i", "\n[FORM: $2$3$4] ", $goodStr);
        $goodStr = preg_replace("/<(input|textarea|button|select)[^>]*>/i", "[INPUT] ", $goodStr);
        //strip all remaining tags (mostly closing tags)
        $goodStr = strip_tags($goodStr);
        //convert HTML entities
        $goodStr = strtr($goodStr, array_flip(get_html_translation_table(HTML_ENTITIES)));
        preg_replace("/&#(\d+);/me", "chr('$1')", $goodStr);
        //wordwrap
        $goodStr = wordwrap($goodStr);
        //make sure there are no more than 3 linebreaks in a row and trim whitespace
        return preg_replace("/^\n*|\n*$/", '', preg_replace("/[ \t]+(\n|$)/", "$1", preg_replace("/\n(\s*\n){2}/", "\n\n\n", preg_replace("/\r\n?|\f/", "\n", str_replace(chr(160), ' ', $goodStr)))));
    }

    /**
      Parse XML file into an array
     */
    function xml2array($url, $get_attributes = 1, $priority = 'tag') {
        $contents = "";
        if (!function_exists('xml_parser_create')) {
            return array();
        }
        $parser = xml_parser_create('');
        if (!($fp = @ fopen($url, 'rb'))) {
            return array();
        }
        while (!feof($fp)) {
            $contents .= fread($fp, 8192);
        }
        fclose($fp);
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);
        if (!$xml_values) {
            return; //Hmm...
        }
        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();
        $current = & $xml_array;
        $repeated_tag_index = array();
        foreach ($xml_values as $data) {
            unset($attributes, $value);
            extract($data);
            $result = array();
            $attributes_data = array();
            if (isset($value)) {
                if ($priority == 'tag') {
                    $result = $value;
                } else {
                    $result['value'] = $value;
                }
            }
            if (isset($attributes) and $get_attributes) {
                foreach ($attributes as $attr => $val) {
                    if ($priority == 'tag') {
                        $attributes_data[$attr] = $val;
                    } else {
                        $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                    }
                }
            }
            if ($type == "open") {
                $parent[$level - 1] = & $current;
                if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
                    $current[$tag] = $result;
                    if ($attributes_data) {
                        $current[$tag . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    $current = & $current[$tag];
                } else {
                    if (isset($current[$tag][0])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {
                        $current[$tag] = array($current[$tag], $result);
                        $repeated_tag_index[$tag . '_' . $level] = 2;
                        if (isset($current[$tag . '_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                    $current = & $current[$tag][$last_item_index];
                }
            } else if ($type == "complete") {
                if (!isset($current[$tag])) {
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $attributes_data) {
                        $current[$tag . '_attr'] = $attributes_data;
                    }
                } else {
                    if (isset($current[$tag][0]) and is_array($current[$tag])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        if ($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {
                        $current[$tag] = array($current[$tag], $result);
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        if ($priority == 'tag' and $get_attributes) {
                            if (isset($current[$tag . '_attr'])) {
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset($current[$tag . '_attr']);
                            }
                            if ($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                    }
                }
            } else if ($type == 'close') {
                $current = & $parent[$level - 1];
            }
        }
        return ($xml_array);
    }

    /**
     * Taken from http://www.php.net/manual/en/function.array-search.php#69232
     * search haystack for needle and return an array of the key path, FALSE otherwise.
     * if NeedleKey is given, return only for this key mixed ArraySearchRecursive(mixed Needle,array Haystack[,NeedleKey[,bool Strict[,array Path]]])
     * @author ob (at) babcom (dot) biz
     * @param mixed $Needle
     * @param array $Haystack
     * @param mixed $NeedleKey
     * @param bool $Strict
     * @param array $Path
     * @return array
     */
    function arraysearchrecursive($Needle, $Haystack, $NeedleKey="", $Strict=false, $Path=array()) {
        if (!is_array($Haystack))
            return false;
        foreach ($Haystack as $Key => $Val) {
            if (is_array($Val) &&
                    $SubPath = $this->arraysearchrecursive($Needle, $Val, $NeedleKey, $Strict, $Path)) {
                $Path = array_merge($Path, Array($Key), $SubPath);
                return $Path;
            } elseif ((!$Strict && $Val == $Needle &&
                    $Key == (strlen($NeedleKey) > 0 ? $NeedleKey : $Key)) ||
                    ($Strict && $Val === $Needle &&
                    $Key == (strlen($NeedleKey) > 0 ? $NeedleKey : $Key))) {
                $Path[] = $Key;
                return $Path;
            }
        }
        return false;
    }

    function DebugEcho($string, $level=DEBUG_INFO) {
        if ($this->isDebug($level)) {
            $this->out($string);
        }
        $this->debug_log[] = "[". time()."][".$level."] ".strip_tags($string);
    }

    function DebugPrint($string, $level=DEBUG_INFO) {
        if ($this->isDebug($level)) {
            $this->outn($string);
        }
        $this->debug_log[] = "[". time()."][".$level."] ".strip_tags($string);
    }

    function DebugDump($v, $level=DEBUG_ALL) {
        if ($this->isDebug($level)) {
            foreach ($v as $key => $data) {
                //Get rid of useless HTML tags!
                $v[$key] = is_array($data) ? array_map('htmlentities', $data) : htmlentities($data);
            }
            $this->out("<pre>");
            var_dump($v);
            $this->out("</pre><br/>");
            //html_entity_decode()
        }
    }

    function DebugDie($sError) {
        if ($this->isDebug(DEBUG_WARN) && (!$this->cli)) {
            echo "<hr /><div><strong>" . $sError . "</strong><br /><table border='1'>";
            $sOut = "";
            $aCallstack = debug_backtrace();

            echo "<thead><tr><th>file</th><th>line</th><th>function</th></tr></thead>";
            foreach ($aCallstack as $aCall) {
                if (!isset($aCall['file']))
                    $aCall['file'] = '[PHP Kernel]';
                if (!isset($aCall['line']))
                    $aCall['line'] = '';

                echo "<tr><td>{$aCall["file"]}</td><td>{$aCall["line"]}</td><td>{$aCall["function"]}</td></tr>";
            }
            echo "</table></div><hr />";
            die();
        } else {
            die($sError);
        }
    }

    function sys_get_temp_dir() {
        if (!empty($_ENV['TMP'])) {
            return realpath($_ENV['TMP']);
        }
        if (!empty($_ENV['TMPDIR'])) {
            return realpath($_ENV['TMPDIR']);
        }
        if (!empty($_ENV['TEMP'])) {
            return realpath($_ENV['TEMP']);
        }
        $tempfile = tempnam(uniqid(rand(), TRUE), '');
        if (file_exists($tempfile)) {
            unlink($tempfile);
            return realpath(dirname($tempfile));
        }
    }
    
    function ContainsKeywords($name, $keywords) {
        $key_words = array();
        $temp_array = explode(',', $keywords);
        foreach ($temp_array as $val) {
            $key_words[] = trim($val);
        }

        return (($name == str_ireplace($key_words, '', $name)) ? false : true);
    }

    function SearchURL($url, $regexp, &$match, $PostData=null, $strip_trn = FALSE) {
        $this->DebugPrint("Search URL={$url}", DEBUG_WARN);
        $value = $this->get_url_contents($url, $PostData);

        //Remove all newlines, carriage returns and tabs from content if needed
        $value = ($strip_trn) ? preg_replace('/[\n\r\t]*/i', '', $value) : $value;

        $this->DebugPrint("Returned Content (w/Stripped \\n\\r\\t):<br /><textarea rows='2' cols='20'>" . $value . "</textarea>", DEBUG_ALL);


        if (is_array($regexp)) {
            // Look through each pattern to see if we find a match -- take the first match
            foreach ($regexp as $pattern) {
                $this->DebugPrint("Testing pattern=" . htmlentities($pattern), DEBUG_WARN);
                $result = preg_match($pattern, $value, $match);
                if ($result) {
                    break;
                }
            }
        } else {
            $this->DebugPrint("Testing pattern=" . htmlentities($regexp), DEBUG_WARN);
            $result = preg_match($regexp, $value, $match);
        }
        $this->DebugPrint("Dumping Matches", DEBUG_WARN);
        $this->DebugDump($match);

        return $result;
    }

    function ExtractMatch($match, $index=1) {
        $name = "";

        if (isset($match[$index]) && strlen($match[$index])) {
            //putting this here too just incase we need to remove newlines and such from found elements
            $match[$index] = preg_replace('/[\n\r\t]*/i', '', $match[$index]);
            // Remove any ASCII embedded HEX codes e.g. \x27
            $name = $this->StripHexCodes(trim(strip_tags($match[$index])));
        } else {
            $this->DebugPrint("not found");
        }
        return $name;
    }

    function FormatNumber($thenumber, $mask) {
        $sResult = $thenumber;
        $thenumber = preg_replace('/[^0-9^\.]/', "", $thenumber);  // strip non-digits
        // If mask fits the number after removing other characters
        if (strlen($thenumber) == strlen(preg_replace('/[^0-9^\.]/', "", $mask))) {
            for ($sResult = "", $m = 0, $n = 0; $m < strlen($mask); $m++) {
                if (is_numeric(substr($mask, $m, 1))) {
                    $sResult .= substr($thenumber, $n++, 1);
                } else {
                    $sResult .= substr($mask, $m, 1);
                }
            }
        }
        return $sResult;
    }

    function StripHexCodes($value) {
        // Search for \x99 pattern - note 4 backslashes translates to '\\' when called
        while (preg_match("/\\\\x([0-9][0-9])/", $value, $match)) {
            // Fetch HEX value from regex match.
            $hex = $this->ExtractMatch($match);

            // Convert escaped hex to ASCII character  e.g. \x27 to chr(39)
            $value = str_replace("\x{$hex}", chr(hexdec($hex)), $value);
        }
        return $value;
    }
    
    function flush_buffers() {
        $array = ob_get_status();
        if(!empty($array)) {
            ob_end_flush();
        }
        //ob_flush();
        flush();
        ob_start();
    }

}