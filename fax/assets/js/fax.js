$(document).ready(function() {
	if ($('#system_instance').val() == 'disabled') {
		$('#defaultmail').hide();
	}
	$('#system_instance').click(function(){
		if ($(this).val() == 'disabled'){
			$('#defaultmail').hide();
		}else{
			$('#defaultmail').show();
		}
	});
});