<?php
class evenement extends alpha {
	
	var $ecartdate ;	//0 - de... à... | 1 - entre le ... et le ...
	var $andebut ;
	var $moisdebut ;
	var $jourdebut ;
	var $datedebut ;
	var $versdebut ;		//1 - vers - 2 - siècle 3 - décennie - 4 - saison
	var $typedatedebut ;	//1 : millénaire, 2 : siècle, 3 : décennie, 4 : début de siècle, 5 : milieu de siècle,6 : fin de siècle
	//7-8-9-10 premier quart / deuxième quart / troisième quart / quatrième quart siècle
	//11-12-13 : début, milieu ou fin d'année ou de mois (selon le cas)
	var $anfin ;
	var $moisfin ;
	var $jourfin ;
	var $versfin ;
	var $typedatefin ;
	var $description ;	//brève description de l'évènement
	var $precisions ;	//éléments plus complets
	var $pere ;
	var $thematiques ; 
	var $fonction ;
	var $url ;
	var $etoiles ;
	var $numeroordre ;
	var $typeeve ;
	var $locus ; 
	var $latitude ;
	var $longitude ;
	var $lieu ;
	
	var $fields = array("num", "ecartdate", "andebut", "moisdebut", "jourdebut", "versdebut", "anfin", "moisfin", "jourfin", "versfin", "description", "precisions", "pere", "thematiques", "fonction", "url", "etoiles", "numeroordre", "typeeve", "locus", "latitude", "longitude", "lieu") ;
	var $jsonliste = array("num", "ecartdate", "jourdebut", "moisdebut", "andebut", "versdebut", "typedatedebut", "jourfin", "moisfin", "anfin", "versfin", "typedatefin", "description", "precisions", "pere", "url", "etoiles", "typeeve", "locus", "latitude", "longitude", "lieu", "fonction", "numeroordre", "thematiques") ;
	
	var $personne ;
	
