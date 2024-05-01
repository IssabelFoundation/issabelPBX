<?php
//      License for all code of this IssabelPBX module can be found in the license file inside the module directory
//      Copyright 2013 Schmooze Com Inc.
//  Copyright (c) 2003, 2004, 2005 Matthew Asham <matthewa@bcwireless.net>, David Eder <david@eder.us>

if(!class_exists('AGI'))
{
    require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'phpagi.php');
}

/**
 * Asterisk Manager class
 *
 * @link http://www.voip-info.org/wiki-Asterisk+config+manager.conf
 * @link http://www.voip-info.org/wiki-Asterisk+manager+API
 * @example examples/sip_show_peer.php Get information about a sip peer
 * @package phpAGI
 */
class AGI_AsteriskManager {
    /**
     * Config variables
     *
     * @var array
     * @access public
     */
    public $config;

    /**
     * Socket
     *
     * @access public
     */
    public $socket = NULL;

    /**
     * Server we are connected to
     *
     * @access public
     * @var string
     */
    public $server;

    /**
     * Port on the server we are connected to
     *
     * @access public
     * @var integer
     */
    public $port;

    /**
     * Parent AGI
     *
     * @access private
     * @var AGI
     */
    public $pagi;

    /**
     * Username we connected with (for reconnect)
     *
     * @access public
     * @var string
     */
    public $username = NULL;

    /**
     * Secret we connected with (for reconnect)
     *
     * @access public
     * @var string
     */
    public $secret = NULL;

    /**
     * Current state of events (for reconnect)
     *
     * @access public
     * @var string
     */
    public $events = NULL;

    /**
     * Number of reconnect attempts per incident
     *
     * @access public
     * @var string
     */
    public $reconnects = 2;

    /**
     * Event Handlers
     *
     * @access private
     * @var array
     */
    public $event_handlers;

    /**
     * Log Level
     *
     * @access private
     * @var integer
     */
    public $log_level;

    public $useCaching = false;
    public $memAstDB = null;

    /**
     * Constructor
     *
     * @param string $config is the name of the config file to parse or a parent agi from which to read the config
     * @param array $optconfig is an array of configuration vars and vals, stuffed into $this->config['asmanager']
     */
    function __construct($config=NULL, $optconfig=array()) {
        // load config
        if(!is_null($config) && file_exists($config)) {
            $this->config = parse_ini_file($config, true);
        } elseif(file_exists(DEFAULT_PHPAGI_CONFIG)) {
            $this->config = parse_ini_file(DEFAULT_PHPAGI_CONFIG, true);
        }

        // If optconfig is specified, stuff vals and vars into 'asmanager' config array.
        foreach($optconfig as $var=>$val) {
            $this->config['asmanager'][$var] = $val;
        }


        // add default values to config for uninitialized values
        if (!isset($this->config['asmanager']['server'])) {
            $this->config['asmanager']['server'] = 'localhost';
        }
        if (!isset($this->config['asmanager']['port'])) {
            $this->config['asmanager']['port'] = 5038;
        }
        if (!isset($this->config['asmanager']['username'])) {
            $this->config['asmanager']['username'] = 'phpagi';
        }
        if (!isset($this->config['asmanager']['secret'])) {
            $this->config['asmanager']['secret'] = 'phpagi';
        }
        if (isset($this->config['asmanager']['cachemode'])) {
            $this->useCaching = $this->config['asmanager']['cachemode'];
        }

        $this->log_level = (isset($this->config['asmanager']['log_level']) && is_numeric($this->config['asmanager']['log_level']))
            ? $this->config['asmanager']['log_level'] : false;
        $this->reconnects = isset($this->config['asmanager']['reconnects']) ? $this->config['asmanager']['reconnects'] : 2;
    }

    function LoadAstDB(){
        if ($this->memAstDB != null) {
            unset($this->memAstDB);
        }
        $this->memAstDB = $this->database_show();
    }

    /**
     * Send a request
     *
     * @param string $action
     * @param array $parameters
     * @return array of parameters
     */
    function send_request($action, $parameters=array(), $retry=true) {
        $reconnects = $this->reconnects;

        $req = "Action: $action\r\n";
        foreach($parameters as $var=>$val) {
            if (is_array($val)) {
                foreach($val as $k => $v) {
                    $req .= "$var: $k=$v\r\n";
                }
            } else {
                $req .= "$var: $val\r\n";
            }

        }
        $req .= "\r\n";
        $this->log("Sending Request down socket:",10);
        $this->log($req,10);
        fwrite($this->socket, $req);
        $response = $this->wait_response();

        // If we got a false back then something went wrong, we will try to reconnect the manager connection to try again
        //
        while ($response === false && $retry && $reconnects > 0) {
            $this->log("Unexpected failure executing command: $action, reconnecting to manager and retrying: $reconnects");
            $this->disconnect();
            if ($this->connect($this->server.':'.$this->port, $this->username, $this->secret, $this->events) !== false) {
                fwrite($this->socket, $req);
                $response = $this->wait_response();
            } else {
                if ($reconnects > 1) {
                    $this->log("reconnect command failed, sleeping before next attempt");
                    sleep(1);
                } else {
                    $this->log("FATAL: no reconnect attempts left, command permanently failed, returning to calling program with 'false' failure code");
                }
            }
            $reconnects--;
        }
        if($action == 'Command' && empty($response['data']) && !empty($response['Output'])) {
            $response['data'] = $response['Output'];
            unset($response['Output']);
        }
        return $response;
    }

