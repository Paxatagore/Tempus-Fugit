<?php
class tag extends alpha 
{
	
	var $nom ;
	var $url ;
	var $drapeau ;
	var $nature ;
	var $couleur ;
	var $autreNom ;
	var $motscles ;
	var $adjectifs ;
	var $designation ;
	var $peres ;
	var $fils ;
	var $occurence ;
	var $occurenceJour ;
	var $latitude ;
	var $longitude ;
	//var $evenementDebut ;
	//var $evenementFin ;
	//var $modeCreation ;
	//var $modeFin ;
	

	var $pere ;
	var $eJson ; 
	var $coordonnees ;
	
	var $fields 	= array("num", "nom", "url", "drapeau", "nature", "couleur", "autreNom", "motscles", "adjectifs", "designation", "peres", "fils", "occurence", "occurenceJour", "latitude", "longitude") ;
	var $jsonliste 	= array("num", "nom", "url", "drapeau", "nature", "couleur", "autreNom", "motscles", "adjectifs", "designation", "peres", "fils", "latitude", "longitude") ;
	
	public $lesNature = array(1=>"ville", 2=>"régime", 3=>"peuple", 4=>"dynastie", 5=>"Musée", 6=>"Continent", 7=>"Etat", 8=>"Concepts", 9=>"organisation", 10=>"courant de pensée", 11=>"Mer", 12=>"Ile", 13=>"Astre", 14=>"Etat fédéré") ;
	//public $lesModeCreation = array(1 => "Fondation", 2 => "Unification") ;
	//public $lesModeFin = array(1 => "Dislocation", 2 => "Scission", 3 => "Destruction", 4 => "Abandon") ;

	function display() {
		return $this->nom ;
	}
	
	function aftertreat() {
		$lientt = new lientt() ;
		$tag 	= new tag() ;
		//ajout de nouveaux pères
		if ($_POST["tagsPeres"] != "") {
			$tagPeres = explode(",", $_POST["tagsPeres"]) ;
			if (count($tagPeres) > 0) {
				$this->eJson .= '"lientt": [' ;
				foreach($tagPeres as $ajoute) {
					$tag->num = 0 ;
					$tag->get("nom", trim($ajoute)) ;
					if ($tag->num > 0) {
						$lientt->num = 0 ;
						$lientt->tag1 = $tag->num ;
						$lientt->tag2 = $this->num ;
						$lientt->relation = 0 ;
						$lientt->evenement = 0 ;
						$lientt->save() ;
						$this->eJson .= $lientt->json()."," ;
					}
				}
				$this->eJson = substr($this->eJson, 0, -1)."]," ;
			}
		}
		//ajout de nouveaux fils
		if ($_POST["Fils"] != "") {
			$tagFils = explode(",", $_POST["Fils"]) ;
			if (count($tagFils) > 0) {
				$this->eJson .= '"lientt": [' ;
				foreach($tagFils as $ajoute) {
					$tag->num = 0 ;
					$tag->get("nom", trim($ajoute)) ;
					if ($tag->num > 0) {
						$lientt->num = 0 ;
						$lientt->tag1 = $this->num ;
						$lientt->tag2 = $tag->num ;
						$lientt->relation = 0 ;
						$lientt->save() ;
						$lientt->evenement = 0 ;
						$this->eJson .= $lientt->json()."," ;
					}
				}
				$this->eJson = substr($this->eJson, 0, -1)."]," ;
			}
		}
		
	/*
		//traitement des pères multiples
		$lientt = new lientt() ;
		$tag 	= new tag() ;
		
		//constitution du tableau actuel
		$peresactuels = array() ;
		$lientt->select("where tag2 = $this->num and relation=0") ;
		while ($lientt->next()) {
			$peresactuels[] = $lientt->tag1 ;
		}
		
		//constitution du tableau nouveau
		$peresnouveaux = array() ;
		$i = 1 ; 
		while (isset($_POST["pere".$i])) 
		{
			$p = $_POST["pere".$i] ;
			if ($p <> "") {
				$tag->get("nom", $p) ;
				$peresnouveaux[] = $tag->num ;
			}
			$i++ ;
		}
		 ;
		//tableau des effacés
		$effaced = array_diff($peresactuels, $peresnouveaux) ;

		if (count($effaced) > 0) {
			$this->eJson .= '"liensttsupprime" : [' ;
			foreach($effaced as $efface) {
				$lientt->select("where tag1 = $efface and tag2 = $this->num and relation = 0") ;
				$lientt->next() ;
				$lientt->delete("where tag1 = $efface and tag2 = $this->num and relation = 0") ;
				$this->eJson .= $lientt->json()."," ;
			}
			$this->eJson = substr($this->eJson, 0, -1)."]," ;
		}
		
		//tableau des ajoutés
		$ajouted = array_diff($peresnouveaux, $peresactuels) ;
		if (count($ajouted) > 0) {
			$this->eJson .= '"lientt": [' ;
			foreach($ajouted as $ajoute) {
				$lientt->num = 0 ;
				$lientt->tag1 = $ajoute ;
				$lientt->tag2 = $this->num ;
				$lientt->relation = 0 ;
				$lientt->save() ;
				$this->eJson .= $lientt->json()."," ;
			}
			$this->eJson = substr($this->eJson, 0, -1)."]," ;
		}*/
		return 1 ;
	} // afterTreat()
	
