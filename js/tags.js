/* specifications propres aux tags */
var lesTags = new coreObjet("tag") ;

lesTags.corrigeUne = function(t) {
	t.fils 		= new Array() ;
	t.pere		= new Array() ;
	if (t.nom != "Nord" && t.nom != "Centre" && t.nom != "Politique" && t.nom != "Militaire" && t.nom != "Monde" && t.nom != "Bataille") {
		if (t.nom.length > 4) {
			var s		= "" ;
			if (t.autreNom != "") {
				//il y a aussi d'autres noms. On concocte un listing sous forme nom|autrenom1|autrenom2
				s = "|" + t.autreNom.split(",").map(function(c, i) {return c.strip() ;}).join("|") ;		//les autres noms sont susceptibles d'être divisés par des ,
			}
			t.regExp1	= new RegExp("(\\s|\\.|,|'|\\(){1}" + "(" + t.nom + s + ")(\\s|\\.|,|\\)){1}", 'i') ;
			t.regExp4	= new RegExp("^" + "(" + t.nom + s + ")( |:)", "") ;		//recherche en début de mot. Du coups, les majuscules comptent et le mot doit être suivi d'un espace ou d'un :
			if (t.adjectifs != "") {
				s = t.adjectifs.split(",").map(function(c, i) {return c.strip() ;}).join("|") ;
				t.regExp2	= new RegExp("(" + s + ")(e|es|s)?", 'i') ;
			}
			else {
				t.regExp2	= false ;
			}
		}
		else {
			if (t.autreNom != "") {
				var s		= t.autreNom.split(",").map(function(c, i) {return c.strip() ;}).join("|") ;
				t.regExp1	= new RegExp("(\\s|\\.|'){1}(" + t.nom + "|" + s + ")(\\s|\\.|,){1}", 'i') ;
			}
			else {
				t.regExp1	= new RegExp("(\\s|\\.|'){1}(" + t.nom + ")(\\s|\\.|,){1}") ;
			}
			if (t.adjectifs != "") {
				s = t.adjectifs.split(",").map(function(c, i) {return c.strip() ;}).join("|") ;
				t.regExp2	= new RegExp("(" + s + ")(e |es |s )*") ;
			}
			else {
				t.regExp2	= false ;
			}
		}
		if (t.nature == "1" || t.nature == 15) {
			t.regExp5 = new RegExp("à (" + t.nom + s + ")( |,|.)") ;
		}
		else {
			t.regExp5 = false ;
		}
	}
	else {
		t.regExp1 = false ;
		t.regExp2 = false ;
	}
	//table des mots clés
	if (t.motscles != "") {
		s = t.motscles.split(",").map(function(c, i) {return c.strip() ;}).join("|") ;
		t.regExp3 = new RegExp("(" + s + ")", 'i') ;
	}
	else {
		t.regExp3 = false ;
	}
		
	t.tableAdjectifs	= t.adjectifs.split(",") ;
	t.tableAutreNom		= t.autreNom.split(",") ;
	t.visible	= 1 ;
	return 1 ;
} ;

lesTags.afficheUne = function(t) {
	if (t.visible == 0) return "" ;
	var chaine = '<tr class="uneLigne" id="tag' + t.num+'">' ;
	//case 1 - Image
	if (t.drapeau != "") {
		if (t.drapeau.slice(0, 11) == "drapeau_tag") {
			t.drapeau = CTFA.urlBaseDrapeau + t.drapeau ;
		}
	}
	chaine += caseImage(t.drapeau) ;
	//case 2 - Elements d'information
	var schaine = '<td class="caseTexte" width="45%"><span onclick = "lesTags.form(' + t.num + ')" class="cliquable">' + t.nom + '</span> ' + lienSupprime("lesTags", t.num) + '</div>' ;
	schaine += '<div class="unePetiteInfo">' + t.designation +'</div>' ;
	schaine += '<div class="unePetiteInfo"><span class="uneCaseGras">Mots clés</span> : ' + t.motscles + '</div>' ;
	schaine += '<div class="unePetiteInfo"><span class="uneCaseGras">Autre(s) nom(s)</span> : ' + t.autreNom + '</div>' ;
	schaine += '<div class="unePetiteInfo"><span class="uneCaseGras">Adjectif(s)</span> : ' + t.adjectifs + '</div>' ;	
	schaine += '<div class="unePetiteInfo"><span class="uneCaseGras">Type</span> : ' + CTFA.typeTags[t.nature] ;
	if (t.latitude != 0) {
		schaine += ' <span>' + liengeoloc(t.latitude, t.longitude) + "</span> " 
	}
	schaine += '<div class="unePetiteInfo"><span class="uneCaseGras">Occurences</span> : ' + donnees.lienTag(t.occurence, 	t.num) + '</div>' ;
	chaine += schaine ;
	chaine += '<td id="tagperes' + t.num + '" class="unePetiteInfo caseTexte">&nbsp</td><td id="tagfils' + t.num + '" class="unePetiteInfo caseTexte">&nbsp</td></tr>' ;
	return chaine ;
} ;

lesTags.getNomOuDrapeau = function(t) {
	if (t.drapeau != "") {
		if (t.drapeau.slice(0, 11) == "drapeau_tag") {
			return '<img class="tagPetiteImage" src="' + CTFA.urlBaseDrapeau + t.drapeau + '" title="' + t.nom +'">' ;
		}
		else {
			return '<img class="tagPetiteImage" src="' + t.drapeau + '" title="' + t.nom +'">' ;
		}
	}
	else {
		return t.nom ;
	}
} ;

