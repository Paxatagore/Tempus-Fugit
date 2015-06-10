<?php

class lienpf extends alpha 
{
	
	var $fonction ;
	var $personne ;
	var $evenementdebut ;	//évènement de début
	var $andebut ;
	var $evenementfin ;		//évènement de fin
	var $anfin ;
	var $evenement ;		//évènement d'expression du lien pf
	
	var $fields 	= array("num", "fonction", "personne", "evenementdebut", "andebut", "evenementfin", "anfin", "evenement") ;
	var $jsonliste 	= array("num", "fonction", "personne", "evenementdebut", "evenementfin", "andebut", "anfin", "evenement") ;
	var $eJson ;	//JSON étendu
	
	function beforetreat() {
		$p = new personne() ;
		$p->get("nom", $this->personne) ;
		if ($p->num > 0) {
			$this->personne = $p->num ;
		}
		$f = new fonction() ;
		$f->get("nom", $this->fonction) ;
		if ($f->num > 0) {
			$this->fonction = $f->num ;
		}
	}
	
	function aftertreat() {
		//post traitement 
		
		//1. création ou modification de l'évènement support
		$e 	= new evenement() ;
		if ($this->evenement > 0) $e->get($this->evenement) ;
		$e1	= new evenement() ;
		$e2	= new evenement() ;
		$f	= new fonction() ;
		$p	= new personne() ;
		$lep= new lienep() ;
		$lel= new lienel() ;
		$p->get($this->personne) ;
		$f->get($this->fonction) ;
		$e1->get($this->evenementdebut) ;
		$this->andebut = $e1->andebut ;
		$e->description = $p->display().", ".$f->display(0, $p->sexe) ;
	
		$e->andebut 	= $e1->andebut ;
		$e->moisdebut 	= $e1->moisdebut ;
		$e->jourdebut	= $e1->jourdebut ;
		$e->versdebut	= $e1->versdebut ;
		if ($this->evenementfin <> 0) {
			$e2->get($this->evenementfin) ;
			$e->anfin	 	= $e2->andebut ;
			$e->moisfin 	= $e2->moisdebut ;
			$e->jourfin		= $e2->jourdebut ;
			$e->versfin		= $e2->versdebut ;
			$this->anfin	= $e2->andebut ;
		}
		else {
			$this->anfin	= 0 ;
		}
		$e->etoiles		= 2 ;
		$e->periode		= 1 ;
		$e->numeroordre	= 0 ;
		$e->typeeve		= 3 ;
		$e->save() ;
		$this->eJson .= '"evenement" : '.$e->Json().'}' ;
	
		//personne
		$lep->num		= 0 ;
		$lep->personne	= $p->num ;
		$lep->evenement	= $e->num ;
		$lep->save() ;
		
		//tag
		$lel->num		= 0 ;
		$lel->evenement	= $e->num ;
		$lel->tag		= $f->lieuId ;
		$lel->save() ;
		//$this->eJson .= '"lienel" : '.$lel->num ;

		$this->evenement = $e->num ;
		$this->save() ;
		
		//occurence de fonction
		$f = new fonction() ;
		$f->get($this->fonction) ;
		$f->occurenceJour++ ;
		$f->save() ;
	}
}

?>