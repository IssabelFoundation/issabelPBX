<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

/*     Generates dialplan for "parking" components
    We call this with retrieve_conf
*/

/** parking_get_config
 * Short dialplan generation for this module
 * Long dialplan generation as well as population of conf_classes etc
 * that this module is responsible for.
 */

function parking_get_config($engine) {
    global $db;
    global $amp_conf;
    global $ext;  // is this the best way to pass this?
    global $asterisk_conf;
    global $core_conf;
    global $version;

    switch($engine) {
    case "asterisk":

        // Some contexts used throughout:
        //
        $por = 'park-orphan-routing';
        $ph  = 'park-hints';
        $pd  = 'park-dial';
        $lots = parking_get();

        foreach($lots as $lot) {

            parking_generate_parked_call();
            parking_generate_parkedcallstimeout();
            parking_generate_park_dial($pd, $por, $lot);

            $fcc = new featurecode('parking', 'parkedcall');
            $parkfetch_code = $fcc->getCodeActive();
            unset($fcc);

            // Need to setup featurecode.conf configuration for the parking lot:
            //
            $parkpos1    = $lot['parkpos'];
            $parkpos2    = $parkpos1 + $lot['numslots'] - 1;

            // A bit confusing, park_context is when we call to park which seems to want 'default' from various testing
            // hint_context is basically the actual context thus what we set in config file and what we point hints at
            //
            $park_context = 'default';

            $alphaname = preg_replace("/[^A-za-z0-9]/","",$lot['name']);

            if($lot['defaultlot']=='no') {
                $hint_context = 'parkedcalls_'.$alphaname;
            } else {
                $hint_context = 'parkedcalls';
            }

            $ast_ge_11 = version_compare($version,'11','gt');

            $lotname = 'parkinglot_'.$alphaname;

            if ($ast_ge_11) {

                if($lot['defaultlot']=='no') {

                    $core_conf->addParkingGeneralSection($lotname,'parkext', $lot['parkext']);
                    $core_conf->addParkingGeneralSection($lotname,'parkpos', $parkpos1."-".$parkpos2);
                    $core_conf->addParkingGeneralSection($lotname,'context', $hint_context);
                    $core_conf->addParkingGeneralSection($lotname,'parkext_exclusive', 'no');
                    $core_conf->addParkingGeneralSection($lotname,'parkingtime', $lot['parkingtime']);
                    $core_conf->addParkingGeneralSection($lotname,'comebacktoorigin', 'no'); //Set this to no as we can manage our own internal comebacktoorigin
                    $core_conf->addParkingGeneralSection($lotname,'parkedplay', $lot['parkedplay']);
                    $core_conf->addParkingGeneralSection($lotname,'courtesytone', 'beep');
                    $core_conf->addParkingGeneralSection($lotname,'parkedcalltransfers', $lot['parkedcalltransfers']);
                    $core_conf->addParkingGeneralSection($lotname,'parkedcallreparking', $lot['parkedcallreparking']);
                    $core_conf->addParkingGeneralSection($lotname,'parkedmusicclass', $lot['parkedmusicclass']);
                    $core_conf->addParkingGeneralSection($lotname,'findslot', $lot['findslot']);
     
                } else {
    
                    $core_conf->addParkingGeneral('parkext', $lot['parkext']);
                    $core_conf->addParkingGeneral('parkpos', $parkpos1."-".$parkpos2);
                    $core_conf->addParkingGeneral('context', $hint_context);
                    $core_conf->addParkingGeneral('parkext_exclusive', 'no');
                    $core_conf->addParkingGeneral('parkingtime', $lot['parkingtime']);
                    $core_conf->addParkingGeneral('comebacktoorigin', 'no'); //Set this to no as we can manage our own internal comebacktoorigin
                    $core_conf->addParkingGeneral('parkedplay', $lot['parkedplay']);
                    $core_conf->addParkingGeneral('courtesytone', 'beep');
                    $core_conf->addParkingGeneral('parkedcalltransfers', $lot['parkedcalltransfers']);
                    $core_conf->addParkingGeneral('parkedcallreparking', $lot['parkedcallreparking']);
                    $core_conf->addParkingGeneral('parkedmusicclass', $lot['parkedmusicclass']);
                    $core_conf->addParkingGeneral('findslot', $lot['findslot']);
                }
            } else {
    
                if($lot['defaultlot']=='no') {
    
                    $core_conf->addFeatureGeneralSection($lotname,'parkext', $lot['parkext']);
                    $core_conf->addFeatureGeneralSection($lotname,'parkpos', $parkpos1."-".$parkpos2);
                    $core_conf->addFeatureGeneralSection($lotname,'context', $hint_context);
                    $core_conf->addFeatureGeneralSection($lotname,'parkext_exclusive', 'no');
                    $core_conf->addFeatureGeneralSection($lotname,'parkingtime', $lot['parkingtime']);
                    $core_conf->addFeatureGeneralSection($lotname,'comebacktoorigin', 'no'); //Set this to no as we can manage our own internal comebacktoorigin
                    $core_conf->addFeatureGeneralSection($lotname,'parkedplay', $lot['parkedplay']);
                    $core_conf->addFeatureGeneralSection($lotname,'courtesytone', 'beep');
                    $core_conf->addFeatureGeneralSection($lotname,'parkedcalltransfers', $lot['parkedcalltransfers']);
                    $core_conf->addFeatureGeneralSection($lotname,'parkedcallreparking', $lot['parkedcallreparking']);
                    $core_conf->addFeatureGeneralSection($lotname,'parkedmusicclass', $lot['parkedmusicclass']);
                    $core_conf->addFeatureGeneralSection($lotname,'findslot', $lot['findslot']);
     
                } else {
    
                    $core_conf->addFeatureGeneral('parkext', $lot['parkext']);
                    $core_conf->addFeatureGeneral('parkpos', $parkpos1."-".$parkpos2);
                    $core_conf->addFeatureGeneral('context', $hint_context);
                    $core_conf->addFeatureGeneral('parkext_exclusive', 'no');
                    $core_conf->addFeatureGeneral('parkingtime', $lot['parkingtime']);
                    $core_conf->addFeatureGeneral('comebacktoorigin', 'no'); //Set this to no as we can manage our own internal comebacktoorigin
                    $core_conf->addFeatureGeneral('parkedplay', $lot['parkedplay']);
                    $core_conf->addFeatureGeneral('courtesytone', 'beep');
                    $core_conf->addFeatureGeneral('parkedcalltransfers', $lot['parkedcalltransfers']);
                    $core_conf->addFeatureGeneral('parkedcallreparking', $lot['parkedcallreparking']);
                    $core_conf->addFeatureGeneral('parkedmusicclass', $lot['parkedmusicclass']);
                    $core_conf->addFeatureGeneral('findslot', $lot['findslot']);
                }
            }
    
            $ext->addInclude('from-internal-additional', $ph);
            $ext->addInclude($ph, $hint_context, $lot['name']);

            // Each lot needs a routing table to handle orphaned calls in the event
            // that the call were to timeout if they were routed to return to
            // originator, we route them to the ${PLOT} previously set
    
            if ($lot['comebacktoorigin'] == 'yes') {

                // If they haven't provided a destination then we need to make a context to
                // handle orphaned calls, we'll require destinations but this is a stop gap
                // to be nice to cusotmers and broken systems.
                //
                if (!$lot['dest']) {
                    $ext->add($por, $lot['parkext'], '', new ext_noop('ERROR: No Alternate Destination Available for Orphaned Call'));
                    $ext->add($por, $lot['parkext'], '', new ext_playback('sorry&an-error-has-occured'));
                    $ext->add($por, $lot['parkext'], '', new ext_hangup(''));
                } else {
                    $ext->add($por, $lot['parkext'], '', new ext_goto($lot['dest']));
                }
            }

            // Setup the specific items to do in the park-return-routing context for each lot, we will deal
            // with the per slot routing to this extension in the per slot loop below
            //
            parking_generate_sub_return_routing($lot, $pd, $parkpos1, $parkpos2);

            // Now we have to create the hints and the specific parking slots for picking up the calls since 
            // we do not use the dynamic generated ParkedCall() 
            // 
            $hv_all = '';
            for ($slot = $parkpos1; $slot <= $parkpos2; $slot++) {
    
                $ext->add($ph, $slot, '', new ext_macro('parked-call',$slot . ',' . ($lot['type'] == 'public' ? $park_context : '${CHANNEL(parkinglot)}')));
    
                if ($lot['generatehints'] == 'yes') {
                    $hv = "park:$slot@$hint_context";
                    $hv_all .= $hv.'&';
                    $ext->addHint($ph, $slot, $hv);
                }
            }
            $hv_all = rtrim($hv_all,'&');
            if ($parkfetch_code != '') {
                $ext->add($ph, $parkfetch_code, '', new ext_macro('parked-call', ',' . $park_context));
                $ext->add($ph, $parkfetch_code.$lot['parkext'], '', new ext_macro('parked-call', ',' . $park_context));
                if ($lot['generatehints'] == 'yes') {
                    $ext->addHint($ph, $parkfetch_code, $hv_all);
                    $ext->addHint($ph, $parkfetch_code.$lot['parkext'], $hv_all);
                }
    
                if ($amp_conf['USEDEVSTATE']) {
                    $device_list = core_devices_list("all", 'full', true);
                    foreach ($device_list as $device) {
                        if ($device['tech'] == 'sip' || $device['tech'] == 'iax2') {
                            $ext->add($ph, $parkfetch_code.$device['id'], '', new ext_macro('parked-call', ',' . $park_context));
                            $ext->addHint($ph, $parkfetch_code.$device['id'], "Custom:PARK".$device['id']);
                        }
                    }
                }
            }
    
            if ($lot['autocidpp'] == 'exten' || $lot['autocidpp'] == 'name') {
                parking_generate_sub_park_user($lot);
            }
        }
    }
}

