<?php
//This file is part of IssabelPBX.

/********************************************************************************************************************/
/* issabelpbxlib.install.php
 *
 * These are used by install_amp and the framework install script to run updates
 *
 * These variables are required to be defined outside of this library. The purpose
 * of this is to allow the library to be used by both install_amp as well as the
 * framework which would potentially be accessing these from different locations.
 *
 * Examples:
 *
 * UPGRADE_DIR     dirname(__FILE__)."/upgrades"
 * MODULE_DIR      dirname(__FILE__)."/amp_conf/htdocs/admin/modules/"
 *
 * or (in framework for instance)
 *
 * MODULE_DIR      dirname(__FILE__)."/htdocs/admin/modules/"
 *
 * $debug = false;
 * $dryrun = false;
 */

function upgrade_all($version) {

    // **** Read upgrades/ directory

    outn("Checking for upgrades..");

    // read versions list from upgrades/
    $versions = array();
    $dir = opendir(UPGRADE_DIR);
    while ($file = readdir($dir)) {
        if (($file[0] != ".") && is_dir(UPGRADE_DIR."/".$file)) {
            $versions[] = $file;
        }
    }
    closedir($dir);

    // callback to use php's version_compare() to sort
    usort($versions, "version_compare_issabel");


    // find versions that are higher than the current version
    $starting_version = false;
    foreach ($versions as $check_version) {
        if (version_compare_issabel($check_version, $version) > 0) { // if check_version < version
            $starting_version = $check_version;
            break;
        }
    }

    // run all upgrades from the list of higher versions
    if ($starting_version) {
        $pos = array_search($starting_version, $versions);
        $upgrades = array_slice($versions, $pos); // grab the list of versions, starting at $starting_version
        out(count($upgrades)." found");
        run_upgrade($upgrades);

        /* Set the base version of key modules, currently core and framework, to the
         * Version packaged with this tarball, if any. The expectation is that the
         * packaging scripts will make these module version numbers the same as the
         * release plus a '.0' which can be incremented for bug fixes delivered through
         * the online system between main releases.
         *
         * added if function_exists because if this is being run from framework there is no
         * need to reset the base version.
         */
        if (function_exists('set_base_version')) {
            set_base_version();
        }

    } else {
        out("No further upgrades necessary");
    }

}

//----------------------------------
// dependencies for upgrade_all


/** Invoke upgrades
 * @param $versions array    The version upgrade scripts to run
 */
function run_upgrade($versions) {
    global $dryrun;

    foreach ($versions as $version) {
        out("Upgrading to ".$version."..");
        install_upgrade($version);
        if (!$dryrun) {
            setversion($version);
        }
        out("Upgrading to ".$version."..OK");
    }
}

//get the version number
function install_getversion() {
    global $db;
    $sql = "SELECT value FROM admin WHERE variable = 'version'";
    $results = $db->getAll($sql);
    if(DB::IsError($results)) {
        return false;
    }
    return $results[0][0];
}

//set the version number
function setversion($version) {
    global $db;
    $sql = "UPDATE admin SET value = '".$version."' WHERE variable = 'version'";
    debug($sql);
    $result = $db->query($sql);
    if(DB::IsError($result)) {
        die($result->getMessage());
    }
}

/** Install a particular version
 */
function install_upgrade($version) {
    global $db;
    global $dryrun;
    global $amp_conf;

    $db_engine = $amp_conf["AMPDBENGINE"];

    if (is_dir(UPGRADE_DIR."/".$version)) {
        // sql scripts first
        $dir = opendir(UPGRADE_DIR."/".$version);
        while ($file = readdir($dir)) {
            if (($file[0] != ".") && is_file(UPGRADE_DIR."/".$version."/".$file)) {
                if ( (strtolower(substr($file,-4)) == ".sqlite") && ($db_engine == "sqlite") ) {
                    install_sqlupdate( $version, $file );
                }
                elseif ((strtolower(substr($file,-4)) == ".sql") &&
                    ( ($db_engine  == "mysql")  ||  ($db_engine  == "pgsql") || ($db_engine == "sqlite3") ) ) {
                        install_sqlupdate( $version, $file );
                    }
            }
        }

        // now non sql scripts
        $dir = opendir(UPGRADE_DIR."/".$version);
        while ($file = readdir($dir)) {
            if (($file[0] != ".") && is_file(UPGRADE_DIR."/".$version."/".$file)) {
                if ((strtolower(substr($file,-4)) == ".sql") || (strtolower(substr($file,-7)) == ".sqlite")) {
                    // sql scripts were dealt with first
                } else if (strtolower(substr($file,-4)) == ".php") {
                    out("-> Running PHP script ".UPGRADE_DIR."/".$version."/".$file);
                    if (!$dryrun) {
                        run_included(UPGRADE_DIR."/".$version."/".$file);
                    }

                } else if (is_executable(UPGRADE_DIR."/".$version."/".$file)) {
                    out("-> Executing ".UPGRADE_DIR."/".$version."/".$file);
                    if (!$dryrun) {
                        exec(UPGRADE_DIR."/".$version."/".$file);
                    }
                } else {
                    error("-> Don't know what to do with ".UPGRADE_DIR."/".$version."/".$file);
                }
            }
        }

    }
}


function checkDiff($file1, $file2) {
    // diff, ignore whitespace and be quiet
    exec("diff -wq ".escapeshellarg($file2)." ".escapeshellarg($file1), $output, $retVal);
    return ($retVal != 0);
}

function amp_mkdir($directory, $mode = "0755", $recursive = false) {
    global $runas_uid;
    global $runas_gid;
    debug("mkdir ".$directory.", ".$mode);
    $ntmp = sscanf($mode,"%o",$modenum); //assumes all inputs are octal
    if (version_compare(phpversion(), '5.0') < 0) {
        // php <5 can't recursively create directories
        if ($recursive) {
            $output = false;
            $return_value = false;
            exec("mkdir -m ".$mode." -p ".$directory,  $output, $return_value);
            exec("chown -R $runas_uid:$runas_gid $directory");
            return ($return_value == 0);
        } else {
            $ret=mkdir($directory, $modenum);
            exec("chown -R $runas_uid:$runas_gid $directory");
            return $ret;
        }
    } else {
        $ret=mkdir($directory, $modenum, $recursive);
        exec("chown -R $runas_uid:$runas_gid $directory");
        return $ret;
    }
}

/**
 * Recursive Read Links
 *
 * This function is used to recursively read symlink until we reach a real directory
 *
 * @author Bryan Walters <bryan.walters@schmoozecom.com>
 * @params string $source - The original file we are replacing
 * @returns array of the original source we read in and the real directory for it
 */
function recursive_readlink($source){
    $dir = dirname($source);
    $links = array();
    $ldir = null;

    while (!in_array($dir,array('.','..','','/')) && strpos('.git',$dir) == false) {
        if ($dir == $ldir) {
            break;
        }
        if (is_link($dir)) {
            $ldir = readlink($dir);
            $file = str_replace($dir, $ldir, $source);
            if (!is_link($ldir) && file_exists($file)) {
                $links[$source] = $file;
            }
        } else {
            if (file_exists($source) && !is_link(dirname($source))) {
                break;
            }
            $ldir = dirname($dir);
            $file = str_replace($dir, $ldir, $source);
            if (!is_link($ldir) && file_exists($file)) {
                $links[$source] = $file;
            }
        }
        $ldir = $dir;
        $dir = dirname($dir);
    }

    return $links;
}

/**
 * Substitute Read Links
 *
 * This function is used to substitute symlinks, to real directories where information is stored
 *
 * @author Bryan Walters <bryan.walters@schmoozecom.com>
 * @params string $source - The original file we are replacing
 * @params array $links - A list of possible replacements
 * @return string of the real file path to the given source
 */
function substitute_readlinks($source,$links) {
    foreach ($links as $key => $value) {
        if (strpos($source, $key) !== false) {
            $source = str_replace($key, $value, $source);
            return $source;
        }
    }
}

/** Recursively copy a directory
 */
function recursive_copy($dirsourceparent, $dirdest, &$md5sums, $dirsource = "") {
    global $dryrun;
    global $check_md5s;
    global $amp_conf;
    global $asterisk_conf;
    global $install_moh;
    global $make_links;

    $moh_subdir = isset($amp_conf['MOHDIR']) ? trim(trim($amp_conf['MOHDIR']),'/') : 'mohmp3';

    // total # files, # actually copied
    $num_files = $num_copied = 0;

    if ($dirsource && ($dirsource[0] != "/")) $dirsource = "/".$dirsource;

    if (is_dir($dirsourceparent.$dirsource)) $dir_handle = opendir($dirsourceparent.$dirsource);

    /*
    echo "dirsourceparent: "; var_dump($dirsourceparent);
    echo "dirsource: "; var_dump($dirsource);
    echo "dirdest: "; var_dump($dirdest);
     */

    while (isset($dir_handle) && ($file = readdir($dir_handle))) {
        if (($file!=".") && ($file!="..") && ($file != "CVS") && ($file != ".svn") && ($file != ".git")) {
            $source = $dirsourceparent.$dirsource."/".$file;
            $destination =  $dirdest.$dirsource."/".$file;

            if ($dirsource == "" && $file == "moh" && !$install_moh) {
                // skip to the next dir
                continue;
            }


            // configurable in amportal.conf
            $destination=str_replace("/htdocs/",trim($amp_conf["AMPWEBROOT"])."/",$destination);
            if(strpos($dirsource, 'modules') === false) $destination=str_replace("/bin",trim($amp_conf["AMPBIN"]),$destination);
            $destination=str_replace("/sbin",trim($amp_conf["AMPSBIN"]),$destination);

            // the following are configurable in asterisk.conf
            $destination=str_replace("/astetc",trim($asterisk_conf["astetcdir"]),$destination);
            $destination=str_replace("/moh",trim($asterisk_conf["astvarlibdir"])."/$moh_subdir",$destination);
            $destination=str_replace("/astvarlib",trim($asterisk_conf["astvarlibdir"]),$destination);
            if(strpos($dirsource, 'modules') === false) $destination=str_replace("/agi-bin",trim($asterisk_conf["astagidir"]),$destination);
            if(strpos($dirsource, 'modules') === false) $destination=str_replace("/sounds",trim($asterisk_conf["astvarlibdir"])."/sounds",$destination);

            // if this is a directory, ensure destination exists
            if (is_dir($source)) {
                if (!file_exists($destination)) {
                    if ((!$dryrun) && ($destination != "")) {
                        amp_mkdir($destination, "0750", true);
                    }
                }
            }

            //var_dump($md5sums);
            if (!is_dir($source)) {
                $md5_source = preg_replace("|^/?amp_conf/|", "/", $source);

                if ($check_md5s && file_exists($destination) && isset($md5sums[$md5_source]) && (md5_file($destination) != $md5sums[$md5_source])) {
                    // double check using diff utility (and ignoring whitespace)
                    // This is a somewhat edge case (eg, the file doesn't match
                    // it's md5 sum from the previous version, but no substantial
                    // changes exist compared to the current version), but it
                    // prevents a useless prompt to the user.
                    if (checkDiff($source, $destination)) {
                        $overwrite = ask_overwrite($source, $destination);
                    } else {
                        debug("NOTE: MD5 for ".$destination." was different, but `diff` did not detect any (non-whitespace) changes: overwriting");
                        $overwrite = true;
                    }
                } else {
                    $overwrite = true;
                }

                // These are modified by apply_conf.sh, there may be others that fit in this category also. This keeps these from
                // being symlinked and then developers inadvertently checking in the changes when they should not have.
                //
                $never_symlink = array("cdr_mysql.conf", "manager.conf", "vm_email.inc");

                $num_files++;
                if ($overwrite) {
                    debug(($make_links ? "link" : "copy")." ".$source." -> ".$destination);
                    if (!$dryrun) {
                        if ($make_links && !in_array(basename($source),$never_symlink)) {
                            // symlink, unlike copy, doesn't overwrite - have to delete first
                            if (is_link($destination) || file_exists($destination)) {
                                unlink($destination);
                            }

                            $links = recursive_readlink($source);
                            if (!empty($links)) {
                                symlink(substitute_readlinks($source,$links), $destination);
                            } else {
                                symlink(dirname(__FILE__)."/".$source, $destination);
                            }
                        } else {
                            if(file_exists($destination) && preg_match("/\.conf$/",$destination)) {
                                rename($destination,$destination.".old_".date('YMd_His'));
                            }
                            copy($source, $destination);
                        }
                        $num_copied++;
                    }
                } else {
                    debug("not overwriting ".$destination);
                }
            } else {
                //echo "recursive_copy($dirsourceparent, $dirdest, $md5sums, $dirsource/$file)";
                list($tmp_num_files, $tmp_num_copied) = recursive_copy($dirsourceparent, $dirdest, $md5sums, $dirsource."/".$file);
                $num_files += $tmp_num_files;
                $num_copied += $tmp_num_copied;
            }
        }
    }

    if (isset($dir_handle)) closedir($dir_handle);

    return array($num_files, $num_copied);
}

