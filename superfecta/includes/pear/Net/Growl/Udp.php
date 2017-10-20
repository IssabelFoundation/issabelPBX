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
 * @version  SVN: $Id: Udp.php 324805 2012-04-04 16:05:33Z farell $
 * @link     http://growl.laurent-laville.org/
 * @link     http://pear.php.net/package/Net_Growl
 * @since    File available since Release 0.9.0
 */
 
/**
 * Growl implements UDP protocol
 *
 * @category Networking
 * @package  Net_Growl
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @author   Bertrand Mansion <bmansion@mamasam.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  Release: 2.6.0
 * @link     http://growl.laurent-laville.org/
 * @link     http://pear.php.net/package/Net_Growl
 * @link     http://growl.info Growl Homepage
 * @since    Class available since Release 0.9.0
 */
class Net_Growl_Udp extends Net_Growl
{
    /**
     * Class constructor
     *
     * @param mixed  &$application  Can be either a Net_Growl_Application object
     *                              or the application name string
     * @param array  $notifications List of notification types
     * @param string $password      (optional) Password for Growl
     * @param array  $options       (optional) List of options : 'host', 'port',
     *                              'protocol', 'timeout' for Growl socket server.
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
     * @return true
     * @throws Net_Growl_Exception if remote server communication failure
     */
    public function sendRegister()
    {
        $appName       = $this->utf8Encode($this->getApplication()->getGrowlName());
        $password      = $this->getApplication()->getGrowlPassword();
        $nameEnc       = $defaultEnc = '';
        $nameCnt       = $defaultCnt = 0;
        $notifications = $this->getApplication()->getGrowlNotifications();

        foreach ($notifications as $name => $options) {
            if (is_array($options) && !empty($options['enabled'])) {
                $defaultEnc .= pack('c', $nameCnt);
                $defaultCnt++;
            }

            $name = $this->utf8Encode($name);
            $nameEnc .= pack('n', $this->strByteLen($name)).$name;
            $nameCnt++;
        }

        // Version of the Growl protocol used in this package
        $_growl_protocol_version = 1;
        // Packet of type Registration
        $_growl_type_registration = 0;

        $data = pack(
            'c2nc2',
            $_growl_protocol_version,
            $_growl_type_registration,
            $this->strByteLen($appName),
            $nameCnt,
            $defaultCnt
        );

        $data .= $appName . $nameEnc . $defaultEnc;
        $data .= pack('H32', md5($data . $password));

        return $this->sendRequest('REGISTER', $data);
    }

    /**
     * Sends the NOTIFY message type
     *
     * @param string $name        Notification name
     * @param string $title       Notification title
     * @param string $description Notification description
     * @param string $options     Notification options
     *
     * @return true
     * @throws Net_Growl_Exception if remote server communication failure
     */
    public function sendNotify($name, $title, $description, $options)
    {
        $appName     = $this->utf8Encode($this->getApplication()->getGrowlName());
        $password    = $this->getApplication()->getGrowlPassword();
        $name        = $this->utf8Encode($name);
        $title       = $this->utf8Encode($title);
        $description = $this->utf8Encode($description);
        $priority    = isset($options['priority'])
            ? $options['priority'] : Net_Growl::PRIORITY_NORMAL;

        $flags = ($priority & 7) * 2;

        if ($priority < 0) {
            $flags |= 8;
        }
        if (isset($options['sticky']) && $options['sticky'] === true) {
            $flags = $flags | 1;
        }

        // Version of the Growl protocol used in this package
        $_growl_protocol_version = 1;
        // Packet of type Notification
        $_growl_type_notification = 1;

        $data = pack(
            'c2n5',
            $_growl_protocol_version,
            $_growl_type_notification,
            $flags,
            $this->strByteLen($name),
            $this->strByteLen($title),
            $this->strByteLen($description),
            $this->strByteLen($appName)
        );

        $data .= $name . $title . $description . $appName;
        $data .= pack('H32', md5($data . $password));

        return $this->sendRequest('NOTIFY', $data);
    }
}
 