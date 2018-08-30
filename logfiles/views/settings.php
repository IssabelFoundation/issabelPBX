<?php

global $amp_conf;

$html = '';
$html .= heading(_('Log File Settings'), 2);
$html .= form_open($_SERVER['REQUEST_URI'], '', array('action' => 'save'));
//general settings
$html .= heading(_('General Settings'), 5) . '<hr style="width:50%: margin-left: 0; margin-right: 50%">';
$table = new CI_Table;

//date format
$data = array(
			'name'			=> 'dateformat',
			//'id'			=> 'username',
			'value'			=> $dateformat,
			'placeholder'	=> 'Date Format'
            );
$label = ipbx_label(_('Date Format'), _('Customize the display of debug message time stamps. '
									. 'See strftime(3) Linux manual for format specifiers. '
									. 'Note that there is also a fractional second parameter '
									. 'which may be used in this field.  Use %1q for tenths, '
									. '%2q for hundredths, etc.')
									. br() . _('Leave blank for default: ISO 8601 date format '
									. 'yyyy-mm-dd HH:MM:SS (%F %T)'));
$table->add_row($label, form_input($data));

//rotate
$rotateseq = array(
			'name'		=> 'rotatestrategy',
			'id'		=> 'rotateseq',
			'value'		=> 'sequential',
			'checked'	=> ($rotatestrategy == 'sequential'),
);
$rotateseq = form_label(_('Sequential'), 'rotateseq') . form_radio($rotateseq);

$rotaterot = array(
			'name'		=> 'rotatestrategy',
			'id'		=> 'rotaterot',
			'value'		=> 'rotate',
			'checked'	=> ($rotatestrategy == 'rotate'),
);
$rotaterot = form_label(_('Rotate'), 'rotaterot') . form_radio($rotaterot);

$rotatetime = array(
			'name'		=> 'rotatestrategy',
			'id'		=> 'rotatetime',
			'value'		=> 'timestamp',
			'checked'	=> ($rotatestrategy == 'timestamp'),
);
$rotatetime = form_label(_('Timestamp'), 'rotatetime') . form_radio($rotatetime);

$help_li[] = _('Sequential: Rename archived logs in order, such that the newest has the highest sequence number');
$help_li[] = _('Rotate: Rotate all the old files, such that the oldest has the highest sequence '
			. 'number (expected behavior for Unix administrators).');
$help_li[] = _('Timestamp: Rename the logfiles using a timestamp instead of a sequence number when "logger rotate" is executed.');
$label = ipbx_label(_('Log rotation'), _('Log rotation strategy: ' . ul($help_li)));
$table->add_row($label, '<span class="radioset">' . $rotateseq . $rotaterot . $rotatetime . '</radioset>');


//append hostname
$hostnameyes = array(
			'name'		=> 'appendhostname',
			'id'		=> 'hostnameyes',
			'value'		=> 'yes',
			'checked'	=> ($appendhostname == 'yes'),
);
$hostnameyes = form_label(_('Yes'), 'hostnameyes') . form_radio($hostnameyes);

$hostnameno = array(
			'name'		=> 'appendhostname',
			'id'		=> 'hostnameno',
			'value'		=> 'no',
			'checked'	=> ($appendhostname == 'no'),
);
$hostnameno = form_label(_('No'), 'hostnameno') . form_radio($hostnameno);

$label = ipbx_label(_('Append Hostname'), _('Appends the hostname to the name of the log files'));
$table->add_row($label, '<span class="radioset">' . $hostnameyes . $hostnameno . '</radioset>');


//queue log
$queuelogyes = array(
			'name'		=> 'queue_log',
			'id'		=> 'queuelogyes',
			'value'		=> 'yes',
			'checked'	=> ($queue_log == 'yes'),
);
$queuelogyes = form_label(_('Yes'), 'queuelogyes') . form_radio($queuelogyes);

$queuelogno = array(
			'name'		=> 'queue_log',
			'id'		=> 'queuelogno',
			'value'		=> 'no',
			'checked'	=> ($queue_log == 'no'),
);
$queuelogno = form_label(_('No'), 'queuelogno') . form_radio($queuelogno);

