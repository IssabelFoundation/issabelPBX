//$(function() {
up.compiler('.content',function() {
    $('#ivrfile').on('change',function() { $('#selected_file_name').text(this.value.replace(/.*[\/\\]/, '')); });
	$(".autocomplete-combobox").css('width','250px').chosen({search_contains: true, no_results_text: "No Recordings Found"});
})
