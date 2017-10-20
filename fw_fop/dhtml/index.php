<?
header("Content-type: text/html; charset=utf-8");

if(isset($_GET['context'])) {
    $contexto=$_GET['context'];
    $contexto=strtoupper($contexto);
    $archivo = "variables$contexto.txt";
} else {
    $archivo="variables.txt";
}

$pepe = file_get_contents($archivo);
$partes = preg_split("/&/",$pepe);
foreach ($partes as $elemento) {
	$otri = preg_split("/=/",$elemento);
	if(substr($otri[0],0,5)=="texto") {
		$numero = substr($otri[0],5);
		if($numero > $buttoncount) {$buttoncount = $numero;}
	}
}
?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
  <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
  <META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">

  <link href="css/default.css" rel="stylesheet" type="text/css" ></link>
  <link href="css/operator.css" rel="stylesheet" type="text/css" ></link>

<script language='Javascript'>
<!--
function init() {
	new Ajax.Request('variables.php?context=<?=$contexto?>', {onSuccess:handlerFunc, onFailure:errFunc});
	loglines = new Array();
    tiempos   = new Object();
	tipofree  = new Object();
	win = new Window('log', {top:10, left:10, width:400, height:250, zIndex: 100, resizable: true, title: "Log", hideEffect: Effect.SwitchOff})
	win.getContent().innerHTML= "&nbsp;";
	timerID  = setTimeout("UpdateTimer()", 1000);
};
-->
</script>

  <script type="text/javascript" src="js/prototype.js"></script>
  <script type="text/javascript" src="js/dragdrop.js"></script>
  <script type="text/javascript" src="js/effects.js"> </script>
  <script type="text/javascript" src="js/window.js"> </script>
  <script type="text/javascript" src="js/base64.js"> </script>
  <script type="text/javascript" src="js/scriptaculous.js"> </script>
  <script type="text/javascript" src="js/operator.js"> </script>


</head>

<body>
  <script type="text/javascript" src="js/wz_tooltip.js"> </script>
<a href='javascript: win.showCenter();'><img src='images/bug.png' border=0></a>
<?
for($a=1;$a<=$buttoncount;$a++)
{
$mouseov=" onmouseover='javascript:$(\"mcount$a\").style.display=\"block\";' onmouseout='javascript:$(\"mcount$a\").style.display=\"none\";' ";
$mouseov1 = " onMouseover=\"TagToTip('mwitip$a')\"; onMouseout=\"UnTip()\" ";
$mouseov2 = " onMouseover=\"TagToTip('phonetip$a')\"; onMouseout=\"UnTip()\" ";
echo "<div id=\"boton$a\" class='free' style='display: none;'>\n";
echo "<span id='label$a'>&nbsp;</span><BR>\n";
echo "<span id='clid$a' class='clid'>&nbsp;</span><BR>\n";
echo "<div class='clid' id='tick$a'>&nbsp;</div>";
echo "<div id='phone$a' class='phone1' $mouseov2>&nbsp;</div>";
echo "<span id='phonetip$a'></span>";
echo "<span id='mwitip$a'></span>";
echo "<div id='mwi$a' class='mwi' $mouseov1>&nbsp;</div>";
echo "</div>\n";
} 
?>
<BR><BR>
<div id="pa" style='width:100%; clear:both; font-size: 10px;' >
</div>

<div id="log" style='float: none; font-size: 10px;'>
</div>

<div align='center'>
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="100" height="100" id="operator">
<param name="allowScriptAccess" value="sameDomain" />
<param name="movie" value="comunicator.swf?context=<?=$contexto?>" />
<param name="quality" value="high" />
<param name="scale" value="noScale" />
<param name="bgcolor" value="#ffffff" />
<embed src="comunicator.swf?context=<?=$contexto?>" scale="noScale" quality="high" bgcolor="#ffffff" width="100" height="100" name="operator" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" swLiveConnect="true"/>
</div>
</body>
</html>
