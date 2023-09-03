<?php

global $amp_conf;

$html = '<div class="content">';
$html .= heading(__('Log File Settings'), 2);
$html .= form_open($_SERVER['REQUEST_URI'], '', array('action' => 'save'));
//general settings
$table = new CI_Table;
$table->set_template(array('table_open' => '<table class="table is-narrow is-borderless" id="logfile_settings">'));

$table->add_row(array('colspan' => 2, 'data' => heading(_dgettext('amp','General Settings'), 5) ));
//date format
$data = array(
            'name'            => 'dateformat',
            //'id'            => 'username',
            'value'            => $dateformat,
            'placeholder'    => 'Date Format'
            );
$label = ipbx_label(__('Date Format'), __('Customize the display of debug message time stamps. '
                                    . 'See strftime(3) Linux manual for format specifiers. '
                                    . 'Note that there is also a fractional second parameter '
                                    . 'which may be used in this field.  Use %1q for tenths, '
                                    . '%2q for hundredths, etc.')
                                    . br() . __('Leave blank for default: ISO 8601 date format '
                                    . 'yyyy-mm-dd HH:MM:SS (%F %T)'));
$table->add_row($label, form_input($data,'',' class="input"'));

$help_li[] = __('Sequential: Rename archived logs in order, such that the newest has the highest sequence number.');
$help_li[] = __('Rotate: Rotate all the old files, such that the oldest has the highest sequence '
            . 'number (expected behavior for Unix administrators).');
$help_li[] = __('Timestamp: Rename the logfiles using a timestamp instead of a sequence number when "logger rotate" is executed.');
$label = ipbx_label(__('Log rotation'), __('Log rotation strategy: ') . ul($help_li));
$table->add_row($label, ipbx_radio('rotatestrategy',array(array('value'=>'sequential','text'=>__('Sequential')),array('value'=>'rotate','text'=>__('Rotate')),array('value'=>'timestamp','text'=>__('Timestamp'))),$rotatestrategy,false));

$label = ipbx_label(__('Append Hostname'), __('Appends the hostname to the name of the log files'));
$table->add_row($label, ipbx_radio('appendhostname',array(array('value'=>'yes','text'=>_dgettext('amp','Yes')),array('value'=>'no','text'=>_dgettext('amp','No'))),$appendhostname,false));

$label = ipbx_label(__('Log Queues'), __('Log queue events to a file'));
$table->add_row($label, ipbx_radio('queue_log',array(array('value'=>'yes','text'=>_dgettext('amp','Yes')),array('value'=>'no','text'=>_dgettext('amp','No'))),$queue_log,false));
$html .= $table->generate();


//log files

$table = new CI_Table;
$table->add_row(array('colspan' => 10, 'data' => heading(__('Log Files'), 5) ));
$table->set_template(array('table_open' => '<table class="table is-narrow notfixed" id="logfile_entries">'));

//draw table header with help on every option
$has_security_option = version_compare($amp_conf['ASTVERSION'],'11.0','ge');
$heading = array(
            ipbx_label(__('File Name'), __('Name of file. Use absolute path for a different location.')),
            ipbx_label(__('Debug'), 'debug: ' . __('Messages used for debuging. '
                                    . 'Do not report these as error\'s unless you have a '
                                    . 'specific issue that you are attempting to debug. '
                                    . 'Also note that Debug messages are also very verbose '
                                    . 'and can and do fill up logfiles (and disk storage) quickly.')),
            ipbx_label(__('DTMF'), 'dtmf: ' . __('Keypresses as understood by asterisk. Usefull for debuging IVR and VM issues.')),
            ipbx_label(__('Error'), 'error: ' . __('Critical errors and issues')),
            ipbx_label(__('Fax'), 'fax: ' . __('Transmition and receiving of faxes')),
            ipbx_label(__('Notice'), 'notice: ' . __('Messages of specific actions, such as a phone registration or call completion')),
            ipbx_label(__('Verbose'), 'verbose: ' . __('Step-by-step messages of every step of a call flow. '
                                        . 'Always enable and review if calls dont flow as expected')),
            ipbx_label(__('Warning'), 'warning: ' . __('Possible issues with dialplan syntaxt or call flow, but not critical.'))
        );

if ($has_security_option) { 
    $heading[] = ipbx_label(__('Security'), 'security: ' . __('Notification of security related events such as authentication attempts.')); 
}

$heading[] = ipbx_label(__('Delete'));
$table->add_row($heading);


//actual log files
$count = 0;

foreach ($logfiles as $l) {
    $row[] = form_input(
                array(
                    'name'            => 'logfiles[name][]',
                    'value'            => $l['name'],
                    'placeholder'    => __('file path/name'),
                    'class'         => 'input',
                    'required'        => ''
                )
            );
    
    $onoff = array(
            'on'    => __('On'),
            'off'    => __('Off')
    );

    $row[] = form_dropdown('logfiles[debug][]', $onoff, $l['debug'],' class="componentSelectAutoWidthNoSearch" ');
    $row[] = form_dropdown('logfiles[dtmf][]', $onoff, $l['dtmf'],' class="componentSelectAutoWidthNoSearch" ');
    $row[] = form_dropdown('logfiles[error][]', $onoff, $l['error'],' class="componentSelectAutoWidthNoSearch" ');
    $row[] = form_dropdown('logfiles[fax][]', $onoff, $l['fax'],' class="componentSelectAutoWidthNoSearch" ');
    $row[] = form_dropdown('logfiles[notice][]', $onoff, $l['notice'],' class="componentSelectAutoWidthNoSearch" ');
    $row[] = form_dropdown('logfiles[verbose][]', $onoff, $l['verbose'],' class="componentSelectAutoWidthNoSearch" ');
    $row[] = form_dropdown('logfiles[warning][]', $onoff, $l['warning'],' class="componentSelectAutoWidthNoSearch" ');

    if ($has_security_option) { 
        $row[] = form_dropdown('logfiles[security][]', $onoff, $l['security'],' class="componentSelectAutoWidthNoSearch" '); 
    }
    $row[] = "<button name='del$count' id='del$count' value='Delete' class='button is-small is-danger delete_entry' data-tooltip='".__('Delete')."'><span class='icon is-small'><i class='fa fa-trash'></i></span></button>";

    $table->add_row(array_values($row));
    unset($row);
    $count++;
}

$html .= $table->generate() ;

$html .= '<button type="button" class="button is-small is-rounded" id="add_entry">'.__('New Log File').'</button>';

$html .= form_action_bar('');

$html .= form_close();
$html .= '<script src="/admin/modules/logfiles/assets/js/views/settings.js"></script>';
$html .= '</div>';
echo $html;
?>