    /**
     * Wait for a response
     *
     * If a request was just sent, this will return the response.
     * Otherwise, it will loop forever, handling events.
     *
     * @param boolean $allow_timeout if the socket times out, return an empty array
     * @return array of parameters, empty on timeout
     */
    function wait_response($allow_timeout = false, $return_on_event = false) {
        $timeout = false;
        do {
            $type = NULL;
            $parameters = array();

            if (feof($this->socket) || !$this->socket) {
                $this->log("Got EOF in wait_response() from socket waiting for response, returning false",10);
                return false;
            }
            $buffer = trim(fgets($this->socket, 4096));
            while($buffer != '') {
                $a = strpos($buffer, ':');
                if($a) {
                    if(!count($parameters)) {// first line in a response?
                        $type = strtolower(substr($buffer, 0, $a));
                        if(substr($buffer, $a + 2) == 'Follows') {
                            // A 'follows' response means there is a multiline field that follows.
                            $parameters['data'] = '';
                            $buff = fgets($this->socket, 4096);
                            while(substr($buff, 0, 6) != '--END ') {
                                $parameters['data'] .= $buff;
                                $buff = fgets($this->socket, 4096);
                            }
                        }
                    } elseif(count($parameters) == 2) {
                        if(!isset($parameters['Response'])) { $parameters['Response']=''; }
                        if($parameters['Response'] == "Success" && isset($parameters['Message']) && $parameters['Message'] == 'Command output follows') {
                            // A 'Command output follows' response means there is a muiltiline field that follows.
                            // This is Asterisk 14 Logic:
                            $parameters['data'] = "Privilege: Command\n"; //Add this to make Asterisk 14 look/work like < 13
                            $parameters['data'] .= preg_replace("/^Output:\s*/","",$buffer)."\n";
                            $buff = fgets($this->socket, 4096);
                            while($buff !== "\r\n") {
                                $buff = preg_replace("/^Output:\s*/","",$buff);
                                $parameters['data'] .= trim($buff)."\n";
                                $buff = fgets($this->socket, 4096);
                            }
                            break;
                        }
                    }

                    // store parameter in $parameters
                    $parameters[substr($buffer, 0, $a)] = substr($buffer, $a + 2);
                }
                $buffer = trim(fgets($this->socket, 4096));
            }

            // process response
            switch($type) {
                case '': // timeout occured
                    $timeout = $allow_timeout;
                    break;
                case 'event':
                    $this->process_event($parameters);
                    break;
                case 'response':
                case 'message':
                    break;
                default:
                    $this->log('Unhandled response packet ('.$type.') from Manager: ' . print_r($parameters, true));
                    break;
            }
        } while(($return_on_event && ($type != 'event' && $type != 'response' && $type != 'message' && !$timeout)) || (!$return_on_event && ($type != 'response' && $type != 'message' && !$timeout)));
        $this->log("returning from wait_response with with type: $type",10);
        $this->log('$parmaters: '.print_r($parameters,true),10);
        $this->log('$buffer: '.print_r($buffer,true),10);
        if (isset($buff)) {
            $this->log('$buff: '.print_r($buff,true),10);
        }
        return $parameters;
    }

    /**
     * Connect to Asterisk
     *
     * @example examples/sip_show_peer.php Get information about a sip peer
     *
     * @param string $server
     * @param string $username
     * @param string $secret
     * @return boolean true on success
     */
    function connect($server=NULL, $username=NULL, $secret=NULL, $events='on') {
        // use config if not specified
        if(is_null($server)) {
            $server = $this->config['asmanager']['server'];
        }
        $this->username = is_null($username) ? $this->config['asmanager']['username'] : $username;
        $this->secret = is_null($secret) ? $this->config['asmanager']['secret'] : $secret;
        $this->events = $events;

        // get port from server if specified
        if(strpos($server, ':') !== false) {
            $c = explode(':', $server);
            $this->server = $c[0];
            $this->port = $c[1];
        } else {
            $this->server = $server;
            $this->port = $this->config['asmanager']['port'];
        }

        // connect the socket
        $errno = $errstr = NULL;
        $this->socket = @fsockopen($this->server, $this->port, $errno, $errstr);
        if(!$this->socket) {
            $this->log("Unable to connect to manager {$this->server}:{$this->port} ($errno): $errstr");
            return false;
        }

        // read the header
        $str = fgets($this->socket);
        if($str == false) {
            // a problem.
            $this->log("Asterisk Manager header not received.");
            return false;
        } else {
            // note: don't $this->log($str) until someone looks to see why it mangles the logging
        }

        // login
        $res = $this->send_request('login',
                array('Username'=>$this->username, 'Secret'=>$this->secret, 'Events'=>$this->events),
                false);
        if($res['Response'] != 'Success') {
            $this->log("Failed to login.");
            $this->disconnect();
            return false;
        }
        return true;
    }

