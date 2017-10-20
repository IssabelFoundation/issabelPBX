<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
</head>
<body>
<?
if(!isset($_GET['clid'])) {
     echo "No caller ID provided<BR>";
} else {
     echo "Caller id is: ".$_GET['clid']."<BR>";
}

if(!isset($_GET['clidname'])) {
     echo "No caller ID Name provided<BR>";
} else {
	 echo "Your clid name: ".base64_decode($_GET['clidname'])."<BR>";
}

?>
</body>
</html>
