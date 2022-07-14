<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
// Copyright (C) 2008 Philippe Lindheimer & Bandwidth.com (plindheimer at bandwidth dot com)
// Copyright (C) 2011 Mikael Carlsson (mickecarlsson at gmail dot com)
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation, version 2
// of the License.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// TODO:
// Localization
// get all settings from astdb for extensions - DONE
// click on bulltest/checks toggle state
// same for VmX, enable or disable, or if time permits, change destinations
// make table scrollable with the headers intact

$dispnum = 'extensionsettings';
$extension = _("Extension");
$vmxlocator = _("VmX Locator");
$followme   = _("Follow-Me");
$callstatus = _("Call status");
$status     =_("Status");

global $active_modules;

$html_txt = '<div class="content">';

if (!$extdisplay) {
        $html_txt .= '<br><h2>'._("IssabelPBX Extension Settings").'</h2>';
}
$full_list = framework_check_extension_usage(true);
// Dont waste astman calls, get all family keys in one call
// Get all AMPUSER settings
$ampuser = $astman->database_show("AMPUSER");
// Get all CW settings
$cwsetting = $astman->database_show("CW");
// get all CF settings
$cfsetting = $astman->database_show("CF");
// get all CFB settings
$cfbsetting = $astman->database_show("CFB");
// get all CFU settings
$cfusetting = $astman->database_show("CFU");
// get all DND settings
$dndsetting = $astman->database_show("DND");

