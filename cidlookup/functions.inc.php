<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//  Copyright (C) 2006 WeBRainstorm S.r.l. (ask@webrainstorm.it)

function cidlookup_hook_core($viewing_itemid, $target_menuid) {
	// TODO: add option to avoid CallerID lookup if the telco already supply a CallerID name (Overwrite checkbox ? )
	$html = '';
	if ($target_menuid == 'did')	{
		$html = '<tr><td colspan="2"><h5>';
		$html .= _("CID Lookup Source");
		$html .= '<hr></h5></td></tr>';
		$html .= '<tr>';
		$html .= '<td><a href="#" class="info">';
		$html .= _("Source").'<span>'._("Sources can be added in Caller Name Lookup Sources section").'.</span></a>:</td>';
		$html .= '<script type="text/javascript">';
		$html .= 'function openCNAMNoteDisplay(source, key) {';
		$html .= ' if (source.options[key].text === "OpenCNAM") {';
		$html .= '  document.getElementById("opencnam_hobbyist_note").style.display="";';
		$html .= ' } else {';
		$html .= '  document.getElementById("opencnam_hobbyist_note").style.display="none";';
		$html .= ' }';
		$html .= '}';
		$html .= '</script>';
		$html .= '<td><select name="cidlookup_id" onChange="javascript:openCNAMNoteDisplay(this, this.selectedIndex)">';
		$sources = cidlookup_list();
		$current = cidlookup_did_get($viewing_itemid);
		foreach ($sources as $source)
			$html .= sprintf('<option value="%d" %s>%s</option>', $source['cidlookup_id'], ($current == $source['cidlookup_id']?'selected':''), $source['description']);
		$html .= '</select></td></tr>';
		$html .= '<tr style="display:none" id="opencnam_hobbyist_note"><td colspan="2">';
		$html .= '<p style="max-width:345px;max-height:40px;font-style:italic;font-size:12px;margin-bottom:40px;"><b>NOTE:</b> OpenCNAM\'s Hobbyist Tier only allows you to do 10 cached CID lookups per hour. If you get more than 10 incoming calls per hour, or want real-time CID information (which is more accurate), you should use the Professional Tier, which is configurable via the CallerID Lookup Sources menu.</td></p>';
		$html .= '</tr>';

/*
		// Not yet fully implemented
		$html .= '<tr>';
		$html .= '<td><a href="#" class="info">';
		$html .= _("Overwrite Caller Name").'<span>'._("This option let the source specified overwrite the caller name if already supplied from telco").'.</span></a>:</td>';
		$html .= '<td><input type="checkbox" name="overwrite" value="1"></td>';
		$html .= '</tr>';
*/

	}

	return $html;

}

function cidlookup_did_add($cidlookupid, $extension, $cidnum) {
		$results = sql(sprintf('INSERT INTO cidlookup_incoming (cidlookup_id, extension, cidnum) VALUES ("%d", "%s", "%s")',
		$cidlookupid, $extension, $cidnum));
}

function cidlookup_did_del($extension, $cidnum) {
		$results = sql(sprintf("DELETE FROM cidlookup_incoming WHERE extension = '%s' AND cidnum = '%s'", $extension, $cidnum));
}

function cidlookup_hookProcess_core($viewing_itemid, $request) {

		if (!isset($request['action']))
			return;
		switch ($request['action']) {
			case 'addIncoming':
				cidlookup_did_add($request['cidlookup_id'], $request['extension'], $request['cidnum']);
				break;
			case 'delIncoming':
				$extarray = explode('/', $request['extdisplay'], 2);
				cidlookup_did_del($extarray[0], $extarray[1]);
                break;
			case 'edtIncoming':     // deleting and adding as in core module
				$extarray = explode('/', $request['extdisplay'], 2);
				cidlookup_did_del($extarray[0], $extarray[1]);
				cidlookup_did_add($request['cidlookup_id'], $request['extension'], $request['cidnum']);
				break;
		}
}

