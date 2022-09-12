/* Popup Box Function */
function dahdi_modal_settings(type,id) {
    if(typeof id !== 'undefined') {
        if(type=='analog') {
            if(id=='fxo') {
                Swal.fire({
                    title: 'Do you want to save the changes?',
                    html: $('#analog-settings-fxo').html(),
                    showDenyButton: true,
                    showCancelButton: false,
                    confirmButtonText: ipbx.msg.framework.save,
                    denyButtonText: ipbx.msg.framework.cancel,
                    customClass: 'swal-wide',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $("#dahdi_editanalog_fxo").ajaxSubmit();
                        $("#reboot").fadeIn(3000).show();
                        toggle_reload_button('show');
                    }
                });
            } else {
                Swal.fire({
                    title: 'Do you want to save the changes?',
                    html: $('#analog-settings-fxs').html(),
                    showDenyButton: true,
                    showCancelButton: false,
                    confirmButtonText: ipbx.msg.framework.save,
                    denyButtonText: ipbx.msg.framework.cancel,
                    customClass: 'swal-wide',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $("#dahdi_editanalog_fxs").ajaxSubmit();
                        $("#reboot").fadeIn(3000).show();
                        toggle_reload_button('show');
                    }
                });
            }
        } else {
             // digital
            Swal.fire({
                title: 'Do you want to save the changes?',
                html: $('#'+type+'-settings-'+id).html(),
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: ipbx.msg.framework.save,
                denyButtonText: ipbx.msg.framework.cancel,
                customClass: 'swal-wide',
            }).then((result) => {
                if (result.isConfirmed) {
                    gdata = JSON.stringify(spandata[id]['groups']);
                    $("#dahdi_editspan_"+id).ajaxSubmit({
                        data: { groupdata: gdata}, 
                        dataType: 'json', 
                        success: function(j) {
                            if(j.status) {
                                $.each(j, function(index, value) {
                                    if((index == 'framingcoding' && value != '/') || (index != 'framingcoding' && value !== null)) {
                                        $("#digital_"+index+"_"+j.span+"_label").html(value);
                                    }
                                });
                                toggle_reload_button('show');
                                $("#reboot").show();
                            }
                         }
                     });
                 }
            })
        }
    } else {
        if(type == "modprobe") {
            createModProbeSettings();
        }
        var welem = 'open_'+type+'_settings';
        console.log(welem);
        window[welem]();
    }
}
/* End Popup Box Function */
function storeModProbeSettings(mod_name) {
    //Local Storage is an object {}
    var settings = {'mp_setting_add':[]};
    var module = (mod_name != null) ? mod_name : $('#module_name').val();
    var z = 0;
    //Find ALL elements in modprobe id.
    $("#modprobe").find('*').each(function() {
        //Store jquery data in child
        var child = $(this);
        //Following check to make sure they or form elements
        if (child.is(":checkbox"))
            settings[child.attr("name")] = child.prop("checked");
        if (child.is(":text"))
            settings[child.attr("name")] = child.val();
        if (child.is("select"))
            settings[child.attr("name")] = child.val();
        if (child.is(":input:hidden") && child.attr("name") == 'mp_setting_add[]') {
            settings['mp_setting_add'][z] = child.val();
            z++
        }
    })

    if(!modprobesettings.hasOwnProperty(module)) {
        modprobesettings[module] = {}
    }
    modprobesettings[module]['formsettings'] = settings;
}