    /**
     * Disconnect
     *
     * @example examples/sip_show_peer.php Get information about a sip peer
     */
    function disconnect() {
        $this->logoff();
        fclose($this->socket);
    }

    /**
     * Check if the socket is connected
     *
     */
    function connected() {
        return (bool)$this->socket;
    }

    /**
     * Set Absolute Timeout
     *
     * Hangup a channel after a certain time. Acknowledges set time with Timeout Set message.
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_AbsoluteTimeout
     * @version 11
     * @param string $channel
     * @param integer $timeout
     */
    function AbsoluteTimeout($channel, $timeout) {
        return $this->send_request('AbsoluteTimeout', array('Channel'=>$channel, 'Timeout'=>$timeout));
    }

    /**
     * Sets an agent as no longer logged in.
     *
     * Sets an agent as no longer logged in.
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_AgentLogoff
     * @version 11
     * @param string $agent Agent ID of the agent to log off.
     * @param string $soft  Set to true to not hangup existing calls.
     */
    function AgentLogoff($agent, $soft) {
        return $this->send_request('AgentLogoff', array('Agent'=>$agent, 'Soft'=>$soft));
    }

    /**
     * Lists agents and their status.
     *
     * Will list info about all possible agents.
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_Agents
     * @version 11
     */
    function Agents($channel, $actionid=NULL) {
        if ($actionid) {
            $parameters = array('ActionID'=>$actionid);
        }
        return $this->send_request('Agents', $parameters);
    }

    /**
     * Add an AGI command to execute by Async AGI.
     *
     * Add an AGI command to the execute queue of the channel in Async AGI.
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_AGI
     * @param string $channel
     * @param string $file
     */
    function AGI($channel, $command, $commandid) {
        return $this->send_request('AGI', array('Channel'=>$channel, 'Command'=>$command, "CommandID" => $commandid));
    }

    /**
     * Change monitoring filename of a channel
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+ChangeMonitor
     * @param string $channel
     * @param string $file
     */
    function ChangeMonitor($channel, $file) {
        return $this->send_request('ChangeMontior', array('Channel'=>$channel, 'File'=>$file));
    }

    /**
     * Execute Command
     *
     * @example examples/sip_show_peer.php Get information about a sip peer
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+Command
     * @link http://www.voip-info.org/wiki-Asterisk+CLI
     * @param string $command
     * @param string $actionid message matching variable
     */
    function Command($command, $actionid=NULL) {
        $parameters = array('Command'=>$command);
        if($actionid) {
            $parameters['ActionID'] = $actionid;
        }
        return $this->send_request('Command', $parameters);
    }

    /**
     * Kick a Confbridge user.
     *
     * Kick a Confbridge user.
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_ConfbridgeKick
     * @param string $conference Conference number.
     * @param string $channel If this parameter is not a complete channel name, the first channel with this prefix will be used.
     */
    function ConfbridgeKick($conference, $channel) {
        return $this->send_request('ConfbridgeKick', array('Conference'=>$conference, 'Channel'=>$channel));
    }

    /**
     * List Users in a Conference
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_ConfbridgeList
     * @param string $conference Conference number.
     */
    function ConfbridgeList($conference) {
        $this->add_event_handler("confbridgelist", array($this, 'Confbridge_catch'));
        $this->add_event_handler("confbridgelistcomplete", array($this, 'Confbridge_catch'));
        $response = $this->send_request('ConfbridgeList', array('Conference'=>$conference));
        if ($response["Response"] == "Success") {
            $this->response_catch = array();
            $this->wait_response(true);
            stream_set_timeout($this->socket, 30);
        } else {
            return false;
        }
        return $this->response_catch;
    }

    /**
     * List active conferences.
     *
     * Lists data about all active conferences. ConfbridgeListRooms will follow as separate events, followed by a final event called ConfbridgeListRoomsComplete.
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_ConfbridgeListRooms
     */
    function ConfbridgeListRooms() {
        $this->add_event_handler("confbridgelistrooms", array($this, 'Confbridge_catch'));
        $this->add_event_handler("confbridgelistroomscomplete", array($this, 'Confbridge_catch'));
        $response = $this->send_request('ConfbridgeListRooms');
        if ($response["Response"] == "Success") {
            $this->response_catch = array();
            $this->wait_response(true);
            stream_set_timeout($this->socket, 30);
        } else {
            return false;
        }
        return $this->response_catch;
    }

    /**
     * Conference Bridge Event Catch
     *
     * This catches events obtained from the confbridge stream, it should not be used externally
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_ConfbridgeListRooms
     */
    private function Confbridge_catch($event, $data, $server, $port) {
        switch($event) {
            case 'confbridgelistcomplete':
            case 'confbridgelistroomscomplete':
                /* HACK: Force a timeout after we get this event, so that the wait_response() returns. */
                stream_set_timeout($this->socket, 0, 1);
                break;
            case 'confbridgelist':
                $this->response_catch[] =  $data;
                break;
            case 'confbridgelistrooms':
                $this->response_catch[] =  $data;
                break;
        }
    }

