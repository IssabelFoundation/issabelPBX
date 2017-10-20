<?php /* $id:$ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
function queues_ivr_delete_event($id = '') {
	global $db;
	
	if (!$id) {
		sql('UPDATE queues_config SET ivr_id = ""');
	} else {
		$sql = 'UPDATE queues_config SET ivr_id = "" WHERE ivr_id = ?';
		$ret = $db->query($sql, array($id));
	}
}

function queues_configprocess_ivr() {
	$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$display = isset($_REQUEST['display'])?$_REQUEST['display']:null;
	$id = isset($_REQUEST['id'])?$_REQUEST['id']:null;

	
	if($display == 'ivr' && $action == 'delete') {
		queues_ivr_delete_event($id);
	}
	
}
?>