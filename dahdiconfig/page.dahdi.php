<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

if (isset($_POST['reloaddahdi'])) {
    exec('asterisk -rx "module unload chan_dahdi.so"');
    exec('asterisk -rx "module load chan_dahdi.so"');
}

if (isset($_POST['restartamportal'])) {
    if(file_exists('/var/spool/asterisk/sysadmin/amportal_restart')) {
        file_put_contents('/var/spool/asterisk/sysadmin/amportal_restart',time());
    }
}

$dahdi_info = dahdiconfig_getinfo();
$dahdi_ge_260 = version_compare(dahdiconfig_getinfo('version'),'2.6.0','ge');
global $amp_conf;
$brand = $amp_conf['DASHBOARD_ISSABELPBX_BRAND']?$amp_conf['DASHBOARD_ISSABELPBX_BRAND']:'IssabelPBX';

echo "<div class='content'>";

//Check to make sure dahdi is running. Display an error if it's not
if(!preg_match('/\d/i',$dahdi_info[1])) {
    $type='is-danger';
    $dahdi_message = __("DAHDi Doesn't appear to be running. Click the 'Reload Asterisk Dahdi Module' below");
    include('views/dahdi_message_box.php');
    $dahdi_info[1] = '';
}

//Check to make sure we aren't symlinking chan_dahdi.conf like we were in the past as we don't do that anymore.
if(!$amp_conf['DAHDIDISABLEWRITE'] && is_link('/etc/asterisk/chan_dahdi.conf') && (readlink('/etc/asterisk/chan_dahdi.conf') == dirname(__FILE__).'/etc/chan_dahdi.conf')) {
    if(!unlink('/etc/asterisk/chan_dahdi.conf')) {
        //If unlink fails then alert the user
        $type='is-danger';
        $dahdi_message = sprintf(__('Please Delete the System Generated %s'),"/etc/asterisk/chan_dahdi.conf");
        include('views/dahdi_message_box.php');
    }
}

$dahdi_cards = new dahdi_cards();
$error = array();

if ($dahdi_cards->hdwr_changes()) {
    $dahdi_message = __('You have new hardware! Please configure your new hardware using the edit button(s). Then reload DAHDi with the button below.');
    $type='is-success';
    include('views/dahdi_message_box.php');
    if(file_exists($amp_conf['ASTETCDIR'].'/chan_dahdi_groups.conf')) {
        global $astman;
        copy($amp_conf['ASTETCDIR'].'/chan_dahdi_groups.conf', $amp_conf['ASTETCDIR'].'/chan_dahdi_groups.conf.bak');
        file_put_contents($amp_conf['ASTETCDIR'].'/chan_dahdi_groups.conf', '');
        exec('asterisk -rx "module unload chan_dahdi.so"');
        exec('asterisk -rx "module load chan_dahdi.so"');
        $astman->send_request('Command', array('Command' => 'dahdi restart'));
    }
}
?>
<div id="reboot_mods" class="notification is-warning" style='display:none;'>
<?php echo __("For your hardware changes to take effect, you need to reboot your system! (Or press the 'Restart DAHDi and Asterisk' button after pressing the 'Apply Changes' button)");?>
</div>
<div id="reboot_mp" class="notification is-warning" style='display:none;'>
<?php echo __('For your hardware changes to take effect, you need to reboot your system!');?>
</div>
<div id="reboot" class="notification is-warning" style='display:none;'>
<?php echo __("For your changes to take effect, click the 'Apply Changes' button and then the 'Reload Asterisk Dahdi Module' below");?>
</div>

