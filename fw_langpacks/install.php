<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
// HELPER FUNCTIONS:

function fw_langpacks_print_errors($src, $dst, $errors) {
	out("error copying files:");
	out(sprintf(_("'cp -ru' from src: '%s' to dst: '%s'...details follow"), $src, $dst));
	issabelpbx_log(IPBX_LOG_ERROR, sprintf(_("fw_langpacks couldn't copy file to %s"),$dst));
	foreach ($errors as $error) {
		out("$error");
		issabelpbx_log(IPBX_LOG_ERROR, _("cp error output: $error"));
	}
}
global $amp_conf;

$debug = false;
$dryrun = false;

/** verison_compare that works with IssabelPBX version numbers
 *  included here because there are some older versions of functions.inc.php that do not have
 *  it included as it was added during 2.3.0beta1
 */
if (!function_exists('version_compare_issabel')) {
	function version_compare_issabel($version1, $version2, $op = null) {
		$version1 = str_replace("rc","RC", strtolower($version1));
		$version2 = str_replace("rc","RC", strtolower($version2));
		if (!is_null($op)) {
			return version_compare($version1, $version2, $op);
		} else {
			return version_compare($version1, $version2);
		}
	}
}

/*
 * fw_langpacks install script
 *
 * for each installed component on the target system, copy localization files using the -u option
 * on copy which will only copy them if our copy is newer then the destination which protects
 * from overwriting destination files that have been updated by the user.
 */
	$htdocs_source = dirname(__FILE__)."/mo";
	$htdocs_dest = $amp_conf['AMPWEBROOT'];

	if (!file_exists($htdocs_source)) {
    out(sprintf(_("No directory %s, install script not needed"),$htdocs_source));
    return true;
  }

	foreach(glob(dirname(__FILE__).'/mo/*',GLOB_ONLYDIR) as $language) {
		$lang = basename($language);
		foreach(glob($language.'/*.mo') as $mo) {
			$modinfo = pathinfo($mo);
			$module = ($modinfo['filename'] == 'amp') ? 'framework' : $modinfo['filename'];
			if($module != 'framework') {
				$i18n = $htdocs_dest."/admin/modules/".$module."/i18n";
				if(!file_exists($htdocs_dest."/admin/modules/".$module)) {
					continue;
				}
			} else {
				$i18n = $htdocs_dest."/admin/i18n";
			}
			if(!file_exists($i18n."/".$lang."/LC_MESSAGES")) {
				mkdir($i18n."/".$lang."/LC_MESSAGES",0777,true);
			}

			exec("cp -ru ".$mo." ".$i18n."/".$lang."/LC_MESSAGES/".basename($mo)." 2>&1",$out,$ret);
			if ($ret != 0) {
				fw_langpacks_print_errors($mo, $i18n."/".$lang."/LC_MESSAGES/".basename($mo), $out);
			} else {
				out(sprintf(_("Updated %s"),basename($mo)));
			}
		}
	}

	// We now delete the files, this makes sure that if someone had an unprotected system where they have not enabled
	// the .htaccess files or otherwise allowed direct access, that these files are not around to possibly cause problems
	//
	out(_("fw_langpacks file install done, removing packages from module"));
	unset($out);
	exec("rm -rf $htdocs_source 2>&1",$out,$ret);
	if ($ret != 0) {
		out(_("an error occured removing the packaged files"));
	} else {
		out(_("files removed successfully"));
	}
