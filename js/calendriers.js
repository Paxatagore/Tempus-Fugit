lesmois 			= new Array("", "janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre", "hiver", "printemps", "été", "automne", "hiver", "décennie", "siècle", "début du siècle", "milieu du siècle", "fin du siècle", "début", "mi-", "fin") ;
lesSaisons 			= new Array("", "hiver", "hiver", "hiver", "printemps", "printemps", "printemps", "été", "été", "été", "automne", "automne", "hiver", "", "", "", "", "", "", "", "", "", "", "début") ;

/* opérations élémentaires */

function annee(d, e) {
	//d peut être indifféremment un objet construit possédant un attribut année ou un entier (une année).
	if (d.hasOwnProperty("annee")) d = d.annee ;
	
	//dates préhistoriques
	if (d < -999999999) {
		//milliards
		return Math.abs(Math.floor(d / 100000000)) / 10 + " milliards d'années BP" ;
	}
	//- avant 1 million d'année
	if (d < -999999) {
		//dates antérieures à 1 MA avant le présent 
		return Math.abs(Math.floor(d / 100000)) / 10 + " millions d'années BP" ;
	}
	if (d < - 12000) {
		//dates supposées être "BP"
		return Math.abs(Math.floor(d / 1000)) + " " + Math.abs(d % 1000) + "0".times(3-String.interpret(d % 1000).length) + " BP" ;
	}
	
	if (d > cetteAnnee) return "à ce jour" ;
	if (d < 0) return ' ' + lienStructure(-d + " av. JC", "requete", d, e.num) ;
	else {
		return ' ' + lienStructure(d, "requete", d, e.num) ;
	}
}

function vers(d) {
	//d doit être un objet possédant au moins un attribut année et un attribut vers
	if (d.vers == 2) {
		var siecle = Math.floor(d.annee / 100) ;
		if (siecle > 0) chaine = siecle + "e siècle" ;
		else chaine = Math.abs(siecle) + "e siècle av JC" ;
		return chaine ;
	}
	if (d.vers == 3) {
		return "décennie " + Math.floor(d.annee / 10) * 10 ;
	}
	if (d.vers == 1 || d.annee < -800) {	//par principe, toutes les dates antérieures à -800 sont supposées être imprécises. Le "vers" est donc affiché par principe.
		return "v. " ;
	}
	return "" ;
}

function jour(j) {
	//j peut être indifféremment un objet construit possédant un attribut jour ou un entier (un jour).
	if (j.hasOwnProperty("jour")) j = j.jour ;
	if (j != 0) {
		if (j == 1) return '1er ' ;
		else return j + ' '  ;
	}
	else return "" ;
}

function mois(d) {
	//d est un objet comprenant un attribut vers et un attribut mois.
	if (d.vers == 4) return " " + lesSaisons[d.mois] ;
	else return " " + lesmois[d.mois] ;
}

function afficheunedate(uneDate, e) {
	var chaine = "" ;
	//dates préhistoriques
	if (uneDate.annee < -999999999) {
		//milliards
		chaine = Math.abs(Math.floor(uneDate.annee / 100000000)) / 10 + " milliards d'années BP" ;
		return chaine ;
	}
	//- avant 1 million d'année
	if (uneDate.annee < -999999) {
		//dates antérieures à 1 MA avant le présent 
		chaine = Math.abs(Math.floor(uneDate.annee / 100000)) / 10 + " millions d'années BP" ;
		return chaine ;
	}
	if (uneDate.annee < - 12000) {
		//dates supposées être "BP"
		chaine = Math.abs(Math.floor(uneDate.annee / 1000)) + " " + Math.abs(uneDate.annee % 1000) + "0".times(3-String.interpret(uneDate.annee % 1000).length) + " BP" ;
		return chaine ;
	}
	if (uneDate.annee > cetteAnnee) return "à ce jour" ;
	if (uneDate.vers == 2) {
		var siecle = Math.floor(uneDate.annee / 100) ;
		if (siecle > 0) chaine = siecle + "e siècle" ;
		else chaine = Math.abs(siecle) + "e siècle av JC" ;
		return chaine ;
	}
	if (uneDate.vers == 3) {
		var decenie = Math.floor(uneDate.annee / 10) * 10 ;
		chaine = "décennie " + decenie ;
		return chaine ;
	}
	if (uneDate.vers == 1 && uneDate.annee > -800) {	//par principe, toutes les dates antérieures à -800 sont supposées être imprécises. Le "vers" n'est donc pas affiché.
		chaine += "v. " ;
	}
	if (uneDate.jour != 0) {
		if (uneDate.jour == 1) {
			chaine += '1er ' ;
		}
		else chaine += uneDate.jour + ' '  ;
	}
	if (uneDate.vers == 4) {
		chaine += lesSaisons[uneDate.mois] ;
	}
	else {
		chaine += lesmois[uneDate.mois] ;
	}
	if (uneDate.annee < 0) {
		chaine += ' ' + donnees.lienAnnee(-uneDate.annee + " av. JC", uneDate.annee, 0, e) ;
	}
	else {
		chaine += ' ' + donnees.lienAnnee(uneDate.annee, uneDate.annee, 0, e) ;
	}
	return chaine ;
}

function affichedeuxdates(j, m, a, v, j2, m2, a2, v2, e)  {
	var d1 = {
		"jour":j,
		"mois":m,
		"annee":eval(a),
		"vers":v } ;
	var d2 = {
		"jour":j2,
		"mois":m2,
		"annee":eval(a2),
		"vers":v2} ;
	if ((d2.vers == 3) && (d2.annee == (eval(d1.annee) + 10))) return afficheunedate(d1, e) ;
	if ((d2.vers == 2) && (d2.annee == d1.annee + 100)) return afficheunedate(d1, e) ;
	if ((d2.vers == 2) && (d2.annee == d1.annee + 99)) return afficheunedate(d1, e) ;
	//doublons
	if (d1.annee == d2.annee) {
		if (d1.vers == d2.vers) {
			if (d1.mois == d2.mois) {
				if (d1.jour == d2.jour) {
					return afficheunedate(d1, e) ;
				}
				else {
					//deux jours du même mois
					return vers(d1) + " " + jour(d1) + " - " + jour(d2) + mois(d1) + annee(d1, e) ;
				}
			}
			else {
				//deux jours de la même année
				return vers(d1) + " " + jour(d1) + mois(d1) + " - " + jour(d2) + mois(d2) + annee(d1, e) ;
			}
		}
	}
					
	var chaine = afficheunedate(d1, e) ;
	if (a2 != 0) {
		chaine += ' - ' + afficheunedate(d2, e) ;
	}
	return chaine ;
}

function afficheS(coor, type) {
	//type : 0 => latitude, 1 -> longitude ;
	type = type || 0 ;
	if (type == 0) {
		if (coor.degre > 0) {
			var pc = "N " ;
		}
		else {
			coor.degre = - coor.degre ;
			var pc = "S " ;
		}
	}
	else {
		if (coor.degre > 0) {
			var pc = "E" ;
		}
		else {
			coor.degre = - coor.degre ;
			var pc = "O" ;
		}
	}
	return coor.degre + "° " + coor.minute + "' " + coor.seconde + "'' " + pc ;
}
