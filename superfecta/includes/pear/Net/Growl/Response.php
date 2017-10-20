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
 * @version  SVN: $Id: Response.php 324805 2012-04-04 16:05:33Z farell $
 * @link     http://growl.laurent-laville.org/
 * @link     http://pear.php.net/package/Net_Growl
 * @since    File available since Release 2.1.0
 */
 
/**
 * Class representing a Growl response using GNTP protocol
 *
 * The class is designed to be used to get additionnal information
 * on the response received.
 * <code>
 *  $growl = Net_Growl::singleton($appName, $notifications, $password, $options);
 *
 *  $response = $growl->register();
 *
 *  if ($response->getStatus() != 'OK') {
 *      echo $response->getStatus() . ' ' .
 *           $response->getErrorCode() . ' ' .
 *           $response->getErrorDescription();
 *  }
 * </code>
 *
 * @category Networking
 * @package  Net_Growl
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @author   Bertrand Mansion <bmansion@mamasam.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  Release: 2.6.0
 * @link     http://growl.laurent-laville.org/
 * @link     http://pear.php.net/package/Net_Growl
 * @link     http://www.growlforwindows.com/gfw/help/gntp.aspx#responses
 * @since    Class available since Release 2.1.0
 */
class Net_Growl_Response
{
    /**
     * GNTP protocol version
     * @var string
     * @see getVersion()
     */
    protected $version;

    /**
     * Status code
     * @var string
     * @see getStatus()
     */
    protected $code;

    /**
     * Request action
     * @var string
     * @see getResponseAction()
     */
    protected $action;

    /**
     * Error code
     * @var integer
     * @see getErrorCode()
     */
    protected $errorCode;

    /**
     * Error description
     * @var string
     * @see getErrorDescription()
     */
    protected $errorDescription;

    /**
     * The machine name/host name of the sending computer
     * @var string
     * @see getOriginMachineName()
     */
    protected $machineName;

    /**
     * The identity of the sending framework
     * @var string
     * @see getOriginSoftwareName()
     */
    protected $softwareName;

    /**
     * The version of the sending framework
     * @var string
     * @see getOriginSoftwareVersion()
     */
    protected $softwareVersion;

    /**
     * The identify of the sending computer OS/platform
     * @var string
     * @see getOriginPlatformName()
     */
    protected $platformName;

    /**
     * The version of the the sending computer OS/platform
     * @var string
     * @see getOriginPlatformVersion()
     */
    protected $platformVersion;

    /**
     * Response body
     * @var string
     * @see appendBody()
     */
    protected $body;

    /**
     * Constructor, parses the response status line
     *
     * @param string $statusLine Response status line (e.g. "GNTP/1.0 -OK NONE")
     *
     * @throws Net_Growl_Exception if status line is invalid according to spec
     * @link   http://www.growlforwindows.com/gfw/help/gntp.aspx
     */
    public function __construct($statusLine)
    {
        if (!preg_match('!^GNTP/(\d\.\d)\s\-([^\s]+)\s(.+)?$!', $statusLine, $m)) {
            throw new Net_Growl_Exception("Malformed response: {$statusLine}");
        }
        $this->version = $m[1];
        $this->code    = $m[2];
        $this->body    = '';
    }

    /**
     * Appends a string to the response body excluding
     * the protocol identifier, version, message type, and encryption algorithm id
     *
     * Should be used only by Mock adapter intended for testing
     *
     * @param string $bodyChunk Part or full body response
     *
     * @return void
     * @see Net_Growl_GntpMock::createResponseFromString(),
     *      Net_Growl_GntpMock::createResponseFromFile()
     */
    public function appendBody($bodyChunk)
    {
        $this->body .= $bodyChunk;

        $parts = preg_split('!(\r?\n){1}!m', $this->body);

        foreach ($parts as $part) {
            if (preg_match('/^Response-Action: (.*)$/', $part, $m)) {
                $this->action = $m[1];
            }
            /**
             * For messagetype -ERROR,
             * the following headers may also be returned:
             */
            if (preg_match('/^Error-Code: (.*)$/', $part, $m)) {
                $this->errorCode = $m[1];
            }
            if (preg_match('/^Error-Description: (.*)$/', $part, $m)) {
                $this->errorDescription = $m[1];
            }

            /**
             * For all types of responses,
             * the following generic headers may also be returned:
             */
            if (preg_match('/^Origin-Machine-Name: (.*)$/', $part, $m)) {
                $this->machineName = $m[1];
            }
            if (preg_match('/^Origin-Software-Name: (.*)$/', $part, $m)) {
                $this->softwareName = $m[1];
            }
            if (preg_match('/^Origin-Software-Version: (.*)$/', $part, $m)) {
                $this->softwareVersion = $m[1];
            }
            if (preg_match('/^Origin-Platform-Name: (.*)$/', $part, $m)) {
                $this->platformName = $m[1];
            }
            if (preg_match('/^Origin-Platform-Version: (.*)$/', $part, $m)) {
                $this->platformVersion = $m[1];
            }
        }
    }

