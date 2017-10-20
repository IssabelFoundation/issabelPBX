<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//  Copyright (C) 2006 WeBRainstorm S.r.l. (ask@webrainstorm.it)
//
function phonebook_list() {
	global $amp_conf;
	global $astman;

	if ($astman) {
		$list = $astman->database_show();
		foreach ($list as $k => $v) {
			if (isset($v)) { // Somehow, a 'null' value is leaking into astdb.
				if (substr($k, 1, 7) == 'cidname')
					$numbers['foo'.substr($k, 9)]['name'] = $v ;
				if (substr($k, 1, 13) == 'sysspeeddials')
					$numbers['foo'.$v]['speeddial'] = substr($k, 15) ;
			}
		}

		if (isset($numbers) && is_array($numbers)) {
			foreach ($numbers as $key => $row) {
				$names[$key]  = strtolower($row['name']);
			}
			// Array multisort renumber keys if they are numeric, (casting doesn't work), that's why I added 'foo' in front of the key
			// Quite ugly, I know... should recode it
			array_multisort($names, SORT_ASC, SORT_STRING, $numbers);
			foreach ($numbers as $key => $value) {
				$retnumbers[' '.substr($key, 3).' '] = $value;
			}
		}

		return isset($retnumbers)?$retnumbers:null;
	} else {
		fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
	}
}

function phonebook_del($number, $speeddial){
	global $amp_conf;
	global $astman;

	if ($astman) {
		$astman->database_del("cidname",trim($number));
		if ($speeddial != '')
			$astman->database_del("sysspeeddials",$speeddial);
	} else {
		fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
	}
}

function phonebook_empty(){
	global $amp_conf;
	global $astman;

	if ($astman) {
		$astman->database_deltree("cidname");
		$astman->database_deltree("sysspeeddials");
	} else {
		fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);
	}
}

function phonebook_add($number, $name, $speeddial, $gensd){
	global $amp_conf;
	global $astman;

	if(!phonebook_chk($number))
		return false;

	if ($astman) {
		// Was the user a twonk and didn't specify a speeddial?
		// Should we really automatically generate a speeddial ? definatly only when gensd is checked
		// If yes I think we should start from 99 going down and leave easier speeddials to users 

		if ($gensd == "yes"){
      if (empty($speeddial)) { 
			  for ($nbr = 99; $nbr > 0; $nbr--) { 
				  if ($astman->database_get("sysspeeddials",sprintf("%02d",$nbr))===false) {
					  $speeddial = sprintf("%02d", $nbr);
					  break;
				  }
			  }
		  }
		}
		$astman->database_put("cidname",$number, '"'.$name.'"');
		if ($speeddial != '')
			$astman->database_put("sysspeeddials",$speeddial, '"'.$number.'"');
	} else {
		fatal("Cannot connect to Asterisk Manager with ".$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"]);

	}
}


// TODO: ensures post vars is valid
function phonebook_chk($post){
	return true;
}

/*
* @version V1.01 16 June 2004 (c) Petar Nedyalkov (bu@orbitel.bg). All rights reserved.
* Released under the GPL license.
* http://bu.orbitel.bg/fgetcsvfromline.php
*/

function phonebook_fgetcsvfromline ($line, $columnCount, $delimiterChar = ';', $enclosureChar = '"') {
    $regExpSpecialChars = array (
        "|" => "\\|",
        "&" => "\\&",
        "$" => "\\$",
        "(" => "\\(",
        ")" => "\\)",
        "^" => "\\^",
        "[" => "\\[",
        "]" => "\\]",
        "{" => "\\{",
        "}" => "\\}",
        "." => "\\.",
        "*" => "\\*",
        "\\" => "\\\\",
        "/" => "\\/"
    );

    $matches = array();
    $delimiterChar = strtr($delimiterChar, $regExpSpecialChars);
    $enclosureChar = strtr($enclosureChar, $regExpSpecialChars);

    $regExp = "/^";
    for ($i = 0; $i < $columnCount; $i++) {
        $regExp .= '('.$enclosureChar.'?)(.*)\\'.(2*$i + 1).$delimiterChar; // construct the regular expression
    }
    $regExp = substr($regExp, 0, (strlen($regExp) - strlen($delimiterChar)))."/"; // format the regular expression

    if (preg_match($regExp, $line, $matches)) {
        $result = array();
        for ($i = 1; $i < count($matches)/2; $i++) {
            if (strlen($matches[2*$i]) < 1)
              $matches[2*$i] = "";
            $result[$i] = $matches[2*$i]; // get only the fields but not the delimiters
        }
        return $result;
    }
    return FALSE;
}

?>