    /**
     * Lock a Confbridge conference.
     *
     * Lock a Confbridge conference.
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_ConfbridgeLock
     * @param string $conference Conference number.
     */
    function ConfbridgeLock($conference) {
        return $this->send_request('ConfbridgeLock', array('Conference'=>$conference));
    }

    /**
     * Mute a Confbridge user.
     *
     * Mute a Confbridge user.
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_ConfbridgeMute
     * @param string $conference Conference number.
     * @param string $channel If this parameter is not a complete channel name, the first channel with this prefix will be used.
     */
    function ConfbridgeMute($conference,$channel) {
        return $this->send_request('ConfbridgeMute', array('Conference'=>$conference, 'Channel' => $channel));
    }

    /**
     * Set a conference user as the single video source distributed to all other participants.
     *
     * Set a conference user as the single video source distributed to all other participants.
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_ConfbridgeSetSingleVideoSrc
     * @param string $conference Conference number.
     * @param string $channel If this parameter is not a complete channel name, the first channel with this prefix will be used.
     */
    function ConfbridgeSetSingleVideoSrc($conference,$channel) {
        return $this->send_request('ConfbridgeSetSingleVideoSrc', array('Conference'=>$conference, 'Channel' => $channel));
    }

    /**
     * Start recording a Confbridge conference.
     *
     * Start recording a conference. If recording is already present an error will be returned.
     * If RecordFile is not provided, the default record file specified in the conference's bridge profile will be used, if that is not present either a file will automatically be generated in the monitor directory.
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_ConfbridgeMute
     * @param string $conference Conference number.
     * @param string $channel If this parameter is not a complete channel name, the first channel with this prefix will be used.
     */
    function ConfbridgeStartRecord($conference,$recordFile) {
        return $this->send_request('ConfbridgeStartRecord', array('Conference'=>$conference, 'RecordFile' => $recordFile));
    }

    /**
     * Stop recording a Confbridge conference.
     *
     * Stop recording a Confbridge conference.
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_ConfbridgeStopRecord
     * @param string $conference Conference number.
     */
    function ConfbridgeStopRecord($conference) {
        return $this->send_request('ConfbridgeStopRecord', array('Conference'=>$conference));
    }

    /**
     * Unlock a Confbridge conference.
     *
     * Unlock a Confbridge conference.
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_ConfbridgeUnlock
     * @param string $conference Conference number.
     */
    function ConfbridgeUnlock($conference) {
        return $this->send_request('ConfbridgeUnlock', array('Conference'=>$conference));
    }

    /**
     * Unmute a Confbridge user.
     *
     * Unmute a Confbridge user.
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_ConfbridgeUnmute
     * @param string $conference Conference number.
     */
    function ConfbridgeUnmute($conference) {
        return $this->send_request('ConfbridgeUnmute', array('Conference'=>$conference));
    }

    /**
     * Enable/Disable sending of events to this manager
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+Events
     * @param string $eventmask is either 'on', 'off', or 'system,call,log'
     */
    function Events($eventmask) {
        $this->events = $eventmask;
        return $this->send_request('Events', array('EventMask'=>$eventmask));
    }

    /**
     * Check Extension Status
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+ExtensionState
     * @param string $exten Extension to check state on
     * @param string $context Context for extension
     * @param string $actionid message matching variable
     */
    function ExtensionState($exten, $context, $actionid = NULL) {
        $parameters = array('Exten'=>$exten, 'Context'=>$context);
        if($actionid) {
            $parameters['ActionID'] = $actionid;
        }
        return $this->send_request('ExtensionState', $parameters);
    }

    /**
     * Gets a Channel Variable
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+GetVar
     * @param string $channel  Channel to read variable from
     * @param string $variable
     * @param string $actionid message matching variable
     */
    function GetVar($channel, $variable, $actionid=NULL) {
        $parameters = array('Channel'=>$channel, 'Variable'=>$variable);
        if($actionid) {
            $parameters['ActionID'] = $actionid;
        }
        return $this->send_request('GetVar', $parameters);
    }

    /**
     * Hangup Channel
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+Hangup
     * @param string $channel The channel name to be hungup
     */
    function Hangup($channel) {
        return $this->send_request('Hangup', array('Channel'=>$channel));
    }

    /**
     * List IAX Peers
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+IAXpeers
     */
    function IAXPeers() {
        return $this->send_request('IAXPeers');
    }

    function PresenceState($provider) {
        return $this->send_request('PresenceState',array('Provider'=>$provider));
    }

    /**
     * List available manager commands
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+ListCommands
     * @param string $actionid message matching variable
     */
    function ListCommands($actionid=NULL) {
        if($actionid) {
            return $this->send_request('ListCommands', array('ActionID'=>$actionid));
        } else {
            return $this->send_request('ListCommands');
        }
    }

    /**
     * Logoff Manager
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+Logoff
     */
    function Logoff() {
        return $this->send_request('Logoff',array(),false);
    }

