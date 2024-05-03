<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$get_vars = array(
    'ecm'              => '',
    'fax_rx_email'     => '',
    'force_detection'  => 'no',
    'headerinfo'       => '',
    'legacy_mode'      => 'no',
    'localstationid'   => '',
    'maxrate'          => '',
    'minrate'          => '',
    'modem'            => '',
    'sender_address'   => '',
);
foreach($get_vars as $k => $v){
    $fax[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
}

$tabindex = 0;
// get/put options
if (isset($_REQUEST['action']) &&  $_REQUEST['action'] == 'edit'){
    fax_save_settings($fax);
        $_SESSION['msg']=base64_encode(_dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        $_SESSION['msgtstamp']=time();
        $_REQUEST['action']='';
        redirect_standard('action');
}
$fax = array_merge($fax, fax_get_settings());
$action = '';//no action to do

$fax_detect = fax_detect();
$trans_rates = array(
                    '2400'    => '2400',
                    '4800'    => '4800',
                    '7200'    => '7200',
                    '9600'    => '9600',
                    '12000'   => '12000',
                    '14400'   => '14400'
                    );
?>
<div class='content'>
<h2><?php echo __("Fax Options")?></h2>
<form id="mainform" name="edit" method=POST>
    <table id="faxoptionstable" class="table is-borderless is-narrow">
        <tbody>
            <tr><td colspan="3"><h5><?php echo __("Fax Presentation Options")?></h5></td></tr>
            <tr>
                <td><a href="#" class="info"><?php echo __("Default Fax header")?><span><?php echo __("Header information that is passed to remote side of the fax transmission and is printed on top of every page. This usually contains the name of the person or entity sending the fax.")?></span></a></td>
                <td><input class="input" type="text" name="headerinfo" value="<?php  echo $fax['headerinfo']; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
            </tr>
            <tr>
                <td><a href="#" class="info"><?php echo __("Default Local Station Identifier")?><span><?php echo __("The outgoing Fax Machine Identifier. This is usually your fax number.")?></span></a></td>
                <td><input class="input" type="text" name="localstationid" value="<?php  echo $fax['localstationid']; ?>" tabindex="<?php echo ++$tabindex;?>"></td>
            </tr>
            <tr>
                <td><a class="info" href="#"><?php echo __("Outgoing Email address")?><span><?php echo __("Email address that faxes appear to come from if 'system default' has been chosen as the default fax extension.")?></span></a></td>
                <td><input type="text" class="input" name="sender_address" value="<?php  echo htmlspecialchars($fax['sender_address'])?>" tabindex="<?php echo ++$tabindex;?>"/></td>
            </tr>
            <tr><td colspan="3"><h5><?php echo __("Fax Feature Code Options")?></h5></td></tr>
            <tr>
                <td><a class="info" href="#"><?php echo __("Email address")?><span><?php echo __("Email address that faxes are sent to when using the \"Dial System Fax\" feature code. This is also the default email for fax detection in legacy mode, if there are routes still running in this mode that do not have email addresses specified.")?></span></a></td>
                <td><input type="text" class="input" name="fax_rx_email" value="<?php  echo htmlspecialchars($fax['fax_rx_email'])?>" tabindex="<?php echo ++$tabindex;?>"/></td>
            </tr>
            <tr><td colspan="3"><h5><?php echo __("Fax Transport Options")?></h5></td></tr>
            <tr><td><?php echo ipbx_label(__("Error Correction Mode"),
            __("Error Correction Mode (ECM) option is used to specify whether to use ecm mode or not.")); ?></td>
                <td>
                <?php
                $val =  ($fax['ecm'] == 'yes')?'yes':'no';
                echo ipbx_radio('ecm',array(array('value'=>'yes','text'=>_dgettext('amp','Yes')),array('value'=>'no','text'=>_dgettext('amp','No'))),$val,false);
                ?>
                </td>
            </tr>
            <tr>
                <td><?php echo ipbx_label(__("Maximum transfer rate"),
                __("Maximum transfer rate used during fax rate negotiation.")); ?></td>
                <td><?php echo form_dropdown('maxrate', $trans_rates, $fax['maxrate'],' class="componentSelect" '); ?></td>
            </tr>
            <tr>
                <td><?php echo ipbx_label(__("Minimum transfer rate"),
                __("Minimum transfer rate used during fax rate negotiation.")); ?></td>
                <td><?php echo form_dropdown('minrate', $trans_rates, $fax['minrate'], ' class="componentSelect" '); ?></td>
            </tr>
            <tr><td colspan="3"><h5><?php echo __("Fax Module Options")?></h5></td></tr>
            <tr>
                <td><a href="#" class="info"><?php echo __("Always Allow Legacy Mode")?><span><?php echo __("In earlier versions, it was possible to provide an email address with the incoming FAX detection to route faxes that were being handled by fax-to-email detection. This has been deprecated in favor of Extension/User FAX destinations where an email address can be provided. During migration, the old email address remains present for routes configured this way but goes away once 'properly' configured. This options forces the Legacy Mode to always be present as an option.")?></span></a></td>
    <td>

<?php
    $val =  ($fax['legacy_mode'] == 'yes')?'yes':'no';
    echo ipbx_radio('legacy_mode',array(array('value'=>'yes','text'=>_dgettext('amp','Yes')),array('value'=>'no','text'=>_dgettext('amp','No'))),$val,false);
?>
</td>
            </tr>

<?php if(!$fax_detect['module']){ ?>
            <tr>
                <td><a href="#" class="info"><?php echo __("Always Generate Detection Code")?>:<span><?php echo __("When no fax modules are detected the module will not generate any detection dialplan by default. If the system is being used with phyical FAX devices, hylafax + iaxmodem, or other outside fax setups you can force the dialplan to be generated here.")?></span></a></td>
        <td><span class="radioset"><input type="radio" name="force_detection" id="force_detection_yes" value="yes" <?php echo (($fax['force_detection'] == 'yes')?'checked':''); ?> tabindex="<?php echo ++$tabindex;?>"><label for="force_detection_yes"><?php echo __("Yes")?></label>
        <input type="radio" name="force_detection" value="no" id="force_detection_no" <?php echo (($fax['force_detection'] == 'no')?'checked':''); ?> tabindex="<?php echo ++$tabindex;?>"><label for="force_detection_no"><?php echo __("No")?></label></span></td>
            </tr>
<?php } ?>
    </tbody>
    </table>
    <br />

    <input type="hidden" value="fax" name="display"/>
    <input type="hidden" name="action" value="edit">

</form>
<?php
//add hooks
echo $module_hook->hookHtml;
?>
<script>
<?php echo js_display_confirmation_toasts(); ?>
</script>
</div>
<?php echo form_action_bar(''); ?>
