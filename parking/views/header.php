<?php if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); } ?>

<div class="rnav">
	<ul>
        <li><a href="config.php?display=parking&type=setup&action=add"><?php echo _('Add Park Slot');?></a></li>

        <li><hr></li>
        <?php foreach($lots as $l) {?>
        <li><a href="config.php?display=parking&amp;id=<?php echo $l['id']?>&amp;action=modify"><?php echo $l['defaultlot'] == 'yes' ? '<strong>[D]</strong> ' : ''?><?php echo $l['name']?></a></li>
        <?php } ?>
	</ul>
</div>
<h2><?php echo _('Parking Lot') ?></h2>
<hr class="parking-hr"/>
