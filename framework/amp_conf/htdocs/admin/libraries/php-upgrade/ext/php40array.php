<?php
/**
 *
 *  Extended PHP array functions - _diff and _intersect() for associative
 *  arrays and/or with callback functions (for keys and/or values). These
 *  are too rarely used and exotic to be part of the core "upgrade.php"
 *  script.
 *  
 *  NOTHING IN HERE WAS SERIOUSLY TESTED. Please grab the definitions from
 *  PEAR::PHP_Compat if you want reliable and tested versions.
 *
 *
 *  @group ARRAY_FUNCS_4_0
 *  @since 4.0
 *  @untested
 *
 */


#-- diff associative arrays with two user callbacks
#   (if this looks complicated to you, don't even try to look at the manual)
if (!function_exists("array_udiff_uassoc")) {
   function array_udiff_uassoc() {
      $in = func_get_args();
      $key_cb = array_pop($in);
      $val_cb = array_pop($in);
      $arr1 = array_shift($in);
      $r = array();
      
      foreach ($arr1 as $i=>$v) {
         #-- in each array, compare against each key/value pair
         foreach (array_keys($in) as $c) {
            foreach ($in[$c] as $i2=>$v2) {
               
               $key_cmp = call_user_func_array($key_cb, array($i, $i2));
               if ($key_cmp == 0) {

                  #-- ok, in this case we must compare the data as well
                  $val_cmp = call_user_func_array($val_cb, array($v, $v2));
                  if ($val_cmp == 0) {
                     continue 3;
                  }
               }
            }
         }

         #-- this combination isn't really found anywhere else
         $r[$i] = $v;
      }
      return($r);
   }
}


#-- same, but that keys now are compared normally (without callback)
if (!function_exists("array_udiff_assoc")) {
   function array_udiff_assoc() {
      $in = func_get_args();
      $val_cb = array_pop($in);
      $arr1 = array_shift($in);
      $r = array();
      
      #-- compare against each key/value pair in other arrays
      foreach ($arr1 as $i=>$v) {
         foreach (array_keys($in) as $c) {
            if (isset($in[$c][$i])) {
               #-- now compare data by callback
               $cmp = call_user_func_array($val_cb, array($v, $in[$c][$i]));
               if ($cmp == 0) {
                   continue 2;
               }
            }
         }
         #-- everything exists only in array1
         $r[$i] = $v;
      }
      return($r);
   }
}


#-- ....
if (!function_exists("array_diff_uassoc")) {
   function array_diff_uassoc() {
      $in = func_get_args();
      $key_cb = array_pop($in);
      $arr1 = array_shift($in);
      $num = count($in);
      $r = array();
      
      foreach ($arr1 as $i=>$v) {
         #-- in other arrays?
         for ($c=0; $c<$num; $c++) {
            foreach ($in[$c] as $i2=>$v2) {
               if ($v == $v2) {
                  $cmp = call_user_func_array($key_cb, array($i, $i2));
                  if ($cmp == 0) {
                     continue 3;
                  }
               }
            }
         }
         #-- exists only in array1
         $r[$i] = $v;
      }
      return($r);
   }
}


#-- diff array, keys ignored, callback for comparing values
if (!function_exists("array_udiff")) {
   function array_udiff() {
      $in = func_get_args();
      $val_cb = array_pop($in);
      $arr1 = array_shift($in);
      $num = count($in);
      $r = array();
      foreach ($arr1 as $i=>$v) {
         #-- check other arrays
         for ($c=0; $c<$num; $c++) {
            foreach ($in[$c] as $v2) {
               $cmp = call_user_func_array($val_cb, array($v, $v2));
               if ($cmp == 0) {
                  continue 3;
               }
            }
         }
         #-- exists only in array1
         $r[$i] = $v;
      }
      return($r);
   }
}












#-- same for intersections
if (!function_exists("array_uintersect_uassoc")) {
   function array_uintersect_uassoc() {
      $in = func_get_args();
      $key_cb = array_pop($in);
      $val_cb = array_pop($in);
      $all = array();
      $conc = count($in);
      foreach ($in[0] as $i=>$v) {
         #-- must exist in each array (at least once, callbacks may match fuzzy)
         for ($c=1; $c<$conc; $c++) {
            $ok = false;
            foreach ($in[$c] as $i2=>$v2) {
               $key_cmp = call_user_func_array($key_cb, array($i, $i2));
               $val_cmp = call_user_func_array($val_cb, array($v, $v2));
               if (($key_cmp == 0) && ($val_cmp == 0)) {
                  $ok = true;
                  break;
               }
            }
            if (!$ok) {
               continue 2;
            }
         }
         #-- exists in all arrays
         $all[$i] = $v;
      }
      return($all);
   }
}




#-- intersection again
if (!function_exists("array_uintersect_assoc")) {
   function array_uintersect_assoc() {
      $in = func_get_args();
      $val_cb = array_pop($in);
      $all = array();
      $conc = count($in);
      foreach ($in[0] as $i=>$v) {
         #-- test for that entry in any other array
         for ($c=1; $c<$conc; $c++) {
            if (isset($in[$c][$i])) {
               $cmp = call_user_func_array($val_cb, array($v, $in[$c][$i]));
               if ($cmp == 0) { continue; }
            }
            #-- failed
            continue 2;
         }
         #-- exists in all arrays
         # (but for fuzzy matching: only the first entry will be returned here)
         $all[$i] = $v;
      }
      return($all);
   }
}





#-- array intersection, no keys compared, but callback for values
if (!function_exists("array_uintersect")) {
   function array_uintersect() {
      $in = func_get_args();
      $val_cb = array_pop($in);
      $arr1 = array_shift($in);
      $num = count($in);
      $r = array();

      foreach ($arr1 as $i=>$v) {
         #-- must have equivalent value in all other arrays
         for ($c=0; $c<$num; $c++) {
            foreach ($in[$c] as $i2=>$v2) {
               $cmp = call_user_func_array($val_cb, array($v, $v2));
               if ($cmp == 0) {
                  continue 2; //found
               }
            }
            continue 2; //failed
         }
         #-- everywhere
         $r[$i] = $v;
      }
      return($r);
   }
}




#-- diff array, keys ignored, callback for comparing values
if (!function_exists("array_intersect_uassoc")) {
   function array_intersect_uassoc() {
      $args = func_get_args();
      $key_cb = array_pop($args);
      $array1 = array_shift($args);
      $num = count($args);
      $all = array();
      foreach ($array1 as $i=>$v) {
         #-- look through other arrays
         for ($c=0; $c<$num; $c++) {
            $ok = 0;
            foreach ($args[$c] as $i2=>$v2) {
               $cmp = call_user_func_array($key_cb, array($i, $i2));
               if (($cmp == 0) && ($v == $v2)) {
                  $ok = 1;
                  continue 2;
               }
            }
            if (!$ok) { 
               continue 2;
            }
         }
         #-- found in all arrays
         if ($ok) {
            $diff[$i] = $v;
         }
      }
      return($diff);
   }
}


?>