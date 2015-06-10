<?php
class personne extends alpha {
	
	var $nom ;
	var $autreNom ;
	var $dynastie ;
	var $url ;
	var $sexe ;	//h ou f
	var $naissance_evenementId ;		//naissance et mort sont des références vers des évènements
	var $annaissance ;
	var $mort_evenementId ;
	var $anmort ;
	var $vivant ;		//0 -> non, 1 -> oui
	var $notice ;
	var $valide ;
	var $pere ;		//propriété hors table
	var $mere ;
	var $occurence ;
	var $occurenceJour ;
	
	var $personne ;
	var $lieunaissance ;
	var $versnaissance ;
	var $lieumort ;
	var $versmort ;
	var $dynastie_Id ;
	
	var $fields 	= array("num", "nom", "autreNom", "dynastie", "url", "sexe", "naissance_evenementId", "annaissance", "mort_evenementId", "anmort", "vivant", "notice", "valide", "occurence", "occurenceJour") ;
	var $jsonliste 	= array("num", "nom", "autreNom", "url", "annaissance", "naissance_evenementId", "anmort", "mort_evenementId", "vivant", "dynastie", "sexe", "notice", "occurence", "valide") ;
	
	function autocomplete($field='nom', $first='', $limit=15) {
		//recherche par défaut pour un champ autocomplete
		//$first = utf8_decode(strtolower(stripslashes($first))) ;
		$first = str_ireplace("â", "_", $first) ;	
		$first = str_ireplace("é", "_", $first) ;
		$first = str_ireplace("è", "_", $first) ;
		$first = str_ireplace("ç", "_", $first) ;
		$first = str_ireplace("ï", "_", $first) ;		
		$first = str_ireplace("î", "_", $first) ;
		$first = str_ireplace("ü", "_", $first) ;	
		$first = str_ireplace("-", "_", $first) ;	
		//echo $first ;
		$first = stripslashes($first) ;
		$lq = strlen($first) ;
		$q = 'WHERE (nom LIKE "'.$first.'%") ORDER BY occurenceJour DESC, occurence DESC, nom LIMIT 0, '.$limit ;
		$this->select($q) ;
		$string = '<ul>' ;
		if ($this->lenen() > 0) {
			while($this->next()) {
				$string .= '<li id="'.$this->num.'">'.$this->nom.'<span class="informal"> ' ;
				if ($this->annaissance <> "" AND $this->anmort <> "") {
					$string .= " (".$this->annaissance."-".$this->anmort.") " ;
				}
				elseif ($this->annaissance <> "") {
					$string .= " (".$this->annaissance."- ?)" ;
				}
				elseif($this->anmort <> "") {
					$string .= " (? -".$this->anmort.")" ;
				}
				$string .= '</span></li>' ;
			}
		}
		$this->select('WHERE (autreNom LIKE "%'.$first.'%") ORDER BY occurenceJour DESC, occurence DESC, autreNom LIMIT 0,5') ;
		if ($this->lenen() > 0) {
			while($this->next()) {
				$string .= '<li id="'.$this->num.'"><span class="informal">'.$this->autreNom.'  (</span>'.$this->nom.'<span class="informal">)' ;
				if ($this->annaissance <> "" AND $this->anmort <> "") {
					$string .= " (".$this->annaissance."-".$this->anmort.") " ;
				}
				elseif ($this->annaissance <> "") {
					$string .= " (".$this->annaissance."- ?)" ;
				}
				elseif($this->anmort <> "") {
					$string .= " (? -".$this->anmort.")" ;
				}
				$string .= '</span></li>' ;
			}
		}
		$string .= '</ul>' ;
		return $string ;
	}
	
