<?php
/* $Id:$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

sql("DROP TABLE `sipsettings`");
sql("DROP TABLE `pjsipsettings`");
