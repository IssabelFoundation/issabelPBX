<?php
/**
 * api: php
 * type: intercept
 * title: PHP fixes
 * descriptions: removes bogus magic_quotes and left over superglobals
 * version: 1.2
 * priority: auto
 * autoexec: 1
 * category: library
 * conflicts: strike_register_globals, strip_wonderful_slashes
 *
 *  Outdated and bogus PHP settings (register_globals and magic_quotes) are
 *  undone by this script. This avoids negative impact on contemporary code.
 *
 *  This variant can be manually included, or used as auto_prepend_file=
 *  via .user.ini or .htaccess declarations. Preferrably of course, the main
 *  php.ini should be fixed.
 *
 **/


 #-- implementation
 if (!function_exists("upgradephp_recursive_stripslashes")) {
    function upgradephp_recursive_unset(&$TO, $FROM) {
       foreach ($FROM as $var=>$value) {
          if (isset($TO[$var]) && ($TO[$var]==$FROM[$var])) {
             unset($TO[$var]);
             unset($TO[$var]);   // double unset to work around ZE-num/assoc-hashcode bug
          }
       }
    }
    function upgradephp_recursive_stripslashes(&$var) {
       if (is_array($var)) {
          foreach ($var as $key=>$item) {
             upgradephp_recursive_stripslashes($var[$key]);
          }
       }
       else {
          $var = stripslashes($var);
       }
    }
 }


 #-- strike register_globals (injected variables)
 if (ini_get("register_globals") == "1") {
    upgradephp_recursive_unset($GLOBALS, $_REQUEST);
    ini_set("register_globals", 0);
 }


 #-- strip any \'s if magic_quotes (variable garbaging) is still enabled
 if (ini_get("magic_quotes_gpc") && get_magic_quotes_gpc() && !defined("MAGIC_QUOTES_DISABLED")) {
    upgradephp_recursive_stripslashes($_REQUEST);
    upgradephp_recursive_stripslashes($_GET);
    upgradephp_recursive_stripslashes($_POST);
    upgradephp_recursive_stripslashes($_COOKIE);
    upgradephp_recursive_stripslashes($_ENV);
    upgradephp_recursive_stripslashes($_SERVER);
    ini_set("magic_quotes_gpc", 0);
    define("MAGIC_QUOTES_DISABLED", 1) or trigger_error("fix_magic_quotes has been invoked twice");
 }


 #-- now that one is really dumb
 get_magic_quotes_runtime() && set_magic_quotes_runtime(0);



?>