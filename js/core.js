/* les fonctions */

var lesFonctions = new coreObjet("fonction") ;

lesFonctions.condition = function() {
	if (this.charge == 0 || lesTags.charge == 0) {
		return 0 ;
	}
	return 1 ;
} ;

lesFonctions.traite = function() {
	this.sort() ;
	return this.corrige() ;
} ;

lesFonctions.corrigeUne = function(f) {
	f.nomTag = lesTags.getById(f.lieuId).nom || '' ;
	return 1 ;
} ;

lesFonctions.afficheUne = function(f) {
	if (f.visible == 0) return "" ;
	var x = lesFonctions.getId(f.num) ;
	return '<tr classe="uneLigne" id="fonction' + f.num + '"><td><div class="uneCaseGras" onclick="lesFonctions.form(' + x + ')" class = "cliquable">' + f.nom + '</div><div class="unePetiteInfo">' + f.description + '</div></td><td><div class="unePetiteInfo"><span class="uneCaseGras">Tag associé : </span> ' + f.nomTag + '</div><div class="unePetiteInfo"><span class="uneCaseGras">Occurences : </span> ' + donnees.lienFonction(f.occurence, f.num) + '<span> ' + lienSupprime("lesFonctions", f.num) + '</div></td></tr>' ; 
} ;
	
lesFonctions.form = function(x) {
	var fc = this.donnees[x] || {} ; 
	fc.nomTag = lesTags.getById(fc.lieuId).nom ;
	var f = new unFormulaire (this, "une fonction", fc) ;
	f.nvFieldset() ;
	f.ligne("Nom", f.input("nom")) ;
	f.ligne("Nom féminin", f.input("nomFeminin")) ;
	f.ligne("Tag", f.inputacsimple("lieuId", fc.nomTag, lesTags.lesNoms)) ;
	f.hidden("lieuId_value", fc.lieuId) ;
	f.ligne("Description", f.textarea("description", fc.description, 5)) ;
	f.boutons() ;
	return f.affiche() ;
} ;

lesFonctions.pretraitementForm = function() {
	//fonction de pré traitement du formulaire
	console.log("Appel de la fonction de prétraitement des fonctions.") ;
	//vérification 1. On s'assure que le nom du tag entré en référence existe bien
	var tag = $('edition').getInputs('text', 'lieuId') ;
	leTag = lesTags.getByName(tag[0].value) ;
	if (!leTag) {
		alert("Ce tag n'existe pas.") ;
		return 0 ;
	}
	tag = $('edition').getInputs('hidden', 'lieuId_value') ;
	tag[0].value = leTag.num ;
	return leTag.num ;
} ;

lesFonctions.affiche = lesFonctions.affichePL ; 


/* dynasties */
var lesDynasties = new coreObjet("dynastie") ;

lesDynasties.corrigeUne = function(d) {
	d.nomPere = "" ;
	if (d.pere > 0) d.nomPere = lesDynasties.getById(d.pere).nom ;
	d.visible = 1 ;
	return d ;
} ;

lesDynasties.afficheUne = function(d) {
	if (d.visible == 0) return "" ;
	var chaine = ['<tr id="dynastie' + d.num + '" class="uneLigne">' + caseImage(d.armoirie)] ;
	chaine.push('<td width="60%"><div><span class="uneCaseGras" onclick="lesDynasties.form(' + d.num + ')" class = "cliquable">' + d.nom + ' ' + lienSupprime("lesDynasties", d.num) + '</div><div class="unePetiteInfo">' + d.description + '</div></td>') ;
	chaine.push('<td width="30%"><div class="unePetiteInfo" ><span class="uneCaseGras">Couleur</span> : <span style="color:#' + d.couleur + '">' + d.couleur + '</span></div>') ;
	if (d.pere > 0) {
		chaine.push('<div class="unePetiteInfo"><span class="uneCaseGras">Origine</span> : <span onClick = "$(\'dynastie' + d.pere + '\').scrollTo() ;" class = "cliquable">' + d.nomPere + '</span></div>') ;
	}
	chaine.push('<div class="unePetiteInfo"><span class="uneCaseGras">Personnes</span> : ' + donnees.lienDynastie(d.occurence, d.num) + '</div><div class="unePetiteInfo"><span class="cliquable" onClick="arbreDynastique.requete([' + d.num + '])">Arbre généalogique</span></div></td></tr>') ;
	return chaine.join("")  ;
} ;

