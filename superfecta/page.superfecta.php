<?php

//	Superfecta code maintained by forummembers at PBXIAF.
//  Development SVN is at projects.colsolgrp.net
//	Caller ID Tricfecta / Superfecta was invented by Ward Mundy,
//  based on another authors work.
//
//	v 1.0.0 - 1.1.0 Created / coded by Tony Shiffer
//	V 2.0.0 - 2.20 Principle developer Jeremy Jacobs
//  v 2.2.1		Significant development by Patrick ELX
//
//	This program is free software; you can redistribute it and/or modify it
//	under the terms of the GNU General Public License as published by
//	the Free Software Foundation; either version 2 of the License, or
//	(at your option) any later version.
//
require_once("includes/superfecta_base.php");
$superfecta = new superfecta_base;

//Define our rootpath
define("SUPERFECTA_ROOT_PATH", dirname(__FILE__) . '/');

//Include templating engine
require_once(SUPERFECTA_ROOT_PATH . 'includes/rain.tpl.class.php');

//Define template locations
raintpl::configure("base_url", SUPERFECTA_ROOT_PATH . '/views/images/');
raintpl::configure("tpl_dir", SUPERFECTA_ROOT_PATH . 'views/');
raintpl::configure("cache_dir", SUPERFECTA_ROOT_PATH . 'views/compiled/');

//Setup templating engine
$supertpl = new RainTPL;

$scheme = (isset($_REQUEST['scheme'])) ? $_REQUEST['scheme'] : '';
$module_info = $superfecta->xml2array("modules/superfecta/module.xml");
$schemecopy = '';
$goto = NULL;

//create a copy of a scheme if requested
if ($schemecopy != "") {
    //determine the highest order amount.
    $query = "SELECT MAX(ABS(value)) FROM superfectaconfig WHERE field = 'order'";
    $results = sql($query, "getAll");
    $new_order = $results[0][0] + 1;

    //set new scheme name
    $name_good = false;
    $new_name = $schemecopy . ' copy';
    $new_name_count = 2;
    while (!$name_good) {
        $query = "SELECT * FROM superfectaconfig WHERE source = '" . $new_name . "'";
        $results = sql($query, "getAll");
        if (empty($results[0][0])) {
            $name_good = true;
        } else {
            if (substr($new_name, -4) == 'copy') {
                $new_name .= ' ' . $new_name_count;
            } else {
                $new_name = substr($new_name, 0, -2) . ' ' . $new_name_count;
            }
            $new_name_count++;
        }
    }

    //copy data from existing scheme into new scheme
    $query = "SELECT field,value FROM superfectaconfig WHERE source = '" . $schemecopy . "'";
    $results = sql($query, "getAll");
    foreach ($results as $val) {
        if (!empty($val)) {
            if ($val[0] == 'order') {
                $val[1] = $new_order;
            }
            $query = "REPLACE INTO superfectaconfig (source,field,value) VALUES('" . $new_name . "','$val[0]','$val[1]')";
            sql($query);
        }
    }

    $query = "SELECT source,field,value FROM superfectaconfig WHERE source LIKE '" . substr($schemecopy, 5) . "\\_%'";
    $results = sql($query, "getAll");
    foreach ($results as $val) {
        if (!empty($val)) {
            $new_name_source = substr($new_name, 5) . substr($val[0], strlen(substr($schemecopy, 5)));
            $query = "REPLACE INTO superfectaconfig (source,field,value) VALUES('$new_name_source','$val[1]','$val[2]')";
            sql($query);
        }
    }

    $scheme = $new_name;
}
$sql = "SELECT source, value FROM superfectaconfig WHERE source LIKE 'base_%' AND field = 'order' ORDER BY ABS(value)";
$results = sql($sql, "getAll", DB_FETCHMODE_ASSOC);

