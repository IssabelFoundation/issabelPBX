<?php /* $Id$ */

/** Controls if online module and install/uninstall options are available.
 * This is meant for when using external packaging systems (eg, deb or rpm) to manage
 * modules. Package maintainers should set AMPEXTERNPACKAGES to true in /etc/amportal.conf.
 * Optionally, the other way is to remove the below lines, and instead just define
 * EXTERNAL_PACKAGE_MANAGEMENT as 1. This prevents changing the setting from amportal.conf.
 */
if (!isset($amp_conf['AMPEXTERNPACKAGES']) || ($amp_conf['AMPEXTERNPACKAGES'] != 'true')) {
	define('EXTERNAL_PACKAGE_MANAGEMENT', 0);
} else {
	define('EXTERNAL_PACKAGE_MANAGEMENT', 1);
}

// Handle the ajax post back of an update online updates email array and status
//
if ($quietmode && isset($_REQUEST['update_email'])) {

	$update_email   = $_REQUEST['update_email'];
	$ci = new CI_Email();
	if (!$ci->valid_email($update_email) && $update_email) {
		$json_array['status'] = _("Invalid email address") . ' : ' . $update_email;
	} else {
		$cm =& cronmanager::create($db);
		$cm->save_email($update_email);
		$json_array['status'] = true;
	}
	header("Content-type: application/json");
	echo json_encode($json_array);
	exit;
}

$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:'';

global $active_repos;
$loc_domain = 'amp';
if (isset($_REQUEST['check_online'])) {
  $online = 1;
  $active_repos = $_REQUEST['active_repos'];
  module_set_active_repos($active_repos);
} else {
  $online = (isset($_REQUEST['online']) && $_REQUEST['online'] && !EXTERNAL_PACKAGE_MANAGEMENT) ? 1 : 0;
  $active_repos = module_get_active_repos();
}

// fix php errors from undefined variable. Not sure if we can just change the reference below to use
// online since it changes values so just setting to what we decided it is here.

$moduleaction = isset($_REQUEST['moduleaction'])?$_REQUEST['moduleaction']:false;
/*
	moduleaction is an array with the key as the module name, and possible values:

	downloadinstall - download and install (used when a module is not locally installed)
	upgrade - download and install (used when a module is locally installed)
	install - install/upgrade locally available module
	enable - enable local module
	disable - disable local module
	uninstall - uninstall local module
*/

$issabelpbx_version = get_framework_version();
$issabelpbx_version = $issabelpbx_version ? $issabelpbx_version : getversion();
$issabelpbx_help_url = "http://www.issabel.org/issabelpbx-help-system?issabelpbx_version=".urlencode($issabelpbx_version);

if (!$quietmode) {
	$cm =& cronmanager::create($db);
	$online_updates = $cm->updates_enabled() ? 'yes' : 'no';
	$update_email   = $cm->get_email();

	if (!$cm->updates_enabled()) {
		$shield_class = 'updates_off';
	} else {
		$shield_class = $update_email ? 'updates_full' : 'updates_partial';
	}

	$update_blurb   = htmlspecialchars(_("Add your email here to receive important security and module updates. The email address you provide is NEVER transmitted to the IssabelPBX remote servers. The email is ONLY used by your local PBX to send notifications of updates that are available as well as IMPORTANT Security Notifications. It is STRONGLY advised that you keep this enabled and keep updated of these important notifications to avoid costly security vulnerabilities."));
	$ue = htmlspecialchars($update_email);
?>
<div class='content responsive'>
<?php

echo "<h2>" . _("Module Administration") . "</h2>";

?>

<?php
$modify_notification='';
if($shield_class=='updates_partial') {
    $umsg = _("There is no email configured yet for update notifications. It is recommended to setup one.");
    $utitle = _("Click to configure Update Notifications");
?>
<div id="shield_link_div" class="block">
<div class="notification is-warning is-light">
  <button class="delete"></button>
  <div class='block'>
  <?php echo $umsg; ?>
    <a href="#" id="show_auto_update"><?php echo $utitle;?></a>
  </div>
</div>

</div>
<?php
} else if($shield_class=='updates_full' ) {
    $modify_notification = "<a id='modify_notification_email' data-email='$ue' class='button is-rounded is-small'>"._('Change notification email address')."</a>";
}
?>

<script>

    function email_popup(current_email) {
        Swal.fire({
            title: ipbx.msg.framework.enteremail,
            text: '<?php echo $update_blurb?>',
            input: 'email',
            inputValue: current_email,
            showCancelButton: true,
            confirmButtonText: ipbx.msg.framework.save,
            cancelButtonText: ipbx.msg.framework.cancel,
            validationMessage: ipbx.msg.framework.bademail,
            customClass: {
                popup: 'swal-wide',
            }
        }).then((result) => {
            if (result.isConfirmed) {
                url = window.location.href.split('?')[0] + "?quietmode=1&skip_astman=1&display=modules&update_email=" + result.value;
                fetch(url)
                    .then(handleErrors)
                    .then(response => { sweet_toast('success',ipbx.msg.framework.item_modified); $('#modify_notification_email').attr('data-email',result.value); $('.notification').hide(); })
                    .catch(error => sweet_toast('error',error));
            }
        });
    }

    $(function(){

        $('#modify_notification_email').on('click',function() {
            email_popup($(this).attr('data-email'));
        });

        $('#show_auto_update').on('click',function() {
            email_popup('');
        });

        $('.vulpopup').on('click',function(e) {
			e.preventDefault();
            e.stopPropagation();
            showSecurityMsg(this);
        });

	$('#uploadmod').on('change',function() { $('#selected_file_name').text(this.value.replace(/.*[\/\\]/, '')); });

        window.showSecurityMsg = async (element) => {
            console.log(element);
            var steps = [];
            var text = [];
            var cont=0;
            $.each($(element).data('sec'), function(index, value) {
                cont++;
                steps.push(cont);
                text.push(value);
            });
            const Queue = Swal.mixin({
               progressSteps: steps,
               confirmButtonText: ipbx.msg.framework.next,
               cancelButtonText:  ipbx.msg.framework.previous,
               reverseButtons: true,
               showClass: { backdrop: 'swal2-noanimation' },
               hideClass: { backdrop: 'swal2-noanimation' }
            })
            for(i=0;i<cont;i++) {
                if($('#security-'+text[i]).length>0) {
                    showtext = $('#security-'+text[i]).html();
                } else {
                    showtext = text[i];
                }
                var confText = ipbx.msg.framework.next;
                if(i+1==cont) {
                    confText = ipbx.msg.framework.close;
                }
                const result = await Queue.fire({ title: text[i], showCancelButton: i > 0, confirmButtonText: confText, html: showtext, customClass: 'swal-wide', currentProgressStep: i });
                if(result.dismiss === Swal.DismissReason.cancel) {
                    i--;
                    i--;
                }
            }
        }
    })

	function toggleInfoPane(pane) {
		var style = document.getElementById(pane).style;
		if (style.display == 'none' || style.display == '') {
			style.display = 'block';
		} else {
			style.display = 'none';
        }
        positionActionBar();
    }

	function check_upgrade_all() {
		var re = /^moduleaction\[([a-z0-9_\-]+)\]$/;
		for(i=0; i<document.modulesGUI.elements.length; i++) {
			if (document.modulesGUI.elements[i].value == 'upgrade') {
				if (match = document.modulesGUI.elements[i].name.match(re)) {
					// check the box
					document.modulesGUI.elements[i].checked = true;
					// expand info pane
					document.getElementById('infopane_'+match[1]).style.display = 'block';
				}
			}
		}
	}
	function check_download_all() {
		var re = /^moduleaction\[([a-z0-9_\-]+)\]$/;
		for(i=0; i<document.modulesGUI.elements.length; i++) {
			if (document.modulesGUI.elements[i].value == 'downloadinstall') {
				if (match = document.modulesGUI.elements[i].name.match(re)) {
					// check the box
					document.modulesGUI.elements[i].checked = true;
					// expand info pane
					document.getElementById('infopane_'+match[1]).style.display = 'block';
				}
			}
		}
	}
	function showhide_upgrades() {
		var upgradesonly = document.getElementById('show_upgradable_only').checked;
		var module_re = /^module_([a-z0-9_-]+)$/;   // regex to match a module element id
		var cat_re = /^category_([a-zA-Z0-9_]+)$/; // regex to match a category element id
		var elements = document.getElementById('modulelist').getElementsByTagName('li');
		// loop through all modules, check if there is an upgrade_<module> radio box
		for(i=0; i<elements.length; i++) {
			if (match = elements[i].id.match(module_re)) {
				if (!document.getElementById('upgrade_'+match[1])) {
					// not upgradable
					document.getElementById('module_'+match[1]).style.display = upgradesonly ? 'none' : 'block';
				}
			}
		}
		// hide category headings that don't have any visible modules
		var elements = document.getElementById('modulelist').getElementsByTagName('div');
		// loop through category items
		for(i=0; i<elements.length; i++) {
			if (elements[i].id.match(cat_re)) {
				var subelements = elements[i].getElementsByTagName('li');
				var display = false;
				for(j=0; j<subelements.length; j++) {
					// loop through children <li>'s, find names that are module element id's
					if (subelements[j].id.match(module_re) && subelements[j].style.display != 'none') {
						// if at least one is visible, we're displaying this element
						display = true;
						break; // no need to go further
					}
				}
				document.getElementById(elements[i].id).style.display = display ? 'block' : 'none';
			}
		}
	}
	var box;
    function process_module_actions(actions) {

        urlStr = "config.php?type=<?php echo $type ?>&amp;display=modules&amp;extdisplay=process&amp;quietmode=1";
        for (var i in actions) {
            urlStr += "&amp;moduleaction["+i+"]="+actions[i];
        }
        Swal.fire({
            html: '<iframe id="popover-frame" frameBorder="0" src="' + urlStr + '" width="100%" height="95%" onload="popOverDisplay(this)"></iframe>',
            focusConfirm: false,
            cancelButtonText: ipbx.msg.framework.cancel,
            showConfirmButton: false,
            showCancelButton: false,
            heightAuto: false,
            customClass: {'popup':'swal-popover','actions':'popover_actions'},
        }).then((result)=>{
            location.href = 'config.php?display=modules&amp;type=<?php echo $type ?>&amp;online=<?php echo $online; ?>';
        });
    }

    function close_module_actions() {
        swal.close();
    }
	</script>

<?php
  //TODO: decide if warnings of any sort need to be given, or just list of repos active?
} else {
	// $quietmode==true
	?>
    <html><head>
        <link href="assets/css/mainstyle.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="assets/css/bulma.min.css">
    </head><body>
	<?php
}

