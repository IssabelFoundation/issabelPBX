<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
$get_vars = array(
				'action'			=> '',
				'display'			=> '',
				'id'				=> '',
				'path'				=> '',
				'restore_path'		=> '',
				'restore_source'	=> '',
				'restore'			=> '',
				'submit'			=> '',
				'upload'			=> ''
				);

foreach ($get_vars as $k => $v) {
	$var[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
}

//set action to delete if delete was pressed instead of submit
if ($var['submit'] == _('Download') && $var['action'] == 'backup_list') {
	$var['action'] = 'download';
}

//set action to view if only id is set
if ($var['action'] == '' && $var['id']) {
	$var['action'] = 'browseserver';
}

//action actions
switch ($var['action']) {
	case 'download':
		$var['restore_path'] = backup_restore_locate_file($var['id'], $var['restore_path']);
		$_SESSION['backup_restore_path'] = $var['restore_path'];
		download_file($var['restore_path']);
		break;
	case 'upload':

		//make sure our file was uploaded
		if (!is_uploaded_file($_FILES['upload']['tmp_name'])) {
			echo _('Error uploading file!');
			$var['action'] = '';
			break;

		}
		
		//ensure uploaded file is a valid tar file
		exec(ipbx_which('tar') . ' -tf ' . $_FILES['upload']['tmp_name'], $array, $ret_code);
		if ($ret_code !== 0) {
			echo _('Error verifying uploaded file!');
			$var['action'] = '';
			break;
		}
		
		$dest = $amp_conf['ASTSPOOLDIR'] 
				. '/tmp/' 
				. 'backuptmp-suser-'
				. time() . '-'
				. basename($_FILES['upload']['name']);
		move_uploaded_file($_FILES['upload']['tmp_name'], $dest);
		
		//$var['restore_path'] = $dest;
		$_SESSION['backup_restore_path'] = $dest;
		break;
	case 'list_dir':
		echo json_encode(backup_jstree_list_dir($var['id'], $var['path']));
		exit;
		break;
	case 'backup_list':
		//prepare file + ensure that its local
		if(!isset($_SESSION['backup_restore_path'])) {
			$var['restore_path'] = backup_restore_locate_file($var['id'], $var['restore_path']);
			
			/*
			 * being that this is an absolute file path 
			 * and being that we arent going to be sanitizing/sanity checking this path anymore
			 * store it in the session so that the user cant manipulate it
			 */
			$_SESSION['backup_restore_path'] = $var['restore_path'];
		}
		break;
	case 'restore':
	case 'restore_post':
	case 'restore_get':
		//dont clear $_SESSION['backup_restore_path'] which happnes every action
		//that doesnt have a case here
		break;
	default:
		//if backup_restore_path is already set, we probobly dont want that any more. delete it
		if (isset($_SESSION['backup_restore_path'])) {
			unset($_SESSION['backup_restore_path']);
		}
		break;
}

//rnav
$var['servers'] = backup_get_server('all');
echo load_view(dirname(__FILE__) . '/views/rnav/restore.php', $var);;


//view actions
switch ($var['action']) {
	case 'browseserver':
		echo load_view(dirname(__FILE__) . '/views/restore/browseserver.php', $var);
		break;
	case 'upload':
	case 'backup_list':
		$var['servers'] = backup_get_server('all');
		$var['templates'] = backup_get_template('all_detailed');
		
		//transalate variables
		//TODO: make this anonymous once we require php 5.3
		function callback(&$var) {
			$var = backup__($var);
		}
		array_walk_recursive($var['servers'], 'callback');
		array_walk_recursive($var['templates'], 'callback');
		
		if (is_array($_SESSION['backup_restore_path'])) {
			//TODO: if $var['restore_path'] is an array, that means it contains an error + error
			// message. Do something with the error meesage
			echo _('Invalid backup for or undefined error');
			break;
		}
		
		//try to get a manifest, and continue if we did
		if ($var['manifest'] = backup_get_manifest_tarball($_SESSION['backup_restore_path'])) {
			echo load_view(dirname(__FILE__) . '/views/restore/backup_list.php', $var);
			break;
		}
		
		//we didnt get a manifest. Maybe this is a legacy backup?
		$var['restore_path']	= backup_migrate_legacy($_SESSION['backup_restore_path']);
		$var['manifest']		= backup_get_manifest_tarball($var['restore_path']);
		if ($var['restore_path'] && $var['manifest']) {
			$_SESSION['backup_restore_path'] = $var['restore_path'];
			echo load_view(dirname(__FILE__) . '/views/restore/backup_list.php', $var);
			break;
		}
		
		//still here? oops, something is really broken
		echo _('Invalid backup for or undefined error');

		dbug($_SESSION['backup_restore_path'], $var);
		break;
	case 'restore_post':
		while (ob_get_level()) {
			ob_end_clean();
		}
		$_SESSION['backup_restore_data'] = $var['restore'];
		exit();
		break;
	case 'restore':
	case 'restore_get':
		
		//if action is restore_get, get restore data from session
		$restore = $var['action'] == 'restore_get' 
					? $_SESSION['backup_restore_data']
					: $var['restore'];
		
		//dont stop until were all done 
        //restore will compelte EVEN IS USER NAVIGATES AWAY FROM PAGE!! 
        ignore_user_abort(true); 
 
        //clear all buffers, those will interfere with the stream 
        while (ob_get_level()) { 
            ob_end_clean(); 
        } 
  
        ob_start(); 
        header('Content-Type: text/event-stream'); 
        header('Cache-Control: no-cache');
        $cmd = $amp_conf['AMPBIN'] . '/restore.php --restore='
                . $_SESSION['backup_restore_path'] 
                . ' --items=' 
                . base64_encode(serialize($restore))
                . ' 2>&1';
        
        //start running backup
        $run = popen($cmd, 'r');
        while (($msg = fgets($run)) !== false) {
            //dbug('backup', $msg);
            //send results back to the user
            backup_log($msg);
        }

        pclose($run);
  
        //send messgae to browser that were done
        backup_log('END');
        exit();	
		break;
	default:
		echo load_view(dirname(__FILE__) . '/views/restore/restore.php', $var);
		break;
}
?>
