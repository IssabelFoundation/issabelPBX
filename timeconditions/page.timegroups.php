<?php /* $Id: page.timegroups.php $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$dispnum = 'timegroups'; //used for switch on config.php

if(isset($_REQUEST['action'])) {
    if($_REQUEST['action']=='holidayfill') {
        fill_holidays($_REQUEST['extdisplay'],$_REQUEST['country']);
        die('ok set');
    }
}
?>

<?php 
$groups = timeconditions_timegroups_list_groups();
drawListMenu($groups, $type, $display, $extdisplay);
?>

<div class='content' up-main>

<div class="tag is-info servertime">
	<?php echo __("Server time")?>: <span id="idTime">00:00:00</span>
</div>

<script>
var hour = <?php $l = localtime(); echo $l[2]?>;
var min  = <?php $l = localtime(); echo $l[1]?>;
var sec  = <?php $l = localtime(); echo $l[0]?>;

//time groups stole this from timeconditions
//who stole it from http://www.aspfaq.com/show.asp?id=2300
function PadDigits(n, totalDigits) 
{ 
	n = n.toString(); 
	var pd = ''; 
	if (totalDigits > n.length) 
	{ 
		for (i=0; i < (totalDigits-n.length); i++) 
		{ 
			pd += '0'; 
		} 
	} 
	return pd + n.toString(); 
} 

function updateTime()
{
	sec++;
	if (sec==60)
	{
		min++;
		sec = 0;
	}	
		
	if (min==60)
	{
		hour++;
		min = 0;
	}

	if (hour==24)
	{
		hour = 0;
	}
	
	document.getElementById("idTime").innerHTML = PadDigits(hour,2)+":"+PadDigits(min,2)+":"+PadDigits(sec,2);
	setTimeout('updateTime()',1000);
}

updateTime();
$(document).ready(function(){
	$(".remove_section").on('click',function(){
    if (confirm('<?php echo __("This section will be removed from this time group and all current settings including changes will be updated. OK to proceed?") ?>')) {
      $(this).parent().parent().prev().remove();
      $(this).closest('form').submit();
    }
  });

  $('#autofill').on('click',function(e) { 

       e.preventDefault();

       msg = '<?php echo __("This action will remove all configured times in this groups and recreate them based on holiday calendars by Google and other providers. Are you sure you want to continue?") ?>"';
       Swal.fire({
           title: ipbx.msg.framework.areyousure,
           text: msg,
           icon: 'warning',
           showCancelButton: true,
           confirmButtonColor: '#3085d6',
           cancelButtonColor: '#d33',
           confirmButtonText: ipbx.msg.framework.yes,
           cancelButtonText: ipbx.msg.framework.cancel
       }).then((result) => {
           if (result.isConfirmed) {

               value = $('#countries').val();
               issurl = window.location.href.split('#')[0];
               issurl += '&action=holidayfill&country='+value;

               $.ajax(issurl, {
                   success: function(data) {
                       window.location.reload();
                   },
                   error: function() {
                      sweet_alert(ipbx.msg.framework.invalid_response);
                   }
               });
           }
       });
  });


});
</script>
