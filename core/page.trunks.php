<?php /* $Id: page.trunks.php 1145 2006-03-14 19:53:20Z mheydon1973 $ */
// routing.php Copyright (C) 2004 Greg MacLellan (greg@mtechsolutions.ca)
// Asterisk Management Portal Copyright (C) 2004 Coalescent Systems Inc. (info@coalescentsystems.ca)
// Copyright 2006-2014 Schmooze Com Inc.
// Copyright 2020 Nicolas Gudino (nicolas@issabel.com)

if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$display='trunks'; 
$extdisplay=isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:'';
$trunknum = ltrim($extdisplay,'OUT_');

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
// Now check if the Copy Trunks submit button was pressed, in which case we duplicate the trunk
//
if (isset($_REQUEST['copytrunk'])) {
  $action = 'copytrunk';
}

$codecs = array(
    'ulaw'     => '',
    'alaw'     => '',
    'gsm'      => '',
    'g726'     => '',
    'g722'     => '',
    'slin'     => '',
    'g729'     => '',
    'ilbc'     => '',
    'g723'     => '',
    'g726aal2' => '',
    'adpcm'    => '',
    'lpc10'    => '',
    'speex'    => '',
    'siren7'   => '',
    'siren14'  => '',
    'speex16'  => '',
    'slin16'   => '',
    'g719'     => '',
    'speex32'  => '',
    'slin12'   => '',
    'slin24'   => '',
    'slin32'   => '',
    'slin44'   => '',
    'slin48'   => '',
    'slin96'   => '',
    'slin192'  => '',
    'opus'     => '',
    'silk8'    => '',
    'silk12'   => '',
    'silk16'   => '',
    'silk24'   => '',
);

$tech         = strtolower(isset($_REQUEST['tech'])?htmlentities($_REQUEST['tech']):'');
$outcid       = isset($_REQUEST['outcid'])?$_REQUEST['outcid']:'';
$maxchans     = isset($_REQUEST['maxchans'])?$_REQUEST['maxchans']:'';
$dialoutprefix= isset($_REQUEST['dialoutprefix'])?$_REQUEST['dialoutprefix']:'';
$channelid    = isset($_REQUEST['channelid'])?$_REQUEST['channelid']:'';
$peerdetails  = isset($_REQUEST['peerdetails'])?$_REQUEST['peerdetails']:'';
$usercontext  = isset($_REQUEST['usercontext'])?$_REQUEST['usercontext']:'';
$userconfig   = isset($_REQUEST['userconfig'])?$_REQUEST['userconfig']:'';
$register     = isset($_REQUEST['register'])?$_REQUEST['register']:'';
$keepcid      = isset($_REQUEST['keepcid'])?$_REQUEST['keepcid']:'off';
$disabletrunk = isset($_REQUEST['disabletrunk'])?$_REQUEST['disabletrunk']:'off';
$continue     = isset($_REQUEST['continue'])?$_REQUEST['continue']:'off';
$provider     = isset($_REQUEST['provider'])?$_REQUEST['provider']:'';
$trunk_name   = isset($_REQUEST['trunk_name'])?$_REQUEST['trunk_name']:'';

$pjsip_context                  = isset($_REQUEST['pjsip_context'])?$_REQUEST['pjsip_context']:'from-pstn';
$pjsip_inband_progress          = isset($_REQUEST['pjsip_inband_progress'])?$_REQUEST['pjsip_inband_progress']:'no';
$pjsip_auth_rejection_permanent = isset($_REQUEST['pjsip_auth_rejection_permanent'])?$_REQUEST['pjsip_auth_rejection_permanent']:'no';
$pjsip_direct_media             = isset($_REQUEST['pjsip_direct_media'])?$_REQUEST['pjsip_direct_media']:'no';
$pjsip_qualify_frequency        = isset($_REQUEST['pjsip_qualify_frequency'])?$_REQUEST['pjsip_qualify_frequency']:'60';
$pjsip_max_retries              = isset($_REQUEST['pjsip_max_retries'])?$_REQUEST['pjsip_max_retries']:'10';
$pjsip_retry_interval           = isset($_REQUEST['pjsip_retry_interval'])?$_REQUEST['pjsip_retry_interval']:'60';
$pjsip_expiration               = isset($_REQUEST['pjsip_expiration'])?$_REQUEST['pjsip_expiration']:'3600';
$pjsip_transport                = isset($_REQUEST['pjsip_transport'])?$_REQUEST['pjsip_transport']:'transport-udp';
$pjsip_rtp_symmetric            = isset($_REQUEST['pjsip_rtp_symmetric'])?$_REQUEST['pjsip_rtp_symmetric']:'yes';
$pjsip_rewrite_contact          = isset($_REQUEST['pjsip_rewrite_contact'])?$_REQUEST['pjsip_rewrite_contact']:'yes';
$pjsip_dtmf_mode                = isset($_REQUEST['pjsip_dtmf_mode'])?$_REQUEST['pjsip_dtmf_mode']:'auto';
$pjsip_trust_id_inbound         = isset($_REQUEST['pjsip_trust_id_inbound'])?$_REQUEST['pjsip_trust_id_inbound']:'no';
$pjsip_fax_detect               = isset($_REQUEST['pjsip_fax_detect'])?$_REQUEST['pjsip_fax_detect']:'no';
$pjsip_t38_udptl                = isset($_REQUEST['pjsip_t38_udptl'])?$_REQUEST['pjsip_t38_udptl']:'no';
$pjsip_t38_udptl_nat            = isset($_REQUEST['pjsip_t38_udptl_nat'])?$_REQUEST['pjsip_t38_udptl_nat']:'no';
$pjsip_t38_udptl_ec             = isset($_REQUEST['pjsip_t38_udptl_ec'])?$_REQUEST['pjsip_t38_udptl_ec']:'none';
$pjsip_support_path             = isset($_REQUEST['pjsip_support_path'])?$_REQUEST['pjsip_support_path']:'no';

$post_codec = isset($_REQUEST['codec']) ? $_REQUEST['codec'] : array(); 

if(count($post_codec)>0) {
    $pri = 1;
    foreach (array_keys($post_codec) as $codec) {
        $codecs[$codec] = $pri++;
    }
    asort($codecs);
    $sel_codec=array();
    foreach($codecs as $key=>$val) {
        if($val<>''){
            $sel_codec[$val]=$key;
       }
    }
    $pjsip_codecs=implode(",",$sel_codec);
}

if(isset($_REQUEST['pjsip_registration'])) { $register=$_REQUEST['pjsip_registration']; }

$failtrunk    = isset($_REQUEST['failtrunk'])?$_REQUEST['failtrunk']:'';
$failtrunk_enable = ($failtrunk == "")?'':'CHECKED';

$dialopts     = isset($_REQUEST['dialopts'])?$_REQUEST['dialopts']:false;

$pjsipconfig='';
foreach($_REQUEST as $key=>$val) {
    if(substr($key,0,5)=='pjsip') {
        $newkey = substr($key,6);
        $pjsipconfig .="$newkey=$val\n";
    }
}

if($pjsipconfig<>'') {
    $pjsipconfig.="codecs=$pjsip_codecs\n";
    $userconfig=$pjsipconfig;
}

// Check if they uploaded a CSV file for their route patterns
//
if (isset($_FILES['pattern_file']) && $_FILES['pattern_file']['tmp_name'] != '') {
    $fh = fopen($_FILES['pattern_file']['tmp_name'], 'r');
    if ($fh !== false) {
        $csv_file = array();
        $index = array();

        // Check first row, ingoring empty rows and get indices setup
        //
        while (($row = fgetcsv($fh, 5000, ",", "\"")) !== false) {
            if (count($row) == 1 && $row[0] == '') {
                continue;
            } else {
                $count = count($row) > 3 ? 3 : count($row);
                for ($i=0;$i<$count;$i++) {
                    switch (strtolower($row[$i])) {
                    case 'prepend':
                    case 'prefix':
                    case 'match pattern':
                        $index[strtolower($row[$i])] = $i;
                        break;
                    default:
                        break;
                    }
                }
                // If no headers then assume standard order
                if (count($index) == 0) {
                    $index['prepend'] = 0;
                    $index['prefix'] = 1;
                    $index['match pattern'] = 2;
                    if ($count == 3) {
                        $csv_file[] = $row;
                    }
                }
                break;
            }
        }
        $row_count = count($index);
        while (($row = fgetcsv($fh, 5000, ",", "\"")) !== false) {
            if (count($row) == $row_count) {
                $csv_file[] = $row;
            }
        }
    }
}

//
// Use a hash of the value inserted to get rid of duplicates
$dialpattern_insert = array();
$p_idx = 0;
$n_idx = 0;

// If we have a CSV file it replaces any existing patterns
//
if (!empty($csv_file)) {
    foreach ($csv_file as $row) {
        $this_prepend = isset($index['prepend']) ? htmlspecialchars(trim($row[$index['prepend']])) : '';
        $this_prefix = isset($index['prefix']) ? htmlspecialchars(trim($row[$index['prefix']])) : '';
        $this_match_pattern = isset($index['match pattern']) ? htmlspecialchars(trim($row[$index['match pattern']])) : '';

        if ($this_prepend != '' || $this_prefix  != '' || $this_match_pattern != '') {
            $dialpattern_insert[] = array(
                'prepend_digits' => $this_prepend,
                'match_pattern_prefix' => $this_prefix,
                'match_pattern_pass' => $this_match_pattern,
            );
        }
    }
} else if (isset($_POST["prepend_digit"])) {
    $prepend_digit = $_POST["prepend_digit"];
    $pattern_prefix = $_POST["pattern_prefix"];
    $pattern_pass = $_POST["pattern_pass"];

    foreach (array_keys($prepend_digit) as $key) {
        if ($prepend_digit[$key]!='' || $pattern_prefix[$key]!='' || $pattern_pass[$key]!='') {

            $dialpattern_insert[] = array(
                'prepend_digits' => htmlspecialchars(trim($prepend_digit[$key])),
                'match_pattern_prefix' => htmlspecialchars(trim($pattern_prefix[$key])),
                'match_pattern_pass' => htmlspecialchars(trim($pattern_pass[$key])),
            );
        }
    }
}

