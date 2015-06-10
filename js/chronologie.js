/* 
Chronologie - Tempus fugit
début des travaux : août 2011
*/

verbose = 1 ;	//1 pour obtenir la verbosité


//à exécuter dès le chargement du script
//création des évènements
cetteAnnee 			= new Date().getFullYear() ;
var CTFA = {
	//CTFA est l'objet application. Il contient les éléments invariants.
	"version":"4.2.6.0",
	"auteur":"Matthieu Duclos",
	"typeTags" : ["Territoire", "Ville", "Régime", "Peuple", "Dynastie", "Musée", "Continent", "Etat", "Concepts", "Organisation", "Courant de pensée", "Mer", "Ile", "Astre", "Etat fédéré", "Ville et territoire"],
	"pr":0,
	"urlBaseDrapeau":localisation + 'dataimg/',
	
	//initialisation
	"initialise" : function () {
		//fonction appelée lorsque la page est chargée pour initialiser les données essentielles
		messager.affiche("Initialisation des données.") ;
		menu.initialise() ;
		CTFA.enTete.initialise() ;
		//chargement des données de base : tags, dynasties, fonctions, liensTT
		if (localStorage.hasOwnProperty("tag") && localStorage.hasOwnProperty("lientt") && localStorage.hasOwnProperty("fonction") && localStorage.hasOwnProperty("dynastie")) {
			console.log("Chargement des données depuis le localStorage.") ;
			//on a une sauvegarde locale pour les objets
			lesTags.donnees 		= JSON.parse(localStorage.tag) ;
			lesTags.charge			= 1 ;
			lesLiensTT.donnees 		= JSON.parse(localStorage.lientt) ;
			lesLiensTT.charge		= 1 ;
			lesFonctions.donnees 	= JSON.parse(localStorage.fonction) ;
			lesFonctions.charge		= 1 ;
			lesDynasties.donnees 	= JSON.parse(localStorage.dynastie) ;
			lesDynasties.charge		= 1 ;
			CTFA.initialise2() ;
			lesTags.charge == 0 ;
			lesFonctions.charge == 0 ;
			lesDynasties.charge == 0 ;
			lesLiensTT.charge == 0 ;
			setTimeout(CTFA.requeteObtientAjax, 55000) ;	//dans 45 secondes, on rechargera les données de base
			setTimeout(function() { 
				CTFA.pr=2 ;
				CTFA.initialise2() ;
			}, 62000) ;	//dans 45 secondes, on rechargera les données de base
		}
		else {
			messager.affiche("Initialisation des données à partir de la base de données.") ;
			lesTags.requeteObtient() ;
			lesLiensTT.requeteObtient() ;
			lesFonctions.requeteObtient() ;
			lesDynasties.requeteObtient() ;
		}
		//lancement des les listeners
		$('rcsubmit').observe("click", function(e) {Requete.soumissionFormulaire(e) ; } ) ;
		//cache du contexte
		donnees.contexte.initialise() ;
		//sur la recherche interne à certains espaces
		$('boiteRecherchertagChamp').observe("change", function (e) {lesTags.recherche() ;}) ; 
		$('boiteRechercherfonctionChamp').observe("change", function (e) {lesFonctions.recherche() ;}) ; 
		$('boiteRechercherdynastieChamp').observe("change", function (e) {lesDynasties.recherche() ;}) ;
		//affichage des scripts	
		afficheScripts() ;	
		//passage en mode chronos
		lesTypesdEvenements.modeChronos() ;
		return 1 ;		
	},
	
	"requeteObtientAjax":function() {
		//Cette fonction est appelée 45 secondes après l'exécution du script précédent.
		//Elle charge en mémoire depuis la base de données par l'intermédiaire du PHP les données de tags, liensTT, fonctions, dynasties.
		messager.affiche("Rechargement des données de base.") ;
		console.log("Rechargement des données de base par AJAX.") ;
		CTFA.pr = 2 ;	//on remet pr à 0 pour exécuter à nouveau la fonction initialise2
		lesTags.charge == 0 ;
		lesFonctions.charge == 0 ;
		lesDynasties.charge == 0 ;
		lesLiensTT.charge == 0 ;
		lesTags.requeteObtient() ;
		lesLiensTT.requeteObtient() ;
		lesFonctions.requeteObtient() ;
		lesDynasties.requeteObtient() ;
		messager.affiche("Données chargées") ;
		console.log("On attend désormais l'exécution de initialise 2.") ;
		return 1 ;
	},
	
	"initialise2" : function() {
		if (CTFA.pr == 1) return -1 ; //déjà exécutée
		//deuxième partie - les 4 séries de données de base ont été chargées
		if (lesTags.charge == 1 & lesFonctions.charge == 1 & lesDynasties.charge == 1 & lesLiensTT.charge == 1) {
			//traitement
			lesTags.triEtCorrige() ;
			lesFonctions.triEtCorrige() ;
			lesDynasties.triEtCorrige() ;
			//objets nom
			lesTags.lesNoms 		= lesTags.donnees.collect(function(e) { return e.nom }) ;
			lesFonctions.lesNoms 	= lesFonctions.donnees.collect(function(e) { return e.nom }) ;
			lesDynasties.lesNoms 	= lesDynasties.donnees.collect(function(e) { return e.nom }) ;

			//autocompleter sur le requêteur
			new Ajax.Autocompleter ("rctags", "upd_rctags", '../ajax/o_autocomplete.php', {method: 'post', paramName: 'premice', parameters: 'objet=tag&champ=nom', minChars: 2, tokens: ','}) ;
			new Ajax.Autocompleter ("rcpersonnes", "upd_rcpersonnes", '../ajax/o_autocomplete.php', {method: 'post', paramName: 'premice', parameters: 'objet=personne&champ=nom', minChars: 2, tokens: ','}) ;
			new Ajax.Autocompleter ("rcfonction", "upd_rcfonction", '../ajax/o_autocomplete.php', {method: 'post', paramName: 'premice', parameters: 'objet=fonction&champ=nom', minChars: 2, tokens: ','}) ;
			new Ajax.Autocompleter ("rcdynastie", "upd_rcdynastie", '../ajax/o_autocomplete.php', {method: 'post', paramName: 'premice', parameters: 'objet=dynastie&champ=nom', minChars: 2, tokens: ','}) ;
			
			//affichage
			lesTags.affichePL() ;
			lesFonctions.affichePL() ;
			lesDynasties.affichePL() ;
			if (CTFA.pr == 0) Requete.premiereRequete() ;	//on relance le chargement qu'à la première exécution
			else {
				localStorage.setItem("tag", JSON.stringify(lesTags.donnees)) ;	//on sauvegarde le tout dans le localStorage
				localStorage.setItem("lientt", JSON.stringify(lesLiensTT.donnees)) ;	//on sauvegarde le tout dans le localStorage
				localStorage.setItem("fonction", JSON.stringify(lesFonctions.donnees)) ;	//on sauvegarde le tout dans le localStorage
				localStorage.setItem("dynastie", JSON.stringify(lesDynasties.donnees)) ;	//on sauvegarde le tout dans le localStorage
			}
			CTFA.pr = 1 ;
			return 1 ;
		}
		else {
			console.log("Appel rejeté.") ;
			return 0 ;
		}
	},
		
	
	
	//images
	"images" : {
		"affiche":0,
		"tableau":[],
		"compteur":0,
		"videTableau":function() {
			this.tableau.splice(0, this.length) ;
			this.compteur = 0 ;
			return 1 ;
		},
		"ajouteTableau":function(urlImage) {
			this.compteur++ ;
			this.tableau[this.compteur] = (urlImage) ;
			return this.compteur ;
		},
		"affiche": function(i) {
			$("photo").innerHTML = '<img src="' + this.tableau[i] + '">' ;
			//style="top:' + pos + 'px"
			$("conteneurPhoto").style.display = "block" ;
			$("conteneurPhoto").scrollTo() ;
			$("conteneurPhoto").observe("click", function (e) {CTFA.images.ferme() ;}) ;
		},
		"ferme": function() {
			$("conteneurPhoto").style.display = "" ;
			$("photo").innerHTML = "" ;
			$("conteneurPhoto").stopObserving() ;
		}
	},
	
	//cookie
	"cookie" : {
		set:function(value, categorie) {
			if (value == "") return 0 ;
			categorie = categorie || "rechercher" ;
			document.cookie = 'rechercher =' + value +' ; expires=Mon, 23 Jul 2040 22:00:00 GMT';
			return 1 ;
		},
		get:function() {
			var x,y, ARRcookies = document.cookie.split(";");
			for (var i=0 ; i < ARRcookies.length ; i++) {
				x = ARRcookies[i].substr(0,ARRcookies[i].indexOf("=")) ;
				y = ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1) ;
				x = x.replace(/^\s+|\s+$/g,"") ;
				if (x == "rechercher") {
					return unescape(y);
				}
			}
			return 0 ;
		}
	},
	
	"enTete": {
		"blocs":["evenements", "personnes", "requetes", "tags", "fonctions", "dynasties", "scripts", "arbre", "arbredynastique"],
		"blocCourant":0,
		//gestion de l'en tête
		"initialise":function() {
			$('blocTete').style.display = "block" ;	//on permet l'affichage du bloc tête principal
			this.changeBloc(0) ;					//par défaut, on affiche le bloc "évènements"
			$('blocTete2').style.display = "block" ;	//on permet l'affichage du bloc tête secondaire
		},
		"changeBloc":function(bloc) {
			$('navigation_' + this.blocs[this.blocCourant]).style.display = "none" ;		//on cache le bloc courant
			this.blocCourant = bloc || 0 ;									//on définit le nouveau bloc courant
			$('navigation_' + this.blocs[bloc]).style.display = "block" ;	//on affiche le bloc courant
			return 1 ;
		},
	},
};

