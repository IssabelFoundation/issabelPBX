<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

class procinfo {
	var $distro;
	
	function procinfo($distro = false) {
		$this->distro = $distro;
	}
	
	function check_port($port, $server = "localhost") {
		$timeout = 5;
		if ($sock = @fsockopen($server, $port, $errno, $errstr, $timeout)) {
			fclose($sock);
			return true;
		}
		return false;
	}
	
	/* FOP has been removed, currenlty unsupported optional module
	function check_fop_server() {
    global $amp_conf;
    $fop_settings = parse_ini_file($amp_conf['FOPWEBROOT'].'/op_server.cfg');
    if (is_array($fop_settings)) {
      $listen_port = isset($fop_settings['listen_port']) && trim($fop_settings['listen_port']) != ''?$fop_settings['listen_port']:4445;
    } else {
      $listen_port = 4445;
    }

		return $this->check_port($listen_port);
	}
	 */
	
	function check_mysql($hoststr) {
		$host = 'localhost';
		$port = '3306';
		if (preg_match('/^([^:]+)(:(\d+))?$/',$hoststr,$matches)) {
			// matches[1] = host, [3] = port
			$host = $matches[1];
			if (!empty($matches[3])) {
				$port = $matches[3];
			}
		}
		return $this->check_port($port, $host);
	}
}

?>
