<h2><?php echo (isset($_REQUEST['action']) && $_REQUEST['action'] == 'showuser') ? __("Edit User") : __("Add User")?></h2>
<?php if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'showuser') {?>
	<p>
		<a href="config.php?display=userman&amp;action=deluser&amp;user=<?php echo $user['id']?>">
			<span>
				<img width="16" height="16" border="0" title="<?php echo sprintf(__('Delete User %s'),$user['username'])?>" alt="<?php echo sprintf(__('Delete User %s'),$user['username'])?>" src="images/core_delete.png"><?php echo sprintf(__('Delete User %s'),$user['username'])?>
			</span>
		</a>
	</p>
<?php } ?>
<?php if(!empty($message)) {?>
	<div class="alert alert-<?php echo $message['type']?>"><?php echo $message['message']?></div>
<?php } ?>
<form autocomplete="off" name="editM" action="" method="post">
	<input type="hidden" name="type" value="user">
	<input type="hidden" name="prevUsername" value="<?php echo !empty($user['username']) ? $user['username'] : ''; ?>">
	<input type="hidden" name="user" value="<?php echo !empty($user['id']) ? $user['id'] : ''; ?>">
	<table>
		<tr class="guielToggle" data-toggle_class="userman">
			<td colspan="2"><h4><span class="guielToggleBut">-  </span><?php echo __("User Settings")?></h4><hr></td>
		</tr>
		<tr class="userman">
			<td><a href="#" class="info"><?php echo __("Login Name")?>:<span><?php echo __("This is the name that the user will use when logging in.")?></span></a></td>
			<td><input type="text" autocomplete="off" name="username" maxlength="100" value="<?php echo !empty($user['username']) ? $user['username'] : ''; ?>"></td>
		</tr>
		<tr class="userman">
			<td><a href="#" class="info"><?php echo __("Description")?>:<span><?php echo __("A brief description for this user.")?></span></a></td>
			<td><input type="text" autocomplete="off" name="description" maxlength="100" value="<?php echo !empty($user['description']) ? $user['description'] : ''; ?>"></td>
		</tr>
		<tr class="userman">
			<td><a href="#" class="info"><?php echo __("Password")?>:<span><?php echo __("The user's password.")?></span></a></td>
			<td><input type="password" autocomplete="off" name="password" maxlength="150" value="<?php echo !empty($user['password']) ? '******' : ''; ?>"></td>
		</tr>
		<tr class="userman">
			<td><a href="#" class="info"><?php echo __("First Name")?>:<span><?php echo __("The user's first name.")?></span></a></td>
			<td><input type="text" autocomplete="off" name="fname" maxlength="100" value="<?php echo !empty($user['fname']) ? $user['fname'] : ''; ?>"></td>
		</tr>
		<tr class="userman">
			<td><a href="#" class="info"><?php echo __("Last Name")?>:<span><?php echo __("The user's last name.")?></span></a></td>
			<td><input type="text" autocomplete="off" name="lname" maxlength="100" value="<?php echo !empty($user['lname']) ? $user['lname'] : ''; ?>"></td>
		</tr>
		<tr class="userman">
			<td><a href="#" class="info"><?php echo __("Title")?>:<span><?php echo __("The user's title.")?></span></a></td>
			<td><input type="text" autocomplete="off" name="title" maxlength="100" value="<?php echo !empty($user['title']) ? $user['title'] : ''; ?>"></td>
		</tr>
		<tr class="userman">
			<td><a href="#" class="info"><?php echo __("Email Address")?>:<span><?php echo __("The email address to associate with this user.")?></span></a></td>
			<td><input type="text" autocomplete="off" name="email" maxlength="100" value="<?php echo !empty($user['email']) ? $user['email'] : ''; ?>"></td>
		</tr>
		<tr class="userman">
			<td><a href="#" class="info"><?php echo __("Cell Phone Number")?>:<span><?php echo __("The user's cell (mobile) phone number.")?></span></a></td>
			<td><input type="text" autocomplete="off" name="cell" maxlength="100" value="<?php echo !empty($user['cell']) ? $user['cell'] : ''; ?>"></td>
		</tr>
		<tr class="userman">
			<td><a href="#" class="info"><?php echo __("Work Phone Number")?>:<span><?php echo __("The user's work phone number.")?></span></a></td>
			<td><input type="text" autocomplete="off" name="work" maxlength="100" value="<?php echo !empty($user['work']) ? $user['work'] : ''; ?>"></td>
		</tr>
		<tr class="userman">
			<td><a href="#" class="info"><?php echo __("Home Phone Number")?>:<span><?php echo __("The user's home phone number.")?></span></a></td>
			<td><input type="text" autocomplete="off" name="home" maxlength="100" value="<?php echo !empty($user['home']) ? $user['home'] : ''; ?>"></td>
		</tr>
		<tr class="userman">
			<td><a href="#" class="info"><?php echo __("Send Welcome Email")?>:<span><?php echo __("Choose whether the user should receive a welcome email sent to his/her address when these contents are saved.")?></span></a></td>
			<td>
				<span class="radioset">
					<input type="radio" id="sendEmail1" name="sendEmail" value="yes" <?php echo (!isset($_REQUEST['action']) || $_REQUEST['action'] != 'showuser') ? 'checked' : ''?>><label for="sendEmail1">Yes</label>
					<input type="radio" id="sendEmail2" name="sendEmail" value="no" <?php echo (isset($_REQUEST['action']) && $_REQUEST['action'] == 'showuser') ? 'checked' : ''?>><label for="sendEmail2">No</label>
				</span>
			</td>
		</tr>
		<tr class="userman">
			<td><a href="#" class="info"><?php echo __("Linked Extension")?>:<span><?php echo __("This is the extension this user is linked to from the Extensions page. A single user can only be linked to one extension, and one extension can only be linked to a single user. If using Rest Apps on a phone, this is the extension that will be mapped to the API permissions set below for this user.")?></span></a></td>
			<td>
				<select id="defaultextension" name="defaultextension">
					<?php foreach($dipbxusers as $dipbxuser) {?>
						<option value="<?php echo $dipbxuser['ext']?>" <?php echo $dipbxuser['selected'] ? 'selected' : '' ?>><?php echo $dipbxuser['name']?> &lt;<?php echo $dipbxuser['ext']?>&gt;</option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr class="userman">
			<td><a href="#" class="info"><?php echo __("Additional Assigned Extensions")?>:<span><?php echo __("Additional Extensions over which this user will have control.")?></span></a></td>
			<td>
				<div class="extensions-list">
				<?php foreach($ipbxusers as $ipbxuser) {?>
					<label><input class="extension-checkbox" data-name="<?php echo $ipbxuser['name']?>" data-extension="<?php echo $ipbxuser['ext']?>" type="checkbox" name="assigned[]" value="<?php echo $ipbxuser['ext']?>" <?php echo $ipbxuser['selected'] ? 'checked' : '' ?>> <?php echo $ipbxuser['name']?> &lt;<?php echo $ipbxuser['ext']?>&gt;</label><br />
				<?php } ?>
				</div>
			</td>
		</tr>
	</table>
	<?php echo $hookHtml;?>
	<table>
		<tr>
			<td colspan="2"><input type="submit" name="submit" value="<?php echo __('Submit')?>"></td>
		</tr>
	</table>
</form>
