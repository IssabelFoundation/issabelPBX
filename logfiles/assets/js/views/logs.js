$(document).ready(function(){
	//load a lof file on browse if were not looking at the settings page
	if (!$.urlParam('view')) {
		get_lines(500);
	}

	$('#show').click(function(){
		get_lines($('#lines').val());
	});

	$('select[name=logfile], #lines').change(function(){
		get_lines($('#lines').val());
	});

	$('#log_view.pre').css('max-height',($(window).height() - $('#footer').height() - $('#logfiles_header').height() - 60));

	$(window).resize(function() {
		$('#log_view.pre').css('max-height',($(window).height() - $('#footer').height() - $('#logfiles_header').height() - 60));
	})
});

function get_lines(lines) {
	$.get(window.location.href, {'lines': lines, 'logfile': $('select[name=logfile]').val()}, function(data){
		$('#log_view').html(data);
	})
}
