<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Copyright 2006 Seth Sargent, Steven Ward
//  Portions Copyright 2009, 2011 Mikael Carlsson, mickecamino@gmail.com
//	Copyright 2013 Schmooze Com Inc.
//
/* functions.inc.php - functions for BulkDIDs module. */

$modInstall = module_getinfo();

$bulkdids_lang_exists = false;
if ($modInstall['languages']['status'] == MODULE_STATUS_ENABLED && file_exists("modules/languages/functions.inc.php")) {
	include_once("modules/languages/functions.inc.php"); // for using languages functions to retrieve language setting

	if (function_exists("languages_incoming_get") && function_exists("languages_incoming_update")) {
		$bulkdids_lang_exists    = true;
	}
}

$bulkdids_cidlookup_exists = false;
if ($modInstall['cidlookup']['status'] == MODULE_STATUS_ENABLED && file_exists("modules/cidlookup/functions.inc.php")) {
	include_once("modules/cidlookup/functions.inc.php"); // for using cidlookup functions to retrieve cidlookup setting

	if (function_exists("cidlookup_did_add") && function_exists("cidlookup_did_del")) {
		$bulkdids_cidlookup_exists    = true;
	}
}

$bulkdids_fax_exists = false;
if ($modInstall['fax']['status'] == MODULE_STATUS_ENABLED && file_exists("modules/fax/functions.inc.php")) {
	include_once("modules/fax/functions.inc.php"); // for using fax functions to retreive fax settings

	if (function_exists("fax_save_incoming") && function_exists("fax_delete_incoming")) {
		$bulkdids_fax_exists = true;
	}
}

function bulkdids_exportdids_all() {
	global $db;
	global $bulkdids_lang_exists,$bulkdids_cidlookup_exists,$bulkdids_fax_exists;

	$action		= "edit";
	$fname		= "bulkdids__" .  (string) time() . $_SERVER["SERVER_NAME"] . ".csv";
	$csv_header 	= "action,DID,description,destination,cidnum,pricid,alertinfo,grppre,mohclass,ringing,delay_answer,privacyman,pmmaxretries,pmminlength,cidlookup,langcode,faxdetect,faxdetectiontype,faxdetectiontime,faxdestination\n";
	$data 		= $csv_header;
	$exts 		= bulkdids_get_all_dids();
	foreach ($exts as $ext) {

		$e 	= $ext;
		$did_info = core_did_get($e['extension'],$e['cidnum']);
		if($bulkdids_lang_exists) {
			$lang_info = languages_incoming_get($e['extension'],$e['cidnum']);
		}
		if($bulkdids_cidlookup_exists) {
			$cid_info = cidlookup_did_get($e['extension']."/".$e['cidnum']);
		}
		if($bulkdids_fax_exists) {
			$fax_info = fax_get_incoming($e['extension'],$e['cidnum']);
			$fax_detect = 'no';
			if (!empty($fax_info)) {
				$fax_detect = 'yes';
			}
		}
		$csv_line[0] 	= $action;
		$csv_line[1] 	= isset($did_info["extension"])?$did_info["extension"]:"";
		$csv_line[2] 	= isset($did_info["description"])?$did_info["description"]:"";
		$csv_line[3] 	= isset($did_info["destination"])?$did_info["destination"]:"";
		$csv_line[4] 	= isset($did_info["cidnum"])?$did_info["cidnum"]:"";
		$csv_line[5]	= isset($did_info["pricid"])?$did_info["pricid"]:"";
		$csv_line[6] 	= isset($did_info["alertinfo"])?$did_info["alertinfo"]:"";
		$csv_line[7] 	= isset($did_info["grppre"])?$did_info["grppre"]:"";
		$csv_line[8]	= isset($did_info["mohclass"])?$did_info["mohclass"]:"";
		$csv_line[9]	= isset($did_info["ringing"])?$did_info["ringing"]:"0";
		$csv_line[10]	= isset($did_info["delay_answer"])?$did_info["delay_answer"]:"";
		$csv_line[11]	= isset($did_info["privacyman"])?$did_info["privacyman"]:"";
		$csv_line[12]	= isset($did_info["pmmaxretries"])?$did_info["pmmaxretries"]:"";
		$csv_line[13]	= isset($did_info["pmminlength"])?$did_info["pmminlength"]:"";
		$csv_line[14]	= isset($cid_info)?$cid_info:"";
		$csv_line[15]	= isset($lang_info)?$lang_info:"";
		if (isset($fax_detect)) {
			$csv_line[16]	= $fax_detect;
			$csv_line[17]	= isset($fax_info['detection'])?$fax_info['detection']:"";
			$csv_line[18]	= isset($fax_info['detectionwait'])?$fax_info['detectionwait']:"";
			$csv_line[19]	= isset($fax_info['destination'])?$fax_info['destination']:"";
		}
		for ($i = 0; $i < count($csv_line); $i++) {
			/* If the string contains a comma, enclose it in double-quotes. */
			if (strpos($csv_line[$i], ",") !== FALSE) {
				$csv_line[$i] = "\"" . $csv_line[$i] . "\"";
			}
			if ($i != count($csv_line) - 1) {
				$data = $data . $csv_line[$i] . ",";
			} else {
				$data = $data . $csv_line[$i];
			}
		}
		$data = $data . "\n";
		unset($csv_line);
	}
	bulkdids_force_download($data, $fname);
	return;
}

function bulkdids_get_all_dids() {
	$sql 	= "SELECT extension,cidnum FROM incoming ORDER BY extension";
	//$extens = sql($sql,"getAll");
	$extens = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
	if (isset($extens)) {
		return $extens;
	} else {
		return null;
	}
}

function bulkdids_force_download ($data, $name, $mimetype="", $filesize=false) {
    // File size not set?
    if ($filesize == false OR !is_numeric($filesize)) {
        $filesize = strlen($data);
    }
    // Mimetype not set?
    if (empty($mimetype)) {
        $mimetype = "application/octet-stream";
    }
    // Make sure there's not anything else left
    bulkdids_ob_clean_all();
    // Start sending headers
    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false); // required for certain browsers
    header("Content-Transfer-Encoding: binary");
    header("Content-Type: " . $mimetype);
    header("Content-Length: " . $filesize);
    header("Content-Disposition: attachment; filename=\"" . $name . "\";" );
    // Send data
    echo $data;
    die();
}

function bulkdids_ob_clean_all () {
    $ob_active = ob_get_length () !== false;
    while($ob_active) {
        ob_end_clean();
        $ob_active = ob_get_length () !== false;
    }
    return true;
}

function bulkdids_generate_table_rows() {
	$fh = fopen("modules/bulkdids/table.csv", "r");
	if ($fh == NULL) {
		return NULL;
	}
	$k = 0;
	$table = array();
	while (($csv_data = fgetcsv($fh, 1000, ",", "\"")) !== FALSE) {
		$k++;
		for ($i = 0; $i < 5; $i++) {
			if (isset($csv_data[$i])) {
				$table[$k][$i] = $csv_data[$i];
			} else {
				$table[$k][$i] = "";
			}
		}
	}
	fclose($fh);
	return $table;
}
?>
