<?php
$html = '';

$data = array(
			'never'		=> _('Never'),
			'hourly'	=> _('Hourly'),
			'daily'		=> _('Daily'),
			'weekly'	=> _('Weekly'),
			'monthly'	=> _('Monthly'),
			'annually'	=> _('Annually'),
			'reboot'	=> _('Reboot'),
			'custom'	=> _('Custom')
);
$txt = <<<EOM
Select how often to reset queue stats. The following schedule will be followed for all but custom:<br/>
Hourly &nbsp&nbspRun once an hour, beginning of hour<br/>
Daily &nbsp&nbsp&nbsp&nbspRun once a day, at midnight<br/>
Weekly &nbsp&nbspRun once a week, midnight on Sun<br/>
Monthly &nbsp&nbspRun once a month, midnight, first of month<br/>
Annually &nbspRun once a year, midnight, Jan. 1<br/>
Reboot &nbsp&nbspRun at startup of the server OR of the cron deamon (i.e. after every <code>service cron restart</code>)<br/>
<br/>
If Randomize is selcted, a similar frequency will be followed, only the exact times will be randomized (avoiding peak business hours, when possible). Please note: randomized schedules will be rescheduled (randomly) every time ANY backup is saved
<br/><br/>
Never will never reset the queue stats automatically
<br/><br/>
If a custom schedule is selected, any section not specficed will be considered to be "any" (aka: wildcard).
I.e. if Day of Month is set to 12 and Day of Week is not set, the queue stats will be reset on ANY 12th of
the month - regardless of the day of the week. If Day of Week is set to, say, Monday, the queue stats will be reset ONLY
 on a Monday, and ONLY if it's the 12th of the month.
EOM;
$label = ipbx_label(_('Run'), _($txt));
$html .= $label . ' ' . form_dropdown('cron_schedule', $data, $cron_schedule);
$data = array(
	'name'		=> 'cron_random',
	'id'		=> 'cron_random',
	'value'		=> 'true',
	'checked'	=> ($cron_random == 'true' ? true : false),
);

$html .= br() . form_label('Randomize', 'cron_random') . form_checkbox($data);

$html .= '<div id="crondiv">';
//minutes
$html .= form_fieldset(_('Minutes'), ' class="cronset sortable cronsetheight ui-sortable ui-menu ui-widget ui-widget-content ui-corner-all" ');
$html .= '<div class="cronsetdiv">';
for($i = 0; $i < 60; $i++) {
	$html .= form_label(sprintf("%02d", $i), 'cron_minute' . $i);
	$data = array(
		'name'	=> 'cron_minute[]',
		'id'	=> 'cron_minute' . $i,
		'value'	=> $i,
	);
	in_array($i, $cron_minute) ? $data['checked'] = 'checked' : '';
	$html .= form_checkbox($data) . ' ';
}
$html .= '</div>';
$html .= form_fieldset_close();

//hours
$html .= form_fieldset(_('Hour'), ' class="cronset sortable cronsetheight ui-sortable ui-menu ui-widget ui-widget-content ui-corner-all" ');
$html .= '<div class="cronsetdiv">';
for($i = 0; $i < 24; $i++) {
	$html .= form_label(sprintf("%02d", $i), 'cron_hour' . $i);
	$data = array(
		'name'	=> 'cron_hour[]',
		'id'	=> 'cron_hour' . $i,
		'value'	=> $i,
	);
	in_array($i, $cron_hour) ? $data['checked'] = 'checked' : '';
	$html .= form_checkbox($data) . ' ';
}
$html .= '</div>';
$html .= form_fieldset_close();

//day of week
$html .= form_fieldset(_('Day of Week'), ' class="cronset narrow sortable cronsetheight ui-sortable ui-menu ui-widget ui-widget-content ui-corner-all" ');
$html .= '<div class="cronsetdiv">';
$doy = array(
		'0' => _('Sunday'),
		'1' => _('Monday'),
		'2' => _('Tuesday'),
		'3' => _('Wednesday'),
		'4' => _('Thursday'),
		'5' => _('Friday'),
		'6' => _('Saturday'),
);
foreach ($doy as $k => $v) {
	$html .= form_label($v, 'cron_dow' . $k);
	$data = array(
		'name'	=> 'cron_dow[]',
		'id'	=> 'cron_dow' . $k,
		'value'	=> $k,
	);
	in_array((string) $k, $cron_dow) ? $data['checked'] = 'checked' : '';
	$html .= form_checkbox($data) . ' ';
}
$html .= '</div>';
$html .= form_fieldset_close();

//month
$html .= form_fieldset(_('Month'), ' class="cronset narrow sortable cronsetheight ui-sortable ui-menu ui-widget ui-widget-content ui-corner-all" ');
$html .= '<div class="cronsetdiv">';
$doy = array(
		'1' => _('January'),
		'2' => _('February'),
		'3' => _('March'),
		'4' => _('April'),
		'5' => _('May'),
		'6' => _('June'),
		'7' => _('July'),
		'8' => _('August'),
		'9' => _('September'),
		'10' => _('October'),
		'11' => _('November'),
		'12' => _('December'),
);
foreach ($doy as $k => $v) {
	$html .= form_label($v, 'cron_month' . $k);
	$data = array(
		'name'	=> 'cron_month[]',
		'id'	=> 'cron_month' . $k,
		'value'	=> $k,
	);
	in_array($k, $cron_month) ? $data['checked'] = 'checked' : '';
	$html .= form_checkbox($data) . ' ';
}
$html .= '</div>';
$html .= form_fieldset_close();

//day of month
$html .= form_fieldset(_('Day of Month'), ' class="cronset sortable cronsetheight ui-sortable ui-menu ui-widget ui-widget-content ui-corner-all" ');
$html .= '<div class="cronsetdiv">';
for($i = 1; $i < 32; $i++) {
	$html .= form_label(sprintf("%02d", $i), 'cron_dom' . $i);
	$data = array(
		'name'	=> 'cron_dom[]',
		'id'	=> 'cron_dom' . $i,
		'value'	=> $i,
	);
	in_array($i, $cron_dom) ? $data['checked'] = 'checked' : '';
	$html .= form_checkbox($data) . ' ';
}
$html .= '</div>';
$html .= form_fieldset_close();
$html .= '</div>';
echo $html;
?>
