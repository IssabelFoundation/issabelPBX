<?php /* $Id: page.ringgroups.php 5340 2007-12-04 19:10:53Z p_lindheimer $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$dispnum = 'dundicheck'; //used for switch on config.php
$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:'';
$type = isset($_REQUEST['type'])?$_REQUEST['type']:'tool';
$dundiconflict = isset($_REQUEST['dundiconflict'])?$_REQUEST['dundiconflict']:'';


if ($extdisplay != "") {
	echo "<h2>".sprintf(_("DUNDi Information: %s"),$extdisplay)."</h2>";
	if ($dundiconflict == 'true') {
		echo sprintf(_("The number you are trying to use, %s, is currently available from one of the DUNDi routes you have configured on your system. As a result you cannot use this number on this system. Even if the route configuration does not pass this number you will still be blocked from creating it. If this is not an error, then you will have to un-publish this number on your remote DUNDi setup, disable the DUNDi trunk in question, or disable this module to avoid the checks. Otherwise, remove %s from the remote system before creating this one."),$extdisplay,$extdisplay);
	}
	$list = dundicheck_lookup_all($extdisplay);
	if (empty($list)) {
		echo "<h5>"._("No matches found")."</h5>";
	} else {
		foreach ($list as $map => $line) {
			echo "<h5>".sprintf(_("Results from DUNDi trunk: %s"),$map)."</h5>";
			$output = explode("\n",$line);
			unset($output[0]);
			foreach ($output as $item) {
				echo $item."<br />";
			}
		}
	}
?>

<?php
} else {
	echo "<h2>".sprintf(_("DUNDi Lookup"))."</h2>";
}

?>
<form name="dundicheck" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post">
	<input type="hidden" name="display" value="<?php echo $dispnum?>">
	<input type="hidden" name="type" value="<?php echo $type?>">
	<table>
		<tr>
			<td align="right"><?php echo ($extdisplay == '')?_("Lookup Number:"):_("Lookup Another Number:")?></td>
			<td class="type"><input name="extdisplay" type="text" size="12" value="<?php htmlspecialchars($extdisplay);?>"></td>
			<td valign="top">
				<input type="submit" class="button" value="<?php echo _("Lookup")?>">
			</td>
		</tr>
		
		<tr>
			<td height="8"></td>
			<td></td>
		</tr>
	</table>
</form>
