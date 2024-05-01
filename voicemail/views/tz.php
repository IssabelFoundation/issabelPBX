<div class='box'>
<table class='table is-borderless mt-2 notfixed'>
	<tr>
		<td colspan=2>
			<?php echo __("A timezone definition specifies how the Voicemail system announces the time.") ?>
        </td>
	</tr>
	<tr>
		<td colspan=2>
			<?php echo  __("For example, the time a message was left will be announced according to the user's timezone on message playback.") ?>
		</td>
	</tr>
	<tr>
		<td colspan=2>
			<b><?php echo __("Entries below will be written to Voicemail configuration as-is.") ?></b>
		</td>
	</tr>
	<tr>
		<td colspan=2>
			<b><?php echo __("Please be sure to follow the format for timezone definitions described below.") ?></b>
		</td>
    </tr>

</table>
</div>
<div class='box'>
<table class='table is-borderless mt-2 notfixed'>

	<tr>
		<td>
			<a href='#' class='info'><?php echo __("Name") ?><span><?php echo $tooltips["tz"]["name"] ?></span></a>
		</td>
		<td>
			<a href='#' class='info'><?php echo __("Timezone Definition") ?><span><?php echo $tooltips["tz"]["def"] ?></span></a>
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
				<a href='#' class='info'><?php echo __("Delete") ?><span><?php echo $tooltips["tz"]["del"] ?></span></a>
			</td>
		</tr>
	<?php } ?>
	<tr>
		<td>
			<a href='#' class='info'>
				<?php echo __("New Name") ?><span><?php echo $tooltips["tz"]["name"] ?></span></a>
			</td>
			<td>
				<a href='#' class='info'><?php echo __("New Timezone Definition") ?><span><?php echo $tooltips["tz"]["def"] ?></span></a>
			</td>
	<tr>
		<td>
			<input class='input' style='width:10em;' type='text' name='tznew_name' id='tznew_name' tabindex='1' value='' />
		</td>
		<td>
			<input class='input' type='text' name='tznew_def' id='tznew_def' tabindex='1' value='' />
		</td>
    </tr>
</table>
</div>

<div class='box'>
    <table class='table is-narrow is-borderless'>
	<tr>
		<td style='max-width: 60px' colspan='2'>
			<?php echo __("Timezone definition format is: ") ?>&nbsp;&nbsp;<b style='font-family:courier;'><?php echo __("timezone|values")?></b>
		</td>
		<td>
			
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px' colspan='2'>
			<br /><b><?php echo __("<i>Timezones</i> are listed in /usr/share/zoneinfo")?>
		</td>
	</tr>
	
	<tr>
		<td style='max-width: 60px' colspan='2'>
			<b><?php echo __("The <i>values</i> supported in the timezone definition string include:")?></b>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __("'filename'")?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo __("The name of a sound file (the file name must be single-quoted)")?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __("variable")?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo __("A variable to be substituted (see below for supported variable values)")?>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px' colspan='2'>
			<b><?php echo __("Supported <i>variables</i>:")?></b>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px'>
			<?php echo __("A or a") 	?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo __("Day of week (Saturday, Sunday, ...)")?>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px'>
			<?php echo __("B or b or h")?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo __("Month name (January, February, ...)")?>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px'>
			<?php echo __("d or e") 	?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo __("numeric day of month (first, second, ..., thirty-first)")?>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px'>
			<?php echo __("Y")?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo __("Year")?>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px'>
			<?php echo __("I or l") 	?>
		</td><td style='max-width: 60px' colspan='2'>
			<?php echo __("Hour, 12 hour clock")?>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px'>
			<?php echo __("H")?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo __("Hour, 24 hour clock (single digit hours preceded by \"oh\")")?>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px'>
			<?php echo __("k")?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo __("Hour, 24 hour clock (single digit hours NOT preceded by \"oh\")")?>
		</td>
	</tr>
	<tr>
		<td style='max-width: 60px'>
			<?php echo __("M")?>
		</td>
		<td style='max-width: 60px' colspan='2'>
			<?php echo __("Minute, with 00 pronounced as \"o'clock\"")?>
		</td></tr>
		<tr>
			<td style='max-width: 60px'>
				<?php echo __("N")?>
			</td>
			<td style='max-width: 60px' colspan='2'>
				<?php echo __("Minute, with 00 pronounced as \"hundred\" (US military time)")?>
			</td>
		</tr>
		<tr>
			<td style='max-width: 60px'>
				<?php echo __("P or p") 	?>
			</td>
			<td style='max-width: 60px' colspan='2'>
				<?php echo __("AM or PM")?>
			</td>
		</tr>
		<tr>
			<td style='max-width: 60px'>
				<?php echo __("Q")?>
			</td>
			<td style='max-width: 60px' colspan='2'>
				<?php echo __("\"today\", \"yesterday\" or ABdY")?>
			</td>
		</tr>
		<tr>
			<td style='max-width: 60px'>
				<?php echo __("q")?>
			</td>
			<td style='max-width: 60px' colspan='2'>
				<?php echo __("\"\" (for today), \"yesterday\", weekday, or ABdY")?>
			</td>
		</tr>
		<tr>
			<td style='max-width: 60px'>
				<?php echo __("R")?>
			</td>
			<td style='max-width: 60px' colspan='2'>
				<?php echo __("24 hour time, including minute")?>
			</td>
        </tr>
    <tr class='is-hidden'>
        <td colspan='2'>
            <input type='hidden' name='action' id='action' value='<?php echo __('Submit');?>' />
        </td>
    </tr>
	</tr>
</table>
</div>
<script>
<?php echo js_display_confirmation_toasts(); ?>
</script>
</div>
<?php echo form_action_bar(''); ?>

