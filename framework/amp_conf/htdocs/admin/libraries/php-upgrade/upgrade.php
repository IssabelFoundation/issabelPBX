<?php
/**
 * api:		php
 * title:	upgrade.php
 * description:	Emulates functions from new PHP versions on older interpreters.
 * version:	18
 * license:	Public Domain
 * url:		http://freshmeat.net/projects/upgradephp
 * type:	functions
 * category:	library
 * priority:	auto
 * load_if:     (PHP_VERSION<5.2)
 * sort:	-255
 * provides:	upgrade-php, api:php5, json
 *
 *
 * By loading this library you get PHP version independence. It provides
 * downwards compatibility to older PHP interpreters by emulating missing
 * functions or constants using IDENTICAL NAMES. So this doesn't slow down
 * script execution on setups where the native functions already exist. It
 * is meant as quick drop-in solution. It spares you from rewriting code or
 * using cumbersome workarounds instead of the more powerful v5 functions.
 * 
 * It cannot mirror PHP5s extended OO-semantics and functionality into PHP4
 * however. A few features are added here that weren't part of PHP yet. And
 * some other function collections are separated out into the ext/ directory.
 * It doesn't produce many custom error messages (YAGNI), and instead leaves
 * reporting to invoked functions or for native PHP execution.
 * 
 * And further this is PUBLIC DOMAIN (no copyright, no license, no warranty)
 * so therefore compatible to ALL open source licenses. You could rip this
 * paragraph out to republish this instead only under more restrictive terms
 * or your favorite license (GNU LGPL/GPL, BSDL, MPL/CDDL, Artistic/PHPL, ..)
 *
 * Any contribution is appreciated. <milky*users#sf#net>
 *
 */





/**
 *                                   -------------------------- FUTURE ---
 * @group SVN
 * @since future
 *
 * Following functions aren't implemented in current PHP versions, but
 * might already be in CVS/SVN.
 *
 */







/**
 *                                   ----------------------------- 5.4 ---
 * @group 5_4
 * @since 5.4
 *
 * Extensions in PHP 5.4
 *
 * @emulated
 *    gzdecode
 *    hex2bin
 *    session_status -> basic probing
 *
 * @stub
 *    class_uses
 *    trait_exists
 *    get_declared_traits
 *
 * @missing
 *    libxml_set_external_entity_loader
 *    zlib_encode -> PHP_ZLIB_ENCODE_FUNC(zlib_encode, 0);
 *    zlib_decode -> PHP_ZLIB_DECODE_FUNC(zlib_decode, PHP_ZLIB_ENCODING_ANY);
 *    session_register_shutdown
 *    socket_import_stream
 *    getimagesizefromstring
 *    header_register_callback
 *    http_response_code
 *    stream_set_chunk_size
 *    CallbackFilterIterator
 *    RecursiveCallbackFilterIterator
 *    SessionHandler
 *    ReflectionZendExtension
 *
 * @unimplementable
 *    imageantialias
 *    imagelayereffect
 *
 */



/**
 * Simple convenience function for pack H*,
 * Converts a hextuplet string into its binary representation.
 *
 */
if (!function_exists("hex2bin")) {
   function hex2bin($hex) {
       return pack("H*", $hex);
   }
}



/**
 * Set or get HTTP status code.
 *
 */
if (!function_exists("http_response_code")) {
   function http_response_code($which=NULL) {

       $cgi = ini_get("cgi.rfc2616_headers");
       $headers = preg_grep("#^Status:|^HTTP/\d\.\d#i", headers_list());

       # override       
       if ($which >= 100 and $which <= 999) {
           if ($headers) {
               header_remove(current($headers));
           }
           # implicit notices for headers_sent()
           header($cgi ? "HTTP/1.0 $which n/a" : "Status: $which n/a");
       }
       # get current
       elseif ($which == NULL) {
           if (!$headers || !preg_match("/\d\d\d/", current($headers), $m)) {
               return 200; #default
           }
           return intval($m[0]);
       }
       else trigger_error("invalid status number", E_USER_WARNING);
   }
}



/**
 * Sends a Location: header. Unlike the PHP-builtin, it won't complete relative URLs.
 * So it's RFC6616 compliant, not anal about the original HTTP/1.1 revision RFC2616.
 * The <meta> fallback is also extraneous.
 * Belongs to http extensions actually. (Will be grouped out in later upgradephp revision.)
 *
 */
if (!function_exists("http_redirect")) {
   function http_redirect($url, $params=array(), $session=false, $status=0) {
       if ($session) {
           $params[session_name()] = session_id();
       }
       if ($params) {
           $url .= strstr($url, "?") ? "&" : "?";
           $url .= http_build_query($params);
       }
       header("Location: $url"); #, $status ? $status : 301);
       $url = htmlspecialchars($url, ENT_QUOTES, "UTF-8");
       print "Redirecting to <a href=\"$url\">$url</a>\n";
       print "<meta http-equiv=\"Location\" content=\"$url\" />\n";
       exit; // built-in exit
   }
}



/**
 * Sends a Content-Type: header
 * Belongs to http extensions actually. (Will be grouped out in later upgradephp revision.)
 *
 */
if (!function_exists("http_send_content_type")) {
   function http_send_content_type($content_type="application/x-octetstream") {
       return header("Content-Type: $content_type");
   }
}



/**
 * @stub
 * Traits (partial classes, elaborate syntactic and academic workaround, because MI is
 * hard to implement in *compiled* languages) cannot be emulated in older interpreters.
 *
 */
if (!function_exists("class_uses")) {
   function class_uses($trait) {
       return false;
   }
}
if (!function_exists("trait_exists")) {
   function trait_exists($trait) {
       return false;
   }
}
if (!function_exists("get_declared_traits")) {
   function get_declared_traits($trait) {
       return (array)NULL;
   }
}



/**
 * Long predicted, officially available @since 5.4.
 *
 * Inflates a string enriched with gzip headers. Counterpart to gzencode().
 *
 */
if (!function_exists("gzdecode")) {
   function gzdecode($gzdata, $maxlen=NULL) {

      #-- decode header
      $len = strlen($gzdata);
      if ($len < 20) {
         return;
      }
      $head = substr($gzdata, 0, 10);
      $head = unpack("n1id/C1cm/C1flg/V1mtime/C1xfl/C1os", $head);
      list($ID, $CM, $FLG, $MTIME, $XFL, $OS) = array_values($head);
      $FTEXT = 1<<0;
      $FHCRC = 1<<1;
      $FEXTRA = 1<<2;
      $FNAME = 1<<3;
      $FCOMMENT = 1<<4;
      $head = unpack("V1crc/V1isize", substr($gzdata, $len-8, 8));
      list($CRC32, $ISIZE) = array_values($head);

      #-- check gzip stream identifier
      if ($ID != 0x1f8b) {
         trigger_error("gzdecode: not in gzip format", E_USER_WARNING);
         return;
      }
      #-- check for deflate algorithm
      if ($CM != 8) {
         trigger_error("gzdecode: cannot decode anything but deflated streams", E_USER_WARNING);
         return;
      }

      #-- start of data, skip bonus fields
      $s = 10;
      if ($FLG & $FEXTRA) {
         $s += $XFL;
      }
      if ($FLG & $FNAME) {
         $s = strpos($gzdata, "\000", $s) + 1;
      }
      if ($FLG & $FCOMMENT) {
         $s = strpos($gzdata, "\000", $s) + 1;
      }
      if ($FLG & $FHCRC) {
         $s += 2;  // cannot check
      }
      
      #-- get data, uncompress
      $gzdata = substr($gzdata, $s, $len-$s);
      if ($maxlen) {
         $gzdata = gzinflate($gzdata, $maxlen);
         return($gzdata);  // no checks(?!)
      }
      else {
         $gzdata = gzinflate($gzdata);
      }
      
      #-- check+fin
      $chk = crc32($gzdata);
      if ($CRC32 != $chk) {
         trigger_error("gzdecode: checksum failed (real$chk != comp$CRC32)", E_USER_WARNING);
      }
      elseif ($ISIZE != strlen($gzdata)) {
         trigger_error("gzdecode: stream size mismatch", E_USER_WARNING);
      }
      else {
         return($gzdata);
      }
   }
}



/**
 * Probes for format(?) before decoding with one of the gz* functions.
 *
 */
if (!function_exists("zlib_decode")) {
   function zlib_decode($data) {
       if (!strncmp($data, "\x1F\x8B", 2)) {
           return gzdecode($data);
       }
       elseif (!strncmp($data, "\x78\x9C", 2)) {
           return gzuncompress($data);
       }
       else {
           return gzinflate($data);
       }
   }
}

/**
 * Weird constants, not documented in the manual yet, but that's how the function declaration looks.
 *
 */
if (!function_exists("zlib_encode")) {
   define("ZLIB_ENCODING_DEFLATE", 15);
   define("ZLIB_ENCODING_RAW", -15);
   define("ZLIB_ENCODING_GZIP", 31);
   function zlib_encode($data, $method) {
       if ($method == ZLIB_ENCODING_RAW) {
           return gzdeflate($data);
       }
       elseif ($method == ZLIB_ENCODING_DEFLATE) {
           return gzcompress($data);
       }
       elseif ($method == ZLIB_ENCODING_GZIP) {
           return gzencode($data);
       }
       else trigger_error("encoding mode must be either ZLIB_ENCODING_RAW, ZLIB_ENCODING_GZIP or ZLIB_ENCODING_DEFLATE", E_USER_WARNING);
   }
}


/**
 * @stub Tests whether a session is established.
 *
 */
if (!function_exists("session_status")) {
   define("PHP_SESSION_DISABLED", 0);
   define("PHP_SESSION_NONE", 1);
   define("PHP_SESSION_ACTIVE", 2);
   function session_status() {
       return (ini_get("session.name") != "") ? PHP_SESSION_DISABLED : 
           (isset($_SESSION) ? PHP_SESSION_ACTIVE : PHP_SESSION_NONE);
   }
}





/**
 *                                   ----------------------------- 5.3 ---
 * @group 5_3
 * @since 5.3
 *
 * Known additions of PHP 5.3
 *
 * @emulated
 *    ob_get_headers (stub)
 *    preg_filter
 *    lcfirst
 *    class_alias
 *    header_remove
 *    parse_ini_string
 *    array_replace
 *    array_replace_recursive
 *    str_getcsv
 *    forward_static_call
 *    forward_static_call_array
 *    quoted_printable_encode
 *    E_DEPRECATED
 *    E_USER_DEPRECATED
 *
 * @missing
 *    get_called_class
 *    stream_context_get_params
 *    stream_context_set_default
 *    stream_supports_lock
 *    hash_copy
 *    date_create_from_format
 *    date_parse_from_format
 *    date_get_last_errors
 *    date_add
 *    date_sub
 *    date_diff
 *    date_timestamp_set
 *    date_timestamp_get
 *    timezone_location_get
 *    date_interval_create_from_date_string
 *    date_interval_format
 *
 */



/**
 * @since PHP 5.3.0
 */
if (!defined('E_DEPRECATED')) { define('E_DEPRECATED', 8192); }
if (!defined('E_USER_DEPRECATED')) { define('E_USER_DEPRECATED', 16384); }


/**
 * preg_replace() variant, which filters out any unmatched $subject.
 *
 */
if (!function_exists("preg_filter")) {
   function preg_filter($pattern, $replacement, $subject, $limit=-1, $count=NULL) {

      // just do the replacing first, and eventually filter later
      $r = preg_replace($pattern, $replacement, $subject, $limit, $count);

      // look at subject lines one-by-one, remove from result per index
      foreach ((array)$subject as $si=>$s) {
         $any = 0;
         foreach ((array)$pattern as $p) {
            $any = $any ||preg_match($p, $s);
         }
         // remove if NONE of the patterns matched
         if (!$any) {
            if (is_array($r)) {
               unset($r[$si]);  // del from result array
            }
            else {
               return NULL;  // subject was a str
            }
         }
      }

      return $r;    // is already string if $subject was too
   }
}



/**
 * Lowercase first character.
 *
 * @param string
 * @return string
 */
if (!function_exists("lcfirst")) {
   function lcfirst($str) {
      return strlen($str) ? strtolower($str[0]) . substr($str, 1) : "";
   }
}



/**
 * @stub  cannot be emulated, because output buffering functions
 *        already swallow up any sent http header
 * @since 5.3.?
 *
 * get all ob_ soaked headers(),
 *
 */