function createModProbeSettings() {
    el = $('#module_name');
    //If there is no session data then pull from database
    if(typeof modprobesettings === undefined || !modprobesettings.hasOwnProperty($(el).val()) || !modprobesettings[$(el).val()].hasOwnProperty('formsettings')) {
        $.ajaxSetup({ cache: false });
        $.getJSON("config.php?quietmode=1&handler=file&module=dahdiconfig&file=ajax.html.php",{dcmodule: $(el).val(), type: 'modprobe'}, function(j){
            if(j.status) {
                $('.mp_js_additionals').remove();
                $('#mp_setting_key_0').val('')
                $('#mp_setting_value_0').val('')
                $('#mp_setting_origsetting_key_0').val('')

                if(j.module == "wctdm") {
                    $('#tr_ringdetect').show();
                } else {
                    $('#tr_ringdetect').hide();
                }

                if(j.module == "wctc4xxp") {
                    $('#normal_mp_settings').hide();
                    $('#wct4xxp_wcte12xp_settings').hide();
                    $('#wctc4xxp_settings').show();
                    $("#mode_checkbox").attr('checked',j.mode_checkbox);
                    $('#mode').val(j.mode);
                } else {
                    if((j.module == "wct4xxp") || (j.module == "wcte12xp")) {
                        $('#wct4xxp_wcte12xp_settings').show();
                        $('#defaultlinemode_checkbox').attr('checked',j.defaultlinemode_checkbox);
                        $('#defaultlinemode').val(j.defaultlinemode);
                        $('#normal_mp_settings').hide();
                    } else {
                        $('#wct4xxp_wcte12xp_settings').hide();
                        $('#defaultlinemode_checkbox').attr('checked',false);
                        $('#defaultlinemode').val('t1');
                         $('#normal_mp_settings').show();
                    }
                    $('#wctc4xxp_settings').hide();
                    $("#opermode_checkbox").attr('checked',j.opermode_checkbox);
                    $('#opermode').val(j.opermode);
                    $("#alawoverride_checkbox").attr('checked',j.alawoverride_checkbox);
                    $('#alawoverride').val(j.alawoverride);
                    $('#fxs_honor_mode_checkbox').attr('checked',j.fxs_honor_mode_checkbox);
                    $('#fxs_honor_mode').val(j.fxs_honor_mode);
                    $('#boostringer_checkbox').attr('checked',j.boostringer_checkbox);
                    $('#boostringer').val(j.boostringer);
                    $('#fastringer_checkbox').attr('checked',j.fastringer_checkbox);
                    $('#fastringer').val(j.fastringer);
                    $('#lowpower_checkbox').attr('checked',j.lowpower_checkbox);
                    $('#lowpower').val(j.lowpower);
                    $('#ringdetect_checkbox').attr('checked',j.ringdetect_checkbox);
                    $('#ringdetect').val(j.ringdetect);
                    $('#mwi_checkbox').attr('checked',j.mwi_checkbox);
                    $('#mwi').val(j.mwi);
                    if (j.mwi == 'neon') {
                        $('.neon').show();
                    } else {
                        $('.neon').hide();
                    }
                    $('#neon_voltage').val(j.neon_voltage);
                    $('#neon_offlimit').val(j.neon_offlimit);
                }

                //Re-create additionals for this probe
                var z = 1;
                if(typeof j.additionals !== 'undefined') {
                    $.each(j.additionals, function(index, value) {
                        if(z == 1) {
                            $('#mp_setting_key_0').val(index)
                            $('#mp_setting_value_0').val(value)
                        } else {
                            $("#mp_add").before('<div class="columns mp_js_additionals" id="mp_additional_'+z+'"><div class="column"><a href="#" onclick="mp_delete_field('+z+',\''+j.module+'\')"><img height="10px" src="images/trash.png"></a> <input type="hidden" name="mp_setting_add[]" value="'+z+'" /><input type="hidden" id="mp_setting_origsetting_key_'+z+'" name="mp_setting_origsetting_key_'+z+'" value="'+index+'" /> <input id="mp_setting_key_'+z+'" name="mp_setting_key_'+z+'" value="'+index+'" class="valueinput" /> = <input id="mp_setting_value_'+z+'" name="mp_setting_value_'+z+'" value="'+value+'" class="valueinput" /></div></div>');
                        }
                        z++
                    })
                }
                $("#mp_add_button").attr("onclick","mp_add_field("+z+",'"+j.module+"')");
                storeModProbeSettings(el.val());
            }
        })
    } else {
        if($(el).val() == "wctdm") {
            $('#tr_ringdetect').show();
        } else {
            $('#tr_ringdetect').hide();
        }

        if(($(el).val() == "wct4xxp") || ($(el).val() == "wcte12xp")) {
            $('#wct4xxp_wcte12xp_settings').show();
            $('#normal_mp_settings').hide();
        } else {
            $('#wct4xxp_wcte12xp_settings').hide();
            $('#normal_mp_settings').show();
        }

        //Hide neon settings
        $('.neon').hide();
        //Remove all extra additionals
        $('.mp_js_additionals').remove();
        var module = $(el).val();
        //Re-create additionals for this probe
        var z = 1;
        $.each(modprobesettings[$(el).val()]['formsettings']['mp_setting_add'], function(index, value) {
            var i = value;
            if(i != '0') {
                $("#mp_add").before('<div class="columns mp_js_additionals" id="mp_additional_'+i+'"><div class="column"><a href="#" onclick="mp_delete_field('+i+',\''+module+'\')"><img height="10px" src="images/trash.png"></a> <input type="hidden" name="mp_setting_add[]" value="'+i+'" /> <input id="mp_setting_key_'+i+'" name="mp_setting_key_'+i+'" value="" class="valueinput"/> = <input id="mp_setting_value_'+i+'" name="mp_setting_value_'+i+'" value="" class="valueinput" /></div></div>');
            }
            z++
        })
        $("#mp_add_button").attr("onclick","mp_add_field("+z+",'"+module+"')");
        $.each(modprobesettings[$(el).val()]['formsettings'], function(index, value) {
            //Check to make sure ID exits before we reset it, but only do it inside the modprobe div element (though IDs should be unique!)
          if (document.getElementById(index)) {
              element = $('#modprobe #'+index);
              if (element.is(":checkbox")) {
                  if(value) {
                    element.attr('checked','checked');
                } else {
                    element.removeAttr('checked');
                }
              }
              if (element.is(":text")) {
                  element.val(value);
              }

              if (element.is("select"))
                  element.val(value);
                //Show extra neon stuff
                if ((index == 'mwi') && (value == 'neon')) {
                    $('.neon').show();
                }
          }
        });
        //console.log('end'+$("#modprobe").height());
    }
}

