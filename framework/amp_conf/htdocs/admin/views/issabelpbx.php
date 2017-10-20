<?php
/** Main IssabelPBX view - sets up the base HTML page, and IssabelPBX header
 */
// BRANDABLE COMPONENTS
//

// get version info to be used to version images, css, etc.
//


?>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js"></script>
<script type="text/javascript" >window.jQuery.ui || document.write('<script src="assets/js/jquery-ui-1.8.x.min.js"><\/script>')</script>
<?php
if (isset($amp_conf['DEVEL']) && $amp_conf['DEVEL']) {
	$benchmark_time = number_format(microtime_float() - $benchmark_starttime, 4);
	echo '<div id="benchmark_time">Page loaded in ' . $benchmark_time . 's</div>';
}
// Production versions should include the packed consolidated javascript library but if it
// is not present (useful for development, then include each individual library below
if ($amp_conf['USE_PACKAGED_JS'] && file_exists("assets/js/pbxlib.js")) {
	$pbxlibver = '.' . filectime("assets/js/pbxlib.js");
	$html .= '<script type="text/javascript" src="assets/js/pbxlib.js'
			. $version_tag . $pbxlibver . '"></script>';
} else {
	/*
	 * files below:
	 * jquery.cookie.js - for setting cookies
	 * jquery.toggleval.3.0.js - similar to html5 form's placeholder. depreciated
	 * interface.dim.js - interface blocking (reload, modadmin)
	 * tabber-minimized.js - sed for module admin (hiding content)
	 */
	echo ' <script type="text/javascript" src="assets/js/menu.js' . $version_tag . '"></script>'
	 	. '<script type="text/javascript" src="assets/js/jquery.cookie.js' . $version_tag . '"></script>'
	 	. '<script type="text/javascript" src="assets/js/script.legacy.js' . $version_tag . '"></script>'
	 	. '<script type="text/javascript" src="assets/js/jquery.toggleval.3.0.js' . $version_tag . '"></script>'
	 	. '<script type="text/javascript" src="assets/js/interface.dim.js' . $version_tag . '"></script>'
	 	. '<script type="text/javascript" src="assets/js/tabber-minimized.js' . $version_tag . '"></script>';
}

if (isset($module_name) && $module_name != '') {
	echo framework_include_js($this_time_append, $version_tag);
}
?>
