<?php

class relationpersonnes extends alpha 
{
	public $personne1 ;
	public $personne2 ;
	public $typerelation ;
	
	var $fields 	= array("num", "personne1", "personne2", "typerelation") ;
	var $jsonliste 	= array("num", "personne1", "personne2", "typerelation") ;
	
	//personne1 est le type_relation de personne2
	//où type_relation est "Fils", "Frère", "Epoux", "Neveu", "Fils adoptif", "Petit-fils", "Cousin", "Disciple", "Amant", "Membre", "Fils illégitime"
	//ou l'équivalent féminin
	
	function beforetreat() {
		$p1 = new personne() ;
		$p2 = new personne() ;
		if ($this->typerelation > 11) {
			//on doit inverser
			$p1->creeridentifie($this->personne2) ;
			$p2->creeridentifie($this->personne1) ;
			$this->typerelation -= 11 ; 
		}
		else {
			$p1->creeridentifie($this->personne1) ;
			$p2->creeridentifie($this->personne2) ;
		}
		$this->personne1 = $p1->num ;
		$this->personne2 = $p2->num ;

		//ajout du plus vieux à l'évènement naissance du plus jeune 
		$en1 = $p1->naissance_evenementId ;
		if ($this->typerelation == 1 or $this->typerelation == 4 or $this->typerelation == 5 or $this->typerelation == 6 or $this->typerelation == 8 or $this->typerelation == 11) {
			//relation nécessairement descendante
			if ($en1 > 0) {
				$ep = new lienep() ;
				$ep->select("WHERE evenement = $en1 AND personne = $this->personne2") ;
				if ($ep->lenen() == 0) {
					$ep->num 		= 0 ;
					$ep->evenement 	= $en1 ;
					$ep->personne 	= $this->personne2 ;
					$ep->save() ;
					$this->eJson = '"lienep" : '.$ep->json().',' ;
				}
			}
		}
		elseif ($this->typerelation <> 10) {
			//Relation où il faut identifier le plus jeune et le plus vieux 
			$en2 = $p2->naissance_evenementId ;
			if ($en1 > 0 AND $en2 > 0) {
				$e1 = new evenement() ;
				$e1->get($en1) ;
				$e2 = new evenement() ;
				$e2->get($en2) ;
				if ($e2->andebut < $e1->andebut) {
					//p2 est le plus vieux. on l'ajoute à la naissance de p1
					$ev  	= $en1 ;
					$pnum	= $this->personne2 ;
				}
				else {
					//p1 est le plus vieux. on l'ajoute à la naissance de p2
					$ev  	= $en2 ;
					$pnum	= $this->personne1 ;
				}
				$ep = new lienep() ;
				$ep->select("WHERE evenement = $ev AND personne = $pnum") ;
				if ($ep->lenen() == 0) {
					$ep->num 		= 0 ;
					$ep->evenement 	= $ev ;
					$ep->personne 	= $pnum ;
					$ep->save() ;
					$this->eJson = '"lienep" : '.$ep->json().',' ;
				}
			}
		}
		return 1 ;
	}
}
?>