function cidlookup_hookGet_config($engine) {
	// TODO: integrating with direct extension <-> DID association
	// TODO: add option to avoid CallerID lookup if the telco already supply a CallerID name (GosubIf)
	global $ext;  // is this the best way to pass this?

	switch($engine) {
		case "asterisk":
			$pairing = cidlookup_did_list();
			if(is_array($pairing)) {
				foreach($pairing as $item) {
					if ($item['cidlookup_id'] != 0) {

						// Code from modules/core/functions.inc.php core_get_config inbound routes
						$exten = trim($item['extension']);
						$cidnum = trim($item['cidnum']);

						if ($cidnum != '' && $exten == '') {
							$exten = 's';
							$pricid = ($item['pricid']) ? true:false;
						} else if (($cidnum != '' && $exten != '') || ($cidnum == '' && $exten == '')) {
							$pricid = true;
						} else {
							$pricid = false;
						}
						$context = ($pricid) ? "ext-did-0001":"ext-did-0002";

                        if (function_exists('empty_issabelpbx')) {
                            $exten = (empty_issabelpbx($exten)?"s":$exten);
                        } else {
                            $exten = (empty($exten)?"s":$exten);
                        }
						$exten = $exten.(empty($cidnum)?"":"/".$cidnum); //if a CID num is defined, add it

						$ext->splice($context, $exten, 2, new ext_gosub('1', 'cidlookup_'.$item['cidlookup_id'], 'cidlookup'));

					}
				}
			}

		break;
	}

}

/*

// 	Generates dialplan for cidlookup
//	We call this with retrieve_conf

*/

function cidlookup_get_config($engine) {
	// TODO: discuss if mysql and http lookup should be implemented in dialplan or in an external AGI
	global $ext;  // is this the best way to pass this?
	global $asterisk_conf;
  global $version;
	switch($engine) {
		case "asterisk":
			$sources = cidlookup_list(true);
			if(is_array($sources)) {
        $ast_ge_162 = version_compare($version,'1.6.2','ge');
				foreach($sources as $item) {

					// Search for number in the cache, if found lookupcidnum and return
					if ($item['cidlookup_id'] != 0)	{
						if ($item['cache'] == 1 && $item['sourcetype'] != 'internal' && $item['sourcetype'] != 'opencnam') {
							$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_gotoif('$[${DB_EXISTS(cidname/${CALLERID(num)})} = 1]', 'cidlookup,cidlookup_return,1'));
						}
					}

					switch($item['sourcetype']) {

						case "internal":
							$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_lookupcidname(''));
						break;

						case "enum":
              $ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_setvar('CALLERID(name)', '${TXTCIDNAME(${CALLERID(num)})}'));


						break;

						case "opencnam":
							if (!empty($item['opencnam_account_sid']) && !empty($item['opencnam_auth_token'])) {
								$auth = sprintf('%s:%s@', urlencode($item['opencnam_account_sid']), urlencode($item['opencnam_auth_token']));
							} else {
								$auth = '';
							}

							$url = sprintf('https://%sapi.opencnam.com/v2/phone/${CALLERID(num)}?format=pbx&ref=issabelpbx', $auth);
							$curl = sprintf('${CURL(%s)}', $url);

							// Hardcode for now, add configuration option in future. Setting 7 =~ 1 ring
							//
							if ($ast_ge_162) {
								$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_set('CURLOPT(httptimeout)', '7'));
							}
							$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_set('CALLERID(name)', $curl));

							// If the user is using the OpenCNAM Hobbyist Tier,
							// track hourly query stats--this allows us to alert
							// the user if they go past their free usage limits
							// and need to upgrade their OpenCNAM plan.
							if (!$auth) {
								$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_set('current_hour', '${STRFTIME(,,%Y-%m-%d %H)}'));
								$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_set('last_query_hour', '${DB(cidlookup/opencnam_last_query_hour)}'));
								$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_set('total_hourly_queries', '${DB(cidlookup/opencnam_total_hourly_queries)}'));
								$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_execif('$["${last_query_hour}" != "${current_hour}"]', 'Set', 'DB(cidlookup/opencnam_total_hourly_queries)=0'));
								$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_execif('$["${total_hourly_queries}" = ""]', 'Set', 'DB(cidlookup/opencnam_total_hourly_queries)=0'));
								$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_set('DB(cidlookup/opencnam_total_hourly_queries)', '${MATH(${DB(cidlookup/opencnam_total_hourly_queries)}+1,i)}'));
								$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_execif('$[${DB(cidlookup/opencnam_total_hourly_queries)} >= 60]', 'System', '${ASTVARLIBDIR}/bin/opencnam-alert.php'));
								$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_set('DB(cidlookup/opencnam_last_query_hour)', '${current_hour}'));
							}

						break;

						case "https":
						case "http":
							if (!empty($item['http_username']) && !empty($item['http_password']))
								$auth = sprintf('%s:%s@', $item['http_username'], urlencode($item['http_password']));
							else
								$auth = '';

							if (!empty($item['http_port']))
								$host = sprintf('%s:%d', $item['http_host'], $item['http_port']);
							else
								$host = $item['http_host'].':80';

							if (substr($item['http_path'], 0, 1) == '/')
								$path = substr($item['http_path'], 1);
							else
								$path = $item['http_path'];

							$query = str_replace('[NUMBER]', '${CALLERID(num)}', $item['http_query']);
							$url = sprintf('%s://%s%s/%s?%s', $item['sourcetype'],$auth, $host, $path, $query);
							$curl = sprintf('${CURL(%s)}', $url);

              // Hardcode for now, add configuration option in future. Setting 7 =~ 1 ring
              //
              if ($ast_ge_162) {
							  $ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_set('CURLOPT(httptimeout)', '7'));
              }
							$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_set('CALLERID(name)', $curl));
						break;

						case "mysql":
			              if (version_compare($version, "1.6", "lt")) {
										  //Escaping MySQL query - thanks to http://www.asteriskgui.com/index.php?get=utilities-mysqlscape
										  $replacements = array (
										  	'\\' => '\\\\',
										  	'"' => '\\"',
										  	'\'' => '\\\'',
										  	' ' => '\\ ',
										  	',' => '\\,',
										  	'(' => '\\(',
										  	')' => '\\)',
										  	'.' => '\\.',
										  	'|' => '\\|'
										  );
			                $query = str_replace(array_keys($replacements), array_values($replacements), $item['mysql_query']);
			              } else {
			                $query = $item['mysql_query'];
			              }
							$query = str_replace('[NUMBER]', '${CALLERID(num)}', $query);
							$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_mysql_connect('connid', $item['mysql_host'],  $item['mysql_username'],  $item['mysql_password'],  $item['mysql_dbname'],  $item['mysql_charset']));
							$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_mysql_query('resultid', 'connid', $query));
							$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_mysql_fetch('fetchid', 'resultid', 'CALLERID(name)'));
							$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_mysql_clear('resultid'));
							$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_mysql_disconnect('connid'));
						break;

						// TODO: implement SugarCRM lookup, look at code snippet at http://nerdvittles.com/index.php?p=82
						case "sugarcrm":
							$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_noop('SugarCRM not yet implemented'));
							$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_return(''));
						break;
					}

					// Put numbers in the cache
					if ($item['cidlookup_id'] != 0)	{
						if ($item['cache'] == 1 && $item['sourcetype'] != 'internal') {
							$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_db_put('cidname', '${CALLERID(num)}', '${CALLERID(name)}' ));
						}
						$ext->add('cidlookup', 'cidlookup_'.$item['cidlookup_id'], '', new ext_return(''));
					}
				}

				$ext->add('cidlookup', 'cidlookup_return', '', new ext_lookupcidname(''));
				$ext->add('cidlookup', 'cidlookup_return', '', new ext_return(''));
			}
		break;
	}
}


