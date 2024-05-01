<h2><?php echo __('General Settings')?></h2>
<?php if(!empty($message)) {?>
	<div class="alert alert-<?php echo $message['type']?>"><?php echo $message['message']?></div>
<?php } ?>
<form autocomplete="off" name="general" action="" method="post">
	<input type="hidden" name="type" value="general">
	<table>
		<tr class="guielToggle" data-toggle_class="userman">
			<td colspan="2"><h4><span class="guielToggleBut">-  </span><?php echo __("Email Settings")?></h4><hr></td>
		</tr>
		<tr class="userman">
			<td><a href="#" class="info"><?php echo __("Email Body")?>:<span><?php echo sprintf(__("Text to be used for the body of the welcome email. Useable variables are:<ul><li>fname: First name</li><li>lname: Last name</li><li>brand: %s</li><li>title: title</li><li>username: Username</li><li>password: Password</li></ul>"),$brand)?></span></a></td>
			<td>
				<textarea name="email" rows="15" cols="80"><?php echo !empty($email) ? $email : file_get_contents(__DIR__.'/emails/welcome_text.tpl')?></textarea>
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
