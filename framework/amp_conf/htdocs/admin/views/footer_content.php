<?php
global $amp_conf;
$html = '';
$version	 = get_framework_version();
$version_tag = '?load_version=' . urlencode($version);
if ($amp_conf['FORCE_JS_CSS_IMG_DOWNLOAD']) {
  $this_time_append	= '.' . time();
  $version_tag 		.= $this_time_append;
} else {
	$this_time_append = '';
}


// Brandable logos in footer
//ipbx logo
/*
$html .= '<a target="_blank" href="' 
		. $amp_conf['BRAND_IMAGE_ISSABELPBX_LINK_FOOT']
		. '" class ="footer-float-left">'
 	 	. '<img id="footer_logo1" src="'.$amp_conf['BRAND_IMAGE_ISSABELPBX_FOOT'].$version_tag
		. '" alt="'.$amp_conf['BRAND_ISSABELPBX_ALT_FOOT'] .'"/></a>';
*/
//text
$html .= '<span class="footer-float-left" id="footer_text">';
$html .= '<a href="http://www.issabel.org" target="_blank">IssabelPBX</a> ' . br();
$html .= _('IssabelPBX') . ' ' . $version . ' ' . _('is licensed under the')
		. '<a href="http://www.gnu.org/copyleft/gpl.html" target="_blank"> GPL</a>' . br();
$html .= '<a href="http://www.issabel.org/copyright.html" target="_blank">Copyright&copy; 2004-'.date('Y',time()).'</a>';

//module license
if (!empty($active_modules[$module_name]['license'])) {
  $html .= br() . sprintf(_('Current module licensed under %s'),
  trim($active_modules[$module_name]['license']));
}

//benchmarking
if (isset($amp_conf['DEVEL']) && $amp_conf['DEVEL']) {
	$benchmark_time = number_format(microtime_float() - $benchmark_starttime, 4);
	$html .= '<br><span id="benchmark_time">Page loaded in ' . $benchmark_time . 's</span>';
}
$html .= '</span>';
/*
$html .= '<a target="_blank" href="' . $amp_conf['BRAND_IMAGE_SPONSOR_LINK_FOOT'] 
		. '" class="footer-float-left">'
		. '<img id="footer_logo" src="' . $amp_conf['BRAND_IMAGE_SPONSOR_FOOT'] . '" '
		. 'alt="' . $amp_conf['BRAND_SPONSOR_ALT_FOOT'] . '"/></a>';
*/
echo $html;
?>
