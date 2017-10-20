<?php
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Portions Copyright (C) 2011 Igor Okunev
//  Portions Copyright (C) 2011 Mikael Carlsson
//	Copyright 2013 Schmooze Com Inc.
//
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $amp_conf, $db;
// Are a crypt password specified? If not, use the supplied.
$REC_CRYPT_PASSWORD = (isset($amp_conf['AMPPLAYKEY']) && trim($amp_conf['AMPPLAYKEY']) != "")?trim($amp_conf['AMPPLAYKEY']):'TheWindCriesMary';
$dispnum = "cdr";
$db_result_limit = 100;

// Check if cdr database and/or table is set, if not, use our default settings
$db_name = !empty($amp_conf['CDRDBNAME'])?$amp_conf['CDRDBNAME']:"asteriskcdrdb";
$db_table_name = !empty($amp_conf['CDRDBTABLENAME'])?$amp_conf['CDRDBTABLENAME']:"cdr";
$system_monitor_dir = isset($amp_conf['ASTSPOOLDIR'])?$amp_conf['ASTSPOOLDIR']."/monitor":"/var/spool/asterisk/monitor";

// if CDRDBHOST and CDRDBTYPE are not empty then we assume an external connection and don't use the default connection
//
if (!empty($amp_conf["CDRDBHOST"]) && !empty($amp_conf["CDRDBTYPE"])) {
	$db_hash = array('mysql' => 'mysql', 'postgres' => 'pgsql');
	$db_type = $db_hash[$amp_conf["CDRDBTYPE"]];
	$db_host = $amp_conf["CDRDBHOST"];
	$db_port = empty($amp_conf["CDRDBPORT"]) ? '' :  ':' . $amp_conf["CDRDBPORT"];
	$db_user = empty($amp_conf["CDRDBUSER"]) ? $amp_conf["AMPDBUSER"] : $amp_conf["CDRDBUSER"];
	$db_pass = empty($amp_conf["CDRDBPASS"]) ? $amp_conf["AMPDBPASS"] : $amp_conf["CDRDBPASS"];
	$datasource = $db_type . '://' . $db_user . ':' . $db_pass . '@' . $db_host . $db_port . '/' . $db_name;
	$dbcdr = DB::connect($datasource); // attempt connection
	if(DB::isError($dbcdr)) {
		die_issabelpbx($dbcdr->getDebugInfo());
	}
} else {
	$dbcdr = $db;
}

// For use in encrypt-decrypt of path and filename for the recordings
include_once("crypt.php");
switch ($action) {
	case 'cdr_play':
	case 'cdr_audio':
  	include_once("$action.php");
		exit;
		break;
	case 'download_audio':
			$file = $db->getOne('SELECT recordingfile FROM ' . $db_name.'.'.$db_table_name . ' WHERE uniqueid = ?',
			 array($_REQUEST['cdr_file']));
			db_e($file);
			if ($file) {
				$rec_parts = explode('-',$file);
				$fyear = substr($rec_parts[3],0,4);
				$fmonth = substr($rec_parts[3],4,2);
				$fday = substr($rec_parts[3],6,2);
				$monitor_base = $amp_conf['MIXMON_DIR'] ? $amp_conf['MIXMON_DIR'] : $amp_conf['ASTSPOOLDIR'] . '/monitor'; 
				$file = "$monitor_base/$fyear/$fmonth/$fday/" . $file;
				download_file($file, '', '', true);
			}
			exit;
		break;
	default:
		break;
}

// ISSABELPBX-8845
foreach ($_POST as $k => $v) {
	$_POST[$k] = preg_replace('/;/', ' ', $dbcdr->escapeSimple($v));
}

$h_step = 30;
?>
	<h3><?php echo _('CDR Reports'); ?></h3><hr>
	<div id="maincdr">
	<table class="cdr">
	<tr><td>
		<form method="post" enctype="application/x-www-form-urlencoded">
		<fieldset>
		<legend class="title"><?php echo _("Call Detail Record Search")?></legend>
			<table width="100%">
			<tr>
				<th><?php echo _("Order By")?></th>
				<th><?php echo _("Search conditions")?></th>
				<th>&nbsp;</th>
			</tr>
			<tr>
				<?php $calldate_tooltip = _("Select time span for your report. You can select Date, Month, Year, Hour and Minute to narrow your search");?>
				<td><input <?php if (empty($_POST['order']) || $_POST['order'] == 'calldate') { echo 'checked="checked"'; } ?> type="radio" name="order" value="calldate" />&nbsp;<?php echo "<a href=\"#\" class=\"info\">"._("Call Date")."<span>".$calldate_tooltip."</span></a>"?>:</td>
				<td><?php echo _("From")?>:
				<input type="text" name="startday" id="startday" size="2" maxlength="2" value="<?php if (isset($_POST['startday'])) { echo htmlspecialchars($_POST['startday']); } else { echo '01'; } ?>" />
				<select name="startmonth" id="startmonth">
<?php
				$months = array('01' => _('January'), '02' => _('February'), '03' => _('March'), '04' => _('April'), '05' => _('May'), '06' => _('June'), '07' => _('July'), '08' => _('August'), '09' => _('September'), '10' => _('October'), '11' => _('November'), '12' => _('December'));
				foreach ($months as $i => $month) {
					if ((empty($_POST['startmonth']) && date('m') == $i) || (isset($_POST['startmonth']) && $_POST['startmonth'] == $i)) {
						echo "<option value=\"$i\" selected=\"selected\">$month</option>\n";
					} else {
						echo "<option value=\"$i\">$month</option>\n";
					}
				}
?>
				</select>
				<select name="startyear" id="startyear">
<?php
				for ( $i = 2000; $i <= date('Y'); $i++) {
					if ((empty($_POST['startyear']) && date('Y') == $i) || (isset($_POST['startyear']) && $_POST['startyear'] == $i)) {
						echo "<option value=\"$i\" selected=\"selected\">$i</option>\n";
					} else {
						echo "<option value=\"$i\">$i</option>\n";
					}
				}
?>
				</select>
				<input type="text" name="starthour" id="starthour" size="2" maxlength="2" value="<?php if (isset($_POST['starthour'])) { echo htmlspecialchars($_POST['starthour']); } else { echo '00'; } ?>" />:
				<input type="text" name="startmin" id="startmin" size="2" maxlength="2" value="<?php if (isset($_POST['startmin'])) { echo htmlspecialchars($_POST['startmin']); } else { echo '00'; } ?>" /><?php echo _("To")?>:
				<input type="text" name="endday" id="endday" size="2" maxlength="2" value="<?php if (isset($_POST['endday'])) { echo htmlspecialchars($_POST['endday']); } else { echo '31'; } ?>" />
				<select name="endmonth" id="endmonth">
<?php
				foreach ($months as $i => $month) {
				if ((empty($_POST['endmonth']) && date('m') == $i) || (isset($_POST['endmonth']) && $_POST['endmonth'] == $i)) {
	                echo "<option value=\"$i\" selected=\"selected\">$month</option>\n";
					} else {
	               		echo "<option value=\"$i\">$month</option>\n";
	        		}
				}
?>
</select>
<select name="endyear" id="endyear">
<?php
for ( $i = 2000; $i <= date('Y'); $i++) {
        if ((empty($_POST['endyear']) && date('Y') == $i) || (isset($_POST['endyear']) && $_POST['endyear'] == $i)) {
                echo "        <option value=\"$i\" selected=\"selected\">$i</option>\n";
        } else {
                echo "        <option value=\"$i\">$i</option>\n";
        }
}
?>
</select>
	<input type="text" name="endhour" id="endhour" size="2" maxlength="2" value="<?php if (isset($_POST['endhour'])) { echo htmlspecialchars($_POST['endhour']); } else { echo '23'; } ?>" />:
	<input type="text" name="endmin" id="endmin" size="2" maxlength="2" value="<?php if (isset($_POST['endmin'])) { echo htmlspecialchars($_POST['endmin']); } else { echo '59'; } ?>" />
	</td>
