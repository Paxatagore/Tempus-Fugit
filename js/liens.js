/* spécifications propres aux liens TT */
var lesLiensTT = new coreObjet("lientt") ;

lesLiensTT.parametresRequest = "" ;

lesLiensTT.traite = function() {
	//this.filiation() ;
	this.affiche() ;
	return 1 ;
} ;

lesLiensTT.condition = function() {
	if (this.charge == 0) return 0 ;
	if (lesTags.charge == 0) return 0 ;
	return 1 ;
} ;

lesLiensTT.corrigeUne = function(l) {
	if (this.condition == 0) return l ;
	lesTags.donnees[lesTags.getId(l.tag1)].fils.push(l.tag2) ;
	lesTags.donnees[lesTags.getId(l.tag2)].pere.push(l.tag1) ;
	return l ;
} ;

lesLiensTT.afficheUne = function(l) {
	var tpere = lesTags.getById(l.tag1) ;
	var tfils = lesTags.getById(l.tag2) ;
	if (tpere.num + tfils.num > 0) {	//on s'assure que les tags existent bien et n'ont pas été mis à -1 (effacés)
		if (tpere.visible == 1) {
			if ($("tagfils" + tpere.num)) {
				if ($("tagfils" + tpere.num).innerHTML == "&nbsp;") $("tagfils" + tpere.num).innerHTML = "" ;
				var chaine = '<div id="lttp-' + l.num + '" class="tpere cliquable" onclick="$(\'tag' + tfils.num + '\').scrollTo() ;">' + tfils.nom ;
				$("tagfils" + tpere.num).innerHTML += chaine + '</div>' ;	
				
			}
			else {
				console.log("J'ai cherché sans succès à afficher le lien TT, côté père :") ;
				console.log(l) ;
			}
			
		}
		if (tfils.visible == 1) {
			if ($("tagperes" + tfils.num)) {
				if ($("tagperes" + tfils.num).innerHTML == "&nbsp;") $("tagperes" + tfils.num).innerHTML = "" ;
				var chaine = "" ;
				if (l.de != 0) {
					chaine += ' (' + l.de ;
					if (l.a != 0) {
						chaine += ' - ' + l.a + ')' ;
					}
					else {
						chaine += ')' ;
					}
				}
				$("tagperes" + tfils.num).innerHTML += '<div id="lttf-' + l.num + '" class="tfils cliquable" onclick="$(\'tag' + tpere.num + '\').scrollTo() ;">' + tpere.nom + chaine + '</div>' ;	
			}
			else {
				console.log("J'ai cherché sans succès à afficher le lien TT, côté fils :") ;
				console.log(l) ;
			}
		}
		return 1 ;
	}
	else return 0 ;
} ;

lesLiensTT.form = function(num) {
	var l = this.getById(num) || {} ; 
	var tpere = lesTags.getById(l.tag1) ;
	var tfils = lesTags.getById(l.tag2) ;
	var f = new unFormulaire (this, "un lien entre tags", l) ;
	//description
	f.nvFieldset("Description") ;
	f.ligne("", tpere.nom)
	f.ligne("est le père de", tfils.nom) ;
	f.ligne("De", f.input("de")) ;
	f.ligne("A", f.input("a")) ;
	f.boutons() ;
	f.affiche() ;
} ;

lesLiensTT.modifie = function(oJson) {
	//modifie l'un des éléments de la collection
	for (var i = 0 ; i < this.donnees.length ; i++) {
		if (this.donnees[i].num == oJson.num) {
			this.donnees[i] = oJson ;
			$('lttp-' + oJson.num).remove() ;
			$('lttf-' + oJson.num).remove() ;
			lesLiensTT.afficheUne(oJson) ;
			$('tag' + oJson.tag2).scrollTo() ;
			return 1 ;
		}
	}
	return 0 ;
}


lesLiensTT.filiation = function() {
	//place les liens de filiation dans les tableaux pères et fils de chaque tag
	//à voir si on le garde, car cela ne sert que très peu en l'état.
	/*if (this.condition == 0) return 0 ;
	this.each(function(l) {
		lesTags.donnees[lesTags.getId(l.tag1)].fils.push(l.tag2) ;
		lesTags.donnees[lesTags.getId(l.tag2)].pere.push(l.tag1) ;
	}) ;*/
	return 1 ;
} ;

