<?php
class fonction extends alpha {
	
	var $nom ;
	var $lieuId ;	//lieu est une référence vers un tag
	var $nomFeminin ; //nom féminin
	var $description ;
	var $occurence ;
	var $occurenceJour ;
	
	var $fields 	= array("num", "nom", "lieuId", "nomFeminin", "description", "occurence", "occurenceJour") ;
	var $jsonliste 	= array("num", "nom", "lieuId", "nomFeminin", "description", "occurence") ;
	var $eJson ;
	
	function beforetreat() {
		//normalement, le JS nous a passé la valeur du tag dans lieuId_value, ce qui permet d'éviter une requête pour aller le chercher dans la base à partir du nom.
		if ($this->lieuId_value > 0) {
			$this->lieuId = $this->lieuId_value ;
		}
		else {
			if ($this->lieuId <> "") {
				$t = new tag() ;
				$t->get("nom", $this->lieuId) ;
				$this->lieuId = $t->num ;
			}
		}
		$this->occurenceJour++ ;
		return true ;
	}
	
	function autocomplete($field='nom', $first='') {
		//recherche par défaut pour un champ autocomplete
		$first = str_ireplace("é", "_", $first) ;
		$first = str_ireplace("è", "_", $first) ;
		$lq = strlen($first) ;
		$this->select('WHERE '.$field.' LIKE "'.$first.'%" ORDER BY occurenceJour DESC, occurence DESC, nom DESC, '.$field.' limit 0,20') ;
		$string = "" ;
		if ($this->lenen() > 0) {
			$string = '<ul>' ;
			while($this->next()) {
				$string .= '<li>'.$this->$field.'</li>';
			}
			$string .= '</ul>' ;
		}
		return $string ;
	}
}
?>