    /**
     * Check Mailbox Message Count
     *
     * Returns number of new and old messages.
     *   Message: Mailbox Message Count
     *   Mailbox: <mailboxid>
     *   NewMessages: <count>
     *   OldMessages: <count>
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+MailboxCount
     * @param string $mailbox Full mailbox ID <mailbox>@<vm-context>
     */
    function MailboxCount($mailbox, $actionid=NULL) {
        $parameters = array('Mailbox'=>$mailbox);
        if($actionid) {
            $parameters['ActionID'] = $actionid;
        }
        return $this->send_request('MailboxCount', $parameters);
    }

    /**
     * Check Mailbox
     *
     * Returns number of messages.
     *   Message: Mailbox Status
     *   Mailbox: <mailboxid>
     *   Waiting: <count>
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+MailboxStatus
     * @param string $mailbox Full mailbox ID <mailbox>@<vm-context>
     * @param string $actionid message matching variable
     */
    function MailboxStatus($mailbox, $actionid=NULL) {
        $parameters = array('Mailbox'=>$mailbox);
        if($actionid) {
            $parameters['ActionID'] = $actionid;
        }
        return $this->send_request('MailboxStatus', $parameters);
    }

    /**
     * List participants in a conference.
     *
     * Lists all users in a particular MeetMe conference. MeetmeList will follow as separate events, followed by a final event called MeetmeListComplete.
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_MeetmeList
     * @param string $conference Conference number.
     */
    function MeetmeList($conference) {
        return $this->send_request('MeetmeList', array('Conference'=>$conference));
    }

    /**
     * List active conferences.
     *
     * Lists data about all active conferences. MeetmeListRooms will follow as separate events, followed by a final event called MeetmeListRoomsComplete.
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_ConfbridgeListRooms
     */
    function MeetmeListRooms() {
        return $this->send_request('MeetmeListRooms');
    }

    /**
     * Mute a Meetme user.
     *
     * Mute a Meetme user.
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_MeetmeMute
     * @param string $meetme Conference number.
     * @param string $usernum User Number
     */
    function MeetmeMute($meetme,$usernum) {
        return $this->send_request('MeetmeMute', array('Meetme'=>$meetme,'Usernum'=>$usernum));
    }

    /**
     * Unmute a Meetme user.
     *
     * Unmute a Meetme user.
     *
     * @link https://wiki.asterisk.org/wiki/display/AST/Asterisk+11+ManagerAction_MeetmeUnmute
     * @param string $meetme Conference number.
     * @param string $usernum User Number
     */
    function MeetmeUnmute($meetme,$usernum) {
        return $this->send_request('MeetmeUnmute', array('Meetme'=>$meetme,'Usernum'=>$usernum));
    }

    /**
     * Monitor a channel
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+Monitor
     * @param string $channel
     * @param string $file
     * @param string $format
     * @param boolean $mix
     */
    function Monitor($channel, $file=NULL, $format=NULL, $mix=NULL) {
        $parameters = array('Channel'=>$channel);
        if($file) {
            $parameters['File'] = $file;
        }
        if($format) {
            $parameters['Format'] = $format;
        }
        if(!is_null($file)) {
            $parameters['Mix'] = ($mix) ? 'true' : 'false';
        }
        return $this->send_request('Monitor', $parameters);
    }

    /**
     * Originate Call
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+Originate
     * @param string $channel
     * @param string $exten
     * @param string $context
     * @param string $priority
     * @param integer $timeout
     * @param string $callerid
     * @param string $variable
     * @param string $account
     * @param string $application
     * @param string $data
     * == exactly 11 values required ==
     *
     * -- OR --
     *
     * @pram array a key => value array of what ever you want to pass in
     */
    function Originate() {
        $num_args = func_num_args();

        if ($num_args === 10) {
            $args = func_get_args();

            $parameters = array();
            if ($args[0]) {
                $parameters['Channel'] = $args[0];
            }
            if ($args[1]) {
                $parameters['Exten'] = $args[1];
            }
            if ($args[2]) {
                $parameters['Context'] = $args[2];
            }
            if ($args[3]) {
                $parameters['Priority'] = $args[3];
            }
            if ($args[4]) {
                $parameters['Timeout'] = $args[4];
            }
            if ($args[5]) {
                $parameters['CallerID'] = $args[5];
            }
            if ($args[6]) {
                $parameters['Variable'] = $args[6];
            }
            if ($args[7]) {
                $parameters['Account'] = $args[7];
            }
            if ($args[8]) {
                $parameters['Application'] = $args[8];
            }
            if ($args[9]) {
                $parameters['Data'] = $args[9];
            }
        } else {
            $args = func_get_args();
            $args = $args[0];
            foreach ($args as $key => $val) {
                $parameters[$key] = $val;
            }
        }

        return $this->send_request('Originate', $parameters);
    }

    /**
     * List parked calls
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+ParkedCalls
     */
    function ParkedCalls($actionid=NULL) {
        if($actionid) {
            return $this->send_request('ParkedCalls', array('ActionID'=>$actionid));
        } else {
            return $this->send_request('ParkedCalls');
        }
    }

    /**
     * Ping
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+Ping
     */
    function Ping() {
        return $this->send_request('Ping');
    }