lesDynasties.form = function(num) {
	var d = this.getById(num) || {} ;
	var f = new unFormulaire (this, "une dynastie", d) ;
	f.nvFieldset() ;
	f.ligne("Nom", f.input("nom")) ;
	f.ligne("Armoiries", f.input("armoirie")) ;
	f.ligne("Description", f.textarea("description", d.description, 5)) ;
	f.ligne("Dérive de", f.inputacsimple("pere", d.nomPere, lesDynasties.lesNoms)) ;
	f.hidden("pere_value", d.pere) ;
	f.ligne("Couleur", f.input("couleur")) ;
	f.boutons() ;
	return f.affiche() ;
} ;

lesDynasties.pretraitementForm = function() {
	//fonction de pré traitement du formulaire
	//Vérification que la dynastie père qui a été entrée exist ebien.
	var dynastie = $('edition').getInputs('text', 'pere') ;
	laDynastie = lesDynasties.getByName(dynastie[0].value) ;
	if (!lesDynasties) {
		alert("Cette dynastie n'existe pas.") ;
		return 0 ;
	}
	dynastie = $('edition').getInputs('hidden', 'pere_value') ;
	dynastie[0].value = laDynastie.num ;
	return 1 ;
} ;

lesDynasties.affiche = lesDynasties.affichePL ; 

/* personnes */

