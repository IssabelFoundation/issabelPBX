$(document).ready(function() {
	$('form[name=page_edit]').submit(function(){
		if (!isInterger($('input[name=pagenbr]').val())) {
			alert('Please enter a valid Paging Extension');
			return false;
		}
	});


	//style devices as buttons
	$('.device_list > span').button();

	//make devices dragable
	$('.device_list').sortable({
		connectWith: '.device_list',
		items: ' > span',
		deactivate: function(){dev_list_height();},
		receive: function(i, ui) {
			//if dev_limit returns false, cancel the move
			dev_limit($(ui.item).parent().attr('id')) 
				|| $(ui.sender).sortable('cancel');
		}
	}).disableSelection();
	
	//set device width so there all the same size
	dev_list_item_width();
	
	//resize device lists, now that there 'sortabled' and 'buttoned'
	dev_list_height();

	//allow devices to move between lists by double clicking on them
	$('.device_list > span').dblclick(function(e){
		var to = $(this).parent().attr('id') == 'selected_dev'
					? 'notselected_dev'
					: 'selected_dev';
		
		//dont transfer devices if at limit
		if (!dev_limit(to)) {
			return false;
		}
		$(this).appendTo($('#' + to));
		dev_list_height();
	});

	//add devices to form on submit
	$('#page_opts_form').submit(function(){
		var form = $(this);

		$('#selected_dev > span').each(function(){
			form.append('<input type="hidden" name="pagelist[]" value="' 
				+ $(this).attr('data-ext') + '">');
		});

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
	$('.device_list > span').each(function(){
		width = $(this).width() > width ? $(this).width() : width;
	});

	$('.device_list > span').width(width);
}
function dev_list_sort() {
	$('.device_list').each(function(){
		var dev_list = $(this);
		var list = dev_list.find('span').sort(function(a, b){
			return $(a).data('ext') > $(b).data('ext') ? 1 : -1;
		})
		$.each(list, function(id, item){
			console.log(item);
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
