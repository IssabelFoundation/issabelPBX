<?php
$version			= get_framework_version();
$version_tag		= '?load_version=' . urlencode($version);
if ($amp_conf['FORCE_JS_CSS_IMG_DOWNLOAD']) {
	$this_time_append	= '.' . time();
	$version_tag 		.= $this_time_append;
} else {
	$this_time_append = '';
}

//html head
$html = '';
$html .= '<!DOCTYPE html>';
$html .= '<html>';
$html .= '<head>';
$html .= '<title>'
		. (isset($title) ? _($title) : $amp_conf['BRAND_TITLE'])
		. '</title>';

$html .= '<meta http-equiv="Content-Type" content="text/html;charset=utf-8">'
		. '<meta http-equiv="X-UA-Compatible" content="chrome=1">'
		. '<meta name="robots" content="noindex" />'
		. '<link rel="shortcut icon" href="' . $amp_conf['BRAND_IMAGE_FAVICON'] . '">';
//css
$mainstyle_css      = $amp_conf['BRAND_CSS_ALT_MAINSTYLE'] 
                       ? $amp_conf['BRAND_CSS_ALT_MAINSTYLE'] 
                       : 'assets/css/mainstyle.css';
$framework_css = ($amp_conf['DISABLE_CSS_AUTOGEN'] || !file_exists($amp_conf['mainstyle_css_generated'])) ? $mainstyle_css : $amp_conf['mainstyle_css_generated'];
$css_ver = '.' . filectime($framework_css);
$html .= '<link href="' . $framework_css.$version_tag.$css_ver . '" rel="stylesheet" type="text/css">';

//include jquery-ui css
if ($amp_conf['DISABLE_CSS_AUTOGEN'] == true) {
	$html .= '<link href="' . $amp_conf['JQUERY_CSS'] . $version_tag . '" rel="stylesheet" type="text/css">';
}
//add the popover.css stylesheet if we are displaying a popover to override mainstyle.css styling
if ($use_popover_css) {
	$popover_css = $amp_conf['BRAND_CSS_ALT_POPOVER'] ? $amp_conf['BRAND_CSS_ALT_POPOVER'] : 'assets/css/popover.css';
	$html .= '<link href="' . $popover_css.$version_tag . '" rel="stylesheet" type="text/css">';
}

//include rtl stylesheet if using a rtl langauge
if (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], array('he_IL'))) {
	$html .= '<link href="assets/css/mainstyle-rtl.css" rel="stylesheet" type="text/css" />';
}
// Insert a custom CSS sheet if specified (this can change what is in the main CSS)
if ($amp_conf['BRAND_CSS_CUSTOM']) {
	$html .= '<link href="' . $amp_conf['BRAND_CSS_CUSTOM'] 
			. $version_tag . '" rel="stylesheet" type="text/css">';
}

//it seems extremely difficult to put jquery in the footer with the other scripts
if ($amp_conf['USE_GOOGLE_CDN_JS']) {
	$html .= '<script src="//ajax.googleapis.com/ajax/libs/jquery/' . $amp_conf['JQUERY_VER'] . '/jquery.min.js"></script>';
	$html .= '<script>window.jQuery || document.write(\'<script src="assets/js/jquery-' . $amp_conf['JQUERY_VER'] . '.min.js"><\/script>\')</script>';
} else {
	$html .= '<script type="text/javascript" src="assets/js/jquery-' . $amp_conf['JQUERY_VER'] . '.min.js"></script>';
}
		
$html .= '</head>';

//open body
$html .= '<body>';

$html .= '<div id="page">';//open page

//add script warning
$html .= '<noscript><div class="attention">'
		. _('WARNING: Javascript is disabled in your browser. '
		. 'The IssabelPBX administration interface requires Javascript to run properly. '
		. 'Please enable javascript or switch to another  browser that supports it.') 
		. '</div></noscript>';

echo $html;
