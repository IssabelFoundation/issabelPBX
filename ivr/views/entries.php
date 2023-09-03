<?php
$table = new CI_Table;
$table->set_template(array('table_open' => '<table class="table is-striped is-narrow notfixed IVREntries" id="ivr_entries">'));
//build header
$h = array();
foreach($headers as $mod => $header) {
	$h += $header;
}
$table->set_heading($h);

$show_spoken=0;
if(file_exists("/etc/asterisk/res-speech-vosk.conf")) {
    $show_spoken=1;
}

$count = 0;
foreach ($entries as $e) {
	$count++;

	//add ext to dial
	$row[] = form_input(
				array(
					'name'			=> 'entries[ext][]',
					'value'			=> $e['selection'],
					'placeholder'	=> __('digits pressed'),
					'required'		=> ''
                ),
                '','class="input"'
			);
	
	//add destination. The last one gets a different count so that we can manipualte it on the page
	if ($count == count($entries)) {
		$row[] = drawselects($e['dest'], 'DESTID', false, false) . form_hidden('entries[goto][]', '');
	} else {
		$row[] = drawselects($e['dest'], $count, false, false) . form_hidden('entries[goto][]', '');
	}
	
	
	//return to ivr
	$row[] = ipbx_label(form_switch('entries[ivr_ret][]', '1', ($e['ivr_ret'] == 1)), 
			__('Check this box to have this option return to a parent IVR if it was called '
			. 'from a parent IVR. If not, it will go to the chosen destination.<br><br>'
			. 'The return path will be to any IVR that was in the call path prior to this '
			. 'IVR which could lead to strange results if there was an IVR called in the '
			. 'call path but not immediately before this'));

    if($show_spoken==1) {

        $row[] = form_input(
				array(
					'name'			=> 'entries[spoken][]',
					'value'			=> $e['spoken'],
					'placeholder'	=> __('spoken word'),
                ),
                '','class="input"'
        );

    }
    $row[] = '<button type="button" class="delete_entrie button is-small is-danger has-tooltip-right" data-tooltip="'.__('Delete').'"><span class="icon is-small""><i class="fa fa-trash"></i></span></button>';
		
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
$ret .= '<button class="button is-small is-rounded" id="add_entrie"><span class="icon is-small is-left"><i class="fa fa-plus"></i></span><span>'.__('Add Entry').'</span></button>';

$ret .= "<script>var clone = \$(\"#ivr_entries tr:last\").clone(true);</script>";

echo $ret;
?>
