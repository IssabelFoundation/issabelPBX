<?php
echo "<h2>"._("Google Voice (Motif)")."</h2>";

$type = $action == 'add' ? 'Add' : 'Edit';

if($action == 'edit') { 
	echo "<h2>Account: ".$form_number."</h2>"; 
	echo "<a href='config.php?display=motif&action=delete&id=".$id."'><img src='images/user_delete.png' /> ".('Delete Account')." ".$form_number."</a>";
}
?>
<script>
	function editM_onsubmit() {
		return true;
	}
</script>
    <form autocomplete="off" name="editM" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return editM_onsubmit();">
	<?php if($action == 'edit') { echo '<input type="hidden" name="id" value="'.$id.'">'; } ?>
        <table>
            <tr>
                <td colspan="2"><h5><?php echo _("Typical Settings")?></h5><hr></td>
            </tr>
            <tr>
                <td><a href="#" class="info"><?php echo _("Google Voice Username")?>:<span><?php echo _("This is your google voice login.<br />If don't you supply '@domain' we will append @gmail.com")?></span></a></td>
                <td><input type="text" name="username" maxlength="100" value="<?php echo isset($form_username) ? $form_username : ''; ?>"></td>
            </tr>
            <tr>
                <td><a href="#" class="info"><?php echo _("Google Voice Password")?>:<span><?php echo _("This is your Google Voice Password")?></span></a></td>
            	<td><input type="password" name="password" maxlength="150" value="<?php echo isset($form_password) ? $form_password : ''; ?>"></td>
			</tr>
			<tr>
                <td><a href="#" class="info"><?php echo _("Google Voice Phone Number")?>:<span><?php echo _("This is your Google Voice Phone Number <br />10 Digit Format")?></span></a></td>
            	<td><input type="text" name="number" maxlength="12" value="<?php echo isset($form_number) ? $form_number : ''; ?>"></td>
			</tr>
			<tr>
                <td><a href="#" class="info"><?php echo _($type." Trunk")?>:<span><?php echo _("Automatically Add this Account as a Trunk")?></span></a></td>
            	<td><input type="checkbox" name="trunk" <?php echo isset($form_trunk) && $form_trunk ? 'CHECKED' : ''; ?>></td>
			</tr>
			<tr>
                <td><a href="#" class="info"><?php echo _($type." Outbound Routes")?>:<span><?php echo _("Automatically Add Outbound Route for this Account")?></span></a></td>
            	<td><input type="checkbox" name="obroute" <?php echo isset($form_obroute) && $form_obroute ? 'CHECKED' : ''; ?>></td>
			</tr>
			<!-- not configured
			<tr>
                <td><a href="#" class="info"><?php echo _($type. " Inbound Routes")?>:<span><?php echo _("Automatically Add Inbound Routes for this Account")?></span></a></td>
            	<td><input type="checkbox" name="ibroute" <?php echo isset($form_ibroute) && $form_ibroute ? 'CHECKED' : ''; ?>></td>
			</tr>
			-->
			<tr>
                <td><a href="#" class="info"><?php echo _("Send Unanswered to Google Voicemail")?>:<span><?php echo _("Send unanswered calls to Google voicemail after 25 seconds<br />Note: You MUST route this to a device that can answer. Example: IVRs and Phone directories can NOT answer")?></span></a></td>
            	<td><input type="checkbox" name="gvm" <?php echo isset($form_gvm) && $form_gvm ? 'CHECKED' : ''; ?>></td>
			</tr>
        </table>
		<br />
        <table>
            <tr>
                <td colspan="2"><h5><?php echo _("Advanced Settings")?></h5><hr></td>
            </tr>
            <tr>
               <td><a href="#" class="info"><?php echo _("Google Voice Status Message")?>:<span><?php echo _("This is your Google Voice Status Message that buddies will see")?></span></a></td>
               <td><input type="text" name="statusmessage" value="<?php echo isset($form_statusmessage) ? $form_statusmessage : ''; ?>"></td>
            </tr>
            <tr>
               <td><a href="#" class="info"><?php echo _("XMPP Priority")?>:<span><?php echo _("This is the priority of where google will route an inbound call. A higher number has a higher priority. We believe that:<ul><li>Windows Gtalk client is 20</li><li>GMail is 24</li><li>Pidgin would be 0 or 1</li></ul>The range of acceptable values is -128 to +127. Anything else will be reset to the highest or lowest value.")?></span></a></td></td>
               <td><input type="text" name="priority" value="<?php echo isset($form_priority) ? $form_priority : '127'; ?>"></td>
            </tr>
				<td><a href="#" class="info"><?php echo _("Always Answer (IVR Mode)")?><span><?php echo _("Add a stealth greeting so Google Voice is forced to pass the call when you want unanswered calls to go to GoogleVoice Voicemail (above). WARNING: The PBX will always answer, however if the PBX goes offline then GoogleVoice Voicemail will pick the call up.")?></span></a></td>
				<td><input type="checkbox" name="greeting" <?php echo isset($form_greeting) && $form_greeting ? 'CHECKED' : ''; ?>></td>
        </table>
		<br />
		<?php if($action == 'edit') { ?>
        <table>
            <tr>
                <td colspan="2"><h5><?php echo _("Detailed Status")?></h5><hr></td>
            </tr>
            <tr>
                <td><u><?php echo _("Status")?>:</u></td>
				<td><?php if($status['connected']) { ?><div style="background-color:green; color:white; height: 20px; width: 80px; text-align: center"><?php echo _("Connected")?></div><?php } else { ?><div style="background-color:red; color:white; height: 20px; width: 80px; text-align: center"><?php echo _("Disconnected")?></div><?php } ?></td>
            </tr>
			<tr>
				<td colspan="2"><u><?php echo _("Buddies")?>:</u></td>
			</tr>
			<tr>
				<td colspan="2">
					<ul>
			<?php foreach($buddies as $list) {?>
                <li><?php echo $list ?></li>
			<?php } ?>
					</ul>
				</td>
			</tr>
        </table>
		<?php } ?>
		<br />
		<br />
		<input type="submit" value="Submit">
    </form>