function reset_digital_groups(span,usedchans) {
    update_digital_groups(span,0,usedchans);
}

/* Span Group Automation */
function update_digital_groups(span,group,usedchans) {
    usedchans = Number(usedchans)
    span = Number(span)
    group = Number(group)

    spandata[span]['groups'][group]['usedchans'] = Number(usedchans)

    $.getJSON("config.php?quietmode=1&handler=file&module=dahdiconfig&file=ajax.html.php",{type: 'calcbchanfxx', span: span, usedchans: usedchans, startchan: spandata[span]['groups'][group]['startchan']}, function(j){
        j.endchan = Number(j.endchan)
        $('#editspan_'+span+'_from_'+ group).html(j.fxx);
        spandata[span]['groups'][group]['endchan'] = j.endchan;
        spandata[span]['groups'][group]['fxx'] = j.fxx
        spandata[span]['groups'][group]['span'] = j.span

        if(j.endchan < (spandata[span]['spandata']['max_ch']-1)) {
            if (!document.getElementById('editspan_'+span+'_group_settings_' + (group+1))) {
                var startchan = j.endchan+1
                var add = ((spandata[span]['groups'][group]['usedchans'] + Number(spandata[span]['spandata']['min_ch'])) > spandata[span]['spandata']['reserved_ch']) ? 1 : 0;
                var usedchans = (spandata[span]['spandata']['max_ch'] + add) - startchan
                var group_num = $('#editspan_'+span+'_group_'+group).val();
                group_num = $.isNumeric(group_num) ? group_num : group;
                $.getJSON("config.php?quietmode=1&handler=file&module=dahdiconfig&file=ajax.html.php",{type: 'digitaladd', span: span, groupc: group+1, usedchans: usedchans, startchan: startchan, group_num: (Number(group_num)+1)}, function(z){
                    $('#editspan_'+span+'_group_settings_' + (group)).after(z.html);
                    group++;
                    spandata[span]['groups'][group] = {};
                    spandata[span]['groups'][group]['endchan'] = z.endchan;
                    spandata[span]['groups'][group]['usedchans'] = Number(usedchans);
                    spandata[span]['groups'][group]['fxx'] = z.fxx
                    spandata[span]['groups'][group]['startchan'] = Number(z.startchan)
                    $('#editspan_'+span+'_definedchans_' + group).on('change', function() {
                        var usedchans = $(this).val();
                        update_digital_groups(span,group,usedchans);
                    });
                })
            } else {
                var count = spandata[span]['groups'].length;
                var i = 1;
                var prevkey = 0;
                $.each(spandata[span]['groups'], function(key, value) {
                    if(group < key) {
                        var startchan = spandata[span]['groups'][(prevkey)]['endchan'] + 1
                        var usedchans = $('#editspan_'+span+'_definedchans_'+key).val()
                        var selected = 0;
                        if(i == count) {
                            usedchans = spandata[span]['spandata']['max_ch'] - spandata[span]['groups'][prevkey]['endchan']
                            selected = usedchans
                        } else {
                            selected = $('#editspan_'+span+'_definedchans_' + key).val()
                        }
                        $.ajax({
                          url: "config.php?quietmode=1&handler=file&module=dahdiconfig&file=ajax.html.php",
                          dataType: 'json',
                          data: {type: 'digitaladd', span: span, usedchans: usedchans, startchan: startchan},
                          async: false
                        }).done(function(x){
                            $('#editspan_'+span+'_from_'+ key).html(x.fxx)
                            $('#editspan_'+span+'_definedchans_' + key).html(x.select)
                            $('#editspan_'+span+'_definedchans_' + key).val(selected)
                            spandata[span]['groups'][key]['endchan'] = x.endchan
                            spandata[span]['groups'][key]['fxx'] = x.fxx
                            spandata[span]['groups'][key]['startchan'] = x.startchan
                        });

                    }
                    i++;
                    prevkey = key;
                });
            }
        } else {
            //Delete all groups forward
            if((spandata[span]['groups'][group]['startchan'] + spandata[span]['groups'][group]['usedchans']) > spandata[span]['spandata']['max_ch']) {
                var selected = spandata[span]['spandata']['max_ch'] - spandata[span]['groups'][group]['startchan']
                $('#editspan_'+span+'_definedchans_' + group).val(selected)
            }
            $.each(spandata[span]['groups'], function(key, value) {
                if(document.getElementById('editspan_'+span+'_group_settings_' + key) && (key > group)) {
                    $('#editspan_'+span+'_group_settings_' + key).remove();
                    delete spandata[span]['groups'][key]
                }
            })
        }
    })
}
/* End Span Group Automation */
/* Custom settings for Global Settings */
/* Delete Custom Setting */
function dh_global_delete_field(id) {
    var origkey = $("#dh_global_origsetting_key_"+id).val();
    var key = $("#dh_global_setting_key_"+id).val();
    var val = $("#dh_global_setting_val_"+id).val();
    if(typeof origkey === 'undefined') {
        if(id > 0) {
            $('#dh_global_additional_'+ id).remove();
        } else {
            $('#dh_global_setting_key_0').val('');
            $('#dh_global_setting_value_0').val('');
        }
    } else {
        if(id > 0) {
            $.getJSON("config.php?quietmode=1&handler=file&module=dahdiconfig&file=ajax.html.php",{type: 'globalsettingsremove', keyword: key, origkeyword: origkey, value: val}, function(z){
                $('#dh_global_additional_'+ id).remove();
            });
        } else {
            $.getJSON("config.php?quietmode=1&handler=file&module=dahdiconfig&file=ajax.html.php",{type: 'globalsettingsremove', keyword: key, origkeyword: origkey, value: val}, function(z){
                $('#dh_global_setting_key_0').val('');
                $('#dh_global_setting_value_0').val('');
            });
        }
    }
    Swal.update({html: $('#global-settings').html()});
}
/* End Delete Custom Setting */
/* Add Custom Setting */
var max_dh_global = 0;
//var dh_global_additional_key = 0;
function dh_global_add_field(start) {
    var i = (start < max_dh_global) ? max_dh_global : start;
    $("#dh_global_add").before('<div class="columns" id="dh_global_additional_'+i+'"><div class="column"><a href="javascript:void(0)" onclick="dh_global_delete_field('+i+')"><button type="button" class="is-danger button is-small"><span class="icon is-small"><i class="fa fa-trash"></i></span></button></a> <input type="hidden" name="dh_global_add[]" value="'+i+'" /><input type="text" class="valueinput" id="dh_global_setting_key_'+i+'" name="dh_global_setting_key_'+i+'" value="" /> = <input id="dh_global_setting_value_'+i+'" class="valueinput" type="text" name="dh_global_setting_value_'+i+'" value="" /></div></div>');
    max_dh_global = i+1;
    Swal.update({html: $('#global-settings').html()});
}
/* End Add Custom Setting */
/* End Custom settings for Global Settings */

