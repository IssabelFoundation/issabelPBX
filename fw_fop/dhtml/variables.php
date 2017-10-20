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
		if($numero > $maxnumero) {$maxnumero = $numero;}
		$printresponse.="$otri[0]=$otri[1]&";
	}
	if(substr($otri[0],0,4)=="icon") {
		$printresponse.="$otri[0]=$otri[1]&";
	}
}
$printresponse.="maxnumero=$maxnumero";

echo $printresponse;
?>