// TODO: remember old name, if new one is different the don't rename
//
//if submitting form, update database
switch ($action) {
case "copytrunk":

    $sv_channelid    = isset($_REQUEST['sv_channelid'])?$_REQUEST['sv_channelid']:'';
    $sv_trunk_name    = isset($_REQUEST['sv_trunk_name'])?$_REQUEST['sv_trunk_name']:'';
    $sv_usercontext    = isset($_REQUEST['sv_usercontext'])?$_REQUEST['sv_usercontext']:'';

    if ($trunk_name == $sv_trunk_name) {
        $trunk_name .= ($trunk_name == '' ? '' : '_') . "copy_$trunknum";
    }
    if ($channelid == $sv_channelid) {
        $channelid .= '_copy_' . $trunknum;
    }
    if ($usercontext != '' && $usercontext == $sv_usercontext) {
        $usercontext .= '_copy_' . $trunknum;
    }
    $disabletrunk = 'on';
    $continue = 'on';
    $trunknum = '';
    $extdisplay='';
    // Fallthrough to addtrunk now...
    //
case "addtrunk":
    $trunknum = core_trunks_add($tech, $channelid, $dialoutprefix, $maxchans, $outcid, $peerdetails, $usercontext, $userconfig, $register, $keepcid, trim($failtrunk), $disabletrunk, $trunk_name, $provider, $continue, $dialopts);

    core_trunks_update_dialrules($trunknum, $dialpattern_insert);
    needreload();
    redirect_standard();
    break;
case "edittrunk":
    core_trunks_edit($trunknum, $channelid, $dialoutprefix, $maxchans, $outcid, $peerdetails, $usercontext, $userconfig, $register, $keepcid, trim($failtrunk), $disabletrunk, $trunk_name, $provider, $continue, $dialopts);

    // this can rewrite too, so edit is the same
    core_trunks_update_dialrules($trunknum, $dialpattern_insert, true);
    needreload();
    redirect_standard('extdisplay');
    break;
case "deltrunk":
    core_trunks_del($trunknum);
    core_trunks_delete_dialrules($trunknum);
    core_routing_trunk_delbyid($trunknum);
    needreload();
    redirect_standard();
    break;
case "populatenpanxx7": 
case "populatenpanxx10": 
    $dialpattern_array = $dialpattern_insert;
    if (preg_match("/^([2-9]\d\d)-?([2-9]\d\d)$/", $_REQUEST["npanxx"], $matches)) {
        // first thing we do is grab the exch:
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, "http://www.localcallingguide.com/xmllocalprefix.php?npa=".$matches[1]."&nxx=".$matches[2]);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; IssabelPBX Local Trunks Configuration)");
        $str = curl_exec($ch);
        curl_close($ch);

        // quick 'n dirty - nabbed from PEAR
        require_once($amp_conf['AMPWEBROOT'] . '/admin/modules/core/XML_Parser.php');
        require_once($amp_conf['AMPWEBROOT'] . '/admin/modules/core/XML_Unserializer.php');

        $xml = new xml_unserializer;
        $xml->unserialize($str);
        $xmldata = $xml->getUnserializedData();

        if (isset($xmldata['lca-data']['prefix'])) {
            $hash_filter = array(); //avoid duplicates
            if ($action == 'populatenpanxx10') {
                // 10 digit dialing
                // - add area code to 7 digits
                // - match local 10 digits
                // - add 1 to anything else
                $dialpattern_array[] = array(
                    'prepend_digits' => '',
                    'match_pattern_prefix' => '',
                    'match_pattern_pass' => htmlspecialchars($matches[1].'NXXXXXX'),
                );
                // add NPA to 7-digits
                foreach ($xmldata['lca-data']['prefix'] as $prefix) {
                    if (isset($hash_filter[$prefix['npa'].'+'.$prefix['nxx']])) {
                        continue;
                    } else {
                        $hash_filter[$prefix['npa'].'+'.$prefix['nxx']] = true;
                    }
                    $dialpattern_array[] = array(
                        'prepend_digits' =>  htmlspecialchars($prefix['npa']),
                        'match_pattern_prefix' => '',
                        'match_pattern_pass' => htmlspecialchars($prefix['nxx'].'XXXX'),
                    );
                }
                foreach ($xmldata['lca-data']['prefix'] as $prefix) {
                    if (isset($hash_filter[$prefix['npa'].$prefix['nxx']])) {
                        continue;
                    } else {
                        $hash_filter[$prefix['npa'].$prefix['nxx']] = true;
                    }
                    $dialpattern_array[] = array(
                        'prepend_digits' =>  '',
                        'match_pattern_prefix' => '',
                        'match_pattern_pass' => htmlspecialchars($prefix['npa'].$prefix['nxx'].'XXXX'),
                    );
                }
                // if a number was not matched as local, dial it with '1' prefix
                $dialpattern_array[] = array(
                    'prepend_digits' =>  '',
                    'match_pattern_prefix' => '',
                    'match_pattern_pass' => '1+NXXNXXXXXX',
                );
            } else {
                // 7 digit dialing
                // - drop area code from local numbers
                // - match local 7 digit numbers
                // - add 1 to everything else
                foreach ($xmldata['lca-data']['prefix'] as $prefix) {
                    if (isset($hash_filter[$prefix['npa'].'|'.$prefix['nxx']])) {
                        continue;
                    } else {
                        $hash_filter[$prefix['npa'].'|'.$prefix['nxx']] = true;
                    }
                    $dialpattern_array[] = array(
                        'prepend_digits' =>  '',
                        'match_pattern_prefix' => htmlspecialchars( $prefix['npa']),
                        'match_pattern_pass' => htmlspecialchars($prefix['nxx'].'XXXX'),
                    );
                }
                foreach ($xmldata['lca-data']['prefix'] as $prefix) {
                    if (isset($hash_filter[$prefix['nxx']])) {
                        continue;
                    } else {
                        $hash_filter[$prefix['nxx']] = true;
                    }
                    $dialpattern_array[] = array(
                        'prepend_digits' =>  '',
                        'match_pattern_prefix' => '',
                        'match_pattern_pass' => htmlspecialchars($prefix['nxx'].'XXXX'),
                    );
                }
                $dialpattern_array[] = array(
                    'prepend_digits' =>  '1',
                    'match_pattern_prefix' => '',
                    'match_pattern_pass' => 'NXXNXXXXXX',
                );
                $dialpattern_array[] = array(
                    'prepend_digits' => htmlspecialchars('1'.$matches[1]),
                    'match_pattern_prefix' => '',
                    'match_pattern_pass' => 'NXXXXXX',
                );
            }

            // check for duplicates, and re-sequence
            unset($hash_filter);
        } else {
            $errormsg = _("Error fetching prefix list for: "). $_REQUEST["npanxx"];
        }

    } else {
        // what a horrible error message... :p
        $errormsg = _("Invalid format for NPA-NXX code (must be format: NXXNXX)");
    }

    if (isset($errormsg)) {
        echo "<script language=\"javascript\">alert('".addslashes($errormsg)."');</script>";
        unset($errormsg);
    }
    break;
}

?>

<div class="rnav">
<ul>
    <li><a <?php  echo ($extdisplay=='' ? 'class="current"':'') ?> href="config.php?display=<?php echo urlencode($display)?>"><?php echo _("Add Trunk")?></a></li>
<?php 
//get existing trunk info
$tresults = core_trunks_getDetails();

foreach ($tresults as $tresult) {
    $background = ($tresult['disabled'] == 'on')?'#DDD':'';
    switch ($tresult['tech']) {
    case 'enum':
        $label = substr($tresult['name'],0,15)." ENUM";
        break;
    case 'dundi':
        $label = substr($tresult['name'],0,15)." (DUNDi)";
        break;
    case 'iax2':
        $tresult['tech'] = 'iax';
    case 'zap':
    case 'dahdi':
        $label = substr($tresult['name'],0,15);
        if (trim($label) == '') {
            $label = sprintf(_('Channel %s'),substr($tresult['channelid'],0,15));
        }
        $label .= " (".$tresult['tech'].")";
        break;
    case 'sip':
    case 'iax':
    case 'custom':
    default:
        $label = substr($tresult['name'],0,15);
        if (trim($label) == '') {
            $label = substr($tresult['channelid'],0,15);
        }
        $label .= " (".$tresult['tech'].")";
        break;
    }
    echo "\t<li><a ".($trunknum==$tresult['trunkid'] ? 'class="current"':'')." href=\"config.php?display=".urlencode($display)."&amp;extdisplay=OUT_".urlencode($tresult['trunkid'])."\" title=\"".urlencode($tresult['name'])."\" style=\"background: $background;\" >".$label."</a></li>\n";
}

?>
</ul>
</div>

