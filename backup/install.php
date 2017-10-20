<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db, $amp_conf;

$autoincrement = ($amp_conf["AMPDBENGINE"] == "sqlite3") ? "AUTOINCREMENT" : "AUTO_INCREMENT";
$sql[] = $bu_table = 'CREATE TABLE IF NOT EXISTS `backup` (
			`id` int(11) NOT NULL ' . $autoincrement . ',
			`name` varchar(50) default NULL,
			`description` varchar(255) default NULL,
			`immortal` varchar(25) default NULL,
			`data` longtext default NULL,
			PRIMARY KEY  (`id`)
			)';

$sql[] = 'CREATE TABLE IF NOT EXISTS `backup_details` (
			`backup_id` int(11) NOT NULL,
			`key` varchar(50) default NULL,
			`index` varchar(25) default NULL,
			`value` varchar(250) default NULL
			)';

$sql[] = 'CREATE TABLE IF NOT EXISTS `backup_items` (
			`backup_id` int(11) NOT NULL,
			`type` varchar(50) default NULL,
			`path` text,
			`exclude` text
			)';

$sql[] = 'CREATE TABLE IF NOT EXISTS `backup_cache` (
			`id` varchar(50) NOT NULL,
			`manifest` longtext,
			UNIQUE KEY `id` (`id`)
			)';

$sql[] = 'CREATE TABLE IF NOT EXISTS `backup_servers` (
			`id` int(11) NOT NULL ' . $autoincrement . ',
			`name` varchar(50) default NULL,
			`desc` varchar(255) default NULL,
			`type` varchar(50) default NULL,
			`readonly` varchar(250) default NULL,
			`immortal` varchar(25) default NULL,
			`data` longtext default NULL,
			PRIMARY KEY  (`id`)
			)';

$sql[] = 'CREATE TABLE IF NOT EXISTS `backup_server_details` (
			`server_id` int(11) NOT NULL,
			`key` varchar(50) default NULL,
			`value` varchar(250) default NULL
			)';

$sql[] = 'CREATE TABLE IF NOT EXISTS `backup_templates` (
			`id` int(11) NOT NULL ' . $autoincrement . ',
			`name` varchar(50) default NULL,
			`desc` varchar(255) default NULL,
			`immortal` varchar(25) default NULL,
			`data` longtext default NULL,
			PRIMARY KEY  (`id`)
			)';

$sql[] = 'CREATE TABLE IF NOT EXISTS `backup_template_details` (
			`template_id` int(11) NOT NULL,
			`type` varchar(50) default NULL,
			`path` text,
			`exclude` text
			)';

