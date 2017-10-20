<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
// HELPER FUNCTIONS:

function fw_ari_print_errors($src, $dst, $errors) {
	out("error copying files:");
	out(sprintf(_("'cp -rf' from src: '%s' to dst: '%s'...details follow"), $src, $dst));
	issabelpbx_log(FPBX_LOG_ERROR, sprintf(_("fw_ari couldn't copy file to %s"),$dst));
	foreach ($errors as $error) {
		out("$error");
		issabelpbx_log(FPBX_LOG_ERROR, _("cp error output: $error"));
	}
}

global $amp_conf;
global $asterisk_conf;

$debug = false;
$dryrun = false;

/** verison_compare that works with freePBX version numbers
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
 * fw_ari install script
 */
$htdocs_ari_source = $amp_conf['AMPWEBROOT']."/admin/modules/fw_ari/htdocs_ari/*";
$htdocs_ari_dest = $amp_conf['AMPWEBROOT']."/recordings";

if (!file_exists(dirname($htdocs_ari_source))) {
	out(sprintf(_("No directory %s, install script not needed"),dirname($htdocs_ari_source)));
	return true;
}

$msg = _("installing files to %s..");

// TODO: for some reason the .htaccess is not being copied with the rest????
$src_file[] = $htdocs_ari_source;
$src_file[] = dirname($htdocs_ari_source) . "/.htaccess";
foreach ($src_file as $src) {
	outn(sprintf($msg, $htdocs_ari_dest));
	$out = array();
	exec("cp -rf $src $htdocs_ari_dest 2>&1",$out,$ret);
	if ($ret != 0) {
		fw_ari_print_errors($src, $htdocs_ari_dest, $out);
		out(_("done, see errors below"));
	} else {
		out(_("done"));
	}
}
// Make sure that libissabelpbx.javascripts.js is available to ARI
$libissabelpbx = $amp_conf['AMPWEBROOT'].'/admin/common/libissabelpbx.javascripts.js';
$dest_libissabelpbx = $htdocs_ari_dest.'/theme/js/libissabelpbx.javascripts.js'; 
if (file_exists($libissabelpbx) && !file_exists($dest_libissabelpbx)) {
	outn(_("linking libissabelpbx.javascripts.js to theme/js.."));
	if (link($libissabelpbx, $dest_libissabelpbx)) {
		out(_("ok"));
	} else {
		out(_("possible error - check warning message"));
	}
}

// We now delete the files, this makes sure that if someone had an unprotected system where they have not enabled
// the .htaccess files or otherwise allowed direct access, that these files are not around to possibly cause problems
//
out(_("fw_ari file install done, removing packages from module"));
unset($out);
exec("rm -rf $htdocs_ari_source 2>&1",$out,$ret);

if ($ret != 0) {
	out(_("an error occured removing the packaged files"));
} else {
	out(_("files removed successfully"));
}

//remove userpaneltab as fw_ari is a module now

$installed_status = array(MODULE_STATUS_ENABLED, MODULE_STATUS_DISABLED);
$module_name = 'userpaneltab';
$userpaneltab_module = module_getinfo($module_name, $installed_status);
if (isset($userpaneltab_module[$module_name])) {
	module_delete($module_name,true);
	out(sprintf(_("Uninstalling outdated %s module..."), $module_name));
}