$i = 1;
$total = count($results);
foreach ($results as $data) {
    $scheme_list[$i] = $data;
    $scheme_list[$i]['name'] = substr($data['source'], 5);
    $scheme_list[$i]['showdown'] = $i == $total ? FALSE : TRUE;
    $scheme_list[$i]['showup'] = $i == 1 ? FALSE : TRUE;
    $scheme_list[$i]['showdelete'] = TRUE;
    $sql = "SELECT value FROM superfectaconfig WHERE source = '".$data['source']."' AND field = 'order'";
    $power = sql($sql, "getOne");
    $scheme_list[$i]['powered'] = $power < 0 ? FALSE : TRUE;
    $i++;
}

$supertpl->assign('schemes', $scheme_list);

$supertpl->draw('header');

if ($scheme != "") {
    $conf = superfecta_getConfig($scheme);

    $goto = (!empty($conf['spam_destination'])) ? $conf['spam_destination'] : '';

    //Get list of processors
    $processors_list = array();
    $conf['processor'] = ((!isset($conf['processor'])) OR (empty($conf['processor']))) ? 'superfecta_single.php' : $conf['processor'];
    $processors_loc = dirname(__FILE__);
    foreach (glob($processors_loc . "/includes/processors/*.php") as $filename) {
        $name = explode("_", basename($filename));
        require_once($filename);
        $class_name = basename($filename, '.php');
        $class_class = new $class_name(); //PHP < 5.3
        $processors_list[] = array(
            "name" => strtoupper($class_class->name),
            "description" => $class_class->description,
            "filename" => basename($filename),
            "selected" => ($conf['processor'] == basename($filename)) ? TRUE : FALSE,
        );
        unset($class_class);
    }

    //get a list of the files that are on this local server
    $groups_list = array();

    foreach (glob(dirname(__FILE__) . "/sources/source-*.module") as $filename) {
        if ($filename != '') {
            $source_desc = '';
            $source_param = array();

            require_once($filename);

            $this_source_name = str_replace(".module", "", str_replace(dirname(__FILE__) . "/sources/source-", "", $filename));
            $source_class = NEW $this_source_name;

            $settings = $source_class->settings();
            $groups = isset($settings['groups']) ? $settings['groups'] : NULL;

            $glist = explode(',', $groups);
            $groups_list['ALL'][] = $this_source_name;
            foreach ($glist as $data) {
                if (!empty($data)) {
                    $data = strtoupper($data);
                    $groups_list[$data][] = $this_source_name;
                }
            }
            unset($source_class);
        }
    }
    $supertpl->assign('scheme_name', substr($scheme, 5));
    $supertpl->assign('did', isset($conf['DID']) ? $conf['DID'] : '' );
    $supertpl->assign('cid_rules', isset($conf['CID_rules']) ? $conf['CID_rules'] : '');
    $supertpl->assign('curl_timeout', isset($conf['Curl_Timeout']) ? $conf['Curl_Timeout'] : '5');
    $supertpl->assign('processors_list', $processors_list);
    $supertpl->assign('multifecta_timeout', isset($conf['multifecta_timeout']) ? $conf['multifecta_timeout'] : '1.5');
    $supertpl->assign('prefix_url', isset($conf['Prefix_URL']) ? $conf['Prefix_URL'] : '');
    $supertpl->assign('spam_text', isset($conf['SPAM_Text']) ? $conf['SPAM_Text'] : '');
    $supertpl->assign('spam_text_substitute', isset($conf['SPAM_Text_Substitute']) && ($conf['SPAM_Text_Substitute'] == 'Y') ? TRUE : FALSE);
    $supertpl->assign('spam_int', !empty($conf['spam_interceptor']) && ($conf['spam_interceptor'] == 'Y') ? TRUE : FALSE);
    $supertpl->assign('spam_threshold', $conf['SPAM_threshold']);
    $supertpl->assign('interceptor_select', drawselects($goto, 0, FALSE, FALSE));
    include('sources.php');
}
$supertpl->assign('module_vers', $module_info['module']['version']);
$supertpl->draw('footer');