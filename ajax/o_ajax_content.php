<?php
//renvoie un fichier JSON qui contient un objet
require_once("../inc/centrale.php") ;
//extraction("objet", $_GET) ;
extraction("objet", $_POST) ;
$o = new $objet ;
$condition = "WHERE 1 AND " ;
while (list($key, $value) = each($_POST))
{
	if (property_exists ($o, $key)) 
	{
		$condition .= "$key = '$value' AND " ;
	}
	$o->$key = $value ;
}
$condition .= "1" ;
$o->select($condition) ;
if ($o->lenen() > 0) {
	$json = '{"'.$objet.'" : [' ;
	while ($o->next()) {
		$json .= $o->json().", " ;
	}
	$json = substr($json, 0, -2);
	$json .= ']}' ;
}
else {
	$json = '{"'.$objet.'" : ""}' ;
}
die($json) ;
?>
