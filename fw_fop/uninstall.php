<?php
/*
 * Mimic the amportal code that stops FOP server. If we don't do it here, then once
 * uninstalled amportal will no longer stop FOP since it is invoked with a hook which
 * will be gone.
 *
	if [ "$pid_length" != "0" -a "$pid_length" != "" ]
	then
 		ps -ef | grep safe_opserver | grep -v grep | awk '{print $2}' | xargs kill
		killall op_server.pl
		echo "FOP Server Stopped"
	fi
 */
global $amp_conf;

$pidof = fpbx_which("pidof");
if ($pidof === false) {
	$pidof = "/sbin/pidof";
}
$awk = fpbx_which("awk");
$ps = fpbx_which("ps");
$grep = fpbx_which("grep");
$xargs = fpbx_which("xargs");
$echo = fpbx_which("echo");
$kill = fpbx_which("kill");
$killall = fpbx_which("killall");

outn("Checking FOP servers status..");
$pid_length = trim(`$pidof -x op_server.pl | $awk '{print length($0)}'`);
if ($pid_length) {
	out("running got $pid_length");
	outn("Trying to stop safe_opserver..");
	$kill_args = trim(`$ps -ef | $grep safe_opserver | $grep -v grep | $awk '{print $2}' | $xargs $echo`);
	outn("processes $kill_args..");
	exec("$kill $kill_args", $kill_arr, $ret);
	if (!$ret) {
		out("stopped");
		outn("trying to stop op_server.pl..");
		exec("$killall op_server.pl", $killall_arr, $ret);
		if (!$ret) {
			out("stopped");
		} else {
			out("failed");
			out("you may need to reboot the server to stop the FOP services");
		}
	} else {
		out("failed");
		out("you may need to reboot the server to stop the FOP services");
	}
} else {
	out("not running");
}

// TODO: remove the symlink created by retrieve_conf. Probably need to look at
//       modifying module_admin so this is done automatically when a module is
//       uninstalled and probably even disabled.
//
//$hook = $amp_conf['ASTVARLIBDIR'] . "/issabelpbx_engine_hook_fw_fop";
$hook = $amp_conf['AMPBIN'] . "/issabelpbx_engine_hook_fw_fop";
if (is_link($hook) || is_file($hook)) {
	outn("removing issabelpbx_engine_hook_fw_fop..");
	if (unlink($hook)) {
		out("removed");
	} else {
		out("failed to remove");
		out("ERROR: The following symlink/file must be removed: $hook");
	}
} else {
	out("$hook is not there");
}
