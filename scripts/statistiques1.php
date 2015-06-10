<?php
//script qui affiche quelques statistiques
$time_start = microtime(true);
require_once("../inc/centrale.php") ;
echo "<h2>Statistiques sur la base de donnée.</h2>" ;

$q = 'SELECT num, description, precisions FROM evenement where andebut < 476';
$q = mysqldb::send($q) ;

$mots = array() ;
$occurences = array() ;
$compteur = 0 ;

while ($row = mysql_fetch_array($q)) {
	$description = $row[1].$row[2] ;
	$lesmots = preg_split("/[\s,\\.\\(\\)\'\’]+/", $description) ;
	foreach ($lesmots as $unmot) {
		$unmot = trim($unmot) ;
		if (strlen($unmot) > 4) {
			if (!in_array($unmot, $mots)) {
				$mots[$compteur] = $unmot ;
				$occurences[$compteur] = 1 ;
				$compteur++ ;
			}
			else {
				for ($i = 0 ; $i < $compteur ; $i++) {
					if ($mots[$i] == $unmot) {
						$occurences[$i]++ ;
						$i = $compteur ;
					}
				}
			}
		}
	}
}

echo "<p></p>A ce stade, le dictionnaire compte désormais ".count($mots)." mots. Je vais maintenant les retraiter." ;

//1. Premier travail : élimination des majuscules inutiles
for ($i = 0 ; $i < $compteur ; $i++) {
	if (strtolower(substr($mots[$i], 0, 1)) != substr($mots[$i], 0, 1)) {
		if (in_array(strtolower($mots[$i]), $mots)) {
			//echo $mots[$i]." existe aussi en minuscule. Je fusionne... </br>" ;
			for ($j = $i ; $j < $compteur ; $j++) {
				if ($mots[$j] == strtolower($mots[$i])) {
					$occurences[$j] += $occurences[$i] ;
					$mots[$i] = "" ;
					$occurences[$i] = -1 ;
					$j = $compteur ;
				}
			}
		}
	}
}

//2. Second travail : élimination des pluriels inutiles
for ($i = 0 ; $i < $compteur ; $i++) {
	if (strtolower(substr($mots[$i], -1)) == "s") {
		if (in_array(substr($mots[$i], 0, -1), $mots)) {
			//echo $mots[$i]." existe aussi au singulier. Je fusionne... </br>" ;
			for ($j = $i ; $j < $compteur ; $j++) {
				if ($mots[$j] == substr($mots[$i], 0, -1)) {
					$occurences[$j] += $occurences[$i] ;
					$mots[$i] = "" ;
					$occurences[$i] = -1 ;
					$j = $compteur ;
				}
			}
		}
	}
}

echo "<p></p>Voici maintenant ma sélection :" ;
for ($i = 0 ; $i < $compteur ; $i++) {
	if ($mots[$i] != "" && $occurences[$i] > 4) {
		echo("<li>".$mots[$i]." (".$occurences[$i].")</li>") ;
	}
}

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "<br/>Le script a bien été exécuté, en $time secondes." ;

?>