	function beforetreat() {
		//Fonction de traitement
		//1) conversion de la dynastie littérale en un numéro
		//normalement, le JS nous a passé la valeur de cette dynastie dans dynastie_Id, ce qui permet d'éviter une requête pour aller le chercher dans la base à partir du nom.
		if ($this->dynastie_Id > 0) {
			$this->dynastie = $this->dynastie_Id ;
		}
		else {
			if ($this->dynastie <> "") {
				$dyn = new dynastie() ;
				$dyn->get("nom", $this->dynastie) ;
				if ($dyn->num > 0) {
					$dyn->occurenceJour = $dyn->occurenceJour + 1 ;
					$this->dynastie = $dyn->num ;
					$dyn->save() ;
				}
			}
		}
		//2) ajout automatique d'un point à la notice s'il n'y en a pas (sous réserve que la notice ne soit pas vide
		$this->notice = trim($this->notice) ;
		if ($this->notice <> "" && substr($this->notice, -1, 1) <> ".") {
			$this->notice .= "." ;
		}
		$this->notice = ucfirst($this->notice) ;
		//3) ajout de l'occurenceJour
		$this->occurenceJour = $this->occurenceJour + 2 ;
		//création du valide
		$this->valide = 1 ;
		return true ;
	}

	function aftertreat() {
		//1. les tags
		$this->traiteTags() ;
		//2. La naissance
		$this->naissancemort() ;
		//3. Les liens personnes / personnes
		$this->traiteLienPP() ;
		//4. La filiation à la naissance
		$this->evenementnfiliation() ;
		return true ;
	}
	
	function traiteTags() {
		//traite les tags et les inscrits dans les liens pl
		$lienpl			= new lienpl() ;
		$tag 			= new tag() ;
		$jsonTags		= array() ;
		if (isset($_POST["tags"]) AND ($_POST["tags"] != "")) {
			$tags = explode(",", $_POST["tags"]) ;
			if (count($tags) > 0) {
				foreach($tags as $ajoute) {
					$tag->num = 0 ;
					$tag->get("nom", trim($ajoute)) ;
					if ($tag->num > 0) {
						$lienpl->num = 0 ;
						$lienpl->lieu = $tag->num ;
						$lienpl->personne = $this->num ;
						$lienpl->save() ;
						$jsonTags[]= $lienpl->json() ;
						$tag->occurenceJour = $tag->occurenceJour + 1 ;
						$tag->save() ;
					}
				}
			}
		}
		$this->eJson .= '"lpt":['.implode(", ", $jsonTags).'],' ;
	} //traiteTags
	
	function traiteLienPP() {
		$ssJson	= "" ;
		$lpp = new relationpersonnes() ;
		$p2 = new personne() ;
		if (isset($_POST["parents"]) AND $_POST["parents"] != "") {
			$parents = explode(",", $_POST["parents"]) ;
			if (count($parents) > 0) {
				$chaineparents = "" ;
				foreach($parents as $personne) {
					$lpp->num = 0 ;
					$lpp->personne1 = $this->num ;
					$lpp->typerelation = 1 ;
					$lpp->personne2 = $p2->creeridentifie($personne) ;
					$lpp->save() ;
					$ssJson .= $lpp->json()."," ;
					if ($chaineparents == "") $chaineparents = $personne ;
					else $chaineparents .= " et de ".$personne ;
				}
				if ($this->naissance_evenementId > 1) {
					$en = new evenement() ;
					$en->get($this->naissance_evenementId) ;
					if ($this->sexe == "f") $en->precisions = "Fille de $chaineparents" ;
					else $en->precisions = "Fils de $chaineparents" ;
					$en->save() ;
				}
			}
			//et on retraite la précision
			/*
			//ensuite, on prend le père
			if ($this->pere <> "")
			{
				$pere = new personne() ;
				$pere->creeridentifie($this->pere) ;
				$lienep = new lienep() ;
				//et on vérifie qu'il est lié à l'évènement de naissance
				$n = $lienep->count("where personne = $pere->num and evenement = $en->num") ;
				if ($n == 0) {
					//ce n'est pas le cas : on fait le lien
					$lienep->num = 0 ;
					$lienep->personne = $pere->num ;
					$lienep->evenement = $en->num ;
					$lienep->save() ;
					$lienep->num = 0 ;
				}
			}
			//idem pour la mère
			if ($this->mere <> "")
			{
				$mere = new personne() ;
				$mere->creeridentifie($this->mere) ;
				$lienep = new lienep() ;
				$n = $lienep->count("where personne = $mere->num and evenement = $en->num") ;
				if ($n == 0) {
					$lienep->num = 0 ;
					$lienep->personne = $mere->num ;
					$lienep->evenement = $en->num ;
					$lienep->save() ;
					$lienep->num = 0 ;
				}
			}
		}
		return 1 ;
			}*/
		}
		if (isset($_POST["enfants"]) AND $_POST["enfants"] != "") {
			$enfants = explode(",", $_POST["enfants"]) ;
			if (count($enfants) > 0) {
				foreach($enfants as $personne) {
					$lpp->num = 0 ;
					$lpp->personne2 = $this->num ;
					$lpp->typerelation = 1 ;
					$lpp->personne1 = $p2->creeridentifie($personne) ;
					$lpp->save() ;
					$ssJson .= $lpp->json()."," ;
				}
			}
		}
		if (isset($_POST["conjoints"]) AND $_POST["conjoints"] != "") {
			$conjoints = explode(",", $_POST["conjoints"]) ;
			if (count($conjoints) > 0) {
				foreach($conjoints as $personne) {
					$lpp->num = 0 ;
					$lpp->personne2 = $this->num ;
					$lpp->typerelation = 3 ;
					$lpp->personne1 = $p2->creeridentifie($personne) ;
					$lpp->save() ;
					$ssJson .= $lpp->json()."," ;
				}
			}
		}		
		if ($ssJson != "") $this->eJson .= '"lpp" : ['.substr($ssJson, 0, -1)."]," ;
	} //traiteLienPP
	
