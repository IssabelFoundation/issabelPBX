<?php
$html = '';
$html .= heading(_('Welcome to ') . $amp_conf['BRAND_TITLE'] . '!', 3) . '<hr/>';
//$html .= '<div id="login_form">';
$html .= form_open($_SERVER['REQUEST_URI'], 'id="loginform"');


$html .= heading(_('Initial setup'), 5) . '<hr/>';
$html .= _('Please provide the core credentials that will be used to '
        . 'administer your system');
$html .= br(2);
$table = new CI_Table;
if ($errors) {
	$html .= '<span class="obe_error">';
	$html .= _('Please correct the following errors:');
	$html .= ul($errors);
	$html .= '</span>';
}
//username
$label = fpbx_label(_('Username'), _('Admin user name'));
$data = array(
			'name' => 'username',
			'value'	=> $username,
			'required' => '',
			'placeholder' => _('username')
		);
$table->add_row($label, form_input($data));

//password
$label = fpbx_label(_('Password'), _('Admin password'));
$data = array(
			'name' => 'password',
			'type' => 'password',
			'value'	=> $password,
			'required' => '',
			'placeholder' => _('password')
        );

$table->add_row($label, form_input($data));

//confirm password
$label = fpbx_label(_('Confirm Password'));
$data = array(
			'name' => 'confirm_password',
			'value'	=> $confirm_password,
			'type' => 'password',
			'required' => '',
			'placeholder' => _('password')
        );

$table->add_row($label, form_input($data));

//email address
$label = fpbx_label(_('Admin Email address'));
$data = array(
			'name' 	=> 'email_address',
			'value'	=> $email_address,
			'type'	=> 'email',
			'placeholder' => _('email address')
        );

$table->add_row($label, form_input($data));

//Confirm email address
$label = fpbx_label(_('Confirm Email address'));
$data = array(
			'name' => 'confirm_email',
			'value'	=> $confirm_email,
			'type'	=> 'email',
			'placeholder' => _('confirm email')
        );

$table->add_row($label, form_input($data));

$html .= $table->generate();
$html .= br(5);
$html .= form_hidden('action', 'setup_admin');
$html .= form_submit('submit', _('Set up my Account'));
$html .= form_close();

/*$html .= '<script type="text/javascript">';
$html .= '$(document).ready(function(){
		$("#key").click(function(){
			dest = "ssh://" + window.location.hostname + " \"/usr/sbin/amportal a u ' . session_id() . '\"";
			console.log(dest)
			window.open(dest).close(); setTimeout(\'window.location.reload()\', 3000);
		});
})';
$html .= '</script>';*/
echo $html;

?>