if (!function_exists("ob_get_headers")) {
   function ob_get_headers() {
      return (array)NULL;
   }
}



/**
 * @stub  Cannot be emulated correctly, but let's try.
 *
 */
if (!function_exists("header_remove")) {
   function header_remove($name="") {
      if (strlen($name) and ($name = preg_replace("/[^-_.\w\d]+/", "", $name))) header("$name: \t");
      // Apache1.3? removed duplettes, empty header overrides previous.
      // ONLY if case was identical to previous header() call. (Very uncertain for applications which need to resort to such code smell.)
   }
}



/**
 * WTF?
 * At least an explaning reference was available on the php.net manual.
 * Why the parameters are supposed to be optional is a mystery.
 *
 */
if (!function_exists("class_alias")) {
   function class_alias($original, $alias) {
      $abstract = "";
      if (class_exists("ReflectionClass")) {
         $oc = new ReflectionClass($original);
         $abstract = $oc->isAbstract() ? "abstract" : "";
      }
      eval("$abstract class $alias extends $original { /* identical subclass */ }");
      return get_parent_class($alias) == $original;
   }
}




/**
 * Hey, reimplementin is fun.
 * (Could have used a data: wrapper for parse_ini_file, but that wouldn't work for php<5.2, and the data:// (!) wrapper is flaky anyway.)
 *
 */
if (!function_exists("parse_ini_string")) {
   function parse_ini_string($ini, $sectioned=false, $raw=0) {
      $r = array();
      $map = array("true"=>1, "yes"=>1, "1"=>1, "null"=>"", "false"=>"", "no"=>"", "0"=>0);
      $section = "";
      foreach (explode("\n", $ini) as $line) {
         if (!strlen($line)) {
         }
         // handle [sections]
         elseif (($line[0] == "[") and preg_match("/\[([-_\w ]+)\]/", $line, $uu)) {
            $section = $uu[1];
         }
         elseif (/*deprecated*/($line[0] != "#") && ($line[0] != ";") && ($i = strpos($line, "="))) {
            // key=value split
            $n = trim(substr($line, 0, $i));
            $v = trim(substr($line, $i+1));
            // replace special values
            if (!$raw) {
               $v=trim($v, '"');   // should actually use regex, to handle key="..\n.." multiline values
               $v=trim($v, "'");
               if (isset($map[$v])) {
                  $v=$map[$v];
               }
            }
            // special array[]= keys allowed
            if ($i = strpos($n, "[")) {
               $r[$section][substr($n, 0, $i)][] = $v;
            }
            else {
               $r[$section][$n] = $v;
            }
         }
      }
      return $sectioned ? $r : call_user_func_array("array_merge", $r);
   }
}




/**
 * Inject values from supplemental arrays into $target, according to its keys.
 *
 * @param array  $targt
 * @param+ array $supplements
 * @return array
 */
if (!function_exists("array_replace")) {
   function array_replace(/* & (?) */$target/*, $from, $from2, ...*/) {
      $merge = func_get_args();
      array_shift($merge);
      foreach ($merge as $add) {
         foreach ($add as $i=>$v) {
            $target[$i] = $v;
         }
      }
      return $target;
   }
}


/**
 * Descends into sub-arrays when replacing values by key in $target array.
 *
 */
if (!function_exists("array_replace_recursive")) {
   function array_replace_recursive($target/*, $from1, $from2, ...*/) {
      $merge = func_get_args();
      array_shift($merge);

      // loop through all merge arrays
      foreach ($merge as $from) {
         foreach ($from as $i=>$v) {
            // just add (wether array or scalar) if key does not exist yet
            if (!isset($target[$i])) {
               $target[$i] = $v;
            }
            // dive in
            elseif (is_array($v) && is_array($target[$i])) {
               $target[$i] = array_replace_recursive($target[$i], $v);
            }
            // replace
            else {
               $target[$i] = $v;
            }
         }
      }
      return $target;
   }
}




/**
 * Breaks up a SINGLE LINE in CSV format.
 * abc,123,"text with spaces",xy,"\""
 *
 */
if (!function_exists("str_getcsv")) {
   function str_getcsv($line, $del=",", $q='"', $esc="\\", $rm_spaces="\s*") {
      $line = rtrim($line, "\r\n");
      preg_match_all("/\G $rm_spaces ([^$q$del]*?) $rm_spaces $del | $q(( [$esc$esc][$q]|[^$q]* )+)$q \s* $del /xms", $line.$del, $r);
      foreach ($r[1] as $i=>$v) {  // merge both captures
         if (empty($v) && strlen($r[2][$i])) {
            $r[1][$i] = str_replace("$esc$q", "$q", $r[2][$i]);  // remove escape character
         }            # use stripcslashes to support standard CSV \r \n escapes
      }
      return($r[1]);
   }
}



/**
 * @stub: Basically aliases for function calls; just throw an error if called from main() and not from within a class.
 * The real implementations would behave on late static binding, though.
 *
 */
if (!function_exists("forward_static_call")) {
   function forward_static_call_array($callback, $args=NULL) {
      return call_user_func_array($callback, $args);
   }
   function forward_static_call($callback /*, ... */) {
      $args = func_get_args();
      array_shift($args);
      return call_user_func_array($callback, $args);
   }
}




/**
 * Encodes special chars as =0D=0A patterns. Soft-break at 76 characters.
 *
 */
if (!function_exists("quoted_printable_encode")) {
   function quoted_printable_encode($str) {
      $str = preg_replace("/([\\000-\\041=\\176-\\377])/e", "'='.strtoupper(dechex(ord('\$1')))", $str);
      $str = preg_replace("/(.{1,76})(?<=[^=][^=])/ims", "\$1=\r\n", $str); // QP-soft-break
      return $str;
   }
}













/**
 *                                   ------------------------------ 5.2 ---
 * @group 5_2
 * @since 5.2
 *
 * Additions of PHP 5.2.0
 * - some listed here might have appeared earlier or in release candidates
 *
 * @emulated
 *    json_encode
 *    json_decode
 *    error_get_last
 *    preg_last_error
 *    lchown
 *    lchgrp
 *    E_RECOVERABLE_ERROR
 *    M_SQRTPI
 *    M_LNPI
 *    M_EULER
 *    M_SQRT3
 *    array_fill_keys  (@doc: 4.2 or 5.2 ?)
 *    array_diff_key   (@doc: 5.1 or 5.2 ?)
 *    array_diff_ukey
 *    array_product
 *    inet_ntop
 *    inet_pton
 *    array_intersect_key
 *    array_intersect_ukey
 *    mysql_set_charset
 *
 * @missing
 *    sys_getloadavg
 *    ftp_ssl_connect
 *    XmlReader
 *    XmlWriter
 *    PDO*
 *    pdo_drivers     (should be in ext/pdo)
 *
 * @unimplementable
 *    stream_*
 *
 */





/**
 * @since unknown
 */
if (!defined("E_RECOVERABLE_ERROR")) { define("E_RECOVERABLE_ERROR", 4096); }



/**
 * Converts PHP variable or array into a "JSON" (JavaScript value expression
 * or "object notation") string.
 *
 * @compat
 *    Output seems identical to PECL versions. "Only" 20x slower than PECL version.
 * @bugs
 *    Doesn't take care with unicode too much - leaves UTF-8 sequences alone.
 *
 * @param  $var mixed  PHP variable/array/object
 * @return string      transformed into JSON equivalent
 */
if (!defined("JSON_HEX_TAG")) {
   define("JSON_HEX_TAG", 1);
   define("JSON_HEX_AMP", 2);
   define("JSON_HEX_APOS", 4);
   define("JSON_HEX_QUOT", 8);
   define("JSON_FORCE_OBJECT", 16);
}
if (!defined("JSON_NUMERIC_CHECK")) {
   define("JSON_NUMERIC_CHECK", 32);      // 5.3.3
}
if (!defined("JSON_UNESCAPED_SLASHES")) {
   define("JSON_UNESCAPED_SLASHES", 64);  // 5.4.0
   define("JSON_PRETTY_PRINT", 128);      // 5.4.0
   define("JSON_UNESCAPED_UNICODE", 256); // 5.4.0
}
if (!function_exists("json_encode")) {
   function json_encode($var, $options=0, $_indent="") {

      #-- prepare JSON string
      $obj = ($options & JSON_FORCE_OBJECT);
      list($_space, $_tab, $_nl) = ($options & JSON_PRETTY_PRINT) ? array(" ", "    $_indent", "\n") : array("", "", "");
      $json = "$_indent";
      
      if ($options & JSON_NUMERIC_CHECK and is_string($var) and is_numeric($var)) {
          $var = (strpos($var, ".") || strpos($var, "e")) ? floatval($var) : intval($var);
      }
      
      #-- add array entries
      if (is_array($var) || ($obj=is_object($var))) {

         #-- check if array is associative
         if (!$obj) {
            $keys = array_keys((array)$var);
            $obj = !($keys == array_keys($keys));   // keys must be in 0,1,2,3, ordering, but PHP treats integers==strings otherwise
         }

         #-- concat individual entries
         $empty = 0; $json = "";
         foreach ((array)$var as $i=>$v) {
            $json .= ($empty++ ? ",$_nl" : "")    // comma separators
                   . $_tab . ($obj ? (json_encode($i, $options, $_tab) . ":$_space") : "")   // assoc prefix
                   . (json_encode($v, $options, $_tab));    // value
         }

         #-- enclose into braces or brackets
         $json = $obj ? "{"."$_nl$json$_nl$_indent}" : "[$_nl$json$_nl$_indent]";
      }

      #-- strings need some care
      elseif (is_string($var)) {

         if (!utf8_decode($var)) {
            trigger_error("json_encode: invalid UTF-8 encoding in string, cannot proceed.", E_USER_WARNING);
            $var = NULL;
         }
         $rewrite = array(
             "\\" => "\\\\",
             "\"" => "\\\"",
           "\010" => "\\b",
             "\f" => "\\f",
             "\n" => "\\n",
             "\r" => "\\r", 
             "\t" => "\\t",
             "/"  => $options & JSON_UNESCAPED_SLASHES ? "/" : "\\/",
             "<"  => $options & JSON_HEX_TAG  ? "\\u003C" : "<",
             ">"  => $options & JSON_HEX_TAG  ? "\\u003E" : ">",
             "'"  => $options & JSON_HEX_APOS ? "\\u0027" : "'",
             "\"" => $options & JSON_HEX_QUOT ? "\\u0022" : "\"",
             "&"  => $options & JSON_HEX_AMP  ? "\\u0026" : "&",
         );
         $var = strtr($var, $rewrite);
         if (function_exists("iconv") && ($options & JSON_UNESCAPED_UNICODE) == 0) {
            $var = preg_replace("/[^\\x{0000}-\\x{007F}]/ue", "'\\u'.current(unpack('H*', iconv('UTF-8', 'UCS-2BE', '$0')))", $var);
         }
         if ($options & 0x8000) {//@COMPAT: for fully-fully-compliance
            $var = preg_replace("/[\000-\037]/", "", $var);
         }
         $json = '"' . $var . '"';
      }

      #-- basic types
      elseif (is_bool($var)) {
         $json = $var ? "true" : "false";
      }
      elseif ($var === NULL) {
         $json = "null";
      }
      elseif (is_int($var) || is_float($var)) {
         $json = "$var";
      }

      #-- something went wrong
      else {
         trigger_error("json_encode: don't know what a '" .gettype($var). "' is.", E_USER_WARNING);
      }
      
      #-- done
      return($json);
   }
}


/**
 * Parses a JSON (JavaScript value expression) string into a PHP variable
 * (array or object).
 *
 * @compat
 *    Behaves similar to PECL version, but is less quiet on errors.
 *    Now even decodes unicode \uXXXX string escapes into UTF-8.
 *    "Only" 27 times slower than native function.
 * @bugs
 *    Might parse some misformed representations, when other implementations
 *    would scream error or explode.
 * @code
 *    This is state machine spaghetti code. Needs the extranous parameters to
 *    process subarrays, etc. When it recursively calls itself, $n is the
 *    current position, and $waitfor a string with possible end-tokens.
 *
 * @param   $json string   JSON encoded values
 * @param   $assoc bool    pack data into php array/hashes instead of objects
 * @return  mixed          parsed into PHP variable/array/object
 */
