/*

les évènements

*/

var lesEvenements = new coreObjet("evenement") ;

lesEvenements.importance 		= Array("Local", "Ordinaire", "Important", "Majeur") ;
lesEvenements.lesTagsT 			= ["19", "22", "16", "15", "17", "21", "18", "20", "79"] ;
lesEvenements.dateER2			= /^(vers|v.)?( ?)(\d+)?(?:er)?( ?)(janvier|février|mars|avril|mai|juin|juillet|août|septembre|octobre|novembre|décembre|hiver|printemps|été|automne|décennie|siècle)?( ?)(\d+)?(?:er)?( ?)(-?)( ?)(\d+)?( ?)(janvier|février|mars|avril|mai|juin|juillet|août|septembre|octobre|novembre|décembre|hiver|printemps|été|automne|décennie|siècle)?( ?)(\d+)?( ?)(,|:){1}( ?)/i ;			//pattern susceptible de trouver deux années entières positives uniquement
lesEvenements.consulsRomains	= /\(\d+ (janvier|février|mars|avril|mai|juin|juillet|août|septembre|octobre|novembre|décembre)( \d?)? du calendrier romain\) /i
lesEvenements.classeTemps 		= ["nvAnnee", "nvDecennie", "nvSiecle", "nvMillenaire"] ;
lesEvenements.notesER			= /(?:[a-zéè»\)])(\d){1,2}/i

lesEvenements.sort = function() {
	//trie les évènements
	return lesEvenements.donnees.sort(triparDate) ;
} ;
	
lesEvenements.corrigeUne = function(e) {
	//apporte des corrections à chaque évènements chargé.
	//1. Correction du champ "précision".
	e.precision2 = "" ;	//on crée "précision2", celui qui va être affiché dans la liste des évènements : on remplace le "*" par des puces HTML <LI>
	if (e.precisions != "") {
		var lesPrecisions2 = e.precisions.split('*') ;	//on découpe précisions en autant de morceaux qu'il y a d'étoiles et on obtient precisions2, sous forme de tableau
		if (lesPrecisions2.length > 1) {				//si on a plusieurs morceaux...
			lesPrecisions2.each(						//pour chacun d'eux
				function (param, indice) {
					if (param != '') {
						lesPrecisions2[indice] = "<li>" + param + "</li>" ;		//on ajoute avant et après chacun d'eux une puce <LI>
					}
				}
			) ;
		}
		e.precision2 = lesPrecisions2.join("") ;		//ensuite, on reconstitue une chaine en joignant les différents éléments du tableau
	}
	e.precision2 = e.precision2.split("\n").join('<br/>') ;
	//2. éventuel élément de recherche
	lesEvenements.corrigeRechercheFonction(e) ;
	//détermination de la thématique dominante - par convention, c'est la première thématique indiquée
	e.tagThematique = e.thematiques.indexOf('1') ;
	//CSS de l'élément sous forme de tableau
	e.elementCSS = [lesTypesdEvenements[e.typeeve].elementCSS, ""] ;
	if (e.tagThematique > -1) {											//1 -> le css de l'évènement compte tenu de sa thématique
		e.elementCSS[1] = lesTypesdeTagThematique[e.tagThematique].elementCSS ;	
	}
	lesEvenements.corrigeLienET(e) ;	//traitement des lien ET
	lesEvenements.corrigeLienEP(e) ;	//traitement des liens EP
	return e ;
} ;

lesEvenements.corrigeRecherche = function() {
	if (Requete.recherche != "") {
		lesEvenements.corrigeRechercheFonction = function(e) {
			var r = new RegExp(Requete.recherche) ;
			e.precision2 = e.precision2.replace(r, '<span class="resultat">' + Requete.recherche + '</span>') ;
			return 1 ;
		}
		return 1 ;
	}
	else {
		lesEvenements.corrigeRechercheFonction = function(e) { return 1 } ;
		return 0 ;
	}
} ;

nonTagThematique = function(lel) {
	//fonction utilisée dans corrigeLienET, qui sert à discriminer les liens EL qui ne seraient pas des liens vers des tags thématiques
	if (lesEvenements.lesTagsT.indexOf(lel.tag) < 0) return 1
	else return 0 ;
} ;