foreach($sql as $q) {
	db_e($db->query($q), 'die_issabelpbx', 0, _("Can not create backup tables"));
}
unset($sql);
//#5751
//#7842 Move up so email column added on new installs before defaults are inserted.
if (!$db->getAll('SHOW COLUMNS FROM backup WHERE FIELD = "email"')) {
	sql('ALTER TABLE backup ADD COLUMN `email` longtext default NULL');
}
//migration to 2.7
$migrate=$db->getAll('show tables like "Backup"');
if($db->IsError($migrate)) {
	die_issabelpbx(_("Can't check for Backup table")."\n".$migrate->getMessage());
}
if(count($migrate) > 0){//migrate to new backup structure
	$sql=$db->query('insert into backup (name, voicemail, recordings, configurations, cdr, fop, minutes, hours, days, months, weekdays, command, method, id) select * from Backup;');
	if ($db->IsError($sql)) {
		out(_('ERROR: failed to migrate from old "Backup" table to new "backup" table'));
		out(_('This error can result from a previous incomplete/failed install of'));
		out(_('this module. You should probably uninstall and reinstall this module'));
		out(_('doing so will result in a loss of all your backup settings though previous'));
		out(_('backup data will be preserved.'));
		out(_("Failure Message:")."\n".$sql->getMessage());
	} else {
		  //get data from amportal and populate the table with it
		  //ftp
		if (isset($amp_conf['FTPBACKUP']) && $amp_conf['FTPBACKUP'] == strtolower('yes')) {
			$data['ftpuser']=$amp_conf['FTPUSER'];
			$data['ftppass']=$amp_conf['FTPPASSWORD'];
			$data['ftphost']=$amp_conf['FTPSERVER'];
			$data['ftpdir']=$amp_conf['FTPSUBDIR'];
		}
		  //ssh
		if(isset($amp_conf['SSHBACKUP']) && $amp_conf['SSHBACKUP']==strtolower('yes')){
			$data['sshuser']=$amp_conf['SSHUSER'];
			$data['sshkey']=$amp_conf['SSHRSAKEY'];
			$data['sshhost']=$amp_conf['SSHSERVER'];
			$data['sshdir']=$amp_conf['SSHSUBDIR'];
		}
		  //includes & excludes
		if (isset($amp_conf['AMPPROVROOT']) && $amp_conf['AMPPROVROOT']!='') {
			$data['include']=str_replace(' ',"\n",$amp_conf['AMPPROVROOT']);
			if(isset($amp_conf['AMPPROVEXCLUDELIST']) && $amp_conf['AMPPROVEXCLUDELIST']!=''){
				$data['exclude']=str_replace('',"\r",trim($opts['AMPPROVEXCLUDELIST']));
			}
			if(isset($amp_conf['AMPPROVEXCLUDE']) && $amp_conf['AMPPROVEXCLUDE']!=''){
				@$data['exclude']=implode("\r",file($amp_conf['AMPPROVEXCLUDE']));
			}
		}
		if(isset($data)){
			$db_parms=$data;
			$data='';
	 		//dont include empty values in the query
			foreach(array_keys($db_parms) as $key){
				if($db_parms[$key]!=''){
					$data.=$key.'="'.$db->escapeSimple($db_parms[$key]).'",';
				}
			}
			$data=substr($data,0,-1);//remove trailing ,
			$sql='UPDATE backup set '.$data;
			$check = $db->query($sql);
			if($db->IsError($check)) {
				die_issabelpbx(_('Can not migrate Backup table'));
			}

			out(_('Backup migration completed'));
		}else{
			out(_('Nothing to migrate'));
		}
		$sql='DROP TABLE Backup';
		$check = $db->query($sql);
		if($db->IsError($check)) {
			out(_('ERROR: Failed to remove old "Backup" table. You should uninstall'));
			out(_('and then re-install this module. Settings will be lost but old'));
			out(_('backup data will be retained.'));
		}else{
			out(_('Old Backup table removed'));
		}
	}
}

//migration to 2.9
$sql	= 'describe backup';
$fields	= $db->getAssoc($sql);
if (!array_key_exists('remotesshhost',$fields) && array_key_exists('command',$fields)) {
	// This should only be needed once.
	// But the original migration did not do it and there is no harm in cleansing the database anyhow
	outn(_('Replacing ampbackup.pl in db..'));
	$sql=$db->query("UPDATE backup SET command = REPLACE(command,'ampbackup.pl','ampbackup.php')");
	if($db->IsError($sql)) {
		out(_('an error has occurred, update not done'));
		out($sql->getMessage());
	} else {
		out(_('ok'));
	}

	// Remove retrieve_backup_cron_from_mysql.pl if still there and a link
	//
	if (is_link($amp_conf['AMPBIN'].'/retrieve_backup_cron_from_mysql.pl') && readlink($amp_conf['AMPBIN'].'/retrieve_backup_cron_from_mysql.pl') == $amp_conf['AMPWEBROOT'].'/admin/modules/backup/bin/retrieve_backup_cron_from_mysql.pl') {
		outn(_("removing retrieve_backup_cron_from_mysql.pl.."));
		if (unlink($amp_conf['AMPBIN'].'/retrieve_backup_cron_from_mysql.pl')) {
			out(_("removed"));
		} else {
			out(_("failed"));
		}
	}

	//check remote backup fields
	$sql	= 'describe backup';
	$fields	= $db->getAssoc($sql);
	if (!array_key_exists('remotesshhost',$fields)) {
		out(_('Migrating backup table...'));
		$sql = 'alter table backup add remotesshhost varchar(50) default NULL,
				add remotesshuser varchar(50) default NULL,
				add remotesshkey varchar(150) default NULL,
				add remoterestore varchar(5) default NULL';
		$q = $db->query($sql);
		if ($db->IsError($q)) {
			out(_('WARNING: backup table not migrated'));
		} else {
			out(_('Successfully migrated backup table!'));
		}
	}

	//check for overwritebackup filed
	$sql='describe backup';
	$fields=$db->getAssoc($sql);
	if(!array_key_exists('overwritebackup',$fields)){
		out(_('Migrating backup table...'));
		$sql='alter table backup add overwritebackup varchar(5) default NULL';
		$q=$db->query($sql);
		if($db->IsError($q)){
			out(_('WARNING: backup table not migrated'));
		} else {
			out(_('Successfully migrated backup table!'));
		}
	}

	if (!is_dir($amp_conf['ASTVARLIBDIR'].'/backups')) {
		outn(_('Creating backups directory..'));
		if (mkdir($amp_conf['ASTVARLIBDIR'].'/backups')) {
			out(_('ok'));
		} else {
			out(_('failed'));
			out(sprintf(_('WARNING: failed to create backup directory: %s'),$amp_conf['ASTVARLIBDIR'].'/backups'));
		}
	}

}