collectionLiensEL = {
	donnees:[],		//les données,
	elementTri:"nom",
	evenement:0,	//évènement de référence
	
	vide: function(evenement) {
		this.donnees = [] ;
		this.evenement = evenement || 0 ;
		return 1 ;
	},
	
	push:function(l) {
		l.nom = lesTags.getById(l.tag).nom ;
		return this.donnees.push(l) ;
	},
	
	sort:function() {
		this.donnees.sort(fonctionTri(this.elementTri)) ;
	},
		
	affiche:function() {
		chaine = "" ;
		for(var i = 0 ; i < this.donnees.length ; i++) {
			chaine += '<div id="liensel2' + this.donnees[i].num + '">' +  this.donnees[i].nom + ' ' + lienSupprime("lesLiensEL", this.donnees[i].num) + '</div>' ;
		}
		return chaine ;
	}
} ;

collectionLiensEP = {
	donnees:[],		//les données,
	elementTri:"nom",
	evenement:0,

	setEvenement:function(e) {
		return this.evenement = e ;
	},
	vide: function() {
		this.donnees = [] ;
		this.evenement = 0 ;
		return 1 ;
	},

	push:function(l) {
		l.lapersonne = lesPersonnes.getById(l.personne) ;
		l.nom	= l.lapersonne.nom ;
		return this.donnees.push(l) ;
	},

	sort:function() {
		this.donnees.sort(fonctionTri(this.elementTri)) ;
	},

	affiche:function() {
		chaine = "" ;
		for(var i = 0 ; i < this.donnees.length ; i++) {
			chaine += '<div id="liensep2' + this.donnees[i].num +  '">' + lesPersonnes.afficheNM(this.donnees[i].lapersonne) ;
			if (this.donnees[i].lapersonne.naissance_evenementId == this.evenement) {
				chaine +=  ' <img src="../img/naissance.png" title="Cet évènement est associé à la naissance de cette personne." id="imglep2' + i + '">' ;
			}
			else if (this.donnees[i].lapersonne.mort_evenementId == this.evenement) {
				chaine +=  ' <img src="../img/deces.png" title="Cet évènement est associé au décès de cette personne." id="imglep2' + i + '">' ;
			}
			else {
				chaine +=  ' <img src="../img/evenement.png" title="Cet évènement fait simplement partie de la vie de cette personne." id="imglep2' + i + '">' ;
			}
			chaine += lienSupprime("lesLiensEP", this.donnees[i].num) + '</div>' ;
		}
		return chaine ;
	}
} ;

//les liens personnes - personnes

var lesPersonnesPP = new coreObjet("personne") ;	//catalogue de référence des personnes qui serviront pour les liens PP

var lesLiensPP = new coreObjet("relationpersonnes") ;
lesLiensPP.typerelation 	= new Array("", "Fils", "Frère", "Epoux", "Neveu", "Fils adoptif", "Petit-fils", "Cousin", "Disciple", "Amant", "Membre", "Fils illégitime") ;
lesLiensPP.typerelationf 	= new Array("", "Fille", "Soeur", "Epouse", "Nièce", "Fille adoptive", "Petite-fille", "Cousine", "Disciple", "Maîtresse", "Membre", "Fille illégitime") ;

