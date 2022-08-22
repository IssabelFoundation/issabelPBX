<?php if ($id && $id>1){ ?>
    <!--a href='config.php?display=parking&amp;action=delete&amp;id=<?php echo $id?>'><img src='images/user_delete.png'> <?php echo _("Delete Parking Lot")?></a-->
<?php } ?>



<form id="mainform" action="config.php?display=parking&amp;extdisplay=<?php echo $id?>&amp;action=update" method="post">
<input type=hidden name=action value='<?php echo (isset($id)?'update':'add');?>'>

    <table>
        <tr>
            <td colspan="2"><h5><?php echo _("Parking Lot Options")?></h5></td>
        </tr>
        <tr>
            <td><a href=# class="info"><?php echo _("Parking Lot Extension")?><span><?php echo _("This is the extension where you will transfer a call to park it")?></span></a></td>
            <td>
                <input class="input" type="text" id="parkext" name="parkext" value="<?php echo $parkext?>" class='w100'>
            </td>
        </tr>
        <tr>
            <td><a href=# class="info"><?php echo _("Parking Lot Name")?><span><?php echo _("Provide a Descriptive Title for this Parking Lot")?></span></a></td>
            <td>
                <input class="input" id="name" type="text" name="name" value="<?php echo $name?>" class='w100'>
            </td>
        </tr>
        <tr>
            <td><a href=# class="info"><?php echo _("Parking Lot Starting Position")?><span><?php echo _("The starting postion of the parking lot")?></span></a></td>
            <td>
                <input class="input" type="text" id="parkpos" name="parkpos" value="<?php echo $parkpos?>" class='w100'>
            </td>
        </tr>
    	<tr>
    		<td><a href="#" class="info"><?php echo _("Number of Slots")?><span><?php echo _("The total number of parking lot spaces to configure. Example, if 70 is the extension and 8 slots are configured, the parking slots will be 71-78")?></span></a></td>
    		<td>
				<input class="input" type="number" min="1" id="numslots" name="numslots" size="5" value="<?php echo $numslots?>"><span id="slotslist" style="font-size:90%"></span>
    		</td>
    	</tr>
    	<tr>
    	    <td><a href="#" class="info"><?php echo _("Parking Timeout (seconds)")?><span><?php echo _("The timeout period in seconds that a parked call will attempt to ring back the original parker if not answered")?></span></a></td>
    		<td>
                <input class="input" type="number" min="0" id="parkingtime" name="parkingtime" value="<?php echo $parkingtime?>" class='w100'>
    		</td>
    	</tr>
    	<tr>
    		<td><a href="#" class="info"><?php echo _("Parked Music Class")?><span><?php echo _("This is the music class that will be played to a parked call while in the parking lot UNLESS the call flow prior to parking the call explicitly set a different music class, such as if the call came in through a queue or ring group.")?></span></a></td>
    		<td>
    			<select name="parkedmusicclass" class='componentSelect'>
                <?php
                    $tresults = music_list();
                    $none = array_search('none',$tresults);
                    if ($none !== false) {
                        unset($tresults[$none]);
                    }
                    if (isset($tresults)) {
                        foreach ($tresults as $tresult) {
                            $searchvalue="$tresult";
                            $ttext = $tresult;
                            if($tresult == 'default') $ttext = _("default");						
                            echo '<option value="'.$tresult.'" '.($searchvalue == $parkedmusicclass ? 'SELECTED' : '').'>'.$ttext;
                        }
                    }
                ?>		
    			</select>		
    		</td>
    	</tr>
        <tr>
            <td><a href=# class="info"><?php echo _("BLF Capabilities")?><span><?php echo _("Enable this to have Asterisk 'hints' generated to use with BLF buttons.")?></span></a></td>
            <td>

<?php echo ipbx_radio('generatehints',array(array('value'=>'yes','text'=>_('Enable')),array('value'=>'no','text'=>_('Disable'))),$generatehints,false);?>

            </td>
        </tr>
    	<tr>
    	    <td><a href=# class="info"><?php echo _("Find Slot")?><span><?php echo _("Next: If you want the parking lot to seek the next sequential parking slot relative to the the last parked call instead of seeking the first available slot. First: Use the first parking lot slot available")?></span></a></td>
    	    <td>
<?php echo ipbx_radio('findslot',array(array('value'=>'next','text'=>_('Next')),array('value'=>'first','text'=>_('First'))),$findslot,false);?>
            </td>
    	</tr>
        <tr>
            <td colspan="2"><h5><?php echo _("Returned Call Behavior")?></h5></td>
        </tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Pickup Courtesy Tone")?><span><?php echo _("Whom to play the courtesy tone to when a parked call is retrieved.")?></span></a></td>
            <td>
<?php echo ipbx_radio('parkedplay',array(array('value'=>'caller','text'=>_('Caller')),array('value'=>'callee','text'=>_('Parked')),array('value'=>'both','text'=>_('Both'))),$parkedplay,false);?>
            </td>
      	</tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Transfer Capability")?><span><?php echo _("Asterisk: parkedcalltransfers. Enables or disables DTMF based transfers when picking up a parked call.")?></span></a></td>
                <td>
<?php echo ipbx_radio('parkedcalltransfers',array(array('value'=>'caller','text'=>_('Caller')),array('value'=>'callee','text'=>_('Parked')),array('value'=>'both','text'=>_('Both')),array('value'=>'no','text'=>_('Neither'))),$parkedcalltransfers,false);?>
                </td>
      	</tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Re-Parking Capability")?><span><?php echo _("Asterisk: parkedcallreparking. Enables or disables DTMF based parking when picking up a parked call.")?></span></a></td>
            <td>
