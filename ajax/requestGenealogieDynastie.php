<?php
//produit les informations à afficher dans l'arbre généalogique
//$modeVerbeux = 0 ;
$modeVerbeux = 1 ;


/* fonctions */

function getPersonne($num) {
	global $lesPersonnes ;
	for ($i = 0 ; $i < count($lesPersonnes) ; $i++) {
		if ($lesPersonnes[$i]["num"] == $num) {
			return $lesPersonnes[$i] ;
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

function triAnNaissance($a, $b) {
	$personneA = getPersonne($a) ;
	$personneB = getPersonne($b) ;
	if ($personneA["annaissance"] > $personneB["annaissance"]) return 1 ;
	else return -1 ;
}

function affichePersonnes() {
	global $lesPersonnes, $modeVerbeux ;
	if ($modeVerbeux < 1) return 0 ;
	$chaine = '<p></p>Voilà comment à ce stade les personnes se présentent :<p></p><table border="1"><tr><td>#</td><td>Num</td><td>Nom</td><td>Sexe</td><td>Année de naissance</td><td>Père 1</td><td>Père 2</td><td>Enfants</td><td>Adoptés</td><td>Illégitimes</td><td>Fonctions</td></tr>' ;
	for ($i = 0 ; $i < count($lesPersonnes) ; $i++) {
		$chaine .= '<tr><td>'.$i.'</td><td>'.$lesPersonnes[$i]["num"].'</td><td>'.$lesPersonnes[$i]["nom"].'</td><td>'.$lesPersonnes[$i]["sexe"].'</td><td>'.$lesPersonnes[$i]["annaissance"].'</td><td>'.$lesPersonnes[$i]["pere1"].'</td><td>'.$lesPersonnes[$i]["pere2"].'</td><td>' ;
		for ($j = 0 ; $j < count($lesPersonnes[$i]["enfants"]) ; $j++) {
			$chaine .= $lesPersonnes[$i]["enfants"][$j].", " ;
		}
		$chaine .= '</td><td>' ;
		for ($j = 0 ; $j < count($lesPersonnes[$i]["enfantsAdoptifs"]) ; $j++) {
			$chaine .= $lesPersonnes[$i]["enfantsAdoptifs"][$j].", " ;
		}
		$chaine .= '</td><td>' ;
		for ($j = 0 ; $j < count($lesPersonnes[$i]["enfantsIllegitimes"]) ; $j++) {
			$chaine .= $lesPersonnes[$i]["enfantsIllegitimes"][$j].", " ;
		}
		$chaine .= '</td><td>' ;
		for ($j = 0 ; $j < count($lesPersonnes[$i]["fonctions"]) ; $j++) {
			$chaine .= $lesPersonnes[$i]["fonctions"][$j]["andebut"]." - ".$lesPersonnes[$i]["fonctions"][$j]["anfin"]." - ".$lesPersonnes[$i]["fonctions"][$j]["fonction"]." ," ;
		}
		$chaine .= '</td></tr>' ;
	}
	$chaine .= '</table><p></p><table>' ;
	echo $chaine ;
}


/* FIN DES FONCTIONS */

require_once("../inc/centrale.php") ;
extraction("dynastie", "modeVerbeux", $_POST) ;

//PREMIERE PARTIE - CAPTURE DES DONNEES INITIALES
if ($modeVerbeux == 1) echo "<h1>Capture des données initiales</h1>" ;

//1A - Constitution du isin initial
$dynasties = "(".implode($dynastie, ",").")" ;


//1B - Sélection des personnes appartenant à la dynastie concernée
$lesPersonnes 			= array()  ;		//la liste de toutes les personnes qu'on a requise. Cette liste est composée de tableaux avec les éléments suivants : num, nom, sexe, annaissance, json, pere1, pere2 et enfants. enfants est lui même un tableau qui comprend des références au numéro de chacun des enfants. pere1 et pere2 à 0 signifie qu'aucun parent n'est défini. Un nombre positif signifie qu'une parent est défini. Un nombre négatif signifie qu'un parent inconnu a été construit fictivement pour les besoins de l'arbre.
$lesPersonnesSimple 	= array () ;		//la liste de toutes les personnes qu'on a requise, avec uniquement leur numéro.
$lesPersonnesADemander 	= array() ;			//la liste des personnes qu'on n'a pas encore et qu'il faudra demander - POUR LE MOMENT, INUTILISEE.
$npf 					= -2 ;				//numéro de personne fictive. Par défaut, ils sont négatifs et on enlève 1 à chaque fois qu'on en ajoute une. On commence à -2
$isin 					= "(" ;				//on constitue une liste des personnes, pour pouvoir ensuite demander leurs liens PP.
$isinF					= "(" ;				//on constitue une liste des personnes de sexe féminin, pour ensuite obtenir leurs époux

$p = new personne ;
$p->select("WHERE dynastie IN $dynasties ORDER BY annaissance") ;
while ($p->next()) {
	$isin .= $p->num."," ;
	$lesPersonnes[] = array("num" => $p->num, "nom" => $p->nom, "sexe" => $p->sexe, "annaissance" => $p->annaissance, "anmort" => $p->anmort, "dynastie"=>$p->dynastie, "pere1" => 0, "pere2" => 0, "enfants" => array(), "enfantsAdoptifs" =>array(), "enfantsIllegitimes" => array(), "fonctions"=>array(), "url" => $p->url, "Range" => 0, "epoux" => array(), "notice" => $p->notice) ;
	$lesPersonnesSimple[] = $p->num ;
	if ($p->sexe == "f") $isinF .= $p->num."," ;
}
$isin = substr($isin, 0, -1).")" ;
$isinF = substr($isinF, 0, -1).")" ;
affichePersonnes() ;

//1B - Sélection des liens PP des personnes concernées
$lesRPP = array() ;						//la liste de toutes les relations de personnes. Chaque relation est représentée par un tableau (num, personne1, personne2, typerelation)
$lesRPPparents 	= array() ;				//la liste des relations de personnes de parentalité (personne1 est le FILS/la FILLE de personne2)
$lesRPPfreres 	= array() ;				//la liste des relations entre frères (personne1 est le FRERE / la soeur de personne2)
$lesRPPgrandsparents = array() ;		//la liste des relations de petit-fils à grand-parent
$lesRPPoncles	= array() ;				//la liste des relations oncles - neveux

$lpp = new relationpersonnes ;
$lpp->select("WHERE (personne1 IN $isin OR personne2 IN $isin) AND typerelation IN (1,2,4,6,7,11)") ; 
//$lpp->select("WHERE (personne1 IN $isin OR personne2 IN $isin) AND typerelation IN (1,2,4,5, 6,7,11)") ; //avec les enfants adoptifs
while ($lpp->next()) {
	$lesRPP[] = array("num" => $lpp->num, "personne1" => $lpp->personne1, "personne2" => $lpp->personne2, "typerelation" => $lpp->typerelation) ;
	if ($lpp->typerelation == "1" or $lpp->typerelation == "5" or $lpp->typerelation == "11") {
		$lesRPPparents[] = array("num" => $lpp->num, "personne1" => $lpp->personne1, "personne2" => $lpp->personne2, "typerelation" => $lpp->typerelation) ;
	}
	if ($lpp->typerelation == "2") {
		$lesRPPfreres[] = array("num" => $lpp->num, "personne1" => $lpp->personne1, "personne2" => $lpp->personne2, "typerelation" => $lpp->typerelation) ;
	}
	if ($lpp->typerelation == "6") {
		$lesRPPgrandsparents[] = array("num" => $lpp->num, "personne1" => $lpp->personne1, "personne2" => $lpp->personne2, "typerelation" => $lpp->typerelation) ;
	}
	if ($lpp->typerelation == "4") {
		$lesRPPoncles[] = array("num" => $lpp->num, "personne1" => $lpp->personne1, "personne2" => $lpp->personne2, "typerelation" => $lpp->typerelation) ;
	}
}

//1C - Récupération des époux des femmes des personnes concernées
if ($isinF != ")") {
	if ($modeVerbeux == 1)  echo "<h2>Récupération des époux</h2>" ;
	//Personne 1
	$q = "SELECT relationpersonnes.personne1, personne.num, personne.nom FROM relationpersonnes, personne WHERE relationpersonnes.personne1 IN $isinF AND relationpersonnes.typerelation = 3 AND relationpersonnes.personne2 = personne.num" ;
	$iq = mysqldb::send($q) ;
	//while ($row = mysql_fetch_assoc($iq)) {
	while ($row = mysqldb::$req->fetch()) {
		$idEpouse = getKeyPersonne($row["personne1"]) ;
		$lesPersonnes[$idEpouse]["epoux"][] = array("num" => $row["num"], "nom" => $row["nom"]) ;
		if ($modeVerbeux == 1) echo "Je rajoute un nouvel époux (".$row["nom"].") à ".$lesPersonnes[$idEpouse]["nom"]."<br/>" ;
	}
	//Personne 2
	$q = "SELECT relationpersonnes.personne2, personne.num, personne.nom FROM relationpersonnes, personne WHERE relationpersonnes.personne2 IN $isinF AND relationpersonnes.typerelation = 3 AND relationpersonnes.personne1 = personne.num" ;
	$iq = mysqldb::send($q) ;
	//while ($row = mysql_fetch_assoc($iq)) {
	while ($row = mysqldb::$req->fetch()) {
		$idEpouse = getKeyPersonne($row["personne2"]) ;
		$lesPersonnes[$idEpouse]["epoux"][] = array("num" => $row["num"], "nom" => $row["nom"]) ;
		if ($modeVerbeux == 1) echo "Je rajoute un nouvel époux (".$row["nom"].") à ".$lesPersonnes[$idEpouse]["nom"]."<br/>" ;
	}
}

//DEUXIEME PARTIE - TRAITEMENT DES INFORMATIONS CAPTUREES

if ($modeVerbeux == 1) echo "<h1>Traitement des informations capturées</h1>" ;

if ($modeVerbeux == 1) echo "<h2>Injection des liens de parentalité dans le tableau des personnes </h2>" ;

$lesPeresConnus = array() ;

//2A - Injection des liens de parentalité dans le tableau des personnes 
for ($j = 0 ; $j < count($lesRPPparents) ; $j++) {					//on prend 1 par 1 ces liens
	$leFils = getKeyPersonne($lesRPPparents[$j]["personne1"]) ;		//on chope le fils : sa place dans la tableau personne
	$lePere = getKeyPersonne($lesRPPparents[$j]["personne2"]) ;		//on chope le pere : sa place dans la tableau personne
	
	//1ère situation (normale) - parent et enfant sont connus et appartiennent à la même dynastie
	if ($leFils > -1 AND $lePere > -1) {
		if ($modeVerbeux == 1) echo $lesPersonnes[$leFils]["nom"]." a pour parent ".$lesPersonnes[$lePere]["nom"].". J'inscrit cette relation dans les deux personnes. <br />" ;
		//on injecte cette relation de parentalité dans les infos du fils
		if ($lesPersonnes[$leFils]["pere1"] == 0) {				//si le Père1 de ce fils n'a pas encore été défini
			$lesPersonnes[$leFils]["pere1"] = $lesPersonnes[$lePere]["num"] ;		//on le définit comme étant le num du père
		}
		else {														//s'il a déjà été défini
			$lesPersonnes[$leFils]["pere2"] = $lesPersonnes[$lePere]["num"] ;		//on définit alors Pere2
		}
		//on injecte cette relation de parentalité dans les infos du pere
		$lesPersonnes[$lePere]["enfants"][] = $lesPersonnes[$leFils]["num"] ;
		//on vérifie s'il s'agit d'un enfant illégitime ou adoptif
		if ($lesRPPparents[$j]["typerelation"] == 5) {
			//enfant adoptif => on l'ajoute à ce titre
			$lesPersonnes[$lePere]["enfantsAdoptifs"][] = $lesPersonnes[$leFils]["num"] ;
			if ($modeVerbeux == 1) echo "C'est un enfant adoptif : je l'ajoute en tant que tel en plus.<br/>" ;
		}
		if ($lesRPPparents[$j]["typerelation"] == 11) {
			//enfant illégitime => on l'ajoute à ce titre
			$lesPersonnes[$lePere]["enfantsIllegitimes"][] = $lesPersonnes[$leFils]["num"] ;
			if ($modeVerbeux == 1) echo "C'est un enfant illégitime : je l'ajoute en tant que tel en plus.<br/>" ;
		}
		
	}
	elseif ($leFils < 0 AND $lePere > -1) {
		//2ème situation - le parent appartient à la dynastie mais pas l'enfant.
		if ($lesPersonnes[$lePere]["sexe"] != "f") {
			//première explication : notre personnage est de sexe féminin. La dynastie s'arrête, pour elle et ses propres enfants appartiennent à la dynastie de son mari.
			//deuxième explication : il y a  un "décrochage" de dynastie, le fils créant une nouvelle dynastie (ex : les Valois, en considérant qu'on regarde les capétiens)
			if ($modeVerbeux == 1) echo "L'enfant de ".$lesPersonnes[$lePere]["nom"]." est connu mais n'appartient pas à notre dynastie : il en fonde donc une nouvelle ! On l'ajoute à la liste des personnes à charger dans un second temps et on le met comme enfant de ce père.<br/>" ;
			$lesPersonnesADemander[] = array("fils" => $lesRPPparents[$j]["personne1"], "pere" => $lesPersonnes[$lePere]["num"]) ;
			$lesPersonnes[$lePere]["enfants"][] = $lesRPPparents[$j]["personne1"] ;
		}
	}
	elseif ($leFils > -1 AND $lePere < 0) {
	//3ème situation - le fils appartient à la dynastie, mais pas le père - le fils crée donc une autre dynastie que celle qu'on étudie
		if ($modeVerbeux == 1) echo "Le parent de ".$lesPersonnes[$leFils]["nom"]." est connu (".$lesRPPparents[$j]["personne2"].") mais n'appartient pas à notre dynastie. Ce peut être sa mère mais nous pouvons aussi être à la fondation de la dynastie !<br/>" ;
		$lesPeresConnus[] = $lesRPPparents[$j] ; 	//on stocke ces noeux particuliers pour les exploiter en 2B.
	}
}

//2B - Chargement des fils dynastes
if ($modeVerbeux == 1) echo "<h2>Recherche des fils qui créent leurs propres dynasties</h2>" ;

if ($modeVerbeux == 1) echo " A ce stade, j'ai ".count($lesPersonnesADemander)." personnes qui pourraient être intéressantes à ajouter.<br/>" ;

if (count($lesPersonnesADemander) > 0) {
	if ($modeVerbeux == 1) echo "J'interroge MYSQL pour récupérer ces personnes supplémentaires.<br/>" ;
	$isin = "(" ;
	for ($i = 0 ; $i < count($lesPersonnesADemander) ; $i++) {
		$isin .= $lesPersonnesADemander[$i]["fils"]."," ;
	}
	$isin = substr($isin, 0, -1).")" ;
	$p->select("WHERE num IN $isin ORDER BY annaissance") ;
	while ($p->next()) {
		$lesPersonnes[] = array("num" => $p->num, "nom" => $p->nom, "dynastie" => $p->dynastie, "sexe" => $p->sexe, "annaissance" => $p->annaissance, "anmort" => $p->anmort, "pere1" => 0, "pere2" => 0, "enfants" => array(), "enfantsAdoptifs" =>array(), "enfantsIllegitimes" => array(), "fonctions"=>array(), "url" => $p->url, "Range" => 0) ;
		$lesPersonnesSimple[] = $p->num ;
	}
	//ensuite, on retraite
	if ($modeVerbeux == 1) echo "Maintenant, je les associe à leurs pères et je crée un fils fictif pour y accueillir la dynastie.<br/>" ;
	for ($i = 0 ; $i < count($lesPersonnesADemander) ; $i++) {
		//association avec le père
		$leFils = getKeyPersonne($lesPersonnesADemander[$i]["fils"]) ;		//on chope le fils : sa place dans la tableau personne
		$lesPersonnes[$leFils]["pere1"] = $lesPersonnesADemander[$i]["pere"] ;
		if ($lesPersonnes[$leFils]["dynastie"] > 0) {
			//ce fils a une autre dynastie - on crée une personne "dynastie", dont le JSON est spécialement configuré pour être traité par le javascript :  il est indiqué comme type "dynastie" et comprend le numéro de dynastie. Le JS, qui a accès à la totalité des dynasties, calculera le reste.
			$lesPersonnes[] = array("num" => $npf, "nom" => "Dynastie", "dynastie" => $lesPersonnes[$leFils]["dynastie"], "sexe"=>"", "annaissance" => "", "anmort" => "", "pere1" => $lesPersonnes[$leFils]["num"], "pere2" => 0, "enfants" => array(), "enfantsAdoptifs" =>array(), "enfantsIllegitimes" => array(), "fonctions"=>array(), "url" => "", "Range" => 0) ;
			//association avec le fils
			$lesPersonnes[$leFils]["enfants"][] = $npf ;
			$npf-- ;
		}
	}
}
affichePersonnes() ;

//il faudra ajouter : les grands parents, les frères
//2C - Recherche des pères des individus dont le père n'appartient pas à la dynastie, susceptibles d'être des têtes d'arbre.
if ($modeVerbeux == 1) echo "<h2>Recherche des pères des individus à ce jour sans père</h2>" ;
for ($i = 0 ; $i < count($lesPersonnes) ; $i++) {
	if ($lesPersonnes[$i]["pere1"] == 0) {		//on prend les personnes dont les parents ne sont pas définis
		if ($modeVerbeux == 1) echo $lesPersonnes[$i]["nom"]." n'a pas de père défini. Voyons sa situation.<br/>" ;
		//première possibilité - le père est connu mais n'appartient pas à la dynastie -> fondation d'une nouvelle dynastie
		//il faut donc passer en revue les Pères connus qu'on a stocké précédemment
		for ($j = 0 ; $j < count($lesPeresConnus) ; $j++) {
			if ($lesPeresConnus[$j]["personne1"] == $lesPersonnes[$i]["num"]) {
				$perenum = getKeyPersonne($lesPeresConnus[$j]["personne2"]) ;
				if ($perenum < 0) {
					//ce noeud concerne bien cette personne et le père n'a pas été chargé par ailleurs
					$lePere = new personne() ;
					$lePere->get($lesPeresConnus[$j]["personne2"]) ;
					if ($lePere->sexe != "f") {
						if ($modeVerbeux == 1) echo "Son père est connu (". $lePere->nom.") mais ne fait pas partie de la dynastie en cours. Je le charge et je lui ajoute les informations concernant son fils, tout en modifiant celui-ci en indiquant qui est son père.<br/>" ;
						//il est possible que le père n'ait pas de dynastie du tout : c'est une erreur à corriger (du moins, on le postule)
						if ($lePere->dynastie == 0) {
							$lePere->dynastie = $lesPersonnes[$i]["dynastie"] ;
							$lePere->save() ;
							if ($modeVerbeux == 1) echo "Ce père était sans dynastie. Je rectifie la base.<br/>" ;
						}
						$lesPersonnes[] = array("num" => $lePere->num, "nom" => $lePere->nom, "sexe" => $lePere->sexe, "dynastie"=> $lePere->dynastie, "annaissance" => $lePere->annaissance, "anmort" => $lePere->anmort, "json" => $lePere->json(), "pere1" => 0, "pere2"=>0, "enfants" => array($lesPersonnes[$i]["num"]), "enfantsAdoptifs" =>array(), "enfantsIllegitimes" => array(), "fonctions"=>array(), "url" => "") ;
						$lesPersonnes[$i]["pere1"] = $lePere->num ;
					}
				}
				else {
					if ($modeVerbeux == 1) echo "le père a été chargé par ailleurs mais n'appartient pas à cette dynastie. On fait les liens.<br/>" ;
					$lesPersonnes[$perenum]["enfants"][] = $lesPersonnes[$i]["num"] ;
					$lesPersonnes[$i]["pere1"] = $lesPersonnes[$perenum]["num"] ;
					
					
				}
			}
		}
	}
}

affichePersonnes() ;

//2D - Recherche des grands parents
if ($modeVerbeux == 1) echo "<h2>Recherche des grands parents des individus à ce jour sans père</h2>" ;
for ($i = 0 ; $i < count($lesPersonnes) ; $i++) {
	if ($lesPersonnes[$i]["pere1"] ==  0) {		//on prend les personnes dont les parents ne sont toujours pas définis.
		if ($modeVerbeux == 1) echo $lesPersonnes[$i]["nom"]." n'a pas de père défini dans cette dynastie. Ce pourrait donc être une tête d'arbre mais peut être a-t-il un grand père dans cette dynastie ?<br/>" ;
		for ($j = 0 ; $j < count($lesRPPgrandsparents) ; $j++) {
			if ($lesRPPgrandsparents[$j]["personne1"] == $lesPersonnes[$i]["num"]) {
				if ($modeVerbeux == 1) echo $lesRPPgrandsparents[$j]["personne2"]." est un grand-parent. Est-il dans notre dynastie ??<br/>" ;
				$gp = getKeyPersonne($lesRPPgrandsparents[$j]["personne2"]) ;
				if ($gp > -1) {
					if ($modeVerbeux == 1) echo "En effet, il en fait bien partie ! On va pouvoir les relier tous les deux. Tout d'abord, on crée une personne fictive qui a pour père le grand-père et et pour fils le petit-fils.<p></p> " ;
					$lesPersonnes[] = array("num" => $npf, "nom" => "?", "sexe" => "h", "annaissance" => "", "anmort" => "", "pere1"=>$lesRPPgrandsparents[$j]["personne2"], "pere2" => 0, "enfants" => array($lesPersonnes[$i]["num"]), "enfantsAdoptifs" =>array(), "enfantsIllegitimes" => array(), "fonctions"=>array(), "url" => "") ;
					//"json"=>'{"num":'.$npf.', "nom":"Père de '.$lesPersonnes[$i]["nom"].'"}', 
					//on ajoute les liens PP - bien que je ne pense pas qu'on en ait besoin !
					$lesRPP[] = array("num" => -1, "personne1" => $lesRPPgrandsparents[$j]["personne1"], "personne2" => $npf, "typerelation" => "1") ;
					$lesRPP[] = array("num" => -1, "personne1" => $npf, "personne2" => $lesRPPgrandsparents[$j]["personne2"], "typerelation" => "1") ;
					//on ajoute cet enfant inconnu au grand-père
					$lesPersonnes[$gp]["enfants"][] = $npf ;
					//on ajoute ce parent inconnu au petit-fils
					$lesPersonnes[$i]["pere1"] = $npf ;
					$npf-- ;
					$j =  count($lesRPPgrandsparents) ;
				}
			}
		}
	}
}

affichePersonnes() ;

//2E - Recherche des oncles et neveux
if ($modeVerbeux == 1) echo "<h2>Recherche des oncles des individus à ce jour sans père</h2>" ;
for ($i = 0 ; $i < count($lesPersonnes) ; $i++) {
	if ($lesPersonnes[$i]["pere1"] ==  0) {		//on prend les personnes dont les parents ne sont toujours pas définis.
		if ($modeVerbeux == 1) echo $lesPersonnes[$i]["nom"]." n'a pas de père défini dans cette dynastie. Ce pourrait donc être une tête d'arbre mais peut être a-t-il un oncle dans cette dynastie ?<br/>" ;
		for ($j = 0 ; $j < count($lesRPPoncles) ; $j++) {
			if ($lesRPPoncles[$j]["personne1"] == $lesPersonnes[$i]["num"]) {	//p1 est le neveu de p2
				if ($modeVerbeux == 1) echo $lesRPPoncles[$j]["personne2"]." est un oncle. Est-il dans notre dynastie ??<br/>" ;
				$oncle = getKeyPersonne($lesRPPoncles[$j]["personne2"]) ;
				if ($oncle > -1) {
					//l'oncle est bien dans la dynastie. A ce stade, deux options : l'oncle lui-même n'a pas de père défini ou alors il en a un.
					if ($lesPersonnes[$oncle]["pere1"] == 0) {
						//pas de père défini pour l'oncle.
						if ($modeVerbeux == 1) echo "L'oncle appartient à la dynastie mais n'a pas de père défini ! On va les relier tous les deux, en créant deux personnes fictives : le père de p1 et le grand père de p1 et père de p2.<br/>";
						//le père de p1
						$lesPersonnes[] = array("num" => $npf, "nom" => "Père de ".$lesPersonnes[$i]["nom"], "sexe" => "h", "annaissance" => "", "pere1"=>$npf-1, "pere2" => 0, "enfants" => array($lesPersonnes[$i]["num"]), "enfantsAdoptifs" =>array(), "enfantsIllegitimes" => array(), "fonctions"=>array(), "url" => "") ;
						//"json"=>'{"num":'.$npf.', "nom":"Père de '.$lesPersonnes[$i]["nom"].'"}', 
						//on ajoute ce père inconnu à p1
						$lesPersonnes[$i]["pere1"] = $npf ;
						$npf-- ;
						//le grand père commun
						$lesPersonnes[] = array("num" => $npf, "nom" => "?", "sexe" => "h", "annaissance" => "", "pere1"=>0, "pere2" => 0, "enfants" => array($npf+1, $lesRPPoncles[$j]["personne2"]), "fonctions"=>array(), "url" => "") ;
						//"json"=>'{"num":'.$npf.', "nom":"inconnu"}', 
						//on ajoute ce père inconnu à p2
						$lesPersonnes[$oncle]["pere1"] = $npf ;
						$npf-- ;
					}
					else {
						//l'oncle a un père défini
						if ($modeVerbeux == 1) echo "L'oncle appartient à la dynastie et a un de père défini ! On va les relier tous les deux, en créant une personnes fictives : le père de p1 et le frère de p2.<br/>";
						//le père de p1
						$lesPersonnes[] = array("num" => $npf, "nom" => "Père de ".$lesPersonnes[$i]["nom"], "sexe" => "h", "annaissance" => "", "pere1"=> $lesPersonnes[$oncle]["pere1"], "pere2" => 0, "enfants" => array($lesPersonnes[$i]["num"]), "enfantsAdoptifs" =>array(), "enfantsIllegitimes" => array(), "fonctions"=>array(), "url" => "") ;
						//"json"=>'{"num":'.$npf.', "nom":"Père de '.$lesPersonnes[$i]["nom"].'"}', 
						//on ajoute ce père inconnu à p1
						$lesPersonnes[$i]["pere1"] = $npf ;
						//on ajoute ce nouveau personnage comme enfant du grand-père 
						$gp = getKeyPersonne($lesPersonnes[$oncle]["pere1"]) ;
						$lesPersonnes[$gp]["enfants"][] = $npf ;
						$npf-- ;
					}
				}
			}
		}
	}
}
affichePersonnes() ;
						

//2F - Recherche des frères des individus dont le père n'appartient pas à la dynastie, afin de reconstituer une tête d'arbre
//PARTIE 2D A TESTER !
if ($modeVerbeux == 1) echo "<h2>Recherche des frères des individus à ce jour sans père</h2>" ;
for ($i = 0 ; $i < count($lesPersonnes) ; $i++) {
	if ($lesPersonnes[$i]["pere1"] ==  0) {		//on prend les personnes dont les parents ne sont toujours pas définis.
		if ($modeVerbeux == 1) echo $lesPersonnes[$i]["nom"]." n'a pas de père défini dans cette dynastie. Ce pourrait donc être une tête d'arbre mais peut être a-t-il un frère ?<br/>" ;
		$flag = 0 ;
		for ($j = 0 ; $j < count($lesRPPfreres) ; $j++) {
			if (($lesRPPfreres[$j]["personne1"] == $lesPersonnes[$i]["num"]) || ($lesRPPfreres[$j]["personne2"] == $lesPersonnes[$i]["num"]))  {
				$numFrere = $lesRPPfreres[$j]["personne1"] ; 
				if ($numFrere == $lesPersonnes[$i]["num"]) $numFrere = $lesRPPfreres[$j]["personne2"] ;
				//on a effectivement un frère
				//sous étape 2Ca - recherche d'un père commun qui est déjà dans la dynastie
				if ($modeVerbeux == 1) echo "Il a effectivement un frère ($numFrere). Est-il dans cette dynastie ? <br/>" ;
				$leFrere = getKeyPersonne($numFrere) ;
				if ($leFrere > -1) {			//oui, il y est !
					$lePere = getKeyPersonne($lesPersonnes[$leFrere]["pere1"]) ;	//le 1er parent de ce frère est-il dans la dynastie ?
					if ($lePere > -1) {								// oui !
						if ($lesPersonnes[$lePere]["sexe"] == "h") {	//est-ce bien un homme ?
							//et c'est bien le père. On crée donc la relation RPP manquante.
							if ($modeVerbeux == 1) echo "J'ai retrouvé le père (.".$lesPersonnes[$lePere]["num"].") de ce frère.</br>" ;
							$lesRPP[] = array("num" => -1, "personne1" => $lesPersonnes[$i]["num"], "personne2" => $lesPersonnes[$lePere]["num"], "typerelation" => "1") ;
							$lesRPPparents[] = array("num" => -1, "personne1" => $lesPersonnes[$i]["num"], "personne2" => $lesPersonnes[$lePere]["num"], "typerelation" => "1") ;
							//et on modifie le fils comme le père
							$lesPersonnes[$lePere]["enfants"][] = $lesPersonnes[$i]["num"] ;	//côté père
							$lesPersonnes[$i]["pere1"] = $lesPersonnes[$lePere]["num"] ;		//côté père
							$flag = 1 ;
							$j = count($lesRPPfreres) ;
						}
						else {
							//ce n'est pas un homme - peut-être qu'il faut tenter avec pere2 ?
							$lePere = getKeyPersonne($leFrere["pere2"]) ;	//le 2e parent de ce frère est-il dans la dynastie ?
							if ($lePere > -1) {	//oui !
								if ($lesPersonnes[$lePere]["sexe"] == "h") {	//est-ce bien un homme ? si non, il y a un pb de données !
									if ($modeVerbeux == 1) echo "J'ai retrouvé le père (.".$lesPersonnes[$lePere]["num"].") de ce frère.</br>" ;
									$lesRPP[] = array("num" => -1, "personne1" => $lesPersonnes[$i]["num"], "personne2" => $lesPersonnes[$lePere]["num"], "typerelation" => "1") ;
									$lesRPPparents[] = array("num" => -1, "personne1" => $lesPersonnes[$i]["num"], "personne2" => $lesPersonnes[$lePere]["num"], "typerelation" => "1") ;
									//et on modifie le fils comme le père
									$lesPersonnes[$lePere]["enfants"][] = $lesPersonnes[$i]["num"] ;	//côté père
									$lesPersonnes[$i]["pere2"] 			= $lesPersonnes[$lePere]["num"] ;		//côté père - a tester si besoin !
									$flag = 1 ;
									$j = count($lesRPPfreres) ;
								}
								else {
									if ($modeVerbeux == 1) echo "Problème de données. Les deux parents de $numFrere sont définis comme de sexe féminins ou n'ont pas de définition de genre." ;
								}
							}
						}
					}
					if ($flag == 0) {
						//on a bien un frère mais pas de père. Il faut donc établir le père fictivement, comme tête de l'arbre.
						if ($modeVerbeux == 1) echo ("on a bien un frère mais pas de père. Il faut donc établir le père fictivement, comme tête de l'arbre.") ;
						$lesPersonnes[$i]["pere1"] = $npf ;
						$lesPersonnes[$leFrere]["pere1"] = $npf ; 
						$lesPersonnes[] = array("num" => $npf, "nom" => "?", "sexe" => "h", "annaissance" => "0", "pere1" => 0, "pere2"=>0, "enfants" => array($lesPersonnes[$i]["num"], $numFrere), "enfantsAdoptifs" =>array(), "enfantsIllegitimes" => array(), "fonctions"=>array(), "url" => "") ;
						//"json"=>'{"num":'.$npf.', "nom":"inconnu"}', 
						$lesRPP[] = array("num" => -1, "personne1" => $lesPersonnes[$i]["num"], "personne2" => $npf, "typerelation" => "1") ;
						$lesRPPparents[] = array("num" => -1, "personne1" => $lesPersonnes[$i]["num"], "personne2" => $npf, "typerelation" => "1") ;
						$lesRPP[] = array("num" => -1, "personne1" => $numFrere, "personne2" => $npf, "typerelation" => "1") ;
						$lesRPPparents[] = array("num" => -1, "personne1" => $numFrere, "personne2" => $npf, "typerelation" => "1") ;
						$npf-- ;
						if ($modeVerbeux == 1) echo "Je n'ai pas retrouvé de père commun à ces deux frères => je le construis fictivement.<br>" ;
						$flag = 1 ;
						$j = count($lesRPPfreres) ;
					}
				}
				else {
					if ($modeVerbeux == 1) echo "Ce frère existe mais ne fait pas partie de la dynastie : on ne peut en tenir compte.<br/>" ;
				}
			}
		}
	}
}

affichePersonnes() ;

//2F - Dernière recherche des têtes d'arbre
if ($modeVerbeux == 1) echo "<h2>Recherche des personnes dont il ne reste vraiment plus de père défini.</h2>" ;

$listeSansParents = array() ;		//la liste des personnes sans parents (liste de num)

for ($i = 0 ; $i < count($lesPersonnes) ; $i++) {
	if ($lesPersonnes[$i]["pere1"] == 0) {
		if ($modeVerbeux == 1) echo "Après analyse, ".$lesPersonnes[$i]["nom"]." n'a pas de père défini dans cette dynastie, ni de frère. C'est donc être une tête d'arbre.<br/>" ;
		$listeSansParents[] = $lesPersonnes[$i]["num"] ;
	}
}

//2G - On nettoie les personnes qui ont deux parents
if ($modeVerbeux == 1) echo "<h2>Nettoyage des individus ayant deux parents</h2>" ;
for ($i = 0 ; $i < count($lesPersonnes) ; $i++) {
	if ($lesPersonnes[$i]["pere1"] > 0 and $lesPersonnes[$i]["pere2"] > 0) {	
		if ($modeVerbeux == 1) echo "Les deux parents de ".$lesPersonnes[$i]["nom"]." appartiennent à cette dynastie. Il y a un peu d'endogamie là dedans. On corrige celà. <br/>" ;
		//cette personne a deux parents - il y a fort à parier qu'il y a une endogamie dynastique. L'un d'eux est le père, l'autre est la mère. Seul le père doit nous intéresser.
		//on le recherche
		$pere1 = getKeyPersonne($lesPersonnes[$i]["pere1"]) ;
		$pere2 = getKeyPersonne($lesPersonnes[$i]["pere2"]) ;
		if ($lesPersonnes[$pere1]["sexe"] == "h" and $lesPersonnes[$pere2]["sexe"] != "h") {
			if ($modeVerbeux == 1) echo "Le vrai père, c'est ".$lesPersonnes[$i]["pere1"].". J'en tire les conséquences, notamment sur les ".count($lesPersonnes[$pere2]["enfants"])." enfants de cette personne.<br/>" ;
			//le vrai père c'est père 1
			$lesPersonnes[$i]["pere2"] = 0 ;	//on enlève la définition côté enfant
			for ($j = 0 ; $j < count($lesPersonnes[$pere2]["enfants"]) ; $j++) {
				if ($lesPersonnes[$pere2]["enfants"][$j] == $lesPersonnes[$i]["num"]) {
					array_splice($lesPersonnes[$pere2]["enfants"], $j, 1) ;
					$j = count($lesPersonnes[$pere2]["enfants"]) + 1 ;
				}
			} //et côté parent
		}
		elseif ($lesPersonnes[$pere2]["sexe"] == "h" and $lesPersonnes[$pere1]["sexe"] != "h") {
			if ($modeVerbeux == 1) echo "Le vrai père, c'est ".$lesPersonnes[$i]["pere2"].". J'en tire les conséquences.<br/>" ;
			//le vrai père c'est père 2 - on le met en père 1
			$lesPersonnes[$i]["pere1"] = $lesPersonnes[$i]["pere2"] ;	//on enlève la définition côté enfant
			$lesPersonnes[$i]["pere2"] = 0 ;
			for ($j = 0 ; $j < count($lesPersonnes[$pere1]["enfants"]) ; $j++) {
				if ($lesPersonnes[$pere1]["enfants"][$j] == $lesPersonnes[$i]["num"]) {
					array_splice($lesPersonnes[$pere1]["enfants"], $j, 1) ;
					$j = count($lesPersonnes[$pere1]["enfants"]) + 1 ;
				}
			} //et côté parent			
		}
	}
}

//2H - Tri des listes d'enfants, dans un ordre chronologique
if ($modeVerbeux == 1) echo "<h2>Tri des grappes d'enfants</h2>" ;

for ($i = 0 ; $i < count($lesPersonnes) ; $i++) {
	if (count($lesPersonnes[$i]["enfants"]) > 1) {
		$tableau = usort($lesPersonnes[$i]["enfants"], "triAnNaissance") ;
	}
}

affichePersonnes() ;

//TROISIEME PARTIE - RECUPERATION DES FONCTIONS ASSOCIEES

//3.1. Reconstitution d'une liste ISIN

if ($modeVerbeux == 1) echo "<h1>Chargement des fonctions</h1>" ;
$isin2 = "(" ;
for ($i = 0 ; $i < count($lesPersonnes) ; $i++) {
	$isin2 .= $lesPersonnes[$i]["num"]."," ;
}
$isin2 = substr($isin2, 0, -1).")" ;
$q = "SELECT evenement.num, evenement.andebut, evenement.anfin, evenement.fonction, lienep.personne FROM evenement, lienep WHERE evenement.num = lienep.evenement AND lienep.personne IN $isin2 AND fonction > 0 ORDER BY andebut" ;
if ($modeVerbeux == 1)  echo "Requête : $q <p></p>" ;
$iq = mysqldb::send($q) ;
//while ($row = mysqli_fetch_assoc($iq)) {
while ($row = mysqldb::$req->fetch()) {
	$p = getKeyPersonne($row["personne"]) ;
	$lesPersonnes[$p]["fonctions"][] = $row ;
	if ($modeVerbeux == 1) echo "Je rajoute une nouvelle fonction à ".$lesPersonnes[$p]["nom"]."<br/>" ;
}

affichePersonnes() ;

//TROISIEME PARTIE - CONSTRUCTION DE L'ARBRE ET DU JSON
/*à ce stade, les opérations normalement ne sont plus que mécaniques et il n'y a plus grand calcul à faire. Il s'agit juste de traduire les données qu'on a obtenu
et retraité sous forme d'un JSON exploitable par le JS.
Ce JSON comprend plusieurs objets :
{"arbres" : qui comprend une représentation graphique d'arbe (cf. infra),
"couples": qui comprend les différents couples (pere, fils)
"dynastie": pour mémoire, le numéro de la dynastie
"log" : qui ne sert pas là}
Le javascript va utiliser arbres pour dessiner les boîtes et les couples pour les relier entre elles.
*/

if ($modeVerbeux == 1) echo "<h1>Construction de l'arbre</h1>" ;

if ($modeVerbeux == 1) echo "<h2>Construction de l'arbre</h2>" ;	
//$jsonCouples = '"couples":[' ;

$arbre = array() ;	//arbre est un tableau multidimensionnel qui décrit la structure généalogique
//sa première dimension correspond à une branche (chaque tête d'arbre identifiée a une branche)
//sa 2e dimension correspond à la génération
//sa 3e dimension correspond à la personne, stockée sous forme de JSON

function descenteRecursive($pere, $ntableau, $generation) {
	global $arbre, $jsonCouples, $modeVerbeux, $lesPersonnes ;
	$pereId = getKeyPersonne($pere) ;		//on cherche le # du père
	if ($modeVerbeux == 1) echo "Je traite les ".count($lesPersonnes[$pereId]["enfants"])." enfant(s) de ".$lesPersonnes[$pereId]["nom"]."<br/>" ;
	for ($i = 0 ; $i < count($lesPersonnes[$pereId]["enfants"]) ; $i++) {
		//on prend ses enfants
		$enfant = getKeyPersonne($lesPersonnes[$pereId]["enfants"][$i]) ;	//on chope le # de cet enfant
		if ($modeVerbeux == 1) echo "Parmi les enfants de ".$lesPersonnes[$pereId]["nom"]." (".$lesPersonnes[$pereId]["num"]."), j'ai identifié un enfant : ".$lesPersonnes[$enfant]["nom"].".</br>" ;
		$arbre[$ntableau][$generation][] = json_encode($lesPersonnes[$enfant]) ;		//on ajoute cet élément dans le tableau arbre
		$n = descenteRecursive($lesPersonnes[$enfant]["num"], $ntableau, $generation+1) ;	//on appelle récursivement la fonction avec cet enfant comme père et une génération supplémentaire
	}
}

for ($i = 0 ; $i < count($listeSansParents) ; $i++) {
	//on prend une à une les têtes d'arbre qu'on a identifié en II.
	if ($modeVerbeux == 1) echo "<p></p>Je fouille maitenant la descendance de ".$listeSansParents[$i]["nom"].".<br/>" ;
	$arbre[$i] = array() ;
	$pere = getKeyPersonne($listeSansParents[$i]) ;
	$arbre[$i][0][0] = json_encode($lesPersonnes[$pere]) ;		//on définit le premier niveau comme étant la tête de l'arbre.
	descenteRecursive($listeSansParents[$i], $i, 1) ;
}

$json = '{"arbres":[' ;

if ($modeVerbeux == 1) echo "<h2>Construction du JSON</h2>" ;	

for ($i = 0 ; $i < count($arbre) ; $i++) {		//on va parcourir l'arbre de bout en bout
	$ngen = count($arbre[$i]) ;
	$json .= '{"num":"'.$i.'", "ngeneration":'.$ngen.', "generations":[' ;
	for ($j = 0 ; $j < $ngen ; $j++) {
		$nper = count($arbre[$i][$j]) ;
		$json .= '{"generation":"'.$j.'", "npersonnes":'.$nper.', "personnes":[' ;
		for ($k = 0 ; $k < $nper ; $k++) {
			$json .= $arbre[$i][$j][$k].',' ;
		}
		$json = substr($json, 0, -1).']},' ;
	}
	$json = substr($json, 0, -1).']},' ;
}
//$json = substr($json, 0, -1).'], '.$jsonCouples.', "log":"'.$log.'"}' ;
$json = substr($json, 0, -1).']}' ;

//à la fin de l'envoi, je touche
die($json) ;
?>
