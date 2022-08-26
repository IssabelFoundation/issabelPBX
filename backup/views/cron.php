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
$txt = "Select how often to run this backup. The following schedule will be followed for all but custom:<br/>";
$txt.= "Hourly &nbsp&nbspRun once an hour, beginning of hour<br/>";
$txt.= "Daily &nbsp&nbsp&nbsp&nbspRun once a day, at midnight<br/>";
$txt.= "Weekly &nbsp&nbspRun once a week, midnight on Sun<br/>";
$txt.= "Monthly &nbsp&nbspRun once a month, midnight, first of month<br/>";
$txt.= "Annually &nbspRun once a year, midnight, Jan. 1<br/>";
$txt.= "Reboot &nbsp&nbspRun at startup of the server OR of the cron deamon (i.e. after every <code>service cron restart</code>)<br/>";
$txt.= "<br/>";
$txt.= "If Randomize is selcted, a similar frequency will be followed, only the exact times will be randomized (avoiding peak business hours, when possible). Please note: randomized schedules will be rescheduled (randomly) every time ANY backup is saved";
$txt.= "<br/><br/>";
$txt.= "Never will never run the backup automatically";
$txt.= "<br/><br/>";
$txt.= "If a custom schedule is selected, any section not specficed will be considered to be 'any' (aka: wildcard).";
$txt.= "I.e. if Day of Month is set to 12 and Day of Week is not set, the Backup will be run on ANY 12th of ";
$txt.= "the month - regardless of the day of the week. If Day of Week is set to, say, Monday, the Backup will run ONLY ";
$txt.= "on a Monday, and ONLY if it's the 12th of the month.";
$label = ipbx_label(_('Run Automatically'), _($txt));
$html .= $label . ' ' . form_dropdown('cron_schedule', $data, $cron_schedule, ' class="componentSelect" ');
$data = array(
	'name'		=> 'cron_random',
	'id'		=> 'cron_random',
	'value'		=> 'true',
	'checked'	=> ($cron_random == 'true' ? true : false),
);

$html .= br() . form_label(_('Randomize'), 'cron_random') . form_checkbox($data);

$html .= '<div id="crondiv" class="columns">';
//minutes
$html .= "<div class='column'>";
$html .= form_fieldset(_('Minutes'), ' class="cronset ui-widget-content" ');
$html .= '<div class="cronsetdiv">';

for($i = 0; $i < 60; $i++) {
    $checked = in_array($i, $cron_minute) ? "checked='checked' " : "";
    $label = sprintf("%02d",$i);
    $html .= nice_checkbox($i, $label,$checked,'cron_minute[]');
}

$html .= '</div>';
$html .= '</div>';
$html .= form_fieldset_close();

//hours
$html .= "<div class='column'>";
$html .= form_fieldset(_('Hour'), ' class="cronset ui-widget-content" ');
$html .= '<div class="cronsetdiv">';

for($i = 0; $i < 24; $i++) {
    in_array($i, $cron_hour) ? $data['checked'] = 'checked' : '';
    $checked = in_array($i, $cron_hour) ? "checked='checked' " : "";
    $label = sprintf("%02d",$i);
    $html .= nice_checkbox($i,$label,$checked,'cron_hour[]');
}

$html .= '</div>';
$html .= '</div>';
$html .= form_fieldset_close();

//day of week
$html .= "<div class='column'>";
$html .= form_fieldset(_('Day of Week'), ' class="cronset ui-widget-content" ');
$html .= '<div class="cronsetdiv">';
$doy = array(
		'0' => dgettext('amp','Sunday'),
		'1' => dgettext('amp','Monday'),
		'2' => dgettext('amp','Tuesday'),
		'3' => dgettext('amp','Wednesday'),
		'4' => dgettext('amp','Thursday'),
		'5' => dgettext('amp','Friday'),
		'6' => dgettext('amp','Saturday'),
);
foreach ($doy as $k => $v) {
    $checked = in_array((string)$k, $cron_dow) ? "checked='checked' " : "";
    $label = $v;
    $html .= nice_checkbox($k, $label,$checked,'cron_dow[]');
}

$html .= '</div>';
$html .= '</div>';
$html .= form_fieldset_close();

$html .= "
<script>
maxW=0;
\$('.cron_dow').each(function () {
    var x = $(this).width();
    if (x > maxW) {
        maxW = x;
    }
});
\$('.cron_dow').css('width', maxW + 'px');
</script>
";


//month
$html .= "<div class='column'>";
$html .= form_fieldset(_('Month'), ' class="cronset ui-widget-content" ');
$html .= '<div class="cronsetdiv">';
$doy = array(
        '1' => dgettext('amp','January'),
        '2' => dgettext('amp','February'),
        '3' => dgettext('amp','March'),
        '4' => dgettext('amp','April'),
        '5' => dgettext('amp','May'),
        '6' => dgettext('amp','June'),
        '7' => dgettext('amp','July'),
        '8' => dgettext('amp','August'),
        '9' => dgettext('amp','September'),
        '10' => dgettext('amp','October'),
        '11' => dgettext('amp','November'),
        '12' => dgettext('amp','December'),
);

foreach ($doy as $k => $v) {
    $checked = in_array($k, $cron_month) ? "checked='checked' " : "";
    $label = $v;
	$html .= nice_checkbox($k, $label,$checked,'cron_month[]');
}

$html .= '</div>';
$html .= '</div>';
$html .= form_fieldset_close();

$html .= "
<script>
maxW=0;
\$('.cron_month').each(function () {
    var x = $(this).width();
    if (x > maxW) {
        maxW = x;
    }
});
\$('.cron_month').css('width', maxW + 'px');
</script>
";


//day of month
$html .= "<div class='column'>";
$html .= form_fieldset(_('Day of Month'), ' class="cronset ui-widget-content" ');
$html .= '<div class="cronsetdiv">';


for($i = 1; $i < 32; $i++) {
    in_array($i, $cron_dom) ? $data['checked'] = 'checked' : '';
    $checked = in_array($i, explode(",",$cron_dom[0])) ? "checked='checked' " : "";
    $label = sprintf("%02d",$i);
    $html .= nice_checkbox($i, $label,$checked,'cron_dom[]');
}

$html .= '</div>';
$html .= '</div>';
$html .= form_fieldset_close();
$html .= '</div>';
echo $html;

function nice_checkbox($value, $label, $checked, $name) {
    $class = preg_replace('/[\W]/',"",$name);
    $out="
<span class='control'>
    <label class='is-checkbox is-small is-info is-rounded'>
        <input name='$name' $checked type='checkbox' value='$value'>
        <span  class='icon is-small checkmark'>
            <i class='fa fa-check'></i>
        </span>
        <span class='$class'>$label</span>
    </label>
</span>";

    return $out;
}

