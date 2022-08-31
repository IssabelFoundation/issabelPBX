<?php
function checkWakeUpProp($file) {

    $myresult = array();

    $bfile = basename($file);
    $tmp = explode(".", $bfile);

    $myresult['month']   = date('m',filemtime($file));
    $myresult['day']     = date('d',filemtime($file));
    $myresult['year']    = date('Y',filemtime($file));
    $myresult['hour']    = date('H',filemtime($file));
    $myresult['minute']  = date('i',filemtime($file));
    $myresult['tstamp']  = $tmp[1];
    $myresult['ext']     = $tmp[3];
    return $myresult;
}

// this function required to make the feature code work
function hotelwakeup_get_config($engine) {
	$modulename = 'hotelwakeup';

	// This generates the dialplan
	global $ext;
	global $asterisk_conf;
	switch($engine) {
		case "asterisk":
			if (is_array($featurelist = featurecodes_getModuleFeatures($modulename))) {
				foreach($featurelist as $item) {
					$featurename = $item['featurename'];
					$fname = $modulename.'_'.$featurename;
					if (function_exists($fname)) {
						$fcc = new featurecode($modulename, $featurename);
						$fc = $fcc->getCodeActive();
						unset($fcc);

						if ($fc != '')
							$fname($fc);
					} else {
						$ext->add('from-internal-additional', 'debug', '', new ext_noop($modulename.": No func $fname"));
					}
				}
			}
		break;
	}
}

// this function required to make the feature code work
function hotelwakeup_hotelwakeup($c) {
	global $ext;
	global $asterisk_conf;

	$id = "app-hotelwakeup"; // The context to be included

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal
	$ext->add($id, $c, '', new ext_Macro('user-callerid'));
	$ext->add($id, $c, '', new ext_answer(''));
	$ext->add($id, $c, '', new ext_wait(1));
	$ext->add($id, $c, '', new ext_AGI('wakeupphp'));
	$ext->add($id, $c, '', new ext_Hangup);
	}


function hotelwakeup_saveconfig() {
	global $db;

	# clean up
	$operator_mode = $db->escapeSimple($_POST['operator_mode']);
	$extensionlength = $db->escapeSimple($_POST['extensionlength']);
	$operator_extensions = $db->escapeSimple($_POST['operator_extensions']);
	$waittime = $db->escapeSimple($_POST['waittime']);
	$retrytime = $db->escapeSimple($_POST['retrytime']);
	$maxretries = $db->escapeSimple($_POST['maxretries']);
	$calleridtext = $db->escapeSimple($_POST['calleridtext']);
	$calleridnumber = $db->escapeSimple($_POST['calleridnumber']);

	# Make SQL thing
	$sql = "UPDATE `hotelwakeup` SET";
	$sql .= " `maxretries`='{$maxretries}'";
	$sql .= ", `waittime`='{$waittime}'";
	$sql .= ", `retrytime`='{$retrytime}'";
	$sql .= ", `extensionlength`='{$extensionlength}'";
	$sql .= ", `cnam`='{$calleridtext}'";
	$sql .= ", `cid`='{$calleridnumber}'";
	$sql .= ", `operator_mode`='{$operator_mode}'";
	$sql .= ", `operator_extensions`='{$operator_extensions}'";
//	$sql .= " LIMIT 1;";

	sql($sql);
}

function hotelwakeup_getconfig() {
// this function gets the values from the wakeup database, and returns them in an associative array

	global $db;
	$sql = "SELECT * FROM hotelwakeup LIMIT 1";
	$results_2d = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
	$results_1d = array();
	foreach ($results_2d[0] as $key => $value) {
		$results_1d[$key] = $value;
	}
	return $results_1d;

}

function hotelwakeup_gencallfile($foo) {
// This function will generate the wakeup call file based on the array provided

/**** array format ******
array(
	time  => timestamp value,
	ext => phone number,
	maxretries => int value seconds,
	retrytime => int value seconds,
	waittime => int value seconds,
	callerid => in 'name <number>' format,
	application => value,
	data => value,
	tempdir => path to temp directory including trailing slash
	outdir => path to outgoing directory including trailing slash
	filename => filename to use for call file
)
**** array format ******/

    if (!isset($foo['tempdir'])) {
		$foo['tempdir'] = "/var/spool/asterisk/tmp/";
	}
    if (!isset($foo['outdir'])) {
		$foo['outdir'] = "/var/spool/asterisk/outgoing/";
	}
    if (!isset($foo['filename'])) {
		$foo['filename'] = "wuc.".$foo['time'].".ext.".$foo['ext'].".call";
	}

	$tempfile = $foo['tempdir'].$foo['filename'];
	$outfile = $foo['outdir'].$foo['filename'];

	// Delete any old .call file with the same name as the one we are creating.
	if( file_exists( "$outfile" ) ) {
		unlink($outfile);
    }

    if($foo['originalfile']!='') {
        if(file_exists($foo['originalfile'])) {
            unlink($foo['originalfile']);
        }
    }

	// Create up a .call file, write and close
	$wuc = fopen( $tempfile, 'w');
	fputs( $wuc, "channel: Local/".$foo['ext']."@from-internal\n" );
	fputs( $wuc, "maxretries: ".$foo['maxretries']."\n");
	fputs( $wuc, "retrytime: ".$foo['retrytime']."\n");
	fputs( $wuc, "waittime: ".$foo['waittime']."\n");
	fputs( $wuc, "callerid: ".$foo['callerid']."\n");
	fputs( $wuc, "application: ".$foo['application']."\n");
	fputs( $wuc, "data: ".$foo['data']."\n");
	fclose( $wuc );

	// set time of temp file and move to outgoing
	touch( $tempfile, $foo['time'], $foo['time'] );
    rename( $tempfile, $outfile );

    return $foo['time']."-".$foo['ext'];
}