var lesPersonnes = new coreObjet("personne") ;
lesPersonnes.modeAffichage = 0 ;	//affiche toutes les personnes
lesPersonnes.lesModesAffichagesId = ["selectionpersonnesTous", "selectionpersonnesInvalides", "selectionpersonnesHorsRegnes"] ;
lesPersonnes.roiRegExp = /([^?#*]*) (Ier|XIV|XVIII|XVII|XVI|XV|IV|VIII|VII|VI|V|IX|XIII|XII|XIX|XI|XX|III|II|X) ([^?#*]*)/ ;
lesPersonnes.ts = new Array(
		{"cle":"h", "valeur":"homme"}, 
		{"cle":"f", "valeur":"femme"},
		{"cle":"s", "valeur":"sonde"},
		{"cle":"o", "valeur":"organisation"},
		{"cle":"p", "valeur":"parti"},
		{"cle":"c", "valeur":"club sportif"}) ;

lesPersonnes.corrige = function() {
	//Première étape - Constitution d'une liste des personnes pour obtenir les liens entre personnes les concernant
	var lpp = [] ;
	this.each(function(p) { 
		p.lpt = [] ;	//on fait de la place tout de suite pour les futurs liens personnes tags
		p.lpf = [] ;	//et pour les liens personnes fonctions
		p.lpp = [] ; 	//et enfin pour les liens personnes - personnes
		return lpp.push(p.num) 
	}) ;
	new Ajax.Request(localisation + 'ajax/requestPersonnes.php', {
		"method":"post",
		"postBody":"personnes=" + lpp,
		"onSuccess" : function(requester) {
			lesPersonnesPP.donnees = requester.responseJSON.personnes ;
			lesLiensPP.setDonnees(requester.responseJSON.lienspp) ;
			lesLiensPT.setDonnees(requester.responseJSON.lienspl) ;
			lesLiensPF.setDonnees(requester.responseJSON.fonctions) ;
			console.log("Chargement des liens relatifs aux personnes... terminé.") ;
		}
	}) ;
	this.each(this.corrigeUne) ;	//on applique la correction générique pour toutes les personnes
	return 1 ;
} ;

lesPersonnes.corrigeUne = function(p) {
	//1. Pour les personnes ayant une dynastie, on obtient le nom de cette dynastie.
	p.dynastieNom = "" ;
	if (p.dynastie > 0) p.dynastieNom = lesDynasties.getById(p.dynastie).nom ;
	//2. Création des expressions régulières pour rechercher les personnes dans les évènements.
	var s = "" ;
	if (p.autreNom != "") s = "|" + p.autreNom.split(",").join("|") ;
	p.regExp1	= new RegExp("(" + lesPersonnes.analyseNom(p.nom) + s + ")( |\.)", 'i') ;
	return 1 ;
} ;

lesPersonnes.analyseNom = function(nom) {
	//analyse le nom pour les expressions régulières
	//d'abord, on enlève la partie entre parenthèses, parce qu'on a des personnes identifiées avec leurs date de naissance et de mort
	if (nom.indexOf("(") > 0) nom = nom.slice(0, nom.indexOf("(")-1) ;	
	//nom de roi
	var r = lesPersonnes.roiRegExp.exec(nom) ;	
	if (r) {
		//on a un nom de roi
		return nom + "|" + r[1] + " " + r[2] + "|" + r[1] + " " + r[3] ;
	}
	else return nom ;
} ;

lesPersonnes.affiche = function() {
	//affiche la collection & la liste des premières lettres
	var chaine = [] ;
	var chaineDesLiens = [] ;
	var premiereLettre = "" ;
	this.each(function(p) {
		if (p.nom[0].toUpperCase() != premiereLettre) {
			premiereLettre = p.nom[0].toUpperCase() ;
			chaineDesLiens.push('<span onClick="$(\'' + lesPersonnes.objetNom + p.num + '\').scrollTo();" class = "cliquable">' + premiereLettre + "</span>") ;
		}
		if ((lesPersonnes.modeAffichage == 0) || (lesPersonnes.modeAffichage == 1 && p.valide == 0) || (lesPersonnes.modeAffichage == 2 && p.regne == 0)) affiche = 1 ;
		else affiche = 0 ;
		chaine.push(lesPersonnes.ecritUne(p, affiche)) ;
	}) ;
	$(this.IDtable).innerHTML = chaine.join("") ;
	$(this.IDn).innerHTML = this.donnees.length ;
	$(this.objetNom + 'ParLettres').innerHTML = chaineDesLiens.join(" - ") ;
	lesLiensPP.affiche() ;
	return 1 ;
} ;

lesPersonnes.ecritUne = function(p, affiche) {
	var chaine = ['<tr id="personne' + p.num + '" class="uneLigne"'] ;
	if (affiche == 0) chaine.push(' style="display:none;">' );
	else chaine.push('>') ;
	//case 1 - image (éventuelle)
	if (p.url != "") {
		var n = CTFA.images.ajouteTableau(p.url) ;
		chaine.push('<td class="caseImagedePersonne"><img class="uneImagedePersonne" src="' + p.url + '" onClick="CTFA.images.affiche(' + n + ')"></td>') ;	
	}
	else chaine.push('<td></td>') ;
	//case 2 - informations générales
	chaine.push('<td class="caseTexte ')
	if (p.valide == 0) chaine.push("novalide") ;
	chaine.push('" width="40%"><div onclick = "lesPersonnes.form(' + p.num + ')" class="uneCaseGras nomPersonne">' + p.nom + '</div><div><div class="unePetiteInfo">' + p.autreNom + '</div><div class="unePetiteInfo">') ;
	if (p.sexe == "h") {
		chaine.push('<img src="../img/Mars_symbol.svg.png" class="tagPetiteImage"> ') ;
	}
	if (p.sexe == "f") {
		chaine.push('<img src="../img/Venus_symbol.svg.png" class="tagPetiteImage"> ') ;
	}
	chaine.push(lesPersonnes.afficheDates(p) + ' (' + donnees.lienPersonne(p.occurence, p.num) + ') ' + lienSupprime("lesPersonnes", p.num) + ' </div>') ;
	chaine.push('<div id="lpt' + p.num + '" class="unePetiteInfo">') ;
	p.lpt.each(function(lpt) {
		chaine.push('<span id="lptid-' + lpt.num + '">' + lpt.drapeau + '</span> ') ;
	}) ;
	chaine.push('</div></td>') ;
	
	//case 3  - Précisions
	chaine.push('<td class="caseTexte">' + p.notice) ;
	if (p.dynastieNom != "") chaine.push('<div class="unePetiteInfo"><span class="uneCaseGras">Dynastie</span> : <span class="cliquable" onClick="arbreDynastique.requete([' + p.dynastie+ '])">' + p.dynastieNom + '</span></div>') ;
	//affichage des fonctions
	p.lpf.each(function(lpf) {
		chaine.push('<div class="unePetiteInfo"><span class="uneCaseGras">' + lpf.fonctionNom + '</span> : ' + lpf.andebut + ' - ' + lpf.anfin + '</div>') ;
	}) ;
	//affichage des liens pp
	chaine.push('<div id="leslienspp' + p.num + '" class="unePetiteInfo">') ;
	p.lpp.each(function(lpp) {
		if (lpp.personne1 == p.num) chaine.push(lpp.chaine1) ;
		else chaine.push(lpp.chaine2) ;
	}) ;
	chaine.push('</div><div class="unePetiteInfo"><span onclick="lesLiensPP.form(-1, ' + p.num + ')" class = "cliquable">Ajouter une relation avec une autre personne</span> - <span onclick="arbre.requete(' + p.num + ')" class = "cliquable">Arbre généalogique</span></div></td></tr>') ;
	return chaine.join("") ;
} ;

//Fonctions d'affichage des personnes

lesPersonnes.afficheNM = function(p) {
	//renvoie le nom de la personne et ses éventuelles dates de naissance et de mort
	//p peut être soit un identifiant de personne, soit l'objet personne lui-même.
	if (typeof p === "number") p = this.getById(p) ;
	if (p.nom && p.nom.indexOf("(") > 0) {
		//il y a une parenthèse dans le nom
		return p.nom ;
	}
	return p.nom + ' (' + lesPersonnes.afficheDates(p) + ') ' ;
} ;

lesPersonnes.title = function(p) {
	//renvoie le nom de la personne et ses éventuelles dates de naissance et de mort
	//p peut être soit un identifiant de personne, soit l'objet personne lui-même.
	if (typeof p === "number") p = this.getById(p) ;
	var chaine = '' ;
	if (p.nom && p.nom.indexOf("(") > 0) {
		//il y a une parenthèse dans le nom
		chaine = p.nom ;
	}
	else chaine = p.nom + ' (' + lesPersonnes.afficheDates(p) + ') ' ;
	if (p.notice != "") chaine += ', ' + p.notice ;
	return chaine ;
} ;

lesPersonnes.stripNM = function(p) {
	//renvoie le nom de la personne en enlevant ses éventuelles dates de naissance et de mort
	//p peut être soit un identifiant de personne, soit l'objet personne lui-même.
	if (typeof p === "number") p = this.getById(p) ;
	if (p.nom.indexOf("(") < 0) return p.nom ;
	else {
		var chaine = p.nom.slice(0, p.nom.indexOf("(") - 1) ;
		chaine = chaine.trim() ;
		return chaine ;
	}
} ;

lesPersonnes.afficheDates = function(p) {
	var chaine = "" ;
	if (p.annaissance != 0) {
		if (p.anmort != 0) {
			chaine += p.annaissance + ' - ' + p.anmort ;
		}
		else {
			chaine += 'né' ;
			if (p.sexe == "f") chaine += 'e' ;
			chaine += ' en ' + p.annaissance ;
		}
	}
	else if (p.anmort != 0) {
		chaine += 'décédé' ;
		if (p.sexe == "f") chaine += 'e' ;
		chaine += ' en ' + p.anmort ;
	}
	return chaine ;
} ;

//Formulaire & modifications

lesPersonnes.form = function(num) {
	var p = this.getById(num) || {lpp:[]} ; 
	p.dynastie = lesDynasties.getById(p.dynastie) ;
	p.dynastieNom = p.dynastie.nom || "" ;
	var f = new unFormulaire (this, "une personne", p) ;
	
	f.nvFieldset("Identité") ; 
	f.ligne("Nom", f.input("nom")) ;
	f.ligne("Dit encore", f.input("autreNom")) ;
	f.ligne("Dynastie", f.inputacsimple("dynastie", p.dynastieNom, lesDynasties.lesNoms)) ;
	f.hidden("dynastie_Id", p.dynastie.num || 0) ;
	f.ligne("Notice", f.textarea("notice", p.notice, 4)) ;
	f.ligne("Image", f.input("url")) ;
	f.ligne("Sexe", f.selectTableauComplexe("sexe", this.ts)) ;
	f.boutons() ;
	//naissance & mort
	f.nvFieldset("Naissance / mort") ;
	if (p.num > 0) {
		f.tableauEvenement("lienep", p.num, "evtp") ;
		p.tabEvt = new Array() ;
		for (var i = 0 ; i < f.evtp.length ; i++) {
			p.tabEvt[i] = {"cle" :f.evtp[i].num, "valeur" : f.evtp[i].description.slice(0,50) + " (" + f.evtp[i].andebut + ")"} ;
		}
		f.ligne("Evenement Naissance", f.selectTableauComplexe("naissance_evenementId", p.tabEvt)) ;
	}
	f.ligne("Naissance", f.fdate("datenaissance")) ;
	f.ligne("Lieu", f.inputacsimple("lieunaissance", "", lesTags.lesNoms)) ;
	f.hidden("lieunaissance_Id", 0) ;
	if (p.num > 0) {
		f.ligne("Evenement Décès", f.selectTableauComplexe("mort_evenementId", p.tabEvt)) ;
	}
	f.ligne("Mort", f.fdate("datemort")) ;
	f.ligne("Lieu", f.inputacsimple("lieumort", "", lesTags.lesNoms)) ;
	f.hidden("lieumort_Id", 0) ;
	f.boutons() ;
	//filiation
	f.nvFieldset("Filiation") ;
	var chaineParents = "" ;
	var lesParents = new collectionLiensPP() ;
	var chaineConjoints = "" ;
	var lesConjoints = new collectionLiensPP() ;
	var chaineEnfants = "" ;
	var lesEnfants = new collectionLiensPP() ;
	var np = 0 ;
	for (var i = 0 ; i < p.lpp.length ; i++) {
		if ((p.lpp[i].personne1 == p.num) && (p.lpp[i].typerelation == 1)) {
			lesParents.push(p.lpp[i]) ;
			np++ ;
		}
		if ((p.lpp[i].personne2 == p.num) && (p.lpp[i].typerelation == 1)) {
			lesEnfants.push(p.lpp[i]) ;
		}
		if ((p.lpp[i].personne1 == p.num) && (p.lpp[i].typerelation == 3)) {
			lesConjoints.push(p.lpp[i]) ;
		}
		if ((p.lpp[i].personne2 == p.num) && (p.lpp[i].typerelation == 3)) {
			lesConjoints.push(p.lpp[i]) ;
		}		
	}
	lesParents.sort() ;
	chaineParents += lesParents.affiche() ;
	if (np < 2) chaineParents += f.inputac("parents", "", "personne", "nom", 50) ;
	lesConjoints.sort() ;
	chaineConjoints += lesConjoints.afficheDiscriminant(p.num) ;
	chaineConjoints += f.inputac("conjoints", "", "personne", "nom", 50) ;
	lesEnfants.sort("personne1nom") ;
	chaineEnfants += lesEnfants.affiche("personne1nom") ;
	chaineEnfants += f.inputac("enfants", "", "personne", "nom", 50) ;
	f.ligne("Parents", chaineParents) ;
	f.ligne("Conjoints", chaineConjoints) ;
	f.ligne("Enfants", chaineEnfants) ;
	//tags
	f.nvFieldset("Tags") ;
	var chaine = "" ;
	if (p.num > 0) {
		p.lpt.each(function(lpt) {
			chaine += '<div id="lienplf' + lpt.num + '">' + lpt.nom + ' ' + lienSupprime("lesLiensPT", lpt.num) + '</div>' ;
		}) ;
	}
	chaine +=  f.inputac("tags", "", "tag", "nom", 50) ;
	f.ligne("Tags", chaine) ;

	f.boutons() ;
	f.affiche() ;
} ;

lesPersonnes.pretraitementForm = function() {
	//fonction de pré traitement du formulaire
	//Recherche de la dynastie
	var dyn  = $('edition').getInputs('text', 'dynastie') ;
	dyn = dyn[0].value ;
	if (dyn != "") {
		var laDyn = lesDynasties.getByName(dyn) ;
		if (!laDyn) {
			alert("Cette dynastie n'existe pas.") ;
		}
		else {
			var dynid = $('edition').getInputs('hidden', 'dynastie_Id') ;
			dynid[0].value = laDyn.num ;
		}
	}
	//lieu de naissance - ici, le fait que le tag ne soit pas retrouvé ne signifie pas nécessairement une erreur puisque le lieu de naissance peut être un tag ou non.
	tag = $('edition').getInputs('text', 'lieunaissance') ;
	leTag = lesTags.getByName(tag[0].value) ;
	if (leTag & leTag > 0) {
		tag = $('edition').getInputs('hidden', 'lieunaissance_Id') ;
		tag[0].value = leTag.num ;
	}
	//lieu de décès  - ici, le fait que le tag ne soit pas retrouvé ne signifie pas nécessairement une erreur puisque le lieu de décès peut être un tag ou non.
	tag = $('edition').getInputs('text', 'lieumort') ;
	leTag = lesTags.getByName(tag[0].value) ;
	if (leTag & leTag > 0) {
		tag = $('edition').getInputs('hidden', 'lieumort_Id') ;
		tag[0].value = leTag.num ;
	}
	return 1 ;
} ;

lesPersonnes.setDates = function (p, e, lep) {
	p = lesPersonnes.getById(p) ;
	e = lesEvenements.getById(e) ;
	//on fait une rotation normal - naissance - mort
	if (p.naissance_evenementId == e.num) {
		var po = "&naissance_evenementId=0&andatenaissance=0&mort_evenementId="+e.num+"&andatemort="+e.andebut ;
		var src = "../img/deces.png" ;
		var title = "Cet évènement est associé au décès de cette personne." ;
	}
	else if (p.mort_evenementId == e.num) {
		var po = "&naissance_evenementId=0andatenaissance=0&mort_evenementId=0&andatemort=0" ;
		var src = "../img/evenement.png" ;
		var title = "Cet évènement fait simplement partie de la vie de cette personne." ;
	}
	else {
		var po = "&naissance_evenementId="+e.num+"&andatenaissance="+e.andebut+"&mort_evenementId=0&andatemort=0" ;
		var src = "../img/naissance.png" ;
		var title = "Cet évènement est associé à la naissance de cette personne." ;
	}
	new Ajax.Request(localisation + 'ajax/o_ajax_ajouter2.php', {
		method : "post",
		postBody : "objet=personne&num=" + p.num + po,
		onComplete: function(requester) {
			oJson = requester.responseText.evalJSON() ;
			lesPersonnes.modifie(oJson) ;
			$('imglep2'+lep).src = src ;
			$('imglep2'+lep).title = title ;
		}
	}) ;
} ;

lesPersonnes.setAffichage = function(mode) {
	lesPersonnes.modeAffichage = mode ;
	[0,1,2].each(
		function(i) { 
			if (i == lesPersonnes.modeAffichage) {
				$(lesPersonnes.lesModesAffichagesId[i]).addClassName("choixSelectionne") ;			
			}
			else {
				$(lesPersonnes.lesModesAffichagesId[i]).removeClassName("choixSelectionne") ;
			}
		}
	) ;
	lesPersonnes.affiche() ;
} ;

lesPersonnes.modifie = function(oJson) {
	//ajoute ou modifie l'un des éléments de la collection
	//cette fonction peut être appelée à la suite de la modification ou de l'ajout d'une personne par le formulaire
	//mais aussi lorsqu'on a ajouté un évènement auquel cette personne est liée et qu'elle est donc ajoutée.
	var ok = 0 ;
	this.corrigeUne(oJson) ;	//correction préliminaire
	if (oJson.hasOwnProperty("lpt")) oJson.lpt.each(lesLiensPT.corrigeUne) ;
	else oJson.lpt = [] ;
	if (oJson.hasOwnProperty("lpf")) oJson.lpf.each(lesLiensPF.corrigeUne) ;
	else oJson.lpf = [] ;
	if (oJson.hasOwnProperty("lpp")) oJson.lpp.each(lesLiensPP.corrigeUne) ;
	else oJson.lpp = [] ;
	oJson.regne = 0 ;
	for (var i = 0 ; i < this.donnees.length ; i++) {
		if (this.donnees[i].num == oJson.num) {
			var templpt = this.donnees[i].lpt ;	//on sauvegarde les liens personne - tags qui existaient déjà et qui seront effacés par la ligne suivante
			var templpp = this.donnees[i].lpp ;	//idem pour les liens personnes personnes
			this.donnees[i] = oJson ;
			//ensuite, on les ajoute à lpt / lpp
			templpt.each(function(t) { lesPersonnes.donnees[i].lpt.push(t) ; }) ;
			templpp.each(function(p) { lesPersonnes.donnees[i].lpp.push(p) ; }) ;
			i == this.length ;
			ok = 1 ;
		}
	}
	if (ok == 0) {
		oJson.lpt = [] ;
		this.ajoute(oJson) ;
		lesLiensPT.obtientPersonne(oJson) ;		//on a une nouvelle personne mais le PHP ne nous a pas renvoyé ses liens personnes tags : on les charge.
	}
	this.sort() ;
	/*if (oJson.hasOwnProperty("lienspp")) {
		for(var i = 0 ; i < oJson.lienspp.length ; i++) {
			lesLiensPP.modifie(oJson.lienspp[i]) ;	
		}
	}*/
	this.affiche() ;
	if ($(this.objetNom + oJson.num)) {
		$(this.objetNom + oJson.num).scrollTo() ;
		new Effect.Highlight(this.objetNom + oJson.num) ;
	}
	return 1 ;
} ;

function tripersonne(param) {
	lesPersonnes.elementTri = param || "nom" ;
	lesPersonnes.sort() ;
	lesPersonnes.affiche() ;
} ;
