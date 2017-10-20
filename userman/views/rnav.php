<div class='rnav'>
	<ul>
		<li class="rnav-heading"><?php echo _('Settings')?></li>
		<li><a href='config.php?display=userman&amp;action=general'><?php echo _('General')?></a></li>
		<li class="rnav-heading"><?php echo _('User List')?></li>
		<li><a href='config.php?display=userman&amp;action=adduser'><?php echo _('Add New User')?></a></li>
		<li><hr></li>
		<?php foreach($users as $user) {?>
			<li><a href='config.php?display=userman&amp;action=showuser&amp;user=<?php echo $user['id']?>'><?php echo $user['username']?></a></li>
		<?php }?>
	</ul>
</div>
