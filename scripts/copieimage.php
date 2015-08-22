<?php
//script qui copie les images
require_once("../inc/centrale.php") ;

function extension($chaine) {
	$extension = strtolower(substr($chaine, -5)) ;
	if (substr($extension, 0, 1) <> ".") {
		$extension = substr($chaine, -4) ;
		if (substr($extension, 0, 1) <> ".") {
			$extension = substr($chaine, -3) ;
		}
	}
	return $extension ;
}

function copie($origine, $destination) {
	if ($origine == $destination) {
		return -2 ;	//copie inutile
	}
	if (substr($origine, 0, 21) == "https://www.steppe.fr") {
		return -3 ; //copie impossible, on est sur notre serveur 
	}
	if (!is_file($destination)) {
		echo "<p></p>Fichier : ".$origine."</br>" ;
		if(!@copy($origine, $destination)) {
			$errors = error_get_last() ;
			//echo "COPY ERROR: ".$errors['type'] ;
			//echo "<br />\n".$errors['message'] ;
			return 0 ;	//erreur
		} 
		else {
			echo "Fichier copié ! </br>" ;
			return 1 ; //le fichier est bien copié
		}
	}
	return -1 ;	//le fichier existe déjà
}

function travaille ($table, $o, $schemaimage, $champ = "url") {
	echo "Travail sur les objets de type $table <br/>" ;
	$q = "SELECT num, ".$champ." FROM ".$table." WHERE ".$champ." NOT LIKE '%../".$schemaimage."_%'AND ".$champ." <> ''" ;
	mysqldb::send($q) ;
	$liste = mysqldb::fetchall() ;
	foreach ($liste as $row) {	
		$num = $row["num"] ;
		$url = $row[$champ] ; 
		//a - on prend l'extension du fichier
		$extension = extension($url) ; 
		//b - copie du fichier
		$destination = "../".$schemaimage."_".$num.$extension ;
		$n = copie($url, $destination) ;
		if ($n == 1|| $n == -1) {
			$o->get($num) ;
			$o->$champ = $destination ; 
			$o->save();
		}
		echo "Objet : $num - Fichier : $destination - Résultat : $n <br/>" ;
	}
}

$d = new dynastie ;
travaille("dynastie", $d, "img_dynasties/dynastie", "armoirie") ;

$e = new evenement ;
travaille("evenement", $e, "img_evt/evt") ;

$p = new personne() ;
travaille("personne", $p, "img_personnes/personne") ;

$t = new tag ;
travaille("tag", $t, "img_tags/drapeau", "drapeau") ;


/*
$d	= new dynastie() ;
$d->select("WHERE armoirie <> ''") ;
while($d->next()) {
	//echo "Dynastie : $d->nom <br/>" ;
	//a - on prend l'extension du fichier
	$extension = extension($d->armoirie) ;
	//b - copie du fichier
	$destination = "../img_dynasties/dynastie_".$d->num.$extension ;
	$n = copie($d->armoirie, $destination) ;
	if ($n == 1) {
		$d->armoirie = $destination ; 
		$d->save();
	}
}

$e = new evenement() ;
$e->select("WHERE url <> ''") ;
while($e->next()) {
	//echo "Evenement : $e->description <br/>" ;
	//a - on prend l'extension du fichier
	$extension = extension($e->url) ;
	//b - copie du fichier
	$destination = "../img_evt/evt_".$e->num.$extension ;
	$n = copie($e->url, $destination) ;
	if ($n == 1 || $n == -1) {
		$e->url = $destination ; 
		$e->save();
	}
}




$p = new personne() ;
$q = "SELECT num, url FROM personne WHERE url NOT LIKE '%../img_personnes/personne_%'AND url <> ''" ;
mysqldb::send($q) ;
$liste = mysqldb::fetchall() ;
foreach ($liste as $row) {	
	$pnum = $row["num"] ;
	$url = $row["url"] ; 
	//a - on prend l'extension du fichier
	$extension = extension($url) ; 
	//b - copie du fichier
	$destination = "../img_personnes/personne_".$pnum.$extension ;
	$n = copie($url, $destination) ;
	if ($n == 1|| $n == -1) {
		$p->get($pnum) ;
		$p->url = $destination ; 
		$p->save();
	}
	echo "Personne : $pnum - Fichier : $destination - Résultat : $n <br/>" ;
}

$t = new tag() ;
$t->select("WHERE drapeau <> ''") ;
while($t->next()) {
	//a - on prend l'extension du fichier
	$extension = extension($t->drapeau) ; 
	//b - copie du fichier
	$destination = "../img_tags/drapeau_".$t->num.$extension ;
	$n = copie($t->drapeau, $destination) ;
	if ($n == 1|| $n == -1) {
		$t->drapeau = $destination ; 
		$t->save();
	}
}*/

echo "<br/>Le script a bien été exécuté.<br/>" ;
?>