$modules_local = module_getinfo(false,false,true);

/*
echo "<pre>";
print_r($modules_local);
echo "</pre>";
*/

if ($online) {
    $security_array = array();
    $security_issues_to_report = array();
    $modules_online = module_getonlinexml(false, false, $security_array);

    // $module_getonlinexml_error is a global set by module_getonlinexml()
    if ($module_getonlinexml_error) {
        echo "<div class=\"warning\"><p>".sprintf(_("Warning: Cannot connect to online repository(s) (%s). Online modules are not available."), $amp_conf['MODULE_REPO'])."</p></div><br />";
        $online = 0;
        unset($modules_online);
    } else if (!is_array($modules_online)) {
        echo "<div class=\"warning\"><p>".sprintf(_("Warning: Error retrieving updates from online repository(s) (%s). Online modules are not available."), $amp_conf['MODULE_REPO'])."</p></div><br />";
        $online = 0;
        unset($modules_online);
    } else {
        // combine online and local modules
        $modules = $modules_online;
        foreach (array_keys($modules) as $name) {
            if (isset($modules_local[$name])) {
                // combine in any other values in _local that aren't in _online
                $modules[$name] += $modules_local[$name];

                // explicitly override these values with the _local ones
                // - should never come from _online anyways, but this is just to be sure
                $modules[$name]['status'] = $modules_local[$name]['status'];
                $modules[$name]['dbversion'] = isset($modules_local[$name]['dbversion'])?$modules_local[$name]['dbversion']:'';
            } else {
                // not local, so it's not installed
                $modules[$name]['status'] = MODULE_STATUS_NOTINSTALLED;
            }
        }
        // add any remaining local-only modules
        $modules += $modules_local;

        // use online categories
        foreach (array_keys($modules) as $modname) {
            if (isset($modules_online[$modname]['category'])) {
                $modules[$modname]['category'] = $modules_online[$modname]['category'];
            }
        }
    }
}

if (!isset($modules)) {
    $modules = & $modules_local;
}

