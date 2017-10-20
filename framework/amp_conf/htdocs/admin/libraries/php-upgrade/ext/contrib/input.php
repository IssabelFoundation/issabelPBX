<?php
 /**
  * type: interface
  * title: $_REQUEST var object-wrappers
  * description: provides sanitization by encapsuling request variables against raw access
  * version: 0.1
  * license: public domain (=anything you want)
  * depends: php:filter, php >5.1
  * x-throws: E_NOTICE
  * 
  * Object wrappers for HTTP input variables sanitize incoming data,
  * and prevent casual unverified access. Using them ensures a single
  * entry point and verification spot for all user data.
  *
  *   $_REQUEST = new input($_REQUEST);
  *   $_GET = new input($_GET);
  *   $_POST = new input($_POST);
  *   $_SERVER = new input($_SERVER);
  *
  * Provides convenient access to data over various filter methods:
  *
  *   print $_GET->int("search_q");
  *   mysql_query("  SELECT * FROM x WHERE y='{$_POST->sql(y)}'  ");
  *   require($_SERVER->name("SERVER_NAME").".htm");
  *
  * Available filter methods are:
  *   ->int
  *   ->name
  *   ->raw
  *   ->text
  *   ->regex
  *   ->array_int
  *   ->sql
  * and most sanitizers that the php filter_ extension provides.
  *
  * With PHP5 you can also access the filtered variables simply as
  * $_GET->int->varname  and  $_GET->int["varname"] instead of just
  * with the ordinary method $_GET->int("varname") way. PHP filter_
  * extension methods can be used with the method syntax only.
  *
  *
  * Input validation of course is no substitute for secure application
  * logic, parameterized sql and proper output encoding. But this
  * method is a good start, and streamlines input data handling.
  *
  */



/**
 * @package Request variable input wrapper.
 *
 */  
class input {


    /**
     * previous suberglobal input array
     * @var array
     * @access private
     */
    var $vars;
    
    
    /**
     * Initialize object from
     * @param array  one of $_REQUEST, $_GET or $_POST etc.
     */
    function __construct($in) {
    
        # save superglobal
        $this->vars = (array)$in;

        # provides convenience access ->filter->var and ->filter["trick"]
        foreach (get_class_methods(__CLASS__) as $sub) {
            $this->{$sub} = new input___sub($this, "$sub");
        }
    }
    
    
    /**
     * Generic array features. (has no keys)
     *  ->has(isset)  ->no(empty)  ->keys()
     */
    function has($name) {
        return isset($this->vars[$name]);
    }
    function no($name) {
        return empty($this->vars[$name]);
    }
    function keys($name) {
        return array_keys($this->vars);
    }
   


    
    

    
    #--- sanitization functions ---



    # this should obviously be avoided
    function raw($name) {
        trigger_error("Unfiltered input variable '$name' accessed.", E_USER_NOTICE);
        return $this->vars[$name];
    }

    # integer
    function int($name) {
        return (int)$this->vars[$name];
    }
    
    # proper identifiers (e.g. var names, only letters)
    function name($name) {
        return preg_replace("/[^\w_]+/", "", $this->vars[$name]);
    }
    
    # human-readable ascii text without control characters
    function text($name) {
        return preg_replace("/[^\w\d\s,._]+/U", "", strip_tags($this->vars[$name]));
    }

    # regular expression filter / or data match
    function regex($name, $rx="", $match=1) {
        # validating
        if (strpos($rx, "(")) {
            if (preg_match($rx, $this->vars[$name], $result)) {
                return($result[$match]);
            }
        }
        # cropping
        elseif (strpos($rx, "[^")) {
            return preg_replace($rx, "", $this->vars[$name]);
        }
    }
    
    # max length string
    function length($name, $max=65535) {
       return substr($this->text($name), 0, $max);
    }
    
    
    
    #--- custom functions ---
    
    
    
    # escape for concatenating data into sql query (= not good, folks!)
    function sql($name) {
        trigger_error("SQL escaping of input variable '$name'. Use of parameterized SQL is recommended for speed and security reasons.", E_USER_NOTICE);
        return mysql_real_escape_string/*seriously?!*/($this->vars[$name]);
    }
    
    # identifiers with underscores and dots, like "xvar.1_2.x"
    function id($name) {
        return preg_replace("#(^[^a-z_]+)|[^\w\d_.]+|([^\w_]$)#i", "", $this->vars[$name]);
    }
    

    
    // function email($name) { ... }   // provided by filter extension