lesEvenements.corrigeLienET = function(e) {
	//corrige les liens évènements tags (anciens LiensEL) de l'évènement e
	//Constitution d'une collection enrichie de lien EL et d'une collection au contraire très pauvre
	e.lienETriche 	= [] ;
	e.lienETsimple 	= [] ;
	e.lienETliste	= ['<span id="lesliensel' + e.num + '">'] ;		//la liste des liens ET, pour l'affichage de l'événément
	e.lienETformulaire = [] ;										//la liste, pour l'affichage dans le formulaire
	e.lienEL.select(nonTagThematique).each(function(lel) {
		//on sélectionne tous les liens ET qui ne sont pas des tags thématiques
		var t = lesTags.getById(lel.tag) ;
		if (t != false) {
			var lr = t.nom ;
			if (t.drapeau != "") {
				lr = "<img src='" + t.drapeau + "' class='tagPetiteImage' title='" + t.nom + "'>" ;
			}
		}
		e.lienETriche.push({"num":lel.num, "tag": lel.tag, "nom":t.nom, "lr":lr}) ;
		e.lienETsimple.push(lel.tag);
		return 1 ;
	}) ;
	e.lienETriche.sort(fonctionTri("nom")) ;	//tri par nom
	e.lienETriche.each(function(letr) {
		e.lienETliste.push('<span class="liensel" id="liensel' + letr.num + '" title="' + letr.nom + '">' + donnees.lienTag(letr.lr, letr.tag, e.num) + '</span>') ;
		e.lienETformulaire.push('<div id="liensel2' + letr.num + '">' +  letr.nom + ' <img class="tagPetiteImage" src="../img/b_drop.png" onclick="lesEvenements.supprimeLien(0, ' + e.num + ',' + letr.num + ')" title="Supprimer ce lien."></div>') ;
		return 1 ;
	}) ;
	e.lienETliste.push('</span>') ;
	e.lienETliste = e.lienETliste.join("") ;
	e.lienETformulaire = e.lienETformulaire.join("") ;
	return 1 ;
} ;

lesEvenements.corrigeLienEP = function(e) {
	//corrige les liens évènements personne de l'évènement e
	//Constitution d'une collection enrichie de lien EP et d'une collection au contraire très pauvre
	e.lienEPriche 	= [] ;
	e.lienEPsimple	= [] ;
	e.lienEPliste	= ['<span id="lesliensep' + e.num + '">'] ;
	e.lienEPformulaire = [] ;
	e.lienEP.each(function(lep) {
		var p = lesPersonnes.getById(lep.personne) ;
		if (p!= false) {
			var evenements = '<span class="liensep" id="liensep' + lep.num + '"title="' + lesPersonnes.title(p) + '">' +  donnees.lienPersonne(p.nom, p.num, e.num) +  '</span>' ;
			var formulaire = ['<div id="liensep2' + lep.num +  '">' + lesPersonnes.afficheNM(p)] ;
			if (p.naissance_evenementId == e.num) formulaire.push(' <img src="../img/naissance.png" title="Cet évènement est associé à la naissance de cette personne.">') ;
			else if (p.mort_evenementId == e.num) formulaire.push(' <img src="../img/deces.png" title="Cet évènement est associé au décès de cette personne.">') ;
			formulaire.push(' <img class="tagPetiteImage" src="../img/b_drop.png" onclick="lesEvenements.supprimeLien(1, ' + e.num + ',' + lep.num + ')" title="Supprimer ce lien."></div>') ;
			e.lienEPriche.push({"num":lep.num, "personne":lep.personne, "nom":p.nom, "evenements":evenements, "formulaire":formulaire.join("")}) ;
			e.lienEPsimple.push(lep.personne) ;
		}
	}) ;
	e.lienEPriche.sort(fonctionTri("nom")) ;
	e.lienEPriche.each(function(lep) {
		e.lienEPformulaire.push(lep.formulaire) ; 
		e.lienEPliste.push(lep.evenements) ;
	}) ;
	e.lienEPliste.push('</span>') ;
	e.lienEPliste = e.lienEPliste.join("") ;
	e.lienEPformulaire = e.lienEPformulaire.join("") ;
	return 1 ;
} ;
	
lesEvenements.obtient = function(evts, evt) {
	//charge les évènements (evts) dans le présent objet et scrolle à l'évènement evt, s'il existe.
	this.donnees 	= evts || new Array() ;
	this.charge 	= 1 ;		//indicateur de chargement
	this.corrige() ;			//fonction de correction des données
	this.affiche() ;			//affiche les évènements
	if ($("evenement" + evt)) $("evenement" + evt).scrollTo() ;
	return this.donnees.length ;
} ;

