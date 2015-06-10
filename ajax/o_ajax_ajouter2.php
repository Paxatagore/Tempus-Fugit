<?php
require_once("../inc/centrale.php") ;
//$_POST = $_GET ;
extraction("objet", $_POST) ;
$o = new $objet ;

if (isset($_POST["num"])) {
	$o->get($_POST["num"]) ;
}
while (list($key, $value) = each($_POST)) {	
	$o->$key = $value ;
}
$o->beforetreat() ;
$o->save() ;
$o->aftertreat() ;
echo $o->json() ;
die() ;
?>