	function autocomplete($field='nom', $first='', $limit = 10) {
		//recherche par défaut pour un champ autocomplete
		//$first = utf8_decode(strtolower(stripslashes($first))) ;
		/*$first = str_ireplace("â", "_", $first) ;	
		$first = str_ireplace("ê", "_", $first) ;
		$first = str_ireplace("é", "_", $first) ;
		$first = str_ireplace("è", "_", $first) ;
		$first = str_ireplace("ç", "_", $first) ;
		$first = str_ireplace("ï", "_", $first) ;		
		$first = str_ireplace("î", "_", $first) ;
		$first = str_ireplace("ü", "_", $first) ;
		$first = str_ireplace("ô", "_", $first) ;		
		$first = str_ireplace("-", "_", $first) ;*/
		
		//echo $first ;
		$first = stripslashes($first) ;
		$lq = strlen($first) ;
		$q = 'where (nom like "'.$first.'%") ORDER BY occurenceJour DESC, occurence DESC, nom LIMIT 0, '.$limit ;
		$this->select($q) ;
		$string = '<ul>' ;
		if ($this->lenen() > 0) {
			while($this->next()) {
				$string .= '<li id="'.$this->num.'">'.$this->nom.'</li>' ;
			}
		}
		$this->select('where (autreNom like "%'.$first.'%") ORDER BY occurenceJour DESC, occurence DESC, autreNom limit 0,5') ;
		if ($this->lenen() > 0) {
			while($this->next()) {
				$string .= '<li id="'.$this->num.'"><span class="informal">'.$this->autreNom.'  (</span>'.$this->nom.'<span class="informal">)' ;
				$string .= '</span></li>' ;
			}
		}
		$string .= '</ul>' ;
		return $string ;
	}
	
	public function detail() {
		//affiche des éléments de détail sur le tag en question
		//1. le JSON classique
		$string = '{"tag":'.$this->json().', ' ;
		
		//2. les liens tt : occupations, conquête
		$lientt = new lientt() ;
		$q = "SELECT * FROM lientt, evenement WHERE lientt.tag2 = $this->num AND lientt.evenement = evenement.num ORDER BY evenement.andebut ASC" ; 
		$lientt->iq = mysqldb::send($q) ;
		if ($lientt->lenen() > 0) {
			$string .= '"lientt" : [' ;
			while($lientt->next()) {
				$string .= $lientt->json(array("tag1", "tag2", "relation", "andebut"))."," ;
			}
			$string = substr($string, 0, -1).'], ' ;
		}
		else {
			$string .= '"lientt" : "",' ;
		}
		
		//3. les fonctions
		$f = new fonction() ;
		$f->select("WHERE lieuId = $this->num") ;
		if ($f->lenen() > 0) {
			$string .= '"fonctions" : [' ;
			while ($f->next()) {
				$string .= $f->json()."," ;
			}
			$string = substr($string, 0, -1).'], ' ;
		}
		else {
			$string .= '"fonctions" : "", ' ;
		}
		$string = substr($string, 0, -2).'}' ;
		return $string ;
	}
}
?>