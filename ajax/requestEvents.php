<?php
//renvoi un fichier JSON qui contient un objet
require_once("../inc/centrale.php") ;
$e 			= new evenement() ;
$cotable 	= $_POST["cotable"] ;
$critere	= $_POST["critere"] ;
$cc 		= "" ;
if ($cotable == "lienep") {
	$cc = "lienep.personne = $critere" ;
}
$condition = ", $cotable WHERE $cc AND $cotable.evenement = evenement.num ORDER BY andebut, moisdebut, jourdebut, anfin, moisfin, jourfin" ;
$e->select($condition) ;
$json = "" ;
if ($e->lenen() > 0) {
	$json = '[' ;
	while ($e->next()) {
		$json .= $e->json()."," ;
	}
	$json = substr($json, 0, -1);
	$json .= ']' ;
}
else {
	$json = '{}' ;
}
die($json) ;
?>