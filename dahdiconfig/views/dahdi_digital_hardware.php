<div class='box'>
<h2><?php echo _('Digital Hardware')?></h2>
<table class="table is-striped" id="digital_cards_table" cellpadding="5" cellspacing="1" border="0">
        <thead>
        <tr>
                <th><?php echo _('Span')?></th>
                <th><?php echo _('Alarms')?></th>
                <th><?php echo _('Framing/Coding')?></th>
                <th><?php echo _('Channels Used/Total')?></th>
                <th><?php echo _('D-Channel')?></th>
                <th><?php echo _('Signaling')?></th>
                <th><?php echo _('Action')?></th>
        </tr>
        </thead>
        <tbody>
	<?php $ctr = 1;
	foreach($dahdi_cards->get_spans() as $key=>$span) {
		$name_split = explode('/', $span['name']);
		$devicetype = $span['devicetype'];
		$name = "{$span['manufacturer']} - {$span['description']} [{$span['dsid']}]";
	?>
	<tr>
		<td><?php echo $name?></td>
		<td id="digital_alarms_<?php echo $key; ?>_label"><?php echo $span['alarms']?></td>
		<td id="digital_framingcoding_<?php echo $key; ?>_label"><?php echo !empty($span['framing']) ? $span['framing'] : _('N/A')?><?php echo !empty($span['coding']) ? "/".$span['coding'] : ''?></td>
		<td id="digital_totchans_<?php echo $key; ?>_label"><?php echo $span['totchans']."/".$span['totchans']?></td>
		<td id="digital_dchan_<?php echo $key; ?>_label"><?php echo ((isset($span['reserved_ch']))?$span['reserved_ch']:_("Not Yet Defined"))?></td>
		<td id="digital_signalling_<?php echo $key; ?>_label"><?php echo ((isset($span['signalling']))?$span['signalling']:_("Not Yet Defined"))?></td>
		<td><a href="#" onclick="dahdi_modal_settings('digital','<?php echo $key?>');">Edit</a></td>
	</tr>
	<?php $ctr++;
	} ?>
        </tbody>
</table>
</div>
