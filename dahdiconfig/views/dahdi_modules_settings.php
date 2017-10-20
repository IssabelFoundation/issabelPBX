<form id="form-modules" action="config.php?quietmode=1&amp;handler=file&amp;module=dahdiconfig&amp;file=ajax.html.php&amp;type=modulessubmit">
	<div id="modules">
		<h2><?php echo _('DAHDi Module Load/Order')?></h2>
		<h3><?php echo sprintf(_('This edits order and loading of DAHDi modules in %s'),'/etc/dahdi/modules')?></h3>
		<ul class="modules-sortable" id="modules-sortable">
		<?php
		$mod_id = 0;
		foreach($mods as $modules => $info) {?>
			<li id="mod-<?php echo ($info['type'] == 'ud') ? 'ud-'.$mod_id : $modules ?>">
				<?php if($info['type'] == 'sys') {?>
					<input type="checkbox" id="input-<?php echo $modules?>" value="on" <?php echo $info['status'] ? 'checked' : ''?>><?php echo $modules?>
				<?php } elseif($info['type'] == 'ud') {?>
					<input type="checkbox" id="mod-ud-checkbox-<?php echo $mod_id?>" value="on" <?php echo $info['status'] ? 'checked' : ''?>><img style="cursor: pointer;" height="10px" src="images/trash.png" onclick="mods_del_field('mod-ud-<?php echo $mod_id?>')"><input type="textbox" id="mod-ud-name-<?php echo $mod_id?>" value="<?php echo $modules?>">
				<?php $mod_id++; } ?>
				<br/>
			</li>
		<?php } ?>
			<li id="mod-ud-<?php echo $mod_id?>">
				<input type="checkbox" id="mod-ud-checkbox-<?php echo $mod_id?>"><img style="cursor: pointer;" height="10px" src="images/trash.png" onclick="mods_del_field('mod-ud-<?php echo $mod_id?>')"><input type="textbox" id="mod-ud-name-<?php echo $mod_id?>" value="">
			</li>
		</ul>
		<a style="cursor: pointer;" onclick="mods_add_field()"><img src="assets/dahdiconfig/images/add.png"></a>
	</div>
	<input type="hidden" id="mods_add_id" value"<?php echo $mod_id + 1?>">
</form>
