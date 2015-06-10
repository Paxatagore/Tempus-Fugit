/* gestion des tags thématiques
- les tags thématiques correspondent à des tags de référence
- un évenement peut relever de 0, 1 ou n tags thématiques
- on distingue des objets de type unTagThematique et une collection de ces objets, le tableau lesTypesdeTagThematique
*/

function unTagThematique(nom, equivalent, elementHTML, elementCSS) {
	this.nom 			= nom ;				//le nom du tag thématique
	this.equivalent 	= equivalent ;		//correspondant au numéro de tag dans la liste des tags
	this.tableau 		= new Array() ;		//tableau des évènements associés
	this.elementHTML 	= elementHTML || nom.slice(0, 3).toLowerCase() ;	//le suffixe de l'ID du span HTML contenant le lien vers ce tag thématique. s'il n'est pas fourni dans l'appel de la fonction constructeur, ce sont les trois premières lettres du nom, en minuscule
	this.elementCSS		= elementCSS || nom.slice(0, 3).toLowerCase() ;
}

unTagThematique.prototype 	= {
	//afficheLien() est une fonction qui affiche le lien vers le tag thématique, dans le menu du haut. Elle crée un appel vers la fonction toggle, qui permet d'activer ou désactiver les évènements relevant de ce tag thématique
	afficheLien : function(i) {
		return $('subMenuThematique').innerHTML += '<li><a id="tagthem' + this.elementHTML + '" onClick="lesTypesdeTagThematique.toggle(' + i + ')">' + this.nom + '</a></li>' ;
	},
	//activeTagThematique() : active ce tag. On enlève tout style particuliers d'affichage du lien
	activeTagThematique : function() {
		$("tagthem" + this.elementHTML).removeClassName("menuItemGrise") ;
	},
	//desactiveTagThematique() : la fonction contraire. On affiche le lien en grisé
	desactiveTagThematique : function() {
		//$("tagthem" + this.elementHTML).src = '../img/icone' + this.elementHTML + '2.png' ;
		$("tagthem" + this.elementHTML).addClassName("menuItemGrise") ;
	},
	//ajoute un évenement donné (i étant son numéro) à ce tag thématique (dans le tableau this.tableau)
	addEvenement : function(i) {
		this.tableau.push(i) ;
	},
	//vérifie si oui ou non l'évènement dont le numéro est i relève de ce tag thématique là
	isIncluded : function(i) {
		for (var x = 0 ; x < this.tableau.length ; x++) {
			if (this.tableau[x] == i) return 1 ;
		}
		return 0 ;
	}
} ;

//lesTypesdeTagThematique est une collection (tableau) de tous les tags thématiques

lesTypesdeTagThematique = new Array(
		new unTagThematique("Arts", 19, "art", "arts"), 
		new unTagThematique("Idées", 22, "ide", "idees"), 
		new unTagThematique("Militaire", 16, "mil", "militaire"), 
		new unTagThematique("Politique", 15, "pol", "politique"), 
		new unTagThematique("Religion", 17, "rel", "religion"), 
		new unTagThematique("Sciences", 21, "sci", "sciences"), 
		new unTagThematique("Société", 18, "soc", "societe"), 
		new unTagThematique("Diplomatie", 79, "dip", "diplomatie"), 
		new unTagThematique("Sports", 20, "spo", "sports")) ;
lesTypesdeTagThematique.actif = -1 ;

lesTypesdeTagThematique.affiche = function () {
	//affiche le menu des tags thématiques
	//cette fonction est appelée une seule fois, au lancement de la page, une fois celle-ci chargée, par la fonction onLoad() qui commende le début du script
	for (var i = 0 ; i < this.length ; i++) {
		this[i].afficheLien(i) ;
	}
} ;

lesTypesdeTagThematique.addEvenement = function(lienel) {
	//prend un lienel, vérifie s'il correspond à l'un des tags thématiques et, si oui, le stocke dans le tag thématique (en faisant appel à la fonction addEvenement de l'objet unTagThematique.
	for (var i = 0 ; i < this.length ; i++) {
		if (this[i].equivalent == lienel.tag) {
			this[i].addEvenement(lienel.evenement) ;
			return 1 ;
		}
	}
	return 0 ;
} ;

lesTypesdeTagThematique.toggle = function(tagThem) {
	//sélectionne / déselectionne un tag thématique donné
	//tagThem est le numéro du tag thématique concerné
	
	if (lesTypesdeTagThematique.actif != tagThem) {
		//on change de tag thématique
		//on grise tous les tags thems
		for (var i = 0 ; i < this.length ; i++) {
			this[i].desactiveTagThematique() ;
		}
		//sauf celui qui est concerné, qu'on bleute
		this[tagThem].activeTagThematique() ;
		lesTypesdeTagThematique.actif = tagThem ;
	}
	else {
		lesTypesdeTagThematique.actif = -1 ;
		for (var i = 0 ; i < this.length ; i++) {
			this[i].activeTagThematique() ;
		}
	}
	lesEvenements.affiche() ;
	return lesTypesdeTagThematique.actif ;
} ;

lesTypesdeTagThematique.isActif = function(evenement) {
	//précise si l'évèenement donné (qu'on passe comme objet et pas comme nom) doit être affiché ou non
	if (lesTypesdeTagThematique.actif == -1) return 1 ;		//aucun tag n'est particulièrement spécifié => pas de filtre
	//return this[lesTypesdeTagThematique.actif].isIncluded(evenement.num) ;	//on filtre sur ce tag thématique particuliers
	return evenement.thematiques[lesTypesdeTagThematique.actif] ;
} ;