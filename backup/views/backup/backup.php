<?php
$html = '';
$html .= heading(_('Backup'), 3) . '<hr class="backup-hr"/>';
$html .= form_open($_SERVER['REQUEST_URI'], 'id="backup_form"');
$html .= form_hidden('action', 'save');
$html .= form_hidden('id', $id);

$table = new CI_Table;

//name
$label	= ipbx_label(_('Backup Name'));
$data 	= array(
			'name' => 'name', 
			'value' => $name
		);
//$immortal ? $data['disabled'] = '' : '';
$table->add_row($label, form_input($data));

//description
$label	= ipbx_label(_('Description'), _('Description or notes for this backup'));
$data 	= array(
			'name' => 'desc', 
			'value' => $desc
		);

//$immortal ? $data['disabled'] = '' : '';
$table->add_row($label, form_input($data));

//email
$label = ipbx_label(_('Status Email'), _('Email to send status messages to when this task is run'));
$data = array(
			'name' => 'email',
			'value' => $email
		);
//$immortal ? $data['disabled'] = '' : '';
$table->add_row($label, form_input($data));
$html .= $table->generate();
$html .= $table->clear();

//ITEMS
$html .= heading(_('Items'), 5) . '<hr class="backup-hr"/>';
$current = load_view(dirname(__FILE__) . '/../item_table.php', 
			array('items' => $items, 'immortal' => ''));
$current .= '<div id="items_over">' . _('drop here') . '</div>';
$template_list = '<ul id="templates" class="sortable">';
foreach ($templates as $t) {
	$template_list .= '<li data-template="' . rawurlencode(json_encode($t['items'])) . '"'
					. ' title="' . $t['desc'] . '"'
					.'>' 
					. '<a href="#">'
					. '<span class="dragable"></span>'
					. $t['name'] 
					. '</a>'
					. '</li>';
}
$template_list .= '</ul>';

$table->set_heading(
			ipbx_label(_('Backup Items'), 
				_('Drag templates and drop them in the items table to add the templates items to the table'))
			, 
			ipbx_label(_('Templates'), _('Drag templates and drop them in the Backup Items table. '
										. 'Add as many templates as you need')));
$table->add_row($current, array('data' => $template_list, 'style' => 'padding-left: 100px;padding-right: 100px'));
$html .= $table->generate();
$html .= $table->clear();


//HOOKS
//pre backup hook
$html .= heading(_('Hooks'), 5) . '<hr class="backup-hr"/>';
$label	= ipbx_label(_('Pre-backup Hook'), _('A script to be run BEFORE a backup is started.'));
$data 	= array(
			'name' => 'prebu_hook', 
			'value' => $prebu_hook
		);
//$immortal ? $data['disabled'] = '' : '';
$table->add_row($label, form_input($data));

//post backup hook
$label	= ipbx_label(_('Post-backup Hook'), _('A script to be run AFTER a backup is completed.'));
$data 	= array(
			'name' => 'postbu_hook', 
			'value' => $postbu_hook
		);
//$immortal ? $data['disabled'] = '' : '';
$table->add_row($label, form_input($data));

//pre-restore backup hook
$label	= ipbx_label(_('Pre-restore Hook'), _('A script to be run BEFORE a backup is restored.'));
$data 	= array(
			'name' => 'prere_hook', 
			'value' => $prere_hook
		);
	
//$immortal ? $data['disabled'] = '' : '';
$table->add_row($label, form_input($data));

//post-restore backup hook
$label	= ipbx_label(_('Post-restore Hook'), _('A script to be run AFTER a backup is restored.'));
$data 	= array(
			'name' => 'postre_hook', 
			'value' => $postre_hook
		);
//$immortal ? $data['disabled'] = '' : '';
$table->add_row($label, form_input($data));
$html .= $table->generate();
$html .= $table->clear();


//BACKUP Server
$html .= heading(_('Backup Server'), 5) . '<hr class="backup-hr"/>';
$data = array();

//hardcode THIS server, as there isnt really any other way of relating to it
//as its not a local server in that its not filesystem dependant (it depends on the whole issabelpbx/asterisk shebang)
$data[0] = _('This server');
foreach ($servers as $s) {
	if ($s['type'] == 'ssh') {
		$data[$s['id']] = $s['name'];
	}
}

$label = ipbx_label(
			_('Backup Server'), 
			_('Select the server to be backed up (this server, or any other SSH server)')
		);
$label = form_label($label, 'bu_server');
$table->add_row($label, form_dropdown('bu_server', $data, $bu_server));

$label = form_label($label, 'restore');
$data = array(
	'name'		=> 'restore',
	'id'		=> 'restore',
	'value'		=> 'true',
	'checked'	=> ($restore == 'true' ? true : false),
);
$label = ipbx_label(_('Restore Here'), 'Restored backup to this server after the backup is complete');
$label = array('data' => form_label($label, 'restore'), 'class' => 'remote ');
$data = array('data' => form_checkbox($data), 'class' => 'remote ');
$table->add_row($label, $data);

//disbale trunks
$label = ipbx_label(_('Disable Registered Trunks'), 
		'After a restore, disable any trunks that use registration. This is helpfull to '
		. 'prevent the Primary and Standby systems from "fighting" for the '
		. 'registration, resulting in some calls routed to the Standby system.');
