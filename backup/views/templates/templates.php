<?php

echo '<div class="content">';
echo heading('Templates', 2);

echo '<a href="config.php?type=setup&display=backup_templates&action=edit">';
echo '<input type="button" class="button is-rounded" value="' . __('New Template') . '"></a><br />';

echo "<script>";
echo js_display_confirmation_toasts();
echo "</script>";
