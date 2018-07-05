<?php /* $Id: page.asteriskinfo.php 2243 2006-08-12 17:13:17Z p_lindheimer $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$dispnum = 'asteriskinfo'; //used for switch on config.php

$tabindex = 0;

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$extdisplay = !empty($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:'summary';
$chan_dahdi = ast_with_dahdi();

	$modesummary = _("Summary");
	$moderegistries = _("Registries");
	$modechannels = _("Channels");
	$modepeers = _("Peers");
	$modesip = _("Sip Info");
	$modeiax = _("IAX Info");
	$modeconferences = _("Conferences");
	$modequeues = _("Queues");
	$modesubscriptions = _("Subscriptions");
	$modeall = _("Full Report");
	
	$uptime = _("Uptime");
	$activechannels = _("Active Channel(s)");
	$sipchannels = _("Sip Channel(s)");
	$iax2channels = _("IAX2 Channel(s)");
	$iax2peers = _("IAX2 Peers");
	$sipregistry = _("Sip Registry");
	$sippeers = _("Sip Peers");
	$iax2registry = _("IAX2 Registry");
	$subscribenotify = _("Subscribe/Notify");
	if ($chan_dahdi){
		$zapteldriverinfo = _("DAHDi driver info");
	} else {
		$zapteldriverinfo = _("Zaptel driver info");
	}
        $conf_meetme = _("MeetMe Conference Info");
        $conf_confbridge = _("Conference Bridge Info");
	$queuesinfo = _("Queues Info");
	$voicemailusers = _("Voicemail Users");
	$gtalkchannels = _("Google Talk Channels");
        $jabberconnections = _("Jabber Connections");
	$xmppconnections = _("Motif Connections");

$modes = array(
	"summary" => $modesummary,
	"registries" => $moderegistries,
	"channels" => $modechannels,
	"peers" => $modepeers,
	"sip" => $modesip,
	"iax" => $modeiax,
	"conferences" => $modeconferences,
	"subscriptions" => $modesubscriptions,
	"voicemail" => $voicemailusers,
	"queues" => $modequeues,
	"all" => $modeall
);
$arr_all = array(
	$uptime => "core show uptime",
	$activechannels => "show channels",
	$sipchannels => "sip show channels",
	$iax2channels => "iax2 show channels",
        $gtalkchannels => "",
	$sipregistry => "sip show registry",
	$sippeers => "sip show peers",
	$iax2registry => "iax2 show registry",
	$iax2peers => "iax2 show peers",
        $jabberconnections => "",
	$xmppconnections => "",
	$subscribenotify => "show hints",
	$zapteldriverinfo => "zap show channels",
        $conf_meetme => "",
        $conf_confbridge => "",
	$voicemailusers => "show voicemail users",
	$queuesinfo => "queue show",
);
$arr_registries = array(
	$sipregistry => "sip show registry",
	$iax2registry => "iax2 show registry",
        $jabberconnections => "",
	$xmppconnections => "",
);
$arr_channels = array(
	$activechannels => "show channels",
	$sipchannels => "sip show channels",
	$iax2channels => "iax2 show channels",
        $gtalkchannels => "",
);
$arr_peers = array(
	$sippeers => "sip show peers",
	$iax2peers => "iax2 show peers",
);
$arr_sip = array(
	$sipregistry => "sip show registry",
	$sippeers => "sip show peers",
);
$arr_iax = array(
	$iax2registry => "iax2 show registry",
	$iax2peers => "iax2 show peers",
);
$arr_conferences = array(
        $conf_meetme => "",
        $conf_confbridge => "",
);
$arr_subscriptions = array(
	$subscribenotify => "show hints"
);
$arr_voicemail = array(
	$voicemailusers => "show voicemail users",
);
$arr_queues = array(
        $queuesinfo => "queue show",
);

$engineinfo = engine_getinfo();
$astver =  $engineinfo['version'];

if (version_compare($astver, '1.8', 'ge')) {
    $meetme_check = $astman->send_request('Command', array('Command' =>
    'module show like meetme'));
    $confbridge_check = $astman->send_request('Command', array('Command' =>
    'module show like confbridge'));
    $meetme_module = preg_match('/[1-9] modules loaded/', $meetme_check['data']);
    $confbridge_module = preg_match('/[1-9] modules loaded/', $confbridge_check['data']);
    if ($meetme_module) {
        $arr_conferences[$conf_meetme]="meetme list";
        $arr_all[$conf_meetme]="meetme list";
    }
    if ($confbridge_module) {
        $arr_conferences[$conf_confbridge]="confbridge list";
        $arr_all[$conf_confbridge]="confbridge list";
    }
}

if (version_compare($astver, '1.4', 'ge')) {
  /* Check for Jabber and GTalk modules only if version > 1.4
     because they weren't introduced til then. */

  $jabber_mod_check = $astman->send_request('Command', array('Command' => 
    'module show like jabber'));
  $gtalk_mod_check = $astman->send_request('Command', array('Command' =>
    'module show like gtalk'));
  $xmpp_mod_check = $astman->send_request('Command', array('Command' =>
    'module show like xmpp'));
  $jabber_module = preg_match('/[1-9] modules loaded/', $jabber_mod_check['data']);
  $gtalk_module = preg_match('/[1-9] modules loaded/', $gtalk_mod_check['data']);
  $xmpp_module = preg_match('/[1-9] modules loaded/', $xmpp_mod_check['data']);
	$arr_all[$uptime]="core show uptime";
	$arr_all[$activechannels]="core show channels";
	$arr_all[$subscribenotify]="core show hints";
	$arr_all[$voicemailusers]="voicemail show users";
	$arr_channels[$activechannels]="core show channels";
	$arr_subscriptions[$subscribenotify]="core show hints";
	$arr_voicemail[$voicemailusers]="voicemail show users";
  if ($gtalk_module) {
          $arr_all[$gtalkchannels]="gtalk show channels";
          $arr_channels[$gtalkchannels]="gtalk show channels";
  }
  if ($jabber_module) {
          $arr_all[$jabberconnections]="jabber show connected";
          $arr_registries[$jabberconnections]="jabber show connected";
  }
}