lesEvenements.affiche = function() {
	//affiche les évènements
	messager.fade("Affichage des évènements...") ;
	var n = this.ecrit() ;		//1. écrit les évènements
	n += this.rendVisible();	//2. Rend visible ceux qui doivent l'être
	messager.fade() ;			//3. Efface le messager
	return n ;
} ;
	
lesEvenements.ecrit = function() {
	//fonction qui prend un à un les évènements et les écrit. Par défaut, ils sont tous cachés.
	if (this.donnees.length < 1) {
		this.writed = 1 ;
		$(this.IDtable).innerHTML = "" ;
		return -1 ;
	}
	var chaine = [] ;		//chaine va contenir les évènements
	//pdu = première date utile
	//par défaut, on prend la date la plus récente entre la commande de base et la première date. Par exemple, on a pu demander les évènements sur 1900-1910 et le premier évènement retourné aura une date antérieure, parce que c'est un règne ou une période. En ce cas, on prendra 1900 (et non la date de cet évènement). Mais si cet évènement date en fait de 1902, on prendra 1902.
	//on divise ensuite cette date par 10 puissance l'intervalle de date, calculé dans donnees.setDonnees : 1000, 100, 10...
	var puissance = Math.pow(10, Requete.modeIntervalle) ;
	var pdu = Math.floor(Math.max(Requete.datedepart, this.donnees[0].andebut) / puissance) - 1 ;
	donnees.intervalles.init() ;	//réinitialise le <span id="intervallesNavigation">
	
	switch(Requete.modeIntervalle) {
		case 3:
			var laFonctionduTemps = function(pdu) {
				if (pdu > 0) return chaine.push(eval(pdu + 1) + 'e millénaire') ;
				else if (pdu < 0) return chaine.push(Math.abs(pdu) + 'e millénaire avant JC') ;	
				else return chaine.push('1er millénaire') ;
			}
			break ;
		case 2:
			var laFonctionduTemps = function(pdu) {
				if (pdu > 0) return chaine.push(eval(pdu + 1) + 'e siècle après JC') ;
				else if (pdu < 0) return chaine.push(Math.abs(pdu) + 'e siècle avant JC') ;	
				else return chaine.push('1er siècle après JC') ;
			}
			break ;
		case 1:
			var laFonctionduTemps = function(pdu) {
				return chaine.push('Années ' + pdu + '0</td></tr>') ;
			}
			break ;
		case 0:
			var laFonctionduTemps = function(pdu) { 
				return chaine.push('<div class="uneLigne nvAnnee" id="repere' + pdu + '">' + pdu + '</div>') ;
			}
			break ;
		default:
			var laFonctionduTemps = function(pdu) { return true ;} ;
			break ;
	}

	this.donnees.each(function(e) {
		if ((Requete.datefin != 0) && (e.andebut > -10000) && (pdu < Math.floor(e.andebut / puissance))) {
			pdu = Math.floor(e.andebut / puissance) ;
			chaine.push('<tr class="uneLigne ' + lesEvenements.classeTemps[Requete.modeIntervalle] + '" id="repere' + pdu + '"><td colspan="2" class="' + lesEvenements.classeTemps[Requete.modeIntervalle] + '">') ;
			laFonctionduTemps(pdu) ;
			donnees.intervalles.add(pdu * puissance, "repere" + pdu) ;
			chaine.push('</td></tr>') ;
		}
		return chaine.push(lesEvenements.ecritUnEvenement(e, 0)) ;
	}) ;
	donnees.intervalles.affiche() ;	//on affiche l'intervalle
	this.writed = 1 ;
	$(this.IDtable).innerHTML = chaine.join("") ;	//on entre la chaine dans le bon DIV
	$('lesEvenements').style.display = "block" ;	//on l'affiche
	$('nEvenement').innerHTML = this.donnees.length ;
	return this.donnees.length ;
} ;

