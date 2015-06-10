<?php
require_once("../inc/centrale.php") ;

$p		= new personne() ;
$e		= new evenement() ;
$rpp	= new relationpersonnes() ;
$p2		= new personne() ;
$e2		= new evenement() ;
$lep	= new lienep() ;

$p->select() ;
while ($p->next()) {
	//vérifier les liens frères - soeurs 
	if ($p->naissance_evenementId > 0) {
		//1. L'évènement est-il enregistré comme une naissance ?
		$e->get($p->naissance_evenementId) ;
		if ($e->typeeve <> 13) {
			$e->typeeve = 13 ;
			$e->save() ;
			echo("La naissance de $p->nom n'était pas référencée comme naissance. Je le modifie.</br>") ;
		}
		//2. Les parents sont-ils connus ?
		$rpp->select("WHERE personne1 = $p->num AND typerelation = 1 ") ;
		while ($rpp->next()) {
			//sont ils enregistrés dans les liens EP ?
			$a = $lep->add($p->naissance_evenementId, $rpp->personne2) ;
			if ($a == 1) echo("L'un des parents de $p->nom n'était pas attaché à l'évènement naissance. Je rectifie.<br/>") ;
		}
		//2. Les frères sont-ils connus ?
		$rpp->select("WHERE personne1 = $p->num AND typerelation = 2 ") ;
		while ($rpp->next()) {
			//le frère personne2 est-il plus âgé ?
			$p2->get($rpp->personne2) ;
			if ($p2->naissance_evenementId > 0) {
				$e2->get($p2->naissance_evenementId) ;
				if ($e2->andebut < $e->andebut) {
					//personne2 est un grand frère de personne1 -> il faut l'inscrire dans les liens EP si ce n'est déjà fait
					$a = $lep->add($p->naissance_evenementId, $p2->num) ;
					if ($a == 1) echo("Le grand frère de $p->nom n'était pas à l'évènement naissance. Je rectifie.<br/>") ;
				}
			}
		}
		$rpp->select("WHERE personne2 = $p->num AND typerelation = 2 ") ;
		while ($rpp->next()) {
			//le frère personne1 est-il plus âgé ?
			$p2->get($rpp->personne1) ;
			if ($p2->naissance_evenementId > 0) {
				$e2->get($p2->naissance_evenementId) ;
				if ($e2->andebut < $e->andebut) {
					//personne2 est un grand frère de personne1 -> il faut l'inscrire dans les liens EP si ce n'est déjà fait
					$a = $lep->add($p->naissance_evenementId, $p2->num) ;
					if ($a == 1) echo("Le grand frère de $p->nom n'était pas à l'évènement naissance. Je rectifie.<br/>") ;
				}
			}
		}			
	}
}

echo "<br/>Le script a bien été exécuté.<br/>" ;
?>