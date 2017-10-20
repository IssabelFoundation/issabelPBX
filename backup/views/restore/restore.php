<?php
$html = '';
$html .= heading(_('Restore'), 3) . '<hr class="backup-hr"/>';
//$html .= form_hidden('restore_source', 'upload');
//$html .= form_hidden('post_max_size ', '1048576000');
$html .= form_open_multipart($_SERVER['REQUEST_URI']);
$html .= form_hidden('action', 'upload');


$html .= _('Upload a backup file to restore from it. Or, pick a saved backup by selecting a server from the list on the right.');
$html .= br(2);
$html .= form_upload('upload');


$html .= form_submit('submit', _('Go!'));
$html .= form_close();
$html .= br(15);
echo $html;
