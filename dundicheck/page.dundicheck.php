<?php /* $Id: page.ringgroups.php 5340 2007-12-04 19:10:53Z p_lindheimer $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$dispnum = 'dundicheck'; //used for switch on config.php
$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:'';
$type = isset($_REQUEST['type'])?$_REQUEST['type']:'tool';
$dundiconflict = isset($_REQUEST['dundiconflict'])?$_REQUEST['dundiconflict']:'';

echo "<div class='content'>\n";

if ($extdisplay != "") {
	echo "<h2>".sprintf(_("DUNDi Information: %s"),$extdisplay)."</h2>";
	if ($dundiconflict == 'true') {
		echo sprintf(_("The number you are trying to use, %s, is currently available from one of the DUNDi routes you have configured on your system. As a result you cannot use this number on this system. Even if the route configuration does not pass this number you will still be blocked from creating it. If this is not an error, then you will have to un-publish this number on your remote DUNDi setup, disable the DUNDi trunk in question, or disable this module to avoid the checks. Otherwise, remove %s from the remote system before creating this one."),$extdisplay,$extdisplay);
	}
	$list = dundicheck_lookup_all($extdisplay);
	if (empty($list)) {
		echo "<div class='notifications box'>"._("No matches found")."</div>";
	} else {
		foreach ($list as $map => $line) {
			echo "<div class='notifications box'>".sprintf(_("Results from DUNDi trunk: %s"),$map)."</div>";
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
<div class='box'>
<form name="dundicheck" id='mainform' method="post">
	<input type="hidden" name="display" value="<?php echo $dispnum?>">
	<input type="hidden" name="type" value="<?php echo $type?>">
	<table class='table is-borderless is-narrow notfixed'>
		<tr>
			<td><label><?php echo ($extdisplay == '')?_("Lookup Number"):_("Lookup Another Number")?></label>
			<input name="extdisplay" type="text" class="input" value="<?php htmlspecialchars($extdisplay);?>"></td>
            <td valign="top">
                <br/>
				<input type="submit" class="button" value="<?php echo _("Lookup")?>">
			</td>
		</tr>
		
		<tr>
			<td height="8"></td>
			<td></td>
		</tr>
	</table>
</form>

</div>
</div>
