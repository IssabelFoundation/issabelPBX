<?php

echo '<div class="content">';

echo heading(__('Backup Servers'), 2);

echo '<a href="config.php?type=setup&display=backup_servers&action=edit&server_type=email">';
echo '<button class="button is-link is-light"><span class="icon is-small is-left"><i class="fa fa-plus"></i></span><span>'.__('New Email server').'</span></button>';
echo '</a><br/><br/>';

echo '<a href="config.php?type=setup&display=backup_servers&action=edit&server_type=ftp">';
echo '<button class="button is-link is-light"><span class="icon is-small is-left"><i class="fa fa-plus"></i></span><span>'.__('New FTP server').'</span></button>';
echo '</a><br/><br/>';

//echo '<a href="config.php?type=setup&display=backup_servers&action=edit&server_type=http">';
//echo '<input type="button" class="button is-link is-light mb-3" value="' . __('New HTTP server') . '"></a><br />';

//echo '<a href="config.php?type=setup&display=backup_servers&action=edit&server_type=smb">';
//echo '<input type="button" class="button is-link is-light mb-3" value="' . __('New Samba server') . '"></a><br />';

echo '<a href="config.php?type=setup&display=backup_servers&action=edit&server_type=local">';
echo '<button class="button is-link is-light"><span class="icon is-small is-left"><i class="fa fa-plus"></i></span><span>'.__('New Local server').'</span></button>';
echo '</a><br/><br/>';

echo '<a href="config.php?type=setup&display=backup_servers&action=edit&server_type=mysql">';
echo '<button class="button is-link is-light"><span class="icon is-small is-left"><i class="fa fa-plus"></i></span><span>'.__('New Mysql server').'</span></button>';
echo '</a><br/><br/>';

echo '<a href="config.php?type=setup&display=backup_servers&action=edit&server_type=ssh">';
echo '<button class="button is-link is-light"><span class="icon is-small is-left"><i class="fa fa-plus"></i></span><span>'.__('New SSH server').'</span></button>';
echo '</a><br/><br/>';


echo '<script>';
echo js_display_confirmation_toasts();
echo '</script>';
