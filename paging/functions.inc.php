<?php 
/* $Id $ */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//    License for all code of this IssabelPBX module can be found in the license file inside the module directory
//    Copyright 2013 Schmooze Com Inc.
//

/* paging_init - Is run every time the page is loaded, checks
   to make sure that the database is current and loaded, if not,
   it propogates it. I expect that extra code will go here to 
   check for version upgrades, etc, of the paging database, to
   allow for easy upgrades. */

//    Generates dialplan for paging  - is called from retrieve_conf

function paging_get_config($engine) {
    global $db, $ext, $chan_dahdi, $version, $amp_conf, $conferences_conf;
    switch($engine) {
        case "asterisk":
            $ast_ge_14 = version_compare($version, "1.4", "ge");
            $ast_ge_10 = version_compare($version, '10', 'ge');

            // setup for intercom
            $fcc = new featurecode('paging', 'intercom-prefix');
            $intercom_code = $fcc->getCodeActive();
            unset($fcc);

            // Since these are going down channel local, set ALERT_INFO and SIPADDHEADER which will be set in dialparties.agi
            // no point in even setting the headers here they will get lost in channel local
            //

            /* Set these up once here and in intercom so that autoanswer macro does not have
             * to go through this for every single extension which causes a lot of extra overhead
             * with big page groups
             */

            $has_answermacro = false;

            $alertinfo = 'Alert-Info: Ring Answer';
            $callinfo  = 'Call-Info: <uri>\;answer-after=0';
            $sipuri    = 'intercom=true';
            $doptions = 'A(beep)';
            $vxml_url = '';
            $dtime = '5';
            $custom_vars = array();
            $autoanswer_arr = paging_get_autoanswer_defaults();

            foreach ($autoanswer_arr as $autosetting) {
                switch (trim($autosetting['var'])) {
                    case 'ALERTINFO':
                        $alertinfo = trim($autosetting['setting']);
                        break;
                    case 'CALLINFO':
                        $callinfo = trim($autosetting['setting']);
                        break;
                    case 'SIPURI':
                        $sipuri = trim($autosetting['setting']);
                        break;
                    case 'VXML_URL':
                        $vxml_url = trim($autosetting['setting']);
                        break;
                    case 'DOPTIONS':
                        $doptions = trim($autosetting['setting']);
                        break;
                    case 'DTIME':
                        $dtime = trim($autosetting['setting']);
                        break;
                    default:
                        $key = trim($autosetting['var']);
                        $custom_vars[$key] = trim($autosetting['setting']);
                        if (ltrim($custom_vars[$key],'_') == "ANSWERMACRO") {
                            $has_answermacro = true;
                        }
                        break;
                }
            }

            $apppaging = 'app-paging';
            if (!empty($intercom_code)) {
                $code = '_'.$intercom_code.'.';
                $context = 'ext-intercom';
                $ext->add($context, $code, '', new ext_macro('user-callerid'));
                $ext->add($context, $code, '', new ext_setvar('dialnumber', '${EXTEN:'.strlen($intercom_code).'}'));
                $ext->add($context, $code, '', new ext_setvar('INTERCOM_CALL', 'TRUE'));
                $ext->add($context, $code, '', new ext_gosub('1','s','sub-record-check','exten,${dialnumber}'));
                $ext->add($context, $code, '', new ext_gotoif('$["${DB(AMPUSER/${AMPUSER}/intercom/block)}" = "blocked"]', 'end'));
                $ext->add($context, $code, '', new ext_gotoif('$["${DB(DND/${dialnumber})}" = "YES"]', 'end'));
                $ext->add($context, $code, '', new ext_gotoif('$["${DB(AMPUSER/${dialnumber}/intercom/${AMPUSER})}" = "allow" ]', 'allow'));
                $ext->add($context, $code, '', new ext_gotoif('$["${DB(AMPUSER/${dialnumber}/intercom/${AMPUSER})}" = "deny" ]', 'nointercom'));
                $ext->add($context, $code, '', new ext_gotoif('$["${DB(AMPUSER/${dialnumber}/intercom)}" = "disabled" ]', 'nointercom'));
                $ext->add($context, $code, 'allow', new ext_dbget('DEVICES','AMPUSER/${dialnumber}/device'));
                $ext->add($context, $code, '', new ext_gotoif('$["${DEVICES}" = "" ]', 'end'));
                $ext->add($context, $code, '', new ext_setvar('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));

                /* Set these up so that macro-autoanswer doesn't have to
                 */
                $ext->add($context, $code, '', new ext_setvar('_SIPURI', ''));
                if (trim($alertinfo) != "") {
                    $ext->add($context, $code, '', new ext_setvar('_ALERTINFO', $alertinfo));
                }
                if (trim($callinfo) != "") {
                    $ext->add($context, $code, '', new ext_setvar('_CALLINFO', $callinfo));
                }
                if (trim($sipuri) != "") {
                    $ext->add($context, $code, '', new ext_setvar('_SIPURI', $sipuri));
                }
                if (trim($vxml_url) != "") {
                    $ext->add($context, $code, '', new ext_setvar('_VXML_URL', $vxml_url));
                }
                if (trim($doptions) != "") {
                    $ext->add($context, $code, '', new ext_setvar('_DOPTIONS', $doptions));
                }
                foreach ($custom_vars as $key => $value) {
                    $ext->add($context, $code, '', new ext_setvar('_'.ltrim($key,'_'), $value));
                }
                $ext->add($context, $code, '', new ext_setvar('_DTIME', $dtime));
                $ext->add($context, $code, '', new ext_setvar('_ANSWERMACRO', ''));

                $ext->add($context, $code, '', new ext_gotoif('$[${LOOPCNT} > 1 ]', 'pagemode'));
                $ext->add($context, $code, '', new ext_macro('autoanswer','${DEVICES}'));

        if ($ast_ge_14) {
                  $ext->add($context, $code, 'check', new ext_chanisavail('${DIAL}', 's'));
                $ext->add($context, $code, '', new ext_gotoif('$["${AVAILORIGCHAN}" = ""]', 'end'));            
        } else {
                  $ext->add($context, $code, 'check', new ext_chanisavail('${DIAL}', 'sj'));
        }
                $ext->add($context, $code, '', new ext_noop_trace('AVAILCHAN: ${AVAILCHAN}, AVAILORIGCHAN: ${AVAILORIGCHAN}, AVAILSTATUS: ${AVAILSTATUS}',5));
                $dopt = '';
                if ($amp_conf['AST_FUNC_CONNECTEDLINE']) {
                    $len = strlen($code)-2;
                    $dopt = 'I';
                    $ext->add($context, $code, '', new ext_gotoif('$["${DB(AMPUSER/${EXTEN:' . $len . '}/cidname)}" = ""]','godial'));
                    $ext->add($context, $code, '', new ext_set('CONNECTEDLINE(name,i)', '${DB(AMPUSER/${EXTEN:' . $len . '}/cidname)}'));
                    $ext->add($context, $code, '', new ext_set('CONNECTEDLINE(num)', '${EXTEN:' . $len . '}'));
                }
                $ext->add($context, $code, 'godial', new ext_dial('${DIAL}','${DTIME},' . $dopt . '${DOPTIONS}${INTERCOM_EXT_DOPTIONS}'));


        $ext->add($context, $code, 'end', new ext_execif('$[${INTERCOM_RETURN}]', 'Return'));
                $ext->add($context, $code, '', new ext_busy());
                $ext->add($context, $code, '', new ext_macro('hangupcall'));
        if (!$ast_ge_14) {
                  $ext->add($context, $code, '', new ext_execif('$[${INTERCOM_RETURN}]', 'Return'),'check',101);
                $ext->add($context, $code, '', new ext_busy());
                $ext->add($context, $code, '', new ext_macro('hangupcall'));
        }

                $ext->add($context, $code, 'pagemode', new ext_setvar('ITER', '1'));
                $ext->add($context, $code, '', new ext_setvar('DIALSTR', ''));
                $ds = $amp_conf['ASTCONFAPP'] == 'app_confbridge' ? '${DIALSTR}-${CUT(DEVICES,&,${ITER})}' 
                    : '${DIALSTR}&LOCAL/PAGE${CUT(DEVICES,&,${ITER})}@'.$apppaging;
                $ext->add($context, $code, 'begin', new ext_chanisavail('${DB(DEVICE/${CUT(DEVICES,&,${ITER})}/dial)}','s'));
                $ext->add($context, $code, '', new ext_gotoif('$["${AVAILORIGCHAN}" = ""]', 'skip'));
                $ext->add($context, $code, '', new ext_setvar('DIALSTR', $ds));
                $ext->add($context, $code, 'skip', new ext_setvar('ITER', '$[${ITER} + 1]'));
                $ext->add($context, $code, '', new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin'));
                $ext->add($context, $code, '', new ext_setvar('DIALSTR', '${DIALSTR:1}'));
                $ext->add($context, $code, '', new ext_gotoif('$["${DIALSTR}" = ""]', 'end2'));
                $ext->add($context, $code, '', new ext_setvar('_AMPUSER', '${AMPUSER}'));
                if ($amp_conf['ASTCONFAPP'] == 'app_confbridge') {
                    $ext->add($context, $code, '', new ext_gosub('1', 'page', false, '${DIALSTR}'));
                } else {
                    $ext->add($context, $code, '', new ext_page('${DIALSTR},d'));
                }
           $ext->add($context, $code, 'end2', new ext_execif('$[${INTERCOM_RETURN}]', 'Return'));
                $ext->add($context, $code, '', new ext_busy());
                $ext->add($context, $code, '', new ext_macro('hangupcall'));

                $ext->add($context, $code, 'nointercom', new ext_noop('Intercom disallowed by ${dialnumber}'));
                $ext->add($context, $code, '', new ext_execif('$[${INTERCOM_RETURN}]', 'Return'));
                $ext->add($context, $code, '', new ext_playback('intercom&for&extension'));
                $ext->add($context, $code, '', new ext_saydigits('${dialnumber}'));
                $ext->add($context, $code, '', new ext_playback('is&disabled'));
                $ext->add($context, $code, '', new ext_congestion());

                if ($amp_conf['ASTCONFAPP'] == 'app_confbridge') {
                    $sub = 'page';
                    $ext->add($context, $sub, '', new ext_set('PAGE_CONF', '${EPOCH}${RAND(100,999)}'));
                    $ext->add($context, $sub, '', new ext_set('PAGEMODE', 'PAGE'));
                    $ext->add($context, $sub, '', new ext_set('PAGE_MEMBERS', '${ARG1}'));
                    $ext->add($context, $sub, '', new ext_set('PAGE_CONF_OPTS', 'duplex'));
                    $ext->add($context, $sub, '', new ext_agi('page.agi'));            
                    if ($ast_ge_10) { 
                        $ext->add($context, $sub, '', new ext_set('CONFBRIDGE(user,template)', 'page_user_duplex'));
                        $ext->add($context, $sub, '', new ext_set('CONFBRIDGE(user,admin)', 'yes'));
                        $ext->add($context, $sub, '', new ext_set('CONFBRIDGE(user,marked)', 'yes'));
                        $ext->add($context, $sub, '', new ext_meetme('${PAGE_CONF}',',','admin_menu'));
                    } else {
                        $ext->add($context, $sub, '', new ext_meetme('${PAGE_CONF}', 'doqwxAG'));
                    }
                    $ext->add($context, $sub, '', new ext_hangup());
                }

                $extintercomusers = 'ext-intercom-users';
                $userlist = core_users_list();
                if (is_array($userlist)) {
                    foreach($userlist as $item) {
                        $ext_intercom_code = $intercom_code.$item[0];
                        $ext->add($extintercomusers, $ext_intercom_code, '', new ext_goto($context.',${EXTEN},1'));
                    }
                }

                $context = $extintercomusers;
                $ext->addInclude('from-internal-additional', $context);
            }
            
            $fcc = new featurecode('paging', 'intercom-on');
            $oncode = $fcc->getCodeActive();
            unset($fcc);

            if ($oncode) {
                $ext->add($context, $oncode, '', new ext_answer(''));
                $ext->add($context, $oncode, '', new ext_wait('1'));
                $ext->add($context, $oncode, '', new ext_macro('user-callerid'));
                $ext->add($context, $oncode, '', new ext_setvar('DB(AMPUSER/${AMPUSER}/intercom)', 'enabled'));
                $ext->add($context, $oncode, '', new ext_playback('intercom&enabled'));
                $ext->add($context, $oncode, '', new ext_macro('hangupcall'));

                $target = '${EXTEN:'.strlen($oncode).'}';
                $oncode = "_".$oncode.".";
                $ext->add($context, $oncode, '', new ext_answer(''));
                $ext->add($context, $oncode, '', new ext_wait('1'));
                $ext->add($context, $oncode, '', new ext_macro('user-callerid'));
                $ext->add($context, $oncode, '', new ext_gotoif('$["${DB(AMPUSER/${AMPUSER}/intercom/'.$target.')}" = "allow" ]}','unset'));
                $ext->add($context, $oncode, '', new ext_gotoif('$[${DB_EXISTS(AMPUSER/${EXTEN:3}/device)} != 1]','invaliduser'));
                $ext->add($context, $oncode, '', new ext_dbput('AMPUSER/${AMPUSER}/intercom/'.$target, 'allow'));
                $ext->add($context, $oncode, '', new ext_playback('intercom&enabled&for&extension&number'));
                $ext->add($context, $oncode, '', new ext_saydigits($target));
                $ext->add($context, $oncode, '', new ext_macro('hangupcall'));
                $ext->add($context, $oncode, 'unset', new ext_dbdeltree('AMPUSER/${AMPUSER}/intercom/'.$target));
                $ext->add($context, $oncode, '', new ext_playback('intercom&enabled&cancelled&for&extension&number'));
                $ext->add($context, $oncode, '', new ext_saydigits($target));
                $ext->add($context, $oncode, '', new ext_macro('hangupcall'));
                $ext->add($context, $oncode, 'invaliduser', new ext_playback('extension&number'));
                $ext->add($context, $oncode, '', new ext_saydigits($target));
                $ext->add($context, $oncode, '', new ext_playback('is&invalid'));
                $ext->add($context, $oncode, '', new ext_macro('hangupcall'));
            }
            
            $fcc = new featurecode('paging', 'intercom-off');
            $offcode = $fcc->getCodeActive();
            unset($fcc);
    
            if ($offcode) {
                $ext->add($context, $offcode, '', new ext_answer(''));
                $ext->add($context, $offcode, '', new ext_wait('1'));
                $ext->add($context, $offcode, '', new ext_macro('user-callerid'));
                $ext->add($context, $offcode, '', new ext_setvar('DB(AMPUSER/${AMPUSER}/intercom)', 'disabled'));
                $ext->add($context, $offcode, '', new ext_playback('intercom&disabled'));
                $ext->add($context, $offcode, '', new ext_macro('hangupcall'));

                $target = '${EXTEN:'.strlen($offcode).'}';
                $offcode = "_".$offcode.".";
                $ext->add($context, $offcode, '', new ext_answer(''));
                $ext->add($context, $offcode, '', new ext_wait('1'));
                $ext->add($context, $offcode, '', new ext_macro('user-callerid'));
                $ext->add($context, $offcode, '', new ext_gotoif('$["${DB(AMPUSER/${AMPUSER}/intercom/'.$target.')}" = "deny" ]}','unset2'));
                $ext->add($context, $offcode, '', new ext_gotoif('$[${DB_EXISTS(AMPUSER/${EXTEN:3}/device)} != 1]','invaliduser2'));
                $ext->add($context, $offcode, '', new ext_dbput('AMPUSER/${AMPUSER}/intercom/'.$target, 'deny'));
                $ext->add($context, $offcode, '', new ext_playback('intercom&disabled&for&extension&number'));
                $ext->add($context, $offcode, '', new ext_saydigits($target));
                $ext->add($context, $offcode, '', new ext_macro('hangupcall'));
                $ext->add($context, $offcode, 'unset2', new ext_dbdeltree('AMPUSER/${AMPUSER}/intercom/'.$target));
                $ext->add($context, $offcode, '', new ext_playback('intercom&disabled&cancelled&for&extension&number'));
                $ext->add($context, $offcode, '', new ext_saydigits($target));
                $ext->add($context, $offcode, '', new ext_macro('hangupcall'));
                $ext->add($context, $offcode, 'invaliduser2', new ext_playback('extension&number'));
                $ext->add($context, $offcode, '', new ext_saydigits($target));
                $ext->add($context, $offcode, '', new ext_playback('is&invalid'));
                $ext->add($context, $offcode, '', new ext_macro('hangupcall'));
            }

            /* Create macro-autoanswer that will try to intelligently set the
               required parameters to handle paging. Eventually it will use
                 known device information.

                This macro does the following:

                Input:  IssabelPBX Device number to be called requiring autoanswer
                Output: ${DIAL} Channel Variable with the dial string to be called
                        Appropriate SIP headers added
                                Other special requirements that may be custom for this device

                1. Set ${DIAL} to the device's dial string
                2. If there is a device specific macro defined in the DEVICE's object
                   (DEVICE/<devicenum>/autoanswer/macro) then execute that macro and end
                3. Try to identify endpoints by their useragents that may need known
                   changes and make those changes. These are generated from the
                     paging_autoanswer table so users can extend them, if any are present
                5. Set the variables and end unless a useragent specific ANSWERMACRO is
                   defined in which case call it and end.

                This macro is called for intercoming and paging to try and enable the
                target device to auto-answer. Devices with special needs can be handled
                with the device specific macro. For example, if you have a device that
                can not auto-answer except by specifically configuring a line key on
                the device that always answers, you could use a device specific macro
                to change the dialstring. If you had a set of such devices, you could
                standardize on the device numbers (e.g. nnnn for normal calls and 2nnnn
                for auto-answer calls). You could then create a general purpose macro
                to modify the dial string accordingly. Provisioning tools will be able
                to take advantage of setting and creating such an ability.
                If you have a set of devices that can be identified with a SIP useragent
                then you can use a general macro without setting info in each device.
             */

            $autoanswer_arr = paging_get_autoanswer_useragents();

            $macro = 'macro-autoanswer';
            $ext->add($macro, "s", '', new ext_setvar('DIAL', '${DB(DEVICE/${ARG1}/dial)}'));

            // If we are in DAHDI compat mode, then we need to substitute DAHDI for ZAP
            if ($chan_dahdi) {
                $ext->add($macro, "s", '', new ext_execif('$["${DIAL:0:3}" = "ZAP"]', 'Set','DIAL=DAHDI${DIAL:3}'));
            }
            $ext->add($macro, "s", '', new ext_gotoif('$["${DB(DEVICE/${ARG1}/autoanswer/macro)}" != "" ]', 'macro'));

            // If there are no phone specific auto-answer vars, then we don't care what the phone is below
            //
            if (!empty($autoanswer_arr)) {
                $ext->add($macro, "s", '', new ext_setvar('phone', '${SIPPEER(${CUT(DIAL,/,2)}:useragent)}'));
            }
            // We used to set all the variables here (ALERTINFO, CALLINFO, etc. That has been moved to each
            // paging group and the intercom main macro, since it was redundant for every phone causing a lot
            // of overhead with large page groups.
            //

            // Defaults are setup, now make specific adjustments for detected phones
            // These come from the SQL table as well where installations can make customizations
            //
            foreach ($autoanswer_arr as $autosetting) {
                $useragent   = trim($autosetting['useragent']);
                $autovar     = trim($autosetting['var']);
                $data        = trim($autosetting['setting']);
                switch (ltrim($autovar,'_')) {
                    case 'ANSWERMACRO':
                        $has_answermacro = true;
                        // fall through - no break on purpose
                    case 'ALERTINFO':
                    case 'CALLINFO':
                    case 'SIPURI':
                    case 'VXML_URL':
                    case 'DOPTIONS':
                    case 'DTIME':
                    default:
                        if (trim($data) != "") {
                            $ext->add($macro, "s", '', new ext_execif('$["${phone:0:'.strlen($useragent).'}" = "'.$useragent.'"]', 'Set',$autovar.'='.$data));
                        }
                        break;
                }
            }

            // Now any adjustments have been made, set the headers and done
            //
            if ($has_answermacro) {
                $ext->add($macro, "s", '', new ext_gotoif('$["${ANSWERMACRO}" != ""]','macro2'));
            }
            $ext->add($macro, "s", '', new ext_execif('$["${ALERTINFO}" != ""]', 'SipAddHeader','${ALERTINFO}'));
            $ext->add($macro, "s", '', new ext_execif('$["${CALLINFO}" != ""]', 'SipAddHeader','${CALLINFO}'));
            $ext->add($macro, "s", '', new ext_execif('$["${SIPURI}" != ""]', 'Set','__SIP_URI_OPTIONS=${SIPURI}'));
            $ext->add($macro, "s", 'macro', new ext_macro('${DB(DEVICE/${ARG1}/autoanswer/macro)}','${ARG1}'), 'n',2);
            if ($has_answermacro) {
                $ext->add($macro, "s", 'macro2', new ext_macro('${ANSWERMACRO}','${ARG1}'), 'n',2);
            }
            
            //auto answer stuff
            //set autoanswer variables
            if (!empty($custom_vars)) {
                foreach ($custom_vars as $key => $value) {
                    $ext->add($apppaging, '_AUTOASWER.', '', new ext_setvar('_'.ltrim($key,'_'), $value));
                }
                $ext->add($apppaging, '_AUTOASWER.', '', new ext_macro('autoanswer','${EXTEN:9}'));
                $ext->add($apppaging, '_AUTOASWER.', '', new ext_return());
            }

            // Setup Variables before AGI script
            //
            $ext->add($apppaging, 'ssetup', '', new ext_set('_SIPURI', ''));
            if (isset($alertinfo) && trim($alertinfo) != "") {
                $ext->add($apppaging, 'ssetup', '', new ext_set('_ALERTINFO', $alertinfo));
            }
            
            if (isset($callinfo) && trim($callinfo) != "") {
                $ext->add($apppaging, 'ssetup', '', new ext_set('_CALLINFO', $callinfo));
            }
            if (isset($sipuri) && trim($sipuri) != "") {
                $ext->add($apppaging, 'ssetup', '', new ext_set('_SIPURI', $sipuri));
            }
            if (isset($vxml_url) && trim($vxml_url) != "") {
                $ext->add($apppaging, 'ssetup', '', new ext_set('_VXML_URL', $vxml_url));
            }
            if (isset($doptions) && trim($doptions) != "") {
                $ext->add($apppaging, 'ssetup', '', new ext_set('_DOPTIONS', $doptions));
            }
            $ext->add($apppaging, 'ssetup', '', new ext_set('_DTIME', $dtime));
            $ext->add($apppaging, 'ssetup', '', new ext_set('_ANSWERMACRO', ''));

            $page_opts = $amp_conf['ASTCONFAPP'] == 'app_confbridge' ? '1qs' : '1dqsx';
            $ext->add($apppaging, 'ssetup', '', new ext_set('PAGE_CONF', '${EPOCH}${RAND(100,999)}'));
            $ext->add($apppaging, 'ssetup', '', new ext_return());
                
            // Normal page version (now used for Force also)
            // If we had any custom_vars then call the AUTOASWER subroutine first, otherwise go
            // straight to macro-autoanswer
            if (!empty($custom_vars)) {
                $ext->add($apppaging, "_PAGE.", 'SKIPCHECK', new ext_gosub('AUTOASWER${EXTEN:4},1'));
            } else {
                $ext->add($apppaging, "_PAGE.", 'SKIPCHECK', new ext_macro('autoanswer', '${EXTEN:4}'));
            }
            $ext->add($apppaging, "_PAGE.", '', new ext_dial('${DIAL}','${DTIME},${DOPTIONS}'));
            $ext->add($apppaging, "_PAGE.", 'skipself', new ext_hangup());

            // Try ChanSpy Version
            $ext->add($apppaging, "_SPAGE.", 'chanspy', new ext_chanspy('${SP_DEVICE}-','qW'));
            $ext->add($apppaging, "_SPAGE.", '', new ext_hangup());

            $apppagegroups = 'app-pagegroups';
            // Now get a list of all the paging groups...
            $sql = "SELECT page_group, force_page, duplex FROM paging_config";
            $paging_groups = $db->getAll($sql, DB_FETCHMODE_ASSOC);
            
            if (!$paging_groups) {
                break;//no need to continue if we dont have any pagegroups
            }

            // If Asterisk 10 and app_confbridge:
            //
            // Common to admin:
            //  d: dynamically addd conf
            //  o: talker optimization (don't mix non-talkers)
            //  q: quiet mode no enter/leave sounds
            //  x: close conf when last marked user exits
            //
            // Not in Admin:
            //  1: ???
            //  s: present menu

            if ($amp_conf['ASTCONFAPP'] == 'app_confbridge' && $ast_ge_10 
                && isset($conferences_conf) && is_a($conferences_conf, "conferences_conf")) {
                $pu = 'page_user';
                $pud = 'page_user_duplex';
                foreach (array($pu, $pud) as $u) {
                    $conferences_conf->addConfUser($u, 'quiet', 'yes');
                    $conferences_conf->addConfUser($u, 'announce_user_count', 'no');
                    $conferences_conf->addConfUser($u, 'wait_marked', 'yes');
                    $conferences_conf->addConfUser($u, 'end_marked', 'yes');
                    $conferences_conf->addConfUser($u, 'dsp_drop_silence', 'yes');
                    $conferences_conf->addConfUser($u, 'announce_join_leave', 'no');
                    $conferences_conf->addConfUser($u, 'admin', 'no');
                    $conferences_conf->addConfUser($u, 'marked', 'no');
                }
                $conferences_conf->addConfUser($pu, 'startmuted', 'yes');
            }

            foreach ($paging_groups as $thisgroup) {
                $grp=trim($thisgroup['page_group']);
                switch ($thisgroup['force_page']) {
                case 1:
                    $pagemode = 'FPAGE';
                break;
                case 2:
                    $pagemode = 'SPAGE';
                break;
                case 0:
                default:
                    $pagemode = 'PAGE';
                break;
                }

                $sql = "SELECT ext FROM paging_groups WHERE page_number='$grp'";
                $all_exts = $db->getCol($sql);
                    
                // Create the paging context that is used in the paging application for each phone to auto-answer
                //add ext-paging with goto's to our app-paging context and a hint for the page
                $extpaging = 'ext-paging';
                $ext->add($extpaging, $grp, '', new ext_goto($apppagegroups . ',' . $grp . ',1'));
                $ext->addInclude('from-internal-noxfer-additional',$extpaging);
                $ext->addHint($extpaging, $grp, 'Custom:PAGE' . $grp);
                    
                //app-page dialplan
                    
                $ext->add($apppagegroups, $grp, '', new ext_macro('user-callerid'));
                $ext->add($apppagegroups, $grp, '', new ext_set('_PAGEGROUP', $grp));
                    
                //if page group it in use, goto to busy
                $ext->add($apppagegroups, $grp, 'busy-check', new ext_gotoif('$[${TRYLOCK(apppagegroups'. $grp .')}]', '', 'busy'));
                    
                //set blf to in use
                $ext->add($apppagegroups, $grp, 'devstate', new ext_setvar('DEVICE_STATE(Custom:PAGE' . $grp .')', 'INUSE'));
                    
                $ext->add($apppagegroups, $grp, '', new ext_answer(''));
                $ext->add($apppagegroups, $grp, '', new ext_gosub('1','ssetup', $apppaging));
                $ext->add($apppagegroups, $grp, '', new ext_set('PAGEMODE', $pagemode));
                $ext->add($apppagegroups, $grp, '', new ext_set('PAGE_MEMBERS', implode('-', $all_exts)));
                if ($amp_conf['ASTCONFAPP'] == 'app_confbridge' && $ast_ge_10) { 
                    $ext->add($apppagegroups, $grp, '', new ext_set('PAGE_CONF_OPTS', ($thisgroup['duplex'] ? 'duplex' : '')));
                } else {
                    $ext->add($apppagegroups, $grp, '', new ext_set('PAGE_CONF_OPTS', $page_opts . (!$thisgroup['duplex'] ? 'm' : '')));
                }
                $ext->add($apppagegroups, $grp, 'agi', new ext_agi('page.agi'));            

                //we cant use originate from the dialplan as the dialplan command is not asynchronous
                //we would like to though...
                //this code here as a sign of hope -MB
                /*foreach ($page_members as $member) {
                        $ext->add($apppagegroups, $grp, 'page', new ext_originate($member,'app','meetme', '${PAGE_CONF}\,${PAGE_CONF_OPTS}'));
                }*/                    
                // TODO this is the master so set appropriate
                //      This is what everyone else has: 1doqsx
                //      Common:
                //        d: dynamically addd conf
                //        o: talker optimization (don't mix non-talkers)
                //        q: quiet mode no enter/leave sounds
                //        x: close conf when last marked user exits
                //      Added:
                //          w: W() wait until marked user enters conf
                //          A: Set marked mode
                //          G: G() Play an intro announcemend in conference
                //      Removed:
                //        1: ???
                //        s: present menu
                //
                //
                if ($amp_conf['ASTCONFAPP'] == 'app_confbridge' && $ast_ge_10) { 
                    $ext->add($apppagegroups, $grp, '', new ext_set('CONFBRIDGE(user,template)', $pud));
                    $ext->add($apppagegroups, $grp, '', new ext_set('CONFBRIDGE(user,admin)', 'yes'));
                    $ext->add($apppagegroups, $grp, '', new ext_set('CONFBRIDGE(user,marked)', 'yes'));
                    // TODO: should I have no menu?
                    $ext->add($apppagegroups, $grp, 'page', new ext_meetme('${PAGE_CONF}',',','admin_menu'));
                } else {
                    $ext->add($apppagegroups, $grp, 'page', new ext_meetme('${PAGE_CONF}', 'dqwxAG'));
                }
                $ext->add($apppagegroups, $grp, '', new ext_hangup());
                $ext->add($apppagegroups, $grp, 'busy', new ext_set('PAGE${PAGEGROUP}BUSY', 'TRUE'));
                $ext->add($apppagegroups, $grp, 'play-busy', new ext_busy(3));
                $ext->add($apppagegroups, $grp, 'busy-hang', new ext_goto('app-pagegroups,h,1'));
            }
            
            //h
            $ext->add($apppagegroups, 'h', '', 
                new ext_execif('$[${ISNULL(${PAGE${PAGEGROUP}BUSY})}]', 'Set', 'DEVICE_STATE(Custom:PAGE${PAGEGROUP})=NOT_INUSE'));
            
            //page playback
            $c = 'app-page-stream';
            $ext->add($c, 's', '', new ext_wait(1));
            $ext->add($c, 's', '', new ext_answer());

            // TODO: PAGE_CONF_OPTS reset in agi script so just use proper context if 10+confbridge no mute
            // x: close conf when last marked user exits
            // q: quiet mode no enter/leave sounds
            //
            // TODO: Ideally what we want is to mark the stream and wait for that, if no stream then no wait for makred user. However
            //       it seems like to end a conference you have to have the kick after last marked user since there doesn't have to be
            //       an admin as far as I can tell.
            //
            //
            if ($amp_conf['ASTCONFAPP'] == 'app_confbridge' && $ast_ge_10) { 
                $ext->add($c, 's', '', new ext_set('CONFBRIDGE(user,template)', $pud));
                $ext->add($c, 's', '', new ext_set('CONFBRIDGE(user,marked)', 'yes'));
                $ext->add($c, 's', '', new ext_meetme('${PAGE_CONF}','',''));
            } else {
                $ext->add($c, 's', '', new ext_meetme('${PAGE_CONF}', '${PAGE_CONF_OPTS}'));
            }
            $ext->add($c, 's', '', new ext_hangup());
            
        break;
    }
}

// This is the hook for 'destinations'
function paging_destinations() {
    $results = paging_list();
    // return an associative array with destination and description
        $extens = array();
    if (isset($results)) {
        foreach($results as $result){
            $desc = $result['description'] ? $result['description'] : _('Page Group') . ' ' . $result['page_group'];
            $extens[] = array('destination' => 'app-pagegroups,' . $result['page_group'] . ',1', 'description' => $desc);
        }
        return $extens;
    } else {
        return null;
    }
}

function paging_getdest($exten) {
    return array('pagegroups,'.$exten.',1');
}

function paging_get_autoanswer_defaults($orderd = false) {
    global $db;

    $sql = "SELECT * FROM paging_autoanswer WHERE useragent = 'default'";
    $results = $db->getAll($sql,DB_FETCHMODE_ASSOC);
    if(DB::IsError($results)) {
        $results = array();
    } 
    if ($orderd) {
        foreach ($results as $r) {
            $res[$r['var']] = $r['setting'];    
        }
        $results = $res;
    }
    return $results;
}

function paging_set_autoanswer_defaults($data) {
    global $db;
    
    if (!is_array($data)) {
        return false;
    }    
    
    foreach ($data as $k => $v) {
        $put[] = array('default', $k, $v);
    }
    
    $sql = "REPLACE INTO paging_autoanswer (useragent, var, setting) VALUES (?, ?, ?)";
    $sql = $db->prepare($sql);
    $res = $db->executeMultiple($sql, $put);
    db_e($res);
    
    return true;
}

function paging_get_autoanswer_useragents($useragent = '') {
    global $db;

    $sql = "SELECT * FROM paging_autoanswer WHERE useragent != 'default' ";
    if ($useragent != "") {
        $sql .= "AND useragent = $useragent ";
    }
    $results = $db->getAll($sql,DB_FETCHMODE_ASSOC);
    if(DB::IsError($results)) {
        $results = array();
    } 
    return $results;
}

function paging_list() {
    global $db;

    $sql = "SELECT page_group, description FROM paging_config ORDER BY page_group";
    $results = $db->getAll($sql,DB_FETCHMODE_ASSOC);
    if(DB::IsError($results)) {
        $results = null;
    } else {
        $default = paging_get_default();
        foreach ($results as $key => $list) {
            $results[$key][0] = $list['page_group'];
            if ($list['page_group'] === $default) {
                $results[$key]['is_default'] = true;
            } else {
                $results[$key]['is_default'] = false;
            }
        }
    }
    return $results;
}

function paging_check_extensions($exten=true) {
    global $active_modules;

    $extenlist = array();
    if (is_array($exten) && empty($exten)) {
        return $extenlist;
    }

    $sql = "SELECT page_group, description FROM paging_config ";
    if (is_array($exten)) {
        $sql .= "WHERE page_group in ('".implode("','",$exten)."')";
    }
    $sql .= " ORDER BY page_group";
    $results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

    $type = isset($active_modules['paging']['type'])?$active_modules['paging']['type']:'setup';
    foreach ($results as $result) {
        $thisexten = $result['page_group'];
        $extenlist[$thisexten]['description'] = _("Page Group: ").$result['description'];
        $extenlist[$thisexten]['status'] = 'INUSE';
        $extenlist[$thisexten]['edit_url'] = 'config.php?type='.urlencode($type).'setup&display=paging&selection='.urlencode($thisexten).'&action=modify';
    }
    return $extenlist;
}

function paging_get_devs($grp) {
    global $db;

    // Just in case someone's trying to be smart with a SQL injection.
    $grp = $db->escapeSimple($grp); 

    $sql = "SELECT ext FROM paging_groups where page_number='$grp'";
    $results = $db->getAll($sql);
    if(DB::IsError($results)) 
        $results = array();
    foreach ($results as $val)
        $tmparray[] = $val[0];
    return $tmparray;
}

function paging_get_pagingconfig($grp) {
    global $db;

    // Just in case someone's trying to be smart with a SQL injection.
    $grp = $db->escapeSimple($grp); 

    $sql = "SELECT * FROM paging_config WHERE page_group='$grp'";
    $results = $db->getRow($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($results)) {
        $results = null;
    }
    $sql = "SELECT * FROM admin WHERE variable='default_page_grp' AND value='$grp'";
    $default_group = $db->getRow($sql, DB_FETCHMODE_ASSOC);
    if(DB::IsError($default_group)) {
        $results['default_group'] = 0;
    } else {
        $results['default_group'] = empty($default_group) ? 0 : $default_group['value'];
    }
    return $results;
}

function paging_modify($oldxtn, $xtn, $plist, $force_page, $duplex, $description='', $default_group=0) {
    global $db;

    // Just in case someone's trying to be smart with a SQL injection.
    $xtn = $db->escapeSimple($xtn);

    // Delete it if it's there.
    paging_del($oldxtn);

    // Now add it all back in.
    paging_add($xtn, $plist, $force_page, $duplex, $description, $default_group);

    // Aaad we need a reload.
    needreload();

}

function paging_del($xtn) {
    global $db;
    $sql = "DELETE FROM paging_groups WHERE page_number='$xtn'";
    $res = $db->query($sql);
    if (DB::isError($res)) {
        var_dump($res);
        die_issabelpbx("Error in paging_del(): ");
    }
    
    $sql = "DELETE FROM paging_config WHERE page_group='$xtn'";
    $res = $db->query($sql);
    if (DB::isError($res)) {
        var_dump($res);
        die_issabelpbx("Error in paging_del(): ");
    }
    sql("DELETE FROM `admin` WHERE variable = 'default_page_grp' AND value = '$xtn'");
    
    needreload();
}

function paging_add($xtn, $plist, $force_page, $duplex, $description='', $default_group) {
    global $db;

    // $plist contains a string of extensions, with \n as a seperator. 
    // Split that up first.
    if (is_array($plist)) {
        $xtns = $plist;
    } else {
        $xtns = explode("\n",$plist);
    }
    foreach (array_keys($xtns) as $val) {
        $val = $db->escapeSimple(trim($xtns[$val]));
        // Sanity check input.
        
        $sql = "INSERT INTO paging_groups(page_number, ext) VALUES ('$xtn', '$val')";
        $db->query($sql);
    }
    
    $description = $db->escapeSimple(trim($description));
    $sql = "INSERT INTO paging_config(page_group, force_page, duplex, description) VALUES ('$xtn', '$force_page', '$duplex', '$description')";
    $db->query($sql);
    
    if ($default_group) {
        sql("DELETE FROM `admin` WHERE variable = 'default_page_grp'");
        sql("INSERT INTO `admin` (variable, value) VALUES ('default_page_grp', '$xtn')");
    } else {
        sql("DELETE FROM `admin` WHERE variable = 'default_page_grp' AND value = '$xtn'");
    }
    
    needreload();
}

function paging_check_default($extension) {
    $sql = "SELECT ext FROM paging_groups WHERE ext = '$extension' AND page_number = (SELECT value FROM admin WHERE variable = 'default_page_grp' limit 1)";
    $results = sql($sql,"getAll");
    return (count($results) ? 1 : 0);
}

function paging_get_default() {
    $default_group = sql("SELECT value FROM `admin` WHERE variable = 'default_page_grp' limit 1", "getOne");
  return $default_group;
}

function paging_set_default($extension, $value) {
    $default_group = sql("SELECT value FROM `admin` WHERE variable = 'default_page_grp' limit 1", "getOne");
    if ($default_group == '') {
        return false;
    }
    sql("DELETE FROM paging_groups WHERE ext = '$extension' AND page_number = '$default_group'");
    if ($value == 1) {
        sql("INSERT INTO paging_groups (page_number, ext) VALUES ('$default_group', '$extension')");
    }
}
    
function paging_configpageinit($pagename) {
    global $currentcomponent;

    $action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
    $extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
    $extension = isset($_REQUEST['extension'])?$_REQUEST['extension']:null;
    $tech_hardware = isset($_REQUEST['tech_hardware'])?$_REQUEST['tech_hardware']:null;

    // We only want to hook 'devices' or 'extensions' pages.
    if ($pagename != 'devices' && $pagename != 'extensions') {
        return true;
    }

    if ($tech_hardware != null && ($pagename == 'extensions' || $pagename == 'devices')) {
        paging_applyhooks();
        $currentcomponent->addprocessfunc('paging_configprocess', 8);
    } elseif ($action=="add") {
        // We don't need to display anything on an 'add', but we do need to handle returned data.
        $currentcomponent->addprocessfunc('paging_configprocess', 8);
    } elseif ($extdisplay != '') {
        // We're now viewing an extension, so we need to display _and_ process.
        paging_applyhooks();
        $currentcomponent->addprocessfunc('paging_configprocess', 8);
    }
}

function paging_applyhooks() {
    global $currentcomponent;

    // Add the 'process' function - this gets called when the page is loaded, to hook into 
    // displaying stuff on the page.
    $currentcomponent->addoptlistitem('page_group', '0', _("Exclude"));
    $currentcomponent->addoptlistitem('page_group', '1', _("Include"));
    $currentcomponent->setoptlistopts('page_group', 'sort', false);

    $currentcomponent->addguifunc('paging_configpageload');
}

/*
*/
// This is called before the page is actually displayed, so we can use addguielem().
function paging_configpageload() {
    global $currentcomponent;

    // Init vars from $_REQUEST[]
    $action = isset($_REQUEST['action']) ? $_REQUEST['action']:null;
    $extdisplay = isset($_REQUEST['extdisplay']) ? $_REQUEST['extdisplay']:null;
    $tech_hardware = isset($_REQUEST['tech_hardware']) ? $_REQUEST['tech_hardware']:'';
    
    // Don't display this stuff it it's on a 'This xtn has been deleted' page.
    if ($action != 'del' && $tech_hardware != 'virtual') {

        $default_group = sql("SELECT value FROM `admin` WHERE variable = 'default_page_grp'", "getOne");
        $section = _("Default Group Inclusion");
        if ($default_group != "") {
            $in_default_page_grp = paging_check_default($extdisplay);
            $currentcomponent->addguielem($section, new gui_selectbox('in_default_page_grp', $currentcomponent->getoptlist('page_group'), $in_default_page_grp, _('Default Page Group'), _('You can include or exclude this extension/device from being part of the default page group when creating or editing.'), false));
        } 
    }
}

function paging_configprocess() {
    global $db;

    //create vars from the request
    //
    $action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
    $ext = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
    $extn = isset($_REQUEST['extension'])?$_REQUEST['extension']:null;
    $in_default_page_grp = isset($_REQUEST['in_default_page_grp'])?$_REQUEST['in_default_page_grp']:false;

    if (($_REQUEST['display'] == 'devices') && $action == 'add') {
        $extdisplay = $_REQUEST['deviceid'];
    } else {
        $extdisplay = ($ext=='') ? $extn : $ext;
    }

    if ($action == "add" || $action == "edit") {
        if (!isset($GLOBALS['abort']) || $GLOBALS['abort'] !== true) {
            if ($in_default_page_grp !== false) {
                paging_set_default($extdisplay, $in_default_page_grp);
            }
        }
    } elseif ($action == "del") {
        $sql = "DELETE FROM paging_groups WHERE ext = '$extdisplay'";
        sql($sql);
    }
}

?>
