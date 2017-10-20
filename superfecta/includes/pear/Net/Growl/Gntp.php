<?php
/**
 * Copyright (c) 2009-2012, Laurent Laville <pear@laurent-laville.org>
 *                          Bertrand Mansion <bmansion@mamasam.com>
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the authors nor the names of its contributors
 *       may be used to endorse or promote products derived from this software
 *       without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * PHP version 5
 *
 * @category Networking
 * @package  Net_Growl
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @author   Bertrand Mansion <bmansion@mamasam.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  SVN: $Id: Gntp.php 324862 2012-04-05 14:34:09Z farell $
 * @link     http://growl.laurent-laville.org/
 * @link     http://pear.php.net/package/Net_Growl
 * @since    File available since Release 0.9.0
 */
 
/**
 * Growl implements GNTP 1.0 protocol
 *
 * @category Networking
 * @package  Net_Growl
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @author   Bertrand Mansion <bmansion@mamasam.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  Release: 2.6.0
 * @link     http://growl.laurent-laville.org/
 * @link     http://pear.php.net/package/Net_Growl
 * @link     http://www.growlforwindows.com/gfw/ Growl for Windows Homepage
 * @since    Class available since Release 0.9.0
 */
class Net_Growl_Gntp extends Net_Growl
{
    /**
     * Password hash alorithms supported by Gfw 2.0
     * @var array
     */
    private $_passwordHashAlgorithm = array('md5', 'sha1', 'sha256', 'sha512');

    /**
     * Class constructor
     *
     * @param mixed  &$application  Can be either a Net_Growl_Application object
     *                              or the application name string
     * @param array  $notifications List of notification types
     * @param string $password      (optional) Password for Growl
     * @param array  $options       (optional) List of options : 'host', 'port',
     *                              'protocol', 'timeout' for Growl socket server.
     *                              'passwordHashAlgorithm', 'encryptionAlgorithm'
     *                              to secure communications.
     *                              'debug' to know what data are sent and received.
     */
    public function __construct(&$application, $notifications = array(),
        $password = '', $options = array()
    ) {
        parent::__construct($application, $notifications, $password, $options);
    }

    /**
     * Sends the REGISTER message type
     *
     * @return Net_Growl_Response
     * @throws Net_Growl_Exception if remote server communication failure
     */
    public function sendRegister()
    {
        $binaries      = array();
        $growl_logo    = self::getDefaultGrowlIcon();
        $growl_logo_id = md5($growl_logo);

        // Application-Name: <string>
        // Required - The name of the application that is registering
        $data = "Application-Name: "
              .  $this->utf8Encode($this->getApplication()->getGrowlName())
              .  "\r\n";

        // Application-Icon: <url> | <uniqueid>
        // Optional - The icon of the application
        $icon  = $this->getApplication()->getGrowlIcon();
        if (!empty($icon)) {
            $fp = @fopen($icon, 'rb');
            if ($fp === false) {
                $this->debug("Invalid Application Icon URL '$icon'", 'warning');
                // invalid Application Icon URL; force to use default growl logo
                $icon = '';
            } else {
                fclose($fp);
            }
        }
        if (empty($icon)) {
            $icon = "x-growl-resource://" . $growl_logo_id;
            $binaries[] = $growl_logo_id;
        }
        $data .= "Application-Icon: " . $icon . "\r\n";

        // Notifications-Count: <int>
        // Required - The number of notifications being registered
        $notifications = $this->getApplication()->getGrowlNotifications();
        $data .= "Notifications-Count: " . count($notifications) . "\r\n";

        foreach ($notifications as $name => $options) {
            $data .= "\r\n";

            // Notification-Name: <string>
            // Required - The name (type) of the notification being registered
            $data .= "Notification-Name: " . $this->utf8Encode($name) . "\r\n";

            // Notification-Display-Name: <string>
            // Optional - The name of the notification that is displayed to the user
            // (defaults to the same value as Notification-Name)
            if (is_array($options) && isset($options['display'])) {
                $data .= "Notification-Display-Name: "
                      .  $options['display']
                      .  "\r\n";
            }

            // Notification-Enabled: <boolean>
            // Optional - Indicates if the notification should be enabled by default
            // (defaults to False)
            if (is_array($options) && isset($options['enabled'])) {
                $data .= "Notification-Enabled: "
                      .  $this->_toBool($options['enabled'])
                      .  "\r\n";
            }

            // Notification-Icon: <url> | <uniqueid>
            // Optional - The default icon to use for notifications of this type
            if (is_array($options) && isset($options['icon'])) {
                $icon = $options['icon'];
                $fp = @fopen($icon, 'rb');
                if ($fp === false) {
                    $this->debug("Invalid Notification Icon URL '$icon'", 'warning');
                    // invalid Notification Icon URL; force to use default growl logo
                    $icon = '';
                } else {
                    fclose($fp);
                }
                if (empty($icon)) {
                    $icon = "x-growl-resource://" . $growl_logo_id;
                    $binaries[] = $growl_logo_id;
                }
                $data .= "Notification-Icon: " . $icon . "\r\n";
            }
        }

        $meth = 'REGISTER';
        $crypt_algorithm = strtolower($this->options['encryptionAlgorithm']);
        if ($crypt_algorithm != 'none') {
            // add extra CRLF to header before encryption to fix Gfw 2.0.21 problem
            $data .= "\r\n";
        }
        $data = $this->genMessageStructure($meth, $data);
        if ($crypt_algorithm != 'none') {
            // add extra CRLF to header before encryption to fix Gfw 2.0.21 problem
            $data .= "\r\n";
        }

        // binary section
        foreach ($binaries as $bin) {
            $res = $this->genMessageStructure($meth, $growl_logo, true);

            $data .= "\r\n";
            $data .= "Identifier: " . $bin . "\r\n";
            $data .= "Length: " . strlen($res) . "\r\n";
            $data .= "\r\n";
            $data .= $res;
        }

        // message termination
        // A GNTP request must end with <CRLF><CRLF> (two blank lines)
        $data .= "\r\n";
        $data .= "\r\n";

        return $this->sendRequest($meth, $data);
    }