<?php 
if (!$tech && !$extdisplay) {
?>
    <h2><?php echo _("Add a Trunk")?></h2>
<?php
    $baseURL   = $_SERVER['PHP_SELF'].'?display='.urlencode($display).'&';
    $trunks[] = array('url'=> $baseURL.'tech=SIP', 'tlabel' =>  _("Add SIP Trunk"));
    $trunks[] = array('url'=> $baseURL.'tech=PJSIP', 'tlabel' =>  _("Add PJSIP Trunk"));
    $trunks[] = array('url'=> $baseURL.'tech=DAHDI', 'tlabel' =>  _("Add DAHDi Trunk"));
    $trunks[] = array('url'=> $baseURL.'tech=IAX2', 'tlabel' =>  _("Add IAX2 Trunk"));
    //--------------------------------------------------------------------------------------
    // Added to enable the unsupported misdn module
    if (function_exists('misdn_ports_list_trunks') && count(misdn_ports_list_trunks())) {
        $trunks[] = array('url'=> $baseURL.'tech=MISDN', 'tlabel' =>  _("Add mISDN Trunk"));
  }
    //--------------------------------------------------------------------------------------
    $trunks[] = array('url'=> $baseURL.'tech=ENUM', 'tlabel' =>  _("Add ENUM Trunk"));
    $trunks[] = array('url'=> $baseURL.'tech=DUNDI', 'tlabel' =>  _("Add DUNDi Trunk"));
    $trunks[] = array('url'=> $baseURL.'tech=CUSTOM', 'tlabel' =>  _("Add Custom Trunk"));
    foreach ($trunks as $trunk) {
        $label = '<span><img width="16" height="16" border="0" title="'.$trunk['tlabel'].'" alt="" src="images/core_add.png"/>&nbsp;'.$trunk['tlabel'].'</span>';
        echo "<a href=".$trunk['url'].">".$label."</a><br /><br />";
    }
} else {
    if ($extdisplay) {

        $trunk_details = core_trunks_getDetails($trunknum);

        $tech             = htmlentities($trunk_details['tech']);
        $outcid           = htmlentities($trunk_details['outcid']);
        $maxchans         = htmlentities($trunk_details['maxchans']);
        $dialoutprefix    = htmlentities($trunk_details['dialoutprefix']);
        $keepcid          = htmlentities($trunk_details['keepcid']);
        $failtrunk        = htmlentities($trunk_details['failscript']);
        $failtrunk_enable = ($failtrunk == "")?'':'CHECKED';
        $disabletrunk     = htmlentities($trunk_details['disabled']);
        $continue         = htmlentities($trunk_details['continue']);
        $provider         = $trunk_details['provider'];
        $trunk_name       = htmlentities($trunk_details['name']);
        $dialopts         = $trunk_details['dialopts'] === false ? false : htmlentities($trunk_details['dialopts']);

        if ($tech!="enum") {
    
            $channelid = htmlentities($trunk_details['channelid']);

            if ($tech!="custom" && $tech!="dundi" && $tech!="pjsip") {  // custom trunks will not have user/peer details in database table
                // load from db
                if (empty($peerdetails)) {    
                    $peerdetails = core_trunks_getTrunkPeerDetails($trunknum);
                }
                if (empty($usercontext)) {    
                    $usercontext = htmlentities($trunk_details['usercontext']);
                }
    
                if (empty($userconfig)) {    
                    $userconfig = core_trunks_getTrunkUserConfig($trunknum);
                }
                    
                if (empty($register)) {    
                    $register = core_trunks_getTrunkRegister($trunknum);
                }
            } else if ($tech=="pjsip") {
                if (empty($register)) {    
                    $register = core_trunks_getTrunkRegister($trunknum);
                    $userconfig = core_trunks_getTrunkUserConfig($trunknum);
                                        // ok, this is ugly, but we need this done fast, not pretty
                    $lineas = explode("\n",$userconfig);
                                        foreach($lineas as $linea) {
                                            $partes = preg_split("/=/",$linea);
                                            if($partes[0]=='codecs') {
                                                $storedcodecs=explode(",",$partes[1]);
                                                $pri=1;
                                                foreach($storedcodecs as $codecname) {
                                                    $codecs[$codecname]=$pri++; 
                                                }
                                            } else {
                                                $varname = "pjsip_".$partes[0];
                                                $$varname=$partes[1];
                                            }
                    }
                }
            }
        }
    if (count($dialpattern_array) == 0) {
      $dialpattern_array = core_trunks_get_dialrules($trunknum);
    }
        $upper_tech = strtoupper($tech);
    if (trim($trunk_name) == '') {
          $trunk_name = ($upper_tech == 'ZAP'|$upper_tech == 'DAHDI'?sprintf(_('%s Channel %s'),$upper_tech,$channelid):$channelid);
    }
        echo "<h2>".sprintf(_("Edit %s Trunk"),$upper_tech).($upper_tech == 'ZAP' && ast_with_dahdi()?" ("._("DAHDi compatibility Mode").")":"")."</h2>";
        $tlabel = sprintf(_("Delete Trunk %s"),substr($trunk_name,0,20));
        $label = '<span><img width="16" height="16" border="0" title="'.$tlabel.'" alt="" src="images/core_delete.png"/>&nbsp;'.$tlabel.'</span>';
?>
        <p><a href="config.php?display=<?php echo urlencode($display) ?>&extdisplay=<?php echo urlencode($extdisplay) ?>&action=deltrunk"><?php echo $label ?></a></p>
<?php 

        // find which routes use this trunk
        $routes = core_trunks_gettrunkroutes($trunknum);
        $num_routes = count($routes);
        if ($num_routes > 0) {
            echo "<a href=# class=\"info\">&nbsp;"._("In use by")." ".$num_routes." ".($num_routes == 1 ? _("route") : _("routes"))."<span>";
            foreach($routes as $route=>$priority) {
                echo _("Route")." <b>".$route."</b>: "._("Sequence")." <b>".$priority."</b><br>";
            }
            echo "</span></a>";
        } else {
            echo "&nbsp;<b>"._("WARNING:")."</b> <a href=# class=\"info\">"._("This trunk is not used by any routes!")."<span>";
            echo _("This trunk will not be able to be used for outbound calls until a route is setup that uses it. Click on <b>Outbound Routes</b> to setup routing.");
            echo "</span></a>";
        }
        $usage_list = framework_display_destination_usage(core_getdest(ltrim($extdisplay,'OUT_')));
        if (!empty($usage_list)) {
        ?>
            <a href="#" class="info"><?php echo $usage_list['text']?><span><?php echo $usage_list['tooltip']?></span></a>
        <?php
        }


    } else {
        // set defaults
        $outcid = "";
        $maxchans = "";
        $dialoutprefix = "";
        
        if ($tech == 'zap' || $tech == 'dahdi') {
            $channelid = 'g0';
        } else {
            $channelid = '';
        }
        
        // only for iax2/sip
        $peerdetails = "host=***provider ip address***\nusername=***userid***\nsecret=***password***\ntype=peer";
        $usercontext = "";
        $userconfig = "secret=***password***\ntype=user\ncontext=from-trunk";
        $register = "";
        
        $localpattern = "NXXXXXX";
        $lddialprefix = "1";
        $areacode = "";
    
        $upper_tech = strtoupper($tech);
        echo "<h2>".sprintf(_("Add %s Trunk"),$upper_tech).($upper_tech == 'ZAP' && ast_with_dahdi()?" ("._("DAHDi compatibility mode").")":"")."</h2>";
    } 
  if (!isset($dialpattern_array)) {
    $dialpattern_array = array();
  }
        
switch ($tech) {
    case 'dundi':
        $helptext = _('IssabelPBX offers limited support for DUNDi trunks and additional manual configuration is required. The trunk name should correspond to the [mappings] section of the remote dundi.conf systems. For example, you may have a mapping on the remote system, and corresponding configurations in dundi.conf locally, that looks as follows:<br /><br />[mappings]<br />priv => dundi-extens,0,IAX2,priv:${SECRET}@218.23.42.26/${NUMBER},nopartial<br /><br />In this example, you would create this trunk and name it priv. You would then create the corresponding IAX2 trunk with proper settings to work with DUNDi. This can be done by making an IAX2 trunk in IssabelPBX or by using the iax_custom.conf file.<br />The dundi-extens context in this example must be created in extensions_custom.conf. This can simply include contexts such as ext-local, ext-intercom-users, ext-paging and so forth to provide access to the corresponding extensions and features provided by these various contexts and generated by IssabelPBX.');
        break;
    case 'sip':
        break;
    default:
        $helptext = '';
}
if ($helptext != '') {
    if ($extdisplay) {
        echo "<br /><br />";
    }
    echo $helptext;
}

?>
    
        <form enctype="multipart/form-data" name="trunkEdit" action="config.php" method="post" onsubmit="return trunkEdit_onsubmit('<?php echo ($extdisplay ? "edittrunk" : "addtrunk") ?>');">
            <input type="hidden" name="display" value="<?php echo $display?>"/>
            <input type="hidden" name="extdisplay" value="<?php echo $extdisplay ?>"/>
            <input type="hidden" name="action" value=""/>
            <input type="hidden" name="tech" value="<?php echo $tech?>"/>
            <input type="hidden" name="provider" value="<?php echo $provider?>"/>
            <input type="hidden" name="sv_trunk_name" value="<?php echo $trunkname?>"/>
            <input type="hidden" name="sv_usercontext" value="<?php echo $usercontext?>"/>
            <input type="hidden" name="sv_channelid" value="<?php echo $channelid?>"/>
            <input id="npanxx" name="npanxx" type="hidden" />
            <table>
            <tr>
                <td colspan="2">
                    <h5><?php echo _("General Settings")?></h5>
                </td>
            </tr>
            <tr>
                <td>
                    <a href=# class="info"><?php echo _("Trunk Name")?><span><?php echo _("Descriptive Name for this Trunk")?></span></a>
                </td><td>
                    <input type="text" class="w100" name="trunk_name" value="<?php echo $trunk_name;?>" tabindex="<?php echo ++$tabindex;?>"/>
                </td>
            </tr>
            <tr>
                <td>
                    <a href=# class="info"><?php echo _("Outbound CallerID")?><span><?php echo _("CallerID for calls placed out on this trunk<br><br>Format: <b>&lt;#######&gt;</b>. You can also use the format: \"hidden\" <b>&lt;#######&gt;</b> to hide the CallerID sent out over Digital lines if supported (E1/T1/J1/BRI/SIP/IAX).")?></span></a>
                </td><td>
                    <input type="text" class="w100" name="outcid" value="<?php echo $outcid;?>" tabindex="<?php echo ++$tabindex;?>"/>
                </td>
            </tr>
            <tr>

        <tr>
                <td>
                    <a href="#" class="info"><?php echo _("CID Options")?><span><?php echo _("Determines what CIDs will be allowed out this trunk. IMPORTANT: EMERGENCY CIDs defined on an extension/device will ALWAYS be used if this trunk is part of an EMERGENCY Route regardless of these settings.<br />Allow Any CID: all CIDs including foreign CIDS from forwarded external calls will be transmitted.<br />Block Foreign CIDs: blocks any CID that is the result of a forwarded call from off the system. CIDs defined for extensions/users are transmitted.<br />Remove CNAM: this will remove CNAM from any CID sent out this trunk<br />Force Trunk CID: Always use the CID defined for this trunk except if part of any EMERGENCY Route with an EMERGENCY CID defined for the extension/device.") . _("Intra-Company Routes will always trasmit an extension's internal number and name.");?></span></a>
                </td><td>

                <select name="keepcid" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
                <?php
                    $default = (isset($keepcid) ? $keepcid : 'off');
                    echo '<option value="off"' . ($default == 'off'  ? ' SELECTED' : '').'>'._("Allow Any CID")."\n";
                    echo '<option value="on"'  . ($default == 'on'   ? ' SELECTED' : '').'>'._("Block Foreign CIDs")."\n";
                    echo '<option value="cnum"'. ($default == 'cnum' ? ' SELECTED' : '').'>'._("Remove CNAM")."\n";
                    echo '<option value="all"' . ($default == 'all'  ? ' SELECTED' : '').'>'._("Force Trunk CID")."\n";
                ?>
                </select>
                </td>
      </tr>

            <tr>
                <td>
<?php
    if ($tech == "sip" || substr($tech,0,3) == "iax") {
        $pr_tech = ($tech == "iax") ? "iax2":$tech;
?>
                    <a href=# class="info"><?php echo _("Maximum Channels")?><span><?php echo sprintf(_("Controls the maximum number of outbound channels (simultaneous calls) that can be used on this trunk. To count inbound calls against this maximum, use the auto-generated context: %s as the inbound trunk's context. (see extensions_additional.conf) Leave blank to specify no maximum."),((isset($channelid) && trim($channelid)!="")?"from-trunk-$pr_tech-$channelid":"from-trunk-[trunkname]"))?></span></a>
<?php
    } else {
?>
                    <a href=# class="info"><?php echo _("Maximum Channels")?><span><?php echo _("Controls the maximum number of outbound channels (simultaneous calls) that can be used on this trunk. Inbound calls are not counted against the maximum. Leave blank to specify no maximum.")?></span></a>
<?php
    }
?>
                </td><td>
                    <input type="text" size="3" name="maxchans" value="<?php echo htmlspecialchars($maxchans); ?>" tabindex="<?php echo ++$tabindex;?>"/>
                </td>
            </tr>

<?php
    $data['name'] = $data['id'] = 'dialopts';
    if ($dialopts !== false) {
        $data['value'] = $dialopts;
    } else {
        $data['disabled'] = true;
    }
    $data['size'] = '20';
    $data['tabindex'] = $tabindex++;

    $dialopts_label = ipbx_label(_('Asterisk Trunk Dial Options'), _('Asterisk Dial command options to be used when calling out this trunk. To override the Advanced Settings default, check the box and then provide the required options for this trunk')) . "\n";
    $dialopts_box = ipbx_form_input_check($data, '', '', '<small>' . _('Override') . '</small>', $amp_conf['TRUNK_OPTIONS'], true) . "\n";
?>
            <tr>
                <td>
                    <?php echo $dialopts_label; ?>
                </td><td>
                    <?php echo $dialopts_box; ?>
                </td>
            </tr>

            <tr>
                <td><a class="info" href="#"><?php echo _("Continue if Busy")?><span><?php echo _("Normally the next trunk is only tried upon a trunk being 'Congested' in some form, or unavailable. Checking this box will force a failed call to always continue to the next configured trunk or destination even when the channel reports BUSY or INVALID NUMBER.")?></span></a>
                </td>
                <td>
                <input type='checkbox'  tabindex="<?php echo ++$tabindex;?>"name='continue' id="continue" <?php if ($continue=="on") { echo 'CHECKED'; }?> '><label for='continue'><small><?php echo _("Check to always try next trunk")?></small></label>
                </td>
            </tr>

            <tr>
                <td><a class="info" href="#"><?php echo _("Disable Trunk")?><span><?php echo _("Check this to disable this trunk in all routes where it is used.")?></span></a>
                </td>
                <td>
                <input type='checkbox'  tabindex="<?php echo ++$tabindex;?>"name='disabletrunk' id="disabletrunk" <?php if ($disabletrunk=="on") { echo 'CHECKED'; }?> OnClick='disable_verify(disabletrunk); return true;'><label for='disabletrunk'><small><?php echo _("Disable")?></small></label>
                </td>
            </tr>

<?php
                        if ($failtrunk_enable && $failtrunk || $amp_conf['DISPLAY_MONITOR_TRUNK_FAILURES_FIELD']) {
?>
            <tr>
                <td><a class="info" href="#"><?php echo _("Monitor Trunk Failures")?><span><?php echo _("If checked, supply the name of a custom AGI Script that will be called to report, log, email or otherwise take some action on trunk failures that are not caused by either NOANSWER or CANCEL.")?></span></a>
                </td>
                <td>
                <input <?php if (!$failtrunk_enable) echo "disabled style='background: #DDD;'"?> type="text" size="20" name="failtrunk" value="<?php echo htmlspecialchars($failtrunk)?>"/>
                <input type='checkbox' tabindex="<?php echo ++$tabindex;?>" name='failtrunk_enable' id="failtrunk_enable" value='1' <?php if ($failtrunk_enable) { echo 'CHECKED'; }?> OnClick='disable_field(failtrunk,failtrunk_enable); return true;'><small><?php echo _("Enable")?></small>
                </td>
            </tr>
<?php
                        }
?>

    <tr>
      <td colspan="2"><h5>
      <a href=# class="info"><?php echo _("Dialed Number Manipulation Rules")?><span>
      <?php echo _("These rules can manipulate the dialed number before sending it out this trunk. If no rule applies, the number is not changed. The original dialed number is passed down from the route where some manipulation may have already occurred. This trunk has the option to further manipulate the number. If the number matches the combined values in the <b>prefix</b> plus the <b>match pattern</b> boxes, the rule will be applied and all subsequent rules ignored.<br/> Upon a match, the <b>prefix</b>, if defined, will be stripped. Next the <b>prepend</b> will be inserted in front of the <b>match pattern</b> and the resulting number will be sent to the trunk. All fields are optional.")?><br /><br /><b><?php echo _("Rules:")?></b><br />
      <b>X</b>&nbsp;&nbsp;&nbsp; <?php echo _("matches any digit from 0-9")?><br />
      <b>Z</b>&nbsp;&nbsp;&nbsp; <?php echo _("matches any digit from 1-9")?><br />
      <b>N</b>&nbsp;&nbsp;&nbsp; <?php echo _("matches any digit from 2-9")?><br />
      <b>[1237-9]</b>&nbsp;   <?php echo _("matches any digit in the brackets (example: 1,2,3,7,8,9)")?><br />
      <b>.</b>&nbsp;&nbsp;&nbsp; <?php echo _("wildcard, matches one or more dialed digits")?> <br />
      <b><?php echo _("prepend:")?></b>&nbsp;&nbsp;&nbsp; <?php echo _("Digits to prepend upon a successful match. If the dialed number matches the patterns in the <b>prefix</b> and <b>match pattern</b> boxes, this will be prepended before sending to the trunk.")?><br />
      <b><?php echo _("prefix:")?></b>&nbsp;&nbsp;&nbsp; <?php echo _("Prefix to remove upon a successful match. If the dialed number matches this plus the <b>match pattern</b> box, this prefix is removed before adding the optional <b>prepend</b> box and sending the results to the trunk.")?><br />
      <b><?php echo _("match pattern:")?></b>&nbsp;&nbsp;&nbsp; <?php echo _("The dialed number will be compared against the <b>prefix</b> plus this pattern. Upon a match, this portion of the number will be sent to the trunks after removing the <b>prefix</b> and appending the <b>prepend</b> digits")?><br />
        <?php echo _("You can completely replace a number by matching on the <b>prefix</b> only, replacing it with a <b>prepend</b> and leaving the <b>match pattern</b> blank."); ?>
      </span></a>
      </h5></td>
    </tr>

    <tr><td colspan="2"><div class="dialpatterns"><table>
<?php
  $pp_tit = _("prepend");
  $pf_tit = _("prefix");
  $mp_tit = _("match pattern");
  $dpt_title_class = 'dpt-title dpt-display';
  foreach ($dialpattern_array as $idx => $pattern) {
    $tabindex++;
    if ($idx == 50) {
      $dpt_title_class = 'dpt-title dpt-nodisplay';
    }
    $dpt_class = $pattern['prepend_digits'] == '' ? $dpt_title_class : 'dpt-value';
    echo <<< END
    <tr>
      <td colspan="2">
        (<input title="$pp_tit" type="text" size="10" id="prepend_digit_$idx" name="prepend_digit[$idx]" class="dial-pattern dp-prepend $dpt_class" value="{$pattern['prepend_digits']}" tabindex="$tabindex">) +
END;
    $tabindex++;
    $dpt_class = $pattern['match_pattern_prefix'] == '' ? $dpt_title_class : 'dpt-value';
    echo <<< END
        <input title="$pf_tit" type="text" size="6" id="pattern_prefix_$idx" name="pattern_prefix[$idx]" class="dp-prefix $dpt_class" value="{$pattern['match_pattern_prefix']}" tabindex="$tabindex"> |
END;
    $tabindex++;
    $dpt_class = $pattern['match_pattern_pass'] == '' ? $dpt_title_class : 'dpt-value';
    echo <<< END
        <input title="$mp_tit" type="text" size="20" id="pattern_pass_$idx" name="pattern_pass[$idx]" class="dp-match $dpt_class" value="{$pattern['match_pattern_pass']}" tabindex="$tabindex">
END;
?>
        <img src="images/core_add.png" style="cursor:pointer; float:none; margin-left:0px; margin-bottom:-3px;" alt="<?php echo _("insert")?>" title="<?php echo _('Click here to insert a new pattern')?>" onclick="addCustomField('','','',$('#prepend_digit_<?php echo $idx?>').parent().parent())">
        <img src="images/trash.png" style="cursor:pointer; float:none; margin-left:0px; margin-bottom:-3px;" alt="<?php echo _("remove")?>" title="<?php echo _('Click here to remove this pattern')?>" onclick="patternsRemove(<?php echo "$idx" ?>)">
      </td>
    </tr>
<?php
  }
  $next_idx = count($dialpattern_array);
?>
    <tr>
      <td colspan="2">
        (<input title="<?php echo $pp_tit?>" type="text" size="10" id="prepend_digit_<?php echo $next_idx?>" name="prepend_digit[<?php echo $next_idx?>]" class="dp-prepend dial-pattern dpt-title dpt-display" value="" tabindex="<?php echo ++$tabindex;?>">) +
        <input title="<?php echo $pf_tit?>" type="text" size="6" id="pattern_prefix_<?php echo $next_idx?>" name="pattern_prefix[<?php echo $next_idx?>]" class="dp-prefix dpt-title dpt-display" value="" tabindex="<?php echo ++$tabindex;?>"> |
        <input title="<?php echo $mp_tit?>" type="text" size="20" id="pattern_pass_<?php echo $next_idx?>" name="pattern_pass[<?php echo $next_idx?>]" class="dp-match dpt-title dpt-display" value="" tabindex="<?php echo ++$tabindex;?>">
        <img src="images/core_add.png" style="cursor:pointer; float:none; margin-left:0px; margin-bottom:-3px;" alt="<?php echo _("insert")?>" title="<?php echo _('Click here to insert a new pattern')?>" onclick="addCustomField('','','',$('#prepend_digit_<?php echo $idx?>').parent().parent())">
        <img src="images/trash.png" style="cursor:pointer; float:none; margin-left:0px; margin-bottom:-3px;" alt="<?php echo _("remove")?>" title="<?php echo _("Click here to remove this pattern")?>" onclick="patternsRemove(<?php echo "$next_idx" ?>)">

      </td>
    </tr>
    <tr id="last_row"></tr> 
    </table></div></tr>
<?php
  $tabindex += 2000; // make room for dynamic insertion of new fields
?>
    <tr><td colspan="2">
      <input type="button" id="dial-pattern-add"  value="<?php echo _("+ Add More Dial Pattern Fields")?>" />
      <input type="button" id="dial-pattern-clear"  value="<?php echo _("Clear all Fields")?>" />
    </td></tr>
            <tr>
                <td>
                    <a href=# class="info"><?php echo _("Dial Rules Wizards")?><span>
                    <strong><?php echo _("Always dial with prefix")?></strong> <?php echo _("is useful for VoIP trunks, where if a number is dialed as \"5551234\", it can be converted to \"16135551234\".")?><br>
                    <strong><?php echo _("Remove prefix from local numbers")?></strong> <?php echo _("is useful for ZAP and DAHDi trunks, where if a local number is dialed as \"6135551234\", it can be converted to \"555-1234\".")?><br>
                    <strong><?php echo _("Setup directory assistance")?></strong> <?php echo _("is useful to translate a call to directory assistance")?><br>
                    <strong><?php echo _("Lookup numbers for local trunk")?></strong> <?php echo _("This looks up your local number on www.localcallingguide.com (NA-only), and sets up so you can dial either 7 or 10 digits (regardless of what your PSTN is) on a local trunk (where you have to dial 1+area code for long distance, but only 5551234 (7-digit dialing) or 6135551234 (10-digit dialing) for local calls")?><br>
                    <strong><?php echo _("Upload from CSV")?></strong> <?php echo sprintf(_("Upload patterns from a CSV file replacing existing entries. If there are no headers then the file must have 3 columns of patterns in the same order as in the GUI. You can also supply headers: %s, %s and %s in the first row. If there are less then 3 recognized headers then the remaining columns will be blank"),'<strong>prepend</strong>','<strong>prefix</strong>','<strong>match pattern</strong>')?><br>
                    </span></a>
                </td><td valign="top"><select id="autopop"  tabindex="<?php echo ++$tabindex;?>" name="autopop" onChange="changeAutoPop();" class='componentSelect'>
                        <option value="" SELECTED><?php echo _("(pick one)")?></option>
                        <option value="always"><?php echo _("Always dial with prefix")?></option>
                        <option value="remove"><?php echo _("Remove prefix from local numbers")?></option>
                        <option value="directory"><?php echo _("Setup directory assistance")?></option>
                        <option value="lookup7"><?php echo _("Lookup numbers for local trunk (7-digit dialing)")?></option>
                        <option value="lookup10"><?php echo _("Lookup numbers for local trunk (10-digit dialing)")?></option>
            <option value="csv"><?php echo _("Upload from CSV")?></option>
                    </select>
          <input type="file" name="pattern_file" id="pattern_file" tabindex="<?php echo ++$tabindex;?>"/>
                </td>
            </tr>
            <script language="javascript">
            
            function disable_field(field, field_enable) {
                if (field_enable.checked) {
                field.style.backgroundColor = '#FFF';
                field.disabled = false;
                }
                else {
                field.style.backgroundColor = '#DDD';
                field.disabled = true;
                }
            }

            function disable_verify(field) {
                if (field.checked) {
                    var answer=confirm("<?php echo _("Are you sure you want to disable this trunk in all routes it is used?") ?>");
                    if (!answer) {
                        field.checked = false;
                    }
                } else {
                    alert("<?php echo _("You have enabled this trunk in all routes it is used") ?>");
                }
            }

            function populateLookup(digits) {
<?php 
    if (function_exists("curl_init")) { // curl is installed
?>                
                //var npanxx = prompt("What is your areacode + prefix (NPA-NXX)?", document.getElementById('areacode').value);
                do {
                    var npanxx = <?php echo 'prompt("'._("What is your areacode + prefix (NPA-NXX)?\\n\\n(Note: this database contains North American numbers only, and is not guaranteed to be 100% accurate. You will still have the option of modifying results.)\\n\\nThis may take a few seconds.".'")')?>;
                    if (npanxx == null) return;
                } while (!npanxx.match("^[2-9][0-9][0-9][-]?[2-9][0-9][0-9]$") && <?php echo '!alert("'._("Invalid NPA-NXX. Must be of the format \'NXX-NXX\'").'")'?>);
                
                document.getElementById('npanxx').value = npanxx;
                if (digits == 10) {
                    document.trunkEdit.action.value = "populatenpanxx10";
                } else {
                    document.trunkEdit.action.value = "populatenpanxx7";
                }
        clearPatterns();
                document.trunkEdit.submit();
<?php  
    } else { // curl is not installed
?>
                <?php echo 'alert("'._("Error: Cannot continue!\\n\\nPrefix lookup requires cURL support in PHP on the server. Please install or enable cURL support in your PHP installation to use this function. See http://www.php.net/curl for more information.").'")'?>;
<?php 
    }
?>
            }
            
            function populateAlwaysAdd() {
                do {
          var localpattern = <?php echo 'prompt("'._("What is the local dialing pattern?\\n\\n(ie. NXXNXXXXXX for US/CAN 10-digit dialing, NXXXXXX for 7-digit)").'"'?>,"<?php echo _("NXXXXXX")?>");
                    if (localpattern == null) return;
                } while (!localpattern.match('^[0-9#*ZXN\.]+$') && <?php echo '!alert("'._("Invalid pattern. Only 0-9, #, *, Z, N, X and . are allowed.").'")'?>);
                
                do {
                    var localprefix = <?php echo 'prompt("'._("What prefix should be added to the dialing pattern?\\n\\n(ie. for US/CAN, 1+areacode, ie, \'1613\')?").'")'?>;
                    if (localprefix == null) return;
                } while (!localprefix.match('^[0-9#*]+$') && <?php echo '!alert("'._("Invalid prefix. Only dialable characters (0-9, #, and *) are allowed.").'")'?>);

        return addCustomField(localprefix,'',localpattern,$("#last_row"));
            }
            
            function populateRemove() {
                do {
                    var localprefix = <?php echo 'prompt("'._("What prefix should be removed from the number?\\n\\n(ie. for US/CAN, 1+areacode, ie, \'1613\')").'")'?>;
                    if (localprefix == null) return;
                } while (!localprefix.match('^[0-9#*ZXN\.]+$') && <?php echo '!alert("'._('Invalid prefix. Only 0-9, #, *, Z, N, and X are allowed.').'")'?>);
                
                do {
          var localpattern = <?php echo 'prompt("'._("What is the dialing pattern for local numbers after")?> "+localprefix+"? \n\n<?php echo _("(ie. NXXNXXXXXX for US/CAN 10-digit dialing, NXXXXXX for 7-digit)").'"'?>,"<?php echo _("NXXXXXX")?>");
                    if (localpattern == null) return;
                } while (!localpattern.match('^[0-9#*ZXN\.]+$') && <?php echo '!alert("'._("Invalid pattern. Only 0-9, #, *, Z, N, X and . are allowed.").'")'?>);
                
        return addCustomField('',localprefix,localpattern,$("#last_row"));
            }

            function populatedirectory() {
                do {
        var localprefix = <?php echo 'prompt("'._("What is the directory assistance number you will dial locally in the format that is passed to this trunk?").'"'?>,"<?php echo ""?>");
                    if (localprefix == null) return;
                } while (!localprefix.match('^[0-9#*]+$') && <?php echo '!alert("'._("Invalid pattern. Only 0-9, #, *").'")'?>);
                do {

        var localprepend = <?php echo 'prompt("'._("Number to dial when calling directory assistance on this trunk").'"'?>,"<?php echo '' ?>");
                    if (localprepend == null) return;
                } while (!localprepend.match('^[0-9#*]+$') && <?php echo '!alert("'._('Invalid number. Only 0-9, #,  and * are allowed.').'")'?>);
                
        return addCustomField(localprepend,localprefix,'',$("#last_row"));
            }
            
            function changeAutoPop() {
        var idx = false;
        // hide the file box if nothing was set
        if ($('#pattern_file').val() == '') {
          $('#pattern_file').hide();
        }
                switch(document.getElementById('autopop').value) {
                    case "always":
                        idx = populateAlwaysAdd();
            if (idx) {
              $('#pattern_prefix_'+idx).focus();
            }
                    break;
                    case "remove":
                        idx = populateRemove();
            if (idx) {
              $('#prepend_digit_'+idx).focus();
            }
                    break;
                    case "directory":
                        idx = populatedirectory();
            if (idx) {
              $('#pattern_pass_'+idx).focus();
            }
                    break;
                    case "lookup7":
                        populateLookup(7);
                    break;
                    case "lookup10":
                        populateLookup(10);
                    break;
                    case 'csv':
            $('#pattern_file').show().click();
            return true;
                    break;
                }
                document.getElementById('autopop').value = '';
            }
            </script>

            <tr>
                <td>
                    <a href=# class="info"><?php echo _("Outbound Dial Prefix")?><span><?php echo _("The outbound dialing prefix is used to prefix a dialing string to all outbound calls placed on this trunk. For example, if this trunk is behind another PBX or is a Centrex line, then you would put 9 here to access an outbound line. Another common use is to prefix calls with 'w' on a POTS line that need time to obtain dial tone to avoid eating digits.<br><br>Most users should leave this option blank.")?></span></a>
                </td><td>
                    <input type="text" size="8" name="dialoutprefix" id="dialoutprefix" value="<?php echo htmlspecialchars($dialoutprefix) ?>" tabindex="<?php echo ++$tabindex;?>"/>
                </td>
            </tr>
            <?php if (isset($extdisplay) && !empty($extdisplay) && !empty($dialpattern_array)) {?>
            <tr>
                <td><a href=# class="info"><?php echo _("Export Dialplans as CSV")?><span><?php echo sprintf(_("Export patterns as a CSV file with headers listed as: %s, %s and %s in the first row."),'<strong>prepend</strong>','<strong>prefix</strong>','<strong>match pattern</strong>')?></span><a>:</td>
                <td><input type="button" onclick="parent.location='config.php?quietmode=1&amp;handler=file&amp;file=export.html.php&amp;module=core&amp;display=trunks&amp;extdisplay=<?php echo $extdisplay;?>'" value="Export"></td>
            </tr>
            <?php } ?>
            <?php if ($tech != "enum" && $tech != "pjsip") { ?>
            <tr>
                <td colspan="2">
        <h5><?php echo _("Outgoing Settings")?></h5>
                </td>
            </tr>
            <?php } ?>

    <?php 
    switch ($tech) {
        case "zap":
    ?>
                <tr>
                    <td>
                        <a href=# class="info"><?php echo _("Zap Identifier")?><span><?php echo _("ZAP channels are referenced either by a group number or channel number (which is defined in zapata.conf).  <br><br>The default setting is <b>g0</b> (group zero).")?></span></a>
                    </td><td>
                        <input type="text" size="8" name="channelid" value="<?php echo htmlspecialchars($channelid) ?>" tabindex="<?php echo ++$tabindex;?>" class="w100"/>
                        <input type="hidden" size="14" name="usercontext" value="notneeded"/>
                    </td>
                </tr>
    <?php 
        break;
        case "dahdi":
    ?>
                <tr>
                    <td>
                        <a href=# class="info"><?php echo _("DAHDi Identifier")?><span><?php echo _("DAHDi channels are referenced either by a group number or channel number (which is defined in chan_dahdi.conf).  <br><br>The default setting is <b>g0</b> (group zero).")?></span></a>
                    </td><td>
                        <input type="text" size="8" name="channelid" value="<?php echo htmlspecialchars($channelid) ?>" tabindex="<?php echo ++$tabindex;?>" class="w100"/>
                        <input type="hidden" size="14" name="usercontext" value="notneeded"/>
                    </td>
                </tr>
    <?php 
        break;
        case "enum":
        break;
    //--------------------------------------------------------------------------------------
    // Added to enable the unsupported misdn module
        case "misdn":
      if (function_exists('misdn_groups_ports')) {
  ?> 
        <tr> 
          <td> 
            <a href=# class="info"><?php echo _("mISDN Group/Port")?><span><br><?php echo _("mISDN channels are referenced either by a group name or channel number (use <i>mISDN Port Groups</i> to configure).")?><br><br></span></a>
          </td> 
          <td> 
            <select name="channelid"> 
  <?php 
        $gps = misdn_groups_ports(); 
        foreach($gps as $gp) { 
          echo "<option value='$gp'"; 
          if ($gp == $channelid) 
            echo ' selected="1"'; 
          }
          echo ">$gp</option>\n"; 
  ?> 
            </select> 
            <input type="hidden" size="14" name="usercontext" value="notneeded"/> 
          </td> 
        </tr> 
  <?php 
      }
    break; 
    //--------------------------------------------------------------------------------------
        case "custom":
    ?>
                <tr>
                    <td>
                        <a href=# class="info"><?php echo _("Custom Dial String")?><span><?php echo _("Define the custom Dial String.  Include the token")?> $OUTNUM$ <?php echo _("wherever the number to dial should go.<br><br><b>examples:</b><br>")?>CAPI/XXXXXXXX/$OUTNUM$<br>H323/$OUTNUM$@XX.XX.XX.XX<br>OH323/$OUTNUM$@XX.XX.XX.XX:XXXX<br>vpb/1-1/$OUTNUM$</span></a>
                    </td><td>
                        <input type="text" size="35" maxlength="46" name="channelid" value="<?php echo htmlspecialchars($channelid) ?>" tabindex="<?php echo ++$tabindex;?>" class="w100"/>
                        <input type="hidden" size="14" name="usercontext" value="notneeded"/>
                    </td>
                </tr>    
    <?php
        break;
        case "dundi":
    ?>
                <tr>
                    <td>
                        <a href=# class="info"><?php echo _("DUNDi Mapping")?><span><?php echo _("This is the name of the DUNDi mapping as defined in the [mappings] section of remote dundi.conf peers. This corresponds to the 'include' section of the peer details in the local dundi.conf file. This requires manual configuration of DUNDi to use this trunk.")?></span></a>
                    </td><td>
                        <input type="text" size="35" maxlength="46" name="channelid" value="<?php echo htmlspecialchars($channelid) ?>" tabindex="<?php echo ++$tabindex;?>" class="w100"/>
                        <input type="hidden" size="14" name="usercontext" value="notneeded"/>
                    </td>
                </tr>    
    <?php
        break;
        default:
    ?>
                <tr>
                    <td>
                        <a href=# class="info"><?php echo _("Trunk Name")?><span><?php echo _("Give this trunk a unique name.  Example: myiaxtel")?></span></a>
                    </td><td>
                        <input type="text" size="14" name="channelid" value="<?php echo htmlspecialchars($channelid) ?>" tabindex="<?php echo ++$tabindex;?>" class="w100"/>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                    <a href=# class="info"><?php echo _("PEER Details")?><span><?php echo _("Modify the default PEER connection parameters for your VoIP provider.<br><br>You may need to add to the default lines listed below, depending on your provider.<br /><br />WARNING: Order is important as it will be retained. For example, if you use the \"allow/deny\" directives make sure deny comes first.")?></span></a>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <textarea rows="10" cols="40" style='width:100%; height:10em;' name="peerdetails" tabindex="<?php echo ++$tabindex;?>"><?php echo htmlspecialchars($peerdetails) ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <h5><?php echo _("Incoming Settings")?></h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <a href=# class="info"><?php echo _("USER Context")?><span><?php echo _("This is most often the account name or number your provider expects.<br><br>This USER Context will be used to define the below user details.")?></span></a>
                    </td><td>
                        <input type="text" size="14" name="usercontext" value="<?php echo htmlspecialchars($usercontext)  ?>" tabindex="<?php echo ++$tabindex;?>" class="w100"/>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                    <a href=# class="info"><?php echo _("USER Details")?><span><?php echo _("Modify the default USER connection parameters for your VoIP provider.")?><br><br><?php echo _("You may need to add to the default lines listed below, depending on your provider..<br /><br />WARNING: Order is important as it will be retained. For example, if you use the \"allow/deny\" directives make sure deny 
                comes first.")?></span></a>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <textarea rows="10" cols="40" style='width:100%; height:10em;' name="userconfig" tabindex="<?php echo ++$tabindex;?>"><?php echo htmlspecialchars($userconfig); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <h5><?php echo _("Registration")?></h5>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <a href=# class="info"><?php echo _("Register String")?><span><?php echo _("Most VoIP providers require your system to REGISTER with theirs. Enter the registration line here.<br><br>example:<br><br>username:password@switch.voipprovider.com.<br><br>Many providers will require you to provide a DID number, ex: username:password@switch.voipprovider.com/didnumber in order for any DID matching to work.")?></span></a>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="text" size="90" name="register" value="<?php echo htmlspecialchars($register) ?>" tabindex="<?php echo ++$tabindex;?>" />
                    </td>
                </tr>
    <?php 
        break;
                case "pjsip":
?>
                <tr>
                    <td colspan="2">
                        <h5><?php echo _("PJSIP Settings")?></h5>
                    </td>
                </tr>

                <tr>
                    <td>
                        <a href=# class="info"><?php echo _("Trunk Name")?><span><?php echo _("Give this trunk a unique name.  Example: mypjsipprovider")?></span></a>
                    </td><td>
                        <input type="text" size="14" id="pjsip_channelid" name="channelid" value="<?php echo htmlspecialchars($channelid) ?>" tabindex="<?php echo ++$tabindex;?>"/>
                    </td>
                </tr>
    
<tr> 
<td> 
 <a href=# class="info"><?php echo _("Username")?><span><?php echo _("Authentication user name for this trunk.")?></span></a>
</td> 
<td> 
<input type="text" size="30" name="pjsip_username" id="pjsip_username" data-originalvalue="<?php echo htmlspecialchars($pjsip_username) ?>" value="<?php echo htmlspecialchars($pjsip_username) ?>" tabindex="<?php echo ++$tabindex;?>"/> 
</td> 
</tr>

<tr> 
<td> 
 <a href=# class="info"><?php echo _("Secret")?><span><?php echo _("Authentication password for this trunk.")?></span></a>
</td> 
<td> 
<input type="text" size="30" name="pjsip_secret" value="<?php echo htmlspecialchars($pjsip_secret) ?>" tabindex="<?php echo ++$tabindex;?>"/> 
</td> 
</tr>

<tr> 
<td> 
 <a href=# class="info"><?php echo _("Authentication")?><span><?php echo _("When to use authentication on this trunk.")?></span></a>
</td> 
<td> 
<select name="pjsip_authentication" id="pjsip_authentication" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'/> 
<?php
     $auths = array();
     $auths[_('Outbound')]='outbound';
     $auths[_('Inbound')]='inbound';
     $auths[_('Both')]='both';
     $auths[_('None')]='none';

     foreach($auths as $key=>$val) {
         echo "<option value='$val'"; 
         if ($val == $pjsip_authentication) { 
             echo ' selected="1"'; 
         }
         echo ">$key</option>\n"; 
     }
?>
</select>
</td> 
</tr>


<tr> 
<td> 
 <a href=# class="info"><?php echo _("Registration")?><span><?php echo _("When to use registration on this trunk.")?></span></a>
</td> 
<td> 
<select id="pjsip_registration" name="pjsip_registration" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'/> 
<?php
     $regs = array();
     $regs[_('Send')]='send';
     $regs[_('Receive')]='receive';
     $regs[_('None')]='none';

     foreach($regs as $key=>$val) {
         echo "<option value='$val'"; 
         if ($val == $pjsip_registration) { 
             echo ' selected="1"'; 
         }
         echo ">$key</option>\n"; 
     }
?>
</select>
</td> 
</tr>


<tr> 
<td> 
 <a href=# class="info"><?php echo _("SIP Server")?><span><?php echo _("Hostname or IP address of your VoIP provider.")?></span></a>
</td> 
<td> 
<input type="text" size="30" name="pjsip_server" id="pjsip_server" value="<?php echo htmlspecialchars($pjsip_server) ?>" tabindex="<?php echo ++$tabindex;?>"/> 
</td> 
</tr>

<tr> 
<td> 
 <a href=# class="info"><?php echo _("SIP Server Port")?><span><?php echo _("SIP port your VoIP provider listens to.")?></span></a>
</td> 
<td> 
<input type="text" size="30" name="pjsip_port" id="pjsip_port" value="<?php echo htmlspecialchars($pjsip_port) ?>" tabindex="<?php echo ++$tabindex;?>"/> 
</td> 
</tr>

<tr> 
<td> 
 <a href=# class="info"><?php echo _("Context")?><span><?php echo _("Dialplan context to use for inbound calls.")?></span></a>
</td> 
<td> 
<input type="text" size="30" name="pjsip_context" value="<?php echo htmlspecialchars($pjsip_context) ?>" tabindex="<?php echo ++$tabindex;?>"/> 
</td> 
</tr>

<tr> 
<td> 
 <a href=# class="info"><?php echo _("Transport")?><span><?php echo _("Transport to use.")?></span></a>
</td> 
<td> 
<select name="pjsip_transport" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'/> 
<?php
     $transports = array();
     $transports['UDP']='transport-udp';
     $transports['TCP']='transport-tcp';
     $transports['TLS']='transport-tls';
     $transports['WS']='transport-ws';
     $transports['WSS']='transport-wss';

     foreach($transports as $key=>$val) {
         echo "<option value='$val'"; 
         if ($val == $pjsip_transport) { 
             echo ' selected="1"'; 
         }
         echo ">$key</option>\n"; 
     }
?>
</select>
</td> 
</tr>


                <tr>
                    <td colspan="2">
                        <h5><?php echo _("PJSIP Advanced Settings")?></h5>
                    </td>
                </tr>


<tr> 
<td> 
 <a href=# class="info"><?php echo _("DTMF Mode")?><span><?php echo _("The DTMF signaling mode used by this trunk, usually RFC for most trunks<br/><ul><li>Auto [Asterisk 13] - DTMF is sent as RFC 4733 if the other side supports it or as INBAND if not.</li><li>rfc4733 - DTMF is sent out of band of the main audio stream.This supercedes the older RFC-2833 used within the older chan_sip.</li><li>inband - DTMF is sent as part of audio stream.</li><li>info - DTMF is sent as SIP INFO packets..</li></ul>")?></span></a>
</td> 
<td> 
<select name="pjsip_dtmf_mode" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'/> 
<?php
     $select = array();
     $select['auto'] = _('Auto');
     $select['rfc4733']  = _('RFC 4733');
     $select['inband']   = _('Inband');
     $select['info']     = _('Info');

     foreach($select as $key=>$val) {
         echo "<option value='$key'"; 
         if ($key == $pjsip_dtmf_mode) { 
             echo ' selected="1"'; 
         }
         echo ">$val</option>\n"; 
     }
?>
</select>
</td> 
</tr>

<tr> 
<td> 
 <a href=# class="info"><?php echo _("From Domain")?><span><?php echo _("Domain to use in From header for requests to this trunk.")?></span></a>
</td> 
<td> 
<input type="text" size="30" name="pjsip_from_domain" value="<?php echo htmlspecialchars($pjsip_from_domain) ?>" tabindex="<?php echo ++$tabindex;?>"/> 
</td> 
</tr>

<tr> 
<td> 
 <a href=# class="info"><?php echo _("From User")?><span><?php echo _("Username to use in From header for requests to this trunk.")?></span></a>
</td> 
<td> 
<input type="text" size="30" name="pjsip_from_user" value="<?php echo htmlspecialchars($pjsip_from_user) ?>" tabindex="<?php echo ++$tabindex;?>"/> 
</td> 
</tr>

<tr> 
<td> 
 <a href=# class="info"><?php echo _("General Retry Interval")?><span><?php echo _("The number of seconds Asterisk will wait before attempting to send another REGISTER request to the registrar.")?></span></a>
</td> 
<td>
<input type="text" size="30" name="pjsip_retry_interval" value="<?php echo htmlspecialchars($pjsip_retry_interval) ?>" tabindex="<?php echo ++$tabindex;?>"/> 
</td> 
</tr>

<tr> 
<td> 
 <a href=# class="info"><?php echo _("Max Retries")?><span><?php echo _("How many times Asterisk will attempt to re-attempt registration before permanently giving up. Maximum of 10000000")?></span></a>
</td> 
<td>
<input type="text" size="30" name="pjsip_max_retries" value="<?php echo htmlspecialchars($pjsip_max_retries) ?>" tabindex="<?php echo ++$tabindex;?>"/> 
</td> 
</tr>


<tr> 
<td> 
 <a href=# class="info"><?php echo _("Expiration")?><span><?php echo _("Expiration time for registrations in seconds")?></span></a>
</td> 
<td>
<input type="text" size="30" name="pjsip_expiration" value="<?php echo htmlspecialchars($pjsip_expiration) ?>" tabindex="<?php echo ++$tabindex;?>"/> 
</td> 
</tr>


<tr> 
<td> 
 <a href=# class="info"><?php echo _("Qualify Frequency")?><span><?php echo _("Interval at which to qualify.")?></span></a>
</td> 
<td>
<input type="text" size="30" name="pjsip_qualify_frequency" value="<?php echo htmlspecialchars($pjsip_qualify_frequency) ?>" tabindex="<?php echo ++$tabindex;?>"/> 
</td> 
</tr>

<tr> 
<td> 
 <a href=# class="info"><?php echo _("Symmetric RTP")?><span><?php echo _("Enforce that RTP must be symmetric. This should almost always be on.")?></span></a>
</td> 
<td> 
<select name="pjsip_rtp_symmetric" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'/> 
<?php
     $select = array();
     $select['yes'] = _('Yes');
     $select['no']  = _('No');

     foreach($select as $key=>$val) {
         echo "<option value='$key'"; 
         if ($key == $pjsip_rtp_symmetric) { 
             echo ' selected="1"'; 
         }
         echo ">$val</option>\n"; 
     }
?>
</select>
</td> 
</tr>

<tr> 
<td> 
 <a href=# class="info"><?php echo _("Rewrite Contact")?><span><?php echo _("Allow Contact header to be rewritten with the source IP address-port.")?></span></a>
</td> 
<td> 
<select name="pjsip_rewrite_contact" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'/> 
<?php
     $select = array();
     $select['yes'] = _('Yes');
     $select['no']  = _('No');

     foreach($select as $key=>$val) {
         echo "<option value='$key'"; 
         if ($key == $pjsip_rewrite_contact) { 
             echo ' selected="1"'; 
         }
         echo ">$val</option>\n"; 
     }
?>
</select>
</td> 
</tr>


<tr> 
<td> 
 <a href=# class="info"><?php echo _("Inband Progress")?><span><?php echo _("Determines whether chan_pjsip will indicate ringing using inband progress.")?></span></a>
</td> 
<td> 
<select name="pjsip_inband_progress" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'/> 
<?php
     $select = array();
     $select['yes'] = _('Yes');
     $select['no']  = _('No');

     foreach($select as $key=>$val) {
         echo "<option value='$key'"; 
         if ($key == $pjsip_inband_progress) { 
             echo ' selected="1"'; 
         }
         echo ">$val</option>\n"; 
     }
?>
</select>
</td> 
</tr>

<tr> 
<td> 
 <a href=# class="info"><?php echo _("Permanent Auth Rejection")?><span><?php echo _("Determines whether failed authentication challenges are treated as permanent failures.")?></span></a>
</td> 
<td> 
<select name="pjsip_auth_rejection_permanent" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'/> 
<?php
     $select = array();
     $select['yes'] = _('Yes');
     $select['no']  = _('No');

     foreach($select as $key=>$val) {
         echo "<option value='$key'"; 
         if ($key == $pjsip_auth_rejection_permanent) { 
             echo ' selected="1"'; 
         }
         echo ">$val</option>\n"; 
     }
?>
</select>
</td> 
</tr>


<tr> 
<td> 
 <a href=# class="info"><?php echo _("Direct Media")?><span><?php echo _("Determines whether media may flow directly between endpoints.")?></span></a>
</td> 
<td> 
<select name="pjsip_direct_media" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'/> 
<?php
     $select = array();
     $select['yes'] = _('Yes');
     $select['no']  = _('No');

     foreach($select as $key=>$val) {
         echo "<option value='$key'"; 
         if ($key == $pjsip_direct_media) { 
             echo ' selected="1"'; 
         }
         echo ">$val</option>\n"; 
     }
?>
</select>

</td> 
</tr>


<tr> 
<td> 
 <a href=# class="info"><?php echo _("Trust RPID/PAI")?><span><?php echo _("Trust the Remote-Party-ID and/or P-Asserted-Identity header")?></span></a>
</td> 
<td> 
<select name="pjsip_trust_id_inbound" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'/> 
<?php
     $select = array();
     $select['yes'] = _('Yes');
     $select['no']  = _('No');

     foreach($select as $key=>$val) {
         echo "<option value='$key'"; 
         if ($key == $pjsip_trust_id_inbound) { 
             echo ' selected="1"'; 
         }
         echo ">$val</option>\n"; 
     }
?>
</select>
</td> 
</tr>



<tr> 
<td> 
 <a href=# class="info"><?php echo _("Support T.38 UDPTL")?><span><?php echo _("Whether T.38 UDPTL support is enabled or not.")?></span></a>
</td> 
<td> 
<select name="pjsip_t38_udptl" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'/> 
<?php
     $select = array();
     $select['yes'] = _('Yes');
     $select['no']  = _('No');

     foreach($select as $key=>$val) {
         echo "<option value='$key'"; 
         if ($key == $pjsip_t38_udptl) { 
             echo ' selected="1"'; 
         }
         echo ">$val</option>\n"; 
     }
?>
</select>
</td> 
</tr>


<tr> 
<td> 
 <a href=# class="info"><?php echo _("T.38 UDPTL NAT")?><span><?php echo _("Whether NAT support is enabled on UDPTL sessions.")?></span></a>
</td> 
<td> 
<select name="pjsip_t38_udptl_nat" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'/> 
<?php
     $select = array();
     $select['yes'] = _('Yes');
     $select['no']  = _('No');

     foreach($select as $key=>$val) {
         echo "<option value='$key'"; 
         if ($key == $pjsip_t38_udptl_nat) { 
             echo ' selected="1"'; 
         }
         echo ">$val</option>\n"; 
     }
?>
</select>
</td> 
</tr>

<tr> 
<td> 
 <a href=# class="info"><?php echo _("T.38 UDPTL Error Correction")?><span><?php echo _("T.38 UDPTL error correction method.")?></span></a>
</td> 
<td> 
<select name="pjsip_t38_udptl_ec" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'/> 
<?php
     $select = array();
     $select['none'] = _('None');
     $select['fec']  = _('Forward');
     $select['redundancy']  = _('Redundancy');

     foreach($select as $key=>$val) {
         echo "<option value='$key'"; 
         if ($key == $pjsip_t38_udptl_ec) { 
             echo ' selected="1"'; 
         }
         echo ">$val</option>\n"; 
     }
?>
</select>
</td> 
</tr>


<tr> 
<td> 
 <a href=# class="info"><?php echo _("Fax Detect")?><span><?php echo _("This option can be set to send the session to the fax extension when a CNG tone is detected.")?></span></a>
</td> 
<td> 
<select name="pjsip_fax_detect" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'/> 
<?php
     $select = array();
     $select['yes'] = _('Yes');
     $select['no']  = _('No');

     foreach($select as $key=>$val) {
         echo "<option value='$key'"; 
         if ($key == $pjsip_fax_detect) { 
             echo ' selected="1"'; 
         }
         echo ">$val</option>\n"; 
     }
?>
</select>
</td> 
</tr>

<tr> 
<td> 
 <a href=# class="info"><?php echo _("Match (Permit)")?><span><?php echo _("IP addresses or networks to match against. The value is a comma-delimited list of IP addresses. IP addresses may have a subnet mask appended. The subnet mask may be written in either CIDR or dot-decimal notation. Separate the IP address and subnet mask with a slash ('/'). This setting is automatically generated by the PBX if left blank")?></span></a>
</td> 
<td>
<input type="text" size="30" name="pjsip_match" value="<?php echo htmlspecialchars($pjsip_match) ?>" tabindex="<?php echo ++$tabindex;?>"/> 
</td> 
</tr>


<tr> 
<td> 
 <a href=# class="info"><?php echo _("Support Path")?><span><?php echo _("When this option is enabled, outbound REGISTER requests will advertise support for Path headers so that intervening proxies can add to the Path header as necessary..")?></span></a>
</td> 
<td> 
<select name="pjsip_support_path" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'/> 
<?php
     $select = array();
     $select['yes'] = _('Yes');
     $select['no']  = _('No');

     foreach($select as $key=>$val) {
         echo "<option value='$key'"; 
         if ($key == $pjsip_support_path) { 
             echo ' selected="1"'; 
         }
         echo ">$val</option>\n"; 
     }
?>
</select>
</td> 
</tr>


  <tr>
    <td colspan="2"><h5><?php echo _("Audio Codecs")?></h5></td>
  </tr>
  <tr>
    <td valign='top'><a href="#" class="info"><?php echo _("Codecs")?><span><?php echo _("Check the desired codecs, all others will be disabled. Drag to re-order.")?></span></a></td>
    <td>
<?php
  $seq = 1;
echo '<ul class="sortable">';
  $hasone=0;
  foreach ($codecs as $codec => $codec_state) {
      if($codec_state=='')  { $codec_state=1000; }  else { $hasone=1; }; 
      $codecs[$codec]=$codec_state;
  }

  if($hasone==0) {
      $codecs['ulaw']=1;
      $codecs['alaw']=2;
      $codecs['gsm']=3;
      $codecs['g726']=4;
      $codecs['g722']=5;
  }

  asort($codecs);
  foreach ($codecs as $codec => $codec_state) {
    $tabindex++;
    $codec_trans = _($codec);
    if($codec_state==1000) $codec_state=0;
    $codec_checked = $codec_state ? 'checked' : '';
        echo '<li><a href="#">'
                . '<img src="/admin/modules/core/assets/images/arrow_up_down.png" height="16" width="16" border="0" alt="move" style="float:none; margin-left:-6px; margin-bottom:-3px;cursor:move" /> '
                . '<input type="checkbox" '
                . ($codec_checked ? 'value="'. $seq++ . '" ' : '')
                . 'name="codec[' . $codec . ']" '
                . 'id="'. $codec . '" '
                . 'class="audio-codecs" tabindex="' . $tabindex. '" '
                . $codec_checked
                . ' />'
                . '<label for="'. $codec . '"> '
                . '<small>' . $codec_trans . '</small>'
                . ' </label></a></li>';
  }
echo '</ul>';
?>

    </td>
  </tr>







<?php

    }
  // implementation of module hook
  // object was initialized in config.php
  echo $module_hook->hookHtml;
  ?>
            <tr>
                <td colspan="2">
          <h6>
            <input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>">
            <input name="copytrunk" type="submit" value="<?php echo _("Duplicate Trunk");?>"/>
            <!--input type="button" id="page_reload" value="<?php echo _("Refresh Page");?>"/-->
          </h6>
                </td>
            </tr>
            </table>

