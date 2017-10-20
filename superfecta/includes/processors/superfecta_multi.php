<?php

class superfecta_multi extends superfecta_base {

    public $name = 'Multi';
    public $description = 'Multifecta, runs all sources at the same time';
    public $type = 'MULTI';

    function __construct($options=array()) {
		if(!empty($options)) {
	        $this->setDebug($options['debug']);
	        $sn = explode("_", $options['scheme_name']);
	        $this->scheme_name = $sn[1];
	        $this->scheme = $options['scheme_name'];
	        $this->db = $options['db'];
	        $this->amp_conf = $options['amp_conf'];
	        $this->astman = $options['astman'];
	        $this->scheme_param = $options['scheme_parameters'];
	        $this->path_location = $options['path_location'];
	        $this->multifecta_id = $options['multifecta_id'];
	        $this->source = $options['source'];
	        $this->trunk_info = $options['trunk_info'];  
	        //Check if we are a multifecta child, if so, get our variables from our child record
	        $this->multi_type = $this->multifecta_id ? 'CHILD' : 'PARENT';

	        if ($this->multi_type == "CHILD") {
	            $query = "SELECT mf.superfecta_mf_id, mf.scheme, mf.cidnum, mf.extension, mf.debug, mfc.source
						FROM superfecta_mf mf, superfecta_mf_child mfc
						WHERE mfc.superfecta_mf_child_id = " . $this->db->quoteSmart($this->multifecta_id) . "
						AND mf.superfecta_mf_id = mfc.superfecta_mf_id";

	            $res = $this->db->query($query);
	            if (DB::IsError($res)) {
	                $this->DebugDie("Unable to load child record: " . $res->getMessage());
	            }
	            if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {

	                $this->scheme = $row['scheme'];
	                $this->trunk_info['callerid'] = $row['cidnum'];
	                $this->DID = $row['extension'];
	                $this->multifecta_parent_id = $row['superfecta_mf_id'];
	                $this->single_source = $row['source'];
	            } else {
	                $this->DebugDie("Unable to load multifecta child record '" . $this->multifecta_id . "'");
	            }
	        }
		}
    }

    function is_master() {
        if ($this->multi_type == 'PARENT') {
            return(TRUE);
        } elseif ($this->multi_type == 'CHILD') {
            return(FALSE);
        }
    }

    function get_results() {
        if ($this->multi_type == 'PARENT') {
            return($this->run_parent());
        } elseif ($this->multi_type == 'CHILD') {
            $this->run_child();
        }
    }