    /**
     * Sends the NOTIFY message type
     *
     * @param string $name        Notification name
     * @param string $title       Notification title
     * @param string $description Notification description
     * @param string $options     Notification options
     *
     * @return Net_Growl_Response
     * @throws Net_Growl_Exception if remote server communication failure
     */
    public function sendNotify($name, $title, $description, $options)
    {
        $appName     = $this->utf8Encode($this->getApplication()->getGrowlName());
        $name        = $this->utf8Encode($name);
        $title       = $this->utf8Encode($title);
        $description = $this->utf8Encode($description);
        $priority    = isset($options['priority'])
            ? $options['priority'] : self::PRIORITY_NORMAL;
        $icon        = isset($options['icon']) ? $options['icon'] : '';

        if (!empty($icon)) {
            // check if valid icon URL
            $fp = @fopen($icon, 'rb');
            if ($fp === false) {
                $this->debug("Invalid Notification Icon URL '$icon'", 'warning');
                $icon = '';
            } else {
                fclose($fp);
            }
        }

        // Application-Name: <string>
        // Required - The name of the application that sending the notification
        // (must match a previously registered application)
        $data = "Application-Name: " . $appName . "\r\n";

        // Notification-Name: <string>
        // Required - The name (type) of the notification (must match a previously
        // registered notification name registered by the application specified
        // in Application-Name)
        $data .= "Notification-Name: " . $name . "\r\n";

        // Notification-Title: <string>
        // Required - The notification's title
        $data .= "Notification-Title: " . $title . "\r\n";

        // Notification-Text: <string>
        // Optional - The notification's text. (defaults to "")
        $data .= "Notification-Text: " . $description . "\r\n";

        // Notification-Priority: <int>
        // Optional - A higher number indicates a higher priority.
        // This is a display hint for the receiver which may be ignored.
        $data .= "Notification-Priority: " . $priority . "\r\n";

        if (!empty($icon)) {
            // Notification-Icon: <url>
            // Optional - The icon to display with the notification.
            $data .= "Notification-Icon: " . $icon . "\r\n";
        }

        // Notification-Sticky: <boolean>
        // Optional - Indicates if the notification should remain displayed
        // until dismissed by the user. (default to False)
        if (is_array($options) && isset($options['sticky'])) {
            $sticky = $options['sticky'];
            $data .= "Notification-Sticky: " .  $this->_toBool($sticky) .  "\r\n";
        }

        // Notification-ID: <string>
        // Optional - A unique ID for the notification. If present, serves as a hint
        // to the notification system that this notification should replace any
        // existing on-screen notification with the same ID. This can be used
        // to update an existing notification.
        // The notification system may ignore this hint.
        if (is_array($options) && isset($options['ID'])) {
            $data .= "Notification-ID: " .  $options['ID'] .  "\r\n";
        }

        // Notification-Callback-Context: <string>
        // Optional - Any data (will be passed back in the callback unmodified)

        // Notification-Callback-Context-Type: <string>
        // Optional, but Required if 'Notification-Callback-Context' is passed.
        // The type of data being passed in Notification-Callback-Context
        // (will be passed back in the callback unmodified). This does not need
        // to be of any pre-defined type, it is only a convenience
        // to the sending application.
        if (is_array($options)
            && (isset($options['CallbackContext'])
            || isset($options['CallbackTarget']))
        ) {
            $data .= "Notification-Callback-Context: "
                  .  $options['CallbackContext']
                  .  "\r\n";
            $data .= "Notification-Callback-Context-Type: "
                  .  $options['CallbackContextType']
                  .  "\r\n";
            $callback = true;
        } else {
            $callback = false;
        }

        // Notification-Callback-Target: <string>
        // Optional - An alternate target for callbacks from this notification.
        // If passed, the standard behavior of performing the callback over the
        // original socket will be ignored and the callback data will be passed
        // to this target instead.
        if (is_array($options)
            && isset($options['CallbackTarget'])
        ) {
            $query = '';
            if (is_array($options) && isset($options['ID'])) {
                $query .= '&NotificationID='
                       .  urlencode($options['ID']);
            }
            if (is_array($options) && isset($options['ID'])) {
                $query .= '&NotificationContext='
                       .  urlencode($options['CallbackContext']);
            }

            $callbackTarget = $options['CallbackTarget'] ;

            if (strpos($options['CallbackTarget'], '?') === false) {
                $callbackTarget .= '?' . substr($query, 1);
            } else {
                $callbackTarget .= $query;
            }

            // BOTH methods are provided here for GfW compatibility.
            $data .= "Notification-Callback-Context-Target: "
                  .  $callbackTarget
                  .  "\r\n";
            // header kept for compatibility - @todo remove on final version
            $data .= "Notification-Callback-Context-Target-Method: GET \r\n";

            // Only those ones should be keep on final version
            $data .= "Notification-Callback-Target: "
                  .  $callbackTarget
                  .  "\r\n";
            // header kept for compatibility - @todo remove on final version
            $data .= "Notification-Callback-Target-Method: GET \r\n";
        }

        $meth = 'NOTIFY';
        $data = $this->genMessageStructure($meth, $data);

        // message termination
        // A GNTP request must end with <CRLF><CRLF> (two blank lines)
        $data .= "\r\n";
        $data .= "\r\n";

        $res = $this->sendRequest($meth, $data, $callback);
        if ($res
            && is_array($options) && isset($options['CallbackFunction'])
            && is_callable($options['CallbackFunction'])
        ) {
            // handle Socket Callbacks
            call_user_func_array(
                $options['CallbackFunction'],
                $this->growlNotificationCallback
            );
        }
        return $res;
    }

