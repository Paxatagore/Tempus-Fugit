/* arbre généalogique */

arbre = {
	"init":0,
	"personne":0,
	"profondeur":6,
	"profondeurAffichee":3,
	
	initialise : function() {
		//initialise l'affichage
		this.init = 1 ;
		return this.init ;
	},
	
	requete : function(personne) {
		//lance le processus pour rechercher et afficher un arbre généalogique.
		if (this.init == 0) this.initialise() ;
		CTFA.enTete.changeBloc(7) ;
		$('lesPersonnes').style.display = "" ;				//au cas où, on ferme l'affichage de LesPersonnes (on appelle généralement l'arbre depuis la liste des personnes
		$('arbreAscendants').innerHTML = "" ;				//on vide le contenu			
		$('arbreDescendants').innerHTML = "" ;
		$('arbreAscendants3').innerHTML = "" ;				
		$('lesArbres').style.display = "block" ;			//on affiche la partie les arbres
		
		this.personnes = [] ;								//on vide les données
		this.personne = personne ;
		//on lance la requête AJAX
		new Ajax.Request(localisation + 'ajax/requestGenealogie.php',
			{"method" : "post",
			"postBody" : "personne=" + personne + "&profondeur=" + this.profondeur ,			//les deux paramètres sont la personne concernée et la profondeur
			"onSuccess": function(requester) {
				arbre.data = requester.responseJSON ;
				arbre.ancetres 	= arbre.data.ancetres ;											//les ancêtres
				arbre.afficheNomPersonne() ;													//affiche le nom de la personne dont on sort l'arbre, dans le titre
				//$('arbreAscendants').innerHTML = arbre.afficheChaine(arbre.data.ancetres[0], 0) ;	//affiche l'arbre des ancêtres
				arbre.SVGsetArbreAscendant(arbre.data.ancetres[0]) ;
				$('arbreDescendants').innerHTML = arbre.afficheChaine(arbre.data.enfants[0]) ;	//affiche l'arbre des descendants
				$('conteneurGenealogie').scrollTo() ;											//se positionne au début du conteneur de généalogie
			}}
		) ;
	},
	
	afficheProfondeur : function() {
		//fonction qui affiche la profondeur de calcul
		$('arbreProfondeur').innerHTML = this.profondeur ;
		return 1 ;
	},
	
	fixeProfondeur : function(e, n) {
		//permet de modifier de n la profondeur de calcul
		this.profondeur += n ;
		if (this.profondeur < 1) this.profondeur = 1 ;
		this.AfficheProfondeur() ;
		this.requete(this.personne) ;	//on relance le calcul
	},
	
	afficheNomPersonne : function() {
		//affiche le nom de la personne dont on affiche l'arbre généaologique, dans la partie de titre de la page
		var nom = this.ancetres[0].personne.nom ;
		var pl = nom.slice(0,1).toLowerCase() ;
		var chaine = "de " + nom ;
		if (["a", "e", "i", "o", "u", "y"].indexOf(pl) > -1) var chaine = "d'" + nom ;
		$('arbreNom').innerHTML = chaine ;
		return 1 ;
	},
	
	afficheChaine : function(oJson, n) {
		//affiche un arbre
		var chaine = "" ;
		n = n || 0 ;
		chaine += this.affichePersonne(oJson.personne, n)  ;
		if (oJson.hasOwnProperty("parents")) {
			var affiche = "" ;
			if (n > this.profondeurAffichee) affiche = 'style="display:none"' ;
			chaine += '<div ' + affiche + '>' ;
			for (var i = 0 ; i < oJson.parents.length ; i++) {
				chaine += this.afficheChaine(oJson.parents[i], n+1) ;
			}
			chaine += '</div>' ;
		}
		return chaine ;
	},
	
	affichePersonne : function(p, n) {
		var left = 35 * (n +1) ;
		var chaine = '<div id="blocAscendant' + p.num + '" onClick="arbre.ascendantsFerme(' + p.num + ')"><table class="genTable"><tr><td class="genTableImage" width="' + left + 'px">' ;
		//image
		var url = "../img/homme.jpg" ;
		if (p.url != "") url = p.url ;
		else if (p.sexe == "f") url = "../img/femme.jpg" ;
		chaine += '<img class="uneImage genImage" src="' + url + '" /></td>' ;
		//texte
		chaine += '<td><span class="nom">' + p.nom + '</span> ' ;
		//date de naissance
		var datenaissance = "" ;
		if (p.annaissance != 0 && p.anmort != 0) {
			datenaissance = '(' + p.annaissance + ' - ' + p.anmort + ')' ;
		}
		chaine += datenaissance ;
		if (p.notice != "") chaine += ', <span class="genInformation" title="' + p.notice + '">' +  p.notice + '</span>' ;
		chaine += "</td></tr></table></div>" ;
		return chaine ;
	},
	
	ascendantsFerme : function(n) {
		var e = $("blocAscendant"+n).nextSiblings() ;
		if (e) {
			if (e[0].style.display == "none") e[0].style.display = "block" ;
			else e[0].style.display = "none" ;
		}
	},
	
	ferme : function(e) {
		console.log(e) ;
		$('arbreGenealogique').innerHTML = "" ;
		$('conteneurGenealogie').style.display = "" ;
	},
	
	dessineAscendant:function(ancetres) {
		console.log("Dessinons les ascendants.") ;
		arbre.dessinePersonne("arbrePersonne", 432, 612, 144, 216, ancetres.personne) ;
	},
	
	SVGsetArbreAscendant:function(ancetres) {
		console.log("Dessinons les ascendants.") ;
		var chaine = "" ;
		arbre.lo = [] ; 		//liste des personnes sur lesquelles mettre un observer
		//defs
		chaine = '<svg id = "arbreascsv" xmlns="http://www.w3.org/2000/svg" version="1.1"><defs><g id="lignesAGPGP"><line x1="0" y1="0" x2="0" y2="18" class="SVGligne" /><line x1="108" y1="0" x2="108" y2="18" class="SVGligne" /><line x1="0" y1="18" x2="108" y2="18" class="SVGligne" /><line x1="54" y1="18" x2="54" y2="36" class="SVGligne" /></g><g id="lignesGPP"><line x1="0" y1="0"	x2="0" y2="18" class="SVGligne" /><line x1="252" y1="0" x2="252" y2="18" class="SVGligne" /><line x1="0" y1="18" x2="252" y2="18" class="SVGligne" /><line x1="126" y1="18" x2="126" y2="36" class="SVGligne" /></g></defs>' ;
		//console.log(chaine) ;
		chaine += arbre.dessinePersonne("arbrePersonne", 432, 612, 144, 216, ancetres.personne) ;
		
		//tri
		if (ancetres.parents[0]) {
			if (ancetres.parents[0].parents[0]) {
				if (ancetres.parents[0].parents[1] && ancetres.parents[0].parents[1].personne.sexe == "h") {
					var p = ancetres.parents[0].parents[1] ;
					ancetres.parents[0].parents[1] = ancetres.parents[0].parents[0] ;
					ancetres.parents[0].parents[0] = p ;
				}
			}
			if (ancetres.parents[1]) {
				//on a père et mère
				if (ancetres.parents[1].personne.sexe == "h") {
					var p = ancetres.parents[1]
					ancetres.parents[1] = ancetres.parents[0] ;
					ancetres.parents[0] = p ;
				}
			}
		}
					
		
		//lignée parentale 1
		if (ancetres.parents[0]) {
			chaine += arbre.dessinePersonne("p1", 180, 360, 144, 216, ancetres.parents[0].personne, 0) ;
			if (ancetres.parents[0].parents[0]) {
				chaine += arbre.dessinePersonne("gp1", 72, 180, 108, 144, ancetres.parents[0].parents[0].personne, 0) ;
				if (ancetres.parents[0].parents[0].parents[0]) {
					//arrières grands parents
					chaine += arbre.dessinePersonne("agp1", 36, 36, 72, 108, ancetres.parents[0].parents[0].parents[0].personne, 0) ;
					if (ancetres.parents[0].parents[0].parents[1]) {
						chaine += arbre.dessinePersonne("agp1", 144, 36, 72, 108, ancetres.parents[0].parents[0].parents[1].personne, 0) ;
						chaine += '<use xlink:href="#lignesAGPGP" x="72" y="144" />' ;
					}
					else {
						//il manque AGP2
						chaine += arbre.SVGrelie2Boite(36, 72, 144, 72, 108, 180) ;
					}
				}
				//grand-mère paternelle
				if (ancetres.parents[0].parents[1]) {
					chaine += arbre.dessinePersonne("gp2", 324, 180, 108, 144, ancetres.parents[0].parents[1].personne, 0) ;
					chaine += '<use xlink:href="#lignesGPP" x="126" 	y="324" width="252" height="36"/>' ;
					//arrières grands parents
					if (ancetres.parents[0].parents[1].parents[0]) {
						chaine += arbre.dessinePersonne("agp3", 288, 36, 72, 108, ancetres.parents[0].parents[1].parents[0].personne, 0) ;
						if (ancetres.parents[0].parents[1].parents[1]) {
							chaine += arbre.dessinePersonne("agp4", 396, 36, 72, 108, ancetres.parents[0].parents[1].parents[1].personne, 0) ;
							chaine += '<use xlink:href="#lignesAGPGP" x="324" y="144" />' ;
						}
						else {
							//il manque AGP4
							chaine += arbre.SVGrelie2Boite(288, 72, 144, 324, 108, 180) ;
						}
					}
				}
				else {
					//il manque GP2
					chaine += arbre.SVGrelie2Boite(108, 108, 324, 180, 144, 360) ;
				}
			}
			//lignée parentale 2
			//console.log(chaine) ;
			if (ancetres.parents[1]) {
				chaine += arbre.dessinePersonne("p2", 684, 360, 144, 216, ancetres.parents[1].personne, 0) ;
				chaine += '<line x1="252" y1="576" x2="252" y2="594" class="SVGligne" /><line x1="756" y1="576" x2="756" y2="594" class="SVGligne" /><line x1="252" y1="594" x2="756" y2="594" class="SVGligne" /><line x1="504" y1="594" x2="504" y2="612" class="SVGligne" />' ;
				//grand père maternel
				if (ancetres.parents[1].parents[0]) {
					chaine += arbre.dessinePersonne("gp3", 576, 180, 108, 144, ancetres.parents[1].parents[0].personn, 0) ;
					if (ancetres.parents[1].parents[0].parents[0]) {
						//arrières grands parents
						chaine += arbre.dessinePersonne("agp5", 540, 36, 72, 108, ancetres.parents[1].parents[0].parents[0].personne, 0) ;
						if (ancetres.parents[1].parents[0].parents[1]) {
							chaine += arbre.dessinePersonne("agp6", 648, 36, 72, 108, ancetres.parents[1].parents[0].parents[1].personne, 0) ;
							chaine += '<use xlink:href="#lignesAGPGP" x="576" y="144" />' ;
						}
						else {
							chaine += arbre.SVGrelie2Boite(540, 72, 144, 576, 108, 180) ;
						}
					}
				}
				//grand-mère maternelle
				if (ancetres.parents[1].parents[1]) {
					chaine += arbre.dessinePersonne("gp4", 828, 180, 108, 144, ancetres.parents[1].parents[1].personne, 0) ;
					chaine += '<use xlink:href="#lignesGPP" x="630" y="324" width="252" height="36"/>' ;
					//arrières grands parents
					if (ancetres.parents[1].parents[1].parents[0]) {
						chaine += arbre.dessinePersonne("agp7", 792, 36, 72, 108, ancetres.parents[1].parents[1].parents[0].personne, 0) ;
						if (ancetres.parents[1].parents[1].parents[1]) {
							chaine += arbre.dessinePersonne("agp8", 900, 36, 72, 108, ancetres.parents[1].parents[1].parents[1].personne, 0) ;
							chaine += '<use xlink:href="#lignesAGPGP" x="828" y="144" />' ;
						}
						else {
							chaine += arbre.SVGrelie2Boite(792, 72, 144, 828, 108, 180) ;
						}
					}
				}
				else {
					//il manque GP4
					chaine += arbre.SVGrelie2Boite(576, 108, 324, 684, 144, 360) ;
				}
			}
			else {
				//on n'a que la parent 1 et non le parent 2
				chaine += arbre.SVGrelie2Boite(180, 144, 576, 432, 144, 612) ;
			}
		}
		chaine += '</svg>' ;
		//console.log(chaine) ;
		$('arbreAscendants3').innerHTML = chaine ;
		console.log("Processus d'affichage de l'arbre des ascendants terminé.") ;
	},
	
	SVGSetBoite:function(id, x, y, l, h) {
		var chaine = '' ;
		chaine = '<rect id="' + id + '" x="' + x + '" y="' + y + '" width="' + l + '" height="' + h + '" class="SVGligne" />' ;
		return chaine ;
	},
	
	SVGrelie2Boite:function(x1, l1, y1, x2, l2, y2, couleur) {
		//dresse une ligne entre la boite dont le bas est (x1, y) vers le haut de la boite (x2, y2)
		//delta = delta || 0 ;		//delta est destiné à établir une différence entre les lignes susceptibles de se croiser
		couleur = couleur || 4 ;
		var xpd = l1 / 2 + x1 ;		//coordonnées x du point de départ : à la moitié de la boite 
		var xpa = l2 / 2 + x2 ;		//coordonnées x du point d'arrivée : à la moitié de la boite 
		var ym	= (y2 - y1) / 2 + y1  ;//coordonnées y de la moitié du chemin
		
		if (x1 == x2 && l1 == l2) {
			//pas besoin de "décroché"
			return '<polyline points = "'+ xpd + ', '+ y1 + ', '+ xpa + ', '+ y2 + '" class="SVGligne" />' ; 
		}
		else {
			return '<polyline points = "'+ xpd + ', '+ y1 + ', '+ xpd + ', '+ ym + ', '+ xpa + ', '+ ym + ', '+ xpa + ', '+ y2 + '" class="SVGligne SVGligneCouleur' + couleur + '" />' ; 
		}
	},
	
	dessinePersonne:function(boite, x, y, l, h, p, mode) {
		console.log("J'affiche ") ;
		if (p.hasOwnProperty("type") && p.type == "dynastie") {
			//en fait, on affiche une dynastie...
			var d = lesDynasties.getById(p.dynastie) ;
			p.nom = d.nom || "" ;
		}
		console.log(p) ;
		//p est un objet de type personne
		var chaine = "" ;
		chaine += arbre.SVGSetBoite(boite, x, y, l, h) ;
		var xx = x+l/2 ;
		var yy = y + h * 3/4 ;
		var yy2 = yy + 15 ;
		var ll = l - 10 ;
		var naa = lesPersonnes.stripNM(p) ;		//naa : nom à afficher
		var size = 12 ;
		if (naa.length > l * 0.15) size = 10 ;
		if (naa.length > l * 0.18) size = 9 ;
		if (naa.length > l * 0.21) size = 8 ;
		//dynastie
		if (p.dynastie > 0) {
			var d = lesDynasties.getById(p.dynastie) ;
			if (d.couleur != "") {
				xd1 = x ;
				yd1 = y ;
				xd2 = x + 12 ;
				yd2 = y + 12 ;
				chaine += '<polyline points = "'+ xd1 + ', '+ yd1 + ', '+ xd2 + ', '+ yd1 + ', '+ xd1 + ', '+ yd2 + ', '+ xd1 + ', '+ yd1 + '" style="fill:#' + d.couleur + ' ;stroke-linejoin:round;stroke:#'+d.couleur+';stroke-width:2"/ title="'+d.nom+'" />' ; 
			}
			

		}
		//nom
		if (mode == 0) {
			var subfct = 'arbre.deplace' ;
		}
		else {
			var subfct = 'arbreDynastique.affichePersonne' ;
		}
		chaine += '<text onClick="' + subfct + '(' + p.num + ')" font-size="' + size + '" id="arbreAsc' + p.num + '" x="' + xx + '" y="' + yy + '" text-anchor="middle" textLenght = "' + ll +'" lengthAdjust="spacingAndGlyphs">' + naa + '</text>' ;
		//dates de naissance et de mort
		if (p.hasOwnProperty("annaissance")) {
			chaine += '<text onClick="' + subfct + '(' + p.num + ')" font-size="10" x="' + xx + '" y="' + yy2 + '" text-anchor="middle" textLenght = "' + ll +'" lengthAdjust="spacingAndGlyphs">' + lesPersonnes.afficheDates(p) + '</text>' ;
		}
		//photo 
		if (p.hasOwnProperty("url") && p.url != "") {
			var xi = x + 5 ;
			var yi = y + 5 ;
			var li = l-10 ;
			var hi = h / 2 * 1.2  ;
			chaine += '<image xlink:href="' + p.url + '" x="'+xi+'" y="'+yi+'" width="'+li+'" height="'+hi+'" />' ;
		}
		//console.log(chaine) ;
		return chaine ; 
	},
	
	deplace:function(num) {
		console.log("On passe à l'arbre de : " + num) ;
		arbre.requete(num) ;
	}
} ;