var menu = {
	"menus": ["Ajouter", "Thématiques", "Types", "Etoiles", "Evènements", "Personnes", "Requêtes", "Tags", "Fonctions", "Dynasties", "Scripts"],
	"id":["Ajouter", "Thematique", "Types", "Etoiles"],
	"zone":["Evenements", "Personnes", "Requetes", "Tags", "Fonctions", "Dynasties", "Scripts", "Arbres", "ArbreDynastique"],
	"initialise": function() {
		lesTypesdeTagThematique.affiche() ;
		lesTypesdEvenements.affiche() ;
		donnees.etoiles.affiche() ;
		//observers
		$('menuAjouter').observe("click", function (e) { menu.affiche(0) ; }) ;
		$('menuThematique').observe("click", function (e) { menu.affiche(1) ; }) ;
		$('menuTypes').observe("click", function (e) { menu.affiche(2) ; }) ;
		$('menuEtoiles').observe("click", function (e) { menu.affiche(3) ; }) ;
		$('menuEvenements').observe("click", function (e) { menu.activeMenu(0) ;}) ;
		$('menuPersonnes').observe("click", function (e) { menu.activeMenu(1) ;}) ;
		$('menuRequetes').observe("click", function (e) { menu.activeMenu(2) ; }) ;
		$('menuScripts').observe("click", function (e) { menu.activeMenu(6) ; }) ;
		$('menuTags').observe("click", function (e) { menu.activeMenu(3) ; }) ;
		$('menuFonctions').observe("click", function (e) { menu.activeMenu(4) ; }) ;
		$('menuDynasties').observe("click", function (e) { menu.activeMenu(5) ; }) ;
		$('menuAddEvenement').observe("click", function (e) {lesEvenements.form() ;}) ;
		$('menuAddPersonne').observe("click", function (e) {lesPersonnes.form() ;}) ;
		$('menuAddTag').observe("click", function (e) {lesTags.form() ;}) ;
		$('menuAddDynastie').observe("click", function (e) {lesDynasties.form() ;}) ;
		$('menuAddFonction').observe("click", function (e) {lesFonctions.form() ;}) ;
		$('actualiserAffichage').observe("click", function (e) { lesEvenements.affiche() ; }) ;
		$('mChronos').observe("click", function(e) { lesTypesdEvenements.modeChronos() ; });
		$('antimChronos').observe("click", function(e) { lesTypesdEvenements.modeNormal() ; });
		$('persAlea2').observe("click", function (e) { Requete.aleaPersonne() ; } ) ;
		$('addEv').observe("click", function (e) {lesEvenements.form(-1) ;}) ;
		$('addEvS').observe("click", function (e) {lesEvenements.form(-1, 1) ;}) ;
		$('addEvR').observe("click", function (e) {lesEvenements.form(-1, 2) ;}) ;
		$('selectionpersonnesTous').observe("click", function(e) { lesPersonnes.setAffichage(0) ; }) ;
		$('selectionpersonnesInvalides').observe("click", function(e) { lesPersonnes.setAffichage(1) ; }) ;
		$('selectionpersonnesHorsRegnes').observe("click", function(e) { lesPersonnes.setAffichage(2) ; }) ;
		$('version').innerHTML = CTFA.version ;
		return menu.affiche(0) ;
	},
	"activeMenu":function(nMenu) {
		menu.zone.each(function(zone) { $('les' + zone).style.display = "none" ;}) ;
		menu.id.each(function(id) { $('subMenu' + id).style.display = "" ;}) ;
		$('les' + menu.zone[nMenu]).style.display = "block" ;
		CTFA.enTete.changeBloc(nMenu) ;
		return 1 ;
	},
	"affiche":function(nMenu) {
		//affiche un sous menu - ou le désaffiche
		var eltSubMenu	= $('subMenu' + menu.id[nMenu]) ;		//sous menu
		var eltMenu		= $('menu'+ menu.id[nMenu]) ;			//menu 
		if (eltSubMenu.style.display == "") {
			menu.id.each(function(id) {
				$('subMenu' + id).style.display = "" ;
				$('menu'+ id).className = "menuItem" ;
			}) ;
			eltSubMenu.style.display = "block" ;
			eltMenu.className += " menuItemSelectionne" ;
			return 1 ;
		}
		else {
			eltSubMenu.style.display = "" ;
			eltMenu.className = "menuItem" ;
			return 0 ;
		}
	}
} ;

