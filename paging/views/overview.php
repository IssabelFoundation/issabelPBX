<?php
$html = '';
$html .= heading(__('Paging'), 3);
$table = new CI_Table;

$html .= '<div class="box overview">';
$html .=  __("This module is for specific phones that are capable of Paging or Intercom. This section is for configuring group paging, intercom is configured through <strong>Feature Codes</strong>. Intercom must be enabled on a handset before it will allow incoming calls. It is possible to restrict incoming intercom calls to specific extensions only, or to allow intercom calls from all extensions but explicitly deny from specific extensions.<br /><br />This module should work with Aastra, Grandstream, Linksys/Sipura, Mitel, Polycom, SNOM , and possibly other SIP phones (not ATAs). Any phone that is always set to auto-answer should also work (such as the console extension if configured).");

if ($intercom_code != '') {
	$html .= br() . br() . __('Example usage') . ': ' . br() . br();
	$table->add_row($intercom_code . 'nnn:', __('Intercom extension nnn'));	
	$table->add_row($oncode . ':', 
		__('Enable all extensions to intercom you '
		. '(except those explicitly denied)'));
	$table->add_row($oncode . 'nnn:',
		__('Explicitly allow extension nnn to intercom you '
		. '(even if others are disabled)'));
	$table->add_row($offcode . ':', 
		__('Disable all extensions from intercom you '
		. '(except those explicitly allowed)'));
	$table->add_row($offcode . 'nnn:',
		__('Explicitly deny extension nnn to intercom you (even if '
		. 'generally enabled)'));
	
	$html .= $table->generate();
} else {
	$html .= __('Intercom mode is currently disabled, it can be enabled in '
		 . 'the Feature Codes Panel.');
}
$html .= '</div>';

$html .= "<script>";
$html .= js_display_confirmation_toasts(); 
$html .= '</script>';
$html .= '</div>';

$html .= form_action_bar($extdisplay);
echo $html;
?>
