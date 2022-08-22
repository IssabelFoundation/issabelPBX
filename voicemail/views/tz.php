<table class='table is-borderless mt-2'>
	<tr>
		<td style='max-width: 60px' colspan='2'>
			<?php echo _("A timezone definition specifies how the Voicemail system announces the time.") ?>
		</td>
		<td>
			
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px' colspan='2'>
			<?php echo  _("For example, the time a message was left will be announced according to the user's timezone on message playback.") ?>
		</td>
		<td>
			
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px' colspan='2'>
			<b><?php echo _("Entries below will be written to Voicemail configuration as-is.") ?></b>
		</td>
		<td>
			
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px' colspan='2'>
			<b><?php echo _("Please be sure to follow the format for timezone definitions described below.") ?></b>
		</td>
	</tr>
	<tr>
		<td>
			<a href='#' class='info'><?php echo _("Name") ?><span><?php echo $tooltips["tz"]["name"] ?></span></a>
		</td>
		<td>
			<a href='#' class='info'><?php echo _("Timezone Definition") ?><span><?php echo $tooltips["tz"]["def"] ?></span></a>
		</td>
	</tr>
	<?php foreach ($settings as $key => $val) { ?>
		<tr>
			<td><?php echo $key ?></td>
			<td>
				<input class="input" type='text' name='tz__<?php echo $key ?>' id='tz__<?php echo $key ?>' tabindex='1' value="<?php echo htmlentities($val) ?>" />
				&nbsp;&nbsp;&nbsp;&nbsp;
				<input type='checkbox' name='tzdel__<?php echo $key ?>' id='tzdel__<?php echo $key ?>' value='true' />
				&nbsp;&nbsp;
				<a href='#' class='info'><?php echo _("Delete") ?><span><?php echo $tooltips["tz"]["del"] ?></span></a>
			</td>
		</tr>
	<?php } ?>
	<tr>
		<td>
			<a href='#' class='info'>
				<?php echo _("New Name") ?><span><?php echo $tooltips["tz"]["name"] ?></span></a>
			</td>
			<td>
				<a href='#' class='info'><?php echo _("New Timezone Definition") ?><span><?php echo $tooltips["tz"]["def"] ?></span></a>
			</td>
	<tr>
		<td>
			<input class='input' style='width:10em;' type='text' name='tznew_name' id='tznew_name' tabindex='1' value='' />
		</td>
		<td>
			<input class='input' type='text' name='tznew_def' id='tznew_def' tabindex='1' value='' />
		</td>
	</tr>

	<tr>
		<td colspan='2'>
			<hr />
		</td>
	</tr>
	
	<tr>
		<td style='max-width: 60px' colspan='2'>
			<?php echo _("Timezone definition format is: ") ?>&nbsp;&nbsp;<b style='font-family:courier;'><?php echo _("timezone|values")?></b>
		</td>
		<td>
			
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px' colspan='2'>
			<br /><b><?php echo _("<i>Timezones</i> are listed in /usr/share/zoneinfo")?>
		</td>
	</tr>
	
	<tr>
		<td style='max-width: 60px' colspan='2'>
			<b><?php echo _("The <i>values</i> supported in the timezone definition string include:")?></b>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo _("'filename'")?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo _("The name of a sound file (the file name must be single-quoted)")?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo _("variable")?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo _("A variable to be substituted (see below for supported variable values)")?>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px' colspan='2'>
			<b><?php echo _("Supported <i>variables</i>:")?></b>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px'>
			<?php echo _("A or a") 	?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo _("Day of week (Saturday, Sunday, ...)")?>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px'>
			<?php echo _("B or b or h")?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo _("Month name (January, February, ...)")?>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px'>
			<?php echo _("d or e") 	?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo _("numeric day of month (first, second, ..., thirty-first)")?>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px'>
			<?php echo _("Y")?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo _("Year")?>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px'>
			<?php echo _("I or l") 	?>
		</td><td style='max-width: 60px' colspan='2'>
			<?php echo _("Hour, 12 hour clock")?>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px'>
			<?php echo _("H")?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo _("Hour, 24 hour clock (single digit hours preceded by \"oh\")")?>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px'>
			<?php echo _("k")?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo _("Hour, 24 hour clock (single digit hours NOT preceded by \"oh\")")?>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px'>
			<?php echo _("M")?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo _("Minute, with 00 pronounced as \"o'clock\"")?>
		</td></tr>
		<tr>
			<td style='max-width: 60px'>
				<?php echo _("N")?>
			</td>
			<td style='max-width: 60px' colspan='2'>
				<?php echo _("Minute, with 00 pronounced as \"hundred\" (US military time)")?>
			</td>
		</tr>
		<tr>
			<td style='max-width: 60px'>
				<?php echo _("P or p") 	?>
			</td>
			<td style='max-width: 60px' colspan='2'>
				<?php echo _("AM or PM")?>
			</td>
		</tr>
		<tr>
			<td style='max-width: 60px'>
				<?php echo _("Q")?>
			</td>
			<td style='max-width: 60px' colspan='2'>
				<?php echo _("\"today\", \"yesterday\" or ABdY")?>
			</td>
		</tr>
		<tr>
			<td style='max-width: 60px'>
				<?php echo _("q")?>
			</td>
			<td style='max-width: 60px' colspan='2'>
				<?php echo _("\"\" (for today), \"yesterday\", weekday, or ABdY")?>
			</td>
		</tr>
		<tr>
			<td style='max-width: 60px'>
				<?php echo _("R")?>
			</td>
			<td style='max-width: 60px' colspan='2'>
				<?php echo _("24 hour time, including minute")?>
			</td>
        </tr>
    <tr class='is-hidden'>
        <td colspan='2'>
            <input type='hidden' name='action' id='action' value='<?php echo _('Submit');?>' />
        </td>
    </tr>
	</tr>
</table>
<script>
<?php echo js_display_confirmation_toasts(); ?>
</script>
</div>
<?php echo form_action_bar(''); ?>

