<?php
/**
 * These two alternate bcmath implementations don't emulate arbitrary
 * precision at all. And therefore have been removed from bcmath.php.
 *
 */


#-- GMP
if (!function_exists("bcadd") && function_exists("gmp_strval")) {
   function bcadd($a, $b) {
      return gmp_strval(gmp_add($a, $b));
   }
   function bcsub($a, $b) {
      return gmp_strval(gmp_sub($a, $b));
   }
   function bcmul($a, $b) {
      return gmp_strval(gmp_mul($a, $b));
   }
   function bcdiv($a, $b, $precision=NULL) {
      $qr = gmp_div_qr($a, $b);
      $q = gmp_strval($qr[0]);
      $r = gmp_strval($qr[1]);
      if ((!$r) || ($precision===0)) {
         return($q);
      }
      else {
         if (isset($precision)) {
            $r = substr($r, 0, $precision);
         }
         return("$q.$r");
      }
   }
   function bcmod($a, $b) {
      return gmp_strval(gmp_mod($a, $b));
   }
   function bcpow($a, $b) {
      return gmp_strval(gmp_pow($a, $b));
   }
   function bcpowmod($x, $y, $mod) {
      return gmp_strval(gmp_powm($x, $y, $mod));
   }
   function bcsqrt($x) {
      return gmp_strval(gmp_sqrt($x));
   }
   function bccomp($a, $b) {
      return gmp_cmp($a, $b);
   }
   function bcscale($scale="IGNORED") {
      trigger_error("bcscale(): ignored", E_USER_ERROR);
   }
}//gmp emulation



#-- bigint
// @dl("php_big_int".PHP_SHLIB_SUFFIX))
if (!function_exists("bcadd") && function_exists("bi_serialize")) {
   function bcadd($a, $b) {
      return bi_to_str(bi_add($a, $b));
   }
   function bcsub($a, $b) {
      return bi_to_str(bi_sub($a, $b));
   }
   function bcmul($a, $b) {
      return bi_to_str(bi_mul($a, $b));
   }
   function bcdiv($a, $b) {
      return bi_to_str(bi_div($a, $b));
   }
   function bcmod($a, $b) {
      return bi_to_str(bi_mod($a, $b));
   }
   function bcpow($a, $b) {
      return bi_to_str(bi_pow($a, $b));
   }
   function bcpowmod($a, $b, $c) {
      return bi_to_str(bi_powmod($a, $b, $c));
   }
   function bcsqrt($a) {
      return bi_to_str(bi_sqrt($a));
   }
   function bccomp($a, $b) {
      return bi_cmp($a, $b);
   }
   function bcscale($scale="IGNORED") {
      trigger_error("bcscale(): ignored", E_USER_ERROR);
   }
}


?>