<?php

/**
 * @file
 * login functions
 */

/**
  * Class for login
  */
class Login {

  var $error;

  /**
    * Authenticate user and register user information into a session
    */
  function Auth() {

    global $ariadminusername;
    global $ariadminpassword;
    global $ARI_ADMIN_EXTENSIONS;
    global $ARI_CRYPT_PASSWORD;
    global $ASTERISK_VOICEMAIL_CONF;
    global $ASTERISK_VOICEMAIL_CONTEXT;
    global $ASTERISK_VOICEMAIL_PATH;
    global $ASTERISK_PROTOCOLS;
    global $CALLMONITOR_ADMIN_EXTENSIONS;
    global $ARI_NO_LOGIN;
    global $ARI_DEFAULT_ADMIN_PAGE;
    global $ARI_DEFAULT_USER_PAGE;

    $crypt = new Crypt();

    // init variables
    $extension = '';
    $displayname = '';
    $vm_password = '';
    $category = '';
    $context = '';
    $voicemail_enabled = '';
    $voicemail_email_address = '';
    $voicemail_pager_address = '';
    $voicemail_email_enable = '';
    $admin = '';
    $admin_callmonitor = '';
    $default_page = '';

    $username = '';
    $password = '';

    // get the ari authentication cookie 
    $data = '';
    $chksum = '';
    if (isset($_COOKIE['ari_auth'])) {
      $buf = json_decode($_COOKIE['ari_auth'],true);
      if(!is_array($buf)) {
        $data = false;
        $chksum = false;
      } else {
        list($data,$chksum) = $buf;
      }
    }
    if (md5($data) == $chksum) {
      $data = json_decode($crypt->decrypt($data,$ARI_CRYPT_PASSWORD),true);
      $username = $data['username'];
      $password = $data['password'];
    }

    if (isset($_POST['username']) && 
          isset($_POST['password'])) {
      $username = $_POST['username'];
      $password = $_POST['password'];
    }

    // init email options array
    $voicemail_email = array();

    // when login, make a new session
    if ($username && !$ARI_NO_LOGIN) {

      $auth = false;
      // check admin
      if (!$auth) {
        if ($username==$ariadminusername && 
              $password==$ariadminpassword) {

          // authenticated
          $auth = true; 

          $extension = 'admin';
          $name = 'Administrator';
          $admin = 1;
          $admin_callmonitor = 1;

          $default_page = $ARI_DEFAULT_ADMIN_PAGE;
        }
      }

      // check voicemail login
      if (!$auth) {

        if (is_readable($ASTERISK_VOICEMAIL_CONF)) {

          $lines = file($ASTERISK_VOICEMAIL_CONF);

          // look for include files and tack their lines to end of array
          foreach ($lines as $key => $line) {

            if (preg_match("/include/i",$line)) {

              $include_filename = '';
              $parts = preg_split('/\s/',$line);
              if (isset($parts[1])) {
                $include_filename = trim($parts[1]);
              }

              if ($include_filename) {
                $path_parts = pathinfo($ASTERISK_VOICEMAIL_CONF);
                $include_path = fixPathSlash($path_parts['dirname']) . $include_filename;
                foreach (glob($include_path) as $include_file) {
                  $include_lines = file($include_file);
                  $lines = array_merge($include_lines,$lines);
                }
              }
            }
          }

          // process
          foreach ($lines as $key => $line) {
			$line = trim($line);
			if (preg_match("/^;/i",$line)) {
				continue;
			}

            // check for current context and process
            if (preg_match("/\[.*\]/i",$line)) {
              $currentContext = trim(preg_replace('/\[|\]/', '', $line));
            }
            if ($ASTERISK_VOICEMAIL_CONTEXT &&
                  $currentContext!=$ASTERISK_VOICEMAIL_CONTEXT) {
              continue;
            }

            // check for user and process
            unset($value);
            $parts = preg_split('/=>/',$line);
            if (isset($parts[0])) {
              $var = $parts[0];
            }
            if (isset($parts[1])) {
              $value = $parts[1];
            }
            $var = trim($var);
            if ($var==$username && $value) {
              $buf = preg_split('/,/',$value);
              if ($buf[0]==$password) {  

                // authenticated
                $auth = true; 
                $extension = $username;
                $displayname = $buf[1];
                $vm_password = $buf[0];
                $default_page = $ARI_DEFAULT_USER_PAGE;
                $context = $currentContext;
                $voicemail_enabled = 1;
                $voicemail_email_address = $buf[2];
                $voicemail_pager_address = $buf[3];
                
                if ($voicemail_email_address || $voicemail_pager_address) {
                  $voicemail_email_enable = 1;
                }

                $options = preg_split('/\|/',$buf[4]);
                foreach ($options as $option) {
                  $opt_buf = preg_split('/=/',$option);
                  $voicemail_email[$opt_buf[0]] = trim($opt_buf[1]);
                }

                $admin = 0;
                if ($ARI_ADMIN_EXTENSIONS) {
                  $extensions = preg_split('/,/',$ARI_ADMIN_EXTENSIONS);
                  foreach ($extensions as $key => $value) {
                    if ($extension==$value) {
                      $admin = 1;
                      break 2;
                    }
                  }
                }
  
                $admin_callmonitor = 0;
                if ($CALLMONITOR_ADMIN_EXTENSIONS) {
                  $extensions = preg_split('/,/',$CALLMONITOR_ADMIN_EXTENSIONS);
                  foreach ($extensions as $key => $value) {
                    if ($value=='all' || $extension==$value) {
                      $admin_callmonitor = 1;
                      break 2;
                    }
                  }
                }
              }
              else {
                $_SESSION['ari_error'] = _("Incorrect Username or Password");
                return;
              }
            }
          }
        }
        else {
          $_SESSION['ari_error'] = "File not readable: " . $ASTERISK_VOICEMAIL_CONF;
          return;
        }
      }

      // check sip login
      if (!$auth) {

        foreach($ASTERISK_PROTOCOLS as $protocol => $value) {

          $config_files = preg_split('/;/',$value['config_files']);
          foreach ($config_files as $config_file) {

            if (is_readable($config_file)) {

              $lines = file($config_file);
              foreach ($lines as $key => $line) {

                unset($value);
                $parts = preg_split('/=/',$line);
                if (isset($parts[0])) {
                  $var = trim($parts[0]);
                }
                if (isset($parts[1])) {
                  $value = trim($parts[1]);
                }
                if ($var=="username") {
                  $protocol_username = $value;
                }
                if ($var=="secret") {

                  $protocol_password = $value;
                  if ($protocol_username==$username &&
                        $protocol_password==$password) {  

                    // authenticated
                    $auth = true;  
                    $extension = $username ;
                    $displayname = $username;
                    $default_page = $ARI_DEFAULT_ADMIN_PAGE;
  
                    $admin = 0;
                    if ($ARI_ADMIN_EXTENSIONS) {
                      $extensions = preg_split('/,/',$ARI_ADMIN_EXTENSIONS);
                      foreach ($extensions as $key => $value) {
                        if ($extension==$value) {
                          $admin = 1;
                          break 2;
                        }
                      }
                    }

                    $admin_callmonitor = 0;
                    if ($CALLMONITOR_ADMIN_EXTENSIONS) {
                      $extensions = preg_split('/,/',$CALLMONITOR_ADMIN_EXTENSIONS);
                      foreach ($extensions as $key => $value) {
                        if ($value=='all' || $extension==$value) {
                          $admin_callmonitor = 1;
                          break 2;
                        }
                      }
                    }
                  }
                  else if ($protocol_username==$username &&
                             $protocol_password!=$password) {
                    $_SESSION['ari_error'] = _("Incorrect Username or Password");
                    return;
                  }
                }
              }
            }
          }
        }
      }

      // let user know bad login
      if (!$auth) {
        $_SESSION['ari_error'] = _("Incorrect Username or Password");
      }

      // if authenticated and user wants to be remembered, set cookie 
      $remember = '';
      if (isset($_POST['remember'])) {
        $remember = $_POST['remember'];
      }
      if ($auth && $remember) {

        $data = array('username' => $username, 'password' => $password);
        $data = $crypt->encrypt(json_encode($data),$ARI_CRYPT_PASSWORD);

        $chksum = md5($data);

        $buf = json_encode(array($data,$chksum));
        setcookie('ari_auth',$buf,time()+365*24*60*60,'/');
      }

      // set category
      if (!$category) {
        $category = "general";
      }
   
      // set context
      if (!$context) {
        $context = "default";
      }

      // no login user
      if ($ARI_NO_LOGIN) {
        $extension = 'admin';
        $name = 'Administrator';
        $admin_callmonitor = 1;
        $default_page = $ARI_DEFAULT_ADMIN_PAGE;
      } 

      // get outboundCID if it exists
      $outboundCID = $this->getOutboundCID($extension);

      // set
      if ($extension) {
        $_SESSION['ari_user']['extension'] = $extension;
        $_SESSION['ari_user']['outboundCID'] = $outboundCID;
        $_SESSION['ari_user']['displayname'] = $displayname;
        $_SESSION['ari_user']['voicemail_password'] = $vm_password;
        $_SESSION['ari_user']['category'] = $category;
        $_SESSION['ari_user']['context'] = $context;
        $_SESSION['ari_user']['voicemail_enabled'] = $voicemail_enabled;
        $_SESSION['ari_user']['voicemail_email_address'] = $voicemail_email_address;
        $_SESSION['ari_user']['voicemail_pager_address'] = $voicemail_pager_address;
        $_SESSION['ari_user']['voicemail_email_enable'] = $voicemail_email_enable;
        foreach ($voicemail_email as $key => $value) {
          $_SESSION['ari_user']['voicemail_email'][$key] = $value;
        }
        $_SESSION['ari_user']['admin'] = $admin;
        $_SESSION['ari_user']['admin_callmonitor'] = $admin_callmonitor;
        $_SESSION['ari_user']['default_page'] = $default_page;

				// Make sure nothing stale is here:
				//
				unset($_SESSION['ari_user']['recfiles']);

        // don't force the session data saved, there may be voicemail or callmonitor files still
      } 
    }
  } 

