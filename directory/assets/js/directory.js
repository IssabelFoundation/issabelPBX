$(document).ready(function(){
	//show/hide add button/dropdown
	$('#addbut').click(function(){
		$('#addusersel').val('none');//reset select box
		$(this).fadeOut(250,
		function(){
			$('#addrow').fadeIn(250);
		});
		return false;
	});

	//add row button
	$('#addrow').change(function(){
		$(this).fadeOut(250,
		function(){
			$('#addbut').not("span").fadeIn(250).find("span").hide();
		});
		if($('#addusersel').val() != 'none'){
			var rownum=$('[class^=entrie]').length+1;
			//increment id untill we find one that isnt being used
			while($('.entrie' + rownum).length == 1){
				rownum++;
			}
			addrow($('#addusersel').val() + '|' + rownum);
		}
		return false;
	});

	//set toggle value for text-box hint text
  $(".dpt-title").toggleVal({
    populateFrom: "title",
    changedClass: "text-normal",
    focusClass: "text-normal"
  });
	$("form").submit(function() {
    $(".dpt-title").each(function() {
      if($(this).val() == $(this).data("defText")) {
        $(this).val("");
      }
    });
	});

	//delete row when trash can is clicked
	$('.trash-tr').live('click', function(){
	$(this).parents('tr').fadeOut(500,
		function(){
			$(this).remove()
		})
	});


});


//add a new entry to the table
function addrow(user){
	$.ajax({
		type: 'POST',
	  url: location.href,
	  data: 'ajaxgettr='+encodeURIComponent(user)+'&quietmode=1',
	  success: function(data) {
	    $('#dir_entries_tbl > tbody:last').append(data);
      /* now re-apply toggleval - redundant but they may have appended multipe values so... */
      $(".dpt-title").not('.text-normal').toggleVal({
        populateFrom: "title",
        changedClass: "text-normal",
        focusClass: "text-normal"
      });
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
      var msg = "An Error occurred trying to contact the server adding a row, no reply.";
      alert(msg);
    }
  });
}
