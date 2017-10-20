$(document).ready(function() {
 $('.sortable').sortable(	{
	   update: function(event, ui) {
			//console.log(ui.item.find('input').val(), ui.item.index())
			ui.item.find('input').val(ui.item.index())
		}
	})
});
