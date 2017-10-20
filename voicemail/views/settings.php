<table border='0' cellpadding='0.3px' cellspacing='2px'>
	<?php if (!empty($extension)) { ?>
		<tr>
			<td>
				<h5><?php echo _("Account View Links:") ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h5>
			</td>
			<td colspan='2'>
				<h5>
					<a style='<?php echo $action == 'bsettings' ? 'color:#ff9933;' : ''?>' href='config.php?display=voicemail&amp;action=bsettings&amp;ext=<?php echo $extension ?>'><?php echo _("Settings") ?></a>
					&nbsp;&nbsp;|&nbsp;&nbsp;
					<a style='<?php echo $action == 'usage' ? 'color:#ff9933;' : ''?>' href='config.php?display=voicemail&amp;action=usage&amp;ext=<?php echo $extension ?>'><?php echo _("Usage") ?></a>
					&nbsp;&nbsp;|&nbsp;&nbsp;
					<a style='<?php echo $action == 'settings' ? 'color:#ff9933;' : ''?>' href='config.php?display=voicemail&amp;action=settings&amp;ext=<?php echo $extension ?>'><?php echo _("Advanced Settings") ?></a>
				</h5>
			</td>
		</tr>
		<tr>
			<td colspan='2'>
				<hr />
			</td>
		</tr>
	<?php } else { ?>
		<tr>
			<td colspan='2'>
				<hr />
			</td>
		</tr>
	<?php } ?>