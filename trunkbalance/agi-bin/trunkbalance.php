#!/usr/bin/php -q
<?php

/**********************************
Trunk Balancing Module - agi file
Last edited by lgaetz 2014-11-16
**********************************/


// Using IssabelPBX bootstrap for some later features, requires IssabelPBX 2.9 or higher
if (!@include_once(getenv('ISSABELPBX_CONF') ? getenv('ISSABELPBX_CONF') : '/etc/issabelpbx.conf')) {
include_once('/etc/asterisk/issabelpbx.conf');
}
// set IssabelPBX globals
global $db;  // IssabelPBX asterisk database connector
global $amp_conf;  // array with Asterisk configuration
global $astman;  // AMI

// origninal features are not using IssabelPBX Bootstrap to collect user params
set_time_limit(5);
require('phpagi.php');
require('sqltrunkbal.php');

error_reporting(0);


$AGI = new AGI();
$db1 = new AGIDB($AGI);

if (!isset($argv[1])) {
        $AGI->verbose('Missing trunk info',3);
        exit(1);
}

// get arguements passed to agi
$trunk = $argv[1];            // asterisk ${ARG1} is the trunk number

// if $exten is not passed as arguement 2 then use AGI request to attempt to get the dialed digits
if (isset($argv[2]))
	{
		$exten = $argv[2];            // asterisk ${ARG2} is the $exten number passed to the dialout macro
	}
	else
	{
		if ($AGI->request['agi_extension']=='s')
		{
			$exten = $AGI->request['agi_dnid'];
		}
		else
		{
			$exten = $AGI->request['agi_extension'];
		}

		if (!is_numeric($exten))
		{
			$exten = NULL;		//agi request may not return a useful result so clear variable if not numeric
		}
	}
$AGI->verbose('Dialed digits: '.$exten, 3);


$sql='SELECT * FROM `trunks` WHERE trunkid=\''.$trunk.'\'';
$res = $db1->sql($sql,'ASSOC');
$name=$res[0]['name'];


