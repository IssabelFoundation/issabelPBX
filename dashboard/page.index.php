<?php /* $Id: page.parking.php 2243 2006-08-12 17:13:17Z p_lindheimer $ */

if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

if (!defined('DASHBOARD_ISSABELPBX_BRAND')) { 
	if (!empty($_SESSION['DASHBOARD_ISSABELPBX_BRAND'])) {
		define('DASHBOARD_ISSABELPBX_BRAND', $_SESSION['DASHBOARD_ISSABELPBX_BRAND']);
	} else {
		define('DASHBOARD_ISSABELPBX_BRAND', 'IssabelPBX');
	}
} else {
	$_SESSION['DASHBOARD_ISSABELPBX_BRAND'] = DASHBOARD_ISSABELPBX_BRAND;
}

$dashboard_debug = false;

$dispnum = 'sysinfo'; //used for switch on config.php
$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';

$quietmode = isset($_REQUEST['quietmode'])?$_REQUEST['quietmode']:'';
$info = isset($_REQUEST['info'])?$_REQUEST['info']:false;

$title=sprintf("%s: Sysinfo Info", DASHBOARD_ISSABELPBX_BRAND);
$message="System Info";

if (isset($_REQUEST['showall'])) {
	$_SESSION['syslog_showall'] = (bool)$_REQUEST['showall'];
}

//require_once('functions.inc.php');

define('BAR_WIDTH_LEFT', 400);
define('BAR_WIDTH_RIGHT', 200);

// AJAX update intervals (in seconds)
if (isset($amp_conf['DASHBOARD_STATS_UPDATE_TIME']) && $amp_conf['DASHBOARD_STATS_UPDATE_TIME'] && ctype_digit($amp_conf['DASHBOARD_STATS_UPDATE_TIME'])) {
	define('STATS_UPDATE_TIME', $amp_conf['DASHBOARD_STATS_UPDATE_TIME']);
} else {
	define('STATS_UPDATE_TIME', 6); // update interval for system information
}
if (isset($amp_conf['DASHBOARD_INFO_UPDATE_TIME']) && $amp_conf['DASHBOARD_INFO_UPDATE_TIME'] && ctype_digit($amp_conf['DASHBOARD_INFO_UPDATE_TIME'])) {
	define('INFO_UPDATE_TIME', $amp_conf['DASHBOARD_INFO_UPDATE_TIME']);
} else {
	define('INFO_UPDATE_TIME', 30); // update interval for system uptime information
}

/** draw_graph
 *  draw a bar graph
 *
 *  $text         Title of text
 *  $real_units   Units to display
 *  $val          Value to graph
 *  $total        Total of graph
 *  $classes      CSS classes to use based on value percent
 *  $show_percent If results should be shown as percent
 *  $total_width  Width of graph
 */

function draw_graph($text, $real_units, $val, $total = 100, $classes = null, $show_percent = true, $total_width = 200) {
	if ($classes == null) {
		$classes = array(
			0=>'graphok',
			70=>'graphwarn',
			90=>'grapherror',
		);
	}

	$chars_per_pixel = 7;
	if (strlen($text) * $chars_per_pixel > $total_width - 35) {
		$text_trimmed = substr($text,0, floor(($total_width - 35) / $chars_per_pixel)).'..';
	} else {
		$text_trimmed = $text;
	}

	$clean_val = preg_replace("/[^0-9\.]*/","",$val);

	if ($total == 0) {
		$percent = ($clean_val == 0) ? 0 : 100;
	} else {
		$percent = round($clean_val/$total*100);
	}
	
	$graph_class = false;
	foreach ($classes as $limit=>$class) {
		if (!$graph_class) {
			$graph_class = $class;
		}
		if ($limit <= $percent) {
			$graph_class = $class;
		} else {
			break;
		}
	}
	$width = $total_width * ($percent/100);
	if ($width > $total_width) { 
		$width = $total_width;
	}
	
	$tooltip = $text.": ".$val.$real_units." / ".$total.$real_units." (".$percent."%)";
	$display_value = ($show_percent ? $percent."%" : $val.$real_units); 
	
	$out = "<div class=\"databox graphbox\" style=\"width:".$total_width."px;\" title=\"".$tooltip."\">\n";
	$out .= " <div class=\"bargraph ".$graph_class."\" style=\"width:".$width."px;\"></div>\n";
	$out .= " <div class=\"dataname\">".$text_trimmed."</div>\n";
	$out .= " <div class=\"datavalue\">".$display_value."</div>\n";
	$out .= "</div>\n";
	
	return $out;
}

