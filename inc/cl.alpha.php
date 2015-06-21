<?php
abstract class alpha {
	//partie modèle

	//données statiques
	public $table ;
	public $name ;
	public $fields ;

	//données propres à chaque enregistrement
	public $num ; 
	public $creation ;
	public $modification ;
		
	//données de fonctionnement
	public $iq ;
	public $form ;
	private $query ;
	
	var $eJson ;
		
	final function __construct() {			
		$this->name = get_class($this) ;
		$this->table = mysqldb::$prefixe.get_class($this) ;
	}
	
	//première partie
	//comportement collectif, manipulation d'une pluralité de lignes
	
	final public function select($string = "") {
		//sélectionne des lignes données.
		//$string correspond aux options (where, order by, limit...)
		$q = "SELECT DISTINCT ".$this->table.".* FROM ".$this->table." ".$string ;
		$this->query = $q ;
		$this->iq = mysqldb::send($q) ;
		return $this->iq ;
	}
	
	final public function query() {
		return $this->query ;
	}
	
	public function autocomplete($field='nom', $first='') {
		//recherche par défaut pour un champ autocomplete
		//$first = str_ireplace("é", "_", $first) ;
		//$first = str_ireplace("è", "_", $first) ;
		$lq = strlen($first) ;
		$this->select('where '.$field.' like "'.$first.'%" order by occurence DESC, '.$field.' limit 0,20') ;
		$string = "" ;
		if ($this->lenen() > 0) {
			$string = '<ul>' ;
			while($this->next()) {
				$string .= '<li id="'.$this->num.'">'.$this->$field.'</li>';
			}
			$string .= '</ul>' ;
		}
		return $string ;
	}
	
	final public function lenen() {
		//retourne la longueur de la sélection en cours dans la base de donnée
		return mysqldb::$req->rowCount() ;
		//return mysqli_num_rows($this->iq) ;
	}
	
	final public function next() {
		//on passe à la ligne suivante dans la sélection
		if ($row = mysqldb::$req->fetch()) {
			$this->getrow($row) ;
			return true ;
		}
		else {
			mysqldb::$req->closeCursor() ;
			return false ;
		}
		/*	
		if ($row = mysqli_fetch_assoc($this->iq)) {
			 $this->getrow($row) ;
			 return true ;
		}
		else return false ;*/
	}
	
	final public function count($condition = "where 1") {
		//cette fonction compte le nombre d'enregistrements de la table
		//le cas échéant après avoir vérifié $condition
		$q = "SELECT count(*) FROM $this->table $condition" ;
		return mysqldb::unik($q) ;
	}
	
	//seconde partie
	//comportement individuel : manipulation d'une ligne précise
	
	final public function get()	{
		//get permet d'obtenir une ligne précise
		if (func_num_args() == 1) {
	 	 	//comportement habituel : $objet->get($num), obtient la ligne de la table avec num=$num
	     	$num = func_get_arg(0) ;
	     	$q = mysqldb::getline($this->table,$num) ;
	     	if ($q) return $this->getrow($q) ;
	     	else return 0 ;
	 	}
	 	else {
			//comportement à deux attributs
			//1) il cherche les lignes de la table telles que table.argument1 = $argument 2
			//2) et renvoie la première de ces lignes
			//on peut accéder aux autres par un simple $o->next()
			$champ = func_get_arg(0) ;
			$valeur = func_get_arg(1) ;
			$valeur = str_replace("\'", "%", $valeur) ;
			$valeur = str_replace("'", "%", $valeur) ;
			$q = "WHERE $champ LIKE '$valeur'";
			$this->select($q) ;
			if ($this->lenen() > 0) {
				$this->next() ;
				return true ;
			}
			else return false ;
	 	}
	}
	
	function identify($chaine) {
		//s'il existe une instance dont le nom est $chaine, elle est chargée
		//sinon, elle est créée et chargée
		$chaine = trim($chaine) ;
		if ($chaine == "" ) return false ;
		if (!$this->get("nom", $chaine)) {
			$this->nom = $chaine ;
			$this->num = 0 ;
			$this->save() ;
		}
	}
	
