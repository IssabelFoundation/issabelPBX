<h2><?php echo sprintf(_('Analog %s Ports'),(($_GET['ports'] == 'fxo')?'FXO':'FXS'))?></h2>
<hr />
<form name="dahdi_editanalog" action="/admin/config.php?type=setup&amp;display=dahdi&amp;dahdi_form=analog_signalling&amp;ports=<?=$_GET['ports']?>" method="post">
<input type="hidden" name="type" value="<?=$_GET['ports']?>" />
<div id="editanalog_options_container">
<?
	$spans = ($_GET['ports'] == 'fxo') ? $dahdi_cards->get_fxo_ports() : $dahdi_cards->get_fxs_ports();
	foreach ($spans as $p): ?>
	<? $port = $dahdi_cards->get_port($p); ?>
	<div>
		<?php echo sprintf(_('Port %s'),$p)?>:
		<select name="port_<?=$p?>" id="port_<?=$p?>">
			<option value="ks"><?php echo _('Kewl Start')?></option>
			<option value="ls"><?php echo _('Loop Start')?></option>
		</select>
		<?php echo _('Group')?>:
		<input type="text" name="port_<?=$p?>_group" id="port_<?=$p?>_group" size="2" value="<?=$port['group']?>" />
		<? if ($_GET['ports'] == 'fxo'): ?>
		<?php echo _('Context')?>:
		<input type="text" name="port_<?=$p?>_context" id="port_<?=$p?>_context" value="<?=$port['context']?>" />
		<? endif; ?>
	</div>
	<? endforeach; ?>
</div>
<div id="editanalog_buttons">
	<input type="submit" name="editanalog_cancel" value="<?php echo _('Cancel')?>" />
	<input type="submit" name="editanalog_submit" value="<?php echo _('Save')?>" />
</div>
</form>
<script type="text/javascript">
	<?
	$lsports = $dahdi_cards->get_ls_ports();

	foreach ($spans as $p): ?>
	ChangeSelectByValue('port_<?=$p?>', "<?=((in_array($p, $lsports)) ? 'ls' : 'ks')?>", true);
	<? endforeach; ?>
</script>
