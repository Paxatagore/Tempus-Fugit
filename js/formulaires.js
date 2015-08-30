//formulaires

tabindex = 1 ;
dateapproximation = new Array("", "vers", "siècle", "décennie", "saison") ;

function unFormulaire (coreObjet, titre, objet) {
	this.coreObjet		= coreObjet ;
	this.typeObjet 		= this.coreObjet.objetNom ;			//le type d'objet édité par le formulaire
	if (this.typeObjet == "") return 0 ;
	this.objet 	= objet || {} ;					//l'objet édité
	this.num	= objet.num || 0 ;
	this.compteBouton	= 0	;
	if (this.num == 0) {
		this.titre 		= "Ajouter " + titre ;
	}
	else {
		this.titre 		= "Modifier " + titre ;
	}
	//this.listener		= new Array() ;
	this.autocompleters	= new Array() ;			//un tableau de tous les autocompleters
	this.autocompleterLocals = new Array() ;	//un tableau de tous les autocompleters locaux
	this.tabindex 	= tabindex ;				//le tabindex
	this.fieldset 	= 0 ;						//0 - aucun fieldset n'est ouvert
	//partie d'affichage
	$('edition').innerHTML = "" ;
	this.titre = '<h2 class="titreFormulaire" id="titreFormulaire">' + this.titre + ' <img src="../img/iconefermer.png" height="20px" onClick="fermeFormulaire()"></h2>' ;
	this.chaine = '' ;
	this.hidden("objet", this.typeObjet) ;
	this.hidden("num", this.num) ;
	this.parties 	= new Array() ;
	this.npartie	= 0 ;
}