function parking_generate_sub_park_user() {
    global $db;
    global $amp_conf;
    global $ext;  // is this the best way to pass this?
    global $asterisk_conf;
    global $version;

    $ast_ge_10 = version_compare($version,'10','ge');

    $spu = 'sub-park-user';
    $exten = 's';

     if ($ast_ge_10) {
        $ext->add($spu, $exten, '', new ext_set('UEXTEN', 'UNKNOWN'));
        $ext->add($spu, $exten, '', new ext_set('UNAME', 'UNKNOWN'));
        $ext->add($spu, $exten, '', new ext_set('DEVS', '${DB_KEYS(DEVICE)}'));
        $ext->add($spu, $exten, '', new ext_while('$["${SET(DEV=${POP(DEVS)})}" != ""]'));
        $ext->add($spu, $exten, '', new ext_gotoif('$["${DB(DEVICE/${DEV}/dial)}" = "${CUT(CHANNEL(name),-,1)}"]','found'));
        $ext->add($spu, $exten, '', new ext_endwhile(''));
        $ext->add($spu, $exten, '', new ext_return(''));
        $ext->add($spu, $exten, 'found', new ext_execif('$[${LEN(${DB(DEVICE/${DEV}/user)})} > 0]','Set','UEXTEN=${DB(DEVICE/${DEV}/user)}'));
        $ext->add($spu, $exten, '', new ext_execif('$[${LEN(${UEXTEN})} > 0]','Set','UNAME=${DB(AMPUSER/${UEXTEN}/cidname)}'));
        $ext->add($spu, $exten, '', new ext_return(''));
    } else {
        $ext->add($spu, $exten, '', new ext_agi('parkuser.php'));
        $ext->add($spu, $exten, '', new ext_return(''));
    }
}