if (!function_exists("json_decode")) {

   define("JSON_OBJECT_AS_ARRAY", 1);     // undocumented
   define("JSON_BIGINT_AS_STRING", 2);    // 5.4.0
   define("JSON_PARSE_JAVASCRIPT", 4);    // unquoted object keys, and single quotes ' strings identical to double quoted, more relaxed parsing

   function json_decode($json, $assoc=FALSE, $limit=512, $options=0, /*emu_args*/$n=0,$state=0,$waitfor=0) {
      global ${'.json_last_error'}; ${'.json_last_error'} = JSON_ERROR_NONE;

      #-- result var
      $val = NULL;
      $FAILURE = array(/*$val:=*/ NULL, /*$n:=*/ 1<<31);
      static $lang_eq = array("true" => TRUE, "false" => FALSE, "null" => NULL);
      static $str_eq = array("n"=>"\012", "r"=>"\015", "\\"=>"\\", '"'=>'"', "f"=>"\f", "b"=>"\010", "t"=>"\t", "/"=>"/");
      if ($limit<0) { ${'.json_last_error'} = JSON_ERROR_DEPTH; return /* __cannot_compensate */; }
      
      #-- strip UTF-8 BOM (the native version doesn't do this, but .. should)
      while (strncmp($json, "\xEF\xBB\xBF", 3) == 0) {
          trigger_error("UTF-8 BOM prefaces JSON, that's invalid for PHPs native json_decode", E_USER_ERROR);
          $json = substr($json, 3);
      }

      #-- flat char-wise parsing
      for (/*$n=0,*/ $len = strlen($json); $n<$len; /*$n++*/) {
         $c = $json[$n];

         #-= in-string
         if ($state==='"' or $state==="'") {

            if ($c == '\\') {
               $c = $json[++$n];
               
               // simple C escapes
               if (isset($str_eq[$c])) {
                  $val .= $str_eq[$c];
               }

               // here we transform \uXXXX Unicode (always 4 nibbles) references to UTF-8
               elseif ($c == "u") {
                  // read just 16bit (therefore value can't be negative)
                  $hex = hexdec( substr($json, $n+1, 4) );
                  $n += 4;
                  // Unicode ranges
                  if ($hex < 0x80) {    // plain ASCII character
                     $val .= chr($hex);
                  }
                  elseif ($hex < 0x800) {   // 110xxxxx 10xxxxxx 
                     $val .= chr(0xC0 + $hex>>6) . chr(0x80 + $hex&63);
                  }
                  elseif ($hex <= 0xFFFF) { // 1110xxxx 10xxxxxx 10xxxxxx 
                     $val .= chr(0xE0 + $hex>>12) . chr(0x80 + ($hex>>6)&63) . chr(0x80 + $hex&63);
                  }
                  // other ranges, like 0x1FFFFF=0xF0, 0x3FFFFFF=0xF8 and 0x7FFFFFFF=0xFC do not apply
               }

               // for JS (not JSON) the extraneous backslash just gets omitted
               elseif ($options & JSON_PARSE_JAVASCRIPT) {
                  if (is_numeric($c) and preg_match("/[0-3][0-7][0-7]|[0-7]{1,2}/", substr($json, $n), $m)) {
                     $val .= chr(octdec($m[0]));
                     $n += strlen($m[0]) - 1;
                  }
                  else {
                     $val .= $c;
                  }
               }
               
               // redundant backslashes disallowed in JSON
               else {
                  $val .= "\\$c";
                  ${'.json_last_error'} = JSON_ERROR_CTRL_CHAR; // not quite, but
                  trigger_error("Invalid backslash escape for JSON \\$c", E_USER_WARNING);
                  return $FAILURE;
               }
            }

            // end of string
            elseif ($c == $state) {
               $state = 0;
            }

            //@COMPAT: specialchars check - but native json doesn't do it?
            #elseif (ord($c) < 32) && !in_array($c, $str_eq)) {
            #   ${'.json_last_error'} = JSON_ERROR_CTRL_CHAR;
            #}
            
            // a single character was found
            else/*if (ord($c) >= 32)*/ {
               $val .= $c;
            }
         }

         #-> end of sub-call (array/object)
         elseif ($waitfor && (strpos($waitfor, $c) !== false)) {
            return array($val, $n);  // return current value and state
         }
         
         #-= in-array
         elseif ($state===']') {
            list($v, $n) = json_decode($json, $assoc, $limit, $options, $n, 0, ",]");
            $val[] = $v;
            if ($json[$n] == "]") { return array($val, $n); }
         }

         #-= in-object
         elseif ($state==='}') {
            // quick regex parsing cheat for unquoted JS object keys
            if ($options & JSON_PARSE_JAVASCRIPT and $c != '"' and preg_match("/^\s*(?!\d)(\w\pL*)\s*/u", substr($json, $n), $m)) {
                $i = $m[1];
                $n = $n + strlen($m[0]);
            }
            else {
                // this allowed non-string indicies
                list($i, $n) = json_decode($json, $assoc, $limit, $options, $n, 0, ":");
            }
            list($v, $n) = json_decode($json, $assoc, $limit, $options, $n+1, 0, ",}");
            $val[$i] = $v;
            if ($json[$n] == "}") { return array($val, $n); }
         }

         #-- looking for next item (0)
         else {
         
            #-> whitespace
            if (preg_match("/\s/", $c)) {
               // skip
            }

            #-> string begin
            elseif ($c == '"') {
               $state = $c;
            }

            #-> object
            elseif ($c == "{") {
               list($val, $n) = json_decode($json, $assoc, $limit-1, $options, $n+1, '}', "}");
               
               if ($val && $n) {
                  $val = $assoc ? (array)$val : (object)$val;
               }
            }

            #-> array
            elseif ($c == "[") {
               list($val, $n) = json_decode($json, $assoc, $limit-1, $options, $n+1, ']', "]");
            }

            #-> numbers
            elseif (preg_match("#^(-?\d+(?:\.\d+)?)(?:[eE]([-+]?\d+))?#", substr($json, $n), $uu)) {
               $val = $uu[1];
               $n += strlen($uu[0]) - 1;
               if (strpos($val, ".")) {  // float
                  $val = floatval($val);
               }
               elseif ($val[0] == "0") {  // oct
                  $val = octdec($val);
               }
               else {
                  $toobig = strval(intval($val)) !== strval($val);
                  if ($toobig and !isset($uu[2]) and ($options & JSON_BIGINT_AS_STRING)) {
                      $val = $val;  // keep lengthy numbers as string
                  }
                  elseif ($toobig or isset($uu[2])) {  // must become float anyway
                      $val = floatval($val);
                  }
                  else {  // int
                      $val = intval($val);
                  }
               }
               // exponent?
               if (isset($uu[2])) {
                  $val *= pow(10, (int)$uu[2]);
               }
            }

            #-> boolean or null
            elseif (preg_match("#^(true|false|null)\b#", substr($json, $n), $uu)) {
               $val = $lang_eq[$uu[1]];
               $n += strlen($uu[1]) - 1;
            }

            #-> JS-string begin
            elseif ($options & JSON_PARSE_JAVASCRIPT and $c == "'") {
               $state = $c;
            }

            #-> comment
            elseif ($options & JSON_PARSE_JAVASCRIPT and ($c == "/") and ($json[$n+1]=="*")) {
               // just find end, skip over
               ($n = strpos($json, "*/", $n+1)) or ($n = strlen($json));
            }

            #-- parsing error
            else {
               // PHPs native json_decode() breaks here usually and QUIETLY
               trigger_error("json_decode: error parsing '$c' at position $n", E_USER_WARNING);
               ${'.json_last_error'} = JSON_ERROR_SYNTAX;
               return $waitfor ? $FAILURE : NULL;
            }

         }//state
         
         #-- next char
         if ($n === NULL) { ${'.json_last_error'} = JSON_ERROR_STATE_MISMATCH; return NULL; }   // ooops, seems we have two failure modes
         $n++;
      }//for

      #-- final result
      return ($val);
   }
}


/**
 * @stub
 *
 * Should return last JSON decoding error.
 *
 */
if (!defined("JSON_ERROR_NONE")) {
   define("JSON_ERROR_NONE", 0);
   define("JSON_ERROR_DEPTH", 1);
   define("JSON_ERROR_STATE_MISMATCH", 2);
   define("JSON_ERROR_CTRL_CHAR", 3);
   define("JSON_ERROR_SYNTAX", 4);
   define("JSON_ERROR_UTF8", 5);
}
if (!function_exists("json_last_error")) {
   function json_last_error() {
       global ${'.json_last_error'}; 
       return ${'.json_last_error'}; // gives a notice if json_decode was never invoked before (no status constant for that)
   }
}



/**
 * @stub
 *
 * Should return last PCRE error.
 *
 */
if (!function_exists("preg_last_error")) {
   if (!defined("PREG_NO_ERROR")) { define("PREG_NO_ERROR", 0); }
   if (!defined("PREG_INTERNAL_ERROR")) { define("PREG_INTERNAL_ERROR", 1); }
   if (!defined("PREG_BACKTRACK_LIMIT_ERROR")) { define("PREG_BACKTRACK_LIMIT_ERROR", 2); }
   if (!defined("PREG_RECURSION_LIMIT_ERROR")) { define("PREG_RECURSION_LIMIT_ERROR", 3); }
   if (!defined("PREG_BAD_UTF8_ERROR")) { define("PREG_BAD_UTF8_ERROR", 4); }
   function preg_last_error() {
      return PREG_NO_ERROR;
   }
}




/**
 * returns path of the system directory for temporary files
 *
 * @since 5.2.1
 */
if (!function_exists("sys_get_temp_dir")) {
   function sys_get_temp_dir() {
      # check possible alternatives
      ($temp = ini_get("temp_dir"))
      or
      ($temp = @$_ENV["TMPDIR"])
      or
      ($temp = @$_ENV["TEMP"])
      or
      ($temp = @$_ENV["TMP"])
      or
      ($temp = "/tmp");
      # fin
      return($temp);
   }
}



/**
 * @stub
 *
 * Should return associative array with last error message.
 *
 */
if (!function_exists("error_get_last")) {
   function error_get_last() {
      return array(
         "type" => 0,
         "message" => $GLOBALS["php_errormsg"],
         "file" => "unknonw",
         "line" => 0,
      );
   }
}




/**
 * @flag quirky, exec, realmode
 *
 * Change owner of a symlink filename.
 *
 */
if (!function_exists("lchown")) {
   function lchown($fn, $user) {
      if (PHP_OS != "Linux") {
         return false;
      }
      $user = escapeshellcmd($user);
      $fn = escapeshellcmd($fn);
      exec("chown -h '$user' '$fn'", $uu, $state);
      return($state);
   }
}



/**
 * @flag quirky, exec, realmode
 *
 * Change group of a symlink filename.
 *
 */
if (!function_exists("lchgrp")) {
   function lchgrp($fn, $group) {
      return lchown($fn, ":$group");
   }
}



/**
 * @doc: Got this function new in PHP 5.2, but documentation says 4.2 ???
 * 
 * array_fill() with given $keys
 *
 */
if (!function_exists("array_fill_keys")) {
   function array_fill_keys($keys, $value) {
      return array_combine($keys, array_fill(0, count($keys), $value));
   }
}



/**
 * @doc: php manual says 5.1, but function appeared with 5.2
 *
 * Returns array entries, whose keys are not in any of the comparison arrays.
 *
 */
if (!function_exists("array_diff_key")) {
   function array_diff_key($base /*...*/) {
      $other = func_get_args();
      array_shift($other);

      $cmp = call_user_func_array("array_merge", array_map("array_keys", $other));

      foreach ($cmp as $key) {
            $key = (string) $key;
            if (array_key_exists($key, $base)) {
               // cannot compare if $key is actually a string in $base
               unset($base[$key]);
            }
      }
      return ($base);
   }
}




/**
 * @doc: php manual says 5.1, but function appeared with 5.2
 *
 * Uses callback function to compare array keys.
 * Callback returns -1, 0, +1, and then some keys are filtered???
 * Let's assume ==0 is meant for no difference --> and no difference => filter out
 *
 */
