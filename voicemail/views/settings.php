<?php if (!empty($extension)) { ?>
<div id="tabs2" class="tabs is-boxed">
    <ul>
        <li data-tab="1" class="<?php echo ($action == "bsettings") ? 'is-active' : ''?>"><a href='config.php?display=voicemail&amp;action=bsettings&amp;extdisplay=<?php echo $extension ?>'><?php echo __('Settings');?></a></li>
        <li data-tab="1" class="<?php echo ($action == "usage") ? 'is-active' : ''?>"><a href='config.php?display=voicemail&amp;action=usage&amp;extdisplay=<?php echo $extension ?>'><?php echo __('Usage');?></a></li>
        <li data-tab="1" class="<?php echo ($action == "settings") ? 'is-active' : ''?>"><a href='config.php?display=voicemail&amp;action=settings&amp;extdisplay=<?php echo $extension ?>'><?php echo __('Advanced Settings');?></a></li>
    </ul>
</div>
<?php } ?>

<table class='table is-borderless is-narrow'>

<?php if (!empty($extension)) { ?>
<tr>
    <td colspan='2'>
        <h5><?php echo _dgettext('amp','Extension').': '.$extension;?></h5>
    </td>
</tr>
<?php } ?>

