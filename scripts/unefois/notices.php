<?php
require_once("../inc/centrale.php") ;
$p = new personne() ;
$p->select() ;
while($p->next()) {
	$a = $p->notice ; 	//valeur par défaut
	$p->notice = trim($p->notice) ;
	if ($p->notice <> "" && substr($p->notice, -1, 1) <> ".") {
		$p->notice .= "." ;
	}
	if ($p->notice == ".") $p->notice = "" ;
	$p->notice = ucfirst($p->notice) ;
	if ($a != $p->notice) {
		echo "J'ai changé la notice de ".$p->nom." en $a " ;
		$p->save() ;
	}
}
?>