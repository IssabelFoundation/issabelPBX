<?php

$html = ''; 
$html .= heading(_('Paging'), 3) . '<hr class="paging-hr"/>';

$html .= form_open($_SERVER['REQUEST_URI'], 'id="page_opts_form"');
$html .= form_hidden('display', $display);
$html .= form_hidden('pagegrp', $extdisplay);
$html .= form_hidden('extdisplay', $extdisplay);
$html .= form_hidden('action', 'submit');

$table = new CI_Table;
if ($conflict_url) {
	$html .= heading(_('Conflicting Extensions'));
	$html .= implode(br(), $conflict_url);
}
if ($extdisplay) {
	$table->add_row(array('colspan' => 2,
		'data' => heading(_('Modify Paging Group'), 5) . '<hr />'));
} else {
	$table->add_row(array('colspan' => 2,
		'data' => heading(_('Add Paging Group'), 5) . '<hr />'));
}

//extension
$label = ipbx_label(_('Paging Extension'),
			_('The number users will dial to page this group'));
$table->add_row($label, form_input('pagenbr', $extdisplay, 'class="extdisplay"'));

//description
$label = ipbx_label(_('Group Description'),
			_('Provide a descriptive title for this Page Group.'));
$table->add_row($label, form_input('description', $description));

//device list
$label = ipbx_label(_('Device List'), 
			_('Devices to page. Please note, paging calls the '
			. 'actual device (and not the user). Amount of pagable devices is '
			. 'restricted by the advanced setting key PAGINGMAXPARTICIPANTS '
			. 'and is currently set to ') . $amp_conf['PAGINGMAXPARTICIPANTS']
);
$selected_dev = $notselected_dev = '';
foreach ($device_list as $ext => $name) {
	if (in_array((string)$ext, $devices)) {
		$selected_dev .= '<span data-ext="' . $ext . '">' . $name .'</span>';
	} else {
		$notselected_dev .= '<span data-ext="' . $ext . '">' . $name .'</span>';
	}
} 
$class = ' class="device_list ui-sortable ui-menu ui-widget ui-widget-content ui-corner-all" ';
$selected_dev = form_fieldset(_('Selected'), $class . 'id="selected_dev" ' )
				. $selected_dev 
				. form_fieldset_close();
$notselected_dev = form_fieldset(_('Not Selected'), $class . 'id="notselected_dev" ' )
				. $notselected_dev 
				. form_fieldset_close();
$table->add_row($label);
$table->add_row('', $selected_dev, $notselected_dev);

//busy ext
$help[] = _('"Skip" will not page any busy extension. All other extensions '
			. 'will be paged as normal');
$help[] = _('"Force" will not check if the device is in use before paging '
			. 'it. This means conversations can be interrupted by a page '
			. '(depending on how the device handles it). This is useful '
			. 'for "emergency" paging groups.');
$help[] = _('"Whisper" will attempt to use the ChanSpy capability on SIP '
			. 'channels, resulting in the page being "sent to the '
			. 'device\'s earpiece "whispered" to the user but not heard '
			. 'by the remote party. If ChanSpy is not supported on the '
			. 'device or otherwise fails, no page will get through. It '
			. 'probably does not make too much sense to choose duplex if '
			. 'using Whisper mode.');

$data = array(
		'id'	=> 'force_page_no',
		'name'	=> 'force_page',
		'value'	=> 0
);
$force_page == 0 ? $data['checked'] = 'checked' : '';
$skip = form_label(_('Skip'), 'force_page_no')
		. form_radio($data);

$data = array(
		'id'	=> 'force_page_yes',
		'name'	=> 'force_page',
		'value'	=> 1
);
$force_page == 1 ? $data['checked'] = 'checked' : '';
$force = form_label(_('Force'), 'force_page_yes')
		. form_radio($data);


$data = array(
		'id'	=> 'force_page_whisper',
		'name'	=> 'force_page',
		'value'	=> 2
);
$force_page == 2 ? $data['checked'] = 'checked' : '';
$whisper = form_label(_('Whisper'), 'force_page_whisper')
		. form_radio($data);

$label = ipbx_label(_('Busy Extensions'), ul($help));
$table->add_row(
	$label, 
	'<span class="radioset">'
	. $skip . $force . $whisper
	. '</span>'
);

//duplex
$label = ipbx_label(_('Duplex'), 
			_('Paging is typically one way for announcements only. '
			. 'Checking this will make the paging duplex, allowing all '
			. 'phones in the paging group to be able to talk and be '
			. 'heard by all. This makes it like an "instant conference"'));
$table->add_row($label, form_checkbox('duplex', 1, $duplex));

//default
$label = ipbx_label(_('Default Page Group'));
$table->add_row($label, form_checkbox('default_group', 1, $default_group));

$html .= $table->generate();
$html .= $hooks . br(2);

$html .= form_submit('Submit', _('Submit'));
if ($extdisplay) {
	$html .= form_submit('Submit', _('Delete'));
}


echo $html;
