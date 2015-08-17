<?php
//script qui assure la continuité dynastique : chaque enfant d'une personne ayant une dynastie est à son tour affecté à cette dynastie, s'il n'en a pas déjà une.
require_once("../inc/centrale.php") ;

$p		= new personne() ;
$p->select("WHERE dynastie > 0 AND sexe='h'") ;
$peres = array() ;
while($p->next()) {
	$peres[] = ["num" => $p->num, "dynastie" => $p->dynastie, "nom" => $p->nom] ;
}
mysqldb::$req->closeCursor() ;
foreach($peres as $p) {
	recursivedynastie($p) ;
}

function recursivedynastie($pere) {
	$p2		= new personne() ;
	$lpp	= new relationpersonnes() ;
	/*$q = 'SELECT * FROM relationpersonnes WHERE personne2 = '.$pere["num"] .' AND (typerelation = 1 OR typerelation = 5)' ;
	mysqldb::send($q) ;
	echo $q ;
	$lesLPP = [] ;
	while ($row = mysqldb::$req->fetch()) {
		$lesLPP[] = $row["personne1"] ;
	}*/
	
	$lpp->select('WHERE personne2 = '.$pere["num"].' AND (typerelation = 1 OR typerelation = 5)') ;
	$lesLPP = [] ;
	while($lpp->next()) {
		$lesLPP[] = $lpp->personne1 ;
	}

	//print_r($lesLPP) ;
	echo("<p></p>") ;
	
	foreach ($lesLPP as $lpp) {
		$p2->get($lpp) ;
		if ($p2->dynastie == 0) {
			$p2->dynastie = $pere["dynastie"] ;
			echo $pere["nom"]." de la dynastie $pere->dynastie est le père de ".$p2->display().", sans dynastie. Je rectifie et je lance la vérification récursive.</br>" ;
			$p2->save() ;
			recursivedynastie(["num" => $p2->num, "dynastie" => $p2->dynastie, "nom" => $p2->nom]) ;
		}
	}
}

echo "<br/>Le script a bien été exécuté.<br/>" ;
?>