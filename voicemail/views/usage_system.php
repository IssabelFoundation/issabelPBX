<table>
	<tr>
		<td colspan='3'>
			<br />
		</td>
	</tr>
	<tr>
		<td colspan='3'>
			<hr />
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo _("Number of Accounts:") ?><span><?php echo _("Total ( Activated / Unactivated / Disabled )") ?></span></a>
		</td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $acts_total ?>&nbsp;&nbsp;(&nbsp;<?php echo $acts_act ?>&nbsp;/&nbsp;<?php echo $acts_unact ?>&nbsp;/&nbsp;<?php echo $disabled_count ?>&nbsp;)</td>
	</tr>
	<tr>
		<td colspan='3'>
			<hr style='height:0.1px;' />
		</td>
	</tr>

	<tr>
		<td>
			<a href='#' class='info'><?php echo _("Number of Messages:") ?><span><?php echo _("Total ( Messages in inboxes / Messages in other folders )") ?></span></a>
		</td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $msg_total ?>&nbsp;&nbsp;(&nbsp;<?php echo $msg_in ?>&nbsp;/&nbsp;<?php echo $msg_other ?>&nbsp;)</td>
		<td>
			<input type='checkbox' name='del_msgs' id='del_msgs' value='true' />&nbsp;<a href='#' class='info'><?php echo _("Delete") ?><span><?php echo _("Remove all messages") ?></span></a>
		</td>
	</tr>
	<tr>
		<td colspan='3'>
			<hr style='height:0.1px;' />
		</td>
	</tr>

	<tr>
		<td>
			<a href='#' class='info'><?php echo _("Recorded Names:") ?><span><?php echo _("Number of recorded name greetings") ?></span></a>
		</td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $name ?></td>
		<td>
			<input type='checkbox' name='del_names' id='del_names' value='true' />&nbsp;<a href='#' class='info'><?php echo _("Delete") ?><span><?php echo _("Remove all recorded names") ?></span></a>
		</td>
	</tr>
	<tr>
		<td colspan='3'>
			<hr style='height:0.1px;' />
		</td>
	</tr>

	<tr>
		<td>
			<a href='#' class='info'><?php echo _("Unavailable Greetings:") ?><span><?php echo _("Number of recorded unavailable greetings") ?></span></a>
		</td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $unavail ?></td>
		<td>
			<input type='checkbox' name='del_unavail' id='del_unavail' value='true' />&nbsp;<a href='#' class='info'><?php echo _("Delete") ?><span><?php echo _("Remove all unavailable greetings") ?></span></a>
		</td>
	</tr>
	<tr>
		<td colspan='3'>
			<hr style='height:0.1px;' />
		</td>
	</tr>

	<tr>
		<td>
			<a href='#' class='info'><?php echo _("Busy Greetings:") ?><span><?php echo _("Number of recorded busy greetings") ?></span></a>
		</td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $busy ?></td>
		<td>
			<input type='checkbox' name='del_busy' id='del_busy' value='true' />&nbsp;<a href='#' class='info'><?php echo _("Delete") ?><span><?php echo _("Remove all busy greetings") ?></span></a>
		</td>
	</tr>
	<tr>
		<td colspan='3'>
			<hr style='height:0.1px;' />
		</td>
	</tr>

	<tr>
		<td>
			<a href='#' class='info'><?php echo _("Temporary Greetings:") ?><span><?php echo _("Number of recorded temporary greetings") ?></span></a>
		</td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $temp ?></td>
		<td>
			<input type='checkbox' name='del_temp' id='del_temp' value='true' />&nbsp;<a href='#' class='info'><?php echo _("Delete") ?><span><?php echo _("Remove all temporary greetings") ?></span></a>
		</td>
	</tr>
	<tr>
		<td colspan='3'>
			<hr style='height:0.1px;' />
		</td>
	</tr>

	<tr>
		<td>
			<a href='#' class='info'><?php echo _("Abandoned Greetings:") ?><span><?php echo _("Number of abandoned greetings. Such greetings were recorded by the user but were NOT accepted, so the sound file remains on disk but is not used as a greeting.") ?></span></a>
		</td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $abandoned ?></td>
		<td>
			<input type='checkbox' name='del_abandoned' id='del_abandoned' value='true' />&nbsp;<a href='#' class='info'><?php echo _("Delete") ?><span><?php echo _("Remove all abandoned greetings (> 1 day old)") ?></span></a>
		</td>
	</tr>
	<tr>
		<td colspan='3'>
			<hr style='height:0.1px;' />
		</td>
	</tr>

	<tr>
		<td>
			<a href='#' class='info'><?php echo _("Storage Used:") ?><span><?php echo _("Disk space currently in use by Voicemail data") ?></span></a>
		</td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $storage ?></td>
	</tr>
	<tr>
		<td colspan='3'>
			<hr style='height:0.1px;' />
		</td>
	</tr>
	<tr>
		<td>
			
		</td>
		<td colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='action' id='action' value='Submit' /></td>
	</tr>
</table>
