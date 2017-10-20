<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'setup';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] :  '';
if (isset($_REQUEST['delete'])) $action = 'delete'; 

$cid_id = isset($_REQUEST['cid_id']) ? $_REQUEST['cid_id'] :  false;
$description = isset($_REQUEST['description']) ? $_REQUEST['description'] :  '';
$cid_name = isset($_REQUEST['cid_name']) ? $_REQUEST['cid_name'] :  '';
$cid_num = isset($_REQUEST['cid_num']) ? $_REQUEST['cid_num'] :  '';
$dest = isset($_REQUEST['dest']) ? $_REQUEST['dest'] :  '';

if (isset($_REQUEST['goto0']) && $_REQUEST['goto0']) {
	$dest = $_REQUEST[ $_REQUEST['goto0'].'0' ];
}

switch ($action) {
	case 'add':
		setcid_add($description, $cid_name, $cid_num, $dest);
		needreload();
		redirect_standard();
	break;
	case 'edit':
		setcid_edit($cid_id, $description, $cid_name, $cid_num, $dest);
		needreload();
		redirect_standard('extdisplay');
	break;
	case 'delete':
		setcid_delete($cid_id);
		needreload();
		redirect_standard();
	break;
}

?> 
<div class="rnav"><ul>
<?php 

echo '<li><a href="config.php?display=setcid&amp;type='.$type.'">'._('Add CallerID').'</a></li>';

foreach (setcid_list() as $row) {
	echo '<li><a href="config.php?display=setcid&amp;type='.$type.'&amp;extdisplay='.$row['cid_id'].'" class="rnavdata" rnavdata="'.$row['description'].','.$row['cid_name'].','.$row['cid_num'].','.$row['dest'].'">'.$row['description'].'</a></li>';

}

?>
</ul></div>
<?php

if ($extdisplay) {
	// load
	$row = setcid_get($extdisplay);
	$description = $row['description'];
	$cid_name   = htmlspecialchars($row['cid_name']);
	$cid_num   = htmlspecialchars($row['cid_num']);
	$dest      = $row['dest'];

	echo "<h2>"._("Edit: ")."$description ($cid_name)"."</h2>";

		$usage_list = framework_display_destination_usage(setcid_getdest($extdisplay));
		if (!empty($usage_list)) {
		?>
			<tr><td colspan="2">
			<a href="#" class="info"><?php echo $usage_list['text']?>:<span><?php echo $usage_list['tooltip']?></span></a>
			</td></tr><br /><br />
		<?php
		}

} else {
	echo "<h2>"._("Add CallerID")."</h2>";
}

$helptext = _("Set CallerID allows you to change the caller id of the call and then continue on to the desired destination. For example, you may want to change the caller id form \"John Doe\" to \"Sales: John Doe\". Please note, the text you enter is what the callerid is changed to. To append to the current callerid, use the proper asterisk variables, such as \"\${CALLERID(name)}\" for the currently set callerid name and \"\${CALLERID(num)}\" for the currently set callerid number.");
echo $helptext;
echo $row['dest'];



?>

<form name="editSetcid" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkSetcid(editSetcid);">
	<input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
	<input type="hidden" name="cid_id" value="<?php echo $extdisplay; ?>">
	<input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
	<table>
	<tr><td colspan="2"><h5><?php  echo ($extdisplay ? _("Edit CallerID Instance") : _("Add CallerID Instance")) ?><hr></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Description")?>:<span><?php echo _("The descriptive name of this CallerID instance. For example \"new name here\"");?></span></a></td>
		<td><input size="30" type="text" name="description" value="<?php  echo $description; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("CallerID Name")?>:<span><?php echo _("The CallerID Name that you want to change to. If you are appending to the current callerid, dont forget to include the appropriate asterisk variables. If you leave this box blank, the CallerID name will be blanked");?></span></a></td>
		<td><input size="30" type="text" name="cid_name" value="<?php echo $cid_name; ?>"  tabindex="<?php echo ++$tabindex;?>"/></td> </tr>
    <td><a href="#" class="info"><?php echo _("CallerID Number")?>:<span><?php echo _("The CallerID Number that you want to change to. If you are appending to the current callerid, dont forget to include the appropriate asterisk variables. If you leave this box blank, the CallerID number will be blanked");?></span></a></td>
		<td><input size="30" type="text" name="cid_num" value="<?php echo $cid_num; ?>"  tabindex="<?php echo ++$tabindex;?>"/></td> </tr>
	<tr><td colspan="2"><br><h5><?php echo _("Destination")?>:<hr></h5></td></tr>

<?php 
//draw goto selects
echo drawselects($dest,0);
?>
			
	<tr>
		<td colspan="2"><br><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>">
			<?php if ($extdisplay) { echo '&nbsp;<input name="delete" type="submit" value="'._("Delete").'">'; } ?>
		</td>		

	</tr>
</table>
</form>

<script language="javascript">
<!--
$(document).ready(function () {

  if (!$('[name=description]').attr("value")) {
  $('[name=cid_name]').attr({value: "${CALLERID(name)}"});
  $('[name=cid_num]').attr({value: "${CALLERID(num)}"});
    }
    
 // select rnav options - fake type = edit
 /*
  $("a.rnavdata").click(function(event){
  event.preventDefault();
  linktext = $(this).text();
  rnavdata = $(this).attr("rnavdata");
  arr = rnavdata.split(",");
  $('h2').text("<?php echo _("Edit") ?>: " + arr[0]);
	$('[name=description]').attr({value: arr[0]});
  $('[name=cid_name]').attr({value: arr[1]});
  $('[name=cid_num]').attr({value: arr[2]});
  });
  */
});


function checkSetcid(theForm) {
	var msgInvalidDescription = "<?php echo _('Invalid description specified'); ?>";

	// set up the Destination stuff
	setDestinations(theForm, '_post_dest');

	// form validation
	defaultEmptyOK = false;	
	if (isEmpty(theForm.description.value))
		return warnInvalid(theForm.description, msgInvalidDescription);

	if (!validateDestinations(theForm, 1, true))
		return false;

	return true;
}
//-->
</script>