function draw_status_box($text, $status, $tooltip = false, $total_width = 200) {
	switch ($status) {
		case "ok":
			$status_text = _("OK");
			$class = "graphok";
		break;
		case "warn":
			$status_text = _("Warn");
			$class = "graphwarn";
		break;
		case "error":
			$status_text = _("ERROR");
			$class = "grapherror";
		break;
		case "disabled":
			$status_text = _("Disabled");
			$class = "";
		break;
	}
	if ($tooltip !== false) {
		$status_text = '<a href="#" title="'.$tooltip.'">'.$status_text.'</a>';
	}
	
	$out = "<div class=\"databox statusbox\" style=\"width:".$total_width."px;\">\n";
	$out .= " <div class=\"dataname\">".$text."</div>\n";
	$out .= " <div id=\"datavalue_".str_replace(" ","_",$text)."\" class=\"datavalue ".$class."\">".$status_text."</div>\n";
	$out .= "</div>\n";
	
	return $out;
}

function draw_box($text, $value, $total_width = 200) {
	$tooltip = $text.": ".$value;
	
	$out = "<div class=\"databox\" style=\"width:".$total_width."px;\">\n";
	$out .= " <div class=\"dataname\">".$text."</div>\n";
	$out .= " <div class=\"datavalue\"><a href=\"#\" title=\"".$tooltip."\">".$value."</a></div>\n";
	$out .= "</div>\n";
	
	return $out;
}

function time_string($seconds) {
    if ($seconds == 0) {
        return "0 "._("minutes");
    } elseif ($seconds < 60) {
        return "$seconds "._("seconds");
    }

    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;

    $hours = floor($minutes / 60);
    $minutes = $minutes % 60;

    $days = floor($hours / 24);
    $hours = $hours % 24;
	
	$weeks = floor($days / 7);
    $days = $days % 7;
	
	$output = array();
	if ($weeks) { 
		$output[] = $weeks." ".($weeks == 1 ? _("week") : _("weeks"));
	}
	if ($days) { 
		$output[] = $days." ".($days == 1 ? _("days") : _("days"));
	}
	if ($hours) { 
		$output[] = $hours." ".($hours == 1 ? _("hour") : _("hours"));
	}
	if ($minutes) { 
		$output[] = $minutes." ".($minutes == 1 ? _("minute") : _("minutes"));
	}
	
    return implode(", ",$output);
}

function show_sysstats() {
	global $sysinfo;
	$out = '';
	
	$out .= "<h3 class=\"ui-widget-header  ui-state-default ui-corner-all\">"._("System Statistics")."</h3>";
	$out .= "<h4>"._("Processor")."</h4>";
	$loadavg = $sysinfo->loadavg(true);
	$out .= draw_box(_("Load Average"), $loadavg['avg'][0]);
	$out .= draw_graph(_("CPU"), "", number_format($loadavg['cpupercent'],2), 100);
	
	$out .= "<h4>"._("Memory")."</h4>";
	$memory = $sysinfo->memory();
	$app_memory = isset($memory["ram"]["app"]) ? 
		$memory["ram"]["app"] : 
		$memory["ram"]["total"] - $memory["ram"]["t_free"] - $memory['ram']['cached'] - $memory['ram']['buffers'];

	$out .= draw_graph(_("App Memory"), "MB", number_format($app_memory/1024,2), $memory["ram"]["total"]/1024);
	$out .= draw_graph(_("Swap"), "MB", number_format(($memory["swap"]["total"]-$memory["swap"]["free"])/1024,2), $memory["swap"]["total"]/1024);
	
	$out .= "<h4>"._("Disks")."</h4>";
	foreach ($sysinfo->filesystems() as $fs) {
		$out .= draw_graph($fs["mount"], "GB", number_format($fs["used"]/1024/1024, 2,".",""), number_format($fs["size"]/1024/1024,2,".",""), strpos( $fs["options"],"ro" )!==false ? array(0=>"graphok"):null);
	}
	
	$out .= "<h4>"._("Networks")."</h4>";
	foreach ($sysinfo->network() as $net_name=>$net) {
		$net_name = trim($net_name);
		if ($net_name == 'lo' || $net_name == 'sit0' || preg_match('/w.g./',$net_name)) continue;
		
		$tx = new average_rate_calculator($_SESSION["netstats"][$net_name]["tx"], 10); // 30s max age
		$rx = new average_rate_calculator($_SESSION["netstats"][$net_name]["rx"], 10); // 30s max age
		
		$rx->add( $net["rx_bytes"] );
		$tx->add( $net["tx_bytes"] );
		
		$out .= draw_box($net_name." "._("receive"), number_format($rx->average()/1000,2)." KB/s");
		$out .= draw_box($net_name." "._("transmit"), number_format($tx->average()/1000,2)." KB/s");
	}
	return $out;
}

