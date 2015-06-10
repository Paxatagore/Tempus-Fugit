<?php
//moteur de renvoi d'éléments du contexte
require_once("../inc/centrale.php") ;

$json = '{' ;
//$_POST = $_GET ;
//paramètres passés nécessairement : dates, tags
$n = extraction("datedebut", "datefin") ;
if ($n == 0) {
		//aucun paramètre n'a été passé
		$json = '{"erreur":1}' ;
		die($json) ;
}
$tags = Array() ;
$i = 0 ;
while (isset($_POST["tag".$i])) {
	$tags[] = $_POST["tag".$i] ;
	$i++ ;
}

$ccr = "" ;	//chaine de compte rendu
$q = Array() ;

function ajoutetag($t, $isin, $datedebut, $datefin) {
	global $q, $json ;
	$datecondition = datecondition($datedebut, $datefin) ;
	$q []= "SELECT DISTINCT * FROM evenement, lienel WHERE (lienel.evenement = evenement.num) AND (lienel.num = $t) AND $datecondition" ;
	$isin[] = $t ;
	$json .= $t."," ; 
	$isin = remontertag($t, $isin, $datedebut, $datefin) ;
	return $isin ;
}

function datecondition($datedebut, $datefin) {
	//condition de date
	if ($datefin == $datedebut OR $datefin == 0) {
		$datecondition = " ((evenement.andebut <= $datedebut AND evenement.anfin >= $datedebut AND evenement.anfin <>0) OR evenement.andebut = $datedebut) AND " ; 
	}
	else {
		$datecondition = " ((evenement.andebut <= $datedebut AND evenement.anfin >= $datefin AND evenement.anfin <>0) OR (evenement.anfin >= $datedebut AND evenement.anfin<= $datefin AND evenement.anfin <> 0) OR (evenement.andebut >= $datedebut AND evenement.andebut <= $datefin))" ; 
	}
	return $datecondition ;
}

function remontertag($pere, $isin, $datedebut, $datefin) {
	//remonte les catégories mères récursivement
	global $ccr ;
	$t 		= new tag() ;
	$lientt = new lientt() ;
	$lientt->select("WHERE tag2 = $pere") ;
	while ($lientt->next()) {
		//echo $lientt->json() ;
		if (!in_array($lientt->tag1, $isin)) {
			if ($lientt->de != 0) {
				if ($datedebut <= $lientt->de and $datefin >= $lientt->a) {
					$isin = ajoutetag($lientt->tag1, $isin, $lientt->de, $lientt->a) ;
				}
				if ($datedebut >= $lientt->de and $datefin <= $lientt->a) {
					$isin = ajoutetag($lientt->tag1, $isin, $datedebut, $datefin) ;
				}
				if ($datedebut >= $lientt->de and $datedebut <= $lientt->a and $datefin >= $lientt->a) {
					$isin = ajoutetag($lientt->tag1, $isin, $datedebut, $lientt->de) ;
				}
				if ($datedebut <= $lientt->de and $datefin >= $lientt->de and $datefin < $lientt->a) {
					$isin = ajoutetag($lientt->tag1, $isin, $lientt->de, $lientt->a) ;
				}
			}
			else {
				$ccr .= "En ce qui concerne le tag $lientt->tag1, il est sans restriction de temps. $lientt->de" ;
				$isin = ajoutetag($lientt->tag1, $isin, $datedebut, $datefin) ;
			}
		}
	}
	return $isin ;
}

$isin = Array()  ;
$json .= '"tags":[';
foreach ($tags as $tag) {
	$isin = remontertag($tag, $isin, $datedebut, $datefin) ;
}
$json = substr($json, 0, -1).'], "ccr": "'.$ccr.'", ' ;

$q = implode(" UNION ", $q) ;
// "  ORDER BY evenement.andebut, evenement.moisdebut, evenement.jourdebut ASC, evenement.numeroordre ASC, evenement.num ASC" ;
$e = new evenement() ;
$e->iq = mysqldb::send($q) ;

$nev = $e->lenen() ;
/*if ($nev == 0) {
	$json = '{"erreur":0}' ;
	die($json) ;
}*/
$isin = "" ;

$json .= '"evenements" : [' ;
while ($e->next()) {
	$json .= $e->json()."," ;
	$isin .= $e->num.", " ;
}
if ($nev > 0) $json = substr($json, 0, -1) ;
$json .= ']' ;
//LIENS EL et EP
if ($isin <> "") {
	$isin = substr($isin, 0, -2) ;
	$isin = "($isin)" ;
	//liens évènements - tags
	$json .= ', "lienel" : [' ;
	$lienel = new lienel() ;
	$lienel->select("where evenement IN $isin") ;
	while ($lienel->next()) {
		$json .= $lienel->json()."," ;
	}
	$json = substr($json, 0, -1)."]" ;
	//liens évènements - personne
	$lienep = new lienep();
	$lienep->select(", personne where lienep.evenement IN $isin AND lienep.personne = personne.num ORDER BY annaissance, anmort") ;
	$isin2 = "" ;
	if ($lienep->lenen() > 0) {
		$json .= ', "lienep" : [' ;
		while ($lienep->next()) {
			$json .= $lienep->json().", " ;
			$isin2 .= $lienep->personne.", " ;
		}
		$json = substr($json, 0, -2)."]" ;
		$isin2 = "(".substr($isin2, 0, -2).")" ;
		//liste des personnes concernées
		if ($isin2 <> "()") {
			$json .= ', "personnes": [' ;
			$p = new personne() ;
			$p->select("where num IN $isin2 ORDER BY nom") ;
			$isin3 = "" ;
			while ($p->next()) {
				$json .= $p->json().", " ;
				$isin3 .= $p->num.", " ;
			}
			$json = substr($json, 0, -2)."]" ;

		}
	}
}
$json .= '}' ;
die($json) ;
?>