lesLiensPP.typerelationinv = new Array("", "Père", "Frère", "Epoux", "Oncle", "Père adoptif", "Grand-père", "Cousin", "Maître", "Amant", "Comprend", "Père illégitime") ;
lesLiensPP.typerelationfinv = new Array("", "Mère", "Soeur", "Epouse", "Tante", "Mère adoptive", "Grand-mère", "Cousin", "Maîtresse", "Maîtresse", "Comprend", "Mère illégitime") ;
lesLiensPP.typerelationsglobal = new Array("", "Fils", "Frère", "Epoux", "Neveu", "Fils adoptif", "Petit-fils", "Cousin", "Disciple", "Amant", "Membre", "Fils illégitime", "Père", "Frère", "Epoux", "Oncle", "Père adoptif", "Grand-père", "Cousin", "Maître", "Amant", "Comprend", "Père illégitime") ;
lesLiensPP.typerelationsglobalf = new Array("", "Fille", "Soeur", "Epouse", "Nièce", "Fille adoptive", "Petite-fille", "Cousine", "Disciple", "Maîtresse", "Membre", "Fille illégitime", "Mère", "Soeur", "Epouse", "Tante", "Mère adoptive", "Grand-mère", "Cousin", "Maîtresse", "Maîtresse", "Comprend", "Mère illégitime") ;

lesLiensPP.corrige = function() {
	//1ère étape : on associe les tags à leurs nom
	this.each(lesLiensPP.corrigeUne) ;
	//2e étape : on trie - une fois pour toute, cela garantit ensuite que pour chaque personne, c'est trié !
	//this.sort("nom") ;
	//3e étape : on range dans les personnes
	this.each(function(lelpp) {
		p = lesPersonnes.getById(lelpp.personne1) ;
		if (p) p.lpp.push(lelpp) ;
		p = lesPersonnes.getById(lelpp.personne2) ;
		if (p) p.lpp.push(lelpp) ;
	}) ;
	return 1 ;
} ;

lesLiensPP.corrigeUne = function(l) {
	var p1 = lesPersonnesPP.getById(l.personne1) ;
	var p2 = lesPersonnesPP.getById(l.personne2) ;
	if (p1) {
		l.personne1nom 	= p1.nom ;
		l.personne1sexe = p1.sexe ;
	}
	else {
		new Ajax.Request('../ajax/o_ajax_content.php', {
			"method":"post",
			"postBody":"objet=personne&num=" + l.personne1,
			"onSuccess":function(requester) {
				lesPersonnesPP.donnees.push(requester.responseJSON.personne[0]) ;
				lesLiensPP.corrigeUne(l) ;
				lesPersonnes.affiche() ;
			}
		}) ;
	}
	if (p2) {
		l.personne2nom = p2.nom ;
		l.personne2sexe = p2.sexe ;
	}
	else {
		new Ajax.Request('../ajax/o_ajax_content.php', {
			"method":"post",
			"postBody":"objet=personne&num=" + l.personne2,
			"onSuccess":function(requester) {
				lesPersonnesPP.donnees.push(requester.responseJSON.personne[0]) ;
				lesLiensPP.corrigeUne(l) ;
				lesPersonnes.affiche() ;
			}
		}) ;
	}
	var tr = "" ;
	if (l.personne1sexe == "f") tr = lesLiensPP.typerelationf[l.typerelation] ;
	else tr = lesLiensPP.typerelation[l.typerelation] ;
	l.chaine1 = '<div id="lienspp-1' + l.num + '"><span onclick="lesLiensPP.form(' + l.num + ')" class = "cliquable"><b>' + tr + '</b></span> de <span class="cliquable" id="liensppnom-1' + l.num + '" title="">' + donnees.lienPersonne(l.personne2nom, l.personne2) +  '</span> ' + lienSupprime("lesLiensPP", l.num) + '</div>' ;
	if (l.personne2sexe == "f") tr = lesLiensPP.typerelationfinv[l.typerelation] ;
	else tr = lesLiensPP.typerelationinv[l.typerelation] ;
	l.chaine2 = '<div id="lienspp-2' + l.num + '"><span onclick="lesLiensPP.form(' + l.num + ')" class = "cliquable"><b>' + tr + '</b></span> de <span class="cliquable" id="liensppnom-2' + l.num + '" title="">' + donnees.lienPersonne(l.personne1nom, l.personne1) +  '</span> ' + lienSupprime("lesLiensPP", l.num) + '</div>' ;
	return l ;
}

lesLiensPP.affiche = function() { 
	//remplace la fonction d'affichage normale, qui n'est pas utile.
	//l'affichage des liens PP est réalisé par l'objet lesPersonnes.
	return 1 ; 
} ;

