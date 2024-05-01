<form method="post" name=bosssecretary action="{form_url}" id="mainform" onsubmit="return edit_onsubmit(this)">
<input type='hidden' name='action' value='{action}'>
<input type='hidden' name='extdisplay' value='{group_number}'>
<table class="table is-borderless is-narrow">
			<tr>
				<td colspan="3"><h5>{form_title}</h5></td>
			</tr>			
			<tr>
				<td colspan="3"><label>{group_label_title}</label> <input class="input" type="text" name= "group_label" value="{group_label}"/></td>				
			</tr>
			<tr>
				<td colspan="3"><hr /></td>
			</tr>
			<tr>
				<td align="center"><a href="#" class="info"><span>{bosses_help}</span>{bosses_label}</a> </td>
				<td align="center"><a href="#" class="info"><span>{secretaries_help}</span>{secretaries_label}</a> </td>
				<td align="center"><a href="#" class="info"><span>{chiefs_help}</span>{chiefs_label}</a> </td>
			</tr>
			<tr>
				<td><textarea class='textarea' name="bosses_extensions">{bosses_extensions}</textarea class='textarea'></td>
				<td><textarea class='textarea' name="secretaries_extensions">{secretaries_extensions}</textarea class='textarea'></td>
				<td><textarea class='textarea' name="chiefs_extensions">{chiefs_extensions}</textarea class='textarea'></td>
			</tr>
</table>
</form> 
{toast_and_submit} 
