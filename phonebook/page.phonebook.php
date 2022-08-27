<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2022 Issabel Foundation
//	Copyright 2013 Schmooze Com Inc.
//  Copyright (C) 2006 WeBRainstorm S.r.l. (ask@webrainstorm.it)
//

isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';
isset($_REQUEST['number'])?$number = $_REQUEST['number']:$number='';
isset($_REQUEST['name'])?$name = $_REQUEST['name']:$name='';
isset($_REQUEST['speeddial'])?$speeddial = $_REQUEST['speeddial']:$speeddial='';
isset($_REQUEST['gensd'])?$gensd = $_REQUEST['gensd']:$gensd='';

isset($_REQUEST['editnumber'])?$editnumber = $_REQUEST['editnumber']:$editnumber='';

$dispnum = "phonebook"; //used for switch on config.php

//if submitting form, update database

if(isset($_REQUEST['action'])) {
	switch ($action) {
		case "add":
			phonebook_add($number, $name, $speeddial, $gensd);
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been added'));
            $_SESSION['msgtype']='success';
			redirect_standard();
		exit;
		break;
		case "delete":
			$numbers = phonebook_list();
			phonebook_del($number, $numbers[$number]['speeddial']);
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
            $_SESSION['msgtype']='warning';
			redirect_standard();
		break;
		case "edit":
			$numbers = phonebook_list();
			phonebook_del($editnumber, $numbers[$editnumber]['speeddial']);
			phonebook_add($number, $name, $speeddial, $gensd);
            $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
            $_SESSION['msgtype']='success';
			redirect_standard('extdisplay');
		break;
		case "empty":
			phonebook_empty();
		break;
		case "import":
			$i = 0; // imported lines
            if(is_uploaded_file($_FILES['csv']['tmp_name'])) {
                $lines = file($_FILES['csv']['tmp_name']);
				if (is_array($lines))	{
                    $n = count($lines); // total lines
                    foreach($lines as $line) {
						$fields = phonebook_fgetcsvfromline($line, 3);
                        $fields = array_map('trim', $fields);

						if (is_array($fields) && count($fields) == 3 
							&& is_numeric($fields[2]) 
							&&  ($fields[3] == '' || is_numeric($fields[3]))
						) {
							phonebook_del($fields[2], $numbers[$fields[2]]['speeddial']);
							phonebook_add(htmlentities($fields[2],ENT_QUOTES, 'UTF-8'),
							 				addslashes(htmlentities($fields[1],ENT_QUOTES, 'UTF-8')),
							 				htmlentities($fields[3],ENT_QUOTES, 'UTF-8'), true);
							$i++;
						}
                    }
					redirect_standard();
				}
			} else
				$n = 0; // total lines if no file
    break;
		case "export":
			header('Content-Type: text/csv');
			header('Content-disposition: attachment; filename=phonebook.csv');
			$numbers = phonebook_list();
			foreach ($numbers as $number => $values)
				printf("\"%s\";%s;%s\n", $values['name'], trim($number), $values['speeddial']);
            exit;
		break;
    }
}
$rnavitems = array();
$numbers = phonebook_list();
foreach ($numbers as $num=>$values) {
    $rnavitems[]=array(trim($num),$values['name'].' '.$num,$values['speeddial'],'');
}
drawListMenu($rnavitems, $type, $display, $extdisplay);
?>
<div class='content'>
<?php

    
$helptext = _('Use this module to create system wide speed dial numbers that can be dialed from any phone.');
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';


if ($extdisplay) {
    // load
    $name  = $numbers[$extdisplay]['name'];
    $sdial = $numbers[$extdisplay]['speeddial'];
} else {
    $name = '';
    $sdial = '';
}

echo "<div class='is-flex'><h2>".($extdisplay ? _('Edit Phonebook Entry').': '.$name : _("Add Phonebook Entry"))."</h2>$help</div>\n";
?>

<form id="mainform" autocomplete="off" name="edit" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return edit_onsubmit(this);">
<input type="hidden" name="display" value="<?php echo $dispnum?>">
<input type="hidden" name="action" value="add">
<input type="hidden" name="editnumber" value="<?php echo ($extdisplay)?$extdisplay:'';?>">

