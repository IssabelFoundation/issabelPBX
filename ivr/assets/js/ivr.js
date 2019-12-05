$(document).ready(function(){
    //on load, hide elememnts that may need to be hidden
    invalid_elements();
    timeout_elements();

    $('#add_entrie').click(function(){
        // we get this each time in case a popOver has updated the array
        new_entrie = '<tr>' + $('#gotoDESTID').parents('tr').html() + '</tr>';
        id = new Date().getTime();//must be cached, as we have many replaces to do and the time can shift
        thisrow = $('#ivr_entries > tbody:last').find('tr:last').after(new_entrie.replace(/DESTID/g, id));
        $('.destdropdown2', $(thisrow).next()).hide();
        bind_dests_double_selects();
    });

    $('input[type=submit]').click(function(){
        //remove the last blank field so that it isnt subject to validation, assuming it wasnt set
        //called from .click() as that is fired before validation
        last = $('#ivr_entries > tbody:last').find('tr:last');
        if(last.find('input[name="entries[ext][]"]').val() == ''
            && last.find('.destdropdown').val() == ''){
            last.remove()
        }
    });

    //fix for popovers because jquery wont bubble up a real "submit()" correctly.
    //See ISSABELPBX-8122 for more information
    if($('form[name=frm_ivr]').length>0) {
        $('form[name=frm_ivr]')[0].onsubmit = function() {
            //set timeout/invalid destination, removing hidden field if there is no valus being set
            if ($('#invalid_loops').val() != 'disabled') {
                invalid = $('[name=' + $('[name=gotoinvalid]').val() + 'invalid]').val();
                $('#invalid_destination').val(invalid)
            } else {
                $('#invalid_destination').remove()
            }

            if ($('#timeout_loops').val() != 'disabled') {
                timeout = $('[name=' + $('[name=gototimeout]').val() + 'timeout]').val();
                $('#timeout_destination').val(timeout)
            } else {
                $('#timeout_destination').remove()
            }


            //set goto fileds for destinations
            $('[name^=goto]').each(function(){
                num = $(this).attr('name').replace('goto', '');
                dest = $('[name=' + $(this).val() + num + ']').val();
                $(this).parent().find('input[name="entries[goto][]"]').val(dest)
                //console.log(num, dest, $(this).parent().find('input[name="entries[goto][]"]').val())
            })

            //set ret_ivr checkboxes to SOMETHING so that they get sent back
            $('[name="entries[ivr_ret][]"]').not(':checked').each(function(){
                $(this).attr('checked', 'checked').val('uncheked')
            })

            //disable dests so that they dont get posted
            $('.destdropdown, .destdropdown2').attr("disabled", "disabled");

            setTimeout(restore_form_elemens, 100);
        }
    }

    //delete rows on click
    $('.delete_entrie').live('click', function(){
        $(this).closest('tr').fadeOut('normal', function(){$(this).closest('tr').remove();})
    })

    //show/hide invalid elements on change
    $('#invalid_loops').change(invalid_elements)

    //show/hide timeout elements on change
    $('#timeout_loops').change(timeout_elements)
});

function restore_form_elemens() {
    $('.destdropdown, .destdropdown2').removeAttr('disabled')
    $('[name="entries[ivr_ret][]"][value=uncheked]').each(function(){
        $(this).removeAttr('checked')
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
            invalid_elements.attr('disabled', 'disabled')
            invalid_element_tr.hide()
            break;
        case '0':
            invalid_elements.removeAttr('disabled')
            invalid_element_tr.show();
            $('#invalid_retry_recording').parent().parent().hide();
            $('#invalid_append_announce').parent().parent().hide();
            break;
        default:
            invalid_elements.removeAttr('disabled')
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
            timeout_elements.attr('disabled', 'disabled')
            timeout_element_tr.hide()
            break;
        case '0':
            timeout_elements.removeAttr('disabled')
            timeout_element_tr.show();
            $('#timeout_retry_recording').parent().parent().hide();
            $('#timeout_append_announce').parent().parent().hide();
        default:
            timeout_elements.removeAttr('disabled')
            timeout_element_tr.show()
            break;
    }
}