lesLiensPP.form = function (num) {
	lpp = this.getById(num) || {"num":0, "personne1nom":"", "personne2nom":""} ;
	var f = new unFormulaire (this, "une relation entre personnes", lpp) ;
	f.nvFieldset() ;
	if (lpp.num > 0) {
		f.ligne("", lpp.personne1nom) ;
		f.hidden("personne1", lpp.personne1nom) ;
		if (lpp.personne1sexe == "f") f.ligne("est la ", f.selectTableauSimple("typerelation", this.typerelationf, lpp.typerelation)) ; 
		else f.ligne("est le", f.selectTableauSimple("typerelation", this.typerelation, lpp.typerelation)) ;
	}
	else {
		f.ligne("", f.inputac("personne1", "", "personne", "nom")) ;
		f.ligne("est le /la", f.selectTableauSimple("typerelation", this.typerelationsglobal, lpp.typerelation)) ;
	}
	f.ligne("de", f.inputac("personne2", lpp.personne2nom, "personne", "nom")) ;
	f.boutons() ;
	f.affiche() ;
} ;

lesLiensPP.supprime = function(num) {
	for (var i = 0 ; i < this.donnees.length ; i++) {
		if (this.donnees[i].num == num) {
			var p = lesPersonnes.getById(this.donnees[i].personne1) ;
			if (p) {
				if ($('lienspp-1' + this.donnees[i].num)) $('lienspp-1' + this.donnees[i].num).fade() ;
				for (var j = 0 ; j < p.lpp.length ; j++) {
					if (p.lpp[j].num == num) {
						delete p.lpp[j] ;
						j = p.lpp.length ;
						console.log("Suppression du lien pp côté 1") ;
					}
				}
			}
			p = lesPersonnes.getById(this.donnees[i].personne2) ;
			if (p) {
				if ($('lienspp-2' + this.donnees[i].num)) $('lienspp-2' + this.donnees[i].num).fade() ;
				for (var j = 0 ; j < p.lpp.length ; j++) {
					if (p.lpp[j].num == num) {
						delete p.lpp[j] ;
						j = p.lpp.length ;
						console.log("Suppression du lien pp côté 2") ;
					}
				}
			}
			i = this.length ;
			return 1 ;		
		}
	}
	console.log("Echec de la suppression du lienpp sur le client.") ;
	return 0 ;
} ;

lesLiensPP.obtientPersonne = function(p) {
	//permet d'ajouter les liens PP d'une personne déterminée, p, passée comme telle ou en numéro
	//le résultat est corrigé et stocké dans les liens PP de la personne
	if (typeof p !== "number") num = p.num ; 
	else {
		num = p ;
		p = lesPersonnes.getById(num) ;
	}
	var listeNum = p.lpp.pluck("num") ;
	new Ajax.Request('../ajax/o_ajax_content.php', {
		"method":"post",
		"postBody":"objet=lienpp&personne1=" + num,
		"onSuccess":function(requester) {
			requester.responseJSON.lienpp.each(function(lelpp) {
				if (!listeNum.include(lelpp.num)) p.lpp.push(lelpp) ;
			}) ;
			
			return lesPersonnes.affiche() ; 
		}
	}) ;
} ;


