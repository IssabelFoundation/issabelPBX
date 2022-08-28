<table class='table is-striped mt-0 notfixed'>
	<tr>
		<td>
			<a href='#' class='info'><?php echo _("Number of Messages") ?><span><?php echo _("Total ( Messages in inboxes / Messages in other folders )") ?></span></a>
		</td>
		<td><?php echo $msg_total ?>(<?php echo $msg_in ?>/<?php echo $msg_other ?>)</td>
		<td>
			<input type='checkbox' name='del_msgs' id='del_msgs' value='true' /><a href='#' class='info'><?php echo _("Delete") ?><span><?php echo _("Remove all messages") ?></span></a>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo _("Recorded Name") ?><span><?php echo _("Has a recorded name greeting?") ?></span></a>
		</td>
		<td><?php if(!empty($name_ts)) { ?><a href='#' class='info'><?php echo _("yes") ?><span><?php echo _("File timestamp: ") . $name_ts; } else { echo _("no"); }?></span></a>
		</td>
		<td>
			<input type='checkbox' name='del_names' id='del_names' value='true' /><a href='#' class='info'><?php echo _("Delete") ?><span><?php echo _("Remove recorded name") ?></span></a>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo _("Unavailable Greeting") ?><span><?php echo _("Has a recorded unavailable greeting?") ?></span></a>
		</td>
		<td><?php if(!empty($unavail_ts)) { ?><a href='#' class='info'><?php echo _("yes") ?><span><?php echo _("File timestamp: ") . $unavail_ts; } else { echo _("no"); }?></td>
		<td>
			<input type='checkbox' name='del_unavail' id='del_unavail' value='true' /><a href='#' class='info'><?php echo _("Delete") ?><span><?php echo _("Remove unavailable greeting") ?></span></a>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo _("Busy Greetings") ?><span><?php echo _("Has a recorded busy greeting?") ?></span></a>
		</td>
		<td><?php if(!empty($busy_ts)) { ?><a href='#' class='info'><?php echo _("yes") ?><span><?php echo _("File timestamp: ") . $busy_ts; } else { echo _("no"); }?></td>
		<td>
			<input type='checkbox' name='del_busy' id='del_busy' value='true' /><a href='#' class='info'><?php echo _("Delete") ?><span><?php echo _("Remove busy greeting") ?></span></a>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo _("Temporary Greeting") ?><span><?php echo _("Has a recorded temporary greeting?") ?></span></a>
		</td>
		<td><?php if(!empty($temp_ts)) { ?><a href='#' class='info'><?php echo _("yes") ?><span><?php echo _("File timestamp: ") . $temp_ts; } else { echo _("no"); }?></td>
		<td>
			<input type='checkbox' name='del_temp' id='del_temp' value='true' /><a href='#' class='info'><?php echo _("Delete") ?><span><?php echo _("Remove temporary greeting") ?></span></a>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo _("Abandoned Greetings") ?><span><?php echo _("Number of abandoned greetings. Such greetings were recorded by the user but were NOT accepted, so the sound file remains on disk but is not used as a greeting.") ?></span></a>
		</td>
		<td><?php echo $abandoned; ?></td>
		<td>
			<input type='checkbox' name='del_abandoned' id='del_abandoned' value='true' /><a href='#' class='info'><?php echo _("Delete") ?><span><?php echo _("Remove all abandoned greetings (> 1 day old)") ?></span></a>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo _("Storage Used") ?><span><?php echo _("Disk space currently in use by Voicemail data") ?></span></a>
		</td>
		<td><?php echo $storage; ?></td>
	</tr>
</table>
<?php echo form_action_bar(''); ?>