if (!function_exists("array_diff_ukey")) {
   function array_diff_ukey($base, $other_arrays/*...*/, $callback) {
      $other = func_get_args();
      array_shift($other);
      $callback = array_pop($other);
      
      $cmp = call_user_func_array("array_merge", array_map("array_keys", $other));

      foreach ($base as $key=>$value) {
         // compare against each key from $other arrays
         foreach ($cmp as $k) {
            if ($callback($key, $k) === 0) {
               unset($base[$key]);
            }
         }
      }
      return $base;      
   }
}



/**
 * @doc: 5.1 vs 5.2
 *
 * Keeps only array-entries, if key exists also in comparison arrays
 *
 */
if (!function_exists("array_intersect_key")) {
   function array_intersect_key($base /*...*/) {
      $all_arrays = array_map("array_keys", func_get_args());
      $keep = call_user_func_array("array_intersect", $all_arrays);
      
      $r = array();
      foreach ($keep as $k) {
         $r[$k] = $base[$k];
      }
      return ($r);
   }
}



/**
 * @doc: 5.1 vs 5.2
 *
 * array_uintersect on keys
 *
 */
if (!function_exists("array_intersect_ukey")) {
   function array_intersect_ukey(/*...*/) {
      $args = func_get_args();
      $base = $args[0];
      $callback = array_pop($other);

      $keys = array_map("array_values", $args);
      $intersect = call_user_func_array("array_uintersect", array_merge($keys, array($callback)));
      
      $r = array();
      foreach ($intersect as $key) {
         $r[$key] = $base[$key];
      }
      return $r;
   }
}







/**
 * Hmmm.
 *
 */
if (!function_exists("array_product")) {
   function array_product($multiply_us) {
      $r = count($multiply_us) ? 1 : NULL;
      foreach ($multiply_us as $m) {
         $r = $r * $m;
      }
      return $r;
   }
}



/**
 * Converts chr/bin/string-representation to human-readable IP text.
 *
 */
if (!function_exists("inet_ntop")) {
   function inet_ntop($bin) {
      if (strlen($bin) == 4) {   // IPv4
         return implode(".", array_map("ord", str_split($bin, 1)));
      }
      elseif (strlen($bin) == 16) {  // IPv6
         return preg_replace("/:?(0000:)+/", "::", implode(":", str_split(bin2hex($bin), 4)));
      }
      elseif (strlen($bin) == 6) {  // MAC
         return implode(":", str_split(bin2hex($bin), 2));
      }
   }
}


/**
 * Compact IPv4 1.2.3.4 or IPv6 ::FFFF:0001 addresses into binary string.
 *
 */
if (!function_exists("inet_pton")) {
   function inet_pton($str) {
      if (strpos($str, ".")) {  // IPv4
         return array_map("chr", explode(".", $str));
      }
      elseif (strstr($str, ":")) { // IPv6
         $str = str_replace("::", str_repeat(":", 2 + 7 - substr_count($str, ":")), $str);   // padding "::" can appear anywhere inside, replaces 7-x other :0000 colons and zeros
         $str = implode(array_map("inet_pton___ipv6_pad", explode(":", $str)));
         return pack("H32", $str);
      }
   }
   function inet_pton___ipv6_pad($s) {
      return str_pad($s, 4, "0", STR_PAD_LEFT);
   }
}


/**
 * @since 5.2.3
 * SET NAMES $charset
 *
 */
if (!function_exists("mysql_set_charset")) {
   function mysql_set_charset($charset, $link=NULL) {
      return mysqli_query("SET NAMES '$charset'", $link);
   }
}




/**
 *                                   ------------------------------ 5.1 ---
 * @group 5_1
 * @since 5.1
 *
 * Additions in PHP 5.1
 * - most functions here appeared in -rc1 already
 * - and were backported to 4.4 series?
 *
 * @emulated
 *    hash_hmac
 *    property_exists
 *    time_sleep_until
 *    fputcsv
 *    strptime
 *    ENT_COMPAT
 *    ENT_QUOTES
 *    ENT_NOQUOTES
 *    htmlspecialchars_decode
 *    PHP_INT_SIZE
 *    PHP_INT_MAX
 *    M_SQRTPI
 *    M_LNPI
 *    M_EULER
 *    M_SQRT3
 *
 * @missing
 *    strptime
 *
 * @unimplementable
 *    ...
 *
 */



/**
 * HMAC as per rfc2104,
 * only works with PHP-available "md5" and "sha1" algorithm backends
 *
 */
if (!function_exists("hash_hmac")) {
   function hash_hmac($H, $data, $key, $raw=0) {

       # algorithm parameters
       static $bitsize = array("sha1"=>160, "md5"=>128, "sha256"=>256, "sha512"=>512, "sha384"=>384, "sha224"=>224, "ripemd"=>160);
       $B = 64;

       # bring key to block size 64, hash it first if longer
       if (strlen($key) > $B) {
           $key = $H($key, 1);
       }
       $key .= str_repeat("\0", $B - strlen($key));

       # padding, XOR with key
       $inner_pad = "";
       $outer_pad = "";
       for ($i=0; $i<$B; $i++) {
           $inner_pad .= chr(0x36 ^ ord($key[$i]));
           $outer_pad .= chr(0x5C ^ ord($key[$i]));
       }

       # apply hash
       $data = $H($outer_pad . $H($inner_pad . $data, 1), 1);
       
       # bin2hex
       return $raw ? $data : bin2hex($data);
   }
}



/**
 * Constants for future 64-bit integer support.
 *
 */
if (!defined("PHP_INT_SIZE")) { define("PHP_INT_SIZE", 4); }
if (!defined("PHP_INT_MAX")) { define("PHP_INT_MAX", 2147483647); }



/**
 * @flag bugfix
 * @see #33895
 *
 * Missing constants in 5.1, originally appeared in 4.0.
 */
if (!defined("M_SQRTPI")) { define("M_SQRTPI", 1.7724538509055); }
if (!defined("M_LNPI")) { define("M_LNPI", 1.1447298858494); }
if (!defined("M_EULER")) { define("M_EULER", 0.57721566490153); }
if (!defined("M_SQRT3")) { define("M_SQRT3", 1.7320508075689); }




/**
 * removes entities &lt; &gt; &amp; and eventually &quot; from HTML string
 *
 */
if (!function_exists("htmlspecialchars_decode")) {
   if (!defined("ENT_COMPAT")) { define("ENT_COMPAT", 2); }
   if (!defined("ENT_QUOTES")) { define("ENT_QUOTES", 3); }
   if (!defined("ENT_NOQUOTES")) { define("ENT_NOQUOTES", 0); }
   function htmlspecialchars_decode($string, $quotes=2) {
      $d = $quotes & ENT_COMPAT;
      $s = $quotes & ENT_QUOTES;
      return str_replace(
         array("&lt;", "&gt;", ($s ? "&quot;" : "&.-;"), ($d ? "&#039;" : "&.-;"), "&amp;"),
         array("<",    ">",    "'",                      "\"",                     "&"),
         $string
      );
   }
}



/**
 * @flag needs5
 *
 * Checks for existence of object property, should return TRUE even for NULL values.
 *
 * @compat
 *    no test for edge cases
 */
if (!function_exists("property_exists")) {
   function property_exists($obj, $propname) {
      if (is_object($obj)) {
         $props = array_keys(get_object_vars($obj));
      }
      elseif (class_exists($obj)) {
         $props = array_keys(get_class_vars($obj));
      }
      return !empty($props) and in_array($propname, $props);
   }
}



/**
 * halt execution, until given timestamp
 *
 */
if (!function_exists("time_sleep_until")) {
   function time_sleep_until($t) {
      $delay = $t - time();
      if ($delay < 0) {
         trigger_error("time_sleep_until: timestamp in the past", E_USER_WARNING);
         return false;
      }
      else {
         sleep((int)$delay);
         #usleep(($delay - floor($delay)) * 1000000);
         return true;
      }
   }
}



/**
 * @untested
 *
 * Writes an array as CSV text line into opened filehandle.
 *
 */
if (!function_exists("fputcsv")) {
   function fputcsv($fp, $fields, $delim=",", $encl='"') {
      $line = "";
      foreach ((array)$fields as $str) {
         $line .= ($line ? $delim : "")
                . $encl
                . str_replace(array('\\', $encl), array('\\\\'. '\\'.$encl), $str)
                . $encl;
      }
      fwrite($fp, $line."\n");
   }
}



/**
 * @flag basic
 * @untested
 *
 * @compat
 *    only implements a few basic regular expression lookups
 *    no idea how to handle all of it
 */
if (!function_exists("strptime")) {
   function strptime($str, $format) {
      static $expand = array(
         "%D" => "%m/%d/%y",
         "%T" => "%H:%M:%S",
      );
      static $map_r = array(
          "%S"=>"tm_sec",
          "%M"=>"tm_min",
          "%H"=>"tm_hour",
          "%d"=>"tm_mday",
          "%m"=>"tm_mon",
          "%Y"=>"tm_year",
          "%y"=>"tm_year",
          "%W"=>"tm_wday",
          "%D"=>"tm_yday",
          "%u"=>"unparsed",
      );
      static $names = array(
         "Jan" => 1, "Feb" => 2, "Mar" => 3, "Apr" => 4, "May" => 5, "Jun" => 6,
         "Jul" => 7, "Aug" => 8, "Sep" => 9, "Oct" => 10, "Nov" => 11, "Dec" => 12,
         "Sun" => 0, "Mon" => 1, "Tue" => 2, "Wed" => 3, "Thu" => 4, "Fri" => 5, "Sat" => 6,
      );

      #-- transform $format into extraction regex
      $format = str_replace(array_keys($expand), array_values($expand), $format);
      $preg = preg_replace("/(%\w)/", "(\w+)", preg_quote($format));

      #-- record the positions of all STRFCMD-placeholders
      preg_match_all("/(%\w)/", $format, $positions);
      $positions = $positions[1];
      
      #-- get individual values
      if (preg_match("#$preg#", "$str", $extracted)) {

         #-- get values
         foreach ($positions as $pos=>$strfc) {
            $v = $extracted[$pos + 1];

            #-- add
            if ($n = $map_r[$strfc]) {
               $vals[$n] = ($v > 0) ? (int)$v : $v;
            }
            else {
               $vals["unparsed"] .= $v . " ";
            }
         }
         
         #-- fixup some entries
         $vals["tm_wday"] = $names[ substr($vals["tm_wday"], 0, 3) ];
         if ($vals["tm_year"] >= 1900) {
            $tm_year -= 1900;
         }
         elseif ($vals["tm_year"] > 0) {
            $vals["tm_year"] += 100;
         }
         if ($vals["tm_mon"]) {
            $vals["tm_mon"] -= 1;
         }
         else {
            $vals["tm_mon"] = $names[ substr($vals["tm_mon"], 0, 3) ] - 1;
         }
         
         #-- calculate wday
         // ... (mktime)
      }
      return isset($vals) ? $vals : false;
   }
}













/**
 *                                   ------------------------------ 5.0 ---
 * @group 5_0
 * @since 5.0
 *
 * PHP 5.0 introduces the Zend Engine 2 with new object-orientation features
 * which cannot be reimplemented/defined for PHP4. The additional procedures
 * and functions however can.
 *
 * @emulated
 *    stripos
 *    strripos
 *    str_ireplace
 *    get_headers
 *    headers_list
 *    fprintf
 *    vfprintf
 *    str_split
 *    http_build_query
 *    convert_uuencode
 *    convert_uudecode
 *    scandir
 *    idate
 *    time_nanosleep
 *    strpbrk
 *    get_declared_interfaces
 *    array_combine
 *    array_walk_recursive
 *    substr_compare
 *    spl_classes
 *    class_parents
 *    session_commit
 *    dns_check_record
 *    dns_get_mx
 *    setrawcookie
 *    file_put_contents
 *    COUNT_NORMAL
 *    COUNT_RECURSIVE
 *    count_recursive
 *    FILE_USE_INCLUDE_PATH
 *    FILE_IGNORE_NEW_LINES
 *    FILE_SKIP_EMPTY_LINES
 *    FILE_APPEND
 *    FILE_NO_DEFAULT_CONTEXT
 *    E_STRICT
 *    mysqli_set_charset
 *
 * @missing
 *    proc_nice
 *    dns_get_record
 *    date_sunrise - undoc.
 *    date_sunset - undoc.
 *    PHP_CONFIG_FILE_SCAN_DIR
 *    clone
 *
 * @unimplementable
 *    set_exception_handler
 *    restore_exception_handler
 *    debug_print_backtrace - in ext, needs4.3
 *    debug_backtrace       - stub
 *    class_implements
 *    proc_terminate
 *    proc_get_status
 *    range        - new param
 *    microtime    - new param
 *
 */
 
 


