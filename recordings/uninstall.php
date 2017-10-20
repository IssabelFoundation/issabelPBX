<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

// Delete them all even if they should not exist just in case
//

// Don't bother uninstalling feature codes, now module_uninstall does it

sql('DROP TABLE IF EXISTS recordings');

?>