<script language="javascript">
<!--

$(document).ready(function(){
  /* Add a Custom Var / Val textbox */
  $("#dial-pattern-add").click(function(){
    addCustomField('','','',$("#last_row"));
  });
  $('#pattern_file').hide();
  $("#dial-pattern-clear").click(function(){
    clearAllPatterns();
  });
  $(".dpt-display").toggleVal({
    populateFrom: "title",
    changedClass: "text-normal",
    focusClass: "text-normal"
  });
  $(".dpt-nodisplay").mouseover(function(){
    $(this).toggleVal({
      populateFrom: "title",
      changedClass: "text-normal",
      focusClass: "text-normal"
    }).removeClass('dpt-nodisplay').addClass('dpt-display').unbind('mouseover');
  });

  enable_disable_auth($('#pjsip_authentication').val());
  enable_disable_reg($('#pjsip_registration').val());

}); 

$('#pjsip_authentication').on('change', function() {
   enable_disable_auth(this.value);
});

function enable_disable_auth(value) {
   if(value=='none') {
       $('#pjsip_username').prop('disabled',true).attr('placeholder','<?php echo _('Authetication disabled'); ?>').val('');
       $('#pjsip_registration').prop('disabled',true);
   } else if(value=='outbound') {
      $('#pjsip_username').val($('#pjsip_username').data('originalvalue'));
      $('#pjsip_username').prop('disabled',false).attr('placeholder','');
       $('#pjsip_registration').prop('disabled',false);
   } else {
       $('#pjsip_username').prop('disabled',true).attr('placeholder','<?php echo _('Username is trunk name'); ?>').val('');
       $('#pjsip_registration').prop('disabled',false);
   }

}