//--------------------------------------------------------------------------------------------------------
switch ($extdisplay) {  // process, confirm, or nothing
case 'process':
    echo "<div id=\"moduleBoxContents\">";
    echo "<h4>"._("Please wait while module actions are performed")."</h4>\n";
    echo "<div id=\"moduleprogress\">";

    // stop output buffering, and send output
    @ ob_flush();
    flush();
    foreach ($moduleaction as $modulename => $action) {
        $didsomething = true; // set to false in default clause of switch() below..

        switch ($action) {
        case 'force_upgrade':
        case 'upgrade':
        case 'downloadinstall':
            if (!EXTERNAL_PACKAGE_MANAGEMENT) {
                echo sprintf(_('Downloading %s'), $modulename).' <span id="downloadprogress_'.$modulename.'"></span>';
                if (is_array($errors = module_download($modulename, false, 'download_progress'))) {
                    echo '<span class="error">'.sprintf(_("Error(s) downloading %s"),$modulename).': ';
                    echo '<ul><li>'.implode('</li><li>',$errors).'</li></ul>';
                    echo '</span>';
                } else {
                    if (is_array($errors = module_install($modulename))) {
                        echo '<span class="error">'.sprintf(_("Error(s) installing %s"),$modulename).': ';
                        echo '<ul><li>'.implode('</li><li>',$errors).'</li></ul>';
                        echo '</span>';
                    } else {
                        echo '<span class="success">'.sprintf(_("%s installed successfully"),$modulename).'</span>';
                    }
                }
            }
            break;
        case 'install':
            if (!EXTERNAL_PACKAGE_MANAGEMENT) {
                if (is_array($errors = module_install($modulename))) {
                    echo '<span class="error">'.sprintf(_("Error(s) installing %s"),$modulename).': ';
                    echo '<ul><li>'.implode('</li><li>',$errors).'</li></ul>';
                    echo '</span>';
                } else {
                    echo '<span class="success">'.sprintf(_("%s installed successfully"),$modulename).'</span>';
                }
            }
            break;
        case 'enable':
            if (is_array($errors = module_enable($modulename))) {
                echo '<span class="error">'.sprintf(_("Error(s) enabling %s"),$modulename).': ';
                echo '<ul><li>'.implode('</li><li>',$errors).'</li></ul>';
                echo '</span>';
            } else {
                echo '<span class="success">'.sprintf(_("%s enabled successfully"),$modulename).'</span>';
            }
            break;
        case 'disable':
            if (is_array($errors = module_disable($modulename))) {
                echo '<span class="error">'.sprintf(_("Error(s) disabling %s"),$modulename).': ';
                echo '<ul><li>'.implode('</li><li>',$errors).'</li></ul>';
                echo '</span>';
            } else {
                echo '<span class="success">'.sprintf(_("%s disabled successfully"),$modulename).'</span>';
            }
            break;
        case 'uninstall':
            if (!EXTERNAL_PACKAGE_MANAGEMENT) {
                if (is_array($errors = module_uninstall($modulename))) {
                    echo '<span class="error">'.sprintf(_("Error(s) uninstalling %s"),$modulename).': ';
                    echo '<ul><li>'.implode('</li><li>',$errors).'</li></ul>';
                    echo '</span>';
                } else {
                    echo '<span class="success">'.sprintf(_("%s uninstalled successfully"),$modulename).'</span>';
                }
            }
            break;
        default:
            // just so we don't send an <hr> and flush()
            $didsomething = false;
        }

        if ($didsomething) {
            echo "<hr /><br />";
            @ ob_flush();
            flush();
        }
    }
    echo "</div>";
    if ($quietmode) {
        echo "\t<a class='button is-rounded is-link is-small' href=\"#\" onclick=\"parent.close_module_actions(true);\" />"._("Return")."</a>";
    } else {
        echo "\t<input class='button is-rounded is-link is-small' type=\"button\" value=\""._("Return")."\" onclick=\"location.href = 'config.php?display=modules&amp;type=$type&amp;online=".$online."';\" />";
        echo "</div>";
    }
    break;
case 'confirm':
    ksort($moduleaction);
        /* if updating language packs, make sure they are the last thing to be done so that
        any modules currently being updated at the same time will be done so first and
            language pack updates for those modules will be included.
         */
    if (isset($moduleaction['fw_langpacks'])) {
        $tmp = $moduleaction['fw_langpacks'];
        unset($moduleaction['fw_langpacks']);
        $moduleaction['fw_langpacks'] = $tmp;
        unset($tmp);
    }

    echo "<form name=\"modulesGUI\" action=\"config.php\" method=\"post\">";
    echo "<input type=\"hidden\" name=\"display\" value=\"".$display."\" />";
    echo "<input type=\"hidden\" name=\"type\" value=\"".$type."\" />";
    echo "<input type=\"hidden\" name=\"online\" value=\"".$online."\" />";
    echo "<input type=\"hidden\" name=\"extdisplay\" value=\"process\" />";

    echo "\t<script type=\"text/javascript\"> var moduleActions = new Array(); </script>\n";

    $actionstext = array();
    $force_actionstext = array();
    $errorstext = array();
    foreach ($moduleaction as $module => $action) {
        $text = false;
        $skipaction = false;

        // make sure name is set. This is a problem for broken modules
        if (!isset($modules[$module]['name'])) {
            $modules[$module]['name'] = $module;
        }

        switch ($action) {
        case 'upgrade':
        case 'force_upgrade':
            if (!EXTERNAL_PACKAGE_MANAGEMENT) {
                if (is_array($errors = module_checkdepends($modules_online[$module]))) {
                    $skipaction = true;
                    $errorstext[] = sprintf(_("%s cannot be upgraded: %s Please try again after the dependencies have been installed."),
                        $modules[$module]['name'],
                        '<ul><li>'.implode('</li><li>',$errors).'</li></ul>');
                } else {
                    switch ( version_compare_issabel($modules[$module]['dbversion'], $modules_online[$module]['version'])) {
                    case '-1':
                        $actionstext[] = sprintf(_("%s %s will be upgraded to online version %s"), dgettext($module,$modules[$module]['name']), $modules[$module]['dbversion'], $modules_online[$module]['version']);
                        break;
                    case '0':
                        $force_actionstext[] = sprintf(_("%s %s will be re-installed to online version %s"), dgettext($module,$modules[$module]['name']), $modules[$module]['dbversion'], $modules_online[$module]['version']);
                        break;
                    default:
                        $force_actionstext[] = sprintf(_("%s %s will be downgraded to online version %s"), dgettext($module,$modules[$module]['name']), $modules[$module]['dbversion'], $modules_online[$module]['version']);
                    }
                }
            }
            break;
        case 'downloadinstall':
            if (!EXTERNAL_PACKAGE_MANAGEMENT) {
                if (is_array($errors = module_checkdepends($modules_online[$module]))) {
                    $skipaction = true;
                    $errorstext[] = sprintf(_("%s cannot be installed: %s Please try again after the dependencies have been installed."),
                        $modules[$module]['name'],
                        '<ul><li>'.implode('</li><li>',$errors).'</li></ul>');
                } else {
                    $actionstext[] =  sprintf(_("%s %s will be downloaded and installed"), dgettext($module,$modules[$module]['name']), $modules_online[$module]['version']);
                }
            }
            break;
        case 'install':
            if (!EXTERNAL_PACKAGE_MANAGEMENT) {
                if (is_array($errors = module_checkdepends($modules[$module]))) {
                    $skipaction = true;
                    $errorstext[] = sprintf((($modules[$module]['status'] == MODULE_STATUS_NEEDUPGRADE) ?  _("%s cannot be upgraded: %s Please try again after the dependencies have been installed.") : _("%s cannot be installed: %s Please try again after the dependencies have been installed.") ),
                        $modules[$module]['name'],
                        '<ul><li>'.implode('</li><li>',$errors).'</li></ul>');
                } else {
                    if ($modules[$module]['status'] == MODULE_STATUS_NEEDUPGRADE) {
                        $actionstext[] =  sprintf(_("%s %s will be upgraded to %s"), dgettext($module,$modules[$module]['name']), $modules[$module]['dbversion'], $modules[$module]['version']);
                    } else {
                        $actionstext[] =  sprintf(_("%s %s will be installed and enabled"), dgettext($module,$modules[$module]['name']), $modules[$module]['version']);
                    }
                }
            }
            break;
        case 'enable':
            if (is_array($errors = module_checkdepends($modules[$module]))) {
                $skipaction = true;
                $errorstext[] = sprintf(_("%s cannot be enabled: %s Please try again after the dependencies have been installed."),
                    $modules[$module]['name'],
                    '<ul><li>'.implode('</li><li>',$errors).'</li></ul>');
            } else {
                $actionstext[] =  sprintf(_("%s %s will be enabled"), dgettext($module,$modules[$module]['name']), $modules[$module]['dbversion']);
            }
            break;
        case 'disable':
            if (is_array($errors = module_reversedepends($modules[$module]))) {
                $skipaction = true;
                $errorstext[] = sprintf(_("%s cannot be disabled because the following modules depend on it: %s Please disable those modules first then try again."),
                    $modules[$module]['name'],
                    '<ul><li>'.implode('</li><li>',$errors).'</li></ul>');
            } else {
                $actionstext[] =  sprintf(_("%s %s will be disabled"), dgettext($module,$modules[$module]['name']), $modules[$module]['dbversion']);
            }
            break;
        case 'uninstall':
            if (!EXTERNAL_PACKAGE_MANAGEMENT) {
                if (is_array($errors = module_reversedepends($modules[$module]))) {
                    $skipaction = true;
                    $errorstext[] = sprintf(_("%s cannot be uninstalled because the following modules depend on it: %s Please disable those modules first then try again."),
                        $modules[$module]['name'],
                        '<ul><li>'.implode('</li><li>',$errors).'</li></ul>');
                } else {
                    $actionstext[] =  sprintf(_("%s %s will be uninstalled"), dgettext($module,$modules[$module]['name']), $modules[$module]['dbversion']);
                }
            }
            break;
        }

        // If error above we skip this action so we can proceed with the others
        //
        if (!$skipaction) { //TODO
            echo "\t<script type=\"text/javascript\"> moduleActions['".$module."'] = '".$action."'; </script>\n";
        }
    }

    // Write out the errors, if there are additional actions that can be accomplished list those next with the choice to
    // process which will ignore the ones with errors but process the rest.
    //
    if (count($errorstext) > 0) {
        echo "<h4>"._("Errors with selection:")."</h4>\n";
        echo "<ul>\n";
        foreach ($errorstext as $text) {
            echo "\t<li>".$text."</li>\n";
        }
        echo "</ul>";
    }
    if (count($actionstext) > 0 || count($force_actionstext) > 0) {
        if (count($errorstext) > 0) {
            echo "<h4>"._("You may confirm the remaining selection and then try the again for the listed issues once the required dependencies have been met:")."</h4>\n";
        } else {
            echo "<h4>"._("Please confirm the following actions:")."</h4>\n";
        }
        if (count($actionstext)) {
            echo "<table class='table is-borderless is-narrow'><tr><td>";
            //echo "<h5>"._("Upgrades, installs, enables and disables:")."</h5>\n";
            echo "<ul>\n";
            foreach ($actionstext as $text) {
                echo "\t<li>".$text."</li>\n";
            }
            echo "</ul>";
            echo "</td></tr></table>";
        }
        if (count($force_actionstext)) {
            echo "<div>"._("Forced downgrades and re-installs:")."</div>\n";
            echo "<ul>\n";
            foreach ($force_actionstext as $text) {
                echo "\t<li>".$text."</li>\n";
            }
            echo "</ul>";
        }
        echo "\t<input type=\"button\" class=\"button is-small is-rounded\" value=\""._("Confirm")."\" name=\"process\" onclick=\"process_module_actions(moduleActions);\" />";
    } else {
        echo "<h4>"._("No actions to perform")."</h4>\n";
        echo "<p>"._("Please select at least one action to perform by clicking on the module, and selecting an action on the \"Action\" tab.")."</p>";
    }
    echo "\t<input type=\"button\" class=\"button is-small is-rounded\" value=\""._("Cancel")."\" onclick=\"location.href = 'config.php?display=modules&amp;type=$type&amp;online=$online';\" />";
    echo "</form>";

    break;
case 'upload':
    // display links
    if (!EXTERNAL_PACKAGE_MANAGEMENT) {
        $disp_buttons[] = 'local';
        if (isset($_FILES['uploadmod']) && !empty($_FILES['uploadmod']['name'])) {
            // display upload button, only if they did upload something
            $disp_buttons[] = 'upload';
        }
     //   displayRepoSelect($disp_buttons,$modify_notification);
    } else {
        echo "<a href='config.php?display=modules&amp;type=$type'>"._("Manage local modules")."</a>\n";
    }

    if (isset($_FILES['uploadmod']) && !empty($_FILES['uploadmod']['name'])) {
        $res = module_handleupload($_FILES['uploadmod']);
        if (is_array($res)) {

            echo '<div class="error"><p>';
            echo sprintf(_('The following error(s) occurred processing the uploaded file: %s'),
                '<ul><li>'.implode('</li><li>',$res).'</li></ul>');
            echo sprintf(_('You should fix the problem or select another file and %s.'),
                "<a href='config.php?display=modules&amp;type=$type'>"._("try again")."</a>");
            echo "</p></div>\n";
        } else {

            echo "<div class='box mt-2'>".sprintf(_("Module uploaded successfully. You need to enable the module using %s to make it available."),
                "<a href='config.php?display=modules&amp;type=$type'>"._("local module administration")."</a>")
                ."</div>\n";
        }

    } else {
        echo "<div class='box mt-2'>";
        echo "<p>"._('You can upload a tar gzip file containing a IssabelPBX module from your local system. If a module with the same name already exists, it will be overwritten.')."</p>\n";

        echo "<form name=\"modulesGUI-upload\" action=\"config.php\" method=\"post\" enctype=\"multipart/form-data\">";
        echo "<input type=\"hidden\" name=\"display\" value=\"".$display."\" />";
        echo "<input type=\"hidden\" name=\"type\" value=\"".$type."\" />";
        echo "<input type=\"hidden\" name=\"extdisplay\" value=\"upload\" />";
        //echo "<input type=\"file\" name=\"uploadmod\" /> ";
        //echo "&nbsp;&nbsp; <input type=\"submit\" value=\"Upload\" />";

echo "<div class='file has-name is-fullwidth has-addons'>
  <label class='file-label'>
    <input class='file-input' type='file' name='uploadmod' id='uploadmod'>
    <span class='file-cta'>
      <span class='file-icon'>
        <i class='fa fa-upload'></i>
      </span>
      <span class='file-label'>"._('Choose a file...')."
      </span>
    </span>
    <span class='file-name' id='selected_file_name'>
    </span>
  </label>
  <div class='control'><input type='submit' class='button is-info' value='"._('Upload')."' onclick='$.LoadingOverlay('show');'/></div>
</div>";

	echo "</form>";




	echo "</div>";



    }

    break;
case 'online':
default:

    uasort($modules, 'category_sort_callback');

    if ($online) {
        // Check for announcements such as security advisories, required updates, etc.
        //
        $announcements = module_get_annoucements();
        if (isset($announcements) && !empty($announcements)) {
            echo "<div class='announcements'>$announcements</div>";
        }

        if (!EXTERNAL_PACKAGE_MANAGEMENT) {
            echo "<a class='button is-small is-rounded' href='config.php?display=modules&amp;type=$type&amp;online=0'>"._("Manage local modules")."</a>\n";
//            echo "<input type=\"checkbox\" id=\"show_upgradable_only\" onclick=\"showhide_upgrades();\" /><label for=\"show_upgradable_only\">"._("Show only upgradeable")."</label>";

            echo "
<div class='field'>
  <input id='show_upgradable_only' type='checkbox' class='switch is-rtl' onclick=\"showhide_upgrades();\">
  <label for='show_upgradable_only'>"._("Show only upgradeable")."</label>
</div>
";

        }
    } else {
        if (!EXTERNAL_PACKAGE_MANAGEMENT) {
            displayRepoSelect(array('upload'),$modify_notification);
        } else {
            echo " | <a href='config.php?display=modules&type=$type&extdisplay=upload'>"._("Upload module")."</a><br />\n";
        }
    }

    echo "<form name=\"modulesGUI\" action=\"config.php\" method=\"post\">";
    echo "<input type=\"hidden\" name=\"display\" value=\"".$display."\" />";
    echo "<input type=\"hidden\" name=\"type\" value=\"".$type."\" />";
    echo "<input type=\"hidden\" name=\"online\" value=\"".$online."\" />";
    echo "<input type=\"hidden\" name=\"extdisplay\" value=\"confirm\" />";

    echo "<div id=\"modulelist\" class=\"mt-2\">\n";

    echo "\t<div id=\"modulelist-header\" class='columns mb-0'>";
    echo "\t\t<div class=\"column modulename\">"._("Module")."</div>\n";
    echo "\t\t<div class=\"column moduleversion\">"._("Version")."</div>\n";
    echo "\t\t<div class=\"column modulepublisher\">"._("Publisher")."</div>\n";
    echo "\t\t<div class=\"column clear\">&nbsp;</div>\n";
    echo "\t</div>";

    $category = false;
    $numdisplayed = 0;
    $fd = $amp_conf['ASTETCDIR'].'/issabelpbx_module_admin.conf';
    if (file_exists($fd)) {
        $module_filter = parse_ini_file($fd);
    } else {
        $module_filter = array();
    }
    foreach (array_keys($modules) as $name) {
        if (!isset($modules[$name]['category'])) {
            $modules[$name]['category'] = _("Broken");
            $modules[$name]['name'] = $name;
        }
        if (isset($module_filter[$name]) && strtolower(trim($module_filter[$name])) == 'hidden') {
            continue;
        }

        // Theory: module is not in the defined repos, and since it is not local (meaning we loaded it at some point) then we
        //         don't show it. Exception, if the status is BROKEN then we should show it because it was here once.
        //
        if ((!isset($active_repos[$modules[$name]['repo']]) || !$active_repos[$modules[$name]['repo']])
            && $modules[$name]['status'] != MODULE_STATUS_BROKEN && !isset($modules_local[$name])) {
            continue;
        }

        // If versionupgrade module is present then allow it to skip modules that should not be presented
        // because an upgrade is in process. This can help assure only safe modules are present and
        // force the user to upgrade in the proper order.
        //
        if (function_exists('versionupgrade_allowed_modules') && !versionupgrade_allowed_modules($modules[$name])) {
            continue;
        }
        $numdisplayed++;

        if ($category !== $modules[$name]['category']) {
            // show category header

            if ($category !== false) {
                // not the first one, so end the previous blocks
                echo "\t</ul></div>\n";
            }

            // start a new category header, and associated html blocks
            $category = $modules[$name]['category'];
            echo "\t<div class=\"category\" id=\"category_".prep_id($category)."\"><h3>"._($category)."</h3>\n";
            echo "\t<ul>";
        }

        $loc_domain = $name;
        $name_text = modgettext::_($modules[$name]['name'], $loc_domain);

        echo "\t\t<li id=\"module_".prep_id($name)."\">\n";

        // ---- module header
        $salert = isset($modules[$name]['vulnerabilities']);
        if ($salert) {
            if(!is_array($modules[$name]['vulnerabilities']['vul'])) {
                $vuls = array($modules[$name]['vulnerabilities']['vul']);
            } else {
                $vuls = $modules[$name]['vulnerabilities']['vul'];
            }
            foreach ($vuls as $vul) {
                $security_issues_to_report[$vul] = true;
            }
        }
        $mclass = $salert ? "moduleheader has-background-warning" : "moduleheader";

        echo "\t\t<div class=\"columns $mclass\" onclick=\"toggleInfoPane('infopane_".prep_id($name)."');\" >\n";

        echo "\t\t\t<div class=\"column modulename\"><a href=\"javascript:void(null);\">".(!empty($name_text) ? $name_text : $name)."</a>";
        echo "</div>\n";

        echo "\t\t\t<div class=\"column moduleversion\">".(isset($modules[$name]['dbversion'])?$modules[$name]['dbversion']:'&nbsp;');
        if ($salert) {
            $tooltip = sprintf(_("Vulnerable, Requires: %s"), $modules[$name]['vulnerabilities']['minver']) ;
            if(!is_array($modules[$name]['vulnerabilities']['vul'])) {
                $vuls = array($modules[$name]['vulnerabilities']['vul']);
            } else {
                $vuls = $modules[$name]['vulnerabilities']['vul'];
            }
            echo "<a class='vulpopup' data-tooltip='$tooltip' data-sec='" . json_encode($vuls) . "'><i class='has-text-danger mx-2 fa fa-exclamation-triangle'></i></a>";
        }
        echo "</div>\n";

        echo "\t\t\t<div class=\"column modulepublisher\">".(isset($modules[$name]['publisher'])?$modules[$name]['publisher']:'&nbsp;')."</div>\n";

        echo "\t\t\t<div class=\"column modulestatus\">";

        switch ($modules[$name]['status']) {
        case MODULE_STATUS_NOTINSTALLED:
            if (isset($modules_local[$name])) {
                echo '<span class="notinstalled">'._('Not Installed (Locally available)').'</span>';
            } else {
                echo '<span class="notinstalled">'.sprintf(_('Not Installed (Available online: %s)'), $modules_online[$name]['version']).'</span>';
            }
            break;
        case MODULE_STATUS_DISABLED:
            if (isset($modules_online[$name]['version'])) {
                $vercomp = version_compare_issabel($modules_local[$name]['version'], $modules_online[$name]['version']);
                if ($vercomp < 0) {
                    echo '<span class="alert">'.sprintf(_('Disabled; Online upgrade available (%s)'),$modules_online[$name]['version']).'</span>';
                } else if ($vercomp > 0) {
                    echo sprintf(_('Disabled; Newer than online version (%s)'), $modules_online[$name]['version']);
                } else {
                    echo _('Disabled; up to date');
                }
            } else {
                echo '<span class="disabled">'._('Disabled').'</span>';
            }
            break;
        case MODULE_STATUS_NEEDUPGRADE:
            echo '<span class="alert">'.sprintf(_('Disabled; Pending upgrade to %s'),$modules_local[$name]['version']).'</span>';
            break;
        case MODULE_STATUS_BROKEN:
            echo '<span class="alert">'._('Broken').'</span>';
            break;
        default:
            // check for online upgrade
            if (isset($modules_online[$name]['version'])) {
                $vercomp = version_compare_issabel($modules_local[$name]['version'], $modules_online[$name]['version']);
                if ($vercomp < 0) {
                    echo '<span class="alert">'.sprintf(_('Online upgrade available (%s)'), $modules_online[$name]['version']).'</span>';
                } else if ($vercomp > 0) {
                    echo sprintf(_('Newer than online version (%s)'),$modules_online[$name]['version']);
                } else {
                    echo _('Enabled and up to date');
                }
            } else if (isset($modules_online)) {
                // we're connected to online, but didn't find this module
                echo _('Enabled; Not available online');
            } else {
                echo _('Enabled');
            }
            break;
        }
        echo "</div>\n";
        /*
        if ($salert) {
            echo "\t\t\t<div class=\"column modulevul\"><a class=\"modulevul_tag\" href=\"#\" data-sec='" . json_encode($modules[$name]['vulnerabilities']['vul']) . "'><img src=\"images/notify_security.png\" alt=\"\" width=\"16\" height=\"16\" border=\"0\" title=\"" .
                sprintf(_("Vulnerable to security issues %s"), implode(', ',$modules[$name]['vulnerabilities']['vul'])) .
                "\" /> " . sprintf(_("Vulnerable, Requires: %s"), $modules[$name]['vulnerabilities']['minver']) . "</a></div>\n";
        }
         */

        //			echo "\t\t\t<span class=\"clear\">&nbsp;</span>\n";
        echo "\t\t</div>\n";

        // ---- end of module header

        // ---- drop-down tab box thingy:

        echo "\t\t<div class=\"moduleinfopane\" id=\"infopane_".prep_id($name)."\" >\n";
        echo "\t\t\t<div class=\"tabber\">\n";

        if (isset($modules_online[$name]['attention']) && !empty($modules_online[$name]['attention'])) {
            echo "\t\t\t\t<div class=\"tabbertab\" title=\""._("Attention")."\">\n";
            echo nl2br(modgettext::_($modules[$name]['attention'], $loc_domain));
            echo "\t\t\t\t</div>\n";
        }

        echo "\t\t\t\t<div class=\"tabbertab actiontab\" title=\""._("Action")."\">\n";

        echo '<input type="radio" checked="CHECKED" id="noaction_'.prep_id($name).'" name="moduleaction['.prep_id($name).']" value="0" /> '.
            '<label for="noaction_'.prep_id($name).'">'._('No Action').'</label> <br />';
        switch ($modules[$name]['status']) {

        case MODULE_STATUS_NOTINSTALLED:
            if (!EXTERNAL_PACKAGE_MANAGEMENT) {
                if (isset($modules_local[$name])) {
                    echo '<input type="radio" id="install_'.prep_id($name).'" name="moduleaction['.prep_id($name).']" value="install" /> '.
                        '<label for="install_'.prep_id($name).'">'._('Install').'</label> <br />';
                } else {
                    echo '<input type="radio" id="upgrade_'.prep_id($name).'" name="moduleaction['.prep_id($name).']" value="downloadinstall" /> '.
                        '<label for="upgrade_'.prep_id($name).'">'._('Download and Install').'</label> <br />';
                }
            }
            break;
        case MODULE_STATUS_DISABLED:
            echo '<input type="radio" id="enable_'.prep_id($name).'" name="moduleaction['.prep_id($name).']" value="enable" /> '.
                '<label for="enable_'.prep_id($name).'">'._('Enable').'</label> <br />';
            if (!EXTERNAL_PACKAGE_MANAGEMENT) {
                echo '<input type="radio" id="uninstall_'.prep_id($name).'" name="moduleaction['.prep_id($name).']" value="uninstall" /> '.
                    '<label for="uninstall_'.prep_id($name).'">'._('Uninstall').'</label> <br />';
                if (isset($modules_online[$name]['version'])) {
                    $vercomp = version_compare_issabel($modules_local[$name]['version'], $modules_online[$name]['version']);
                    if ($vercomp < 0) {
                        echo '<input type="radio" id="upgrade_'.prep_id($name).'" name="moduleaction['.prep_id($name).']" value="upgrade" /> '.
                            '<label for="upgrade_'.prep_id($name).'">'.sprintf(_('Download %s, keep Disabled'),$modules_online[$name]['version']).'</label> <br />';
                    }
                }
            }
            break;
        case MODULE_STATUS_NEEDUPGRADE:
            if (!EXTERNAL_PACKAGE_MANAGEMENT) {
                echo '<input type="radio" id="install_'.prep_id($name).'" name="moduleaction['.prep_id($name).']" value="install" /> '.
                    '<label for="install_'.prep_id($name).'">'.sprintf(_('Upgrade to %s and Enable'),$modules_local[$name]['version']).'</label> <br />';

                if (isset($modules_online[$name]['version'])) {
                    $vercomp = version_compare_issabel($modules_local[$name]['version'], $modules_online[$name]['version']);
                    if ($vercomp < 0) {
                        echo '<input type="radio" id="upgrade_'.prep_id($name).'" name="moduleaction['.prep_id($name).']" value="upgrade" /> '.
                            '<label for="upgrade_'.prep_id($name).'">'.sprintf(_('Download and Upgrade to %s'), $modules_online[$name]['version']).'</label> <br />';
                    }
                }
                echo '<input type="radio" id="uninstall_'.prep_id($name).'" name="moduleaction['.prep_id($name).']" value="uninstall" /> '.
                    '<label for="uninstall_'.prep_id($name).'">'._('Uninstall').'</label> <br />';
            }
            break;
        case MODULE_STATUS_BROKEN:
            if (!EXTERNAL_PACKAGE_MANAGEMENT) {
                echo '<input type="radio" id="install_'.prep_id($name).'" name="moduleaction['.prep_id($name).']" value="install" /> '.
                    '<label for="install_'.prep_id($name).'">'._('Install').'</label> <br />';
                echo '<input type="radio" id="uninstall_'.prep_id($name).'" name="moduleaction['.prep_id($name).']" value="uninstall" /> '.
                    '<label for="uninstall_'.prep_id($name).'">'._('Uninstall').'</label> <br />';
            }
            break;
        default:
            // check for online upgrade
            if (isset($modules_online[$name]['version'])) {
                $vercomp = version_compare_issabel($modules_local[$name]['version'], $modules_online[$name]['version']);
                if (!EXTERNAL_PACKAGE_MANAGEMENT) {
                    if ($vercomp < 0) {
                        echo '<input type="radio" id="upgrade_'.prep_id($name).'" name="moduleaction['.prep_id($name).']" value="upgrade" /> '.
                            '<label for="upgrade_'.prep_id($name).'">'.sprintf(_('Download and Upgrade to %s'), $modules_online[$name]['version']).'</label> <br />';
                    } else {
                        $force_msg = ($vercomp == 0 ? sprintf(_('Force Download and Install %s'), $modules_online[$name]['version']) : sprintf(_('Force Download and Downgrade to %s'), $modules_online[$name]['version']));
                        echo '<input type="radio" id="force_upgrade_'.prep_id($name).'" name="moduleaction['.prep_id($name).']" value="force_upgrade" /> '.
                            '<label for="force_upgrade_'.prep_id($name).'">'.$force_msg.'</label> <br />';
                    }
                }
            }
            if (enable_option($name,'candisable')) {
                echo '<input type="radio" id="disable_'.prep_id($name).'" name="moduleaction['.prep_id($name).']" value="disable" /> '.
                    '<label for="disable_'.prep_id($name).'">'._('Disable').'</label> <br />';
            }
            if (!EXTERNAL_PACKAGE_MANAGEMENT && enable_option($name,'canuninstall')) {
                echo '<input type="radio" id="uninstall_'.prep_id($name).'" name="moduleaction['.prep_id($name).']" value="uninstall" /> '.
                    '<label for="uninstall_'.prep_id($name).'">'._('Uninstall').'</label> <br />';
            }
            break;
        }
        echo "\t\t\t\t</div>\n";

        echo "\t\t\t\t<div class=\"tabbertab\" title=\""._("Description")."\">\n";
        if (isset($modules[$name]['publisher'])) {
            echo "<div class='has-text-weight-semibold'>".sprintf(_("Publisher: %s"),$modules[$name]['publisher'])."</div>";
        }
        echo "<div class='has-text-weight-semibold'>".sprintf(_("License: %s"), (isset($modules[$name]['license'])?$modules[$name]['license']:"GPLv2") )."</div>";
        if ($salert) {
            echo "<div class='has-text-weight-semibold'>".sprintf(_("Fixes Vulnerabilities: %s"), implode(', ',$modules[$name]['vulnerabilities']['vul']))."</div>";
        }
        if (isset($modules[$name]['description']) && !empty($modules[$name]['description'])) {
            echo "<div class='has-text-weight-semibold'>".sprintf(_("Description for version %s"),$modules[$name]['version'])."</div>";
            echo "<div class='moddesc'>".nl2br(modgettext::_($modules[$name]['description'], $loc_domain))."</div>";
        } else {
            echo _("No description is available.");
        }
        if (isset($modules[$name]['info']) && !empty($modules[$name]['info'])) {
            echo '<p>'._('More info').': <a href="'.$modules[$name]['info'].'" target="_new">'.$modules[$name]['info'].'</a></p>';
        } else {
            echo '<p>'._('More info').': <a href="'."$issabelpbx_help_url&issabelpbx_module=".urlencode($name).'" target="help">'.sprintf(_("Get help for %s"),$name_text).'</a></p>';
        }
        echo "\t\t\t\t</div>\n";

        if (isset($modules[$name]['changelog']) && !empty($modules[$name]['changelog'])) {
            echo "\t\t\t\t<div class=\"tabbertab\" title=\""._("Changelog")."\">\n";
            echo "<div class='has-text-weight-semibold'>".sprintf(_("Change Log for version %s"), $modules[$name]['version'])."</div>";

            // convert "1.x.x:" and "*1.x.x*" into bold, and do nl2br
            // TODO: need to fix this to convert 1.x.xbetax.x, 1.x.xalphax.x, 1.x.xrcx.x, 1.x.xRCx.x formats as well
            //
            $changelog = nl2br($modules[$name]['changelog']);
            $changelog = preg_replace('/(\d+(\.\d+|\.\d+beta\d+|\.\d+alpha\d+|\.\d+rc\d+|\.\d+RC\d+)+):/', '<strong>$0</strong>', $changelog);
            $changelog = preg_replace('/\*(\d+(\.\d+|\.\d+beta\d+|\.\d+alpha\d+|\.\d+rc\d+|\.\d+RC\d+)+)\*/', '<strong>$1:</strong>', $changelog);

            // convert '#xxx', 'ticket xxx', 'bug xxx' to ticket links and rxxx to changeset links in trac
            //
            $changelog = preg_replace_callback('/(?<!\w)(?:#|bug |ticket )([^&]\d{3,4})(?!\w)/i', 'trac_replace_ticket', $changelog);
            $changelog = preg_replace_callback('/(?<!\w)r(\d+)(?!\w)/', 'trac_replace_changeset', $changelog);
            $changelog = preg_replace_callback('/(?<!\w)\[(\d+)\](?!\w)/', 'trac_replace_changeset', $changelog);

            echo $changelog;
            echo "\t\t\t\t</div>\n";
        }

        if ($amp_conf['DEVEL']) {
            echo "\t\t\t\t<div class=\"tabbertab\" title=\""._("Debug")."\">\n";
            echo "\t\t\t\t<h5>".$name."</h5><pre>\n";
            print_r(isset($modules_local[$name]) ? $modules_local[$name] : $name);
            echo "</pre>";
            if (isset($modules_online)) {
                echo "\t\t\t\t<h5>Online info</h5><pre>\n";
                print_r(isset($modules_online[$name]) ? $modules_online[$name] : $name);
                echo "</pre>\n";
            }
            echo "\t\t\t\t<h5>combined</h5><pre>\n";
            print_r($modules[$name]);
            echo "</pre>\n";
            echo "\t\t\t\t</div>\n";
        }

        echo "\t\t\t</div>\n";
        echo "\t\t</div>\n";

        // ---- end of drop-down tab box

        echo "\t\t</li>\n";
    }

    if ($numdisplayed == 0) {
        if (isset($modules_online) && count($modules_online) > 0) {
            echo _("All available modules are up-to-date and installed.");
        } else {
            echo _("No modules to display.");
        }
    }

    echo "\t</ul></div>\n";
    echo "</div>";

    echo "<div class='xmodulebuttons' id='action-bar'>";
    echo "<div id='action-buttons'>";
    echo '<a id="collapseactionmenuicon" class="action_menu_icon"><i class="fa fa-angle-double-right"></i></a>';
    if ($online) {
        echo "\t<a class='button is-small is-rounded' href=\"javascript:void(null);\" onclick=\"check_download_all();\">"._("Download all")."</a>";
        echo "\t<a class='button is-small is-rounded' href=\"javascript:void(null);\" onclick=\"check_upgrade_all();\">"._("Upgrade all")."</a>";
    }
    echo "\t<input class='button is-small is-rounded' type=\"reset\" value=\""._("Reset")."\" />";
    echo "\t<input class='button is-small is-rounded' type=\"submit\" value=\""._("Process")."\" name=\"process\" />";
    echo "</div>";
    echo "</div>";

    echo "</form>";
    break;
}
if (!empty($security_issues_to_report)) {
    foreach (array_keys($security_issues_to_report) as $id) {

		if (!is_array($security_array[$id]['related_urls']['url'])) {
			$security_array[$id]['related_urls']['url'] = array($security_array[$id]['related_urls']['url']);
		}
		//$tickets = preg_replace_callback('/(?<!\w)(?:#|bug |ticket )([^&]\d{3,4})(?!\w)/i', 'trac_replace_ticket', $security_array[$id]['tickets']);
?>
<div class="module_security_description" id="security-<?php echo $id ?>" style="display: none;">
<table>
<tr><td><?php echo _('ID') ?></td><td><?php echo $id ?></td></tr>
<tr><td><?php echo _('Type') ?></td><td><?php echo $security_array[$id]['type'] ?></td></tr>
<tr><td><?php echo _('Severity') ?></td><td><?php echo _($security_array[$id]['severity']) ?></td></tr>
<tr><td><?php echo _('Description') ?></td><td><?php echo $security_array[$id]['description'] ?></td></tr>
<tr><td><?php echo _('Date Reported') ?></td><td><?php echo $security_array[$id]['reported'] ?></td></tr>
<tr><td><?php echo _('Date Fixed') ?></td><td><?php echo $security_array[$id]['fixed'] ?></td></tr>
<?php
		$related_urls = count($security_array[$id]['related_urls']['url']) == 1 ? _("Related URL") : _("Related URLs");
		foreach ($security_array[$id]['related_urls']['url'] as $url) {
?>
<tr><td><?php echo $related_urls ?></td><td><a href="<?php echo $url ?>" target="_security"><?php echo $url ?></a></td></tr>
<?php
			$related_urls = '';
		}
?>
<tr><td><?php echo _('Related Tickets') ?></td><td><?php echo $tickets ?></td></tr>
</table>
</div>
<?php
	}
}