    /**
     * Queue Add
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+QueueAdd
     * @param string $queue
     * @param string $interface
     * @param integer $penalty
     */
    function QueueAdd($queue, $interface, $penalty=0) {
        $parameters = array('Queue'=>$queue, 'Interface'=>$interface);
        if($penalty) {
            $parameters['Penalty'] = $penalty;
        }
        return $this->send_request('QueueAdd', $parameters);
    }

    /**
     * Queue Remove
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+QueueRemove
     * @param string $queue
     * @param string $interface
     */
    function QueueRemove($queue, $interface) {
        return $this->send_request('QueueRemove', array('Queue'=>$queue, 'Interface'=>$interface));
    }
    /**
     * Queues
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+Queues
     */
    function Queues() {
        return $this->send_request('Queues');
    }

    /**
     * Queue Status
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+QueueStatus
     * @param string $actionid message matching variable
     */
    function QueueStatus($actionid=NULL) {
        if($actionid) {
            return $this->send_request('QueueStatus', array('ActionID'=>$actionid));
        } else {
            return $this->send_request('QueueStatus');
        }
    }

    /**
     * Redirect
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+Redirect
     * @param string $channel
     * @param string $extrachannel
     * @param string $exten
     * @param string $context
     * @param string $priority
     */
    function Redirect($channel, $extrachannel, $exten, $context, $priority) {
        return $this->send_request('Redirect', array('Channel'=>$channel, 'ExtraChannel'=>$extrachannel, 'Exten'=>$exten,
                    'Context'=>$context, 'Priority'=>$priority));
    }

    /**
     * Set the CDR UserField
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+SetCDRUserField
     * @param string $userfield
     * @param string $channel
     * @param string $append
     */
    function SetCDRUserField($userfield, $channel, $append=NULL) {
        $parameters = array('UserField'=>$userfield, 'Channel'=>$channel);
        if($append) {
            $parameters['Append'] = $append;
        }
        return $this->send_request('SetCDRUserField', $parameters);
    }

    /**
     * Set Channel Variable
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+SetVar
     * @param string $channel Channel to set variable for
     * @param string $variable name
     * @param string $value
     */
    function SetVar($channel, $variable, $value) {
        return $this->send_request('SetVar', array('Channel'=>$channel, 'Variable'=>$variable, 'Value'=>$value));
    }

    /**
     * List SIP Peers
     */
    function SIPpeers() {
        // XXX need to look at source to find this function...
        return $this->send_request('SIPpeers');
    }

    /* Channel Status
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+Status
     * @param string $channel
     * @param string $actionid message matching variable
     */
    function Status($channel, $actionid=NULL) {
        $parameters = array('Channel'=>$channel);
        if ($actionid) {
            $parameters['ActionID'] = $actionid;
        }
        return $this->send_request('Status', $parameters);
    }

    /**
     * Stop monitoring a channel
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+StopMonitor
     * @param string $channel
     */
    function StopMonitor($channel) {
        return $this->send_request('StopMonitor', array('Channel'=>$channel));
    }

    /**
     * Dial over Zap channel while offhook
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+ZapDialOffhook
     * @param string $zapchannel
     * @param string $number
     */
    function ZapDialOffhook($zapchannel = '', $number = '') {
        // XXX need to look at source to find this function...
        if ($zapchannel && $number) {
            return $this->send_request('ZapDialOffhook', array('ZapChannel'=>$zapchannel, 'Number'=>$number));
        } else {
            return $this->send_request('ZapDialOffhook');
        }

    }

    /**
     * Toggle Zap channel Do Not Disturb status OFF
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+ZapDNDoff
     * @param string $zapchannel
     */
    function ZapDNDoff($zapchannel = '') {
        // XXX need to look at source to find this function...
        if ($zapchannel) {
            return $this->send_request('ZapDNDoff', array('ZapChannel'=>$zapchannel));
        } else {
            return $this->send_request('ZapDNDoff');
        }
    }

    /**
     * Toggle Zap channel Do Not Disturb status ON
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+ZapDNDon
     * @param string $zapchannel
     */
    function ZapDNDon($zapchannel = '') {
        // XXX need to look at source to find this function...
        if ($zapchannel) {
            return $this->send_request('ZapDNDon', array('ZapChannel'=>$zapchannel));
        } else {
            return $this->send_request('ZapDNDon');
        }
    }

    /**
     * Hangup Zap Channel
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+ZapHangup
     * @param string $zapchannel
     */
    function ZapHangup($zapchannel = '') {
        // XXX need to look at source to find this function...
        if ($zapchannel) {
            return $this->send_request('ZapHangup', array('ZapChannel'=>$zapchannel));
        } else {
            return $this->send_request('ZapHangup');
        }
    }

    /**
     * Transfer Zap Channel
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+ZapTransfer
     * @param string $zapchannel
     */
    function ZapTransfer($zapchannel = '') {
        // XXX need to look at source to find this function...
        if ($zapchannel) {
            return $this->send_request('ZapTransfer', array('ZapChannel'=>$zapchannel));
        } else {
            return $this->send_request('ZapTransfer');
        }
    }
    /**
     * Zap Show Channels
     *
     * @link http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+ZapShowChannels
     * @param string $actionid message matching variable
     */
    function ZapShowChannels($actionid=NULL) {
        if($actionid) {
            return $this->send_request('ZapShowChannels', array('ActionID'=>$actionid));
        } else {
            return $this->send_request('ZapShowChannels');
        }
    }