if (version_compare($astver, '1.8', 'ge')) { 
  if ($jabber_module) {
          $arr_all[$jabberconnections] = "jabber show connections";
          $arr_registries[$jabberconnections] = "jabber show connections";
  }
}

if (version_compare($astver, '11', 'ge')) {
  if ($xmpp_module) {
          $arr_all[$xmppconnections] = "xmpp show connections";
          $arr_registries[$xmppconnections] = "xmpp show connections";
  }
}

if ($chan_dahdi){
	$arr_all[$zapteldriverinfo]="dahdi show channels";
}
?>
<div class="rnav"><ul>
<?php 
foreach ($modes as $mode => $value) {
	echo "<li><a id=\"".($extdisplay==$mode)."\" href=\"config.php?&type=".urlencode("tool")."&display=".urlencode($dispnum)."&extdisplay=".urlencode($mode)."\">"._($value)."</a></li>";
}
?>
</ul></div>

<h2><span class="headerHostInfo"><?php echo _("Asterisk (Ver. ").$astver."): "._($modes[$extdisplay])?></span></h2>

<form name="asteriskinfo" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post">
<input type="hidden" name="display" value="asteriskinfo"/>
<input type="hidden" name="action" value="asteriskinfo"/>
<table>

<table class="box">
<?php
if (!$astman) {
?>
	<tr class="boxheader">
		<td colspan="2" align="center"><h5><?php echo _("ASTERISK MANAGER ERROR")?><hr></h5></td>
	</tr>
		<tr class="boxbody">
			<td>
			<table border="0" >
				<tr>
					<td align="left">
							<?php 
							echo "<br>"._("The module was unable to connect to the Asterisk manager.<br>Make sure Asterisk is running and your manager.conf settings are proper.<br><br>");
							?>
					</td>
				</tr>
			</table>
			</td>
		</tr>
<?php
} else {
	if ($extdisplay != "summary") {
		$arr="arr_".$extdisplay;
		foreach ($$arr as $key => $value) {
                  if ($value) {
?>
			<tr class="boxheader">
				<td colspan="2" align="center"><h5><?php echo _("$key")?><hr></h5></td>
			</tr>
			<tr class="boxbody">
				<td>
				<table border="0" >
					<tr>
						<td>
							<pre>
								<?php 
								$response = $astman->send_request('Command',array('Command'=>$value));
								$new_value = $response['data'];
								echo ltrim($new_value,'Privilege: Command');
								?>
							</pre>
						</td>
					</tr>
				</table>
				</td>
			</tr>
		<?php
                          }
			}
		} else {
	?>
			<tr class="boxheader">
				<td colspan="2" align="center"><h5><?php echo _("Summary")?><hr></h5></td>
			</tr>
			<tr class="boxbody">
				<td>
				<table border="0">
					<tr>
						<td>
							<?php echo buildAsteriskInfo($astver); ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
<?php
	}
}
?>
	</table>
<tr>
	<td colspan="2"><h6><input name="Submit" type="submit" value="<?php echo _("Refresh")?>" tabindex="<?php echo ++$tabindex;?>"></h6></td>
</tr>
</table>

