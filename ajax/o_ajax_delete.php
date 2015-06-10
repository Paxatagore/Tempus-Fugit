<?php
require_once("../inc/centrale.php") ;
$modeVerbeux = 0 ;
//$modeVerbeux = 1 ;

extraction("num", "objet", $_POST) ;
if ($modeVerbeux == 1) {
	echo "Objet à supprimer : $objet <br/>" ;
	echo "Référence num : $num <br/>" ;
}

$o = new $objet ;

if ($modeVerbeux == 1) {
	echo "Objet bien instancié.<br/>" ;
}


$o->get($num) ;

if ($modeVerbeux == 1) {
	echo "Objet bien chargé.<br/>" ;
}

$n = $o->delete() ;
if ($modeVerbeux == 1) {
	if ($n >0 ) {
		echo "Objet bien effacé.<br/>" ;
	}
}

echo '{"result" : "'.$n.'"}' ;
die() ;
?>
