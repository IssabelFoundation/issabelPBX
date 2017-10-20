<?php

/**
 * @file
 * site-specific configuration file.
 */

###############################
# AMP or standalone settings
###############################
#
# From AMP.  Used for logon to database.
#
$AMP_FUNCTIONS_FILES = "../admin/functions.php;../admin/functions.inc.php";
$AMPORTAL_CONF_FILE = "/etc/amportal.conf";

//$amp_conf = parse_amportal_conf($AMPORTAL_CONF_FILE);
#
# Host for Asterisk Manager Interface
#
$ASTERISKMGR_DBHOST = "localhost";

#
# Database options for older legacy AMP installations (pre-IssabelPBX)
#   - $LEGACY_AMP_DBFILE only needs to be set if using a database like sqlite
#
$LEGACY_AMP_DBHOST = "localhost";
$LEGACY_AMP_DBENGINE = "mysql";
$LEGACY_AMP_DBFILE = "";
$LEGACY_AMP_DBNAME = "asterisk";

#
# Database cdr settings
#   - Only need to update these settings if standalone or an older AMP version (pre-IssabelPBX) is used
#   - $ASTERISKCDR_DBFILE only needs to be set if using a database like sqlite
#   Options: supported database types (others are supported, but not listed)
#     'mysql' - MySQL
#     'pgsql' - PostgreSQL
#     'oci8' - Oracle
#     'odbc' - ODBC
#
$ASTERISKCDR_DBHOST = "localhost";
$ASTERISKCDR_DBENGINE = "mysql";
$ASTERISKCDR_DBFILE = "";
$ASTERISKCDR_DBNAME = "asteriskcdrdb";
$ASTERISKCDR_DBTABLE = "cdr";

#
# Standalone, for use without AMP
#   set use = true;
#   set asterisk_mgruser to Asterisk Call Manager username
#   set asterisk_mgrpass to Asterisk Call Manager password
#
$STANDALONE['use'] = false;
$STANDALONE['asterisk_mgruser'] = "";
$STANDALONE['asterisk_mgrpass'] = "";
$STANDALONE['asteriskcdr_dbuser'] = "";
$STANDALONE['asteriskcdr_dbpass'] = "";

###############################
# authentication settings
###############################
#
# For using the Call Monitor only
#   option: 0 - use Authentication, Voicemail, and Call Monitor
#           1 - use only the Call Monitor
#
$ARI_NO_LOGIN = 0;

#
# Admin only account - change defaults to prevent unauthorized access to call recordings
#
$ARI_ADMIN_USERNAME = "admin";
$ARI_ADMIN_PASSWORD ="ari_password";
#
# Admin extensions
#   option: Comma delimited list of extensions
#
$ARI_ADMIN_EXTENSIONS = "";

#
# Authentication password to unlock cookie password
#   This must be all continuous and only letters and numbers
#   Change this password to prevent unauthorized access to cookie contents
#
$ARI_CRYPT_PASSWORD = "z1Mc6KRxA7Nw90dGjY5qLXhtrPgJOfeCaUmHvQT3yW8nDsI2VkEpiS4blFoBuZ";

###############################
# modules settings
###############################
#
# modules with admin only status (they will not be displayed for regular users)
#   option: Comma delimited list of module names (ie voicemail,callmonitor,help,settings)
#
$ARI_ADMIN_MODULES = "";

#
# disable modules (you can also just delete them from /recordings/modules without problems)
#   option: Comma delimited list of module names (ie voicemail,callmonitor,help,settings)
#
$ARI_DISABLED_MODULES = "";

#
# sets the default admin page
#   option: Comma delimited list of module names (ie voicemail,callmonitor,help,settings)
#
$ARI_DEFAULT_ADMIN_PAGE = "callmonitor";

#
# sets the default user page
#   option: Comma delimited list of module names (ie voicemail,callmonitor,help,settings)
#
$ARI_DEFAULT_USER_PAGE = "voicemail";

#
# enables ajax page refresh
#   option: 0 - disable ajax page refresh
#           1 - enable ajax page refresh
#
$AJAX_PAGE_REFRESH_ENABLE = 1;

#
# sets the default user page
#   option: refresh time in 'minutes:seconds' (0 to inifinity) : (0 to 59)
#
$AJAX_PAGE_REFRESH_TIME ="01:00";
###############################
# voicemail settings
###############################
#
# voicemail config.
#
$ASTERISK_VOICEMAIL_CONF = $amp_conf["ASTETCDIR"]."/voicemail.conf";