	public function delete() {
		return mysqldb::deleteline($this->table, $this->num) ;
		
		//efface la ligne correspondante à l'objet dans la base de donnée
		/*
		$query = "DELETE FROM ".$this->table." WHERE num = ".$this->num ;
		$this->query = $query ;
		$query = mysqldb::send($query);
		return $query;	*/
	}

	final private function getrow($row) {
		//on transfert le contenu d'un tableau $row dans l'objet - utilisé en interne
		if (is_array($row)) {
			foreach($row as $key => $value) {
				$this->$key = stripslashes($value) ;
			}
			return 1 ;
		}
		else return false ;
	}
	
	final public function save() {
		//enregistre dans la base mysql l'objet, soit en création (insert) soit en modification (update), suivant ce qui est nécessaire
		//fonctionnement détaillé : la fonction repose sur une condition centrale (if/else) qui teste la valeur de $this->num. Si celle-ci n'a pas été définie
		if (!empty($this->num)) {
			//mode "update"
	    	//on écrit la requête maintenant
			$q = "UPDATE ".$this->table." SET num ='$this->num'" ;
			foreach($this->fields as $nom) {
				$value 	= addslashes($this->$nom) ;
				if (!mb_check_encoding($value, 'UTF-8')) {
					$value = mb_convert_encoding($value, 'UTF-8') ;
				}
				$q .= ", $nom='".$value."'" ;
			}
			$q .= ", modification = curdate() WHERE num='$this->num'" ;
			//on envoie la requête ainsi composée
			return mysqldb::send($q) ;
		}
		else {
			//mode "save"
			$q = "INSERT INTO ".$this->table." VALUES (" ;
			$this->num = 0 ;
			foreach($this->fields as $nom) {
				$value 	= addslashes($this->$nom) ;
				if (!mb_check_encoding($value, 'UTF-8')) $value = mb_convert_encoding($value, 'UTF-8') ;
				$q 		.= "'".$value."', " ;
			}
			$q .= "curdate(), '')" ;
			//on envoie la requête ainsi composée
			mysqldb::send($q) ;
			//on détermine ainsi $this->num
			//$this->num = mysqli_insert_id(mysqldb::$id) ;
			$this->num = mysqldb::$id->lastInsertId() ;
			return $this->num ;
		}
	}

	private function jsonMaker($cle) {
		$contenu = $this->$cle ;
		if (!mb_check_encoding($contenu, "UTF-8")) $contenu = mb_convert_encoding($contenu, "UTF-8") ;
		//$contenu = str_replace(chr(10), "", $contenu) ;
		//$contenu = str_replace(chr(13), "", $contenu) ;
		return '"'.$cle.'" : '.json_encode($contenu).',' ;
	}
	
	public function json($jsonListe = "") {
		//renvoie l'objet en format json
		$string = '{' ;
		if ($jsonListe != "") {
			foreach ($jsonListe as $cle) {
				$string .= $this->jsonMaker($cle) ;
			}
		}
		else {
			if (isset($this->jsonliste)) {
				foreach ($this->jsonliste as $cle) {
					$string .= $this->jsonMaker($cle) ;
				}
			}
			else {
				$liste = get_object_vars($this) ;
				foreach ($liste as $cle) {
					$string .= $this->jsonMaker($cle) ;
				}
			}
		}
		if ($this->eJson <> "") {
			$string .= $this->eJson ;
		}
		$string = substr($string, 0, -1).'}' ;
		return $string ;
	}
	
	//troisième partie
	//fonctions de hook
	
	public function beforetreat() {
		//fonction générique de traitement des objets avant la sauvegarde
		//doit être, éventuellement, définie
		return 1 ;
	}
	
	public function aftertreat() {
		//fonction générique de traitement des objets après la sauvegarde
		//doit être, éventuellement, définie
		return 1 ;
	}
	
		
	//fonction d'affichage 
	
	public function display() {
		return $this->nom ;
	}
		
}
?>
