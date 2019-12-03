<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$type    = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'setup';
$action  = isset($_REQUEST['action']) ? $_REQUEST['action'] :  '';
if (isset($_REQUEST['delete'])) $action = 'delete'; 

$id                = isset($_REQUEST['id'])          ? $_REQUEST['id']          :  false;
$name              = isset($_REQUEST['name'])        ? $_REQUEST['name']        :  '';
$dtmf              = isset($_REQUEST['dtmf'])        ? $_REQUEST['dtmf']        :  '';
$activate_on       = isset($_REQUEST['activate_on']) ? $_REQUEST['activate_on'] :  '';
$application       = isset($_REQUEST['application']) ? $_REQUEST['application'] :  '';
$arguments         = isset($_REQUEST['arguments'])   ? $_REQUEST['arguments']   :  '';
$moh_class         = isset($_REQUEST['moh_class'])   ? $_REQUEST['moh_class']   :  '';

switch ($action) {
    case 'add':
        $_REQUEST['extdisplay'] = dynamicfeatures_add($name, $dtmf, $activate_on, $application, $arguments, $moh_class);
        needreload();
        redirect_standard('extdisplay');
    break;
    case 'edit':
        dynamicfeatures_edit($id, $name, $dtmf, $activate_on, $application, $arguments, $moh_class);
        needreload();
        redirect_standard('extdisplay');
    break;
    case 'delete':
        dynamicfeatures_delete($id);
        needreload();
        redirect_standard();
    break;
}

?> 

<div class="rnav"><ul>
<?php 

echo '<li><a href="config.php?display=dynamicfeatures&amp;type='.$type.'">'._('Add Dynamic Feature').'</a></li>';

foreach (dynamicfeatures_list() as $row) {
    echo '<li><a href="config.php?display=dynamicfeatures&amp;type='.$type.'&amp;extdisplay='.$row['id'].'" class="">'.$row['name'].'</a></li>';
}

?>
</ul></div>

<?php

if ($extdisplay) {
    // load
    $row = dynamicfeatures_get($extdisplay);
    
    $name        = $row['name'];
    $dtmf        = $row['dtmf'];
    $activate_on = $row['activate_on'];
    $application = $row['application'];
    $arguments   = $row['arguments'];
    $moh_class   = $row['moh_class'];

    echo "<h2>"._("Edit: ")."$name ($dtmf)"."</h2>";
} else {
    echo "<h2>"._("Add Dynamic Feature")."</h2>";
}

$helptext = _("Dynamic Features allow you to define custom feature codes  mapped to Asterisk applications. In this way you can trigger some action over an active call by dialing the configured feature code");
echo $helptext;
?>

<form name="editDynamicFeature" action="<?php  $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkDynamicFeature(editDynamicFeature);">
    <input type="hidden" name="extdisplay" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="id" value="<?php echo $extdisplay; ?>">
    <input type="hidden" name="action" value="<?php echo ($extdisplay ? 'edit' : 'add'); ?>">
    <table>
    <tr><td colspan="2"><h5><?php  echo ($extdisplay ? _("Edit Dynamic Feature") : _("Add Dynamic Feature")) ?><hr></h5></td></tr>
    <tr>
        <td><a href="#" class="info"><?php echo _("Name")?>:<span><?php echo _("The descriptive name of this dynamic feature. For example \"playback_rules\"")?></span></a></td>
        <td><input size="30" type="text" name="name" value="<?php  echo $name; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("DTMF")?>:<span><?php echo _("The DTMF sequence to trigger this dynamic feature")?></span></a></td>
        <td><input size="14" type="text" name="dtmf" value="<?php echo $dtmf; ?>"  tabindex="<?php echo ++$tabindex;?>"/></td> 
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Activate On")?>:<span><?php echo _("On what leg to execute the application, could be set to 'self' or 'peer'")?></span></a></td>
        <td>
            <select name="activate_on"  tabindex="<?php echo ++$tabindex;?>"/>
                <option value='self' <?php if($activate_on=='self') echo  " selected "; ?>>self</option>
                <option value='peer' <?php if($activate_on=='peer') echo  " selected "; ?>>peer</option>
            </select>
        </td> 
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Application")?>:<span><?php echo _("The application to run")?></span></a></td>
        <td><input size="14" type="text" name="application" value="<?php echo $application; ?>"  tabindex="<?php echo ++$tabindex;?>"/></td> 
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Arguments")?>:<span><?php echo _("Arguments to pass to the application")?></span></a></td>
        <td><input size="14" type="text" name="arguments" value="<?php echo $arguments; ?>"  tabindex="<?php echo ++$tabindex;?>"/></td> 
    </tr>

    <tr>
        <td><a href="#" class="info"><?php echo _("Music on Hold")?>:<span><?php echo _("Music on Hold class to play to other leg while application is being run")?></span></a></td>
        <td>
            <select name="moh_class" tabindex="<?php echo ++$tabindex;?>">
            <?php
            $tresults = music_list();
            $cur = (isset($moh_class) && $moh_class != "" ? $moh_class : 'default');
            if (isset($tresults[0])) {
                foreach ($tresults as $tresult) {
                   ($tresult == 'none' ? $ttext = _("No Music") : $ttext = $tresult);
                   ($tresult == 'default' ? $ttext = _("Default") : $ttext = $tresult);
                   echo '<option value="'.$tresult.'"'.($tresult == $cur ? ' SELECTED' : '').'>'._($ttext)."</option>\n";
                }
            }
            ?>
            </select>
       </td>
    </tr>

    <tr>
        <td colspan="2"><br><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>">
            <?php if ($extdisplay) { echo '&nbsp;<input name="delete" type="submit" value="'._("Delete").'">'; } ?>
        </td>
    </tr>



</table>
</form>

<script language="javascript">
<!--

function checkDynamicFeature(theForm) {
    var msgInvalidDescription = "<?php echo _('Invalid name specified'); ?>";

    // form validation
    defaultEmptyOK = false;    
    if (isEmpty(theForm.name.value))
        return warnInvalid(theForm.name, msgInvalidDescription);

    return true;
}
//-->
</script>
