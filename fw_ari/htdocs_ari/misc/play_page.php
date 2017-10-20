<?php

/**
 * @file
 * page for playing recording
 */

chdir("..");
include_once("./includes/bootstrap.php");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <TITLE>ARI</TITLE>
    <link rel="stylesheet" href="../theme/main.css" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  </head>
  <body>

<?php

  $path = $_SESSION['ari_user']['recfiles'][$_REQUEST['recindex']];

  if (isset($path)) {

    echo("<embed width='100%' type='audio/basic' src='audio.php?recindex=" . $_REQUEST['recindex'] . "' width=300, height=25 autoplay=true loop=false></embed><br>");
  }
  echo("<script language='javascript'>parent.document.getElementById('pb_load_inprogress').value='false';</script>");
?>

  </body>
</html>

