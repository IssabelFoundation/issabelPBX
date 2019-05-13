<script language="javascript">
	function deleteGroup(question, url)
	{ 
		if (confirm (question))
		{ 
			window.location.href=url;
		} 
	}
</script>

<input type="button" name="delete" value="{delete_button_label}" onclick="deleteGroup('{delete_question}','{delete_url}');" />
