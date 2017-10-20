<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

//for translation only
if (false) {
	_("Camp-On");
	_("Camp-On Request");
	_("Camp-On Cancel");
	_("Camp-On Toggle");
}

// Camp-On Request Feature Code
$fcc = new featurecode('campon', 'request');
$fcc->setDescription('Camp-On Request');
$fcc->setDefault('*82');
$fcc->update();
unset($fcc);

// Camp-On Cancel Feature Code
$fcc = new featurecode('campon', 'cancel');
$fcc->setDescription('Camp-On Cancel');
$fcc->setDefault('*83');
$fcc->update();
unset($fcc);

// TODO: do I make this dependant on a patch
//
// Camp-On Toggle Feature Code
$fcc = new featurecode('campon', 'toggle');
$fcc->setDescription('Camp-On Toggle');
$fcc->setDefault('*84');
$fcc->update();
unset($fcc);

$issabelpbx_conf =& issabelpbx_conf::create();

// CC_NON_EXTENSION_POLICY
//
$set['value'] = 'never';
$set['defaultval'] =& $set['value'];
$set['options'] = array('never', 'generic', 'always');
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 0;
$set['sortorder'] = 20;
$set['name'] = 'Non Extensions Callee Policy';
$set['description'] = "If this is set to 'generic' or 'always' then it will be possible to attempt call completion requests when dialing non-extensions such as ring groups and other possible destinations that could work with call completion. Setting this will bypass any Callee Policies and can result in inconsistent behavior. If set, 'generic' is the most common, 'always' will attempt to use technology specific capabilities if the called extension uses a channel that supports that.";
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('CC_NON_EXTENSION_POLICY',$set);

// CC_FORCE_DEFAULTS
//
$set['value'] = true;
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 0;
$set['sortorder'] = 30;
$set['name'] = 'Only Use Default Camp-On Settings';
$set['description'] = 'You can force all extensions on a system to only used the default Camp-On settings. When in this mode, the individual settings will not be shown on the extension page. If there were unique settings previously configured, the data will be retained but not used unless you switch this back to false. With this set, the Caller Policy (cc_agent_policy) and Callee Policy (cc_monitor_policy) settings will still be configurable for each user so you can still enable/disable Call Camping ability on select extensions.';
$set['type'] = CONF_TYPE_BOOL;
$issabelpbx_conf->define_conf_setting('CC_FORCE_DEFAULTS',$set);

// CC_ANNOUNCE_MONITOR_DEFAULT
//
$set['value'] = true;
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 0;
$set['sortorder'] = 140;
$set['name'] = 'Announce the Callee Extension';
$set['description'] = 'When set to true the target extension being called will be announced upone answering the Callback prior to ringing the extension. Setting this to false will go directly to ringing the extension, the CID information will still reflect who is being called back.';
$set['type'] = CONF_TYPE_BOOL;
$issabelpbx_conf->define_conf_setting('CC_ANNOUNCE_MONITOR_DEFAULT',$set);

// CC_AGENT_POLICY_DEFAULT
//
$set['value'] = 'generic';
$set['defaultval'] =& $set['value'];
$set['options'] = array('never', 'generic', 'native');
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 0;
$set['sortorder'] = 40;
$set['name'] = 'Caller Policy Default';
$set['description'] = "Asterisk: cc_agent_policy. Used to enable Camp-On for a user and set the Technology Mode that will be used when engaging the feature. In most cases 'generic' should be chosen unless you have phones designed to work with channel specific capabilities.";
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('CC_AGENT_POLICY_DEFAULT',$set);

// CC_MONITOR_POLICY_DEFAULT
//
$set['value'] = 'generic';
$set['defaultval'] =& $set['value'];
$set['options'] = array('never', 'generic', 'native', 'always');
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 0;
$set['sortorder'] = 50;
$set['name'] = 'Callee Policy Default';
$set['description'] = "Asterisk: cc_monitor_policy. Used to control if other phones are allowed to Camp On to an extension. If so, it sets the technology mode used to monitor the availability of the extension. If no specific technology support is available then it should be set to a 'generic'. In this mode, a callback will be initiated to the extension when it changes from an InUse state to NotInUse. If it was busy when first attempted, this will be when the current call has eneded. If it simply did not answer, then this will be the next time this phone is used to make or answer a call and then hangs up. It is possible to set this to take advantage of 'native' technology support if available and automatically fallback to 'generic' whe not by setting it to 'always'.";
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('CC_MONITOR_POLICY_DEFAULT',$set);

// CC_OFFER_TIMER_DEFAULT
//
$set['value'] = '30';
$set['defaultval'] =& $set['value'];
$set['options'] = array('20', '30', '45', '60', '120', '180', '240', '300', '600');
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 0;
$set['sortorder'] = 60;
$set['name'] = "Caller Timeout to Request Default";
$set['description'] = "Asterisk: cc_offer_timer. How many seconds after dialing an extenion a user has to make a call completion request.";
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('CC_OFFER_TIMER_DEFAULT',$set);