lesEvenements.ecritUnEvenement = function(e, actif) {
	//écrit l'évènement lesEvenements[num]
	//on prend les évènements un par un
	//structure d'un7e ligne événement :
	//la ligne a pour ID : evenement + le numéro de l'évènement dans la base
	//elle a pour classe CSS : ligneEvenement
	//elle est composé de 2 cases (TD) :
	//- la date (class CSS : date)
	//- l'évènement (class css : evenement + une classe correspondant au type d'évènement)
	//l'évènement lui même se décompose en :
	//- un DIV de description (class : etoileN où n correspond à l'importance de l'évènement & une classe correspondant au type d'évènement 
	//- un DIV de précision (class : prescisions)
	//- un DIV de liens 
	//- un DIV d'URL (éventuellement)
	if (e == {}) return "" ;
	if (!e.hasOwnProperty("num")) return "" ;
	actif = actif || 0 ;
	var style = ['style="display:none"', ''] ;
	var sc = ['', 'contexte'] ;
	var chaine = ['<tr class="uneLigne" id="evenement' + e.num + '" ' + style[actif] + '><td class="caseDate" id="evenementDate'+ e.num + '">' + affichedeuxdates(e.jourdebut, e.moisdebut, e.andebut, e.versdebut, e.jourfin, e.moisfin, e.anfin, e.versfin, e.num) + '</td>'] ;
	chaine.push('<td class="caseEvenement ' + sc[e.contexte] + '"><div class="' + e.elementCSS[donnees.modeColorisation] + '" ><div class="etoile' + e.etoiles + ' ' + e.elementCSS[donnees.modeColorisation] + 'desc" onclick="lesEvenements.form(' + e.num + ')">' + e.description + '</div><div class="precisions">' + e.precision2 + '</div><div class="liensTT" id="lesLientt' + e.num + '"></div><div class="liens" id="liensevenement' + e.num + '">' + e.lienETliste + e.lienEPliste) ;
	if (e.latitude != 0 && e.longitude != 0) {
		chaine.push('<a href="https://maps.google.fr/maps?ll=' + e.latitude + ',' + e.longitude + '&t=h&spn=3,3&q=' + e.latitude + ',' + e.longitude + '&hl=fr"><img title="' + e.lieu + '" src="../img/monde.png"></a>') ; //l'évènement est géolocalisé
	}
	//un père ?
	var pere = eval(e.pere) ;
	if (pere > 0 && (ePere = this.getById(pere))) {
		chaine.push('<span class="evepere">' + lienStructure(ePere.description, "evenement", ePere.num, e.num) + '</span>') ;	//affichage de l'évènement père, avec un lien
	}
	chaine.push(lienSupprime("lesEvenements", e.num)) ;
	//une URL ?
	if (e.url != "") {
		fin = e.url.substr(-4, 4).toLowerCase() ;
		if ((fin == ".png") || (fin==".jpg") || (fin == ".gif") || (fin="jpeg")) {
			var n = CTFA.images.ajouteTableau(e.url) ;
			chaine.push('<div onClick="CTFA.images.affiche(' + n + ')"><img class="image" src="' + e.url + '"></div>') ;
		}
		else {
			chaine.push('<div class="lien"><a href="' + e.url + '">Référence</a></div>') ;
		}
	}
	chaine.push('</div></div></td></tr>') ;	//fin de la ligne
	return chaine.join("") ;
} ;

lesEvenements.rendVisible = function() {
	var nEvAffiche = 0 ; 
	//prend les évènements un par un et détermine s'ils doivent être affichés ou non
	this.donnees.each(function(e) {
		if ($('evenement' + e.num)) {
			if ((e.etoiles >= donnees.etoiles.importance) && 
				(lesTypesdeTagThematique.actif == -1 || (lesTypesdeTagThematique.isActif(e) > 0)) &&
				(donnees.contexte.modeContexte == 1 || (donnees.contexte.modeContexte == 0 && e.contexte == 0)) &&
				(lesTypesdEvenements.isActif(e) > 0)) {
				$('evenement' + e.num).style.display = "" ;
				nEvAffiche++ ;	
			}
			else {
				$('evenement' + e.num).style.display = "none" ;
			}
		}
	}) ;
	donnees.nevenementaffiche = nEvAffiche ;
	return donnees.bev.display() ;
} ;

