#!/usr/bin/env php
<?php
$restrict_mods						= array('backup' => true, 'core' => true);
$bootstrap_settings['cdrdb']		= true;
$bootstrap_settings['issabelpbx_auth']	= false;
if (!@include_once(getenv('ISSABELPBX_CONF') ? getenv('ISSABELPBX_CONF') : '/etc/issabelpbx.conf')) {
	include_once('/etc/asterisk/issabelpbx.conf');
}

//ensure the backup modules is avalible before continuing
$mod_info = module_getinfo('backup', MODULE_STATUS_ENABLED);

if (!isset($mod_info['backup'])) {
	echo _('Backup module not found or is disabled. Aborting!' . PHP_EOL);
	exit(1);
}
/**
 * OPTIONS
 * opts - if we have opts, run the backup from it, passing the file back when finisehed
 * id - if we have an id. If we do, just run a "regular" backup, using the id for options
 *    and pulling all other data from the database
 * astdb - tools for handeling the astdb
 */

$getopt = (function_exists('_getopt') ? '_' : '') . 'getopt';
$vars = $getopt($short = '', $long = array('opts::', 'id::', 'astdb::', 'data::'));

//if the id option was passed
if (isset($vars['id']) && $vars['id']) {
	//bu = backup settings
	//s= servers
	//b= backup object
	if ($bu = backup_get_backup($vars['id'])) {
		//dont run if no storage servers were found
		if (!isset($bu['storage_servers']) || count($bu['storage_servers']) < 1) {
			backup_log(_('No storage servers found! Aborting.'));
			exit();
		}
		$s = backup_get_server('all_detailed');
		$b = new Backup($bu, $s);
		backup_log(_('Intializing Backup') . ' ' .$vars['id']);
		backup_clear_log();
		$b->init();
		if ($b->b['bu_server'] == "0") {
			//get lock to prevent backups from being run cuncurently
			while (!$b->acquire_lock()) {
				backup_log(_('waiting for lock...'));
				sleep(10);
			}
			backup_log(_('Backup Lock acquired!'));

			backup_log(_('Running pre-backup hooks...'));
			$b->run_hooks('pre-backup');

			backup_log(_('Adding items...'));
			$b->add_items();

			backup_log(_('Bulding manifest...'));
			$b->build_manifest();
			$b->save_manifest('local');
			$b->save_manifest('db');

			backup_log(_('Creating backup...'));
			$b->create_backup_file();
		} else {//run backup remotly
			$opts = array(
					'bu'	=> $bu,
					's'		=> $s,
					'b'		=> $b
			);

			//dont run if there are no items to backup
			if (!$opts['bu']['items']) {
				backup_log(_('No items in backup set. Aborting.'));
				exit();
			}
			backup_log(_('Connecting to remote server...'));
			$cmd[] = ipbx_which('ssh');
			$cmd[] = '-o StrictHostKeyChecking=no -i';
			$cmd[] = backup__($s[$b->b['bu_server']]['key']);
			$cmd[] = '-p';
			$cmd[] = $s[$b->b['bu_server']]['port'];
			$cmd[] = backup__($s[$b->b['bu_server']]['user'])
					. '\@'
					. backup__($s[$b->b['bu_server']]['host']);
			$cmd[] = '\'php -r "';
			$escape = '$bootstrap_settings["issabelpbx_auth"] = false;
				$bootstrap_settings["skip_astman"] = true;
				$restrict_mods = true;
				if (!@include_once(getenv("ISSABELPBX_CONF") ? getenv("ISSABELPBX_CONF") : "/etc/issabelpbx.conf")) {
					include_once("/etc/asterisk/issabelpbx.conf");
				}
				system($amp_conf["AMPBIN"] . "/backup.php --opts=' . base64_encode(serialize($opts)) . '");
				';
			$cmd[] = addcslashes(str_replace(array("\n", "\t"), '', $escape), '"$');
			$cmd[] = '"\'';
			$cmd[] = '> ' . $b->b['_tmpfile'];
			//backup_log(implode(' ', $cmd));
			exec(implode(' ', $cmd), $ret, $status);
			if ($status !== 0) {
				backup_log(_('Something went wrong when connecting to remote server. Aborting!'));
				exit($status);
			}
			unset($cmd);

			backup_log(_('Verifying received file...'));
			$cmd[] = ipbx_which('tar');
			$cmd[] = '-zxOf';
			$cmd[] = $b->b['_tmpfile'];
			$cmd[] = '&> /dev/null';
			exec(implode(' ', $cmd), $ret, $status);
			unset($cmd);

			if ($status !== 0) {

				//read out the first 10 lines of the file
				//use the 'old fashtion' way of reading a file, as it
				//guarenties that we dont load more than 1 line at a time
				$file = fopen($b->b['_tmpfile'], 'r');
				$linecount = 0;
				backup_log(_('File verification failed. '));
				backup_log(_('Here are the first few lines of the file '
				. 'as sent by the remote server:'));
				backup_log('');

				while(($line = fgets($file)) !== false && $linecount < 10) {
					backup_log(' > ' . $line);
					$linecount++;
				}

				exit(1);
			}

			backup_log(_('Processing received file...'));
			$b->b['manifest'] = backup_get_manifest_tarball($b->b['_tmpfile']);
			$b->save_manifest('db');
		}

		backup_log(_('Storing backup...'));
		$b->store_backup();

		backup_log(_('Running post-backup hooks...'));
		$b->run_hooks('post-backup');

		if ($b->b['bu_server'] == "0") { //local backup? Were done!
			if ($b->b['error'] !== false) {
				backup_log(_('Backup completed with errors!'));
			} else {
				backup_log(_('Backup successfully completed!'));
			}
		} else {
			if ($b->b['restore']) {
				if (isset($b->b['manifest']['file_list'])) {
					foreach ($b->b['manifest']['file_list'] as $dir => $file) {
						$files[] = $dir;
					}
				}
				$restore['settings'] = true;
				if (isset($files)) {
					$restore['files'] = $files;
				}

				//check if we have a cdr to restore
				if ($b->b['manifest']['fpbx_cdrdb'] != '') {
					$restore['cdr'] = true;
				}

				backup_log(_('Restoring backup...'));
				$cmd = $amp_conf['AMPBIN'] . '/restore.php '
						. '--restore=' . $b->b['_tmpfile']
						. ' --items=' . base64_encode(serialize($restore));
				system($cmd);
			}

			backup_log(_('Running post-backup hooks...'));
			$b->run_hooks('post-backup');

			//disable registered trunks if requested
			if ($b->b['disabletrunks'] == 'true' && function_exists('core_trunks_disable')) {
				//disables registered trunks
				core_trunks_disable('reg', true);
			}

			//apply configs if requested
			if ($b->b['applyconfigs'] == 'true') {
				do_reload(true);
			}

			if ($b->b['error'] !== false) {
				backup_log(_('Backup completed with errors!'));
			} else {
				backup_log(_('Backup successfully completed!'));
			}
		}

	} else { //invalid backup
		backup_log('backup id ' . $vars['id'] . ' not found!');
	}
	if(is_object($b) && method_exists($b,'emailCheck')){
		$b->emailCheck();
	}
//if the opts option was passed, used for remote backup (warm spare)
} elseif(isset($vars['opts']) && $vars['opts']) {
	//r = remote options
	if(!$r = unserialize(base64_decode($vars['opts']))) {
		echo 'invalid opts';
		exit(1);
	}
	$b = new Backup($r['bu'], $r['s']);
	$b->b['_ctime']		= $r['b']->b['_ctime'];
	$b->b['_file']		= $r['b']->b['_file'];
	$b->b['_dirname']	= $r['b']->b['_dirname'];
	backup_clear_log();
	$b->init();
	$b->run_hooks('pre-backup');
	$b->add_items();
	$b->build_manifest();
	$b->save_manifest('local');
	$b->create_backup_file(true);
	exit();
} elseif(isset($vars['astdb']) && $vars['astdb']) {
	switch ($vars['astdb']) {
		case 'dump':
			echo astdb_get(array('RG', 'BLKVM', 'FM', 'dundi'));
			break;
		case 'restore':
			if (is_file($vars['data'])) {
				$vars['data'] = file_get_contents($vars['data']);
			}
			astdb_put(unserialize($vars['data']), array('RINGGROUP', 'BLKVM', 'FM', 'dundi'));
			break;
	}
} else {
	show_opts();
}

exit();

function show_opts() {
	$e[] = 'backup.php';
	$e[] = '';
	$e[] = 'options:';
	$e[] = "\t" . '--id=<id number> - a valid backup id';
	$e[] = "\t" . '--astdb=<restore|dump> - dump or restore the astdb';
	$e[] = "\t" . '--data=<data> a serilialized string of the astdb dumb to restore.';
	$e[] = "\t\t" . ' Can also point to a file contianing the serializes string';
	$e[] = '';
	$e[] = '';
	echo implode("\n", $e);
}