#
# To set to a specific context.
#   If using default or more than one context then leave blank
#
$ASTERISK_VOICEMAIL_CONTEXT = "";

#
# Location of asterisk voicemail recordings on server
#    Use semi-colon for multiple paths
#

$ASTERISK_VOICEMAIL_PATH = $amp_conf["ASTSPOOLDIR"]."/voicemail";

#
# valid mailbox folders
#
$ASTERISK_VOICEMAIL_FOLDERS = array();
$ASTERISK_VOICEMAIL_FOLDERS[0]['folder'] = "INBOX";
$ASTERISK_VOICEMAIL_FOLDERS[0]['name'] = _("INBOX");
$ASTERISK_VOICEMAIL_FOLDERS[1]['folder'] = "Family";
$ASTERISK_VOICEMAIL_FOLDERS[1]['name'] = _("Family");
$ASTERISK_VOICEMAIL_FOLDERS[2]['folder'] = "Friends";
$ASTERISK_VOICEMAIL_FOLDERS[2]['name'] = _("Friends");
$ASTERISK_VOICEMAIL_FOLDERS[3]['folder'] = "Old";
$ASTERISK_VOICEMAIL_FOLDERS[3]['name'] = _("Old");
$ASTERISK_VOICEMAIL_FOLDERS[4]['folder'] = "Work";
$ASTERISK_VOICEMAIL_FOLDERS[4]['name'] = _("Work");
$ASTERISK_VOICEMAIL_FOLDERS[5]['folder'] = "Urgent";
$ASTERISK_VOICEMAIL_FOLDERS[5]['name'] = _("Urgent");

###############################
# call monitor settings
###############################
#
# Location of asterisk call monitor recordings on server
#
$ASTERISK_CALLMONITOR_PATH = $amp_conf["ASTSPOOLDIR"]."/monitor";

#
# Extensions with access to all call monitor recordings
#   option: Comma delimited list of extensions or "all"
#
$CALLMONITOR_ADMIN_EXTENSIONS ="";
#
# Allow call monitor users to delete monitored calls
#   option: 0 - do not show controls
#           1 - show controls
#
$CALLMONITOR_ALLOW_DELETE = 1;

#
# Allow for aggressive matching of recording files to database records
#     will match recordings that are marked several seconds off
#   option: 0 - do not aggressively match
#           1 - aggressively match
#
$CALLMONITOR_AGGRESSIVE_MATCHING = 1;

#
# Limits log/recording file matching to exact matching
#     will not try to look through all the recordings and make a best match
#     even if there is not uniqueid
#     requires that the MYSQL_UNIQUEID flag be compiled in asterisk-addons
#     (in the asterisk-addon Makefile add the following "CFLAGS+=-DMYSQL_LOGUNIQUEID")
#
#     * use if there are or will be more than 2500 recording files
#
#   option: 0 - do not exact match
#           1 - only exact match
#
$CALLMONITOR_ONLY_EXACT_MATCHING = 0;

###############################
# conference page settings
###############################
#
# Meetme extension prefix
#   for this module to function, the user has to have
#   a meetme conference room {prefix}{extension}
#
$CONFERENCE_WEBMEETME_PREFIX = "";

#
# url to web meetme conference room
#   example: "http://example.mycompany.com/webmeetme"
#
$CONFERENCE_WEBMEETME_URL = "";

###############################
# help page settings
###############################
#
# help feature codes
#   list of handset options and their function
#
$ARI_HELP_FEATURE_CODES = array();
//$ARI_HELP_FEATURE_CODES['*411'] = _("Directory");
//$ARI_HELP_FEATURE_CODES['*43'] = _("Echo Test");
//$ARI_HELP_FEATURE_CODES['*60'] = _("Time");
//$ARI_HELP_FEATURE_CODES['*61'] = _("Weather");
//$ARI_HELP_FEATURE_CODES['*62'] = _("Schedule wakeup call");
//$ARI_HELP_FEATURE_CODES['*65'] = _("festival test (your extension is XXX)");
//$ARI_HELP_FEATURE_CODES['*77'] = _("IVR Recording");
//$ARI_HELP_FEATURE_CODES['*99'] = _("Playback IVR Recording");
//$ARI_HELP_FEATURE_CODES['666'] = _("Test Fax");
//$ARI_HELP_FEATURE_CODES['7777'] = _("Simulate incoming call");