function show_aststats() {
	global $amp_conf;
	global $astinfo;
	global $db;
	$out = '';
	
	$channels = $astinfo->get_channel_totals();
	// figure out max_calls
	
	// guess at the max calls: number of users
	if (!isset($_SESSION["calculated_max_calls"])) {
		// set max calls to either MAXCALLS in amportal.conf, or the number of users in the system
		if (isset($amp_conf['MAXCALLS'])) {
			$_SESSION["calculated_max_calls"] = $amp_conf["MAXCALLS"];
		} else if (function_exists('core_users_list')) {
			$_SESSION["calculated_max_calls"] = count(core_users_list());
		} else {
			$_SESSION["calculated_max_calls"] = 1;
		}
	}
	// we currently see more calls than we guessed, increase it
	if ($channels['total_calls'] > $_SESSION["calculated_max_calls"]) {
		$_SESSION["calculated_max_calls"] = $channels['total_calls'];
	}
	$max_calls = $_SESSION["calculated_max_calls"];
	
	$classes = array(0=>'graphok');
	$max_chans = $max_calls * 2;
	
	$out .= "<h3 class=\"ui-widget-header  ui-state-default ui-corner-all\">".sprintf(_("%s Statistics"), DASHBOARD_ISSABELPBX_BRAND)."</h3>";
	$out .= draw_graph(_('Total active calls'), '', $channels['total_calls'], $max_calls, $classes , false, BAR_WIDTH_LEFT);
	$out .= draw_graph(_('Internal calls'), '', $channels['internal_calls'], $max_calls, $classes , false, BAR_WIDTH_LEFT);
	$out .= draw_graph(_('External calls'), '', $channels['external_calls'], $max_calls, $classes , false, BAR_WIDTH_LEFT);
	$out .= draw_graph(_('Total active channels'), '', $channels['total_channels'], $max_chans, $classes , false, BAR_WIDTH_LEFT);
	
	$out .= "<h4>".sprintf(_("%s Connections"), DASHBOARD_ISSABELPBX_BRAND)."</h4>";
	
	/* This is generally very bad style, and we should look at adding this to core_devices_list or another core
	 * function. However, since this is in Ajax lite weight code, it is currently the cleanest way to get the sip and iax2
	 * devices in a hash format that we would like to pass to the class
	 */
	$sql = "SELECT `id` FROM `devices` WHERE `tech` IN ('sip', 'iax2')";
	$devices = $db->getCol($sql);
	if(DB::IsError($devices)) {
		$devices = false;
	} else {
		$devices = array_flip($devices);
	}

	$conns = $astinfo->get_connections( $devices );

	if ($conns['users_total'] > 0) {
		$out .= draw_graph(_('IP Phones Online'), '', $conns['users_online'], $conns['users_total'], $classes, false, BAR_WIDTH_LEFT);
	}
	if ($conns['trunks_total'] > 0) {
		$out .= draw_graph(_('IP Trunks Online'), '', $conns['trunks_online'], $conns['trunks_total'], $classes, false, BAR_WIDTH_LEFT);
	}
	if ($conns['registrations_total'] > 0) {
		$out .= draw_graph(_('IP Trunk Registrations'), '', $conns['registrations_online'], $conns['registrations_total'], $classes, false, BAR_WIDTH_LEFT);
	}

	return $out;
}

