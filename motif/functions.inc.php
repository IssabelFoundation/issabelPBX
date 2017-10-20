<?php
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//  Copyright 2012 Andrew Nagy
//
global $amp_conf;

function motif_get_config($engine) {
	global $db;
	global $amp_conf;
	global $ext;  // is this the best way to pass this?
	global $asterisk_conf;
	global $core_conf;
	global $version;

	switch($engine) {
		case "asterisk":
			if(method_exists($core_conf, "addRtpAdditional")) {
				$core_conf->addRtpAdditional('general', array("icesupport" => "yes"));
			} else {
				throw new \Exception(_("Please update the module named 'motif' or enable the module named 'core'."));
			}
			break;
	}
}

class motif_conf {

	//Tell which files we want to 'control'
	function get_filename() {
        $files = array(
            'motif.conf',
            'xmpp.conf',
            'extensions_additional.conf'
        );
        return $files;
	}

	//This function is called for every file defined in 'get_filename()' function above
	function generateConf($file) {
        global $version,$amp_conf,$astman;

		//Create all custom files
        if(!file_exists($amp_conf['ASTETCDIR'] . '/xmpp_general_custom.conf')) {
            touch($amp_conf['ASTETCDIR'] . '/xmpp_general_custom.conf');
        }

        if(!file_exists($amp_conf['ASTETCDIR'] . '/motif_custom.conf')) {
            touch($amp_conf['ASTETCDIR'] . '/motif_custom.conf');
        }

        if(!file_exists($amp_conf['ASTETCDIR'] . '/xmpp_custom.conf')) {
            touch($amp_conf['ASTETCDIR'] . '/xmpp_custom.conf');
        }

		//Backup all old xmpp & motif.conf files
        if(file_exists($amp_conf['ASTETCDIR'] . '/xmpp.conf') && !file_exists($amp_conf['ASTETCDIR'] . '/xmpp.conf.bak')) {
            copy($amp_conf['ASTETCDIR'] . '/xmpp.conf', $amp_conf['ASTETCDIR'] . '/xmpp.conf.bak');
        }

        if(file_exists($amp_conf['ASTETCDIR'] . '/motif.conf') && !file_exists($amp_conf['ASTETCDIR'] . '/motif.conf.bak')) {
            copy($amp_conf['ASTETCDIR'] . '/motif.conf', $amp_conf['ASTETCDIR'] . '/motif.conf.bak');
        }

		//Disable gtalk and jabber
		if(file_exists($amp_conf['ASTETCDIR'] . '/jabber.conf') && !file_exists($amp_conf['ASTETCDIR'] . '/jabber.conf.old')) {
            rename($amp_conf['ASTETCDIR'] . '/jabber.conf', $amp_conf['ASTETCDIR'] . '/jabber.conf.old');
        }

		if($astman->mod_loaded('jabber')) {
			$astman->send_request('Command', array('Command' => 'module unload res_jabber.so'));
		}

        if(file_exists($amp_conf['ASTETCDIR'] . '/gtalk.conf') && !file_exists($amp_conf['ASTETCDIR'] . '/gtalk.conf.old')) {
            rename($amp_conf['ASTETCDIR'] . '/gtalk.conf', $amp_conf['ASTETCDIR'] . '/gtalk.conf.old');
        }

		if($astman->mod_loaded('gtalk')) {
			$astman->send_request('Command', array('Command' => 'module unload chan_gtalk.so'));
		}

		//Setup specific file matching
        switch ($file) {
            case 'motif.conf':
                return $this->generate_motif_conf($version);
                break;
            case 'xmpp.conf':
                return $this->generate_xmpp_conf($version);
                break;
            case 'extensions_additional.conf':
                return $this->generate_extensions_conf($version);
                break;
        }
    }

