#!/usr/bin/php -q
<?php
{
	// Wakeconfirm AKA ANNOY
	// Version 1.0

	// all user settings come from the file wake.inc which is shared by both agi files
	require 'wake.inc';

	GLOBAL	$stdin, $stdout, $stdlog, $result, $parm_debug_on, $parm_test_mode;
    
	// These setting are on the WIKI pages http://www.voip-info.org
	ob_implicit_flush(false);
	set_time_limit(30);
	error_reporting(0);

	$stdin = fopen( 'php://stdin', 'r' );
	$stdout = fopen( 'php://stdout', 'w' );

	// You will see a whole bunch of this its for development or if you change anything and
	// want to write to a log file in the TMP directory
	if ($parm_debug_on)
	{
		$stdlog = fopen( $parm_error_log, 'w' );
		fputs( $stdlog, "---Start---\n" );
	}


	// ASTERISK * Sends in a bunch of variables, This accepts them and puts them in an array
	// http://www.voip-info.org/tiki-index.php?page=Asterisk%20AGI%20php
	while ( !feof($stdin) ) 
	{
		$temp = fgets( $stdin );

		if ($parm_debug_on)
			fputs( $stdlog, $temp );

		// Strip off any new-line characters
		$temp = str_replace( "\n", "", $temp );

		$s = explode( ":", $temp );
		$agivar[$s[0]] = trim( $s[1] );
		if ( ( $temp == "") || ($temp == "\n") )
		{
			break;
		}
	} 


	// There are two ways to contact a phone, by its channel or by its local 
	// extension number.  This next session will extract both sides
    
	// split the Channel  SIP/11-3ef4  or Zap/4-1 into is components
	$channel = $agivar[agi_channel];
	if (preg_match('.^([a-zA-Z]+)/([0-9]+)([0-9a-zA-Z-]*).', $channel, $match) )
	{
		$sta = trim($match[2]);
		$chan = trim($match[1]);
	}



	// Go Split the Caller ID into its components
	$callerid = $agivar['agi_extension'];

	// First look for the Extension in <####> 
	if (preg_match('/<([ 0-9]+)>/', $callerid, $match) )
	{
		$cidn = trim($match[1]);
	}
	else	// Did not find the <##...> look for the first number in the string to use as CID
	{
		if (preg_match('/([0-9]+)/', $callerid, $match) )
		{
			$cidn = trim($match[1]);
		}
		else
			$cidn = -1;		// I'm saying an error no caller id # found
	}
    

	//=========================================================================
	// This is where we interact with the caller.  Answer the phone and so on
	//=========================================================================


	$rc = execute_agi( "ANSWER ");

	sleep(1);	// Wait for the channel to get created and RTP packets to be sent
					// On my system the welcome you would only hear 'elcome'  So I paused for 1 second

    


	if ( !$rc[result] )
		$rc = execute_agi( "STREAM FILE hello \"\" ");
	if ( !$rc[result] )
		$rc = execute_agi( "STREAM FILE this-is-yr-wakeup-call \"\" ");

	// initialize a counter so the while loop will eventually expire
	$lgcount = 0;

	// Start prompting them if they want to snooze or turn off the wake up
	while ( !$rc[result] && $lgcount < 15)   // set hard limit of 15 repeats
	{
		if ( !$rc[result] )
			$rc = execute_agi( "STREAM FILE to-cancel-wakeup \"1234\" ");
		if ( !$rc[result] )
			$rc = execute_agi( "STREAM FILE press-1 \"1234\" ");
		if ( !$rc[result] )
			$rc = execute_agi( "STREAM FILE to-snooze-for \"1234\" ");
		if ( !$rc[result] )
			$rc = execute_agi( "STREAM FILE digits/5 \"1234\" ");
		if ( !$rc[result] )
			$rc = execute_agi( "STREAM FILE minutes \"1234\" ");
		if ( !$rc[result] )
			$rc = execute_agi( "STREAM FILE press-2 \"1234\" ");
		if ( !$rc[result] )
			$rc = execute_agi( "STREAM FILE to-snooze-for \"1234\" ");
		if ( !$rc[result] )
			$rc = execute_agi( "STREAM FILE digits/10 \"1234\" ");
		if ( !$rc[result] )
			$rc = execute_agi( "STREAM FILE minutes \"1234\" ");
		if ( !$rc[result] )
			$rc = execute_agi( "STREAM FILE press-3 \"1234\" ");
		if ( !$rc[result] )
			$rc = execute_agi( "STREAM FILE to-snooze-for \"1234\" ");
		if ( !$rc[result] )
			$rc = execute_agi( "STREAM FILE digits/15 \"1234\" ");
		if ( !$rc[result] )
			$rc = execute_agi( "STREAM FILE minutes \"1234\" ");
		if ( !$rc[result] )
			$rc = execute_agi( "STREAM FILE press-4 \"1234\" ");
		if ( !$rc[result] )
		{
			$rc = execute_agi( "WAIT FOR DIGIT 15000");
		}
		if ( $rc[result] != -1 )
		{
			if ( $rc[result] == 49 || $rc[result] == 50 || $rc[result] == 51 || $rc[result] == 52 )
			{
				; // Do nothing correct input
			}
			else
			{
				// This was just for fun, if they press something other than 1, 2, 3, or 4
				$rc[result] = 0;
				$rc = execute_agi( "STREAM FILE im-sorry \"\" ");
				$rc = execute_agi( "STREAM FILE you-dialed-wrong-number \"\" ");
				$rc = execute_agi( "STREAM FILE i-dont-understand3 \"\" ");
				$rc = execute_agi( "STREAM FILE your \"\" ");
				$rc = execute_agi( "STREAM FILE communications \"\" ");

			}
		}
		$lgcount++;   // increment counter so loop will give up eventually
	}

	switch( $rc[result] )
	{
	case '49':	// Pressed 1  - This is to cancel the wakeup call
		{
				execute_agi( "EXEC background \"wakeup-call-cancelled\" ");
				execute_agi( "EXEC wait \"1\" ");
				execute_agi( "EXEC background \"goodbye\" ");
				execute_agi( "HANGUP" );
				exit;
		}
		break;

	case '50':		// Pressed 2 - Snooze for 5 minutes
		{
			$time_wakeup = time( );
			$time_wakeup += 300;

			create_wakeup( $time_wakeup );

			execute_agi( "EXEC background \"rqsted-wakeup-for\" ");
			execute_agi( "EXEC background \"digits/5\" ");
			execute_agi( "EXEC background \"minutes\" ");
			execute_agi( "EXEC background \"vm-from\" ");
			execute_agi( "EXEC background \"now\" ");
		}
		break;

	case '51':		// Pressed 3 - Snooze for 10 minutes
		{
			$time_wakeup = time( );
			$time_wakeup += 600;

			create_wakeup( $time_wakeup );

			execute_agi( "EXEC background \"rqsted-wakeup-for\" ");
			execute_agi( "EXEC background \"digits/10\" ");
			execute_agi( "EXEC background \"minutes\" ");
			execute_agi( "EXEC background \"vm-from\" ");
			execute_agi( "EXEC background \"now\" ");
		}
		break;

	case '52':		// Pressed 4 - Snooze for 15 minutes
		{
			$time_wakeup = time( );
			$time_wakeup += 900;

			create_wakeup( $time_wakeup );

			execute_agi( "EXEC background \"rqsted-wakeup-for\" ");
			execute_agi( "EXEC background \"digits/15\" ");
			execute_agi( "EXEC background \"minutes\" ");
			execute_agi( "EXEC background \"vm-from\" ");
			execute_agi( "EXEC background \"now\" ");
		}
		break;
	}

$rc = execute_agi( "STREAM FILE goodbye \"\" ");
	execute_agi( "HANGUP" );
	exit;
}