if (substr($name,0,4)=='BAL_') //balanced trunk
	{
	// default condition is that the trunk is allowed, failure of any rule means trunk gets denied
	$trunkallowed=true;

	$name=substr($name,4);
	$AGI->verbose("This trunk $name is balanced. Evaluating rules", 3);
	$sql='SELECT * FROM `trunkbalance` WHERE description=\''.$name.'\'';
	$baltrunk = $db1->sql($sql,'ASSOC');
	$desttrunk=$baltrunk[0]['desttrunk_id'];
	$disabled =$baltrunk[0]['disabled'];
//	description not needed in this file
	$dialpattern=$baltrunk[0]['dialpattern'];
	$dp_andor=$baltrunk[0]['dp_andor'];
	$notdialpattern=$baltrunk[0]['notdialpattern'];
	$notdp_andor=$baltrunk[0]['notdp_andor'];
	$billing_cycle=$baltrunk[0]['billing_cycle'];
	$billingtime=$baltrunk[0]['billingtime'];
	$billing_day=$baltrunk[0]['billing_day'];
	$billingdate=$baltrunk[0]['billingdate'];
	$billingperiod=$baltrunk[0]['billingperiod'];
	$endingdate=$baltrunk[0]['endingdate'];
	$count_inbound=$baltrunk[0]['count_inbound'];
	$count_unanswered=$baltrunk[0]['count_unanswered'];
	$loadratio=$baltrunk[0]['loadratio'];
	$maxtime=$baltrunk[0]['maxtime'];
	$maxnumber=$baltrunk[0]['maxnumber'];
	$maxidentical=$baltrunk[0]['maxidentical'];
	$timegroup=$baltrunk[0]['timegroup_id'];
	$url=$baltrunk[0]['url'];
	$url_timeout=$baltrunk[0]['url_timeout'];
	$regex=trim($baltrunk[0]['regex']);
	$todaydate=gettimeofday(true);
	$today=getdate();

	if ($disabled == "on") {
		$AGI->verbose('Trunk is disabled, no calls permitted',3);
		$trunkallowed = false;
	}
	
	if ($count_unanswered)  {
		$disposition = "(disposition='ANSWERED' OR disposition='NO ANSWER')";
	} else {
		$disposition = "disposition='ANSWERED'";
	}

	if ($timegroup>0 & $trunkallowed) // check the time group condition
    { 
		$daynames = array("sun"=>0, "mon"=>1, "tue"=>2, "wed"=>3, "thu"=>4, "fri"=>5, "sat"=>6); 
		$monthnames= array ("jan"=>1, "feb"=>2, "mar"=>3, "apr"=>4, "may"=>5, "jun"=>6, "jul"=>7, "aug"=>8, "sep"=>9, "oct"=>10, "nov"=>11, "dec"=>12);
		$timegroupcondition=false;
		$sql='SELECT * FROM `timegroups_details` WHERE timegroupid=\''.$timegroup.'\'';
		$res = $db1->sql($sql,'ASSOC');
		if(is_array($res))
		{
			foreach($res as $timegroupdetail)
		{
		$timedetail=$timegroupdetail['time'];
		$timecondition=true;
		if ($timedetail<>'')
		{
			$AGI->verbose("  Timedetail: $timedetail ", 4);
			list($condtimerange,$conddaysweek,$conddaysmonth,$condmonths)=explode("|",$timedetail);
			
			if ($condmonths<>'*')
			{ 
				$startmonth='';
				$endmonth='';
				list($startmonth,$endmonth)=explode("-",$condmonths);
				if ($endmonth=='') {$endmonth=$startmonth;}
				$endmonthnum=$monthnames[$endmonth];
				$startmonthnum=$monthnames[$startmonth];

				if ((($endmonthnum<$today[mon])&($today[mon]<$startmonthnum)) or
                          (($endmonthnum>=$startmonthnum)& (($today[mon]<$startmonthnum)or($today[mon]>$endmonthnum))))
				{
					$AGI->verbose("     month condition '$condmonths' failed", 4);
					$timecondition=false;
				} else
                {
					$AGI->verbose("     month condition '$condmonths' passed", 4);
				} 

			}

			if ($conddaysmonth<>'*')
			{
				$startdate=0;
				$enddate=0;
			  list($startdate,$enddate)=explode("-",$conddaysmonth);
			  if ($enddate==0) { $enddate=$startdate;}
                       if ((($enddate<$today[mday])&($today[mday]<$startdate)) or
                          (($enddate>=$startdate)& (($today[mday]<$startdate)or($today[mday]>$enddate))))
                       {
                         $AGI->verbose("     day of the month condition '$conddaysmonth' failed", 4);
			    $timecondition=false;
			  } else
                       {
			    $AGI->verbose("     day of the month condition '$conddaysmonth' passed", 4);
			  } 
			}

			if ($conddaysweek<>'*')
			{
			  $startday='';
			  $endday='';
			  list($startday,$endday)=explode("-",$conddaysweek);
			  if ($endday=='') {$endday=$startday;}
			  $startdaynum=$daynames[$startday];
			  $enddaynum=$daynames[$endday];


			  if ((($enddaynum<$today[wday])&($today[wday]<$startdaynum)) or
                          (($enddaynum>=$startdaynum)& (($today[wday]<$startdaynum)or($today[wday]>$enddaynum))))
                       {
                         $AGI->verbose("     day of the week condition '$conddaysweek' failed", 4);
			    $timecondition=false;
			  } else
                       {
			    $AGI->verbose("     day of the week condition '$conddaysweek' passed", 4);
			  } 

			}

			if ($condtimerange<>'*')
			{
			  $starttime='';
			  $endtime='';
			  $todaysminutes=$today[hours]*60+$today[minutes];
			  list($starttime,$endtime)=explode("-",$condtimerange);
			  if ($endtime=='') {$endtime=$starttime;}
			  list($thou,$tmin)=explode(":",$starttime);
			  $starttimemin=$thou*60+$tmin;
			  list($thou,$tmin)=explode(":",$endtime);
			  $endtimemin=$thou*60+$tmin;
			  if ((($endtimemin<$todaysminutes)&($todaysminutes<$starttimemin)) or
                          (($endtimemin>=$starttimemin)& (($todaysminutes<$starttimemin)or($todaysminutes>$endtimemin))))
                       {
                         $AGI->verbose("     time of the day condition '$condtimerange' failed", 4);
			    $timecondition=false;
			  } else
                       {
			    $AGI->verbose("     time of the day condition '$condtimerange' passed", 4);
			  } 


			}

                 
		       if ($timecondition)
		       {
			    $timegroupcondition=true;
		 	    $AGI->verbose("  Timedetail condition passed", 4);
			
		       } else
		       {
		   	   $AGI->verbose("  Timedetail condition failed", 4);
		       }
		   }

              } 
	   } 
	  if ($timegroupcondition)
         {
	    $trunkallowed=true;
 	    $AGI->verbose("Time condition passed", 3);

         } else
         {
	    $trunkallowed=false;
	    $AGI->verbose("Time condition failed", 3);
         }
	}
// end timegroup check


	if (($loadratio>1)&($trunkallowed))
	{
		$randnum=rand(1,$loadratio);
		if ($randnum==1) {
				$AGI->verbose("Balance ratio rule of 1:$loadratio. $randnum was pooled. Rule passed", 3);
			} else
			{
				$AGI->verbose("Balance ratio rule of 1:$loadratio. $randnum was pooled. Rule failed", 3);
				$trunkallowed=false;
			} 
	}

	if ($trunkallowed) //to save time, if the call is already denied ignore the following
	{
		$sqldate='';
		if ($billing_cycle != -1)
		{
			//determine starting date/time of the billing period
			switch ($billing_cycle) {
				case "day":
					$foo=(date("Y-m-d",$todaydate))." ".$billingtime;	// starting date and time in string format
					$bar=strtotime($foo);  //timestamp format for start date/time
					// check if date is in future, if so subtract days worth of seconds
					if ($bar > time()) {
						$bar = $bar - 86400;
					}
					$stringdate=date("Y-m-d H:i",$bar);
					$AGI->verbose("billing date $stringdate", 3);
					$sqldate=' AND calldate>\''.$stringdate.'\'';
				break;
				
				case "week":
					$foo=date("Y-m-d", strtotime('last '. $billing_day))." ".$billingtime;  //string format at start time on previous billing day
					$bar=strtotime($foo);  //timestamp format for start date/time
					// check if start date is more than 7 days ago and if so, add weeks worth of seconds
					if ((time() - $bar) > 604800) {
						$bar = $bar + 604800;
					}
					$stringdate=date("Y-m-d H:i",$bar);
					$AGI->verbose("billing date $stringdate", 3);
					$sqldate=' AND calldate>\''.$stringdate.'\'';
				break;
				
				case "month":
					$diff= $billingdate - (date("j",$todaydate));
					$stringdate=(date("Y-m-",$todaydate)).$billingdate;
					if ($diff>0)
					{
						$billingdate=strtotime($stringdate. " - 1 month");
					} else
					{
						$billingdate=strtotime($stringdate);
					}
					
					$stringdate=date("Y-m-d 00:00",$billingdate);
					$AGI->verbose("billing date $stringdate", 3);
					$sqldate=' AND calldate>\''.$stringdate.'\'';
				break;
				
				case "floating";
					//get the beginning date of the period
					$AGI->verbose("billing period $billingperiod hours", 3);
					$sqldate=' AND calldate>=DATE_SUB(curdate(), INTERVAL '.$billingperiod.' HOUR)';
				break;
			}
				
				
		}

		// break up user supplied match patterns into query
		$sqlpattern='';
		if ($dialpattern!=='') {
			if ($dp_andor == 'on') {
				$combiner = " AND ";
			} else {
				$combiner = " OR ";
			}
			$sqlpattern=' AND (';
			$dps = explode(',',$dialpattern);
			$count = 1;
			foreach ($dps as $dp)  {
				$dp=trim($dp);
				if ($count == 1) {
					$sqlpattern = $sqlpattern." dst LIKE '$dp'";
				} else {
					$sqlpattern = $sqlpattern." $combiner dst LIKE '$dp'";
				}
				$count = $count + 1;
			}
			$sqlpattern = $sqlpattern." ) ";
		}
		
		// break up user supplied non-match patterns into query
		if ($notdialpattern!=='')  {
			if ($notdp_andor== 'on') {
				$combiner = " AND ";
			} else {
				$combiner = " OR ";
			}
			$sqlpattern=$sqlpattern.' AND (';
			$dps = explode(',',$notdialpattern);
			$count = 1;
			foreach ($dps as $dp)  {
				$dp=trim($dp);
				if ($count == 1) {
					$sqlpattern = $sqlpattern." dst NOT LIKE '$dp'";
				} else {
					$sqlpattern = $sqlpattern." $combiner dst NOT LIKE '$dp'";
				}
				$count = $count + 1;
			}
			$sqlpattern = $sqlpattern." ) ";
		}


		// load info from the destination trunk
		$sql='SELECT * FROM `trunks` WHERE trunkid=\''.$desttrunk.'\'';
		$res = $db1->sql($sql,'ASSOC');
		$destrunk_tech=$res[0]['tech'];
		$destrunk_channelid=$res[0]['channelid'];
		switch ($destrunk_tech)
		{
			case 'sip':
				if ($count_inbound) {
					$channel_filter="(dstchannel LIKE 'SIP/".$destrunk_channelid."%' OR channel LIKE 'SIP/".$destrunk_channelid."%')";
				} else {
					$channel_filter="dstchannel LIKE 'SIP/".$destrunk_channelid."%'";
				}
			break;
			case 'iax':
				if ($count_inbound) {
					$channel_filter="(dstchannel LIKE 'IAX2/".$destrunk_channelid."%' OR channel LIKE 'IAX2/".$destrunk_channelid."%')";
				} else {
					$channel_filter="dstchannel LIKE 'IAX2/".$destrunk_channelid."%'";
				}
			break;
			case 'dahdi':
				if ($count_inbound) {
					$channel_filter="(dstchannel LIKE 'DAHDI/".$destrunk_channelid."%' OR channel LIKE 'DAHDI/".$destrunk_channelid."%')";
				} else {
					$channel_filter="dstchannel LIKE 'DAHDI/".$destrunk_channelid."%'";
				}
			break;
			case 'custom':
			$ParsParam = explode("/",$destrunk_channelid);
			switch ($ParsParam[0])
			{
				case 'Dongle':
					if ($count_inbound) {
						$channel_filter="(dstchannel LIKE 'Dongle/".$ParsParam[1]."%' OR channel LIKE 'Dongle/".$ParsParam[1]."%')";
					} else {
						$channel_filter="dstchannel LIKE 'Dongle/".$ParsParam[1]."%'";
					}
				break;
				default: $channel_filter="dstchannel LIKE '%".$destrunk_channelid."%'";
			}
			break;
			default: $channel_filter=$destrunk_channelid;;
		}
		$db2 = new AGIDB($AGI);
		$db2->dbname='asteriskcdrdb';

		//test number of calls
		if ($maxnumber>0)  {
			$sql='SELECT COUNT(*) FROM `cdr` WHERE '.$disposition.' AND '.$channel_filter.' '.$sqldate.$sqlpattern;
			$query= $db2->sql($sql,'NUM');
			$numberofcall=$query[0][0];
			if ($maxnumber>$numberofcall)
			{ 
				$AGI->verbose("$maxnumber max calls. This trunk has now only $numberofcall calls - Rule passed", 3);
				$AGI->verbose($sql, 3);
			} else {
				$AGI->verbose("$maxnumber max calls. This trunk has now $numberofcall calls - Rule failed", 3);
				$trunkallowed=false;
				$AGI->verbose($sql, 3);
			}
		
		}

		//test number of different calls
		if (($maxidentical>0) and ($trunkallowed))
		{
			$sql='SELECT DISTINCT(dst) FROM `cdr` WHERE '.$disposition.' AND '.$channel_filter.' '.$sqldate.$sqlpattern;
			$query= $db2->sql($sql,'NUM');
			$numberofdiffcall=count($query)-1;    //for some reason count is always 1 higher than actual prob because it's a 2D array

			function in_multiarray($elem, $array)   //this function borrowed from stack overflow because in_array doesn't seem to work well on 2D arrays
			{
				$top = sizeof($array) - 1;
				$bottom = 0;
				while($bottom <= $top)
				{
					if($array[$bottom] == $elem)
						return true;
					else 
						if(is_array($array[$bottom]))
							if(in_multiarray($elem, ($array[$bottom])))
								return true;
							
					$bottom++;
				}        
				return false;
			}

			if ($maxidentical>$numberofdiffcall)
			{ 
				$AGI->verbose("$maxidentical max different calls. This trunk has now only $numberofdiffcall calls - Rule passed", 3);
				$AGI->verbose($sql);
			} 
			else
			{
				// check to see if the dialed number is in the array $query and if so allow call otherwise deny
				if (!$exten)
				{
					$AGI->verbose("Cannot determine dialed number and trunk has exceeded call count of $maxidentical - Rule failed", 3);
					$trunkallowed=false;
				}
				else if (in_multiarray($exten,$query)) 
				{
					$AGI->verbose("Trunk has exceeded call count of $maxidentical but dialed number $exten is included in this count - Rule passed", 3);
					$trunkallowed=true;
				}
				else
				{
					$AGI->verbose("Trunk has exceeded call count of $maxidentical and dialed number $exten is not included in this count - Rule failed", 3);
					$trunkallowed=false;
				}
				
			}
		}


		//duration of call
		if (($maxtime>0) and ($trunkallowed))
			{
			$sql='SELECT SUM(ROUND((billsec/60)+0.5)) FROM `cdr` WHERE '.$disposition.' AND '.$channel_filter.' '.$sqldate.$sqlpattern;
			$query= $db2->sql($sql,'NUM');
			$numberofminutes=($query[0][0]); 
			if ($maxtime>$numberofminutes)
				{
				$AGI->verbose("$maxtime max minutes. This trunk has now only $numberofminutes min. - Rule passed", 3);		

				} else
				{
				$AGI->verbose("$maxtime max minutes. This trunk has now $numberofminutes min. - Rule failed", 3);
				$trunkallowed=false;
				}
			}

		//limit date
		if (($trunkallowed) and ($endingdate!=='0000-00-00 00:00:00'))
			{
	 		if ($todaydate<strtotime($endingdate)) 
				{
				$AGI->verbose("Expiration date $endingdate  - Rule passed", 3);
				} else
				{
				$AGI->verbose("Expiration date $endingdate  - Rule failed", 3);
				$trunkallowed=false;
				}
			}
		}
	// URL section, load user provided URL and check regex against it
	if ($url && $regex) {
		$AGI->verbose("Checking URL and regex", 3);

		//break up regex lines into array
		$reg_array = explode("\n",$regex);

		// check URL and regex for string $OUTNUM$ and substitute the dialled digits
		$url = str_replace("\$OUTNUM$", trim($exten), $url);
		$AGI->verbose("URL :".$url, 3);
		$foo = tb_get_url_contents($url);

		$regex_match = false;
		$regex_count = 0;
		foreach ($reg_array as $reg) {
			$reg = str_replace("\$OUTNUM$", trim($exten), $reg);
			$AGI->verbose("regex".++$regex_count.": ".$reg, 3);
			preg_match($reg, $foo, $matches);
			if (count($matches)) {
				$regex_match = true;
				break;
			}
		}

		if ($regex_match) {
			$AGI->verbose("regex match", 3);
		}
		else {
			$AGI->verbose("no regex match", 3);
			$trunkallowed = false;
		}
	}
	if ($trunkallowed)
		{
		$AGI->verbose("Call authorized. The new trunk number is $desttrunk", 3);
		$AGI->set_variable('DIAL_TRUNK', $desttrunk);
		} else
		{
		$AGI->verbose("At least one condition failed. Call refused.", 3);
		}

	} else
	{
	$AGI->verbose("No balancing rules are defined for this trunk", 3);

	}

?>
