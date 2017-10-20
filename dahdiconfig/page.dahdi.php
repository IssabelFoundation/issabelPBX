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

//Check to make sure dahdi is running. Display an error if it's not
if(!preg_match('/\d/i',$dahdi_info[1])) {
    $dahdi_message = _("DAHDi Doesn't appear to be running. Click the 'Restart/Reload Dahdi Button' Below");
    include('views/dahdi_message_box.php');
    $dahdi_info[1] = '';
}

//Check to make sure we aren't symlinking chan_dahdi.conf like we were in the past as we don't do that anymore.
if(!$amp_conf['DAHDIDISABLEWRITE'] && is_link('/etc/asterisk/chan_dahdi.conf') && (readlink('/etc/asterisk/chan_dahdi.conf') == dirname(__FILE__).'/etc/chan_dahdi.conf')) {
    if(!unlink('/etc/asterisk/chan_dahdi.conf')) {
        //If unlink fails then alert the user
        $dahdi_message = sprintf(_('Please Delete the System Generated %s'),"/etc/asterisk/chan_dahdi.conf");
        include('views/dahdi_message_box.php');
    }
}

$dahdi_cards = new dahdi_cards();
$error = array();

if ($dahdi_cards->hdwr_changes()) {
	$dahdi_message = _('You have new hardware! Please configure your new hardware using the edit button(s). Then reload DAHDi with the button below.');
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
<div id="reboot_mods" style="display:none;background-color:#f8f8ff; border: 1px solid #aaaaff; padding:10px;font-family:arial;color:red;font-size:20px;text-align:center;font-weight:bolder;">For your hardware changes to take effect, you need to reboot your system! <br/> or <br/>Press the Restart DAHDi and Asterisk Button! <br/>After pressing the Red Apply Changes Bar.</div>
<div id="reboot_mp" style="display:none;background-color:#f8f8ff; border: 1px solid #aaaaff; padding:10px;font-family:arial;color:red;font-size:20px;text-align:center;font-weight:bolder;">For your hardware changes to take effect, you need to reboot your system!</div>
<div id="reboot" style="display:none;background-color:#f8f8ff; border: 1px solid #aaaaff; padding:10px;font-family:arial;color:red;font-size:20px;text-align:center;font-weight:bolder;">For your changes to take effect, click the 'Restart/Reload Dahdi Button' Below</div>

<script type="text/javascript" src="assets/dahdiconfig/js/jquery.form.js"></script>
  <!-- right side menu -->
  <div class="rnav">
    <ul>
      <li style="text-decoration:underline"><strong><?php echo _('Settings')?></strong></li>
      <li>
        <a href="#" onclick="dahdi_modal_settings('global');"><?php echo _('Global Settings')?></a>
      </li>
      <li>
        <a href="#" onclick="dahdi_modal_settings('system');"><?php echo _('System Settings')?></a>
      </li>
      <li>
        <a href="#" onclick="dahdi_modal_settings('modprobe');"><?php echo _('Modprobe Settings')?></a>
      </li>
      <li>
        <a href="#" onclick="dahdi_modal_settings('modules');"><?php echo _('Module Settings')?></a>
      </li>
      <?php
      foreach($dahdi_cards->modules as $mod_name => $module) {
        if(method_exists($module,'settings')) {
          $out = $module->settings();
          ?>
          <li>
            <a href="#" onclick="dahdi_modal_settings('<?php echo $mod_name?>');"><?php echo $out['title']?></a>
          </li>
          <?php
        }
      }
      ?>
    </ul>
    <br /> <br /> <br /> <br /> <br /> <br /> <br />

       	</div>

         <div id="global-settings" title="<?php echo _('Global Settings')?>" style="display: none;">
            <?php require dirname(__FILE__).'/views/dahdi_global_settings.php'; ?>
        </div>
       	<div id="system-settings" title="<?php echo _('System Settings')?>" style="display: none;">
            <?php require dirname(__FILE__).'/views/dahdi_system_settings.php'; ?>
        </div>
       	<div id="modprobe-settings" title="<?php echo _('Modprobe Settings')?>" style="display: none;">
            <?php require dirname(__FILE__).'/views/dahdi_modprobe_settings.php'; ?>
        </div>
       	<div id="modules-settings" title="<?php echo _('Module Settings')?>" style="display: none;">
			<?php $mods = $dahdi_cards->get_all_modules(); ?>
            <?php require dirname(__FILE__).'/views/dahdi_modules_settings.php'; ?>
        </div>
        <?php
        foreach($dahdi_cards->modules as $mod_name => $module) {
            if(method_exists($module,'settings')) {
                $out = $module->settings();
                ?>
                <div id="<?php echo $mod_name?>-settings" title="<?php echo $out['title']?>" style="display: none;">
                    <form id="form-<?php echo $mod_name?>settings" action="config.php?quietmode=1&amp;handler=file&amp;module=dahdiconfig&amp;file=ajax.html.php&amp;type=<?php echo $mod_name?>settingssubmit">
                        <?php echo $out['html']?>
                    </form>
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
        <div id="analog-settings-fxo" title="<?php echo _('FXO Settings')?>" style="display: none;">
            <?php $analog_type = 'fxo'; require dirname(__FILE__).'/views/dahdi_analog_settings.php'; ?>
        </div>
        <div id="analog-settings-fxs" title="<?php echo _('FXS Settings')?>" style="display: none;">
            <?php $analog_type = 'fxs'; require dirname(__FILE__).'/views/dahdi_analog_settings.php'; ?>
        </div>
	<div id="digital_hardware">
	<?php require dirname(__FILE__).'/views/dahdi_digital_hardware.php'; ?>
	</div>
	<div id="analog_hardware">
	<?php require dirname(__FILE__).'/views/dahdi_analog_hardware.php'; ?>
	</div>
	<div class="btn_container">
	    <form name="dahdi_advanced_settings" method="post" action="config.php?display=dahdi">
    	    <input type="submit" id="reloaddahdi" name="reloaddahdi" value="<?php echo _('Reload Asterisk Dahdi Module')?>" />
            <?php if(file_exists('/var/spool/asterisk/sysadmin/amportal_restart')) {?>
            <input type="submit" id="restartamportal" name="restartamportal" value="<?php echo _('Restart Dahdi & Asterisk')?>" />
            <?php } ?>
    	</form>
    </div>
   	<div id="dahdi-write" title="<?php echo _('DAHDi Write Disabled Disclaimer')?>" style="display: none;">
        <div style="text-align:center;color:red;font-weight:bold;"><?php echo _('DAHDi is DISABLED for writing')?></div>
        <br/>
        <strong><?php echo _('WARNING: When this module is "enabled" for writing it WILL overwrite the following files:')?></strong>
        <ul>
            <li><?php echo $amp_conf['ASTETCDIR']?>/chan_dahdi_general.conf</li>
            <li><?php echo $amp_conf['ASTETCDIR']?>/chan_dahdi_groups.conf</li>
            <li><?php echo $amp_conf['ASTETCDIR']?>/chan_dahdi.conf</li>
            <li><?php echo $amp_conf['DAHDISYSTEMLOC']?></li>
            <li><?php echo $amp_conf['DAHDIMODPROBELOC']?></li>
        </ul>
        <?php echo _('It is YOUR responsibility to backup all relevant files on your system!')?>
        <?php echo sprintf(_("The %s team can NOT be held responsible if you enable this module and your trunks/cards suddenly stop working because your configurations have changed."),$brand)?>
        <br />
        <br />
        <?php echo _('This module should never be used alongside "dahdi_genconfig". Using "dahdi_genconfig" and this module at the same time can have unexpected consequences.')?>
        <br />
        <br />
        <?php echo _("Because of this the module's configuration file write ability is disabled by default. You can enable it in this window or you can later enable it under Advanced Settings")?>
        <br/>
        <br/>
        <i><?php echo _("This message will re-appear everytime you load the module while it is in a disabled write state so as to not cause any confusion")?>
        </i>
    </div>
<h5><?php echo trim($dahdi_info[1]);?></h5>

<script>
var dgps = new Array();
var spandata = new Array();

var modprobesettings = {}
<?php foreach($dahdi_cards->read_all_dahdi_modprobe() as $list) { ?>
modprobesettings['<?php echo $list['module_name'] ?>'] = {}
modprobesettings['<?php echo $list['module_name'] ?>']['dbsettings'] = <?php echo $list['settings'] ?>

<?php } ?>

<?php foreach($dahdi_cards->get_spans() as $key=>$span) {
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

<?php $groups = json_decode($span['additional_groups'],TRUE);
    foreach($groups as $gkey => $data) { ?>
$('#editspan_<?php echo $key?>_definedchans_<?php echo $gkey?>').change(function() {
    var span = <?php echo $key?>;
    var endchan = $(this).val();
    var totalchan = <?php echo $span['totchans']?>;
    var group = <?php echo $gkey?>;
    update_digital_groups(span,group,endchan);
});

<?php } ?>

<?php } ?>
$(function(){
	$('.modules-sortable').sortable();
    <?php
    if ($amp_conf['DAHDIDISABLEWRITE']) {
        ?>
        $( "#dahdi-write" ).dialog({
            autoOpen: true,
            height: 400,
            width: 500,
            modal: true,
            buttons: {
                "<?php echo _('Enable')?>": function() {
                    $.getJSON("config.php?quietmode=1&handler=file&module=dahdiconfig&file=ajax.html.php",{mode: 'enable', type: 'write'}, function(j){
                    });
                    $( this ).dialog( "close" );
                },
                "<?php echo _('Disable')?>": function() {
                    $.getJSON("config.php?quietmode=1&handler=file&module=dahdiconfig&file=ajax.html.php",{mode: 'disable', type: 'write'}, function(j){
                    });
                    $( this ).dialog( "close" );
                }
            },
            close: function() {
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
    $('#module_name').focus(function () {
		storeModProbeSettings();
    }).change(function() {
        createModProbeSettings();
    })

    var options = {
        type: 'POST'
    };
    $( "#analog-settings-fxo" ).dialog({
        autoOpen: false,
        height: 400,
        width: 500,
        modal: true,
        buttons: {
            "<?php echo _('Save')?>": function() {
                $("#dahdi_editanalog_fxo").ajaxSubmit();
                $("#reboot").fadeIn(3000).show();
                toggle_reload_button('show');
                $( this ).dialog( "close" );
            },
            <?php echo _('Cancel')?>: function() {
                $( this ).dialog( "close" );
            }
        },
        close: function() {
        }
    });
    $( "#analog-settings-fxs" ).dialog({
        autoOpen: false,
        height: 400,
        width: 500,
        modal: true,
        buttons: {
            "<?php echo _('Save')?>": function() {
                $("#dahdi_editanalog_fxs").ajaxSubmit();
                $("#reboot").fadeIn(3000).show();
                toggle_reload_button('show');
                $( this ).dialog( "close" );
            },
            <?php echo _('Cancel')?>: function() {
                $( this ).dialog( "close" );
            }
        },
        close: function() {
        }
    });
    <?php foreach($dahdi_cards->get_spans() as $key=>$span) { ?>
    $( "#digital-settings-<?php echo $key?>" ).dialog({
        autoOpen: false,
        height: 400,
        width: 500,
        modal: true,
        buttons: {
            "<?php echo _('Save')?>": function() {
                //spandata[<?php echo $key?>]
                gdata = JSON.stringify(spandata[<?php echo $key?>]['groups'])
                $("#dahdi_editspan_<?php echo $key?>").ajaxSubmit({data: {groupdata: gdata}, dataType: 'json', success: function(j) {
                        if(j.status) {
                            $.each(j, function(index, value) {
								if((index == 'framingcoding' && value != '/') || (index != 'framingcoding' && value !== null))
                                	$("#digital_"+index+"_"+j.span+"_label").html(value);
                            });
                            toggle_reload_button('show');
                            $("#reboot").show();
                        }
                    }});
                $( this ).dialog( "close" );
            },
            <?php echo _('Cancel')?>: function() {
                $( this ).dialog( "close" );
            }
        },
        close: function() {
        }
    });
    $("#editspan_<?php echo $key?>_signalling").change(function() {
        if($( this ).val().substring(0,3) == 'bri' || <?php echo $span['totchans']?> != 3 || $( this ).val().substring(0,3) == 'pri') {
            $("#editspan_<?php echo $key?>_switchtype_tr").fadeIn('slow')
            $("#editspan_<?php echo $key?>_switchtype").val('euroisdn')
        } else {
            $("#editspan_<?php echo $key?>_switchtype_tr").fadeOut('slow')
        }
    })
    <?php } ?>
    <?php
    foreach($dahdi_cards->modules as $mod_name => $module) {
        if(method_exists($module,'settings')) {
            $out = $module->settings();
            ?>
            $( "#<?php echo $mod_name?>-settings" ).dialog({
                autoOpen: false,
                height: <?php echo $out['dialog']['height']?>,
                width: <?php echo $out['dialog']['width']?>,
                modal: true,
                buttons: {
                    "<?php echo _('Save')?>": function() {
                        $("#form-<?php echo $mod_name?>settings").ajaxSubmit(options);
                        toggle_reload_button('show');
                        var reboot = '<?php echo $out['reboot'] ? 'reboot_mp' : 'reboot'?>';
                        $("#"+reboot).fadeIn(3000).show();
                        $( this ).dialog( "close" );
                    },
                    <?php echo _('Cancel')?>: function() {
                        $( this ).dialog( "close" );
                    }
                },
                close: function() {
                }
            });
            <?php
        }
    }
    ?>
    $( "#global-settings" ).dialog({
        autoOpen: false,
        height: 400,
        width: 500,
        modal: true,
        buttons: {
            "<?php echo _('Save')?>": function() {
                $("#form-globalsettings").ajaxSubmit(options);
                toggle_reload_button('show');
                $("#reboot").fadeIn(3000).show();
                $( this ).dialog( "close" );
            },
            <?php echo _('Cancel')?>: function() {
                $( this ).dialog( "close" );
            }
        },
        close: function() {
        }
    });
    $( "#system-settings" ).dialog({
        autoOpen: false,
        height: 245,
        width: 550,
        modal: true,
        buttons: {
            "<?php echo _('Save')?>": function() {
                $('#form-systemsettings').ajaxSubmit(options);
                toggle_reload_button('show');
                $("#reboot").fadeIn(3000).show();
                $( this ).dialog( "close" );
            },
            <?php echo _('Cancel')?>: function() {
                $( this ).dialog( "close" );
            }
        },
        close: function() {
        }
    });
    $( "#modules-settings" ).dialog({
        autoOpen: false,
        height: 600,
        width: 500,
        modal: true,
        buttons: {
            "<?php echo _('Save')?>": function() {
				var morder = {}
				$(".modules-sortable li").each(function(i, el){
					var id = $(el).attr('id');
					if (/^mod\-ud\-(?:\d*)$/i.test(id)) {
						id = id.replace("mod-ud-","");
						var name = $('#mod-ud-name-'+id).val();
						if(name !== undefined && name != '') {
							morder['ud::'+name] = $('#mod-ud-checkbox-'+id).prop('checked')
						}
					} else if(/mod\-/i.test(id)) {
						id = id.replace("mod-","");
						morder['sys::'+id] = $('#input-'+id).prop('checked')
					}
				});

				options.data = { order: morder }
                $("#form-modules").ajaxSubmit(options);
                toggle_reload_button('show');
                $("#reboot_mods").fadeIn(3000).show();
                $( this ).dialog( "close" );
            },
			"<?php echo _('Reset File To Defaults')?>": function() {
				var r=confirm("<?php echo _('This will reload the page')?>");
				if (r==true) {
					options.data = { reset: true }
					options.success = function(responseText, statusText, xhr, $form) {
						location.reload();
					};
                	$("#form-modules").ajaxSubmit(options);
				}
			},
      <?php echo _('Cancel')?>: function() {
                $( this ).dialog( "close" );
            }
        },
        close: function() {
        }
    });
    $( "#modprobe-settings" ).dialog({
        autoOpen: false,
        height: 450,
        width: 530,
        modal: true,
        buttons: {
            "<?php echo _('Save')?>": function() {
                //Local Storage is an object {}
                var settings = {'mp_setting_add':[]};
                var z = 0;
                //Find ALL elements in modprobe id.
                $("#modprobe").find('*').each(function() {
                    //Store jquery data in child
                    var child = $(this);
                    //Following check to make sure they are form elements
                    if (child.is(":checkbox"))
                        settings[child.attr("name")] = child.attr("checked") ? true : false;
                    if (child.is(":text"))
                        settings[child.attr("name")] = child.val();
                    if (child.is("select"))
                        settings[child.attr("name")] = child.val();
                    if (child.is(":input:hidden") && child.attr("name") == 'mp_setting_add[]') {
                        settings['mp_setting_add'][z] = child.val();
                        z++
                    }
                })
                //Store data in our storage array
                if(!modprobesettings.hasOwnProperty($('#module_name').val())) {
                    modprobesettings[$('#module_name').val()] = {}
                }
                modprobesettings[$('#module_name').val()]['formsettings'] = settings
                $.each(modprobesettings, function(index, value) {
                    $.post("config.php?quietmode=1&handler=file&module=dahdiconfig&file=ajax.html.php&type=modprobesubmit",{settings: JSON.stringify(value['formsettings'])}, function(j){

                    })
                })
                toggle_reload_button('show');
                $("#reboot_mp").fadeIn(3000).show();
                $( this ).dialog( "close" );
            },
            Cancel: function() {
                $( this ).dialog( "close" );
            }
        },
        close: function() {
			storeModProbeSettings();
        }
    });

 })
$('#mwi').change(function(evt) {
	if ($('#mwi :selected').val() == 'neon') {
		$('.neon').show();
	} else {
		$('.neon').hide();
	}
});

</script>
<?php
//Easy Form Setting method
function set_default($default,$option=NULL,$true='selected') {
	if(isset($option)) {
		return $option == $default ? $true : '';
	} else {
		return isset($default) ? $default : '';
	}
}