<table class='table is-borderless is-narrow'>
    <tr><td colspan="2"><h5><?php echo dgettext('amp','General Settings');?></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Name")?><span><?php echo _("Enter the name")?></span></a></td>
        <td><input class='input' type="text" name="name" tabindex="<?php echo ++$tabindex;?>" value='<?php echo $name;?>'></td>
	</tr>
	
	<tr>
		<td><a href="#" class="info"><?php echo _("Number")?>
		<span><?php echo _("Enter the number (For CallerID lookup to work it should match the CallerID received from network)")?></span></a></td>
        <td><input class='input' type="text" name="number" tabindex="<?php echo ++$tabindex;?>" value='<?php echo $extdisplay;?>'></td>
	</tr>

	<tr>
		<td><a href="#" class="info"><?php echo _("Speed dial code")?><span><?php echo _("Enter a speed dial code<br/>Speeddial module is required to use speeddial codes")?></span></a></td>
        <td><input class='input' type="text" name="speeddial" tabindex="<?php echo ++$tabindex;?>" value='<?php echo $sdial;?>'></td>
	</tr>

    <tr>
		<td><a href="#" class="info"><?php echo _("Set Speed Dial?"); ?><span><?php echo _("Check to have a speed dial created automatically for this number"); ?></span></a></td>
        <td>
           <div class='field'><input type='checkbox' class='switch' id='gensd' name='gensd' value='yes' checked='checked' tabindex='<?php echo ++$tabindex;?>'/><label style='height:auto; line-height:1em; padding-left:3em;' for='gensd'>&nbsp;</label></div>
        </td>
    </tr>
</form>

<form autocomplete="off" enctype="multipart/form-data" name="import" id="importform" method="post" onsubmit='return import_onsubmit();'>
<input type="hidden" name="MAX_FILE_SIZE" value="30000">
<input type="hidden" name="display" value="<?php echo $dispnum?>">
<input type="hidden" name="action" value="import">

	<tr><td colspan="2"><h5><?php echo _("Import from CSV") ?></h5></td></tr>

        <tr>
                <td><a href="#" class="info"><?php echo _("File")?>
                <span><?php echo _("Import a CSV File formatted as follows:<br/>\"Name\";Number;Speeddial<br /> Names should be enclosed by '\"' and fields separated by ';' <br /><br /> Example:<br/>\"John Doe\";12345678;123")?></span></a></td>
                <td>
<!--input type="file" name="csv" tabindex="<?php echo ++$tabindex;?>"-->


<div class="file has-name is-fullwidth has-addons">
  <label class="file-label">
    <input class="file-input" type="file" name="csv" id="csv" tabindex="<?php echo ++$tabindex;?>">
    <span class="file-cta">
      <span class="file-icon">
        <i class="fa fa-upload"></i>
      </span>
      <span class="file-label"><?php echo _('Choose a file...');?></span>
    </span>
    <span class="file-name" id="selected_file_name">
    </span>
  </label>
  <div class="control"><input type="submit" style="font-size:0.85em;" class="button is-small is-info" value="<?php echo _('Upload');?>"/></div>
</div>



                </td>
        </tr>

</table>
</form>
<script>

function import_onsubmit(theForm) {
    var msgInvalidFile = "<?php echo _("Please select a file"); ?>";

    if($('#csv').val()=='')
        return warnInvalid($('#csv')[0],msgInvalidFile);

    $.LoadingOverlay('show');
    return true;
}

function edit_onsubmit(theForm) {
	var msgInvalidNumber = "<?php echo _("Please enter a valid Number"); ?>";
	var msgInvalidName = "<?php echo _("Please enter a valid Name"); ?>";
	var msgInvalidCode = "<?php echo _("Please enter a valid Speeddial code or leave it empty"); ?>";
	defaultEmptyOK = false;
	if (!isInteger(theForm.number.value))
		return warnInvalid(theForm.number, msgInvalidNumber);
	
	defaultEmptyOK = true;
	if (!isInteger(theForm.speeddial.value))
		return warnInvalid(theForm.speeddial, msgInvalidCode);

    $.LoadingOverlay('show');    
	return true;
}

$(function(){
const fileInput = document.querySelector("input[type=file]");
  fileInput.onchange = () => {
    if (fileInput.files.length > 0) {
      const fileName = document.querySelector(".file-name");
      fileName.textContent = fileInput.files[0].name;
    }
}
})


<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