/* liens PP 
 * modification substantielle au 28 mai 2013(?)
 * désormais, la gestion des liens pp repose sur deux objets de type coreObjet : 
 * le principal est lesLiensPP, qui contient les liens tels qu'issus de la table relationpersonnes et enrichis par la fonction corrigeUne, avec le nom et le sexe de chacune des deux personnes de la relation
 * le second est lesPersonnesPP, qui est un catalogue des personnes citées dans lesLiensPP
 * 


var lesLiensPP =  new coreObjet("relationpersonnes") ;
lesLiensPP.lien = "pp" ;
lesLiensPP.typerelation 	= new Array("", "Fils", "Frère", "Epoux", "Neveu", "Fils adoptif", "Petit-fils", "Cousin", "Disciple", "Amant", "Membre", "Fils illégitime") ;
lesLiensPP.typerelationf 	= new Array("", "Fille", "Soeur", "Epouse", "Nièce", "Fille adoptive", "Petite-fille", "Cousine", "Disciple", "Maîtresse", "Membre", "Fille illégitime") ;

lesLiensPP.typerelationinv = new Array("", "Père", "Frère", "Epoux", "Oncle", "Père adoptif", "Grand-père", "Cousin", "Maître", "Amant", "Comprend", "Père illégitime") ;
lesLiensPP.typerelationfinv = new Array("", "Mère", "Soeur", "Epouse", "Tante", "Mère adoptive", "Grand-mère", "Cousin", "Maîtresse", "Maîtresse", "Comprend", "Mère illégitime") ;
lesLiensPP.groupe			= new Array(new Array(0, 1, 4, 2, 4, 1, 1, 4, 4, 4, 4, 1), new Array(0, 3, 4, 2, 4, 3, 3, 4, 4, 4, 4, 3)) ;

lesLiensPP.typerelationsglobal = new Array("", "Fils", "Frère", "Epoux", "Neveu", "Fils adoptif", "Petit-fils", "Cousin", "Disciple", "Amant", "Membre", "Fils illégitime", "Père", "Frère", "Epoux", "Oncle", "Père adoptif", "Grand-père", "Cousin", "Maître", "Amant", "Comprend", "Père illégitime") ;
lesLiensPP.typerelationsglobalf = new Array("", "Fille", "Soeur", "Epouse", "Nièce", "Fille adoptive", "Petite-fille", "Cousine", "Disciple", "Maîtresse", "Membre", "Fille illégitime", "Mère", "Soeur", "Epouse", "Tante", "Mère adoptive", "Grand-mère", "Cousin", "Maîtresse", "Maîtresse", "Comprend", "Mère illégitime") ;
lesLiensPP.vide = lesLiensTT.vide ;

lesLiensPP.corrigeUne = function(l) {
	var p1 = lesPersonnesPP.getById(l.personne1) ;
	var p2 = lesPersonnesPP.getById(l.personne2) ;
	l.personne1nom = p1.nom ;
	l.personne1sexe = p1.sexe ;
	l.personne2nom = p2.nom ;
	l.personne2sexe = p2.sexe ;
	return l ;
} ;

lesLiensPP.affiche = function() {
	if (this.charge == 0) return -1 ;
	if (lesPersonnes.charge == 0) return -2 ;
	lesPersonnes.each(function(p) {
		if ($('leslienspp' + p.num)) {
			$('leslienspp' + p.num).innerHTML = "" ;
		}
		else {
			console.log("J'ai essayé sans succès d'afficher le lien PP de " + p.nom);
			console.log(p) ;
		}
		
	}) ;
	this.each(this.afficheUne) ;
	return 1 ;
} ;

lesLiensPP.afficheUne = function(l) {
	//1 - on cherche à afficher le lien côté personne1.
	var tr = "" ;
	var a= 0 ;
	if (lesPersonnes.getById(l.personne1)) {
		if ($('leslienspp' + l.personne1)) {
			if (l.personne1sexe == "f") tr = lesLiensPP.typerelationf[l.typerelation] ;
			else tr = lesLiensPP.typerelation[l.typerelation] ;
			if ($('leslienspp' + lesLiensPP.groupe[0][l.typerelation] + '-' + l.personne1)) {
				$('leslienspp' + lesLiensPP.groupe[0][l.typerelation] + '-' + l.personne1).innerHTML += '<div id="lienspp' + l.num + '"><span onclick="lesLiensPP.form(' + l.num + ')" class = "cliquable"><b>' + tr + '</b></span> de <span class="cliquable" title="">' + donnees.lienPersonne(l.personne2nom, l.personne2) +  '</span> ' + lienSupprime("lesLiensPP", l.num) ;				
			}
			else {
				console.log("Erreur dans l'affichage de ") ;
				console.log(l) ;
				console.log('leslienspp' + lesLiensPP.groupe[0][l.typerelation] + '-' + l.personne1 + ' n existe pas.') ;
			}
			a = a +1 ;
		}
	}
	//2 - et du côté personne2
	var tr = "" ;
	if (lesPersonnes.getById(l.personne2)) {
		if ($('leslienspp' + l.personne2)) {
			if (l.personne2sexe == "f") tr = lesLiensPP.typerelationfinv[l.typerelation] ;
			else tr = lesLiensPP.typerelationinv[l.typerelation] ;
			if ($('leslienspp' + lesLiensPP.groupe[1][l.typerelation] + '-' + l.personne2)) {} 
				$('leslienspp' + lesLiensPP.groupe[1][l.typerelation] + '-' + l.personne2).innerHTML += '<div id="lienspp' + l.num + '"><span onclick="lesLiensPP.form(' + l.num + ')" class = "cliquable"><b>' + tr + '</b></span> de <span class="cliquable" title="">' + donnees.lienPersonne(l.personne1nom, l.personne1) + '</span>' + lienSupprime("lesLiensPP", l.num) ;
			}
		else {
			console.log("Erreur dans l'affichage de ") ;
			console.log(l) ;
			console.log('leslienspp' + lesLiensPP.groupe[1][l.typerelation] + '-' + l.personne2 + ' n existe pas.') ;
		}
		a = a + 2 ;
	}
	return a ;
} ;

lesLiensPP.modifie = function(oJson) {
	var ok = 0 ;
	//traitement des liens ep nouveau éventuel
	if (oJson.hasOwnProperty("lienep")) {
		lesLiensEP.donnees.push(oJson.lienep) ;
	}
	//1e partie - on vérifie si p1 et p2 existent bien dans le tableau de référence
	if (!lesPersonnesPP.getById(oJson.personne1)) {
		var askPersonne = new Ajax.Request(localisation + 'ajax/o_ajax_content.php', { method : 'post',
			"postBody" : 'objet=personne&num=' + oJson.personne1,
			"onSuccess" : function(result, eventnum) {
				console.log(result.responseJSON.personne[0]) ;
				lesPersonnesPP.donnees.push(result.responseJSON.personne[0]) ;
				lesPersonnes.affiche() ; 
			}
		}) ;
	}
	if (!lesPersonnesPP.getById(oJson.personne2)) {
		var askPersonne = new Ajax.Request(localisation + 'ajax/o_ajax_content.php', { method : 'post',
			"postBody" : 'objet=personne&num=' + oJson.personne2,
			"onSuccess" : function(result, eventnum) {
				console.log(result.responseJSON.personne[0]) ;
				lesPersonnesPP.donnees.push(result.responseJSON.personne[0]) ;
				lesPersonnes.affiche() ; 
			}
		}) ;
	}
	lesLiensPP.corrigeUne(oJson) ;
	//2e partie - on ajoute ou on modifie le lien PP en tant que tel
	for (var i = 0 ; i < this.donnees.length ; i++) {
		if (this.donnees[i].num == oJson.num) {
			this.donnees[i] = oJson ;
			i == this.donnees.length ;
			ok = 1 ;
		}
	}
	if (ok == 0) {
		this.donnees.push(oJson) ;
	}
	
}  ;

lesLiensPP.form = function (x, p) {
	if (x > -1) rpp = this.getById(x) ;
	else {
		p1 = lesPersonnes.getById(p) ;
		rpp = {"personne1nom":p1.nom, "personne1sexe":p1.sexe, "num":0} ;
	}
	var f = new unFormulaire (this, "une relation entre personnes", rpp) ;
	f.nvFieldset() ;
	f.ligne("", rpp.personne1nom) ;
	f.hidden("personne1", rpp.personne1nom) ;
	if (rpp.num > 0) {
		if (rpp.personne1sexe == "f") f.ligne("est la ", f.selectTableauSimple("typerelation", this.typerelationf, rpp.typerelation)) ; 
		else f.ligne("est le", f.selectTableauSimple("typerelation", this.typerelation, rpp.typerelation)) ;
	}
	else {
		if (rpp.personne1sexe == "f") f.ligne("est la ", f.selectTableauSimple("typerelation", this.typerelationsglobalf, rpp.typerelation)) ; 
		else f.ligne("est le", f.selectTableauSimple("typerelation", this.typerelationsglobal, rpp.typerelation)) ;
	}
	f.ligne("de", f.inputac("personne2", rpp.personne2nom, "personne", "nom")) ;
	f.boutons() ;
	f.affiche() ;
} ;

lesLiensPP.supprime = function(x) {
	for (var i = 0 ; i < this.donnees.length ; i++) {
		if (this.donnees[i].num == x) {
			$('liens' + this.lien + this.donnees[i].num).fade() ;
			$('liens' + this.lien + '2' + this.donnees[i].num).fade() ;
			this.donnees[i] = {} ;
			i = this.length ;
			return 1 ;
		}
	}
	return 0 ;
} ;

*/