function cidlookup_did_get($did){
	$extarray = explode('/', $did, 2);
	if(count($extarray) == 2)	{ // differentiate beetween '//' (Any did / any cid and '' empty string)
		$sql = sprintf("SELECT cidlookup_id FROM cidlookup_incoming WHERE extension = '%s' AND cidnum = '%s'", $extarray[0], $extarray[1]);
		$result = sql($sql, "getRow", DB_FETCHMODE_ASSOC);
		if(is_array($result)){
			return $result['cidlookup_id'];
		} else
			return null;
	} else { // $did is an empty string (for example when adding a new did)
		return 0;
	}
}

function cidlookup_did_list($id=false) {
	$sql = "
	SELECT cidlookup_id, a.extension extension, a.cidnum cidnum, pricid FROM cidlookup_incoming a
	INNER JOIN incoming b
	ON a.extension = b.extension AND a.cidnum = b.cidnum
	";
  if ($id !== false && ctype_digit($id)) {
    $sql .= " WHERE cidlookup_id = '$id'";
  }

	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
	return is_array($results)?$results:array();
}

function cidlookup_list($all=false) {
	$allowed = array(array('cidlookup_id' => 0, 'description' => _("None"), 'sourcetype' => null));
	$results = sql("SELECT * FROM cidlookup","getAll",DB_FETCHMODE_ASSOC);
	if(is_array($results)){
		foreach($results as $result){
			// check to see if we have a dept match for the current AMP User.
			if ($all || checkDept($result['deptname'])){
				// return this item
				$allowed[] = $result;
			}
		}
	}
	return isset($allowed)?$allowed:null;
}

function cidlookup_get($id){
	$results = sql("SELECT * FROM cidlookup WHERE cidlookup_id = '$id'","getRow",DB_FETCHMODE_ASSOC);
	return isset($results)?$results:null;
}

