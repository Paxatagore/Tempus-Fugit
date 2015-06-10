<?php
class lienep extends alpha {
	var $evenement ;
	var $personne ;
	
	var $fields 	= array("num", "evenement", "personne") ;
	var $jsonliste 	= array("num", "evenement", "personne") ;
	
	function add($e, $p) {
		//ajoute un lien s'il n'existe pas
		$this->select("WHERE evenement = $e AND personne = $p") ;
		if ($this->lenen() == 0) {
			$this->num = 0 ;
			$this->evenement = $e ;
			$this->personne	= $p ;
			$this->save() ;
			return 1 ; 
		}
		else return 0 ;
	}
}
?>