lesEvenements.modifie = function(oJson) {
	var ok = 0 ;
	oJson = this.corrigeUne(oJson) ;
	oJson.contexte = 0 ;	//on met le contexte à 0 de sorte que cet évènement sera désormais affiché.
	//est-ce qu'il y a des personnes nouvelles ??
	oJson.lienEP.each(function(lep) {
		if (lesPersonnes.getById(lep.personne) == false) {
			new Ajax.Request(localisation + 'ajax/o_ajax_content.php',
				 {"method" : 'post',
				"postBody" : 'objet=personne&num=' + lep.personne,
				"onSuccess" : function(result, eventnum) {
					var nouvellePersonne = result.responseJSON.personne[0] ;
					lesPersonnes.modifie(nouvellePersonne) ;
					tripersonne(lesPersonnes.modeTri) ;
					lesPersonnes.affiche() ;
					lesEvenements.corrigeLienEP(oJson) ;
					lesEvenements.ecrit() ;
					lesEvenements.rendVisible() ;
					$('evenement' + oJson.num).scrollTo() ;
				}
			})
		}
	}) ;
	if (e = this.getId(oJson.num)) {
		this.donnees[e] = oJson ;
	}
	else {
		this.donnees.push(oJson) ;
		donnees.nevenement += 1 ;
	}
	if (donnees.triEvenement == 1) this.donnees.sort(triparDateInverse) ;
	else this.donnees.sort(triparDate) ;
	lesEvenements.affiche() ;
	$('evenement' + oJson.num).scrollTo() ;
	//Est-ce que cet évènement est un nouvel élément à ajouter à la liste des évènements pères potentiels ?
	if (["4","5","8","9","10"].indexOf(oJson.typeeve) > -1) {
		//potentiellement
		if (donnees.evenementsPeres.pluck("cle").indexOf(oJson.num) < 0) {
			donnees.evenementsPeres.push({"cle":oJson.num, "valeur":oJson.description.slice(0,50) + " (" + oJson.andebut + " - " + oJson.anfin + ")"}) ;
		}
	} ;
} ;

lesEvenements.form = function(x, option) {
	option = option || 0 ;
	var e = lesEvenements.getById(x) || {
		"etoiles":1,
		"thematiques":"000000000",
		"ecartdate":0,
		"lienEL":[],
		"lienEP":[],
		"lienETsimple":[],
		"lienEPsimple":[],
		"lienETformulaire":'',
		"lienEPformulaire":'',
		"andebut":"", "moisdebut":0, "jourdebut":"", "versdebut":0, "anfin":"", "moisfin":0, "jourfin":"", "versfin":0, "ecartdate":"0"
	} ; 
	if (e.andebut == "") {
		if (Requete.datedepart != 0 & Requete.datefin == 0) {
			e.andebut = Requete.datedepart ;
		}
	}
	var f = new unFormulaire (lesEvenements, "un évènement", e) ;
	f.nvFieldset("Date") ;
	f.ligne("Date", f.fdate2("", e)) ;
	f.nvFieldset("Evènement") ;
	f.ligne("Description", f.input("description", e.description, 70)) ;
	f.ligne("Précisions", f.textarea("precisions")) ;
	f.ligne("Analyse", '<input id="boutonAnalyse" type="button" ' + f.tabInd() + ' value="Analyse" width="400px">') ;

	if (option == 2) {
		f.hidden("typeeve", 3) ;
	}	
	else {
		lte = new Array("-", "Oeuvre d'art", "Bataille", "Règne", "Guerre", "Période historique", "Traité", "Loi", "Evenement complexe", "Tendance", "Notice", "Election", "Match", "Naissance", "Carte", "Dynastie", "Occupation") ;
	f.ligne("Type d'évènement", f.radio("typeeve", lte, e.typeeve, 6)) ;
	}
	f.ligne("Importance", f.radio("etoiles", lesEvenements.importance, e.etoiles, 6)) ;
	if (option == 0) {
		f.ligne("Image", f.input("url", e.url)) ;
	}
	else {
		f.ligneCachee("Image", f.input("url", e.url), "urlimage") ;
	}
	//évènement père
	if (option < 2) {	
		f.ligne("Evenement père", f.selectTableauComplexe("pere", donnees.evenementsPeres, e.pere))
	}
	else {
		f.ligneCachee("Evenement père", f.selectTableauComplexe("pere", donnees.evenementsPeres, e.pere), "epere") ;
	}
	var fonctionDefaut = "" ;
	if (e.fonction != "undefined" && e.fonction > 0) {
		fonctionDefaut = lesFonctions.getById(e.fonction).nom ;
	}
	if (option != 1) {
		f.ligne("Fonction", f.inputac("fonction", fonctionDefaut, "fonction", "nom")) ;
	}
	else {
		f.ligneCachee("Fonction", f.inputac("fonction", fonctionDefaut, "fonction", "nom"), "fonction") ;
	}
	if (option < 2)  {
		if (option !=1) f.ligne("Numéro d'ordre", f.input("numeroordre")) ;
		f.ligne("Coordonnées", f.geographique()) ;
		f.ligne("Lieu", f.input("lieu"), f.lieu) ;
	}	
	f.boutons() ;
	//tags
	f.nvFieldset("Tags et personnes") ;
	//thématiques
	var tt = new Array() ;
	for (var i = 0 ; i < lesTypesdeTagThematique.length ; i++) {
		var objet = {"nom" : lesTypesdeTagThematique[i].nom, "selection": e.thematiques[i], "id":i} ;
		tt.push(objet) ;
	}
	f.ligne("Tags thématiques", f.checkbox("thematiques", tt)) ;
	//tags ordinaires
	chaine = e.lienETformulaire + f.inputac("tags", "", "tag", "nom") + '<span style="display:none">' + f.input("tagssuggeres") + '</span>' ;
	//chaine = e.lienETformulaire + f.inputacsimple("tags", "", lesTags.lesNoms, 100) + '<span style="display:none">' + f.input("tagssuggeres") + '</span>' ;
	f.ligne("Tags", chaine) ;
	//tags suggérés
	e.tagsSuggeres = [] ;
	f.ligne('Tags suggérés', '<div id="tagss"></div>') ;
	
	//personnes
	if (Requete.personneNum > 0 && !(e.num > 0)) chaine = e.lienEPformulaire + f.inputac("personnes", lesPersonnes.getById(Requete.personneNum).nom, "personne", "nom") ;
	else chaine = e.lienEPformulaire + f.inputac("personnes", "", "personne", "nom") + '<span style="display:none">' + f.input("personnessuggerees") + '</span>' ;
	f.ligne("Personnes", chaine) ;
	e.personnesSuggerees = [] ; 
	f.ligne('Personnes suggérées', '<div id="personness"></div>') ;
	f.boutons() ;
	f.affiche() ;
	$('boutonAnalyse').observe("click", function() { lesEvenements.analyse(e) ; }) ;
} ;