function parking_generate_sub_return_routing($lot, $pd) {
    global $ext;

    $parkpos1    = $lot['parkpos'];
    $parkpos2    = $parkpos1 + $lot['numslots'] - 1;

    $prr = 'park-return-routing';
    $pexten = $lot['parkext'];

    $ext->add($prr, $pexten, '', new ext_set('PLOT',$pexten));
    if ($lot['alertinfo']) {
        $ext->add($prr, $pexten, '', new ext_sipremoveheader('Alert-Info:'));
        $ext->add($prr, $pexten, '', new ext_sipaddheader('Alert-Info',$lot['alertinfo']));
    }

    // Prepend options are parkingslot they were parked on, or the extension number or user name of the user who parked them
    //
    switch ($lot['autocidpp']) {
    case 'slot':
        $autopp = '${PARKINGSLOT}:';
        break;
    case 'exten':
        $ext->add($prr, $pexten, '', new ext_gosub('1','s','sub-park-user'));
        $autopp = '${UEXTEN}:';
        break;
    case 'name':
        $ext->add($prr, $pexten, '', new ext_gosub('1','s','sub-park-user'));
        $autopp = '${UNAME}:';
        break;
    default:
        $autopp = '';
        break;
    }
    if ($lot['cidpp'] || $autopp != '') {
        $cidpp = $lot['cidpp'] . $autopp;
        $ext->add($prr, $pexten, '', new ext_execif('$[${LEN(${PREPARK_CID})} = 0]','Set','PREPARK_CID=${CALLERID(name)}'));
        $ext->add($prr, $pexten, '', new ext_set('CALLERID(name)',$cidpp . '${PREPARK_CID}'));
    }
    if ($lot['announcement_id']) {
        $parkingannmsg = recordings_get_file($lot['announcement_id']);
        $ext->add($prr, $pexten, '', new ext_playback($parkingannmsg));
    }

    // If comeback to origin is set then send the call back to the parking target
    // This is our workaround so that we can send Alert-Info and Prepend on a comeback to origin request
    // The default method in Asterisk will not let us send or setup alert-info or prepend anything
    if ($lot['comebacktoorigin'] == 'yes') {
        $ext->add($prr, $pexten, '', new ext_goto($pd . ',${PARK_TARGET},1'));
    }
    
    // If comback to origin wasn't set or if we have already tried that.
    if (!$lot['dest']) {
        $ext->add($prr, $pexten, '', new ext_noop('ERROR: No Alternate Destination Available for Orphaned Call'));
        $ext->add($prr, $pexten, '', new ext_playback('sorry&an-error-has-occured'));
        $ext->add($prr, $pexten, '', new ext_hangup(''));
    } else {
        $ext->add($prr, $pexten, '', new ext_goto($lot['dest'] ? $lot['dest'] : $pd . ',${PARK_TARGET},1'));
    }

    // Route park-return-routing from slot to PARK_TARGET:
    for ($slot = $parkpos1; $slot <= $parkpos2; $slot++) {
        $ext->add($prr, $slot, '', new ext_goto('1', $pexten));
    }
}

