 <!-- right side menu -->
	<div class="rnav">
		<ul>
			<a href="config.php?display=motif"><?php echo _("Add Google Voice Account")?></a><br />
			<hr>
			<?php foreach($accounts as $list) { ?>
				<a href="config.php?display=motif&amp;action=edit&amp;id=<?php echo $list['id']; ?>"><?php echo $list['phonenum']; ?> [<?php echo $list['username']; ?>]</a><br />
			<?php } ?>
		</ul>
	</div>

