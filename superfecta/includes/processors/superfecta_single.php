<?php

class superfecta_single extends superfecta_base {

    public $name = 'Single';
    public $description = 'Runs all sources in specified order, like old superfecta';
    public $type = 'SINGLE';

    function __construct($options=array()) {
		if(!empty($options)) {
	        $this->setDebug($options['debug']);
	        $sn = explode("_", $options['scheme_name']);
	        $this->scheme_name = $sn[1];
	        $this->scheme = isset($options['scheme_name']) ? $options['scheme_name'] : '';
	        $this->db = isset($options['db']) ? $options['db'] : '';
	        $this->amp_conf = isset($options['amp_conf']) ? $options['amp_conf'] : '';
	        $this->astman = isset($options['astman']) ? $options['astman'] : '';
	        $this->scheme_param = isset($options['scheme_parameters']) ? $options['scheme_parameters'] : '';
	        $this->path_location = isset($options['path_location']) ? $options['path_location'] : '';
	        $this->trunk_info = isset($options['trunk_info']) ? $options['trunk_info'] : ''; 
		}   
    }

    function is_master() {
        return(TRUE);
    }

    function get_results() {
        $caller_id = '';
        $sources = explode(",", $this->scheme_param['sources']);
        foreach ($sources as $data) {
            $this->caller_id = '';
            $start_time = $this->mctime_float();

            $sql = "SELECT field,value FROM superfectaconfig WHERE source = '" . $this->scheme_name . "_" . $data . "'";
            $run_param = $this->db->getAssoc($sql);

            $source_name = $this->path_location . "/source-" . $data . ".module";
            if (file_exists($source_name)) {
                require_once($source_name);
                $source_class = NEW $data;
                //Gotta be a better way to do this
                $source_class->setDebug($this->getDebug());
                $source_class->set_AmpConf($this->amp_conf);
                $source_class->set_DB($this->db);
                $source_class->set_AsteriskManager($this->astman);
                $source_class->set_TrunkInfo($this->trunk_info);

                if (method_exists($source_class, 'get_caller_id')) {
                    $caller_id = $source_class->get_caller_id($this->trunk_info['callerid'], $run_param);
                    $this->set_CacheFound($source_class->isCacheFound());
                    $this->setSpam($source_class->isSpam());
                    if ($source_class->isSpam()) {
                        $this->set_SpamCount($this->get_SpamCount() + 1);
                    }
                    unset($source_class);
                    $caller_id = $this->_utf8_decode($caller_id);


                    if (($this->first_caller_id == '') && ($caller_id != '')) {
                        $this->first_caller_id = $caller_id;
                        $winning_source = $data;
                        if ($this->isDebug()) {
                            $end_time_whole = $this->mctime_float();
                        }
                    }
                } else {
                    $this->DebugPrint("Function 'get_caller_id' does not exist!");
                }
            } else {
                $this->DebugPrint("Unable to find source '" . $source_name . "' skipping..");
            }

            if ($this->isDebug()) {
                if ($caller_id != '') {
                    print "'" . utf8_encode($caller_id) . "'<br>\nresult <img src='images/scrollup.gif'> took " . number_format(($this->mctime_float() - $start_time), 4) . " seconds.<br>\n<br>\n";
                } else {
                    print "result <img src='images/scrollup.gif'> took " . number_format(($this->mctime_float() - $start_time), 4) . " seconds.<br>\n<br>\n";
                }
            } else if ($caller_id != '') {
                break;
            }
        }
        return($this->first_caller_id);
    }

    function send_results($caller_id) {
        $sources = explode(",", $this->scheme_param['sources']);

        $this->DebugPrint("Post CID retrieval processing.");

        foreach ($sources as $source_name) {
            // Run the source
            $sql = "SELECT field,value FROM superfectaconfig WHERE source = '" . $this->scheme_name . "_" . $source_name . "'";
            $run_param = $this->db->getAssoc($sql);
            $source_file = $this->path_location . "/source-" . $source_name . ".module";
            if (file_exists($source_file)) {
                require_once($source_file);
                $source_class = NEW $source_name;
                $source_class->set_DB($this->db);
                $source_class->setDebug($this->getDebug());
                if (method_exists($source_class, 'post_processing')) {
                    $caller_id = $source_class->post_processing($this->isCacheFound(), NULL, $this->first_caller_id, $run_param, $this->trunk_info['callerid']);
                } else {
                    print "Method 'post_processing' doesn't exist<br\>\n";
                }
            } else {
                $this->DebugPrint("Couldn't load " . $source_name . " for post processing");
            }
        }
    }

    //Run this when web debug is initiated
    function web_debug() {
        return($this->get_results());
    }

}