function collectionLiensPP(personne) {
	this.donnees = [] ;		//les données,
	this.personne = personne ;
	this.vide = function() {
		this.donnees = [] ;
		return 1 ;
	} ;
	this.push = function(l) {
		return this.donnees.push(l) ;
	} ;
	this.sort = function(elementTri) {
		this.elementTri = elementTri || "personne1nom" ;
		return this.donnees.sort(fonctionTri(this.elementTri)) ;
	} ;
	this.affiche = function(personne) {
		var personneAffichee = personne || "personne2nom" ; 
		chaine = "" ;
		for(var i = 0 ; i < this.donnees.length ; i++) {
			chaine += '<div id="lienspp2' + this.donnees[i].num +  '">' + this.donnees[i][personneAffichee] + lienSupprime("lesLiensPP", this.donnees[i].num) + '</div>' ;
		}
		return chaine ;
	} ;
	this.afficheDiscriminant = function(personne) {
		chaine = "" ;
		for(var i = 0 ; i < this.donnees.length ; i++) {
			if (this.donnees[i].personne1 == personne) {
				chaine += '<div id="lienspp2' + this.donnees[i].num +  '">' + this.donnees[i].personne2nom + lienSupprime("lesLiensPP", this.donnees[i].num) + '</div>' ;
			}
			else {
				chaine += '<div id="lienspp2' + this.donnees[i].num +  '">' + this.donnees[i].personne1nom + lienSupprime("lesLiensPP", this.donnees[i].num) + '</div>' ;
			}
		}
		return chaine ;
	}
} ; 