	function naissancemort() {
		//appels :
		//quand on sauve la personne, les paramètres peuvent être :
		//- un évènement existant (naissance / mort)
		//- une date
		//- des informations de précision
		//- un tag de naissance
		if ($_POST["andebutdatenaissance"] <> "") {
			if ($_POST["andebutdatenaissance"] == "?") {
				//la date de naissance de ce personnage est ignorée
				$this->naissancemortignoree("naissance") ;
			}
			else {
				//une date de naissance a été fixée => il faut en faire un évènement
				$this->NewEvenementnm("naissance") ;
			}
		}
		if ($_POST["andebutdatemort"] <> "") {
			if ($_POST["andebutdatemort"] == "?") {
				//la date de naissance de ce personnage est ignorée
				$this->naissancemortignoree("mort") ;
			}
			else {
				//une date de naissance a été fixée => il faut en faire un évènement
				$this->NewEvenementnm("mort") ;
			}			
		}
		$this->evenementnmlinks() ;
		$this->creerfiliation() ;
		$this->tagsevenementnm() ;
		$this->checknaissancemort() ;
	}
	
	function naissancemortignoree($nm="mort") {
		//naissance ou mort sont ignorées
		//nm prend les valeurs naissance ou mort selon le cas
		//echo "Naissance mort ignorée ! " ;
		if ($nm <> "mort" and $nm <> "naissance") return false ;
		$a = "an".$nm ;
		$this->$a = "?" ;
		$b = $nm."_evenementId" ;
		$this->$b = 0 ;
		//echo "je sauve !" ;
		return $this->save() ;
	}
	
