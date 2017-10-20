<?php
/*
   simplistic exception handling for PHP4
   --------------------------------------
   
   As you might know, PHP5 introduced exceptions, similiar to Javas. This
   feature of course cannot be used for PHP4-compatible scripts, and it can
   in no way be emulated by a functional API emulation like "upgrade.php".

   To use an exception-like scheme in PHP4 you'd have to agree on conventions
   - lots of. And exceptions won't look object-oriented anymore and not be
   compliant with the PHP5 scheme - the names are in fact held incompatible
   to avoid conflicts in the language semantics (reserved words).
   

   interfaces
   ----------
  
    - class Exception 
    - global variable $_EXCEPTION (- should better be a list of excptn objs?)
    - _try() function
    - _throw() function
    - _catch() function


   howto
   -----
   
    - prefix a block of commands with _try();
      this will initialize exception handling (evtl. resets internal vars)
    - errors are thrown, by calling _throw() with an Exception-class derived
      object instance as parameter, and returning immediately
    - exceptions are "catched" in if() statements, the _catch() function
      with a classname as parameter returns false or the $_EXCEPTION object
    - you shouldn't use set_exception_handler(), but $_EXCEPTION="funcname";

    #-- main code      
    _try();
    {
       sub_call();
    }
    if ($e = _catch("Special_Case")) {
       echo $e->broken_file();
    }
    if ($e = _catch("Exception")) {
       echo "Something broke, I'd say.";
    }
    
    #-- error-prone
    function sub_call() {
       // ...
       _throw(new Exception("error",255)); return();
    }

    
   note
   ----
   
   Please don't send hatemails only because you feel the syntax is too
   far away from PHP5s native exception handling and counter to that in
   other languages. And the underscores in this agreement are just to
   prevent conflicts with PHP5 constructs - this is not yet another case
   of PHP-underscoritis ;)
   
   -> there has been another PHP framework which implemented exceptions
      long before PHP5 came out; I just don't know anymore which it was
      //@TODO: build a search engine similiar to Google to find that out
*/


#-- base class for exceptions
if (!class_exists("exception")) {
   class Exception
   {
      #-- attributes
      var $message = "";
      var $code = 0;
      var $file = NULL;
      var $line = NULL;
      var $backtrace = NULL;
      
      #-- constructor
      function Exception($message="", $code=0) {
      
         #-- values
         $this->message = $message;
         $this->code = $code;
         
         #-- debugging
         $this->backtrace = debug_backtrace();
         array_shift($this->backtrace);
         $this->file = @$this->backtrace[0]["file"];
         $this->line = @$this->backtrace[0]["line"];
      }
      
      #-- get_ wrappers
      function getMessage() {
         return($this->message);
      }
      function getCode() {
         return($this->code);
      }
      function getFile() {
         return($this->file);
      }
      function getLine() {
         return($this->line);
      }
      function getTrace() {
         return($this->backtrace);
      }
      function getTraceAsString() {
         return(var_export($this->backtrace, TRUE));
      }
      
      #-- output
      function __toString() {
         return($this->message);
      }
   }
}



#-- initialize exception handling for next block
function _try()
{
   global $_EXCEPTION;

   #-- clean up
   if (!is_string($_EXCEPTION) || !function_exists($_EXCEPTION)) {
      $_EXCEPTION = new Object();
   }
}


#-- use for throwing errors
function _throw($obj) {
   global $_EXCEPTION;

   #-- quick
   if (is_string($_EXCEPTION) && function_exists($_EXCEPTION)) {
      $_EXCEPTION($obj);
   }

   #-- what do we do if there's already an exception?
   if ($_EXCEPTION) {
      // ???
      trigger_error("_throw: there is already an unhandled exception on the stack", E_USER_ERROR);
   }

   #-- generate object from error message
   if (!is_object($obj)) {
      $_EXCEPTION = new Exception("$obj");
   }

   #-- pass
   $_EXCEPTION = $obj;

   return(true);
   // break 5;  (after throwing an exception, you should
   //           exit from your current function quickly)
}


#-- check if exception thrown
function &_catch($classname="Exception") {
   global $_EXCEPTION;
   static $e;

   #-- checked for a specific error type / exception class
   if (is_object($_EXCEPTION) && (($classname == "*") || is_a($_EXCEPTION, $classname))) {
      $e = &$_EXCEPTION;   //@FIX: remove reference passing, seems unnecessary
      unset($_EXCEPTION);  // this doesn't clean the global var  [but _try() does]
   }
   else {
      $e = false;
   }

   #-- give out extracted exception   
   return $e;
}


#-- functional additions
if (!function_exists("debug_backtrace")) {
   function debug_backtrace() {
      return array();
   }
}


#-- sets global state
if (!function_exists("set_exception_handler")) {
   // quick hack, should use a different func name
   function set_exception_handler($func) {
      global $_EXCEPTION;
      $_EXCEPTION = $func;
   }
}


?>