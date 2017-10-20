function backup_log(div, msg) {
	//get background color from appropriate jquery ui class
	var bcolor = div.parent().parent().css('backgroundColor');
	
	//build span
	var span = $('<span></span>')
			.html(msg)
			.css('backgroundColor', '#fff2a8');
	
	//append to div
	div.append(span);

	//scroll down and show new span; and remove highlighting
	div.animate({scrollTop: div.prop("scrollHeight")}, 
		500,
		function(){
			span.animate({backgroundColor: bcolor},
			1500,
			function() {
				span.css('background-color', '')
			}
			)
		});

}
