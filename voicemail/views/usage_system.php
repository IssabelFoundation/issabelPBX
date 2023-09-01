<table class='table is-striped mt-2 notfixed'>
	<tr>
		<td>
			<a href='#' class='info'><?php echo __("Number of Accounts") ?><span><?php echo __("Total ( Activated / Unactivated / Disabled )") ?></span></a>
		</td>
		<td><?php echo $acts_total ?>(<?php echo $acts_act ?>/<?php echo $acts_unact ?>/<?php echo $disabled_count ?>)</td>
	</tr>
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
			<a href='#' class='info'><?php echo __("Recorded Names") ?><span><?php echo __("Number of recorded name greetings") ?></span></a>
		</td>
		<td><?php echo $name ?></td>
		<td>
			<input type='checkbox' name='del_names' id='del_names' value='true' /><a href='#' class='info'><?php echo __("Delete") ?><span><?php echo __("Remove all recorded names") ?></span></a>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo __("Unavailable Greetings") ?><span><?php echo __("Number of recorded unavailable greetings") ?></span></a>
		</td>
		<td><?php echo $unavail ?></td>
		<td>
			<input type='checkbox' name='del_unavail' id='del_unavail' value='true' /><a href='#' class='info'><?php echo __("Delete") ?><span><?php echo __("Remove all unavailable greetings") ?></span></a>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo __("Busy Greetings") ?><span><?php echo __("Number of recorded busy greetings") ?></span></a>
		</td>
		<td><?php echo $busy ?></td>
		<td>
			<input type='checkbox' name='del_busy' id='del_busy' value='true' /><a href='#' class='info'><?php echo __("Delete") ?><span><?php echo __("Remove all busy greetings") ?></span></a>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo __("Temporary Greetings") ?><span><?php echo __("Number of recorded temporary greetings") ?></span></a>
		</td>
		<td><?php echo $temp ?></td>
		<td>
			<input type='checkbox' name='del_temp' id='del_temp' value='true' /><a href='#' class='info'><?php echo __("Delete") ?><span><?php echo __("Remove all temporary greetings") ?></span></a>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo __("Abandoned Greetings") ?><span><?php echo __("Number of abandoned greetings. Such greetings were recorded by the user but were NOT accepted, so the sound file remains on disk but is not used as a greeting.") ?></span></a>
		</td>
		<td><?php echo $abandoned ?></td>
		<td>
			<input type='checkbox' name='del_abandoned' id='del_abandoned' value='true' /><a href='#' class='info'><?php echo __("Delete") ?><span><?php echo __("Remove all abandoned greetings (> 1 day old)") ?></span></a>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo __("Storage Used") ?><span><?php echo __("Disk space currently in use by Voicemail data") ?></span></a>
		</td>
		<td colspan=2><?php echo $storage ?></td>
	</tr>
</table>
<?php echo form_action_bar(''); ?>