function show_sysinfo() {
	global $sysinfo;
	global $astinfo;
	$out = "<h3 class=\"ui-widget-header  ui-state-default ui-corner-all\">"._("Uptime")."</h3><br />";
	$out .= '<table summary="'._('System Information Table').'">';
	/*
	$out .= '<tr><th>Distro:</th><td>'.$sysinfo->distro().'</td></tr>';
	$out .= '<tr><th>Kernel:</th><td>'.$sysinfo->kernel().'</td></tr>';
	$cpu = $sysinfo->cpu_info();
	$out .= '<tr><th>CPU:</th><td>'.$cpu['model'].' '.$cpu['cpuspeed'].'</td></tr>';
	*/
	
	$out .= '<tr><th>'._('System Uptime').':</th><td>'.time_string($sysinfo->uptime()).'</td></tr>';
	$ast_uptime = $astinfo->get_uptime();
	if (empty($ast_uptime['system'])) {
		$ast_uptime['system'] = time_string(0);
	}
	if (empty($ast_uptime['reload'])) {
		$ast_uptime['reload'] = time_string(0);
	}
	$out .= '<tr><th>'._('Asterisk Uptime').':</th><td>'.$ast_uptime['system'].'</td></tr>';
	$out .= '<tr><th>'._('Last Reload').':</th><td>'.$ast_uptime['reload'].'</td></tr>';
	
	$out .= '</table>';
	return $out;
}

function show_procinfo() {
	global $procinfo;
	global $astinfo;
	global $amp_conf;
	$out = '';
	
	$out .= "<h3 class=\"ui-widget-header  ui-state-default ui-corner-all\">"._("Server Status")."</h3>";
	// asterisk
	if ($astver = $astinfo->check_asterisk()) {
		$out .= draw_status_box(_("Asterisk"), "ok", sprintf(_('Asterisk is running: %s'),$astver));
	} else {
		$out .= draw_status_box(_("Asterisk"), "error", _('Asterisk is not running, this is a critical service!'));
	}
	
	// asterisk proxy (optionally)
	if ($amp_conf['ASTMANAGERPROXYPORT']) {
		if ($procinfo->check_port($amp_conf['ASTMANAGERPROXYPORT'])) {
			$out .= draw_status_box(_("Manager Proxy"), "ok", _('Asterisk Manager Proxy is running'));
		} else {
			$out .= draw_status_box(_("Manager Proxy"), "warn", sprintf(_('Asterisk Manager Proxy is not running, %s will fall back to using Asterisk directly, which may result in poor performance'), DASHBOARD_ISSABELPBX_BRAND));
		}		
	}
	
	// fop
	/* FOP has been removed (optional un-supported module currenlty)
	$warn = draw_status_box(_("Op Panel"), "warn", _('FOP Operator Panel Server is not running, you will not be able to use the operator panel, but the system will run fine without it.'));
	if($amp_conf['FOPDISABLE']) { // FOP is disabled, display that on the dashboard
    $out .= draw_status_box(_("Op Panel"), "disabled", _('FOP Operator Panel is disabled in Advanced Settings'));
  } else {
    if (!$amp_conf['FOPRUN']) { 
      $out .= $warn; // if FOPRUN is false, display warning on the dashboard
    } elseif ($procinfo->check_fop_server()) { // if FOPRUN is true, then check the fop tcp port, if OK display that on dashboard
      $out .= draw_status_box(_("Op Panel"), "ok", _('FOP Operator Panel Server is running'));
    } else { // check_fop_server returned an error, display warning
			$out .= $warn;
    }
  }
	 */

	// mysql
	if ($amp_conf['AMPDBENGINE'] == "mysql") {
		/* this is silly- it's always running, if the web interface loads
		if ($procinfo->check_mysql($amp_conf['AMPDBHOST'])) {
			$out .= draw_status_box(_("MySQL"), "ok", _('MySQL Server is running'));
		} else {
			$out .= draw_status_box(_("MySQL"), "error", _('MySQL Server is not running, this is a critical service for the web interface and call logs!'));
		}
		*/
		$out .= draw_status_box(_("MySQL"), "ok", _('MySQL Server is running'));
	}
	
	// web always runs .. HOWEVER, we can turn it off with dhtml
	$out .= draw_status_box(_("Web Server"), "ok", _('Web Server is running'));
	
	// ssh	
	$ssh_port = (isset($amp_conf['SSHPORT']) && (ctype_digit($amp_conf['SSHPORT']) || is_int($amp_conf['SSHPORT'])) && ($amp_conf['SSHPORT'] > 0) && ($amp_conf['SSHPORT'] < 65536))?$amp_conf['SSHPORT']:22;
	if ($procinfo->check_port($ssh_port)) {
		$out .= draw_status_box(_("SSH Server"), "ok", _('SSH Server is running'));
	} else {
		$out .= draw_status_box(_("SSH Server"), "warn", _('SSH Server is not running, you will not be able to connect to the system console remotely'));
	}
	return $out;
}

