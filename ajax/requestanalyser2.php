<?php
//moteur d'analyse de la requête et de création d'un fichier JSON
//$modeVerbeux = 0 ;
//$modeVerbeux = 1 ;
$time_start = microtime(true);
require_once("../inc/centrale.php") ;

//définition des données de calcul
$tag 		= Array() ;
$listeTags	= "" ;
$listeFonctions = "" ;
$listeDynasties = "" ;
$personne 	= 0 ;
$dynastie	= 0 ;
$datedepart = 0 ;
$datefin 	= 0 ;
$jour 		= 0 ; 
$mois 		= 0 ; 
$evenement	= 0 ;
$fonction	= 0 ;
$recherche	= Array() ;
$requete	= "" ;
$rs			= "" ;
$tables		= "" ;	//les tables SQL dans lesquelles taper
$condition	= "" ;	//la condition SQL à injecter
$alea 		= 0 ;
$e			= new evenement ;

$n = extraction("rctags", "rcpersonnes", "rcdynastie", "rcfonction", "rcdatedepart", "rcdatefin", "rcrecherche", "rcfonction", "evenement", "listeTags", "listeFonctions", "listeDynasties", "modeVerbeux", $_GET) ;
if ($n == 0) {
	$n = extraction("rctags", "rcpersonnes", "rcdynastie", "rcfonction", "rcdatedepart", "rcdatefin", "rcrecherche", "rcfonction", "evenement", "listeTags", "listeFonctions", "listeDynasties", "modeVerbeux") ;
}

if ($modeVerbeux == 1) echo "Mode verbeux activé.<p></p>" ;

if ($modeVerbeux == 1) echo "<h1>Computation de la requête</h1>" ;

//dates
if ($rcdatedepart <> 0) {
	if ($rcdatefin == $rcdatedepart OR $rcdatefin == 0) {
		$condition .= " ((evenement.andebut <= $rcdatedepart AND evenement.anfin >= $rcdatedepart AND evenement.anfin <>0) OR evenement.andebut = $rcdatedepart) AND " ; 
	}
	else {
		$condition .= " ((evenement.andebut <= $rcdatedepart AND evenement.anfin >= $rcdatefin AND evenement.anfin <>0) OR (evenement.anfin >= $rcdatedepart AND evenement.anfin<= $rcdatefin AND evenement.anfin <> 0) OR (evenement.andebut >= $rcdatedepart AND evenement.andebut <= $rcdatefin)) AND " ; 
	}
}

//tags
if ($listeTags != "") {
	$isin = explode(",", $listeTags) ;		//la liste des tags à inclure dans la requête (is in)
	$tables .= ", lienel" ;
	
	$t = new tag() ;
	$t->select("WHERE num IN ($listeTags)") ;
	while ($t->next()) {
		if ($t->peres != "") {
			$peres = explode(",", $t->peres) ;
			foreach ($peres as $unPere) {
				if (!in_array($unPere, $isin)) $isin[] = $unPere ;
			}
		}
		if ($t->fils != "") {
			$fils = explode(",", $t->fils) ;
			foreach ($fils as $unFils) {
				if (!in_array($unFils, $isin)) $isin[] = $unFils ;
			}
		}
	}
	$isinTxt = implode(",", $isin) ;
	$condition .= "((lienel.tag IN ($isinTxt)) AND (evenement.num = lienel.evenement)) AND " ;
	if ($modeVerbeux == 1) echo "<br/>Ajout de conditions sur les tags. La liste tags est $listeTags. Recherche des tags qui sont dans $isinTxt <p></p>" ;
}

//personne
if ($rcpersonnes != "") {
	$p = new personne() ;
	if ($rcpersonnes == "alea") {
		$p->select("WHERE valide = 0 ORDER BY RAND() LIMIT 0,1") ;
		$p->next() ;
		$jsonpersonnealea = $p->json() ;
		$alea = 1 ;
	}
	elseif (is_numeric($rcpersonnes)) $p->get($rcpersonnes) ;
	else $p->get("nom", stripslashes($rcpersonnes)) ;
	if ($p->num > 0) {
		$tables .= ", lienep" ;
		$condition .= "(lienep.personne = $p->num and evenement.num = lienep.evenement) AND ";
		if ($modeVerbeux == 1) echo "<br /> Ajout de la personne $p->num. <p></p>" ;	
	}
}

//recherche libre

function ajoutrecherche($chaine) {
	$chaine = trim($chaine) ;
	return "evenement.description LIKE '%".$chaine."%' OR evenement.precisions LIKE '%".$chaine."%'" ;
}