<script language="javascript">
<!--
var theForm = document.asteriskinfo;
//-->
</script>
</form>

<?php

function convertActiveChannel($sipChannel, $channel = NULL){
	if($channel == NULL){
		print_r($sipChannel);
		exit();
		$sipChannel_arr = explode(' ', $sipChannel[1]);
		if($sipChannel_arr[0] == 0){
			return 0;
		}else{
			return count($sipChannel_arr[0]);
		}
	}elseif($channel == 'IAX2'){
		$iaxChannel = $sipChannel;
	}
}

function getActiveChannel($channel_arr, $channelType = NULL){
	if(count($channel_arr) > 1){
		if($channelType == NULL || $channelType == 'SIP'){
			$sipChannel_arr = $channel_arr;
			$sipChannel_arrCount = count($sipChannel_arr);
			$sipChannel_string = $sipChannel_arr[$sipChannel_arrCount - 2];
			$sipChannel = explode(' ', $sipChannel_string);
			return $sipChannel[0];
		}elseif($channelType == 'IAX2'){
			$iax2Channel_arr = $channel_arr;
			$iax2Channel_arrCount = count($iax2Channel_arr);
			$iax2Channel_string = $iax2Channel_arr[$iax2Channel_arrCount - 2];
			$iax2Channel = explode(' ', $iax2Channel_string);
			return $iax2Channel[0];
		}
	}
}

function getRegistration($registration, $channelType = 'SIP'){
	if($channelType == NULL || $channelType == 'SIP'){
		$sipRegistration_arr = $registration;
		$sipRegistration_count = count($sipRegistration_arr);
		return $sipRegistration_count-3;
		
	}elseif($channelType == 'IAX2'){
		$iax2Registration_arr = $registration;
		$iax2Registration_count = count($iax2Registration_arr);
		return $iax2Registration_count-3;
	}
}

function getPeer($peer, $channelType = NULL){
	global $astver_major, $astver_minor;
	global $astver;
	if(count($peer) > 1){	
		if($channelType == NULL || $channelType == 'SIP'){
			$sipPeer = $peer;
			$sipPeer_count = count($sipPeer);
			$sipPeerInfo_arr['sipPeer_count'] = $sipPeer_count -3;
			$sipPeerInfo_string = $sipPeer[$sipPeer_count -2];
			$sipPeerInfo_arr2 = explode('[',$sipPeerInfo_string);
			$sipPeerInfo_arr3 = explode(' ',$sipPeerInfo_arr2[1]);
			if (version_compare($astver, '1.4', 'ge')) { 
				$sipPeerInfo_arr['online'] = $sipPeerInfo_arr3[1] ;
				$sipPeerInfo_arr['offline'] = $sipPeerInfo_arr3[3];

				$sipPeerInfo_arr['online-unmonitored'] = $sipPeerInfo_arr3[6];
				$sipPeerInfo_arr['offline-unmonitored'] = $sipPeerInfo_arr3[8];
			}else{
				$sipPeerInfo_arr['online'] = $sipPeerInfo_arr3[0];
				$sipPeerInfo_arr['offline'] = $sipPeerInfo_arr3[3];
			}
			return $sipPeerInfo_arr;
			
		}elseif($channelType == 'IAX2'){
			$iax2Peer = $peer;
			$iax2Peer_count = count($iax2Peer);
			$iax2PeerInfo_arr['iax2Peer_count'] = $iax2Peer_count -3;
			$iax2PeerInfo_string = $iax2Peer[$iax2Peer_count -2];
			$iax2PeerInfo_arr2 = explode('[',$iax2PeerInfo_string);
			$iax2PeerInfo_arr3 = explode(' ',$iax2PeerInfo_arr2[1]);
			$iax2PeerInfo_arr['online'] = $iax2PeerInfo_arr3[0];
			$iax2PeerInfo_arr['offline'] = $iax2PeerInfo_arr3[2];
			$iax2PeerInfo_arr['unmonitored'] = $iax2PeerInfo_arr3[4];
			return $iax2PeerInfo_arr;
		}
	}
}

