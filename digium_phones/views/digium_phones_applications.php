<br />

<?php
	if (!$easymode) {
		if (function_exists('queues_list')) {
?>
	<a href="config.php?type=setup&display=digium_phones&digium_phones_form=application_queues_edit" class="btn btn-default">Queues</a>
<?php
		}
?>
	<a href="config.php?type=setup&display=digium_phones&digium_phones_form=application_status_edit" class="btn btn-default">Status</a>

	<a href="config.php?type=setup&display=digium_phones&digium_phones_form=application_custom_edit" class="btn btn-default">Custom</a>
<?php
	}
?>