    /**
     * Returns GNTP protocol version (e.g. 1.0, 1.1)
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Returns the status code (OK | ERROR)
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->code;
    }

    /**
     * Returns the request action (REGITER | NOTIFY)
     *
     * @return string
     */
    public function getResponseAction()
    {
        return $this->action;
    }

    /**
     * Returns the error code
     *
     * 200 - TIMED_OUT
     *       The server timed out waiting for the request to complete
     * 201 - NETWORK_FAILURE
     *       The server was unavailable
     *       or the client could not reach the server for any reason
     * 300 - INVALID_REQUEST
     *       The request contained an unsupported directive,
     *       invalid headers or values, or was otherwise malformed
     * 301 - UNKNOWN_PROTOCOL
     *       The request was not a GNTP request
     * 302 - UNKNOWN_PROTOCOL_VERSION
     *       The request specified an unknown or unsupported GNTP version
     * 303 - REQUIRED_HEADER_MISSING
     *       The request was missing required information
     * 400 - NOT_AUTHORIZED
     *       The request supplied a missing or wrong password/key
     *       or was otherwise not authorized
     * 401 - UNKNOWN_APPLICATION
     *       Application is not registered to send notifications
     * 402 - UNKNOWN_NOTIFICATION
     *       Notification type is not registered by the application
     * 500 - INTERNAL_SERVER_ERROR
     *       An internal server error occurred while processing the request
     *
     * @return integer
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Returns the error description
     *
     * @return string
     * @see getErrorCode()
     */
    public function getErrorDescription()
    {
        return $this->errorDescription;
    }

    /**
     * Returns the machine name/host name of the sending computer
     *
     * @return string
     */
    public function getOriginMachineName()
    {
        return $this->machineName;
    }

    /**
     * Returns the identity of the sending framework
     * Example1: Growl/Win
     * Example2: GrowlAIRConnector
     *
     * @return string
     */
    public function getOriginSoftwareName()
    {
        return $this->softwareName;
    }

    /**
     * Returns the version of the sending framework.
     * Example1: 2.0.0.28
     * Example2: 1.2
     *
     * @return string
     */
    public function getOriginSoftwareVersion()
    {
        return $this->softwareVersion;
    }

    /**
     * Returns the identify of the sending computer OS/platform
     * Example1: Microsoft Windows NT 5.1.2600 Service Pack 3
     * Example2: Mac OS X
     *
     * @return string
     */
    public function getOriginPlatformName()
    {
        return $this->platformName;
    }

    /**
     * Returns the version of the sending computer OS/platform
     * Example1: 5.1.2600.196608
     * Example2: 10.6
     *
     * @return string
     */
    public function getOriginPlatformVersion()
    {
        return $this->platformVersion;
    }

    /**
     * Returns the String representation of the Growl response
     * Example1: Response REGISTER OK (Growl/Win 2.0.0.28)
     * Example2: Response ERROR 402 No notifications registered (Growl/Win 2.0.0.28)
     *
     * @return string
     */
    public function __toString()
    {
        $str  = rtrim("Response " . $this->getResponseAction());
        $str .= ' ' . $this->getStatus();

        if ($this->getStatus() == 'ERROR') {
            $str .= sprintf(
                ' %s %s',
                $this->getErrorCode(),
                $this->getErrorDescription()
            );
        }
        $str .= sprintf(
            ' (%s %s)',
            $this->getOriginSoftwareName(),
            $this->getOriginSoftwareVersion()
        );
        return $str;
    }
}
 