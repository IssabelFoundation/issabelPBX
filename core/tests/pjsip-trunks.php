<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
// Standalone regression tests; no running PBX or database is required.
define('ISSABELPBX_IS_AUTH', true);
define('DB_FETCHMODE_ASSOC', 2);
function __($text) { return $text; }
class DB { static function IsError($value) { return false; } }
class TrunkTestDatabase {
    public $trunks = array();
    public $writes = 0;
    public $saved = array();
    function getAll($query, $mode) {
        return strpos($query, 'SELECT channelid,keyword,data') !== false ? $this->trunks : array();
    }
    function escapeSimple($value) { return addslashes($value); }
    function prepare($query) { return $query; }
    function executeMultiple($query, $rows) { $this->writes++; $this->saved = $rows; return true; }
}
$managed_order = '';
$identified_trunks = array();
function sql($query, $mode = null, $fetch = null) {
    global $managed_order, $identified_trunks;
    if (strpos($query, 'SELECT trunks.channelid') === 0) { return $identified_trunks; }
    if (strpos($query, 'endpoint_identifier_order') !== false) { return $managed_order; }
    return '';
}
$db = new TrunkTestDatabase();
$astman = false;
$amp_conf = array('AMPDBENGINE' => 'mysql');
require_once dirname(__DIR__).'/pjsip.functions.php';
// Used only to capture the pre-patch generator's golden fixture.
require_once getenv('PJSIP_TEST_CORE') ?: dirname(__DIR__).'/functions.inc.php';
$conf = new core_conf();
function render_trunks($trunks) {
    global $db, $conf;
    $db->trunks = array();
    foreach ($trunks as $id => $fields) {
        foreach ($fields as $keyword => $data) {
            $db->trunks[] = array('channelid' => $id, 'keyword' => $keyword, 'data' => $data);
        }
    }
    return $conf->generate_pjsip_additional('22.9.0');
}
function check($ok, $message) {
    global $checks;
    if (!$ok) { throw new Exception($message); }
    $checks++;
}
$checks = 0;
$legacy = array(
    'provider-ip' => array('registration'=>'none', 'authentication'=>'none', 'server'=>'192.0.2.1', 'username'=>'', 'secret'=>'', 'match'=>'192.0.2.0/24', 'context'=>'from-pstn', 'transport'=>'transport-udp', 'codecs'=>'alaw,ulaw'),
    'provider-register' => array('registration'=>'send', 'authentication'=>'outbound', 'server'=>'sip.example.test:5080', 'username'=>'account', 'secret'=>'test-secret', 'match'=>'', 'context'=>'from-pstn', 'transport'=>'transport-udp', 'retry_interval'=>'60', 'outbound_proxy'=>'proxy.example.test:5090'),
    'provider-inbound' => array('registration'=>'receive', 'authentication'=>'inbound', 'server'=>'192.0.2.3', 'username'=>'', 'secret'=>'test-secret', 'context'=>'from-pstn')
);
if (in_array('--snapshot', $argv, true)) { echo render_trunks($legacy); exit; }
check(render_trunks($legacy) === file_get_contents(__DIR__.'/fixtures/pjsip-legacy.conf'), 'Legacy trunk output changed');
$fields = $legacy['provider-ip'];
$fields['identify_by'] = 'default';
check(render_trunks(array('provider-ip'=>$fields)) === render_trunks(array('provider-ip'=>$legacy['provider-ip'])), 'default must inherit legacy behavior');
$fields['identify_by'] = '';
check(render_trunks(array('provider-ip'=>$fields)) === render_trunks(array('provider-ip'=>$legacy['provider-ip'])), 'empty must inherit legacy behavior');
$fields['identify_by'] = 'auth_username';
$fields['authentication'] = 'inbound';
$fields['secret'] = 'test-secret';
$output = render_trunks(array('wuzapi'=>$fields));
check(strpos($output, 'identify_by=auth_username') !== false && strpos($output, 'type=identify') === false, 'Auth-only must omit IP identify');
check(strpos($output, 'auth=wuzapi') !== false && strpos($output, 'username=wuzapi') !== false, 'Inbound identity must remain the trunk name');
$fields['identify_by'] = 'auth_username,ip';
check(strpos(render_trunks(array('wuzapi'=>$fields)), 'type=identify') !== false, 'Mixed methods must include IP identify');
$fields['identify_by'] = 'username';
check(strpos(render_trunks(array('wuzapi'=>$fields)), 'type=identify') === false, 'Username-only must omit IP identify');
$fields['identify_by'] = 'future_method';
$output = render_trunks(array('wuzapi'=>$fields));
check(strpos($output, 'identify_by=future_method') !== false && strpos($output, 'type=identify') === false, 'Unknown stored method must not silently fall back to IP');
$receive = $legacy['provider-inbound'];
$receive['server'] = '';
$receive['port'] = '5061';
$output = render_trunks(array('receive'=>$receive));
check(strpos($output, 'contact=') === false && strpos($output, 'match=') === false && strpos($output, 'server_uri=') === false, 'Receive without host must not create static destinations');
$receive['match'] = '192.0.2.0/24';
check(strpos(render_trunks(array('receive'=>$receive)), 'match=192.0.2.0/24') !== false, 'Explicit match must work without host');
unset($receive['server']);
check(strpos(render_trunks(array('receive'=>$receive)), 'match=192.0.2.0/24') !== false, 'Explicit match must work when server field is absent');
$send = $legacy['provider-register'];
$send['server'] = 'sip.example.test';
$send['port'] = '5061';
$output = render_trunks(array('send'=>$send));
check(strpos($output, 'server_uri=sip:sip.example.test:5061') !== false && strpos($output, 'client_uri=sip:account@sip.example.test:5061') !== false && strpos($output, 'contact=sip:account@sip.example.test:5061') !== false, 'Destination port must reach AOR and registration URIs');
check(strpos($output, 'match=sip.example.test' . "\n") !== false, 'Destination port must not become source port');
check(strpos($output, 'outbound_proxy=sip:proxy.example.test:5090') !== false, 'Proxy must retain its independent port');
check(core_pjsip_destination('host:5080', '5061') === 'host:5080', 'Legacy port must win');
check(core_pjsip_destination('2001:db8::1', '5061') === '[2001:db8::1]:5061', 'IPv6 must be bracketed');
check(core_pjsip_destination('[2001:db8::1]:5080', '5061') === '[2001:db8::1]:5080', 'Legacy IPv6 port must win');
check(core_pjsip_destination('', '5061') === '', 'Empty host must remain empty');
$valid = array('identify_by'=>'auth_username', 'authentication'=>'inbound', 'secret'=>'test-secret', 'registration'=>'receive');
check(core_pjsip_trunk_errors($valid, array('ip','username','auth_username','anonymous'), true) === array(), 'Valid inbound auth should pass');
check(count(core_pjsip_trunk_errors($valid, array('ip','username','anonymous'), true)) > 0, 'Missing global auth_username must fail');
check(count(core_pjsip_trunk_errors($valid, array('auth_username'), false)) > 0, 'Unavailable identifier must fail');
foreach (array(array('secret'=>''), array('authentication'=>'outbound'), array('port'=>'65536'), array('port'=>'5060oops'), array('port'=>'0'), array('identify_by'=>array('ip')), array('secret'=>"secret\ncontext=from-internal")) as $invalid) {
    check(count(core_pjsip_trunk_errors(array_merge($valid,$invalid), array('auth_username'), true)) > 0, 'Invalid trunk input accepted');
}
check(core_pjsip_global_config('') === '', 'Unset global order must emit nothing');
check(core_pjsip_global_config('ip, username,auth_username,anonymous') === "endpoint_identifier_order=ip,username,auth_username,anonymous\n", 'Managed order must be emitted');
check(!core_pjsip_order_valid(array('ip')) && !core_pjsip_order_valid("ip\nanonymous") && !core_pjsip_order_valid('ip,typo'), 'Invalid global orders accepted');
$temporary = sys_get_temp_dir().'/issabel-pjsip-test-'.getmypid();
mkdir($temporary);
$amp_conf['ASTETCDIR'] = $temporary;
try {
    // Use the shipped include hierarchy, with only custom files supplied locally.
    copy(dirname(__DIR__).'/etc/pjsip.conf', $temporary.'/pjsip.conf');
    check(core_pjsip_identifier_order('') === array('ip','username','anonymous'), 'Global default changed');
    check(core_pjsip_identifier_order('ip,username,auth_username,anonymous') === array('ip','username','auth_username','anonymous'), 'Pending managed order ignored');
    file_put_contents($temporary.'/pjsip_custom_post.conf', "[global](+)\nendpoint_identifier_order=auth_username,ip,anonymous\n");
    check(core_pjsip_identifier_order('ip,username,anonymous') === array('auth_username','ip','anonymous'), 'Custom post override must win');
    file_put_contents($temporary.'/pjsip_custom_post.conf', "#include \"nested.conf\"\n");
    file_put_contents($temporary.'/nested.conf', "endpoint_identifier_order=ip,auth_username,anonymous\n#include pjsip_custom_post.conf\n");
    check(core_pjsip_identifier_order('') === array('ip','auth_username','anonymous'), 'Custom includes and cycle guard failed');
    unlink($temporary.'/pjsip_custom_post.conf');
    require_once dirname(__DIR__, 2).'/sipsettings/functions.inc.php';
    $managed_order = 'ip,username,auth_username,anonymous';
    $settings = array('codecs'=>array('alaw'=>'1'), 'endpoint_identifier_order'=>'ip,username,anonymous');
    $identified_trunks = array(array('channelid'=>'wuzapi', 'data'=>'auth_username'));
    $errors = pjsipsettings_edit($settings);
    check(is_array($errors) && $db->writes === 0, 'Global edit must not disable an existing trunk method');
    $settings['endpoint_identifier_order'] = array('ip');
    check(is_array(pjsipsettings_edit($settings)) && $db->writes === 0, 'Array global input must not be saved');
    $settings['endpoint_identifier_order'] = 'ip,username,auth_username,anonymous';
    $managed_order = '';
    check(is_array(pjsipsettings_edit($settings)) && $db->writes === 0, 'Global auth method must be available before save');
    $astman = new class {
        function Command($command) { return array('data'=>"Identifier Names:\n ip\n username\n auth_username\n anonymous\n"); }
    };
    check(pjsipsettings_edit($settings) === true && $db->writes === 1, 'Valid global setting must be saved');
    check(in_array(array('endpoint_identifier_order','ip,username,auth_username,anonymous','0',SIP_NORMAL), $db->saved, true), 'Global order must persist as a normal PJSIP setting');
    $managed_order = 'ip,username,auth_username,anonymous';
    check(in_array('pjsip_global_additional.conf', $conf->get_filename(), true) &&
        $conf->generateConf('pjsip_global_additional.conf') === "endpoint_identifier_order=ip,username,auth_username,anonymous\n", 'Generated files must include the managed global order');
    $astman = new class {
        function Command($command) { return array('data'=>"Global Settings:\n endpoint_identifier_order : ip,username,auth_username,anonymous\n other_setting : no\n"); }
    };
    check(core_pjsip_running_identifier_order() === 'ip,username,auth_username,anonymous', 'Running order must come from settings, not available identifiers');
    $astman = false;
    check(core_pjsip_running_identifier_order() === null, 'Disconnected running order must be unknown');
    $amp_conf['AMPDBENGINE'] = 'sqlite3';
    $settings['endpoint_identifier_order'] = 'ip,username,anonymous';
    check(is_array(pjsipsettings_edit($settings)) && $db->writes === 1, 'SQLite path must also protect existing trunks');
    $identified_trunks = array(array('channelid'=>'legacy', 'data'=>null));
    $settings['endpoint_identifier_order'] = 'username,anonymous';
    check(is_array(pjsipsettings_edit($settings)) && $db->writes === 1, 'Global edits must preserve IP identification on default trunks');
    $identified_trunks = array(array('channelid'=>'pre-existing-issue', 'data'=>'auth_username'));
    $managed_order = '';
    $settings['endpoint_identifier_order'] = '';
    check(pjsipsettings_edit($settings) === true && $db->writes === 2, 'Unchanged global order must not block unrelated edits because of an existing issue');
} finally {
    foreach (glob($temporary.'/*') as $path) { unlink($path); }
    rmdir($temporary);
}
echo "Passed $checks checks\n";
