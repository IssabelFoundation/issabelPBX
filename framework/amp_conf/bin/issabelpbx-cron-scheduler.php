#!/usr/bin/php -q
<?php
//include bootstrap
$bootstrap_settings['issabelpbx_auth'] = false;
if (!@include_once(getenv('ISSABELPBX_CONF') ? getenv('ISSABELPBX_CONF') : '/etc/issabelpbx.conf')) {
    if(!is_readable('/etc/asterisk/issabelpbx.conf')) { die(); }
    include_once('/etc/asterisk/issabelpbx.conf');
}

// Set language
$lang = $issabelpbx_conf->get_conf_setting('LANGUAGE');
if($lang!='') {
    T_setlocale(LC_MESSAGES,  $lang);
    //putenv("LANGUAGE=".$lang);
    _bindtextdomain('amp',$amp_conf['AMPWEBROOT'].'/admin/i18n');
    _bind_textdomain_codeset('amp', 'utf8');
    _textdomain('amp');
}

// Define the notification class for logging to the dashboard
//
$nt = notifications::create($db);

// Check to see if email should be sent
//

$cm =& cronmanager::create($db);

$cm->run_jobs();

$email = $cm->get_email();
if ($email) {

    $text="";

    // clear email flag
    $nt->delete('issabelpbx', 'NOEMAIL');

    // set to false, if no updates are needed then it will not be
    // set to true and no email will go out even though the hash
    // may have changed.
    //
    $send_email = false;

    $security = $nt->list_security();
    if (count($security)) {
        $send_email = true;
        $text = "SECURITY NOTICE: ";
        foreach ($security as $item) {
            $text .= $item['display_text']."\n";
            $text .= $item['extended_text']."\n\n";
        }
    }
    $text .= "\n\n";

    $updates = $nt->list_update();
    if (count($updates)) {
        $send_email = true;
        $text = "UPDATE NOTICE: ";
        foreach ($updates as $item) {
            $text .= $item['display_text']."\n";
            $text .= $item['extended_text']."\n\n";
        }
    }

    if ($send_email && (! $cm->check_hash('update_email', $text))) {
        $cm->save_hash('update_email', $text);
        if (mail($email, __("IssabelPBX: New Online Updates Available"), $text)) {
            $nt->delete('issabelpbx', 'EMAILFAIL');
        } else {
            $nt->add_error('issabelpbx', 'EMAILFAIL', __('Failed to send online update email'), sprintf(__('An attempt to send email to: %s with online update status failed'),$email));
        }
    }
} else {
    $nt->add_notice('issabelpbx', 'NOEMAIL', __('No email address for online update checks'), __('You are automatically checking for online updates nightly but you have no email address setup to send the results. This can be set in Module Admin. They will continue to show up here.'), '', 'PASSIVE', false);
}
?>
