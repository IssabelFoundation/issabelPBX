$(function() {
    if($("#parkext").length != 0) {
		
        $("#parkext").numeric();
        $("#parkpos").numeric();
        $("#parkingtime").numeric();
    	$("#numslots").numeric();
		
        var parkext = Number($('#parkext').val());
        delete extmap[parkext]
        var parkpos = Number($('#parkpos').val());
        var numslots = Number($('#numslots').val());
        var parkend = (parkpos + numslots - 1)
        var usedslots = [];
        for(var i=parkpos;i<=parkend;i++) {
            usedslots[i] = i;
            delete extmap[i]
        }
        if($('#parkpos').val() && $('#parkext').val()) {
            if(Number($('#parkpos').val()) != parkend) {
                $('#slotslist').html('('+$('#parkpos').val()+'-'+parkend+')')
            } else {
                $('#slotslist').html('('+$('#parkpos').val()+')')
            }
        }
        
        $('#parkform').submit(function() {
            if(!$('#parkext').val()) {
                alert('Parking Lot Extension can not be blank!');
                return false;
            }
            if(!$('#name').val()) {
                alert('Parking Lot Name can not be blank!');
                return false;
            }
            if(!$('#parkpos').val()) {
                alert('Parking Lot Starting Position can not be blank!');
                return false;
            }
			
			if(!$('#goto0').val()) {
				alert('You must select a valid destination')
				return false;
			}
        });
    }
    
    $('input[type=text][name=parkext],input[type=text][name=parkpos],input[type=number][name=numslots]')
    .after(" <span style='display:none'><a href='#'><img src='images/notify_critical.png'/></a></span>").bind("keyup change", function(){
        //Recalc
        var new_parkext = Number($('#parkext').val());        
        var new_parkpos = Number($('#parkpos').val());
        var new_numslots = Number($('#numslots').val());
        var new_parkend = (new_parkpos + new_numslots - 1)
        var new_usedslots = [];
        var reset = true;
        for(var i=new_parkpos;i<=new_parkend;i++) {
            new_usedslots[i] = i;
            switch(true) {
                case new_parkext == i:
                    var type = this.id == 'parkext' ? 'Parking Slot: ' : 'Parking Lot Extension: ';
                    $(this).addClass('duplicate-exten').next('span').not('#slotslist').show().children('a').attr('title',type+i);
                    reset = false
                    break;
                case (typeof extmap[i] != "undefined"):
                    $(this).addClass('duplicate-exten').next('span').not('#slotslist').show().children('a').attr('title',extmap[i]);
                    reset = false
                    break;
                default:
                    break;
            }
            
            if(!reset) {
                break;
            }
        }
        if($('#parkpos').val() && $('#parkext').val()) {
            if(Number($('#parkpos').val()) != new_parkend) {
                $('#slotslist').html('('+$('#parkpos').val()+'-'+new_parkend+')')
            } else {
                $('#slotslist').html('('+$('#parkpos').val()+')')
            }
        }
        switch(true) {
            case (new_parkext == this.id) && (this.id != 'parkext'):
                $(this).addClass('duplicate-exten').next('span').not('#slotslist').show().children('a').attr('title','Parking Lot Extension: '+i);
                reset = false
                break;
            case (typeof extmap[this.value] !== "undefined"):
                $(this).addClass('duplicate-exten').next('span').not('#slotslist').show().children('a').attr('title',extmap[this.value]);
                reset = false
                break;
            default:
                break;
        }
        
        if(reset) {
            $(this).removeClass('duplicate-exten').next('span').hide();
            $("#parksubmit").off("click")
        } else {
            $("#parksubmit").on("click", function() {
                alert('You have an error on the form. Please go back and correct it before submitting this page');
                return false;
            });
        }
    }).each(function(){ 
        /* we automatically add a data-extdisplay data tag to the element if it is not already there and set the value that was 
        * loaded at page load time. This allows modules who are trying to guess at an extension value to preset so we don't 
        * pre-determine a value is safe when the generating code may be flawed, such as ringgroups and vmblast groups. 
        */ 
        if (typeof $(this).data('extdisplay') == "undefined") { 
            $(this).data('extdisplay', this.value); 
        } else if (typeof extmap[this.value] != "undefined") { 
            this.value++; 
            while (typeof extmap[this.value] != "undefined") { 
                this.value++; 
            } 
        } 
    });
})