{messages}
<form method="post" name=bosssecretary action="{form_url}">
<table>
			<tr>
				<td colspan="3"><h5>{form_title}</h5> <hr /> </td>
			</tr>			
			<tr>
				<td colspan="3"><label>Group Label:</label> <input type="text" name= "group_label" value="{group_label}"/></td>				
			</tr>
			<tr>
				<td colspan="3"><hr /></td>
			</tr>
			<tr>
				<td align="center"><a href="#" class="info"><span>Put bosses extensions here</span>Bosses</a> </td>
				<td align="center"><a href="#" class="info"><span>Put secretaries extensions here</span>Secretaries</a> </td>
				<td align="center"><a href="#" class="info"><span>Put chiefs extensions here</span>Chiefs</a> </td>
			</tr>
			<tr>
				<td><textarea name="bosses_extensions">{bosses_extensions}</textarea></td>
				<td><textarea name="secretaries_extensions">{secretaries_extensions}</textarea></td>
				<td><textarea name="chiefs_extensions">{chiefs_extensions}</textarea></td>
			</tr>
			<tr>
				<td colspan="3" align="center"> <input type="submit" name="clean{action}" value="Clean and remove duplicates" />
			</tr>
			<tr>
				<td colspan="3"><hr /></td>
			</tr>
			<tr>
				<td colspan="3" align="center"><input type="hidden" name="group_number" value="{group_number}" /> <input type="submit" name="submit{action}" value="Save" /> {delete_button}</td>
			</tr>
</table>
</form> 


