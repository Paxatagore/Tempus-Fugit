<?php
// papes 
//vérifie que les début et les fins de fonction de papauté (6)
//et les fonctions de papauté elles-même
//sont biens inscrites sur Rome (66) et sur Etats de l'Eglise (282), du moins entre 476 et 1871 pour ce dernier tag.

require_once("../inc/centrale.php") ;

$lienpf = new lienpf() ;
$lienpf->select("WHERE fonction = 6") ;

while($lienpf->next()) {
	$e1 	= $lienpf->evenementdebut ;
	control($e1) ;
	$e2		= $lienpf->evenementfin ;
	control($e2) ;
	$e3		= $lienpf->evenement ;
	control($e3) ;
}
	
	
function control($e) {
	$listes	= array() ;
	$lienel = new lienel() ;
	$lienel->select("WHERE evenement = ".$e) ;	
	while($lienel->next()) {
		$listes[] = $lienel->tag ;
	}
	print_r($listes) ;
	
	if (!in_array(66, $listes)) {
		$lienel->num		= 0 ;
		$lienel->evenement	= $e ;
		$lienel->tag		= 66 ;
		$lienel->save() ;
		echo "Nouveau lien EL - 66<br/>" ;
	}
	
	if (!in_array(282, $listes)) {
		$lienel->num		= 0 ;
		$lienel->evenement	= $e ;
		$lienel->tag		= 282 ;
		$lienel->save() ;
		echo "Nouveau lien EL - 282<br/>" ;
	}
}
?>