function read_md5_file($filename) {
    $md5 = array();
    if (file_exists($filename)) {
        foreach (file($filename) as $line) {
            if (preg_match("/^([a-f0-9]{32})\s+(.*)$/", $line, $matches)) {
                $md5[ "/".$matches[2] ] = $matches[1];
            }
        }
    }
    return $md5;
}

/** Include a .php file
 * This is a function just to keep a separate context
 */
function run_included($file) {
    global $db;
    global $amp_conf;

    include($file);
}

function install_sqlupdate( $version, $file )
{
    global $db;
    global $dryrun;

    out("-> Running SQL script ".UPGRADE_DIR."/".$version."/".$file);
    // run sql script
    $fd = fopen(UPGRADE_DIR."/".$version."/".$file, "r");
    $data = "";
    while (!feof($fd)) {
        $data .= fread($fd, 1024);
    }
    fclose($fd);

    preg_match_all("/((SELECT|INSERT|UPDATE|DELETE|CREATE|DROP|ALTER).*);\s*\n/Us", $data, $matches);

    foreach ($matches[1] as $sql) {
        debug($sql);
        if (!$dryrun) {
            $result = $db->query($sql);
            if(DB::IsError($result)) {
                fatal($result->getDebugInfo()."\" while running ".$file."\n");
            }
        }
    }
}

