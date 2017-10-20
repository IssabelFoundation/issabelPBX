<div id="message" class="alert" style="display:none;"></div>
<form role="form">
	<label class="playcid">
		Enable
		<div class="onoffswitch">
			<input type="checkbox" name="follow_me_ddial" class="onoffswitch-checkbox" id="follow_me_ddial" <?php echo ($enabled) ? 'checked' : ''?>>
			<label class="onoffswitch-label" for="follow_me_ddial">
				<div class="onoffswitch-inner"></div>
				<div class="onoffswitch-switch"></div>
			</label>
		</div>
	</label>
	<div class="form-group">
		<label for="follow_me_list"><?php echo _('Follow Me List')?></label>
		<textarea id="follow_me_list" name="follow_me_list" class="form-control" rows="3"><?php echo implode("\n",$list)?></textarea>
	</div>
	<div class="form-group">
		<label for="follow_me_prering_time"><?php echo sprintf(_('Ring %s First For'),$exten) ?>:</label><br/>
		<select name="follow_me_prering_time" id="follow_me_prering_time">
			<?php foreach($prering_time as $key => $value) { ?>
				<option value="<?php echo $key?>" <?php echo ($prering == $key) ? 'selected' : ''?>><?php echo $value?> <?php echo _('Seconds')?></option>
			<?php } ?>
		</select>
	</div>
	<div class="form-group">
		<label for="follow_me_listring_time"><?php echo _('Ring Followme List For') ?>:</label><br/>
		<select name="follow_me_listring_time" id="follow_me_listring_time">
			<?php foreach($listring_time as $key => $value) { ?>
				<option value="<?php echo $key?>" <?php echo ($ringtime == $key) ? 'selected' : ''?>><?php echo $value?> <?php echo _('Seconds')?></option>
			<?php } ?>
		</select>
	</div>
	<label class="envelope">
		<?php echo _('Use Confirmation')?>
		<div class="onoffswitch">
			<input type="checkbox" name="follow_me_confirm" class="onoffswitch-checkbox" id="follow_me_confirm" <?php echo ($confirm) ? 'checked' : ''?>>
			<label class="onoffswitch-label" for="follow_me_confirm">
				<div class="onoffswitch-inner"></div>
				<div class="onoffswitch-switch"></div>
			</label>
		</div>
	</label>
</form>
