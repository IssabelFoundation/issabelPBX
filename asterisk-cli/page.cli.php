<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright (C) 2005, Xorcom
//	Copyright 2013 Schmooze Com Inc.
//
$txtCommand = isset($_POST['txtCommand'])?$_POST['txtCommand']:'';

$tabindex = 0;
?>

<h2><?php echo _("Asterisk CLI")?></h2>

<form action="config.php?type=tool&display=cli" method="POST" enctype="multipart/form-data" name="frmExecPlus">
	<table>
		<tr>
			<td class="label" align="right"><?php echo _("Command:")?></td>
			<td class="type"><input name="txtCommand" type="text" size="70" value="<?php htmlspecialchars($txtCommand);?>" tabindex="<?php echo ++$tabindex;?>"></td>
		</tr>
		
		<tr>
			<td valign="top">   </td>
			<td valign="top" class="label">
				<input type="submit" class="button" value="<?php echo _("Execute:")?>" tabindex="<?php echo ++$tabindex;?>">
			</td>
		</tr>
		
		<tr>
			<td height="8"></td>
			<td></td>
		</tr>
	</table>
</form>

<p>
<?php if (isBlank($txtCommand)): ?>
</p>
<?php endif; 

function isBlank( $arg ) { return (trim($arg) == ''); }

if (!isBlank($txtCommand))
{
	$html_out = cli_runcommand($txtCommand);
	echo $html_out;
}

?>