function show_syslog(&$md5_checksum) {
	global $db;
	$out = '';
	$checksum = '';

	// notify_classes are also used as the image names
	$notify_classes = array( 
		NOTIFICATION_TYPE_CRITICAL => 'notify_critical',
		NOTIFICATION_TYPE_SECURITY => 'notify_security',
		NOTIFICATION_TYPE_UPDATE => 'notify_update',
		NOTIFICATION_TYPE_ERROR => 'notify_error',
		NOTIFICATION_TYPE_WARNING => 'notify_warning',
		NOTIFICATION_TYPE_NOTICE => 'notify_notice',
	);
	$notify_descriptions = array(
		NOTIFICATION_TYPE_CRITICAL => _('Critical Error'),
		NOTIFICATION_TYPE_SECURITY => _('Security Update'),
		NOTIFICATION_TYPE_UPDATE => _('Update'),
		NOTIFICATION_TYPE_ERROR => _('Error'),
		NOTIFICATION_TYPE_WARNING => _('Warning'),
		NOTIFICATION_TYPE_NOTICE => _('Notice'),
	);
	
	$notify =& notifications::create($db);
	
	$showall = (isset($_SESSION['syslog_showall']) ? $_SESSION['syslog_showall'] : false);
	
	$items = $notify->list_all($showall);

	$out .= "<h3 class=\"ui-widget-header  ui-state-default ui-state-default ui-corner-all\">".sprintf(_("%s Notices"), DASHBOARD_ISSABELPBX_BRAND)."</h3>";
	
	if (count($items)) {
		$out .= '<ul>';
		foreach ($items as $item) {
			$checksum .= $item['module'].$item['id']; // checksum, so it is only updated on the page if this has changed
			
			$domid = "notify_item_".str_replace(' ','_',$item['module']).'_'.str_replace(' ','_',$item['id']);
			
			$out .= "\n";
			$out .= '<li id="'.$domid.'" ';
			if (isset($notify_classes[$item['level']])) {
				$out .= ' class="'.$notify_classes[$item['level']].'"';
			}
			$out .= '><div>';
			
			$out .= '<div class="syslog_text">';
			$out .= '<h4>';
			$out .= '<span><img src="images/'.$notify_classes[$item['level']].'.png" alt="'.$notify_descriptions[$item['level']].'" title="'.$notify_descriptions[$item['level']].'" width="16" height="16" border="0" />&nbsp;';
			$out .= $item['display_text'].'</span>';
			$out .= '</h4>';
			$out .= "\n";
			$out .= '<div class="notification_buttons">';
			if (isset($item['candelete']) && $item['candelete']) {
				$out .= '<a class="notify_ignore_btn" title="'._('Delete this').'" '.
				        'onclick="delete_notification(\''.$domid.'\', \''.$item['module'].'\', \''.$item['id'].'\');">'.
				        '<img src="images/cancel.png" width="16" height="16" border="0" alt="'._('Delete this').'" /></a>';
			}
			if (!$item['reset']) {
				$out .= '<a class="notify_ignore_btn" title="'._('Ignore this').'" '.
				        'onclick="hide_notification(\''.$domid.'\', \''.$item['module'].'\', \''.$item['id'].'\');">'.
				        '<img src="images/notify_delete.png" width="16" height="16" border="0" alt="'._('Ignore this').'" /></a>';
			}
			$out .= '</div>';
			$out .= '</div>';
			$out .= "\n";
			$out .= '<div class="syslog_detail">';
			$out .= nl2br($item['extended_text']);
			$out .= '<br/><span>'.sprintf(_('Added %s ago'), time_string(time() - $item['timestamp'])).'<br/>'.
			        '('.$item['module'].'.'.$item['id'].')</span>';
			$out .= '</div>';
			
			$out .= '</div></li>';
		}
		$out .= '</ul>';
	} else {
		if ($showall) {
			$out .= _('No notifications');
		} else {
			$out .= _('No new notifications');
		}
	}
	
	$md5_checksum = md5($checksum);
	
	$out .= '<div id="syslog_button">';
	
	if ($showall) {
		$out .= '<a href="#" onclick="changeSyslog(0);">'._('show new').'</a>'; 
	} else {
	  $out .= '<a href="#" onclick="changeSyslog(1);">'._('show all').'</a>'; 
	}
	$out .= '</div>';
	return $out;
}

