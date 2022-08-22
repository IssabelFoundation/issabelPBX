<?php
$helptext =  _("This module is for specific phones that are capable of Paging or Intercom. This section is for configuring group paging, intercom is configured through <strong>Feature Codes</strong>. Intercom must be enabled on a handset before it will allow incoming calls. It is possible to restrict incoming intercom calls to specific extensions only, or to allow intercom calls from all extensions but explicitly deny from specific extensions.<br /><br />This module should work with Aastra, Grandstream, Linksys/Sipura, Mitel, Polycom, SNOM , and possibly other SIP phones (not ATAs). Any phone that is always set to auto-answer should also work (such as the console extension if configured).");

		$disabled = '(' . _('Disabled') . ')';

		$fcc = new featurecode('paging', 'intercom-prefix');
		$vars['intercom_code'] = $fcc->getCodeActive();
		unset($fcc);

		$fcc = new featurecode('paging', 'intercom-on');
		$vars['oncode'] = $fcc->getCodeActive();
		unset($fcc);
		if ($vars['oncode'] === '') {
			$vars['oncode'] = $disabled;
		}

		$fcc = new featurecode('paging', 'intercom-off');
		$vars['offcode'] = $fcc->getCodeActive();
		unset($fcc);
		if ($vars['offcode'] === '') {
			$vars['offcode'] = $disabled;
		}

        extract($vars);

if ($intercom_code != '') {
    $helptext .= br() . br() . _('Example usage') . ': ' . br() . br();
$table = new CI_Table;
    $table->add_row($intercom_code . 'nnn:', _('Intercom extension nnn'));
    $table->add_row($oncode . ':',
        _('Enable all extensions to intercom you '
        . '(except those explicitly denied)'));
    $table->add_row($oncode . 'nnn:',
        _('Explicitly allow extension nnn to intercom you '
        . '(even if others are disabled)'));
    $table->add_row($offcode . ':',
        _('Disable all extensions from intercom you '
        . '(except those explicitly allowed)'));
    $table->add_row($offcode . 'nnn:',
        _('Explicitly deny extension nnn to intercom you (even if '
        . 'generally enabled)'));

    $helptext .= $table->generate();
} else {
    $helptext .= _('Intercom mode is currently disabled, it can be enabled in '
         . 'the Feature Codes Panel.');
}

$helpp = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';


$html = ''; 
if ($extdisplay) {
    $html .= heading('<div class="is-flex">'._('Modify Paging Group').': '.$description.$helpp.'</div>', 2);
} else {
    $html .= heading('<div class="is-flex">'._('Add Paging Group').$helpp.'</div>', 2);

}

$html .= form_open($_SERVER['REQUEST_URI'], 'id="mainform"');
$html .= form_hidden('display', $display);
$html .= form_hidden('pagegrp', $extdisplay);
$html .= form_hidden('extdisplay', $extdisplay);
$html .= form_hidden('action', 'submit');

$table = new CI_Table;
if ($conflict_url) {
	$html .= heading(_('Conflicting Extensions'));
	$html .= implode(br(), $conflict_url);
}

$table->add_row(array('colspan' => 2, 'data' => heading(dgettext('amp','General Settings'), 5) ));

//extension
$label = ipbx_label(_('Paging Extension'),
			_('The number users will dial to page this group'));
$table->add_row($label, form_input('pagenbr', $extdisplay, 'class="extdisplay input w100"'));

//description
$label = ipbx_label(_('Group Description'),
			_('Provide a descriptive title for this Page Group.'));
$table->add_row($label, form_input('description', $description, 'class="input w100"'));

//device list
$label = ipbx_label(_('Device List'), 
			_('Devices to page. Please note, paging calls the '
			. 'actual device (and not the user). Amount of pagable devices is '
			. 'restricted by the advanced setting key PAGINGMAXPARTICIPANTS '
			. 'and is currently set to ') . $amp_conf['PAGINGMAXPARTICIPANTS']
);
$selected_dev = $notselected_dev = '';
foreach ($device_list as $ext => $name) {
	if (in_array((string)$ext, $devices)) {
		$selected_dev .= '<div data-ext="' . $ext . '">' . $name .'</div>';
	} else {
		$notselected_dev .= '<div data-ext="' . $ext . '">' . $name .'</div>';
	}
} 
$class = ' class="box device_list" ';
$selected_dev = form_fieldset(_('Selected'), $class . 'id="selected_dev" ' )
				. $selected_dev 
				. form_fieldset_close();
$notselected_dev = form_fieldset(_('Not Selected'), $class . 'id="notselected_dev" ' )
				. $notselected_dev 
				. form_fieldset_close();
$table->add_row($label);
$table->add_row($selected_dev, $notselected_dev);

//busy ext
$help=array();
$help[] = _('"Skip" will not page any busy extension. All other extensions '
			. 'will be paged as normal');
$help[] = _('"Force" will not check if the device is in use before paging '
			. 'it. This means conversations can be interrupted by a page '
			. '(depending on how the device handles it). This is useful '
			. 'for "emergency" paging groups.');
$help[] = _('"Whisper" will attempt to use the ChanSpy capability on SIP '
			. 'channels, resulting in the page being "sent to the '
			. 'device\'s earpiece "whispered" to the user but not heard '
			. 'by the remote party. If ChanSpy is not supported on the '
			. 'device or otherwise fails, no page will get through. It '
			. 'probably does not make too much sense to choose duplex if '
			. 'using Whisper mode.');


$newradio = ipbx_radio('force_page',array(array('value'=>0,'text'=>_('Skip')),array('value'=>1,'text'=>_('Force')),array('value'=>2,'text'=>_('Whisper'))),$force_page,false);
$label    = ipbx_label(_('Busy Extensions'), ul($help));
$table->add_row( $label, $newradio );

//duplex
$label = ipbx_label(_('Duplex'), 
			_('Paging is typically one way for announcements only. '
			. 'Checking this will make the paging duplex, allowing all '
			. 'phones in the paging group to be able to talk and be '
			. 'heard by all. This makes it like an "instant conference"'));
$table->add_row($label, ipbx_yesno_checkbox("duplex",$duplex,false));

//default
$label = ipbx_label(_('Default Page Group'));

$table->add_row($label, ipbx_yesno_checkbox("default_group",$default_group,false));

$html .= $table->generate();
$html .= $hooks . br(2);

$html .= "</form>";
$html .= "<script>";

$html .= "
var msgInvalidDescription = '"._('Invalid description specified')."';
var msgInvalidExtension = '"._('Invalid extension specified')."';
";
$html .= js_display_confirmation_toasts();
$html .= "</script>";
$html .= "</div>";
$html .= form_action_bar($extdisplay);


echo $html;
