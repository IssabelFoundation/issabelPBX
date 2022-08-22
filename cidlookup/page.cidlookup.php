<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

//Get Future Module information (This is only used for a few things right now)
$root = dirname(__FILE__);
$cid_modules = array();
foreach (glob($root."/modules/*",GLOB_ONLYDIR) as $filename) {
    $cid_modules[] = basename($filename);
}

isset($_REQUEST['action']) ? ($action = $_REQUEST['action']) : $action='';
isset($_REQUEST['extdisplay']) ? ($itemid = $_REQUEST['extdisplay']) : $itemid='';

$tabindex = 0;

//if submitting form, update database
if(isset($_REQUEST['action'])) {
	switch ($action) {
		case "add":
			cidlookup_add($_REQUEST);
			needreload();
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
            $_SESSION['msgtype']='success';
			redirect_standard();
		break;
		case "delete":
			cidlookup_del($itemid);
			needreload();
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
            $_SESSION['msgtype']='warning';
			redirect_standard();
		break;
		case "edit":
			cidlookup_edit($itemid,$_REQUEST);
			needreload();
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
            $_SESSION['msgtype']='success';
			redirect_standard('extdisplay');
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


errInvalidHTTPHost      = '<?php echo _('Please enter a valid HTTP(S) Host name');?>'; 
errInvalidMysqlHost     = '<?php echo _('Please enter a valid MySQL Host name');?>';
errInvalidMysqlDatabase = '<?php echo _('Please enter a valid MySQL Database name');?>';
errInvalidMysqlQuery    = '<?php echo _('Please enter a valid MySQL Query string');?>';
errInvalidMysqlUsername = '<?php echo _('Please enter a valid MySQL Username');?>';
errInvalidAccountSID    = '<?php echo _('Please enter a valid Account SID');?>';
errInvalidAuthToken     = '<?php echo _('Please enter a valid Auth Token');?>';
errInvalidDescription   = '<?php echo _('Description cannot be blank!');?>';


</script>
