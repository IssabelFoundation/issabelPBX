<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

//Get Future Module information (This is only used for a few things right now)
$root = dirname(__FILE__);
$cid_modules = array();
foreach (glob($root."/modules/*",GLOB_ONLYDIR) as $filename) {
    $cid_modules[] = basename($filename);
}

isset($_REQUEST['action']) ? ($action = $_REQUEST['action']) : $action='';
isset($_REQUEST['itemid']) ? ($itemid = $_REQUEST['itemid']) : $itemid='';

$tabindex = 0;

//if submitting form, update database
if(isset($_REQUEST['action'])) {
	switch ($action) {
		case "add":
			cidlookup_add($_REQUEST);
			needreload();
			redirect_standard();
		break;
		case "delete":
			cidlookup_del($itemid);
			needreload();
			redirect_standard();
		break;
		case "edit":
			cidlookup_edit($itemid,$_REQUEST);
			needreload();
			redirect_standard('itemid');
		break;
	}
}

//get list of CallerID lookup sources
$cidsources = cidlookup_list();

if ($action != 'delete') {
    if ($itemid){
        $thisItem = cidlookup_get($itemid);
        $dids_using_arr = cidlookup_did_list($itemid);
        $dids_using = count($dids_using_arr);
        $thisItem_description = isset($thisItem['description']) ? htmlspecialchars($thisItem['description']):'';
    } else {
        $thisItem = Array( 'description' => '', 'sourcetype' => null, 'cache' => null);
    }
}

require_once('views/main.html.php');
?>
<script>
var cid_modules = <?php echo json_encode($cid_modules)?>
</script>
