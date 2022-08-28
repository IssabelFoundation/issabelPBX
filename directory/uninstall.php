<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
outn(_('dropping directory_details, directory_entries..'));
sql('DROP TABLE directory_details');
sql('DROP TABLE directory_entries');
out(_('ok'));

outn(_('deleting default_directory and migration tracking keys..'));
sql("DELETE FROM `admin` WHERE `variable` = 'default_directory'");
sql("DELETE FROM `admin` WHERE `variable` = 'directory28_migrated'");
out(_('ok'));
?>
