<?php
/*commandes spéciales :
 * verbose = 1 - ajoute des éléments de débugage comme la requête SQL
 */

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
 
require_once("../inc/centrale.php") ;
$verbose = 0 ;	//par défaut. Une valeur supérieure renvoie en plus la requête SQL.

function checkObjet($objet) {
	global $verbose ;
	if (!in_array($objet, ["dynastie", "evenement", "fonction", "lienel", "lienep", "lienpf", "lienpl", "lientt", "personne", "relationpersonnes", "tag"]) ) {
		if ($verbose == 1) die('{"status":"0", "erreur":"1", "message":"objet inexistant"}') ;
		else die ('{"status":"0", "erreur":"1"}') ;
	}
	return true ;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	extraction("objet", "verbose", $_GET) ;
	checkObjet($objet) ;
	$o = new $objet ;
	$query = "" ;
	$condition = array() ; 
	while (list($key, $value) = each($_GET)) {
		if (property_exists ($o, $key)) {
			$condition[] = "$key = '$value' " ;
			$o->$key = $value ;
		}
	}
	if (!empty($condition)) $query = "WHERE ".implode(" AND ", $condition) ;
	$o->select($query) ;
	if ($o->lenen() > 0) {
		while ($o->next()) {
			$ojson[] = $o->json() ;
		}
		if ($verbose > 0) $json = '{"query":"'.$o->query().'", "status":1, "lenen":'.$o->lenen().', "'.$objet.'" : ['.implode(", ", $ojson).']}' ;
		else $json = '{"status":1, "lenen":'.$o->lenen().', "'.$objet.'" : ['.implode(", ", $ojson).']}' ;
	}
	else {
		if ($verbose > 0) $json = '{"query":"'.$o->query().'", "status":1, "lenen":0, "'.$objet.'" : []}' ;
		else $json = '{"status":1, "lenen":0, "'.$objet.'" : []}' ;
	}
}
elseif ($_SERVER["REQUEST_METHOD"] == "POST") {

	$n = extraction("command", "num", "objet", "verbose") ;
	checkObjet($objet) ;
	$o = new $objet ;
	$o->get($num) ;
	if (isset($command) and $command == "delete") {
		$n = $o->delete() ;
		if ($n) {
			if ($verbose > 0) $json = '{"query":"'.$o->query().'", "status":"1"}' ;
			else $json = '{"status":"1"}' ;
		}
		else {
			if ($verbose > 0) $json = '{"query":"'.$o->query().'", "status":"0"}' ;
			else $json = '{"status":"0"}' ;
		}
	}
	elseif (!isset($command) or $command == "save") {
		
		while (list($key, $value) = each($_POST)) {	
			$o->$key = $value ;
		}
		if ($o->beforetreat()) {
			if ($o->save()) {
				if ($o->aftertreat()) {
					if ($verbose > 0) $json = '{"query":"'.$o->query().'", "status":1, "num":'.$o->num.', "'.$objet.'":'.$o->json().'}' ;
					else $json = '{"status":1, "num":'.$o->num.', "'.$objet.'":'.$o->json().'}' ;
				}
				else {
					if ($verbose > 0) $json = '{"query":"'.$o->query().'", "status":-1, "num":'.$o->num.', "'.$objet.'":'.$o->json().'}' ;
					else $json = '{"status":-1, "num":'.$o->num.', "'.$objet.'":'.$o->json().'}' ;
				}
			}
			else {
				if ($verbose > 0) $json = '{"query":"'.$o->query().'", "status":-2}' ;
				else $json = '{"status":-2}' ;
			}
		}
		else {
			if ($verbose > 0) $json = '{"query":"'.$o->query().'", "status":-3}' ;
			else $json = '{"status":-3}' ;
		}
	}
}
die($json) ;
?>