	function NewEvenementnm($nm = "mort") {
		//crée un nouvel évènement naissance ou mort
		//nm prend les valeurs naissance ou mort selon le cas
		//cette fonction retourne l'évènement ainsi créé
		if ($nm <> "mort" and $nm <> "naissance") return false ;
		//echo "Création de l'évènement $nm...." ;
		$e = new evenement() ;
		//description de l'évènement
		$pl = strtolower(substr($this->nom, 0, 1)) ;
		if ($pl == "a" or $pl == "e" or $pl == "i" or $pl == "o" or $pl == "u" or $pl == "y") $e->description = ucfirst($nm)." d'".$this->nom ;
		else $e->description = ucfirst($nm)." de ".$this->nom ;
		$locus = "lieu".$nm ;
		$locus_Id = "lieu".$nm."_Id" ;
		if ($this->$locus <> "") {
			$e->description .= " (".$this->$locus.")" ;
			$e->lieu = $this->$locus ; 
			$string = str_replace (" ", "+", urlencode($this->$locus));
			$details_url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$string."&sensor=false";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $details_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$response = json_decode(curl_exec($ch), true);
 			// If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
			if ($response['status'] == 'OK') {
				$geometry = $response['results'][0]['geometry'];
				$e->longitude = $geometry['location']['lng'];
				$e->latitude = $geometry['location']['lat'];
			}
		}
		$e->description .= "." ;
		//date de l'évènenement
		$e->andebut = $_POST["andebutdate".$nm] ;
		if (isset($_POST["moisdebutdate".$nm])) $e->moisdebut = $_POST["moisdebutdate".$nm] ;
		if (isset($_POST["jourdebutdate".$nm])) $e->jourdebut = $_POST["jourdebutdate".$nm] ;
		if ($e->andebut == "") return false() ;
		//précision de l'évèneement
		if (isset($_POST["versdebutdate".$nm])) $e->versdebut = 1 ;
		
		//étoiles
		$e->etoiles = 0 ;
		if ($nm == "naissance") $e->typeeve = 13 ;	//évenement de type naissance
		//on sauve
		$e->save() ;
		//tag de l'évènement
		if ($this->$locus_Id > 0) {
			$lienel = new lienel() ;
			$lienel->evenement = $e->num ;
			$lienel->tag = $this->$locus_Id ;
			$lienel->save() ;
		}
		//on met cet évènement comme évènement de naissance ou mort de la personne
		$b = $nm."_evenementId" ;
		$this->$b = $e->num ;
		$c = "an".$nm ;
		$this->$c = $e->andebut ;
		$this->save() ;
		return 1 ;
	}
	
	function evenementnmlinks() {
		//vérifie ou crée le lien entre la personne et ses évènements de naissance et de mort
		//echo "Check liens naissance/mort et personne..." ;
		$lienep = new lienep() ;
		//naissance
		if ($this->naissance_evenementId > 1) {
			$n = $lienep->count("where evenement = $this->naissance_evenementId and personne = $this->num") ;
			if ($n == 0) {
				$lienep->num = 0 ;
				$lienep->evenement = $this->naissance_evenementId ;
				$lienep->personne = $this->num ;
				$lienep->save() ;
				$lienep->num = 0 ;
				//echo "Création du lien évènement de naissance / personne..." ;
			}
		}
		//mort
		if ($this->mort_evenementId > 1) {
			$n = $lienep->count("where evenement = $this->mort_evenementId and personne = $this->num") ;
			if ($n == 0) {
				$lienep->num = 0 ;
				$lienep->evenement = $this->mort_evenementId ;
				$lienep->personne = $this->num ;
				$lienep->save() ;
				$lienep->num = 0 ;
				//echo "Création du lien évènement de mort / personne..." ;
			}
		}
		return 1 ;
	}
	