/********************************************************************************************************************/
/*                          ISSABELPBX SETTINGS (AMPORTAL.CONF) DEFINED HERE                                           */
/********************************************************************************************************************/
//
// TODO: find a good way to extract the required localization strings for the tools to pickup
//
// issabelpbx_settings_init()
// this is where we initialize all the issabelpbx_settings (amportal.conf). This will be run with install_amp and every
// time we run the framework installer, so new settings can be added here that are framework wide. It may make send to
// break this out separately but for now we'll keep it here since this is already part of the infrastructure that is
// used by both install_amp and the framework install/upgrade script.
//
function issabelpbx_settings_init($commit_to_db = false) {
    global $amp_conf;

    if (!class_exists('issabelpbx_conf')) {
        include_once ($amp_conf['AMPWEBROOT'].'/admin/libraries/issabelpbx_conf.class.php');
    }

    $issabelpbx_conf =& issabelpbx_conf::create();

    $set['value'] = '';
    $set['defaultval'] =& $set['value'];
    $set['readonly'] = 0;
    $set['hidden'] = 0;
    $set['level'] = 0;
    $set['module'] = '';
    $set['emptyok'] = 0;


    //
    // CATEGORY: Advanced Settings Display
    //
    $set['category'] = 'Advanced Settings Details';

  /* This was too confusing, will remove for now and re-evaluate if needed
  // AS_DISPLAY_DETAIL_LEVEL
  $set['value'] = '0';
  $set['options'] = '0,1,2,3,4,5,6,7,8,9,10';
  $set['name'] = 'Display Detail Level';
  $set['description'] = 'This will filter which settings that are displayed on this Advanced Settings page. The higher the level, the more obscure settings will be shown. Settings at higher levels are unlikely to be of interest to most users and could be more volatile to breaking your system if set wrong.';
  $set['emptyok'] = 0;
  $set['level'] = 0;
  $set['readonly'] = 0;
  $set['type'] = CONF_TYPE_SELECT;
  $issabelpbx_conf->define_conf_setting('AS_DISPLAY_DETAIL_LEVEL',$set);
  $set['readonly'] = 0;
  $set['level'] = 0;
   */

    // Make this hidden, has proven to confusing since users can't change them. This could be turned on programatically for dev purposes.
    // AS_DISPLAY_HIDDEN_SETTINGS
    //
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Display Hidden Settings';
    $set['description'] = 'This will display settings that are normally hidden by the system. These settings are often internally used settings that are not of interest to most users.';
    $set['emptyok'] = 0;
    $set['level'] = 0;
    $set['readonly'] = 1;
    $set['hidden'] = 1;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('AS_DISPLAY_HIDDEN_SETTINGS',$set);
    $set['readonly'] = 0;
    $set['level'] = 0;
    $set['hidden'] = 0;

    // AS_DISPLAY_READONLY_SETTINGS
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Display Readonly Settings';
    $set['description'] = 'This will display settings that are readonly. These settings are often internally used settings that are not of interest to most users. Since they are readonly they can only be viewed.';
    $set['emptyok'] = 0;
    $set['level'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('AS_DISPLAY_READONLY_SETTINGS',$set);
    $set['readonly'] = 0;
    $set['level'] = 0;

    // AS_OVERRIDE_READONLY
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Override Readonly Settings';
    $set['description'] = 'Setting this to true will allow you to override un-hidden readonly setting to change them. Settings that are readonly may be extremely volatile and have a high chance of breaking your system if you change them. Take extreme caution when electing to make such changes.';
    $set['emptyok'] = 0;
    $set['level'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('AS_OVERRIDE_READONLY',$set);
    $set['readonly'] = 0;
    $set['level'] = 0;

    // AS_DISPLAY_FRIENDLY_NAME
    $set['value'] = true;
    $set['options'] = '';
    $set['name'] = 'Display Friendly Name';
    $set['description'] = 'Normally the friendly names will be displayed on this page and the internal issabelpbx_conf configuration names are shown in the tooltip. If you prefer to view the configuration variables, and the friendly name in the tooltip, set this to false..';
    $set['emptyok'] = 0;
    $set['level'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('AS_DISPLAY_FRIENDLY_NAME',$set);
    $set['readonly'] = 0;
    $set['level'] = 0;

    //
    // CATEGORY: System Setup
    //
    $set['category'] = 'System Setup';

    // AMPSYSLOGLEVEL
    $set['value'] = 'FILE';
    $set['options'] = 'FILE, LOG_EMERG, LOG_ALERT, LOG_CRIT, LOG_ERR, LOG_WARNING, LOG_NOTICE, LOG_INFO, LOG_DEBUG';
    // LOG_SQL, SQL are discontinued, they are removed during migration if the slipped in and in core if it were to persist because amportal.conf was not
    // writeable for a while.
    //
    if (isset($amp_conf['AMPSYSLOGLEVEL']) && (strtoupper($amp_conf['AMPSYSLOGLEVEL']) == 'SQL' || strtoupper($amp_conf['AMPSYSLOGLEVEL']) == 'LOG_SQL')) {
        $set['options'] .= ', LOG_SQL, SQL';
    }
    $set['name'] = 'IssabelPBX Log Routing';
    $set['description'] = "Determine where to send log information if the log is enabled ('Disable IssabelPBX Log' (AMPDISABLELOG) false. There are two places to route the log messages. 'FILE' will send all log messages to the defined 'IssabelPBX Log File' (IPBX_LOG_FILE). All the other settings will route the log messages to your System Logging subsystem (syslog) using the specified log level. Syslog can be configured to route different levels to different locations. See 'syslog' documentation (man syslog) on your system for more details.";
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['sortorder'] = -190;
    $set['type'] = CONF_TYPE_SELECT;
    $issabelpbx_conf->define_conf_setting('AMPSYSLOGLEVEL',$set);

    // AMPDISABLELOG
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Disable IssabelPBX Log';
    $set['description'] = 'Whether or not to invoke the IssabelPBX log facility.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['sortorder'] = -180;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('AMPDISABLELOG',$set);

    // LOG_OUT_MESSAGES
    $set['value'] = true;
    $set['options'] = '';
    $set['name'] = 'Log Verbose Messages';
    $set['description'] = 'IssabelPBX has many verbose and useful messages displayed to users during module installation, system installations, loading configurations and other places. In order to accumulate these messages in the log files as well as the on screen display, set this to true.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['sortorder'] = -170;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('LOG_OUT_MESSAGES',$set);

    // LOG_NOTIFICATIONS
    $set['value'] = true;
    $set['options'] = '';
    $set['name'] = 'Send Dashboard Notifications to Log';
    $set['description'] = 'When enabled all notification updates to the Dashboard notification panel will also be logged into the specified log file when enabled.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['sortorder'] = -160;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('LOG_NOTIFICATIONS',$set);

    // IPBX_LOG_FILE
    $set['value'] = $amp_conf['ASTLOGDIR'] . '/issabelpbx.log';
    $set['options'] = '';
    $set['name'] = 'IssabelPBX Log File';
    $set['description'] = 'Full path and name of the IssabelPBX Log File used in conjunction with the Syslog Level (AMPSYSLOGLEVEL) being set to FILE, not used otherwise. Initial installs may have some early logging sent to /tmp/issabelpbx_pre_install.log when it is first bootstrapping the installer.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['sortorder'] = -150;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('IPBX_LOG_FILE',$set);

    // PHP_ERROR_HANDLER_OUTPUT
    $set['value'] = 'issabelpbxlog';
    $set['options'] = array('dbug','issabelpbxlog','off');
    $set['name'] = 'PHP Error Log Output';
    $set['description'] = "Where to send PHP errors, warnings and notices by the IssabelPBX PHP error handler. Set to 'dbug', they will go to the Debug File regardless of whether dbug Loggin is disabled or not. Set to 'issabelpbxlog' will send them to the IssabelPBX Log. Set to 'off' and they will be ignored.";
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['sortorder'] = -140;
    $set['type'] = CONF_TYPE_SELECT;
    $issabelpbx_conf->define_conf_setting('PHP_ERROR_HANDLER_OUTPUT',$set);

    // AGGRESSIVE_DUPLICATE_CHECK
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Aggresively Check for Duplicate Extensions';
    $set['description'] = "When set to true IssabelPBX will update its extension map every page load. This is used to check for duplicate extension numbers in the client side javascript validation. Normally the extension map is only created when Apply Configuration Settings is pressed and retrieve_conf is run.";
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['sortorder'] = -137;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('AGGRESSIVE_DUPLICATE_CHECK',$set);


    // AMPEXTENSIONS
    $set['value'] = 'extensions';
    $set['options'] = 'extensions,deviceanduser';
    $set['name'] = 'User & Devices Mode';
    $set['description'] = 'Sets the extension behavior in IssabelPBX.  If set to <b>extensions</b>, Devices and Users are administered together as a unified Extension, and appear on a single page. If set to <b>deviceanduser</b>, Devices and Users will be administered separately. Devices (e.g. each individual line on a SIP phone) and Users (e.g. <b>101</b>) will be configured independent of each other, allowing association of one User to many Devices, or allowing Users to login and logout of Devices.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['sortorder'] = -135;
    $set['type'] = CONF_TYPE_SELECT;
    $issabelpbx_conf->define_conf_setting('AMPEXTENSIONS',$set);

    // AUTHTYPE
    $set['value'] = 'database';
    $set['options'] = 'database,none,webserver';
    $set['name'] = 'Authorization Type';
    $set['description'] = 'Authentication type to use for web admin. If type set to <b>database</b>, the primary AMP admin credentials will be the AMPDBUSER/AMPDBPASS above. When using database you can create users that are restricted to only certain module pages. When set to none, you should make sure you have provided security at the apache level. When set to webserver, IssabelPBX will expect authentication to happen at the apache level, but will take the user credentials and apply any restrictions as if it were in database mode.';
    $set['emptyok'] = 0;
    $set['level'] = 3;
    $set['readonly'] = 1;
    $set['sortorder'] = -130;
    $set['type'] = CONF_TYPE_SELECT;
    $issabelpbx_conf->define_conf_setting('AUTHTYPE',$set);
    $set['level'] = 0;

    // AMP_ACCESS_DB_CREDS
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Allow Login With DB Credentials';
    $set['description'] = "When Set to True, admin access to the IssabelPBX GUI will be allowed using the IssabelPBX configured AMPDBUSER and AMPDBPASS credentials. This only applies when Authorization Type is 'database' mode.";
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['sortorder'] = -126;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('AMP_ACCESS_DB_CREDS',$set);

    // ARI_ADMIN_USERNAME
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'User Portal Admin Username';
    $set['description'] = 'This is the default admin name used to allow an administrator to login to ARI bypassing all security. Change this to whatever you want, do not forget to change the User Portal Admin Password as well. Default = not set';
    $set['emptyok'] = 1;
    $set['readonly'] = 0;
    $set['sortorder'] = -120;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('ARI_ADMIN_USERNAME',$set);

    // ARI_ADMIN_PASSWORD
    $set['value'] = 'ari_password';
    $set['options'] = '';
    $set['name'] = 'User Portal Admin Password';
    $set['description'] = 'This is the default admin password to allow an administrator to login to ARI bypassing all security. Change this to a secure password. Default = not set';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['sortorder'] = -110;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('ARI_ADMIN_PASSWORD',$set);

    // FORCED_ASTVERSION
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'Force Asterisk Version';
    $set['description'] = 'Normally IssabelPBX gets the current Asterisk version directly from Asterisk. This is required to generate proper dialplan for a given version. When using some custom Asterisk builds, the version may not be properly parsed and improper dialplan generated. Setting this to an equivalent Asterisk version will override what is read from Asterisk. This SHOULD be left blank unless you know what you are doing.';
    $set['emptyok'] = 1;
    $set['readonly'] = 1;
    $set['sortorder'] = -100;
    $set['type'] = CONF_TYPE_TEXT;
    $set['level'] = 4;
    $issabelpbx_conf->define_conf_setting('FORCED_ASTVERSION',$set);
    $set['level'] = 0;

    // AMPENGINE
    $set['value'] = 'asterisk';
    $set['options'] = 'asterisk';
    $set['name'] = 'Telephony Engine';
    $set['description'] = 'The telephony backend engine being used, asterisk is the only option currently.';
    $set['emptyok'] = 0;
    $set['level'] = 3;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_SELECT;
    $issabelpbx_conf->define_conf_setting('AMPENGINE',$set);
    $set['level'] = 0;

    // AMPVMUMASK
    $set['value'] = '007';
    $set['options'] = '';
    $set['name'] = 'Asterisk VMU Mask';
    $set['description'] = 'Defaults to 077 allowing only the asterisk user to have any permission on VM files. If set to something like 007, it would allow the group to have permissions. This can be used if setting apache to a different user then asterisk, so that the apache user (and thus ARI) can have access to read/write/delete the voicemail files. If changed, some of the voicemail directory structures may have to be manually changed.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $set['level'] = 4;
    $issabelpbx_conf->define_conf_setting('AMPVMUMASK',$set);
    $set['level'] = 0;

    // AMPWEBADDRESS
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'IssabelPBX Web Address';
    $set['description'] = 'This is the address of your Web Server. It is mostly obsolete and derived when not supplied and will be phased out, but there are still some areas expecting a variable to be set and if you are using it this will migrate your value.';
    $set['emptyok'] = 1;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $set['level'] = 4;
    $issabelpbx_conf->define_conf_setting('AMPWEBADDRESS',$set);
    $set['level'] = 0;

    // AMPASTERISKUSER
    $set['value'] = 'asterisk';
    $set['options'] = '';
    $set['name'] = 'System Asterisk User';
    $set['description'] = 'The user Asterisk should be running as, used by issabelpbx_engine. Most systems should not change this.';
    $set['emptyok'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $set['level'] = 4;
    $set['readonly'] = 1;
    $issabelpbx_conf->define_conf_setting('AMPASTERISKUSER',$set);
    $set['level'] = 0;

    // AMPASTERISKGROUP
    $set['value'] = 'asterisk';
    $set['options'] = '';
    $set['name'] = 'System Asterisk Group';
    $set['description'] = 'The user group Asterisk should be running as, used by issabelpbx_engine. Most systems should not change this.';
    $set['emptyok'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $set['level'] = 4;
    $set['readonly'] = 1;
    $issabelpbx_conf->define_conf_setting('AMPASTERISKGROUP',$set);
    $set['level'] = 0;

    // AMPASTERISKWEBUSER
    $set['value'] = 'asterisk';
    $set['options'] = '';
    $set['name'] = 'System Web User';
    $set['description'] = 'The user your httpd should be running as, used by issabelpbx_engine. Most systems should not change this.';
    $set['emptyok'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $set['level'] = 4;
    $set['readonly'] = 1;
    $issabelpbx_conf->define_conf_setting('AMPASTERISKWEBUSER',$set);
    $set['level'] = 0;

    // AMPASTERISKWEBGROUP
    $set['value'] = 'asterisk';
    $set['options'] = '';
    $set['name'] = 'System Web Group';
    $set['description'] = 'The user group your httpd should be running as, used by issabelpbx_engine. Most systems should not change this.';
    $set['emptyok'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $set['level'] = 4;
    $set['readonly'] = 1;
    $issabelpbx_conf->define_conf_setting('AMPASTERISKWEBGROUP',$set);
    $set['level'] = 0;

    // AMPDEVUSER
    $set['value'] = 'asterisk';
    $set['options'] = '';
    $set['name'] = 'System Device User';
    $set['description'] = 'The user that various device directories should be set to, used by issabelpbx_engine. Examples include /dev/zap, /dev/dahdi, /dev/misdn, /dev/mISDN and /dev/dsp. Most systems should not change this.';
    $set['emptyok'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $set['level'] = 4;
    $set['readonly'] = 1;
    $issabelpbx_conf->define_conf_setting('AMPDEVUSER',$set);
    $set['level'] = 0;

    // AMPDEVGROUP
    $set['value'] = 'asterisk';
    $set['options'] = '';
    $set['name'] = 'System Device Group';
    $set['description'] = 'The user group that various device directories should be set to, used by issabelpbx_engine. Examples include /dev/zap, /dev/dahdi, /dev/misdn, /dev/mISDN and /dev/dsp. Most systems should not change this.';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_TEXT;
    $set['level'] = 4;
    $issabelpbx_conf->define_conf_setting('AMPDEVGROUP',$set);
    $set['level'] = 0;

    // BROWSER_STATS
    $set['value'] = true;
    $set['options'] = '';
    $set['name'] = 'Browser Stats';
    $set['description'] = 'Setting this to true will allow the development team to use google analytics to anonymously analyze browser information to help make better development decision.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('BROWSER_STATS',$set);

    // USE_GOOGLE_CDN_JS
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Use Google Distribution Network for js Downloads';
    $set['description'] = 'Setting this to true will fetch system javascript libraries such as jQuery and jQuery-ui from ajax.googleapis.com. This can be advantageous if accessing remote or multiple different IssabelPBX systems since the libraries are only cached once in your browser. If external internet connections are problematic, setting this true could result in slow systems. IssabelPBX will always fallback to the locally available libraries if the CDN is not available.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('USE_GOOGLE_CDN_JS',$set);

    //JQUERY_VER
    $set['value'] = '1.7.1';
    $set['options'] = '';
    $set['defaultval'] =& $set['value'];
    $set['readonly'] = 1;
    $set['hidden'] = 1;
    $set['level'] = 0;
    $set['module'] = '';
    $set['category'] = 'System Setup';
    $set['emptyok'] = 0;
    $set['name'] = 'jQuery Version';
    $set['description'] = 'The version of jQuery that we wish to use.';
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('JQUERY_VER', $set);
    $set['hidden'] = 0;

    //JQUERYUI_VER
    $set['value'] = '1.8.9';
    $set['options'] = '';
    $set['defaultval'] =& $set['value'];
    $set['readonly'] = 1;
    $set['hidden'] = 1;
    $set['level'] = 0;
    $set['module'] = '';
    $set['category'] = 'System Setup';
    $set['emptyok'] = 0;
    $set['name'] = 'jQuery UI Version';
    $set['description'] = 'The version of jQuery UI that we wish to use.';
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('JQUERYUI_VER', $set);
    $set['hidden'] = 0;

    // CRONMAN_UPDATES_CHECK
    $set['value'] = true;
    $set['options'] = '';
    $set['defaultval'] =& $set['value'];
    $set['readonly'] = 1;
    $set['hidden'] = 0;
    $set['level'] = 0;
    $set['module'] = '';
    $set['emptyok'] = 0;
    $set['name'] = 'Update Notifications';
    $set['description'] = 'IssabelPBX allows you to automatically check for updates online. The updates will NOT be automatically installed. It is STRONGYLY advised that you keep this enabled and keep updated of these important notificaions to avoid costly security issues.';
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('CRONMAN_UPDATES_CHECK',$set);
    $set['hidden'] = 0;


    //
    // CATEGORY: Dialplan and Operational
    //
    $set['category'] = 'Dialplan and Operational';

    // AMPBADNUMBER
    $set['value'] = true;
    $set['options'] = '';
    $set['name'] = 'Use bad-number Context';
    $set['description'] = 'Generate the bad-number context which traps any bogus number or feature code and plays a message to the effect. If you use the Early Dial feature on some Grandstream phones, you will want to set this to false.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $set['level'] = 2;
    $issabelpbx_conf->define_conf_setting('AMPBADNUMBER',$set);
    $set['level'] = 0;

    // CWINUSEBUSY
    $set['value'] = true;
    $set['options'] = '';
    $set['name'] = 'Occupied Lines CW Busy';
    $set['description'] = 'For extensions that have CW enabled, report unanswered CW calls as <b>busy</b> (resulting in busy voicemail greeting). If set to no, unanswered CW calls simply report as <b>no-answer</b>.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('CWINUSEBUSY',$set);

    // ZAP2DAHDICOMPAT
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Convert ZAP Settings to DAHDi';
    $set['description'] = 'If set to true, IssabelPBX will check if you have chan_dahdi installed. If so, it will automatically use all your ZAP configuration settings (devices and trunks) and silently convert them, under the covers, to DAHDi so no changes are needed. The GUI will continue to refer to these as ZAP but it will use the proper DAHDi channels. This will also keep Zap Channel DIDs working.';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('ZAP2DAHDICOMPAT',$set);

    // DYNAMICHINTS
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Dynamically Generate Hints';
    $set['description'] = 'If true, Core will not statically generate hints, but instead make a call to the AMPBIN php script, and generate_hints.php through an Asterisk #exec call. This requires asterisk.conf to be configured with <b>execincludes=yes<b> set in the [options] section.';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('DYNAMICHINTS',$set);

    // ENABLECW
    $set['value'] = true;
    $set['options'] = '';
    $set['name'] = 'CW Enabled by Default';
    $set['description'] = 'Enable call waiting by default when an extension is created (Default is yes). Set to <b>no</b> to if you do not want phones to be commissioned with call waiting already enabled. The user would then be required to dial the CW feature code (*70 default) to enable their phone. Most installations should leave this alone. It allows multi-line phones to receive multiple calls on their line appearances.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('ENABLECW',$set);

    // FCBEEPONLY
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Feature Codes Beep Only';
    $set['description'] = 'When set to true, a beep is played instead of confirmation message when activating/de-activating: CallForward, CallWaiting, DayNight, DoNotDisturb and FindMeFollow.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('FCBEEPONLY',$set);

    // USEDEVSTATE
    $set['value'] = true;
    $set['options'] = '';
    $set['name'] = 'Enable Custom Device States';
    $set['description'] = 'If this is set, it assumes that you are running Asterisk 1.4 or higher and want to take advantage of the func_devstate.c backport available from Asterisk 1.6. This allows custom hints to be created to support BLF for server side feature codes such as daynight, followme, etc';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('USEDEVSTATE',$set);

    // USEGOOGLEDNSFORENUM
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Use Google DNS for Enum';
    $set['description'] = 'Setting this flag will generate the required global variable so that enumlookup.agi will use Google DNS 8.8.8.8 when performing an ENUM lookup. Not all DNS deals with NAPTR record, but Google does. There is a drawback to this as Google tracks every lookup. If you are not comfortable with this, do not enable this setting. Please read Google FAQ about this: <b>http://code.google.com/speed/public-dns/faq.html#privacy</b>.';
    $set['emptyok'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $set['level'] = 2;
    $set['readonly'] = 0;
    $issabelpbx_conf->define_conf_setting('USEGOOGLEDNSFORENUM',$set);
    $set['level'] = 0;

    // DISABLECUSTOMCONTEXTS
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Disable -custom Context Includes';
    $set['description'] = 'Normally IssabelPBX auto-generates a custom context that may be usable for adding custom dialplan to modify the normal behavior of IssabelPBX. It takes a good understanding of how Asterisk processes these includes to use this and in many of the cases, there is no useful application. All includes will result in a WARNING in the Asterisk log if there is no context found to include though it results in no errors. If you know that you want the includes, you can set this to true. If you comment it out IssabelPBX will revert to legacy behavior and include the contexts.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $set['level'] = 2;
    $issabelpbx_conf->define_conf_setting('DISABLECUSTOMCONTEXTS',$set);
    $set['level'] = 0;


    // NOOPTRACE
    $set['value'] = '0';
    $set['options'] = '0,1,2,3,4,5,6,7,8,9,10';
    $set['name'] = 'NoOp Traces in Dialplan';
    $set['description'] = 'Some modules will generate lots of NoOp() commands proceeded by a [TRACE](trace_level) that can be used during development or while trying to trace call flows. These NoOp() commands serve no other purpose so if you do not want to see excessive NoOp()s in your dialplan you can set this to 0. The higher the number the more detailed level of trace NoOp()s will be generated';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_SELECT;
    $issabelpbx_conf->define_conf_setting('NOOPTRACE',$set);

    // DIVERSIONHEADER
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Generate Diversion Headers';
    $set['description'] = 'If this value is set to true, then calls going out your outbound routes that originate from outside your PBX and were subsequently forwarded through a call forward, ring group, follow-me or other means, will have a SIP diversion header added to the call with the original incoming DID assuming there is a DID available. This is useful with some carriers that may require this under certain circumstances.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('DIVERSIONHEADER',$set);

    // CFRINGTIMERDEFAULT
    $opts = array();
    for ($i=-1;$i<=120;$i++) {
        $opts[]=$i;
    }
    $set['value'] = '0';
    $set['options'] = $opts;
    $set['name'] = 'Call Forward Ringtimer Default';
    $set['description'] = 'This is the default time in seconds to try and connect a call that has been call forwarded by the server side CF, CFU and CFB options. (If your phones use client side CF such as SIP redirects, this will not have any affect) If set to the default of 0, it will use the standard ring timer. If set to -1 it will ring the forwarded number with no limit which is consistent with the behavior of some existing PBX systems. If set to any other value, it will ring for that duration before diverting the call to the users voicemail if they have one. This can be overridden for each extension.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_SELECT;
    $issabelpbx_conf->define_conf_setting('CFRINGTIMERDEFAULT',$set);
    unset($opts);

    // DEFAULT_INTERNAL_AUTO_ANSWER
    $set['value'] = 'disabled';
    $set['options'] = array('disabled','intercom');
    $set['name'] = 'Internal Auto Answer Default';
    $set['description'] = "Default setting for new extensions. When set to Intercom, calls to new extensions/users from other internal users act as if they were intercom calls meaning they will be auto-answered if the endpoint supports this feature and the system is configured to operate in this mode. All the normal white list and black list settings will be honored if they are set. External calls will still ring as normal, as will certain other circumstances such as blind transfers and when a Follow Me is configured and enabled. If Disabled, the phone rings as a normal phone.";
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_SELECT;
    $issabelpbx_conf->define_conf_setting('DEFAULT_INTERNAL_AUTO_ANSWER',$set);

    // FORCE_INTERNAL_AUTO_ANSWER_ALL
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Force All Internal Auto Answer';
    $set['description'] = "Force all extensions to operate in the Internal Auto Answer mode regardless of their individual settings. See 'Internal Auto Answer Default' for more information.";
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('FORCE_INTERNAL_AUTO_ANSWER_ALL',$set);

    // CONCURRENCYLIMITDEFAULT
    $opts = array();
    for ($i=0;$i<=120;$i++) {
        $opts[]=$i;
    }
    $set['value'] = '0';
    $set['options'] = $opts;
    $set['name'] = 'Extension Concurrency Limit';
    $set['description'] = 'Default maximum number of outbound simultaneous calls that an extension can make. This is also very useful as a Security Protection against a system that has been compromised. It will limit the number of simultaneous calls that can be made on the compromised extension. This default is used when an extension is created. A default of 0 means no limit.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_SELECT;
    $issabelpbx_conf->define_conf_setting('CONCURRENCYLIMITDEFAULT',$set);
    unset($opts);

    // BLOCK_OUTBOUND_TRUNK_CNAM
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Block CNAM on External Trunks';
    $set['description'] = "Some carriers will reject a call if a CallerID Name (CNAM) is presented. This occurs in several areas when configuring CID on the PBX using the format of 'CNAM' <CNUM>. To remove the CNAM part of CID on all external trunks, set this value to true. This WILL NOT remove CNAM when a trunk is called from an Intra-Company route. This can be done on each individual trunk in addition to globally if there are trunks where it is desirable to keep CNAM information, though most carriers ignore CNAM.";
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('BLOCK_OUTBOUND_TRUNK_CNAM',$set);

    // ASTSTOPTIMEOUT
    $opts = array();
    $set['value'] = '120';
    $set['options'] = array(0,5,10,30,60,120,300,600,1800,3600,7200,10800);
    $set['name'] = 'Waiting Period to Stop Asterisk';
    $set['description'] = "When Asterisk is stopped or restarted with the 'amportal stop/restart' commands, it does a graceful stop waiting for active channels to hangup. This sets the maximum time in seconds to wait prior to force stopping Asterisk";
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_SELECT;
    $issabelpbx_conf->define_conf_setting('ASTSTOPTIMEOUT',$set);

    // ASTSTOPPOLLINT
    $opts = array();
    $set['value'] = '2';
    $set['options'] = array(1,2,3,5,10);
    $set['name'] = 'Polling Interval for Stopping Asterisk';
    $set['description'] = "When Asterisk is stopped or restarted with the 'amportal stop/restart' commands, it does a graceful stop waiting for active channels to hangup. This sets the polling interval to check if Asterisk is shutdown and update the countdown timer.";
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_SELECT;
    $issabelpbx_conf->define_conf_setting('ASTSTOPPOLLINT',$set);

    // CID_PREPEND_REPLACE
    $set['value'] = true;
    $set['options'] = '';
    $set['name'] = 'Only Use Last CID Prepend';
    $set['description'] = "Some modules allow the CNAM to be prepended. If a previous prepend was done, the default behavior is to remove the previous prepend and only use the most recent one. Setting this to false will turn that off allowing all prepends to be 'starcked' in front of one another.";
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('CID_PREPEND_REPLACE',$set);

    // DITECH_VQA_INBOUND
    $set['value'] = '7';
    $set['options'] = array(0,1,2,3,4,5,6,7);
    $set['name'] = 'Ditech VQA Inbound Setting';
    $set['description'] = "If Ditech's VQA, Voice Quality application is installed, this setting will be used for all inbound calls. For more information 'core show application VQA' at the Asterisk CLI will show the different settings.";
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_SELECT;
    $issabelpbx_conf->define_conf_setting('DITECH_VQA_INBOUND',$set);

    // DITECH_VQA_OUTBOUND
    $set['value'] = '7';
    $set['options'] = array(0,1,2,3,4,5,6,7);
    $set['name'] = 'Ditech VQA Outbound Setting';
    $set['description'] = "If Ditech's VQA, Voice Quality application is installed, this setting will be used for all outbound calls. For more information 'core show application VQA' at the Asterisk CLI will show the different settings.";
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_SELECT;
    $issabelpbx_conf->define_conf_setting('DITECH_VQA_OUTBOUND',$set);

    // ASTCONFAPP
    $set['value'] = 'app_meetme';
    $set['options'] = array('app_meetme', 'app_confbridge');
    $set['name'] = 'Conference Room App';
    $set['description'] = 'The asterisk application to use for conferencing. If only one is compiled into asterisk, IssabelPBX will auto detect and change this value if set wrong. The app_confbridge application is considered "experimental" with known issues and does not work on Asterisk 10 where it was completely rewritten and changed from the version on 1.6 and 1.8.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['hidden'] = 0;
    $set['type'] = CONF_TYPE_SELECT;
    $issabelpbx_conf->define_conf_setting('ASTCONFAPP', $set);

    // TRUNK_RING_TIMER
    $set['value'] = '300';
    $set['name'] = 'Trunk Dial Timeout';
    $set['description'] = 'How many seconds to try a call on your trunks before giving up. This should normally be a very long time and is usually only changed if you have some sort of problematic trunks. This is the Asterisk Dial Command timeout parameter.';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_INT;
    $set['options'] = array(0,86400);
    $set['level'] = 2;
    $issabelpbx_conf->define_conf_setting('TRUNK_RING_TIMER',$set);
    $set['level'] = 0;

    // REC_POLICY
    $set['value'] = 'caller';
    $set['options'] = array('caller', 'callee');
    $set['name'] = 'Call Recording Policy';
    $set['description'] = 'Call Recording Policy used to resove the winner in a conflict between two extensions when one wants a call recorded and the other does not, if both their priorities are also the same.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_SELECT;
    $issabelpbx_conf->define_conf_setting('REC_POLICY',$set);

    // TRANSFER_CONTEXT
    $set['value'] = 'from-internal-xfer';
    $set['options'] = '';
    $set['readonly'] = 1;
    $set['hidden'] = 1;
    $set['level'] = 9;
    $set['module'] = '';
    $set['emptyok'] = 1;
    $set['name'] = 'Asterisk TRANSFER_CONTEXT Variable';
    $set['description'] = "This is the Asterisk Channel Variable TRANSFER_CONTEXT. In general it should NOT be changed unless you really know what you are doing. It is used to do create slightly different 'views' when a call is being transfered. An example is hiding the paging groups so a call isn't accidentally transfered into a page.";
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('TRANSFER_CONTEXT', $set);
    $set['hidden'] = 0;

    //
    // CATEGORY: Directory Layout
    //
    $set['category'] = 'Directory Layout';

    // AMPBIN
    $set['value'] = '/var/lib/asterisk/bin';
    $set['options'] = '';
    $set['name'] = 'IssabelPBX bin Dir';
    $set['description'] = 'Location of the IssabelPBX command line scripts.';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_DIR;
    $set['level'] = 4;
    $issabelpbx_conf->define_conf_setting('AMPBIN',$set);

    // AMPSBIN
    $set['value'] = '/usr/sbin';
    $set['options'] = '';
    $set['name'] = 'IssabelPBX sbin Dir';
    $set['description'] = 'Where (root) command line scripts are located.';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_DIR;
    $set['level'] = 4;
    $issabelpbx_conf->define_conf_setting('AMPSBIN',$set);

    // AMPWEBROOT
    $set['value'] = '/var/www/html';
    $set['options'] = '';
    $set['name'] = 'IssabelPBX Web Root Dir';
    $set['description'] = 'The path to Apache webroot (leave off trailing slash).';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_DIR;
    $set['level'] = 4;
    $issabelpbx_conf->define_conf_setting('AMPWEBROOT',$set);

    // ASTAGIDIR
    $set['value'] = '/var/lib/asterisk/agi-bin';
    $set['options'] = '';
    $set['name'] = 'Asterisk AGI Dir';
    $set['description'] = 'This is the default directory for Asterisks agi files.';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_DIR;
    $set['level'] = 4;
    $issabelpbx_conf->define_conf_setting('ASTAGIDIR',$set);

    // ASTETCDIR
    $set['value'] = '/etc/asterisk';
    $set['options'] = '';
    $set['name'] = 'Asterisk etc Dir';
    $set['description'] = 'This is the default directory for Asterisks configuration files.';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_DIR;
    $set['level'] = 4;
    $issabelpbx_conf->define_conf_setting('ASTETCDIR',$set);

    // ASTLOGDIR
    $set['value'] = '/var/log/asterisk';
    $set['options'] = '';
    $set['name'] = 'Asterisk Log Dir';
    $set['description'] = 'This is the default directory for Asterisks log files.';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_DIR;
    $set['level'] = 4;
    $issabelpbx_conf->define_conf_setting('ASTLOGDIR',$set);

    // ASTMODDIR
    $set['value'] = '/usr/lib/asterisk/modules';
    $set['options'] = '';
    $set['name'] = 'Asterisk Modules Dir';
    $set['description'] = 'This is the default directory for Asterisks modules.';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_DIR;
    $set['level'] = 4;
    $issabelpbx_conf->define_conf_setting('ASTMODDIR',$set);

    // ASTSPOOLDIR
    $set['value'] = '/var/spool/asterisk';
    $set['options'] = '';
    $set['name'] = 'Asterisk Spool Dir';
    $set['description'] = 'This is the default directory for Asterisks spool directory.';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_DIR;
    $set['level'] = 4;
    $issabelpbx_conf->define_conf_setting('ASTSPOOLDIR',$set);

    // ASTRUNDIR
    $set['value'] = '/var/run/asterisk';
    $set['options'] = '';
    $set['name'] = 'Asterisk Run Dir';
    $set['description'] = 'This is the default directory for Asterisks run files.';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_DIR;
    $set['level'] = 4;
    $issabelpbx_conf->define_conf_setting('ASTRUNDIR',$set);

    // ASTVARLIBDIR
    $set['value'] = '/var/lib/asterisk';
    $set['options'] = '';
    $set['name'] = 'Asterisk bin Dir';
    $set['description'] = 'This is the default directory for Asterisks lib files.';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_DIR;
    $set['level'] = 4;
    $issabelpbx_conf->define_conf_setting('ASTVARLIBDIR',$set);

    // AMPCGIBIN
    $set['value'] = '/var/www/cgi-bin ';
    $set['options'] = '';
    $set['name'] = 'CGI Dir';
    $set['description'] = 'The path to Apache cgi-bin dir (leave off trailing slash).';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_DIR;
    $set['level'] = 4;
    $issabelpbx_conf->define_conf_setting('AMPCGIBIN',$set);

    // MOHDIR
    $set['value'] = 'moh';
    $set['options'] = array('moh','mohmp3');
    $set['name'] = 'MoH Subdirectory';
    $set['description'] = 'This is the subdirectory for the MoH files/directories which is located in ASTVARLIBDIR. Older installation may be using mohmp3 which was the old Asterisk default and should be set to that value if the music files are located there relative to the ASTVARLIBDIR.';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_SELECT;
    $set['level'] = 4;
    $issabelpbx_conf->define_conf_setting('MOHDIR',$set);
    $set['level'] = 0;


    //
    // CATEGORY: GUI Behavior
    //
    $set['category'] = 'GUI Behavior';

    // CHECKREFERER
    $set['value'] = true;
    $set['options'] = '';
    $set['name'] = 'Check Server Referrer';
    $set['description'] = 'When set to the default value of true, all requests into IssabelPBX that might possibly add/edit/delete settings will be validated to assure the request is coming from the server. This will protect the system from CSRF (cross site request forgery) attacks. It will have the effect of preventing legitimately entering URLs that could modify settings which can be allowed by changing this field to false.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('CHECKREFERER',$set);

    // MODULEADMINWGET
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Use wget For Module Admin';
    $set['description'] = 'Module Admin normally tries to get its online information through direct file open type calls to URLs that go back to the issabel.org server. If it fails, typically because of content filters in firewalls that do not like the way PHP formats the requests, the code will fall back and try a wget to pull the information. This will often solve the problem. However, in such environment there can be a significant timeout before the failed file open calls to the URLs return and there are often 2-3 of these that occur. Setting this value will force IssabelPBX to avoid the attempt to open the URL and go straight to the wget calls.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('MODULEADMINWGET',$set);

    //SHOWLANGUAGE
    $set['value'] = true;
    $set['options'] = '';
    $set['name'] = 'Show Language setting';
    $set['description'] = 'Show Language setting on menu . Defaults = false';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('SHOWLANGUAGE', $set);

    // SERVERINTITLE
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Include Server Name in Browser';
    $set['description'] = 'Precede browser title with the server name.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('SERVERINTITLE',$set);

    // RELOADCONFIRM
    $set['value'] = true;
    $set['options'] = '';
    $set['name'] = 'Require Confirm with Apply Changes';
    $set['description'] = 'When set to false, will bypass the confirm on Reload Box.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('RELOADCONFIRM',$set);

    // BADDESTABORT
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Abort Config Gen on Bad Dest';
    $set['description'] = 'Setting either of these to true will result in retrieve_conf aborting during a reload if an extension conflict is detected or a destination is detected. It is usually better to allow the reload to go through and then correct the problem but these can be set if a more strict behavior is desired.';
    $set['emptyok'] = 0;
    $set['level'] = 3;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('BADDESTABORT',$set);
    $set['level'] = 0;

    // XTNCONFLICTABORT
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Abort Config Gen on Exten Conflict';
    $set['description'] = 'Setting either of these to true will result in retrieve_conf aborting during a reload if an extension conflict is detected or a destination is detected. It is usually better to allow the reload to go through and then correct the problem but these can be set if a more strict behavior is desired.';
    $set['emptyok'] = 0;
    $set['level'] = 3;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('XTNCONFLICTABORT',$set);
    $set['level'] = 0;

    // CUSTOMASERROR
    $set['value'] = true;
    $set['options'] = '';
    $set['name'] = 'Report Unknown Dest as Error';
    $set['description'] = 'If false, then the Destination Registry will not report unknown destinations as errors. This should be left to the default true and custom destinations should be moved into the new custom apps registry.';
    $set['emptyok'] = 0;
    $set['level'] = 2;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('CUSTOMASERROR',$set);
    $set['level'] = 0;

    // ALWAYS_SHOW_DEVICE_DETAILS
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Show all Device Setting on Add';
    $set['description'] = 'When adding a new extension/device, setting this to true will show most available device settings that are displayed when you edit the same extension/device. Otherwise, just a few basic settings are displayed.';
    $set['emptyok'] = 0;
    $set['level'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('ALWAYS_SHOW_DEVICE_DETAILS',$set);
    $set['level'] = 0;

    // USE_ISSABELPBX_MENU_CONF
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Use issabelpbx_menu.conf Configuration';
    $set['description'] = 'When set to true, the system will check for a issabelpbx_menu.conf file amongst the normal configuraiton files and if found, it will be used to define and remap the menu tabs and contents. See the template supplied with IssabelPBX for details on how to do this.';
    $set['emptyok'] = 0;
    $set['level'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('USE_ISSABELPBX_MENU_CONF',$set);
    $set['level'] = 0;


    //
    // CATEGORY: Asterisk Manager
    //
    $set['category'] = 'Asterisk Manager';

    // AMPMGRPASS
    $set['value'] = 'amp111';
    $set['options'] = '';
    $set['name'] = 'Asterisk Manager Password';
    $set['description'] = 'Password for accessing the Asterisk Manager Interface (AMI), this will be automatically updated in manager.conf.';
    $set['emptyok'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $set['level'] = 2;
    $set['readonly'] = 0;
    $issabelpbx_conf->define_conf_setting('AMPMGRPASS',$set);
    $set['level'] = 0;

    // AMPMGRUSER
    $set['value'] = 'admin';
    $set['options'] = '';
    $set['name'] = 'Asterisk Manager User';
    $set['description'] = 'Username for accessing the Asterisk Manager Interface (AMI), this will be automatically updated in manager.conf.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $set['level'] = 2;
    $issabelpbx_conf->define_conf_setting('AMPMGRUSER',$set);
    $set['level'] = 0;

    // ASTMANAGERHOST
    $set['value'] = 'localhost';
    $set['options'] = '';
    $set['name'] = 'Asterisk Manager Host';
    $set['description'] = 'Hostname for the Asterisk Manager';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_TEXT;
    $set['level'] = 2;
    $issabelpbx_conf->define_conf_setting('ASTMANAGERHOST',$set);
    $set['level'] = 0;

    // ASTMANAGERPORT
    $set['value'] = '5038';
    $set['name'] = 'Asterisk Manager Port';
    $set['description'] = 'Port for the Asterisk Manager';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_INT;
    $set['options'] = array(1024,65535);
    $set['level'] = 2;
    $issabelpbx_conf->define_conf_setting('ASTMANAGERPORT',$set);
    $set['level'] = 0;

    // ASTMANAGERPROXYPORT
    $set['value'] = '';
    $set['name'] = 'Asterisk Manager Proxy Port';
    $set['description'] = 'Optional port for an Asterisk Manager Proxy';
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_INT;
    $set['emptyok'] = 1;
    $set['options'] = array(1024,65535);
    $set['level'] = 2;
    $issabelpbx_conf->define_conf_setting('ASTMANAGERPROXYPORT',$set);
    $set['level'] = 0;

    // ASTMGRWRITETIMEOUT
    $set['value'] = '5000';
    $set['name'] = 'Asterisk Manager Write Timeout';
    $set['description'] =
        'Timeout, im ms, for write timeouts for cases where Asterisk disconnects frequently';
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_INT;
    $set['emptyok'] = 1;
    $set['options'] = array(100,100000);
    $set['level'] = 2;
    $issabelpbx_conf->define_conf_setting('ASTMGRWRITETIMEOUT',$set);
    $set['level'] = 0;

    //
    // CATEGORY: Developer and Customization
    //
    $set['category'] = 'Developer and Customization';
    $set['level'] = 2;

    // IPBXDBUGFILE
    $set['value'] = $amp_conf['ASTLOGDIR'] . '/issabelpbx_dbug';
    $set['options'] = '';
    $set['name'] = 'Debug File';
    $set['description'] = 'Full path and name of IssabelPBX debug file. Used by the dbug() function by developers.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('IPBXDBUGFILE',$set);

    // IPBXDBUGDISABLE
    $set['value'] = true;
    $set['options'] = '';
    $set['name'] = 'Disable IssabelPBX dbug Logging';
    $set['description'] = 'Set to true to stop all dbug() calls from writing to the Debug File (IPBXDBUGFILE)';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('IPBXDBUGDISABLE',$set);

    // DIE_ISSABELPBX_VERBOSE
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Provide Verbose Tracebacks';
    $set['description'] = 'Provides a very verbose traceback when die_issabelpbx() is called including extensive object details if present in the traceback.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('DIE_ISSABELPBX_VERBOSE',$set);



    // DEVEL
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Developer Mode';
    $set['description'] = 'This enables several debug features geared towards developers, including some page load timing information, some debug information in Module Admin, use of original CSS files and other future capabilities will be enabled.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('DEVEL',$set);

    // USE_PACKAGED_JS
    $set['value'] = true;
    $set['options'] = '';
    $set['name'] = 'Use Packaged Javascript Library ';
    $set['description'] = 'IssabelPBX packages several javascript libraries and components into a compressed file called libissabelpbx.javascript.js. By default this will be loaded instead of the individual uncompressed libraries. Setting this to false will force IssabelPBX to load all the libraries as individual uncompressed files. This is useful during development and debugging.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('USE_PACKAGED_JS',$set);

    // FORCE_JS_CSS_IMG_DOWNLOAD
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Always Download Web Assets';
    $set['description'] = 'IssabelPBX appends versioning tags on the CSS and javascript files and some of the main logo images. The versioning will help force browsers to load new versions of the files when module versions are upgraded. Setting this value to true will try to force these to be loaded to the browser every page load by appending an additional timestamp in the version information. This is useful during development and debugging where changes are being made to javascript and CSS files.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('FORCE_JS_CSS_IMG_DOWNLOAD',$set);

    // DEVELRELOAD
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Leave Reload Bar Up';
    $set['description'] = "Forces the 'Apply Configuration Changes' reload bar to always be present even when not necessary.";
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('DEVELRELOAD',$set);

    // PRE_RELOAD
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'PRE_RELOAD Script';
    $set['description'] = 'Optional script to run just prior to doing an extension reload to Asterisk through the manager after pressing Apply Configuration Changes in the GUI.';
    $set['emptyok'] = 1;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('PRE_RELOAD',$set);

    // POST_RELOAD
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'POST_RELOAD Script';
    $set['description'] = 'Automatically execute a script after applying changes in the AMP admin. Set POST_RELOAD to the script you wish to execute after applying changes. If POST_RELOAD_DEBUG=true, you will see the output of the script in the web page.';
    $set['emptyok'] = 1;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('POST_RELOAD',$set);

    // POST_RELOAD_DEBUG
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'POST_RELOAD Debug Mode';
    $set['description'] = 'Display debug output for script used if POST_RELOAD is used.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('POST_RELOAD_DEBUG',$set);

    // AMPLOCALBIN
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'AMPLOCALBIN Dir for retrieve_conf';
    $set['description'] = 'If this directory is defined, retrieve_conf will check for a file called <i>retrieve_conf_post_custom</i> and if that file exists, it will be included after other processing thus having full access to the current environment for additional customization.';
    $set['emptyok'] = 1;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_DIR;
    $issabelpbx_conf->define_conf_setting('AMPLOCALBIN',$set);

    // DISABLE_CSS_AUTOGEN
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Disable Mainstyle CSS Compression';
    $set['description'] = 'Stops the automatic generation of a stripped CSS file that replaces the primary sheet, usually mainstyle.css.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('DISABLE_CSS_AUTOGEN',$set);

    // MODULEADMIN_SKIP_CACHE
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Disable Module Admin Caching';
    $set['description'] = 'Module Admin caches a copy of the online XML document that describes what is available on the server. Subsequent online update checks will use the cached information if it is less than 5 minutes old. To bypass the cache and force it to go to the server each time, set this to True. This should normally be false but can be helpful during testing.';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('MODULEADMIN_SKIP_CACHE',$set);

    // DISPLAY_MONITOR_TRUNK_FAILURES_FIELD
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Display Monitor Trunk Failures Option';
    $set['description'] = 'Setting this to true will expose the "Monitor Trunk Failures" field on the Trunks page. This field allows for a custom AGI script to be called upon a trunk failure. This is an advanced field requiring a custom script to be properly written and installed. Existing trunk page entries will not be affected if this is set to false but if the settings are changed on those pages the field will go away.';
    $set['emptyok'] = 0;
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('DISPLAY_MONITOR_TRUNK_FAILURES_FIELD',$set);

    //
    // CATEGORY: Flash Operator Panel
    //
    $set['category'] = 'Flash Operator Panel';
    $set['level'] = 0;

    // FOPWEBROOT also used by FOP2 and iSymphony modules
    // FOPWEBROOT
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'FOP Web Root Dir';
    $set['description'] = 'Path to the Flash Operator Panel webroot or other modules providing such functionality (leave off trailing slash).';
    $set['emptyok'] = 1;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_DIR;
    $set['level'] = 4;
    $issabelpbx_conf->define_conf_setting('FOPWEBROOT',$set);
    $set['level'] = 0;


    //
    // CATEGORY: Remote CDR Database
    //
    $set['category'] = 'Remote CDR Database';
    $set['level'] = 3;

    // CDRDBHOST
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'Remote CDR DB Host';
    $set['description'] = 'DO NOT set this unless you know what you are doing. Only used if you do not use the default values provided by IssabelPBX.<br>Hostname of db server if not the same as AMPDBHOST.';
    $set['emptyok'] = 1;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('CDRDBHOST',$set);

    // CDRDBNAME
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'Remote CDR DB Name';
    $set['description'] = 'DO NOT set this unless you know what you are doing. Only used if you do not use the default values provided by IssabelPBX.<br>Name of database used for cdr records.';
    $set['emptyok'] = 1;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('CDRDBNAME',$set);

    // CDRDBPASS
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'Remote CDR DB Password';
    $set['description'] = 'DO NOT set this unless you know what you are doing. Only used if you do not use the default values provided by IssabelPBX.<br>Password for connecting to db if its not the same as AMPDBPASS.';
    $set['emptyok'] = 1;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('CDRDBPASS',$set);

    // CDRDBPORT
    $set['value'] = '';
    $set['options'] = array(1024,65536);
    $set['name'] = 'Remote CDR DB Port';
    $set['description'] = 'DO NOT set this unless you know what you are doing. Only used if you do not use the default values provided by IssabelPBX.<br>Port number for db host.';
    $set['emptyok'] = 1;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_INT;
    $issabelpbx_conf->define_conf_setting('CDRDBPORT',$set);

    // CDRDBTABLENAME
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'Remote CDR DB Table';
    $set['description'] = 'DO NOT set this unless you know what you are doing. Only used if you do not use the default values provided by IssabelPBX. Name of the table in the db where the cdr is stored. cdr is default.';
    $set['emptyok'] = 1;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('CDRDBTABLENAME',$set);

    // CDRDBTYPE
    $set['value'] = '';
    $set['description'] = 'DO NOT set this unless you know what you are doing. Only used if you do not use the default values provided by IssabelPBX. Defaults to your configured AMDBENGINE.';
    $set['name'] = 'Remote CDR DB Type';
    $set['emptyok'] = 1;
    $set['options'] = ',mysql,postgres';
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_SELECT;
    $issabelpbx_conf->define_conf_setting('CDRDBTYPE',$set);

    // CDRDBUSER
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'Remote CDR DB User';
    $set['description'] = 'DO NOT set this unless you know what you are doing. Only used if you do not use the default values provided by IssabelPBX. Username to connect to db with if it is not the same as AMPDBUSER.';
    $set['emptyok'] = 1;
    $set['readonly'] = 1;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('CDRDBUSER',$set);


    //
    // CATEGORY: Styling and Logos
    //
    $set['category'] = 'Styling and Logos';
    $set['level'] = 1;

    // BRAND_IMAGE_FAVICON
    $set['value'] = 'images/favicon.ico';
    $set['options'] = '';
    $set['name'] = 'Favicon';
    $set['description'] = 'Favicon';
    $set['readonly'] = 1;
    $set['hidden'] = 1;
    $set['sortorder'] = 40;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 0;
    $issabelpbx_conf->define_conf_setting('BRAND_IMAGE_FAVICON', $set);
    $set['hidden'] = 0;

    // BRAND_TITLE
    $set['value'] = 'IssabelPBX Administration';
    $set['options'] = '';
    $set['name'] = 'Page Title';
    $set['description'] = 'HTML title of all pages';
    $set['readonly'] = 1;
    $set['hidden'] = 1;
    $set['sortorder'] = 40;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 0;
    $issabelpbx_conf->define_conf_setting('BRAND_TITLE', $set);
    $set['hidden'] = 0;

    // BRAND_IMAGE_TANGO_LEFT
    $set['value'] = 'images/tango.png';
    $set['options'] = '';
    $set['name'] = 'Image: Left Upper';
    $set['description'] = 'Left upper logo.  Path is relative to admin.';
    $set['readonly'] = 1;
    $set['sortorder'] = 40;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 0;
    $issabelpbx_conf->define_conf_setting('BRAND_IMAGE_TANGO_LEFT',$set);

    // BRAND_IMAGE_ISSABELPBX_FOOT
    $set['value'] = 'images/issabelpbx_small.png';
    $set['options'] = '';
    $set['name'] = 'Image: Footer';
    $set['description'] = 'Logo in footer.  Path is relative to admin.';
    $set['readonly'] = 1;
    $set['sortorder'] = 50;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 1;
    $issabelpbx_conf->define_conf_setting('BRAND_IMAGE_ISSABELPBX_FOOT',$set);

    // BRAND_IMAGE_SPONSOR_FOOT
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'Image: Footer';
    $set['description'] = 'Logo in footer.  Path is relative to admin.';
    $set['readonly'] = 1;
    $set['sortorder'] = 50;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 1;
    $issabelpbx_conf->define_conf_setting('BRAND_IMAGE_SPONSOR_FOOT',$set);

    // BRAND_ISSABELPBX_ALT_LEFT
    $set['value'] = 'IssabelPBX';
    $set['options'] = '';
    $set['name'] = 'Alt for Left Logo';
    $set['description'] = 'alt attribute to use in place of image and title hover value. Defaults to IssabelPBX';
    $set['readonly'] = 1;
    $set['sortorder'] = 70;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 1;
    $issabelpbx_conf->define_conf_setting('BRAND_ISSABELPBX_ALT_LEFT',$set);

    // BRAND_ISSABELPBX_ALT_FOOT
    $set['value'] = 'IssabelPBX&reg;';
    $set['options'] = '';
    $set['name'] = 'Alt for Footer Logo';
    $set['description'] = 'alt attribute to use in place of image and title hover value. Defaults to IssabelPBX';
    $set['readonly'] = 1;
    $set['sortorder'] = 90;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 1;
    $issabelpbx_conf->define_conf_setting('BRAND_ISSABELPBX_ALT_FOOT',$set);

    // BRAND_SPONSOR_ALT_FOOT
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'Alt for Footer Logo';
    $set['description'] = 'alt attribute to use in place of image and title hover value. Defaults to IssabelPBX';
    $set['readonly'] = 1;
    $set['sortorder'] = 90;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 1;
    $issabelpbx_conf->define_conf_setting('BRAND_SPONSOR_ALT_FOOT',$set);

    // BRAND_IMAGE_ISSABELPBX_LINK_LEFT
    $set['value'] = 'http://www.issabel.org';
    $set['options'] = '';
    $set['name'] = 'Link for Left Logo';
    $set['description'] = 'link to follow when clicking on logo, defaults to http://www.issabel.org';
    $set['readonly'] = 1;
    $set['sortorder'] = 100;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 1;
    $issabelpbx_conf->define_conf_setting('BRAND_IMAGE_ISSABELPBX_LINK_LEFT',$set);

    // BRAND_IMAGE_ISSABELPBX_LINK_FOOT
    $set['value'] = 'http://www.issabel.org';
    $set['options'] = '';
    $set['name'] = 'Link for Footer Logo';
    $set['description'] = 'link to follow when clicking on logo, defaults to http://www.issabel.org';
    $set['readonly'] = 1;
    $set['sortorder'] = 120;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 1;
    $issabelpbx_conf->define_conf_setting('BRAND_IMAGE_ISSABELPBX_LINK_FOOT',$set);

    // BRAND_IMAGE_SPONSOR_LINK_FOOT
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'Link for Sponsor Footer Logo';
    $set['description'] = 'link to follow when clicking on sponsor logo';
    $set['readonly'] = 1;
    $set['sortorder'] = 120;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 1;
    $issabelpbx_conf->define_conf_setting('BRAND_IMAGE_SPONSOR_LINK_FOOT',$set);

    // BRAND_CSS_ALT_MAINSTYLE
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'Primary CSS Stylesheet';
    $set['description'] = 'Set this to replace the default mainstyle.css style sheet with your own, relative to admin.';
    $set['readonly'] = 1;
    $set['sortorder'] = 160;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 1;
    $issabelpbx_conf->define_conf_setting('BRAND_CSS_ALT_MAINSTYLE',$set);

    // BRAND_CSS_ALT_POPOVER
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'Primary CSS Popover Stylesheet Addtion';
    $set['description'] = 'Set this to replace the default popover.css style sheet with your own, relative to admin.';
    $set['readonly'] = 1;
    $set['sortorder'] = 162;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 1;
    $issabelpbx_conf->define_conf_setting('BRAND_CSS_ALT_POPOVER',$set);

    // BRAND_CSS_CUSTOM
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'Optional Additional CSS Stylesheet';
    $set['description'] = 'Optional custom CSS style sheet included after the primary one and any module specific ones are loaded, relative to admin.';
    $set['readonly'] = 1;
    $set['sortorder'] = 170;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 1;
    $issabelpbx_conf->define_conf_setting('BRAND_CSS_CUSTOM',$set);

    // VIEW_ISSABELPBX_ADMIN
    $set['value'] = 'views/issabelpbx_admin.php';
    $set['options'] = '';
    $set['name'] = 'View: issabelpbx_admin.php';
    $set['description'] = 'issabelpbx_admin.php view. This should never be changed except for very advanced layout changes.';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 180;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_ISSABELPBX_ADMIN',$set);
    $set['hidden'] = 0;

    // VIEW_ISSABELPBX
    $set['value'] = 'views/issabelpbx.php';
    $set['options'] = '';
    $set['name'] = 'View: issabelpbx.php';
    $set['description'] = 'issabelpbx.php view. This should never be changed except for very advanced layout changes.';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 190;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_ISSABELPBX',$set);
    $set['hidden'] = 0;

    // VIEW_ISSABELPBX_RELOAD
    $set['value'] = 'views/issabelpbx_reload.php';
    $set['options'] = '';
    $set['name'] = 'View: issabelpbx_reload.php';
    $set['description'] = 'issabelpbx_reload.php view. This should never be changed except for very advanced layout changes.';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 200;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_ISSABELPBX_RELOAD',$set);
    $set['hidden'] = 0;

    // VIEW_ISSABELPBX_RELOADBAR
    $set['value'] = 'views/issabelpbx_reloadbar.php';
    $set['options'] = '';
    $set['name'] = 'View: issabelpbx_reloadbar.php';
    $set['description'] = 'issabelpbx_reloadbar.php view. This should never be changed except for very advanced layout changes.';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 210;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_ISSABELPBX_RELOADBAR',$set);
    $set['hidden'] = 0;

    // VIEW_WELCOME
    $set['value'] = 'views/welcome.php';
    $set['options'] = '';
    $set['name'] = 'View: welcome.php';
    $set['description'] = 'welcome.php view. This should never be changed except for very advanced layout changes.';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 220;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_WELCOME',$set);
    $set['hidden'] = 0;

    // VIEW_WELCOME_NONMANAGER
    $set['value'] = 'views/welcome_nomanager.php';
    $set['options'] = '';
    $set['name'] = 'View: welcome_nomanager.php';
    $set['description'] = 'welcome_nomanager.php view. This should never be changed except for very advanced layout changes.';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 230;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_WELCOME_NONMANAGER',$set);
    $set['hidden'] = 0;

    // VIEW_MENUITEM_DISABLED
    $set['value'] = 'views/menuitem_disabled.php';
    $set['options'] = '';
    $set['name'] = 'View: menuitem_disabled.php';
    $set['description'] = 'menuitem_disabled.php view. This should never be changed except for very advanced layout changes.';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 240;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_MENUITEM_DISABLED',$set);
    $set['hidden'] = 0;

    // VIEW_NOACCESS
    $set['value'] = 'views/noaccess.php';
    $set['options'] = '';
    $set['name'] = 'View: noaccess.php';
    $set['description'] = 'noaccess.php view. This should never be changed except for very advanced layout changes.';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 250;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_NOACCESS',$set);
    $set['hidden'] = 0;

    // VIEW_UNAUTHORIZED
    $set['value'] = 'views/unauthorized.php';
    $set['options'] = '';
    $set['name'] = 'View: unauthorized.php';
    $set['description'] = 'unauthorized.php view. This should never be changed except for very advanced layout changes.';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 260;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_UNAUTHORIZED',$set);
    $set['hidden'] = 0;

    // VIEW_BAD_REFFERER
    $set['value'] = 'views/bad_refferer.php';
    $set['options'] = '';
    $set['name'] = 'View: bad_refferer.php';
    $set['description'] = 'bad_refferer.php view. This should never be changed except for very advanced layout changes.';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 270;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_BAD_REFFERER',$set);
    $set['hidden'] = 0;

    // VIEW_LOGGEDOUT
    $set['value'] = 'views/loggedout.php';
    $set['options'] = '';
    $set['name'] = 'View: loggedout.php';
    $set['description'] = 'loggedout.php view. This should never be changed except for very advanced layout changes.';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 280;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_LOGGEDOUT',$set);
    $set['hidden'] = 0;

    // VIEW_PANEL
    $set['value'] = 'views/panel.php';
    $set['options'] = '';
    $set['name'] = 'View: panel.php';
    $set['description'] = 'panel.php view. This should never be changed except for very advanced layout changes.';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 290;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_PANEL',$set);
    $set['hidden'] = 0;

    // VIEW_REPORTS
    $set['value'] = 'views/reports.php';
    $set['options'] = '';
    $set['name'] = 'View: reports.php';
    $set['description'] = 'reports.php view. This should never be changed except for very advanced layout changes.';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 300;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_REPORTS',$set);
    $set['hidden'] = 0;

    // VIEW_MENU
    $set['value']    = 'views/menu.php';
    $set['options'] = '';
    $set['name'] = 'View: menu.php';
    $set['description'] = 'menu.php view. This should never be changed except for very advanced layout changes';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 310;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_MENU', $set);
    $set['hidden'] = 0;

    // VIEW_BETA_NOTICE
    $set['value']    = 'views/beta_notice.php';
    $set['options'] = '';
    $set['name'] = 'View: beta_notice.php';
    $set['description'] = 'beta_notice.php view. This should never be changed except for very advanced layout changes';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 312;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_BETA_NOTICE', $set);
    $set['hidden'] = 0;

    // VIEW_OBE
    $set['value']    = 'views/obe.php';
    $set['options'] = '';
    $set['name'] = 'View: obe.php';
    $set['description'] = 'obe.php view. This should never be changed except for very advanced layout changes';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 310;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_OBE', $set);
    $set['hidden'] = 0;

    // JQUERY_CSS
    $set['value']    = 'assets/css/jquery-ui.css';
    $set['options'] = '';
    $set['name'] = 'jQuery UI css';
    $set['description'] = 'css file for jquery ui';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 320;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('JQUERY_CSS', $set);
    $set['hidden'] = 0;

    // VIEW_LOGIN
    $set['value']    = 'views/login.php';
    $set['options'] = '';
    $set['name'] = 'View: login.php';
    $set['description'] = 'login.php view. This should never be changed except for very advanced layout changes';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 330;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_LOGIN', $set);
    $set['hidden'] = 0;

    // VIEW_HEADER
    $set['value']    = 'views/header.php';
    $set['options'] = '';
    $set['name'] = 'View: header.php';
    $set['description'] = 'header.php view. This should never be changed except for very advanced layout changes';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 340;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_HEADER', $set);
    $set['hidden'] = 0;

    // VIEW_FOOTER
    $set['value']    = 'views/footer.php';
    $set['options'] = '';
    $set['name'] = 'View: issabelpbx.php';
    $set['description'] = 'footer.php view. This should never be changed except for very advanced layout changes';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 350;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_FOOTER', $set);
    $set['hidden'] = 0;

    // VIEW_FOOTER_CONTENT
    $set['value']    = 'views/footer_content.php';
    $set['options'] = '';
    $set['name'] = 'View: footer_content.php';
    $set['description'] = 'footer_content.php view. This should never be changed except for very advanced layout changes';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 360;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_FOOTER_CONTENT', $set);
    $set['hidden'] = 0;

    // VIEW_POPOVER_JS
    $set['value']    = 'views/popover_js.php';
    $set['options'] = '';
    $set['name'] = 'View: popover_js.php';
    $set['description'] = 'popover_js.php view. This should never be changed except for very advanced layout changes';
    $set['readonly'] = 1;
    $set['emptyok'] = 0;
    $set['hidden'] = 1;
    $set['sortorder'] = 355;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('VIEW_POPOVER_JS', $set);
    $set['hidden'] = 0;

    // BRAND_ALT_JS
    $set['value']    = '';
    $set['options'] = '';
    $set['name'] = 'Alternate JS';
    $set['description'] = 'Alternate JS file, to supplement legacy.script.js';
    $set['readonly'] = 1;
    $set['emptyok'] = 1;
    $set['hidden'] = 1;
    $set['sortorder'] = 360;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('BRAND_ALT_JS', $set);
    $set['hidden'] = 0;


    //
    // CATEGORY: Device Setting Defaults
    //
    $set['category'] = 'Device Settings';
    $set['level'] = 0;

    // ALWAYS_SHOW_DEVICE_DETAILS
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Show all Device Setting on Add';
    $set['description'] = 'When adding a new extension/device, setting this to true will show most available device settings that are displayed when you edit the same extension/device. Otherwise, just a few basic settings are displayed.';
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $set['emptyok'] = 0;
    $set['sortorder'] = 10;
    $issabelpbx_conf->define_conf_setting('ALWAYS_SHOW_DEVICE_DETAILS',$set);

    // DEVICE_STRONG_SECRETS
    $set['value'] = true;
    $set['options'] = '';
    $set['name'] = 'Require Strong Secrets';
    $set['description'] = 'Requires a strong secret on SIP and IAX devices requiring at least two numeric and non-numeric characters and 6 or more characters. This can be disabled if using devices that can not meet these needs, or you prefer to put other constraints including more rigid constraints that this rule actually considers weak when it may not be.';
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $set['emptyok'] = 0;
    $set['sortorder'] = 12;
    $issabelpbx_conf->define_conf_setting('DEVICE_STRONG_SECRETS',$set);

    // DEVICE_REMOVE_MAILBOX
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Remove mailbox Setting when no Voicemail';
    $set['description'] = 'If set to true, any fixed device associated with a user that has no voicemail configured will have the "mailbox=" setting removed in the generated technology configuration file such as sip_additional.conf. This will not affect the value in the GUI.';
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_BOOL;
    $set['emptyok'] = 0;
    $set['sortorder'] = 15;
    $issabelpbx_conf->define_conf_setting('DEVICE_REMOVE_MAILBOX',$set);

    // DEVICE_SIP_CANREINVITE
    $set['value'] = 'no';
    $set['options'] = array('no', 'yes', 'nonat', 'update');
    $set['name'] = 'SIP canrenivite (directmedia)';
    $set['description'] = 'Default setting for SIP canreinvite (same as directmedia). See Asterisk documentation for details.';
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_SELECT;
    $set['emptyok'] = 0;
    $set['sortorder'] = 20;
    $issabelpbx_conf->define_conf_setting('DEVICE_SIP_CANREINVITE',$set);

    // DEVICE_SIP_TRUSTRPID
    $set['value'] = 'yes';
    $set['options'] = array('no', 'yes');
    $set['name'] = 'SIP trustrpid';
    $set['description'] = 'Default setting for SIP trustrpid. See Asterisk documentation for details.';
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_SELECT;
    $set['emptyok'] = 0;
    $set['sortorder'] = 30;
    $issabelpbx_conf->define_conf_setting('DEVICE_SIP_TRUSTRPID',$set);

    // DEVICE_SIP_SENDRPID
    $set['value'] = 'no';
    $set['options'] = array('no', 'yes', 'pai');
    $set['name'] = 'SIP sendrpid';
    $set['description'] = "Default setting for SIP sendrpid. A value of 'yes' is equivalent to 'rpid' and will send the 'Remote-Party-ID' header. A value of 'pai' is only valid starting with Asterisk 1.8 and will send the 'P-Asserted-Identity' header. See Asterisk documentation for details.";
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_SELECT;
    $set['emptyok'] = 0;
    $set['sortorder'] = 40;
    $issabelpbx_conf->define_conf_setting('DEVICE_SIP_SENDRPID',$set);

    // DEVICE_SIP_NAT
    $set['value'] = 'no';
    $set['options'] = array('no', 'yes', 'never', 'route');
    $set['name'] = 'SIP nat';
    $set['description'] = "Default setting for SIP nat. A 'yes' will attempt to handle nat, also works for local (uses the network ports and address instead of the reported ports), 'no' follows the protocol, 'never' tries to block it, no RFC3581, 'route' ignores the rport information. See Asterisk documentation for details.";
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_SELECT;
    $set['emptyok'] = 0;
    $set['sortorder'] = 50;
    $issabelpbx_conf->define_conf_setting('DEVICE_SIP_NAT',$set);

    // DEVICE_SIP_ENCRYPTION
    $set['value'] = 'no';
    $set['options'] = array('no', 'yes');
    $set['name'] = 'SIP encryption';
    $set['description'] = "Default setting for SIP encryption. Whether to offer SRTP encrypted media (and only SRTP encrypted media) on outgoing calls to a peer. Calls will fail with HANGUPCAUSE=58 if the peer does not support SRTP. See Asterisk documentation for details.";
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_SELECT;
    $set['emptyok'] = 0;
    $set['sortorder'] = 60;
    $issabelpbx_conf->define_conf_setting('DEVICE_SIP_ENCRYPTION',$set);

    // DEVICE_SIP_QUALIFYFREQ
    $set['value'] = 60;
    $set['options'] = array(15, 86400);
    $set['name'] = 'SIP qualifyfreq';
    $set['description'] = "Default setting for SIP qualifyfreq. Only valid for Asterisk 1.6 and above. Frequency that 'qualify' OPTIONS messages will be sent to the device. Can help to keep NAT holes open but not dependable for remote client firewalls. See Asterisk documentation for details.";
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_INT;
    $set['emptyok'] = 0;
    $set['sortorder'] = 70;
    $issabelpbx_conf->define_conf_setting('DEVICE_SIP_QUALIFYFREQ',$set);

    // DEVICE_QUALIFY
    $set['value'] = 'yes';
    $set['options'] = '';
    $set['name'] = 'SIP and IAX qualify';
    $set['description'] = "Default setting for SIP and IAX qualify. Whether to send periodic OPTIONS messages (for SIP) or otherwise monitor the channel, and at what point to consider the channel unavailable. A value of 'yes' is equivalent to 2000, time in msec. Can help to keep NAT holes open with SIP but not dependable for remote client firewalls. See Asterisk documentation for details.";
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 0;
    $set['sortorder'] = 80;
    $issabelpbx_conf->define_conf_setting('DEVICE_QUALIFY',$set);

    // DEVICE_DISALLOW
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'SIP and IAX disallow';
    $set['description'] = "Default setting for SIP and IAX disallow (for codecs). Codecs to disallow, can help to reset from the general settings by setting a value of 'all' and then specifically including allowed codecs with the 'allow' directive. Values van be separated with '&' e.g. 'g729&g722'. See Asterisk documentation for details.";
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 1;
    $set['sortorder'] = 90;
    $issabelpbx_conf->define_conf_setting('DEVICE_DISALLOW',$set);

    // DEVICE_ALLOW
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'SIP and IAX allow';
    $set['description'] = "Default setting for SIP and IAX allow (for codecs). Codecs to allow in addition to those set in general settings unless explicitly 'disallowed' for the device. Values van be separated with '&' e.g. 'ulaw&g729&g729' where the preference order is preserved. See Asterisk documentation for details.";
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 1;
    $set['sortorder'] = 90;
    $issabelpbx_conf->define_conf_setting('DEVICE_ALLOW',$set);

    // DEVICE_CALLGROUP
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'SIP and DAHDi callgroup';
    $set['description'] = "Default setting for SIP, DAHDi (and Zap) callgroup. Callgroup(s) that the device is part of, can be one or more callgroups, e.g. '1,3-5' would be in groups 1,3,4,5. See Asterisk documentation for details.";
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 1;
    $set['sortorder'] = 100;
    $issabelpbx_conf->define_conf_setting('DEVICE_CALLGROUP',$set);

    // DEVICE_PICKUPGROUP
    $set['value'] = '';
    $set['options'] = '';
    $set['name'] = 'SIP and DAHDi pickupgroup';
    $set['description'] = "Default setting for SIP, DAHDi (and Zap) pickupgroup. Pickupgroups(s) that the device can pickup calls from, can be one or more groups, e.g. '1,3-5' would be in groups 1,3,4,5. Device does not have to be in a group to be able to pickup calls from that group. See Asterisk documentation for details.";
    $set['readonly'] = 0;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 1;
    $set['sortorder'] = 110;
    $issabelpbx_conf->define_conf_setting('DEVICE_PICKUPGROUP',$set);


    //
    // CATEGORY: Internal Use
    //
    $set['category'] = 'Internal Use';
    $set['level'] = 10;


    // SIPUSERAGENT
    //
    $set['value'] = 'IPBX';
    $set['options'] = '';
    $set['name'] = 'SIP User Agent';
    $set['description'] = 'User Agent prefix';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['hidden'] = 1;
    $set['type'] = CONF_TYPE_TEXT;
    $issabelpbx_conf->define_conf_setting('SIPUSERAGENT',$set);
    $set['hidden'] = 0;

    // MODULE_REPO
    $set['value'] = 'http://cloud.issabel.org,http://cloud2.issabel.org';
    $set['options'] = '';
    $set['name'] = 'Repo Server';
    $set['description'] = 'repo server';
    $set['readonly'] = 1;
    $set['hidden'] = 1;
    $set['type'] = CONF_TYPE_TEXT;
    $set['emptyok'] = 0;
    $issabelpbx_conf->define_conf_setting('MODULE_REPO',$set);
    $set['hidden'] = 0;

    // NOTICE_BROWSER_STATS
    $set['value'] = false;
    $set['options'] = '';
    $set['name'] = 'Browser Stats Notice';
    $set['description'] = 'Internal use to track if notice has been given that anonyous browser stats are being collected.';
    $set['emptyok'] = 0;
    $set['readonly'] = 1;
    $set['hidden'] = 1;
    $set['type'] = CONF_TYPE_BOOL;
    $issabelpbx_conf->define_conf_setting('NOTICE_BROWSER_STATS',$set);
    $set['hidden'] = 0;

    //mainstyle_css_generated
    $set['value'] = (isset($amp_conf['mainstyle_css_generated']) && $amp_conf['mainstyle_css_generated']) ? $amp_conf['mainstyle_css_generated'] : '';
    $set['description'] = 'internal use';
    $set['type'] = CONF_TYPE_TEXT;
    $set['defaultval'] = '';
    $set['name'] = 'Compressed Copy of Main CSS';
    $set['readonly'] = 1;
    $set['hidden'] = 1;
    $set['emptyok'] = 1;
    $issabelpbx_conf->define_conf_setting('mainstyle_css_generated', $set);
    $set['hidden'] = 0;

    // The following settings are used in various modules prior to 2.9. If they are found in amportal.conf then we
    // retain their values until the individual modules are updated and their install scripts run where a full
    // configuration (descriptions, defaults, etc.) will be provided and maintained. This provides just enough to
    // carry the setting through the migration since most upgrades will run framework or install_amp followed by the
    // module install scripts.
    //
    $module_migrate['AMPPLAYKEY'] = CONF_TYPE_TEXT;
    $module_migrate['AMPBACKUPEMAILFROM'] = CONF_TYPE_TEXT;
    $module_migrate['AMPBACKUPSUDO'] = CONF_TYPE_BOOL;
    $module_migrate['USEQUEUESTATE'] = CONF_TYPE_BOOL;
    $module_migrate['DASHBOARD_INFO_UPDATE_TIME'] = CONF_TYPE_INT;
    $module_migrate['DASHBOARD_STATS_UPDATE_TIME'] = CONF_TYPE_INT;
    $module_migrate['SSHPORT'] = CONF_TYPE_INT;
    $module_migrate['MAXCALLS'] = CONF_TYPE_INT;
    $module_migrate['AMPMPG123'] = CONF_TYPE_BOOL;

    $mod_set['value'] = '';
    $mod_set['defaultval'] = '';
    $mod_set['readonly'] = 0;
    $mod_set['hidden'] = 1;
    $mod_set['level'] = 10;
    $mod_set['module'] = '';
    $mod_set['category'] = 'Under Migration';
    $mod_set['emptyok'] = 1;
    $mod_set['description'] = 'This setting is being migrated and will be initialized by its module install script on upgrade.';
    foreach ($module_migrate as $setting => $type) {
        if (isset($amp_conf[$setting])  && !$issabelpbx_conf->conf_setting_exists($setting)) {
            $val = $amp_conf[$setting];

            // since this came from a conf file, change any 'false' that will otherwise turn to true
            //
            if ($type == CONF_TYPE_BOOL) switch (strtolower($val)) {
case 'false':
case 'no':
case 'off':
    $val = false;
    break;
            }
            $mod_set['value'] = $val;
            $mod_set['type'] = $type;
            $issabelpbx_conf->define_conf_setting($setting,$mod_set);
        }
    }

    if ($commit_to_db) {
        $issabelpbx_conf->commit_conf_settings();
    }

    //Make sure we don't set the value again because we dont need to do that
    //also to prevent against loops
    if($issabelpbx_conf->get_conf_setting('CRONMAN_UPDATES_CHECK') && file_exists($issabelpbx_conf->get_conf_setting("AMPWEBROOT").'/admin/libraries/cronmanager.class.php')) {
        if(!class_exists('cronmanager')) {
            include($amp_conf["AMPWEBROOT"].'/admin/libraries/cronmanager.class.php');
        }
        global $db;
        $cm =& cronmanager::create($db);
        $cm->enable_updates();
    }

}