#-- constant: end of line
if (!defined("PHP_EOL")) { define("PHP_EOL", ( (DIRECTORY_SEPARATOR == "\\") ? "\015\012" : (strncmp(PHP_OS, "D", 1) ? "\012" : "\015") )  ); } # "D" for Darwin



/**
 * case-insensitive string search function,
 * - finds position of first occourence of a string c-i
 * - parameters identical to strpos()
 */
if (!function_exists("stripos")) {
   function stripos($haystack, $needle, $offset=NULL) {
   
      #-- simply lowercase args
      $haystack = strtolower($haystack);
      $needle = strtolower($needle);
      
      #-- search
      $pos = strpos($haystack, $needle, $offset);
      return($pos);
   }
}




/**
 * case-insensitive string search function
 * - but this one starts from the end of string (right to left)
 * - offset can be negative or positive
 * 
 */
if (!function_exists("strripos")) {
   function strripos($haystack, $needle, $offset=NULL) {

      #-- lowercase incoming strings
      $haystack = strtolower($haystack);
      $needle = strtolower($needle);

      #-- [-]$offset tells to ignore a few string bytes,
      #   we simply cut a bit from the right
      if (isset($offset) && ($offset < 0)) {
         $haystack = substr($haystack, 0, strlen($haystack) - 1);
      }

      #-- let PHP do it
      $pos = strrpos($haystack, $needle);

      #-- [+]$offset => ignore left haystack bytes
      if (isset($offset) && ($offset > 0) && ($pos > $offset)) {
         $pos = false;
      }

      #-- result      
      return($pos);
   }
}


/**
 * case-insensitive version of str_replace
 * 
 */
if (!function_exists("str_ireplace")) {
   function str_ireplace($search, $replace, $subject, $count=NULL) {

      #-- call ourselves recursively, if parameters are arrays/lists 
      if (is_array($search)) {
         $replace = array_values($replace);
         foreach (array_values($search) as $i=>$srch) {
            $subject = str_ireplace($srch, $replace[$i], $subject);
         }
      }
      
      #-- sluice replacement strings through the Perl-regex module
      #   (faster than doing it by hand)
      else {
         $replace = addcslashes($replace, "$\\");
         $search = "{" . preg_quote($search) . "}i";
         $subject = preg_replace($search, $replace, $subject);
      }

      #-- result
      return($subject);
   }
}


/**
 * performs a http HEAD request
 * 
 */
if (!function_exists("get_headers")) {
   function get_headers($url, $parse=0) {
   
      #-- extract URL parts ($host, $port, $path, ...)
      $c = parse_url($url);
      $c = array_merge(array("port"=>"80", "path"=>"/"), $c);
      extract($c);
      
      #-- try to open TCP connection      
      $f = fsockopen($host, $port, $errno, $errstr, $timeout=15);
      if (!$f) {
         return;
      }

      #-- send request header
      socket_set_blocking($f, true);
      fwrite($f, "HEAD $path HTTP/1.0\015\012"
               . "Host: $host\015\012"
               . "Connection: close\015\012"
               . "Accept: */*, xml/*\015\012"
               . "User-Agent: ".trim(ini_get("user_agent"))."\015\012"
               . "\015\012");

      #-- read incoming lines
      $ls = array();
      while ( !feof($f) && ($line = trim(fgets($f, 1<<16))) ) {
         
         #-- read header names to make result an hash (names in array index)
         if ($parse) {
            if ($l = strpos($line, ":")) {
               $name = substr($line, 0, $l);
               $value = trim(substr($line, $l + 1));
               #-- merge headers
               if (isset($ls[$name])) {
                  $ls[$name] .= ", $value";
               }
               else {
                  $ls[$name] = $value;
               }
            }
            #-- HTTP response status header as result[0]
            else {
               $ls[] = $line;
            }
         }
         
         #-- unparsed header list (numeric indices)
         else {
            $ls[] = $line;
         }
      }

      #-- close TCP connection and give result
      fclose($f);
      return($ls);
   }
}


/**
 * @stub
 * list of already/potentially sent HTTP responsee headers(),
 * CANNOT be implemented (except for Apache module maybe)
 * 
 */
if (!function_exists("headers_list")) {
   function headers_list() {
      trigger_error("headers_list(): not supported by this PHP version", E_USER_WARNING);
      return (array)NULL;
   }
}


/**
 * write formatted string to stream/file,
 * arbitrary numer of arguments
 * 
 */
if (!function_exists("fprintf")) {
   function fprintf(/*...*/) {
      $args = func_get_args();
      $stream = array_shift($args);
      return fwrite($stream, call_user_func_array("sprintf", $args));
   }
}


/**
 * write formatted string to stream, args array
 * 
 */
if (!function_exists("vfprintf")) {
   function vfprintf($stream, $format, $args=NULL) {
      return fwrite($stream, vsprintf($format, $args));
   }
}


/**
 * splits a string in evenly sized chunks
 * 
 * @return array
 */
if (!function_exists("str_split")) {
   function str_split($str, $chunk=1) {
      $r = array();
      
      #-- return back as one chunk completely, if size chosen too low
      if ($chunk < 1) {
         $r[] = $str;
      }
      
      #-- add substrings to result array until subject strings end reached
      else {
         $len = strlen($str);
         for ($n=0; $n<$len; $n+=$chunk) {
            $r[] = substr($str, $n, $chunk);
         }
      }
      return($r);
   }
}


/**
 * constructs a QUERY_STRING (application/x-www-form-urlencoded format, non-raw)
 * from a nested array/hash with name=>value pairs
 * - only first two args are part of the original API - rest used for recursion
 *
 * @param  mixed  $vars           variable data for query string
 * @param  string $int_prefix     (optional)
 * @param  string $subarray_pfix  (optional)
 * @param integer $level  
 * @return mixed
 */
if (!function_exists("http_build_query")) {
   function http_build_query($vars, $int_prefix="", $subarray_pfix="", $level=0) {
   
      #-- empty starting string
      $s = "";
      ($SEP = ini_get("arg_separator.output")) or ($SEP = "&");
      
      #-- traverse hash/array/list entries 
      foreach ($vars as $index=>$value) {
         
         #-- add sub_prefix for subarrays (happens for recursed innovocation)
         if ($subarray_pfix) {
            if ($level) {
               $index = "[" . $index . "]";
            }
            $index =  $subarray_pfix . $index;
         }
         #-- add user-specified prefix for integer-indices
         elseif (is_int($index) && strlen($int_prefix)) {
            $index = $int_prefix . $index;
         }
         
         #-- recurse for sub-arrays
         if (is_array($value)) {
            $s .= http_build_query($value, "", $index, $level + 1);
         }
         else {   // or just literal URL parameter
            $s .= $SEP . $index . "=" . urlencode($value);
         }
      }
      
      #-- remove redundant "&" from first round (-not checked above to simplifiy loop)
      if (!$subarray_pfix) {
         $s = substr($s, strlen($SEP));
      }

      #-- return result / to previous array level and iteration
      return($s);
   }
}


/**
 * transform into 3to4 uuencode
 * - this is the bare encoding, not the uu file format
 * 
 * @param  string
 * @return string
 */
if (!function_exists("convert_uuencode")) {
   function convert_uuencode($bin) {

      #-- init vars
      $out = "";
      $line = "";
      $len = strlen($bin);
      $bin .= "\01\01\01";   // PHP and uuencode(1) use some special garbage??, looks like "\000"* and "`\n`" simply appended

      #-- canvass source string
      for ($n=0; $n<$len; ) {
      
         #-- make 24-bit integer from first three bytes
         $x = (ord($bin[$n++]) << 16)
            + (ord($bin[$n++]) <<  8)
            + (ord($bin[$n++]) <<  0);
            
         #-- disperse that into 4 ascii characters
         $line .= chr( 32 + (($x >> 18) & 0x3f) )
                . chr( 32 + (($x >> 12) & 0x3f) )
                . chr( 32 + (($x >>  6) & 0x3f) )
                . chr( 32 + (($x >>  0) & 0x3f) );
                
         #-- cut lines, inject count prefix before each
         if (($n % 45) == 0) {
            $out .= chr(32 + 45) . "$line\n";
            $line = "";
         }
      }

      #-- throw last line, +length prefix
      if ($trail = ($len % 45)) {
         $out .= chr(32 + $trail) . "$line\n";
      }

      // uuencode(5) doesn't tell so, but spaces are replaced with the ` char in most implementations
      $out = strtr("$out \n", " ", "`");
      return($out);
   }
}


/**
 * decodes uuencoded() data again
 *
 * @param  string $from  
 * @return string
 */
if (!function_exists("convert_uudecode")) {
   function convert_uudecode($from) {

      #-- prepare
      $out = "";
      $from = strtr($from, "`", " ");
      
      #-- go through lines
      foreach(explode("\n", ltrim($from)) as $line) {
         if (!strlen($line)) {
            break;  // end reached
         }
         
         #-- current line length prefix
         unset($num);
         $num = ord($line{0}) - 32;
         if (($num <= 0) || ($num > 62)) {  // 62 is the maximum line length
            break;          // according to uuencode(5), so we stop here too
         }
         $line = substr($line, 1);
         
         #-- prepare to decode 4-char chunks
         $add = "";
         for ($n=0; strlen($add)<$num; ) {
         
            #-- merge 24 bit integer from the 4 ascii characters (6 bit each)
            $x = ((ord($line[$n++]) - 32) << 18)
               + ((ord($line[$n++]) - 32) << 12)  // were saner with "& 0x3f"
               + ((ord($line[$n++]) - 32) <<  6)
               + ((ord($line[$n++]) - 32) <<  0);
               
            #-- reconstruct the 3 original data chars
            $add .= chr( ($x >> 16) & 0xff )
                  . chr( ($x >>  8) & 0xff )
                  . chr( ($x >>  0) & 0xff );
         }

         #-- cut any trailing garbage (last two decoded chars may be wrong)
         $out .= substr($add, 0, $num);
         $line = "";
      }

      return($out);
   }
}


/**
 * return array of filenames in a given directory
 * (only works for local files)
 *
 * @param  string $dirname  
 * @param  bool   $desc  
 * @return array
 */
if (!function_exists("scandir")) {
   function scandir($dirname, $desc=0) {
   
      #-- check for file:// protocol, others aren't handled
      if (strpos($dirname, "file://") === 0) {
         $dirname = substr($dirname, 7);
         if (strpos($dirname, "localh") === 0) {
            $dirname = substr($dirname, strpos($dirname, "/"));
         }
      }
      
      #-- directory reading handle
      if ($dh = opendir($dirname)) {
         $ls = array();
         while ($fn = readdir($dh)) {
            $ls[] = $fn;  // add to array
         }
         closedir($dh);
         
         #-- sort filenames
         if ($desc) {
            rsort($ls);
         }
         else {
            sort($ls);
         }
         return $ls;
      }

      #-- failure
      return false;
   }
}


/**
 * like date(), but returns an integer for given one-letter format parameter
 *
 * @param  string  $formatchar
 * @param  integer $timestamp
 * @return integer
 */
if (!function_exists("idate")) {
   function idate($formatchar, $timestamp=NULL) {
   
      #-- reject non-simple type parameters
      if (strlen($formatchar) != 1) {
         return false;
      }
      
      #-- get current time, if not given
      if (!isset($timestamp)) {
         $timestamp = time();
      }
      
      #-- get and turn into integer
      $str = date($formatchar, $timestamp);
      return (int)$str;
   }
}



/**
 * combined sleep() and usleep() 
 * 
 */
if (!function_exists("time_nanosleep")) {
   function time_nanosleep($sec, $nano) {
      sleep($sec);
      usleep($nano);
   }
}




/**
 * search first occourence of any of the given chars, returns rest of haystack
 * (char_list must be a string for compatibility with the real PHP func)
 *
 * @param  string $haystack  
 * @param  string $char_list  
 * @return integer
 */
if (!function_exists("strpbrk")) {
   function strpbrk($haystack, $char_list) {
   
      #-- prepare
      $len = strlen($char_list);
      $min = strlen($haystack);
      
      #-- check with every symbol from $char_list
      for ($n = 0; $n < $len; $n++) {
         $l = strpos($haystack, $char_list{$n});
         
         #-- get left-most occourence
         if (($l !== false) && ($l < $min)) {
            $min = $l;
         }
      }
      
      #-- result
      if ($min) {
         return(substr($haystack, $min));
      }
      else {
         return(false);
      }
   }
}



