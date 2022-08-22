<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright (C) 2005, Xorcom
//	Copyright 2013 Schmooze Com Inc.
//	Copyright 2022 Issabel Foundation

$txtCommand = isset($_POST['txtCommand'])?$_POST['txtCommand']:'';

$tabindex = 0;
?>
<div class='content'>
<h2><?php echo _("Asterisk CLI")?></h2>

<form action="config.php?type=tool&display=cli" method="POST" enctype="multipart/form-data" name="frmExecPlus">

<div class="field has-addons">
  <!--label class="label"><?php echo _("Command:")?></label-->
  <div class="control is-expanded">
    <input class="input" type="text" autofocus name="txtCommand" placeholder="core show channels" value="<?php htmlspecialchars($txtCommand);?>" tabindex="<?php echo ++$tabindex;?>">
  </div>
  <div class="control">
    <button class="button is-info">
       <?php echo _("Execute"); ?>
    </button>
  </div>
</div>
	<!--table class='table is-borderless is-narrow'>
		<tr>
			<td class="label" align="right"><?php echo _("Command:")?></td>
			<td class="type"><input name="txtCommand" type="text" class="input w100" value="<?php htmlspecialchars($txtCommand);?>" tabindex="<?php echo ++$tabindex;?>"></td>
		</tr>
		
		<tr>
			<td valign="top">   </td>
			<td valign="top" class="label">
				<input type="submit" class="button is-small is-rounded" value="<?php echo _("Execute")?>" tabindex="<?php echo ++$tabindex;?>">
			</td>
		</tr>
		
		<tr>
			<td height="8"></td>
			<td></td>
		</tr>
	</table-->
</form>
</div>
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

