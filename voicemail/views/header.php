<div class='content'>

<?php echo $title; ?>


<div id="tabs" class="tabs is-boxed">
    <ul>
        <li data-tab="1" class="<?php echo ($sys_view_flag && $action == "usage") ? 'is-active' : ''?>"><a href='config.php?display=voicemail&amp;action=usage'><?php echo _('Usage');?></a></li>
        <li data-tab="1" class="<?php echo ($sys_view_flag && $action == "settings") ? 'is-active' : ''?>"><a href='config.php?display=voicemail&amp;action=settings'><?php echo _('Settings');?></a></li>
        <li data-tab="1" class="<?php echo ($sys_view_flag && $action == "dialplan") ? 'is-active' : ''?>"><a href='config.php?display=voicemail&amp;action=dialplan'><?php echo _('Dialplan Behavior');?></a></li>
        <li data-tab="1" class="<?php echo ($sys_view_flag && $action == "tz") ? 'is-active' : ''?>"><a href='config.php?display=voicemail&amp;action=tz'><?php echo _('Timezone Definitions');?></a></li>
    </ul>
</div>


<!--table class='table'>
<tr><td>
<div class='columns'>
<div class='column'>
<h5><?php echo _("System View Links:") ?></h5>
</div>
<div class='column'>
<h5><a style="<?php echo ($sys_view_flag && $action == "dialplan") ? 'color:#ff9933' : ''?>" href='config.php?display=voicemail&amp;action=dialplan'><?php echo _('Dialplan Behavior');?></a></h5>
</div>
<div class='column'>
<h5><a style="<?php echo ($sys_view_flag && $action == "settings") ? 'color:#ff9933' : ''?>" href='config.php?display=voicemail&amp;action=settings'><?php echo _('Settings');?></a></h5>
</div>
<div class='column'>
<h5><a style="<?php echo ($sys_view_flag && $action == "usage") ? 'color:#ff9933' : ''?>" href='config.php?display=voicemail&amp;action=usage'><?php echo _('Usage');?></a></h5>
</div>
<div class='column'>
<h5><a style="<?php echo ($sys_view_flag && $action == "tz") ? 'color:#ff9933' : ''?>" href='config.php?display=voicemail&amp;action=tz'><?php echo _('Timezone Definitions');?></a></h5>
</div>
</div>
</td></tr>
</table-->

<form id='mainform' name='frm_voicemail' method='post' onsubmit='return voicemail_submit()'">
	<input type='hidden' name='type' id='type' value='<?php echo $type ?>' />
	<input type='hidden' name='display' id='display' value='<?php echo $display ?>' />
	<input type='hidden' name='ext' id='ext' value='<?php echo $extension ?>' />
    <input type='hidden' name='page_type' id='page_type' value='<?php echo $action ?>' />

        <!--table class='table is-narrow is-borderless'>
            <tr>
				<td colspan='3'><?php echo $title?></td>
			</tr>
			<tr>
				<td>
					<h5><?php echo _("System View Links:") ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h5>
				</td>
				<td colspan='2'>
					<h5>
						<a style="<?php echo ($sys_view_flag && $action == "dialplan") ? 'color:#ff9933' : ''?>" href='config.php?display=voicemail&amp;action=dialplan'><?php echo _('Dialplan Behavior');?></a>&nbsp;&nbsp;|&nbsp;&nbsp;
						<a style="<?php echo ($sys_view_flag && $action == "settings") ? 'color:#ff9933' : ''?>" href='config.php?display=voicemail&amp;action=settings'><?php echo _('Settings');?></a>&nbsp;&nbsp;|&nbsp;&nbsp;
						<a style="<?php echo ($sys_view_flag && $action == "usage") ? 'color:#ff9933' : ''?>" href='config.php?display=voicemail&amp;action=usage'><?php echo _('Usage');?></a>&nbsp;&nbsp;|&nbsp;&nbsp;
						<a style="<?php echo ($sys_view_flag && $action == "tz") ? 'color:#ff9933' : ''?>" href='config.php?display=voicemail&amp;action=tz'><?php echo _('Timezone Definitions');?></a>
					</h5>
				</td>
			</tr>
		</table-->