	function evenementnfiliation() {
		//pour les évènements de naissance : vérifie que les deux parents sont liés
		//et ajoutés dans la précision
		$en = new evenement() ;
		//on prend l'évènement de naissance
		if ($this->naissance_evenementId > 1) {
			$en->get($this->naissance_evenementId) ;
			if ($this->pere <> "" or $this->mere <> "")
			{
				if ($this->sexe == "f")
				{
					$en->precisions = "Fille de " ;
				}
				else $en->precisions = "Fils de ";
				if ($this->pere <> "" and $this->mere <> "")
				{
					$en->precisions .= $this->pere." et de ".$this->mere ;
				}
				else {
					$en->precisions .= $this->pere.$this->mere ;
				}
			}
			//et on retraite la précision
			$en->save() ;
			//ensuite, on prend le père
			if ($this->pere <> "")
			{
				$pere = new personne() ;
				$pere->creeridentifie($this->pere) ;
				$lienep = new lienep() ;
				//et on vérifie qu'il est lié à l'évènement de naissance
				$n = $lienep->count("where personne = $pere->num and evenement = $en->num") ;
				if ($n == 0) {
					//ce n'est pas le cas : on fait le lien
					$lienep->num = 0 ;
					$lienep->personne = $pere->num ;
					$lienep->evenement = $en->num ;
					$lienep->save() ;
					$lienep->num = 0 ;
				}
			}
			//idem pour la mère
			if ($this->mere <> "")
			{
				$mere = new personne() ;
				$mere->creeridentifie($this->mere) ;
				$lienep = new lienep() ;
				$n = $lienep->count("where personne = $mere->num and evenement = $en->num") ;
				if ($n == 0) {
					$lienep->num = 0 ;
					$lienep->personne = $mere->num ;
					$lienep->evenement = $en->num ;
					$lienep->save() ;
					$lienep->num = 0 ;
				}
			}
		}
		return 1 ;
	}
	
	function creerfiliation() {
		//on inscrit la filiation dans les tables
		//à partir de this->pere et this->mere
		/*pp = new relationpersonnes() ;
		if ($this->pere<> "") {
			$pere = new personne() ;
			$pere->identify($this->pere) ;
			$n = $rpp->count("where personne1 = $this->num and personne2 = $pere->num and typerelation = 1") ;
			if ($n == 0) {
				$rpp->personne1 = $this->num ;
				$rpp->personne2 = $pere->num ;
				$rpp->typerelation = 1 ;
				$rpp->num = 0 ;
				$rpp->save() ;
			}
		}
		if ($this->mere<> "") {
			$mere = new personne() ;
			$mere->identify($this->mere) ;
			$mere->sexe = "f" ;
			$mere->save() ;
			$n = $rpp->count("where personne1 = $this->num and personne2 = $mere->num and typerelation = 1") ;
			if ($n == 0) {
				$rpp->personne1 = $this->num ;
				$rpp->personne2 = $mere->num ;
				$rpp->typerelation = 1 ;
				$rpp->num = 0 ;
				$rpp->save() ;
			}
		}
		//echo "fraternité" ;
		//vérifications des liens de fraternité
		//$rpp = new relationspersonnes() ;
		$rpp2 = new relationpersonnes() ;
		//même père
		if (isset($pere)) {
			//echo "il y a un père" ;
			$rpp->select("where personne1 <> $this->num AND personne2 = $pere->num and typerelation = 1") ;
			while ($rpp->next()) {
				$frere = $rpp->personne1 ;
				$n = $rpp2->count("where ((personne1 = $this->num and personne2 = $frere) or (personne2 = $this->num and personne1 = $frere) and typerelation = 2)") ;
				if ($n == 0) {
					//on créée la relation
					$rpp2->num = 0 ;
					$rpp2->personne1 = $this->num ;
					$rpp2->personne2 = $frere ;
					$rpp2->typerelation = 2 ;
					$rpp2->save() ;
				}
			}	
		}
		//echo "fraternité fin" ;*/
		return 1 ;
	}
	
	function tagsevenementnm() {
		//vérifie, pour la naissance comme pour la mort
		//que les tags de la personne sont biens rentrés
		//on prend les tags de la personne
		$lienpl = new lienpl() ;
		$lienel = new lienel() ;
		$lienel->num = 0 ;
		$lienpl->select("where personne = $this->num") ;
		while ($lienpl->next()) {
			//1. naissance
			$n = $lienel->count("where evenement = $this->naissance_evenementId and tag = $lienpl->lieu") ;
			if ($n == 0) {
				$lienel->evenement = $this->naissance_evenementId ;
				$lienel->tag = $lienpl->lieu ;
				$lienel->num = 0 ;
				$lienel->save() ;
			}
			//2. mort
			$n = $lienel->count("where evenement = $this->mort_evenementId and tag = $lienpl->lieu") ;
			if ($n == 0) {
				$lienel->evenement = $this->mort_evenementId ;
				$lienel->tag = $lienpl->lieu ;
				$lienel->num = 0 ;
				$lienel->save() ;
			}
		}		
		return 1 ;	
	}
				
