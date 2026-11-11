<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

// Empty/default means inherit Asterisk's username,ip behavior, not identify_by=ip.
function core_pjsip_identify_methods($value) {
    if (!is_string($value)) { return false; }
    $value = strtolower(trim($value));
    if ($value === '' || $value === 'default') { return array(); }
    $methods = array_map('trim', explode(',', $value));
    if (array_diff($methods, array('ip', 'username', 'auth_username', 'header', 'request_uri'))) {
        return false;
    }
    return array_values(array_unique($methods));
}

function core_pjsip_order_valid($value) {
    return is_string($value) && (trim($value) === '' ||
        preg_match('/^[a-z_]+(?:\s*,\s*[a-z_]+)*$/D', trim($value))) &&
        !array_diff(array_map('trim', explode(',', trim($value))),
            array('', 'ip', 'username', 'auth_username', 'anonymous', 'header', 'request_uri'));
}

function core_pjsip_global_config($order) {
    return core_pjsip_order_valid($order) && trim($order) !== ''
        ? 'endpoint_identifier_order='.implode(',', array_map('trim', explode(',', $order)))."\n" : '';
}

// Read only the identification order, following ordinary Asterisk includes. Never
// execute #exec directives. Substitute the pending managed value before Apply.
function core_pjsip_identifier_order($managed = null) {
    global $amp_conf;
    if ($managed === null) {
        $managed = sql("SELECT data FROM pjsipsettings WHERE keyword='endpoint_identifier_order' AND type=0", 'getOne');
    }
    $managed = is_string($managed) ? $managed : '';
    $directory = isset($amp_conf['ASTETCDIR']) ? $amp_conf['ASTETCDIR'] : '/etc/asterisk';
    $root = $directory.'/pjsip.conf';
    if (!is_readable($root)) { $root = dirname(__FILE__).'/etc/pjsip.conf'; }
    $order = 'ip,username,anonymous';
    $section = '';
    $stack = array();
    $read = function ($path) use (&$read, &$order, &$section, &$stack, $directory, $managed) {
        $real = realpath($path);
        if ($real === false || isset($stack[$real]) || count($stack) >= 32 || !is_readable($real)) { return; }
        $stack[$real] = true;
        foreach (file($real) as $line) {
            $line = trim(preg_replace('/;.*$/', '', $line));
            if (preg_match('/^#(?:try)?include\s+["<]?([^">]+)[">]?$/', $line, $m)) {
                $name = trim($m[1]);
                if (basename($name) === 'pjsip_global_additional.conf') {
                    if ($section === 'global' && core_pjsip_order_valid($managed) && trim($managed) !== '') {
                        $order = trim($managed);
                    }
                    continue;
                }
                $pattern = substr($name, 0, 1) === '/' ? $name : $directory.'/'.$name;
                foreach ((array) glob($pattern) as $include) { $read($include); }
            } elseif (preg_match('/^\[([^\]]+)\]/', $line, $m)) {
                $section = strtolower($m[1]);
            } elseif ($section === 'global' && preg_match('/^endpoint_identifier_order\s*=>?\s*(.*)$/i', $line, $m)) {
                $order = trim($m[1]);
            }
        }
        unset($stack[$real]);
    };
    $read($root);
    return array_map('trim', explode(',', $order));
}

function core_pjsip_auth_identifier_available() {
    global $astman;
    if (!$astman) { return false; }
    $response = $astman->Command('pjsip show identifiers');
    return is_array($response) && isset($response['data']) &&
        preg_match('/\bauth_username\b/', $response['data']);
}

function core_pjsip_running_identifier_order() {
    global $astman;
    if (!$astman) { return null; }
    $response = $astman->Command('pjsip show settings');
    if (is_array($response) && isset($response['data']) &&
        preg_match('/^\s*endpoint_identifier_order\s*[:=]?\s+([a-z_, ]+)\s*$/mi', $response['data'], $m)) {
        return trim($m[1]);
    }
    return null;
}

function core_pjsip_trunk_errors($fields, $order, $auth_available) {
    $errors = array();
    foreach ($fields as $key => $value) {
        if (!is_string($value) || preg_match('/[\r\n\x00]/', $value)) {
            $errors[] = __('PJSIP fields must contain a single text value.');
            return $errors;
        }
    }
    $methods = core_pjsip_identify_methods(isset($fields['identify_by']) ? $fields['identify_by'] : '');
    if ($methods === false) {
        $errors[] = __('Invalid PJSIP identification method.');
        return $errors;
    }
    foreach ($methods as $method) {
        if (!in_array($method, $order, true)) {
            $errors[] = sprintf(__('Enable %s in PJSIP Settings / Advanced General Settings / Endpoint Identifier Order before selecting it on this trunk.'), $method);
        }
    }
    if (in_array('auth_username', $methods, true)) {
        if (!$auth_available) {
            $errors[] = __('Cannot verify the auth_username identifier in Asterisk. Check the Asterisk connection and res_pjsip_endpoint_identifier_user module.');
        }
        if (!isset($fields['authentication']) || !in_array($fields['authentication'], array('inbound', 'both'), true) ||
            !isset($fields['secret']) || trim($fields['secret']) === '') {
            $errors[] = __('Authentication username requires Inbound or Both authentication and a secret. The remote authentication username must be the trunk name.');
        }
    }
    $port = isset($fields['port']) ? trim($fields['port']) : '';
    if ($port !== '' && (!ctype_digit($port) || (int)$port < 1 || (int)$port > 65535)) {
        $errors[] = __('SIP Server Port must be an integer between 1 and 65535.');
    }
    $registration = isset($fields['registration']) ? $fields['registration'] : 'none';
    if ($registration !== 'receive' && (!isset($fields['server']) || trim($fields['server']) === '')) {
        $errors[] = __('SIP Server is required unless Registration is Receive.');
    }
    return $errors;
}

function core_pjsip_destination($host, $port) {
    $host = trim($host);
    if ($host === '') { return ''; }
    // A port embedded in a legacy server value takes precedence.
    if (preg_match('/^\[[^\]]+\]:[0-9]+$/D', $host) ||
        (substr_count($host, ':') === 1 && preg_match('/:[0-9]+$/D', $host))) { return $host; }
    if (substr_count($host, ':') > 1 && substr($host, 0, 1) !== '[') { $host = '['.$host.']'; }
    $port = trim($port);
    if ($port !== '' && ctype_digit($port) && (int)$port >= 1 && (int)$port <= 65535) {
        $host .= ':'.(int)$port;
    }
    return $host;
}