    // function url($name) {  return preg_replace("/[^-\w\d\$.+!*'(),{}\|\\~\^\[\]\`<>#%\";\/?:@&=]+/", "", $this->vars[$name]); }

    // function json($name) { ... }
    
    // function datetime($name) { ... }  // as in HTML5
    
    /*function html($name) {
        $h = new HTML_Purifier;
        return $h->purify( $this->vars[$name] );
    }*/
    


    #--- application logic tailored ---


    /*function category_id($name) {
        $s = $this->name(vars[$name]);
        if (!isset($GLOBALS["app_config"]["categories"][$s])) {
            error_log("Security breach: User tried access with invalid &category= parameter, {$_SERVER->text(REMOTE_ADDR)}", 0);
        }
        else return $s;
    }*/
    
    // function session_id($name) { ... }  // e.g. verify last IP, stale session, user-agent

    // function range($name, $min, $max) { ... } //


    
    
    
    #--- array variants ---
    
    
    
    # input variable is an array of integers
    function array_int($name) {
        return $this->array_($name, "intval");
    }
    function array_($name, $func) {
        $tmp = (array)($this->vars[$name]);
        foreach ($tmp as $i=>$v) {
            $tmp[$i] = $func($v);
        }
        return $tmp;
    }
    
   
    
    
    #--- magic functions ---
    
    
    /**
     * If a bare variable access $_SERVER->SERVER_NAME occours,
     * refer to ->name() because this is the most commonly desired effect.
     */
    function __get($name) {
        return $this->name($name);
    }

    
    /**
     * Unkown methods are just passed on to the native PHP filter extension,
     * use ->array_WHATEVER() for recursive sanitiziation
     * or ->filter_validate_thingy() for native filter names
     */
    function __call($method, $args) {

        $map = array(
            "float" => "filter_number_float",
            "ip" => "filter_validate_ip",
            "hex" => array("filter_validate_int", "filter_flag_allow_hex"),
        );
        $name = $args[0];
        $flags = isset($args[1]) ? $args[1] : 0;
        $array_walk = (0 == strncmp($method, "array_", 6)) && ($method = substr($method, 6));

        # defer filter method
        if (isset($map[$method])) {
            $filter = $map[$method];
            if (is_array($filter)) {
                list($filter, $flags) = $filter;
            }
            $filter = strtoupper($filter);
            $flags = strtoupper($flags);
        }
        else {
            $filter = strtoupper($method);
            if (strpos($method, "validate") !== false) {
                $filter = "FILTER_SANITIZE_" . $filter;
            }
        }
       
        # whichnow?
        $filter_id = defined($filter) ? constant($filter) : filter_id($method);
        $flags_id = is_int($flags) ? $flags : (strlen($flags)>2 ? constant($flags) : 0);
        /* int, boolean, float, validate_url, validate_email, validate_ip,
        string, stripped, encoded, special_chars, unsafe_raw, email, url,
        number_int, number_float, magic_quotes */

        # pass on
        if ($filter_id === false) {
            trigger_error("no filter '$method'", E_USER_ERROR);
        }
        elseif ($array_walk) {
            $tmp = $this->vars[$name];
            foreach ($tmp as $i=>$v) {
                filter_var($tmp[$i], $filter_id, $flags_id);
            }
            return $tmp;
        }
        else {
            return filter_var($this->vars[$name], $filter_id, $flags_id);
        }

    }


}


/**
 * Allows additional access methods to input variables:
 *   $_REQUEST->name->VARNAME
 * and:
 *   $_REQUEST->name["invar"]
 *
 * @subpackage methodarray
 */
class input___sub implements ArrayAccess {
    function __construct($parent, $func) {
        $this->parent = $parent;
        $this->func = $func;
    }
    function __get($name) {
        return $this->parent->{$this->func}($name);
    }
    function offsetExists($name) { 
        return isset($this->parent->vars[$name]);
    }
    function offsetGet($name) {
        return $this->__get($name);
    }
    function offsetSet($name, $value) { /*forget it*/}
    function offsetUnset($name) { 0; }
}





/**
 * @code Initialize automatically.
 *
 */

$_SERVER = new input($_SERVER);
$_REQUEST = new input(array_merge($_GET, $_POST));
$_GET = new input($_GET);
$_POST = new input($_POST);
$_COOKIE = new input($_COOKIE);
#$_SESSION = new input($_SESSION);
#$_ENV = new input($_ENV);
#$_FILES cannot be used that way

?>