/* Start Custom settings for System Settings */
/* Delete Custom Setting */
function dh_system_delete_field(id) {
    var origkey = $("#dh_system_origsetting_key_"+id).val();
    var key = $("#dh_system_setting_key_"+id).val();
    var val = $("#dh_system_setting_val_"+id).val();
    if(typeof origkey === 'undefined') {
        if(id > 0) {
            $('#dh_system_additional_'+ id).remove();
        } else {
            $('#dh_system_setting_key_0').val('');
            $('#dh_system_setting_value_0').val('');
        }
    } else {
        if(id > 0) {
            $.getJSON("config.php?quietmode=1&handler=file&module=dahdiconfig&file=ajax.html.php",{type: 'systemsettingsremove', keyword: key, origkeyword: origkey, value: val}, function(z){
                $('#dh_system_additional_'+ id).remove();
            });
        } else {
            $.getJSON("config.php?quietmode=1&handler=file&module=dahdiconfig&file=ajax.html.php",{type: 'systemsettingsremove', keyword: key, origkeyword: origkey, value: val}, function(z){
                $('#dh_system_setting_key_0').val('');
                $('#dh_system_setting_value_0').val('');
            });
        }
    }
}
/* End Delete Custom Setting */
/* Add Custom Setting */
var max_dh_system = 0;
//var dh_global_additional_key = 0;
function dh_system_add_field(start) {
    var i = (start < max_dh_system) ? max_dh_system : start;
    $("#dh_system_add").before('<div class="columns" id="dh_system_additional_'+i+'"><div class="column"><a href="#" onclick="dh_system_delete_field('+i+')"><button type="button" class="is-danger button is-small"><span class="icon is-small"><i class="fa fa-trash"></i></span></button></a> <input type="hidden" name="dh_system_add[]" value="'+i+'" /><input type="text" class="valueinput" id="dh_system_setting_key_'+i+'" name="dh_system_setting_key_'+i+'" value="" /> = <input id="dh_system_setting_value_'+i+'" name="dh_system_setting_value_'+i+'" value="" type="text" class="valueinput"/></div></div>');
    max_dh_system = i+1;
}
/* End Add Custom Setting */
/* End Custom settings for System Settings */