//messager
var messager= {
	"texte":"",
	"id":"messager",
	"affiche" : function(texte) {
		this.texte = texte || "En cours" ;
		$(this.id).style.display = "block" ;
		$(this.id).innerHTML = this.texte ;
		return 1 ;
	},
	"fade" : function(texte) {
		this.texte = texte || "Voilà qui est fait." ;
		$(this.id).innerHTML = this.texte ;
		$(this.id).style.display = '' ;
	}
} ;

var Requete = {
	"vide" : function() {
		this.datedepart			= 0 ;
		this.datedepartcalculee = 0 ;
		this.datefin			= 0 ;
		this.datefincalculee	= 0 ;
		this.dynastie			= "" ;
		this.fonction			= "";
		this.tags 				= "" ;
		this.personne 			= "" ;
		this.personneNom 		= "" ;
		this.personneNum		= 0 ;
		this.jour				= "" ;
		this.mois				= "" ;
		this.recherche 			= ""  ;
		this.evenementSrc 		= 0 ;
		this.modeIntervalle		= 0 ; 	//0 = pas d'affichage des millénaires, siècles et années. 1 = affichage des millénaires. 2 = affichage des siècles. 3 = des décennies. 4 affichage des années.
	},
	"sauvegardeLocale" : function() {
		localStorage.setItem("requete", JSON.stringify({"datedepart":this.datedepart, "datefin":this.datefin, "dynastie":this.dynastie, "fonction":this.fonction, "personne":this.personne, "tags":this.tags, "recherche":this.recherche})) ; 
		return 1 ;
	},
	"calculeIntervalle": function() {
		if (this.datefin == 0 || this.datedepart == 0 || this.datedepart == this.datefin ) this.modeIntervalle = 0 ;
		else {
			var delta = Math.abs(this.datefin-this.datedepart) ;
			if (delta > 1000) this.modeIntervalle = 3 ;
			else if (delta > 100) this.modeIntervalle = 2 ;
			else if (delta > 9) this.modeIntervalle = 1 ;
			else this.modeIntervalle = 4 ;
		}
		return this.modeIntervalle ;
	},
	"retraitePersonne" : function() {
		if (typeof(this.personne) == "string") {
			//on a le nom de la personne => il est probable qu'il ait été entré manuellement par l'utilisateur et qu'on ne l'ait donc pas en mémoire
			if (this.personneNum = lesPersonnes.getByName(this.personne)) {
				this.personneNom 	= this.personne ;
				return this.personneNum ;
			}
			else {
				return false ;
			}
		}
		if (typeof(this.personne) == "number") {
			//on a le numéro de la personne et normalement c'est un passage par un lien
			var p = lesPersonnes.getById(this.personne)
			if (p) {
				this.personneNom = p.nom ;
				this.personneNum = p.num ;
				return this.personneNum ;
			}
			else return false ;
		}
		return false ;
	},
	"rechercher" : function() {
		this.retraitePersonne() ;
		if (typeof(this.tags) == "string")		listeTags 		= this.tags.split(",").map(function(id) { return lesTags.getByName(id.strip()).num ;}).join(",") ;
		if (typeof(this.fonction) == "string")	listeFonctions 	= this.fonction.split(",").map(function(id) { return lesFonctions.getByName(id.strip()).num ;}).join(",") ;
		if (typeof(this.dynastie) == "string")	listeDynasties 	= this.dynastie.split(",").map(function(id) { return lesDynasties.getByName(id.strip()).num ;}).join(",") ;
		this.requeteURL = "rcdatedepart=" + this.datedepart + "&rcdatefin=" + this.datefin + "&rctags=" + this.tags + "&rcpersonnes=" +this.personne + "&rcrecherche=" + this.recherche + "&rcfonction=" + this.fonction + "&rcdynastie=" + this.dynastie + "&listeTags=" + listeTags + "&listeFonctions=" + listeFonctions + "&listeDynasties=" + listeDynasties ;
		return this.requeteURL ;
	},
	"lienAnnee2" : function(depart, fin, evt) {
		this.datedepart	= depart ;
		this.datefin	= fin || 0 ;
		if (this.datefin == 0) {
			this.tags = ""; 
			this.dynastie = "" ;
			this.fonction = "" ;
			this.recherche = "" ;
			this.personne = "" ;
		}
		this.evenementSrc = evt ; 
		if (this.datefin == this.datedepart) this.datedepart = 0 ;
		return donnees.requete() ;
	},
	"lienTag2" : function(tag, evt) {
		var t = lesTags.getById(tag) ;
		if (t == false) {
			alert("Tag inconnu") ;
			return 0 ;
		}
		this.tags = t.nom ;
		this.dynastie = "" ;
		this.fonction = "" ;
		this.recherche = "" ;
		this.personne = "" ;
		this.evenementSrc = evt ; 
		return donnees.requete() ;
	}, //lienTag2
	"lienPersonne2" : function(personne, evt) {
		var p = lesPersonnes.getById(personne) ;
		this.datedepart = "" ;
		this.datefin = "" ;
		this.tags = "" ;
		this.dynastie = "" ;
		this.fonction = "" ;
		this.recherche = "" ;
		this.personneNom 		= "" ;
		this.personneNum		= 0 ;
		this.personne = personne ;
		this.evenementSrc = evt ; 
		return donnees.requete() ;
	}, //lienPersonne2
	"aleaPersonne" : function() {
		this.datedepart = "" ;
		this.datefin = "" ;
		this.tags = "" ;
		this.dynastie = "" ;
		this.fonction = "" ;
		this.recherche = "" ;
		this.personne = "alea" ;
		this.evenementSrc = null ; 
		this.intervalles = 0 ;
		return donnees.requete() ;
	}, //aleaPersonne
	"lienFonction2" : function(fonction, evt) {
		var f = lesFonctions.getById(fonction) ;
		if (f == false) {
			alert("Fonction inconnue") ;
			return 0 ;
		}
		this.datedepart = "" ;
		this.datefin = "" ;
		this.tags = "" ;
		this.dynastie = "" ;
		this.fonction = f.nom ;
		this.recherche = "" ;
		this.personne = "" ;
		this.evenementSrc = evt ; 
		this.calculeIntervalle() ;
		return this.requete() ;
	},  //lienFonction
	"lienDynastie2" : function(dynastie, evt) {
		var d = lesDynasties.getById(dynastie) ;
		if (d == false) {
			alert("Dynastie inconnue") ;
			return 0 ;
		}
		this.datedepart = "" ;
		this.datefin = "" ;
		this.tags = "" ;
		this.dynastie = d.nom ;
		this.fonction = "" ;
		this.recherche = "" ;
		this.personne = "" ;
		this.evenementSrc = evt ; 
		this.calculeIntervalle() ;
		return this.requete() ;
	},
	"premiereRequete" : function() {
		donnees.historiqueRequete.initialise() ;
		if ("requete" in localStorage) {
			requete = JSON.parse(localStorage.requete) ;
			if ("datedepart" in requete) {
				console.log("... depuis la sauvegarde.") ;
				Object.extend(this, requete) ;		//on copie les éléments issus de la requête dans l'objet données.
			}
			else {
				console.log("... depuis l'année courante.") ;
				this.vide() ;
				this.datedepart	= cetteAnnee ;
			}
		}
		else {
			console.log("... depuis l'année courante.") ;
			this.vide() ;
			this.datedepart	= cetteAnnee ;
		} ;
		this.calculeIntervalle() ;
		return donnees.requete() ;
	},
	"soumissionFormulaire" : function(e) {
		messager.affiche("Définition de la requête issue du formulaire") ;
		Event.stop(e) ;
		this.vide() ;
		this.datedepart	= $('rcdatedepart').value ;
		this.datefin	= $('rcdatefin').value ;
		this.dynastie	= $('rcdynastie').value ;
		this.fonction	= $('rcfonction').value ;
		this.personne	= $('rcpersonnes').value ; 
		this.tags		= $('rctags').value ;
		this.recherche	= $('rcrecherche').value ;
		this.evenementSrc = 0 ;
		this.calculeIntervalle() ;
		return donnees.requete() ;
	},
} ;