    /*
     * Log a message
     *
     * @param string $message
     * @param integer $level from 1 to 4
     */
    function log($message, $level = 1) {
        if($this->pagi != false) {
            $this->pagi->conlog($message, $level);
        } elseif ($this->log_level === false && $level <= $this->log_level) {
            if (function_exists('dbug')) {
                dbug('php-asmanager log', $message);
            } else {
                error_log(date('r') . ' - ' . $message);
            }
        }
    }

    /**
     * Add event handler
     *
     * Known Events include ( http://www.voip-info.org/wiki-asterisk+manager+events )
     *   Link - Fired when two voice channels are linked together and voice data exchange commences.
     *   Unlink - Fired when a link between two voice channels is discontinued, for example, just before call completion.
     *   Newexten -
     *   Hangup -
     *   Newchannel -
     *   Newstate -
     *   Reload - Fired when the "RELOAD" console command is executed.
     *   Shutdown -
     *   ExtensionStatus -
     *   Rename -
     *   Newcallerid -
     *   Alarm -
     *   AlarmClear -
     *   Agentcallbacklogoff -
     *   Agentcallbacklogin -
     *   Agentlogoff -
     *   MeetmeJoin -
     *   MessageWaiting -
     *   join -
     *   leave -
     *   AgentCalled -
     *   ParkedCall - Fired after ParkedCalls
     *   Cdr -
     *   ParkedCallsComplete -
     *   QueueParams -
     *   QueueMember -
     *   QueueStatusEnd -
     *   Status -
     *   StatusComplete -
     *   ZapShowChannels -  Fired after ZapShowChannels
     *   ZapShowChannelsComplete -
     *
     * @param string $event type or * for default handler
     * @param string $callback function
     * @return boolean sucess
     */
    function add_event_handler($event, $callback) {
        $event = strtolower($event);
        $this->event_handlers[$event][] = $callback;
        return true;
    }

    /**
     * Process event
     *
     * @access private
     * @param array $parameters
     * @return mixed result of event handler or false if no handler was found
     */
    function process_event($parameters) {
        $ret = false;
        $handlers = array();
        $e = strtolower($parameters['Event']);
        $this->log("Got event... $e");

        if(isset($this->event_handlers[$e])) {
            $handlers = array_merge($handlers, $this->event_handlers[$e]);
        }
        if(isset($this->event_handlers['*'])) {
            $handlers = array_merge($handlers, $this->event_handlers['*']);
        }

        foreach ($handlers as $handler) {
            if(is_callable($handler)){
                if (is_array($handler)) {
                    $this->log('Execute handler ' . get_class($handler[0]) . '::' . $handler[1]);
                    $ret = $handler[0]->$handler[1]($e, $parameters, $this->server, $this->port);
                } else {
                    $this->log("Execute handler $handler");
                    $ret = $handler($e, $parameters, $this->server, $this->port);
                }
            }
        }


        return $ret;
    }

    /** Show all entries in the asterisk database
     * @return Array associative array of key=>value
     */
    function database_show($family='') {
        if ($this->useCaching && $this->memAstDB != null) {
            if ($family == '') {
                return $this->memAstDB;
            } else {
                $key = '/'.$family;
                if (isset($this->memAstDB[$key])) {
                    return array($key => $this->memAstDB[$key]);
                } else {
                    $key .= '/';
                    $len = strlen($key);
                    $fam_arr = array();
                    foreach ($this->memAstDB as $this_key => $value) {
                        if (substr($this_key,0,$len) ==  $key) {
                            $fam_arr[$this_key] = $value;
                        }
                    }
                    return $fam_arr;
                }
            }
        }
        $r = $this->command("database show $family");

        $data = explode("\n",$r["data"]);
        $db = array();

        // Remove the Privilege => Command initial entry that comes from the heading
        //
        array_shift($data);
        foreach ($data as $line) {
            $temp = explode(":",$line,2);
            if (trim($temp[0]) != '' && count($temp) == 2) {
                $temp[1] = isset($temp[1])?$temp[1]:null;
                $db[ trim($temp[0]) ] = trim($temp[1]);
            }
        }
        return $db;
    }

    /** Add an entry to the asterisk database
     * @param string $family    The family name to use
     * @param string $key        The key name to use
     * @param mixed $value        The value to add
     * @return bool True if successful
     */
    function database_put($family, $key, $value) {
        $value = (trim($value) == '')?'"'.$value.'"':$value;
        $r = $this->command("database put ".str_replace(" ","/",$family)." ".str_replace(" ","/",$key)." ".$value);
        if (!empty($this->memAstDB)){
            $keyUsed="/".str_replace(" ","/",$family)."/".str_replace(" ","/",$key);
            $this->memAstDB[$keyUsed] = $value;
        }
        return (bool)strstr($r["data"], "success");
    }