lesEvenements.analyse = function(e) {
	//Analyse du contenu de l'évènement dans le formulaire
	var caa = $('description').value + $('precisions').value ;
	caa = caa.strip() ;
	//1. Vérification de l'existence d'une date
	//plusieurs possibilités
	//
	var dates2 = lesEvenements.dateER2.exec(caa) ;
	if (dates2) {
		if (dates2[1]) $('versdebut').checked = true ;			//vers
		if (dates2[3]) {
			//hypothèse 1 - une année seule
			if (!dates2[5] && !dates2[7]) {
				$('andebut').value 		= dates2[3] ;		//an début
			}
			else {
				$('jourdebut').value = dates2[3] ;			//vers
			}
		}
		if (dates2[5]) 	{
			m = dates2[5].toLowerCase() ;	//mois début
			for (var i = 1 ; i < lesmois.length ; i++) {
				if (m == lesmois[i]) $('moisdebut').value = i ;
			}
		}
		if (dates2[7]) 	$('andebut').value 		= dates2[7] ;	//an début
		if (dates2[11]) {
			if ($('datefin') && $('datefin').style.display == "none") {	//pour faire apparaître l'éventuelle 2e ligne de date
				$('datefin').style.display = "" ;
			}
			//hypothèse 1 - une année seule
			if (!dates2[13] && !dates2[15]) {
				$('anfin').value = dates2[11] ;	//an début
			}
			else {
				$('jourfin').value = dates2[11]			//vers
			}
		}
		if (dates2[13])	 {
			if ($('datefin') && $('datefin').style.display == "none") {	//pour faire apparaître l'éventuelle 2e ligne de date
				$('datefin').style.display = "" ;
			}
			m = dates2[13].toLowerCase() ;	
			for (var i = 1 ; i < lesmois.length ; i++) {
				if (m == lesmois[i]) $('moisfin').value = i ;
			}
			if (!dates2[15]) $('anfin') .value = $('andebut').value ;
		}
		if (!dates2[5]) {
			//cas où seul le mois de fin est défini. Exemple : 24-26 avril.
			if ($('datefin') && $('datefin').style.display == "none") {	//pour faire apparaître l'éventuelle 2e ligne de date
				$('datefin').style.display = "" ;
			}
			$('moisdebut').value = $('moisfin').value ;
		}
		if (dates2[15]) {
			if ($('datefin') && $('datefin').style.display == "none") {	//pour faire apparaître l'éventuelle 2e ligne de date
				$('datefin').style.display = "" ;
			}
			$('anfin') .value		= dates2[15] ;	//an de fin
		}
		caa = caa.replace(lesEvenements.dateER2, "") ;
	}
	//remplit description si cela n'a pas été déjà fait.
	if ($('description').value == '') {
		var pos = $('precisions').value.indexOf(".") ;
		$('description').value 	= $('precisions').value.slice(0, pos+1) ;
		$('precisions').value	= $('precisions').value.slice(pos +1) ;
	}
	$('description').value = $('description').value.trim() ;
	$('description').value = $('description').value.replace(lesEvenements.dateER2, "").replace(lesEvenements.consulsRomains, "").replace("(en)", "") ; 
	$('description').value = $('description').value.trim() ;
	var t = lesEvenements.notesER.exec($('description').value) ;
	if (t) $('description').value = $('description').value.replace(t[1], '') ;
	$('precisions').value = $('precisions').value.trim().replace("(en)", "") ; 
	//$('precisions').value = $('precisions').value.replace(lesEvenements.notesER, "") ;
	var t = lesEvenements.notesER.exec($('precisions').value) ;
	if (t) $('precisions').value = $('precisions').value.replace(t[1], '') ;
	//Analyse des tags
	lesTags.each(function(t) {
		if (e && e.tagsSuggeres.indexOf(t.num) < 0) { 
			//on teste que ce tag ne fait pas déjà parti de la liste des tags suggérés
			if (((t.regExp1 && t.regExp1.test(caa)) || (t.regExp2 && t.regExp2.test(caa)) || (t.regExp3 && t.regExp3.test(caa))) && (e.lienETsimple.indexOf(t.num)  < 0)) {
				if ($('tagssuggeres').value == '')	$('tagssuggeres').value = t.nom ;
				else $('tagssuggeres').value += ', ' + t.nom ;
				$('tagss').innerHTML += '<span id="ts' + t.num + '"><span class="sugItem">' + t.nom + '</span> <img class="tagPetiteImage" src="../img/b_drop.png" onclick="lesEvenements.supprimeSug(' + t.num + ', 0)" title="Supprimer cette suggestion"></span> ' ;
				e.tagsSuggeres.push(t.num) ;
			}
		}
	});
	//Analyse des personnes
	lesPersonnes.each(function(p) {
		if (e.personnesSuggerees.indexOf(p.num) < 0 && p.regExp1 && p.regExp1.test(caa) && e.lienEPsimple.indexOf(p.num)  < 0) {
			if ($('personnessuggerees').value == '') $('personnessuggerees').value = p.nom ;
			else $('personnessuggerees').value += ', ' + p.nom ;
			$('personness').innerHTML += '<span id="ps' + p.num + '"><span class="sugItem">' + p.nom + '</span> <img class="tagPetiteImage" src="../img/b_drop.png" onclick="lesEvenements.supprimeSug(' + p.num + ', 1)" title="Supprimer cette suggestion"></span> ' ;
			e.personnesSuggerees.push(p.num) ;
		}
	}) ;
	//analyseur de mots clés
	if (/traité |pacte |paix entre /i.test(caa)) {
		$('typeeve6').checked = true ;		//radio traité
		$('thematiques7').checked = true ;	//case thématique
	}
	if (/(élection|election|législatives|élu )/i.test(caa) || /référendum/i.test(caa)) {
		$('typeeve11').checked = true ;			//radio : élection
		$('thematiques3').checked = true ;		//case thématique politique
	}
	if (/guerre /i.test(caa)) {
		$('typeeve4').checked = true ;			//radio : guerre
		$('thematiques2').checked = true ;		//case thématique militaire
	}
	if (/bataille /i.test(caa)) {
		$('typeeve2').checked = true ;			//radio : bataille
		$('thematiques2').checked = true ;		//case thématique militaire
	}
	if (/naissance de /i.test(caa)) {
		$('typeeve13').checked = true ;			//radio : naissance
	}
	console.log("Fin de l'analyse textuelle.") ;
	//traduction de la localisation GPS
	var coords = [$('longitude').value, $('latitude').value].map(function(c) { return c.replace('°', '') ;}) ;
	$('longitude').value 	= coords[0] ;
	$('latitude').value 	= coords[1] ;
	return 1 ; 
}