// compare version numbers of local module.xml and remote module.xml 
// returns true if a new version is available
function hotelwakeup_vercheck() {
	$newver = false;
	if ( function_exists(hotelwakeup_xml2array)){
		$module_local = hotelwakeup_xml2array("modules/hotelwakeup/module.xml");
		$module_remote = hotelwakeup_xml2array("https://raw.github.com/POSSA/Hotel-Style-Wakeup-Calls/master/module.xml");
		if ( $module_remote[module][version] > $module_local[module][version])
			{
			$newver = true;
			}
		return ($newver);
		}
	}

//Parse XML file into an array
function hotelwakeup_xml2array($url, $get_attributes = 1, $priority = 'tag')  {
	$contents = "";
	if (!function_exists('xml_parser_create'))
	{
		return array ();
	}
	$parser = xml_parser_create('');
	if(!($fp = @ fopen($url, 'rb')))
	{
		return array ();
	}
	while(!feof($fp))
	{
		$contents .= fread($fp, 8192);
	}
	fclose($fp);
	xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, trim($contents), $xml_values);
	xml_parser_free($parser);
	if(!$xml_values)
	{
		return; //Hmm...
	}
	$xml_array = array ();
	$parents = array ();
	$opened_tags = array ();
	$arr = array ();
	$current = & $xml_array;
	$repeated_tag_index = array ();
	foreach ($xml_values as $data)
	{
		unset ($attributes, $value);
		extract($data);
		$result = array ();
		$attributes_data = array ();
		if (isset ($value))
		{
			if($priority == 'tag')
			{
				$result = $value;
			}
			else
			{
				$result['value'] = $value;
			}
		}
		if(isset($attributes) and $get_attributes)
		{
			foreach($attributes as $attr => $val)
			{
				if($priority == 'tag')
				{
					$attributes_data[$attr] = $val;
				}
				else
				{
					$result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
				}
			}
		}
		if ($type == "open")
		{
			$parent[$level -1] = & $current;
			if(!is_array($current) or (!in_array($tag, array_keys($current))))
			{
				$current[$tag] = $result;
				if($attributes_data)
				{
					$current[$tag . '_attr'] = $attributes_data;
				}
				$repeated_tag_index[$tag . '_' . $level] = 1;
				$current = & $current[$tag];
			}
			else
			{
				if (isset ($current[$tag][0]))
				{
					$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
					$repeated_tag_index[$tag . '_' . $level]++;
				}
				else
				{
					$current[$tag] = array($current[$tag],$result);
					$repeated_tag_index[$tag . '_' . $level] = 2;
					if(isset($current[$tag . '_attr']))
					{
						$current[$tag]['0_attr'] = $current[$tag . '_attr'];
						unset ($current[$tag . '_attr']);
					}
				}
				$last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
				$current = & $current[$tag][$last_item_index];
			}
		}
		else if($type == "complete")
		{
			if(!isset ($current[$tag]))
			{
				$current[$tag] = $result;
				$repeated_tag_index[$tag . '_' . $level] = 1;
				if($priority == 'tag' and $attributes_data)
				{
					$current[$tag . '_attr'] = $attributes_data;
				}
			}
			else
			{
				if (isset ($current[$tag][0]) and is_array($current[$tag]))
				{
					$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
					if ($priority == 'tag' and $get_attributes and $attributes_data)
					{
						$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
					}
					$repeated_tag_index[$tag . '_' . $level]++;
				}
				else
				{
					$current[$tag] = array($current[$tag],$result);
					$repeated_tag_index[$tag . '_' . $level] = 1;
					if ($priority == 'tag' and $get_attributes)
					{
						if (isset ($current[$tag . '_attr']))
						{
							$current[$tag]['0_attr'] = $current[$tag . '_attr'];
							unset ($current[$tag . '_attr']);
						}
						if ($attributes_data)
						{
							$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
						}
					}
					$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
				}
			}
		}
		else if($type == 'close')
		{
			$current = & $parent[$level -1];
		}
	}
	return ($xml_array);
}