// ----------------------------------------------
// This will say military time in AM/PM format
// ----------------------------------------------
function say_wakeup( $wtime )
{
    GLOBAL	$stdin, $stdout, $stdlog, $parm_debug_on;
    
    $pm = 0;
    
    if ($wtime > 1159 )
    {
        $wtime -=1200;
        $pm = 1;
    }
    
    if ($wtime <= 59 )
        $wtime += 1200;
    
    if ( strlen( $wtime ) == 3 )
        $wtime = '0' . $wtime;
    
    
    $h = substr( $wtime, 0, 2 );
    $h1 = substr( $wtime, 0, 1 );
    $h2 = substr( $wtime, 1, 1 );
    $m = substr( $wtime, 2, 2 );
    $m1 = substr( $wtime, 2, 1);
    $m2 = substr( $wtime, 3, 1);
    
    
    if ($parm_debug_on)
        fputs( $stdlog, "Wakeup time is set to $wtime\n" );
    
    $rc = execute_agi( "STREAM FILE rqsted-wakeup-for \"\" ");
    
    if ( !$rc[result] )
    {
        if ( $h1 == 0 ) 
            $rc = execute_agi( "SAY NUMBER $h2 \"\"");
        else
            $rc = execute_agi( "SAY NUMBER $h \"\"");
        
        if ( !$rc[result] )
        {
            if ($m == 0 )
                $rc = execute_agi( "STREAM FILE digits/oclock \"\" ");
            else
            {		
                if ( $m1 == 0 ) 
                {
                    $rc = execute_agi( "STREAM FILE digits/oh \"\" ");
                    $rc = execute_agi( "SAY NUMBER $m2 \"\" ");
                }
                else
                    $rc = execute_agi( "SAY NUMBER $m \"\"");
            }
            if ( !$rc[result] )
            {
                if ( $pm )
                    $rc = execute_agi( "STREAM FILE digits/p-m \"\" ");
                else
                    $rc = execute_agi( "STREAM FILE digits/a-m \"\" ");
            }
        }
    }	
}


