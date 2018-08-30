<?php /* $Id */
/** issabelpbx_conf class
 * This class represents the evolution of $amp_conf settings that
 * were originally parsed from the amportal.conf table. It is an
 * integral part of the bootstrap ability of IssabelPBX which now relies
 * on a very short issabelpbx.conf bootstrap file containing the database
 * credentials and some basic path information necessary to get
 * started.
 *
 * The class is used to help many of the advanced and detailed
 * configuration paramters that can tweak all sorts of behavior in
 * IssabelPBX as well as critical settings like directory path locations
 * and credentials for the Asterisk manager.
 *
 * These settings can be viewed and changed by the Advanced Settings
 * page in IssabelPBX Core.
 */
/**
 * configuration types, constant should be used here
 * and in all calling functions.
 */
define("CONF_TYPE_BOOL",   'bool');
define("CONF_TYPE_TEXT",   'text');
define("CONF_TYPE_DIR",    'dir');
define("CONF_TYPE_INT",    'int');
define("CONF_TYPE_SELECT", 'select');
define("CONF_TYPE_FSELECT",'fselect');

// For translation (need to be in english in the DB, translated when pulled out
// TODO: is there a better place to put these like in install script?
//
if (false) {
  _('No Description Provided');
  _('Undefined Category');
}

class issabelpbx_conf {
  /** $legacy_conf_defaults are used by parse_amprotal_conf to
   * assure that a system being migrated has all the expected $amp_conf
   * settings defined as the code expects them to be there.
   */
  var $legacy_conf_defaults = array(
  'AMPDBENGINE'    => array(CONF_TYPE_SELECT, 'mysql'),
  'AMPDBNAME'      => array(CONF_TYPE_TEXT, 'asterisk'),
  'AMPENGINE'      => array(CONF_TYPE_SELECT, 'asterisk'),
  'ASTMANAGERPORT' => array(CONF_TYPE_INT, '5038'),
  'ASTMANAGERHOST' => array(CONF_TYPE_TEXT, 'localhost'),
  'AMPDBHOST'      => array(CONF_TYPE_TEXT, 'localhost'),
  'AMPDBUSER'      => array(CONF_TYPE_TEXT, 'asteriskuser'),
  'AMPDBPASS'      => array(CONF_TYPE_TEXT, 'amp109'),
  'AMPMGRUSER'     => array(CONF_TYPE_TEXT, 'admin'),
  'AMPMGRPASS'     => array(CONF_TYPE_TEXT, 'amp111'),
  'AMPSYSLOGLEVEL' => array(CONF_TYPE_SELECT, 'FILE'),
  'NOOPTRACE'      => array(CONF_TYPE_INT, '1'),
  'ARI_ADMIN_PASSWORD' => array(CONF_TYPE_TEXT, 'ari_password'),
  'CFRINGTIMERDEFAULT' => array(CONF_TYPE_SELECT, '0'),

  'AMPASTERISKWEBUSER'	=> array(CONF_TYPE_TEXT, 'asterisk'),
  'AMPASTERISKWEBGROUP'	=> array(CONF_TYPE_TEXT, 'asterisk'),
  'AMPASTERISKUSER'	=> array(CONF_TYPE_TEXT, 'asterisk'),
  'AMPASTERISKGROUP'	=> array(CONF_TYPE_TEXT, 'asterisk'),
  'AMPDEVUSER'	   => array(CONF_TYPE_TEXT, 'apache'),
  'AMPDEVGROUP'    => array(CONF_TYPE_TEXT, 'apache'),

  'ASTETCDIR'      => array(CONF_TYPE_DIR, '/etc/asterisk'),
  'ASTMODDIR'      => array(CONF_TYPE_DIR, '/usr/lib/asterisk/modules'),
  'ASTVARLIBDIR'   => array(CONF_TYPE_DIR, '/var/lib/asterisk'),
  'ASTAGIDIR'      => array(CONF_TYPE_DIR, '/var/lib/asterisk/agi-bin'),
  'ASTSPOOLDIR'    => array(CONF_TYPE_DIR, '/var/spool/asterisk/'),
  'ASTRUNDIR'      => array(CONF_TYPE_DIR, '/var/run/asterisk'),
  'ASTLOGDIR'      => array(CONF_TYPE_DIR, '/var/log/asterisk'),
  'AMPBIN'         => array(CONF_TYPE_DIR, '/var/lib/asterisk/bin'),
  'AMPSBIN'        => array(CONF_TYPE_DIR, '/usr/sbin'),
  'AMPWEBROOT'     => array(CONF_TYPE_DIR, '/var/www/html'),
  'MOHDIR'         => array(CONF_TYPE_DIR, 'mohmp3'),
  'IPBXDBUGFILE'	 => array(CONF_TYPE_DIR, '/tmp/issabelpbx_debug.log'),

  'ENABLECW'       => array(CONF_TYPE_BOOL, true),
  'CWINUSEBUSY'    => array(CONF_TYPE_BOOL, true),
  'AMPBADNUMBER'   => array(CONF_TYPE_BOOL, true),
  'DEVEL'          => array(CONF_TYPE_BOOL, false),
  'DEVELRELOAD'    => array(CONF_TYPE_BOOL, false),
  'CUSTOMASERROR'  => array(CONF_TYPE_BOOL, true),
  'DYNAMICHINTS'   => array(CONF_TYPE_BOOL, false),
  'BADDESTABORT'   => array(CONF_TYPE_BOOL, false),
  'SERVERINTITLE'  => array(CONF_TYPE_BOOL, false),
  'USEDEVSTATE'    => array(CONF_TYPE_BOOL, false),
  'MODULEADMINWGET'=> array(CONF_TYPE_BOOL, false),
  'AMPDISABLELOG'  => array(CONF_TYPE_BOOL, true),
  'CHECKREFERER'   => array(CONF_TYPE_BOOL, true),
  'RELOADCONFIRM'  => array(CONF_TYPE_BOOL, true),
  'DIVERSIONHEADER' => array(CONF_TYPE_BOOL, false),
  'ZAP2DAHDICOMPAT' => array(CONF_TYPE_BOOL, false),
  'XTNCONFLICTABORT' => array(CONF_TYPE_BOOL, false),
  'AMPENABLEDEVELDEBUG' => array(CONF_TYPE_BOOL, false),
  'DISABLECUSTOMCONTEXTS' => array(CONF_TYPE_BOOL, false),

  // Time Conditions (2.9 New)
  'TCINTERVAL'     => array(CONF_TYPE_INT, '60'),
  'TCMAINT'        => array(CONF_TYPE_BOOL, true),

  // Queues
  'USEQUEUESTATE'  => array(CONF_TYPE_BOOL, false),

  // Day Night (2.9 New)
  'DAYNIGHTTCHOOK' => array(CONF_TYPE_BOOL, false),

  // Music
  'AMPMPG123'      => array(CONF_TYPE_BOOL, true),
  );

