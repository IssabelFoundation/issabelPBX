<?php
/**
 * Some functions from PHP5, that aren't actually senseful to replicate
 * for PHP4. The new native object semantics cannot be emulated anyhow.
 *
 * - see also ext/contrib/exceptions.php
 *
 */



 
/**
 * Make a real object copy. This is a token in PHP5, not a function.
 *
 * Uses serialize-trick from PHP_Compat, because PHP4 references can
 * neither be detected nor resolved otherwise.
 *
 * @stub
 * @since 5.0
 */
if (!function_exists("clone") && PHP_VERSION < "5.0") {
   eval('
   function clone($obj) {
   
      // this however duplicates sub-objects and arrays too
      $new = unserialize(serialize(($obj));
     
      if (method_exists($new, "__clone")) {
         $new->__clone();
      }

      return $new;
   }
   ');
}


?>