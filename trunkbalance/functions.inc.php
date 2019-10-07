<?php
// PBX Open Source Software Allinace
//
//This program is free software; you can redistribute it and/or
//modify it under the terms of the GNU General Public License
//as published by the Free Software Foundation; either version 2
//of the License, or (at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.


function trunkbalance_list() {
	$allowed = array(array('trunkbalance_id' => 0, 'description' => _("None")));
	$results = sql("SELECT * FROM trunkbalance","getAll",DB_FETCHMODE_ASSOC);
	if(is_array($results)){
		foreach($results as $result){
				$allowed[] = $result;
		}
	}
	return isset($allowed)?$allowed:null;
}

function trunkbalance_listtrunk() {
	$allowed = array(array('trunkid' => 0, 'name' => _("None"), 'tech' => _("None")));
//	$sqlr = "SELECT * FROM `trunks` WHERE (name NOT LIKE 'BAL_%') AND (tech!='enum' AND tech!='dundi') ORDER BY tech, name";
	$sqlr = "SELECT * FROM `trunks` WHERE (name NOT LIKE 'BAL_%') AND (tech!='enum' AND tech!='dundi' AND tech != 'dahdi') or (tech = 'dahdi' AND channelid  REGEXP '^[0-9]*$' )  ORDER BY tech, name";
	$results = sql($sqlr,"getAll",DB_FETCHMODE_ASSOC);
	if(is_array($results)){
		foreach($results as $result){
				$allowed[] = $result;
		}
	}
	return isset($allowed)?$allowed:null;
}

function trunkbalance_listtimegroup() {
	$allowed = array(array('id' => 0, 'description' => _("None")));
	$sqlr = "SELECT * FROM `timegroups_groups`";
	$results = sql($sqlr,"getAll",DB_FETCHMODE_ASSOC);
	if(is_array($results)){
		foreach($results as $result){
				$allowed[] = $result;
		}
	}
	return isset($allowed)?$allowed:null;
}


function trunkbalance_get($id){
	$results = sql("SELECT * FROM trunkbalance WHERE trunkbalance_id = '$id'","getRow",DB_FETCHMODE_ASSOC);
	return isset($results)?$results:null;
}

function trunkbalance_trunkid($trunkname){
      	$results = sql("SELECT `trunkid` FROM `trunks` WHERE name='BAL_$trunkname'","getOne");
       return isset($results)?$results:null;
}


function trunkbalance_del($id){
	// Deleting source and its associations
	$trunkname = sql("SELECT `description`FROM `trunkbalance` WHERE trunkbalance_id='$id'","getOne");
	$trunknum = trunkbalance_trunkid($trunkname);
	$result = core_trunks_del($trunknum,''); 
	$results = sql("DELETE FROM `trunkbalance` WHERE trunkbalance_id = '$id'","query");
}