lesTags.modifie = function(oJson) {
	this.corrigeUne(oJson) ;
	var ok = 0 ;
	for (var i = 0 ; i < this.donnees.length ; i++) {
		if (this.donnees[i].num == oJson.num) {
			this.donnees[i] = oJson ;
			i == this.length ;
			ok = 1 ;
		}
	}
	if (ok == 0) {
		this.ajoute(oJson) ;
	}
	//Ajout des liens TT nouveaux
	if (oJson.hasOwnProperty("lientt")) {
		console.log(lesLiensTT.donnees.length) ;
		for (var j = 0 ; j < oJson.lientt.length ; j++) {
			lesLiensTT.ajoute(oJson.lientt[j]) ;
		}
	}
	this.sort() ;
	lesTags.affichePL() ;
	$('tag' + oJson.num).scrollTo() ;
	new Effect.Highlight('tag' + oJson.num) ;
} ;

lesTags.form = function(x) {
	var t = this.getById(x) || {} ; 
	var f = new unFormulaire (this, "un tag", t) ;
	//description
	f.nvFieldset("Description") ;
	f.ligne("Nom", f.input("nom")) ;
	f.ligne("URL", f.input("url")) ;
	f.ligne("Drapeau", f.input("drapeau")) ;
	f.ligne("Nature", f.selectTableauSimple("nature", CTFA.typeTags)) ;
	f.boutons() ;
	//Précision
	f.nvFieldset("Précisions") ;
	//couleur
	f.ligne("Autre nom", f.input("autreNom")) ;
	f.ligne("Mots clés", f.input("motscles")) ;
	f.ligne("Adjectifs", f.input("adjectifs")) ;
	f.ligne("Avec déterminant", f.input("designation")) ;
	f.ligne("Latitude", f.input("latitude")) ;
	f.ligne("Longitude", f.input("longitude")) ;
	f.boutons() ;
	//lien TT - Pères
	f.nvFieldset("Liens pères / fils") ;
	var chaine = "" ;
	if (t.num > 0) {
		for (var i = 0 ; i < lesLiensTT.donnees.length ; i++) {
			if (t.num == lesLiensTT.donnees[i].tag2) {
				chaine += '<div id="lientt' + lesLiensTT.donnees[i].num + '">' + this.getById(lesLiensTT.donnees[i].tag1).nom ;
				if (lesLiensTT.donnees[i].de != 0) {
					chaine += ' (' + lesLiensTT.donnees[i].de ;
					if (lesLiensTT.donnees[i].a != 0) {
						chaine += ' - ' + lesLiensTT.donnees[i].a + ')' ;
					}
					else {
						chaine += ')' ;
					}
				}
				else {
					chaine += '<span class="unePetiteInfo" onClick="lesLiensTT.form(' + lesLiensTT.donnees[i].num + ')"> (préciser les dates)</span>' ;
				}
				chaine += ' ' + lienSupprime("lesLiensTT", lesLiensTT.donnees[i].num) + '</div>' ;
			}
		}
	}
	chaine += f.inputacsimple("tagsPeres", "", lesTags.lesNoms) ;
	f.ligne("Tags pères", chaine) ;
	//lien TT - fils
	var chaine2 = "" ;
	if (t.num > 0) {
		for (var i = 0 ; i < lesLiensTT.donnees.length ; i++) {
			if (t.num == lesLiensTT.donnees[i].tag1) {
				chaine2 += '<div id="lientt' + lesLiensTT.donnees[i].num + '">' + this.getById(lesLiensTT.donnees[i].tag2).nom + ' ' + lienSupprime("lesLiensTT", lesLiensTT.donnees[i].num) + '</div>' ;
			}
		}
	}
	chaine2 += f.inputacsimple("Fils", "", lesTags.lesNoms) ;
	f.ligne("Tags fils", chaine2) ;
	f.boutons() ;
	f.affiche() ;
} ;

lesTags.toutremonter = function(listeTags) {
	if (lesTags.charge == 0) return 0 ;
	listeTagsPeres = [] ;
	for (var i = 0 ; i < listeTags.length ; i++) {
		if (listeTags[i].mode == "") {
			listeTagsPeres = lesTags.remonter(listeTags[i].num) ;
		}
	}
	return lesTags.remonteeAfficher(listeTagsPeres) ;
} ;

lesTags.remonter = function(pere, listeTagsPeres) {
	listeTagsPeres = listeTagsPeres || [] ;
	var tagPere = this.getById(pere) ;
	for (var i = 0 ; i < tagPere.pere.length ; i++) {
		if (listeTagsPeres.include(tagPere.pere[i]) == false) {
			listeTagsPeres.push(eval(tagPere.pere[i])) ;
		}
		listeTagsPeres = this.remonter(tagPere.pere[i], listeTagsPeres) ;
	}
	return listeTagsPeres ;
} ;

lesTags.remonteeAfficher = function(remontee) {
	var tableau = [] ;
	var chaine = "" ;
	for (var i = 0 ; i < remontee.length ; i++) {
		var leTag = this.getById(remontee[i]) ;
		tableau.push(leTag.nom) ;
	}
	tableau.sort() ;
	for (var i = 0 ; i < tableau.length ; i++) {
		chaine += tableau[i] + " - " ;
	}
	chaine = chaine.slice(0, -3) ;
	return chaine ;
} ;


function tritag(param) {
	lesTags.elementTri = param || "nom" ;
	lesTags.sort() ;
	lesTags.affichePL() ;
} ;