function buildAsteriskInfo($astver){
	global $astman;
	$uptime = _("Uptime: ");
	$activesipchannels = _("Active SIP Channel(s): ");
	$activeiax2channels = _("Active IAX2 Channel(s): ");
	$sipregistry = _("Sip Registry: ");
	$iax2registry = _("IAX2 Registry: ");
	$sippeers = _("Sip Peers: ");
	$iax2peers = _("IAX2 Peers: ");

	
	$arr = array(
		$uptime => "show uptime",
		$activesipchannels => "sip show channels",
		$activeiax2channels => "iax2 show channels",
		$sipregistry => "sip show registry",
		$iax2registry => "iax2 show registry",
		$sippeers => "sip show peers",
		$iax2peers => "iax2 show peers",	
	);
	
	if (version_compare($astver, '1.4', 'ge')) {
		$arr[$uptime] = 'core show uptime';
	}
	
	$htmlOutput  = '<div style="color:#000000;font-size:12px;margin:10px;">';
	$htmlOutput  .= '<table border="1" cellpadding="10">';

	foreach ($arr as $key => $value) {
	
		$response = $astman->send_request('Command',array('Command'=>$value));
		$astout = explode("\n",$response['data']);

		switch ($key) {
			case $uptime:
				$uptime = $astout;
				$htmlOutput .= '<tr><td colspan="2">Asterisk '.$uptime[1]."<br />".$uptime[2]."<br /></td>";
				$htmlOutput .= '</tr>';
			break;
			case $activesipchannels:
				$activeSipChannel = $astout;
				$activeSipChannel_count = getActiveChannel($activeSipChannel, $channelType = 'SIP');
				$htmlOutput .= '<tr>';
				$htmlOutput .= "<td>".$key.$activeSipChannel_count."</td>";
			break;
			case $activeiax2channels:
				$activeIAX2Channel = $astout;
				$activeIAX2Channel_count = getActiveChannel($activeIAX2Channel, $channelType = 'IAX2');
				$htmlOutput .= "<td>".$key.$activeIAX2Channel_count."</td>";
				$htmlOutput .= '</tr>';
			break;
			break;
			case $sipregistry:
				$sipRegistration = $astout;
				$sipRegistration_count = getRegistration($sipRegistration, $channelType = 'SIP');
				$htmlOutput .= '<tr>';
				$htmlOutput .= "<td>".$key.$sipRegistration_count."</td>";
			break;
			case $iax2registry:
				$iax2Registration = $astout;
				$iax2Registration_count = getRegistration($iax2Registration, $channelType = 'IAX2');
				$htmlOutput .= "<td>".$key.$iax2Registration_count."</td>";
				$htmlOutput .= '</tr>';
			break;
			case $sippeers:
				$sipPeer = $astout;
				$sipPeer_arr = getPeer($sipPeer, $channelType = 'SIP');
				if($sipPeer_arr['offline'] != 0){
					$sipPeerColor = 'red';
				}else{
					$sipPeerColor = '#000000';
				}
				$htmlOutput .= '<tr>';
			  if (version_compare($astver, '1.4', 'ge')) { 
				  $htmlOutput .= "<td>".$key."<br />&nbsp;&nbsp;&nbsp;&nbsp;"._("Online: ").$sipPeer_arr['online']."<br />&nbsp;&nbsp;&nbsp;&nbsp;"._("Online-Unmonitored: ").$sipPeer_arr['online-unmonitored'];
          $htmlOutput .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;"._("Offline: ")."<span style=\"color:".$sipPeerColor.";font-weight:bold;\">".$sipPeer_arr['offline']."</span><br />&nbsp;&nbsp;&nbsp;&nbsp;"._("Offline-Unmonitored: ")."<span style=\"color:".$sipPeerColor.";font-weight:bold;\">".$sipPeer_arr['offline-unmonitored']."</span></td>";
        } else {
				  $htmlOutput .= "<td>".$key."<br />&nbsp;&nbsp;&nbsp;&nbsp;"._("Online: ").$sipPeer_arr['online']."<br />&nbsp;&nbsp;&nbsp;&nbsp;"._("Offline: ")."<span style=\"color:".$sipPeerColor.";font-weight:bold;\">".$sipPeer_arr['offline']."</span></td>";
        }
			break;
			case $iax2peers:
				$iax2Peer = $astout;
				$iax2Peer_arr = getPeer($iax2Peer, $channelType = 'IAX2');
				if($iax2Peer_arr['offline'] != 0){
					$iax2PeerColor = 'red';
				}else{
					$iax2PeerColor = '#000000';
				}
				$htmlOutput .= "<td>".$key."<br />&nbsp;&nbsp;&nbsp;&nbsp;"._("Online: ").$iax2Peer_arr['online']."<br />&nbsp;&nbsp;&nbsp;&nbsp;"._("Offline: ")."<span style=\"color:".$iax2PeerColor.";font-weight:bold;\">".$iax2Peer_arr['offline']."</span><br />&nbsp;&nbsp;&nbsp;&nbsp;"._("Unmonitored: ").$iax2Peer_arr['unmonitored']."</td>";
				$htmlOutput .= '</tr>';
			break;
			default:
			}
		}
	$htmlOutput .= '</table>';
	return $htmlOutput."</div>";
	}
?>
