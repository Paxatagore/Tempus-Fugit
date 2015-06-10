<?php
require_once("../inc/centrale.php") ;
/*
function __autoload($class_name) 
{
    ((@require_once "../inc/cl.".$class_name.".php") or (@require_once "inc/cl.".$class_name.".php")) or(trigger_error("Impossible de trouver mysqldb.", E_USER_ERROR));
}

mysqldb::instance($bddserver, $bdduser, $bddpassword, $bdddatabase) ;
header('Content-type: text/html; charset=UTF-8'); 
*/
//$_POST = $_GET ;
//paramètres requis :
//- objet : le type d'objet concerné
//- premice : la prémice de la recherche
//- champ : le champ qu'il faut afficher
//$_POST = $_GET ;
if (isset($_POST["objet"]))
{
	$objet = $_POST["objet"] ;
	$o = new $objet ;
}
else $o = new lieu() ;

if (isset($_POST["premice"])) $premice = $_POST["premice"] ;
else $premice = "" ;
if (isset($_POST["champ"])) $champ = $_POST["champ"] ;
else $champ = "" ;

if ($premice != "") {	
	echo $o->autocomplete($champ, $premice) ;
}
?>