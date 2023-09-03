<?php echo __("This module is used to configure Parking Lot(s) in Asterisk.") ?>
<br/><br/>
<div class="messageb"><?php echo __("Simply transfer the call to said parking lot extension. Asterisk will then read back the parking lot number the call has been placed in. To retrieve the call simply dial that number back.") ?></div>
<br/>
<table width="50%">
	<tr>
		<td colspan="2"><?php echo __("Example usage") ?>:</td>
	</tr>
	<tr>
		<td><?php echo __("*2nn:") ?></td>
		<td><?php echo __("Attended Transfer call into Park lot nnn (It will announce the slot back to you)") ?></td>
	</tr>
	<tr>
		<td><?php echo __("nn:") ?></td>
		<td><?php echo __("Park Yourself into Parking lot nnn (Announcing your parked slot to you)") ?></td>
	</tr>
</table>

<?php if(function_exists('parking_overview_display')) { echo parking_overview_display(); }?>