  /*
   * Gets user outbound caller id
   *
   * @param $exten
   *   Extension to get information about
   * @return $ret
   *   outbound caller id 
   */
  function getOutboundCID($extension) {

    global $asterisk_manager_interface;

    $ret = '';
    $response = $asterisk_manager_interface->Command2("Action: Command\r\nCommand: database get AMPUSER $extension/outboundcid\r\n\r\n");
    if ($response) {
    
		if (!strpos( $response, "<")) {
			$ret = trim($response);
		} else {
			$posLeft  = strpos( $response, "<")+strlen("<");
			$posRight = strpos( $response, ">", $posLeft);
			$ret = substr( $response,$posLeft,$posRight-$posLeft);
		}
    }
    return $ret;
  }

  /**
    * logout
    */
  function Unauth() {
    unset($_COOKIE["ari_auth"]);
    setcookie('ari_auth',"",time(),'/');
    unset($_SESSION['ari_user']);
  }

  /**
   * Provide a login form for user
   *
   * @param $request
   *   Variable to hold data entered into form
   */
  function GetForm() {

    global $ARI_NO_LOGIN;
	$ret = '';
	
    if ($ARI_NO_LOGIN) {
      $ret = '';
      return;
    }

    if (isset($_GET['login'])) {
      $login = $_GET['login'];
    }

    // if user name and password were given, but there was a problem report the error
    if ($this->error!='') {
      $ret = $this->error;
    }

    $language = new Language();
    $display = new Display(NULL);

    // new header
    $ret .= $display->DisplayHeaderText(_("Login"));
    $ret .= $display->DisplayLine();
    $ret .= checkErrorMessage();
    $ret .= "
      <table id='login'>
        <form id='login' name='login' action=" . $_SESSION['ARI_ROOT'] . " method='POST'>
          <tr>
            <td class='right'>
              <small><small>" . _("Login") . ":&nbsp;&nbsp;</small></small>
            </td>
            <td>
              <input type='text' name='username' value='" . htmlspecialchars($login) . "' maxlength=20 tabindex=1>
            </td>
          </tr>
          <tr>
            <td class='right'>
              <small><small>" . _("Password") . ":&nbsp;&nbsp;</small></small>
            </td>
            <td colspan=1>
              <input type='password' name='password' maxlength=20 tabindex=2>
            </td>
          </tr>	
          <tr>				
            <td></td>	
            <td>
              <input type='submit' name='btnSubmit' value='" . _("Submit") . "' tabindex=3></small></small></p>
            </td>
          </tr>
          <tr>
            <td class='right'>
              <input type='checkbox' name='remember'>
            </td>
            <td class='left'>
              <p class='small'>" . _("Remember Password") . "</p>
            </td>
          </tr>
        </form>
        <tr>				
          <td></td>	
          <td>
            " . $language->getForm() . "
          </td>
        </tr>
        <tr><td>&nbsp;</td></tr>
      </table>
      <table id='login_text'>
        <tr>
          <td>" .
            _("Use your <b>Voicemail Mailbox and Password</b>") . "<br>" .
            _("This is the same password used for the phone") . "<br>" .
            "<br>" . 
            _("For password maintenance or assistance, contact your Phone System Administrator.") . "<br>" . "
          </td>
        </tr>
      </table>";

    $ret .= "
      <script type='text/javascript'> 
      <!-- 
        if (document.login) { 
          document.login.username.focus(); 
        } 
      // --> 
      </script>";

    return $ret;
  } 


}


?>