/**
 * logo image activation URL query strings (gaga feature)
 * 
 */
if (!function_exists("php_real_logo_guid")) {
   function php_real_logo_guid() { return php_logo_guid(); }
   function php_egg_logo_guid() { return zend_logo_guid(); }
}


/**
 * no need to implement this
 * (there aren't interfaces in PHP4 anyhow)
 * 
 */
if (!function_exists("get_declared_interfaces")) {
   function get_declared_interfaces() {
      trigger_error("get_declared_interfaces(): Current script won't run reliably with PHP4.", E_USER_WARNING);
      return( (array)NULL );
   }
}



/**
 * creates an array from lists of $keys and $values
 * (both should have same number of entries)
 *
 * @param  array $keys  
 * @param  array $values  
 * @return array
 */
if (!function_exists("array_combine")) {
   function array_combine($keys, $values) {
   
      #-- convert input arrays into lists
      $keys = array_values($keys);
      $values = array_values($values);
      $r = array();
      
      #-- one from each
      foreach ($values as $i=>$val) {
         if ($key = $keys[$i]) {
            $r[$key] = $val;
         }
         else {
            $r[] = $val;   // useless, PHP would have long aborted here
         }
      }
      return($r);
   }
}


/**
 * apply userfunction to each array element (descending recursively)
 * use it like:  array_walk_recursive($_POST, "stripslashes");
 * - $callback can be static function name or object/method, class/method
 *
 * @param  array  $input  
 * @param  string $callback  
 * @param  array  $userdata  (optional)
 * @return array
 */
if (!function_exists("array_walk_recursive")) {
   function array_walk_recursive(&$input, $callback, $userdata=NULL) {
      #-- each entry
      foreach ($input as $key=>$value) {

         #-- recurse for sub-arrays
         if (is_array($value)) {
            array_walk_recursive($input[$key], $callback, $userdata);
         }

         #-- $callback handles scalars
         else {
            call_user_func_array($callback, array(&$input[$key], $key, $userdata) );
         }
      }

      // no return value
   }
}


/**
 * complicated wrapper around substr() and and strncmp()
 *
 * @param  string  $haystack  
 * @param  string  $needle  
 * @param  integer $offset  
 * @param  integer $len  
 * @param  integer $ci  
 * @return mixed
 */
if (!function_exists("substr_compare")) {
   function substr_compare($haystack, $needle, $offset=0, $len=0, $ci=0) {

      #-- check params   
      if ($len <= 0) {   // not well documented
         $len = strlen($needle);
         if (!$len) { return(0); }
      }
      #-- length exception
      if ($len + $offset >= strlen($haystack)) {
         trigger_error("substr_compare: given length exceeds main_str", E_USER_WARNING);
         return(false);
      }

      #-- cut
      if ($offset) {
         $haystack = substr($haystack, $offset, $len);
      }
      #-- case-insensitivity
      if ($ci) {
         $haystack = strtolower($haystack);
         $needle = strtolower($needle);
      }

      #-- do
      return(strncmp($haystack, $needle, $len));
   }
}


/**
 * stub, returns empty list as usual;
 * you must load "ext/spl.php" beforehand to get this
 * 
 */
if (!function_exists("spl_classes")) {
   function spl_classes() {
      trigger_error("spl_classes(): not built into this PHP version");
      return (array)NULL;
   }
}



/**
 * gets you list of class names the given objects class was derived from, slow
 *
 * @param  object $obj  
 * @return object
 */
if (!function_exists("class_parents")) {
   function class_parents($obj) {
   
      #-- first get full list
      $all = get_declared_classes();
      $r = array();
      
      #-- filter out
      foreach ($all as $potential_parent) {
         if (is_subclass_of($obj, $potential_parent)) {
            $r[$potential_parent] = $potential_parent;
         }
      }
      return($r);
   }
}


/**
 * an alias
 * 
 */
if (!function_exists("session_commit") && function_exists("session_write_close")) {
   function session_commit() {
      // simple
      session_write_close();
   }
}


/**
 * aliases
 *
 * @param  mixed $host  
 * @param  mixed $type  (optional)
 * @return mixed
 */
if (!function_exists("dns_check_record")) {
   function dns_check_record($host, $type=NULL) {
      // synonym to
      return checkdnsrr($host, $type);
   }
}
if (!function_exists("dns_get_mx")) {
   function dns_get_mx($host, $mx) {
      $args = func_get_args();
      // simple alias - except the optional, but referenced third parameter
      if ($args[2]) {
         $w = & $args[2];
      }
      else {
         $w = false;
      }
      return getmxrr($host, $mx, $w);
   }
}


/**
 * setrawcookie(),
 * can this be emulated 100% exactly?
 *
 * @param  string $name 
 * @param  mixed  $value
 * @param  mixed  $expire
 * @param  mixed  $path
 * @param  mixed  $domain
 * @param integer $secure
 * @return string
 */
if (!function_exists("setrawcookie")) {
   // we output everything directly as HTTP header(), PHP doesn't seem
   // to manage an internal cookie list anyhow
   function setrawcookie($name, $value=NULL, $expire=NULL, $path=NULL, $domain=NULL, $secure=0) {
      if (isset($value) && strpbrk($value, ",; \r\t\n\f\014\013")) {
         trigger_error("setrawcookie: value may not contain any of ',; \r\n' and some other control chars; thrown away", E_USER_WARNING);
      }
      else {
         $h = "Set-Cookie: $name=$value"
            . ($expire ? "; expires=" . gmstrftime("%a, %d-%b-%y %H:%M:%S %Z", $expire) : "")
            . ($path ? "; path=$path": "")
            . ($domain ? "; domain=$domain" : "")
            . ($secure ? "; secure" : "");
         header($h);
      }
   }
}


/**
 * write-at-once file access (counterpart to file_get_contents)
 *
 * @param  integer $filename
 * @param  mixed   $content  
 * @param  integer $flags 
 * @param  mixed   $resource
 * @return integer
 */
if (!function_exists("file_put_contents")) {
   function file_put_contents($filename, $content, $flags=0, $resource=NULL) {

      #-- prepare
      $mode = ($flags & FILE_APPEND ? "a" : "w" ) ."b";
      $incl = $flags & FILE_USE_INCLUDE_PATH;
      $length = strlen($content);
//      $resource && trigger_error("EMULATED file_put_contents does not support \$resource parameter.", E_USER_ERROR);
      
      #-- write non-scalar?
      if (is_array($content) || is_object($content)) {
         $content = implode("", (array)$content);
      }

      #-- open for writing
      $f = fopen($filename, $mode, $incl);
      if ($f) {
      
         // locking
         if (($flags & LOCK_EX) && !flock($f, LOCK_EX)) {
            return fclose($f) && false;
         }

         // write
         $written = fwrite($f, $content);
         fclose($f);
         
         #-- only report success, if completely saved
         return($length == $written);
      }
   }
}


/**
 * file-related constants
 *
 */
if (!defined("FILE_USE_INCLUDE_PATH")) { define("FILE_USE_INCLUDE_PATH", 1); }
if (!defined("FILE_IGNORE_NEW_LINES")) { define("FILE_IGNORE_NEW_LINES", 2); }
if (!defined("FILE_SKIP_EMPTY_LINES")) { define("FILE_SKIP_EMPTY_LINES", 4); }
if (!defined("FILE_APPEND")) { define("FILE_APPEND", 8); }
if (!defined("FILE_NO_DEFAULT_CONTEXT")) { define("FILE_NO_DEFAULT_CONTEXT", 16); }



#-- more new constants for 5.0
/**
 * @since PHP 5
 */
if (!defined("E_STRICT")) { define("E_STRICT", 2048); }  // _STRICT is a special case of _NOTICE (_DEBUG)
# PHP_CONFIG_FILE_SCAN_DIR




#-- array count_recursive()
if (!defined("COUNT_NORMAL")) { define("COUNT_NORMAL", 0); }      // count($array, 0);
if (!defined("COUNT_RECURSIVE")) { define("COUNT_RECURSIVE", 1); }    // use count_recursive()



/**
 * @since never
 * @nonstandard
 * 
 * we introduce a new function, because we cannot emulate the
 * newly introduced second parameter to count()
 * 
 * @param  array $array 
 * @param  integer $mode
 * @return integer
 */
if (!function_exists("count_recursive")) {
   function count_recursive($array, $mode=1) {
      if (!$mode) {
         return(count($array));
      }
      else {
         $c = count($array);
         foreach ($array as $sub) {
            if (is_array($sub)) {
               $c += count_recursive($sub);
            }
         }
         return($c);
      }
   }
}



/**
 * Sets the default client character set.
 *
 * @compat
 *    Procedural style
 * @bugs
 *    PHP documentation says this function exists in PHP 5 >= 5.0.5,
 *    but it also depends on the versions of external libraries, e.g.,
 *    php_mysqli.dll and libmysql.dll.
 *
 * @param $link    mysqli MySQLi connection resource
 * @param $charset string Character set
 * @return bool           TRUE on success, FALSE on failure
 */
if (!function_exists("mysqli_set_charset") && function_exists("mysqli_query")) {
   function mysqli_set_charset($link, $charset) {
      return mysqli_query($link, "SET NAMES '$charset'");
   }
}









/**
 *                                   ------------------------------ 4.4 ---
 * @group 4_4
 * @since 4.4
 *
 * PHP 4.4 is a bugfix and backporting version created after PHP 5. It went
 * mostly unchanged, but changes a few language semantics (e.g. references).
 *
 * @emulated
 *    PHP_INT_SIZE
 *    PHP_INT_MAX
 *    SORT_LOCALE_STRING
 *
 */

if (!defined("PHP_INT_SIZE")) { define("PHP_INT_SIZE", 4); }
if (!defined("PHP_INT_MAX")) { define("PHP_INT_MAX", 2147483647); }
if (!defined("SORT_LOCALE_STRING")) { define("SORT_LOCALE_STRING", 5); }






/**
 *                                   ------------------------------ 4.3 ---
 * @group 4_3
 * @since 4.3
 *
 *  Additions in 4.3 version of PHP interpreter.
 *
 * @emulated
 *    file_get_contents
 *    array_key_exists
 *    array_intersect_assoc
 *    array_diff_assoc
 *    html_entity_decode
 *    str_word_count
 *    str_shuffle
 *    get_include_path
 *    set_include_path
 *    restore_include_path
 *    fnmatch
 *    FNM_PATHNAME
 *    FNM_NOESCAPE
 *    FNM_PERIOD
 *    FNM_LEADING_DIR
 *    FNM_CASEFOLD
 *    FNM_EXTMATCH
 *    glob
 *    GLOB_MARK
 *    GLOB_NOSORT
 *    GLOB_NOCHECK
 *    GLOB_NOESCAPE
 *    GLOB_BRACE
 *    GLOB_ONLYDIR
 *    GLOB_NOCASE
 *    GLOB_DOTS
 *    __FUNCTION__
 *    PATH_SEPARATOR
 *    PHP_SHLIB_SUFFIX
 *    PHP_SAPI
 *    PHP_PREFIX
 *
 * @missing
 *    sha1_file
 *    sha1 - too much code, and has been reimplemented elsewhere
 *
 * @unimplementable
 *    money_format
 *
 */


/**
 * simplified file read-at-once function
 *
 * @param  string  $filename  
 * @param  integer $use_include_path  (optional)
 * @return string
 */
if (!function_exists("file_get_contents")) {
   function file_get_contents($filename, $use_include_path=1) {

      #-- open file, let fopen() report error
      $f = fopen($filename, "rb", $use_include_path);
      if (!$f) { return; }

      #-- read max 2MB
      $content = fread($f, 1<<21);
      fclose($f);
      return($content);
   }
}



/**
 * shell-like filename matching (* and ? globbing characters)
 *
 * @param  string $pattern  glob-pattern with *s and ?s
 * @param  string $fn       filename to match it against (without path)
 * @param integer $flags    (optional)
 * @return bool
 */