unFormulaire.prototype = {

	//utilitaires de mise en forme
	
	ligne : function(element1, element2, id, cache) {
		//ajoute une ligne de contenu
		this.chaine += '<tr id="' + id + '"><td style="vertical-align:top;"><label>' + element1 + '</label></td><td>' + element2 + '</td></tr>' ;
		return 1 ;
	},
	
	ligneCachee : function(element1, element2, id) {
		//ajoute une ligne de contenu caché mais révélable !
		this.chaine += '<tr id = "' + id + '" style="display:none"; ><td><label>' + element1 + '</label></td><td>' + element2 + '</td></tr>' ;
		return 1 ;
	},
	
	tabInd : function() {
		//incrémente le tabindex et renvoie la chaine de caractère de tabindex
		this.tabindex++ ;
		return 'tabindex="' + this.tabindex + '"' ;
	},
	
	boutons : function(nom) {
		//affiche les boutons d'envoi
		this.nomBouton = nom || "Envoyer" ;
		if (this.fieldset == 1) {
			this.finfieldset() ;
		}
		this.chaine += '<p class="submit"><img src="../img/petiteflechehaute.jpg" onClick="$(\'titreFormulaire\').scrollTo() ;"> <input type="submit" ' + this.tabInd() + ' value="' + this.nomBouton + '" id="boutonsoumettre' + this.compteBouton	+ '"><input type="reset" ' + this.tabInd() + ' value="Remise à zéro" id="boutonraz"></p>'; 
		this.compteBouton++	;
	},
	
	affiche : function () {
		//à écrire
		var fs = '<p class="formmenu">' ;
		if (this.parties.length > 0) {
			for (var i = 0 ; i < this.parties.length ; i++) {
				fs += '<span onClick="$(\'fs' + i + '\').scrollTo() ;">' + this.parties[i] + "</span> - " ;
			}
			fs = fs.slice(0, fs.length - 3) ;
		}
		fs += '</p>' ;
		
		$('edition').innerHTML = this.titre + fs + this.chaine ;
		if (this.autocompleters.length > 0) {
			for (var i = 0 ; i < this.autocompleters.length ; i++) {
				new Ajax.Autocompleter (this.autocompleters[i].champ, "upd_" + this.autocompleters[i].champ, '../ajax/o_autocomplete.php', {method: 'post', paramName: 'premice', parameters: 'objet=' + this.autocompleters[i].objet + '&champ=' +this.autocompleters[i].champBDD, minChars: 2, tokens: ','}) ;
			}
		}
		if (this.autocompleterLocals.length > 0) {
			for (var i = 0 ; i < this.autocompleterLocals.length ; i++) {
				new Autocompleter.Local (this.autocompleterLocals[i].champ, "upd_" + this.autocompleterLocals[i].champ, this.autocompleterLocals[i].tableau, {"minChars": 2, "tokens": ',', "ignoreCase":true}) ;
			}
		}		
		$('edition').style.display = "block" ;
		for (var i = 0 ; i < this.compteBouton	; i++) {
			$('boutonsoumettre' + i).observe("click", this.coreObjet.sendForm.bindAsEventListener(this.coreObjet)) ;
		}
		window.scrollTo(0,0) ;
		Form.focusFirstElement('edition') ;	
	},
	
	//gestion des fieldset
	nvFieldset : function(titreF) {
		titreF = titreF || "" ;
		//affiche un nouveau fieldset
		if (this.fieldset == 1) {
			//Ferme le fieldset précédent si besoin
			this.finfieldset() ;
		}
		this.fieldset = 1 ;
		if (titreF != "") {
			this.chaine += '<fieldset><legend id="fs' + this.npartie + '">' + titreF + '</legend><table><tbody>' ;
			this.parties.push(titreF) ;
			this.npartie++ ;
		}
		else {
			this.chaine += '<fieldset><table><tbody>' ;
		}
	},
	
	finfieldset : function() {
		this.chaine += '</tbody></table></fieldset>' ;
		this.fieldset = 0 ;
	},

	testeValeur : function(nom, valeur) {
		if (typeof valeur == "undefined") {
			if (this.objet.hasOwnProperty(nom)) {
				return this.objet[nom] ;
			}
			else return "" ;
		}
		else return valeur ;
	},
	
	//champs
	hidden : function (nom, valeur) {
		valeur = this.testeValeur(nom, valeur) ;
		this.chaine += '<input type="hidden" name="' + nom + '" id="' + nom + '" value="' + valeur + '" class="formInput">' ;
		return 1 ;
	},
	
	input : function (champ, valeur, taille, classeCSS) {
		classeCSS = classeCSS || "" ;
		valeur = this.testeValeur(champ, valeur) ;
		taille = taille || 50 ;
		contenu = '<input type="text" id="' + champ + '" name="' + champ + '" value="' + valeur + '" ' + this.tabInd() + ' size="' + taille + '" class="inputOrdinaire ' + classeCSS + '">' ;
		//contenu = '<input onChange="sendForm2(' + this.num + ', \'' + this.coreObjet + '\', \'' + champ + '\' )" type="text" id="' + champ + '" name="' + champ + '" value="' + valeur + '" ' + this.tabInd() + ' size="' + taille + '" class="inputOrdinaire">' ;
		//this.listener.push({"champ":champ}) ;
		return contenu ;
	},
	
	inputac : function (champ, valeur, objet, champBDD, taille, classeCSS) {
		classeCSS = classeCSS || "" ;
		valeur = this.testeValeur(champ, valeur) ;
		taille = taille || 50 ;
		contenu	= '<input class="inputTexte ' + classeCSS + '" type="text" value="' + valeur + '" ' + this.tabInd() + ' name = "' + champ + '" id = "' + champ + '" size = "' + taille + '"><div class="autocomplete" id="upd_' + champ + '"></div>' ;
		this.autocompleters.push({"champ":champ, "objet":objet, "champBDD":champBDD}) ;
		//new Ajax.Autocompleter (champ, "upd_" + champ, '../ajax/o_autocomplete.php', {method: 'post', paramName: 'premice', parameters: 'objet=' + objet + '&champ=' +champBDD, minChars: 2}) ;
		return contenu ;
	},
	
	inputacsimple : function (champ, valeur, tableau, taille, classeCSS) {
		classeCSS = classeCSS || "" ;
		valeur = this.testeValeur(champ, valeur) ;
		taille = taille || 50 ;
		contenu	= '<input class="inputTexte ' + classeCSS + '" type="text" value="' + valeur + '" ' + this.tabInd() + ' name = "' + champ + '" id = "' + champ + '" size = "' + taille + '"><div class="autocomplete" id="upd_' + champ + '"></div>' ;
		this.autocompleterLocals.push({"champ":champ, "tableau":tableau}) ;
		return contenu ;
	},
	
	textarea : function (champ, contenu, tailleMax) {
		tailleMax = tailleMax || 15 ;
		contenu = this.testeValeur(champ, contenu) ;
		taille = Math.max(tailleMax, contenu.length /80 + tailleMax) ;
		contenu = '<textarea cols="80" rows="' + taille + '" ' + this.tabInd() +  ' name="' + champ + '" class="formInput inputOrdinaire" id="' + champ + '" >' + contenu +  '</textarea>' ;
		return contenu ;
	},
	
	fdate2 : function(champ, date) {
		date = date || {"andebut":"", "moisdebut":0, "jourdebut":"", "versdebut":0, "anfin":"", "moisfin":0, "jourfin":"", "versfin":0, "ecartdate":"0"}
		var contenu = ['<span class="inputOrdinaire">'] ; 
		var c = [['selected', ''], ['', 'selected']]
		contenu.push('<select id="ecartdate' + champ + '"' + this.tabInd() + '><option value="0" ' + c[date.ecartdate][0] + '>du<option value="1" ' + c[date.ecartdate][1] + '>entre le...</select>') ;
		//contenu.push(this.selectTableauSimple("ecartdate" + champ, ["du", "entre le..."], date.ecartdate)) ;
		//approximation de la date
		var c = "" ;
		if (date.versdebut == 1) c = 'checked' ;
		contenu.push('<input type="checkbox" id="versdebut' + champ + '" name="versdebut' + champ + '[]" ' + this.tabInd() + ' value = "1" ' + c + ' class = "formInput"> ~  ') ;
		//jour
		contenu.push('<input id="jourdebut' + champ + '" name="jourdebut' + champ + '" size="2" value="' + date.jourdebut+'" min="0" max="31"' + this.tabInd() + ' autocomplete="off">') ;
		//mois
		contenu.push('<select ' + this.tabInd() + ' id="moisdebut' + champ + '" name="moisdebut' + champ + '">') ;
		for (var i = 0 ; i < lesmois.length ; i++) {
			if (date.moisdebut == i) {
				contenu.push('<option selected value="' + i + '">' + lesmois[i] + '</option>') ;
			}
			else {
				contenu.push('<option value="' + i + '">' + lesmois[i] + '</option>') ;
			}
		}
		for (var i = 1 ; i < 13 ; i++) {
			contenu.push('<option value="' + i + '">' + i + '</option>') ;
		}
		contenu.push('</select>') ;
		//année
		if (date.andebut == "0" || typeof date.andebut == "undefined") date.andebut = "" ;
		contenu.push('<input type="text" name="andebut' + champ + '" id="andebut' + champ + '" value="' + date.andebut + '" maxsize="20" size="10" ' + this.tabInd() + '><br />') ;
		contenu.push('<span id="ecardate2' + champ + '"> au / et le ... </span> ') ;
		//approximation de la date
		var c = "" ;
		if (date.versfin == 1) c = 'checked' ;
		contenu.push('&nbsp<input type="checkbox" id="versfin' + champ + '" name="versfin' + champ + '[]" ' + this.tabInd() + ' value = "1" ' + c + ' class = "formInput"> ~  ') ;
		//jour
		contenu.push('<input id="jourfin' + champ + '" name="jourfin' + champ + '" size="2" value="' + date.jourfin + '" min="0" max="31"' + this.tabInd() + '>') ;
		//mois
		contenu.push('<select ' + this.tabInd() + ' id="moisfin' + champ + '" name="moisfin' + champ + '">') ;
		for (var i = 0 ; i < lesmois.length ; i++) {
			if (date.moisfin == i) {
				contenu.push('<option selected value="' + i + '">' + lesmois[i] + '</option>') ;
			}
			else {
				contenu.push('<option value="' + i + '">' + lesmois[i] + '</option>') ;
			}
		}
		for (var i = 1 ; i < 13 ; i++) {
			contenu.push('<option value="' + i + '">' + i + '</option>') ;
		}
		contenu.push('</select>') ;
		//année
		if (date.anfin == "0" || typeof date.anfin == "undefined") date.anfin = "" ;
		contenu.push('<input type="text" name="anfin' + champ + '" id="anfin' + champ + '" value="' + date.anfin + '" maxsize="20" size="10" ' + this.tabInd() + '></span>') ;
		return contenu.join("") ;
	},
	
	fdate : function (champ, date) {
		return this.fdate2(champ,date) ;
		/*date = date || {"annee":"", "mois":0, "jour":0, "vers":""} ;
		if (date.vers == 4) {
			if (date.mois == 1) date.mois = 13 ;
			if (date.mois == 4) date.mois = 14 ;
			if (date.mois == 7) date.mois = 15 ;
			if (date.mois == 10) date.mois = 16 ;
			if (date.mois == 12) date.mois = 17 ;
		}
		if (date.vers == 2) date.mois = 19 ;
		if (date.vers == 3) date.mois = 18 ;
		contenu = '<span class="inputOrdinaire">' ; 
		*/
	},
	
	//Liste déroulante à partir d'un tableau préconstitué
	selectTableauSimple : function(champ, tableau, selection) {
		//le tableau est simple quand il comprend une seule dimension et que la clé de chaque valeur est l'indice dans le tableau
		selection = this.testeValeur(champ, selection) || 0 ;
		var contenu = '<select class="inputOrdinaire" name="' + champ + '" ' + this.tabInd() + '><option value="0"></option>' ;
		for (var i = 0 ; i < tableau.length ; i++) {
			if (selection == i) {
				contenu += '<option selected value="' + i + '">' + tableau[i] + '</option>' ;
			}
			else {
				contenu += '<option value="' + i + '">' + tableau[i] + '</option>' ;
			}
		}
		contenu += '</select>' ;
		return contenu ;
	},
	
	selectTableauComplexe : function(champ, tableau, selection) {
		tableau = tableau || [] ;
		//le tableau est complexe quand chacune de ses valeurs est un couple clé / valeur.
		selection = this.testeValeur(champ, selection) || 0 ;
		return '<select class="inputOrdinaire" id="' + champ + '" name="' + champ + '" ' + this.tabInd() + '><option value="0"></option>' + this.rempliOptionsComplexes(tableau, selection) + '</select>'  ;
		//contenu = '<select class="inputOrdinaire" id="' + champ + '" name="' + champ + '" ' + this.tabInd() + '><option value="0"></option>' + this.rempliOptionsComplexes(tableau, selection) + '</select>'  ;
		/*for (var i = 0 ; i < tableau.length ; i++) {
			if (selection == tableau[i].cle) {
				contenu += '<option selected value="' + tableau[i].cle + '">' + tableau[i].valeur + '</option>' ;
			}
			else {
				contenu += '<option value="' + tableau[i].cle + '">' + tableau[i].valeur + '</option>' ;
			}
		}
		contenu += '</select>' ;
		return contenu ;*/
	},
	
	rempliOptionsComplexes(tableau, selection) {
		var contenu = [] ;
		for (var i = 0 ; i < tableau.length ; i++) {
			if (selection == tableau[i].cle) contenu.push('<option selected value="' + tableau[i].cle + '">' + tableau[i].valeur + '</option>') ;
			else contenu.push('<option value="' + tableau[i].cle + '">' + tableau[i].valeur + '</option>') ;
		}
		return contenu.join("") ;
	},

	//Cases à cocher
	checkbox : function (champ, tableau) {
		//tableau est un tableau d'objets de paires {"nom", "id", "selection"}
		contenu = '<table width="100%"><tr>' ;
		var x = 1 ;
		for (var i = 0 ; i < tableau.length ; i++) {
			var c = "" ;
			if (tableau[i].selection == 1) c = 'checked' ;
			contenu += '<td><input type="checkbox" id="' + champ + i + '" name="' + champ + '[]" ' + this.tabInd() + ' ' + c + ' value = "' + tableau[i].id + '" class = "formInput">' + tableau[i].nom  + '</td>' ;
			x++ ;
			if (x == 4) {
				x = 1 ;
				contenu += '</tr><tr>' ;
			}
		}
		contenu += '</tr></table>' ;
		return contenu ;
	},
	
	//Radio
	radio : function (champ, tableau, selection, taille) {
		selection = this.testeValeur(champ, selection) || 0 ;
		taille = taille || 4 ;
		contenu = '<table width="100%"><tr>' ;
		var x = 1 ;
		for (var i = 0 ; i < tableau.length ; i++) {
			var c = "" ;
			if (i == selection) c = 'checked' ;
			contenu += '<td><input type="radio" id="' + champ + i + '" name="' + champ + '" ' + this.tabInd() + ' ' + c + ' value = "' + i+ '" class = "formInput">' + tableau[i]  + '</td>' ;
			x++ ;
			if (x == taille) {
				x = 1 ;
				contenu += '</tr><tr>' ;
			}
		}
		contenu += '</tr></table>' ;
		return contenu ;
	},
	
	geographique : function() {
		//on suppose que les champs s'appellent lattitude et longitude
		//les paramètres lattitude et longitude sont des valeurs exprimées en décimales
		var lat = this.testeValeur("latitude") ;
		if (eval(lat) == 0) lat = "0" ;
		var lon = this.testeValeur("longitude") ;
		if (eval(lon) == 0) lon = "0" ;
		return this.input("latitude", lat, 10) + " de latitude, "  + this.input("longitude", lon, 10) + " de longitude" ;
	},

	tableau:function(objet, condition, tableau) {
		var url = localisation + 'ajax/o_ajax_content.php?objet=' + objet + '&' + condition ;
		var ter = new XMLHttpRequest() ;
		ter.open('POST', url, false) ;
		ter.send() ;
		rep = ter.responseText.evalJSON() ;
		rep = rep[objet] ;
		this[tableau] = new Array() ;
		for (var i = 0 ; i < rep.length ; i++) {
			this[tableau].push(rep[i]) ;
		}
	},
	
	tableauEvenement:function(cotable, critere, tableau) {
		var url = localisation + 'ajax/requestEvents.php' ;
		var ter = new XMLHttpRequest() ;
		ter.open('POST', url, false) ;
		ter.setRequestHeader("Content-type", "application/x-www-form-urlencoded") ;
		ter.send("cotable=" + cotable + "&critere=" + critere) ;
		rep = ter.responseText.evalJSON() ;
		this[tableau] = new Array() ;
		for (var i = 0 ; i < rep.length ; i++) {
			this[tableau].push(rep[i]) ;
		}
	}
} ;

fermeFormulaire = function() {
	//ferme un formulaire ouvert
	$('edition').fade() ;
	$('edition').innerHTML = '' ;
} ;
