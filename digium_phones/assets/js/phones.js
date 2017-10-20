//for extensions
$(function() {
    $( "#lines" ).sortable({
        connectWith: '.ext',
        create: function(event, ui) {
			$(this).children().removeClass('filled');
        },
        
        receive: function(event,ui) {
			$(this).children().removeClass('filled');
		},
        
		remove: function(ui){
			$(this).children().removeClass('filled');
			$(this).removeClass('dontDrop');
		}
		}).disableSelection();
    
    $('#availableExtensions').sortable({
        connectWith: '.ext',
        remove: function(ui){
			$(this).children().removeClass('filled');
        }
    }).disableSelection();
	
});

//for phonebooks
$(function() {
    $( "#devicephonebooks" ).sortable({
        connectWith: '.pb',
        create: function(event, ui) {
			$(this).children().removeClass('filled');
        },
        
        receive: function(event,ui) {
			$(this).children().removeClass('filled');
		},
        
		remove: function(ui){
			$(this).children().removeClass('filled');
			$(this).removeClass('dontDrop');
		}
		}).disableSelection();
    
    $('#availablePhonebooks').sortable({
        connectWith: '.pb',
        remove: function(ui){
			$(this).children().removeClass('filled');
        }
    }).disableSelection();
});

//for networks
$(function() {
    $( "#devicenetworks" ).sortable({
        connectWith: '.networks',
        create: function(event, ui) {
			$(this).children().removeClass('filled');
        },
        
        receive: function(event,ui) {
			$(this).children().removeClass('filled');
		},
        
		remove: function(ui){
			$(this).children().removeClass('filled');
			$(this).removeClass('dontDrop');
		}
		}).disableSelection();
    
    $('#availableNetworks').sortable({
        connectWith: '.networks',
        remove: function(ui){
			$(this).children().removeClass('filled');
        }
    }).disableSelection();
});

//for logos
$(function() {
    $( "#devicelogos" ).sortable({
        connectWith: '.logos',
        create: function(event, ui) {
			$(this).children().removeClass('filled');
        },
        
        receive: function(event,ui) {
			$(this).children().removeClass('filled');
		},
        
		remove: function(ui){
			$(this).children().removeClass('filled');
			$(this).removeClass('dontDrop');
		}
		}).disableSelection();
    
    $('#availableLogos').sortable({
        connectWith: '.logos',
        remove: function(ui){
			$(this).children().removeClass('filled');
        }
    }).disableSelection();
});

//for Alerts
$(function() {
    $( "#devicealerts" ).sortable({
        connectWith: '.alerts',
        create: function(event, ui) {
			$(this).children().removeClass('filled');
        },
        
        receive: function(event,ui) {
			$(this).children().removeClass('filled');
		},
        
		remove: function(ui){
			$(this).children().removeClass('filled');
			$(this).removeClass('dontDrop');
		}
		}).disableSelection();
    
    $('#availableAlerts').sortable({
        connectWith: '.alerts',
        remove: function(ui){
			$(this).children().removeClass('filled');
        }
    }).disableSelection();
});

//for Ringtones
$(function() {
    $( "#deviceringtones" ).sortable({
        connectWith: '.ringtones',
        create: function(event, ui) {
			$(this).children().removeClass('filled');
        },
        
        receive: function(event,ui) {
			$(this).children().removeClass('filled');
		},
        
		remove: function(ui){
			$(this).children().removeClass('filled');
			$(this).removeClass('dontDrop');
		}
		}).disableSelection();
    
    $('#availableringtones').sortable({
        connectWith: '.ringtones',
        remove: function(ui){
			$(this).children().removeClass('filled');
        }
    }).disableSelection();
});

//for Statuses
$(function() {
    $( "#devicestatuses" ).sortable({
        connectWith: '.statuses',
        create: function(event, ui) {
			$(this).children().removeClass('filled');
        },
        
        receive: function(event,ui) {
			$(this).children().removeClass('filled');
		},
        
		remove: function(ui){
			$(this).children().removeClass('filled');
			$(this).removeClass('dontDrop');
		}
		}).disableSelection();
    
    $('#availableStatuses').sortable({
        connectWith: '.statuses',
        remove: function(ui){
			$(this).children().removeClass('filled');
        }
    }).disableSelection();
});

//for Customapps
$(function() {
    $( "#devicecustomapps" ).sortable({
        connectWith: '.customapps',
        create: function(event, ui) {
			$(this).children().removeClass('filled');
        },
        
        receive: function(event,ui) {
			$(this).children().removeClass('filled');
		},
        
		remove: function(ui){
			$(this).children().removeClass('filled');
			$(this).removeClass('dontDrop');
		}
		}).disableSelection();
    
    $('#availableCustomapps').sortable({
        connectWith: '.customapps',
        remove: function(ui){
			$(this).children().removeClass('filled');
        }
    }).disableSelection();
});

$('#digium_phones_editdevice').submit(function(e) {
	e.preventDefault();
	var form = $('form').serialize();
	var lines = $("#lines").sortable("serialize");
	var phonebooks = $( "#devicephonebooks" ).sortable("serialize");
	var networks = $("#devicenetworks").sortable("serialize");
	var logos = $("#devicelogos").sortable("serialize");
	var alerts = $("#devicealerts").sortable("serialize");
	var ringtones = $("#deviceringtones").sortable("serialize");
	var statuses = $("#devicestatuses").sortable("serialize");
	var customapps = $("#devicecustomapps").sortable("serialize");
	var postvar = form 
	postvar = postvar + '&' + lines;
	if (phonebooks) {
		postvar = postvar + '&' + phonebooks;
	}
	if (networks) {
		postvar = postvar + '&' + networks; 
	}
	if (logos) {
		postvar = postvar + '&' + logos;
	}
	if (alerts) {
		postvar = postvar + '&' + alerts;
	}
	if (ringtones) {
		postvar = postvar + '&' + ringtones;
	}
	if (statuses) {
		postvar = postvar + '&' + statuses;
	}
	if (customapps) {
		postvar = postvar + '&' + customapps;
	}
	if ($('#pin_voicemail').is(':checked')) {
		postvar = postvar + '&pin=voicemail';
	}

	$.ajax({
		type: "POST",
		url: $('form').attr('action'),
		data: postvar,
		success: function(data) {
			document.location.reload(true);
		}
	});
});

// for views/digium_phones_phones.php
$('#pin_voicemail').click(function() {
	var pin = $(this).prev();
	if (this.checked) {
		pin.data('saved', pin.val());
		pin.prop('disabled', true);
		pin.val('voicemail');
	} else {
		pin.prop('disabled', false);
		pin.val(pin.data('saved'));
	}
});

