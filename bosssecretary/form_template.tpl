{messages}
<form method="post" name=bosssecretary action="{form_url}">
<table>
			<tr>
				<td colspan="3"><h5>{form_title}</h5></td>
			</tr>			
			<tr>
				<td colspan="3"><label>{group_label_title}</label> <input type="text" name= "group_label" value="{group_label}"/></td>				
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
				<td><textarea name="bosses_extensions">{bosses_extensions}</textarea></td>
				<td><textarea name="secretaries_extensions">{secretaries_extensions}</textarea></td>
				<td><textarea name="chiefs_extensions">{chiefs_extensions}</textarea></td>
			</tr>
			<tr>
				<td colspan="3" align="center"> <input type="submit" name="clean{action}" value="{clean_and_remove_duplicates}" />
			</tr>
			<tr>
				<td colspan="3"><hr /></td>
			</tr>
			<tr>
				<td colspan="3" align="center"><input type="hidden" name="group_number" value="{group_number}" /> <input type="submit" name="submit{action}" value="{save}" /> {delete_button}</td>
			</tr>
</table>
</form> 


