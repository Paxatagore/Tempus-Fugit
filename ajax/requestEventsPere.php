<?php
//renvoi un fichier JSON qui contient un objet
require_once("../inc/centrale.php") ;
$e 			= new evenement() ;
$andebut 	= $_POST["andebut"] ;
$anfin		= $_POST["anfin"] ;
if ($anfin == 0) $anfin = $andebut ;
if ($anfin == "") $anfin = $andebut ;
$condition	= "typeeve IN (4,5,8,9,10) " ;
		
//cas 1 : l'�v�nement p�re d�borde enti�rement des bornes
$condition .= "AND ((andebut <= $andebut AND anfin >= $anfin) " ;
//cas 2 : l'�v�nement p�re est enti�rement compris dans les bornes
$condition .= " OR (andebut >= $andebut AND anfin <= $anfin AND anfin <> 0)" ;
//cas 3 : l'�v�nement p�re d�borde en partie sur la p�riode ant�rieure
$condition .= "OR (andebut <= $andebut AND anfin >= $andebut AND anfin <= $anfin AND anfin <> 0) " ;
//cas 4 : l'�v�nement p�re d�borde en partie sur la p�riode post�rieure
$condition .= "OR (andebut >= $andebut AND andebut <= $anfin AND anfin >= $anfin AND anfin <> 0) " ;
$condition .= ")" ;
$e->select("WHERE ".$condition." ORDER BY andebut, moisdebut, jourdebut") ;
$json = '{"evenements":[' ;
if ($e->lenen() > 0) {
	while ($e->next()) {
		$json .= $e->json().", " ;
	}
	$json = substr($json, 0, -2);
}
$json .= ']}' ;
//$json .= 'q":"'.$condition.'"]' ;
die($json) ;
?>