$label = ipbx_label(_('Log Queues'), _('Log queue events to a file'));
$table->add_row($label, '<span class="radioset">' . $queuelogyes . $queuelogno . '</radioset>');

$html .= $table->generate();
$html .= br(2);



//log files
$html .= heading(_('Log Files'), 5) . '<hr style="width:50%: margin-left: 0; margin-right: 50%">';

$table = new CI_Table;
$table->set_template(array('table_open' => '<table class="alt_table" id="logfile_entries">'));

//draw table header with help on every option
$has_security_option = version_compare($amp_conf['ASTVERSION'],'11.0','ge');
$heading = array(
			ipbx_label(_('File Name'), _('Name of file, relative to TODO!!!!. Use absolute path for a different location')),
			ipbx_label(_('Debug'), 'debug: ' . _('Messages used for debuging. '
									. 'Do not report these as error\'s unless you have a '
									. 'specific issue that you are attempting to debug. '
									. 'Also note that Debug messages are also very verbose '
									. 'and can and do fill up logfiles (and disk storage) quickly.')),
			ipbx_label(_('DTMF'), 'dtmf: ' . _('Keypresses as understood by asterisk. Usefull for debuging IVR and VM issues.')),
			ipbx_label(_('Error'), 'error: ' . _('Critical errors and issues')),
			ipbx_label(_('Fax'), 'fax: ' . _('Transmition and receiving of faxes')),
			ipbx_label(_('Notice'), 'notice: ' . _('Messages of specific actions, such as a phone registration or call completion')),
			ipbx_label(_('Verbose'), 'verbose: ' . _('Step-by-step messages of every step of a call flow. '
										. 'Always enable and review if calls dont flow as expected')),
			ipbx_label(_('Warning'), 'warning: ' . _('Possible issues with dialplan syntaxt or call flow, but not critical.'))
		);

if ($has_security_option) { 
	$heading[] = ipbx_label(_('Security'), 'security: ' . _('Notification of security related events such as authentication attempts.')); 
}

$heading[] = ipbx_label(_('Delete'));
$table->set_heading($heading);


//actual log files
$count = 0;
//$logfiles[] = array('name' => 'test', 'debug' => 'off', 'dtmf' => 'off', 'error' => 'on', 'fax' => 'off', 'notice' => 'on', 'verbose' => 'on', 'warning' => 'on');

foreach ($logfiles as $l) {
	$row[] = form_input(
				array(
					'name'			=> 'logfiles[name][]',
					'value'			=> $l['name'],
					'placeholder'	=> _('file path/name'),
					'required'		=> ''
				)
			);
	
	$onoff = array(
			'on'	=> _('On'),
			'off'	=> _('Off')
	);
	
	$row[] = form_dropdown('logfiles[debug][]', $onoff, $l['debug']);
	$row[] = form_dropdown('logfiles[dtmf][]', $onoff, $l['dtmf']);
	$row[] = form_dropdown('logfiles[error][]', $onoff, $l['error']);
	$row[] = form_dropdown('logfiles[fax][]', $onoff, $l['fax']);
	$row[] = form_dropdown('logfiles[notice][]', $onoff, $l['notice']);
	$row[] = form_dropdown('logfiles[verbose][]', $onoff, $l['verbose']);
	$row[] = form_dropdown('logfiles[warning][]', $onoff, $l['warning']);
	if ($has_security_option) { 
		$row[] = form_dropdown('logfiles[security][]', $onoff, $l['security']); 
	}
	$row[] = '<img src="images/trash.png" style="cursor:pointer" title="' 
			. _('Delete this entry. Click Submit to save changes') 
			. '" class="delete_entry">';
	$table->add_row(array_values($row));
	unset($row);
}

$html .= $table->generate() . br();
$html .= '<img class="IVREntries" src="/admin/modules/logfiles/assets/images/add.png" style="cursor:pointer" title="' . _('New Log File') 
		. '" id="add_entry">';

$html .= br(4) . form_submit('save', _('Save'));
$html .= form_close();
$html .= '<script type="text/javascript" src="/admin/modules/logfiles/assets/js/views/settings.js"></script>';
echo $html;
?>