if (!function_exists("fnmatch")) {

   #-- associated constants
   if (!defined("FNM_PATHNAME")) { define("FNM_PATHNAME", 1<<0); }  // no wildcard ever matches a "/"
   if (!defined("FNM_NOESCAPE")) { define("FNM_NOESCAPE", 1<<1); }  // backslash can't escape meta chars
   if (!defined("FNM_PERIOD")) { define("FNM_PERIOD",   1<<2); }  // leading dot must be given explicit
   if (!defined("FNM_LEADING_DIR")) { define("FNM_LEADING_DIR", 1<<3); }  // not in PHP
   if (!defined("FNM_CASEFOLD")) { define("FNM_CASEFOLD", 0x50); }  // match case-insensitive
   if (!defined("FNM_EXTMATCH")) { define("FNM_EXTMATCH", 1<<5); }  // not in PHP
   
   #-- implementation
   function fnmatch($pattern, $fn, $flags=0x0000) {
      
      #-- 'hidden' files
      if ($flags & FNM_PERIOD) {
         if (($fn[0] == ".") && ($pattern[0] != ".")) {
            return(false);    // abort early
         }
      }

      #-- case-insensitivity
      $rxci = "";
      if ($flags & FNM_CASEFOLD) {
         $rxci = "i";
      }
      #-- handline of pathname separators (/)
      $wild = ".";
      if ($flags & FNM_PATHNAME) {
         $wild = "[^/".DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR."]";
      }

      #-- check for cached regular expressions
      static $cmp = array();
      if (isset($cmp["$pattern+$flags"])) {
         $rx = $cmp["$pattern+$flags"];
      }

      #-- convert filename globs into regex
      else {
         $rx = preg_quote($pattern);
         $rx = strtr($rx, array(
            "\\*"=>"$wild*?", "\\?"=>"$wild", "\\["=>"[", "\\]"=>"]",
         ));
         $rx = "{^" . $rx . "$}" . $rxci;
         
         #-- cache
         if (count($cmp) >= 50) {
            $cmp = array();   // free
         }
         $cmp["$pattern+$flags"] = $rx;
      }
      
      #-- compare
      return(preg_match($rx, $fn));
   }
}


/**
 * file search and name matching (with shell patterns)
 *
 * @param  string  $pattern   search pattern and path ../* string
 * @param  integer $flags (optional)
 * @return array
 */
if (!function_exists("glob")) {

   #-- introduced constants
   if (!defined("GLOB_MARK")) { define("GLOB_MARK", 1<<0); }
   if (!defined("GLOB_NOSORT")) { define("GLOB_NOSORT", 1<<1); }
   if (!defined("GLOB_NOCHECK")) { define("GLOB_NOCHECK", 1<<2); }
   if (!defined("GLOB_NOESCAPE")) { define("GLOB_NOESCAPE", 1<<3); }
   if (!defined("GLOB_BRACE")) { define("GLOB_BRACE", 1<<4); }
   if (!defined("GLOB_ONLYDIR")) { define("GLOB_ONLYDIR", 1<<5); }
   if (!defined("GLOB_NOCASE")) { define("GLOB_NOCASE", 1<<6); }
   if (!defined("GLOB_DOTS")) { define("GLOB_DOTS", 1<<7); }
   // unlikely to work under Win(?), without replacing the explode() with
   // a preg_split() incorporating the native DIRECTORY_SEPARATOR as well

   #-- implementation
   function glob($pattern, $flags=0x0000) {
      $ls = array();
      $rxci = ($flags & GLOB_NOCASE) ? "i" : "";
#echo "\n=> glob($pattern)...\n";
      
      #-- transform glob pattern into regular expression
      #   (similar to fnmatch() but still different enough to require a second func)
      if ($pattern) {

         #-- look at each directory/fn spec part separately
         $parts2 = explode("/", $pattern);
         $pat = preg_quote($pattern);
         $pat = strtr($pat, array("\\*"=>".*?", "\\?"=>".?"));
         if ($flags ^ GLOB_NOESCAPE) {
            // uh, oh, ouuch - the above is unclean enough...
         }
         if ($flags ^ GLOB_BRACE) {
            $pat = preg_replace("/\{(.+?)\}/e", 'strtr("[$1]", ",", "")', $pat);
         }
         $parts = explode("/", $pat);
#echo "parts == ".implode(" // ", $parts) . "\n";
         $lasti = count($parts) - 1;
         $dn = "";
         foreach ($parts as $i=>$p) {

            #-- basedir included (yet no pattern matching necessary)
            if (!strpos($p, "*?") && (strpos($p, ".?")===false)) {
               $dn .= $parts2[$i] . ($i!=$lasti ? "/" : "");
#echo "skip:$i, cause no pattern matching char found -> only a basedir spec\n";
               continue;
            }
            
            #-- start reading dir + match filenames against current pattern
            if ($dh = opendir($dn ?$dn:'.')) {
               $with_dot = ($p[1]==".") || ($flags & GLOB_DOTS);
#echo "part:$i:$p\n";
#echo "reading dir \"$dn\"\n";
               while ($fn = readdir($dh)) {
                  if (preg_match("\007^$p$\007$rxci", $fn)) {

                     #-- skip over 'hidden' files
                     if (($fn[0] == ".") && !$with_dot) {
                        continue;
                     }

                     #-- add filename only if last glob/pattern part
                     if ($i==$lasti) {
                        if (is_dir("$dn$fn")) {
                           if ($flags & GLOB_ONLYDIR) {
                              continue;
                           }
                           if ($flags & GLOB_MARK) {
                              $fn .= "/";
                           }
                        }
#echo "adding '$fn' for dn=$dn to list\n";
                        $ls[] = "$dn$fn";
                     }

                     #-- initiate a subsearch, merge result list in
                     elseif (is_dir("$dn$fn")) {
                        // add reamaining search patterns to current basedir
                        $remaind = implode("/", array_slice($parts2, $i+1));
                        $ls = array_merge($ls, glob("$dn$fn/$remaind", $flags));
                     }
                  }
               }
               closedir($dh);

               #-- prevent scanning a 2nd part/dir in same glob() instance:
               break;  
            }

            #-- given dirname doesn't exist
            else {
               return($ls);
            }

         }// foreach $parts
      }

      #-- return result list
      if (!$ls && ($flags & GLOB_NOCHECK)) {
         $ls[] = $pattern;
      }
      if ($flags ^ GLOB_NOSORT) {
         sort($ls);
      }
#print_r($ls);
#echo "<=\n";
      return($ls);
   }
} //@FIX: fully comment, remove debugging code (- as soon as it works ;)



/**
 * redundant alias for isset()
 * 
 */
if (!function_exists("array_key_exists")) {
   function array_key_exists($key, $search) {
      return isset($search[$key]);
   }
}


/**
 * who could need that?
 * 
 */
if (!function_exists("array_intersect_assoc")) {
   function array_intersect_assoc( /*array, array, array...*/ ) {

      #-- parameters, prepare
      $in = func_get_args();
      $cmax = count($in);
      $whatsleftover = array();
      
      #-- walk through each array pair
      #   (take first as checklist)
      foreach ($in[0] as $i => $v) {
         for ($c = 1; $c < $cmax; $c++) {
            #-- remove entry, as soon as it isn't present
            #   in one of the other arrays
            if (!isset($in[$c][$i]) || (@$in[$c][$i] !== $v)) {
               continue 2;
            }
         }
         #-- it was found in all other arrays
         $whatsleftover[$i] = $v;
      }
      return $whatsleftover;
   }
}


/**
 * the opposite of the above
 * 
 */
if (!function_exists("array_diff_assoc")) {
   function array_diff_assoc( /*array, array, array...*/ ) {

      #-- params
      $in = func_get_args();
      $diff = array();
      
      #-- compare each array with primary/first
      foreach ($in[0] as $i=>$v) {
         for ($c=1; $c<count($in); $c++) {
            #-- skip as soon as it matches with entry in another array
            if (isset($in[$c][$i]) && ($in[$c][$i] == $v)) {
               continue 2;
            }
         }
         #-- else
         $diff[$i] = $v;
      }
      return $diff;
   }
}


/**
 * opposite of htmlentities
 * 
 */
if (!function_exists("html_entity_decode")) {
   function html_entity_decode($string, $quote_style=ENT_COMPAT, $charset="ISO-8859-1") {
      //@FIX: we fall short on anything other than Latin-1
      $y = array_flip(get_html_translation_table(HTML_ENTITIES, $quote_style));
      return strtr($string, $y);
   }
}


/**
 * extracts single words from a string
 * 
 */
if (!function_exists("str_word_count")) {
   function str_word_count($string, $result=0) {
   
      #-- let someone else do the work
      preg_match_all('/([\w](?:[-\'\w]?[\w]+)*)/', $string, $uu);

      #-- return full word list
      if ($result == 1) {
         return($uu[1]);
      }
      
      #-- array() of $pos=>$word entries
      elseif ($result >= 2) {
         $r = array();
         $l = 0;
         foreach ($uu[1] as $word) {
            $l = strpos($string, $word, $l);
            $r[$l] = $word;
            $l += strlen($word);  // speed up next search
         }
         return($r);
      }

      #-- only count
      else {
         return(count($uu[1]));
      }
   }
}


/**
 * creates a permutation of the given strings characters
 * (let's hope the random number generator was alread initialized)
 * 
 */
if (!function_exists("str_shuffle")) {
   function str_shuffle($str) {
      $r = "";

      #-- cut string down with every iteration
      while (strlen($str)) {
         $n = strlen($str) - 1;
         if ($n) {
            $n = rand(0, $n);   // glibcs` rand is ok since 2.1 at least
         }
         
         #-- cut out elected char, add to result string
         $r .= $str{$n};
         $str = substr($str, 0, $n) . substr($str, $n + 1);
      }
      return($r);
   }
}


/**
 * simple shorthands
 * 
 */
if (!function_exists("get_include_path")) {
   function get_include_path() {
      return(get_cfg_var("include_path"));
   }
   function set_include_path($new) {
      return ini_set("include_path", $new);
   }
   function restore_include_path() {
      ini_restore("include_path");
   }
}


#-- constants for 4.3
   if (!defined("PATH_SEPARATOR")) { define("PATH_SEPARATOR", ((DIRECTORY_SEPARATOR=='\\') ? ';' :':')); }
   if (!defined("PHP_SHLIB_SUFFIX")) { define("PHP_SHLIB_SUFFIX", ((DIRECTORY_SEPARATOR=='\\') ? 'dll' :'so')); }
   if (!defined("PHP_SAPI")) { define("PHP_SAPI", php_sapi_name()); }
   if (!defined("__FUNCTION__")) { define("__FUNCTION__", NULL); }   // empty string would signalize main()


#-- not identical to what PHP reports (it seems to `which` for itself)
if (!defined("PHP_PREFIX") && isset($_ENV["_"])) { define("PHP_PREFIX", substr($_ENV["_"], 0, strpos($_ENV["_"], "bin/"))); }






/**
 *                                   ------------------------------ 4.2 ---
 * @group 4_2
 * @since 4.2
 *
 *
 *  Functions added in PHP 4.2 interpreters.
 *
 *
 * @emulated
 *   str_rot13
 *   array_change_key_case
 *   array_fill
 *   array_chunk
 *   md5_file
 *   is_a
 *   fmod
 *   floatval
 *   is_infinite
 *   is_nan
 *   is_finite
 *   var_export
 *   strcoll
 * @missing
 *   ...
 *
 *   almost complete!?
 *
 *
 */


/**
 * shy away from this function - it was broken in all PHP4.2 releases,
 * and our emulation here won't change that
 *
 * @param  string $str  
 * @return string
 */
if (!function_exists("str_rot13")) {
   function str_rot13($str) {
      static $from = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
      static $to = "NOPQRSTUVWXYZABCDEFGHIJKLMnopqrstuvwxyzabcdefghijklm";
      return strtr($str, $from, $to);
   }
}


/**
 * changes case of textual index keys
 *
 * @param  array $array  
 * @param  int   $case
 * @return array
 */
if (!function_exists("array_change_key_case")) {
   
   #-- introduced constants
   if (!defined("CASE_LOWER")) { define("CASE_LOWER", 0); }
   if (!defined("CASE_UPPER")) { define("CASE_UPPER", 1); }
   
   #-- implementation
   function array_change_key_case($array, $case=CASE_LOWER) {
   
      #-- loop through
      foreach ($array as $i=>$v) {
         #-- do anything for strings only
         if (is_string($i)) {
            unset($array[$i]);
            $i = ($case==CASE_LOWER) ? strtolower($i) : strtoupper($i);
            $array[$i] = $v;
         }
         // non-recursive      
      }
      return($array);
   }
}


