
//for queues
$(function() {
    $( "#availableDevices" ).sortable({
        connectWith: '.devices',
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
    
    $('#managersS').sortable({
        connectWith: '.devices',
		
        remove: function(ui){
			$(this).children().removeClass('filled');
        }
    }).disableSelection();
});

$('form').submit(function(e) {
	var managers = $("#managersS").sortable("toArray");
	$("#tempManagers").val(managers);
	
});



