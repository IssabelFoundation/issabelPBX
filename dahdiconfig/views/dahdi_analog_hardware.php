<div class='box my-5'>
<h2><?php echo __('Analog Hardware')?></h2>
<table class="alt_table" id="digital_cards_table" cellpadding="5" cellspacing="1" border="0">
        <thead>
        <tr>
                <th><?php echo __('Type')?></th>
                <th><?php echo __('Ports')?></th>
                <th><?php echo __('Action')?></th>
        </tr>
        </thead>
        <tbody>
	<?php
		$fxo = $dahdi_cards->get_fxo_ports();
		$fxs = $dahdi_cards->get_fxs_ports();
	?>
	<tr>
		<td><?php echo sprintf(__('%s Ports'),'FXO')?></td>
		<td><?php
		$c = count($fxo);
		if($c) {
    		$i = 1;
    		foreach($fxo as $chan) {
    		    echo $chan;
    		    echo ($c != $i) ? "," : "";
    		    $i++;
    		}
	    } else {
	        echo "--";
	    }
		?></td>
		<td><?php echo ((count($fxo))?'<a href="#" onclick="dahdi_modal_settings(\'analog\',\'fxo\');">'.__('Edit').'</a>':'')?></td>
	</tr>
	<tr>
		<td><?php echo sprintf(__('%s Ports'),'FXS')?></td>
		<td><?php echo ((count($fxs))?implode(',', $fxs):'--')?></td>
		<td><?php echo ((count($fxs))?'<a href="#" onclick="dahdi_modal_settings(\'analog\',\'fxs\');">'.__('Edit').'</a>':'')?></td>
	</tr>
        </tbody>
</table>
</div>
