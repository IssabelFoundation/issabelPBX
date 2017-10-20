<?php
if (file_exists(dirname(__FILE__) . '/index_custom.php')) {
	  include_once(dirname(__FILE__) . '/index_custom.php');
} else {
	  header('Location: /admin');
}