$('#pjsip_registration').on('change', function() {
    enable_disable_reg(this.value);
});

function enable_disable_reg(value) {
    if(value=='receive') {
        $('#pjsip_server').prop('disabled',true);
        $('#pjsip_port').prop('disabled',true);
    } else {
        $('#pjsip_server').prop('disabled',false);
        $('#pjsip_port').prop('disabled',false);
    }
}

function patternsRemove(idx) {
  $("#prepend_digit_"+idx).parent().parent().remove();
}

function addCustomField(prepend_digit, pattern_prefix, pattern_pass, start_loc) {
  var idx = $(".dial-pattern").size();
  var idxp = idx - 1;
  var tabindex = parseInt($("#pattern_pass_"+idxp).attr('tabindex')) + 1;
  var tabindex1 = tabindex + 2;
  var tabindex2 = tabindex + 3;
  var dpt_title = 'dpt-title dpt-display';
  var dpt_prepend_digit = prepend_digit == '' ? dpt_title : 'dpt-value';
  var dpt_pattern_prefix = pattern_prefix == '' ? dpt_title : 'dpt-value';
  var dpt_pattern_pass = pattern_pass == '' ? dpt_title : 'dpt-value';

  var new_insert = start_loc.before('\
  <tr>\
    <td colspan="2">\
    (<input title="<?php echo $pp_tit?>" type="text" size="10" id="prepend_digit_'+idx+'" name="prepend_digit['+idx+']" class="dp-prepend dial-pattern '+dpt_prepend_digit+'" value="'+prepend_digit+'" tabindex="'+tabindex+'">) +\
    <input title="<?php echo $pf_tit?>" type="text" size="6" id="pattern_prefix_'+idx+'" name="pattern_prefix['+idx+']" class="dp-prefix '+dpt_pattern_prefix+'" value="'+pattern_prefix+'" tabindex="'+tabindex1+'"> |\
    <input title="<?php echo $mp_tit?>" type="text" size="20" id="pattern_pass_'+idx+'" name="pattern_pass['+idx+']" class="dp-match '+dpt_pattern_pass+'" value="'+pattern_pass+'" tabindex="'+tabindex2+'">\
      <img src="images/core_add.png" style="cursor:pointer; float:none; margin-left:0px; margin-bottom:-3px;" alt="<?php echo _("insert")?>" title="<?php echo _("Click here to insert a new pattern")?>" onclick="addCustomField(\'\',\'\',\'\',$(\'#prepend_digit_'+idx+'\').parent().parent())">\
      <img src="images/trash.png" style="cursor:pointer; float:none; margin-left:0px; margin-bottom:-3px;" alt="<?php echo _("remove")?>" title="<?php echo _("Click here to remove this pattern")?>" onclick="patternsRemove('+idx+')">\
    </td>\
  </tr>\
  ').prev();

  new_insert.find(".dpt-title").toggleVal({
    populateFrom: "title",
    changedClass: "text-normal",
    focusClass: "text-normal"
  });

  return idx;
}