	function checknaissancemort() {
		//vérifie, si les évènements de naissance ou de mort sont bien remplis
		//que cela coïncide avec les champs annaissance et anmort
		$m = 0 ;
		if ($this->naissance_evenementId > 0)
		{
			//naissance
			$en = new evenement() ;
			$en->get($this->naissance_evenementId) ;
			if ($en->andebut != $this->annaissance) {
				$this->annaissance = $en->andebut ;
				//ici, il faudra préciser
				$m = 1 ;
			}
		}
		if ($this->mort_evenementId > 0) {
			$em = new evenement() ;
			$em->get($this->mort_evenementId) ;
			if ($em->andebut != $this->anmort) {
				$this->anmort = $em->andebut ;
				//ici, il faudra préciser
				$m = 1 ;
			}
		}
		if ($m == 1) {
			return $this->save() ;
		}
		else return false ;
	}

	function displaypourarbre($ep=1) {
		//ep = 1 : on affiche les époux également
		
		
		if ($this->nom == "?" or $this->num == 0) 
		{
			$chaine = "<a href='o_ajouter.php?objet=personne'>?</a>" ;
			return $chaine ;
		}
		$chaine = "<a href='arbregen.php?personne=$this->num'><img src='../test/arbre2.php?personne=$this->num&ep=$ep' border=0></a>" ;
		/*
		$chaine = "" ;
		if ($this->url <> "") 
		{
			$fin = strtolower(substr($this->url, -4, 4)) ;
			if ($fin == ".png" or $fin==".jpg"  or $fin == "jpeg" or $fin=".gif")
				$chaine .= "<img src='$this->url' border=0 height=100><br />" ;
		}
		else {
			if ($this->sexe == "f") {
				$chaine .= "<img src='../img/femme.jpg' border=0 height=100><br />" ;
			}
			else {
				$chaine .= "<img src='../img/homme.jpg' border=0 height=100><br />" ;
			}
		}
		$chaine .= "<a href='arbregen.php?personne=$this->num' title='".htmlspecialchars($this->display(5), ENT_QUOTES, "UTF-8")."'><b>$this->nom</b></a><br />" ;
		if ($this->naissance_evenementId != 0 or $this->mort_evenementId != 0) 
		{
			if ($this->naissance_evenementId > 0 and $this->mort_evenementId> 0) 
			{
				$naissance = new evenement() ;
				$naissance->get($this->naissance_evenementId) ;
				$chaine .= " (".$naissance->andebut." - " ;
				$mort = new evenement() ;
				$mort->get($this->mort_evenementId) ;
				$chaine .= $mort->andebut.") " ;
			}
			else 
			{
				if ($this->naissance_evenementId > 0) 
				{
					$naissance = new evenement() ;
					$naissance->get($this->naissance_evenementId) ;
					$chaine .= " (né en ".$naissance->andebut.")" ;
				}
				if ($this->mort_evenementId > 0) 
				{
					$mort = new evenement() ;
					$mort->get($this->mort_evenementId) ;
					$chaine .= " (mort en ".$mort->andebut.") " ;
				}
			}
		}
		$chaine .= "<font size='2'>" ;
		if ($this->num > 0)
		{
			$lienpf = new lienpf() ;
			$lienpf->select("WHERE personne = $this->num") ;
			while ($lienpf->next()) {
				$chaine .= "<br />".$lienpf->display(0) ;
			}
			if ($ep > 0) {
				$rpp = new relationpersonnes() ;
				$epouse = new personne() ;
				$rpp->select("where (personne1=$this->num OR personne2=$this->num) and typerelation = 3") ;
				if ($rpp->lenen() > 0) {
					while ($rpp->next()) {
						if ($rpp->personne1 == $this->num) {
							$epouse->get($rpp->personne2) ;
						}
						else {
							$epouse->get($rpp->personne1) ;
						}
						$chaine .= "<br/><i>épouse ".$epouse->display()."</i>" ;
					}
				}
			}
			$chaine .= "</font>" ;
		}*/
		return $chaine ;
	}
	
