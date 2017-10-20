<?php
/*
   This script simulates the gz*() functions, without actually providing
   compression functionality. The generated data streams will be correct,
   but reading compressed files isn't possible.
   
   Not very useful; should only be used if there is no other way. But if
   your provider seriously doesn't have PHP with builtin zlib support,
   you'd be better off simply switching to someone else...
*/


 #-- fake zlib
 if (!function_exists("gzopen")) {

    function gzopen($fp, $mode) {
       $mode = preg_replace('/[^carwb+]/', '', $mode);
       return(fopen($fp, $mode));
    }
    function gzread($fp, $len) {
       return(fread($fp, $len));
    }
    function gzwrite($fp, $string) {
       return(fwrite($fp, $string));
    }
    function gzputs($fp, $string) {
       return(fputs($fp, $string));
    }
    function gzclose($fp) {
       return(fclose($fp));
    }
    function gzeof($fp) {
       return(feof($fp));
    }
    function gzseek($fp, $offs) {
       return(fseek($fp, $offs, SEEK_SET));
    }
    function gzrewind($fp) {
       return(frewind($fp));
    }
    function gztell($fp) {
       return(ftell($fp));
    }

    function gzpassthru($fp) {
       while (!gzeof($fp)) {
          print(gzred($fp, 1<<20));
       }
       gzclose($fp);
    }
    function readgzfile($fn) {
       if ($fp = gzopen($fn, "rb")) {
          gzpassthru($fp);
       }
    }
   
    function gzfile($fn) {
       return(file($fn));
    }

    function gzgetc($fp) {
       return(fgetc($fp));
    }
    function gzgets($fp, $len) {
       return(fgets($fp, $len));
    }
    function gzgetss($fp, $len, $allowedtags="") {
       return(fgetss($fp, $len, $allowedtags));
    }

 }


 #-- fake compression methods
 if (!function_exists("gzdeflate")) {

    // only returns uncompressed deflate streams
    function gzdeflate($data, $level=0) {
        $gz = "";
        $end = strlen($data);
        $p = 0;
        do {
           $c = $end - $pos;
           if ($c >= 65536) {
              $c = 0xFFFF;
              $end = 0x00;
           }
           else {
              $end = 0x01;
           }
           $gz .= pack("Cvv",
                     ($end << 7) + (00 << 5),  // LAST=0/1, BTYPE=00
                     $c,                       // LEN
                     $c ^ 0xFFFF               // NLEN
                  );
           $gz .= substr($data, $p, $c);
           $p += $c;
        }
        while ($p < $end);
        return($gz);
    }

    // only can strip deflate headers, cannot decompress
    function gzinflate($data, $length=NULL) {
       $end = strlen($data);
       $gz = "";
       if (isset($length) && (($max*0.99) > $length)) {
          trigger_error("gzinflate(): gave up, decompressed string is likely longer than requested", E_USER_ERROR);
          return;
       }
       $out = "";
       $p = 0;
       do {
          $head = ord($data[$p]);
          $last = ($head >> 7);
          if (($head & 0x60) != 00) {
             trigger_error("gzinflate(): cannot decode compressed stream", E_USER_ERROR);
             return;
          }
          $head = unpack("v1LEN/v1NLEN", substr($data, $p+1, 4));
          $c = $head["LEN"];
          if (($c ^ 0xFFFF) != $head["NLEN"]) {
             trigger_error("gzinflate(): data error in stream", E_USER_ERROR);
             return;
          }
          $p += 5;
          $out .= substr($data, $p, $c);
          $p += $c;
       }
       while (($p < $end) && !$last);
       return($out);
    }


//    function gzcompress() {
//    }
//    function gzuncompress() {
//    }

    // without real compression support again
    function gzencode($data, $level=0) {
       $isize = strlen($data);
       $crc32 = crc32($data);
       $gz = "";
       {
          $gz .= pack("nCCVCC",
             $_ID = 0x1f8b,
             $_CM = 0x08,  // deflate fmt
             $_FLG = 0x00, // nothing extra
             $_MTIME = time(),
             $_XFL = 0x00, // no bonus flags
             $_OS = 255    // "unknown"
          );
          $gz .= gzdeflate($data);
          $gz .= pack("VV", $crc32, $isize);
       }
       return($gz);
    }

 }

?>