<script type="text/javascript" src="assets/dahdiconfig/js/jquery.form.js"></script>
  <!-- right side menu -->
  <div class="rnav">
    <ul>
      <li style="font-sze:1.2em"><strong><?php echo __('Settings')?></strong></li>
      <li>
        <a href='javascript:void(0)' class="js-modal-trigger" data-target="globalsettings" ><?php echo __('Global Settings')?></a>
      </li>
      <li>
        <a href='javascript:void(0)' class="js-modal-trigger" data-target="syssettings" ><?php echo __('System Settings')?></a>
      </li>
      <li>
        <a href='javascript:void(0)' class="js-modal-trigger" data-target="modprobesettings" ><?php echo __('Modprobe Settings')?></a>
      </li>
      <li>
        <a href='javascript:void(0)' class="js-modal-trigger" data-target="modulesettings" ><?php echo __('Module Settings')?></a>
      </li>
      <?php
      foreach($dahdi_cards->modules as $mod_name => $module) {
        if(method_exists($module,'settings')) {
          $out = $module->settings();
          ?>
          <li>
              <a href='javascript:void(0)' class="js-modal-trigger" data-target="<?php echo $mod_name?>settings" ><?php echo __($out['title'])?></a>
          </li>
          <?php
        }
      }
      ?>
    </ul>
    <br /> <br /> <br /> <br /> <br /> <br /> <br />

           </div>

        <?php require dirname(__FILE__).'/views/dahdi_global_settings.php'; ?>
        <?php require dirname(__FILE__).'/views/dahdi_system_settings.php'; ?>
        <?php require dirname(__FILE__).'/views/dahdi_modprobe_settings.php'; ?>
        <?php $mods = $dahdi_cards->get_all_modules(); ?>
        <?php require dirname(__FILE__).'/views/dahdi_modules_settings.php'; ?>
        <?php
        foreach($dahdi_cards->modules as $mod_name => $module) {
            if(method_exists($module,'settings')) {
                $out = $module->settings();
                ?>

                <div class="modal animate__animated animate__fadeIn" id="<?php echo $mod_name?>settings">
                  <div class="modal-background"></div>
                  <div class="modal-card">
                    <header class="modal-card-head">
                      <p class="modal-card-title" style="margin-bottom:0;"><?php echo __('Global Settings')?></p>
                      <button class="delete" aria-label="close"></button>
                    </header>
                    <section class="modal-card-body">

                    <form id="form-<?php echo $mod_name?>settings" action="config.php?quietmode=1&amp;handler=file&amp;module=dahdiconfig&amp;file=ajax.html.php&amp;type=<?php echo $mod_name?>settingssubmit">
                        <?php echo $out['html']?>
                    </form>

                    </section>
                    <footer class="modal-card-foot">
                      <button data-target="form-<?php echo $mod_name?>settings" class="button is-success formsubmit"><?php echo __('Save')?></button>
                      <button class="button"><?php echo __('Cancel')?></button>
                    </footer>
                  </div>
                </div>


                <?php
            }
        }
        ?>
        <?php foreach($dahdi_cards->get_spans() as $key=>$span) {
            $span['signalling'] = !empty($span['signalling']) ? $span['signalling'] : '';
            $span['switchtype'] = !empty($span['switchtype']) ? $span['switchtype'] : '';
            $span['pridialplan'] = !empty($span['pridialplan']) ? $span['pridialplan'] : '';
            $span['prilocaldialplan'] = !empty($span['prilocaldialplan']) ? $span['prilocaldialplan'] : '';
            $span['priexclusive'] = !empty($span['priexclusive']) ? $span['priexclusive'] : '';
            $span['txgain'] = !empty($span['txgain']) ? $span['txgain'] : '0.0';
            $span['rxgain'] = !empty($span['rxgain']) ? $span['rxgain'] : '0.0';
            ?>
        <div id="digital-settings-<?php echo $key;?>" title="Span: <?php echo $span['description']?>" style="display: none;">
            <?php require dirname(__FILE__).'/views/dahdi_digital_settings.php'; ?>
        </div>
        <?php } ?>
        <div id="analog-settings-fxo" title="<?php echo __('FXO Settings')?>" style="display: none;">
            <?php $analog_type = 'fxo'; require dirname(__FILE__).'/views/dahdi_analog_settings.php'; ?>
        </div>
        <div id="analog-settings-fxs" title="<?php echo __('FXS Settings')?>" style="display: none;">
            <?php $analog_type = 'fxs'; require dirname(__FILE__).'/views/dahdi_analog_settings.php'; ?>
        </div>
    <div id="digital_hardware">
    <?php require dirname(__FILE__).'/views/dahdi_digital_hardware.php'; ?>
    </div>
    <div id="analog_hardware">
    <?php require dirname(__FILE__).'/views/dahdi_analog_hardware.php'; ?>
    </div>
    <div class="btn_container mt-5">
        <form name="dahdi_advanced_settings" method="post" action="config.php?display=dahdi" onsubmit="$.LoadingOverlay('show')">
            <input type="submit" id="reloaddahdi" name="reloaddahdi" class="button is-small is-warning is-rounded" value="<?php echo __('Reload Asterisk Dahdi Module')?>" />
            <?php if(file_exists('/var/spool/asterisk/sysadmin/amportal_restart')) {?>
            <input type="submit" id="restartamportal" name="restartamportal" value="<?php echo __('Restart Dahdi & Asterisk')?>" />
            <?php } ?>
        </form>
    </div>
       <div id="dahdi-write" title="<?php echo __('DAHDi Write Disabled Disclaimer')?>" style="display: none;">
        <div style="text-align:center;color:red;font-weight:bold;"><?php echo __('DAHDi is DISABLED for writing')?></div>
        <br/>
        <strong><?php echo __('WARNING: When this module is "enabled" for writing it WILL overwrite the following files:')?></strong>
        <ul>
            <li><?php echo $amp_conf['ASTETCDIR']?>/chan_dahdi_general.conf</li>
            <li><?php echo $amp_conf['ASTETCDIR']?>/chan_dahdi_groups.conf</li>
            <li><?php echo $amp_conf['ASTETCDIR']?>/chan_dahdi.conf</li>
            <li><?php echo $amp_conf['DAHDISYSTEMLOC']?></li>
            <li><?php echo $amp_conf['DAHDIMODPROBELOC']?></li>
        </ul>
        <?php echo __('It is YOUR responsibility to backup all relevant files on your system!')?>
        <?php echo sprintf(__("The %s team can NOT be held responsible if you enable this module and your trunks/cards suddenly stop working because your configurations have changed."),$brand)?>
        <br />
        <br />
        <?php echo __('This module should never be used alongside "dahdi_genconfig". Using "dahdi_genconfig" and this module at the same time can have unexpected consequences.')?>
        <br />
        <br />
        <?php echo __("Because of this the module's configuration file write ability is disabled by default. You can enable it in this window or you can later enable it under Advanced Settings")?>
        <br/>
        <br/>
        <i><?php echo __("This message will re-appear everytime you load the module while it is in a disabled write state so as to not cause any confusion")?>
        </i>
    </div>
