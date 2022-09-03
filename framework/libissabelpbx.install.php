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
        if (($file!=".") && ($file!="..") && ($file != "CVS") && ($file != ".svn") && (!preg_match("/\.git/",$file))) {
            $source = $dirsourceparent.$dirsource."/".$file;
            $destination =  $dirdest.$dirsource."/".$file;

            if ($dirsource == "" && $file == "moh" && !$install_moh) {
                // skip to the next dir
                continue;
            }

            if(basename($dirsourceparent)=='framework' || basename($dirsourceparent)=='amp_conf' || basename($dirsourceparent)=='core') {
                $destination=str_replace("/htdocs/",trim($amp_conf["AMPWEBROOT"])."/",$destination);
                $pos = strpos($destination,"/sbin");
                $destination=substr(str_replace("/sbin",trim($amp_conf["AMPSBIN"]),$destination),$pos);
                $pos = 0;
                $pos = strpos($destination,"/bin");
                $destination=substr(str_replace("/bin",trim($amp_conf["AMPBIN"]),$destination),$pos);
                $pos = 0;
                $pos = strpos($destination,"/agi-bin");
                $destination=substr(str_replace("/agi-bin",trim($asterisk_conf["astagidir"]),$destination),$pos);
                $pos = 0;
                if(basename($dirsourceparent)=='framework') {
                    $pos = strpos($destination,"/sounds");
                    $destination=substr(str_replace("/sounds",trim($asterisk_conf["astdatadir"])."/sounds",$destination),$pos);
                    $pos = 0;
                }
            }
            // the following are configurable in asterisk.conf
            $destination=str_replace("/astetc",trim($asterisk_conf["astetcdir"]),$destination);
            $destination=str_replace("/astvarlib",trim($asterisk_conf["astvarlibdir"]),$destination);
            $destination=str_replace("/moh",trim($asterisk_conf["astdatadir"])."/$moh_subdir",$destination);

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
// The following mysql query exports the csv file:
//
// select category,keyword,value,options,name,description,emptyok,level,readonly,type from issabelpbx_settings where module ='' order by category,keyword INTO OUTFILE '/tmp/settings.csv' FIELDS ENCLOSED BY '"' TERMINATED BY ',' ESCAPED BY '"' LINES TERMINATED BY '\n';
//
function issabelpbx_settings_init($commit_to_db = false) {
    global $amp_conf;

    if (!class_exists('issabelpbx_conf')) {
        include_once ($amp_conf['AMPWEBROOT'].'/admin/libraries/issabelpbx_conf.class.php');
    }

    $issabelpbx_conf =& issabelpbx_conf::create();

    $csv =  array_map('str_getcsv', file('initial_settings.csv'));

    foreach($csv as $val) {
        $set = array();
        $set['category']    = array_shift($val);
        $keyword            = array_shift($val);
        $set['value']       = array_shift($val);
        $set['defaultval']  =& $set['value'];
        $set['options']     = array_shift($val);
        $set['name']        = array_shift($val);
        $set['description'] = array_shift($val);
        $set['emptyok']     = array_shift($val);
        $set['level']       = array_shift($val);
        $set['readonly']    = array_shift($val);
        $set['type']        = array_shift($val);

        if($set['type']=='fselect') {
            $optarray = unserialize($set['options']);
            $set['options']=$optarray;
        }
        $issabelpbx_conf->define_conf_setting($keyword,$set);
    }


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
        if(is_array($db->tableInfo('cronmanager'))) {
           $cm =& cronmanager::create($db);
           $cm->enable_updates();
        }
    }

}