//--------------------------------------------------
// This function will send out the command and get 
//	the response back
//--------------------------------------------------
function execute_agi( $command )
{
    GLOBAL	$stdin, $stdout, $stdlog, $parm_debug_on;
    
    fputs( $stdout, $command . "\n" );
    fflush( $stdout );
    if ($parm_debug_on)
        fputs( $stdlog, $command . "\n" );
    
    $resp = fgets( $stdin, 4096 );
    
    if ($parm_debug_on)
        fputs( $stdlog, $resp );
    
    if ( preg_match("/^([0-9]{1,3}) (.*)/", $resp, $matches) ) 
    {
        if (preg_match('/result=([-0-9a-zA-Z]*)(.*)/', $matches[2], $match)) 
        {
            $arr['code'] = $matches[1];
            $arr['result'] = $match[1];
            if (isset($match[3]) && $match[3])
                $arr['data'] = $match[3];
            return $arr;
        } 
        else 
        {
            if ($parm_debug_on)
                fputs( $stdlog, "Couldn't figure out returned string, Returning code=$matches[1] result=0\n" );	
            $arr['code'] = $matches[1];
            $arr['result'] = 0;
            return $arr;
        }
   	} 
    else 
    {
        if ($parm_debug_on)
            fputs( $stdlog, "Could not process string, Returning -1\n" );
        $arr['code'] = -1;
        $arr['result'] = -1;
        return $arr;
    }
}

function create_wakeup( $time_wakeup )
{
	GLOBAL $parm_chan_ext, $parm_temp_dir, $parm_call_dir, $parm_debug_on, $chan, $sta, $cidn, $agivar, $parm_maxretries, $parm_retrytime, $parm_waittime, $parm_wakeupcallerid, $parm_application, $parm_data, $stdin, $stdout, $stdlog;

	$w = getdate( $time_wakeup );

	$foo = array(
		time  => $time_wakeup,
		date => 'unused',
		ext => $cidn,
		maxretries => $parm_maxretries,
		retrytime => $parm_retrytime,
		waittime => $parm_waittime,
		callerid => $parm_wakeupcallerid,
		application => $parm_application,
		data => $parm_data,
		);

	hotelwakeup_gencallfile($foo);

}