function trunkbalance_add($post){
	global $db;

	$desttrunk_id = $db->escapeSimple($post['desttrunk_id']);
	$disabled = $db->escapeSimple($post['disabled']);
	$description = $db->escapeSimple($post['description']);
	$dialpattern = $db->escapeSimple($post['dialpattern']);
	$dp_andor = $db->escapeSimple($post['dp_andor']);
	$notdialpattern = $db->escapeSimple($post['notdialpattern']);
	$notdp_andor = $db->escapeSimple($post['notdp_andor']);
	$billing_cycle = $db->escapeSimple($post['billing_cycle']);
	$billingtime = $db->escapeSimple($post['billingtime']);
	$billingday = $db->escapeSimple($post['billingday']);
	$billingdate = $db->escapeSimple($post['billingdate']);
	$billingperiod = $db->escapeSimple($post['billingperiod']);
	$endingdate = $db->escapeSimple($post['endingdate']);
	$count_inbound = $db->escapeSimple($post['count_inbound']);
	$count_unanswered = $db->escapeSimple($post['count_unanswered']);
	$loadratio = $db->escapeSimple($post['loadratio']);
	$maxtime = $db->escapeSimple($post['maxtime']);
	$maxnumber = $db->escapeSimple($post['maxnumber']);
	$maxidentical = $db->escapeSimple($post['maxidentical']);
	$timegroup_id = $db->escapeSimple($post['timegroup_id']);
	$url = $db->escapeSimple($post['url']);
	$url_timeout = $db->escapeSimple($post['url_timeout']);
	$regex = $db->escapeSimple($post['regex']);

	$results = sql("
		INSERT INTO trunkbalance
			(desttrunk_id,
			disabled,
			description,
			dialpattern,
			dp_andor,
			notdialpattern,
			notdp_andor,
			billing_cycle,
			billingtime,
			billing_day,
			billingdate,
			billingperiod,
			endingdate,
			count_inbound,
			count_unanswered,
			loadratio,
			maxtime,
			maxnumber,
			maxidentical,
			timegroup_id,
			url,
			url_timeout,
			regex)
		VALUES 
			('$desttrunk_id',
			'$disabled',
			'$description',
			'$dialpattern',
			'$dp_andor',
			'$notdialpattern',
			'$notdp_andor',
			'$billing_cycle',
			'$billingtime',
			'$billing_day',
			'$billingdate',
			'$billingperiod',
			'$endingdate',
			'$count_inbound',
			'$count_unanswered',
			'$loadratio',
			'$maxtime',
			'$maxnumber',
			'$maxidentical',
			'$timegroup_id',
			'$url',
			'$url_timeout',
			'$regex')
		");
	$result=core_trunks_add('custom','Balancedtrunk/'.$description,'','','','','notneeded','','','off','','off','BAL_'.$description,'');

}

function trunkbalance_edit($id,$post){
	global $db;

	$desttrunk_id = $db->escapeSimple($post['desttrunk_id']);
	$disabled = $db->escapeSimple($post['disabled']);
	$description = $db->escapeSimple($post['description']);
	$dialpattern = $db->escapeSimple($post['dialpattern']);
	$dp_andor = $db->escapeSimple($post['dp_andor']);
	$notdialpattern = $db->escapeSimple($post['notdialpattern']);
	$notdp_andor = $db->escapeSimple($post['notdp_andor']);	
	$billing_cycle = $db->escapeSimple($post['billing_cycle']);
	$billingtime = $db->escapeSimple($post['billingtime']);
	$billing_day = $db->escapeSimple($post['billing_day']);
	$billingdate = $db->escapeSimple($post['billingdate']);
	$billingperiod = $db->escapeSimple($post['billingperiod']);
	$endingdate = $db->escapeSimple($post['endingdate']);
	$count_inbound = $db->escapeSimple($post['count_inbound']);
	$count_unanswered = $db->escapeSimple($post['count_unanswered']);
	$loadratio = $db->escapeSimple($post['loadratio']);
	$maxtime = $db->escapeSimple($post['maxtime']);
	$maxnumber = $db->escapeSimple($post['maxnumber']);
	$maxidentical = $db->escapeSimple($post['maxidentical']);
	$timegroup_id = $db->escapeSimple($post['timegroup_id']);
	$url = $db->escapeSimple($post['url']);
	$url_timeout = $db->escapeSimple($post['url_timeout']);
	$regex = $db->escapeSimple($post['regex']);

	$olddescription=sql("SELECT `description`FROM `trunkbalance` WHERE trunkbalance_id='$id'","getOne");

	$results = sql("
		UPDATE trunkbalance 
		SET 
			desttrunk_id = '$desttrunk_id',
			disabled = '$disabled',
			description = '$description',
			dialpattern = '$dialpattern',
			dp_andor = '$dp_andor',
			notdialpattern = '$notdialpattern',
			notdp_andor = '$notdp_andor',
			billing_cycle = '$billing_cycle',
			billingtime = '$billingtime',
			billing_day = '$billing_day',
			billingdate = '$billingdate',
			billingperiod ='$billingperiod',
			endingdate = '$endingdate',
			count_inbound = '$count_inbound',
			count_unanswered = '$count_unanswered',
			loadratio = '$loadratio',
			maxtime = '$maxtime',
			maxnumber = '$maxnumber',
			maxidentical ='$maxidentical',
			timegroup_id = '$timegroup_id',
			url = '$url',
			url_timeout = '$url_timeout',
			regex = '$regex'
		WHERE trunkbalance_id = '$id'");

	if ($olddescription !== $description) {//need to update the trunk too
		$trunknum = trunkbalance_trunkid($olddescription);
  		$result=core_trunks_edit($trunknum,'Balancedtrunk/'.$description,'','','','','notneeded','','','off','','off','BAL_'.$description,'');

	}
}


function trunkbalance_hookGet_config($engine) {
	global $ext;
	switch($engine) {
		case "asterisk":
			$ext->splice('macro-dialout-trunk','s',1, new ext_agi('trunkbalance.php,${ARG1},${ARG2}'));			
			
		break;
	}
}

	
function trunkbalance_vercheck() {
	// compare version numbers of local module.xml and remote module.xml 
	// returns true if a new version is available
	$newver = false;
	$module_local = trunkbalance_xml2array("modules/trunkbalance/module.xml");
	$module_remote = trunkbalance_xml2array("https://raw.github.com/POSSA/freepbx-trunk-balancing/master/module.xml");
	if ( $foo= empty($module_local) or $bar = empty($module_remote) )
	{
		//  if either array is empty skip version check
	}
	else if ( $module_remote[module][version] > $module_local[module][version])
	{
		$newver = true;
	}
	return ($newver);
}

	//Parse XML file into an array
function trunkbalance_xml2array($url, $get_attributes = 1, $priority = 'tag')  {
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

/**
  Returns the content of a URL.
 */
function tb_get_url_contents($url, $post_data=false, $referrer=false, $cookie_file=false, $useragent=false, $curl_timeout=10) {
	$crl = curl_init();
	if (!$useragent) {
		// Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 ( .NET CLR 3.5.30729)
		$useragent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";
	}
	if ($referrer) {
		curl_setopt($crl, CURLOPT_REFERER, $referrer);
	}
	curl_setopt($crl, CURLOPT_USERAGENT, $useragent);
	curl_setopt($crl, CURLOPT_URL, $url);
	curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $curl_timeout);
	curl_setopt($crl, CURLOPT_FAILONERROR, true);
	curl_setopt($crl, CURLOPT_TIMEOUT, $curl_timeout);
	if ($cookie_file) {
		curl_setopt($crl, CURLOPT_COOKIEJAR, $cookie_file);
		curl_setopt($crl, CURLOPT_COOKIEFILE, $cookie_file);
	}
	if ($post_data) {
		curl_setopt($crl, CURLOPT_POST, 1); // set POST method
		curl_setopt($crl, CURLOPT_POSTFIELDS, $this->cisf_url_encode_array($post_data)); // add POST fields
	}
	$ret = trim(curl_exec($crl));
	if (curl_error($crl)) {
	 //   $this->DebugPrint(" " . curl_error($crl) . " ");
	}
	//if debug is turned on, return the error number if the page fails.
	if ($ret === false) {
		$ret = '';
	}
	//something in curl is causing a return of "1" if the page being called is valid, but completely empty.
	//to get rid of this, I'm doing a nasty hack of just killing results of "1".
	if ($ret == '1') {
		$ret = '';
	}
	curl_close($crl);
	// $this->DebugPrint("Orignal Raw Returned Data: </br><textarea>".$ret."</textarea></br>",DEBUG_ALL);
	return $ret;
}


