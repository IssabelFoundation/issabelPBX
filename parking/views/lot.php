<?php if ($id && $id>1){ ?>
    <a href='config.php?display=parking&amp;action=delete&amp;id=<?php echo $id?>'><img src='images/user_delete.png'> <?php echo _("Delete Parking Lot")?></a>
    <?php if($dids_using) {?>
        <small><?php sprintf(_("There are %s DIDs using this source that will no longer have lookups if deleted."),$dids_using)?></small>
    <?php } ?>
<?php } ?>



<form id="parkform" action="config.php?display=parking&amp;id=<?php echo $id?>&amp;action=update" method="post">
    <table width="40%">
        <tr>
            <td colspan="2"><h5><?php echo _("Parking Lot Options")?><hr></h5></td>
        </tr>
        <tr>
            <td><a href=# class="info"><?php echo _("Parking Lot Extension:")?><span><?php echo _("This is the extension where you will transfer a call to park it")?></span></a></td>
            <td>
                <input type="text" id="parkext" name="parkext" size="5" value="<?php echo $parkext?>">
            </td>
        </tr>
        <tr>
            <td><a href=# class="info"><?php echo _("Parking Lot Name:")?><span><?php echo _("Provide a Descriptive Title for this Parking Lot")?></span></a></td>
            <td>
                <input id="name" type="textbox" name="name" size="30" value="<?php echo $name?>">
            </td>
        </tr>
        <tr>
            <td><a href=# class="info"><?php echo _("Parking Lot Starting Position:")?><span><?php echo _("The starting postion of the parking lot")?></span></a></td>
            <td>
                <input type="text" id="parkpos" name="parkpos" size="5" value="<?php echo $parkpos?>">
            </td>
        </tr>
    	<tr>
    		<td><a href="#" class="info"><?php echo _("Number of Slots:")?><span><?php echo _("The total number of parking lot spaces to configure. Example, if 70 is the extension and 8 slots are configured, the parking slots will be 71-78")?></span></a></td>
    		<td>
				<input type="number" min="1" id="numslots" name="numslots" size="5" value="<?php echo $numslots?>"><span id="slotslist" style="font-size:90%"></span>
    		</td>
    	</tr>
    	<tr>
    	    <td><a href="#" class="info"><?php echo _("Parking Timeout (seconds):")?><span><?php echo _("The timeout period in seconds that a parked call will attempt to ring back the original parker if not answered")?></span></a></td>
    		<td>
                <input type="number" min="0" id="parkingtime" name="parkingtime" value="<?php echo $parkingtime?>">
    		</td>
    	</tr>
    	<tr>
    		<td><a href="#" class="info"><?php echo _("Parked Music Class:")?><span><?php echo _("This is the music class that will be played to a parked call while in the parking lot UNLESS the call flow prior to parking the call explicitly set a different music class, such as if the call came in through a queue or ring group.")?></span></a></td>
    		<td>
    			<select name="parkedmusicclass">
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
            <td><a href=# class="info"><?php echo _("BLF Capabilities:")?><span><?php echo _("Enable this to have Asterisk 'hints' generated to use with BLF buttons.")?></span></a></td>
            <td>
                <span class="radioset">
									<input type="radio" name="generatehints" value="yes" id="parkinghints_enable" <?php echo ($generatehints == 'yes' ? 'checked' : '')?>><label for="parkinghints_enable"><?php echo _("Enable") ?></label>
									<input type="radio" name="generatehints" value="no" id="parkinghints_disable" <?php echo ($generatehints == 'no' ? 'checked' : '')?>><label for="parkinghints_disable"><?php echo _("Disable") ?></label>
                </span>
            </td>
        </tr>
    	<tr>
    	    <td><a href=# class="info"><?php echo _("Find Slot:")?><span><?php echo _("Next: If you want the parking lot to seek the next sequential parking slot relative to the the last parked call instead of seeking the first available slot. First: Use the first parking lot slot available")?></span></a></td>
    	    <td>
                <span class="radioset">
									<input type="radio" name="findslot" value="next" id="findslot_next" <?php echo ($findslot == 'next' ? 'checked' : '')?>><label for="findslot_next"><?php echo _("Next") ?></label>
									<input type="radio" name="findslot" value="first" id="findslot_first" <?php echo ($findslot == 'first' ? 'checked' : '')?>><label for="findslot_first"><?php echo _("First") ?></label>
                </span>
            </td>
    	</tr>
        <tr>
            <td colspan="2"><h5><?php echo _("Returned Call Behavior")?><hr></h5></td>
        </tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Pickup Courtesy Tone:")?><span><?php echo _("Whom to play the courtesy tone to when a parked call is retrieved.")?></span></a></td>
            <td>
                <span class="radioset">
                    <input type="radio" name="parkedplay" id="parkedplay-caller" value="caller" <?php echo ($parkedplay == 'caller' ? 'checked' : '')?>/><label for="parkedplay-caller"><?php echo _("Caller") ?></label>
                    <input type="radio" name="parkedplay" id="parkedplay-callee" value="callee" <?php echo ($parkedplay == 'callee' ? 'checked' : '')?>/><label for="parkedplay-callee"><?php echo _("Parked") ?></label>
                    <input type="radio" name="parkedplay" id="parkedplay-both" value="both" <?php echo ($parkedplay == 'both' ? 'checked' : '')?>/><label for="parkedplay-both"><?php echo _("Both") ?></label>
                </span>
            </td>
      	</tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Transfer Capability:")?><span><?php echo _("Asterisk: parkedcalltransfers. Enables or disables DTMF based transfers when picking up a parked call.")?></span></a></td>
            <span class="radioset">
                <td>
                    <span class="radioset">
                        <input type="radio" name="parkedcalltransfers" id="parkedcalltransfers-caller" value="caller" <?php echo ($parkedcalltransfers == 'caller' ? 'checked' : '')?>/><label for="parkedcalltransfers-caller"><?php echo _("Caller") ?></label>
                        <input type="radio" name="parkedcalltransfers" id="parkedcalltransfers-callee" value="callee" <?php echo ($parkedcalltransfers == 'callee' ? 'checked' : '')?>/><label for="parkedcalltransfers-callee"><?php echo _("Parked") ?></label>
                        <input type="radio" name="parkedcalltransfers" id="parkedcalltransfers-both" value="both" <?php echo ($parkedcalltransfers == 'both' ? 'checked' : '')?>/><label for="parkedcalltransfers-both"><?php echo _("Both") ?></label>
                        <input type="radio" name="parkedcalltransfers" id="parkedcalltransfers-no" value="no" <?php echo ($parkedcalltransfers == 'no' ? 'checked' : '')?>/><label for="parkedcalltransfers-no"><?php echo _("Neither") ?></label>
                    </span>
                </td>
            </span>
      	</tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Re-Parking Capability:")?><span><?php echo _("Asterisk: parkedcallreparking. Enables or disables DTMF based parking when picking up a parked call.")?></span></a></td>
            <td>
                <span class="radioset">
                    <input type="radio" name="parkedcallreparking" id="parkedcallreparking-caller" value="caller" <?php echo ($parkedcallreparking == 'caller' ? 'checked' : '')?>/><label for="parkedcallreparking-caller"><?php echo _("Caller") ?></label>
                    <input type="radio" name="parkedcallreparking" id="parkedcallreparking-callee" value="callee" <?php echo ($parkedcallreparking == 'callee' ? 'checked' : '')?>/><label for="parkedcallreparking-callee"><?php echo _("Parked") ?></label>
                    <input type="radio" name="parkedcallreparking" id="parkedcallreparking-both" value="both" <?php echo ($parkedcallreparking == 'both' ? 'checked' : '')?>/><label for="parkedcallreparking-both"><?php echo _("Both") ?></label>
                    <input type="radio" name="parkedcallreparking" id="parkedcallreparking-no" value="no" <?php echo ($parkedcallreparking == 'no' ? 'checked' : '')?>/><label for="parkedcallreparking-no"><?php echo _("Neither") ?></label>
                </span>
            </td>
      	</tr>
    	<tr>
    	    <td><a href=# class="info"><?php echo _("Parking Alert-Info:")?><span><?php echo _("Alert-Info to add to the call prior to sending back to the Originator or to the Alternate Destination.")?><br></span></a></td>
    	    <td>
                <input type="text" size="30" name="alertinfo" value="<?php echo htmlspecialchars($alertinfo)?>"/>
            </td>
    	</tr>
    	<tr>
    	    <td><a href=# class="info"><?php echo _("CallerID Prepend:")?><span><?php echo _("String to prepend to the current Caller ID associated with the parked call prior to sending back to the Originator or the Alternate Destination.") ?><br></span></a></td>
    	    <td>
                <input type="text" size="30" name="cidpp" value="<?php echo htmlspecialchars($cidpp)?>"/>
            </td>
    	</tr>
    	<tr>
    		<td><a href="#" class="info"><?php echo _("Auto CallerID Prepend:")?><span><?php echo _("These options will be appended after CallerID Prepend if set. Otherwise they will appear first. The automatic options are as follows:<ul><li><strong>None:</strong> No Automatic Prepend</li><li><strong>Slot:</strong> Parking lot they were parked on</li><li><strong>Extension:</strong> The extension number that parked the call</li><li><strong>Name:</strong> The user who parked the call</li></ul>")?></span></a></td>
    		<td>
    			<select name="autocidpp">
						<option value="none" <?php echo ($autocidpp == 'none' ? 'selected' : '')?>><?php echo _("None") ?></option>
						<option value="slot" <?php echo ($autocidpp == 'slot' ? 'selected' : '')?>><?php echo _("Slot") ?></option>
						<option value="exten" <?php echo ($autocidpp == 'exten' ? 'selected' : '')?>><?php echo _("Extension") ?></option>
						<option value="name" <?php echo ($autocidpp == 'name' ? 'selected' : '')?>><?php echo _("Name") ?></option>
					</select>
				</td>
        </tr>
        <?php if(function_exists('recordings_list')) { //only include if recordings is enabled?>
        	<tr>
        		<td><a href="#" class="info"><?php echo _("Announcement:")?><span><?php echo _("Optional message to be played to the call prior to sending back to the Originator or the Alternate Destination.") ?></span></a></td>
        		<td>
        			<select name="announcement_id">
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
        		<td><a href="#" class="info"><?php echo _("Announcement:")?><span><?php echo _("Optional message to be played to the call prior to sending back to the Originator or the Alternate Destination.") . '<br /><br />' .  _("You must install and enable the \"Systems Recordings\" Module to edit this option")?></span></a></td>
        		<td>
        			<input type="hidden" name="announcement_id" value="<?php echo $announcement_id; ?>"><?php echo ($announcement_id != '' ? $announcement_id : 'None'); ?>
        		</td>
        	</tr>
        <?php }
        ?>
        <tr>
            <td colspan="2"><h5><?php echo _("Alternate Destination")?><hr></h5></td>
        </tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Come Back to Origin:")?><span><?php echo _("Where to send a parked call that has timed out. If set to yes then the parked call will be sent back to the originating device that sent the call to this parking lot. If the origin is busy then we will send the call to the Destination selected below. If set to no then we will send the call directly to the destination selected below")?></span></a></td>
            <td>
                <span class="radioset">
                    <input type="radio" name="comebacktoorigin" id="parking_dest-device" value="yes" <?php echo ($comebacktoorigin == 'yes' ? 'checked' : '')?>/><label for="parking_dest-device"><?php echo _("Yes") ?></label>
                    <input type="radio" name="comebacktoorigin" id="parking_dest-dest" value="no" <?php echo ($comebacktoorigin == 'no' ? 'checked' : '')?>/><label for="parking_dest-dest"><?php echo _("No") ?></label>
                </span>
            </td>
      	</tr>
        <tr>
					<td><?php echo _("Destination:") ?></td>
            <td>
                <?php
                  echo drawselects($dest,0,false,false);
                ?>
            </td>
        </tr>

      	<tr>
      		<td colspan="2"><br><h6><input id="parksubmit" name="Submit" type="submit" value="<?php echo _("Submit Changes")?>"></h6></td>
      	</tr>
    </table>
</form>
