<?php
/**
 *
 * @group 4_0
 * @since 4.0
 * @untested
 *
 *
 * Here we collect PHP functions for versions before 4.1.
 *
 * YOU AIN'T GONNA NEED IT.
 *
 * It should be noted that 4.0.x versions can't be made compatible with
 * newer versions anyhow, because the newer superglobals ($_GET, $_REQUEST)
 * are missing - or not **superglobal** at least.
 *
 */

#---------------------------------------------------------------- 4.0.7 ---

#-- simulate superglobals
# you still had to put "global $_REQUEST,$_GET,..." into any function that
# used them - so this workaround is not all too useful anyhow, you should
# not accept PHP versions prior 4.0.7 for your scripts in any case!
if (!isset($GLOBALS["_REQUEST"])) {
   $GLOBALS["_GET"] = & $HTTP_GET_VARS;
   $GLOBALS["_POST"] = & $HTTP_POST_VARS;
   $GLOBALS["_SERVER"] = & $HTTP_SERVER_VARS;
   $GLOBALS["_ENV"] = & $HTTP_ENV_VARS;
   $GLOBALS["_COOKIE"] = & $HTTP_COOKIE_VARS;
   $GLOBALS["_FILES"] = & $HTTP_POST_FILES;
   $GLOBALS["_REQUEST"] = array_merge($HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS);
}


#---------------------------------------------------------------- 4.0.6 ---


#-- uses callback function to remove entries from array
if (!function_exists("array_filter")) {
   function array_filter($array, $callback="is_int") {
      foreach ($array as $key=>$val) {
         if (!call_user_func_array($callback, array($val))) {
            unset($array[$key]);
         }
      }
      return($array);
   }
}


#-- uses callback function to change array values, multiple input arrays
if (!function_exists("array_map")) {
   function array_map() {
      $arrays = get_func_args();
      $callback = shift($arrays);
      $r = &$arrays[0];
      foreach ($r as $key=>$val) {
         $val = call_user_func_array($callback, $arrays);
         $r[$key] = $val;
      }
      return($r);
   }
}


#-- checks for existence or only syntax of function or obj/class+method name
if (!function_exists("is_callable")) {
   function is_callable($var, $syntax_only=0) {
      $a = get_func_args();
      if (count($a) >= 3) {
         $fin = & $a[2];
      }

      #-- class/object+method
      if (is_array($var)) {
         if (count($var)!=2) {
            return false;
         }
         list($obj, $method) = $var;
         $class = $obj;
         if (is_object($obj)) {
            $class = get_class($obj);
         }

         if ($syntax_only) {
            $r = preg_match('/^\w+$/', $method);
            if (!is_object($obj)) {
               $r = $r && preg_match('/^\w+$/', $obj);
            }
         }
         else {
            if (is_object($obj)) {
               $r = method_exists($obj, $method);
            }
            else {
               $all = get_class_methods($obj);
               $r = in_array($method, $all);
            }
         }
         if ($r) { $fin = strtolower("$class:$method"); }
      }
      #-- simple function name
      elseif (is_string($var)) {
         if ($syntax_only) {
            $r = preg_match('/^\w+$/', $var);
         }
         else {
            $r = function_exists($var);
         }
         if ($r) { $fin = strtolower($var); }
      }
      #-- ooops
      else {
         $r = false;
      }
      return $r;
   }
}




#---------------------------------------------------------------- 4.0.5 ---
# chroot


#-- return index of value in array
if (!function_exists("array_search")) {
   function array_search($value, $array, $strict=false) {
      $strict = $strict ? true : false;
      foreach ($array as $key=>$v) {
         if ($strict&&($v===$value) || !$strict&&($v==$value)) {
            return($key);
         }
      }
      return(NULL);  // PHP prior 4.2 also did so
   }
}


#-- recursively collapse array into one value (via user callback)
if (!function_exists("array_reduce")) {
   function array_reduce($array, $callback, $value=NULL) {
      foreach ($array as $v) {
         if (is_array($v)) {
            $v = array_reduce($v, $callback);
         }
         else {
            $v = call_user_func_array($callback, array($value, $v));
         }
         $value = $v;
      }
      return($value);
   }
}


#-- checks variable to be of a simple type
if (!function_exists("is_scalar")) {
   function is_scalar($var) {
      return( !is_array($var) && !is_object($var) );
   }
}

#-- only static value (mix of C/English/European/US)
if (!function_exists("localeconv")) {
   function localeconv() {
      return array (
        'decimal_point' => '.',
        'thousands_sep' => '',
        'int_curr_symbol' => 'EUR ',   // more international than USD
        'currency_symbol' => '¤',      // unnamed/trans currency symbol
        'mon_decimal_point' => '.',
        'mon_thousands_sep' => ',',
        'positive_sign' => '',
        'negative_sign' => '-',
        'int_frac_digits' => 2,
        'frac_digits' => 2,
        'p_cs_precedes' => 1,
        'p_sep_by_space' => 0,
        'n_cs_precedes' => 1,
        'n_sep_by_space' => 0,
        'p_sign_posn' => 1,
        'n_sign_posn' => 1,
        'grouping' => array (0=>3, 1=>3,),
        'mon_grouping' => array(0=>3, 1=>3,),
      );
   }
}


