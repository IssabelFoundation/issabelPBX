<?php
$html = '';
$html .= heading(__('Welcome to ' . $amp_conf['BRAND_TITLE'].'.') , 2) . '<hr/>';
//$html .= '<div id="login_form">';
$html .= form_open($_SERVER['REQUEST_URI'], 'id="loginform"');

$html .= heading(__('Initial setup'), 5);
$html .= __('Please provide the core credentials that will be used to '
        . 'administer your system');
$html .= br(2);
$table = new CI_Table;
if ($errors) {
	$html .= '<div class="obe_error">';
	$html .= __('Please correct the following errors:');
	$html .= ul($errors);
	$html .= '</div>';
}
//username
$label = ipbx_label(__('Username'), __('Admin user name'));
$data = array(
			'name' => 'username',
			'value'	=> $username,
			'required' => '',
			'placeholder' => __('username')
		);
//$table->add_row($label, form_input($data),'class="input"');
$table->add_row($label, form_input($data,'','class="input w100"'));

//password
$label = ipbx_label(__('Password'), __('Admin password'));
$data = array(
			'name' => 'password',
			'type' => 'password',
			'value'	=> $password,
			'required' => '',
			'placeholder' => __('password')
        );

$table->add_row($label, form_input($data,'','class="input w100"'));

//confirm password
$label = ipbx_label(__('Confirm Password'));
$data = array(
			'name' => 'confirm_password',
			'value'	=> $confirm_password,
			'type' => 'password',
			'required' => '',
			'placeholder' => __('password')
        );

$table->add_row($label, form_input($data,'','class="input w100"'));

//email address
$label = ipbx_label(__('Admin Email address'));
$data = array(
			'name' 	=> 'email_address',
			'value'	=> $email_address,
			'type'	=> 'email',
			'placeholder' => __('email address')
        );

$table->add_row($label, form_input($data,'','class="input"'));

//Confirm email address
$label = ipbx_label(__('Confirm Email address'));
$data = array(
			'name' => 'confirm_email',
			'value'	=> $confirm_email,
			'type'	=> 'email',
			'placeholder' => __('confirm email')
        );

$table->add_row($label, form_input($data,'','class="input"'));

$html .= $table->generate();
$html .= form_hidden('action', 'setup_admin');

$html .= form_action_bar('','',true,true);

echo $html;

?>