	function beforetreat() {
		//si description n'est pas définie, on prend la première phrase de precisions
		$this->description 	= ucfirst(trim(str_ireplace  ("?", "'", $this->description))) ;
		$this->precisions	= ucfirst(trim(str_ireplace  ("?", "'", $this->precisions))) ;
		if ($this->description == "") {
			$a = strpos($this->precisions, ".") ;
			$this->description	= substr($this->precisions, 0, $a) ;
			$this->precisions 	= substr($this->precisions, $a+1, strlen($this->precisions) - strlen($this->description)) ;
		}
		//on enlève les ? qui se glissent quand on copie depuis wikipedia.
		$a = 0 ;
		//on enlève également les #
		$this->description 	= ucfirst(trim(str_ireplace  ("#", "", $this->description, $a))) ;
		//on met un point à la fin de description
		if (substr($this->description, -1, 1) <> ".") $this->description .= "." ;
		if (substr($this->description, -1) <> ".") $this->description .= "." ;
			
		//traitement spécifique aux règnes
		if ($this->typeeve == 3) {
			$this->numeroordre = 15 ;	//on définit le numéro ordre à bcp, pour qu'il passe toujours derrière l'évènement premier du règne. 
			$f = new fonction() ;
			if ($this->fonction != "") {
				$f->get("nom", $this->fonction) ;
				$this->fonction = $f->num ;
				if (strlen($this->description) < 2) {
					if (isset($_POST["personnes"])) {
						$p1 = $_POST["personnes"] ;
						$this->description = $p1.", ".$f->nom ;
					}
					$t = new tag() ;
					$t->get($f->lieuId) ;
					$_POST["tags"] .= ",".$t->nom ;
				}
			}
		}
		$this->versdebut 	= 0 ;
		$this->versfin 		= 0 ;
		if (isset($_POST["versdebut"])) 	$this->versdebut = $this->versdebut[0] ;
		if (isset($_POST["versfin"])) 		$this->versfin = $this->versfin[0] ;
		//traitement des dates
		if ($this->moisdebut == 19) {
			//siècle - on calcule automatiquement le bon siècle à partir de la date fournie par andebut
			$this->andebut 		= floor($this->andebut / 100) * 100 ;
			$this->moisdebut	= 0 ;
			//si anfin est défini également, on calcule le bon siècle spécifique - cela permet de couvrir plusieurs siècles
			if ($this->anfin <> "") $this->anfin = floor($this->anfin / 100) * 100 ;
			//sinon, on calcule juste 100 ans de plus que le début...
			else $this->anfin		= $this->andebut + 100 ;
			$this->moisfin			= 0 ;
			$this->typedatedebut 	= 2 ;
			$this->typedatefin		= 2 ;
		}
		elseif ($this->moisdebut == 18) {
			//décennie - même fonctionnement
			$this->andebut 		= floor($this->andebut / 10) * 10 ;
			$this->moisdebut	= 0 ;
			if ($this->anfin <> "") $this->anfin = floor($this->anfin / 10) * 10 ;
			else $this->anfin		= $this->andebut + 10 ;
			$this->moisfin			= 0 ;
			$this->typedatedebut 	= 3 ;
			$this->typedatefin		= 3 ;
		}
		elseif ($this->moisdebut == 20) {
			//début de siècle
			$this->andebut 		= floor($this->andebut / 100) * 100 ;
			$this->anfin		= $this->andebut + 33 ;
			$this->typedatedebut 	= 4 ;
			$this->typedatefin		= 4 ;
		}
		elseif ($this->moisdebut == 21) {
			//mi siècle
			$this->andebut 		= floor($this->andebut / 100) * 100 + 33 ;
			$this->anfin		= $this->andebut + 33 ;
			$this->typedatedebut 	= 5 ;
			$this->typedatefin		= 5 ;
		}
		elseif ($this->moisdebut == 22) {
			//fin de siècle
			$this->andebut 		= floor($this->andebut / 100) * 100 + 66;
			$this->anfin		= $this->andebut + 34 ;
			$this->typedatedebut 	= 6 ;
			$this->typedatefin		= 6 ;
		}
		elseif ($this->moisdebut > 12) {
			//saisons
			if ($this->moisdebut == 13)	$this->moisdebut = 1 ;		//hiver
			if ($this->moisdebut == 14)	$this->moisdebut = 4 ;		//printemps
			if ($this->moisdebut == 15)	$this->moisdebut = 7 ;		//été
			if ($this->moisdebut == 16)	$this->moisdebut = 10 ;		//automne
			if ($this->moisdebut == 17)	$this->moisdebut = 12 ;		//nouvel hiver
			$this->versdebut = 4 ;
		}
		else {
			//mois normal
			if ($this->versdebut > 1) 	$this->versdebut = 0 ;
			if ($this->versfin > 1) 	$this->versfin = 0 ;
		}
		//traitement des tags thématiques
		$this->thematiques = "000000000" ;
		if (isset($_POST["thematiques"])) {
			foreach($_POST["thematiques"] as $tt) {
				$this->thematiques[$tt] = "1";
			}
		}
		//traitement du lieu
		if ($this->lieu != "" && $this->latitude == 0) {
			//première vérification : ce lieu est-il dans la table des loci ?
			$l = new locus() ;
			$l->get("nom", $this->lieu) ;
			if ($l->num > 0) {
				//oui - on prend les données existantes
				$this->longitude 	= $l->longitude ;
				$this->latitude 	= $l->latitude ;
				$this->locus 		= $l->num ;
			}
			else {
				//non, on tente une recherche google maps
				$string = str_replace (" ", "+", urlencode($this->lieu));
				$details_url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$string."&sensor=false";
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $details_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$response = json_decode(curl_exec($ch), true);
				// If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
				if ($response['status'] == 'OK') {
					$geometry = $response['results'][0]['geometry'];
					$this->longitude = $geometry['location']['lng'];
					$this->latitude = $geometry['location']['lat'];
					$l->longitude 	= $this->longitude ;
					$l->latitude 	= $this->latitude ;
					$l->nom			= $this->lieu ;
					$l->save() ;
					$this->locus 	= $l->num ;
				}
				
			}
			
		}
		return 1 ;
	}
	