<td rowspan="10" valign='top' align='right'>
<fieldset>
<legend class="title"><?php echo _("Extra options")?></legend>
<table>
<tr>
<td><?php echo _("Report type")?> : </td>
<td>
<input <?php if ( (empty($_POST['need_html']) && empty($_POST['need_chart']) && empty($_POST['need_chart_cc']) && empty($_POST['need_csv'])) || ( ! empty($_POST['need_html']) &&  $_POST['need_html'] == 'true' ) ) { echo 'checked="checked"'; } ?> type="checkbox" name="need_html" value="true" /> : <?php echo _("CDR search")?><br />
<input <?php if ( ! empty($_POST['need_csv']) && $_POST['need_csv'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="need_csv" value="true" /> : <?php echo _("CSV file")?><br/>
<input <?php if ( ! empty($_POST['need_chart']) && $_POST['need_chart'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="need_chart" value="true" /> : <?php echo _("Call Graph")?><br />
<!--
<input <?php if ( ! empty($_POST['need_chart_cc']) && $_POST['need_chart_cc'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="need_chart_cc" value="true" /> : <?php echo _("Concurrent Calls")?><br />
-->
</td>
</tr>
<tr>
<td><label for="Result limit"><?php echo _("Result limit")?> : </label></td>
<td>
<input value="<?php
if (isset($_POST['limit']) ) {
        echo htmlspecialchars($_POST['limit']);
} else {
        echo $db_result_limit;
} ?>" name="limit" size="6" />
</td>
</tr>
</table>
</fieldset>
</td>
</tr>
<tr>
<?php $cnum_tooltip = _("Search for calls based on CallerID Number. You can enter multiple numbers separated by a comma. This field support Asterisk regular expression. Example<br>");?>
<?php $cnum_tooltip .= _("<b>_2XXN, _562., _.0075</b> = search for any match of these numbers<br>");?>
<?php $cnum_tooltip .= _("<b>_!2XXN, _562., _.0075</b> = Search for any match <b>except</b> for these numbers");?>
<?php $cnum_tooltip .= _("<br>Asterisk pattern matching<br>");?>
<?php $cnum_tooltip .= _("<b>X</b> = matches any digit from 0-9<br>");?>
<?php $cnum_tooltip .= _("<b>Z</b> = matches any digit from 1-9<br>");?>
<?php $cnum_tooltip .= _("<b>N</b> = matches any digit from 2-9<br>");?>
<?php $cnum_tooltip .= _("<b>[1237-9]</b> = matches any digit or letter in the brackets<br>(in this example, 1,2,3,7,8,9)<br>");?>
<?php $cnum_tooltip .= _("<b>.</b> = wildcard, matches one or more characters<br>");?>
<td><input <?php if (isset($_POST['order']) && $_POST['order'] == 'cnum') { echo 'checked="checked"'; } ?> type="radio" name="order" value="cnum" />&nbsp;<label for="cnum"><?php echo "<a href=\"#\" class=\"info\">"._("CallerID Number")."<span>$cnum_tooltip</span></a>"?>:</label></td>
<td><input type="text" name="cnum" id="cnum" value="<?php if (isset($_POST['cnum'])) { echo htmlspecialchars($_POST['cnum']); } ?>" />
<?php echo _("Not")?>:<input <?php if ( isset($_POST['cnum_neg'] ) && $_POST['cnum_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="cnum_neg" value="true" />
<?php echo _("Begins With")?>:<input <?php if (empty($_POST['cnum_mod']) || $_POST['cnum_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="cnum_mod" value="begins_with" />
<?php echo _("Contains")?>:<input <?php if (isset($_POST['cnum_mod']) && $_POST['cnum_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="cnum_mod" value="contains" />
<?php echo _("Ends With")?>:<input <?php if (isset($_POST['cnum_mod']) && $_POST['cnum_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="cnum_mod" value="ends_with" />
<?php echo _("Exactly")?>:<input <?php if (isset($_POST['cnum_mod']) && $_POST['cnum_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="cnum_mod" value="exact" />
</td>
</tr>

<tr>
<?php $cnam_tooltip = _("Select CallerID Name to search for.");?>
<td><input <?php if (isset($_POST['order']) && $_POST['order'] == 'cnam') { echo 'checked="checked"'; } ?> type="radio" name="order" value="cnam" />&nbsp;<label for="cnam"><?php echo "<a href=\"#\" class=\"info\">"._("CallerID Name")."<span>$cnam_tooltip</span></a>"?>:</label></td>
<td><input type="text" name="cnam" id="cnam" value="<?php if (isset($_POST['cnam'])) { echo htmlspecialchars($_POST['cnam']); } ?>" />
<?php echo _("Not")?>:<input <?php if ( isset($_POST['cnam_neg'] ) && $_POST['cnam_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="cnam_neg" value="true" />
<?php echo _("Begins With")?>:<input <?php if (empty($_POST['cnam_mod']) || $_POST['cnam_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="cnam_mod" value="begins_with" />
<?php echo _("Contains")?>:<input <?php if (isset($_POST['cnam_mod']) && $_POST['cnam_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="cnam_mod" value="contains" />
<?php echo _("Ends With")?>:<input <?php if (isset($_POST['cnam_mod']) && $_POST['cnam_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="cnam_mod" value="ends_with" />
<?php echo _("Exactly")?>:<input <?php if (isset($_POST['cnam_mod']) && $_POST['cnam_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="cnam_mod" value="exact" />
</td>
</tr>

<tr>
<?php $obcnum_tooltip = _("Search for calls based on outbound CID used. You can enter multiple numbers separated by a comma. This field support Asterisk regular expression. Example<br>");?>
<?php $obcnum_tooltip .= _("<b>_2XXN, _562., _.0075</b> = search for any match of these numbers<br>");?>
<?php $obcnum_tooltip .= _("<b>_!2XXN, _562., _.0075</b> = Search for any match <b>except</b> for these numbers");?>
<?php $obcnum_tooltip .= _("<br>Asterisk pattern matching<br>");?>
<?php $obcnum_tooltip .= _("<b>X</b> = matches any digit from 0-9<br>");?>
<?php $obcnum_tooltip .= _("<b>Z</b> = matches any digit from 1-9<br>");?>
<?php $obcnum_tooltip .= _("<b>N</b> = matches any digit from 2-9<br>");?>
<?php $obcnum_tooltip .= _("<b>[1237-9]</b> = matches any digit or letter in the brackets<br>(in this example, 1,2,3,7,8,9)<br>");?>
<?php $obcnum_tooltip .= _("<b>.</b> = wildcard, matches one or more characters<br>");?>
<td><input <?php if (isset($_POST['order']) && $_POST['order'] == 'outbound_cnum') { echo 'checked="checked"'; } ?> type="radio" name="order" value="outbound_cnum" />&nbsp;<label for="outbound_cnum"><?php echo "<a href=\"#\" class=\"info\">"._("Outbound CallerID Number")."<span>$obcnum_tooltip</span></a>"?>:</label></td>
<td><input type="text" name="outbound_cnum" id="outbound_cnum" value="<?php if (isset($_POST['outbound_cnum'])) { echo htmlspecialchars($_POST['outbound_cnum']); } ?>" />
<?php echo _("Not")?>:<input <?php if ( isset($_POST['outbound_cnum_neg'] ) && $_POST['outbound_cnum_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="outbound_cnum_neg" value="true" />
<?php echo _("Begins With")?>:<input <?php if (empty($_POST['outbound_cnum_mod']) || $_POST['outbound_cnum_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="outbound_cnum_mod" value="begins_with" />
<?php echo _("Contains")?>:<input <?php if (isset($_POST['outbound_cnum_mod']) && $_POST['outbound_cnum_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="outbound_cnum_mod" value="contains" />
<?php echo _("Ends With")?>:<input <?php if (isset($_POST['outbound_cnum_mod']) && $_POST['outbound_cnum_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="outbound_cnum_mod" value="ends_with" />
<?php echo _("Exactly")?>:<input <?php if (isset($_POST['outbound_cnum_mod']) && $_POST['outbound_cnum_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="outbound_cnum_mod" value="exact" />
</td>
</tr>

<tr>
<?php $did_tooltip = _("Search for a DID.");?>
<td><input <?php if (isset($_POST['order']) && $_POST['order'] == 'did') { echo 'checked="checked"'; } ?> type="radio" name="order" value="did" />&nbsp;<label for="did"><?php echo "<a href=\"#\" class=\"info\">"._("DID")."<span>$did_tooltip</span></a>"?></label></td>
<td><input type="text" name="did" id="did" value="<?php if (isset($_POST['did'])) { echo htmlspecialchars($_POST['did']); } ?>" />
<?php echo _("Not")?>:<input <?php if ( isset($_POST['did_neg'] ) && $_POST['did_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="did_neg" value="true" />
<?php echo _("Begins With")?>:<input <?php if (empty($_POST['did_mod']) || $_POST['did_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="did_mod" value="begins_with" />
<?php echo _("Contains")?>:<input <?php if (isset($_POST['did_mod']) && $_POST['did_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="did_mod" value="contains" />
<?php echo _("Ends With")?>:<input <?php if (isset($_POST['did_mod']) && $_POST['did_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="did_mod" value="ends_with" />
<?php echo _("Exactly")?>:<input <?php if (isset($_POST['did_mod']) && $_POST['did_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="did_mod" value="exact" />
</td>
</tr>
<tr>
<?php $destination_tooltip = _("Search for destination calls. You can enter multiple sources separated by a comma. This field support Asterisk regular expression. Example<br>");?>
<?php $destination_tooltip .= _("<b>_2XXN, _562., _.0075</b> = search for any match of these numbers<br>");?>
<?php $destination_tooltip .= _("<b>_!2XXN, _562., _.0075</b> = Search for any match <b>except</b> for these numbers");?>
<?php $destination_tooltip .= _("<br>Asterisk pattern matching<br>");?>
<?php $destination_tooltip .= _("<b>X</b> = matches any digit from 0-9<br>");?>
<?php $destination_tooltip .= _("<b>Z</b> = matches any digit from 1-9<br>");?>
<?php $destination_tooltip .= _("<b>N</b> = matches any digit from 2-9<br>");?>
<?php $destination_tooltip .= _("<b>[1237-9]</b> = matches any digit or letter in the brackets<br>(in this example, 1,2,3,7,8,9)<br>");?>
<?php $destination_tooltip .= _("<b>.</b> = wildcard, matches one or more characters<br>");?>
<td><input <?php if (isset($_POST['order']) && $_POST['order'] == 'dst') { echo 'checked="checked"'; } ?> type="radio" name="order" value="dst" />&nbsp;<label for="dst"><?php echo "<a href=\"#\" class=\"info\">"._("Destination")."<span>$destination_tooltip</span></a>"?>:</label></td>
<td><input type="text" name="dst" id="dst" value="<?php if (isset($_POST['dst'])) { echo htmlspecialchars($_POST['dst']); } ?>" />
<?php echo _("Not")?>:<input <?php if ( isset($_POST['dst_neg'] ) &&  $_POST['dst_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="dst_neg" value="true" />
<?php echo _("Begins With")?>:<input <?php if (empty($_POST['dst_mod']) || $_POST['dst_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="dst_mod" value="begins_with" />
<?php echo _("Contains")?>:<input <?php if (isset($_POST['dst_mod']) && $_POST['dst_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="dst_mod" value="contains" />
<?php echo _("Ends With")?>:<input <?php if (isset($_POST['dst_mod']) && $_POST['dst_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="dst_mod" value="ends_with" />
<?php echo _("Exactly")?>:<input <?php if (isset($_POST['dst_mod']) && $_POST['dst_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="dst_mod" value="exact" />
</td>
</tr>
<?php
	// TODO: make this configurable since it requires outbound CNAM lookup
?>
<tr>
<?php $dstcnam_tooltip = _("Select Destination Caller Name to search for.");?>
<td><input <?php if (isset($_POST['order']) && $_POST['order'] == 'dst_cnam') { echo 'checked="checked"'; } ?> type="radio" name="order" value="dst_cnam" />&nbsp;<label for="dst_cnam"><?php echo "<a href=\"#\" class=\"info\">"._("Destination CallerID Name")."<span>$dstcnam_tooltip</span></a>"?>:</label></td>
<td><input type="text" name="dst_cnam" id="dst_cnam" value="<?php if (isset($_POST['dst_cnam'])) { echo htmlspecialchars($_POST['dst_cnam']); } ?>" />
<?php echo _("Not")?>:<input <?php if ( isset($_POST['dst_cnam_neg'] ) && $_POST['dst_cnam_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="dst_cnam_neg" value="true" />
<?php echo _("Begins With")?>:<input <?php if (empty($_POST['dst_cnam_mod']) || $_POST['dst_cnam_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="dst_cnam_mod" value="begins_with" />
<?php echo _("Contains")?>:<input <?php if (isset($_POST['dst_cnam_mod']) && $_POST['dst_cnam_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="dst_cnam_mod" value="contains" />
<?php echo _("Ends With")?>:<input <?php if (isset($_POST['dst_cnam_mod']) && $_POST['dst_cnam_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="dst_cnam_mod" value="ends_with" />
<?php echo _("Exactly")?>:<input <?php if (isset($_POST['dst_cnam_mod']) && $_POST['dst_cnam_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="dst_cnam_mod" value="exact" />
</td>
</tr>

<tr>
<?php $userfield_tooltip = _("Search for userfield data (if enabled).");?>
<td><input <?php if (isset($_POST['order']) && $_POST['order'] == 'userfield') { echo 'checked="checked"'; } ?> type="radio" name="order" value="userfield" />&nbsp;<label for="userfield"><?php echo "<a href=\"#\" class=\"info\">"._("Userfield")."<span>$userfield_tooltip</span></a>"?>:</label></td>
<td><input type="text" name="userfield" id="userfield" value="<?php if (isset($_POST['userfield'])) { echo htmlspecialchars($_POST['userfield']); } ?>" />
<?php echo _("Not")?>:<input <?php if (  isset($_POST['userfield_neg'] ) && $_POST['userfield_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="userfield_neg" value="true" />
<?php echo _("Begins With")?>:<input <?php if (empty($_POST['userfield_mod']) || $_POST['userfield_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="userfield_mod" value="begins_with" />
<?php echo _("Contains")?>:<input <?php if (isset($_POST['userfield_mod']) && $_POST['userfield_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="userfield_mod" value="contains" />
<?php echo _("Ends With")?>:<input <?php if (isset($_POST['userfield_mod']) && $_POST['userfield_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="userfield_mod" value="ends_with" />
<?php echo _("Exactly")?>:<input <?php if (isset($_POST['userfield_mod']) && $_POST['userfield_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="userfield_mod" value="exact" />
</td>
</tr>
<tr>
<?php $accountcode_tooltip = _("Search for accountcode.");?>
<td><input <?php if (isset($_POST['order']) && $_POST['order'] == 'accountcode') { echo 'checked="checked"'; } ?> type="radio" name="order" value="accountcode" />&nbsp;<label for="userfield"><?php echo "<a href=\"#\" class=\"info\">"._("Account Code")."<span>$accountcode_tooltip</span></a>"?>:</label></td>
<td><input type="text" name="accountcode" id="accountcode" value="<?php if (isset($_POST['accountcode'])) { echo htmlspecialchars($_POST['accountcode']); } ?>" />
<?php echo _("Not")?>:<input <?php if ( isset($_POST['accountcode_neg'] ) &&  $_POST['accountcode_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="accountcode_neg" value="true" />
<?php echo _("Begins With")?>:<input <?php if (empty($_POST['accountcode_mod']) || $_POST['accountcode_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="accountcode_mod" value="begins_with" />
<?php echo _("Contains")?>:<input <?php if (isset($_POST['accountcode_mod']) && $_POST['accountcode_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="accountcode_mod" value="contains" />
<?php echo _("Ends With")?>:<input <?php if (isset($_POST['accountcode_mod']) && $_POST['accountcode_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="accountcode_mod" value="ends_with" />
<?php echo _("Exactly")?>:<input <?php if (isset($_POST['accountcode_mod']) && $_POST['accountcode_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="accountcode_mod" value="exact" />
</td>
</tr>
<tr>
<?php $duration_tooltip = _("Search for calls that matches the call length specified.");?>
<td><input <?php if (isset($_POST['order']) && $_POST['order'] == 'duration') { echo 'checked="checked"'; } ?> type="radio" name="order" value="duration" />&nbsp;<label><?php echo "<a href=\"#\" class=\"info\">"._("Duration")."<span>$duration_tooltip</span></a>"?>:</label></td>
<td><?php echo _("Between")?>:
<input type="text" name="dur_min" value="<?php if (isset($_POST['dur_min'])) { echo htmlspecialchars($_POST['dur_min']); } ?>" size="3" maxlength="5" />
<?php echo _("And")?>:
<input type="text" name="dur_max" value="<?php if (isset($_POST['dur_max'])) { echo htmlspecialchars($_POST['dur_max']); } ?>" size="3" maxlength="5" />
<?php echo _("Seconds")?>
</td>
</tr>
<tr>
<?php $disposition_tooltip = _("Search for calls that matches either ANSWERED, BUSY, FAILED or NO ANSWER.");?>
<td><input <?php if (isset($_POST['order']) && $_POST['order'] == 'disposition') { echo 'checked="checked"'; } ?> type="radio" name="order" value="disposition" />&nbsp;<label for="disposition"><?php echo "<a href=\"#\" class=\"info\">"._("Disposition")."<span>$disposition_tooltip</span></a>"?>:</label></td>
<td>

<select name="disposition" id="disposition">
<option <?php if (empty($_POST['disposition']) || $_POST['disposition'] == 'all') { echo 'selected="selected"'; } ?> value="all"><?php echo _("All Dispositions")?></option>
<option <?php if (isset($_POST['disposition']) && $_POST['disposition'] == 'ANSWERED') { echo 'selected="selected"'; } ?> value="ANSWERED"><?php echo _("Answered")?></option>
<option <?php if (isset($_POST['disposition']) && $_POST['disposition'] == 'BUSY') { echo 'selected="selected"'; } ?> value="BUSY"><?php echo _("Busy")?></option>
<option <?php if (isset($_POST['disposition']) && $_POST['disposition'] == 'FAILED') { echo 'selected="selected"'; } ?> value="FAILED"><?php echo _("Failed")?></option>
<option <?php if (isset($_POST['disposition']) && $_POST['disposition'] == 'NO ANSWER') { echo 'selected="selected"'; } ?> value="NO ANSWER"><?php echo _("No Answer")?></option>
</select>
<?php echo _("Not")?>:<input <?php if ( isset($_POST['disposition_neg'] ) && $_POST['disposition_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="disposition_neg" value="true" />
</td>
</tr>
<tr>
<td>
<select name="sort" id="sort">
<option <?php if (isset($_POST['sort']) && $_POST['sort'] == 'ASC') { echo 'selected="selected"'; } ?> value="ASC"><?php echo _("Oldest First")?></option>
<option <?php if (empty($_POST['sort']) || $_POST['sort'] == 'DESC') { echo 'selected="selected"'; } ?> value="DESC"><?php echo _("Newest First")?></option>
</select>
</td>
<td><table width="100%"><tr><td>
<label for="group"><?php echo _("Group By")?>:</label>
<select name="group" id="group">
<optgroup label="<?php echo _("Account Information")?>">
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'accountcode') { echo 'selected="selected"'; } ?> value="accountcode"><?php echo _("Account Code")?></option>
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'userfield') { echo 'selected="selected"'; } ?> value="userfield"><?php echo _("User Field")?></option>
</optgroup>
<optgroup label="<?php echo _("Date/Time")?>">
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'minutes1') { echo 'selected="selected"'; } ?> value="minutes1"><?php echo _("Minute")?></option>
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'minutes10') { echo 'selected="selected"'; } ?> value="minutes10"><?php echo _("10 Minutes")?></option>
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'hour') { echo 'selected="selected"'; } ?> value="hour"><?php echo _("Hour")?></option>
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'hour_of_day') { echo 'selected="selected"'; } ?> value="hour_of_day"><?php echo _("Hour of day")?></option>
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'day_of_week') { echo 'selected="selected"'; } ?> value="day_of_week"><?php echo _("Day of week")?></option>
<option <?php if (empty($_POST['group']) || $_POST['group'] == 'day') { echo 'selected="selected"'; } ?> value="day"><?php echo _("Day")?></option>
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'week') { echo 'selected="selected"'; } ?> value="week"><?php echo _("Week ( Sun-Sat )")?></option>
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'month') { echo 'selected="selected"'; } ?> value="month"><?php echo _("Month")?></option>
</optgroup>
<optgroup label="<?php echo _("Telephone Number")?>">
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'dst') { echo 'selected="selected"'; } ?> value="dst"><?php echo _("Destination Number")?></option>
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'did') { echo 'selected="selected"'; } ?> value="did"><?php echo _("DID")?></option>
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'cnum') { echo 'selected="selected"'; } ?> value="cnum"><?php echo _("Caller ID Number")?></option>
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'cnam') { echo 'selected="selected"'; } ?> value="cnam"><?php echo _("Caller ID Name")?></option>
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'outbound_cnum') { echo 'selected="selected"'; } ?> value="outbound_cnum"><?php echo _("Outbound Caller ID Number")?></option>
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'outbound_cnam') { echo 'selected="selected"'; } ?> value="outbound_cnam"><?php echo _("Outbound Caller ID Name")?></option>
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'dst_cnam') { echo 'selected="selected"'; } ?> value="dst_cnam"><?php echo _("Destination Caller ID Name")?></option>
</optgroup>
<optgroup label="<?php echo _("Tech info")?>">
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'disposition') { echo 'selected="selected"'; } ?> value="disposition">Disposition</option>
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'disposition_by_day') { echo 'selected="selected"'; } ?> value="disposition_by_day">Disposition by Day</option>
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'disposition_by_hour') { echo 'selected="selected"'; } ?> value="disposition_by_hour">Disposition by Hour</option>
<option <?php if (isset($_POST['group']) && $_POST['group'] == 'dcontext') { echo 'selected="selected"'; } ?> value="dcontext">Destination context</option>
</optgroup>
</select></td><td align="left" width="40%">
<input type="submit" value="<?php echo _("Search")?>" />
</td></td></table>
</td>
</tr>
</table>
</fieldset>
</form>
</td>
</tr>
</table>
<?php

// Determine all CEL events associated with this uid, and then get all CDR records related to this event stream
// to display below
//
if ($amp_conf['CEL_ENABLED'] && !isset($_POST['need_html']) && $action == 'cel_show') {
	echo '<a id="CEL"></a>';
	$cdr_uids = array();

	$uid = $dbcdr->escapeSimple($_REQUEST['uid']);

	$db_cel_name = !empty($amp_conf['CELDBNAME'])?$amp_conf['CELDBNAME']:"asteriskcdrdb";
	$db_cel_table_name = !empty($amp_conf['CELDBTABLENAME'])?$amp_conf['CELDBTABLENAME']:"cel";
	$cel = cdr_get_cel($uid, $db_cel_name . '.' . $db_cel_table_name);
	$tot_cel_events = count($cel);

	if ( $tot_cel_events ) {
		echo "<p class=\"center title\">"._("Call Event Log - Search Returned")." ".$tot_cel_events." "._("Events")."</p>";
		echo "<table id=\"cdr_table\" class=\"cdr\">";

		$i = $h_step - 1;
		foreach($cel as $row) {

			// accumulate all id's for CDR query
			//
			$cdr_uids[] = $row['uniqueid'];
			$cdr_uids[] = $row['linkedid'];

			++$i;
			if ($i == $h_step) {
			?>
				<tr>
				<th class="record_col"><?php echo _("Time")?></th>
				<th class="record_col"><?php echo _("Event")?></th>
				<th class="record_col"><?php echo _("CNAM")?></th>
				<th class="record_col"><?php echo _("CNUM")?></th>
				<th class="record_col"><?php echo _("ANI")?></th>
				<th class="record_col"><?php echo _("DID")?></th>
				<th class="record_col"><?php echo _("AMA")?></th>
				<th class="record_col"><?php echo _("exten")?></th>
				<th class="record_col"><?php echo _("context")?></th>
				<th class="record_col"><?php echo _("App")?></th>
				<th class="record_col"><?php echo _("channel")?></th>
				<th class="record_col"><?php echo _("UserDefType")?></th>
				<th class="record_col"><?php echo _("EventExtra")?></th>
				<th class="img_col"><a href="#CEL" title="Go to the top of the CEL table"><img src="images/scrollup.gif" alt="CEL Table" /></a></th>
				</tr>
				<?php
				$i = 0;
			}

			echo "  <tr class=\"record\">\n";
			cdr_formatCallDate($row['eventtime']);
			cdr_cel_formatEventType($row['eventtype']);
			cdr_formatCNAM($row['cid_name']);
			cdr_formatCNUM($row['cid_num']);
			cdr_formatANI($row['cid_ani']);
			cdr_formatDID($row['cid_dnid']);
			cdr_formatAMAFlags($row['amaflags']);
			cdr_formatExten($row['exten']);
			cdr_formatContext($row['context']);
			cdr_formatApp($row['appname'], $row['appdata']);
			cdr_cel_formatChannelName($row['channame']);
			cdr_cel_formatUserDefType($row['userdeftype']);
			cdr_cel_formatEventExtra($row['eventextra']);
			echo "    <td></td>\n";
			echo "    <td></td>\n";
			echo "  </tr>\n";
		}
		echo "</table>";
	}
	// now determine CDR query that we will use below in the same code that normally
	// displays the CDR data, in this case all related records that are involved with
	// this event stream.
	//
	$where = "WHERE `uniqueid` IN ('" . implode("','",array_unique($cdr_uids)) . "')";
	$query = "SELECT `calldate`, `clid`, `did`, `src`, `dst`, `dcontext`, `channel`, `dstchannel`, `lastapp`, `lastdata`, `duration`, `billsec`, `disposition`, `amaflags`, `accountcode`, `uniqueid`, `userfield`, unix_timestamp(calldate) as `call_timestamp`, `recordingfile`, `cnum`, `cnam`, `outbound_cnum`, `outbound_cnam`, `dst_cnam` FROM $db_name.$db_table_name $where";
	$resultscdr = $dbcdr->getAll($query, DB_FETCHMODE_ASSOC);
}

echo '<a id="CDR"></a>';

$startmonth = empty($_POST['startmonth']) ? date('m') : $_POST['startmonth'];
$startyear = empty($_POST['startyear']) ? date('Y') : $_POST['startyear'];

if (empty($_POST['startday'])) {
	$startday = '01';
} elseif (isset($_POST['startday']) && ($_POST['startday'] > date('t', strtotime("$startyear-$startmonth")))) {
	$startday = $_POST['startday'] = date('t', strtotime("$startyear-$startmonth"));
} else {
	$startday = sprintf('%02d',$_POST['startday']);
}
$starthour = empty($_POST['starthour']) ? '00' : sprintf('%02d',$_POST['starthour']);
$startmin = empty($_POST['startmin']) ? '00' : sprintf('%02d',$_POST['startmin']);

$startdate = "'$startyear-$startmonth-$startday $starthour:$startmin:00'";
$start_timestamp = mktime( $starthour, $startmin, 59, $startmonth, $startday, $startyear );

$endmonth = empty($_POST['endmonth']) ? date('m') : $_POST['endmonth'];
$endyear = empty($_POST['endyear']) ? date('Y') : $_POST['endyear'];

if (empty($_POST['endday']) || (isset($_POST['endday']) && ($_POST['endday'] > date('t', strtotime("$endyear-$endmonth-01"))))) {
	$endday = $_POST['endday'] = date('t', strtotime("$endyear-$endmonth"));
} else {
	$endday = sprintf('%02d',$_POST['endday']);
}
$endhour = empty($_POST['endhour']) ? '23' : sprintf('%02d',$_POST['endhour']);
$endmin = empty($_POST['endmin']) ? '59' : sprintf('%02d',$_POST['endmin']);

$enddate = "'$endyear-$endmonth-$endday $endhour:$endmin:59'";
$end_timestamp = mktime( $endhour, $endmin, 59, $endmonth, $endday, $endyear );

#
# asterisk regexp2sqllike
#
if ( !isset($_POST['outbound_cnum']) ) {
	$outbound_cnum_number = NULL;
} else {
	$outbound_cnum_number = cdr_asteriskregexp2sqllike( 'outbound_cnum', '' );
}

if ( !isset($_POST['cnum']) ) {
	$cnum_number = NULL;
} else {
	$cnum_number = cdr_asteriskregexp2sqllike( 'cnum', '' );
}

if ( !isset($_POST['dst']) ) {
	$dst_number = NULL;
} else {
	$dst_number = cdr_asteriskregexp2sqllike( 'dst', '' );
}

$date_range = "calldate BETWEEN $startdate AND $enddate";

$mod_vars['outbound_cnum'][] = $outbound_cnum_number;
$mod_vars['outbound_cnum'][] = empty($_POST['outbound_cnum_mod']) ? NULL : $_POST['outbound_cnum_mod'];
$mod_vars['outbound_cnum'][] = empty($_POST['outbound_cnum_neg']) ? NULL : $_POST['outbound_cnum_neg'];

$mod_vars['cnum'][] = $cnum_number;
$mod_vars['cnum'][] = empty($_POST['cnum_mod']) ? NULL : $_POST['cnum_mod'];
$mod_vars['cnum'][] = empty($_POST['cnum_neg']) ? NULL : $_POST['cnum_neg'];

$mod_vars['cnam'][] = !isset($_POST['cnam']) ? NULL : $_POST['cnam'];
$mod_vars['cnam'][] = empty($_POST['cnam_mod']) ? NULL : $_POST['cnam_mod'];
$mod_vars['cnam'][] = empty($_POST['cnam_neg']) ? NULL : $_POST['cnam_neg'];

$mod_vars['dst_cnam'][] = !isset($_POST['dst_cnam']) ? NULL : $_POST['dst_cnam'];
$mod_vars['dst_cnam'][] = empty($_POST['dst_cnam_mod']) ? NULL : $_POST['dst_cnam_mod'];
$mod_vars['dst_cnam'][] = empty($_POST['dst_cnam_neg']) ? NULL : $_POST['dst_cnam_neg'];

$mod_vars['did'][] = !isset($_POST['did']) ? NULL : $_POST['did'];
$mod_vars['did'][] = empty($_POST['did_mod']) ? NULL : $_POST['did_mod'];
$mod_vars['did'][] = empty($_POST['did_neg']) ? NULL : $_POST['did_neg'];

$mod_vars['dst'][] = $dst_number;
$mod_vars['dst'][] = empty($_POST['dst_mod']) ? NULL : $_POST['dst_mod'];
$mod_vars['dst'][] = empty($_POST['dst_neg']) ? NULL : $_POST['dst_neg'];

$mod_vars['userfield'][] = !isset($_POST['userfield']) ? NULL : $_POST['userfield'];
$mod_vars['userfield'][] = empty($_POST['userfield_mod']) ? NULL : $_POST['userfield_mod'];
$mod_vars['userfield'][] = empty($_POST['userfield_neg']) ? NULL : $_POST['userfield_neg'];

$mod_vars['accountcode'][] = !isset($_POST['accountcode']) ? NULL : $_POST['accountcode'];
$mod_vars['accountcode'][] = empty($_POST['accountcode_mod']) ? NULL : $_POST['accountcode_mod'];
$mod_vars['accountcode'][] = empty($_POST['accountcode_neg']) ? NULL : $_POST['accountcode_neg'];
$result_limit = (!isset($_POST['limit']) || empty($_POST['limit'])) ? $db_result_limit : $_POST['limit'];

$multi = array('dst', 'cnum', 'outbound_cnum');
foreach ($mod_vars as $key => $val) {
	if (is_blank($val[0])) {
		unset($_POST[$key.'_mod']);
		$$key = NULL;
	} else {
		$pre_like = '';
		if ( $val[2] == 'true' ) {
			$pre_like = ' NOT ';
		}
		switch ($val[1]) {
			case "contains":
				if (in_array($key, $multi)) {
					$values = explode(',',$val[0]);
					if (count($values) > 1) {
						foreach ($values as $key_like => $value_like) {
							if ($key_like == 0) {
								$$key = "AND $key $pre_like LIKE '%$value_like%'";
							} else {
 								$$key .= " OR $key $pre_like LIKE '%$value_like%'";
							}
						}
					} else {
						$$key = "AND $key $pre_like LIKE '%$val[0]%'";
					}
				} else {
					$$key = "AND $key $pre_like LIKE '%$val[0]%'";
				}
			break;
			case "ends_with":
				if (in_array($key, $multi)) {
					$values = explode(',',$val[0]);
					if (count($values) > 1) {
						foreach ($values as $key_like => $value_like) {
							if ($key_like == 0) {
								$$key = "AND $key $pre_like LIKE '%$value_like'";
							} else {
								$$key .= " OR $key $pre_like LIKE '%$value_like'";
							}
						}
					} else {
						$$key = "AND $key $pre_like LIKE '%$val[0]'";
					}
				} else {
					$$key = "AND $key $pre_like LIKE '%$val[0]'";
				}
			break;
			case "exact":
				if ( $val[2] == 'true' ) {
					$$key = "AND $key != '$val[0]'";
				} else {
					$$key = "AND $key = '$val[0]'";
				}
			break;
			case "asterisk-regexp":
				$ast_dids = preg_split('/\s*,\s*/', $val[0], -1, PREG_SPLIT_NO_EMPTY);
				$ast_key = '';
				foreach ($ast_dids as $adid) {
					if (strlen($ast_key) > 0 ) {
						if ( $pre_like == ' NOT ' ) {
							$ast_key .= " and ";
						} else {
							$ast_key .= " or ";
						}
						if ( '_' == substr($adid,0,1) ) {
							$adid = substr($adid,1);
						}
					}
					$ast_key .= " $key $pre_like RLIKE '^$adid\$'";
				}
				$$key = "AND ( $ast_key )";
			break;
			case "begins_with":
			default:
				if (in_array($key, $multi)) {
					$values = explode(',',$val[0]);
					if (count($values) > 1) {
						foreach ($values as $key_like => $value_like) {
							if ($key_like == 0) {
								$$key = "AND $key $pre_like LIKE '$value_like%'";
							} else {
								$$key .= " OR $key $pre_like LIKE '$value_like%'";
							}
						}
					} else {
						$$key = "AND $key $pre_like LIKE '$val[0]%'";
					}
				} else {
					$$key = "AND $key $pre_like LIKE '$val[0]%'";
				}
			break;
		}
	}
}

if ( isset($_POST['disposition_neg']) && $_POST['disposition_neg'] == 'true' ) {
	$disposition = (empty($_POST['disposition']) || $_POST['disposition'] == 'all') ? NULL : "AND disposition != '$_POST[disposition]'";
} else {
	$disposition = (empty($_POST['disposition']) || $_POST['disposition'] == 'all') ? NULL : "AND disposition = '$_POST[disposition]'";
}

$duration = (!isset($_POST['dur_min']) || is_blank($_POST['dur_max'])) ? NULL : "AND duration BETWEEN '$_POST[dur_min]' AND '$_POST[dur_max]'";
$order = empty($_POST['order']) ? 'ORDER BY calldate' : "ORDER BY $_POST[order]";
$sort = empty($_POST['sort']) ? 'DESC' : $_POST['sort'];
$group = empty($_POST['group']) ? 'day' : $_POST['group'];

//Allow people to search SRC and DSTChannels using existing fields
if (isset($cnum)) {
  $cnum_length = strlen($cnum);
  $cnum_type = substr($cnum, 0 ,strpos($cnum , 'cnum') -1);
  $cnum_remaining = substr($cnum, strpos($cnum , 'cnum'));
  $src = str_replace('AND cnum', '', $cnum);

  $cnum = "$cnum_type ($cnum_remaining OR src $src)";
}

if (isset($dst)) {
  $dst_length = strlen($dst);
  $dst_type = substr($dst, 0 ,strpos($dst , 'dst') -1);
  $dst_remaining = substr($dst, strpos($dst , 'dst'));
  $dstchannel = str_replace('AND dst', '', $dst);

  $dst = "$dst_type ($dst_remaining OR dstchannel $dstchannel)";
}

// Build the "WHERE" part of the query
$where = "WHERE $date_range $cnum $outbound_cnum $cnam $dst_cnam $did $dst $userfield $accountcode $disposition $duration";

if ( isset($_POST['need_csv']) && $_POST['need_csv'] == 'true' ) {
	$query = "(SELECT calldate, clid, did, src, dst, dcontext, channel, dstchannel, lastapp, lastdata, duration, billsec, disposition, amaflags, accountcode, uniqueid, userfield, cnum, cnam, outbound_cnum, outbound_cnam, dst_cnam FROM $db_name.$db_table_name $where $order $sort LIMIT $result_limit)";
	$resultcsv = $dbcdr->getAll($query, DB_FETCHMODE_ASSOC);
	cdr_export_csv($resultcsv);
}

if ( empty($resultcdr) && isset($_POST['need_html']) && $_POST['need_html'] == 'true' ) {
	$query = "SELECT `calldate`, `clid`, `did`, `src`, `dst`, `dcontext`, `channel`, `dstchannel`, `lastapp`, `lastdata`, `duration`, `billsec`, `disposition`, `amaflags`, `accountcode`, `uniqueid`, `userfield`, unix_timestamp(calldate) as `call_timestamp`, `recordingfile`, `cnum`, `cnam`, `outbound_cnum`, `outbound_cnam`, `dst_cnam`  FROM $db_name.$db_table_name $where $order $sort LIMIT $result_limit";
	$resultscdr = $dbcdr->getAll($query, DB_FETCHMODE_ASSOC);
}
if ( isset($resultscdr) ) {
	$tot_calls_raw = sizeof($resultscdr);
} else {
	$tot_calls_raw = 0;
}
if ( $tot_calls_raw ) {
	// This is a bit of a hack, if we generated CEL data above, then these are simply the records all related to that CEL
	// event stream.
	//
	if (!isset($cel)) {
		echo "<p class=\"center title\">"._("Call Detail Record - Search Returned")." ".$tot_calls_raw." "._("Calls")."</p>";
	} else {
		echo "<p class=\"center title\">"._("Related Call Detail Records") . "</p>";
	}
	echo "<table id=\"cdr_table\" class=\"cdr\">";

	$i = $h_step - 1;
	$id = -1;  // tracker for recording index
	foreach($resultscdr as $row) {
		++$id;  // Start at table row 1
		++$i;
		if ($i == $h_step) {
		?>
			<tr>
			<th class="record_col"><?php echo _("Call Date")?></th>
			<th class="record_col"><?php echo _("Recording")?></th>
			<th class="record_col"><?php echo _("System")?></th>
			<th class="record_col"><?php echo _("CallerID")?></th>
			<th class="record_col"><?php echo _("Outbound CallerID")?></th>
			<th class="record_col"><?php echo _("DID")?></th>
			<th class="record_col"><?php echo _("App")?></th>
			<th class="record_col"><?php echo _("Destination")?></th>
			<th class="record_col"><?php echo _("Disposition")?></th>
			<th class="record_col"><?php echo _("Duration")?></th>
			<th class="record_col"><?php echo _("Userfield")?></th>
			<th class="record_col"><?php echo _("Account")?></th>
			<th class="img_col"><a href="#CDR" title="Go to the top of the CDR table"><img src="images/scrollup.gif" alt="CDR Table" /></a></th>
			<th class="img_col"><a href="#Graph" title="Go to the top of the CDR graph"><img src="images/scrolldown.gif" alt="CDR Graph" /></a></th>
			</tr>
			<?php
			$i = 0;
			++$id;
		}

		/* If CDR claims there is a call recording we make sure there is and the file is there, or we set it blank. In some cases
		 * a recording may have been planned but not done so this assures there are no dead links.
		 */
		if ($row['recordingfile']) {
			$rec_parts = explode('-',$row['recordingfile']);
			$fyear = substr($rec_parts[3],0,4);
			$fmonth = substr($rec_parts[3],4,2);
			$fday = substr($rec_parts[3],6,2);
			$monitor_base = $amp_conf['MIXMON_DIR'] ? $amp_conf['MIXMON_DIR'] : $amp_conf['ASTSPOOLDIR'] . '/monitor';
			$recordingfile = "$monitor_base/$fyear/$fmonth/$fday/" . $row['recordingfile'];
			if (!file_exists($recordingfile)) {
				$recordingfile = '';
			}
		} else {
			$recordingfile = '';
		}

		echo "  <tr class=\"record\">\n";
		cdr_formatCallDate($row['calldate']);
		cdr_formatRecordingFile($recordingfile, $row['recordingfile'], $id, $row['uniqueid']);
		cdr_formatUniqueID($row['uniqueid']);

		$tcid = $row['cnam'] == '' ? '<' . $row['cnum'] . '>' : $row['cnam'] . ' <' . $row['cnum'] . '>';
		if ($row['outbound_cnum'] != '') {
			$cid = '<' . $row['outbound_cnum'] . '>';
			if ($row['outbound_cnam'] != '') {
				$cid = $row['outbound_cnam'] . ' ' . $cid;
			}
		} else {
			$cid = $tcid;
		}
		// for legacy records
		if ($cid == '<>') {
			$cid = $row['src'];
			$tcid = $row['clid'];
		}
		//cdr_formatSrc($cid, $tcid);
		if ($row['cnum'] != '' || $row['cnum'] != '') {
			cdr_formatCallerID($row['cnam'], $row['cnum'], $row['channel']);
		} else {
			cdr_formatSrc($row['src'], $row['clid']);
		}
		cdr_formatCallerID($row['outbound_cnam'], $row['outbound_cnum'], $row['dstchannel']);
		cdr_formatDID($row['did']);
		cdr_formatApp($row['lastapp'], $row['lastdata']);
		cdr_formatDst($row['dst'], $row['dst_cnam'], $row['dstchannel'], $row['dcontext']);
		cdr_formatDisposition($row['disposition'], $row['amaflags']);
		cdr_formatDuration($row['duration'], $row['billsec']);
		cdr_formatUserField($row['userfield']);
		cdr_formatAccountCode($row['accountcode']);
		echo "    <td></td>\n";
		echo "    <td></td>\n";
		echo "  </tr>\n";
	}
	echo "</table>";
}
?>

<!-- Display Call Usage Graph -->
<?php

echo '<a id="Graph"></a>';

//NEW GRAPHS
$group_by_field = $group;
// ConcurrentCalls
$group_by_field_php = array( '', 32, '' );

switch ($group) {
	case "disposition_by_day":
	    $graph_col_title = 'Disposition by day';
	    $group_by_field_php = array('%Y-%m-%d / ',17,'');
	    $group_by_field = "CONCAT(DATE_FORMAT(calldate, '$group_by_field_php[0]'),disposition)";
	break;
	case "disposition_by_hour":
	    $graph_col_title = 'Disposition by hour';
	    $group_by_field_php = array( '%Y-%m-%d %H / ', 20, '' );
	    $group_by_field = "CONCAT(DATE_FORMAT(calldate, '$group_by_field_php[0]'),disposition)";
	break;
	case "disposition":
	    $graph_col_title = 'Disposition';
	break;
	case "dcontext":
	    $graph_col_title = 'Destination context';
	break;
	case "accountcode":
		$graph_col_title = _("Account Code");
	break;
	case "dst":
		$graph_col_title = _("Destination Number");
	break;
	case "did":
		$graph_col_title = _("DID");
	break;
	case "cnum":
		$graph_col_title = _("Caller ID Number");
	break;
	case "cnam":
		$graph_col_title = _("Caller ID Name");
	break;
	case "outbound_cnum":
		$graph_col_title = _("Outbound Caller ID Number");
	break;
	case "outbound_cnam":
		$graph_col_title = _("Outbound Caller ID Name");
	break;
	case "dst_cnam":
		$graph_col_title = _("Destination Caller ID Name");
	break;
	case "userfield":
		$graph_col_title = _("User Field");
	break;
	case "hour":
		$group_by_field_php = array( '%Y-%m-%d %H', 13, '' );
		$group_by_field = "DATE_FORMAT(calldate, '$group_by_field_php[0]')";
		$graph_col_title = _("Hour");
	break;
	case "hour_of_day":
		$group_by_field_php = array('%H',2,'');
		$group_by_field = "DATE_FORMAT(calldate, '$group_by_field_php[0]')";
		$graph_col_title = _("Hour of day");
	break;
	case "week":
		$group_by_field_php = array('%V',2,'');
		$group_by_field = "DATE_FORMAT(calldate, '$group_by_field_php[0]') ";
		$graph_col_title = _("Week ( Sun-Sat )");
	break;
	case "month":
		$group_by_field_php = array('%Y-%m',7,'');
		$group_by_field = "DATE_FORMAT(calldate, '$group_by_field_php[0]')";
		$graph_col_title = _("Month");
	break;
	case "day_of_week":
		$group_by_field_php = array('%w - %A',20,'');
		$group_by_field = "DATE_FORMAT( calldate, '%W' )";
		$graph_col_title = _("Day of week");
	break;
	case "minutes1":
		$group_by_field_php = array( '%Y-%m-%d %H:%M', 16, '' );
		$group_by_field = "DATE_FORMAT(calldate, '%Y-%m-%d %H:%i')";
		$graph_col_title = _("Minute");
	break;
	case "minutes10":
		$group_by_field_php = array('%Y-%m-%d %H:%M',15,'0');
		$group_by_field = "CONCAT(SUBSTR(DATE_FORMAT(calldate, '%Y-%m-%d %H:%i'),1,15), '0')";
		$graph_col_title = _("10 Minutes");
	break;
	case "day":
	default:
		$group_by_field_php = array('%Y-%m-%d',10,'');
		$group_by_field = "DATE_FORMAT(calldate, '$group_by_field_php[0]')";
		$graph_col_title = _("Day");
}

if ( isset($_POST['need_chart']) && $_POST['need_chart'] == 'true' ) {
	$query2 = "SELECT $group_by_field AS group_by_field, count(*) AS total_calls, sum(duration) AS total_duration FROM $db_name.$db_table_name $where GROUP BY group_by_field ORDER BY group_by_field ASC LIMIT $result_limit";
	$result2 = $dbcdr->getAll($query2, DB_FETCHMODE_ASSOC);

	$tot_calls = 0;
	$tot_duration = 0;
	$max_calls = 0;
	$max_duration = 0;
	$tot_duration_secs = 0;
	$result_array = array();
	foreach($result2 as $row) {
		$tot_duration_secs += $row['total_duration'];
		$tot_calls += $row['total_calls'];
		if ( $row['total_calls'] > $max_calls ) {
			$max_calls = $row['total_calls'];
		}
		if ( $row['total_duration'] > $max_duration ) {
			$max_duration = $row['total_duration'];
		}
		array_push($result_array,$row);
	}
	$tot_duration = sprintf('%02d', intval($tot_duration_secs/60)).':'.sprintf('%02d', intval($tot_duration_secs%60));

	if ( $tot_calls ) {
		$html = "<p class=\"center title\">"._("Call Detail Record - Call Graph by")." ".$graph_col_title."</p><table class=\"cdr\">";
		$html .= "<tr><th class=\"end_col\">". $graph_col_title . "</th>";
		$html .= "<th class=\"center_col\">"._("Total Calls").": ". $tot_calls ." / "._("Max Calls").": ". $max_calls ." / "._("Total Duration").": ". $tot_duration ."</th>";
		$html .= "<th class=\"end_col\">"._("Average Call Time")."</th>";
		$html .= "<th class=\"img_col\"><a href=\"#CDR\" title=\""._("Go to the top of the CDR table")."\"><img src=\"images/scrollup.gif\" alt=\"CDR Table\" /></a></th>";
		$html .= "<th class=\"img_col\"><a href=\"#Graph\" title=\""._("Go to the CDR Graph")."\"><img src=\"images/scrolldown.gif\" alt=\"CDR Graph\" /></a></th>";
		$html .= "</tr>";
		echo $html;

		foreach ($result_array as $row) {
			$avg_call_time = sprintf('%02d', intval(($row['total_duration']/$row['total_calls'])/60)).':'.sprintf('%02d', intval($row['total_duration']/$row['total_calls']%60));
			$bar_calls = $row['total_calls']/$max_calls*100;
			$percent_tot_calls = intval($row['total_calls']/$tot_calls*100);
			$bar_duration = $row['total_duration']/$max_duration*100;
			$percent_tot_duration = intval($row['total_duration']/$tot_duration_secs*100);
			$html_duration = sprintf('%02d', intval($row['total_duration']/60)).':'.sprintf('%02d', intval($row['total_duration']%60));
			echo "  <tr>\n";
			echo "    <td class=\"end_col\">".$row['group_by_field']."</td><td class=\"center_col\"><div class=\"bar_calls\" style=\"width : $bar_calls%\">".$row['total_calls']." - $percent_tot_calls%</div><div class=\"bar_duration\" style=\"width : $bar_duration%\">$html_duration - $percent_tot_duration%</div></td><td class=\"chart_data\">$avg_call_time</td>\n";
			echo "    <td></td>\n";
			echo "    <td></td>\n";
			echo "  </tr>\n";
		}
		echo "</table>";
	}
}
if ( isset($_POST['need_chart_cc']) && $_POST['need_chart_cc'] == 'true' ) {
	$date_range = "( (calldate BETWEEN $startdate AND $enddate) or (calldate + interval duration second  BETWEEN $startdate AND $enddate) or ( calldate + interval duration second >= $enddate AND calldate <= $startdate ) )";
	$where = "WHERE $date_range $cnum $outbound_cnum $cnam $dst_cnam $did $dst $userfield $accountcode $disposition $duration";

	$tot_calls = 0;
	$max_calls = 0;
	$result_array_cc = array();
	$result_array = array();
	if ( strpos($group_by_field,'DATE_FORMAT') === false ) {
		/* not date time fields */
		$query3 = "SELECT $group_by_field AS group_by_field, count(*) AS total_calls, unix_timestamp(calldate) AS ts, duration FROM $db_name.$db_table_name $where GROUP BY group_by_field, unix_timestamp(calldate) ORDER BY group_by_field ASC LIMIT $result_limit";
		$result3 = $dbcdr->getAll($query3, DB_FETCHMODE_ASSOC);
		$group_by_str = '';
		foreach($result3 as $row) {
			if ( $group_by_str != $row['group_by_field'] ) {
				$group_by_str = $row['group_by_field'];
				$result_array = array();
			}
			for ( $i=$row['ts']; $i<=$row['ts']+$row['duration']; ++$i ) {
				if ( isset($result_array[ "$i" ]) ) {
					$result_array[ "$i" ] += $row['total_calls'];
				} else {
					$result_array[ "$i" ] = $row['total_calls'];
				}
				if ( $max_calls < $result_array[ "$i" ] ) {
					$max_calls = $result_array[ "$i" ];
				}
				if ( ! isset($result_array_cc[ $row['group_by_field'] ]) || $result_array_cc[ $row['group_by_field'] ][1] < $result_array[ "$i" ] ) {
					$result_array_cc[$row['group_by_field']][0] = $i;
					$result_array_cc[$row['group_by_field']][1] = $result_array[ "$i" ];
				}
			}
			$tot_calls += $row['total_calls'];
		}
	} else {
		/* data fields */
		$query3 = "SELECT unix_timestamp(calldate) AS ts, duration FROM $db_name.$db_table_name $where ORDER BY unix_timestamp(calldate) ASC LIMIT $result_limit";
		$result3 = $dbcdr->getAll($query3, DB_FETCHMODE_ASSOC);
		$group_by_str = '';
		foreach($result3 as $row) {
			$group_by_str_cur = substr(strftime($group_by_field_php[0],$row['ts']),0,$group_by_field_php[1]) . $group_by_field_php[2];
			if ( $group_by_str_cur != $group_by_str ) {
				if ( $group_by_str ) {
					for ( $i=$start_timestamp; $i<$row['ts']; ++$i ) {
						if ( ! isset($result_array_cc[ "$group_by_str" ]) || ( isset($result_array["$i"]) && $result_array_cc[ "$group_by_str" ][1] < $result_array["$i"] ) ) {
							$result_array_cc[ "$group_by_str" ][0] = $i;
							$result_array_cc[ "$group_by_str" ][1] = isset($result_array["$i"]) ? $result_array["$i"] : 0;
						}
						unset( $result_array[$i] );
					}
					$start_timestamp = $row['ts'];
				}
				$group_by_str = $group_by_str_cur;
			}
			for ( $i=$row['ts']; $i<=$row['ts']+$row['duration']; ++$i ) {
				if ( isset($result_array["$i"]) ) {
					++$result_array["$i"];
				} else {
					$result_array["$i"]=1;
				}
				if ( $max_calls < $result_array["$i"] ) {
					$max_calls = $result_array["$i"];
				}
			}
			$tot_calls++;
		}
		for ( $i=$start_timestamp; $i<=$end_timestamp; ++$i ) {
			$group_by_str = substr(strftime($group_by_field_php[0],$i),0,$group_by_field_php[1]) . $group_by_field_php[2];
			if ( ! isset($result_array_cc[ "$group_by_str" ]) || ( isset($result_array["$i"]) && $result_array_cc[ "$group_by_str" ][1] < $result_array["$i"] ) ) {
				$result_array_cc[ "$group_by_str" ][0] = $i;
				$result_array_cc[ "$group_by_str" ][1] = isset($result_array["$i"]) ? $result_array["$i"] : 0;
			}
		}
	}
	if ( $tot_calls ) {
		$html = "<p class=\"center title\">"._("Call Detail Record - Concurrent Calls by")." ".$graph_col_title."</p><table class=\"cdr\">";
		$html .= "<tr><th class=\"end_col\">". $graph_col_title . "</th>";
		$html .= "<th class=\"center_col\">"._("Total Calls").": ". $tot_calls ." / "._("Max Calls").": ". $max_calls ."</th>";
		$html .= "<th class=\"end_col\">"._("Time")."</th>";
		$html .= "</tr>";
		echo $html;

		ksort($result_array_cc);

		foreach ( array_keys($result_array_cc) as $group_by_key ) {
			$full_time = strftime( '%Y-%m-%d %H:%M:%S', $result_array_cc[ "$group_by_key" ][0] );
			$group_by_cur = $result_array_cc[ "$group_by_key" ][1];
			$bar_calls = $group_by_cur/$max_calls*100;
			echo "  <tr>\n";
			echo "    <td class=\"end_col\">$group_by_key</td><td class=\"center_col\"><div class=\"bar_calls\" style=\"width : $bar_calls%\">&nbsp;$group_by_cur</div></td><td>$full_time</td>\n";
			echo "  </tr>\n";
		}

		echo "</table>";
	}
}

?>
</div>
<?php
/* CDR Table Display Functions */
function cdr_formatCallDate($calldate) {
	echo "<td>".$calldate."</td>";
}

function cdr_formatUniqueID($uniqueid) {
	global $amp_conf;

	$system = explode('-', $uniqueid, 2);
	if ($amp_conf['CEL_ENABLED']) {
		$href=$_SERVER['SCRIPT_NAME']."?display=cdr&action=cel_show&uid=" . urlencode($uniqueid);
		echo '<td title="' . _("UniqueID") . ": " . $uniqueid . '">' .
			'<a href="' . $href . '" >' . $system[0] . '</a></td>';
	} else {
		echo '<td title="' . _("UniqueID") . ": " . $uniqueid . '">' . $system[0] . '</td>';
	}
}

function cdr_formatChannel($channel) {
	$chan_type = explode('/', $channel, 2);
	echo '<td title="' . _("Channel") . ": " . $channel . '">' . $chan_type[0] . "</td>";
}

function cdr_formatSrc($src, $clid) {
	if (empty($src)) {
		echo "<td class=\"record_col\">UNKNOWN</td>";
	} else {
		$clid = htmlspecialchars($clid);
		echo '<td title="' . _("CallerID") . ": " . $clid . '">' . $src . "</td>";
	}
}

function cdr_formatCallerID($cnam, $cnum, $channel) {
	$dcnum = $cnum == '' && $cnam == '' ? '' : htmlspecialchars('<' . $cnum . '>');
	$dcnam = htmlspecialchars($cnam == '' ? '' : '"' . $cnam . ' "');
	echo '<td title="' ._("Channel") . ": " . $channel . '">' . $dcnam . $dcnum . '</td>';
}

function cdr_formatDID($did) {
	$did = htmlspecialchars($did);
	echo '<td title="' . _("DID") . ": " . $did . '">' . $did . "</td>";
}

function cdr_formatANI($ani) {
	$ani = htmlspecialchars($ani);
	echo '<td title="' . _("ANI") . ": " . $ani . '">' . $ani . "</td>";
}

function cdr_formatApp($app, $lastdata) {
	$app = htmlspecialchars($app);
	$lastdata = htmlspecialchars($lastdata);
	echo '<td title="' .  _("Application") . ": " . $app . "(" . $lastdata . ")" . '">'
	. $app . "</td>";
}

function cdr_formatDst($dst, $dst_cnam, $channel, $dcontext) {
	if ($dst == 's') {
		$dst .= ' [' . $dcontext . ']';
	}
	if ($dst_cnam != '') {
		$dst = '"' . $dst_cnam . '" ' . $dst;
	}
	echo '<td title="' . _("Channel") . ": " . $channel . ' ' . _("Destination Context") . ": " . $dcontext . '">'
		. $dst . "</td>";
}

function cdr_formatDisposition($disposition, $amaflags) {
	switch ($amaflags) {
		case 0:
			$amaflags = 'DOCUMENTATION';
			break;
		case 1:
			$amaflags = 'IGNORE';
			break;
		case 2:
			$amaflags = 'BILLING';
			break;
		case 3:
		default:
			$amaflags = 'DEFAULT';
	}
	echo '<td title="' . _("AMA Flag") . ": " . $amaflags . '">'
		. $disposition . "</td>";
}

function cdr_formatDuration($duration, $billsec) {
	$duration = sprintf('%02d', intval($duration/60)).':'.sprintf('%02d', intval($duration%60));
	$billduration = sprintf('%02d', intval($billsec/60)).':'.sprintf('%02d', intval($billsec%60));
	echo '<td title="' . _("Billing Duration") . ": " . $billduration . '">'
		. $duration . "</td>";
}

function cdr_formatUserField($userfield) {
	$userfield = htmlspecialchars($userfield);
	echo "<td>".$userfield."</td>";
}

function cdr_formatAccountCode($accountcode) {
	$accountcode = htmlspecialchars($accountcode);
	echo "<td>".$accountcode."</td>";
}

function cdr_formatRecordingFile($recordingfile, $basename, $id, $uid) {

	global $REC_CRYPT_PASSWORD;

	if ($recordingfile) {
		$crypt = new Crypt();
		// Encrypt the complete file
		$audio = urlencode($crypt->encrypt($recordingfile, $REC_CRYPT_PASSWORD));
		$recurl=$_SERVER['SCRIPT_NAME']."?display=cdr&action=cdr_play&recordingpath=$audio";
		$download_url=$_SERVER['SCRIPT_NAME']."?display=cdr&action=download_audio&cdr_file=$uid";
		$playbackRow = $id +1;
		//
		echo "<td title=\"$basename\"><a href=\"#\" onClick=\"javascript:cdr_play($playbackRow,'$recurl'); return false;\"><img src=\"assets/cdr/images/cdr_sound.png\" alt=\"Call recording\" /></a>
		<a href=\"$download_url\"><img src=\"assets/cdr/images/cdr_download.png\" alt=\"Call recording\" /></a></td>";

	} else {
		echo "<td></td>";
	}
}

function cdr_formatCNAM($cnam) {
	$cnam = htmlspecialchars($cnam);
	echo '<td title="' . _("Caller ID Name") . ": " . $cnam . '">' . $cnam . "</td>";
}

function cdr_formatCNUM($cnum) {
	$cnum = htmlspecialchars($cnum);
	echo '<td title="' . _("Caller ID Number") . ": " . $cnum . '">' . $cnum . "</td>";
}

function cdr_formatExten($exten) {
	$exten = htmlspecialchars($exten);
	echo '<td title="' . _("Dialplan exten") . ": " . $exten . '">' . $exten . "</td>";
}

function cdr_formatContext($context) {
	$context = htmlspecialchars($context);
	echo '<td title="' . _("Dialplan context") . ": " . $context . '">' . $context . "</td>";
}

function cdr_formatAMAFlags($amaflags) {
	switch ($amaflags) {
		case 0:
			$amaflags = 'DOCUMENTATION';
			break;
		case 1:
			$amaflags = 'IGNORE';
			break;
		case 2:
			$amaflags = 'BILLING';
			break;
		case 3:
		default:
			$amaflags = 'DEFAULT';
	}
	echo '<td title="' . _("AMA Flag") . ": " . $amaflags . '">'
		. $amaflags . "</td>";
}

// CEL Specific Formating:
//

function cdr_cel_formatEventType($eventtype) {
	$eventtype = htmlspecialchars($eventtype);
	echo "<td>".$eventtype."</td>";
}

function cdr_cel_formatUserDefType($userdeftype) {
	$userdeftype = htmlspecialchars($userdeftype);
	echo '<td title="' .  _("UserDefType") . ": " . $userdeftype . '">'
	. $userdeftype . "</td>";
}

function cdr_cel_formatEventExtra($eventextra) {
	$eventextra = htmlspecialchars($eventextra);
	echo '<td title="' .  _("Event Extra") . ": " . $eventextra . '">'
	. $eventextra . "</td>";
}

function cdr_cel_formatChannelName($channel) {
	$chan_type = explode('/', $channel, 2);
	$type = htmlspecialchars($chan_type[0]);
	$channel = htmlspecialchars($channel);
	echo '<td title="' . _("Channel") . ": " . $channel . '">' . $channel . "</td>";
}
