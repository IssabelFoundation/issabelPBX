<?php
define("LOCAL_PATH", str_replace("includes","",dirname(__FILE__)));
global $amp_conf;

if(!isset($amp_conf) OR empty($amp_conf)) {
    if(file_exists("/etc/issabelpbx.conf")) {
            //This is IssabelPBX 2.9+
            $bootstrap_settings['issabelpbx_auth'] = false;
            require_once("/etc/issabelpbx.conf");
    } elseif(file_exists("/etc/asterisk/issabelpbx.conf")) {
            //This is IssabelPBX 2.9+
            $bootstrap_settings['issabelpbx_auth'] = false;
            require_once("/etc/asterisk/issabelpbx.conf");
    } else {
            //This is > IssabelPBX 2.8	
            $functions_location = str_replace("modules/superfecta/includes", "", dirname(__FILE__))."functions.inc.php";
            require_once($functions_location);	
            require_once 'DB.php';
            define("AMP_CONF", "/etc/amportal.conf");
            $amp_conf = parse_amportal_conf(AMP_CONF);
            if(count($amp_conf) == 0) {
                    fatal("FAILED");
            }
            $dsn = array(
                'phptype'  => 'mysql',
                'username' => $amp_conf['AMPDBUSER'],
                'password' => $amp_conf['AMPDBPASS'],
                'hostspec' => $amp_conf['AMPDBHOST'],
                'database' => $amp_conf['AMPDBNAME'],
            );
            $options = array();
            $db =& DB::connect($dsn, $options);
            if(PEAR::isError($db)){
                    die($db->getMessage());
            }

            //connect to the asterisk manager
            $phpasman_location = exec('find '.$amp_conf['AMPWEBROOT'].'/admin -name php-asmanager.php');
            if (file_exists($phpasman_location)) {

                require_once($phpasman_location);
                $astman = new AGI_AsteriskManager();

                // attempt to connect to asterisk manager proxy
                if (!isset($amp_conf["ASTMANAGERPROXYPORT"]) || !$res = $astman->connect("127.0.0.1:" . $amp_conf["ASTMANAGERPROXYPORT"], $amp_conf["AMPMGRUSER"], $amp_conf["AMPMGRPASS"], 'off')) {
                    // attempt to connect directly to asterisk, if no proxy or if proxy failed
                    if (!$res = $astman->connect("127.0.0.1:" . $amp_conf["ASTMANAGERPORT"], $amp_conf["AMPMGRUSER"], $amp_conf["AMPMGRPASS"], 'off')) {
                        // couldn't connect at all
                        die('Could Not Connect to Asterisk Manager!');
                    }
                }
            } else {
                die('Could Not Load php-asmanager.php!');
            }
    
    }
} else {
    global $db,$amp_conf,$astman;
    
    if (!is_object($astman)) {
        $phpasman_location = exec('find '.$amp_conf['AMPWEBROOT'].'/admin -name php-asmanager.php');
        if (file_exists($phpasman_location)) {
            require_once($phpasman_location);
            $astman = new AGI_AsteriskManager();

            // attempt to connect to asterisk manager proxy
            if (!isset($amp_conf["ASTMANAGERPROXYPORT"]) || !$res = $astman->connect("127.0.0.1:" . $amp_conf["ASTMANAGERPROXYPORT"], $amp_conf["AMPMGRUSER"], $amp_conf["AMPMGRPASS"], 'off')) {
                // attempt to connect directly to asterisk, if no proxy or if proxy failed
                if (!$res = $astman->connect("127.0.0.1:" . $amp_conf["ASTMANAGERPORT"], $amp_conf["AMPMGRUSER"], $amp_conf["AMPMGRPASS"], 'off')) {
                    // couldn't connect at all
                    die('Could Not Connect to Asterisk Manager!');
                }
            }
        } else {
            die('Could Not Load php-asmanager.php!');
        }
    }
}
