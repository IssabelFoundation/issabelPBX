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
 * @version  SVN: $Id: GntpMock.php 324805 2012-04-04 16:05:33Z farell $
 * @link     http://growl.laurent-laville.org/
 * @link     http://pear.php.net/package/Net_Growl
 * @since    File available since Release 2.1.0
 */
 
/**
 * Mock adapter intended for testing
 *
 * Can be used to test applications depending on Net_Growl package without
 * actually performing any GNTP requests. This adapter will return responses
 * previously added via addResponse()
 * <code>
 *  $mock = Net_Growl::singleton($appName, $notifications, $password, $options);
 *  $mock->addResponse(
 *          "GNTP/1.0 -OK NONE\r\n" .
 *          "..."
 *  );
 *
 * // This will return the response set above
 * $response = $mock->register();
 * </code>
 *
 * @category Networking
 * @package  Net_Growl
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  Release: 2.6.0
 * @link     http://growl.laurent-laville.org/
 * @link     http://pear.php.net/package/Net_Growl
 * @since    Class available since Release 2.1.0
 */
class Net_Growl_GntpMock extends Net_Growl
{
    /**
     * A queue of responses to be returned by sendRequest()
     *
     * @var array
     */
    protected $responses = array();

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
    protected function __construct(&$application, $notifications = array(),
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
        $response = $this->sendRequest(null, null);
        if ($response->getStatus() == 'OK'
            && $response->getResponseAction() == 'REGISTER'
        ) {
            $this->isRegistered = true;
        }
        return $response;
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
        $response = $this->sendRequest(null, null);
        if ($response->getStatus() == 'OK'
            && $response->getResponseAction() == 'NOTIFY'
        ) {
            $this->growlNotificationCount++;
        }
        return $response;
    }

    /**
     * Returns the next response from the queue built by addResponse()
     *
     * If the queue is empty it will return default empty response with status 400,
     * if an Exception object was added to the queue it will be thrown.
     *
     * @param string $method   NOT USED
     * @param mixed  $data     NOT USED
     * @param bool   $callback NOT USED
     *
     * @return Net_Growl_Response
     * @throws Exception
     */
    protected function sendRequest($method, $data, $callback = false)
    {
        if (count($this->responses) > 0) {
            $response = array_shift($this->responses);
            if ($response instanceof Net_Growl_Response) {
                $this->debug($response->__toString());
                return $response;
            } else {
                // rethrow the exception
                $class   = get_class($response);
                $message = $response->getMessage();
                $code    = $response->getCode();
                throw new $class($message, $code);
            }
        } else {
            $this->debug('Bad Request', 'error');
            return self::createResponseFromString("Bad Request\r\n");
        }
    }

    /**
     * Adds response to the queue
     *
     * @param mixed $response Either a string, a pointer to an open file,
     *                        an instance of Net_Growl_Exception or Exception
     *
     * @return void
     * @throws Net_Growl_Exception
     */
    public function addResponse($response)
    {
        if (is_string($response)) {
            $response = $this->createResponseFromString($response);
        } elseif (is_resource($response)) {
            $response = $this->createResponseFromFile($response);
        } elseif (!$response instanceof Net_Growl_Exception
            && !$response instanceof Exception
        ) {
            throw new Net_Growl_Exception('Parameter is not a valid response');
        }
        $this->responses[] = $response;
    }

    /**
     * Creates a new Net_Growl_Response object from a string
     *
     * @param string $str Expected Growl Response
     *
     * @return Net_Growl_Response
     */
    protected function createResponseFromString($str)
    {
        $parts    = preg_split('!(\r?\n){1}!m', $str, 2);
        $response = new Net_Growl_Response($parts[0]);
        if (isset($parts[1])) {
            $response->appendBody($parts[1]);
        }
        return $response;
    }

    /**
     * Creates a new Net_Growl_Response object from a file
     *
     * @param resource $fp File pointer returned by fopen()
     *
     * @return Net_Growl_Response
     */
    protected function createResponseFromFile($fp)
    {
        $response = new Net_Growl_Response(fgets($fp));
        while (!feof($fp)) {
            $response->appendBody(fread($fp, 8192));
        }
        return $response;
    }
}
 