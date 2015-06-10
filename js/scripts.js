/* scripts */

ListeScripts = [{
		"Nom" : "Nouveau jour",
		"Script":"todayIsANewDay",
		"Description" : "Remise à zéro des occurences journalières",
		"Out":0
	},
	{
		"Nom" : "Continuité dynastique",
		"Script" : "dynasties",
		"Description" : "Script s'assurant que tous les enfants d'un dynaste appartiennent à la même dynastie",
		"Out":0
	},
	{
		"Nom" : "Liste des personnes",
		"Script" : "listepersonnes",
		"Description" : "Script affichant la liste des personnes",
		"Out":0
	},
	{
		"Nom" : "Gestion des liens de personnes",
		"Script" : "personne-naissance",
		"Description" : "Modification des naissances des personnes",
		"Out":0
	},
	{
		"Nom" : "Fusion automatique des doublons",
		"Script" : "doublons",
		"Description" : "Script qui fusionne les homonymes",
		"Out":0
	},
	{
		"Nom" : "Fusion de personnes",
		"Script" : "mergePersonne",
		"Description" : "Script permettant de fusionner deux personnes",
		"Out":1
	},
	{
		"Nom":"Tue les personnes vides",
		"Script":"tuePersonnesVides",
		"Description":"Script qui supprime les personnes vides",
		"Out":0
	},
	{
		"Nom" : "Calcul des occurences",
		"Script" : "occurences",
		"Description" : "Script déterminant les occurences des tags, des personnes et des dynasties",
		"Out":0
	},
	{
		"Nom" : "Génération d'un mot de passe",
		"Script" : "passwordmaker",
		"Description" : "Formulaire pour crypter un mot de passe",
		"Out":0
	},
	{
		"Nom" : "PHPinfo",
		"Script" : "phpinfo",
		"Description" : "phpinfo",
		"Out":0
	},
	{
		"Nom" : "Stats",
		"Script" : "stats",
		"Description" : "Statistiques MySQL",
		"Out":0
	},
	{
		"Nom" : "Copie les images",
		"Script" : "copieimage",
		"Description" : "Transfère les images sur le serveur local",
		"Out":0
	},
	{
		"Nom" : "Upload une carte",
		"Script" : "upload_carte",
		"Description" : "Transfère une carte sur le serveur local depuis l'ordinateur",
		"Out":1
	},
	{
		"Nom" : "Mise à jour des liens pères fils sur les tags",
		"Script" : "tags peres_fils",
		"Description" : "Met à jour les liens pères et fils sur les tags.",
		"Out":0
	}	
	] ;
	

function afficheScripts() {
	var chaine = '' ;
	for(var i = 0 ; i < ListeScripts.length ; i++) {
		if (ListeScripts[i].Out == 0) {
			chaine += '<div class="unScript"><a class="cliquable" onClick = "lanceScripts( ' + i + ')" title="' + ListeScripts[i].Description + '">' + ListeScripts[i].Nom + '</a></div><div class="executionScript" id="zoneScript' + i + '"></div>' ;
		}
		else {
			chaine += '<div class="unScript"><a class="cliquable" target="_blank" href = "../scripts/' + ListeScripts[i].Script + '.php">' + ListeScripts[i].Nom + '</a></div><div class="executionScript" id="zoneScript' + i + '"></div>' ;
		}
	}
	$('listeScripts').innerHTML = chaine ;
	return chaine ;
} ;

function lanceScripts(i) {
	videScripts() ;
	$('zoneScript' + i).innerHTML = "Lancement du script " + ListeScripts[i].Nom + '<br/>' ;
	var exescript = new Ajax.Updater('zoneScript' + i, localisation + 'scripts/' + ListeScripts[i].Script + '.php', {
			method : "post",
			insertion:Insertion.Top} ); 
	console.log("Script exécuté.") ;
}

function videScripts() {
	console.log("Vide les zones d'affichage des scripts.") ;
	for(var i = 0 ; i < ListeScripts.length ; i++) {
		$('zoneScript' + i).innerHTML = "" ;
	}
	return 1 ;
}