    /**
     * Generates full message structure (header + body).
     *
     * @param string $method   Identifies the type of message
     * @param string $data     Request message type data
     * @param bool   $binaries (optional) Do not encrypt binary data header
     *
     * @return string
     */
    protected function genMessageStructure($method, $data, $binaries = false)
    {
        static $keys;

        if ($binaries === false) {
            $data = 'X-Sender: '
                . 'PEAR/Net_Growl ' . Net_Growl::VERSION
                . ' PHP ' . phpversion()
                . "\r\n"
                . $data;
        }

        $password = $this->getApplication()->getGrowlPassword();
        $req      = '';

        if (empty($password)) {
            if ($binaries === false) {
                $req = "GNTP/1.0 $method NONE\r\n";
            }
            $cipherText = $data;
        } else {
            if (!isset($keys)) {
                $password = $this->utf8Encode($password);
                $keys     = $this->_genKey($password);
            }
            list($hash, $key)         = $keys;
            list($crypt, $cipherText) = $this->_genEncryption($key, $data);
            if ($binaries === false) {
                $req = "GNTP/1.0 $method $crypt $hash\r\n";
            }
        }
        $req .= $cipherText;

        return $req;
    }

    /**
     * Generates Security Header message part.
     *
     * The authorization of messages is accomplished by passing key information
     * that proves that the sending application knows a shared secret with the
     * notification system, namely a password. Users that want to authorize
     * applications must share with them a password that will be used for both
     * authorization and encryption.
     *
     * Note: By default, authorization is not required for requests orginating
     *       on the local machine.
     *
     * @param string $password Both client and server should know the password
     *
     * @return array
     * @throws Net_Growl_Exception on wrong password hash algorithm
     */
    private function _genKey($password)
    {
        $hash_algorithm = strtolower($this->options['passwordHashAlgorithm']);
        if ($hash_algorithm == 'none') {
            return array('NONE', '');
        }
        if (!in_array($hash_algorithm, hash_algos())) {
            // Hash algo unknown
            $message = 'Password hash algorithm not supported by php Mcrypt.';
            throw new Net_Growl_Exception($message);
        }
        if (!in_array($hash_algorithm, $this->_passwordHashAlgorithm)) {
            // Hash algo incompatible with Gfw 2.0
            $message = 'Password hash algorithm is not compatible with Gfw 2.0';
            throw new Net_Growl_Exception($message);
        }
        $saltVal   = mt_rand(268435456, mt_getrandmax());
        $saltHex   = dechex($saltVal);
        $saltBytes = pack("H*", $saltHex);

        $passHex   = bin2hex($password);
        $passBytes = pack("H*", $passHex);
        $keyBasis  = $passBytes . $saltBytes;

        $key     = hash($hash_algorithm, $keyBasis, true);
        $keyHash = hash($hash_algorithm, $key);

        return array(strtoupper("$hash_algorithm:$keyHash.$saltHex"), $key);
    }