/**
 * create fixed-length array made up of $value data
 * 
 */
if (!function_exists("array_fill")) {
   function array_fill($start_index, $num, $value) {

      #-- params
      $r = array();
      $i = $start_index;
      $end = $num + $start_index;
      
      #-- append
      for (; $i < $end; $i++)
      {
         $r[$i] = $value;
      }
      return($r);
   }
}


/**
 * split an array into evenly sized parts
 * 
 */
if (!function_exists("array_chunk")) {
   function array_chunk($input, $size, $preserve_keys=false) {
   
      #-- array for chunked output
      $r = array();
      $n = -1;  // chunk index
      
      #-- enum input array blocks
      foreach ($input as $i=>$v) {
      
         #-- new chunk
         if (($n < 0) || (count($r[$n]) == $size)) {
            $n++;
            $r[$n] = array();
         }
         
         #-- add input value into current [$n] chunk
         if ($preserve_keys) {
            $r[$n][$i] = $v;
         }
         else {
            $r[$n][] = $v;
         }
      }
      return($r);
   }
}


/**
 * convenience wrapper
 * 
 */
if (!function_exists("md5_file")) {
   function md5_file($filename, $raw_output=false) {

      #-- read file, apply hash function
      $r = md5(file_get_contents($filename, "rb"));
         
      #-- transform? and return
      if ($raw_output) {
         $r = pack("H*", $r);
      }
      return $r;
   }
}


/**
 * object type checking
 * 
 */
if (!function_exists("is_a")) {
   function is_a($obj, $classname) {
   
      #-- lowercase everything for comparison
      $classnaqme = strtolower($classname);
      $obj_class =  strtolower(get_class($obj));
      
      #-- two possible checks
      return ($obj_class == $classname) or is_subclass_of($obj, $classname);
   }
}


/**
 * floating point modulo
 * 
 */
if (!function_exists("fmod")) {
   function fmod($x, $y) {
      $r = $x / $y;
      $r -= (int)$r;
      $r *= $y;
      return($r);
   }
}


/**
 * makes float variable from string
 *
 * @param  string
 * @return float
 */
if (!function_exists("floatval")) {
   function floatval($str) {
      $str = ltrim($str);
      return (float)$str;
   }
}


/**
 * floats
 *
 */
if (!function_exists("is_infinite")) {

   #-- constants as-is
   if (!defined("NAN")) { define("NAN", "NAN"); }
   if (!defined("INF")) { define("INF", "INF"); }   // there is also "-INF"
   
   #-- simple checks
   function is_infinite($f) {
      $s = (string)$f;
      return(  ($s=="INF") || ($s=="-INF")  );
   }
   function is_nan($f) {
      $s = (string)$f;
      return(  $s=="NAN"  );
   }
   function is_finite($f) {
      $s = (string)$f;
      return(  !strpos($s, "N")  );
   }
}


/**
 * throws value-instantiation PHP-code for given variable
 *
 * @compat
 *    output differentiates from native PHP version,
 *    but functions identically
 *
 * @param  mixed $var  
 * @param  mixed $return  (optional) false
 * @param  string $indent  (optional) ""
 * @param  string $output  (optional) ""
 * @return mixed
 */
if (!function_exists("var_export")) {
   function var_export($var, $return=false, $indent="", $output="") {

      #-- output as in-class variable definitions
      if (is_object($var)) {
         $output = get_class($var) . "::_set_state(array(\n";
         foreach (((array)$var) as $id=>$var) {
            $output .= "  '\$$id' => " . var_export($var, true) . ",\n";
         }
         $output .= "));";
      }
      
      #-- array constructor
      elseif (is_array($var)) {
         foreach ($var as $id=>$next) {
            if ($output) $output .= ",\n";
                    else $output = "array(\n";
            $output .= $indent . '  '
                    . (is_numeric($id) ? $id : '"'.addslashes($id).'"')
                    . ' => ' . var_export($next, true, "$indent  ");
         }
         if (empty($output)) $output = "array(";
         $output .= "\n{$indent})";
       #if ($indent == "") $output .= ";";
      }
      
      #-- literals
      elseif (is_numeric($var)) {
         $output = "$var";
      }
      elseif (is_bool($var)) {
         $output = $var ? "true" : "false";
      }
      else {
         $output = "'" . preg_replace("/([\\\\\'])/", '\\\\$1', $var) . "'";
      }

      #-- done
      if ($return) {
         return($output);
      }
      else {
         print($output);
      }
   }
}


/**
 * @stub
 * @since existed since PHP 4.0.5, but under Win32 first since 4.3.2
 * 
 * strcmp() variant that respects locale setting,
 *
 * @param  string $str1  
 * @param  string $str2  
 * @return string
 */
if (!function_exists("strcoll")) {
   function strcoll($str1, $str2) {
      return strcmp($str1, $str2);
   }
}





/**
 *                                   ------------------------------ 4.1 ---
 * @group 4_1
 * @since 4.1
 *
 *
 * See also "ext/math41.php" for some more (rarely used mathematical funcs).
 *
 *
 * @emulated
 *   diskfreespace
 *   disktotalspace
 *   vprintf
 *   vsprintf
 *   import_request_variables
 *   hypot
 *   log1p
 *   expm1
 *   sinh
 *   cosh
 *   tanh
 *   asinh
 *   acosh
 *   atanh
 *   mhash
 *   mhash_count
 *   mhash_get_hash_name
 *   mhash_get_block_size
 * @missing
 *   nl_langinfo - unimpl?
 *   getmygid
 *   version_compare
 *
 */




/**
 * aliases (an earlier fallen attempt to unify PHP function names)
 * 
 */
if (!function_exists("diskfreespace")) {
   function diskfreespace() {
      return disk_free_sapce();
   }
   function disktotalspace() {
      return disk_total_sapce();
   }
}


/**
 * variable count of arguments (in array list) printf variant
 *
 * @param  string $format  
 * @param  mixed  $args
 * @output
 */
if (!function_exists("vprintf")) {
   function vprintf($format, $args=NULL) {
      call_user_func_array("fprintf", func_get_args());
   }
}


/**
 * same as above, but doesn't output directly and returns formatted string
 *
 * @param  string $format
 * @param  mixed  $args
 * @return string
 */
if (!function_exists("vsprintf")) {
   function vsprintf($format, $args=NULL) {
      $args = array_merge(array($format), array_values((array)$args));
      return call_user_func_array("sprintf", $args);
   }
}


/**
 * @extended
 *
 * can be used to simulate a register_globals=on environment
 *
 * @param  string $types   order of GET,POST,COOKIE variables
 * @param  string $pfix    prefix for imported variable names
 * @global $GLOBALS
 */
if (!function_exists("import_request_variables")) {
   function import_request_variables($types="GPC", $pfix="") {
      
      #-- associate abbreviations to global var names
      $alias = array(
         "G" => "_GET",
         "P" => "_POST",
         "C" => "_COOKIE",
         "S" => "_SERVER",   // non-standard
         "E" => "_ENV",      // non-standard
      );

      #-- alias long names (PHP < 4.0.6)    //@FIXME: does that belong here?
      if (!isset($_REQUEST)) {
         $_GET = & $HTTP_GET_VARS;
         $_POST = & $HTTP_POST_VARS;
         $_COOKIE = & $HTTP_COOKIE_VARS;
      }
      
      #-- copy
      foreach (str_split($types, 1) as $c) {
         if ($FROM = $alias[strtoupper($c)]) {
            foreach ($$FROM as $key=>$val) {
               if (!isset($GLOBALS[$pfix.$key])) {
                  $GLOBALS[$pfix . $key] = $val;
               }
            }
         }
      }
      // done
   }
}


// a few mathematical functions follow
// (wether we should really emulate them is a different question)

#-- me has no idea what this function means
if (!function_exists("hypot")) {
   function hypot($num1, $num2) {
      return sqrt($num1*$num1 + $num2*$num2);  // as per PHP manual ;)
   }
}

#-- more accurate logarithm func, but we cannot simulate it
#   (too much work, too slow in PHP)
if (!function_exists("log1p")) {
   function log1p($x) {
      return(  log(1+$x)  );
   }
   #-- same story for:
   function expm1($x) {
      return(  exp($x)-1  );
   }
}

#-- as per PHP manual
if (!function_exists("sinh")) {
   function sinh($f) {
      return(  (exp($f) - exp(-$f)) / 2  );
   }
   function cosh($f) {
      return(  (exp($f) + exp(-$f)) / 2  );
   }
   function tanh($f) {
      return(  sinh($f) / cosh($f)  );   // ok, that one makes sense again :)
   }
}

#-- these look a bit more complicated
if (!function_exists("asinh")) {
   function asinh($x) {
      return(  log($x + sqrt($x*$x+1))  );
   }
   function acosh($x) {
      return(  log($x + sqrt($x*$x-1))  );
   }
   function atanh($x) {
      return(  log1p( 2*$x / (1-$x) ) / 2  );
   }
}




/**
 * HMAC from RFC2104, but see also PHP_Compat or Crypt_HMAC
 *
 * @param  string $hashtype  which encoding functions to use
 * @param  string $text      plaintext to hash
 * @param  string $key       key data
 * @return string            hash
 */
if (!function_exists("mhash")) {

   #-- constants
   if (!defined("MHASH_CRC32")) { define("MHASH_CRC32", 0); }
   if (!defined("MHASH_MD5")) { define("MHASH_MD5", 1); }       // RFC1321
   if (!defined("MHASH_SHA1")) { define("MHASH_SHA1", 2); }      // RFC3174
   if (!defined("MHASH_TIGER")) { define("MHASH_TIGER", 7); }
   if (!defined("MHASH_MD4")) { define("MHASH_MD4", 16); }      // RFC1320
   if (!defined("MHASH_SHA256")) { define("MHASH_SHA256", 17); }
   if (!defined("MHASH_ADLER32")) { define("MHASH_ADLER32", 18); }
   
   #-- implementation
   function mhash($hashtype, $text, $key) {
   
      #-- hash function
      if (!($func = mhash_get_hash_name($hashtype)) || !function_exists($func)) {
         return trigger_error("mhash: cannot use hash algorithm #$hashtype/$func", E_USER_ERROR);
      }
      if (!$key) {
         trigger_error("mhash: called without key", E_USER_WARNING);
      }
      
      #-- params
      $bsize = mhash_get_block_size($hashtype);   // fixed size, 64

      #-- pad key
      if (strlen($key) > $bsize) {  // hash key, when it's too long
         $key = $func($key); 
         $key = pack("H*", $key);   // binarify
      }
      $key = str_pad($key, $bsize, "\0");  // fill up with NULs (1)
      
      #-- prepare inner and outer padding stream
      $ipad = str_pad("", $bsize, "6");   // %36
      $opad = str_pad("", $bsize, "\\");  // %5C
      
      #-- call hash func    // php can XOR strings for us
      $dgst = pack("H*",  $func(  ($key ^ $ipad)  .  $text  ));  // (2,3,4)
      $dgst = pack("H*",  $func(  ($key ^ $opad)  .  $dgst  ));  // (5,6,7)
      return($dgst);
   }
   
   #-- return which hash functions are implemented
   function mhash_count() {
      return(MHASH_SHA1);
   }
   
   #-- map numeric identifier to hash function name
   function mhash_get_hash_name($i) {
      static $hash_funcs = array(
          MHASH_CRC32 => "crc32",   // would need dechex()ing in main func?
          MHASH_MD5 => "md5",
          MHASH_SHA1 => "sha1",
      );
      return(strtoupper($hash_funcs[$i]));
   }
   
   #-- static value
   function mhash_get_block_size($i) {
      return(64);
   }
}





/**
 *
 * @group REMOVED_STUFF
 * @since unknown
 * @until unknown
 *
 *
 * @emulated
 *    ...
 *
 * @missing
 *    leak  - occupy a given amount of memory
 *
 */





/**
 *
 * group PRE_4_1
 * since 4.0
 * since 3.0
 *
 *
 * @emulated
 *    ...
 *
 *
 * No need to implement anything below that, because such old versions
 * will be incompatbile anyhow (- none of the newer superglobals known).
 *
 * but see also "ext/old"
 *
 */


?>