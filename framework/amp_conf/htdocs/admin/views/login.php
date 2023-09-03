<?php
$html = '';
//$html .= heading(__('Welcome!'), 3) . '<hr class="backup-hr"/>';
if ($errors) {
    $html.="
       <script>
         document.addEventListener('DOMContentLoaded', function(){
           Toast.fire({
             icon: 'error',
             title: '${errors[0]}'
           });
         });
       </script>";
}
$html .= '<div id="login_form">';
$html .= form_open('config.php', 'id="loginform"');
$html .= __('To get started, please enter your credentials:');
$html .= br(2);
$data = array(
			'name' => 'username',
			'placeholder' => __('username')
		);
$html .= form_input($data);
$html .= br(2);
$data = array(
			'name' => 'password',
			'type' => 'password',
			'placeholder' => __('password')
		);
$html .= form_input($data);
$html .= br(2);
//$html .= form_submit('submit', __('Login'));
//$html .= br(2);
$html .= form_close();
$html .= '</div>';
$html .= '<div id="login_icon_holder">';
$html .= '<div class="login_item_title"><a href="#" class="login_item" id="login_admin" style="background-image: url(assets/images/sys-admin.png);"/>&nbsp</a><span class="login_item_text" style="display: block;width: 160px;text-align: center;">' . __('IssabelPBX Administration') . '</span></div>';

/*
$html .= '<div class="login_item_title"><a href="/recordings" '
                . 'class="login_item" id="login_ari" style="background-image: url(assets/images/user-control.png);"/>&nbsp</a><span class="login_item_text" style="display: block;width: 160px;text-align: center;">' . __('User Control Panel') . '</span></div>';
 */
if ($panel) {
    $html .= '<div class="login_item_title"><a href="' . $panel . '" '
		    . 'class="login_item" id="login_fop" style="background-image: url(assets/images/operator-panel.png);"/>&nbsp</a><span class="login_item_text" style="display: block;width: 160px;text-align: center;">' . __('Operator Panel') . '</span></div>';
}
$html .= '<div class="login_item_title"><a href="http://www.issabel.com" '
		. 'class="login_item" id="login_support" style="background-image: url(assets/images/support.png);"/>&nbsp</a><span class="login_item_text" style="display: block;width: 160px;text-align: center;">' . __('Get Support') . '</span></div>';
$html .= '<div></div>';
$html .= '</div>';
$html .= br(1) . '<div id="key" style="color: white;font-size:small">'
	  . session_id()
	  . '</div>';
$html .= '<script src="assets/js/views/login.js"></script>';

echo $html;

?>
