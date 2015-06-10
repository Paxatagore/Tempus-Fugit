<?php
/*ce fichier est appelé par principe par tous les scripts php. Il centralise quelques fonctions indispensables en
tout état de cause, opère la connexion à mysql et définit les headers de la page */

//Inclusions de classes automatiques
function __autoload($class_name) {
    ((@require_once "../inc/cl.".$class_name.".php") or (@require_once "inc/cl.".$class_name.".php")) or die("Impossible de trouver ".$class_name) ;
}
require_once("../local/database.php") ;		//les coordonnées de connexion à mysql
require_once("../inc/cl.alpha.php") ;		//la classe alpha

//Connexion mysql
mysqldb::instance($bddserver, $bdduser, $bddpassword, $bdddatabase) ;

//Lancement de la session - est-ce nécessaire ?
//session_start() ;

//Header
//en principe, le php ne sert plus qu'à produire du json, on envoie donc les headers des json.
//en débogage, ça peut être commode d'avoir de headers de html. 
if (isset($debogage) && $debogage == 1) header("Content-type: text/html; charset=UTF-8") ;
else header('Content-Type: application/json; charset=UTF-8') ;

//fonctions nécessaires

function extraction() {
	//extrait des paramètres d'un tableau
	//extraction($a,$b,$c,$tableau) cherchera $tableau["a"] et la mettra dans $a, idem pour b et c 
	$n = func_num_args() ;				//n est le nombre d'arguments passés à la fonction
	$derniere = func_get_arg($n - 1) ;	//on prend le dernier argument
	if (is_array($derniere)) {			//si c'est un tableau
		$method = $derniere ;			//alors c'est tableau qu'on va étudier
		$d = 1 ;
	}
	else {
		$method = $_POST ;					//le dernier argument n'est pas un tableau : c'est $_POST qu'on va étudier
		if (count($_POST) < 1) $method = $_GET ;	//si POST est vide, on prend GET
		$d = 0 ;
	}
	$reussi = 0 ;							//reussi est le nombre d'arguments qu'on a découvert
	for($i = 0 ; $i < $n - $d ; $i++) { 	//on prend la liste des arguments un à un
	//en évitant le dernier (d=1) si le tableau est le dernier argument
		$_nom = func_get_arg($i) ;		//on prend cet argument
		if (array_key_exists($_nom,$method)) {		//on regarde si on a une clé de ce nom là dans le tableau
			$GLOBALS[$_nom] = $method[$_nom] ;		//on définit cette variable avec le contenu tiré de $method
			$reussi++ ;								//on incrémente réussi
		}
	}
	return $reussi ;					//retourne le nombre de paramètres analysés
}
?>