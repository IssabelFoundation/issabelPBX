<?php

echo heading('Backup Servers', 3) . '<hr class="backup-hr"/>';

echo '<a href="config.php?type=setup&display=backup_servers&action=edit&server_type=email">';
echo '<input type="button" value="' . _('New Email server') . '"></a><br />';

echo '<a href="config.php?type=setup&display=backup_servers&action=edit&server_type=ftp">';
echo '<input type="button" value="' . _('New FTP server') . '"></a><br />';

//echo '<a href="config.php?type=setup&display=backup_servers&action=edit&server_type=http">';
//echo '<input type="button" value="' . _('New HTTP server') . '"></a><br />';

//echo '<a href="config.php?type=setup&display=backup_servers&action=edit&server_type=smb">';
//echo '<input type="button" value="' . _('New Samba server') . '"></a><br />';

echo '<a href="config.php?type=setup&display=backup_servers&action=edit&server_type=local">';
echo '<input type="button" value="' . _('New Local server') . '"></a><br />';

echo '<a href="config.php?type=setup&display=backup_servers&action=edit&server_type=mysql">';
echo '<input type="button" value="' . _('New Mysql server') . '"></a><br />';

echo '<a href="config.php?type=setup&display=backup_servers&action=edit&server_type=ssh">';
echo '<input type="button" value="' . _('New SSH server') . '"></a><br />';
echo br(20);
