<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//    License for all code of this IssabelPBX module can be found in the license file inside the module directory
//    Copyright 2013 Schmooze Com Inc.

class logfiles_conf {
    var $_loggergeneral  = array();
    var $_loggerlogfiles = array();

    private static $obj;

    // IssabelPBX magic ::create() call
    public static function create() {
        if (!isset(self::$obj))
            self::$obj = new logfiles_conf();

        return self::$obj;
    }

    public function __construct() {
        self::$obj = $this;
    }

    // return an array of filenames to write
    function get_filename() {
        return array(
                'logger_general_additional.conf',
                'logger_logfiles_additional.conf',
                );
    }

    // return the output that goes in each of the files
    function generateConf($file) {
        global $version, $amp_conf;

        switch ($file) {
            case 'logger_general_additional.conf':
                return $this->generate_loggergeneral_additional($version);
                break;
            case 'logger_logfiles_additional.conf':
                return $this->generate_loggerlogfiles_additional($version);
                break;
        }
    }


    function addLoggerGeneral($key, $value) {
        $this->_loggergeneral[] = array('key' => $key, 'value' => $value);
    }

    function generate_loggergeneral_additional($ast_version) {
        $output = '';

        if (isset($this->_loggergeneral) && is_array($this->_loggergeneral)) {
            foreach ($this->_loggergeneral as $values) {
                $output .= $values['key'] . '=' . $values['value'] . "\n";
            }
        }

        return $output;
    }

    function addLoggerLogfiles($key, $value) {
        $this->_loggerlogfiles[] = array('key' => $key, 'value' => $value);
    }

    function generate_loggerlogfiles_additional($ast_version) {
        $output = '';
        if (isset($this->_loggerlogfiles) && is_array($this->_loggerlogfiles)) {
            foreach ($this->_loggerlogfiles as $values) {
                $output .= $values['key'] . ' => ' . $values['value'] . "\n";
            }
        }
        return $output;
    }

}
/*
 * Highlight asterisk applications
 */
function logfiles_highlight_asterisk($line,&$channels) {
    //for i in `asterisk -rx 'core show applications'|awk '{print $1}'|grep -v -|sed 's/://g'`; do echo -n $i'|'; done
    static $apps = 'AddQueueMember|ADSIProg|AELSub|AGI|Answer|Authenticate|BackGround|BackgroundDetect|Bridge|Busy|CallCompletionCancel|CallCompletionRequest|CELGenUserEvent|ChangeMonitor|ChanIsAvail|ChannelRedirect|ChanSpy|ClearHash|ConfBridge|Congestion|ContinueWhile|ControlPlayback|DAHDIAcceptR2Call|DAHDIBarge|DAHDIRAS|DAHDIScan|DAHDISendKeypadFacility|DateTime|DBdel|DBdeltree|DeadAGI|Dial|Dictate|Directory|DISA|DumpChan|EAGI|Echo|EndWhile|Exec|ExecIf|ExecIfTime|ExitWhile|ExtenSpy|ExternalIVR|Flash|Flite|ForkCDR|GetCPEID|Gosub|GosubIf|Goto|GotoIf|GotoIfTime|Hangup|IAX2Provision|ICES|ImportVar|Incomplete|Log|Macro|MacroExclusive|MacroExit|MacroIf|MailboxExists|MeetMe|MeetMeAdmin|MeetMeChannelAdmin|MeetMeCount|Milliwatt|MinivmAccMess|MinivmDelete|MinivmGreet|MinivmMWI|MinivmNotify|MinivmRecord|MixMonitor|Monitor|Morsecode|MP3Player|MSet|MusicOnHold|MYSQL|NBScat|NoCDR|NoOp|Originate|Page|Park|ParkAndAnnounce|ParkedCall|PauseMonitor|PauseQueueMember|Pickup|PickupChan|Playback|PlayTones|PrivacyManager|Proceeding|Progress|Queue|QueueLog|RaiseException|Read|ReadExten|ReadFile|ReceiveFAX|Record|RemoveQueueMember|ResetCDR|RetryDial|Return|Ringing|SayAlpha|SayCountPL|SayDigits|SayNumber|SayPhonetic|SayUnixTime|SendDTMF|SendFAX|SendImage|SendText|SendURL|Set|SetAMAFlags|SetCallerPres|SetMusicOnHold|SIPAddHeader|SIPDtmfMode|SIPRemoveHeader|SLAStation|SLATrunk|SMS|SoftHangup|SpeechActivateGrammar|SpeechBackground|SpeechCreate|SpeechDeactivateGrammar|SpeechDestroy|SpeechLoadGrammar|SpeechProcessingSound|SpeechStart|SpeechUnloadGrammar|StackPop|StartMusicOnHold|StopMixMonitor|StopMonitor|StopMusicOnHold|StopPlayTones|System|TestClient|TestServer|Transfer|TryExec|TrySystem|UnpauseMonitor|UnpauseQueueMember|UserEvent|Verbose|VMAuthenticate|VMSayName|VoiceMail|VoiceMailMain|Wait|WaitExten|WaitForNoise|WaitForRing|WaitForSilence|WaitMusicOnHold|WaitUntil|While|Zapateller';

    //Match Channel ID
    $colors = array("silver","seagreen","lime","red","orange","green","yellow","magenta","pink");
    if(preg_match('/\[(\d*)\]/',$line,$matches)) {
        if(!isset($channels[$matches[1]])) {
            $channels[$matches[1]] = $colors[rand(0,count($colors)-1)];
        }
        $line = str_replace('['.$matches[1].']','[<span class="'.$channels[$matches[1]].'">'.$matches[1].'</span>]',$line);
    }

    //match any app
    $line = preg_replace('/(?:' . $apps . ')(?=\()/', '<span class="app">$0</span>', $line, 1);
    //match arguments
    $line = preg_replace('/(?<=\(\").*(?=\"\,)/', '<span class="appargs">$0</span>', $line, 1);
    $line = preg_replace('/(?<=\,( )\").*(?=\"\))/', '<span class="appargs">$0</span>', $line, 1);
    return $line;
}