//migration to 2.10

//migrate pre 2.10 backups
if ($db->getOne('SELECT COUNT(*) FROM backup_templates') < 1) {
	// Don't know which of these it needs, but including functions.inc.php won't work when ugprading form earlier releases as the old version will
	// already be there.
	$dir = dirname(__FILE__);
	require_once($dir . '/functions.inc/class.backup.php');
	require_once($dir . '/functions.inc/backup.php');
	require_once($dir . '/functions.inc/servers.php');
	require_once($dir . '/functions.inc/templates.php');
	require_once($dir . '/functions.inc/restore.php');

	//create default servers
	$server['legacy'] = array(
					'id'		=> '',
					'name'		=> 'Legacy Backup',
					'desc'		=> _('Location of backups pre 2.10'),
					'immortal'	=> '',
					'type'		=> 'local',
					'path'		=> '__ASTVARLIBDIR__/backups',
	);

	$server['local'] = array(
					'id'		=> '',
					'name'		=> 'Local Storage',
					'desc'		=> _('Storage location for backups'),
					'immortal'	=> 'true',
					'type'		=> 'local',
					'path'		=> '__ASTSPOOLDIR__/backup',
	);

	$server['mysql'] = array(
					'id'		=> '',
					'name'		=> 'Config server',
					'desc'		=> _('PBX config server, generally a local database server'),
					'immortal'	=> 'true',
					'type'		=> 'mysql',
					'host'		=> '__AMPDBHOST__',
					'port'		=> 3306,
					'user'		=> '__AMPDBUSER__',
					'password'	=> '__AMPDBPASS__',
					'dbname'	=> '__AMPDBNAME__',
	);

	$server['cdr'] = array(
					'id'		=> '',
					'name'		=> 'CDR server',
					'desc'		=> _('CDR server, generally a local database server'),
					'immortal'	=> 'true',
					'type'		=> 'mysql',
					'host'		=> '__CDRDBHOST__',
					'port'		=> '__CDRDBPORT__',
					'user'		=> '__CDRDBUSER__',
					'password'	=> '__CDRDBPASS__',
					'dbname'	=> '__CDRDBNAME__',
	);

	foreach ($server as $not_this => $t) {
		$server[$not_this] = backup_put_server($t);
	}
	sql('UPDATE backup_servers SET readonly = "a:1:{i:0;s:1:\"*\";}"');
	sql('UPDATE backup_servers SET immortal = "true"');
	$createdby = serialize(array('created_by' => 'install.php'));
	sql('UPDATE backup_servers SET data = "' . addslashes($createdby) . '"');

	out(_('added default backup servers'));

	//create default temaplates
	$temp['basic'] = array(
					'id'		=> '',
					'name'		=> 'Config Backup',
					'desc'		=> _('Configurations only'),
					'immortal'	=> 'true',
					'type'		=> array(
									'mysql',
									'astdb'
					),
					'path'		=> array(
									'server-' . $server['mysql'],
									''
					),
					'exclude'	=> array(
									'',
									''
					)
	);
	$temp['full']				= $temp['basic'];
	$temp['full']['name']		= 'Full Backup';
	$temp['full']['desc']		= _('A full backup of core settings and web files, doesn\'t include system sounds or recordings.');

	$temp['full']['type'][]		= 'mysql';
	$temp['full']['path'][]		= 'server-' . $server['cdr'];
	$temp['full']['exclude'][]	= '';

	$temp['full']['type'][]		= 'mysql';
	$temp['full']['path'][]		= 'server-' . $server['mysql'];
	$temp['full']['exclude'][]	= '';

	$temp['full']['type'][]		= 'astdb';
	$temp['full']['path'][]		= '';
	$temp['full']['exclude'][]	= '';

	$temp['full']['type'][]		= 'dir';
	$temp['full']['path'][]		= '__ASTETCDIR__';
	$temp['full']['exclude'][]	= '';

	$temp['full']['type'][]		= 'dir';
	$temp['full']['path'][]		= '__AMPWEBROOT__';
	$temp['full']['exclude'][]	= '';

	$temp['full']['type'][]		= 'dir';
	$temp['full']['path'][]		= '__AMPBIN__';
	$temp['full']['exclude'][]	= array(
									'__ASTVARLIBDIR__/moh',
									'__ASTVARLIBDIR__/sounds'
									);

	$temp['full']['type'][]		= 'dir';
	$temp['full']['path'][]		= '/etc/dahdi';
	$temp['full']['exclude'][]	= '';

	$temp['full']['type'][]		= 'dir';
	$temp['full']['path'][]		= '/tftpboot';
	$temp['full']['exclude'][]	= '';


	$temp['cdr'] = array(
					'id'		=> '',
					'name'		=> 'CDR\'s',
					'desc'		=>	_('Call Detail Records'),
					'immortal'	=> 'true',
					'type'		=> array(
									'mysql',
					),
					'path'		=> array(
									'server-' . $server['cdr'],
					),
					'exclude'	=> array(
									'',
					)
	);

	$temp['voicemail'] = array(
					'id'		=> '',
					'name'		=> 'Voice Mail',
					'desc'		=> _('Voice Mail Storage'),
					'immortal'	=> 'true',
					'type'		=> array(
									'dir',
					),
					'path'		=> array(
									'__ASTSPOOLDIR__/voicemail',
					),
					'exclude'	=> array(
									'',
					)
	);

	$temp['recordings'] = array(
					'id'		=> '',
					'name'		=> 'System Audio',
					'desc'		=> _('All system audio - including IVR prompts and Music On Hold.'
									. ' DOES NOT BACKUP VOICEMAIL'),
					'immortal'	=> 'true',
					'type'		=> array(
									'dir',
									'dir'
					),
					'path'		=> array(
									'__ASTVARLIBDIR__/moh',
									'__ASTVARLIBDIR__/sounds/custom',
					),
					'exclude'	=> array(
									'',
									''
					)
	);

	$temp['safe_backup'] = array(
					'id'		=> '',
					'name'		=> 'Exclude Backup Settings',
					'desc'		=>	_('Exclude Backup\'s settings so that they dont get restored, usefull for a remote restore'),
					'immortal'	=> 'true',
					'type'		=> array(
									'mysql',
					),
					'path'		=> array(
									'server-' . $server['mysql'],
					),
					'exclude'	=> array(
									"backup\n"
									. "backup_cache\n"
									. "backup_details\n"
									. "backup_items\n"
									. "backup_server_details\n"
									. "backup_servers\n"
									. "backup_template_details\n"
									. "backup_templates\n"
					)
	);

	foreach ($temp as $that => $t) {
		$temp[$that] = backup_put_template($t);
	}

	//lock this all down so that their readonly
	sql('UPDATE backup_templates SET immortal = "true"');
	$createdby = serialize(array('created_by' => 'install.php'));
	sql('UPDATE backup_templates SET data = "' . addslashes($createdby) . '"');
	out(_('added default backup templates'));


	$sql	= 'describe backup';
	$fields	= $db->getAssoc($sql);
	db_e($fields);
	if (array_key_exists('command', $fields)) {
		//migrate backup table
		$sql = 'SELECT * FROM backup';
		$backup = $db->getAll($sql, DB_FETCHMODE_ASSOC);
		sql('RENAME TABLE backup TO backup_old', 'query');
		sql($bu_table);



		//if we currently have backups, migrate them
		if (count($backup)) {
			foreach ($backup as $b) {
				out('migrating backup ' . $b['name']);
				//prapre old values
				$b['minutes']				= trim($b['minutes'], ':');
				$b['days']					= trim($b['days'], ':');
				$b['weekdays']				= trim($b['weekdays'], ':');
				$b['hours']					= trim($b['hours'], ':');
				$b['months']				= trim($b['months'], ':');

				//set up array for insertion
				$new['name']				= _('MIGRATED') . ' ' . $b['name'];
				$new['id']					= '';
				$new['desc']				= _('migrated backup') . ' ' . $b['name'];

				if (substr($b['command'], 0, 9) == '0 0 0 0 0') {//formerly "now"
					$new['cron_schedule']	= 'never';
				} else {
					$new['cron_schedule']	= 'custom';
					$new['cron_minute']		= explode('::', $b['minutes']);
					$new['cron_dom']		= explode('::', $b['days']);
					$new['cron_dow']		= explode('::', $b['weekdays']);
					$new['cron_hour']		= explode('::', $b['hours']);
					$new['cron_month']		= explode('::', $b['months']);
				}

				//include items
				foreach ($b as $k => $v) {
					if (!isset($v) || $v != 'yes') {
						continue;
					}
					switch ($k) {
						case 'voicemail':
							$new['type'][]		= 'dir';
							$new['path'][]		= '__ASTSPOOLDIR__/voicemail';
							$new['exclude'][]	= '';
							break;
						case 'recordings':
							$new['type'][]		= 'dir';
							$new['path'][]		= '__ASTVARLIBDIR__/moh';
							$new['exclude'][]	= '';

							$new['type'][]		= 'dir';
							$new['path'][]		= '__ASTVARLIBDIR__/sounds/custom';
							$new['exclude'][]	= '';
							break;
						case 'configurations':
							$new['type'][]		= 'mysql';
							$new['path'][]		= 'server-' . $server['mysql'];
							$new['exclude'][]	= '';

							$new['type'][]		= 'astdb';
							$new['path'][]		= '';
							$new['exclude'][]	= '';

							$new['type'][]		= 'dir';
							$new['path'][]		= '__ASTETCDIR__';
							$new['exclude'][]	= '';
							break;
						case 'cdr':
							$new['type'][]		= 'mysql';
							$new['path'][]		= 'server-' . $server['cdr'];
							if ($b['overwritebackup'] == 'yes') {
								$new['exclude'][]	= "backup\n"
													. "backup_cache\n"
													. "backup_details\n"
													. "backup_items\n"
													. "backup_server_details\n"
													. "backup_servers\n"
													. "backup_template_details\n"
													. "backup_templates\n";
							} else {
								$new['exclude'][]	= '';
							}

							break;
						case 'fop':
							$new['type'][]		= 'dir';
							$new['path'][]		= '__AMPWEBROOT__/panel';
							$new['exclude'][]	= '';
							break;
						case 'admin':
							$new['type'][]		= 'dir';
							$new['path'][]		= '__AMPWEBROOT__/admin';
							$new['exclude'][]	= '';
							break;
					}

				}

				//include/exclude
				$includes = explode("\n", $b['include']);
				foreach ($includes as $include) {
					$new['type'][]			= 'dir';
					$new['path'][]			= $include;
					$new['exclude'][]		= $b['exclude'];
				}

				//storage
				//always assume local
				$new['storage_servers'][]	= $server['local'];
				$new['bu_server'] 			= 0;//not remote. will get reset later if needed

				//ftp server
				if ($b['ftpuser'] && $b['ftppass'] && $b['ftphost']) {
					$s = array(
								'id'		=> '',
								'name'		=> 'Migrated FTP server',
								'desc'		=> _('Migrated FTP server for backup ') . $b['name'],
								'type'		=> 'ftp',
								'host'		=> $b['ftphost'],
								'port'		=> 21,
								'user'		=> $b['ftpuser'],
								'password'	=> $b['ftppass'],
								'path'		=> $b['ftpdir'],
					);
					$new['storage_servers'][] = backup_put_server($s);
				}

				//sshserver
				if ($b['sshkey'] && $b['sshhost'] ) {
					$s = array(
								'id'		=> '',
								'name'		=> 'Migrated SSH server',
								'desc'		=> _('Migrated SSH server for backup ') . $b['name'],
								'type'		=> 'ssh',
								'host'		=> $b['sshhost'],
								'port'		=> 22,
								'user'		=> $b['sshuser'],
								'key'		=> $b['sshkey'],
								'path'		=> $b['sshdir'],
					);
					$new['storage_servers'][] = backup_put_server($s);
				}

				//email server
				if ($b['sshkey'] && $b['sshhost'] ) {
					$s = array(
								'id'		=> '',
								'name'		=> 'Migrated EMAIL server',
								'desc'		=> _('Migrated EMAIL server for backup ') . $b['name'],
								'type'		=> 'email',
								'addr'		=> $b['emailaddr'],
								'maxsize'	=> string2bytes($b['emailmaxsize'], $b['emailmaxtype'])
					);
					$new['storage_servers'][]= backup_put_server($s);
				}

				//remote ssh server
				if ($b['remoterestore']) {
					$s = array(
								'id'		=> '',
								'name'		=> 'Migrated SSH server',
								'desc'		=> _('Migrated remote SSH server for backup ')
												. $b['name'],
								'type'		=> 'ssh',
								'host'		=> $b['remotesshhost'],
								'port'		=> 22,
								'user'		=> $b['remotesshuser'],
								'key'		=> $b['remotesshkey'],
								'path'		=> '',
					);
					$s = backup_put_server($s);
					$new['storage_servers'][] = $s;
					$new['bu_server'] = $s;
				}

				//insert backup
				backup_put_backup($new);
				unset($new);
			}

			//drop tem backup table
			sql('DROP TABLE backup_old');
		}

	} else {//no items to migrate, just install a default backup

		//remove the legacy backup location
		backup_del_server($server['legacy']);

		//add a default backup
		$new['id']					= '';
		$new['name']				= 'Default backup';
		$new['desc']				= _('Default backup; automatically installed');
		$new['cron_schedule']		= 'monthly';
		$new['type'][]				= 'mysql';
		$new['path'][]				= 'server-' . $server['mysql'];
		$new['exclude'][]			= '';
		$new['type'][]				= 'astdb';
		$new['path'][]				= '';
		$new['exclude'][]			= '';
		$new['storage_servers'][] 	= $server['local'];
		$new['bu_server'] 			= 0;
		$new['delete_amount']		= 12;

		//insert backup
		backup_put_backup($new);
		$createdby = serialize(array('created_by' => 'install.php'));
		sql('UPDATE backup SET data = "' . addslashes($createdby) . '"');
		unset($new);
	}

}