function wuc_match_pattern_all($array, $number){

	// If we did not get an array, it's probably a list. Convert it to an array.
	if(!is_array($array)){
		$array =  explode("\n",trim($array));		
	}

	$match = false;
	$pattern = false;
	
	// Search for a match
	foreach($array as $pattern){
		// Strip off any leading underscore
		$pattern = (substr($pattern,0,1) == "_")?trim(substr($pattern,1)):trim($pattern);
		if($match = wuc_match_pattern($pattern,$number)){
			break;
		}elseif($pattern == $number){
			$match = $number;
			break;
		}
	}

	// Return an array with our results
	return array(
		'pattern' => $pattern,
		'number' => $match,
		'status' => (isset($array[0]) && (strlen($array[0])>0))
	);
}

/**
	Parses Asterisk dial patterns and produces a resulting number if the match is successful or false if there is no match.
*/

function wuc_match_pattern($pattern, $number)
{
	global $debug;
	$pattern = trim($pattern);
	$p_array = str_split($pattern);
	$tmp = "";
	$expression = "";
	$new_number = false;
	$remove = NULL;
	$insert = "";
	$error = false;
	$wildcard = false;
	$match = $pattern?true:false;
	$regx_num = "/^\[[0-9]+(\-*[0-9])[0-9]*\]/i";
	$regx_alp = "/^\[[a-z]+(\-*[a-z])[a-z]*\]/i";

	// Try to build a Regular Expression from the dial pattern
	$i = 0;
	while (($i < strlen($pattern)) && (!$error) && ($pattern))
	{
		switch(strtolower($p_array[$i]))
		{
			case 'x':
				// Match any number between 0 and 9
				$expression .= $tmp."[0-9]";
				$tmp = "";
				break;
			case 'z':
				// Match any number between 1 and 9
				$expression .= $tmp."[1-9]";
				$tmp = "";
				break;
			case 'n':
				// Match any number between 2 and 9
				$expression .= $tmp."[2-9]";
				$tmp = "";
				break;
			case '[':
				// Find out if what's between the brackets is a valid expression.
				// If so, add it to the regular expression.
				if(preg_match($regx_num,substr($pattern,$i),$matches)
					||preg_match($regx_alp,substr(strtolower($pattern),$i),$matches))
				{
					$expression .= $tmp."".$matches[0];
					$i = $i + (strlen($matches[0])-1);
					$tmp = "";
				}
				else
				{
					$error = "Invalid character class";
				}
				break;
			case '.':
				// Match one or more occurrences of any number
				if(!$wildcard){
					$wildcard = true;
					$expression .= $tmp."[0-9]+";
					$tmp = "";
				}else{
					$error = "Cannot have more than one wildcard";
				}
				break;
			case '!':
				// Match zero or more occurrences of any number
				if(!$wildcard){
					$wildcard = true;
					$expression .= $tmp."[0-9]*";
					$tmp = "";
				}else{
					$error = "Cannot have more than one wildcard";
				}
				break;

			case '+':
				// Prepend any numbers before the '+' to the final match
                                // Store the numbers that will be prepended for later use
				if(!$wildcard){
					if($insert){
						$error = "Cannot have more than one '+'";
					}elseif($expression){
						$error = "Cannot use '+' after X,Z,N or []'s";
					}else{
						$insert = $tmp;
						$tmp = "";
					}
				}else{
					$error = "Cannot have '+' after wildcard";
				}
				break;
			case '|':
				// Any numbers/expression before the '|' will be stripped
				if(!$wildcard){
					if($remove){
						$error = "Cannot have more than one '|'";
					}else{
						// Move any existing expression to the "remove" expression
						$remove = $tmp."".$expression;
						$tmp = "";
						$expression = "";
					}
				}else{
					$error = "Cannot have '|' after wildcard";
				}
				break;
			default:
				// If it's not any of the above, is it a number betwen 0 and 9?
				// If so, store in a temp buffer.  Depending on what comes next
				// we may use in in an expression, or a prefix, or a removal expression
				if(preg_match("/[0-9]/i",strtoupper($p_array[$i]))){
					$tmp .= strtoupper($p_array[$i]);
				}else{
					$error = "Invalid character '".$p_array[$i]."' in pattern";
				}
		}
		$i++;
	}
	$expression .= $tmp;
	$tmp = "";
	if($error){
		// If we had any error, report them
		$match = false;
		if($debug){print $error." - position $i<br>\n";}
	}else{
		// Else try out the regular expressions we built
		if(isset($remove)){
			// If we had a removal expression, se if it works
			if(preg_match("/^".$remove."/i",$number,$matches)){
				$number = substr($number,strlen($matches[0]));
			}else{
				$match = false;
			}
		}
		// Check the expression for the rest of the number
		if(preg_match("/^".$expression."$/i",$number,$matches)){
			$new_number = $matches[0];
		}else{
			$match = false;
		}
		// If there was a prefix defined, add it.
		$new_number = $insert . "" . $new_number;
		
	}
	if(!$match){
		// If our match failed, return false
		$new_number = false;
	}
	return $new_number;

}