var max_mp = 0;
function mp_add_field(start,module) {
    var i = (start < max_mp) ? max_mp : start;
    $("#mp_add").before('<div class="columns mp_js_additionals" id="mp_additional_'+i+'"><div class="column"><a href="#" onclick="mp_delete_field('+i+',\''+module+'\')"><button type="button" class="is-danger button is-small"><span class="icon is-small"><i class="fa fa-trash"></i></span></button></a> <input type="hidden" name="mp_setting_add[]" value="'+i+'" /> <input class="valueinput" type="text" id="mp_setting_key_'+i+'" name="mp_setting_key_'+i+'" value="" /> = <input id="mp_setting_value_'+i+'" name="mp_setting_value_'+i+'" type="text" class="valueinput" value="" /></div></div>');
    max_mp = i+1;
}

function mp_delete_field(id,module) {
    var origkey = $("#mp_setting_origsetting_key_"+id).val();
    var key = $("#mp_setting_key_"+id).val();
    var val = $("#mp_setting_val_"+id).val();
    if(typeof origkey === 'undefined') {
        if(id > 0) {
            $('#mp_additional_'+ id).remove();
        } else {
            $('#mp_setting_key_0').val('');
            $('#mp_setting_value_0').val('');
        }
    } else {
        if(id > 0) {
            $.getJSON("config.php?quietmode=1&handler=file&module=dahdiconfig&file=ajax.html.php",{type: 'mpsettingsremove', mod: module, keyword: key, origkeyword: origkey, value: val}, function(z){
                $('#mp_additional_'+ id).remove();
            });
        } else {
            $.getJSON("config.php?quietmode=1&handler=file&module=dahdiconfig&file=ajax.html.php",{type: 'mpsettingsremove', mod: module, keyword: key, origkeyword: origkey, value: val}, function(z){
                $('#mp_setting_key_0').val('');
                $('#mp_setting_value_0').val('');
            });
        }
    }
}

