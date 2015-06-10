<?php
//script qui lance la fusion de deux personnes
require_once("../inc/centrale.php") ;

$p1 = new personne() ;
$p2 = new personne() ;
$p1->get("nom", $_GET["p1"]) ;
$p2->get("nom", $_GET["p2"]) ;
echo "$p1->nom va fusionner avec $p2->nom." ;

//fusion des éléments. On prend par principe les éléments de p1, sauf s'ils sont vides et que p2 est défini.
function fusionString($array) {
	foreach($array as $data) {
	global $p1, $p2 ;
		if ($p1->$data == "" and $p2->$data <> "") {
			$p1->$data = $p2->$data ;
		}
	}
}
function fusionNumeric($array) {
	global $p1, $p2 ;
	foreach($array as $data) {
		if ($p1->$data == 0 and $p2->$data <> 0) {
			$p1->$data = $p2->$data ;
		}
	}
}

fusionString(array("autreNom", "url", "sexe", "notice")) ;
fusionNumeric(array("dynastie", "naissance_evenementId", "annaissance", "mort_evenementId", "anmort")) ;

$p1->save() ;

//fusion des liens EP

$lienep1 	= new lienep() ;
$lep1 		= array() ;
$lienep2 	= new lienep() ;
$lep2 		= array() ;
$lienep3	= new lienep() ;

$lienep1->select("where personne = $p1->num") ;
while ($lienep1->next()) {
	$lep1[] = $lienep1->evenement ;
}

$lienep2->select("where personne = $p2->num") ;
while ($lienep2->next()) {
	$lienep2->delete() ;
	$lep2[] = $lienep2->evenement ;
}

$aajouter = array_diff($lep2, $lep1) ;
foreach($aajouter as $data) {
	$lienep3->num 		= 0 ;
	$lienep3->personne	= $p1->num ;
	$lienep3->evenement	= $data ;
	echo "Il faut ajouter ".$lienep3->evenement."<br/>" ;
	$lienep3->save() ;
}

$p2->delete() ;
?>