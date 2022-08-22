<table class='table is-borderless is-narrow'>
	<tr>
		<td colspan='2'>
			<h5><?php echo _("General Dialplan Settings") ?></h5>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo ipbx_label(_("Disable Standard Prompt"), _("Check this box to disable the standard voicemail instructions that follow the user recorded message. These standard instructions tell the caller to leave a message after the beep. This can be individually controlled for users who have VMX locater enabled."))?>
		</td>
        <td>
            <?php $checked = ($settings['VM_OPTS']=='s')?' checked="checked" ':'';?>
            <div class='field'><input type='checkbox' class='switch' id='VM_OPTS' name='VM_OPTS' value='s' <?php echo $checked;?> tabindex='<?php echo ++$tabindex;?>'/><label style='height:auto; line-height:1em; padding-left:3em;' for='VM_OPTS'>&nbsp;</label></div>

		</td>
	</tr>
	<tr>
		<td>
			<?php echo ipbx_label(_("Direct Dial Mode"), _("Whether to play the busy, unavailable or no message when direct dialing voicemail")) ?>
		</td>
		<td>
			<?php echo form_dropdown('VM_DDTYPE', $direct_dial_opts, $settings['VM_DDTYPE'], ' class="componentSelect" ') ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo ipbx_label(_("Voicemail Recording Gain"), _("The amount of gain to amplify a voicemail message when geing recorded. This is usually set when users are complaining about hard to hear messages on your system, often caused by very quiet analog lines. The gain is in Decibels which doubles for every 3 db.")) ?>
		</td>
		<td>
			<?php echo form_dropdown('VM_GAIN', $voicemail_gain_opts, $settings['VM_GAIN'], ' class="componentSelect" ')?>
		</td>
	</tr>
	<tr>
		<td><?php echo ipbx_label(_("Operator Extension"), _("Default number to dial when a voicemail user 'zeros out' if enabled. This can be overriden for each extension with the VMX Locater option that is valid even when VMX Locater is not enabled. This can be any number including an external number and there is NO VALIDATION so it should be tested after configuration.")) ?> 
		</td>
		<td>
			<?php echo form_input('OPERATOR_XTN', $settings['OPERATOR_XTN'], ' class="input" ') ?>
		</td>
	</tr>
	<tr>
		<td colspan='2'>
			<h5><?php echo _("Advanced VmX Locater Settings") ?></h5>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo ipbx_label(_("Msg Timeout"), _("Time to wait after message has played to timeout and/or repeat the message if no entry pressed.")) ?>
		</td>
		<td>
			<?php echo form_dropdown('VMX_TIMEOUT', $vmx_timeout_opts, $settings['VMX_TIMEOUT'], ' class="componentSelect" ')?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo ipbx_label(_("Times to Play Message"), _("Number of times to play the recorded message if the caller does not press any options and it times out. One attempt means we won't repeat and it will be treated as a timeout. A timeout would be the normal behavior and it is fairly normal to leave this zero and just record a message that tells them to press the various options now and leave enough time in the greeting letting them know it will otherwise go to voicemail as is normal.")) ?> 
		</td>
		<td>
			<?php echo form_dropdown('VMX_REPEAT', $vmx_repeat_opts, $settings['VMX_REPEAT'], ' class="componentSelect" ')?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo ipbx_label(_("Error Re-tries"), _("Number of times to play invalid options and repeat the message upon receiving an undefined option. One retry means it will repeat at one time after the intial failure.")) ?>
		</td>
		<td>
			<?php echo form_dropdown('VMX_LOOPS', $vmx_loops_opts, $settings['VMX_LOOPS'], ' class="componentSelect" ')?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo ipbx_label(_("Disable Standard Prompt after Max Loops"), _("If the Max Loops are reached and the call goes to voicemail, checking this box will disable the standard voicemail prompt prompt that follows the user's recorded greeting. This default can be overriden with a unique ..vmx/vmxopts/loops AstDB entry for the given mode (busy/unavail) and user.")) ?>
		</td>
		<td>
            <?php $checked = ($settings['VM_OPTS_LOOP']=='s')?' checked="checked" ':'';?>
            <div class='field'><input type='checkbox' class='switch' id='VM_OPTS_LOOP' name='VM_OPTS_LOOP' value='s' <?php echo $checked;?> tabindex='<?php echo ++$tabindex;?>'/><label style='height:auto; line-height:1em; padding-left:3em;' for='VM_OPTS_LOOP'>&nbsp;</label></div>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo ipbx_label(_("Disable Standard Prompt on 'dovm' Extension"), _("If the special advanced extension of 'dovm' is used, checking this box will disable the standard voicemail prompt prompt that follows the user's recorded greeting. This default can be overriden with a unique ..vmx/vmxopts/dovm AstDB entry for the given mode (busy/unavail) and user.")) ?>
		</td>
		<td>
            <?php $checked = ($settings['VM_OPTS_DOVM']=='s')?' checked="checked" ':'';?>
            <div class='field'><input type='checkbox' class='switch' id='VM_OPTS_DOVM' name='VM_OPTS_DOVM' value='s' <?php echo $checked;?> tabindex='<?php echo ++$tabindex;?>'/><label style='height:auto; line-height:1em; padding-left:3em;' for='VM_OPTS_DOVM'>&nbsp;</label></div>
		</td>
	</tr>
	<tr class='is-hidden'>
		<td colspan='2'>
			<input type='hidden' name='action' id='action' value='<?php echo _('Submit');?>' />
		</td>
	</tr>
</table>
</form>
<script>
<?php echo js_display_confirmation_toasts(); ?>
</script>
</div>
<?php echo form_action_bar(''); ?>
