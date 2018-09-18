#!/usr/bin/php -q
<?php
	$current_user = posix_getpwuid(posix_geteuid());
	if ($current_user['uid'] !== 0) {
		die('Forbidden - must be root');
	}
  // Generate the a list of variables that can be sourced by
  // a bash script
  $bootstrap_settings['issabelpbx_auth'] = false;
  $bootstrap_settings['skip_astman'] = true;//no need for astman here
  $restrict_mods = true;//no need for modules here
  if (!@include_once(getenv("ISSABELPBX_CONF") ? getenv("ISSABELPBX_CONF") : "/etc/issabelpbx.conf")) {
    include_once("/etc/asterisk/issabelpbx.conf");
  }
  foreach($amp_conf as $key => $val) {
    if (is_bool($val)) {
      echo "export " . trim($key) . "=" . ($val?"TRUE":"FALSE") ."\n";
    } else {
      if(!strstr($val," ")) {
        echo "export " . trim($key) . "=" . escapeshellcmd(trim($val)) ."\n";
      }
    }
  }
