#!/usr/bin/env php
<?php
$restrict_mods                          = array('queues' => true, 'core' => true);
$bootstrap_settings['cdrdb']            = true;
$bootstrap_settings['issabelpbx_auth']     = false;
if (!@include_once(getenv('ISSABELPBX_CONF') ? getenv('ISSABELPBX_CONF') : '/etc/issabelpbx.conf')) {
        include_once('/etc/asterisk/issabelpbx.conf');
}

//ensure the queues module is avalible before continuing
$mod_info = module_getinfo('queues', MODULE_STATUS_ENABLED);

if (!isset($mod_info['queues'])) {
        echo _('Queues module not found or is disabled. Aborting!' . PHP_EOL);
        exit(1);
}

/**
 * OPTIONS
 * id - if we have an id, it's the queue id, so go ahead and reset the queue stats
 */

$getopt = (function_exists('_getopt') ? '_' : '') . 'getopt';
$vars = $getopt($short = '', $long = array('opts::','id::'));

if (isset($vars['id']) && $vars['id'] && is_numeric($vars['id'])) {

	if ($amp_conf['AMPENGINE'] == 'asterisk' && isset($astman) && $astman->connected()) {

                $cmd = 'queue reset stats ' . $vars['id'];
                $response = $astman->send_request('Command', array('Command' => $cmd));

        }

} else {
	show_opts();
}

exit();

function show_opts() {
        $e[] = 'queue_reset_stats.php';
        $e[] = '';
        $e[] = 'options:';
        $e[] = "\t" . '--id=<id number> - a valid queue number';
        $e[] = '';
        $e[] = '';
        echo implode("\n", $e);
}
