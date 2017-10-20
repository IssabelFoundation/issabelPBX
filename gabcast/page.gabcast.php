<?php 
/* $Id:$ */
//This program is free software; you can redistribute it and/or
//modify it under the terms of the GNU General Public License
//as published by the Free Software Foundation; either version 2
//of the License, or (at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.

$iam = 'gabcast'; //used for switch on config.php
$type = 'tool';

// look for form post
isset($_POST['action'])?$postaction = $_POST['action']:$postaction='';

switch ($postaction) {
	case "add":
		gabcast_add($_POST['ext'],$_POST['channbr'],$_POST['pin']);
		needreload();
		redirect_standard();
	break;
	case "delete":
		gabcast_del($_POST['ext']);
		needreload();
		redirect_standard();
	break;
	case "edit":
		gabcast_edit($_POST['ext'],$_POST['channbr'],$_POST['pin']);
		needreload();
		redirect_standard('ext');
	break;
}

// look for get
isset($_GET['action'])?$action = $_GET['action']:$action='';
isset($_GET['ext'])?$ext=$_GET['ext']:$ext='';

switch ($action) {
	case "add":
		gabcast_sidebar($ext, $type, $iam);
		gabcast_show(null, $type, $iam);
	break;
	case "edit":
		gabcast_sidebar($ext, $type, $iam);
		gabcast_show($ext, $type, $iam);
	break;
	default:
		gabcast_sidebar(null, $type, $iam);
		gabcast_text();
	break;
}


function gabcast_text() {

    echo "<div>";
    echo _("This ");
    echo "<a class=info href=\"http://www.gabcast.com\" target=_new>Gabcast<span>";
    echo _("Gabcast is a social broadcasting platform that offers virtual communities, individuals, and organizations an easy way to create and distribute audio content.<br><br>Visit www.gabcast.com for more info.");
    echo "</span>";
    echo "</a> "._("module allows you to:");
    echo "<ul><li>";
    echo _("Link extensions to Gabcast channels. It creates a feature code, which defaults to <u>*422</u> 'gab' - you can change this in");
    echo "&nbsp;<a href=\"config.php?type=setup&display=featurecodeadmin\">&nbsp;"._("Feature Code Admin").",&nbsp;</a>";
    echo _("which allows you to log directly into your Gabcast account.  This is ideal for personal podcasting!");
    echo "<li>";
    echo _("Define a Gabcast channel as a Destination for other modules. For example, you can direct a DID or IVR menu option directly to Gabcast. This is ideal for group and public podcasting!");
    echo "</ul>";
    echo "</div>";
    echo "<div style=;margin-top:20px;>";
    echo gabcast_player();
    echo "</div>";
}

function gabcast_show($xtn, $type, $iam) {
	
	//get settings if editing
	if(!empty($xtn)) {
		$thisxtn = gabcast_get($xtn);
		if(!is_array($thisxtn)) {
			echo _("Error: cannot retreive Gabcast info for this extension");
			return;
		}
		$player = gabcast_player($thisxtn[1]);
		$action = 'edit';

?>		
		<form method="POST" action="{$_SERVER['PHP_SELF']}?type={$type}&display={$iam}">
		<input type="hidden" name="action" value="delete">
		<input type="hidden" name="ext" value="{$thisxtn[0]}">
		<input type="submit" value="<?php echo _('Delete settings');?>"></form>
<?php
	}
		$usage_list = framework_display_destination_usage(gabcast_getdest($xtn));
		if (!empty($usage_list)) {
		?>
			<a href="#" class="info"><?php echo $usage_list['text']?>:<span><?php echo $usage_list['tooltip']?></span></a>
		<?php
		}
	echo "<hr>";
	
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'add') {
		$thisxtn['ext'] = $_REQUEST['ext'];
		$thisxtn[0] = $_REQUEST['ext'];
		$player = "";
		$action = 'add';
		
		echo "<div style=margin-bottom:10px;>";

		echo _("You <u>must</u> have a Gabcast account & channel to use this feature. Visit");
		echo "&nbsp;<a href=\"http://www.gabcast.com\" target=\"_blank\">www.gabcast.com</a>&nbsp;";
		echo _("to sign up. <u>It's a free service</u>!");
		echo "</div>";
	}
	
	echo "<form method=\"POST\" action=\"{$_SERVER['PHP_SELF']}?type={$type}&display={$iam}&action={$action}\">";
	echo "<input type=\"hidden\" name=\"action\" value=\"{$action}\"><div>";
	echo _("Gabcast Channel Number:");
	echo "<input size=\"10\" type=\"text\" name=\"channbr\" value=\"{$thisxtn[1]}\"></div><dic>";
	echo _("Gabcast Channel Password:");
	echo "<input size=\"10\" type=\"text\" name=\"pin\" value=\"{$thisxtn[2]}\"></div><div>";
	echo _("Link to Extension/User Number:");
	echo "<input size=\"10\" type=\"text\" name=\"ext\" value=\"{$thisxtn[0]}\"></div><div>";
	echo "<input type=\"submit\"></div><div style=\"margin-top:20px;\">";
	echo "{$player}";
	echo "</div></form>";
}

function gabcast_sidebar($sel, $type, $iam) {
        echo "</div><div class='rnav'>\n";
        echo "<ul><li><a id='".($sel==''?'current':'std')."' ";
        echo "href='config.php?type={$type}&amp;display={$iam}&amp;action=add'>"._("Add Gabcast Channel")."</a></li>";
        //get the list of paging groups
        $resarr = gabcast_list();
        if ($resarr) {
                foreach ($resarr as $resar) {
                        $cursel = $resar[0];
                        echo "<li><a id=\"".($sel==$cursel ? 'current':'std');
                        echo "\" href=\"config.php?type=${type}&amp;display=";
                        echo "${iam}&amp;ext=${cursel}&amp;action=edit\">";
                        echo _("Ext")." ${cursel} -> "._("Chan")." ${resar[1]} </a></li>";
                }
        }
        echo "</ul></div><div class='content'><h2>"._("Gabcast Configuration")."</h2>\n";
}

function gabcast_player($chanid = false) {
	if ($chanid) {
		$title = "<h4>".sprintf(_("The latest episodes in channel #%s"),$chanid).":</h4>";
		$feed = "http://www.gabcast.com/casts/{$chanid}/rss/rss.xml";
	} else {
		$title = "<h4>"._("The latest episodes across all channels").":</h4>";
		$feed = "http://www.gabcast.com/casts/feeds/latest.xml";
	}
	
	return $title.'<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="300" height="300" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0"><param name="movie" value="http://www.gabcast.com/mp3play/mp3player.swf?file='.$feed.'&config=http://www.gabcast.com/mp3play/config.php?ini=full.0.l" /><param name="wmode" value="transparent" /><param name="allowScriptAccess" value="always"><embed src="http://www.gabcast.com/mp3play/mp3player.swf?file='.$feed.'&config=http://www.gabcast.com/mp3play/config.php?ini=full.0.l" allowScriptAccess="always" wmode="transparent" width="300" height="300" name="mp3player" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed></object><br><br><div>'._('Feed URL: ').'<a href="'.$feed.'" target=_blank>'.$feed.'</a></div>';
	
}
?>