  /** $db_conf_store is the resident internal store for settings
   * and is backed by the issabelpbx_settings SQL table.
   *
   * hashed on keyword and fields include:
   *
   *                [keyword]     Setting
   *                [value]       Value
   *                [defaultval]  Default value
   *                [type]        Type of setting, used defines above
   *                [name]        Friendly Short Description
   *                [description] Long description for tooltip
   *                [category]    Category description of setting
   *                [module]      Module setting belongs to, optional
   *                [level]       Level of setting
   *                [options]     select options, or validation options
   *                [emptyok]     boolean if value can be blank
   *                [readonly]    boolean for readonly
   *                [hidden]      boolean for hidden fields
   *                [sortorder]   'primary' sort key for presentation
   */
  var $db_conf_store;

  /** simple key => value store for settings. Also augmented with boostrap settings
   * if provided which are not included in db_conf_store.
   */
  var $conf = array();

  /** legacy $asterisk_conf that we need to obsolete
   */
  var $asterisk_conf = array();

  /** This will be set with any update/define to provide feedback that can be optionally
   * used inside or outside of the class. The structure should be:
   * $last_update_status[$keyword]['validated']   true/false
   * $last_update_status[$keyword]['saved']       true/false
   * $last_update_status[$keyword]['orig_value']  value submitted
   * $last_update_status[$keyword]['saved_value'] value submitted
   * $last_update_status[$keyword]['msg']         error message
   */
  var $last_update_status;

  /** Internal reference pointer to the internal $last_update_status[$keyword]
   * e.g. $this->_last_update_status =& $last_update_status[$keyword];
   */
  var $_last_update_status;

  // TODO: move to static var in method?
  /** internal tracker used by parse_amportal_conf
   */
  var $parsed_from_db = false;

  /** status of the amportal.conf file passed in and if it can be written to
   */
  var $amportal_canwrite;


  /** All access to this class should be done using the static method create
   * so a single copy is created and used throughout.
   *
   * @return obj  returns an object to a new or the current instance of
   *              a issabelpbx_conf class.
   */
  function &create() {
    static $obj;
    global $db;
    if (!isset($obj) || !is_object($obj)) {
      $obj = new issabelpbx_conf();
    }
    return $obj;
  }

  /** php4 constructor
   */
  function issabelpbx_conf() {
    $this->__construct();
  }

  /** issabelpbx_conf constructor
   * The class when initialized is filled populated from the SQL store
   * along with some level of validation in case corrupted data has
   * been put into the store form outside sources. It does not write back
   * upon detecting corrupted data though.
   *
   * Along with populating the db_conf_store hash, it also populates the
   * key => value conf hash by reference so that changes to db_conf_store
   * will be reflected. (Since $amp_conf should be assigned as a reference
   * to the conf hash).
   */
  function __construct() {
    global $db;

    if(!is_array($db->tableInfo('issabelpbx_settings'))) {
       // table does not exist, create it
       $sql = "CREATE TABLE `issabelpbx_settings` (
          `keyword` varchar(50) NOT NULL DEFAULT '',
          `value` varchar(255) DEFAULT NULL,
          `name` varchar(80) DEFAULT NULL,
          `level` tinyint(1) DEFAULT '0',
          `description` text,
          `type` varchar(25) DEFAULT NULL,
          `options` text,
          `defaultval` varchar(255) DEFAULT NULL,
          `readonly` tinyint(1) DEFAULT '0',
          `hidden` tinyint(1) DEFAULT '0',
          `category` varchar(50) DEFAULT NULL,
          `module` varchar(25) DEFAULT NULL,
          `emptyok` tinyint(1) DEFAULT '1',
          `sortorder` int(11) DEFAULT '0',
          PRIMARY KEY (`keyword`)
        ) ";
        $res = $db->query($sql);

        // populate the settings table with sensible defaults
        if(is_readable('/var/www/html/admin/modules/framework/SQL/upgradefromfpbx.db')) {
            $lines = file('/var/www/html/admin/modules/framework/SQL/upgradefromfpbx.db');
            foreach($lines as $line) {
                if(preg_match("/^INSERT/",$line)) {
                    $res = $db->query($line);
                }
            }
        }
    }

