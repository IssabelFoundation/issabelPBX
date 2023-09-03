<?php
$html = '<div class="content">';
$html .= heading(__('Restore'), 2);
//$html .= form_hidden('restore_source', 'upload');
//$html .= form_hidden('post_max_size ', '1048576000');
$html .= form_open_multipart($_SERVER['REQUEST_URI'],' name="upload" id="upload" class="mx-2" ');
$html .= form_hidden('action', 'upload');


$html .= __('Upload a backup file to restore from it. Or, pick a saved backup by selecting a server from the list on the right.');
$html .= br(2);
//$html .= form_upload('upload');



$html .='<div class="file has-name is-fullwidth has-addons">
  <label class="file-label">
    <input class="file-input" type="file" name="upload" id="uploadid">
    <span class="file-cta">
      <span class="file-icon">
        <i class="fa fa-upload"></i>
      </span>
      <span class="file-label">'.__('Choose a file...').'</span>
    </span>
    <span class="file-name" id="selected_file_name">
    </span>
  </label>
  <div class="control"><input type="button" class="button is-info" value="'.__("Go!").'" onclick="document.upload.submit();$.LoadingOverlay(\'show\');"/></div>
</div>';




//$html .= form_submit('submit', __('Go!'), ' class="button is-rounded is-small" ');
$html .= form_close();

$html.='
<script>
$(function(){
const fileInput = document.querySelector("input[type=file]");
  fileInput.onchange = () => {
    if (fileInput.files.length > 0) {
      const fileName = document.querySelector(".file-name");
      fileName.textContent = fileInput.files[0].name;
    }
}
});';

$html.=js_display_confirmation_toasts(); 


$html.='</script>';
include("frameworkmsg.php");
$html.='</div>';

echo $html;