function do_syslog_ack() {	
	global $db;
	$notify =& notifications::create($db);
	
	if (isset($_REQUEST['module']) && $_REQUEST['id']) {
		$notify->reset($_REQUEST['module'], $_REQUEST['id']);
	}
}
function do_syslog_delete() {	
	global $db;
	$notify =& notifications::create($db);
	
	if (isset($_REQUEST['module']) && $_REQUEST['id']) {
	var_dump($_REQUEST);
		$notify->safe_delete($_REQUEST['module'], $_REQUEST['id']);
	}
}

/********************************************************************************************/


define("IN_PHPSYSINFO", "1");
define("APP_ROOT", dirname(__FILE__).'/phpsysinfo');
include APP_ROOT."/common_functions.php";
include APP_ROOT."/class.".PHP_OS.".inc.php";
include dirname(__FILE__)."/class.astinfo.php";
include dirname(__FILE__)."/class.average_rate_calculator.php";
include dirname(__FILE__)."/class.procinfo.php";
include dirname(__FILE__)."/class.error.inc.php";

$error = new Error;


$sysinfo = new sysinfo;
$astinfo = new astinfo($astman);
$procinfo = new procinfo;


if (!$quietmode) {
	?>
	
	<script type="text/javascript">
	$(document).ready(function(){
    $.ajaxSetup({
      timeout:10000
    });
		scheduleInfoUpdate();
		scheduleStatsUpdate();
		
		makeSyslogClickable();
	});
	
	function makeSyslogClickable() {
		$('#syslog h4 span').click(function() {
			$(this).parent().parent().next('div').slideToggle('fast');
		});
	}
	
	var syslog_md5;
	var webserver_fail = 0;
	var info_timer = null;
	var stats_timer = null;

	function updateFailed(reqObj, status) {
		// stop updating 
		clearTimeout(stats_timer);
		stats_timer = null;
		clearTimeout(info_timer);
		info_timer = null;

		webserver_fail += 1;
		webobj = $('#datavalue_Web_Server')

		if (webserver_fail == 1) {
			webobj.text("Timeout");
			webobj.removeClass("graphok");
			webobj.addClass("graphwarn");	
		} else {
			webobj.text("ERROR");
			webobj.removeClass("graphok");
			webobj.removeClass("graphwarn");
			webobj.addClass("grapherror");
		}
		scheduleInfoUpdate();
	}


	function updateInfo() {
		$.ajax({
			type: 'GET',
			url: "<?php echo $_SERVER["PHP_SELF"]; ?>?type=tool&display=<?php echo $module_page; ?>&quietmode=1&info=info&restrictmods=core/dashboard", 
			dataType: 'json',
			success: function(data) {
				$('#procinfo').html(data.procinfo);
				$('#sysinfo').html(data.sysinfo);
				// only update syslog div if the md5 has changed
				if (syslog_md5 != data.syslog_md5) {
					$('#syslog').html(data.syslog);
					makeSyslogClickable();
					syslog_md5 = data.syslog_md5;
				}

				// webserver is ok
				webserver_fail = 0;

				scheduleInfoUpdate();
				if (stats_timer == null) {
					// restart stats updates
					scheduleStatsUpdate();
				}
			},
			error: updateFailed
		});
	}
	function scheduleInfoUpdate() {
		info_timer = setTimeout('updateInfo();',<?php echo INFO_UPDATE_TIME; ?>000);
	}
	
	
	function updateStats() {
		$.ajax({
			type: 'GET',
			url: "<?php echo $_SERVER["PHP_SELF"]; ?>?type=tool&display=<?php echo $module_page; ?>&quietmode=1&info=stats&restrictmods=core/dashboard", 
			dataType: 'json',
			success: function(data) {
				$('#sysstats').html(data.sysstats);
				$('#aststats').html(data.aststats);
				scheduleStatsUpdate();
			},
			error: updateFailed
		});
	}
	function scheduleStatsUpdate() {
		stats_timer = setTimeout('updateStats();',<?php echo STATS_UPDATE_TIME; ?>000);
	}
	
	
	function changeSyslog(showall) {
		$('#syslog_button').text('<?php echo _('loading...'); ?>');
		$('#syslog').load("<?php echo $_SERVER["PHP_SELF"]; ?>?type=tool&display=<?php echo $module_page; ?>&quietmode=1&restrictmods=core/dashboard&info=syslog&showall="+showall,{}, function() {
			makeSyslogClickable();
		});
	}

	function hide_notification(domid, module, id) {
		$('#'+domid).fadeOut('slow');
		$.post('config.php', {display:'<?php echo $module_page; ?>', quietmode:1, info:'syslog_ack', module:module, id:id, restrictmods:'core/dashboard'});
	}
	function delete_notification(domid, module, id) {
		$('#'+domid).fadeOut('slow');
		$.post('config.php', {display:'<?php echo $module_page; ?>', quietmode:1, info:'syslog_delete', module:module, id:id, restrictmods:'core/dashboard'});
	}
	</script>

	<h2><?php echo sprintf(_("%s System Status"), DASHBOARD_ISSABELPBX_BRAND);?></h2>
	<div id="dashboard">
	<?php
	echo '<div id="sysinfo-left">';
	
	// regular page
	echo '<div id="syslog" class="infobox ui-widget-content  ui-corner-all">';
	echo show_syslog($syslog_md5);
	// syslog_md5 is used by javascript updateInfo() to determine if the syslog div contents have changed
	echo '<script type="text/javascript"> syslog_md5 = "'.$syslog_md5.'"; </script>';
	//echo "log goes here<br/><br/><br/>";
	echo '</div>';
	
	echo '<div id="aststats" class="infobox ui-widget-content  ui-corner-all">';
	echo show_aststats();
	echo '</div>';
	
	echo '<div id="sysinfo" class="infobox ui-widget-content  ui-corner-all">';
	echo show_sysinfo();
	echo '</div>';
	
	
	echo '</div><div id="sysinfo-right">';
	
	echo '<div id="sysstats" class="infobox ui-widget-content  ui-corner-all">';
	echo show_sysstats();
	echo '</div>';
	
	echo '<div id="procinfo" class="infobox ui-widget-content  ui-corner-all">';
	echo show_procinfo();
	echo '</div>';
	
	//echo '<div style="clear:both;"></div>';
	echo '</div></div>'; // #sysinfo-right, #dashboard
	echo '<div id="sysinfo-bot">&nbsp</div>';

	if($dashboard_debug && $error->ErrorsExist()) {
		$fh = fopen($amp_conf['ASTLOGDIR']."/dashboard-error.log","a");
		fwrite($fh, $error->ErrorsAsText());
		fclose($fh);
	}           

} else {
	// Handle AJAX updates
	
	switch ($info) {
		case "sysstats":
			echo show_sysstats();
		break;
		case "aststats":
			echo show_aststats();
		break;
		case "procinfo":
			echo show_procinfo();
		break;
		case 'sysinfo':
			echo show_sysinfo();
		break;
		case 'syslog':
			echo show_syslog($syslog_md5);	
			// syslog_md5 is used by javascript updateInfo() to determine if the syslog div contents have changed
			echo '<script type="text/javascript"> syslog_md5 = "'.$syslog_md5.'"; </script>';
		break;
		case 'syslog_ack':
			do_syslog_ack();
		break;
		case 'syslog_delete':
			do_syslog_delete();
		break;
		
		case 'info':
			header("Content-type: application/json"); 
			echo json_encode(
				array(
					'procinfo'=>show_procinfo(),
					'sysinfo'=>show_sysinfo(),
					'syslog'=>show_syslog($syslog_md5),
					'syslog_md5'=>$syslog_md5,
				)
			);
		break;
		case 'stats':
			header("Content-type: application/json"); 
			echo json_encode(
				array(
					'sysstats'=>show_sysstats(),
					'aststats'=>show_aststats(),
				)
			);
		break;
		case 'all':
			header("Content-type: application/json"); 
			echo json_encode(
				array(
					'sysstats'=>show_sysstats(),
					'aststats'=>show_aststats(),
					'procinfo'=>show_procinfo(),
					'sysinfo'=>show_sysinfo(),
					'syslog'=>show_syslog(),
				)
			);
		break;
	}
}

?>