lesEvenements.supprimeSug = function(i, n) {
	console.log(i); 
	if (n==0) {
		if ($('ts' + i)) $('ts' + i).remove() ;
		var chaine = $('tagssuggeres').value.replace(lesTags.getById(i).nom + ', ', '') ;
		chaine = chaine.replace(lesTags.getById(i).nom, '') ;
		$('tagssuggeres').value = chaine ;
	}
	if (n==1) {
		if ($('ps' + i)) $('ps' + i).remove() ;
		var chaine = $('personnessuggerees').value.replace(lesPersonnes.getById(i).nom + ', ', '') ;
		chaine = chaine.replace(lesPersonnes.getById(i).nom, '') ;
		$('personnessuggerees').value = chaine ;
	}
}

lesEvenements.supprimeLien = function(epet, numE, numLien) {
	//Supprimer un lien. epet prend les valeurs 0 (et/el) ou 1 (ep)
	epet = epet || 0 ;
	if (epet == 0) epet = "el" ;
	else epet = "ep" ;
	messager.affiche("Suppression en cours...") ;
	new Ajax.Request(localisation + 'ajax/o_ajax_delete.php', {
		method:"post",
		"postBody":"objet=lien" + epet + "&num=" + numLien,
		"onSuccess" : function(requester) {
			if (requester.responseJSON.result == 1) {
				$('liens' + epet + '2' + numLien).fade() ;	//suppression sur le formulaire
				$('liens' + epet + numLien).fade() ;		//suppression sur le listing des évènements
				e = lesEvenements.getById(numE) ;			//on sélectionne l'évènement qui correspond à numE
				var ok = 0 ;
				//mettre epet en maj
				epet = epet.toUpperCase() ;
				var t = e["lien" + epet] ;
				for (i = 0 ; i < t.length ; i++) {
					if (t[i].num == numLien) {	//on trouve dans e.lienEL le lienEL à supprimer
						t.splice(i, 1) ;		//ce qu'on fait
						if (epet == "ET") lesEvenements.corrigeLienET(e) ;	//on relance corrigeLienET pour étendre cette suppression à tous les tableaux de lienEL (simple et riche).
						else lesEvenements.corrigeLienEP(e) ;	//on relance corrigeLienET pour étendre cette suppression à tous les tableaux de lienEL (simple et riche).
						i = t.length + 1  ;
						ok = 1 ;
						messager.fade("Suppression effective.") ;
						return 1 ;
					}
				}
				messager.fade("Erreur dans le processus interne. L'objet est cependant bien supprimé dans la base de données. ") ;
				return 0 ;
			}
			messager.fade("Echec dans la suppression.") ;
			return 0 ;
		}
	}) ;
	return 1 ;
} ;

