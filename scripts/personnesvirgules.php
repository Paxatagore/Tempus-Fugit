<?php
require_once("../inc/centrale.php") ;
$p = new personne() ;
$p->select() ;
while($p->next()) {
	$string = str_replace (",", " -", $p->nom, $n) ;
	if ($n > 0) {
		echo "J'ai modifié le nom de ".$p->nom." en ".$string.". <br/>" ;
		$p->nom = $string ;
		$p->save() ;
	}
}
?>