    /**
     * Generates Encryption Header message part.
     *
     * @param string $key       Key generated from the password and salt
     * @param string $plainText Request message type data
     *
     * @return array
     * @throws Net_Growl_Exception on wrong hash/crypt algorithms usage
     */
    private function _genEncryption($key, $plainText)
    {
        static $ivVal;

        $hash_algorithm  = strtolower($this->options['passwordHashAlgorithm']);
        $crypt_algorithm = strtolower($this->options['encryptionAlgorithm']);
        $crypt_mode      = MCRYPT_MODE_CBC;

        $k = array_search($hash_algorithm, $this->_passwordHashAlgorithm);

        switch ($crypt_algorithm) {
        case 'aes':
            if ($k < 2) {
                $message = "Password hash ($hash_algorithm)"
                        .  " and encryption ($crypt_algorithm) algorithms"
                        .  " are not compatible."
                        .  " Please uses SHA256 or SHA512 instead.";
                throw new Net_Growl_Exception($message);
            }
            $cipher = MCRYPT_RIJNDAEL_128;
            // Be compatible with Gfw 2, PHP Mcrypt ext. returns 32 in this case
            $key_size = 24;
            break;
        case 'des':
            $cipher = MCRYPT_DES;
            break;
        case '3des':
            if ($k < 2) {
                $message = "Password hash ($hash_algorithm)"
                        .  " and encryption ($crypt_algorithm) algorithms"
                        .  " are not compatible."
                        .  " Please uses SHA256 or SHA512 instead.";
                throw new Net_Growl_Exception($message);
            }
            $cipher = MCRYPT_3DES;
            break;
        case 'none':  // No encryption required
            return array('NONE', $plainText);
        default:      // Encryption algorithm unknown
            $message = "Invalid encryption algorithm ($crypt_algorithm)";
            throw new Net_Growl_Exception($message);
        }

        // All encryption algorithms should use
        // a block mode of CBC (Cipher Block Chaining)

        $td = mcrypt_module_open($cipher, '', $crypt_mode, '');

        $iv_size    = mcrypt_enc_get_iv_size($td);
        $block_size = mcrypt_enc_get_block_size($td);
        if (!isset($key_size)) {
            $key_size = mcrypt_enc_get_key_size($td);
        }

        // Here's our 128-bit IV which is used for both 256-bit and 128-bit keys.
        if (!isset($ivVal)) {
            $ivVal = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        }
        $ivHex = bin2hex($ivVal);

        // Different encryption algorithms require different key lengths
        // and IV sizes, so use the first X bytes of the key as required.
        $key = substr($key, 0, $key_size);

        $init = mcrypt_generic_init($td, $key, $ivVal);
        if ($init != -1) {
            if ($crypt_mode == MCRYPT_MODE_CBC) {
                /**
                 * Pads a string using the RSA PKCS7 padding standards
                 * so that its length is a multiple of the blocksize.
                 * $block_size - (strlen($text) % $block_size) bytes are added,
                 * each of which is equal to
                 * chr($block_size - (strlen($text) % $block_size)
                 */
                $length = $this->strByteLen($plainText);
                $pad = $block_size - ($length % $block_size);
                $plainText = str_pad($plainText, $length + $pad, chr($pad));
            }
            $cipherText = mcrypt_generic($td, $plainText);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
        } else {
            $cipherText = $plainText;
        }

        return array(strtoupper("$crypt_algorithm:$ivHex"), $cipherText);
    }

    /**
     * Translates boolean value to comprehensible text for GNTP messages
     *
     * @param mixed $value Compatible Boolean String or value to translate
     *
     * @return string
     */
    private function _toBool($value)
    {
        if (preg_match('/^([Tt]rue|[Yy]es)$/', $value)) {
            return 'True';
        }
        if (preg_match('/^([Ff]alse|[Nn]o)$/', $value)) {
            return 'False';
        }
        if ((bool)$value === true) {
            return 'True';
        }
        return 'False';
    }
}
 