    /** Get an entry from the asterisk database
     * @param string $family    The family name to use
     * @param string $key        The key name to use
     * @return mixed Value of the key, or false if error
     */
    function database_get($family, $key) {
        if ($this->useCaching) {
            if ($this->memAstDB == null) {
                $this->LoadAstDB();
            }
            $keyUsed="/".str_replace(" ","/",$family)."/".str_replace(" ","/",$key);
            if (array_key_exists($keyUsed,$this->memAstDB)){
                return $this->memAstDB[$keyUsed];
            }
        } else {
            $r = $this->command("database get ".str_replace(" ","/",$family)." ".str_replace(" ","/",$key));
            $data = strpos($r["data"],"Value:");
            if ($data !== false) {
                return trim(substr($r["data"],6+$data));
            }
        }
        return false;
    }


    /** Delete an entry from the asterisk database
     * @param string $family    The family name to use
     * @param string $key        The key name to use
     * @return bool True if successful
     */
    function database_del($family, $key) {
        if (!empty($this->memAstDB)){
            $keyUsed="/".str_replace(" ","/",$family)."/".str_replace(" ","/",$key);
            unset($this->memAstDB[$keyUsed]);
        }
        $r = $this->command("database del ".str_replace(" ","/",$family)." ".str_replace(" ","/",$key));
        return (bool)strstr($r["data"], "removed");
    }

    /** Delete a family from the asterisk database
     * @param string $family    The family name to use
     * @return bool True if successful
     */
    function database_deltree($family) {
        if (!empty($this->memAstDB)){
            $keyUsed="/".str_replace(" ","/",$family);
            unset($this->memAstDB[$keyUsed]);
        }
        $r = $this->command("database deltree ".str_replace(" ","/",$family));
        return (bool)strstr($r["data"], "removed");
    }

    /** Returns whether a give function exists in this Asterisk install
     * @param string $func    The case sensitve name of the function
     * @return bool True if if it exists
     */
    function func_exists($func) {
        $r = $this->command("core show function $func");
        return (strpos($r['data'],"No function by that name registered") === false);
    }

    /** Returns whether a give application exists in this Asterisk install
     * @param string $app    The case in-sensitve name of the application
     * @return bool True if if it exists
     */
    function app_exists($app) {
        $r = $this->command("core show applications like $app");
        return (strpos($r['data'],"0 Applications Matching") === false);
    }

    /** Returns whether a give channeltype exists in this Asterisk install
     * @param string $channel    The case in-sensitve name of the channel
     * @return bool True if if it exists
     */
    function chan_exists($channel) {
        $r = $this->command("core show channeltype $channel");
        return (strpos($r['data'],"is not a registered channel driver") === false);
    }

    /** Returns whether a give asterisk module is loaded in this Asterisk install
     * @param string $app    The case in-sensitve name of the application
     * @return bool True if if it exists
     */
    function mod_loaded($mod) {
        $r = $this->command("module show like $mod");
        return (preg_match('/1 modules loaded/', $r['data']) > 0);
    }

    /** Sets a global var or function to the provided value
     * @param string $var    The variable or function to set
     * @param string $val    the value to set it to
     * @return array returns the array value from the send_request
     */
    function set_global($var, $val) {
        global $amp_conf;
        static $pre = '';

        if (! $pre) {
            $pre = version_compare($amp_conf['ASTVERSION'], "1.6.1", "ge") ? 'dialplan' : 'core';
        }
        return $this->command($pre . ' set global ' . $var . ' ' . $val);
    }

    /**
     * Reload module(s)
     *
     * @link http://www.voip-info.org/wiki/view/Asterisk+Manager+API+Action+Reload
     * @param string $module
     * @param string $actionid
     */
    function Reload($module=NULL, $actionid=NULL) {
        $parameters = array();

        if ($actionid) {
            $parameters['ActionID'] = $actionid;
        }
        if ($module) {
            $parameters['Module'] = $module;
        }
        return $this->send_request('Reload', $parameters);
    }

    /** Starts mixmonitor
     * @param string $channel    The channel to start recording
     * @param string $file The file to record to
     * @param string $options Options to pass to mixmonitor
     * @param string $postcommand Command to execute after recording
     * @param string $actionid message matching variable
     *
     * @return array returns the array value from the send_request
     */
    function mixmonitor($channel, $file, $options='', $postcommand='', $actionid=NULL) {
        if (!$channel || !$file) {
            return false;
        }
        $args = 'mixmonitor start ' . trim($channel) . ' ' . trim($file);
        if ($options || $postcommand) {
            $args .= ',' . trim($options);
        }
        if ($postcommand) {
            $args .= ',' . trim($postcommand);
        }
        return $this->command($args, $actionid);
    }

    /** Stops mixmonitor
     * @param string $channel    The channel to stop recording
     * @param string $actionid message matching variable
     *
     * @return array returns the array value from the send_request
     */
    function stopmixmonitor($channel, $actionid=NULL) {
        if (!$channel) {
            return false;
        }
        $args = 'mixmonitor stop ' . trim($channel);
        return $this->command($args, $actionid);
    }
}