function parking_generate_parked_call() {
    global $ext;
    global $version;

    // macro-parked-call
    // pickup a parked call from a specified slot
    //
    // NOTE: consider changing this to a subroutine
    //
    $pc = 'macro-parked-call';
    $exten = 's';
    $ast_ge_13 = version_compare($version,'13','gt');

    //
    // Determine from parked channel if we were previously recording and if so keep doing so
    //
    if ($ast_ge_13) {
        $ext->add($pc, $exten, '', new ext_agi('parkfetch.agi,${ARG1},${ARG2}'));
    } else {
        $ext->add($pc, $exten, '', new ext_agi('parkfetch.agi,${ARG1}'));
    }
    $ext->add($pc, $exten, '', new ext_gotoif('$["${REC_STATUS}" != "RECORDING"]','next'));
    $ext->add($pc, $exten, '', new ext_set('AUDIOHOOK_INHERIT(MixMonitor)','yes'));
    $ext->add($pc, $exten, '', new ext_set('CDR(recordingfile)','${CALLFILENAME}.${MON_FMT}'));
    $ext->add($pc, $exten, '', new ext_mixmonitor('${MIXMON_DIR}${YEAR}/${MONTH}/${DAY}/${CALLFILENAME}.${MIXMON_FORMAT}','a','${MIXMON_POST}'));
    $ext->add($pc, $exten, 'next', new ext_set('CCSS_SETUP','TRUE'));
    $ext->add($pc, $exten, '', new ext_macro('user-callerid'));
    $ext->add($pc, $exten, '', new ext_gotoif('$["${ARG1}" = "" | ${DIALPLAN_EXISTS(${IF($["${ARG2}" = "default"]?parkedcalls:${ARG2})},${ARG1},1)} = 1]','pcall')); //fails here when ${ARG2} defined in ext_parkedcall
    $ext->add($pc, $exten, '', new ext_resetcdr(''));
    $ext->add($pc, $exten, '', new ext_nocdr(''));
    $ext->add($pc, $exten, '', new ext_wait('1'));
    $ext->add($pc, $exten, '', new ext_noop_trace('User: ${CALLERID(all)} tried to pickup non-existent Parked Call Slot ${ARG1}'));
    $ext->add($pc, $exten, '', new ext_playback('pbx-invalidpark'));
    $ext->add($pc, $exten, '', new ext_wait('1'));
    $ext->add($pc, $exten, '', new ext_hangup(''));
    $ext->add($pc, $exten, 'pcall', new ext_noop('User: ${CALLERID(all)} attempting to pick up Parked Call Slot ${ARG1}'));

    // ParkedCalls can't handle picking up the default lot as 'parkedcalls' context, it wants 'default'
    //
    if ($ast_ge_13) {
        $ext->add($pc, $exten, '', new ext_parkedcall('${ARG2},${ARG1}'));
    } else {
        $ext->add($pc, $exten, '', new ext_parkedcall('${ARG1},${ARG2}'));
    }
    $ext->add($pc, 'h', '', new ext_macro('hangupcall'));
}