#-- function by name
if (!function_exists("call_user_func_array")) {
   function call_user_func_array($callback, $param_array=array()) {
      if ($param_array) {
         $param_array = array_values($param_array);
         $params = "'" . implode("','", $param_array) . "'";
      }
      if (is_array($callback)) {
         $obj = &$callback[0];
         $method = $callback[1];
         if (!method_exists($obj, $method)) {
            trigger_error("call_user_method_array: method '$method' does not exist", E_ERROR);
         }
         elseif (is_object($obj)) {
            eval("return \$obj->$method($params);");
         }
         else {
            eval("return $obj::$method($params);");
         }
      }
      elseif (!function_exists("$callback")) {
         trigger_error("call_user_func_array: function '$method' does not exist", E_ERROR);
      }
      else {
         switch (count($param_array)) {
            case 0:
               return $callback();
            case 1:
               return $callback($param_array[0]);
            case 2:
               return $callback($param_array[0], $param_array[1]);
            case 3:
               return $callback($param_array[0], $param_array[1], $param_array[2]);
            case 4:
               return $callback($param_array[0], $param_array[1], $param_array[2], $param_array[3]);
            default:
               eval("return $callback($params);");
         }
      }
   }
   function call_user_method_array($method, &$obj, $param_array=array()) {
      call_user_func_array(array(&$obj, $method), $param_array);
   }
}




#---------------------------------------------------------------- 4.0.4 ---



#-- adds all values of given array into total sum
if (!function_exists("array_sum")) {
   function array_sum($array) {
      $sum = 0;
      foreach ($array as $val) {
         $sum += $val;
      }
      return $sum;
   }
}

#-- value of constant (if their name was returned as string from somewhere)
if (!function_exists("constant")) {
   function constant($name) {
      if (defined($name)) {
         eval("return $name");
      }
      else {
         return NULL;
      }
   }
}

#-- more a language construct
if (!function_exists("is_null")) {
   function is_null($var) {
      return($var === NULL);
   }
}



#---------------------------------------------------------------- 4.0.3 ---
# register_tick_function
# unregister_tick_function

if (!function_exists("pathinfo")) {
   function pathinfo($fn) {
      preg_match("#^(?:(.+)/)?([^/]+?(?:\.([^/\.]+))?)$#", $fn, $uu);
      return array(
         "dirname" => $uu[1],
         "basename" => $uu[2],
         "extension" => $uu[3],
      );
   }
}

if (!function_exists("escapeshellarg")) {
   function escapeshellarg($arg) {
      $arg = str_replace("'", "'\\''", $arg);
      return "'$arg'";
   }
}

if (!function_exists("is_uploaded_file")) {
   function is_uploaded_file($fn) {
      ( $dir = get_cfg_var("upload_tmp_dir") )
       or
      ( $dir = dirname(tempnam("", "")) );
      return( realpath($dir) == realpath(dirname($fn)) );
   }
}

if (!function_exists("move_uploaded_file")) {
   function move_uploaded_file($fn, $dest) {
      if (is_uploaded_file($fn)) {
         return copy($fn, $dest) && unlink($fn);
      }
   }
}


#---------------------------------------------------------------- 4.0.2 ---
# ob_get_length



if (!function_exists("strncasecmp")) {
   function strncasecmp($str1, $str2, $len=0) {
      if ($len > 0) {
         $str1 = substr($str1, 0, $len);
         $str2 = substr($str2, 0, $len);
      }
      return strcasecmp($str1, $str2);
   }
}


if (!function_exists("wordwrap")) {
   function wordwrap($text, $width=75, $break="\n", $hardcut=0) {
      $out = "";
      foreach (explode("\n", $text) as $line) {
         if ($out) {
            $out .= "\n";
         }
         while (strlen($line)) {
            if ($hardcut) {
               $l = $width;
            }
            else {
               $l = strrpos(substr($text, 0, $width), " ");
               if ($l && ($l < strlen($line))) {
                  $l = strlen($line);
               }
               if ($l === false) {
                  $l = $width;
               }
            }
            $out .= substr($line, 0, $l);
            $out .= "\n";
            $line = substr(0, $l);
         }
      }
      return($out);
   }
}

if (!function_exists("php_uname")) {
   function php_uname($mode="a") {
      switch ($mode) {
         case "s":  $p = "--kernel-name";  break;
         case "n":  $p = "--node-name";  break;
         case "r":  $p = "--release";  break;
         case "v":  $p = "--kernel-version";  break;
         case "m":  $p = "--machine";  break;
         default:  $p = "--all";  break;
      }
      return `uname $p`;
   }
}



#---------------------------------------------------------------- 4.0.1 ---
# levensth
# fflush() - unimplementable
# array_unique
# array_diff
# array_intersect
# array_merge_recursive
# crc32
# fscanf
# sscanf
# str_pad
# set_file_buffer
# spliti

if (!function_exists("php_sapi_name")) {
   function php_sapi_name() {
      if (isset($_ENV["CGI_INTERFACE"])) {
         return "cgi";
      }
      elseif (strpos($_ENV["SERVER_SOFTWARE"], "Apache") !== false) {
         return "apache";
      }
      // ...
      else {
         return "cgi";  // the silly "cli" variation didn't exist before 4.2
      }
   }
}

?>