if ($quietmode) {
	echo '</body></html>';
}

//-------------------------------------------------------------------------------------------
// Help functions
//

function category_sort_callback($a, $b) {
	if (!isset($a['category']) || !isset($b['category'])) {
		if (!isset($a['name']) || !isset($b['name'])) {
			return 0;
		} else {
			return strcmp($a['name'], $b['name']);
		}
	}
	// sort by category..
	$catcomp = strcmp($a['category'], $b['category']);
	if ($catcomp == 0) {
		// .. then by name
		return strcmp($a['name'], $b['name']);
	} elseif ($a['category'] == 'Basic') {
			return -1;
	} elseif ($b['category'] == 'Basic') {
		return 1;
	} else {
		return $catcomp;
	}
}

/** preps a string to use as an HTML id element
 */
function prep_id($name) {
	return preg_replace("/[^a-z0-9-]/i", "_", $name);
}

/** Progress callback used by module_download()
 */
function download_progress($action, $params) {
	switch ($action) {
		case 'untar':
			echo '<script type="text/javascript">
			        var txt = document.createTextNode("'._('Untarring..').'");
			        var br = document.createElement(\'br\');
			        document.getElementById(\'moduleprogress\').appendChild(br);
					document.getElementById(\'moduleprogress\').appendChild(txt);
			     </script>';
			@ ob_flush();
			flush();
		break;
		case 'downloading':
			if ($params['total']==0) {
				$progress = $params['read'].' of '.$params['total'].' (0%)';
			} else {
				$progress = $params['read'].' of '.$params['total'].' ('.round($params['read']/$params['total']*100).'%)';
			}
			echo '<script type="text/javascript">
			        document.getElementById(\'downloadprogress_'.$params['module'].'\').innerHTML = \''.$progress.'\';
			      </script>';
			@ ob_flush();
			flush();
		break;
		case 'done';
			echo '<script type="text/javascript">
			        var txt = document.createTextNode("'._('Done.').'");
					var br = document.createElement(\'br\');
			        document.getElementById(\'moduleprogress\').appendChild(txt);
					document.getElementById(\'moduleprogress\').appendChild(br);
			     </script>';
			@ ob_flush();
			flush();
		break;
	}
}

/* enable_option($module_name, $option)
   This function will return false if the particular option, which is a module xml tag,
	 is set to 'no'. It also provides for some hardcoded overrides on critical modules to
	 keep people from editing the xml themselves and then breaking their the system.
*/
function enable_option($module_name, $option) {
	global $modules;

	$enable=true;
	$override = array('core'      => array('candisable' => 'no',
	                                       'canuninstall' => 'no',
					                              ),
	                  'framework' => array('candisable' => 'no',
	                                       'canuninstall' => 'no',
																			  ),
	                 );
	if (isset($modules[$module_name][$option]) && strtolower(trim($modules[$module_name][$option])) == 'no') {
		$enable=false;
	}
	if (isset($override[$module_name][$option]) && strtolower(trim($override[$module_name][$option])) == 'no') {
		$enable=false;
	}
	return $enable;
}

/* Replace '#nnn', 'bug nnn', 'ticket nnn' type ticket numbers in changelog with a link, taken from Greg's drupal filter
*/
function trac_replace_ticket($match) {
  $baseurl = 'http://issabel.org/trac/ticket/';
  return '<a target="tractickets" href="'.$baseurl.$match[1].'" title="ticket '.$match[1].'">'.$match[0].'</a>';
}

/* Replace 'rnnn' changeset references to a link, taken from Greg's drupal filter
*/
function trac_replace_changeset($match) {
  $baseurl = 'http://issabel.org/trac/changeset/';
  return '<a target="tractickets" href="'.$baseurl.$match[1].'" title="changeset '.$match[1].'">'.$match[0].'</a>';
}

function pageReload(){
	return "";
	//return "<script language=\"Javascript\">document.location='".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."&foo=".rand()."'</script>";
}

function displayRepoSelect($buttons,$modify_notification='') {
  global $display, $type, $online, $tabindex;
  global $active_repos;

  $standard_repo = true;
  $extended_repo = true;
  $unsupported_repo = false;
  $commercial_repo = true;

  $button_display = '';
  $href = "config.php?display=$display&type=$type";
  $button_template = '<input type="button" class="button is-rounded is-small" value="%s" onclick="location.href=\'%s\';" />'."\n";

  foreach ($buttons as $button) {
    switch($button) {
    case 'local':
      $button_display .= sprintf($button_template, _("Manage local modules"), $href);
    break;
    case 'upload':
      $button_display .= sprintf($button_template, _("Upload modules"), $href.'&extdisplay=upload');
    break;
    }
  }

  $tooltip  = _("Choose the repositories that you want to check for new modules. Any updates available for modules you have on your system will be detected even if the repository is not checked. If you are installing a new system, you may want to start with the Basic repository and update all modules, then go back and review the others.").' ';
  $tooltip .= _(" The modules in the Extended repository are less common and may receive lower levels of support. The Unsupported repository has modules that are not supported by the IssabelPBX team but may receive some level of support by the authors.").' ';
  $tooltip .= _("The Commercial repository is reserved for modules that are available for purchase and commercially supported.").' ';
  $tooltip .= '<br /><br /><small><i>('._("Checking for updates will transmit your IssabelPBX, Distro, Asterisk and PHP version numbers along with a unique but random identifier. This is used to provide proper update information and track version usage to focus development and maintenance efforts. No private information is transmitted.").')</i></small>';
?>
  <form name="onlineRepo" action="config.php" method="post">
    <input type="hidden" name="display" value="<?php echo $display ?>"/>
    <input type="hidden" name="type" value="<?php echo $type ?>"/>
    <input type="hidden" name="online" value="<?php echo $online ?>"/>

          <div class='columns'>
              <div class='column'>
          <?php echo ipbx_label(_("Repositories"), $tooltip); ?>
<?php

  $repos = array();
  $repos[] = array('id'=>'standard','name'=>"active_repos['standard']'", 'value'=>'1','label'=>_("Basic"));
  $repos[] = array('id'=>'extended','name'=>"active_repos['extended']'", 'value'=>'1','label'=>_("Extended"));
  $repos[] = array('id'=>'unsupported','name'=>"active_repos['unsupported']'", 'value'=>'1','label'=>_("Unsupported"));
  $repos[] = array('id'=>'commercial','name'=>"active_repos['commercial']'", 'value'=>'1','label'=>_("Commercial"));

  foreach($repos as $idx=>$repo) {
      $checked = isset($active_repos[$repo['id']])?' checked="checked" ':'';
      echo "
<span class='control'>
    <label class='is-checkbox is-small is-link is-rounded'>
        <input id='${repo['id']}_repo' tabindex='".++$tabindex."' $checked value=1 type='checkbox'>
        <span  class='icon is-small checkmark'>
            <i class='fa fa-check'></i>
        </span>
        <span>${repo['label']}</span>
    </label>
</span>
";

  }

 echo $modify_notification;
?>

              </div>
          </div>
    <input type="submit" class="button is-small is-rounded" value="<?php echo _("Check Online") ?>" name="check_online" />
    <?php echo $button_display ?>
  </form>
<?php
}
?>