<?php echo ipbx_radio('parkedcallreparking',array(array('value'=>'caller','text'=>_('Caller')),array('value'=>'callee','text'=>_('Parked')),array('value'=>'both','text'=>_('Both')),array('value'=>'no','text'=>_('Neither'))),$parkedcallreparking,false);?>
            </td>
      	</tr>
    	<tr>
    	    <td><a href=# class="info"><?php echo _("Parking Alert-Info")?><span><?php echo _("Alert-Info to add to the call prior to sending back to the Originator or to the Alternate Destination.")?><br></span></a></td>
    	    <td>
                <input type="text" class="input" name="alertinfo" value="<?php echo htmlspecialchars($alertinfo)?>" class='w100'/>
            </td>
    	</tr>
    	<tr>
    	    <td><a href=# class="info"><?php echo _("CallerID Prepend")?><span><?php echo _("String to prepend to the current Caller ID associated with the parked call prior to sending back to the Originator or the Alternate Destination.") ?><br></span></a></td>
    	    <td>
                <input type="text" class="input" name="cidpp" value="<?php echo htmlspecialchars($cidpp)?>" class='w100'/>
            </td>
    	</tr>
    	<tr>
    		<td><a href="#" class="info"><?php echo _("Auto CallerID Prepend")?><span><?php echo _("These options will be appended after CallerID Prepend if set. Otherwise they will appear first. The automatic options are as follows:<ul><li><strong>None:</strong> No Automatic Prepend</li><li><strong>Slot:</strong> Parking lot they were parked on</li><li><strong>Extension:</strong> The extension number that parked the call</li><li><strong>Name:</strong> The user who parked the call</li></ul>")?></span></a></td>
    		<td>
    			<select name="autocidpp" class='componentSelect'>
						<option value="none" <?php echo ($autocidpp == 'none' ? 'selected' : '')?>><?php echo _("None") ?></option>
						<option value="slot" <?php echo ($autocidpp == 'slot' ? 'selected' : '')?>><?php echo _("Slot") ?></option>
						<option value="exten" <?php echo ($autocidpp == 'exten' ? 'selected' : '')?>><?php echo _("Extension") ?></option>
						<option value="name" <?php echo ($autocidpp == 'name' ? 'selected' : '')?>><?php echo _("Name") ?></option>
					</select>
				</td>
        </tr>
        <?php if(function_exists('recordings_list')) { //only include if recordings is enabled?>
        	<tr>
        		<td><a href="#" class="info"><?php echo _("Announcement")?><span><?php echo _("Optional message to be played to the call prior to sending back to the Originator or the Alternate Destination.") ?></span></a></td>
        		<td>
        			<select name="announcement_id" class='componentSelect'>
        			<?php
        				$tresults = recordings_list();
        				echo '<option value="">'._("None")."</option>";
        				if (isset($tresults[0])) {
        					foreach ($tresults as $tresult) {
        						echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $announcement_id ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
        					}
        				}
        			?>
        			</select>
        		</td>
        	</tr>
        <?php } else { ?>
        	<tr>
        		<td><a href="#" class="info"><?php echo _("Announcement")?><span><?php echo _("Optional message to be played to the call prior to sending back to the Originator or the Alternate Destination.") . '<br /><br />' .  _("You must install and enable the \"Systems Recordings\" Module to edit this option")?></span></a></td>
        		<td>
        			<input type="hidden" name="announcement_id" value="<?php echo $announcement_id; ?>"><?php echo ($announcement_id != '' ? $announcement_id : 'None'); ?>
        		</td>
        	</tr>
        <?php }
        ?>
        <tr>
            <td colspan="2"><h5><?php echo _("Alternate Destination")?></h5></td>
        </tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Come Back to Origin")?><span><?php echo _("Where to send a parked call that has timed out. If set to yes then the parked call will be sent back to the originating device that sent the call to this parking lot. If the origin is busy then we will send the call to the Destination selected below. If set to no then we will send the call directly to the destination selected below")?></span></a></td>
            <td>
<?php echo ipbx_radio('comebacktoorigin',array(array('value'=>'yes','text'=>_('Enable')),array('value'=>'no','text'=>_('Disable'))),$comebacktoorigin,false);?>
                <!--span class="radioset">
                    <input type="radio" name="comebacktoorigin" id="parking_dest-device" value="yes" <?php echo ($comebacktoorigin == 'yes' ? 'checked' : '')?>/><label for="parking_dest-device"><?php echo _("Yes") ?></label>
                    <input type="radio" name="comebacktoorigin" id="parking_dest-dest" value="no" <?php echo ($comebacktoorigin == 'no' ? 'checked' : '')?>/><label for="parking_dest-dest"><?php echo _("No") ?></label>
                </span-->
            </td>
      	</tr>
        <tr>
					<td><?php echo _("Destination") ?></td>
            <td>
                <?php
                  echo drawselects($dest,0,false,false);
                ?>
            </td>
        </tr>

    </table>
</form>
<script>
    <?php echo js_display_confirmation_toasts(); ?>
    $(function() {
        ipbx.msg.framework.notblank = '<?php echo _("Field can not be blank!")?>';
    })
</script>
</div>
<?php 
if ($id && $id>1){ 
    echo form_action_bar($id); 
} else {
    // id 1 cannot be deleted
    echo form_action_bar(''); 
}
?>