var Cadre = {
	//gestionnaire du "cadre", avec les informations essentielles liées à la requête
	"calculeTitre" : function() {
		//calcule le titre et l'affiche
		this.titre = "" ;
		if (Requete.datedepart != "") {
			this.titre		+= Requete.datedepart ;
			if (Requete.datefin != 0 & Requete.datefin != Requete.datedepart) this.titre += " - " + Requete.datefin + ", " ;
			else this.titre += ", " ;
		}
		if (Requete.dynastie != "") this.titre += Requete.dynastie + ", " ;
		if (Requete.fonction != "") this.titre += Requete.fonction + ", " ;
		Requete.retraitePersonne() ;
		if (Requete.personne != "") {
			if (Requete.personne == "alea") {
				this.titre = "Personne aléatoire, " ;
			}
			else if (Requete.personneNom != "") {
				this.titre += Requete.personneNom + ", " ;
			}
			else this.titre += Requete.personne + ", " ;
		}
		if (Requete.tags != "") this.titre += Requete.tags +", " ;
		if (Requete.recherche != "") this.titre += Requete.recherche + ", " ;
		this.titre = this.titre.slice(0, -2) ;
		document.title = this.titre ;
		return this.titre ;
	},
	"affiche": function() {
		//afficheCadre assure l'affichage du "cadre" : décor, titre, requête, liens de navigation...
		this.calculeTitre() ;
		$('titre').innerHTML = this.titre ;
		if (Requete.datedepart != 0) $('rcdatedepart').value = Requete.datedepart ;
		else $('rcdatedepart').value = "" ;
		if (Requete.datefin != 0) $('rcdatefin').value = Requete.datefin ;
		else $('rcdatefin').value = "" ;
		if (Requete.tags != 0) {
			if (typeof(Requete.tags) == "string") $('rctags').value = Requete.tags
			else $('rctags').value = Requete.tags.pluck("nom").join(", ") ;
		}
		else $('rctags').value = "" ;
		if (Requete.personne != 0) {
			if (typeof(Requete.personne) == "string") $('rcpersonnes').value = Requete.personne ;
			else $('rcpersonnes').value = Requete.personneNom ; 
		}
		else $('rcpersonnes').value = "" ;
		if (Requete.recherche != "") $('rcrecherche').value = Requete.recherche ;
		else $('rcrecherche').value = "" ;
		if (Requete.fonction != 0) {
			if (typeof(Requete.fonction) == "string") $('rcfonction').value = Requete.fonction ;
			else $('rcfonction').value = lesFonctions.getById(Requete.fonction).nom  ;
		}
		else $('rcfonction').value = "" ;
		if (Requete.dynastie != 0) {
			if (typeof(Requete.dynastie) == "string") $('rcdynastie').value = Requete.dynastie ;
			else $('rcdynastie').value = lesDynasties.getById(Requete.dynastie).nom  ;
		}
		else $('rcdynastie').value = "" ;
		return 1 ;
	} //afficheCadre
} ;
		
	
var donnees = {
	//données contient les données variantes, qui dépendent de la requête envoyée au serveur
	"data":{},			//les données retournées par le serveur
	//"rechercher" : CTFA.cookie.get() || cetteAnnee,	
	"modeAffichageTag":0,		//0 - tous les évènements, 1 - juste ceux qui concernent le tags
	"modeColorisation":0,		//0 = par type d'évènement - 1 par tags thématiques
	"modeIntervalle":0,			//0 = pas d'affichage des millénaires, siècles et années. 1 = affichage des millénaires. 2 = affichage des siècles. 3 = des décennies. 4 affichage des années.
	"listePropriete" : ["datedepart", "datefin", "dynastie", "fonction", "personne", "tags", "recherche", "evenementSrc"],	//il faudra ajouter les jours & mois, les évènements,
	

	//données
	
	"etoiles": {
		"importance" : 0,		//par défaut, on affiche tous les évènements
		"affiche":function() {
			$('subMenuEtoiles').innerHTML = "" ;
			for (var i = 0 ; i < this.importance ; i++) {
				$('subMenuEtoiles').innerHTML += this.menu(i, "menuItemGrise") ;
			}
			$('subMenuEtoiles').innerHTML += this.menu(this.importance, "") ; 
			i++;
			for(i ; i < 4 ; i++) $('subMenuEtoiles').innerHTML += this.menu(i, "menuItemGrise") ;
			return this.importance ;
		},
		"menu":function(i, classe) {
			var chaine = '<li><a  class="' + classe + '" onClick="donnees.etoiles.setImportance(' + i + ')">' + (i+1) + ' étoile' ;
			if (i > 0) chaine += 's' ;
			chaine += '</a></li>' ;
			return chaine ;
		},
		"setImportance": function(value) {
			//règle l'importance
			value == value || 0 ;
			if (this.importance == value) this.importance = 0 ;
			else this.importance = value ;
			this.affiche() ;
			return lesEvenements.affiche() ;
		}	
	},
	
	"intervalles": {
		init:function() {
			$('intervallesNavigation').innerHTML = "" ;
			this.chaine = "" ;
		},
		add:function(value, id) {
			this.chaine += '<span onClick="donnees.intervalles.scroll(\''+id+'\')">' + value + '</span> | ' ;
		},
		affiche:function() {
			$('intervallesNavigation').innerHTML = this.chaine ;
		},
		scroll:function(id) {
			$(id).scrollTo() ;
		}
	},
	
	"requete":function() {
		//prépare et envoie la requête
		this.nevenement	= null ;
		this.nevenementaffiche = null ;
		this.nevenementcontexte = null ;
		this.evenement	= null ;
		menu.activeMenu(0) ;	//On affiche les évènements
		menu.affiche(0) ;		//On affiche les évènements
		this.rechercher = Requete.rechercher() ;
		var that = this ;
		lesEvenements.corrigeRecherche() ;		//définition de la fonction lesEvenements.corrigeRechercheFonction
		new Ajax.Request(localisation + 'ajax/requestanalyser2.php',	{
			"method" : "post",
			"postBody" : this.rechercher + "&modeVerbeux=0",
			"onSuccess": function(requester) {
				messager.affiche("Réponse reçue. Traitement en cours") ;
				that.data = requester.responseJSON ;
				lesPersonnes.setDonnees(that.data.personnes || []) ;
				if (Requete.personne == "alea") {
					Requete.personne = that.data.personneAlea.nom ;
					$('rcpersonnes').value = that.data.personneAlea.nom ;
				}
				Cadre.affiche() ;		//affichage du titre, des sous catégories...
				that.nevenement = that.data.evenements.length ;
				lesEvenements.obtient(that.data.evenements, that.evenementSrc) ;
				that.nevenementcontexte = that.data.nevcontexte ;
				donnees.contexte.affiche() ;		//choisit s'il y a lieu ou non d'afficher les options de contexte et si c'est le cas, indique le nombre d'évènements concernés
				//cadrage des dates
				var chaine = [] ;
				if (Requete.datedepart != 0) {
					Requete.datedepart = eval(Requete.datedepart) ; 
					if (Requete.datefin == 0) {
						for (var i = Requete.datedepart - 10 ; i < Requete.datedepart ; i++) chaine.push(donnees.lienAnnee(i, i) + " | ") ; 
						for (var i = Requete.datedepart + 1  ; i < 10 + Requete.datedepart  ; i++) chaine.push(" | " + donnees.lienAnnee(i, i)) ; 
						Requete.datedepartcalculee 	= Requete.datedepart ;
						Requete.datefincalculee = Requete.datedepart  ;
					}
					else {
						//on a un intervalle
						Requete.datefin = eval(Requete.datefin) ;
						var ecart = Requete.datefin - Requete.datedepart ;
						//première partie de la chaine
						for (var i = Requete.datedepart - 5 * ecart ; i < Requete.datedepart ; i = i + ecart) chaine.push(donnees.lienAnnee(i + " - " + eval(i + ecart), i, i + ecart) + " | ") ; 
						if (Requete.datefin < cetteAnnee) {
							//seconde partie
							var ep = 0 ;
							for (var i = Requete.datefin ; i < Math.min(Requete.datefin + 5 * ecart, cetteAnnee) ;  i = i + ecart) {
								if (i + ecart > cetteAnnee) ep = cetteAnnee ;
								else ep = i + ecart ;
								chaine.push(" | "  + donnees.lienAnnee(i + "-" + ep, i, i + ecart)) ; 
							}
						}
						Requete.datedepartcalculee 	= Requete.datedepart ;
						Requete.datefincalculee = Requete.datefin  ;
					}
				}
				else {
					if (lesEvenements.donnees.length > 0) {
						Requete.datedepartcalculee 	= lesEvenements.donnees[0].andebut - 25  ;
						Requete.datefincalculee		= lesEvenements.donnees[lesEvenements.donnees.length - 1].anfin  ;
						if (Requete.datefincalculee == 0) Requete.datefincalculee = lesEvenements.donnees[lesEvenements.donnees.length - 1].andebut ;
						Requete.datefincalculee = Number(Requete.datefincalculee) + 25 ;
					}
				}
				$('navigation').innerHTML = chaine.join("") ;
				if (lesEvenements.donnees.length > 0) that.evenementsPeres = lesEvenementsPere() ;
				else that.evenementsPeres = [] ;
				//Recalcul éventuel du nom de personne
				if (!Requete.personneNom) {
					Requete.retraitePersonne() ;
					Cadre.affiche() ;
				}
				//affichage de la requête dans l'historique des requêtes
				donnees.historiqueRequete.ajoute() ;
				Requete.sauvegardeLocale() ;
				console.log("La partie requête (2) est terminé.") ;
				return 1 ;
			}
		}) ;
		//*** OPERATIONS MOINS PRIORITAIRES PAR RAPPORT A L'ENVOI DE LA REQUETE ***
		//on sauvegarde la requête le localStorage
		localStorage.setItem("requete", JSON.stringify({"datedepart":this.datedepart, "datefin":this.datefin, "dynastie":this.dynastie, "fonction":this.fonction, "personne":this.personne, "tags":this.tags, "recherche":this.recherche})) ; 
	
		//affichage du titre 
		console.log("La partie requête est terminée.") ;
		return 1 ;
	},
	
	"contexte": {
		//principes
		//la notion de "contexte" représente les événements qui ne relèvent pas des tags demandés mais de tags fils ou pères.
		//cette notion n'a donc de sens que si un ou plusieurs tags a été choisi.
		//le script php requestanalyser identifie ces événements. Ceux qui sont dans le contexte uniquement ont l'attribut contexte qui vaut 1
		//les autres ont l'attribut contexte à 0
		//le script php requestanalyser renvoie également dans l'objet JSON de réponse un attribut nevcontexte qui indique le nombre d'événements contextes
		//modecontexte indique si on choisit d'afficher les évènements de contexte (1) ou non (0). Par défaut, on ne les montre pas.
		"modeContexte":0,			//1 = montre le contexte, 0 = ne le montre pas - par principe, on ne le montre pas
		
		"initialise":function() {
			//cette fonction est appelée depuis CTFA et sert à initialiser certains éléments
			//d'abord, des évènements sont associés aux Id "masquer le contexte" (context2) et "montrer le contexte" (contexte3)
			$('contexte2').observe("click", function (e) {donnees.contexte.toggleContext(0) ;}) ;
			$('contexte3').observe("click", function (e) {donnees.contexte.toggleContext(1) ;}) ;
			//ensuite le premier est caché par défaut et le second est montré par défaut afin de permettre à l'utilisateur de faire apparaître les événéments de contexte
			$('contexte2').style.display = "none" ; 
			$('contexte3').style.display = "inline" ; 
			return 1 ;
		},
		
		"affiche":function() {
			//affiche ou non les options de contextes - elles ne sont affichées que lorsqu'un tag est activé
			//cette fonction est appelée depuis la fonction de callback de requete()
			if (Requete.tags == "") {
				//aucun tag n'a été sélectionné, la question du contexte ne se pose donc pas
				//on cache donc l'ensemble du span "contexte"
				$('contexte').style.display = "none" ;
				this.modeContext = 1 ;
				return 0 ;
			}
			else {
				//au moins un tag a été sélectionné. on affiche donc l'ensemble du span contexte
				$('contexte').style.display = "inline" ;
				//et on affiche le nombre d'évènements de contexte, tel qu'il nous l'a été spécifié par le script php requestanalyser.
				$('contexteNum').innerHTML = donnees.nevenementcontexte ;
				return 1 ;
			} 
		},
		
		toggleContext:function(mode) {
			//passage d'un mode à un autre
			if (mode == 1) {
				//on montre le contexte
				$('contexte2').style.display = "inline" ;	//=> on affiche donc "masquer le contexte", pour pouvoir retourner à l'état précédent
				$('contexte3').style.display = "none" ; ;
			}
			else {
				//on cache le contexte
				$('contexte2').style.display = "none" ;
				$('contexte3').style.display = "inline" ;	//on affiche donc "montrer le contexte" pour pouvoir retourner à l'état précédent
			}
			this.modeContexte = mode ;
			lesEvenements.rendVisible() ;	//on relance l'affichage du contexte
			return this.modeContexte ; 
		}
	},
		
	setModeColorisation:function(value) {
		donnees.modeColorisation = value ;
		lesEvenements.affiche() ;
		return donnees.modeColorisation  ;
	},

	
	"bev" : {
		//boite du nombre d'évènements
		display:function() {
			$('nEvenement').innerHTML = donnees.nevenement ;
			$('nEvenementAffiches').innerHTML = donnees.nevenementaffiche ;
			return donnees.nevenementaffiche ;
		}
	},
	
	"lienAnnee":function(texteHTML, depart, fin, evt) {
		fin = fin || 0 ;
		evt = evt || 0 ;
		return '<a onClick = "Requete.lienAnnee2(' + depart + ', ' + fin + ', ' + evt + ')" class = "cliquable">' + texteHTML + '</a>' ;
	},
	
	"lienTag":function(texteHTML, tag, evt) {
		evt = evt || 0 ;
		return '<a onClick = "Requete.lienTag2(' + tag + ', ' + evt + ')" class = "cliquable">' + texteHTML + '</a>' ;
	},
	
	"lienPersonne":function(texteHTML, personne, evt) {
		evt = evt || 0 ;
		return '<a onClick = "Requete.lienPersonne2(' + personne + ', ' + evt + ')" class = "cliquable">' + texteHTML + '</a>' ;
	},
	
	"lienFonction":function(texteHTML, fonction, evt) {
		evt = evt || 0 ;
		return '<a onClick = "Requete.lienFonction2(' + fonction + ', ' + evt + ')" class = "cliquable">' + texteHTML + '</a>' ;
	},
	
	"lienDynastie":function(texteHTML, dynastie, evt) {
		evt = evt || 0 ;
		return '<a onClick = "Requete.lienDynastie2(' + dynastie + ', ' + evt + ')" class = "cliquable">' + texteHTML + '</a>' ;
	},
	
	"historiqueRequete":{
		"historique" : [],
		"nh":0,
		"initialise": function() {
			return 1 ;
			
		},
		"ajoute": function() {
			var d = new Date();
			var h = {"requeteTitre":Cadre.titre, "datedepart":Requete.datedepart, "datefin":Requete.datefin, "personne":Requete.personne, "dynastie":Requete.dynastie, "tags":Requete.tags, "fonction":Requete.fonction, "recherche":Requete.recherche, "moment":d.toLocaleString(), "nevenements":Requete.nevenement, "npersonnes":lesPersonnes.donnees.length || 0} ;
			this.nh++ ;
			this.historique[this.nh] = h ;
			var chaine = caseTexte('<a onClick = "donnees.historiqueRequete.renvoie(' + this.nh + ')" class = "cliquable">' + h.requeteTitre + '</a>') + caseTexte(h.nevenements) + caseTexte(h.npersonnes) + caseTexte(h.moment);
			$('listeRequetes').insert({'top':ligne(chaine)}) ;
			return true ;
		},
		"renvoie": function(n) {
			//Recharge la requête sélectionnée
			Object.extend(Requete, this.historique[n]) ;		//on copie les éléments issus de la requête dans l'objet données.
			return donnees.requete() ;
		}
	}	
		
} ; //objet global données

Event.observe(window, "load", CTFA.initialise, false) ;
