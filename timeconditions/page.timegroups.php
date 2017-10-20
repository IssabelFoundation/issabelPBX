<?php /* $Id: page.timegroups.php $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$dispnum = 'timegroups'; //used for switch on config.php

?>


<div class="rnav">
<?php 
$groups = timeconditions_timegroups_list_groups();
drawListMenu($groups, $skip, $type, $display, $extdisplay, _("Time Group"));
?>
</div>

<div class="rnav" style="margin:15px 10px; padding: 5px; background: #e0e0ff; border: #2E78A7 solid 1px;">
	<?php echo _("Server time:")?> <span id="idTime">00:00:00</span>
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
	$(".remove_section").click(function(){
    if (confirm('<?php echo _("This section will be removed from this time group and all current settings including changes will be updated. OK to proceed?") ?>')) {
      $(this).parent().parent().prev().remove();
      $(this).closest('form').submit();
    }
  });
});
</script>