// CCBS_AVAILABLE_TIMER_DEFAULT
//
$set['value'] = '4800';
$set['defaultval'] =& $set['value'];
$set['options'] = array('1200', '2400', '3600', '4800', '6000', '7200', '10800', '14400', '18000', '21600', '25200', '28800', '32400');
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 0;
$set['sortorder'] = 70;
$set['name'] = "Max Camp-On Life Busy Default";
$set['description'] = "Asteirsk: ccbs_available_timer. How long a call completion request will remain active, in seconds, before expiring if the phone rang busy when first attempting the call.";
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('CCBS_AVAILABLE_TIMER_DEFAULT',$set);

// CCNR_AVAILABLE_TIMER_DEFAULT
//
$set['value'] = '7200';
$set['defaultval'] =& $set['value'];
$set['options'] = array('1200', '2400', '3600', '4800', '6000', '7200', '10800', '14400', '18000', '21600', '25200', '28800', '32400');
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 0;
$set['sortorder'] = 80;
$set['name'] = "Max Camp-On Life No Answer Default";
$set['description'] = "Asteirsk: ccnr_available_timer. How long a call completion request will remain active, in seconds, before expiring if the phone was simply unanswered when first attempting the call.";
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('CCNR_AVAILABLE_TIMER_DEFAULT',$set);

// CC_RECALL_TIMER_DEFAULT
//
unset($options);
for ($i=5;$i<=60;$i++) {
  $options[] = $i;
}
$set['value'] = '15';
$set['defaultval'] =& $set['value'];
$set['options'] = $options;
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 0;
$set['sortorder'] = 90;
$set['name'] = "Default Time to Ring Back Caller";
$set['description'] = "Asterisk: cc_recall_timer. How long in seconds to ring back a caller who's Caller Policy is set to 'Generic Device'. This has no affect if set to any other setting.";
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('CC_RECALL_TIMER_DEFAULT',$set);

// CC_MAX_AGENTS_DEFAULT
//
unset($options);
for ($i=1;$i<=20;$i++) {
  $options[] = $i;
}
$set['value'] = '5';
$set['defaultval'] =& $set['value'];
$set['options'] = $options;
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 0;
$set['sortorder'] = 110;
$set['name'] = "Default Max Camped-On Extensions";
$set['description'] = "Asterisk: cc_max_agents. Only valid for when using 'native' technology support for Caller Policy. This is the number of outstanding Call Completion requests that can be pending to different extensions. With 'generic' device mode you can only have a single request outstanding and this will be ignored.";
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('CC_MAX_AGENTS_DEFAULT',$set);

// CC_AGENT_DIALSTRING_DEFAULT
//
$set['value'] = 'extension';
$set['defaultval'] =& $set['value'];
$set['options'] = array('', 'extension', 'internal');
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 0;
$set['sortorder'] = 100;
$set['name'] = "Default Caller Callback Mode";
$set['description'] = "Affects Asterisk: cc_agent_dialstring. If not set a callback request will be dialed straight to the speciifc device that made the call. If using 'native' technology support this may be the peferred mode. The 'internal' (Callback Standard) option will intiate a call back to the caller just as if someone else on the system placed the call, which means the call can pursue Follow-Me. To avoid Follow-Me setting, choose 'extension' (Callback Extension).";
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('CC_AGENT_DIALSTRING_DEFAULT',$set);

// CC_MAX_MONITORS_DEFAULT
//
unset($options);
for ($i=1;$i<=20;$i++) {
  $options[] = $i;
}
$set['value'] = '5';
$set['defaultval'] =& $set['value'];
$set['options'] = $options;
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 0;
$set['sortorder'] = 170;
$set['name'] = "Default Max Queued Callers";
$set['description'] = "Asterisk: cc_max_monitors. This is the maximum number of callers that are allowed to queue up call completion requests against this extension.";
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('CC_MAX_MONITORS_DEFAULT',$set);

// CC_AGENT_ALERT_INFO_DEFAULT
//
$set['value'] = '';
$set['defaultval'] =& $set['value'];
$set['options'] = '';
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 1;
$set['sortorder'] = 120;
$set['name'] = "Default Callback Alert-Info";
$set['description'] = "An optional Alert-Info setting that can be used when initiating a callback. Only valid when 'Caller Policy' is set to 'generic' device'";
$set['type'] = CONF_TYPE_TEXT;
$issabelpbx_conf->define_conf_setting('CC_AGENT_ALERT_INFO_DEFAULT',$set);