$ARI_HELP_FEATURE_CODES['*72'] = _("Call Forward All Activate");
$ARI_HELP_FEATURE_CODES['*73'] = _("Call Forward All Deactivate");
$ARI_HELP_FEATURE_CODES['*74'] = _("Call Forward All Prompting Deactivate");
$ARI_HELP_FEATURE_CODES['*90'] = _("Call Forward Busy Activate");
$ARI_HELP_FEATURE_CODES['*91'] = _("Call Forward Busy Deactivate");
$ARI_HELP_FEATURE_CODES['*92'] = _("Call Forward Busy Prompting Deactivate");
$ARI_HELP_FEATURE_CODES['*52'] = _("Call Forward No Answer/Unavailable Activate");
$ARI_HELP_FEATURE_CODES['*53'] = _("Call Forward No Answer/Unavailable Deactivate");
$ARI_HELP_FEATURE_CODES['*70'] = _("Call Waiting - Activate");
$ARI_HELP_FEATURE_CODES['*71'] = _("Call Waiting - Deactivate");
$ARI_HELP_FEATURE_CODES['*78'] = _("Do-Not-Disturb Activate");
$ARI_HELP_FEATURE_CODES['*79'] = _("Do-Not-Disturb Deactivate");
$ARI_HELP_FEATURE_CODES['*97'] = _("My Voicemail");
$ARI_HELP_FEATURE_CODES['*98'] = _("Dial Voicemail");

###############################
# settings page settings
###############################
#
# protocol config.
#   config_file options: semi-colon delimited list of extensions
#
$ASTERISK_PROTOCOLS = array();
$ASTERISK_PROTOCOLS['iax']['table'] = "iax";
$ASTERISK_PROTOCOLS['iax']['config_files'] = $amp_conf["ASTETCDIR"]."/iax.conf;".$amp_conf["ASTETCDIR"]."/iax_additional.conf";
$ASTERISK_PROTOCOLS['sip']['table'] = "sip";
$ASTERISK_PROTOCOLS['sip']['config_files'] = $amp_conf["ASTETCDIR"]."/sip.conf;".$amp_conf["ASTETCDIR"]."/sip_additional.conf";
$ASTERISK_PROTOCOLS['zap']['table'] = "zap";
$ASTERISK_PROTOCOLS['zap']['config_files'] = $amp_conf["ASTETCDIR"]."/zapata.conf;".$amp_conf["ASTETCDIR"]."/zapata_additional.conf;".$amp_conf["ASTETCDIR"]."/chan_dahdi.conf;".$amp_conf["ASTETCDIR"]."/chan_dahdi_additional.conf";
# Settings for Follow-Me Select Boxes in seconds
#

$SETTINGS_PRERING_LOW = 0;
$SETTINGS_PRERING_HIGH = 30;
$SETTINGS_LISTRING_LOW = 0;
$SETTINGS_LISTRING_HIGH = 60;

$SETTINGS_FOLLOW_ME_LIST_MAX = 5;
$SETTINGS_ALLOW_VMX_SETTINGS = true;
#
# For setting
#   option: 0 - do not show controls
#           1 - show controls
#
$SETTINGS_ALLOW_CALLFORWARD_SETTINGS = 1;
$SETTINGS_ALLOW_VOICEMAIL_SETTINGS = 1;
$SETTINGS_ALLOW_VOICEMAIL_PASSWORD_SET = 1;

#
# password length
#   setting: number of characters required for changing voicemail password
#
$SETTINGS_VOICEMAIL_PASSWORD_LENGTH = 3;

#
# password exact length
#   option: 0 - do not require exact length when setting the password
#           1 - require exact length when setting the password
#
$SETTINGS_VOICEMAIL_PASSWORD_EXACT = 0;

#
# Default
#   option:
#           ".WAV" - wav49 format
#	    ".wav" - wav format
#           ".gsm" - gsm format
#
$ARI_VOICEMAIL_AUDIO_FORMAT_DEFAULT = ".WAV";

#
# For setting
#   option: 0 - do not show controls
#           1 - show controls
#
$SETTINGS_ALLOW_CALL_RECORDING_SET = 1;


$SETTINGS_ALLOW_PHONE_SETTINGS = 1;

#
# Maximum number of sound files that will be read before an error is generated indicating issues since
# too many files can be create problems but on some systems this may need to be increased.
#
$SETTINGS_MAX_FILES=3000;

?>
