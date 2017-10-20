<?php 
/* $Id: */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

//Both of these are used for switch on config.php
$display = isset($_REQUEST['display'])?$_REQUEST['display']:'weakpasswords';

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$email = isset($_REQUEST['email'])?$_REQUEST['email']:'';

?>
<p>
<?php
	echo "<table cellpadding=5><tr><td colspan=3><div class='content'><h2>"._("Weak Password Detection")."</h2></td></tr>\n";
	echo "<tr><td><b>"._("Type")."</b></td><td><b>"._("Name")."</b></td><td><b>"._("Secret")."</b></td><td><b>"._("Message")."</b></td></tr>";
	$weak = weakpasswords_get_users();
	if(sizeof($weak) > 0)  {
		foreach ($weak as $details) {
			echo '<tr><td>'.$details['deviceortrunk'].'</td><td>'.$details['name'].'</td><td>'.$details['secret'].'</td><td>'.$details['message']."</td></tr>";
		}
	} else  {
		echo "<tr><td colspan=3>"._("No weak secrets detected on this system.")."</td></tr>";
	}
	// implementation of module hook
	// object was initialized in config.php
	echo $module_hook->hookHtml;
?>
	</table>