//les liens personnes - tags

var lesLiensPT = new coreObjet("lienpl") ;

lesLiensPT.corrige = function() {
	//1ère étape : on associe les tags à leurs nom
	this.each(lesLiensPT.corrigeUne) ;
	//2e étape : on trie - une fois pour toute, cela garantit ensuite que pour chaque personne, c'est trié !
	this.sort("nom") ;
	//3e étape : on range dans les personnes
	this.each(function(lelpt) {
		p = lesPersonnes.getById(lelpt.personne) ;
		if (p) {
			p.lpt.push(lelpt) ;
		}
	}) ;
	return lesPersonnes.affiche() ;
} ;

lesLiensPT.corrigeUne = function(lpt) {
	t = lesTags.getById(lpt.lieu) ;
	lpt.nom = t.nom ;
	lpt.drapeau = lesTags.getNomOuDrapeau(t) ;
} ;

lesLiensPT.affiche = function() { 
	//remplace la fonction d'affichage normale, qui n'est pas utile.
	//l'affichage des liens PT est réalisé par l'objet lesPersonnes.
	return 1 ; 
} ;

lesLiensPT.obtientPersonne = function(p) {
	//permet d'ajouter les liens PT d'une personne déterminée, p, passée comme telle ou en numéro
	//le résultat est corrigé et stocké dans les liens PT de la personne
	if (typeof p !== "number") num = p.num ; 
	else {
		num = p ;
		p = lesPersonnes.getById(num) ;
	}
	var listeNum = p.lpt.pluck("num") ;
	console.log(listeNum) ;
	new Ajax.Request('../ajax/o_ajax_content.php', {
		"method":"post",
		"postBody":"objet=lienpl&personne=" + num,
		"onSuccess":function(requester) {
			requester.responseJSON.lienpl.each(function(lelpt) {
				lesLiensPT.corrigeUne(lelpt) ;
				if (!listeNum.include(lelpt.num)) p.lpt.push(lelpt) ;
			}) ;
			return lesPersonnes.affiche() ; ;
		}
	}) ;
} ;

