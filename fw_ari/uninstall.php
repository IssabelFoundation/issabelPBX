<?php
global $amp_conf;
exec("rm -Rf ".$amp_conf['AMPWEBROOT']."/recordings/*");
file_put_contents($amp_conf['AMPWEBROOT']."/recordings/index.php","<?php header( 'Location: ../ucp' );");