if ($rcrecherche != "") {
	$rcrecherche = explode(",", $rcrecherche) ;
	$rcrecherche = array_map("ajoutrecherche", $rcrecherche) ;
	$rcrecherche = implode(" OR ", $rcrecherche) ;
	$condition .= "(".$rcrecherche.") AND " ;
	
	if ($modeVerbeux == 1) echo "<br /> Ajout de la recherche libre <p></p>" ;	
}

//évènement père

if ($evenement > 0) {
	if ($modeVerbeux == 1) echo "<br /> Ajout de la recherche d'un évènement père<p></p>" ;	
	$condition .= "(evenement.pere = $evenement OR evenement.num = $evenement) AND " ;
}

//fonction
if ($listeFonctions != "") {
	$condition .= "(fonction IN ($listeFonctions)) AND" ;
}

//dynastie
if ($listeDynasties != "") {
	$tables .= ", personne, lienep" ;
	//on prend la liste des évènements liés aux personnes qui sont dans la liste des dynasties
	$condition .= "(lienep.personne = personne.num AND evenement.num = lienep.evenement AND personne.dynastie IN ($listeDynasties)) AND " ;
}

if ($modeVerbeux == 1) echo $condition ;

$q = "$tables WHERE $condition 1 ORDER BY evenement.andebut, evenement.moisdebut, evenement.jourdebut ASC, evenement.numeroordre ASC, evenement.num ASC limit 0, 1000" ;

if ($modeVerbeux == 1) echo $q ;

$e->select($q) ;
$nev = $e->lenen() ;

function afficheTableauEvenement() {
	global $lesEvenements ;
	echo "<table><tr><td>Num</td><td>Date</td><td>Evenement</td><td>Lien EL</td><td>Contexte</td><td>Lien EP</td></tr>" ;
	for ($i = 0 ; $i < count($lesEvenements) ; $i++) {
		echo ("<tr><td>".$lesEvenements[$i]["num"]."</td><td>".$lesEvenements[$i]["andebut"]."</td><td>".substr($lesEvenements[$i]["description"], 0, 50)."</td>") ;
		echo ("<td>") ;
		for ($j = 0 ; $j < count($lesEvenements[$i]["lienEL"]) ; $j++) {
			echo $lesEvenements[$i]["lienEL"][$j]["tag"]." - " ;
		}
		echo ("</td><td>".$lesEvenements[$i]["contexte"]."</td><td>") ;
		for ($j = 0 ; $j < count($lesEvenements[$i]["lienEP"]) ; $j++) {
			echo $lesEvenements[$i]["lienEP"][$j]["personne"]." - " ;
		}
		echo("</td></tr>") ;
	}
	echo "</table><p></p>" ;
	return 1 ;
}
	
function afficheTableauPersonnes() {
	global $lesPersonnes ;
	echo "<h2>Personne</h2><table><tr><td>Num</td><td>Naissance</td><td>Mort</td><td>Nom</td><td>Règne</td></tr>" ;
	for ($i = 0 ; $i < count($lesPersonnes) ; $i++) {
		echo("<tr><td>".$lesPersonnes[$i]["num"]."</td><td>".$lesPersonnes[$i]["annaissance"]."</td><td>".$lesPersonnes[$i]["anmort"]."</td><td>".$lesPersonnes[$i]["nom"]."</td><td>".$lesPersonnes[$i]["regne"]."</td></tr>") ;
	}
	echo "</table><p></p>" ;
	return 1 ;
}
	
function getKeyEvt($num) {
	global $lesEvenements ;
	for ($i = 0 ; $i < count($lesEvenements) ; $i++) {
		if ($lesEvenements[$i]["num"] == $num) {
			return $i ;
		}
	}
	return -1 ;
}

function getKeyPersonne($num) {
	global $lesPersonnes ;
	for ($i = 0 ; $i < count($lesPersonnes) ; $i++) {
		if ($lesPersonnes[$i]["num"] == $num) {
			return $i ;
		}
	}
	return -1 ;
}

if ($modeVerbeux == 1) echo "Evénements : $nev </br>" ;


