<?php
//script qui tue les personnes qui n'ont pas de nom.
require_once("../inc/centrale.php") ;
$p = new personne() ;
$p->select('WHERE nom=""') ;
while($p->next()) {
	echo $p->num." n'a pas de nom : ".$p->nom.". Je le tue. <br/>" ;
	$p->delete() ;
}

//vérifie ensuite les liens PP
$l = new relationpersonnes() ;
$p = new personne() ;
$l->select() ;
while ($l->next()) {
	$p->num = 0 ;
	$p->get($l->personne1) ;
	if ($p->num == 0) {
		echo "$l->personne1 n'existe plus. Je supprime le lien PP $l->num. <br/>" ;
		$l->delete() ;
	}
	else {
		$p->num = 0 ;
		$p->get($l->personne2) ;
		if ($p->num == 0) {
			echo "$l->personne2 n'existe plus. Je supprime le lien PP $l->num. <br/>" ;
			$l->delete() ;
		}
	}
}

echo "<br/>Le script a bien été exécuté.<br/>" ;
?>