foreach ($full_list as $key => $value) {
  $vmxcolor = "BLACK;\"";
  $sub_heading_id = $txtdom = $active_modules[$key]['rawname'];
  if ($active_modules[$key]['rawname'] != 'core' || ($quietmode && !isset($_REQUEST[$sub_heading_id]))) {
    continue; // we just want core
  }
  if ($txtdom == 'core') {
    $active_modules[$key]['name'] = 'Extensions';
    $core_heading = $sub_heading =  modgettext::_($active_modules[$key]['name'], $txtdom);
  } else {
    $sub_heading =  modgettext::_($active_modules[$key]['name'], $txtdom);
  }
  $module_select[$sub_heading_id] = $sub_heading;
  $html_txt_arr[$sub_heading] =   "<div class=\"$sub_heading_id\"><table id=\"set_table\" border=\"0\" width=\"85%\"><tr>";
  $html_txt_arr[$sub_heading] .=  "<tr><td><strong>".$extension."</strong></td>";
  $html_txt_arr[$sub_heading] .=  "<td colspan=\"7\" align=\"center\"><strong>".$vmxlocator."</strong></td>";
  $html_txt_arr[$sub_heading] .=  "<td colspan=\"2\" align=\"center\"><strong>".$followme."</strong></td>";
  $html_txt_arr[$sub_heading] .=  "<td colspan=\"5\" align=\"center\"><strong>".$callstatus."</strong></td>";
  $html_txt_arr[$sub_heading] .=  "</tr><td>&nbsp;</td>";
  $html_txt_arr[$sub_heading] .=  "<td align=\"center\"><strong>".$status."</strong></td>";
  $html_txt_arr[$sub_heading] .=  "<td align=\"center\"><strong>"._('Busy')."</strong></td>";
  $html_txt_arr[$sub_heading] .=  "<td align=\"center\"><strong>"._('Unavail')."</strong></td>";
  $html_txt_arr[$sub_heading] .=  "<td align=\"center\"><strong>"._('Operator')."</strong></td>";
  $html_txt_arr[$sub_heading] .=  "<td align=\"center\"><strong>"._('Press 0')."</strong></td>";
  $html_txt_arr[$sub_heading] .=  "<td align=\"center\"><strong>"._('Press 1')."</strong></td>";
  $html_txt_arr[$sub_heading] .=  "<td align=\"center\"><strong>"._('Press 2')."</strong></td>";
  $html_txt_arr[$sub_heading] .=  "<td align=\"center\"><strong>FM</strong></td>";
  $html_txt_arr[$sub_heading] .=  "<td align=\"center\"><strong>FM-list</strong></td>";
  $html_txt_arr[$sub_heading] .=  "<td align=\"center\"><strong>CW</strong></td>";
  $html_txt_arr[$sub_heading] .=  "<td align=\"center\"><strong>DND</strong></td>";
  $html_txt_arr[$sub_heading] .=  "<td align=\"center\"><strong>CF</strong></td>";
  $html_txt_arr[$sub_heading] .=  "<td align=\"center\"><strong>CFB</strong></td>";
  $html_txt_arr[$sub_heading] .=  "<td align=\"center\"><strong>CFU</strong></td></tr>\n";

  foreach ($value as $exten => $item) {
    $vmxzero = "";
    $vmxbusy = "<img src=\"images/bullet.png\" alt=\"Off\" title=\"Off\"/>";
    $vmxunavail = "images/bullet.png\" alt=\"Off\" title=\"Off";
    $description = explode(":",$item['description'],2);
	//Hack for PHP 5.3 negative number key issue
	//https://bugs.php.net/bug.php?id=51008
	preg_match('/display=(\d+)&/i',$item['edit_url'],$matches);
	$exten = !empty($matches[1]) ? $matches[1] : $exten;
	//end hack
    $html_txt_arr[$sub_heading] .= "<tr><td><a href=\"".$item['edit_url']."\" class=\"info\">".$exten."<span>".(trim($description[1])==''?$exten:$description[1])."</span></a></td>";
    // Is VmX enabled, check only busy, if VmX is enabled, we have either "disabled", "enabled" or "blocked" in one of the states.
    if ( isset($ampuser['/AMPUSER/'.$exten.'/vmx/busy/state'])) {
      // We have one of the states, if it is "blocked", set proper icon
      if ($ampuser['/AMPUSER/'.$exten.'/vmx/busy/state'] == "blocked" ) {
        $vmxstate = "<img src=\"images/bullet.png\" alt=\"Off\" title=\"Off\"/>";
        //$vmxcolor = "\"RED\"";
        $vmxcolor = "GREY;\"";
      } else {
        $vmxstate = "<img src=\"images/bullet_checked.png\" alt=\"On\" title=\"On\"/>";
      }
      // Get the states of the VmX, we have either Busy or Unavailable enabled
      if ($ampuser['/AMPUSER/'.$exten.'/vmx/busy/state'] == "enabled") {
        $vmxbusy = "<img src=\"images/bullet_checked.png\" alt=\"On\" title=\"On\"/>";
      } else {
        $vmxbusy = "<img src=\"images/bullet.png\" alt=\"Off\" title=\"Off\"/>";
      }
      if ($ampuser['/AMPUSER/'.$exten.'/vmx/unavail/state'] == "enabled") {
        $vmxunavail = "images/bullet_checked.png\" alt=\"On\" title=\"On";
      } else {
      }
      // Do we have a VmX Busy/Unavail number for 0? If we have, then show it, otherwise display "Operator"
      if( isset($ampuser['/AMPUSER/'.$exten.'/vmx/busy/0/ext'])) {
        $vmxzero = $ampuser['/AMPUSER/'.$exten.'/vmx/busy/0/ext'];
        $vmxoperator = "images/bullet.png\" alt=\"Off\" title=\"Off";
      } else {
        $vmxzero = "Operator";
        $vmxoperator = "images/bullet_checked.png\" alt=\"On\" title=\"On";
      }
      // Do we have a VmX Busy/Unavail for 1? We only need to check Busy, as the number is the same for busy and unavail
      if( isset($ampuser['/AMPUSER/'.$exten.'/vmx/busy/1/ext'])) {
        $vmxone = $ampuser['/AMPUSER/'.$exten.'/vmx/busy/1/ext'];
      } else {
        $vmxone = "";
      }
      // Do we have a VmX Busy/Unavailable number for 2?
      if( isset($ampuser['/AMPUSER/'.$exten.'/vmx/busy/2/ext'])) {
        $vmxtwo = $ampuser['/AMPUSER/'.$exten.'/vmx/busy/2/ext'];
      } else {
        $vmxtwo = "";
      }
    } else {
      $vmxstate = "<img src=\"images/bullet.png\" alt=\"Off\" title=\"Off\">";
      $vmxoperator = "images/bullet.png\" alt=\"Off\" title=\"Off";
      $vmxone = $vmxtwo = "";
    };

    $html_txt_arr[$sub_heading] .= "<td align=\"center\">".$vmxstate."</td>";
    $html_txt_arr[$sub_heading] .= "<td align=\"center\">".$vmxbusy."</td>";
		$html_txt_arr[$sub_heading] .= "<td align=\"center\"><img src=\"".$vmxunavail."\"></td>";
		$html_txt_arr[$sub_heading] .= "<td align=\"center\"><img src=\"".$vmxoperator."\"></td>";
    $html_txt_arr[$sub_heading] .= "<td>".$vmxzero."</td>";
    $html_txt_arr[$sub_heading] .= "<td>".$vmxone."</td>";
		$html_txt_arr[$sub_heading] .= "<td>".$vmxtwo."</td>";
    // Has the extension followme enabled?
    $fm = "<img src=\"images/bullet.png\" alt=\"Off\" title=\"Off\"/>";
    $fmstate = false;
    if( isset($ampuser['/AMPUSER/'.$exten.'/followme/ddial'])) {
      if( $ampuser['/AMPUSER/'.$exten.'/followme/ddial'] == "DIRECT" || $ampuser['/AMPUSER/'.$exten.'/followme/ddial'] == "EXTENSION") {
				$fm = "<img src=\"images/bullet_checked.png\" alt=\"On\" title=\"On\"/>";
        $fmstate = true;
      } else {
        $fm = "<img src=\"images/bullet.png\" alt=\"Off\" title=\"Off\"/>";
        $fmstate = false;
      }
    }
    $html_txt_arr[$sub_heading] .= "<td align=\"center\">".$fm."</td>";
    // If follow-me is enabled, get the follow-me list
    if($fmstate) {
      $fmlist = str_replace("-","<br>",$ampuser['/AMPUSER/'.$exten.'/followme/grplist']);
    } else {
    $fmlist = "";
		}
    $html_txt_arr[$sub_heading] .= "<td>".$fmlist."</td>";
    $fmlist = ""; // Empty the list
    // Now get CW, CF, CFB and CFU if set
    if( isset($cwsetting['/CW/'.$exten]) && $cwsetting['/CW/'.$exten] == "ENABLED" ) {
      $cw = "<img src=\"images/bullet_checked.png\" alt=\"On\" title=\"On\"/>";
    } else {
      $cw = "<img src=\"images/bullet.png\" alt=\"Off\" title=\"Off\"/>";
    }
    if( isset($dndsetting['/DND/'.$exten]) && $dndsetting['/DND/'.$exten] == "YES" ) {
      $dnd = "<img src=\"images/bullet_checked.png\" alt=\"On\" title=\"On\"/>";
    } else {
      $dnd = "<img src=\"images/bullet.png\" alt=\"Off\" title=\"Off\"/>";
    }
    if( isset($cfsetting['/CF/'.$exten])) {
      $cf = $cfsetting['/CF/'.$exten];
    } else {
      $cf = "";
    }
		if( isset($cfbsetting['/CFB/'.$exten])) {
			$cfb = $cfbsetting['/CFB/'.$exten];
    } else {
      $cfb = "";
    }
    if( isset($cfusetting['/CFU/'.$exten])) {
      $cfu = $cfusetting['/CFU/'.$exten];
    } else {
      $cfu = "";
    }
    $html_txt_arr[$sub_heading] .= "<td align=\"center\">".$cw."</td>";
		$html_txt_arr[$sub_heading] .= "<td align=\"center\">".$dnd."</td>";
		$html_txt_arr[$sub_heading] .= "<td>".$cf."</td>";
    $html_txt_arr[$sub_heading] .= "<td>".$cfb."</td>";
    $html_txt_arr[$sub_heading] .= "<td>".$cfu."</td>";
    $html_txt_arr[$sub_heading] .= "</tr>\n";
  }
  $html_txt_arr[$sub_heading] .= "</table>";
}

function core_top($a, $b) {
        global $core_heading;

        if ($a == $core_heading) {
                return -1;
        } elseif ($b == $core_heading) {
                return 1;
        } elseif ($a != $b) {
                return $a < $b ? -1 : 1;
        } else {
                return 0;
        }
}

uksort($html_txt_arr, 'core_top');
if (!$quietmode) {
        //asort($module_select);
        uasort($module_select, 'core_top');
}

$html_txt_arr[$sub_heading] .= "</table></div>";
$html_txt .= implode("\n",$html_txt_arr);
echo $html_txt."</div>";
?>
