<?php

// This file collects user settings for the IssabelPBX Wakeup call module
// initially they were hard coded here by the user, but this file has been
// modified to get user settings from the Module
// last edited Sept 26, 2011 by lgaetz

// Include IssabelPBX bootstrap settings
$bootstrap_settings['issabelpbx_auth'] = false;
if (!@include_once(getenv('ISSABELPBX_CONF') ? getenv('ISSABELPBX_CONF') : '/etc/issabelpbx.conf')) {
	include_once('/etc/asterisk/issabelpbx.conf');
}

//  With IssabelPBX bootstrap, we can reuse this function which gets the module config provided by user
	$date = hotelwakeup_getconfig();

   	// ------------
	// CONFIG Parms
	// ------------
	
	// If the application is having problems you can log to this file
	$parm_error_log =  $amp_conf[ASTLOGDIR].'/wakeup.log';   
	
	// Set to 1 to turn on the log file
	$parm_debug_on = 1;	  //  currently this setting is not set in the module
	
	// This is where the Temporary WakeUp Call Files will be created
	// $parm_temp_dir =  '/var/spool/asterisk/tmp';     // old setting
	$parm_temp_dir =  $amp_conf[ASTSPOOLDIR].'/tmp';
	// Create Temporary WakeUp directory if it doesn't exist, as 0775 (rwxrwxrx), limited by overall umask
	if (!is_dir ($parm_temp_dir)) mkdir ($parm_temp_dir, 0775);
	
	// This is where the WakeUp Call Files will be moved to when finished
	// $parm_call_dir = '/var/spool/asterisk/outgoing';    //old setting
	$parm_call_dir = $amp_conf[ASTSPOOLDIR].'/outgoing';

	// How many times to try the call
	//$parm_maxretries = 3;   // old setting
	$parm_maxretries = $date['maxretries'];

	// How long to keep the phone ringing
	// $parm_waittime = 60;		// old settings
	$parm_waittime = $date['waittime'];

	// Number of seconds between retries
	// $parm_retrytime = 60;   // old setting
	$parm_retrytime = $date['retrytime'];

	// Caller ID of who the wakeup call is from Change this to the extension you want to display on the phone
	// $parm_wakeupcallerid = '"Wake Up Call" ';   // old setting
	$parm_wakeupcallerid = $date[cnam]." <".$date[cid].">";

	// Set to 1 to use the Channel
	// Set to 0 for Caller ID,  Caller IS is assumed just a number ### or "Name Name" <##>
	// The big difference is when using caller ID, wakeup will call ANY phone with that extension number
	// Where using Channel will only wake up the one specific channel
	$parm_chan_ext = 0;   // this setting is not set in the module
	
	// Set to 1 to allow 700# or 0700 to be entered for time, less than 4 digits you can press # key
	// Set to 0 if you require 4 digit entry for time 0500 or 1200 or 0000
	$parm_short_entry = 1;          // this setting is not set in the module

	// ----------------------------------------------------
	// Which application to run when the call is connected.  
	//$parm_application = 'MusicOnHold';
	//$parm_data = '';
	
	// -- Use this for the ANNOY application
	$parm_application = 'AGI';    // this var is not set in the module, but future versions will include it
	$parm_data = 'wakeconfirm.php';  // this var is not set in the module, but future versions will include it
	// ----------------------------------------------------
	
	// Which method to use for time entry
	// 0 current method, if after 13:00 won't prompt for am/pm
	// 1 military time never prompt for am/pm
	// 2 always prompt for am/pm and only accept to 1259 for time
	$parm_prompt_ampm = 0;
	
	// Operator Mode, Allow an extension to key in wakeup calls for other extensions
	//	$parm_operator_mode = 1;    // old method
	$parm_operator_mode = $date['operator_mode'];	
	
	// Operator Extensions
	// Enter any extension that is allowed to enter in operator mode - Caller ID is used to validate
	//	$parm_operator_extensions= array(  00, 00, 00);  // old method
		if(eregi(',', $date[operator_extensions])) {
			$poe = explode(',', eregi_replace(' ', '', $date[operator_extensions]));
		} else {
			$poe = array($date[operator_extensions]);
		}
	$parm_operator_extensions = $poe;

	// The max length of an extension when entering by operator
	//	$parm_operator_ext_len = 3;   // old method
	$parm_operator_ext_len = $date['extensionlength'];

	//-------------------
	// END CONFIG PARMS
	//-------------------

