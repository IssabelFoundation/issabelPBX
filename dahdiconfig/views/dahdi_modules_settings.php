<div class="modal animate__animated animate__fadeIn" id="modulesettings">
  <div class="modal-background"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title" style="margin-bottom:0;"><?php echo __('DAHDi Module Load/Order')?></p>
      <button class="delete" aria-label="close"></button>
    </header>
    <section class="modal-card-body">

    <div class='columns mb-5'><div class='column'>
        <div class='is-size-7'><?php echo sprintf(__('This edits order and loading of DAHDi modules in %s'),'/etc/dahdi/modules')?></div>
    </div></div>


        <form id="form-modules" action="config.php?quietmode=1&amp;handler=file&amp;module=dahdiconfig&amp;file=ajax.html.php&amp;type=modulessubmit">

		<ul class="modules-sortable" id="modules-sortable">
		<?php
		$mod_id = 0;
		foreach($mods as $modules => $info) {?>
			<li id="mod-<?php echo ($info['type'] == 'ud') ? 'ud-'.$mod_id : $modules ?>" >
<i class="fa fa-arrows-v mr-2"></i>
				<?php if($info['type'] == 'sys') {?>
					<input class='mx-2 checkbox' type="checkbox" id="input-<?php echo $modules?>" value="on" <?php echo $info['status'] ? 'checked' : ''?>><?php echo $modules?>
				<?php } elseif($info['type'] == 'ud') {?>
					<input class='mx-2 checkbox' type="checkbox" name="mod-ud-checkbox-<?php echo $mod_id?>" id="mod-ud-checkbox-<?php echo $mod_id?>" value="on" <?php echo $info['status'] ? 'checked' : ''?>> <a href='javascript:void(0);'  onclick="mods_del_field('mod-ud-<?php echo $mod_id?>')"> <button type="button" class="mx-2 is-danger button is-small"><span class="icon is-small"><i class="fa fa-trash"></i></span></button></a> <input type="text" class="valueintput" id="mod-ud-name-<?php echo $mod_id?>" value="<?php echo $modules?>">
				<?php $mod_id++; } ?>
			</li>
		<?php } ?>
			<li id="mod-ud-<?php echo $mod_id?>" >
				<input type="checkbox" class='mx-2 checkbox' id="mod-ud-checkbox-<?php echo $mod_id?>"><a href='javascript:void(0);' onclick="mods_del_field('mod-ud-<?php echo $mod_id?>')"><button type="button" class="is-danger button is-small mx-2"><span class="icon is-small"><i class="fa fa-trash"></i></span></button></a> <input type="text" class="valueinput" id="mod-ud-name-<?php echo $mod_id?>" value="">
			</li>
		</ul>
		<a onclick="mods_add_field()" class='button is-small is-rounded'><?php echo __('Add another field')?></a>
	<input type="hidden" id="mods_add_id" value"<?php echo $mod_id + 1?>">
    <input type="hidden" name="reset" id="reset" value=0>
</form>


    </form>
    </section>
    <footer class="modal-card-foot">
      <button data-target="form-modules" onclick='$("#reset").val(0)' class="button is-success formsubmit"><?php echo __('Save')?></button>
      <button data-target="form-modules" id="reset" onclick='$("#reset").val(1)' class="button is-success formsubmit"><?php echo __('Reset File to Defaults')?></button>
      <button class="button"><?php echo __('Cancel')?></button>
    </footer>
  </div>
</div>



