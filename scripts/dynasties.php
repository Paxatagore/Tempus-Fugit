<?php
//script qui assure la continuité dynastique : chaque enfant d'une personne ayant une dynastie est à son tour affecté à cette dynastie, s'il n'en a pas déjà une.
require_once("../inc/centrale.php") ;

$p		= new personne() ;
$p->select("WHERE dynastie > 0 AND sexe='h'") ;
while($p->next()) {
	recursivedynastie($p) ;
}

function recursivedynastie($pere) {
	$p2		= new personne() ;
	$lpp	= new relationpersonnes() ;
	$lpp->select("WHERE personne2 = $pere->num AND (typerelation = 1 OR typerelation = 5)") ;
	while($lpp->next()) {
		$p2->get($lpp->personne1) ;
		if ($p2->dynastie == 0) {
			$p2->dynastie = $pere->dynastie ;
			echo $pere->display()." de la dynastie $pere->dynastie est le père de ".$p2->display().", sans dynastie. Je rectifie et je lance la vérification récursive.</br>" ;
			$p2->save() ;
			recursivedynastie($p2) ;
		}
	}
}

echo "<br/>Le script a bien été exécuté.<br/>" ;

?>