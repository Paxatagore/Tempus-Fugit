/* gestion des types d'évènements
- pour mémoire, ces types sont les suivants : générique, oeuvre d'art, bataille, règne, guerre, période historique, traité, loi, évènement complexe, tendances, notice, élection
- un évènement appartient à un type et à un seul (noté par evenement.typeeve)
- on distingue une série d'objet de type unTypedEvenement de l'objet lesTypesdEvenements qui est un objet, unique, collectionnant tous les types dans un tableau
*/

//définition du type unType d'évènement

function unTypedEvenement(nom, elementHTML, elementCSS) {
	this.nom 			= nom || "Générique" ;								//nom du type, fourni par l'appel à la fonction constructeur - générique est le défaut
	this.actif 			= 1 ;												//par défaut, le type est activé et les évènements de ce type seront donc affichés
	this.elementHTML 	= elementHTML || nom.slice(0, 3).toLowerCase() ;	//le suffixe de l'ID du span HTML contenant le lien vers ce type d'évenement. s'il n'est pas fourni dans l'appel de la fonction constructeur, ce sont les trois premières lettres du nom, en minuscule
	this.elementCSS		= elementCSS || nom.slice(0, 3).toLowerCase() ;		//le suffixe du style correspondant à ce type d'évènement
}

unTypedEvenement.prototype 	= {
	//afficheLien() est une fonction qui affiche le lien vers le type d'évenement, dans le menu du haut. Elle crée un appel vers la fonction toggle, qui permet d'activer ou désactiver les évènements relevant de ce type d'évènement
	afficheLien : function(i) {
		return $('subMenuTypes').innerHTML += '<li><a id="typeeve' + this.elementHTML+ '" onClick="lesTypesdEvenements.toggle(' + i + ')" OnDblclick = "lesTypesdEvenements.selectOnly(' + i + ')">' + this.nom + '</a></li>' ;
	},
	//activeTypeEvenement() : active ce type d'évenement. La propriété "actif" passe à 1, on enlève tout style particuliers d'affichage du lien
	activeTypeEvenement : function() {
		this.actif = 1 ;
		$("typeeve" + this.elementHTML).removeClassName("menuItemGrise") ;
		return this.actif ;
	},
	//desactiveTypeEvenement() : la fonction contraire. actif passe à 0, on affiche le lien en grisé
	desactiveTypeEvenement : function() {
		this.actif = 0 ;
		$("typeeve" + this.elementHTML).addClassName("menuItemGrise") ;
		return this.actif ;
	},
	//fonction qui renvoie this.actif (future encapsulation)
	isActif : function() {
		return this.actif ;
	}
} ;

//création de l'objet unique lesTypesdEvenements (tableau d'objets de type unTypedEvenement)

lesTypesdEvenements = new Array(new unTypedEvenement("Générique", "gen", ""), new unTypedEvenement("Oeuvre d'art", "oeu", "oeuvreart"),  new unTypedEvenement("Bataille", "bat", "bataille"), 
		new unTypedEvenement("Règne", "reg", "regne"), new unTypedEvenement("Guerre", "gue", "guerre"), new unTypedEvenement("Période historique", "per", "periodehistorique"), 
		new unTypedEvenement("Traité", "tra", "traite"), new unTypedEvenement("Loi"), new unTypedEvenement("Evenement complexe"), new unTypedEvenement("Tendances", "ten", "tendance"), 
		new unTypedEvenement("Notice", "not", "notice"),new unTypedEvenement("Election"), new unTypedEvenement("Match", "mat", "match"), new unTypedEvenement("Naissance"), 
		new unTypedEvenement("Carte"), new unTypedEvenement("Dynastie"), new unTypedEvenement("Occupation étrangère")) ;
//en cas de création d'un nouveau type, il suffit de l'ajouter ici

lesTypesdEvenements.affiche = function () {
	//affiche le menu des types d'évènements
	//cette fonction est appelée une seule fois, au lancement de la page, une fois celle-ci chargée, par la fonction onLoad() qui commende le début du script
	for (var i = 0 ; i < this.length ; i++) {
		this[i].afficheLien(i) ;
	}
} ;

lesTypesdEvenements.toggle = function(i) {
	//active ou désactive un type d'évènement donné
	if (this[i].actif == 0) {
		//on active
		this[i].activeTypeEvenement() ;
	}
	else {
		this[i].desactiveTypeEvenement() ;
	}
	lesEvenements.affiche() ;		//on relance le moteur qui décide si les évènements sont affichés ou non
	return this[i].actif ;
} ;

lesTypesdEvenements.selectOnly = function(n) {
	//sélectionne uniquement le i
	for (var i = 0; i < this.length ; i++) {
		if (i == n) {
			this[i].activeTypeEvenement() ;
		}
		else {
			this[i].desactiveTypeEvenement() ;
		}
	}
	lesEvenements.affiche() ;		//on relance le moteur qui décide si les évènements sont affichés ou non
} ;

lesTypesdEvenements.isActif = function(i) {
	//i peut être : soit un nombre qui correspond au type d'évènement (en pratique, c'est une chaine)
	//soit un évènement lui même : en ce cas, on interroge son type d'évènement
	if ("string" === typeof i) {
		return this[i].isActif() ;
	}
	if ("object" === typeof i) {
		//on suppose que i n'est pas un nombre mais un objet de type lesEvenements (qui reste à implémenter)
		return this[i.typeeve].isActif() ;
	}
	return 1 ;	//par défaut, on ne fait pas obstacle à l'affichage de l'évènement.
} ;

lesTypesdEvenements.modeChronos = function() {
	//passe en mode "chronos", spécialement pour rentrer les dates
	this[0].activeTypeEvenement() ;	//générique
	this[1].activeTypeEvenement() ;	//oeuvre d'art
	this[2].activeTypeEvenement() ;	//bataille
	this[3].desactiveTypeEvenement() ;	//règne
	this[4].activeTypeEvenement() ;	//guerre
	this[5].desactiveTypeEvenement() ;	//période historique
	this[6].activeTypeEvenement() ; //Traité
	this[7].activeTypeEvenement() ; //Loi
	this[8].activeTypeEvenement() ; //Evenement complexe
	this[9].desactiveTypeEvenement() ; //Tendances
	this[10].desactiveTypeEvenement() ;	//notice
	this[11].activeTypeEvenement() ;	//élection
	this[12].desactiveTypeEvenement() ;	//match
	this[13].activeTypeEvenement() ;	//naissance
	this[14].desactiveTypeEvenement() ;	//carte
	this[15].desactiveTypeEvenement() ; //dynastie
	this[16].desactiveTypeEvenement() ; //occupation étrangère
	lesEvenements.affiche() ;		//on relance le moteur qui décide si les évènements sont affichés ou non
} ;

lesTypesdEvenements.modeNormal = function() {
	//passe en mode normal, ou tout est affiché.
	for (var i = 0 ; i < 17 ; i++) {
		this[i].activeTypeEvenement() ;
	}
	return lesEvenements.affiche() ;		//on relance le moteur qui décide si les évènements sont affichés ou non
} ;