function clearPatterns() {
  $(".dpt-display").each(function() {
    if($(this).val() == $(this).data("defText")) {
      $(this).val("");
    }
  });
  return true;
}

function clearAllPatterns() {

  $(".dpt-value").addClass('dpt-title dpt-nodisplay').removeClass('dpt-value').mouseover(function(){
    $(this).toggleVal({
      populateFrom: "title",
      changedClass: "text-normal",
      focusClass: "text-normal"
    }).removeClass('dpt-nodisplay').addClass('dpt-display').unbind('mouseover');
  }).each(function(){
    $(this).val("");
  });

  return true;
}

// all blanks are ok
function validatePatterns() {
  var culprit;
  var msgInvalidDialPattern;
  defaultEmptyOK = true;

  // TODO: need to validate differently for prepend, prefix and match fields. The prepend
  //      must be a dialable digit. The prefix can be any pattern but not contain "." and
  //      the pattern can contain a "." also
  //$filter_prepend = '/[^0-9\+\*\#/';
  //$filter_match = '/[^0-9\-\+\*\#\.\[\]xXnNzZ]/';
  //$filter_prefix = '/[^0-9\-\+\*\#\[\]xXnNzZ]/';
    //defaultEmptyOK = false;
  /* TODO: get some sort of check in for dialpatterns
    if (!isDialpattern(theForm.dialpattern.value))
        return warnInvalid(theForm.dialpattern, msgInvalidDialPattern);
    */

  $(".dp-prepend").each(function() {
    if ($.trim(this.value) == '') {
    } else if (this.value.search('[^0-9*#+wW\s]+') >= 0) {
      culprit = this;
      return false;
    }
  });
  if (!culprit) {
    $(".dp-prefix").each(function() {
      if ($.trim($(this).val()) == '') {
      } else if (!isDialpattern(this.value) || this.value.search('[._]+') >= 0) {
        culprit = this;
        return false;
      }
    });
  }
  if (!culprit) {
    $(".dp-match").each(function() {
      if ($.trim(this.value) == '') {
      } else if (!isDialpattern(this.value) || this.value.search('[_]+') >= 0) {
        culprit = this;
        return false;
      }
    });
  }

  if (culprit != undefined) {
      msgInvalidDialPattern = "<?php echo _('Dial pattern is invalid'); ?>";
    // now we have to put it back...
    // do I have to turn it off first though?
    $(".dpt-display").each(function() {
      if ($.trim($(this).val()) == '') {
        $(this).toggleVal({
          populateFrom: "title",
          changedClass: "text-normal",
          focusClass: "text-normal"
        });
      }
    });
    return warnInvalid(culprit, msgInvalidDialPattern);
  } else {
    return true;
  }
}