function triReverse() {
	if (donnees.triEvenement == 1) {
		donnees.triEvenement = 0 ;
	}
	else {
		donnees.triEvenement = 1 ;
	}
	lesEvenements.sort() ;
	lesEvenements.affiche() ;
} ;

 
 //liste d'évènements 
   
 lesEvenementsPere = function() {
	var url = localisation + 'ajax/requestEventsPere.php' ;
	var ter = new XMLHttpRequest() ;
	ter.open('POST', url, false) ;
	ter.setRequestHeader("Content-type", "application/x-www-form-urlencoded") ;
	req = "andebut=" + Requete.datedepartcalculee + "&anfin=" + Requete.datefincalculee ;
	ter.send(req) ;
	rep = ter.responseText.evalJSON().evenements ;
	perespotentiels = new Array() ;
	for (var i = 0 ; i < rep.length ; i++) {
		if (rep[i].anfin != 0 && rep[i].anfin != rep[i].andebut) {
			perespotentiels.push({"cle" :rep[i].num, "valeur" : rep[i].description.slice(0,50) + " (" + rep[i].andebut + " - " + rep[i].anfin + ")"}) ;
		}
		else {
			perespotentiels.push({"cle" :rep[i].num, "valeur" : rep[i].description.slice(0,50) + " (" + rep[i].andebut + ")"}) ;
		}
	}
	return perespotentiels ;
}
