//$(document).ready(function(){
up.compiler('.content', function(element,data) {
	//cron_custom
	cron_custom();
	$('select[name=cron_schedule]').on('change',cron_custom);
	
	//cron_schedule
	cron_random();
	$('select[name=cron_schedule]').on('change',cron_random);
	
	
	//style cron custom times
	//$('#crondiv').find('input[type=checkbox]').button();

    $('.ui-helper-hidden-accessible').on('change',function(element) { 
        element_name = element.target.name;
        curstate = element.target.checked;
        $('input[name="'+element_name+'"]').prop('checked',false);
        element.target.checked=curstate;
        //$('#crondiv').find('input[type=checkbox]').button('refresh');
    });

})
function cron_custom() {
	if ($('select[name=cron_schedule]').val() == 'custom') {
		$('#crondiv').show();
	} else {
		$('#crondiv').hide();
	}
}

function cron_random() {
	switch($('select[name=cron_schedule]').val()) {
		case 'never':
		case 'custom':
		case 'reboot':
			$('label[for=cron_random]').hide();
			$('#cron_random').prop("checked",false).hide();
			break;
		default:
			$('label[for=cron_random]').show();
			$('#cron_random').show();
			break;
	}
}