// CC_AGENT_CID_PREPEND_DEFAULT
//
$set['value'] = '';
$set['defaultval'] =& $set['value'];
$set['options'] = '';
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 1;
$set['sortorder'] = 130;
$set['name'] = "Default Callback CID Prepend";
$set['description'] = "An optional CID Prepend setting that can be used when initiating a callback. Only valid when 'Caller Policy' is set to a 'generic' device'";
$set['type'] = CONF_TYPE_TEXT;
$issabelpbx_conf->define_conf_setting('CC_AGENT_CID_PREPEND_DEFAULT',$set);

// CC_MONITOR_ALERT_INFO_DEFAULT
//
$set['value'] = '';
$set['defaultval'] =& $set['value'];
$set['options'] = '';
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 1;
$set['sortorder'] = 150;
$set['name'] = "Default Callee Alert-Info";
$set['description'] = "An optional Alert-Info setting that can be used to send to the extension being called back.";
$set['type'] = CONF_TYPE_TEXT;
$issabelpbx_conf->define_conf_setting('CC_MONITOR_ALERT_INFO_DEFAULT',$set);

// CC_MONITOR_CID_PREPEND_DEFAULT
//
$set['value'] = '';
$set['defaultval'] =& $set['value'];
$set['options'] = '';
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 1;
$set['sortorder'] = 160;
$set['name'] = "Default Callee CID Prepend";
$set['description'] = "An optional CID Prepend setting that can be used to send to the extension being called back.'";
$set['type'] = CONF_TYPE_TEXT;
$issabelpbx_conf->define_conf_setting('CC_MONITOR_CID_PREPEND_DEFAULT',$set);

// CC_MAX_REQUESTS_GLOBAL
//
$set['value'] = '20';
$set['defaultval'] =& $set['value'];
$set['options'] = array(1,1000);
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 0;
$set['sortorder'] = 10;
$set['name'] = "Maximum Active Camp-On Requests";
$set['description'] = "System wide maximum number of outstanding Camp-On requests that can be active. This limit is useful on a system that may have memory constraints since the internal state machine takes up system resources relative to the number of active requests it has to track. Restart Asterisk for changes to take effect.";
$set['type'] = CONF_TYPE_INT;
$issabelpbx_conf->define_conf_setting('CC_MAX_REQUESTS_GLOBAL',$set);

$options = array('NOT_INUSE', 'INUSE', 'BUSY', 'UNAVAILABLE', 'RINGING', 'RINGINUSE', 'ONHOLD');

// CC_BLF_OFFERED
// Used for: cc_available, cc_offered, cc_caller_requested
//
$set['value'] = 'NOT_INUSE';
$set['defaultval'] =& $set['value'];
$set['options'] = $options;
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 0;
$set['sortorder'] = 180;
$set['name'] = "BLF Camp-On Available State";
$set['description'] = "This is the state that will be set for BLF subscriptions after attempting a call while it is still possible to Camp-On to the last called number, prior to the offer_timer expiring. Restart Asterisk for changes to take effect.";
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('CC_BLF_OFFERED',$set);

// CC_BLF_PENDING
// Used for: cc_active, cc_callee_ready
//
$set['value'] = 'INUSE';
$set['defaultval'] =& $set['value'];
$set['options'] = $options;
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 0;
$set['sortorder'] = 190;
$set['name'] = "BLF Camp-On Pending State";
$set['description'] = "This is the state that will be set for BLF subscriptions upon a successful Camp-On request, pending a callback when the party becomes available. Restart Asterisk for changes to take effect.";
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('CC_BLF_PENDING',$set);

// CC_BLF_CALLER_BUSY
// Used for: cc_caller_busy
//
$set['value'] = 'ONHOLD';
$set['defaultval'] =& $set['value'];
$set['options'] = $options;
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 0;
$set['sortorder'] = 200;
$set['name'] = "BLF Camp-On Busy Caller State";
$set['description'] = "This is the state that will be set for BLF subscriptions once the callee becomes available if the caller is not busy. Restart Asterisk for changes to take effect.";
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('CC_BLF_CALLER_BUSY',$set);

// CC_BLF_RECALL
// Used for: cc_recalling
//
$set['value'] = 'RINGING';
$set['defaultval'] =& $set['value'];
$set['options'] = $options;
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'campon';
$set['category'] = 'Camp-On Module';
$set['emptyok'] = 0;
$set['sortorder'] = 210;
$set['name'] = "BLF Camp-On Recalling State";
$set['description'] = "This is the state that will be set for BLF subscriptions once the callee becomes available if the caller is not busy. Restart Asterisk for changes to take effect.";
$set['type'] = CONF_TYPE_SELECT;
$issabelpbx_conf->define_conf_setting('CC_BLF_RECALL',$set);

$issabelpbx_conf->commit_conf_settings();
