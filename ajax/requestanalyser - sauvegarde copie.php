﻿<?php
//moteur d'analyse de la requête et de création d'un fichier JSON
require_once("../inc/centrale.php") ;

//définition des données de calcul
$tag 		= Array() ;
$personne 	= 0  ;
$dynastie	= 0 ;
$datedepart = 0  ;
$datefin 	= 0  ;
$jour 		= 0  ; 
$mois 		= 0  ; 
$evenement	= 0  ;
$fonction			= 0 ;
$recherche		= Array() ;
$requete			= "" ;
$rs					= "" ;

$n = extraction("tag", "personne", "dynastie", "fonction", "personne", "datedebut", "datefin", "recherche", "evenement", "requete", "fonction", $_GET) ;
if ($n == 0) {
	$n = extraction("tag", "personne", "dynastie", "fonction", "personne", "datedebut", "datefin", "recherche", "evenement", "requete", "fonction") ;
}
if ($n == 0) {
	$requete = "aujourd'hui" ;
}

$p		= new personne() ;
$t		= new tag() ;
$e 		= new evenement() ;
$d 		= new dynastie() ;
$f		= new fonction() ;

if (!is_array($tag) ) {
	if ($tag > 0) {
		$a = intval($tag) ;
		$tag = Array() ; 
		$t->get($a) ;
		$tag[] = Array($a, "", $t->nom) ;
	}
}

if ($dynastie <> "") {
	$d->get($dynastie) ;
}

$tables		= "" ;	//les tables SQL dans lesquelles taper
$condition	= "" ;	//la condition SQL à injecter

$titre		= "" ;	//titre de la page
$json 		=  '{' ;	//la ligne JSON

//on découpe la requête en plusieurs mots, séparés par une virgule
$motscles = explode(",", $requete) ;

