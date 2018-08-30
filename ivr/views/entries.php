<?php
$table = new CI_Table;
$table->set_template(array('table_open' => '<table class="alt_table IVREntries" id="ivr_entries">'));
//build header
$h = array();
foreach($headers as $mod => $header) {
	$h += $header;
}
$table->set_heading($h);

$count = 0;
foreach ($entries as $e) {
	$count++;

	//add ext to dial
	$row[] = form_input(
				array(
					'name'			=> 'entries[ext][]',
					'value'			=> $e['selection'],
					'placeholder'	=> _('digits pressed'),
					'required'		=> ''
				)
			);
	
	//add destination. The last one gets a different count so that we can manipualte it on the page
	if ($count == count($entries)) {
		$row[] = drawselects($e['dest'], 'DESTID', false, false) . form_hidden('entries[goto][]', '');
	} else {
		$row[] = drawselects($e['dest'], $count, false, false) . form_hidden('entries[goto][]', '');
	}
	
	
	//return to ivr
	$row[] = ipbx_label(form_checkbox('entries[ivr_ret][]', '1', ($e['ivr_ret'] == 1)), 
			_('Check this box to have this option return to a parent IVR if it was called '
			. 'from a parent IVR. If not, it will go to the chosen destination.<br><br>'
			. 'The return path will be to any IVR that was in the call path prior to this '
			. 'IVR which could lead to strange results if there was an IVR called in the '
			. 'call path but not immediately before this'));

	//delete buttom
	$row[] = '<img src="images/trash.png" style="cursor:pointer" title="' 
	. _('Delete this entry. Dont forget to click Submit to save changes!') 
	. '" class="delete_entrie">';
		
	//add module hooks	
	if (isset($e['hooks']) && $e['hooks']) {
		foreach ($e['hooks'] as $module => $hooks) {
			foreach ($hooks as $h) {
				$row[] = $h;
			}
		}
		
	}
	

	$table->add_row(array_values($row));	
	
	unset($row);
}

$ret = '';
$ret .= $table->generate();
$ret .= '<img class="IVREntries" src="modules/ivr/assets/images/add.png" style="cursor:pointer" title="' . _('Add Entry') 
		. '" id="add_entrie">';


echo $ret;
?>