	function displaypourarbreimage($im, $xx=200, $yy=255, $x, $y, $ep=1) {
		$photo = $xx * 0.40 ;
		global $rouge, $blanc, $noir, $font_file, $italic ; 
		if ($this->num == 0) {
			return 0 ;
		}
		
		imagerectangle ($im, $x, $y, $x+$xx-1, $y+$yy-1, $noir) ;
		
		//dynastie
		if ($this->dynastie <> "") {
			$dynastie = new dynastie() ;
			$dynastie->get($this->dynastie) ;
			$red = 100; 
			$green = 100; 
			$blue = 100; 
			if ($dynastie->couleur <> "") {
				// Split the HTML color representation 
				$hexcolor = str_split($dynastie->couleur, 2); 
				// Convert HEX values to DECIMAL 
				$bincolor[0] = hexdec("0x{$hexcolor[0]}"); 
				$bincolor[1] = hexdec("0x{$hexcolor[1]}"); 
				$bincolor[2] = hexdec("0x{$hexcolor[2]}"); 
				$dyncolor = ImageColorAllocate($im, $bincolor[0], $bincolor[1], $bincolor[2]); 
				imageline($im, $x, $y, $x+25, $y, $dyncolor) ;
				imageline($im, $x+25, $y, $x, $y+25, $dyncolor) ;
				imageline($im, $x, $y, $x, $y+25, $dyncolor) ;
				imagefill($im, $x+5, $y+5, $dyncolor) ;
				imagerectangle ($im, $x, $y, $x+$xx-1, $y+$yy-1, $dyncolor) ;
			}
			else {
				imagerectangle ($im, $x, $y, $x+$xx-1, $y+$yy-1, $noir) ;
			}
		}
		else {
			imagerectangle ($im, $x, $y, $x+$xx-1, $y+$yy-1, $noir) ;
		}

		// image
		$newheight = 0 ;
		/*
		$filename = "" ;
		if ($this->url <> "")  {
			$fin = strtolower(substr($this->url, -4, 4)) ;
			if ($fin == ".png" or $fin==".jpg"  or $fin == "jpeg" or $fin=".gif") {
				$filename .= $this->url ;
			}
			else {
				if ($this->sexe == "f") {
					$filename .= '../img/femme.jpg' ;
				}
				else {
					$filename .= '../img/homme.jpg' ;
				}
			}
			list($width, $height) = getimagesize($filename);
			$rapport = $photo/$width ;
			$newheight = $rapport * $height ;

			$source = imagecreatefromjpeg($filename);
			// Redimensionnement
			imagecopyresized($im, $source, $x+$xx/2-$photo/2, $y+10, 0, 0, $photo, $newheight, $width, $height);	
		}*/
		//nom
		$dnn = preg_match('/[0-9]{4}/', $this->nom) ;
		if ($dnn> 0) $this->nom=substr($this->nom, 0, -11) ;
		$longueur = $xx +15 ;
		$police = 15 ;
		while(($longueur+8)> $xx) {
			$police = $police - 0.2 ;
			$bbox = imagettfbbox ($police, 0, $font_file, $this->nom) ;
			$longueur = $bbox[2] ;
		}
		$milieu = ($xx/2) - $longueur / 2 ;
		imagefttext($im, $police, 0, $x+$milieu, $y+30+$newheight, $rouge, $font_file, $this->nom);

		//dates de naissance et de mort
		$chaine = "" ;
		if ($this->naissance_evenementId != 0 or $this->mort_evenementId != 0) {
			if ($this->naissance_evenementId > 0 and $this->mort_evenementId> 0) 
			{
				$naissance = new evenement() ;
				$naissance->get($this->naissance_evenementId) ;
				$chaine .= " (".$naissance->andebut." - " ;
				$mort = new evenement() ;
				$mort->get($this->mort_evenementId) ;
				$chaine .= $mort->andebut.") " ;
			}
			else 
			{
				if ($this->naissance_evenementId > 0) 
				{
					$naissance = new evenement() ;
					$naissance->get($this->naissance_evenementId) ;
					$chaine .= " (né en ".$naissance->andebut.")" ;
				}
				if ($this->mort_evenementId > 0) 
				{
					$mort = new evenement() ;
					$mort->get($this->mort_evenementId) ;
					$chaine .= " (mort en ".$mort->andebut.") " ;
				}
			}
		}
		$bbox = imagettfbbox(10,0, $font_file, $chaine) ;
		$longueur = $bbox[2] ;
		$milieu = $xx/2 - $longueur / 2 ;
		imagefttext($im, 10, 0, $x+$milieu, $y+50+$newheight, $noir, $font_file, $chaine);

		//époux
		if ($ep == 1 and $this->num > 0) {
			$rpp = new relationpersonnes() ;
			$epouse = new personne() ;
			$rpp->select("where (personne1=$this->num OR personne2=$this->num) and typerelation = 3") ;
			if ($rpp->lenen() > 0) {
				$chaine = "épouse " ;
				while ($rpp->next()) {
					if ($rpp->personne1 == $this->num) {
						$epouse->get($rpp->personne2) ;
					}
					else {
						$epouse->get($rpp->personne1) ;
					}
					$chaine .= $epouse->display().", " ;
				}
				
				
				$chaine = substr($chaine, 0, -2) ;
				
				$longueur = $xx +15 ;
				$police = 12 ;
				while(($longueur+20)> $xx) {
					$police-- ;
					$bbox = imagettfbbox($police,0, $italic, $chaine) ;
					$longueur = $bbox[2] ;
				}
				$milieu = $xx/2 - $longueur / 2 ;
				imagefttext($im, $police, 0, $x+$milieu, $y+65+$newheight, $noir, $italic, $chaine);
			}
		}
	}
	
