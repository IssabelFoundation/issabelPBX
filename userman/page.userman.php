<?php
$userman = setup_userman();
//pre-boostrap styles
if(version_compare(getVersion(), '12.0', '<')) { ?>
	<style>
		.alert {
			width: 80%;
			padding: 15px;
			margin-bottom: 20px;
			border: 1px solid transparent;
			border-radius: 4px;
		}
		.alert-success {
			color: #468847;
			background-color: #dff0d8;
			border-color: #d6e9c6;
		}
		.alert-danger {
			color: #b94a48;
			background-color: #f2dede;
			border-color: #ebccd1;
		}
	</style>
<?php }
echo $userman->myShowPage();
