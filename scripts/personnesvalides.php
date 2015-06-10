<?php
require_once("../inc/centrale.php") ;
$p = new personne() ;
$p->select() ;
while($p->next()) {
	if ($p->naissance_evenementId <> 0 or $p->mort_evenementId <> 0 or $p->notice <> "" or $p->occurence > 2) {
		$p->valide = 1 ;
		$p->save() ;
	}
	else {
		echo("Est invalide : $p->nom ($p->num)<br/>") ;
	}
}
?>