/**
 * Get last X lines of log file, with html tags to provide highlighting
 */
function logfiles_get_logfile($lines = 500, $file) {
    global $amp_conf;
    $files = logfiles_list();
    $logfile = $amp_conf['ASTLOGDIR'] . '/' . $files[$file];

    if (!file_exists($logfile) || !is_file($logfile)) {
        echo _('Error parsing log file or file not found!');
        return;
    }

    $channels = array();
    exec(ipbx_which('tail') . ' -n' . $lines . ' ' . $logfile, $log);
    foreach($log as $l){
        switch (true) {
            case strpos($l, 'INFO'):
                $l = '<span class="beige">' . htmlentities($l) . '</span>';
                break;
            case strpos($l, 'WARNING'):
                $l = '<span class="orange">' . htmlentities($l) . '</span>';
                break;
            case strpos($l, 'DEBUG'):
                $l = '<span class="green">' . htmlentities($l) . '</span>';
                break;
            case strpos($l, 'UPDATE'):
            case strpos($l, 'NOTICE'):
                $l = '<span class="cyan">' . htmlentities($l) . '</span>';
                break;
            case strpos($l, 'FATAL'):
            case strpos($l, 'CRITICAL'):
            case strpos($l, 'ERROR'):
                $l = '<span class="red">' . htmlentities($l) . '</span>';
                break;
            default:
                $l = logfiles_highlight_asterisk(htmlentities($l, ENT_NOQUOTES),$channels);
                break;
        }
        echo $l . '<br />';
    }
}

/**
 * Generate astierks configs
 */
function logfiles_get_config($engine) {
    global $ext, $amp_conf;
    $logfiles_conf = logfiles_conf::create();

    $has_security_option = version_compare($amp_conf['ASTVERSION'],'11.0','ge');
    switch ($engine) {
        case 'asterisk':
            $opts = logfiles_get_opts();
            //set logfile data to be generated
            //dbug('here', (isset($logfiles_conf) && ($logfiles_conf instanceof logfiles_conf)), 1);
            if (!isset($logfiles_conf) || !($logfiles_conf instanceof logfiles_conf)) {
                dbug('NOT GENERATING LOGGER CONFIGS AS $logfiles_conf IS NOT SET!');
                return false;
            }

            foreach ($opts as $k => $v) {
                switch ($k) {
                    case 'appendhostname':
                    case 'dateformat':
                    case 'queue_log':
                    case 'rotatestrategy':
                        if ($v) {
                            $logfiles_conf->addLoggerGeneral($k, $v);
                        }
                        break;
                    default:
                        break;
                }
            }

            foreach ($opts['logfiles'] as $k => $v) {
                $name = $v['name'];
                unset($v['name']);
                foreach ($v as $opt => $set) {
                    if ($set == 'on') {
                        if ($has_security_option || $opt != 'security') {
                            if($opt=='verbose') { $opt='verbose(3)'; }
                            $name_opt[] = $opt;
                        }
                    }
                }
                //dbug($name, $name_opt);
                $logfiles_conf->addLoggerLogfiles($name, implode(',', $name_opt));
                if (isset($name_opt)) {
                    unset($name_opt);
                }

            }
            break;
    }
}