	function aftertreatlienel() {
		//mise en ordre des liens el
		$lienel		= new lienel() ;
		$tag 		= new tag() ;
		$ssJson		= "" ;
		$tags		= "" ;
		if ($_POST["tags"] != "") {
			$tags = $_POST["tags"] ;
			if ($_POST["tagssuggeres"] != "") {
				$tags .= ",".$_POST["tagssuggeres"] ;
			}
		}
		else {
			if ($_POST["tagssuggeres"] != "") {
				$tags = $_POST["tagssuggeres"] ;
			}
		}
		if ($tags != "") {
			$tags = explode(",", $tags) ;
			if (count($tags) > 0) {
				foreach($tags as $ajoute) {
					$tag->num = 0 ;
					$tag->get("nom", trim($ajoute)) ;
					if ($tag->num > 0) {
						$lienel->num = 0 ;
						$lienel->select("WHERE tag = $tag->num AND evenement = $this->num") ;
						if ($lienel->lenen() < 1) {
							$lienel->tag = $tag->num ;
							$lienel->evenement = $this->num ;
							$lienel->save() ;
							$ssJson .= $lienel->json()."," ;
							$tag->occurenceJour++ ;
							$tag->save() ;
						}
					}
				}
			}
		}
		$lienel->select("WHERE evenement = $this->num") ;
		$lesLiensEL = array() ;
		//while ($row = mysqli_fetch_assoc($lienel->iq)) {
		while ($row = mysqldb::$req->fetch()) {
			$lesLiensEL[] = $row ;
		}
		mysqldb::$req->closeCursor() ;
		$this->eJson .= '"lienEL" : '.json_encode($lesLiensEL).',' ;		//['.substr($ssJson, 0, -1)."]," ;
		return 1 ;
	} //aftertreatliensel
		
	function aftertreatliensep() {
		//mise en ordre des liens ep
		//création des éventuelles personnes à ajouter
		$lienep		= new lienep() ;
		$personne	= new personne() ;
		$ssJson		= "" ;
		$personnes 	= "" ;
		if ($_POST["personnes"] != "") {
			$personnes = $_POST["personnes"] ;
			if (isset($_POST["personnessuggerees"]) and $_POST["personnessuggerees"] !="") {
				$personnes .= ",".$_POST["personnessuggerees"] ;
			}
		}
		else {
			if (isset($_POST["personnessuggerees"]) and $_POST["personnessuggerees"] !="") {
				$personnes = ",".$_POST["personnessuggerees"] ;
			}
		}
		if ($personnes != "") {
			$personnes = explode(",", $personnes) ;
			if (count($personnes) > 0) {
				foreach($personnes as $ajoute) {
					if ($ajoute != "") {
						$personne	= new personne() ;
						$personne->creeridentifie(trim($ajoute)) ;
						$lienep->num = 0 ;
						$lienep->select("WHERE personne = $personne->num AND evenement = $this->num") ;
						if ($lienep->lenen() < 1) {
							$lienep->personne = $personne->num ;
							$lienep->evenement = $this->num ;
							$lienep->save() ;
							$ssJson .= $lienep->json()."," ;
							$personne->occurence++ ;
							$personne->occurenceJour++ ;
							$personne->save() ;
						}
					}
				}
			}
		}
		$lienep->select("WHERE evenement = $this->num") ;
		$lesLiensEP = array() ;
		//while ($row = mysqli_fetch_assoc($lienep->iq)) {
		while ($row = mysqldb::$req->fetch()) {
			$lesLiensEP[] = $row ;
		}
		mysqldb::$req->closeCursor() ;
		$this->eJson .= '"lienEP" : '.json_encode($lesLiensEP).',' ;
		return 1 ;
	} //aftertreatliensep
	
	function aftertreat() {
		$this->get($this->num) ;
		$this->aftertreatlienel() ;
		$this->aftertreatliensep() ;
		$this->save() ;
		return 1 ;
	}

	function delete() {
		//suppression des liens el liés
		$lienel = new lienel() ;
		$lienel->select("where evenement = $this->num") ;
		while ($lienel->next()) $lienel->delete() ;
		//suppression des liens ep liés
		$lienep = new lienep() ;
		$lienep->select("where evenement = $this->num") ;
		while ($lienep->next()) $lienep->delete() ;
		return mysqldb::deleteline($this->table, $this->num); 
	}
}
?>
