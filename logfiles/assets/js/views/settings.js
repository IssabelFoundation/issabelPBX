$(document).ready(function(){

	var new_entry = '<tr>' + $('#logfile_entries > tbody:last').find('tr:last').html() + '</tr>';
	$('#add_entry').click(function(){
		$('#logfile_entries > tbody:last').find('tr:last').after(new_entry);
	});
	
	//delete rows on click
	$('.delete_entry').live('click', function(){
		$(this).closest('tr').fadeOut('normal', function(){$(this).closest('tr').remove();})
	});
	
	$('input[type=submit]').click(function(){
		//remove the last blank field so that it isnt subject to validation, assuming it wasnt set
		//called from .click() as that is fired before validation
		last = $('#logfile_entries > tbody:last').find('tr:last');
		if(last.find('input[name="logfiles[name][]"]').val() == ''){
			last.remove();
		}
	});
});