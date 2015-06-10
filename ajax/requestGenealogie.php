<?php
//produit les informations à afficher dans l'arbre généalogique
require_once("../inc/centrale.php") ;
$profondeur = 6 ;
extraction("personne", "profondeur", $_POST) ;
$p = new personne() ;
$p->get($personne) ;
$profondeur = intval($profondeur) ;
$jsonAncetres = '{"ancetres" : [' ;
$jsonEnfants = ', "enfants" : [' ;

function obtientParents($p, $n) {
	//où p est le numéro d'une personne
	global $profondeur, $jsonAncetres ;
	$per = new personne() ;
	$per->get($p) ;
	if ($n < $profondeur) {
		//echo("<p>Cherchons les ancêtres.</p>") ;
		$jsonAncetres .= '{"personne":'.$per->json().', "parents" : [' ;
		$rpp = new relationpersonnes ;
		$rpp->select("where personne1 = $p and typerelation = 1") ;
		if ($rpp->lenen() > 0) {
			while ($rpp->next()) {
				$p2 = $rpp->personne2 ;
				$npu = $n +1 ;
				obtientParents($p2, $npu)."," ;
				
			}
			$jsonAncetres = substr($jsonAncetres, 0, -1) ;
		}
		$jsonAncetres .= ']},' ;
	}
	else {
		$jsonAncetres .= '{"personne": '.$per->json().'},' ;
		
	}
	return 1 ;
}

function obtientEnfants($p, $n) {
	global $profondeur, $jsonEnfants ;
	$per = new personne() ;
	$per->get($p) ;
	if ($n < $profondeur) {
		$jsonEnfants .= '{"personne":'.$per->json().', "parents" : [' ;
		$rpp = new relationpersonnes ;
		$rpp->select("where personne2 = $p and typerelation = 1") ;
		if ($rpp->lenen() > 0) {
			while ($rpp->next()) {
				$p2 = $rpp->personne1 ;
				$npu = $n +1 ;
				obtientEnfants($p2, $npu)."," ;
			}
			$jsonEnfants = substr($jsonEnfants, 0, -1) ;
		}
		$jsonEnfants .= ']},' ;
	}
	else {
		$jsonEnfants .= '{"personne": '.$per->json().'},' ;
		
	}
	return 1 ;
}

obtientParents($personne, $jsonAncetres, 0) ;
$jsonAncetres = substr($jsonAncetres, 0, -1)."]" ;
obtientEnfants($personne, $jsonEnfants, 0) ;
$jsonEnfants = substr($jsonEnfants, 0, -1)."]}" ;
$json = $jsonAncetres.$jsonEnfants ;
die($json) ;
?>