//add data fields if they dont exists
//2.10 beta
if (!array_key_exists('data', $db->getAssoc('describe backup'))) {
	sql('ALTER TABLE backup ADD COLUMN `data` longtext default NULL');
}

if (!array_key_exists('data', $db->getAssoc('describe backup_servers'))) {
	sql('ALTER TABLE backup_servers ADD COLUMN `data` longtext default NULL');
}

if (!array_key_exists('data', $db->getAssoc('describe backup_templates'))) {
	sql('ALTER TABLE backup_templates ADD COLUMN `data` longtext default NULL');
}

//fix for schmooze#1388, __AMPBIN__ excludes were being set to null
$ex = $db->getOne('SELECT exclude FROM backup_template_details '
	. 'WHERE template_id = 2 AND path =  "__AMPBIN__"');
db_e($ex);

if ($ex = 'N;') {
	$value = serialize(array(
			'__ASTVARLIBDIR__/moh',
			'__ASTVARLIBDIR__/sounds'
	));
	$sql = 'UPDATE backup_template_details SET exclude = ? '
		. 'WHERE template_id = 2 AND path = "__AMPBIN__"';
	$res = $db->query($sql, array($value));
	db_e($res);
}

//fix for #6083
$full_issabelpbx_conf = $db->getOne('select path FROM backup_template_details '
			. 'WHERE template_id = 2 AND path = "/etc/issabelpbx.conf"');

if ($full_issabelpbx_conf) {
	sql('DELETE FROM backup_template_details WHERE type = "file" and path = "/etc/issabelpbx.conf" and template_id = 2');
}

$issabelpbx_conf =& issabelpbx_conf::create();

// AMPBACKUPEMAILFROM
//
$set['value'] = '';
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = 'backup';
$set['category'] = 'Backup Module';
$set['emptyok'] = 1;
$set['name'] = 'Email "From:" Address';
$set['description'] = 'The From: field for emails when using the backup email feature.';
$set['type'] = CONF_TYPE_TEXT;
$issabelpbx_conf->define_conf_setting('AMPBACKUPEMAILFROM',$set,true);
//TODO: delete sudo option