function cidlookup_del($id){
	// Deleting source and its associations
	$results = sql("DELETE FROM cidlookup WHERE cidlookup_id = '$id'","query");
	$results = sql("DELETE FROM cidlookup_incoming WHERE cidlookup_id = '$id'","query");
}

function cidlookup_add($post){
	global $db;

	$description = $db->escapeSimple($post['description']);
	$sourcetype = $db->escapeSimple($post['sourcetype']);
	$deptname = $db->escapeSimple($post['deptname']);
	$http_host = $db->escapeSimple($post['http_host']);
	$http_port = $db->escapeSimple($post['http_port']);
	$http_username = $db->escapeSimple($post['http_username']);
	$http_password = $db->escapeSimple($post['http_password']);
	$http_path = $db->escapeSimple($post['http_path']);
	$http_query = $db->escapeSimple($post['http_query']);
	$mysql_host = $db->escapeSimple($post['mysql_host']);
	$mysql_dbname = $db->escapeSimple($post['mysql_dbname']);
	$mysql_query = $db->escapeSimple($post['mysql_query']);
	$mysql_username = $db->escapeSimple($post['mysql_username']);
	$mysql_password = $db->escapeSimple($post['mysql_password']);
	$mysql_charset = $db->escapeSimple($post['mysql_charset']);
	$opencnam_account_sid = $db->escapeSimple($post['opencnam_account_sid']);
	$opencnam_auth_token = $db->escapeSimple($post['opencnam_auth_token']);

	$cache = isset($post['cache']) ? $db->escapeSimple($post['cache']) : 0;

	$results = sql("
		INSERT INTO cidlookup
			(description, sourcetype, cache, deptname, http_host, http_port, http_username, http_password, http_path, http_query, mysql_host, mysql_dbname, mysql_query, mysql_username, mysql_password, mysql_charset, opencnam_account_sid, opencnam_auth_token)
		VALUES
			('$description', '$sourcetype', '$cache', '$deptname', '$http_host', '$http_port', '$http_username', '$http_password', '$http_path', '$http_query', '$mysql_host', '$mysql_dbname', '$mysql_query', '$mysql_username', '$mysql_password', '$mysql_charset', '$opencnam_account_sid', '$opencnam_auth_token')
		");
}

function cidlookup_edit($id,$post){
	global $db;

	$description = $db->escapeSimple($post['description']);
	$sourcetype = $db->escapeSimple($post['sourcetype']);
	$deptname = $db->escapeSimple($post['deptname']);
	$http_host = $db->escapeSimple($post['http_host']);
	$http_port = $db->escapeSimple($post['http_port']);
	$http_username = $db->escapeSimple($post['http_username']);
	$http_password = $db->escapeSimple($post['http_password']);
	$http_path = $db->escapeSimple($post['http_path']);
	$http_query = $db->escapeSimple($post['http_query']);
	$mysql_host = $db->escapeSimple($post['mysql_host']);
	$mysql_dbname = $db->escapeSimple($post['mysql_dbname']);
	$mysql_query = $db->escapeSimple($post['mysql_query']);
	$mysql_username = $db->escapeSimple($post['mysql_username']);
	$mysql_password = $db->escapeSimple($post['mysql_password']);
	$mysql_charset = $db->escapeSimple($post['mysql_charset']);
	$opencnam_account_sid = $db->escapeSimple($post['opencnam_professional_tier']) ? $db->escapeSimple($post['opencnam_account_sid']) : '';
	$opencnam_auth_token = $db->escapeSimple($post['opencnam_professional_tier']) ? $db->escapeSimple($post['opencnam_auth_token']) : '';

  if (!isset($post['cache'])) {
   $cache = 0;
  }
  else {
   if($sourcetype != "internal" || $sourcetype != "opencnam") {
		 $cache = 1;
	 }
  }
	$results = sql("
		UPDATE cidlookup
		SET
			description = '$description',
			deptname = '$deptname',
			sourcetype = '$sourcetype' ,
			cache = '$cache',
		    http_host = '$http_host',
			http_port = '$http_port',
			http_username = '$http_username',
			http_password = '$http_password',
			http_path = '$http_path',
			http_query = '$http_query',
			mysql_host = '$mysql_host',
			mysql_dbname = '$mysql_dbname',
			mysql_query = '$mysql_query',
			mysql_username = '$mysql_username',
			mysql_password  = '$mysql_password',
			mysql_charset = '$mysql_charset',
			opencnam_account_sid = '$opencnam_account_sid',
			opencnam_auth_token = '$opencnam_auth_token'
		WHERE cidlookup_id = '$id'");
}