function parking_generate_parkedcallstimeout() {
    global $ext;

    // parkedcallstimeout:
    // All timedout parked calls come here regardless of the lot, we thus use this context to route the call to their properly
    // configured destination or back to the originator through a routing table based on the slot that returned the call
    //
    $pc = 'parkedcallstimeout';
    $exten = '_[0-9a-zA-Z*#].';

    $ext->add($pc, $exten, '', new ext_noop_trace('Slot: ${PARKINGSLOT} returned directed at ${EXTEN}'));
    $ext->add($pc, $exten, '', new ext_set('PARK_TARGET','${EXTEN}'));
    $ext->add($pc, $exten, '', new ext_gotoif('$["${REC_STATUS}" != "RECORDING"]','next'));
    $ext->add($pc, $exten, '', new ext_set('AUDIOHOOK_INHERIT(MixMonitor)','yes'));
    $ext->add($pc, $exten, '', new ext_mixmonitor('${MIXMON_DIR}${YEAR}/${MONTH}/${DAY}/${CALLFILENAME}.${MIXMON_FORMAT}','a','${MIXMON_POST}'));
    $ext->add($pc, $exten, 'next', new ext_goto('1','${PARKINGSLOT}','park-return-routing'));
}

function parking_generate_park_dial($pd, $por, $lot) {
    global $ext;
    // park-dial
    // This is a special context where calls are routed if they are being sent back to the parker. The parking application dynamically
    // inserts extensions into this context in the form of TECH_DEVICEID but if a call were to fail either from a timeout or otherwise
    // then it will move on to priority 2 ... so we need to catch that and then route the call to the park-orphan-routing context to
    // determine where their final destinaition lies.
    //
    foreach (array('t', '_[0-9a-zA-Z*#].') as $exten) {
        //$ext->add($pd, $exten, '', new ext_goto('1', '${PLOT}', $por));
        $ext->add($pd, $exten, '', new ext_noop('WARNING: PARKRETURN to: [${EXTEN}] failed with: [${DIALSTATUS}]. Trying Alternate Dest On Parking Lot ${PARKINGSLOT}'));
        //$ext->add($pd, $exten, '', new ext_goto('1', '${PLOT}', $por));
        $ext->add($pd, $exten, '', new ext_goto('1', $lot['parkext'], $por));
    }
}
