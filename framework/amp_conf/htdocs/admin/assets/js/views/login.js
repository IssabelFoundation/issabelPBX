$(document).ready(function() {
	var hash = window.location.hash.split("#")[1] || '';
	if (hash == 'login') {
		setTimeout(function(){
			$('#login_admin').click();
		}, 1000);
	}
});
