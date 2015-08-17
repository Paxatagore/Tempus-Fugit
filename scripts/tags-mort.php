<?php
require_once("../inc/centrale.php") ;
//les parents
//on sélection l'ensemble personnes - lienpl qui est bien enregistré comme il faut et on l'exclut du grand ensemble personnes - événément naissance
$q = "SELECT DISTINCT lienpl.num, lienpl.lieu, personne.mort_evenementId
FROM personne, lienpl
WHERE personne.num = lienpl.personne
AND personne.mort_evenementId > 0
AND (lienpl.num) NOT
IN (SELECT lienpl.num 
FROM evenement, personne, lienpl, lienel
WHERE lienel.evenement = evenement.num 
AND evenement.num = personne.mort_evenementId
AND personne.num = lienpl.personne
AND lienpl.lieu = lienel.tag)" ;
mysqldb::send($q) ;
$listepok = array() ;
$requetes = array() ;
while ($row = mysqldb::$req->fetch()) {
	$listepok[] = $row ;
}
mysqldb::$req->closeCursor() ;

$lienel = new lienel() ;
if (count($listepok) > 0) {
	foreach($listepok as $pok) {
		list($lplnum, $tag, $evenement) = $pok ;
		$lienel->num = 0 ;
		$lienel->tag = $tag ;
		$lienel->evenement = $evenement ;
		$lienel->save() ;
		echo ("Ajout d'un lien el ($lienel->num) sur l'événément $evenement, avec le tag $tag, qui vient de la relation personne - tag $lplnum. <br/>") ;
	}
}
echo "<br/>Le script a bien été exécuté.<br/>" ;
?>