    $sql = 'SELECT * FROM issabelpbx_settings ORDER BY category, sortorder, name';
    $db_raw = $db->getAll($sql, array(), DB_FETCHMODE_ASSOC);
    if(DB::IsError($db_raw)) {
      die_issabelpbx(_('fatal error reading issabelpbx_settings'));
    }
    unset($this->last_update_status);
    foreach($db_raw as $setting) {
      $this->last_update_status[$setting['keyword']]['validated'] = false;
      $this->last_update_status[$setting['keyword']]['saved'] = false;
      $this->last_update_status[$setting['keyword']]['orig_value'] = $setting['value'];
      $this->last_update_status[$setting['keyword']]['saved_value'] = $setting['value'];
      $this->last_update_status[$setting['keyword']]['msg'] = '';
      $this->_last_update_status =& $this->last_update_status[$setting['keyword']];

      $this->db_conf_store[$setting['keyword']] = $setting;
      $this->db_conf_store[$setting['keyword']]['modified'] = false;
      // setup the conf array also
      // note the reference assignment, if it's actually the authoritative source
      $this->conf[$setting['keyword']] =& $this->db_conf_store[$setting['keyword']]['value'];

      // The assumption is that the database settings were validated on input. We are not going to throw errors when
      // reading them back but the last_update_status array is available for debugging purposes to review.
      //
      if (!$setting['emptyok'] && $setting['value'] == '') {
        $this->db_conf_store[$setting['keyword']]['value'] = $this->_prepare_conf_value($setting['defaultval'], $setting['type'], $setting['emptyok'], $setting['options']);
      } else {
        $this->db_conf_store[$setting['keyword']]['value'] = $this->_prepare_conf_value($setting['value'], $setting['type'], $setting['emptyok'], $setting['options']);
      }
    }
    unset($db_raw);
  }

  /** This method returns a copy of the conf hash that is equivalent to the $amp_conf configuration
   * hash that is used throughout IssabelPBX for many settings. The method will determine if it needs to
   * parse the passed $filename to get authoritative information about the settings or if the
   * information stored in the database is authoritative. The database is deemed to be the authority
   * if the passed file is writeable, unless the $file_is_authority is set to true in which case it
   * will force the file to be used as the correct information. In the event that the $filename is the
   * authority, its values will be written back to the database.
   *
   * When it does read from the supplied amportal.conf file, it will also make sure that all the values
   * from the $legacy_conf_defaults are present and if not, it will set those values. It will also scan
   * the asterisk.conf file and set the corresponding values as well. This is done because there is an
   * expectation throughout IssabelPBX that these legacy values are set and available since they used to
   * be present through a similar default array.
   *
   * There is also an optional $bootstrap_conf array that can be passed in. This is used by IssabelPBX to
   * supply the values found in the $amp_conf bootstrap array that is configured in /etc/issabelpbx.conf
   * typically. These values are added to the $conf hash which is what is returned and otherwise used
   * for $amp_conf. This is done so that IssabelPBX has access to these database credentials which it needs
   * to configure and pass on in places like retrieve_conf when generating dialplan where some of these
   * credentials are provided so that dialplan code such as AGI scripts can have access to these.
   *
   * @param string  The filename, amportal.conf, to potentially parse.
   * @param array   The bootstrapped array of credentials, opptional
   * @param bool    Can force the use of the file as authority if set true
   * @return array  returns the hash which is used for $amp_conf.
   */
  function parse_amportal_conf($filename, $bootstrap_conf = array(), $file_is_authority=false) {
	  global $db;

    // if we have loaded for the db, then just return what we already have
    if ($this->parsed_from_db && !$file_is_authority) {
	    return $this->conf;
    }

	  /* defaults
	  * This defines defaults and formatting to assure consistency across the system so that
	  * components don't have to keep being 'gun shy' about these variables.
	  *
	  * we will read these settings out of the db, but only when $filename is writeable
	  * otherwise, we read the $filename
	  */
    // If conf file is not writable, then we use it as the master so parse it.
	  if (!is_writable($filename) || $file_is_authority) {
		  $file = file($filename);
		  if (is_array($file)) {
        $write_back = false;
			  foreach ($file as $line) {
				  if (preg_match("/^\s*([a-zA-Z0-9_]+)=([a-zA-Z0-9 .&-@=_!<>\"\']+)\s*$/",$line,$matches)) {
            // overrite anything that was initialized from the db with the conf file authoritative source
            // if different from the db value then let's write it back to the db
            // TODO: massage any data we read from the conf file with _preapre_conf_value since it is
            //       written back to the DB here if different from the DB.
            //
            if (!isset($this->conf[$matches[1]]) || $this->conf[$matches[1]] != $matches[2]) {
              if (isset($this->db_conf_store[$matches[1]])) {
                $this->db_conf_store[$matches[1]]['value'] = $matches[2];
                $this->db_conf_store[$matches[1]]['modified'] = true;
					      $this->conf[$matches[1]] =& $this->db_conf_store[$matches[1]]['value'];
                $write_back = true;
              } else {
					      $this->conf[$matches[1]] = $matches[2];
              }
            }
				  }
 			  }
        $this->amportal_canwrite = false;
        if ($write_back) {
          $this->commit_conf_settings();
        }
		  } else {
			  die_issabelpbx(sprintf(_("Missing or unreadable config file [%s]...cannot continue"), $filename));
		  }
      // Need to handle transitionary period where modules are adding new settings. So once we parsed the file
      // we still go read from the database and add anything that isn't there from the conf file.
      //
	  } else {
      $this->amportal_canwrite = true;
      $this->parsed_from_db = true;
    }
    // If boostrap_conf settings are passed in, add them to the class
    //
    // TODO: Make a method that can do this as well (and maybe use here) so parse_amportal_conf isn't the
    //       only way to do this.
    foreach ($bootstrap_conf as $key => $value) {
      if (!isset($this->conf[$key])) {
        $this->conf[$key] = $value;
      }
    }

    // We set defaults above from the database so anything that needed a default
    // and had one available was set there. The only conflict here is if we did
    // not specify emptyok and yet the legacy ones do have a default.
    //
    // it looks like the only ones that don't accept an empty but set variable are directories
    //
	  // set defaults
    // TODO: change this to use _prepare_conf_value ?
    // TODO: beware that these are all free-form entered (e.g. booleans) need pre-conditioning if from conf file
	  foreach ($this->legacy_conf_defaults as $key=>$arr) {

		  switch ($arr[0]) {
			  // for type dir, make sure there is no trailing '/' to keep consistent everwhere
			  //
        case CONF_TYPE_DIR:
				  if (!isset($this->conf[$key]) || trim($this->conf[$key]) == '') {
					  $this->conf[$key] = $arr[1];
				  } else {
					  $this->conf[$key] = rtrim($this->conf[$key],'/');
				  }
				  break;
			  // booleans:
			  // "yes", "true", "on", true, 1 (case-insensitive) will be treated as true, everything else is false
			  //
        case CONF_TYPE_BOOL:
				  if (!isset($this->conf[$key])) {
					  $this->conf[$key] = $arr[1];
				  } else {
					  $this->conf[$key] = ($this->conf[$key] === true || strtolower($this->conf[$key]) == 'true' || $this->conf[$key] === 1 || $this->conf[$key] == '1'
					                                      || strtolower($this->conf[$key]) == 'yes' ||  strtolower($this->conf[$key]) == 'on');
				  }
				  break;
			  default:
				  if (!isset($this->conf[$key])) {
					  $this->conf[$key] = $arr[1];
				  } else {
					  $this->conf[$key] = trim($this->conf[$key]);
				  }
		  }
	  }

	  $convert = array(
		  'astetcdir'    => 'ASTETCDIR',
		  'astmoddir'    => 'ASTMODDIR',
		  'astvarlibdir' => 'ASTVARLIBDIR',
		  'astagidir'    => 'ASTAGIDIR',
		  'astspooldir'  => 'ASTSPOOLDIR',
		  'astrundir'    => 'ASTRUNDIR',
		  'astlogdir'    => 'ASTLOGDIR'
	  );

	  $file = file($this->conf['ASTETCDIR'].'/asterisk.conf');
	  foreach ($file as $line) {
		  if (preg_match("/^\s*([a-zA-Z0-9]+)\s* => \s*(.*)\s*([;#].*)?/",$line,$matches)) {
			  $this->asterisk_conf[ $matches[1] ] = rtrim($matches[2],"/ \t");
		  }
	  }

	  // Now that we parsed asterisk.conf, we need to make sure $amp_conf is consistent
	  // so just set it to what we found, since this is what asterisk will use anyhow.
	  //
	  foreach ($convert as $ast_conf_key => $amp_conf_key) {
		  if (isset($this->conf[$ast_conf_key])) {
			  $this->conf[$amp_conf_key] = $this->asterisk_conf[$ast_conf_key];
		  }
	  }

	  return $this->conf;
  }

  /** Generate an amportal.conf file from the db_conf_store settings loaded.
   *
   * @param bool    true if a verbose file should be written that includes some documentation.
   * @return string returns the amportal.conf text that can be written out to a file.
   */
  function amportal_generate($verbose=true) {
    // purposely lcoalized the '---------' lines, if someone translates this, theymay want to keep it 'neat'
    // Only localize text, not special characters, and dont add the end ";" as localized text can be of any length
    $conf_string  = "#;--------------------------------------------------------------------------------\n";
    $conf_string .= "#; ";
    $conf_string .= _("Do NOT edit this file as it is auto-generated by IssabelPBX. All modifications to");
    $conf_string .= "\n#; ";
    $conf_string .= _("this file must be done via the Web GUI. There are alternative files to make");
    $conf_string .= "\n#; ";
    $conf_string .= _("custom modifications, details at:");
    $conf_string .= " http://issabel.org/configuration_files\n";
    $conf_string .= "#;--------------------------------------------------------------------------------\n\n\n";
    $conf_string .= "#;--------------------------------------------------------------------------------\n#; ";
    $conf_string .= _("All settings can be set from the Advanced Settings page accessible in IssabelPBX");
    $conf_string .=  "\n#;--------------------------------------------------------------------------------\n\n\n\n";
    $comments = '';

    // Note, No localization of the name field, this is a conf file! DON'T MESS WITH THIS!
    $category = '';
    foreach ($this->conf as $keyword => $value) {
      if ($this->conf_setting_exists($keyword)) {
        if ($this->db_conf_store[$keyword]['hidden']) {
          continue;
        }
        if ($this->db_conf_store[$keyword]['type'] == CONF_TYPE_BOOL) {
          $default_val = $this->db_conf_store[$keyword]['defaultval'] ? 'TRUE' : 'FALSE';
          $this_val    = $value ? 'TRUE' : 'FALSE';
        } else {
          $default_val = $this->db_conf_store[$keyword]['defaultval'];
          $this_val    = $value;
        }
      } else {
        $this_val = $value;
      }
      if ($verbose) {
        if ($this->conf_setting_exists($keyword)) {
          $comments = '';
          if ($this->db_conf_store[$keyword]['category'] != $category) {
            $category = $this->db_conf_store[$keyword]['category'];
            $comments = "#\n# --- CATEGORY: $category ---\n#\n\n";
          }
          $comments .= "# " . $this->db_conf_store[$keyword]['name'] . "\n";
          $comments .= "# Default Value: $default_val\n";
        } else {
          $comments = "#\n";
          if ($category != 'Bootstrapped or Legacy Settings') {
            $category = 'Bootstrapped or Legacy Settings';
            $comments = "#\n# --- CATEGORY: $category ---\n#\n\n#\n";
          }
        }
      }
			$this_val = str_replace(' ','\ ',$this_val);
      $conf_string .= $comments . "$keyword=$this_val\n\n";
    }
    return $conf_string;
  }


  /** Returns the detrmined status of if the amportal.conf file used to
   * start up this session is writable. You must have called the
   * parse_amportal_conf() for this setting to have meaning.
   *
   * @return bool   whether or not amortal.conf is writable if parse_amportal_conf()
   *                was previously called.
   */
  function amportal_canwrite() {
    return $this->amportal_canwrite;
  }

  /** Reset all the db_conf_store settings to their defaults and
   * optionally commit them back to the database.
   *
   * @param bool    Resets all the settings to their default values.
   */
  function reset_all_conf_settings($commit=false) {
    $update_arr = array();
    foreach ($this->db_conf_store as $keyword => $atribs) {
      if (!$atribs['hidden']) {
        $update_arr[$keyword] = $atribs['defaultval'];
      }
      return $this->set_conf_values($update_arr,$commit,true);
    }
  }

  /** Returns the the hash that is used in various parts of IssabelPBX as $asterisk_conf. This hash and its
   * use should be deprecated and removed from IssabelPBX as all the information is available in the main
   * $conf array as it has been in $amp_conf.
   *
   * @return array  returns the hash which is used for $asterisk_conf.
   */
  function get_asterisk_conf() {
	  return $this->asterisk_conf;
  }

  /** Returns a hash of the full $db_conf_store, getter for that object.
   *
   * @return array   a copy of the db_conf_store
   */
  function get_conf_settings() {
		$db_conf_store = $this->db_conf_store;
		foreach ($db_conf_store as $k => $s) {
			if ($s['type'] == CONF_TYPE_FSELECT) {
				$db_conf_store[$k]['options'] = unserialize($s['options']);
			}
		}
    return $db_conf_store;
  }

  /** Determines if a setting exists in the configuration database.
   *
   * @return bool   True if the setting exists.
   */
  function conf_setting_exists($keyword) {
    return isset($this->db_conf_store[$keyword]);
  }

  /** Get's the current value of a configuration setting from the database store.
   *
   * @param string  The setting to fetch.
   * @param boolean Optional forces the actual database variable to be fetched
   * @return mixed  returns the value of the setting, or boolean false if the
   *                setting does not exist. Since configuration booleans are
   *                returned as '0' and '1', they can be differentiated by a
   *                true boolean false (use === operator) if a setting does
   *                not exist.
   */
  function get_conf_setting($keyword, $passthru=false) {
		if ($passthru) {
			// This is a special case situation, do I need to confirm if the setting
			// actually exists so I can return a boolean false if not?
			//
			global $db;
			$sql = "SELECT `value` FROM issabelpbx_settings WHERE `keyword` = '$keyword'";
			$value = $db->getOne($sql);
			if (isset($this->db_conf_store[$keyword])) {
				$this->db_conf_store[$keyword]['value'] = $value;
			}
			return $value;
		} elseif (isset($this->db_conf_store[$keyword])) {
            return $this->db_conf_store[$keyword]['value'];
        } else {
            return false;
        }
  }

  /** Get's the default value of a configuration setting from the database store.
   *
   * @param string  The setting to fetch.
   * @return mixed  returns the default of the setting, or boolean false if the
   *                setting does not exist. Since configuration booleans are
   *                returned as '0' and '1', they can be differentiated by a
   *                true boolean false (use === operator) if a setting does
   *                not exist.
   */
  function get_conf_default_setting($keyword) {
    if (isset($this->db_conf_store[$keyword])) {
      return $this->db_conf_store[$keyword]['defaultval'];
    } else {
      return false;
    }
  }

  /** Reset all conf settings specified int the passed in array to their defaults.
   *
   * @param array   An array of the settings that should be reset.
   * @param array   Boolean set to true if the db_conf_store should be commited to
   *                the database after reseting it.
   * @return int    returns the number of settings that differed from the current
   *                values.
   */
  function reset_conf_settings($settings, $commit=false) {
    $update_arr = array();
    foreach ($settings as $keyword) {
      $update_arr[$keyword] = $this->db_conf_store[$keyword]['defaultval'];
    }
    return $this->set_conf_values($update_arr,$commit,true);
  }

  /** Set's configuration store values with an option to commit and an option to
   * override readonly settings.
   *
   * @param array   A hash of key/value settings to update.
   * @param bool    Boolean set to true if the db_conf_store should be commited to
   *                the database after reseting it.
   * @param bool    Boolean set to true if readonly settings should be allowed
   *                to be changed.
   * @return int    returns the number of settings that differed from the current
   *                values and are marked dirty unless written out.
   */
  function set_conf_values($update_arr, $commit=false, $override_readonly=false) {
		global $amp_conf;
    $cnt = 0;
    if (!is_array($update_arr)) {
      die_issabelpbx(_("called set_conf_values with a non-array"));
    }
    unset($this->last_update_status);
    foreach($update_arr as $keyword => $value) {
      if (!isset($this->db_conf_store[$keyword])) {
        die_issabelpbx(sprintf(_("trying to set keyword [%s] to [%s] on uninitialized setting"),$keyword, $value));
      }
      $this->last_update_status[$keyword]['validated'] = false;
      $this->last_update_status[$keyword]['saved'] = false;
      $this->last_update_status[$keyword]['orig_value'] = $value;
      $this->last_update_status[$keyword]['saved_value'] = $value;
      $this->last_update_status[$keyword]['msg'] = '';
      $this->_last_update_status =& $this->last_update_status[$keyword];

      $prep_value = $this->_prepare_conf_value($value, $this->db_conf_store[$keyword]['type'], $this->db_conf_store[$keyword]['emptyok'], $this->db_conf_store[$keyword]['options']);

      // If we reported saved then even if we didn't validate, we still were able to rectify
      // it into something and therefore will use it. For example, if we set an integer out of
      // range then we will still save the value. If the calling function wants to be strict
      // they can not supply the commit flag and check the validation status and not save/commit
      // the value based on their own decision criteria.
      //
      if ($this->_last_update_status['saved']
        && $prep_value != $this->db_conf_store[$keyword]['value']
        && ($prep_value !== '' || $this->db_conf_store[$keyword]['emptyok'])
        && ($override_readonly || !$this->db_conf_store[$keyword]['readonly'])) {

        $this->db_conf_store[$keyword]['value'] = $prep_value;
        $this->db_conf_store[$keyword]['modified'] = true;
        $cnt++;
      }

			// Make sure it get's update in amp_conf
			//
			$amp_conf[$keyword] = $prep_value;
			// Process some specific keywords that require further actions
			//
			$this->_setting_change_special($keyword, $prep_value);

    }
    if ($commit) {
      $this->commit_conf_settings();
    }
    return $cnt;
  }

  /** Get's the results of the last update and can be used to get errors,
   * values if settings were altered from validation, etc.
   *
   * @return array  returns the last_update_status hash
   */
  function get_last_update_status() {
    return $this->last_update_status;
  }

  // TODO should I remove (or ignore) need for value. Or should I provide the option
  //      of setting the current and default values different as there are some migration
  //      scenarios that would support this?
  /** used to insert or update an existing setting such as in an install
   * script. $vars will include some required fields and we are strict
   * with a die_freebpx() if they are missing.
   *
   * the value parameter will not be altered in memory or in the database if
   * the setting has already been defined, but most of the other settings can
   * be changed with the exception of the type setting which must be the same
   * once created, or you must remove the setting entirely if the type is to
   * be changed.
   *
   * @param string  the setting keyword
   * @param array   a parameter array with all the settings
   *                [value]       required, value of the setting
   *                [name]        required, Friendly Short Description
   *                [level]       optional, default 0, level of setting
   *                [description] required, long description for tooltip
   *                [type]        required, type of setting
   *                [options]     conditional, required for selects, optional
   *                              for others. For INT a 2 place array
   *                              indicates the allowed range, for others
   *                              it is a REGEX validation, for BOOL, nothing
   *                [emptyok]     optional, default true, if setting can be blank
   *                [defaultval]  required and same as value
   *                [readonly]    optional, default false, if readonly
   *                [hidden]      optional, default false, if hidden
   *                [category]    required, category of the setting
   *                [module]      optional, module name that owns the setting
   *                              and if the setting should only exist when
   *                              the module is installed. If set, uninstalling
   *                              the module will automatically remove this.
   *                [sortorder]   'primary' sort order key for presentation
   * @param bool    set to true if a commit back to the database should be done
   */
  function define_conf_setting($keyword,$vars,$commit=false) {
    global $amp_conf;

    unset($this->last_update_status);
    $this->last_update_status[$keyword]['validated'] = false;
    $this->last_update_status[$keyword]['saved'] = false;
    $this->last_update_status[$keyword]['orig_value'] = $vars['value'];
    $this->last_update_status[$keyword]['saved_value'] = $vars['value'];
    $this->last_update_status[$keyword]['msg'] = '';

    $this->_last_update_status =& $this->last_update_status[$keyword];

    $attributes = array(
	    'keyword' => '',
	    'value' => '',
	    'name' => '',
	    'level' => 0,
	    'description' => 'No Description Provided', // Don't gettext this
	    'type' => '',
	    'options' => '',
	    'defaultval' => '',
	    'readonly' => 0,
	    'hidden' => 0,
	    'category' => 'Undefined Category', // Don't gettext this
	    'module' => '',
	    'emptyok' => 1,
	    'sortorder' => 0,
	    'modified' => false, // set to false to compare against existing array
      );
    // Got to have a type and value, if no type, _prepared_conf_value will throw an error
    $new_setting = !isset($this->db_conf_store[$keyword]);
    // If not a new setting, default appropriate values that have not been set for us
    //
    if (!$new_setting) {
      if (!isset($vars['defaultval'])) {
        $vars['defaultval'] = $this->db_conf_store[$keyword]['defaultval'];
      }
      if (!isset($vars['name'])) {
        $vars['name'] = $this->db_conf_store[$keyword]['name'];
      }
      if (!isset($vars['level'])) {
        $vars['level'] = $this->db_conf_store[$keyword]['level'];
      }
      if (!isset($vars['type'])) {
        $vars['type'] = $this->db_conf_store[$keyword]['type'];
      }
      if (!isset($vars['description'])) {
        $vars['description'] = $this->db_conf_store[$keyword]['description'];
      }
      if (!isset($vars['options'])) {
        $vars['options'] = $this->db_conf_store[$keyword]['options'];
      }
      if (!isset($vars['readonly'])) {
        $vars['readonly'] = $this->db_conf_store[$keyword]['readonly'];
      }
      if (!isset($vars['hidden'])) {
        $vars['hidden'] = $this->db_conf_store[$keyword]['hidden'];
      }
      if (!isset($vars['category'])) {
        $vars['category'] = $this->db_conf_store[$keyword]['category'];
      }
      if (!isset($vars['sortorder'])) {
        $vars['sortorder'] = $this->db_conf_store[$keyword]['sortorder'];
      }
    }
    if (!$new_setting && $vars['type'] != $this->db_conf_store[$keyword]['type']) {
      die_issabelpbx(sprintf(_("you can't convert an existing type, keyword [%s]"),$keyword));
    }
    if (!isset($vars['value']) || !isset($vars['defaultval'])) {
      die_issabelpbx(sprintf(_("missing value and/or defaultval required for [%s]"),$keyword));
    } else {
      $attributes['keyword'] = $keyword;
      $attributes['type'] = $vars['type'];
    }
    switch ($vars['type']) {
    case CONF_TYPE_SELECT:
      if (!isset($vars['options']) || $vars['options'] == '') {
        die_issabelpbx(sprintf(_("missing options for keyword [%] required if type is select"),$keyword));
      } else {
        $opt_array =  is_array($vars['options']) ? $vars['options'] : explode(',',$vars['options']);
        foreach($opt_array as $av) {
          $trim_options[] = trim($av);
        }
        $attributes['options'] = implode(',',$trim_options);
        unset($opt_array);
        unset($trim_options);
      }
    break;
    case CONF_TYPE_FSELECT:
      if (!isset($vars['options']) || !is_array($vars['options'])) {
        die_issabelpbx(sprintf(_("missing options array for keyword [%] required if type is select"),$keyword));
      } else {
        $attributes['options'] = serialize($vars['options']);
			}
    break;
    case CONF_TYPE_INT:
      if (isset($vars['options']) && $vars['options'] != '') {
        $validate_options = !is_array($vars['options']) ? explode(',',$vars['options']) : $vars['options'];
        if (count($validate_options) != 2 || !is_numeric($validate_options[0]) || !is_numeric($validate_options[1])) {
          die_issabelpbx(sprintf(_("invalid validation options provided for keyword %s: %s"),$keyword,implode(',',$validate_options)));
        } else {
          $attributes['options'] = (int) $validate_options[0] . ',' . (int) $validate_options[1];
        }
      }
    break;
    case CONF_TYPE_TEXT:
    case CONF_TYPE_DIR:
      if (isset($vars['options'])) {
        $attributes['options'] = $vars['options'];
      }
    break;
    }

    if (isset($vars['level'])) {
      $attributes['level'] = (int) $vars['level'] > 0 ? ((int) $vars['level'] < 10 ? (int) $vars['level'] : 10) : 0;
    }
    if (isset($vars['category']) && $vars['category']) {
      $attributes['category'] = $vars['category'];
    }
    $optional = array('readonly', 'hidden', 'emptyok');
    foreach ($optional as $atrib) {
      if (isset($vars[$atrib])) {
        $attributes[$atrib] = $vars[$atrib] ? '1' : '0';
      }
    }
    $optional = array('name', 'description', 'module', 'sortorder');
    foreach ($optional as $atrib) {
      if (isset($vars[$atrib])) {
        $attributes[$atrib] = $vars[$atrib];
      }
    }
    if ($attributes['name'] == '') {
      $attributes['name'] = $attributes['keyword'];
    }

    // validate even if already set, catches coding errors early even though we don't use it
    $value = $this->_prepare_conf_value($vars['value'], $vars['type'] ,$attributes['emptyok'], $attributes['options']);
    $attributes['value'] = $new_setting ? $value : $this->db_conf_store[$keyword];
    $attributes['defaultval'] = $this->_prepare_conf_value($vars['defaultval'], $vars['type'] ,$attributes['emptyok'], $attributes['options']);

    // Let's be really stict here, if anything violated validation, we fail!
    // This method is only called to define new settings, this catches programming errors early on.
    //
    if (!$this->_last_update_status['validated']) {
      die_issabelpbx(
        sprintf(_("method define_conf_setting() failed to pass validation for keyword [%s] setting value [%s], error msg if supplied [%s]"),
        $keyword, $vars['value'], $this->_last_update_status['msg'])
      );
    }

    if ($new_setting || $attributes != $this->db_conf_store[$keyword]) {
      if (!$new_setting) {
        unset($attributes['keyword']);
        unset($attributes['value']);
        unset($attributes['type']);
        unset($attributes['modified']);
      }
      foreach ($attributes as $key => $val) {
        $this->db_conf_store[$keyword][$key] = $val;
      }
      if ($new_setting) {
        $this->conf[$keyword] =& $this->db_conf_store[$keyword]['value'];
        $amp_conf[$keyword] =& $this->conf[$keyword];
      }
      $this->db_conf_store[$keyword]['modified'] = true;
    }
    if ($commit) {
      $this->commit_conf_settings();
    }
  }

  /** Removes a set of settings from the db_conf_store, used in functions like
   * uninstall scripts if settings are no longer needed.
   *
   * @param  array  array of settings to be removed
   */
  function remove_conf_settings($settings) {
    global $db,$amp_conf;
    if (!is_array($settings)) {
      $settings = array($settings);
    }
    foreach ($settings as $setting) {
      if (isset($this->db_conf_store[$setting]) ) {
        unset($this->db_conf_store[$setting]);
      }
      if (isset($this->conf[$setting])) {
        unset($this->conf[$setting]);
      }
      //for legacy sakes
      if (isset($amp_conf[$setting])) {
        unset($amp_conf[$setting]);
      }
    }
    $sql = "DELETE FROM issabelpbx_settings WHERE keyword in ('".implode("','",$settings)."')";
    $result = $db->query($sql);
    if(DB::IsError($result)) {
      die_issabelpbx(_('fatal error deleting rows from issabelpbx_settings, sql query: %s').$sql);
    }
  }

  /** Exact same as remove_conf_setting() method, either can be
   * used since they both detect a single or multiple settings.
   *
   * @param  array  array of settings to be removed
   */
  function remove_conf_setting($setting) {
    return $this->remove_conf_settings($setting);
  }

  // TODO: modify to remove in memory settings also
  //
  /** Remove all settings with the indicated module ownership, used
   * during functions like uninstalling modules.
   *
   * @param  array  array of settings to be removed
   */
  function remove_module_settings($module) {
    global $db;
    $sql = "DELETE FROM issabelpbx_settings WHERE module = '$module'";
    $result = $db->query($sql);
    if(DB::IsError($result)) {
      die_issabelpbx(_('fatal error deleting rows from issabelpbx_settings, sql query: %s').$sql);
    }
  }

  /** Commit back to database all in memory settings that have been marked as modified.
   *
   * @return int    The number of modified settings it committed back.
   */
  function commit_conf_settings() {
    global $db;
    $update_array = array();
	if(empty($this->db_conf_store)) {
		return 0;
	}
    foreach ($this->db_conf_store as $keyword => $atrib) {
      if (!$atrib['modified']) {
        continue;
      }
      //TODO: confirm that prepare with ? does an escapeSimple() or equiv, the docs say so
      $update_array[] = array(
        $keyword,
        $atrib['value'],
        $atrib['name'],
        $atrib['level'],
        $atrib['description'],
        $atrib['type'],
        $atrib['options'],
        $atrib['defaultval'],
        $atrib['readonly'],
        $atrib['hidden'],
        $atrib['category'],
        $atrib['module'],
        $atrib['emptyok'],
        $atrib['sortorder'],
      );
      $this->db_conf_store[$keyword]['modified'] = false;
    }
    if (empty($update_array)) {
      return 0;
    }
    $sql = 'REPLACE INTO issabelpbx_settings
      (keyword, value, name, level, description, type, options, defaultval, readonly, hidden, category, module, emptyok, sortorder)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
    $compiled = $db->prepare($sql);
    $result = $db->executeMultiple($compiled,$update_array);
    if(DB::IsError($result)) {
      die_issabelpbx(_('fatal error updating issabelpbx_settings table'));
    }
    return count($update_array);
  }

  /**
   *
   * PRIVATE METHODS (not using php private so this remains compatible with php4
   *
   */

  /** prepares a value to be inserted into the configuration settings using the
   * type information and any provided validation rules. Integers that are out
   * of range will be set to the lowest or highest values. Validation issues
   * are recorded and can be examined with the get_last_update_status() method.
   *
   * @param mixed   integer, string or boolean to be prepared
   * @param type    the type being validated
   * @param bool    emptyok attribute of this setting
   * @param mixed   options string or array used for validating the type
   *
   * @return string value to be inserted into the store
   *                last_update_status is updated with any relevant issues
   */
  function _prepare_conf_value($value, $type, $emptyok, $options = false) {
    switch ($type) {

    case CONF_TYPE_BOOL:
      $ret = $value ? 1 : 0;
      $this->_last_update_status['validated'] = true;
      break;

    case CONF_TYPE_SELECT:
      $val_arr = explode(',',$options);
      if (in_array($value,$val_arr)) {
        $ret = $value;
        $this->_last_update_status['validated'] = true;
      } else {
        $ret = null;
        $this->_last_update_status['validated'] = false;
        $this->_last_update_status['msg'] = _("Invalid value supplied to select");
        $this->_last_update_status['saved_value'] = $ret;
        $this->_last_update_status['saved'] = false;
        //
        // NOTE: returning from function early!
        return $ret;
      }
      break;

    case CONF_TYPE_FSELECT:
			if (!is_array($options)) {
				$options = unserialize($options);
			}
      if (array_key_exists($value, $options)) {
        $ret = $value;
        $this->_last_update_status['validated'] = true;
      } else {
        $ret = null;
        $this->_last_update_status['validated'] = false;
        $this->_last_update_status['msg'] = _("Invalid value supplied to select");
        $this->_last_update_status['saved_value'] = $ret;
        $this->_last_update_status['saved'] = false;
        //
        // NOTE: returning from function early!
        return $ret;
      }
      break;

    case CONF_TYPE_DIR:
      // we don't consider trailing '/' in a directory an error for validation purposes
      $value = rtrim($value,'/');
      // NOTE: fallthrough to CONF_TYPE_TEXT, NO break on purpose!
      //       |
      //       |
      //       V
    case CONF_TYPE_TEXT:
      if ($value == '' && !$emptyok) {
        $this->_last_update_status['validated'] = false;
        $this->_last_update_status['msg'] = _("Empty value not allowed for this field");
      } else if ($options != '' && $value != '') {
        if (preg_match($options,$value)) {
          $ret = $value;
          $this->_last_update_status['validated'] = true;
        } else {
          $ret = null;
          $this->_last_update_status['validated'] = false;
          $this->_last_update_status['msg'] = sprintf(_("Invalid value supplied violates the validation regex: %s"),$options);
          $this->_last_update_status['saved_value'] = $ret;
          $this->_last_update_status['saved'] = false;
          //
          // NOTE: returning from function early!
          return $ret;
        }
      } else {
        $ret = $value;
        $this->_last_update_status['validated'] = true;
      }
      break;

    case CONF_TYPE_INT:
      $ret = !is_numeric($value) && $value != '' ? '' : $value;
      $ret = $emptyok && (string) trim($ret) === '' ? '' : (int) $ret;

      if ($options != '' && (string) $ret !== '') {
        $range = is_array($options) ? $options : explode(',',$options);
        switch ($ret) {
        case $ret < $range[0]:
          $ret = $range[0];
          $this->_last_update_status['validated'] = false;
          $this->_last_update_status['msg'] = sprintf(_("Value [%s] out of range, changed to [%s]"),$value,$ret);
        break;
        case $ret > $range[1]:
          $ret = $range[1];
          $this->_last_update_status['validated'] = false;
          $this->_last_update_status['msg'] = sprintf(_("Value [%s] out of range, changed to [%s]"),$value,$ret);
        break;
        default:
          $this->_last_update_status['validated'] = (string) $ret === (string) $value;
        break;
        }
      } else {
        $this->_last_update_status['validated'] = (string) $ret === (string) $value;
      }
      break;

    default:
      die_issabelpbx(sprintf(_("unknown type: [%s]"),$type));
      break;
    }
    $this->_last_update_status['saved_value'] = $ret;
    $this->_last_update_status['saved'] = true;
    return $ret;
  }

	/** Deal with corner case Settings that change and need further actions
	 *
	 * Some settings require further actions when they change. Any time we set, reset,
	 * etc the settings we should call this function.
	 *
	 * @param string $keyword the setting that needs to be addressed
	 * @param string $value the new value for the setting that was just changed
	 *
	 * @return null
	 */
	function _setting_change_special($keyword, $prep_value) {
		switch ($keyword) {
			case 'AMPMGRPASS':
				ipbx_ami_update(false, $prep_value);
			break;
			case 'AMPMGRUSER':
				ipbx_ami_update($prep_value, false);
			break;
			case 'ASTMGRWRITETIMEOUT':
				ipbx_ami_update(false, false, true);
			break;
			default:
			break;
		}
	}
}

/** DEPRECATED: $amp_conf provided by bootstrap or use issabelpbx_conf class.
 * this must be in a if (!function_exists('parse_amportal_conf')) because during
 * upgrading from 2.8 to 2.9, the old functions.inc.php is currently loaded
 * and calls functions which include this.
 *
 * @param string  filename of amportal.conf to pass to parse_amportal_conf method
 *
 * @return array  $amp_conf array
 */
if (!function_exists('parse_amportal_conf')) {
  function parse_amportal_conf($conf) {
    global $db;

    if (!is_object($db)) {
      $restrict_mods = true;
      $bootstrap_settings['skip_astman'] = true;
      if (!@include_once(getenv('ISSABELPBX_CONF') ? getenv('ISSABELPBX_CONF') : '/etc/issabelpbx.conf')) {
	      include_once('/etc/asterisk/issabelpbx.conf');
      }
    }

    $issabelpbx_conf =& issabelpbx_conf::create();

    issabelpbx_log(IPBX_LOG_ERROR,'parse_amportal_conf() is deprecated. Use of bootstrap.php creates $amp_conf');
    return $issabelpbx_conf->parse_amportal_conf($conf);
  }
}
?>
