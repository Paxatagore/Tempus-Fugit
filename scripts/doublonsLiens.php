<?php
//script qui identifie les doublons dans les liens et les détruit.
require_once("../inc/centrale.php") ;

echo "Nettoyage premier. <br/>" ;/*
//D'abord, on nettoie les non évenements
$q = "DELETE FROM lienel WHERE evenement = 0" ;
$n = mysqldb::send($q) ;
$q = "DELETE FROM lienel WHERE tag = 0" ;
mysqldb::send($q) ;
$q = "DELETE FROM lienep WHERE evenement = 0" ;
mysqldb::send($q) ;
$q = "DELETE FROM lienep WHERE personne = 0" ;
mysql::query($q) ;
$q = "DELETE FROM lienpl WHERE personne = 0" ;
mysqldb::send($q) ;
$q = "DELETE FROM lienpl WHERE lieu = 0" ;
mysqldb::send($q) ;
$q = "DELETE FROM relationpersonnes WHERE personne1 = 0" ;
mysqldb::send($q) ;
$q = "DELETE FROM relationpersonnes WHERE personne2 = 0" ;
mysqldb::send($q) ;*/

echo "Détruisons maintenant les liens EP en doublons.<br/>" ;
$l 	= new lienep() ;
$l2	= new lienep() ;

$l->select() ;
while ($l->next()) {
	$l2->select("WHERE evenement = $l->evenement AND personne = $l->personne AND num > $l->num") ;
	while ($l2->next()) {
		echo("Il y a matière à tuer $l2->num, semblable à $l->num. </br>") ;
		$l2->delete() ;
	}
}

echo "Détruisons maintenant les liens PL en doublons.<br/>" ;
$l 	= new lienpl() ;
$l2	= new lienpl() ;
$l->select() ;
while ($l->next()) {
	$l2->select("WHERE personne = $l->personne AND lieu = $l->lieu AND num > $l->num") ;
	while ($l2->next()) {
		echo("Il y a matière à tuer $l2->num, semblable à $l->num. </br>") ;
		$l2->delete() ;
	}
}

echo "Détruisons maintenant les liens PP en doublons.<br/>" ;
$l 	= new relationpersonnes() ;
$l2	= new relationpersonnes() ;
$l->select() ;
while ($l->next()) {
	$l2->select("WHERE personne1 = $l->personne1 AND personne2 = $l->personne2 AND num > $l->num") ;
	while ($l2->next()) {
		echo("Il y a matière à tuer $l2->num, semblable à $l->num. </br>") ;
		$l2->delete() ;
	}
}
?>