	function creeridentifie($chaine) {
		//s'il existe une personne $chaine, cette personne est chargée
		//sinon, elle est créée et chargée
		if (trim($chaine) == "") return 0 ;
		if (!$this->get("nom", $chaine)) {
			$this->nom = trim($chaine) ;
			$this->autreNom = "" ;
			$this->dynastie = "" ;
			$this->url = "";
			$this->sexe = "" ;	//h ou f
			$this->naissance_evenementId = 0 ;		//naissance et mort sont des références vers des évènements
			$this->annaissance = "" ;
			$this->mort_evenementId = 0;
			$this->anmort = "" ;
			$this->vivant = 0 ;		//0 -> non, 1 -> oui
			$this->notice = "";
			$this->valide = 0 ;
			$this->occurence = 0 ;
			$this->occurenceJour = 0 ;
			$this->num = 0 ;
			$this->valide = 0 ;
			$this->save() ;
		}
		return $this->num ;
	}

	function delete() {
		$rpp = new relationpersonnes() ;
		$rpp->select("WHERE personne1 = $this->num OR personne2 = $this->num") ;
		while ($rpp->next()) {
			$rpp->delete() ;
		}
		$l = new lienep() ;
		$l->select("WHERE personne = $this->num") ;
		while ($l->next()) {
			$l->delete() ;
		}
		return mysqldb::deleteline($this->table, $this->num) ;
	}
	
	function getparents() {
		$rep = array() ;
		$rpp = new relationpersonnes ;
		$rpp->select("where personne1 = $this->num and typerelation = 1") ;
		$rep = array() ;
		while ($rpp->next())
		{
			$rep[] = $rpp->personne2 ;
		}
		return $rep ;
	}
	
	function getenfants() {
		$rep = array() ;
		$rpp = new relationpersonnes ;
		$rpp->select(", personne where (personne2 = $this->num) and (typerelation = 1) and (personne1 = personne.num) order by personne.annaissance") ;
		while ($rpp->next())
		{
			$rep[] = $rpp->personne1 ;
		}
		return $rep ;
	}
}
?>