<div class='notification mt-5'><?php echo trim($dahdi_info[1]);?></div>

<script>
var dgps = new Array();
var spandata = new Array();

var modprobesettings = {}
<?php foreach($dahdi_cards->read_all_dahdi_modprobe() as $list) { ?>
modprobesettings['<?php echo $list['module_name'] ?>'] = {}
modprobesettings['<?php echo $list['module_name'] ?>']['dbsettings'] = <?php echo $list['settings'] ?>

<?php } ?>

<?php 
foreach($dahdi_cards->get_spans() as $key=>$span) {
    $o = $span;
    unset($o['additional_groups']);
    $o = json_encode($o);
?>

    spandata[<?php echo $key?>] = {};
    spandata[<?php echo $key?>]['groups'] = <?php echo !empty($span['additional_groups']) ? $span['additional_groups'] : '{}'?>;
    spandata[<?php echo $key?>]['spandata'] = <?php echo $o?>;

    $('#editspan_<?php echo $key?>_signalling').change(function() {
        if(($(this).val() == 'pri_net') || ($(this).val() == 'pri_cpe')) {
            //$('#editspan_<?php echo $key?>_reserved_ch').fadeIn('slow');
        } else {
            //$('#editspan_<?php echo $key?>_reserved_ch').fadeOut('slow');
        }
    });

<?php 
    $groups = json_decode($span['additional_groups'],TRUE);
    foreach($groups as $gkey => $data) { 
?>
        $('#editspan_<?php echo $key?>_definedchans_<?php echo $gkey?>').change(function() {
            var span = <?php echo $key?>;
            var endchan = $(this).val();
            var totalchan = <?php echo $span['totchans']?>;
            var group = <?php echo $gkey?>;
            update_digital_groups(span,group,endchan);
        });
<?php 
    } 
}
?>

$(function(){

    ipbx.msg.framework.pagereload = "<?php echo __("This will reload the page")?>";

    $('.modules-sortable').sortable();
    <?php
    if ($amp_conf['DAHDIDISABLEWRITE']) {
?>

Swal.fire({
  title: '<?php echo __("Do you want to enable configuration writes?")?>',
  html: $('#dahdi-write').html(),
  showDenyButton: true,
  showCancelButton: false,
  confirmButtonText: '<?php echo _dgettext('amp','Enable')?>',
  denyButtonText: '<?php echo _dgettext('amp','Disable')?>',
  customClass: 'swal-wide',
}).then((result) => {
  if (result.isConfirmed) {
    $.getJSON("config.php?quietmode=1&handler=file&module=dahdiconfig&file=ajax.html.php",{mode: 'enable', type: 'write'}, function(j){});
  } else if (result.isDenied) {
    $.getJSON("config.php?quietmode=1&handler=file&module=dahdiconfig&file=ajax.html.php",{mode: 'disable', type: 'write'}, function(j){});
  }
});

<?php
    }
    foreach($dahdi_cards->modules as $module) {
        if(method_exists($module,'settings')) {
            $out = $module->settings();
            echo $out['javascript'];
        }
    }
    ?>

    //On Focus of module name element then we save the local storage
    $('#module_name').on('focus',function () {
        storeModProbeSettings();
    }).on('change',function() {
        createModProbeSettings();
    })

    var options = {
        type: 'POST'
    };

    <?php foreach($dahdi_cards->get_spans() as $key=>$span) { ?>
    $("#editspan_<?php echo $key?>_signalling").on('change',function() {
        if($( this ).val().substring(0,3) == 'bri' || <?php echo $span['totchans']?> != 3 || $( this ).val().substring(0,3) == 'pri') {
            $("#editspan_<?php echo $key?>_switchtype_tr").fadeIn('slow')
            $("#editspan_<?php echo $key?>_switchtype").val('euroisdn')
        } else {
            $("#editspan_<?php echo $key?>_switchtype_tr").fadeOut('slow')
        }
    });

    <?php } ?>

});


$('#mwi').on('change',function(evt) {
    if ($('#mwi :selected').val() == 'neon') {
        $('.neon').show();
    } else {
        $('.neon').hide();
    }
});

</script>
</div>
<?php
//Easy Form Setting method
function set_default($default,$option=NULL,$true='selected') {
    if(isset($option)) {
        return $option == $default ? $true : '';
    } else {
        return isset($default) ? $default : '';
    }
}