    function generate_motif_conf($ast_version) {
        global $astman;

		$sql = 'SELECT * FROM `motif`';
		$accounts = sql($sql, 'getAll', DB_FETCHMODE_ASSOC);

		//Clear output for motif file
                $output = '';
		foreach($accounts as $list) {
			$context = str_replace('@','',str_replace('.','',$list['username'])); //Remove special characters for use in contexts. There still might be a char limit though
			$output .= "[g".$context."]\n"; //Add contexts for each 'line'
			$output .= "context=im-".$context."\n";
			$output .= "disallow=all\n";
			$output .= "allow=ulaw\n";
			$output .= "connection=g".$context."\n\n";
		}
		$output .= "#include motif_custom.conf\n";
		return $output;
	}

	function generate_xmpp_conf($ast_version) {
		global $astman,$db;

		$sql = 'SELECT * FROM `motif`';
		$accounts = sql($sql, 'getAll', DB_FETCHMODE_ASSOC);

		$output = "[general]\n\n";
		$output .= "#include xmpp_general_custom.conf\n\n";

		foreach($accounts as $list) {
			$context = str_replace('@','',str_replace('.','',$list['username'])); //Remove special characters for use in contexts. There still might be a char limit though

			$output .= "[g".$context."]\n";
			$output .= "type=client\n";
			$output .= "serverhost=talk.google.com\n";

			$output .= "username=".$list['username']."\n";
			$output .= "secret=".$list['password']."\n";

			$output .= "port=5222\n";
			$output .= "usetls=yes\n";
			$output .= "usesasl=yes\n";
			$output .= "status=available\n";
			$output .= "statusmessage=\"".$list['statusmessage']."\"\n";
			$output .= "timeout=5\n";
			$output .= "priority=".$list['priority']."\n\n";

		}

		$output .= "#include xmpp_custom.conf\n";

		return $output;
	}

	function generate_extensions_conf($ast_version) {
        global $ext;

		$sql = 'SELECT * FROM `motif`';
		$accounts = sql($sql, 'getAll', DB_FETCHMODE_ASSOC);

		foreach($accounts as $list) {
		    $settings = unserialize($list['settings']);
			$context = str_replace('@','',str_replace('.','',$list['username'])); //Remove special characters for use in contexts. There still might be a char limit though

			$incontext = "im-".$context;
			$address = 's'; //Joshua Colp @ Digium: 'It will only accept from the s context'

			$ext->add($incontext, $address, '1', new ext_noop('Receiving GoogleVoice on DID: '.$list['phonenum']));

			$ext->add($incontext, $address, '', new ext_noop('${EXTEN}'));

			$ext->add($incontext, $address, '', new ext_setvar('CALLERID(name)', '${CUT(CALLERID(name),@,1)}'));
	        $ext->add($incontext, $address, '', new ext_gotoif('$["${CALLERID(name):0:2}" != "+1"]', 'nextstop'));
	        $ext->add($incontext, $address, '', new ext_setvar('CALLERID(name)', '${CALLERID(name):2}'));
	        $ext->add($incontext, $address, 'nextstop', new ext_gotoif('$["${CALLERID(name):0:1}" != "+"]', 'notrim'));
	        $ext->add($incontext, $address, '', new ext_setvar('CALLERID(name)', '${CALLERID(name):1}'));
	        $ext->add($incontext, $address, 'notrim', new ext_setvar('CALLERID(number)', '${CALLERID(name)}'));



	        if(isset($settings['gvm']) && $settings['gvm']) {
	            $ext->add($incontext, $address, '', new ext_setvar('DIAL_OPTIONS', '${DIAL_OPTIONS}aD(:1)'));
				if(isset($settings['greeting']) && $settings['greeting']) {
					$ext->add($incontext, $address, '', new ext_answer(''));
					$ext->add($incontext, $address, '', new ext_playback('hello') );
					$ext->add($incontext, $address, '', new ext_senddtmf('1'));
				}
	        } else {
				$ext->add($incontext, $address, '', new ext_wait('1'));
				$ext->add($incontext, $address, '', new ext_answer(''));
				$ext->add($incontext, $address, '', new ext_senddtmf('1'));
	        }

			$ext->add($incontext, $address, '', new ext_goto('1', $list['phonenum'], 'from-trunk'));

			$ext->add($incontext, 'h', '', new ext_hangup(''));
		}
		return $ext->generateConf();
	}
}
