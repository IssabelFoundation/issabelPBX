<?php echo _("This module is used to configure Parking Lot(s) in Asterisk.") ?>
<br/><br/>
<div class="messageb"><?php echo _("Simply transfer the call to said parking lot extension. Asterisk will then read back the parking lot number the call has been placed in. To retrieve the call simply dial that number back.") ?></div>
<br/>
<table width="50%">
	<tr>
		<td colspan="2"><?php echo _("Example usage") ?>:</td>
	</tr>
	<tr>
		<td><?php echo _("*2nn:") ?></td>
		<td><?php echo _("Attended Transfer call into Park lot nnn (It will announce the slot back to you)") ?></td>
	</tr>
	<tr>
		<td><?php echo _("nn:") ?></td>
		<td><?php echo _("Park Yourself into Parking lot nnn (Announcing your parked slot to you)") ?></td>
	</tr>
</table>

<!--<div class="messageb"><?php echo _("There are also different levels of Parking. To see what level you have and to see options and features you'd get from other modules please see the chart below")?></div>
<table class="myTable">
    <tr>
        <td><a href=# class="info"><?php echo _("Paging")?><span><?php echo _("Paging Provides the Ability to setup Park and Announce")?></span></a></td>
        <td class="<?php echo $modules['paging'] ? 'green' : 'red'?>"><?php echo $modules['paging'] ? 'Installed' : 'Not Installed' ?></td>
    </tr>
    <tr>
        <td><a href=# class="info"><?php echo _("Paging Pro")?><span><?php echo _("Paging Pro enables the Ability to setup Park and Announce")?></span></a></td>
        <td class="<?php echo $modules['pagingpro'] ? 'green' : 'red'?>"><?php echo $modules['pagingpro'] ? 'Installed' : 'Not Installed' ?></td>
    </tr>
    <tr>
        <td><a href=# class="info"><?php echo _("Park Pro")?><span><?php echo _("Park Pro enables the Ability to setup Park and Announce")?></span></a></td>
        <td class="<?php echo $modules['parkpro'] ? 'green' : 'red'?>"><?php echo $modules['parkpro'] ? 'Installed' : 'Not Installed' ?></td>
    </tr>
</table>
-->
<?php if(function_exists('parking_overview_display')) { echo parking_overview_display(); }?>