document.trunkEdit.trunk_name.focus();

function trunkEdit_onsubmit(act) {
    var theForm = document.trunkEdit;
    var msgInvalidOutboundCID = "<?php echo _('Invalid Outbound CallerID'); ?>";
    var msgInvalidMaxChans = "<?php echo _('Invalid Maximum Channels'); ?>";
    var msgInvalidDialRules = "<?php echo _('Invalid Dial Rules'); ?>";
    var msgInvalidOutboundDialPrefix = "<?php echo _('The Outbound Dial Prefix contains non-standard characters. If these are intentional the press OK to continue.'); ?>";
    var msgInvalidTrunkName = "<?php echo _('Invalid Trunk Name entered'); ?>";
    var msgInvalidChannelName = "<?php echo _('Invalid Custom Dial String entered'); ?>"; 
    var msgInvalidTrunkAndUserSame = "<?php echo _('Trunk Name and User Context cannot be set to the same value'); ?>";
    var msgConfirmBlankContext = "<?php echo _('User Context was left blank and User Details will not be saved!'); ?>";
    var msgCIDValueRequired = "<?php echo _('You must define an Outbound CallerID when Choosing this CID Options value'); ?>";
    var msgCIDValueEmpty = "<?php echo _('It is highly recommended that you define an Outbound CallerID on all trunks, undefined behavior can result when nothing is specified. The CID Options can control when this CID is used. Do you still want to continue?'); ?>";

    defaultEmptyOK = true;

    if (isEmpty($.trim(theForm.outcid.value))) {
        if (theForm.keepcid.value == 'on' || theForm.keepcid.value == 'all') {
            return warnInvalid(theForm.outcid, msgCIDValueRequired);
        } else {
            if (confirm(msgCIDValueEmpty) == false) {
                return false;
            }
        }
    }

    if (!isCallerID(theForm.outcid.value))
        return warnInvalid(theForm.outcid, msgInvalidOutboundCID);

    if (!isInteger(theForm.maxchans.value))
        return warnInvalid(theForm.maxchans, msgInvalidMaxChans);

    if (!isDialIdentifierSpecial(theForm.dialoutprefix.value)) {
        if (confirm(msgInvalidOutboundDialPrefix) == false) {
            $('#dialoutprefix').focus();
            return false;
        }
    }

    <?php if ($tech != "enum" && $tech != "custom" && $tech != "dundi" && $tech != "pjsip") { ?>
    defaultEmptyOK = true;
    if (isEmpty(theForm.channelid.value) || isWhitespace(theForm.channelid.value))
        return warnInvalid(theForm.channelid, msgInvalidTrunkName);

    if (theForm.channelid.value == theForm.usercontext.value)
        return warnInvalid(theForm.usercontext, msgInvalidTrunkAndUserSame);
    <?php } else if ($tech == "custom" || $tech == "dundi") { ?> 
    if (isEmpty(theForm.channelid.value) || isWhitespace(theForm.channelid.value)) 
        return warnInvalid(theForm.channelid, msgInvalidChannelName); 

    if (theForm.channelid.value == theForm.usercontext.value) 
        return warnInvalid(theForm.usercontext, msgInvalidTrunkAndUserSame);
    <?php } else if ($tech == "pjsip") { ?>
    defaultEmptyOK = true;
    if (isEmpty(theForm.channelid.value) || isWhitespace(theForm.channelid.value))
        return warnInvalid(theForm.channelid, msgInvalidTrunkName);
    <?php } ?>


    <?php if ($tech == "sip" || substr($tech,0,3) == "iax") { ?>
    if ((isEmpty(theForm.usercontext.value) || isWhitespace(theForm.usercontext.value)) && 
        (!isEmpty(theForm.userconfig.value) && !isWhitespace(theForm.userconfig.value)) &&
        (theForm.userconfig.value != "secret=***password***\ntype=user\ncontext=from-trunk")) {
            if (confirm(msgConfirmBlankContext) == false)
                return false;
        }
    <?php } ?>

    clearPatterns();
    if (validatePatterns()) {
        theForm.action.value = act;
        return true;
    } else {
        return false;
    }
}

function isDialIdentifierSpecial(s) { // special chars allowed in dial prefix (e.g. fwdOUT)
    var i;

    if (isEmpty(s)) 
       if (isDialIdentifierSpecial.arguments.length == 1) return defaultEmptyOK;
       else return (isDialIdentifierSpecial.arguments[1] == true);

    for (i = 0; i < s.length; i++)
    {   
        var c = s.charAt(i);

        if ( !isDialDigitChar(c) && (c != "w") && (c != "W") && (c != "q") && (c != "Q") && (c != "+") ) return false;
    }

    return true;
}
//-->
</script>

        </form>
<?php  
}
?>
