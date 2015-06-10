<?php
class dynastie extends alpha {
	
	var $nom ;
	var $pere ;			//une autre dynastie
	var $couleur ;
	var $description ;
	var $armoirie ;
	var $occurence ;
	var $occurenceJour ;
	
	var $fields		= array("num", "nom", "pere", "couleur", "description", "armoirie", "occurence", "occurenceJour") ;
	var $jsonliste 	= array("num", "nom", "pere", "couleur", "description", "armoirie", "occurence", "occurenceJour") ;
	var $eJson ;
	
	function beforetreat() {
		//normalement, le JS nous a passé la valeur du père dans pere_value, ce qui permet d'éviter une requête pour aller le chercher dans la base à partir du nom.
		if ($this->pere_value > 0) {
			$this->pere = $this->pere_value ;
		}
		else {
			if ($this->pere <> "") {
				$d = new dynastie() ;
				$d->identify($this->pere) ;
				$this->pere = $d->num ;
			}
		}
		$this->occurenceJour++ ;
		return true ;
	}
}
?>