//1. Constitution du tableau des évènements
if ($nev > 0) {		//on a au moins un évènement

	if ($modeVerbeux == 1) echo "<h2>Constitution du tableau des évènements.</h2>" ;

	$lesEvenements 	= array() ;
	$lesPersonnes	= array() ;
	$isin = "" ;
	//while ($row = mysqli_fetch_assoc($e->iq)) {
	while ($row = mysqldb::$req->fetch()) {
		$lesEvenements[] = $row ;
		$isin .= $row["num"].", " ;
	}
	mysqldb::$req->closeCursor() ;
	$isin = substr($isin, 0, -2) ;
	$isin = "($isin)" ;

	//constitution du tableau des tags requis
	//$pTags = array() ;
	$c = 0 ;	//contexte par défaut
	$pTags = array() ;
	if ($listeTags != "") {
		$pTags = explode(",", $listeTags) ;
		if (count($pTags) > 0) $c = 1 ;
		if ($modeVerbeux == 1) {
			echo "Liste des tags : " ; 
			print_r($pTags) ;
		}
	}
	
	
	$nevcontexte = $nev ;
	for ($i = 0 ; $i < count($lesEvenements) ; $i++) {
		$lesEvenements[$i]["lienEL"] = array() ;
		$lesEvenements[$i]["lienEP"] = array() ;
		$lesEvenements[$i]["contexte"] = $c ;
	}

	if ($modeVerbeux == 1) afficheTableauEvenement() ;

	//2. Ajout des liens EL
	if ($modeVerbeux == 1) echo "<h2>Ajout des liens EL et contextualisation</h2>" ;

	$lienel = new lienel() ;
	$lienel->select("where evenement IN $isin") ;
	if ($lienel->lenen() > 0) {
		while ($lienel->next()) {
			$ne = getKeyEvt($lienel->evenement) ;
			$lesEvenements[$ne]["lienEL"][] = array("num" => $lienel->num, "tag" => $lienel->tag) ;
			if (in_array($lienel->tag, $pTags)) {
				$lesEvenements[$ne]["contexte"] = 0 ;
				$nevcontexte-- ;
			}
		}
	}
	if ($modeVerbeux == 1) afficheTableauEvenement() ;

	//3. Ajout des liens EP
	if ($modeVerbeux == 1) echo "<h2>Ajout des liens EP</h2>" ;
	
	$lienep = new lienep();
	$lienep->select(", personne where lienep.evenement IN $isin AND lienep.personne = personne.num ORDER BY annaissance, anmort") ;
	$isin2 = "" ;
	if ($lienep->lenen() > 0) {
		while ($lienep->next()) {
			$ne = getKeyEvt($lienep->evenement) ;
			$lesEvenements[$ne]["lienEP"][] = array("num" => $lienep->num, "personne" => $lienep->personne) ;
			$isin2 .= $lienep->personne.", " ;
		}
	}
	$isin2 = "(".substr($isin2, 0, -2).")" ;
	
	if ($modeVerbeux == 1) afficheTableauEvenement() ;
	
	//4. Constitution d'une liste de personnes
	if ($isin2 <> "()") {
		
		$p = new personne() ;
		$p->select("where num IN $isin2 ORDER BY nom") ;
		$isin3 = "" ;
		//while ($row = mysqli_fetch_assoc($p->iq)) {
		while ($row = mysqldb::$req->fetch()) {
			$lesPersonnes[]	= $row ;
			$isin3 .= $p->num.", " ;
		}
		mysqldb::$req->closeCursor() ;
	}
	
	//5. Vérification règne / non règne
	//quelles sont les personnes qui sont exclusivement liées à un règne
	for ($i = 0 ; $i < count($lesPersonnes) ; $i++) {
		$lesPersonnes[$i]["regne"] = 1 ;
		for ($j = 0 ; $j < count($lesEvenements) ; $j++) {
			if ($lesEvenements[$j]["typeeve"] <> 3) {
				for ($k = 0 ; $k < count($lesEvenements[$j]["lienEP"]) ; $k++) {
					if ($lesEvenements[$j]["lienEP"][$k]["personne"] == $lesPersonnes[$i]["num"]) {
						$lesPersonnes[$i]["regne"] = 0 ;
						break 2 ;
						//$k = count($lesEvenements[$j]["lienEP"]) ;
						//$j = count($lesEvenements) ;
					}
				}
			}
		}
	}
	
	if ($modeVerbeux == 1) afficheTableauPersonnes() ;
	//5. Ajout des évènements à la chaine JSON
	//6. Ajout des personnes à la chaine JSON
	$json = '{"nevcontexte" : "'.$nevcontexte.'", "evenements" : '.json_encode($lesEvenements).', "personnes": '.json_encode($lesPersonnes) ;
}
else {
	if ($rcpersonnes != "" && $p->num > 0) {
		$json = '{"nev":0, "evenements" : [], "personnes": ['.$p->json().']' ;
	}
	else $json = '{"nev":0, "evenements" : [], "personnes": []' ;
	
}
if ($alea == 1) $json .= ', "personneAlea":'.$jsonpersonnealea ;

$json .= "}" ;
if ($modeVerbeux == 1) echo ("<h1>JSON</h1>") ;
$time_end = microtime(true) ;
$time = $time_end - $time_start ;
if ($modeVerbeux == 1) echo ("Script exécuté en $time secondes.") ;
die($json) ;