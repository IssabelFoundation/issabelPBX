$('body').on('click','input[type=submit]',function(){
    //remove the last blank field so that it isnt subject to validation, assuming it wasnt set
    //called from .click() as that is fired before validation
    last = $('#ivr_entries > tbody:last').find('tr:last');
    if(last.find('input[name="entries[ext][]"]').val() == ''
        && last.find('.destdropdown').val() == ''){
        last.remove()
    }
});

$('body').on('click','.delete_entrie',function() {
    $(this).closest('tr').fadeOut('normal', function(){$(this).closest('tr').remove();})
});

$('body').on('submit','form[name=frm_ivr]',function(){
    //set timeout/invalid destination, removing hidden field if there is no valus being set
    if ($('#invalid_loops').val() != 'disabled') {
        //invalid = $('[name=' + $('[name=gotoinvalid]').val() + 'invalid]').val();
        invalid = $('#'+$('[name=gotoinvalid]').val()).val()
        $('#invalid_destination').val(invalid)
    } else {
        $('#invalid_destination').remove()
    }

    if ($('#timeout_loops').val() != 'disabled') {
        //timeout = $('[name=' + $('[name=gototimeout]').val() + 'timeout]').val();
        timeout = $('#'+$('[name=gototimeout]').val()).val()
        $('#timeout_destination').val(timeout)
    } else {
        $('#timeout_destination').remove()
    }

    //set goto fileds for destinations
    $('[name^=goto]').each(function(){
        num = $(this).attr('name').replace('goto', '');
        if(num=='invalid') return;
        if(num=='timeout') return;
        dest = $('#'+$(this).val()).val();
        $(this).parent().find('input[name="entries[goto][]"]').val(dest)
        //console.log(num, dest, $(this).parent().find('input[name="entries[goto][]"]').val())
    })

    //set ret_ivr checkboxes to SOMETHING so that they get sent back
    $('[name="entries[ivr_ret][]"]').not(':checked').each(function(){
        $(this).prop('checked', true).val('uncheked')
    })

    //disable dests so that they dont get posted
    $('.destdropdown, .destdropdown2').attr("disabled", "disabled");

    setTimeout(restore_form_elemens, 100);    
});


// Add IVR Entries - Special Clone to work with Chosen
jQuery(function($){ 
    var clone = $("#ivr_entries tr:last").clone(true);  // clone select before chosen is applied
    $('body').on('click', '#add_entrie', function(event) {
        event.preventDefault();
        var ParentRow = $("#ivr_entries tr").last();
        nextid = $("#ivr_entries tr").length;
        clone.clone(true).insertAfter(ParentRow).find('select:first').attr('id','goto'+nextid).data('id',nextid).attr('data-id',nextid).attr('name','goto'+nextid);
        $('#goto'+nextid+' > option').each(function() {
            valor = this.value.replace(/DESTID/,nextid);
            $(this).val(valor);
        });
        $('#goto'+nextid).parent().find('.destdropdown2').each(
            function(idx,el) { 
                curname = $(el).attr('name'); 
                curname = curname.replace(/DESTID/,nextid); 
                $(el).attr('name',curname); 
                $(el).data('id',nextid).attr('data-id',nextid).attr('id',curname).removeClass('gotoDESTID').addClass('goto'+nextid);
            })
        bind_dests_double_selects(); // bind events before chosen on cloned element
        $('.destdropdown:not(".haschosen")').addClass('haschosen').chosen({disable_search: false, inherit_select_classes: true, width: '100%'});
        $('.destdropdown2:not(".haschosen")').addClass('haschosen').chosen({disable_search: true, inherit_select_classes: true, width: '100%'});
    });
});

up.compiler('.content', function(element,data) {

    //on load, hide elements that may need to be hidden
    invalid_elements();
    timeout_elements();

    //show/hide invalid elements on change
    $('#invalid_loops').on('change', invalid_elements)

    //show/hide timeout elements on change
    $('#timeout_loops').on('change', timeout_elements)
});

function restore_form_elemens() {
    $('.destdropdown, .destdropdown2').prop('disabled',false)
    $('[name="entries[ivr_ret][]"][value=uncheked]').each(function(){
        $(this).prop('checked',false)
    })
    invalid_elements();
    timeout_elements();
}

//always disable hidden elements so that they dont trigger validation
function invalid_elements() {
    var invalid_elements = $('#invalid_retry_recording, #invalid_recording, #invalid_append_announce, #invalid_ivr_ret, [name=gotoinvalid]');
    var invalid_element_tr = invalid_elements.parent().parent();
    switch ($('#invalid_loops').val()) {
        case 'disabled':
            invalid_elements.prop('disabled', true)
            invalid_element_tr.hide()
            break;
        case '0':
            invalid_elements.prop('disabled',false)
            invalid_element_tr.show();
            $('#invalid_retry_recording').parent().parent().hide();
            $('#invalid_append_announce').parent().parent().hide();
            break;
        default:
            invalid_elements.prop('disabled',false)
            invalid_element_tr.show()
            break;
    }
}

//always disable hidden elements so that they dont trigger validation
function timeout_elements() {
    var timeout_elements = $('#timeout_retry_recording, #timeout_recording, #timeout_append_announce, #timeout_ivr_ret, [name=gototimeout]');
    var timeout_element_tr = timeout_elements.parent().parent();
    switch ($('#timeout_loops').val()) {
        case 'disabled':
            timeout_elements.prop('disabled', true)
            timeout_element_tr.hide()
            break;
        case '0':
            timeout_elements.prop('disabled', false)
            timeout_element_tr.show();
            $('#timeout_retry_recording').parent().parent().hide();
            $('#timeout_append_announce').parent().parent().hide();
        default:
            timeout_elements.prop('disabled',false)
            timeout_element_tr.show()
            break;
    }
}