var mods_add_id = '';
function mods_add_field() {
    mods_add_id = (mods_add_id == '') ? $('#mods_add_id').val() : mods_add_id;
    $('#modules-sortable li:last').after('<li id="mod-ud-'+mods_add_id+'"><input type="checkbox"  class="checkbox mx-2" id="mod-ud-checkbox-'+mods_add_id+'"><a href="javascript:void(0)" onclick="mods_del_field(\'mod-ud-'+mods_add_id+'\')"><button type="button" class="mx-2 is-danger button is-small"><span class="icon is-small"><i class="fa fa-trash"></i></span></button></a><input type="text" class="valueinput" id="mod-ud-name-'+mods_add_id+'" value=""></li>');
    mods_add_id++
    $('.modules-sortable').sortable('destroy');
    $('.modules-sortable').sortable();
}

function mods_del_field(id) {
    $('#'+id).remove();
}


document.addEventListener('DOMContentLoaded', () => {
  // Functions to open and close a modal
  function openModal($el) {
    $el.classList.add('is-active');
  }

  function closeModal($el) {
    $el.classList.remove('is-active');
  }

  function closeAllModals() {
    (document.querySelectorAll('.modal') || []).forEach(($modal) => {
      closeModal($modal);
    });
  }

  // Add a click event on buttons to open a specific modal
  (document.querySelectorAll('.js-modal-trigger') || []).forEach(($trigger) => {
    const modal = $trigger.dataset.target;
    const $target = document.getElementById(modal);

    $trigger.addEventListener('click', () => {
      openModal($target);
    });
  });

  // Add a click event on various child elements to close the parent modal
  (document.querySelectorAll('.modal-background, .modal-close, .modal-card-head .delete, .modal-card-foot .button') || []).forEach(($close) => {
    const $target = $close.closest('.modal');

    $close.addEventListener('click', () => {
      closeModal($target);
    });
  });

  // Add a keyboard event to close all modals
  document.addEventListener('keydown', (event) => {
    const e = event || window.event;

    if (e.keyCode === 27) { // Escape key
      closeAllModals();
    }
  });


  (document.querySelectorAll('.formsubmit') || []).forEach(($trigger) => {
    const form = $trigger.dataset.target;
    const $target = document.getElementById(form);

    $trigger.addEventListener('click', (evt) => {
      formsubmit($target);
    });

  });

 function formsubmit($el) {

     var options = { type: 'POST' };

     if($($el).attr('id')=='form-modules') {

         sData = $($el).serialize();
         prms = new URLSearchParams(sData); let unsData = {}; for (let [k, v] of prms) unsData[k] = v;

         if(unsData['reset']==1) {

              Swal.fire({
                title: ipbx.msg.framework.areyousure,
                text: ipbx.msg.framework.pagereload,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: ipbx.msg.framework.yes,
                cancelButtonText: ipbx.msg.framework.cancel
              }).then((result) => {
                if (result.isConfirmed) {
                    options.data = { reset: true }
                    options.success = function(responseText, statusText, xhr, $form) {
                        location.reload();
                    };
                    $("#form-modules").ajaxSubmit(options);
                }
             });

         } else {
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
             $($el).ajaxSubmit(options);
             toggle_reload_button('show');
             $("#reboot").fadeIn(3000).show();
         }

     } else if($($el).attr('id')=='form-modprobe') {

         //Local Storage is an object {}
         var settings = {'mp_setting_add':[]};
         var z = 0;
         //Find ALL elements in modprobe id.
         $("#modprobe").find('*').each(function() {
             //Store jquery data in child
             var child = $(this);
             //Following check to make sure they are form elements
             if (child.is(":checkbox")) {
                 settings[child.attr("name")] = child.attr("checked") ? true : false;
             }
             if (child.is(":text")) {
                 settings[child.attr("name")] = child.val();
             }
             if (child.is("select")) {
                 settings[child.attr("name")] = child.val();
             }
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
             $.post("config.php?quietmode=1&handler=file&module=dahdiconfig&file=ajax.html.php&type=modprobesubmit",{settings: JSON.stringify(value['formsettings'])}, function(j){})
         })
         toggle_reload_button('show');
         $("#reboot_mp").fadeIn(3000).show();
     } else {
         $($el).ajaxSubmit(options);
         toggle_reload_button('show');
         $("#reboot").fadeIn(3000).show();
     }
 }

});