foreach ($motscles as $motcle) {
	//on enlève les blancs en trops
	$motcle = addslashes(trim($motcle)) ;
	if (strlen($motcle) > 0) {
		//première possibilité : c'est une recherche forcée sur un terme donné
		if (substr(strtoupper($motcle), 0, 2) == "C:") {
			$recherche[] = substr($motcle, 2) ;
		}
		else {
			//deuxième possibilité : c'est une date unique
			if (is_numeric($motcle)) {
				$datedepart = $motcle ;
			}
			else  {
				//ce sont deux dates
				$a = dates($motcle) ;
				if (is_array($a)) {
					$datedepart = $a[0] ;
					$datefin	= $a[1] ;
				}
				else {
					$personne = personne($motcle) ;
					if ($personne == 0) {
						$rtag = tag($motcle) ;
						if ($rtag == 0) {
							$dynastie = dynastie($motcle) ;
							if ($dynastie == 0) {
								$fonction = fonction($motcle) ;
								if ($fonction == 0) {
									if (strtolower($motcle) == "prehistoire") {
										$datedepart = -5000000000 ;
										$datefin	= -5000 ;
									}
									else {
										if (strtolower($motcle) == "alea") {
											$p->select("WHERE valide = 0 ORDER BY RAND()") ;
											$p->next() ;
											$personne = $p->num ;
										}
										else {
											$recherche[] = $motcle ;
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
}

function dates($search) {
	$chaines = explode("-", $search) ;
	if ($chaines <> $search and count($chaines)>1) {
		switch(count($chaines)) {
		case 2:
			//deux années positives
			$dd = trim($chaines[0]) ;
			$df = trim($chaines[1]) ;
		break ;
		case 4:
			//deux années négatives
			$dd = -trim($chaines[1]) ;
			$df = -trim($chaines[3]) ;
			//echo $dd." à ".$df ;
		break ;
		case 3:
			//une année négative, l'autre nom
			//on présume que la première année est négative
			$dd = -trim($chaines[1]) ;
			$df = trim($chaines[2]) ;
			break ;
		}
		if (is_numeric($dd) and is_numeric($df)) {
			if ($dd > $df) {
				$a 	= $df ;
				$df = $dd ;
				$dd = $a ;
			}
			return array($dd, $df) ;
		}
	}	
	return 0 ;
}

function personne($search) {
	global $p ;
	$p->get("nom", stripslashes($search)) ;
	if ($p->num > 0) {
		return $p->num  ;
	}
	else return 0 ;
}

function dynastie($search) {
	global $d ;
	$d->get("nom", $search) ;
	if ($d->num > 0) {
		return $d->num ;
	}
	else return 0 ;
}

function fonction($search) {
	global $f ;
	$f->get("nom", $search) ;
	if ($f->num > 0) {
		return $f->num ;
	}
	else return 0 ;
}	

function tag($search) {
	global $t, $tag ;
	$tableau = array() ;
	if (substr($search, 0, 1) == "/") {
		$tableau[1] = "/" ;
		$search = substr($search, 1) ;
	}
	else {
		//tag : croisé avec (*)
		if (substr($search, 0, 1) == "*") {
			$tableau[1] = "*" ;
			$search = substr($search, 1) ;
		}
		else {
			//tag descendant
			if (substr($search, 0, 1) == "$") {
				$tableau[1] = "$" ;
				$search = substr($search, 1) ;
			}
			else {
				$tableau[1] = "" ;
			}
		}
	}
	$t->get("nom", $search) ;
	if ($t->num > 0) {
		$tableau[0] = $t->num ;
		$tableau[2]	= $t->nom ;
		$tag[] 		= $tableau ;
		return 1 ;
	}
	else return 0 ;
}

function cherche($search) {
	if ($search <> "") {
		global $recherche ;
		$recherche	= $search ;
		return 1 ;
	}
	else return false() ;
}

function descendretag($pere, $isin) {	
	//prend tous les tag dessous
	$t 		= new tag() ;
	$lientt = new lientt() ;
	$lientt->select("where tag1 = $pere and de = 0" ) ;
	while($lientt->next()) {
		$t->get($lientt->tag2) ;
		if (!in_array($t->num, $isin)) {
			$isin[] = $t->num ;
			$isin = descendretag($t->num, $isin) ;
		}
	}
	return $isin ;
}

function remontertag($pere, $isin) {
	//remonte les catégories mères récursivement
	$t 		= new tag() ;
	$lientt = new lientt() ;
	$lientt->select("where tag2 = $pere and relation = 0") ;
	while ($lientt->next()) {
		$t->get($lientt->tag1) ;
		if (!in_array($t->num, $isin)) {
			$isin[] = $t->num ;
			$isin = remontertag($t->num, $isin) ;
		}
		
	}
	return $isin ;
}
//Dates
if ($datedepart <> 0) {
	if ($datefin == $datedepart OR $datefin == 0) {
		//calcul
		$condition .= " ((evenement.andebut <= $datedepart AND evenement.anfin >= $datedepart AND evenement.anfin <>0) OR evenement.andebut = $datedepart) AND " ; 
		$titre .= $datedepart.", " ;
	}
	else {
		//calcul
		$condition .= " ((evenement.andebut <= $datedepart AND evenement.anfin >= $datefin AND evenement.anfin <>0) OR (evenement.anfin >= $datedepart AND evenement.anfin<= $datefin AND evenement.anfin <> 0) OR (evenement.andebut >= $datedepart AND evenement.andebut <= $datefin)) AND" ; 
		//$condition .= " ((andebut BETWEEN $datedepart AND $datefin) or (anfin <> 0 AND anfin BETWEEN $datedepart AND $datefin)) AND" ;
		$titre .= $datedepart." - ".$datefin.", " ;
	}
}

//tag
if (count($tag) > 0) {
	$isin = array() ;		//la liste des tags à inclure dans la requête (is in)
	$isin2 = array() ;
	$tables .= ", lienel" ;
	$jsonTag = '"tags":[' ;
	
	
	
	/* nouveau module */
	
	foreach ($tag as $unTag) {		//on prend les tags un par un
		list($leTagNumero, $leTagMode, $leTagNom) = $unTag ;
		if (!in_array($leTagNumero, $isin)) $isin[] = $leTagNumero ;		//ce tag n'est pas encore dans la liste des isIn, on l'ajoute.
		$isin = descendretag($leTagNumero, $isin) ;
		$isin = remontertag($leTagNumero, $isin) ;
		$titre .= $leTagNom.", " ;
		$jsonTag .= '{"num":"'.$leTagNumero.'", "nom":"'.$leTagNom.'", "mode":"'.$leTagMode.'"},' ;
	}	
		
		
	/* ancien module  */
	/*
	foreach ($tag as $unTag) {		//on prend les tags un par un
		list($leTagNumero, $leTagMode, $leTagNom) = $unTag ;
		if ($leTagMode == "*") {
			//mode filtre par un tag : seul les évènements répondant à ce tag également seront pris
			$titre .= "*".$leTagNom.", " ;
			if (!in_array($leTagNumero, $isin2)) $isin2[] = $leTagNumero ;
		}
		else {
			if (!in_array($leTagNumero, $isin)) $isin[] = $leTagNumero ;		//ce tag n'est pas encore dans la liste des isIn, on l'ajoute.
		
			if ($leTagMode == "" ){
				//si le mode de tag est vide, on déroule toutes les catégories parentes
				$isin = remontertag($leTagNumero, $isin) ;
				$titre .= $leTagNom.", " ;
			}
			if ($leTagMode == "/" ){
				//ajout d'un tag sans ses sur ou sous-catégories : on ne modifie que le titre
				$titre .= "/".$leTagNom.", " ;
			}
			if ($leTagMode == "$" ){
				//ajout d'un tag avec ses sous catégories uniquement 
				$isin = descendretag($leTagNumero, $isin) ;
				$titre .= "$".$leTagNom.", " ;
			}
		}
		$jsonTag .= '{"num":"'.$leTagNumero.'", "nom":"'.$leTagNom.'", "mode":"'.$leTagMode.'"},' ;
	}*/
	
	//on transforme le tableau des isin en une chaîne de texte
	$isinTxt = "" ;
	foreach($isin as $in) {
		$isinTxt .= ",".$in ;
	}
	$isinTxt = substr($isinTxt, 1) ;
	$condition .= "((lienel.tag IN ($isinTxt)) AND (evenement.num = lienel.evenement)) AND " ;
	if (count($isin2) > 0) {
		$isin2Txt = "" ;
		foreach($isin2 as $in) {
			$isin2Txt .= ",".$in ;
		}
		$isin2Txt = substr($isin2Txt, 1) ;
		$condition .= "((lienel.tag IN ($isin2Txt)) AND (evenement.num = lienel.evenement)) AND " ;
	}
	$jsonTag = substr($jsonTag, 0, -1).']' ;
}
else {
	$jsonTag = '"tags":""' ;
}

//personne
if ($personne > 0) {
	if ($p->nom == "") {
		$p->get($personne) ;
	}
	$tables .= ", lienep" ;
	$condition .= "(lienep.personne = $personne and evenement.num = lienep.evenement) AND ";
	$titre .= $p->display().", " ;		//affichage
}

if ($dynastie > 0) {
	$tables .= ", personne, lienep" ;
	$condition .= "(lienep.personne = personne.num AND evenement.num = lienep.evenement AND personne.dynastie = $dynastie) AND " ;
	$titre .= $d->nom.", " ;
}

if ($fonction > 0) {
	$condition .= "(fonction = $fonction) AND" ;
	if ($f->num == 0) {
		$f->get($fonction) ;
	}
	$titre .= $f->nom.", " ;
}

if ($jour > 0) {
	$condition .= " jourdebut = $jour AND" ;
	$titre .= "$jour " ;
}
if ($mois > 0) {
	$lesmois = array("", "janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre") ;
	$condition .= " moisdebut = $mois AND" ;
	$titre .= $lesmois[$mois].", " ;
}

if ($jour > 0 AND $mois > 0) {
	$jav = $jour - 1 ;
	$jap = $jour + 1 ;
	$mav = $mois ;
	$map = $mois ;
	if ($jav == 0) {
		$mav = $mav - 1 ;
		if ($mav < 1) $mav = 12 ;		
		if ($mav == 1 OR $mav == 3 OR $mav == 5 OR $mav == 7 OR $mav == 8 or $mav == 10 OR $mav == 12) $jav = 31 ;
		else {
			if ($mav == 2) $jav = 29 ;
			else $jav = 30 ;
		}
	}
	if ($jap > 29) {
		if (($map == 2 and $jap>29) OR (($map == 4 OR $map == 6 OR $map == 9 OR $map == 11) and $jap > 30) OR $jap > 31) {
			$map = $map + 1 ;
			$jap = 1 ;
		}
		if ($map == 13) $map = 1 ;
	}
	$h2 = "<a href='index.php?jour=$jav&mois=$mav'>$jav";
	if ($jav == 1) $h2 .= "er";
	$h2 .= " ".$lesmois[$mav]."</a> - <a href='index.php?jour=$jap&mois=$map'>".$jap ;
	if ($jap == 1) $h2 .= "er"; 
	$h2 .= " ".$lesmois[$map]."</a>" ;
	//$stitre[] = $h2 ;
	$liensjours = "&jourdebut=$jour&moisdebut=$mois" ;
}

//Recherche
if (count($recherche) > 0) {
	$condition .= "(" ;
	$rs = "" ;
	foreach ($recherche as $r) {
		$condition.= "evenement.description LIKE '%".$r."%' OR evenement.precisions LIKE '%".$r."%' OR " ;
		$titre .= $r.", " ;
		$rs .= $r.", " ;
	}
	$condition = substr($condition, 0, -3).") AND " ;
	
}

//Evènement
if ($evenement > 0) {
	$e = new evenement() ;
	$e->get($evenement) ;
	$condition .= "(evenement.num = $evenement OR evenement.pere = $evenement) AND " ;
	$titre .= "Ev:".$e->description.", " ;
	$condition .= "(evenement.fonction = $fonction) AND " ;
	if ($f->num == 0) {
		//on n'a pas appelé l'objet f
		$f->get($fonction) ;
	}
	$titre .= $f->nom.", " ;
}

//titre
if (strlen($titre) > 0) $titre = substr($titre, 0, -2) ; //on enlève les , qui ferment le titre !

//REQUETE PRINCIPALE DES EVENEMENTS
$q = "$tables WHERE $condition 1 ORDER BY evenement.andebut, evenement.moisdebut, evenement.jourdebut ASC, evenement.numeroordre ASC, evenement.num ASC limit 0, 1500" ;

$e->select($q) ;
$nev = $e->lenen() ;
$json .= $jsonTag.', "personne" : "'.$personne.'", "dynastie" : "'.$dynastie.'", "fonction" : "'.$fonction.'", "datedepart" : "'.$datedepart.'", "datefin" : "'.$datefin.'", "jour" : "'.$jour.'", "mois" : "'.$mois.'", "evenement" : "'.$evenement.'", "recherche" : "'.stripslashes($rs).'", "nevenement" : "'.$nev.'", "titre": "'.stripslashes($titre).'", "evenements" : [' ;

$isin = "" ;
while ($e->next()) {
	$json .= $e->json()."," ;
	$isin .= $e->num.", " ;
}
if ($nev > 0) $json = substr($json, 0, -1) ;
$json .= ']' ;
$json .= ', "q" : "'.stripslashes($q).'"' ;
//LIENS EL et EP
if ($isin <> "") {
	$isin = substr($isin, 0, -2) ;
	$isin = "($isin)" ;
	//liens évènements - tags
	$json .= ', "lienel" : [' ;
	$lienel = new lienel() ;
	$lienel->select("where evenement IN $isin") ;
	if ($lienel->lenen() > 0) {
		while ($lienel->next()) {
			$json .= $lienel->json()."," ;
		}
		$json = substr($json, 0, -1) ;
	}
	$json .= "]" ;
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
	//liens TT
	$lientt = new lientt() ;
	$lientt->select("WHERE evenement IN $isin AND relation > 0") ;
	if ($lientt->lenen() >0) {
		$json .= ', "lientt" : [' ;
		while ($lientt->next()) {
			$json .= $lientt->json().", " ;
		}
		$json = substr($json, 0, -2)."]" ;
	}
}
else {
	if ($personne > 0) {
		$json .= ', "personnes": [' ;
		$json .= $p->json()."," ;
		$json = substr($json, 0, -1)."]" ;
	}
}
$json .= "}" ;
die($json) ;
?>