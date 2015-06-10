<?php
//écriture de l'ensemble des liens tags pères fils
require_once("../inc/centrale.php") ;
$modeVerbeux = 0 ;
$n = extraction($modeVerbeux, $_GET) ;
$peres 	= array() ;
$fils 	= array() ;
$peresetendus 	= array() ;
$filsetendus 	= array() ;
$tags	= array() ;
$t = new tag() ;
$t->select() ;
$maxtags = 0 ;
while ($t->next()) {
	$tags[$t->num] = $t->nom ;
	$maxtags = max($maxtags, $t->num) ;
	$peres[$t->num] = array() ;
	$fils[$t->num] = array() ;
	$peresetendus[$t->num] = array() ;
	$filsetendus[$t->num] = array() ;
}
if ($modeVerbeux == 1) echo "Mode verbeux activé.<p></p>" ;
if ($modeVerbeux == 1) echo "Il y a ".count($tags)." tags alors que le dernier tag est à l'indice $maxtags .<br>" ;

function affichageTags() {
	global $tags, $peres, $fils, $peresetendus, $filsetendus, $maxtags ;
	echo "<p></p><table border='1'><tr><td>Num</td><td>Nom</td><td>Pères</td><td>Pères étendus</td><td>Fils</td><td>Fils étendus</td></tr>" ;
	for ($i = 0 ; $i < $maxtags ; $i++) {
		if (isset($tags[$i])) {
			echo "<tr><td>$i</td><td>".$tags[$i]."</td><td>" ;
			if (isset($peres[$i])) {
				//echo implode(", ", $peres[$i]) ;
				affichageTableaudeTags($peres[$i]) ;
			}
			echo "</td><td>" ;
			if (isset($peresetendus[$i])) {
				//echo implode(", ", $peresetendus[$i]) ;
				affichageTableaudeTags($peresetendus[$i]) ;
			}
			echo "</td><td>" ;
			if (isset($fils[$i])) {
				//echo implode(", ", $fils[$i]) ;
				affichageTableaudeTags($fils[$i]) ;
			}
			echo "</td><td>" ;
			if (isset($filsetendus[$i])) {
				//echo implode(", ", $filsetendus[$i]) ;
				affichageTableaudeTags($filsetendus[$i]) ;
			}
			echo "</td></tr>" ;
		}
	}
	echo "</table>" ;
}

function affichageTableaudeTags($tableau) {
	global $tags, $maxtags ;
	for ($i = 0 ; $i < $maxtags ; $i++) {
		if (isset($tableau[$i])) {
			echo "<li>".$tags[$tableau[$i]]." ($tableau[$i])</li>" ;
		}
	}
}

$lientt = new lientt() ;
$lientt->select() ;
while ($lientt->next()) {
	//lientt tag1 est le père
	//tag2 est le fils
	if ($lientt->tag2 == $lientt->tag1) {
		if ($modeVerbeux == 1) echo "Il y a un problème pour le lien tt $lientt->num pour lequel père et fils sont identiques ($lientt->tag2). Il faut l'effacer ! <br/>" ;
		$lientt->delete() ;
	}
	else {
		if (in_array($lientt->tag1, $peres[$lientt->tag2])) {
			if ($modeVerbeux == 1) echo "Il y a un doublon pour le lien tt $lientt->num ($lientt->tag2), qui existe déjà. Il faut l'effacer ! <br/>" ;
			$lientt->delete() ;
		}
		else {
			$peres[$lientt->tag2][] = $lientt->tag1 ;
		}
		if (in_array($lientt->tag2, $peres[$lientt->tag1])) {
			if ($modeVerbeux == 1) echo "Il y a un doublon pour le lien tt $lientt->num ($lientt->tag2), qui existe déjà. Il faut l'effacer ! <br/>" ;
			$lientt->delete() ;
		}
		else {
			$fils[$lientt->tag1][] = $lientt->tag2 ;
		}
	}
}

if ($modeVerbeux == 1) echo "<h1>Premier état</h1>" ;
if ($modeVerbeux == 1) affichageTags() ;

//travail sur les pères
for ($i = 0 ; $i < $maxtags ; $i++) {
	$peresetendus[$i] = peresRecursifs(array(), $i) ;
	sort($peresetendus[$i]) ;
}

function peresRecursifs($tableau, $t) {
	global $peres ;
	//echo "Je cherche les pères récursifs du tag $t. <br/>" ;
	if (!isset($peres[$t])) return $tableau ;
	//echo "Celui-ci indique comme pères ".implode(", ", $peres[$t]).". Je les explore. <br/>" ;
	for ($i = 0 ; $i < count($peres[$t]) ; $i++) {
		if (!in_array($peres[$t][$i], $tableau)) {
			$tableau[] = $peres[$t][$i] ;
			$tableau = peresRecursifs($tableau, $peres[$t][$i]) ;
		}
	}
	return $tableau ;
}

//travail sur les fils
for ($i = 2 ; $i < $maxtags ; $i++) {
	$filsetendus[$i] = filsRecursifs(array(), $i) ;
	sort($filsetendus[$i]) ;
}

function filsRecursifs($tableau, $t) {
	global $fils ;
	//echo "Je cherche les fils récursifs du tag $t. <br/>" ;
	if (!isset($fils[$t])) return $tableau ;
	//echo "Celui-ci indique comme fils ".implode(", ", $fils[$t]).". Je les explore. <br/>" ;
	for ($i = 0 ; $i < count($fils[$t]) ; $i++) {
		if (!in_array($fils[$t][$i], $tableau)) {
			$tableau[] = $fils[$t][$i] ;
			$tableau = filsRecursifs($tableau, $fils[$t][$i]) ;
		}
	}
	return $tableau ;
}
	
if ($modeVerbeux == 1) echo "<h1>Second état</h1>" ;
if ($modeVerbeux == 1) affichageTags() ;

$t = new tag() ;
$t->select() ;
$maxtags = 0 ;
while ($t->next()) {
	$t->peres = implode(",", $peresetendus[$t->num]) ;
	$t->fils = implode(",", $filsetendus[$t->num]) ;
	$t->save() ;
}

echo "<br/>Le script a bien été exécuté.<br/>" ;

?>