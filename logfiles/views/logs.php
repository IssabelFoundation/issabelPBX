<?php
$html = '';
$html = '<div id="logfiles_header" class="content">';
$html .= heading(__('Asterisk Log Files'), 2);


$logs = array('1'  => '/var/log/asterisk/full');

/*
$html .= form_dropdown('logfile', $files, $full, ' class="componentSelectAutoWidth" ');

$lines = array(
			'name'			=> 'lines',
			'id'			=> 'lines',
			'value'			=> 500,
			'placeholder'	=> __('lines')
);
$html .= form_input($lines,'',' class="input" style="width:100px;" ');

$show = array(
		'name'		=> 'show',
		'content'	=> __('Show'),
		'id'		=> 'show',
);
$html .= form_button($show,'',' class="button is-small" ');
 */





$html.="<div class='field has-addons'>
  <p class='control'>
    <span class='select'>
      <select name='logfile'>";

foreach($files as $idx=>$file) {
    $html.="<option value='$idx'>$file</option>";
}
$html.="
      </select>
    </span>
  </p>
  <p class='control'>
    <input class='input' type='text' name='lines' value='500' id='lines'>
  </p>
  <p class='control'>
    <a class='button'>".__('Show')."
    </a>
  </p>
</div>
";





$html .= '</div>';
$html .= '<div id="log_view" class="pre"></div>';
$html .= '<script type="text/javascript" src="/admin/modules/logfiles/assets/js/views/logs.js"></script>';
echo $html;
?>
