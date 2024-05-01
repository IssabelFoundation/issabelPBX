<table class='table is-striped mt-0 notfixed'>
	<tr>
		<td>
			<a href='#' class='info'><?php echo __("Number of Messages") ?><span><?php echo __("Total ( Messages in inboxes / Messages in other folders )") ?></span></a>
		</td>
		<td><?php echo $msg_total ?>(<?php echo $msg_in ?>/<?php echo $msg_other ?>)</td>
		<td>
			<input type='checkbox' name='del_msgs' id='del_msgs' value='true' /><a href='#' class='info'><?php echo __("Delete") ?><span><?php echo __("Remove all messages") ?></span></a>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo __("Recorded Name") ?><span><?php echo __("Has a recorded name greeting?") ?></span></a>
		</td>
		<td><?php if(!empty($name_ts)) { ?><a href='#' class='info'><?php echo __("yes") ?><span><?php echo __("File timestamp: ") . $name_ts; } else { echo __("no"); }?></span></a>
		</td>
		<td>
			<input type='checkbox' name='del_names' id='del_names' value='true' /><a href='#' class='info'><?php echo __("Delete") ?><span><?php echo __("Remove recorded name") ?></span></a>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo __("Unavailable Greeting") ?><span><?php echo __("Has a recorded unavailable greeting?") ?></span></a>
		</td>
		<td><?php if(!empty($unavail_ts)) { ?><a href='#' class='info'><?php echo __("yes") ?><span><?php echo __("File timestamp: ") . $unavail_ts; } else { echo __("no"); }?></td>
		<td>
			<input type='checkbox' name='del_unavail' id='del_unavail' value='true' /><a href='#' class='info'><?php echo __("Delete") ?><span><?php echo __("Remove unavailable greeting") ?></span></a>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo __("Busy Greetings") ?><span><?php echo __("Has a recorded busy greeting?") ?></span></a>
		</td>
		<td><?php if(!empty($busy_ts)) { ?><a href='#' class='info'><?php echo __("yes") ?><span><?php echo __("File timestamp: ") . $busy_ts; } else { echo __("no"); }?></td>
		<td>
			<input type='checkbox' name='del_busy' id='del_busy' value='true' /><a href='#' class='info'><?php echo __("Delete") ?><span><?php echo __("Remove busy greeting") ?></span></a>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo __("Temporary Greeting") ?><span><?php echo __("Has a recorded temporary greeting?") ?></span></a>
		</td>
		<td><?php if(!empty($temp_ts)) { ?><a href='#' class='info'><?php echo __("yes") ?><span><?php echo __("File timestamp: ") . $temp_ts; } else { echo __("no"); }?></td>
		<td>
			<input type='checkbox' name='del_temp' id='del_temp' value='true' /><a href='#' class='info'><?php echo __("Delete") ?><span><?php echo __("Remove temporary greeting") ?></span></a>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo __("Abandoned Greetings") ?><span><?php echo __("Number of abandoned greetings. Such greetings were recorded by the user but were NOT accepted, so the sound file remains on disk but is not used as a greeting.") ?></span></a>
		</td>
		<td><?php echo $abandoned; ?></td>
		<td>
			<input type='checkbox' name='del_abandoned' id='del_abandoned' value='true' /><a href='#' class='info'><?php echo __("Delete") ?><span><?php echo __("Remove all abandoned greetings (> 1 day old)") ?></span></a>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo __("Storage Used") ?><span><?php echo __("Disk space currently in use by Voicemail data") ?></span></a>
		</td>
		<td><?php echo $storage; ?></td>
	</tr>
</table>
<?php echo form_action_bar(''); ?>
