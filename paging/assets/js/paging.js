
up.compiler('.content', function(element,data) {

    if($('#selected_dev').length>0) {

	    dev_list_height();
        $('.device_list > div').each(function() { $(this).addClass('button');});
        dev_list_item_width();

        el = document.getElementById('selected_dev');
        Sortable.create( el, { 
            group: { 
                name: 'selected_dev', 
                put: ['notselected_dev']
            }, animation:100 
        });
        el = document.getElementById('notselected_dev');
        Sortable.create(  el, { 
            group: { 
                name: 'notselected_dev', 
                put: ['selected_dev']
            },animation:100
        });
    }

	//add devices to form on submit
	$('#mainform').on('submit',function(){

        if(typeof msgInvalidExtension == 'undefined') {
            // In General Settings Form we do not have invalid messages, do not validate form
            $.LoadingOverlay('show');
            return true;
        }

		var form = $(this);

        if (!isInteger($('input[name=pagenbr]').val())) {
            return warnInvalid($('input[name=pagenbr]'), msgInvalidExtension);
        }

        if (isEmpty($('input[name=description]').val())) {
            return warnInvalid($('input[name=description]'), msgInvalidDescription);
        }

		$('#selected_dev > div').each(function(){
            console.log('agregado pagelist '+$(this).attr('data-ext'));
			form.append('<input type="hidden" name="pagelist[]" value="' 
				+ $(this).attr('data-ext') + '">');
		});

        $.LoadingOverlay('show');

	});


});

//make devlist heights the same
function dev_list_height() {
	var height = 0;
	$('.device_list').height('auto').each(function(){
		height = $(this).height() > height ? $(this).height() : height;
	}).height(height);
}

function dev_list_item_width() {
	var width = 0;
	$('.device_list > div').each(function(){
		width = $(this).width() > width ? $(this).width() : width;
	});

	$('.device_list > div').width(width);
}

function dev_list_sort() {
	$('.device_list').each(function(){
		var dev_list = $(this);
		var list = dev_list.find('div').sort(function(a, b){
			return $(a).data('ext') > $(b).data('ext') ? 1 : -1;
		})
		$.each(list, function(id, item){
			dev_list.append(item);
		})
	});
}

/**
 * limit the amount of devices a page can contain, based on the advanced settings
 * key PAGINGMAXPARTICIPANTS
 * @param string id - id of target
 *
 * @returns bool
 */
function dev_limit(id) {
	if (id == 'notselected_dev') {
		return true;
	}
	
	//if key isnt set, just return true
	if (typeof ipbx.conf.PAGINGMAXPARTICIPANTS == 'undefined') {
		return true;
	}
	return $('#selected_dev > span').length < ipbx.conf.PAGINGMAXPARTICIPANTS;
}