    function run_parent() {
	
		global $db;
		global $amp_conf;
		
        // We are a multifecta parent
        $multifecta_start_time = $this->mctime_float();
        // Clean up multifecta records that are over 10 minutes old
        $query = "DELETE mf, mfc FROM superfecta_mf mf, superfecta_mf_child mfc
				WHERE mf.timestamp_start < " . $this->db->quoteSmart($multifecta_start_time - (60 * 10)) . "
				AND mfc.superfecta_mf_id = mf.superfecta_mf_id
				";
        $res2 = $this->db->query($query);
        if (DB::IsError($res2)) {
            $this->DebugDie("Unable to delete old multifecta records: " . $res2->getMessage());
        }

        // Prepare for launching children.
        $query = "INSERT INTO superfecta_mf (
				timestamp_start, 
				scheme, 
				cidnum, 
				extension, 
				prefix, 
				debug
			) VALUES (
				" . $this->db->quoteSmart($multifecta_start_time) . ",
				" . $this->db->quoteSmart($this->scheme) . ",
				" . $this->db->quoteSmart($this->trunk_info['callerid']) . ",
				" . $this->db->quoteSmart('NULL') . ",
				" . $this->db->quoteSmart('PREFIX') . ",
				" . $this->db->quoteSmart(($this->isDebug()) ? '1' : '0') . "
			)";
        // Create the parent record
        $res2 = $this->db->query($query);
        if (DB::IsError($res2)) {
            $this->DebugDie("Unable to create parent record: " . $res2->getMessage());
        }
        // (jkiel - 01/04/2011) Get id of the parent record 
        // (jkiel - 01/04/2011) [Insert complaints on Pear DB not supporting a last_insert_id method here]
        // (jkiel - 01/04/2011) What is the point of an abstraction layer when you are forced to bypass it?!?!?
		// instead of complaining, just fix it
		if(method_exists($db,'insert_id')) {
			$id = $db->insert_id();
		} else {
			$id = $amp_conf["AMPDBENGINE"] == "sqlite3" ? sqlite_last_insert_rowid($db->connection) : mysql_insert_id($db->connection);
		}
        if ($superfecta_mf_id = $id) {
            // We have the parent record id
            $this->DebugPrint("Multifecta Parent ID:" . $superfecta_mf_id);
        } else {
            $this->DebugDie("Unable to get parent record id");
        }
        $sources = explode(",", $this->scheme_param['sources']);
        $multifecta_count = 1;
        foreach ($sources as $data) {
            $multifecta_child_start_time = $this->mctime_float();
            $query = "INSERT INTO superfecta_mf_child (
						superfecta_mf_id,
						priority,
						source,
						timestamp_start
					) VALUES (
						" . $this->db->quoteSmart($superfecta_mf_id) . ",
						" . $this->db->quoteSmart($multifecta_count) . ",
						" . $this->db->quoteSmart($data) . ",
						" . $this->db->quoteSmart($multifecta_child_start_time) . "
					)";
            // Create the child record
            $res2 = $this->db->query($query);
            if (DB::IsError($res2)) {
                $this->DebugDie("Unable to create child record: " . $res2->getMessage());
            }
			if(method_exists($db,'insert_id')) {
				$id = $db->insert_id();
			} else {
				$id = $amp_conf["AMPDBENGINE"] == "sqlite3" ? sqlite_last_insert_rowid($db->connection) : mysql_insert_id($db->connection);
			}
            if ($superfecta_mf_child_id = $id) {
                $trunk_info = base64_encode(serialize($this->trunk_info));
                if ($this->isDebug()) {
                    $this->DebugPrint("Spawning child " . $superfecta_mf_child_id . ":" . $data);
                    exec('/usr/bin/php '.$amp_conf['AMPWEBROOT'].'/admin/modules/superfecta/includes/callerid.php -s ' . $this->scheme_name . ' -d ' . $this->getDebug() . ' -m ' . $superfecta_mf_child_id . ' -t ' . $trunk_info . ' -r ' . $data . ' > log-' . $superfecta_mf_child_id . '.log 2>&1 &');                    
                } else {
                    exec('/usr/bin/php '.$amp_conf['AMPWEBROOT'].'/admin/modules/superfecta/includes/callerid.php -s ' . $this->scheme_name . ' -m ' . $superfecta_mf_child_id . ' -t ' . $trunk_info . ' -r ' . $data . ' > /dev/null 2>&1 &');
                }
            }
            $multifecta_count++;
        }
        $this->DebugPrint("Parent took " . number_format(($this->mctime_float() - $multifecta_start_time), 4) . " seconds to spawn children.");
        $query = "SELECT superfecta_mf_child_id, priority, cnam, spam_text, spam, source, cached
				FROM superfecta_mf_child
				WHERE superfecta_mf_id = " . $this->db->quoteSmart($superfecta_mf_id) . "
				AND timestamp_cnam IS NOT NULL
				ORDER BY priority
				";
        $loop_limit = 200; // Loop 200 times maximum, just incase our timeout function fails
        $loop_start_time = $this->mctime_float();
        $loop_cur_time = $this->mctime_float();
        $loop_priority_time_limit = $this->scheme_param['multifecta_timeout'];
        $loop_time_limit = ($this->scheme_param['Curl_Timeout'] + .5); //Give us an extra half second over CURL
        $multifecta_timeout_hit = false;
        while ($loop_limit && (($loop_cur_time - $loop_start_time) <= $loop_time_limit)) {
            $res2 = $this->db->query($query);
            if (DB::IsError($res2)) {
                $this->DebugDie("Unable to search for winning child: " . $res2->getMessage());
            }
            $winning_child_id = false;
            $last_priority = 0;
            $first_caller_id = '';
            $spam_text = '';
            $spam = '';
            $spam_source = '';
            $spam_child_id = false;
            $loop_cur_time = $this->mctime_float();
            while ($res2 && ($row2 = $res2->fetchRow(DB_FETCHMODE_ASSOC))) {
                /*                 * * FUTURE
                  echo "<pre>";
                  print_r($row2);
                  echo "</pre>";
                  if($row2['cnam'] && (!$first_caller_id)){
                  $first_caller_id = $row2['cnam'];
                  $winning_child_id = $row2['superfecta_mf_child_id'];
                  $winning_source = $row2['source'];
                  $cache_found = $row2['cached'];
                  break;
                  }
                 * */
                // Wait for a winning child, in the order of it's preference
                // Take the first to finish after multifecta_timeout is reached
                if (($row2['priority'] == $last_priority)
                        || ($loop_limit == 1)
                        || (($loop_cur_time - $loop_start_time) > $loop_time_limit)
                        || (($loop_cur_time - $loop_start_time) > $loop_priority_time_limit)
                ) {
                    if ((!$multifecta_timeout_hit) && (($loop_cur_time - $loop_start_time) > $loop_priority_time_limit)) {
                        $multifecta_timeout_hit = true;
                        $this->DebugPrint("Multifecta Timeout reached.  Taking first child with a CNAM result.");
                    }
                    // Record the results of any spam sources
                    // We dont break out of the loop for spam though.  We'll just keep
                    // checking it over and over until we get a cnam or we time-out.
                    $spam_text = (($row2['spam_text']) ? $row2['spam_text'] : $spam_text);
                    if ($row2['spam']) {
                        $this->setSpam(TRUE);
                        $this->spam_count++;
                        $spam_text = $row2['spam_text'];
                        $spam_source = $row2['source'];
                        $spam_child_id = $row2['superfecta_mf_child_id'];
                    }
                    // If we hit a cnam result, we are done.  break out of the loop.
                    $spam = (($row2['spam_text']) ? $row2['spam'] : $spam);
                    if ($row2['cnam'] && (!$first_caller_id)) {
                        $first_caller_id = $row2['cnam'];
                        $winning_child_id = $row2['superfecta_mf_child_id'];
                        $winning_source = $row2['source'];
                        $this->set_CacheFound($row2['cached']);
                        break;
                    }
                    $last_priority++;
                }
            }
            // We have a cnam, break out of this loop too
            if ($first_caller_id) {
                break;
            }
            $loop_limit--;
            if ($loop_limit && ($loop_cur_time - $loop_start_time) <= $loop_time_limit) {
                usleep(50000); // sleep for 1/20 second. Short delay, but should help from taxing the system too much.
            } else {
                $this->DebugPrint("Maximum timeout reached.  Will not wait for any more children.");
                break;
            }
        }

        if ($this->isDebug()) {
            $sql = 'SELECT superfecta_mf_child_id, source FROM superfecta_mf_child WHERE superfecta_mf_id = ' . $superfecta_mf_id;
            $list = & $this->db->getAll($sql, array(), DB_FETCHMODE_ASSOC);
            usleep(50000);
            foreach ($list as $data) {
                $this->DebugPrint("<b>Debug From Child-" . $data['superfecta_mf_child_id'] . "-" . $data['source'] . ":</b><br/>");
                $this->DebugEcho("<pre>");
                $this->DebugEcho(file_get_contents("log-" . $data['superfecta_mf_child_id'] . ".log"));
                $this->DebugEcho("</pre>");
                unlink("log-" . $data['superfecta_mf_child_id'] . ".log");
            }
        }

        $multifecta_parent_end_time = $this->mctime_float();
        $query = "UPDATE superfecta_mf
			SET timestamp_end = " . $this->db->quoteSmart($multifecta_parent_end_time);
        if ($winning_child_id) {
            $query .= ",
				winning_child_id = " . $this->db->quoteSmart($winning_child_id);
        }
        if ($spam_child_id) {
            $query .= ",
				spam_child_id = " . $this->db->quoteSmart($spam_child_id);
        }
        $query .= "
			  	WHERE superfecta_mf_id = " . $this->db->quoteSmart($superfecta_mf_id) . "
				";
        $res2 = $this->db->query($query);

        if ($loop_cur_time) {
            $this->DebugPrint("Parent waited " . number_format(($loop_cur_time - $loop_start_time), 4) . " seconds for children's results.");
        }
        if ($first_caller_id) {
            $this->DebugPrint("Winning CNAM child source " . $winning_child_id . ":" . $winning_source . ", with: " . $first_caller_id);
        }
        if ($spam_text) {
            $this->DebugPrint("Winning SPAM child source " . $spam_child_id . ":" . $spam_source);
        }
        if ((!$first_caller_id) && (!$spam_text)) {
            $this->DebugPrint("No winning SPAM or CNAM children found in allotted time.");
            return(FALSE);
        }
        return($first_caller_id);
    }

    function run_child() {
        $cache_found = FALSE;
        $start_time = $this->mctime_float();

        $sql = "SELECT field,value FROM superfectaconfig WHERE source = '" . $this->scheme_name . "_" . $this->source . "'";
        $run_param = $this->db->getAssoc($sql);

        print_r($run_param);

        $source_file = $this->path_location . "/source-" . $this->source . ".module";

        if (file_exists($source_file)) {
            require_once($source_file);
            $source_class = NEW $this->source;
            //Gotta be a better way to do this
            $source_class->setDebug($this->getDebug());
            $source_class->set_AmpConf($this->amp_conf);
            $source_class->set_DB($this->db);
            $source_class->set_AsteriskManager($this->astman);
            $source_class->set_TrunkInfo($this->trunk_info);
            
            if (method_exists($source_class, 'get_caller_id')) {
                $caller_id = $source_class->get_caller_id($this->trunk_info['callerid'], $run_param);
                $this->setSpam($source_class->isSpam());
                $cache_found = $source_class->isCacheFound();
                unset($source_class);
                $caller_id = $this->_utf8_decode($caller_id);

                if (isset($this->multifecta_id)) {
                    $this->caller_id_array[$this->multifecta_id] = $caller_id;
                }
                if (($this->first_caller_id == '') && ($caller_id != '')) {
                    $this->DebugPrint("<br/>Returned Result was: " . $caller_id);
                    $this->DebugPrint("Execution time: " . number_format(($this->mctime_float() - $start_time), 4) . " seconds.");
                }
                $end_time_whole = $this->mctime_float();

                $multifecta_child_cname_time = $this->mctime_float();
                $query = "UPDATE superfecta_mf_child SET timestamp_cnam = " . $this->db->quoteSmart($multifecta_child_cname_time);
                if ($caller_id) {
                    $query .= ", cnam = " . $this->db->quoteSmart(trim($this->caller_id_array[$this->multifecta_id]));
                }
                if ($this->spam_text) {
                    $query .= ", spam_text = " . $this->db->quoteSmart($this->spam_text);
                }
                if ($this->isSpam()) {
                    $query .= ", spam = " . $this->db->quoteSmart($this->spam);
                }
                if ($cache_found) {
                    $query .= ", cached = 1";
                }
                $query .= ", timestamp_end = " . $end_time_whole . " WHERE superfecta_mf_child_id = " . $this->db->quoteSmart($this->multifecta_id) . "
						";
                $res = $this->db->query($query);
                if (DB::IsError($res)) {
                    $this->DebugDie("Unable to update child: " . $res->getMessage());
                }
            } else {
                $this->DebugPrint("Function 'get_caller_id' does not exist!");
            }
        } else {
            $this->DebugPrint("Unable to find source '" . $this->source . "' skipping..");
        }
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
                $source_class->setDebug($this->isDebug());
                if (method_exists($source_class, 'post_processing')) {
                    $source_class->post_processing($this->isCacheFound(), NULL, $caller_id, $run_param, $this->trunk_info['callerid']);
                } else {
                    print "Method 'post_processing' doesn't exist<br\>\n";
                }
            }
        }
    }

    //Run this when web debug is initiated
    function web_debug() {
        return($this->get_results());
    }

}