$data = array(
	'name'		=> 'disabletrunks',
	'id'		=> 'disabletrunks',
	'value'		=> 'true',
	'checked'	=> ($disabletrunks == 'true' ? true : false),
);
$label = array('data' => form_label($label, 'disabletrunks'), 'class' => 'remote restore');
$data = array('data' => form_checkbox($data), 'class' => 'remote restore');
$table->add_row($label, $data);

//apply configs
$label = ipbx_label(_('Apply Configs'), 
		'Equivalence of clicking the red button, will happen automatically after a restore on a Standby system');
$data = array(
	'name'		=> 'applyconfigs',
	'id'		=> 'applyconfigs',
	'value'		=> 'true',
	'checked'	=> ($applyconfigs == 'true' ? true : false),
);
$label = array('data' => form_label($label, 'applyconfigs'), 'class' => 'remote restore');
$data = array('data' => form_checkbox($data), 'class' => 'remote restore');
$table->add_row($label, $data);


$html .= $table->generate();
$html .= $table->clear();


//SERVERS
$html .= heading(_('Storage Locations'), 5) . '<hr class="backup-hr"/>';
foreach ($storage_servers as $s) {
	$html .= '<input type="hidden" name="storage_servers[]" value="' . $s . '">';
}
$current_servers = '<ul id="storage_used_servers" class="sortable storage_servers">';

foreach ($storage_servers as $idx => $s) {
	$current_servers .= '<li data-server-id="' . $servers[$s]['id'] . '">' 
					. '<a href="#">'
					. '<span class="dragable"></span>'
					. $servers[$s]['name'] 
					. ' (' . $servers[$s]['type'] . ')'
					. '</a>'
					. '</li>';
	unset($servers[$s]);
}
$current_servers .= '</ul>';
$avalible_servers = '<ul id="storage_avail_servers" class="sortable storage_servers">';
foreach ($servers as $s) {
	if (in_array($s['type'], array('ftp', 'ssh', 'email', 'local'))) {
		$avalible_servers .= '<li data-server-id="' . $s['id'] . '">' 
						. '<a href="#">'
						. '<span class="dragable"></span>'
						. $s['name'] 
						. ' (' . $s['type'] . ')'
						. '</a>'
						. '</li>';
	}
}
$avalible_servers .= '</ul>';
$table->set_heading(
			ipbx_label(_('Storage Servers'), 
				_('drag servers from the Available Servers list to add them as Storage Servers'))
			, _('Available Servers'));
$table->add_row($current_servers, array('data' => $avalible_servers, 'style' => 'padding-left: 100px;padding-right: 100px'));
$html .= $table->generate();
$html .= $table->clear();

//SCHEDULE
$html .= heading(_('Backup Schedule'), 5) . '<hr class="backup-hr"/>';
$cron = array(
	'cron_dom'			=> $cron_dom,
	'cron_dow'			=> $cron_dow,
	'cron_hour'			=> $cron_hour,
	'cron_minute'		=> $cron_minute,
	'cron_month'		=> $cron_month,
	'cron_random'		=> $cron_random,
	'cron_schedule'		=> $cron_schedule
);
$html .= load_view(dirname(__FILE__) . '/../cron.php', $cron);

//MAINTENANCE
$html .= heading(_('Maintenance'), 5) . '<hr class="backup-hr"/>';
$label	= ipbx_label(_('Delete after'), _('Delete this backup after X amount of minutes/hours/days/weeks/months/years. Please note that deletes aren\'t time based and will only happen after a backup was run. Setting the value to 0 will disable any deleting'));
$data 	= array(
			'name' 	=> 'delete_time', 
			'value' => $delete_time,
			'type'	=> 'number',
			'min'	=> 0
		);
//$immortal ? $data['disabled'] = '' : '';
$data2 = array(
			'minutes'	=> _('Minutes'),
			'hours'		=> _('Hours'),
			'days'		=> _('Days'),
			'weeks'		=> _('Weeks'),
			'months'	=> _('Months'),
			'years'		=> _('Years')
);
$table->add_row($label, form_input($data) . ' ' . form_dropdown('delete_time_type', $data2, $delete_time_type));
$label	= ipbx_label(_('Delete after'), _('Delete this backup after X amount of runs. Setting the value to 0 will disable any deleting'));
$data 	= array(
			'name'	=> 'delete_amount', 
			'value' => $delete_amount,
			'type'	=> 'number',
			'min'	=> 0
		);
//$immortal ? $data['disabled'] = '' : '';
$table->add_row($label, form_input($data) . _(' runs'));
$html .= $table->generate();
$html .= $table->clear();

$html .= br(3);
if ($immortal != 'true') {
	$html .= '<span class="radioset">';
	$html .= form_submit(array(
					'name'	=> 'submit',
					'value'	=> _('Save'),
					'id'	=> 'save_backup'
	));
	//can only run saved backups
	if ($id) {
		$html .= form_button(array('content' => _('and Run'), 'id' => 'run_backup'));
	}
	$html .= '</span>';
	$html .= form_submit('submit', _('Delete'));
}


$html .= form_close(). PHP_EOL;

$html .= '<script type="text/javascript" src="modules/backup/assets/js/views/backup.js"></script>';

echo $html;
