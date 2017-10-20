<?php
/**
 *
 * This script implements the base64-encoding functions and base32 and
 * base16 as described in RFC3548.
 *
 * @since never
 * @nonstandard
 *
 */
if (!function_exists("base16_encode")) {


   #-- URL and filename safe variants of base64-encoding
   function base64_encode_safe($str) {
      return strtr(base64_encode($str), "+/", "-_");
   }
   function base64_decode_safe($b64) {
      return base64_decode(strtr($str, "-_", "+/"));
   }


   #-- base16
   function base16_encode($str) {
      $str = unpack("H".(2*strlen($str)), $str);
      $str = chunk_split($str[1]);
      return($str);
   }
   function base16_decode($b16) {
      $b16 = preg_replace("/\s+/", '', $b16);
      $b16 = pack("H*", $b16);
      return($b16);
   }


   #-- base32
   function base32_encode() {
      
      # strtoupper()
      # "A-Z,0-7,="
   }

}
?>