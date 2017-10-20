<?php
global $amp_conf;

if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed');}

$sql[] = 'CREATE TABLE IF NOT EXISTS `logfile_settings` (
  `key` varchar(100) NOT NULL default "",
  `value` varchar(255) default NULL,
  PRIMARY KEY  (`key`)
)';

$sql[] = 'CREATE TABLE IF NOT EXISTS `logfile_logfiles` (
  `name` varchar(25) NOT NULL default "",
  `debug` varchar(25) default NULL,
  `dtmf` varchar(25) default NULL,
  `error` varchar(25) default NULL,
  `fax` varchar(25) default NULL,
  `notice` varchar(25) default NULL,
  `verbose` varchar(25) default NULL,
  `warning` varchar(25) default NULL,
  `security` varchar(25) default NULL,
  PRIMARY KEY  (`name`)
)';

foreach($sql as $s) {
	sql($s);
}
unset($sql);

// upgrade table if necessary
if (!$db->getAll('SHOW COLUMNS FROM logfile_logfiles WHERE FIELD = "security"')) {
	sql('ALTER TABLE logfile_logfiles ADD COLUMN security varchar(25) null default NULL');
	sql('UPDATE logfile_logfiles SET security="off"');
}

//set some defualts
$first_install = $db->getOne('SELECT COUNT(*) FROM logfile_settings');

if (!$first_install) { //zero count (aka false) is a new install
	$sql = 'INSERT INTO logfile_logfiles (name, debug, dtmf, error, fax, notice, verbose, warning, security)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
	$s = $db->query($sql, array('full', 'on', 'off', 'on', 'off', 'on', 'on', 'on', 'off'));
	$db->query($sql, array('console', 'on', 'off', 'on', 'off', 'on', 'on', 'on', 'off'));
}

// logger.conf used to be in core so let's make sure if there is a linked file it points to
// us and if not remove the link, retrieve_conf will deal with putting it back.
//
$lf = $amp_conf['ASTETCDIR'] . '/logger.conf';
if (file_exists($lf) && is_link($lf)) {
	$l = readlink($lf);
	if ($l != $amp_conf['AMPWEBROOT'] . "/admin/modules/logfiles/etc/logger.conf") {
		out(_("logger.conf symlinked to incorrect file:"));
		out($l);
		outn(_("removing.."));
		if (unlink($lf)) {
			out(_('ok'));
		} else {
			out(_('failed'));
		}
	}
}
