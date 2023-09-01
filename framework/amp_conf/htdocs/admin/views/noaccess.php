<?php
$html = '';

$html.= "<article class='message is-warning'>
  <div class='message-header'>
    <p>".__('Not found')."</p>
  </div>
  <div class='message-body'>".__('The section you requested does not exist or you do not have access to it.')."
  </div>
  </article>
  ";

echo $html;
?>
