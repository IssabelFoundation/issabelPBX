<div class='content'>
	<form name='frm_voicemail' action='<?php $_SERVER['PHP_SELF'] ?>' method='post'>
		<input type='hidden' name='type' id='type' value='<?php echo $type ?>' />
		<input type='hidden' name='display' id='display' value='<?php echo $display ?>' />
		<input type='hidden' name='ext' id='ext' value='<?php echo $extension ?>' />
		<input type='hidden' name='page_type' id='page_type' value='<?php echo $action ?>' />
		<table border='0' cellpadding='0.3px' cellspacing='2px'>
			<tr>
				<td colspan='3'><?php echo $title?></td>
			</tr>
			<tr>
				<td>
					<h5><?php echo _("System View Links:") ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h5>
				</td>
				<td colspan='2'>
					<h5>
						<a style="<?php echo ($sys_view_flag && $action == "dialplan") ? 'color:#ff9933' : ''?>" href='config.php?display=voicemail&amp;action=dialplan'>Dialplan Behavior</a>&nbsp;&nbsp;|&nbsp;&nbsp;
						<a style="<?php echo ($sys_view_flag && $action == "settings") ? 'color:#ff9933' : ''?>" href='config.php?display=voicemail&amp;action=settings'>Settings</a>&nbsp;&nbsp;|&nbsp;&nbsp;
						<a style="<?php echo ($sys_view_flag && $action == "usage") ? 'color:#ff9933' : ''?>" href='config.php?display=voicemail&amp;action=usage'>Usage</a>&nbsp;&nbsp;|&nbsp;&nbsp;
						<a style="<?php echo ($sys_view_flag && $action == "tz") ? 'color:#ff9933' : ''?>" href='config.php?display=voicemail&amp;action=tz'>Timezone Definitions</a>
					</h5>
				</td>
			</tr>
		</table>