lesLiensPT.reqSupprime = function(num) {
	//envoie une demande de suppression
	messager.affiche("Suppression du lien personne tag en cours...") ;
	new Ajax.Request(localisation + 'ajax/o_ajax_delete.php', {
		method:"post",
		"postBody":"objet=lienpl&num=" + num,
		"onSuccess" : function(requester) {
			if (requester.responseJSON.result == 1) {
				if (lesLiensPT.supprime(num) > 0) {
					messager.fade("Suppression effective.") ;
					return 1 ;
				}
				else {
					messager.fade("Erreur dans le processus interne. L'objet est cependant bien supprimé dans la base. ") ;
					return 0 ;
				}
			}
			else {
				messager.fade("Echec dans la suppression.") ;
				return 0 ;
			}
		}
	}) ;
} ;

lesLiensPT.supprime = function(x) {
	//supprime effectivement l'un des éléments de la collection
	var lpt = this.getById(x) ;
	var p = lesPersonnes.getById(lpt.personne) ;
	for (var i = 0 ; i < p.lpt.length ; i++) {
		if (p.lpt[i].num == x) {
			//a - on l'enlève de la personne
			p.lpt[i] = {} ;
			i = p.lpt.length ;
			//b - on le supprime de l'affichage dans le formulaire
			$('lienplf' + lpt.num).fade() ;
			//c - on le supprime de l'affichage dans le listing des personnes
			$('lptid-' + lpt.num).fade() ;
			return 1 ;
		}
	}
	return 0 ;
} ;


/* les liens personnes fonctions */
/* écrit de façon fonctionnelle le 1er juin 2014.
 * ces liens sont en réalité des évènements qui matérialisent la fonction qu'une personne a occupé. 
 * l'objet sert exclusivement comme "tampon", pour le chargement initial ou ensuite ultérieur d'une personne.
 * il n'a pas d'autre fonctionnalité à cet objet. La suppression ou la modification des liens PF doivent passer
 * par l'édition de l'évènement concerné. */

var lesLiensPF = new coreObjet("lienpf") ;

lesLiensPF.corrige = function() {
	//1ère étape : on associe les tags à leurs nom
	this.each(lesLiensPF.corrigeUne) ;
	//2e étape : on trie - une fois pour toute, cela garantit ensuite que pour chaque personne, c'est trié !
	this.sort("andebut") ;
	//3e étape : on range dans les personnes
	this.each(function(lelpf) {
		p = lesPersonnes.getById(lelpf.personne) ;
		if (p) {
			p.lpf.push(lelpf) ;
		}
	}) ;
	return lesPersonnes.affiche() ;
} ;

lesLiensPF.corrigeUne = function(l) {
	return l.fonctionNom = lesFonctions.getById(l.fonction).nom ;
} ;

lesLiensPF.affiche = function() {
	//remplace la fonction d'affichage normale, qui n'est pas utile.
	//l'affichage des liens PF est réalisé par l'objet lesPersonnes.
	return 1 ;
} ;

lesLiensPF.obtientPersonne = function(p) {
	//permet d'ajouter les liens PF d'une personne déterminée, p, passée comme telle ou en numéro
	//le résultat est corrigé et stocké dans les liens PT de la personne
	if (typeof p !== "number") num = p.num ; 
	else {
		num = p ;
		p = lesPersonnes.getById(num) ;
	}
	var listeNum = p.lpt.pluck("num") ;
	console.log(listeNum) ;
	new Ajax.Request('../ajax/o_ajax_content.php', {
		"method":"post",
		"postBody":"objet=lienpf&personne=" + num,
		"onSuccess":function(requester) {
			requester.responseJSON.lienpf.each(function(lelpf) {
				lesLiensPT.corrigeUne(lelpf) ;
				if (!listeNum.include(lelpf.num)) p.lpt.push(lelpf) ;
			}) ;
			return lesPersonnes.affiche() ; ;
		}
	}) ;
} ;
