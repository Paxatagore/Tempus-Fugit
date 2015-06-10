<?php
//Inclusions
function __autoload($class_name) 
{
    ((@require_once "../inc/cl.".$class_name.".php") or (@require_once "inc/cl.".$class_name.".php")) or die("Impossible de trouver ".$class_name) ;
}
require_once("../local/database.php") ;
require_once("../inc/cl.alpha.php") ;		//la classe alpha

//Connexion mysql
mysqldb::instance($bddserver, $bdduser, $bddpassword, $bdddatabase) ;

//Lancement de la session
session_start() ;

//Header
//header("Content-type: text/html; charset=UTF-8") ;
header('Content-Type: application/json; charset=UTF-8') ;
//fonctions nécessaires
function redirect($url)
{
	header("Location: ".$url) ;
	exit ;
}

function extraction() {
	//retourne le nombre de paramètres analysés
	$n = func_num_args() ;
	$derniere = func_get_arg($n - 1) ;
	if (is_array($derniere)) {
		$method = $derniere ;
		$d = 1 ;
	}
	else {
		$method = $_POST ;
		$d = 0 ;
	}
	$reussi = 0 ;
	for($i=0 ; $i < $n - $d ; $i++) {
		$_nom = func_get_arg($i) ;
		if (array_key_exists($_nom,$method)) {
			global $$_nom ;
			$$_nom = $method[$_nom] ;
			$reussi++ ;
		}
	}
	return $reussi ;
}
?>
