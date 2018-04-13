<?php
$html = '';
//$html .= heading(_('Welcome!'), 3) . '<hr class="backup-hr"/>';
if ($errors) {
	$html .= '<span class="obe_error">';
	$html .= _('Please correct the following errors:');
	$html .= ul($errors);
	$html .= '</span>';
}
$html .= '<div id="login_form">';
$html .= form_open('config.php', 'id="loginform"');
$html .= _('To get started, please enter your credentials:');
$html .= br(2);
$data = array(
			'name' => 'username',
			'placeholder' => _('username')
		);
$html .= form_input($data);
$html .= br(2);
$data = array(
			'name' => 'password',
			'type' => 'password',
			'placeholder' => _('password')
		);
$html .= form_input($data);
$html .= br(2);
//$html .= form_submit('submit', _('Login'));
//$html .= br(2);
$html .= form_close();
$html .= '</div>';
$html .= '<div id="login_icon_holder">';
$html .= '<div class="login_item_title"><a href="#" class="login_item" id="login_admin" style="background-image: url(assets/images/sys-admin.png);"/>&nbsp</a><span class="login_item_text" style="display: block;width: 160px;text-align: center;">' . _('IssabelPBX Administration') . '</span></div>';

/*
$html .= '<div class="login_item_title"><a href="/recordings" '
                . 'class="login_item" id="login_ari" style="background-image: url(assets/images/user-control.png);"/>&nbsp</a><span class="login_item_text" style="display: block;width: 160px;text-align: center;">' . _('User Control Panel') . '</span></div>';
 */
if ($panel) {
    $html .= '<div class="login_item_title"><a href="' . $panel . '" '
		    . 'class="login_item" id="login_fop" style="background-image: url(assets/images/operator-panel.png);"/>&nbsp</a><span class="login_item_text" style="display: block;width: 160px;text-align: center;">' . _('Operator Panel') . '</span></div>';
}
$html .= '<div class="login_item_title"><a href="http://www.issabel.com" '
		. 'class="login_item" id="login_support" style="background-image: url(assets/images/support.png);"/>&nbsp</a><span class="login_item_text" style="display: block;width: 160px;text-align: center;">' . _('Get Support') . '</span></div>';
$html .= '<div></div>';
$html .= '</div>';
$html .= br(5) . '<div id="key" style="color: white;font-size:small">'
	  . session_id()
	  . '</div>';

/*$html .= '<script type="text/javascript">';
$html .= '$(document).ready(function(){
		$("#key").click(function(){
			dest = "ssh://" + window.location.hostname + " \"/usr/sbin/amportal a u ' . session_id() . '\"";
			console.log(dest)
			window.open(dest).close(); setTimeout(\'window.location.reload()\', 3000);
		});
})';
$html .= '</script>';*/

$html .= '<script type="text/javascript" src="assets/js/views/login.js"></script>';

echo $html;

?>
