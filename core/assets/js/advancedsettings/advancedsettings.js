$(document).ready(function() {
	//save settings
	function savebinder(e) {
		if (!can_write_amportalconf) {
			alert(amportalconf_error);
			return false;
		}
		var mythis = $(this);
		var mykey = $(this).attr('data-key');
		switch ($(this).attr('data-type')) {
			case 'BOOL':
				var myval = $('input[name="' + mykey + '"]:checked').val();
				break;
			default:
				var myval = $('#' + mykey).val();
				break;
		}
		switch (mykey) {
			case 'AMPMGRUSER':
			case 'AMPMGRPASS':
				var skipastman = 0;
				break;
			default:
				var skipastman = 1;
				break;
		}
		$.ajax({
			type: 'POST',
			url: location.href,
			data: {
					quietmode: 1,
					skip_astman: skipastman,
					restrictmods: 'core',
					action: 'setkey',
					keyword: mykey,
					value: myval
					},
			beforeSend: function(XMLHttpRequest, set) {
				mythis.attr({src: '/admin/images/spinner.gif'})
			},
			dataType: 'json',
			success: function(data, textStatus, XMLHttpRequest) {
				//console.log(data);
				mythis.attr({src: '/admin/images/accept.png'});
				if (!data.validated) {
					alert(data.msg);
				}
				if (!data.validated && data.saved) {
				  $('#' + mykey).val(data.saved_value);
				}
				if (data.saved) {
					//remove save button
					mythis.off('click');
					mythis.data('isbound', false);
					mythis.fadeOut('normal', function(){
						mythis.closest('tr').find('.savetd').hide();
					});
					
					//hide retor to defualt if its we have reverted to defualt
					//should not be nesesary -MB
					/*
					input = mythis.closest('tr').find('input.valueinput').val() ;
					defval = mythis.closest('tr').find('input.adv_set_default').attr('data-default')
					console.log(input, defval)
					if(input == defval){
						mythis.closest('tr').find('input.adv_set_default').fadeOut()
					}
					*/
					// If they changed the page layout
					switch (mykey) {
						case 'AS_DISPLAY_HIDDEN_SETTINGS':
						case 'AS_DISPLAY_READONLY_SETTINGS':
						case 'AS_DISPLAY_FRIENDLY_NAME':
						case 'AS_OVERRIDE_READONLY':
							if (page_reload_check()) {
								location.href=location.href;
							} else {
								alert(msgChangesRefresh);
							}
							break;
						default:
  						toggle_reload_button('show');
							break;
						}
					}
				//reset data-valueinput-orig to new value
				switch (mythis.attr('data-type')) {
					case 'BOOL':
						$('input[name="' + mykey + '"]').attr('data-valueinput-orig', mykey);
						break;
					default:
						var myval = $('#' + mykey).attr('data-valueinput-orig', mykey);
						break;
				}
			},
			error: function(data, textStatus, XMLHttpRequest) {
				alert('Ajax Web ERROR: When saving key ' + mykey + ': ' + textStatus);
			}
		})
	}
	//set defualt values
	$('.adv_set_default').click(function(){
		switch ($(this).attr('data-type')) {
		case 'BOOL':
			$('input[name="' + $(this).attr('data-key')).removeAttr("checked");
			$('input[name="' + $(this).attr('data-key') + '"]').filter('[value=' + $(this).attr('data-default') + ']').attr("checked","checked").trigger('change');
			break;
		default:
			$('#'+$(this).attr('data-key')).val($(this).attr('data-default')).trigger('change');
			break;
		}
		$(this).hide();
	});

	//show save button
	$('.valueinput').bind('keyup keypress keydown paste change', function(){
		var save = $(this).closest('tr').find('input.save');
		var savetd = $(this).closest('tr').find('.savetd');
		var adv_set_default = $(this).closest('tr').find('input.adv_set_default');
		
		//if the value was changed since the last page refresh
		if($(this).val() != $(this).attr('data-valueinput-orig')){
			if (savetd.is(':hidden')) {
				savetd.show();
			}
			save.stop(true, true).delay(100).fadeIn();
			//only bind if not already bound
			if (typeof(save.data("isbound")) == 'undefined' || !save.data('isbound')) {
				save.data("isbound", true);
				save.bind('click', savebinder);
			}
		} else {
			save.data("isbound", false);
			save.stop(true, true).delay(100).fadeOut('normal', function(){
				if (!savetd.is(':hidden')) {
					savetd.hide();
				}
			}).off('click'); 
		}
		if($(this).val() != adv_set_default.attr('data-default')){
			if (adv_set_default.is(':hidden')) {
				adv_set_default.show()
			}
		} else {
			if (!adv_set_default.is(':hidden')) {
				adv_set_default.fadeOut()
			}
		}
	})

	$("#page_reload").click(function(){
		if (!page_reload_check()) {
			if (!confirm(msgUnsavedChanges)) {
				return false;
			}
		}
		location.href=location.href;
	});
});

function page_reload_check(msgUnsavedChanges) {
	var reload = true;
	$(".save").each(function() {
		if ($(this).data("events") != undefined) {
			reload = false;
			return false;
		}
	});
	return reload;
}