/* arbre dynastique */

arbreDynastique = {

	modeVerbeux:0,

	requete : function(dynastie) {
		this.dynastie = dynastie ;
		//lance le processus pour rechercher et afficher un arbre dynastique.
		CTFA.enTete.changeBloc(8) ;
		$('lesDynasties').style.display = "" ;			//on ferme l'affichage des dynasties (pour le cas où il était ouvert)
		$('lesPersonnes').style.display = "" ;			//idem
		$('arbreDynastiqueContenu').innerHTML = "" ;	//on vide le contenu			
		$('lesArbreDynastique').style.display = "block" ;			//on affiche la partie les arbres
		var qd = "" ;
		var noms = "" ;
		for (var i = 0 ; i < this.dynastie.length ; i++) {
			qd += "dynastie[]=" + this.dynastie[i] + "&" ;
			noms += lesDynasties.getById(this.dynastie[i]).nom + ", " ;
		}
		this.personnes = [] ;								//on vide les données
		noms = noms.slice(0,-2) ;
		$('arbreDynastiqueNom').innerHTML = noms ;
		//on lance la requête AJAX
		new Ajax.Request(localisation + 'ajax/requestGenealogieDynastie.php', {
			"method" : "post",
			"postBody" : qd + "modeVerbeux=0",
			"onSuccess": function(requester) {
				arbreDynastique.data = requester.responseJSON ;
				arbreDynastique.affiche(arbreDynastique.data.arbres) ;
			}}
		) ;
	},
	
	affiche:function(arbres) {
		var chaines = "" ;
		for (var i = 0 ; i < arbres.length ; i++) {
			this.listeBoites = Array() ;		//on redéfinit la liste des boîtes
			var chaine = '' ;
			var largeurMax = 180 ;
			//dans un premier temps, on déroule les générations pour connaître nmax, le nombre maximum d'individus sur une génération donnée
			var nmax = 1 ;
			for (var j = 0 ; j < arbres[i].ngeneration ; j++) {
				nmax = Math.max(nmax, arbres[i].generations[j].npersonnes) ;
			}
			//var largeurMax = Math.min(144, 1024 / nmax) ; 		//on en déduit largeurMax, la largeur maximale d'une case.
			var largeurtotale = largeurMax * nmax ;
			if (largeurtotale < 1024) largeurtotale = 1024 ;
			//ensuite, on déroule les générations une seconde fois, cette fois pour afficher les boîtes de personnages
			this.y = 20 ;	//on se place à y = 20
			for (var j = 0 ; j < arbres[i].ngeneration ; j++) {
				var npersonnes = arbres[i].generations[j].npersonnes ;	//cette génération compte npersonnes
				var espaceVide = (largeurtotale - (largeurMax * npersonnes)) ;// ce qui permet de calculer l'espace à laisser libre
				var espacement = espaceVide / (npersonnes + 1) ;	//et l'espacement entre personnes
				this.maxHauteur = 144 ;
				for (var k = 0 ; k < npersonnes ; k++) {			//on fait ensuite défiler chacune de ces personnes
					var laPersonne = arbres[i].generations[j].personnes[k] ;	//la personne
					var x = espacement * (k+1) + largeurMax * k ;	//la boite va se situer à (x,y)
					chaine += arbreDynastique.dessinePersonne("SVGpersonne" + laPersonne.num, x, largeurMax * 0.8, largeurMax * 1.2, laPersonne, 1) ;
					this.maxHauteur = Math.max(this.maxHauteur, this.yy-this.y); ;
					this.listeBoites[laPersonne.num] = {"num":laPersonne.num, "x":x, "y":this.y, "hauteur":largeurMax * 1.2, "largeur":largeurMax * 0.8} ;
				}
				this.y += this.maxHauteur + 30 ;	//à chaque génération, on décale y vers le bas
				if (this.modeVerbeux == 1) console.log("Génération : " + j + " - Max H " + this.maxHauteur) ;
				//on refait les personnes, pour afficher maintenant les boîtes
				for (var k = 0 ; k < npersonnes ; k++) {	
					var laPersonne = arbres[i].generations[j].personnes[k] ;	//la personne
					if (this.modeVerbeux == 1) console.log("J'essaie de dessiner le rectangle de " + laPersonne.num) ;
					//chaine += this.SVGRectangle(laPersonne.num, 'onClick="arbreDynastique.affichePersonne('+laPersonne.num+')"') ;
					chaine += this.SVGRectangle(laPersonne.num, 'onClick="donnees.lienPersonne2('+laPersonne.num+')"') ;
				}
			}
			if (this.modeVerbeux == 1) console.log("Les rectangles ont été affichés.") ;
			//on redéroule ensuite les générations, à l'envers cette fois, pour afficher les liens
			for (var j = arbres[i].ngeneration - 1 ; j > 0 ; j--) {
				this.ym = 0 ;
				for (var k = 0 ; k < arbres[i].generations[j].npersonnes ; k++) {
					var laPersonne = arbres[i].generations[j].personnes[k] ;
					chaine += arbreDynastique.SVGrelie2Boite(laPersonne.pere1, laPersonne.num) ;
				}
			}
			var hauteur = largeurMax * arbres[i].ngeneration * 1.6 ;	//hauteur est la hauteur totale de l'image.
			
			//chaines += '<div class="arbreSousLegende">Branche de ' + arbres[i].generations[0].personnes[0].nom + '</div><svg id = "arbreascsv" xmlns="http://www.w3.org/2000/svg" version="1.1" width="1024" height="' + this.y +'">' + chaine + '</svg>' ;
			chaines += '<div class="arbreSousLegende">Branche de ' + arbres[i].generations[0].personnes[0].nom + '</div><svg id = "arbreascsv" xmlns="http://www.w3.org/2000/svg" version="1.1" width="' + largeurtotale + '" height="' + this.y +'">' + chaine + '</svg>' ;
		}
		$('arbreDynastiqueContenu').innerHTML = chaines ;
	},
	
	affichePersonne:function(p) {
		donnees.sendData(p, "personne") ;
	},
	
	afficheDynastie:function(d) {
		dynastie = this.dynastie ;
		dynastie.push(d) ;
		arbreDynastique.requete(dynastie) ;
	},
	
	SVGrelie2Boite:function(pere, fils) {
		//dresse une ligne entre la boite pere et la boite fils
		var boitePere 	= this.listeBoites[pere] ;
		var boiteFils	= this.listeBoites[fils] ;
		if (typeof boitePere == "undefined") return "" ;
		if (typeof boiteFils == "undefined") return "" ;
		//console.log("Je relie les boîtes suivantes :") ;
		//console.log(boitePere) ;
		//console.log(boiteFils) ;
		var xpd = boitePere.x + boitePere.largeur / 2	 ;		//coordonnée x du point de départ : à la moitié de la boite père
		var ypd = boitePere.y + boitePere.hauteur ;				//coordonnée y du point de départ : en bois de la boite père
		var xpa = boiteFils.x + boiteFils.largeur/ 2;			//coordonnée x du point d'arrivée : en haut de la boite fils, à la moitié
		var ypa	= boiteFils.y ;									//coordonnée y du point d'arrivée : en haut de la bois fils
		if (this.ym == 0) {
			this.ym = (ypa - ypd) / 2 + ypd  ;						//coordonnée y de la moitié du chemin
		}
		
		//possibilités de problème
		//cas 1
		if (this.xhaut < xpd && this.xbas >= xpd && xpd < xpa) {
			if (this.modeVerbeux == 1) console.log("Problème - CAS 1. Je pense.") ;
			this.ym = this.ym - 4 ;
		}
		//cas 2
		if (xpd > this.xhaut && xpa <= this.xhaut) {
			if (this.modeVerbeux == 1) console.log("Problème - CAS 2. Je pense.") ;
			this.ym = this.ym + 4 ;
		}
		
		//on aligne les nouveaux xhaut et xbas
		this.xhaut = xpd ;
		this.xbas = xpa ;
		if (ypd == ypa) {
			//pas besoin de "décroché"
			return '<polyline points = "'+ xpd + ', '+ ypd + ', '+ xpa + ', '+ ypa + '" class="SVGligne" />' ; 
		}
		else {
			return '<polyline points = "'+ xpd + ', '+ ypd + ', '+ xpd + ', '+ this.ym + ', '+ xpa + ', '+ this.ym + ', '+ xpa + ', '+ ypa + '" class="SVGligne" />' ; 
		}
	},

	dessinePersonne:function(boite, x, l, h, p, mode) {
		//p est un objet de type personne
		//var fonction = 'onClick="arbreDynastique.affichePersonne('+p.num+')"' ;
		var fonction = 'onClick="Requete.lienPersonne2('+p.num+')"' ;
		if (p.nom == "Dynastie") {
			//en fait, on affiche une dynastie...
			var d = lesDynasties.getById(p.dynastie) ;
			p.nom = d.nom || "" ;
			fonction = 'onClick="arbreDynastique.afficheDynastie('+p.dynastie+')"' ;
		}
		if (this.modeVerbeux == 1) {
			console.log("J'affiche ") ;
			console.log(p) ;
		}
		var unitY = h / 9 ;
		this.yy = this.y + 10 ;	//compteur y interne
		var xx = x + l/2 ;	//positionnement x de l'affichage des chaines (par centrage)
		var ll = l - 10 ;
		var chaine = "" ;
		//photo 
		if (p.hasOwnProperty("url") && p.url != "") {
			var xi = x + 5 ;
			var li = l - 10 ;
			var hi = unitY * 3.7 ;
			chaine += '<image xlink:href="' + p.url + '" x="'+xi+'" y="' + this.yy + '" width="' + li + '" height="' + hi + '" />' ;
			this.yy = this.yy + hi + 10 ;
		}
		else {
			this.yy += 15 ;
		}
		//nom
		this.yy += 5 ;
		var naa = lesPersonnes.stripNM(p) ;	//naa : nom à afficher
		chaine += this.SVGAfficheTexte(naa, xx, ll, fonction) ;
		//date de naissance
		if (p.hasOwnProperty("annaissance")) {
			naa = lesPersonnes.afficheDates(p) ;
			chaine += this.SVGAfficheTexte(naa, xx, ll, fonction) ;
		}
		//fonctions
		if (p.hasOwnProperty("fonctions") && p.fonctions.length > 0 ) {
			for (var i = 0 ; i < p.fonctions.length ; i++) {
				var txtfct = lesFonctions.getById(p.fonctions[i].fonction).nom ;
				chaine += this.SVGAfficheTexte(txtfct, xx, ll, fonction) ;
				var txtfct2 = '(' + p.fonctions[i].andebut + '-' + p.fonctions[i].anfin + ')' ;
				chaine += this.SVGAfficheTexte(txtfct2, xx, ll, fonction) ;
			}
		}
		//époux
		if (p.hasOwnProperty("epoux") && p.epoux.length > 0 ) {
			for (var i = 0 ; i < p.epoux.length ; i++) {
				chaine += this.SVGAfficheTexte("ép : " + p.epoux[i].nom, xx, ll, 'onClick="Requete.lienPersonne2(' + p.epoux[i].num + ')"') ;
			}
		}
		var couleur = "" ;
		if (p.dynastie > 0) {
			var d = lesDynasties.getById(p.dynastie) ;
			if (d.couleur != "") {
				couleur = 'stroke:'+d.couleur ;
				if (p.dynastie > 0) {
					xd1 = x ;
					yd1 = this.y ;
					xd2 = x + 15 ;
					yd2 = this.y + 15 ;
					chaine += '<polyline points = "'+ xd1 + ', '+ yd1 + ', '+ xd2 + ', '+ yd1 + ', '+ xd1 + ', '+ yd2 + ', '+ xd1 + ', '+ yd1 + '" style="fill:#' + d.couleur + ' ;stroke-linejoin:round;stroke:#'+d.couleur+';stroke-width:2"/ title="'+d.nom+'" />' ; 
				}
			}
		}
		//chaine += '<rect x="' + x + '" y="' + this.y + '" width="' + l + '" height="' + h + '" class="SVGligne" />' ; 
		return chaine ; 
	},
	
	SVGRectangle:function(nBoite, fonction) {
		this.listeBoites[nBoite].hauteur = this.maxHauteur ;
		fonction = fonction || "" ;
		return '<rect ' + fonction + ' x="' + this.listeBoites[nBoite].x + '" y="' + this.listeBoites[nBoite].y + '" width="' + this.listeBoites[nBoite].largeur + '" height="' + this.listeBoites[nBoite].hauteur + '" class="SVGligne" />' ; 
	},
	
	SVGTailleAfficheTexte:function(chaine, largeurBoite) {
		//calcul de la bonne taille d'affichage
		var taille = 12 ;
		if (chaine.length > largeurBoite * 0.15) taille = 10 ;
		if (chaine.length > largeurBoite * 0.18) taille = 9 ;
		if (chaine.length > largeurBoite * 0.21) taille = 8 ;
		if (chaine.length > largeurBoite * 0.24) taille = 6 ;
		return taille ;
	},
	
	SVGAfficheTexte:function(texte, x, l, fonction) {
		fonction = fonction || "" ;
		var taille = this.SVGTailleAfficheTexte(texte, l) ;	//la taille de l'affichage
		if (taille < 8) {
			//le texte est trop petit => on essaie de le faire tenir sur deux lignes
			var lt = texte.length ;
			var michaine = Math.round(lt/2) ;
			if (texte[michaine] == ' ') {
				txt1 = texte.slice(0, michaine) ;
				txt2 = texte.slice(michaine) ;
			}
			else {
				var premierEspace = 0 ;
				var deuxiemeEspace = 0 ;
				var pas = 1 ;
				var txt1 = "" ;
				var txt2 = "" ;
				console.log(texte) ;
				do {
					if (texte[michaine-pas] == " ") {
						var premierEspace = michaine-pas ;
						txt1 = texte.slice(0, premierEspace) ;
						txt2 = texte.slice(premierEspace) ;
						
					}
					if (texte[michaine+pas] == " ") {
						var deuxiemeEspace = michaine+pas ;
						txt1 = texte.slice(0, deuxiemeEspace) ;
						txt2 = texte.slice(deuxiemeEspace) ;
					}
					pas++ ;
				}
				while (txt1 == "" && pas < michaine) ;
				if (txt1 == "") {
					txt1= texte.slice(0, michaine) ;
					txt2= texte.slice(michaine+1) ;
				}
			}
			txt1 = txt1.trim() ;
			txt2 = txt2.trim() ;
			var chaine = this.SVGAfficheTexte(txt1, x, l, fonction) ;
			chaine += this.SVGAfficheTexte(txt2, x, l, fonction) ;
			console.log("Voilà.") ;
		}
		else {
			taille = 10 ;
			var chaine = '<text ' + fonction + ' font-size="' + taille + '" x="' + x + '" y="' + this.yy + '" text-anchor="middle" textLenght = "' + l +'" lengthAdjust="spacingAndGlyphs">' + texte + '</text>' ;
			this.yy = this.yy + taille + 4 ;
		}
		return chaine ; 
	}
}
