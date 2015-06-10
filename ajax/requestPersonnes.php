<?php
require_once("../inc/centrale.php") ;
$i 		= 0 ;
$isin 	= "" ;
$isin2 	= "" ;
$json 	= "" ;
$json1	= "[]" ;
$json2	= "[]" ;
$json4	= "[]" ;
$ejse	= array("num", "jourdebut", "moisdebut", "andebut", "versdebut", "jourfin", "moisfin", "anfin", "versfin", "fonction", "personne") ;
$isin = $_POST["personnes"] ;

if ($isin != "") {
	//liens PP
	$json1 = '[' ;
	$lpp = new relationpersonnes() ;
	$lpp->select("WHERE personne1 IN ($isin) or personne2 IN ($isin)") ;
	if ($lpp->lenen() > 0) {
		while ($lpp->next()) {
			$json1 .= $lpp->json()."," ;
			$isin2 .= $lpp->personne1.",".$lpp->personne2."," ;
		}
		$json1 = substr($json1, 0, -1)."]" ;
	}
	else {
		$json1 .= "]" ;
	}
	
	//catalogue des personnes associées
	if ($isin2 !="") {
		$isin2 = substr($isin2, 0, -1);
		$json2 = '[' ;
		$p = new personne() ;
		$p->select("WHERE num IN ($isin2)") ;
		while ($p->next()) {
			$json2 .= $p->json()."," ;
		}
		$json2 = substr($json2, 0, -1)."]" ;
	}
	
	//liens pf
	$e = new evenement() ;
	$q = "SELECT evenement.num, evenement.andebut, evenement.anfin, evenement.fonction, lienep.personne FROM evenement, lienep WHERE evenement.num = lienep.evenement AND lienep.personne IN ($isin) AND evenement.fonction != '' " ;
	$q = mysqldb::send($q) ;
	//if (mysqli_num_rows($q)) {
	if (mysqldb::$req->rowCount()) {
		$json3 = array() ;
		//while ($row = mysqli_fetch_assoc($q)) {
		while ($row = mysqldb::$req->fetch()) {
			$json3[] = json_encode($row) ;
		}
		mysqldb::$req->closeCursor() ;
		$json3 = "[".implode(",", $json3)."]" ;
	}
	else {
		$json3 = "[]" ;
	}
	
	//liens personnes - lieu
	$json4 = '[' ;
	$lpl = new lienpl() ;
	$lpl->select("WHERE lienpl.personne IN ($isin)") ;
	if ($lpl->lenen() > 0) {
		while ($lpl->next()) {
			$json4 .= $lpl->json()."," ;
		}
		$json4 = substr($json4, 0, -1)."]" ;
	}
	else {
		$json4 .= "]" ;
	}
}
$json = '{ "lienspp" : '.$json1.', "personnes" : '.$json2.', "fonctions" : '.$json3.', "lienspl" : '.$json4.'}' ;
die($json) ;
?>
