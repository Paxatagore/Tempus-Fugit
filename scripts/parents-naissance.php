<?php
require_once("../inc/centrale.php") ;
//les parents
//on sélection l'ensemble personnes - événément naissance - lienep - relationpersonnes qui est bien enregistré comme il faut et on l'exclut du grand ensebmel personnes - événément naissance - lien ep
$q = "SELECT DISTINCT relationpersonnes.num AS rpp, relationpersonnes.personne2, personne.nom, evenement.num
FROM personne, evenement, relationpersonnes
WHERE personne.naissance_evenementId = evenement.num
AND evenement.typeeve =13
AND relationpersonnes.personne1 = personne.num
AND relationpersonnes.typerelation =1
AND (
relationpersonnes.num
) NOT
IN (

SELECT relationpersonnes.num
FROM personne, evenement, relationpersonnes, lienep
WHERE personne.naissance_evenementId = evenement.num
AND evenement.typeeve =13
AND relationpersonnes.personne1 = personne.num
AND relationpersonnes.typerelation =1
AND lienep.evenement = evenement.num
AND lienep.personne = relationpersonnes.personne2
)
LIMIT 0 , 30" ;
mysqldb::send($q) ;
$listepok = array() ;
$requetes = array() ;
while ($row = mysqldb::$req->fetch()) {
	$listepok[] = $row ;
	echo ($row[2]." a un parent ".$row[1]." qui n'est pas repris dans son événément de naissance ".$row[3]." <br/>") ;
}
mysqldb::$req->closeCursor() ;
$lienep = new lienep() ;
if (count($listepok) > 0) {
	foreach($listepok as $pok) {
		list($rpp, $p2, $pnom, $e) = $pok ;
		$lienep->num = 0 ;
		$lienep->personne = $p2 ;
		$lienep->evenement = $e ;
		$lienep->save() ;
	}
}
echo "<br/>Le script a bien été exécuté.<br/>" ;
?>
