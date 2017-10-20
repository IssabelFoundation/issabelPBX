<?php
/**
 * api:         php
 * title:       upgrade.php
 * description: Emulates functions from new PHP versions on older interpreters.
 * version:     17
 * license:     Public Domain
 * url:         http://freshmeat.net/projects/upgradephp
 * type:        functions
 * category:    library
 * priority:    auto
 * load_if:     (PHP_VERSION<5.2)
 * sort:        -255
 * provides:    upgrade-php, api:php5, json
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
if (!function_exists("json_encode")) {
  function json_encode($var, /*emu_args*/$obj=FALSE) {

     #-- prepare JSON string
     $json = "";

     #-- add array entries
     if (is_array($var) || ($obj=is_object($var))) {

        #-- check if array is associative
        if (!$obj) foreach ((array)$var as $i=>$v) {
           if (!is_int($i)) {
              $obj = 1;
              break;
           }
        }

        #-- concat invidual entries
        foreach ((array)$var as $i=>$v) {
           $json .= ($json ? "," : "")    // comma separators
                  . ($obj ? ("\"$i\":") : "")   // assoc prefix
                  . (json_encode($v));    // value
        }

        #-- enclose into braces or brackets
        $json = $obj ? "{".$json."}" : "[".$json."]";
     }

     #-- strings need some care
     elseif (is_string($var)) {
        if (!utf8_decode($var)) {
           $var = utf8_encode($var);
        }
        $var = str_replace(array("\\", "\"", "/", "\b", "\f", "\n", "\r", "\t"), array("\\\\", '\"', "\\/", "\\b", "\\f", "\\n", "\\r", "\\t"), $var);
        $json = '"' . $var . '"';
        //@COMPAT: for fully-fully-compliance   $var = preg_replace("/[\000-\037]/", "", $var);
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
        trigger_error("json_encode: don't know what a '" .gettype($var). "' is.", E_USER_ERROR);
     }

     #-- done
     return($json);
  }
}

/**
 * Get hostname on older php versions
 * @return string
 */
if (!function_exists("gethostname")) {
    function gethostname() {
        return trim(php_uname('n'));
    }
}

require_once('php-upgrade/upgrade.php');
require_once('php-upgrade/ext/gettext.php');
?>