/**
 * Gets logfile relates settings
 */
function logfiles_get_opts() {
    global $db;
    $settings = array();
    $setting = $db->getAll('SELECT * FROM logfile_settings', DB_FETCHMODE_ASSOC);
    db_e($setting);

    if ($setting) {
        foreach ($setting as $s) {
            $settings[$s['key']] = $s['value'];
        }
    }

    //add defaults if none are set
    $settings['dateformat']    = isset($settings['dateformat'])
        ? $settings['dateformat'] : '';
    $settings['appendhostname']    = isset($settings['appendhostname'])
        ? $settings['appendhostname'] : '';
    $settings['queue_log']         = isset($settings['queue_log'])
        ? $settings['queue_log'] : '';
    $settings['rotatestrategy']    = isset($settings['rotatestrategy'])
        ? $settings['rotatestrategy'] : '';
    foreach ($settings as $k => $v) {
        switch ($k) {
            case 'dateformat':
                if (!$v) {
                    $settings[$k] = '%F %T';
                }
                break;
            case 'rotatestrategy':
                if (!$v) {
                    $settings[$k] = 'rotate';
                }
                break;
            case 'queue_log':
                if (!$v) {
                    $settings[$k] = 'yes';
                }
                break;
            case 'appendhostname':
                if (!$v) {
                    $settings[$k] = 'no';
                }
                break;
        }
    }


    $settings['logfiles'] = $db->getAll('SELECT * FROM logfile_logfiles', DB_FETCHMODE_ASSOC);
    db_e($settings['logfiles']);

    return $settings;
}

/**
 * Get list of files in log directory
 */
function logfiles_list() {
    global $amp_conf;

    $dir = scandirr($amp_conf['ASTLOGDIR'], true);

    //only show files, relative to $amp_conf['ASTLOGDIR']
    foreach ($dir as $k => $v) {
        if (!is_file($v)) {
            unset($dir[$k]);
        } else {
            $dir[$k] = str_replace($amp_conf['ASTLOGDIR'] . '/', '', $v); //relative paths only
        }
    }

    return array_values($dir);
}


/**
 * Saves logfile related settings
 */
function logfiles_put_opts($opts) {
    global $db, $amp_conf;
    $has_security_option = version_compare($amp_conf['ASTVERSION'],'11.0','ge');
    //save options
    foreach ($opts as $k => $v) {
        switch ($k) {
            case 'appendhostname':
            case 'dateformat':
            case 'queue_log':
            case 'rotatestrategy':
                $data[] = array($k, $v);
                break;
            default:
                break; //do nothing
        }
    }

    //dbug('save settings', $data);
    $sql = $db->prepare('REPLACE INTO logfile_settings (`key`, value) VALUES (?, ?)');
    $ret = $db->executeMultiple($sql, $data);
    db_e($ret);

    unset($data);

    //save log files
    foreach ($opts['logfiles'] as $item => $values) {
        foreach ($values as $index => $v) {
            $logs[$index][$item] = $v;
            if (!$has_security_option) {
                $logs[$index]['security'] = 'off';
            }
        }
    }

    //ensure the order of our array is correct
    foreach ($logs as $k => $l) {
        $data = array('name' => $l['name'],
                'debug' => $l['debug'],
                'dtmf' => $l['dtmf'],
                'error' => $l['error'],
                'fax' => $l['fax'],
                'notice' => $l['notice'],
                'verbose' => $l['verbose'],
                'warning' => $l['warning'],
                'security' => $l['security']);
        $logData[] = array_values($data);
    }

    sql('TRUNCATE logfile_logfiles');

    $sql = $db->prepare('INSERT INTO logfile_logfiles
            (name, debug, dtmf, error, fax, notice, verbose, warning, security)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $ret = $db->executeMultiple($sql, $logData);
    db_e($ret);

    needreload();
    return true;
}

/*
 * Draw right navigation box
 */
function logfiles_rnav() {
    $html = '';
    $html .= '<div class="rnav"><ul>'."\n";
    $html .= '<li><a href="config.php?display=logfiles">'._('View Logs').'</a></li>';
    $html .= '<li><a href="config.php?display=logfiles&view=opts">'._('Log file settings').'</a></li>';
    $html .= "</ul><br /></div>";

    return $html;
}
?>
